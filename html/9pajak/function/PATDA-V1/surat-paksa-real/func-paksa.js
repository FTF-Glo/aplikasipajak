$(document).ready(function () {
    $('#CPM_JUMLAH_TUNGGAKAN').number(true, 0);
    $('#CPM_TGL_SKPD').datepicker({
        dateFormat: 'dd/mm/yy',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."});
    $('#CPM_JATUH_TEMPO').datepicker({
        dateFormat: 'dd/mm/yy',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."});

    var form = $("#form-lapor");
    form.validate({
        rules: {
            "PAJAK[CPM_NPWPD]": "required",
            "PAJAK[CPM_NO_SURAT]": "required",
            "PAJAK[CPM_NAMA_OP]": "required",
            "PAJAK[CPM_ALAMAT_OP]": "required",
            "PAJAK[CPM_KECAMATAN_OP]": "required",
            "PAJAK[CPM_NAMA_WP]": "required",
            "PAJAK[CPM_NO_SKPD]": "required",
            "PAJAK[CPM_TAHUN_PAJAK]": "required",
            "PAJAK[CPM_MASA_PAJAK]": "required",
            "PAJAK[CPM_JUMLAH_TUNGGAKAN]": "required",
        },
        messages: {
            "PAJAK[CPM_NPWPD]": "harus diisi",
            "PAJAK[CPM_NO_SURAT]": "harus diisi",
            "PAJAK[CPM_NAMA_OP]": "harus diisi",
            "PAJAK[CPM_ALAMAT_OP]": "harus diisi",
            "PAJAK[CPM_KECAMATAN_OP]": "harus diisi",
            "PAJAK[CPM_NAMA_WP]": "harus diisi",
            "PAJAK[CPM_NO_SKPD]": "harus diisi",
            "PAJAK[CPM_TAHUN_PAJAK]": "harus diisi",
            "PAJAK[CPM_MASA_PAJAK]": "harus diisi",
            "PAJAK[CPM_JUMLAH_TUNGGAKAN]": "harus diisi",
        }
    });

    $("input.btn-submit").click(function () {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan surat teguran ini?");
            }        
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk merubah surat teguran ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus surat teguran ini?");
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


    $("#btn-search-npwpd").click(function () {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=" + $("#CPM_JENIS_PAJAK").val(),
            url: "function/PATDA-V1/surat-paksa-real/svc-paksa.php",
            dataType: "json",
            success: function (res) {
                $('#load-search-npwpd').html("");
                if (res.result == 1) {
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
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

    var function_sum = function (obj) {
        if ($(obj).attr('readonly') == 'readonly')
            return false;

        total = Math.ceil(total);
        var total = Number($('#CPM_JUMLAH_TUNGGAKAN').val()) ? $('#CPM_JUMLAH_TUNGGAKAN').val() : 0;
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
    $('input.SUM').keyup(function_sum);
});

function download_excel(id, url) {
    var form = $("<form></form>");
    var npwpd = $("<input type='hidden' name='CPM_NPWPD' value='" + $('#HIDDEN-' + id).attr('npwpd') + "'>");
    var tahun = $("<input type='hidden' name='TAHUN_PAJAK' value='" + $('#HIDDEN-' + id).attr('tahun') + "'>");
    var bulan = $("<input type='hidden' name='MASA_PAJAK' value='" + $('#HIDDEN-' + id).attr('bulan') + "'>");
    var alldevice = $("<input type='hidden' name='alldevice' value='" + $('#HIDDEN-' + id).attr('deviceid') + "'>");
    var a = $("<input type='hidden' name='a' value='" + $('#HIDDEN-' + id).attr('a') + "'>");
    var notran = $("<input type='hidden' name='NO_TRAN' value='" + $('#NO_TRAN-' + id).val() + "'>");
    var deviceid = $("<input type='hidden' name='CPM_DEVICE_ID' value='" + $('#CPM_DEVICE_ID-' + id).val() + "'>");
    var tran_date1 = $("<input type='hidden' name='TRAN_DATE1' value='" + $('#TRAN_DATE1-' + id).val() + "'>");
    var tran_date2 = $("<input type='hidden' name='TRAN_DATE2' value='" + $('#TRAN_DATE2-' + id).val() + "'>");
    form.attr("action", url).attr("method", "post").attr("target", "excel").append(npwpd).append(tahun).append(bulan).append(alldevice).append(a).append(notran).append(deviceid).append(tran_date1).append(tran_date2).submit();
}
