<?php
// Ambil ID dari koneksi aktif yang akan diputus dari URL
$id_active = $_GET['id'];

// Kirim perintah 'remove' ke MikroTik
$API->comm("/ppp/active/remove", array(
    ".id" => $id_active,
));

// Redirect kembali ke halaman daftar koneksi aktif
echo "<script>window.location='./?ppp=active&session=" . $session . "'</script>";
?>