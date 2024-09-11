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
                    <div class="form-group">
                        <label>Jenis Hiburan</label>
                        <select class="form-control" name="cbType" id="cbType">
                            <option value="0">-Pilih Jenis-</option>
                            <?php
                                foreach($hiburan_type as $row){
                                    echo '
                                            <option value="'.$row['tax'].'">'.$row['title'].'</option>
                                         ';
                                }
                            ?>
                        </select>
                        <input id="txTax" hidden >
                    </div>
                    <div class="form-group">
                        <label>DPP</label>
                        <select class="form-control" name="cbDPP" id="cbDPP">
                            <option value="1">DPP</option>
                            <option value="0">non DPP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label >Omzet</label>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input type="text" class="form-control aUse" id="txOmzet" name="txOmzet" data-a-dec="," data-a-sep=".">

                          </div>
                    </div>
                    <div class="form-group">
                        <label >Jangka Waktu</label>
                          <div class="input-group mb-2">
                         
                            <input type="number" class="form-control" name="txSpan" id="txSpan">
                            <div class="input-group-prepend">
                              <div class="input-group-text">bulan</div>
                            </div>
                          </div>
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
        var iTax = this.value;
        $('#txTax').val(iTax);
    });
    
    
    $('#btnCount').click(function(){ //PERLU PERHITUNGAN DENDA
        var iTax = $('#txTax').val();
        var iSpan = $('#txSpan').val();
        var iOmzet = $('#txOmzet').val().split('.').join("");
        var iFine = <?php echo $tax_fine;?>;
        var iDPP = $('#cbDPP').val();
        if(iTax == "" || iSpan =="" || iOmzet==""){
            alert("Data yang di-input belum lengkap !")
        }else{
            $.ajax({
    			type: "POST",
                data:{iTax:iTax,iSpan:iSpan,iOmzet:iOmzet,iFine:iFine,iDPP:iDPP},
            			url: "<?php echo base_url('api/calcTaxOmzet'); ?>",
            			success: function(msg){
                            var arr = JSON.parse(msg);
                            $("#txResult").val(arr.result);
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