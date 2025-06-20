<?php
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  // get MikroTik system clock
  $getclock = $API->comm("/system/clock/print");
  $clock = $getclock[0];
  $timezone = $getclock[0]['time-zone-name'];
  $_SESSION['timezone'] = $timezone;
  date_default_timezone_set($timezone);

  // get system resource MikroTik
  $getresource = $API->comm("/system/resource/print");
  $resource = $getresource[0];

  // get routeboard info
  $getrouterboard = $API->comm("/system/routerboard/print");
  $routerboard = $getrouterboard[0];

  // get & counting hotspot users & active
  $countallusers = $API->comm("/ip/hotspot/user/print", array("count-only" => ""));
  if ($countallusers <= 1) { $uunit = "item"; } else { $uunit = "items"; }
  $counthotspotactive = $API->comm("/ip/hotspot/active/print", array("count-only" => ""));
  if ($counthotspotactive <= 1) { $hunit = "item"; } else { $hunit = "items"; }
  
  // get & counting PPP
  $countpppactive = $API->comm("/ppp/active/print", array("count-only" => ""));
  if ($countpppactive <= 1) { $punit_a = "item"; } else { $punit_a = "items"; }
  $countpppsecret = $API->comm("/ppp/secret/print", array("count-only" => ""));
  if ($countpppsecret <= 1) { $punit_s = "item"; } else { $punit_s = "items"; }
  $countpppprofile = $API->comm("/ppp/profile/print", array("count-only" => ""));
  if ($countpppprofile <= 1) { $punit_p = "item"; } else { $punit_p = "items"; }
  
  // =======================================================
  // === AWAL LOGIKA BARU UNTUK MENGHITUNG PPP DISCONNECTED ==
  // =======================================================
  $all_enabled_secrets = $API->comm("/ppp/secret/print", array("?disabled" => "no"));
  $countppp_disconnected = count($all_enabled_secrets) - $countpppactive;
  if ($countppp_disconnected < 0) {$countppp_disconnected = 0;} // Jaga-jaga jika ada anomali data
  if ($countppp_disconnected <= 1) { $punit_d = "item"; } else { $punit_d = "items"; }
  // =======================================================
  // ================ AKHIR LOGIKA BARU ====================
  // =======================================================

  if ($livereport == "disable") {
    $logh = "457px";
    $lreport = "style='display:none;'";
  } else {
    $logh = "350px";
    $lreport = "style='display:block;'";
  }

  // Logika deteksi otomatis Interface List WAN
  $interfaces_to_monitor = [];
  $interface_list_name = "WAN"; 
  $getListMembers = $API->comm("/interface/list/member/print", array("?list" => $interface_list_name));

  if (is_array($getListMembers)) {
      foreach ($getListMembers as $member) {
          $interfaces_to_monitor[] = $member['interface'];
      }
  }
}
?>
<div id="reloadHome">

    <!-- Bagian atas (Resource) -->
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
                    $_SESSION[$session.'sdate'] = $clock['date'];
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

    <div class="row">
        <div class="col-8">
            <!-- Panel Hotspot -->
            <div id="r_2" class="row">
              <div class="card">
                <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-3 col-box-6">
                        <div class="box bg-blue bmh-75">
                          <a onclick="cancelPage()" href="./?hotspot=active&session=<?= $session; ?>">
                            <h1><?= $counthotspotactive; ?><span style="font-size: 15px;"><?= $hunit; ?></span></h1>
                            <div><i class="fa fa-laptop"></i> <?= $_hotspot_active ?></div>
                          </a>
                        </div>
                      </div>
                      <div class="col-3 col-box-6">
                      <div class="box bg-green bmh-75">
                        <a onclick="cancelPage()" href="./?hotspot=users&profile=all&session=<?= $session; ?>">
                              <h1><?= $countallusers; ?><span style="font-size: 15px;"><?= $uunit; ?></span></h1>
                        <div><i class="fa fa-users"></i> <?= $_hotspot_users ?></div>
                        </a>
                      </div>
                    </div>
                    <div class="col-3 col-box-6">
                      <div class="box bg-yellow bmh-75">
                        <a onclick="cancelPage()" href="./?hotspot-user=add&session=<?= $session; ?>">
                          <div><h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_add ?></span></h1></div>
                          <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                        </a>
                      </div>
                    </div>
                    <div class="col-3 col-box-6">
                      <div class="box bg-red bmh-75">
                        <a onclick="cancelPage()" href="./?hotspot-user=generate&session=<?= $session; ?>">
                          <div><h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_generate ?></span></h1></div>
                          <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>

            <!-- Panel PPP (diperbarui) -->
            <div id="r_ppp">
                <div class="card" style="margin-top:15px;">
                    <div class="card-header"><h3><i class="fa fa-sitemap"></i> PPP</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 col-box-6">
                                <div class="box bg-blue bmh-75">
                                <a onclick="cancelPage()" href="./?ppp=active&session=<?= $session; ?>">
                                    <h1><?= $countpppactive; ?><span style="font-size: 15px;"><?= $punit_a; ?></span></h1>
                                    <div><i class="fa fa-plug"></i> PPP Active</div>
                                </a>
                                </div>
                            </div>
                            <!-- Kotak PPP Disconnected -->
                            <div class="col-3 col-box-6">
                            <div class="box bg-orange bmh-75">
                                <a onclick="cancelPage()" href="./?ppp=secrets&status=disconnected&session=<?= $session; ?>">
                                <div><h1><?= $countppp_disconnected; ?><span style="font-size: 15px;"><?= $punit_d; ?></span></h1></div>
                                <div><i class="fa fa-power-off"></i> PPP Disconnected</div>
                                </a>
                            </div>
                            </div>
                            <div class="col-3 col-box-6">
                            <div class="box bg-green bmh-75">
                                <a onclick="cancelPage()" href="./?ppp=secrets&session=<?= $session; ?>">
                                    <h1><?= $countpppsecret; ?><span style="font-size: 15px;"><?= $punit_s; ?></span></h1>
                                <div><i class="fa fa-users"></i> All PPP Users</div>
                                </a>
                            </div>
                            </div>
                            <div class="col-3 col-box-6">
                            <div class="box bg-purple bmh-75">
                                <a onclick="cancelPage()" href="./?ppp=profiles&session=<?= $session; ?>">
                                <div><h1><?= $countpppprofile; ?><span style="font-size: 15px;"><?= $punit_p; ?></span></h1></div>
                                <div><i class="fa fa-book"></i> PPP Profiles</div>
                                </a>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Traffic -->
            <div class="card" style="margin-top:15px;">
              <div class="card-header"><h3><i class="fa fa-area-chart"></i> <?= $_traffic ?> </h3></div>
              <div class="card-body">
                <?php if (!empty($interfaces_to_monitor)): ?>
                  <?php foreach ($interfaces_to_monitor as $index => $iface_name): ?>
                    <div id="trafficMonitor<?= $index ?>" style="height: 250px; margin-bottom: 20px;"></div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="alert alert-warning">Interface List named "<b><?= htmlspecialchars($interface_list_name) ?></b>" not found or has no members.</div>
                <?php endif; ?>
              </div>
            </div>
            
        </div>  
        <div class="col-4">
            <!-- Panel Live Report -->
            <div id="r_4" class="row">
              <div <?= $lreport; ?> class="box bmh-75 box-bordered">
                <div class="box-group">
                  <div class="box-group-icon"><i class="fa fa-money"></i></div>
                    <div class="box-group-area">
                      <span >
                        <div id="reloadLreport">
                          <?php 
                          if (isset($_SESSION[$session.'sdate']) && $_SESSION[$session.'sdate'] == $_SESSION[$session.'idhr']){
                            echo $_income." <br/>" . "
                          ".$_today." " . $_SESSION[$session.'totalHr'] . "vcr : " . $currency . " " . $_SESSION[$session.'dincome']. "<br/>
                          ".$_this_month." " . $_SESSION[$session.'totalBl'] . "vcr : " . $currency . " " . $_SESSION[$session.'mincome']; 
                          }else{
                            echo "<div id='loader' ><i><span> <i class='fa fa-circle-o-notch fa-spin'></i> ". $_processing." </i></div>";
                          }
                          ?>                       
                        </div>
                    </span>
                </div>
              </div>
            </div>
            </div>

            <!-- Panel Log -->
            <div id="r_3" class="row">
            <div class="card">
              <div class="card-header">
                <h3><a onclick="cancelPage()" href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log" ><i class="fa fa-align-justify"></i> <?= $_hotspot_log ?></a></h3></div>
                  <div class="card-body">
                    <div style="padding: 5px; height: <?= $logh; ?> ;" class="mr-t-10 overflow">
                      <table class="table table-sm table-bordered table-hover" style="font-size: 12px; td.padding:2px;">
                        <thead><tr><th><?= $_time ?></th><th><?= $_users ?> (IP)</th><th><?= $_messages ?></th></tr></thead>
                        <tbody><tr><td colspan="3" class="text-center"><div id="loader" ><i><i class='fa fa-circle-o-notch fa-spin'></i> <?= $_processing ?> </i></div></td></tr></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
    </div>
</div>

<script type="text/javascript">
  var interfaces = <?= json_encode($interfaces_to_monitor) ?>;
  var sessiondata = "<?= $session ?>";
  var charts = [];

  function requestTrafficData(session, iface, chartIndex) {
    $.ajax({
      url: './traffic/dashboard_traffic.php?session=' + session + '&iface=' + iface,
      datatype: "json",
      success: function(data) {
        var midata = JSON.parse(data);
        if (midata.length > 0) {
          var TX = parseInt(midata[0].data);
          var RX = parseInt(midata[1].data);
          var x = (new Date()).getTime();
          var chart = charts[chartIndex];
          var shift = chart.series[0].data.length > 19;
          chart.series[0].addPoint([x, TX], true, shift);
          chart.series[1].addPoint([x, RX], true, shift);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.error("Status: " + textStatus + " request: " + XMLHttpRequest);
        console.error("Error: " + errorThrown);
      }
    });
  }

  function createChart(container, interfaceName, index) {
    var chart = new Highcharts.Chart({
      chart: { renderTo: container, animation: Highcharts.svg, type: 'areaspline', events: { load: function() { setInterval(function() { requestTrafficData(sessiondata, interfaceName, index); }, 5000); } } },
      title: { text: 'Traffic on ' + interfaceName },
      xAxis: { type: 'datetime', tickPixelInterval: 150, maxZoom: 20 * 1000 },
      yAxis: { minPadding: 0.2, maxPadding: 0.2, title: { text: null }, labels: { formatter: function() { var bytes = this.value; var sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps']; if (bytes == 0) return '0 bps'; var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024))); return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i]; }, }, },
      series: [{ name: 'Tx', data: [] }, { name: 'Rx', data: [] }],
      tooltip: { shared: true },
      credits: { enabled: false }
    });
    return chart;
  }

  $(document).ready(function() {
    Highcharts.setOptions({ global: { useUTC: false } });
    interfaces.forEach(function(iface, index) {
      var containerId = 'trafficMonitor' + index;
      if ($('#' + containerId).length) {
        var newChart = createChart(containerId, iface, index);
        charts.push(newChart);
      }
    });
  });
</script>