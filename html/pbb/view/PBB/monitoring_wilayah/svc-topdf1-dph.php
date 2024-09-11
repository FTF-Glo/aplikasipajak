<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

date_default_timezone_set('Asia/Jakarta');

/** PHPExcel */
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once("dbMonitoringDph.php");

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

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$q              = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p              = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml            = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$thn2           = @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : 1;
$nop            = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na             = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status         = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$total          = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;

// exit;

$nmFile         = "Data-WP-Sudah-Bayar";
if ($status == 2) {
    $nmFile = "Data-WP-Belum-Bayar";
}

$tempo1         = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2         = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan      = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan      = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$tagihan        = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export         = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$bank           = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$DPH            = @isset($_REQUEST['noDph']) ? $_REQUEST['noDph'] : "0";

if ($q == "") exit(1);

$q              = base64_decode($q);
$j              = $json->decode($q);
$uid            = $j->uid;
$area           = $j->a;
$moduleIds      = $j->m;

$host           = $_REQUEST['GW_DBHOST'];
$port           = $_REQUEST['GW_DBPORT'];
$user           = $_REQUEST['GW_DBUSER'];
$pass           = $_REQUEST['GW_DBPWD'];
$dbname         = $_REQUEST['GW_DBNAME'];

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
if ($DPH != "" && $DPH != "0")
    array_push($arrWhere, "B.NO_DPH LIKE '{$DPH}%'");
$where = implode(" AND ", $arrWhere);
$data;
//echo "qqqqqqqqqqqqqqq";
if (stillInSession($DBLink, $json, $sdata)) {
    global $data;

    $monPBB = new dbMonitoringDph($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    if ($p == 'all') {
        $monPBB->setRowPerpage($total);
        $monPBB->setPage(1);
    } else {
        $monPBB->setRowPerpage(10000);
        $monPBB->setPage($p);
    }

    // $sql_table = "PBB_SPPT A";
    // $sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
    // OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
    // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
    // IFNULL(A.PBB_DENDA,0) as DENDA ,
    // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+A.PBB_DENDA,0) as JUMLAH ";

    $sql_table = "PBB_SPPT A JOIN cppmod_pbb_dph_DETAIL B ON  A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.TAHUN";
    $sql_select = " SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        IFNULL(A.PBB_DENDA,0) as DENDA ,
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH ";

    $monPBB->setTable($sql_table);
    $monPBB->setWhere($where);
    $monPBB->query($sql_select);
    $result = $monPBB->query_result($sql_select);
    // echo "AS"; 
    // print_r($result);
    // print_r(mysqli_fetch_assoc($result['data']));
    // echo"0000";
    // exit;


    $i     = 0;
    $data  = array();
    while ($rows  = mysqli_fetch_assoc($result['data'])) {
        $data[$i]['NOP']        = $rows['NOP'];
        $data[$i]['TAHUN']      = $rows['TAHUN'];
        $data[$i]['NAMA']       = $rows['WP_NAMA'];
        $data[$i]['DESA']       = $rows['DESA_OP'];
        $data[$i]['KECAMATAN']  = $rows['KECAMATAN_OP'];
        $data[$i]['PBB']        = $rows['PBB_TERHUTANG'];
        $data[$i]['DENDA']      = $rows['DENDA'];
        $data[$i]['JUMLAH']     = $rows['JUMLAH'];
        $i++;
    }
    // var_dump($data);
    // exit;

    //  return $data;

} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

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

    public function setMaxData($num)
    {
        global $sumRows;
        $sumRows = $num;
    }

    public function judul()
    {
        global $appConfig;

        // $prop = $_REQUEST['prop'];
        // $kota = $_REQUEST['kota'];
        // $kec = $_REQUEST['kec'];
        // $kel = $_REQUEST['kel'];

        // $kd_prop = substr($_REQUEST['kd_prop'],-2);
        // $kd_kota = substr($_REQUEST['kd_kota'],-2);
        // $kd_kec = substr($_REQUEST['kd_kec'],-3);
        // $kd_kel = substr($_REQUEST['kd_kel'],-3);

        // $blok_awal = substr($_REQUEST['blok_awal'],-3);
        // $blok_akhir = substr($_REQUEST['blok_akhir'],-3);

        $tempo1         = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
        $tempo2         = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
        $kecamatan      = @isset($_REQUEST['kc']) && $_REQUEST['kc'] != undefined ? $_REQUEST['kc'] : "";
        $kelurahan      = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "-";
        $tagihan        = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "all";
        $export         = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
        $bank           = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
        $thn            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
        $kl_text        = @isset($_REQUEST['kl_text']) ? $_REQUEST['kl_text'] : 1;
        $buku_text      = @isset($_REQUEST['buku_text']) ? $_REQUEST['buku_text'] : 1;

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
$pdf->setMaxData(sizeof($data));
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

    for ($x = 0; $x < 20; $x++) {

        if (!isset($data[$i])) break;
        $CPM_NOP = substr($data[$i]['NOP'], 10, 3) . "-" . substr($data[$i]['NOP'], 13, 4) . "." . substr($data[$i]['NOP'], 17, 1);
        $html .= "<tr>
            <td>" . ($i + 1) . "</td>
            <td>" . $data[$i]['NOP'] . "</td>
            <td>" . $data[$i]['TAHUN'] . "</td>
            <td align=\"left\">" . $data[$i]['NAMA'] . "</td>
            <td align=\"left\">" . $data[$i]['DESA'] . "</td>
            <td align=\"left\">" . $data[$i]['KECAMATAN'] . "</td>
            <td align=\"right\">" . $data[$i]['PBB'] . "</td>
            <td align=\"right\">" . $data[$i]['DENDA'] . "</td>
            <td align=\"right\">" . $data[$i]['JUMLAH'] . "</td>
        </tr>";
        $i++;
    }

    $flag = (sizeof($data) == $i) ? false : true;
    //$flag =false;
    $html .= "</table>";
    $pdf->AddPage('L', 'A4');
    $pdf->writeHTML($html, true, false, false, false, '');
} while ($flag == true);
$pdf->Output('DPH Harian.pdf', 'D');
