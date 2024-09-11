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
$setting 	= new SCANCentralSetting(0, LOG_FILENAME, $DBLink);
$q 			= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$nop1 = @isset($_REQUEST['n1']) ? $_REQUEST['n1'] : "";
$nop2 = @isset($_REQUEST['n2']) ? $_REQUEST['n2'] : "";
$nop3 = @isset($_REQUEST['n3']) ? $_REQUEST['n3'] : "";
$nop4 = @isset($_REQUEST['n4']) ? $_REQUEST['n4'] : "";
$nop5 = @isset($_REQUEST['n5']) ? $_REQUEST['n5'] : "";
$nop6 = @isset($_REQUEST['n6']) ? $_REQUEST['n6'] : "";
$nop7 = @isset($_REQUEST['n7']) ? $_REQUEST['n7'] : "";
$status 	= @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

if ($q == "") exit(1);

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

// $arrWhere = array();
// if ($nop1 != "") array_push($arrWhere, "SUBSTR(nop, 1, 2) = '{$nop1}'");
// if ($nop2 != "") array_push($arrWhere, "SUBSTR(nop, 3, 2) = '{$nop2}'");
// if ($nop3 != "") array_push($arrWhere, "SUBSTR(nop, 5, 3) = '{$nop3}'");
// if ($nop4 != "") array_push($arrWhere, "SUBSTR(nop, 8, 3) = '{$nop4}'");
// if ($nop5 != "") array_push($arrWhere, "SUBSTR(nop, 11, 3) = '{$nop5}'");
// if ($nop6 != "") array_push($arrWhere, "SUBSTR(nop, 14, 4) = '{$nop6}'");
// if ($nop7 != "") array_push($arrWhere, "SUBSTR(nop, 18, 1) = '{$nop7}'");

$nop = $nop1 . $nop2 . $nop3 . $nop4 . $nop5 . $nop6 . $nop7;

// $where = implode(" AND ", $arrWhere);
// echo $where;exit;
if (stillInSession($DBLink, $json, $sdata)) {
	$htmlData = "
	<style>
		#bx1, #bx2{
			display:none;
			position:fixed;
			height:100%;
			width:100%;
			top:0;
			left:0;
		}
		#bx1{
			background-color:#000000;
			filter:alpha(opacity=70);
			opacity:0.7;
			z-index:1;
		}
		#bx2{
			z-index: 2;
		}
		#closednomor{cursor: pointer;}
	</style>
	<script>
	$(document).ready(function() {
		$(\".btn-penerbitan\").on('click', function(){
			//alert('test');
            $(\"#bx1\").css(\"display\",\"block\");
            $(\"#bx2\").css(\"display\",\"block\");
            var wp = $(this).attr(\"id\");
            var v_wp = wp.split(\"+\");
			
			$(\"#nop\").attr(\"value\",v_wp[0]);
			$(\"#thn\").attr(\"value\",v_wp[1]);
			$(\"#uid\").attr(\"value\",v_wp[2]);
        });
        $(\"#closednomor\").on('click', function(){
            $(\"#bx2\").css(\"display\",\"none\");
            $(\"#bx1\").css(\"display\",\"none\");
        });
		$(\"#btn-batal-2\").on('click', function(){
            $(\"#bx2\").css(\"display\",\"none\");
            $(\"#bx1\").css(\"display\",\"none\");
        });
		$(\"#btn-ya-2\").on('click', function(){
			var proses = 1;
			var alasan = $('#terbit-alasan').val();
            $.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-penerbitan.php\",
			   data: \"alasan=\"+alasan+\"&uid=\"+$(\"#uid\").val()+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT+\"&TAHUN_TAGIHAN=\"+TAHUN_TAGIHAN,
               dataType : \"json\",
			   success: function(data){
                   $(\"#bx2\").hide();
                   $(\"#bx1\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Penerbitan SPPT Sukses!\");
					   onSearchPenerbitanSPPT(7);
				   } else alert('Penerbitan SPPT Gagal!');
               },
			   error : function(data){
				   console.log(data)
			   }
             });
        });
		$(\"#btn-tidak-2\").on('click', function(){
			var proses = 2;
			var alasan = $('#terbit-alasan').val();
            $.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-penerbitan.php\",
			   data: \"alasan=\"+alasan+\"&uid=\"+$(\"#uid\").val()+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT+\"&TAHUN_TAGIHAN=\"+TAHUN_TAGIHAN,
               dataType : \"json\",
			   success: function(data){
                   $(\"#bx2\").hide();
                   $(\"#bx1\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Penerbitan SPPT Sukses!\");
					   onSearchPenerbitanSPPT(7);
				   } else alert('Penerbitan SPPT Gagal!');
               },
			   error : function(data){
				   console.log(data)
			   }
             });
        });
		
	});
	</script>
	<div id=\"bx2\">
		<div align=\"center\" id=\"setnomor\" style=\"width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;\">
		<div style=\"width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;\"><div id=\"closednomor\" style=\"float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;\">X</div></div>
		
		<div class='wadah' style='margin-left:15px;margin-bottom:50px;'>
			<div style='float:left;width:100px;'>Alasan :</div>
			<div style='float:left;width:100px;'>
				<textarea row='2' type=\"text\" id=\"terbit-alasan\" placeholder=\"Alasan Penerbitan\" ></textarea>
			</div>
		</div>
		
		<div style=\"margin: 10px;margin-left: 10px;\">
		Ubah data NOP menjadi Fasilitas Umum?<br><br>
		<form action=\"\">
			<input type=\"hidden\" id=\"nop\"/>
			<input type=\"hidden\" id=\"thn\"/>
			<input type=\"hidden\" id=\"uid\"/>
			<input type=\"button\" name=\"btn-ya\" id=\"btn-ya-2\" value=\"Ya\">
			<input type=\"button\" name=\"btn-tidak\" id=\"btn-tidak-2\" value=\"Tidak\">
			<input type=\"button\" name=\"btn-batal\" id=\"btn-batal-2\" value=\"Batal\">
		</form>
        </div>
    </div>
	</div>
	<div id=\"bx1\"></div>";
	$res = $svcPembatalan->getGateWayPBBSPPTPembatalan($nop);
	$rowCount = mysqli_num_rows($res);
	while ($row = mysqli_fetch_assoc($res)) {
		$htmlData .= "
			<tr>
                <td><input type=\"button\" class=\"btn-penerbitan\" name=\"btn-penerbitan\" id=\"" . $row['NOP'] . "+" . $row['SPPT_TAHUN_PAJAK'] . "+" . $uid . "\" value=\"Terbitkan SPPT\"></td>
                <td align=center>" . $row['SPPT_TAHUN_PAJAK'] . "</td>
                <td>" . $row['NOP'] . "</td>
                <td>" . $row['WP_NAMA'] . "</td>
                <td>" . $row['WP_ALAMAT'] . "</td>
                <td>" . $row['OP_ALAMAT'] . "</td>
                <td align=right>" . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . "</td>
            </tr>";
	}
	if ($rowCount > 0) {
		echo '<div id="frame-tbl-monitoring" class="tbl-monitoring">
                <table  class="table table-bordered table-striped table-hover">
                    <th class=tdheader width=9>Aksi</th>
                    <th class=tdheader width=9>Thn Pajak</th>
                    <th class=tdheader width=9>NOP</th>
                    <th class=tdheader>Nama WP</th>
                    <th class=tdheader>Alamat WP</th>
                    <th class=tdheader>Alamat OP</th>
                    <th class=tdheader>Tagihan</th>
                    ' . $htmlData . '
                </table></div>';
	} else {
		echo  "Data tidak ditemukan !\n";
	}
} else {
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
