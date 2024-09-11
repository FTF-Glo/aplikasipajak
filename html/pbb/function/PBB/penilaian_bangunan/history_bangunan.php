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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_bangunan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/PBB/dbBangunan.php");

$appConfig = $User->GetAppConfig($application);
$dbBangunan = new DbBangunan($dbSpec);

$yearNow = $appConfig['tahun_tagihan'];
$yearBack = $appConfig['tahun_tagihan'] - 1;
?>

<script type="text/javascript">
    function confirmProses() {
        return confirm('Anda yakin untuk memproses pembuatan history dan update tahun ini ?')
    }
</script>

<h4>History Master Bangunan</h4>
<form method="post">
    <br/>
    Pembuatan history tahun sebelumnya (<?php echo $yearBack ?>) dan update data tahun sekarang (<?php echo $yearNow ?>) untuk seluruh data penilaian bangunan
    <br/>
    <input type="submit" name="doAct" value="Proses" onclick="return confirmProses();">
</form>

<?php
if (isset($_REQUEST['doAct'])) {
    $rResGroup = $dbBangunan->goHistoryBangunan('resource_group', $yearBack);
    $rResItem = $dbBangunan->goHistoryBangunan('resource_item', $yearBack);
    $rResHarga = $dbBangunan->goHistoryBangunan('resource_harga', $yearBack, $yearNow);
    $rKelasBangunan = $dbBangunan->goHistoryBangunan('kelas_bangunan', $yearBack);
    $rPekerjaan = $dbBangunan->goHistoryBangunan('pekerjaan', $yearBack);
    $rKegiatan = $dbBangunan->goHistoryBangunan('kegiatan', $yearBack);
    $rKegVol = $dbBangunan->goHistoryBangunan('kegiatan_resource_volume', $yearBack);
    $rKegHarga = $dbBangunan->goHistoryBangunan('kegiatan_harga', $yearBack, $yearNow);
    $rBangunan = $dbBangunan->goHistoryBangunan('bangunan', $yearBack);
    $rBangunanKegVol = $dbBangunan->goHistoryBangunan('bangunan_kegiatan_volume', $yearBack);
    $rBangunanKegHarga = $dbBangunan->goHistoryBangunan('bangunan_kegiatan_harga', $yearBack, $yearNow);
    

    echo "<ul>";
    echo "<li>$rResGroup data Resource Group di salin</li>";
    echo "<li>$rResItem data Resource Item di salin</li>";
    echo "<li>$rResHarga data Resource Harga di salin</li>";
    echo "<li>$rKelasBangunan data Kelas Bangunan di salin</li>";
    echo "<li>$rPekerjaan data Pekerjaan di salin</li>";
    echo "<li>$rKegiatan data Kegiatan di salin</li>";
    echo "<li>$rKegVol data Kegiatan Resource Volume di salin</li>";
    echo "<li>$rKegHarga data Kegiatan Harga di salin</li>";
    echo "<li>$rBangunan data Bangunan di salin</li>";
    echo "<li>$rBangunanKegVol data Bangunan Kegiatan Volume di salin</li>";
    echo "<li>$rBangunanKegHarga data Bangunan Kegiatan Harga di salin</li>";
    echo "</ul>";
}
?>