$(document).ready(function () {
    $(".btn-tambah").click(function () {
        var no = parseInt($("#count").val()),
            npwpd = $("#CPM_NPWPD").val(),
            type_masa = $("#CPM_ATR_TYPE_MASA").val(),
            waktu =
                $("#CPM_MASA_PAJAK").val() +
                " " +
                $("#CPM_ATR_TYPE_MASA option:selected").text(),
            nop = $("#CPM_NOP").val(),
            rek2 = $("#CPM_REKENING").val(),

            awal = $("#CPM_ATR_BATAS_AWAL").val(),
            akhir = $("#CPM_ATR_BATAS_AKHIR").val(),
            pajak = $("#CPM_ATR_BIAYA").val(),
            om = $("#CPM_TOTAL_OMZET").val(),
            rek = $("#CPM_ATR_REKENING").val();
          //  $('#CPM_TOTAL_OMZET-'+no).autoNumeric('init');
        // alert(rek2);
        if (om == "") {
            alert("Silahkan isi Pembayaran Pemakaian Objek Pajak!");
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
                nop: nop,
                rek2: rek2,
                type_masa: type_masa,
                waktu: waktu,
                tarif: pajak,
                function: "addRow",
            },
            url: "view/PATDA-V1/jalan/svc-jalan.php",
            async: false,

            success: function (res) {
                //  alert(url);
                $(".atr_reklame").append(res);
                $("#count").val(no + 1);
                $("#CPM_TOTAL_KWH-" + (no + 1)).autoNumeric("init");
                $("#CPM_TOTAL_OMZET-" + (no + 1)).autoNumeric("init");
                $("#CPM_DPP-" + (no + 1)).autoNumeric("init");
               // -2
                $("#CPM_ATR_JUMLAH-" + (no + 1)).autoNumeric("init");
                $("#CPM_ATR_TOTAL-" + (no + 1)).autoNumeric("init");
            },

        });
        // console.log('sjo');
    });
    // var no = parseInt($("#count").val())
    // console.log(no);
    
   

    // NEW
    $('input[type="checkbox"]#HITUNG_DARI_KETETAPAN').on('change', function () {
        var v = $(this);
        var radNonDpp = $('input[type="radio"][value="Non DPP"].CPM_METODE_HITUNG');
        var radDpp = $('input[type="radio"][value="DPP"].CPM_METODE_HITUNG');
        var totalOmzet = $('#CPM_TOTAL_OMZET');
        var terhutang = $('#CPM_BAYAR_TERUTANG');

        if (v.prop('checked')) {
            terhutang.prop('readonly', false);
            totalOmzet.prop('readonly', true);
            radNonDpp.prop('checked', true);
            radDpp.parent().hide();
        } else {
            terhutang.prop('readonly', true);
            totalOmzet.prop('readonly', false);
            radNonDpp.prop('checked', true);
            radDpp.parent().show();
        }

        if (!Number(terhutang.autoNumeric('get'))) {
            terhutang.autoNumeric('set', 0);
        }

        $('.CPM_METODE_HITUNG').trigger('change');

    });

    $('#CPM_BAYAR_TERUTANG').on('input', function (e) {
        var v = $(this);

        if (v.prop('readonly')) {
            return;
        }

        var ketetapan = Number(v.autoNumeric('get'));
        var tarif_pajak = Number($('#CPM_TARIF_PAJAK').val());
        var rumus = (tarif_pajak / 100);

        var input_omzet = $('#CPM_TOTAL_OMZET');

        if (rumus == 0) {
            input_omzet.autoNumeric('set', ketetapan);
        } else {
            input_omzet.autoNumeric('set', (ketetapan / rumus));
        }
        function_sum(this);
    })


    // NEW -- ENDS

    $('input:reset').click(function () {
        $('select#CPM_NPWPD').html('').trigger('change');
    });

    $("select#CPM_NOP").select2({
        escapeMarkup: function (markup) {
            var fd = markup.split(' | ');
            if (fd[1]) {
                fd[0] = fd[0].split(' - ');
                return '<b>[' + fd[0][0] + ']</b> [' + fd[0][1] + ']  ' + fd[1];
            } else {
                return markup;
            }
        }
    });

    $('#CPM_PEMBANGKIT').autoNumeric('init');
    $('#CPM_FAKTOR_DAYA').autoNumeric('init');
    $('#CPM_SATUAN').autoNumeric('init');

    $('#CPM_TOTAL_KVA').autoNumeric('init');
    $('#CPM_NYALA').autoNumeric('init');

    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    $('#CPM_ATR_TOTAL').autoNumeric('init');
    $('#CPM_BAYAR_LAINNYA').autoNumeric('init');
    $('#CPM_TOTAL_KWH').autoNumeric('init');
    // $('#CPM_TOTAL_KWH2').autoNumeric('init');
    $('#CPM_HARGA_DASAR').autoNumeric('init');
    $('#CPM_DPP').autoNumeric('init');
    $('#CPM_TARIF_PAJAK').autoNumeric('init');
    $('#CPM_BAYAR_TERUTANG').autoNumeric('init');
    $('#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');
    $('#CPM_MASA_PAJAK1').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."
    }).next('.ui-datepicker-trigger').attr('id', 'CPM_DATEPICKER');
    $('#CPM_MASA_PAJAK2').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."
    }).next('.ui-datepicker-trigger').attr('id', 'CPM_DATEPICKER1');

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
            "PAJAK[CPM_TOTAL_PAJAK]": "required",
            "PAJAK[CPM_GOL_JALAN]": "required",
            "PAJAK[CPM_MASA_PAJAK1]": "required",
        },
        messages: {
            "PAJAK[CPM_NO]": "harus diisi",
            "PAJAK[CPM_NPWPD]": "harus diisi",
            "PAJAK[CPM_NAMA_WP]": "harus diisi",
            "PAJAK[CPM_ALAMAT_WP]": "harus diisi",
            "PAJAK[CPM_NOP]": "harus diisi",
            "PAJAK[CPM_NAMA_OP]": "harus diisi",
            "PAJAK[CPM_ALAMAT_OP]": "harus diisi",
            "PAJAK[CPM_TOTAL_OMZET]": "harus diisi",
            "PAJAK[CPM_TOTAL_PAJAK]": "harus diisi",
            "PAJAK[CPM_GOL_JALAN]": "harus diisi",
            "PAJAK[CPM_MASA_PAJAK1]": "harus diisi",
        }
    });

    $('input.SUM').keyup(function_sum);

    $('#CPM_TRAN_INFO').removeClass('required');
    $('input.AUTHORITY').change(function () {
        if ($(this).val() == 1) {
            $('#CPM_TRAN_INFO').removeClass('required');
            $('#CPM_TRAN_INFO').attr('readonly', 'readonly');
            $('#CPM_TRAN_INFO').val('');
        } else {
            $('#CPM_TRAN_INFO').addClass('required');
            $('#CPM_TRAN_INFO').removeAttr('readonly');
        }
    })

    $("input.btn-submit").click(function () {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);

        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan laporan ini?");
            }
        } else if (action == "save_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan dan memfinalkan laporan ini?");
            }
        } else if (action == "update_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk memperbaharui dan memfinalkan laporan ini?");
            }
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk merubah laporan ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus laporan ini?");
        } else if (action == "verifikasi" || action == "persetujuan") {
            res = confirm("Apakah anda yakin untuk menyetujui / menolak laporan ini?");
        } else if (action == "new_version") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk membuat versi baru laporan ini?");
            }
        } else if (action == "new_version_final") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk membuat versi baru dan memfinalkan laporan ini?");
            }
        }
        if (res) {
            document.getElementById("form-lapor").submit();
        }
    });

    $("input.btn-print").click(function () {
        var action = $(this).attr('action');
        $('#function').val(action);
        $("#form-lapor").attr('target', '_blank');
        document.getElementById("form-lapor").submit();
    });

    /* FUNGSI PADA INPUT DI PELAYANAN*/
    $("#CPM_NPWPD").keyup(function () {
        if ($(this).attr('readonly') == 'readonly')
            return false;
        $("#CPM_ID_PROFIL").val('');
        $("#CPM_NAMA_WP").val('');
        $("#CPM_ALAMAT_WP").val('');
        $("#CPM_NOP").val('');
        $("#CPM_NAMA_OP").val('');
        $("#CPM_ALAMAT_OP").val('');
        $("#CPM_GOL_JALAN option").prop('selected', false).attr('disabled', 'disabled');
        $('#CPM_TARIF_PAJAK').val(0);
    });

    $("#CPM_PEMBANGKIT").keyup(function () {
        var pembangkit = Number($('#CPM_PEMBANGKIT').autoNumeric('get'));
        var faktor_daya = Number($('#CPM_FAKTOR_DAYA').autoNumeric('get'));
        var satuan = Number($('#CPM_SATUAN').autoNumeric('get'));
        var kwh = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
        var total_pemakaian = eval(pembangkit) * eval(faktor_daya) * eval(satuan) * eval(kwh);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
        function_sum();
    });

    $("#CPM_FAKTOR_DAYA").keyup(function () {
        var pembangkit = Number($('#CPM_PEMBANGKIT').autoNumeric('get'));
        var faktor_daya = Number($('#CPM_FAKTOR_DAYA').autoNumeric('get'));
        var satuan = Number($('#CPM_SATUAN').autoNumeric('get'));
        var kwh = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
        var total_pemakaian = eval(pembangkit) * eval(faktor_daya) * eval(satuan) * eval(kwh);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
        function_sum();
    });


    $("#CPM_SATUAN").keyup(function () {
        var pembangkit = Number($('#CPM_PEMBANGKIT').autoNumeric('get'));
        var faktor_daya = Number($('#CPM_FAKTOR_DAYA').autoNumeric('get'));
        var satuan = Number($('#CPM_SATUAN').autoNumeric('get'));
        var kwh = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
        var total_pemakaian = eval(pembangkit) * eval(faktor_daya) * eval(satuan) * eval(kwh);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
        function_sum();
    });

    $("#CPM_TOTAL_KWH").keyup(function () {
        var pembangkit = Number($('#CPM_PEMBANGKIT').autoNumeric('get'));
        var faktor_daya = Number($('#CPM_FAKTOR_DAYA').autoNumeric('get'));
        var satuan = Number($('#CPM_SATUAN').autoNumeric('get'));
        var kwh = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
        var total_pemakaian = eval(pembangkit) * eval(faktor_daya) * eval(satuan) * eval(kwh);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
        function_sum();
    });

    // $("#CPM_TOTAL_KWH").keyup(function () {
    //     var kwh = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
    //     var harga = Number($('#CPM_HARGA_DASAR').autoNumeric('get'));
    //     var omzet = eval(kwh) * eval(harga);
    //     $('#CPM_TOTAL_OMZET').autoNumeric('set', omzet);
    //     function_sum();
    // });

    // $("#CPM_TOTAL_KVA").keyup(function () {
    //     var totalKva = Number($('#CPM_TOTAL_KVA').autoNumeric('get'));
    //     var pemakaian = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
    //     var nyala = Number($('#CPM_NYALA').autoNumeric('get'));
    //     var total_pemakaian = eval(totalKva) * eval(pemakaian) * eval(nyala);
    //     $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
    //     function_sum();
    // });

    // $("#CPM_TOTAL_KWH").keyup(function () {
    //     var totalKva = Number($('#CPM_TOTAL_KVA').autoNumeric('get'));
    //     var pemakaian = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
    //     var nyala = Number($('#CPM_NYALA').autoNumeric('get'));
    //     var total_pemakaian = eval(totalKva) * eval(pemakaian) * eval(nyala);
    //     $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
    //     function_sum();
    // });

    // $("#CPM_NYALA").keyup(function () {
    //     var totalWbp = 4;
    //     var lwbpFactor = 1.4;
    //     var totalLwbp = totalWbp * lwbpFactor;

    //     var totalKva = Number($('#CPM_TOTAL_KVA').autoNumeric('get'))
    //     var pemakaian = Number($('#CPM_TOTAL_KWH').autoNumeric('get'));
    //     var omzet = (pemakaian / (totalKva * totalWbp)) + (pemakaian / (totalKva * totalLwbp));; 
    //     console.log(omset);
    //     var total_pemakaian = eval(totalKva) * eval(pemakaian) * eval(omzet);
    //     $('#CPM_TOTAL_OMZET').autoNumeric('set', total_pemakaian);
    //     function_sum();
    // });

    $("#btn-search-npwpd").click(function () {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=6",
            url: "function/PATDA-V1/jalan/lapor/svc-lapor.php",
            dataType: "json",
            success: function (res) {
                $('#load-search-npwpd').html("");
                if (res.result == 1) {
                    $("#CPM_ID_PROFIL").val(res.CPM_ID);
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NOP").val(res.CPM_NOP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                    $("#CPM_GOL_JALAN option[value='" + res.CPM_GOL_JALAN + "']").prop('selected', true).removeAttr('disabled');
                    $('#CPM_TARIF_PAJAK').val($("#CPM_GOL_JALAN option[value='" + res.CPM_GOL_JALAN + "']").attr('tarif'));
                    $('#CPM_HARGA_DASAR').autoNumeric('set', $("#CPM_GOL_JALAN option[value='" + res.CPM_GOL_JALAN + "']").attr('harga'));
                    function_sum();
                } else {
                    alert("NPWPD tidak ditemukan!")
                }
            },
            error: function (res) {
                console.log(res)
            }
        })
    });

    var setTipePajak = function (tipe) {
        if (tipe == 1) {
            $('#CPM_MASA_PAJAK option').removeAttr('disabled');
            $('#CPM_DATEPICKER' ).hide();
            $('#CPM_DATEPICKER1').hide();
            //tambahan aan

        } else {
            $('#CPM_MASA_PAJAK option').removeAttr('selected');
            $('#CPM_MASA_PAJAK option').attr('disabled', 'disabled');
            $('#CPM_MASA_PAJAK option').first().attr('selected', 'selected')
            $('#CPM_DATEPICKER' ).show();
            $('#CPM_DATEPICKER1').show();
            
            //tambahan aan

            $('#CPM_MASA_PAJAK1').change(function_sum)
            $('#CPM_MASA_PAJAK2').change(function_sum)
        }
        
    }
    setTipePajak($('#CPM_TIPE_PAJAK').val());
    $('#CPM_TIPE_PAJAK').change(function () {
        var tipe = $(this).val();
        $('#CPM_MASA_PAJAK1').val('');
        $('#CPM_MASA_PAJAK2').val('');
        setTipePajak(tipe);
        function_sum(this);
    });



});

function selisihBulan(awal_bulan) {

    var bulan = awal_bulan.substring(3, 5);
    var tahun = awal_bulan.substring(6, 10);

    var date = new Date(),
        bulan_sekarang = date.getMonth() + 1
        , tahun_sekarang = date.getFullYear();

    var hasil = (bulan_sekarang + (12 * (tahun_sekarang - tahun)) + 1) - bulan;
    return hasil - 1;
}

function function_sum(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;
    //console.log(obj);
    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    var count = parseInt($("#count").val());
    ($('#CPM_MASA_PAJAK10').val(myvar));
    // alert(count);
    var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
    var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK').val());
    var dpp = eval(omzet) + eval(lain);
    if ($(obj).attr('id') == 'CPM_BAYAR_TERUTANG') {
        var terutang = Number($(obj).autoNumeric('get'));
    } else if(count == 1){
        var terutang = dpp * tarif / 100;
        $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
        $('#CPM_ATR_TOTAL').autoNumeric('set', terutang);
    }else if (count > 1) {
        var jum =0;
        for (var i = 2; i <= count; i++) {
           // console.log($("#CPM_TOTAL_OMZET-" + i));
          if ($("#CPM_ATR_TOTAL-" + i).length) {
            $("#CPM_ATR_TOTAL-" + i).autoNumeric("init");
            t = $("#CPM_ATR_TOTAL-" + i).autoNumeric("get");
            // a = $('#CPM_TARIF_PAJAK-' + i).val();
          
            jum += parseFloat(t);
          //  console.log(jum);
          }
         // console.log(omzet);
          //console.log(tarif);
        }
      }
    $('#CPM_DPP').autoNumeric('set', dpp);

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

    if (selisih_bulan > 24000 || selisih_bulan2 > 24000) {
        selisih_bulan = 0;
        selisih_bulan2 = 0;
    }

    // var today = new Date();
    // var day = String(today.getDate()).padStart(2, '0');

    // if (selisih_bulan > 0) {
    //     if (day <= 20) {
    //         selisih_bulan = selisih_bulan - 1;
    //     }
    // }



    var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

    var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
    total = Math.ceil(total);


    $('#CPM_TOTAL_PAJAK').autoNumeric('set', total);
    $.ajax({
        type: "POST",
        data: "num=" + total,
        url: "function/PATDA-V1/svc-terbilang.php",
        success: function (res) {
            $("#CPM_TERBILANG").val(res);
        }
    })
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
        }, 800);
    });
}
function hitungaku() {
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
	// var date = new Date(),
	// 	bulan_sekarang = date.getMonth() + 1
  
	var selisih_bulan = selisihBulan(masa_pajak_akhir);
	//tambahan
	var masa_pajak_akhir2 = $("#CPM_ATR_BATAS_AWAL").val();
	var selisih_bulan2 = selisihBulan(masa_pajak_akhir2);
	// if(bulan)
	//tamabah if (selisih_bulan > 1)
	//console.log(selisih_bulan, selisih_bulan2);
	// alert(sanksi);
  
	var sanksi = $("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("get");
	$("#CPM_DENDA_TERLAMBAT_LAP").autoNumeric("set", sanksi);
	var total = eval(omzet) + eval(kurangLebih) + eval(sanksi);
	// console.log(sanksi);
  
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


function rumusPerhitungan(no) {
//   var a=  $('#CPM_MASA_PAJAK-'+no);
//   console.log(a);

$('#CPM_TOTAL_OMZET-'+ no ).keyup(function () {
    var value = $(this).val();
    //satu
   //var tes= $('#CPM_TOTAL_PAJAK').autoNumeric('get');
    var omzet = Number($('#CPM_TOTAL_OMZET' ).autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK' ).val());
    var atrTotal = Number($('#CPM_ATR_TOTAL' ).val());
     // var lain = Number($('#CPM_BAYAR_LAINNYA-'+ no ).autoNumeric('get'));
     var cleanedValue = value.replace(/,/g, '').replace(/\./g, '');
     var dpp = Number(cleanedValue);
     //console.log(dpp);
    var dpp1 = eval(omzet);
 
    if (no > 1) {
       // var jum =tot;
        //
        for (var i = 2; i <= no; i++) {
           // console.log($("#CPM_TOTAL_OMZET-" + i));
         //  alert(no);
          if ($("#CPM_ATR_TOTAL-" + i).length) {
             $("#CPM_ATR_TOTAL-" + i).autoNumeric("init");
            t = $("#CPM_ATR_TOTAL-" + i).autoNumeric("get");
            // a = $('#CPM_TARIF_PAJAK-' + i).val();
         
            atrTotal += parseFloat(t);
          
          }
      
        }
      }
 
    
    $('#CPM_DPP-'+ no).val(value);
    $('#CPM_DPP').val(omzet);
    
    var terutang = dpp * tarif / 100;
    var a=terutang+atrTotal;
    var total= a;
    total=Math.ceil(total)
    console.log('iani adalah jumlah : '+ atrTotal);
    console.log(a);
    $('#CPM_ATR_TOTAL-'+no).autoNumeric('set', terutang+atrTotal); 
    $('#CPM_BAYAR_TERUTANG').autoNumeric('set',atrTotal); 
    $('#CPM_TOTAL_PAJAK').autoNumeric('set', Math.ceil(tot+a));  
    // $('#CPM_TOTAL_PAJAK').autoNumeric('set', total);
    $.ajax({
        type: "POST",
        data: "num=" + total,
        url: "function/PATDA-V1/svc-terbilang.php",
        success: function (res) {
            $("#CPM_TERBILANG").val(res);
        }
    })
   
})

$('#CPM_MASA_PAJAK1-'+no).datepicker({
    dateFormat: 'dd/mm/yy',
    changeYear: true,
    showOn: "button",
    buttonImageOnly: false,
    buttonText: "..."
}).next('.ui-datepicker-trigger').attr('id', 'CPM_DATEPICKER1-' + no);
$('#CPM_MASA_PAJAK2-'+no).datepicker({
    dateFormat: 'dd/mm/yy',
    changeYear: true,
    showOn: "button",
    buttonImageOnly: false,
    buttonText: "..."
}).next('.ui-datepicker-trigger').attr('id', 'CPM_DATEPICKER-' + no);
    var setTipePajaks = function (tipex) {
        if (tipex == 1) {
            $('#CPM_MASA_PAJAK-'+ no ).removeAttr('disabled');
            $('#CPM_DATEPICKER-' + no).hide();
            $('#CPM_DATEPICKER1-' + no).hide();
            //tambahan aan

        } else {
            $('#CPM_MASA_PAJAK-'+no ).removeAttr('selected');
            $('#CPM_MASA_PAJAK-'+no ).attr('disabled', 'disabled');
            $('#CPM_MASA_PAJAK-'+no ).first().attr('selected', 'selected')
            $('#CPM_DATEPICKER-' + no).show();
            $('#CPM_DATEPICKER1-' + no).show();
            
            //tambahan aan

            $('#CPM_MASA_PAJAK1-'+no ).change(function_sum);
            $('#CPM_MASA_PAJAK2-'+no ).change(function_sum);
        }
    }
    // alert(no);
    setTipePajaks($('#CPM_TIPE_PAJAK-'+ no).val());
    $('#CPM_TIPE_PAJAK-'+ no).change(function () {
        var tipex = $(this).val();
        // console.log( $('#CPM_TIPE_PAJAK-'+ no));
        $('#CPM_MASA_PAJAK1-'+ no).val('');
        $('#CPM_MASA_PAJAK2-'+ no).val('');
        setTipePajaks(tipex);
      //  function_sum(this);
    });
    $('#CPM_TAHUN_PAJAK-' + no).change(function() {
        // Tindakan yang ingin Anda jalankan saat perubahan terjadi
        var selectedYear = $(this).val();
       $('CPM_MASA_PAJAK1-'+no).val(selectedYear)
         // Mendapatkan nilai yang dipilih
        // Lakukan tindakan lainnya
    });
   // console.log('oke');
   // console.log( $('#CPM_MASA_PAJAK-'+ no +', #CPM_TAHUN_PAJAK-'+ no))
    $('#CPM_MASA_PAJAK-'+no + ', #CPM_TAHUN_PAJAK-'+no).change(function() {
       // console.log( $('#CPM_MASA_PAJAK1-'+no));
        if ($('#CPM_TIPE_PAJAK-'+ no).val() === 2) return false;
        var bln = $('#CPM_MASA_PAJAK-'+no).val();
        //console.log(bln);
        if (bln == "") {
            $('#CPM_MASA_PAJAK1-'+no).val('');
            $('#CPM_MASA_PAJAK2-'+no).val('');
            return false;
        }
      
        var thn = $('#CPM_TAHUN_PAJAK-'+no).val();
       
        var tgl = new Date(thn, bln, 0).getDate();
        //alert(bln);
        bln = (eval(bln) < 10) ? '0' + bln : bln;
//console.log(bln);
        $('#CPM_MASA_PAJAK1-'+no).val('01/' + bln + '/' + thn);
        $('#CPM_MASA_PAJAK2-'+no).val(tgl + '/' + bln + '/' + thn);
        
        if(typeof function_sum === "function"){
			function_sum(this);
		}    
    });
 }