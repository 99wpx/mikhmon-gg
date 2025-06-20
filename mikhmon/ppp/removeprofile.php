<?php
// Ambil ID profil dari URL
$id_profile = $_GET['id'];

// Kirim perintah 'remove' ke MikroTik
$API->comm("/ppp/profile/remove", array(
    ".id" => $id_profile,
));

// Redirect kembali ke halaman daftar profiles
echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
?>