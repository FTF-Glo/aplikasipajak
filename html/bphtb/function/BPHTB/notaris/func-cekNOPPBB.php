<?php

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
// print_r($row_config['CTR_AC_VALUE']);exit;
//$NOP_INFO = getConfigValue("NOP_INFO");
$NOP_VALIDASI = getConfigValue("NOP_VALIDASI");
$THN_PAJAK_BERLAKU = getConfigValue("THN_PAJAK_BERLAKU");

if ($NOP_VALIDASI == 0){
    echo "exit";exit;
}
	$dbName =  getConfigValue("PBBDBNAME");
	$dbHost =  getConfigValue("PBBDBHOST");
	$dbPwd =   getConfigValue("PBBDBPASS");
	$dbUser =  getConfigValue("PBBDBUSER");
        //$dbTable = getConfigValue('PBBDBTABLE');

// echo $dbName . ' ' . $dbHost . ' ' .$dbPwd . ' ' .$dbUser . '<br>';exit();
//$conn = mysql_connect($dbHost, $dbUser, $dbPwd);
//$conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
$conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
if ($conn) {
    // Akses Basis Data
    $qry_config="select CTR_AC_VALUE from sw_pbb.CENTRAL_APP_CONFIG where CTR_AC_AID='aPBB' and CTR_AC_KEY='tahun_tagihan'";
    $result_config=mysqli_query($conn, $qry_config);
    $row_config=mysqli_fetch_assoc($result_config);
    $db="";
    if ($row_config['CTR_AC_VALUE']== $THN_PAJAK_BERLAKU){
        $db="CPPMOD_PBB_SPPT_CURRENT";
    }else{
        $db="CPPMOD_PBB_SPPT_CETAK_$THN_PAJAK_BERLAKU";
    }
    $nop = $_POST['nop'];
    $query = "SELECT A.NOP, A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS,
                            A.OP_ALAMAT, A.OP_KELURAHAN, A.OP_RT, A.OP_RW, A.OP_KECAMATAN, A.OP_KOTAKAB,
                            A.OP_LUAS_BUMI, A.OP_NJOP_BUMI, A.OP_LUAS_BANGUNAN, A.OP_NJOP_BANGUNAN, A.SPPT_TAHUN_PAJAK, IFNULL(IFNULL(B.CPM_OT_ZONA_NILAI,C.CPM_OT_ZONA_NILAI),D.CPM_OT_ZONA_NILAI)
            FROM sw_pbb.{$db} A
            LEFT JOIN sw_pbb.CPPMOD_PBB_SPPT_FINAL B ON A.NOP = B.CPM_NOP
            LEFT JOIN sw_pbb.CPPMOD_PBB_SPPT_SUSULAN C ON C.CPM_NOP=A.NOP
            LEFT JOIN sw_pbb.CPPMOD_PBB_SPPT D ON D.CPM_NOP = A.NOP
            WHERE NOP='{$nop}' ORDER BY SPPT_TAHUN_PAJAK DESC LIMIT 0,1;";
    // print_r($query);exit;
    // print_r($query);exit;
    $result = mysqli_query($conn, $query);
    
	$check_noppbb=mysqli_num_rows($result);
	
	if($check_noppbb == 0){
		    $query = "SELECT A.CPM_NOP AS NOP, A.CPM_WP_NAMA AS WP_NAMA, A.CPM_WP_ALAMAT AS WP_ALAMAT, A.CPM_WP_KELURAHAN AS WP_KELURAHAN, A.CPM_WP_RT AS WP_RT, A.CPM_WP_RW AS WP_RW, A.CPM_WP_KECAMATAN AS WP_KECAMATAN, A.CPM_WP_KOTAKAB AS WP_KOTAKAB, A.CPM_WP_KODEPOS AS WP_KODEPOS, A.CPM_OP_ALAMAT AS OP_ALAMAT, G.CPC_TKL_KELURAHAN AS OP_KELURAHAN, A.CPM_OP_RT AS OP_RT, A.CPM_OP_RW AS OP_RW, F.CPC_TKC_KECAMATAN AS OP_KECAMATAN, E.CPC_TK_KABKOTA AS OP_KOTAKAB, A.CPM_OP_LUAS_TANAH as OP_LUAS_BUMI, A.CPM_NJOP_TANAH as OP_NJOP_BUMI, A.CPM_OP_LUAS_BANGUNAN as OP_LUAS_BANGUNAN, A.CPM_NJOP_BANGUNAN AS OP_NJOP_BANGUNAN, A.CPM_SPPT_THN_PENETAPAN AS SPPT_TAHUN_PAJAK, IFNULL(IFNULL(A.CPM_OT_ZONA_NILAI,C.CPM_OT_ZONA_NILAI),D.CPM_OT_ZONA_NILAI) FROM 
                CPPMOD_PBB_SPPT_FINAL A
                LEFT JOIN CPPMOD_PBB_SPPT_CURRENT B ON A.CPM_NOP = B.NOP
                LEFT JOIN cppmod_pbb_sppt_susulan C ON C.CPM_NOP=A.CPM_NOP 
                LEFT JOIN CPPMOD_PBB_SPPT D ON D.CPM_NOP = A.CPM_NOP 
                LEFT JOIN CPPMOD_TAX_KABKOTA E ON  E.CPC_TK_ID  = A.CPM_OP_KOTAKAB 
                LEFT JOIN CPPMOD_TAX_KECAMATAN F ON F.CPC_TKC_ID  = A.CPM_OP_KECAMATAN 
                LEFT JOIN CPPMOD_TAX_KELURAHAN G ON G.CPC_TKL_ID = A.CPM_OP_KELURAHAN 
                WHERE A.CPM_NOP='{$nop}' ORDER BY SPPT_TAHUN_PAJAK DESC LIMIT 0,1";
                //print_r($query);exit;
                // print_r($query);exit;
            $result = mysqli_query($conn, $query);
            $check_result=mysqli_num_rows($result);
            if ($check_result==0) {

                    $query = "SELECT D.CPM_NOP AS NOP,D.CPM_WP_NAMA AS WP_NAMA,D.CPM_WP_ALAMAT AS WP_ALAMAT,D.CPM_WP_KELURAHAN AS WP_KELURAHAN,D.CPM_WP_RT AS WP_RT,D.CPM_WP_RW AS WP_RW, D.CPM_WP_KECAMATAN AS WP_KECAMATAN, D.CPM_WP_KOTAKAB AS WP_KOTAKAB,D.CPM_WP_KODEPOS AS WP_KODEPOS,D.CPM_OP_ALAMAT AS OP_ALAMAT,G.CPC_TKL_KELURAHAN AS OP_KELURAHAN,D.CPM_OP_RT AS OP_RT,D.CPM_OP_RW AS OP_RW,
                        F.CPC_TKC_KECAMATAN AS OP_KECAMATAN, E.CPC_TK_KABKOTA AS OP_KOTAKAB, D.CPM_OP_LUAS_TANAH AS OP_LUAS_BUMI, D.CPM_NJOP_TANAH AS OP_NJOP_BUMI, D.CPM_OP_LUAS_BANGUNAN AS OP_LUAS_BANGUNAN,
                        D.CPM_NJOP_BANGUNAN AS OP_NJOP_BANGUNAN, D.CPM_SPPT_THN_PENETAPAN AS SPPT_TAHUN_PAJAK, IFNULL(IFNULL(A.CPM_OT_ZONA_NILAI,C.CPM_OT_ZONA_NILAI),D.CPM_OT_ZONA_NILAI)
                        FROM CPPMOD_PBB_SPPT_FINAL A 
                        LEFT JOIN CPPMOD_PBB_SPPT_CURRENT B ON A.CPM_NOP = B.NOP
                        LEFT JOIN cppmod_pbb_sppt_susulan C ON C.CPM_NOP=A.CPM_NOP
                        RIGHT JOIN CPPMOD_PBB_SPPT D ON D.CPM_NOP = A.CPM_NOP 
                        LEFT JOIN CPPMOD_TAX_KABKOTA E ON  E.CPC_TK_ID  = D.CPM_OP_KOTAKAB
                        LEFT JOIN CPPMOD_TAX_KECAMATAN F ON F.CPC_TKC_ID  = D.CPM_OP_KECAMATAN
                        LEFT JOIN CPPMOD_TAX_KELURAHAN G ON G.CPC_TKL_ID = D.CPM_OP_KELURAHAN 
                        WHERE D.CPM_NOP='{$nop}' ORDER BY SPPT_TAHUN_PAJAK DESC LIMIT 0,1";
                // print_r($query);exit;
                // print_r($query);exit;
                $result = mysqli_query($conn, $query);
            }
	}
	//print_r($rowcount);
	// print_r($query);exit;


    $data = null;
    while ($row = mysqli_fetch_assoc($result)) {
        // var_dump($row['OP_RW']);exit;
        $njop_bgn=0;
        if($row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN']==""){
            $njop_bgn=0;
        }else{
            $njop_bgn=$row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];
        }
        if($row['CPM_OT_ZONA_NILAI']!=""){
            $znt_tanah=$row['CPM_OT_ZONA_NILAI'];
        }else{
            $znt_tanah="0";
        }
        $year= date('Y');
        $data = $row['WP_NAMA'] . '*' . $row['WP_ALAMAT'] . '*' . $row['WP_KELURAHAN'] . '*' . $row['WP_RT'] . '*' .
                $row['WP_RW'] . '*' . $row['WP_KECAMATAN'] . '*' . $row['WP_KOTAKAB'] . '*' . $row['WP_KODEPOS'] . '*' .
                $row['OP_ALAMAT'] . '*' . $row['OP_KELURAHAN'] . '*' . $row['OP_RT'] . '*' . $row['OP_RW'] . '*' .
                $row['OP_KECAMATAN'] . '*' . $row['OP_KOTAKAB'] . '*' . $row['OP_LUAS_BUMI'] . '*' . ($row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI']) . '*' .
                $row['OP_LUAS_BANGUNAN'] . '*' . $njop_bgn . '*' . $year . '*' . $znt_tanah;
    }
    echo $data;
} else {
    echo "Koneksi Database Gagal" . mysqli_error($conn);
}
?>
