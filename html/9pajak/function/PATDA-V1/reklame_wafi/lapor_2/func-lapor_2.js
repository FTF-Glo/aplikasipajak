$body = $("body");
$(document).on({
    ajaxStart: function() {
        $body.addClass("loading");
    },
    ajaxStop: function() {
        $body.removeClass("loading");
    }
});

$(document).ready(function(){
	$("#CPM_CEK_PIHAK_KETIGA").click(function() {
    	var x = document.getElementById("CPM_CEK_PIHAK_KETIGA").checked;
    	calculation();
		$('#CPM_NILAI_PIHAK_KETIGA').val('');
    	if ($('#CPM_CEK_PIHAK_KETIGA').prop('checked')) {
			$('#CPM_NILAI_PIHAK_KETIGA').removeAttr('readonly');
			$('#CPM_ATR_KAWASAN').prop('selectedIndex',0);
			$('#CPM_ATR_KAWASAN').attr('disabled','true');
			$('#CPM_ATR_PANJANG').attr('readonly','readonly');
			$('#CPM_ATR_LEBAR').attr('readonly','readonly');
			$('#CPM_ATR_MUKA').attr('readonly','readonly');
			$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
    	}else{
			$('#CPM_NILAI_PIHAK_KETIGA').attr('readonly','readonly');
			$('#CPM_ATR_KAWASAN').removeAttr('disabled');
			$('#CPM_ATR_PANJANG').removeAttr('readonly');
			$('#CPM_ATR_LEBAR').removeAttr('readonly');
			$('#CPM_ATR_JUMLAH').removeAttr('readonly');
			$('#CPM_ATR_MUKA').removeAttr('readonly');
			/*
    		var kdrek = $("select#CPM_ATR_REKENING").val();
			var data = $('select#CPM_ATR_REKENING option[value="'+kdrek+'"]').data();

			if (kdrek === '4.1.1.4.01.3' || //Megatron/Videotron/LED
				kdrek === '4.1.1.4.01.1' || //Billboard/Papan Nama/Neon Box
				kdrek === '4.1.1.4.01.1.1' || //Billboard/Papan Nama/Neon Box - Produk Bersinar
				kdrek === '4.1.1.4.01.1.2' || //Billboard/Papan Nama/Neon Box - Produk Tak Bersinar
				kdrek === '4.1.1.4.01.2.1' || //Billboard/Papan Nama/Neon Box - Non Produk Bersinar
				kdrek === '4.1.1.4.01.2.2' || //Billboard/Papan Nama/Neon Box - Non Produk Tidak Bersinar
				kdrek === '4.1.1.4.01.4' || //Baliho
				kdrek === '4.1.1.4.02.1' || //Kain/Spanduk/Umbul umbul/Banner
				kdrek === '4.1.1.4.10.1' //Reklame Perusahaan Lain Pada Dinding Bangunan Toko
			){
				alert('kondisi 1');
				$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
				$('#CPM_ATR_JUMLAH').val('');
				$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
				$('#CPM_ATR_KAWASAN').removeAttr('disabled');
				$('#CPM_ATR_TINGGI').val('');
				$('#CPM_ATR_LEBAR').val('');
				$('#CPM_ATR_MUKA').val('');
				$('#CPM_ATR_TINGGI').removeAttr('readonly');
				$('#CPM_ATR_LEBAR').removeAttr('readonly');
				$('#CPM_ATR_MUKA').removeAttr('readonly');
				// $('#CPM_ATR_KAWASAN').attr('disabled','false');

			} else if (kdrek === '4.1.1.4.06.1' ||//Reklame Udara
					   kdrek === '4.1.1.4.08.1' //Reklame Suara
			){
				alert('kondisi 2');
				$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
				$('#CPM_ATR_TINGGI').val('');
				$('#CPM_ATR_LEBAR').val('');
				$('#CPM_ATR_MUKA').val('');
				$('#CPM_ATR_TINGGI').attr('readonly','readonly');
				$('#CPM_ATR_LEBAR').attr('readonly','readonly');
				$('#CPM_ATR_MUKA').attr('readonly','readonly');
				$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
	    		$('#CPM_ATR_KAWASAN').prop('selectedIndex',0);
				$('#CPM_ATR_KAWASAN').attr('disabled','true');
			} else if (kdrek === '4.1.1.4.04.1' ||//Reklame Selebaran/Brosur/Leaflet
					   kdrek === '4.1.1.4.05.1' ||//Reklame Berjalan Termasuk Kendaraan
					   kdrek === '4.1.1.4.09.1' ||//Reklame Film/Slide
					   kdrek === '4.1.1.4.11.1' //Reklame Peragaan
			){
				alert('kondisi 3');
				$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
				$('#CPM_ATR_TINGGI').val('');
				$('#CPM_ATR_LEBAR').val('');
				$('#CPM_ATR_MUKA').val('');
				$('#CPM_ATR_TINGGI').attr('readonly','readonly');
				$('#CPM_ATR_LEBAR').attr('readonly','readonly');
				$('#CPM_ATR_MUKA').attr('readonly','readonly');
	    		$('#CPM_ATR_KAWASAN').prop('selectedIndex',0);
				$('#CPM_ATR_KAWASAN').attr('disabled','true');
				$('#CPM_ATR_JUMLAH').val('');
				$('#CPM_ATR_JUMLAH').removeAttr('readonly');
			}

			nmrek = data.nmrek;
			$("#nama-rekening").html(nmrek);
			$('#CPM_NILAI_PIHAK_KETIGA').attr('readonly','readonly');
			*/
    	}
    });
	$("#CPM_NILAI_PIHAK_KETIGA").change(function() {
    	calculation();
	});
});

function hitung_masa() {
	var startdate = $('#CPM_ATR_BATAS_AWAL').val();
	var enddate = $('#CPM_ATR_BATAS_AKHIR').val();

	if(startdate == "" || enddate == "") return false;
	$.ajax({
		type: "POST",
		data: {startdate:startdate,enddate:enddate,'function':'hitung_masa'},
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		dataType:'json',
		async :false,
		success: function(res) {
			waktu = [res.tahun, res.semester,res.triwulan, res.bulan, res.minggu, res.hari];

			$('#CPM_ATR_JUMLAH_TAHUN').val(res.tahun);
			$('#CPM_ATR_JUMLAH_BULAN').val(res.bulan);
			$('#CPM_ATR_JUMLAH_MINGGU').val(res.minggu);
			$('#CPM_ATR_JUMLAH_HARI').val(res.hari);
			setJangkaWaktu();
		}
	});
	return true;
}

function setJangkaWaktu(){
	var durasi = $('#CPM_MASA_PAJAK').val();
	var html = "<span>"+durasi+" "+$('#CPM_ATR_TYPE_MASA option:selected').text()+"</span>";
	$("#jangka-waktu").html(html);
}

function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function load_first(){
	var kdrek = $('select#CPM_ATR_REKENING').val();
	var data = $('select#CPM_ATR_REKENING option[value="'+kdrek+'"]').data();

	if (kdrek === '4.1.1.4.01.3' || //Megatron/Videotron/LED
		kdrek === '4.1.1.4.01.1' || //Billboard/Papan Nama/Neon Box - Bersinar
		kdrek === '4.1.1.4.01.2' || //Billboard/Papan Nama/Neon Box - Tidak Bersinar
		kdrek === '4.1.1.4.01.4' || //Baliho
		kdrek === '4.1.1.4.02.1' || //Kain/Spanduk/Umbul umbul/Banner
		kdrek === '4.1.1.4.10.1' //Reklame Perusahaan Lain Pada Dinding Bangunan Toko
	){
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif);
		// $('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		// $('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
		// $('#CPM_ATR_KAWASAN').attr('disabled','false');

	} else if (kdrek === '4.1.1.4.06.1' ||//Reklame Udara
			   kdrek === '4.1.1.4.08.1' //Reklame Suara
	){
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif);
		// $('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		// $('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_TINGGI').attr('readonly','readonly');
		$('#CPM_ATR_LEBAR').attr('readonly','readonly');
		$('#CPM_ATR_MUKA').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
	} else if (kdrek === '4.1.1.4.04.1' ||//Reklame Selebaran/Brosur/Leaflet
			   kdrek === '4.1.1.4.05.1' ||//Reklame Berjalan Termasuk Kendaraan
			   kdrek === '4.1.1.4.09.1' ||//Reklame Film/Slide
			   kdrek === '4.1.1.4.11.1' //Reklame Peragaan
	){
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
		// $('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		// $('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_PANJANG').attr('readonly','readonly');
		$('#CPM_ATR_LEBAR').attr('readonly','readonly');
		$('#CPM_ATR_MUKA').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').removeAttr('readonly');
	}

	nmrek = data.nmrek;
	$("#nama-rekening").html(nmrek);
}

function calculation(){
	$("#harga-dasar").autoNumeric('init');

	if($.trim($('#jangka-waktu').html()) === '') alert('Silakan isi masa pajak');

	if($('#CPM_NO').length) hitung_masa();
	// get_hargadasar();
	hitung_total();
}


function get_hargadasar(){
	var kdrek = $('#CPM_ATR_REKENING').val();
	var data = $('select#CPM_ATR_REKENING option[value="'+kdrek+'"]').data();

	var params= {
		'kdrek': kdrek,
		'panjang': $('#CPM_ATR_PANJANG').val(),
		'lebar' : $('#CPM_ATR_LEBAR').val(),
		'muka' : $('#CPM_ATR_MUKA').val(),
		'sisi' : Number($('#CPM_ATR_SISI').val()),
		'biaya' : $('#CPM_ATR_BIAYA').val(),
		// 'harga_dasar_uk' : $('#CPM_ATR_HARGA_DASAR_UK').val(),
		// 'harga_dasar_tin' : $('#CPM_ATR_HARGA_DASAR_TIN').val(),
		'tarif' : data.tarif,
		'kawasan' : $('#CPM_ATR_KAWASAN').val(),
		'jalan' : $('#CPM_ATR_JALAN').val(),
		'sudut_pandang' : $('#CPM_ATR_SUDUT_PANDANG').val(),
		'durasi' : $('#CPM_MASA_PAJAK').val(),
		'durasi_label' : $('#CPM_ATR_TYPE_MASA option:selected').text(),
		'jumlah' : $('#CPM_ATR_JUMLAH').val(),
		'x' : document.getElementById("CPM_CEK_PIHAK_KETIGA").checked,
		'npk' : $("#CPM_NILAI_PIHAK_KETIGA").val(),
		'function' : 'get_hargadasar'
	};

	if((params.panjang == "" || params.lebar == "" || params.muka == "" || params.kawasan == "" || params.jumlah == "") == true) return false;

	$.ajax({
		url: 'function/PATDA-V1/reklame/lapor/svc-lapor.php',
		type: 'POST',
		dataType: 'json',
		data: params,
		async:false,
		success: function(res){

			$('#area_perhitungan').html(res.html);
			$("#CPM_ATR_TARIF").val(res.tarif);

			var tarif = res.tarif;
			var omzet = res.total;
			var kurangLebih = 0;
			var masa_pajak_akhir = $('#CPM_ATR_BATAS_AKHIR').val();

		    var tahun = masa_pajak_akhir.substring(6, 10);
		    var date = new Date(),
		    bulan_sekarang = date.getMonth()+1

			var selisih_bulan = selisihBulan(masa_pajak_akhir);
      //tambahan
			if(selisih_bulan > 0){
				if($('#editable_terlambat_lap').val() == 1){
          var sanksi = omzet * (selisih_bulan) * 2 / 100;
					var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
				}else{
					var sanksi = omzet * (selisih_bulan) * 2 / 100;
				}
			}else{
				var sanksi = 0;
			}
			$('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set',sanksi);


			var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);

			$('input#CPM_DISCOUNT').autoNumeric('init',{vMax: '100'});
			$('#CPM_TOTAL_OMZET').autoNumeric('init');
			$('#CPM_TOTAL_PAJAK').autoNumeric('init');
			$('#CPM_ATR_TOTAL').autoNumeric('init');

			var persen_pengurangan = Number($('#CPM_DISCOUNT').autoNumeric('get'));

			total = eval(total) - (eval(total) * eval(persen_pengurangan)/ 100);
			total = Math.round(total);

			$('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set',sanksi);
			$("#CPM_TOTAL_OMZET").autoNumeric('set',omzet);
			$("#CPM_TOTAL_PAJAK").autoNumeric('set',total);

			$("#CPM_TARIF_PAJAK").val(params.tarif);
			$("#CPM_ATR_TARIF").val(params.tarif);
			$("#CPM_MASA_PAJAK").val(params.durasi);
			$("#CPM_ATR_TOTAL").autoNumeric('set',total);
			$("#CPM_JNS_MASA_PAJAK").val(params.durasi_label);

			if (res.total !== 0)
				$("#terbilang").html(terbilang(Math.round(total)) + " Rupiah");
		}
	});


}

function hitung_denda(masa_pajak, tagihan) {
	if(masa_pajak == "" || tagihan == "" || tagihan == 0) return false;
	$.ajax({
		type: "POST",
		data: {masa_pajak:masa_pajak, tagihan:tagihan,'function':'hitung_denda'},
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		dataType:'json',
		async :false,
		success: function(res) {
			console.log(res)
		}
	});
}


function rekDetail(no){
	var rek = $("#CPM_ATR_REKENING-"+no+" option:selected"),
		data = rek.data();
	$("#nama-rekening-"+no).html(data.nmrek);
	$("#CPM_ATR_BIAYA-"+no).val(data.tarif);
}

function hitungDetail(no){
	var rek = $('#CPM_ATR_REKENING-'+no),
		data = rek.find('option:selected').data();

	var params= {
		'kdrek': rek.val(),
		'panjang': $('#CPM_ATR_PANJANG-'+no).val(),
		'lebar' : $('#CPM_ATR_LEBAR-'+no).val(),
		'muka' : $('#CPM_ATR_MUKA-'+no).val(),
		// 'sisi' : Number($('#CPM_ATR_SISI-'+no).val()),
		'sudut_pandang' : $('#CPM_ATR_SUDUT_PANDANG-'+no).val(),
		'biaya' : $('#CPM_ATR_BIAYA-'+no).val(),
		'tarif' : data.tarif,
		'kawasan' : $('#CPM_ATR_KAWASAN-'+no).val(),
		// 'jalan' : $('#CPM_ATR_JALAN-'+no).val(),
		'durasi' : $('#CPM_MASA_PAJAK').val(),
		'durasi_label' : $('#CPM_ATR_TYPE_MASA option:selected').text(),
		'jumlah' : $('#CPM_ATR_JUMLAH-'+no).val(),
		'x' : document.getElementById("CPM_CEK_PIHAK_KETIGA").checked,
		'npk' : $("#CPM_NILAI_PIHAK_KETIGA").val(),
		'function' : 'get_hargadasar'
	};

	if((params.panjang == "" || params.lebar == "" || params.muka == "" || params.kawasan == "" || params.jumlah == "") == true) return false;

	$.ajax({
		url: 'function/PATDA-V1/reklame/lapor/svc-lapor.php',
		type: 'POST',
		dataType: 'json',
		data: params,
		async:false,
		success: function(res){
			$("#CPM_ATR_TARIF-"+no).val(res.tarif);
			$("#area_perhitungan-"+no).html(res.html);
			$("#CPM_ATR_TOTAL-"+no).autoNumeric('init');
			$("#CPM_ATR_TOTAL-"+no).autoNumeric('set', res.total);

			hitung_total();
		}
	});
}

function hapusDetail(no){
	var tanya = confirm('Anda yakin ingin menghapus reklame ini?');
	var id = $("#CPM_ATR_ID-"+no).val();
	var t;
	if(tanya){
		$("#btn-hapus-"+no).html("Menghapus...");
		if(id==""){
			$("#CPM_ATR_TOTAL-"+no).autoNumeric('init');
			$("#CPM_ATR_TOTAL-"+no).autoNumeric('set', 0);
			$("#atr_rek-"+no).hide();
			hitung_total();
		}else{
			$.ajax({
				url: 'function/PATDA-V1/reklame/lapor/svc-lapor.php',
				type: 'POST',
				dataType: 'json',
				data: {atr:id, no:no, function:'delRow'},
				async:false,
				success: function(res){
					alert(res.pesan);
					if(res.status==1){
						$("#CPM_ATR_TOTAL-"+no).val('0');
						$("#atr_rek-"+no).remove();
					}else{
						$("#btn-hapus-"+no).html("Hapus");
					}
					hitung_total();
				}
			});
		}

	}
}

function hitung_total(){
	var tarif = parseFloat($("#CPM_ATR_BIAYA").val());
	var omzet = parseFloat($("#CPM_ATR_TOTAL").autoNumeric('get'));
	var kurangLebih = 0;
	var count = parseInt($("#count").val());
	var t;
	if(count==1){
		get_hargadasar();
		return false;
	}else if(count>1){
		for(var i=2; i<=count; i++){
			if($("#CPM_ATR_TOTAL-"+i).length){
				$("#CPM_ATR_TOTAL-"+i).autoNumeric('init');
				t = $("#CPM_ATR_TOTAL-"+i).autoNumeric('get');
				omzet += parseFloat(t);
			}
		}
	}
			var kurangLebih = 0;
			var masa_pajak_akhir = $('#CPM_ATR_BATAS_AKHIR').val();

		    var tahun = masa_pajak_akhir.substring(6, 10);
		    var date = new Date(),
		    bulan_sekarang = date.getMonth()+1

			var selisih_bulan = selisihBulan(masa_pajak_akhir);
			// if(bulan)
			if(selisih_bulan > 1){
			// alert(selisih_bulan);

				if($('#editable_terlambat_lap').val() == 1){
					var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
				}else{
					var sanksi = omzet * (selisih_bulan-2) * 2 / 100;
					$('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set',sanksi);
				}
			}else{
				var sanksi = 0;
			}

			// alert(sanksi);

			var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);
			console.log(sanksi);


	$('input#CPM_DISCOUNT').autoNumeric('init',{vMax: '100'});
	$('#CPM_TOTAL_OMZET').autoNumeric('init');
	$('#CPM_TOTAL_PAJAK').autoNumeric('init');

	var persen_pengurangan = Number($('#CPM_DISCOUNT').autoNumeric('get'));


	total = eval(total) - (eval(total) * eval(persen_pengurangan)/ 100);
	total = Math.round(total);

	$('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set',sanksi);
	$("#CPM_TOTAL_OMZET").autoNumeric('set',omzet);
	$("#CPM_TOTAL_PAJAK").autoNumeric('set',total);

	if (total !== 0)
		$("#terbilang").html(terbilang(Math.round(total)) + " Rupiah");
}

function selisihBulan(awal_bulan){

    var bulan = awal_bulan.substring(3, 5);
    var tahun = awal_bulan.substring(6, 10);

    var date = new Date(),
    bulan_sekarang = date.getMonth()+1
    ,tahun_sekarang = date.getFullYear();

    var hasil = (bulan_sekarang + (12 * (tahun_sekarang-tahun))+1)-bulan;
    return hasil;
}

$(function() {
    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
		changeMonth: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "...",
        onSelect: function(dateText) {
			$("#CPM_ATR_TYPE_MASA").trigger('change');
            if(hitung_masa()) get_hargadasar();
        },
		onClose: function(dateText,datePickerInstance) {
			$("#CPM_ATR_TYPE_MASA").trigger('change');
		}
    });

    $('input:reset').click(function(){
		$('select#CPM_NPWPD').html('').trigger('change');
	});

	$("select.CPM_NOP").select2({
        escapeMarkup: function (markup) {
            var fd = markup.split(' | ');
            if(fd[1]){
                fd[0] = fd[0].split(' - ');
                return '<b>['+fd[0][0]+']</b> ['+fd[0][1]+']  '+fd[1];
            }else{
                return markup;
            }
        }
    });

    $('input.format').autoNumeric('init');
    $('input#CPM_ATR_JUMLAH').autoNumeric('init');
    $('input#CPM_ATR_TOTAL').autoNumeric('init');
    $('input#CPM_ATR_PANJANG').autoNumeric('init');
    $('input#CPM_ATR_LEBAR').autoNumeric('init');
	// $('input#CPM_ATR_MUKA').autoNumeric('init');
	// $('input#CPM_ATR_SISI').autoNumeric('init', {vMin:1, mDec:0});
    $('input#CPM_ATR_BIAYA').autoNumeric('init');
    // $('input#CPM_ATR_HARGA_DASAR_UK').autoNumeric('init');
    // $('input#CPM_ATR_HARGA_DASAR_TIN').autoNumeric('init');

    $('input#CPM_TOTAL_OMZET').autoNumeric('init');
    $('input#CPM_DENDA_TERLAMBAT').autoNumeric('init');
    $('input#CPM_DISCOUNT').autoNumeric('init',{vMax: '100'});
    $('input#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('input#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');

    var form = $("#form-lapor");
    form.validate({
        rules: {
            "PAJAK[CPM_NO]": "required",
            "PAJAK[CPM_NPWPD]": "required",
            "PAJAK[CPM_NAMA_WP]": "required",
            "PAJAK[CPM_ALAMAT_WP]": "required",
            "PAJAK[CPM_NOP]": "required",
            "PAJAK[CPM_NAMA_OP]": "required",
            "PAJAK[CPM_ALAMAT_OP]": "required",
            "PAJAK[CPM_TOTAL_OMZET]": "required",
            "PAJAK_ATR[CPM_ATR_JUDUL][]" : "required",
            "PAJAK_ATR[CPM_ATR_LOKASI][]" : "required",
        },
        messages: {
            "PAJAK[CPM_NO]": " harus diisi",
            "PAJAK[CPM_NPWPD]": " harus diisi",
            "PAJAK[CPM_NAMA_WP]": " harus diisi",
            "PAJAK[CPM_ALAMAT_WP]": " harus diisi",
            "PAJAK[CPM_NOP]": " harus diisi",
            "PAJAK[CPM_NAMA_OP]": " harus diisi",
            "PAJAK[CPM_ALAMAT_OP]": " harus diisi",
            "PAJAK[CPM_TOTAL_OMZET]": " harus diisi",
            "PAJAK_ATR[CPM_ATR_JUDUL][]" : "harus diisi",
            "PAJAK_ATR[CPM_ATR_LOKASI][]" : "harus diisi",
        }

    });

    $('#CPM_TRAN_INFO').removeClass('required');
    $('input.AUTHORITY').change(function() {
        if ($(this).val() === 1) {
            $('#CPM_TRAN_INFO').removeClass('required');
            $('#CPM_TRAN_INFO').attr('readonly', 'readonly');
            $('#CPM_TRAN_INFO').val('');
        } else {
            $('#CPM_TRAN_INFO').addClass('required');
            $('#CPM_TRAN_INFO').removeAttr('readonly');
        }
    })

    $("input.btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);

        if (action === "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan laporan ini?");
            }
        } else if (action === "save_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan dan memfinalkan laporan ini?");
            }
        } else if (action === "update_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk memperbaharui dan memfinalkan laporan ini?");
            }
        } else if (action === "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk merubah laporan ini?");
            }
        } else if (action === "delete") {
            res = confirm("Apakah anda yakin untuk menghapus laporan ini?");
        } else if (action === "verifikasi" || action === "persetujuan") {
            res = confirm("Apakah anda yakin untuk menyetujui / menolak laporan ini?");
        } else if (action === "new_version") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk membuat versi baru laporan ini?");
            }
        } else if (action === "new_version_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk membuat versi baru dan memfinalkan laporan ini?");
            }
        }
        if (res) {
            document.getElementById("form-lapor").submit();
        }
    });

    $("input.btn-print").click(function() {
        var action = $(this).attr('action');
        $('#function').val(action);
        $("#form-lapor").attr('target', '_blank');
        document.getElementById("form-lapor").submit();
    });

	$(".btn-tambah").click(function() {
		var no = parseInt($("#count").val()),
		    npwpd = $("#CPM_NPWPD").val(),
			type_masa = $("#CPM_ATR_TYPE_MASA").val(),
			waktu = $("#CPM_MASA_PAJAK").val()+" "+$("#CPM_ATR_TYPE_MASA option:selected").text(),
			awal = $("#CPM_ATR_BATAS_AWAL").val(),
			akhir = $("#CPM_ATR_BATAS_AKHIR").val(),
			pajak = $("#CPM_ATR_BIAYA").val(),
			rek = $("#CPM_ATR_REKENING").val();
		if(awal=='' || akhir==''){
			alert('silahkan isi masa pajak!');
			return false;
		}else if(rek==''){
			alert('silahkan pilih rekening!');
			return false;
		}

        $.ajax({
			type: "POST",
			data: {no:no,npwpd:npwpd,type_masa:type_masa,waktu:waktu,tarif:pajak,'function':'addRow'},
			url: "view/PATDA-V1/reklame/svc-reklame.php",
			async :false,
			success: function(res) {
				$(".atr_reklame").append(res);
				$("#count").val(no+1);
				$('#CPM_ATR_PANJANG-'+(no+1)).autoNumeric('init');
				$('#CPM_ATR_LEBAR-'+(no+1)).autoNumeric('init');
				$('#CPM_ATR_JUMLAH-'+(no+1)).autoNumeric('init');
				$('#CPM_ATR_TOTAL-'+(no+1)).autoNumeric('init');
			}
		});
    });

    /* FUNGSI PADA INPUT DI PELAYANAN*/
    $("#CPM_NPWPD").keyup(function() {
        if ($(this).attr('readonly') === 'readonly')
            return false;
        $("#CPM_ID_PROFIL").val('');
        $("#CPM_NAMA_WP").val('');
        $("#CPM_ALAMAT_WP").val('');
        $("#CPM_NOP").val('');
        $("#CPM_NAMA_OP").val('');
        $("#CPM_ALAMAT_OP").val('');
    });


	$("#CPM_ATR_KAWASAN, #CPM_ATR_JALAN, #CPM_ATR_SUDUT_PANDANG").change(function(){
		calculation();
	});

	$("#CPM_ATR_TYPE_MASA").change(function(){
		$('#CPM_MASA_PAJAK').val(waktu[$(this).val()-1]);
		setJangkaWaktu();
		calculation();

	});

	$('#CPM_ATR_JUMLAH, #CPM_ATR_PANJANG, #CPM_ATR_LEBAR, #CPM_ATR_MUKA, #CPM_ATR_SISI, #CPM_ATR_TINGGI, #CPM_ATR_JARI, #CPM_DISCOUNT, #CPM_DENDA_TERLAMBAT_LAP, #CPM_ATR_JUMLAH').keyup(function() {
		if ($(this).attr('readonly') === 'readonly')
            return false;
		get_hargadasar();
		calculation();
    });

    $('#CPM_ATR_BIAYA').change(function(){
		if ($(this).attr('readonly') === 'readonly')
            return false;
        calculation();
	});

	// $('#CPM_ATR_BATAS_AKHIR').change(function(){
	// 	$('#CPM_ATR_REKENING').val('4.1.1.4.01.2');
	// 	$('#CPM_ATR_REKENING').trigger('change');
	// })

	$('select#CPM_ATR_REKENING').change(function(){
		var kdrek = $(this).val();
		var data = $('select#CPM_ATR_REKENING option[value="'+kdrek+'"]').data();
		// $('#CPM_ATR_TYPE_MASA option:not(:selected)').attr('disabled','disabled');
		// $('#CPM_ATR_BIAYA').attr('readonly','readonly');
		// $('#CPM_ATR_MUKA').val(1);
		// $('#CPM_ATR_MUKA').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').val(1);
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif);
		// $('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		// $('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);

		/* if(kdrek==='4.1.1.4.02.1'){
			$('#CPM_ATR_TYPE_MASA').val(4); //bulan
		}else{
			$('#CPM_ATR_TYPE_MASA').val(1); //tahun
		} */
		$('#CPM_ATR_TYPE_MASA').trigger('change');

		/* if (
			kdrek === '4.1.1.4.03.1' || //Reklame Melekat/Stiker
			kdrek === '4.1.1.4.04.1' //Reklame Selebaran/poster/leaflet
			) {
			$('#CPM_ATR_TINGGI').attr('placeholder','Panjang (cm)');
			$('#CPM_ATR_LEBAR').attr('placeholder','Lebar (cm)');
			$('#label_jumlah').html('Jumlah Lembar (Qty)');
		}else if(
			kdrek === '4.1.1.4.05.1' //Reklame Berjalan termasuk pada Kendaraan
			){
			$('#CPM_ATR_TINGGI').attr('placeholder','Panjang (m)');
			$('#CPM_ATR_LEBAR').attr('placeholder','Lebar (m)');
			$('#label_jumlah').html('Jumlah Unit (Qty)');
		}else if(
			kdrek === '4.1.1.4.09.1' //Reklame Film/slide
			){
			$('#CPM_ATR_TINGGI').attr('placeholder','Panjang (m)');
			$('#CPM_ATR_LEBAR').attr('placeholder','Lebar (m)');
			$('#label_jumlah').html('Jumlah Tayangan (Qty)');
		}else if(
			kdrek === '4.1.1.4.10.1' //Reklame Peragaan
			){
			$('#CPM_ATR_TINGGI').attr('placeholder','Panjang (m)');
			$('#CPM_ATR_LEBAR').attr('placeholder','Lebar (m)');
			$('#label_jumlah').html('Jumlah Peragaan (Qty)');
		}else{
			$('#CPM_ATR_TINGGI').attr('placeholder','Panjang (m)');
			$('#CPM_ATR_LEBAR').attr('placeholder','Lebar (m)');
			$('#label_jumlah').html('Jumlah (Qty)');
		} */

		// if(
		// 	kdrek === '4.1.1.4.03.1' || //Reklame Melekat/Stiker
		// 	kdrek === '4.1.1.4.04.1' || //Reklame Selebaran/poster/leaflet
		// 	kdrek === '4.1.1.4.06.1' || //Reklame Udara
		// 	kdrek === '4.1.1.4.07.1' // Reklame Apung
		// ){
		// 	$('#CPM_ATR_BIAYA').removeAttr('readonly');
		// 	$('#CPM_ATR_TINGGI').attr('readonly','readonly').autoNumeric('set',0);
		// 	$('#CPM_ATR_LEBAR').attr('readonly','readonly').autoNumeric('set',0);
		// 	$('#CPM_ATR_MUKA').attr('readonly','readonly').autoNumeric('set',0);
		// }else{
		// 	$('#CPM_ATR_BIAYA').attr('readonly','readonly').autoNumeric('set',data.harga);
		// 	$('#CPM_ATR_TINGGI').removeAttr('readonly');
		// 	$('#CPM_ATR_LEBAR').removeAttr('readonly');
		// 	$('#CPM_ATR_MUKA').removeAttr('readonly');
		// }

		// if(
		// 	kdrek === '4.1.1.4.02.1' || //Reklame kain /spanduk/umbul-umbul, tenda reklame, banner dan sejenisnya
		// 	kdrek === '4.1.1.4.06.1' || //Reklame Udara
		// 	kdrek === '4.1.1.4.07.1' // Reklame Apung
		// ){
		// 	$('#CPM_ATR_TYPE_MASA').val(6);//hari
		// 	$('#CPM_ATR_TYPE_MASA').trigger('change');
		// 	$('#CPM_ATR_TYPE_MASA option:not(:selected)').attr('disabled','disabled');
		// }else{
		// 	$('#CPM_ATR_TYPE_MASA option').removeAttr('disabled');
		// }



		nmrek = data.nmrek;
		$("#nama-rekening").html(nmrek);

		calculation();
	});


});
