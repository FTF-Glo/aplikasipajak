$(document).ready(function() {
	
	$('input:reset').click(function(){
		$('select#CPM_NPWPD').html('').trigger('change');
		$('select#CPM_KECAMATAN_OP').html('').trigger('change');
		$('select#CPM_KELURAHAN_OP').html('').trigger('change');
	});
	
	
    var form = $("#form-profil");
    form.validate({
        rules: {
            "PROFIL[CPM_NPWPD]": "required",
            "PROFIL[CPM_NAMA_WP]": "required",
            "PROFIL[CPM_ALAMAT_WP]": "required",
            "PROFIL[CPM_NOP]": "required",
            "PROFIL[CPM_NAMA_OP]": "required",
            "PROFIL[CPM_ALAMAT_OP]": "required",
            "PROFIL[CPM_GOL_HOTEL]": "required",
        },
        messages: {
            "PROFIL[CPM_NPWPD]": "harus diisi",
            "PROFIL[CPM_NAMA_WP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_WP]": "harus diisi",
            "PROFIL[CPM_NOP]": "harus diisi",
            "PROFIL[CPM_NAMA_OP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_OP]": "harus diisi",
            "PROFIL[CPM_GOL_HOTEL]": "harus diisi",
        }
    });

    $("#btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (form.valid()) {
            res = confirm("Apakah anda yakin untuk menyimpan perubahan?");
        }
        if (res) {
            document.getElementById("form-profil").submit();
        }
    });

    $("#btn-delete").click(function() {
        if (confirm("Apakah anda yakin untuk membatalkan perubahan?")) {
            $('#function').val("rollback");
            document.getElementById("form-profil").submit();
        }
    });

    /* FUNGSI UNTUK PENGATURAN PROFIL PADA PELAYANAN*/
    $("#CPM_NPWPD").keyup(function() {
        if ($(this).attr('readonly') == 'readonly')
            return false;
        $("#CPM_ID").val('');
        $("#CPM_NAMA_WP").val('');
        $("#CPM_ALAMAT_WP").val('');
        $("#CPM_NOP").val('');
        $("#CPM_NAMA_OP").val('');
        $("#CPM_ALAMAT_OP").val('');
        $("#CPM_GOL_HOTEL option").prop('selected', false);
        $("#CPM_DEVICE_ID").val('');
    });

    $("#btn-search-npwpd").click(function() {
        $('#load-search-npwpd').html("<img src='image/large-loading.gif' style='width:20px;'>");
        var npwpd = $("#CPM_NPWPD").val();
        $.ajax({
            type: "POST",
            data: "PAJAK[CPM_NPWPD]=" + npwpd + "&function=search_npwpd&PAJAK[CPM_JENIS_PAJAK]=3",
            url: "function/PATDA-V1/hotel/lapor/svc-lapor.php",
            dataType: "json",
            success: function(res) {
                $('#load-search-npwpd').html("");
                if (res.result == 1) {
                    $("#CPM_ID").val(res.CPM_ID);
                    $("#CPM_NPWPD").val(res.CPM_NPWPD);
                    $("#CPM_NAMA_WP").val(res.CPM_NAMA_WP);
                    $("#CPM_ALAMAT_WP").val(res.CPM_ALAMAT_WP);
                    $("#CPM_NOP").val(res.CPM_NOP);
                    $("#CPM_NAMA_OP").val(res.CPM_NAMA_OP);
                    $("#CPM_ALAMAT_OP").val(res.CPM_ALAMAT_OP);
                    $("#CPM_GOL_HOTEL option[value='" + res.CPM_GOL_HOTEL + "']").prop('selected', true);
                    $("#CPM_DEVICE_ID").val(res.CPM_DEVICE_ID_ORI);
                } else {
                    alert("NPWPD tidak ditemukan!")
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });
});
