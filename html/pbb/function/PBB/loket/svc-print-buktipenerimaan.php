<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1); 
date_default_timezone_set("Asia/Jakarta");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$arConfig = $User->GetModuleConfig('mLkt');

$dataNotaris="";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getAuthor($field,$uname) {
	global $DBLink,$appID;	
	$id= $appID;
	$qry = "select $field from tbl_reg_user_pbb where userId = '".$uname."'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo mysqli_error($DBLink);
	}
	
	$num_rows = mysqli_num_rows($res);
	if ($num_rows==0) return $uname;
	while ($row = mysqli_fetch_assoc($res)) {
		return $row[$field];
	}
}

function getConfigValue ($id,$key) {
	global $DBLink;	
	//$id= $appID;
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


function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getData($idssb) {
	global $data,$DBLink,$dataNotaris;
	
	$query = "SELECT
					BS.*, TKEC.CPC_TKC_KECAMATAN,
					DATE_FORMAT(
						BS.CPM_DATE_RECEIVE,
						'%d-%m-%Y'
					) AS TGL_MASUK,
					TKEL.CPC_TKL_KELURAHAN
				FROM
					cppmod_pbb_services BS
				LEFT JOIN cppmod_tax_kecamatan TKEC ON BS.CPM_OP_KECAMATAN = TKEC.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan TKEL ON BS.CPM_OP_KELURAHAN = TKEL.CPC_TKL_ID
				WHERE
					CPM_ID='$idssb'";
					
	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		echo $query."<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res,"data"));	
	$dt = $dataNotaris->data[0];
	return $dt;
}



function getHTML ($idssb, $initData, $fileLogo) {
	global $uname,$NOP, $appId, $arConfig;
	$data = getData($idssb);
	$dateNow = date('d-m-Y');
        $C_HEADER_FORM_PENERIMAAN	= getConfigValue ($appId, 'C_HEADER_FORM_PENERIMAAN');
        $NAMA_KOTA_PENGESAHAN		= ucwords(strtolower(getConfigValue ($appId, 'NAMA_KOTA_PENGESAHAN')));
	//$NOP = $data->CPM_OP_NUMBER;
	//echo $fileLogo;exit;
	
	$berkas = $data->CPM_TYPE;
	
	$jnsBerkas = array(
		1 => "OP BARU",
		2 => "PEMECAHAN",
		3 => "PENGGABUNGAN",
		4 => "MUTASI",
		5 => "PERUBAHAN DATA",
		6 => "PEMBATALAN",
		7 => "SALINAN",
		8 => "PENGHAPUSAN",
		9 => "PENGURANGAN",
		10 => "KEBERATAN",
		11 => "SURAT KETERANGAN NJOP",
		12 => "PENGURANGAN DENDA"
	);
	
	$buktiTitle = "BUKTI PENERIMAAN ".$jnsBerkas[$berkas]." PBB";
	$parse1 = "";
	$parse2 = "";
				
	
	$html = "
	<html>
	<table border=\"1\" cellpadding=\"5\">
		<tr>
			<!--LOGO-->
			<td align=\"center\" width=\"28%\">
				
			</td>
			<!--COP-->
			<td align=\"center\" width=\"72%\" colspan=\"2\">
			<br>
				".$C_HEADER_FORM_PENERIMAAN."
			</td>
		</tr>
        <tr>
        	<!--ISI-->
			<td colspan=\"3\">
				<font size=\"-1\">
				<table border=\"0\" cellpadding=\"1\" cellspacing=\"5\">
					<tr>
                        <td colspan=\"3\" align=\"center\">".$buktiTitle."<br></td>
                    </tr>
                    <tr>
                        <td width=\"125\" align=\"left\">Nomor</td><td width=\"10\">:</td>
						<td width=\"180\">".$data->CPM_ID."</td>
                    </tr>
                    <tr>
                        <td>Nama Wajib Pajak</td><td>:</td>
                        <td>".$data->CPM_WP_NAME."</td>
                    </tr>
                    <tr>
                        <td>Tanggal Surat Masuk</td><td>:</td>
                        <td>".$data->TGL_MASUK."</td>
                    </tr>";
             if($arConfig['TAMPILKAN_TGL_SELESAI'] == '1'){
                 
                 $arrayEstimasi = array(
                        1 => "ESTIMASI_SELESAI_OPBARU",
                        2 => "ESTIMASI_SELESAI_PEMECAHAN",
                        3 => "ESTIMASI_SELESAI_PENGGABUNGAN",
                        4 => "ESTIMASI_SELESAI_MUTASI",
                        5 => "ESTIMASI_SELESAI_PERUBAHAN",
                        6 => "ESTIMASI_SELESAI_PEMBATALAN",
                        7 => "ESTIMASI_SELESAI_SALINAN",
                        8 => "ESTIMASI_SELESAI_PENGHAPUSAN",
                        9 => "ESTIMASI_SELESAI_PENGURANGAN",
                        10 => "ESTIMASI_SELESAI_KEBERATAN",
                        11 => "ESTIMASI_SELESAI_SKNJOP",
                        12 => "ESTIMASI_SELESAI_OPTANAHREGISTER"
                );
        
                 $tglSelesai = date("d-m-Y", mktime(0, 0, 0, substr($data->TGL_MASUK, 3,2), (substr($data->TGL_MASUK, 0,2)+$arConfig[$arrayEstimasi[$berkas]]), substr($data->TGL_MASUK, 6,4)));
                 $html .= "<tr>
                        <td>Tanggal Selesai</td><td>:</td>
                        <td>".$tglSelesai."</td>
                    </tr>";
             }
             $html .= "<tr>
                        <td>Kecamatan</td><td>:</td>
                        <td>".$data->CPC_TKC_KECAMATAN."</td>
                    </tr>
                    <tr>
                        <td>Kelurahan</td><td>:</td>
                        <td>".$data->CPC_TKL_KELURAHAN."</td>
                    </tr>
                    <tr>
                        <td>NOP</td><td>:</td>
                        <td>".$data->CPM_OP_NUMBER."</td>
                    </tr>
                     <tr>
                        <td>No Telp WP</td><td>:</td>
                        <td>".$data->CPM_WP_HANDPHONE."</td>
                    </tr>
                     <tr>
                        <td>Jenis Berkas</td><td>:</td>
                        <td>".$jnsBerkas[$data->CPM_TYPE]."</td>
                    </tr>
					<tr>
                        <td>Keterangan</td><td>:</td>
                        <td></td>
                        <!-- data CPM_INFORMATION -->
                    </tr>
					<br>
					<br>
					<br>
					<tr>
                        <td></td><td></td>
                        <td>".$NAMA_KOTA_PENGESAHAN.", ".$dateNow."</td>
                    </tr>
                    <tr>
                        <td>Pemohon</td><td></td>
                        <td>Petugas Pelayanan</td>
					</tr>
					<tr>
                        <td></td><td></td>
                        <td></td>
					</tr>
                    <tr>
                        <td>".$data->CPM_WP_NAME."</td><td></td>
						<td> 
						<br>
						
						".getAuthor('nm_lengkap', $uname)."
						<hr style=\"width:120px\">
						NIP. ".getAuthor('nip', $uname)."</td>
                    </tr>
        		</table>
			</font>				
			</td>
		</tr>
	</table>
</html>";	  
	return $html;
}

function getInitData($id=""){    
    global $DBLink;	
    
	if($id == '') return getDataDefault();
	
    $qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
		return getDataDefault();
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'],8,2).'-'.substr($row['CPM_DATE_RECEIVE'],5,2).'-'.substr($row['CPM_DATE_RECEIVE'],0,4);
			return $row;
        }                
    }
}

function getDataDefault(){
	$default = array('CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '', 
	'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '', 
	'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '');
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q); 
$q = $json->decode($q);

$v = count($q);

$NOP = "";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(50, 4, 50);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 0);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('helvetica', 'B', 20);

// add a page
//$pdf->AddPage();

//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions=array('modify'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null);
$HTML = "";
$names = null;
for ($i = 0; $i < $v; $i++) {	
	$uname = $q[$i]->uname;
	$idssb = $q[$i]->svcId;
	$appId = $q[$i]->appId;
	$initData = getInitData($idssb);
	//echo $appId;exit;
	$fileLogo =  getConfigValue($appId,'LOGO_CETAK_PDF');
	//echo $fileLogo;exit;
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb, $initData, $fileLogo);
	$pdf->Image($sRootPath.'image/'.$fileLogo, 56, 6, 20, '', '', '', '', false, 300, '', false);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	//echo $sRootPath.'image/'.$fileLogo;
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb, $initData, $fileLogo);
	$pdf->Image($sRootPath.'image/'.$fileLogo,56, 162, 20, '', '', '', '', false, 300, '', false);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	//echo $sRootPath.'image/'.$fileLogo;
	$pdf->SetAlpha(0.3);
	if($i > 0){
		$names .= "-".$idssb;
	}
	else{
		$names .= $idssb;
	}
}
/*for ($i=0;$i<$v;$i++) {
	$idssb = $q[$i]->id;
	$uname = "";//$q[0]->uname;
	$draf = $q[$i]->draf;
	$appID = base64_decode($q[$i]->axx);
	$fileLogo =  getConfigValue("1",'FILE_LOGO');
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	$pdf->Image($sRootPath.'view/Registrasi/configure/logo/'.$fileLogo, 5, 20, 40, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	if ($draf == 1) $pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	else if ($draf == 0) $pdf->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	$HTML = getHTML($idssb);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	$pdf->Image($sRootPath.'view/Registrasi/configure/logo/'.$fileLogo, 5, 20, 40, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	if ($draf == 1) $pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	else if ($draf == 0) $pdf->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
}
*/

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($names.'.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
