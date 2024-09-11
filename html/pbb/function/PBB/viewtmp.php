<?php echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">"; ?>
<?php
$OP_LUAS_TANAH_VIEW = '0';
if(isset($OP_LUAS_TANAH)){
    if(strrchr($OP_LUAS_TANAH,'.') != '') {
        $OP_LUAS_TANAH_VIEW = number_format($OP_LUAS_TANAH,2,',','.');
    }else {$OP_LUAS_TANAH_VIEW = number_format($OP_LUAS_TANAH,0,',','.');}
} 

$OP_LUAS_BANGUNAN_VIEW = '0';
if(isset($OP_LUAS_BANGUNAN)){
    if(strrchr($OP_LUAS_BANGUNAN,'.') != '')  {
        $OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN,2,',','.');
    }else $OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN,0,',','.');
} 
?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-10" style="max-width:690px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f">
		<table border=0 width=60% cellspacing=0 cellpadding=2>
			<tr><td colspan=3 align='center'><h2>Surat Pemberitahuan Objek Pajak</h2></td></tr>
			<tr><td colspan=3 align='center'>&nbsp;</td></tr>
			<tr><td colspan=3>Denah : <?php echo isset($OP_SKET)?"<a href='".$OP_SKET."'>".substr($OP_SKET, strrpos($OP_SKET, '/')+1)."</a>":"-"?></td></tr>
			<tr><td colspan=3>Foto : <?php echo isset($OP_FOTO)?"<a href='".$OP_FOTO."'>".substr($OP_FOTO, strrpos($OP_FOTO, '/')+1)."</a>":"-"?></td></tr>
			<tr><td colspan=3>
				Daftar Lampiran
				<ul>
					<?php echo isset($HtmlExt) ? $HtmlExt : "<li>-</li>"?>
				</ul><br></td></tr>
			
			<tr><td>1.</td><td>NOP</td>
				<td><?php echo isset($NOP) ? $NOP : ""?></td></tr>
			<tr><td>2.</td><td>NOP Bersama</td>
				<td><?php echo isset($NOP_BERSAMA)?$NOP_BERSAMA:""?></td></tr>
			
			<tr><td colspan=3 class="title-header"><br>B. Data Letak Objek Pajak</td></tr>
			<tr><td>3.</td><td>Nama Jalan</td>
				<td><?php echo isset($OP_ALAMAT)?$OP_ALAMAT:""?></td></tr>
			<!-- <tr><td>4.</td><td>Blok/Kav/Nomor</td>
				<td><?php#=isset($OP_NOMOR)?$OP_NOMOR:""?></td></tr> -->
			<tr><td>4.</td><td>RT</td>
				<td><?php echo isset($OP_RT)?$OP_RT:""?></td></tr>
			<tr><td>5.</td><td>RW</td>
				<td><?php echo isset($OP_RW)?$OP_RW:""?></td></tr>
			<tr><td>6.</td><td><?php echo $appConfig['LABEL_KELURAHAN'];?></td>
				<td><?php echo isset($OP_KELURAHAN)?$aOPKelurahan[0]['CPC_TKL_KELURAHAN']:""?></td></tr>
				<tr><td></td><td>Kecamatan</td>
				<td><?php echo isset($OP_KECAMATAN)?$aOPKecamatan[0]['CPC_TKC_KECAMATAN']:""?></td></tr>
				<tr><td></td><td>Kab/kodya</td>
				<td><?php echo isset($OP_KOTAKAB)?$aOPKabKota[0]['CPC_TK_KABKOTA']:""?></td></tr>
				
			<tr><td colspan=3 class="title-header"><br>C. Data Subjek Pajak</td></tr>
			<tr><td>7.</td><td>Status</td>
				<td><?php echo isset($WP_STATUS)?$WP_STATUS:""?></td></tr>
			<tr><td>8.</td><td>Pekerjaan</td>
				<td><?php echo isset($WP_PEKERJAAN)?$WP_PEKERJAAN:""?></td></tr>
			<tr><td>9.</td><td>Nama Subjek Pajak</td>
				<td><?php echo isset($WP_NAMA)?$WP_NAMA:""?></td></tr>
			<tr><td>10.</td><td>Nama Jalan</td>
				<td><?php echo isset($WP_ALAMAT)?$WP_ALAMAT:""?></td></tr>
			<tr><td>11.</td><td>RT</td>
				<td><?php echo isset($WP_RT)?$WP_RT:""?></td></tr>
			<tr><td>12.</td><td>RW</td>
				<td><?php echo isset($WP_RW)?$WP_RW:""?></td></tr>
			<tr><td>13.</td><td><?php echo $appConfig['LABEL_KELURAHAN'];?></td>
				<td><?php echo isset($WP_KELURAHAN)?$WP_KELURAHAN:""?></td></tr>
			<tr><td>14.</td><td>Kecamatan</td>
				<td><?php echo isset($WP_KECAMATAN)?$WP_KECAMATAN:""?></td></tr>
			<tr><td>15.</td><td>Kab/kodya</td>
				<td><?php echo isset($WP_KOTAKAB)?$WP_KOTAKAB:""?></td></tr>
			<tr><td>16.</td><td>Kode Pos</td>
				<td><?php echo isset($WP_KODEPOS)?$WP_KODEPOS:""?></td></tr>
			<tr><td>17.</td><td>Nomor KTP</td>
				<td><?php echo isset($WP_NO_KTP)?$WP_NO_KTP:""?></td></tr>
			
			<tr><td colspan=3 class="title-header"><br>D. Data Tanah</td></tr>
			<tr><td>18.</td><td>Luas Tanah</td>
				<td><?php echo $OP_LUAS_TANAH_VIEW?></td></tr>
			<tr><td>19.</td><td>Zona Nilai Tanah</td>
				<td><?php echo isset($OT_ZONA_NILAI)?$OT_ZONA_NILAI:""?></td></tr>
			<tr><td>20.</td><td>Jenis Tanah</td>
				<td><?php if(isset($OT_JENIS)){
						if($OT_JENIS==1) echo "Tanah + Bangunan"; else
						if($OT_JENIS==2) echo "Kavling siap bangun"; else
						if($OT_JENIS==3) echo "Tanah kosong"; else
						if($OT_JENIS==4) echo "Fasilitas umum"; 
					}?></td></tr>
			<tr><td>21.</td><td>Nomor Sertifikat</td>
				<td><?php echo isset($NOMOR_SERTIFIKAT)?$NOMOR_SERTIFIKAT:""?></td></tr>
			<tr><td>22.</td><td>Tanggal Sertifikat</td>
				<td><?php echo isset($TANGGAL)?$TANGGAL:""?></td></tr>
			<tr><td>23.</td><td>Sertifikat Atas Nama</td>
				<td><?php echo isset($NAMA_SERTIFIKAT)?$NAMA_SERTIFIKAT:""?></td></tr>
			
			<tr><td colspan=3 class="title-header"><br>E. Data Bangunan</tr>
			<tr><td>24.</td><td>Jumlah Bangunan</td>
				<td><?php echo isset($OP_JML_BANGUNAN)?$OP_JML_BANGUNAN:""?></td></tr>
				
			<tr><td colspan=3 class="title-header"><br>F. Pernyataan Subjek Pajak</td></tr>
			<tr><td colspan=3>Saya menyatakan bahwa informasi yang telah saya berikan dalam formulir ini termasuk lampirannya adalah benar, jelas dan lengkap menurut keadaan yang sebenarnya, sesuai dengan sesuai dengan pasal 83 ayat(2) Undang-Undang Nomor 28 Tahun 2009</td></tr>
			<tr><td>25.</td><td>Nama <?php echo isset($PP_TIPE)?$PP_TIPE:""?></td>
				<td><?php echo isset($PP_NAMA)?$PP_NAMA:""?></td></tr>
			<tr><td>26.</td><td>Tanggal</td>
				<td><?php echo isset($PP_DATE)?$PP_DATE:""?></td>
			</tr>
			<tr><td colspan=3 class="title-header"><br>G. Data Penilaian</td></tr>
			<tr>
				<td colspan=3>
					<table class="table-penilaian">
					<tr>
						<th> </th>
						<th>Luas</th>
						<th>Kelas</th>
						<th>NJOP / M2</th>
						<th>NJOP</th>
					</tr>
					<tr>
						<td>Bumi</td>
						<td align="right"><?php echo $OP_LUAS_TANAH_VIEW?></td>
						<td align="center"><?php echo isset($OP_KELAS_TANAH)?$OP_KELAS_TANAH:"0"?></td>
						<td align="right"><?php echo isset($NJOP_TANAH)?number_format($NJOP_TANAH/$OP_LUAS_TANAH,0,',','.'):"0"?></td>
						<td align="right"><?php echo isset($NJOP_TANAH)?number_format($NJOP_TANAH,0,',','.'):"0"?></td>
					</tr>
					<tr>
						<td>Bangunan</td>
						<td align="right"><?php echo $OP_LUAS_BANGUNAN_VIEW?></td>
						<td align="center"><?php echo isset($OP_KELAS_BANGUNAN)?$OP_KELAS_BANGUNAN:"0"?></td>
						<td align="right"><?php echo isset($NJOP_BANGUNAN) && isset($OP_LUAS_BANGUNAN) && $OP_LUAS_BANGUNAN > 0?number_format($NJOP_BANGUNAN/$OP_LUAS_BANGUNAN,0,',','.'):"0"?></td>
						<td align="right"><?php echo isset($NJOP_BANGUNAN)?number_format($NJOP_BANGUNAN,0,',','.'):"0"?></td>
					</tr>
					<?php if($NOP_BERSAMA != ''){ 
						$LUAS_BUMI_BEBAN_VIEW = '0';
										if(isset($LUAS_BUMI_BEBAN)){
											if(strrchr($LUAS_BUMI_BEBAN,'.') != '') {
												$LUAS_BUMI_BEBAN_VIEW = number_format($LUAS_BUMI_BEBAN,2,',','.');
											}else {$LUAS_BUMI_BEBAN_VIEW = number_format($LUAS_BUMI_BEBAN,0,',','.');}
										} 

										$LUAS_BNG_BEBAN_VIEW = '0';
										if(isset($LUAS_BNG_BEBAN)){
											if(strrchr($LUAS_BNG_BEBAN,'.') != '')  {
												$LUAS_BNG_BEBAN_VIEW = number_format($LUAS_BNG_BEBAN,2,',','.');
											}else $LUAS_BNG_BEBAN_VIEW = number_format($LUAS_BNG_BEBAN,0,',','.');
										} 
										
										echo "<tr>
								<td>Bumi Bersama</td>
								<td align=\"right\">".$LUAS_BUMI_BEBAN_VIEW."</td>
								<td align=\"center\">".(isset($KELAS_BUMI_BEBAN)?$KELAS_BUMI_BEBAN:"0")."</td>
								<td align=\"right\">".(isset($NJOP_BUMI_BEBAN)?number_format($NJOP_BUMI_BEBAN/$LUAS_BUMI_BEBAN,0,',','.'):"0")."</td>
								<td align=\"right\">".(isset($NJOP_BUMI_BEBAN)?number_format($NJOP_BUMI_BEBAN,0,',','.'):"0")."</td>
							</tr>";
						echo "<tr>
								<td>Bangunan Bersama</td>
								<td align=\"right\">".$LUAS_BNG_BEBAN_VIEW."</td>
								<td align=\"center\">".(isset($KELAS_BNG_BEBAN)?$KELAS_BNG_BEBAN:"0")."</td>
								<td align=\"right\">".(isset($NJOP_BNG_BEBAN)?number_format($NJOP_BNG_BEBAN/$LUAS_BNG_BEBAN,0,',','.'):"0")."</td>
								<td align=\"right\">".(isset($NJOP_BNG_BEBAN)?number_format($NJOP_BNG_BEBAN,0,',','.'):"0")."</td>
							</tr>";
					} ?>
					</table>
				</td>
			</tr>
		</table>
		<?php
			if($arConfig['usertype'] == 'dispenda' || $arConfig['usertype'] == 'dispenda2')
				echo '<input type="button" name="hitung" value="Nilai Ulang" id="hitung-njop"/>';
		?>
	</div>
</div>


<script type="text/javascript">

$("#hitung-njop").click(function(){
    $("#load-mask").css("display","block");
    $("#load-content").fadeIn();
        
    loadNB('<?php echo $NBParam?>');
});


function loadNBSuccess(params){
        $("#load-content").css("display","none");
        $("#load-mask").css("display","none");
	
	if(params.responseText){
		var objResult=Ext.decode(params.responseText);

		if (objResult.RC == "0000") {
			alert('Penilaian sukses.');
                        document.location.reload(true);
		} else {
			alert('Gagal melakukan penilaian. Terjadi kesalahan server');
		}
	} else {
		alert('Gagal melakukan penilaian. Terjadi kesalahan server');
	}
}

function loadNBFailure(params){
	$("#load-content").css("display","none");
        $("#load-mask").css("display","none");
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
}

function loadNB(svr_param) {

        var params = "{\"SVR_PRM\":\""+svr_param+"\",\"NOP\":\"<?php echo $NOP;?>\", \"TAHUN\":\"<?php echo $tahun_tagihan;?>\", \"TIPE\":\"2\", \"SUSULAN\":\"0\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
                url : 'inc/PBB/svc-penilaian.php',
                success: loadNBSuccess,
                failure: loadNBFailure,			
                params :{req:params}
        });   

}
</script>
<style type="text/css">
    #btnClose{cursor: pointer;}
    .linkto:hover, .linkstpd:hover, .linkdate:hover{color: #ce7b00;}
    .linkto, .linkstpd, .linkdate{text-decoration: underline; cursor: pointer;}
    #load-mask, #load-content{
        display:none;
        position:fixed;
        height:100%;
        width:100%;
        top:0;
        left:0;
    }
    #load-mask{
        background-color:#000000;
        filter:alpha(opacity=70);
        opacity:0.7;
        z-index:1;
    }
    #load-content{
        z-index: 2;
    }

    #closeddate{cursor: pointer;}
    #loader {
        margin-right: auto;
        margin-left: auto; 
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }
	
	.table-penilaian th {
	    background-color: #ffffff;
	    color: #000000;
	    padding-bottom: 4px;
	    padding-top: 5px;
	    text-align: center;
	}
	.table-penilaian td, .table-penilaian th {
	    border: 1px solid #000000;
	    padding: 3px 7px 2px;
		cellspacing:0px;
	}
	.table-penilaian
	{
		border-collapse: collapse;
		width: 100%;
	}
</style>

<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif"  style="margin-right: auto;margin-left: auto;"/>
    </div>
</div>
<div id="load-mask"></div>