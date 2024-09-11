<?php
$DIR = "PATDA-V1";
$modul = "restoran";

require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/profil/class-profil.php");
require_once("function/{$DIR}/penetapan/class-penetapan.php");

$lapor = new LaporPajak();
$penetapan = new PenetapanPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$penetapan->
$DATA = $lapor->get_pajak();
$SKPDKB = $penetapan->get_pajak($DATA);
$SKPDKB['CPM_PETUGAS'] = $SKPDKB['CPM_PETUGAS'] == "" ? $data->uname : $SKPDKB['CPM_PETUGAS'];
unset($DATA['tarif']);

$edit = ($SKPDKB['ACTION'] == 1) ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/penetapan/{$modul}"; ?>/func-penetapan.js"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}" ?>/penetapan/svc-penetapan.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="SKPDKB[CPM_ID]" value="<?php echo $SKPDKB['CPM_ID']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NO_SPTPD]" value="<?php echo $SKPDKB['CPM_NO_SPTPD']; ?>">
    <input type="hidden" name="SKPDKB[CPM_PETUGAS]" value="<?php echo $SKPDKB['CPM_PETUGAS']; ?>">
    <table class="main" width="900">              
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK RESTORAN</b></td>
        </tr>        
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="SKPDKB[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($SKPDKB['CPM_NPWPD']) ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="SKPDKB[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $SKPDKB['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="SKPDKB[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly><?php echo $SKPDKB['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Nama Restoran <b class="isi">*</b></td>
            <td>: <input type="text" name="SKPDKB[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $SKPDKB['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>SKPDKB</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child" cellpadding="0" cellspacing="0">                    
                    <tr>
                        <th colspan="2">Data Pajak</th>
                    </tr>
                    <tr>
                        <td width="150">Jenis Pajak <b class="isi">*</b></td>
                        <td width="700">: 
                            <select name="SKPDKB[CPM_JENIS_PAJAK]">
                                <?php
                                echo "<option value=\"{$SKPDKB['CPM_JENIS_PAJAK']}\" selected>{$lapor->arr_pajak[$SKPDKB['CPM_JENIS_PAJAK']]}</option>";
                                ?>                                   
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="SKPDKB[CPM_MASA_PAJAK]">
                                <?php
                                echo "<option value='{$SKPDKB['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]}</option>";
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Tahun Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="SKPDKB[CPM_TAHUN_PAJAK]">
                                <?php
                                echo "<option value='{$SKPDKB['CPM_TAHUN_PAJAK']}' selected>{$SKPDKB['CPM_TAHUN_PAJAK']}</option>";
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">No. SKPDKB <b class="isi">*</b></td>
                        <td> : <input type="text" name="SKPDKB[CPM_NO_SKPDKB]" id="CPM_NO_SKPDKB" class="number" maxlength="25" <?php echo $readonly ?> value="<?php echo $SKPDKB['CPM_NO_SKPDKB'] ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" align="center" cellpadding="0" cellspacing="0" class="child">                            
                                <tr>
                                    <td align="left" colspan="2">
                                        <table cellpadding="0" width="850" cellspacing="0" >
                                            <tr>
                                                <th width="150" rowspan="2">Pemeriksaan Pajak (Rp)</th>
                                                <th width="300" colspan="2">Sanksi</th>
                                                <th width="150" rowspan="2">Penyetoran (Rp)</th>
                                                <th width="150" rowspan="2">Kekurangan Setor (Rp)</th>
                                            </tr>
                                            <tr>
                                                <th width="150">Bunga</th>
                                                <th width="150">Denda</th>
                                            </tr>
                                            <tr>
                                                <td><input type="text" name="SKPDKB[CPM_PEMERIKSAAN_PAJAK]" id="CPM_PEMERIKSAAN_PAJAK" style="text-align:right" size="20" value="<?php echo $SKPDKB['CPM_PEMERIKSAAN_PAJAK'] ?>"></td>
                                                <td><input type="text" name="SKPDKB[CPM_BUNGA]" id="CPM_BUNGA" style="text-align:right" size="20" value="<?php echo $SKPDKB['CPM_BUNGA'] ?>"></td>
                                                <td><input type="text" name="SKPDKB[CPM_DENDA]" id="CPM_DENDA" style="text-align:right" size="20" value="<?php echo $SKPDKB['CPM_DENDA'] ?>"></td>
                                                <td><input type="text" name="SKPDKB[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $SKPDKB['CPM_TOTAL_PAJAK'] ?>"></td>
                                                <td><input type="text" name="SKPDKB[CPM_KURANG_BAYAR]" id="CPM_KURANG_BAYAR" style="text-align:right" maxlength="15" size="20" value="<?php echo $SKPDKB['CPM_KURANG_BAYAR'] ?>"></td>
                                            </tr>                                            
                                            <tr>
                                                <td colspan="5">
                                                    Dengan Huruf : <input type="text" name="SKPDKB[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $SKPDKB['CPM_TERBILANG'] ?>">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>        
        <tr class="button-area">
            <td align="center" colspan="2">                
                <?php
                if ($lapor->_mod == "1") {
                    echo "<input type=\"reset\" value=\"Reset\">\n ";
                    if ($edit) {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                    } else {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                    }
                } else if ($lapor->_mod == "2") {
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdkb\" value=\"Cetak SKPDKB\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
