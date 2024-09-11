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
    $("#modalDialog").dialog({
        autoOpen: false,
        modal: true,
        width: "900",
        resizable: false,
        draggable: false,
        height: 'auto',
        title: "",
        position: ['middle', 50]
    });

    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    $('#CPM_BAYAR_LAINNYA').autoNumeric('init');
    $('#CPM_DPP').autoNumeric('init');
    $('#CPM_TARIF_PAJAK').autoNumeric('init');
    $('#CPM_BAYAR_TERUTANG').autoNumeric('init');
    $('#CPM_TOTAL_PAJAK').autoNumeric('init');
    $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('init');
    $('#CPM_MASA_PAJAK1').datepicker({
        dateFormat: 'dd/mm/yy',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."
    });
    $('#CPM_MASA_PAJAK2').datepicker({
        dateFormat: 'dd/mm/yy',
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
            "PAJAK[CPM_TOTAL_OMZET]": "required",
            "PAJAK[CPM_TOTAL_PAJAK]": "required",
            "PAJAK[CPM_GOL_HOTEL]": "required",
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
            "PAJAK[CPM_GOL_HOTEL]": "harus diisi",
            "PAJAK[CPM_MASA_PAJAK1]": "harus diisi",
        }
    });

    var function_getval_tapbox = function () {
        return false;
        if ($('#CPM_TOTAL_OMZET').attr('readonly') == 'readonly')
            return false;

        var npwpd = $('#CPM_NPWPD').val();
        var nop = $('#CPM_NOP').val();
        var thn = $('#CPM_TAHUN_PAJAK').val();
        var bln = $('#CPM_MASA_PAJAK').val();
        var id = $('#CPM_DEVICE_ID').val();
        var a = $('#a').val();
        $('#val_tapbox').html("<img src='image/large-loading.gif' style='width:10px;'>");
        $.ajax({
            type: "POST",
            data: "a=" + a + "&PAJAK[CPM_NPWPD]=" + npwpd + "&PAJAK[CPM_NOP]=" + nop + "&PAJAK[CPM_TAHUN_PAJAK]=" + thn + "&PAJAK[CPM_MASA_PAJAK]=" + bln + "&id=" + id + "&function=get_val_tapbox",
            url: "function/PATDA-V1/hotel/lapor/svc-lapor.php",
            dataType: "json",
            success: function (res) {
                if ($('#CPM_NO').length) {
                    $('#val_tapbox').html(res.formated_amount + ', ' + res.link);
                } else {
                    $('#val_tapbox').html(res.link)
                    // $('#CPM_TOTAL_OMZET').autoNumeric('set',res.amount);
                }

                $('#CPM_BAYAR_LAINNYA').trigger('keyup');
            },
            error: function (res) {
                console.log(res)
            }
        });
    }
    $('#CPM_TAHUN_PAJAK,#CPM_MASA_PAJAK').change(function_getval_tapbox)
    function_getval_tapbox();

    $('input.SUM').keyup(function_sum);
    $('input.SUM2').keyup(function_sum2);

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
        $("#CPM_DEVICE_ID").val('');
        $("#CPM_ID_PROFIL").val('');
        $("#CPM_NAMA_WP").val('');
        $("#CPM_ALAMAT_WP").val('');
        $("#CPM_NOP").val('');
        $("#CPM_NAMA_OP").val('');
        $("#CPM_ALAMAT_OP").val('');
        $("#CPM_GOL_HOTEL").val('');
        $('#val_tapbox').html('Rp. 0');
        $("#CPM_GOL_HOTEL option").prop('selected', false).attr('disabled', 'disabled');
        $('#CPM_TARIF_PAJAK').val(0);
    });

    $("#btn-search-npwpd").click(function () {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=3",
            url: "function/PATDA-V1/hotel/lapor/svc-lapor.php",
            dataType: "json",
            success: function (res) {
                $('#load-search-npwpd').html("");
                if (res.result == 1) {
                    $("#modalDialog").val(res.CPM_DEVICE_ID_ORI);
                    $("#CPM_DEVICE_ID").val(res.CPM_DEVICE_ID);
                    $("#CPM_ID_PROFIL").val(res.CPM_ID);
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NOP").val(res.CPM_NOP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                    $("#CPM_GOL_HOTEL option[value='" + res.CPM_GOL_HOTEL + "']").prop('selected', true).removeAttr('disabled');
                    $('#CPM_TARIF_PAJAK').val($("#CPM_GOL_HOTEL option[value='" + res.CPM_GOL_HOTEL + "']").attr('tarif'));
                    function_getval_tapbox();
                } else {
                    alert("NPWPD tidak ditemukan!")
                }
            },
            error: function (res) {
                console.log(res)
            }
        })
    });

    $(".CPM_METODE_HITUNG").change(function_sum);

    var setTipePajak = function (tipe) {
        if (tipe == 1) {
            $('#CPM_MASA_PAJAK option').removeAttr('disabled');
            $('.ui-datepicker-trigger').hide();
            //tambahan aan

        } else {
            $('#CPM_MASA_PAJAK option').removeAttr('selected');
            $('#CPM_MASA_PAJAK option').attr('disabled', 'disabled');
            $('#CPM_MASA_PAJAK option').first().attr('selected', 'selected')
            $('.ui-datepicker-trigger').show();
            //tambahan aan

            $('#CPM_MASA_PAJAK1').change(function_sum)
            $('#CPM_MASA_PAJAK2').change(function_sum)
        }
        function_getval_tapbox();
    }
    setTipePajak($('#CPM_TIPE_PAJAK').val());
    $('#CPM_TIPE_PAJAK').change(function () {
        var tipe = $(this).val();
        $('#CPM_MASA_PAJAK1').val('');
        $('#CPM_MASA_PAJAK2').val('');
        setTipePajak(tipe);
        function_sum(this);
    });



    $("#modalDialog").dialog({
        autoOpen: false,
        modal: false,
        width: "900",
        resizable: false,
        draggable: true,
        height: 'auto',
        title: "",
        position: ['middle', 50]
    });

    $("#closeCBox").click(function () {
        $("#cBox").css("display", "none");
    })
});

function getDetTranTapbox(q, json) {
    $("#modalDialog").html('<img src="image/large-loading.gif" />');
    $("#modalDialog").dialog('open');
    $.ajax({
        data: "i=6&p=" + json + "&q=" + q,
        type: "post",
        url: "view/PATDA-V1/hotel/svc-list-pembanding-detail.php",
        success: function (msg) {
            $("#modalDialog").html(msg);
        }
    });
}

function admit(id, a) {
    $('#link-' + id).hide();
    var ket = $('#admit-' + id).attr('ket');
    var textarea = "<table><tr valign='top'><td>";
    textarea += "<textarea id='textarea-" + id + "'>" + ket + "</textarea></td><td>";
    textarea += "<input type='button' value='Ok' onclick=\"javascript:setAdmit('" + id + "','" + a + "')\">";
    textarea += "</td></tr></table>";

    $('#admit-' + id).html(textarea);
}

function setAdmit(id, a) {
    var val = $.trim($('#textarea-' + id).val());
    var ket = $('#admit-' + id).attr('ket');
    $('#admit-' + id).html('');

    if (val === ket) {
        $('#link-' + id).html(val).show();
    } else if (val !== ket) {

        if (val === "" || val === "...") {
            val = "...";
        }

        $('#admit-' + id).html('<img style="width:15px;" src="image/large-loading.gif" />');
        $.ajax({
            type: "post",
            data: "function=save_ket_tapbox&a=" + a + "&ket=" + val + "&id=" + id,
            url: "function/PATDA-V1/parkir/lapor/svc-lapor.php",
            success: function (msg) {
                $('#admit-' + id).attr('ket', val);
                $('#admit-' + id).html('');
                $('#link-' + id).html(val).show();
            }, error: function (msg) {
                console.log(msg)
            }
        });
    }
}

function download_excel(id, url, p, l) {

    var form = document.createElement("form");
    form.setAttribute("method", 'post');
    form.setAttribute("target", 'excel');
    form.setAttribute("action", url);

    var page = document.createElement("input");
    page.setAttribute("type", "hidden");
    page.setAttribute("name", 'page');
    page.setAttribute("value", p);
    form.appendChild(page);

    var limit = document.createElement("input");
    limit.setAttribute("type", "hidden");
    limit.setAttribute("name", 'limit');
    limit.setAttribute("value", l);
    form.appendChild(limit);

    var tahun = document.createElement("input");
    tahun.setAttribute("type", "hidden");
    tahun.setAttribute("name", 'TAHUN_PAJAK');
    tahun.setAttribute("value", $('#HIDDEN-' + id).attr('tahun'));
    form.appendChild(tahun);

    var bulan = document.createElement("input");
    bulan.setAttribute("type", "hidden");
    bulan.setAttribute("name", 'MASA_PAJAK');
    bulan.setAttribute("value", $('#HIDDEN-' + id).attr('bulan'));
    form.appendChild(bulan);

    var npwpd = document.createElement("input");
    npwpd.setAttribute("type", "hidden");
    npwpd.setAttribute("name", 'CPM_NPWPD');
    npwpd.setAttribute("value", $('#HIDDEN-' + id).attr('npwpd'));
    form.appendChild(npwpd);

    var nop = document.createElement("input");
    nop.setAttribute("type", "hidden");
    nop.setAttribute("name", 'CPM_NOP');
    nop.setAttribute("value", $('#HIDDEN-' + id).attr('nop'));
    form.appendChild(nop);

    var a = document.createElement("input");
    a.setAttribute("type", "hidden");
    a.setAttribute("name", 'a');
    a.setAttribute("value", $('#HIDDEN-' + id).attr('a'));
    form.appendChild(a);

    var notran = document.createElement("input");
    notran.setAttribute("type", "hidden");
    notran.setAttribute("name", 'NO_TRAN');
    notran.setAttribute("value", $('#NO_TRAN-' + id).val());
    form.appendChild(notran);

    var tran_date1 = document.createElement("input");
    tran_date1.setAttribute("type", "hidden");
    tran_date1.setAttribute("name", 'TRAN_DATE1');
    tran_date1.setAttribute("value", $('#TRAN_DATE1-' + id).val());
    form.appendChild(tran_date1);

    var tran_date2 = document.createElement("input");
    tran_date2.setAttribute("type", "hidden");
    tran_date2.setAttribute("name", 'TRAN_DATE2');
    tran_date2.setAttribute("value", $('#TRAN_DATE2-' + id).val());
    form.appendChild(tran_date2);

    document.body.appendChild(form);

    if ($("#cBox").is(":hidden") || typeof p === 'undefined') {

        var nmfileAll = "file-hotel";
        var nmfile = nmfileAll + '-part-';

        $("#loadlink-" + id).show();

        $.ajax({
            type: "POST",
            url: url,
            data: $(form).serialize() + "&count=1",
            dataType: "json",
            success: function (res) {
                console.log(res)
                var sumOfPage = Math.ceil(res.total_row / res.limit);
                var strOfLink = "";
                if (res.total_row < res.limit)
                    strOfLink += "<a href='javascript:void(0)' onclick=javascript:download_excel('" + id + "','" + url + "','all','" + res.limit + "')>" + nmfileAll + "</a><br/>";
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += "<a href='javascript:void(0)'  onclick=javascript:download_excel('" + id + "','" + url + "','" + page + "','" + res.limit + "')>" + nmfile + page + "</a><br/>";
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");
                $("#loadlink-" + id).hide();
            }
        });
    } else {
        if (p) form.submit();
    }

    form.parentNode.removeChild(form);

}

// function selisihBulan(awal_bulan) {

//     var bulan = awal_bulan.substring(3, 5);
//     var tahun = awal_bulan.substring(6, 10);

//     var date = new Date(),
//         bulan_sekarang = date.getMonth() + 1
//         , tahun_sekarang = date.getFullYear();

//     var hasil = (bulan_sekarang + (12 * (tahun_sekarang - tahun)) + 1) - bulan;
//     return hasil - 1;
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

function function_sum(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    var metode_hitung = $('.CPM_METODE_HITUNG:checked').val();

    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    ($('#CPM_MASA_PAJAK10').val(myvar));

    if (metode_hitung == 'DPP') {
        var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
        var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
        var tarif = Number($('#CPM_TARIF_PAJAK').val());
        // var dpp = (eval(omzet) + eval(lain)) * (100 / ( 100 + eval(tarif)));
        var dpp = (eval(omzet) * (100 / 110));
        var terutang = dpp * tarif / 100;
        $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
        $('#CPM_DPP').autoNumeric('set', dpp);


        var kurangLebih = 0;
        var masa_pajak_akhir = $('#CPM_MASA_PAJAK2').val();
        var selisih_bulan = selisihBulan(masa_pajak_akhir);

        //tambahan
        var masa_pajak_akhir2 = $('#CPM_MASA_PAJAK2').val();
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

        if (selisih_bulan > 0) {
            if (day <= 20) {
                selisih_bulan = selisih_bulan - 1;
            }
        }

        // var today = new Date();
        // var day = String(today.getDate()).padStart(2, '0');

        // var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

        if (selisih_bulan2 > 1) {
            sanksi = 100000;
        } else {
            sanksi = 0;
        }
        $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

        var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
        total = Math.ceil(total);

    } else {
        var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
        var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
        var tarif = Number($('#CPM_TARIF_PAJAK').val());
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

        // var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

        if (selisih_bulan2 > 1) {
            sanksi = 100000;
        } else {
            sanksi = 0;
        }
        $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

        var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
        total = Math.ceil(total);
    }

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

    var metode_hitung = $('.CPM_METODE_HITUNG:checked').val();

    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    ($('#CPM_MASA_PAJAK10').val(myvar));

    if (metode_hitung == 'DPP') {
        var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
        var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
        var tarif = Number($('#CPM_TARIF_PAJAK').val());
        // var dpp = (eval(omzet) + eval(lain)) * (100 / ( 100 + eval(tarif)));
        var dpp = (eval(omzet) * (100 / 110));
        var terutang = dpp * tarif / 100;
        $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
        $('#CPM_DPP').autoNumeric('set', dpp);


        var kurangLebih = 0;
        var masa_pajak_akhir = $('#CPM_MASA_PAJAK2').val();
        var selisih_bulan = selisihBulan(masa_pajak_akhir);

        //tambahan
        var masa_pajak_akhir2 = $('#CPM_MASA_PAJAK2').val();
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

        if (selisih_bulan > 0) {
            if (day <= 20) {
                selisih_bulan = selisih_bulan - 1;
            }
        }

        // var today = new Date();
        // var day = String(today.getDate()).padStart(2, '0');

        // var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

        // if (selisih_bulan2 > 1) {
        //     sanksi = 100000;
        // } else {
        //     sanksi = 0;
        // }
        // $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

        var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

        var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
        total = Math.ceil(total);

    } else {
        var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
        var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
        var tarif = Number($('#CPM_TARIF_PAJAK').val());
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

        // var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

        // if (selisih_bulan2 > 1) {
        //     sanksi = 100000;
        // } else {
        //     sanksi = 0;
        // }
        var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');
        // $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('set', sanksi);

        var total = eval(terutang) + eval(kurangLebih) + eval(sanksi);
        total = Math.ceil(total);
    }

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

