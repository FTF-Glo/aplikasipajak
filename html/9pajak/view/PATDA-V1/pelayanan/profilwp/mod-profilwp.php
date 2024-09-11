<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/profilwp/svc-list-profilwp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaPelayananRegWp','mod':''}") . "\">Objek Pajak Aktif</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/profilwp/svc-list-profilwp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaPelayananRegWp','mod':''}") . "\">OP Non Aktif</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/profilwp/svc-list-profilwp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'fPatdaPelayananRegWp','mod':''}") . "\">Log Penghapusan</a></li>\n";
    
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
        
        document.body.appendChild(form);
        form.submit();
    }
	$(function() {
		var dialog = $( "#dialog-confirm" ).dialog({
			autoOpen: false,
			resizable: false,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
				"Aktifkan": function() {
					$.ajax({
						 type:'post',
						 data:{id:$('#reff_id').val(),'function':'activated_npwpd',m:'<?php echo $a;?>',u:'<?php echo $data->uname;?>'},
						 url : 'view/<?php echo $DIR;?>/pelayanan/profilwp/svc-profilwp.php',
						 dataType:'json',
						 success:function(data){
							alert(data.msg);
							if(data.res == 1) $('#cari-'+$('#reff_idx').val()).click();
						 }
					});
					$( this ).dialog( "close" );
				},
				"Hapus": function() {
					deleteNPWPD($('#reff_id').val());
					$( this ).dialog( "close" );
				},
				Cancel: function() {
				  $( this ).dialog( "close" );
				}
			}
		});
		$(document).on("click",'.btn-action', function() {
			$('#reff_id').val($(this).data('id'));
			$('#reff_idx').val($(this).data('idx'));
			dialog.dialog("open");
		});
	});
</script>
<div id="dialog-confirm" title="Informasi">
  <p>Silakan pilih tindakan yang ingin diproses</p>
  <input type="hidden" id="reff_id">
  <input type="hidden" id="reff_idx">
</div>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

