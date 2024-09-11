<?php
    $uid     = $data->uid;
    $uname     = $data->uname;

    $sql     = "SELECT * FROM `cppmod_pbb_user_printer` WHERE CPM_UID = '$uid' AND CPM_MODULE = '$m'";
    $result  = mysqli_query($DBLink, $sql);
    $row     = mysqli_fetch_array($result);

    $printer = str_replace("\\", "\\\\", $row['CPM_PRINTERNAME']);
    $driver  = $row['CPM_DRIVER'];
    $urlCekTagihan = 'http://'.$_SERVER['HTTP_HOST'].'/portlet/';
    
    $tgl = date("d-m-Y");
	$q = @isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
	 $q = base64_decode($q);
	 $q = $json->decode($q);
	
	$function = @isset($f) ? $f : "";
	//print_r($f);
	$hidden="";
	if($function!=""){
		$hidden="hidden";
	}
?>
<script language="javascript" src="jquery-1.4.2.min.js"></script>
<!--<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.9.2.custom.min.js"></script>-->

<div id="header-tax" name="header-tax">
	<form action="" method="post" id="inqform" name="inqform">
            <input type="hidden" id="uname" name="uname" value="<?php echo $uname;?>">
		<table border="0" cellpadding="4" cellspacing="1" width="820px">
			<tr>
				<td colspan="4" style="background-color:transparent;"><b>Pencarian</b></td>
			</tr>
			<tr>
				<td style="background-color:transparent;" width="35%"><input name="radiogroup" value="0" id="radiogroup0" checked="" type="radio"> NOP / Kode Bayar </td>
				<td style="background-color:transparent;" width="10%"><input name="radiogroup" value="1" id="radiogroup1" style="display:none" type="radio"><span style="display:none">NPWP</span> </td>
				<td style="background-color:transparent;" width="10%"><input name="nop_npwp" id="nop_npwp" maxlength="32" size="32" type="text"  value=""></td>
				
				<td style="background-color:transparent;" width="60%" colspan="2">
					<input name="inquiry" value="Inquiry" id="inquiry" onclick="sendInquiry();" type="button">
					
					<td style="background-color:transparent;color:red;" width="15%"> <b>Tanggal <b></td>
				 <td style="background-color:transparent;">:</td>
				 <td style="background-color:transparent;" width="15%"><input class="srcTgl" name="tgl-bayar" id="tgl-bayar" readonly="readonly" type="text" size="9" maxlength="10" value="<?php echo $tgl;?>"></td>
                    
					<input value="<?php echo  $driver ?>" name="driver" id="driver" type="hidden">
					<input value="bayar" name="mode" id="mode" type="hidden">
				</td>
			</tr>
		</table>
	</form>
</div>

<!-- BODY -->

<div id="body-tax" name="body-tax">
	 <table border="0" cellpadding="0" cellspacing="0" width="820px">
		 <tbody><tr>
			 <td colspan="3" style="background-color:transparent;" width="50%"><font color="#999999"><b>Nama Wajib Pajak</b></font><b><b></b></b></td>
			 <td colspan="3" style="background-color:transparent;" width="40%"><font color="#999999"><b>Tanggal Jatuh Tempo<b></b></b></font></td>
		 </tr>
		 <tr>
			 <td colspan="3" style="background-color:transparent;" ><span id="wp-name" name="wp-name">-</span></td>
			 <td colspan="3" style="background-color:transparent;" ><span id="wp-duedate" name="wp-duedate">0000-00-00</span></td>
		 </tr>
		 <tr>
			 <td colspan="3" style="background-color:transparent;"><font color="#999999"><b>Alamat Wajib Pajak</b></font><b><b></b></b></td>
			 <td colspan="3" style="background-color:transparent;"><font color="#999999"><b>Alamat Objek Pajak</b></font><b><b></b></b></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">Alamat</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="wp-address" name="wp-address">-</span></td>
			  <td style="background-color:transparent;">Alamat</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="op-address" name="op-address">-</span></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">Kabupaten/Kota</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="wp-kelurahan" name="wp-kelurahan">-</span></td>
			 <td style="background-color:transparent;">Kabupaten/Kota</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="op-kelurahan" name="op-kelurahan">-</span></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">RT/RW</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;" width="40%"><span id="wp-rtRw" name="wp-rtRw">-</span></td>
			 <td style="background-color:transparent;">RT/RW</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;" width="40%"><span id="op-rtRw" name="op-rtRw">-</span></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">Kecamatan</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="wp-kecamatan" name="wp-kecamatan">-</span></td>
			 <td style="background-color:transparent;">Kecamatan</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="op-kecamatan" name="op-kecamatan">-</span></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">Kabupaten</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="wp-kabupaten" name="wp-kabupaten">-</span></td>
			 <td style="background-color:transparent;">Kabupaten</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="op-kabupaten" name="op-kabupaten">-</span></td>
		 </tr>
		 <tr>
			 <td style="background-color:transparent;">Kode Pos</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="wp-kdPos" name="wp-kdPos">-</span></td>
			 <td style="background-color:transparent;">Kode Pos</td>
			 <td style="background-color:transparent;">:</td>
			 <td style="background-color:transparent;"><span id="op-kdPos" name="op-kdPos">-</span></td>
		 </tr>
		 
	 </tbody></table></div><div id="footer-tax" name="footer-tax">
	
		 <table border="0" cellpadding="4" cellspacing="1" width="900px">
			 <tbody><tr><td colspan="10" style="background-color:transparent;"><b>Pembayaran</b></td></tr><tr>
			 </tr><tr>
                                
				 <td style="background-color:transparent;" width="15%"> Jumlah </td>
				 <td style="background-color:transparent;">:</td>
				 <td style="background-color:transparent;" width="15%"><input name="jml-bayar" value="" id="jml-bayar" readonly="readonly" type="text"></td>
				 <td style="background-color:transparent;" width="15%" <?php echo $hidden ?>>Uang </td>
				 <td style="background-color:transparent;" <?php echo $hidden ?>>:</td>
<!--				 <td style="background-color:transparent;" width="15%"><input name="jml-uang" value="" id="jml-uang" onkeypress="//return(currencyFormatI(this,'.',event));" onselect="return(onSelectClearFormat(this, '.'))" onblur="return(currencyFormatIC(this,'.'))" onkeyup="jml();" type="text"></td>-->
				 <td style="background-color:transparent;" width="15%"><input name="jml-uang" <?php echo $hidden ?> value="" id="jml-uang" onkeypress="return(currencyFormatI(this,'.',event));" onselect="return(onSelectClearFormat(this, '.'))" onkeyup="jml();" type="text"></td>
				 <td style="background-color:transparent;" width="15%">Kembali </td>
				 <td style="background-color:transparent;">:</td>
				 <td style="background-color:transparent;" width="15%"><input name="jml-kembali" value="" id="jml-kembali" readonly="readonly" type="text"></td>
				 <td style="background-color:transparent;"><input name="payment" value="Bayar" id="payment" onclick="sendBayar()" type="button" disabled></td>
			 </tr>
		 </tbody></table>
	
</div>
<br><br>
<div id="tab-result"></div>

<applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" height="0" width="0">
	<param name="printer" id="printer" value="<?php echo $printer?>">
	<param name="sleep" value="200">
</applet>
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<script language="javascript" src="view/BPHTB/pembayaran/pembayaran.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
         $( ".srcTgl" ).datepicker({dateFormat:'dd-mm-yy',maxDate: '0'});
		$("#nop_npwp").focus();
		$("#nop_npwp").keypress(function(e){
			if(e.keyCode==13){
				var dt  = $("#nop_npwp").val();
				var res = dt.split("\\");
				$("#nop_npwp").val(res[0]);
				$("#year").val(res[1]);
				e.preventDefault();
			}
		});
    });
</script>