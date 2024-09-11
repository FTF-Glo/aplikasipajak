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
	
	var nmrek = $('#CPM_ATR_REKENING').val();
	
	if(nmrek === "4.1.01.09.01.004" || nmrek === "" ){
		$('.ID_JAM').show();
	}else{
		$('.ID_JAM').hide();
	}
		
	$("#CPM_CEK_PIHAK_KETIGA").click(function() {
    	var x = document.getElementById("CPM_CEK_PIHAK_KETIGA").checked;
    	calculation();
		$('#CPM_NILAI_PIHAK_KETIGA').val('');
    	if ($('#CPM_CEK_PIHAK_KETIGA').prop('checked')) {			
			$('#CPM_NILAI_PIHAK_KETIGA').removeAttr('readonly');
			$('#CPM_ATR_KAWASAN').prop('selectedIndex',0);
			$('#CPM_ATR_KAWASAN').attr('disabled','true');
			$('#CPM_ATR_TINGGI').attr('readonly','readonly');
			$('#CPM_ATR_LEBAR').attr('readonly','readonly');
			$('#CPM_ATR_PANJANG').attr('readonly','readonly');
			$('#CPM_ATR_JUMLAH').attr('readonly','readonly');				
    	}else{
			$('#CPM_NILAI_PIHAK_KETIGA').attr('readonly','readonly');
			$('#CPM_ATR_KAWASAN').removeAttr('disabled');
			$('#CPM_ATR_TINGGI').removeAttr('readonly');
			$('#CPM_ATR_LEBAR').removeAttr('readonly');
			$('#CPM_ATR_JUMLAH').removeAttr('readonly');
			$('#CPM_ATR_PANJANG').removeAttr('readonly');
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
    })
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
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
		$('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		$('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_JUMLAH').attr('readonly','readonly');

		// $('#CPM_ATR_KAWASAN').attr('disabled','false');

	} else if (kdrek === '4.1.1.4.06.1' ||//Reklame Udara
			   kdrek === '4.1.1.4.08.1' //Reklame Suara
	){
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
		$('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		$('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_TINGGI').attr('readonly','readonly');
		$('#CPM_ATR_LEBAR').attr('readonly','readonly');
		$('#CPM_ATR_PANJANG').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').attr('readonly','readonly');
	} else if (kdrek === '4.1.1.4.04.1' ||//Reklame Selebaran/Brosur/Leaflet
			   kdrek === '4.1.1.4.05.1' ||//Reklame Berjalan Termasuk Kendaraan
			   kdrek === '4.1.1.4.09.1' ||//Reklame Film/Slide
			   kdrek === '4.1.1.4.11.1' //Reklame Peragaan
	){
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif+"%");
		$('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		$('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		$('#CPM_ATR_TINGGI').attr('readonly','readonly');
		$('#CPM_ATR_LEBAR').attr('readonly','readonly');
		$('#CPM_ATR_PANJANG').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').removeAttr('readonly');
	}
	
	
	
	nmrek = data.nmrek;
	$("#nama-rekening").html(nmrek);
}

function calculation(){
	if($('#CPM_NO').length) hitung_masa();
	
	if($('#CPM_ATR_BATAS_AWAL').val() === '' || $('#CPM_ATR_BATAS_AKHIR').val() === '') alert('Silakan isi masa pajak');
	
	get_hargadasar();
}


function get_hargadasar(){
    var total = Number($(CPM_TOTAL_PAJAK).autoNumeric('get'));
    total = Math.ceil(total);

    $.ajax({
        type: "POST",
        data: "num=" + total,
        url: "function/PATDA-V1/svc-terbilang.php",
        success: function (res) {
			$("#terbilang").html(res);
        }
    })
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
	
    $('input.format').autoNumeric('init');
    $('input#CPM_ATR_JUMLAH').autoNumeric('init');
    $('input#CPM_ATR_TOTAL').autoNumeric('init');
    $('input#CPM_ATR_LEBAR').autoNumeric('init');
    $('input#CPM_ATR_TINGGI').autoNumeric('init');
	$('input#CPM_ATR_PANJANG').autoNumeric('init');
	$('input#CPM_ATR_SISI').autoNumeric('init', {vMin:1, mDec:0});
    $('input#CPM_ATR_BIAYA').autoNumeric('init');
    $('input#CPM_ATR_HARGA_DASAR_UK').autoNumeric('init');
    $('input#CPM_ATR_HARGA_DASAR_TIN').autoNumeric('init');

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
    
	
	$("#CPM_ATR_TYPE_MASA").change(function(){
		$('#CPM_MASA_PAJAK').val(waktu[$(this).val()-1]);
		setJangkaWaktu();
		calculation();
		
	});
	
	$('#CPM_TOTAL_PAJAK').keyup(function() {
		if ($(this).attr('readonly') === 'readonly')
            return false;
        calculation();
    });
    

	
	$('select#CPM_ATR_REKENING').change(function(){
		var kdrek = $(this).val();
		var data = $('select#CPM_ATR_REKENING option[value="'+kdrek+'"]').data();
		$('#CPM_ATR_TYPE_MASA').val(6);//bulan
		$('#CPM_ATR_TYPE_MASA').trigger('change'); 
		// $('#CPM_ATR_TYPE_MASA option:not(:selected)').attr('disabled','disabled');
		// $('#CPM_ATR_BIAYA').attr('readonly','readonly');
		// $('#CPM_ATR_MUKA').val(1);
		// $('#CPM_ATR_MUKA').attr('readonly','readonly');
		$('#CPM_ATR_JUMLAH').val(1);
		$('#CPM_ATR_BIAYA').attr('readonly','readonly').val(data.tarif);
		$('#CPM_ATR_HARGA_DASAR_UK').attr('readonly','readonly').val(data.harga);
		$('#CPM_ATR_HARGA_DASAR_TIN').attr('readonly','readonly').val(data.tinggi);
		
		if (
			//kdrek === '4.1.01.09.01.005'  || // Bando
			kdrek === '4.1.01.09.04' || // Berjalan
			kdrek === '4.1.01.09.05' || // Udara
			kdrek === '4.1.01.09.06' || // Apung
			//kdrek === '4.1.01.09.09' || // Film
			//kdrek === '4.1.01.09.10'  // Peragaan
			kdrek === '4.1.01.09.08'  // Film
	){
			// Panjang + Lebar
		$('#CPM_ATR_PANJANG').removeAttr('readonly');
		$('#CPM_ATR_LEBAR').removeAttr('readonly');
		$('#CPM_ATR_TINGGI').val('0').attr('readonly',true);
	}else if(
		kdrek === '4.1.01.09.02' || // Melekat/Stiker
		kdrek === '4.1.01.09.07' || // Suara
		kdrek === '4.1.01.09.03' // Selebaran
		//kdrek === '4.1.01.09.03' || // Selebaran
		//kdrek === '4.1.01.09.08' // Selebaran
	){
		// Hanya Jumlah
		$('#CPM_ATR_PANJANG').val('0').attr('readonly',true);
		$('#CPM_ATR_LEBAR').val('0').attr('readonly',true);
		$('#CPM_ATR_TINGGI').val('0').attr('readonly',true);
	}else{
		// Panjang + Lebar + Tinggi
		$('#CPM_ATR_PANJANG').removeAttr('readonly');
		$('#CPM_ATR_LEBAR').removeAttr('readonly');
		$('#CPM_ATR_TINGGI').removeAttr('readonly');
		// $('#label_jumlah').html('Jumlah (Qty)');
	}
	
	
	$('#CPM_ATR_JAM').val('0');
	if(kdrek === "4.1.01.09.01.004"){
		$('.ID_JAM').show();
	}else{
		$('.ID_JAM').hide();
	}

		nmrek = data.nmrek;
		$("#nama-rekening").html(nmrek);
		
		calculation();
	});
	

});
