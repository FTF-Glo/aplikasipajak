<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/rekap/svc-list-rekap.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':''}") . "\">Rekap</a></li>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/rekap/svc-list-kendali.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'','mod':''}") . "\">Buku Kendali</a></li>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/rekap/svc-list-rekap-bulan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'','mod':''}") . "\">Rekap Bulanan</a></li>\n";
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

    function download_excel_new(url) {
        var tahun_pajak = $('#simpatda_tahun_pajak-1');
        var jenis_pajak = $('#CPM_JENIS_PAJAK-1');

        if(tahun_pajak.val() && jenis_pajak.val()){
            var form = document.createElement("form");
            form.setAttribute("method", 'post');
            form.setAttribute("target", 'excel');
            form.setAttribute("action", url);

            var tahun = document.createElement("input");
            tahun.setAttribute("type", "hidden");
            tahun.setAttribute("name", 'simpatda_tahun_pajak');
            tahun.setAttribute("value", tahun_pajak.val());
            form.appendChild(tahun);

            var jenis = document.createElement("input");
            jenis.setAttribute("type", "hidden");
            jenis.setAttribute("name", 'simpatda_jenis_pajak');
            jenis.setAttribute("value", jenis_pajak.val());
            form.appendChild(jenis);

            var a = document.createElement("input");
            a.setAttribute("type", "hidden");
            a.setAttribute("name", 'a');
            a.setAttribute("value", $('#HIDDEN-1').attr('a'));
            form.appendChild(a);

            document.body.appendChild(form);
            form.submit();
        }else {
            showDialog("Error","Silahkan pilih Jenis Pajak dan Tahun terlebih dahulu.");
        }
    }

    function download_excel(id, url) {
        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

        var tahun = document.createElement("input");
        tahun.setAttribute("type", "hidden");
        tahun.setAttribute("name", 'simpatda_tahun_pajak');
        tahun.setAttribute("value", $('#simpatda_tahun_pajak-' + id).val());
        form.appendChild(tahun);

        var jenis = document.createElement("input");
        jenis.setAttribute("type", "hidden");
        jenis.setAttribute("name", 'jenis');
        jenis.setAttribute("value", $('#CPM_JENIS_PAJAK-' + id).val());
        form.appendChild(jenis);

		var jenis_nm = document.createElement("input");
        jenis_nm.setAttribute("type", "hidden");
        jenis_nm.setAttribute("name", 'jenis_nm');
        jenis_nm.setAttribute("value", $('#CPM_JENIS_PAJAK-' + id+' :selected').html());
        form.appendChild(jenis_nm);

        var date1 = document.createElement("input");
        date1.setAttribute("type", "hidden");
        date1.setAttribute("name", 'date1');
        date1.setAttribute("value", $('#CPM_TGL_LAPOR1-' + id).val());
        form.appendChild(date1);

        var date2 = document.createElement("input");
        date2.setAttribute("type", "hidden");
        date2.setAttribute("name", 'date2');
        date2.setAttribute("value", $('#CPM_TGL_LAPOR2-' + id).val());
        form.appendChild(date2);

        var a = document.createElement("input");
        a.setAttribute("type", "hidden");
        a.setAttribute("name", 'a');
        a.setAttribute("value", $('#HIDDEN-' + id).attr('a'));
        form.appendChild(a);

        document.body.appendChild(form);
		form.submit();
    }
    
    function download_excel_rekap_bulan(jenis,tahun,bulan) {
		var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", 'function/PATDA-V1/rekap/svc-download.rekap-bulan-detail.xls.php');
        
        var a = document.createElement("input");
        a.setAttribute("type", "hidden");
        a.setAttribute("name", 'a');
        a.setAttribute("value", '<?php echo $a?>');
        form.appendChild(a);
        
		var nmfile = document.createElement("input");
        nmfile.setAttribute("type", "hidden");
        nmfile.setAttribute("name", 'nmfile');
        nmfile.setAttribute("value", 'sudahbayar-<?php echo date("dmYhis") ?>.xls');
        form.appendChild(nmfile);
        
        var jns = document.createElement("input");
        jns.setAttribute("type", "hidden");
        jns.setAttribute("name", 'jenis');
        jns.setAttribute("value",jenis);
        form.appendChild(jns);

        var thn = document.createElement("input");
        thn.setAttribute("type", "hidden");
        thn.setAttribute("name", 'simpatda_tahun_pajak');
        thn.setAttribute("value", tahun);
        form.appendChild(thn);

        var bln = document.createElement("input");
        bln.setAttribute("type", "hidden");
        bln.setAttribute("name", 'simpatda_bulan_pajak');
        bln.setAttribute("value", bulan);
        form.appendChild(bln);
        
        var status = document.createElement("input");
        status.setAttribute("type", "hidden");
        status.setAttribute("name", 's');
        status.setAttribute("value", 1);
        form.appendChild(status);

        document.body.appendChild(form);
		form.submit();
	}
	
	function getDetail(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=1&p=" + json + "&q=<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}','u':'{$data->uname}','i':'2'}"); ?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/rekap/svc-rekap-detail.php",
            success: function (msg) {
                $("#modalDialog").html(msg);
            }
        });
    }
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

<div id="modalDialog"></div>
