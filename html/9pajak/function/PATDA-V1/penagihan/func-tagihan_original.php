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
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
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
    <table class="main" width="900">                      
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>BULAN DAN TAHUN TAGIHAN</b></td>
        </tr>   
        <tr>
            <td width="200">Tahun <b class="isi">*</b></td>
            <td>: 
                <select name="TAGIHAN[CPM_TAHUN_STPD]" style="width:150px;">
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
            </td>
        </tr>
        <tr>
            <td width="200">Bulan <b class="isi">*</b></td>
            <td>: 
                <select name="TAGIHAN[CPM_MASA_STPD]" style="width:150px;">
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
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL</b></td>
        </tr>        
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="TAGIHAN[CPM_NPWPD]" id="CPM_NPWPD" style="width: 150px;" readonly value="<?php echo Pajak::formatNPWPD($TAGIHAN['CPM_NPWPD']); ?>"></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="TAGIHAN[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" readonly value="<?php echo $TAGIHAN['CPM_NAMA_WP']; ?>"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="TAGIHAN[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly><?php echo $TAGIHAN['CPM_ALAMAT_WP']; ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>STPD</b></td>
        </tr>   
        <tr>
            <td width="200">Nomor <b class="isi">*</b></td>
            <td> : <?php echo ($TAGIHAN['CPM_NO_STPD'] != "") ? "<input type=\"text\" name=\"TAGIHAN[CPM_NO_STPD]\" id=\"CPM_NO_STPD\" style=\"width: 250px;\" maxlength=\"25\" value=\"{$TAGIHAN['CPM_NO_STPD']}\" readonly>" : "..." ?></td>
        </tr>
        <tr>
            <td>Masa Pajak <b class="isi">*</b></td>
            <td>: 
                <select name="TAGIHAN[CPM_MASA_PAJAK]" style="width:150px;">
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
            </td>
        </tr>
        <tr valign="top">
            <td>Tahun Pajak <b class="isi">*</b></td>
            <td>: 
                <select name="TAGIHAN[CPM_TAHUN_PAJAK]" style="width:150px;">
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
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child" cellpadding="0" cellspacing="0">   
                    <tr>
                        <td colspan="2">Berdasarkan peraturan perundang - undangan yang berlaku, telah dilakukan pemeriksaan atau katerangan lain atas pelaksanaan kewajiban</td>
                    </tr>
                    <tr>
                        <td width="200">Ayat Pajak <b class="isi">*</b></td>
                        <td width="600">: <select name="TAGIHAN[CPM_AYAT_PAJAK]" id="CPM_AYAT_PAJAK" style="width:580px">
                                <?php
                                $gol_id = $TAGIHAN['CPM_AYAT_PAJAK'];
                                
                                foreach ($TAGIHAN['CPM_REKENING'] as $gol) {
									if($gol_id == "" || $gol_id == "-"){
										echo "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}'>{$gol['kdrek']} - {$gol['nmrek']}</option>";
									}else{
										echo ($gol_id == $gol['kdrek']) ? "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}' selected>{$gol['kdrek']} - {$gol['nmrek']}</option>" : "<option value='{$gol['kdrek']}' tarif='{$gol['tarif']}' harga='{$gol['harga']}' disabled>{$gol['kdrek']} - {$gol['nmrek']}</option>";
									}
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Nama Pajak <b class="isi">*</b></td>
                        <td>: <input type="text" name="TAGIHAN[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $TAGIHAN['CPM_NAMA_OP']; ?>" readonly></td>
                    </tr>
                    <tr valign="top">
                        <td>Tanggal Jatuh Tempo <b class="isi">*</b></td>
                        <td>: <input type="text" name="TAGIHAN[CPM_TGL_JATUH_TEMPO_PAJAK]" <?php echo $edit ? "" : "id=\"CPM_TGL_JATUH_TEMPO_PAJAK\"" ?> style="width: 200px;" readonly value="<?php echo $TAGIHAN['CPM_TGL_JATUH_TEMPO_PAJAK']; ?>" placeholder="Tanggal Jatuh Tempo"></td>
                    </tr>
                    <tr>
                        <td colspan="2">Dari Penelitian dan atau pemeriksaan tersebut di atas, penghitungan jumlah yang seharusnya di bayar adalah sebagai berikut :</td>
                    </tr>
                    <tr>
                        <td width="200">Pajak yang kurang dibayar <b class="isi">*</b></td>
                        <td>: <input type="text" name="TAGIHAN[CPM_KURANG_BAYAR]" id="CPM_KURANG_BAYAR" class="number SUM" style="width: 200px;" <?php echo ($TAGIHAN['CPM_KURANG_BAYAR'] == 0)? "" : "readonly-comment" ?> value="<?php echo $TAGIHAN['CPM_KURANG_BAYAR']; ?>"></td>
                    </tr>
                    <tr>
                        <td>Sanksi administrasi (2% x Pajak kurang bayar)<b class="isi">*</b></td>
                        <td>: <input type="text" name="TAGIHAN[CPM_SANKSI]" id="CPM_SANKSI" class="number" readonly style="width: 200px;" value="<?php echo $TAGIHAN['CPM_SANKSI']; ?>"></td>
                    </tr>
                    <tr valign="top">
                        <td>Jumlah yang masih harus dibayar <b class="isi">*</b></td>
                        <td>: <input type="text" name="TAGIHAN[CPM_TOTAL_PAJAK]" class="number" id="CPM_TOTAL_PAJAK" readonly style="width: 200px;" value="<?php echo $TAGIHAN['CPM_TOTAL_PAJAK']; ?>"></td>
                    </tr>                    
                </table>
            </td>
        </tr>
        <?php
        if ($tagihan->_mod == "ver" && $tagihan->_s == 2) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"TAGIHAN[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        } else if ($tagihan->_mod == "per" && $tagihan->_s == 3) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>PERSETUJUAN</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"TAGIHAN[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"TAGIHAN[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        }
        ?>
        <tr class="button-area">
            <td align="center" colspan="2">                
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
            </td>
        </tr>
    </table>
</form>
