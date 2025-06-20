<?php
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  $session = $_GET['session'];
  include('../include/config.php');
  include('../include/readcfg.php');
  include_once('../lib/routeros_api.class.php');
  $API = new RouterosAPI();
  $API->debug = false;
  if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
    // Ambil nama interface dari parameter URL
    $interface = $_GET['iface'];

    // Pastikan nama interface valid dan tidak mengandung karakter berbahaya
    if(preg_match('/^[a-zA-Z0-9\s\-_<>\/]+$/', $interface)) {
        $getinterfacetraffic = $API->comm("/interface/monitor-traffic", array(
            "interface" => "$interface",
            "once" => "",
        ));

        $tx = $getinterfacetraffic[0]['tx-bits-per-second'];
        $rx = $getinterfacetraffic[0]['rx-bits-per-second'];

        $data = array(
            0 => array(
                'name' => 'Tx',
                'data' => $tx
            ),
            1 => array(
                'name' => 'Rx',
                'data' => $rx
            )
        );
        echo json_encode($data);
    } else {
        // Jika nama interface tidak valid, kirim data kosong
        echo json_encode([]);
    }
  }
}
?>