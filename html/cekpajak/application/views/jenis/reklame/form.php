<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container">
    <div class="content bg-light p-4">
    <?php echo form_open('users/add_sppt/reklame'); ?>
        <h3>Formulir Hitung Pajak Reklame</h3>
        <hr />
        <?php
          if($this->session->flashdata('msg') != null){
            echo $this->session->flashdata('msg');
          }
        ?>
        <div class="row my-2">
            <div class="col-md-6">
                <div class="p-3 bg-infotext">
                    <h4>Informasi</h4>
                    <?php echo $info;?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border p-3">
                    <input id="txmBasePrice" name="txmBasePrice" hidden />
                    <input id="txmHighPrice" name="txmHighPrice" hidden />
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
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-primary btn-block" id="btnAddLoc" disabled>Tambah Lokasi</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label >Jenis Reklame</label>
                        <select name="cbType" id="cbType" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($type_reklame as $row){
                                    echo '<option value="'.$row['id'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group row d-none" id="oLuas">
                        <div class="col-6">
                            <label>Panjang</label>
                            <input name="txP" id="txP" class="form-control">
                        </div>
                        <div class="col-6">
                            <label>Lebar</label>
                            <input name="txL" id="txL" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row d-none" id="oTinggi">
                        <div class="col-6">
                            <label>Tinggi</label>
                            <input name="txT" id="txT" class="form-control">
                        </div>
                        <div class="col-6">
                            <label>Jumlah Sisi</label>
                            <select name="txS" id="txS" class="form-control">
								<option value="0">-Pilih-</option>
                                <option value="1">1 sisi</option>
                                <option value="2">2 sisi</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="oLembar">
                        <label>Jumlah Lembar</label>
                        <input type="number" name="txLembar" value="1" id="txLembar" class="form-control">
                    </div>
                    <div class="form-group d-none" id="oDurasi">
                        <label>Durasi</label>
                        <input type="number" name="txDetik" id="txDetik" class="form-control">
                    </div>
                    <div class="form-group">
                        <label >Fungsi Ruang</label>
                        <input id="txNFR" name="txNFR" hidden/>
                        <select id="cbNFR" name="cbNFR" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($nfr as $row){
                                    echo '<option value="'.$row['value'].'" data-id="'.$row['id'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label >Fungsi Jalan</label>
                        <input id="txNFJ" name="txNFJ" hidden/>
                        <select id="cbNFJ" name="cbNFJ" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($nfj as $row){
                                    echo '<option value="'.$row['value'].'" data-id="'.$row['id'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label >Fungsi Sudut Pandang</label>
                        <input id="txNSP" name="txNSP" hidden/>
                        <select id="cbNSP" name="cbNSP" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($nsp as $row){
                                    echo '<option value="'.$row['value'].'" data-id="'.$row['id'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
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
                    <div>
                        <button type="button" class="btn btn-primary" id="btnCount">Hitung</button>
                        <button type="submit" class="btn btn-primary" id="btn-sppd" disabled="disabled">Buat SPPD</button>
                    </div>  
                </div>
            </div>
        </div>
        <a href="<?php echo base_url();?>" class="btn btn-secondary"><i class="icon-arrow-left mr-2"></i>Kembali</a>
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
        <button type="submit" class="btn btn-primary">Simpan</button>
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
        			    $("#txModUsahaName").val(arr[0].name);
        			}
        });	
  modAddLoc.modal('show');
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
            			         
            			     }else{
                                $("#cbLoc").prop('disabled', true);
            			     }
            			     $("#txModUsaha").val(iUsaha);
            			    
            			     
            			
            			}
            });	
        }else{
            $("#cbLoc").prop('disabled', true);
            $("#btnAddLoc").prop('disabled', true);
        }
       
    });
    $("#cbNFR").change(function(){
        var element = $(this).find('option:selected');
        var id = element.attr('data-id');
        $("#txNFR").val(id);
    });
    $("#cbNFJ").change(function(){
        var element = $(this).find('option:selected');
        var id = element.attr('data-id');
        $("#txNFJ").val(id);
    });
    $("#cbNSP").change(function(){
        var element = $(this).find('option:selected');
        var id = element.attr('data-id');
        $("#txNSP").val(id);
    });
    $("#cbType").change(function(){
        var iType = this.value;
        $('#oLuas').addClass("d-none");
        $('#oTinggi').addClass("d-none");
        $('#oDurasi').addClass("d-none");
        $.ajax({
			type: "POST",
            data:{iType:iType},
    			url: "<?php echo base_url('api/getReklameTypeInfo'); ?>",
    			success: function(msg){
                    var arr = JSON.parse(msg);
                    $('#txmBasePrice').val(arr.harga_dasar);
                    $('#txmHighPrice').val(arr.harga_tinggi);
                    if(arr.id_satuan == '1'){
                        $('#oLuas').removeClass("d-none");
                    }else if(arr.id_satuan == '2'){
                        $('#oLembar').removeClass("d-none");
                    }else if(arr.id_satuan == '4'){
                        $('#oDurasi').removeClass("d-none");
                    }
                    if(arr.id == '1' || arr.id == '2'){
                        $('#oTinggi').removeClass("d-none");
                    }
    			}
        });	
    });
    $('#btnCount').click(function(){
        var iType = $("#cbType").val();
        if(iType == 0){
            alert("Data yang diinput belum lengkap !");
        }else if(iType == '1' || iType == '2' ){
            var iTinggi = $("#txT").val();
            var iPanjang = $('#txP').val();
            var iLebar = $('#txL').val();
            var iNFR = $('#cbNFR').val();
            var iNFJ = $("#cbNFJ").val();
            var iNSP = $('#cbNSP').val();
            var iSisi = $("#txS").val();
            var iBasePrice = $("#txmBasePrice").val();
            var iHighPrice = $("#txmHighPrice").val();
            var iLembar = $("#txLembar").val();
            if(iTinggi == "" || iPanjang == "" || iLebar=="" || iNFR=="" || iNFJ =="" || iNSP=="" || iSisi==""){
                alert("Data yang diinput belum lengkap !");
            }else{
                $.ajax({
        			type: "POST",
                    data:{iTinggi:iTinggi,iPanjang:iPanjang,iLebar:iLebar, iNFR:iNFR, iNFJ:iNFJ, iNSP:iNSP, iSisi:iSisi, iBasePrice:iBasePrice, iHighPrice:iHighPrice},
                			url: "<?php echo base_url('api/getCalcReklame1'); ?>",
                			success: function(msg){
                                var arr = JSON.parse(msg);
                                $("#txResult").val(arr.result * iLembar);
                                $("#btn-sppd").prop('disabled',false);
                			}
                });	
            }
        }else if(iType=='3'){
            var iPanjang = $('#txP').val();
            var iLebar = $('#txL').val();
            var iNFR = $('#cbNFR').val();
            var iNFJ = $("#cbNFJ").val();
            var iNSP = $('#cbNSP').val();
            var iSisi = $("#txS").val();
            var iBasePrice = $("#txmBasePrice").val();
            var iHighPrice = $("#txmHighPrice").val();
            var iLembar = $("#txLembar").val();
            if(iPanjang == "" || iLebar=="" || iNFR=="" || iNFJ =="" || iNSP=="" || iSisi==""){
                alert("Data yang diinput belum lengkap !");
            }else{
                $.ajax({
        			type: "POST",
                    data:{iType:iType,iPanjang:iPanjang,iLebar:iLebar, iNFR:iNFR, iNFJ:iNFJ, iNSP:iNSP, iSisi:iSisi, iBasePrice:iBasePrice, iHighPrice:iHighPrice},
                			url: "<?php echo base_url('api/getCalcReklame2'); ?>",
                			success: function(msg){
                                var arr = JSON.parse(msg);
                                $("#txResult").val(arr.result * iLembar);
                                $("#btn-sppd").prop('disabled',false);
                			}
                });	
            }
        }

    });
    
</script>
<script type="text/javascript" src="<?php echo base_url();?>ext/custom/autoNumeric.js" ></script>
<script> 
  jQuery(function($) {
      $('.aUse').autoNumeric('init',{mDec:'0'});    
  });
</script>