<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';
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
//echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";
echo "<script type=\"text/javascript\" src=\"function/PBB/consol/script.js\"></script>";

$appConfig['minimum_njoptkp'] = (!isset($appConfig['minimum_njoptkp']) || $appConfig['minimum_njoptkp'] == null) ? 10000000 : $appConfig['minimum_njoptkp'];
$appConfig['minimum_sppt_pbb_terhutang'] = (!isset($appConfig['minimum_sppt_pbb_terhutang']) || $appConfig['minimum_sppt_pbb_terhutang'] == null) ? 25000 : $appConfig['minimum_sppt_pbb_terhutang'];

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
	global $isSusulan, $kec, $kel, $jumhal, $srch, $aKecamatan, $a, $appConfig;

	$url 		= $appConfig['SMS_URL'];
	$user 		= $appConfig['SMS_USERID'];
	$pass 		= $appConfig['SMS_USERPWD'];
	$SMSParam = base64_encode("" . $url . "&" . $user . "&" . $pass);

	echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . $kec . "\">";
	echo "<div class=\"row\">";
	//echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
	//echo "\t<table border=0 " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . "><tr><td>";
	//echo "\t<table border=0 width=\"100%\"><tr><td>";
	if ($selected == 10) {
		echo "<div class=\"col-md-2\">
		<button type=\"submit\" class=\"btn btn-primary btn-orange\" value=\"Finalkan\" name=\"btn-finalize\"/ onClick=\"return confirm('Anda yakin akan memfinalisasi data ini? Data akan langsung terkirim ke verifikasi')\">Finalkan</button>
		</div>";
	} else if ($selected == 33) {
		// echo "<input type=\"button\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\"/ onClick=\"return actionSendNotification('".$PenilaianParam."', '10', '".$a."');\">\n";
		echo "<div class=\"col-md-2\">
		<button value=\"Kirim Notifikasi\" class=\"btn btn-primary btn-orange\" name=\"btn-kirim-notifikasi\"/ onClick=\"return actionSendNotification('" . $SMSParam . "', '10');\">Kirim Notifikasi</button>
		</div>";
	}
	/* if ($selected == 33){
		echo "<input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\">&nbsp&nbsp";
		echo "<input type=\"button\" value=\"PDF SK Kep. DPD\" name=\"btn-cetak2\" id=\"btn-cetak2\">&nbsp&nbsp";
	} */

	echo "
		<p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
            <button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapsekeberatan-{$selected}\" aria-expanded=\"false\" aria-controls=\"collapsekeberatan-{$selected}\">
           Filter Data
            </button>
        </p>
        <div class=\"collapse\" id=\"collapsekeberatan-{$selected}\">
            <div class=\"card card-body\">
				<div class=\"row\" style=\"margin-left:5px; margin-top:10px\">
			
					<div class=\"form-group col-md-3\">
						<label> Nomor/Nama/NOP </label>
						<div style=\"display: flex; align-items: center;\">
						<input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" placeholder=\"Nomor/Nama/NOP\" style=\"flex-grow: 1; margin-right: 10px;\"/>
						<button onclick=\"setTabs(" . $selected . "," . $selected . ")\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-info\">Cari</button>
						
						</div>
					</div>

					<div class=\"form-group col-md-3\">
						<label>Kecamatan</label>
							<select name=\"kec\" id=\"kec\" class=\"form-control\" onchange=\"showKel(this)\">
								<option value=\"\">Kecamatan</option>";
								foreach ($aKecamatan as $row) {
									echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
								}
							
							echo " </select>
					</div>
				
					<div class=\"form-group col-md-3\">
						<label>Kelurahan</label>
						<div  id=\"sKel" . $selected . "\">
							<select name=\"kel\" class=\"form-control\" id=\"kel\" onchange=\"filKel(" . $selected . ",this)\">
								<option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>
							</select>
						</div>
					</div>
					
				</div>
        	</div>
		</div>
		
	<div class=\"row\" style=\"margin-top: 20px;\">
		<div class=\"col-md-12\">
			<div class=\"table-responsive\">
				<table class=\"table table-bordered\" " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . ">
					<tr>";
	if ($selected == 10 || $selected == 33) {
		echo "<td class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>";
	} else {
		echo "<td class=\"tdheader\">&nbsp;</td>";
	}

	echo createHeader($selected);
	echo "</tr>";
	echo printData($selected);
	echo "		</table>
				</div>
			</div>
			<div class=\"col-md-12\">
				<div style=\"float:right\">" . paging() . "</div>
			</div>
		</div>
	</form>";
}

function createHeader($selected)
{
	global $appConfig;

	//variable header set
	$hBasic =
		"\t\t<td class=\"tdheader\"> Nomor </td> \n
		 \t\t<td class=\"tdheader\"> Nama WP </td> \n
		 \t\t<td class=\"tdheader\"> Kecamatan </td> \n
		 \t\t<td class=\"tdheader\"> " . $appConfig['LABEL_KELURAHAN'] . " </td> \n
		 \t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Tanggal Terima </td> \n";

	$hTolak 	 	= "\t\t<td class=\"tdheader\">Ditolak di</td> \n
					   \t\t<td class=\"tdheader\">Alasan</td> \n";

	$hVerifikasi 	= "\t\t<td class=\"tdheader\">Dalam Proses di</td> \n";

	$hPersetujuan	= "\t\t<td class=\"tdheader\">Tanggal di setujui</td> \n
					   \t\t<td class=\"tdheader\">Nomor LHP</td> \n";

	$hSK			= "\t\t<td class=\"tdheader\">Nomor SK</td> \n
					  \t\t<td class=\"tdheader\">Keterangan</td> \n";

	$hCetakSK		= "\t\t<td class=\"tdheader\">Cetak SK</td> \n";

	$hCetakLHP		= "\t\t<td class=\"tdheader\">Cetak LHP</td> \n";

	$header = $hBasic;

	switch ($selected) {
		case 10:
		case 20:
		case 30:
		case 40:
			break;
		case 11:
			$header .= $hVerifikasi;
			break;
		case 12:
		case 22:
		case 32:
		case 42:
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
	if ($aData != null && count($aData) > 0)
		foreach ($aData as $data) {

			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			$HTML .= "\t<tr>\n";
			if (($selected == 10) || ($selected == 33)) {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_ID'] . "\" /></td>\n";
			} else {
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
			}
			$HTML .= parseData($data, $selected, $class);
			$HTML .= "\t</tr>\n";
			$i++;
		}
	return $HTML;
}

function getData($selected)
{
	global $dbServices, $srch, $arConfig, $appConfig, $data, $kec, $custom, $jumhal, $totalrows, $perpage, $page, $kel;

	$filter = array();
	//Seleksi Status dan jenis Berkas
	$filter['CPM_TYPE'][] = 10;

	//Penerimaan Keberatan
	if ($selected == 10) {
		$filter['CPM_STATUS'][] = 1;
	} else if ($selected == 11) {
		$filter['CPM_STATUS'][] = 2;
		$filter['CPM_STATUS'][] = 3;
		$filter['CPM_STATUS'][] = 7;
	} else if ($selected == 12) {
		$filter['CPM_STATUS'][] = 5;
		$filter['CPM_STATUS'][] = 6;
		$filter['CPM_STATUS'][] = 8;
		//----------------------
		//Verifikasi Keberatan
	} else if ($selected == 20) {
		$filter['CPM_STATUS'][] = 2;
	} else if ($selected == 22) {
		$filter['CPM_STATUS'][] = 5;
		$filter['CPM_STATUS'][] = 6;
		//----------------------
		//Rekomendasi Keberatan
	} else if ($selected == 40) {
		$filter['CPM_STATUS'][] = 7;
	} else if ($selected == 42) {
		$filter['CPM_STATUS'][] = 8;
		//----------------------
		//Persetujuan Keberatan
	} else if ($selected == 30) {
		$filter['CPM_STATUS'][] = 3;
	} else if ($selected == 32) {
		$filter['CPM_STATUS'][] = 6;
	} else if ($selected == 33) {
		$filter['CPM_STATUS'][] = 4;
	} else if ($selected == 90) {
		$filter['CPM_STATUS'][] = 2;
		$filter['CPM_STATUS'][] = 3;
		$filter['CPM_STATUS'][] = 4;
	}
	//-----------------------
	$perpage = $appConfig['ITEM_PER_PAGE'];
	if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
	$data = $dbServices->get($filter, $srch, $jumhal, $perpage, $page);
	$totalrows = $dbServices->totalrows;
	return $data;
}

function getDataObjection($field, $id)
{
	global $dbServices;
	return $dbServices->getObjection($field, $id);
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

	$numLHP		 = getDataObjection('CPM_OB_LHP_NUMBER', $data['CPM_ID']);
	$dateLHP	 = getDataObjection('CPM_OB_LHP_DATE', $data['CPM_ID']);
	$numSK		 = getDataObjection('CPM_OB_SK_NUMBER', $data['CPM_ID']);
	$dateSK	 	 = getDataObjection('CPM_OB_SK_DATE', $data['CPM_ID']);
	$keterangan	 = getDataObjection('CPM_OB_NOTICE', $data['CPM_ID']);
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
		6 => "Persetujuan",
		7 => "Rekomendasi",
		8 => "Rekomendasi"
	);

	$dBasic =
		"\t\t<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params . "&nop=" . $data['CPM_OP_NUMBER'] . "&svcid=" . $data['CPM_ID']) . "'>" . $data['CPM_ID'] . "</a> </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td> \n
		 \t\t<td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . " </td> \n
		 \t\t<td class=\"$class\"> " . kelShow($data['CPM_OP_KELURAHAN']) . " </td> \n
		 \t\t<td class=\"$class\" align=\"center\"> " . $data['CPM_OP_NUMBER'] . " </td> \n
		 \t\t<td class=\"$class\" align=\"center\"> " . substr($data['CPM_DATE_RECEIVE'], 8, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 5, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 0, 4) . " </td> \n";

	$dTolak 	 	= "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n  
						   \t\t<td class=\"$class\"> " . $data['CPM_REFUSAL_REASON'] . " </td> \n";
	$dVerifikasi 	= "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n";
	$dPersetujuan	= "\t\t<td class=\"$class\" align=\"center\"> " . $data['CPM_DATE_APPROVER'] . " </td> \n";

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
	//$LHPNum			= getDataObjection('CPM_OB_LHP_NUMBER',$data['CPM_ID']);
	//$LHPDate		= getDataObjection('CPM_OB_LHP_DATE',$data['CPM_ID']);
	//$persenApp		= getDataReduce('CPM_RE_PERCENT_APPROVE',$data['CPM_ID']);

	/* $STP			= $data['CPM_STP'];
		
		#Jika belum ada No dan tanggal LHP
		if((!$LHPNum) && (!$LHPDate)){
			$dSK = "\t\t<td class=\"$class\" align=\"center\"> Belum ada SK </td> \n";	
		} 
		else if($STP==1) {
			$dSK = "\t\t<td class=\"$class\" align=\"center\"> Selesai </td> \n";
		}			
		else if($STP==0) {
			$dSK = "\t\t<td class=\"$class kirim\" align=\"center\"><input type=\"button\" name=\"kirim\" id=\"kirim\" value=\"Kirim\" onclick=\"sendToPenetapan('".$data['CPM_OP_NUMBER']."','".$a."','".$data['CPM_SPPT_YEAR']."')\"></td> \n";
		} */

	$pbbTerutang = $data['CPM_SPPT_DUE'];
	$maxValue	 = isset($arConfig['maks_np_kdpd']) ? $arConfig['maks_np_kdpd'] : '';
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
	}
	$parse = $dBasic;

	switch ($selected) {
		case 10:
		case 20:
		case 30:
		case 40:
			break;
		case 11:
			$parse .= $dVerifikasi;
			break;
		case 12:
		case 22:
		case 32:
		case 42:
			$parse .= $dTolak;
			break;
		case 33:
			$parse .= $dPersetujuan . $dLHP . $dSK . $dCetakSK;
			break;
		case 90:
			$parse .= $dCetakLHP;
			break;
	}
	return $parse;
}
function paging()
{
	global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage, $totalrows;

	//$params = "a=".$a."&m=".$m;

	$html = "<div style=\"font-weight: bold;\">";
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
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$jumhal = @isset($_REQUEST['jumhal']) ? $_REQUEST['jumhal'] : "";
$kec 	= @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'], 0, 7) : "";

$q = base64_decode($q);
$q = $json->decode($q);
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

//set new page
if (isset($_SESSION['stSPOP'])) {
	if ($_SESSION['stSPOP'] != $s) {
		$_SESSION['stSPOP'] = $s;
		$kec = "";
		$kel = "";
		$srch = "";
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
	$idForm = $arConfig['idkeberatan-form'];
$params = "a=$a&m=$m&f=" . $idForm;
if (($s == 10) || ($s == 20)) {
	$params .= "&dis=0&tab=$s";
} else {
	$params .= "&dis=0&tab=$s";
}
//echo $params;

$uid = $data->uid;
//$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);
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

		$("#simpannomor").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/keberatan/nomor-input.php",
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

		$("#simpanketerangan").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/keberatan/input-keterangan.php",
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

		$("#simpannomorsk").unbind('click').click(function() {
			$.ajax({
				type: "POST",
				url: "./function/PBB/keberatan/nomor-input-sk.php",
				data: "spop=" + $("#spop").val() + "&tanggalSK=" + $("#tanggalSK").val() + "&nomorSK=" + $("#nomorSK").val() + "&nop=" + $("#nop").val() + "&tahun=" + $("#tahun").val() + "&C_HOST_PORT=<?php echo $appConfig['GW_DBHOST'] ?>&C_USER=<?php echo $appConfig['GW_DBUSER'] ?>&C_PWD=<?php echo $appConfig['GW_DBPWD'] ?>&C_DB=<?php echo $appConfig['GW_DBNAME'] ?>&minimum_njoptkp=<?php echo $appConfig['minimum_njoptkp'] ?>&minimum_sppt_pbb_terhutang=<?php echo $appConfig['minimum_sppt_pbb_terhutang'] ?>",
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

		$(".inputNoSK").click(function() {

			var wp = $(this).attr("id");
			var v_wp = wp.split("+");

			//                $.ajax({
			//                    type: "POST",
			//                    url: "./function/PBB/keberatan/svc-inq-pembayaran.php",
			//                    data: "spop="+v_wp[0]+"&tanggalSK="+v_wp[1]+"&nomorSK="+v_wp[2]+"&nop="+v_wp[3]+"&tahun="+v_wp[4]+"&C_HOST_PORT=<?php echo $appConfig['GW_DBHOST'] ?>&C_USER=<?php echo $appConfig['GW_DBUSER'] ?>&C_PWD=<?php echo $appConfig['GW_DBPWD'] ?>&C_DB=<?php echo $appConfig['GW_DBNAME'] ?>",
			//                    dataType : "json",
			//                    success: function(data){
			//                            console.log(data.message)
			//                            if(data.respon=='1'){
			//                                //alert('true');
			//                                $("#content3").css("display","block");
			//                                $("#content4").css("display","block");
			//                            } else if(data.respon=='0'){
			//                                //alert('false');
			//                                $("#content1").css("display","block");
			//                                $("#content2").css("display","block");
			//                            } else if(data.respon=='-1'){
			//                                //alert('false');
			//                                $("#content5").css("display","block");
			//                                $("#content6").css("display","block");
			//                            }  
			//                            $("#spop").attr("value",v_wp[0]);
			//                            $("#nomorSK").attr("value",v_wp[1]);
			//                            $("#tanggalSK").attr("value",v_wp[2]);
			//                            $("#nop").attr("value",v_wp[3]);
			//                            $("#tahun").attr("value",v_wp[4]);
			//                            setTabs(sel,sts);
			//                    },
			//                    error : function(data){
			//                            console.log(data)
			//                    }
			//                });

			$("#content1").css("display", "block");
			$("#content2").css("display", "block");
			$("#spop").attr("value", v_wp[0]);
			$("#nomorSK").attr("value", v_wp[1]);
			$("#tanggalSK").attr("value", v_wp[2]);
			$("#nop").attr("value", v_wp[3]);
			$("#tahun").attr("value", v_wp[4]);
			//                setTabs(sel,sts);
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

		//Input No SK
		/* $(".inputNoSK").click(function(){
			//alert('test');
            $("#content1").css("display","block");
            $("#content2").css("display","block");
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
			
			$("#spop").attr("value",v_wp[0]);
			$("#nomorSK").attr("value",v_wp[1]);
			$("#tanggalSK").attr("value",v_wp[2]);
			$("#nop").attr("value",v_wp[3]);
			$("#tahun").attr("value",v_wp[4]);
			console.log(v_wp[0])
        });
        $("#closednomorSK").click(function(){
            $("#content2").css("display","none");
            $("#content1").css("display","none");
        }); */

		$("#tanggalSK").datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$("#tanggalP").datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$("#tanggalV").datepicker({
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
																					echo "<select name='kel' id='kel' class='form-control' onchange='filKel(" . $s . ",this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
																					foreach ($aKelurahan as $row2) {
																						if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
																							echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
																						}
																					}
																					echo "</select>"; ?>";
			}
		<?php } ?>
	}

	function printDataToPDF(d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'), d);
		var s = "";
		if (dt != "") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printToPDF(s)
	}

	function printToPDF(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/keberatan/sk-print-walkot.php?q=' + params, '_newtab');
	}

	function printDataToPD2F(d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'), d);
		var s = "";
		if (dt != "") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printToPDF2(s)
	}

	function printToPDF2(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/keberatan/sk-print-kdpd.php?q=' + params, '_newtab');
	}

	function sendToPenetapan(nop, app, thn) {
		//alert(nop);
		$.ajax({
			type: "POST",
			url: "./function/PBB/keberatan/send-to-penetapan.php",
			data: "nop=" + nop + "&app=" + app + "&thn=" + thn,
			success: function(msg) {
				alert(msg);
				console.log(msg)
				setTabs(sel);
			}
		});
	}

	function prevLHP(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/keberatan/lhp-print-preview.php?q=' + params, '_blank');
	}

	function printLHP(id) {
		var params = {
			svcId: id,
			appId: '<?php echo $a; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/keberatan/lhp-print.php?q=' + params, '_blank');
	}
</script>

<?php
displayContent($s);
?>