<?php
$DIR = 'peta';
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR, '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink 	= $User->GetDbConnectionFromApp($a);
$appConfig 	= $User->GetAppConfig($a);
$dbSpec 	= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$appConfig = $User->GetAppConfig($a);
$url = $appConfig['MAP_URL'];

?>

<script type="text/javascript" src="inc/js/jquery-1.3.2.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<br />

<h4>Sistem Informasi Geografis</h4>
<form method="post" id="formAct">
	<ol>
		<li>Pilih jenis peta yang ingin anda buka</li>
		<ul>
			<li>
				Peta Objek Pajak / Tematik
				<button type="button" onclick="javascript:open_webgis()" target="_map">
					<i class="fa fa-search"></i> Lihat Peta
				</button>
			</li>
			<br />
			<li>Gambar Baru / Update / Hapus NOP</li>
			<ul>
				<li>
					<label>
						<!--<input type="text" id="input-nop" placeholder="Masukkan NOP" maxlength="18" style="width:200px">-->
						<input type="text" style="width:40px" id="input-nop-1" placeholder="PR">
						<input type="text" style="width:60px" id="input-nop-2" placeholder="DTII">
						<input type="text" style="width:60px" id="input-nop-3" placeholder="KEC">
						<input type="text" style="width:60px" id="input-nop-4" placeholder="KEL">
						<input type="text" style="width:60px" id="input-nop-5" placeholder="BLOK">
						<input type="text" style="width:60px" id="input-nop-6" placeholder="NO.URUT">
						<input type="text" style="width:60px" id="input-nop-7" placeholder="KODE">
						<button type="button" id="srch-button" onclick="javascript:open_webgis('modify')"><i class="fa fa-pencil">
							</i> Buka
						</button>
					</label>
				</li>
			</ul>
		</ul>
	</ol>
</form>

<script>
	function open_webgis(type) {
		var nop1 = $.trim($('#input-nop-1').val());
		var nop2 = $.trim($('#input-nop-2').val());
		var nop3 = $.trim($('#input-nop-3').val());
		var nop4 = $.trim($('#input-nop-4').val());
		var nop5 = $.trim($('#input-nop-5').val());
		var nop6 = $.trim($('#input-nop-6').val());
		var nop7 = $.trim($('#input-nop-7').val());
		var nop = nop1 + '' + nop2 + '' + nop3 + '' + nop4 + '' + nop5 + '' + nop6 + '' + nop7;
		var url = '<?php echo $url ?>';
		var name = 'webgis';

		if (typeof type !== 'undefined') {
			if (nop === '') {
				alert('Silakan masukkan NOP');
				return false;
			}
			url += 'nop/' + nop;
			name = 'drawmodify';
		}

		window.open(url, name);
	}
</script>