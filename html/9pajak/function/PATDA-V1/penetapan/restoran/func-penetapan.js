$(document).ready(function() {
    $('#CPM_PEMERIKSAAN_PAJAK').number(true, 2);
    $('#CPM_BUNGA').number(true, 2);
    $('#CPM_DENDA').number(true, 2);
    $('#CPM_KURANG_BAYAR').number(true,2);

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

    $('#CPM_KURANG_BAYAR').keyup(function(){
        var total = eval ($(this).val())
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
