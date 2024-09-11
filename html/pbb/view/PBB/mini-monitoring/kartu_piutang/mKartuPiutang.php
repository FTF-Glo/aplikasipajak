<?php 
/* 
Nama File 							: 
Deskripsi File 						: -
Nama Developer (email) 				: 
Tanggal Development					: 06/16/2015
Tanggal Revisi (list) + Perubahan	: -
*/

$DIR = "PBB";
$modul = "kartu_piutang";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
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

displayContent();

function displayContent() {
	global $DIR, $modul, $a, $m, $appConfig;
	
	$thn = date("Y");
    $thnTagihan = $appConfig['tahun_tagihan'];
	
	echo '
	<div class="ui-widget consol-main-content">
		<div class="ui-widget-content consol-main-content-inner">
			<div class="filter">
				<table width="100%" border="0" cellspacing="0" cellpadding="5">
                    <tr>
						<td style="background:#eeeeee;" width=\'70\'><input type="radio" name="tipekartu" id="tipekartu" value="pernop"> Per NOP</td>
						<td style="background:#eeeeee;" width=\'110\'><input type="radio" name="tipekartu" id="tipekartu" value="kec"> Per Kecamatan</td>
						<td style="background:#eeeeee;" width=\'75\'><input type="radio" name="tipekartu" id="tipekartu" value="kel"> Per Desa</td> 
						<td style="background:#eeeeee;"><input type="radio" name="tipekartu" id="tipekartu" value="inv"> Inventaris</td>
                    </tr>
					<tr>
						<td style="background:#eeeeee;" colspan=\'4\'>
							NOP <input type="text" name="nop" id="nop" size="35" maxlength="18" placeholder=" NOP "> &nbsp;&nbsp;&nbsp;&nbsp;
							Kecamatan <select id="kecamatan"></select> &nbsp;&nbsp;&nbsp;&nbsp;
							'.($appConfig['LABEL_KELURAHAN'] <> "" ? $appConfig['LABEL_KELURAHAN'] : "Kelurahan").' <select id="kelurahan"></select> &nbsp;&nbsp;&nbsp;&nbsp;
							<input type="button" name="btnTampil" value="Tampilkan" onClick="showKartuPiutang()"/>
                            <input type="button" name="btnCetak" value="Cetak" onClick="cetakKartuPiutang()"/> 
						</td>
                    </tr>
				</table>
			</div>
			<div id="konten-kartu-piutang" class="monitoring-content" style="background:#FFFFFF;"></div>
		</div>
	</div>
	';
}
?>

<link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script>

var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
var LBL_KEL = '<?php echo $appConfig['LABEL_KELURAHAN']; ?>';
var THN_TAGIHAN = '<?php echo $appConfig['tahun_tagihan']; ?>';
var a = '<?php echo $a; ?>';
var m = '<?php echo $m; ?>';
	
function showKartuPiutang() {
	var nop			= $("#nop").val();
    var kecamatan 	= $("#kecamatan").val();
    var kelurahan 	= $("#kelurahan").val();
	var tipekartu	= $("input:radio[name=tipekartu]:checked").val();
	
	if($('input[name=tipekartu]:checked').length>0){
		if(tipekartu=="pernop" && nop==""){
			alert("NOP tidak boleh kosong.");
		} else if (tipekartu=="kec" && kecamatan==""){
			alert("Kecamatan harus dipilih.");
		} else {
			$("#konten-kartu-piutang").html("<br><img src='image/large-loading.gif'/>");
			$("#konten-kartu-piutang").load("view/PBB/kartu_piutang/svc-kartu-piutang.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m'}"); ?>",
				{nop:nop, kecamatan: kecamatan, kelurahan: kelurahan, tipekartu:tipekartu}, function (response, status, xhr) {
					if (status == "error") {
						var msg = "Sorry but there was an error: ";
						$("#konten-kartu-piutang").html(msg + xhr.status + " " + xhr.statusText);
					}
			});
		}
	} else {
		alert("Silahkan pilih tipe kartu.");
	}
}

function cetakKartuPiutang(){
	var nop			= $("#nop").val();
	var kecamatan 	= $("#kecamatan").val();
	var kelurahan 	= $("#kelurahan").val();
	var tipekartu	= $("input:radio[name=tipekartu]:checked").val();
	
	var params = {a:a, m:m, nop:nop, kecamatan: kecamatan, kelurahan: kelurahan, tipekartu:tipekartu};
	console.log("print ...");
	params = Base64.encode(Ext.encode(params));
	window.open('./view/PBB/kartu_piutang/print-kartu-piutang.php?q='+params, '_newtab');
}

function showKelurahan() {
	var id = $('select#kecamatan').val();
	var request = $.ajax({
		url: "view/PBB/monitoring/svc-kecamatan.php",
		type: "POST",
		data: {id: id, kel: 1},
		dataType: "json",
		success: function (data) {
			var c = data.msg.length;
			var options = '';
			options += '<option value="">Pilih Kelurahan</option>';
			for (var i = 0; i < c; i++) {
				options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
				$("select#kelurahan").html(options);
			}
		}
	});
}

function showKecamatan() {
	var request = $.ajax({
		url: "view/PBB/monitoring/svc-kecamatan.php",
		type: "POST",
		data: {id: "<?php echo  $appConfig['KODE_KOTA'] ?>"},
		dataType: "json",
		success: function (data) {
			var c = data.msg.length;
			var options = '';
			options += '<option value="">Pilih Kecamatan</option>';
			for (var i = 0; i < c; i++) {
				options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
				$("select#kecamatan").html(options);
			}
		}
	});

}

function showKecamatanAll() {
	var request = $.ajax({
		url: "view/PBB/monitoring/svc-kecamatan.php",
		type: "POST",
		data: {id: "<?php echo  $appConfig['KODE_KOTA'] ?>"},
		dataType: "json",
		success: function (data) {
			var c = data.msg.length;
			var options = '';
			options += '<option value="">Pilih Kecamatan</option>';
			for (var i = 0; i < c; i++) {
				options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
			}
			$("select#kecamatan").html(options);
		}
	});

}

$(document).ready(function () {
	showKecamatanAll();

	$("select#kecamatan").change(function () {
		showKelurahan('1');
	})
	
	$('input[type="radio"]').click(function() {
		var radio_value = $(this).val();
		
		if(radio_value == 'pernop'){
			$('#nop').prop('disabled', false);
			$('#nop').focus()
			$('#kecamatan').prop('disabled', true);
			$('#kelurahan').prop('disabled', true);
		} else if (radio_value == 'kec'){
			$('#nop').prop('disabled', true);
			$('#kecamatan').prop('disabled', false);
			$('#kelurahan').prop('disabled', true);
		} else if (radio_value == 'kel'){
			$('#nop').prop('disabled', true);
			$('#kecamatan').prop('disabled', false);
			$('#kelurahan').prop('disabled', false);
		} else if(radio_value == 'inv'){
			$('#nop').prop('disabled', true);
			$('#kecamatan').prop('disabled', false);
			$('#kelurahan').prop('disabled', true);
		}
	   
	});
	
});
</script>