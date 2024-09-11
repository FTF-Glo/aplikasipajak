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
$appConfig = $User->GetAppConfig("aPBB");
// var_dump($appConfig);
// echo "123";
// exit;
$arConfig = $User->GetModuleConfig('mLkt');

$dataNotaris = "";
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
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
function getTahunPajak($nop)
{
	global $DBLink, $appConfig;
	$dbname = $appConfig['GW_DBNAME'];
	$con = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $dbname, $appConfig['GW_DBPORT']);
	// var_dump($dbname);
	// EXIT;
	$array = array();
	// mysql_select_db($dbname);
	// var_dump($dbname);
	// exit;

	$query = "
	SELECT
		SPPT_TAHUN_PAJAK
	FROM
		$dbname.pbb_sppt
	WHERE
	NOP = '$nop'
	ORDER BY SPPT_TAHUN_PAJAK ASC
	";
	$res = mysqli_query($con, $query);
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($con);
	} else {

		$i = 0;
		while ($row = mysqli_fetch_array($res)) {
			$array[$i] = $row['SPPT_TAHUN_PAJAK'];
			// $array[$i] = $row['SPPT_TAHUN_PAJAK'];
			$i++;
		}
	}
	return $array;
}
function getNJOPTKP($nop, $tahun)
{
	global $DBLink;
	$query = "
	SELECT
		OP_NJOPTKP
	FROM
		GW_PBB.pbb_sppt
	WHERE
	NOP = '$nop'
	and 
	SPPT_TAHUN_PAJAK = '$tahun'
	#NOP = '190301000100000017'
	ORDER BY SPPT_TAHUN_PAJAK ASC
	";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	} else {
		$array = array();
		$i = 0;
		$row = mysqli_fetch_array($res);
		// while ($row = mysqli_fetch_array($res) ) {
		// $array[$i] = $row['SPPT_TAHUN_PAJAK'];
		// $array[$i] = $row['SPPT_TAHUN_PAJAK'];
		// $i++;
		// }
	}
	return $row;
}
function getUserData($uid)
{
	global $data, $DBLink, $dataNotaris;
	$query = "SELECT 
	U.nip as nip,
	U.nm_lengkap as nama_lengkap,
	RM.ctr_rm_name as jabatan FROM tbl_reg_user_pbb U 
	INNER JOIN 
	cppmod_pbb_role_module RM
	ON U.jabatan = RM.CTR_RM_ID
	where U.userId = '$uid'
	";
	// echo $query;
	$res = mysqli_query($DBLink, $query);

	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	} else {
		$data = mysqli_fetch_array($res);
		// var_dump($data);
	}
	return $data;
}
function getQueryBeritaAcara($is_selesai)
{
	if ($is_selesai) {
		$where1 = "  CPM_STATUS IN (4) AND (CPM_TYPE != '1' AND CPM_TYPE != '2')";
		$where2 = "  CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0' ";
		$where3 = "  CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0' ";
		$where4 =  " CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0' ";
		$where5 =  " CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0' ";
	} else {
		$where1 = " 1 = 1";
		$where2 = " 1 = 1";
		$where3 = " 1 = 1";
		$where4 = " 1 = 1";
		$where5 = " 1 = 1";
	}
	return "
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, 
        TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR FROM cppmod_pbb_services BS LEFT JOIN 
        cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN LEFT JOIN 
        cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN LEFT JOIN
        cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
        WHERE $where1
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
        FROM cppmod_pbb_services BS 
        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
        LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
        JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
        WHERE  $where2
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
        FROM cppmod_pbb_services BS 
        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
        LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
        JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
        WHERE $where3
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
        FROM cppmod_pbb_services BS 
        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
        LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_SID = BS.CPM_ID 
        JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
        WHERE $where4
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
        FROM cppmod_pbb_services BS 
        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
        LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_SID = BS.CPM_ID 
        JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
        WHERE $where5
            ";
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
	// var_dump($idssb);
	$where = " ";
	if ($idssb != "") $whereClause[] = " (TBL.CPM_ID = '" . $idssb . "' ) ";
	// OR TBL.CPM_NEW_NOP LIKE '%".$idssb."%'  OR TBL.CPM_OP_NUMBER LIKE '%".$idssb."%' 
	if ($whereClause) $where = " WHERE " . join('AND', $whereClause);
	$query = "SELECT * FROM ( " . getQueryBeritaAcara(0) . " ) TBL 
LEFT JOIN 
	(
		SELECT
			*
		FROM
			cppmod_pbb_sppt_final AS A
		UNION ALL
			SELECT
				*
			FROM
				cppmod_pbb_sppt_susulan AS B
 	) AS MS
ON  MS.CPM_NOP = TBL.CPM_OP_NUMBER OR MS.CPM_NOP = TBL.CPM_NEW_NOP 

$where ORDER BY CPM_DATE_RECEIVE, CPM_SPPT_DOC_CREATED DESC";
	// echo $query;die;

	// var_dump($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";

	$res = mysqli_query($DBLink, $query);
	// echo $query;
	// exit;
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res, "data"));

	// die(var_dump($dataNotaris));

	$dt = $dataNotaris->data[0];
	return $dt;
}

function geValidDataOnly($dataNotaris, $index = 0)
{
	$validKeys = array(
		'CPM_ID',
		'CPM_WP_NAME',
		'CPM_WP_ADDRESS',
		'CPM_OP_ADDRESS',
		'CPM_OP_NUMBER',
		'CPM_NEW_NOP',
		'CPM_TYPE',
		'CPM_STATUS',
		'CPM_DATE_RECEIVE',
		'CPM_RECEIVER',
	);
}



function getHTML($idssb, $initData, $fileLogo, $userData)
{
	global $uname, $NOP, $appId, $arConfig, $sRootPath;
	$data = getData($idssb);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	// exit;
	// $NOP = $idssb;
	$tahunPajak = getTahunPajak($data->CPM_NEW_NOP ? $data->CPM_NEW_NOP : $data->CPM_OP_NUMBER);
	// var_dump($tahunPajak);
	$count = count($tahunPajak);
	// var_dump($count);
	if ($count > 0) {
		$awalTahun = $tahunPajak[0];
		$akhirTahun = $tahunPajak[$count - 1];
		if ($count > 1) {
			$tahunPajak = $awalTahun . " s/d " . $akhirTahun;
		} else {
			$tahunPajak = $awalTahun;
		}

		$NJOPTKP = getNJOPTKP($data->CPM_NEW_NOP, $akhirTahun);
		if (intval($NJOPTKP) > 0) {
			$NJOPTKP = "Dikenakan";
		} else {
			$NJOPTKP = "Tidak Dikenakan";
		}
	} else {
		$NJOPTKP = " ";
		$tahunPajak = " ";
	}
	// echo "<pre>";??
	// print_r($tahunPajak);
	// echo "</pre>";

	//echo $fileLogo;exit;
	//print_r($initData); exit;
	// $berkas 		= $data->CPM_TYPE;
	$berkas 		= 1;
	$lampiran 		= $initData['CPM_ATTACHMENT'];
	//    $gambarLogo =  getConfigValue($appId,'LOGO_CETAK_PDF');
	// $url  = "image/logo.png";
	$gambarLogo =  getConfigValue($appId, 'LOGO_CETAK_PDF');
	// $fileLogo =  getConfigValue($appId,'LOGO_CETAK_PDF');
	$url  = 'image/logo.jpg';

	// var_dump($url);exit;

	$header_berkas	= getConfigValue($appId, 'C_HEADER_DISPOSISI');
	$alamat_berkas	= getConfigValue($appId, 'C_ALAMAT_DISPOSISI');
	// $peneliti	= getConfigValue ($appId, 'C_PENELITI');
	// $peneliti_nip	= getConfigValue ($appId, 'C_NIP_PENELITI');
	// $peneliti_jabatan	= getConfigValue ($appId, 'C_JABATAN_PENELITI');
	$peneliti	= $userData['nama_lengkap'];
	$peneliti_nip	= $userData['nip'];
	$peneliti_jabatan	= $userData['jabatan'];


	$C_PETUGAS_PENETAPAN	= getConfigValue($appId, 'C_PETUGAS_PENETAPAN');
	$C_KEPALA_PENETAPAN	= getConfigValue($appId, 'KABID_NAMA');
	$C_KEPALA_PENETAPAN_NIP	= getConfigValue($appId, 'KABID_NIP');
	$C_PETUGAS_PENGOLAHANDATA	= getConfigValue($appId, 'C_PETUGAS_PENGOLAHANDATA');
	$C_KEPALA_PENGOLAHANDATA	= getConfigValue($appId, 'C_KEPALA_PENGOLAHANDATA');
	$C_PETUGAS_PENETAPAN_NIP	= getConfigValue($appId, 'C_PETUGAS_PENETAPAN_NIP');




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
		11 => "SURAT KETERANGAN NJOP"
	);
	$spaceTableMessage = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$spaceTable = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	// $buktiTitle = "BUKTI DISPOSISI ".$jnsBerkas[$berkas]." PBB";
	$buktiTitle = "BERITA ACARA PENELITIAN SEDERHANA KANTOR <br/> PENDAFTARAN OBJEK PAJAK BUMI DAN BANGUNAN <br/> NOMOR :  ";

	$parse1 = "";
	$parse2 = "";
	//Kasi Pelayan
	$bKasiPly = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasi Pelayanan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";

	$bKabidPBB = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kabid PBB</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Kasi Pendataan			
	$bPetugasPenilaian = "<td width=\"280\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\"><b>KASUBBID PBB P2</b></td></tr>
					<tr><td></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td align='center'>" .  $C_PETUGAS_PENETAPAN . "<br />NIP : " . $C_PETUGAS_PENETAPAN_NIP . "</td></tr>
					</table>
				</td>";
	$bPetugasPengelolahanData = "<td width=\"240\" height=\"150\">
				<table border=\"0\" cellspacing=\"3\">
				<tr><td align=\"center\"><b>Petugas Pengolahan Data</b></td></tr>
				<tr><td></td></tr> 
				<tr><td><br><br><br><br></td></tr>
				<tr><td align='center'>" . $C_PETUGAS_PENGOLAHANDATA . "<br />NIP : </td></tr>
		</table>
			</td>";

	$bKabidPengolahanData = "<td width=\"240\" height=\"150\">
		<table border=\"0\" cellspacing=\"3\">
		<tr><td align=\"center\"><b>Kepala Bidang Pengelolahan Data dan Evaluasi PAD</b></td></tr>
		<tr><td></td></tr> 
		<tr><td><br><br><br></td></tr>
		<tr><td align='center'>" . $C_KEPALA_PENGOLAHANDATA . "<br />NIP : </td></tr>
		</table>
	</td>";
	//Koordinator Pendataan
	$bKoorPend = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">KEPALA BIDANG PBB-P2 DAN BPHTB</td></tr>
					<tr><td><font size=\"-2\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Kasi Pendataan			
	$bKasPend = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Sub.BIDANG PENILAIAN DAN PENETAPAN</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator Penilaian			
	$bKoorPen = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">BIDANG PENGOLAHAN DATA</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator Pengurangan
	$bKoorPeng = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Koordinator Pengurangan,<br/>Penagihan & Keberatan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";

	//Kasi Pengurangan
	$bKasPeng = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Kasi Pengurangan,<br/>Penagihan & Keberatan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";


	//UNTUK PARSE 2		

	//Kasi Penetapan
	$bKasPene = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">BIDANG PENAGIHAN</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Petugas Pencetakan
	$bPtgsPctk = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Petugas Penilaian dan Penetapan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Petugas Pencetakan 2
	$bPtgsPctk2 = "<td width=\"240\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\">Petugas Pencetakan<br/></td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>____________________________<br />NIP : </td></tr>
					</table>
				</td>";
	//Koordinator IT
	$bkabidPenetapan = "<td width=\"350\" height=\"150\">
					<table border=\"0\" cellspacing=\"3\">
					<tr><td align=\"center\"><b>KEPALA BIDANG PBB P2 DAN BPHTB</b></td></tr>
					<tr><td></td></tr> 
					<tr><td><br><br><br><br></td></tr>
				<tr><td align='center'>" . $C_KEPALA_PENETAPAN . "<br />NIP : " . $C_KEPALA_PENETAPAN_NIP . "</td></tr>

					</table>
				</td>";

	$openTable = "<table border=\"0\" cellpadding=\"12\">
					<tr>";

	$closeTable = " </tr>
				  </table>";

	// var_dump($berkas);
	switch ($berkas) {
		case 1:
			$parse1 = $bPetugasPenilaian . $bkabidPenetapan;
			// $parse2 = $openTable . $bKasPene . $bPtgsPctk . $closeTable;
			//$parse2 = $openTable . $bPetugasPengelolahanData . $bKabidPengolahanData . $closeTable;
			break;
			// case 2:
			// 	$parse1 = $bKoorPend . $bKasPend . $bKoorPen;
			// 	break;
			// case 3:
			// case 4:
			// case 5:
			// case 7:
			// case 6:
			// case 8:
			// 	$parse1 = $bKoorIT . $bPtgsPctk;	
			// 	break;
			// case 9:
			// case 10:
			// 	$parse1 = $bKoorPeng . $bKasPeng . $bPtgsPctk2;
			// 	break;
			// case 11:
			// 	$parse1 = $bKasiPly . $bKabidPBB;
			// 	break;
	}
	// $parse1 = "";
	// $parse2 = "";

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

	$hari = array(
		1 => "Senin",
		2 => "Selasa",
		3 => "Rabu",
		4 => "Kamis",
		5 => "Jum'at",
		6 => "Sabtu",
		7 => "Minggu",
	);
	$html = "
	<html>
	<table border=\"0\" cellpadding=\"3\" style=\"font-family:Arial-Narrow\" >
		<tr>
			<!--LOGO-->
			<td rowspan=\"2\" align=\"center\" width=\"12%\">&nbsp;</td>
			<!--COP-->
			<td align=\"center\" width=\"88%\">
				<!-- <font size=\"+3\"> --> " . $header_berkas . "
				<!-- </font> -->
			</td>
		</tr>
		<tr>
			<!--ALAMAT-->
			<td align=\"center\" colspan=\"3\">
					" . $alamat_berkas . "	
			</td>
		</tr>		
        <tr>
        	<!--ISI-->
			<td colspan=\"3\">
				<table border=\"0\" width=\"100%\">
					<tr>
						<td colspan=\"3\" valign=\"top\">
							<p style=\"text-align:center;font-size:35px;border-top: 1px solid black;\">
								<b><br>BERITA ACARA PENELITIAN SEDERHANA KANTOR<BR> PENDAFTARAN OBJEK PAJAK BUMI DAN BANGUNAN<BR>NOMOR : 800/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/V.04/C/" . date("Y") . " </b>
							</p>
							<br>
						</td>
					</tr>
					<tr>
						<td colspan=\"3\" >&nbsp;Pada hari ini, " . $hari[date("w")] . ", " . date("d M Y") . " yang bertanda tangan dibawah ini : <br /></td>
					</tr>			
					<tr>
						<td width=\"40%\">" . $spaceTable . "Nama / NIP</td>
						<td width=\"1%\" >:</td>
						<td width=\"59%\">" . $peneliti . " / " . $peneliti_nip . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Jabatan</td>
						<td width=\"1%\">:</td>
						<td>" . $peneliti_jabatan . " <br></td>
					</tr>
					<tr>
						<td colspan=\"3\" valign=\"middle\">
						<p style=\"text-align:justify\">" . $spaceTableMessage . "Sehubungan dengan permohonan Pendaftaran Objek Pajak Bumi dan Bangunan dari Wajib Pajak, dengan nomor  &nbsp;pelayanan : " . $data->CPM_ID . ", telah dilakukan penelitian data baik atribut maupuan spasial sehingga diperoleh &nbsp;&nbsp;kesimpulan</p>
						<br>
						</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Nama Wajib Pajak</td>
						<td width=\"1%\">:</td>
						<td>" . $data->CPM_WP_NAME . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Nomor Objek Pajak (NOP)</td>
						<td width=\"1%\">:</td>
						<td>" . ($data->CPM_NEW_NOP ? $data->CPM_NEW_NOP : $data->CPM_OP_NUMBER) . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Letak Objek Pajak</td>
						<td width=\"1%\">:</td>
						<!-- <td>" . $data->CPM_OP_ALAMAT . "</td> -->
						<td>" . $data->CPM_OP_ADDRESS . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Alamat Wajib Pajak</td>
						<td width=\"1%\">:</td>
						<!-- <td>" . $data->CPM_WP_ALAMAT . "</td> -->
						<td>" . $data->CPM_WP_ADDRESS . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Luas Tanah</td>
						<td width=\"1%\">:</td>
						<td>" . $data->CPM_OP_LUAS_TANAH . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Kode ZNT</td>
						<td width=\"1%\">:</td>
						<td>" . $data->CPM_OT_ZONA_NILAI . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "Luas Bangunan</td>
						<td width=\"1%\">:</td>
						<td>" . $data->CPM_OP_LUAS_BANGUNAN . "</td>
					</tr>
						<tr>
						<td>" . $spaceTable . "Tahun Pajak</td>
						<td width=\"1%\">:</td>
						<td>" . $tahunPajak . "</td>
					</tr>
					<tr>
						<td>" . $spaceTable . "NJOPTKP</td>
						<td width=\"1%\">:</td>
						<td>" . $NJOPTKP . "</td>
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
			11 => "ESTIMASI_SELESAI_SKNJOP"
		);

		// $tglSelesai = date("d-m-Y", mktime(0, 0, 0, substr($data->TGL_MASUK, 3,2), (substr($data->TGL_MASUK, 0,2)+$arConfig[$arrayEstimasi[$berkas]]), substr($data->TGL_MASUK, 6,4)));
		$tglSelesai = date("d M Y");
		$html .= "
                  <!--
                 	<tr>
                        <td>" . $spaceTable . "Tanggal Selesai</td><td>:</td>
                        <td>" . $tglSelesai . "</td>
                    </tr>
                   -->
                    ";
	}
	$html .= "
           		
                     <tr>
                        <td>" . $spaceTable . "Jenis Berkas</td>
                        <td>:</td>
                        <td>" . $jnsBerkas[$data->CPM_TYPE] . "</td>
                    </tr>


                    <tr>
		       		 <td colspan=\"3\"><p style=\"text-align:justify\" ><br>" . $spaceTableMessage . "&nbsp;Demikian Berita Acara Penelitian Pendaftaran Objek Pajak ini dibuat sebagai dasar penerbitan SPPT PBB.
		       		 <!--
					 <br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Berita Acara dibuat dalam 2 (dua) rangkap lembar pertama untuk Bidang Pelayanan dan Pengolahan Data dan lembar &nbsp;kedua untuk Bidang PBB P2
					 -->
		       		 </p>
				        </td>
					</tr>

		       		
				
        		</table>					
			</td>
		</tr>
		<!--SALINAN DISPOSISI-->
		<tr>
			<td width=\"100%\" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<table border=\"0\">
					<tr>
					<td>
					<table border=\"0\" cellpadding=\"12\">
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

	// echo $html;exit;
	return $html;
}

function getInitData($id = "")
{
	global $DBLink;

	if ($id == '') return getDataDefault();

	$qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";
	// echo $qry;
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
// print_r($q);
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
$pdf->SetMargins(5, 8, 5);
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

$pdf->SetFont('bookmanoldstyle', '', 12);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";
for ($i = 0; $i < $v; $i++) {
	$idssb = $q[$i]->svcId;
	// var_dump($idssb);
	// $idssb = "10/spop-baru/penda/2017";
	$appId = $q[$i]->appId;
	$initData = getInitData($idssb);
	//echo $appId;exit;
	// var_dump($initData);exit;
	$fileLogo =  getConfigValue($appId, 'LOGO_CETAK_PDF');
	// echo $fileLogo;exit;
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$userData = getUserData($_REQUEST['uid']);

	$HTML = getHTML($idssb, $initData, $fileLogo, $userData);
	$pdf->writeHTML($HTML, true, false, true, false, '');
	//echo $sRootPath.'image/'.$fileLogo;
	$pdf->Image($sRootPath . 'image/' . $fileLogo, 18, 10, 20, '', '', '', '', false, 300, '', false);
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
// print_r($_REQUEST);
// echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
