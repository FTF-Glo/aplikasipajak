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

$q          = @isset($_POST['q'])       ? $_POST['q'] : "";
$p          = @isset($_REQUEST['p'])    ? $_REQUEST['p'] : 1;
$jml        = @isset($_REQUEST['j'])    ? $_REQUEST['j'] : 1;
$thn        = @isset($_REQUEST['th'])   ? $_REQUEST['th'] : 1;
$thn2       = @isset($_REQUEST['th2'])  ? $_REQUEST['th2'] : 1;
$nop        = @isset($_REQUEST['n'])    ? $_REQUEST['n'] : "";
$na         = @isset($_REQUEST['na'])   ? str_replace("%20"," ",$_REQUEST['na']) : "";
$status     = @isset($_REQUEST['st'])   ? $_REQUEST['st'] : "";
$tempo1     = @isset($_REQUEST['t1'])   ? $_REQUEST['t1'] : "";
$tempo2     = @isset($_REQUEST['t2'])   ? $_REQUEST['t2'] : "";
$kecamatan  = @isset($_REQUEST['kc'])   ? $_REQUEST['kc'] : "";
$kelurahan  = @isset($_REQUEST['kl'])   ? $_REQUEST['kl'] : "";
$export     = @isset($_REQUEST['exp'])  ? $_REQUEST['exp'] : "";

$tagihan    = @isset($_REQUEST['tagihan'])  ? $_REQUEST['tagihan'] : "0";
$bank       = @isset($_REQUEST['bank'])     ? $_REQUEST['bank'] : "0";
$barcode    = @isset($_REQUEST['barcode'])  ? $_REQUEST['barcode'] : "0";
$simpan     = @isset($_REQUEST['simpan'])   ? $_REQUEST['simpan'] : "0";
$lihat      = @isset($_REQUEST['lihat'])    ? $_REQUEST['lihat'] : "0";

if ($q=="") exit(1);
$q = base64_decode($q);
// echo $q;
// echo "<br>";
$j = $json->decode($q);
// $a = $q->a;
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
    // echo "sssss";
    // print_r($host);
    // exit;
// echo $host;
// echo "---";
// echo $pass;
// echo "*****";
// echo $dbname;

$GWDBLink = mysqli_connect($host,$user,$pass,$dbname) or die(mysqli_error($DBLink));
//mysql_select_db($dbname,$GWDBLink);
define("MyTable", "cppmod_pbb_dph");

if(stillInSession($DBLink,$json,$sdata)){ 

    if (!empty($_POST) ) {

        function getDataJson($sql){
       // print_r($sql);
        global $GWDBLink;
         $res = mysqli_query($GWDBLink, $sql);
        if ( $res === false ){
            echo "false";
        }else { 
            $json =array();
            $index =0;
            while ($row = mysqli_fetch_assoc($res)) {
             // print_r($row);
                $row["nomor"]=$index+1;
                $json[$index]= $row ;   
                $index++;
            }
            return $json;
        }
    }

    function getData($sql){
       // print_r($sql);
       
        global $GWDBLink;
       // global $connection ;//we use connection already opened
        // var_dump($GWDBLink);
        $query          = mysqli_query($GWDBLink, $sql) OR DIE ("Can't get Data from DB , check your SQL Query " );
        $data           = array();

        foreach ($query as $row ) {
            $data[] = $row ;
        }
        return $data;
    }

    /* Useful $_POST Variables coming from the plugin */
    $draw                   = $_POST["draw"];//counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
    $orderByColumnIndex     = $_POST['order'][0]['column'];// index of the sorting column (0 index based - i.e. 0 is the first record)
    $orderBy                = $_POST['columns'][$orderByColumnIndex]['data'];//Get name of the sorting column from its index
    $orderType              = $_POST['order'][0]['dir']; // ASC or DESC
    $start                  = $_POST["start"];//Paging first record indicator.
    $length                 = $_POST['length'];//Number of records that the table can display in the current draw
    /* END of POST variables */

    //$recordsTotal = count(getData("SELECT ID FROM ".MyTable));
    // echo "SELECT ID FROM ".MyTable." WHERE CREATED_BY=".$uid;
    $recordsTotal = count(getDataJson("SELECT ID FROM ".MyTable." WHERE CREATED_BY='".$uid."'"));
    /* SEARCH CASE : Filtered data */
    if(!empty($_POST['search']['value'])){

        /* WHERE Clause for searching */
        for($i=0 ; $i<count($_POST['columns']);$i++){
            $column     =   $_POST['columns'][$i]['data'];//we get the name of each column using its index from POST request
            $where[]    =   "$column like '%".$_POST['search']['value']."%'";
        }
        $where = "WHERE ".implode(" OR " , $where);// id like '%searchValue%' or name like '%searchValue%' ....
        /* End WHERE */

        $sql = sprintf("SELECT * FROM %s %s %s", MyTable , $where, " AND CREATED_BY='".$uid."'");//Search query without limit clause (No pagination)

        $recordsFiltered = count(getDataJson($sql));//Count of search result

        /* SQL Query for search with limit and orderBy clauses*/
        $sql = sprintf("SELECT * FROM %s %s %s ORDER BY %s %s limit %d , %d ", MyTable , $where, " AND CREATED_BY='".$uid."'" ,$orderBy, $orderType ,$start,$length  );
        $data = getDataJson($sql);
    }
    /* END SEARCH */
    else {
         $sql = sprintf("SELECT * FROM %s %s ORDER BY %s %s limit %d , %d ", MyTable , " WHERE CREATED_BY='".$uid."'", $orderBy,$orderType ,$start , $length);
         $data = getDataJson($sql);

         $recordsFiltered = $recordsTotal;
    }

    /* Response to client before JSON encoding */
        $response = array(
         "draw"             => intval($draw),
         "recordsTotal"     => $recordsTotal,
         "recordsFiltered"  => $recordsFiltered,
         "data"             => $data
        );

        echo json_encode($response);

    } else {
        echo "NO POST Query from DataTable";
    }


}else{
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

?>
