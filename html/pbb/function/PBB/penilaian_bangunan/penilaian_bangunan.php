<?php
//ini_set("display_errors",1); error_reporting(E_ALL);
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

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($a);
$tahun        = $appConfig['tahun_tagihan'];
$kd_kab        = $appConfig['KODE_KOTA'];
$NBParam     = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');
?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<center>

    <font size="4"><b>Klik Hitung untuk melakukan perhitungan DBKB</b></font> <br><br>
    <table style="border: 1px solid;" width="200" height="50">
        <tr>
            <td align="center">
                <input type="button" style="width: 170px; heigth: 35px;" name="hitung" value="Hitung" id="penilaian-bangunan" />
            </td>
        </tr>
    </table>
</center>
<style type="text/css">
    #load-mask,
    #load-content {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #load-mask {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #load-content {
        z-index: 2;
    }

    #loader {
        margin-right: auto;
        margin-left: auto;
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }
</style>
<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
    </div>
</div>
<div id="load-mask"></div>

<script type="text/javascript">
    $("#penilaian-bangunan").click(function() {

        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();

        loadNB('<?php echo $NBParam ?>');
    });


    function loadNBSuccess(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");

        if (params.responseText) {
            var objResult = Ext.decode(params.responseText);

            if (objResult.RC == "0000") {
                alert('Penilaian sukses.');
                document.location.reload(true);
            } else {
                alert('Gagal melakukan penilaian. Terjadi kesalahan server');
            }
        } else {
            alert('Gagal melakukan penilaian. Terjadi kesalahan server');
        }
    }

    function loadNBFailure(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
    }

    function loadNB(svr_param) {

        var params = "{\"SVR_PRM\":\"" + svr_param + "\", \"TAHUN\":\"<?php echo $tahun; ?>\", \"KABUPATEN\":\"<?php echo $kd_kab; ?>\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penilaian-bangunan.php',
            success: loadNBSuccess,
            failure: loadNBFailure,
            params: {
                req: params
            }
        });

    }
</script>