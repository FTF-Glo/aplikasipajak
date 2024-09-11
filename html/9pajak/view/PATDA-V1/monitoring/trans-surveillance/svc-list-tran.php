<?php
session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "trans-surveillance";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-surveillance.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$q = $json->decode(base64_decode($_REQUEST['q']));

$_REQUEST['a'] = $q->a;
$_REQUEST['m'] = $q->m;
$_REQUEST['i'] = 1;


$tran = new TransaksiSurveillance();
?>
<script type="text/javascript">
    var table = "";
    var loadajax = "<img src='image/icon/ajax-loader.gif' style='border-radius:20px'>";
    
    $(document).ready(function(){
		
    });
	
    // Add : parameter
    function get_resume(parameter){
	     var allid = '';
	     $('.loadsign').html("<img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'>");
	     // Add : parameter dateStart, dateEnd, noTran to svc_tran_hotel.php [OST - 14/05/2018]
	     $.ajax({
		     type:'post',
		     data:parameter,
		     url : "view/<?php echo $DIR."/".$modul."/".$submodul?>/svc-trans-surveillance.php?function=get_resume&a=<?php echo $q->a?>&m=<?php echo $q->m?>",
		     dataType:'json',
		     success:function(res){
				
			    $('.loadsign').html('');
			    // Modify : "today" to "yesterday" [OST - 24/04/2018]
			    $('.transaksi_kemarin').html('<a href="javascript:void(0)" onclick="javascript:get_tran(\'yesterday\',\'<?php echo $_REQUEST['i']?>\')">'+res.transaksi_kemarin+'</a>');
			    $('.transaksi_bulan_ini').html('<a href="javascript:void(0)" onclick="javascript:get_tran(\'this_month\',\'<?php echo $_REQUEST['i']?>\')">'+res.transaksi_bulan_ini+'</a>');
			    // Add : element total_transaksi [OST - 14/05/2018]
			    $('.total_transaksi').html(res.total_transaksi);
		     }
	     });
    }

    function reloadAllStatus(){
    	$('#table-status').jtable('load', {
			CPM_NPWPD : $('#CPM_NPWPD-2').val(),
			CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-2').val()
		});
    }
    
    function get_tran(type, id){
		var now = new Date();
		var y = <?php echo date('Y')?>;
		var m = <?php echo date('m')?>;
		var d = <?php echo date('d')?>;
		
		//--- Add : previous day [OST - 24/04/2018]
		var prevY = <?php echo date('Y', (time() - 60 * 60 * 24))?>;
		var prevM = <?php echo date('m', (time() - 60 * 60 * 24))?>;
		var prevD = <?php echo date('d', (time() - 60 * 60 * 24))?>;
		
		var prevDate = new Date(prevY, prevM-1, prevD);
		//---
		
		var currentDate = new Date(y, m, d);
		var prevMonthLastDate = new Date(y, m, 0);
		var prevMonthFirstDate = new Date(y, (m - 1 + 12) % 12, 1);

		var formatDateComponent = function(dateComponent) {
			return (dateComponent < 10 ? '0' : '') + dateComponent;
		};

		var formatDate = function(date) {
			return formatDateComponent(date.getDate()) + '-' + formatDateComponent(date.getMonth() + 1) + '-' + date.getFullYear();
		};
		
		//--- Add & comments : yesterday [OST - 24/04/2018]
		$('#TRAN_DATE1-'+id).val((type === 'yesterday')? formatDate(prevDate) : formatDate(prevMonthFirstDate));
		$('#TRAN_DATE2-'+id).val((type === 'yesterday')? formatDate(prevDate) : formatDate(prevMonthLastDate));
		//---	
		
		//$('#TRAN_DATE1-'+id).val((type === 'today')? formatDate(currentDate) : formatDate(prevMonthFirstDate));
		//$('#TRAN_DATE2-'+id).val((type === 'today')? formatDate(currentDate) : formatDate(prevMonthLastDate));		
		$('#cari-'+id).trigger('click');
	}
</script>
<div class="row">
	<input type="hidden" id="strdeviceid">
	<div class="col-lg-9">
		<div class="panel panel-default">
			<div class="panel-heading">
			</div>
			
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">
						<?php $tran->grid_table_pembanding_detail();?>
					</div>
				</div>
			</div>			
			
			<!--- Add : add body total_transaksi [OST - 14/05/2018]-->
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">
						<div class='filtering'>
							<table width='100%'>
								<tr>
									<td style='background:transparent;padding:2px' align='right'>
										Total Nilai Transaksi : <b class='total_transaksi'><img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'></b>
									</td>
								</tr>
							</table>
						</div>	
					</div>
				</div>
			</div>			
			
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bell fa-fw"></i> Dashboard
			</div>						
			<div class="panel-body">
				<div class="list-group">
				    <a class="list-group-item">
						<!-- Modify : "Transaksi Hari ini" to "Transaksi Kemarin"  [OST - 24/04/2018] -->
					   <i class="fa fa-check text-success"></i> Transaksi Hari Kemarin : <br/><b class='transaksi_kemarin'><img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'></b>
				    </a>
				    <a class="list-group-item">
					   <i class="fa fa-check text-success"></i> Transaksi Bulan ini : <br/><b class='transaksi_bulan_ini'><img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'></b>
				    </a>
				</div>
			</div>
		</div>
	</div>
</div>
