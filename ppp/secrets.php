<?php
if(isset($_GET['disable'])){
    $API->comm("/ppp/secret/set", array(".id" => $_GET['disable'], "disabled" => "yes"));
    echo "<script>window.location='" . $_SERVER['HTTP_REFERER'] . "'</script>";
}
if(isset($_GET['enable'])){
    $API->comm("/ppp/secret/set", array(".id" => $_GET['enable'], "disabled" => "no"));
    echo "<script>window.location='" . $_SERVER['HTTP_REFERER'] . "'</script>";
}

$filter_profile = isset($_GET['profile']) ? $_GET['profile'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$params = [];
$page_title = "PPP Secrets";
$final_secrets_list = [];

if ($filter_status == 'disconnected') {
    // Logika khusus untuk status 'disconnected'
    $all_enabled_secrets = $API->comm("/ppp/secret/print", array("?disabled" => "no"));
    $all_active_users = $API->comm("/ppp/active/print");

    $active_user_names = [];
    foreach ($all_active_users as $active_user) {
        $active_user_names[] = $active_user['name'];
    }

    foreach ($all_enabled_secrets as $secret) {
        if (!in_array($secret['name'], $active_user_names)) {
            // Jika user enabled tapi tidak ada di daftar aktif, masukkan ke list final
            $final_secrets_list[] = $secret;
        }
    }
    $page_title .= " | Status: <span class='text-warning' style='font-weight:600'>Disconnected</span>";
    // Jika ada filter profil juga, filter lagi list finalnya
    if(!empty($filter_profile)){
        $temp_list = [];
        foreach($final_secrets_list as $secret){
            if($secret['profile'] == $filter_profile){
                $temp_list[] = $secret;
            }
        }
        $final_secrets_list = $temp_list;
        $page_title .= ", Profile: <span class='text-primary' style='font-weight:600'>" . htmlspecialchars($filter_profile) . "</span>";
    }

} else {
    // Logika filter normal
    if (!empty($filter_profile)) {
        $params['?profile'] = $filter_profile;
        $page_title .= " | Profile: <span class='text-primary' style='font-weight:600'>" . htmlspecialchars($filter_profile) . "</span>";
    }
    $final_secrets_list = $API->comm("/ppp/secret/print", $params);
}

$countSecrets = count($final_secrets_list);

$export_url = "./?ppp=exportsecrets&session=" . $session;
if (!empty($filter_profile)) { $export_url .= "&profile=" . urlencode($filter_profile); }
if (!empty($filter_status)) { $export_url .= "&status=" . urlencode($filter_status); }
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fa fa-key"></i> <?= $page_title ?> (<?= htmlspecialchars($countSecrets) ?>)</h4>
                <div class="card-header-right">
                    <a href="<?= $export_url ?>" target="_blank" class="btn btn-info"><i class="fa fa-print"></i> Print to XLS</a>
                    <a href="./?ppp=addsecret&session=<?= $session; ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add Secret</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name (User)</th>
                                <th>Password</th>
                                <th>Service</th>
                                <th>Profile</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($countSecrets > 0) {
                                $no = 1;
                                foreach ($final_secrets_list as $secret) {
                                    $secretID = $secret['.id'];
                                    $secretName = htmlspecialchars($secret['name']);
                                    $isDisabled = ($secret['disabled'] == 'true');
                            ?>
                                    <tr class="<?=$isDisabled ? 'disabled-row' : ''?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= $secretName ?></td>
                                        <td><?= htmlspecialchars($secret['password']) ?></td>
                                        <td><?= htmlspecialchars($secret['service']) ?></td>
                                        <td><a href="./?ppp=secrets&profile=<?=isset($secret['profile']) ? urlencode($secret['profile']) : 'default'?>&session=<?=$session?>" title="Filter by this profile"><?= isset($secret['profile']) ? htmlspecialchars($secret['profile']) : '<i>(default)</i>' ?></a></td>
                                        <td><?= isset($secret['comment']) ? htmlspecialchars($secret['comment']) : '' ?></td>
                                        <td>
                                            <?php if ($isDisabled): ?>
                                                <a href="./?ppp=secrets&enable=<?= urlencode($secretID) ?>&session=<?= $session ?>" title="Enable this secret" class="btn btn-success btn-sm">Enable</a>
                                            <?php else: ?>
                                                <a href="./?ppp=secrets&disable=<?= urlencode($secretID) ?>&session=<?= $session ?>" title="Disable this secret" class="btn btn-secondary btn-sm">Disable</a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="./?ppp=editsecret&id=<?= urlencode($secretID) ?>&session=<?= $session ?>" title="Edit Secret" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                                            <a href="./?ppp=removesecret&id=<?= urlencode($secretID) ?>&session=<?= $session ?>" title="Remove Secret" onclick="return confirm('Are you sure you want to remove <?= $secretName ?>?')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center">No PPP Secrets found with current filter.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>