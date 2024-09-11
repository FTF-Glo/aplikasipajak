<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "view/BPHTB/mod-display.php");

function displayMenuMonitoring()
{  // srch
    global $a, $m, $data;
    echo "\t<ul>\n";


    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'1', 'n':'1', 'u':'$data->uname'}") . "\">Rekapitulasi Pembayaran <br>Berdasarkan User</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'2', 'n':'2', 'u':'$data->uname'}") . "\">Rekapitulasi Pembayaran <br>Per User</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'3', 'n':'3', 'u':'$data->uname'}") . "\">Rekapitulasi Approval <br>Berdasarkan User</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'4', 'u':'$data->uname'}") . "\">Rekapitulasi Approval <br>Per User</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5', 'n':'5', 'u':'$data->uname'}") . "\">Rekapitulasi Persetujuan <br>yang siap bayar</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'8', 'n':'8', 'u':'$data->uname'}") . "\">Sudah <br> bayar</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'9', 'n':'9', 'u':'$data->uname'}") . "\">Belum <br> bayar</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10', 'n':'10', 'u':'$data->uname'}") . "\">Kadaluarsa<br> &nbsp;</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'11', 'n':'11', 'u':'$data->uname'}") . "\">Nihil<br>  &nbsp;</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'12', 'n':'12', 'u':'$data->uname'}") . "\">Laporan Harian<br>  &nbsp;</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'13', 'n':'13', 'u':'$data->uname'}") . "\">Rekapitulasi <br> Harian</a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'14', 'n':'14', 'u':'$data->uname'}") . "\">Realisasi<br>&nbsp;</a></li>\n";


    echo "\t</ul>\n";
}

?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css?0002" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script language="javascript">
    var uname = '<?php echo $data->uname ?>';
</script>
<script language="javascript">
    var ap = '<?php echo $a ?>';
</script>
<script type="text/javascript" src="view/BPHTB/monitoring/mod-monitoring.js?v.0.0.1"></script>

<script type="text/javascript">
    var page = 1;
    var axx = '<?php echo base64_encode($a) ?>';

    function setTabs(sts) {
        //setter
        var sel = 0;
        if (sts == 1) sel = 0;
        if (sts == 2) sel = 1;
        if (sts == 3) sel = 2;
        if (sts == 4) sel = 3;
        if (sts == 5) sel = 4;
        if (sts == 8) sel = 5;
        if (sts == 9) sel = 6;
        if (sts == 10) sel = 7;
        if (sts == 11) sel = 8;
        if (sts == 12) sel = 9;
        if (sts == 13) sel = 10;
        if (sts == 14) sel = 11;
        var tgl1 = $("#src-tgl1-" + sts).val();
        var tgl2 = $("#src-tgl2-" + sts).val();
        var find = $("#src-approved-" + sts).val();
        var kec = $("#kecamatan2-" + sts).val();
        var kel = $("#kelurahan2-" + sts).val();
        var tagihan_awal = $("#tagihan_awal").val();
        var tagihan_akhir = $("#tagihan_akhir").val();
        var nm_wp = $("#nm_wp-" + sts).val();
        var find_notaris = $("#src-notaris-" + sts).val();
        var jh = $("#jenis_hak").val();
        var kd_byar = $("#src-kdbyr-" + sts).val();
        if (sts == 14) jh = $("#tahun-pajak-14").val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                find_notaris: find_notaris,
                tgl1: tgl1,
                tgl2: tgl2,
                kec: kec,
                kel: kel,
                jh: jh,
                tagihan_awal: tagihan_awal,
                tagihan_akhir: tagihan_akhir,
                nm_wp: nm_wp,
                kd_byar: kd_byar,
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        var sel = 0;
        if (sts == 1) sel = 0;
        if (sts == 2) sel = 1;
        if (sts == 3) sel = 2;
        if (sts == 4) sel = 3;
        if (sts == 5) sel = 4;
        if (sts == 8) sel = 5;
        if (sts == 9) sel = 6;
        if (sts == 10) sel = 7;
        if (sts == 11) sel = 8;
        if (sts == 12) sel = 9;
        if (sts == 13) sel = 10;

        if (np == 1) page++;
        else page--;

        var tgl1 = $("#src-tgl1-" + sts).val();
        var tgl2 = $("#src-tgl2-" + sts).val();
        var find = $("#src-approved-" + sts).val();
        var kd_byar = $("#src-kdbyr-" + sts).val();
        var kec = $("#kecamatan2-" + sts).val();
        var kel = $("#kelurahan2-" + sts).val();
        var tagihan_awal = $("#tagihan_awal").val();
        var tagihan_akhir = $("#tagihan_akhir").val();
        var find_notaris = $("#src-notaris-" + sts).val();
        var jh = $("#jenis_hak").val();
        var nm_wp = $("#nm_wp" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                find_notaris: find_notaris,
                tgl1: tgl1,
                tgl2: tgl2,
                kec: kec,
                kel: kel,
                jh: jh,
                tagihan_awal: tagihan_awal,
                tagihan_akhir: tagihan_akhir,
                nm_wp: nm_wp,
                kd_byar: kd_byar,
                np: np,
                page: page,
                s: sts
            }
        });
        // alert(sel)
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function getCheckedValue(buttonGroup, draf) {
        // Go through all the check boxes. return an array of all the ones
        // that are selected (their position numbers). if no boxes were checked,
        // returned array will be empty (length will be zero)
        var retArr = new Array();

        var lastElement = 0;
        if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
            for (var i = 0; i < buttonGroup.length; i++) {
                if (buttonGroup[i].checked) {
                    retArr.length = lastElement;
                    var arrObj = new Object();
                    arrObj.id = buttonGroup[i].value;
                    arrObj.draf = draf;
                    arrObj.axx = axx;
                    arrObj.uname = "";
                    retArr[lastElement] = arrObj;
                    lastElement++;
                }
            }
        } else { // There is only one check box (it's not an array)
            if (buttonGroup.checked) { // if the one check box is checked
                retArr.length = lastElement;
                var arrObj = new Object();
                arrObj.id = buttonGroup[i].value;
                arrObj.draf = draf;
                arrObj.axx = axx;
                retArr[lastElement] = arrObj; // return zero as the only array value
            }
        }
        return retArr;
    }

    function printDataToPDF(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printToPDF(s)
    }

    function showKecamatanAll() {
        var request = $.ajax({
            url: "view/BPHTB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: "1801"
            },
            dataType: "json",
            success: function(data) {
                // alert(data);
                var c = data.msg.length;
                var options = '';
                options += '<option value="">Pilih Semua</option>';
                for (var i = 0; i < c; i++) {
                    // alert(data.msg[i].id);
                    options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                }
                // alert(options);
                $("#kecamatan").html(options);
            }
        });

    }

    function changekecmatan(e, sts) {

        $.ajax({
            url: "view/BPHTB/monitoring/svc-list-kecamatan-new.php",
            type: "POST",
            dataType: "json",
            data: {
                sts_: sts,
                value_: $(e).val()
            },
            success: function(data) {
                console.log(data)
                $("#kelurahan2-" + sts).html(data)
            },
            error: function(msg) {
                // alert(msg)
            }
        })
    }

    function showKelurahan() {
        var id = $('select#kecamatan').val()
        var request = $.ajax({
            url: "view/BPHTB/monitoring/svc-kecamatan.php",
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
                console.log(data);
                if (data == null) {
                    $("select#kelurahan").html("");
                    return false;
                }
                var c = data.msg.length;
                // alert(c);
                var options = '';
                if (parseInt(c) > 0) {
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kelurahan").html(options);
                    }
                } else {
                    $("select#kelurahan").html("");
                    // opti
                }
            },
            error: function(msg) {
                // alert(msg);
                $("select#kelurahan").html("");
            }
        });
    }

    $(document).ready(function() {
        /*$("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });*/


        // $("#kecamatan2-8").trigger('change')


        $("#tabsContent").tabs({
            load: function(e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },

            select: function(e, ui) {
                var $panel = $(ui.panel);
                var d = $('#select-all').val();
                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });

        //getter
        //var selected = $( "#tabsContent" ).tabs( "option", "selected" );
        //console.log(selected);

    });
</script>

<div id="tabsContent">
    <?php echo displayMenuMonitoring() ?>
</div>