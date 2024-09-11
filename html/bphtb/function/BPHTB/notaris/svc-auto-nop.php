<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue($key) {
    global $DBLink, $appID;
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getDataGW($kdbayar){
		$a = "aBPHTB";
		$DbName = getConfigValue('BPHTBDBNAME');
        $DbHost = getConfigValue('BPHTBHOSTPORT');
        $DbPwd =  getConfigValue('BPHTBPASSWORD');
        $DbTable = getConfigValue('BPHTBTABLE');
        $DbUser = getConfigValue('BPHTBUSERNAME');

        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }
		
		$qry = "select * from ssb where id_switching = '" . $kdbayar . "'";
		$res = mysqli_query($LDBLink, $qry);
		$result=array();
		if ($res === false) {
			echo $qry . "<br>";
			echo mysqli_error($LDBLink);
		}
		while ($row = mysqli_fetch_array($res)) {
			$result['ID']=$row['id_switching'];
			$result['payment_flag']=$row['payment_flag'];
			$result['bphtb_dibayar']=$row['bphtb_dibayar'];
		}
		$result['JML_DATA']=mysqli_num_rows($res);
		//echo $DbName;
		return $result;
	}

function getNOPBPHTB($nop) {
    global $DBLink;

//    $dbName = "SW_SSB_O2W";
//    $dbHost = "localhost";
//    $dbPwd = "sw_user";
//    $dbUser = "sw_pwd";
//    $dbTable = "cppmod_ssb_doc";
//    #SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn, $dbHost, $dbUser, $dbPwd, $dbName);
//    $DBLinkLookUp = mysql_connect('127.0.0.1', 'sw_user', 'sw_pwd',true);
//    mysql_select_db('SW_SSB_O2W');
    // $conn = mysql_connect('127.0.0.1', 'sw_user', 'sw_pwd',true);
    // mysql_select_db('SW_SSB_O2W');
     $sql_lookup = "select * from cppmod_ssb_doc where CPM_OP_NOMOR = '{$nop}' order by CPM_SSB_CREATED desc limit 1";
    $res_lookup = mysqli_query($DBLink, $sql_lookup);
    
    $respon = array();
    $respon['result'] = true;
    $data = array();
    //print_r($sql_lookup);
    if ($dt_lookup = mysqli_fetch_array($res_lookup)) {
        #Letak tanah, kelurahan, kecamatan, nama WP Lama, RT-RW, Kota, dan NJOP / m2 terisi dan tidak bisa diedit.
        #Luas terisi dengan data luas sesuai data objek pajak PBB namun bisa diedit. 
        $data['name'] = $dt_lookup['CPM_WP_NAMA'];
        $data['npwp'] = $dt_lookup['CPM_WP_NPWP'];
        $data['noktp'] = $dt_lookup['CPM_WP_NOKTP'];
        $data['address'] = $dt_lookup['CPM_WP_ALAMAT'];
        $data['kelurahan'] = $dt_lookup['CPM_WP_KELURAHAN'];
        $data['kecamatan'] = $dt_lookup['CPM_WP_KECAMATAN'];
        $data['rt'] = ($dt_lookup['CPM_WP_RT']=="")? "00" : $dt_lookup['CPM_WP_RT'];
        $data['rw'] = ($dt_lookup['CPM_WP_RW']=="")? "000" : $dt_lookup['CPM_WP_RW'];
        $data['kabupaten'] = $dt_lookup['CPM_WP_KABUPATEN'];
        $data['zipcode'] = $dt_lookup['CPM_WP_KODEPOS'];
        
        $data['nama_wp_lama'] = $dt_lookup['CPM_WP_NAMA_LAMA'];
        $data['address2'] = $dt_lookup['CPM_OP_LETAK'];
        $data['namawpcert'] = $dt_lookup['CPM_WP_NAMA_CERT'];
        $data['kelurahan2'] = $dt_lookup['CPM_OP_KELURAHAN'];
        $data['kecamatan2'] = $dt_lookup['CPM_OP_KECAMATAN'];
        $data['rt2'] = ($dt_lookup['CPM_OP_RT']=="")? "00" : $dt_lookup['CPM_OP_RT'];
        $data['rw2'] = ($dt_lookup['CPM_OP_RW']=="")? "000" : $dt_lookup['CPM_OP_RW'];
        $data['kabupaten2'] = $dt_lookup['CPM_OP_KABUPATEN'];
        $data['zipcode2'] = $dt_lookup['CPM_OP_KODEPOS'];
        
        $data['right_year'] = $dt_lookup['CPM_OP_THN_PEROLEH'];
        $data['land_njop'] = $dt_lookup['CPM_OP_NJOP_TANAH'];
        $data['building_njop'] = $dt_lookup['CPM_OP_NJOP_BANGUN'];        
        $data['land_area'] = $dt_lookup['CPM_OP_LUAS_TANAH'];
        $data['building_area'] = $dt_lookup['CPM_OP_LUAS_BANGUN']; 
        
        $data['trans_value'] = $dt_lookup['CPM_OP_HARGA']; 
        $data['right_land_build'] = $dt_lookup['CPM_OP_JENIS_HAK'];
        $data['certificate_number'] = $dt_lookup['CPM_OP_NMR_SERTIFIKAT'];
        $data['akumulasi'] = $dt_lookup['CPM_SSB_AKUMULASI'];
        
        $data['tNPOP'] = $dt_lookup['CPM_SSB_AKUMULASI'];
        $data['tNPOPTKP'] = $dt_lookup['CPM_OP_NPOPTKP'];
        $data['tBPHTBTU'] = $dt_lookup['CPM_OP_BPHTB_TU'];
        $data['jsb_choose'] = $dt_lookup['CPM_PAYMENT_TIPE_SURAT'];
        $data['jsb_choose_number'] = $dt_lookup['CPM_PAYMENT_TIPE_SURAT_NOMOR'];
        $data['jsb_choose_date'] = $dt_lookup['CPM_PAYMENT_TIPE_SURAT_TANGGAL'];
		$data['penguranganaphb'] = $dt_lookup['CPM_APHB'];
		$data['znt'] = $dt_lookup['CPM_OP_ZNT'];
		$dataGW=getDataGW($dt_lookup['CPM_SSB_ID']);
		$data['bphtb_dibayar'] = $dataGW['bphtb_dibayar'];
		$data['id_ssb_sebelum'] = $dt_lookup['CPM_SSB_ID'];
        
        
    }else{
        $respon['result'] = false;
        $respon['message'] = "";
    }
    $respon['data'] = $data;
    return $respon;
}

if (!isset($_POST['nop']))
    exit($json->encode(array()));

$NOP_AUTOFILL = getConfigValue("NOP_AUTOFILL");
$nop = $_POST['nop'];

if ($NOP_AUTOFILL == 0)
    $res = array();
else
    $res = getNOPBPHTB($nop);

echo $json->encode($res);
exit;
?>
