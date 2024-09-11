<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
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
require_once("classPembatalan.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$q 			= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$nop 		= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$thn	 	= @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";

if ($q=="") exit(1);

$q 			= base64_decode($q);
$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$arConfig 	= $User->GetModuleConfig($moduleIds);
$appConfig 	= $User->GetAppConfig($area);
$tahun		= $appConfig['tahun_tagihan'];

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 

$svcPembatalan = new SvcPembatalanSPPT($dbSpec);
$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_PORT = $port;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;

$arrWhere = array();
if ($nop!="") array_push($arrWhere,"nop LIKE '%{$nop}%'");
           
$where = implode (" AND ",$arrWhere);
// echo $where;exit;
if(stillInSession($DBLink,$json,$sdata)){	
	$htmlData = "
	<style>
		#box1, #box2{
			display:none;
			position:fixed;
			height:100%;
			width:100%;
			top:0;
			left:0;
		}
		#box1{
			background-color:#000000;
			filter:alpha(opacity=70);
			opacity:0.7;
			z-index:1;
		}
		#box2{
			z-index: 2;
		}
		#closednomor{cursor: pointer;}
	</style>
	<script>
	$(document).ready(function() {
		$(\".btn-pembatalan\").click(function(){
			//alert('test');
            $(\"#box1\").css(\"display\",\"block\");
            $(\"#box2\").css(\"display\",\"block\");
            var wp = $(this).attr(\"id\");
            var v_wp = wp.split(\"+\");
			
			$(\"#nop\").attr(\"value\",v_wp[0]);
			$(\"#thn\").attr(\"value\",v_wp[1]);
			$(\"#uid\").attr(\"value\",v_wp[2]);
        });
        $(\"#closednomor\").click(function(){
            $(\"#box2\").css(\"display\",\"none\");
            $(\"#box1\").css(\"display\",\"none\");
        });
		$(\"#btn-batal\").click(function(){
            $(\"#box2\").css(\"display\",\"none\");
            $(\"#box1\").css(\"display\",\"none\");
        });
		$(\"#btn-ya\").click(function(){
			var proses = 1;
            $.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan.php\",
			   data: \"uid=\"+$(\"#uid\").val()+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT,
               dataType : \"json\",
			   success: function(data){
                   $(\"#box2\").hide();
                   $(\"#box1\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Pembatalan SPPT Sukses!\");
					   onSearchPembatalanSPPT(6);
				   } else alert('Pembatalan PerKel SPPT Gagal(0)!');
               },
			   error : function(data){
				   console.log(data)
			   }
             });
        });
		$(\"#btn-tidak\").click(function(){
			var proses = 2;
            $.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan.php\",
			   data: \"uid=\"+$(\"#uid\").val()+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT,
               dataType : \"json\",
			   success: function(data){
                   $(\"#box2\").hide();
                   $(\"#box1\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Pembatalan SPPT Sukses!\");
					   onSearchPembatalanSPPT(6);
				   } else alert('Pembatalan PerKel SPPT Gagal(1)!');
               },
			   error : function(data){
				   console.log(data)
			   }
             });
        });
		
	});
	</script>
	<div id=\"box2\">
		<div align=\"center\" id=\"setnomor\" style=\"width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;\">
		<div style=\"width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;\"><div id=\"closednomor\" style=\"float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;\">X</div></div>
		<div style=\"margin: 10px;margin-left: 10px;\">
		Ubah data NOP menjadi Fasilitas Umum?<br><br>
		<form action=\"\">
			<input type=\"hidden\" id=\"nop\"/>
			<input type=\"hidden\" id=\"thn\"/>
			<input type=\"hidden\" id=\"uid\"/>
			<input type=\"button\" name=\"btn-ya\" id=\"btn-ya\" value=\"Ya\">
			<input type=\"button\" name=\"btn-tidak\" id=\"btn-tidak\" value=\"Tidak\">
			<input type=\"button\" name=\"btn-batal\" id=\"btn-batal\" value=\"Batal\">
		</form>
        </div>
    </div>
	</div>
	<div id=\"box1\"></div>";
	$res = $svcPembatalan->getGateWayPBBSPPT($nop,$thn);
	$rowCount = mysqli_num_rows($res);
	while ($row = mysqli_fetch_assoc($res)){
		$htmlData .= "
			<tr>
                <!-- <td align=\"center\">".($row['PAYMENT_FLAG']!='1' ? "<input type=\"button\" class=\"btn-pembatalan\" name=\"btn-pembatalan\" id=\"".$row['NOP']."+".$row['SPPT_TAHUN_PAJAK']."+".$uid."\" value=\"Batalkan SPPT\">" : "TAGIHAN LUNAS")."</td> -->
                <td align=\"center\">&nbsp;".$row['NOP']."</td>
                <td align=\"\">&nbsp;".$row['WP_NAMA']."</td>
                <td align=\"\">&nbsp;".$row['WP_ALAMAT']."</td>
                <td align=\"\">&nbsp;".$row['OP_ALAMAT']."</td>
                <td align=\"center\">&nbsp;".$row['SPPT_TAHUN_PAJAK']."</td>
                <td align=\"right\">&nbsp;".number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td>
            </tr>";
	}
	$htmlData .= "
				<tr>
					<td colspan =\"6\" align=\"right\">Jumlah Data : ".$rowCount."</td>
				</tr>
				";
	if($rowCount>0){
        echo "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">
                <table width=\"auto\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <!-- <th width=\"100px\">Aksi</th> -->
                    <th width=\"110px\">NOP</th>
                    <th width=\"280px\">Nama WP</th>
                    <th width=\"420px\">Alamat WP</th>
                    <th width=\"420px\">Alamat OP</th>
                    <th width=\"80px\">Thn Pajak</th>
                    <th width=\"80px\">Tagihan</th>
                    ".$htmlData."
                </table></div>";
    }else{
            echo  "Data tidak ditemukan !\n";
    }
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>
