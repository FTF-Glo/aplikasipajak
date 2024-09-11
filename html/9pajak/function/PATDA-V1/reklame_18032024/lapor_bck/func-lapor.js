$body = $("body");
$(document).on({
    ajaxStart: function() {
        $body.addClass("loading");
    },
    ajaxStop: function() {
        $body.removeClass("loading");
    }
});

var waktu_label = ['Tahun','Semester','Triwulan','Bulan','Minggu','Hari'];
function hitung_masa() {
	var startdate = $('#CPM_ATR_BATAS_AWAL').val();
	var enddate = $('#CPM_ATR_BATAS_AKHIR').val();
	
	if(startdate == "" || enddate == "") return false;
	$.ajax({
		type: "POST",
		data: {startdate:startdate,enddate:enddate,'function':'hitung_masa'},
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		dataType:'json',
		success: function(res) {
			waktu = [res.tahun, res.semester,res.triwulan, res.bulan, res.minggu, res.hari];
			
			$('#CPM_ATR_JUMLAH_TAHUN').val(res.tahun);
			$('#CPM_ATR_JUMLAH_BULAN').val(res.bulan);
			$('#CPM_ATR_JUMLAH_MINGGU').val(res.minggu);
			$('#CPM_ATR_JUMLAH_HARI').val(res.hari);
                    
			if(type_masa!=0){
				setJangkaWaktu();
				calculation();
			}
			$("#CPM_ATR_REKENING").removeAttr("readonly");
		}
	});
}
function setJangkaWaktu(){
	var durasi = (typeof waktu[type_masa-1] === 'undefined')? 0 : waktu[type_masa-1];
	var html = "<span>"+durasi+" "+waktu_label[type_masa-1]+"</span>";
	$("#jangka-waktu").html(html);
}

function get_hargadasar(){
	var postData = {};
	postData.function= 'get_hargadasar';
	postData.TYPE_MASA= $('#CPM_ATR_TYPE_MASA').val();
	postData.REKENING= $('#CPM_ATR_REKENING').val();
	
	type_masa = postData.TYPE_MASA;
	setJangkaWaktu();
	
	$.ajax({
		type: "POST",
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		data: postData,
		dataType: 'json',
		success: function(data){
			harga_dasar = data.harga_dasar;
			$("#harga-dasar").autoNumeric('set',harga_dasar);
			calcu();
		}		
	});
}
$(document).ready(function() {    
    
    $('#CPM_ATR_BATAS_AWAL').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
		changeMonth: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "...",
        onSelect: function(dateText) {
            $(this).change();
            hitung_masa();
        }
    });
    $('#CPM_ATR_BATAS_AKHIR').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
		changeMonth: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "...",
        onSelect: function(dateText) {
            $(this).change();
            hitung_masa();
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
    $('input#CPM_ATR_MUKA').autoNumeric('init');
    $('input#CPM_ATR_JARI').autoNumeric('init');

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
            // "PAJAK[CPM_TOTAL_PAJAK]": "required",
        },
        messages: {
            "PAJAK[CPM_NO]": " 1 harus diisi",
            "PAJAK[CPM_NPWPD]": " 2 harus diisi",
            "PAJAK[CPM_NAMA_WP]": " 3 harus diisi",
            "PAJAK[CPM_ALAMAT_WP]": " 4 harus diisi",
            "PAJAK[CPM_NOP]": " 5 harus diisi",
            "PAJAK[CPM_NAMA_OP]": " 6 harus diisi",
            "PAJAK[CPM_ALAMAT_OP]": " 7 harus diisi",
            "PAJAK[CPM_TOTAL_OMZET]": " 8 harus diisi",
            //"PAJAK[CPM_TOTAL_PAJAK]": " 9 harus diisi",
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

    $("#btn-search-npwpd").click(function() {
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=7",
            url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
            dataType: "json",
            success: function(res) {
                if (res.result === 1) {
                    $("#CPM_ID_PROFIL").val(res.CPM_ID);
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NOP").val(res.CPM_NOP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                } else {
                    alert("NPWPD tidak ditemukan!")
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });
    
    $('#CPM_ATR_JUMLAH, #CPM_ATR_TINGGI, #CPM_ATR_LEBAR, #CPM_ATR_MUKA, #CPM_ATR_TINGGI, #CPM_ATR_JARI, #CPM_DISCOUNT, #CPM_DENDA_TERLAMBAT_LAP, #CPM_ATR_JUMLAH').keyup(function() {
        calculation();
    });
});

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

function calculation(){
	$("#harga-dasar").autoNumeric('init');
	get_hargadasar();
}

function calcu() {
	$('#CPM_ATR_TINGGI').autoNumeric('init');
	$('#CPM_ATR_TINGGI').autoNumeric('init');
	$('#CPM_ATR_LEBAR').autoNumeric('init');
	$('#CPM_ATR_JUMLAH').autoNumeric('init');
    $('#CPM_ATR_TOTAL').autoNumeric('init');
    $('#CPM_ATR_MUKA').autoNumeric('init');
    $('#CPM_ATR_JARI').autoNumeric('init');
    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT').autoNumeric('init');
    $('#CPM_DISCOUNT').autoNumeric('init',{vMax: '100'});
    $('#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');
    
	var panjang = Number($('#CPM_ATR_TINGGI').autoNumeric('get'));
    var lebar = Number($('#CPM_ATR_LEBAR').autoNumeric('get'));
    var muka = Number($('#CPM_ATR_MUKA').autoNumeric('get'));
    var jumlah = Number($('#CPM_ATR_JUMLAH').autoNumeric('get'));
    var persen_pengurangan = Number($('#CPM_DISCOUNT').autoNumeric('get'));
        
	var lama = waktu[type_masa-1];
	var masa = waktu_label[type_masa-1];
	
	var html = "<span style='font-weight:bold;font-style:italic;'>Tarif Pajak x Lama Pemasangan x Ukuran x Muka x Jumlah reklame x Harga dasar</span><br/>";
	html += "<span>";
	html += persen_pajak + "% (tarif pajak) x ";
	html += lama + " ("+waktu_label[type_masa-1]+") x ";
	html += (panjang * lebar) + " m<sup>2</sup> (luas) x ";
	html += muka + " (muka) x ";
	html += jumlah + " (jumlah reklame) x ";	
	html += addCommas(harga_dasar) + " (harga dasar)";
	
	$('#perhitungan_1').html(html);
	
	var total_omzet = eval(persen_pajak) / 100 * eval(lama) * eval(panjang) * eval(lebar) * eval(jumlah) * eval(muka) * eval(harga_dasar);
	var sanksi = eval(get_persen_terlambat_lapor()) * total_omzet / 100;
	var total_pajak = eval(total_omzet) + eval(sanksi);
	
	total_pajak = eval(total_pajak) - (eval(total_pajak) * eval(persen_pengurangan)/ 100);
	
    $("#CPM_TOTAL_OMZET").autoNumeric('set',total_omzet);
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set',sanksi);
    $("#CPM_TARIF_PAJAK").val(persen_pajak);
    $("#CPM_ATR_TARIF").val(tarif_kawasan);
    $("#CPM_MASA_PAJAK").val(lama);
    $("#CPM_TOTAL_PAJAK").autoNumeric('set',total_pajak);
    $("#CPM_ATR_TOTAL").autoNumeric('set',total_pajak);
    $("#CPM_JNS_MASA_PAJAK").val(masa);

    if (total_pajak !== 0)
        $("#terbilang").html(terbilang(Math.round(total_pajak)) + " Rupiah");

}

$(function() {
	
	if($("#CPM_ATR_REKENING").is('[readonly]')===false){
		$.ajax({
			type: "POST",
			url: "view/PATDA-V1/reklame/svc-reklame.php",
			data: {'function' : 'get_permen'},
			dataType: 'json',
			success: function(data){
				$("#CPM_ATR_REKENING").select2({
					placeholder: ' ',
					allowClear: true,
					data : data.items,
					escapeMarkup: function (markup) { return markup;},
					templateResult: formatRepo,
					templateSelection: formatRepoSelection
				});
			}
		});
		
		$('#CPM_ATR_REKENING').on("select2:select", function(e) { 
			var data = e.params.data;
			
			tarif_kawasan = data.tarif3;
			persen_pajak = data.tarif1;
			
			$("#CPM_ATR_REKENING").val(data.kode_rekening);
			$("#nama-rekening").html(data.nama_rekening);
			$("#tarif-kawasan").html(tarif_kawasan);
			$('#warning-rekening').html("");
			$("#CPM_TARIF_PAJAK").val(persen_pajak);
			
			if(waktu.length != 0){
				calculation();
			}
			return false;
		});
	}
	
	$("#CPM_ATR_TYPE_MASA").click(function(){
		type_masa = $(this).val();
		if(waktu.length != 0){
			
			var durasi = (typeof waktu[type_masa-1] === 'undefined')? 0 : waktu[type_masa-1];
			
			html = "<span>"+durasi+" "+waktu_label[type_masa-1]+"</span>";
			$("#jangka-waktu").html(html);
			calculation();
		}
	});
	

});

function formatRepo (repo) {
	if (repo.loading) return repo.text;
	var markup = "<div class='select2-result-repository clearfix'>" +
	"<div class='select2-result-repository__meta'>" +
	  "<div class='select2-result-repository__title'>" + repo.id + "</div>";
	markup += (repo.text)? "<div class='select2-result-repository__description'>" + repo.text + "</div>" : "";
	markup += "</div></div>";

  return markup;
}
function formatRepoSelection (repo, con) {
	return (repo.id)? repo.id : 'Pilih rekening ';
}
