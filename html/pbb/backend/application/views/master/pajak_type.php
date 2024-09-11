<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Master Jenis Pajak</h3>
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
                                <th>Name</th>
                                <th>Pajak</th>
                                <th>Sanksi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
   
                                    $btn = $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'" data-name="'.$row['name'].'"');
                                    if($row['parameter'] == '1'){
                                        $tax = '<a href="'.base_url('parameter/'.$row['code']).'" >Parameter</a>';
                                    }else {
                                        $tax = $row['tax'].'%';
                                    }
                                    echo '
                                            <tr>
                                                <td>'.$row['id'].'</td>
                                                <td>'.$row['name'].'</td>
                                                <td>'.$tax.'</td>
                                                <td>'.$row['tax_fine'].' %</td>
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
  <form action="<?php echo base_url();?>master/pajak_type_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Perbaharui Jenis Pajak</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
    		    <div class="form-group">
    		        <label for="txName">Name :</label>
    		        <input type="text" id="txName" name="txName" class="form-control" readonly/>
    		    </div>
    		    <div class="form-group">
    		        <label for="txTax">Pajak (%):</label>
    		        <input  id="txTax" name="txTax" class="form-control"/>
    		    </div>
                <div class="form-group">
    		        <label for="txFine">Sanksi Pajak (%):</label>
    		        <input  id="txFine" name="txFine" class="form-control"/>
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
			url: "<?php echo base_url(); ?>api_master/getPajakTypeID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#txName').val(arr[0]['name']);
			    if(arr[0]['parameter'] == '1'){
			        modAct.find('#txTax').prop('disabled',true);
			    }else{
                    modAct.find('#txTax').prop('disabled',false);
				    modAct.find('#txTax').val(arr[0]['tax']);
                }
				modAct.find('#txID').val(id);
                modAct.find('#txFine').val(arr[0]['tax_fine']);
				modAct.modal('show');
                return false;
			}
		});	
		
});

</script>

