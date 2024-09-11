<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
        $myFile = "tes.txt";
        $fh = fopen($myFile, 'r') or die("can't open file");
        $sql = "INSERT INTO CPM_PBB_SPPT_UPDATE_LOG VALUES ";
        $i = 0;
        if ($fh) {
            while (($content = fgets($fh, 4096)) !== false) {
                $content = str_replace("\n","",$content);
                $row = explode('|',$content);
                if($i > 0) $sql .=",";
                $sql .="('".mysqli_real_escape_string($DBLink, $row[0])."','".mysqli_real_escape_string($DBLink, $row[1])."','".mysqli_real_escape_string($DBLink, $row[2])."','".mysqli_real_escape_string($DBLink, $row[3])."')";
                $i++;
            }
            if (!feof($fh)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($fh);
        }
        mysqli_query($DBLink, $sql);
?>
