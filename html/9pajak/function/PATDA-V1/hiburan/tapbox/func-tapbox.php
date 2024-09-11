<?php
$DIR = "PATDA-V1";
$modul = "hiburan";

require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");

if(isset($_SESSION['npwpd']) && !empty($_SESSION['npwpd'])) $npwpd = $_SESSION['npwpd'];

$pajak = new Pajak();
$op = new ObjekPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS); 
$value = $op->get_last_profil((isset($npwpd)? $npwpd : ''), (isset($nop)? $nop : ''));

$list_nop = isset($npwpd)? $op->get_list_nop($npwpd) : array();

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">

<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/tapbox/func-tapbox.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<form class="cmxform" id="form-tapbox" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/tapbox/svc-tapbox.php?param=<?php echo base64_encode($json->encode(array("a" => $op->_a, "m" => $op->_m))) ?>">
	<input type="hidden" name="url" value="main.php?<?php echo $_SERVER['QUERY_STRING']?>">
    <input type="hidden" name="param" id="param" value="<?php echo $_GET['param']?>">
    <input type="hidden" name="function" id="function">
    <input type="hidden" name="PROFIL[CPM_ID]" id="CPM_ID" value="<?php echo $value['CPM_ID']?>">
    <input type="hidden" name="PROFIL[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">

    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PENGATURAN TAPBOX</b></td>
        </tr>
        
        <?php if(!empty($npwpd)):?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>: 
					<input type="text" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" readonly>
					<?php
					if(empty($_SESSION['npwpd'])):
						$prm = 'main.php?param='.base64_encode('a='.$a.'&m='.$m.'&f='.$f);
					?>
						<input type="button" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm?>'">
					<?php endif;?>
				</td>
			</tr>
		<?php else:?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>: 
					<input type="hidden" id="TBLJNSPJK" value="RESTORAN">
					<select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
					<label id="loading"></label>
				</td>
			</tr>
		<?php endif;?>
        
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly placeholder="Alamat Wajib Pajak"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        
        <tr>
			<td>NOP <b class="isi">*</b></td>
			<td>: 
			
			<?php if(empty($value['pajak']['CPM_ID'])):?>
				<select name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width:200px;" onchange="javascript:selectOP()">
                    <?php
                    if(count($list_nop) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
                    else echo (empty($nop))? "<option value='' selected disabled>Pilih NOP</option>" : ""; 
                    
                    foreach($list_nop as $list){
						echo "<option value='{$list['CPM_NOP']}'>{$list['CPM_NOP']}</option>";
					}
					
                    ?>
                </select>
                
                <?php
				if(count($list_nop) == 0 && !empty($npwpd)):
					$ff = str_replace('Tapbox','Lapor',$f);
					$addOp = substr($ff,0,strlen($ff)-1).'OP'.substr($ff,-1);
					$prm = 'main.php?param='.base64_encode('a='.$a.'&m='.$m.'&f='.$addOp.'&npwpd='.$npwpd.'&nop=').'#CPM_TELEPON_WP';
					?>
					<input type="button" value="Tambah NOP" onclick="location.href='<?php echo $prm?>'">
				<?php endif;?>
                
			<?php else:?>
				<input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $value['CPM_NOP'] ?>" readonly>
			<?php endif;?>
			</td>
		</tr>
		
		<tr>
            <td>Nama Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Objek Pajak"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3" readonly placeholder="Alamat Objek Pajak"><?php echo $value['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <td>ID Device <sub> separator semilocon [;]</sub> <b class="isi">*</b></td>
            <td>: <textarea name="PROFIL[CPM_DEVICE_ID]" id="CPM_DEVICE_ID" cols="80" rows="3" placeholder="Device01;Device02;Device03; dst..."><?php echo $value['CPM_DEVICE_ID'] ?></textarea></td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">
                <input type="reset" value="Reset">
                <input type="button" id="btn-submit" action="update_tapbox" value="Simpan Perubahan">
            </td>
        </tr>
    </table>
</form>
