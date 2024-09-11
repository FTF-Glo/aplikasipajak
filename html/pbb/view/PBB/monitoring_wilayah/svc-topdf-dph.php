<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
$where;
global $DBLink;
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

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
// require_once("dbMonitoringDph.php");

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

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 1);

$setting    = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);


$q              = @isset($_POST['q']) ? $_POST['q'] : "";
$p              = @isset($_POST['p']) ? $_POST['p'] : 1;
$jml            = @isset($_POST['j']) ? $_POST['j'] : 1;
$thn            = @isset($_POST['th']) ? $_POST['th'] : 1;
$thn2           = @isset($_POST['th2']) ? $_POST['th2'] : 1;
$nop            = @isset($_POST['n']) ? $_POST['n'] : "";
$na             = @isset($_POST['na']) ? str_replace("%20", " ", $_POST['na']) : "";
$status         = @isset($_POST['st']) ? $_POST['st'] : "";
$total          = @isset($_POST['total']) ? $_POST['total'] : 0;

// print_r($q );
// exit;

$nmFile         = "Data-WP-Belum-Bayar";

$tempo1         = @isset($_POST['t1']) ? $_POST['t1'] : "";
$tempo2         = @isset($_POST['t2']) ? $_POST['t2'] : "";
$kecamatan      = @isset($_POST['kc']) ? $_POST['kc'] : "";
$kelurahan      = @isset($_POST['kl']) ? $_POST['kl'] : "";
$tagihan        = @isset($_POST['tagihan']) ? $_POST['tagihan'] : "0";
$export         = @isset($_POST['exp']) ? $_POST['exp'] : "";
$bank           = @isset($_POST['bank']) ? $_POST['bank'] : "0";

if ($q == "") exit(1);

$q              = base64_decode($q);
$j              = $json->decode($q);
$uid            = $j->uid;
$area           = $j->a;
$moduleIds      = $j->m;


$host           = $_POST['GW_DBHOST'];
$port           = $_POST['GW_DBPORT'];
$user           = $_POST['GW_DBUSER'];
$pass           = $_POST['GW_DBPWD'];
$dbname         = $_POST['GW_DBNAME'];

$arrTempo = array();

if ($tempo1 != "") array_push($arrTempo, "A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.payment_paid<='{$tempo2} 23:59:59'");

$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();

if ($kecamatan != "" && $kecamatan != 'undefined') {
	array_push($arrWhere, "A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
}

if ($kelurahan != "") {
	array_push($arrWhere, "A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
}

if ($nop != "") array_push($arrWhere, "A.nop='{$nop}'");
if ($thn != "") array_push($arrWhere, "A.sppt_tahun_pajak between  '{$thn}' and '{$thn2}'  ");
if ($na != "") array_push($arrWhere, "A.wp_nama like '%{$na}%'");
if ($status != "") {
	array_push($arrWhere, "(A.payment_flag != 1 OR A.payment_flag IS NULL)");
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");

if ($tagihan != 0) {
	switch ($tagihan) {
		case 1:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
			break;
		case 12:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
			break;
		case 123:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
			break;
		case 1234:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
			break;
		case 12345:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
			break;
		case 2:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
			break;
		case 23:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
			break;
		case 234:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
			break;
		case 2345:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
			break;
		case 3:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
			break;
		case 34:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
			break;
		case 345:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
			break;
		case 4:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
			break;
		case 45:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
			break;
		case 5:
			array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
			break;
	}
}

if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");

// $User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
// $arConfig 	= $User->GetModuleConfig($moduleIds);
// $appConfig 	= $User->GetAppConfig($area);

//$where = implode (" AND ",$arrWhere);
// echo $DBLink;
// print_r( $json);
// print_r( $sdata);
// echo "-----------";
// 	if(stillInSession($DBLink,$json,$sdata)){    
// 	//echo "session terbaca ...";       
//     $monPBB = new dbMonitoringDph ($host,$port,$user,$pass,$dbname);
//     $monPBB->setConnectToMysql();
//         if($p == 'all'){
//             $monPBB->setRowPerpage($total);
//             $monPBB->setPage(1);
//         }else{
//             $monPBB->setRowPerpage(10000);
//             $monPBB->setPage($p);
//         }

//         $sql_table = "PBB_SPPT A";
//         $sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
//         OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
//         IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
//         IFNULL(A.PBB_DENDA,0) as DENDA ,
//         IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+A.PBB_DENDA,0) as JUMLAH ";

//         $monPBB->setTable($sql_table);
//         $monPBB->setWhere($where);      
//         $monPBB->query($sql_select);
//         $resultPdf = $monPBB->query_result($sql_select);
//          // echo $where;
//          // exit;
//         // print_r($monPBB->getAllQuery());
//         // print_r($resultPdf);
//         //  print_r($resultPdf['data']);
//        //	$rowt = mysql_result($resultPdf['data']);
//       //  	var_dump($rowt);
//       //   // print_r(mysqli_fetch_assoc($result['data']));
//       //   echo "+++++";
//       //   echo json_encode($rowt);	
//       // exit;
//      var_dump($DBLink);
//      echo "-^^^^^^";
//      $queryy = $monPBB->getAllQuery1();
//      echo $queryy;
//      $res = mysql_query ($queryy,$DBLink);
//      var_dump($res);
//      echo "-^^***********************";
//      exit;
// 	// if ($res === false) {
// 	// 	echo mysqli_error($DBLink);
// 	// 	exit();
// 	// }

//     $data  = array();
// 	$i     = 0;
// 	while($rows  = mysqli_fetch_assoc($result['data'])){
// 		$data[$i]['NOP'] 			= $rows['CPM_NOP'];
// 		$data[$i]['NAMA'] 			= $rows['CPM_WP_NAMA'];
// 		$data[$i]['ALAMAT'] 		= $rows['CPM_OP_ALAMAT'];
// 		$data[$i]['RT'] 			= $rows['CPM_OP_RT'];
// 		$data[$i]['RW'] 			= $rows['CPM_OP_RW'];
// 		$data[$i]['LUAS_TANAH'] 	= $rows['CPM_OP_LUAS_TANAH'];
// 		$data[$i]['LUAS_BANGUNAN'] 	= $rows['CPM_OP_LUAS_BANGUNAN'];
// 		$data[$i]['ZNT'] 			= $rows['CPM_OT_ZONA_NILAI'];
// 		$data[$i]['NJOP_TANAH']		= $rows['CPM_NJOP_TANAH'];
// 		$data[$i]['NJOP_BANGUNAN']	= $rows['CPM_NJOP_BANGUNAN'];

// 		$i++;
// 	}
// }else{
//     echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
// }



function getData()
{

	global $DBLink;

	// $nop = $_POST['nop'];
	// $blok_awal = $_POST['blok_awal'];
	// $blok_akhir = $_POST['blok_akhir'];

	//$where = empty($nop)? '' : sprintf("AND CPM_NOP = '%s'", $nop);
	$query = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA, OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG, IFNULL(A.PBB_DENDA,0) as DENDA , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+A.PBB_DENDA,0) as JUMLAH FROM PBB_SPPT A WHERE A.OP_KELURAHAN_KODE like '3205210018%' AND A.sppt_tahun_pajak between '2017' and '2017' AND (A.payment_flag != 1 OR A.payment_flag IS NULL) AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
	echo $query;
	$res = mysqli_query($DBLink, $query);
	echo "---";
	var_dump($res);
	echo "-111--";
	exit;
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

// /* inisiasi parameter */
// $q = @isset($_POST['q']) ? $_POST['q'] : "";

// $q = base64_decode($q);
// $q = $json->decode($q);

// $a = $q->a;
// $m = $q->m;
// $n = $q->n;



$data = getData();
//$sumRows = count($data);

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

		$tempo1         = @isset($_POST['t1']) ? $_POST['t1'] : "";
		$tempo2         = @isset($_POST['t2']) ? $_POST['t2'] : "";
		$kecamatan      = @isset($_POST['kc']) && $_POST['kc'] != undefined ? $_POST['kc'] : "";
		$kelurahan      = @isset($_POST['kl']) ? $_POST['kl'] : "-";
		$tagihan        = @isset($_POST['tagihan']) ? $_POST['tagihan'] : "all";
		$export         = @isset($_POST['exp']) ? $_POST['exp'] : "";
		$bank           = @isset($_POST['bank']) ? $_POST['bank'] : "0";
		$thn            = @isset($_POST['th']) ? $_POST['th'] : 1;
		$kl_text        = @isset($_POST['kl_text']) ? $_POST['kl_text'] : 1;
		$buku_text      = @isset($_POST['buku_text']) ? $_POST['buku_text'] : 1;

		$html = "<br/><br/>
		<table border=\"0\" cellpadding=\"1\">
			<tr><td align=\"center\" colspan=\"8\"><b>DAFTAR PENERIMAAN HARIAN (DPH) PAJAK BUMI DAN BANGUNAN</b></td></tr>
			<tr><td align=\"center\" colspan=\"8\"><b></b></td></tr>
			<tr><td align=\"center\" colspan=\"8\"><b></b></td></tr>
			
			<tr>
				<td colspan=\"2\"><b>KECAMATAN</b></td>
				<td colspan=\"3\"><b>: {$kecamatan}</b></td>
				<td colspan=\"1\"><b>BUKU</b></td>
				<td colspan=\"2\"><b>: {$buku_text}</b></td>
			</tr>
			
			<tr>
				<td colspan=\"2\"><b>DESA</b></td>
				<td colspan=\"3\"><b>: {$kl_text}</b></td>
				<td colspan=\"1\"><b>Ambil data belum bayar tahun</b></td>
				<td colspan=\"2\"><b>: {$thn}</b></td>
			</tr>
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
do {
	$html = "
	<table border=\"1\" cellpadding=\"2\">
		<tr>
			<td width=\"30\" align=\"center\"><b>NO</b></td>
			<td width=\"150\" align=\"center\"><b>NOMOR OBJEK<br/>PAJAK</b></td>
			<td width=\"70\" align=\"center\"><b>TAHUN PAJAK</b></td>
			<td width=\"150\" align=\"center\"><b>NAMA WAJIB PAJAK</b></td>
			<td width=\"100\" align=\"center\"><b>DESA OP</b></td>
			<td width=\"100\" align=\"center\"><b>KECAMATAN OP</b></td>
			<td width=\"120\" align=\"center\"><b>PBB TERHUTANG</b></td>
			<td width=\"140\" align=\"center\"><b>DENDA</b></td>
			<td width=\"140\" align=\"center\"><b>JUMLAH</b></td>
		</tr>";

	for ($x = 0; $x < 15; $x++) {
		if (!isset($data[$i])) break;
		$CPM_NOP = substr($data[$i]['NOP'], 10, 3) . "-" . substr($data[$i]['NOP'], 13, 4) . "." . substr($data[$i]['NOP'], 17, 1);

		$html .= "<tr>
			<td>" . ($i + 1) . "</td>
			<td>{$CPM_NOP}</td>
			<td>" . ($data[$i]['NAMA'] . "<br/>" . $data[$i]['ALAMAT']) . "</td>
			<td align=\"center\">" . ($data[$i]['RT'] . "/<br/>" . $data[$i]['RW']) . "</td>
			<td align=\"center\">" . (" " . $data[$i]['ZNT'] . " ") . "</td>
			<td align=\"right\">" . (number_format($data[$i]['LUAS_TANAH'], 0) . "<br/>" . number_format($data[$i]['LUAS_BANGUNAN'], 0)) . "</td>
			<td align=\"right\">" . (number_format($data[$i]['NJOP_TANAH'], 0) . "<br/>" . number_format($data[$i]['NJOP_BANGUNAN'], 0)) . "</td>
			<td align=\"right\">" . number_format($data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN'], 0) . "</td>
			<td align=\"right\">" . number_format($data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN'], 0) . "</td>
		</tr>";
		$i++;
	}

	$flag = ($sumRows == $i) ? false : true;
	$html .= "</table>";
	$pdf->AddPage('L', 'A4');
	$pdf->writeHTML($html, true, false, false, false, '');
} while ($flag == true);
$pdf->Output('OP Ringkas.pdf', 'I');
