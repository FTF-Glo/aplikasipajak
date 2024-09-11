<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$DIR = "PATDA-V1";
$modul = "pelayanan/skpdkb";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-berkas-skpdkb.php");
$berkas = new BerkasSKPDKB();
$berkas->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$DATA = $berkas->get_berkas();
$DATA['CPM_AUTHOR'] = ($DATA['CPM_AUTHOR'] == "") ? $data->uname : $DATA['CPM_AUTHOR'];
$radio_lampiran[1] = strpos($DATA['CPM_LAMPIRAN'], "1") === false ? "" : "checked";
$radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "" : "checked";
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-berkas-skpdkb.js"></script>

<form class="cmxform" id="form-berkas-skpdkb" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-berkas-skpdkb.php?param=<?php echo base64_encode($json->encode(array("a" => $berkas->_a, "m" => $berkas->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="BERKAS[CPM_ID]" value="<?php echo $DATA['CPM_ID']; ?>">
    <input type="hidden" name="BERKAS[CPM_AUTHOR]" value="<?php echo $DATA['CPM_AUTHOR']; ?>">
    <input type="hidden" name="BERKAS[CPM_PETUGAS]" value="<?php echo $DATA['CPM_AUTHOR']; ?>">
    <?php
    if ($berkas->_id != "") {
        echo "<div class=\"message-box\">";        
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> ".($berkas->_i == 1? "Berkas Masuk" : "Berkas diterima")."</div>";
        echo "</div>";
    }
    ?>
    <table class="main" width="900">               
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PENERIMAAN BERKAS PELAYANAN</b></td>
        </tr>
        <tr>
            <td width="200">Tanggal masuk Surat <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_TGL_INPUT]" id="CPM_TGL_INPUT" style="width: 150px;" value="<?php echo $DATA['CPM_TGL_INPUT'] ?>" readonly></td>
        </tr>
        <tr>
            <td>Jenis Pajak <b class="isi">*</b></td>
            <td>: 
                <select name="BERKAS[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK" >
                    <?php
                    if ($DATA['CPM_JENIS_PAJAK'] != "") {
                        echo "<option value=\"{$DATA['CPM_JENIS_PAJAK']}\" selected>{$berkas->arr_pajak[$DATA['CPM_JENIS_PAJAK']]}</option>";
                    } else {
                        foreach ($berkas->arr_pajak as $pjk_id => $pjk_name) {
                            echo "<option value=\"{$pjk_id}\">{$pjk_name}</option>";
                        }
                    }
                    ?>                    
                </select>
            </td>
        </tr>
        <tr>
            <td>No. SPTPD <b class="isi">*</b></td>
            <td>: 
                <input type="text" name="BERKAS[CPM_NO_SPTPD]" id="CPM_NO_SPTPD" style="width: 200px;" <?php echo ($berkas->_id != "") ? "readonly" : "" ?> value="<?php echo $DATA['CPM_NO_SPTPD'] ?>">
            </td>
        </tr>
        <tr>
            <td>NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['CPM_NPWPD']) ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $DATA['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="BERKAS[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="70" rows="3" readonly><?php echo $DATA['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Nama/Nomor Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="BERKAS[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="70" rows="3" readonly><?php echo $DATA['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <p><b>Lampiran : </b></p></br>
                <table width="100%" border="0" align="center" class="header">                    
                    <tr>
                        <td width="80"><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="1" <?php echo $radio_lampiran[1] ?>> SKPDKB</td>
                        <td width="80"><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="2" <?php echo $radio_lampiran[2] ?>> SKPDKBT</td>
                        <td width="100"></td>
                    </tr>                 
                </table></br>
            </td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">
                <input type="reset" value="Reset"> 
                <?php
                if ($berkas->_id == "") {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\"> ";
                } else {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui\"> ";
                    if ($berkas->_sts == 1) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_buktiterima\" value=\"Cetak Bukti Penerimaan\"> ";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_disposisi\" value=\"Cetak Disposisi\">";
                    }
                }
                ?>
            </td>
        </tr>
    </table>
</form>
