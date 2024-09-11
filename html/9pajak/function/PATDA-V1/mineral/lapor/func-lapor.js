function function_sum(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
    var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK').val());
    var dpp = eval(omzet) + eval(lain);
    var terutang = dpp * tarif / 100;
    $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
    $('#CPM_DPP').autoNumeric('set', dpp);

    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    ($('#CPM_MASA_PAJAK10').val(myvar));

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
    //console.log(selisih_bulan, selisih_bulan2);

    // var today = new Date();
    // var day = String(today.getDate()).padStart(2, '0');

    // if (selisih_bulan > 0) {
    //     if (day <= 20) {
    //         selisih_bulan = selisih_bulan - 1;
    //     }
    // }



    // var sanksi = $('#CPM_DENDA_TERLAMBAT_LAP').autoNumeric('get');

    if (selisih_bulan2 > 1) {
        sanksi = 100000;
    } else {
        sanksi = 0;
    }
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

function function_sum2(obj) {
    if ($(obj).attr('readonly') == 'readonly')
        return false;

    var omzet = Number($('#CPM_TOTAL_OMZET').autoNumeric('get'));
    var lain = Number($('#CPM_BAYAR_LAINNYA').autoNumeric('get'));
    var tarif = Number($('#CPM_TARIF_PAJAK').val());
    var dpp = eval(omzet) + eval(lain);
    var terutang = dpp * tarif / 100;
    $('#CPM_BAYAR_TERUTANG').autoNumeric('set', terutang);
    $('#CPM_DPP').autoNumeric('set', dpp);

    //tambahan
    var mystr = $('#CPM_MASA_PAJAK1').val();
    var myarr = mystr.split("/");
    var myvar = myarr[1];
    var myvar = parseInt(myvar, 10);
    ($('#CPM_MASA_PAJAK10').val(myvar));

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
    //console.log(selisih_bulan, selisih_bulan2);

    // var today = new Date();
    // var day = String(today.getDate()).padStart(2, '0');

    // if (selisih_bulan > 0) {
    //     if (day <= 20) {
    //         selisih_bulan = selisih_bulan - 1;
    //     }
    // }



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

function function_getval_tracking() {
    var npwpd = $('#CPM_NPWPD').val();
    var thn = $('#CPM_TAHUN_PAJAK').val();
    var bln = $('#CPM_MASA_PAJAK').val();
    var id = $('#CPM_TRUCK_ID').val();
    var a = $('#a').val();

    $('#val_tracking').html("<img src='image/large-loading.gif' style='width:10px;'>");
    $.ajax({
        type: "POST",
        data: "a=" + a + "&PAJAK[CPM_NPWPD]=" + npwpd + "&PAJAK[CPM_TAHUN_PAJAK]=" + thn + "&PAJAK[CPM_MASA_PAJAK]=" + bln + "&id=" + id + "&function=get_val_tracking",
        url: "function/PATDA-V1/mineral/lapor/svc-lapor.php",
        dataType: "json",
        success: function (res) {
            $('#val_tracking').html(res.amount)
        },
        error: function (res) {
            console.log('error', res)
        }
    });
}
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

    $('input#CPM_ATR_VOLUME').autoNumeric('init');
    $('input#CPM_ATR_HARGA').autoNumeric('init');

    $('#CPM_TOTAL_OMZET').autoNumeric('init');
    $('#CPM_BAYAR_LAINNYA').autoNumeric('init');
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
            "PAJAK[CPM_TOTAL_OMZET]": "required",
            "PAJAK[CPM_TOTAL_PAJAK]": "required",
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
            "PAJAK[CPM_MASA_PAJAK1]": "harus diisi",
        }
    });

    $('input.SUM').keyup(function_sum);
    $('input.SUM2').keyup(function_sum2);

    $("#CPM_MASA_TIPE").change(function () {
        $("#lbl-masa").html($(this).val());
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

        if ($(this).val() == "Triwulan" || $(this).val() == "6 Bulan") {
            if ($(this).val() == "Triwulan") {
                bln = parseInt(bln) + 2;
                if (bln > 12) {
                    bln = bln - 12;
                    thn = parseInt(thn) + 1;
                }
                var tgl2 = new Date(thn, bln, 0);
            } else if ($(this).val() == "6 Bulan") {
                bln = parseInt(bln) + 5;
                if (bln > 12) {
                    bln = bln - 12;
                    thn = parseInt(thn) + 1;
                }
                var tgl2 = new Date(thn, bln, 0);
            }
            tgl = tgl2.getDate();
            bln = tgl2.getMonth() + 1;
            tgl = (eval(tgl) < 10) ? '0' + tgl : tgl;
            bln = (eval(bln) < 10) ? '0' + bln : bln;
        }

        $('#CPM_MASA_PAJAK2').val(tgl + '/' + bln + '/' + thn);
    });



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
    });

    $("#btn-search-npwpd").click(function () {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=4",
            url: "function/PATDA-V1/mineral/lapor/svc-lapor.php",
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
                } else {
                    alert("NPWPD tidak ditemukan!")
                }
            },
            error: function (res) {
                console.log(res)
            }
        })
    });


    var function_tarif = function (obj) {
        var type = $('#CPM_TIPE_PAJAK option:selected').val();
        //if (type == 1) {
        $('#CPM_TARIF_PAJAK').val($('#CPM_ATR_NAMA_1 option:selected').attr('tarif'));
        //} else {
        //    $('#CPM_TARIF_PAJAK').val(100)
        //}
        function_sum(Object);
    }

    $('#CPM_TIPE_PAJAK').change(function_tarif);

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
    }
    setTipePajak($('#CPM_TIPE_PAJAK').val());
    $('#CPM_TIPE_PAJAK').change(function () {
        var tipe = $(this).val();
        $('#CPM_MASA_PAJAK1').val('');
        $('#CPM_MASA_PAJAK2').val('');
        setTipePajak(tipe);
        function_sum(this);
    });



    $('#CPM_TAHUN_PAJAK,#CPM_MASA_PAJAK').change(function_getval_tracking)
    if ($('#CPM_TRUCK_ID').val() != '') {
        function_getval_tracking();
    }


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

function getHarga(obj, id) {
    getBayar(id);
    $('#CPM_ATR_HARGA_' + id).val($('#CPM_ATR_NAMA_' + id + ' option:selected').attr('harga'));

    //if ($('#CPM_TIPE_PAJAK').val() == 1) {
    $('#CPM_TARIF_PAJAK').val($('#CPM_ATR_NAMA_' + id + ' option:selected').attr('tarif'));
    //} else {
    //    $('#CPM_TARIF_PAJAK').val(100);
    //}
    function_sum(obj);
}

function getBayar(id) {
    var loop = $(".CPM_ATR_NAMA").length;
    var total = 0;
    for (var x = 1; x <= loop; x++) {
        var volume = Number($('#CPM_ATR_VOLUME_' + x).val()) ? $('#CPM_ATR_VOLUME_' + x).val() : 0;
        var harga = Number($('#CPM_ATR_HARGA_' + x).val()) ? $('#CPM_ATR_HARGA_' + x).val() : 0;
        var subtotal = eval(volume) * eval(harga);
        total += subtotal;
    }

    $('#CPM_TOTAL_OMZET').autoNumeric('set', total);
    function_sum(Object);
}

function get_detail_tracking(q, json) {
    $("#modalDialog").html('<img src="image/large-loading.gif" />');
    $("#modalDialog").dialog('open');
    $.ajax({
        data: "i=6&p=" + json + "&q=" + q,
        type: "post",
        url: "view/PATDA-V1/tracking/mineral/svc-list-tracking.php",
        success: function (msg) {
            $("#modalDialog").html(msg);
        }
    });
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

    var alldevice = document.createElement("input");
    alldevice.setAttribute("type", "hidden");
    alldevice.setAttribute("name", 'alltruckid');
    alldevice.setAttribute("value", $('#HIDDEN-' + id).attr('truck_id'));
    form.appendChild(alldevice);

    var a = document.createElement("input");
    a.setAttribute("type", "hidden");
    a.setAttribute("name", 'a');
    a.setAttribute("value", $('#HIDDEN-' + id).attr('a'));
    form.appendChild(a);

    var notran = document.createElement("input");
    notran.setAttribute("type", "hidden");
    notran.setAttribute("name", 'CPM_TRAN_ID');
    notran.setAttribute("value", $('#CPM_TRAN_ID-' + id).val());
    form.appendChild(notran);

    var deviceid = document.createElement("input");
    deviceid.setAttribute("type", "hidden");
    deviceid.setAttribute("name", 'CPM_TRUCK_ID');
    deviceid.setAttribute("value", $('#CPM_TRUCK_ID-' + id).val());
    form.appendChild(deviceid);

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

        var nmfileAll = "file-tracking";
        var nmfile = nmfileAll + '-part-';

        $("#loadlink-" + id).show();

        $.ajax({
            type: "POST",
            url: url,
            data: $(form).serialize() + "&count=1",
            dataType: "json",
            success: function (res) {
                var sumOfPage = Math.ceil(res.total_row / res.limit);
                var strOfLink = "";

                if (res.total_row < res.limit) {
                    url += '?nmfile=' + nmfileAll;
                    strOfLink += "<a href='javascript:void(0)' onclick=javascript:download_excel('" + id + "','" + url + "','all','" + res.limit + "')>" + nmfileAll + ".xls</a><br/>";
                } else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        url += '?nmfile=' + nmfile + page;
                        strOfLink += "<a href='javascript:void(0)'  onclick=javascript:download_excel('" + id + "','" + url + "','" + page + "','" + res.limit + "')>" + nmfile + page + ".xls</a><br/>";
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
