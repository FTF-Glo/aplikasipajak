$(document).ready(function() {
    $('#CPM_TGL_JATUH_TEMPO_PAJAK').datepicker({
        dateFormat: 'dd/mm/yy',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."});    
    $('#CPM_KURANG_BAYAR').number(true, 2);
    $('#CPM_SANKSI').number(true, 2);
    $('#CPM_TOTAL_PAJAK').number(true, 2);
    $('#CPM_TOTAL_SETOR').number(true, 2);
    $('#CPM_BUNGA').number(true, 2);
    $('#CPM_TAGIHAN').number(true, 2);
    
    var form = $("#form-penagihan");
    form.validate({
        rules: {
            "TAGIHAN[CPM_NO_STPD]": "required",
            "TAGIHAN[CPM_MASA_PAJAK]":"required",
            "TAGIHAN[CPM_TAHUN_PAJAK]":"required",
            "TAGIHAN[CPM_TAHUN_STPD]": "required",
            "TAGIHAN[CPM_MASA_STPD]": "required",
            "TAGIHAN[CPM_AYAT]": "required",
            "TAGIHAN[CPM_NAMA_OP]": "required",
            "TAGIHAN[CPM_TGL_JATUH_TEMPO_PAJAK]": "required",
            "TAGIHAN[CPM_KURANG_BAYAR]": "required",
            "TAGIHAN[CPM_SANKSI]": "required",
            "TAGIHAN[CPM_TOTAL_PAJAK]": "required",
            //"TAGIHAN[CPM_AYAT_PAJAK]": "required"            
            
        },
        messages: {
            "TAGIHAN[CPM_NO_STPD]": "harus diisi",
            "TAGIHAN[CPM_MASA_PAJAK]":"harus diisi",
            "TAGIHAN[CPM_TAHUN_PAJAK]":"harus diisi",
            "TAGIHAN[CPM_TAHUN_STPD]": "harus diisi",
            "TAGIHAN[CPM_MASA_STPD]": "harus diisi",
            "TAGIHAN[CPM_AYAT]": "harus diisi",
            "TAGIHAN[CPM_NAMA_OP]": "harus diisi",
            "TAGIHAN[CPM_TGL_JATUH_TEMPO_PAJAK]": "harus diisi",            
            "TAGIHAN[CPM_KURANG_BAYAR]": "harus diisi",
            "TAGIHAN[CPM_SANKSI]": "harus diisi",
            "TAGIHAN[CPM_TOTAL_PAJAK]": "harus diisi",
            //"TAGIHAN[CPM_AYAT_PAJAK]": "harus diisi"
        }
    });

    $("input.btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);

        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan penagihan ini?");
            }
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk merubah penagihan ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus penagihan ini?");
        } else if (action == "verifikasi" || action == "persetujuan") {
            res = confirm("Apakah anda yakin untuk menyetujui / menolak penagihan ini?");
        } else if (action == "new_version") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk membuat versi baru penagihan ini?");
            }
        } 
        if (res) {
            document.getElementById("form-penagihan").submit();
        }
    });

    $("input.btn-print").click(function() {
        var action = $(this).attr('action');
        $('#function').val(action);
        $("#form-penagihan").attr('target', '_blank');
        document.getElementById("form-penagihan").submit();
    });
    
    $('#CPM_TAGIHAN').keyup(function() {
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
    
    var function_sum = function(obj) {
        if ($(obj).attr('readonly') == 'readonly')
            return false;

        var kurangbayar = Number($('#CPM_KURANG_BAYAR').val()) ? $('#CPM_KURANG_BAYAR').val() : 0;
        var sanksi = 2/100 * eval(kurangbayar);
        var totalpajak = eval(sanksi) + eval(kurangbayar);
        $('#CPM_SANKSI').val(sanksi);
        $('#CPM_TOTAL_PAJAK').val(totalpajak);
        

    }
    $('input.SUM').keyup(function_sum);
});
