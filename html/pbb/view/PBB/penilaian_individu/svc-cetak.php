<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_individu', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

date_default_timezone_set("Asia/Jakarta");

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

$q 			= isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$thn 		= isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$kecamatan 	= isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 	= isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$page 		= isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
// print_r($_REQUEST);
if ($q=="") exit(1);
$q = base64_decode($q);

$j 	= $json->decode($q);
$u 	= $j->uid;
$a 	= $j->a;
$m 	= $j->m;
$s 	= $j->s;

$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$User 	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);

$defaultPage 	= 1;
$perpage 		= $appConfig['ITEM_PER_PAGE'];

if(stillInSession($DBLink,$json,$sdata)){
	displayData();
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

function headerContent(){
	$html = "
		<table width='100%'>
			<tr>
				<th rowspan=\"2\">NO</th>
				<th rowspan=\"2\">NOP</th>
				<th rowspan=\"2\">NAMA WAJIB PAJAK<br>ALAMAT OBJEK PAJAK</th>
				<th rowspan=\"2\">RT/RW</th>
				<th rowspan=\"2\">JML<br>BNG</th>
				<th colspan=\"2\">LUAS</th>
				<th rowspan=\"2\">KODE<br>ZNT</th>
				<th colspan=\"2\">KELAS</th>
				<th colspan=\"2\">NJOP/M2</th>
				<th colspan=\"2\">NJOP (Rp 000,-)</th>
				<th rowspan=\"2\">JUMLAH NJOP<br>(Rp 000,-)</th>
			</tr>
			<tr>
				<th>TNH<br>TNH-BERS</th>
				<th>BNG<br>BNG-BERS</th>
				<th>TNH</th>
				<th>BNG</th>
				<th>TNH<br>TNH-BERS</th>
				<th>BNG<br>BNG-BERS</th>
				<th>TNH<br>TNH-BERS</th>
				<th>BNG<br>BNG-BERS</th>
			</tr>
		";
	
	return $html;
}

function createContent(&$totalrows){
	global $perpage;
	$data 	   = getData();
	$totalrows = count($data);
	// echo $totalrows; exit;
	$html = "";
	$i=1;
	foreach($data as $row){
        $class = $i%2==0 ? "tdbody1":"tdbody2";
        $html .= "<div class=\"container\"><tr>\n";
		
		$totalNJOPBersama 	  	  	  = $data[$i]['NJOP_TNH_BERSAMA']+$data[$i]['NJOP_BNG_BERSAMA'];
		$NJOPTanahPerMeterBersama 	  = ($data[$i]['LUAS_TNH_BERSAMA']!=0 && $data[$i]['NJOP_TNH_BERSAMA']!=0 ? ($data[$i]['NJOP_TNH_BERSAMA']/$data[$i]['LUAS_TNH_BERSAMA']) : 0);
		$NJOPBangunanPerMeterBersama  = ($data[$i]['LUAS_BNG_BERSAMA']!=0 && $data[$i]['NJOP_BNG_BERSAMA']!=0 ? ($data[$i]['NJOP_BNG_BERSAMA']/$data[$i]['LUAS_BNG_BERSAMA']) : 0);
		
        $html .= "<td class=\"".$class."\" align=\"right\" valign=\"top\">".$i."</td> \n";
        $html .= "<td class=\"".$class."\" align=\"center\" valign=\"top\">".$row['NOP']."</td> \n";
        $html .= "<td class=\"".$class."\" valign=\"top\">".$row['NAMA']."<br>".$row['ALAMAT']."</td> \n";
        $html .= "<td class=\"".$class."\" align=\"center\" valign=\"top\">".$row['RT']."/".$row['RW']."</td> \n";
        $html .= "<td class=\"".$class."\" align=\"center\" valign=\"top\">".$row['JML_BNG']."</td> \n";
		$html .= "<td class=\"".$class."\" align=\"right\" valign=\"top\">".$row['LUAS_TANAH']."<br>".$row['LUAS_TNH_BERSAMA']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".$row['LUAS_BANGUNAN']."<br>".$row['LUAS_BNG_BERSAMA']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"center\" valign=\"top\">".$row['ZNT']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"center\" valign=\"top\">".$row['KLS_TANAH']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"center\" valign=\"top\">".$row['KLS_BANGUNAN']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".($row['NJOP_TANAH']/$row['LUAS_TANAH'])."<br>".$NJOPTanahPerMeterBersama."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".($row['NJOP_BANGUNAN']/$row['LUAS_BANGUNAN'])."<br>".$NJOPBangunanPerMeterBersama."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".($row['NJOP_TANAH'])."<br>".$row['NJOP_TNH_BERSAMA']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".($row['NJOP_BANGUNAN'])."<br>".$row['NJOP_BNG_BERSAMA']."</td> \n";
		$html .= "<td class=\"".$class."\"align=\"right\" valign=\"top\">".($row['NJOP_TANAH']+$row['NJOP_BANGUNAN'])."<br>".$totalNJOPBersama."</td> \n";
		$html .= "</tr></div>\n";
        $i++;
    }
	$html .= "</table>";
	return $html;
}

function getData(){
	global $DBLink, $thn, $kecamatan, $kelurahan, $page, $perpage;
	
	// $queryCount = " SELECT COUNT(*) AS TOTALROWS
				// FROM
					// cppmod_pbb_sppt_final A
				// LEFT JOIN cppmod_pbb_sppt_ext_final B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
				// WHERE
					// B.CPM_PAYMENT_PENILAIAN_BGN = 'individu' ";
	// $resCount 	= mysql_query($queryCount,$DBLink);
	// $rowCount	= mysqli_fetch_assoc($resCount);
	
	$addCondition = "";
	if($thn!=""){
		$addCondition .= " AND A.CPM_SPPT_THN_PENETAPAN = '".$thn."' ";
	}
	if($kecamatan!=""){
		$addCondition .= " AND A.CPM_OP_KECAMATAN = '".$kecamatan."' ";
	}
	if($kelurahan!=""){
		$addCondition .= " AND A.CPM_OP_KELURAHAN = '".$kelurahan."' ";
	}
	
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$query = "SELECT * FROM (SELECT
				A.CPM_SPPT_DOC_ID,
				A.CPM_NOP AS NOP,
				A.CPM_WP_NAMA AS NAMA,
				A.CPM_OP_ALAMAT AS ALAMAT,
				A.CPM_OP_RT AS RT,
				A.CPM_OP_RW AS RW,
				A.CPM_OP_KELURAHAN AS KELURAHAN,
				A.CPM_OP_KECAMATAN AS KECAMATAN,
				A.CPM_OP_LUAS_TANAH AS LUAS_TANAH,
				A.CPM_OP_LUAS_BANGUNAN AS LUAS_BANGUNAN,
				A.CPM_OT_ZONA_NILAI AS ZNT,
				A.CPM_OP_KELAS_TANAH AS KLS_TANAH,
				A.CPM_OP_KELAS_BANGUNAN AS KLS_BANGUNAN,
				A.CPM_NJOP_TANAH AS NJOP_TANAH,
				A.CPM_NJOP_BANGUNAN AS NJOP_BANGUNAN,
				B.CPM_OP_NUM AS NO_BANGUNAN,
				B.CPM_PAYMENT_INDIVIDU AS NILAI_INDIVIDU,
				COUNT(B.CPM_SPPT_DOC_ID) AS JML_BNG,
				C.CPM_LUAS_BUMI_BEBAN AS LUAS_TNH_BERSAMA,
				C.CPM_LUAS_BNG_BEBAN AS LUAS_BNG_BERSAMA,
				C.CPM_NJOP_BUMI_BEBAN AS NJOP_TNH_BERSAMA,
				C.CPM_NJOP_BNG_BEBAN AS NJOP_BNG_BERSAMA
			FROM
				cppmod_pbb_sppt_final A
			LEFT JOIN cppmod_pbb_sppt_ext_final B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
			LEFT JOIN cppmod_pbb_sppt_anggota C ON A.CPM_NOP=C.CPM_NOP
			WHERE
				B.CPM_PAYMENT_PENILAIAN_BGN = 'individu' ".$addCondition."
			GROUP BY A.CPM_NOP
			UNION
			SELECT
				A.CPM_SPPT_DOC_ID,
				A.CPM_NOP AS NOP,
				A.CPM_WP_NAMA AS NAMA,
				A.CPM_OP_ALAMAT AS ALAMAT,
				A.CPM_OP_RT AS RT,
				A.CPM_OP_RW AS RW,
				A.CPM_OP_KELURAHAN AS KELURAHAN,
				A.CPM_OP_KECAMATAN AS KECAMATAN,
				A.CPM_OP_LUAS_TANAH AS LUAS_TANAH,
				A.CPM_OP_LUAS_BANGUNAN AS LUAS_BANGUNAN,
				A.CPM_OT_ZONA_NILAI AS ZNT,
				A.CPM_OP_KELAS_TANAH AS KLS_TANAH,
				A.CPM_OP_KELAS_BANGUNAN AS KLS_BANGUNAN,
				A.CPM_NJOP_TANAH AS NJOP_TANAH,
				A.CPM_NJOP_BANGUNAN AS NJOP_BANGUNAN,
				B.CPM_OP_NUM AS NO_BANGUNAN,
				B.CPM_PAYMENT_INDIVIDU AS NILAI_INDIVIDU,
				COUNT(B.CPM_SPPT_DOC_ID) AS JML_BNG,
				C.CPM_LUAS_BUMI_BEBAN AS LUAS_TNH_BERSAMA,
				C.CPM_LUAS_BNG_BEBAN AS LUAS_BNG_BERSAMA,
				C.CPM_NJOP_BUMI_BEBAN AS NJOP_TNH_BERSAMA,
				C.CPM_NJOP_BNG_BEBAN AS NJOP_BNG_BERSAMA
			FROM
				cppmod_pbb_sppt_susulan A
			LEFT JOIN cppmod_pbb_sppt_ext_susulan B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
			LEFT JOIN cppmod_pbb_sppt_anggota C ON A.CPM_NOP=C.CPM_NOP
			WHERE
				B.CPM_PAYMENT_PENILAIAN_BGN = 'individu' ".$addCondition."
			GROUP BY A.CPM_NOP ) AS TBL ORDER BY CPM_SPPT_DOC_ID ";
			// $query .= "LIMIT ".$hal.",".$perpage."";
	
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data  = array();
	$i     = 0;
	while($rows  = mysqli_fetch_assoc($res)){
		$data[$i]['NOP'] 			= $rows['NOP'];
		$data[$i]['NAMA'] 			= $rows['NAMA'];
		$data[$i]['ALAMAT'] 		= $rows['ALAMAT'];
		$data[$i]['RT'] 			= $rows['RT'];
		$data[$i]['RW'] 			= $rows['RW'];
		$data[$i]['KELURAHAN'] 		= $rows['KELURAHAN'];
		$data[$i]['KECAMATAN'] 		= $rows['KECAMATAN'];
		$data[$i]['LUAS_TANAH'] 	= $rows['LUAS_TANAH'];
		$data[$i]['LUAS_BANGUNAN'] 	= $rows['LUAS_BANGUNAN'];
		$data[$i]['ZNT'] 			= $rows['ZNT'];
		$data[$i]['KLS_TANAH']		= $rows['KLS_TANAH'];
		$data[$i]['KLS_BANGUNAN']	= $rows['KLS_BANGUNAN'];
		$data[$i]['NJOP_TANAH']		= $rows['NJOP_TANAH'];
		$data[$i]['NJOP_BANGUNAN']	= $rows['NJOP_BANGUNAN'];
		$data[$i]['NO_BANGUNAN'] 	= $rows['NO_BANGUNAN'];
		$data[$i]['NILAI_INDIVIDU'] = $rows['NILAI_INDIVIDU'];
		$data[$i]['JML_BNG'] 		= $rows['JML_BNG'];
		$data[$i]['LUAS_TNH_BERSAMA'] = ($rows['LUAS_TNH_BERSAMA']!='' ? $rows['LUAS_TNH_BERSAMA'] : 0);
		$data[$i]['LUAS_BNG_BERSAMA'] = ($rows['LUAS_BNG_BERSAMA']!='' ? $rows['LUAS_BNG_BERSAMA'] : 0);
		$data[$i]['NJOP_TNH_BERSAMA'] = ($rows['NJOP_TNH_BERSAMA']!='' ? $rows['NJOP_TNH_BERSAMA'] : 0);
		$data[$i]['NJOP_BNG_BERSAMA'] = ($rows['NJOP_BNG_BERSAMA']!='' ? $rows['NJOP_BNG_BERSAMA'] : 0);
		$i++;
	}
	
	// $data[$i]['JML_ROWS'] = $rowCount['TOTALROWS'];
	// echo "<pre>";
	// print_r($data);
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

function displayData() {
        echo "<div class=\"ui-widget consol-main-content\">\n";
        echo "<div class=\"ui-widget-content consol-main-content-inner\">\n";
        echo headerContent();
		echo createContent($totalrows);
        echo "</div>\n";
        echo "<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
		// echo paging($totalrows);
        echo "</div>\n";
}

function paging($totalrows) {
    global $a,$m,$s,$page,$perpage;
    $params = "a=".$a."&m=".$m;
    $html = "<div>";
    $row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
    $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
    $html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

    if ($page != 1) {
        $html .= "&nbsp;<a onclick=\"setPage(".$s.",'0')\"><span id=\"navigator-left\"></span></a>";
    }
    if ($rowlast < $totalrows ) {
        $html .= "&nbsp;<a onclick=\"setPage(".$s.",'1')\"><span id=\"navigator-right\"></span></a>";
    }
    $html .= "</div>";
    return $html;
}

?>
