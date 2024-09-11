<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penundaan-jatuh-tempo', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;
function displayMenuPelayanan() {  // srch
    global $a, $m, $data;
    
	echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/penundaan-jatuh-tempo/svc-jatuh-tempo.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Perubahan Jatuh Tempo</a></li>\n";
    echo "\t</ul>\n";
}
?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>

<script type="text/javascript">
	var page = 1;
	
	function setTabs (tab) {
		$( "#tabsContent" ).tabs( "option", "selected", tab );
		$( "#tabsContent" ).tabs('load', tab);
	}
	
	$(document).ready(function() {        
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });	
        $("#tabsContent").tabs({
            load: function (e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function (e, ui) {
                var $panel = $(ui.panel);
                
                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });

    });
</script>
<div id="tabsContent">
	<?php 
		if(isset($tab)){
			echo "<script language='javascript'>setTabs(".$tab.")</script>";
		}        
		echo displayMenuPelayanan() 
	?>
</div>
 <script>
// $(document).ready(function(){
	
	// // $('#tgl_jatuh_tempo').datepicker({dateFormat:'yy-mm-dd'});
	
	// // $('.tab1 #kecamatanOP').change(function(){
		// // if($(this).val() == ''){
			// // var msg = '<option value>Semua Kelurahan</option>';
			// // $('.tab1 #kelurahanOP').html(msg);
		// // }else{
			// // $.ajax({
			   // // type: 'POST',
			   // // url: './function/PBB/loket/svc-search-city.php',
			   // // data: 'type=3&id='+$(this).val(),
			   // // success: function(msg){
					// // var opt = '<option value>Semua Kelurahan</option>';
					// // opt += msg;
					// // $('.tab1 #kelurahanOP').html(opt);
			   // // }
			// // });
		// // }
	// // });
	
	// // $('#only_nop').click(function(){
		// // if($(this).is(':checked')){
			// // $('#info_lengkap').hide();
			// // $('.tab1 #nop').removeAttr('readonly').attr('placeholder','Masukkan NOP');
		// // }else{
			// // $('.tab1 #nop').attr('readonly','readonly').attr('placeholder','Centang untuk memasukkan NOP').val('');
			// // $('#info_lengkap').show();
		// // }
	// // });
	
// //	$('.tab1 #btn-save').click(function(){
	// $("#form-penerimaan").submit(function(e){
		// e.preventDefault();
		// alert ('123');
		// return false;
		
		// var $btn = $(this);
		// var kec = $('.tab1 #kecamatanOP').val();
		// var kel = $('.tab1 #kelurahanOP').val();
		// var thn = $('.tab1 #tahun').val();
		
		// var $nop = $('.tab1 #nop');
		// var nop = $nop.val();
		// var $tgl_jatuh_tempo = $('.tab1 #tgl_jatuh_tempo');
		// var only_nop = $('#only_nop').is(':checked');
		
		// if($tgl_jatuh_tempo.val() == ''){
			// $tgl_jatuh_tempo.focus();
			// alert('Silakan isi jatuh tempo');
			// return false;
		// }
		
		// if(only_nop){
			// if($.trim($nop.val()).length != 18){
				// $nop.focus();
				// alert('Silakan Isi NOP (18 Karakter).');
				// return false;
			// }else{
				// if(confirm('Apakah anda yakin untuk mengubah jatuh tempo untuk \nNOP '+nop+' ini ?') === false) return false;
			// }
		// }else{
			// nop = '';
			// var nmprop = $('.tab1 #propinsiOP option:selected').text();
			// var nmkab = $('.tab1 #kabupatenOP option:selected').text();
			// var nmkec = $('.tab1 #kecamatanOP option:selected').text();
			// var nmkel = $('.tab1 #kelurahanOP option:selected').text();
			// var ask = 'Apakah anda yakin untuk mengubah jatuh tempo untuk';
			// ask += '\nPropinsi : '+nmprop;
			// ask += '\nKabupaten : '+nmkab;
			// ask += '\nKecamatan : '+nmkec;
			// ask += '\nKelurahan : '+nmkel;
			
			// if(confirm(ask) === false) return false;
		// }
		
		// $btn.attr('disabled',true);
		// $.ajax({
			// type: 'POST',
			// url: './view/PBB/penundaan-jatuh-tempo/svc-jatuh-tempo.php',
			// dataType : 'json',
			// data: {
				// action:$(this).attr('id'),
				// kec:kec,
				// kel:kel,
				// nop:nop,
				// thn:thn,
				// tgl_jatuh_tempo:$tgl_jatuh_tempo.val(),
				
			// },
			// success: function(res){
				// alert(res.msg);
				// // $btn.removeAttr('disabled');
				// // setTabs(0);
			// }
		// });
	// });
	
// // });

// function iniAngka(evt,x){
	// if ($(x).attr('readonly') == 'readonly') return false;
	// var charCode = (evt.which) ? evt.which : event.keyCode;
	// if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13){
		// return true;
	// }else{
		// alert('Input hanya boleh angka!');
		// return false;
	// }
// }
// </script>