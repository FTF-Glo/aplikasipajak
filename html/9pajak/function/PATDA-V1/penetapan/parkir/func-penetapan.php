<?php
$DIR = "PATDA-V1";
$modul = "hotel";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/profil/class-profil.php");
$lapor = new LaporPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$DATA = $lapor->get_pajak();
$radio_gol_hotel[1] = $DATA['profil']['CPM_GOL_HOTEL'] == 1 ? "checked" : "disabled";
$radio_gol_hotel[2] = $DATA['profil']['CPM_GOL_HOTEL'] == 2 ? "checked" : "disabled";
$radio_gol_hotel[3] = $DATA['profil']['CPM_GOL_HOTEL'] == 3 ? "checked" : "disabled";
$radio_gol_hotel[4] = $DATA['profil']['CPM_GOL_HOTEL'] == 4 ? "checked" : "disabled";
$radio_gol_hotel[5] = $DATA['profil']['CPM_GOL_HOTEL'] == 5 ? "checked" : "disabled";
$radio_gol_hotel[6] = $DATA['profil']['CPM_GOL_HOTEL'] == 6 ? "checked" : "disabled";

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
    <table class="main" width="900">      
        <?php
        if ($lapor->_id != "") {
            echo "<tr><td colspan=\"2\">";
            echo "<div class=\"message succ\" style=\"width:885px;margin-button:0px;\"><b>Status : {$lapor->arr_status[$lapor->_s]}</b></div>";
            echo ($lapor->_s == 4) ? "<div class=\"message err\" style=\"width:885px;margin:auto\"><b>Ditolak karena : {$lapor->_info}</b></div>" : "";
            echo "</td></tr>";
        }
        ?>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK HOTEL</b></td>
        </tr>        
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
            <td>Nama Hotel <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Hotel <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3" readonly><?php echo $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <p><b>1. Golongan Hotel</b> <b class="isi">*</b></p></br></br>
                <table width="100%" border="0" align="center" class="header">                    
                    <tr>
                        <td width="80"><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="1" <?php echo $radio_gol_hotel[1] ?>> 01 Bintang Empat</td>
                        <td width="100"><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="6" <?php echo $radio_gol_hotel[4] ?>> 04 Bintang Satu</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="2" <?php echo $radio_gol_hotel[2] ?>> 02 Bintang Tiga</td>
                        <td><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="7" <?php echo $radio_gol_hotel[5] ?>> 05 Melati Satu</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="3" <?php echo $radio_gol_hotel[3] ?>> 03 Bintang Dua</td>
                        <td><input type="radio" name="PROFIL[CPM_GOL_HOTEL]" id="CPM_GOL_HOTEL" value="8" <?php echo $radio_gol_hotel[6] ?>> 06 Losmen/ Rumah Penginapan/ Pesanggrahan/ Hostel/ Rumah Kos.</td>
                    </tr>                    
                </table></br>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK HOTEL</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child">                    
                    <tr>
                        <th colspan="2">Data Pajak</th>
                    </tr>
                    <tr>
                        <td width="200">No Pelaporan Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_NO]" id="CPM_NO" class="number" maxlength="25" <?php echo $readonly ?> value="<?php echo $DATA['pajak']['CPM_NO'] ?>"></td>
                    </tr>
                    <tr>
                        <td width="200">Tahun Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="PAJAK[CPM_TAHUN_PAJAK]">
                                <?php
                                if ($edit) {
                                    echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                                } else {
                                    for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                        echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="PAJAK[CPM_MASA_PAJAK]">
                                <?php
                                if ($edit) {
                                    echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                                } else {
                                    foreach ($lapor->arr_bulan as $x => $y) {
                                        echo ($x == $DATA['pajak']['CPM_MASA_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="250">Pembayaran Pemakaian kamar Hotel <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
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
                        <td>Tarif Pajak</td>
                        <td> : <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['tarif']['CPM_TARIF_PAJAK'] ?>"> %
                            <i><?php echo $DATA['tarif']['CPM_PERDA'] ?></i>
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
                        <td colspan="2">Terbilang : <input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $DATA['pajak']['CPM_TERBILANG'] ?>"></td>
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
                    } elseif (in_array($lapor->_s, array(2,3))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Submit Authority\">";
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Submit Authority\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
