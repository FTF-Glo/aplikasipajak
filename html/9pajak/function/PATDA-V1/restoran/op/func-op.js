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
					url: "function/PATDA-V1/restoran/op/svc-op.php?param="+$('#param').val(),
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

    $(".add").on("click", function(){
        var num = eval($("#num_detail").val())+1;
        var row = '<tr><td><input type="hidden" id="CPM_ID-'+num+'" name="PROFIL_DETAIL[CPM_ID][]" value="" /><input type="number" class="number" name="PROFIL_DETAIL[CPM_JUMLAH_MEJA][]" id="CPM_JUMLAH_MEJA-'+num+'" style="display:block;width:100%" /></td><td><input type="number" class="number" name="PROFIL_DETAIL[CPM_JUMLAH_KURSI][]" id="CPM_JUMLAH_KURSI-'+num+'" style="display:block;width:100%" /></td><td><input type="number" class="number" name="PROFIL_DETAIL[CPM_JUMLAH_PENGUNJUNG][]" id="CPM_JUMLAH_PENGUNJUNG-'+num+'" style="display:block;width:100%" /></td><td align="center"><a href="#" onclick="hapus_detail('+num+');return false;">[Hapus]</a></td></tr>';
        $(".profil_detail").append(row);
        $("#num_detail").val(num);
    });
    
});

function hapus_detail(id){
    var tanya = confirm("Anda yakin ingin menghapus baris ini secara permanen?");
    var dataID = $("#CPM_ID-"+id).val();
    if(tanya){
        if(dataID==""){
            $("#CPM_JUMLAH_MEJA-"+id).closest('tr').hide();
        }else{
            $.ajax({
                url:"function/PATDA-V1/restoran/op/svc-op.php",
                type:'post',
                data:{function:'del_detail',DATA_ID:dataID},
                cache:false,
                dataType:'json',
                success:function(a){
                    alert(a.message);
                    if(a.status==1){
                        $("#CPM_JUMLAH_MEJA-"+id).closest('tr').hide();
                    }
                }
            });
        }
        
    }
}
