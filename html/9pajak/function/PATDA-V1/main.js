$(function(){
	
	if(typeof $().select2 === 'function'){
		if ($('#CPM_NPWPD').attr('readonly') !== 'readonly'){ 
			
			$('#CPM_NPWPD').select2({
				placeholder: "Input NPWPD",
				allowClear: true,
				ajax: {
					url: 'function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php',
					type: 'POST',
					dataType: 'json',
					delay: 250,
					cache: true,
					data: function (params) {
						postData = {};
						postData.function = 'get_list_npwpd';
						
						var TBLJNSPJK = $('#TBLJNSPJK').val();
						if(TBLJNSPJK === ''){
							TBLJNSPJK = $('#CPM_JENIS_PAJAK option:selected').attr('data-table');
						}
						
						postData.TBLJNSPJK = TBLJNSPJK;
						postData.CPM_NPWPD = params.term;
						console.log(postData)
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
			});
		}
		
		if($('#TBLJNSPJK').val() !== ''){ //jika bukan skpdkb
			$('#CPM_KECAMATAN_OP').select2({placeholder: "KECAMATAN"});
			$('#CPM_KELURAHAN_OP').select2({placeholder: "KELURAHAN"});
			
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
		}
	}
	
	
    $('#CPM_MASA_PAJAK, #CPM_TAHUN_PAJAK').change(function() {
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
	var npwpd = $("#CPM_NPWPD").val();
	if(npwpd == null || npwpd.length == 0) return false;
	postData = {};
	postData.function = 'getWP';
	postData.CPM_NPWPD = npwpd;
	
	var TBLJNSPJK = $('#TBLJNSPJK').val();
	if(TBLJNSPJK === ''){
		TBLJNSPJK = $('#CPM_JENIS_PAJAK option:selected').attr('data-table');
	}
	postData.TBLJNSPJK = TBLJNSPJK;
	
	console.log(postData);
	
	/*jika readonly maka opsi didisable*/
	$.ajax({
		url: 'function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php',
		type: 'POST',
		dataType: 'json',
		data: postData,
		success:function(res){
			$('#loading').html('');
			$('#btn-submit').attr('disabled','disabled');
			if($('#CPM_ID_PROFIL').length) $('#CPM_ID_PROFIL').val(res.CPM_ID);
			else $("#CPM_ID").val(res.CPM_ID);
			
			$('#CPM_NAMA_WP').val(res.CPM_NAMA_WP);
			$('#CPM_ALAMAT_WP').val(res.CPM_ALAMAT_WP);
			$('#CPM_NOP').val(res.CPM_NOP);
			$('#CPM_NAMA_OP').val(res.CPM_NAMA_OP);
			$('#CPM_ALAMAT_OP').val(res.CPM_ALAMAT_OP);
			$('#CPM_KECAMATAN_WP').val(res.CPM_KECAMATAN_WP);
			$('#CPM_KELURAHAN_WP').val(res.CPM_KELURAHAN_WP);
			$('#CPM_KECAMATAN_OP').val(res.CPM_KECAMATAN_OP);
			
			if($('#CPM_KECAMATAN_OP').val()!==''){
				$('#CPM_KECAMATAN_OP').trigger('change');
			}
			$('#CPM_KELURAHAN_OP').val(res.CPM_KELURAHAN_OP);
			
			$('#KELURAHAN_OP').val(res.CPM_KELURAHAN_OP);
			$('#KECAMATAN_OP').val(res.CPM_KECAMATAN_OP);
			$('#CPM_KEL_NAMA_OP').val(res.CPM_KEL_NAMA_OP);
			$('#CPM_KEC_NAMA_OP').val(res.CPM_KEC_NAMA_OP);
			
			$(".CPM_GOL option[value='" + res.CPM_GOL + "']").prop('selected', true).removeAttr('disabled');
			
			if($('#CPM_TARIF_PAJAK').length) $('#CPM_TARIF_PAJAK').val(res.CPM_TARIF);
			if($('#CPM_HARGA_DASAR').length) $('#CPM_HARGA_DASAR').val(res.CPM_HARGA); //penerangan jalan, walet
			if($('#CPM_HARGA').length) $('#CPM_HARGA').val(res.CPM_HARGA); //air bawah tanah
			
			if($('input#CPM_TRUCK_ID').length){ //pelaporan
				$('#CPM_TRUCK_ID').val(res.CPM_TRUCK_ID); //mineral
				function_getval_tracking();
			}
			
			if($('select#CPM_TRUCK_ID').length){ //profil
				var truck_id = base64_decode(res.CPM_TRUCK_ID).split(";");
				for(var x in truck_id){
					$('select#CPM_TRUCK_ID option[value="'+truck_id[x]+'"]').prop('selected',true);
				}
				$('select#CPM_TRUCK_ID').trigger('change');
			}
			
			if (typeof function_sum === "function") { 
				function_sum()
			}

		},
		complete: function(){
		}
	});
}

function base64_decode(str) {
    return decodeURIComponent(atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
}

function get_persen_terlambat_lapor(){
	
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
}


