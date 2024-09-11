<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");


echo "<script type=\"text/javascript\" src=\"function/PBB/consol/script.js\"></script>";
echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

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
function showKec(){
	global $aKecamatan, $kec;
	foreach($aKecamatan as $row)  
			echo "<option value='".$row['CPC_TKC_ID']."' ".((isset($kec) && $kec==$row['CPC_TKC_ID']) ? "selected" : "").">".$row['CPC_TKC_KECAMATAN']."</option>";
}

function displayContent($selected) {
	global $jumhal,$srch,$aKecamatan,$a,$m,$appConfig,$arConfig;
	$params 	= "a=".$a."&m=".$m;
	$startLink 	= "<a style='text-decoration: none;' href=\"main.php?param=".base64_encode($params."&f=".$arConfig['form_input_jadwal'])."\">";
	$endLink 	= "</a>";
	//echo $selected;
    echo "<form name=\"mainform\" method=\"post\">";
    echo "<div class=\"ui-widget consol-main-content\">\n";
    echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
    echo "\t<table border=0><tr><td>";
    if ($selected == 10) {
        echo "$startLink<input type=\"button\" value=\"Tambah\" name=\"btnTambah\"/>$endLink&nbsp";
		// echo "<input type=\"button\" value=\"Ubah\" name=\"btnUbah\" id=\"btnUbah\"/>&nbsp";
		echo "<input type=\"button\" value=\"Hapus\" name=\"btnHapus\" id=\"btnHapus\"/>&nbsp";
		echo "<input type=\"button\" value=\"Cetak Surat Pemanggilan\" name=\"btnCetak\" id=\"btnCetak\"/>&nbsp";
	} 
    echo " Pencarian <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$selected.",".$selected.");\" id=\"srch-".$selected."\" name=\"srch-".$selected."\" placeholder=\"Nomor/Nama/NOP\" size=\"60\"/> <input type=\"button\" onclick=\"setTabs(".$selected.",".$selected.")\" value=\"Cari\" id=\"btn-src\"/>";
    echo "</td></tr></table>";
    echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\"width=\"100%\">\n";
    echo "\t<tr>\n";
    // if ($selected == 10) {
        // echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>\n";
    // } else {
        echo "\t\t<td width=\"20\" class=\"tdheader\">&nbsp;</td>\n";
    // }
	echo createHeader($selected);
    echo "\t</tr>\n";
    echo printData($selected);
    echo "</table>\n";
    echo "\t</div>\n";
	echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";		
    echo "\t\t</div>\n";
	echo "\t\t<div style=\"float:right\">".paging()."</div>\n";
    echo "\t</div>\n";
    echo "</div>\n";
    echo "</form>\n";
}

function createHeader($selected) {
    global $appConfig;
    
    //variable header set
    $hBasic =
            "\t\t<td class=\"tdheader\"> Nomor </td> \n
		 \t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Nama WP </td> \n
		 \t\t<td class=\"tdheader\"> PBB Terhutang </td> \n";

    $header = $hBasic;
    
    return $header;
}

function printData($selected) {
  global $isSusulan,$a,$m,$arConfig;;
	$params 	= "a=".$a."&m=".$m;
    $HTML 		= "";
    $aData 		= getData($selected);
	// echo "<pre>";
	// print_r($aData);
	$c = count($aData);
	// echo $aData[0]['SP_NAMA_WP'];
    if ($c > 0)
        for($i=0;$i<$c;$i++) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<tr>\n";
            if ($selected == 10) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"".$aData[$i]['SP_NOMOR']."+".$aData[$i]['SP_NOP']."\" /></td>\n";
            } else {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
            }
            $HTML .=
				"\t\t<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params."&f=".$arConfig['form_input_jadwal']."&mode=edit&svcid=".$aData[$i]['SP_NOMOR']) . "'>" . $aData[$i]['SP_NOMOR'] . "</a> </td> \n
				 \t\t<td class=\"$class\"> " . $aData[$i]['SP_NOP'] . "</td> \n
				 \t\t<td class=\"$class\"> " . $aData[$i]['SP_NAMA_WP'] . "</td> \n
				 \t\t<td align=\"right\" class=\"$class\"> " . number_format($aData[$i]['SP_TAGIHAN'],0,',','.'). "</td> \n";
            $HTML .= "\t</tr>\n";
            // $i++;
        }
    return $HTML;
}

function getData($selected) {
		global $GWDBLink, $srch, $arConfig, $appConfig, $data, $kec, $custom, $jumhal, $totalrows, $perpage, $page, $kel;

	$perpage = $appConfig['ITEM_PER_PAGE'];
    $query 	= "SELECT * FROM pbb_sppt_pemanggilan ";
	$res 	= mysqli_query($GWDBLink, $query);
	$data 	= array();
	if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
        }
        return $data;
    }
} 

function paging() {
		global $a,$m,$n,$s,$page,$np,$perpage,$defaultPage,$totalrows;
		
		//$params = "a=".$a."&m=".$m;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}

//mulai program
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$jumhal = @isset($_REQUEST['jumhal']) ? $_REQUEST['jumhal'] : "";
$kec 	= @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'],0,7) : "";


$q = base64_decode($q);
$q = $json->decode($q);
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if(isset($_SESSION['stSPOP'])){
    if($_SESSION['stSPOP'] != $s){
        $_SESSION['stSPOP'] = $s;
		$kec = "";
		$kel = "";
        $srch = "";
		$jumhal = 10;
		$page = 1;
		$np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
    }
}else{
    $_SESSION['stSPOP'] = $s;
}	

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink 	= $User->GetDbConnectionFromApp($a);
$dbSpec 	= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

/* === Get cookie data === */
$cData 	= (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data 	= null;
if (strlen(trim($cData)) > 0) {
    $data = $json->decode(base64_decode($cData));
}

$arConfig 		= $User->GetModuleConfig($m);
$appConfig 		= $User->GetAppConfig($a);
$dbServices 	= new DbServices($dbSpec);
$defaultPage 	= 1;

$uid = $data->uid;
//$userArea = $dbUtils->getUserDetailPbb($uid);

$C_HOST_PORT 	= $appConfig['GW_DBHOST'];
$C_USER 		= $appConfig['GW_DBUSER'];
$C_PWD 			= $appConfig['GW_DBPWD'];
$C_DB 			= $appConfig['GW_DBNAME'];

$GWDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB) or die(mysqli_error($DBLink));
//mysql_select_db($C_DB,$GWDBLink);

?>
<script type="text/javascript">
	var sel = "<?php echo $s; ?>";
	var sts = "<?php echo $s; ?>";
    $(document).ready(function() {
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });
        $(".tipclass").tooltip({
            track: false,
            delay: 0,
            showBody: " - ",
            bodyHandler: function() { 
                var value = $(this)[0].tooltipText.replace(/\n/g, '<br />');
                return value;
            },
            fade: 250,
            extraClass: "fix",
            opacity: 0 
        })
		
		// $("#btnUbah").click(function(){
			// var arrNo = [];
			// x=0;
            // $("input:checkbox[name='check-all\\[\\]']").each(function(){
				// if($(this).is(":checked")){
					
					// // alert($(this).val());
					// arrNo.push($(this).val());
					// x++;
				// }
			// });
			// if(x==0){
				// alert ("Belum ada data yang dipilih!");
			// } else {
				// var r = confirm("Anda yakin akan menghapus data dengan Nomor "+arrNo+"?");
				// if (r == true) {
					// delDataPemanggilan(arrNo);
				// } 
			// }
			
			
        // });
		
		 $("#btnHapus").click(function(){
			var arrNo = [];
			x=0;
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
				if($(this).is(":checked")){
					
					// alert($(this).val());
					arrNo.push($(this).val());
					x++;
				}
			});
			if(x==0){
				alert ("Belum ada data yang dipilih!");
			} else {
				var r = confirm("Anda yakin akan menghapus data dengan Nomor "+arrNo+"?");
				if (r == true) {
					delDataPemanggilan(arrNo);
				} 
			}
			
			
        });
		
		$("#btnCetak").click(function(){
			
			x=0;
			
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
				if($(this).is(":checked")){
					printSuratPemanggilan($(this).val());
					x++;
				}
			});
			if(x==0){
				alert ("Belum ada data yang dipilih!");
			}
        });
		
    });
		
	function delDataPemanggilan(nomor){
		//alert(nop);
		$.ajax({
            type: "POST",
            url: "./function/PBB/penagihan/svc-del-datpemanggilan.php",
            data: "nomor="+nomor+"&GW_DBHOST=<?php echo $appConfig['GW_DBHOST']?>&GW_DBUSER=<?php echo $appConfig['GW_DBUSER']?>&GW_DBPWD=<?php echo $appConfig['GW_DBPWD']?>&GW_DBNAME=<?php echo $appConfig['GW_DBNAME']?>",
            success: function(msg){
                // alert(msg);
				console.log(msg)
                   setTabs(sel,sts);
               }
        });
	}
	
	function printSuratPemanggilan(id) {
		var params = {svcId:id, appId:'<?php echo $a; ?>'};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
			window.open('./function/PBB/penagihan/svc-print-srtpemanggilan.php?q='+params, '_blank');
	}
		
	
</script>
<?php
displayContent($s);
?>
