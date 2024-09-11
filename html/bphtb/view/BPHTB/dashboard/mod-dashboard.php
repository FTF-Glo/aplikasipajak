<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dashboard', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
// var_dump($_SESSION);
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css?0002\" type=\"text/css\">\n";
// echo "<link rel=\"stylesheet\" href=\"view/BPHTB/dashboard/adminlte.min.css\" type=\"text/css\">\n";
echo "<link rel=\"stylesheet\" href=\"view/BPHTB/dashboard/dashboardv2.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo "<script language=\"javascript\" src=\"inc/js/jquery-1.3.2.min.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\" type=\"text/javascript\"></script>\n";

echo "<script language=\"javascript\" type=\"text/javascript\" src=\"inc/js/highcharts.js\"></script>\n";
echo "<script language=\"javascript\">var ap='".$_REQUEST['a']."';</script>\n";

?>

<style type="text/css">
	.card {
	    box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
	    margin-bottom: 1rem;
	}
	.card {
		position: relative;
		display: -ms-flexbox;
		display: flex;
		-ms-flex-direction: column;
		flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border: 0 solid rgba(0,0,0,.125);
		border-radius: .25rem;
	}
	.card-success:not(.card-outline) > .card-header, .card-success:not(.card-outline) > .card-header a {
	    color: #fff;
	}
	.card-success:not(.card-outline) > .card-header {
	    /* background-color: #FF0000; */
	    background-color: #28a745;
	}
	.card-header {
	    background-color: transparent;
	    border-bottom: 1px solid rgba(0,0,0,.125);
	    padding: .75rem 1.25rem;
	    position: relative;
	    border-top-left-radius: .25rem;
	    border-top-right-radius: .25rem;
	}
	.card-header {
	    padding: .75rem 1.25rem;
	    margin-bottom: 0;
	    background-color: rgba(0,0,0,.03);
	    border-bottom: 0 solid rgba(0,0,0,.125);
	}
	.card {
	    word-wrap: break-word;
	}
	.card-title {
	    float: left;
	    font-size: 1.1rem;
	    font-weight: 400;
	    margin: 0;
	}
</style>
     <!-- <div id="summary" style="margin-right:20px; display:block; margin-bottom:10px; font-weight:bold;">Total Transaksi (<?php echo date("d-m-Y")?>) : <span id="tot-trans">
    </span> | Total Penerimaan (<?php echo date("d-m-Y")?>) : <span id="tot-trims"></span></div> -->
    <div class="row">
    	<div class="col-lg-3 col-6">
    		<div class="card">
	    		<select class="form-control" id="txt-thn">
	    			<option value="-" disabled>Pilih Tahun</option>
	    			<?php 
	    				$oldyear = 2017;
	    				$yearnow = date("Y");
	    				for ($i=0; $oldyear <= $yearnow ; $i++) { 
	    					if ($oldyear == $yearnow) {
	    						echo "<option value=\"$oldyear\" selected>".$oldyear."</option>";
	    					}
	    					else{
	    						echo "<option value=\"$oldyear\">".$oldyear."</option>";
	    					}
	    					$oldyear++;
	    				}
	    			 ?>
	    		</select>
    		</div>
    	</div>
    </div>

    <div class="row">
		<div class="col-lg-3 col-6">
		<!-- small box -->
			<div class="small-box bg-info">
				<div class="inner">
					<h3 id="tot-trims">0</h3>
					<p>Total Transaksi hari ini</p>
				</div>
				&nbsp;
			</div>
		</div>
		<div class="col-lg-3 col-6">
		<!-- small box -->
			<div class="small-box bg-success">
				<div class="inner">
					<h3 id="tot-trans">0</h3>
					<p>Total Penerimaan hari ini</p>
				</div>
				&nbsp;
			</div>
		</div>
		<div class="col-lg-3 col-6">
			<!-- small box -->
			<div class="small-box bg-warning">
				<div class="inner">
					<h3 id="sdh_bayar">0</h3>
					<p>Jumlah WP sudah bayar</p>
				</div>
				&nbsp;

				<!-- <div class="icon">
					<i class="ion ion-person-add"></i>
				</div> -->
				<!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
			</div>
		</div>
		<div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3 id="blm_byr">0</h3>
                <p>Jumlah WP belum bayar</p>
              </div>
              <div class="icon">
			  &nbsp;
              <!-- <i class="ion ion-pie-graph"></i>
              </div> -->
              <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
            </div>
        </div>
    </div>
    <div class="row">
		<div class="col-lg-6 col-6">
			<div class="card card-success">
			  <div class="card-header">
			    <h3 class="card-title">Penerimaan BPHTB bulan <?= date('M') ?></h3>
			  </div>
			  <div class="card-body">
			    <div class="chart" id="barChartDiv">
			      <canvas id="barChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
			    </div>
			  </div>
			  <!-- /.card-body -->
			</div>
		</div>
		<div class="col-lg-6 col-6">
			<div class="card card-success">
			  <div class="card-header">
			    <h3 class="card-title">Penerimaan BPHTB tahun <span id="bphtbThn"></span></h3>
			  </div>
			  <div class="card-body">
			    <div class="chart" id="thnChartDiv">
			      <canvas id="thnChart" style="min-height: 290px; height: 290px; max-height: 290px; max-width: 100%;"></canvas>
			    </div>
			  </div>
			</div>
			<div class="card bg-danger">
				<div class="card-header" style="text-align:center">
				<h3 id="Tahunjumlah" style="width:unset;margin-bottom:unset">Jumlah :</h3>
			  </div>
			</div>
		</div>
	</div>
<!-- <div id="cont" style="width: 580px; height: 300px; float:left; margin-right:10px;display:block; margin-bottom:10px"></div>
<div id="cont2" style="width: 580px; height: 320px;float:left;display:block; margin-right:10px "></div> -->
<script src="view/BPHTB/dashboard/Chart.min.js"></script>

<script type="text/javascript" language="javascript">

	function number_format(a, b, c, d) {
		a = Math.round(a * Math.pow(10, b)) / Math.pow(10, b);
		e = a + '';
		f = e.split('.');
		if (!f[0]) {
			f[0] = '0';
		}
		if (!f[1]) {
			f[1] = '';
		}
		if (f[1].length < b) {
			g = f[1];
			for (i=f[1].length + 1; i <= b; i++) {
				g += '0';
			}
			f[1] = g;
		}
		if(d != '' && f[0].length > 3) {
			h = f[0];
			f[0] = '';
			for(j = 3; j < h.length; j+=3) {
				i = h.slice(h.length - j, h.length - j + 3);
				f[0] = d + i +  f[0] + '';
			}
			j = h.substr(0, (h.length % 3 == 0) ? 3 : (h.length % 3));
			f[0] = j + f[0];
		}
		c = (b <= 0) ? '' : c;
		return f[0] + c + f[1];
	}

	function removeNode(obj) {
		if (obj.hasChildNodes()) {
			while ( obj.childNodes.length >= 1 )
			{
				obj.removeChild( obj.firstChild );       
			} 
		}
	}
	$(document).ready(function(){
		$("#txt-thn").change(function(){
			getSummary();
		})
		function changeSummary(sx,sy,jml_pembayar,trs_hari_ini,per,sdh_bayar,blm_byr,txthn) {
			var tot = document.getElementById('tot-trans');
			var tottr = document.getElementById('tot-trims');
			var txtsdh_bayar = document.getElementById('sdh_bayar');
			var txtblm_byr = document.getElementById('blm_byr');
			var bphtbThn = document.getElementById('bphtbThn');

			var t1 = document.createTextNode(number_format(jml_pembayar, 0, '.', ','));
			var t2old = number_format(trs_hari_ini, 2, '.', ',');
			t2 = t2old.split(",");
			var t2new = '';
			for(i=0;i < t2.length; i++){
			  	if(i==0){
			  		if (t2[i]==0) {
				    	t2new += 'Rp '+t2[i];
			  		}
			  		else{
			    		t2new += 'Rp '+t2[i]+',';
			  		}
			    }
			    else{
			    	if (i==1) {
			    		t2new += '<sup style="font-size:20px">'+t2[i]+'</sup>';
			    	}
			    	else{
			    		t2new += '<sup style="font-size:20px">,'+t2[i]+'</sup>';
			    	}
			    }
			}
			// t2new = document.createTextNode(t2new);
			removeNode(tot);
			// removeNode(tottr);
			tot.appendChild(t1);
			tottr.innerHTML = t2new;
			txtsdh_bayar.innerHTML = sdh_bayar;
			txtblm_byr.innerHTML = blm_byr;
			bphtbThn.innerHTML = txthn;


			//////////////////////////////////////////////////////////////
			//////////////////////// CHART //////////////////////////////
			////////////////////////////////////////////////////////////
			var labels_date = [],
			getlengthdate = getDaysInMonth(<?= date('m')?>-1, <?= date('Y')?>);

			for (var i = 1; i <= getlengthdate.length; i++) {
				labels_date.push(i)
			}
		    var areaChartData = {
				labels  : labels_date,
				datasets: [
					{
						label               : 'Perhari',
						backgroundColor     : 'rgba(60,141,188,0.9)',
						borderColor         : 'rgba(60,141,188,0.8)',
						pointRadius         : false,
						pointColor          : '#3b8bba',
						pointStrokeColor    : 'rgba(60,141,188,1)',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(60,141,188,1)',
						data                : sx
					},
				]
		    }
			$('#barChart').remove();
			$('#barChartDiv').append('<canvas id="barChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>');
		    var barChartCanvas = $('#barChart').get(0).getContext('2d')
		    var barChartData = $.extend(true, {}, areaChartData)
		    var temp0 = areaChartData.datasets[0]
		    // var temp1 = areaChartData.datasets[1]
		    // barChartData.datasets[0] = temp1
		    barChartData.datasets[0] = temp0

		    var barChartOptions = {
				responsive              : true,
				maintainAspectRatio     : false,
				datasetFill             : false
		    }

		    new Chart(barChartCanvas, {
				type: 'bar',
				data: barChartData,
				options: barChartOptions
		    })


		    var thnareaChartData = {
				labels  : ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
				datasets: [
					{
						label               : 'Perbulan',
						backgroundColor     : 'rgba(60,141,188,0.9)',
						borderColor         : 'rgba(60,141,188,0.8)',
						pointRadius         : false,
						pointColor          : '#3b8bba',
						pointStrokeColor    : 'rgba(60,141,188,1)',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(60,141,188,1)',
						data                : sy
					}
				]
		    }
			$('#thnChart').remove();
			$('#thnChartDiv').append('<canvas id="thnChart" style="min-height: 290px; height: 290px; max-height: 290px; max-width: 100%;"></canvas>');
		    var thnChartCanvas = $('#thnChart').get(0).getContext('2d')
		    var thnChartData = $.extend(true, {}, thnareaChartData)
		    var temp0 = thnareaChartData.datasets[0]
		    thnChartData.datasets[0] = temp0

		    var thnChartOptions = {
		      responsive              : true,
		      maintainAspectRatio     : false,
		      datasetFill             : false
		    }

		    new Chart(thnChartCanvas, {
				type: 'bar',
				data: thnChartData,
				options: thnChartOptions
		    })
		}

		function numberWithCommas(x) {
			return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
		}

		function getSummary() {
			var TxtThn = $("#txt-thn").val();
			$.ajax({
			  url: "./view/BPHTB/dashboard/svc-get-data.php",
			  datatype:"json",
			  type: "POST",
			  data: {'a':ap,'month':'<?php echo date("n")?>','year':TxtThn},
			  success: function(data){
			  	$("#TxtThn").removeAttr('disabled');
				var obj = JSON.parse(data);
				if(obj.success){
					changeSummary(
									obj.data.trs_bulan_ini,
									obj.data.trs_tahun_ini,
									obj.data.jml_pembayar,
									obj.data.trs_hari_ini,
									obj.data.per,
									obj.data.total_sdh_bayar,
									obj.data.total_blm_bayar,
									TxtThn
								);
					// add by d3Di
					$("#Tahunjumlah").html('Jumlah : '+ numberWithCommas(obj.data.trs_collectible));
				}
			  },
			  beforeSend: function(){
			  	$("#TxtThn").attr('disabled');
			  }
			});
		}
		
		
		getSummary();
		setInterval(function() {
		   getSummary();
		}, 6000*3);  
	});

	function getDaysInMonth(month, year) {
	  var date = new Date(year, month, 1);
	  var days = [];
	  while (date.getMonth() === month) {
	    days.push(new Date(date).getUTCDate());
	    date.setDate(date.getDate() + 1);
	  }
	  return days;
	}
</script>