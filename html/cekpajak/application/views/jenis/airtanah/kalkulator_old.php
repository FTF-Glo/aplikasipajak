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
                        <label>Jenis Usaha</label>
                        <select class="form-control" name="cbUsaha" id="cbUsaha">
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
                            <input type="text" class="form-control aUse" id="txUse" data-a-dec="," data-a-sep=".">
                            <div class="input-group-prepend">
                              <div class="input-group-text">m3</div>
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
    $("#cbUsaha").change(function(){
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
        var iTax = <?php echo $tax;?>;
        if(iValue == "" || iClass ==""){
            alert("Data yang diinput belum lengkap !")
        }else{
            $.ajax({
    			type: "POST",
                data:{iValue:iValue,iClass:iClass,iTax:iTax},
            			url: "<?php echo base_url('api/CalcTaxAirTanah'); ?>",
            			success: function(msg){
                            var arr = JSON.parse(msg);
                            $("#txResult").val(arr.result);
                            
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
</script>