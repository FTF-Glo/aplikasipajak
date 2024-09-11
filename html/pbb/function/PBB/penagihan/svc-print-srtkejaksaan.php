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
                    <td colspan=\"3\">
					<br/>
					<table width=\"100%\" border=\"0\">
                        <tr>
                            <td width=\"90\">Nomor</td>
							<td width=\"10\">:</td>
							<td width=\"275\">" . $dt['NOMOR'] . "</td>
							<td width=\"325\">" . ucfirst(strtolower($kota)) . ", &nbsp;&nbsp;&nbsp;&nbsp;" . $month[date("m")] . " " . $tahun . "</td>
						</tr>
						<tr>
                            <td>Sifat</td>
							<td>:</td>
							<td>Penting</td>
							<td>Kepada</td>
						</tr>
						<tr>
                            <td>Lampiran</td>
							<td>:</td>
							<td>-</td>
							<td>Yth. Sdr. " . $dt['NAMA'] . "</td>
						</tr>
						<tr>
                            <td>Perihal</td>
							<td>:</td>
							<td>Bantuan Hukum</td>
							<td>di</td>
						</tr>
						<tr>
                            <td></td><td></td><td></td><td>" . ucfirst(strtolower($kota)) . "</td>
						</tr>
					</table>		
					<div align=\"justify\" cellspacing>
					<p style=\"line-height: 8px;text-indent: 30px;\">
					<dd>Sehubungan dengan Undang Undang Nomor 16 tahun 2004 tentang Kejaksaan Republik Indonesia dan memperhatikan Surat dari Kejaksaan Agung RI Nomor B-380/G/Gs.2/2006 tanggal 09 Oktober 2008 perihal pemberian bantuan hukum dan Pertimbangan Hukum  Kepada Lembaga Negara / Pemerintah khususnya yang berkaitan dengan kewenagan dalam melakukan  perimbangan dan pelayanan hukum, yang sangat dibutuhkan oleh Dinas Pendapatan Daerah Kota Palembang sebagai salah satu Instansi Pemerintah di Kota Palembang.</dd>
					<dd>Sesuai dengan hal tersebut, kami mengajukan permohonan bantuan hukum kepada Jaksa Pengacara Negara untuk dapat mewakili dan atau mendampingi kami dalam hal penagihan tunggakan piutang Pajak Bumi dan Bangunan, (terlampir Nomor Objek Pajak, Objek Pajak, Subjek Pajak, dan Alamat).</dd>
					<dd>Demikian surat ini kami sampaiakan atas bantuan dan perkenan Bapak diucapkan terima kasih.</dd>
					</p>
					</div>
					<br><br>
                        <table border=\"0\">
                            <tr>
                                <td>&nbsp;</td>
                                <td align=\"left\">" . strtoupper('Kepala Dinas Pendapatan Daerah') . "
                                    <br/>
                                    KOTA " . (strtoupper($kota)) . "
                                    <br/>
                                    <br/>
                                    <br/>
									<br/>
									<br/>
                                    $kepala
                                    <br/>
                                    $jabatan
                                    <br/>
                                    NIP. $nip
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
$pdf->AddPage('P', 'F4');
$HTML = getHTML($dt);
$pdf->writeHTML($HTML, true, false, false, false, '');
$pdf->Image($sRootPath . 'image/' . $fileLogo, 20, 7, 25, '', '', '', '', false, 300, '', false);
$pdf->SetAlpha(0.3);
$pdf->Output($idssb . '.pdf', 'I');
