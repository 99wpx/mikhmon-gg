<?php
// Ambil ID dari user yang akan dihapus dari URL
$id_secret = $_GET['id'];

// Kirim perintah 'remove' ke MikroTik
$API->comm("/ppp/secret/remove", array(
    ".id" => $id_secret,
));

// Redirect kembali ke halaman daftar secrets
echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
?>