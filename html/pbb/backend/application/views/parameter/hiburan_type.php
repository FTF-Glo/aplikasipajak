<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Pajak Hiburan</h3>
                </div>
                <div class="col-md-4 menu-right mb-1">
                    <button class="btn btn-primary" id="btn-add"><i class="fa fa-plus mr-1"></i>Tambah Jenis Hiburan</button>
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
                                <th>Tax</th>
                                <th>Parameter</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
                                    $omzet_range = "";
                                    foreach($omzet as $om){
                                        if($row['id'] == $om['id_hiburan']){
                                            if($om['range_max'] > 0){
                                                $range_max = $om['range_max'];
                                            }else{
                                                $range_max = "dst";
                                            }
                                            $omzet_range .= $om['range_min'].' - '.$range_max.' => <strong>'.$om['nilai'].'</strong><br />';
                                        }
                                    }
                                   // href($link, $text, $class='', $attr='')
                                    $btn =  $this->html->href(base_url('parameter/hiburan_range/'.$row['id']),'<i class="fa fa-th-list"></i>','btn btn-icon mr-1','','data-toggle="tooltip" data-placement="top"
                                    title="Parameter Hiburan"').
                                            $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'"');
                                            $this->html->btn_icon('Delete','fa fa-trash','btn-del','','data-id="'.$row['id'].'" data-name="'.$row['title'].'"');
                          
                          
                                    echo '
                                            <tr>
                                                <td>'.$row['id'].'</td>
                                                <td>'.$row['title'].'</td>
                                                <td>'.$row['tax'].' %</td>
                                                <td>'.$omzet_range.'</td>
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
  <form action="<?php echo base_url();?>parameter/hiburan_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel"></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		        <input name="txID" id="txID" value="" hidden>
    		    <div class="form-group">
    		        <label for="txTitle">Name :</label>
    		        <input type="text" id="txTitle" name="txTitle" class="form-control"/>
    		    </div>
    		    <div class="form-group">
    		        <label for="txTax">Pajak (%):</label>
    		        <input  id="txTax" name="txTax" class="form-control"/>
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
  <form action="<?php echo base_url();?>parameter/hiburan_del" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Konfirmasi hapus</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		  <input name="tbxPage" value="<?php echo current_url();?>" hidden>
			<input id="tbxID" name="tbxID" hidden />
			<input name="txTypeID" value="<?php echo $type[0]['id'];?>" hidden >
			<div>
				Yakin ingin menghapus "<span class="tbxName"></span>"
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button type="submit" class="btn btn-primary">Ya</button>
		  </div>
		</div>
	  </div>
  </form>
</div>


<script>

var modAct = $('#modAct');
$('#btn-add').on('click', function(){
    modAct.find('#exampleModalLabel').html('Tambah Jenis Hiburan');
    modAct.modal('show');
    return false;
});
$('.btn-edit').on('click', function(){
    var id = $(this).attr('data-id');
    
    $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getPajakHiburanTypeID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);
			    modAct.find('#exampleModalLabel').html('Perbaharui Jenis Hiburan');
			    modAct.find('#txTitle').val(arr[0]['title']);
				modAct.find('#txTax').val(arr[0]['tax']);
				modAct.find('#txID').val(id);
				modAct.modal('show');
                return false;
			}
		});	
		
});

var modDel = $('#modDel');
    $('.btn-del').on('click', function(){
        var id = $(this).attr('data-id');
        var title = $(this).attr('data-title');
		modDel.find("#tbxID").val(id);
		modDel.find(".tbxName").html(title);
        modDel.modal('show');
        return false;
    });
    
</script>

