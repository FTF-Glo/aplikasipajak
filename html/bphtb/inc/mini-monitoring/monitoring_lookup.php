<?php
	function formatInt($int) {
		return ($int*1);
	}
	
	function formatString($str){
		return $str;
	}
	
	function formatCurrency($number) {
		$str =number_format($number, 2, ',', '.');
		return $str;
	}
	
	function formatDate($sd) {
		if ($sd != '') {
			$yr = substr($sd, 0, 4);  // returns "cde"
			$mt = substr($sd, 4, 2);  // returns "cde"
			$dy = substr($sd, 6, 2);  // returns "cde"
			$dt = $dy."/".$mt."/".$yr;
			return $dt;
		} else {
			return $sd;
		}
	}
	
	function linkUrlString($strUrl) {
		$xpStrUrl = explode("/",$strUrl);
		$c = count($xpStrUrl);
		$fileDownload = $xpStrUrl[($c-1)];
		$href = implode("/", $xpStrUrl);
		$href = base64_encode($href);
		$link = "<a href='inc/mini-monitoring/download-file.php?q=".$href."'>".$fileDownload."</a>";
		return $link;
	}
	
	function LOOKUP_ALL_MONITORING ($lookupTbl,$dblink) {
		
		$query = "SELECT * FROM ".$lookupTbl." ORDER BY DPP_LOOK_PRIORITY ASC";
		
		$res = mysqli_query($dblink, $query);
		if ($res) {
			$nRes = mysqli_num_rows($res);
			
			if ($nRes > 0) {
				$result = array();
				$i = 0;
				$found = false;
				while (($row = mysqli_fetch_array($res)) && !$found) {
					// var_dump($row);
					$lookupId = $row['DPP_LOOK_ID'];
					$dbName   = $row['DPP_LOOK_DB_NAME'];
					$dbHost   = $row['DPP_LOOK_DB_HOST'] . ":" . $row['DPP_LOOK_DB_PORT'];
					$dbUser   = $row['DPP_LOOK_DB_USER'];
					$dbPwd    = $row['DPP_LOOK_DB_PWD'];
					$dbTable  = $row['DPP_LOOK_DB_TABLE_NAME'];
					$dbPriority = $row['DPP_LOOK_PRIORITY'];
					
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
		return $result;
	}	
?>