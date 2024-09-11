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
                    <div id="iBahan" class="border p-3 bg-white radius mb-3 ">
                        <h5>Bahan <button type="button" class="btn btn-primary btn-add-bahan">+ Add</button></h5>
    
                        <div class="row border-bottom border-top my-1 py-1" id="itemCo" >
                            <div class="col-md-5" id="iputType">
                                <select class="form-control cbType" name="cbType[]" >
                                    <option value="0">-Pilih Jenis-</option>
                                    <?php
                                        foreach($type as $row){
                                            echo '
                                                    <option value="'.$row['id'].'">'.$row['title'].'</option>
                                                 ';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-5" id="iputUse">
                                <div class="input-group mb-2">
                                <input type="text" class="form-control aUse" name="txUse[]"  data-a-dec="," data-a-sep=".">
                                <div class="input-group-prepend">
                                  <div class="input-group-text">m3</div>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-2">
                               
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
var count = 1;
$('.btn-add-bahan').click(function(){
    var type = $("#iputType").html();
    var use = $("#iputUse").html();
    $('#iBahan').append('<div class="row my-1 py-1 border-bottom" id="itemCo'+count+'"><div class="col-md-5">'+type+'</div><div class="col-md-5">'+use+'</div><div class="col-md-2"><button type="button" class="btn btn-primary btn-del" data-id="'+count+'">x</button></div></div>');
    $('.btn-del').click(function(){
        var id = $(this).attr('data-id');
        $("#itemCo"+id).remove();
    });

    count++;
});



    $('#btnCount').click(function(){
        var iValue = [];
        var iType = [];
        var sValue = false;
        var sType = false;
        $('select.cbType').each(function(){
            var valVal = $(this).val();
            if(valVal == 0 || valVal == ""){
                sValue = true;
            }
            iType.push(valVal);
    
        });
        $('input.aUse').each(function(){
            var valType = $(this).val();
            if(valType == 0 || valType == ""){
                sType = true;
            }
            iValue.push($(this).val()); 
        });
        var iTax = <?php echo $tax;?>;
        console.log(iType);
        if(sValue == true || sType == true){
            alert("Data yang diinput belum lengkap !")
        }else{
            $.ajax({
    			type: "POST",
                data:{iValue:iValue,iType:iType,iTax:iTax},
            			url: "<?php echo base_url('api/calcTaxMinerba'); ?>",
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