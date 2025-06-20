<?php
// Ambil semua data profil
$allProfiles = $API->comm("/ppp/profile/print");
// Ambil semua scheduler
$allSchedulers = $API->comm("/system/scheduler/print");

// Cek apakah address list 'ISOLIR-LIST' sudah ada
$checkAddrList = $API->comm("/ip/firewall/address-list/print", array("?list" => "ISOLIR-LIST", "count-only" => ""));
$isolirListExists = ($checkAddrList > 0);

// Cek apakah aturan firewall untuk drop ISOLIR-LIST sudah ada
// Kita akan mengidentifikasinya berdasarkan komentar
$checkFirewallRule = $API->comm("/ip/firewall/filter/print", array("?comment" => "DROP-ISOLIR-MIKHMON", "count-only" => ""));
$dropRuleExists = ($checkFirewallRule > 0);


$profilesToIgnore = ['default', 'default-encryption'];
$filteredProfiles = [];
foreach ($allProfiles as $profile) {
    if (!in_array($profile['name'], $profilesToIgnore)) {
        $scheduler_exists = false;
        $scheduler_id = "";
        foreach($allSchedulers as $sched){
            if($sched['name'] == "ISOLIR-" . $profile['name']){
                $scheduler_exists = true;
                $scheduler_id = $sched['.id'];
                break;
            }
        }
        $profile['scheduler_exists'] = $scheduler_exists;
        $profile['scheduler_id'] = $scheduler_id;
        $filteredProfiles[] = $profile;
    }
}
$countProfiles = count($filteredProfiles);

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fa fa-book"></i> PPP Profiles (<?= htmlspecialchars($countProfiles) ?>)</h4>
                <div class="card-header-right">
                    <!-- Tombol untuk membuat Address List -->
                    <?php if(!$isolirListExists): ?>
                    <a href="./?ppp=managescheduler&action=create_list&session=<?=$session?>" class="btn btn-danger" onclick="return confirm('This will create a new Firewall Address List named ISOLIR-LIST. Continue?')"><i class="fa fa-list"></i> Create Isolir List</a>
                    <?php endif; ?>

                    <!-- ================== TOMBOL BARU DITAMBAHKAN DI SINI ================== -->
                    <!-- Tombol hanya muncul jika Address List sudah ada TAPI rule drop belum ada -->
                    <?php if($isolirListExists && !$dropRuleExists): ?>
                    <a href="./?ppp=managescheduler&action=create_drop_rule&session=<?=$session?>" class="btn btn-danger" onclick="return confirm('This will create a Firewall Rule to DROP internet access for ISOLIR-LIST. Continue?')"><i class="fa fa-ban"></i> Create Drop Rule</a>
                    <?php endif; ?>
                    <!-- ====================================================================== -->
                    
                    <a href="./?ppp=addprofile&session=<?= $session ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add Profile</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-hover">
                        <!-- ... Isi tabel tidak berubah ... -->
                        <thead>
                            <tr>
                                <th>Profile Name</th>
                                <th>Rate Limit</th>
                                <th>Remote Address (Pool)</th>
                                <th>Isolir Scheduler</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($countProfiles > 0) {
                                foreach ($filteredProfiles as $profile) {
                                    $profileID = $profile['.id'];
                                    $profileName = htmlspecialchars($profile['name']);
                            ?>
                                    <tr>
                                        <td><a href="./?ppp=secrets&profile=<?= urlencode($profileName) ?>&session=<?= $session ?>" title="View users in this profile"><?= $profileName ?></a></td>
                                        <td><?= isset($profile['rate-limit']) ? htmlspecialchars($profile['rate-limit']) : '<i>(unlimited)</i>'; ?></td>
                                        <td><?= isset($profile['remote-address']) ? htmlspecialchars($profile['remote-address']) : '<i>(not set)</i>'; ?></td>
                                        <td>
                                            <?php if($profile['scheduler_exists']): ?>
                                                <span class="text-success"><i class="fa fa-check"></i> Active</span>
                                                <a href="./?ppp=managescheduler&action=remove&name=<?=urlencode($profileName)?>&id=<?=urlencode($profile['scheduler_id'])?>&session=<?=$session?>" class="btn btn-outline-danger btn-sm ml-2" title="Remove Scheduler" onclick="return confirm('Are you sure you want to remove the isolir scheduler for this profile?')"><i class="fa fa-times"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted">Not Set</span>
                                                <a href="./?ppp=managescheduler&action=create&name=<?=urlencode($profileName)?>&session=<?=$session?>" class="btn btn-outline-primary btn-sm ml-2" title="Create Scheduler" onclick="return confirm('This will create an isolir scheduler for profile <?= $profileName ?>. Continue?')"><i class="fa fa-plus"></i> Create</a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="./?ppp=editprofile&id=<?= urlencode($profileID) ?>&session=<?= $session ?>" title="Edit Profile" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                                            <a href="./?ppp=removeprofile&id=<?= urlencode($profileID) ?>&session=<?= $session ?>" title="Remove Profile" onclick="return confirm('Are you sure you want to remove profile <?= $profileName ?>?')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center">No manageable PPP Profiles found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <?php if(!$isolirListExists): ?>
                    <div class="alert alert-danger"><b>Penting:</b> Firewall Address List <b>"ISOLIR-LIST"</b> tidak ditemukan. Silakan klik tombol "Create Isolir List" di atas.</div>
                    <?php else: ?>
                    <div class="alert alert-info"><b>Status Address List:</b> <b>"ISOLIR-LIST"</b> <span class="text-success">ditemukan</span>.</div>
                    <?php endif; ?>
                    
                    <!-- ================== PESAN STATUS BARU DITAMBAHKAN DI SINI ================== -->
                    <?php if($isolirListExists): ?>
                        <?php if(!$dropRuleExists): ?>
                        <div class="alert alert-danger"><b>Penting:</b> Firewall Rule untuk memblokir <b>ISOLIR-LIST</b> tidak ditemukan. Silakan klik tombol "Create Drop Rule" di atas.</div>
                        <?php else: ?>
                        <div class="alert alert-info"><b>Status Firewall Rule:</b> Aturan Drop untuk <b>"ISOLIR-LIST"</b> <span class="text-success">ditemukan</span>.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <!-- =========================================================================== -->
                </div>
            </div>
        </div>
    </div>
</div>