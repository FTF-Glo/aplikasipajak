<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
        $sql = "SELECT * FROM cppmod_pbb_sppt_update_log ";
        header('Content-type: text/plain');
        $result = mysqli_query($DBLink, $sql);
        while($row = mysqli_fetch_array($result)){
            echo $row['CPM_SPPT_DOC_ID']."|".$row['CPM_FIELD']."|".$row['CPM_VALUE_BEFORE']."|".$row['CPM_VALUE_AFTER']."\n";
        }
        header('Content-Disposition: attachment; filename="log.txt"');
        
        $sql = "SELECT * FROM cppmod_pbb_sppt_update_ext_log ";
        header('Content-type: text/plain');
        $result = mysqli_query($DBLink, $sql);
        while($row = mysqli_fetch_array($result)){
            echo $row['CPM_SPPT_DOC_ID']."|".$row['CPM_FIELD']."|".$row['CPM_VALUE_BEFORE']."|".$row['CPM_VALUE_AFTER']."\n";
        }
        header('Content-Disposition: attachment; filename="log.txt"');
?>

