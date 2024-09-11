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
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$q = $json->decode(base64_decode($_REQUEST['q']));
?>

<script type="text/javascript">
    var table = "";
    var loadajax = "<img src='image/icon/ajax-loader.gif' style='border-radius:20px'>";
    var loadAll = 1
    var timepro1= "";
    var timepro2= "";
    
    $(document).ready(function(){
		$('#table-alarm').jtable({
			title: '',
			columnResizable : false,
			columnSelectable : false,
			paging: true,
			pageSize: 100, /*{$this->pageSize},*/
			sorting: false,
			selecting: false,
			actions: {
				listAction: "view/<?php echo $DIR."/".$modul."/".$submodul?>/svc-deviceid.php?function=getDeviceAlarm&a=<?php echo $q->a?>",
			},
			fields: {
				NO : {title: 'No',width: '3%'},
				NPWPD: {title: 'NPWPD',width: '5%'},
				DEVICEID: {title: 'Device Id',width: '5%'},
				COMPANY: {title: 'Nama OP',width: '10%'},
				ALARM: {title: 'Alarm',width: '10%'},
				LAST_CHUNKS: {title: 'Last Chunks',width: '10%'},
				LAST_FILES: {title: 'Last Files',width: '5%'},
				FILES_PREV: {title: 'Files Prev',width: '10%'},
				LAST_PARSED: {title: 'Last Parsed',width: '10%'},
				INDIKATOR: {title: 'Indikator',width: '5%'}
			}
		});
		
		$('#table-alarm').jtable('load',{},function(){
			if(loadAll === 1){
				totalAlarm();
			}
			loadAll=0;
		});
		$('tr.jtable-no-data-row td').html('Semua device berjalan baik!');
    });

	/*setInterval( function () {
		table_load();
		listAlarm();
		totalAlarm();
	}, 100000 );*/


    function totalAlarm(){	
		$(".alarm-label").html(loadajax);
		$(".device-label").html(loadajax);
		//$("#alarm-list").html("<center>"+loadajax+"</center>");

        $.ajax({
            type: "post",
            data: "a=<?php echo $q->a ?>&function=getTotalAlarm",
            url: "view/<?php echo $DIR ?>/monitoring/dashboard-tapbox/svc-deviceid.php",
            dataType: "json",
            async: true,
            success: function (res) {				
				$(".alarm-label").html(res.totalAlarm);
				$(".device-label").html(res.totalDevice);
				//listAlarm(res.listAlarm);
            }
        });	
    }	

    function filePreview(file){			
		$("#device-modal").modal({backdrop: false});
		$(".modal-body").html(loadajax);
        $(".modal-title").html("File Preview");
        $.ajax({
            type: "post",
            data: "a=<?php echo $q->a ?>&function=filePreview&file="+file,
            url: "view/<?php echo $DIR ?>/monitoring/dashboard-tapbox/svc-deviceid.php",
            dataType: "json",
            async: true,
            success: function (res) {
				$(".modal-body").html(res.data);
            }
        });	
    }	


    function table_load(str){
		$('#table-alarm').jtable('load',{},function(){
			if(loadAll === 1){
				totalAlarm();
			}
			loadAll=0;
		});
    }

    function reloadAllAlarm(){
		timepro1 = new Date();
		loadAll = 1;
		table_load('');
    }

    function openModal(data) {
        $("#device-modal").modal({backdrop: false});
        $(".modal-title").html(data.CompanyName);
		
		for(var x in data){
			data[x] = (data[x]===null) ? "-" : data[x];
		}
		
        var table = "<table class='table table-bordered table-hover'><tr><th>Device ID</th><td>" + data.DEVICEID + "</td></tr>";
		table += "<tr><th>Company Name</th><td>" + data.COMPANY + "</td></tr>";
        table += "<tr><th>NPWPD</th><td>" + data.NPWPD + "</td></tr><tr><th>Alamat</th><td>" + data.ADDRESS + "</td></tr><tr><th>Kontak Person</th>";
        table += "<td>" + data.CONTACT + "</td></tr><tr><th>No. Telepon</th><td>" + data.PHONE + "</td></tr></table><!--<div id='map-canvas'></div>-->";
        $(".modal-body").html(table);

        /*var map;
        map = new google.maps.Map(document.getElementById('map-canvas'), {
            zoom: 8,
            center: {lat: -34.397, lng: 150.644}
        });*/
        //Latitude: "6.13.54.69S"
        //Longitude: "106.50.50.33E"
    }
    
    function listAlarm(res){
		var total=0;
		var html = "";
		$.each( res, function( key, val ) {
			html += '<a href="javascript:void(0)" class="list-group-item"><i class="fa fa-warning fa-fw"></i> '+val.alarm+'<span class="badge">'+val.total+'</span></a>';
			total++;
		});
		if(total==0){
			html += '<a href="javascript:void(0)" class="list-group-item"><i class="fa fa-check fa-fw"></i> No Power Cable Unplugged</a>';
		}
		$("#alarm-list").html(html);
		
			
		timepro2 = new Date();
		var timepro1ms = timepro1.getTime(timepro1);
		var timepro2ms = timepro2.getTime(timepro2);
		var diff= timepro2ms-timepro1ms;
		var s=(diff/1000)%60;
		$("#process1").html("Query time : " +s.toFixed(2)+ " s");
    }
    
    function listDevice(){
		var html = "<div class=\"table-responsive\"><div id=\"table-device\" style=\"width:100%;\"></div></div>";
		$(".modal-body").html(html);
		$("#device-modal").modal({backdrop: false});
        $(".modal-title").html("List Device Id Terpasang");
        
        $('#table-device').jtable({
			title: '',
			columnResizable : false,
			columnSelectable : false,
			paging: false,
			pageSize: 50,
			sorting: false,
			selecting: true,
			actions: {
				listAction: "view/<?php echo $DIR."/".$modul."/".$submodul?>/svc-deviceid.php?function=getDevice&a=<?php echo $q->a?>&strdeviceid="+$("#strdeviceid").val(),
			},
			fields: {
				NO : {title: 'No',width: '3%'},
				DEVICEID: {title: 'Device Id',width: '10%'},
				COMPANYNAME: {title: 'Nama Perusahaan',width: '10%'},
				ADDRESS: {title: 'Alamat',width: '10%'}
			}
		});		
		$('#table-device').jtable('load',{});
	}
</script>
<div class="row">
	<input type="hidden" id="strdeviceid">
	<div class="col-lg-9">
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-tasks fa-fw"></i> Device ID Bermasalah</span>
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
					          <li><a href="javascript:void(0)" onclick="javascript:reloadAllAlarm()" style="width:200px;">Refresh</a></li>
				          </ul>
			          </div>
		          </div> 
			</div>
			
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">
						<div class="table-responsive">
							<div id="table-alarm" style="width:100%;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="row">
			<div class="col-lg-6">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<a href="javascript:void(0)" onclick="javascript:listDevice()">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-tasks fa-3x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge device-label"></div>
									<div>Jumlah Device ID</div>
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="panel panel-red">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-3">
								<i class="fa fa-warning fa-3x"></i>
							</div>
							<div class="col-xs-9 text-right">
								<div class="huge alarm-label"></div>
								<div>Jumlah Alarm</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bell fa-fw"></i> Keterangan
			</div>						
			<div class="panel-body">
				<div class="list-group">
				    <a class="list-group-item">
					   <i class="fa fa-clock-o text-danger"></i> Parsing data terakhir lebih dari 24 jam yang lalu.
				    </a>
				    <a class="list-group-item">
					   <i class="fa fa-clock-o text-primary"></i> Parsing terakhir kurang dari 24 jam yang lalu.
				    </a>
				</div>
			</div>
			<div class="panel-heading">
				<i class="fa fa-bell fa-fw"></i> Indikator
			</div>						
			<div class="panel-body">
				<div class="list-group">
				    <a class="list-group-item">
					   <i class="fa fa-circle fa-2x text-merah text-danger"></i> Menandakan Kondisi Alat Mati.
				    </a>
				    <a class="list-group-item">
					   <i class="fa fa-circle fa-2x text-hijau text-success"></i> Menandakan Kondisi Alat Menyala.
				    </a>
				</div>
			</div>
		</div>
			
		
	</div>
</div>
<div class="modal fade" id="device-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body">
				<p></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger btn-xs"
					data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
