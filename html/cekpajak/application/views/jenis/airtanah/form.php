<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
 <link rel="stylesheet" type="text/css" href="<?php echo base_url('ext/ui/jquery-ui.min.css');?>">
 <style>
 .ui-datepicker-calendar {
    display: none;
    }
 </style>
<div class="container">
    <div class="content bg-light p-4">
        <?php echo form_open('users/add_sppt/airtanah'); ?>
        <h3>Formulir Hitung Pajak Air Tanah</h3>
        <hr />
		<?php
          if($this->session->flashdata('msg') != null){
            echo $this->session->flashdata('msg');
          }
        ?>
        <div class="row">

            <div class="col-md-7">
                <div class="px-3">
                    <div class="form-group">
                        <label>Nama Usaha</label>
                        <div class="row">
                            <div class="col-8">
                            <?php
                                if($sel == 0){
                                    echo '<select name="cbUsaha" id="cbUsaha" class="form-control">';
                                    echo '<option value="0">-Pilih Usaha-</option>';
                                    foreach($users_usaha as $row){
                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                    }
                                    echo '</select>';
                                    echo '<input id="txUsersUsahaID" name="txUsersUsahaID" hidden>';
                                }else{
                                    echo '<input class="form-control" name="txUsahaName" readonly value="'.$users_usaha[0]['name'].'">';
                                    echo '<input class="form-control" name="txUsersUsahaID" hidden value="'.$users_usaha[0]['id'].'">';
                                }
                            ?>
                            </div>
                            <div class="col-4">
                                <a href="<?php echo base_url('users/usaha_add');?>" class="btn btn-primary btn-block">Tambah Usaha</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Lokasi / Nomor Objek Pajak (NOP)</label>
                        <div class="row">
                            <div class="col-8">
                                <select class="form-control" name="cbLoc" id="cbLoc" disabled>
                                    <?php
                                        foreach($loc as $row){
                                            echo '
                                                    <option value="'.$row['id'].'">'.$row['name'].'</option>
                                                 ';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-primary btn-block" id="btnAddLoc" disabled>Tambah Lokasi</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Usaha</label>
                        <select class="form-control" name="cbType" id="cbType">
                            <option value="0">-Pilih Jenis-</option>
                            <?php
                                foreach($usaha as $row){
                                    echo '
                                            <option value="'.$row['id_class'].'">'.$row['title'].'</option>
                                         ';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input class="form-control" id="txClassName" readonly>
                        <input class="form-control" id="txClassID" hidden>
                    </div>
                    <div class="form-group">
                        <label >Jumlah Pemakaian (meteran akhir - meteran awal/bulan lalu)</label>
                          <div class="input-group mb-2">
                            <input type="text" class="form-control aUse" name="txUse" id="txUse" data-a-dec="," data-a-sep=".">
                            <div class="input-group-prepend">
                              <div class="input-group-text">m3</div>
                            </div>
                          </div>
                    </div>
                    <div class="form-group">
                        <label>Masa Pajak</label>
                        <input class="datepicker form-control font-size-small" autocomplete="off" auto name="txMasa" id="txMasa"  placeholder="">
                    </div>
                    <div class="form-group">
                        <label>Pajak Yang Harus Dibayarkan</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input class="form-control" id="txResult" name="txResult" readonly>
                          </div>
                    </div>
                    <div class="form-group">
                        <label>Denda yang harus Dibayarkan</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input class="form-control" id="txFine" name="txFine" readonly>
                          </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="btnCount">Hitung</button>
                        <button type="submit" class="btn btn-primary" id="btn-sppd" disabled="disabled">Buat SPPD</button>
                    </div>                    
                </div>
            </div>
            <div class="col-md-5">
                <div class="p-3 bg-infotext">
                    <h4>Informasi</h4>
                    <?php echo $info;?>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modAddLoc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Lokasi </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <?php echo form_open('users/add_loc/'.$type[0]['code']); ?>
      <div class="modal-body">
        <div class="form-group">
            <label>Pajak</label>
            <input class="form-control" value="<?php echo $type[0]['name'];?>" readonly>
        </div>
        <div class="form-group">
            <label>Name</label>
            <input class="form-control" id="txModUsahaName" readonly>
        </div>
        <input name="txModType" value="<?php echo $type[0]['id'];?>" hidden>
        <input name="txModUsaha"  id="txModUsaha" hidden>
        <div class="form-group">
            <label>Nomor Objek Pajak (NOP)</label>
            <input name="txNOP" class="form-control">
        </div>
        <div class="form-group">
            <label>Nama Wilayah (hanya sebagai identifikasi)</label>
            <input name="txName" class="form-control">
        </div>
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="txDesc" class="form-control"></textarea>
        </div>
        <div class="form-group">
        <label>Coordinates</label>
            <div class="row">
                
                <div class="col-6 form-group">
                    <input name="txLong" class="form-control" placeholder="Longitude">
                </div>
                <div class="col-6 form-group">
                    <input name="txLat" class="form-control" placeholder="Latitude">
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" >Simpan</button>
      </div>
	  </form>
    </div>
  </div>
</div>

<script>
var modAddLoc = $("#modAddLoc");
$('#btnAddLoc').click(function(){
    var iUsaha = $("#txModUsaha").val();
    $.ajax({
			type: "POST",
            data:{iUsaha:iUsaha},
        			url: "<?php echo base_url('api/getUsahaByID'); ?>",
        			success: function(msg){
        			    var arr = JSON.parse(msg);
        			    console.log(arr);
        			    $("#txModUsahaName").val(arr[0].name);
        			}
        });	
    
  modAddLoc.modal('show');
});
    $("#cbLoc").change(function(){
        var iLoc = this.value;
        var iType = '<?php echo $tax_type_id;?>'; 
        
        $.ajax({
			type: "POST",
            data:{iLoc:iLoc, iType:iType},
        			url: "<?php echo base_url('api/getLastMasa'); ?>",
        			success: function(msg){
        			     $('#txMasa').val(msg);
        			}
        });	
    });
    $("#cbUsaha").change(function(){
        var iUsaha = this.value;
        if(iUsaha != 0){
            $("#btnAddLoc").prop('disabled', false);
             var iType = '<?php echo $tax_type_id;?>';
             $('#cbLoc option').remove();
             $.ajax({
    			type: "POST",
                data:{ids:iUsaha, iType:iType},
            			url: "<?php echo base_url('api/getLoc'); ?>",
            			success: function(msg){
            			     var arr = JSON.parse(msg);
            			     for(var i=0; i<arr.length; i++){
                                $("#cbLoc").append('<option value="'+arr[i].id+'">'+arr[i].name+' ('+arr[i].nop+')</option>');
                                $("#txUsersUsahaID").val(iUsaha);
            			     }
                             if(arr.length!=0){
            			         $("#cbLoc").prop('disabled', false);
            			         $("#txModUsaha").val(iUsaha);
            			     }else{
                                $("#cbLoc").prop('disabled', true);
            			     }
            			}
            });	
        }else{
            $("#cbLoc").prop('disabled', true);
            $("#btnAddLoc").prop('disabled', true);
        }
       
    });
    $("#cbType").change(function(){
        var iClass = this.value;
        $.ajax({
			type: "POST",
            data:{ids:iClass},
        			url: "<?php echo base_url('api/getClass'); ?>",
        			success: function(msg){
                        var arr = JSON.parse(msg);
                        $("#txClassName").val(arr.class);
                        $("#txClassID").val(arr.id);
        			}
        });	
    });
    
    $('#btnCount').click(function(){
        var iValue = $('#txUse').val();
        var iClass = $('#txClassID').val();
        var iTax = <?php echo $type[0]['tax'];?>;
        var iMasa = $('#txMasa').val();
        var iLoc = $('#cbLoc').val();
        if(iValue == "" || iClass =="" || iMasa == "" || iLoc == ""){
            alert("Data yang diinput belum lengkap !")
        }else{
            $.ajax({
    			type: "POST",
                data:{iValue:iValue,iClass:iClass,iTax:iTax,iMasa:iMasa},
            			url: "<?php echo base_url('api/CalcTaxAirTanah'); ?>",
            			success: function(msg){
                            var arr = JSON.parse(msg);
                            $("#txResult").val(arr.result);
                            $('#txFine').val(arr.fine);
                            $("#btn-sppd").prop('disabled',false);
            			}
            });	
        }
    });
    
    $('#btnReset').click(function(){
        $("#txResult").val("");
        $("#txClassName").val("");
        $("#txClassID").val("");
        $("#txUse").val("");
    })
</script>
<script type="text/javascript" src="<?php echo base_url();?>ext/custom/autoNumeric.js" ></script>
<script> 
  jQuery(function($) {
      $('.aUse').autoNumeric('init',{mDec:'0'});    
  });
  	$(function() {
        var d = new Date();
        var n = d.getFullYear();
    $('.datepicker').datepicker( {
        yearRange: '2000:'+n,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy-mm',
        onClose: function(dateText, inst) { 
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    });
      });
</script>

<script src="<?php echo base_url('ext/ui/jquery-ui.min.js');?>" type="text/javascript"></script>