<?php
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  $session = $_GET['session'];
  $load = $_GET['load'];

  include('../include/lang.php');
  include('../lang/'.$langid.'.php');
  include('../include/config.php');
  include('../include/readcfg.php');
  include_once('../lib/routeros_api.class.php');
  include_once('../lib/formatbytesbites.php');
  $API = new RouterosAPI();
  $API->debug = false;

  if ($load == "sysresource") {
    $API->connect($iphost, $userhost, decrypt($passwdhost));
    $getclock = $API->comm("/system/clock/print");
    $clock = $getclock[0];
    $timezone = $getclock[0]['time-zone-name'];
    date_default_timezone_set($timezone);
    $getresource = $API->comm("/system/resource/print");
    $resource = $getresource[0];
    $getrouterboard = $API->comm("/system/routerboard/print");
    $routerboard = $getrouterboard[0];
    ?>
    <div id="r_1" class="row">
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
            <div class="box-group-icon"><i class="fa fa-calendar"></i></div>
              <div class="box-group-area">
              <span ><?= $_system_date_time ?><br>
                    <?php 
                    echo ucfirst($clock['date']) . " " . $clock['time'] . "<br>
                    ".$_uptime." : " . formatDTM($resource['uptime']);
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-info-circle"></i></div>
              <div class="box-group-area">
                <span >
                    <?php
                    echo $_board_name." : " . $resource['board-name'] . "<br/>
                    ".$_model." : " . $routerboard['model'] . "<br/>
                    Router OS : " . $resource['version'];
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
    <div class="col-4">
      <div class="box bmh-75 box-bordered">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-server"></i></div>
              <div class="box-group-area">
                <span >
                    <?php
                    echo $_cpu_load." : " . $resource['cpu-load'] . "%<br/>
                    ".$_free_memory." : " . formatBytes($resource['free-memory'], 2) . "<br/>
                    ".$_free_hdd." : " . formatBytes($resource['free-hdd-space'], 2)
                    ?>
                </span>
                </div>
              </div>
            </div>
          </div> 
      </div>
  <?php 
  } else if ($load == "hotspot") {
    $API->connect($iphost, $userhost, decrypt($passwdhost));
    $countallusers = $API->comm("/ip/hotspot/user/print", array("count-only" => ""));
    if ($countallusers <= 1) { $uunit = "item"; } else { $uunit = "items"; }
    $counthotspotactive = $API->comm("/ip/hotspot/active/print", array("count-only" => ""));
    if ($counthotspotactive <= 1) { $hunit = "item"; } else { $hunit = "items"; }
  ?>
    <div id="r_2" class="card">
        <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-3 col-box-6">
                    <div class="box bg-blue bmh-75">
                        <a href="./?hotspot=active&session=<?= $session; ?>">
                        <h1><?= $counthotspotactive; ?><span style="font-size: 15px;"><?= $hunit; ?></span></h1>
                        <div><i class="fa fa-laptop"></i> <?= $_hotspot_active ?></div>
                        </a>
                    </div>
                </div>
                <div class="col-3 col-box-6">
                    <div class="box bg-green bmh-75">
                        <a href="./?hotspot=users&profile=all&session=<?= $session; ?>">
                        <h1><?= $countallusers; ?><span style="font-size: 15px;"><?= $uunit; ?></span></h1>
                        <div><i class="fa fa-users"></i> <?= $_hotspot_users ?></div>
                        </a>
                    </div>
                </div>
                <div class="col-3 col-box-6">
                    <div class="box bg-yellow bmh-75">
                        <a href="./?hotspot-user=add&session=<?= $session; ?>">
                        <div><h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_add ?></span></h1></div>
                        <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                        </a>
                    </div>
                </div>
                <div class="col-3 col-box-6">
                    <div class="box bg-red bmh-75">
                        <a href="./?hotspot-user=generate&session=<?= $session; ?>">
                        <div><h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_generate ?></span></h1></div>
                        <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <?php 
  } else if ($load == "logs") {
    // ... (kode untuk logs tetap sama)
  } else if ($load == "ppp") {
    $API->connect($iphost, $userhost, decrypt($passwdhost));
    $countpppactive = $API->comm("/ppp/active/print", array("count-only" => ""));
    if ($countpppactive <= 1) { $punit_a = "item"; } else { $punit_a = "items"; }
    $countpppsecret = $API->comm("/ppp/secret/print", array("count-only" => ""));
    if ($countpppsecret <= 1) { $punit_s = "item"; } else { $punit_s = "items"; }
    $countpppprofile = $API->comm("/ppp/profile/print", array("count-only" => ""));
    if ($countpppprofile <= 1) { $punit_p = "item"; } else { $punit_p = "items"; }
    
    // LOGIKA BARU UNTUK MENGHITUNG PPP DISCONNECTED
    $count_enabled_secrets = $API->comm("/ppp/secret/print", array("count-only" => "", "?disabled" => "no"));
    $countppp_disconnected = $count_enabled_secrets - $countpppactive;
    if ($countppp_disconnected < 0) {$countppp_disconnected = 0;}
    if ($countppp_disconnected <= 1) { $punit_d = "item"; } else { $punit_d = "items"; }
  ?>
    <div id="r_ppp" class="card" style="margin-top:15px;">
        <div class="card-header"><h3><i class="fa fa-sitemap"></i> PPP</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-3 col-box-6">
                    <div class="box bg-blue bmh-75">
                      <a href="./?ppp=active&session=<?= $session; ?>">
                        <h1><?= $countpppactive; ?><span style="font-size: 15px;"><?= $punit_a; ?></span></h1>
                        <div><i class="fa fa-plug"></i> PPP Active</div>
                      </a>
                    </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-orange bmh-75">
                    <a href="./?ppp=secrets&status=disconnected&session=<?= $session; ?>">
                      <div><h1><?= $countppp_disconnected; ?><span style="font-size: 15px;"><?= $punit_d; ?></span></h1></div>
                      <div><i class="fa fa-power-off"></i> PPP Disconnected</div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-green bmh-75">
                    <a href="./?ppp=secrets&session=<?= $session; ?>">
                          <h1><?= $countpppsecret; ?><span style="font-size: 15px;"><?= $punit_s; ?></span></h1>
                    <div><i class="fa fa-users"></i> All PPP Users</div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-purple bmh-75">
                    <a href="./?ppp=profiles&session=<?= $session; ?>">
                      <div><h1><?= $countpppprofile; ?><span style="font-size: 15px;"><?= $punit_p; ?></span></h1></div>
                      <div><i class="fa fa-book"></i> PPP Profiles</div>
                    </a>
                  </div>
                </div>
            </div>
        </div>
    </div>
  <?php
  }
}
?>