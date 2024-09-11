<?php
	$sql    = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` LIKE 'GW_DB%' AND `CTR_AC_AID` = 'aPBB'";
	$result = mysqli_query($DBLink, $sql);
	while($row = mysqli_fetch_array($result)) define($row[1], $row[2]); 
?>