<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Halaman Informasi</h3>
                </div>
                <div class="col-md-4 mb-1 menu-right">
                    <button class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Tambah Informasi</button>
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
                                <th>Judul</th>
                                <th>Kode</th>
                                <th>Isi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
                                    $btn = $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'"').
                                           $this->html->btn_icon('Delete','fa fa-trash','btn-del','','data-id="'.$row['id'].'" data-name="'.$row['title'].'"');
                                    echo '
                                            <tr>
                                                <td>'.$row['id'].'</td>
                                                <td>'.$row['title'].'</td>
                                                <td>'.$row['code'].'</td>
                                                <td>'.$row['content'].'</td>
                                                <td>'.$btn.'</td>
                                            </tr>
                                         ';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
	$('#myTable').DataTable();	
});  
</script>

<div class="modal fade" id="modAct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>page/act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Tambah Badan Usaha</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
    		    <div class="form-group">
    		        <label for="txTitle">Judul :</label>
    		        <input type="text" id="txTitle" name="txTitle" class="form-control" required />
    		    </div>
                <div class="form-group">
    		        <label for="txCode">Kode :</label>
    		        <input type="text" id="txCode" name="txCode" class="form-control" required />
    		    </div>
    		    <div class="form-group">
    		        <label for="txContent">Isi:</label>
    		        <textarea name="txContent" id="txContent" class="form-control"></textarea>
    		    </div>

		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-primary btn-submit">Save</button>
		  </div>
		</div>
	  </div>

  </form>
</div>
<div class="modal fade" id="modDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>page/del" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabelDel">Konfirmasi Hapus</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		    <input name="tbxPage" value="<?php echo uri_string();?>" hidden>
		    <input name="tbxTitle" id="tbxTitle" hidden />
			<input id="tbxID" name="tbxID" hidden />
			<div>
			Apakah Anda yakin untuk menghapus "<span class="tbxTitle"></span>" ?
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-primary">Confirm</button>
		  </div>
		</div>
	  </div>
  </form>
</div>

<script>
var modAct = $('#modAct');
$('.btn-add').on('click', function(){
    modAct.find('#exampleModalLabel').html('Tambah Halaman Informasi');
    modAct.modal('show');
    return false;
});
$('.btn-edit').on('click', function(){
    var id = $(this).attr('data-id');
    
    $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getPageID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#exampleModalLabel').html('Perbaharui Halaman Informasi');
			    modAct.find('#txTitle').val(arr[0]['title']);
			    modAct.find('#txCode').val(arr[0]['code']);
				modAct.find('#txContent').val(arr[0]['content']);
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
	modDel.find(".tbxTitle").html(name);
	modDel.find("#tbxTitle").val(name);
    modDel.modal('show');
    return false;
});
</script>