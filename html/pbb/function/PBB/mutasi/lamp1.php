<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script src="function/PBB/consol/jquery.validate.min.js"></script>
<script language="JavaScript">
	var ids={
		'3':'a3', 
		'8':'a8', 
		'2':'a2',
		'9':'a9',
		'4':'a4',
		'5':'a5',
		'6':'a6',
		'7':'a7',
		'12':'a12',
		'13':'a13',
		'15':'a15',
		'16':'a16'
		};
	
	function switchTo(id){	
		hideall();
		show(ids[id]);
	}

	function hideall() {
		//loop through the array and hide each element by id
		for (key in ids){
			hide(ids[key]);
		}		  
	}

	function hide(id) {
		document.getElementById(id).style.display = 'none';
	}

	function show(id) {
		//function to show an element with a specified id
		document.getElementById(id).style.display = '';	  
	}
        
        function iniAngka(evt,x){
			 var charCode = (evt.which) ? evt.which : event.keyCode;
			 //alert(charCode);
			 if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13){
				return true;
			 }else{
				alert("Input hanya boleh angka!");
				return false;
			 }
	}
        function iniAngkaDenganKoma(evt, x) {
            var charCode = (evt.which) ? evt.which : event.keyCode;

            if (charCode >= 46 || (charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
                return true;
            } else {
                alert("Input hanya boleh angka dan titik!");
                return false;
            }
        }
        
        $(document).ready(function() {
            <?php
                if(isset($OP_PENGGUNAAN)) 
                    echo 'switchTo('.$OP_PENGGUNAAN.');';
            ?>
            
            
			
			$("#form3").validate({
				rules : {
					OP_LUAS_BANGUNAN  : "required",
					OP_JML_LANTAI     : "required",
					OP_THN_DIBANGUN   : "required",
					OP_DAYA			  : "required"				
					},
				messages : {
					OP_LUAS_BANGUNAN  : "Wajib diisi",
					OP_JML_LANTAI     : "Wajib diisi",
					OP_THN_DIBANGUN   : "Wajib diisi",
					OP_DAYA			  : "Wajib diisi"	
				}
			});
			
        });
        
</script>
<style>
    input {
        border:1px solid #dadada;
        border-radius:2px;
        font-size:12px;
        padding:4px; 
    }
    input:focus { 
        outline:none;
        border-color:#9ecaed;
        box-shadow:0 0 10px #9ecaed;
    }
    #form3 input.error {
        border-color: #ff0000;
    }
    #form3 textarea.error {
        border-color: #ff0000;
    }
</style>
<div class="row">
	<div class="col-md-1"></div>
	<div class="col-md-11" style="max-width:840px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f">
		<form method="post" name="form3" id="form3">
			<h2>Lampiran Surat Pemberitahuan Objek Pajak (SPOP) (2/2)</h2><br>

			<table border=0 cellspacing=0px cellpadding=2px class="transparent">
				<tr><td width="20%"></td><td width="20%"></td><td width="20%"></td><td width="20%"></td><td width="20%"></td></tr>
				<tr><td colspan=5><h4>A. Rincian Data Bangunan</h4></td></tr>
				<tr><td>1. NOP</td>
					<td colspan=4><span id="NOP"><?php echo $NOP?></span><input type="hidden" name="NOP" value="<?php echo $NOP?>"/></td></tr>
				<tr><td>2. Jumlah bangunan</td>	
					<td colspan=4><?php echo $OP_JML_BANGUNAN?></td></tr>
				<tr><td>3. Bangunan ke</td>
					<td colspan=4><select name="OP_NUM"><?php for($i=1; $i<=$OP_JML_BANGUNAN; $i++) echo "<option ".($num==$i ? "selected":"").">$i</option>"?></select></td></tr>
				<tr><td>4. Jenis penggunaan bangunan</td>	
					<td colspan=4><select name="OP_PENGGUNAAN" id="OP_PENGGUNAAN" onChange="switchTo(this.value)">
						<option value="1" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==1)?"selected":""?>>Perumahan</option>
						<option value="2" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==2)?"selected":""?>>Perkantoran Swasta</option>
						<option value="3" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==3)?"selected":""?>>Pabrik</option>
						<option value="4" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==4)?"selected":""?>>Toko/Apotik/Pasar/Ruko</option>
						<option value="5" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==5)?"selected":""?>>Rumah Sakit/Klinik</option>
						<option value="6" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==6)?"selected":""?>>Olah Raga/Rekreasi</option>
						<option value="7" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==7)?"selected":""?>>Hotel/Wisma</option>
						<option value="8" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==8)?"selected":""?>>Bengkel/Gudang/Pertanian</option>
						<option value="9" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==9)?"selected":""?>>Gedung Pemerintah</option>
						<option value="10" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==10)?"selected":""?>>Lain-lain</option>
						<option value="11" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==11)?"selected":""?>>Bng Tidak Kena Pajak</option>
						<option value="12" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==12)?"selected":""?>>Bangunan Parkir</option>
						<option value="13" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==13)?"selected":""?>>Apartemen</option>
						<option value="14" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==14)?"selected":""?>>Pompa Bensin</option>
						<option value="15" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==15)?"selected":""?>>Tangki Minyak</option>
						<option value="16" <?php echo (isset($OP_PENGGUNAAN) && $OP_PENGGUNAAN==16)?"selected":""?>>Gedung Sekolah</option>
					</select></td></tr>
				<tr><td>5. Luas bangunan</td>	
					<td colspan=4><input type="text" onkeypress="return iniAngkaDenganKoma(event,this)" name="OP_LUAS_BANGUNAN" id="OP_LUAS_BANGUNAN" value="<?php echo isset($OP_LUAS_BANGUNAN)?$OP_LUAS_BANGUNAN:""?>" size=12 maxlength="9"> m&sup2;</td></tr>
				<tr><td>6. Jumlah lantai</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="OP_JML_LANTAI" id="OP_JML_LANTAI" value="<?php echo isset($OP_JML_LANTAI)?$OP_JML_LANTAI:""?>" size=4 maxlength="2"></td></tr>
				<tr><td>7. Tahun dibangun</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="OP_THN_DIBANGUN" value="<?php echo isset($OP_THN_DIBANGUN)?$OP_THN_DIBANGUN:""?>" size=6 maxlength="4"></td></tr>
				<tr><td>8. Tahun direnovasi</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="OP_THN_RENOVASI" value="<?php echo isset($OP_THN_RENOVASI)?$OP_THN_RENOVASI:""?>" size=6 maxlength="4"></td></tr>
				<tr><td>9. Daya listrik terpasang</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="OP_DAYA" value="<?php echo isset($OP_DAYA)?$OP_DAYA:""?>" size=12 maxlength="9"> Watt</td></tr>
				<tr><td>10. Kondisi pada umumnya</td>
					<td id="colored"><label><input type="radio" name="OP_KONDISI" value="1" <?php echo (isset($OP_KONDISI) && $OP_KONDISI=="1")?"checked":""?> checked> Sangat baik</label></td>
					<td id="colored"><label><input type="radio" name="OP_KONDISI" value="2" <?php echo (isset($OP_KONDISI) && $OP_KONDISI=="2")?"checked":""?>> Baik</label></td>
					<td id="colored"><label><input type="radio" name="OP_KONDISI" value="3" <?php echo (isset($OP_KONDISI) && $OP_KONDISI=="3")?"checked":""?>> Sedang</label></td>
					<td id="colored"><label><input type="radio" name="OP_KONDISI" value="4" <?php echo (isset($OP_KONDISI) && $OP_KONDISI=="4")?"checked":""?>> Jelek</label></td></tr>
				<tr><td>11. Konstruksi</td>
					<td><label><input type="radio" name="OP_KONSTRUKSI" value="1" <?php echo (isset($OP_KONSTRUKSI) && $OP_KONSTRUKSI=="1")?"checked":""?> checked> Baja</label></td>
					<td><label><input type="radio" name="OP_KONSTRUKSI" value="2" <?php echo (isset($OP_KONSTRUKSI) && $OP_KONSTRUKSI=="2")?"checked":""?>> Beton</label></td>
					<td><label><input type="radio" name="OP_KONSTRUKSI" value="3" <?php echo (isset($OP_KONSTRUKSI) && $OP_KONSTRUKSI=="3")?"checked":""?>> Batu bata</label></td>
					<td><label><input type="radio" name="OP_KONSTRUKSI" value="4" <?php echo (isset($OP_KONSTRUKSI) && $OP_KONSTRUKSI=="4")?"checked":""?>> Kayu</label></td></tr>	
				<tr><td>12. Atap</td>
					<td id="colored"><label><input type="radio" name="OP_ATAP" value="1" <?php echo (isset($OP_ATAP) && $OP_ATAP=="1")?"checked":""?> checked> Decrabon/Beton/Gtg Glazur</label></td>
					<td id="colored"><label><input type="radio" name="OP_ATAP" value="2" <?php echo (isset($OP_ATAP) && $OP_ATAP=="2")?"checked":""?>> Gtg Beton/Aluminium</label></td>
					<td id="colored" colspan=2><label><input type="radio" name="OP_ATAP" value="3" <?php echo (isset($OP_ATAP) && $OP_ATAP=="3")?"checked":""?>> Gtg Biasa/Sirap</label></td></tr>
				<tr><td></td>
					<td id="colored"><label><input type="radio" name="OP_ATAP" value="4" <?php echo (isset($OP_ATAP) && $OP_ATAP=="4")?"checked":""?>> Asbes</label></td>	
					<td id="colored" colspan=3><label><input type="radio" name="OP_ATAP" value="5" <?php echo (isset($OP_ATAP) && $OP_ATAP=="5")?"checked":""?>> Seng</label></td></tr>
				<tr><td>13. Dinding</td>
					<td><label><input type="radio" name="OP_DINDING" value="1" <?php echo (isset($OP_DINDING) && $OP_DINDING=="1")?"checked":""; ?> checked> Kaca/Aluminium</label></td>
					<td><label><input type="radio" name="OP_DINDING" value="2" <?php echo (isset($OP_DINDING) && $OP_DINDING=="2")?"checked":""?>> Beton</label></td>
					<td colspan=2><label><input type="radio" name="OP_DINDING" value="3" <?php echo (isset($OP_DINDING) && $OP_DINDING=="3")?"checked":""?>> Batu Bata/Conblok</label></td></tr>
				<tr><td></td>
					<td><label><input type="radio" name="OP_DINDING" value="7" <?php echo (isset($OP_DINDING) && $OP_DINDING=="7")?"checked":""?>> Kayu</label></td>
					<td><label><input type="radio" name="OP_DINDING" value="8" <?php echo (isset($OP_DINDING) && $OP_DINDING=="8")?"checked":""?>> Seng</label></td>
					<td colspan=2><label><input type="radio" name="OP_DINDING" value="0" <?php echo (isset($OP_DINDING) && $OP_DINDING=="0")?"checked":""?>> Tidak ada</label></td></tr>
				<tr><td>14. Lantai</td>
					<td id="colored"><label><input type="radio" name="OP_LANTAI" value="1" <?php echo (isset($OP_LANTAI) && $OP_LANTAI=="1")?"checked":""?> checked> Marmer</label></td>
					<td id="colored"><label><input type="radio" name="OP_LANTAI" value="2" <?php echo (isset($OP_LANTAI) && $OP_LANTAI=="2")?"checked":""?>> Keramik</label></td>
					<td id="colored" colspan=2><label><input type="radio" name="OP_LANTAI" value="3" <?php echo (isset($OP_LANTAI) && $OP_LANTAI=="3")?"checked":""?>> Teraso</label></td></tr>
				<tr><td></td>
					<td id="colored"><label><input type="radio" name="OP_LANTAI" value="4" <?php echo (isset($OP_LANTAI) && $OP_LANTAI=="4")?"checked":""?>> Ubin PC/Papan</label></td>
					<td id="colored" colspan=3><label><input type="radio" name="OP_LANTAI" value="5" <?php echo (isset($OP_LANTAI) && $OP_LANTAI=="5")?"checked":""?>> Semen</label></td></tr>
				<tr><td>15. Langit-langit</td>
					<td><label><input type="radio" name="OP_LANGIT" value="1" <?php echo (isset($OP_LANGIT) && $OP_LANGIT=="1")?"checked":""?> checked> Akustik/Jati</label></td>
					<td><label><input type="radio" name="OP_LANGIT" value="2" <?php echo (isset($OP_LANGIT) && $OP_LANGIT=="2")?"checked":""?>> Triplek/Asbes/Bambu</label></td>
					<td colspan=2><label><input type="radio" name="OP_LANGIT" value="3" <?php echo (isset($OP_LANGIT) && $OP_LANGIT=="3")?"checked":""?>> Tidak ada</label></td></tr>
				<tr><td colspan=5><br><h4>B. Fasilitas</h4></td></tr>
				<tr><td>16. Jumlah AC</td>
					<td>Split <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_AC_SPLIT" size=5 maxlength="3" value="<?php echo isset($FOP_AC_SPLIT)?$FOP_AC_SPLIT:""?>"></td>
					<td colspan=3>Window <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_AC_WINDOW" size=5 maxlength="3" value="<?php echo isset($FOP_AC_WINDOW)?$FOP_AC_WINDOW:""?>"></td></tr>
				<tr><td>17. AC sentral</td>
					<td id="colored"><label><input type="radio" name="FOP_AC_CENTRAL" value="1" <?php echo (isset($FOP_AC_CENTRAL) && $FOP_AC_CENTRAL=="1")?"checked":""?>> Ada</label></td>
					<td id="colored" colspan=3><label><input type="radio" name="FOP_AC_CENTRAL" value="0" <?php echo (isset($FOP_AC_CENTRAL) && $FOP_AC_CENTRAL=="0")?"checked":""?>> Tidak ada</label></td></tr>
				<tr><td>18. Luas kolam renang</td>
					<td><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_KOLAM_LUAS" size=7 maxlength="5" value="<?php echo isset($FOP_KOLAM_LUAS)?$FOP_KOLAM_LUAS:""?>"> m&sup2;</td>
					<td><label><input type="radio" name="FOP_KOLAM_LAPISAN" value="Diplester" <?php echo (isset($FOP_KOLAM_LAPISAN) && $FOP_KOLAM_LAPISAN=="Diplester")?"checked":""?>> Diplester</label></td>
					<td colspan=2><label><input type="radio" name="FOP_KOLAM_LAPISAN" value="Dengan pelapis" <?php echo (isset($FOP_KOLAM_LAPISAN) && $FOP_KOLAM_LAPISAN=="Dengan pelapis")?"checked":""?>> Dengan pelapis</label></td></tr>
				<tr><td>19. Luas Perkerasan Halaman</td>
					<td id="colored">Ringan <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_PERKERASAN_RINGAN" size=7 maxlength="5" value="<?php echo isset($FOP_PERKERASAN_RINGAN)?$FOP_PERKERASAN_RINGAN:""?>"> m&sup2;</td>
					<td id="colored" colspan=3>Berat <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_PERKERASAN_BERAT" size=7 maxlength="5" value="<?php echo isset($FOP_PERKERASAN_BERAT)?$FOP_PERKERASAN_BERAT:""?>"> m&sup2;</td></tr>
				<tr><td></td>
					<td id="colored">Sedang <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_PERKERASAN_SEDANG" size=7 maxlength="5" value="<?php echo isset($FOP_PERKERASAN_SEDANG)?$FOP_PERKERASAN_SEDANG:""?>"> m&sup2;</td>
					<td id="colored" colspan=3>Dengan penutup lantai <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_PERKERASAN_PENUTUP" size=7 maxlength="5" value="<?php echo isset($FOP_PERKERASAN_PENUTUP)?$FOP_PERKERASAN_PENUTUP:""?>"> m&sup2;</td></tr>
				<tr><td>20. Jumlah lapangan tenis</td>
					<td></td>
					<td>Dg lampu</td>
					<td colspan=2>Tnp lampu</td></tr>
				<tr><td></td>
					<td>Beton</td>
					<td><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_LAMPU_BETON" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_LAMPU_BETON)?$FOP_TENIS_LAMPU_BETON:""?>"></td>
					<td colspan=2><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_TANPA_LAMPU_BETON" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_TANPA_LAMPU_BETON)?$FOP_TENIS_TANPA_LAMPU_BETON:""?>"></td></tr>
				<tr><td></td>
					<td>Aspal</td>
					<td><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_LAMPU_ASPAL" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_LAMPU_ASPAL)?$FOP_TENIS_LAMPU_ASPAL:""?>"></td>
					<td colspan=2><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_TANPA_LAMPU_ASPAL" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_TANPA_LAMPU_ASPAL)?$FOP_TENIS_TANPA_LAMPU_ASPAL:""?>"></td></tr>
				<tr><td></td>
					<td>Tanah liat/Rumput</td>
					<td><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_LAMPU_TANAH" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_LAMPU_TANAH)?$FOP_TENIS_LAMPU_TANAH:""?>"></td>
					<td colspan=2><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_TENIS_TANPA_LAMPU_TANAH" size=4 maxlength="2" value="<?php echo isset($FOP_TENIS_TANPA_LAMPU_TANAH)?$FOP_TENIS_TANPA_LAMPU_TANAH:""?>"></td></tr>	
				<tr><td>21. Jumlah lift</td>
					<td id="colored">Penumpang <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_LIFT_PENUMPANG" size=4 maxlength="2" maxlength="2" value="<?php echo isset($FOP_LIFT_PENUMPANG)?$FOP_LIFT_PENUMPANG:""?>"></td>
					<td id="colored">Kapsul <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_LIFT_KAPSUL" size=4 maxlength="2" value="<?php echo isset($FOP_LIFT_KAPSUL)?$FOP_LIFT_KAPSUL:""?>"></td>
					<td id="colored" colspan=2>Barang <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_LIFT_BARANG" size=4 maxlength="2" maxlength="2" value="<?php echo isset($FOP_LIFT_BARANG)?$FOP_LIFT_BARANG:""?>"></td></tr>
				<tr><td>22. Jumlah tangga berjalan</td>
					<td>Lebar < 0,8m <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_ESKALATOR_SEMPIT" size=4 maxlength="2" value="<?php echo isset($FOP_ESKALATOR_SEMPIT)?$FOP_ESKALATOR_SEMPIT:""?>"></td>
					<td colspan=3>Lebar > 0,8m <input type="text" onkeypress="return iniAngka(event,this)" name="FOP_ESKALATOR_LEBAR" size=4 maxlength="2" value="<?php echo isset($FOP_ESKALATOR_LEBAR)?$FOP_ESKALATOR_LEBAR:""?>"></td></tr>
				<tr><td>23. Panjang pagar</td>
					<?php
							if(isset($PAGAR_BESI_PANJANG) || isset($PAGAR_BATA_PANJANG)){
								if($PAGAR_BESI_PANJANG > 0){
									$FOP_PAGAR = $PAGAR_BESI_PANJANG;
									$FOP_PAGAR_BAHAN = "Baja/Besi";
								}else if($PAGAR_BATA_PANJANG > 0){
									$FOP_PAGAR = $PAGAR_BATA_PANJANG;
									$FOP_PAGAR_BAHAN = "Bata/Batako";
								}
							}
							?>
							<td id="colored"><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_PAGAR" size=7 maxlength="4" value="<?php echo isset($FOP_PAGAR)?$FOP_PAGAR:""?>"> m</td>
					<td id="colored"><label><input type="radio" name="FOP_PAGAR_BAHAN" value="Baja/Besi" <?php echo (isset($FOP_PAGAR_BAHAN) && $FOP_PAGAR_BAHAN=="Baja/Besi")?"checked":""?>> Baja/Besi </label></td>
					<td id="colored" colspan=2><label><input type="radio" name="FOP_PAGAR_BAHAN" value="Bata/Batako" <?php echo (isset($FOP_PAGAR_BAHAN) && $FOP_PAGAR_BAHAN=="Bata/Batako")?"checked":""?>> Bata/Batako </label></td></tr>
				<tr><td>24. Pemadam kebakaran</td>
					<td><label><input type="checkbox" name="PEMADAM_HYDRANT" value="1" <?php echo isset($PEMADAM_HYDRANT) && strpos($PEMADAM_HYDRANT,"1")!==false?"checked=yes":""?>> Hydrant</label></td>
					<td><label><input type="checkbox" name="PEMADAM_SPRINKLER" value="1" <?php echo isset($PEMADAM_SPRINKLER) && strpos($PEMADAM_SPRINKLER,"1")!==false?"checked=yes":""?>> Sprinkler</label></td>
					<td colspan=2><label><input type="checkbox" name="PEMADAM_FIRE_ALARM" value="1" <?php echo isset($PEMADAM_FIRE_ALARM) && strpos($PEMADAM_FIRE_ALARM,"1")!==false?"checked=yes":""?>> Fire Alarm</label></td></tr>
				<tr><td>25. Jml saluran pes. PABX</td>
					<td id="colored" colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_SALURAN" size=6 maxlength="4" value="<?php echo isset($FOP_SALURAN)?$FOP_SALURAN:""?>"></td></tr>
				<tr><td>26. Kedalaman sumur artesis</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="FOP_SUMUR" size=6 maxlength="4" value="<?php echo isset($FOP_SUMUR)?$FOP_SUMUR:""?>"> m</td></tr>
					
					<tbody id='a8' style="display:none;">
					<tr><td colspan=5><br><h4>C. Data Tambahan Untuk Bangunan</h4></td></tr>
				<tr><td colspan=5>Bangunan Pabrik/Bengkel/Gudang/Pertanian</td></tr>
				<tr><td>27. Tinggi kolom</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB8_TINGGI_KOLOM" size=7 maxlength="4" value="<?php echo isset($JPB8_TINGGI_KOLOM)?$JPB8_TINGGI_KOLOM:""?>"> m</td></tr>
				<tr><td>28. Lebar bentang</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB8_LEBAR_BENTANG" size=7 maxlength="4" value="<?php echo isset($JPB8_LEBAR_BENTANG)?$JPB8_LEBAR_BENTANG:""?>"> m</td></tr>
				<tr><td>29. Daya dukung lantai</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB8_DAYA_DUKUNG_LANTAI" size=7 maxlength="4" value="<?php echo isset($JPB8_DAYA_DUKUNG_LANTAI)?$JPB8_DAYA_DUKUNG_LANTAI:""?>"> Kg/m&sup2;</td></tr>
				<tr><td>30. Keliling dinding</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB8_KELILING_DINDING" size=7 maxlength="4" value="<?php echo isset($JPB8_KELILING_DINDING)?$JPB8_KELILING_DINDING:""?>"> m</td></tr>
				<tr><td>31. Luas Mezzanine</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB8_LUAS_MEZZANINE" size=7 maxlength="4" value="<?php echo isset($JPB8_LUAS_MEZZANINE)?$JPB8_LUAS_MEZZANINE:""?>"> m&sup2;</td></tr>
				</tbody>
				<tbody id='a3' style="display:none;">
					<tr><td colspan=5><br><h4>C. Data Tambahan Untuk Bangunan</h4></td></tr>
				<tr><td colspan=5>Bangunan Pabrik/Bengkel/Gudang/Pertanian</td></tr>
				<tr><td>27. Tinggi kolom</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB3_TINGGI_KOLOM" size=7 maxlength="4" value="<?php echo isset($JPB3_TINGGI_KOLOM)?$JPB3_TINGGI_KOLOM:""?>"> m</td></tr>
				<tr><td>28. Lebar bentang</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB3_LEBAR_BENTANG" size=7 maxlength="4" value="<?php echo isset($JPB3_LEBAR_BENTANG)?$JPB3_LEBAR_BENTANG:""?>"> m</td></tr>
				<tr><td>29. Daya dukung lantai</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB3_DAYA_DUKUNG_LANTAI" size=7 maxlength="4" value="<?php echo isset($JPB3_DAYA_DUKUNG_LANTAI)?$JPB3_DAYA_DUKUNG_LANTAI:""?>"> Kg/m&sup2;</td></tr>
				<tr><td>30. Keliling dinding</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB3_KELILING_DINDING" size=7 maxlength="4" value="<?php echo isset($JPB3_KELILING_DINDING)?$JPB3_KELILING_DINDING:""?>"> m</td></tr>
				<tr><td>31. Luas Mezzanine</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB3_LUAS_MEZZANINE" size=7 maxlength="4" value="<?php echo isset($JPB3_LUAS_MEZZANINE)?$JPB3_LUAS_MEZZANINE:""?>"> m&sup2;</td></tr>
				</tbody>
				
				<tbody id='a2' style="display:none;">
				<tr><td colspan=5>Bangunan Perkantoran swasta/Gedung pemerintah</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB2_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB2_KELAS_BANGUNAN) && $JPB2_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td><label><input type="radio" name="JPB2_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB2_KELAS_BANGUNAN) && $JPB2_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td>
					<td><label><input type="radio" name="JPB2_KELAS_BANGUNAN" value="3" <?php echo (isset($JPB2_KELAS_BANGUNAN) && $JPB2_KELAS_BANGUNAN=="3")?"checked":""?>> Kelas 3</label></td>
					<td><label><input type="radio" name="JPB2_KELAS_BANGUNAN" value="4" <?php echo (isset($JPB2_KELAS_BANGUNAN) && $JPB2_KELAS_BANGUNAN=="4")?"checked":""?>> Kelas 4</label></td></tr>
				</tbody>
				
					<tbody id='a9' style="display:none;">
				<tr><td colspan=5>Bangunan Perkantoran swasta/Gedung pemerintah</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB9_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB9_KELAS_BANGUNAN) && $JPB9_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td><label><input type="radio" name="JPB9_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB9_KELAS_BANGUNAN) && $JPB9_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td>
					<td><label><input type="radio" name="JPB9_KELAS_BANGUNAN" value="3" <?php echo (isset($JPB9_KELAS_BANGUNAN) && $JPB9_KELAS_BANGUNAN=="3")?"checked":""?>> Kelas 3</label></td>
					<td><label><input type="radio" name="JPB9_KELAS_BANGUNAN" value="4" <?php echo (isset($JPB9_KELAS_BANGUNAN) && $JPB9_KELAS_BANGUNAN=="4")?"checked":""?>> Kelas 4</label></td></tr>
				</tbody>
					
				<tbody id='a4' style="display:none;">
				<tr><td colspan=5>Bangunan Toko/Apotik/Pasar/Ruko</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB4_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB4_KELAS_BANGUNAN) && $JPB4_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td><label><input type="radio" name="JPB4_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB4_KELAS_BANGUNAN) && $JPB4_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td>
					<td colspan=2><label><input type="radio" name="JPB4_KELAS_BANGUNAN" value="3" <?php echo (isset($JPB4_KELAS_BANGUNAN) && $JPB4_KELAS_BANGUNAN=="3")?"checked":""?>> Kelas 3</label></td></tr>
				</tbody>
				
				<tbody id='a5' style="display:none;">
				<tr><td colspan=5>Bangunan Rumah sakit/Klinik</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB5_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB5_KELAS_BANGUNAN) && $JPB5_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td><label><input type="radio" name="JPB5_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB5_KELAS_BANGUNAN) && $JPB5_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td>
					<td><label><input type="radio" name="JPB5_KELAS_BANGUNAN" value="3" <?php echo (isset($JPB5_KELAS_BANGUNAN) && $JPB5_KELAS_BANGUNAN=="3")?"checked":""?>> Kelas 3</label></td>
					<td><label><input type="radio" name="JPB5_KELAS_BANGUNAN" value="4" <?php echo (isset($JPB5_KELAS_BANGUNAN) && $JPB5_KELAS_BANGUNAN=="4")?"checked":""?>> Kelas 4</label></td></tr>
				<tr><td>28. Luas kmr dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB5_LUAS_KMR_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB5_LUAS_KMR_AC_CENTRAL)?$JPB5_LUAS_KMR_AC_CENTRAL:""?>"> m&sup2;</td></tr>
				<tr><td>29. Luas rg lain dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB5_LUAS_RUANG_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB5_LUAS_RUANG_AC_CENTRAL)?$JPB5_LUAS_RUANG_AC_CENTRAL:""?>"></td></tr>
				</tbody>
				
				<tbody id='a6' style="display:none;">
				<tr><td colspan=5>Bangunan Olahraga/Rekreasi</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB6_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB6_KELAS_BANGUNAN) && $JPB6_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td colspan=3><label><input type="radio" name="JPB6_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB6_KELAS_BANGUNAN) && $JPB6_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td></tr>
				</tbody>
				
				<tbody id='a7' style="display:none;">
				<tr><td colspan=5>Bangunan Hotel/Wisma</td></tr>
				<tr><td>27. Jenis hotel</td>
					<td><label><input type="radio" name="JPB7_JENIS_HOTEL" value="1" <?php echo (isset($JPB7_JENIS_HOTEL) && $JPB7_JENIS_HOTEL=="1")?"checked":""?>> Non Resort</label></td>
					<td><label><input type="radio" name="JPB7_JENIS_HOTEL" value="2" <?php echo (isset($JPB7_JENIS_HOTEL) && $JPB7_JENIS_HOTEL=="2")?"checked":""?>> Resort</label></td></tr>
				<tr><td>28. Jumlah bintang</td>
					<td><label><input type="radio" name="JPB7_JUMLAH_BINTANG" value="1" <?php echo (isset($JPB7_JUMLAH_BINTANG) && $JPB7_JUMLAH_BINTANG=="1")?"checked":""?>> Bintang 5</label></td>
					<td><label><input type="radio" name="JPB7_JUMLAH_BINTANG" value="2" <?php echo (isset($JPB7_JUMLAH_BINTANG) && $JPB7_JUMLAH_BINTANG=="2")?"checked":""?>> Bintang 4</label></td>
					<td colspan=2><label><input type="radio" name="JPB7_JUMLAH_BINTANG" value="3" <?php echo (isset($JPB7_JUMLAH_BINTANG) && $JPB7_JUMLAH_BINTANG=="3")?"checked":""?>> Bintang 3</label></td></tr>
				<tr><td></td>
					<td><label><input type="radio" name="JPB7_JUMLAH_BINTANG" value="4" <?php echo (isset($JPB7_JUMLAH_BINTANG) && $JPB7_JUMLAH_BINTANG=="4")?"checked":""?>> Bintang 1-2</label></td>
					<td colspan=3><label><input type="radio" name="JPB7_JUMLAH_BINTANG" value="0" <?php echo (isset($JPB7_JUMLAH_BINTANG) && $JPB7_JUMLAH_BINTANG=="0")?"checked":""?>> Non bintang</label></td></tr>
				<tr><td>29. Jumlah kamar</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB7_JUMLAH_KAMAR" size=6 maxlength="4" value="<?php echo isset($JPB7_JUMLAH_KAMAR)?$JPB7_JUMLAH_KAMAR:""?>"></td></tr>
				<tr><td>30. Luas kmr dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB7_LUAS_KMR_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB7_LUAS_KMR_AC_CENTRAL)?$JPB7_LUAS_KMR_AC_CENTRAL:""?>"></td></tr>
				<tr><td>31. Luas rg lain dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB7_LUAS_RUANG_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB7_LUAS_RUANG_AC_CENTRAL)?$JPB7_LUAS_RUANG_AC_CENTRAL:""?>"></td></tr>
				</tbody>
				
				<tbody id='a12' style="display:none;">
				<tr><td colspan=5>Bangunan Parkir</td></tr>
				<tr><td>27. Tipe bangunan</td>
					<td><label><input type="radio" name="JPB12_TIPE_BANGUNAN" value="4" <?php echo (isset($JPB12_TIPE_BANGUNAN) && $JPB12_TIPE_BANGUNAN=="4")?"checked":""?>> Tipe 4</label></td>
					<td><label><input type="radio" name="JPB12_TIPE_BANGUNAN" value="3" <?php echo (isset($JPB12_TIPE_BANGUNAN) && $JPB12_TIPE_BANGUNAN=="3")?"checked":""?>> Tipe 3</label></td>
					<td><label><input type="radio" name="JPB12_TIPE_BANGUNAN" value="2" <?php echo (isset($JPB12_TIPE_BANGUNAN) && $JPB12_TIPE_BANGUNAN=="2")?"checked":""?>> Tipe 2</label></td>
					<td><label><input type="radio" name="JPB12_TIPE_BANGUNAN" value="1" <?php echo (isset($JPB12_TIPE_BANGUNAN) && $JPB12_TIPE_BANGUNAN=="1")?"checked":""?>> Tipe 1</label></td></tr>
				</tbody>
				
				<tbody id='a13' style="display:none;">
				<tr><td colspan=5>Bangunan Apartemen</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB13_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB13_KELAS_BANGUNAN) && $JPB13_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td><label><input type="radio" name="JPB13_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB13_KELAS_BANGUNAN) && $JPB13_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td>
					<td><label><input type="radio" name="JPB13_KELAS_BANGUNAN" value="3" <?php echo (isset($JPB13_KELAS_BANGUNAN) && $JPB13_KELAS_BANGUNAN=="3")?"checked":""?>> Kelas 3</label></td>
					<td><label><input type="radio" name="JPB13_KELAS_BANGUNAN" value="4" <?php echo (isset($JPB13_KELAS_BANGUNAN) && $JPB13_KELAS_BANGUNAN=="4")?"checked":""?>> Kelas 4</label></td></tr>
				<tr><td>28. Jumlah Apartemen</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB13_JUMLAH_APARTEMEN" size=7 maxlength="4" value="<?php echo isset($JPB13_JUMLAH_APARTEMEN)?$JPB13_JUMLAH_APARTEMEN:""?>"></td></tr>
				<tr><td>29. Luas apt dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB13_LUAS_APARTEMEN_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB13_LUAS_APARTEMEN_AC_CENTRAL)?$JPB13_LUAS_APARTEMEN_AC_CENTRAL:""?>"></td></tr>
				<tr><td>30. Luas rg lain dg AC sentral</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB13_LUAS_RUANG_AC_CENTRAL" size=7 maxlength="4" value="<?php echo isset($JPB13_LUAS_RUANG_AC_CENTRAL)?$JPB13_LUAS_RUANG_AC_CENTRAL:""?>"></td></tr>
				</tbody>
				
				<tbody id='a15' style="display:none;">
				<tr><td colspan=5>Bangunan Tangki Minyak</td></tr>
				<tr><td>27. Kapasitas tangki</td>
					<td colspan=4><input type="text" onkeypress="return iniAngka(event,this)" name="JPB15_TANGKI_MINYAK_KAPASITAS" size=7 maxlength="4" value="<?php echo isset($JPB15_TANGKI_MINYAK_KAPASITAS)?$JPB15_TANGKI_MINYAK_KAPASITAS:""?>"></td></tr>
				<tr><td>28. Letak tangki</td>
					<td><label><input type="radio" name="JPB15_TANGKI_MINYAK_LETAK" value="1" <?php echo (isset($JPB15_TANGKI_MINYAK_LETAK) && $JPB15_TANGKI_MINYAK_LETAK=="1")?"checked":""?>> Di atas tanah</label></td>
					<td colspan=3><label><input type="radio" name="JPB15_TANGKI_MINYAK_LETAK" value="2" <?php echo (isset($JPB15_TANGKI_MINYAK_LETAK) && $JPB15_TANGKI_MINYAK_LETAK=="2")?"checked":""?>> Di bawah tanah</label></td></tr>	
				</tbody>
				
				<tbody id='a16' style="display:none;">
				<tr><td colspan=5>Bangunan Gedung Sekolah</td></tr>
				<tr><td>27. Kelas bangunan</td>
					<td><label><input type="radio" name="JPB16_KELAS_BANGUNAN" value="1" <?php echo (isset($JPB16_KELAS_BANGUNAN) && $JPB16_KELAS_BANGUNAN=="1")?"checked":""?>> Kelas 1</label></td>
					<td colspan=3><label><input type="radio" name="JPB16_KELAS_BANGUNAN" value="2" <?php echo (isset($JPB16_KELAS_BANGUNAN) && $JPB16_KELAS_BANGUNAN=="2")?"checked":""?>> Kelas 2</label></td></tr>
				</tbody>
					
					<!--<input type="hidden" name="PAYMENT_PENILAIAN_BGN" VALUE="sistem">-->
				<tr><td colspan=5><br><h4>D. Penilaian Individual</h4></td></tr>
				<tr>
						<td>Penilaian Bangunan</td>
						<td colspan=4><label><input type="radio" name="PAYMENT_PENILAIAN_BGN" VALUE="sistem" <?php echo (!isset($PAYMENT_PENILAIAN_BGN) || (isset($PAYMENT_PENILAIAN_BGN) && $PAYMENT_PENILAIAN_BGN!="individu"))?"checked":""?>> Penilaian Sistem</label> 
							<input type="text" name="PAYMENT_SISTEM" id="PAYMENT_SISTEM" readonly="true" value="<?php echo isset($PAYMENT_SISTEM)?($PAYMENT_SISTEM*1000):"0"?>">
							<input type="submit" name="newNilai" value="Hitung Nilai" onclick="return confirm('Data akan disimpan ke dalam database sebelum dilakukan penilaian. \nAnda yakin sudah mengisi data dengan benar?')">
				</td>
				</tr>
				<tr>
						<td>&nbsp;</td>
						<td colspan=4>
							<label><input type="radio" name="PAYMENT_PENILAIAN_BGN" VALUE="individu" <?php echo (isset($PAYMENT_PENILAIAN_BGN) && $PAYMENT_PENILAIAN_BGN=="individu")?"checked":""?>> Penilaian Individual</label> 
							<input type="text" name="PAYMENT_INDIVIDU" value="<?php echo isset($PAYMENT_INDIVIDU)?($PAYMENT_INDIVIDU*1000):""?>"></td>
				</tr>
			</table>
			<input type="hidden" name="NJOP_BANGUNAN" id="NJOP_BANGUNAN" value="<?php echo isset($NJOP_BANGUNAN)?$NJOP_BANGUNAN:"0"?>" />
			<input type="hidden" name="jenis" value="<?php echo (isset($jenis) && $jenis=='penggabungan')?"penggabungan":"perubahan"?>" />
			<br>
			<input type="submit" name="newLamp" value="Simpan" onclick="return confirm('Anda yakin sudah mengisi data dengan benar?')">
			<input type="button" value="Batal" onClick="if (confirm('PERINGATAN! Perubahan pada halaman ini belum disimpan! \nBatalkan?')) javascript:window.location='main.php?param=<?php echo base64_encode("a=".$a."&m=".$m)?>';">
		</form>
	</div>
</div>