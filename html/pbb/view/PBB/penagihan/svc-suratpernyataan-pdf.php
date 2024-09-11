<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
if(!$q) die();

$q = base64_decode($q);
$q = json_decode($q);

$nop 		= (int)$q->nop;
$thnpajak	= (int)$q->thnpajak;
$appID   	= addslashes($q->appId);

$kepala     = getConfigValue($appID, 'NAMA_PEJABAT_SK2');
$nip        = getConfigValue($appID, 'NAMA_PEJABAT_SK2_NIP');

// print_r($namaPejabatSK);exit;
$denda = 2;
$totalBulanPajak = 24;

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

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
    global $DBLink;
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
        getConfigValue("aPBB", 'TENGGAT_WAKTU'),
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
    global $tgl, $kepala, $nip;
    $tahun = date("Y");

    $rowlt = "";
    $no = 1;
    foreach ($op["LIST_TERHUTANG"] as $lt) {
        $rowlt .= "
                 <tr>
                    <td align=\"center\"><table border=\"1\" cellpadding=\"2\"><tr><td>$no</td></tr></table></td>
                    <td><table border=\"1\" cellpadding=\"2\"><tr><td>" . $lt["NAMA"] . "</td></tr></table></td>
                    <td align=\"center\"><table border=\"1\" cellpadding=\"2\"><tr><td>" . $lt["THN_PAJAK"] . "</td></tr></table></td>
                    <td align=\"right\"><table border=\"1\" cellpadding=\"2\"><tr><td>Rp. " . number_format($lt["PBB_TERHUTANG"], 0, ',', '.') . "</td></tr></table></td>
                    <td align=\"right\"><table border=\"1\" cellpadding=\"2\"><tr><td>Rp. " . number_format($lt["DENDA"], 0, ',', '.') . "</td></tr></table></td>
                    <td align=\"right\"><table border=\"1\" cellpadding=\"2\"><tr><td>Rp. " . number_format($lt["PBB_TERHUTANG_DENDA"], 0, ',', '.') . "</td></tr></table></td>
                    <td><table border=\"1\" cellpadding=\"2\"><tr><td>" . $lt["KETERANGAN"] . "</td></tr></table></td>
                 </tr>
                 ";
        $no++;
    }

    $noWP = ($op["ID_WP"]=='' || $op["ID_WP"]==null || strlen($op["ID_WP"])<>16) ? 'NPWP '.$op["ID_WP"] : 'NO. KTP '.$op["ID_WP"];
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
                    <td width=\"120\">Nama</td>
                    <td width=\"10\">:</td>
                    <td width=\"570\" align=\"left\">" . $op["WP_NAMA"] . "</td>
                </tr>
                <tr>
                    <td width=\"120\">Alamat</td>
                    <td width=\"10\">:</td>
                    <td width=\"570\" align=\"left\">" . $op["WP_ALAMAT"] . "</td>
                </tr>
                <tr>
                    <td width=\"120\">No Telp/HP</td>
                    <td width=\"10\">:</td>
                    <td width=\"570\" align=\"left\">" . $op["WP_NO_HP"] . "</td>
                </tr>
                <tr>
                    <td width=\"120\">Pekerjaan</td>
                    <td width=\"10\">:</td>
                    <td width=\"570\" align=\"left\">" . $op["WP_PEKERJAAN"] . "</td>
                </tr>
                <tr>
                    <td colspan=\"3\" align=\"justify\">Dengan ini memberikan pernyataan kesanggupan untuk membayar Pajak Bumi dan bangunan (PBB) terutang beserta sanksi administrasi (denda) sebagaimana data tagihan PBB pada Dinas Pendapatan Daerah sebagai berikut :</td>
                </tr>
                <tr>
                    <td colspan=\"3\"><table width=\"696\" border=\"1\">
                            <tr>
                                <th align=\"center\" width=\"5%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>NO.</b></td></tr></table></th>
                                <th align=\"center\" width=\"25%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>&nbsp;NAMA WAJIB PAJAK</b></td></tr></table></th>
                                <th align=\"center\" width=\"10%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>&nbsp;TAHUN</b></td></tr></table></th>
                                <th align=\"center\" width=\"18%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>&nbsp;PBB&nbsp;TERHUTANG</b></td></tr></table></th>
                                <th align=\"center\" width=\"15%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>&nbsp;DENDA</b></td></tr></table></th>
                                <th align=\"center\" width=\"18%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>&nbsp;PBB TOTAL</b></td></tr></table></th>
                                <th align=\"center\" width=\"9%\"><table border=\"1\" cellpadding=\"5\"><tr><td><b>KET.</b></td></tr></table></th>
                            </tr>
                            $rowlt
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\" align=\"justify\">Demikian Surat Pernyataan ini saya buat diatas materai dalam keadaan sehat, baik jasmani maupun rohani tanpa adanya paksaaan dari pihak manapun, serta bersedia dikenakan sanksi sesuai ketentuan yang berlaku apabila lalai dalam melaksanakan isi Surat Pernyataan ini.</td>
                </tr>
                <tr>
                    <td width=\"45%\" align=\"center\">&nbsp;
                        <br/>KEPALA DINAS PENDAPATAN DAERAH<br/>
                        KABUPATEN PESAWARAN<br/>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <u><b>$kepala</b></u><br/>NIP. $nip
                    </td>
                    <td width=\"10%\">&nbsp;</td>
                    <td width=\"45%\" align=\"center\">&nbsp;
                        <br/>Kalianda, " . TanggalIndo(date("Y-m-d")) . "<br/>
                        WAJIB PAJAK<br/>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <u><b>".$op["WP_NAMA"]."</b></u><br/>".$noWP."
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">Untuk</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"70\">Lembar I</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"70\">Lembar II</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td coslspan=\"2\" width=\"70\">Lembar III</td>
                    <td width=\"550\" align=\"left\">:</td>
                </tr>
                <tr>
                    <td colspan=\"3\" width=\"700\" align=\"justify\"><br/><strong>NOTE : </strong><i>Silahkan abaikan surat ini apabila wajib pajak telah membayar PBB tahun tertentu dan mohon agar bukti
                    lunas pembayaran PBB dari bank atas tahun dimaksud untuk diserahkan ke seksi penagihan, keberatan
                    dan pengurangan bidang PBB dan BPHTB Dinas Pendapatan Daerah Kabupaten Lampung Selatan.</i>
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
    global $totalBulanPajak, $denda;

    $penalty = 0;

    $month = ceil(countDay($jatuhTempo, date('Y-m-d')) / 30);
    if ($month > $totalBulanPajak) $month = $totalBulanPajak;
    $penalty = $denda * $month * $pbbHarusDibayar / 100;

    return $penalty;
}

function getPbbspptById($nop, $thnpajak)
{
    global $DBLink, $denda, $totalBulanPajak;
    $data = array();
    $thnpajak = date('Y'); // <- revisi munculin terbaru

    // $sql = "SELECT * FROM gw_pbb.pbb_sppt WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thnpajak'";
    $sql = "SELECT * FROM gw_pbb.pbb_sppt WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK<='$thnpajak' ORDER BY SPPT_TAHUN_PAJAK DESC LIMIT 0,1"; // <- revisi munculin terbaru
    $result = mysqli_query($DBLink, $sql);

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
        $data['ID_WP'] = $buffer['ID_WP'];

        $thnsekarang = date("Y");
        $sql_lp = "SELECT 
                    WP_NAMA,
                    SPPT_TAHUN_PAJAK, 
                    SPPT_PBB_HARUS_DIBAYAR,
                    SPPT_TANGGAL_JATUH_TEMPO 
                FROM gw_pbb.pbb_sppt 
                WHERE 
                    NOP='$nop' AND 
                    SPPT_TAHUN_PAJAK<='$thnsekarang' AND 
                    (PAYMENT_FLAG = '0' OR PAYMENT_FLAG IS NULL)
                ORDER BY SPPT_TAHUN_PAJAK DESC";
        $result_lp = mysqli_query($DBLink, $sql_lp);
        if (mysqli_num_rows($result_lp) > 0) {
            while ($buffer_lp = mysqli_fetch_assoc($result_lp)) {
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
        }else{
            $data["LIST_TERHUTANG"] = [];
        }
    } else {
        echo mysqli_error($DBLink);
    }
    return $data;
}


function TanggalIndo($date)
{
	$BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

	$tahun = substr($date, 0, 4);
	$bulan = substr($date, 5, 2);
	$tgl   = substr($date, 8, 2);

	$result = $tgl . " " . $BulanIndo[(int)$bulan - 1] . " " . $tahun;
	return ($result);
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
