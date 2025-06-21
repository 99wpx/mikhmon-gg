<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
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
  exit;
}

$getclock = $API->comm("/system/clock/print");
$clock = $getclock[0];
$timezone = $getclock[0]['time-zone-name'];
$_SESSION['timezone'] = $timezone;
date_default_timezone_set($timezone);

$getresource = $API->comm("/system/resource/print");
$resource = $getresource[0];
$syshealth = $API->comm("/system/health/print")[0];
$getrouterboard = $API->comm("/system/routerboard/print");
$routerboard = $getrouterboard[0];

// --- HOTSPOT LOG ---
$getHotspotLog = $API->comm("/log/print", array("?topics" => "hotspot,info,debug"));
$hotspot_log = array_reverse($getHotspotLog);
$hotspot_logs = [];
foreach ($hotspot_log as $entry) {
    $message = $entry['message'] ?? '';
    $time = $entry['time'] ?? '';
    $user = '-';
    $ip = '-';
    // Ekstrak user dan IP jika ada
    if (preg_match('/user\s(\S+)\son\s(\d+\.\d+\.\d+\.\d+)/', $message, $matches)) {
        $user = $matches[1];
        $ip = $matches[2];
    } elseif (preg_match('/user\s(\S+)/', $message, $matches)) {
        $user = $matches[1];
    }
    $hotspot_logs[] = [
        'time' => $time,
        'user' => $user,
        'ip' => $ip,
        'message' => $message
    ];
}

// --- PPP LOG ---
$getPPPLog = $API->comm("/log/print", array("?topics" => "pppoe,ppp,info,account"));
$ppp_log = array_reverse($getPPPLog);
$ppp_logs = [];
foreach ($ppp_log as $entry) {
    $message = $entry['message'] ?? '';
    $time = $entry['time'] ?? '';
    $user = '-';
    $ip = '-';
    $status = '-';
    if (preg_match('/user\s(\S+)\son\s(\d+\.\d+\.\d+\.\d+)/', $message, $matches)) {
        $user = $matches[1];
        $ip = $matches[2];
        $status = (stripos($message, 'logged in') !== false || stripos($message, 'connected') !== false) ? 'connect' : 'disconnect';
    } elseif (preg_match('/user\s(\S+)\s.*(disconnected|logged out)/i', $message, $matches)) {
        $user = $matches[1];
        $status = 'disconnect';
    }
    $ppp_logs[] = [
        'time' => $time,
        'user' => $user,
        'ip' => $ip,
        'status' => $status,
        'message' => $message
    ];
}

// --- PPP SECRETS ---
$pppSecrets = $API->comm("/ppp/secret/print");
$countpppinactive = 0;
foreach ($pppSecrets as $secret) {
    if (isset($secret['disabled']) && $secret['disabled'] === 'true') {
        $countpppinactive++;
    }
}

// --- HOTSPOT USERS & ACTIVE ---
$countallusers = $API->comm("/ip/hotspot/user/print", array("count-only" => ""));
$uunit = ($countallusers < 2) ? "item" : "items";
$counthotspotactive = $API->comm("/ip/hotspot/active/print", array("count-only" => ""));
$hunit = ($counthotspotactive < 2) ? "item" : "items";

// --- PPP PROFILES, SECRETS, ACTIVE ---
$countprofiles = count($API->comm("/ppp/profile/print"));
$countsecrets = count($API->comm("/ppp/secret/print"));
$countpppactive = count($API->comm("/ppp/active/print"));

// --- Laporan Penjualan ---
$thisD = date("d");
$thisM = strtolower(date("M"));
$thisY = date("Y");
if (strlen($thisD) == 1) $thisD = "0" . $thisD;
$idhr = $thisM . "/" . $thisD . "/" . $thisY;
$idbl = $thisM . $thisY;

$getSRHr = $API->comm("/system/script/print", array("?source" => "$idhr"));
$getSRBl = $API->comm("/system/script/print", array("?owner" => "$idbl"));
$TotalRHr = count($getSRHr);
$TotalRBl = count($getSRBl);

$tHr = 0; $tBl = 0;
for ($i = 0; $i < $TotalRHr; $i++) {
    $parts = explode("-|-", $getSRHr[$i]['name']);
    if (isset($parts[3])) $tHr += (int)$parts[3];
}
for ($i = 0; $i < $TotalRBl; $i++) {
    $parts = explode("-|-", $getSRBl[$i]['name']);
    if (isset($parts[3])) $tBl += (int)$parts[3];
}

// Set session untuk laporan
$_SESSION[$session.'sdate'] = $clock['date'];
$_SESSION[$session.'idhr'] = $idhr;
$_SESSION[$session.'totalHr'] = $TotalRHr;
$_SESSION[$session.'dincome'] = $tHr;
$_SESSION[$session.'totalBl'] = $TotalRBl;
$_SESSION[$session.'mincome'] = $tBl;

?>
<div id="reloadHome">
  <div class="row" id="r_1">
    <div class="col-4">
      <div class="box bmh-75 box-bordered">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-calendar"></i></div>
          <div class="box-group-area">
            <span>
              <?= $_system_date_time ?><br>
              <?= ucfirst($clock['date']) . " " . $clock['time'] ?><br>
              <?= $_uptime . " : " . formatDTM($resource['uptime']) ?>
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
            <span>
              <?= $_board_name . " : " . $resource['board-name'] ?><br/>
              <?= $_model . " : " . $routerboard['model'] ?><br/>
              Router OS : <?= $resource['version'] ?>
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
            <span>
              <?= $_cpu_load . " : " . $resource['cpu-load'] . "%" ?><br/>
              <?= $_free_memory . " : " . formatBytes($resource['free-memory'], 2) ?><br/>
              <?= $_free_hdd . " : " . formatBytes($resource['free-hdd-space'], 2) ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-8">
      <div class="row">
        <div class="col-12">
          <div class="card mb-3">
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
                      <h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_add ?></span></h1>
                      <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-red bmh-75">
                    <a onclick="cancelPage()" href="./?hotspot-user=generate&session=<?= $session; ?>">
                      <h1><i class="fa fa-user-plus"></i><span style="font-size: 15px;"><?= $_generate ?></span></h1>
                      <div><i class="fa fa-user-plus"></i> <?= $_hotspot_users ?></div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card mb-3">
            <div class="card-header"><h3><i class="fa fa-wifi"></i> PPP</h3></div>
            <div class="card-body">
              <div class="row">
                <div class="col-3 col-box-6">
                  <div class="box bg-blue bmh-75">
                    <a onclick="cancelPage()" href="./?ppp=active&session=<?= $session; ?>">
                      <h1><?= $countpppactive; ?><span style="font-size: 15px;"><?= $hunit; ?></span></h1>
                      <div><i class="fa fa-laptop"></i> <?= $_ppp_active ?></div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-green bmh-75">
                    <a onclick="cancelPage()" href="./?ppp=profiles&session=<?= $session; ?>">
                      <h1><?= $countprofiles; ?><span style="font-size: 15px;"><?= $uunit; ?></span></h1>
                      <div><i class="fa fa-users"></i> <?= $_ppp_profiles ?></div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-yellow bmh-75">
                    <a onclick="cancelPage()" href="./?ppp=secrets&session=<?= $session; ?>">
                      <h1><?= $countsecrets; ?><span style="font-size: 15px;"><?= $uunit; ?></span></h1>
                      <div><i class="fa fa-user-secret"></i> <?= $_ppp_secrets ?></div>
                    </a>
                  </div>
                </div>
                <div class="col-3 col-box-6">
                  <div class="box bg-secondary bmh-75">
                    <a onclick="cancelPage()" href="./?ppp=secrets&session=<?= $session; ?>">
                      <h1><?= $countpppinactive; ?><span style="font-size: 15px;"><?= $uunit ?? ''; ?></span></h1>
                      <div><i class="fa fa-user-times"></i> PPP Disable</div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3><i class="fa fa-area-chart"></i> <?= $_traffic ?> </h3>
            </div>
            <div class="card-body">
              <?php $getinterface = $API->comm("/interface/print");
              $interface = $getinterface[$iface - 1]['name']; ?>
              <script type="text/javascript">
                var chart;
                var sessiondata = "<?= $session ?>";
                var interface = "<?= $interface ?>";
                var n = 3000;
                function requestDatta(session,iface) {
                  $.ajax({
                    url: './traffic/traffic.php?session='+session+'&iface='+iface,
                    datatype: "json",
                    success: function(data) {
                      var midata = JSON.parse(data);
                      if( midata.length > 0 ) {
                        var TX=parseInt(midata[0].data);
                        var RX=parseInt(midata[1].data);
                        var x = (new Date()).getTime();
                        shift=chart.series[0].data.length > 19;
                        chart.series[0].addPoint([x, TX], true, shift);
                        chart.series[1].addPoint([x, RX], true, shift);
                      }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                      console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown);
                    }
                  });
                }

                $(document).ready(function() {
                    Highcharts.setOptions({
                      global: {
                        useUTC: false
                      }
                    });

                    Highcharts.addEvent(Highcharts.Series, 'afterInit', function () {
                        this.symbolUnicode = {
                            circle: '●',
                            diamond: '♦',
                            square: '■',
                            triangle: '▲',
                            'triangle-down': '▼'
                        }[this.symbol] || '●';
                    });

                      chart = new Highcharts.Chart({
                      chart: {
                      renderTo: 'trafficMonitor',
                      animation: Highcharts.svg,
                      type: 'areaspline',
                      events: {
                        load: function () {
                          setInterval(function () {
                            requestDatta(sessiondata,interface);
                          }, 8000);
                        }
                      }
                    },
                    title: {
                      text: '<?= $_interface ?> ' + interface
                    },

                    xAxis: {
                      type: 'datetime',
                      tickPixelInterval: 150,
                      maxZoom: 20 * 1000,
                    },
                    yAxis: {
                        minPadding: 0.2,
                        maxPadding: 0.2,
                        title: {
                          text: null
                        },
                        labels: {
                          formatter: function () {
                            var bytes = this.value;
                            var sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
                            if (bytes == 0) return '0 bps';
                            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                            return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
                          },
                        },
                    },

                    series: [{
                      name: 'Tx',
                      data: [],
                      marker: {
                        symbol: 'circle'
                      }
                    }, {
                      name: 'Rx',
                      data: [],
                      marker: {
                        symbol: 'circle'
                      }
                    }],

                    tooltip: {
                      formatter: function () {
                        var _0x2f7f=["\x70\x6F\x69\x6E\x74\x73","\x79","\x62\x70\x73","\x6B\x62\x70\x73","\x4D\x62\x70\x73","\x47\x62\x70\x73","\x54\x62\x70\x73","\x3C\x73\x70\x61\x6E\x20\x73\x74\x79\x6C\x65\x3D\x22\x63\x6F\x6C\x6F\x72\x3A","\x63\x6F\x6C\x6F\x72","\x73\x65\x72\x69\x65\x73","\x3B\x20\x66\x6F\x6E\x74\x2D\x73\x69\x7A\x65\x3A\x20\x31\x2E\x35\x65\x6D\x3B\x22\x3E","\x73\x79\x6D\x62\x6F\x6C\x55\x6E\x69\x63\x6F\x64\x65","\x3C\x2F\x73\x70\x61\x6E\x3E\x3C\x62\x3E","\x6E\x61\x6D\x65","\x3A\x3C\x2F\x62\x3E\x20\x30\x20\x62\x70\x73","\x70\x75\x73\x68","\x6C\x6F\x67","\x66\x6C\x6F\x6F\x72","\x3A\x3C\x2F\x62\x3E\x20","\x74\x6F\x46\x69\x78\x65\x64","\x70\x6F\x77","\x20","\x65\x61\x63\x68","\x3C\x62\x3E\x4D\x69\x6B\x68\x6D\x6F\x6E\x20\x54\x72\x61\x66\x66\x69\x63\x20\x4D\x6F\x6E\x69\x74\x6F\x72\x3C\x2F\x62\x3E\x3C\x62\x72\x20\x2F\x3E\x3C\x62\x3E\x54\x69\x6D\x65\x3A\x20\x3C\x2F\x62\x3E","\x25\x48\x3A\x25\x4D\x3A\x25\x53","\x78","\x64\x61\x74\x65\x46\x6F\x72\x6D\x61\x74","\x3C\x62\x72\x20\x2F\x3E","\x20\x3C\x62\x72\x2F\x3E\x20","\x6A\x6F\x69\x6E"];var s=[];$[_0x2f7f[22]](this[_0x2f7f[0]],function(_0x3735x2,_0x3735x3){var _0x3735x4=_0x3735x3[_0x2f7f[1]];var _0x3735x5=[_0x2f7f[2],_0x2f7f[3],_0x2f7f[4],_0x2f7f[5],_0x2f7f[6]];if(_0x3735x4== 0){s[_0x2f7f[15]](_0x2f7f[7]+ this[_0x2f7f[9]][_0x2f7f[8]]+ _0x2f7f[10]+ this[_0x2f7f[9]][_0x2f7f[11]]+ _0x2f7f[12]+ this[_0x2f7f[9]][_0x2f7f[13]]+ _0x2f7f[14])};var _0x3735x2=parseInt(Math[_0x2f7f[17]](Math[_0x2f7f[16]](_0x3735x4)/ Math[_0x2f7f[16]](1024)));s[_0x2f7f[15]](_0x2f7f[7]+ this[_0x2f7f[9]][_0x2f7f[8]]+ _0x2f7f[10]+ this[_0x2f7f[9]][_0x2f7f[11]]+ _0x2f7f[12]+ this[_0x2f7f[9]][_0x2f7f[13]]+ _0x2f7f[18]+ parseFloat((_0x3735x4/ Math[_0x2f7f[20]](1024,_0x3735x2))[_0x2f7f[19]](2))+ _0x2f7f[21]+ _0x3735x5[_0x3735x2])});return _0x2f7f[23]+ Highcharts[_0x2f7f[26]](_0x2f7f[24], new Date(this[_0x2f7f[25]]))+ _0x2f7f[27]+ s[_0x2f7f[29]](_0x2f7f[28])
                      },
                      shared: true
                    });
                  });
              </script>
              <div id="trafficMonitor"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Laporan Penjualan -->
    <div class="col-md-4">
      <div id="r_4" class="row">
        <div <?= $lreport; ?> class="box bmh-75 box-bordered w-100">
          <div class="box-group">
            <div class="box-group-icon"><i class="fa fa-money"></i></div>
            <div class="box-group-area">
              <span>
                <div id="reloadLreport">
                  <?php
                  if ($_SESSION[$session.'sdate'] == $_SESSION[$session.'idhr']) {
                    echo $_income . " <br/>" .
                      $_today . " " . $_SESSION[$session.'totalHr'] . " vcr : " . $currency . " " . $_SESSION[$session.'dincome'] . "<br/>" .
                      $_this_month . " " . $_SESSION[$session.'totalBl'] . " vcr : " . $currency . " " . $_SESSION[$session.'mincome'];
                  } else {
                    echo "<div id='loader' ><i><span> <i class='fa fa-circle-o-notch fa-spin'></i> " . $_processing . " </i></div>";
                  }
                  ?>
                </div>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- LOG TABLES -->
  <div class="row mt-3">
    <!-- HOTSPOT LOG -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h3>
            <a onclick="cancelPage()" href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log">
              <i class="fa fa-align-justify"></i> <?= $_hotspot_log ?>
            </a>
          </h3>
        </div>
        <div class="card-body">
          <div style="padding: 5px; height: <?= $logh; ?>;" class="mr-t-10 overflow">
            <table class="table table-sm table-bordered table-hover" style="font-size: 12px;">
              <thead>
                <tr>
                  <th><?= $_time ?></th>
                  <th><?= $_users ?> (IP)</th>
                  <th><?= $_messages ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($hotspot_logs)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted"><?= $_no_data ?? 'No log available' ?></td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($hotspot_logs as $log): ?>
                    <tr>
                      <td><?= $log['time'] ?></td>
                      <td><?= $log['user'] ?><?= $log['ip'] ? " ({$log['ip']})" : "" ?></td>
                      <td><?= $log['message'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- PPP LOG -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h3><i class="fa fa-plug"></i> PPP Log</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive" style="font-size:12px; height: <?= $logh ?>; overflow-y:auto;">
            <table class="table table-sm table-bordered table-hover">
              <thead>
                <tr>
                  <th><?= $_time ?></th>
                  <th><?= $_users ?> (IP)</th>
                  <th>Status</th>
                  <th><?= $_messages ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($ppp_logs)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted"><?= $_no_data ?? 'No log available' ?></td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($ppp_logs as $log):
                    $color = ($log['status'] === 'connect') ? 'text-success' : 'text-danger';
                  ?>
                    <tr>
                      <td><?= $log['time'] ?></td>
                      <td><?= $log['user'] ?> (<?= $log['ip'] ?>)</td>
                      <td class="<?= $color ?>"><strong><?= ucfirst($log['status']) ?></strong></td>
                      <td><?= $log['message'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
