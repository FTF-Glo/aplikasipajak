<?php
// echo "123";
// exit;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
require_once("tab-status-bayar.php");
require_once("tab-realisasi.php");
require_once("tab-piutang.php");
require_once("tab-rekap-ketetapan.php");
require_once("tab-rangking-realisasi.php");
require_once("dbMonitoring.php");
require_once("tab-penerimaan-harian.php");
// require_once($sRootPath . "inc/PBB/dbMonitoring.php");

// prevent direct access
if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK        = $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig  = $User->GetAppConfig($application);
$idRole     = $User->GetUserRole($uid, $application);
$NBParam    = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');

$dbMonitoring       = new dbMonitoring(ONPAYS_DBHOST, '3306', ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
//Penentuan kode kecamatan
$dtUser             = $dbMonitoring->getKdKecUser($uid);
$statusBayar        = new StatusBayar($appConfig, $idRole, $dtUser);
$realisasi          = new Realisasi($appConfig, $idRole, $dtUser);
$rekapKetetapan     = new RekapKetetapan($appConfig, $idRole, $dtUser);
$piutang            = new Piutang($appConfig, $idRole, $dtUser);
$rangking           = new RangkingRealisasi($appConfig, $idRole, $dtUser);
$penerimaanHarian   = new PenerimaanHarian($appConfig, $idRole, $dtUser);

// $realisasiTunggakan = new RealisasiTunggakan ($appConfig);

// print_r($dtUser);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

if (!isset($opt)) {
?>
    <link href="view/PBB/monitoring/monitoring.css?v.0.0.0.1" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
    <script>
        var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
        var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
        var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
        var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
        var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
        var LBL_KEL = '<?php echo $appConfig['LABEL_KELURAHAN']; ?>';
        var THN_TAGIHAN = '<?php echo $appConfig['tahun_tagihan']; ?>';
        var IDKEC = '<?php echo $dtUser['kecamatan']; ?>';
        var IDKEL = '<?php echo $dtUser['kelurahan']; ?>';
        var ROLE = '<?php echo $idRole; ?>';
        var APPID = '<?php echo $application; ?>';

        $(document).ready(function() {
            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            });
            $("#carinop-7").change(function() {
                console.log("inout scan");
            });
        })

        $(function() {
            $("#jatuh-tempo1-1").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#jatuh-tempo2-1").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#jatuh-tempo1-2").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#jatuh-tempo2-2").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#piutang-tgl-bayar-awal").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#piutang-tgl-bayar-akhir").datepicker({
                dateFormat: "yy-mm-dd"
            });
        });

        // function updateCount() {
        // var tahun = $("#tahun-pajak-" + 1).val();
        // $("#ketAkm").html('<span style="font-size: 12px;">Loading...</span>');
        // $.ajax({
        // type: "POST",
        // url: "./view/PBB/monitoring/svc-count.php",
        // data: "q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); 
                    ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
        // success: function (msg) {
        // var vcount = msg.split("/");
        // $("#ketAkm").html('<span style="font-size: 13px">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
        // }
        // });
        // }


        function setPage(pg, sts) {
            var tempo1 = $("#jatuh-tempo1-" + sts).val();
            var tempo2 = $("#jatuh-tempo2-" + sts).val();
            var tahun = $("#tahun-pajak-" + sts).val();
            if (sts == '7') {
                var tahun2 = $("#tahun-pajak2-" + sts).val();
            }
            var nop = $("#nop-" + sts).val();
            var nama = $("#wp-name-" + sts).val();
            var jmlBaris = $("#jml-baris").val();
            var kc = $("#kecamatan-" + sts).val();
            var kl = $("#kelurahan-" + sts).val();
            var tagihan = $("#src-tagihan-" + sts).val();
            var bank = $("#bank-" + sts).val();
            $("#monitoring-content-" + sts).html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");

            var svc = "";

            if (sts == '7') {
                //   alert("export 7");
                $("#monitoring-content-" + sts).load("view/PBB/monitoring_wilayah/svc-monitoring-dph.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
                    na: nama,
                    t1: tempo1,
                    t2: tempo2,
                    th: tahun,
                    th2: tahun2,
                    n: nop,
                    st: sts,
                    kc: kc,
                    kl: kl,
                    tagihan: tagihan,
                    bank: bank,
                    GW_DBHOST: GW_DBHOST,
                    GW_DBNAME: GW_DBNAME,
                    GW_DBUSER: GW_DBUSER,
                    GW_DBPWD: GW_DBPWD,
                    GW_DBPORT: GW_DBPORT,
                    LBL_KEL: LBL_KEL
                }, function(response, status, xhr) {
                    if (status == "error") {
                        var msg = "Sorry but there was an error: ";
                        $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
                    }
                });
            } else {
                $("#monitoring-content-" + sts).load("view/PBB/monitoring_wilayah/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
                    na: nama,
                    t1: tempo1,
                    t2: tempo2,
                    th: tahun,
                    n: nop,
                    st: sts,
                    kc: kc,
                    kl: kl,
                    tagihan: tagihan,
                    bank: bank,
                    GW_DBHOST: GW_DBHOST,
                    GW_DBNAME: GW_DBNAME,
                    GW_DBUSER: GW_DBUSER,
                    GW_DBPWD: GW_DBPWD,
                    GW_DBPORT: GW_DBPORT,
                    LBL_KEL: LBL_KEL
                }, function(response, status, xhr) {
                    if (status == "error") {
                        var msg = "Sorry but there was an error: ";
                        $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
                    }
                });
            }
        }

        function showKelurahan(sts) {
            var id = $('select#kecamatan-' + sts).val();
            var mode = 1;
            if (id == undefined) {
                if (ROLE == 'rmKecamatan') {
                    id = IDKEC;
                } else if (ROLE == 'rmKelurahan') {
                    id = IDKEL;
                    mode = 2;
                }
            }
            var request = $.ajax({
                url: "view/PBB/monitoring_wilayah/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: id,
                    kel: mode
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    if (mode == 1) options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kelurahan-" + sts).html(options);
                    }
                }
            });
        }

        function showRW(sts) {
            var id = IDKEL;
            var appID = APPID;
            var request = $.ajax({
                url: "view/PBB/monitoring_wilayah/svc-rw.php",
                type: "POST",
                data: {
                    id: id,
                    appID: appID,
                    sts: sts
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].RW + '">' + data.msg[i].RW + '</option>';
                        $("select#rw-" + sts).html(options);
                    }
                }
            });
        }

        $(function() {
            $("#tabs").tabs();
        });

        function showKecamatan(sts) {
            var mode = 1;
            if (ROLE == 'rmKecamatan') {
                id = IDKEC;
                mode = 2;
            } else if (ROLE == 'rmKabupaten') {
                id = "<?php echo $appConfig['KODE_KOTA'] ?>";
            }
            var request = $.ajax({
                url: "view/PBB/monitoring_wilayah/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: id,
                    mode: mode
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    if (mode == 1) options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kecamatan-" + sts).html(options);
                    }
                }
            });

        }

        function showKecamatanAll() {
            var request = $.ajax({
                url: "view/PBB/monitoring_wilayah/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: "<?php echo $appConfig['KODE_KOTA'] ?>"
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                    }
                    $("select#kecamatan-1").html(options);
                    $("select#kecamatan-2").html(options);
                    $("select#kecamatan-realisasi").html(options);
                    $("select#kecamatan-ketetapan").html(options);
                    $("select#kecamatan-piutang").html(options);
                    $("select#kecamatan-rangking-realisasi").html(options);
                }
            });

        }

        function showBank(sts) {
            var request = $.ajax({
                url: "view/PBB/monitoring_wilayah/svc-bank.php",
                type: "POST",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].CDC_B_ID + '">' + data.msg[i].CDC_B_NAME + '</option>';
                    }
                    $("select#bank-" + sts).html(options);
                }
            });

        }

        function showBulan(id) {
            var bulan = Array("Semua", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
            var options = '';
            for (var i = 0; i < 13; i++) {
                options += '<option value="' + i + '">' + bulan[i] + '</option>';
                $("select#" + id).html(options);
            }
        }

        // function excel_export(id) {
        // var content = $(id).html();
        // window.open('data:application/vnd.ms-excel,' + encodeURIComponent(content));
        // }

        // function excel_export_e2() {
        // var tahun = $("#tahun-pajak-3").val();
        // var kecamatan = $("#kecamatan-3").val();
        // var namakec = $("#kecamatan-3 option:selected").text();
        // var sts = 1;

        // window.open("view/PBB/monitoring/svc-toexcel-e2.php?q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); 
                                                                    ?>" + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT);
        // }

        $(document).ready(function() {
            // if (ROLE == 'rmKabupaten') showKecamatanAll();
            showKecamatanAll();
            showBulan("bulan-1");
            showBulan("bulan-2");
            $('#tabs').tabs({
                select: function(event, ui) { // select event
                    $(ui.tab); // the tab selected
                    if (ui.index == 2) {
                        //showModelE2();
                    }
                }
            });
        });
    </script>

    <div class="col-md-12" style="position: relative;">
        <div id="div-search">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1"><?php echo $statusBayar->sudahBayarLabel; ?></a></li>
                    <li><a href="#tabs-2"><?php echo $statusBayar->belumBayarLabel; ?></a></li>
                    <li><a href="#tabs-3"><?php echo $realisasi->label; ?></a></li>
                    <li><a href="#tabs-4"><?php echo $rekapKetetapan->label; ?></a></li>
                    <li><a href="#tabs-5"><?php echo $piutang->label; ?></a></li>
                    <li><a href="#tabs-6"><?php echo $rangking->label; ?></a></li>
                </ul>
                <div id="tabs-1">
                    <?php $statusBayar->printFromSudahBayar($a, $m, $uid); ?>
                </div>
                <div id="tabs-2">
                    <?php $statusBayar->printFormBelumBayar($a, $m, $uid); ?>
                </div>
                <div id="tabs-3">
                    <?php $realisasi->printForm($a, $m, $uid); ?>
                </div>
                <div id="tabs-4">
                    <?php $rekapKetetapan->printForm($a, $m, $uid); ?>
                </div>
                <div id="tabs-5">
                    <?php $piutang->printForm($a, $m, $uid); ?>
                </div>
                <div id="tabs-6">
                    <?php $rangking->printForm($a, $m, $uid); ?>
                </div>
            </div>
        </div>
    <?php
}
    ?>
    <div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
        <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
            <div style="float: left;">
                <span style="font-size: 12px;">Link Download</span>
            </div>
            <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
        </div>
        <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
    </div>

    <div id="dBox" style="width: 400px; height: 300px; position: absolute; right: 50px; top: 0; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
        <div style="overflow: auto; background-color: #c0c0c0; width: 100%; padding: 3px;">
            <div style="float: left;">
                <span style="font-size: 12px;">List DPH Tersimpan</span>
            </div>
            <div id="closeDBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
        </div>
        <div>
            <table id="contentDph1" style="padding: 3px;overflow: auto;">
                <tr>
                    <td width="40px" align="center">No</td>
                    <td width="150px;">Nama File</td>
                    <td width="100px">Tanggal</td>
                    <td width="100px" align="center">Aksi</td>
                </tr>
            </table>
        </div>
    </div>


    </body>

    <script language="javascript">
        $("input:submit, input:button").button();
        $(document).ready(function() {
            var tahun = $("#tahun-pajak-1").val();
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring/svc-count.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    var vcount = msg.split("/");
                    $("#ketAkm").html('<span style="font-size: 13px; color:#000000">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
                }
            });
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring/svc-tglproseshitungdenda.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    $("#ketHitungDenda").html('<span style="font-size: 13px; color:#000000">Terakhir diproses pada ' + msg + '</span>');
                }
            });


            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            });
            $("#closeDBox").click(function() {
                $("#dBox").css("display", "none");
            });
        })
    </script>

    <div id="load-content">
        <div id="loader">
            <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
        </div>
    </div>
    <div id="load-mask"></div>