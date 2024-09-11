<?php  
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once("classPembatalan.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec 		= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcPembatalan	= new SvcPembatalanSPPT($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$userLogin	= new SCANCentralUser (0,LOG_FILENAME,$DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$nop 		= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$tahun 		= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$proses		= @isset($_REQUEST['proses']) ? $_REQUEST['proses'] : "";
$uid		= @isset($_REQUEST['USER_LOGIN']) ? $_REQUEST['USER_LOGIN'] : "";
$alasan		= @isset($_REQUEST['alasan']) ? $_REQUEST['alasan'] : "";
$no_sk		= @isset($_REQUEST['no_sk']) ? $_REQUEST['no_sk'] : "";
$nop_string = "";



if ( isset($_REQUEST["submit"]) ) {

   if ( isset($_FILES["file"])) {

            //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }
        else {
                 //Print file details
             // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
             // echo "Type: " . $_FILES["file"]["type"] . "<br />";
             // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
             // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

                 //if file already exists
             if (file_exists("upload/" . $_FILES["file"]["name"])) {
           		 echo $_FILES["file"]["name"] . " already exists. ";
             }
             else {
             	$handle = fopen($_FILES["file"]["tmp_name"], 'r');
	        }
		       


	            if ( fopen($_FILES["file"]["tmp_name"], 'r') ) {
				    // echo "File opened.<br />";
				    $file = fopen($_FILES["file"]["tmp_name"], 'r');

				    // $firstline = fgets ($file, 4096 );
				    //     //Gets the number of fields, in CSV-files the names of the fields are mostly given in the first line
				    // $num = strlen($firstline) - strlen(str_replace(";", "", $firstline));

				    //     //save the different fields of the firstline in an array called fields
				    // $fields = array();
				    // $fields = explode( ";", $firstline, ($num+1) );

				    // // echo $fields;
				    // // print_r($fields);
				    // $line = array();
				    // $i = 0;

				        //CSV: one line is one record and the cells/fields are seperated by ";"
				        //so $dsatz is an two dimensional array saving the records like this: $dsatz[number of record][number of cell]
				    $dsatz = array();
				    	// print_r($line);
				    while ( $line[$i] = fgets ($file) ) {

				        // $dsatz[$i] = array();
				        $nilai =  explode( ";", $line[$i]) ;
				        if (!empty($nilai))
					        array_push($dsatz,$nilai);
				        // $dsatz[$i] = explode( ";", $line[$i], ($num+1) );

				        $i++;
				    }
				    // echo "<pre>";
				    // print_r($dsatz);
				    // echo "</pre>";
				    // exit;
				    // echo "123";
				    $array_nop  = array();
				    foreach ($dsatz as $key => $value) {
				    	foreach ($value as $key2 => $value2) {
				    		array_push($array_nop, $value2);
				    	}
				    }
				}else{
					echo "gagal fopen";
				}
	            //end
	             // exit;
            }
        }
     } else {
             echo "No file selected <br />";
     }

$array_all = array();
$kunci = 0;
$temp_array = array();
for($x=0;$x<count($array_nop);$x++){
	array_push($array_all, 
		array(
			'nop'=>$array_nop[$x],
			'tahun'=>$tahun
		)
	);
}


// echo "<pre>";
// print_r($array_all);
// echo "</pre>";
// exit;

// exit;
// foreach ($variable as $key => $value) {
// 	# code...
// }
// echo "<pre>";
// print_r($array_all);
// echo "</pre>";
 // exit;

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 
// $dbname = $_REQUEST['use']; 
$uname	= $userLogin->GetUserName($uid);

// echo $alasan."<br>";
// echo $no_sk."<br>";
// echo "<pre>";
// print_r($array_all);
// echo "<br>";
// echo $no_sk;
// echo "<br>";
// echo $alasan;
// echo "</pre>";

// exit;
$svcPembatalan->C_HOST_PORT = $host.':'.$port;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;
// exit;
$bOK = false;
//Copy data dari PBB_SPPT ke PBB_SPPT_DIBATALKAN
$array_status = array();
foreach ($array_all as $key2=>$value):
	// echo $value['tahun'];
	$tahun = $value['tahun'];
	$nop = $value['nop'];


	$bOK = $svcPembatalan->copyToPembatalan($nop,$tahun,$no_sk,$alasan,$uname);
	//Delete data yang sudah di copy ke PBB_SPPT_DIBATALKAN dari PBB_SPPT 
	if($bOK){ 
		$bOK = $svcPembatalan->delGateWayPBBSPPT($nop,$tahun);
		array_push($array_status, 1);
	}else{
		array_push($array_status, 0);
	}
	if($svcPembatalan->isCurrentExist($nop,$tahun)){ 
		$respon['isCurrentExist'] = true;
		//INSERT ke tabel SW.cppmod_pbb_sppt_current_dibatalkan
		if($bOK){
			$bOK = $svcPembatalan->copySPPTCurrentToPembatalan($nop,$tahun,$no_sk,$alasan,$uname);
			// if (!$bOK){
			// 	echo "fatal sih";
			// }
		}

		if($bOK){
			// //DELETE dari SW.cppmod_pbb_sppt_current (kalau data di tabel CURRENT nya ada/SPPT tahun berjalan)
			$bOK = $svcPembatalan->deleteSPPTCurrent($nop,$tahun);
			// array_push($array_status, 1);
		}else{
			// array_push($array_status, 0);
		}
	}else{
		$respon['isCurrentExist'] = false;
	}
	//Jika di fasumkan 
	if($proses==1){
		if($bOK){
			//UPDATE data pada tabel SW.cppmod_pbb_sppt_final/SW.cppmod_pbb_sppt_susulan/SW.cppmod_pbb_sppt field CPM_OT_JENIS nilainya menjadi 4
			if($svcPembatalan->isPBBSPPTExist($nop)){
				$bOK = $svcPembatalan->updateJenisTanahPBBSPPT($nop);
			}
			else if($svcPembatalan->isFinalExist($nop,$tahun)){
				$bOK = $svcPembatalan->updateJenisTanahFinal($nop,$tahun);
			}
			else if($svcPembatalan->isSusulanExist($nop,$tahun)){
				$bOK = $svcPembatalan->updateJenisTanahSusulan($nop,$tahun);
			}
		}
	}
	if($bOK){
		//UPDATE Tahun Penetapan
		$svcPembatalan->updateTahunPenetapan($nop,$tahun);
	}
	if($bOK){
		//INSERT proses pembatalan ke cppmod_pbb_log_pembatalan
		$svcPembatalan->addToLog($uname,$nop,$tahun);
	}

endforeach;
// echo "ini status";
// print_r($array_status);

if(!$bOK){
    $respon['respon'] = false;
	$respon['message'] = mysqli_error($DBLink);
}else{
	?>
		<script type="text/javascript">
			alert("Data NOP pada <?php echo $_FILES['file']['name']." pada tahun ".$tahun ?>  berhasil di Batalkan ");
			window.history.back();
		</script>
	<?php
	// $respon['respon'] = true;
	// $respon['message'] = "sukses: ".$nop;
}

// echo json_encode($respon);exit;
?>