$(document).ready(function() {
    $('#CPM_PEMERIKSAAN_PAJAK').number(true, 2);
    $('#CPM_BUNGA').number(true, 2);
    $('#CPM_DENDA').number(true, 2);
    $('#CPM_KURANG_BAYAR').number(true, 2);
    $('#CPM_TOTAL_PAJAK').number(true, 2);    
    $('#CPM_TGL_JATUH_TEMPO').datepicker({dateFormat: 'dd/mm/yy',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."});

    var form = $("#form-lapor");
    form.validate({
        rules: {
            "SKPDKB[CPM_NO_SKPDKB]": "required",
            "SKPDKB[CPM_NPWPD]": "required",
            "SKPDKB[CPM_NAMA_WP]": "required",
            "SKPDKB[CPM_ALAMAT_WP]": "required"
        },
        messages: {
            "SKPDKB[CPM_NO_SKPDKB]": "harus diisi",
            "SKPDKB[CPM_NPWPD]": "harus diisi",
            "SKPDKB[CPM_NAMA_WP]": "harus diisi",
            "SKPDKB[CPM_ALAMAT_WP]": "harus diisi"
        }
    });

    var function_sum = function(obj) {
        if ($(obj).attr('readonly') == 'readonly')
            return false;

        var pemeriksaan = Number($('#CPM_PEMERIKSAAN_PAJAK').val()) ? $('#CPM_PEMERIKSAAN_PAJAK').val() : 0;
        var bunga = Number($('#CPM_BUNGA').val()) ? $('#CPM_BUNGA').val() : 0;
        var denda = Number($('#CPM_DENDA').val()) ? $('#CPM_DENDA').val() : 0;
        var penyetoran = ($('#CPM_TOTAL_PAJAK').val())
        
        var kurangbayar = (eval(pemeriksaan) + eval(bunga) + eval(denda)) - eval(penyetoran);
        
        kurangbayar = kurangbayar.toFixed(2);
        
        console.log(kurangbayar)
        $('#CPM_KURANG_BAYAR').val(kurangbayar);
        $.ajax({
            type: "POST",
            data: "num=" + kurangbayar,
            url: "function/PATDA-V1/svc-terbilang.php",
            success: function(res) {
                $("#CPM_TERBILANG").val(res);
            }
        })
    }
    $('input.SUM').keyup(function_sum);

    $('#CPM_TRAN_INFO').removeClass('required');
    $('input.AUTHORITY').change(function() {
        if ($(this).val() == 1) {
            $('#CPM_TRAN_INFO').removeClass('required');
            $('#CPM_TRAN_INFO').attr('readonly', 'readonly');
            $('#CPM_TRAN_INFO').val('');
        } else {
            $('#CPM_TRAN_INFO').addClass('required');
            $('#CPM_TRAN_INFO').removeAttr('readonly');
        }
    });

    $('#CPM_KURANG_BAYAR').keyup(function() {
        var total = eval($(this).val())
        total = total.toFixed(2);
        $.ajax({
            type: "POST",
            data: "num=" + total,
            url: "function/PATDA-V1/svc-terbilang.php",
            success: function(res) {

                $("#CPM_TERBILANG").val(res);
            }
        })
    });

    $("input.btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);

        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan laporan ini?");
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
});
