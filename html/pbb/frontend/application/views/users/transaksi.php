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
        <h3>Daftar Transaksi</h3>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="home" aria-selected="true">Menunggu Pembayaran</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="ver-tab" data-toggle="tab" href="#verification" role="tab" aria-controls="verification" aria-selected="true">Menunggu Verifikasi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#success" role="tab" aria-controls="profile" aria-selected="false">Terbayar</a>
          </li>

        </ul>
        <div class="tab-content p-3 bg-white" id="myTabContent">
          <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="home-tab">
          <table class="table table-hover table-bordered" id="myTable">
                <thead>
                    <tr>
                        <th>Tanggal Buat</th>
                        <th>Usaha/Lokasi</th>
                        <th>Masa Pajak</th>
                        <th>Jenis Pajak</th>
                        <th>Total Pajak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($main as $row){
                            if($row['sppt_status'] == '1'){
                                $tax = $row['tax_value'];
                                $fine = $row['fine'];
                                $total = $tax + $fine;
                                switch ($row['code']){
                                    case 'pbb':
                                        $btn = '<button class="btn btn-icon" id="'.$row['id_sppt'].'" onClick="cetak_pbb(this.id,'.$row['id_pbb'].')"><i class="icon-printer"></i></button>
                                                <a href="'.base_url('users/sppt_pbb/'.$row['id_sppt'].'/'.$row['id_pbb']).'" class="btn btn-icon"><i class="icon-search"></i></a>
                                                <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                    break;
                                    case 'bphtb':
                                        $btn = '<button class="btn btn-icon" id="'.$row['id_sppt'].'" onClick="cetak_bphtb(this.id,'.$row['id_pbb'].')"><i class="icon-printer"></i></button>
                                        <a href="'.base_url('users/sppt_bphtb/'.$row['id_sppt'].'/'.$row['id_pbb']).'" class="btn btn-icon"><i class="icon-search"></i></a>
                                        <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                    break;
                                    default:
                                        $btn = '<button class="btn btn-icon" id="'.$row['id_sppt'].'" onClick="cetak(this.id)"><i class="icon-printer"></i></button>
                                                <a href="'.base_url('users/sppt/'.$row['id_sppt']).'" class="btn btn-icon"><i class="icon-search"></i></a>
                                                <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                }
                                if($row['pajak_type_code'] == 'pbb'){
                                    $usaha = $row['loc_street'];
                                }else{
                                    $usaha = $row['usaha_name'];
                                }
                                echo '
                                        <tr>
                                             <td>'.$row['created_date'].'</td>
                                             <td>'.$usaha.'<br /><strong>NOP: '.$row['nop'].'</strong></td>
                                            <td>'.$row['masa_date'].'</td>
                                            <td>'.$row['pajak_type_name'].'</td>

                                            <td>'.$this->format->currency($total).'</td>
                                            <td>
                                                '.$btn.'
                                            </td>
                                        </tr>
                                     ';
                            }
                        }
                    ?>
                </tbody>
            </table>         
          </div>
          <div class="tab-pane fade show" id="verification" role="tabpanel" aria-labelledby="ver-tab">
          <table class="table table-hover table-bordered" id="myTable2">
                <thead>
                    <tr>
                        <th>Tanggal Buat</th>
                        <th>Usaha/Lokasi/NOP</th>
                        <th>Masa Pajak</th>
                        <th>Jenis Pajak</th>
                        <th>Total Pajak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($main as $row){
                            if($row['sppt_status'] == '0'){
                                $tax = $row['tax_value'];
                                $fine = $row['fine'];
                                $total = $tax + $fine;
                                switch ($row['code']){
                                    case 'pbb':
                                        $btn = '
                                                <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                    break;
                                    case 'bphtb':
                                        $btn = '
                                        <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                    break;
                                    default:
                                        $btn = '
                                                <button class="btn btn-icon btn-del" value="'.$row['id_sppt'].'" data-name="'.$row['pajak_type_name'].'" data-date="'.$row['created_date'].'"><i class="icon-cancel-circle"></i></button>';
                                }
                                if($row['pajak_type_code'] == 'pbb'){
                                    $usaha = $row['loc_street'];
                                }else{
                                    $usaha = $row['usaha_name'];
                                }
                                echo '
                                        <tr>
                                             <td>'.$row['created_date'].'</td>
                                             <td>'.$usaha.'<br /><strong>NOP: '.$row['nop'].'</strong></td>
                                            <td>'.$row['masa_date'].'</td>
                                            <td>'.$row['pajak_type_name'].'</td>

                                            <td>'.$this->format->currency($total).'</td>
                                            <td>
                                                '.$btn.'
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
            <table class="table table-bordered" id="myTable1">
                <thead>
                    <tr>
                        <th>Tanggal Buat</th>
                        <th>Masa Pajak</th>
                        <th>Jenis Pajak</th>
                        <th>Total Pajak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($main as $row){
                            if($row['sppt_status'] == '3'){
                                $tax = $row['tax_value'];
                                $fine = $row['fine'];
                                $total = $tax + $fine;
                                echo '
                                        <tr>
                                            <td>'.$row['created_date'].'</td>
                                            <td>'.$row['masa_date'].'</td>
                                            <td>'.$row['pajak_type_name'].'</td>
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
<div class="modal fade" id="modDelTrans" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<form action="<?php echo base_url();?>users/transaksi_del" method="post" accept-charset="utf-8">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Batalkan Transaksi</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <input name="txDID" id="dID" hidden/>
          Yakin ingin membatalkan transaksi <strong id="dType"></strong> tanggal <strong id="dDate"></strong> ?
      </div>
      <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button type="submit" class="btn btn-primary">Ya</button>
      </div>
    </div>
  </div>
  </form>
</div>

<script>
$(document).ready(function(){
    $('#myTable').DataTable({
        "order": [[ 0, "desc" ]]
    });
    $('#myTable1').DataTable({
        "order": [[ 0, "desc" ]]
    });
    $('#myTable2').DataTable({
        "order": [[ 0, "desc" ]]
    });
});
var modDel = $('#modDelTrans');
$('.btn-del').on('click', function(){
    var id = $(this).attr('value');
	var name = $(this).attr('data-name');
	var date = $(this).attr('data-date');
	modDel.find("#dID").val(id);
	modDel.find("#dType").html(name);
	modDel.find('#dDate').html(date);
    modDel.modal('show');
    return false;
});
function cetak_pbb(id,id_pbb){
    window.open('<?php echo base_url();?>/users/print_sppt_pbb/'+id+'/'+id_pbb,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
}
function cetak_bphtb(id,id_pbb){
    window.open('<?php echo base_url();?>/users/print_sppt_bphtb/'+id+'/'+id_pbb,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
}
function cetak(id){
    window.open('<?php echo base_url();?>/users/print_sppt/'+id,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
}
</script>