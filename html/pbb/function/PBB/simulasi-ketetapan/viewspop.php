<?
//echo mysqli_error($DBLink); 
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (!isset($data)) {
    die("Forbidden direct access");
}

if (!$User) {
    die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
    die("Function access not permitted");
}
require_once("inc/payment/uuid.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("inc/PBB/dbFinalSppt.php");
require_once("inc/PBB/dbSpptHistory.php");
require_once("function/PBB/gwlink.php");
require_once("inc/PBB/dbUtils.php");
require_once("inc/PBB/dbServices.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbServices = new DbServices($dbSpec);

// print_r($arConfig);

// echo "<pre>"; print_r($_REQUEST); echo "</pre>";

//Preparing Parameters
$idd 	= $_REQUEST['doc_id'];
$v 		= $_REQUEST['doc_vers'];


if (isset($idd) || isset($v)) {
    $docVal = $dbSppt->get($idd, $v);
    if ($docVal == null) {
        $docVal = $dbFinalSppt->get($idd, $v);
        if ($docVal == null) {
            $docVal = $dbFinalSppt->getSusulan($idd, $v);
        }
    }

    $aDocExt = $dbSpptExt->get($idd, $v);
    if ($aDocExt == null) {
        $aDocExt = $dbFinalSppt->getExt($idd, $v);
        if ($aDocExt == null) {
            $aDocExt = $dbFinalSppt->getExtSusulan($idd, $v);
        }
    }

    foreach ($docVal[0] as $key => $value) {
        $tKey = substr($key, 4);
        $$tKey = $value;
    }

    if (isset($aDocExt)) {
        $HtmlExt = "";
        foreach ($aDocExt as $docExt) {
            $param = "a=$a&m=$m&f=" . $arConfig['id_view_lampiran'] . "&idd=$idd&v=$v&num=" . $docExt['CPM_OP_NUM'];
            $HtmlExt .= "<li><a href='main.php?param=" . base64_encode($param) . "'>Lampiran Bangunan " . $docExt['CPM_OP_NUM'] . "</a></li>";
        }
    }
    
    if($NOP_BERSAMA != ''){ 
        $docAnggota = $dbSppt->getAnggota($NOP_BERSAMA, $NOP);
        foreach ($docAnggota[0] as $key => $value) {
            $tKey = substr($key, 4);
            $$tKey = $value;
        }
    }
}
$aOPKabKota = $dbUtils->getKabKota($OP_KOTAKAB);
$aOPKecamatan = $dbUtils->getKecamatan($OP_KECAMATAN);
$aOPKelurahan = $dbUtils->getKelurahan($OP_KELURAHAN);
//$aWPKabKota = $dbUtils->getKabKota($WP_KOTAKAB); 
//$aWPKecamatan = $dbUtils->getKecamatan($WP_KECAMATAN);
//$aWPKelurahan = $dbUtils->getKelurahan($WP_KELURAHAN);

echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">";

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";
?>

<script type="text/javascript">
$(document).ready(function() {
    $( "input:submit, input:button").button();
});
</script>
<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">

<?

$NBParam = base64_encode('{"ServerAddress":"'.$appConfig['TPB_ADDRESS'].'","ServerPort":"'.$appConfig['TPB_PORT'].'","ServerTimeOut":"'.$appConfig['TPB_TIMEOUT'].'"}');
$tahun_tagihan = $appConfig['tahun_tagihan'];
include("viewtmp.php");
?>
