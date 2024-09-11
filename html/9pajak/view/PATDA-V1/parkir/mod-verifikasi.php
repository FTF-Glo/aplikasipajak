<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;

    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'2','f':'fPatdaVerifikasi5','mod':'ver'}") . "\">Tertunda <b class='notif tertunda'></b></a></li>\n";
    #echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'3','f':'fPatdaVerifikasi5','mod':'ver'}") . "\">Proses</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'4','f':'fPatdaVerifikasi5','mod':'ver'}") . "\">Ditolak <b class='notif ditolak_ver'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'5','f':'fPatdaVerifikasi5','mod':'ver'}") . "\">Disetujui <b class='notif disetujui_ver'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'0','f':'fPatdaVerifikasi5','mod':'ver'}") . "\">Semua Data</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/parkir/svc-list-pembanding.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6'}") . "\">Data Pembanding</a></li>\n";
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
            url: "function/<?php echo $DIR ?>/parkir/lapor/svc-lapor.php",
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
            modal: true,
            width: "900",
            resizable: false,
            draggable: false,
            height: 'auto',
            title: "",
            position: ['middle', 50]
        });
	$("#closeCBox").click(function () {
	   $("#cBox").css("display", "none");
	})
    });
    function getDetTranTapbox(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=6&p=" + json + "&q=<?php echo base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','url':'function/{$DIR}/svc-download-tapbox.xls.php'}"); ?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/parkir/svc-list-pembanding-detail.php",
            success: function(msg) {
                $("#modalDialog").html(msg);

            }
        });
    }

    function admit(a, b) {
        alert("hanya boleh diisi oleh wajib pajak");
        return false;
    }
    
    // function download_excel(id, url, p, l) {

    //     var form = document.createElement("form");
    //     form.setAttribute("method", 'post');
    //     form.setAttribute("target", 'excel');
    //     form.setAttribute("action", url);

    //     var page = document.createElement("input");
    //     page.setAttribute("type", "hidden");
    //     page.setAttribute("name", 'page');
    //     page.setAttribute("value", p);
    //     form.appendChild(page);

    //     var limit = document.createElement("input");
    //     limit.setAttribute("type", "hidden");
    //     limit.setAttribute("name", 'limit');
    //     limit.setAttribute("value",l);
    //     form.appendChild(limit);

    //     var tahun = document.createElement("input");
    //     tahun.setAttribute("type", "hidden");
    //     tahun.setAttribute("name", 'TAHUN_PAJAK');
    //     tahun.setAttribute("value", $('#HIDDEN-' + id).attr('tahun'));
    //     form.appendChild(tahun);
        
    //     var bulan = document.createElement("input");
    //     bulan.setAttribute("type", "hidden");
    //     bulan.setAttribute("name", 'MASA_PAJAK');
    //     bulan.setAttribute("value", $('#HIDDEN-' + id).attr('bulan'));
    //     form.appendChild(bulan);

    //     var npwpd = document.createElement("input");
    //     npwpd.setAttribute("type", "hidden");
    //     npwpd.setAttribute("name", 'CPM_NPWPD');
    //     npwpd.setAttribute("value", $('#HIDDEN-' + id).attr('npwpd'));
    //     form.appendChild(npwpd);
        
    //     var alldevice = document.createElement("input");
    //     alldevice.setAttribute("type", "hidden");
    //     alldevice.setAttribute("name", 'alldevice');
    //     alldevice.setAttribute("value", $('#HIDDEN-' + id).attr('deviceid'));
    //     form.appendChild(alldevice);
        
    //     var a = document.createElement("input");
    //     a.setAttribute("type", "hidden");
    //     a.setAttribute("name", 'a');
    //     a.setAttribute("value", $('#HIDDEN-' + id).attr('a'));
    //     form.appendChild(a);
        
    //     var notran = document.createElement("input");
    //     notran.setAttribute("type", "hidden");
    //     notran.setAttribute("name", 'NO_TRAN');
    //     notran.setAttribute("value", $('#NO_TRAN-' + id).val());
    //     form.appendChild(notran);
        
    //     var deviceid = document.createElement("input");
    //     deviceid.setAttribute("type", "hidden");
    //     deviceid.setAttribute("name", 'CPM_DEVICE_ID');
    //     deviceid.setAttribute("value", $('#CPM_DEVICE_ID-' + id).val());
    //     form.appendChild(deviceid);
        
    //     var tran_date1 = document.createElement("input");
    //     tran_date1.setAttribute("type", "hidden");
    //     tran_date1.setAttribute("name", 'TRAN_DATE1');
    //     tran_date1.setAttribute("value", $('#TRAN_DATE1-' + id).val());
    //     form.appendChild(tran_date1);
        
    //     var tran_date2 = document.createElement("input");
    //     tran_date2.setAttribute("type", "hidden");
    //     tran_date2.setAttribute("name", 'TRAN_DATE2');
    //     tran_date2.setAttribute("value", $('#TRAN_DATE2-' + id).val());
    //     form.appendChild(tran_date2);        

    //     document.body.appendChild(form);

    // 	// if($("#cBox").is(":hidden")){
    // 	// 	var nmfileAll = '<?php echo date('yymdhmi'); ?>';
    // 	//         var nmfile = nmfileAll + '-part-';

    // 	// 	$("#loadlink-" + id).show();		

    // 	// 	$.ajax({
    // 	// 	    type: "POST",
    // 	// 	    url: url,
    // 	// 	    data: $(form).serialize()+"&count=1",
    // 	// 	    dataType : "json",
    // 	// 	    success: function (res) {
    // 	// 	        console.log(res)
    // 	// 	        var sumOfPage = Math.ceil(res.total_row / res.limit);
    // 	// 	        var strOfLink = "";
    // 	// 	        if (res.total_row < res.limit)
    // 	// 	            strOfLink += "<a href='javascript:void(0)' onclick=javascript:download_excel('"+id+"','"+url+"','all','"+res.limit+"')>" + nmfileAll + "</a><br/>";
    // 	// 	        else {
    // 	// 	            for (var page = 1; page <= sumOfPage; page++) {
    // 	// 	                strOfLink += "<a href='javascript:void(0)'  onclick=javascript:download_excel('"+id+"','"+url+"','"+page+"','"+res.limit+"')>" + nmfile + page + "</a><br/>";
    // 	// 	            }
    // 	// 	        }
    // 	// 	        $("#contentLink").html(strOfLink);
    // 	// 	        $("#cBox").css("display", "block");
    // 	// 	        $("#loadlink-" + id).hide();
    // 	// 	    }
    // 	// 	});
    // 	// }else{
    // 		form.submit();
    // 	// }	
        
    // }

    function download_excel(id, url) {
        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

        var h = $("#hidden-" + id);
        var mod = h.attr('mod');
        var id_pajak = h.attr('id_pajak');
        var s = h.attr('s');

        var modul = document.createElement("input");
        modul.setAttribute("type", "hidden");
        modul.setAttribute("name", 'mod');
        modul.setAttribute("value", mod);
        form.appendChild(modul);

        var jenis_pajak = document.createElement("input");
        jenis_pajak.setAttribute("type", "hidden");
        jenis_pajak.setAttribute("name", 'idp');
        jenis_pajak.setAttribute("value", id_pajak);
        form.appendChild(jenis_pajak);

        var idTab = document.createElement("input");
        idTab.setAttribute("type", "hidden");
        idTab.setAttribute("name", 'i');
        idTab.setAttribute("value", id);
        form.appendChild(idTab);
        
        var status = document.createElement("input");
        status.setAttribute("type", "hidden");
        status.setAttribute("name", 's');
        status.setAttribute("value", s);
        form.appendChild(status);

        var app = document.createElement("input");
        app.setAttribute("type", "hidden");
        app.setAttribute("name", 'a');
        app.setAttribute("value", '<?php echo $a ?>');
        form.appendChild(app);

        var npwpd = document.createElement("input");
        npwpd.setAttribute("type", "hidden");
        npwpd.setAttribute("name", 'CPM_NPWPD');
        npwpd.setAttribute("value", $('#CPM_NPWPD-' + id).val());
        form.appendChild(npwpd);

        var nama = document.createElement("input");
        nama.setAttribute("type", "hidden");
        nama.setAttribute("name", 'CPM_NAMA_WP');
        nama.setAttribute("value", $('#CPM_NAMA_WP-' + id).val());
        form.appendChild(nama);

        var tahun = document.createElement("input");
        tahun.setAttribute("type", "hidden");
        tahun.setAttribute("name", 'CPM_TAHUN_PAJAK');
        tahun.setAttribute("value", $('#CPM_TAHUN_PAJAK-' + id).val());
        form.appendChild(tahun);

        var bulan = document.createElement("input");
        bulan.setAttribute("type", "hidden");
        bulan.setAttribute("name", 'CPM_MASA_PAJAK');
        bulan.setAttribute("value", $('#CPM_MASA_PAJAK-' + id).val());
        form.appendChild(bulan);

        var tran_date1 = document.createElement("input");
        tran_date1.setAttribute("type", "hidden");
        tran_date1.setAttribute("name", 'CPM_TGL_LAPOR1');
        tran_date1.setAttribute("value", $('#CPM_TGL_LAPOR1-' + id).val());
        form.appendChild(tran_date1);

        var tran_date2 = document.createElement("input");
        tran_date2.setAttribute("type", "hidden");
        tran_date2.setAttribute("name", 'CPM_TGL_LAPOR2');
        tran_date2.setAttribute("value", $('#CPM_TGL_LAPOR2-' + id).val());
        form.appendChild(tran_date2);
        
        
        var triwulan = document.createElement("input");
        triwulan.setAttribute("type", "hidden");
        triwulan.setAttribute("name", 'CPM_TRIWULAN');
        triwulan.setAttribute("value", $('#CPM_TRIWULAN-' + id).val());
        form.appendChild(triwulan);

        document.body.appendChild(form);
        form.submit();
    }   
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

<div id="modalDialog"></div>
<div id="cBox" class="animate" style="width: 205px;z-index:9999; height: 300px; position: absolute; right: 50px; top: 100px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>            
    </div>        
    <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
</div>
