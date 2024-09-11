<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tax = $main['tax_value'];
$fine = $main['fine_value'];
$total = $tax+$fine;

?>

<div class="container">
    <div class="content bg-light p-4">
        <div class="row">
            <div class="col-md-6">
                <h3>SURAT BUKTI PENGANTAR PEMBAYARAN SPPT</h3>  
            </div>
            <div class="col-md-6 title-right pb-2">
                <a href="<?php echo base_url('users/transaksi');?>" class="btn btn-secondary "><i class="icon-arrow-left mr-2"></i>Kembali</a>
                <button class="btn btn-primary" id="<?php echo $main['id_sppt'];?>" onClick="cetak(this.id)"><i class="icon-printer mr-2"></i>Cetak</button>
            </div>
           
        </div>
        
        <div class="border">
            <div class="row">
                <div class="col-7 p-5">
                    
                    <table class="table">
                        <tr>
                            <td colspan="2" width="40%">Jenis Pajak</td>
                            <td><h4><?php echo $main['name_pajak_type'];?></h4></td>
                        </tr>
                        <tr>
                            <td colspan="2">Nama</td>
                            <td>: <?php echo $main['fullname'];?></td>
                        </tr>
                        <tr>
                            <td colspan="2">Nomor Objek Pajak (NOP)</td>
                            <td>: <?php echo $main['nop'];?></td>
                        </tr>

                    </table>
                    <table class="table">
                        <tbody>
                        <tr>
                            <td>Pajak</td>
                            <td>Rp. </td>
                            <td class="text-right"><?php echo $this->format->currency($tax);?></td>
                        </tr>
                        <tr>
                            <td>Sangsi/Administrasi</td>
                            <td>Rp. </td>
                            <td class="text-right"><?php echo $this->format->currency($fine);?></td>
                        </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Jumlah</td>
                                <td>Rp. </td>
                                <td class="text-right"><?php echo $this->format->currency($total);?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-5 p-5">
                    <div class="text-right px-3">Terbit : <?php echo $this->format->fulldate($main['created_date']);?></div>
                    <div class="text-right px-3">Jatuh Tempo : <?php echo $this->format->fulldate($main['created_date']);?></div>
                    <div class="img-qrcode px-3 py-1" >   
                        <div class="border p-3" id="qrcode">Kode Bayar:</div>
                        <div class="text-center"><h3><strong><?php echo $main['token'];?></strong></h3></div>
                    </div>
                    
                </div>
                <div class="col-12 p-5">
                    <i>Mohon segera melakukan pembayaran sebelum tanggal jatuh tempo. Proses pembayaran dapat dilakukan di <strong><?php $this->config->item('bank_name');?></strong>, dengan mencetak dan membawa <strong>BUKTI/PENGANTAR PEMBAYARAN</strong> ini</i>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="<?php echo base_url();?>ext/qrcode/qrcode.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    var data = '<?php echo $main['token'];?>';
    new QRCode(document.getElementById("qrcode"), data);
});
function cetak(id){
        window.open('<?php echo base_url();?>/users/print_sppt/'+id,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
    }
</script>