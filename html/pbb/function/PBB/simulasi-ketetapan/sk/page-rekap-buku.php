<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'simulasi-ketetapan' . DIRECTORY_SEPARATOR . 'sk', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/central/dbspec-central.php");

require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");

// 
echo "<script type=\"text/javascript\" src=\"function/PBB/consol/script.js\"></script>";
echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

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
function showKec()
{
	global $aKecamatan, $kec;
	foreach ($aKecamatan as $row)
		echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
}

function displayContent($selected)
{
	global $isSusulan, $kec, $jumhal, $srch, $aKecamatan, $a, $appConfig, $buku, $tahun, $kel, $DBLink;

	$url 		= $appConfig['SMS_URL'];
	$user 		= $appConfig['SMS_USERID'];
	$pass 		= $appConfig['SMS_USERPWD'];

	$SMSParam = base64_encode("" . $url . "&" . $user . "&" . $pass);
	//echo $selected;
	echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . $kec . "\">";
	//echo "<div class=\"ui-widget consol-main-content\">\n";
	//echo "\t<div class=\"ui-widget-content consol-main-content-inner\" " . (($selected == 33) ? "style=\"overflow-y:hidden; overflow-x:auto;\"" : "") . ">\n";
	//echo "\t<table border=0 " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . "><tr><td>";
	echo "\t<div class=\"row\" style=\"margin-bottom: 20px\">";
	if ($selected == 10) {
		echo "<div class=\"col-md-1\">
			<input type=\"button\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Cetak\" name=\"btn-finalize\" onclick=\"exportToExcel(" . $selected . ")\">
		</div>
		<div class=\"col-md-1\">
			<input type=\"button\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Cetak MOU\" name=\"btn-finalize\" onclick=\"cetakMOU(" . $selected . ")\">
		</div>
		<div class=\"col-md-1\" style=\"margin-top: 7px; text-align: right;\">Tahun</div>";
		echo "<div class=\"col-md-2\"><select class=\"form-control\" name=\"tahun\" id=\"tahun\"  style=\"width:150px;display:inherit\" onchange=\"setTabs(" . $selected . "," . $selected . ")\">";
		$sql = "SELECT REPLACE(table_name,'cppmod_pbb_sppt_cetak_','') as `table` FROM information_schema.tables WHERE `table_name` LIKE 'cppmod_pbb_sppt_cetak%' and TABLE_SCHEMA='SW_PBB' ORDER BY 1 DESC";
		// echo $sql;
		$result = mysqli_query($DBLink, $sql);
		// echo "<option value='".date('Y')."'>".date('Y')."</option>";
		echo "<option value='" . $appConfig['tahun_tagihan'] . "'>" . $appConfig['tahun_tagihan'] . "</option>";
		while ($r = mysqli_fetch_array($result)) {
			if ($r[0] == $tahun) $selected70 = 'selected';
			else $selected70 = '';

			echo "<option value='$r[0]' $selected70>$r[0]</option>";
		}
		echo "</select></div>";
	} else if ($selected == 33) {
		echo "<div class=\"col-md-1\">
			<input type=\"button\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\" onClick=\"return actionSendNotification('" . $SMSParam . "', '9');\">
		</div>";
		// echo "<input type=\"button\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\"/ onClick=\"sendSMS('".$SMSParam."', '9');\">\n";
	}

	if (empty($tahun)) {
		$tahun = $appConfig['tahun_tagihan'];
	}
	/* if ($selected == 33){
		echo "<input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\">&nbsp&nbsp";
		echo "<input type=\"button\" value=\"PDF SK Kep. DPD\" name=\"btn-cetak2\" id=\"btn-cetak2\">&nbsp&nbsp";
		//echo "<input type=\"button\" value=\"Kirim ke Penetapan\" name=\"btn-kirim\" id=\"btn-kirim\">&nbsp&nbsp";
	} */
	/* if ($selected == 90){
		echo "<input type=\"button\" value=\"Cetak LHP\" name=\"btn-cetak-lhp\" id=\"btn-cetak-lhp\">&nbsp&nbsp";
	} */
	echo "<div class=\"col-md-1\" style=\"margin-top: 10px; text-align: right;\">Filter</div>
	<div class=\"col-md-2\">
		<select id=\"buku\" name=\"buku\" class=\"form-control\" onchange=\"filBook(" . $selected . ",this)\">
			<option value=\"0\" " . ((isset($buku) && $buku == "0") ? "selected" : "") . ">Pilih Semua</option>
			<option value=\"1\" " . ((isset($buku) && $buku == "1") ? "selected" : "") . ">Buku 1</option>
			<option value=\"12\" " . ((isset($buku) && $buku == "12") ? "selected" : "") . ">Buku 1,2</option>
			<option value=\"123\" " . ((isset($buku) && $buku == "123") ? "selected" : "") . ">Buku 1,2,3</option>
			<option value=\"1234\" " . ((isset($buku) && $buku == "1234") ? "selected" : "") . ">Buku 1,2,3,4</option>
			<option value=\"12345\" " . ((isset($buku) && $buku == "12345") ? "selected" : "") . ">Buku 1,2,3,4,5</option>
			<option value=\"2\" " . ((isset($buku) && $buku == "2") ? "selected" : "") . ">Buku 2</option>
			<option value=\"23\" " . ((isset($buku) && $buku == "23") ? "selected" : "") . ">Buku 2,3</option>
			<option value=\"234\" " . ((isset($buku) && $buku == "234") ? "selected" : "") . ">Buku 2,3,4</option>
			<option value=\"2345\" " . ((isset($buku) && $buku == "2345") ? "selected" : "") . ">Buku 2,3,4,5</option>
			<option value=\"3\" " . ((isset($buku) && $buku == "3") ? "selected" : "") . ">Buku 3</option>
			<option value=\"34\" " . ((isset($buku) && $buku == "34") ? "selected" : "") . ">Buku 3,4</option>
			<option value=\"345\" " . ((isset($buku) && $buku == "345") ? "selected" : "") . ">Buku 3,4,5</option>
			<option value=\"4\" " . ((isset($buku) && $buku == "4") ? "selected" : "") . ">Buku 4</option>
			<option value=\"45\" " . ((isset($buku) && $buku == "45") ? "selected" : "") . ">Buku 4,5</option>
			<option value=\"5\" " . ((isset($buku) && $buku == "5") ? "selected" : "") . ">Buku 5</option>
		</select>
	</div>
	</div>
	<div class=\"row\">
		<div class=\"col-md-12\">
			<div class=\"table-responsive\">
				<table class=\"table table-hover\" " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . ">
					<tr>
						<td>&nbsp;</td>
						<td colspan=\"3\"><strong>" . ($tahun - 1) . "</strong></td>
						<td colspan=\"4\"><strong>" . $tahun . "</strong></td>
					</tr>
					<tr>\n";
	if ($selected == 10 || $selected == 33) {
		echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>\n";
	} else {
		echo "\t\t<td width=\"20\" class=\"tdheader\">&nbsp;</td>\n";
	}
	echo createHeader($selected);
	echo "\t</tr>\n";
	echo printData($selected);
	echo "</table>\n";
	echo "\t</div></div></div>\n";
	echo "\t<div class=\"ui-widget-header consol-main-content-footer\" style=\"height: 30px;\"><div style=\"float:left\">\n";
	echo "\t\t</div>\n";
	echo "\t\t<div style=\"float:right;\">" . paging() . "</div>\n";
	echo "\t</div>\n";
	echo "</div>\n";
	echo "</form>\n";
}

function createHeader($selected)
{
	global $appConfig;

	//variable header set
	$hBasic =
		"\t\t<td class=\"tdheader\"> Buku </td> \n
		\t\t<td class=\"tdheader\"> Jumlah </td> \n
		\t\t<td class=\"tdheader\"> Total </td> \n
		\t\t<td class=\"tdheader\"> Buku </td> \n
		\t\t<td class=\"tdheader\"> Jumlah </td> \n
		\t\t<td class=\"tdheader\"> Total </td> \n
		\t\t<td class=\"tdheader\"> Luas Bumi </td> \n
		\t\t<td class=\"tdheader\"> Luas Bangunan </td> \n
		\t\t<td class=\"tdheader\"> % Kenaikan </td> \n";

	$header = $hBasic;

	return $header;
}

function printData($selected)
{
	global $isSusulan;

	$HTML = "";
	$aData = getData($selected);

	$i = 0;
	if ($aData != null && !empty($aData) && count($aData) > 0) {
		$totaljumlahbukubefore = 0;
		$totalallbukubefore = 0;
		$totaljumlahbuku = 0;
		$totalallbuku = 0;
		$totalluasbumi = 0;
		$totalluasbangunan = 0;
		$totalkenaikan = 0;

		foreach ($aData as $data) {
			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			if ($selected != 26) {
				$HTML .= "\t<tr>\n";
			}
			if ($selected == 10 || $selected == 42) {
				//$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . (isset($data['CPM_TRAN_ID']) ? $data['CPM_TRAN_ID'] : '') . "\" /></td>\n";
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . ($i + 1) . "\" /></td>\n";
			} else if (($selected == 60 && $data['FLAG'] == 2) || $selected == 70) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['NOP'] . "\" /></td>\n";
			} else if ($selected == 24) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
			} else if ((($selected == 80) /*&& $isSusulan*/) || ($selected == 81)) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
			} else if (($selected == 82)) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all-masal[]\" class=\"check-all-masal\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
			} else if ($selected == 50 && $isSusulan) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
			} else if ($selected == 5) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all-tertunda[]\" class=\"check-all-tertunda\" type=\"checkbox\" value=\"" . $data['CPM_ID'] . "\" /></td>\n";
			} else if ($selected == 26) {
				$HTML .= "";
			} else {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
			}
			if ($selected != 26) {
				$HTML .= parseData($data, $selected, $class, $i);

				$totaljumlahbukubefore += $data['jumlahbukubefore'];
				$totalallbukubefore += $data['totalbukubefore'];
				$totaljumlahbuku += $data['jumlahbuku'];
				$totalallbuku += $data['totalbuku'];
				$totalluasbumi += $data['luasbumi'];
				$totalluasbangunan += $data['luasbangungan'];
				$totalkenaikan += $data['kenaikan'];

				$HTML .= "\t</tr>\n";
			}
			$i++;
		}

		$HTML .= "<tr>
			<td>&nbsp;</td>
			<td>Total</td>
			<td>" . ($totaljumlahbukubefore > 0 ? number_format($totaljumlahbukubefore, '0', '', ',') : 0) . "</td>
			<td>" . ($totalallbukubefore > 0 ? number_format($totalallbukubefore, '0', '', ',') : 0) . "</td>
			<td>&nbsp;</td>
			<td>" . ($totaljumlahbuku > 0 ? number_format($totaljumlahbuku, '0', '', ',') : 0) . "</td>
			<td>" . ($totalallbuku > 0 ? number_format($totalallbuku, '0', '', ',') : 0) . "</td>
			<td>" . ($totalluasbumi > 0 ? number_format($totalluasbumi, '0', '', ',') : 0) . "</td>
			<td>" . ($totalluasbangunan > 0 ? number_format($totalluasbangunan, '0', '', ',') : 0) . "</td>
			<td>" . ($totalkenaikan > 0 ? number_format($totalkenaikan, '0', '', ',') : 0) . "</td>
		</tr>";
	}
	return $HTML;
}

function getData($selected)
{
	global $dbServices, $srch, $arConfig, $appConfig, $dbGwCurrent, $data, $kec, $custom, $jumhal, $totalrows, $perpage, $page, $kel, $buku, $tahun;
	$filter = array();
	//Seleksi Status dan jenis Berkas
	$filter['CPM_TRAN_FLAG'] = 0;

	// for new version
	if ($selected == 10) {
		//show data with status = 0
		$filter['CPM_TRAN_STATUS'][] = 0;
	} else if ($selected == 20) {
		$filter['CPM_TRAN_STATUS'][] = 1;
		$filter['CPM_TRAN_STATUS'][] = 2;
		$filter['CPM_TRAN_STATUS'][] = 3;
		//$filter['CPM_TRAN_STATUS'][] = 4;
	} else if ($selected == 21) {
		$filter['CPM_TRAN_STATUS'][] = 1;
	} else if ($selected == 22) {
		$filter['CPM_TRAN_STATUS'][] = 2;
	} else if ($selected == 24) {
		$filter['CPM_TRAN_STATUS'][] = 1;
		$filter['CPM_TRAN_STATUS'][] = 2;
		$filter['CPM_TRAN_STATUS'][] = 3;
		//$filter['CPM_TRAN_STATUS'][] = 4;
		//$filter['CPM_TRAN_STATUS'][] = 5;// reserved buat tab "tertunda" modul "penetapan"
		#Verifikasi III >> Tab Tertunda
	} else if ($selected == 25) {
		$filter['CPM_TRAN_STATUS'][] = 3;
	} else if ($selected == 30) {
		$filter['CPM_TRAN_STATUS'][] = 6;
		$filter['CPM_TRAN_STATUS'][] = 7;
		$filter['CPM_TRAN_STATUS'][] = 8;
	} else if ($selected == 31) {
		$filter['CPM_TRAN_STATUS'][] = 6;
		$filter['CPM_TRAN_STATUS'][] = 7;
		$filter['CPM_TRAN_STATUS'][] = 8;
		$filter['CPM_TRAN_STATUS'][] = 9;
	} else if ($selected == 32) {
		if ($arConfig['usertype'] == "dispenda")
			$filter['CPM_TRAN_STATUS'][] = 7;
		$filter['CPM_TRAN_STATUS'][] = 8;
		$filter['CPM_TRAN_STATUS'][] = 9;
		#Verifikasi III >> Tab Ditolak
	} else if ($selected == 35) {
		if ($arConfig['usertype'] == "dispenda2")
			$filter['CPM_TRAN_STATUS'][] = 8;
	} else if ($selected == 40) {
		$filter['CPM_TRAN_STATUS'][] = 4;
	} else if ($selected == 41) {
		$filter['CPM_TRAN_STATUS'][] = 2;
	} else if ($selected == 42) {
		if ($arConfig['usertype'] == "dispenda") {
			$filter['CPM_TRAN_STATUS'][] = 3;
		} else {
			$filter['CPM_TRAN_STATUS'][] = 4;
		}
		#Verifikasi III >> Tab Disetujui
	} else if ($selected == 45) {
		if ($arConfig['usertype'] == "dispenda2") {
			$filter['CPM_TRAN_STATUS'][] = 4;
		}
	} else if ($selected == 50) {
		$filter['CPM_TRAN_STATUS'][] = 4; // reserved buat tab "telah ditetapkan" modul "penetapan"
	}

	$qBuku = null;

	if ($buku != 0) {
		switch ($buku) {
			case 1:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000 ";
				break;
			case 12:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
				break;
			case 123:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 1234:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 12345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 2:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
				break;
			case 23:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 234:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 2345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 3:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 34:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 4:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 45:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 5:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
		}
	}

	if ($srch) {
		$custom = "(CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
	}

	$perpage = $appConfig['ITEM_PER_PAGE'];
	$uid = $data->uid;
	$uname  = $data->uname;

	$filter = array();

	//-----------------------
	if ($kel) $filter['OP_KELURAHAN_KODE'] = $kel;
	//$data = $dbServices->get($filter, $srch, $jumhal, $perpage, $page);
	if (!$tahun) $tahun = $appConfig['tahun_tagihan'];

	/*if ($daftarNOP) {
		$filter['NOP'] = trim($daftarNOP);
	}*/

	//var_dump($filter);
	//var_dump($srch);
	/*var_dump($qBuku);
	var_dump($jumhal);
	var_dump($perpage);
	var_dump($page);
	var_dump($tahun);
	var_dump($appConfig);*/

	$data = $dbGwCurrent->get70gs($filter, $srch, $buku, $qBuku, $jumhal, $perpage, $page, $tahun, $appConfig, true); // aldes
	$totalrows = 5;
	return $data;
}

function getDataReduce($field, $id)
{
	global $dbServices;
	return $dbServices->getReduce($field, $id);
}

function getDataLHP($field, $id)
{
	global $dbServices;
	return $dbServices->getLHP($field, $id);
}

function kecShow($kode)
{
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKecamatanNama($kode);
}
function kelShow($kode)
{
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKelurahanNama($kode);
}

function parseData($data, $selected, $class, $index)
{
	global $arConfig, $appConfig, $a, $m, $params;

	$parse = '';
	$parse .= "\t\t<td class=\"$class\"> Buku " . ($index + 1) . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['jumlahbukubefore'] > 0 ? number_format($data['jumlahbukubefore'], '0', '', ',') : 0) . "</td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['totalbukubefore'] > 0 ? number_format($data['totalbukubefore'], '0', '', ',') : 0) . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> Buku " . ($index + 1) . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['jumlahbuku'] > 0 ? number_format($data['jumlahbuku'], '0', '', ',') : 0) . "</td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['totalbuku'] > 0 ? number_format($data['totalbuku'], '0', '', ',') : 0) . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['luasbumi'] > 0 ? number_format($data['luasbumi'], '0', '', ',') : 0) . "</td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['luasbangungan'] > 0 ? number_format($data['luasbangungan'], '0', '', ',') : 0) . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . ($data['kenaikan'] > 0 ? number_format($data['kenaikan'], '0', '', ',') : 0) . " </td> \n";
	return $parse;
}
function paging()
{
	global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage, $totalrows;

	//$params = "a=".$a."&m=".$m;

	$html = "<div>";
	$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
	$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

	if ($page != 1) {
		//$page--;
		$html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
	}
	if ($rowlast < $totalrows) {
		//$page++;
		$html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
	}
	$html .= "</div>";
	return $html;
}

//mulai program
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$buku 	= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
$jumhal = @isset($_REQUEST['jumhal']) ? $_REQUEST['jumhal'] : "";
$kec 	= @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'], 0, 7) : "";
$tahun     = @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$penguranganval  = @isset($_REQUEST['penguranganval']) ? $_REQUEST['penguranganval'] : "";

$q = base64_decode($q);
$q = $json->decode($q);
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if (isset($_SESSION['stSPOP'])) {
	if ($_SESSION['stSPOP'] != $s) {
		$_SESSION['stSPOP'] = $s;
		$kec = "";
		$kel = "";
		$buku = "";
		$srch = "";
		$tahun = "";
		$pengurangan = "";
		$jumhal = 10;
		$page = 1;
		$np = 1;
		echo "<script language=\"javascript\">page=1;</script>";
	}
} else {
	$_SESSION['stSPOP'] = $s;
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

/* === Get cookie data === */
$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data = null;
if (strlen(trim($cData)) > 0) {
	$data = $json->decode(base64_decode($cData));
}

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbServices = new DbServices($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$defaultPage = 1;

if ($s == 90) {
	$idForm = $arConfig['lhp-form'];
} else
	$idForm = $arConfig['id_view_lampiran'];

$params = "a=$a&m=$m&f=" . $idForm;
if (($s == 10) || ($s == 20) || ($s == 90) || ($s == 12)) {
	$params .= "&dis=0&tab=$s";
} else {
	$params .= "&dis=1&tab=$s";
}
#echo $params;

$uid = isset($data->uid) ? $data->uid : '';
//$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

$C_HOST_PORT 	= $appConfig['GW_DBHOST'];
$C_USER 		= $appConfig['GW_DBUSER'];
$C_PWD 			= $appConfig['GW_DBPWD'];
$C_DB 			= $appConfig['GW_DBNAME'];

//print_r($aKecamatan);
//print_r($aKelurahan);
?>
<script type="text/javascript">
	var sel = "<?php echo $s; ?>";
	var sts = "<?php echo $s; ?>";

	function exportToExcel(sts) {
		var tahun = $("#tahun").val();
		var tahunConfig = '<?php echo $appConfig['tahun_tagihan']; ?>';
		var buku = $("#buku").val();
		var checkall = new Array();
		$(".check-all").each(function(index) {
			if ($(this).is(":checked")) {
				checkall[index] = $(this).val();
			}
		});

		window.open("function/PBB/simulasi-ketetapan/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'10','uid':'$uid','srch':'$srch'}"); ?>&tahun=" + tahun + "&tahunConfig=" + tahunConfig + "&buku=" + buku + "&checkall=" + checkall);
	}

	function cetakMOU() {
		var params = {
			nop: '181301000700100510',
			uname: '<?php echo $a; ?>',
			appID: '<?php echo $a; ?>'
		};
		params = Base64.encode(Ext.encode(params));
		window.open('function/PBB/simulasi-ketetapan/sk/sk-print-mou.php?q=' + params, '_newtab');
	}

	function tableHtmlToExcel(tableID, filename = '') {
		var downloadLink;
		var dataType = 'application/vnd.ms-excel';
		var tableSelect = document.getElementById(tableID);
		var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

		filename = filename ? filename + '.xls' : 'excel_data.xls';

		downloadLink = document.createElement("a");

		document.body.appendChild(downloadLink);

		if (navigator.msSaveOrOpenBlob) {
			var blob = new Blob(['\ufeff', tableHTML], {
				type: dataType
			});
			navigator.msSaveOrOpenBlob(blob, filename);
		} else {
			downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
			downloadLink.download = filename;
			downloadLink.click();
		}
	}

	$(document).ready(function() {
		$("#all-check-button").click(function() {
			$('.check-all').each(function() {
				this.checked = $("#all-check-button").is(':checked');
			});
		});
		$(".tipclass").tooltip({
			track: false,
			delay: 0,
			showBody: " - ",
			bodyHandler: function() {
				var value = $(this)[0].tooltipText.replace(/\n/g, '<br />');
				return value;
			},
			fade: 250,
			extraClass: "fix",
			opacity: 0
		})

		/* $("#btn-cetak").click(function(){
			
			x=0;
			
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
				if($(this).is(":checked")){
					printToPDF($(this).val());
					x++;
				}
			});
			if(x==0){
				alert ("Belum ada data yang dipilih!");
			}
        });
		
		$("#btn-cetak2").click(function(){
			
			x=0;
			
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
				if($(this).is(":checked")){
					printToPDF2($(this).val());
					x++;
				}
			});
			if(x==0){
				alert ("Belum ada data yang dipilih!");
			}
        }); */

		/* $("#btn-cetak-lhp").click(function(){
			
			x=0;
			
            $("input:checkbox[name='check-all\\[\\]']").each(function(){
				if($(this).is(":checked")){
					printLHP($(this).val());
					x++;
				}
			});
			if(x==0){
				alert ("Belum ada data yang dipilih!");
			}
        }); */

		$("#simpannomor").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/pengurangan/nomor-input.php",
				data: "nspop=" + $("#nspop").val() + "&tanggalP=" + $("#tanggalP").val() + "&nomorP=" + $("#nomorP").val() + "&tanggalV=" + $("#tanggalV").val() + "&nomorV=" + $("#nomorV").val(),
				dataType: "json",
				success: function(data) {
					$("#contsetnomor2").hide();
					$("#contsetnomor1").hide();
					console.log(data.message)
					if (data.respon == true) alert("Input Data Sukses!");
					else alert('Input Data Gagal!');
					setTabs(sel, sts);
				},
				error: function(data) {
					console.log(data)
				}
			});
		});

		//Input No LHP
		$(".linkInputNomor").click(function() {
			//alert('test');
			$("#contsetnomor1").css("display", "block");
			$("#contsetnomor2").css("display", "block");
			var wp = $(this).attr("id");
			var v_wp = wp.split("+");

			$("#nspop").attr("value", v_wp[0]);
			$("#nomorV").attr("value", v_wp[1]);
			$("#tanggalV").attr("value", v_wp[2]);
		});
		$("#closednomor").click(function() {
			$("#contsetnomor2").css("display", "none");
			$("#contsetnomor1").css("display", "none");
		});

		//Input Keterangan
		$(".isiKeterangan").click(function() {
			//alert('test');
			$("#contentKeterangan1").css("display", "block");
			$("#contentKeterangan2").css("display", "block");
			var wp = $(this).attr("id");
			var v_wp = wp.split("+");

			$("#nspop").attr("value", v_wp[0]);
			$("#keterangan").attr("value", v_wp[1]);

		});
		$("#closedKeterangan").click(function() {
			$("#contentKeterangan1").css("display", "none");
			$("#contentKeterangan2").css("display", "none");
		});

		$("#simpannomorsk").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/pengurangan/nomor-input-sk.php",
				data: "nspop=" + $("#nspop").val() + "&tanggalSK=" + $("#tanggalSK").val() + "&nomorSK=" + $("#nomorSK").val() + "&nop=" + $("#nop").val() + "&tahun=" + $("#tahun").val() + "&C_HOST_PORT=<?php echo $appConfig['GW_DBHOST'] ?>&C_USER=<?php echo $appConfig['GW_DBUSER'] ?>&C_PWD=<?php echo $appConfig['GW_DBPWD'] ?>&C_DB=<?php echo $appConfig['GW_DBNAME'] ?>",
				dataType: "json",
				success: function(data) {
					$("#content2").hide();
					$("#content1").hide();
					console.log(data.message)
					if (data.respon == true) alert("Input Data Sukses!");
					else alert('Input Data Gagal!');
					setTabs(sel, sts);
				},
				error: function(data) {
					console.log(data)
				}
			});
		});

		$("#simpanketerangan").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/pengurangan/input-keterangan.php",
				data: "nspop=" + $("#nspop").val() + "&ket=" + $("#keterangan").val(),
				dataType: "json",
				success: function(data) {
					$("#contentKeterangan1").hide();
					$("#contentKeterangan2").hide();
					console.log(data.message)
					if (data.respon == true) alert("Input Data Sukses!");
					else alert('Input Data Gagal!');
					setTabs(sel, sts);
				},
				error: function(data) {
					console.log(data)
				}
			});
		});

		$(".inputNoSK").click(function() {

			var wp = $(this).attr("id");
			var v_wp = wp.split("+");

			$.ajax({
				type: "POST",
				url: "./function/PBB/pengurangan/svc-inq-pembayaran.php",
				data: "nspop=" + v_wp[0] + "&tanggalSK=" + v_wp[1] + "&nomorSK=" + v_wp[2] + "&nop=" + v_wp[3] + "&tahun=" + v_wp[4] + "&C_HOST_PORT=<?php echo $appConfig['GW_DBHOST'] ?>&C_USER=<?php echo $appConfig['GW_DBUSER'] ?>&C_PWD=<?php echo $appConfig['GW_DBPWD'] ?>&C_DB=<?php echo $appConfig['GW_DBNAME'] ?>",
				dataType: "json",
				success: function(data) {
					console.log(data.message)
					if (data.respon == '1') {
						//alert('true');
						$("#content3").css("display", "block");
						$("#content4").css("display", "block");
					} else if (data.respon == '0') {
						//alert('false');
						$("#content1").css("display", "block");
						$("#content2").css("display", "block");
					} else if (data.respon == '-1') {
						//alert('false');
						$("#content5").css("display", "block");
						$("#content6").css("display", "block");
					}
					$("#nspop").attr("value", v_wp[0]);
					$("#nomorSK").attr("value", v_wp[1]);
					$("#tanggalSK").attr("value", v_wp[2]);
					$("#nop").attr("value", v_wp[3]);
					$("#tahun").attr("value", v_wp[4]);
					setTabs(sel, sts);
				},
				error: function(data) {
					console.log(data)
				}
			});
			//console.log(v_wp[3])
		});
		$("#closednomorSK").click(function() {
			$("#content1").css("display", "none");
			$("#content2").css("display", "none");
		});
		$("#closednomorSK2").click(function() {
			$("#content3").css("display", "none");
			$("#content4").css("display", "none");
		});
		$("#closednomorSK3").click(function() {
			$("#content5").css("display", "none");
			$("#content6").css("display", "none");
		});

		/* //Input No SK
		$(".inputNoSKKosong").click(function(){
			//alert('test');
            $("#content1").css("display","block");
            $("#content2").css("display","block");
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
			
			$("#nspop").attr("value",v_wp[0]);
			$("#nomorSK").attr("value",v_wp[1]);
			$("#tanggalSK").attr("value",v_wp[2]);
			$("#nop").attr("value",v_wp[3]);
			$("#tahun").attr("value",v_wp[4]);
			//console.log(v_wp[3])
        }); */


		$("#tanggalP").datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$("#tanggalV").datepicker({
			dateFormat: 'yy-mm-dd'
		});

		$("#tanggalSK").datepicker({
			dateFormat: 'yy-mm-dd'
		});

		<?php
		if ($kec != '') {
			echo "showKel2(" . $kec . ");";
		}
		?>

	});

	function showKel(x) {

		var val = x.value;
		showKel2(val);
	}

	function showKel2(val) {
		var s = <?php echo $s ?>;
		<?php foreach ($aKecamatan as $row) { ?>
			if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
				document.getElementById('sKel' + s).innerHTML = "<?php
																					echo "<select name='kel' class='form-control' id='kel' onchange='filKel(" . $s . ",this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
																					foreach ($aKelurahan as $row2) {
																						if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
																							echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
																						}
																					}
																					echo "</select>"; ?>";
			}
		<?php } ?>
	}

	/* function printDataToPDF (d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'),d);
		var s = "";
		if (dt!="") {
			s = Ext.util.JSON.encode(dt);
		}
			console.log(s);
			printToPDF(s)
	}
	
	function printDataToPDF2 (d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'),d);
		var s = "";
		if (dt!="") {
			s = Ext.util.JSON.encode(dt);
		}
			console.log(s);
			printToPDF2(s)
	} */

	function printToPDF(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/pengurangan/sk-print-walkot.php?q=' + params, '_blank');
	}

	function printToPDF2(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/pengurangan/sk-print-kdpd.php?q=' + params, '_blank');
	}

	function printDataLHP(d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'), d);
		var s = "";
		if (dt != "") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printLHP(s)
	}

	function printLHP(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/pengurangan/lhp-print.php?q=' + params, '_blank');
	}

	function prevLHP(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/pengurangan/lhp-print-preview.php?q=' + params, '_blank');
	}

	function sendToPenetapan(nop, app, thn) {
		//alert(nop);
		$.ajax({
			type: "POST",
			url: "./function/PBB/pengurangan/send-to-penetapan.php",
			data: "nop=" + nop + "&app=" + app + "&thn=" + thn,
			success: function(msg) {
				alert(msg);
				console.log(msg)
				setTabs(sel);
			}
		});
	}
</script>
<?php
displayContent($s);
?>