<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	$uid = $_REQUEST['uid'];
        header('Content-type: text/plain');
        if($_REQUEST['mode']=='spop'){
            $filename = "SPOPLog-".$uid."-".date('Ymd').".txt";
            header("Content-Disposition: attachment; filename=".$filename);
            $sql = "SELECT A.* FROM cppmod_pbb_sppt_update_log A, cppmod_pbb_tranmain B
                WHERE B.CPM_TRAN_SPPT_DOC_ID = A.CPM_SPPT_DOC_ID
                AND B.CPM_TRAN_FIELD_OFFICER='".$uid."'
                AND B.CPM_TRAN_STATUS='1' ";
            $result = mysqli_query($DBLink, $sql);
            while($row = mysqli_fetch_array($result)){
                echo $row['CPM_SPPT_DOC_ID']."|".$row['CPM_FIELD']."|".$row['CPM_VALUE_BEFORE']."|".$row['CPM_VALUE_AFTER']."\n";
            }
        }else if($_REQUEST['mode']=='lspop'){
            $filename = "LSPOPLog-".$uid."-".date('Ymd').".txt";
            header("Content-Disposition: attachment; filename=".$filename);
            $sql = "SELECT A.* FROM cppmod_pbb_sppt_update_ext_log A, cppmod_pbb_tranmain B
                WHERE B.CPM_TRAN_SPPT_DOC_ID = A.CPM_SPPT_DOC_ID
                AND B.CPM_TRAN_FIELD_OFFICER='".$uid."'
                AND B.CPM_TRAN_STATUS='1'";
            
            $result = mysqli_query($DBLink, $sql);
            while($row = mysqli_fetch_array($result)){
                echo $row['CPM_SPPT_DOC_ID']."|".$row['CPM_OP_NUM']."|".$row['CPM_FIELD']."|".$row['CPM_VALUE_BEFORE']."|".$row['CPM_VALUE_AFTER']."\n";
            }
        }
?>

