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
        <?php echo form_open('users/add_sppt/pbb'); ?>
        <h3>Formulir Hitung Pajak Hotel</h3>
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
                        <label>Nomor Objek Pajak / NOP</label>
                        <input class="form-control" name="txNOP" id="txNOP">
                        <input id="txIDPBB" name="txIDPBB" hidden>
                        <input id="txResult" name="txResult" hidden>
                        <input id="txFine" name="txFine" value="" hidden>
                        <input id="txMasa" name="txMasa" value="" hidden>
                        <input id="linkPay" value="<?php echo base_url('users/add_sppt_pbb/');?>" hidden>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="btnCount">Periksa</button>
                    </div>
                    <div id="iNOP" class="border p-3 bg-white radius mb-3 d-none">
                        <h4>Informasi PBB</h4>
                        <div class="form-group">
                            <label>Alamat</label>
                            <div>JL. <span id="iAddr">Address</span></div>
                            <div id="iKel"></div>
                            <div>RT: <span id="iRT"></span>  RW: <span id="iRW"></span></div>
                            <div id="iKec">Kecamatan</div>
                            <div id="iKab">Kabupaten</div>
                            <div id="iProv">Provinsi</div>
                        </div>
                    </div>
                    <div id="iPeriod" class="border p-3 bg-white radius mb-3 d-none">
                        <h5>Periode PBB yang belum terbayar</h5>
                        <hr />
                        <div id="iListPeriod">
                        </div>
                    </div>
                    <div id="iFalse" class="c-red mb-3"></div>
                                    
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
$('#btnCount').click(function(){ //PERLU PERHITUNGAN DENDA
    var iNOP = $('#txNOP').val();
    var linkpay = $('#linkPay').val();
    if(iNOP ==""){
        alert("Data yang di-input belum lengkap !")
    }else{
        $.ajax({
			type: "POST",
            data:{iNOP:iNOP},
        			url: "<?php echo base_url('api/getCheckNOPPBB'); ?>",
        			success: function(msg){
        			    if(msg != "false"){
                            $("#iFalse").html("");
        			        $('#iNOP').removeClass('d-none');
        			        $('#iPeriod').removeClass('d-none');
                            var arr = JSON.parse(msg);
                            $('#txIDPBB').val(arr[0].id);
                            $('#txTax').val(arr[0].pbb);
                            $("#iAddr").html(arr[0].loc_street);
                            $("#iKel").html(arr[0].loc_kel);
                            $("#iRT").html(arr[0].loc_rt);
                            $("#iRW").html(arr[0].loc_rw);
                            $("#iKec").html(arr[0].loc_kec);
                            $("#iKab").html(arr[0].loc_kab);
                            $("#iProv").html(arr[0].loc_prov);
                            $("#btn-sppd").prop('disabled',false);
                            
                            for(var i=0; i<arr.length; i++){
                                $('#iListPeriod').append('<div><strong>'+arr[i].masa+'</strong><a href="'+linkpay+arr[i].id+'" class="btn btn-primary ml-5">Bayar PBB</a></div><hr />');
                            }
                            
        			    }else{
        			        $("#iFalse").html("NOP Tidak terdaftar");
                            $("#btn-sppd").prop('disabled',true);
        			    }
        			}
        });	
        
        
    }
});
 
</script>