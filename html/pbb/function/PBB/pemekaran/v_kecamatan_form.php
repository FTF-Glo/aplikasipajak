<script>
	$(document).ready(function () {
	    $('#kdkota').prop('disabled', true);
		$('#imgloading').hide();
		
		$('#kdkec').val('<?php echo $_REQUEST['id']?>'.substr(4,3));
		$('#nmkec').val('<?php echo $_REQUEST['name']?>');
		
		if('<?php echo $_REQUEST['tag']?>'=='1') $('#kdkec').prop('disabled', true);
	});
	
	function sendData(){
	    var msg = '';
		if($('#kdkec').val().length < 3) msg += 'Kode Kecamatan Harus Diisi 3 Digit ! <br>';
		if($('#nmkec').val().length < 1) msg += 'Nama Kecamatan Harus Diisi !';
		
		if(msg)$('#msgbox').html(msg);
		else{
			var r=confirm("Anda yakin akan menyimpan data?");
			if (r==true){
				$('#imgloading').show();
				$.ajax({
					type: "POST",   
					url: "function/PBB/pemekaran/f_kecamatan.php", 
					data:{
						ID: "<?php echo $sessID?>", 
						action: "iNSERT",
						kdkec: $('#kdkec').val(),
						nmkec: $('#nmkec').val()
					},
					success : function(json){
						$('#imgloading').hide();
						backHome();
					},
					error: function (error){
						$('#imgloading').hide();
						// OK
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
		<td>KODE KECAMATAN</td>
		<td width="20" align="center">:</td>
		<td>
			<input type="text" name="kdkota" id="kdkota" size="2" maxlength="3" value="<?php echo $kdKota?>">
			<input type="text" name="kdkec" id="kdkec" size="1" maxlength="3">
		</td>
		<td width="10"></td>
	</tr>
	<tr height="35">
		<td></td>
		<td>NAMA KECAMATAN</td>
		<td align="center">:</td>
		<td><input type="text" name="nmkec" id="nmkec" size="30"></td>
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