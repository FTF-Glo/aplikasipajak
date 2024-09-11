<?php
$DIR = "PATDA-V1";
$modul = "penagihan";

require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-tagihan.php");

$tagihan = new TagihanPajak();
$tagihan->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$DATA = $tagihan->get_data($tagihan->_type);

$TAGIHAN = $tagihan->get_tagihan($DATA);
$TAGIHAN['CPM_AUTHOR'] = isset($TAGIHAN['CPM_AUTHOR']) && $TAGIHAN['CPM_AUTHOR'] != "" ? $TAGIHAN['CPM_AUTHOR'] : $data->uname;
$edit = ($TAGIHAN['ACTION'] == 1) ? true : false;
$readonly = ($edit) ? "readonly" : "";

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/penagihan/func-penagihan.js"; ?>"></script>

<form class="cmxform" id="form-penagihan" method="post" action="function/<?php echo "{$DIR}" ?>/penagihan/svc-penagihan.php?param=<?php echo base64_encode($json->encode(array("a" => $tagihan->_a, "m" => $tagihan->_m, "mod" => $tagihan->_mod, "f" => $tagihan->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="TAGIHAN[CPM_ID]" value="<?php echo $TAGIHAN['CPM_ID']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_ID_PROFIL]" value="<?php echo $TAGIHAN['CPM_ID_PROFIL']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_NPWPD]" value="<?php echo $TAGIHAN['CPM_NPWPD']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_VERSION]" value="<?php echo $TAGIHAN['CPM_VERSION']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_ALAMAT_OP]" value="<?php echo $TAGIHAN['CPM_ALAMAT_OP']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_AUTHOR]" value="<?php echo $TAGIHAN['CPM_AUTHOR']; ?>">
    <input type="hidden" name="TAGIHAN[CPM_JENIS_PAJAK]" value="<?php echo $tagihan->_type ?>">
    <input type="hidden" name="type" value="<?php echo $tagihan->_type ?>">

    <?php
    if (isset($_REQUEST['flg'])) {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$TAGIHAN['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$tagihan->arr_status[$tagihan->_s]}</div>";
        echo ($tagihan->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$tagihan->_info}</div>" : "";
        echo ($tagihan->_s == 4 && $tagihan->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($TAGIHAN['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>BULAN DAN TAHUN TAGIHAN </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tahun <b class="isi">*</b></label>
                    <select name="TAGIHAN[CPM_TAHUN_STPD]" class="form-control">
                        <?php
                        if (in_array($tagihan->_mod, array("pel", ""))) {
                            if (!in_array($tagihan->_i, array(1, 3, ""))) {
                                echo "<option value='{$TAGIHAN['CPM_TAHUN_STPD']}' selected>{$TAGIHAN['CPM_TAHUN_STPD']}</option>";
                            } else {
                                for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                    echo ($th == $TAGIHAN['CPM_TAHUN_STPD']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$TAGIHAN['CPM_TAHUN_PAJAK']}' selected>{$TAGIHAN['CPM_TAHUN_PAJAK']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bulan <b class="isi">*</b></label>
                    <select name="TAGIHAN[CPM_MASA_STPD]" class="form-control">
                        <?php
                        if (in_array($tagihan->_mod, array("pel", ""))) {
                            if (!in_array($tagihan->_i, array(1, 3, ""))) {
                                echo "<option value='{$TAGIHAN['CPM_MASA_STPD']}' selected>{$tagihan->arr_bulan[$TAGIHAN['CPM_MASA_STPD']]}</option>";
                            } else {
                                foreach ($tagihan->arr_bulan as $x => $y) {
                                    echo ($x == $TAGIHAN['CPM_MASA_STPD']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$TAGIHAN['CPM_MASA_STPD']}' selected>{$tagihan->arr_bulan[$TAGIHAN['CPM_MASA_STPD']]}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>NPWPD <b class="isi">*</b></label>
                    <input type="text" name="TAGIHAN[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" readonly value="<?php echo Pajak::formatNPWPD($TAGIHAN['CPM_NPWPD']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="TAGIHAN[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control"" readonly value=" <?php echo $TAGIHAN['CPM_NAMA_WP']; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat Wajib Pajak <b class="isi">*</b></label>
                    <textarea name="TAGIHAN[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" style="min-width: 100%" rows="3" readonly><?php echo $TAGIHAN['CPM_ALAMAT_WP']; ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>STPD </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nomor <b class="isi">*</b></label>
                    <?php echo ($TAGIHAN['CPM_NO_STPD'] != "") ? "<input type=\"text\" name=\"TAGIHAN[CPM_NO_STPD]\" id=\"CPM_NO_STPD\" class=\"form-control\" maxlength=\"25\" value=\"{$TAGIHAN['CPM_NO_STPD']}\" readonly>" : "<span style=\"color:red; display: block; text-align: center; width: 100%;\">Tidak Tersedia</span>" ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <select name="TAGIHAN[CPM_MASA_PAJAK]" class="form-control">
                        <?php
                        if (in_array($tagihan->_mod, array("pel", ""))) {
                            if (!in_array($tagihan->_i, array(1, 3, ""))) {
                                echo "<option value='{$TAGIHAN['CPM_MASA_PAJAK']}' selected>{$tagihan->arr_bulan[$TAGIHAN['CPM_MASA_PAJAK']]}</option>";
                            } else {
                                foreach ($tagihan->arr_bulan as $x => $y) {
                                    echo ($x == $TAGIHAN['CPM_MASA_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$TAGIHAN['CPM_MASA_PAJAK']}' selected>{$tagihan->arr_bulan[$TAGIHAN['CPM_MASA_PAJAK']]}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tahun Pajak <b class="isi">*</b></label>
                    <select name="TAGIHAN[CPM_TAHUN_PAJAK]" class="form-control">
                        <?php
                        if (in_array($tagihan->_mod, array("pel", ""))) {
                            if (!in_array($tagihan->_i, array(1, 3, ""))) {
                                echo "<option value='{$TAGIHAN['CPM_TAHUN_PAJAK']}' selected>{$TAGIHAN['CPM_TAHUN_PAJAK']}</option>";
                            } else {
                                for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                    echo ($th == $TAGIHAN['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$TAGIHAN['CPM_TAHUN_PAJAK']}' selected>{$TAGIHAN['CPM_TAHUN_PAJAK']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="alert alert-primary">
                        Berdasarkan peraturan perundang - undangan yang berlaku, telah dilakukan pemeriksaan atau katerangan lain atas pelaksanaan kewajiban
                        <div class="row" style="margin-top: 18px">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ayat Pajak <b class="isi">*</b></label>
                                    <select name="TAGIHAN[CPM_AYAT_PAJAK]" id="CPM_AYAT_PAJAK" class="form-control">
                                        <?php
                                        $gol_id = $TAGIHAN['CPM_AYAT_PAJAK'];

                                        foreach ($TAGIHAN['CPM_REKENING'] as $gol) {
                                            if ($gol_id == "" || $gol_id == "-") {
                                                echo "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}'>{$gol['kdrek']} - {$gol['nmrek']}</option>";
                                            } else {
                                                echo ($gol_id == $gol['kdrek']) ? "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}' selected>{$gol['kdrek']} - {$gol['nmrek']}</option>" : "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}' disabled>{$gol['kdrek']} - {$gol['nmrek']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Pajak <b class="isi">*</b></label>
                                    <input type="text" name="TAGIHAN[CPM_NAMA_OP]" id="CPM_NAMA_OP" class="form-control" value="<?php echo $TAGIHAN['CPM_NAMA_OP']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Jatuh Tempo <b class="isi">*</b></label>
                                    <input type="text" name="TAGIHAN[CPM_TGL_JATUH_TEMPO_PAJAK]" <?php echo $edit ? "" : "id=\"CPM_TGL_JATUH_TEMPO_PAJAK\"" ?> class="form-control" style="width: 90%; display: inline-block" readonly value="<?php echo $TAGIHAN['CPM_TGL_JATUH_TEMPO_PAJAK']; ?>" placeholder="Tanggal Jatuh Tempo">
                                </div>
                            </div>
                        </div>
                        Dari Penelitian dan atau pemeriksaan tersebut di atas, penghitungan jumlah yang seharusnya di bayar adalah sebagai berikut :
                        <div class="row" style="margin-top: 18px">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pajak yang kurang dibayar <b class="isi">*</b></label>
                                    <input type="text" name="TAGIHAN[CPM_KURANG_BAYAR]" id="CPM_KURANG_BAYAR" class="form-control number SUM" style="width: 100%;" <?php echo ($TAGIHAN['CPM_KURANG_BAYAR'] == 0) ? "" : "readonly-comment" ?> value="<?php echo $TAGIHAN['CPM_KURANG_BAYAR']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sanksi administrasi (2% x Pajak kurang bayar)<b class="isi">*</b></label>
                                    <input type="text" name="TAGIHAN[CPM_SANKSI]" id="CPM_SANKSI" class="form-control number" readonly style="width: 100%;" value="<?php echo $TAGIHAN['CPM_SANKSI']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Jumlah yang masih harus dibayar <b class="isi">*</b></label>
                                    <input type="text" name="TAGIHAN[CPM_TOTAL_PAJAK]" class="form-control number" id="CPM_TOTAL_PAJAK" readonly style="width: 100%;" value="<?php echo $TAGIHAN['CPM_TOTAL_PAJAK']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if ($tagihan->_mod == "ver" && $tagihan->_s == 2) {
        echo "<div class=\"row\" class=\"child\">
            <div class=\"col-md-12\">
                <div class=\"lm-subtitle-md\">VERIFIKASI</div>
                <hr />
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"form-group\">
                    <label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                    <label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>    
                    <textarea name=\"TAGIHAN[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                </div>
            </div>
        </div>";
        } else if ($tagihan->_mod == "per" && $tagihan->_s == 3) {
        echo "
            <div class=\"row\" class=\"child\">
                <div class=\"col-md-12\">
                    <div class=\"lm-subtitle-md\">PERSETUJUAN</div>
                    <hr />
                </div>
            </div>
            <div class=\"row\">
                <div class=\"col-md-12\">
                    <div class=\"form-group\">
                    <label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                    <label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                    <br/>
                    <textarea name=\"TAGIHAN[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                    </div>
                </div>
            </div>";
        }
        ?>
        <div class="row button-area">
            <div class="col-md-12" align="center" colspan="2">
                <?php
                if (in_array($tagihan->_mod, array("pel", ""))) {
                    if (in_array($tagihan->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                        }
                    } elseif ($tagihan->_s == 4 && $tagihan->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">";
                    } elseif (in_array($tagihan->_s, array(2, 3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_stpd\" value=\"Cetak\">";
                    }
                } elseif ($tagihan->_mod == "ver") {
                    if ($tagihan->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($tagihan->_mod == "per") {
                    if ($tagihan->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($tagihan->_mod == "ply") {
                    if ($tagihan->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </div>
        </div>
    </div>
</form>