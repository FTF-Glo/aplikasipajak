<style type="text/css">
	table{
		border-collapse: collapse;
	}
</style>
<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'perubahan_znt_massal', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/central/user-central.php");

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

/* inisiasi parameter */
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$find 	= @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$NOP_multi 	= @isset($_REQUEST['NOP_multi']) ? $_REQUEST['NOP_multi'] :1;

$NOP_multi = explode(",", $NOP_multi);
$NOP_multi_string = "";
foreach ($NOP_multi as $key => $value) {
	$NOP_multi_string .= "'".trim($value)."',";
}
$NOP_multi_string = rtrim($NOP_multi_string,",");

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;

// NOP_multi
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
// exit;



$data = getListNOP($NOP_multi_string);
echo $data;
// echo "<pre>";
// print_r($data);
// echo "</pre>";

function getListNOP($listnop){
	global $DBLink,$appConfig,$old_znt;
	
	$thn_tagihan = $appConfig['tahun_tagihan'];
	
	$query = "
            SELECT * FROM (
             SELECT * FROM cppmod_pbb_sppt_final WHERE  CPM_NOP IN ($listnop) 
			 UNION
			 SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN ($listnop) 
             ) AS DATA 
             LEFT JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = DATA.CPM_OP_KECAMATAN
             LEFT JOIN cppmod_tax_kelurahan KL ON KL.CPC_TKL_ID = DATA.CPM_OP_KELURAHAN
             ";
	// echo $query; exit;
    $res = mysqli_query($DBLink, $query);
    $num = mysqli_num_rows($res);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        if ($num>0){

        $html =  "
        <div style='display:none' id='num-row'>$num</div>
        <table border='1' width='100%' cellpadding='3'>";
        $html .= "<thead>";
        $html.="
    		<tr>
    			<th>No</td>
    			<th>NOP</td>
    			<th>NAMA WP</td>
    			<th>ZONA NILAI TANAH SAAT INI </td>
                <th>ALAMAT</td>
    			<th>KECAMATAN OP</td>
                <th>KELURAHAN OP</td>
    		</tr>

        	";
        $html .= "</thead>";
        $n = 1;
        while ($row = mysqli_fetch_assoc($res)) {
        	$html.="
        	<tr>
        		<td>$n</td>
        		<td>$row[CPM_NOP]</td>
        		<td>$row[CPM_WP_NAMA]</td>
        		<td>$row[CPM_OT_ZONA_NILAI]</td>
        		<td>$row[CPM_WP_ALAMAT]</td>
                <td>$row[CPC_TKC_KECAMATAN]</td>
                <td>$row[CPC_TKL_KELURAHAN]</td>
        	</tr>
        	";
        	$n++;
            // $data[$row['CPM_NOP']][] = $row['CPM_NOP'];
            // $data[$row['CPM_NOP']][] = $row['CPM_WP_NAMA'];
        }        
        $html .= "</table>";
        }else{
        	$html.= "<p style='color:red'>Data Tidak ditemukan</p>";
        }
        return $html;
    }
	
}

?>