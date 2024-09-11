<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$DIR = "PATDA-V1";
$modul = "registrasi-wp";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}


class GETCONFIG extends Pajak {
    function __construct() {
        parent::__construct();
    }
    public function getCofig () {
        return $this->get_config_value($_REQUEST['a']);
    }
}

function print_card() {
    global $sRootPath,$DBLink;
	$pajak = new GETCONFIG() ;
 
	$config = $pajak->getCofig ();

    $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
	$JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
	$NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
	$NAMA_BADAN = $config['NAMA_BADAN_PENGELOLA'];
	$JALAN = $config['ALAMAT_JALAN'];
	$KOTA = $config['ALAMAT_KOTA'];
	$PROVINSI = $config['ALAMAT_PROVINSI'];
	$KODE_POS = $config['ALAMAT_KODE_POS'];
    $KEPALA_DINAS_NAMA = $config['KEPALA_DINAS_NAMA'];
    $NIP = $config['KEPALA_DINAS_NIP'];
    $TELEPON = $config['TELEPON'];
    $NPWPD = $_REQUEST['npwp'];
	
	$query = "select * from PATDA_WP WHERE CPM_NPWPD='{$NPWPD}' limit 0,1";
	$result = mysqli_query($DBLink, $query);
    $row = mysqli_fetch_array($result);
    $NAMA = $row['CPM_NAMA_WP'];
    $ALAMAT = $row['CPM_ALAMAT_WP'];
    
	require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
    
    $pdf = new TCPDF('l', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('vpost');
    $pdf->SetTitle('-');
    $pdf->SetSubject('-');
    $pdf->SetKeywords('-');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(5, 5, 5);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

    $pdf->AddPage('L', 'A7');
    


    $pdf->SetFillColor(255);
	$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
	$pdf->RoundedRect(4, 4, 97, 66, 2.50, '1111', 'DF');
	
	$txt = 'Using the startLayer() method you can group PDF objects into layers.
	This text is on "layer1".';

	// write somethingDINAS PENDAPATAN"
	$x = 3;
	
	$pdf->SetXY(20,$x+2);
	$pdf->SetFont('helvetica', 'B', 9);
	$pdf->Write(0, strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN), '', 00, 'C', true, 0, false, false, 0);
	$pdf->SetXY(20,($x+6));
	$pdf->SetFont('helvetica', 'B', 9);
	$pdf->Write(0, 'BADAN PENGELOLAAN KEUANGAN DAERAH', '', 00, 'C', true, 0, false, false, 0);
	$pdf->SetXY(20,($x+8));
	$pdf->SetFont('helvetica', 'B', 10);
	$pdf->Write(0, '', '', 00, 'C', true, 0, false, false, 0);
	$pdf->SetXY(20,($x+13));
	$pdf->SetFont('helvetica', 'B', 8);
	$pdf->Write(0, "{$JALAN} - {$PROVINSI} {$KODE_POS}", '', 00, 'C', true, 0, false, false, 0);
	//$pdf->SetXY(20,($x+16.5));
	//$pdf->SetFont('helvetica', 'B', 8);
	//$pdf->Write(0, "{$KOTA} - {$PROVINSI} {$KODE_POS}", '', 00, 'C', true, 0, false, false, 0);
	$pdf->Line(6, ($x+21), 99, ($x+21), array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));$pdf->SetXY(25,7);
	
	$x = ($x+21);
	$pdf->SetXY(5,26);
	$pdf->SetFont('helvetica', 'B', 10);
	$pdf->Write(0, "KARTU NPWPD", '', 00, 'C', true, 0, false, false, 0);

	$pdf->SetXY(5,35);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, "NPWPD", '', 00, 'L', true, 0, false, false, 0);
	$pdf->SetXY(25,35);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, ": ".Pajak::formatNPWPD($NPWPD), '', 00, 'L', true, 0, false, false, 0);

	$pdf->SetXY(5,40);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, "Nama", '', 00, 'L', true, 0, false, false, 0);
	$pdf->SetXY(25,40);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, ": {$NAMA}", '', 00, 'L', true, 0, false, false, 0);

	$pdf->SetXY(5,45);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, "Alamat", '', 00, 'L', true, 0, false, false, 0);
	$pdf->SetXY(25,45);
	$pdf->SetFont('helvetica', '', 9);
	$pdf->Write(0, ": {$ALAMAT}", '', 00, 'L', true, 0, false, false, 0);


	// define barcode style
	$style = array(
	    'position' => 'C',
	    'align' => 'C',
	    'stretch' => false,
	    'fitwidth' => true,
	    'cellfitalign' => '',
	    'border' => false,
	    'hpadding' => 'auto',
	    'vpadding' => 'auto',
	    'fgcolor' => array(0,0,0),
	    'bgcolor' => false, //array(255,255,255),
	    'text' => true,
	    'font' => 'helvetica',
	    'fontsize' => 6,
	    'stretchtext' => 4
	);

	// PRINT VARIOUS 1D BARCODES

	// CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
	$pdf->SetXY(4,52);
	$pdf->write1DBarcode("{$NPWPD}", 'C39', '', '', 80, 16, 0.4, $style, 'N');

	//$txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    //$pdf->writeHTML($html, true, false, false, false, '');
    //$pdf->Image("{$sRootPath}style/default/logo.jpg", 7, 7, 16, '', '', '', '', false, 300, '', false);
    $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 8, 6, 12, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);

    $pdf->Output("{$NPWPD}.pdf", 'I');
}


//print_card();
//session_start();

//if (@isset($_SESSION['uname'])) {
	print_card();
//} else {
//	echo "404";
//}

?>
