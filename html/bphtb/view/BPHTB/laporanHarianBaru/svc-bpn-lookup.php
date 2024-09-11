<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'laporanHarianBaru', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

$iErrCode="";
$sErrMsg="";

function LOOKUP_ALL_BPHTB() {
	global $iErrCode,$sErrMsg;
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
		 
	$query = "SELECT * FROM cppmod_ssb_lookup ORDER BY CPM_LOOK_PRIORITY ASC";
	$res = mysqli_query($DBLink, $query);
	if ($res) {
		$nRes = mysqli_num_rows($res);
		
		if ($nRes > 0) {
			$result = array();
			$i = 0;
			while (($row = mysqli_fetch_array($res))) {
				$lookupId = $row['CPM_LOOK_ID'];
				$dbName   = $row['CPM_LOOK_DB_NAME'];
				$dbHost   = $row['CPM_LOOK_DB_HOST'] . ":" . $row['CPM_LOOK_DB_PORT'];
				$dbUser   = $row['CPM_LOOK_DB_USER'];
				$dbPwd    = $row['CPM_LOOK_DB_PWD'];
				$dbTable  = $row['CPM_LOOK_TABLE_NAME'];
				$dbPriority = $row['CPM_LOOK_PRIORITY'];
				
				$result[$i]["LOOK_ID"] = $lookupId;
				$result[$i]["DB_NAME"] = $dbName;
				$result[$i]["DB_HOST"] = $dbHost;
				$result[$i]["DB_USER"] = $dbUser;
				$result[$i]["DB_PWD"]  = $dbPwd;
				$result[$i]["DB_TABLE"] = $dbTable;
				$result[$i]["DB_PRIORITY"] = $dbPriority;
				
				$i++;
			}
		}
	}
	SCANPayment_CloseDB($DBLink);
	
	return $result;
}

function LOOKUP_BPHTB ($whereClause, &$resDbName, &$resDbHost, &$resDbUser, &$resDbPwd, &$resDbTable) {
	global $iErrCode,$sErrMsg;
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
	
	$query = "SELECT * FROM cppmod_ssb_lookup ORDER BY CPM_LOOK_PRIORITY ASC";
	
	$res = mysqli_query($DBLink, $query);
	if ($res) {
		$nRes = mysqli_num_rows($res);
		
		if ($nRes > 0) {
			$i = 0;
			$found = false;
			while (($row = mysqli_fetch_array($res)) && !$found) {
				$dbName  = $row['CPM_LOOK_DB_NAME'];
				$dbHost  = $row['CPM_LOOK_DB_HOST'] . ":" . $row['CPM_LOOK_DB_PORT'];
				$dbUser  = $row['CPM_LOOK_DB_USER'];
				$dbPwd   = $row['CPM_LOOK_DB_PWD'];
				$dbTable = $row['CPM_LOOK_TABLE_NAME'];
				$dbPriority = $row['CPM_LOOK_PRIORITY'];
				
				// Connect to lookup database
				SCANPayment_ConnectToDB($DBLink2, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
				
				$query2 = "SELECT COUNT(*) COUNT FROM $dbTable ";
				if (strlen(trim($whereClause)) > 0) {
					$whereClause = trim($whereClause);
					$pos = stripos($whereClause, "where");
					if ($pos === false) {
						$query2 .= "WHERE " . $whereClause;
					} else {
						$query2 .= $whereClause;
					}
				}
				$res2 = mysqli_query($DBLink2, $query2);
				if ($res2) {
					$nRes2 = mysqli_num_rows($res2);
					
					if ($nRes2 > 0) {
						$row2 = mysqli_fetch_array($res2);
						$count = $row2['COUNT'];
						if ($count > 0) {
							$resDbName = $dbName;
							$resDbHost = $dbHost;
							$resDbUser = $dbUser;
							$resDbPwd = $dbPwd;
							$resDbTable = $dbTable;
							$found = true;
						}

					}
				}
				
				SCANPayment_CloseDB($DBLink2);
				
				$i++;
			}
		}
	}
	SCANPayment_CloseDB($DBLink);
}

?>