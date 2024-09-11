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
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$q 			= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$nop 		= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$kel 		= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : false;
$kec 		= @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : false;
$jns 		= @isset($_REQUEST['jns']) ? $_REQUEST['jns'] : false;
$status 	= @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

if ($q=="") exit(1);
if (!$kec && $nop=='') exit();

$q 			= base64_decode($q);
$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$arConfig 	= $User->GetModuleConfig($moduleIds);
$appConfig 	= $User->GetAppConfig($area);
$tahun		= $appConfig['tahun_tagihan'];

$host 	= isset($_REQUEST['GW_DBHOST'])?$_REQUEST['GW_DBHOST']:'';
$port 	= isset($_REQUEST['GW_DBPORT'])?$_REQUEST['GW_DBPORT']:'';
$user 	= isset($_REQUEST['GW_DBUSER'])?$_REQUEST['GW_DBUSER']:'';
$pass 	= isset($_REQUEST['GW_DBPWD'])?$_REQUEST['GW_DBPWD']:'';
$dbname = isset($_REQUEST['GW_DBNAME'])?$_REQUEST['GW_DBNAME']:''; 

$svcPembatalan = new SvcPembatalanSPPT($dbSpec);

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;
$svcPembatalan->C_PORT = $port;

$arrWhere = array();
if ($nop!="") array_push($arrWhere,"nop LIKE '%{$nop}%'");
           
$where = implode (" AND ",$arrWhere);
// echo $where;exit;
if(stillInSession($DBLink,$json,$sdata)) {
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
	});
	</script>
	<div id=\"box2\">
		
		<div align=\"center\" id=\"setnomor\" style=\"width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;\">
		<div style=\"width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;\"><div id=\"closednomor\" style=\"float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;\">X</div></div>
					<br>
					<br>
					<div class='wadah' style='margin-left:15px;margin-bottom:50px;'>
						<div style='float:left;width:100px;text-align:left'>
							Nomor SK :
						</div>
						<div style='float:left;width:100px;text-align:left'>
							<input type=\"text\" id=\"batal-nomor-sk\" placeholder=\"Nomor SK\" />
						</div>
						<br>
						<br>
						<br>
						<div style='float:left;width:100px;'>
						Alasan :
						</div>
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
    //var_dump('1', ($nop==""));exit;

	$res = $svcPembatalan->getRiwayatPembatalan($jns, $kec, $kel, $nop, $tahun='');

	
	$rowCount = mysqli_num_rows($res);
	$htmlData1 = $htmlData;
    $htmlData = "";
	$counter = 0;
	while ($row = mysqli_fetch_assoc($res)){
		$htmlData .= '<tr>
			<td style="display:none">'.($row['PAYMENT_FLAG']!='X' ? '<input type="button" class="btn-pembatalan" name="btn-pembatalan" id="'.$row['NOP'].'+'.$row['SPPT_TAHUN_PAJAK'].'+'.$uid.'" value="Kembalikan SPPT">' : 'TAGIHAN LUNAS').'</td>
			<td align=right>'. ++$counter .'</td>
			<td>'.$row['KECAMATAN'].'</td>
			<td>'.$row['KELURAHAN'].'</td>
			<td>'.$row['OP_ALAMAT'].'</td>
			<td>'.$row['NOP'].'</td>
			<td>'.$row['WP_NAMA'].'</td>
			<td>'.$row['WP_ALAMAT'].'</td>
			<td align=center>'.$row['SPPT_TAHUN_PAJAK'].'</td>
			<td align=right>'.number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,',','.').'</td>
		</tr>';
	}
	if($rowCount>0){

		echo '<div id="frame-tbl-monitoring" class="tbl-monitoring">
                <table class="table table-bordered table-striped table-hover">
                    <tr>
                        <th style="display:none" width="100px">Aksi</th>
                        <th class=tdheader width=9>#</th>
                        <th class=tdheader>Kecamatan</th>
                        <th class=tdheader>Kel/Desa</th>
                        <th class=tdheader>Alamat OP</th>
                        <th class=tdheader width=9>NOP</th>
                        <th class=tdheader>Nama WP</th>
                        <th class=tdheader>Alamat WP</th>
                        <th class=tdheader width=9>Thn Pajak</th>
                        <th class=tdheader>Tagihan</th>
                    </tr>
                    '.$htmlData.'
                </table>
                <div style="display:none" class="count-data">'.$rowCount.'</div>
                </div>';
    }else{
                    // <th width=\"300px\">No SK</th>
                    // <th width=\"300px\">Alasan</th>
            echo  "<br><br>Data tidak ditemukan !\n<br><br>";
    }
}else{
	echo  "<br><br>Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n<br><br>";
}
?>