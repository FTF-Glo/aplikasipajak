<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pbb-minimum', '', dirname(__FILE__))) . '/';
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
		echo "<div class=\"col-md-2\">
		<input type=\"text\" class=\"form-control\" name=\"pengurangan-val\" id=\"pengurangan-val\" placeholder=\"Input Nilai PBB Minimum\">
		</div>
		<div class=\"col-md-1\">
			<input type=\"submit\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Finalkan\" name=\"btn-finalize\" onclick=\"return confirm('Anda yakin akan melakukan perubahan PBB minimum?')\">
		</div>
		<div class=\"col-md-1\" style=\"margin-top: 7px\">Tahun</div>";
		echo "<select class=\"form-control\" name=\"tahun\" id=\"tahun\"  style=\"width:150px;display:inherit\" onchange=\"setTabs(" . $selected . "," . $selected . ")\">";
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
		echo "</select>";
	} else if ($selected == 33) {
		echo "<div class=\"col-md-1\">
			<input type=\"button\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\" onClick=\"return actionSendNotification('" . $SMSParam . "', '9');\">
		</div>";
		// echo "<input type=\"button\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\"/ onClick=\"sendSMS('".$SMSParam."', '9');\">\n";
	}
	/* if ($selected == 33){
		echo "<input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\">&nbsp&nbsp";
		echo "<input type=\"button\" value=\"PDF SK Kep. DPD\" name=\"btn-cetak2\" id=\"btn-cetak2\">&nbsp&nbsp";
		//echo "<input type=\"button\" value=\"Kirim ke Penetapan\" name=\"btn-kirim\" id=\"btn-kirim\">&nbsp&nbsp";
	} */
	/* if ($selected == 90){
		echo "<input type=\"button\" value=\"Cetak LHP\" name=\"btn-cetak-lhp\" id=\"btn-cetak-lhp\">&nbsp&nbsp";
	} */
	echo "</div>
	
	
	<p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
	<button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapsePengurangan-{$selected}\" aria-expanded=\"false\" aria-controls=\"collapsePengurangan-{$selected}\">
   Filter Data
	</button>
</p>

<div class=\"collapse\" id=\"collapsePengurangan-{$selected}\">
	<div class=\"card card-body\">
		<div class=\"row\" style=\"margin-left:5px; margin-top:10px\">
	
			<div class=\"form-group col-md-3\">
				<label> Nomor/Nama/NOP </label>
				<div style=\"display: flex; align-items: center;\">
				<input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" placeholder=\"Nomor/Nama/NOP\" value=\"" . $srch . "\" style=\"flex-grow: 1; margin-right: 10px;\"/>

				<input type=\"button\" class=\"btn btn-info\" style=\"border-radius: 0; margin-top: 0px;\" onclick=\"setTabs(" . $selected . "," . $selected . ")\" value=\"Cari\" id=\"btn-src\"/>
				</div>
			</div>
			

			<div class=\"form-group col-md-3\">
				<label>Kecamatan</label>
					<select class=\"form-control\" name=\"kec\" id=\"kec\" onchange=\"showKel(this)\">
						<option value=\"\">Kecamatan</option>";
						foreach ($aKecamatan as $row)
						echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
					echo "</select>
			</div>
		
			<div class=\"form-group col-md-3\">
				<label>Kelurahan</label>
				<div  id=\"sKel" . $selected . "\">
				<select class=\"form-control\" name=\"kel\" id=\"kel\" onchange=\"filKel(" . $selected . ",this)\">
					<option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>
				</select>
				</div>
			</div>

			<div class=\"form-group col-md-3\">
				<label> Buku </label>
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
	</div>
</div>
	
	
	
	
	<div class=\"row\">
		<div class=\"col-md-12\">
			<div class=\"table-responsive\">
				<table class=\"table table-hover\" " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . ">
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
		"\t\t<td class=\"tdheader\"> NOP </td> \n
		\t\t<td class=\"tdheader\"> Nama </td> \n
		\t\t<td class=\"tdheader\"> Alamat Wajib Pajak </td> \n
		\t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n
		\t\t<td class=\"tdheader\"> NJKP </td> \n
		\t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n
		\t\t<td class=\"tdheader\">Tahun Pajak</td> \n";

	$hTolak 	 	= "\t\t<td class=\"tdheader\">Ditolak di</td> \n
					   \t\t<td class=\"tdheader\">Alasan</td> \n";

	$hVerifikasi 	= "\t\t<td class=\"tdheader\">Dalam Proses di</td> \n";

	$hPersetujuan	= "\t\t<td class=\"tdheader\" align=\"center\">Tanggal di setujui</td> \n
					   \t\t<td class=\"tdheader\">Nomor LHP</td> \n";

	$hSK			= "\t\t<td class=\"tdheader\">No SK</td> \n
					   \t\t<td class=\"tdheader\">Keterangan</td> \n";

	$hCetakSK		= "\t\t<td class=\"tdheader\">Cetak SK</td> \n";

	$hCetakLHP		= "\t\t<td class=\"tdheader\">Cetak LHP</td> \n";

	$header = $hBasic;

	switch ($selected) {
		case 10:
		case 20:
		case 30:
			break;
		case 11:
			$header .= $hVerifikasi;
			break;
		case 12:
		case 22:
		case 32:
			$header .= $hTolak;
			break;
		case 33:
			$header .= $hPersetujuan . $hSK . $hCetakSK;
			break;
		case 90:
			$header .= $hCetakLHP;
			break;
	}

	return $header;
}

function printData($selected)
{
	global $isSusulan;

	$HTML = "";
	$aData = getData($selected);

	$i = 0;
	if ($aData != null && !empty($aData) && count($aData) > 0)
		foreach ($aData as $data) {
			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			if ($selected != 26) {
				$HTML .= "\t<tr>\n";
			}
			if ($selected == 10 || $selected == 42) {
				//$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . (isset($data['CPM_TRAN_ID']) ? $data['CPM_TRAN_ID'] : '') . "\" /></td>\n";
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['NOP'] . "\" /></td>\n";
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
				$HTML .= parseData($data, $selected, $class);

				$HTML .= "\t</tr>\n";
			}
			$i++;
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

	$data = $dbGwCurrent->get70s($filter, $srch, $qBuku, $jumhal, $perpage, $page, $tahun, $appConfig);
	$totalrows = $dbGwCurrent->totalrows;
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

function parseData($data, $selected, $class)
{
	global $arConfig, $appConfig, $a, $m, $params;

	/*$numLHP		 = getDataReduce('CPM_RE_LHP_NUMBER', $data['CPM_ID']);
	$dateLHP	 = getDataReduce('CPM_RE_LHP_DATE', $data['CPM_ID']);
	$numSK		 = getDataReduce('CPM_RE_SK_NUMBER', $data['CPM_ID']);
	$dateSK	 	 = getDataReduce('CPM_RE_SK_DATE', $data['CPM_ID']);
	$keterangan	 = getDataReduce('CPM_RE_NOTICE', $data['CPM_ID']);
	#Jika belum ada No dan Tanggal LHP
	if ((!$numLHP) && (!$dateLHP)) {
		$numLHP		= getDataLHP('CPM_LHP_NO', $data['CPM_ID']);
		$dateLHP	= getDataLHP('CPM_LHP_DATE', $data['CPM_ID']);
	}

	$status = array(
		1 => "Pendataan",
		2 => "Verifikasi",
		3 => "Persetujuan",
		4 => "Selesai",
		5 => "Verifikasi",
		6 => "Persetujuan"
	);

	$dBasic =
		"\t\t<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params . "&svcid=" . $data['CPM_ID']) . "'>" . $data['CPM_ID'] . "</a> </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td> \n
		 \t\t<td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . " </td> \n
		 \t\t<td class=\"$class\"> " . kelShow($data['CPM_OP_KELURAHAN']) . " </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_OP_NUMBER'] . " </td> \n
		 \t\t<td class=\"$class\" align=\"center\"> " . substr($data['CPM_DATE_RECEIVE'], 8, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 5, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 0, 4) . " </td> \n";

	$dTolak 	 	= "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n  
						   \t\t<td class=\"$class\"> " . $data['CPM_REFUSAL_REASON'] . " </td> \n";
	$dVerifikasi 	= "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n";
	#Old
	/* $dPersetujuan	= "\t\t<td class=\"$class\"> " . $data['CPM_DATE_APPROVER'] . " </td> \n
						   \t\t<td class=\"$class linkInputNomor\" align=\"center\" id=\"".$data['CPM_ID']."+". $numLHP ."+". $dateLHP ."\">Isi Nomor</td> \n"; */
	#New 
	/*$dPersetujuan	= "\t\t<td class=\"$class\" align=\"center\"> " . $data['CPM_DATE_APPROVER'] . " </td> \n ";

	if ($numLHP) {
		$dLHP = "\t\t<td class=\"$class linkInputNomor\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numLHP . "+" . $dateLHP . "\">" . $numLHP . " </td> \n";
	} else {
		$dLHP = "\t\t<td class=\"$class linkInputNomor\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numLHP . "+" . $dateLHP . "\">Isi Nomor</td> \n";
	}

	if ($numSK) {
		$dSK = "\t\t<td class=\"$class inputNoSK\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numSK . "+" . $dateSK . "+" . $data['CPM_OP_NUMBER'] . "+" . $data['CPM_SPPT_YEAR'] . "\">" . $numSK . " </td> \n";
	} else {
		$dSK = "\t\t<td class=\"$class inputNoSK\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numSK . "+" . $dateSK . "+" . $data['CPM_OP_NUMBER'] . "+" . $data['CPM_SPPT_YEAR'] . "\">Isi Nomor</td> \n";
	}

	if ($keterangan) $dSK .= "\t\t<td class=\"$class isiKeterangan\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $keterangan . "\">" . $keterangan . "</td> \n";
	else $dSK .= "\t\t<td class=\"$class isiKeterangan\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $keterangan . "\">Isi</td> \n";
	//$dSK = "\t\t<td class=\"$class inputNoSK\" align=\"center\" id=\"".$data['CPM_ID']."+". $numSK ."+". $dateSK ."+".$data['CPM_OP_NUMBER']."+".$data['CPM_SPPT_YEAR']."\">Isi Nomor</td> \n";

	//$dLHP			= "\t\t<td class=\"$class\"> " . $data['CPM_DATE_APPROVER'] . " </td> \n";

	/* $LHPNum			= getDataReduce('CPM_RE_LHP_NUMBER',$data['CPM_ID']);
		$LHPDate		= getDataReduce('CPM_RE_LHP_DATE',$data['CPM_ID']);
		$persenApp		= getDataReduce('CPM_RE_PERCENT_APPROVE',$data['CPM_ID']);	
		
		$STP			= $data['CPM_STP'];
		
		#Jika belum ada No dan tanggal LHP
		if((!$LHPNum) && (!$LHPDate)){
			$dSK = "\t\t<td class=\"$class\" align=\"center\"> Belum ada SK </td> \n";	
		} 
		#Jika STP = 0 atau persen yang disetujui = 0
		else if(($STP==1) || ($persenApp==0)) {
			$dSK = "\t\t<td class=\"$class\" align=\"center\"> Selesai </td> \n";
		}	
		#Jika STP selain dari 1
		else if($STP!=1) {
			$dSK = "\t\t<td class=\"$class kirim\" align=\"center\"><input type=\"button\" name=\"kirim\" id=\"kirim\" value=\"Kirim\" onclick=\"sendToPenetapan('".$data['CPM_OP_NUMBER']."','".$a."','".$data['CPM_SPPT_YEAR']."')\"></td> \n";
		} */


	/*$pbbTerutang = $data['CPM_SPPT_DUE'];
	$maxValue	 = isset($arConfig['maks_np_kdpd']) ? $arConfig['maks_np_kdpd'] : null;
	#Jika PBB yang terutang lebih dari 1,5 Milyar Cetak SK Walikota
	if ($pbbTerutang > $maxValue) {
		$dCetakSK = "\t\t<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\" onclick=\"printToPDF('" . $data['CPM_ID'] . "')\"></td>\n";
	} else {
		$dCetakSK = "\t\t<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"PDF SK Kadin\" name=\"btn-cetak2\" id=\"btn-cetak2\" onclick=\"printToPDF2('" . $data['CPM_ID'] . "')\"></td>\n";
	}

	$dCetakLHP = "<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"Pratinjau LHP\" name=\"btn-prev-lhp\" id=\"btn-prev-lhp\" onclick=\"prevLHP('" . $data['CPM_ID'] . "')\"></td>\n";
	if ($selected == '90') {
		if (getDataLHP('CPM_LHP_APPROVAL_STATUS', $data['CPM_ID']) == '1') {
			$dCetakLHP = "\t\t<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"Cetak LHP\" name=\"btn-cetak-lhp\" id=\"btn-cetak-lhp\" onclick=\"printLHP('" . $data['CPM_ID'] . "')\"></td>\n";
		} else if (getDataLHP('CPM_LHP_SID', $data['CPM_ID']) == '') {
			$dCetakLHP = "\t\t<td class=\"$class\" align=\"center\"></td>\n";
		}
	}*/


	/*$parse = $dBasic;

	switch ($selected) {
		case 10:
		case 20:
		case 30:
			break;
		case 11:
			$parse .= $dVerifikasi;
			break;
		case 12:
		case 22:
		case 32:
			$parse .= $dTolak;
			break;
		case 33:
			$parse .= $dPersetujuan . $dLHP . $dSK . $dCetakSK;
			break;
		case 90:
			$parse .= $dCetakLHP;
			break;
	}*/
	$parse = '';
	$parse .= "\t\t<td class=\"$class\"> " . $data['NOP'] . "</td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . $data['WP_NAMA'] . "</td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . $data['WP_ALAMAT'] . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . $data['OP_ALAMAT'] . " </td> \n";
	$parse .= "\t\t<td class=\"$class\" align=\"right\"> " . number_format($data['OP_NJKP'], 0, ',', '.') . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . $data['OP_KECAMATAN'] . " - " . $data['OP_KELURAHAN'] . " </td> \n";
	$parse .= "\t\t<td class=\"$class\"> " . $data['SPPT_TAHUN_PAJAK'] . " </td> \n";
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
	$idForm = $arConfig['id_view_spop'];

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