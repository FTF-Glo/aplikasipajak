<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url("ext/datatables/datatables.min.css");?>"/>
<script type="text/javascript" src="<?php echo base_url("ext/datatables/datatables.min.js");?>"></script>
<div class="container">
    <div class="content p-4">
        <div class="mb-2">
            <div class="row">
                <div class="col-md-6">
                    <h3><strong>Daftar Objek Pajak / Usaha</strong></h3> 
                    
                </div>
                <div class="col-md-6 title-right">
                    <!--
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                      Tambah Objek Pajak
                    </button>-->
                    <a href="<?php echo base_url('users/usaha_add');?>" class="btn btn-primary">Tambah Data Usaha</a>
                </div>
            </div>  
        </div>
        <div class="mb-3">
            <?php
                foreach($main as $row){
            ?>
                    <div class="bg-white p-3 my-2">
                        <h3><?php echo $row['name'];?></h3>
                        <h6><?php echo $row['badan_usaha_name'];?></h6>
                        <?php
                            foreach($row['tax_type'] as $rt){
                        ?>
                                <div class="border my-2 p-3">
                                    <h5>Pajak <?php echo $rt['name'];?></h5>
                                    <div>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <td>NOP</td>
                                                    <td>Lokasi</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        <?php
                            }
                        ?>
                        
                    </div>
            <?php
                }
            ?>
            </div>
            
            
            
        <div class="card-body">
           
            
            
            <table id="myTable" class="table table-bordered table-striped table-sm responsive zero-configuration">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nama Usaha</th>
                        <th>Jenis Pajak</th>
                        <th class="text-center">NPWPD</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                        foreach($main as $key=>$row){
                            $key += 1;
                            $tax_type = "";
                            foreach($row['tax_type'] as $rowTT){
                              $tax_type .= '- '.$rowTT['name'].' ( '.$rowTT['last_payment'].' )<br />'; 
                            }
                            $npwpd = $row['npwpd'];
                            $btnPayment = ' <button class="btn btn-small btn-addloc" value="'.$row['id'].'">Tambah Lokasi</button>
                                            <button class="btn btn-small btn-addtype" value="'.$row['id'].'">Tambah Jenis Pajak</button>
                                            <a href="'.base_url('users/usaha_detail/'.$row['id']).'" class="btn btn-small">Data Pembayaran<a>
                                            <button class="btn btn-small btn-payment" value="'.$row['id'].'">Bayar Pajak</button>';
                            if($row['npwpd'] == 0){
                              $npwpd = "Menunggu Verifikasi";
                              $btnPayment = "";
                            }
                            echo '
                                    <tr>
                                        <td>'.$key.'</td>
                                        <td><strong>'.$row['name'].'</strong><br />( '.$row['badan_usaha_name'].' )</td>
                                        <td>'.$tax_type.'</td>
                                        <td class="text-center">'.$npwpd.'</td>
                                        <td class="text-center">
                                            '.$btnPayment.'
                                        </td>
                                    </tr>
                                 ';
                        }
                    ?>
                </tbody>
            </table>
            <div class="p-3 mt-3 bg-infotext">
                    <h4>Informasi</h4>
                    * Anda dapat melakukan pembayaran pajak setelah Usaha/Objek Pajak diverifikasi
                </div>
        </div>

      
    </div>
</div>

<div class="modal fade" id="modPayment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Pilih Objek Pajak</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="row" id="col-payment">
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modAddLoc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Pilih Objek Pajak</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="row" id="col-addloc">
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modAddType" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Pilih Objek Pajak</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="row" id="col-addtype">
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>


<script>
$(document).ready(function(){
    $('#myTable').DataTable();
});

var modPayment = $("#modPayment");
$('.btn-payment').click(function(){
  var iID = this.value;
  $.ajax({
            type: "POST",
            data: {ids:iID},
            url: "<?php echo base_url(); ?>api/getUserOP",
            success: function(msg){
              $("#col-payment").html("");
              var arr = JSON.parse(msg);
              if(arr.length != 0){
                for(var i=0; i<arr.length; i++){
                  var myCol = $('<div class="col-2 col-md-2 my-3"><div class="p-1 border"><div><a href="<?php echo base_url();?>users/payment/'+arr[i].code+'/'+iID+'"><img src="<?php echo base_url();?>images/icon/'+arr[i].icon+'"></a></div><div class="text-center"><a href="<?php echo base_url();?>users/payment/'+arr[i].code+'/'+iID+'">'+arr[i].name+'</a></div></div></div>');
                  $("#col-payment").append(myCol);
                }
              }else{
                var myCol = $('<div class="col">Belum ada jenis Pajak terdaftar</div>');
                $("#col-payment").append(myCol);
              }
            }
        });

  modPayment.modal('show');
});

var modAddLoc = $("#modAddLoc");
$('.btn-addloc').click(function(){
  var iID = this.value;
  $.ajax({
            type: "POST",
            data: {ids:iID},
            url: "<?php echo base_url(); ?>api/getUserOP",
            success: function(msg){
              $("#col-addloc").html("");
              var arr = JSON.parse(msg);
              if(arr.length != 0){
                for(var i=0; i<arr.length; i++){
                  var myCol = $('<div class="col-2 col-md-2 my-3"><div class="p-1 border"><div><a href="<?php echo base_url();?>users/addloc/'+arr[i].code+'/'+iID+'"><img src="<?php echo base_url();?>images/icon/'+arr[i].icon+'"></a></div><div class="text-center"><a href="<?php echo base_url();?>users/payment/'+arr[i].code+'/'+iID+'">'+arr[i].name+'</a></div></div></div>');
                  $("#col-addloc").append(myCol);
                }
              }else{
                var myCol = $('<div class="col">Belum ada jenis Pajak terdaftar</div>');
                $("#col-addloc").append(myCol);
              }
            }
        });

  modAddLoc.modal('show');
});

var modAddType = $("#modAddType");
$('.btn-addtype').click(function(){
  var iID = this.value;
  $.ajax({
            type: "POST",
            data: {ids:iID},
            url: "<?php echo base_url(); ?>api/getPajakTypeUn",
            success: function(msg){
              $("#col-addtype").html("");
              var arr = JSON.parse(msg);
              console.log(arr);
              if(arr.length != 0){
                for(var i=0; i<arr.length; i++){
                  var myCol = $('<div class="col-2 col-md-2 my-3"><div class="p-1 border"><div><a href="<?php echo base_url();?>users/add_tax/'+arr[i].id+'/'+iID+'"><img src="<?php echo base_url();?>images/icon/'+arr[i].icon+'"></a></div><div class="text-center"><a href="<?php echo base_url();?>users/add_tax/'+arr[i].id+'/'+iID+'">'+arr[i].name+'</a></div></div></div>');
                  $("#col-addtype").append(myCol);
                }
              }
            }
        });

  modAddType.modal('show');
});
</script>

