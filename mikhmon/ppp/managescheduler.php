<?php
$action = $_GET['action'];

if($action == "create"){
    $profile_name = $_GET['name'];
    $scheduler_name = "ISOLIR-" . $profile_name;
    
    $on_event_script = <<<SCRIPT
:local dateint do={:local montharray ( "01","02","03","04","05","06","07","08","09","10","11","12" );:local days [ :pick \$d 8 10 ];:local month [ :pick \$d 5 7 ];:local year [ :pick \$d 0 4 ];:local monthint ([ :find \$montharray \$month]);:local month (\$monthint + 1);:if ( [len \$month] = 1) do={:local zero ("0");:return [:tonum ("\$year\$zero\$month\$days")];} else={:return [:tonum ("\$year\$month\$days")];}}; :local timeint do={:local hours [ :pick \$t 0 2 ];:local minutes [ :pick \$t 3 5 ];:return (\$hours * 60 + \$minutes) ;}; :local date [ /system clock get date ];:local time [ /system clock get time ];:local today [\$dateint d=\$date] ;:local curtime [\$timeint t=\$time] ; :foreach i in [ /ppp secret find where profile="$profile_name" ] do={:local comment [ /ppp secret get \$i comment];:local name [ /ppp secret get \$i name];:local gettime [:pic \$comment 11 19];:if ([:pic \$comment 4] = "-" and [:pic \$comment 7] = "-") do={ :local expd [\$dateint d=\$comment] ;:local expt [\$timeint t=\$gettime] ;:if ((\$expd < \$today and \$expt < \$curtime) or (\$expd < \$today and \$expt > \$curtime) or (\$expd = \$today and \$expt < \$curtime)) do={:local remoteAddr [ /ppp secret get \$i remote-address ];:if (\$remoteAddr != "0.0.0.0" && \$remoteAddr != "") do={ /ip firewall address-list add list=ISOLIR-LIST address=\$remoteAddr comment=\$name;}}}}
SCRIPT;

    $API->comm("/system/scheduler/add", array(
        "name" => $scheduler_name,
        "start-time" => "23:59:59",
        "interval" => "1d",
        "policy" => "read,write",
        "on-event" => $on_event_script
    ));

} elseif($action == "remove"){
    $scheduler_id = $_GET['id'];
    $API->comm("/system/scheduler/remove", array(
        ".id" => $scheduler_id
    ));

} elseif($action == "create_list"){
    $API->comm("/ip/firewall/address-list/add", array(
        "list" => "ISOLIR-LIST",
        "comment" => "Dibuat oleh Mikhmon untuk Isolir Pelanggan"
    ));

// ================== LOGIKA BARU DITAMBAHKAN DI SINI ==================
} elseif($action == "create_drop_rule"){
    // Kirim perintah untuk membuat aturan Firewall Filter
    $API->comm("/ip/firewall/filter/add", array(
        "chain" => "forward",
        "action" => "drop",
        "src-address-list" => "ISOLIR-LIST",
        "comment" => "DROP-ISOLIR-MIKHMON" // Penanda unik
    ));
    // Kita juga bisa mencoba memindahkan aturan ini ke posisi paling atas
    // $findRule = $API->comm("/ip/firewall/filter/print", array("?comment" => "DROP-ISOLIR-MIKHMON"));
    // if(!empty($findRule)){
    //    $API->comm("/ip/firewall/filter/move", array("numbers" => $findRule[0]['.id'], "destination" => "0"));
    // }
// ======================================================================

}

// Redirect kembali ke halaman daftar profiles
echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";

?>