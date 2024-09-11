$(document).ready(function () {
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


	var getDecimals = 3;
    var getOptions = $.parseJSON('{"mDec": "' + getDecimals + '"}');
    //Then $(selector).autoNumeric('init', getOptions);

    $('.CPM_VOLUME_AIR').autoNumeric('init', getOptions);
    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    // $('#CPM_BAYAR_LAINNYA').autoNumeric('init');
    $('#CPM_DPP').autoNumeric('init');
    $('#CPM_TARIF_PAJAK').autoNumeric('init');
    $('#CPM_BAYAR_TERUTANG').autoNumeric('init');
    $('#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');
    $('#CPM_HARGA').autoNumeric('init');
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
            "PAJAK[CPM_VOLUME_AIR]": "required",
            "PAJAK[CPM_TOTAL_OMZET]": "required",
            "PAJAK[CPM_TOTAL_PAJAK]": "required",
            "PAJAK[CPM_GOL_AIRBAWAHTANAH]": "required",
            "PAJAK[CPM_HARGA]": "required",
            "PAJAK[CPM_TARIF_PAJAK]": "required",
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
            "PAJAK[CPM_VOLUME_AIR]": "harus diisi",
            "PAJAK[CPM_TOTAL_OMZET]": "harus diisi",
            "PAJAK[CPM_TOTAL_PAJAK]": "harus diisi",
            "PAJAK[CPM_GOL_AIRBAWAHTANAH]": "harus diisi",
            "PAJAK[CPM_HARGA]": "harus diisi",
            "PAJAK[CPM_TARIF_PAJAK]": "harus diisi",
            "PAJAK[CPM_MASA_PAJAK1]": "harus diisi",
        }
    });
    var function_sum2 = function (obj) {
        if ($(obj).attr('readonly') == 'readonly')
            return false;

        var harga = Number($('#CPM_HARGA').autoNumeric('get'));
        var vol = Number($('#CPM_VOLUME_AIR').autoNumeric('get'));

        var lokasi = Number($('#CPM_LOKASI_SUMBER_AIR').val());
        var kualitas = Number($('#CPM_KUALITAS_AIR').val());
        var tingkat_kerusakan = Number($('#CPM_TINGKAT_KERUSAKAN').val());

        var omzet = eval(harga) * eval(vol) * eval(lokasi) * eval(kualitas) * eval(tingkat_kerusakan);
        $('#CPM_TOTAL_OMZET').autoNumeric('set', omzet);
        function_sum(obj);
    }
    $('input.SUM2').keyup(function_sum2);
    $('select.SUM2').change(function_sum2);

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
        $("#CPM_GOL_AIRBAWAHTANAH option").prop('selected', false).attr('disabled', 'disabled');
        $('#CPM_TARIF_PAJAK').val(0);
        $('#CPM_HARGA').val(0);
    });

    $("#btn-search-npwpd").click(function () {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=1",
            url: "function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php",
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
                    $("#CPM_GOL_AIRBAWAHTANAH option[value='" + res.CPM_GOL_AIRBAWAHTANAH + "']").prop('selected', true).removeAttr('disabled');
                    $('#CPM_TARIF_PAJAK').val($("#CPM_GOL_AIRBAWAHTANAH option[value='" + res.CPM_GOL_AIRBAWAHTANAH + "']").attr('tarif'));
                    $('#CPM_HARGA').autoNumeric('set', $("#CPM_GOL_AIRBAWAHTANAH option[value='" + res.CPM_GOL_AIRBAWAHTANAH + "']").attr('harga'));
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

    $('#CPM_TYPE_MASA').change(function () {
        var type = parseInt($(this).val());
        var thn = $('#CPM_TAHUN_PAJAK').val();
        var bulan = ['', 'Januari', "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember"];
        if (type > 30) {
            if (type == 31) {
                var bln_awal = 1, bln_akhir = 3;
            } else if (type == 32) {
                var bln_awal = 4, bln_akhir = 6;
            } else if (type == 33) {
                var bln_awal = 7, bln_akhir = 9;
            } else if (type == 34) {
                var bln_awal = 10, bln_akhir = 12;
            }
            var tgl1 = '01/' + (eval(bln_awal) < 10 ? '0' + bln_awal : bln_awal) + '/' + thn;
            var tgl2 = new Date(thn, bln_akhir, 0).getDate();
            tgl2 = tgl2 + '/' + (eval(bln_akhir) < 10 ? '0' + bln_akhir : bln_akhir) + '/' + thn;
            $('#CPM_MASA_PAJAK').val(bln_awal);
            $('#CPM_MASA_PAJAK1').val(tgl1);
            $('#CPM_MASA_PAJAK2').val(tgl2);
            $('#CPM_ATR_BULAN-1').val(bln_awal);
            $('#CPM_ATR_BULAN-2').val(bln_awal + 1);
            $('#CPM_ATR_BULAN-3').val(bln_akhir);
            $("#bulan-perolehan-1").html(bulan[bln_awal]);
            $("#bulan-perolehan-2").html(bulan[bln_awal + 1]);
            $("#bulan-perolehan-3").html(bulan[bln_akhir]);
            $("#tahun-perolehan-1,#tahun-perolehan-2,#tahun-perolehan-3").html(thn);
            $('#item-perolehan-2,#item-perolehan-3').show();

            $('#CPM_ATR_VOLUME-2,#CPM_ATR_VOLUME-3,#CPM_ATR_VOLUME-1').val(0);
            $('#CPM_ATR_TOTAL-2,#CPM_ATR_TOTAL-3,#CPM_ATR_TOTAL-1').val(0);
            $('#CPM_ATR_PERHITUNGAN-2,#CPM_ATR_PERHITUNGAN-3,#CPM_ATR_PERHITUNGAN-1').val(0);

            // Tambahan
            $('#CPM_MASA_PAJAK').css('display', 'none');
            if (!$('#temp_CPM_MASA_PAJAK').length) {
                $('#CPM_MASA_PAJAK').after('<span id="temp_CPM_MASA_PAJAK">' + bulan[bln_awal] + '</span>');
            } else {
                $('#temp_CPM_MASA_PAJAK').html(bulan[bln_awal]);
            }
        } else {
            $('#CPM_ATR_BULAN-2,#CPM_ATR_BULAN-3').val(0);
            $('#item-perolehan-2,#item-perolehan-3').hide();
            // kosongkan inputan
            $('#CPM_ATR_VOLUME-2,#CPM_ATR_VOLUME-3,#CPM_ATR_VOLUME-1').val(0);
            $('#CPM_ATR_TOTAL-2,#CPM_ATR_TOTAL-3,#CPM_ATR_TOTAL-1').val(0);
            $('#CPM_ATR_PERHITUNGAN-2,#CPM_ATR_PERHITUNGAN-3,#CPM_ATR_PERHITUNGAN-1').val(0);
            $('#tabel_perolehan-2,#tabel_perolehan-3').html('');

            // Tambahan
            $('#CPM_MASA_PAJAK').css('display', '');
            $('#temp_CPM_MASA_PAJAK').remove();
        }

        $('#CPM_ATR_VOLUME-1, #CPM_ATR_VOLUME-2, #CPM_ATR_VOLUME-3').val(0);
        $('#tabel_perolehan-1,#tabel_perolehan-2,#tabel_perolehan-3').html('');
        $('#CPM_ATR_TOTAL-1,#CPM_ATR_TOTAL-2,#CPM_ATR_TOTAL-3').val(0);
        $('#CPM_ATR_PERHITUNGAN-1,#CPM_ATR_PERHITUNGAN-2,#CPM_ATR_PERHITUNGAN-3').val(0);

        $('#CPM_TOTAL_OMZET').val(0);
        $('#CPM_TARIF_PAJAK').val(0);
        $('#CPM_BAYAR_TERUTANG').val(0);
        $('#CPM_DPP').val(0);
        $('#CPM_DENDA_TERLAMBAT_LAP').val(0);
        $('#CPM_TOTAL_PAJAK').val(0);
        $("#CPM_TERBILANG").val(0);

        function_sum();
    });

});

function setTarif(obj) {
    // console.log($('option:selected', obj).attr('tarif'))
    $('#CPM_HARGA').val($('option:selected', obj).attr('harga'));
    $('#CPM_TARIF_PAJAK').val($('option:selected', obj).attr('tarif'));
}

function function_sum(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    //console.log(typeof myvar);
    ($('#CPM_MASA_PAJAK10').val(myvar));

    var total = Number($(CPM_TOTAL_PAJAK).autoNumeric('get'));
    total = Math.ceil(total);


    $.ajax({
        type: "POST",
        data: "num=" + total,
        url: "function/PATDA-V1/svc-terbilang.php",
        success: function (res) {
            $("#CPM_TERBILANG").val(res);
        }
    })
}

function selisihBulan(awal_bulan) {

    var bulan = awal_bulan.substring(3, 5);
    var tahun = awal_bulan.substring(6, 10);

    var date = new Date(),
        bulan_sekarang = date.getMonth() + 1
        , tahun_sekarang = date.getFullYear();

    var hasil = (bulan_sekarang + (12 * (tahun_sekarang - tahun)) + 1) - bulan;
    return hasil - 1;
    // var hasil = (bulan_sekarang + (12 * (tahun_sekarang-tahun))+1)-bulan;
    // return hasil-1;
}
function hitungNPA() {
    function_sum();
}
