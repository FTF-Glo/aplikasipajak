<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
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
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);
$idssb = $q->svcId;
$appID = $q->appId;

$dbhost 	= getConfigValue($appID, 'GW_DBHOST');
$dbuser 	= getConfigValue($appID, 'GW_DBUSER');
$dbpwd 		= getConfigValue($appID, 'GW_DBPWD');
$dbname 	= getConfigValue($appID, 'GW_DBNAME');
$nip		= getConfigValue($appID, 'KABID_NIP');
$jabatan	= getConfigValue($appID, 'KABID_JABATAN');
$kepala		= getConfigValue($appID, 'KABID_NAMA');
$kota		= getConfigValue($appID, 'NAMA_KOTA');
$dbnamesw = getConfigValue($appID, 'ADMIN_SW_DBNAME');
// var_dump($kota);
// exit;

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost, $dbuser, $dbpwd, $dbname);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$dataNotaris = "";
$month = array(
	"01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", "05" => "Mei",
	"06" => "Juni", "07" => "Juli", "08" => "Agustus", "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
);
$tgl = date("d") . "/" . $month[date("m")] . "/" . date("Y");

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
	global $DBLink, $appID, $dbnamesw;
	$id = $appID;
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from $dbnamesw.central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";

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

function getHTML($dt)
{
	global $tgl, $kepala, $nip, $jabatan, $kota, $appID, $month;
	$tahun = date("Y");
	$header_berkas	= getConfigValue($appID, 'C_HEADER_SK');
	$alamat_berkas	= getConfigValue($appID, 'C_ALAMAT_DISPOSISI');
	$nama_kabid		= getConfigValue($appID, 'KABID_NAMA');
	$jabatan		= getConfigValue($appID, 'KABID_JABATAN');
	$alamat			= getConfigValue($appID, 'KABID_ALAMAT');

	$html = "
			<html>
			<table border=\"0\" cellpadding=\"10\" width=\"100%\">
				<tr>
					<!--LOGO-->
					<td align=\"center\" width=\"20%\">
						
					</td>
					<!--COP-->
					<td align=\"center\" width=\"79%\">
						" . $header_berkas . "
					</td>
					<!--KOSONG-->
					<td align=\"center\" width=\"1%\">
					</td>
				</tr>
				<tr>
					<td colspan=\"3\"><hr></td>
				</tr>
				<tr>
					<td colspan=\"3\" align=\"center\"><u>SURAT KUASA</u><br>Nomor : " . $dt['NOMOR'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\" align=\"left\">
						<table border=\"0\" cellpadding=\"2\">
							<tr>
								<td colspan=\"3\">Yang bertanda tangan di bawah ini:</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td width=\"100\">Nama</td><td width=\"10\">:</td><td width=\"582\">" . $nama_kabid . "</td>
							</tr>
							<tr>
								<td>Jabatan</td><td>:</td><td>" . $jabatan . "</td>
							</tr>
							<tr>
								<td>Alamat</td><td>:</td><td>" . $alamat . "</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=\"3\">Selanjutnya disebut sebagai Pemberi Kuasa:</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td width=\"100\">Nama</td><td width=\"10\">:</td><td width=\"582\">" . $dt['NAMA'] . "</td>
							</tr>
							<tr>
								<td>Jabatan</td><td>:</td><td>" . $dt['JABATAN'] . "</td>
							</tr>
							<tr>
								<td>Alamat</td><td>:</td><td>" . $dt['ALAMAT'] . "</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=\"3\">Selanjutnya disebut sebagai Penerima Kuasa:</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
							<tr>
								<td colspan=\"3\" align=\"center\"><b>KHUSUS</b></td>
							</tr>
							<tr>
								<td colspan=\"3\" align=\"justify\">
								<p>Untuk dan atas nama Dinas Pendapatan Daerah Kota Plaembang, melakukan negosiasi dengan pihak Wajib Pajak , terhadap permasalahan tunggakan Pajak Bumi dan Bangunan (daftar terlampir).</p>
								<p>Untuk itu penerima kuasa berhak melakukan pertemuan, menghadap pejabat yang berwenang, membuat usulan perdamaian, berita acara perdamaian, kesepakatan perdamaian, somasi/peringatan dan menandatangani surat surat yang diperlukan serta melakukan segala tindakan dan perbuatan yang dianggap perlu dan berguna untuk kepentingan pemberi kuasa.</p>
								</td>
							</tr>
							<tr>
								<td></td><td></td><td></td>
							</tr>
						</table>
					</td>
				</tr>
                <tr>
                    <td colspan=\"3\">
                        <table border=\"0\">
                            <tr>
                                <td align=\"left\" width=\"450\">Penerima Kuasa
                                    <br/>
                                    <br/>
                                    <br/>
									<br/>
                                    <br/>
									<br/>
                                    <br/>
                                    " . $dt['NAMA'] . "
                                </td>
                                <td align=\"left\">
                                    <br/>
                                    " . (ucfirst(strtolower($kota))) . ",&nbsp;&nbsp;&nbsp;&nbsp;" . $month[date("m")] . " " . $tahun . "
                                    <br>Pemberi Kuasa
                                    <br/>
                                    <br/>
									<br/>
									<br/>
									<br/>
									<br/>
									" . $nama_kabid . "
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            ";

	return $html;
}

function getDataById($idssb)
{
	global $DBLinkLookUp;
	$data = array();

	$sql = "SELECT
				*
			FROM
				SURAT_PENGANTAR_KEJAKSAAN
			WHERE
				SPK_NOMOR = '{$idssb}'";
	$result = mysqli_query($DBLinkLookUp, $sql);

	if ($result) {
		$buffer = mysqli_fetch_assoc($result);
		$data['NOMOR'] 		= $buffer['SPK_NOMOR'];
		$data['NAMA'] 		= $buffer['SPK_NAMA'];
		$data['JABATAN']	= $buffer['SPK_JABATAN'];
		$data['ALAMAT']		= $buffer['SPK_ALAMAT'];
	} else {
		echo mysqli_error($DBLink);
	}
	return $data;
}

class MYPDF extends TCPDF
{
	public function Header()
	{
		global $sRootPath, $draf;
		$bMargin = $this->getBreakMargin();
		$auto_page_break = $this->AutoPageBreak;
		$this->SetAutoPageBreak(false, 0);
		$this->SetAlpha(0.3);
		$this->SetAutoPageBreak($auto_page_break, $bMargin);
	}
}

$dt = getDataById($idssb);

// print_r($dt);

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(5, 7, 4);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";
$fileLogo =  getConfigValue($appID, 'LOGO_CETAK_PDF');
// if ($fileLogo===null){
// 	$fileLogo = 'logo_bangka.jpeg';
// }
// var_dump($fileLogo);
// exit;
$pdf->AddPage('P', 'F4');
$HTML = getHTML($dt);
$pdf->writeHTML($HTML, true, false, false, false, '');
$pdf->Image($sRootPath . 'image/' . $fileLogo, 20, 7, 25, '', '', '', '', false, 300, '', false);
$pdf->SetAlpha(0.3);
$pdf->Output($idssb . '.pdf', 'I');
