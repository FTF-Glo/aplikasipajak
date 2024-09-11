<?php
$DIR = "PATDA-V1";
$modul = "jalan/profil";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-profil.php");
$pajak = new Pajak();
$profil = new ProfilPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $profil->get_last_profil($data->uname);
$kecamatan = $pajak->get_list_kecamatan();
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery.number.js"></script>
<script type="text/javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-profil.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}" ?>/main.js"></script>

<form class="cmxform" id="form-profil" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-profil.php?param=<?php echo base64_encode($json->encode(array("a" => $profil->_a, "m" => $profil->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="PROFIL[CPM_ID]" value="<?php echo $value['CPM_ID']; ?>">
    <input type="hidden" name="PROFIL[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">

    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK PENERANGAN JALAN</b></td>
        </tr>
        <?php
        if ($value['CPM_APPROVE'] != "" && $value['CPM_APPROVE'] == 0) {
            echo "<tr><td colspan=\"2\"><div class=\"message succ\">Profil diperbaharui pada : <code>{$value['CPM_TGL_UPDATE']}</code></div></td></tr>";
        }
        ?>
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_WP'] ?>"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PROFIL[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
		<tr>
            <td>Kecamatan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" style="width: 200px;" value="<?php echo $value['CPM_KECAMATAN_WP'] ?>">
            </td>
        </tr>
        <tr>
            <td>Kelurahan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;" value="<?php echo $value['CPM_KELURAHAN_WP'] ?>">
            </td>
        </tr>			
        <tr>
            <td>NOP <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $value['CPM_NOP'] ?>" maxlength="20"></td>
        </tr>
        <tr>
            <td>Nama Penerangan Jalan <b class="isi">*</b></td>
            <td>: <input type="text" name="PROFIL[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_OP'] ?>"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Penerangan Jalan <b class="isi">*</b></td>
            <td>: <textarea name="PROFIL[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3"><?php echo $value['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
		<tr>
            <td>Kecamatan Objek Pajak <b class="isi">*</b></td>
            <td>: 
				<select name="PROFIL[CPM_KECAMATAN_OP]" id="CPM_KECAMATAN_OP" style="width: 200px;" data-kel="<?php echo $value['CPM_KELURAHAN_OP'] ?>" >
					<option></option>
					<?php
					if(count($kecamatan)>0){
						foreach($kecamatan as $kec){
							echo '<option value="'.$kec->CPM_KEC_ID.'" '.($value['CPM_KECAMATAN_OP'] == $kec->CPM_KEC_ID? 'selected' : '').'>'.$kec->CPM_KECAMATAN.'</option>';
						}
					}
					?>
				</select>            
            </td>
        </tr>
        <tr>
            <td>Kelurahan Objek Pajak <b class="isi">*</b></td>
            <td>: 
				<select name="PROFIL[CPM_KELURAHAN_OP]" id="CPM_KELURAHAN_OP" style="width: 200px;" >
					<option></option>
				</select>            
            </td>
        </tr>		
        <tr valign="top">
            <td>Golongan Penerangan Jalan <b class="isi">*</b></td>
            <td>: <select name="PROFIL[CPM_GOL_JALAN]" id="CPM_GOL_JALAN">
                    <?php
                    foreach ($value['CPM_REKENING'] as $gol) {
                        echo ($value['CPM_GOL_JALAN'] == $gol['kdrek'])? "<option value='{$gol['kdrek']}' selected>{$gol['kdrek']} - {$gol['nmrek']}</option>" : "<option value='{$gol['kdrek']}'>{$gol['kdrek']} - {$gol['nmrek']}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">
                <input type="reset" value="Reset">
                <?php
                if ($value['CPM_APPROVE'] == "" || $value['CPM_APPROVE'] == 1) {
                    echo "<input type=\"button\" id=\"btn-submit\" action=\"save\" value=\"Simpan Perubahan\">";
                } elseif ($value['CPM_APPROVE'] != "" && $value['CPM_APPROVE'] == 0) {
                    echo "<input type=\"button\" id=\"btn-submit\" action=\"update\" value=\"Simpan Perubahan\">\n";
                    echo "<input type=\"button\" id=\"btn-delete\" value=\"Batalkan Perubahan\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>

