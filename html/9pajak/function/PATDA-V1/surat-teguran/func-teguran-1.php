<?php
$DIR = "PATDA-V1";
$modul = "surat-teguran";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-teguran.php");
$lapor = new Teguran();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$DATA = $lapor->get_pajak();

$DATA['pajak']['CPM_AUTHOR'] = $DATA['pajak']['CPM_AUTHOR'] == "" ? $data->uname : $DATA['pajak']['CPM_AUTHOR'];

$edit = ($lapor->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-teguran.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}"; ?>/main.js"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-teguran.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="tipe_teguran" id="tipe_teguran" value="1">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="a" id="a" value="<?php echo $lapor->_a; ?>">

    <table class="main" width="900"> 
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>SURAT TEGURAN 1</b></td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child"> 
                    <tr>
                        <td>Jenis Pajak <b class="isi">*</b></td>
                        <td> :
                            <select name="PAJAK[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK">                                
                                <?php
                                foreach ($lapor->arr_pajak as $x => $y) {
									$tbl = $lapor->arr_pajak_table[$x];
                                    echo ($x == $DATA['pajak']['CPM_JENIS_PAJAK']) ? "<option value='{$x}' data-table='{$tbl}' selected>{$y}</option>" : "<option value='{$x}' data-table='{$tbl}'>{$y}</option>";
                                }
                                ?>
                            </select>
                            <label id="load-tarif"></label>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">NPWPD <b class="isi">*</b></td>
                        <td>: <!--<input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['pajak']['CPM_NPWPD']) ?>">
                        <input type="button" value="Cari" class="button" id="btn-search-npwpd">
                        -->
							<input type="hidden" id="TBLJNSPJK">
							<select name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 250px;"></select>
                            <label id="load-search-npwpd"></label>
                        </td>
                    </tr>                    
                    <tr>
                        <td>Nama Usaha <b class="isi">*</b></td>
                        <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" readonly style="width: 200px;" value="<?php echo $DATA['pajak']['CPM_NAMA_OP'] ?>" placeholder="Nama Usaha"></td>
                    </tr>
		    <tr>
                        <td>Pemilik / Pimpinan <b class="isi">*</b></td>
                        <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" readonly style="width: 200px;" value="<?php echo $DATA['pajak']['CPM_NAMA_WP'] ?>" placeholder="Pemilik / Pemimpin"></td>
                    </tr>	
                    <tr valign="top">
                        <td>Alamat <b class="isi">*</b></td>
                        <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="70" readonly rows="3" placeholder="Alamat"><?php echo $DATA['pajak']['CPM_ALAMAT_OP'] ?></textarea></td>
                    </tr>
		    <tr>
                        <td>Kecamatan <b class="isi">*</b></td>
                        <td>: <input type="text" name="PAJAK[CPM_KECAMATAN_OP]" id="CPM_KECAMATAN_OP" readonly style="width: 200px;" value="<?php echo $DATA['pajak']['CPM_KECAMATAN_OP'] ?>" placeholder="Kecamatan"></td>
                    </tr>				
                    <tr>
                        <td colspan="2" align="center">&nbsp;</td>
                    </tr>      
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child">                    
                    <tr valign="top">
                        <td width="250">No Teguran <b class="isi">*</b></td>
                        <td width="150"> : <input type="text" name="PAJAK[CPM_NO_SURAT]" id="CPM_NO_SURAT" maxlength="25" value="<?php echo $DATA['pajak']['CPM_NO_SURAT'] ?>" placeholder="No Teguran"></td>
                        <td width="200" rowspan="9"></td>
                    </tr>                    
                    <tr>
                        <td>Tahun Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
                                <?php
                                echo "<option value=\"\"> Pilih Tahun</option>";
                                for ($th = date("Y"); $th >= date("Y")-5; $th--) {
                                    echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Bulan Pajak <b class="isi">*</b></td>
                        <td> :
                            <select name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK">                                
                                <?php
                                echo "<option value=\"\"> Pilih Bulan</option>";
                                foreach ($lapor->arr_bulan as $x => $y) {
                                    $x = str_pad($x,2,0,STR_PAD_LEFT);
                                    echo ($x == $DATA['pajak']['CPM_MASA_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="250">No SKPD <b class="isi">*</b></td>
                        <td width="150"> : <input type="text" name="PAJAK[CPM_NO_SKPD]" id="CPM_NO_SKPD" maxlength="25" value="<?php echo $DATA['pajak']['CPM_NO_SKPD'] ?>" placeholder="No SKPD"></td>
                    </tr>
                    <tr>
                        <td width="250">Tanggal SKPD <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TGL_SKPD]" id="CPM_TGL_SKPD" style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_TGL_SKPD']; ?>" placeholder="Tanggal SKPD"></td>
                    </tr>
                    <tr>
                        <td width="250">Tanggal Jatuh Tempo <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_JATUH_TEMPO]" id="CPM_JATUH_TEMPO" style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_JATUH_TEMPO']; ?>" placeholder="Tanggal Jatuh Tempo"></td>
                    </tr>
                    <tr>
                        <td>Jumlah Tunggakan</td>
                        <td> : <input type="text" name="PAJAK[CPM_JUMLAH_TUNGGAKAN]" id="CPM_JUMLAH_TUNGGAKAN" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_JUMLAH_TUNGGAKAN'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Jumlah Tunggakan"></td>
                    </tr>
                    <tr>
                        <td colspan="3">Terbilang : <input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $DATA['pajak']['CPM_TERBILANG'] ?>" placeholder="Terbilang"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">
                <?php
                if ($edit) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">
                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">
                          <input type=\"button\" class=\"btn-print\" action=\"print_teguran\" value=\"Cetak\">";
                } else {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
<div id="modalDialog"></div>
