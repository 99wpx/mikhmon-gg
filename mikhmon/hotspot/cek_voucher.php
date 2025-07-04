<?php
session_start();
error_reporting(0);

// Block direct access and ensure user is logged in
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__) || !isset($_SESSION['mikhmon'])) {
    header("Location: ../admin.php?id=login");
    exit;
}

include('../include/config.php');
include('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');

function secondsToTime($seconds) {
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes');
}

function parseTimeToSeconds($timeStr) {
    $time = 0;
    if (preg_match_all('/(\d+)([dhms])/', $timeStr, $matches)) {
        foreach ($matches[1] as $i => $val) {
            switch ($matches[2][$i]) {
                case 'd': $time += $val * 86400; break;
                case 'h': $time += $val * 3600; break;
                case 'm': $time += $val * 60; break;
                case 's': $time += $val; break;
            }
        }
    }
    return $time;
}

$API = new RouterosAPI();
$API->debug = false;
$session = htmlspecialchars($_GET['session'] ?? '');
?>

<div class="row">
  <div class="col-8">
    <div class="card box-bordered">
      <div class="card-header">
        <h3><i class="fa fa-search"></i> Check Voucher Status</h3>
      </div>
      <div class="card-body">
        <!-- Form -->
        <form method="get" action="">
          <input type="hidden" name="hotspot" value="cek-voucher">
          <input type="hidden" name="session" value="<?= $session ?>">
          <div class="form-group">
            <label>Enter Voucher Username</label>
            <input type="text" name="user" class="form-control" placeholder="Example: wifi123" required>
          </div>
          <button type="submit" class="btn bg-primary">
            <i class="fa fa-search"></i> Check Status
          </button>
        </form>

        <hr>

<?php
if (!empty($_GET['user'])):
    $username = htmlspecialchars($_GET['user']);

    if ($API->connect($iphost, $userhost, decrypt($passwdhost))):
        // Get user data
        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $username);
        $userData = $API->read();

        // Get active session data
        $API->write('/ip/hotspot/active/print', false);
        $API->write('?user=' . $username);
        $activeData = $API->read();

        $API->disconnect();

        if (!empty($userData)):
            $user = $userData[0];
            $uptimeUsed = parseTimeToSeconds($user['uptime'] ?? '0s');
            $uptimeLimit = parseTimeToSeconds($user['limit-uptime'] ?? '0s');
            $remaining = ($uptimeLimit > 0) ? max(0, $uptimeLimit - $uptimeUsed) : null;

            echo "<table class='table table-bordered'>";
            echo "<tr><th>Username</th><td><b>{$user['name']}</b></td></tr>";
            echo "<tr><th>Password</th><td>" . ($user['password'] ?? '-') . "</td></tr>";
            echo "<tr><th>Profile</th><td>" . ($user['profile'] ?? '-') . "</td></tr>";
            echo "<tr><th>Used Uptime</th><td>" . ($user['uptime'] ?? '-') . "</td></tr>";
            echo "<tr><th>Uptime Limit</th><td>" . ($user['limit-uptime'] ?? 'Unlimited') . "</td></tr>";
            echo "<tr><th>Remaining Time</th><td>" . ($remaining !== null ? secondsToTime($remaining) : 'Unlimited') . "</td></tr>";
            echo "<tr><th>Status</th><td>" . ($user['disabled'] === 'true' ? "<span class='text-danger'>Disabled</span>" : "<span class='text-success'>Active</span>") . "</td></tr>";
            echo "<tr><th>Data Limit</th><td>" . ($user['limit-bytes-total'] ?? 'Unlimited') . "</td></tr>";
            echo "<tr><th>Comment</th><td>" . ($user['comment'] ?? '-') . "</td></tr>";

            if (!empty($activeData)):
                $active = $activeData[0];
                echo "<tr><th colspan='2' class='table-active text-center'>Status: <span class='text-success'>Logged In</span></th></tr>";
                echo "<tr><th>Bytes In</th><td>" . ($active['bytes-in'] ?? '0') . " Bytes</td></tr>";
                echo "<tr><th>Bytes Out</th><td>" . ($active['bytes-out'] ?? '0') . " Bytes</td></tr>";
                echo "<tr><th>Active Uptime</th><td>" . ($active['uptime'] ?? '-') . "</td></tr>";
                echo "<tr><th>IP Address</th><td>" . ($active['address'] ?? '-') . "</td></tr>";
                echo "<tr><th>MAC Address</th><td>" . ($active['mac-address'] ?? '-') . "</td></tr>";
            else:
                echo "<tr><th colspan='2' class='table-active text-center'>Status: <span class='text-muted'>Not Active / Logged Out</span></th></tr>";
            endif;

            echo "</table>";
        else:
            echo "<div class='alert alert-danger'>Voucher <b>$username</b> not found.</div>";
        endif;
    else:
        echo "<div class='alert alert-danger'>Failed to connect to MikroTik.</div>";
    endif;
endif;
?>
      </div>
    </div>
  </div>

  <div class="col-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> Information</h3>
      </div>
      <div class="card-body">
        <p>Enter the <b>voucher username</b> such as <code>wifi123</code> to check its usage status.</p>
        <p><b>Displayed:</b> Active status, uptime, data limit, IP & MAC address if logged in.</p>
      </div>
    </div>
  </div>
</div>
