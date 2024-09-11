<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Master Informasi Pajak</h3>
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
                                <th>Posisi</th>
                                <th>Jenis pajak</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
                                    foreach($type as $ty){
                                        if($row['id_type'] == $ty['id']){
                                            $tax_type = $ty['name'];
                                        }
                                    }
                                    $btn = $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'" data-type="'.$tax_type.'"');
                          
                                    echo '
                                            <tr>
                                                <td>'.$row['id'].'</td>
                                                <td>'.$row['page'].'</td>
                                                <td>'.$tax_type.'</td>
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
  <form action="<?php echo base_url();?>master/info_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Perbaharui Informasi</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
    		    <div class="form-group">
    		        <label for="txPage">Posisi :</label>
    		        <input type="text" id="txPage" name="txPage" class="form-control" readonly/>
    		    </div>
    		    <div class="form-group">
    		        <label for="txType">Kode:</label>
    		        <input type="text" id="txType" name="txType" class="form-control" readonly />
    		    </div>
                <div class="form-group">
    		        <label for="txContent">Konten:</label>
    		        <textarea name="txContent" id="txContent" rows="8" class="form-control">
    		        
    		        </textarea>
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

<script>

var modAct = $('#modAct');
$('.btn-edit').on('click', function(){
    var id = $(this).attr('data-id');
    var type = $(this).attr('data-type');

    $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getInfoID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#txPage').val(arr[0]['page']);
				modAct.find('#txType').val(type);
				modAct.find('#txID').val(id);
                modAct.find('#txContent').html(arr[0]['content']);
				modAct.modal('show');
                return false;
			}
		});	
		
});

</script>

