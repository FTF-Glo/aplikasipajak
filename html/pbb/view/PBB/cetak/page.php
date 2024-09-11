<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'cetak', '', dirname(__FILE__))) . '/';
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

require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbExistSppt.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");

// 
// echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

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
    global $isSusulan, $kec, $kel, $jumlah, $srch, $almt, $PenilaianParam, $appConfig, $module, $m, $aKecamatan, $a, $dbUtils, $dbGwCurrent;
    echo "<form name=\"mainform\" method=\"post\" enctype=\"multipart/form-data\" >";
    echo "<input type=\"hidden\" name=\"kecamatan\" value=\"".$kel."\">";
    echo "<div class=\"ui-widget consol-main-content\">\n"; 
    echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
    
    echo "\t<table border=0 width=100%><tr><td>";
    echo "<input type=\"button\" value=\"Cetak PDF\" name=\"btn-print\"/ onClick=\"printpreviewdata()\">";
    echo "&nbsp;&nbsp;Masukan Kata Kunci Pencarian <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$selected.",".$selected.");\" id=\"srch-".$selected."\" name=\"srch-".$selected."\" size=\"40\" placeholder=\"NOP/Nama\" value=\"".$srch."\"/>&nbsp;&nbsp;<input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$selected.",".$selected.");\" id=\"almt-".$selected."\" name=\"almt-".$selected."\" size=\"40\" placeholder=\"Alamat\" value=\"".$almt."\"/> <input type=\"button\" onclick=\"setTabs(".$selected.",".$selected.")\" value=\"Cari\" id=\"btn-src\"/>";
	echo "&nbsp;&nbsp;Filter <select name=\"kec\" id=\"kec\" onchange=\"showKel(this)\">";
	echo "<option value=\"\">Kecamatan</option>";
        foreach($aKecamatan as $row) 
			echo "<option value='".$row['CPC_TKC_ID']."' ".((isset($kec) && $kec==$row['CPC_TKC_ID']) ? "selected" : "").">".$row['CPC_TKC_KECAMATAN']."</option>";
            echo "</select>";
            echo "<div id=\"sKel".$selected."\" style=\"margin-left:5px; display:inline-block;\" >";
            echo "            <select name=\"kel\" id=\"kel\" onchange=\"filKel(".$selected.",this)\">";
            echo "        <option value=\"\">".$appConfig['LABEL_KELURAHAN']."</option>";
            echo "            </select>";
            echo "    </div>";
    echo "\t</td><td align=\"right\">";
    echo "</td></tr></table>";
    echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    echo "\t<tr>\n";
    echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\"/></td>\n";
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
            "\t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Nama </td> \n
		 \t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n";
    
	$header = $hBasic;
	 
    return $header;
}

function printData($selected) {
    global $isSusulan;

    $HTML = "";
    $aData = getData($selected);

    $i = 0;

    if (count($aData) > 0)
        foreach ($aData as $data) {
			
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
            $HTML .= parseData($data, $selected, $class);
            $HTML .= "\t</tr>\n";
            $i++;
        }
    return $HTML;
}

function getData($selected) {
    global $dbExistSppt, $srch, $almt, $arConfig, $appConfig, $isSusulan,
    $data, $kec, $kel, $custom, $jumlah, $totalrows, $perpage, $page, $dbUtils, $uid;
	
	//Jika ada keyword pencarian
    
    $perpage = $appConfig['ITEM_PER_PAGE'];
    $filter = array();

    if($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
    if($almt) $filter['CPM_OP_ALAMAT'] = $almt;

    $data = $dbExistSppt->get_where($filter, $srch, $jumlah, $perpage, $page);
    $totalrows = $dbExistSppt->totalrows;
     
    return $data;
}

function kecShow($kode) {
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKecamatanNama($kode);
}
function kelShow($kode) {
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKelurahanNama($kode);
}
	
function parseData($data, $selected, $class) {
    global $arConfig, $appConfig, $a, $m;
	
	$bSlash = "\'";
	$ktip = "'";
	
    //menentukan jenis tampilan, form input atau view biasa
        $params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&idd=" . $data['CPM_SPPT_DOC_ID'] . "&v=" . $data['CPM_SPPT_DOC_VERSION']."&s=". $selected;    
        if ($selected == 10) {
            $params = "a=$a&m=$m&f=" . $arConfig['id_spop'] . "&idd=" . $data['CPM_SPPT_DOC_ID'] . "&v=" . $data['CPM_SPPT_DOC_VERSION'];
        }
        
    $dBasic =
            "\t\t<td class=\"$class\">" . $data['CPM_NOP'] . " </td> \n
                \t\t<td class=\"$class\"> " . $data['CPM_WP_NAMA'] . "</td> \n
                \t\t<td class=\"$class\"> " . $data['CPM_OP_ALAMAT'] . " " . $data['CPM_OP_NOMOR'] . " </td> \n";

    $dTolak =
            "\t\t<td class=\"$class\"> " . $status[$data['CPM_TRAN_STATUS']] . " </td> \n
                \t\t<td class=\"$class\"> " . ((strlen($data['CPM_TRAN_INFO']) > 25) ? "<label class=\"tipclass\" title=\"" . $data['CPM_TRAN_INFO'] . "\">" . substr($data['CPM_TRAN_INFO'], 0, 25) . "...</label>" : $data['CPM_TRAN_INFO']) . " </td> \n";

    $dVerifikasi =
            "\t\t<td class=\"$class\"> " . $status[$data['CPM_TRAN_STATUS']] . " </td> \n";

    $dAdv =
            "\t\t<td class=\"$class\"> ".kecShow($data['CPM_OP_KECAMATAN'])."-".kelShow($data['CPM_OP_KELURAHAN'])." </td> \n
                \t\t<td class=\"$class\"> " . $data['CPM_OPR_NIP'] . " </td> \n";


    $parse = $dBasic;

    return $parse;
}
function paging() {
        global $a,$m,$n,$s,$page,$np,$perpage,$defaultPage,$totalrows;

        $params = "a=".$a."&m=".$m;

        $html = "<div>";
        $row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
        $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
        $html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

        if ($page != 1) {
                $html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','0')\"><span id=\"navigator-left\"></span></a>";
        }
        if ($rowlast < $totalrows ) {
                $html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','1')\"><span id=\"navigator-right\"></span></a>";
        }
        $html .= "</div>";
        return $html;
}

// print_r($_REQUEST);
//mulai program
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$almt 	= @isset($_REQUEST['almt']) ? $_REQUEST['almt'] : "";
$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$jumlah = @isset($_REQUEST['jumlah']) ? $_REQUEST['jumlah'] : "";
$kec 	= @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'],0,7) : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if(isset($_SESSION['stSPOP'])){
    if($_SESSION['stSPOP'] != $s){
        $_SESSION['stSPOP'] = $s;
		$kel = "";
		$kec = "";
        $srch = "";
		$jumlah = 10;
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
$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data = null;
if (strlen(trim($cData)) > 0) {
    $data = $json->decode(base64_decode($cData));
}

$arConfig 		= $User->GetModuleConfig($m);
$appConfig 		= $User->GetAppConfig($a);
$dbUtils 		= new DbUtils($dbSpec);
$dbExistSppt 	= new DbExistSppt($dbSpec);
$dbSppt 		= new DbSppt($dbSpec);

$PenilaianParam = base64_encode('{"ServerAddress":"'.$appConfig['TPB_ADDRESS'].'","ServerPort":"'.$appConfig['TPB_PORT'].'","ServerTimeOut":"'.$appConfig['TPB_TIMEOUT'].'"}');

$defaultPage = 1;
$perpage = $appConfig['ITEM_PER_PAGE'];

$uid = $data->uid;

$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

?>
<script type="text/javascript">
    $(document).ready(function() {
        $( "input:submit, input:button").button();
		
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){
                
                this.checked = $("#all-check-button").is(':checked');
				$('#btn-penilaian').attr('disabled', false);
            });
        });
		$("#all-check-button-tab-ditetapkan").click(function() {
            $('.check-all').each(function(){
                
                this.checked = $("#all-check-button-tab-ditetapkan").is(':checked');
				$('#btn-penilaian').attr('disabled', false);
            });
        });
        $('.check-all').click(function(){ 
			$('#btn-penilaian').attr('disabled', false);
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
        
        <?php
            if($kec != '') {
                echo "showKel2(".$kec.");";
            }
        ?>
                
//        $('fieldset .content').hide();
        $('legend').click(function(){
            $(this).parent().find('.content').slideToggle("slow");
        });
    });
	
	function checkAll(){
		$("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked');
				$('#btn-penilaian').attr('disabled', false);
            });
        });
		$('.check-all').click(function(){ 
			$('#btn-penilaian').attr('disabled', false);
        });
	}
    
    function showKel(x) {
        
        var val = x.value;
        showKel2(val);
    }
    
    function showKel2(val) {
        var s = <?php echo $s ?>;
        <?php foreach($aKecamatan as $row){ ?>
            if(val=="<?php echo $row['CPC_TKC_ID'];?>"){
                document.getElementById('sKel'+s).innerHTML="<?php
                echo "<select name='kel' id='kel' onchange='filKel(".$s.",this);'><option value=''>".$appConfig['LABEL_KELURAHAN']."</option>";
                foreach($aKelurahan as $row2){
                    if($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID'] ){
                        echo "<option value='".$row2['CPC_TKL_ID']."' ".((isset($kel) && $kel==$row2['CPC_TKL_ID']) ? "selected" : "").">".$row2['CPC_TKL_KELURAHAN']."</option>";
                    }
                } echo"</select>"; ?>";
            }
        <?php } ?>
    }
    function printpreviewdata(){
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function(){
            if($(this).is(":checked")){
                x++;
            }
        });
        nop="";
        idx = 0;
        if(x == 0 ) alert ("Pilih data yang akan dicetak!");
        else {
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
                if($(this).is(":checked")){
                    if(idx > 0) nop = nop +",";
                    nop = nop + "'" + $(this).val() + "'";
                    idx++;
                }
            });
             //alert(nop);
            printToPDF(nop);
        }
    }
    
    function printToPDF(nop) {
            var params = {NOP:nop, appID:'<?php echo $a; ?>', tahun:$('#tahun').val()};
            params = Base64.encode(Ext.encode(params));
                    window.open('function/PBB/print-spop.php?req='+params, '_newtab');
    }
</script>
<?php
displayContent($s);
?>
