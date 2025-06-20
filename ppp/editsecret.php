<?php
$id_secret = $_GET['id'];
$getSecret = $API->comm("/ppp/secret/print", array("?.id" => $id_secret));
$secret = $getSecret[0];
$getProfiles = $API->comm("/ppp/profile/print");

// Ekstrak tanggal dari comment
$comment = isset($secret['comment']) ? $secret['comment'] : '';
$date_part = '';
if(preg_match('/(\d{4}-\d{2}-\d{2})/', $comment, $matches)){
    $date_part = $matches[1];
}

if(isset($_POST['save'])){
    $name = $_POST['name'];
    $password = $_POST['password'];
    $service = $_POST['service'];
    $profile = $_POST['profile'];
    $comment_post = $_POST['comment'];
    
    $params = array( ".id" => $id_secret, "name" => $name, "service" => $service, "profile" => $profile, "comment" => $comment_post);
    if(!empty($password)){ $params['password'] = $password; }
    $API->comm("/ppp/secret/set", $params);
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h4><i class="fa fa-pencil"></i> Edit PPP Secret</h4></div>
            <div class="card-body">
                <form action="" method="post">
                    <!-- ... field name, password, service, profile ... (tidak berubah) -->
                     <div class="form-group">
                        <label for="name">Name (User)</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($secret['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="text" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label for="service">Service</label>
                        <select class="form-control" id="service" name="service">
                            <option <?= ($secret['service'] == 'pppoe') ? 'selected' : '' ?>>pppoe</option><option <?= ($secret['service'] == 'any') ? 'selected' : '' ?>>any</option><option <?= ($secret['service'] == 'l2tp') ? 'selected' : '' ?>>l2tp</option><option <?= ($secret['service'] == 'ovpn') ? 'selected' : '' ?>>ovpn</option><option <?= ($secret['service'] == 'pptp') ? 'selected' : '' ?>>pptp</option><option <?= ($secret['service'] == 'sstp') ? 'selected' : '' ?>>sstp</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="profile">Profile</label>
                        <select class="form-control" id="profile" name="profile">
                            <?php foreach ($getProfiles as $prof): if($prof['name'] != "default" && $prof['name'] != "default-encryption"): ?>
                            <option <?= ($secret['profile'] == $prof['name']) ? 'selected' : '' ?>><?= htmlspecialchars($prof['name']) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <!-- Field Comment diubah menjadi Tanggal -->
                    <div class="form-group">
                        <label for="comment">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="comment-date" onchange="updateComment()" value="<?= $date_part ?>">
                        <input type="hidden" id="comment" name="comment" value="<?= htmlspecialchars($comment) ?>">
                        <small class="form-text text-muted">Akan disimpan di kolom Comment dengan format YYYY-MM-DD HH:MM:SS.</small>
                    </div>
                    <button type="submit" name="save" class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
                    <a href="./?ppp=secrets&session=<?= $session; ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                </form>
                <script>
                function updateComment() {
                    var dateVal = document.getElementById('comment-date').value;
                    if (dateVal) {
                        document.getElementById('comment').value = dateVal + " 23:59:59";
                    } else {
                        document.getElementById('comment').value = "";
                    }
                }
                </script>
            </div>
        </div>
    </div>
</div>