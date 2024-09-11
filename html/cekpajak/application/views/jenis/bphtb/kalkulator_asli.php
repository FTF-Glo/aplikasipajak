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
                    <!--
                    <h5>Tanah/Bumi</h5>
                    <div class="form-group">
                        <label >Luas Tanah</label>
                          <div class="input-group mb-2">
                            <input type="text" class="form-control" name="txLuasTanah" id="txLuasTanah">
                            <div class="input-group-prepend">
                              <div class="input-group-text">m<sup>2</sup></div>
                            </div>
                          </div>
                    </div>
                    <div class="form-group">
                        <label >NJOP Tanah (diisi berdasarkan SPPT PBB terakhir sebelum peralihan hak)</label>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input type="text" class="form-control aUse" id="txNJOPTanah" name="txNJOPTanah" data-a-dec="," data-a-sep=".">

                        </div>
                    </div>
                    <hr />
                    <h5>Bangunan</h5>
                    <div class="form-group">
                        <label >Luas Tanah</label>
                          <div class="input-group mb-2">
                            <input type="text" class="form-control" name="txLuasBangunan" id="txLuasBangunan">
                            <div class="input-group-prepend">
                              <div class="input-group-text">m<sup>2</sup></div>
                            </div>
                          </div>
                    </div>
                    <div class="form-group">
                        <label >NJOP Bangunan (diisi berdasarkan SPPT PBB terakhir sebelum peralihan hak)</label>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input type="text" class="form-control aUse" id="txNJOPBangunan" name="txNJOPBangunan" data-a-dec="," data-a-sep=".">
                        </div>
                    </div>
                    -->
                    <div class="form-group">
                        <label>Harga Transaksi Jual atau Lelang</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input type="text" class="form-control aUse" id="txValue" name="txValue" data-a-dec="," data-a-sep=".">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="chkCheck" name="chkCheck">
                        <label class="form-check-label" for="exampleCheck1">Warisan atau Hibah </label>
                    </div>
                    
                    <hr />
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

    
    $('#btnCount').click(function(){ //PERLU PERHITUNGAN DENDA
        var iValue = $("#txValue").val().split('.').join("");
        var iCheck = $("#chkCheck").is(':checked');
        if(iValue ==""){
            alert("Data yang di-input belum lengkap !")
        }else{
            $.ajax({
    			type: "POST",
                data:{iValue:iValue, iCheck:iCheck},
            			url: "<?php echo base_url('api/getCalcBPHTB'); ?>",
            			success: function(msg){
                            var arr = JSON.parse(msg);
                            $("#txResult").val(arr.bphtb_cur);
                            console.log(arr);
            			}
            });	
        }

    });
    
    $('#btnReset').click(function(){
        $("#txResult").val("");
        $("#txTax").val("");
        $("#txOmzet").val("");
        $("#txSpan").val("");
    })
</script>
<script type="text/javascript" src="<?php echo base_url();?>ext/custom/autoNumeric.js" ></script>
<script> 
  jQuery(function($) {
      $('.aUse').autoNumeric('init',{mDec:'0'});    
  });
</script>