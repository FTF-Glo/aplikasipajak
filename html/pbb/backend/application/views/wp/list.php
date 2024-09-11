<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Daftar Wajib Pajak Terdaftar</h3>
                </div>

            </div>
        
            <div class="card">
                <div class="card-body">
					<?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			        ?>
                    <table id="myTable" class="table table-bordered table-striped table-hover table-select">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>KTP</th>
                                <th>Fullname</th>
                                <th>Email</th>
                                <th>Tanggal Daftar</th>
                                <th>Login Terakhir</th>

                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modAct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>master/usaha_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Tambah Bidang Usaha</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
		       

		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-primary btn-submit">Save</button>
		  </div>
		</div>
	  </div>

  </form>
</div>
<script type="text/javascript">
    var table;
    $(document).ready(function() {
        table = $('#myTable').DataTable({ 
            "processing": true, 
            "serverSide": true, 
            "order": [], 
            "ajax": {
                "url": "<?php echo site_url('api_master/getUsersListActive')?>",
                "type": "POST"
            },
            //"columnDefs": [{ 
               // "targets": [1], 
               // "orderable": false, 
            //}],
        });
    });

</script>

<script>
var modAct = $('#modAct');
$('.btn-edit').on('click', function(){
    var id = $(this).attr('data-id');
    
    $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getUsahaID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#exampleModalLabel').html('Perbaharui Bidang Usaha');
			    modAct.find('#txName').val(arr[0]['title']);
                modAct.find('#cbClass option[value='+arr[0]['id_class']+']').attr('selected','selected');
				modAct.find('#txDesc').val(arr[0]['description']);
				modAct.find('#txID').val(id);
				modAct.modal('show');
                return false;
			}
		});	
		
});
var modDel = $('#modDel');
$('.btn-del').on('click', function(){
    var id = $(this).attr('data-id');
	var name = $(this).attr('data-name');
	modDel.find("#tbxID").val(id);
	modDel.find(".tbxName").html(name);
	modDel.find("#tbxName").val(name);
    modDel.modal('show');
    return false;
});
</script>