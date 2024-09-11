<?php

session_start();
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

$arConfig = $User->GetModuleConfig('mLkt');

$dataNotaris = "";
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function getAuthor($field, $uname) {
    global $DBLink, $appID;
    $id = $appID;
    $qry = "select $field from tbl_reg_user_bphtb where userId = '" . $uname . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo mysqli_error($DBLink);
    }

    $num_rows = mysqli_num_rows($res);
    if ($num_rows == 0)
        return $uname;
    while ($row = mysqli_fetch_assoc($res)) {
        return $row[$field];
    }
}

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
    $dateNow = date('d-m-Y');
    $C_HEADER_FORM_PENERIMAAN = getConfigValue($appId, 'C_HEADER_FORM_PENERIMAAN');
    $NAMA_KOTA_PENGESAHAN = ucwords(strtolower(getConfigValue($appId, 'NAMA_KOTA_PENGESAHAN')));
    //$NOP = $data->CPM_OP_NUMBER;
    //echo $fileLogo;exit;

    $buktiTitle = "BUKTI PENERIMAAN BPHTB";
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
				" . $C_HEADER_FORM_PENERIMAAN . "
			</td>
		</tr>
        <tr>
        	<!--ISI-->
			<td colspan=\"3\">
				<font size=\"-1\">
				<table border=\"0\" cellpadding=\"1\" cellspacing=\"5\">
					<tr>
                        <td colspan=\"3\" align=\"center\">" . $buktiTitle . "<br></td>
                    </tr>                    
                    <tr>
                        <td width=\"125\">Nomor Pelayanan</td><td width=\"10\">:</td>
                        <td width=\"180\">" . $data->CPM_BERKAS_NOPEL . "</td>
                    </tr>                    
                    <tr>
                        <td width=\"125\">Nama Wajib Pajak</td><td width=\"10\">:</td>
                        <td width=\"180\">" . $data->CPM_BERKAS_NAMA_WP . "</td>
                    </tr>
                    <tr>
                        <td width=\"125\">NPWP</td><td width=\"10\">:</td>
                        <td width=\"180\">" . $data->CPM_BERKAS_NPWP . "</td>
                    </tr>
                    <tr>
                        <td>Tanggal Surat Masuk</td><td>:</td>
                        <td>" . $data->CPM_BERKAS_TANGGAL . "</td>
                    </tr>";

    $html .= "      <tr>
                        <td width=\"125\">Nomor Objek Pajak</td><td width=\"10\">:</td>
			<td width=\"180\">" . $data->CPM_BERKAS_NOP . "</td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        <td>" . $NAMA_KOTA_PENGESAHAN . ", " . $dateNow . " 
						<br>
						<br>
						<br>
						<br>
						" . ucwords($_SESSION['username']) . "
						<hr style=\"width:120px\">
						NIP. </td>
                    </tr>
        		</table>
			</font>				
			</td>
		</tr>
	</table>
</html>";
    return $html;
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
$pdf->SetMargins(2, 4, 2);

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
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";

$d_row = $json->decode($q->svcId);
$v = count($d_row);
$appId = $q->appId;
$fileLogo = getConfigValue($appId, 'LOGO_CETAK_PDF');

for ($i = 0; $i < $v; $i++) {
    $idssb = $d_row[$i]->id;
    $pdf->AddPage('P', 'A6');
    $initData = getInitData($idssb);
    $HTML = getHTML($idssb, $initData, $fileLogo);
    $pdf->writeHTML($HTML, true, false, false, false, '');
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 6, 9, 20, '', '', '', '', false, 300, '', false);
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
