<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop' . DIRECTORY_SEPARATOR . 'wp', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;

function displayDaftarWP()
{  // srch
    global $a, $m, $data;

    $notifid  = @isset($_REQUEST['notifid']) ? $_REQUEST['notifid'] : "";

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"function/PBB/nop/wp/svc-list-wp.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Daftar WP</a></li>\n";
    echo "\t</ul>\n";
}

?>
<div class="col-md-12">
    <div id="box2">
        <div align="center" id="daftar_nop" style="width: 200px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
            <div style="width: 198; height: 25px; border-bottom: 1px solid #eaeaea; overflow: auto;"><b>
                    <font size="3">DAFTAR NOP</font>
                </b>
                <div id="closednomor" style="float: right; margin: 1px; padding: 1px; border: 1px solid #eaeaea;">X</div>
            </div>
            <div style="margin: 10px;margin-left: 10px;">
                <p id="nop"></p>
                <input type="hidden" id="wpid" name="wpid" />
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div id="box1"></div>
</div>

<style type="text/css">
    .link_id:hover {
        color: #ce7b00;
    }

    .cListNOP:hover {
        color: #ce7b00;
    }

    .link_id {
        text-decoration: underline;
        cursor: pointer;
    }

    .cListNOP {
        text-decoration: underline;
        cursor: pointer;
    }

    #box1,
    #box2 {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #box1 {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #box2 {
        z-index: 2;
    }

    #closednomor {
        cursor: pointer;
    }
</style>

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>
<script type="text/javascript">
    var page = 1;



    function setTabs(tab) {
        page = 1;

        var srcAlamat = $("#srcAlamat-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        var srcKTP = $("#srcKTP-" + tab).val();
        var jns = $("#jns option:selected").val();

        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srcAlamat: srcAlamat,
                srcNama: srcNama,
                srcKTP: srcKTP,
                jns: jns
            }
        });
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);
    }

    function setPage(tab, np) {
        if (np == 1) page++;
        else page--;

        var srcAlamat = $("#srcAlamat-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        var srcKTP = $("#srcKTP-" + tab).val();
        var jns = $("#jns option:selected").val();

        //		var find = $("#src-approved-"+tab).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                srcAlamat: srcAlamat,
                srcNama: srcNama,
                srcKTP: srcKTP,
                jns: jns
            }
        });
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);

    }

    $(document).ready(function() {
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

        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
                this.checked = $("#all-check-button").is(':checked');
            });
        });

        <?php if (isset($tab)) { ?>
            setTabs(<?php echo $tab ?>)
        <?php }  ?>


    });
</script>
<div class="col-md-12">
    <div id="tabsContent">
        <?php echo displayDaftarWP() ?>
    </div>
</div>