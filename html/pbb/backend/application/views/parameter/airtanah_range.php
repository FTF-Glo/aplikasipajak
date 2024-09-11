<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Parameter Pajak Minerba (Mineral dan Pertambangan)</h3>
                </div>
                <div class="col-md-4 menu-right mb-1">
                    <a href="<?php echo base_url().$parent;?>" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i>Kembali</a>
                    <button class="btn btn-primary" id="btn-add"><i class="fa fa-plus mr-1"></i>Tambah Parameter</button>
                </div>
            </div>
        
            <div class="card">
                <div class="card-body">
                Kelas Usaha:
                  <h3><?php echo $type[0]['title'];?></h3>
					<?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			        ?>
                    <table id="myTable" class="table table-bordered table-striped table-hover table-select">
                        <thead>
                            <tr>
                                <th>Ranges</th>
                                <th>Nilai</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($range as $row){
                                    $range2 = explode(",",$row['ranges']);
                                    
                                    $btn =  $this->html->btn_icon('Edit','fa fa-edit','btn-edit','','data-id="'.$row['id'].'" data-range-min="'.$range2[0].'" data-range-max="'.$range2[1].'"').
                                            $this->html->btn_icon('Hapus','fa fa-trash','btn-del','','data-id="'.$row['id'].'" data-range="'.$row['ranges'].'"');
                                    echo '
                                            <tr>
                                                <td>'.$row['ranges'].'</td>
                                                <td>'.$row['nilai'].'</td>
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

</script>

<!--- MODAL START-->
<div class="modal fade" id="modAdd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>parameter/airtanah_range_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Tambah Baru</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		    <input name="txTypeID" value="<?php echo $type[0]['id'];?>" hidden >
            <div class="form-group">
		        <label for="tbxRanges">Range Penggunaan :</label>
		        <div class="row">
                     <div class="input-group col">
                        <input type="text" class="form-control" name="tbxRange1" id="tbxRange1a" placeholder="Awal" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                          <span class="input-group-text" id="basic-addon2">M<sup>3</sup></span>
                        </div> 
                        
                      </div>
                      <div class="input-group col">
                        <input type="text" class="form-control" name="tbxRange2" id="tbxRange2a" placeholder="Akhir" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                          <span class="input-group-text" id="basic-addon2">M<sup>3</sup></span>
                        </div> 
                      </div>
		        </div>
		        <div class="mt-1"><code>kosongkan range akhir, apabila ingin nilai akhir tak terhingga </code></div>
		    </div>
            <div class="form-group">
		        <label for="tbxNilai">Nilai:</label>
		        <input type="text" name="tbxNilai" placeholder="" class="form-control" required />
		    </div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button type="submit" class="btn btn-primary">Simpan</button>
		  </div>
		</div>
	  </div>
  </form>
</div>
<div class="modal fade" id="modEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>parameter/airtanah_range_act" method="post" accept-charset="utf-8">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Ubah data</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		    <input name="tbxPage" value="<?php echo current_url();?>" hidden>
            <input name="txID" id="txID" hidden />
		    <input name="txTypeID" value="<?php echo $type[0]['id'];?>" hidden >
            <div class="form-group">
		        <label for="tbxRanges">Range Penggunaan :</label>
		        <div class="row">
                     <div class="input-group col">
                        <input type="text" class="form-control" name="tbxRange1" id="tbxRange1b" placeholder="Awal" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                          <span class="input-group-text" id="basic-addon2">M<sup>3</sup></span>
                        </div> 
                        
                      </div>
                      <div class="input-group col">
                        <input type="text" class="form-control" name="tbxRange2" id="tbxRange2b" placeholder="Akhir" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                          <span class="input-group-text" id="basic-addon2">M<sup>3</sup></span>
                        </div> 
                        
                      </div>
		        </div>
		    </div>
            <div class="form-group">
		        <label for="tbxNilai">Nilai:</label>
		        <div class="input-group-append">
                <input type="text" class="form-control" name="tbxNilai" id="tbxNilai" placeholder="" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                          <span class="input-group-text" id="basic-addon2"></span>
                        </div>
                </div>
		    </div>
		    
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				<button type="submit" class="btn btn-primary">Simpan</button>
			</div>
			</div><!--modal-body-->
		</div><!--modal-content-->
	  </div><!---modal-dialog-->
  </form>
</div>

<div class="modal fade" id="modDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form action="<?php echo base_url();?>parameter/airtanah_range_del" method="post" accept-charset="utf-8">
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
				Yakin ingin menghapus "<span class="tbxRanges"></span>"
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

	var modAdd = $('#modAdd');
    $('#btn-add').on('click', function(){
        modAdd.modal('show');
        return false;
    });
	var modEdit = $('#modEdit');
    $('.btn-edit').on('click', function(){
        var id = $(this).attr('data-id');
        var range_min = $(this).attr('data-range-min');
        var range_max = $(this).attr('data-range-max');
		modEdit.find("#txID").val(id);
        modEdit.find("#tbxRange1b").val(range_min);
		modEdit.find("#tbxRange2b").val(range_max);
        $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>api_master/getPajakAirTanahID/"+id,
			success: function(data){
			    var arr = JSON.parse(data);

		        modEdit.find("#tbxNilai").val(arr[0].nilai);
				modEdit.modal('show');
			}
		});	
        return false;
    });
	var modDel = $('#modDel');
    $('.btn-del').on('click', function(){
        var id = $(this).attr('data-id');
        var ranges = $(this).attr('data-range');
		modDel.find("#tbxID").val(id);
		modDel.find(".tbxRanges").html(ranges);
        modDel.modal('show');
        return false;
    });
    

</script>
