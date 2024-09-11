<?php
require_once("tab-status-bayar.php");
require_once("tab-realisasi.php");
require_once("tab-realisasi-tunggakan.php");
require_once("tab-rekap-tunggakan.php");
require_once("tab-detail-tunggakan.php");
require_once("tab-rekapan-murni.php");
require_once("tab-realisasi-6tahun.php");
require_once("tab-rekap-penetapan.php");
require_once("tab-penetapan.php");
require_once("tab-rekap-ketetapan.php");
require_once("tab-rincian-sppt-dhkp.php");
require_once("tab-realisasi-desa-kota.php");
require_once("tab-rekap-njop.php");
require_once("tab-piutang.php");
require_once("tab-penyisihan-piutang.php");
require_once("tab-saldo-piutang.php");
require_once("tab-rekap-dhkp.php");
require_once("tab-detail-pembayaran.php");
require_once("tab-op-ringkas.php");
require_once("tab-report-njop.php");
require_once("tab-rekap-pelayanan.php");

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
$realisasi = new Realisasi($appConfig);
$realisasiTunggakan = new RealisasiTunggakan($appConfig);
$rekapTunggakan = new RekapTunggakan($appConfig, $NBParam);
$detailTunggakan = new DetailTunggakan($appConfig);
$rekapanMurni = new RekapanMurni($appConfig);
$realisasi6Tahun = new Realisasi6Tahun($appConfig);
$rekapPenetapan = new RekapPenetapan($appConfig);
$penetapan = new Penetapan($appConfig);
$rekapKetetapan = new RekapKetetapan($appConfig);
$rincianSPPTDHKP = new RincianSPPTDHKP($appConfig);
$realisasiDesaKota = new RealisasiDesaKota($appConfig);
$rekapNJOP = new RekapNJOP($appConfig);
$piutang = new Piutang($appConfig);
$penyisihanPiutang = new PenyisihanPiutang($appConfig);
$saldoPiutang = new SaldoPiutang($appConfig);
$rekapDHKP = new RekapDHKP($appConfig, $DBLink);
$detailPembayaran = new DetailPembayaran($appConfig);
$opRingkas = new OpRingkas($appConfig);
$repNjop = new ReportNjop($appConfig);
$rekapPelayanan = new RekapPelayanan($appConfig);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

$roleBPK = explode(',', $appConfig['ROLE_BPK']);
$roleLOKET = explode(',', $appConfig['ROLE_LOKET']);
$currentUsername = $User->GetUserName($uid);

$isUserLoggedinBPK = in_array($currentUsername, $roleBPK);


$isLOKET = in_array($currentUsername, $roleLOKET);

if ($currentUsername == 'FTFUSER') $isLOKET = false;

if (!isset($opt)) {
?>
    <link href="view/PBB/monitoring/monitoring.css?v00002" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
    <!--    -->
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
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#jatuh-tempo2-1").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#jatuh-tempo1-2").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#jatuh-tempo2-2").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#piutang-tgl-bayar-awal").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#piutang-tgl-bayar-akhir").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });

            $("#periode1-realisasi").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#periode2-realisasi").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });

            $("#periode1-detail-pembayaran").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
            $("#periode2-detail-pembayaran").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });

        });

        function updateCount() {
            var tahun = $("#tahun-pajak-" + 1).val();
            $("#ketAkm").html('<span style="font-size: 12px; color:#fff;">Loading...</span>');
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring/svc-count.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    var vcount = msg.split("/");
                    $("#ketAkm").html('<span style="font-size: 13px;color:black">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
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
            var nj1 = $("#njop1-" + sts).val();
            var nj2 = $("#njop2-" + sts).val();
            var nj3 = $("#njop3-" + sts).val();
            var nj4 = $("#njop4-" + sts).val();
            let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;

            $("#monitoring-content-" + sts).html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");

            var svc = "";
            $("#monitoring-content-" + sts).load("view/PBB/monitoring/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
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
                LBL_KEL: LBL_KEL,
                nj1: nj1,
                nj2: nj2,
                nj3: nj3,
                nj4: nj4,
                showAll: showAll
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
                url: "view/PBB/monitoring/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: id,
                    kel: 1
                },
                dataType: "json",
                beforeSend: function(d) {
                    // alert(d);
                },
                success: function(data) {
                    // alert(data);
                    if (data == null) {
                        $("select#kelurahan-" + sts).html("");
                        return false;
                    }
                    var c = data.msg.length;
                    // alert(c);
                    var options = '';
                    if (parseInt(c) > 0) {
                        options += '<option value="">Pilih Semua</option>';
                        for (var i = 0; i < c; i++) {
                            options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                            $("select#kelurahan-" + sts).html(options);
                        }
                    } else {
                        $("select#kelurahan-" + sts).html("");
                        // opti
                    }
                },
                error: function(msg) {
                    alert(msg);
                    $("select#kelurahan-" + sts).html("");
                }
            });
        }

        function showBank() {
            var request = $.ajax({
                url: "view/PBB/monitoring/svc-bank.php",
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

        //$(function() {
        //    $("#tabs").tabs();
        //});

        $(function() {
            // aldes
            $("#tabs").tabs({
                activate: function(event, ui) {
                    console.log(event, ui);
                    if (ui.newPanel.selector == '#tabs-18') {
                        initQZ();
                    }
                }
            });
        });

        function showKecamatan(sts) {
            var request = $.ajax({
                url: "view/PBB/monitoring/svc-kecamatan.php",
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
                url: "view/PBB/monitoring/svc-kecamatan.php",
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
                    $("select#kecamatan-detail-pembayaran").html(options);
                    $("select#kecamatan-rekap-pelayanan").html(options);
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
            var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") ?>';
            window.open("view/PBB/monitoring/svc-toexcel-e2.php?q=" + q + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT);
        }

        $(document).ready(function() {
            showBank();
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

            $("select#kecamatan-piutang").on('change', function() {
                showKelurahan('piutang');
            })
        });
    </script>

    <div class="col-md-12" style="position: relative">
        <div id="div-search" style="padding-top: 50px;">
            <div id="tabs">
                <ul>
                    <?php if ($isLOKET) : ?>
                        <li><a href="#tabs-20"><?php echo $opRingkas->label; ?></a></li>
                        <li><a href="#tabs-22"><?php echo $rekapPelayanan->label; ?></a></li>
                    <?php else : ?>
                        <li><a href="#tabs-1"><?php echo $statusBayar->sudahBayarLabel; ?></a></li>
                        <li><a href="#tabs-2"><?php echo $statusBayar->belumBayarLabel; ?></a></li>
                        <li><a href="#tabs-3"><?php echo $realisasi->label; ?></a></li>
                        <li><a href="#tabs-4"><?php echo $realisasiTunggakan->label; ?></a></li>
                        <li><a href="#tabs-5"><?php echo $rekapTunggakan->label; ?></a></li>
                        <li><a href="#tabs-6"><?php echo $detailTunggakan->label; ?></a></li>
                        <!-- <li><a href="#tabs-7"><?php echo $rekapanMurni->label; ?></a></li> -->
                        <?php if (!$isUserLoggedinBPK) : ?>
                            <!-- <li><a href="#tabs-8"><?php echo $realisasi6Tahun->label; ?></a></li> -->
                            <li><a href="#tabs-9"><?php echo $rekapPenetapan->label; ?></a></li>
                            <!-- <li><a href="#tabs-10"><?php echo $penetapan->label; ?></a></li> -->
                            <li><a href="#tabs-11"><?php echo $rekapKetetapan->label; ?></a></li>
                            <li><a href="#tabs-12"><?php echo $rincianSPPTDHKP->label; ?></a></li>
                            <!-- <li><a href="#tabs-13"><?php echo $realisasiDesaKota->label; ?></a></li> -->
                            <li><a href="#tabs-14"><?php echo $rekapNJOP->label; ?></a></li>
                            <li><a href="#tabs-15"><?php echo $piutang->label; ?></a></li>
                            <!-- <li><a href="#tabs-16"><?php echo $penyisihanPiutang->label; ?></a></li> -->
                            <li><a href="#tabs-17"><?php echo $saldoPiutang->label; ?></a></li>
                            <li><a href="#tabs-18"><?php echo $rekapDHKP->label; ?></a></li>
                            <li><a href="#tabs-19"><?php echo $detailPembayaran->label; ?></a></li>
                            <li><a href="#tabs-20"><?php echo $opRingkas->label; ?></a></li>
                            <!-- <li><a href="#tabs-21"><?php echo $repNjop->label; ?></a></li> -->
                            <li><a href="#tabs-22"><?php echo $rekapPelayanan->label; ?></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <?php if ($isLOKET) : ?>
                    <div id="tabs-20">
                        <?php $opRingkas->printForm($a, $m, $uid); ?>
                    </div>
                    <div id="tabs-22">
                        <?php $rekapPelayanan->printForm($a, $m, $uid); ?>
                    </div>
                <?php else : ?>
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
                        <?php $realisasiTunggakan->printForm($a, $m, $uid); ?>
                    </div>
                    <div id="tabs-5">
                        <?php $rekapTunggakan->printForm($a, $m, $uid); ?>
                    </div>
                    <div id="tabs-6">
                        <?php $detailTunggakan->printForm($a, $m, $uid); ?>
                    </div>
                    <!-- <div id="tabs-7">
                        <?php $rekapanMurni->printForm($a, $m, $uid); ?>
                    </div> -->
                    <?php if (!$isUserLoggedinBPK) : ?>
                        <!-- <div id="tabs-8">
                            <?php $realisasi6Tahun->printForm($a, $m, $uid); ?>
                        </div> -->
                        <div id="tabs-9">
                            <?php $rekapPenetapan->printForm($a, $m, $uid); ?>
                        </div>
                        <!-- <div id="tabs-10">
                            <?php $penetapan->printForm($a, $m, $uid); ?>
                        </div> -->
                        <div id="tabs-11">
                            <?php $rekapKetetapan->printForm($a, $m, $uid); ?>
                        </div>
                        <div id="tabs-12">
                            <?php $rincianSPPTDHKP->printForm($a, $m, $uid); ?>
                        </div>
                        <!-- <div id="tabs-13">
                            <?php $realisasiDesaKota->printForm($a, $m, $uid); ?>
                        </div> -->
                        <div id="tabs-14">
                            <?php $rekapNJOP->printForm($a, $m, $uid); ?>
                        </div>
                        <div id="tabs-15">
                            <?php $piutang->printForm($a, $m, $uid); ?>
                        </div>
                        <!-- <div id="tabs-16">
                            <?php $penyisihanPiutang->printForm($a, $m, $uid); ?>
                        </div> -->
                        <div id="tabs-17">
                            <?php $saldoPiutang->printForm($a, $m, $uid); ?>
                        </div>
                        <div id="tabs-18">
                            <?php $rekapDHKP->printForm($a, $m, $uid); ?>
                        </div>
                        <div id="tabs-19">
                            <?php $detailPembayaran->printForm($a, $m, $uid); ?>
                        </div>
                        <div id="tabs-20">
                            <?php $opRingkas->printForm($a, $m, $uid); ?>
                        </div>
                        <!-- <div id="tabs-21">
                            <?php $repNjop->printForm($a, $m, $uid); ?>
                        </div> -->
                        <div id="tabs-22">
                            <?php $rekapPelayanan->printForm($a, $m, $uid); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php
}
    ?>
    <div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 116px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
        <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
            <div style="float: left;">
                <span style="font-size: 12px;">Link Download</span>
            </div>
            <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
        </div>
        <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
    </div>


    <div id="cAkumulasi" style="position: absolute; top: 0; right: 23px; display: block; overflow: auto;">
        <div style="float: left; padding-top: 5px;" id="ketAkm">
            <span style="font-size: 13px; color:black!important">Tahun Berjalan (<b>0</b>) + Tunggakan (<b>0</b>) = Total Pembayaran (<b>0</b>)</span>
        </div>&nbsp;&nbsp;
        <input style="float: right;" type="button" class="btn btn-default btn-primary" name="updateCount" id="updateCount" value="Update" onClick="updateCount()" />
    </div>
    </div>

    <script language="javascript">
        var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
        var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
        var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
        var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
        var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
        var LBL_KEL = '<?php echo $appConfig['LABEL_KELURAHAN']; ?>';
        var THN_TAGIHAN = '<?php echo $appConfig['tahun_tagihan']; ?>';

        $("input:submit, input:button").button();
        $(document).ready(function() {
            var tahun = $("#tahun-pajak-1").val();
            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring/svc-count.php",
                data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
                success: function(msg) {
                    var vcount = msg.split("/");
                    $("#ketAkm").html('<span style="font-size: 13px; color:black">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
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
            })
        })
    </script>

    <div id="load-content">
        <div id="loader">
            <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
        </div>
    </div>
    <div id="load-mask"></div>