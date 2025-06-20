<?php
$id_profile = $_GET['id'];

// Ambil data profil yang akan di-edit dari MikroTik
$getProfile = $API->comm("/ppp/profile/print", array("?.id" => $id_profile));
$profile = $getProfile[0];

// Ambil daftar IP Pool
$getPools = $API->comm("/ip/pool/print");

// Proses data jika form disubmit
if(isset($_POST['save'])){
    $name = $_POST['name'];
    $local_address = $_POST['local_address'];
    $remote_address = $_POST['remote_address'];
    $rate_limit = $_POST['rate_limit'];
    $only_one = $_POST['only_one'];
    $comment = $_POST['comment'];
    
    // Siapkan parameter
    $params = [
        ".id" => $id_profile,
        "name" => $name,
        "only-one" => $only_one,
        "local-address" => $local_address,
        "remote-address" => $remote_address,
        "rate-limit" => $rate_limit,
        "comment" => $comment,
    ];

    // Kirim perintah 'set' (update) ke MikroTik
    $API->comm("/ppp/profile/set", $params);
    
    // Redirect kembali
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fa fa-pencil"></i> Edit PPP Profile</h4>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="name">Profile Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="local_address">Local Address</label>
                        <input type="text" class="form-control" id="local_address" name="local_address" value="<?= isset($profile['local-address']) ? htmlspecialchars($profile['local-address']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="remote_address">Remote Address (IP Pool)</label>
                        <select class="form-control" id="remote_address" name="remote_address">
                            <option value="">(None)</option>
                            <?php foreach ($getPools as $pool): ?>
                            <option value="<?= htmlspecialchars($pool['name']) ?>" <?= (isset($profile['remote-address']) && $profile['remote-address'] == $pool['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pool['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rate_limit">Rate Limit [Upload/Download]</label>
                        <input type="text" class="form-control" id="rate_limit" name="rate_limit" value="<?= isset($profile['rate-limit']) ? htmlspecialchars($profile['rate-limit']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="only_one">Only One</label>
                        <select class="form-control" id="only_one" name="only_one">
                            <option value="yes" <?= ($profile['only-one'] == 'true') ? 'selected' : '' ?>>Yes</option>
                            <option value="no" <?= ($profile['only-one'] == 'false') ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <input type="text" class="form-control" id="comment" name="comment" value="<?= isset($profile['comment']) ? htmlspecialchars($profile['comment']) : '' ?>">
                    </div>
                    <button type="submit" name="save" class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
                    <a href="./?ppp=profiles&session=<?= $session; ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>