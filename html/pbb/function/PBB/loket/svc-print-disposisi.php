<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1); 

date_default_timezone_set("Asia/Jakarta");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
//$appConfig = $User->GetAppConfig($appID);
$arConfig = $User->GetModuleConfig('mLkt');

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
function getConfigValue($id, $key)
{
	global $DBLink;
	//$id= $appID;
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";

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

function getData($idssb)
{
	global $data, $DBLink, $dataNotaris;

	$query = "SELECT
					BS.*, TKEC.CPC_TKC_KECAMATAN,
					DATE_FORMAT(
						BS.CPM_DATE_RECEIVE,
						'%d-%m-%Y'
					) AS TGL_MASUK,
					TKEL.CPC_TKL_KELURAHAN
				FROM
					cppmod_pbb_services BS
				LEFT JOIN cppmod_tax_kecamatan TKEC ON BS.CPM_OP_KECAMATAN = TKEC.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan TKEL ON BS.CPM_OP_KELURAHAN = TKEL.CPC_TKL_ID
				WHERE
					CPM_ID='$idssb'";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res, "data"));
	$dt = $dataNotaris->data[0];
	return $dt;
}



function getHTML($idssb, $initData, $fileLogo)
{
	global $uname, $NOP, $appId, $arConfig;
	$data = getData($idssb);
	$NOP = $data->CPM_OP_NUMBER;
	//echo $fileLogo;exit;
	//print_r($initData); exit;
	$berkas              = $data->CPM_TYPE;
	$lampiran            = $initData['CPM_ATTACHMENT'];
	$header_berkas       = getConfigValue($appId, 'C_HEADER_DISPOSISI');
	$alamat_berkas       = getConfigValue($appId, 'C_ALAMAT_DISPOSISI');
	$KasubidPelayanan    = getConfigValue($appId, 'KASUBID_PELAYANAN');
	$NIPKasubidPelayanan = getConfigValue($appId, 'NIP_KASUBID_PELAYANAN');
	$KasubidPBB          = getConfigValue($appId, 'KASUBID_PBB');
	$NIPKasubidPBB       = getConfigValue($appId, 'NIP_KASUBID_PBB');
	$KTUPBB              = getConfigValue($appId, 'KASUBID_KTU');
	$NIPKTUPBB           = getConfigValue($appId, 'NIP_KASUBID_KTU');

	$jnsBerkas = array(
		1 => "OP BARU",
		2 => "PEMECAHAN",
		3 => "PENGGABUNGAN",
		4 => "MUTASI",
		5 => "PERUBAHAN DATA",
		6 => "PEMBATALAN",
		7 => "SALINAN",
		8 => "PENGHAPUSAN",
		9 => "PENGURANGAN",
		10 => "KEBERATAN",
		11 => "SURAT KETERANGAN NJOP",
		12 => "PENGURANGAN DENDA"
	);

	$buktiTitle = "BUKTI DISPOSISI " . $jnsBerkas[$berkas] . " PBB";
	$tanggal = date('d-m-Y');
	$parse1 = "";
	$parse2 = "";
	//Kasi Pelayan
	$bKasiPly = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasubid Pelayanan</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br>
					</td></tr>
					<tr><td>" . $KasubidPelayanan . "<br/>____________________________<br/>NIP : " . $NIPKasubidPelayanan . "</td></tr>
					</table>
				</td>";

	$bKabidPBB = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kabid PBB</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Kasi Pendataan			
	$bKasPend = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasi Pendataan</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator Pendataan
	$bKoorPend = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Pelayanan / KTU Pajak</td></tr>
					<tr><td><font size=\"-1\">Tanggal: {$tanggal}</font></td></tr> 
					<tr><td><br><br><br><br><br></td></tr>
					<tr><td>{$KTUPBB}<br/>____________________________<br />NIP : $NIPKTUPBB</td></tr>
					</table>
				</td>";
	//Kasi Pendataan			
	$bKasPend = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Sub.BIDANG PENILAIAN DAN PENETAPAN</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator Penilaian			
	$bKoorPen = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">BIDANG PENGOLAHAN DATA</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator Pengurangan
	$bKoorPeng = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Koordinator Pengurangan,<br/>Penagihan & Keberatan</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";

	//Kasi Pengurangan
	$bKasPeng = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasi Pengurangan,<br/>Penagihan & Keberatan</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Kasi Pendataan			
	$bKasPBB = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasubid PBB</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>" . $KasubidPBB . "<br/>____________________________<br/>NIP : " . $NIPKasubidPBB . "</td></tr>
					</table>
				</td>";

	//UNTUK PARSE 2		

	//Kasi Penetapan
	$bKasPene = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">BIDANG PENAGIHAN</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Petugas Pencetakan
	$bPtgsPctk = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Sub. BIDANG PENDATAAN</td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Petugas Pencetakan 2
	$bPtgsPctk2 = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Petugas Pencetakan<br/></td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator IT
	$bKoorIT = "<td width=\"229\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kordinator IT/td></tr>
					<tr><td><font size=\"-1\">Tanggal: </font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";

	$openTable = "<table border=\"1\" cellpadding=\"12\">
					<tr>";

	$closeTable = " </tr>
				  </table>";


	switch ($berkas) {
		case 1:
		case 12:
		case 2:
			$parse1 = $bKoorPend . $bKasiPly . $bKasPBB;
			//$parse2 = $openTable . $bKabidPBB . $closeTable;
			break;
		case 3:
		case 4:
		case 5:
		case 7:
		case 6:
		case 8:
			$parse1 = $bKoorPend . $bKasiPly . $bKasPBB;
			//$parse2 = $openTable . $bKabidPBB . $closeTable;	
			break;
		case 9:
		case 10:
			$parse1 = $bKoorPend . $bKasiPly . $bKasPBB;
			//$parse2 = $openTable . $bKabidPBB . $closeTable;
			break;
		case 11:
			$parse1 = $bKoorPend . $bKasiPly . $bKasPBB;
			//$parse2 = $openTable . $bKabidPBB . $closeTable;
			break;
	}

	$lamp = "			" . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 1)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Permohonan.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 2)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 4)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 8)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi KTP / Kartu Keluarga.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 16)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Bukti Kepemilikan Tanah.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 32)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi IMB.</td></tr>" : "<tr><td></td><td></td></tr>") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 64)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Bukti Pelunasan PBB Tahun Sebelumnya.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 128)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pemberitahuan Pajak Terutang (SPPT).</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 256)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Ketetapan Pajak Daerah (SKPD).</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 512)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Setoran Pajak Daerah (SSPD).</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 1024)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Kuasa (bila dikuasakan).</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 2048)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi SPPT PBB / SKP tahun lalu.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 4096)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi bukti pembayaran PBB yang terakhir.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 8192)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi SPPT PBB tetangga terdekat.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && ($initData['CPM_ATTACHMENT'] & 8388608)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Keterangan Lurah.</td></tr>" : "") . "
										  
						" . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 1)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Permohonan</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 2)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 4)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 8)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Tidak ada tunggakan PBB tahun-tahun sebelumnya.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 16)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Keterangan : Tidak Mampu / Tidak Bekerja / Tidak Ada Penghasilan / Lainnya / Dokumen lain yang dipersamakan dan telah ditandatangani oleh Pejabat Berwenang.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 32)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 64)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Pengurangan.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 128)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 9) && ($initData['CPM_ATTACHMENT'] & 256)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Izin Mendirikan Bangunan (IMB), khusus bangunan yang bersifat komersil.</td></tr>" : "") . "
										  
						" . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 1)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Permohonan.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 2)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi SPPT PBB yang akan diajukan Permohonan Keberatan.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 4)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Tidak ada tunggakan PBB tahun-tahun sebelumnya.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 8)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</td></tr>" : "") . "
						" . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 16)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Keberatan.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 32)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi SPPT PBB tetangga terdekat.</td></tr>" : "") . "
                        " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 64)) ? " <tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Fotocopi Izin Mendirikan Bangunan (IMB), apabila objek yang diajukan keberatan memiliki bangunan.<br/>(Khusus bangunan yang bersifat komersil).</td></tr>" : "") . " 
			";



	$html = "
	<html>
	<table border=\"1\" cellpadding=\"10\">
		<tr>
			<!--LOGO-->
			<td rowspan=\"2\" align=\"center\" width=\"20%\">
				
			</td>
			<!--COP-->
			<td align=\"center\" width=\"70%\">
				<!-- <font size=\"+3\"> --> " . $header_berkas . "<br />
				<!-- </font> -->
			</td>
			<!--KOSONG-->
			<td rowspan=\"2\" align=\"center\" width=\"10%\">
			</td>
		</tr>
		<tr>
			<!--ALAMAT-->
			<td align=\"center\">
				<!-- <font size=\"+2\"> -->
					" . $alamat_berkas . "
				<!-- </font> -->
			</td>
		</tr>
        <tr>
        	<!--ISI-->
			<td colspan=\"3\">
				<table border=\"0\" cellpadding=\"2\" cellspacing=\"7\">
					<tr>
                        <td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">" . $buktiTitle . "<br /></font></td>
                    </tr>
                    <tr>
                        <td>Nomor</td><td width=\"20\">:</td>
						<td>" . $data->CPM_ID . "</td>
                    </tr>
                    <tr>
                        <td>Nama Wajib Pajak</td><td>:</td>
                        <td>" . $data->CPM_WP_NAME . "</td>
                    </tr>
                    <tr>
                        <td>Tanggal Surat Masuk</td><td>:</td>
                        <td>" . $data->TGL_MASUK . "</td>
                    </tr>";
	if ($arConfig['TAMPILKAN_TGL_SELESAI'] == '1') {

		$arrayEstimasi = array(
			1 => "ESTIMASI_SELESAI_OPBARU",
			2 => "ESTIMASI_SELESAI_PEMECAHAN",
			3 => "ESTIMASI_SELESAI_PENGGABUNGAN",
			4 => "ESTIMASI_SELESAI_MUTASI",
			5 => "ESTIMASI_SELESAI_PERUBAHAN",
			6 => "ESTIMASI_SELESAI_PEMBATALAN",
			7 => "ESTIMASI_SELESAI_SALINAN",
			8 => "ESTIMASI_SELESAI_PENGHAPUSAN",
			9 => "ESTIMASI_SELESAI_PENGURANGAN",
			10 => "ESTIMASI_SELESAI_KEBERATAN",
			11 => "ESTIMASI_SELESAI_SKNJOP",
			12 => "ESTIMASI_SELESAI_OPTANAHREGISTER"
		);

		$tglSelesai = date("d-m-Y", mktime(0, 0, 0, substr($data->TGL_MASUK, 3, 2), (substr($data->TGL_MASUK, 0, 2) + $arConfig[$arrayEstimasi[$berkas]]), substr($data->TGL_MASUK, 6, 4)));
		$html .= "<tr>
                        <td>Tanggal Selesai</td><td>:</td>
                        <td>" . $tglSelesai . "</td>
                    </tr>";
	}
	$html .= "<tr>
                        <td>Kecamatan</td><td>:</td>
                        <td>" . $data->CPC_TKC_KECAMATAN . "</td>
                    </tr>
                    <tr>
                        <td>Kelurahan</td><td>:</td>
                        <td>" . $data->CPC_TKL_KELURAHAN . "</td>
                    </tr>
                    <tr>
                        <td>NOP</td><td>:</td>
                        <td>" . $data->CPM_OP_NUMBER . "</td>
                    </tr>
                     <tr>
                        <td>No Telp WP</td><td>:</td>
                        <td>" . $data->CPM_WP_HANDPHONE . "</td>
                    </tr>
                     <tr>
                        <td>Jenis Berkas</td><td>:</td>
                        <td>" . $jnsBerkas[$data->CPM_TYPE] . "</td>
                    </tr>
					<tr>
                        <td>Lampiran</td><td>:</td> 
                        <td width=\"auto\" cellspacing=\"5\">
						" . ($lampiran != 0 ? "<table border='1'>" . $lamp . "</table>" : "") . "
						</td>
                    </tr>
        		</table>					
			</td>
		</tr>
		<!--SALINAN DISPOSISI-->
		<tr>
			<td colspan=\"3\">
				<table border=\"0\">
					<tr>
					<td>
					<table border=\"1\" cellpadding=\"12\">
					  <tr>
						" . $parse1 . "
					  </tr>
					</table>
						" . $parse2 . "
					</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</html>";
	return $html;
}

function getInitData($id = "")
{
	global $DBLink;

	if ($id == '') return getDataDefault();

	$qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
		return getDataDefault();
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
			return $row;
		}
	}
}

function getDataDefault()
{
	$default = array(
		'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
		'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
		'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
	);
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

// $idssb = $q->svcId;
// $appId = $q->appId;
// $initData = getInitData($idssb);

$v = count($q);

$NOP = "";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 14, 5);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(5);

//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('helvetica', 'B', 20);

// add a page
//$pdf->AddPage();

//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";
for ($i = 0; $i < $v; $i++) {
	$idssb = $q[$i]->svcId;
	$appId = $q[$i]->appId;
	$initData = getInitData($idssb);
	//echo $appId;exit;
	$fileLogo =  getConfigValue($appId, 'LOGO_CETAK_PDF');
	//echo $fileLogo;exit;
	$pdf->AddPage('P', 'A4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb, $initData, $fileLogo);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	//echo $sRootPath.'image/'.$fileLogo;
	$pdf->Image($sRootPath . 'image/' . $fileLogo, 15, 19, 20, '', '', '', '', false, 300, '', false);
	// $pdf->Image($sRootPath.'image/stempel-ttd.png', 50, 50, 35, '', '', '', '', false, 300);
	$pdf->SetAlpha(0.3);
}
/*for ($i=0;$i<$v;$i++) {
	$idssb = $q[$i]->id;
	$uname = "";//$q[0]->uname;
	$draf = $q[$i]->draf;
	$appID = base64_decode($q[$i]->axx);
	$fileLogo =  getConfigValue("1",'FILE_LOGO');
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	$pdf->Image($sRootPath.'view/Registrasi/configure/logo/'.$fileLogo, 5, 20, 40, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	if ($draf == 1) $pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	else if ($draf == 0) $pdf->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
}
*/

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($idssb . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
