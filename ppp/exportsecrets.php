<?php
// Mencegah output HTML apapun sebelum header dikirim
ob_start();

// File ini dipanggil oleh index.php, jadi variabel $API dan $session sudah ada.
// Tidak perlu include file apapun di sini.

// Cek apakah ada filter profil dari URL
$filter_profile = isset($_GET['profile']) ? $_GET['profile'] : '';
$params = [];

// Jika ada filter, tambahkan ke parameter query API
if (!empty($filter_profile)) {
    $params['?profile'] = $filter_profile;
}

// Ambil semua data secret dari MikroTik sesuai filter
$getSecrets = $API->comm("/ppp/secret/print", $params);

// Tentukan nama file yang akan diunduh
$filename = "Data_Pelanggan_PPP_" . date('Y-m-d') . ".csv";
if (!empty($filter_profile)) {
    // Bersihkan nama profil agar aman digunakan sebagai nama file
    $safe_profile_name = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filter_profile);
    $filename = "Data_Pelanggan_PPP_" . $safe_profile_name . "_" . date('Y-m-d') . ".csv";
}

// Bersihkan semua output yang mungkin sudah ada (seperti HTML dari file lain)
ob_end_clean();

// Atur header HTTP untuk memberitahu browser agar mengunduh file, bukan menampilkannya
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output stream PHP untuk menulis langsung ke response
$output = fopen('php://output', 'w');

// Tulis baris header yang sudah disederhanakan ke file CSV
fputcsv($output, ['No', 'Username', 'Password', 'Profil Paket', 'Keterangan (Comment)']);

// Loop melalui setiap secret dan tulis datanya ke file CSV
if (count($getSecrets) > 0) {
    $no = 1;
    foreach ($getSecrets as $secret) {
        // Hanya user yang aktif (tidak disabled) yang akan dimasukkan ke laporan
        if ($secret['disabled'] == 'false') {
            $row = [
                $no++,
                $secret['name'],
                $secret['password'],
                isset($secret['profile']) ? $secret['profile'] : '(default)',
                isset($secret['comment']) ? $secret['comment'] : ''
            ];
            fputcsv($output, $row);
        }
    }
}

// Tutup stream dan hentikan eksekusi skrip karena ini adalah file download
fclose($output);
exit();