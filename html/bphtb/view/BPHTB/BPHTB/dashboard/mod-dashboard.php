<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dashboard', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");


error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css?0002\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/js/jquery-1.3.2.min.js\" type=\"text/javascript\"></script>\n";

echo "<script language=\"javascript\" type=\"text/javascript\" src=\"inc/js/highcharts.js\"></script>\n";
echo "<script language=\"javascript\">var ap='".$_REQUEST['a']."';</script>\n";

function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getConfigValue ($id,$key) {
	global $data,$appDbLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($appDbLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($appDbLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getDocument($month,&$dat,&$mxval) {
	global $appDbLink,$json;

	$a = $_REQUEST['a'];
	$DbName = getConfigValue($a,'BPHTBDBNAME');
	$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
	$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
	$DbTable = getConfigValue($a,'BPHTBTABLE');
	$DbUser = getConfigValue($a,'BPHTBUSERNAME');
	$tw = getConfigValue($a,'TENGGAT_WAKTU');
	
	$where = " WHERE PAYMENT_FLAG = 1 AND MONTH(saved_Date) = ".$month;
	
	$iErrCode=0;
	
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	
	$query = "SELECT payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY day(DATE(payment_paid))  ORDER BY payment_paid "; 
	// print_r($query);
	$res = mysqli_query($LDBLink, $query);
	if ( $res === false ){
		 print_r($query.mysqli_error($LDBLink));
		 return false; 
	}
	
	$d =  $json->decode(mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	$ss = true;
	$arrDat=array();
	$arrVal=array();
	for ($i=0;$i<count($data->data);$i++) {
/*		$data->data[$i]->op_nomor;
		$data->data[$i]->wp_nama;
		$data->data[$i]->wp_alamat;
		getAUTHOR($data->data[$i]->op_nomor);*/
		$arrDat[$i]["bayar"] = $data->data[$i]->sum_bphtb_dibayar;
		$arrDat[$i]["tanggal"] = $data->data[$i]->payment_paid;
		$arrVal[$i] = $data->data[$i]->sum_bphtb_dibayar;
		
	}
	
	if($arrVal) {
		$mx = max($arrVal);
		$mxval = $mx;
	} else {
		$mxval = 0;
	}
	$dat = $arrDat;
	
	return true;
}

?>

  <script type="text/javascript" language="javascript">
  

<?php
if (getDocument(date("n"),$dat,$mxval)) {
	$var =  "s1 = [";
	
	$cdat = count($dat);
	$num = cal_days_in_month(CAL_GREGORIAN, date("n"), date("Y"));
	$mon = array();
	//$max = max($dat[]["bayar"]);
	//print_r($max);
	$cm = strlen($mxval)-2;
	$pm = pow(10,$cm);

	for($c=0;$c<$num;$c++){
		 $trs = 0;
		 $mon[$c] = 0;
		 for ($f=0;$f<$cdat;$f++){
			 $m = floatval(substr($dat[$f]["tanggal"],8,2));
			 if ($m ==($c+1)) $mon[$c] = round(floatval($dat[$f]["bayar"])/$pm,2);
		 }
	}
	$imp = implode(",",$mon);
	$var .= $imp . "];";
	echo $var;
}
?>
//s1 = [4, 3, 9, 16, 12, 8];
//s2 = [null, null, null, 3, 7, 6];

function daysInMonth(iMonth, iYear)
{
	return 32 - new Date(iYear, iMonth, 32).getDate();
}
var month = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var d = new Date();
var days = daysInMonth(d.getMonth(),d.getYear());
var ticks = Array();
for (var i=0;i<days;i++) {
	ticks[i] = i+1;
}

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

var chart;
var chart1;
var options = {
		chart: {
			renderTo: 'cont',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Penerimaan BPHTB bulan ' + month[d.getMonth()]
		},
		subtitle: {
			text: '<?php echo getConfigValue($a,'NAMA_DINAS')?>'
		},
		xAxis: {
			categories:ticks,
			title: {
				text: null
			}
		},
		tooltip: {
			formatter: function() {
				return ''+
					 this.series.name +' tanggal '+ this.x +' : '+number_format((this.y), 0, '.', ',')+'';
			}
		},
		plotOptions: {
			column: {
				dataLabels: {
					enabled: false,
					rotation: -90,
					align: 'right',
            		x: 0,
					y: -35,
					color:'#000000',
					formatter: function() {
					   var jt  = this.y /1000000000;
						var sjt = number_format(jt, 2	, '.', ',')+" M";
					   return sjt;
					}
				}
			}
		},
		legend: {
			layout: 'horizontal',
			align: 'right',
			verticalAlign: 'top',
			floating: true,
			borderWidth: 1,
			backgroundColor: '#FFFFFF',
			shadow: true,
			y:30
		},
		credits: {
			enabled: false
		}
		
	};
	


var options2 = {
		chart: {
			renderTo: 'cont2',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Penerimaan BPHTB Tahun <?php echo date("Y")?>' 
		},
		subtitle: {
			text: '<?php echo getConfigValue($a,'NAMA_DINAS')?>'
		},
		
		xAxis: {
			categories:month,
			title: {
				text: null
			},
			labels: {
				rotation: -30,
				align: 'right',
				style: {
					font: 'normal 10px Verdana, sans-serif'
				}
			 }
		},
		tooltip: {
			formatter: function() {
				return ''+
					 this.series.name +' '+ this.x +' : '+number_format((this.y), 0, '.', ',')+'';
			}
		},
		plotOptions: {
			column: {
				dataLabels: {
					enabled: false,
					rotation: -90,
					align: 'right',
            		x: -5,
					y: -35,
					color:'#000000',
					formatter: function() {
						var jt  = this.y /1000000000;
						var sjt = number_format(jt, 2	, '.', ',')+" M";
					   return sjt;
					}
				}
			}
		},
		legend: {
			layout: 'horizontal',
			align: 'right',
			verticalAlign: 'top',
			floating: true,
			borderWidth: 1,
			backgroundColor: '#FFFFFF',
			shadow: true,
			y:30
		},
		credits: {
			enabled: false
		}
		
	};

function removeNode(obj) {
	if (obj.hasChildNodes()) {
		while ( obj.childNodes.length >= 1 )
		{
			obj.removeChild( obj.firstChild );       
		} 
	}
}

$(document).ready(function() {
	var dm = new Date();
	function changeSummary(sx,sy,jml_pembayar,trs_hari_ini,per) {
		var tot = document.getElementById('tot-trans');
		var tottr = document.getElementById('tot-trims');
		var t1 = document.createTextNode(number_format(jml_pembayar, 0, '.', ','));
		var t2 = document.createTextNode("Rp. "+number_format(trs_hari_ini, 2, '.', ','));
		removeNode(tot);
		removeNode(tottr);
		tot.appendChild(t1);
		tottr.appendChild(t2);
		
		options.series=[{
			//name: 'Penerimaan bulan ' + month[dm.getMonth()],
			name: 'Perhari',
			color:'#ddd700',
			data: sx
		}]
		
		options.yAxis= {
			min: 0,
			title: {
				//text: '1:'+number_format(per, 0, '.', ','),
				text: '',
				align: 'high'
			},
			labels:{
				formatter : function() {
						var jt  = this.value /1000000000;
						var sjt = number_format(jt, 2	, '.', ',')+" Miliar";
						return sjt;
					}			
			}
		};
		
		chart = new Highcharts.Chart(options);
		options2.series=[{
			color:'#fed700',
			//name: 'Penerimaan tahun ',
			name: 'Perbulan',
			data: sy
		}]
		
		options2.yAxis= {
			min: 0,
			title: {
				//text: '1:'+number_format(per, 0, '.', ','),
				text: '',
				align: 'high'
			},
			labels:{
				formatter : function() {
						var jt = this.value /1000000000;
						var sjt = number_format(jt, 2, '.', ',')+" Miliar";
						return sjt;
					}			
			}
		};
		chart2 = new Highcharts.Chart(options2);
	}
	
	function getSummary() {
		$.ajax({
		  url: "./view/BPHTB/dashboard/svc-get-data.php",
		  datatype:"json",
		  type: "POST",
		  data: {'a':ap,'month':'<?php echo date("n")?>','year':'<?php echo date("Y")?>'},
		  success: function(data){
			var obj = JSON.parse(data);
			if(obj.success){
				changeSummary(obj.data.trs_bulan_ini,obj.data.trs_tahun_ini,obj.data.jml_pembayar,obj.data.trs_hari_ini,obj.data.per) ;
			}
		  }
		});
	}
	
	
	getSummary();
	setInterval(function() {
	   getSummary();
	}, 6000*3);
	
	
	
});
  </script>
  <br>
    <div id="summary" style="margin-right:20px; display:block; margin-bottom:10px; font-weight:bold;">Total Transaksi (<?php echo date("d-m-Y")?>) : <span id="tot-trans">
    </span> | Total Penerimaan (<?php echo date("d-m-Y")?>) : <span id="tot-trims"></span></div>
	<div id="cont" style="width: 580px; height: 300px; float:left; margin-right:10px;display:block; margin-bottom:10px"></div>
	<div id="cont2" style="width: 580px; height: 320px;float:left;display:block; margin-right:10px "></div>
   