<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

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

error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBLink = "";

function headerListTagihan()
{
	global $appConfig;

	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\">
	  <tr>
		<th width=\"80\" align=\"center\">TAHUN</td>
		<th width=\"117\" align=\"center\">NOP</td>
		<th width=\"136\" align=\"center\">NAMA</td>
		<th width=\"136\" align=\"center\">ALAMAT</td>
		<th width=\"100\" align=\"center\">LUAS BUMI</td>
		<th width=\"100\" align=\"center\">LUAS BANGUNAN</td>
		<th width=\"100\" align=\"center\">NJOP BUMI</td>
		<th width=\"100\" align=\"center\">NJOP BANGUNAN</td>
		<th width=\"100\" align=\"center\">TOTAL NJOP</td>
		<th width=\"100\" align=\"center\">POKOK</td>
		<th width=\"100\" align=\"center\">DENDA</td>
		<th width=\"100\" align=\"center\">TOTAL</td>
		<th width=\"100\" align=\"center\">STATUS</td>
		<th width=\"100\" align=\"center\">TANGGAL BAYAR</td>
	  </tr>
	";
	return $html;
}

// koneksi mysql
function openMysql()
{
	global $appConfig;
	$host 	= $appConfig['GW_DBHOST'];
	$port 	= isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
	$user 	= $appConfig['GW_DBUSER'];
	$pass 	= $appConfig['GW_DBPWD'];
	$dbname = $appConfig['GW_DBNAME'];
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function showTable()
{
	global $nop;

	$dt 		= getListTagihan($nop);
	// print_r($dt);
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a = 1;
	$html 		.= headerListTagihan();
	$summary = array('name' => 'TOTAL', 'SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
	for ($i = 0; $i < $c; $i++) {

		$html .= " <tr>
				<td align=\"center\">" . $dt[$i]['TAHUN'] . "</td>
	            <td align=\"center\">" . $dt[$i]['NOP'] . "</td>
				<td align=\"left\">" . $dt[$i]['NAMA'] . "</td>
				<td align=\"left\">" . $dt[$i]['ALAMAT'] . "</td>
				<td align=\"right\">" . $dt[$i]['LUAS_BUMI'] . "</td>
	            <td align=\"right\">" . $dt[$i]['LUAS_BANGUNAN'] . "</td>
				<td align=\"right\">" . number_format($dt[$i]['NJOP_BUMI'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['NJOP_BANGUNAN'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['NJOP'], 0, ',', '.') . "</td>
     	        <td align=\"right\">" . number_format($dt[$i]['POKOK'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['DENDA'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['TOTAL'], 0, ',', '.') . "</td>
                <td align=\"left\">" . ($dt[$i]['STATUS'] == 1 ? "LUNAS" : "BELUM BAYAR") . "</td>
                <td align=\"left\">" . $dt[$i]['TGL_BAYAR'] . "</td>
	          </tr>";

		$summary['SUM_POKOK'] 	+= $dt[$i]['POKOK'];
		$summary['SUM_DENDA'] 	+= $dt[$i]['DENDA'];
		$summary['GRAND_TOTAL']	+= $dt[$i]['TOTAL'];

		$a++;
	}
	$html .= " <tr>
            <td colspan=\"9\" align=\"center\"><b>" . $summary['name'] . "</b></td>
            <td align=\"right\">" . number_format($summary['SUM_POKOK'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['SUM_DENDA'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['GRAND_TOTAL'], 0, ',', '.') . "</td>
            <td align=\"right\" colspan=\"2\"></td>
          </tr>";

	return $html . "</table>";
}

function getListTagihan($nop)
{
	global $myDBLink, $appConfig;

	$myDBLink = openMysql();
	$return = array();
	$query = "SELECT 
				NOP,
				SPPT_TAHUN_PAJAK AS TAHUN,
				WP_NAMA AS NAMA,
				WP_ALAMAT AS ALAMAT,
				SPPT_PBB_HARUS_DIBAYAR AS POKOK,
				PBB_DENDA AS DENDA,
				PBB_TOTAL_BAYAR AS TOTAL,
				PAYMENT_FLAG AS STATUS,
				PAYMENT_PAID AS TGL_BAYAR,
				OP_LUAS_BUMI AS LUAS_BUMI, 
				OP_LUAS_BANGUNAN AS LUAS_BANGUNAN, 
				OP_NJOP_BUMI AS NJOP_BUMI, 
				OP_NJOP_BANGUNAN AS NJOP_BANGUNAN, 
				OP_NJOP AS NJOP
				
				FROM
				PBB_SPPT
			WHERE
				NOP = '" . $nop . "' ORDER BY SPPT_TAHUN_PAJAK DESC ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);
		$return[$i]["NOP"]		= ($row["NOP"] != "") ? $row["NOP"] : "";
		$return[$i]["TAHUN"]	= ($row["TAHUN"] != "") ? $row["TAHUN"] : "";
		$return[$i]["NAMA"]		= ($row["NAMA"] != "") ? $row["NAMA"] : "";
		$return[$i]["ALAMAT"]	= ($row["ALAMAT"] != "") ? $row["ALAMAT"] : "";
		$return[$i]["POKOK"]	= ($row["POKOK"] != "") ? $row["POKOK"] : 0;
		$return[$i]["DENDA"]	= ($row["DENDA"] != "") ? $row["DENDA"] : 0;
		$return[$i]["TOTAL"]	= ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
		$return[$i]["STATUS"]	= ($row["STATUS"] != "") ? $row["STATUS"] : 0;
		$return[$i]["LUAS_BUMI"]	= ($row["LUAS_BUMI"] != "") ? $row["LUAS_BUMI"] : 0;
		$return[$i]["LUAS_BANGUNAN"]	= ($row["LUAS_BANGUNAN"] != "") ? $row["LUAS_BANGUNAN"] : 0;
		$return[$i]["NJOP_BUMI"]	= ($row["NJOP_BUMI"] != "") ? $row["NJOP_BUMI"] : 0;
		$return[$i]["NJOP_BANGUNAN"]	= ($row["NJOP_BANGUNAN"] != "") ? $row["NJOP_BANGUNAN"] : 0;
		$return[$i]["NJOP"]	= ($row["NJOP"] != "") ? $row["NJOP"] : 0;
		$return[$i]["TGL_BAYAR"]	= ($row["TGL_BAYAR"] != "") ? $row["TGL_BAYAR"] : 0;
		$i++;
	}
	closeMysql($myDBLink);
	return $return;
}

$nop 				= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$a 					= @isset($_REQUEST['app']) ? $_REQUEST['app'] : "";
$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);

// print_r($_REQUEST);exit;
// print_r($appConfig);exit;

echo showTable();
