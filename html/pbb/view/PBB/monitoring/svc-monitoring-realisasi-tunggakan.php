<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
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

date_default_timezone_set("Asia/Jakarta");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
// var_dump($DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$myDBLink = "";

function headerTable($namakecamatan) {
    global $appConfig, $eperiode, $thn1, $thn2;
    
    $ntabel = ($thn2 - $thn1) + 1;
    $jmlCol = ($ntabel*4) + 5;
    
    if ($namakecamatan) {
        $ket = 'REALISASI TUNGGAKAN KEC. ' . strtoupper($namakecamatan);
    } else {
        $ket = 'REALISASI TUNGGAKAN PAJAK BUMI DAN BANGUNAN';
    }

    $headerAngkaTahun = '';
    $headerKetTahun = '';
    for($thn=$thn2; $thn>=$thn1; $thn--) {
        $headerAngkaTahun .= "<th colspan=4>$thn</th>";
        $headerKetTahun .= "<th>STTS</th><th>PBB</th><th>DENDA</th><th>JUMLAH</th>";
    }

    $html = '<table class="table table-bordered table-striped"><tr><th colspan='.$jmlCol.'><b>'.$ket.'<b></th></tr>
        <tr>
            <th rowspan=2>WILAYAH</th>
            '.$headerAngkaTahun.'
            <th colspan=4>JUMLAH REALISASI</th>
        </tr>
        <tr>
            '.$headerKetTahun.'
            <th>STTS</th>
            <th>PBB</th>
            <th>DENDA</th>
            <th>JUMLAH</th>
        </tr>';
    return $html;
}

function openMysql() {
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname, $myDBLink);
    return $myDBLink;
}

function closeMysql($con) {
    mysqli_close($con);
}

function getData($kode=false) {
    global $myDBLink, $thn1, $thn2, $tanggal1, $tanggal2, $qBuku;																																	

    $myDBLink = openMysql();

    $whr = '';
    if($kode){
        $len = strlen($kode);
        $whr = " AND LEFT(NOP,$len)='$kode'";
    }

    $q="SELECT
            OP_KECAMATAN AS KECAMATAN,
            LEFT(NOP,7) AS KEC, 
            OP_KELURAHAN AS KELURAHAN,
            LEFT(NOP,10) AS KEL, 
            SPPT_TAHUN_PAJAK AS THN,
            COUNT(NOP) AS STTS,
            SUM(SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
            SUM(PBB_DENDA) AS DENDA,
            SUM(PBB_TOTAL_BAYAR) AS TOTAL
        FROM pbb_sppt
        WHERE 
            PAYMENT_FLAG = '1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) >= '$tanggal1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) <= '$tanggal2' 
            AND SPPT_TAHUN_PAJAK >= '$thn1' 
            AND SPPT_TAHUN_PAJAK <= '$thn2' 
            $whr 
            $qBuku
        GROUP BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK
        ORDER BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK DESC";

    $res = mysqli_query($myDBLink, $q);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }
    
    $rows = [];

    $addware = ($kode) ? "WHERE l.CPC_TKL_KCID='$kode'" : "";
    $k="SELECT 
            c.CPC_TKC_KECAMATAN AS KECAMATAN,
            l.CPC_TKL_KCID AS KEC,
            l.CPC_TKL_KELURAHAN AS KELURAHAN,
            l.CPC_TKL_ID AS KEL
        FROM cppmod_tax_kelurahan l
        INNER JOIN cppmod_tax_kecamatan c ON c.CPC_TKC_ID=l.CPC_TKL_KCID
        $addware 
        ORDER BY KEL";

    $datakel = mysqli_query($myDBLink, $k);
    if ($datakel === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($datakel)){
        for($thn=$thn2; $thn>=$thn1; $thn--) { 
            $kec = substr($row['KEC'],0,2) .'.'. substr($row['KEC'],2,2) .'.'. substr($row['KEC'],-3,3);
            $kel = substr($row['KEL'],-3,3);
            $rows[$row['KEC']][$row['KEL']]['KEC'] = $kec;
            $rows[$row['KEC']][$row['KEL']]['KEL'] = $kel;
            $rows[$row['KEC']][$row['KEL']]['KECAMATAN'] = $row['KECAMATAN'];
            $rows[$row['KEC']][$row['KEL']]['KELURAHAN'] = $row['KELURAHAN'];
            $rows[$row['KEC']][$row['KEL']][$thn] = array("STTS"=>0,"POKOK"=>0,"DENDA"=>0,"TOTAL"=>0);
        }
    }

    while ($row = mysqli_fetch_assoc($res)){
        $kec = $row['KEC'];
        $kel = $row['KEL'];
        $thn = (int)$row['THN'];
        $row['KEC'] = substr($row['KEC'],0,2) .'.'. substr($row['KEC'],2,2) .'.'. substr($row['KEC'],-3,3);
        $row['KEL'] = substr($row['KEL'],-3,3);
        
        $rows[$kec][$kel]['KEC'] = $row['KEC'];
        $rows[$kec][$kel]['KEL'] = $row['KEL'];
        $rows[$kec][$kel]['KECAMATAN'] = $row['KECAMATAN'];
        $rows[$kec][$kel]['KELURAHAN'] = $row['KELURAHAN'];

        unset($row['THN']);
        unset($row['KEC']);
        unset($row['KEL']);
        unset($row['KECAMATAN']);
        unset($row['KELURAHAN']);

        $rows[$kec][$kel][$thn] = $row;
    }

    closeMysql($myDBLink);

    // echo '<pre>';
    // print_r($tung);
    // exit;

    // header('Content-Type: application/json; charset=utf-8');
    // print_r(json_encode($rows));
    // exit;
    
    return $rows;
}

function showTable($periode, $namakecamatan=false) {
    global $tanggal1, $tanggal2, $thn1, $thn2, $qBuku, $appConfig, $kodekec;

    $html   = "";
    $ntabel = ($thn2 - $thn1) + 1;
    $jmlCol = ($ntabel*4) + 5;
    $html  .= headerTable($namakecamatan);

    $data = getData($kodekec);
    
    foreach ($data as $dataKecamatan) {
        $labelkel = '';
        foreach ($dataKecamatan as $r) {
            $lblkec = isset($r['KEC']) ? $r['KEC'] . ' - ' . $r['KECAMATAN'] : '-';
            $lblkel = isset($r['KEL']) ? $r['KEL'] . ' - ' . $r['KELURAHAN'] : '-';

            $jmlpbbThn = array('stts'=>0,'pokok'=>0,'denda'=>0,'jumlah'=>0);

            if($labelkel == $lblkec){
                $html .= "<tr class=tright><td class=tleft>$lblkel</td>";
                $thn = $thn2;
                while($thn >= $thn1){
                    $adathn = isset($r[$thn]) ? true : false;
                    $stts  = ($adathn) ? $r[$thn]['STTS'] : 0;
                    $pokok = ($adathn) ? $r[$thn]['POKOK'] : 0;
                    $denda = ($adathn) ? $r[$thn]['DENDA'] : 0;
                    $total = ($adathn) ? $r[$thn]['TOTAL'] : 0;

                    $jmlpbbThn['stts']  += $stts;
                    $jmlpbbThn['pokok'] += $pokok;
                    $jmlpbbThn['denda'] += $denda;
                    $jmlpbbThn['jumlah']+= $total;

                    $stts  = number_format($stts, 0, ",", ".");
                    $pokok = number_format($pokok, 0, ",", ".");
                    $denda = number_format($denda, 0, ",", ".");
                    $total = number_format($total, 0, ",", ".");

                    $html .="<td>$stts</td><td>$pokok</td><td>$denda</td><td>$total</td>";
                    $thn--;
                }

                $stts  = number_format($jmlpbbThn['stts'], 0, ",", ".");
                $pokok = number_format($jmlpbbThn['pokok'], 0, ",", ".");
                $denda = number_format($jmlpbbThn['denda'], 0, ",", ".");
                $total = number_format($jmlpbbThn['jumlah'], 0, ",", ".");
                $html .="<td>$stts</td><td>$pokok</td><td>$denda</td><td>$total</td>";
                $html .= "</tr>";
            }else{
                $labelkel = $lblkec;
                $html .= "<tr class='tbold'><td colspan=$jmlCol>$labelkel</td></tr>";
                $html .= "<tr class=tright><td class=tleft>$lblkel</td>";
                $thn = $thn2;
                while($thn >= $thn1){
                    $adathn = isset($r[$thn]) ? true : false;
                    $stts  = ($adathn) ? $r[$thn]['STTS'] : 0;
                    $pokok = ($adathn) ? $r[$thn]['POKOK'] : 0;
                    $denda = ($adathn) ? $r[$thn]['DENDA'] : 0;
                    $total = ($adathn) ? $r[$thn]['TOTAL'] : 0;

                    $jmlpbbThn['stts']  += $stts;
                    $jmlpbbThn['pokok'] += $pokok;
                    $jmlpbbThn['denda'] += $denda;
                    $jmlpbbThn['jumlah']+= $total;

                    $stts  = number_format($stts, 0, ",", ".");
                    $pokok = number_format($pokok, 0, ",", ".");
                    $denda = number_format($denda, 0, ",", ".");
                    $total = number_format($total, 0, ",", ".");

                    $html .="<td>$stts</td><td>$pokok</td><td>$denda</td><td>$total</td>";
                    $thn--;
                }

                $stts  = number_format($jmlpbbThn['stts'], 0, ",", ".");
                $pokok = number_format($jmlpbbThn['pokok'], 0, ",", ".");
                $denda = number_format($jmlpbbThn['denda'], 0, ",", ".");
                $total = number_format($jmlpbbThn['jumlah'], 0, ",", ".");
                $html .="<td>$stts</td><td>$pokok</td><td>$denda</td><td>$total</td>";
                $html .= "</tr>";
            }
        }
        $html .= "<tr><td colspan=$jmlCol>&nbsp;</td></tr>";
    }

    return $html . "</table>";
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);

$thntagihan = @isset($_REQUEST['thntagihan']) ? $_REQUEST['thntagihan'] : "";
$kodekec    = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : false;

$thn1       = @isset($_REQUEST['th1']) ? $_REQUEST['th1'] : "";
$thn2       = @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : "";
$namakecamatan = @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : false;
$tanggal1   = @isset($_REQUEST['eperiode1']) ? $_REQUEST['eperiode1'] : "";
$tanggal2   = @isset($_REQUEST['eperiode2']) ? $_REQUEST['eperiode2'] : "";
$buku       = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : 0;

if(!$tanggal1 || !$tanggal2) die('');

$qBuku = "";
if($buku != 0){
    switch ($buku){
        case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
        case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
        case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
        case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
        case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
        case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
        case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
        case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
        case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
        case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
        case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
    }
}

$periode1 = date("d-m-Y", strtotime($tanggal1));
$periode2 = date("d-m-Y", strtotime($tanggal2));
$periode = "PERIODE $periode1 S/D $periode2";

if($kodekec) {
    echo showTable($periode, $namakecamatan);
} else {
    echo showTable($periode, false);
}