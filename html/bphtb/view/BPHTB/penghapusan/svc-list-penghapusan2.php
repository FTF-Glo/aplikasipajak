<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'penghapusan', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

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

class BPHTBService extends modBPHTBApprover
{

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    #-

    // function getAUTHOR($nop)
    // {
    //     global $data, $DBLink;

    //     $query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '" . $nop . "'";

    //     $res = mysqli_query($DBLink, $query);
    //     if ($res === false) {
    //         return "Tidak Ditemukan";
    //     }
    //     $json = new Services_JSON();
    //     $data = $json->decode($this->mysql2json($res, "data"));
    //     for ($i = 0; $i < count($data->data); $i++) {
    //         return $data->data[$i]->CPM_SSB_AUTHOR;
    //     }
    //     return "Tidak Ditemukan";
    // }

    // function getNOP($author)
    // {
    //     global $data, $DBLink;

    //     $query = "SELECT CPM_OP_NOMOR 
    //               FROM cppmod_ssb_doc doc INNER JOIN cppmod_ssb_tranmain tran
    //               ON tran.CPM_TRAN_SSB_ID = doc.CPM_SSB_ID
    //               WHERE 
    //               doc.CPM_SSB_AUTHOR like '%" . $author . "%' and
    //               tran.CPM_TRAN_STATUS='5'";

    //     $res = mysqli_query($DBLink, $query);
    //     $arr = array();
    //     while ($d = mysqli_fetch_object($res)) {
    //         $arr[] = $d->CPM_OP_NOMOR;
    //     }
    //     return "'" . implode("','", $arr) . "'";
    // }

    function getConfigValue($id, $key)
    {
        global $DBLink;
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
    function select_option_kelurahan($value, $sts, $kel)
    {
        global $DBLink;
        $qry = "SELECT cppmod_tax_kelurahan2.* FROM cppmod_tax_kelurahan2
                JOIN cppmod_tax_kecamatan2 ON cppmod_tax_kecamatan2.CPC_TKC_ID = cppmod_tax_kelurahan2.CPC_TKL_KCID
                WHERE cppmod_tax_kecamatan2.CPC_TKC_KECAMATAN ='{$value}'";
        $res = mysqli_query($DBLink, $qry);

        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $return = "<select id=\"kelurahan2-{$sts}\" style=\"height:28px\">";
        $return .= "<option value=''>--Pilih Kelurahan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            $selected = '';

            $kel = strtoupper(str_replace(" ", "", $kel));
            $kelrhn = strtoupper(str_replace(" ", "", $row['CPC_TKL_KELURAHAN']));

            if ($kel == $kelrhn) {
                $selected = 'selected';
            }
            $return .= "<option value=\"{$row['CPC_TKL_KELURAHAN']}\" {$selected}>{$row['CPC_TKL_KELURAHAN']}</option>";
        }
        $return .= "</select>";
        return $return;
    }
    function select_option_kecamatan($value, $sts)
    {
        global $DBLink;
        $qry = "SELECT * FROM cppmod_tax_kecamatan2";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $return = "<select id=\"kecamatan2-{$sts}\" style=\"height:28px\" onchange=\"changekecmatan(this,$sts)\">";
        $return .= "<option value=''>--Pilih Kecamatan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            $selected = '';

            $value = strtoupper(str_replace(" ", "", $value));
            $kecmtn = strtoupper(str_replace(" ", "", $row['CPC_TKC_KECAMATAN']));

            if ($value == $kecmtn) {
                $selected = 'selected';
            }
            $return .= "<option value=\"{$row['CPC_TKC_KECAMATAN']}\" {$selected}>{$row['CPC_TKC_KECAMATAN']}</option>";
        }
        $return .= "</select>";
        return $return;
    }

    private $jml_transaksi = 0;
    private $total_transaksi = 0;
    private $jml_transaksi_select = 0;
    private $total_transaksi_select = 0;
    function getdatabelumbayar()
    {
    }

    //header table
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? trim($_REQUEST['find']) : "";
$find_notaris = @isset($_REQUEST['find_notaris']) ? trim($_REQUEST['find_notaris']) : "";
$nm_wp = @isset($_REQUEST['nm_wp']) ? trim($_REQUEST['nm_wp']) : "";
$kd_byar = @isset($_REQUEST['kd_byar']) ? trim($_REQUEST['kd_byar']) : "";
$jh = @isset($_REQUEST['jh']) ? trim($_REQUEST['jh']) : "";
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : "";
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : "";
$kec = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : "";

$kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$tagihan_awal = @isset($_REQUEST['tagihan_awal']) ? $_REQUEST['tagihan_awal'] : "";
$tagihan_akhir = @isset($_REQUEST['tagihan_akhir']) ? $_REQUEST['tagihan_akhir'] : "";

// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);
//var_dump($q);
//die;
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;


#echo $a."-".$m."-".$n."-".$s."-".$uname; #~
if (isset($_SESSION['stVerifikasi'])) {
    if ($_SESSION['stVerifikasi'] != $s) {
        $_SESSION['stVerifikasi'] = $s;
        $find = "";
        $find_notaris = "";
        $page = 1;
        $np = 1;
        $tgl1 = '';
        $tgl2 = '';
        $kec = '';
        $kel = '';
        $nm_wp = '';
        $kd_byar = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stVerifikasi'] = $s;
}

$modNotaris = new BPHTBService(1, $uname);
$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);

$modNotaris->displayDataMonitoring($s);
