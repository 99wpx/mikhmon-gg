<?php
session_start();
error_reporting(0);

// Blokir akses langsung dan pastikan login
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
    return $dtF->diff($dtT)->format('%a hari, %h jam, %i menit');
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

function formatBytes($bytes, $precision = 2) {
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if ($bytes == 0) return '0 Bytes';
    $pow = floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

$API = new RouterosAPI();
$API->debug = false;
$session = htmlspecialchars($_GET['session'] ?? '');
?>

<div class="row">
  <div class="col-8">
    <div class="card box-bordered">
      <div class="card-header">
        <h3><i class="fa fa-search"></i> Cek Status Voucher</h3>
      </div>
      <div class="card-body">

        <!-- Form -->
        <form method="get" action="">
          <input type="hidden" name="hotspot" value="cek-voucher">
          <input type="hidden" name="session" value="<?= $session ?>">
          <div class="form-group">
            <label>Masukkan Username Voucher</label>
            <input type="text" name="user" class="form-control" placeholder="Contoh: wifi123" required>
          </div>
          <button type="submit" class="btn bg-primary"><i class="fa fa-search"></i> Cek Status</button>
        </form>

        <hr>

<?php
if (!empty($_GET['user'])):
    $username = htmlspecialchars($_GET['user']);

    if ($API->connect($iphost, $userhost, decrypt($passwdhost))):
        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $username);
        $userData = $API->read();

        $API->write('/ip/hotspot/active/print', false);
        $API->write('?user=' . $username);
        $activeData = $API->read();

        $API->disconnect();

        if (!empty($userData)):
            $user = $userData[0];
            $uptimeUsed = parseTimeToSeconds($user['uptime'] ?? '0s');
            $uptimeLimit = parseTimeToSeconds($user['limit-uptime'] ?? '0s');
            $remaining = ($uptimeLimit > 0) ? max(0, $uptimeLimit - $uptimeUsed) : null;

            // Tentukan Bytes In/Out
            if (!empty($activeData)) {
                $active = $activeData[0];
                $bytesIn = formatBytes($active['bytes-in'] ?? 0);
                $bytesOut = formatBytes($active['bytes-out'] ?? 0);
            } else {
                $bytesIn = formatBytes($user['bytes-in'] ?? ($user['bytes-total'] ?? 0));
                $bytesOut = formatBytes($user['bytes-out'] ?? 0);
            }

            echo "<table class='table table-bordered'>";
            echo "<tr><th>Username</th><td><b>{$user['name']}</b></td></tr>";
            echo "<tr><th>Password</th><td>" . ($user['password'] ?? '-') . "</td></tr>";
            echo "<tr><th>Profile</th><td>" . ($user['profile'] ?? '-') . "</td></tr>";
            echo "<tr><th>Uptime Digunakan</th><td>" . ($user['uptime'] ?? '-') . "</td></tr>";
            echo "<tr><th>Limit Uptime</th><td>" . ($user['limit-uptime'] ?? 'Unlimited') . "</td></tr>";
            echo "<tr><th>Sisa Waktu</th><td>" . ($remaining !== null ? secondsToTime($remaining) : 'Unlimited') . "</td></tr>";

            $disabled = ($user['disabled'] === 'true');
            echo "<tr><th>Status</th><td>" . ($disabled ? "<span class='text-danger'>❌ Nonaktif</span>" : "<span class='text-success'>✅ Aktif</span>") . "</td></tr>";
            echo "<tr><th>Komentar</th><td>" . ($user['comment'] ?? '-') . "</td></tr>";

            if (!empty($activeData)) {
                echo "<tr class='table-success text-center'><th colspan='2'>Status: <span class='text-success'>✅ Login</span></th></tr>";
                echo "<tr><th>Uptime Aktif</th><td>" . ($active['uptime'] ?? '-') . "</td></tr>";
                echo "<tr><th>IP Address</th><td>" . ($active['address'] ?? '-') . "</td></tr>";
                echo "<tr><th>MAC Address</th><td>" . ($active['mac-address'] ?? '-') . "</td></tr>";
            } else {
                echo "<tr class='table-warning text-center'><th colspan='2'>Status: <span class='text-muted'>❌ Tidak Aktif / Logout</span></th></tr>";
            }

            echo "<tr><th>Bytes In</th><td>$bytesIn</td></tr>";
            echo "<tr><th>Bytes Out</th><td>$bytesOut</td></tr>";
            echo "</table>";

        else:
            echo "<div class='alert alert-danger'>Voucher <b>$username</b> tidak ditemukan.</div>";
        endif;
    else:
        echo "<div class='alert alert-danger'>Koneksi ke Mikrotik gagal.</div>";
    endif;
endif;
?>
      </div>
    </div>
  </div>

  <div class="col-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> Keterangan</h3>
      </div>
      <div class="card-body">
        <p>Masukkan <b>username voucher</b> seperti <code>wifi123</code> untuk melihat status penggunaannya.</p>
        <p><b>Ditampilkan:</b> Status aktif, uptime, sisa waktu, Bytes In/Out, dan IP saat login.</p>
      </div>
    </div>
  </div>
</div>
