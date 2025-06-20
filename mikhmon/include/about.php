<?php

session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
}
?>
<style>
.iFWrapper {
	position: relative;
	padding-bottom: 56.25%; /* 16:9 */
	padding-top: 25px;
	height: 0;
}
.iFWrapper iframe {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
  height: 100%;
  border :none;
}
</style>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> About</h3>
      </div>
      <div class="card-body">
        <h3>MIKHMON V<?= $_SESSION['v']; ?></h3>
        <p>
          Aplikasi ini dipersembahkan untuk pengusaha hotspot di manapun Anda berada.
          Semoga makin sukses.
        </p>
        <p>
          <ul>
            <li>
              Author : Laksamadi Guko
            </li>
            <li>
              Licence : <a href="https://github.com/laksa19/mikhmonv2/blob/master/LICENSE">GPLv2</a>
            </li>
            <li>
              API Class : <a href="https://github.com/BenMenking/routeros-api">routeros-api</a>
            </li>
            <li>
              Website : <a href="https://laksa19.github.io">laksa19.github.io</a>
            </li>
            <li>
              Facebook : <a href="https://fb.com/laksamadi">fb.com/laksamadi</a>
            </li>
          </ul>
        </p>
        <p>
          Terima kasih untuk semua yang telah mendukung pengembangan MIKHMON.
        </p>
        <div>
            <i>Copyright © <i> 2018 Laksamadi Guko</i></i>
            <i>- MOD @ <i> Cackil 2025</i></i>
        </div>
      </div>
    </div>
  </div>

  <!-- AWAL BAGIAN YANG DIDISABLE -->
  <?php /*
  <div class="col-12">
    <div class="card">
      <div class="card-header">
      <h3><i class="fa fa-info-circle"></i> Changelog</h3>
      </div>
      <div class="card-body">
      <div class="iFWrapper">
        <iframe src="https://laksa19.github.io/mikhmonv3" ></iframe>
      </div>
      </div>
    </div>
  </div>
  */ ?>
  <!-- AKHIR BAGIAN YANG DIDISABLE -->
  
</div>