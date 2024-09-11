$(document).ready(function () {
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

    $('input#CPM_ATR_LUAS_GEDUNG').autoNumeric('init');
    $('input#CPM_ATR_JUMLAH_TITIK').autoNumeric('init');
    $('input#CPM_ATR_JUMLAH_KG').autoNumeric('init');
    $('#CPM_MASA_PAJAK1').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."
    });
    $('#CPM_MASA_PAJAK2').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."
    });

    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    $('#CPM_BAYAR_LAINNYA').autoNumeric('init');
    $('#CPM_DPP').autoNumeric('init');
    $('#CPM_TARIF_PAJAK').autoNumeric('init');
    $('#CPM_BAYAR_TERUTANG').autoNumeric('init');
    $('#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('#CPM_HARGA_DASAR').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');

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
            "PAJAK[CPM_MASA_PAJAK1]": "required",
            "PAJAK[CPM_MASA_PAJAK2]": "required",
            "PAJAK[CPM_GOL_WALET]": "required",
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
            "PAJAK[CPM_MASA_PAJAK1]": "harus diisi",
            "PAJAK[CPM_MASA_PAJAK2]": "harus diisi",
            "PAJAK[CPM_GOL_WALET]": "harus diisi",
        }
    });

    $('input.SUM').keyup(function_sum);
    $('input.SUM2').keyup(function_sum);

    var function_jumlah = function (obj) {
        if ($(obj).attr('readonly') == 'readonly')
            return false;

        var jumlah = 0;
        $('.JUMLAH').each(function () {
            jumlah += eval(Number($(this).autoNumeric('get')));
        });
        var harga_dasar = Number($('#CPM_HARGA_DASAR').autoNumeric('get'));
        var omzet = eval(jumlah) * eval(harga_dasar);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', omzet);
        function_sum();

    }
    $('input.JUMLAH').keyup(function_jumlah);

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
        $("#CPM_GOL_WALET option").prop('selected', false).attr('disabled', 'disabled');
        $('#CPM_TARIF_PAJAK').val(0);
    });

    $("#btn-search-npwpd").click(function () {
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=9",
            url: "function/PATDA-V1/walet/lapor/svc-lapor.php",
            dataType: "json",
            success: function (res) {
                if (res.result == 1) {
                    $("#CPM_ID_PROFIL").val(res.CPM_ID);
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NOP").val(res.CPM_NOP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                    $("#CPM_GOL_WALET option[value='" + res.CPM_GOL_WALET + "']").prop('selected', true).removeAttr('disabled');
                    $('#CPM_TARIF_PAJAK').val($("#CPM_GOL_WALET option[value='" + res.CPM_GOL_WALET + "']").attr('tarif'));
                    $('#CPM_HARGA_DASAR').autoNumeric('set', $("#CPM_GOL_WALET option[value='" + res.CPM_GOL_WALET + "']").attr('harga'));
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
            $('.ui-datepicker-trigger').hide();
        } else {
            $('#CPM_MASA_PAJAK option').removeAttr('selected');
            $('#CPM_MASA_PAJAK option').attr('disabled', 'disabled');
            $('#CPM_MASA_PAJAK option').first().attr('selected', 'selected')
            $('.ui-datepicker-trigger').show();
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

function function_sum(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
    var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK').autoNumeric('get'));
    var dpp = eval(omzet) + eval(lain);
    if ($(obj).attr('id') == 'CPM_BAYAR_TERUTANG') {
        var terutang = Number($(obj).autoNumeric('get'));
    } else {
        var terutang = dpp * tarif / 100;
        $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
    }
    $('#CPM_DPP').autoNumeric('set', dpp);

    var kurangLebih = 0;
    var masa_pajak_akhir = $('#CPM_MASA_PAJAK2').val();
    var masa_pajak_awal = $('#CPM_MASA_PAJAK1').val();
    var selisih_bulan = selisihBulan(masa_pajak_akhir);
    var selisih_bulan2 = selisihBulan(masa_pajak_akhir);



    if (selisih_bulan2 > 0) {
        sanksi = 100000;
    } else {
        sanksi = 0;
    }

    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

    var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
    total = Math.round(total);

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
function function_sum2(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
    var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK').autoNumeric('get'));
    var dpp = eval(omzet) + eval(lain);
    if ($(obj).attr('id') == 'CPM_BAYAR_TERUTANG') {
        var terutang = Number($(obj).autoNumeric('get'));
    } else {
        var terutang = dpp * tarif / 100;
        $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
    }
    $('#CPM_DPP').autoNumeric('set', dpp);

    var kurangLebih = 0;
    var masa_pajak_akhir = $('#CPM_MASA_PAJAK2').val();
    var masa_pajak_awal = $('#CPM_MASA_PAJAK1').val();
    var selisih_bulan = selisihBulan(masa_pajak_akhir);
    var selisih_bulan2 = selisihBulan(masa_pajak_akhir);



    // if (selisih_bulan2 > 0) {
    //     sanksi = 100000;
    // } else {
    //     sanksi = 0;
    // }

    // $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);
    var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

    var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
    total = Math.round(total);

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


// function selisihBulan(awal_bulan){

//     var bulan = awal_bulan.substring(3, 5);
//     var tahun = awal_bulan.substring(6, 10);

//     var date = new Date(),
//     bulan_sekarang = date.getMonth()+1
//     ,tahun_sekarang = date.getFullYear();

//     var hasil = (bulan_sekarang + (12 * (tahun_sekarang-tahun))+1)-bulan;
//     return hasil-1;
// }

function selisihBulan(awal_bulan) {
    var hari = parseInt(awal_bulan.substring(0, 2), 10); // Misalnya '01' dari '01-04-2020'
    var bulan = parseInt(awal_bulan.substring(3, 5), 10); // Misalnya '04' dari '01-04-2020'
    var tahun = parseInt(awal_bulan.substring(6, 10), 10); // Misalnya '2020' dari '01-04-2020'
    // console.log(hari);
    // Mendapatkan bulan dan tahun saat ini
    var date = new Date();
    var hari_ini = date.getDate();
    var bulan_sekarang = date.getMonth() + 1; // Bulan saat ini (getMonth() dimulai dari 0)
    var tahun_sekarang = date.getFullYear(); // Tahun saat ini

    // Menghitung selisih bulan
    var hasil = (bulan_sekarang + (12 * (tahun_sekarang - tahun)) + 1) - bulan;

    // Jika tanggal lebih dari 10, tambahkan 1 ke hasil
    if (hari_ini > 10) {
        hasil += 1;
    }

    // Mengembalikan hasil akhir setelah dikurangi 1
    return hasil - 1;
}