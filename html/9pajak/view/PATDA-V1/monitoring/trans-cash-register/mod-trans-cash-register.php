<?php
session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
	global $DIR, $a, $m, $data;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/{$DIR}/monitoring/trans-cash-register/svc-list-tran.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2'}") . "\">Daftar Transaksi</a></li>\n";
	echo "\t</ul>\n";
}
?>

<link href="<?php echo "style/{$styleFolder}/jquery/jquery-ui-1.8.18.custom.css"; ?>" rel="stylesheet" type="text/css" />
<link href="inc/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css">
<script	type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="inc/js/jquery-ui-1.9.1.js"></script>
<script	src="inc/bootstrap/js/bootstrap.js"></script>
<link href="inc/<?php echo $DIR?>/jtable/themes/jtable.min.css" rel="stylesheet" type="text/css" />
<script src="inc/<?php echo $DIR?>/jtable/jquery.jtable.min.js" type="text/javascript"></script>

<!--<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>-->

<div id="tabsContent">
<?php echo displayMenu() ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#tabsContent").tabs({
            beforeLoad: function (event, ui) {
                ui.panel.html('<img src="image/large-loading.gif">');
            }
        });   
        
        $("#closeCBox").click(function () {
            $("#cBox").css("display", "none");
        });
        $("#modalDialog").dialog({
            autoOpen: false,
            modal: true,
            width: "900",
            resizable: false,
            draggable: false,
            height: 'auto',
            title: "",
            position: ['middle', 50]
        });
    });
    
    function getTranBulanIniTapbox(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=1&p=" + json + "&q=<?php echo base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','url':'function/{$DIR}/svc-download-tapbox.xls.php'}"); ?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/monitoring/trans-tapbox/svc-list-laporan-tran-tapbox.php",
            success: function (msg) {
                $("#modalDialog").html(msg);
            }
        });
    }
    
    function download_excel(id, url, p, l) {

        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

        var page = document.createElement("input");
        page.setAttribute("type", "hidden");
        page.setAttribute("name", 'page');
        page.setAttribute("value", p);
        form.appendChild(page);

        var limit = document.createElement("input");
        limit.setAttribute("type", "hidden");
        limit.setAttribute("name", 'limit');
        limit.setAttribute("value", l);
        form.appendChild(limit);
                
        var a = document.createElement("input");
        a.setAttribute("type", "hidden");
        a.setAttribute("name", 'a');
        a.setAttribute("value", $('#HIDDEN-' + id).attr('a'));
        form.appendChild(a);

        var npwpd = document.createElement("input");
        npwpd.setAttribute("type", "hidden");
        npwpd.setAttribute("name", 'CPM_NPWPD');
        npwpd.setAttribute("value", $('#CPM_NPWPD-' + id).val());
        form.appendChild(npwpd);
        
        var nop = document.createElement("input");
        nop.setAttribute("type", "hidden");
        nop.setAttribute("name", 'CPM_NOP');
        nop.setAttribute("value", $('#CPM_NOP-' + id).val());
        form.appendChild(nop);
        
        var notran = document.createElement("input");
        notran.setAttribute("type", "hidden");
        notran.setAttribute("name", 'NO_TRAN');
        notran.setAttribute("value", $('#NO_TRAN-' + id).val());
        form.appendChild(notran);

        var tran_date1 = document.createElement("input");
        tran_date1.setAttribute("type", "hidden");
        tran_date1.setAttribute("name", 'TRAN_DATE1');
        tran_date1.setAttribute("value", $('#TRAN_DATE1-' + id).val());
        form.appendChild(tran_date1);

        var tran_date2 = document.createElement("input");
        tran_date2.setAttribute("type", "hidden");
        tran_date2.setAttribute("name", 'TRAN_DATE2');
        tran_date2.setAttribute("value", $('#TRAN_DATE2-' + id).val());
        form.appendChild(tran_date2);

        document.body.appendChild(form);

        if ($("#cBox").is(":hidden")) {
            var nmfileAll = 'file-cash-register';
            var nmfile = nmfileAll + '-part-';

            $("#loadlink-" + id).show();

            $.ajax({
                type: "POST",
                url: url,
                data: $(form).serialize() + "&count=1",
                dataType: "json",
                success: function (res) {
                    console.log(res)
                    var sumOfPage = Math.ceil(res.total_row / res.limit);
                    var strOfLink = "";
                    if (res.total_row < res.limit)
                        strOfLink += "<a href='javascript:void(0)' onclick=javascript:download_excel('" + id + "','" + url + "','all','" + res.limit + "')>" + nmfileAll + "</a><br/>";
                    else {
                        for (var page = 1; page <= sumOfPage; page++) {
                            strOfLink += "<a href='javascript:void(0)'  onclick=javascript:download_excel('" + id + "','" + url + "','" + page + "','" + res.limit + "')>" + nmfile + page + "</a><br/>";
                        }
                    }
                    $("#contentLink").html(strOfLink);
                    $("#cBox").css("display", "block");
                    $("#loadlink-" + id).hide();
                }
            });
        } else {
			if(p) form.submit();
        }
        form.parentNode.removeChild(form);

    }
    
    function getDetHarianTranTapbox(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=1&p=" + json + "&q=<?php echo base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','url':'function/{$DIR}/svc-download-tapbox.xls.php'}"); ?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/monitoring/trans-tapbox/svc-list-pembanding-detail-harian.php",
            success: function (msg) {
                $("#modalDialog").html(msg);
            }
        });
    }


</script>
<div id="modalDialog"></div>
<div id="cBox" class="animate" style="width: 205px;z-index:9999; height: 300px; right:35%; top: 10%; border: 1px solid gray; background-color: #eaeaea; display: none; position:fixed; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>            
    </div>        
    <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
</div>
