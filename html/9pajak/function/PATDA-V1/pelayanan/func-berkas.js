$(document).ready(function() {
    //$('#CPM_TGL_INPUT').date();
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            // Hanya memungkinkan digit 0-9
            return false;
        }
        return true;
    }
    var form = $("#form-berkas");
    form.validate({
        rules: {
            "BERKAS[CPM_TGL_INPUT]": "required",
            "BERKAS[CPM_NO_SPTPD]": "required",
            "BERKAS[CPM_NPWPD]": "required",
            "BERKAS[CPM_NAMA_WP]": "required",
            "BERKAS[CPM_ALAMAT_WP]": "required",
            "BERKAS[CPM_NAMA_OP]": "required",
            "BERKAS[CPM_ALAMAT_OP]": "required",
        },
        messages: {
            "BERKAS[CPM_TGL_INPUT]": "harus isi",
            "BERKAS[CPM_NO_SPTPD]": "harus isi",
            "BERKAS[CPM_NPWPD]": "harus diisi",
            "BERKAS[CPM_NAMA_WP]": "harus diisi",
            "BERKAS[CPM_ALAMAT_WP]": "harus diisi",
            "BERKAS[CPM_NAMA_OP]": "harus diisi",
            "BERKAS[CPM_ALAMAT_OP]": "harus diisi",
            "BERKAS[CPM_GOL_HOTEL]": "harus diisi",
        }
    });

    $("#btn-search-sptpd").click(function() {
        var no_sptpd = $("#CPM_NO_SPTPD").val();
        var jns_pajak = $("#CPM_JENIS_PAJAK").val();
        $.ajax({
            type: "POST",
            data: "BERKAS[CPM_NO_SPTPD]=" + no_sptpd + "&function=search_sptpd&BERKAS[CPM_JENIS_PAJAK]=" + jns_pajak,
            url: "function/PATDA-V1/pelayanan/svc-berkas.php",
            dataType: "json",
            success: function(res) {
                console.log(res)
                if (res.result == 1) {
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                } else {
                    alert("SPTPD tidak ditemukan!")
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });

    $("input.btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);

        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk berkas ini?");
            }
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk merubah berkas ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus berkas ini?");
        }
        if (res) {
            document.getElementById("form-berkas").submit();
        }
    });

    $("input.btn-print").click(function() {
        var action = $(this).attr('action');
        $('#function').val(action);
        $("#form-berkas").attr('target', '_blank');
        document.getElementById("form-berkas").submit();
    });
});
