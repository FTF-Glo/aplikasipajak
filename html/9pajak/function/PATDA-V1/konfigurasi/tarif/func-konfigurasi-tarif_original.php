<?php
$DIR = "PATDA-V1";
$modul = "konfigurasi/tarif";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");

$pajak = new Pajak();
$rekening = $pajak->jenis_rekening();
// print_r($rekening); exit;

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<form class="cmxform" id="form-conf" method="post">
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>Tambah Rekening</b></td>
		</tr>
		<tr>
            <td>Jenis Pajak <b class="isi">*</b></td>
            <td>: <select name="REK[nmheader3]" id="nmheader3">				
					<?php
					if(count($rekening)>0){
					
							echo '<option value="">Pilih jenis rekening</option>';
							foreach($rekening as $rekening){
									echo $rekening;
							}
						
					}
					?>
				</select>
			</td>
        </tr>
        <tr>
            <td width="200">Kode Rekening <b class="isi">*</b></td>
            <td>: <input type="text" name="REK[kdrek]" id="kdrek" readonly = "true" style="width: 200px;"> <input type="hidden" name="REK[nama]" id="nama" readonly = "true" style="width: 200px;"></td>
        </tr>
        <tr>
            <td>Nama Rekening <b class="isi">*</b></td>
            <td>: <input type="text" name="REK[nmrek]" id="nmrek"  style="width: 300px;"></td>
        </tr>
        
		<tr>
            <td>Tarif Pajak <b class="isi">*</b></td>
            <td>: <input type="number" name="REK[tarif1]" id="tarif1" style="width: 100px;"></td>
        </tr>
		<?php
		if(isset($_REQUEST['tarif']) && $_REQUEST['tarif']!='reklame'){
		?>
		<tr>
            <td>Harga Dasar</td>
            <td>: <input type="number" name="REK[tarif2]" id="tarif2" style="width: 100px;"></td>
        </tr>
		<?php
		}
		elseif(isset($_REQUEST['tarif']) && $_REQUEST['tarif']=='reklame'){
		?>
		<tr>
            <td>Tarif Kawasan (Reklame)</td>
            <td>: <input type="text" name="REK[tarif3]" id="tarif3" style="width: 100px;"></td>
        </tr><input type="hidden" name="masa[1]" id="masa-1"><input type="hidden" name="masa[2]" id="masa-2"><input type="hidden" name="masa[3]" id="masa-3"><input type="hidden" name="masa[4]" id="masa-4"><input type="hidden" name="masa[5]" id="masa-5"><input type="hidden" name="masa[6]" id="masa-6">
		<?php
		}
		?>		
        <tr class="button-area">
            <td align="center" colspan="2">
				<input type="reset" value="Reset">
				<input type="submit" class="btn-submit" action="save" value="Simpan">
				<input type="hidden" name="function" id="function" value="save_tarif">
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
$(document).ready(function(){

	$('#nmheader3').change(function(){
							$.ajax({
								type: "POST",
								dataType:'json',
								url: "function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php",
								data: {'function' : 'get_no_rek', 'CPM_KD_ID' : $(this).val()},
								async:false,
								success: function(data){
									// console.log(data.nama);
									// $('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>'+html);
									$('#kdrek').val(data.nilai);
									$('#nama').val(data.nama);
								}
							});
						});

	$('#form-conf').submit(function(e){
		e.preventDefault();
		if($('#kdrek').val()!='' && $('#nmrek').val()!='' && $('#nmheader3').val()!='' && $('#tarif1').val()!=''){
				$.post('function/<?php echo "{$DIR}/{$modul}"; ?>/svc-konfigurasi-tarif-new.php',$(this).serialize(),function(data){
					
				},'json');
				alert("data tersimpan");
						window.location='main.php?param=<?php echo base64_encode("a={$_REQUEST['a']}&m={$_REQUEST['m']}&i='2'") ?>';
		}
		else {
			alert('Isian dengan tanda bintang harus diisi!');
		}
	})
});
</script>
