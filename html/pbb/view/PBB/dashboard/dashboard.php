<?php 
ini_set("display_errors",1);
error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'dashboard', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/PBB/dashboard/dbDashboard.php");


function getHeader(&$arModuleConfig,&$GWdbSpec,&$dbDashboard){
	global $User, $a, $m, $dbSpec ;
	$appConfig = $User->GetAppConfig($a);
	$arModuleConfig = $User->GetModuleConfig($m);
	
	SCANPayment_ConnectToDB($GWDBLink, $GWDBConn, $appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], true);
	$GWdbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $GWDBLink);
	$dbDashboard = new dbDashboard($GWdbSpec,$dbSpec,$arModuleConfig["CPC_TK_ID"],$arModuleConfig['CPC_TK_KABKOTA']); // $arModuleConfig['CPC_TK_KABKOTA']

	$header  = "<style type=\"text/css\"> #Pkec{ display:none;} #Pkel{ display:none;} #Pall{ display:block;} </style>";
	$header .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
	$header .= "<script type=\"text/javascript\" src=\"inc/datepicker/datepickercontrol.js\"></script>";
	$header .= "<script type=\"text/javascript\" src=\"view/PBB/dashboard/dashboard.js\"></script>";
	$header .= "<script type=\"text/javascript\" src=\"inc/js/jquery-1.3.2.min.js\" ></script>"; //type=\"text/javascript\"
	$header .= "<script type=\"text/javascript\" src=\"inc/js/highcharts.js\"></script>"; //type=\"text/javascript\"
	return $header;
}
function getChart($id, $title ,$subTitle, $series, $xAxisData, $yAxisTitle){
	$chart ="<script type=\"text/javascript\">";
	$chart .="$(function () {
    $(document).ready(function() {
		getChart(\"$id\",\"$title\",\"$subTitle\",\"$series\",\"$xAxisData\",\"$yAxisTitle\");
	});
	});
	";
$chart .= "</script>";
return $chart;
}

function getChartBar($id, $title ,$subTitle, $series, $xAxisData, $yAxisTitle, $satuan){
	$chart ="<script type=\"text/javascript\">";
	$chart .="getChartBar(\"$id\",\"$title\",\"$subTitle\",\"$series\",\"$xAxisData\",\"$yAxisTitle\",\"$satuan\");";
$chart .= "</script>";
return $chart;
}

function getChartPie($id, $title ,$subTitle, $series){
	$chart ="<script type=\"text/javascript\">";
	$chart .="getChartPie(\"$id\",\"$title\",\"$subTitle\",\"$series\");";
$chart .= "</script>";
return $chart;
}

function getContainetChart($id,$width,$height){
	$container = "<div id=\"$id\" style=\"width: $width; height: $height; float:left; margin: 10 10 0 10\"></div>";
	return $container;
}

// prevent direct access
if (!isset($data)) { return; }
$uid = $data->uid;
// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
//prevent access to not accessible module
if (!$bOK) { return false; } 
/*=========================================================Header Modul===========================================================================*/
echo getHeader($arModuleConfig,$GWdbSpec,$dbDashboard);
if (1==1) { // !isset($_REQUEST['btSubmit']) tampilan paling utama, konfigurasi data apa yang akan di pilih.
?>
<h3>Monitoring SPPT daerah <?php echo $arModuleConfig["CPC_TK_KABKOTA"]; ?></h3>
    <form action="" method="post">
    <table class="transparent">
        <tr>
            <td colspan="9"><b>View Monitoring</b></td>
        </tr>
        <tr valign="top">
            <td>View</td>
            <td>
            <a href="#" id="Lkec" onclick="Fkec()">Kec</a> /
            <a href="#" id="Lkel" onclick="Fkel()">Kel</a> /
            <a href="#" id="LSemua" onclick="Fsemua()">Semua</a>
          </td>
            <td><a href="#" id="Lthn" onclick="Ftahun()">Tahun</a> (<a href="#" id="Lrentang" onclick="Frentang()">Rentang</a>)</td>
            <td>Atribut (Flag)</td>
            <!--<td>Bentuk Chart</td>-->
            <td>&nbsp;</td>
        </tr>
        <tr valign="top">
            <td>
            <select name="objek" onchange="disFlag(this)">
              <option value="100">Jumlah Banyaknya SPPT</option>
              <option value="1">Jumlah Nilai Perolehan SPPT</option>
          	</select></td>
            <td>
            <p id="Pkec">
            <?php 
			echo "<input type='checkbox' name='Ckec_all' id='Ckec_all' onchange=check(this,'Ckec[]') />&nbsp;<b>Cek Semua...</b><br />";
			$res = $dbDashboard->getKecamatanList();
			foreach($res as $key=>$value){
				echo "<input type='checkbox' name='Ckec[]' value='".$value['CPC_TKC_ID']."' />&nbsp;".$value['CPC_TKC_KECAMATAN']."<br />";
			}
			?>
            </p>
            <p id="Pkel">
            <?php 
			echo "<input type='checkbox' name='Ckel_all' id='Ckel_all' onchange=check(this,'Ckel[]') />&nbsp;<b>Cek Semua...</b><br />";
			$res = $dbDashboard->getKelurahanList();
			foreach($res as $key=>$value){
				echo "<input type='checkbox' name='Ckel[]' value='".$value['CPC_TKL_ID']."' />&nbsp;".$value['CPC_TKL_KELURAHAN']."<br />";
			}
			?>
            </p>
            <p id="Pall">
            <?php echo "<input type='checkbox' name='Call' id='Call' checked='checked' />&nbsp;<b>Tampil semua data</b><br />"; ?>
            </p>
            </td>
            <td>
            <input name="tgl_0" type="text" id="tgl_0" size="4" maxlength="4" onkeypress="return iniAngka(event)" /> -
            <input name="tgl_1" type="text" id="tgl_1" size="4" maxlength="4" onkeypress="return iniAngka(event)" disabled="disabled"/></td>
            <td><p>
              <input type="radio" name="flag" value="100" id="flag_100" checked="checked" />&nbsp;Semua<br />
              <input type="radio" name="flag" value="1" id="flag_1" />&nbsp;Sudah di Bayar<br />
              <input type="radio" name="flag" value="0" id="flag_0" />&nbsp;Belum di Bayar<br />
              <input type="radio" name="flag" value="10" id="flag_10" />&nbsp;Sudah & Belum di Bayar<br />
            </p></td>
            <!--<td><p>
              <input type="radio" name="chart" value="1" id="chart_batang" checked="checked" />&nbsp;Batang<br />
              <input type="radio" name="chart" value="2" id="chart_pie" />&nbsp;Pie<br />
            </p></td>-->
            <td><input type="submit" name="btSubmit" id="button" value="Submit" onclick="return validate();" /></td>
        </tr>
    </table>
    </form>
<?php
}  
if( isset($_REQUEST['objek']) ){ 
	$dbDashboard->getSPPT($_REQUEST);
}else{
	$jsonfile = "http://192.168.30.2:9800/payment/pc/svr/central/view/PBB/dashboard/svc-get-data.php?nmKota=".$arModuleConfig['CPC_TK_KABKOTA'];
	$data = json_decode(file_get_contents($jsonfile),true);
	
	$title = $data[1]['title'];
	$subTitle = $data[1]['subTitle']; //Nama kota request dari configuration
	$series = $data[1]['series'];
	$xAxisData =$data[1]['xAxisData'];
	$yAxisTitle = $data[1]['yAxisTitle']; //Permanen
	echo getChart('containerChart', $title ,$subTitle, $series, $xAxisData, $yAxisTitle);
	echo getContainetChart('containerChart',500,400);
	
	
	$title = $data[0]['title'];
	$subTitle = $data[0]['subTitle']; //Nama kota request dari configuration
	$series = $data[0]['series'];
	$xAxisData =$data[0]['xAxisData'];
	$yAxisTitle = $data[0]['yAxisTitle']; //Permanen
	echo getChart('containerChart2', $title ,$subTitle, $series, $xAxisData, $yAxisTitle);
	echo getContainetChart('containerChart2',500,400);
}

?>
<script type="text/javascript">
setInterval(function() {	getDataDayMonth('<?php echo $arModuleConfig['CPC_TK_KABKOTA']; ?>');	  }, 1000*10);
</script>
