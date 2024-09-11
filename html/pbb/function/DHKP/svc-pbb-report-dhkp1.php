<?php 

echo "abc";
/*
//ini_set("display_errors", 1); error_reporting(E_ALL);
//$sRootPath = str_replace('\\', '/', str_replace('/svr/pbb', '', dirname(__FILE__))).'/';
$sRootPath = str_replace('\\', '/', str_replace('/function/DHKP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/report/eng-report-table.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");

$kecamatan=base64_decode($_REQUEST['kecamatan']);
$kelurahan=base64_decode($_REQUEST['kelurahan']);
$limit_1=base64_decode($_REQUEST['limit_1']);
$limit_2=base64_decode($_REQUEST['limit_2']);  

function getDataReport(&$DB,&$total_hal_ini,&$total_sampai_ini,&$kota_kab,&$loadKecamatan,&$loadKelurahan,&$jumlahRow) {
	global $iErrCode,$sErrMsg,$DBLink, $DBConn, $limit_1, $limit_2, $kecamatan, $kelurahan;
		//$C_HOST_PORT="127.0.0.1:3306";
		//$C_USER="sw_user";
		//$C_PWD="sw_pwd";
		//$C_DB="SW_SSB_O2W_DEMO";
		/$C_HOST_PORT="192.168.26.192:3306";
		  $C_USER="sw_user_devel";
		  $C_PWD="sw_pwd_devel";
		  $C_DB="VSI_SWITCHER_DEVEL";
		$kecamatan = trim($kecamatan);
		$kelurahan = trim($kelurahan);
		$LDBLink=mysql_connect($C_HOST_PORT,$C_USER,$C_PWD) or die(mysqli_error($DBLink));
		mysql_select_db($C_DB,$LDBLink);
			$query="select * from cppmod_pbb_sppt_current where FLAG = '0' ";
		if(!empty($kecamatan)){
			$query.=" and OP_KECAMATAN_KODE='$kecamatan'";
		}
		if(!empty($kelurahan)){
			$query.=" and OP_KELURAHAN_KODE='$kelurahan'";
		}
			$query.="ORDER BY SPPT_TAHUN_PAJAK,OP_KELURAHAN,OP_KECAMATAN,NOP ";
			
		if(preg_match("/^[0-9]/", $limit_1) and preg_match("/^[0-9]/", $limit_2)) {
			$query.=" LIMIT ".$limit_1.",".$limit_2." ;";
		}

		$qu=mysql_query($query) or die("#er01: ".mysqli_error($DBLink));
		$i=0;
		$jpajakTerutang=0;
		while($r=mysqli_fetch_array($qu)){
			$r['NOP']=($r['NOP']==NULL)?"0":$r['NOP'];
			$r['WP_NAMA']=($r['WP_NAMA']==NULL)?"0":$r['WP_NAMA'];
			$r['WP_ALAMAT']=($r['WP_ALAMAT']==NULL)?"0":$r['WP_ALAMAT'];
			$r['SPPT_PBB_HARUS_DIBAYAR']=($r['SPPT_PBB_HARUS_DIBAYAR']==NULL)?"0":$r['SPPT_PBB_HARUS_DIBAYAR'];
			$r['SPPT_TANGGAL_JATUH_TEMPO']=($r['SPPT_TANGGAL_JATUH_TEMPO']==NULL)?"1999-01-01":$r['SPPT_TANGGAL_JATUH_TEMPO'];
			$r['OP_KECAMATAN_KODE']=($r['OP_KECAMATAN_KODE']==NULL)?"0":$r['OP_KECAMATAN_KODE'];
			$r['OP_KELURAHAN_KODE']=($r['OP_KELURAHAN_KODE']==NULL)?"0":$r['OP_KELURAHAN_KODE'];
			$r['OP_KECAMATAN']=($r['OP_KECAMATAN']==NULL)?"0":$r['OP_KECAMATAN'];
			$r['OP_KELURAHAN']=($r['OP_KELURAHAN']==NULL)?"0":$r['OP_KELURAHAN'];
			$r['OP_KOTAKAB']=($r['OP_KOTAKAB']==NULL)?"0":$r['OP_KOTAKAB'];

			$DB[$i]['NOP']=$r['NOP'];
			$DB[$i]['NOMOR_INDUK']='';
			$DB[$i]['NAMA_WAJIB_PAJAK']=substr($r['WP_NAMA'],0,20);
			$DB[$i]['ALAMAT_OBJEK_PAJAK']=substr($r['WP_ALAMAT'],0,22);
			$DB[$i]['PAJAK_TERUTANG']=$r['SPPT_PBB_HARUS_DIBAYAR'];
			$DB[$i]['PERUBAHAN_PAJAK']=000000; 
			$DB[$i]['TGL_BAYAR']=$r['SPPT_TANGGAL_JATUH_TEMPO']; //ambil dari field
			$DB[$i]['KECAMATAN_KODE']=$r['OP_KECAMATAN_KODE'];
			$DB[$i]['KELURAHAN_KODE']=$r['OP_KELURAHAN_KODE'];
			$DB[$i]['KECAMATAN']=$r['OP_KECAMATAN'];
			$DB[$i]['KELURAHAN']=$r['OP_KELURAHAN'];
			$i++;
			$jpajakTerutang=$jpajakTerutang+$r['SPPT_PBB_HARUS_DIBAYAR'];
			$kota_kab = $r['OP_KOTAKAB'];
			$loadKecamatan = $r['OP_KECAMATAN'];
			$loadKelurahan = $r['OP_KELURAHAN'];
		}
		if($kecamatan==0){
			$loadKecamatan="Semua";
		}
		if($kelurahan==0){
			$loadKelurahan="Semua";
		}
		if($kota_kab==0 or $kota_kab==NULL or $kota_kab==""){
			$sql="select OP_KOTAKAB from cppmod_pbb_sppt_current where OP_KOTAKAB<>'' limit 1";
			$qu=mysql_query($sql) or die("#er02: ".mysqli_error($DBLink));
			$rd=mysqli_fetch_assoc($qu);
			$rd['OP_KOTAKAB']=($rd['OP_KOTAKAB']==NULL)?"":$rd['OP_KOTAKAB'];
			$kota_kab= $rd['OP_KOTAKAB'];
		}
		$total_hal_ini =$jpajakTerutang;
		$total_sampai_ini ="total_sampai_ini";
		
		$qJml="select count(*) as JML from cppmod_pbb_sppt_current where FLAG = '0' ";
		if(!empty($kecamatan)){
			$qJml.=" and OP_KECAMATAN_KODE='$kecamatan'";
		}
		if(!empty($kelurahan)){
			$qJml.=" and OP_KELURAHAN_KODE='$kelurahan'";
		}
		$qJml.="ORDER BY OP_KELURAHAN,OP_KECAMATAN,NOP,SPPT_TAHUN_PAJAK ";
		$quJml=mysql_query($qJml) or die("#er02: ".mysqli_error($DBLink));	
		$rJ=mysqli_fetch_array($quJml);
		$jumlahRow=$rJ['JML'];
		
		mysqli_close($LDBLink);
	return true;
}
function getValuesForPrint (&$hValues,&$bValues,&$fValues,&$jRow){
	global $sTemplateFile,$sdata,$User,$data,$driver,$total_hal_ini,$total_sampai_ini;
	$OK = true;
	
	$body = array();
	if (getDataReport($bdy,$total_hal_ini,$total_sampai_ini,$kota_kab,$loadKecamatan,$loadKelurahan,$jumlahRow)){ $body = $bdy; } 
	//ambil isi variabel body dengan passing by reference dari fungsi getDataReport()
	
	$header = array();
	$header['KOTA_KAB'] = $kota_kab;
	$header['KECAMATAN'] = $loadKecamatan;
	$header['KELURAHAN'] = $loadKelurahan;

	$footer = array();
	$footer['TOTAL_HAL_INI'] = $total_hal_ini;
	$footer['TOTAL_SAMPAI_INI'] = $total_sampai_ini;
	
	$hValues = $header; // variabel header laporan
	$bValues = $body; // variabel body laporan
	$fValues = $footer; // variabel footer laporan
	$jRow=$jumlahRow;
	return $OK;
}

function printReceipt(&$printHTML,&$printCode) {
	global $sTemplateFile,$sdata,$User,$data,$driver;
	$sTemplateFile = str_replace('/function/fahmi/svr', '', dirname(__FILE__)).DIRECTORY_SEPARATOR."function/fahmi/xml/DHKP-Report.xml"; 
	//alamat template laporan
		if (getValuesForPrint($hValues,$bValues,$fValues,$jRow)) {  // ambil isi data untuk di print
			$re = new ReportEngineTable($sTemplateFile,$hValues,$bValues,$fValues); // ambil class ReportEngine (mesin print)
			if($driver=="other"){
				$re->Print2OnpaysTXT($printcode);
			}else{
				$re->Print2TXT($printcode);
			}
			$re->PrintHTML($printhtml);
			$printHTML = $printhtml;
		    $printCode = $printcode;
		}
}
	  if(isset($sdata)){
	  $pccopy = isset($PPID_setting[$sdata->ppid.".PP.voucher.PC.print.copy"])?intval($PPID_setting[$sdata->ppid.".PP.voucher.PC.print.copy"]):1;
	  $driver=isset($PPID_setting[$sdata->ppid.".PP.voucher.PC.print.driver"])?$PPID_setting[$sdata->ppid.".PP.voucher.PC.print.driver"]:"epson";
	  }else{
	  $pccopy = 1;
	  $driver = "epson";
	  }
	  if($pccopy<1)$pccopy=1;
	  
	  printReceipt($printHTML,$printCode); 
	  getValuesForPrint($hValues,$bValues,$fValues,$jRow); //print_r($bValues)."<br>";
	  $result = array();
	  $result['result'] = true;		  
	  $result['message'] = $printHTML;
	  $result['printcode'] = $driver=="other"?$printCode:base64_encode($printCode);
	  $result['printcopy'] = $pccopy;
	  $result['header'] = $hValues;
	  $result['body'] = $bValues;
	  $result['footer'] = $fValues;
	  $result['totalData'] = $jRow;
	  $json = new Services_JSON();
	  echo base64_encode($json->encode($result));
	  //echo $json->encode($result);
?>