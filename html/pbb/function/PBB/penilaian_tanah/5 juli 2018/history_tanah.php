<?php
if (!isset($data)) {
    die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
    $terminalColumn = $arAreaConfig['terminalColumn'];
    $accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
    if (!$accessible) {
        echo "Illegal access";
        return;
    }
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_tanah', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/PBB/dbTanah.php");

$appConfig = $User->GetAppConfig($application);
$dbTanah = new DbTanah($dbSpec);
?>

<script type="text/javascript">
    function confirmProses() {
        return confirm('Anda yakin untuk memproses pembuatan history dan update tahun ini ?')
    }
</script>

<h4>History Master Tanah</h4>
<form method="post">
    <br/>
    Pembuatan history tahun sebelumnya (<?php echo $appConfig['tahun_tagihan'] - 1 ?>) dan update data tahun sekarang (<?php echo $appConfig['tahun_tagihan'] ?>) untuk seluruh data penilaian tanah
    <br/>
    <input type="submit" name="doAct" value="Proses" onclick="return confirmProses();">
</form>

<?php
if (isset($_REQUEST['doAct'])) {
    $rZnt = $dbTanah->goHistoryZnt($appConfig['tahun_tagihan'] - 1);
    $rBumi = $dbTanah->goHistoryKelasBumi($appConfig['tahun_tagihan'] - 1);
    $rBlok = $dbTanah->goHistoryBlok($appConfig['tahun_tagihan'] - 1);

    echo "<ul>";
    echo "<li>$rZnt data Znt di salin</li>";
    echo "<li>$rBumi data Kelas Bumi di salin</li>";
    echo "<li>$rBlok data Blok di salin</li>";
    echo "</ul>";
}
?>