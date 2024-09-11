<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
             <h3>Detail Validasi Pendaftaran Usaha</h3>
             <div class="card">
                <div class="card-body">
                    <h4><?php echo $usaha['usaha_name'];?></h4>
                    <h6><?php echo $usaha['badan_name'];?></h6>
                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Alamat</strong></label>
                                <?php echo $this->format->address($usaha);?>
                            </div>
                            <form action="<?php echo base_url();?>wp/validasi_usaha_accept" method="post" accept-charset="utf-8">
                            <input name="txID" value="<?php echo $usaha['id_usaha'];?>" hidden>
                            <div class="form-group mt-2">
                                <label><strong>NPWPD (Nomor Pokok Wajib Pajak Daerah)</strong></label>
                                <input name="txNPWPD" value="<?php echo $usaha['npwpd'];?>" class="form-control" /> 
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">Setujui</button>
                                <button type="button" class="btn btn-secondary btn-block btn-reject" data-id="<?php echo $usaha['id_usaha'];?>" data-name="<?php echo $usaha['usaha_name'];?>">Tolak</button>
                            </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-gray p-1">
                                <h5>PENDAFTAR</h5>
                                <div class="form-group">
                                    <label>Nama Lengkap :</label>
                                    <div><strong><?php echo $usaha['fullname'];?></strong></div>
                                </div>
                                <div class="form-group">
                                    <label>No.KTP :</label>
                                    <div><strong><?php echo $usaha['ktp'];?></strong></div>
                                </div>
                                <div class="form-group">
                                    <label>Email :</label>
                                    <div><strong><?php echo $usaha['email'];?></strong></div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modReject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>wp/validasi_usaha_reject" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Tolak Pendaftaran Usaha</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
                Apakah Anda yakin untuk menolak "<span class="tbxName"></span>" ?
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button type="submit" class="btn btn-primary btn-submit">Yakin</button>
		  </div>
		</div>
	  </div>

  </form>
</div>

<script>
var modDel = $('#modReject');
$('.btn-reject').on('click', function(){
    var id = $(this).attr('data-id');
    var name = $(this).attr('data-name');
	modDel.find("#txID").val(id);
	modDel.find(".tbxName").html(name);
    modDel.modal('show');
    return false;
});
</script>