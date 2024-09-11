<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan_khusus_2016', '', dirname(__FILE__))) . '/';
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

$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$thnpajak = @isset($_REQUEST['thnpajak']) ? $_REQUEST['thnpajak'] : "";
$dbhost = @isset($_REQUEST['dbhost']) ? $_REQUEST['dbhost'] : "";
$dbuser = @isset($_REQUEST['dbuser']) ? $_REQUEST['dbuser'] : "";
$dbpwd = @isset($_REQUEST['dbpwd']) ? $_REQUEST['dbpwd'] : "";
$dbname = @isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : "";
$kepala = @isset($_REQUEST['kepala']) ? $_REQUEST['kepala'] : "";
$kota = @isset($_REQUEST['kota']) ? $_REQUEST['kota'] : "";
$nip = @isset($_REQUEST['nip']) ? $_REQUEST['nip'] : "";
$jabatan = @isset($_REQUEST['jabatan']) ? $_REQUEST['jabatan'] : "";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
$denda = @isset($_REQUEST['denda']) ? $_REQUEST['denda'] : "";
$totalBulanPajak = @isset($_REQUEST['totalBulanPajak']) ? $_REQUEST['totalBulanPajak'] : "";
$tipeKalkulasiPajak = @isset($_REQUEST['tipeKalkulasiPajak']) ? $_REQUEST['tipeKalkulasiPajak'] : "";
$limitTahunPajak = @isset($_REQUEST['limitTahunPajak']) ? $_REQUEST['limitTahunPajak'] : "";


SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost, $dbuser, $dbpwd, $dbname);

//echo $dbhost." | ".$dbuser." | ".$dbpwd." | ".$dbname;


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
    global $DBLink, $appID;
    $id = $appID;
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

function getHTML($op)
{
    global $tgl, $kepala, $nip, $jabatan, $kota, $bank;
    $tahun = date("Y");

    $rowlt = "";
    $no = 1;
    foreach ($op["LIST_TERHUTANG"] as $lt) {
        $rowlt .= "
                 <tr>
                    <td>$no</td>
                    <td>" . $lt["NAMA"] . "</td>
                    <td>" . $lt["THN_PAJAK"] . "</td>
                    <td>Rp. " . number_format($lt["PBB_TERHUTANG"], 0, ',', '.') . "</td>
                    <td>Rp. " . number_format($lt["DENDA"], 0, ',', '.') . "</td>
                    <td>Rp. " . number_format($lt["PBB_TERHUTANG_DENDA"], 0, ',', '.') . "</td>
                    <td>" . $lt["KETERANGAN"] . "</td>
                 </tr>
                 ";
        $no++;
    }

    $html = "<table width=\"700\" cellpadding=\"2\">
                <tr>
                    <td colspan=\"3\" align=\"center\"><b>SURAT PERNYATAAN<br/>KESANGGUPAN MEMBAYAR TUNGGAKAN PBB BESERTA SANKSI ADMINISTRASI</b></td>
                </tr>
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan=\"3\">Saya yang bertanda tangan di bawah ini:</td>
                </tr>
                <tr>
                    <td width=\"150\">Nama</td>
                    <td width=\"10\">:</td>
                    <td width=\"540\" align=\"left\">" . $op["WP_NAMA"] . "</td>
                </tr>
                <tr>
                    <td width=\"150\">Alamat</td>
                    <td width=\"10\">:</td>
                    <td width=\"540\" align=\"left\">" . $op["WP_ALAMAT"] . "</td>
                </tr>
                <tr>
                    <td width=\"150\">No Telp/HP</td>
                    <td width=\"10\">:</td>
                    <td width=\"540\" align=\"left\">" . $op["WP_NO_HP"] . "</td>
                </tr>
                <tr>
                    <td width=\"150\">Pekerjaan</td>
                    <td width=\"10\">:</td>
                    <td width=\"540\" align=\"left\">" . $op["WP_PEKERJAAN"] . "</td>
                </tr>
                <tr>
                    <td colspan=\"3\">Dengan ini memberikan pernyataan kesanggupan untuk membayar Pajak Bumi dan bangunan (PBB) terutang beserta sanksi administrasi (denda) sebagaimana data tagihan PBB pada Dinas Pendapatan Daerah sebagai berikut :</td>
                </tr>
                <tr>
                    <td colspan=\"3\" align=\"center\">
                        <table width=\"100%\" cellpading=\"2\" border=\"1\" style=\"font-weigth:bold;\">
                            <tr valign=\"middle\">
                                <td align=\"center\" width=\"5%\">NO.</td>
                                <td align=\"center\" width=\"20%\">NAMA WAJIB PAJAK</td>
                                <td align=\"center\" width=\"10%\">TAHUN PAJAK</td>
                                <td align=\"center\" width=\"15%\">PBB TERHUTANG</td>
                                <td align=\"center\" width=\"15%\">DENDA</td>
                                <td align=\"center\" width=\"15%\">PBB TERHUTANG + DENDA</td>
                                <td align=\"center\" width=\"20%\">KETERANGAN</td>
                            </tr>
                            $rowlt
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">Demikian Surat Pernyataan ini saya buat diatas materai dalam keadaan sehat, baik jasmani maupun rohani tanpa adanya paksaaan dari pihak manapun, serta bersedia dikenakan sanksi sesuai ketentuan yang berlaku apabila lalai dalam melaksanakan isi Surat Pernyataan ini.</td>
                </tr>
                 <tr>
                    <td colspan=\"2\" width=\"60%\">&nbsp;</td>
                    <td width=\"40%\" align=\"left\">" . ucfirst(strtolower($kota)) . ", ...........................<br/><br/>
                                    Hormat Saya,<br/>
                                    <br/>
                                    <br/>
                                    <font size=\"7\">materai 6000</font><br/>
                                    <br/>
                                    <br/>
                                    <br/>
                                    " . ucwords(strtolower($op["WP_NAMA"])) . "
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan=\"3\">Untuk :</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"150\">Lembar I</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"150\">Lembar II</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"150\">Lembar III</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td colspan=\"3\" width=\"700\"><br/><br/><br/><strong>NOTE : </strong><i>Silahkan abaikan surat ini apabila wajib pajak telah membayar PBB tahun tertentu dan mohon agar bukti
                    lunas pembayaran PBB dari bank atas tahun dimaksud untuk diserahkan ke seksi penagihan, keberatan
                    dan pengurangan bidang PBB dan BPHTB dinas pendapatan daerah kota $kota.</i>
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
    global $totalBulanPajak, $tipeKalkulasiPajak, $denda;

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
    global $DBLinkLookUp, $DBLink, $denda, $totalBulanPajak, $tipeKalkulasiPajak, $limitTahunPajak;
    $data = array();

    $sql = "SELECT * FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thnpajak'";
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
        $kab = mysqli_fetch_assoc($temp_wil);*/

        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT'] . " RT " . $buffer['WP_RT'] . "/RW " . $buffer['WP_RW'] .
            ", Kel. " . ucwords(strtolower($buffer['WP_KELURAHAN'])) . ", Kec. " . ucwords(strtolower($buffer['WP_KECAMATAN'])) . ", Kota/Kab. " . ucwords(strtolower($buffer['WP_KOTAKAB']));
        $data['WP_NO_HP'] = $buffer['WP_NO_HP'];
        $data['WP_PEKERJAAN'] = $buffer['WP_PEKERJAAN'];

        for ($thn = $limitTahunPajak; $thn <= date("Y"); $thn++) {
            $sql_lp = "SELECT WP_NAMA,SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR,SPPT_TANGGAL_JATUH_TEMPO FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thn' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG is null)";
            $result_lp = mysql_query($sql_lp, $DBLinkLookUp);
            if (mysqli_num_rows($result_lp) > 0) {
                $buffer_lp = mysqli_fetch_assoc($result_lp);
                $denda_lp = getPenalty($buffer_lp['SPPT_PBB_HARUS_DIBAYAR'], $buffer_lp['SPPT_TANGGAL_JATUH_TEMPO']);
                $tmp = array(
                    "NAMA" => $buffer_lp["WP_NAMA"],
                    "THN_PAJAK" => $buffer_lp["SPPT_TAHUN_PAJAK"],
                    "PBB_TERHUTANG" => $buffer_lp["SPPT_PBB_HARUS_DIBAYAR"],
                    "DENDA" => $denda_lp,
                    "PBB_TERHUTANG_DENDA" => ($buffer_lp["SPPT_PBB_HARUS_DIBAYAR"] + $denda_lp),
                    "KETERANGAN" => ""
                );
                $data["LIST_TERHUTANG"][] = $tmp;
            }
        }
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

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

//$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(5, 14, 10);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";


$pdf->AddPage('P', 'F4');
$wp = getPbbspptById($nop, $thnpajak);
$HTML = getHTML($wp);

//echo $HTML;
$pdf->writeHTML($HTML, true, false, false, false, '');
$pdf->Output('sp_' . $nop . '.pdf', 'I');
