<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
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
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
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
	global $isSusulan, $kec, $jumhal, $srch, $aKecamatan, $a, $appConfig, $buku, $kel;

	$url 		= $appConfig['SMS_URL'];
	$user 		= $appConfig['SMS_USERID'];
	$pass 		= $appConfig['SMS_USERPWD'];

	$SMSParam = base64_encode("" . $url . "&" . $user . "&" . $pass);
	//echo $selected;
	echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . $kec . "\">";
	//echo "<div class=\"ui-widget consol-main-content\">";
	//echo "<div class=\"ui-widget-content consol-main-content-inner\" " . (($selected == 33) ? "style=\"overflow-y:hidden; overflow-x:auto;\"" : "") . ">";
	//echo "<table border=0 " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . "><tr><td>";
	echo "<div class=\"row\" style=\"margin-bottom: 20px\">";
	if ($selected == 10) {
		echo "<div class=\"col-md-1\">
			<input type=\"submit\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Finalkan\" name=\"btn-finalize\" onClick=\"return confirm('Anda yakin akan memfinalisasi data ini? Data akan langsung terkirim ke verifikasi')\">
		</div>";
	} else if ($selected == 33) {
		echo "<div class=\"col-md-1\">
			<input type=\"button\" class=\"btn btn-primary bg-maka\" style=\"border-radius: 0; margin-top: 7px;\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\" onClick=\"return actionSendNotification('" . $SMSParam . "', '9');\">
		</div>";
		// echo "<input type=\"button\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\"/ onClick=\"sendSMS('".$SMSParam."', '9');\">";
	}
	/* if ($selected == 33){
		echo "<input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\">&nbsp&nbsp";
		echo "<input type=\"button\" value=\"PDF SK Kep. DPD\" name=\"btn-cetak2\" id=\"btn-cetak2\">&nbsp&nbsp";
		//echo "<input type=\"button\" value=\"Kirim ke Penetapan\" name=\"btn-kirim\" id=\"btn-kirim\">&nbsp&nbsp";
	} */
	/* if ($selected == 90){
		echo "<input type=\"button\" value=\"Cetak LHP\" name=\"btn-cetak-lhp\" id=\"btn-cetak-lhp\">&nbsp&nbsp";
	} */
	echo "
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
				<input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" placeholder=\"Nomor/Nama/NOP\" style=\"flex-grow: 1; margin-right: 10px;\"/>
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
</div>";

	
	// <div class=\"col-md-1\">Masukan Kata Kunci Pencarian</div>
	// <div class=\"col-md-2\">
	// 	<input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" placeholder=\"Nomor/Nama/NOP\" size=\"60\"/>
	// </div>
	// <div class=\"col-md-1\">
	// 	<input type=\"button\" class=\"btn btn-primary btn-orange\" style=\"border-radius: 0; margin-top: 7px;\" onclick=\"setTabs(" . $selected . "," . $selected . ")\" value=\"Cari\" id=\"btn-src\"/>
	// </div>";

	// echo " <div class=\"col-md-1\" style=\"margin-top: 10px; text-align: right;\">Filter</div>
	// <div class=\"col-md-2\">
	// 	<select class=\"form-control\" name=\"kec\" id=\"kec\" onchange=\"showKel(this)\">";
	// echo "<option value=\"\">Kecamatan</option>";
	// foreach ($aKecamatan as $row)
	// 	echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
	// echo "</select>
	// </div>
	// <div class=\"col-md-2\">";
	// echo "<div id=\"sKel" . $selected . "\" style=\"margin-left:5px; display:inline-block; width: 100%;\" >";
	// echo "    <select class=\"form-control\" name=\"kel\" id=\"kel\" onchange=\"filKel(" . $selected . ",this)\">";
	// echo "        <option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>";
	// echo "    </select>";
	// echo "</div>
	// </div>
	// <div class=\"col-md-2\">
	// 	<select id=\"buku\" name=\"buku\" class=\"form-control\" onchange=\"filBook(" . $selected . ",this)\">
	// 		<option value=\"0\" " . ((isset($buku) && $buku == "0") ? "selected" : "") . ">Pilih Semua</option>
	// 		<option value=\"1\" " . ((isset($buku) && $buku == "1") ? "selected" : "") . ">Buku 1</option>
	// 		<option value=\"12\" " . ((isset($buku) && $buku == "12") ? "selected" : "") . ">Buku 1,2</option>
	// 		<option value=\"123\" " . ((isset($buku) && $buku == "123") ? "selected" : "") . ">Buku 1,2,3</option>
	// 		<option value=\"1234\" " . ((isset($buku) && $buku == "1234") ? "selected" : "") . ">Buku 1,2,3,4</option>
	// 		<option value=\"12345\" " . ((isset($buku) && $buku == "12345") ? "selected" : "") . ">Buku 1,2,3,4,5</option>
	// 		<option value=\"2\" " . ((isset($buku) && $buku == "2") ? "selected" : "") . ">Buku 2</option>
	// 		<option value=\"23\" " . ((isset($buku) && $buku == "23") ? "selected" : "") . ">Buku 2,3</option>
	// 		<option value=\"234\" " . ((isset($buku) && $buku == "234") ? "selected" : "") . ">Buku 2,3,4</option>
	// 		<option value=\"2345\" " . ((isset($buku) && $buku == "2345") ? "selected" : "") . ">Buku 2,3,4,5</option>
	// 		<option value=\"3\" " . ((isset($buku) && $buku == "3") ? "selected" : "") . ">Buku 3</option>
	// 		<option value=\"34\" " . ((isset($buku) && $buku == "34") ? "selected" : "") . ">Buku 3,4</option>
	// 		<option value=\"345\" " . ((isset($buku) && $buku == "345") ? "selected" : "") . ">Buku 3,4,5</option>
	// 		<option value=\"4\" " . ((isset($buku) && $buku == "4") ? "selected" : "") . ">Buku 4</option>
	// 		<option value=\"45\" " . ((isset($buku) && $buku == "45") ? "selected" : "") . ">Buku 4,5</option>
	// 		<option value=\"5\" " . ((isset($buku) && $buku == "5") ? "selected" : "") . ">Buku 5</option>
	// 	</select>
	// </div>
	// </div>
	echo "	<div class=\"row\">
		<div class=\"col-md-12\">
			<div class=\"table-responsive\">
				<table class=\"table table-hover\" " . (($selected == 33) ? "width=\"1500\"" : "width=\"100%\"") . ">
					<tr>";
	if ($selected == 10 || $selected == 33) {
		echo "<td width=\"20\" class=tdheader><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>";
	} else {
		echo "<td width=\"20\" class=tdheader>&nbsp;</td>";
	}
	echo createHeader($selected);
	echo "</tr>";
	echo printData($selected);
	echo "</table>";
	echo "</div></div></div>";
	echo "<div class=\"ui-widget-header consol-main-content-footer\" style=\"height: 30px;\"><div style=\"float:left\">";
	echo "</div>";
	echo "<div style=\"float:right;\">" . paging() . "</div>";
	echo "</div>";
	echo "</div>";
	echo "</form>";
}

function createHeader($selected)
{
	global $appConfig;

	//variable header set
	$hBasic =
		"<td class=tdheader>Nomor</td> 
		 <td class=tdheader>Nama WP</td> 
		 <td class=tdheader>Kecamatan</td> 
		 <td class=tdheader>" . $appConfig['LABEL_KELURAHAN'] . "</td> 
		 <td class=tdheader>NOP</td> 
		 <td class=tdheader>Tanggal Terima</td> ";

	$hTolak 	 	= "<td class=tdheader>Ditolak di</td> 
					   <td class=tdheader>Alasan</td> ";

	$hVerifikasi 	= "<td class=tdheader>Dalam Proses di</td> ";

	$hPersetujuan	= "<td class=tdheader align=center>Tanggal di setujui</td> 
					   <td class=tdheader>Nomor LHP</td> ";

	$hSK			= "<td class=tdheader>No SK</td> 
					   <td class=tdheader>Keterangan</td> ";

	$hCetakSK		= "<td class=tdheader>Cetak SK</td> ";

	$hCetakLHP		= "<td class=tdheader>Cetak LHP</td> ";

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
	if ($aData != null && count($aData) > 0)
		foreach ($aData as $data) {

			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			$HTML .= "<tr>";
			if ($selected == 10 || $selected == 33) {
				$HTML .= "<td width=9 class=$class align=center><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_ID'] . "+" . $data['CPM_WP_HANDPHONE'] . "\" /></td>";
			} else {
				$HTML .= "<td width=9 class=$class align=center>&nbsp;</td>";
			}
			$HTML .= parseData($data, $selected, $class);
			$HTML .= "</tr>";
			$i++;
		}
	return $HTML;
}

function getData($selected)
{
	global $dbServices, $srch, $arConfig, $appConfig, $data, $kec, $custom, $jumhal, $totalrows, $perpage, $page, $kel, $buku;
	$filter = array();
	//Seleksi Status dan jenis Berkas
	$filter['CPM_TYPE'][] = 9;

	//Penerimaan Pengurangan
	if ($selected == 10) {
		$filter['CPM_STATUS'][] = 1;
	} else if ($selected == 11) {
		$filter['CPM_STATUS'][] = 2;
		$filter['CPM_STATUS'][] = 3;
	} else if ($selected == 12) {
		$filter['CPM_STATUS'][] = 5;
		$filter['CPM_STATUS'][] = 6;
		//----------------------
		//Verifikasi Pengurangan
	} else if ($selected == 20) {
		$filter['CPM_STATUS'][] = 2;
	} else if ($selected == 22) {
		$filter['CPM_STATUS'][] = 5;
		//----------------------
		//Persetujuan Pengurangan
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

	$numLHP		 = getDataReduce('CPM_RE_LHP_NUMBER', $data['CPM_ID']);
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
		"<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params . "&svcid=" . $data['CPM_ID']) . "'>" . $data['CPM_ID'] . "</a> </td> 
		 <td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td> 
		 <td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . " </td> 
		 <td class=\"$class\"> " . kelShow($data['CPM_OP_KELURAHAN']) . " </td> 
		 <td class=\"$class\"> " . $data['CPM_OP_NUMBER'] . " </td> 
		 <td class=\"$class\" align=\"center\"> " . substr($data['CPM_DATE_RECEIVE'], 8, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 5, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 0, 4) . " </td> ";

	$dTolak 	 	= "<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td>   
						   <td class=\"$class\"> " . $data['CPM_REFUSAL_REASON'] . " </td> ";
	$dVerifikasi 	= "<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> ";
	#Old
	/* $dPersetujuan	= "<td class=\"$class\"> " . $data['CPM_DATE_APPROVER'] . " </td> 
						   <td class=\"$class linkInputNomor\" align=\"center\" id=\"".$data['CPM_ID']."+". $numLHP ."+". $dateLHP ."\">Isi Nomor</td> "; */
	#New 
	$dPersetujuan	= "<td class=\"$class\" align=\"center\"> " . $data['CPM_DATE_APPROVER'] . " </td>  ";

	if ($numLHP) {
		$dLHP = "<td class=\"$class linkInputNomor\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numLHP . "+" . $dateLHP . "\">" . $numLHP . " </td> ";
	} else {
		$dLHP = "<td class=\"$class linkInputNomor\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numLHP . "+" . $dateLHP . "\">Isi Nomor</td> ";
	}

	if ($numSK) {
		$dSK = "<td class=\"$class inputNoSK\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numSK . "+" . $dateSK . "+" . $data['CPM_OP_NUMBER'] . "+" . $data['CPM_SPPT_YEAR'] . "\">" . $numSK . " </td> ";
	} else {
		$dSK = "<td class=\"$class inputNoSK\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $numSK . "+" . $dateSK . "+" . $data['CPM_OP_NUMBER'] . "+" . $data['CPM_SPPT_YEAR'] . "\">Isi Nomor</td> ";
	}

	if ($keterangan) $dSK .= "<td class=\"$class isiKeterangan\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $keterangan . "\">" . $keterangan . "</td> ";
	else $dSK .= "<td class=\"$class isiKeterangan\" align=\"center\" id=\"" . $data['CPM_ID'] . "+" . $keterangan . "\">Isi</td> ";
	//$dSK = "<td class=\"$class inputNoSK\" align=\"center\" id=\"".$data['CPM_ID']."+". $numSK ."+". $dateSK ."+".$data['CPM_OP_NUMBER']."+".$data['CPM_SPPT_YEAR']."\">Isi Nomor</td> ";

	//$dLHP			= "<td class=\"$class\"> " . $data['CPM_DATE_APPROVER'] . " </td> ";

	/* $LHPNum			= getDataReduce('CPM_RE_LHP_NUMBER',$data['CPM_ID']);
		$LHPDate		= getDataReduce('CPM_RE_LHP_DATE',$data['CPM_ID']);
		$persenApp		= getDataReduce('CPM_RE_PERCENT_APPROVE',$data['CPM_ID']);	
		
		$STP			= $data['CPM_STP'];
		
		#Jika belum ada No dan tanggal LHP
		if((!$LHPNum) && (!$LHPDate)){
			$dSK = "<td class=\"$class\" align=\"center\"> Belum ada SK </td> ";	
		} 
		#Jika STP = 0 atau persen yang disetujui = 0
		else if(($STP==1) || ($persenApp==0)) {
			$dSK = "<td class=\"$class\" align=\"center\"> Selesai </td> ";
		}	
		#Jika STP selain dari 1
		else if($STP!=1) {
			$dSK = "<td class=\"$class kirim\" align=\"center\"><input type=\"button\" name=\"kirim\" id=\"kirim\" value=\"Kirim\" onclick=\"sendToPenetapan('".$data['CPM_OP_NUMBER']."','".$a."','".$data['CPM_SPPT_YEAR']."')\"></td> ";
		} */


	$pbbTerutang = $data['CPM_SPPT_DUE'];
	$maxValue	 = $arConfig['maks_np_kdpd'];
	#Jika PBB yang terutang lebih dari 1,5 Milyar Cetak SK Walikota
	if ($pbbTerutang > $maxValue) {
		$dCetakSK = "<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"PDF SK Walikota\" name=\"btn-cetak\" id=\"btn-cetak\" onclick=\"printToPDF('" . $data['CPM_ID'] . "')\"></td>";
	} else {
		$dCetakSK = "<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"PDF SK Kadin\" name=\"btn-cetak2\" id=\"btn-cetak2\" onclick=\"printToPDF2('" . $data['CPM_ID'] . "')\"></td>";
	}

	$dCetakLHP = "<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"Pratinjau LHP\" name=\"btn-prev-lhp\" id=\"btn-prev-lhp\" onclick=\"prevLHP('" . $data['CPM_ID'] . "')\"></td>";
	if ($selected == '90') {
		if (getDataLHP('CPM_LHP_APPROVAL_STATUS', $data['CPM_ID']) == '1') {
			$dCetakLHP = "<td class=\"$class\" align=\"center\"><input type=\"button\" value=\"Cetak LHP\" name=\"btn-cetak-lhp\" id=\"btn-cetak-lhp\" onclick=\"printLHP('" . $data['CPM_ID'] . "')\"></td>";
		} else if (getDataLHP('CPM_LHP_SID', $data['CPM_ID']) == '') {
			$dCetakLHP = "<td class=\"$class\" align=\"center\"></td>";
		}
	}


	$parse = $dBasic;

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
	}
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
	$idForm = $arConfig['idpengurangan-form'];

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
				var value = $(this)[0].tooltipText.replace(//g, '<br />');
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