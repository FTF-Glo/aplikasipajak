<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'nop/sk_njop', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

// require_once($sRootPath . "inc/PBB/dbWajibPajak.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

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

function getDocument(&$dat) {
        global $DBLink,$json,$data,$a,$m,$tab,$page,$totalrows, $perpage,$arConfig,$srcNOP,$srcAlamat,$srcNama, $dbWajibPajak;
        
		$rows = $data;
		// echo "<pre>";
		// print_r($rows); exit;
        // $params = "a=".$a."&m=".$m."&f=".$arConfig['form_input']."&tab=".$tab; 
		$params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'];
        $i = 1;
        $HTML = "";
        foreach($rows as $row){
            $class = $i%2==0 ? "tdbody1":"tdbody2";
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\"><a href='main.php?param=" . base64_encode($params.'&doc_id='.$row['CPM_SPPT_DOC_ID'].'&doc_vers='.$row['CPM_SPPT_DOC_VERSION']) . "'>".$row['CPM_NOP']."</a></td> \n";
            $HTML .= "\t\t<td class=\"".$class."\">".$row['CPM_WP_NAMA']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$row['CPM_OP_ALAMAT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$row['CPM_OT_ZONA_NILAI']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$row['CPM_OP_LUAS_TANAH']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$row['CPM_OP_LUAS_BANGUNAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$row['CPM_NJOP_TANAH']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$row['CPM_NJOP_BANGUNAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".($row['CPM_NJOP_TOTAL'])."</td> \n";
            $HTML .= "\t\t<td class=\"".$class."\">".getKecamatanNama($row['CPM_OP_KECAMATAN'])."</td> \n";
            $HTML .= "\t\t<td class=\"".$class."\">".getKelurahanNama($row['CPM_OP_KELURAHAN'])."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\"><button onclick=\"listTagihan('".$row['CPM_NOP']."')\">Lihat Tagihan</button></td> \n";
            $HTML .= "\t</tr></div>\n";
            $i++;
        }
        $dat = $HTML;
}

function getData(){
	global $DBLink,$page,$perpage,$srcNOP,$srcAlamat,$srcNama,$tab;
	
	$arrWhere = array();
	if ($srcNOP!=""){
		array_push($arrWhere," CPM_NOP LIKE '%{$srcNOP}%'");  
	} 
	if ($srcNama!=""){
		array_push($arrWhere," CPM_WP_NAMA LIKE '%{$srcNama}%'");  
	} 
	if ($srcAlamat !="") {
		array_push($arrWhere," CPM_OP_ALAMAT LIKE '%{$srcAlamat}%'");
	}
	$where = implode (" AND ",$arrWhere);
	
	if($where!=''){
		$whr = ' WHERE '. $where;
	}
	
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	
	switch($tab){
		case 0 : $tableName = "cppmod_pbb_sppt"; break;
		case 1 : $tableName = "cppmod_pbb_sppt_susulan"; break;
		case 2 : $tableName = "cppmod_pbb_sppt_final"; break;
	}
	
	$query = "SELECT CPM_SPPT_DOC_ID,CPM_SPPT_DOC_VERSION,CPM_NOP,CPM_WP_NAMA,CPM_OP_ALAMAT, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN FROM $tableName $whr LIMIT $hal,$perpage";
	
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$qCount   = "SELECT COUNT(*) AS TOTALROWS FROM $tableName $whr";
	// echo $qCount;
				
	$resCount 	= mysqli_query($DBLink, $qCount);
	$rowCount	= mysqli_fetch_assoc($resCount);
	
	$row = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$i]["CPM_SPPT_DOC_ID"]  	= ($row["CPM_SPPT_DOC_ID"]!="")?$row["CPM_SPPT_DOC_ID"]:'-';
		$return[$i]["CPM_SPPT_DOC_VERSION"] = ($row["CPM_SPPT_DOC_VERSION"]!="")?$row["CPM_SPPT_DOC_VERSION"]:'-';
		$return[$i]["CPM_NOP"]           	= ($row["CPM_NOP"]!="")?$row["CPM_NOP"]:'-';
		$return[$i]["CPM_WP_NAMA"]          = ($row["CPM_WP_NAMA"]!="")?$row["CPM_WP_NAMA"]:'-';
		$return[$i]["CPM_OP_ALAMAT"]     	= ($row["CPM_OP_ALAMAT"]!="")?$row["CPM_OP_ALAMAT"]:'-';
		$return[$i]["CPM_OT_ZONA_NILAI"]    = ($row["CPM_OT_ZONA_NILAI"]!="")?$row["CPM_OT_ZONA_NILAI"]:'-';
		$return[$i]["CPM_OP_LUAS_TANAH"]    = ($row["CPM_OP_LUAS_TANAH"]!="")?$row["CPM_OP_LUAS_TANAH"]:'0';
		$return[$i]["CPM_OP_LUAS_BANGUNAN"] = ($row["CPM_OP_LUAS_BANGUNAN"]!="")?$row["CPM_OP_LUAS_BANGUNAN"]:'0';
		$return[$i]["CPM_NJOP_TANAH"] 		= ($row["CPM_NJOP_TANAH"]!="")?$row["CPM_NJOP_TANAH"]:'0';
		$return[$i]["CPM_NJOP_BANGUNAN"] 	= ($row["CPM_NJOP_BANGUNAN"]!="")?$row["CPM_NJOP_BANGUNAN"]:'0';
		$return[$i]["CPM_NJOP_TOTAL"] 		= ($row["CPM_NJOP_BANGUNAN"]+$row["CPM_NJOP_BANGUNAN"]);
		$return[$i]["CPM_OP_KELURAHAN"]  	= ($row["CPM_OP_KELURAHAN"]!="")?$row["CPM_OP_KELURAHAN"]:'';
		$return[$i]["CPM_OP_KECAMATAN"]  	= ($row["CPM_OP_KECAMATAN"]!="")?$row["CPM_OP_KECAMATAN"]:'';
		$i++;
	}
        $return[$i]['JML_DATA'] = $i;
		$return[$i]['JML_ROWS'] = $rowCount['TOTALROWS'];
		
		// echo '<pre>';
		// print_r($return);
		
	return $return;
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

function headerContent() {
    global $find,$a,$m,$tab,$srcNOP,$srcAlamat,$srcNama;

    $HTML = "";
    $HTML .= headerContentWP();

    getDocument($dt);

    if ($dt) {
            $HTML .= $dt;
    } else {
            $HTML .= "<tr><td colspan=\"12\">Klik Cari untuk menampilkan data.</td></tr> ";
    }
    $HTML .= "</table>\n";
    return $HTML;
}

function headerContentWP() {
    global $find,$a,$m,$arConfig,$appConfig,$tab,$srcNOP,$srcAlamat,$srcNama;

    $params = "a=".$a."&m=".$m;
    $startLink = "<a style='text-decoration: none;' href=\"main.php?param=".base64_encode($params."&f=".$arConfig['form_input']."&jnsBerkas=1")."\">";
    $endLink = "</a>";

                $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
    $HTML .= "
    <div style='overflow:auto;'>
        <div style='float:left'>
            Pencarian        
            <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$tab.");\" id=\"srcNOP-".$tab."\" name=\"srcNOP\" size=\"20\" value=\"".$srcNOP."\" placeholder=\"NOP\"/>
            <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$tab.");\" id=\"srcNama-".$tab."\" name=\"srcNama\" size=\"30\" value=\"".$srcNama."\" placeholder=\"Nama\"/>
            <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$tab.");\" id=\"srcAlamat-".$tab."\" name=\"srcAlamat\" size=\"30\" value=\"".$srcAlamat."\" placeholder=\"Alamat\"/>
			<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(".$tab.")\"/>\n
        </div>
    </div>
    </form>\n";

    $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    $HTML .= "\t<tr>\n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\" width=\"150\"> NOP </td> \n";
	$HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\" width=\"150\"> Nama WP </td> \n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\" width=\"150\"> Alamat </td> \n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\" width=\"35\"> Kode ZNT </td> \n";
    $HTML .= "\t\t<td colspan=\"2\" class=\"tdheader\"> Luas </td> \n";
    $HTML .= "\t\t<td colspan=\"3\" class=\"tdheader\"> NJOP </td> \n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\"> Kecamatan </td> \n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\"> ".$appConfig['LABEL_KELURAHAN']." </td> \n";
    $HTML .= "\t\t<td rowspan=\"2\" class=\"tdheader\"> List Tagihan </td> \n";
    $HTML .= "\t</tr>\n";
	$HTML .= "\t<tr>\n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"65\"> Tanah </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"45\"> Bangunan </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"45\"> Tanah </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"45\"> Bangunan </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"45\"> Total </td> \n";
    $HTML .= "\t</tr>\n";

    return $HTML;
}

function displayDataWP () {
        echo "<div class=\"ui-widget consol-main-content\">\n";
        echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
        echo headerContent();
        echo "\t</div>\n";
        echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
        echo paging();
        echo "</div>\n";
}

function paging() {
        global $a,$m,$n,$tab,$page,$np,$perpage,$defaultPage,$totalrows;

        $params = "a=".$a."&m=".$m;
        $html = "<div>";
        $row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
        $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
        $html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

        if ($page != 1) {
                //$page--;
                $html .= "&nbsp;<a onclick=\"setPage(".$tab.",'0')\"><span id=\"navigator-left\"></span></a>";
        }
        if ($rowlast < $totalrows ) {
                //$page++;
                $html .= "&nbsp;<a onclick=\"setPage(".$tab.",'1')\"><span id=\"navigator-right\"></span></a>";
        }
        $html .= "</div>";
        return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";

$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab = $q->tab;
$uname = $q->u;
$uid = $q->uid;

$srcAlamat  = @isset($_REQUEST['srcAlamat']) ? $_REQUEST['srcAlamat'] :'';
$srcNama  = @isset($_REQUEST['srcNama']) ? $_REQUEST['srcNama'] :'';
$srcNOP  = @isset($_REQUEST['srcNOP']) ? $_REQUEST['srcNOP'] :'';


$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
// $dbWajibPajak = new DbWajibPajak($dbSpec);

$defaultPage 	= 1;
$perpage 		= $appConfig['ITEM_PER_PAGE'];
$totalrows = 1;


// echo '<pre>';
// print_r($data);

//set new page
if(isset($_SESSION['stWP'])){
    if($_SESSION['stWP'] != $tab){
        $_SESSION['stWP'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcAlamat  = '';
        $srcNama  = '';
        $srcNOP  = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
}else{
    $_SESSION['stWP'] = $tab;
}

//echo 'srcNOP ='.$srcNOP;
if ($srcNOP != '' || $srcAlamat !='' || $srcNama != '')
{
	$data = getData();
	$totalrows = $data[25]['JML_ROWS'];
}


displayDataWP();

?>

