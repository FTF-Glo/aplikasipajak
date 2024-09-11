<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/kartudata/svc-list-kartudata.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':''}") . "\">Kartu Data</a></li>\n";
    
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
        
        $("#closeCBox").click(function () {
            $("#cBox").css("display", "none");
        })
        
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
    
    function download_execute(_npwpd, _tahun, _jnspajak, _bulan, _url){
		var _a = "<?php echo $a?>";
        var url = (_url)? _url : "view/<?php echo $DIR ?>/kartudata/svc-download-xls.php";
        
		var form = document.createElement("form");
			form.setAttribute("method", 'post');
			form.setAttribute("target", 'excel');
			form.setAttribute("action", url);

		var a = document.createElement("input");
			a.setAttribute("type", "hidden");
			a.setAttribute("name", 'a');
			a.setAttribute("value", _a);
			form.appendChild(a);
			
		var jnspajak = document.createElement("input");
			jnspajak.setAttribute("type", "hidden");
			jnspajak.setAttribute("name", 'jnspajak');
			jnspajak.setAttribute("value", _jnspajak);
			form.appendChild(jnspajak);
			
		var npwpd = document.createElement("input");
			npwpd.setAttribute("type", "hidden");
			npwpd.setAttribute("name", 'npwpd');
			npwpd.setAttribute("value", _npwpd);
			form.appendChild(npwpd);

		var tahun = document.createElement("input");
			tahun.setAttribute("type", "hidden");
			tahun.setAttribute("name", 'tahun');
			tahun.setAttribute("value", _tahun);
			form.appendChild(tahun);
		
		if(_bulan){
			var bulan = document.createElement("input");
			bulan.setAttribute("type", "hidden");
			bulan.setAttribute("name", 'bulan');
			bulan.setAttribute("value", _bulan);
			form.appendChild(bulan);
		}
			
		document.body.appendChild(form);
		form.submit();
	}
    
    function download_excel() {
        var _npwpd = $('#npwpd-download').val();
        var _tahun = $('#tahun-download').val();
        var _jnspajak = $('#jnspajak-download').val();
        download_execute(_npwpd, _tahun, _jnspajak);
    }
    
    function download_kartudata_excel(id) {
		
        var _url = 'view/<?php echo $DIR ?>/kartudata/svc-kartudata-download-xls.php';
        var _a = "<?php echo $a?>";
        
        var form = document.createElement("form");
			form.setAttribute("method", 'post');
			form.setAttribute("target", 'excel');
			form.setAttribute("action", _url);

		var a = document.createElement("input");
			a.setAttribute("type", "hidden");
			a.setAttribute("name", 'a');
			a.setAttribute("value", _a);
			form.appendChild(a);
			
		var fields = ['CPM_NPWPD','CPM_TAHUN','CPM_NAMA','CPM_ALAMAT','CPM_ALAMAT','CPM_KECAMATAN','CPM_KELURAHAN','CPM_JENIS_PAJAK'];
		
		for(var x in fields){
			if(x!='remove'){
				console.log(fields[x]);
				var field = document.createElement("input");
				field.setAttribute("type", "hidden");
				field.setAttribute("name", fields[x]);
				// if(fields[x] == 'CPM_KELURAHAN' || fields[x] == 'CPM_KECAMATAN')
					// field.setAttribute("value", $('#'+fields[x]).val());
				// else
					// field.setAttribute("value", $('#'+fields[x]+'-'+id).val());
				field.setAttribute("value", $('#'+fields[x]+'-'+id).val());
				form.appendChild(field);
			}
		}
			
		document.body.appendChild(form);
		form.submit();
    }
    
    function toExcel(npwpd,jnspajak,jnspajak_label) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";
        var nmfile = 'kartudata'+ "<?php echo date("dmYhis") ?>";

        var str_data = "";
		str_data += "&npwpd=" + npwpd;
		
        $("#jnspajak-label").html(jnspajak_label);
        $("#jnspajak-download").val(jnspajak);
        
        $("#npwpd-label").html(npwpd);
        $("#npwpd-download").val(npwpd);
        $("#cBox").css("display", "block");
    }
    
    function download_excel_profilwp(id, url) {

		var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

		var idTab = document.createElement("input");
        idTab.setAttribute("type", "hidden");
        idTab.setAttribute("name", 's');
        idTab.setAttribute("value", id);
        form.appendChild(idTab);

		var app = document.createElement("input");
        app.setAttribute("type", "hidden");
        app.setAttribute("name", 'a');
        app.setAttribute("value", '<?php echo $a?>');
        form.appendChild(app);

		var jenis_pajak = document.createElement("input");
        jenis_pajak.setAttribute("type", "hidden");
        jenis_pajak.setAttribute("name", 'CPM_JENIS_PAJAK');
        jenis_pajak.setAttribute("value", $('#CPM_JENIS_PAJAK-' + id).val());
        form.appendChild(jenis_pajak);

		var npwpd = document.createElement("input");
        npwpd.setAttribute("type", "hidden");
        npwpd.setAttribute("name", 'CPM_NPWPD');
        npwpd.setAttribute("value", $('#CPM_NPWPD-' + id).val());
        form.appendChild(npwpd);

        var nama = document.createElement("input");
        nama.setAttribute("type", "hidden");
        nama.setAttribute("name", 'CPM_NAMA');
        nama.setAttribute("value", $('#CPM_NAMA-' + id).val());
        form.appendChild(nama);
        
        var alamat = document.createElement("input");
        alamat.setAttribute("type", "hidden");
        alamat.setAttribute("name", 'CPM_ALAMAT');
        alamat.setAttribute("value", $('#CPM_ALAMAT-' + id).val());
        form.appendChild(alamat);
		
        var kecamatan = document.createElement("input");
        kecamatan.setAttribute("type", "hidden");
        kecamatan.setAttribute("name", 'CPM_KECAMATAN');
        kecamatan.setAttribute("value", $('#CPM_KECAMATAN-' + id).val());
        form.appendChild(kecamatan);
		
        var kelurahan = document.createElement("input");
        kelurahan.setAttribute("type", "hidden");
        kelurahan.setAttribute("name", 'CPM_KELURAHAN');
        kelurahan.setAttribute("value", $('#CPM_KELURAHAN-' + id).val());
        form.appendChild(kelurahan);
        
        document.body.appendChild(form);
        form.submit();
    }
    
    function getDetail(json) {
        $("#modalDialog").html('<img src="image/large-loading.gif" />');
        $("#modalDialog").dialog('open');
        $.ajax({
            data: "i=1&p=" + json + "&q=<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}','u':'{$data->uname}','i':'2'}"); ?>",
            type: "post",
            url: "view/<?php echo $DIR ?>/kartudata/svc-detail.php",
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
<div id="cBox" class="animate" style="width: 300px; height: 150px; position: fixed; left: 30%; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 294px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>            
    </div>        
    <div id="contentLink" style="padding: 3px; width: 270px; overflow: auto;">
		<table style="width:100%" class="transparent">
			<tr>
				<td>Jenis Pajak</td>
				<td>: <span id='jnspajak-label'></span><input type='hidden' id='jnspajak-download'></td>
			</tr>
			<tr>
				<td>NPWPD</td>
				<td>: <span id='npwpd-label'></span><input type='hidden' id='npwpd-download'></td>
			</tr>
			<tr>
				<td>Tahun</td>
				<td>: <select id='tahun-download'>
					<option value=''>All</option>
					<?php
					for($x=date("Y")-5;$x<=date("Y");$x++){
						echo "<option value='{$x}' ".(date("Y")==$x? "selected" : "").">{$x}</option>";
					}
					?>						
					</select>
				</td>
			</tr>
			<tr><td colspan='2'>&nbsp;</td></tr>
			<tr>
				<td colspan='2'><button type='button' onclick="javascript:download_excel()" style="float:right">Download</button></td>
			</tr>
		</table>
		
    </div>
</div>
