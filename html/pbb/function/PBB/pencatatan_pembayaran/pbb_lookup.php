<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'pbb', '', dirname(__FILE__))).'/';
require_once("../../../inc/payment/db-payment.php");
require_once("../../../inc/payment/inc-payment-db-c.php");
$iErrCode="";
$sErrMsg="";

function LOOKUP_ALL_pbb ($myConn) {
	global $iErrCode,$sErrMsg,$DBLink;
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
	
	$query = "SELECT * FROM CSCMOD_TAX_LOOKUP ORDER BY CSM_LOOK_PRIORITY ASC";

	$res = mysqli_query($myConn, $query);
	if ($res) {
		
		$nRes = mysqli_num_rows($res);
		if ($nRes > 0) {
			$result = array();
			$i = 0;
			while (($row = mysqli_fetch_array($res, MYSQL_ASSOC))) {
				//var_dump($row);
				$lookupId = $row['CSM_LOOK_ID'];
				$dbName   = $row['CSM_LOOK_DB_NAME'];
				$dbHost   = $row['CSM_LOOK_DB_HOST'] . ":" . $row['CSM_LOOK_DB_PORT'];
				$dbUser   = $row['CSM_LOOK_DB_USER'];
				$dbPwd    = $row['CSM_LOOK_DB_PWD'];
				$dbTable  = $row['CSM_LOOK_TABLE_NAME'];
				$dbPriority = $row['CSM_LOOK_PRIORITY'];
				
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

function LOOKUP_pbb ($whereClause, $myConn, &$resDbName, &$resDbHost, &$resDbUser, &$resDbPwd, &$resDbTable) {
	global $tableName;

	$OK = false;
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
	
	$query = "SELECT * FROM CSCMOD_TAX_LOOKUP ORDER BY CSM_LOOK_PRIORITY ASC";
	$res = mysqli_query($myConn, $query);

	if ($res) {
		$nRes = mysqli_num_rows($res);
		
		if ($nRes > 0) {
			$i = 0;
			$found = false;
			while (($row = mysqli_fetch_array($res, MYSQL_ASSOC)) && !$found) {
				// var_dump($row);
				$dbName  = $row['CSM_LOOK_DB_NAME'];
				$dbHost  = $row['CSM_LOOK_DB_HOST'] . ":" . $row['CSM_LOOK_DB_PORT'];
				$dbUser  = $row['CSM_LOOK_DB_USER'];
				$dbPwd   = $row['CSM_LOOK_DB_PWD'];
				$dbTable = $row['CSM_LOOK_TABLE_NAME'];
				$dbPriority = $row['CSM_LOOK_PRIORITY'];
				
				
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
				
				// echo "<br /><br />query 2 = $query2<br />\n";
				
				$res2 = mysqli_query($DBLink2, $query2);
				
				if ($res2) {
					$nRes2 = mysqli_num_rows($res2);
					if ($nRes2 > 0) {
						
						$row2 = mysqli_fetch_array($res2, MYSQL_ASSOC);
						$count = $row2['COUNT'];
						
						if ($count > 0) {
							$resDbName = $dbName;
							$resDbHost = $dbHost;
							$resDbUser = $dbUser;
							$resDbPwd = $dbPwd;
							$resDbTable = $dbTable;
							$found = true;
							//var_dump($resDbUser);
						}
					}
				}
				
				SCANPayment_CloseDB($DBLink2);
				
				$i++;
			}
			
			$OK = true;
		}
		
	} else {
		echo "error : ".mysqli_error($DBLink);
	}
	SCANPayment_CloseDB($DBLink);

	return $OK;

}


?>