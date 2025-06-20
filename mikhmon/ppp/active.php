<?php
// Mengambil semua data dari /ppp/active di MikroTik
$getActive = $API->comm("/ppp/active/print");
$countActive = count($getActive);
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="fa fa-plug"></i> PPP Active Connections (<?= htmlspecialchars($countActive) ?>)
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Service</th>
                                <th>Caller ID (MAC)</th>
                                <th>IP Address</th>
                                <th>Uptime</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($countActive > 0) {
                                foreach ($getActive as $active) {
                                    $activeID = $active['.id'];
                                    $activeName = htmlspecialchars($active['name']);
                            ?>
                                    <tr>
                                        <td><?= $activeName ?></td>
                                        <td><?= htmlspecialchars($active['service']) ?></td>
                                        <td><?= isset($active['caller-id']) ? htmlspecialchars($active['caller-id']) : '' ?></td>
                                        <td><?= isset($active['address']) ? htmlspecialchars($active['address']) : '' ?></td>
                                        <td><?= isset($active['uptime']) ? htmlspecialchars($active['uptime']) : '' ?></td>
                                        <td>
                                            <!-- Tombol Disconnect sekarang aktif -->
                                            <a href="./?ppp=removeactive&id=<?= urlencode($activeID) ?>&session=<?= $session ?>" title="Disconnect User" onclick="return confirm('Are you sure you want to disconnect <?= $activeName ?>?')" class="btn btn-danger btn-sm"><i class="fa fa-times-circle"></i> Disconnect</a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No PPP active connections.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>