<?php
$DIR = "PATDA-V1";
$modul = "mineral";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/profil/class-profil.php");
$lapor = new LaporPajak();
$lapor->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$DATA = $lapor->get_pajak();

$DATA['pajak']['CPM_AUTHOR'] = $DATA['pajak']['CPM_AUTHOR'] == "" ? $data->uname : $DATA['pajak']['CPM_AUTHOR'];

$edit = ($lapor->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo $DATA['profil']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo $DATA['tarif']['CPM_ID']; ?>">
    <?php
    if ($lapor->_id != "") {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$DATA['pajak']['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$lapor->arr_status[$lapor->_s]}</div>";
        echo ($lapor->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$lapor->_info}</div>" : "";
        echo ($lapor->_s == 4 && $lapor->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($DATA['pajak']['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }
    ?>
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK MINERAL NON LOGAM DAN BATUAN</b></td>
        </tr>
        <?php
        if ($DATA['profil']['CPM_ID'] == "") {
            echo "<tr><td colspan=\"2\"><div class=\"message err\">Silakan lakukan <b>pengaturan profil</b> terlebih dahulu.</div></td></tr>";
        }
        ?>
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'] ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly><?php echo $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>NOP <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NOP'] ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3" readonly><?php echo $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK MINERAL NON LOGAM DAN BATUAN</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child">                    
                    <tr>
                        <th colspan="3">Data Pajak</th>
                    </tr>
                    <tr valign="top">
                        <td width="150">No Pelaporan Pajak <b class="isi">*</b></td>
                        <td width="300"> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
                        <td width="150" rowspan="4">Keterangan :</br> 
                            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="4" cols="40" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Tipe Pajak <b class="isi">*</b></td>
                        <td> :
                            <select name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">                                
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {                                        
                                        echo "<option value='{$DATA['pajak']['CPM_TIPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TIPE_PAJAK']]}</option>";
                                    } else {
                                        foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                            echo ($x == $DATA['pajak']['CPM_TIPE_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_TIPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TIPE_PAJAK']]}</option>";
                                }
                                ?>
                            </select>
                            <label id="load-tarif"></label>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Tahun Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                                    } else {
                                        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                            echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Bulan Pajak</td>
                        <td> :
                            <select name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                                    } else {
                                        echo "<option></option>";
                                        foreach ($lapor->arr_bulan as $x => $y) {
											echo ($DATA['pajak']['CPM_MASA_PAJAK'] == $x) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_MASA_PAJAK1]"  <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>"> s.d
                             <input type="text" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>">
                        </td>
                    </tr>                 
                </table><br/>
                <table width="900" class="child">
                    <tr>
                        <th width="50">No.</th>
                        <th width="250">Kode Rekening - Golongan</th>
                        <th width="200">Volume / Tonase (m<sup>3</sup>)</th>
                        <th>Harga pasar / nilai standar (m<sup>3</sup>)</th>
                    </tr>
                    <?php
                    $xx = 1;
                    foreach ($DATA['pajak_atr'] as $pajak_atr) {
                        ?>
                        <tr>
                            <td align="center"><?php echo $xx ?>.</td>
                            <td align="center">
                                <select name="PAJAK_ATR[CPM_ATR_NAMA][]" class="CPM_ATR_NAMA" id="CPM_ATR_NAMA_<?php echo $xx ?>" onchange="javascript:getHarga(this, <?php echo $xx ?>)" idsum="<?php echo $xx ?>">                                    
                                    <?php
                                    $rek = $DATA['pajak']['CPM_REKENING'][$pajak_atr['CPM_ATR_NAMA']];
                                    if (in_array($lapor->_mod, array("pel", ""))) {
                                        if (!in_array($lapor->_i, array(1, 3, ""))) {
                                            echo "<option value='{$pajak_atr['CPM_ATR_NAMA']}' selected>{$pajak_atr['CPM_ATR_NAMA']} - {$DATA['pajak']['CPM_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                        } else {
                                            echo "<option selected></option>";
                                            foreach ($DATA['pajak']['CPM_REKENING'] as $rek) {
                                                echo ($rek['kdrek'] == $pajak_atr['CPM_ATR_NAMA']) ?
                                                        "<option value='{$rek['kdrek']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}' selected>{$rek['kdrek']} - {$rek['nmrek']}</option>" :
                                                        "<option value='{$rek['kdrek']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}'>{$rek['kdrek']} - {$DATA['pajak']['CPM_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                            }
                                        }
                                    } else {
                                        echo "<option value='{$pajak_atr['CPM_ATR_NAMA']}' selected>{$pajak_atr['CPM_ATR_NAMA']} - {$DATA['pajak']['CPM_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                    }
                                    ?>
                                </select>
                                <label id="load-harga_<?php echo $xx ?>"></label>
                            </td>
                            <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME_<?php echo $xx ?>" class="number" style="width: 150px;" value="<?php echo number_format($pajak_atr['CPM_ATR_VOLUME'], 0) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> onkeyup="javascript:getBayar(<?php echo $xx ?>)"></td>
                            <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_HARGA][]" id="CPM_ATR_HARGA_<?php echo $xx ?>" class="number" style="width: 150px;" value="<?php echo number_format($pajak_atr['CPM_ATR_HARGA'], 0) ?>" readonly></td>
                        </tr>                    
                        <?php
                        $xx++;
                    }
                    ?>
                </table>                    
                </br>                                            
                <table width="100%" border="0" align="center" class="child">  
                    <tr>
                        <td width="250">Pembayaran Objek Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="17" readonly="" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>"></td>
                    </tr>
                    <tr>
                        <td>Pembayaran Lain-lain</td>
                        <td> : <input type="text" name="PAJAK[CPM_BAYAR_LAINNYA]" id="CPM_BAYAR_LAINNYA" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_LAINNYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                    </tr>
                    <tr>
                        <td>Dasar Pengenaan Pajak (DPP)</td>
                        <td> : <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="number" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                    </tr>                    
                    <tr>
                        <td>Tarif Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['tarif']?>"> %
                        </td>
                    </tr>
                    <tr>
                        <td>Pembayaran Terutang (Tarif x DPP)</td>
                        <td> : <input type="text" name="PAJAK[CPM_BAYAR_TERUTANG]" id="CPM_BAYAR_TERUTANG" readonly class="number" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_TERUTANG'] ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="3">Terbilang : <input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $DATA['pajak']['CPM_TERBILANG'] ?>"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        } else if ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>PERSETUJUAN</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        }
        ?>
        <tr class="button-area">
            <td align="center" colspan="2">                
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"update_final\" value=\"Perbaharui dan Finalkan\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"save_final\" value=\"Simpan dan Finalkan\">";
                        }
                    } elseif ($lapor->_s == 4 && $lapor->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"new_version_final\" value=\"Simpan versi baru dan Finalkan\">";
                    } elseif (in_array($lapor->_s, array(2, 3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        #echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
