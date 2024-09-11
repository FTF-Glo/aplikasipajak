<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'penghapusan', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function displayMenuPendata()
{  // srch
// var_dump($_SERVER ['HTTP_USER_AGENT']);die;
    global $a, $m, $data;
    // var_dump($data->uname);
    // die;
    echo "<ul>";
    // echo "<li><a href=\"view/BPHTB/monitoring/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'9', 'n':'9', 'u':'$data->uname'}") . "\">Belum <br> bayar</a></li>";
    echo "<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'9', 'n':'9', 'u':'$data->uname'}") . "\"><i class=\"fa fa-clipboard\" style=\"font-size:16px;\"></i>Data belum bayar <spanp class=\"w3-badge w3-tiny w3-green\" id=\"setuju\"></span></a></li>";

    echo "<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'2', 'u':'$data->uname'}") . "\"><i class=\"fa fa-window-close\" style=\"font-size:16px;\"></i>Ditolak <span class=\"w3-badge w3-tiny w3-red\" id=\"tolak\"></span></a></li>";
    echo "<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'1', 'n':'4', 'u':'$data->uname'}") . "\"><i class=\"fa fa-paperclip\" style=\"font-size:16px;\"></i>Sementara <span class=\"w3-badge w3-tiny w3-blue\" id=\"sementara\"></span></a></li>";
    echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'2', 'n':'3', 'u':'$data->uname'}") . "\"><i class=\"fa fa-inbox\" style=\"font-size:16px;\"></i> Tertunda <span class=\"w3-badge w3-tiny w3-orange\" id=\"tunda\"></span></a></li>\n";
    // if($_SERVER ['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0'){
        // echo "<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'3', 'n':'3', 'u':'$data->uname'}") . "\"><i class=\"fa fa-inbox\" style=\"font-size:16px;\"></i>Tertunda <span class=\"w3-badge w3-tiny w3-orange\" id=\"tunda\"></span></a></a></li>";
    // }

    // echo "<li><a href=\"view/BPHTB/dispenda/svc-list-persetujuan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'100', 'n':'4', 'u':'$data->uname'}") . "\"><i class=\"fa fa-list-ul\" style=\"font-size:16px;\"></i> Semua Data <span class=\"w3-badge w3-tiny w3-black\" id=\"semua\"></span></a></li>";
    echo "</ul>";
}
?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css?00001" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<!-- <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript">
    var page = 1;
    var axx = '<?=base64_encode($a)?>';
    var dispenda = 2;

    function setTabs(sts) {
        var sel = 0;
        if (sts == 1) sel = 2;
        if (sts == 4) sel = 1;
        if (sts == 9) sel = 0;
        if (sts == 2) sel = 3;

        var tgl1 = $("#src-tgl1-" + sts).val();
        var tgl2 = $("#src-tgl2-" + sts).val();
        var find = $("#src-approved-" + sts).val();
        // var kec = $("#kecamatan2-" + sts).val();
        // var kel = $("#kelurahan2-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        var tagihan_awal = $("#tagihan_awal").val();
        var tagihan_akhir = $("#tagihan_akhir").val();
        var nm_wp = $("#nm_wp-" + sts).val();
        var find_notaris = $("#src-notaris-" + sts).val();
        var jh = $("#jenis_hak").val();
        var kd_byar = $("#src-kdbyr-" + sts).val();
        // console.log(kec);
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                find_notaris: find_notaris,
                tgl1: tgl1,
                tgl2: tgl2,
                // kec: kec,
                // kel: kel,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn,
                jh: jh,
                tagihan_awal: tagihan_awal,
                tagihan_akhir: tagihan_akhir,
                nm_wp: nm_wp,
                kd_byar: kd_byar,
            }
        });
        // console.log(data);
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

        //setter
        //     var sel = 0;
        //     if (sts == 9)
        //         sel = 9;

        //     if (sts == 4)
        //         sel = 1;

        //     if (sts == 3)
        //         sel = 2;

        //     if (sts == 100)
        //         sel = 3;

        //     var find = $("#src-approved-" + sts).val();
        //     var find_notaris = $("#src-notaris-" + sts).val();
        //     var find_kcmtn = $("#src-kcmtn-" + sts).val();
        //     var find_nop = $("#src-nop-" + sts).val();
        //     var find_kdbyr = $("#src-kdbyr-" + sts).val();
        //     var find_kdbyr = $("#src-kdbyr-" + sts).val();
        //     var find_klrhn = $("#src-klrhn-" + sts).val();
        //     var find_klrhn = $("#src-klrhn-" + sts).val();
        //   //  var jnshak = $("#src-jnshak-" + sts).val();
        //     console.log(sts);
        //     $("#tabsContent").tabs("option", "ajaxOptions", {
        //         async: false,
        //         data: {
        //             find: find,
        //             find_notaris: find_notaris,
        //             find_kcmtn: find_kcmtn,
        //             find_klrhn: find_klrhn,
        //             find_nop: find_nop,
        //             find_kdbyr: find_kdbyr,
        //             jnshak: jnshak,
        //         }
        //     });
        //     $("#tabsContent").tabs("option", "selected", sel);
        //     $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        var sel = 0;
        var sel = 0;
        if (sts == 5)
            sel = 0;
        if (sts == 4)
            sel = 1;
        if (sts == 3)
            sel = 2;
        if (sts == 2)
            sel = 3;
        if (sts == 100)
            sel = 3;
        if (np == 1)
            page++;
        else
            page--;
        //console.log(sel);
        


        var find = $("#src-approved-" + sts).val();
        var find_nop = $("#src-nop-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        var find_notaris = $("#src-notaris-" + sts).val();
        var jnshak = $("#src-jnshak-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                page: page,
                np: np,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn,
                find_nop: find_nop,
                find_notaris: find_notaris,
                jnshak: jnshak,
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

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
                $("#src-klrhn-" + sts).html(data)
            },
            error: function(msg) {
                alert(msg)
            }
        })
    }
    //$data - > uname;

    function getCheckedValue(buttonGroup, draf) {
        // Go through all the check boxes. return an array of all the ones
        // that are selected (their position numbers). if no boxes were checked,
        // returned array will be empty (length will be zero)
        var retArr = new Array();
        var ad = '<?php echo  $uname ?>';
        //console.log(ad);
        var lastElement = 0;
        if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
            for (var i = 0; i < buttonGroup.length; i++) {
                if (buttonGroup[i].checked) {
                    retArr.length = lastElement;
                    var arrObj = new Object();
                    arrObj.id = buttonGroup[i].value;
                    arrObj.draf = draf;
                    arrObj.axx = axx;
                    arrObj.uname = ad;
                    //  arrObj.alasan = sju;
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
        console.log(s);
        printToPDF(s)
    }

    function dataHapus(d) {

        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        console.log("kenikah datanya?");
        // console.log(s);

        hapus_sementara(s)
    }

    function dataHapus_reject(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        console.log(s);
        hapus_reject(s)
    }

    function dataHapus_tunda(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        console.log(s);
        hapus_tunda(s)
    }

    function hapus_sementara(json) {
        if (json) {
            var reason = prompt("Masukkan alasan penghapusan data:");

            if (reason !== null && reason.trim() !== "") {
                // Lanjutkan dengan penghapusan jika alasan diisi
                $.ajax({
                    url: './view/BPHTB/penghapusan/svc-print-penghapusan-sementara.php',
                    type: 'GET',
                    data: {
                        q: Base64.encode(json),
                        reason: reason
                    },
                    success: function(response) {
                        alert('data berhasil di hapus');
                        location.reload();
                        // Jalankan script atau tampilkan response di halaman yang sama
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memproses request.');
                    }
                });
                // window.open('./view/BPHTB/penghapusan/svc-print-penghapusan-app.php?q=' + Base64.encode(json));
            } else {
                alert('Alasan anda menghapus tidak boleh kosong!!');
            }
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function hapus_reject(json) {
        if (json) {
            var reason = prompt("Masukkan alasan penghapusan data:");

            if (reason !== null && reason.trim() !== "") {
                // Lanjutkan dengan penghapusan jika alasan diisi
                $.ajax({
                    url: './view/BPHTB/penghapusan/svc-print-penghapusan-reject.php',
                    type: 'GET',
                    data: {
                        q: Base64.encode(json),
                        reason: reason
                    },
                    success: function(response) {
                        alert('data berhasil di hapus');
                        location.reload();
                        // Jalankan script atau tampilkan response di halaman yang sama
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memproses request.');
                    }
                });
                // window.open('./view/BPHTB/penghapusan/svc-print-penghapusan-app.php?q=' + Base64.encode(json));

            } else {
                alert('Alasan anda menghapus tidak boleh kosong!!');
            }
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function hapus_tunda(json) {
        if (json) {
            var reason = prompt("Masukkan alasan penghapusan data:");

            if (reason !== null && reason.trim() !== "") {
                // Lanjutkan dengan penghapusan jika alasan diisi
                $.ajax({
                    url: './view/BPHTB/penghapusan/svc-print-penghapusan-tertunda.php',
                    type: 'GET',
                    data: {
                        q: Base64.encode(json),
                        reason: reason
                    },
                    success: function(response) {
                        alert('data berhasil di hapus');
                        // location.reload();
                        // Jalankan script atau tampilkan response di halaman yang sama
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memproses request.');
                    }
                });
                // window.open('./view/BPHTB/penghapusan/svc-print-penghapusan-app.php?q=' + Base64.encode(json));

            } else {
                alert('Alasan anda menghapus tidak boleh kosong!!');
            }
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function printToPDF(json) {
        if (json) {
            var reason = prompt("Masukkan alasan penghapusan data:");

            if (reason !== null && reason.trim() !== "") {
                // Lanjutkan dengan penghapusan jika alasan diisi
                $.ajax({
                    url: './view/BPHTB/penghapusan/svc-print-penghapusan-app.php',
                    type: 'GET',
                    data: {
                        q: Base64.encode(json),
                        reason: reason
                    },
                    success: function(response) {
                        alert('data berhasil di hapus');
                        location.reload();
                        // Jalankan script atau tampilkan response di halaman yang sama
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memproses request.');
                    }
                });
                // window.open('./view/BPHTB/penghapusan/svc-print-penghapusan-app.php?q=' + Base64.encode(json));

            } else {
                alert('Alasan anda menghapus tidak boleh kosong!!');
            }
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function printDataToXLS(d) {
        var tgl1 = $("#src-tgl1").val();
        var tgl2 = $("#src-tgl2").val();


        var retArr = new Array();
        var arrTgl = new Object();
        arrTgl.tgl1 = tgl1;
        arrTgl.tgl2 = tgl2;
        arrTgl.appID = axx;
        retArr[0] = arrTgl;
        var dt = retArr;
        var s = "";
        //console.log(dt)
        if (dt.length > 0) {
            s = Ext.util.JSON.encode(dt); //Ext.JSON.encode(dt);
            //s = $.parseJSON(dt);
        }
        console.log(s);
        printToXLS(s)
    }

    function printToXLS(json) {

        alert('yey');
        console.log(json);
        //if (json) {
        // window.open('./view/BPHTB/balailelang/svc-print-notaris-app-xls.php?q=' + Base64.encode(json), 'excel');
        // } else {
        //    alert("Silahkan pilih data yang akan di print!");
        // }

    }


    $(document).ready(function() {
        //        $("#all").click(function () {
        //            console.log('testse');
        //            $("input [name='check-all']").each(function () {
        //                this.checked = $("#all-check-button").is(':checked');
        //            });
        //        });

        $("#tabsContent").tabs({
            load: function(e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function(e, ui) {
                var $panel = $(ui.panel);

                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });
        loadData();
        //getter
        //var selected = $( "#tabsContent" ).tabs( "option", "selected" );
        //console.log(selected);

    });

    function toExcel() {
        window.open("view/BPHTB/dispenda/ToExcel.php", "_blank");
    }

    function loadData() {
        Ext.Ajax.request({
            url: './view/BPHTB/penghapusan/svc-penghapusan.php',
            params: {
                dispenda: dispenda
            },
            method: 'POST',
            scope: this,
            callback: function(options, success, response) {
                if (Ext.decode(response.responseText).success == false) {
                    var er = Ext.decode(response.responseText).error;
                    Ext.Msg.alert('Error!', "Salah");
                } else {
                    changeStatusMenu('tolak', '0');
                    changeStatusMenu('setuju', '0');
                    changeStatusMenu('tunda', '0');
                    changeStatusMenu('semua', '0');
                    changeStatusMenu('proses', '0');
                    var approved = Ext.decode(response.responseText).approved2;
                    var reject = Ext.decode(response.responseText).reject;
                    var delay = Ext.decode(response.responseText).delay;
                    var delay5 = Ext.decode(response.responseText).delay5;
                    var proses = Ext.decode(response.responseText).proses;

                    if (reject != 0) changeStatusMenu('tolak', reject);
                    if (approved != 0) changeStatusMenu('setuju', approved);
                    if (delay != 0) changeStatusMenu('tunda', delay);
                    if (delay5 != 0) changeStatusMenu('semua', delay5);
                    if (proses != 0) changeStatusMenu('proses', proses);
                    setTimeout('loadData()', 35000);
                }

            }
        });
    }

    function changeStatusMenu(id, val) {
        var x = document.getElementById(id);
        if (x != null) {
            removeFChild(x);
            var ttext = document.createTextNode(val);
            x.appendChild(ttext);
        }
    }

    function removeFChild(obj) {
        if (obj != null) {
            if (obj.hasChildNodes()) {
                while (obj.childNodes.length >= 1) {
                    obj.removeChild(obj.firstChild);
                }
            }
        }
    }
</script>

<div id="tabsContent">
    <?php echo displayMenuPendata()
    ?>
</div>