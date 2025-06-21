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

echo '
<form method="get">
  <label>Masukkan Username Voucher:</label><br>
  <input type="text" name="user" required>
  <button type="submit">Cek Status</button>
</form>
<hr>
';

if ($API->connect($host, $user, $pass)) {
    if (isset($_GET['user'])) {
        $username = $_GET['user'];

        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $username);
        $result = $API->read();

        if (!empty($result)) {
            $user = $result[0];

            $uptimeUsed = isset($user['uptime']) ? parseTimeToSeconds($user['uptime']) : 0;
            $uptimeLimit = isset($user['limit-uptime']) ? parseTimeToSeconds($user['limit-uptime']) : 0;
            $remaining = ($uptimeLimit > 0) ? max(0, $uptimeLimit - $uptimeUsed) : null;

            echo "<h3>Status Voucher</h3>";
            echo "User: <b>" . $user['name'] . "</b><br>";
            echo "Password: " . ($user['password'] ?? '-') . "<br>";
            echo "Paket / Jenis Profile: <b>" . ($user['profile'] ?? '-') . "</b><br>";
            echo "Uptime Digunakan: " . ($user['uptime'] ?? '-') . "<br>";
            echo "Limit Uptime: " . ($user['limit-uptime'] ?? 'Unlimited') . "<br>";
            echo "Sisa Waktu: " . ($remaining !== null ? secondsToTime($remaining) : 'Unlimited') . "<br>";
            echo "Status: " . ($user['disabled'] == 'true' ? '<span style="color:red">Nonaktif</span>' : '<span style="color:green">Aktif</span>') . "<br>";
            echo "Data Terpakai (Bytes): " . ($user['bytes-total'] ?? '0') . "<br>";
            echo "Limit Data: " . ($user['limit-bytes-total'] ?? 'Unlimited') . "<br>";
            echo "Komentar: " . ($user['comment'] ?? '-') . "<br>";
        } else {
            echo "<span style='color:red'>Voucher tidak ditemukan.</span>";
        }
    }

    $API->disconnect();
} else {
    echo "Koneksi ke Mikrotik gagal.";
}
?>
