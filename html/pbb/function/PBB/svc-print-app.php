<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

error_reporting(E_ALL);
ini_set("display_errors", 1); 

function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getData($idd, $v) {
	global $DBLink;
	
	//ambil data untuk SPPT
	$query = "SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$idd' AND CPM_SPPT_DOC_VERSION='$v'";
	
	$res = mysqli_query($DBLink, $query);
	if (!$res){
		echo $query."<br>";
		echo mysqli_error($DBLink);
	}
	
	$json = new Services_JSON();
	$data =  $json->decode(mysql2json($res,"data"));	
	$dt->data = $data->data[0];

	//ambil data untuk lampiran SPPT
	$total = $dt->data->CPM_OP_JML_BANGUNAN;
	for ($i=0; $i<$total; $i++) {
		$query = "SELECT * FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='$idd' AND CPM_SPPT_DOC_VERSION='$v' AND CPM_OP_NUM='".($i+1)."'";
		$res = mysqli_query($DBLink, $query);
		if (!$res){
			echo $query."<br>";
			echo mysqli_error($DBLink);
		} else {
			$data =  $json->decode(mysql2json($res,"data"));
			
			if (count($data->data)>0) {
				$dt->lamp[$i] = $data->data[0];
			}
		}
	}
	return $dt;
}

function getDataHTML($data) {
	global $sRootPath;
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	
	$HTML = "
		<link rel=\"stylesheet\" href=\"../../function/PBB/viewspop.css\" type=\"text/css\">
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"pdftable\">
			<tr><td colspan=\"3\" style=\"border-bottom:black solid 1px;\" align=\"center\">
				<table border=\"0\">
					<tr><td rowspan=\"4\" width=\"25%\" align=\"center\"><img src=\"".$sRootPath."inc/PBB/symbol.jpg\" style=\"height:50px; width=50px\"></td>
						<td align=\"center\" width=\"50%\">DEPARTEMEN KEUANGAN REPUBLIK INDONESIA</td><td rowspan=\"4\" width=\"25%\"></td></tr>
					<tr><td align=\"center\">DIREKTORAT JENDERAL PAJAK</td></tr>
					<tr><td align=\"center\"><hr style=\"border: black solid 1px;\"></td></tr>
					<tr><td align=\"center\" style=\"font-size:large; font-weight:bold\">SURAT PEMBERITAHUAN OBJEK PAJAK<br></td></tr>
				</table>
			</td></tr>
			

			<tr><td width=\"5%\" >1.</td><td width=\"20%\">NOP</td>
				<td width=\"75%\">".$data->CPM_NOP."</td></tr>
			<tr><td>2.</td><td>NOP Bersama</td>
				<td>".$data->CPM_NOP_BERSAMA."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">A. Data Letak Objek Pajak</td></tr>
			<tr><td>3.</td><td>Nama Jalan</td>
				<td>".$data->CPM_OP_ALAMAT."</td></tr>
			<tr><td>4.</td><td>Blok/Kav/Nomor</td>
				<td>".$data->CPM_OP_NOMOR."</td></tr>
			<tr><td>5.</td><td>Kelurahan/Desa</td>
				<td>".$data->CPM_OP_KELURAHAN."</td></tr>
			<tr><td>6.</td><td>RW</td>
				<td>".$data->CPM_OP_RW."</td></tr>
			<tr><td>7.</td><td>RT</td>
				<td>".$data->CPM_OP_RT."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">B. Data Subjek Pajak</td></tr>
			<tr><td>8.</td><td>Status</td>
				<td>".$data->CPM_WP_STATUS."</td></tr>
			<tr><td>9.</td><td>Pekerjaan</td>
				<td>".$data->CPM_WP_PEKERJAAN."</td></tr>
			<tr><td>10.</td><td>Nama Subjek Pajak</td>
				<td>".$data->CPM_WP_NAMA."</td></tr>
			<tr><td>11.</td><td>Nama Jalan</td>
				<td>".$data->CPM_WP_ALAMAT."</td></tr>
			<tr><td>12.</td><td>Kelurahan/Desa</td>
				<td>".$data->CPM_WP_KELURAHAN."</td></tr>
			<tr><td>13.</td><td>RW</td>
				<td>".$data->CPM_WP_RW."</td></tr>
			<tr><td>14.</td><td>RT</td>
				<td>".$data->CPM_WP_RT."</td></tr>
			<tr><td>15.</td><td>Kab/kodya</td>
				<td>".$data->CPM_WP_KOTAKAB."</td></tr>
			<tr><td>16</td><td>Kecamatan</td>
				<td>".$data->CPM_WP_KECAMATAN."</td></tr>
			<tr><td>17</td><td>Kode Pos</td>
				<td>".$data->CPM_WP_KODEPOS."</td></tr>
			<tr><td>18.</td><td>Nomor KTP</td>
				<td>".$data->CPM_WP_NO_KTP."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">C. Data Tanah</td></tr>
			<tr><td>19.</td><td>Luas Tanah</td>
				<td>".$data->CPM_OT_LUAS."</td></tr>
			<tr><td>20.</td><td>Zona Nilai Tanah</td>
				<td>".$data->CPM_OT_ZONA_NILAI."</td></tr>
			<tr><td>21.</td><td>Jenis Tanah</td>
				<td>".(($data->CPM_OT_JENIS==1)?"Tanah + Bangunan":(($data->OT_JENIS==2)?"Kavling siap bangun":(($data->OT_JENIS==3)?"Tanah kosong":(($data->OT_JENIS==4)?"Fasilitas umum":""))))."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">D. Data Bangunan</td></tr>
			<tr><td>22.</td><td>Jumlah Bangunan</td>
				<td>".$data->CPM_OP_JML_BANGUNAN."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">E. Penilaian Tanah</td></tr>
			<tr><td>23</td><td>Nilai Tanah</td>
				<td>Rp. ".number_format((($data->CPM_OT_PENILAIAN_TANAH == "sistem") ? $data->CPM_OT_PAYMENT_SISTEM : $data->CPM_OT_PAYMENT_INDIVIDU), 0, ",", ".")."</td></tr>
			<tr><td>24</td><td>Sistem Penilaian</td>
				<td>".(($data->CPM_OT_PENILAIAN_TANAH == "sistem") ? "Penilaian Sistem" : "Penilaian Individu")."</td></tr>			
		
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">F. Pernyataan Subjek Pajak</td></tr>
			<tr><td colspan=\"3\">Saya menyatakan bahwa informasi yang telah saya berikan dalam formulir ini termasuk lampirannya adalah benar, jelas dan lengkap menurut keadaan yang sebenarnya, sesuai dengan Pasal 9 ayat (2) Undang-undang No.12 Tahun 1985</td></tr>
			<tr><td colspan=\"3\" align=\"center\">
				".$data->CPM_PP_DATE."<br>
				<br>
				<br>
				<br>				
				<u>".$data->CPM_PP_NAMA."</u><br>
				".$data->CPM_PP_TIPE."
			</td></tr>
			<tr><td colspan=\"3\"><br><ul>
				<li>Dalam hal bertindak selaku kuasa, Surat Kuasa harap dilampirkan</li>
				<li>Dalam hal Subjek Pajak mendaftarkan sendiri Objek Pajak, supaya menggambarkan Sket/ Denah Lokasi Objek Pajak</li>
				<li>Batas waktu pengembalian SPOP 30 (tiga puluh) hari sejak diterima oleh Subjek Pajak sesuai Pasal 9 ayat (2) UU No. 12 Tahun 1985</li>
				</ul></td></tr>
			
			<tr><td colspan=\"3\" class=\"pdfrow\">G. Identitas Pendata / Pejabat Yang Berwenang</td></tr>
			<tr><td colspan=\"3\">
				<table width=\"100%\">
					<tr><td align=\"center\">Petugas Pendata</td><td align=\"center\">Mengetahui Pejabat Yang Berwenang</td></tr>
					<tr><td align=\"center\">".$data->CPM_OPR_TGL_PENDATAAN."</td><td align=\"center\">".$data->CPM_PJB_TGL_PENELITIAN."</td></tr>
					<tr><td colspan=\"2\"><br><br><br></td></tr>
					<tr><td align=\"center\"><u>".$data->CPM_OPR_NAMA."</u></td><td align=\"center\"><u>".$data->CPM_PJB_NAMA."</u></td></tr>
					<tr><td align=\"center\">".$data->CPM_OPR_NIP."</td><td align=\"center\">".$data->CPM_PJB_NIP."</td></tr>
				</table>
			</td></tr>
		</table>
	";
	// echo $HTML;
	// $HTML = "<h1>Hello World</h1>";
	return $HTML;
}

function getMapHTML($data) {
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	
	$HTML = "
		<link rel=\"stylesheet\" href=\"../../function/PBB/viewspop.css\" type=\"text/css\">
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
			<tr><td colspan=\"3\" align=\"center\" class=\"pdfrow\"><h4>SKET/DENAH LOKASI OBJEK PAJAK</h4></td></tr>
		</table>
	";
	return $HTML;
}

function getLampHTML($data, $nop, $jml_bangunan) {
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	
	$JPB = array(
			"-", 
			"Perumahan",
			"Perkantoran Swasta",
			"Pabrik",
			"Toko/Apotik/Pasar/Ruko",
			"Rumah Sakit/Klinik",
			"Olah Raga/Rekreasi",
			"Hotel/Wisma",
			"Bengkel/Gudang/Pertanian",
			"Gedung Pemerintah",
			"Lain-lain",
			"Bangunan Tidak Kena Pajak",
			"Bangunan Parkir",
			"Apartemen",
			"Pompa Bensin",
			"Tangki Minyak",
			"Gedung Sekolah");
			
	$HTML = "
		<link rel=\"stylesheet\" href=\"../../function/PBB/viewspop.css\" type=\"text/css\">
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"pdftable\">
			<tr><td colspan=\"3\" align=\"center\"><br><h4>LAMPIRAN SURAT PEMBERITAHUAN OBJEK PAJAK</h4></td></tr>
			<tr><td colspan=\"3\" align=\"center\" class=\"bottom-line\"></td></tr>
			<tr><td width=\"5%\">1.</td><td width=\"20%\">NOP</td>
					<td width=\"75%\">".$nop."</td></tr>
			<tr><td>2.</td><td>Jumlah Bangunan</td>
				<td>".$jml_bangunan."</td></tr>
			<tr><td>3.</td><td>Bangunan Ke</td>
				<td>".$data->CPM_OP_NUM."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">A. Rincian Data Bangunan</td></tr>
			<tr><td>4.</td><td>Jenis penggunaan bangunan</td>
				<td>".$JPB[$data->cpm_op_penggunaan]."</td></tr>
			<tr><td>5.</td><td>Luas bangunan</td>
				<td>".$data->CPM_OP_LUAS_BANGUNAN." m&sup2;</td></tr>
			<tr><td>6.</td><td>Jumlah lantai</td>
				<td>".$data->CPM_OP_JML_LANTAI."</td></tr>
			<tr><td>7.</td><td>Tahun dibangun</td>
				<td>".$data->CPM_OP_THN_DIBANGUN."</td></tr>
			<tr><td>8.</td><td>Tahun direnovasi</td>
				<td>".$data->CPM_OP_THN_RENOVASI."</td></tr>
			<tr><td>9.</td><td>Daya listrik terpasang</td>
				<td>".$data->CPM_OP_DAYA."</td></tr>
			<tr><td>10.</td><td>Kondisi pada umumnya</td>
				<td>".$data->CPM_OP_KONDISI."</td></tr>
			<tr><td>11.</td><td>Konstruksi</td>
				<td>".$data->CPM_OP_KONSTRUKSI."</td></tr>
			<tr><td>12.</td><td>Atap</td>
				<td>".$data->CPM_OP_ATAP."</td></tr>
			<tr><td>13.</td><td>Dinding</td>
				<td>".$data->CPM_OP_DINDING."</td></tr>
			<tr><td>14.</td><td>Lantai</td>
				<td>".$data->CPM_OP_LANTAI."</td></tr>
			<tr><td>15.</td><td>Langit-langit</td>
				<td>".$data->CPM_OP_LANGIT."</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">B. Fasilitas</td></tr>
			<tr><td class=\"bottom-line\">16.</td><td class=\"bottom-line\">Jumlah AC</td>
				<td class=\"bottom-line\"><span class=\"spacer\">Split: ".$data->CPM_FOP_AC_SPLIT."</span>
					<span class=\"spacer\">Window: ".$data->CPM_FOP_AC_WINDOW."</span></td></tr>
			<tr><td class=\"bottom-line\">17.</td><td class=\"bottom-line\">AC sentral</td>
				<td class=\"bottom-line\">".$data->CPM_FOP_AC_CENTRAL."</td></tr>
			<tr><td class=\"bottom-line\">18.</td><td class=\"bottom-line\">Luas kolam renang</td>
				<td class=\"bottom-line\">".$data->CPM_FOP_KOLAM_LUAS." m&sup2; ".$data->CPM_FOP_KOLAM_LAPISAN."</td></tr>
			<tr><td>19.</td><td>Luas Perkerasan Halaman</td>
				<td><span class=\"spacer\">Ringan: ".$data->CPM_FOP_PERKERASAN_RINGAN." m&sup2;</span>
					<span class=\"spacer\">Berat: ".$data->CPM_FOP_PERKERASAN_BERAT." m&sup2;</span>
				</td></tr>
			<tr><td class=\"bottom-line\"></td><td class=\"bottom-line\"></td>
				<td class=\"bottom-line\"><span class=\"spacer\">Sedang: ".$data->CPM_FOP_PERKERASAN_SEDANG." m&sup2;</span>
					<span class=\"spacer\">Dengan penutup lantai: ".$data->CPM_FOP_PERKERASAN_PENUTUP." m&sup2;</span>
				</td></tr>
			<tr><td>20.</td><td>Jumlah lapangan tenis</td>
				<td><span class=\"spacer\">&nbsp;</span>
					<span class=\"spacer\">Dgn Lampu</span>
					<span class=\"spacer\">Tanpa Lampu</span>
				</td></tr>
			<tr><td></td><td></td>
				<td><span class=\"spacer\">Beton</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_LAMPU_BETON."</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_TANPA_LAMPU_BETON."</span>
				</td></tr>
			<tr><td></td><td></td>
				<td><span class=\"spacer\">Aspal</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_LAMPU_ASPAL."</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_TANPA_LAMPU_ASPAL."</span>
				</td></tr>
			<tr><td class=\"bottom-line\"></td><td class=\"bottom-line\"></td>
				<td class=\"bottom-line\"><span class=\"spacer\">Tanah liat/Rumput</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_LAMPU_TANAH."</span>
					<span class=\"spacer\">".$data->CPM_FOP_TENIS_TANPA_LAMPU_TANAH."</span>
				</td></tr>
			<tr><td class=\"bottom-line\">21.</td><td class=\"bottom-line\">Jumlah lift</td>
				<td class=\"bottom-line\">Penumpang:".$data->CPM_FOP_LIFT_PENUMPANG.", Kapsul:".$data->CPM_FOP_LIFT_KAPSUL.", Barang:".$data->CPM_FOP_LIFT_BARANG."</td></tr>
			<tr><td class=\"bottom-line\">22.</td><td class=\"bottom-line\">Jumlah tangga berjalan</td>
				<td class=\"bottom-line\"><span class=\"spacer\">Lebar &lt; 0,8m: ".$data->CPM_FOP_ESKALATOR_SEMPIT."</span><span class=\"spacer\">Lebar &gt; 0,8m: ".$data->CPM_FOP_ESKALATOR_LEBAR."</span></td></tr>
			<tr><td class=\"bottom-line\">23.</td><td class=\"bottom-line\">Panjang pagar</td>
				<td class=\"bottom-line\"><span class=\"spacer\">".$data->CPM_FOP_PAGAR." m</span><span class=\"spacer\">Bahan Pagar: ".$data->CPM_FOP_PAGAR_BAHAN."</span></td></tr>			
			<tr><td class=\"bottom-line\">24.</td><td class=\"bottom-line\">Pemadam kebakaran</td>
				<td class=\"bottom-line\">".$data->CPM_FOP_PEMADAM."</td></tr>
			<tr><td class=\"bottom-line\">25.</td><td class=\"bottom-line\">Jml saluran pes. PABX</td>
				<td class=\"bottom-line\">".$data->CPM_FOP_SALURAN."</td></tr>
			<tr><td class=\"bottom-line\">26.</td><td class=\"bottom-line\">Kedalaman sumur artesis</td>
				<td class=\"bottom-line\">".$data->CPM_FOP_SUMUR." m</td></tr>
			
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr><td colspan=\"3\" class=\"pdfrow\">C. Data Tambahan Untuk Bangunan</td></tr>
			
		";
	if ($data->cpm_op_penggunaan==3 || $data->cpm_op_penggunaan==8) {
		$HTML .= "
			<tr><td colspan=\"3\">Bangunan Pabrik/Bengkel/Gudang/Pertanian</td></tr>
			<tr><td>27.</td><td>Tinggi kolom</td>
				<td>".$data->CPM_PABRIK_TINGGI."</td></tr>
			<tr><td>28.</td><td>Lebar bentang</td>
				<td>".$data->CPM_PABRIK_LEBAR."</td></tr>
			<tr><td>29.</td><td>Daya dukung lantai</td>
				<td>".$data->CPM_PABRIK_DAYA."</td></tr>
			<tr><td>30.</td><td>Keliling dinding</td>
				<td>".$data->CPM_PABRIK_KELILING."</td></tr>
			<tr><td>31.</td><td>Luas Mezzanine</td>
				<td>".$data->CPM_PABRIK_LUAS."</td></tr>	
		";
	}	
	if ($data->cpm_op_penggunaan == 2 || $data->cpm_op_penggunaan == 9) {
		$HTML .= "
			<tr><td colspan=\"3\">Bangunan Perkantoran swasta/Gedung pemerintah</td></tr>
			<tr><td>27.</td><td>Kelas bangunan</td>
				<td>".$data->CPM_OP_KELAS."</td></tr>	
		";
	}
	if ($data->cpm_op_penggunaan == 4) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Toko/Apotik/Pasar/Ruko</td></tr>
		<tr><td>27.</td><td>Kelas bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>	
		";
	}
	if ($data->cpm_op_penggunaan == 5) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Rumah sakit/Klinik</td></tr>
		<tr><td>27.</td><td>Kelas bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		<tr><td>28.</td><td>Luas kmr dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_KMR." m&sup2;</td></tr>
		<tr><td>29.</td><td>Luar rg lain dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_LAIN." m&sup2;</td></tr>		
		";
	}
	if ($data->cpm_op_penggunaan == 6) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Olahraga/Rekreasi</td></tr>
		<tr><td>27.</td><td>Kelas bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		";
	}
	if ($data->cpm_op_penggunaan == 7) {
		$HTML .= "
		<tr><td colspan=3>Bangunan Hotel/Wisma</td></tr>
		<tr><td>27.</td><td>Jenis hotel</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		<tr><td>28.</td><td>Jumlah bintang</td>
			<td>".$data->CPM_OP_HOTEL_BINTANG."</td></tr>
		<tr><td>29.</td><td>Jumlah kamar</td>
			<td>".$data->CPM_OP_JML_KMR."</td></tr>
		<tr><td>30.</td><td>Luas kmr dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_KMR." m&sup2;</td></tr>
		<tr><td>31.</td><td>Luar rg lain dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_LAIN." m&sup2;</td></tr>		
		";
	}
	if ($data->cpm_op_penggunaan == 12) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Parkir</td></tr>
		<tr><td>27.</td><td>Tipe bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		";
	}
	if ($data->cpm_op_penggunaan == 13) {
		$HTML .= "
		<tr><td colspan=3>Bangunan Apartemen</td></tr>
		<tr><td>27.</td><td>Kelas bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		<tr><td>28.</td><td>Jumlah Apartemen</td>
			<td>".$data->CPM_OP_JML_KMR."</td></tr>
		<tr><td>29.</td><td>Luas apt dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_KMR." m&sup2;</td></tr>
		<tr><td>30.</td><td>Luar rg lain dg AC sentral</td>
			<td>".$data->CPM_OP_LUAS_LAIN." m&sup2;</td></tr>
		";
	}
	if ($data->cpm_op_penggunaan == 15) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Tangki Minyak</td></tr>
		<tr><td>27.</td><td>Kapasitas tangki</td>
			<td>".$data->CPM_OP_TANGKI_KAPASITAS."</td></tr>
		<tr><td>28.</td><td>Letak tangki</td>
			<td>".($data->CPM_OP_TANGKI_LETAK==1)?"Di atas tanah":($data->CPM_OP_TANGKI_LETAK==2)?"Di bawah tanah":"-"."</td></tr>
		";
	}
	if ($data->cpm_op_penggunaan == 16) {
		$HTML .= "
		<tr><td colspan=\"3\">Bangunan Gedung Sekolah</td></tr>
		<tr><td>27.</td><td>Kelas bangunan</td>
			<td>".$data->CPM_OP_KELAS."</td></tr>
		";
	}
	
	$HTML .= "
		<tr><td colspan=\"3\">&nbsp;</td></tr>
		<tr><td colspan=\"3\" class=\"pdfrow\">D. Penilaian Bangunan</td></tr>
		<tr><td></td><td>Nilai Bangunan</td>
			<td>Rp. ".number_format((($data->CPM_PAYMENT_PENILAIAN_BGN == "sistem") ? $data->CPM_PAYMENT_SISTEM : $data->CPM_PAYMENT_INDIVIDU), 0, ",", ".")."</td></tr>
		<tr><td></td><td>Sistem Penilaian</td>
			<td>".(($data->CPM_PAYMENT_PENILAIAN_BGN == "sistem") ? "Penilaian Sistem" : "Penilaian Individu")."</td></tr>			
			
		</table>
	";
	
	return $HTML;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$idd = $q->id;
$v = $q->v;

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
// $pdf->SetCreator(PDF_CREATOR);
// $pdf->SetAuthor('Nicola Asuni');
// $pdf->SetTitle('TCPDF Example 002');
// $pdf->SetSubject('TCPDF Tutorial');
// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
// $pdf->SetFont('helvetica', 'B', 20);

// add a page
//$pdf->AddPage();

//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetProtection($permissions=array('modify'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null);

$DATA = getData($idd, $v);
// echo "<pre>";
// print_r($DATA);
// echo "</pre>";

//write for the first page
$HTML = "";
$pdf->AddPage();
$HTML = getDataHTML($DATA->data);
$pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($HTML, true, false, false, false, '');
// echo $HTML;

//write for the map page
$pdf->AddPage();
$HTML = getMapHTML($DATA->data);
if ($DATA->data->CPM_OP_SKET!="") 
	$pdf->Image($sRootPath.$DATA->data->CPM_OP_SKET, 20, 35, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true);
$pdf->writeHTML($HTML, true, false, false, false, '');
// echo $HTML;

//write for the extension page
if (isset($DATA->lamp))
foreach ($DATA->lamp as $LAMP) {
	$pdf->AddPage();
	$HTML = getLampHTML($LAMP, $DATA->data->CPM_NOP, $DATA->data->CPM_OP_JML_BANGUNAN);
	$pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	$pdf->writeHTML($HTML, true, false, false, false, '');
}
// echo $HTML;


// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($DATA->data->CPM_NOP.'.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+
?>