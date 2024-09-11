<?php
// session_start();

// // error_reporting(E_ALL);
// // ini_set('display_errors', 1);
// var_dump("asd");exit;
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

// echo "<link type=\"text/css\\\" href=\"inc/development-bundle/themes/base/ui.all.css\" rel=\"stylesheet\" />";
// echo "<script type=\"text/javascript\" src=\"development-bundle/ui/ui.datepicker.js\"></script>";


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



$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$isViewData = @isset($_REQUEST['isViewData']) ? $_REQUEST['isViewData'] : 0;
// echo $page;
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;
$kepala = $q->kepala;
$nip = $q->nip;
$jabatan = $q->jabatan;
$kota = $q->kota;
$sp1 = $q->sp1;
$sp2 = $q->sp2;
$sp3 = $q->sp3;
$bank = isset($q->bank) ? $q->bank : '';
$perpage = $q->perpage;
$kodekota = $q->kodekota;
$isAdminPenagihan = $q->adm;
$lblkel = $q->lblkel;
$thn = $q->thn;


SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $q->dbhost, $q->dbuser, $q->dbpwd, $q->dbname);


//set new page
if (isset($_SESSION['stLaporan'])) {
    if ($_SESSION['stLaporan'] != $s) {
        $_SESSION['stLaporan'] = $s;
        $find = "";
        $page = 1;
        $np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
        $isViewData = 0;
    }
} else {
    $_SESSION['stLaporan'] = $s;
}

$condition = explode("&", $find);
// var_dump($find);
// print_r($condition);echo '<br>';

$modNotaris = new  SPTPDService($uname);
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($perpage);
$modNotaris->setDefaultPage(1);

$modNotaris->displayDataNotaris();


class SPTPDService
{
    public $user;
    public $status;
    public $perpage = 0;
    public $totalRows = 0;
    public $defaultPage = 0;

    function __construct($user)
    {
        $this->user = $user;
    }

    function setDataPerPage($perpage)
    {
        $this->perpage = $perpage;
    }

    function setDefaultPage($defaultPage)
    {
        $this->defaultPage = $defaultPage;
    }

    function setStatus($status)
    {
        $this->status = $status;
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

    function getTotalRows($query)
    {
        global $DBLinkLookUp;
        $res = mysqli_query($DBLinkLookUp, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['TOTALROWS'];
    }

    function conditionBuilder()
    {
        global $condition;
        // var_dump($condition);
        // exit;
        $condQuery = "";

        if ($condition[0] != "") $condQuery .= " AND (A.WP_NAMA LIKE '%" . $condition[0] . "%') ";
        // var_dump($condition[1]);
        // exit;
        if ($condition[1] != 0) {
            switch ($condition[1]) {
                case 1:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR < 5000000) ";
                    break;
                case 2:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 10000000) ";
                    break;
                case 3:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 20000000) ";
                    break;
                case 4:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 30000000) ";
                    break;
                case 5:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 40000000) ";
                    break;
                case 6:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 50000000) ";
                    break;
                case 7:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 100000000) ";
                    break;
                case 8:
                    $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100000000) ";
                    break;
            }
        }

        if ($condition[3] != "") $condQuery .= " AND (A.NOP like'" . $condition[3] . "%') ";
        else if ($condition[2] != "") $condQuery .= " AND (A.NOP like'" . $condition[2] . "%') ";

        if ($condition[4] != "") $condQuery .= " AND (A.SPPT_TAHUN_PAJAK ='" . $condition[4] . "') ";
        if ($condition[5] != "") $condQuery .= " AND (MID(A.NOP, 1, 2) = '" . $condition[5] . "') ";
        if ($condition[6] != "") $condQuery .= " AND (MID(A.NOP, 3, 2) = '" . $condition[6] . "') ";
        if ($condition[7] != "") $condQuery .= " AND (MID(A.NOP, 5, 3) = '" . $condition[7] . "') ";
        if ($condition[8] != "") $condQuery .= " AND (MID(A.NOP, 8, 3) = '" . $condition[8] . "') ";
        if ($condition[9] != "") $condQuery .= " AND (MID(A.NOP, 11, 3) = '" . $condition[9] . "') ";
        if ($condition[10] != "") $condQuery .= " AND (MID(A.NOP, 14, 4) = '" . $condition[10] . "') ";
        if ($condition[11] != "") $condQuery .= " AND (MID(A.NOP, 18, 1) = '" . $condition[11] . "') ";

        return $condQuery;
    }

    function conditionBuilderNotView()
    {
        global $condition;

        $condQuery = "";


        if ($condition[0] != "") $condQuery .= " AND (C.WP_NAMA LIKE '%" . $condition[0] . "%') ";

        if ($condition[1] != 0) {
            switch ($condition[1]) {
                case 1:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR < 5000000) ";
                    break;
                case 2:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 10000000) ";
                    break;
                case 3:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 20000000) ";
                    break;
                case 4:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 30000000) ";
                    break;
                case 5:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 40000000) ";
                    break;
                case 6:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 50000000) ";
                    break;
                case 7:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 100000000) ";
                    break;
                case 8:
                    $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 100000000) ";
                    break;
            }
        }

        if ($condition[3] != "") $condQuery .= " AND (C.NOP like'" . $condition[3] . "%') ";
        else if ($condition[2] != "") $condQuery .= " AND (C.NOP like'" . $condition[2] . "%') ";

        if ($condition[4] != "") $condQuery .= " AND (C.SPPT_TAHUN_PAJAK ='" . $condition[4] . "') ";
        if ($condition[5] != "") $condQuery .= " AND (MID(A.NOP, 1, 2) = '" . $condition[5] . "') ";
        if ($condition[6] != "") $condQuery .= " AND (MID(A.NOP, 3, 2) = '" . $condition[6] . "') ";
        if ($condition[7] != "") $condQuery .= " AND (MID(A.NOP, 5, 3) = '" . $condition[7] . "') ";
        if ($condition[8] != "") $condQuery .= " AND (MID(A.NOP, 8, 3) = '" . $condition[8] . "') ";
        if ($condition[9] != "") $condQuery .= " AND (MID(A.NOP, 11, 3) = '" . $condition[9] . "') ";
        if ($condition[10] != "") $condQuery .= " AND (MID(A.NOP, 14, 4) = '" . $condition[10] . "') ";
        if ($condition[11] != "") $condQuery .= " AND (MID(A.NOP, 18, 1) = '" . $condition[11] . "') ";

        return $condQuery;
    }

    function getDocument($sts, &$dat)
    {
        global $DBLinkLookUp, $DBLink, $sRootPath, $json, $a, $m, $page, $sp1, $sp2, $sp3, $isAdminPenagihan;

        // echo $sts;exit;
        if ($sts == 1)
            $where = $this->conditionBuilder();
        elseif ($sts == 10)
            $where = $this->conditionBuilder();
        else
            $where = $this->conditionBuilderNotView();
        $sp = "";
        // echo $this->conditionBuilder();
        // $where = $this->conditionBuilder();
        // print_r($where);exit;
        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        $fieldKetetapan = "";
        switch ($sts) {
            case 2:
                $sp .= "AND (B.TGL_SP1 IS NOT NULL OR B.TGL_SP1 <> '') AND (B.TGL_SP2 IS NULL OR B.TGL_SP2 = '') AND B.STATUS_SP = 1 AND B.STATUS_PERSETUJUAN = 1";
                break;
            case 3:
                $sp .= "AND (B.TGL_SP3 IS NULL OR B.TGL_SP3 = '') AND B.STATUS_PERSETUJUAN = 2 ";
                break;
            case 6:
                $sp .= "AND (B.TGL_SP1 IS NOT NULL OR B.TGL_SP1 <> '') AND (B.TGL_SP2 IS NULL OR B.TGL_SP2 = '') AND (B.TGL_SP3 IS NULL OR B.TGL_SP3 = '') AND B.STATUS_SP <> 1";
                break;
            case 7:
                $sp .= "AND (B.TGL_SP1 IS NOT NULL OR B.TGL_SP1 <> '') AND (B.TGL_SP2 IS NOT NULL OR B.TGL_SP2 <> '') AND (B.TGL_SP3 IS NULL OR B.TGL_SP3 = '') AND B.STATUS_SP <> 1";
                break;
            case 8:
                $sp .= "AND (B.TGL_SP1 IS NOT NULL OR B.TGL_SP1 <> '') AND (B.TGL_SP2 IS NOT NULL OR B.TGL_SP2 <> '') AND (B.TGL_SP3 IS NOT NULL OR B.TGL_SP3 <> '') AND B.STATUS_SP <> 1";
                break;
            case 9:
                $sp .= "AND B.STATUS_SP = 1";
                break;
        }

        if ($sts == 1) { //Tab SP1
            //$query = "SELECT * FROM VIEW_SP1_GROUPED WHERE SPPT_TAHUN_PAJAK >= '2007' $where LIMIT ".$hal.",".$this->perpage;
            $query = "SELECT
                            A.`NOP`             AS `NOP`,
                            A.`SPPT_TAHUN_PAJAK`AS `SPPT_TAHUN_PAJAK`,
                            A.`WP_NAMA`         AS `WP_NAMA`,
                            A.`WP_ALAMAT`       AS `WP_ALAMAT`,
                            A.`WP_KELURAHAN`    AS `WP_KELURAHAN`,
                            A.`OP_ALAMAT`       AS `OP_ALAMAT`,
                            A.`OP_KECAMATAN`    AS `OP_KECAMATAN`,
                            A.`OP_KELURAHAN`    AS `OP_KELURAHAN`,
                            A.`OP_RT`           AS `OP_RT`,
                            A.`OP_RW`           AS `OP_RW`,
                            A.`OP_LUAS_BUMI`    AS `OP_LUAS_BUMI`,
                            A.`OP_LUAS_BANGUNAN`AS `OP_LUAS_BANGUNAN`,
                            A.`OP_NJOP_BUMI`    AS `OP_NJOP_BUMI`,
                            A.`OP_NJOP_BANGUNAN`AS `OP_NJOP_BANGUNAN`,
                            A.`SPPT_TANGGAL_JATUH_TEMPO`AS `SPPT_TANGGAL_JATUH_TEMPO`,
                            A.`SPPT_PBB_HARUS_DIBAYAR`  AS `SPPT_PBB_HARUS_DIBAYAR`
                    FROM PBB_SPPT A
                    LEFT JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.NOP IS NULL AND
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where 
                    ORDER BY A.`OP_KELURAHAN_KODE`, A.`NOP` ASC, A.SPPT_TAHUN_PAJAK DESC 
                    LIMIT " . $hal . "," . $this->perpage;
            // echo $query;
            // exit;

            $qry = "SELECT COUNT(*) AS TOTALROWS 
                    FROM PBB_SPPT A
                    LEFT JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.NOP IS NULL AND
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where";
        }elseif ($sts == 2) { //Tab SP2
            $query = "SELECT
                            A.`NOP`             AS `NOP`,
                            A.`SPPT_TAHUN_PAJAK`AS `SPPT_TAHUN_PAJAK`,
                            A.`WP_NAMA`         AS `WP_NAMA`,
                            A.`WP_ALAMAT`       AS `WP_ALAMAT`,
                            A.`WP_KELURAHAN`    AS `WP_KELURAHAN`,
                            A.`OP_ALAMAT`       AS `OP_ALAMAT`,
                            A.`OP_KECAMATAN`    AS `OP_KECAMATAN`,
                            A.`OP_KELURAHAN`    AS `OP_KELURAHAN`,
                            A.`OP_RT`           AS `OP_RT`,
                            A.`OP_RW`           AS `OP_RW`,
                            A.`OP_LUAS_BUMI`    AS `OP_LUAS_BUMI`,
                            A.`OP_LUAS_BANGUNAN`AS `OP_LUAS_BANGUNAN`,
                            A.`OP_NJOP_BUMI`    AS `OP_NJOP_BUMI`,
                            A.`OP_NJOP_BANGUNAN`AS `OP_NJOP_BANGUNAN`,
                            A.`SPPT_TANGGAL_JATUH_TEMPO`AS `SPPT_TANGGAL_JATUH_TEMPO`,
                            A.`SPPT_PBB_HARUS_DIBAYAR`  AS `SPPT_PBB_HARUS_DIBAYAR`
                    FROM PBB_SPPT A
                    INNER JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.TGL_SP1 IS NOT NULL AND 
                        B.TGL_SP2 IS NULL AND 
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where 
                    ORDER BY A.`OP_KELURAHAN_KODE`, A.`NOP` ASC, A.SPPT_TAHUN_PAJAK DESC 
                    LIMIT " . $hal . "," . $this->perpage;
            // echo $query;
            // exit;

            $qry = "SELECT COUNT(*) AS TOTALROWS 
                    FROM PBB_SPPT A
                    INNER JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.TGL_SP1 IS NOT NULL AND 
                        B.TGL_SP2 IS NULL AND 
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where";
        }elseif ($sts == 3) { //Tab SP3
            $query = "SELECT
                            A.`NOP`             AS `NOP`,
                            A.`SPPT_TAHUN_PAJAK`AS `SPPT_TAHUN_PAJAK`,
                            A.`WP_NAMA`         AS `WP_NAMA`,
                            A.`WP_ALAMAT`       AS `WP_ALAMAT`,
                            A.`WP_KELURAHAN`    AS `WP_KELURAHAN`,
                            A.`OP_ALAMAT`       AS `OP_ALAMAT`,
                            A.`OP_KECAMATAN`    AS `OP_KECAMATAN`,
                            A.`OP_KELURAHAN`    AS `OP_KELURAHAN`,
                            A.`OP_RT`           AS `OP_RT`,
                            A.`OP_RW`           AS `OP_RW`,
                            A.`OP_LUAS_BUMI`    AS `OP_LUAS_BUMI`,
                            A.`OP_LUAS_BANGUNAN`AS `OP_LUAS_BANGUNAN`,
                            A.`OP_NJOP_BUMI`    AS `OP_NJOP_BUMI`,
                            A.`OP_NJOP_BANGUNAN`AS `OP_NJOP_BANGUNAN`,
                            A.`SPPT_TANGGAL_JATUH_TEMPO`AS `SPPT_TANGGAL_JATUH_TEMPO`,
                            A.`SPPT_PBB_HARUS_DIBAYAR`  AS `SPPT_PBB_HARUS_DIBAYAR`
                    FROM PBB_SPPT A
                    INNER JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.TGL_SP2 IS NOT NULL AND 
                        B.TGL_SP3 IS NULL AND 
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where 
                    ORDER BY A.`OP_KELURAHAN_KODE`, A.`NOP` ASC, A.SPPT_TAHUN_PAJAK DESC 
                    LIMIT " . $hal . "," . $this->perpage;
            // echo $query;
            // exit;

            $qry = "SELECT COUNT(*) AS TOTALROWS 
                    FROM PBB_SPPT A
                    INNER JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                    WHERE
                        ( A.PAYMENT_FLAG = '0' OR A.PAYMENT_FLAG = '' OR A.PAYMENT_FLAG IS NULL ) AND 
                        B.TGL_SP2 IS NOT NULL AND
                        NOW() > A.SPPT_TANGGAL_JATUH_TEMPO 
                        $where";
        } else {
            $query = "SELECT * 
                    FROM
                        (SELECT
                            A.NOP,
                            A.WP_NAMA,
                            A.WP_ALAMAT,
                            A.WP_KELURAHAN,
                            A.OP_ALAMAT,
                            A.OP_KECAMATAN,
                            A.OP_KELURAHAN,
                            A.OP_RT,
                            A.OP_RW,
                            A.OP_LUAS_BUMI,
                            A.OP_LUAS_BANGUNAN,
                            A.OP_NJOP_BUMI,
                            A.OP_NJOP_BANGUNAN,
                            A.SPPT_TANGGAL_JATUH_TEMPO,
                            A.PAYMENT_FLAG,
                            A.SPPT_TAHUN_PAJAK,
                            B.TAHUN_SP1,
                            B.STATUS_SP,
                            B.TGL_SP1,
                            B.TGL_SP2,
                            B.TGL_SP3,
                            B.KETETAPAN_SP1,
                            B.KETETAPAN_SP2,
                            B.KETETAPAN_SP3,
                            B.KETERANGAN_SP1,
                            B.KETERANGAN_SP2,
                            B.KETERANGAN_SP3,
                            B.STATUS_PERSETUJUAN
                        FROM PBB_SPPT A
                        JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                        WHERE 1=1 
                            $sp 
                            $where
                        ORDER BY A.SPPT_TAHUN_PAJAK DESC
                        ) AS PENAGIHAN
                    GROUP BY NOP 
                    LIMIT " . $hal . "," . $this->perpage;
            // echo $query;
            // exit;
            if ($sts == 9)
                // $qry = "SELECT COUNT(DISTINCT(NOP)) AS TOTALROWS FROM
                // (SELECT A.NOP FROM PBB_SPPT_PENAGIHAN A
                // JOIN PBB_SPPT C WHERE A.NOP = C.NOP $sp $where
                // ORDER BY C.SPPT_TAHUN_PAJAK DESC) AS PENAGIHAN";
                $qry = "SELECT COUNT(*) AS TOTALROWS
                        FROM PBB_SPPT A
                        JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                        WHERE 1 = 1
                        $sp 
                        $where 
                        ORDER BY A.SPPT_TAHUN_PAJAK DESC";
            else
                // $qry = "SELECT COUNT(*) AS TOTALROWS 
                // FROM PBB_SPPT_PENAGIHAN A
                // JOIN PBB_SPPT C WHERE A.NOP = C.NOP $sp $where
                // ORDER BY C.SPPT_TAHUN_PAJAK DESC";
                $qry = "SELECT COUNT(DISTINCT(A.NOP)) AS TOTALROWS
                        FROM PBB_SPPT A
                        JOIN PBB_SPPT_PENAGIHAN B ON A.NOP = B.NOP
                        WHERE 1 = 1 
                             $sp 
                             $where
                        ORDER BY A.SPPT_TAHUN_PAJAK DESC";
        }
        // echo $query . "<br>";
        // echo $qry . "<br><br>";
        // echo $query."<br>";
        // echo $qry;

        $res = mysqli_query($DBLinkLookUp, $query);
        if ($res === false || mysqli_num_rows($res) <= 0) {
            return false;
        }

        // if($sts !=1 )
        $this->totalRows = $this->getTotalRows($qry);
        // else $this->totalRows = 0;
        //$d =  $json->decode($this->mysql2json($res,"data"));
        $HTML = $startLink = $endLink = "";
        //$data = $d;
        $params = "a=" . $a . "&m=" . $m;

        $arrStatus = array(
            1 => "SP1 yang sudah diterima Wajib Pajak",
            2 => "Wajib Pajak yang sudah membayar PBB setelah penerbitaan SP 1",
            3 => "Data Wajib Pajak yang dibatalkan",
            4 => "Alamat tidak ditemukan",
            5 => "Tanah sengketa",
            6 => "Wajib Pajak sudah melakukan perubahan data"
        );

        $i = 1;
        while ($tmp = mysqli_fetch_assoc($res)) {
            $tgltempo = explode("-", $tmp['SPPT_TANGGAL_JATUH_TEMPO']);

            $class = $i % 2 == 0 ? "" : "";
            $newStyle = $newStyle = "";

            //			if($tmp['STATUS_CETAK'] == "Belum Tercetak"){
            //				 $newStyle = "style='font-weight:bold;'";
            //			}

            $linkprint = "_linkto";
            $tahunPajak = $tmp['SPPT_TAHUN_PAJAK'];
            if ($sts == 5) {
                $linkprint = "linkstpd";
                $newStyle = "";
            } elseif ($sts == 6 || $sts == 7 || $sts == 8 || $sts == 9) {
                $linkprint = "linkcetakpsp" . $sts;
                $tahunPajak = $tmp['TAHUN_SP1'];
            } else if ($sts == 2 || $sts == 3) {
                $linkprint = "_linkcetakup" . $sts;
                $tahunPajak = $tmp['TAHUN_SP1'];
            }
            $tagihan     = $tmp['SPPT_PBB_HARUS_DIBAYAR'];
            if (isset($tmp['TGL_SP1']) && (!$tmp['TGL_SP2']) && (!$tmp['TGL_SP3'])) {
                $jnsSP = "SP1";
                $tagihan = $tmp['KETETAPAN_SP1'];
                $statusPersetujuan = 1;
            } elseif (isset($tmp['TGL_SP1']) && ($tmp['TGL_SP2']) && (!$tmp['TGL_SP3'])) {
                $jnsSP = "SP2";
                $tagihan = $tmp['KETETAPAN_SP2'];
                $statusPersetujuan = 2;
            } elseif (isset($tmp['TGL_SP1']) && ($tmp['TGL_SP2']) && ($tmp['TGL_SP3'])) {
                $jnsSP = "SP3";
                $tagihan = $tmp['KETETAPAN_SP3'];
                $statusPersetujuan = 3;
            }

            $HTML .= "<tr class=tcenter>";
            $HTML .= ($sts<=3) ? '<td><input class="ckc'.$sts.'" type="checkbox" name="ckc'.$sts.'[]" value="'.$tmp['NOP'].'_'.$tmp['SPPT_TAHUN_PAJAK'].'"></td>' : '';
            $HTML .= "<td>" . $tmp['SPPT_TAHUN_PAJAK'] . "</td>";
            if ($sts == 9)
                $HTML .= "<td class=\"$linkprint\" id=\"" . $tmp['NOP'] . "+" . $tahunPajak . "+" . $statusPersetujuan . "\">" . $tmp['NOP'] . "</td>";
            else
                $HTML .= "<td class=\"$linkprint\" id=\"" . $tmp['NOP'] . "+" . $tahunPajak . "\">" . $tmp['NOP'] . "</td>";

            $HTML .= "<td class=tleft>" . $tmp['WP_NAMA'] . "</td>";
            $HTML .= "<td class=tleft>" . $tmp['WP_ALAMAT'] . "</td>";
            $HTML .= "<td class=tleft>" . $tmp['WP_KELURAHAN'] . "</td>";
            $HTML .= "<td class=tleft>" . $tmp['OP_ALAMAT'] . "</td>";
            $HTML .= "<td class=tleft>" . $tmp['OP_KECAMATAN'] . "</td>";
            $HTML .= "<td class=tleft>" . $tmp['OP_KELURAHAN'] . "</td>";
            $HTML .= "<td>" . $tmp['OP_RT'] . "</td>";
            $HTML .= "<td>" . $tmp['OP_RW'] . "</td>";
            $HTML .= "<td>" . number_format($tmp['OP_LUAS_BUMI'], 0, ',', '.') . "</td>";
            $HTML .= "<td>" . number_format($tmp['OP_LUAS_BANGUNAN'], 0, ',', '.') . "</td>";
            $HTML .= "<td class=tright>" . number_format($tmp['OP_NJOP_BUMI'], 0, ',', '.') . "</td>";
            $HTML .= "<td class=tright>" . number_format($tmp['OP_NJOP_BANGUNAN'], 0, ',', '.') . "</td>";
            $HTML .= "<td>" . ($tgltempo[2] . "-" . $tgltempo[1] . "-" . $tgltempo[0]) . "</td>";
            $HTML .= "<td class=tright>" . number_format($tagihan, 0, ',', '.') . "</td>";
            //			$HTML .= "<td>".$tmp['STATUS_CETAK']."</td>"; 
            // if($isAdminPenagihan == 1){
            // echo $linkprint;
            if ($sts != 4) {
                if ($sts == 5) {
                    $linkdate = "linkdate";
                    if ($tmp['TGL_STPD'] == "")
                        $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+" . $tmp['STATUS_SP'] . "\">Input</td>";
                    else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+" . $tmp['STATUS_SP'] . "\">" . substr($tmp['TGL_STPD'], 8, 2) . "-" . substr($tmp['TGL_STPD'], 5, 2) . "-" . substr($tmp['TGL_STPD'], 0, 4) . "</td>";

                    $linkdate = "linkketerangan";
                    if ($tmp['KETERANGAN_STPD'] == "")
                        $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP" . $tmp['STATUS_SP'] . "\">Input</td>";
                    else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP" . $tmp['STATUS_SP'] . "\">" . $tmp['KETERANGAN_STPD'] . "</td>";
                } elseif ($sts == 6 || $sts == 7 || $sts == 8) {
                    switch ($sts) {
                        case 6:
                            $keterangan = $tmp['KETERANGAN_SP1'];
                            break;
                        case 7:
                            $keterangan = $tmp['KETERANGAN_SP2'];
                            break;
                        case 8:
                            $keterangan = $tmp['KETERANGAN_SP3'];
                            break;
                    }
                    $linkdate = "linkketpsp1";
                    $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP" . $sts . "+" . $tmp['STATUS_SP'] . "+" . $keterangan . "\">" . (($tmp['STATUS_SP'] == '' || $tmp['STATUS_SP'] == NULL || $tmp['STATUS_SP'] == 0) ? "Input" : $arrStatus[$tmp['STATUS_SP']]) . "</td>";
                    $HTML .= "<td>" . $keterangan . "</td>";
                } else if ($sts == 9) {
                    $HTML .= "<td>" . $jnsSP . "</td>";
                    $HTML .= "<td>" . (($tmp['STATUS_PERSETUJUAN'] == '1' || $tmp['STATUS_PERSETUJUAN'] == '2' || $tmp['STATUS_PERSETUJUAN'] == '3') ? "Sudah disetujui" : "<button class=\"btnApprove\" value=\"" . $tmp['NOP'] . "+" . $statusPersetujuan . "\">Setuju</button>") . "</td>";
                }
            } else {
                $HTML .= "<td>" . $tmp['STATUS_SP'] . "</td>";
                $HTML .= "<td></td>";
            }
            // }
            $HTML .= "</tr>";

            $i++;
        }
        $dat = $HTML;
        return true;
    }

    function getDocumentAllSP($sts, &$dat)
    {
        global $DBLinkLookUp, $DBLink, $sRootPath, $json, $a, $m, $page, $sp1, $sp2, $sp3, $isAdminPenagihan;


        $where = $this->conditionBuilder();

        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        $qrangetime = "";

        $query = "SELECT
                    A.NOP,
                    A.SPPT_TAHUN_PAJAK,
                    A.WP_NAMA,
                    A.WP_KELURAHAN,
                    A.WP_ALAMAT,
                    A.OP_ALAMAT,
                    A.OP_KECAMATAN,
                    A.OP_KELURAHAN,
                    A.OP_RT,
                    A.OP_RW,
                    A.OP_LUAS_BUMI,
                    A.OP_LUAS_BANGUNAN,
                    A.OP_NJOP_BUMI,
                    A.OP_NJOP_BANGUNAN,
                    A.SPPT_TANGGAL_JATUH_TEMPO,
                    A.SPPT_PBB_HARUS_DIBAYAR,
                    P.TGL_SP1,
                    P.TGL_SP2,
                    P.TGL_SP3,
                    P.TGL_STPD,
                    P.KETERANGAN_STPD,
                    P.KETERANGAN_SP
                FROM PBB_SPPT_PENAGIHAN P
                INNER JOIN PBB_SPPT A ON A.NOP=P.NOP AND A.SPPT_TAHUN_PAJAK=P.TAHUN 
                WHERE 
                    A.SPPT_TAHUN_PAJAK >= '2007' 
                    $qrangetime 
                    $where 
                ORDER BY A.OP_KELURAHAN_KODE, A.NOP ASC, A.SPPT_TAHUN_PAJAK DESC
                LIMIT " . $hal . "," . $this->perpage;
        // print_r($query);exit;
        $qry = "SELECT COUNT(*) AS TOTALROWS 
                FROM PBB_SPPT_PENAGIHAN P
                INNER JOIN PBB_SPPT A ON A.NOP=P.NOP AND A.SPPT_TAHUN_PAJAK=P.TAHUN 
                WHERE 
                    A.SPPT_TAHUN_PAJAK >= '2007' 
                    $qrangetime 
                    $where";
        // echo $query . "<br>";
        // echo $qry . "<br><br>";
        // exit;

        $res = mysqli_query($DBLinkLookUp, $query);
        if ($res === false) {
            return false;
        }

        $this->totalRows = $this->getTotalRows($qry);

        $HTML = $startLink = $endLink = "";

        $params = "a=" . $a . "&m=" . $m;

        $i = 1;
        while ($tmp = mysqli_fetch_assoc($res)) {
            $tgltempo = explode("-", $tmp['SPPT_TANGGAL_JATUH_TEMPO']);

            $HTML .= "<tr class=tcenter>";
            $HTML .= "<td".(($tmp['TGL_STPD']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['SPPT_TAHUN_PAJAK'] . "</td> ";
            $HTML .= "<td".(($tmp['TGL_STPD']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['NOP'] . "</td> ";
            $HTML .= "<td class=tleft".(($tmp['TGL_STPD']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['WP_NAMA'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['WP_ALAMAT'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['WP_KELURAHAN'] . "</td> ";
            
            $HTML .= "<td".(($tmp['TGL_SP2']==null && $tmp['TGL_SP1']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['TGL_SP1'] . "</td> ";
            $HTML .= "<td".(($tmp['TGL_SP3']==null && $tmp['TGL_SP2']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['TGL_SP2'] . "</td> ";
            $HTML .= "<td".(($tmp['TGL_STPD']!=null && $tmp['TGL_SP3']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['TGL_SP3'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['KETERANGAN_SP'] . "</td> ";
            $HTML .= "<td".(($tmp['TGL_STPD']!=null)?' style="background:#ffe3005c !important"':'').">" . $tmp['TGL_STPD'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['KETERANGAN_STPD'] . "</td> ";

            $HTML .= "<td class=tleft>" . $tmp['OP_ALAMAT'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['OP_KECAMATAN'] . "</td> ";
            $HTML .= "<td class=tleft>" . $tmp['OP_KELURAHAN'] . "</td> ";
            $HTML .= "<td>" . $tmp['OP_RT'] . "</td> ";
            $HTML .= "<td class=tcenter>" . $tmp['OP_RW'] . "</td> ";
            $HTML .= "<td class=tcenter>" . $tmp['OP_LUAS_BUMI'] . "</td> ";
            $HTML .= "<td class=tcenter>" . $tmp['OP_LUAS_BANGUNAN'] . "</td> ";
            $HTML .= "<td class=tright>" . number_format($tmp['OP_NJOP_BUMI'], 0, ',', '.') . "</td> ";
            $HTML .= "<td class=tright>" . number_format($tmp['OP_NJOP_BANGUNAN'], 0, ',', '.') . "</td> ";
            $HTML .= "<td class=tcenter>" . ($tgltempo[2] . "-" . $tgltempo[1] . "-" . $tgltempo[0]) . "</td>";
            $HTML .= "<td class=tright>" . number_format($tmp['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td> ";

            if ($isAdminPenagihan == 1) {
                $linkdate = "linkdate";

                if ($tmp['TGL_SP1'] == "")
                    $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP1\">Input</td> ";
                else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP1\">" . substr($tmp['TGL_SP1'], 8, 2) . "-" . substr($tmp['TGL_SP1'], 5, 2) . "-" . substr($tmp['TGL_SP1'], 0, 4) . "</td> ";
                if ($tmp['TGL_SP2'] == "")
                    $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP2\">Input</td> ";
                else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP2\">" . substr($tmp['TGL_SP2'], 8, 2) . "-" . substr($tmp['TGL_SP2'], 5, 2) . "-" . substr($tmp['TGL_SP2'], 0, 4) . "</td> ";
                if ($tmp['TGL_SP3'] == "")
                    $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP3\">Input</td> ";
                else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP3\">" . substr($tmp['TGL_SP3'], 8, 2) . "-" . substr($tmp['TGL_SP3'], 5, 2) . "-" . substr($tmp['TGL_SP3'], 0, 4) . "</td> \n";

                $linkdate = "linkketerangan";
                if ($tmp['KETERANGAN_SP'] == "")
                    $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP\">Input</td> \n";
                else $HTML .= "<td class=\"$linkdate\" id=\"" . $tmp['NOP'] . "+" . $tmp['SPPT_TAHUN_PAJAK'] . "+SP\">" . $tmp['KETERANGAN_SP'] . "</td> \n";
            }
            $HTML .= "</tr>";

            $i++;
        }
        $dat = $HTML;
        return true;
    }

    public function getKecamatan()
    {
        global $DBLink, $kodekota;

        $kecamatan = array();

        $query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID='$kodekota' ORDER BY CPC_TKC_ID";
        $buffer = mysqli_query($DBLink, $query);
        if (mysqli_num_rows($buffer) > 0) {
            while ($kec = mysqli_fetch_assoc($buffer)) {
                $digit3 = substr($kec["CPC_TKC_ID"], 4, 3) . " - ";
                $tmp = array(
                    "id" => $kec["CPC_TKC_ID"],
                    "nama" => $digit3 . $kec["CPC_TKC_KECAMATAN"]
                );
                $kecamatan[] = $tmp;
            }
        }
        return $kecamatan;
    }

    public function getKelurahan($idkec)
    {
        global $DBLink;

        $kelurahan = array();

        $query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID='$idkec' ORDER BY CPC_TKL_ID";

        $buffer = mysqli_query($DBLink, $query);
        if (mysqli_num_rows($buffer) > 0) {
            while ($kel = mysqli_fetch_assoc($buffer)) {
                $digit3 = substr($kel["CPC_TKL_ID"], 7, 3) . " - ";

                $tmp = array(
                    "id" => $kel['CPC_TKL_ID'],
                    "nama" => $digit3 . $kel['CPC_TKL_KELURAHAN']
                );
                $kelurahan[] = $tmp;
            }
        }
        return $kelurahan;
    }

    public function headerContent($sts)
    {
        global $condition, $isAdminPenagihan, $isViewData, $lblkel, $con4;

        $slcTagihan = array();
        if (!isset($condition[1])) $condition[1] = 0;
        for ($ctr = 0; $ctr <= 8; $ctr++) {
            $slcTagihan[$ctr] = ($condition[1] == $ctr) ? "selected" : "";
        }

        $optKec = '<option value="">--semua--</option>';
        $kec = $this->getKecamatan();
        for ($ctr = 0; $ctr < count($kec); $ctr++) {
            $selected = "";
            if (isset($condition[2]) && $condition[2] == $kec[$ctr]["id"]) $selected = "selected";
            $optKec .= "<option value=\"" . $kec[$ctr]["id"] . "\" $selected>" . strtoupper($kec[$ctr]["nama"]) . "</option>";
        }



        $optKel = "";
        if (isset($condition[3]) && $condition[3] != "") {
            $kel = $this->getKelurahan($condition[2]);
            for ($ctr = 0; $ctr < count($kel); $ctr++) {
                $selected = "";
                if ($condition[3] == $kel[$ctr]["id"]) $selected = "selected";
                $optKel .= "<option value=\"" . $kel[$ctr]["id"] . "\" $selected>" . strtoupper($kel[$ctr]["nama"]) . "</option>";
            }
        } else {
            $optKel = "<option value=\"\">--semua--</option>";
        }

        $cond4 = null;
        if (isset($condition[4])) {
            $cond4 = $condition[4];
        }

        $cond51 = null;
        $cond52 = null;
        $cond53 = null;
        $cond54 = null;
        $cond55 = null;
        $cond56 = null;
        $cond57 = null;
        if (isset($condition[5])) {
            $cond51 = $condition[5];
        }

        if (isset($condition[6])) {
            $cond52 = $condition[6];
        }

        if (isset($condition[7])) {
            $cond53 = $condition[7];
        }

        if (isset($condition[8])) {
            $cond54 = $condition[8];
        }

        if (isset($condition[9])) {
            $cond55 = $condition[9];
        }

        if (isset($condition[10])) {
            $cond56 = $condition[10];
        }

        if (isset($condition[11])) {
            $cond57 = $condition[11];
        }
        $opt_tahun = '<option value="">All</option>';
        for ($th = date("Y") - 10; $th <= date("Y"); $th++) {
            $opt_tahun .= ("<option value='{$th}' " . ($cond4 == $th ? 'selected' : '') . " >{$th}</option>");
        }
        $HTML = "
        <style>
        .form-filtering-penetapan {
            background-color: #fff;
            margin:  20px;
            padding: 20px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

        }
    </style>
        <form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML = "<p style=\"margin-bottom:20px;margin-top:10px; display:flex; align-items:center;justify-content:end;\">
        <button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseMutasi\" aria-expanded=\"false\" aria-controls=\"collapseMutasi\">
       Filter Data
        </button>
       </p>

       <div class=\"collapse\" id=\"collapseMutasi\">
            <div class=\"card card-body form-filtering-penetapan\">
                <div class=\"row\">

                    <div class=\"form-group col-md-3\">
                        <label>Nama WP </label>
                        <input type=\"text\" class=\"form-control\"  onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" value=\"{$condition[0]}\"/>
                    </div>
                    
                    <div class=\"form-group col-md-3\">
                        <label>Kecamatan </label>
                        <select class=\"form-control\" id=\"src-kecamatan-{$sts}\" name=\"src-kecamatan-{$sts}\">$optKec</select>
                    </div>
                    
                    <div class=\"form-group col-md-3\">
                        <label>" . $lblkel . ": </label>
                        <select class=\"form-control\" id=\"src-kelurahan-{$sts}\" name=\"src-kelurahan-{$sts}\">$optKel</select>
                    </div>
                    
                    <div class=\"form-group col-md-3\">
                        <label> Daftar Tagihan </label>
                        <select class=\"form-control\" id=\"src-tagihan-{$sts}\" name=\"src-tagihan-{$sts}\">
                            <option selected value=\"0\" " . $slcTagihan[0] . ">--semua--</option>
                            <option value=\"1\" " . $slcTagihan[1] . ">0 s/d <5jt</option>
                            <option value=\"2\" " . $slcTagihan[2] . ">5jt s/d <10jt</option>
                            <option value=\"3\" " . $slcTagihan[3] . ">10jt s/d <20jt</option>
                            <option value=\"4\" " . $slcTagihan[4] . ">20jt s/d <30jt</option>
                            <option value=\"5\" " . $slcTagihan[5] . ">30jt s/d <40jt</option>
                            <option value=\"6\" " . $slcTagihan[6] . ">40jt s/d <50jt</option>
                            <option value=\"7\" " . $slcTagihan[7] . ">50jt s/d <100jt</option>
                            <option value=\"8\" " . $slcTagihan[8] . ">>=100jt</option>
                        </select>
                    </div>
                        
                        
                    <div class=\"form-group col-md-3\">
                        <label>Tahun: </label>
                        <select class=\"form-control\" id=\"src-tahun-{$sts}\" name=\"src-tahun-{$sts}\">
                            " . $opt_tahun . " 
                        </select>
                    </div>

                    <div class=\"form-group col-md-6\">
                        <label>NOP: </label><br />
                                <div class=\"col-md-1\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-1\" name=\"src-nop-{$sts}-1\" size=\"20\" maxlength=\"18\" value=\"{$cond51}\" class=\"form-control nop-input-1\" style=\"padding: 6px;\" placeholder=\"PR\">
                                </div>
                                <div class=\"col-md-1\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-2\" name=\"src-nop-{$sts}-2\" size=\"20\" maxlength=\"18\" value=\"{$cond52}\" class=\"form-control nop-input-2\" maxlength=\"2\" style=\"padding: 6px;\" placeholder=\"DTII\">
                                </div>
                                <div class=\"col-md-2\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-3\" name=\"src-nop-{$sts}-3\" size=\"20\" maxlength=\"18\" value=\"{$cond53}\" class=\"form-control nop-input-3\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"KEC\">
                                </div>
                                <div class=\"col-md-2\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-4\" name=\"src-nop-{$sts}-4\" size=\"20\" maxlength=\"18\" value=\"{$cond54}\" class=\"form-control nop-input-4\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"KEL\">
                                </div>
                                <div class=\"col-md-2\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-5\" name=\"src-nop-{$sts}-5\" size=\"20\" maxlength=\"18\" value=\"{$cond55}\" class=\"form-control nop-input-5\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"BLOK\">
                                </div>
                                <div class=\"col-md-2\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-6\" name=\"src-nop-{$sts}-6\" size=\"20\" maxlength=\"18\" value=\"{$cond56}\" class=\"form-control nop-input-6\" maxlength=\"4\" style=\"padding: 6px;\" placeholder=\"NO.URUT\">
                                </div>
                                <div class=\"col-md-2\" style=\"padding: 0\">
                                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-7\" name=\"src-nop-{$sts}-7\" size=\"20\" maxlength=\"18\" value=\"{$cond57}\" class=\"form-control nop-input-7\" maxlength=\"1\" style=\"padding: 6px;\" placeholder=\"KODE\">
                                </div>
                    </div>

                    <div class=\" form-group col-md-12\">                          
                        <input type=\"button\" class=\"btn btn-info \" style=\"width:116px\" value=\"Cari\" id=\"btn-src-{$sts}\" name=\"btn-src-{$sts}\" onclick=\"btnCari($sts);\" />
                        <input type=\"button\" class=\"btn btn-primary btn-blue\" value=\"Tampilkan Semua\" id=\"btn-clr-{$sts}\" name=\"btn-clr-{$sts}\" onclick=\"btnTampilSemua($sts);\" />
                    </div>

                </div>
            </div>
        </div>
   ";
        $HTML .= " <!-- <input type=\"button\" value=\"Cetak\" id=\"btn-print-{$sts}\" name=\"btn-print\" /> -->
                        &nbsp;<!-- Tahun --> 
                        <input type=\"hidden\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-tahu1n-{$sts}\" name=\"src-tahu1n-{$sts}\" size=\"5\" maxlength=\"4\" value=\"{$cond4}\"/>
                        
                    
                      
                        <script>
        $(\".nop-input-1\").on(\"keyup\", function(){
            var len = $(this).val().length;
            let nopLengkap = $(this).val();
            
            if(!$(\".nop-input-2\").val()) $(\".nop-input-2\").val(nopLengkap.substr(2, 2));
            if(!$(\".nop-input-3\").val()) $(\".nop-input-3\").val(nopLengkap.substr(4, 3));
            if(!$(\".nop-input-4\").val()) $(\".nop-input-4\").val(nopLengkap.substr(7, 3));
            if(!$(\".nop-input-5\").val()) $(\".nop-input-5\").val(nopLengkap.substr(10, 3));
            if(!$(\".nop-input-6\").val()) $(\".nop-input-6\").val(nopLengkap.substr(13, 4));
            if(!$(\".nop-input-7\").val()) $(\".nop-input-7\").val(nopLengkap.substr(17, 1));
            if(len > 2) $(this).val(nopLengkap.substr(0, 2));
            if(len == 2) {
                $(\".nop-input-2\").focus();
            }
        });

        $(\".nop-input-2\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 2) {
                $(\".nop-input-3\").focus();
            }
        });

        $(\".nop-input-3\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-4\").focus();
            }
        });

        $(\".nop-input-4\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-5\").focus();
            }
        });

        $(\".nop-input-5\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-6\").focus();
            }
        });

        $(\".nop-input-6\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 4) {
                $(\".nop-input-7\").focus();
            }
        });

        $(\".nop-input-7\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 1) {
                btnCari($sts);
            }
        });
        </script>
                 </form>";
        $HTML .= ($sts<=3) ? "<input type=\"button\" class=\"btn btn-success btn-green\" value=\"Proses SP-$sts\" onclick=\"proses($sts);\">" : '';
        $HTML .= "<div class=\"responsive\" style=\"overflow:scroll;margin-bottom:15px;padding-bottom:30px\">
                 <table class=\"table table-bordered\">";
        $HTML .= "<thead data-stt=$sts><tr>";
        $HTML .= ($sts<=3) ? "<th width=10><input type=\"checkbox\" id=\"select-all$sts\"/></th>" : '';
        $HTML .= "<th width=60>Tahun Pajak</th>";
        $HTML .= "<th>Nomor Objek Pajak</th> ";
        $HTML .= "<th>Nama WP</th> ";
        $HTML .= "<th>Alamat WP</th> ";
        $HTML .= "<th>" . $lblkel . " WP</th> ";
        $HTML .= "<th>Alamat OP</th>";
        $HTML .= "<th>Kecamatan OP</th>";
        $HTML .= "<th>Desa OP</th>";
        $HTML .= "<th width=50>RT OP</th>";
        $HTML .= "<th width=50>RW OP</th>";
        $HTML .= "<th width=60>Luas Bumi</th>";
        $HTML .= "<th width=60>Luas Bangunan</th>";
        $HTML .= "<th width=120>Tot NJOP Bumi</th>";
        $HTML .= "<th width=120>Tot NJOP Bangunan</th>";

        $HTML .= "<th width=100>Tanggal Jatuh Tempo</th>";
        $HTML .= "<th width=100>Tagihan</th>";

        // $HTML .= "<th width=100>Status Cetak</th>";

        // if($isAdminPenagihan == 1){
        if ($sts == 1 || $sts == 2 || $sts == 3) {
            // $HTML .= "<th width=100>Tgl Diterima SP</th>";
        } else if ($sts == 5) {
            $HTML .= "<th width=100>Tgl Diterima STPD</th>";
        } else if ($sts == 9) {
            $HTML .= "<th width=100>Jenis SP</th>";
        } else {
            $HTML .= "<th width=100>Status SP</th>";
            $HTML .= "<th width=100>Keterangan</th>";
        }
        if ($sts == 5) $HTML .= "<th width=300>Keterangan</th>";
        if ($sts == 9) $HTML .= "<th width=100>Disetujui</th>";
        // }
        $HTML .= "</tr></thead>";

        if (!$isViewData) {
            if ($sts == 5) $HTML .= "<tr><td colspan=18><div id=loading style=\"text-align:center;padding:30px\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</div></td></tr> ";
            else $HTML .= "<tr><td colspan=17><div id=loading style=\"text-align:center;padding:30px\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</div></td></tr> ";
        } else if ($this->getDocument($sts, $dt)) {
            if ($sts == 5) $HTML .= "<tr><td colspan=18><div id=loading style=\"text-align:center\"></div></td></tr> ";
            else $HTML .= "<tr><td colspan=17><div id=loading style=\"text-align:center\"></div></td></tr> ";
            $HTML .= $dt;
        } else {
            if ($sts == 5) $HTML .= "<tr><td colspan=18><div id=loading style=\"text-align:center;padding:30px\">Data Kosong !<br>tidak ditemukan".(($sts<=3)?'<br>atau sudah masuk proses':'')."</div></td></tr>";
            else $HTML .= "<tr><td colspan=17><div id=loading style=\"text-align:center;padding:30px\">Data Kosong !<br>tidak ditemukan".(($sts<=3)?'<br>atau sudah masuk proses':'')."</div></td></tr>";
        }

        $HTML .= "</table></div>";
        return $HTML;
    }

    public function headerContentAllSP($sts)
    {
        global $condition, $isAdminPenagihan, $isViewData, $lblkel;


        $slcTagihan = array();
        for ($ctr = 0; $ctr <= 8; $ctr++) {
            $slcTagihan[$ctr] = (isset($condition[1]) && $condition[1] == $ctr) ? "selected" : "";
        }

        $optKec = "<option value=\"\">--semua--</option>";
        $kec = $this->getKecamatan();

        for ($ctr = 0; $ctr < count($kec); $ctr++) {
            $selected = "";
            if (isset($condition[2]) && $condition[2] == $kec[$ctr]["id"]) $selected = "selected";
            $optKec .= "<option value=\"" . $kec[$ctr]["id"] . "\" $selected >" . ucfirst(strtolower($kec[$ctr]["nama"])) . "</option>";
        }

        $optKel = "";
        if (isset($condition[3]) && $condition[3] != "") {
            $kel = $this->getKelurahan($condition[2]);
            for ($ctr = 0; $ctr < count($kel); $ctr++) {
                $selected = "";
                if ($condition[3] == $kel[$ctr]["id"]) $selected = "selected";
                $optKel .= "<option value=\"" . $kel[$ctr]["id"] . "\" $selected >" . ucfirst(strtolower($kel[$ctr]["nama"])) . "</option>";
            }
        } else {
            $optKel = "<option value=\"\">--semua--</option>";
        }

        $cond4 = null;
        $cond51 = null;
        $cond52 = null;
        $cond53 = null;
        $cond54 = null;
        $cond55 = null;
        $cond56 = null;
        $cond57 = null;

        if (isset($condition[4])) {
            $cond4 = $condition[4];
        }

        if (isset($condition[5])) {
            $cond51 = $condition[5];
        }

        if (isset($condition[6])) {
            $cond52 = $condition[6];
        }

        if (isset($condition[7])) {
            $cond53 = $condition[7];
        }

        if (isset($condition[8])) {
            $cond54 = $condition[8];
        }

        if (isset($condition[9])) {
            $cond55 = $condition[9];
        }

        if (isset($condition[10])) {
            $cond56 = $condition[10];
        }

        if (isset($condition[11])) {
            $cond57 = $condition[11];
        }

        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<div class=\"row\">
                    <div class=\"col-md-1\" style=\"margin-top: 25px;\">
                        <input type=\"button\" class=\"btn btn-primary btn-orange mb5\" value=\"Cetak\" id=\"btn-print-{$sts}\" name=\"btn-print\" />
                    </div>
                    <div class=\"col-md-2\">
                        <div class=\"form-group\">
                            <label>Tahun</label>
                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-tahu1n-{$sts}\" name=\"src-tahu1n-{$sts}\" size=\"5\" maxlength=\"4\" value=\"{$cond4}\"/>
                        </div>
                    </div>
                    <div class=\"col-md-5\">
                        <div class=\"form-group\">
                            <label>NOP</label><br />
                            <div class=\"col-md-1\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-1\" name=\"src-nop-{$sts}-1\" size=\"20\" maxlength=\"18\" value=\"{$cond51}\" class=\"form-control nop-inputs-1\" maxlength=\"2\" style=\"padding: 6px;\" placeholder=\"PR\">
                            </div>
                            <div class=\"col-md-1\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-2\" name=\"src-nop-{$sts}-2\" size=\"20\" maxlength=\"18\" value=\"{$cond52}\" class=\"form-control nop-inputs-2\" maxlength=\"2\" style=\"padding: 6px;\" placeholder=\"DTII\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-3\" name=\"src-nop-{$sts}-3\" size=\"20\" maxlength=\"18\" value=\"{$cond53}\" class=\"form-control nop-inputs-3\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"KEC\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-4\" name=\"src-nop-{$sts}-4\" size=\"20\" maxlength=\"18\" value=\"{$cond54}\" class=\"form-control nop-inputs-4\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"KEL\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-5\" name=\"src-nop-{$sts}-5\" size=\"20\" maxlength=\"18\" value=\"{$cond55}\" class=\"form-control nop-inputs-5\" maxlength=\"3\" style=\"padding: 6px;\" placeholder=\"BLOK\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-6\" name=\"src-nop-{$sts}-6\" size=\"20\" maxlength=\"18\" value=\"{$cond56}\" class=\"form-control nop-inputs-6\" maxlength=\"4\" style=\"padding: 6px;\" placeholder=\"NO.URUT\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-nop-{$sts}-7\" name=\"src-nop-{$sts}-7\" size=\"20\" maxlength=\"18\" value=\"{$cond57}\" class=\"form-control nop-inputs-7\" maxlength=\"1\" style=\"padding: 6px;\" placeholder=\"KODE\">
                            </div>
                        </div>
                    </div>
                    <div class=\"col-md-2\">
                        <div class=\"form-group\">
                            <label>Nama WP</label>
                            <input class=\"form-control\" type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (" . $sts . ");\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"30\" value=\"{$condition[0]}\"/>
                        </div>
                    </div>
                    <div class=\"col-md-2\">
                        <div class=\"form-group\">
                            <label>Daftar Tagihan</label>
                            <select class=\"form-control\" id=\"src-tagihan-{$sts}\" name=\"src-tagihan-{$sts}\">
                                <option selected value=\"0\" " . $slcTagihan[0] . ">--semua--123</option>
                                <option value=\"1\" " . $slcTagihan[1] . ">0 s/d <5jt</option>
                                <option value=\"2\" " . $slcTagihan[2] . ">5jt s/d <10jt</option>
                                <option value=\"3\" " . $slcTagihan[3] . ">10jt s/d <20jt</option>
                                <option value=\"4\" " . $slcTagihan[4] . ">20jt s/d <30jt</option>
                                <option value=\"5\" " . $slcTagihan[5] . ">30jt s/d <40jt</option>
                                <option value=\"6\" " . $slcTagihan[6] . ">40jt s/d <50jt</option>
                                <option value=\"7\" " . $slcTagihan[7] . ">50jt s/d <100jt</option>
                                <option value=\"8\" " . $slcTagihan[8] . ">>=100jt</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-3\">
                        <div class=\"form-group\">
                            <label>Kecamatan</label>
                            <select class=\"form-control\" id=\"src-kecamatan-{$sts}\" name=\"src-kecamatan-{$sts}\">$optKec</select>
                        </div>
                    </div>
                    <div class=\"col-md-3\">
                        <div class=\"form-group\">
                            <label>" . $lblkel . "</label>
                            <select class=\"form-control\" id=\"src-kelurahan-{$sts}\" name=\"src-kelurahan-{$sts}\">$optKel</select>
                        </div>
                    </div>
                    <div class=\"col-md-6\" style=\"margin-top:25px;text-align:right\">
                        <input type=\"button\" class=\"btn btn-primary btn-blue\" style=\"width:116px\" value=\"Cari\" id=\"btn-src-{$sts}\" name=\"btn-src-{$sts}\" onclick=\"btnCari($sts);\" />
                        <input type=\"button\" class=\"btn btn-primary btn-green\" value=\"Tampilkan Semua\" id=\"btn-clr-{$sts}\" name=\"btn-clr-{$sts}\" onclick=\"btnTampilSemua($sts);\" />\n
                    </div>
                </div>
                <script>
                $(\".nop-inputs-1\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 2) {
                        $(\".nop-inputs-2\").focus();
                    }
                });
        
                $(\".nop-inputs-2\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 2) {
                        $(\".nop-inputs-3\").focus();
                    }
                });
        
                $(\".nop-inputs-3\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 3) {
                        $(\".nop-inputs-4\").focus();
                    }
                });
        
                $(\".nop-inputs-4\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 3) {
                        $(\".nop-inputs-5\").focus();
                    }
                });
        
                $(\".nop-inputs-5\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 3) {
                        $(\".nop-inputs-6\").focus();
                    }
                });
        
                $(\".nop-inputs-6\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 4) {
                        $(\".nop-inputs-7\").focus();
                    }
                });
        
                $(\".nop-inputs-7\").on(\"keyup\", function(){
                    var len = $(this).val().length;
        
                    if(len == 1) {
                        btnCari($sts);
                    }
                });
                </script>
            </form>";
        $HTML .= "<div class=responsive style=\"overflow:scroll;margin-bottom:15px;padding-bottom:30px\">
                <table class=\"table table-bordered\">";
        $HTML .= "<tr>";
        $HTML .= "<th width=60>Tahun Pajak</th>";
        $HTML .= "<th>Nomor Objek Pajak </th> ";
        $HTML .= "<th>Nama WP </th> ";
        $HTML .= "<th>Alamat WP</th> ";
        $HTML .= "<th>" . $lblkel . " WP</th> ";

        $HTML .= "<th>TGL SP1</th>";
        $HTML .= "<th>TGL SP2</th>";
        $HTML .= "<th>TGL SP3</th>";
        $HTML .= "<th>KETERANGAN SP</th>";
        $HTML .= "<th>TGL STPD</th>";
        $HTML .= "<th>KETERANGAN STPD</th>";

        $HTML .= "<th>Alamat OP</th>";
        $HTML .= "<th>Kecamatan OP</th>";
        $HTML .= "<th>Desa OP</th>";
        $HTML .= "<th width=50>RT OP</th>";
        $HTML .= "<th width=50>RW OP</th>";
        $HTML .= "<th width=60>Luas Bumi</th>";
        $HTML .= "<th width=60>Luas Bangunan</th>";
        $HTML .= "<th width=120>Tot NJOP Bumi</th>";
        $HTML .= "<th width=120>Tot NJOP Bangunan</th>";
        $HTML .= "<th width=100>Tanggal Jatuh Tempo</th>";
        $HTML .= "<th width=100>Tagihan</th>";

        if ($isAdminPenagihan == 1) {
            $HTML .= "<th width=100>Tgl Diterima SP1</th>";
            $HTML .= "<th width=100>Tgl Diterima SP2</th>";
            $HTML .= "<th width=100>Tgl Diterima SP3</th>";
            $HTML .= "<th width=300>Keterangan</th>";
        }
        $HTML .= "</tr>";

        if (!$isViewData) {
            $HTML .= "<tr><td colspan=20><div id=loading style=\"text-align:center;padding:30px\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</div></td></tr> ";
        } else if ($this->getDocumentAllSP($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=20><div id=loading style=\"text-align:center;padding:30px\">Data Kosong !<br>tidak ditemukan</div></td></tr> ";
        }

        $HTML .= "</table></div>";
        return $HTML;
    }

    function getContent()
    {
        $HTML = "";
        if ($this->status == '4')
            $HTML .= $this->headerContentAllSP($this->status);
        else $HTML .= $this->headerContent($this->status);
        return $HTML;
    }


    public function displayDataNotaris()
    {
        echo "<div class=\"ui-widget consol-main-content\">\n";
        echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
        echo $this->getContent();
        echo "\t</div>\n";
        echo "\t<div class=\"ui-widget-header consol-main-content-footer\" class=tright style=\"height:30px\">  \n";
        echo $this->paging();
        echo "</div>\n";
    }

    function paging()
    {
        global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage;

        $params = "a=" . $a . "&m=" . $m;

        $html = "<div>";
        $row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $rowlast = (($page) * $perpage) < $this->totalRows ? ($page) * $perpage : $this->totalRows;
        $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $this->totalRows;

        if ($page != 1) {
            //$page--;
            $html .= "&nbsp;<a onclick=\"setPage(" . $s . ",'0')\"><span id=\"navigator-left\"></span></a>";
        }
        if ($rowlast < $this->totalRows) {
            //$page++;
            $html .= "&nbsp;<a onclick=\"setPage(" . $s . ",'1')\"><span id=\"navigator-right\"></span></a>";
        }
        $html .= "</div>";
        return $html;
    }

    // function paging() {
    // global $a,$m,$n,$s,$page,$np;

    // $params = "a=".$a."&m=".$m;
    // $sel = $n;
    // $sts = $s;

    // $html = "<div>";
    // $row = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;
    // $rowlast = (($page) * $this->perpage) < $this->totalRows ? ($page) * $this->perpage : $this->totalRows;
    // $html .= ($row+1)." - ".($rowlast). " dari ".$this->totalRows;

    // $parl = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage-1);
    // $paramsl = base64_encode($parl);

    // $parr = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage+1);
    // $paramsr = base64_encode($parr);

    // if ($page != 1) {
    // //$page--;
    // $html .= "&nbsp;<a onclick=\"setPage('".$s."','0')\"><span id=\"navigator-left\"></span></a>";
    // }
    // if ($rowlast < $this->totalRows ) {
    // //$page++;
    // $html .= "&nbsp;<a onclick=\"setPage('".$s."','1')\"><span id=\"navigator-right\"></span></a>";
    // }
    // $html .= "</div>";
    // return $html;
    // }

    function updateStatus($status)
    {
        global $DBLinkLookUp, $sp1, $sp2, $sp3;

        $statusSP = "";
        switch ($status) {
            case 1:
                $qrangetime = " AND DATEDIFF(CURDATE(), DATE(SPPT_TANGGAL_JATUH_TEMPO)) >= $sp1 AND (TGL_SP1 = '' OR TGL_SP1 IS NULL) ";
                $statusSP = "SP1";
                //echo "<script language=\"javascript\">refreshNotif();</script>";
                break;
            case 2:
                $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP1)) >= $sp2 AND (TGL_SP2 = '' OR TGL_SP2 IS NULL) ";
                $statusSP = "SP2";
                //echo "<script language=\"javascript\">refreshNotif();</script>";
                break;
            case 3:
                $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP2)) >= $sp3 AND (TGL_SP3 = '' OR TGL_SP3 IS NULL) ";
                $statusSP = "SP3";
                //echo "<script language=\"javascript\">refreshNotif();</script>";
                break;
        }
        $query = "SELECT * FROM PBB_SPPT WHERE (PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK >= '2007' $qrangetime";
        $buffer = mysqli_query($DBLinkLookUp, $query);

        if (mysqli_num_rows($buffer) > 0) {
            while ($tmp = mysqli_fetch_assoc($buffer)) {
                //update status sp
                $sqlupdate = "UPDATE PBB_SPPT SET STATUS_SP ='$statusSP' WHERE NOP='" . $tmp['NOP'] . "' AND SPPT_TAHUN_PAJAK='" . $tmp['SPPT_TAHUN_PAJAK'] . "'";
                $tmpupdate = mysqli_query($DBLinkLookUp, $sqlupdate);

                //update status cetak
                $sqlupdate = "UPDATE PBB_SPPT SET STATUS_CETAK = 'Belum Tercetak' WHERE NOP='" . $tmp['NOP'] . "' AND SPPT_TAHUN_PAJAK='" . $tmp['SPPT_TAHUN_PAJAK'] . "' AND STATUS_CETAK != 'Telah Tercetak'";
                $tmpupdate = mysqli_query($DBLinkLookUp, $sqlupdate);
            }
        }
    }
}


?>

<script type="text/javascript">
    var kepala = "<?php echo $kepala; ?>";
    var nip = "<?php echo $nip; ?>";
    var jabatan = "<?php echo $jabatan; ?>";
    var kota = "<?php echo $kota; ?>";
    var status = <?php echo $s; ?>;
    var appId = "<?php echo $a; ?>";
    var thn = "<?php echo $thn; ?>";

    // console.log(status);

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function btnCari(sts) {
        $("tbody").html('<tr><td colspan="17"><div style="text-align:center;padding:30px"><img src="image/icon/loadinfo.net.gif" /> Loading, please wait...</div></td></tr>');
        sleep(500).then(() => { setTabs(sts); });
    }

    function btnTampilSemua(sts) {
        $("tbody").html('<tr><td colspan="17"><div style="text-align:center;padding:30px"><img src="image/icon/loadinfo.net.gif" /> Loading, please wait...</div></td></tr>');
        sleep(500).then(() => { setTabs(sts, true); });
    }

    function proses(sts) {
        var ckv = []; 
        var ckElements = document.getElementsByClassName('ckc'+sts);
        for(var i=0; i<ckElements.length; ++i){
            if(ckElements[i].checked) ckv.push(ckElements[i].value);
        }
        var n = ckv.length;
        if(n>0){
            $("tbody").html('<tr><td colspan="17"><div style="text-align:center;padding:30px"><img src="image/icon/loadinfo.net.gif" /> Loading, please wait...</div></td></tr>');
            sleep(500).then(() => { 
                updateProsesSP(sts,ckv);
            });
        }else{
            alert("Pilih salah satu \natau beberapa \natau semua data terlebih dahulu");
        }
    }

    function updateProsesSP(sts,arr) {
        arr = arr.join(',')
        if(sts==1){
            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-sp1.php",
                data: "nop_array=" + arr + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        btnCari(sts);
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });
        }else{
            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-sp23.php",
                data: "nop_array=" + arr + '&sts=' + sts + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        btnCari(sts);
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });
        }
    }

    function printToPDF(nop, thnpajak, sp) {
        if (status == 6 || status == 7 || status == 8) {
            status = status - 5;
        } else if (status == 9) {
            status = sp;
        }

        var param = {
            nop:nop,
            thnpajak:thnpajak,
            SP:status,
            appId:appId
        }; param = btoa(JSON.stringify(param));

        if (status == '4')
            window.open('view/PBB/penagihan/svc-print-penagihan.php?q='+param, '_blank');
        if (status == '10')
            window.open('view/PBB/penagihan/svc-print-panggilan.php?nop=' + nop + '&thnpajak=' + thnpajak + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname + "&kepala=" + kepala + "&nip=" + nip + "&jabatan=" + jabatan + "&kota=" + kota + "&bank=" + bank + "&denda=" + denda + "&totalBulanPajak=" + totalBulanPajak + "&tipeKalkulasiPajak=" + tipeKalkulasiPajak + "&appId=<?php echo $a; ?>&SP=" + status, '_blank');
        else
            window.open('view/PBB/penagihan/svc-print-penagihan.php?q='+param, '_blank');
    }

    function printSTPDPBBToPDF(nop, thnpajak, nourut) {
        var param = {
            nop:nop,
            thnpajak:thnpajak,
            appId:appId
        }; param = btoa(JSON.stringify(param));
        window.open('view/PBB/penagihan/svc-stpdpbb-pdf.php?q='+param, '_blank');
    }

    function printSuratPernyataanToPDF(nop, thnpajak) {
        var param = {
            nop:nop,
            thnpajak:thnpajak,
            appId:appId
        }; param = btoa(JSON.stringify(param));
        window.open('view/PBB/penagihan/svc-suratpernyataan-pdf.php?q='+param, '_blank');
    }

    function setNoUrut() {
        $.ajax({
            type: "POST",
            url: "./view/PBB/penagihan/nourut.php",
            success: function(nourut) {}
        });
    }

    $(document).ready(function() {

        $("#select-all"+status).click(function() {
            if (this.checked) {
                $('.ckc'+status).each(function() {
                    this.checked = true;
                });
            } else {
                $('.ckc'+status).each(function() {
                    this.checked = false;
                });
            }
        });

        $(".btnApprove").click(function() {

            var wp = $(".btnApprove").val();
            var v_wp = wp.split("+");

            var nop = v_wp[0];
            var statusPersetujuan = v_wp[1];

            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-persetujuan.php",
                data: "nop=" + nop + '&statusPersetujuan=' + statusPersetujuan + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        console.log(data);
                        setTabs(status);
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });

        });

        $("#cetakSP1").unbind('click').click(function() {
            var nop = $('#nop_sp1').val();
            var listTahun = [];
            x = 0;
            $("input:checkbox[name='tahun[]']").each(function() {
                if ($(this).is(":checked")) {
                    listTahun.push($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }

            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-sp1.php",
                data: "nop=" + nop + '&listTahun=' + listTahun + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        console.log(data);
                        printToPDF(nop, listTahun);
                        $("#setyear2").css("display", "none");
                        $("#setyear1").css("display", "none");
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });
        });

        $(".linkto").click(function() {

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            var nop = v_wp[0];

            $("#nop_sp1").attr("value", nop);

            //            $.ajax({
            //               type: "POST",
            //               url: "./view/PBB/penagihan/svc-get-tahun.php",
            //               data: "nop="+nop+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
            //			   dataType : "json", 
            //               success: function(data){
            //					$("#setyear1").css("display","block");
            //					$("#setyear2").css("display","block");
            //					console.log(data);
            //					var jml = (data.tahunbelumSP1).length;
            //					var i   = 0;
            //					var cBoxTahun = "";
            //					var checkAll  = "";
            //					if(jml>0){
            //						while(i < jml){
            //							cBoxTahun += "<input type='checkbox' name='tahun[]' id='tahun' value='"+data.tahunbelumSP1[i]+"'>"+data.tahunbelumSP1[i]+"<br>";	
            //							i++;
            //						}
            //						document.getElementById("year").innerHTML = checkAll + cBoxTahun ;
            //					}else {
            //						label = "<label><center>Semua tahun pajak sudah dicetak SP1</center></label>";
            //						document.getElementById("year").innerHTML = label;
            //						console.log(data);
            //					}
            //               }
            //			   
            //             });

            // var wp = $(this).attr("id");
            // var v_wp = wp.split("+");

            // printToPDF(v_wp[0],v_wp[1]);
            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-sp1.php",
                data: "nop=" + nop + '&listTahun=ALL&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname + '&thn=' + thn,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        printToPDF(nop, '');
                        //                                            $("#setyear2").css("display","none");
                        //                                            $("#setyear1").css("display","none");
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });
        });

        $(".linkcetakpsp" + status).click(function() {
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            printToPDF(v_wp[0], v_wp[1], v_wp[2]);
        });

        $(".linkcetakup" + status).click(function() {

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            var nop = v_wp[0];
            var listTahun = v_wp[1];
            var sts = status;

            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-update-sp23.php",
                data: "nop=" + nop + '&listTahun=' + listTahun + '&sts=' + sts + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                dataType: "json",
                success: function(data) {
                    if (data.respon = "true") {
                        console.log(data);
                        printToPDF(nop, listTahun);
                    } else {
                        alert('Terjadi kesalahan server');
                    }
                }
            });
        });

        $("#closedyear").click(function() {
            $("#setyear2").css("display", "none");
            $("#setyear1").css("display", "none");
        });

        $(".linkketpsp1").click(function() {
            $("#setketSP1-1").css("display", "block");
            $("#setketSP1-2").css("display", "block");

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            var v_option = v_wp[3];

            $("#nop_fu").attr("value", v_wp[0]);
            $("#ketsp" + v_option).attr('checked', true);
            $("#keterangan").attr("value", v_wp[4]);

        });

        $("#closesetSP1").click(function() {
            $("#setketSP1-2").css("display", "none");
            $("#setketSP1-1").css("display", "none");
        });

        $("#simpanketSP1").unbind('click').click(function() {

            var statsp1 = $('input[name="ketsp1"]:checked').val();
            var keterangan = $('#keterangan').val();
            var nop = $("#nop_fu").val();
            var sts = status;
            var radioBtn = $('input[name=ketsp1]:checked').length;

            if (radioBtn <= 0) {
                label = "<label><font color='red'>Silahkan pilih keterangan </font></label>";
                document.getElementById("error1").innerHTML = label;
            } else
            if (keterangan == '') {
                label = "<label><font color='red'>Silahkan isi keterangan </font></label>";
                document.getElementById("error2").innerHTML = label;
            } else {
                $.ajax({
                    type: "POST",
                    url: "./view/PBB/penagihan/svc-update-stat-sp.php",
                    data: "nop=" + nop + "&sts=" + sts + "&keterangan=" + keterangan + "&statsp1=" + statsp1 + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                    dataType: "json",
                    success: function(data) {
                        console.log(data);
                        if (data.respon == true) {
                            alert('Penyimpanan data berhasil.');
                            $("#setketSP1-2").hide();
                            $("#setketSP1-1").hide();
                            setTabs(status);
                        } else alert('Penyimpanan data gagal.');
                    }
                });
            }
        });

        $(".linkstpd").bind("click", function() {
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            printSTPDPBBToPDF(v_wp[0], v_wp[1]);
            printSuratPernyataanToPDF(v_wp[0], v_wp[1]);
            setNoUrut();
        });


        $("#simpantanggal").unbind('click').click(function() {
            if ($("#tanggal").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "./view/PBB/penagihan/svc-update-datesp.php",
                    data: "nop=" + $("#nop_fu").val() + "&thnpajak=" + $("#thnpajak_fu").val() + "&tgl=" + $("#tanggal").val() + "&sp=" + $("#sp_fu").val() + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                    success: function(msg) {
                        //refreshNotif();
                        if (msg == '1') {
                            alert('Penyimpanan data berhasil.');
                            $("#contsetdate2").hide();
                            $("#contsetdate1").hide();
                            setTabs(status);
                        } else alert('Penyimpanan data gagal. Dengan error :' + msg);
                    }
                });
            } else {
                alert("Tanggal tidak boleh kosong, silahkan isi!");
            }
        });

        $(".linkdate").click(function() {
            $("#contsetdate1").css("display", "block");
            $("#contsetdate2").css("display", "block");

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            $("#nop_fu").attr("value", v_wp[0]);
            $("#thnpajak_fu").attr("value", v_wp[1]);
            if (status != 5)
                $("#sp_fu").attr("value", v_wp[2]);
            else
                $("#sp_fu").attr("value", "STPD");
        });
        $("#closeddate").click(function() {
            $("#contsetdate2").css("display", "none");
            $("#contsetdate1").css("display", "none");
        });
        $("#tanggal").datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $("#simpanketerangan").unbind('click').click(function() {
            //        $("#simpanketerangan").live("click",function(){
            if ($("#keterangan").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "./view/PBB/penagihan/svc-update-keterangan.php",
                    data: "nop=" + $("#nop_fuket").val() + "&thnpajak=" + $("#thnpajak_fuket").val() + "&keterangan=" + $("#keterangan").val() + "&sp=" + $("#sp_fuket").val() + '&dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname,
                    success: function(msg) {
                        //refreshNotif();
                        if (msg == '1') {
                            alert('Penyimpanan data berhasil.');
                            $("#contsetdate4").hide();
                            $("#contsetdate3").hide();
                            setTabs(status);
                        } else alert('Penyimpanan data gagal. Dengan error :' + msg);
                    }
                });
            } else {
                alert("Keterangan tidak boleh kosong, silahkan isi!");
            }
        });

        $(".linkketerangan").click(function() {
            $("#contsetdate3").css("display", "block");
            $("#contsetdate4").css("display", "block");

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            $("#nop_fuket").attr("value", v_wp[0]);
            $("#thnpajak_fuket").attr("value", v_wp[1]);
            if (status != 5)
                $("#sp_fuket").attr("value", v_wp[2]);
            else
                $("#sp_fuket").attr("value", "STPD");
        });
        $("#closedketerangan").click(function() {
            $("#contsetdate4").css("display", "none");
            $("#contsetdate3").css("display", "none");
        });

        $("#src-kecamatan-" + status).change(function() {
            $.ajax({
                type: "POST",
                url: "./view/PBB/penagihan/svc-get-kelurahan.php",
                data: "id=" + $(this).val(),
                success: function(msg) {
                    $("#src-kelurahan-" + status).html(msg);
                }
            });
        })

        $("#btn-print-" + status).click(function() {
            var tahun = $("#src-tahun-" + status).val();
            var nop = $("#src-nop-" + status).val();
            var kec = $("#src-kecamatan-" + status).val();
            var kel = $("#src-kelurahan-" + status).val();
            var nm = $("#src-approved-" + status).val();
            var tagihan = $("#src-tagihan-" + status).val();
            var sp1 = "<?php echo $sp1 ?>";
            var sp2 = "<?php echo $sp2 ?>";
            var sp3 = "<?php echo $sp3 ?>";
            var lblkel = "<?php echo $lblkel ?>";

            window.open('view/PBB/penagihan/svc-listpenagihan-excel.php?dbhost=' + dbhost + '&dbuser=' + dbuser + '&dbpwd=' + dbpwd + '&dbname=' + dbname + '&nm=' + nm + '&tagihan=' + tagihan + '&kec=' + kec + '&kel=' + kel + '&status=' + status + "&sp1=" + sp1 + "&sp2=" + sp2 + "&sp3=" + sp3 + "&tahun=" + tahun + "&nop=" + nop + "&lblkel=" + lblkel, '_blank');
        })
    });
</script>