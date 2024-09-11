<?php 
//ini_set("display_errors", 1); error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace('/function/DHKP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/db-payment.php");

$kecamatan=base64_decode($_REQUEST['kecamatan']);
$kelurahan=base64_decode($_REQUEST['kelurahan']);
$limit_1=base64_decode($_REQUEST['limit_1']);
$limit_2=base64_decode($_REQUEST['limit_2']);  

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);

function clean($str) { 
  $search  = array('&'    , '"'     , "'"    , '<'   , '>'    ); 
  $replace = array('&amp;', '&quot;', '&#39;', '&lt;', '&gt;' ); 

  $str = str_replace($search, $replace, $str); 
  return $str; 
} 

function getDataReport(&$DB,&$total_hal_ini,&$total_sampai_ini,&$kota_kab,&$loadKecamatan,&$loadKelurahan,&$jumlahRow, &$tglTerbit) {
	global $iErrCode,$sErrMsg,$DBLink, $DBConn, $limit_1, $limit_2, $kecamatan, $kelurahan;
		$kecamatan = trim($kecamatan);
		$kelurahan = trim($kelurahan);
                
		$query="SELECT CONCAT(SUBSTRING(A.NOP,11,3),'.',SUBSTRING(A.NOP,14,4),'-',SUBSTRING(A.NOP,18,1)) AS NOP, LEFT(A.WP_NAMA, 30) AS WP_NAMA, LEFT(A.OP_ALAMAT, 30) AS OP_ALAMAT, A.OP_RT, A.OP_RW, LEFT(A.WP_ALAMAT, 30) AS WP_ALAMAT, A.SPPT_PBB_HARUS_DIBAYAR, A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, 
                    A.OP_KECAMATAN_KODE, A.OP_KELURAHAN_KODE, A.OP_KECAMATAN, A.OP_KELURAHAN, '' AS TGL_BAYAR, A.SPPT_TANGGAL_TERBIT, A.OP_KELAS_BUMI, B.CPM_NOP_INDUK AS NOP_INDUK from cppmod_pbb_sppt_current A
                    LEFT JOIN cppmod_pbb_sppt_anggota B ON A.NOP=B.CPM_NOP ";
		$whereClause = array();
                if(!empty($kecamatan)){
			$whereClause[] = " A.OP_KECAMATAN_KODE='$kecamatan' ";
		}
		if(!empty($kelurahan)){
			$whereClause[] = " A.OP_KELURAHAN_KODE='$kelurahan'";
		}
		$where = '';
                if($whereClause) $where = ' WHERE '.join(' AND ', $whereClause);
                $query.=" $where ORDER BY A.NOP, A.SPPT_TAHUN_PAJAK";
		
                
		if(preg_match("/^[0-9]/", $limit_1) and preg_match("/^[0-9]/", $limit_2)) {
			$query.=" LIMIT ".$limit_1.",".$limit_2." ;";
		}
                
		$qu=mysqli_query($DBLink, $query) or die("#er01: ".mysqli_error($DBLink));
		$i=0;
		$jpajakTerutang=0;
		while($r=mysqli_fetch_array($qu)){
			$r['NOP']=($r['NOP']==NULL)?"":$r['NOP'];
			$r['NOP_INDUK']=($r['NOP_INDUK']==NULL)?"":$r['NOP_INDUK'];
			$r['WP_NAMA']=($r['WP_NAMA']==NULL)?"":$r['WP_NAMA'];
			$r['OP_ALAMAT']=($r['OP_ALAMAT']==NULL)?"":$r['OP_ALAMAT'];
			$r['OP_RT']=($r['OP_RT']==NULL)?"":$r['OP_RT'];
			$r['OP_RW']=($r['OP_RW']==NULL)?"":$r['OP_RW'];
			$r['WP_ALAMAT']=($r['WP_ALAMAT']==NULL)?"":$r['WP_ALAMAT'];
			$r['SPPT_PBB_HARUS_DIBAYAR']=($r['SPPT_PBB_HARUS_DIBAYAR']==NULL)?"":$r['SPPT_PBB_HARUS_DIBAYAR'];
			$r['TGL_BAYAR']=($r['TGL_BAYAR']==NULL)?"":$r['TGL_BAYAR'];
			$r['OP_KECAMATAN_KODE']=($r['OP_KECAMATAN_KODE']==NULL)?"":$r['OP_KECAMATAN_KODE'];
			$r['OP_KELURAHAN_KODE']=($r['OP_KELURAHAN_KODE']==NULL)?"":$r['OP_KELURAHAN_KODE'];
			$r['OP_KECAMATAN']=($r['OP_KECAMATAN']==NULL)?"":$r['OP_KECAMATAN'];
			$r['OP_KELURAHAN']=($r['OP_KELURAHAN']==NULL)?"":$r['OP_KELURAHAN'];
			$r['OP_LUAS_BUMI']=($r['OP_LUAS_BUMI']==NULL)?"0":$r['OP_LUAS_BUMI'];
			$r['OP_LUAS_BANGUNAN']=($r['OP_LUAS_BANGUNAN']==NULL)?"0":$r['OP_LUAS_BANGUNAN'];
			$r['OP_KELAS_BUMI']=($r['OP_KELAS_BUMI']==NULL)?"XXX":$r['OP_KELAS_BUMI'];

			$DB[$i]['n']=clean($r['NOP']);
			$DB[$i]['ni']=clean($r['NOP_INDUK']);
			$DB[$i]['nm']=clean($r['WP_NAMA']);
			$DB[$i]['ao']=clean($r['OP_ALAMAT']);
			$DB[$i]['ow']=clean($r['OP_RW']);
			$DB[$i]['ot']=clean($r['OP_RT']);
			$DB[$i]['aw']=clean($r['WP_ALAMAT']);
			$DB[$i]['lbm']=$r['OP_LUAS_BUMI'];
			$DB[$i]['lbg']=$r['OP_LUAS_BANGUNAN'];
			$DB[$i]['p']=$r['SPPT_PBB_HARUS_DIBAYAR'];
			$DB[$i]['pp']=''; 
			$DB[$i]['t']=$r['TGL_BAYAR']; //ambil dari field
			$DB[$i]['kck']=$r['OP_KECAMATAN_KODE'];
			$DB[$i]['klk']=$r['OP_KELURAHAN_KODE'];
			$DB[$i]['kc']=clean($r['OP_KECAMATAN']);
			$DB[$i]['kl']=clean($r['OP_KELURAHAN']);
			$DB[$i]['kb']=$r['OP_KELAS_BUMI'];
			$i++;
			$jpajakTerutang=$jpajakTerutang+$r['SPPT_PBB_HARUS_DIBAYAR'];
			$kota_kab = $r['OP_KOTAKAB'];
			$loadKecamatan = $r['OP_KECAMATAN'];
			$loadKelurahan = $r['OP_KELURAHAN'];
			$tglTerbit = $r['SPPT_TANGGAL_TERBIT'];
		}
		if($kecamatan==0){
			$loadKecamatan="Semua";
		}
		if($kelurahan==0){
			$loadKelurahan="Semua";
		}
		if($kota_kab==0 or $kota_kab==NULL or $kota_kab==""){
			$sql="select OP_KOTAKAB from cppmod_pbb_sppt_current where OP_KOTAKAB<>'' limit 1";
			$qu=mysqli_query($DBLink, $sql) or die("#er02: ".mysqli_error($DBLink));
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
		$quJml=mysqli_query($DBLink, $qJml) or die("#er02: ".mysqli_error($DBLink));	
		$rJ=mysqli_fetch_array($quJml);
		$jumlahRow=$rJ['JML'];
		
		// mysqli_close($LDBLink);
	return true;
}
function getValuesForPrint (&$hValues,&$bValues,&$fValues,&$jRow){
	global $sTemplateFile,$sdata,$User,$data,$driver,$total_hal_ini,$total_sampai_ini;
	$OK = true;
	
	$body = array();
	if (getDataReport($bdy,$total_hal_ini,$total_sampai_ini,$kota_kab,$loadKecamatan,$loadKelurahan,$jumlahRow, $tglTerbit)){ $body = $bdy; } 
	//ambil isi variabel body dengan passing by reference dari fungsi getDataReport()
	
	$header = array();
	$header['KOTA_KAB'] = $kota_kab;
	$header['KECAMATAN'] = $loadKecamatan;
	$header['KELURAHAN'] = $loadKelurahan;
	$header['TGL_TERBIT'] = $tglTerbit;

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
	  // echo $json->encode($result);

?>