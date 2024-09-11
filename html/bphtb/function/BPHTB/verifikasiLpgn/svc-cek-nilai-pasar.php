<?php


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue ($id,$key) {
	global $DBLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	
		$res = mysqli_query($DBLink, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	
}

function getConfigure ($appID) {
  $config = array();
  $a=$appID;
  $config['TENGGAT_WAKTU'] = getConfigValue($a,'TENGGAT_WAKTU');
  $config['NPOPTKP_STANDAR'] = getConfigValue($a,'NPOPTKP_STANDAR');
  $config['NPOPTKP_WARIS'] = getConfigValue($a,'NPOPTKP_WARIS');
  $config['TARIF_BPHTB'] = getConfigValue($a,'TARIF_BPHTB');
  $config['PRINT_SSPD_BPHTB'] = getConfigValue($a,'PRINT_SSPD_BPHTB');
  $config['NAMA_DINAS'] = getConfigValue($a,'NAMA_DINAS');
  $config['ALAMAT'] = getConfigValue($a,'ALAMAT');
  $config['NAMA_DAERAH'] = getConfigValue($a,'NAMA_DAERAH');
  $config['KODE_POS'] = getConfigValue($a,'KODE_POS');
  $config['NO_TELEPON'] = getConfigValue($a,'NO_TELEPON');
  $config['NO_FAX'] = getConfigValue($a,'NO_FAX');
  $config['EMAIL'] = getConfigValue($a,'EMAIL');
  $config['WEBSITE'] = getConfigValue($a,'WEBSITE');
  $config['KODE_DAERAH'] = getConfigValue($a,'KODE_DAERAH');
  $config['KEPALA_DINAS'] = getConfigValue($a,'KEPALA_DINAS');
  $config['NAMA_JABATAN'] = getConfigValue($a,'NAMA_JABATAN');
  $config['NIP'] = getConfigValue($a,'NIP');
  $config['NAMA_PJB_PENGESAH'] = getConfigValue($a,'NAMA_PJB_PENGESAH');
  $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a,'JABATAN_PJB_PENGESAH');
  $config['NIP_PJB_PENGESAH'] = getConfigValue($a,'NIP_PJB_PENGESAH');
  
  $config['BPHTBDBNAME'] = getConfigValue($a,'BPHTBDBNAME');
  $config['BPHTBHOSTPORT'] = getConfigValue($a,'BPHTBHOSTPORT');
  $config['BPHTBPASSWORD'] = getConfigValue($a,'BPHTBPASSWORD');
  $config['BPHTBTABLE'] = getConfigValue($a,'BPHTBTABLE');
  $config['BPHTBUSERNAME'] = getConfigValue($a,'BPHTBUSERNAME');
  
  return $config;
}
$nop = @isset($_REQUEST['nop']) ? intval($_REQUEST['nop']) : "";
$role = @isset($_REQUEST['role']) ? $_REQUEST['role'] : "";
$ceknop = substr($nop,0,13);
$znt = @isset($_REQUEST['znt']) ? $_REQUEST['znt'] : "";
$harga = @isset($_REQUEST['harga']) ? intval($_REQUEST['harga']) : "";
$luas_tnh = @isset($_REQUEST['luas_tnh']) ? intval($_REQUEST['luas_tnh']) : "";
$njop_bgn = @isset($_REQUEST['njop_bgn']) ? intval($_REQUEST['njop_bgn']) : "";
$luas_bgn = @isset($_REQUEST['luas_bgn']) ? intval($_REQUEST['luas_bgn']) : "";
$jp = @isset($_REQUEST['id']) ? intval($_REQUEST['id']) : "";
$appId =base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");

$result = array();

	// $dbName = getConfigValue($appId,'BPHTBDBNAME');
	// $dbHost = getConfigValue($appId,'BPHTBHOSTPORT');
	// $dbPwd = getConfigValue($appId,'BPHTBPASSWORD');
	// $dbTable = getConfigValue($appId,'BPHTBTABLE');
	// $dbUser = getConfigValue($appId,'BPHTBUSERNAME');

	//SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	
	$qry = "select * from cppmod_ssb_nilai_pasar WHERE CONCAT(CPM_OP_KELURAHAN_KODE,CPM_OP_BLOK)='{$ceknop}' and CPM_OP_ZNT_KODE='{$znt}' limit 1";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	$row = mysqli_fetch_assoc($res);
	$compare= ($row['CPM_OP_NILAI_PASAR_TANAH']*$luas_tnh)+($njop_bgn*$luas_bgn);
	$jns_bebas_pasar=getConfigValue('aBPHTB','JNS_BEBAS_PASAR');
	$jenis = explode(',', $jns_bebas_pasar);
	$jns_pasar_adm=getConfigValue('aBPHTB','JNS_BEBAS_PASAR_ADMIN');
	$jenis_adm = explode(',', $jns_pasar_adm);
	//print_r($jenis);
	//print_r($row);
	$result['hasil']="";
   if(getConfigValue($appId,'CONFIG_PASAR')!=0){
	   
		if($role!="rmPelaporanHP"){
			
		  if(in_array($jp,$jenis)){
			if(getConfigValue('aBPHTB','CONFIG_PASAR_BTN_LOCK')!=0){
				
				if($harga<$compare){
					$result['hasil']="Harga Transaksi Tidak Sesuai dengan Harga Pasar";
					$result['flag']=2;
				 }else{
					$result['hasil']="Harga Transaksi Sesuai dengan Harga Pasar";
					$result['flag']=3;
				 }
			}else{
				if($harga<$compare){
					$result['hasil']="Harga Transaksi Tidak Sesuai dengan Harga Pasar";
					$result['flag']=3;
				 }else{
					$result['hasil']="Harga Transaksi Sesuai dengan Harga Pasar";
					$result['flag']=3;
				 }
			}
			 
			 
			 $result['success'] = true;
			 $result["result"] =  "
				<br><br><br>
				<table width=\"850\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
					</tr>
				  <tr>
					
					<td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
					</tr>
				  <tr>
					<td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_tnh, 0, ',', '.') . "
					  m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">". number_format($row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.'). "</td>
					<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_bgn, 0, ',', '.') . "
				m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($njop_bgn, 0, ',', '.') . "</td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_bgn*$njop_bgn, 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format((($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'])+($luas_bgn*$njop_bgn)), 0, ',', '.')."</td>
				  </tr>
					  </table>";
		}else{

			if($harga<$compare){
				$result['flag']=1;
				$result['success'] = true;
				$result['hasil']="Harga Transaksi Tidak Sesuai dengan Harga Pasar";
				$result["result"] =  "
				<br><br><br>
				<table width=\"850\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
					</tr>
				  <tr>
					
					<td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
					</tr>
				  <tr>
					<td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_tnh, 0, ',', '.') . "
					  m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">". number_format($row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.'). "</td>
					<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_bgn, 0, ',', '.') . "
				m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($njop_bgn, 0, ',', '.') . "</td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_bgn*$njop_bgn, 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format((($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'])+($luas_bgn*$njop_bgn)), 0, ',', '.')."</td>
				  </tr>
					  </table>";
				// $result["result"] =  "
				// <p align=\"center\"><b><font color=\"red\">Harga Transaksi Kurang dari Nilai Harga Pasar</font></b></p>
				// <p align=\"center\"><b>Dengan Perhitungan Sebagai Berikut:</b></p>
				// <p align=\"center\"><table width=\"747\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
				  // <tr>
					// <td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
					// </tr>
				  // <tr>
					
					// <td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
					// </tr>
				  // <tr>
					// <td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
					// <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
					// </tr>
				  // <tr>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_tnh, 0, ',', '.') . "
					  // m²</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">". number_format($row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.'). "</td>
					// <td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.')."</td>
				  // </tr>
				  // <tr>
					// <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
					// <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
					// </tr>
				  // <tr>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_bgn, 0, ',', '.') . "
				// m²</td>
					// <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($njop_bgn, 0, ',', '.') . "</td>
					// <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_bgn*$njop_bgn, 0, ',', '.')."</td>
				  // </tr>
				  // <tr>
					// <td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
					// <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format((($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'])+($luas_bgn*$njop_bgn)), 0, ',', '.')."</td>
				  // </tr>
					  // </table></p><br><br><span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>";
			}else{
				$result['flag']=0;
				$result['success'] = true;
				$result['hasil']="Harga Transaksi Sesuai dengan Harga Pasar";
				$result["result"] =  "
				<br><br><br>
				<table width=\"850\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
					</tr>
				  <tr>
					
					<td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
					</tr>
				  <tr>
					<td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_tnh, 0, ',', '.') . "
					  m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">". number_format($row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.'). "</td>
					<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_bgn, 0, ',', '.') . "
				m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($njop_bgn, 0, ',', '.') . "</td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_bgn*$njop_bgn, 0, ',', '.')."</td>
				  </tr>
				  <tr>
					<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format((($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'])+($luas_bgn*$njop_bgn)), 0, ',', '.')."</td>
				  </tr>
					  </table>";
			}
	}
}else{
	if(in_array($jp,$jenis_adm)){
		if($harga<$compare){
			$result['hasil']="Harga Transaksi Tidak Sesuai dengan Harga Pasar";
			$result['flag']=3;
		 }else{
			$result['hasil']="Harga Transaksi Sesuai dengan Harga Pasar";
			$result['flag']=3;
		 }
	}else{
		if($harga<$compare){
			$result['hasil']="Harga Transaksi Tidak Sesuai dengan Harga Pasar";
			$result['flag']=2;
		 }else{
			$result['hasil']="Harga Transaksi Sesuai dengan Harga Pasar";
			$result['flag']=3;
		 }
	}
	
	$result['success'] = true;
	$result["result"] =  "
	<br><br><br>
	<table width=\"850\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
	  <tr>
		<td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
		</tr>
	  <tr>
		
		<td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
		</tr>
	  <tr>
		<td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
		<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
		</tr>
	  <tr>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_tnh, 0, ',', '.') . "
		  m²</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">". number_format($row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.'). "</td>
		<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'], 0, ',', '.')."</td>
	  </tr>
	  <tr>
		<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
		<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
		</tr>
	  <tr>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($luas_bgn, 0, ',', '.') . "
	m²</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($njop_bgn, 0, ',', '.') . "</td>
		<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format($luas_bgn*$njop_bgn, 0, ',', '.')."</td>
	  </tr>
	  <tr>
		<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
		<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">".number_format((($luas_tnh*$row['CPM_OP_NILAI_PASAR_TANAH'])+($luas_bgn*$njop_bgn)), 0, ',', '.')."</td>
	  </tr>
		  </table>";
}
   }else{
	   $result['flag']=3;
	   $result['success'] = true;
   }
	$sResponse = $json->encode($result);
	echo $sResponse;
	
SCANPayment_CloseDB($DBLink);
?>
