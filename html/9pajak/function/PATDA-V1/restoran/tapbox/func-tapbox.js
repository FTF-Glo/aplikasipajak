$(document).ready(function() {
	
    var form = $("#form-tapbox");
    form.validate({
        rules: {
            "PROFIL[CPM_NPWPD]": "required",
            "PROFIL[CPM_NAMA_WP]": "required",
            "PROFIL[CPM_ALAMAT_WP]": "required",
            "PROFIL[CPM_NOP]": "required",
            "PROFIL[CPM_NAMA_OP]": "required",
            "PROFIL[CPM_ALAMAT_OP]": "required",
            "PROFIL[CPM_KECAMATAN_WP]": "required",
            // "PROFIL[CPM_KELURAHAN_WP]": "required",
            "PROFIL[CPM_KECAMATAN_OP]": "required",
            "PROFIL[CPM_KELURAHAN_OP]": "required",
            "PROFIL[CPM_DEVICE_ID]": "required"
        },
        messages: {
            "PROFIL[CPM_NPWPD]": "harus diisi",
            "PROFIL[CPM_NAMA_WP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_WP]": "harus diisi",
            "PROFIL[CPM_NOP]": "harus diisi",
            "PROFIL[CPM_NAMA_OP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_OP]": "harus diisi",
            "PROFIL[CPM_KECAMATAN_WP]": "harus diisi",
            // "PROFIL[CPM_KELURAHAN_WP]": "harus diisi",
            "PROFIL[CPM_KECAMATAN_OP]": "harus diisi",
            "PROFIL[CPM_KELURAHAN_OP]": "harus diisi",
            "PROFIL[CPM_DEVICE_ID]": "harus diisi"
        }
    });

    $("#btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (form.valid()) {
			
			res = confirm("Apakah anda yakin untuk menyimpan perubahan?");
			if (res) {
				document.getElementById("form-tapbox").submit();
			}
        }        
        
    });
    
});
