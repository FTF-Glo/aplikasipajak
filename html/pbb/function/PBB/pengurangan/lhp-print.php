<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$dataNotaris = "";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getAuthor($uname)
{
	global $DBLink, $appID;
	$id = $appID;
	$qry = "select nm_lengkap from TBL_REG_USER_NOTARIS where userId = '" . $uname . "'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo mysqli_error($DBLink);
	}

	$num_rows = mysqli_num_rows($res);
	if ($num_rows == 0) return $uname;
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['nm_lengkap'];
	}
}

function getConfigValue($id, $key)
{
	global $DBLink;
	//$id= $appID;
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";

	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}


function mysql2json($mysql_result, $name)
{
	$json = "{\n'$name': [\n";
	$field_names = array();
	$fields = mysqli_num_fields($mysql_result);
	for ($x = 0; $x < $fields; $x++) {
		$field_name = mysqli_fetch_field($mysql_result);
		if ($field_name) {
			$field_names[$x] = $field_name->name;
		}
	}
	$rows = mysqli_num_rows($mysql_result);
	for ($x = 0; $x < $rows; $x++) {
		$row = mysqli_fetch_array($mysql_result);
		$json .= "{\n";
		for ($y = 0; $y < count($field_names); $y++) {
			$json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
			if ($y == count($field_names) - 1) {
				$json .= "\n";
			} else {
				$json .= ",\n";
			}
		}
		if ($x == $rows - 1) {
			$json .= "\n}\n";
		} else {
			$json .= "\n},\n";
		}
	}
	$json .= "]\n}";
	return ($json);
}

function getData($idssb)
{
	global $data, $DBLink, $dataNotaris;

	$query = "SELECT S.*, R.*, DATE_FORMAT(R.CPM_RE_LHP_DATE,'%d-%m-%Y') AS LHP_DATE, DATE_FORMAT(S.CPM_DATE_RECEIVE,'%d-%m-%Y') AS TGL_MASUK 
			FROM cppmod_pbb_services S
			JOIN cppmod_pbb_service_reduce R
			WHERE S.CPM_ID=R.CPM_RE_SID 
			AND CPM_ID='$idssb'";
	//echo $query;exit;
	$res = mysqli_query($DBLink, $query);
	$record = mysqli_num_rows($res);
	if ($record < 1) {
		echo "Data tidak ada!";
		exit;
	}
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res, "data"));
	$dt = $dataNotaris->data[0];
	return $dt;
}

function getDataLHP($idssb)
{
	global $data, $DBLink;

	$query = "SELECT *, DAYOFWEEK(CPM_LHP_DATE) AS HARI FROM cppmod_pbb_service_lhp WHERE CPM_LHP_SID = '$idssb'";

	$res = mysqli_query($DBLink, $query);
	$record = mysqli_num_rows($res);
	if ($record < 1) {
		echo "Data tidak ada!";
		exit;
	}
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataLHP =  $json->decode(mysql2json($res, "data"));
	$dat = $dataLHP->data[0];
	return $dat;
}

function getLHPPetugas($idssb = "")
{
	global $DBLink;
	$qry = "SELECT * FROM cppmod_pbb_service_lhp_petugas WHERE CPM_LHP_PE_SID='$idssb' ORDER BY CPM_LHP_PE_URUTAN";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else
		$arr = array();
	while ($row = mysqli_fetch_array($res)) {
		$arr[] = $row;
	}
	//print_r($arr);exit;		
	return $arr;
}

function getHTML($idssb, $initData, $fileLogo)
{
	global $uname, $NOP, $appId, $DBLink, $namaPejabat, $nipPejabat, $jabatanPejabat;
	$data 			= getData($idssb);
	// echo "<pre>";
	// print_r($data); exit;
	$dataLHP		= getDataLHP($idssb);
	$dataLHPP		= getLHPPetugas($idssb);
	$tglP			= SayInIndonesian(substr($dataLHP->CPM_LHP_DATE, 8, 2));
	$blnP			= (int)substr($dataLHP->CPM_LHP_DATE, 5, 2);
	$thnP			= substr($dataLHP->CPM_LHP_DATE, 0, 4);
	$header			= getConfigValue($appId, 'C_HEADER_SK');
	$hrP			= $dataLHP->HARI;
	$bulan	        = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
	$tglSkr			= date('j');
	$blnSkr			= $bulan[date('n') - 1];
	$thnSkr			= date('Y');
	$WktSkr			= $tglSkr . " " . $blnSkr . " " . $thnSkr;
	$hari			= array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
	$hasilEx  		= explode("##", $dataLHP->CPM_LHP_RESULT);
	$hasil			= "";
	$otomatis		= getConfigValue($appId, 'NOMOR_LHP_OTOMATIS');
	$formatLHP		= getConfigValue($appId, 'FORMAT_NOMOR_LHP');
	$LHPNumber      = $data->CPM_RE_LHP_NUMBER;
	//$last			= end($hasilEx);	
	foreach ($hasilEx as $val) {
		//if($val != $last){
		$hasil .= "<tr><td width=\"20\" align=\"justify\">-</td><td width=\"614\">" . $val . "</td></tr>";
		//} else 
		//$hasil .= "- ".$val;
	}
	//$hasil .= "<tr><td>-</td><td>WP menerima gaji / honor / penghasilan sebesar Rp .................................... / bulan</td></tr>";
	//$hasil .= "<tr><td>-</td><td>Data pendukung lengkap dan sesuai</td></tr>";
	//$hasil .= "<tr><td>-</td><td>Berkas permohonan memenuhi syarat untuk diproses dan diberikan pengurangan</td></tr>";

	$petugas 		= "";
	$ttdPetugas		= "";
	$i 				= 1;
	//$lastP			= end($dataLHPP);
	foreach ($dataLHPP as $v) {
		$petugas 	.= "<tr><td>" . $i . ". " . $v['CPM_LHP_PE_NAMA'] . "</td><td>" . $v['CPM_LHP_PE_JABATAN'] . "</td><td>" . $v['CPM_LHP_PE_NIP'] . "</td></tr>";
		$ttdPetugas .= "<tr><td width=\"265\" align=\"left\">" . $i . ". " . $v['CPM_LHP_PE_NAMA'] . "</td><td width=\"65\" align=\"right\">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td></tr>";
		$i++;
	}

	$html = "
	<html>
<body>
	<table border=\"0\" width=\"650\" cellpadding=\"3\">
		<tr>
			<td align=\"center\" width=\"130\"></td>
			<td align=\"center\" colspan = \"2\" width=\"520\">" . $header . "
			</td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><hr style=\"height: 2px\"></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><font size=\"15\"><u>Laporan Hasil Penelitian</u></font><br>Nomor : " . ($otomatis == "1" ? $LHPNumber : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $formatLHP) . "</td>
		</tr>
		<tr>
			<td colspan=\"3\"></td>
		</tr>
		<tr>
			<td align=\"left\" colspan=\"3\"><dd>Pada Hari <i>" . $hari[$hrP - 1] . "</i>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal <i>" . $tglP . "</i>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bulan <i>" . $bulan[$blnP - 1] . "</i>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tahun " . $thnP . "</dd></td>
		</tr>
		<tr>
			<td colspan=\"3\">
			<table border=\"0\" cellpadding=\"5\">
				<tr align=\"left\">
					<td width=\"300\">NAMA</td><td width=\"190\">JABATAN</td><td width=\"150\">NIP</td>
				</tr>
				" . $petugas . "
			</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"3\"><dd>Dengan ini telah dilakukan Penelitian terhadap Permohonan Pengurangan / Keberatan Wajib Pajak:</dd></td>
		</tr>
		<tr>
			<td colspan=\"3\">
			<table>
				<tr>
					<td width=\"60\">Nama</td><td width=\"8\">:</td><td width=\"550\">" . $data->CPM_REPRESENTATIVE . "</td>
				</tr>
				<tr>
					<td>Alamat</td><td>:</td><td>" . $data->CPM_WP_ADDRESS . "</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"3\"><dd>Dengan objek pajak sebagai berikut:</dd></td>
		</tr>
		<tr>
			<td colspan=\"3\">
			<table>
				<tr>
					<td width=\"60\">Nama</td><td width=\"8\">:</td><td width=\"550\">" . $data->CPM_WP_NAME . "</td>
				</tr>
				<tr>
					<td>NOP</td><td>:</td><td>" . $data->CPM_OP_NUMBER . "</td>
				</tr>
				<tr>
					<td>Alamat</td><td>:</td><td>" . $data->CPM_OP_ADDRESS . "</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"3\"><dd>Dengan Hasil Pemeriksaan sebagai berikut:</dd></td>
		</tr>
		<tr>
			<td colspan=\"3\">
				<table border=\"0\">
					" . $hasil . "
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"3\"><dd>Dengan Laporan Hasil Penelitian ini dibuat dengan sebenarnya.</dd></td>
		</tr>
		<tr>
			<td colspan=\"3\">
				<table border=\"0\" width=\"730\">
				<tr>
					<td width=\"200\"></td>
					<td width=\"100\"></td>
					<td align=\"right\" width=\"auto\">
						<div align=\"center\">
						" . ucfirst(strtolower(getConfigValue($appId, 'NAMA_KOTA_PENGESAHAN'))) . ", &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br><br>
						<br>Yang Membuat<br>
						Laporan<br><br>
						Petugas<br>
						<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
							" . $ttdPetugas . "
						</table>
						</div>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"3\">- &nbsp;&nbsp;&nbsp;&nbsp;Kolom Persetujuan: Setuju / Tidak untuk diberikan pengurangan sebesar ...... %</td>
		</tr>
		<tr>
			<td colspan=\"3\" align=\"center\"><br><br><br><br><br>
				<table border=\"0\">
					<tr><td width=\"222\"></td><td width=\"200\">Mengetahui/Menyetujui,</td><td width=\"222\"></td></tr>
					<tr><td><br><br><br><br><br><br><br></td><td align=\"left\">Kepala Bidang PBB</td><td></td></tr>
					<tr><td></td><td align=\"left\">" . $namaPejabat . "</td><td></td></tr>
					<tr><td></td><td align=\"left\">" . $jabatanPejabat . "</td><td></td></tr>
					<tr><td></td><td align=\"left\">NIP. " . $nipPejabat . "</td><td></td></tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	";
	return $html;
}

function getInitData($id = "")
{
	global $DBLink;

	if ($id == '') return getDataDefault();

	$qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
		return getDataDefault();
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
			return $row;
		}
	}
}

function getDataDefault()
{
	$default = array(
		'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
		'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
		'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
	);
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$idssb = $q->svcId;
$appId = $q->appId;
$initData = getInitData($idssb);

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
$pdf->SetMargins(12, 14, 12);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(5);

//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

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
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";


//echo $appId;exit;
$fileLogo =  getConfigValue($appId, 'LOGO_CETAK_SK');
$namaPejabat =  getConfigValue($appId, 'KABID_NAMA');
$nipPejabat =  getConfigValue($appId, 'KABID_NIP');
$jabatanPejabat =  getConfigValue($appId, 'KABID_JABATAN');
//echo $fileLogo;exit;
$pdf->AddPage('P', 'F4');
//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
$HTML = getHTML($idssb, $initData, $fileLogo);
$pdf->Image($sRootPath . 'image/' . $fileLogo, 30, 13, 28, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($HTML, true, false, false, false, '');
//echo $sRootPath.'image/'.$fileLogo;

$pdf->SetAlpha(0.3);

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
}
*/

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($NOP . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
