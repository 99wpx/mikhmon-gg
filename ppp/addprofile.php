<?php
// Ambil daftar IP Pool untuk ditampilkan di dropdown Remote Address
$getPools = $API->comm("/ip/pool/print");

// Proses data jika form disubmit
if(isset($_POST['save'])){
    $name = $_POST['name'];
    $local_address = $_POST['local_address'];
    $remote_address = $_POST['remote_address'];
    $rate_limit = $_POST['rate_limit'];
    $only_one = $_POST['only_one'];
    $comment = $_POST['comment'];
    
    $params = [
        "name" => $name,
        "only-one" => $only_one,
    ];

    // Hanya tambahkan parameter jika diisi, untuk menghindari error
    if (!empty($local_address)) { $params['local-address'] = $local_address; }
    if (!empty($remote_address)) { $params['remote-address'] = $remote_address; }
    if (!empty($rate_limit)) { $params['rate-limit'] = $rate_limit; }
    if (!empty($comment)) { $params['comment'] = $comment; }

    // Kirim perintah 'add' ke MikroTik
    $API->comm("/ppp/profile/add", $params);
    
    // Redirect kembali ke halaman daftar profiles setelah berhasil
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fa fa-plus"></i> Add PPP Profile</h4>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="name">Profile Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g., PPOE-10Mbps" required>
                    </div>
                    <div class="form-group">
                        <label for="local_address">Local Address</label>
                        <input type="text" class="form-control" id="local_address" name="local_address" placeholder="IP Address of your router (e.g., 192.168.88.1)">
                    </div>
                    <div class="form-group">
                        <label for="remote_address">Remote Address (IP Pool)</label>
                        <select class="form-control" id="remote_address" name="remote_address">
                            <option value="">(None)</option>
                            <?php foreach ($getPools as $pool): ?>
                                <option value="<?= htmlspecialchars($pool['name']) ?>"><?= htmlspecialchars($pool['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rate_limit">Rate Limit [Upload/Download]</label>
                        <input type="text" class="form-control" id="rate_limit" name="rate_limit" placeholder="e.g., 1M/10M for 10 Mbps">
                    </div>
                    <div class="form-group">
                        <label for="only_one">Only One</label>
                        <select class="form-control" id="only_one" name="only_one">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <input type="text" class="form-control" id="comment" name="comment" placeholder="Description for this profile (optional)">
                    </div>
                    <button type="submit" name="save" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    <a href="./?ppp=profiles&session=<?= $session; ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>