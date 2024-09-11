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
require_once("pengurangan-lib.php");

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

	$query = "SELECT S.*,R.CPM_RE_SK_NUMBER, R.CPM_RE_LHP_NUMBER, P.CPM_PNG_PERSEN, P.CPM_PNG_NILAI, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN, DATE_FORMAT(R.CPM_RE_LHP_DATE,'%d-%m-%Y') AS LHP_DATE, DATE_FORMAT(S.CPM_DATE_RECEIVE,'%d-%m-%Y') AS TGL_MASUK
			FROM cppmod_pbb_services S
			JOIN cppmod_pbb_service_reduce R
                        JOIN cppmod_pbb_sppt_pengurangan P
			JOIN cppmod_tax_kecamatan TKEC
			JOIN cppmod_tax_kelurahan TKEL
			WHERE S.CPM_ID=R.CPM_RE_SID 
			AND TKEC.CPC_TKC_ID = S.CPM_OP_KECAMATAN
			AND TKEL.CPC_TKL_ID = S.CPM_OP_KELURAHAN
                        AND P.CPM_PNG_NOP=S.CPM_OP_NUMBER AND P.CPM_PNG_TAHUN = S.CPM_SPPT_YEAR
			AND CPM_ID='$idssb'";

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

function getHTML($idssb, $initData, $fileLogo)
{
	global $uname, $NOP, $appId;
	$data 			= getData($idssb);
	$kota 			= getConfigValue($appId, 'NAMA_KOTA');
	$kotaPengesahan	= ucfirst(strtolower(getConfigValue($appId, 'NAMA_KOTA_PENGESAHAN')));
	$pejabatSK 		= getConfigValue($appId, 'PEJABAT_SK');
	$namaPejabatSK	= getConfigValue($appId, 'NAMA_PEJABAT_SK');
	$date			= tgl_indo(date("d-m-Y"));
	$kabkot			= getConfigValue($appId, 'C_KABKOT');
	$rReduce 		= $data->CPM_PNG_NILAI;
	$finalReduce 	= number_format(($data->CPM_SPPT_DUE - $rReduce), 0, '', '');
	$otomatis		= getConfigValue($appId, 'NOMOR_SK_OTOMATIS');
	$SKFormat		= getConfigValue($appId, 'NOMOR_SK_FORMAT');
	$SKNumber		= $data->CPM_RE_SK_NUMBER;
	$html = "
	<html>
<body>
	<table border=\"0\" width=\"650\" cellpadding=\"10\">
		<tr>
			<td align=\"center\" width=\"130\"></td>
			<td align=\"center\" colspan = \"2\" width=\"520\" height=\"120\">
			</td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><b>KEPUTUSAN " . $pejabatSK . "<br>NOMOR : " . (!empty($SKNumber) ? $SKNumber : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $SKFormat) . " </b></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><b>TENTANG<br>PENGURANGAN PAJAK BUMI DAN BANGUNAN PERKOTAAN</b></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><b>" . $pejabatSK . "</b></td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"130\" rowspan=\"2\">Menimbang :</td>
			<td valign=\"top\" width=\"40\">a.</td>
			<td align=\"justify\" width=\"480\">
			<!-- &nbsp;&nbsp;&nbsp;&nbsp; -->bahwa sehubungan dengan Surat Permohonan Pengurangan Pajak Bumi dan Bangunan Perkotaan, atas nama Wajib Pajak <b>" . $data->CPM_WP_NAME . "</b><br>
			<!--&nbsp;&nbsp; -->Nomor : " . $data->CPM_ID . " Tanggal <i>" . tgl_indo($data->TGL_MASUK) . "</i> atas SPPT PBB NOP <i>" . $data->CPM_OP_NUMBER . "</i> Tahun Pajak " . $data->CPM_SPPT_YEAR . " yang diterima oleh petugas dan dengan mempertimbangkan hasil penelitian yang dituangkan dalam Laporan Hasil Penelitian Pengurangan PBB<br>
			<!--&nbsp;&nbsp;&nbsp;&nbsp; -->Nomor : " . $data->CPM_RE_LHP_NUMBER . " Tanggal <i>" . tgl_indo($data->LHP_DATE) . "</i> perlu diterbitkan keputusan atas permohonan pengurangan PBB dimaksud;
			</td>
		</tr>
		<tr>
			<td valign=\"top\">b.</td>
			<td align=\"justify\">bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a, perlu menetapkan Keputusan Walikota Palembang tentang Pengurangan Pajak Bumi dan Bangunan Perkotaan;</td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"130\" rowspan=\"4\">Mengingat :</td>
			<td valign=\"top\" width=\"40\">1.</td>
			<td align=\"justify\" width=\"480\">Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah (Lembaran Negara Republik Indonesia Tahun 2009 Nomor 130, Tambahan Lembaran Negara Republik Indonesia Nomor 5049).</td>
		</tr>
		<tr>
			<td valign=\"top\">2.</td>
			<td align=\"justify\">Peraturan Menteri Keuangan Nomor 110 Tahun 2009 tentang Pemberian Pengurangan Pajak Bumi dan Bangunan.</td>
		</tr>
		<tr>
			<td valign=\"top\">3.</td>
			<td align=\"justify\">Peraturan Daerah Kota Palembang Nomor 3 Tahun 2011 tentang Pajak Bumi dan Bangunan Perkotaan (Lembaran Daerah Kota Palembang Tahun 2011 Nomor 3 Seri B).</td>
		</tr>
		<tr>
			<td valign=\"top\">4.</td>
			<td align=\"justify\">Peraturan Walikota Palembang Nomor 12.a Tahun 2013 tentang Tata Cara Pemberian Pengurangan dan Penyelesaian Keberatan Pajak Bumi dan Bangunan Perkotaan.</td>
		</tr>
	</table>
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<table border=\"0\" width=\"650\" cellpadding=\"10\">
		<tr>
			<td align=\"center\" colspan=\"4\"><b>MEMUTUSKAN:</b><br><br>
			</td>
		</tr>
		<tr>
			<td align=\"left\" colspan=\"4\">Menetapkan:
			</td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"100\">KESATU</td>
			<td valign=\"top\" width=\"40\">:</td>
			<td align=\"justify\" colspan=\"2\" width=\"510\">Mengabulkan seluruhnya / Mengabulkan sebagian / Menolak *) permohonan pengurangan PBB terutang yang tercantum dalam SPPT PBB NOP <i>" . $data->CPM_OP_NUMBER . "</i> Tahun Pajak " . $data->CPM_SPPT_YEAR . " sebagai berikut :<br>
				a. Wajib Pajak<br>
				&nbsp;&nbsp;&nbsp;&nbsp;<table border=\"0\">
					<tr>
						<td width=\"120\">Nama</td><td width=\"5\">:</td><td>" . $data->CPM_REPRESENTATIVE . "</td>
					</tr>
					<tr>
						<td>Alamat</td><td>:</td><td>" . $data->CPM_WP_ADDRESS . "</td>
					</tr>
				</table>
				<br><br>
				b. Obyek Pajak <br>
					&nbsp;&nbsp;&nbsp;&nbsp;<table border=\"0\">
					<tr>
						<td width=\"120\">Nama</td><td width=\"5\">:</td><td width=\"300\">" . $data->CPM_WP_NAME . "</td>
					</tr>
					<tr>
						<td width=\"120\">NOP</td><td width=\"5\">:</td><td width=\"300\">" . $data->CPM_OP_NUMBER . "</td>
					</tr>
					<tr>
						<td>Alamat</td><td>:</td><td>" . $data->CPM_OP_ADDRESS . "</td>
					</tr>
					<tr>
						<td>Kelurahan</td><td>:</td><td>" . $data->CPC_TKL_KELURAHAN . "</td>
					</tr>
					<tr>
						<td>Kecamatan</td><td>:</td><td>" . $data->CPC_TKC_KECAMATAN . "</td>
					</tr>
					<tr>
						<td>Kota</td><td>:</td><td>" . $kota . "</td>
					</tr>
					<tr>
						<td>Sebesar</td><td>:</td><td>" . $data->CPM_PNG_PERSEN . "% (" . SayInIndonesian($data->CPM_PNG_PERSEN) . " persen) dari PBB yang terutang</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign=\"top\">KEDUA</td>
			<td valign=\"top\">:</td>
			<td align=\"justify\" colspan=\"2\">Besarnya PBB yang harus dibayar atas penetapan sebagaimana dimaksud diktum KESATU adalah sebagai berikut :<br>
				<table border=\"0\">
					<tr>
						<td width=\"15\">a.</td><td width=\"280\">PBB yang terutang menurut SPPT PBB</td><td width=\"20\">Rp</td><td align=\"right\" width=\"100\">" . number_format($data->CPM_SPPT_DUE, 0, ',', '.') . "</td>
					</tr>
					<tr>
						<td>b.</td><td colspan=\"3\">Besarnya pengurangan</td>
					</tr>
					<tr>
						<td></td><td>(" . $data->CPM_PNG_PERSEN . "% x Rp " . number_format($data->CPM_SPPT_DUE, 0, ',', '.') . ") = </td>
						<td valign=\"\">Rp</td><td align=\"right\">" . number_format($rReduce, 0, ',', '.') . "</td>
					</tr>
					<tr>
						<td>c.</td><td>Jumlah PBB yang terutang setelah pengurangan</td><td>Rp</td><td align=\"right\">" . number_format($finalReduce, 0, ',', '.') . "</td>
					</tr>
					<tr>
						<td></td><td colspan=\"3\">(terbilang : " . SayInIndonesian($finalReduce) . " rupiah)</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign=\"top\" rowspan=\"2\">KETIGA</td>
			<td valign=\"top\" rowspan=\"2\">:</td>
			<td valign=\"top\" width=\"35\">a.</td>
			<td align=\"justify\" valign=\"top\" width=\"475\">Keputusan ini mulai berlaku pada tanggal ditetapkan dan apabila ternyata terdapat kekeliruan akan diadakan perubahan dan perbaikan sebagaimana mestinya.
			</td>
		</tr>
		<tr>
			<td valign=\"top\">b.</td>
			<td valign=\"top\">Asli keputusan ini disampaikan kepada Wajib Pajak dan Salinan Keputusan ini disimpan sebagai arsip Dinas Pendapatan Daerah.</td>
		</tr>
		<tr>
			<td colspan=\"4\" align=\"left\">
				<table border=\"0\">
				<tr>
					<td width=\"200\"></td>
					<td width=\"200\"></td>
					<td width=\"250\">
						Ditetapkan di " . $kotaPengesahan . "<br>
						pada tanggal ........... <br>
						<br>
						<b>" . $pejabatSK . "</b><br>
						<br>
						<br>
						<br>
						<br>
						<br>
						<b>" . $namaPejabatSK . "</b><br>
					</td>
				</tr>
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

$NOP = ""; //$initData['CPM_OP_NUMBER'];
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
$fileLogo =  getConfigValue($appId, 'LOGO_CETAK_SK_WALKOT');
//echo $fileLogo;exit;
$pdf->AddPage('P', 'F4');
//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
$HTML = getHTML($idssb, $initData, $fileLogo);
$pdf->Image($sRootPath . 'image/' . $fileLogo, 90, 20, 30, '', '', '', '', false, 300, '', false);
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
