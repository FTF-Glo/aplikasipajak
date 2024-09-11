<?php
    $data = ($nop || $idwp) ? $portlet->getData() : null;
?>
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            <div class="card w-100 mb-5" style="max-width: 30rem">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3">Daftar tagihan SPPT PBB</h5>
                    <hr>
                    <form action="" method="post" id="formSppt">
                        <div class="from-group">
                            <label for="idwp" class="font-weight-bold">ID WP</label>
                            <input type="text" class="form-control form-control-sm" id="idwp" name="idwp" value="<?= $idwp ?>">
                        </div>
                        <div class="from-group">
                            <label for="nop" class="font-weight-bold">NOP</label>
                            <input type="text" class="form-control form-control-sm" id="nop" name="nop" value="<?= $nop ?>">
                        </div>
                        <div class="from-group">
                            <label class="font-weight-bold">Tahun</label>
                            <div class="input-group input-group-sm mb-3">
                                <select class="form-control" name="tahun1" id="tahun1">
                                    <?= $portlet->getTahunFilter($tahun1) ?>
                                </select>
                                <div class="input-group-append">
                                    <span class="input-group-text">s/d</span>
                                </div>
                                <select class="form-control" name="tahun2" id="tahun2">
                                    <?= $portlet->getTahunFilter($tahun2) ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-block mt-4">
                            <div class="row">
                                <div class="col-4">
                                    <button class="btn btn-success btn-sm btn-block" type="button" name="submit" value="inquiry">Inquiry</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success btn-sm btn-block" type="button" name="submit" value="export-sppt/pdf.php" <?php if (!isset($data['rows']) || empty($data['rows'])) : ?> disabled <?php endif; ?>><img src="assets/img/printer.png" alt=""> PDF</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success btn-sm btn-block" type="button" name="submit" value="export-sppt/excel.php" <?php if (!isset($data['rows']) || empty($data['rows'])) : ?> disabled <?php endif; ?>><img src="assets/img/printer.png" alt=""> Excel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if ($nop || $idwp) : ?>
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">NO</th>
                            <th class="text-center">NAMA WP</th>
                            <th class="text-center">TAHUN PAJAK</th>
                            <th class="text-center">PBB</th>
                            <th class="text-center">DENDA (*)</th>
                            <th class="text-center">KURANG BAYAR</th>
                            <th class="text-center">STATUS BAYAR</th>
                            <th class="text-center">KODE BAYAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['rows'])) : foreach ($data['rows'] as $key => $row) : ?>
                                <tr>
                                    <td class="text-right"><?= ($key + 1) ?></td>
                                    <td class="text-left"><?= $row['WP_NAMA'] ?></td>
                                    <td class="text-center"><?= $row['SPPT_TAHUN_PAJAK'] ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['SPPT_PBB_HARUS_DIBAYAR']) ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['PBB_DENDA']) ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['TAGIHAN_PLUS_DENDA']) ?></td>
                                    <td class="text-left font-weight-bold"><?= !$row['IS_LUNAS'] ? Portlet::BELUM_LUNAS_TEXT : 'LUNAS: ' . $row['PAYMENT_PAID'] ?></td>
                                    <td class="text-center font-weight-bold"><?= !$row['IS_LUNAS'] ? $row['PAYMENT_CODE'] : 'LUNAS' ?></td>
                                </tr>

                            <?php endforeach; ?>

                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">TOTAL</td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['tagihan']) ?></td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['denda']) ?></td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['tagihan_plus_denda']) ?></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>

                        <?php else : ?>
                            <tr>
                                <th class="text-center" colspan="8">Data tidak ditemukan.</th>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($data['rows'])) : ?>
                <div class="alert alert-info">
                    <small class="d-block">*Untuk Pembayaran menggunakan Bank Lampung</small>
                    <small class="d-block">**Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</small>
                    <small class="d-block">**Per 1 Januari 2024 Denda 1% setiap bulan, maksimal 24 bulan.</small>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($data['rows'])): ?>
<script>
	window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })
</script>
<?php endif; ?>