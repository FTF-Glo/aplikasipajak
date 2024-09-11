<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'penghapusan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

function displayMenuPendata()
{  // srch
    global $a, $m, $data;
    echo "\t<ul>\n";
    // echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5', 'n':'1', 'u':'$data->uname'}") . "\"><i class=\"fa fa-clipboard\" style=\"font-size:16px;\"></i> Disetujui <p class=\"w3-badge w3-tiny w3-green\" id=\"setuju\">0</p></a></li>\n";

    //  echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'2', 'u':'$data->uname'}") . "\"><i class=\"fa fa-window-close\" style=\"font-size:16px;\"></i> Ditolak <p class=\"w3-badge w3-tiny w3-red\" id=\"tolak\">0</p></a></li>\n";

    echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'2', 'n':'3', 'u':'$data->uname'}") . "\"><i class=\"fa fa-inbox\" style=\"font-size:16px;\"></i> Tertunda <p class=\"w3-badge w3-tiny w3-orange\" id=\"tunda\">0</p></a></li>\n";

    // echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'1', 'n':'4', 'u':'$data->uname'}") . "\"><i class=\"fa fa-paperclip\" style=\"font-size:16px;\"></i> Sementara <p class=\"w3-badge w3-tiny w3-blue\" id=\"sementara\">0</p></a></li>\n";
    // echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'99','n':'5', 'u':'$data->uname'}") . "\"><i class=\"\" style=\"font-size:16px;\"></i> Kadaluarsa <p class=\"w3-badge w3-tiny w3-black\" id=\"semua\">0</p></a></li>\n";

    // echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'100','n':'6', 'u':'$data->uname'}") . "\"><i class=\"fa fa-list-ul\" style=\"font-size:16px;\"></i> Semua Data <p class=\"w3-badge w3-tiny w3-black\" id=\"semua\">0</p></a></li>\n";


    //  echo "\t\t<li><a href=\"view/BPHTB/penghapusan/svc-list-penghapusan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'7', 'u':'$data->uname'}") . "\"><i class=\"fa fa-archive\" style=\"font-size:16px;\"></i> History</a> </li>\n";
    echo "\t</ul>\n";
}
?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript">
    var page = 1;
    var axx = '<?php echo  base64_encode($a) ?>';
    var uname = '<?php echo  $data->uname ?>';

    function setTabs(sts) {
        //setter
        var sel = 0;
        if (sts == 4)
            sel = 1;
        else if (sts == 2)
            sel = 2;
        else if (sts == 1)
            sel = 3;
        else if (sts == 99)
            sel = 4;
        else if (sts == 100)
            sel = 5;
        else if (sts == 6)
            sel = 6;

        var find = $("#src-approved-" + sts).val();
        var ktp = $("#src-ktp-" + sts).val();
        var kd_byr = $("#src-kdbyr-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                ktp: ktp,
                kd_byr: kd_byr,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        var sel = 0;
        if (sts == 4)
            sel = 1;
        else if (sts == 2)
            sel = 2;
        else if (sts == 1)
            sel = 3;
        else if (sts == 99)
            sel = 4;
        else if (sts == 100)
            sel = 5;
        else if (sts == 6)
            sel = 6;

        if (np == 1)
            page++;
        else
            page--;

        //console.log(page)
        var find = $("#src-approved-" + sts).val();
        var ktp = $("#src-ktp-" + sts).val();
        var kd_byr = $("#src-kdbyr-" + sts).val();
        var find_kcmtn = $("#src-kcmtn-" + sts).val();
        var find_klrhn = $("#src-klrhn-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                page: page,
                np: np,
                ktp: ktp,
                kd_byr: kd_byr,
                find_kcmtn: find_kcmtn,
                find_klrhn: find_klrhn
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


    ///// HIT REST API 
    var repeat = 0;

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function getQRCode(id, sha1, code, exp, nop, city) {
        if (repeat == 0) {
            if (confirm("Proses ini akan men-generate QRIS code \n\nApakah mau lanjut ?\n")) {
                repeat = 1;
                document.getElementById("idico" + id).src = "./image/large-loading.gif";
                hitit(id, sha1, code, exp, nop, city);
            } else {
                document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
                repeat = 0;
            }
        }
    }

    // function hitit(id, sha1, code, exp, nop, city) {
    //     if (repeat != 0) {
    //         Ext.Ajax.request({
    //             url: "http://103.140.188.162:5051/function/BPHTBLAMPUNGSELATAN/func-getQRIS-POST.php",
    //             method: "POST",
    //             params: {
    //                 ssbid: id,
    //                 sha1: sha1,
    //                 code: code,
    //                 exp: exp,
    //                 nop: nop,
    //                 city: city
    //             },
    //             success: function(result, request) {
    //                 var respon = JSON.parse(result.responseText);
    //                 if (respon.status) {
    //                     repeat = 0;
    //                     alert("Berhasil");
    //                     document.getElementById("idico" + id).src = "./image/icon/qr.png";
    //                     let elem = document.getElementById("divico" + id).firstElementChild;
    //                     elem.removeAttribute("onclick");
    //                     elem.removeAttribute("href");
    //                 } else if (respon.msg == "repeat") {
    //                     repeat++;
    //                     if (repeat >= 4) {
    //                         repeat = 0;
    //                         document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
    //                         alert("Gagal koneksi ke Server REST API");
    //                     }
    //                     sleep(2000).then(() => {
    //                         hitit(id, sha1, code, exp, nop, city);
    //                     });
    //                 } else {
    //                     repeat = 0;
    //                     alert(respon.msg);
    //                     document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
    //                     let elem = document.getElementById("divico" + id).firstElementChild;
    //                     elem.removeAttribute("onclick");
    //                     elem.removeAttribute("href");
    //                 }
    //             },
    //             failure: function(result, request) {
    //                 repeat = 0;
    //                 alert("Gagal Mengambil Data");
    //                 document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
    //             }
    //         });
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

    function printBeritaAcara(d) {
        var dt = getCheckedValue(document.getElementsByName('check-all'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printBA(s)
    }

    function printBA(json) {
        if (json) {
            window.open('./view/BPHTB/notaris/svc-print-berita.php?q=' + Base64.encode(json), '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }


    function printToPDF(json) {
        if (json) {
            window.open('./view/BPHTB/penghapusan/svc-print-penghapusan-app.php?q=' + Base64.encode(json), '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }


    $(document).ready(function() {
        /*$("#all-check-button").click(function() {
         $('.check-all').each(function(){ 
         this.checked = $("#all-check-button").is(':checked'); 
         });
         });*/





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
        loadData();
        //getter
        //var selected = $( "#tabsContent" ).tabs( "option", "selected" );
        //console.log(selected);

    });


    function loadData() {
        Ext.Ajax.request({
            url: './view/BPHTB/notaris/svc-notaris.php',
            params: {
                uname: uname
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
                    changeStatusMenu('sementara', '0');
                    var approved = Ext.decode(response.responseText).approved;
                    var reject = Ext.decode(response.responseText).reject;
                    var delay = Ext.decode(response.responseText).delay;
                    var temp = Ext.decode(response.responseText).temporary;
                    if (reject != 0) changeStatusMenu('tolak', reject);
                    if (approved != 0) changeStatusMenu('setuju', approved);
                    if (delay != 0) changeStatusMenu('tunda', delay);
                    if (temp != 0) changeStatusMenu('sementara', temp);
                    setTimeout('loadData()', 350000);
                }
            }
        });
    }

    function removeFChild(obj) {
        //if (td!=null) {
        if (obj.hasChildNodes()) {
            while (obj.childNodes.length >= 1) {
                obj.removeChild(obj.firstChild);
            }
        }
        //}
    }

    function changeStatusMenu(id, val) {
        var x = document.getElementById(id);
        removeFChild(x);
        var ttext = document.createTextNode(val);
        x.appendChild(ttext);
    }
</script>
<div id="tabsContent">
    <?php echo displayMenuPendata() ?>
</div>