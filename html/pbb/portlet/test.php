
<?
	include_once("inc-config.php");
	include_once("image/captcha/securimage.php");
	$client="";
	$area="";
	//var_dump($_REQUEST);
	if(!isset($_REQUEST["client"]) || !isset($_REQUEST["area"])){
		exit(1);
	}else{
		if(!AllowedClient($_REQUEST["client"],$_REQUEST["area"])){
			exit;
		}else{
			$client=$_REQUEST["client"];
			$area=$_REQUEST["area"];
		}
	}
	
	
		
	$nop=isset($_POST["nop"])?$_POST["nop"]:"";
	$cimage=isset($_POST["cImage"])?$_POST["cImage"]:"";
	echo "<h3 class='title'>Daftar Tagihan SPPT PBB</h3>";
	echo "<form method='POST'>";
	echo "<input type='hidden' maxlength='32' name='area' value='$area'>";
	echo "<input type='hidden' maxlength='32' name='client' value='$client'>";
	echo "<table><tr><td>NOP:</td><td><input type='text' maxlength='32' name='nop' value='$nop'></td></tr><tr><td>KODE VERIFIKASI :</td><td><img src='captcha2.php' alt='Captcha Image' id='captcha-image' /></td></tr><td>&nbsp;</td><td><input type='text' name='cImage' id='cImage' value ='' size='6' maxlength='10' autocomplete='off'></input>&nbsp;&nbsp;<input type='submit' value='Cari'></td></tr></table>";
	echo "</form>";
	$img = new Securimage();
	$equal = ($img->check($cimage) );
	if($nop!="" && $equal){
		GetListByNOP($nop);
		displayChecker();
	}else{
		echo "<span style='color:red'><b>NOP harus diisi dan Kode Verifikasi Harus Benar</b></span>";
	}
?>