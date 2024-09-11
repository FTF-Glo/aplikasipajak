<?php
defined('BASEPATH') OR exit('No direct script access allowed');


?>

<div class="container">
    <div class="content bg-light p-4">
        <div class="row">
            <div class="col-md-6">
                <h3>SURAT BUKTI PENGANTAR PEMBAYARAN SPPT PBB</h3>  
            </div>
            <div class="col-md-6 title-right pb-2">
                <a href="<?php echo base_url('users/transaksi');?>" class="btn btn-secondary "><i class="icon-arrow-left mr-2"></i>Kembali</a>
                <button class="btn btn-primary" id="<?php echo $id;?>" onClick="cetak(this.id,<?php echo $main['id'];?>)"><i class="icon-printer mr-2"></i>Cetak</button>
            </div>
           
        </div>
        
        <div>
            <table width="100%">
                <tr>
                    <td width="50%">NOP : <strong><?php echo $main['nop'];?></strong></td>
                    <td class="text-right">Tahun : <strong><?php echo $main['masa'];?></strong></td>
                </tr>
            </table>
            <table class="table table-bordered">
                <tr>
                    <td width="50%">
                        <div class="text-center"><strong>LETAK OBJEK PAJAK</strong></div>
                        <div class="form-group p-3">
                            
                            <div>JL. <span id="iAddr"><?php echo $main['loc_street'];?></span></div>
                            <div id="iKel"><?php echo $main['loc_kel'];?></div>
                            <div>RT: <span id="iRT"><?php echo $main['loc_rt'];?></span>  RW: <span id="iRW"><?php echo $main['loc_rw'];?></span></div>
                            <div id="iKec"><?php echo $main['loc_kec'];?></div>
                            <div id="iKab"><?php echo $main['loc_kab'];?></div>
                            <div id="iProv"><?php echo $main['loc_prov'];?></div>
                        </div>
                    </td>
                    <td width="50%">
                        <div class="text-center"><strong>NAMA DAN ALAMAT WAJIB PAJAK</strong></div>
                        <div class="form-group p-3">
                            <label ><strong><?php echo $main['wp_name'];?></strong></label>
                            <div>JL. <span id="iAddr"><?php echo $main['wp_street'];?></span></div>
                            <div id="iKel"><?php echo $main['wp_kel'];?></div>
                            <div>RT: <span id="iRT"><?php echo $main['wp_rt'];?></span>  RW: <span id="iRW"><?php echo $main['wp_rw'];?></span></div>
                            <div id="iKec"><?php echo $main['wp_kec'];?></div>
                            <div id="iKab"><?php echo $main['wp_kab'];?></div>
                            <div id="iProv"><?php echo $main['wp_prov'];?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                     <td colspan="2" style="padding:0px;">
                        <table class="table table-bordered">
                            <tr>
                                <td>OBJEK PAJAK</td>
                                <td class="text-center">LUAS (M<sup>2</sup>)</td>
                                <td class="text-center">KELAS</td>
                                <td class="text-center">NJOP PER M<sup>2</sup> (Rp.)</td>
                                <td class="text-center">TOTAL NJOP (Rp.)</td>
                            </tr>
                            <tr>
                                <?php
                                    $total_njop_bumi = $main['op_price_bumi'] * $main['op_luas_bumi'];
                                    $total_njop_bangunan = $main['op_price_bangunan'] * $main['op_luas_bangunan'];
                                    $njop_dasar = $total_njop_bumi + $total_njop_bangunan;
                                    $njop_pbb = $njop_dasar - $main['njoptkp'];
                                    $njkp = $njop_pbb * ($main['njkp_percent']/100);
                                    $pbb = $njkp * ($main['pbb_percent']/100);
                                ?>
                                <td>
                                    <div>BUMI</div>
                                    <div>BANGUNAN</div>
                                </td>
                                <td class="text-center">
                                    <div><?php echo $this->format->curr($main['op_luas_bumi']);?></div>
                                    <div><?php echo $this->format->curr($main['op_luas_bangunan']);?></div>
                                </td>
                                <td class="text-center">
                                    <div><?php echo $main['op_kelas_bumi'];?></div>
                                    <div><?php echo $main['op_kelas_bangunan'];?></div>
                                </td>
                                <td class="text-right">
                                    <div><?php echo $this->format->curr($main['op_price_bumi']);?></div>
                                    <div><?php echo $this->format->curr($main['op_price_bangunan']);?></div>
                                </td>
                                <td class="text-right">
                                    <div><?php echo $this->format->curr($total_njop_bumi);?></div>
                                    <div><?php echo $this->format->curr($total_njop_bangunan);?></div>
                                </td>
                            </tr>
                        </table>
                     </td>
                </tr>
                <tr>
                    <td style="padding:0;" colspan="2">
                        <table class="table table-borderless" width="100%">
                            <tr>
                                <td>NJOP Sebagai dasar pengenaan PBB-P2</td>
                                <td class="text-left">= </td>
                                <td class="text-right"><?php echo $this->format->curr($njop_dasar);?></td>
                            </tr>
                            <tr>
                                <td>NJOPTKP (NJOP Tidak Kena Pajak)</td>
                                <td class="text-left">= </td>
                                <td class="text-right"><?php echo $this->format->curr($main['njoptkp']);?></td>
                            </tr>
                            <tr>
                                <td>NJKP (Nilai Jual Kena Pajak)</td>
                                <td class="text-left">= <?php echo $main['njkp_percent'];?> x <?php echo $this->format->curr($njop_pbb);?></td>
                                <td  class="text-right"><?php echo $this->format->curr($njkp);?></td>
                            </tr>
                            <tr class="border-top bg-secondary">
                                <td><strong>Pajak Bumi dan Bangunan yang terutang</strong></td>
                                <td class="text-left">= <?php echo $main['pbb_percent'];?> x <?php echo $this->format->curr($njkp);?></td>
                                <td class="text-right"><h4><strong>Rp. <?php echo $this->format->currency($pbb);?></strong></h4></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0px;" colspan="2">
                        <table class="table">
                            <tr>
                                <td>
                                    TGL.JATUH TEMPO : <?php echo $main['jatuh_tempo'];?>
                                    <p><i>Mohon segera melakukan pembayaran sebelum tanggal jatuh tempo. Proses pembayaran dapat dilakukan di <strong><?php $this->config->item('bank_name');?></strong>, dengan mencetak dan membawa <strong>BUKTI/PENGANTAR PEMBAYARAN</strong> ini</i></p>
                                </td>
                                <td>
                                    <div class="img-qrcode px-3 py-1" >   
                                        <div class="border p-3" id="qrcode">Kode Bayar:</div>
                                        <div class="text-center"><h3><strong><?php echo $sppt['token'];?></strong></h3></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>
<script src="<?php echo base_url();?>ext/qrcode/qrcode.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    var data = '<?php echo $sppt['token'];?>';
    new QRCode(document.getElementById("qrcode"), data);
});
function cetak(id,id_pbb){
        window.open('<?php echo base_url();?>/users/print_sppt_pbb/'+id+'/'+id_pbb,'popup','width=900px,height=500px,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0');
    }
</script>