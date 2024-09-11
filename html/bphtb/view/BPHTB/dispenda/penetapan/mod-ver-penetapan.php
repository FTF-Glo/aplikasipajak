<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda' . DIRECTORY_SEPARATOR . 'penetapan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

// error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
// ini_set('display_errors', 1);
// echo "latihan";
// exit;
function displayMenuPendata()
{  // srch
    global $a, $m, $data;

    global $a, $m, $data;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dispenda/penetapan/svc-list-penetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5', 'n':'1', 'u':'$data->uname'}") . "\"> Disetujui <span class=\"w3-badge w3-tiny w3-red\" id=\"setuju\"></span></a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dispenda/penetapan/svc-list-penetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'2', 'u':'$data->uname'}") . "\"> Ditolak <span class=\"w3-badge w3-tiny w3-red\" id=\"tolak\"></span></a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/dispenda/penetapan/svc-list-penetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'2', 'n':'3', 'u':'$data->uname'}") . "\"> Tertunda <span class=\"w3-badge w3-tiny w3-red\" id=\"proses\"></span></a></a></li>\n";
    // echo "\t\t<li><a href=\"view/BPHTB/dispenda/penetapan/svc-list-penetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'3', 'n':'4', 'u':'$data->uname'}") . "\"> Proses se <span class=\"w3-badge w3-tiny w3-red\" id=\"proses\"></span></a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dispenda/penetapan/svc-list-penetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'100', 'n':'5', 'u':'$data->uname'}") . "\"> Semua Data</a></li>\n";
    echo "\t</ul>\n";
}
?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css?0002" type="text/css">
<!-- <link href="view/PBB/spop.css" rel="stylesheet" type="text/css" /> -->
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<!-- <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript">
    var page = 1;
    var axx = '<?php echo  base64_encode($a) ?>';
    var dispenda = 1;

    function setTabs(sts) {
        //setter
        var sel = 0;
        if (sts == 5)
            sel = 0;
        if (sts == 4)
            sel = 1;
        if (sts == 2)
            sel = 2;
        if (sts == 3)
            sel = 3;
        if (sts == 100)
            sel = 4;
        var find = $("#src-approved-" + sts).val();
        var find_notaris = $("#src-notaris-" + sts).val();
        var find_nop = $("#src-nop-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        var jnshak = $("#src-jnshak-" + sts).val();
        var tgl1 = $("#src-tgl1").val();
        var tgl2 = $("#src-tgl2").val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                find_notaris: find_notaris,
                find_nop: find_nop,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn,
                jnshak: jnshak,
                tgl1: tgl1,
                tgl2: tgl2
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        var sel = 0;
        if (sts == 5)
            sel = 0;
        if (sts == 4)
            sel = 1;
        if (sts == 2)
            sel = 2;
        if (sts == 3)
            sel = 3;
        if (sts == 100)
            sel = 4;
        if (np == 1)
            page++;
        else
            page--;
        //console.log(sel);
        var find = $("#src-approved-" + sts).val();
        var find_nop = $("#src-nop-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        var jnshak = $("#src-jnshak-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                page: page,
                find_nop: find_nop,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn,
                jnshak: jnshak,
                np: np
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

    function submitNtpd(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        // console.log(s);
        executeNtpd(s)
    }

    function executeNtpd(json) {
        if (json) {
            // Lakukan pembaruan database di sini
            // Anda bisa menggunakan AJAX untuk melakukan pembaruan ke server
            // Berikut ini hanya contoh, Anda harus menyesuaikan dengan struktur dan logika aplikasi Anda
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "./view/BPHTB/dispenda/update_ntpd.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        // Respon dari server setelah pembaruan berhasil
                        var response = xhr.responseText;
                        if (response === "success") {
                            // Jika pembaruan berhasil, tampilkan alert berhasil
                            alert("Berhasil memperbarui data!");
                            window.location.reload();
                        } else {
                            // Jika pembaruan gagal, tampilkan pesan kesalahan
                            alert("Gagal memperbarui data!");
                        }
                    } else {
                        // Menangani kesalahan HTTP
                        alert("Terjadi kesalahan saat melakukan permintaan ke server.");
                    }
                }
            };
            xhr.send(JSON.stringify({
                data: json
            }));
        } else {
            alert("Silahkan pilih data yang akan di terbitkan NTPD!");
        }
    }
    // function executeNtpd(json) {
    //     console.log(json);
    //     if (json) {
    //         // window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q=' + Base64.encode(json), '_newtab');
    //     } else {
    //         alert("Silahkan pilih data yang akan di print!");
    //     }
    // }

    function printDataToPDF(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printToPDF(s)
    }

    function printDataNTPD(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        print_NTPD(s)
    }

    function print_NTPD(json) {
        if (json) {
            window.open('./view/BPHTB/dispenda/penetapan/svc-print-ntpd.php?q=' + Base64.encode(json), '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function printToPDF(json) {
        if (json) {
            window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q=' + Base64.encode(json), '_newtab');
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
        //if (json) {
        window.open('./view/BPHTB/balailelang/svc-print-notaris-app-xls.php?q=' + Base64.encode(json), 'excel');
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
            url: './view/BPHTB/dispenda/svc-dispenda.php',
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
                    changeStatusMenu('tolak', 0);
                    changeStatusMenu('setuju', 0);
                    changeStatusMenu('tunda', 0);
                    changeStatusMenu('semua', 0);
                    changeStatusMenu('proses', 0);

                    var approved = Ext.decode(response.responseText).approved2;
                    var reject = Ext.decode(response.responseText).reject;
                    var delay = Ext.decode(response.responseText).delay;
                    var delay5 = Ext.decode(response.responseText).delay5;
                    var proses = Ext.decode(response.responseText).proses;
                    // console.log(proses);
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
    <?php echo displayMenuPendata() ?>
</div>