<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url("ext/datatables/datatables.min.css");?>"/>
<script type="text/javascript" src="<?php echo base_url("ext/datatables/datatables.min.js");?>"></script>
<style>
.dataTables_length{display:none;}
.dataTables_filter{display:none;}
</style>
<div class="container">
    <div class="content bg-light p-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <h3><?php echo $usaha['name'];?></h3>
                <h6>NPWPD: <?php echo $usaha['npwpd'];?></h6>
            </div>
            <div class="col-md-6 title-right">
                <button class="btn btn-small btn-payment" value="<?php echo $usaha['id'];?>">Bayar Pajak</button>
            </div>
        </div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="home" aria-selected="true">Menunggu Pembayaran</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#success" role="tab" aria-controls="profile" aria-selected="false">Terbayar</a>
          </li>

        </ul>
        <div class="tab-content p-3 bg-white" id="myTabContent">
          <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="home-tab">
          <table class="table table-bordered" id="myTable">
                <thead>
                    <tr>
                        <th>Masa Pajak</th>
                        <th>Jenis Pajak</th>
                        <th>Pajak</th>
                        <th>Sangsi</th>
                        <th>Total Pajak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($sppt as $row){
                            if($row['sppt_status'] == '0'){
                                $tax = $row['tax_value'];
                                $fine = $row['fine'];
                                $total = $tax + $fine;
                                
                                echo '
                                        <tr>
                                            <td>'.$row['masa_date'].'</td>
                                            <td>'.$row['pajak_type_name'].'</td>
                                            <td>'.$this->format->currency($tax).'</td>
                                            <td>'.$this->format->currency($fine).'</td>
                                            <td>'.$this->format->currency($total).'</td>
                                            <td>
                                                <button class="btn btn-icon" id="'.$row['id_sppt'].'" onClick="cetak(this.id)"><i class="icon-printer"></i></button>
                                                <a href="'.base_url('users/sppt/'.$row['id_sppt']).'" class="btn btn-icon"><i class="icon-search"></i></a>
                                                <button class="btn btn-icon" value="'.$row['id_sppt'].'"><i class="icon-bin"></i></button>
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
            <table class="table table-bordered" id="myTable">
                <thead>
                    <tr>
                        <th>Masa Pajak</th>
                        <th>Jenis Pajak</th>
                        <th>Pajak</th>
                        <th>Sangsi</th>
                        <th>Total Pajak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($sppt as $row){
                            if($row['sppt_status'] == '1'){
                                $tax = $row['tax_value'];
                                $fine = $row['fine'];
                                $total = $tax + $fine;
                                
                                echo '
                                        <tr>
                                            <td>'.$row['masa_date'].'</td>
                                            <td>'.$row['pajak_type_name'].'</td>
                                            <td>'.$this->format->currency($tax).'</td>
                                            <td>'.$this->format->currency($fine).'</td>
                                            <td>'.$this->format->currency($total).'</td>
                                            <td></td>
                                        </tr>
                                     ';
                            }
                        }
                    ?>
                </tbody>
            </table>         
          </div>
        </div>
        <div>
          
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
<script>
$(document).ready(function(){
    $('#myTable').DataTable({

    });
});
function cetak(id){
        window.open('<?php echo base_url();?>/users/print_sppt/'+id,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
    }
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
</script>