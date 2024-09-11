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
$id       = explode('+', $idssb);
$nomor = $id[0];
$nop   = (int)$id[1];

$dbhost     = getConfigValue($appID, 'GW_DBHOST');
$dbuser     = getConfigValue($appID, 'GW_DBUSER');
$dbpwd      = getConfigValue($appID, 'GW_DBPWD');
$dbname     = getConfigValue($appID, 'GW_DBNAME');
$nip        = getConfigValue($appID, 'KABID_NIP');
$jabatan    = getConfigValue($appID, 'KABID_JABATAN');
$kepala     = getConfigValue($appID, 'KABID_NAMA');
$kota       = getConfigValue($appID, 'NAMA_KOTA');
$tahun_tagihan = getConfigValue($appID, 'tahun_tagihan');
$dbnamesw   = getConfigValue($appID, 'ADMIN_SW_DBNAME');

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

function getData($iddoc)
{
    global $data, $DBLink, $dataNotaris;
    $query = sprintf(
        "SELECT * , DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED
					FROM CPPMOD_SSB_DOC A,CPPMOD_SSB_TRANMAIN B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
					AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'",
        getConfigValue("1", 'TENGGAT_WAKTU'),
        $iddoc
    );

    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $dataNotaris =  $json->decode(mysql2json($res, "data"));
    $dt = $dataNotaris->data[0];
    return $dt;
}

function getTunggakan($nop)
{
    global $DBLinkLookUp;

    $qry = "SELECT SPPT_PBB_HARUS_DIBAYAR,SPPT_TAHUN_PAJAK,SPPT_TANGGAL_JATUH_TEMPO FROM PBB_SPPT WHERE NOP = '" . $nop . "' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ORDER BY SPPT_TAHUN_PAJAK ASC";
    // echo $qry; exit;
    $res = mysqli_query($DBLinkLookUp, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    return $res;
}

function getHTML($op)
{
    global $tgl, $kepala, 
    $nip, $jabatan, $kota, $bank, $SP, $listTahun, $thnArr, $appID, $month, $listTagihan, $listDenda, $tagihanPlusDenda, $listTahun;
    $angkaRomawi = array('01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV', '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII', '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII');
    $tahun = date("Y");
    $tgl = explode("-", $op['SPPT_TANGGAL_JATUH_TEMPO']);
    $header_berkas    = getConfigValue($appID, 'C_HEADER_SK');
    $alamat_berkas    = getConfigValue($appID, 'C_ALAMAT_DISPOSISI');

    $sumTagihan         = array_sum($listTagihan);
    $sumDenda          = array_sum($listDenda);
    $sumTagihanDenda = array_sum($tagihanPlusDenda);
    $listTahun = join(',', $listTahun);
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
							<td width=\"275\">973/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/Dispenda-II/" . $angkaRomawi[date("m")] . "/" . $tahun . "</td>
							<td width=\"325\">" . ucfirst(strtolower($kota)) . ", &nbsp;&nbsp;&nbsp;&nbsp;" . $month[date("m")] . " " . $tahun . "</td>
						</tr>
						<tr>
                            <td>Sifat</td>
							<td>:</td>
							<td>Biasa</td>
							<td>Kepada</td>
						</tr>
						<tr>
                            <td>Lampiran</td>
							<td>:</td>
							<td>1 (satu) berkas</td>
							<td>Yth. Sdr. " . $op['WP_NAMA'] . "</td>
						</tr>
						<tr>
                            <td>Perihal</td>
							<td>:</td>
							<td>Konfirmasi Pembayaran PBB</td>
							<td>di</td>
						</tr>
						<tr>
                            <td></td><td></td><td></td><td>" . ucfirst(strtolower($kota)) . "</td>
						</tr>
					</table>		
					<br/>
					<br/>
                    Berdasarkan data Dispenda Kota Palembang, ternyata saudara masih menunggak Pajak Bumi dan Bangunan, PBB yang menjadi kewajiban saudara adalah sebagai berikut : <br/><br/>
                    <table width=\"700\" border=\"0\">
                        <tr>
                            <td width=\"200\">Nomor SPPT</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\" width=\"490\">" . $op['NOP'] . "</td>
                        </tr>
                        <tr>
                            <td>Nama Wajib Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['WP_NAMA'] . "</td>
                        </tr>
                        <tr>
                            <td>Alamat Wajib Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['WP_ALAMAT'] . "</td>
                        </tr>
                        <tr>
                            <td>Alamat Objek Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['ALAMAT_OP'] . "</td>
                        </tr>
                        <tr>
                            <td>Tahun Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $listTahun . "</td>
                        </tr> 
						<!--
                        <tr>
                            <td>PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td>Rp. " . number_format($op['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td>Denda</td>
                            <td width=\"10\">:</td>
                            <td>Rp. " . number_format($op['DENDA'], 0, ',', '.') . "</td>
                        </tr> 
						-->
                        <tr>
                            <td>PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td width=\"20\" >Rp </td>
							<td width=\"90\"align=\"right\">" . number_format($sumTagihan, 0, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td>Denda</td>
                            <td width=\"10\">:</td>
                            <td width=\"20\">Rp </td>
							<td width=\"90\" align=\"right\">" . number_format($sumDenda, 0, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td>Jumlah PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td width=\"20\">Rp </td>
							<td width=\"90\" align=\"right\">" . number_format($sumTagihan + $sumDenda, 0, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td>Jatuh Tempo</td>
                            <td width=\"10\">:</td>
                            <td width=\"auto\" colspan=\"2\">" . ($tgl[2] . "/" . $tgl[1] . "/" . $tgl[0]) . "</td>
                        </tr>
                        <tr>
                            <td>Bank Tempat Pembayaran</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">$bank</td>
                        </tr>
                    </table>
					<br/><br/>
					Sehubungan dengan hal tersebut diatas kami mengharapkan kehadiran saudara pada : <br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table width=\"700\" border=\"0\">
                        <tr>
                            <td width=\"200\">Hari</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\" width=\"490\">" . $op['HARI'] . "</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . substr($op['TANGGAL'], 8, 2) . '/' . substr($op['TANGGAL'], 5, 2) . '/' . substr($op['TANGGAL'], 0, 4) . "</td>
                        </tr>
                        <tr>
                            <td>Jam</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['JAM'] . "</td>
                        </tr>
                        <tr>
                            <td>Acara</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['ACARA'] . "</td>
                        </tr>
                        <tr>
                            <td>Tempat</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">" . $op['TEMPAT'] . "</td>
                        </tr> 
					</table>
                    <br/><br/>
                    Mengingat pentingnya acara dimaksud, mohon kehadiran saudara tepat pada waktunya. Perlu kami beritahukan bahwa jika Saudara tidak merespon perihal tersebut diatas, kami akan melakukan penyampaian surat kepada Instansi Penegak Hukum, yang kemudian akan dilakukan tindakan Hukum berdasarkan Perundang-undangan yang ada.
                    <br/>
                    <br/>
                    Dalam hal saudara telah membayar dan melunasi PBB tersebut diatas, kami harapkan saudara dapat menyampaikan salinan bukti lunas PBB kepada kami.
                    <br/>
                    <br/>
                        <table border=\"0\">
                            <tr>
                                <td>&nbsp;</td>
                                <td align=\"left\">Kepala Dinas Pendapatan Daerah
                                    <br/>
                                    Kota " . ucfirst(strtolower($kota)) . "
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
						<br><br>
						<table border=\"1\" width=\"310\" cellpadding=\"5\">
							<tr><td>
						
						<table border=\"0\" width=\"300\">
                            <tr>
                                <td colspan=\"2\" align=\"center\"><b>UNTUK DIMAKLUMI</b></td>
                            </tr>
							<tr>
                                <td colspan=\"2\" align=\"justify\">Berdasarkan Perda Nomor 3 Tahun 2011 Tentang Pajak Bumi dan Bangunan  Perkotaan :</td>
                            </tr>
							<tr>
                                <td align=\"left\" width=\"20\">a</td>
								<td align=\"justify\" width=\"280\">Pasal 14 ayat 1 bahwa Pajak yang terutang berdasarkan Surat Tagihan Pajak Daerah (STPD), Surat Keputusan Pembetulan, Surat Keputusan Keberatan, dan Putusan Banding yang tidak atau kurang dibayar oleh wajib pajak pada waktunya dapat ditagih dengan surat paksa.</td>
                            </tr>
							<tr>
                                <td align=\"left\" width=\"20\">b</td>
								<td align=\"justify\">Pasal 14 ayat 2 bahwa Penagihan Pajak dengan Surat Paksa dilaksanakan berdasarkan peraturan perundang-undangan.</td>
                            </tr>
                        </table>
						</td></tr>
						</table>
                    </td>
                </tr>
            </table>
            ";

    return $html;
}

function countDay($s_day, $e_day)
{
    $startTimeStamp = strtotime($s_day);
    $endTimeStamp = strtotime($e_day);

    if ($startTimeStamp > $endTimeStamp)
        return 0;

    $timeDiff = abs($endTimeStamp - $startTimeStamp);

    $numberDays = $timeDiff / 86400;  // 86400 seconds in one day

    //convert to integer
    $numberDays = intval($numberDays);

    return $numberDays;
}

function getPenalty($pbbHarusDibayar, $jatuhTempo)
{

    $tipeKalkulasiPajak = 1;
    $totalBulanPajak = 24;
    $denda = 2;
    $penalty = 0;

    switch ($tipeKalkulasiPajak) {
        case 0:
            $day = countDay($jatuhTempo, date('Y-m-d'));
            $penalty = $denda * $day * $pbbHarusDibayar / 100;
            break;
        case 1:
            $month = ceil(countDay($jatuhTempo, date('Y-m-d')) / 30);
            if ($month > $totalBulanPajak) {
                $month = $totalBulanPajak;
            }
            $penalty = $denda * $month * $pbbHarusDibayar / 100;
            break;
    }

    return $penalty;
}

function getPbbspptById($nop, $thnpajak)
{
    global $DBLinkLookUp, $DBLink, $denda, $totalBulanPajak, $tipeKalkulasiPajak;
    $data = array();

    $sql = "SELECT *
			FROM gw_pbb.pbb_sppt A JOIN gw_pbb.pbb_sppt_pemanggilan B ON A.NOP=B.SP_NOP
			WHERE
				A.NOP = '{$nop}'
			    AND SPPT_TAHUN_PAJAK = '{$thnpajak}'";
            // print_r($sql);exit;
    $result = mysqli_query($DBLinkLookUp, $sql);

    if ($result) {
        $buffer = mysqli_fetch_assoc($result);
        $data['NOP'] = $buffer['NOP'];
        $data['WP_NAMA'] = $buffer['WP_NAMA'];
        /*$temp_wil = mysql_query("SELECT CPC_TKL_KELURAHAN AS KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID='".$buffer['WP_KELURAHAN']."'",$DBLink);
        $kel = mysqli_fetch_assoc($temp_wil);

        $temp_wil = mysql_query("SELECT CPC_TKC_KECAMATAN AS KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID='".$buffer['WP_KECAMATAN']."'",$DBLink);
        $kec = mysqli_fetch_assoc($temp_wil);

        $temp_wil = mysql_query("SELECT CPC_TK_KABKOTA AS KABKOTA FROM cppmod_tax_kabkota WHERE CPC_TK_ID='".$buffer['WP_KOTAKAB']."'",$DBLink);
        $kab = mysqli_fetch_assoc($temp_wil);

        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'].
                             ", Kel. ".ucwords(strtolower($kel['KELURAHAN'])).", Kec. ".ucwords(strtolower($kec['KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($kab['KABKOTA']));*/
        $data['WP_ALAMAT_JALAN'] = $buffer['WP_ALAMAT'] . " RT " . $buffer['WP_RT'] . "/RW " . $buffer['WP_RW'];
        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT'] . " RT " . $buffer['WP_RT'] . "/RW " . $buffer['WP_RW'] .
            ", Kel. " . ucwords(strtolower($buffer['WP_KELURAHAN'])) . ", Kec. " . ucwords(strtolower($buffer['WP_KECAMATAN'])) . ", Kota/Kab. " . ucwords(strtolower($buffer['WP_KOTAKAB']));
        $data['SPPT_TAHUN_PAJAK'] = $buffer['SPPT_TAHUN_PAJAK'];
        $data['SPPT_TANGGAL_JATUH_TEMPO'] = $buffer['SPPT_TANGGAL_JATUH_TEMPO'];
        $data['SPPT_PBB_HARUS_DIBAYAR'] = $buffer['SPPT_PBB_HARUS_DIBAYAR'];
        $data['ALAMAT_OP'] = $buffer['OP_ALAMAT'] . " RT " . $buffer['OP_RT'] . "/RW " . $buffer['OP_RW'] .
            ", Kel. " . ucwords(strtolower($buffer['OP_KELURAHAN'])) . ", Kec. " . ucwords(strtolower($buffer['OP_KECAMATAN'])) . ", Kota/Kab. " . ucwords(strtolower($buffer['OP_KOTAKAB']));
        $data['DENDA']         = getPenalty($data['SPPT_PBB_HARUS_DIBAYAR'], $data['SPPT_TANGGAL_JATUH_TEMPO']);
        $data['HARI']         = $buffer['SP_HARI'];
        $data['JAM']         = $buffer['SP_JAM'];
        $data['TANGGAL']     = $buffer['SP_TANGGAL'];
        $data['ACARA']         = $buffer['SP_ACARA'];
        $data['TEMPAT']     = $buffer['SP_TEMPAT'];
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
        //	if ($draf == 1) $this->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false, false, 0);
        //	else if ($draf == 0) $this->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
    }
}

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
$pdf->SetMargins(5, 7, 4);
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

$fileLogo =  getConfigValue($appID, 'LOGO_CETAK_PDF');

$pdf->AddPage('P', 'F4');
//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
$resTunggakan = getTunggakan($nop);
$i = 0;
$listTagihan = $listDenda = $tagihanPlusDenda = $listTahun = array();
while ($row = mysqli_fetch_assoc($resTunggakan)) {
    $listTagihan[]      = $row['SPPT_PBB_HARUS_DIBAYAR']; //
    $listDenda[]        = getPenalty($listTagihan[$i], $row['SPPT_TANGGAL_JATUH_TEMPO']);
    $tagihanPlusDenda[] = $listTagihan[$i] + $listDenda[$i];
    $listTahun[] = $row['SPPT_TAHUN_PAJAK'];
    $i++;
}
// var_dump(count($listTahun));
// exit;
if (count($listTahun) > 0) {
    $i = count($listTahun) - 1;
    $thnpajak = $listTahun[$i];
} else {
    $i = 0;
    $thnpajak = $tahun_tagihan;
}
// echo $thnpajak; exit;
$wp = getPbbspptById($nop, (int)$thnpajak);
if($wp['NOP']==''){
    die();
}
// print_r($wp);exit;
$HTML = getHTML($wp);
$pdf->writeHTML($HTML, true, false, false, false, '');
//echo $sRootPath.'image/'.$fileLogo;
$pdf->Image($sRootPath . 'image/' . $fileLogo, 20, 7, 25, '', '', '', '', false, 300, '', false);
$pdf->SetAlpha(0.3);

//Close and output PDF document
$pdf->Output($nop . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE
//============================================================+
