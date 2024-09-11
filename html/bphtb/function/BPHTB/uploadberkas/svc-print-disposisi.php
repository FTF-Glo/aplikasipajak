<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
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
//$appConfig = $User->GetAppConfig($appID);
$arConfig = $User->GetModuleConfig('mLkt');

$dataNotaris = "";
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function getConfigValue($id, $key) {
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

function mysql2json($mysql_result, $name) {
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
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getData($idssb) {
    global $data, $DBLink, $dataNotaris;

    $query = "SELECT * FROM cppmod_ssb_berkas WHERE CPM_BERKAS_ID='$idssb'";

    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $dataNotaris = $json->decode(mysql2json($res, "data"));
    $dt = $dataNotaris->data[0];
    return $dt;
}

function getHTML($idssb, $initData, $fileLogo) {
    global $uname, $NOP, $appId, $arConfig;
    $data = getData($idssb);
    //print_r($data);
    $data->CPM_BERKAS_HARGA_TRAN = (int) $data->CPM_BERKAS_HARGA_TRAN;
    //echo $fileLogo;exit;
    //print_r($initData); exit;
    $lampiran = $data->CPM_BERKAS_LAMPIRAN;
    $header_berkas = getConfigValue($appId, 'C_HEADER_DISPOSISI');
    $alamat_berkas = getConfigValue($appId, 'C_ALAMAT_DISPOSISI');

    $buktiTitle = "BUKTI PENERIMAAN / LEMBAR EKSPEDISI BERKAS BPHTB";

    $parse1 = "";
    //Koordinator Pendataan
    $bKoorPend = "<td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<table border=\"0\" cellspacing=\"3\" width=\"250\">
					<tr><td align=\"center\">Petugas Input Data</td></tr>
					<tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td align=\"left\">___________________________________<br />NIP : </td></tr>
					</table>
				</td>";
    //Koordinator Penilaian			
    $bKoorPen = "<td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<table border=\"0\" cellspacing=\"3\" width=\"250\">
					<tr><td align=\"center\">Petugas Persetujuan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>___________________________________<br />NIP : </td></tr>
					</table>
				</td>";



    $openTable = "<table border=\"1\" cellpadding=\"12\">
					<tr>";
    $closeTable = " </tr></table>";

    $parse1 = $bKoorPend . $bKoorPen;

    $parse2 = "<td><table border=\"1\" cellpadding=\"2\" width=\"100%\">
<tr>
	<td rowspan=\"2\" align=\"center\" width=\"30\">No</td>
	<td rowspan=\"2\" align=\"center\" width=\"160\">Petugas</td>
	<td colspan=\"2\" align=\"center\">Berkas Masuk</td>
	<td colspan=\"2\" align=\"center\">Berkas Keluar</td>
	<td rowspan=\"2\" align=\"center\">Keterangan</td>
</tr>
<tr>
	<td align=\"center\">Tanggal/Pukul</td>
	<td align=\"center\">Paraf</td>
	<td align=\"center\">Tanggal/Pukul</td>
	<td align=\"center\">Paraf</td>
</tr>
<tr>
	<td align=\"right\">1.</td>
	<td>Petugas Loket Penerimaan</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td align=\"right\">2.</td>
	<td>Koordinator Verifikasi</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td align=\"right\">3.</td>
	<td>Petugas Input Data</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td align=\"right\">4.</td>
	<td>KASI BPHTB</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td align=\"right\">5.</td>
	<td>KABID PBB dan BPHTB</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td align=\"right\">6.</td>
	<td>Petugas Loket Pengembalian</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
</table></td>";

    $lampiran = array();

    $jnsPerolehan = $initData['CPM_BERKAS_JNS_PEROLEHAN'];
    if ($jnsPerolehan == 1) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy KTP</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy SK BPN</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy SPPT PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Bukti Lunas PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Bermaterai</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Kuasa Bermaterai (Bila Dikuasakan)</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photo Lokasi</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Mengisi Sketsa Lokasi</td></tr>" : "";
    } elseif ($jnsPerolehan == 2) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy KTP Pembeli dan Penjual</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Sertifikat Tanah</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy SPPT PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Bukti Lunas PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Bermaterai</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Kuasa Bermaterai (Bila Dikuasakan)</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photo Lokasi</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Mengisi Sketsa Lokasi</td></tr>" : "";
    } elseif ($jnsPerolehan == 3) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy KTP Pemberi Hibah dan Penerima Hibah</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Sertifikat Tanah</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy SPPT PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Bukti Lunas PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Hibah</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Kartu Keluarga</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Akte Kelahiran</td></tr>" : "";
        $lampiran[7] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Bermaterai</td></tr>" : "";
        $lampiran[8] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Kuasa Bermaterai (Bila Dikuasakan)</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photo Lokasi</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Mengisi Sketsa Lokasi</td></tr>" : "";
    } elseif ($jnsPerolehan == 4) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Ahli Waris</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Sertifikat Tanah</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy SPPT PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Bukti Lunas PBB Tahun Terhutang</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Surat Keterangan Waris</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Surat Kuasa Waris</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photocopy Surat Kematian</td></tr>" : "";
        $lampiran[7] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Bermaterai</td></tr>" : "";
        $lampiran[8] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Kuasa Bermaterai (Bila Dikuasakan)</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Photo Lokasi</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Mengisi Sketsa Lokasi</td></tr>" : "";
    }



    $lamp = implode(" ", $lampiran);
    $arr_jnsPerolehan = array(1=>"SK",2=>"JUAL-BELI",3=>"HIBAH",4=>"WARIS");


    $html = "
	<table border=\"1\" cellpadding=\"10\">
            <tr>
                <td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
                <td align=\"center\" width=\"60%\">
                        <!-- <font size=\"+4\"> --> " . $header_berkas . "<br />
                        <!-- </font> -->
                </td>
                <!--KOSONG-->
                <td rowspan=\"2\" align=\"center\" width=\"20%\">
                    <h1><font size=\"+6\">" . $arr_jnsPerolehan[$data->CPM_BERKAS_JNS_PEROLEHAN] . "</font></h1>
                </td>
            </tr>
            <tr>
                <td align=\"center\">
                    " . $alamat_berkas . "
                </td>
            </tr>
            <tr>            
                <td colspan=\"3\">
                    <table border=\"0\" cellpadding=\"2\" cellspacing=\"7\">
                        <tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">" . $buktiTitle . "<br /></font></td></tr>
                        <tr>
                            <td>Nomor Pelayanan</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NOPEL . "</td>
                        </tr>
                        <tr>
                            <td>Nama Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NAMA_WP . "</td>
                        </tr>
                        <tr>
                            <td>Nomor Telp Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_TELP_WP . "</td>
                        </tr>
                        <tr>
                            <td>Tanggal Surat Masuk</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_TANGGAL . "</td>
                        </tr>";
    $html .= "          <tr>
                            <td>NOP</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_NOP . "</td>
                        </tr>
                        <tr>
                            <td>Kelurahan</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_KELURAHAN_OP . "</td>
                        </tr>
                        <tr>
                            <td>Kecamatan</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_KECAMATAN_OP . "</td>
                        </tr>
                        <tr>
                            <td>Persyaratan administrasi</td><td>:</td> 
                            <td width=\"auto\" cellspacing=\"5\">" . ($lampiran != '' ? "<table border='1'>" . $lamp . "</table>" : "") . "</td>
                        </tr>
                        <tr>
                            <td>Harga Transaksi</td><td>:</td>
                            <td>Rp. " . number_format($data->CPM_BERKAS_HARGA_TRAN,0) . "</td>
                        </tr>
                    </table>					
		</td>
            </tr>
            <!--SALINAN DISPOSISI-->
            <tr>
                <td colspan=\"3\"><table border=\"0\">
                        <tr>
                            <td><table border=\"1\" cellpadding=\"12\">
                                  <tr>
                                        " . $parse2 . "
                                  </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
	</table>
        <br>
        <table border=\"1\" cellpadding=\"2\" width=\"100%\">
        <tr><td align=\"center\"><b>SKET / DENAH LOKASI WAJIB PAJAK</b></td></tr>
        
        <tr><td align=\"right\"><br><br><br><br><br><br><br><br><br><br><br><br>
        <font size=\"9\">Dibuat oleh wajib pajak / Kuasa<br>Palembang, ..............................</font><br><br><br><br>
        (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
        </table>";
    return $html;
    echo $html;
}

function getInitData($id = "") {
    global $DBLink;

    if ($id == '')
        return getDataDefault();

    $qry = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID='{$id}'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
        return getDataDefault();
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_BERKAS_TANGGAL'] = substr($row['CPM_BERKAS_TANGGAL'], 8, 2) . '-' . substr($row['CPM_BERKAS_TANGGAL'], 5, 2) . '-' . substr($row['CPM_BERKAS_TANGGAL'], 0, 4);
            return $row;
        }
    }
}

function getDataDefault() {
    $default = array('CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
        'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
        'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '');
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$NOP = "";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('-');
$pdf->SetSubject('-');
$pdf->SetKeywords('-');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 5, 5);
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


$d_row = $json->decode($q->svcId);
$v = count($d_row);
$appId = $q->appId;
$fileLogo = getConfigValue($appId, 'LOGO_CETAK_PDF');
//echo $fileLogo;exit;

//$pdf->AddPage('P', 'A4');
//$HTML = getHTML($idssb, $initData, $fileLogo);
//$pdf->writeHTML($HTML, true, false, false, false, '');
//$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 7, 19, 35, '', '', '', '', false, 300, '', false);
//$pdf->SetAlpha(0.3);

for ($i = 0; $i < $v; $i++) {
    $idssb = $d_row[$i]->id;
    $pdf->AddPage('P', 'F4');
    $initData = getInitData($idssb);
    $HTML = getHTML($idssb, $initData, $fileLogo);
    $pdf->writeHTML($HTML, true, false, false, false, '');
    //$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 7, 19, 35, '', '', '', '', false, 300, '', false);
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 12, 11, 25, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);
}


// -----------------------------------------------------------------------------
//Close and output PDF document
$pdf->Output($idssb . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
?>
