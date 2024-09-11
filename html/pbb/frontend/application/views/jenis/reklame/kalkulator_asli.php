<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container">
    <div class="content bg-light p-4">
        <h3><strong>Pajak <?php echo $title;?></strong></h3>
        <h4>Kalkulator Pajak</h4>
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
                        <label >Jenis Reklame</label>
                        <select name="cbType" id="cbType" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($type as $row){
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
                                <option value="1">1 sisi</option>
                                <option value="2">2 sisi</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="oLembar">
                        <label>Jumlah Reklame</label>
                        <input type="number" value="1" name="txLembar" id="txLembar" class="form-control">
                    </div>
                    <div class="form-group d-none" id="oDurasi">
                        <label>Durasi</label>
                        <input type="number" name="txDetik" id="txDetik" class="form-control">
                    </div>
                    <div class="form-group">
                        <label >Fungsi Ruang</label>
              
                        <select id="cbNFR" name="cbNFR" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($nfr as $row){
                                    echo '<option value="'.$row['value'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label >Fungsi Jalan</label>
          
                        <select id="cbNFJ" name="cbNFJ" class="form-control">
                            <option value="">-Pilih-</option>
                            <?php 
                                foreach($nfj as $row){
                                    echo '<option value="'.$row['value'].'">'.$row['title'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label >Fungsi Sudut Pandang</label>
         
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
                            <input class="form-control" id="txResult" readonly>
                          </div>
                    </div>
                    <div>
                        <button class="btn btn-primary" id="btnCount">Hitung</button>
                        <button class="btn btn-secondary" id="btnReset">Reset</button>
                    </div>
                </div>
            </div>
        </div>
        <a href="<?php echo base_url('jenis/'.$code);?>" class="btn btn-secondary"><i class="icon-arrow-left mr-2"></i>Kembali</a>
    </div>
</div>

<script>

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
                                console.log(arr);
                                $("#txResult").val(arr.result * iLembar);
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
            if(iPanjang == "" || iLebar=="" || iNFR=="" || iNFJ =="" || iNSP=="" || iSisi=="" ){
                alert("Data yang diinput belum lengkap !");
            }else{
                $.ajax({
        			type: "POST",
                    data:{iType:iType,iPanjang:iPanjang,iLebar:iLebar, iNFR:iNFR, iNFJ:iNFJ, iNSP:iNSP, iSisi:iSisi, iBasePrice:iBasePrice, iHighPrice:iHighPrice},
                			url: "<?php echo base_url('api/getCalcReklame2'); ?>",
                			success: function(msg){
                                var arr = JSON.parse(msg);
                                var result = arr.result;
                                $("#txResult").val(result*iLembar);
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