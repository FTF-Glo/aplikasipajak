<?php
// echo "123";
// exit;
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';




date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
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


function bulan($bln)
{
	$bulan = $bln;
	switch ($bulan) {
		case 1:
			$bulan = "Januari";
			break;
		case 2:
			$bulan = "Februari";
			break;
		case 3:
			$bulan = "Maret";
			break;
		case 4:
			$bulan = "April";
			break;
		case 5:
			$bulan = "Mei";
			break;
		case 6:
			$bulan = "Juni";
			break;
		case 7:
			$bulan = "Juli";
			break;
		case 8:
			$bulan = "Agustus";
			break;
		case 9:
			$bulan = "September";
			break;
		case 10:
			$bulan = "Oktober";
			break;
		case 11:
			$bulan = "November";
			break;
		case 12:
			$bulan = "Desember";
			break;
	}
	return strtoupper($bulan);
}

function getData()
{
	// exit;



	global $DBLink;

	//$nop = $_POST['nop'];
	$nop1 = $_POST['nop1'];
	$nop2 = $_POST['nop2'];
	$nop3 = $_POST['nop3'];
	$nop4 = $_POST['nop4'];
	$nop5 = $_POST['nop5'];
	$nop6 = $_POST['nop6'];
	$nop7 = $_POST['nop7'];
	$blok_awal = $_POST['blok_awal'];
	$blok_akhir = $_POST['blok_akhir'];

	//$where = empty($nop) ? '' : sprintf("AND CPM_NOP = '%s'", $nop);
	$where = null;
	//$where = empty($nop) ? '' : sprintf("AND CPM_NOP = '%s'", $nop);
	$where .= empty($nop1) ? '' : sprintf("AND SUBSTR(CPM_NOP, 1, 2) = '%s'", $nop1);
	$where .= empty($nop2) ? '' : sprintf("AND SUBSTR(CPM_NOP, 3, 2) = '%s'", $nop2);
	$where .= empty($nop3) ? '' : sprintf("AND SUBSTR(CPM_NOP, 5, 3) = '%s'", $nop3);
	$where .= empty($nop4) ? '' : sprintf("AND SUBSTR(CPM_NOP, 8, 3) = '%s'", $nop4);
	$where .= empty($nop5) ? '' : sprintf("AND SUBSTR(CPM_NOP, 11, 3) = '%s'", $nop5);
	$where .= empty($nop6) ? '' : sprintf("AND SUBSTR(CPM_NOP, 14, 4) = '%s'", $nop6);
	$where .= empty($nop7) ? '' : sprintf("AND SUBSTR(CPM_NOP, 18, 1) = '%s'", $nop7);
	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	$query = "
	SELECT A.* FROM (
		" . sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt_final 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where}
		UNION ", $blok_awal, $blok_akhir) .

		sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt_susulan 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where}
		UNION ", $blok_awal, $blok_akhir) .

		sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where} 
		", $blok_awal, $blok_akhir) . "
		
		) AS A
	ORDER BY A.CPM_NOP ASC";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($rows  = mysqli_fetch_assoc($res)) {

		$data[$i]['NOP'] 			= $rows['CPM_NOP'];
		$data[$i]['NAMA'] 			= $rows['CPM_WP_NAMA'];
		$data[$i]['ALAMAT'] 		= $rows['CPM_OP_ALAMAT'];
		$data[$i]['RT'] 			= $rows['CPM_OP_RT'];
		$data[$i]['RW'] 			= $rows['CPM_OP_RW'];
		$data[$i]['LUAS_TANAH'] 	= $rows['CPM_OP_LUAS_TANAH'];
		$data[$i]['LUAS_BANGUNAN'] 	= $rows['CPM_OP_LUAS_BANGUNAN'];
		$data[$i]['ZNT'] 			= $rows['CPM_OT_ZONA_NILAI'];
		$data[$i]['NJOP_TANAH']		= $rows['CPM_NJOP_TANAH'];
		$data[$i]['NJOP_BANGUNAN']	= $rows['CPM_NJOP_BANGUNAN'];

		$i++;
	}

	return $data;
}

/* inisiasi parameter */
// print_r($_REQUEST);
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

$data = getData();
$sumRows = count($data);

#setup print
class MYPDF extends TCPDF
{

	public function Header()
	{
		$headerData = $this->getHeaderData();
		$this->SetFont('helvetica', '', 10);
		$this->writeHTML($headerData['string']);
	}

	public function Footer()
	{
		global $sumRows;
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Jumlah Data : ' . $sumRows . ', Hal ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}

	public function judul()
	{
		global $appConfig;

		$prop = $_POST['prop'];
		$kota = $_POST['kota'];
		$kec = $_POST['kec'];
		$kel = $_POST['kel'];

		$kd_prop = substr($_POST['kd_prop'], -2);
		$kd_kota = substr($_POST['kd_kota'], -2);
		$kd_kec = substr($_POST['kd_kec'], -3);
		$kd_kel = substr($_POST['kd_kel'], -3);

		$blok_awal = substr($_POST['blok_awal'], -3);
		$blok_akhir = substr($_POST['blok_akhir'], -3);

		$html = "<br/><br/>
		<table border=\"0\" cellpadding=\"1\">
			<tr><td align=\"center\" colspan=\"8\"><b>LAPORAN DAFTAR RINGKAS OBJEK PAJAK</b></td></tr>
			<tr><td align=\"center\" colspan=\"8\"><b>URUT NOMOR OBJEK PAJAK</b></td></tr>
			<tr><td align=\"center\" colspan=\"8\"><b>( Semua Objek Terdaftar )</b></td></tr>
			
			<tr>
				<td colspan=\"5\"><b>PROVINSI : {$kd_prop} - {$prop}</b></td>
				<td colspan=\"3\"><b>KELURAHAN : {$kd_kel} - {$kel}</b></td>
			</tr>
			
			<tr>
				<td colspan=\"5\"><b>KOTA : {$kd_kota} - {$kota}</b></td>
				<td colspan=\"3\"><b>BLOK AWAL : {$blok_awal}</b></td>
			</tr>
			
			<tr>
				<td colspan=\"5\"><b>KECAMATAN : {$kd_kec} - {$kec}</b></td>
				<td colspan=\"3\"><b>BLOK AKHIR : {$blok_akhir}</b></td>
			</tr>
		</table>";
		return $html;
	}
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetFont('helvetica', '', 9);
$pdf->setHeaderData($ln = '', $lw = 0, $ht = '', $pdf->judul(), $tc = array(0, 0, 0), $lc = array(0, 0, 0));
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Op Ringkas');
$pdf->SetSubject('Alfa System');
$pdf->SetKeywords('Alfa System');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 38, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

$i = 0;
$flag = true;
$gt = 0;
do {
	$html = "
	<table border=\"1\" cellpadding=\"2\">
		<tr>
			<td width=\"30\" align=\"center\"><b>NO</b></td>
			<td width=\"150\" align=\"center\"><b>NOMOR OBJEK<br/>PAJAK</b></td>
			<td width=\"250\" align=\"center\"><b>NAMA WAJIB PAJAK<br/>ALAMAT OBJEK PAJAK</b></td>
			<td width=\"80\" align=\"center\"><b>RT/<br/>RW</b></td>
			<td width=\"100\" align=\"center\"><b>KODE<br/>ZNT</b></td>
			<td width=\"100\" align=\"center\"><b>LUAS BUMI<br/>LUAS BNG</b></td>
			<td width=\"120\" align=\"center\"><b>NJOP BUMI<br/>NJOP BNG</b></td>
			<td width=\"150\" align=\"center\"><b>JUMLAH NJOP</b></td>
		</tr>";

	for ($x = 0; $x < 15; $x++) {

		if (!isset($data[$i])) break;
		$CPM_NOP = substr($data[$i]['NOP'], 10, 3) . "-" . substr($data[$i]['NOP'], 13, 4) . "." . substr($data[$i]['NOP'], 17, 1);

		$total += $data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN'];
		$html .= "<tr>
			<td>" . ($i + 1) . "</td>
			<td>{$CPM_NOP}</td>
			<td>" . ($data[$i]['NAMA'] . "<br/>" . $data[$i]['ALAMAT']) . "</td>
			<td align=\"center\">" . ($data[$i]['RT'] . "/<br/>" . $data[$i]['RW']) . "</td>
			<td align=\"center\">" . (" " . $data[$i]['ZNT'] . " ") . "</td>
			<td align=\"right\">" . (number_format($data[$i]['LUAS_TANAH'], 0) . "<br/>" . number_format($data[$i]['LUAS_BANGUNAN'], 0)) . "</td>
			<td align=\"right\">" . (number_format($data[$i]['NJOP_TANAH'], 0) . "<br/>" . number_format($data[$i]['NJOP_BANGUNAN'], 0)) . "</td>
			<td align=\"right\">" . number_format($data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN'], 0) . "</td>
		</tr>";
		$i++;
	}
	// $gt += $total;
	$flag = ($sumRows == $i) ? false : true;
	if ($flag == false) {
		$html .= "
		<tr>
			<td align=\"left\" colspan=\"7\">Total</td>
			<td align=\"right\">" . number_format($total, 0) . "</td>
		</tr>
		";
	}

	$html .= "</table>";
	$pdf->AddPage('L', 'A4');
	$pdf->writeHTML($html, true, false, false, false, '');
} while ($flag == true);
$pdf->Output('OP Ringkas.pdf', 'I');
