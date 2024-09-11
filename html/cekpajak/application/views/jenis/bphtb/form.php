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
        <?php echo form_open('users/add_sppt/bphtb'); ?>
        <h3>Formulir Hitung Pajak BPHTB</h3>
        <hr />
        <?php
          if($this->session->flashdata('msg') != null){
            echo $this->session->flashdata('msg');
          }
        ?>
        <div class="row">

            <div class="col-md-7">
                <div class="px-3">
					<input name="txNPOPTKP" id="txNPOPTKP" hidden>
					<input name="txNPOPKP" id="txNPOPKP" hidden>
					  
                    <div class="form-group">
                        <label>Nomor Objek Pajak / NOP</label>
                        <div class="row">
                          <div class="col-md-9">
                            <input class="form-control" name="txNOP" id="txNOP">
                            <span id="iFalse" class="text-danger"></span>
                            <input id="txIDPBB" name="txIDPBB" hidden>
                            <input id="txNJOP" name="txNJOP" hidden>
                          </div>
                          <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-block" id="btnCheckNOP">Cari</button>
                          </div>
                        </div>
                    </div>
                    <div id="iNOP" class="border p-3 bg-white radius mb-3 d-none">
                        <h4>Informasi PBB</h4>
                        <div class="form-group">
                            <label>Alamat</label>
                            <div>JL. <span id="iAddr">Address</span></div>
                            <div>RT: <span id="iRT"></span>  RW: <span id="iRW"></span></div>
                            <div><span id="iKel"></span>, <span id="iKec">Kecamatan</span>, <span id="iProv">Provinsi</span></div>
                            <div><strong>NJOP : Rp. <span id="iNJOP"></span>,-</strong></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label >Harga Jual / Transaksi / Lelang</label>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input type="text" class="form-control aUse" id="txValue" name="txValue" data-a-dec="," data-a-sep=".">

                          </div>
                    </div>
                    <div class="form-check my-4">
                        <input type="checkbox" class="form-check-input" id="chkCheck" name="chkCheck">
                        <label class="form-check-label" for="chkCheck">Warisan atau Hibah </label>
                    </div>
                    <div class="form-group mt-3">
                        <label>Pajak Yang Harus Dibayarkan</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <div class="input-group-text">Rp. </div>
                            </div>
                            <input class="form-control" name="txResult" id="txResult" readonly>
                           
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


<script>
$('#btnCheckNOP').click(function(){
  var iNOP = $("#txNOP").val();
    $.ajax({
			type: "POST",
          data:{iNOP:iNOP},
      			url: "<?php echo base_url('api/getCheckPBB'); ?>",
      			success: function(msg){
      			    if(msg != "false"){
                    $("#iFalse").html("");
      			        $('#iNOP').removeClass('d-none');
      			        $('#iPeriod').removeClass('d-none');
                    var arr = JSON.parse(msg);
                    var njop = parseInt(arr.op_price_bumi)+parseInt(arr.op_price_bangunan);
                    $("#iAddr").html(arr.loc_street);
                    $("#iKel").html(arr.loc_kel);
                    $("#iRT").html(arr.loc_rt);
                    $("#iRW").html(arr.loc_rw);
                    $("#iKec").html(arr.loc_kec);
                    $("#iKab").html(arr.loc_kab);
                    $("#iProv").html(arr.loc_prov);
                    $("#iNJOP").html(formatRupiah(njop));
                    $("#txNJOP").val(njop);
                    $("#txIDPBB").val(arr.id);
      			    }else{
      			        $("#iFalse").html("NOP Tidak terdaftar");
      			    }
      			}
      });	
});
    
$('#btnCount').click(function(){ //PERLU PERHITUNGAN DENDA
    var iValue = $("#txValue").val().split('.').join("");
    var iCheck = $("#chkCheck").is(':checked');
    var iNOP = $('#txNOP').val();
    var iIDPBB = $('#txIDPBB').val();
    var iNJOP = $('#txNJOP').val();
    if(iValue =="" || iNOP == "" || iIDPBB == "" || iNJOP == ""){
        alert("Data yang di-input belum lengkap !")
    }else{
        $.ajax({
			type: "POST",
            data:{iValue:iValue, iCheck:iCheck},
        			url: "<?php echo base_url('api/getCalcBPHTB'); ?>",
        			success: function(msg){
                  var arr = JSON.parse(msg);
                  $("#txResult").val(arr.bphtb_cur);
      						$('#txNPOPTKP').val(arr.npoptkp);
      						$('#txNPOPKP').val(arr.npopkp);
                  $("#btn-sppd").prop('disabled',false);
        			}
        });	
    }
});

function formatRupiah(bilangan){
	
  var	number_string = bilangan.toString(),
    sisa 	= number_string.length % 3,
    rupiah 	= number_string.substr(0, sisa),
    ribuan 	= number_string.substr(sisa).match(/\d{3}/g);
      
  if (ribuan) {
    separator = sisa ? '.' : '';
    rupiah += separator + ribuan.join('.');
  }
  return rupiah;
}
    
</script>
<script type="text/javascript" src="<?php echo base_url();?>ext/custom/autoNumeric.js" ></script>
<script> 
  jQuery(function($) {
      $('.aUse').autoNumeric('init',{mDec:'0'});    
  });
</script>
<script src="<?php echo base_url('ext/ui/jquery-ui.min.js');?>" type="text/javascript"></script>