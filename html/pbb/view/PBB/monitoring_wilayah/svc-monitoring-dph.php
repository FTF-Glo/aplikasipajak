<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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

define("PBB_DPH", "cppmod_pbb_dph");
define("PBB_DPH_DETAIL","cppmod_pbb_dph_DETAIL"); 
define("TEMP","cppmod_pbb_dph_TEMP");

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

$q          = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p          = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml        = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn        = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$thn2       = @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : 1;
$nop        = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na         = @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : "";
$status     = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$tempo1     = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2     = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan  = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan  = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$export     = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$tagihan    = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$bank       = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$barcode    = @isset($_REQUEST['barcode']) ? $_REQUEST['barcode'] : "0";
$simpan     = @isset($_REQUEST['simpan']) ? $_REQUEST['simpan'] : "0";
$edit       = @isset($_REQUEST['edit']) ? $_REQUEST['edit'] : "0";
$DPH        = @isset($_REQUEST['noDph']) ? $_REQUEST['noDph'] : "0";


if ($q=="") exit(1);
$q = base64_decode($q);
// echo $q;
// echo "<br>";
$j = $json->decode($q);
// $a = $q->a;
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig  = $User->GetAppConfig($area);

$dbname_sw  =  $appConfig['[ADMIN_GW_DBNAME'];

$st         = "devel";

if ($st=="devel"){
    $dbname_sw = "GW_PBB_CIANJUR_66";
}   

$host       = $_REQUEST['GW_DBHOST'];
$port       = $_REQUEST['GW_DBPORT'];
$user       = $_REQUEST['GW_DBUSER'];
$pass       = $_REQUEST['GW_DBPWD'];
$dbname     = $_REQUEST['GW_DBNAME']; 

$GWDBLink   = mysqli_connect($host,$user,$pass,$dbname) or die(mysqli_error($DBLink));
//mysql_select_db($dbname,$GWDBLink);

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"80px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"CPC_NM_SEKTOR\", \"length\" : \"280px\", \"title\" : \"Sektor Daerah\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"180px\", \"title\" : \"".$_REQUEST['LBL_KEL']." WP\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"180px\", \"title\" : \"".$_REQUEST['LBL_KEL']." OP\", \"align\":\"center\"},
{\"field\":\"op_rt\", \"length\" : \"160px\", \"title\" : \"RT OP\", \"align\":\"center\"},
{\"field\":\"op_rw\", \"length\" : \"160px\", \"title\" : \"RW OP\", \"align\":\"center\"},
{\"field\":\"op_luas_bumi\", \"length\" : \"140px\", \"title\" : \"Luas Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_luas_bangunan\", \"length\" : \"140px\", \"title\" : \"Luas Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bumi\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bangunan\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Tgl Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Pokok\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_denda\", \"length\" : \"80px\", \"title\" : \"Denda\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_total_bayar\", \"length\" : \"80px\", \"title\" : \"Total\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", \"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
{\"field\":\"payment_paid\", \"length\" : \"180px\", \"title\" : \"Tanggal\", \"align\":\"right\",\"format\":\"date\"},
{\"field\":\"CDC_B_NAME\", \"length\" : \"200px\", \"title\" : \"Bank\", \"align\":\"center\"}
]}";

$arrTempo = array();
if ($tempo1!="") array_push($arrTempo,"A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2!="") array_push($arrTempo,"A.payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode (" AND ",$arrTempo);
$arrWhere = array();
if ($kecamatan !="" && $kecamatan !='undefined') {
    if ($kelurahan !="" && $kelurahan !='undefined'){  
        array_push($arrWhere,"A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
        array_push($arrWhere,"A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
    }
    else {
        array_push($arrWhere,"A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
    } 
}else  {
     if ($kelurahan !="" && $kelurahan !='undefined'){  
         array_push($arrWhere,"A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
    }
}

if ($nop!="") array_push($arrWhere,"A.nop LIKE '{$nop}%'");
if ($thn!="") array_push($arrWhere,"A.SPPT_TAHUN_PAJAK between  '{$thn}' and '{$thn2}'  ");
if ($na!="") array_push($arrWhere,"A.wp_nama like '%{$na}%'");
if ($status!="") {
    array_push($arrWhere,"(A.payment_flag != 1 OR A.payment_flag IS NULL)");            
}
if ($tempo1!="") array_push($arrWhere,"({$tempo})");


if($tagihan != 0){
    switch ($tagihan){
        case 1        : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 100000) "); 
                        break;
        case 12       : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) "); 
                        break;
        case 123      : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); 
                        break;
        case 1234     : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); 
                        break;
        case 12345    : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); 
                        break;
        case 2        : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) "); 
                        break;
        case 23       : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); 
                        break;
        case 234      : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); 
                        break;
        case 2345     : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); 
                        break;
        case 3        : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); 
                        break;
        case 34       : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); 
                        break;
        case 345      : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); 
                        break;
        case 4        : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); 
                        break;
        case 45       : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); 
                        break;
        case 5        : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); 
                        break;
    }
}

if ($bank!=0) array_push($arrWhere,"A.PAYMENT_BANK_CODE IN ('".str_replace(",", "','", $bank)."') ");
                


if(stillInSession($DBLink,$json,$sdata)){ 
    if ($simpan == 1 ){ // fungsi untuk menyimpan daftar DPH detail lama diambil dari array dan insert into langsung
        global $DBLink;    
        $where = implode (" AND ",$arrWhere);
        // $res1;
        //$noDph      = date("Ymdhis");
        $noDph      = @isset($_POST['noDph']) ? $_POST['noDph'] : "0";
        $namaFile   = @isset($_POST['nameFile']) ? $_POST['nameFile'] : "0";
        $jsonDaftar = @isset($_POST['jsonDaftar']) ? $_POST['jsonDaftar'] : "0";

        $dataJson = json_decode($jsonDaftar, true);

        foreach ($dataJson as $key  ) {
            $selecta ="INSERT INTO ".PBB_DPH_DETAIL." (NO_DPH,NOP,TAHUN)
            VALUES ('".$noDph."','".$key[0]."',".$key[1].")";    
            $res = mysqli_query($GWDBLink, $selecta);         
        }
        // insert dari temp ke cppmod_pbb_dph_DETAIL
        $sql_select ="INSERT INTO ".PBB_DPH_DETAIL." (NO_DPH,NOP,TAHUN)
        SELECT '".$noDph."', A.NOP, A.TAHUN FROM ".TEMP." as A where A.NO_DPH=".$noDph;    
        
        $res = mysqli_query($GWDBLink, $sql_select);
        // HAPUS TEMP
        $delete_temp ="DELETE FROM ".TEMP." WHERE NO_DPH = ".$noDph;   
        $result_delete = mysqli_query($GWDBLink, $delete_temp);

        if ( $res1 === false ){
                echo $qry ."<br>";
                echo mysqli_error($GWDBLink);
        }else {
               echo 'true';
        }

    }else if ($simpan == 2){ // fungsi untuk menyimpan daftar group baru
        global $DBLink;    
        $where = implode (" AND ",$arrWhere);

        $noDph = date("Ymdhis");
        $namaFile = @isset($_POST['nameFile']) ? $_POST['nameFile'] : "0";
        $sql_select ="INSERT INTO ".PBB_DPH." (NO_DPH, NAMA_FILE,CREATED_BY)
        VALUES ('".$noDph."','".$namaFile."','".$uid."')";    
        
        $res = mysqli_query($GWDBLink, $sql_select);
        if ( $res === false ){
                echo $qry ."<br>";
                echo mysqli_error($GWDBLink);
        }else {
               echo 'true';
        }
    }else if ($simpan==3){ // untuk menyimpan hasil filter ( button tambahkan )
        global $DBLink;    
        $where = implode (" AND ",$arrWhere);

        $noDph      = date("Ymdhis");
        $namaFile   = @isset($_POST['nameFile']) ? $_POST['nameFile'] : "0";
        $jsonDaftar = @isset($_POST['jsonDaftar']) ? $_POST['jsonDaftar'] : "0";

        $dataJson = json_decode($jsonDaftar, true);

          foreach ($dataJson as $key  ) {
            $selecta ="INSERT INTO ".PBB_DPH_DETAIL." (NO_DPH, NAMA_FILE,NOP,TAHUN,CREATED_BY)
            VALUES ('".$noDph."','".$namaFile."','".$key[0]."',".$key[1].",'".$uid."')";    
            $res = mysqli_query($GWDBLink, $selecta);         
        }

        $sql_select ="INSERT INTO ".PBB_DPH_DETAIL." (NO_DPH, NAMA_FILE,NOP,TAHUN,CREATED_BY)
        SELECT '".$noDph."','".$namaFile."', A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN ,'".$uid ."' FROM PBB_SPPT as A where ".$where;    
        
        $res = mysqli_query($GWDBLink, $sql_select);
        if ( $res === false ){
                echo $qry ."<br>";
                echo mysqli_error($GWDBLink);
        }else {
               echo 'true';
        }

    


    }else if($edit==1){ // fungsi untuk menampilkan dph detail dari group yang di pilih
        global $DBLink;
        // hapus temporary cppmod_pbb_dph_TEMP
        $sql_detail =  "DELETE FROM ".TEMP." WHERE NO_DPH = ".$DPH;
        $res_detail = mysqli_query($GWDBLink, $sql_detail);

     
        $where1 = implode (" AND ",$arrWhere);

        $monPBB1 = new dbMonitoringDph ($host,$port,$user,$pass,$dbname);
        $monPBB1->setConnectToMysql();
        $monPBB1->setRowPerpage(30);
        $monPBB1->setPage($p);
        $monPBB1->setStatus($status);

        $sql_table = "PBB_SPPT A JOIN cppmod_pbb_dph_DETAIL B ON  A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.TAHUN";
        // $sql_table = "PBB_SPPT A JOIN ".PBB_DPH_DETAIL." B ON  A.NOP = B.NOP 
        // AND A.SPPT_TAHUN_PAJAK = B.TAHUN JOIN ".TEMP." T ON T.NO_DPH=".$DPH."";

        $sql_select =" SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        IFNULL(A.PBB_DENDA,0) as DENDA ,
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH";

        // $_sql_table = "PBB_SPPT A JOIN cppmod_pbb_dph_TEMP T ON A.NOP = T.NOP 
        // AND A.SPPT_TAHUN_PAJAK = T.TAHUN ";

        // $_sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA, OP_KELURAHAN AS DESA_OP ,
        //  OP_KECAMATAN AS KECAMATAN_OP 
        // , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG, IFNULL(A.PBB_DENDA,0) as DENDA ,
        //  IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH 
        // FROM PBB_SPPT A JOIN cppmod_pbb_dph_DETAIL B ON A.NOP = B.NOP 
        // AND A.SPPT_TAHUN_PAJAK = B.TAHUN WHERE ".$where1."AND B.NO_DPH LIKE '".$DPH."%' UNION ALL
        // SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA, OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP 
        // , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG, IFNULL(A.PBB_DENDA,0) as DENDA ,
        //  IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH 
        // ";

        if ($DPH!="" && $DPH !="0")
            array_push($arrWhere,"B.NO_DPH LIKE '{$DPH}%'");
        $where = implode (" AND ",$arrWhere);

        $monPBB1->setTable($sql_table);
        $monPBB1->setWhere($where);      
        $monPBB1->query($sql_select);

        if ($export=="") {
            echo $monPBB1->showHTML(); 

        }else{
            $monPBB1->exportToXls ();
        }

    }else{
        $where_temp = implode (" AND ",$arrWhere); 

        $sql_temp ="INSERT INTO ".TEMP." (NO_DPH,NOP,TAHUN)
        SELECT '".$DPH."', A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN FROM PBB_SPPT A where ".$where_temp.
        "AND A.NOP NOT IN (SELECT T.NOP FROM ".TEMP." T WHERE T.NO_DPH =".$DPH." AND T.TAHUN = A.SPPT_TAHUN_PAJAK)
        AND A.NOP NOT IN (SELECT B.NOP FROM ".PBB_DPH_DETAIL." B WHERE B.NO_DPH =".$DPH." AND B.TAHUN = A.SPPT_TAHUN_PAJAK)";  

        $result_temp = mysqli_query($GWDBLink, $sql_temp);

        
        $where1 = implode (" AND ",$arrWhere);
        // end simpan ke cppmod_pbb_dph_TEMP

        $monPBB1 = new dbMonitoringDph ($host,$port,$user,$pass,$dbname);
        $monPBB1->setConnectToMysql();
        $monPBB1->setRowPerpage(30);
        $monPBB1->setPage($p);
        $monPBB1->setStatus($status);

        // $sql_table = "PBB_SPPT A";
        // $sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        // OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        // IFNULL(A.PBB_DENDA,0) as DENDA ,
        // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+A.PBB_DENDA,0) as JUMLAH ";

        // $sql_table = "PBB_SPPT A JOIN ".PBB_DPH_DETAIL." B ON  A.NOP = B.NOP 
        // AND A.SPPT_TAHUN_PAJAK = B.TAHUN JOIN ".TEMP." T ON T.NO_DPH=".$DPH."";

        // $sql_select =" SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        // OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        // IFNULL(A.PBB_DENDA,0) as DENDA ,
        // IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+A.PBB_DENDA,0) as JUMLAH ";

        $where1 = implode (" AND ",$arrWhere);

        $_sql_table = "PBB_SPPT A JOIN cppmod_pbb_dph_TEMP T ON A.NOP = T.NOP 
        AND A.SPPT_TAHUN_PAJAK = T.TAHUN ";

        $_sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA, OP_KELURAHAN AS DESA_OP ,
         OP_KECAMATAN AS KECAMATAN_OP 
        , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG, IFNULL(A.PBB_DENDA,0) as DENDA ,
         IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH
        FROM PBB_SPPT A JOIN cppmod_pbb_dph_DETAIL B ON A.NOP = B.NOP 
        AND A.SPPT_TAHUN_PAJAK = B.TAHUN WHERE ".$where1."AND B.NO_DPH LIKE '".$DPH."%' UNION ALL
        SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA, OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP 
        , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG, IFNULL(A.PBB_DENDA,0) as DENDA ,
         IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH 
        ";

        if ($DPH!="" && $DPH !="0")
            array_push($arrWhere,"T.NO_DPH LIKE '{$DPH}%'");

        $where = implode (" AND ",$arrWhere);

        $monPBB1->setTable($_sql_table);
        $monPBB1->setWhere($where);      
        $monPBB1->query($_sql_select);
 
        //print_r($monPBB1->getAllQuery());
        //exit;

            if ($export=="") {
                echo $monPBB1->showHTML(); // ini pengambilan datanya
            }else{
                $monPBB1->exportToXls ();
            }
    
    }
    
       

}else{
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

?>
