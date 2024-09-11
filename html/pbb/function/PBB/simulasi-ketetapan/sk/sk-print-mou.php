<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'simulasi-ketetapan' . DIRECTORY_SEPARATOR . 'sk', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$dataNotaris = "";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getAuthor($uname)
{
	global $DBLink, $appID;
	$id = $appID;
	$qry = "select nm_lengkap from TBL_REG_USER_NOTARIS where userId = '" . $uname . "'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo mysqli_error($DBLink);
	}

	$num_rows = mysqli_num_rows($res);
	if ($num_rows == 0) return $uname;
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['nm_lengkap'];
	}
}

function getConfigValue($appID, $key)
{
	global $DBLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $appID . "' and CTR_AC_KEY = '$key'";
	// echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function mysql2json($mysql_result, $name)
{
	$json = "{\n'$name': [\n";
	$field_names = array();
	$fields = mysqli_num_fields($mysql_result);
	for ($x = 0; $x < $fields; $x++) {
		$field_name = mysqli_fetch_field($mysql_result);
		if ($field_name) {
			$field_names[$x] = $field_name->name;
		}
	}
	$rows = mysqli_num_rows($mysql_result);
	for ($x = 0; $x < $rows; $x++) {
		$row = mysqli_fetch_array($mysql_result);
		$json .= "{\n";
		for ($y = 0; $y < count($field_names); $y++) {
			$json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
			if ($y == count($field_names) - 1) {
				$json .= "\n";
			} else {
				$json .= ",\n";
			}
		}
		if ($x == $rows - 1) {
			$json .= "\n}\n";
		} else {
			$json .= "\n},\n";
		}
	}
	$json .= "]\n}";
	return ($json);
}

function getKecamatanNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TKC_KECAMATAN'];
}

function getKelurahanNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TKL_KELURAHAN'];
}

function getKabkotaNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kabkota` WHERE CPC_TK_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TK_KABKOTA'];
}

function getLastSKNJOPNumber()
{
	global $DBLink, $appConfig;

	$qry = "SELECT MAX(CPM_SKNJOP_NO) AS SKNJOP_NUMBER FROM cppmod_pbb_generate_sknjop_number WHERE CPM_YEAR = '" . $appConfig['tahun_tagihan'] . "'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['SKNJOP_NUMBER'];
		}
		return "0";
	}
}

function generateSKNJOPNumber()
{
	global $appConfig;

	$lastNumber = getLastSKNJOPNumber();
	$newNumber = $lastNumber + 1;

	return '800/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT) . '/C/V.04/' . date('Y');
}

function insertSKNJOPNumber($id)
{
	global $DBLink, $uname, $appConfig;

	$num 	= explode('/', $id);
	$qry = "INSERT INTO cppmod_pbb_generate_sknjop_number (CPM_SKNJOP_ID,CPM_SKNJOP_NO,CPM_CREATOR,CPM_DATE_CREATED,CPM_YEAR) VALUES ('$id','$num[1]','$uname',now(),'" . $appConfig['tahun_tagihan'] . "')";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else
		return $res;
}

function getData($nop)
{
	global $DBLink, $dataNotaris;

	$query = "SELECT
                        A.*,IFNULL(B.cpm_op_penggunaan,0) as cpm_op_penggunaan
                FROM
                        cppmod_pbb_sppt_final A LEFT JOIN cppmod_pbb_sppt_ext_final B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
                WHERE
                        A.CPM_NOP = '$nop'

                UNION ALL
                SELECT
                        A.*,IFNULL(B.cpm_op_penggunaan,0) as cpm_op_penggunaan
                FROM
                        cppmod_pbb_sppt_susulan A LEFT JOIN cppmod_pbb_sppt_ext_susulan B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
                WHERE
                        A.CPM_NOP = '$nop' ";

	$res = mysqli_query($DBLink, $query);
	$record = mysqli_num_rows($res);
	if ($record < 1) {
		echo "Data tidak ada!";
		exit;
	}
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res, "data"));
	$dt = $dataNotaris->data[0];
	return $dt;
}

function headerSK()
{
	global $appID;
	return $strHeader = "
		<div align=\"center\"><br><br>" . getConfigValue($appID, 'C_HEADER_SK') . "</div>
	";
}

function getHTML()
{
	global $uname, $appID, $noSK;

	$kota 				= getConfigValue($appID, 'NAMA_KOTA');
	$kotaPengesahan		= ucfirst(strtolower(getConfigValue($appID, 'NAMA_KOTA_PENGESAHAN')));
	$pejabatSK 			= getConfigValue($appID, 'PEJABAT_SK2');
	$tembusan 			= getConfigValue($appID, 'PEJABAT_SK');
	$namaPejabatSK		= getConfigValue($appID, 'NAMA_PEJABAT_SK2');
	$jabatanPejabatSK	= getConfigValue($appID, 'PEJABAT_SK2');
	$NIPPejabatSK		= getConfigValue($appID, 'NAMA_PEJABAT_SK2_NIP');
	$kabkot				= getConfigValue($appID, 'C_KABKOT');
	//$WktSkr				= tgl_indo(date("d-m-Y"));
	/*$SKNumber = generateSKNJOPNumber();
	insertSKNJOPNumber($SKNumber);*/

	//$data		= getData($nop);

	$SKNumber = null;

	$jenisTanah = array(
		1 => "Tanah + Bangunan",
		2 => "Kavling siap bangun",
		3 => "Tanah kosong",
		4 => "Fasilitas umum"
	);
	$jenisBangunan = array(
		0 => "Tidak Ada Bangunan",
		1 => "Perumahan",
		2 => "Perkantoran Swasta",
		3 => "Pabrik",
		4 => "Toko/Apotik/Pasar/Ruko",
		5 => "Rumah Sakit/Klinik",
		6 => "Olah Raga/Rekreasi",
		7 => "Hotel/Wisma",
		8 => "Bengkel/Gudang/Pertanian",
		9 => "Gedung Pemerintah",
		10 => "Lain-lain",
		11 => "Bng Tidak Kena Pajak",
		12 => "Bangunan Parkir",
		13 => "Apartemen",
		14 => "Pompa Bensin",
		15 => "Tangki Minyak",
		16 => "Gedung Sekolah"
	);


	/*$html = "
	<html>
	<body>
	<table border=\"0\" width=\"650\" cellpadding=\"5\" cellspacing=\"5\">
		<tr>
			<td align=\"center\" colspan=\"3\"><br><br><br><br><hr style=\"height: 2px\"></td>
		</tr>
		<tr>
			<td colspan=\"3\" height=\"800\">
				<table border=\"0\" cellspacing=\"5\"> 
					<tr>
						<td align=\"justify\">
							Pada hari ini Rabu tanggal Satu Bulan April  Tahun Dua Ribu Dua Puluh, bertempat di Kecamatan Pringsewu Kabupaten Pringsewu, yang bertanda tangan dibawah ini :
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"1400\">
								<tr>
									<td width=\"25\"></td>
									<td width=\"150\" valign=\"top\">1.</td>
									<td width=\"10\"></td>
									<td align=\"left\">JULIA DARMA.,SE,Pejabat Pelaksana Teknis Kegiatan Penyampaian SPPT PBB-P2 Tahun 2020 Badan Pendapatan Daerah Kabupaten Pringsewu yang berkedudukan dan berkantor di Komplek Perkantoran Pemerintah Kabupaten, bertindak untuk dan atas nama Pemerintah Kabupaten Pringsewu yang selanjutnya disebut sebagai PIHAK KESATU.</td>
								</tr>
								<tr>
									<td></td><td>Jabatan</td><td>:</td><td align=\"left\">" . $jabatanPejabatSK . "</td>
								</tr>
							</table>
							<!-- <table border=\"1\" width=\"400\">
								<tr>
									<td width=\"150\">Nomor Objek Pajak</td><td width=\"10\">:</td><td width=\"400\">" . $data->CPM_NOP . "</td>
								</tr>
								<tr>
									<td>Jenis Objek Pajak</td><td>:</td><td>" . $jenisTanah[$data->CPM_OT_JENIS] . "</td>
								</tr>
								<tr>
									<td>Jenis Penggunaan</td><td>:</td><td>" . $jenisBangunan[$data->cpm_op_penggunaan] . "</td>
								</tr>
								<tr>
									<td>Alamat Objek Pajak</td><td>:</td><td>" . $data->CPM_OP_ALAMAT . "</td>
								</tr>
							</table> -->
						</td>
					</tr>
					<tr>
						<td align=\"justify\">
							Sesuai dengan ketentuan Pasal 77 ayat (1) Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah dan Pasal 2 ayat (2) Peraturan Daerah Kabupaten Lampung Selatan Nomor 3 Tahun 2011 tentang Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2), dengan ini menerangkan bahwa sesuai dengan basis data Badan Pengelola Pajak dan Retribusi Daerah Kabupaten Lampung Selatan, objek pajak sebagai berikut: 
						</td>
					</tr>
					<!--<tr>
						<td>
							<table border=\"0\" width=\"500\">
								<tr>
									<td width=\"25\"></td><td width=\"150\">Nomor Objek Pajak</td><td width=\"10\">:</td><td width=\"480\">" . $data->CPM_NOP . "</td>
								</tr>
								<tr>
									<td width=\"25\"></td><td>Alamat Objek Pajak</td><td>:</td><td>" . $data->CPM_OP_ALAMAT . " " . ($data->CPM_OP_RT != '' ? 'RT. ' . $data->CPM_OP_RT : '') . " " . ($data->CPM_OP_RW != '' ? 'RW. ' . $data->CPM_OP_RW : '') . " " . ($data->CPM_OP_KELURAHAN != '' ? 'KEL./DESA ' . getKelurahanNama($data->CPM_OP_KELURAHAN) : '') . " " . ($data->CPM_OP_KECAMATAN != '' ? 'KEC. ' . getKecamatanNama($data->CPM_OP_KECAMATAN) : '') . " " . ($data->CPM_OP_KOTAKAB != '' ? 'KAB. ' . getKabkotaNama($data->CPM_OP_KOTAKAB) : '') . "</td>
								</tr>
							</table>
						</td>
					</tr>-->
					<tr>
						<td align=\"justify\">
						Diperoleh data sebagai berikut:
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"550\">
								<tr>
									<td width=\"25\"></td>
									<td width=\"150\">Luas Bumi</td>
									<td width=\"10\">:</td>
									<!--<td align=\"left\" width=\"100\">" . $data->CPM_OP_LUAS_TANAH . " M2</td>-->
									<td width=\"30\"></td>
									<td width=\"120\"></td>
									<td width=\"35\"></td>
									<td width=\"120\"></td>
								</tr>
								<tr>
									<td></td>
									<td>Luas Bangunan</td>
									<td>:</td>
									<!--<td align=\"left\" >" . $data->CPM_OP_LUAS_BANGUNAN . " M2</td>-->
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>NJOP Bumi</td>
									<td>:</td>
									<!--<td align=\"left\">" . number_format($data->CPM_OP_LUAS_TANAH, 0, ',', '.') . " M2</td>
									<td> x Rp</td><td align=\"left\">" . (($data->CPM_OP_LUAS_TANAH != 0) ? number_format($data->CPM_NJOP_TANAH / $data->CPM_OP_LUAS_TANAH, 0, ',', '.') : '0') . "/M2</td>-->
									<td> = Rp</td>
									<!--<td align=\"left\">" . number_format($data->CPM_NJOP_TANAH, 0, ',', '.') . "</td>-->
								</tr>
								<tr>
									<td></td>
									<td>NJOP Bangunan</td>
									<!--<td>:</td><td align=\"left\">" . number_format($data->CPM_OP_LUAS_BANGUNAN, 0, ',', '.') . " M2</td>-->
									<td> x Rp</td>
									<!--<td align=\"left\">" . (($data->CPM_OP_LUAS_BANGUNAN != 0) ? number_format($data->CPM_NJOP_BANGUNAN / $data->CPM_OP_LUAS_BANGUNAN, 0, ',', '.') : '0') . "/M2</td>-->
									<td> = Rp</td>
									<!--<td align=\"left\">" . number_format($data->CPM_NJOP_BANGUNAN, 0, ',', '.') . "</td>-->
								</tr>
								<!-- <tr>
									<td></td><td></td><td></td><td align=\"right\"></td><td></td><td></td><td><hr></td>
								</tr> -->
								<tr>
									<!--<td></td><td colspan=\"3\">Nilai Jual Objek Pajak Keseluruhan</td><td></td><td></td><td> = Rp </td><td align=\"left\">" . number_format(($data->CPM_NJOP_TANAH + $data->CPM_NJOP_BANGUNAN), 0, ',', '.') . "</td>-->
								</tr>
								<tr>
									<!--<td align=\"right\" colspan=\"8\">(" . SayInIndonesian(($data->CPM_NJOP_TANAH + $data->CPM_NJOP_BANGUNAN)) . ")</td>-->
								</tr>
							</table>
							<br>
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"1400\">
								<tr>
									<!--<td width=\"25\"></td><td width=\"150\">Nama Wajib Pajak</td><td width=\"10\">:</td><td align=\"left\">" . $data->CPM_WP_NAMA . "</td>-->
								</tr>
								<tr>
									<!--<td width=\"25\"></td><td>Alamat Wajib Pajak</td><td>:</td><td>" . $data->CPM_WP_ALAMAT . " " . ($data->CPM_WP_RT != '' ? 'RT. ' . $data->CPM_WP_RT : '') . " " . ($data->CPM_WP_RW != '' ? 'RW. ' . $data->CPM_WP_RW : '') . " " . ($data->CPM_WP_KELURAHAN != '' ? 'KEL./DESA ' . $data->CPM_WP_KELURAHAN : '') . " " . ($data->CPM_WP_KECAMATAN != '' ? 'KEC. ' . $data->CPM_WP_KECAMATAN : '') . " " . ($data->CPM_WP_KOTAKAB != '' ? 'KAB. ' . $data->CPM_WP_KOTAKAB : '') . "</td>-->
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=\"justify\">
							Demikian Surat Keterangan NJOP ini dibuat untuk dapat dipergunakan sebagaimana perlu. Apabila di kemudian hari terdapat kekeliruan akan diperbaiki dan ditindak lanjuti sesuai dengan ketentuan yang berlaku.
						</td>
					</tr>
					<tr>
						<td>
							<br><br><br>
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"left\">
												Dibuat di Kalianda<br>
												Pada tanggal, " . TanggalIndo(date("Y-m-d")) . "
									</td>
                        </tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"CENTER\">
										KEPALA BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH &nbsp;&nbsp;&nbsp;KABUPATEN PESAWARAN<br>
										<br>
										<br>
										<br>
										<br>
										<u>" . $namaPejabatSK . "</u><br>
										NIP. " . $NIPPejabatSK . "<br>
									</td>
                        </tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=\"left\">
							<br>
							<br>
							<br>
							Lembar 1 untuk Wajib Pajak <br>
							Lembar 2 untuk BP2RD
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</body>
	</html>
	";*/
	$html = "
	<html>
	<body>
	<table border=\"0\" width=\"650\" cellpadding=\"5\" cellspacing=\"5\">
		<tr>
			<td align=\"center\" colspan=\"3\"><br><br><br><br><hr style=\"height: 2px\"></td>
		</tr>
		<tr>
			<td colspan=\"3\" height=\"800\">
				<table border=\"0\" cellspacing=\"5\"> 
					<tr>
						<td align=\"justify\">
							Pada hari ini Rabu tanggal Satu Bulan April  Tahun Dua Ribu Dua Puluh, bertempat di Kecamatan Pringsewu Kabupaten Pringsewu, yang bertanda tangan dibawah ini :
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"1400\">
								<tr>
									<td width=\"25\"></td>
									<td width=\"150\" valign=\"top\">1.</td>
									<td width=\"10\"></td>
									<td align=\"left\">JULIA DARMA.,SE,Pejabat Pelaksana Teknis Kegiatan Penyampaian SPPT PBB-P2 Tahun 2020 Badan Pendapatan Daerah Kabupaten Pringsewu yang berkedudukan dan berkantor di Komplek Perkantoran Pemerintah Kabupaten, bertindak untuk dan atas nama Pemerintah Kabupaten Pringsewu yang selanjutnya disebut sebagai PIHAK KESATU.</td>
								</tr>
								<tr>
									<td width=\"25\"></td>
									<td width=\"150\" valign=\"top\">2.</td>
									<td width=\"10\"></td>
									<td align=\"left\">DIDI MARYADHI., SIP , Pekon Podomoro Kecamatan Pringsewu yang berkedudukan dan berkantor di Pekon Podomoro Kecamatan Pringsewu , bertindak untuk dan atas nama Pekon Podomoro yang selanjutnya disebut PIHAK KEDUA.</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=\"justify\">
							Berdasarkan pada: (1) Undang-Undang Republik Indonesia Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah, (2) Peraturan Daerah Kabupaten Pringsewu Nomor 14 Tahun 2013 tentang Perubahan atas Peraturan Daerah Nomor 03 Tahun 2011 tentang Pajak Daerah, PIHAK KESATU dan PIHAK KEDUA sepakat dan setuju untuk mengikatkan diri dalam perjanjian penunjukan PIHAK KEDUA sebagai PETUGAS PENDATAAN/PENYAMPAIAN SPPT PBB-P2 dalam pasal-pasal sebagai berikut :
						</td>
					</tr>
					<tr>
						<td align=\"justify\">
							Demikian Surat Keterangan NJOP ini dibuat untuk dapat dipergunakan sebagaimana perlu. Apabila di kemudian hari terdapat kekeliruan akan diperbaiki dan ditindak lanjuti sesuai dengan ketentuan yang berlaku.
						</td>
					</tr>
					<tr>
						<td>
							<br><br><br>
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"left\">
												Dibuat di Kalianda<br>
												Pada tanggal, " . TanggalIndo(date("Y-m-d")) . "
									</td>
                        </tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"CENTER\">
										KEPALA BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH &nbsp;&nbsp;&nbsp;KABUPATEN PESAWARAN<br>
										<br>
										<br>
										<br>
										<br>
										<u>" . $namaPejabatSK . "</u><br>
										NIP. " . $NIPPejabatSK . "<br>
									</td>
                        </tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=\"left\">
							<br>
							<br>
							<br>
							Lembar 1 untuk Wajib Pajak <br>
							Lembar 2 untuk BP2RD
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</body>
	</html>
	";
	return $html;
}


function TanggalIndo($date)
{
	$BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

	$tahun = substr($date, 0, 4);
	$bulan = substr($date, 5, 2);
	$tgl   = substr($date, 8, 2);

	$result = $tgl . " " . $BulanIndo[(int)$bulan - 1] . " " . $tahun;
	return ($result);
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "test";
$q = base64_decode($q);
$q = $json->decode($q);
// print_r($q);

//$nop 	= isset($q->nop) ? $q->nop : '';
$appID  = $q->appID;
$uname 	= $q->uname;
$fileLogo =  getConfigValue($appID, 'LOGO_CETAK_SK');

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($appID);

class MYPDF extends TCPDF
{
	public function Header()
	{
		//$headerData = $this->getHeaderData();
		/*$headerData = '
			<br>
			<div align="center">
				<font size="12">PERJANJIAN KERJASAMA</font>
			</div>
			<div align="center">
				<font size="12">ANTARA</font>
			</div>
			<div align="center">
				<font size="12">PEJABAT PELAKSANA TEKNIS KEGIATAN</font><br>
				<font size="12">PENYAMPAIAN SPPT PBB-P2 TAHUN 2020</font><br>
				<font size="12">BADAN PENDAPATAN DAERAH</font><br>
				<font size="12">KABUPATEN PRINGSEWU</font>
			</div>
			<div align="center">
				<font size="12">DENGAN</font>
			</div>
			<div align="center">
				<font size="12">KEPALA PEKON PODOMORO</font>
			</div>
			<div align="center">
				<font size="12">TENTANG</font>
			</div>
			<div align="center">
				<font size="12">PENYAMPAIAN SPPT PBB-P2 TAHUN 2020</font><br />
				<font size="12">KABUPATEN PRINGSEWU</font>
			</div>
			<div align="center">
				<font size="12">NOMOR : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/B.03/2020</font>
			</div>
		';
		$this->SetFont('bookmanoldstyle', 'b', 10);
		$this->writeHTML($headerData);*/
		//$image_file = K_PATH_IMAGES . 'Logo_doc2.jpg';
		//$this->Image($image_file, 12, 10, 18, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setHeaderData($ln = '', $lw = 0, $ht = '', $hs = '', $tc = array(0, 0, 0), $lc = array(0, 0, 0));
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('SK NJOP');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');
// $pdf->setPrintHeader(false);
// $pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(12, 14, 12);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('bookmanoldstyle', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('P', 'F4');

//$ids 			= explode(",", $nop);
$idx 			= 0;
$strHTML 		= "";
$strHTMLSingle 	= "";
$sumbuYLogo		= 800;
$strHTML 		= '
	<div align="center">
		<font size="12">PERJANJIAN KERJASAMA</font>
	</div>
	<div align="center">
		<font size="12">ANTARA</font>
	</div>
	<div align="center">
		<font size="12">PEJABAT PELAKSANA TEKNIS KEGIATAN</font><br>
		<font size="12">PENYAMPAIAN SPPT PBB-P2 TAHUN 2020</font><br>
		<font size="12">BADAN PENDAPATAN DAERAH</font><br>
		<font size="12">KABUPATEN PRINGSEWU</font>
	</div>
	<div align="center">
		<font size="12">DENGAN</font>
	</div>
	<div align="center">
		<font size="12">KEPALA PEKON PODOMORO</font>
	</div>
	<div align="center">
		<font size="12">TENTANG</font>
	</div>
	<div align="center">
		<font size="12">PENYAMPAIAN SPPT PBB-P2 TAHUN 2020</font><br />
		<font size="12">KABUPATEN PRINGSEWU</font>
	</div>
	<div align="center">
		<font size="12">NOMOR : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/B.03/2020</font>
	</div>
	<div>
		<font size="10">Pada hari ini Rabu tanggal Satu Bulan April  Tahun Dua Ribu Dua Puluh, bertempat di Kecamatan Pringsewu Kabupaten Pringsewu, yang bertanda tangan dibawah ini :</font>
		<font size="10">
			<ol>
				<li>JULIA DARMA.,SE,Pejabat Pelaksana Teknis Kegiatan Penyampaian SPPT PBB-P2 Tahun 2020 Badan Pendapatan Daerah Kabupaten Pringsewu yang berkedudukan dan berkantor di Komplek Perkantoran Pemerintah Kabupaten, bertindak untuk dan atas nama Pemerintah Kabupaten Pringsewu yang selanjutnya disebut sebagai PIHAK KESATU.</li><br>
				<li>DIDI MARYADHI., SIP , Pekon Podomoro Kecamatan Pringsewu yang berkedudukan dan berkantor di Pekon Podomoro Kecamatan Pringsewu , bertindak untuk dan atas nama Pekon Podomoro yang selanjutnya disebut PIHAK KEDUA.</li>
			</ol>
		</font>
		<font size="10">Berdasarkan pada: (1) Undang-Undang Republik Indonesia Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah, (2) Peraturan Daerah Kabupaten Pringsewu Nomor 14 Tahun 2013 tentang Perubahan atas Peraturan Daerah Nomor 03 Tahun 2011 tentang Pajak Daerah, PIHAK KESATU dan PIHAK KEDUA sepakat dan setuju untuk mengikatkan diri dalam perjanjian penunjukan PIHAK KEDUA sebagai PETUGAS PENDATAAN/PENYAMPAIAN SPPT PBB-P2 dalam pasal-pasal sebagai berikut :</font>
	</div>
	<div align="center">
		<font size="12">PASAL 1</font>
		<font size="12">RUANG LINGKUP</font>
	</div>
	<div>
		<font size="10">
			<table width="100%">
				<tr>
					<td width="5%">(1)</td>
					<td width="95%">PIHAK KESATU menunjuk PIHAK KEDUA dan PIHAK KEDUA menerima penunjukan dari PIHAK KESATU sebagai Petugas Pendataan/Penyampaian SPPT PBB-P2 Tahun 2020.</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(2)</td>
					<td>Atas penunjukan sebagaimana dimaksud ayat (1) PIHAK KESATU menyerahkan SPPT PBB-P2 kepada PIHAK KEDUA.</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(3)</td>
					<td>Kewajiban PIHAK KESATU kepada PIHAK KEDUA :</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<table>
							<tr>
								<td width="5%">a.</td>
								<td width="95%">Menyerahkan SPPT PBB-P2 sesuai dengan jumlah objek pajak dan besaran ketetapan PBB-P2</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>Melakukan sosialisasi dan bimbingan teknis terkait penyampaian SPPT PBB-P2</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>Memberikan uang jasa penyampaian SPPT PBB-P2 sesuai dengan jumlah SPPT PBB-P2 yang diterima oleh Wajib Pajak sebesar Rp.1.500,- per SPPT PBB-P2</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(4)</td>
					<td>Kewajiban PIHAK KEDUA kepada PIHAK KESATU dalam pengelolaan Pajak Bumi dan Bangunan meliputi :</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<table>
							<tr>
								<td width="5%">a.</td>
								<td width="95%">Menerima SPPT PBB-P2 yang diserahkan oleh PIHAK KESATU untuk disampaikan kepada Wajib Pajak yang berada di Wilayah PIHAK KEDUA</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>Membuat laporan mengenai jumlah SPPT PBB-P2 yang telah diterima Wajib Pajak</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>Membuat laporan terperinci mengenai jumlah SPPT PBB-P2 yang kembali dikarenakan SPPT ganda, Objek Pajak tidak ada dan Wajib Pajak tidak ditemukan</td>
							</tr>
							<tr>
								<td>d.</td>
								<td>Melaporkan jika didapat masalah dalam penyampaian SPPT PBB-P2</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</font>
	</div>
	<br><br><br><br>
	<div align="center">
		<font size="12">PASAL 2</font>
		<font size="12">KERAHASIAAN</font>
	</div>
	<div>
		<font size="10">PIHAK KEDUA wajib merahasiakan segala sesuatu yang berhubungan dengan keterangan mengenai data dan informasi PIHAK KESATU, kecuali telah mendapatkan izin tertulis dari PIHAK KESATU.</font>
		<font size="10">PIHAK KESATU wajib merahasiakan segala sesuatu yang berhubungan dengan keterangan mengenai data dan informasi PIHAK KEDUA, kecuali telah mendapatkan izin tertulis PIHAK KEDUA.</font>
	</div>
	<div align="center">
		<font size="12">PASAL 3</font>
		<font size="12">JANGKA WAKTU PERJANJIAN</font>
	</div>
	<div>
		<font size="10">Jangka waktu perjanjian ini disepakati kedua belah pihak sepanjang pelaksanaan kegiatan.</font>
	</div>
	<div align="center">
		<font size="12">PASAL 4</font>
		<font size="12">FORCE MAJEURE (KEADAAN MEMAKSA)</font>
	</div>
	<div>
		<font size="10">
			<table width="100%">
				<tr>
					<td width="5%">(1)</td>
					<td width="95%">Tidak dilaksanakannya atau tertundanya pelaksanaan sebagian atau keseluruhan ketentuan perjanjian ini oleh salah satu pihak atau para pihak  tidak termasuk sebagai pelanggaran atas perjanjian apabila hal tersebut disebabkan oleh adanya force majeure (keadaan memaksa)</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(2)</td>
					<td>Yang termasuk sebagai force majeure adalah kejadian-kejadian yang dengan segala daya upaya tidak dapat diduga dan tidak dapat diatasi oleh pihak yang mengalami dan yang secara langsung berpengaruh pada pelaksanaan ketentuan perjanjian ini, yakni peristiwa-peristiwa termasuk namun tidak terbatas pada:</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<table>
							<tr>
								<td width="5%">a.</td>
								<td width="95%">bencana alam/wabah penyakit</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>pemberontakan/huru hara/perang</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>kebakaran</td>
							</tr>
							<tr>
								<td>d.</td>
								<td>sabotase</td>
							</tr>
							<tr>
								<td>e.</td>
								<td>pemogokan umum</td>
							</tr>
							<tr>
								<td>f.</td>
								<td>kebijakan pemerintah atau instansi yang berwenang yang menghalangi secara langsung atau tidak langsung untuk terlaksananya perjanjian ini</td>
							</tr>
							<tr>
								<td>g.</td>
								<td>gangguan jaringan on line/satellite</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(3)</td>
					<td>Pihak yang tidak dapat memenuhi kewajibannya sehubungan dengan force majeure tersebut harus memberitahukan secara tertulis kepada pihak lainnya selambat-lambatnya dalam waktu 7 (tujuh) hari kerja sejak mulainya kejadian tersebut.</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(4)</td>
					<td>Kelalaian atau kelambatan pihak yang terkena force majeure dalam memberitahukan sebagaimana dimaksud pasal ini dapat mengakibatkan tidak diakuinya peristiwa dimaksud sebagai force majeure.</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td>(5)</td>
					<td>Semua kerugian dan biaya yang diderita oleh masing-masing pihak sebagai akibat force majeure menjadi tanggung jawab masing-masing pihak.</td>
				</tr>
			</table>
		</font>
	</div>
	<div align="center">
		<font size="12">PASAL 5</font>
		<font size="12">ADDENDUM</font>
	</div>
	<div>
		<font size="10">Hal-hal lainyang belum diatur dalam perjanjian ini, baik perubahan maupun penambahan akan diselesaikan melalui perundingan antara PIHAK KESATU dan PIHAK KEDUA yang dituangkan dalam bentuk addendum yang ditandatangani oleh kedua belah pihak, dan menjadi bagian yang tidak dipisahkan dari dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini.</font>
	</div>
	<div align="center">
		<font size="12">PASAL 6</font>
		<font size="12">B</font>
		<font size="12">PENYELESAIAN PERSELISIHAN</font>
	</div>
	<div>
		<table width="100%">
			<tr>
				<td width="5%">(1)</td>
				<td width="95%">Setiap perselisihan yang timbul sebagai akibat dari perjanjian ini, kedua belah pihak sepakat untuk menyelesaikan secara musyawarah dan mufakat.</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>(2)</td>
				<td>Apabila penyelesaian secara musyawarah dan mufakat tersebut tidak tercapai, kedua belah pihak sepakat untuk menyelesaikan perselisihan dengan ketentuan yang berlaku dan kedua belah pihak memilih tempat kedudukan (domisili) hukum di kepaniteraan Pengadilan Negeri Tanggamus yang berkedudukan di Kota Agung Tanggamus.</td>
			</tr>
		</table>
	</div>
	<div align="center">
		<font size="12">PASAL 7</font>
		<font size="12">PENUTUP</font>
	</div>
	<div>
		<font size="10">Perjanjian ini ditandatangani oleh kedua belah pihak, dibuat rangkap 2 (dua) masing-masing bermaterai cukup dan memiliki kekuatan hukum yang sama.</font>
	</div>
	<table border=\"0\" width=\"100%\" cellpadding=\"5\" cellspacing=\"5\">
		<tr>
			<td align="center">
				PIHAK KESATU<br>
				PPTK PENYAMPAIAN SPPT PBB-P2<br>
				PIHAK BADAN PENDAPATAN DAERAH<br>
				KABUPATEN PRINGSEWU,<br>
			</td>
			<td></td>
			<td align="center">
				PIHAK KEDUA<br>
				KEPALA PEKON PODOMORO<br>
				KECAMATAN PRINGSEWU<br>
				KABUPATEN PRINGSEWU,<br>
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td align="center">
				<u>JULIA DARMA</u><br>
				NIP. 19880707 201503 1 007
			</td>
			<td></td>
			<td align="center">
				<u>DIDI MARYADHI., SIP</u><br>
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<u>MENGETAHUI :</u>
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				KEPALA BADAN PENDAPATAN DAERAH<br>
				KABUPATEN PRINGSEWU,
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<u>HIPNI.,SE.,MM</u><br>
				NIP. 19651206 198712 1 001
			</td>
		</tr>
	</table>
';
/*foreach ($ids as $nop) {
	$strHTMLSingle 	= getHTML($nop);
	if ($idx > 0) $strHTML .= '<br pagebreak="true"/>';
	$strHTML .= $strHTMLSingle;
	$idx++;
}*/
// $pdf->Image($sRootPath.'image/'.$fileLogo, 30, 15, 24, '', '', '', '', false, 300, '', false);
$pdf->Ln(0);
$pdf->writeHTML($strHTML, true, false, false, false, '');
$pdf->SetAlpha(0.3);
//$pdf->Output($nop . '.pdf', 'I');
$pdf->Output('Test.pdf', 'I');
