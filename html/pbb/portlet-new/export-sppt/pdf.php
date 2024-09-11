<div class="row w-100 export-sppt-area">
    <?php if ($nop || $idwp) : 
        $data = $portlet->getData(); 
        $lastIndex = ($data['numRows'] - 1); 
    ?>
        <div class="col-sm-12 d-flex justify-content-center">
            <img src="../style/default/logo.png" alt="" srcset="" style="width: 5em;">
        </div>
        <div class="col-sm-12 text-center my-4">
            <strong>INFORMASI DATA PEMBAYARAN</strong>
        </div>
        <div class="col-6">
            <table>
                <tr>
                    <td>NOP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $portlet->formatNop($data['rows'][$lastIndex]['NOP']) ?></td>
                </tr>
                <tr>
                    <td>Luas Bumi</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['OP_LUAS_BUMI'] ?> M<sup>2</sup></td>
                </tr>
                <tr>
                    <td>Luas Bangunan</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['OP_LUAS_BANGUNAN'] ?> M<sup>2</sup></td>
                </tr>
                <tr>
                    <td>Kecamatan OP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['OP_KECAMATAN'] ?></td>
                </tr>
                <tr>
                    <td>Alamat OP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['OP_ALAMAT'] ?></td>
                </tr>
                <tr>
                    <td>Nama WP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['WP_NAMA'] ?></td>
                </tr>
                <tr>
                    <td>Alamat WP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['WP_ALAMAT'] ?></td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $portlet->formatDate(date('Y-m-d')) ?></td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table>
                <tr>
                    <td>Tahun Ketetapan</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['SPPT_TAHUN_PAJAK'] ?></td>
                </tr>
                <tr>
                    <td>NJOP Bumi</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $portlet->formatRupiah($data['rows'][$lastIndex]['NJOP_BUMI_M2']) ?> / M<sup>2</sup></td>
                </tr>
                <tr>
                    <td>NJOP Bangunan</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $portlet->formatRupiah($data['rows'][$lastIndex]['NJOP_BANGUNAN_M2']) ?> / M<sup>2</sup></td>
                </tr>
                <tr>
                    <td>Kelurahan OP</td>
                    <td>
                        <span class="mx-3">:</span>
                    </td>
                    <td><?= $data['rows'][$lastIndex]['OP_KELURAHAN'] ?></td>
                </tr>
            </table>
        </div>
        <div class="col-sm-12">
            <div class="table-responsive mt-4">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">NAMA WP</th>
                            <th class="text-center">TAHUN PAJAK</th>
                            <th class="text-center">PBB</th>
                            <th class="text-center">DENDA (*)</th>
                            <th class="text-center">JATUH TEMPO</th>
                            <th class="text-center">KURANG BAYAR</th>
                            <th class="text-center">STATUS BAYAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['rows'])) : foreach ($data['rows'] as $key => $row) : ?>
                                <tr>
                                    <td class="text-left"><?= $row['WP_NAMA'] ?></td>
                                    <td class="text-center"><?= $row['SPPT_TAHUN_PAJAK'] ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['SPPT_PBB_HARUS_DIBAYAR']) ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['PBB_DENDA']) ?></td>
                                    <td class="text-center"><?= $portlet->formatDate($row['SPPT_TANGGAL_JATUH_TEMPO']) ?></td>
                                    <td class="text-right"><?= $portlet->formatRupiah($row['TAGIHAN_PLUS_DENDA']) ?></td>
                                    <td class="text-left font-weight-bold"><?= !$row['IS_LUNAS'] ? Portlet::BELUM_LUNAS_TEXT : 'LUNAS: ' . $row['PAYMENT_PAID'] ?></td>
                                </tr>

                            <?php endforeach; ?>

                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">TOTAL</td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['tagihan']) ?></td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['denda']) ?></td>
                                <td>&nbsp;</td>
                                <td class="text-right"><?= $portlet->formatRupiah($data['total']['tagihan_plus_denda']) ?></td>
                                <td>&nbsp;</td>
                            </tr>

                        <?php else : ?>
                            <tr>
                                <th class="text-center" colspan="6">Data tidak ditemukan.</th>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <table class="table table-sm table-bordered">
                <tr>
                    <td>
                        <span class="float-left">TOTAL PBB YANG BELUM DIBAYAR</span>
                        <span class="float-right"><?= $portlet->formatRupiah($data['total']['tagihan_belum_bayar']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="float-left">TOTAL DENDA (SESUAI TANGGAL CETAK)</span>
                        <span class="float-right"><?= $portlet->formatRupiah($data['total']['denda']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="float-left font-weight-bold">JUMLAH YANG HARUS DIBAYAR</span>
                        <span class="float-right font-weight-bold"><?= $portlet->formatRupiah($data['total']['tagihan_plus_denda']) ?></span>
                    </td>
                </tr>
            </table>
            <small class="d-block mb-3">*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</small>
            <table class="w-100">
                <tr>
                    <td style="width: 80%">Petugas : ......................................................</td>
                    <td style="width: 20%" class="text-center"><?= $portlet->appConfig['NAMA_KOTA_PENGESAHAN'] ?>, <?= $portlet->formatHumanDate(date('Y-m-d')) ?></td>
                </tr>
                <tr>
                    <td style="width: 80%">Keperluan : ......................................................</td>
                    <td style="width: 20%"></td>
                </tr>
                <tr>
                    <td style="width: 80%"></td>
                    <td style="width: 20%" class="text-center">
                        <span class="d-block">......................................................</span>
                        <span class="d-block">......................................................</span>
                    </td>
                </tr>
            </table>
        </div>
        <script>
            $(function() {
                window.print();
                setTimeout(window.close, 0);
            })
        </script>
    <?php endif; ?>
</div>