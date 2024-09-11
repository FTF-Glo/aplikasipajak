<?php
$tax = $main['tax_value'];
$fine = $main['fine_value'];
$total = $tax+$fine;

?>
<html>
<head>
<style>
h1{
    font-size:1rem;
}
h2{
    font-size:0.9rem;
}
h3{
    font-size:1.5rem;
    margin-top:-10px;
}
h4{
    font-size:0.9rem;
}
table, th, td {
  border: 1px solid black;
  padding:10px;
}
table{
    border-collapse:collapse;
    width:100%;
    padding:20px;
}
table td{
    padding: 10px 50px;
}
.text-center{
    text-align:center;
}

.col{
    display:inline-block;
    padding:5px 0px 5px 0px;
}
div{font-size:0.8rem}
ul{ list-style-type: none; }
.qrcode{padding:10px;}
.tax{margin-top:20px;}
.text{margin-top:20px;}
</style>
</head>
<!--<body style="font-family:arial; font-size:10pt; padding:20px">-->
<body onload="window.print();" style="font-family:arial; font-size:10pt; padding:20px">
<h2>SURAT BUKTI PENGANTAR PEMBAYARAN SPPT</h2>
	<table>
        <tr >
	        <td>
                <div>   
                    <div class="col" style=" width:100px;">Jenis Pajak</div>
                    <div class="col" >: <?php echo $main['name_pajak_type'];?></div>
                </div>
                <div>   
                    <div class="col" style=" width:100px;">Nama</div>
                    <div class="col" >: <?php echo $main['fullname'];?></div>
                </div>
                <div>   
                    <div class="col" style=" width:100px;">Nomor Objek Pajak (NOP)</div>
                    <div class="col" >: <?php echo $main['nop'];?></div>
                </div>

                <div>   
                    <div class="col" style=" width:100px;">Tanggal Terbit</div>
                    <div class="col" >: <?php echo $this->format->fulldate($main['created_date']);?></div>
                </div>

                <div class="tax">
                     <table class="table">
                        <tbody>
                        <tr>
                            <td>Pajak</td>
                            <td class="text-right">Rp. <?php echo $this->format->currency($tax);?></td>
                        </tr>
                        <tr>
                            <td>Sangsi/Administrasi</td>
                            <td class="text-right">Rp. <?php echo $this->format->currency($fine);?></td>
                        </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Jumlah</td>
                                <td class="text-right">Rp. <?php echo $this->format->currency($total);?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
	        </td>
	        <td class="text-center">
            
                <div class="img-qrcode px-3 py-1" >   
                    <div class="qrcode" id="qrcode"></div>
                    <div class="text-center"><h3><strong><?php echo $main['token'];?></strong></h3></div>
                </div>
                    
	        </td>
	    </tr>
	
	
	</table>
	<div class="text"><i>Mohon segera melakukan pembayaran sebelum tanggal jatuh tempo. Proses pembayaran dapat dilakukan di <strong><?php echo $this->config->item('bank_name');?></strong>, dengan mencetak dan membawa <strong>BUKTI/PENGANTAR PEMBAYARAN</strong> ini</i></div>
	<script src="<?php echo base_url();?>ext/qrcode/qrcode.js"></script>
    <script>

        var data = '<?php echo $main['token'];?>';
        new QRCode(document.getElementById("qrcode"), data);

    </script>
</body>
</html>
