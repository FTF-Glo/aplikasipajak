<?php
//session_start();
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
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/surat-paksa/svc-list-paksa.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'0','f':'fPatdaSuratPaksa','mod':'pel'}") . "\">Semua Data</a></li>\n";
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
    });
    
    function download_excel(id, url) {
        var form = $("<form></form>");
        var npwpd = $("<input type='hidden' name='CPM_NPWPD' value='"+$('#HIDDEN-' + id).attr('npwpd')+"'>");
        var tahun = $("<input type='hidden' name='TAHUN_PAJAK' value='"+$('#HIDDEN-' + id).attr('tahun')+"'>");
        var bulan = $("<input type='hidden' name='MASA_PAJAK' value='"+$('#HIDDEN-' + id).attr('bulan')+"'>");
        var alldevice = $("<input type='hidden' name='alldevice' value='"+$('#HIDDEN-' + id).attr('deviceid')+"'>");
        var a = $("<input type='hidden' name='a' value='<?php echo $a?>'>");
        var notran = $("<input type='hidden' name='NO_TRAN' value='" + $('#NO_TRAN-' + id).val() + "'>");
        var deviceid = $("<input type='hidden' name='CPM_DEVICE_ID' value='" + $('#CPM_DEVICE_ID-' + id).val() + "'>");
        var tran_date1 = $("<input type='hidden' name='TRAN_DATE1' value='" + $('#TRAN_DATE1-' + id).val() + "'>");
        var tran_date2 = $("<input type='hidden' name='TRAN_DATE2' value='" + $('#TRAN_DATE2-' + id).val() + "'>");
        form.attr("action", url).attr("method", "post").attr("target", "excel").append(npwpd).append(tahun).append(bulan).append(alldevice).append(a).append(notran).append(deviceid).append(tran_date1).append(tran_date2).submit();
    }
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

