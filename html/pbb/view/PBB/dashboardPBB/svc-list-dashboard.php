<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dashboard', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");
require_once($sRootPath . "inc/payment/sayit.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
class dashboard  extends modBPHTBApprover{
   //private $defaultPage =1;
   //private $totalRows=0;
   public $perpage=10;
   //public $whr=null;
    public function __construct($userGroup, $user) {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }
    
    function getConfigValue($id, $key) {
        global $DBLink;
        $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CTR_AC_VALUE'];
        }
    }
    
    function getTotalRows($query,$LDBLink) {
       
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_num_rows($res);
        return $row;
    }

    function mysql2json($mysql_result, $name) {
        $json = "{\n'$name': [\n";
        $field_names = array();
        $fields = mysqli_num_fields($mysql_result);
        for ($x = 0; $x < $fields; $x++) {
            $field_name = mysqli_fetch_field($mysql_result);
            if ($field_name) {
                $field_names[$x] = $field_name->name;
            }
        }
        $rows = mysqli_num_rows($mysql_result);
        for ($x = 0; $x < $rows; $x++) {
            $row = mysqli_fetch_array($mysql_result);
            $json.="{\n";
            for ($y = 0; $y < count($field_names); $y++) {
                $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
                if ($y == count($field_names) - 1) {
                    $json.="\n";
                } else {
                    $json.=",\n";
                }
            }
            if ($x == $rows - 1) {
                $json.="\n}\n";
            } else {
                $json.="\n},\n";
            }
        }
        $json.="]\n}";
        return($json);
    }

function getDocument(&$dat,&$mxval,&$html) {
        global $appDbLink,$json,$a,$m,$s,$page,$data;
        $day = date("d");
	$month = date("n");
        $year  = date("Y");
        $bulan =null;
        $tahun = null;
	$DbName = $this->getConfigValue($a,'BPHTBDBNAME');
	$DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
	$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
	$DbTable = $this->getConfigValue($a,'BPHTBTABLE');
	$DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
	$tw = $this->getConfigValue($a,'TENGGAT_WAKTU');
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        switch($s){
           case 5 : 
                 $where = " WHERE PAYMENT_FLAG = 1 AND MONTH(payment_paid) = ".$month;
                 
                 break;
           case 6 : 
//                 for($i=($year-3);$i<=$year;$i++){
//                   if($i!=$year){                       
//                     $tahun .= $i.',';
//                   }else{
//                     $tahun .= $i; 
//                   }  
//                 } 
                 $where = " WHERE PAYMENT_FLAG = 1 AND YEAR(payment_paid) =".$year;
                 
                 break;  
           case 7 :
                for($i=($year-5);$i<=$year;$i++){
                   if($i!=$year){                       
                     $tahun .= $i.',';
                   }else{
                     $tahun .= $i; 
                   }  
                 } 
	         $where = " WHERE PAYMENT_FLAG = 1 AND YEAR(payment_paid) in (".$tahun.")";
                 
                 break;
           
        }   
	
	$iErrCode=0;
	//print_r($data);
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
        
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	 $query = "SELECT payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY day(DATE(payment_paid)) ORDER BY payment_paid 
                    LIMIT " . $hal . "," . $this->perpage; 
         $qry = "SELECT payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY day(DATE(payment_paid)) ORDER BY payment_paid "; 
         
	$res = mysqli_query($LDBLink, $query);
        
	if ( $res === false ){
		 print_r($query.mysqli_error($DBLink));
		 return false; 
	}
        $query = "SELECT sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where ";
       
        $res2 = mysqli_query($LDBLink, $query);
        
	if ( $res2 === false ){
		 print_r($query.mysqli_error($DBLink));
		 return false; 
	}
        $jumlah = mysqli_fetch_array($res2);
	$this->totalRows = $this->getTotalRows($qry,$LDBLink);
	$d =  $json->decode($this->mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	$params = "a=".$a."&m=".$m; 
	$ss = true;
	$arrDat=array();
	$arrVal=0;
        $arrMax=array();
	for ($i=0;$i<count($data->data);$i++) {
                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
/*		$data->data[$i]->op_nomor;
		$data->data[$i]->wp_nama;
		$data->data[$i]->wp_alamat;
		getAUTHOR($data->data[$i]->op_nomor);*/
		$arrDat[$i]["bayar"] = $data->data[$i]->sum_bphtb_dibayar;
		$arrDat[$i]["tanggal"] = $data->data[$i]->payment_paid;
                $arrMax[$i]= $data->data[$i]->sum_bphtb_dibayar;
		$arrVal = $arrVal + $data->data[$i]->sum_bphtb_dibayar;
		$HTML .= "\t<div class=\"container\"><tr>\n";
                 $HTML .= "\t\t<td align='center' class=\"" . $class . "\">".$data->data[$i]->payment_paid."</td> \n";
                 $HTML .= "\t\t<td align='right' class=\"" . $class . "\">".number_format($data->data[$i]->sum_bphtb_dibayar,0,".",",")."</td> \n";
                 $HTML .= "\t</tr></div>\n";
	}
	       $HTML .="<tr>\n";
               $HTML .="<td align='right'><b>Jumlah</b></td> \n";
               $HTML .="<td align='right'><b>".number_format($jumlah['sum_bphtb_dibayar'],0,".",",")."</b></td> \n";
               $HTML .="</tr>\n";
	if($arrMax) {
		$mx = max($arrMax);
		$mxval = $mx;
	} else {
		$mxval = 0;
	}
	$html = $HTML;
	$dat = $arrDat;
	return true;
}

    public function headerContent() {
        global $find, $a, $m, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama;

        $HTML = $this->headerContentDashboard();
        $this->getDocument($dt,$mxval,$html);
        
        if ($dt) {
            $HTML .= $html;
        } else {
            $HTML .= "<tr><td colspan=\"8\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentDashboard() {
        global $find, $a, $m, $arConfig, $appConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNoPel, $srcNama;
        
        $params = "a=" . $a . "&m=" . $m;
        $startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input'] . "&jnsBerkas=1") . "\">";
        $endLink = "</a>";

        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";       
        $HTML .= "\t\t<td class=\"tdheader\" width='30%'> Tanggal</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\" width='40%'> Rp. </td> \n";
        $HTML .= "\t</tr>\n";

        return $HTML;
    }
    public function displayDataDashboard() {
        global $s;
        echo "<div class=\"ui-widget consol-main-content\" style=\"min-height:300px;\">\n";
        
        echo "\t<div class=\"ui-widget-content consol-main-content-inner\" style=\" width: 50%;  \">\n";
        echo $this->headerContent();
        echo "\t</div>\n";
        echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\" style=\"width: 50%;\">  \n";
        echo $this->paging();        
        echo "\t</div>\n";
        echo "\t</div>\n";
        if($s == '5'){
            echo "<button id=\"buttonExport\">Export chart</button>";
          echo "<div id=\"conts\"  style=\"width:580px; height:300px; position:relative; margin-top:-310px; margin-left:680px; display:block; background-color:  #FFFFFF;\"></div>";
        }else if($s=='7'){
            echo "<div id=\"cont1\"  style=\"width:580px; height:300px; position:relative; margin-top:-310px; margin-left:680px; display:block; background-color:  #FFFFFF;\"></div>";
        }else if($s == '6'){           
          echo "<div id=\"cont\"  style=\"width:580px; height:300px; position:relative; margin-top:-310px; margin-left:680px; display:block; background-color:  #FFFFFF;\"></div>";   
        }   
        
    }
    function paging() {
        global $a, $m, $n, $s, $page, $np;
        //$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
        //$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
        //$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;

        $params = "a=" . $a . "&m=" . $m;
        $sel = $n;
        $sts = $s;
        
        $html = "<div>";
        $row = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        $rowlast = (($page) * $this->perpage) < $this->totalRows ? ($page) * $this->perpage : $this->totalRows;
        //$rowlast = $this->totalRows < $rowlast ? $this->totalRows : $rowlast;
        $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $this->totalRows;

        $parl = $params . "&n=" . $sel . "&s=" . $sts . "&p=" . ($this->defaultPage - 1);
        $paramsl = base64_encode($parl);

        $parr = $params . "&n=" . $sel . "&s=" . $sts . "&p=" . ($this->defaultPage + 1);
        $paramsr = base64_encode($parr);
        
        //if ($np) $page++;
        //else $page--;
        if ($page != 1) {
            //$page--;
            $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
        }
        if ($rowlast < $this->totalRows) {
            //$page++;
            
            $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
        }
        $html .= "</div>";
        return $html;
    } 
}
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";


$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;


$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;

echo "<script language=\"javascript\">var ap='".$a."';</script>\n";
if (isset($_SESSION['atDashboard'])) {

    if ($_SESSION['atDashboard'] != $s) {
        $_SESSION['atDashboard'] = $s;
        $find = "";
        $find_notaris = "";
        $page = 1;
        $np = 1;
        $tgl1 = '';
        $tgl2 = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['atDashboard'] = $s;
}


$dash = new dashboard(1, $uname);
//$pages = $dash->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$dash->setStatus($s);
//$dash->setDataPerPage($pages);
$dash->setDefaultPage(1);

?>
 
 <script type='text/javascript' language='javascript'>
<?php
if ($dash->getDocument($dat,$mxval,$html)) {
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
var th = d.getFullYear();
var ticks = Array();
for (var i=0;i<days;i++) {
	ticks[i] = i+1;
}

var tahun = [(th-11),(th-10),(th-9),(th-8),(th-7),(th-6),(th-5),(th-4),(th-3),(th-2),(th-1),th];


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
var chart2;
var chart1;
var options = {
		chart: {
			renderTo: 'conts',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Penerimaan BPHTB bulan ' + month[d.getMonth()] + ' Tahun <?php echo date("Y")?>'
		},
		subtitle: {
			text: '<?php echo  $dash->getConfigValue($a,'NAMA_DINAS')?>'
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
			renderTo: 'cont1',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Penerimaan BPHTB'
		},
		subtitle: {
			text: '<?php echo  $dash->getConfigValue($a,'NAMA_DINAS')?>'
		},
		xAxis: {
			categories:tahun,
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
	
var options3 = {
		chart: {
			renderTo: 'cont',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Penerimaan BPHTB Per Bulan Tahun <?php echo date("Y")?>' 
		},
		subtitle: {
			text: '<?php echo $dash->getConfigValue($a,'NAMA_DINAS')?>'
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
        var s = '<?php echo  $s ?>';
        
	var dm = new Date();
	function changeSummary(sx,sy,jml_pembayar,trs_hari_ini,per,tahunan) {
		options = {};
		//var tot = document.getElementById('tot-trans');
		//var tottr = document.getElementById('tot-trims');
		var t1 = document.createTextNode(number_format(jml_pembayar, 0, '.', ','));
		var t2 = document.createTextNode("Rp. "+number_format(trs_hari_ini, 2, '.', ','));
		//removeNode(tot);
		//removeNode(tottr);
		//tot.appendChild(t1);
		//tottr.appendChild(t2);
		
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
	       options2.series=[{
			//name: 'Penerimaan bulan ' + month[dm.getMonth()],
			name: 'Tahunan',
			color:'#ddd700',
			data: tahunan
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
						var jt  = this.value /1000000000;
						var sjt = number_format(jt, 2	, '.', ',')+" Miliar";
						return sjt;
					}			
			}
		};
               options3.series=[{
			color:'#fed700',
			//name: 'Penerimaan tahun ',
			name: 'Perbulan',
			data: sy
		}]
		
		options3.yAxis= {
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
                
               if(s == '5' ){ 
               
                chart = new Highcharts.Chart(options);
               }else if(s == '6'){
                 chart2 = new Highcharts.Chart(options3);
               }else if(s =='7'){
		 chart1 = new Highcharts.Chart(options2);
               } 
		
	}
$('#buttonExport').click(function() {
    
    chart.exportChart({type: 'image/jpeg', filename: 'my-jpg'}, {subtitle: {text:''}});
    });
	function getSummary() {
               
		$.ajax({
		  url: "./view/BPHTB/dashboard/svc-get-data.php",
		  datatype:"json",
		  type: "POST",
		  data: {'a':ap,'month':'<?php echo date("n")?>','year':'<?php echo date("Y")?>'},
		  success: function(data){
                       var obj = JSON.parse(data);
                      
                        //console.log(data);
			if(obj.success){
                           
                             changeSummary(obj.data.trs_bulan_ini,obj.data.trs_tahun_ini,obj.data.jml_pembayar,obj.data.trs_hari_ini,obj.data.per,obj.data.trs_tahunan) ;
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
<?php
$dash->displayDataDashboard();
?>
