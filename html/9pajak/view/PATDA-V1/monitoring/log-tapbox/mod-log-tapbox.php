<?php
session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/log-tapbox/svc-list-log-tapbox.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'11','s':'11','f':'','mod':'per'}") . "\">Log Alarm Tapbox</a></li>\n";
    echo "\t</ul>\n";
}
?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#tabsContent").tabs({
            beforeLoad: function(event, ui) {
                ui.panel.html('<img src="image/large-loading.gif" />');
            }
        });
        $.ajax({
            type: "post",
            data: "function=read_dokumen_notif&tab=tertunda;ditolak_ver;disetujui_ver",
            url: "function/<?php echo $DIR ?>/hotel/lapor/svc-lapor.php",
            dataType: "json",
            success: function(res) {
                $('.tertunda').html(res.tertunda + " new");
                $('.ditolak_ver').html(res.ditolak_ver + " new");
                $('.disetujui_ver').html(res.disetujui_ver + " new");

                if (res.tertunda == 0)
                    $('.tertunda').hide();
                if (res.ditolak_ver == 0)
                    $('.ditolak_ver').hide();
                if (res.disetujui_ver == 0)
                    $('.disetujui_ver').hide();
            }
        });
        $("#modalDialog").dialog({
            autoOpen: false,
            modal: false,
            width: "900",
            resizable: false,
            draggable: true,
            height: '580',
            title: "",
            position: ['middle', 40]
        });
    });
    function getDetTranTapbox(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=6&p="+json+"&q=<?php echo base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6'}");?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/monitoring/log-tapbox/svc-list-log-tapbox-detail.php",
            success: function(msg) {
                $("#modalDialog").html(msg);
                
            }
        });

    }

</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

<div id="modalDialog"></div>
