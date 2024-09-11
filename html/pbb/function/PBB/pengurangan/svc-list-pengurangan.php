<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
//echo '<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>';

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

error_reporting(E_ALL);
ini_set('display_errors', 1);

function headerPengurangan ($mod,$nama) {
	global $appConfig;
	$model = ($mod==0) ? "KECAMATAN" : $appConfig['LABEL_KELURAHAN'];
	$dl = "";
	if ($mod==0) { 
		$dl = $appConfig['C_KABKOT']." ".$appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\"><tr><td colspan=\"12\" align=\"center\" height=\"35\"><b>{$dl}<b></td></tr>
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <col width=\"80\" />
	  <col width=\"auto\" />
	  <col width=\"auto\" />
	  <tr>
		<td width=\"auto\" height=\"35\" align=\"center\"><b>NO</b></td>
		<td width=\"auto\" align=\"center\"><b>NOP</b></td>
		<td width=\"auto\" align=\"center\"><b>NAMA</b></td>
		<td width=\"auto\" align=\"center\"><b>ALAMAT</b></td>
		<td width=\"auto\" align=\"center\"><b>KECAMATAN</b></td>
		<td width=\"auto\" align=\"center\"><b>".strtoupper($appConfig['LABEL_KELURAHAN'])."</b></td>
		<td width=\"auto\" align=\"center\"><b>PBB TERHUTANG<br> SEBELUM PENGURANGAN (RP)</b></td>
		<td width=\"80\" align=\"center\"><b>PENGURANGAN (%)</b></td>
		<td width=\"auto\" align=\"center\"><b>PENGURANGAN (RP)</b></td>
		<td width=\"auto\" align=\"center\"><b>PBB TERHUTANG<br> SETELAH PENGURANGAN (RP)</b></td>
		<td width=\"auto\" align=\"center\"><b>TANGGAL MASUK</b></td>
		<td width=\"auto\" align=\"center\"><b>TANGGAL SELESAI</b></td>
		<!-- <td width=\"auto\" align=\"center\"><b>ALASAN PENGURANGAN</b></td> -->
	  </tr>
	";
	return $html; 
}
	
function getKecamatan($p) {
	global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKC_ID"];
		$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		$i++;
	}
	
	return $data;
}

function getKelurahan($p) {
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res )) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	return $data;
}

function getKecamatanNama($kode) {
		global $DBLink;
		$query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '".$kode."';";
		$res   = mysqli_query($DBLink, $query);
		$row   = mysqli_fetch_array($res);
		return $row['CPC_TKC_KECAMATAN'];
}
function getKelurahanNama($kode) {
		global $DBLink;
		$query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '".$kode."';";
		$res   = mysqli_query($DBLink, $query);
		$row   = mysqli_fetch_array($res);
		return $row['CPC_TKL_KELURAHAN'];
}

function showTable ($mod=0,$nama="") {
	global $kd,$kecamatan,$kelurahan,$kab,$appConfig,$page,$dt,$sum,$totalrows, $perpage;
	
	$c = $dt['JML_DATA'];
	//echo $c;
	//print_r($dt);
	$number = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
	$html = "";
	$a= $number + 1;
	$html = headerPengurangan ($mod,$nama);
	$summary = array('name'=>'JUMLAH', 'percent'=>0, 'ketetapan_awal'=>0, 'ketetapan_disetujui'=>0);
    for ($i=0;$i<$c;$i++) {
	
		$nop  	 	   = $dt[$i]['CPM_OP_NUMBER'];
		$name 	 	   = $dt[$i]['CPM_WP_NAME'];
		$alamat		   = $dt[$i]['CPM_OP_ADDRESS'];
		$kecamatan	   = $dt[$i]['KECAMATAN'];//getKecamatanNama($dt[$i]['CPM_OP_KECAMATAN']);
		$kelurahan	   = $dt[$i]['KELURAHAN'];//getKelurahanNama($dt[$i]['CPM_OP_KELURAHAN']);
		$percent 	   = $dt[$i]['CPM_RE_PERCENT_APPROVE'];
		$ketetapanAwal = $dt[$i]['CPM_SPPT_DUE'];
		$jmlBayar	   = $dt[$i]['JUMLAH_BAYAR'];
		$pengurangan   = $dt[$i]['CPM_SPPT_DUE'] - $dt[$i]['JUMLAH_BAYAR'];
		$alasan		   = str_replace('#',', ',$dt[$i]['CPM_RE_ARGUEMENT']);
		
		$html .= "<tr>
					<td align=\"right\">{$a}</td>
					<td align=\"center\">{$nop}</td>
					<td align=\"left\">{$name}</td>
					<td align=\"left\">{$alamat}</td>
					<td align=\"left\">{$kecamatan}</td>
					<td align=\"left\">{$kelurahan}</td>
					<td align=\"right\">".number_format($ketetapanAwal,0,",",".")."</td>
					<td align=\"right\">{$percent}</td>
					<td align=\"right\">".number_format($pengurangan,0,",",".")."</td>
					<td align=\"right\">".number_format($jmlBayar,0,",",".")."</td>
					<td align=\"center\">".$dt[$i]['CPM_DATE_RECEIVE']."</td>
					<td align=\"center\">".$dt[$i]['CPM_DATE_APPROVER']."</td>
					<!-- <td align=\"left\">{$alasan}</td> -->
				  </tr>";
				  
		 /* $summary['percent'] 	 	     += $dt[$i]["CPM_RE_PERCENT_APPROVE"];
		 $summary['ketetapan_awal'] 	 += $dt[$i]["CPM_SPPT_DUE"];
		 $summary['ketetapan_disetujui'] += $dt[$i]["JUMLAH_BAYAR"]; */
		 
		 $a++;
    }  
	
	$html .= "<tr>
					<td align=\"center\" colspan=\"6\">JUMLAH PERMOHONAN : ".$totalrows."</td>
					<td align=\"right\">".number_format($sum['SUM_BEFORE'],0,",",".")."</td>
					<td align=\"right\"></td>
					<td align=\"right\">".number_format($sum['PNG'],0,",",".")."</td>
					<td align=\"right\">".number_format($sum['SUM_AFTER'],0,",",".")."</td>
					<!-- <td align=\"right\"></td> -->
					<td align=\"right\"></td> 
					<td align=\"right\"></td> 
				  </tr>";
	
	return $html."</table><div align=\"center\">".paging()."</div>";
}

function getSummary($where){
	    global $DBLink;
		
		$whr="";
		if($where) {
			$whr =" AND {$where}";
		}
		$query = "SELECT SUM(CPM_SPPT_DUE) AS SUM_BEFORE, 
		SUM(REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100)))),0)), ',','')) AS PNG,
		SUM(REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100))),0)), ',','')) AS SUM_AFTER FROM cppmod_pbb_services A 
		JOIN cppmod_pbb_service_reduce B WHERE A.CPM_ID=B.CPM_RE_SID {$whr}";
		$res   = mysqli_query($DBLink, $query);
		$row   = mysqli_fetch_assoc($res);
		//print_r($row);
		return $row;
}

function getData($where,$page,$perpage) {
	global $DBLink,$kd,$thn,$bulan;
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$return=array();
	$return["CPM_OP_NUMBER"]='';
	$return["CPM_WP_NAME"]='';
	$return["CPM_OP_ADDRESS"]='';
	$return["CPM_OP_KECAMATAN"]='';
	$return["CPM_OP_KELURAHAN"]='';
	$return["CPM_RE_PERCENT_APPROVE"]=0;
	$return["CPM_SPPT_DUE"]=0;
	$return["JUMLAH_BAYAR"]=0;
	$return["PENGURANGAN"]=0;
	$return["CPM_RE_ARGUEMENT"]='';
	
	//$sum = getSummary($where);
	
	$whr="";
	if($where) {
		$whr =" AND {$where}";
	}	
	$query = "SELECT A.CPM_OP_NUMBER,A.CPM_WP_NAME,A.CPM_OP_ADDRESS,A.CPM_SPPT_DUE,B.CPM_RE_PERCENT_APPROVE, B.CPM_RE_ARGUEMENT, A.CPM_DATE_RECEIVE, A.CPM_DATE_APPROVER, C.CPC_TKC_KECAMATAN AS KECAMATAN,D.CPC_TKL_KELURAHAN AS KELURAHAN,  REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100))),0)), ',','') AS JUMLAH_BAYAR, 
                    REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100)))),0)), ',','') AS PENGURANGAN 
                    FROM cppmod_pbb_services A 
                    JOIN cppmod_pbb_service_reduce B 
                    LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN=C.CPC_TKC_ID
                    LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN=D.CPC_TKL_ID WHERE A.CPM_ID = B.CPM_RE_SID {$whr}"; 
	
	if ($perpage) {
            $query .= " ORDER BY CPM_DATE_RECEIVE DESC LIMIT $hal, $perpage ";
    }
	//echo $query.'<br/>';
	$qRows   = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_services A JOIN cppmod_pbb_service_reduce B  WHERE A.CPM_ID = B.CPM_RE_SID {$whr}";
	$resRows = mysqli_query($DBLink, $qRows);
        $numrows = mysql_fetch_row($resRows);
	$return['JML_ROWS'] = $numrows['0'];
	
	$res = mysqli_query($DBLink, $query);
	//$return['JML_DATA'] = mysqli_num_rows($res);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$row = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$i]["CPM_OP_NUMBER"]                = ($row["CPM_OP_NUMBER"]!="")?$row["CPM_OP_NUMBER"]:'-';
		$return[$i]["CPM_WP_NAME"]                  = ($row["CPM_WP_NAME"]!="")?$row["CPM_WP_NAME"]:'-';
		$return[$i]["CPM_OP_ADDRESS"]               = ($row["CPM_OP_ADDRESS"]!="")?$row["CPM_OP_ADDRESS"]:'-';
		//$return[$i]["CPM_OP_KECAMATAN"]	 	  = ($row["CPM_OP_KECAMATAN"]!="")?$row["CPM_OP_KECAMATAN"]:'-';
		//$return[$i]["CPM_OP_KELURAHAN"]	 	  = ($row["CPM_OP_KELURAHAN"]!="")?$row["CPM_OP_KELURAHAN"]:'-';
		$return[$i]["CPM_RE_PERCENT_APPROVE"]       = ($row["CPM_RE_PERCENT_APPROVE"]!="")?$row["CPM_RE_PERCENT_APPROVE"]:0;
		$return[$i]["CPM_SPPT_DUE"]                 = ($row["CPM_SPPT_DUE"]!="")?$row["CPM_SPPT_DUE"]:0;
		$return[$i]["JUMLAH_BAYAR"]                 = ($row["JUMLAH_BAYAR"]!="")?$row["JUMLAH_BAYAR"]:0;
		$return[$i]["PENGURANGAN"]                  = ($row["PENGURANGAN"]!="")?$row["PENGURANGAN"]:0;
		$return[$i]["CPM_RE_ARGUEMENT"]             = ($row["CPM_RE_ARGUEMENT"]!="")?$row["CPM_RE_ARGUEMENT"]:'';
		$return[$i]["CPM_DATE_APPROVER"]            = ($row["CPM_DATE_APPROVER"]!="")?$row["CPM_DATE_APPROVER"]:'';
		$return[$i]["CPM_DATE_RECEIVE"]             = ($row["CPM_DATE_RECEIVE"]!="")?$row["CPM_DATE_RECEIVE"]:'';
		$return[$i]["KECAMATAN"]                    = ($row["KECAMATAN"]!="")?$row["KECAMATAN"]:'';
		$return[$i]["KELURAHAN"]                    = ($row["KELURAHAN"]!="")?$row["KELURAHAN"]:'';
		$i++;
	}
        $return['JML_DATA'] = $i;
	return $return;
}

function paging() {
		global $s,$page,$np,$perpage,$defaultPage,$totalrows;
		
		//$params = "a=".$a."&m=".$m;
		//echo $totalrows;exit;
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"showPenguranganPage(".($page-1).")\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"showPenguranganPage(".($page+1).")\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];

$kab  			  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 		  = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn 			  = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama	 		  = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$np 			  = @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$page 			  = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$perpage 	= $appConfig['ITEM_PER_PAGE'];
//$where 		= " CPM_OP_NUMBER LIKE '".$kecamatan."%'";
//echo $where;
$arrWhere = array();

if ($kecamatan !="") {
        array_push($arrWhere," CPM_OP_NUMBER LIKE '".$kecamatan."%'");
}
if ($thn!=""){
    array_push($arrWhere," CPM_SPPT_YEAR='{$thn}'");  
} 
$where = implode (" AND ",$arrWhere);

//echo $where;

$dt    		= getData($where,$page,$perpage);
$sum		= getSummary($where);
$totalrows	= $dt['JML_ROWS'];

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
}
?>
<script language="javascript">
$(document).ready(function(){
    $("#closeCBox").click(function(){
        $("#cBox").css("display","none");
    })
})
</script>
