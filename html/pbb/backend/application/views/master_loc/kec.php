<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Master Kecamatan</h3>
                </div>
                <div class="col-md-4 mb-1 menu-right">
                    <button class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Tambah Kabupaten</button>
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
                                <th>Nama</th>
                                <th>Kabupaten</th>
                                <th>Provinsi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
                                    $btn = $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'"').
                                           $this->html->btn_icon('Delete','fa fa-trash','btn-del','','data-id="'.$row['id'].'" data-name="'.$row['name'].'"');
                                    foreach($kab as $kb){
                                        if($kb['id'] == $row['id_kab']){
                                            $kabupaten = $kb['name'];
                                        } 
                                    }
                                    foreach($prov as $pr){
                                        if($pr['id'] == $row['id_prov']){
                                            $province = $pr['name'];
                                        } 
                                    }
                                    echo '
                                            <tr>
                                                <td>'.$row['id'].'</td>
                                                <td>'.$row['name'].'</td>
                                                <td>'.$kabupaten.'</td>
                                                <td>'.$province.'</td>
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
  <form action="<?php echo base_url();?>master_loc/kec_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Tambah Kecamatan</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
                <div class="form-group">
    		        <label for="txName">Provinsi:</label>
    		        <select id="cbProv" name="cbProv" class="form-control">
    		          <option value="0">-Pilih-</option>
    		          <?php
    		              foreach($prov as $row){
    		                  echo '
    		                          <option value="'.$row['id'].'">'.$row['name'].'</option>
    		                       ';
    		              }
    		          ?>
    		        </select>
    		    </div>
                <div class="form-group">
    		        <label for="txKab">Kabupaten:</label>
    		        <select id="cbKab" name="cbKab" class="form-control" disabled>
    		          
    		        </select>
    		    </div>
    		    <div class="form-group">
    		        <label for="txName">Nama Kecamatan:</label>
    		        <input type="text" id="txName" name="txName" class="form-control" required />
    		    </div>

		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-primary btn-submit">Save</button>
		  </div>
		</div>
	  </div>

  </form>
  <script>
  $('#cbProv').change(function(){
    var idProv = this.value;
    $('#cbKab option').remove();
    if(idProv != 0){
        $.ajax({
            type: "POST",
            data: {idProv:idProv},
            url: "<?php echo base_url(); ?>api_master/getKab",
            success: function(msg){
              var arr = JSON.parse(msg);
              $('#cbKab').append('<option value="0">-Pilih-</option>');
              for(var i=0; i<arr.length; i++){
                
                $('#cbKab').append('<option value="'+arr[i].id+'">'+arr[i].name+'</option>');
                $("#cbKab").prop('disabled', false);
              }
            }
        });
    }else{
        $("#cbKab").prop('disabled', true);
    }

   });   
  </script>
</div>



<div class="modal fade" id="modDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>master_loc/kab_del" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Konfirmasi Hapus</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		    <input name="tbxPage" value="<?php echo uri_string();?>" hidden>
		    <input name="tbxName" id="tbxName" hidden />
			<input id="tbxID" name="tbxID" hidden />
			<div>
			Apakah Anda yakin untuk menghapus "<span class="tbxName"></span>" ?
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
    modAct.modal('show');
    return false;
});
$('.btn-edit').on('click', function(){
    var id = $(this).attr('data-id');
    $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getKecID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#txName').val(arr[0]['name']);
			    modAct.find('#cbProv option[value='+arr[0]['id_prov']+']').attr('selected','selected');
                modAct.find('#cbKab option[value='+arr[0]['id_kab']+']').attr('selected','selected');
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