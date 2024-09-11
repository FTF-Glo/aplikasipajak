$body = $("body");
$(document).on({
	ajaxStart: function () {
		$body.addClass("loading");
	},
	ajaxStop: function () {
		$body.removeClass("loading");
	},
});



$(document).ready(function () {


	var nx = $("#count").val();
	for (let i = 2; i <= nx; i++) {
		$('select[id="CPM_ATR_JALAN_TYPE-' + i).select2({ placeholder: "PILIH JALAN" });

	}


	$('input[type="checkbox"]#HITUNG_PENGURANGAN').on('change', function () {
		var v = $(this);
		var pengurangan = $('#CPM_DISCOUNT');
		if (v.prop('checked')) {
			pengurangan.prop('readonly', false);
		} else {
			pengurangan.prop('readonly', true);
		}

		if (!Number(pengurangan.autoNumeric('get'))) {
			pengurangan.autoNumeric('set', 0);
		}
	});

	$('#CPM_DISCOUNT').autoNumeric('init');


	$("select#CPM_ATR_REKENING").change(function () {
		var kdrek = $(this).val();
		var data = $(
			'select#CPM_ATR_REKENING option[value="' + kdrek + '"]'
		).data();
		$("#CPM_ATR_TYPE_MASA").val(6); //bulan
		$("#CPM_ATR_TYPE_MASA").trigger("change");
		$("#CPM_ATR_JUMLAH").val(1);
		$("#CPM_ATR_BIAYA").attr("readonly", "readonly").val(data.tarif);
		$("#CPM_ATR_HARGA_DASAR_UK").attr("readonly", "readonly").val(data.harga);
		$("#CPM_ATR_HARGA_DASAR_TIN").attr("readonly", "readonly").val(data.tinggi);

		if (
			kdrek === "4.1.01.09.04" ||
			kdrek === "4.1.01.09.05" ||
			kdrek === "4.1.01.09.06" ||
			kdrek === "4.1.01.09.08"
		) {
			// Panjang + Lebar
			$("#CPM_ATR_PANJANG").removeAttr("readonly");
			$("#CPM_ATR_LEBAR").removeAttr("readonly");
			$("#CPM_ATR_TINGGI").val("0").attr("readonly", true);
		} else {
			// Panjang + Lebar + Tinggi
			$("#CPM_ATR_PANJANG").removeAttr("readonly");
			$("#CPM_ATR_LEBAR").removeAttr("readonly");
			$("#CPM_ATR_TINGGI").removeAttr("readonly");
			// $('#label_jumlah').html('Jumlah (Qty)');
		}

		$("#CPM_ATR_JAM").val("0");
		if (kdrek === "4.1.01.09.01.004") {
			$(".ID_JAM").show();
		} else {
			$(".ID_JAM").hide();
		}



		nmrek = data.nmrek;
		$("#nama-rekening").html(nmrek);

		calculation();
	});

	$("select#CPM_ATR_JALAN_TYPE").change(function () {
		get_hargadasar();
	});

	$("#CPM_CEK_PIHAK_KETIGA").click(function () {
		var x = document.getElementById("CPM_CEK_PIHAK_KETIGA").checked;
		calculation();
		$('#CPM_NILAI_PIHAK_KETIGA').val('');
		if ($('#CPM_CEK_PIHAK_KETIGA').prop('checked')) {
			$('#CPM_NILAI_PIHAK_KETIGA').removeAttr('readonly');
			$('#CPM_ATR_KAWASAN').prop('selectedIndex', 0);
			$('#CPM_ATR_KAWASAN').attr('disabled', 'true');
			$('#CPM_ATR_TINGGI').attr('readonly', 'readonly');
			$('#CPM_ATR_LEBAR').attr('readonly', 'readonly');
			$('#CPM_ATR_PANJANG').attr('readonly', 'readonly');
			$('#CPM_ATR_JUMLAH').attr('readonly', 'readonly');
		} else {
			$('#CPM_NILAI_PIHAK_KETIGA').attr('readonly', 'readonly');
			$('#CPM_ATR_KAWASAN').removeAttr('disabled');
			$('#CPM_ATR_TINGGI').removeAttr('readonly');
			$('#CPM_ATR_LEBAR').removeAttr('readonly');
			$('#CPM_ATR_JUMLAH').removeAttr('readonly');
			$('#CPM_ATR_PANJANG').removeAttr('readonly');
		}
	});
	$("#CPM_NILAI_PIHAK_KETIGA").change(function () {
		calculation();
	});
});

function rekDetail(no) {
	var rek = $("#CPM_ATR_REKENING-" + no + " option:selected"),
		data = rek.data();
	$("#nama-rekening-" + no).html(data.nmrek);
	$("#CPM_ATR_BIAYA-" + no).val(data.tarif);

	$.ajax({
		type: "POST",
		url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
		data: {
			function: "list_pemakaian",
			CPM_ATR_REKENING: $("#CPM_ATR_REKENING-" + no).val(),
		},
		async: false,
		success: function (html) {
			$("#CPM_ATR_TYPE_MASA-" + no).html(html);
		},
	});

	var kdrek = $("#CPM_ATR_REKENING-" + no).val();
	if (kdrek === "4.1.01.09.01.002") {
		$("#CPM_ATR_JAM-" + no).removeAttr("readonly");
		$("#CPM_ATR_PANJANG-" + no).removeAttr("readonly");
		$("#CPM_ATR_LEBAR-" + no).removeAttr("readonly");
		$("#CPM_ATR_MUKA-" + no).removeAttr("readonly");
		$("#CPM_ATR_TINGGI-" + no).removeAttr("readonly");
	} else if (kdrek === "4.1.01.09.06" || kdrek === "4.1.01.09.03") {
		$("#CPM_ATR_PANJANG-" + no)
			.val("0")
			.attr("readonly", true);
		$("#CPM_ATR_LEBAR-" + no)
			.val("0")
			.attr("readonly", true);
		$("#CPM_ATR_MUKA-" + no)
			.val("0")
			.attr("readonly", true);
		$("#CPM_ATR_JAM-" + no)
			.val("0")
			.attr("readonly", true);
		$("#CPM_ATR_TINGGI-" + no)
			.val("0")
			.attr("readonly", true);
	} else {
		$("#CPM_ATR_PANJANG-" + no).removeAttr("readonly");
		$("#CPM_ATR_LEBAR-" + no).removeAttr("readonly");
		$("#CPM_ATR_MUKA-" + no).removeAttr("readonly");
		$("#CPM_ATR_TINGGI-" + no).removeAttr("readonly");
	}

	$("#CPM_ATR_JAM-" + no).val("0");
	if (kdrek === "4.1.01.09.01.004") {
		$(".ID_JAM-" + no).show();
	} else {
		$(".ID_JAM-" + no).hide();
	}
}

function hitung_masa() {
	var startdate = $("#CPM_ATR_BATAS_AWAL").val();
	var enddate = $("#CPM_ATR_BATAS_AKHIR").val();

	if (startdate == "" || enddate == "") return false;
	$.ajax({
		type: "POST",
		data: { startdate: startdate, enddate: enddate, function: "hitung_masa" },
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		dataType: "json",
		async: false,
		success: function (res) {
			waktu = [
				res.tahun,
				res.semester,
				res.triwulan,
				res.bulan,
				res.minggu,
				res.hari,
			];

			$("#CPM_ATR_JUMLAH_TAHUN").val(res.tahun);
			$("#CPM_ATR_JUMLAH_BULAN").val(res.bulan);
			$("#CPM_ATR_JUMLAH_MINGGU").val(res.minggu);
			$("#CPM_ATR_JUMLAH_HARI").val(res.hari);
			setJangkaWaktu();
		},
	});
	return true;
}

function setJangkaWaktu() {
	var durasi = $("#CPM_MASA_PAJAK").val();
	var html =
		"<span>" +
		durasi +
		" " +
		$("#CPM_ATR_TYPE_MASA option:selected").text() +
		"</span>";
	$("#jangka-waktu").html(html);
}

// function setJangkaWaktu(){
// 	var durasi = $('#CPM_MASA_PAJAK').val();
// 	var html = "<span>"+durasi+" "+$('#CPM_ATR_TYPE_MASA option:selected').text()+"</span>";
// 	$("#jangka-waktu").html(html);
// }

function addCommas(nStr) {
	nStr += "";
	x = nStr.split(".");
	x1 = x[0];
	x2 = x.length > 1 ? "." + x[1] : "";
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, "$1" + "," + "$2");
	}
	return x1 + x2;
}



function load_first() {
	var kdrek = $("select#CPM_ATR_REKENING").val();
	var data = $('select#CPM_ATR_REKENING option[value="' + kdrek + '"]').data();

	if (
		kdrek === "4.1.1.4.01.3" || //Megatron/Videotron/LED
		kdrek === "4.1.1.4.01.1" || //Billboard/Papan Nama/Neon Box - Bersinar
		kdrek === "4.1.1.4.01.2" || //Billboard/Papan Nama/Neon Box - Tidak Bersinar
		kdrek === "4.1.1.4.01.4" || //Baliho
		kdrek === "4.1.1.4.02.1" || //Kain/Spanduk/Umbul umbul/Banner
		kdrek === "4.1.1.4.10.1" //Reklame Perusahaan Lain Pada Dinding Bangunan Toko
	) {
		$("#CPM_ATR_BIAYA")
			.attr("readonly", "readonly")
			.val(data.tarif + "%");
		$("#CPM_ATR_HARGA_DASAR_UK").attr("readonly", "readonly").val(data.harga);
		$("#CPM_ATR_HARGA_DASAR_TIN").attr("readonly", "readonly").val(data.tinggi);
		$("#CPM_ATR_JUMLAH").attr("readonly", "readonly");

		// $('#CPM_ATR_KAWASAN').attr('disabled','false');
	} else if (
		kdrek === "4.1.1.4.06.1" || //Reklame Udara
		kdrek === "4.1.1.4.08.1" //Reklame Suara
	) {
		$("#CPM_ATR_BIAYA")
			.attr("readonly", "readonly")
			.val(data.tarif + "%");
		$("#CPM_ATR_HARGA_DASAR_UK").attr("readonly", "readonly").val(data.harga);
		$("#CPM_ATR_HARGA_DASAR_TIN").attr("readonly", "readonly").val(data.tinggi);
		$("#CPM_ATR_TINGGI").attr("readonly", "readonly");
		$("#CPM_ATR_LEBAR").attr("readonly", "readonly");
		$("#CPM_ATR_PANJANG").attr("readonly", "readonly");
		$("#CPM_ATR_JUMLAH").attr("readonly", "readonly");
	} else if (
		kdrek === "4.1.1.4.04.1" || //Reklame Selebaran/Brosur/Leaflet
		kdrek === "4.1.1.4.05.1" || //Reklame Berjalan Termasuk Kendaraan
		kdrek === "4.1.1.4.09.1" || //Reklame Film/Slide
		kdrek === "4.1.1.4.11.1" //Reklame Peragaan
	) {
		$("#CPM_ATR_BIAYA")
			.attr("readonly", "readonly")
			.val(data.tarif + "%");
		$("#CPM_ATR_HARGA_DASAR_UK").attr("readonly", "readonly").val(data.harga);
		$("#CPM_ATR_HARGA_DASAR_TIN").attr("readonly", "readonly").val(data.tinggi);
		$("#CPM_ATR_TINGGI").attr("readonly", "readonly");
		$("#CPM_ATR_LEBAR").attr("readonly", "readonly");
		$("#CPM_ATR_PANJANG").attr("readonly", "readonly");
		$("#CPM_ATR_JUMLAH").removeAttr("readonly");
	}

	nmrek = data.nmrek;
	$("#nama-rekening").html(nmrek);
}

function hitungDetail(no) {
	var rek = $("#CPM_ATR_REKENING-" + no),
		data = rek.find("option:selected").data();
	$('select[id="CPM_ATR_JALAN_TYPE-' + no).select2({ placeholder: "PILIH JALAN" });

	var params = {
		kdrek: rek.val(),
		panjang: $("#CPM_ATR_PANJANG-" + no).val(),
		lebar: $("#CPM_ATR_LEBAR-" + no).val(),
		muka: $("#CPM_ATR_MUKA-" + no).val(),
		tinggi: $("#CPM_ATR_TINGGI-" + no).val(),
		waktu: $("#CPM_ATR_WAKTU-" + no).val(),
		sisi: Number($("#CPM_ATR_SISI-" + no).val()),
		sudut_pandang: $("#CPM_ATR_SUDUT_PANDANG-" + no).val(),
		biaya: $("#CPM_ATR_BIAYA-" + no).val(),
		tarif: data.tarif,
		kawasan: $("#CPM_ATR_KAWASAN-" + no).val(),
		jalan: $("#CPM_ATR_JALAN_TYPE-" + no).val(),
		// jalan_type: $("#CPM_ATR_JALAN-" + no).val(),
		jalan_type: ($("#CPM_ATR_JALAN_TYPE-" + no).val() != '') ? $("#CPM_ATR_JALAN-" + no).val() : false,
		durasi: $("#CPM_MASA_PAJAK").val(),
		durasi_label: $("#CPM_ATR_TYPE_MASA option:selected").text(),
		durasi_hari: $("#CPM_ATR_JUMLAH_HARI").val(),
		judul: $("#CPM_ATR_JUDUL-" + no).val(),
		jumlah: $("#CPM_ATR_JUMLAH-" + no).val(),
		gedung: $(".CPM_GEDUNG-" + no + ":checked").val(),
		bangunan: $(".CPM_BANGUNAN-" + no + ":checked").val(),
		alkohol_rokok: $(".CPM_ALKOHOL_ROKOK-" + no + ":checked").val(),
		// gedung: $(".CPM_GEDUNG2:checked").val(),
		// bangunan: $(".CPM_BANGUNAN2:checked").val(),
		// alkohol_rokok : $(".CPM_ALKOHOL_ROKOK2:checked").val(),
		// jumlah: $("#CPM_ATR_JUMLAH-" + no).val(),
		x: document.getElementById("CPM_CEK_PIHAK_KETIGA").checked,
		npk: $("#CPM_NILAI_PIHAK_KETIGA").val(),
		jam: $("#CPM_ATR_JAM-" + no).val(),
		function: "get_hargadasar",
	};

	if (
		(params.panjang == "" ||
			params.lebar == "" ||
			params.tinggi == "" ||
			params.muka == "" ||
			params.kawasan == "" ||
			params.jumlah == "") == true
	)
		// console.log(tinggi);
		return false;

	$.ajax({
		url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
		type: "POST",
		dataType: "json",
		data: params,
		async: false,
		success: function (res) {
			// console.log(res.lokasi_reklame);
			$("#area_perhitungan-" + no).html(res.html);
			$("#CPM_ATR_TOTAL-" + no).autoNumeric("init");
			$("#CPM_ATR_TOTAL-" + no).autoNumeric("set", res.total);
			$("#CPM_ATR_JALAN-" + no).val(res.lokasi_reklame);
			hitung_total();
			console.log(res.html);
		},
	});
}
function calculation() {
	$("#harga-dasar").autoNumeric("init");

	if ($.trim($("#jangka-waktu").html()) === "") alert("Silakan isi masa pajak");

	if ($("#CPM_NO").length) hitung_masa();
	get_hargadasar();
}

function delRow(no) {
	// var no = parseInt($("#count").val());
	// console.log(no);
	$.ajax({
		type: "POST",
		data: {
			no: no,
			function: "addRow",
		},
		url: "view/PATDA-V1/reklame/svc-reklame.php",
		async: false,
		success: function (res) {
			$("#atr_rek-" + no).remove();
			// $("#count").val(no);
		},
	});
}


function get_op_lainnya(no) {
	var kdrek = $("#CPM_ATR_REKENING-" + no).val();

	var params = {
		kdrek: kdrek,
		cpm_nop: $("#CPM_NOP-" + no).val(),
		function: "get_dataop",
	};
	$.ajax({
		url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
		type: "POST",
		dataType: "json",
		data: params,
		async: false,
		success: function (res) {
			// console.log()
			$("#CPM_ATR_JUDUL-" + no).val(res.CPM_NAMA_OP);
			$("#CPM_ATR_LOKASI-" + no).val(res.CPM_ALAMAT_OP);
		},
	});
}

function get_op() {
	var kdrek = $("#CPM_ATR_REKENING").val();

	var params = {
		kdrek: kdrek,
		cpm_nop: $("#CPM_NOP").val(),
		function: "get_dataop",
	};
	$.ajax({
		url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
		type: "POST",
		dataType: "json",
		data: params,
		async: false,
		success: function (res) {
			// console.log()
			$("#CPM_ATR_JUDUL").val(res.CPM_NAMA_OP);
			$("#CPM_ATR_LOKASI").val(res.CPM_ALAMAT_OP);
		},
	});
}

function get_hargadasar() {
	var kdrek = $("#CPM_ATR_REKENING").val();
	var data = $('select#CPM_ATR_REKENING option[value="' + kdrek + '"]').data();

	var params = {
		kdrek: kdrek,
		panjang: $("#CPM_ATR_PANJANG").val(),
		tab: $("#tab").val(),
		lebar: $("#CPM_ATR_LEBAR").val(),
		tinggi: $("#CPM_ATR_TINGGI").val(),
		muka: $("#CPM_ATR_MUKA").val(),
		sisi: Number($("#CPM_ATR_SISI").val()),
		sudut_pandang: $("#CPM_ATR_SUDUT_PANDANG").val(),
		biaya: $("#CPM_ATR_BIAYA").val(),
		harga_dasar_uk: $("#CPM_ATR_HARGA_DASAR_UK").val(),
		harga_dasar_tin: $("#CPM_ATR_HARGA_DASAR_TIN").val(),
		tarif: data.tarif,
		kawasan: $("#CPM_ATR_KAWASAN").val(),
		jalan: $("#CPM_ATR_JALAN_TYPE").val(),
		jalan_type: ($("#CPM_ATR_JALAN_TYPE").val() != '') ? $("#CPM_ATR_JALAN").val() : false,
		durasi: $("#CPM_MASA_PAJAK").val(),
		durasi_label: $("#CPM_ATR_TYPE_MASA option:selected").text(),
		durasi_hari: $("#CPM_ATR_JUMLAH_HARI").val(),
		durasi_minggu: $("#CPM_ATR_JUMLAH_MINGGU").val(),
		durasi_bulan: $("#CPM_ATR_JUMLAH_BULAN").val(),
		durasi_tahun: $("#CPM_ATR_JUMLAH_TAHUN").val(),
		jumlah: $("#CPM_ATR_JUMLAH").val(),
		x: document.getElementById("CPM_CEK_PIHAK_KETIGA").checked,
		npk: $("#CPM_NILAI_PIHAK_KETIGA").val(),
		gedung: $(".CPM_GEDUNG:checked").val(),
		bangunan: $(".CPM_BANGUNAN:checked").val(),
		alkohol_rokok: $(".CPM_ALKOHOL_ROKOK:checked").val(),
		nsr: $(".CPM_NSR:checked").val(),
		jam: $("#CPM_ATR_JAM").val(),
		function: "get_hargadasar",
	};
	// console.log(params.tab)
	// if (
	// 	(kdrek == "" ||
	// 		params.panjang == "" ||
	// 		params.lebar == "" ||
	// 		params.tinggi == "" ||
	// 		params.kawasan == "" ||
	// 		params.jumlah == "") == true
	// )
	// 	return false;

	$.ajax({
		url: "function/PATDA-V1/reklame/lapor/svc-lapor.php",
		type: "POST",
		dataType: "json",
		data: params,
		async: false,
		success: function (res) {
			$("#area_perhitungan").html(res.html);
			$("#CPM_ATR_TARIF").val(res.tarif);
			// console.log(res.html);
			var tarif = res.tarif;
			var omzet = res.total;
			var kurangLebih = 0;

			var sanksi = $("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("get");
			$("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("set", sanksi);

			var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);

			$("input#CPM_DISCOUNT").autoNumeric("init", { vMax: "100" });
			$("#CPM_TOTAL_OMZET").autoNumeric("init");
			$("#CPM_PEMBULATAN").autoNumeric("init");
			$("#CPM_TOTAL_PAJAK").autoNumeric("init");
			$("#CPM_ATR_TOTAL").autoNumeric("init");

			var persen_pengurangan = Number($("#CPM_DISCOUNT").autoNumeric("get"));

			total = eval(total) - (eval(total) * eval(persen_pengurangan)) / 100;
			total = Math.ceil(total);
			pembulatan = total - omzet;

			$("#CPM_ATR_JALAN").val(res.lokasi_reklame);
			// $("#CPM_ATR_JALAN").val(res.lokasi_reklame);
			$("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("set", sanksi);
			// if (params.tab != 1 && params.tab != 4) {
			$("#CPM_TOTAL_OMZET").autoNumeric("set", omzet);
			$("#CPM_TOTAL_PAJAK").autoNumeric("set", total);
			// }
			$("#CPM_PEMBULATAN").autoNumeric("set", pembulatan);

			$("#CPM_TARIF_PAJAK").val(params.tarif);
			$("#CPM_ATR_TARIF").val(params.tarif);
			$("#CPM_MASA_PAJAK").val(params.durasi);
			$("#CPM_ATR_TOTAL").autoNumeric("set", total);
			$("#CPM_JNS_MASA_PAJAK").val(params.durasi_label);

			if (res.total !== 0)
				$("#terbilang").html(terbilang(Math.ceil(total)) + " Rupiah");
		},
	});




}

$(function () {
	$(".datepicker").datepicker({
		dateFormat: "dd/mm/yy",
		changeYear: true,
		changeMonth: true,
		showOn: "button",
		buttonImageOnly: false,
		buttonText: "...",
		onSelect: function (dateText) {
			$("#CPM_ATR_TYPE_MASA").trigger("change");
			if (hitung_masa()) get_hargadasar();
		},
		onClose: function (dateText, datePickerInstance) {
			$("#CPM_ATR_TYPE_MASA").trigger("change");
		},
	});

	$("input:reset").click(function () {
		$("select#CPM_NPWPD").html("").trigger("change");
	});

	$("input.format").autoNumeric("init");
	$("input#CPM_ATR_JUMLAH").autoNumeric("init");
	$("input#CPM_ATR_TOTAL").autoNumeric("init");
	$("input#CPM_ATR_LEBAR").autoNumeric("init");
	// $("input#CPM_ATR_TINGGI").autoNumeric("init");
	$("input#CPM_ATR_MUKA").autoNumeric("init");
	$("input#CPM_ATR_PANJANG").autoNumeric("init");
	$("input#CPM_ATR_SISI").autoNumeric("init", { vMin: 1, mDec: 0 });
	$("input#CPM_ATR_BIAYA").autoNumeric("init");
	$("input#CPM_ATR_HARGA_DASAR_UK").autoNumeric("init");
	$("input#CPM_ATR_HARGA_DASAR_TIN").autoNumeric("init");

	$("input#CPM_TOTAL_OMZET").autoNumeric("init");
	$("input#CPM_DENDA_TERLAMBAT").autoNumeric("init");
	$("input#CPM_DISCOUNT").autoNumeric("init", { vMax: "100" });
	$("input#CPM_TOTAL_PAJAK").autoNumeric("init");
	$("input#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("init");

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
			"PAJAK_ATR[CPM_ATR_JUDUL][]": "required",
			"PAJAK_ATR[CPM_ATR_LOKASI][]": "required",
			"PAJAK_ATR[CPM_ATR_JALAN_TYPE][]": "required",
			"PAJAK_ATR[CPM_ATR_JALAN][]": "required",
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
			"PAJAK_ATR[CPM_ATR_JUDUL][]": "harus diisi",
			"PAJAK_ATR[CPM_ATR_LOKASI][]": "harus diisi",
			"PAJAK_ATR[CPM_ATR_JALAN_TYPE][]": "harus diisi",
			"PAJAK_ATR[CPM_ATR_JALAN][]": "harus diisi",
		},
	});

	$("#CPM_TRAN_INFO").removeClass("required");
	$("input.AUTHORITY").change(function () {
		if ($(this).val() === 1) {
			$("#CPM_TRAN_INFO").removeClass("required");
			$("#CPM_TRAN_INFO").attr("readonly", "readonly");
			$("#CPM_TRAN_INFO").val("");
		} else {
			$("#CPM_TRAN_INFO").addClass("required");
			$("#CPM_TRAN_INFO").removeAttr("readonly");
		}
	});

	$("input.btn-submit").click(function () {
		var action = $(this).attr("action");
		var res = false;

		$("#function").val(action);

		if (action === "save") {
			if (form.valid()) {
				res = confirm("Apakah anda yakin untuk menyimpan laporan ini?");
			}
		} else if (action === "save_final") {
			if (form.valid()) {
				res = confirm(
					"Apakah anda yakin untuk menyimpan dan memfinalkan laporan ini?"
				);
			}
		} else if (action === "save_final_perpanjangan") {
			if (form.valid()) {
				res = confirm(
					"Apakah anda yakin untuk menyimpan dan memfinalkan laporan ini?"
				);
			}
		} else if (action === "update_final") {
			if (form.valid()) {
				res = confirm(
					"Apakah anda yakin untuk memperbaharui dan memfinalkan laporan ini?"
				);
			}
		} else if (action === "update") {
			if (form.valid()) {
				res = confirm("Apakah anda yakin untuk merubah laporan ini?");
			}
		} else if (action === "delete") {
			res = confirm("Apakah anda yakin untuk menghapus laporan ini?");
		} else if (action === "verifikasi" || action === "persetujuan") {
			res = confirm(
				"Apakah anda yakin untuk menyetujui / menolak laporan ini?"
			);
		} else if (action == "verifikasi_2" || action == "verifikasi_2") {
			res = confirm("Apakah anda yakin untuk menyetujui / menolak laporan ini?");
		} else if (action === "new_version") {
			if (form.valid()) {
				res = confirm(
					"Apakah anda yakin untuk membuat versi baru laporan ini?"
				);
			}
		} else if (action === "new_version_final") {
			if (form.valid()) {
				res = confirm(
					"Apakah anda yakin untuk membuat versi baru dan memfinalkan laporan ini?"
				);
			}
		}
		if (res) {
			document.getElementById("form-lapor").submit();
		}
	});

	$("input.btn-print").click(function () {
		var action = $(this).attr("action");
		$("#function").val(action);
		$("#form-lapor").attr("target", "_blank");
		document.getElementById("form-lapor").submit();
	});

	$(".btn-tambah").click(function () {
		var no = parseInt($("#count").val()),
			npwpd = $("#CPM_NPWPD").val(),
			type_masa = $("#CPM_ATR_TYPE_MASA").val(),
			waktu =
				$("#CPM_MASA_PAJAK").val() +
				" " +
				$("#CPM_ATR_TYPE_MASA option:selected").text(),
			awal = $("#CPM_ATR_BATAS_AWAL").val(),
			akhir = $("#CPM_ATR_BATAS_AKHIR").val(),
			pajak = $("#CPM_ATR_BIAYA").val(),
			rek = $("#CPM_ATR_REKENING").val();
		if (awal == "" || akhir == "") {
			alert("Silahkan isi masa pajak!");
			return false;
		} else if (rek == "") {
			alert("Silahkan pilih rekening!");
			return false;
		}

		$.ajax({
			type: "POST",
			data: {
				no: no,
				npwpd: npwpd,
				type_masa: type_masa,
				waktu: waktu,
				tarif: pajak,
				function: "addRow",
			},
			url: "view/PATDA-V1/reklame/svc-reklame.php",
			async: false,
			success: function (res) {
				$(".atr_reklame").append(res);
				$("#count").val(no + 1);
				$("#CPM_ATR_PANJANGc" + (no + 1)).autoNumeric("init");
				$("#CPM_ATR_LEBAR-" + (no + 1)).autoNumeric("init");
				$("#CPM_ATR_JUMLAH-" + (no + 1)).autoNumeric("init");
				$("#CPM_ATR_TOTAL-" + (no + 1)).autoNumeric("init");
			},
		});
	});

	/* FUNGSI PADA INPUT DI PELAYANAN*/
	$("#CPM_NPWPD").keyup(function () {
		if ($(this).attr("readonly") === "readonly") return false;
		$("#CPM_ID_PROFIL").val("");
		$("#CPM_NAMA_WP").val("");
		$("#CPM_ALAMAT_WP").val("");
		$("#CPM_NOP").val("");
		$("#CPM_NAMA_OP").val("");
		$("#CPM_ALAMAT_OP").val("");
	});

	$(
		"#CPM_ATR_KAWASAN, #CPM_ATR_JALAN,  #CPM_ATR_SUDUT_PANDANG, #CPM_ATR_TINGGI, .CPM_GEDUNG, .CPM_BANGUNAN"
	).change(function () {
		if (
			$("#CPM_ATR_KAWASAN").val() == "Tersebar" &&
			$("#CPM_ATR_JALAN").val() == "Tersebar"
		) {
			$(".CPM_ATR_TINGGI_").hide();
		} else {
			$(".CPM_ATR_TINGGI_").show();
		}
		calculation();
	});

	$("#CPM_ATR_TYPE_MASA").change(function () {
		$("#CPM_MASA_PAJAK").val(waktu[$(this).val() - 1]);
		setJangkaWaktu();
		calculation();
	});
	$("#CPM_NOP").change(function () {
		get_op();
	});

	$(
		"#CPM_ATR_JUMLAH, #CPM_ATR_PANJANG, #CPM_ATR_LEBAR,#CPM_ATR_TINGGI,#CPM_ATR_SUDUT_PANDANG, #CPM_ATR_MUKA, #CPM_ATR_SISI, #CPM_ATR_MUKA, #CPM_ATR_JARI, #CPM_DISCOUNT, #CPM_DENDA_TERLAMBAT_LAP, #CPM_ATR_JUMLAH, #CPM_ATR_JAM"
	).keyup(function () {
		if ($(this).attr("readonly") === "readonly") return false;
		calculation();
	});

	$("#CPM_ATR_BIAYA,.CPM_ALKOHOL_ROKOK,.CPM_NSR").change(function () {
		if ($(this).attr("readonly") === "readonly") return false;
		calculation();
	});



	$("select#CPM_ATR_REKENING").change(function () {
		var kdrek = $(this).val();
		var data = $(
			'select#CPM_ATR_REKENING option[value="' + kdrek + '"]'
		).data();
		$("#CPM_ATR_TYPE_MASA").val(6); //bulan
		$("#CPM_ATR_TYPE_MASA").trigger("change");
		$("#CPM_ATR_JUMLAH").val(1);
		$("#CPM_ATR_BIAYA").attr("readonly", "readonly").val(data.tarif);
		$("#CPM_ATR_HARGA_DASAR_UK").attr("readonly", "readonly").val(data.harga);
		$("#CPM_ATR_HARGA_DASAR_TIN").attr("readonly", "readonly").val(data.tinggi);

		if (
			kdrek === "4.1.01.09.04" ||
			kdrek === "4.1.01.09.06" ||
			kdrek === "4.1.01.09.08"
		) {
			// Panjang + Lebar
			$("#CPM_ATR_PANJANG").removeAttr("readonly");
			$("#CPM_ATR_LEBAR").removeAttr("readonly");
			$("#CPM_ATR_TINGGI").val("0").attr("readonly", true);
		} else if (kdrek === "4.1.01.09.05") {
			$("#CPM_ATR_PANJANG").val("0").attr("readonly", true);
			$("#CPM_ATR_LEBAR").val("0").attr("readonly", true);
			// $("#CPM_ATR_TINGGI").val("0").attr("readonly", true);
		} else if (kdrek === "4.1.01.09.07") {
			$("#CPM_ATR_PANJANG").val("0").attr("readonly", true);
			$("#CPM_ATR_LEBAR").val("0").attr("readonly", true);
			// $("#CPM_ATR_TINGGI").val("0").attr("readonly", true);
			$("#CPM_ATR_SISI").val("1").attr("readonly", true);
		} else {
			// Panjang + Lebar + Tinggi
			$("#CPM_ATR_PANJANG").removeAttr("readonly");
			$("#CPM_ATR_LEBAR").removeAttr("readonly");
			$("#CPM_ATR_TINGGI").removeAttr("readonly");
			// $('#label_jumlah').html('Jumlah (Qty)');
		}

		$("#CPM_ATR_JAM").val("0");
		if (kdrek === "4.1.01.09.01.004" || kdrek === "4.1.01.09.08" || kdrek === "4.1.01.09.07") {
			$(".ID_JAM").show();
		} else {
			$(".ID_JAM").hide();
		}

		nmrek = data.nmrek;
		$("#nama-rekening").html(nmrek);

		calculation();
	});
});

function hitung_total() {
	var tarif = parseFloat($("#CPM_ATR_BIAYA").val());
	var omzet = parseFloat($("#CPM_ATR_TOTAL").autoNumeric("get"));
	// var omzet = 0;
	// console.log(omzet);
	var kurangLebih = 0;
	var count = parseInt($("#count").val());
	var t;
	if (count == 1) {
		get_hargadasar();
		return false;
	} else if (count > 1) {
		for (var i = 2; i <= count; i++) {
			if ($("#CPM_ATR_TOTAL-" + i).length) {
				$("#CPM_ATR_TOTAL-" + i).autoNumeric("init");
				t = $("#CPM_ATR_TOTAL-" + i).autoNumeric("get");
				omzet += parseFloat(t);
			}
		}
	}
	// console.log(omzet);
	var kurangLebih = 0;
	var masa_pajak_akhir = $("#CPM_ATR_BATAS_AKHIR").val();

	var bulans = masa_pajak_akhir.substring(3, 5);
	var tahuns = masa_pajak_akhir.substring(6, 10);

	var date = new Date(),
		bulan_sekarangs = date.getMonth() + 1,
		tahun_sekarangs = date.getFullYear();

	var selisih_bulan = selisihBulan(masa_pajak_akhir);
	//tambahan
	var masa_pajak_akhir2 = $("#CPM_ATR_BATAS_AWAL").val();
	var selisih_bulan2 = selisihBulan(masa_pajak_akhir2);

	var sanksi = $("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("get");
	$("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("set", sanksi);
	var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);

	$("input#CPM_DISCOUNT").autoNumeric("init", { vMax: "100" });
	$("#CPM_TOTAL_OMZET").autoNumeric("init");
	$("#CPM_TOTAL_PAJAK").autoNumeric("init");

	var persen_pengurangan = Number($("#CPM_DISCOUNT").autoNumeric("get"));

	total = eval(total) - (eval(total) * eval(persen_pengurangan)) / 100;
	total = Math.round(total);
	total = total.toFixed(0);

	$("#CPM_TOTAL_OMZET").autoNumeric("set", omzet);
	$("#CPM_TOTAL_PAJAK").autoNumeric("set", total);

	if (total !== 0)
		$("#terbilang").html(terbilang(Math.round(total)) + " Rupiah");
}

function hitung_total_ori() {
	var tarif = parseFloat($("#CPM_ATR_BIAYA").val());
	var omzet = parseFloat($("#CPM_ATR_TOTAL").autoNumeric("get"));
	// var omzet = 0;
	// console.log(omzet);
	var kurangLebih = 0;
	var count = parseInt($("#count").val());
	var t;
	if (count == 1) {
		get_hargadasar();
		return false;
	} else if (count > 1) {
		for (var i = 2; i <= count; i++) {
			if ($("#CPM_ATR_TOTAL-" + i).length) {
				$("#CPM_ATR_TOTAL-" + i).autoNumeric("init");
				t = $("#CPM_ATR_TOTAL-" + i).autoNumeric("get");
				omzet += parseFloat(t);
			}
		}
	}

	//tambahan
	var mystr = $('#CPM_MASA_PAJAK1').val();
	var myarr = mystr.split("/");
	var myvar = myarr[1];
	var myvar = parseInt(myvar, 10);
	console.log(typeof myvar);
	($('#CPM_MASA_PAJAK10').val(myvar));

	var kurangLebih = 0;
	var masa_pajak_akhir = $('#CPM_MASA_PAJAK2').val();
	var selisih_bulan = selisihBulan(masa_pajak_akhir);
	//tambahan
	var masa_pajak_akhir2 = $('#CPM_MASA_PAJAK1').val();
	var selisih_bulan2 = selisihBulan(masa_pajak_akhir2);
	var bulans = masa_pajak_akhir.substring(3, 5);
	var tahuns = masa_pajak_akhir.substring(6, 10);
	var date = new Date(),
		bulan_sekarangs = date.getMonth() + 1
		, tahun_sekarangs = date.getFullYear();
	// console.log(omzet);
	if (selisih_bulan > 24000 || selisih_bulan2 > 24000) {
		selisih_bulan = 0;
		selisih_bulan2 = 0;
	}
	// if(bulan)
	//tamabah if (selisih_bulan > 1)
	//console.log(selisih_bulan, selisih_bulan2);
	// alert(sanksi);

	// var sanksi = $("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("get");
	// $("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("set", sanksi);
	// var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);
	// console.log(sanksi);
	// var pengurangan = $('#CPM_TARIF_PENGURANGAN').autoNumeric('get');
	// $('#CPM_TARIF_PENGURANGAN').autoNumeric('set', pengurangan);
	// pengurangan = (pengurangan > 0) ? eval(pengurangan / 100) : 1;

	// var total = (eval(terutang) + eval(kurangLebih)) * pengurangan;
	// total = Math.ceil(total);

	// var kurangLebih = 0;

	// if (bulans < bulan_sekarangs || tahuns < tahun_sekarangs) {
	// 	if (selisih_bulan > 1) {
	// 		if ($('#editable_terlambat_lap').val() == 1) {
	// 			var sanksi = terutang * (selisih_bulan - 1) * 2 / 100;
	// 			var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
	// 		} else {
	// 			var sanksi = terutang * (selisih_bulan - 1) * 2 / 100;
	// 		}
	// 	} else {
	// 		var sanksi = 0;
	// 	}
	// } else {
	// 	if (selisih_bulan2 > 1) {
	// 		if ($('#editable_terlambat_lap').val() == 1) {
	// 			var sanksi = terutang * (selisih_bulan2 - 1) * 2 / 100;
	// 			var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
	// 		} else {
	// 			var sanksi = terutang * (selisih_bulan2 - 1) * 2 / 100;
	// 		}
	// 	} else {
	// 		var sanksi = 0;
	// 	}
	// }

	// $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);
	// var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
	// $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

	// var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
	var total = eval(terutang) + eval(kurangLebih);
	total = Math.ceil(total);

	$("input#CPM_DISCOUNT").autoNumeric("init", { vMax: "100" });
	$("#CPM_TOTAL_OMZET").autoNumeric("init");
	$("#CPM_TOTAL_PAJAK").autoNumeric("init");

	var persen_pengurangan = Number($("#CPM_DISCOUNT").autoNumeric("get"));
	total = eval(total) - (eval(total) * eval(persen_pengurangan)) / 100;
	total = Math.round(total);
	total = total.toFixed(0);

	$("#CPM_TOTAL_OMZET").autoNumeric("set", omzet);
	$("#CPM_TOTAL_PAJAK").autoNumeric("set", total);

	if (total !== 0)
		$("#terbilang").html(terbilang(Math.round(total)) + " Rupiah");
}

function selisihBulan(awal_bulan) {
	var bulan = awal_bulan.substring(3, 5);
	var tahun = awal_bulan.substring(6, 10);

	var date = new Date(),
		bulan_sekarang = date.getMonth() + 1,
		tahun_sekarang = date.getFullYear();

	var hasil = bulan_sekarang + 12 * (tahun_sekarang - tahun) + 1 - bulan;
	return hasil - 1;
}

function myFunction2() {
	$(document).ready(function () {
		setTimeout(function () {
			$("select.CPM_NOP").select2({
				escapeMarkup: function (markup) {
					var fd = markup.split(" | ");
					if (fd[1]) {
						fd[0] = fd[0].split(" - ");
						return "<b>[" + fd[0][0] + "]</b> [" + fd[0][1] + "]  " + fd[1];
					} else {
						return markup;
					}
				},
			});
			$("select.CPM_ATR_JALAN_TYPE").select2({
				escapeMarkup: function (markup) {
					var fd = markup.split(" | ");
					if (fd[1]) {
						fd[0] = fd[0].split(" - ");
						return "<b>[" + fd[0][0] + "]</b> [" + fd[0][1] + "]  " + fd[1];
					} else {
						return markup;
					}
				},
			});
		}, 800);
	});
}
