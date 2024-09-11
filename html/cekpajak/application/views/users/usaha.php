<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url("ext/datatables/datatables.min.css");?>"/>
<script type="text/javascript" src="<?php echo base_url("ext/datatables/datatables.min.js");?>"></script>
<div class="container">
    <div class="content bg-light p-4">
        <div class="mb-5">
            <div class="row">
                <div class="col-md-6">
                    <h3><strong>Daftar Objek Pajak / Usaha</strong></h3>
                </div>
                <div class="col-md-6 title-right">
                    <!--
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                      Tambah Objek Pajak
                    </button>-->
                    <a href="<?php echo base_url('users/usaha_add');?>" class="btn btn-primary">Tambah Usaha</a>
                </div>
            </div>  
        </div>
   
        <?php
          if($this->session->flashdata('msg') != null){
            echo $this->session->flashdata('msg');
          }
        ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="home" aria-selected="true">Aktif</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#success" role="tab" aria-controls="profile" aria-selected="false">Menunggu Konfirmasi (<?php echo count($pending);?>)</a>
          </li>
        </ul>
        <div class="tab-content p-3 bg-white" id="myTabContent">
          <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="home-tab">
            <table id="myTable" class="table table-bordered table-striped table-sm responsive zero-configuration">
                  <thead>
                      <tr>
                          <th></th>
                          <th>Nama Usaha</th>
                          <th>Jenis Pajak</th>
                          <th class="text-center">NPWPD</th>
                          <th width="30"></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                          if($main != 0){
                          foreach($main as $key=>$row){
                              $key += 1;
                              $tax_type = "";
                              foreach($row['tax_type'] as $rowTT){
                                $tax_type .= '- '.$rowTT['name'].' ( '.$rowTT['last_payment'].' )<br />'; 
                              }
                              $npwpd = $row['npwpd'];
                              $btnPayment = ' <button class="btn btn-small btn-block btn-addloc my-1" value="'.$row['id'].'" dataName="'.$row['name'].'">Tambah Lokasi</button>
                                              <button class="btn btn-small btn-block btn-addtype my-1" value="'.$row['id'].'">Tambah Jenis Pajak</button>
                                          
                                              <button class="btn btn-small btn-block btn-payment my-1" value="'.$row['id'].'">Bayar Pajak</button>';
                              /*
                              $btnPayment = ' <button class="btn btn-small btn-block btn-addloc my-1" value="'.$row['id'].'" dataName="'.$row['name'].'">Tambah Lokasi</button>
                              <button class="btn btn-small btn-block btn-addtype my-1" value="'.$row['id'].'">Tambah Jenis Pajak</button>
                              <a href="'.base_url('users/usaha_detail/'.$row['id']).'" class="btn btn-small btn-block my-1">Data Pembayaran<a>
                              <button class="btn btn-small btn-block btn-payment my-1" value="'.$row['id'].'">Bayar Pajak</button>';
                              */
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
                        }
                      ?>
                  </tbody>
              </table> 
          </div>
          <div class="tab-pane fade" id="success" role="tabpanel" aria-labelledby="profile-tab">
          <table id="myTable" class="table table-bordered table-striped table-sm responsive zero-configuration">
                  <thead>
                      <tr>
                          <th></th>
                          <th>Nama Usaha</th>
                          <th>Jenis Pajak</th>
                          <th class="text-center">NPWPD</th>
                          <th class="text-center">Tanggal Pendaftaran</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                          if($pending != 0){
                          foreach($pending as $key=>$row){
                              $key += 1;
                              $tax_type = "";
                              foreach($row['tax_type'] as $rowTT){
                                $tax_type .= '- '.$rowTT['name'].' ( '.$rowTT['last_payment'].' )<br />'; 
                              }
                              $npwpd = $row['npwpd'];
                           
                              echo '
                                      <tr>
                                          <td>'.$key.'</td>
                                          <td><strong>'.$row['name'].'</strong><br />( '.$row['badan_usaha_name'].' )</td>
                                          <td>'.$tax_type.'</td>
                                          <td class="text-center">'.$npwpd.'</td>
                                          <td class="text-center">
                                             '.$row['created_date'].'
                                          </td>
                                      </tr>
                                   ';
                          }
                        }
                      ?>
                  </tbody>
              </table> 
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
        <h5 class="modal-title" id="exampleModalLabel">Tambah Lokasi </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?php echo form_open('users/add_loc/0/usaha'); ?>
      <div class="modal-body">
        

            <div class="form-group">
                <label>Nama Usaha</label>
                <input class="form-control" id="txModUsahaName" readonly>
            </div>
            <div class="form-group">
                <label>Jenis Pajak</label>
                <select class="form-control" id="txModType" name="txModType">
                    
                </select>
            </div>
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
  var iName = $(this).attr('dataName');
  $("#txModUsahaName").val(iName);
  $("#txModUsaha").val(iID);
  $.ajax({
      type: "POST",
      data: {iUsaha:iID},
      url: "<?php echo base_url(); ?>api/getListTaxType",
      success: function(msg){
        $("#col-payment").html("");
        var arr = JSON.parse(msg);
        for(var i=0; i<arr.length; i++){
              $("#txModType").append('<option value="'+arr[i].id+'">'+arr[i].name+'</option>');
             
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

