<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);

function displayMenu() {
    global $DIR, $a, $m, $data;
    // s : status { 1 : aktif , 2 : blokir , 3 : menunggu konfirmasi }
    echo "\t<ul>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/registrasi-wp/svc-list-wp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaPelayananRegWp','mod':''}") . "\">Wajib pajak Aktif</a></li>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/registrasi-wp/svc-list-wp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaPelayananRegWp','mod':''}") . "\">Wajib pajak Blokir</a></li>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/registrasi-wp/svc-list-wp.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'fPatdaPelayananRegWp','mod':''}") . "\">Wajib pajak Menunggu Konfirmasi</a></li>\n";
    
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
		$(document).on('click','.download-xls',function(){

			var form = document.createElement("form");
			form.setAttribute("method", 'post');
			form.setAttribute("target", 'excel');
			form.setAttribute("action", 'function/<?php echo $DIR;?>/registrasi-wp/svc-download.xls.php');

			var idTab = document.createElement("input");
			idTab.setAttribute("type", "hidden");
			idTab.setAttribute("name", 's');
			idTab.setAttribute("value", $(this).data('id'));
			form.appendChild(idTab);

			var app = document.createElement("input");
			app.setAttribute("type", "hidden");
			app.setAttribute("name", 'a');
			app.setAttribute("value", '<?php echo $a?>');
			form.appendChild(app);

			var user = document.createElement("input");
			user.setAttribute("type", "hidden");
			user.setAttribute("name", 'CPM_USER');
			user.setAttribute("value", $('#CPM_USER-'+$(this).data('id')).val());
			form.appendChild(user);

			var npwpd = document.createElement("input");
			npwpd.setAttribute("type", "hidden");
			npwpd.setAttribute("name", 'CPM_NPWPD');
			npwpd.setAttribute("value", $('#CPM_NPWPD-'+$(this).data('id')).val());
			form.appendChild(npwpd);

			var jenis = document.createElement("input");
			jenis.setAttribute("type", "hidden");
			jenis.setAttribute("name", 'CPM_JENIS_PAJAK');
			jenis.setAttribute("value", $('#CPM_JENIS_PAJAK-' + $(this).data('id')).val());
			form.appendChild(jenis);

			var asalwp = document.createElement("input");
			asalwp.setAttribute("type", "hidden");
			asalwp.setAttribute("name", 'CPM_ASAL_WP');
			asalwp.setAttribute("value", $('#CPM_ASAL_WP-' + $(this).data('id')).val());
			form.appendChild(asalwp);
			
			document.body.appendChild(form);
			form.submit();
		});
    });
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

