$(document).ready(function() {
	
    var form = $("#form-op");
    form.validate({
        rules: {
            "PROFIL[CPM_NPWPD]": "required",
            "PROFIL[CPM_NAMA_WP]": "required",
            "PROFIL[CPM_ALAMAT_WP]": "required",
            // "PROFIL[CPM_NOP]": "required",
            "PROFIL[CPM_NAMA_OP]": "required",
            "PROFIL[CPM_ALAMAT_OP]": "required",
            "PROFIL[CPM_KECAMATAN_WP]": "required",
            // "PROFIL[CPM_KELURAHAN_WP]": "required",
            "PROFIL[CPM_KECAMATAN_OP]": "required",
            "PROFIL[CPM_KELURAHAN_OP]": "required"
        },
        messages: {
            "PROFIL[CPM_NPWPD]": "harus diisi",
            "PROFIL[CPM_NAMA_WP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_WP]": "harus diisi",
            // "PROFIL[CPM_NOP]": "harus diisi",
            "PROFIL[CPM_NAMA_OP]": "harus diisi",
            "PROFIL[CPM_ALAMAT_OP]": "harus diisi",
            "PROFIL[CPM_KECAMATAN_WP]": "harus diisi",
            // "PROFIL[CPM_KELURAHAN_WP]": "harus diisi",
            "PROFIL[CPM_KECAMATAN_OP]": "harus diisi",
            "PROFIL[CPM_KELURAHAN_OP]": "harus diisi"
        }
    });

    $("#btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (form.valid()) {
			
			/* if($('#check_nop').val() == 1){
				$('#loading').html('<img src="image/icon/loading.gif">');
				$.ajax({
					type: "POST",
					url: "function/PATDA-V1/mineral/op/svc-op.php?param="+$('#param').val(),
					data: {'function' : 'check_nop', 'CPM_NPWPD' : $('#CPM_NPWPD').val(), 'CPM_NOP':$('#CPM_NOP').val()},
					dataType : 'json',
					success: function(res){
						$('#loading').html('');
						if(res.TOTAL > 0){
							$('#CPM_NOP').addClass('error');
							$( '<label for="CPM_NOP" generated="true" class="error">NOP sudah tersedia (duplicate).</label>' ).insertAfter( '#CPM_NOP' );

							return false;
						}
						
						res = confirm("Apakah anda yakin untuk menyimpan perubahan?");
						if (res) {
							document.getElementById("form-op").submit();
						}
					}
				});
			}else{ */
				res = confirm("Apakah anda yakin untuk menyimpan perubahan?");
				if (res) {
					document.getElementById("form-op").submit();
				}
			// }
        }        
        
    });

    $("#btn-delete").click(function() {
        if (confirm("Apakah anda yakin untuk membatalkan perubahan?")) {
            $('#function').val("rollback");
            document.getElementById("form-op").submit();
        }
    });
    
    
    $('#CPM_TRUCK_ID').select2({
		placeholder: "ID Angkutan",
		escapeMarkup: function (markup) { return markup;},
		templateResult: function(repo) {
			if (repo.loading) return repo.text;
			var markup = "<div class='select2-result-repository clearfix'>" +
			"<div class='select2-result-repository__meta'>" +
			  "<div class='select2-result-repository__title'> ID Angkutan : " + repo.id + "</div>";
			markup += (repo.text)? "<div class='select2-result-repository__description'> No Polisi : " + repo.text + "</div>" : "";
			markup += "</div></div>";
		  return markup;
		},
		templateSelection: function(repo, con) {
			return (repo.id)? repo.id : 'ID Angkutan';
		}
	});
    
});
