<?php
//session_start();
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

if (!isset($_SESSION['npwpd'])) {
    $query = "SELECT * FROM PATDA_WP WHERE CPM_USER='{$data->uname}'";
    $res = mysql_query($query, $DBLink);
    $d = mysql_fetch_array($res);
    $_SESSION['npwpd'] = isset($d['CPM_NPWPD']) ? $d['CPM_NPWPD'] : "";
}

function displayMenu() {
    global $DIR, $a, $m, $data;
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/reklame/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaLapor7','mod':'pel'}") . "\">Draft <b class='notif draf'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/reklame/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaLapor7','mod':'pel'}") . "\">Proses <b class='notif proses'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/reklame/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'4','f':'fPatdaLapor7','mod':'pel'}") . "\">Ditolak <b class='notif ditolak'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/reklame/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'5','f':'fPatdaLapor7','mod':'pel'}") . "\">Disetujui <b class='notif disetujui'></b></li></a>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/reklame/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'0','f':'fPatdaLapor7','mod':'pel'}") . "\">Semua Data</a></li>\n";
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
            data: "function=read_dokumen_notif&tab=draf;proses;ditolak;disetujui",
            url: "function/<?php echo $DIR ?>/reklame/lapor/svc-lapor.php",
            dataType: "json",
            success: function(res) {
                $('.draf').html(res.draf + " new");
                $('.proses').html(res.proses + " new");
                $('.ditolak').html(res.ditolak + " new");
                $('.disetujui').html(res.disetujui + " new");

                if (res.draf == 0)
                    $('.draf').hide();
                if (res.proses == 0)
                    $('.proses').hide();
                if (res.ditolak == 0)
                    $('.ditolak').hide();
                if (res.disetujui == 0)
                    $('.disetujui').hide();
            }
        });
    });
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>


<?php
$id_pajak = 7;
require_once("function/{$DIR}/class-auth.php");
$auth = new Auth();
$auth->check_auth_wp();
?>
