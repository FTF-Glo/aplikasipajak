<?php
require_once("tab-rekap-kolektif.php");
require_once("tab-status-bayar.php");
// prevent direct access
if (!isset($data)) {
    return;
}

$uid = $data->uid;


// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig = $User->GetAppConfig($application);
$NBParam = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$statusBayar = new StatusBayar($appConfig);
// $realisasi = new Realisasi ($appConfig);
// $realisasiTunggakan = new RealisasiTunggakan ($appConfig);
// $rekapTunggakan = new RekapTunggakan ($appConfig, $NBParam);
// $detailTunggakan = new DetailTunggakan ($appConfig);
// $rekapanMurni = new RekapanMurni ($appConfig);
// $realisasi6Tahun = new Realisasi6Tahun ($appConfig);
// $rekapPenetapan = new RekapPenetapan ($appConfig);
// $penetapan = new Penetapan ($appConfig);
// $rekapKetetapan = new RekapKetetapan ($appConfig);
// $rincianSPPTDHKP = new RincianSPPTDHKP ($appConfig);
// $realisasiDesaKota = new RealisasiDesaKota ($appConfig);
// $rekapNJOP = new RekapNJOP ($appConfig);
// $piutang = new Piutang ($appConfig);
// $penyisihanPiutang = new PenyisihanPiutang ($appConfig);
// $saldoPiutang = new SaldoPiutang ($appConfig);
// $rekapDHKP = new RekapDHKP ($appConfig,$DBLink);
// $rekapPersektor = new RekapPersektor ($appConfig);
// $rekapPersektorDetail = new RekapPersektorDetail ($appConfig);


//prevent access to not accessible module
if (!$bOK) {
    return false;
}


if (!isset($opt)) {
?>
    <link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
    <!--    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>-->
    <!-- <script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script> -->

    <script>
        var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
        var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
        var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
        var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
        var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
        var LBL_KEL = '<?php echo $appConfig['LABEL_KELURAHAN']; ?>';
        var THN_TAGIHAN = '<?php echo $appConfig['tahun_tagihan']; ?>';

        $(document).ready(function() {
            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            })
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

            $("#jatuh-tempo2-col-1").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#jatuh-tempo1-col-1").datepicker({
                dateFormat: "yy-mm-dd"
            });

            $("#piutang-tgl-bayar-awal").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#piutang-tgl-bayar-akhir").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#periode-1").datepicker({
                dateFormat: "yy-mm-dd"
            });
            $("#periode-2").datepicker({
                dateFormat: "yy-mm-dd"
            });
        });

        function updateCount() {
            var tahun = $("#tahun-pajak-" + 1).val();
            $("#ketAkm").html('<span style="font-size: 12px;">Loading...</span>');
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring_pembayaran_kolektif/svc-count.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    var vcount = msg.split("/");
                    $("#ketAkm").html('<span style="font-size: 13px">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
                }
            });
        }


        function setPage(pg, sts) {
            var tempo1 = $("#jatuh-tempo1-" + sts).val();
            var tempo2 = $("#jatuh-tempo2-" + sts).val();
            var tahun = $("#tahun-pajak-" + sts).val();
            var nop = $("#nop-" + sts).val();
            var nama = $("#wp-name-" + sts).val();
            var jmlBaris = $("#jml-baris").val();
            var kc = $("#kecamatan-" + sts).val();
            var kl = $("#kelurahan-" + sts).val();
            var tagihan = $("#src-tagihan-" + sts).val();
            var bank = $("#bank-" + sts).val();
            var kolektif = $("#kd_kolektif").val();

            $("#monitoring-content-" + sts).html("loading ...");

            var svc = "";
            $("#monitoring-content-" + sts).load("view/PBB/monitoring_pembayaran_kolektif/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
                kolektif: kolektif,
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

        function showKelurahan(sts) {
            var id = $('select#kecamatan-' + sts).val()
            var request = $.ajax({
                url: "view/PBB/monitoring_pembayaran_kolektif/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: id,
                    kel: 1
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kelurahan-" + sts).html(options);
                    }
                }
            });
        }

        function showBank() {
            var request = $.ajax({
                url: "view/PBB/monitoring_pembayaran_kolektif/svc-bank.php",
                type: "POST",
                // data: {id: "<?php echo $appConfig['KODE_KOTA'] ?>"},
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].CDC_B_ID + '">' + data.msg[i].CDC_B_NAME + '</option>';
                    }
                    $("select#bank-1").html(options);
                }
            });

        }

        function showBankVA() {
            var request = $.ajax({
                url: "view/PBB/monitoring_pembayaran_kolektif/svc-bank-va.php",
                type: "POST",
                // data: {id: "<?php echo $appConfig['KODE_KOTA'] ?>"},
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].CDC_VB_BANK_ID + '">' + data.msg[i].CDC_VB_NAME + '</option>';
                    }
                    // accessible
                    // $("select#bank-1").html(options);
                    $("select#bank-col-1").html(options);
                }
            });

        }

        $(function() {
            $("#tabs").tabs();
        });

        function showKecamatan(sts) {
            var request = $.ajax({
                url: "view/PBB/monitoring_pembayaran_kolektif/svc-kecamatan.php",
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
                        $("select#kecamatan-" + sts).html(options);
                    }
                }
            });

        }

        function showKecamatanAll() {
            var request = $.ajax({
                url: "view/PBB/monitoring_pembayaran_kolektif/svc-kecamatan.php",
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
                    $("select#kecamatan-realisasi-tunggakan").html(options);
                    $("select#kecamatan-rekap-tunggakan").html(options);
                    $("select#kecamatan-detail-tunggakan").html(options);
                    $("select#kecamatan-rekapan-murni").html(options);
                    $("select#kecamatan-realisasi6tahun").html(options);
                    $("select#kecamatan-rincian-sppt-dhkp").html(options);
                    $("select#kecamatan-penetapan").html(options);
                    $("select#kecamatan-piutang").html(options);
                    $("select#kecamatan-penyisihan-piutang").html(options);
                    $("select#kecamatan-saldo-piutang").html(options);
                    $("select#kecamatan-rekap-dhkp").html(options);
                    $("select#kecamatan-col-1").html(options);
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

        function excel_export(id) {
            var content = $(id).html();
            window.open('data:application/vnd.ms-excel,' + encodeURIComponent(content));
        }

        function excel_export_e2() {
            var tahun = $("#tahun-pajak-3").val();
            var kecamatan = $("#kecamatan-3").val();
            var namakec = $("#kecamatan-3 option:selected").text();
            var sts = 1;

            window.open("view/PBB/monitoring_pembayaran_kolektif/svc-toexcel-e2.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); 
                                                                                        ?>" + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT);
        }

        $(document).ready(function() {
            showBank();
            showBankVA();
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
        <div id="div-search" style="padding-top: 50px;">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1"><?php echo $statusBayar->sudahBayarLabel; ?></a></li>
                    <li><a href="#tabs-2">Rekap Pembayaran Kolektif</a></li>
                </ul>
                <div id="tabs-1">
                    <?php $statusBayar->printFromSudahBayar($a, $m, $uid); ?>
                </div>
                <div id="tabs-2">
                    <?php
                    $format2018 = new Penetapan($appConfig);
                    $format2018->printForm($a, $m, $uid);
                    ?>
                </div>

            </div>
        </div>
    <?php
}
    ?>
    <div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 0; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
        <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
            <div style="float: left;">
                <span style="font-size: 12px;">Link Download</span>
            </div>
            <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
        </div>
        <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
    </div>


    <div id="cAkumulasi" style="position: absolute; top: 0; right: 23px; display: block; overflow: auto;">
        <div style="float: left; padding-top: 5px;" id="ketAkm"><span style="font-size: 13px; color:#fff">Tahun Berjalan (<b>0</b>) + Tunggakan (<b>0</b>) = Total Pembayaran (<b>0</b>)</span></div>&nbsp;&nbsp;
        <input style="float: right;" type="button" class="btn btn-default btn-orange" name="updateCount" id="updateCount" value="Update" onClick="updateCount()" />
    </div>
    </div>

    <script language="javascript">
        $("input:submit, input:button").button();
        $(document).ready(function() {
            var tahun = $("#tahun-pajak-1").val();
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring_pembayaran_kolektif/svc-count.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    var vcount = msg.split("/");
                    $("#ketAkm").html('<span style="font-size: 13px; color:#000000">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
                }
            });
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring_pembayaran_kolektif/svc-tglproseshitungdenda.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    $("#ketHitungDenda").html('<span style="font-size: 13px; color:#000000">Terakhir diproses pada ' + msg + '</span>');
                }
            });


            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            })
        })
    </script>

    <div id="load-content">
        <div id="loader">
            <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
        </div>
    </div>
    <div id="load-mask"></div>