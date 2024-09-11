
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
</style>
</head>
<body onload="window.print();" style="font-family:arial; font-size:10pt; padding:20px">

	<table>
	    <tr >
	        <td class="text-center"><h1><?php echo $this->fun->getConfig('pemerintah');?></h1></td>
	    </tr>
        <tr >
	        <td class="text-center">
	            <h2>SURAT KETETAPAN PAJAK DAERAH</h2>
	            <h3>(SKPD)</h3>
	            <div>Nomor: </div>
	        </td>
	    </tr>
        <tr >
	        <td>
                <h4>PENGUSAHA KENA PAJAK</h4>
                <div>   
                    <div class="col" style=" width:100px;">NPWPD</div>
                    <div class="col" >: <?php echo $main[0]['npwpd'];?></div>
                </div>
                <div>   
                    <div class="col" style=" width:100px;">Nama PKP</div>
                    <div class="col" >: <?php echo $main[0]['nama'];?></div>
                </div>
                <div>   
                    <div class="col" style=" width:100px;">Jenis Usaha</div>
                    <div class="col" >: <?php echo $main[0]['nama_pengelolaan'];?></div>
                </div>
                <div>   
                    <div class="col" style=" width:100px;">Alamat</div>
                    <div class="col" >: <?php echo $main[0]['alamat'].','.$main[0]['nama_kec'].', '. $main[0]['kelurahan'].', '. $main[0]['kabupaten'];?></div>
                </div>
	        </td>
	    </tr>
	    <tr>
	        <td>
	            <div>Jumlah yang harus dibayar :</div>
	            <h4>PAJAK AIR TANAH</h4>
	            <div>
    	            <ol type="I">
    	                <?php
    	                    $total_pajak = 0;
    	                    foreach($main['periode'] as $row){
    	                       $total_pajak += $row['nilai_pajak'];
    	                ?>
                                <li style="font-size:0.8rem; margin-bottom:20px;">Periode : <span style="font-size:1rem; font-weight:bold"><?php echo $this->format->date_my($row['yearmonth']);?></span>
                                    <div style="margin-top:15px;">
                                        <div class="col" style="width:30%;">Volume Air Tanah</div>
                                        <div class="col">: <?php echo $row['pemakaian'];?> m<sup>3</sup></div>
                                    </div>
                                    <div>
                                        <div class="col" style="width:50%;">Jumlah Pokok Wajib Pajak </div>
                                        <div class="col">: <?php echo $this->format->currency($row['nilai_pajak']);?></div>
                                    </div>
                                </li>	                        
    	                <?php
    	                    }
    	                ?>
                    </ol>
	            </div>
	            <div>
                    <ul>
                        <li>
	                        <div class="col" style="width:50%"><strong>Jumlah Pokok yang harus dibayar</strong></div>
	                        <div class="col" style="border-top:2px solid #000">: <strong><?php echo $this->format->currency($total_pajak);?></strong></div>
	                    </li>
	                    <li style="font-size:15px; margin-top:20px;">   
	                        Terbilang : ( <?php echo $this->format->terbilang($total_pajak);?> )
	                    </li>
	                <ol>
	            </div>
	            
	        </td>
	    </tr>
	    <tr>
	        <td>
	            <div>
	                <div class="col" style="width:64%"></div>
	                <div class="col" style="width:35%">
	                    <div class="text-center"><?php echo $this->fun->getConfig('lokasi_skpd');?>, <?php echo $main['date_now'];?></div>
	                    <div class="text-center"><strong>An. <?php echo $this->fun->getConfig('sign');?></strong></div>
	                    <div class="text-center" style="margin-top:100px;">_____________________________</div>
                        <div class="text-center" style="margin-top:20px"></div>
                        <div class="text-center" style="margin-top:10px;"><?php echo $this->fun->getConfig("nama_skpd");?></div>
	                </div>
	                
	            </div>
	        </td>
	    </tr>
	</table>
</body>
</html>