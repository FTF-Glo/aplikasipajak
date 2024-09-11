<?php
$p  = inputGet('p') ? inputGet('p') : 1;
$s  = inputGet('s');
$o  = inputGet('o') ? inputGet('o') : 'asc';
$ob = inputGet('ob') ? inputGet('ob') : 'id_ssb';

$limit = 20;

$db = db::getInstance();
$query = $db
    ->connection('gw_ssb')
    ->where('approval_status', 1, '<>')
    ->orderBy($ob, $o);
if ($s) {
    $searchQuery = '%' . $s . '%';
    $whereSubQuery = "
    (op_nomor LIKE '$searchQuery' OR
    wp_nama LIKE '$searchQuery' OR
    wp_alamat LIKE '$searchQuery' OR
    bphtb_dibayar LIKE '$searchQuery' OR
    approval_status LIKE '$searchQuery' OR
    payment_code LIKE '$searchQuery' OR
    approval_msg LIKE '$searchQuery')
    ";
    $query->where($whereSubQuery);
}
$lists = $query->arraybuilder()->paginate('ssb', $p);
$totalPages = $db->totalPages;

$counter = ($p - 1) * $limit;

?>
<div>
    <h3>Pelaporan BPHTB</h3>
    <div class="table-responsive">
        <div>
            <form action="<?= base_url() ?>" method="get" class="form-inline">
                <input type="hidden" name="page" value="<?= inputGet('page') ?>">
                <input type="hidden" name="p" value="<?= $p ?>">
                <input type="hidden" name="o" value="<?= $o ?>">
                <input type="hidden" name="ob" value="<?= $ob ?>">
                <div class="form-group ml-auto w-300">
                    <a href="<?= base_url() ?>" class="btn btn-secondary ml-auto">Reset</a>
                    <input type="text" name="s" value="<?= $s ?>" class="form-control" placeholder="Cari">
                </div>
            </form>
        </div>
        <table class="table" id="pelaporanTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th><a href="<?= makeOrderByUrl('op_nomor') ?>">NOP <?= getOrderCaret('op_nomor') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('wp_nama') ?>">WP <?= getOrderCaret('wp_nama') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('wp_alamat') ?>">Alamat <?= getOrderCaret('wp_alamat') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('bphtb_dibayar') ?>">Total <?= getOrderCaret('bphtb_dibayar') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('payment_code') ?>">Kode Bayar <?= getOrderCaret('payment_code') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('approval_status') ?>">Persetujuan <?= getOrderCaret('approval_status') ?></a></th>
                    <th><a href="<?= makeOrderByUrl('approval_msg') ?>">Keterangan <?= getOrderCaret('approval_msg') ?></a></th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lists as $key => $list) : ?>
                    <tr>
						<?php 
							$switch = $list['id_switching'];
							$concat = '[{"id":"'. $switch .'","draf":2,"axx":"YUJQSFRC","uname":""}]';
							$abc = base64_encode($concat);
						?>
                        <th><?= ($key + $counter + 1) ?></th>
                        <td><a href="#" onclick="printToPDFDraf('<?= $abc; ?>')"><?= $list['op_nomor'] ?></a></td>
                        <td><?= $list['wp_nama'] ?></td>
                        <td><?= $list['wp_alamat'] ?></td>
                        <td><?= toCurrency($list['bphtb_dibayar']) ?></td>
                        <td><?= $list['payment_code'] ?></td>
                        <td>
                            <?= ($list['approval_status'] == 2) ? 'Ditolak' : 'Belum' ?>
                        </td>
                        <td>
                            <?= $list['approval_msg'] ? $list['approval_msg'] : '-'  ?>
                        </td>
                        <td>
                            <div style="width:12rem">
                                <?php if ($list['approval_status'] == 2) : ?>
                                    <button onclick="changeStatus(<?= $list['id_ssb'] ?>)" class="btn btn-sm btn-danger">Reset</button>
                                <?php else : ?>
                                    <button onclick="approve(<?= $list['id_ssb'] ?>)" class="btn btn-sm btn-primary">Setujui</button>
                                    <button onclick="reject(<?= $list['id_ssb'] ?>)" class="btn btn-sm btn-danger">Tolak</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!count($lists)) : ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div>
            <?php echo pagination($totalPages, $p) ?>
        </div>
    </div>
</div>

<script>
function printToPDFDraf(json) {
	//window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q='+encode64(json), '_newtab');
	window.open('http://114.5.197.198:8071/view/BPHTB/notaris/svc-print-notaris-app.php?q='+json, '_newtab');
}
</script>