<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dashboard', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

function displayMenuPendata()
{  // srch
    global $a, $m, $data;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dashboard/svc-list-dashboard.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5', 'n':'1', 'u':'$data->uname'}") . "\">Hari</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dashboard/svc-list-dashboard.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'2', 'u':'$data->uname'}") . "\">Bulan</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/dashboard/svc-list-dashboard.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'7', 'n':'3', 'u':'$data->uname'}") . "\">Tahun</a></li>\n";
    echo "\t</ul>\n";
}
?>
<!--<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css" type="text/css">-->
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/js/highcharts.js"></script>
<script type="text/javascript" src="inc/js/modules/exporting.js"></script>
<script type="text/javascript">
    var page = 1;
    var axx = '<?php echo base64_encode($a) ?>';

    function setTabs(sts) {
        //setter
        var sel;
        if (sts == 5)
            sel = 0;
        else if (sts == 6)
            sel = 1;
        else if (sts == 7)
            sel = 2;

        var find = $("#src-approved-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        var sel;

        if (sts == 5) {
            sel = 0;
        } else if (sts == 6) {
            sel = 1;
        } else if (sts == 7) {
            sel = 2;
        }


        if (np == 1)
            page++;
        else
            page--;

        //console.log(page)
        //var find = $("#src-approved-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                page: page,
                np: np
            }
        });
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

        //getter
        //var selected = $( "#tabsContent" ).tabs( "option", "selected" );
        //console.log(selected);

    });
</script>
<div id="tabsContent">
    <?php

    echo displayMenuPendata();
    ?>
</div>