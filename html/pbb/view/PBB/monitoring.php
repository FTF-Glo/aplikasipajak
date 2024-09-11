<?php
// prevent direct access
if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbGwMonitor.php");
$appConfig = $User->GetAppConfig($application);

SCANPayment_ConnectToDB($GWDBLink, $GWDBConn, $appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$GWdbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $GWDBLink);
$dbUtils = new DbUtils($dbSpec);
$usrPbb = $dbUtils->getUserDetailPbb($uid);
$aKabkota = $dbUtils->getKabKota($usrPbb[0]['kota']);
$arrConn ['HOST'] = "10.24.110.3";
$arrConn ['PORT'] = "5432";
$arrConn ['USER'] = "payment_pbb";
$arrConn ['PASS'] = "SS26P@ssw0rd";
$arrConn ["DBNAME"] = "db-pajak-palembangkota"; 
if (!isset($opt)) {
    //tampilan paling utama, dibedakan berdasarkan tahun saja.
    $dbGwMonitor = new DbGwMonitor($GWdbSpec, $usrPbb[0]['kota'],$arrConn,"postgres");
    $dbGwMonitor->group_by("SPPT_TAHUN_PAJAK,PBB_TOTAL_BAYAR");
    $dbGwMonitor->order_by("SPPT_TAHUN_PAJAK DESC");
    $result = $dbGwMonitor->get(array(
        "SPPT_TAHUN_PAJAK",
        "count(NOP) as SPPT_COUNT",
        "sum(SPPT_PBB_HARUS_DIBAYAR) as SPPT_PBB_HARUS_DIBAYAR"
            ));
    //echo $dbGwMonitor->last_query();
    ?>

    <h3>Monitoring SPPT daerah <?php echo  $aKabkota[0]["CPC_TK_KABKOTA"] ?></h3>
    <table cellpadding="5">
        <tr>
            <th>TAHUN</th>
            <th>JUMLAH SPPT</th>
            <th>JUMLAH TAGIHAN</th>
            <th>JUMLAH PENERIMAAN</th>
            <th>SUDAH BAYAR</th>
        </tr>
        <?php
					print_r($result);
        foreach ($result as $row) {
            $dbGwMonitor->where(array("PAYMENT_FLAG" => 1, "SPPT_TAHUN_PAJAK" => $row['SPPT_TAHUN_PAJAK']));
            $result2 = $dbGwMonitor->get(array("COUNT(NOP) as SPPT_COUNT", "PBB_TOTAL_BAYAR"));
            $prm = base64_encode("a=$a&m=$m&opt=kec&thn=" . $row['SPPT_TAHUN_PAJAK']);

//    echo $dbGwMonitor->last_query()."<br>";
            echo "<tr>";
            echo "  <td align='center'><a href='main.php?param=$prm'>" . $row['SPPT_TAHUN_PAJAK'] . "</a></td>";
            echo "  <td>" . $row['SPPT_COUNT'] . "</td>";
            echo "  <td align='right'>Rp. " . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td>";
            echo "  <td align='right'>Rp. " . number_format($result2[0]['PBB_TOTAL_BAYAR'], 0, ',', '.') . "</td>";
            echo "  <td>" . $result2[0]['SPPT_COUNT'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <?php
} else if ($opt == "kec") {
    //tampilan berdasarkan tahun tertentu, didaftarkan per kecamatan
    $dbGwMonitor = new DbGwMonitor($GWdbSpec, $usrPbb[0]['kota']);
    $dbGwMonitor->group_by("OP_KECAMATAN_KODE");
    $dbGwMonitor->order_by("OP_KECAMATAN ASC");
    $dbGwMonitor->where(array("SPPT_TAHUN_PAJAK" => $thn));
    $result = $dbGwMonitor->get(array(
        "OP_KECAMATAN",
        "count(NOP) as SPPT_COUNT",
        "sum(SPPT_PBB_HARUS_DIBAYAR) as SPPT_PBB_HARUS_DIBAYAR",
        "OP_KECAMATAN_KODE"
            ));
//    echo $dbGwMonitor->last_query();
//    print_r($_REQUEST);
    ?>

    <h3>Monitoring SPPT daerah <?php echo  $aKabkota[0]["CPC_TK_KABKOTA"] ?> tahun <?php echo  $thn ?> </h3>
    <a href="main.php?param=<?php echo  base64_encode("a=$a&m=$m") ?>">[kembali]</a>
    <table cellpadding="5">
        <tr>
            <th>KECAMATAN</th>
            <th>JUMLAH SPPT</th>
            <th>JUMLAH TAGIHAN</th>
            <th>JUMLAH PENERIMAAN</th>
            <th>SUDAH BAYAR</th>
        </tr>
        <?php
        foreach ($result as $row) {
            $dbGwMonitor->where(array("PAYMENT_FLAG" => 1, "OP_KECAMATAN_KODE" => $row['OP_KECAMATAN_KODE']));
            $result2 = $dbGwMonitor->get(array("count(NOP) as SPPT_COUNT", "PBB_TOTAL_BAYAR"));
            $prm = base64_encode("a=$a&m=$m&opt=kel&thn=$thn&kec=" . $row['OP_KECAMATAN_KODE']);

//            echo $dbGwMonitor->last_query()."<br>";
            echo "<tr>";
            echo "  <td align='center'><a href='main.php?param=$prm'>" . $row['OP_KECAMATAN'] . "</a></td>";
            echo "  <td>" . $row['SPPT_COUNT'] . "</td>";
            echo "  <td align='right'>Rp. " . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td>";
            echo "  <td align='right'>Rp. " . number_format($result2[0]['PBB_TOTAL_BAYAR'], 0, ',', '.') . "</td>";
            echo "  <td>" . $result2[0]['SPPT_COUNT'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <?php
} else if ($opt == "kel") {
    //tampilan berdasarkan tahun tertentu dan kecamatan tertentu, didaftarkan per kelurahan
    $dbGwMonitor = new DbGwMonitor($GWdbSpec, $usrPbb[0]['kota']);
    $dbGwMonitor->group_by("OP_KELURAHAN_KODE");
    $dbGwMonitor->order_by("OP_KELURAHAN ASC");
    $dbGwMonitor->where(array("SPPT_TAHUN_PAJAK" => $thn, "OP_KECAMATAN_KODE" => $kec));
    $result = $dbGwMonitor->get(array(
        "OP_KELURAHAN",
        "count(NOP) as SPPT_COUNT",
        "sum(SPPT_PBB_HARUS_DIBAYAR) as SPPT_PBB_HARUS_DIBAYAR",
        "OP_KELURAHAN_KODE",
        "OP_KECAMATAN_KODE"
            ));
    $aKecamatan = $dbUtils->getKecamatan($kec);
//    echo $dbGwMonitor->last_query();
    ?>

    <h3>Monitoring SPPT Kecamatan <?php echo  $aKecamatan[0]["CPC_TKC_KECAMATAN"] ?> tahun <?php echo  $thn ?> </h3>
    <a href="main.php?param=<?php echo  base64_encode("a=$a&m=$m&opt=kec&thn=$thn") ?>">[kembali]</a>
    <table cellpadding="5">
        <tr>
            <th>KELURAHAN</th>
            <th>JUMLAH SPPT</th>
            <th>JUMLAH TAGIHAN</th>
            <th>JUMLAH PENERIMAAN</th>
            <th>SUDAH BAYAR</th>
        </tr>
        <?php
        foreach ($result as $row) {
            $dbGwMonitor->where(array("PAYMENT_FLAG" => 1, "OP_KECAMATAN_KODE" => $row['OP_KECAMATAN_KODE'], "OP_KELURAHAN_KODE" => $row['OP_KELURAHAN_KODE']));
            $result2 = $dbGwMonitor->get(array("count(NOP) as SPPT_COUNT", "PBB_TOTAL_BAYAR"));
            $prm = base64_encode("a=$a&m=$m&opt=kel&thn=$thn&kec=" . $row['OP_KECAMATAN_KODE']);

//            echo $dbGwMonitor->last_query()."<br>";
            echo "<tr>";
            echo "  <td align='center'><a href='main.php?param=$prm'>" . $row['OP_KELURAHAN'] . "</a></td>";
            echo "  <td>" . $row['SPPT_COUNT'] . "</td>";
            echo "  <td align='right'>Rp. " . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td>";
            echo "  <td align='right'>Rp. " . number_format($result2[0]['PBB_TOTAL_BAYAR'], 0, ',', '.') . "</td>";
            echo "  <td>" . $result2[0]['SPPT_COUNT'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
<?php
}
//echo "<pre>"; print_r($dbGwMonitor->querylog()); echo "</pre>";
?>