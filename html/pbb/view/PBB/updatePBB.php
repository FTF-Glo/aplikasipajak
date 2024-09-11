<?php
// prevent direct access
if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

//prevent access to not accessible module
if (!$bOK) {
	return false;
}


if (!isset($opt)) {
?>
	<link href="view/PBB/monitoring.css" rel="stylesheet" type="text/css" />

	<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
	<script type=\"text/javascript\" src=\"/view/PBB/pembayaran.js\"> </script> <script>
		$(document).ready(function(){
    $("#closeCBox").click(function(){
        $("#cBox").css("display","none");
    })
})
    
$(function() {
        $( "#jatuh-tempo1-1" ).datepicker({ dateFormat: "yy-mm-dd"});
		$( "#jatuh-tempo2-1" ).datepicker({ dateFormat: "yy-mm-dd"});
		$( "#jatuh-tempo1-2" ).datepicker({ dateFormat: "yy-mm-dd"});
		$( "#jatuh-tempo2-2" ).datepicker({ dateFormat: "yy-mm-dd"});
});

/*
function exportXls (sts) {
	var tempo1 = $("#jatuh-tempo1-"+sts).val();
	var tempo2 = $("#jatuh-tempo2-"+sts).val();
	var tahun = $("#tahun-pajak-"+sts).val();
	var nop = $("#nop-"+sts).val();
	//var status = $("#sel-status").val();
	var nama = $("#wp-name-"+sts).val();
	nama = nama.replace(" ","%20");
	var jmlBaris = $("#jml-baris").val();
	var kc = $("#kecamatan-"+sts).val();
	var kl = $("#kelurahan-"+sts).val();
	//var $j = jQuery.noConflict();
	
	//$(document).ready(function() {
	//external attribute
	window.open("view/PBB/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&na: ="+nama+"&t1="+tempo1+"&t2="+tempo2+"&th="+tahun+"&n="+nop+"&st="+sts+"&kc="+kc+"&kl="+kl+"&exp=1");
	//window.open('', 'TheWindow');
	//document.getElementById('TheForm').submit();

		
}
*/

function onSubmit (sts) {
	var tempo1 = $("#jatuh-tempo1-"+sts).val();
	var tempo2 = $("#jatuh-tempo2-"+sts).val();
	var tahun = $("#tahun-pajak-"+sts).val();
	var nop = $("#nop-"+sts).val();
	//var status = $("#sel-status").val();
	var nama = $("#wp-name-"+sts).val();
	nama = nama.replace(" ","%20");
	var jmlBaris = $("#jml-baris").val();
	var kc = $("#kecamatan-"+sts).val();
	var kl = $("#kelurahan-"+sts).val();
	//var par = "&t1="+tempo1+"&t2="+tempo1+"&th="+tahun+"&n="+nop+"&st="+status+"&j="+jmlBaris;
	$("#monitoring-content-"+sts).html("loading ...");
	
        
	var svc = "";
	$("#monitoring-content-"+sts).load("view/PBB/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
	{na: nama,t1:tempo1,t2:tempo2,th:tahun,n:nop,st:sts,kc:kc,kl:kl}, function(response, status, xhr) {
	  if (status == "error") {
		var msg = "Sorry but there was an error: ";
		$("#monitoring-content-"+sts).html(msg + xhr.status + " " + xhr.statusText);
	  }
	});
        
}

function onSearch (sts) {
	var nop = $("#nop-"+sts).val();
	var nama = $("#wp-name-"+sts).val();
	nama = nama.replace(" ","%20");
	var jmlBaris = $("#jml-baris").val();
	//var par = "&t1="+tempo1+"&t2="+tempo1+"&th="+tahun+"&n="+nop+"&st="+status+"&j="+jmlBaris;
	$("#monitoring-content-"+sts).html("loading ...");
	
        
	var svc = "";
	$("#monitoring-content-"+sts).load("view/PBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
	{na: nama,n:nop,st:sts}, function(response, status, xhr) {
	  if (status == "error") {
		var msg = "Sorry but there was an error: ";
		$("#monitoring-content-"+sts).html(msg + xhr.status + " " + xhr.statusText);
	  }
	});
        
}

function toExcel(sts){
        var nmfile = '<?php echo date('yymdhmi') . '-part-'; ?>';
	var tempo1 = $("#jatuh-tempo1-"+sts).val();
	var tempo2 = $("#jatuh-tempo2-"+sts).val();
	var tahun = $("#tahun-pajak-"+sts).val();
	var nop = $("#nop-"+sts).val();
	var nama = $("#wp-name-"+sts).val();
	nama = nama.replace(" ","%20");
	var jmlBaris = $("#jml-baris").val();
	var kc = $("#kecamatan-"+sts).val();
	var kl = $("#kelurahan-"+sts).val();
        
        if(sts == 1)
            $("#loadlink1").show();                
        else
            $("#loadlink2").show();
        
        $.ajax({
            type: "POST",
            url: "./view/PBB/svc-countforlink.php",
            data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>"+"&na="+nama+"&t1="+tempo1+"&t2="+tempo2+"&th="+tahun+"&n="+nop+"&st="+sts+"&kc="+kc+"&kl="+kl,
            success: function(msg){
                var sumOfPage = Math.ceil(msg/1000);
                var strOfLink = ""
                
                for(var page=1; page<=sumOfPage; page++){
                    strOfLink += '<a href="view/PBB/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>'+'&na='+nama+'&t1='+tempo1+'&t2='+tempo2+'&th='+tahun+'&n='+nop+'&st='+sts+'&kc='+kc+'&kl='+kl+'&p='+page+'">'+nmfile+page+'</a><br/>';
                }
                $("#contentLink").html(strOfLink);                
                $("#cBox").css("display","block");
                
                if(sts == 1)
                    $("#loadlink1").hide();                
                else
                    $("#loadlink2").hide();
            }
        });          
}

function updateCount(){
        var tahun = $("#tahun-pajak-"+1).val();        
        $("#ketAkm").html('<span style="font-size: 12px;">Loading...</span>');
        $.ajax({
            type: "POST",
            url: "./view/PBB/svc-count.php",
            data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>"+"&th="+tahun,
            success: function(msg){
                var vcount = msg.split("/");
                $("#ketAkm").html('<span style="font-size: 13px">Tahun Berjalan (<b>'+vcount[0]+'</b>) + Tunggakan (<b>'+vcount[1]+'</b>) = Total Pembayaran (<b>'+vcount[2]+'</b>)</span>');
            }
        });          
}


function setPage (pg,sts) {
	var tempo1 = $("#jatuh-tempo-"+sts).val();
	var tempo2 = $("#jatuh-tempo2-"+sts).val();
	var tahun = $("#tahun-pajak-"+sts).val();
	var nop = $("#nop-"+sts).val();
	//var status = $("#sel-status").val();
	var kc = $("#kecamatan-"+sts).val();
	var kl = $("#kelurahan-"+sts).val();
	var nama = $("#wp-name-"+sts).val();
	nama = nama.replace(" ","%20");
	//var jmlBaris = $("#jml-baris").val();
	$("#monitoring-content-"+sts).html("loading ...");
	$("#monitoring-content-"+sts).load("view/PBB/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>&p="+pg, 
	{na: nama,t1:tempo1,t2:tempo2,th:tahun,n:nop,st:sts,kc:kc,kl:kl},function(response, status, xhr) {
	  if (status == "error") {
		var msg = "Sorry but there was an error: ";
		$("#monitoring-content-"+sts).html(msg + xhr.status + " " + xhr.statusText);
	  }
	});
}

function showKelurahan(sts) {
	var id = $('select#kecamatan-'+sts).val()
		var request = $.ajax({
		  url: "view/PBB/svc-kecamatan.php",
		  type: "POST",
		  data: {id : id,kel:1},
		  dataType: "json",
		  success:function(data){
		  	var c = data.msg.length;
			var options = '';
			options += '<option value="">Pilih Semua</option>';
			for (var i=0;i<c;i++) {
				options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
			  	$("select#kelurahan-"+sts).html(options);
			}
		  }
		});
}

$(function(){
	$("select#kecamatan-1").change(function(){
		showKelurahan(1);
	})
	$("select#kecamatan-2").change(function(){
		showKelurahan(2);
	})	
//	$("select#kecamatan-3").change(function(){
//		showKelurahan(3);
//	})	
})

$(function() {
	$( "#tabs" ).tabs();
});
	
function showKecamatan(sts) {
	var request = $.ajax({
		  url: "view/PBB/svc-kecamatan.php",
		  type: "POST",
		  data: {id : "1671"},
		  dataType: "json",
		  success:function(data){
		  	var c = data.msg.length;
			var options = '';
			options += '<option value="">Pilih Semua</option>';
			for (var i=0;i<c;i++) {
				options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
			  	$("select#kecamatan-"+sts).html(options);
			}
		  }
	});

}

function showBulan(id){
	var bulan = Array("Semua","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
	var options = '';
	for (var i=0;i<13;i++) {
		options += '<option value="' + i + '">' + bulan[i] + '</option>';
			  	$("select#"+id).html(options);
	}
}

function showModelE2() {
	var tahun = $("#tahun-pajak-3").val();
	//var kelurahan = $("#kelurahan-3").val();
	var kecamatan = $("#kecamatan-3").val();
	var namakec = $("#kecamatan-3 option:selected").text();
/*	var bulan1 = $("#bulan-1").val();
	var buku1 = $("#buku-1").val();
	var buku2 = $("#buku-2").val();*/
        var s_periode = Number($("#periode1").val());
        var e_periode = Number($("#periode2").val());
	var sts = 1;


	if((s_periode == -1 && e_periode != -1) || (s_periode != -1 && e_periode == -1) || (s_periode > e_periode)){
            alert("Pastikan pilih periode dengan benar!");
        }
        else{
            $("#monitoring-content-3").html("loading ...");
            $("#monitoring-content-3").load("view/PBB/svc-monitoring-e2.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>",
            {th:tahun,st:sts,kc:kecamatan,n:namakec,speriode:s_periode,eperiode:e_periode},function(response, status, xhr) {
              if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
              }
            });
         }
//        window.open("view/PBB/svc-toexcel-e2.php?q=<?//=base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>"+"&n="+namakec+"&kc="+kecamatan+"&st="+sts+"&th="+tahun);
}

function excelModelE2(){
	var tahun = $("#tahun-pajak-3").val();
	var kecamatan = $("#kecamatan-3").val();
	var namakec = $("#kecamatan-3 option:selected").text();
        var s_periode = Number($("#periode1").val());
        var e_periode = Number($("#periode2").val());
	var sts = 1;

//	$("#monitoring-content-3").html("loading ...");
//	$("#monitoring-content-3").load("view/PBB/svc-monitoring-e2.php?q=<?//=base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>",
//	{th:tahun,st:sts,kc:kecamatan,n:namakec},function(response, status, xhr) {
//	  if (status == "error") {
//		var msg = "Sorry but there was an error: ";
//		$("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
//	  }
//	});
	if((s_periode == -1 && e_periode != -1) || (s_periode != -1 && e_periode == -1) || (s_periode > e_periode)){
            alert("Pastikan pilih periode dengan benar!");
        }
        else{
            window.open("view/PBB/svc-toexcel-e2.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>"+"&n="+namakec+"&kc="+kecamatan+"&st="+sts+"&th="+tahun+"&speriode="+s_periode+"&eperiode="+e_periode);
        }
}

function excel_export(id){
	var content = $(id).html();
	window.open('data:application/vnd.ms-excel,' + encodeURIComponent(content));
} 

function excel_export_e2(){
	var tahun = $("#tahun-pajak-3").val();
	var kecamatan = $("#kecamatan-3").val();
	var namakec = $("#kecamatan-3 option:selected").text();
	var sts = 1;

        window.open("view/PBB/svc-toexcel-e2.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>"+"&n="+namakec+"&kc="+kecamatan+"&st="+sts+"&th="+tahun);
} 

$(document).ready(function() {
  	showKecamatan(1);
	showKecamatan(2);
	showKecamatan(3);
	showBulan("bulan-1");
	showBulan("bulan-2");
//	$("#kecamatan-3").change(function() {
//		showModelE2();
//	});
	$('#tabs').tabs({
		select: function(event, ui) { // select event
			$(ui.tab); // the tab selected
			if (ui.index==2) {
				//showModelE2();
			}
		}
	});
});
</script>

	<body>
		<div id="div-search">
			<div id="tabs">
				<ul>
					<li><a href="#tabs-4">Update Data</a></li>
				</ul>
				<div id="tabs-4">
					<fieldset>
						<form id="TheForm-1" method="post" action="view/PBB/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
							<table width="1063" border="0" cellspacing="0" cellpadding="2">
								<tr>
									<td width="73">NOP </td>
									<td width="3">:</td>
									<td><input type="text" name="nop-4" id="nop-4" /></td>
									<td>&nbsp;</td>
									<td>Nama&nbsp;Wajib&nbsp;Pajak</td>
									<td>:</td>
									<td width="144"><input type="text" name="wp-name" id="wp-name-4" /></td>
									<td width="180">
										<input type="button" name="button2" id="button2" value="Submit" onClick="onSearch (4)" />
										<!--<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>-->
										<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
									</td>
								</tr>
							</table>
						</form>
					</fieldset>
					<div id="monitoring-content-4" class="monitoring-content">
					</div>
				</div>
			</div>
		</div>
	<?php
}
	?>
	<div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
		<div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
			<div style="float: left;">
				<span style="font-size: 12px;">Link Download</span>
			</div>
			<div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
		</div>
		<div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
	</div>


	<div id="cAkumulasi" style="position: absolute; top: 160px; right: 23px; display: block; overflow: auto;">
		<div style="float: left; padding-top: 5px;" id="ketAkm"><span style="font-size: 13px; color:#fff">Tahun Berjalan (<b>0</b>) + Tunggakan (<b>0</b>) = Total Pembayaran (<b>0</b>)</span></div>&nbsp;&nbsp;
		<input style="float: right;" type="button" name="updateCount" id="updateCount" value="Update" onClick="updateCount()" />
	</div>
	</body>

	<script language="javascript">
		$(document).ready(function() {
			var tahun = $("#tahun-pajak-1").val();
			$.ajax({
				type: "POST",
				url: "./view/PBB/svc-count.php",
				data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun,
				success: function(msg) {
					//            alert(msg);
					var vcount = msg.split("/");
					$("#ketAkm").html('<span style="font-size: 13px; color:#fff">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
				}
			});


			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})
	</script>