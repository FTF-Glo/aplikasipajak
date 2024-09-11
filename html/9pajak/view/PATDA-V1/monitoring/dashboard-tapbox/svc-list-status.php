<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "dashboard-tapbox";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul. DIRECTORY_SEPARATOR . $submodul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-tapbox.php");
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$q = $json->decode(base64_decode($_REQUEST['q']));

?>
<script type="text/javascript">
    var table = "";
    var loadajax = "<img src='image/icon/ajax-loader.gif' style='border-radius:20px'>";
    
    $(document).ready(function(){
		$('#table-status').jtable({
			title: '',
			columnResizable : false,
			columnSelectable : false,
			paging: true,
			pageSize: 50, /*{$this->pageSize},*/
			sorting: false,
			selecting: false,
			actions: {
				listAction: "view/<?php echo $DIR."/".$modul."/".$submodul?>/svc-deviceid.php?function=getDeviceDashboard&a=<?php echo $q->a?>",
			},
			fields: {
				NO : {title: 'No',width: '3%'},
				CPM_NPWPD: {title: 'NPWPD',width: '10%'},
				CPM_NAMA_OP: {title: 'OP Nama',width: '10%'},
				CPM_DEVICE_ID: {title: 'Device ID',width: '10%'},
				CPM_PERHARI: {title: 'Transaksi Hari ini',width: '10%'},
				CPM_PERBULAN: {title: 'Transaksi Bulan ini',width: '10%'},
			},recordsLoaded: function(event, data) {
				get_status_device();
			}
		});
		
		$('#table-status').jtable('load',{
			CPM_NPWPD : $('#CPM_NPWPD-2').val(),
			CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-2').val()
		});
		$('tr.jtable-no-data-row td').html('data tidak tersedia!');
		
		$('#cari-table-status').click(function (e) {
			e.preventDefault();
			reloadAllStatus();
		});
    });
    
    setInterval( function () {
		get_status_device();
	}, 60000 );
	
	
    function get_status_device(){
	     var allid = '';
	     $('.loadsign').html("<img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'>");
	     $('.deviceidstr').each(function(){
		     allid += $(this).attr('deviceid')+'|';
	     })
	     $.ajax({
		     type:'post',
		     data:{allid:allid},
		     url : "view/<?php echo $DIR."/".$modul."/".$submodul?>/svc-deviceid.php?function=getDeviceStatus&a=<?php echo $q->a?>&m=<?php echo $q->m?>",
		     dataType:'json',
		     success:function(res){
			    $('.loadsign').html('');
			    var data = res.data;
				for(var x in data){
				     $('.id_'+x).html(data[x]);
				}	
				$('.warningMinutes').html(res.warningMinutes);
		     }
	     });
    }

    function reloadAllStatus(){
    	$('#table-status').jtable('load', {
			CPM_NPWPD : $('#CPM_NPWPD-2').val(),
			CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-2').val()
		});
    }
</script>
<div class="row">
	<input type="hidden" id="strdeviceid">
	<div class="col-lg-9">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-tasks fa-fw"></i> Dashboard Device</span>
				<div class="pull-right">
					<span id="process1" class="label label-primary"></span>
				</div>
				<div class="pull-right" style="margin:3px 20px 3px 0px">
			          <div class="btn-group">
				          <button type="button"
					          class="btn btn-default btn-xs dropdown-toggle"
					          data-toggle="dropdown"> Actions <span class="caret"></span>
				          </button>
				          <ul class="dropdown-menu pull-right" role="menu">
					          <li><a href="javascript:void(0)" onclick="javascript:reloadAllStatus()" style="width:200px;">Refresh</a></li>
				          </ul>
			          </div>
		          </div> 
			</div>
			
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">
						<?php
						$pajak = new TapboxPajak();
						echo $pajak->filtering_device_dashboard(2);
						?>
						<div class="table-responsive">
							<div id="table-status" style="width:100%;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bell fa-fw"></i> Keterangan Device Id
			</div>						
			<div class="panel-body">
				<div class="list-group">
				    <a class="list-group-item">
					   <i class="fa fa-warning text-danger"></i> Device tidak mengirimkan transaksi selama lebih dari <span class='warningMinutes'><img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'></span>!
				    </a>
				    <a class="list-group-item">
					   <i class="fa fa-check text-success"></i> Device mengirimkan transaksi dalam <span class='warningMinutes'><img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'></span> terakhir.
				    </a>
				    <a class="list-group-item">
					   <i class="fa fa-remove text-primary"></i> Device tidak terdaftar.
				    </a>
				</div>
			</div>
		</div>
	</div>
</div>
