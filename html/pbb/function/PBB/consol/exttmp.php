<table border=0 cellspacing=0 cellpadding=2>
	<tr><td colspan=3 align='center'><h4>LAMPIRAN SURAT PEMBERITAHUAN OBJEK PAJAK</h4></td></tr>
	<tr><td colspan=3 align='center'><br><br></td></tr>
	<tr><td>2.</td><td>NOP</td>
			<td><?php echo ($NOP!="") ? $NOP : ""?></td></tr>
	<tr><td>3.</td><td>Jumlah Bangunan</td>
		<td><?php echo ($OP_JML_BANGUNAN!="")?$OP_JML_BANGUNAN:""?></td></tr>
	<tr><td>4.</td><td>Bangunan Ke</td>
		<td><?php echo ($OP_NUM!="")?$OP_NUM:""?></td></tr>
	
	<tr><td colspan=3 class="title-header"><br>A. Rincian Data Bangunan</td></tr>
	<tr><td>5.</td><td>Jenis penggunaan bangunan</td>
		<td><?php echo ($OP_PENGGUNAAN!="")?$JPB[$OP_PENGGUNAAN]:""?></td></tr>
	<tr><td>6.</td><td>Luas bangunan</td>
		<td><?php echo ($OP_LUAS_BANGUNAN!="")?$OP_LUAS_BANGUNAN:"0"?> m&sup2;</td></tr>
	<tr><td>7.</td><td>Jumlah lantai</td>
		<td><?php echo ($OP_JML_LANTAI!="")?$OP_JML_LANTAI:"-"?></td></tr>
	<tr><td>8.</td><td>Tahun dibangun</td>
		<td><?php echo ($OP_THN_DIBANGUN!="")?$OP_THN_DIBANGUN:"-"?></td></tr>
	<tr><td>9.</td><td>Tahun direnovasi</td>
		<td><?php echo ($OP_THN_RENOVASI!="")?$OP_THN_RENOVASI:"-"?></td></tr>
	<tr><td>10.</td><td>Daya listrik terpasang</td>
		<td><?php echo ($OP_DAYA!="")?$OP_DAYA:"-"?></td></tr>
	<tr><td>11.</td><td>Kondisi pada umumnya</td>
		<td><?php echo ($OP_KONDISI!="")?$OP_KONDISI:"-"?></td></tr>
	<tr><td>12.</td><td>Konstruksi</td>
		<td><?php echo ($OP_KONSTRUKSI!="")?$OP_KONSTRUKSI:"-"?></td></tr>
	<tr><td>13.</td><td>Atap</td>
		<td><?php echo ($OP_ATAP!="")?$OP_ATAP:"-"?></td></tr>
	<tr><td>14.</td><td>Dinding</td>
		<td><?php echo ($OP_DINDING!="")?$OP_DINDING:"-"?></td></tr>
	<tr><td>15.</td><td>Lantai</td>
		<td><?php echo ($OP_LANTAI!="")?$OP_LANTAI:"-"?></td></tr>
	<tr><td>16.</td><td>Langit-langit</td>
		<td><?php echo ($OP_LANGIT!="")?$OP_LANGIT:"-"?></td></tr>
	
	<tr><td colspan=3 class="title-header"><br>B. Fasilitas</td></tr>
	<tr><td>17.</td><td>Jumlah AC</td>
		<td>Split: <?php echo ($FOP_AC_SPLIT!="")?$FOP_AC_SPLIT:"0"?>, Window: <?php echo ($FOP_AC_WINDOW!="")?$FOP_AC_WINDOW:"0"?></td></tr>
	<tr><td>18.</td><td>AC sentral</td>
		<td><?php echo ($FOP_AC_CENTRAL!="")?$FOP_AC_CENTRAL:"-"?></td></tr>
	<tr><td>19.</td><td>Luas kolam renang</td>
		<td><?php echo ($FOP_KOLAM_LUAS!="")?$FOP_KOLAM_LUAS:"0"?> m&sup2; <?php echo ($FOP_KOLAM_LAPISAN!="")?"(".$FOP_KOLAM_LAPISAN.")":""?></td></tr>
	<tr><td>20.</td><td>Luas Perkerasan Halaman</td>
		<td><span class='spacer'>Ringan: <?php echo ($FOP_PERKERASAN_RINGAN!="")?$FOP_PERKERASAN_RINGAN:"0"?> m&sup2;</span>
			<span class='spacer'>Berat: <?php echo ($FOP_PERKERASAN_BERAT!="")?$FOP_PERKERASAN_BERAT:"0"?> m&sup2;</span>
		</td></tr>
	<tr><td></td><td></td>
		<td><span class='spacer'>Sedang: <?php echo ($FOP_PERKERASAN_SEDANG!="")?$FOP_PERKERASAN_SEDANG:"0"?> m&sup2;</span>
			<span class='spacer'>Dengan penutup lantai: <?php echo ($FOP_PERKERASAN_PENUTUP!="")?$FOP_PERKERASAN_PENUTUP:"0"?> m&sup2;</span>
		</td></tr>
	<tr><td>21.</td><td>Jumlah lapangan tenis</td>
		<td><span class='spacer'>&nbsp;</span>
			<span class='spacer'>Dgn Lampu</span>
			<span class='spacer'>Tanpa Lampu</span>
		</td></tr>
	<tr><td></td><td></td>
		<td><span class='spacer'>Beton</span>
			<span class='spacer'><?php echo ($FOP_TENIS_LAMPU_BETON!="")?$FOP_TENIS_LAMPU_BETON:"-"?></span>
			<span class='spacer'><?php echo ($FOP_TENIS_TANPA_LAMPU_BETON!="")?$FOP_TENIS_TANPA_LAMPU_BETON:"-"?></span>
		</td></tr>
	<tr><td></td><td></td>
		<td><span class='spacer'>Aspal</span>
			<span class='spacer'><?php echo ($FOP_TENIS_LAMPU_ASPAL!="")?$FOP_TENIS_LAMPU_ASPAL:"-"?></span>
			<span class='spacer'><?php echo ($FOP_TENIS_TANPA_LAMPU_ASPAL!="")?$FOP_TENIS_TANPA_LAMPU_ASPAL:"-"?></span>
		</td></tr>
	<tr><td></td><td></td>
		<td><span class='spacer'>Tanah liat/Rumput</span>
			<span class='spacer'><?php echo ($FOP_TENIS_LAMPU_TANAH!="")?$FOP_TENIS_LAMPU_TANAH:"-"?></span>
			<span class='spacer'><?php echo ($FOP_TENIS_TANPA_LAMPU_TANAH!="")?$FOP_TENIS_TANPA_LAMPU_TANAH:"-"?></span>
		</td></tr>
	<tr><td>22.</td><td>Jumlah lift</td>
		<td>Penumpang:<?php echo ($FOP_LIFT_PENUMPANG!="")?$FOP_LIFT_PENUMPANG:"0"?>, Kapsul:<?php echo ($FOP_LIFT_KAPSUL!="")?$FOP_LIFT_KAPSUL:"0"?>, Barang:<?php echo ($FOP_LIFT_BARANG!="")?$FOP_LIFT_BARANG:"0"?></td></tr>
	<tr><td>23.</td><td>Jumlah tangga berjalan</td>
		<td>Lebar < 0,8m:<?php echo ($FOP_ESKALATOR_SEMPIT!="")?$FOP_ESKALATOR_SEMPIT:"0"?>, Lebar > 0,8m:<?php echo ($FOP_ESKALATOR_LEBAR!="")?$FOP_ESKALATOR_LEBAR:"0"?></td></tr>
	<tr><td>24.</td><td>Panjang pagar</td>
		<td><?php echo ($FOP_PAGAR!="")?$FOP_PAGAR:""?> m<?php echo ($FOP_PAGAR_BAHAN!="")?", ".$FOP_PAGAR_BAHAN:""?></td></tr>
	<tr><td>25.</td><td>Pemadam kebakaran</td>
		<td><?php echo ($FOP_PEMADAM!="")?$FOP_PEMADAM:"-"?></td></tr>
	<tr><td>26.</td><td>Jml saluran pes. PABX</td>
		<td><?php echo ($FOP_SALURAN!="")?$FOP_SALURAN:"-"?></td></tr>
	<tr><td>27.</td><td>Kedalaman sumur artesis</td>
		<td><?php echo ($FOP_SUMUR!="")?$FOP_SUMUR:"0"?> m</td></tr>
	
	<tr><td colspan=3 class="title-header"><br>C. Data Tambahan Untuk Bangunan</td></tr>
<?php
if ($OP_PENGGUNAAN==3 || $OP_PENGGUNAAN==8) {
?>	
	<tr><td colspan=3>Bangunan Pabrik/Bengkel/Gudang/Pertanian</td></tr>
	<tr><td>28.</td><td>Tinggi kolom</td>
		<td><?php echo ($PABRIK_TINGGI!="")?$PABRIK_TINGGI:"-"?></td></tr>
	<tr><td>29.</td><td>Lebar bentang</td>
		<td><?php echo ($PABRIK_LEBAR!="")?$PABRIK_LEBAR:"-"?></td></tr>
	<tr><td>30.</td><td>Daya dukung lantai</td>
		<td><?php echo ($PABRIK_DAYA!="")?$PABRIK_DAYA:"-"?></td></tr>
	<tr><td>31.</td><td>Keliling dinding</td>
		<td><?php echo ($PABRIK_KELILING!="")?$PABRIK_KELILING:"-"?></td></tr>
	<tr><td>32.</td><td>Luas Mezzanine</td>
		<td><?php echo ($PABRIK_LUAS!="")?$PABRIK_LUAS:"-"?></td></tr>		
<?php
}

if ($OP_PENGGUNAAN == 2 || $OP_PENGGUNAAN == 9) {
?>
	<tr><td colspan=3>Bangunan Perkantoran swasta/Gedung pemerintah</td></tr>
	<tr><td>33.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>	
<?php
}

if ($OP_PENGGUNAAN == 4) {
?>
	<tr><td colspan=3>Bangunan Toko/Apotik/Pasar/Ruko</td></tr>
	<tr><td>34.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>	
<?php
}

if ($OP_PENGGUNAAN == 5) {
?>
	<tr><td colspan=3>Bangunan Rumah sakit/Klinik</td></tr>
	<tr><td>35.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
	<tr><td>36.</td><td>Luas kmr dg AC sentral</td>
		<td><?php echo ($OP_LUAS_KMR!="")?$OP_LUAS_KMR:"0"?> m&sup2;</td></tr>
	<tr><td>37.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($OP_LUAS_LAIN!="")?$OP_LUAS_LAIN:"0"?> m&sup2;</td></tr>
	
<?php
}

if ($OP_PENGGUNAAN == 6) {
?>
	<tr><td colspan=3>Bangunan Olahraga/Rekreasi</td></tr>
	<tr><td>38.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 7) {
?>
	<tr><td colspan=3>Bangunan Hotel/Wisma</td></tr>
	<tr><td>39.</td><td>Jenis hotel</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
	<tr><td>40.</td><td>Jumlah bintang</td>
		<td><?php echo ($OP_HOTEL_BINTANG!="")?$OP_HOTEL_BINTANG:"-"?></td></tr>
	<tr><td>41.</td><td>Jumlah kamar</td>
		<td><?php echo ($OP_JML_KMR!="")?$OP_JML_KMR:"-"?></td></tr>
	<tr><td>42.</td><td>Luas kmr dg AC sentral</td>
		<td><?php echo ($OP_LUAS_KMR!="")?$OP_LUAS_KMR:"0"?> m&sup2;</td></tr>
	<tr><td>43.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($OP_LUAS_LAIN!="")?$OP_LUAS_LAIN:"0"?> m&sup2;</td></tr>
	
<?php
}

if ($OP_PENGGUNAAN == 12) {
?>
	<tr><td colspan=3>Bangunan Parkir</td></tr>
	<tr><td>44.</td><td>Tipe bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 13) {
?>
	<tr><td colspan=3>Bangunan Apartemen</td></tr>
	<tr><td>45.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
	<tr><td>46.</td><td>Jumlah Apartemen</td>
		<td><?php echo ($OP_JML_KMR!="")?$OP_JML_KMR:"-"?></td></tr>
	<tr><td>47.</td><td>Luas apt dg AC sentral</td>
		<td><?php echo ($OP_LUAS_KMR!="")?$OP_LUAS_KMR:"0"?> m&sup2;</td></tr>
	<tr><td>48.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($OP_LUAS_LAIN!="")?$OP_LUAS_LAIN:"0"?> m&sup2;</td></tr>
<?php
}

if ($OP_PENGGUNAAN == 15) {
?>
	<tr><td colspan=3>Bangunan Tangki Minyak</td></tr>
	<tr><td>49.</td><td>Kapasitas tangki</td>
		<td><?php echo ($OP_TANGKI_KAPASITAS!="")?$OP_TANGKI_KAPASITAS:"-"?></td></tr>
	<tr><td>50.</td><td>Letak tangki</td>
		<td><?php echo ($OP_TANGKI_LETAK!="")?(($OP_TANGKI_LETAK==1)?"Di atas tanah":($OP_TANGKI_LETAK==2)?"Di bawah tanah":"-"):"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 16) {
?>
	<tr><td colspan=3>Bangunan Gedung Sekolah</td></tr>
	<tr><td>51.</td><td>Kelas bangunan</td>
		<td><?php echo ($OP_KELAS!="")?$OP_KELAS:"-"?></td></tr>
<?
}
?>
	
	<tr><td colspan=3 class="title-header"><br>D. Penilaian Individual</td></tr>
	<tr><td>52.</td><td>Nilai Sistem</td>
		<td><?php echo ($PAYMENT_SISTEM!="")?$PAYMENT_SISTEM:"-"?></td></tr>
	<tr><td>53.</td><td>Penilaian Individual</td>
		<td><?php echo ($PAYMENT_INDIVIDU!="")?$PAYMENT_INDIVIDU:"-"?></td></tr>
</table>
<input type="button" value="Kembali" onClick="javascript:history.back()">