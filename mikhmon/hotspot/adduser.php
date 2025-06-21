<?php
/*
 *  Copyright (C) 2024 riswandy r.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

include('../include/config.php');
include('../include/mikrotik.php');

// Waktu: 3600 -> 1h 0m
function secondsToTime($seconds) {
  $dtF = new DateTime('@0');
  $dtT = new DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a hari, %h jam, %i menit');
}

// Format uptime mikrotik ke detik
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

// Format byte to MB/GB
function formatBytes($bytes, $precision = 2) {
  if ($bytes <= 0) return "0 B";
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $power = floor(log($bytes, 1024));
  return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
}
?>

<div class="row">
<div class="col-8">
<div class="card box-bordered">
  <div class="card-header">
    <h3><i class="fa fa-search"></i> Cek Status Voucher</h3>
  </div>
  <div class="card-body">

<form method="get" action="">
  <input type="hidden" name="hotspot" value="cek-voucher">
  <input type="hidden" name="session" value="<?= htmlspecialchars($_GET['session'] ?? '') ?>">
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
    $username = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_GET['user']);

    $API->write('/ip/hotspot/user/print', false);
    $API->write('?name=' . $username);
    $result = $API->read();

    if (!empty($result)) {
      $user = $result[0];
      $uptimeUsed = isset($user['uptime']) ? parseTimeToSeconds($user['uptime']) : 0;
      $uptimeLimit = isset($user['limit-uptime']) ? parseTimeToSeconds($user['limit-uptime']) : 0;
      $remaining = ($uptimeLimit > 0) ? max(0, $uptimeLimit - $uptimeUsed) : null;

      echo "<table class='table table-bordered'>";
      echo "<tr><th>Username</th><td><b>" . htmlspecialchars($user['name']) . "</b></td></tr>";
      echo "<tr><th>Password</th><td>" . htmlspecialchars($user['password'] ?? '-') . "</td></tr>";
      echo "<tr><th>Profile</th><td>" . htmlspecialchars($user['profile'] ?? '-') . "</td></tr>";
      echo "<tr><th>Uptime Digunakan</th><td>" . htmlspecialchars($user['uptime'] ?? '-') . "</td></tr>";
      echo "<tr><th>Limit Uptime</th><td>" . htmlspecialchars($user['limit-uptime'] ?? 'Unlimited') . "</td></tr>";
      echo "<tr><th>Sisa Waktu</th><td>" . ($remaining !== null ? secondsToTime($remaining) : 'Unlimited') . "</td></tr>";
      echo "<tr><th>Status</th><td>" . ($user['disabled'] == 'true' ? "<span class='text-danger'>Nonaktif</span>" : "<span class='text-success'>Aktif</span>") . "</td></tr>";
      echo "<tr><th>Data Terpakai</th><td>" . formatBytes($user['bytes-total'] ?? 0) . "</td></tr>";
      echo "<tr><th>Limit Data</th><td>" . (isset($user['limit-bytes-total']) ? formatBytes($user['limit-bytes-total']) : 'Unlimited') . "</td></tr>";
      echo "<tr><th>Komentar</th><td>" . htmlspecialchars($user['comment'] ?? '-') . "</td></tr>";
      echo "</table>";
    } else {
      echo "<div class='alert alert-danger'>Voucher <b>" . htmlspecialchars($username) . "</b> tidak ditemukan.</div>";
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

<div class="col-4">
<div class="card">
  <div class="card-header">
    <h3><i class="fa fa-info-circle"></i> Keterangan</h3>
  </div>
  <div class="card-body">
    <p style="padding:0px 5px;">Silakan masukkan <b>username</b> dari voucher yang ingin dicek. Data yang ditampilkan meliputi status aktif, penggunaan data, dan waktu tersisa.</p>
    <p style="padding:0px 5px;"><b>Tips:</b> Cek langsung dari sistem Mikrotik tanpa membuka tab baru.</p>
  </div>
</div>
</div>
</div>

<?php } // end session else ?>
