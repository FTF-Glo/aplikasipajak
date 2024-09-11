<?php
$DIR = "PATDA-V1";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/penetapan/class-penetapan.php");

$penetapan = new PenetapanPajak();
$penetapan->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$DATA = $penetapan->get_data($penetapan->_type);

$SKPDKB = $penetapan->get_pajak($DATA, $penetapan->_tambahan);
$SKPDKB['CPM_AUTHOR'] = $SKPDKB['CPM_AUTHOR'] == "" ? $data->uname : $SKPDKB['CPM_AUTHOR'];

$edit = ($SKPDKB['ACTION'] == 1) ? true : false;
$readonly = ($edit) ? "readonly" : "";

if (!isset($_REQUEST['flg'])) {
    $penetapan->_type = $penetapan->arr_pajak_gw_no[$penetapan->_type];
}

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/penetapan/func-penetapan.js"; ?>"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}" ?>/penetapan/svc-penetapan.php?param=<?php echo base64_encode($json->encode(array("a" => $penetapan->_a, "m" => $penetapan->_m, "mod" => $penetapan->_mod, "f" => $penetapan->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="type" value="<?php echo $penetapan->_type; ?>">
    <input type="hidden" name="SKPDKB[CPM_ID]" value="<?php echo $SKPDKB['CPM_ID']; ?>">
    <input type="hidden" name="SKPDKB[CPM_ID_PROFIL]" value="<?php echo $SKPDKB['CPM_ID_PROFIL']; ?>">
    <input type="hidden" name="SKPDKB[CPM_VERSION]" value="<?php echo $SKPDKB['CPM_VERSION']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NO_SPTPD]" value="<?php echo $SKPDKB['CPM_NO_SPTPD']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NO]" value="<?php echo $SKPDKB['CPM_NO_SPTPD']; ?>"><!--UNTUK SAVE BERKAS -->
    <input type="hidden" name="SKPDKB[CPM_AUTHOR]" value="<?php echo $SKPDKB['CPM_AUTHOR']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NAMA_OP]" value="<?php echo $SKPDKB['CPM_NAMA_OP']; ?>">
    <input type="hidden" name="SKPDKB[CPM_ALAMAT_OP]" value="<?php echo $SKPDKB['CPM_ALAMAT_OP']; ?>">
    <?php
    if (isset($_REQUEST['flg'])) {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$SKPDKB['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$penetapan->arr_status[$penetapan->_s]}</div>";
        echo ($penetapan->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$penetapan->_info}</div>" : "";
        echo ($penetapan->_s == 4 && $penetapan->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($SKPDKB['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL PAJAK </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>NPWPD <b class="isi">*</b></label>
                    <input type="text" name="SKPDKB[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo Pajak::formatNPWPD($SKPDKB['CPM_NPWPD']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="SKPDKB[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo $SKPDKB['CPM_NAMA_WP'] ?>" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat Wajib Pajak <b class="isi">*</b></label>
                    <textarea name="SKPDKB[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" style="min-width: 100%" rows="3" readonly><?php echo $SKPDKB['CPM_ALAMAT_WP'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>KURANG BAYAR </b>
            </div>
        </div>
        <div class="row" class="child">
            <div class="col-md-12">
                <div class="lm-subtitle">Data Pajak</div>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Kurang Bayar <b class="isi">*</b></label>
                    <?php
                    if ($penetapan->_i == 1) {
                        echo "<select name=\"SKPDKB[CPM_TAMBAHAN]\" class=\"form-control\" v>";
                        foreach ($penetapan->arr_kurangbayar as $a => $b) {
                            echo ($SKPDKB['CPM_TAMBAHAN'] == $a) ? "<option value='{$a}' selected>{$b}</option>" : "<option value='{$a}'>{$b}</option>";
                        }
                        echo "</select>";
                    } else {
                        echo "<input type=\"hidden\" name=\"SKPDKB[CPM_TAMBAHAN]\" id=\"CPM_TAMBAHAN\" value=\"{$SKPDKB['CPM_TAMBAHAN']}\">";
                        echo "<input type=\"text\" class=\"form-control\" value=\"{$penetapan->arr_kurangbayar[$SKPDKB['CPM_TAMBAHAN']]}\" readonly>";
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Pajak <b class="isi">*</b></label>
                    <input type="hidden" name="SKPDKB[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK" value="<?php echo $SKPDKB['CPM_JENIS_PAJAK'] ?>">
                    <input type="text" class="form-control" value="<?php echo $penetapan->arr_pajak[$SKPDKB['CPM_JENIS_PAJAK']] ?>" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <input type="hidden" name="SKPDKB[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK" value="<?php echo $SKPDKB['CPM_MASA_PAJAK'] ?>">
                    <input type="text" class="form-control" value="<?php echo isset($penetapan->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]) ? $penetapan->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']] : '-' ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tahun Pajak <b class="isi">*</b></label>
                    <input type="text" name="SKPDKB[CPM_TAHUN_PAJAK]" class="form-control" id="CPM_TAHUN_PAJAK" value="<?php echo $SKPDKB['CPM_TAHUN_PAJAK'] ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal Jatuh Tempo <b class="isi">*</b></label>
                    <input type="text" name="SKPDKB[CPM_TGL_JATUH_TEMPO]" class="form-control" style="width: 80%; display: inline-block" <?php echo $edit ? "" : "id=\"CPM_TGL_JATUH_TEMPO\"" ?> value="<?php echo $SKPDKB['CPM_TGL_JATUH_TEMPO'] ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>No. <?php echo ($penetapan->_i == 2) ? "SKPDKB" : "SKPDKBT" ?> <b class="isi">*</b></label>
                    <?php echo ($SKPDKB['CPM_NO_SKPDKB'] != "") ? "<input type=\"text\" name=\"SKPDKB[CPM_NO_SKPDKB]\" id=\"CPM_NO_SKPDKB\" class=\"form-control\" value=\"{$SKPDKB['CPM_NO_SKPDKB']}\" readonly>" : "<span style=\"color:red; display: block; text-align: center; width: 100%;\">Tidak Tersedia</span>" ?>
                </div>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <table class="table">
                        <tr>
                            <th class="lm-thead">Pemeriksaan Pajak (Rp)</th>
                            <th class="lm-thead">Sanksi Denda</th>
                            <th class="lm-thead">Penyetoran (Rp)</th>
                            <th class="lm-thead">Kekurangan Setor (Rp)</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="SKPDKB[CPM_PEMERIKSAAN_PAJAK]" id="CPM_PEMERIKSAAN_PAJAK" class="form-control number SUM" style="text-align:right; width: 100%" size="20" value="<?php echo $SKPDKB['CPM_PEMERIKSAAN_PAJAK'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                            <td><input type="text" name="SKPDKB[CPM_DENDA]" id="CPM_DENDA" class="form-control number SUM" style="text-align:right; width: 100%" size="20" value="<?php echo $SKPDKB['CPM_DENDA'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                            <td><input type="text" name="SKPDKB[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="form-control number" style="width: 100%" value="<?php echo $SKPDKB['CPM_TOTAL_PAJAK'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                            <td><input type="text" name="SKPDKB[CPM_KURANG_BAYAR]" id="CPM_KURANG_BAYAR" class="form-control" style="text-align:right; width: 100%" maxlength="15" size="20" value="<?php echo $SKPDKB['CPM_KURANG_BAYAR'] ?>" readonly></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <label>Dengan Huruf :</label>
                                <input type="text" name="SKPDKB[CPM_TERBILANG]" id="CPM_TERBILANG" readonly class="form-control" value="<?php echo $SKPDKB['CPM_TERBILANG'] ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <hr/>

        <?php
        if ($penetapan->_mod == "ver" && $penetapan->_s == 2) {
        echo "<div class=\"row\" class=\"child\">
            <div class=\"col-md-12\">
                <div class=\"lm-subtitle\">VERIFIKASI</div>
                <hr />
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"form-group\">
                    <label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                    <label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>    
                    <textarea name=\"SKPDKB[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                </div>
            </div>
        </div>";
        } else if ($penetapan->_mod == "per" && $penetapan->_s == 3) {
        echo "
            <div class=\"row\" class=\"child\">
                <div class=\"col-md-12\">
                    <div class=\"lm-subtitle\">PERSETUJUAN</div>
                    <hr />
                </div>
            </div>
            <div class=\"row\">
                <div class=\"col-md-12\">
                    <div class=\"form-group\">
                    <label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                    <label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                    <br/>
                    <textarea name=\"SKPDKB[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                    </div>
                </div>
            </div>";
        }
        ?>

        <div class="button-area">
            <div class="col-md-12" align="center">
                <?php
                if (in_array($penetapan->_mod, array("pel", ""))) {
                    if (in_array($penetapan->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                        }
                    } elseif ($penetapan->_s == 4 && $penetapan->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">";
                    } elseif (in_array($penetapan->_s, array(2, 3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdkb\" value=\"Cetak\">";
                    }
                } elseif ($penetapan->_mod == "ver") {
                    if ($penetapan->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($penetapan->_mod == "per") {
                    if ($penetapan->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($penetapan->_mod == "ply") {
                    if ($penetapan->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </div>
        </div>
    </div>
</form>