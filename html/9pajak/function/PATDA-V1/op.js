$(function(){
	
	if(typeof $().select2 === 'function'){
		
		if ($('#CPM_NPWPD').attr('readonly') !== 'readonly'){ 
			
			$('#CPM_NPWPD').select2({
				placeholder: "Input NPWPD",
				allowClear: false,
				ajax: {
					url: 'function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php',
					type: 'POST',
					dataType: 'json',
					delay: 250,
					cache: true,
					data: function (params) {
						postData = {};
						postData.function = 'get_list_npwpd';
						postData.TBLJNSPJK = $('#TBLJNSPJK').val();
						postData.CPM_NPWPD = params.term;
						return postData;
					},
					processResults: function (data, params) {return {results: data.items};},
				},
				escapeMarkup: function (markup) { return markup;},
				minimumInputLength: 3,
				templateResult: formatRepo,
				templateSelection: function(repo, con) {
					return (repo.id)? repo.id : 'NPWPD';
				}
			});
			$('#CPM_NPWPD').on("select2:select", function(e) { 
				searchWP();
			}).on("select2:unselect", function (e) {
				if($('#CPM_ID_PROFIL').length) $('#CPM_ID_PROFIL').val('');
				else $("#CPM_ID").val('');
				
				$('#CPM_NPWPD').val('');
				$('#CPM_NAMA_WP').val('');
				$('#CPM_ALAMAT_WP').val('');
				$('#CPM_NOP').val('');
				$('#CPM_NAMA_OP').val('');
				$('#CPM_ALAMAT_OP').val('');
				$(".CPM_GOL option").prop('selected', false).attr('disabled', 'disabled');
				$('#CPM_TRUCK_ID').val('');
				$('#CPM_TELEPON_WP').val('');
				$('#CPM_TELEPON_OP').val('');
			});
		}
		
		$('#CPM_KECAMATAN_OP').select2({placeholder: "KECAMATAN"});
		$('#CPM_KELURAHAN_OP').select2({placeholder: "KELURAHAN"});
	
	}
	
	$('#CPM_KECAMATAN_OP').change(function(){
		$.ajax({
			type: "POST",
			url: "function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php",
			data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
			async:false,
			success: function(html){
				$('#CPM_KELURAHAN_OP').html(html);
			},
			complete: function(){
				$('#btn-submit').removeAttr('disabled');
			}
		});
	});
	
	if($('#CPM_KECAMATAN_OP').val()!==''){
		$('#CPM_KECAMATAN_OP').trigger('change');
		var kel = $('#CPM_KECAMATAN_OP').data('kel');
		$('#CPM_KELURAHAN_OP').val(kel);
		
		
	}
	
	$('#CPM_MASA_PAJAK, #CPM_TAHUN_PAJAK').change(function() {
		//return false;
        if ($('#CPM_TIPE_PAJAK').val() === 2) return false;
        var bln = $('#CPM_MASA_PAJAK').val();
        if (bln == "") {
            $('#CPM_MASA_PAJAK1').val('');
            $('#CPM_MASA_PAJAK2').val('');
            return false;
        }
        var thn = $('#CPM_TAHUN_PAJAK').val();

        var tgl = new Date(thn, bln, 0).getDate();
        bln = (eval(bln) < 10) ? '0' + bln : bln;

        $('#CPM_MASA_PAJAK1').val('01/' + bln + '/' + thn);
        $('#CPM_MASA_PAJAK2').val(tgl + '/' + bln + '/' + thn);
        
        if(typeof function_sum === "function"){
			function_sum(this);
		}
        
    });
});

function formatRepo(repo) {
	if (repo.loading) return repo.text;
	var markup = "<div class='select2-result-repository clearfix'>" +
	"<div class='select2-result-repository__meta'>" +
	  "<div class='select2-result-repository__title'>" + repo.id + "</div>";
	markup += (repo.text)? "<div class='select2-result-repository__description'>" + repo.text + "</div>" : "";
	markup += "</div></div>";
  return markup;
}

function searchWP(){
	$('#loading').html('<img src="image/icon/loading.gif">');
	var npwpd = clear_npwpd($("#CPM_NPWPD").val());
	var param = $('#param').val();
	
	var str = Base64.decode(param);
	str += '&npwpd='+npwpd;
	
	var new_param = Base64.encode(str);
	window.location.href = 'main.php?param='+new_param;
}

function clear_npwpd(npwpd){
	npwpd = npwpd.replace(/\.|-/g,'');
	console.log(npwpd);
	return npwpd;
}

function addOp(){
	$('#btn-addOp').html('<img src="image/icon/loading.gif">');
	var npwpd = clear_npwpd($("#CPM_NPWPD").val());
	var nop = '';
	var param = $('#param').val();
	
	var str = Base64.decode(param);
	str += '&npwpd='+npwpd+'&nop='+nop;
	
	var new_param = Base64.encode(str);
	window.location.href = 'main.php?param='+new_param+'#CPM_TELEPON_WP';
}

function selectOP(){
	$('#btn-addOp').html('<img src="image/icon/loading.gif">');
	var npwpd = clear_npwpd($("#CPM_NPWPD").val());
	var nop = $("#CPM_NOP").val();
	var param = $('#param').val();
	
	var str = Base64.decode(param);
	
	str += '&npwpd='+npwpd+'&nop='+nop;
	
	var new_param = Base64.encode(str);
	// console.log(new_param);
	window.location.href = 'main.php?param='+new_param+'#CPM_TELEPON_WP';
}
// function get_kegiatan(){
// 	$('#btn-addOp').html('<img src="image/icon/loading.gif">');
// 	var npwpd = clear_npwpd($("#CPM_NPWPD").val());
// 	var nop = clear_npwpd($("#CPM_NOP").val());
// 	var param = $('#param').val();
	
// 	var str = Base64.decode(param);
	
// 	str += '&npwpd='+npwpd+'&nop='+nop;
	
// 	var new_param = Base64.encode(str);
// 	// console.log(new_param);
// 	window.location.href = 'main.php?param='+new_param+'#CPM_TELEPON_WP';
// }

/* function get_persen_terlambat_lapor(){
	
	if($('#CPM_TIPE_PAJAK').val() === 2){ 
		//jika non reguler maka tidak kena sanksi
		return 0;
	}
	
	if($('#CPM_MASA_PAJAK1').length>0){
		var bln = $('#CPM_MASA_PAJAK1').val();
		var thn = $('#CPM_MASA_PAJAK1').val();
	}else{
		//reklame
		return 0;
	}
	
	//js bulan index dari 0
	bln = eval(bln.substr(3, 2));
	thn = thn.substr(6, 4);
	
	var today, someday;
	var persen = 0;
	today = new Date();
	someday = new Date();
	someday.setFullYear(thn, bln, 16);
	
	if (someday < today) {
	   persen = $('#persen_terlambat_lap').val();
	}
	
	return persen;
} */

function get_persen_terlambat_lapor(){

	var jns_pajak = $('#type_terlambat_lap').val();
	var type_pajak = $('#CPM_TIPE_PAJAK').val();
	var today = new Date();
	today.setHours(0,0,0,0);

	if (jns_pajak == 7 || jns_pajak == 1) {
		if($('#CPM_ATR_BATAS_AKHIR').length>0){
			var tgl = $("#CPM_ATR_BATAS_AKHIR").datepicker().val().split('/');
			var jatemp = new Date(tgl[2]+'-'+tgl[1]+'-'+tgl[0]);
			jatemp.setHours(0,0,0,0);

			/**
			 == jatuh tempo ==
			 self: +20 hari
			 official: +1 bulan
			 **/
			if(type_pajak==1){
				jatemp.setDate(jatemp.getDay()+20);
			}else if(type_pajak==2){
				jatemp.setMonth(jatemp.getMonth()+1);
			}
		}else{
			return 0;
		}
	
		var persen = 0;
		if(jatemp.getTime() < today.getTime()){
		   persen = $('#persen_terlambat_lap').val();
		}
	}else{ 
		// JIKA JENIS PAJAK SELAIN REKLAME
		if($('#CPM_TIPE_PAJAK').val() === 2){ 
			//jika non reguler maka tidak kena sanksi
			return 0;
		}
		
		if($('#CPM_MASA_PAJAK1').length>0){
			var bln = $('#CPM_MASA_PAJAK1').val();
			var thn = $('#CPM_MASA_PAJAK1').val();
			var hri = $('#CPM_MASA_PAJAK1').val();
		}else{
			//reklame
			return 0;
		}
	
		//js bulan index dari 0
		bln = eval(bln.substr(3, 2));
		thn = thn.substr(6, 4);
		hri = eval(hri.substr(0, 2));
		hri += 1;
		hri = ""+hri;
		hri = hri.padStart(2, '0');
		
		var today, someday;
		var persen = 0;
		today = new Date();
		someday = new Date();
		// console.log("someday = "+someday);
		someday.setFullYear(thn, bln, 16);

		if (someday < today) {
		   persen = $('#persen_terlambat_lap').val();
		}

	}
	
	return persen;
}