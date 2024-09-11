<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
//$nop1 = @isset($_REQUEST['n1']) ? $_REQUEST['n1'] : "";
//$nop2 = @isset($_REQUEST['n2']) ? $_REQUEST['n2'] : "";
//$nop3 = @isset($_REQUEST['n3']) ? $_REQUEST['n3'] : "";
//$nop4 = @isset($_REQUEST['n4']) ? $_REQUEST['n4'] : "";
//$nop5 = @isset($_REQUEST['n5']) ? $_REQUEST['n5'] : "";
//$nop6 = @isset($_REQUEST['n6']) ? $_REQUEST['n6'] : "";
//$nop7 = @isset($_REQUEST['n7']) ? $_REQUEST['n7'] : "";
$nop 		= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$status 	= @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

if ($q == "" || $nop == "") exit(1);

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
$dbname = $_REQUEST['GW_DBNAME'];

$svcPembatalan = new SvcPembatalanSPPT($dbSpec);
$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_PORT = $port;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;

$arrWhere = array();
//if ($nop1 != "") array_push($arrWhere, "SUBSTR(nop, 1, 2) = '{$nop1}'");
//if ($nop2 != "") array_push($arrWhere, "SUBSTR(nop, 3, 2) = '{$nop2}'");
//if ($nop3 != "") array_push($arrWhere, "SUBSTR(nop, 5, 3) = '{$nop3}'");
//if ($nop4 != "") array_push($arrWhere, "SUBSTR(nop, 8, 3) = '{$nop4}'");
//if ($nop5 != "") array_push($arrWhere, "SUBSTR(nop, 11, 3) = '{$nop5}'");
//if ($nop6 != "") array_push($arrWhere, "SUBSTR(nop, 14, 4) = '{$nop6}'");
//if ($nop7 != "") array_push($arrWhere, "SUBSTR(nop, 18, 1) = '{$nop7}'");

//$nop = $nop1 . "" . $nop2 . "" . $nop3 . "" . $nop4 . "" . $nop5 . "" . $nop6 . "" . $nop7;
if ($nop!="") array_push($arrWhere,"nop LIKE '%{$nop}%'");

$where = implode(" AND ", $arrWhere);
// echo $where;exit;
if (stillInSession($DBLink, $json, $sdata)) {
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
		$(\".btn-pembatalan\").on('click', function(){
			//alert('test');
            $(\"#box1\").css(\"display\",\"block\");
            $(\"#box2\").css(\"display\",\"block\");
            var wp = $(this).attr(\"id\");
            var v_wp = wp.split(\"+\");
			
			$(\"#nop\").attr(\"value\",v_wp[0]);
			$(\"#thn\").attr(\"value\",v_wp[1]);
			$(\"#uid\").attr(\"value\",v_wp[2]);
        });
        $(\"#closednomor\").on('click', function(){
            $(\"#box2\").css(\"display\",\"none\");
            $(\"#box1\").css(\"display\",\"none\");
        });
		$(\"#btn-batal\").on('click', function(){
            $(\"#box2\").css(\"display\",\"none\");
            $(\"#box1\").css(\"display\",\"none\");
        });
		$(\"#btn-ya\").on('click', function(){
			var proses = 1;
			var no_sk = $('#batal-nomor-sk').val();
			var alasan = $('#batal-alasan').val();
			// if (no_sk==''){
			// 	alert('Silahkan isi NO SK');
			// 	exit;
			// }	
			if (alasan==''){
				alert('Silahkan isi Alasan Penolakan');
				exit;
			}


            $.ajax({
               	type: \"POST\",
               	url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan.php\",
			   	data: \"uid=\"+$(\"#uid\").val()+\"&alasan=\"+alasan+\"&no_sk=\"+no_sk+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT+\"&USER_LOGIN=\"+USER_LOGIN+\"&TAHUN_TAGIHAN=\"+TAHUN_TAGIHAN,
               	dataType : \"json\",
			   	success: function(data){
			   		// alert(JSON.stringify(data));
                   	$(\"#box2\").hide();
                   	$(\"#box1\").hide();
				   	console.log(data.message)
				   	if(data.respon==true){
					   	alert(\"Pembatalan SPPT Sukses!\");
					   	onSearchPembatalanSPPT(6);
				   	} else alert('Pembatalan SPPT Gagal!');
               	},
			   	error : function(data){
				   	console.log(data)
			   	}
             });
        });
		$(\"#btn-tidak\").click(function(){
			var proses = 2;
			var no_sk = $('#batal-nomor-sk').val();
			var alasan = $('#batal-alasan').val();
			// if (no_sk==''){
			// 	alert('Silahkan isi NO SK');
			// 	exit;
			// }	
			if (alasan==''){
				alert('Silahkan isi Alasan Penolakan');
				exit;
			}

            $.ajax({
               	type: \"POST\",
               	url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan.php\",
				data: \"uid=\"+$(\"#uid\").val()+\"&alasan=\"+alasan+\"&no_sk=\"+no_sk+\"&proses=\"+proses+\"&nop=\"+$(\"#nop\").val()+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT+\"&USER_LOGIN=\"+USER_LOGIN+\"&TAHUN_TAGIHAN=\"+TAHUN_TAGIHAN,
               	dataType : \"json\",
			   	success: function(data){
                   $(\"#box2\").hide();
                   $(\"#box1\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Pembatalan SPPT Sukses!\");
					   onSearchPembatalanSPPT(6);
				   } else alert('Pembatalan SPPT Gagal!');
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
			<br>
			<br>
				
			<div class='wadah' style='margin-left:15px;margin-bottom:50px;'>
				<!-- div style='float:left;width:100px;text-align:left'>
					Nomor SK :
				</div>
				<div style='float:left;width:100px;text-align:left'>
					<input type=\"text\" id=\"batal-nomor-sk\" placeholder=\"Nomor SK\" />
				</div>
				<br>
				<br>
				<br -->
				<div style='float:left;width:100px;'>Alasan :</div>
				<div style='float:left;width:100px;'>
					<textarea row='2' type=\"text\" id=\"batal-alasan\" placeholder=\"Alasan Pembatalan\" ></textarea>
				</div>
			</div>

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
	$res = $svcPembatalan->getGateWayPBBSPPT($nop);
	$rowCount = mysqli_num_rows($res);
	while ($row = mysqli_fetch_assoc($res)) {
		$htmlData .= '<tr>
					<td>' . ($row['PAYMENT_FLAG'] != '1' ? '<input type="button" class="btn-pembatalan" name="btn-pembatalan" id="' . $row['NOP'] . '+' . $row['SPPT_TAHUN_PAJAK'] . '+' . $uid . '" value="Batalkan SPPT">' : 'LUNAS') . '</td>
					<td>' . $row['NOP'] . '</td>
					<td>' . $row['WP_NAMA'] . '</td>
					<td>' . $row['WP_ALAMAT'] . '</td>
					<td>' . $row['OP_ALAMAT'] . '</td>
					<td align=center>' . $row['SPPT_TAHUN_PAJAK'] . '</td>
					<td align=right>' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.') . '</td>
				</tr>';
	}
	if ($rowCount > 0) {
		echo '<div id="frame-tbl-monitoring" class="tbl-monitoring">
                <table class="table table-bordered table-striped table-hover">
					<tr>
						<th class=tdheader width=9>Aksi</th>
						<th class=tdheader width=9>NOP</th>
						<th class=tdheader>Nama WP</th>
						<th class=tdheader>Alamat WP</th>
						<th class=tdheader>Alamat OP</th>
						<th class=tdheader width=9>Thn Pajak</th>
						<th class=tdheader>Tagihan</th>
					</tr>
                    ' . $htmlData . '
                </table></div>';
	} else {
		echo  "<br><br>Data tidak ditemukan !\n<br><br>";
	}
} else {
	echo  "<br><br>Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n<br><br>";
}
