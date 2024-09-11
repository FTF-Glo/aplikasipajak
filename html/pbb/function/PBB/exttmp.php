<?php echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">"; 

$mapKondisi = array('0' => '-', '' => '-', '1' => 'Sangat baik', '2' => 'Baik', '3' => 'Sedang', '4' => 'Jelek');
$mapKonstruksi = array('0' => '-', '' => '-', '1' => 'Baja', '2' => 'Beton', '3' => 'Batu Bata', '4' => 'Kayu');
$mapLangit2 = array('0' => '-', '' => '-', '1' => 'Akustik/Jati', '2' => 'Triplek/Asbes/Bambu', '3' => 'Tidak ada');
$mapLantai = array('0' => '-', '' => '-', '1' => 'Marmer', '2' => 'Keramik', '3' => 'Teraso', '4' => 'Ubin PC/Papan', '5' => 'Semen');
$mapDinding = array('0' => 'Tidak ada', '' => '-', '1' => 'Kaca/Aluminium', '2' => 'Beton', '3' => 'Batu Bata/Conblok', '7' => 'Kayu', '8' => 'Seng');
$mapAtap = array('0' => '-', '' => '-', '1' => 'Decrabon/Beton/Gtg Glazur', '2' => 'Gtg Beton/Aluminium', '3' => 'Gtg Biasa/Sirap', '4' => 'Asbes', '5' => 'Seng');
$mapACCentral = array('0' => 'Tidak ada', '1' => 'Ada');

$OP_LUAS_BANGUNAN_VIEW = '0';
if(isset($OP_LUAS_BANGUNAN)){
    if(strrchr($OP_LUAS_BANGUNAN,'.') != '')  {
        $OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN,2,',','.');
    }else $OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN,0,',','.');
} 

?>
<div class="frame">
<table border=0 cellspacing=0 cellpadding=2>
	<tr><td colspan=3 align='center'><h4>LAMPIRAN SURAT PEMBERITAHUAN OBJEK PAJAK</h4></td></tr>
	<tr><td colspan=3 align='center'><br><br></td></tr>
	<tr><td>1.</td><td>NOP</td>
			<td><?php echo ($NOP!="") ? $NOP : ""?></td></tr>
	<tr><td>2.</td><td>Jumlah Bangunan</td>
		<td><?php echo ($OP_JML_BANGUNAN!="")?$OP_JML_BANGUNAN:""?></td></tr>
	<tr><td>3.</td><td>Bangunan Ke</td>
		<td><?php echo ($OP_NUM!="")?$OP_NUM:""?></td></tr>
	
	<tr><td colspan=3 class="title-header"><br>A. Rincian Data Bangunan</td></tr>
	<tr><td>4.</td><td>Jenis penggunaan bangunan</td>
		<td><?php echo ($OP_PENGGUNAAN!="")?$JPB[$OP_PENGGUNAAN]:""?></td></tr>
	<tr><td>5.</td><td>Luas bangunan</td>
		<td><?php echo $OP_LUAS_BANGUNAN_VIEW?> m&sup2;</td></tr>
	<tr><td>6.</td><td>Jumlah lantai</td>
		<td><?php echo ($OP_JML_LANTAI!="")?$OP_JML_LANTAI:"-"?></td></tr>
	<tr><td>7.</td><td>Tahun dibangun</td>
		<td><?php echo ($OP_THN_DIBANGUN!="")?$OP_THN_DIBANGUN:"-"?></td></tr>
	<tr><td>8.</td><td>Tahun direnovasi</td>
		<td><?php echo ($OP_THN_RENOVASI!="")?$OP_THN_RENOVASI:"-"?></td></tr>
	<tr><td>9.</td><td>Daya listrik terpasang</td>
		<td><?php echo ($OP_DAYA!="")?$OP_DAYA:"-"?></td></tr>
	<tr><td>10.</td><td>Kondisi pada umumnya</td>
		<td><?php echo ($OP_KONDISI!="")?$mapKondisi[$OP_KONDISI]:"-"?></td></tr>
	<tr><td>11.</td><td>Konstruksi</td>
		<td><?php echo ($OP_KONSTRUKSI!="")?$mapKonstruksi[$OP_KONSTRUKSI]:"-"?></td></tr>
	<tr><td>12.</td><td>Atap</td>
		<td><?php echo ($OP_ATAP!="")?$mapAtap[$OP_ATAP]:"-"?></td></tr>
	<tr><td>13.</td><td>Dinding</td>
		<td><?php echo ($OP_DINDING!="")?$mapDinding[$OP_DINDING]:"-"?></td></tr>
	<tr><td>14.</td><td>Lantai</td>
		<td><?php echo ($OP_LANTAI!="")?$mapLantai[$OP_LANTAI]:"-"?></td></tr>
	<tr><td>15.</td><td>Langit-langit</td>
		<td><?php echo ($OP_LANGIT!="")?$mapLangit2[$OP_LANGIT]:"-"?></td></tr>
	
	<tr><td colspan=3 class="title-header"><br>B. Fasilitas</td></tr>
	<tr><td>16.</td><td>Jumlah AC</td>
		<td>Split: <?php echo ($FOP_AC_SPLIT!="")?$FOP_AC_SPLIT:"0"?>, Window: <?php echo ($FOP_AC_WINDOW!="")?$FOP_AC_WINDOW:"0"?></td></tr>
	<tr><td>17.</td><td>AC sentral</td>
		<td><?php echo ($FOP_AC_CENTRAL!="")?$mapACCentral[$FOP_AC_CENTRAL]:"-"?></td></tr>
	<tr><td>18.</td><td>Luas kolam renang</td>
		<td><?php echo ($FOP_KOLAM_LUAS!="")?$FOP_KOLAM_LUAS:"0"?> m&sup2; <?php echo ($FOP_KOLAM_LAPISAN!="")?"(".$FOP_KOLAM_LAPISAN.")":""?></td></tr>
	<tr><td>19.</td><td>Luas Perkerasan Halaman</td>
		<td><span class='spacer'>Ringan: <?php echo ($FOP_PERKERASAN_RINGAN!="")?$FOP_PERKERASAN_RINGAN:"0"?> m&sup2;</span>
			<span class='spacer'>Berat: <?php echo ($FOP_PERKERASAN_BERAT!="")?$FOP_PERKERASAN_BERAT:"0"?> m&sup2;</span>
		</td></tr>
	<tr><td></td><td></td>
		<td><span class='spacer'>Sedang: <?php echo ($FOP_PERKERASAN_SEDANG!="")?$FOP_PERKERASAN_SEDANG:"0"?> m&sup2;</span>
			<span class='spacer'>Dengan penutup lantai: <?php echo ($FOP_PERKERASAN_PENUTUP!="")?$FOP_PERKERASAN_PENUTUP:"0"?> m&sup2;</span>
		</td></tr>
	<tr>
            <td>20.</td>
            <td>Jumlah lapangan tenis</td>
            <td>
                <table>
                    <tr>
                        <td></td>
                        <td>Dengan Lampu</td>
                        <td>Tanpa Lampu</td>
                    </tr>
                    <tr>
                        <td>Beton</td>
                        <td align="center"><?php echo ($FOP_TENIS_LAMPU_BETON!="")?$FOP_TENIS_LAMPU_BETON:"-"?></td>
                        <td align="center"><?php echo ($FOP_TENIS_TANPA_LAMPU_BETON!="")?$FOP_TENIS_TANPA_LAMPU_BETON:"-"?></td>
                    </tr>
                    <tr>
                        <td>Aspal</td>
                        <td align="center"><?php echo ($FOP_TENIS_LAMPU_ASPAL!="")?$FOP_TENIS_LAMPU_ASPAL:"-"?></td>
                        <td align="center"><?php echo ($FOP_TENIS_TANPA_LAMPU_ASPAL!="")?$FOP_TENIS_TANPA_LAMPU_ASPAL:"-"?></td>
                    </tr>
                    <tr>
                        <td>Tanah liat / Rumput</td>
                        <td align="center"><?php echo ($FOP_TENIS_LAMPU_TANAH!="")?$FOP_TENIS_LAMPU_TANAH:"-"?></td>
                        <td align="center"><?php echo ($FOP_TENIS_TANPA_LAMPU_TANAH!="")?$FOP_TENIS_TANPA_LAMPU_TANAH:"-"?></td>
                    </tr>
                </table>
            </td>
        </tr>
	<tr><td>21.</td><td>Jumlah lift</td>
		<td>Penumpang: <?php echo ($FOP_LIFT_PENUMPANG!="")?$FOP_LIFT_PENUMPANG:"0"?>, Kapsul: <?php echo ($FOP_LIFT_KAPSUL!="")?$FOP_LIFT_KAPSUL:"0"?>, Barang: <?php echo ($FOP_LIFT_BARANG!="")?$FOP_LIFT_BARANG:"0"?></td></tr>
	<tr><td>22.</td><td>Jumlah tangga berjalan</td>
		<td>Lebar < 0,8m: <?php echo ($FOP_ESKALATOR_SEMPIT!="")?$FOP_ESKALATOR_SEMPIT:"0"?>, Lebar > 0,8m: <?php echo ($FOP_ESKALATOR_LEBAR!="")?$FOP_ESKALATOR_LEBAR:"0"?></td></tr>
	<tr><td>23.</td><td>Panjang pagar</td>
            <?php
                $FOP_PAGAR = 0;
                $FOP_PAGAR_BAHAN = '';
                if($CPM_PAGAR_BESI_PANJANG > 0) {$FOP_PAGAR = $CPM_PAGAR_BESI_PANJANG; $FOP_PAGAR_BAHAN='Baja/Besi';}
                else if($CPM_PAGAR_BATA_PANJANG > 0) {$FOP_PAGAR = $CPM_PAGAR_BATA_PANJANG; $FOP_PAGAR_BAHAN=Bata/Batako;}
            ?>
		<td><?php echo ($FOP_PAGAR!="")?$FOP_PAGAR:""?> m<?php echo ($FOP_PAGAR_BAHAN!="")?", ".$FOP_PAGAR_BAHAN:""?></td></tr>
	<tr><td>24.</td><td>Pemadam kebakaran</td>
		<td><?php
                    $arrPemadam = array();
                    if($PEMADAM_HYDRANT=="1") $arrPemadam[] = "Hydrant";
                    if($PEMADAM_SPRINKLER=="1") $arrPemadam[] = "Sprinkler";
                    if($PEMADAM_FIRE_ALARM=="1") $arrPemadam[] = "Fire Alarm";
                        echo join($arrPemadam, ', ');
                    ?>
                </td></tr>
	<tr><td>25.</td><td>Jml saluran pes. PABX</td>
		<td><?php echo ($FOP_SALURAN!="")?$FOP_SALURAN:"-"?></td></tr>
	<tr><td>26.</td><td>Kedalaman sumur artesis</td>
		<td><?php echo ($FOP_SUMUR!="")?$FOP_SUMUR:"0"?> m</td></tr>
	
	<tr><td colspan=3 class="title-header"><br>C. Data Tambahan Untuk Bangunan</td></tr>
<?php
if ($OP_PENGGUNAAN==3) {
?>
	<tr><td colspan=3>Bangunan Pabrik</td></tr>
	<tr><td>27.</td><td>Tinggi kolom</td>
		<td><?php echo ($JPB3_TINGGI_KOLOM!="0")?$JPB3_TINGGI_KOLOM:"-"?></td></tr>
	<tr><td>28.</td><td>Lebar bentang</td>
		<td><?php echo ($JPB3_LEBAR_BENTANG!="0")?$JPB3_LEBAR_BENTANG:"-"?></td></tr>
	<tr><td>29.</td><td>Daya dukung lantai</td>
		<td><?php echo ($JPB3_DAYA_DUKUNG_LANTAI!="0")?$JPB3_DAYA_DUKUNG_LANTAI:"-"?></td></tr>
	<tr><td>30.</td><td>Keliling dinding</td>
		<td><?php echo ($JPB3_KELILING_DINDING!="0")?$JPB3_KELILING_DINDING:"-"?></td></tr>
	<tr><td>31.</td><td>Luas Mezzanine</td>
		<td><?php echo ($JPB3_LUAS_MEZZANINE!="0")?$JPB3_LUAS_MEZZANINE:"-"?></td></tr>		
<?php
}

if ($OP_PENGGUNAAN==8) {
?>	
	<tr><td colspan=3>Bangunan Bengkel/Gudang/Pertanian</td></tr>
	<tr><td>27.</td><td>Tinggi kolom</td>
		<td><?php echo ($JPB8_TINGGI_KOLOM!="0")?$JPB8_TINGGI_KOLOM:"-"?></td></tr>
	<tr><td>28.</td><td>Lebar bentang</td>
		<td><?php echo ($JPB8_LEBAR_BENTANG!="0")?$JPB8_LEBAR_BENTANG:"-"?></td></tr>
	<tr><td>29.</td><td>Daya dukung lantai</td>
		<td><?php echo ($JPB8_DAYA_DUKUNG_LANTAI!="0")?$JPB8_DAYA_DUKUNG_LANTAI:"-"?></td></tr>
	<tr><td>30.</td><td>Keliling dinding</td>
		<td><?php echo ($JPB8_KELILING_DINDING!="0")?$JPB8_KELILING_DINDING:"-"?></td></tr>
	<tr><td>31.</td><td>Luas Mezzanine</td>
		<td><?php echo ($JPB8_LUAS_MEZZANINE!="0")?$JPB8_LUAS_MEZZANINE:"-"?></td></tr>		
<?php
}

if ($OP_PENGGUNAAN == 2) {
?>
	<tr><td colspan=3>Bangunan Perkantoran Swasta</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB2_KELAS_BANGUNAN!="0")? 'Kelas '.$JPB2_KELAS_BANGUNAN:"-"?></td></tr>	
<?php
}

if ($OP_PENGGUNAAN == 9) {
?>
	<tr><td colspan=3>Bangunan Gedung Pemerintah</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB9_KELAS_BANGUNAN!="0")? 'Kelas '.$JPB9_KELAS_BANGUNAN:"-"?></td></tr>	
<?php
}
if ($OP_PENGGUNAAN == 4) {
?>
	<tr><td colspan=3>Bangunan Toko/Apotik/Pasar/Ruko</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB4_KELAS_BANGUNAN!="0")? 'Kelas '.$JPB4_KELAS_BANGUNAN:"-"?></td></tr>	
<?php
}

if ($OP_PENGGUNAAN == 5) {
?>
	<tr><td colspan=3>Bangunan Rumah Sakit/Klinik</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB5_KELAS_BANGUNAN!="0")?'Kelas '.$JPB5_KELAS_BANGUNAN:"-"?></td></tr>
	<tr><td>28.</td><td>Luas kmr dg AC sentral</td>
		<td><?php echo ($JPB5_LUAS_KMR_AC_CENTRAL!="")?$JPB5_LUAS_KMR_AC_CENTRAL:"0"?> m&sup2;</td></tr>
	<tr><td>29.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($JPB5_LUAS_RUANG_AC_CENTRAL!="")?$JPB5_LUAS_RUANG_AC_CENTRAL:"0"?> m&sup2;</td></tr>
	
<?php
}

if ($OP_PENGGUNAAN == 6) {
?>
	<tr><td colspan=3>Bangunan Olahraga/Rekreasi</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB6_KELAS_BANGUNAN!="0")?'Kelas '.$JPB6_KELAS_BANGUNAN:"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 7) {
    $mapJenisHotel = array('0' => 'Non Resort', '1' => 'Non Resort', '2' => 'Resort');
    $mapBintang = array('' => 'Non Bintang', '0' => 'Non Bintang', '1' => 'Bintang 5', '2' => 'Bintang 4', '3' => 'Bintang 3', '4' => 'Bintang 1-2');
    
?>
	<tr><td colspan=3>Bangunan Hotel/Wisma</td></tr>
	<tr><td>27.</td><td>Jenis hotel</td>
		<td><?php echo $mapJenisHotel[$JPB7_JENIS_HOTEL]?></td></tr>
	<tr><td>28.</td><td>Jumlah bintang</td>
		<td><?php echo $mapBintang[$JPB7_JUMLAH_BINTANG]?></td></tr>
	<tr><td>29.</td><td>Jumlah kamar</td>
		<td><?php echo ($JPB7_JUMLAH_KAMAR!="0")?$JPB7_JUMLAH_KAMAR:"-"?></td></tr>
	<tr><td>30.</td><td>Luas kmr dg AC sentral</td>
		<td><?php echo ($JPB7_LUAS_KMR_AC_CENTRAL!="")?$JPB7_LUAS_KMR_AC_CENTRAL:"0"?> m&sup2;</td></tr>
	<tr><td>31.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($JPB7_LUAS_RUANG_AC_CENTRAL!="")?$JPB7_LUAS_RUANG_AC_CENTRAL:"0"?> m&sup2;</td></tr>
	
<?php
}

if ($OP_PENGGUNAAN == 12) {
?>
	<tr><td colspan=3>Bangunan Parkir</td></tr>
	<tr><td>27.</td><td>Tipe bangunan</td>
		<td><?php echo ($JPB12_TIPE_BANGUNAN!="")? 'Tipe '.$JPB12_TIPE_BANGUNAN:"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 13) {
?>
	<tr><td colspan=3>Bangunan Apartemen</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB13_KELAS_BANGUNAN!="0")? 'Kelas '.$JPB13_KELAS_BANGUNAN:"-"?></td></tr>
	<tr><td>28.</td><td>Jumlah Apartemen</td>
		<td><?php echo ($JPB13_JUMLAH_APARTEMEN!="")?$JPB13_JUMLAH_APARTEMEN:"-"?></td></tr>
	<tr><td>29.</td><td>Luas apt dg AC sentral</td>
		<td><?php echo ($JPB13_LUAS_APARTEMEN_AC_CENTRAL!="")?$JPB13_LUAS_APARTEMEN_AC_CENTRAL:"0"?> m&sup2;</td></tr>
	<tr><td>30.</td><td>Luar rg lain dg AC sentral</td>
		<td><?php echo ($JPB13_LUAS_RUANG_AC_CENTRAL!="")?$JPB13_LUAS_RUANG_AC_CENTRAL:"0"?> m&sup2;</td></tr>
<?php
}

if ($OP_PENGGUNAAN == 15) {
?>
	<tr><td colspan=3>Bangunan Tangki Minyak</td></tr>
	<tr><td>27.</td><td>Kapasitas tangki</td>
		<td><?php echo ($JPB15_TANGKI_MINYAK_KAPASITAS!="")?$JPB15_TANGKI_MINYAK_KAPASITAS:"-"?></td></tr>
	<tr><td>28.</td><td>Letak tangki</td>
		<td><?php echo ($JPB15_TANGKI_MINYAK_LETAK!="")?(($JPB15_TANGKI_MINYAK_LETAK==1)?"Di atas tanah":($JPB15_TANGKI_MINYAK_LETAK==2)?"Di bawah tanah":"-"):"-"?></td></tr>
<?php
}

if ($OP_PENGGUNAAN == 16) {
?>
	<tr><td colspan=3>Bangunan Gedung Sekolah</td></tr>
	<tr><td>27.</td><td>Kelas bangunan</td>
		<td><?php echo ($JPB16_KELAS_BANGUNAN!="0")? 'Kelas '.$JPB16_KELAS_BANGUNAN:"-"?></td></tr>
<?php
}
?>
	
	<tr><td colspan=3 class="title-header"><br>D. Penilaian Sistem</td></tr>
	<tr><td></td><td>Nilai Sistem</td>
		<td><?php echo ($PAYMENT_SISTEM!="")?$PAYMENT_SISTEM:"-"?></td></tr>
	<!--<tr><td>53.</td><td>Penilaian Individual</td>
		<td><?php echo ($PAYMENT_INDIVIDU!="")?$PAYMENT_INDIVIDU:"-"?></td></tr>-->
</table>
<input type="button" value="Kembali" onClick="javascript:history.back()">
</div>