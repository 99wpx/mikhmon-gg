<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  Licensed under the GNU General Public License v2 or later.
 */
session_start();
error_reporting(0); // Set to E_ALL & display_errors = 1 during dev

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

// Load session
$session = isset($_GET['session']) ? htmlspecialchars($_GET['session']) : '';
$serveractive = isset($_GET['server']) ? htmlspecialchars($_GET['server']) : '';

// Load config
include('../include/config.php');
include('../include/readcfg.php');
include('../include/lang.php');
include('../lang/' . $langid . '.php');

// Load RouterOS API
include_once('../lib/routeros_api.class.php');
include_once('../lib/formatbytesbites.php');
$API = new RouterosAPI();
$API->debug = false;
$API->connect($iphost, $userhost, decrypt($passwdhost));

// Get active hotspot users
if ($serveractive != "") {
    $gethotspotactive = $API->comm("/ip/hotspot/active/print", ["?server" => $serveractive]);
    $TotalReg = count($gethotspotactive);
    $counthotspotactive = $API->comm("/ip/hotspot/active/print", [
        "count-only" => "",
        "?server" => $serveractive
    ]);
} else {
    $gethotspotactive = $API->comm("/ip/hotspot/active/print");
    $TotalReg = count($gethotspotactive);
    $counthotspotactive = $API->comm("/ip/hotspot/active/print", ["count-only" => ""]);
}

$API->disconnect();
?>

<div class="row">
<div id="reloadHotspotActive">
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-wifi"></i> <?= $_hotspot_active ?>
                <?php
                if ($serveractive != "") {
                    echo $serveractive . " ";
                }
                echo ($counthotspotactive < 2) ? "$counthotspotactive item" : "$counthotspotactive items";
                if ($serveractive != "") {
                    echo " | <a href='./?hotspot=active&session=$session'> <i class='fa fa-search'></i> Show all</a>";
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
                        <th class="text-right">Uptime</th>
                        <th class="text-right">Bytes In</th>
                        <th class="text-right">Bytes Out</th>
                        <th class="text-right">Time Left</th>
                        <th>Login By</th>
                        <th>Profile</th>
                        <th><?= $_comment ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 0; $i < $TotalReg; $i++) {
                        $hotspotactive = $gethotspotactive[$i];
                        $id = $hotspotactive['.id'];
                        $server = $hotspotactive['server'] ?? '-';
                        $user = $hotspotactive['user'] ?? '-';
                        $address = $hotspotactive['address'] ?? '-';
                        $mac = $hotspotactive['mac-address'] ?? '-';
                        $uptime = formatDTM($hotspotactive['uptime'] ?? '00:00:00');
                        $usesstime = isset($hotspotactive['session-time-left']) ? formatDTM($hotspotactive['session-time-left']) : '-';
                        $bytesi = formatBytes($hotspotactive['bytes-in'] ?? 0, 2);
                        $byteso = formatBytes($hotspotactive['bytes-out'] ?? 0, 2);
                        $loginby = $hotspotactive['login-by'] ?? '-';
                        $profile = $hotspotactive['user-profile'] ?? '-';
                        $comment = $hotspotactive['comment'] ?? '-';

                        $uriprocess = "'./?remove-user-active=$id&session=$session'";
                        echo "<tr>";
                        echo "<td class='text-center'><span class='pointer' title='Remove $user' onclick=loadpage($uriprocess)><i class='fa fa-minus-square text-danger'></i></span></td>";
                        echo "<td><a title='Filter $server' href='./?hotspot=active&server=$server&session=$session'><i class='fa fa-server'></i> $server</a></td>";
                        echo "<td><a title='Open User $user' href='./?hotspot-user=$user&session=$session'><i class='fa fa-edit'></i> $user</a></td>";
                        echo "<td>$address</td>";
                        echo "<td>$mac</td>";
                        echo "<td class='text-right'>$uptime</td>";
                        echo "<td class='text-right'>$bytesi</td>";
                        echo "<td class='text-right'>$byteso</td>";
                        echo "<td class='text-right'>$usesstime</td>";
                        echo "<td>$loginby</td>";
                        echo "<td>$profile</td>";
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

<!-- DataTables Support -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script>
$(document).ready(function () {
    $('#tFilter').DataTable({
        "order": [[2, "asc"]],
        "pageLength": 25,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "zeroRecords": "No matching records found",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No entries available",
            "infoFiltered": "(filtered from _MAX_ total entries)"
        }
    });
});
</script>
