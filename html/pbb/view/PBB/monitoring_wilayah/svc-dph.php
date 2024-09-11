<?php
$sRootPath  = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once("dbMonitoringDph.php");

date_default_timezone_set("Asia/Jakarta");

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

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$q          = @isset($_POST['q']) ? $_POST['q'] : "";
$p          = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;


if ($q=="") exit(1);
$q = base64_decode($q);
$j = $json->decode($q);

$uid        = $j->uid;
$area       = $j->a;
$moduleIds  = $j->m;

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig  = $User->GetAppConfig($area);


$dbname_sw  =  $appConfig['[ADMIN_GW_DBNAME'];

    $st = "devel";

    if ($st=="devel"){
        $dbname_sw = "GW_PBB_CIANJUR_66";
    }   

$host       = $_POST['GW_DBHOST'];
$port       = $_REQUEST['GW_DBPORT'];
$user       = $_REQUEST['GW_DBUSER'];
$pass       = $_REQUEST['GW_DBPWD'];
$dbname     = $_REQUEST['GW_DBNAME']; 
$method     = $_REQUEST['METHOD'];

$GWDBLink = mysqli_connect($host,$user,$pass,$dbname) or die(mysqli_error($DBLink));
//mysql_select_db($dbname,$GWDBLink);
define("PBB_DPH", "cppmod_pbb_dph");
define("PBB_DPH_DETAIL", "cppmod_pbb_dph_DETAIL");
define("TEMP", "cppmod_pbb_dph_TEMP");

function hapusDaftar ($no){
        global $GWDBLink;

        $status     = true ;

        $sql        =  "DELETE FROM ".PBB_DPH." WHERE NO_DPH = ".$no;
        $sql_detail =  "DELETE FROM ".PBB_DPH_DETAIL." WHERE NO_DPH = ".$no;

        $res_detail = mysqli_query($GWDBLink, $sql_detail);
        $res        = mysqli_query($GWDBLink, $sql);

        if ( $res === false  || $res_detail === false){
            $status = false;
        }else { 
            $status = true;
        }
        return $status; 
    }

    function tambahDetail ($noDph, $data){
        global $GWDBLink;

        $status     = true ;

        $sql        =  "DELETE FROM ".PBB_DPH." WHERE NO_DPH = ".$no;
        $sql_detail =  "DELETE FROM ".PBB_DPH_DETAIL." WHERE NO_DPH = ".$no;

        $res_detail = mysqli_query($GWDBLink, $sql_detail);
        $res        = mysqli_query($GWDBLink, $sql);

        if ( $res === false  || $res_detail === false){
            $status = false;
        }else { 
            $status = true;
        }
        return $status; 
    }
    function cekNop ($DPH ,$NOP, $TAHUN ){
        global $GWDBLink;
        $status2 ="false" ;

        $sql        = "SELECT A.NOP FROM ".PBB_DPH_DETAIL." A WHERE A.NOP=".$NOP." AND A.NO_DPH=".$DPH." AND A.TAHUN=".$TAHUN 
                        ."UNION ALL SELECT B.NOP FROM ".TEMP." B WHERE B.NOP=".$NOP." AND B.NO_DPH=".$DPH." AND B.TAHUN=".$TAHUN;
        $result     = mysqli_query($GWDBLink, $sql);
        $row        = mysqli_num_rows($result);
        if($row==0){
           $status2="true";
        }else{
            $status2="false";
        }
        // echo $row;
        // echo "--------";
        // echo $status2;
        // echo "%--------";
        return $status2;
    }

    if(stillInSession($DBLink,$json,$sdata)){ 

        switch ($method) {
            case "HAPUS":
            $noDph      = $_REQUEST['NO_DPH'];      
        
            echo  hapusDaftar($noDph);

            break;
            case "TAMBAH_TEMP":
            $noDph      = $_REQUEST['NO_DPH'];
            $data;
            echo  tambahDetail($noDph,$data);
            break;

            case "CEK" : 
            $noDph      = $_REQUEST['NO_DPH'];
            $nop        = $_REQUEST['NOP'];
            $tahun      = $_REQUEST['TAHUN'];

            echo cekNop($noDph,$nop,$tahun);
            break;
            default:
                    echo "Tidak ada method terpilih";
        }

    }else{
             echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
    }

?>
