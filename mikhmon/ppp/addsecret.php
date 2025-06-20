<?php
$getProfiles = $API->comm("/ppp/profile/print");

if(isset($_POST['save'])){
    $name = $_POST['name'];
    $password = $_POST['password'];
    $service = $_POST['service'];
    $profile = $_POST['profile'];
    $comment = $_POST['comment'];
    
    $API->comm("/ppp/secret/add", array(
        "name"     => $name,
        "password" => $password,
        "service"  => $service,
        "profile"  => $profile,
        "comment"  => $comment,
    ));
    
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h4><i class="fa fa-plus"></i> Add PPP Secret</h4></div>
            <div class="card-body">
                <form action="" method="post">
                    <!-- ... field name, password, service, profile ... (tidak berubah) -->
                    <div class="form-group">
                        <label for="name">Name (User)</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g., user01" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="text" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="service">Service</label>
                        <select class="form-control" id="service" name="service">
                            <option>pppoe</option><option>any</option><option>l2tp</option><option>ovpn</option><option>pptp</option><option>sstp</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="profile">Profile</label>
                        <select class="form-control" id="profile" name="profile">
                            <?php foreach ($getProfiles as $prof): if($prof['name'] != "default" && $prof['name'] != "default-encryption"): ?>
                            <option><?= htmlspecialchars($prof['name']) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <!-- Field Comment diubah menjadi Tanggal -->
                    <div class="form-group">
                        <label for="comment">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="comment-date" onchange="updateComment()">
                        <input type="hidden" id="comment" name="comment">
                        <small class="form-text text-muted">Akan disimpan di kolom Comment dengan format YYYY-MM-DD HH:MM:SS.</small>
                    </div>
                    <button type="submit" name="save" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    <a href="./?ppp=secrets&session=<?= $session; ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                </form>
                <script>
                function updateComment() {
                    var dateVal = document.getElementById('comment-date').value;
                    if (dateVal) {
                        // Format: YYYY-MM-DD 23:59:59
                        document.getElementById('comment').value = dateVal + " 23:59:59";
                    } else {
                        document.getElementById('comment').value = "";
                    }
                }
                // Set default tanggal ke 1 bulan dari sekarang
                var today = new Date();
                today.setMonth(today.getMonth() + 1);
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                document.getElementById('comment-date').value = yyyy + '-' + mm + '-' + dd;
                updateComment();
                </script>
            </div>
        </div>
    </div>
</div>