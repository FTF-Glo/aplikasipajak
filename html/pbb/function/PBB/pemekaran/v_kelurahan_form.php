<script>
	$(document).ready(function () {
	    $('#kdkec').prop('disabled', true);
		$('#imgloading').hide();
		
		$('#kdkel').val('<?php echo $_REQUEST['id']?>'.substr(7,3));
		$('#nmkel').val('<?php echo $_REQUEST['name']?>');
		
		if('<?php echo $_REQUEST['tag']?>'=='1') $('#kdkel').prop('disabled', true);
		loadSektor();
	});
	
	function loadSektor(){
	    $('#sektor').find('option').remove();
		$.each(arrSektor, function(key, value) {  
		     var selected = '';
			 if('<?php echo $_REQUEST['sektor']?>' == value['kode']) selected='selected';
			 $('#sektor') .append("<option value='"+value['kode']+"' "+selected+">"+value['nama']+"</option>");
		});
	}
	
	function sendData(){
	    var msg = '';
		if($('#kdkel').val().length < 3) msg += 'Kode Kelurahan Harus Diisi 3 Digit ! <br>';
		if($('#nmkel').val().length < 1) msg += 'Nama Kelurahan Harus Diisi ! <br>';
		if(!$('#sektor').val()) msg += 'Pilih Sektor ! <br>';
		
		if(msg)$('#msgbox').html(msg);
		else{
			var r=confirm("Anda yakin akan menyimpan data?");
			if (r==true){
				$('#imgloading').show();
				$.ajax({
					type: "POST",   
					url: "function/PBB/pemekaran/f_kelurahan.php", 
					data:{
						ID: "<?php echo $sessID?>", 
						action: "iNSERT",
						kdkel: $('#kdkel').val(),
						nmkel: $('#nmkel').val(),
						sektor: $('#sektor').val()
					},
					success : function(json){
						$('#imgloading').hide();
						backHome();
					},
					error: function (error){
						$('#imgloading').hide();
					}
				});
			}
		}
	}
	
	function backHome(){
		window.location = 'main.php?param=<?php echo $param?>';
	}
</script>
<table cellpadding="0" cellspacing="0" id="tableForm">
	<tr><td colspan="5" height="10"><img src="/image/icon/loadinfo.net.gif" id="imgloading"/></td></tr> 
	<tr height="35">
		<td width="10"></td>
		<td>KODE KELURAHAN</td>
		<td width="20" align="center">:</td>
		<td>
			<input type="text" name="kdkec" id="kdkec" size="7" maxlength="7" value="<?php echo $kdKec?>">
			<input type="text" name="kdkel" id="kdkel" size="1" maxlength="3">
		</td>
		<td width="10"></td>
	</tr>
	<tr height="35">
		<td></td>
		<td>NAMA KELURAHAN</td>
		<td align="center">:</td>
		<td><input type="text" name="nmkel" id="nmkel" size="30"></td>
		<td></td>
	</tr>
	
	<tr height="35">
		<td></td>
		<td>SEKTOR</td>
		<td align="center">:</td>
		<td><select id="sektor" name="sektor"></select></td>
		<td></td>
	</tr>
	
	<tr>
		<td width="10"></td>
		<td colspan="3" id="msgbox" style="color: red"></td>
		<td width="10"></td>
	</tr> 
	
	<tr height="30">
		<td></td>
		<td></td>
		<td></td>
		<td>
			<input type="button" value="Simpan" onclick="sendData()">
			<input type="button" value="Batal" onclick="backHome()">
			<input type="hidden" value="<?php echo $sessID?>" name="ID">
			<input type="hidden" value="<?php echo $sessID?>" name="action" id="action">
			<input type="hidden" value="<?php echo $sessID?>" name="action" id="action">
		</td>
		<td></td>
	</tr>
	<tr><td colspan="5" height="10"></td></tr> 
</table>