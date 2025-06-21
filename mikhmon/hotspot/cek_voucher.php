<?php
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

include('../include/config.php');
include('../include/mikrotik.php');

function secondsToTime($seconds) {
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a hari, %h jam, %i menit');
}

function parseTimeToSeconds($timeStr) {
    $time = 0;
    if (preg_match_all('/(\d+)([dhms])/', $timeStr, $matches)) {
        foreach ($matches[1] as $i => $value) {
            switch ($matches[2][$i]) {
                case 'd': $time += $value * 86400; break;
                case 'h': $time += $value * 3600; break;
                case 'm': $time += $value * 60; break;
                case 's': $time += $value; break;
            }
        }
    }
    return $time;
}
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-search"></i> Cek Status Voucher</h3>
      </div>
      <div class="card-body">
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
if ($API->connect($host, $user, $pass)) {
    if (!empty($_GET['user'])) {
        $username = $_GET['user'];

        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $username);
        $result = $API->read();

        if (!empty($result)) {
            $user = $result[0];
            $uptimeUsed = isset($user['uptime']) ? parseTimeToSeconds($user['uptime']) : 0;
            $uptimeLimit = isset($user['limit-uptime']) ? parseTimeToSeconds($user['limit-uptime']) : 0;
            $remaining = ($uptimeLimit > 0) ? max(0, $uptimeLimit - $uptimeUsed) : null;

            echo "<table class='table table-bordered'>";
            echo "<tr><th>Username</th><td><b>{$user['name']}</b></td></tr>";
            echo "<tr><th>Password</th><td>{$user['password']}</td></tr>";
            echo "<tr><th>Profile</th><td>{$user['profile']}</td></tr>";
            echo "<tr><th>Uptime Digunakan</th><td>{$user['uptime']}</td></tr>";
            echo "<tr><th>Limit Uptime</th><td>" . ($user['limit-uptime'] ?? 'Unlimited') . "</td></tr>";
            echo "<tr><th>Sisa Waktu</th><td>" . ($remaining !== null ? secondsToTime($remaining) : 'Unlimited') . "</td></tr>";
            echo "<tr><th>Status</th><td>" . ($user['disabled'] == 'true' ? "<span class='text-danger'>Nonaktif</span>" : "<span class='text-success'>Aktif</span>") . "</td></tr>";
            echo "<tr><th>Data Terpakai</th><td>" . ($user['bytes-total'] ?? '0') . " Bytes</td></tr>";
            echo "<tr><th>Limit Data</th><td>" . ($user['limit-bytes-total'] ?? 'Unlimited') . "</td></tr>";
            echo "<tr><th>Komentar</th><td>" . ($user['comment'] ?? '-') . "</td></tr>";
            echo "</table>";
        } else {
            echo "<div class='alert alert-danger'>Voucher <b>$username</b> tidak ditemukan.</div>";
        }
    }
    $API->disconnect();
} else {
    echo "<div class='alert alert-danger'>Koneksi ke Mikrotik gagal.</div>";
}
?>
      </div>
    </div>
  </div>
</div>
