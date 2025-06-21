<?php
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
	header("Location:../admin.php?id=login");
	exit;
}

$session = $_GET['session'];
$serveractive = $_GET['server'];

include('../include/config.php');
include('../include/readcfg.php');
include('../include/lang.php');
include('../lang/'.$langid.'.php');
include_once('../lib/routeros_api.class.php');
include_once('../lib/formatbytesbites.php');

$API = new RouterosAPI();
$API->debug = false;
$API->connect($iphost, $userhost, decrypt($passwdhost));

if ($serveractive != "") {
	$gethotspotactive = $API->comm("/ip/hotspot/active/print", ["?server" => $serveractive]);
	$TotalReg = count($gethotspotactive);
	$counthotspotactive = $API->comm("/ip/hotspot/active/print", [
		"count-only" => "", "?server" => $serveractive
	]);
} else {
	$gethotspotactive = $API->comm("/ip/hotspot/active/print");
	$TotalReg = count($gethotspotactive);
	$counthotspotactive = $API->comm("/ip/hotspot/active/print", ["count-only" => ""]);
}

// Fetch all hotspot users and map their profile by username
$allUsers = $API->comm("/ip/hotspot/user/print");
$userProfiles = [];
foreach ($allUsers as $usr) {
    $userProfiles[$usr['name']] = $usr['profile'] ?? '-';
}
?>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div class="row">
<div id="reloadHotspotActive">
<div class="col-12">
	<div class="card">
		<div class="card-header">
    		<h3><i class="fa fa-wifi"></i> Hotspot Active Users 
				<?php
				if ($serveractive != "") {
					echo $serveractive . " ";
				}
				echo ($counthotspotactive < 2) ? "$counthotspotactive item" : "$counthotspotactive items";
				if ($serveractive != "") {
					echo " | <a href='./?hotspot=active&session=" . $session . "'><i class='fa fa-search'></i> Show all</a>";
				}
				?>
			</h3>
        </div>
        <div class="card-body overflow">
<table id="tFilter" class="table table-bordered table-hover text-nowrap">
  <thead>
  <tr>
    <th></th>
    <th>Server</th>
    <th>User</th>
    <th>Address</th>
    <th>MAC Address</th>
    <th>Profile</th>
    <th class="text-right">Uptime</th>
    <th class="text-right">Bytes In</th>
    <th class="text-right">Bytes Out</th>
    <th>Login By</th>
    <th>Comment</th>
  </tr>
  </thead>
  <tbody>
<?php
for ($i = 0; $i < $TotalReg; $i++) {
	$hotspotactive = $gethotspotactive[$i];
	$id = $hotspotactive['.id'];
	$server = $hotspotactive['server'];
	$user = $hotspotactive['user'];
	$address = $hotspotactive['address'];
	$mac = $hotspotactive['mac-address'];
	$uptime = formatDTM($hotspotactive['uptime']);
	$bytesi = formatBytes($hotspotactive['bytes-in'], 2);
	$byteso = formatBytes($hotspotactive['bytes-out'], 2);
	$loginby = $hotspotactive['login-by'];
	$comment = $hotspotactive['comment'];
	$profile = $userProfiles[$user] ?? '-';

	$uriprocess = "'./?remove-user-active=" . $id . "&session=" . $session . "'";
	echo "<tr>";
	echo "<td style='text-align:center;'><span class='pointer' title='Remove $user' onclick=loadpage($uriprocess)><i class='fa fa-minus-square text-danger'></i></span></td>";
	echo "<td><a title='Filter by $server' href='./?hotspot=active&server=$server&session=$session'><i class='fa fa-server'></i> $server</a></td>";
	echo "<td><a title='Open user $user' href=./?hotspot-user=$user&session=$session><i class='fa fa-edit'></i> $user</a></td>";
	echo "<td>$address</td>";
	echo "<td>$mac</td>";
	echo "<td>$profile</td>";
	echo "<td style='text-align:right;'>$uptime</td>";
	echo "<td style='text-align:right;'>$bytesi</td>";
	echo "<td style='text-align:right;'>$byteso</td>";
	echo "<td>$loginby</td>";
	echo "<td>$comment</td>";
	echo "</tr>";
}
?>
  </tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<!-- Initialize DataTables -->
<script>
$(document).ready(function() {
    $('#tFilter').DataTable({
        columnDefs: [
            { orderable: false, searchable: false, targets: 0 }
        ],
        responsive: true,
        pageLength: 25,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found"
        }
    });
});
</script>
