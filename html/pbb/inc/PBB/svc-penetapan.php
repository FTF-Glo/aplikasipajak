<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// die('exit;');
$penilaianTimeOut = 600;
set_time_limit(800);
date_default_timezone_set("Asia/Jakarta");
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
// tambahan aldes
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
// tambahan aldes -- end
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp('aPBB');
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);
// $dbSpptHistory = new DbSpptHistory($dbSpec);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
// tambahan aldes -- end

//variable for input program:
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$nop = $prm->NOP;
$tahun = $prm->TAHUN;
$tipe = $prm->TIPE;
$susulan = $prm->SUSULAN;
$kelurahan = isset($prm->KELURAHAN) ? $prm->KELURAHAN : '';
$tanggal = $prm->TANGGAL;
$uname = $prm->USER;
$tmp = explode('-', $tanggal);
$tanggal = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
$ServerAddress = $svr_param->ServerAddress;
$ServerPort = $svr_param->ServerPort;
$ServerTimeOut = $penilaianTimeOut; 

/*
    |   TYPE 2 = PENETAPAN Tahun Sekarang
    |   TYPE 3 = PENETAPAN Mundur
*/

$sRequestStream = "{\"PAN\":\"TP\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"" . $kelurahan . "\",\"TIPE\":\"" . $tipe . "\",\"NOP\":\"" . $nop . "\",\"SUSULAN\":\"" . $susulan . "\",\"TANGGAL\":\"" . $tanggal . "\",\"USER\":\"" . $uname . "\"}";

if($tipe==2 && $tahun<=2023){
    die('Jika tahun yg akan ditetapkan 2023 ke bawah, silakan ditetapkan secara Mundur di Menu Penetapan Mundur');
}

$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);
// print_r($bOK);exit;
// echo $ServerAddress."<br>";
// echo $ServerPort."<br>";
// echo $ServerTimeOut."<br>";
// echo $sRequestStream."<br>";
// echo $sResp."<br>";
if ($bOK == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;


    // | ini untuk 2024 ke Atas utk Tabel Current
    if($tipe==2 && $tahun>=2024){
        /*
        | Mencari Batas minimum terhutang ================
        */
        $query="SELECT CTR_AC_VALUE FROM sw_pbb.central_app_config WHERE CTR_AC_KEY = 'minimum_sppt_pbb_terhutang' AND CTR_AC_AID='aPBB'";
        $dbSpec->sqlQueryRow($query, $batasminimum);
        
        $batasminimum = $batasminimum[0]['CTR_AC_VALUE'];
        //================================================
        /*
        | Tarif baru 2024 ke atas ========================
        */
        // $query="SELECT CPM_TRF_TARIF FROM sw_pbb.cppmod_pbb_tarif WHERE CPM_TRF_NILAI_BAWAH>=0 AND CPM_TRF_NILAI_ATAS<=999999999999999 ORDER BY CPM_TRF_ID DESC LIMIT 0,1";
        // $dbSpec->sqlQueryRow($query, $tarif);
        
        // $tarif = (float)$tarif[0]['CPM_TRF_TARIF'];
        // $tarif_= $tarif/100;

        /*
        | Proses mendapatkan NOP secara Group =========
        */
        $nops = explode(',',$nop);
        $temp = [];
        $res = [];
        foreach ($nops as $nop) $temp[] = "'".$nop."'";
        $nops = implode(',',$temp);
        $query="SELECT NOP, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP
                FROM gw_pbb.pbb_sppt 
                WHERE 
                    NOP IN ($nops) AND 
                    SPPT_TAHUN_PAJAK = '$tahun'";

        $dbSpec->sqlQueryRow($query, $res);
        
        $n = count($res);
        if ($n > 0) {
            // Membuat klausa WHERE dengan menggunakan IN untuk menyertakan semua NOP yang dihasilkan dari query
            $nopList = implode(',', array_map(function($row) { return "'" . $row['NOP'] . "'"; }, $res));
        
            // $updateQuery = "UPDATE gw_pbb.pbb_sppt g 
            //                 INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=g.NOP 
            //                 SET g.OP_NJKP=CASE 
            //                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.9
            //                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.7
            //                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)<1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.4
            //                                 ELSE (g.OP_NJOP-g.OP_NJOPTKP)
            //                             END,
            //                     g.SPPT_PBB_HARUS_DIBAYAR = GREATEST(
            //                                                 ROUND(
            //                                                     (CASE 
            //                                                         WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.9
            //                                                         WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.7
            //                                                         WHEN (g.OP_NJOP-g.OP_NJOPTKP)<1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.4
            //                                                         ELSE (g.OP_NJOP-g.OP_NJOPTKP)
            //                                                     END
            //                                                     ) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.002, 0.005)) 
            //                                                 ), 
            //                                                 $batasminimum
            //                                             ),
            //                     g.PAYMENT_FLAG = 0
            //                 WHERE g.NOP IN ($nopList) AND g.SPPT_TAHUN_PAJAK = '$tahun'";
            $updateQuery = "UPDATE gw_pbb.pbb_sppt g 
                            INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=g.NOP 
                            LEFT JOIN sw_pbb.cppmod_pbb_sppt_pengurangan_permanen p ON p.CPM_NOP=g.NOP AND p.CPM_TAHUN_AWAL<='$tahun' AND p.CPM_KTP=f.CPM_WP_NO_KTP 
                            SET g.OP_NJOPTKP= IF(g.OP_LUAS_BANGUNAN<='0', 0, 10000000),
                                g.OP_NJKP= (g.OP_NJOP-(IF(g.OP_LUAS_BANGUNAN<='0', 0, 10000000))),
                                g.SPPT_PBB_HARUS_DIBAYAR = GREATEST(
                                                            IF(p.CPM_PERSEN IS NULL, 
                                                                ROUND((g.OP_NJOP-(IF(g.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(g.OP_NJOP>1000000000, 0.002, 0.0015) ))),
                                                                (ROUND((g.OP_NJOP-(IF(g.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(g.OP_NJOP>1000000000, 0.002, 0.0015) ))) - (ROUND((g.OP_NJOP-(IF(g.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(g.OP_NJOP>1000000000, 0.002, 0.0015) ))) / 100 * p.CPM_PERSEN))
                                                            ), 
                                                            $batasminimum
                                                        ),
                                g.PAYMENT_FLAG = 0
                            WHERE g.NOP IN ($nopList) AND g.SPPT_TAHUN_PAJAK = '$tahun'";
            $dbSpec->sqlQuery($updateQuery);

            // $q="UPDATE sw_pbb.cppmod_pbb_sppt_current c 
            //     INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=c.NOP 
            //     SET c.OP_TARIF = $tarif,
            //         c.OP_NJKP=CASE 
            //                     WHEN (c.OP_NJOP-c.OP_NJOPTKP)>=1000000000000 THEN (c.OP_NJOP-c.OP_NJOPTKP) * 0.9
            //                     WHEN (c.OP_NJOP-c.OP_NJOPTKP)>=1000000000 THEN (c.OP_NJOP-c.OP_NJOPTKP) * 0.7
            //                     WHEN (c.OP_NJOP-c.OP_NJOPTKP)<1000000000 THEN (c.OP_NJOP-c.OP_NJOPTKP) * 0.4
            //                     ELSE (c.OP_NJOP-c.OP_NJOPTKP)
            //                 END,
            //         c.SPPT_PBB_HARUS_DIBAYAR = GREATEST(
            //                                         ROUND(
            //                                             (CASE 
            //                                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.9
            //                                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)>=1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.7
            //                                                 WHEN (g.OP_NJOP-g.OP_NJOPTKP)<1000000000 THEN (g.OP_NJOP-g.OP_NJOPTKP) * 0.4
            //                                                 ELSE (g.OP_NJOP-g.OP_NJOPTKP)
            //                                             END
            //                                             ) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.002, 0.005)) 
            //                                         ), 
            //                                         $batasminimum
            //                                     )
            //     WHERE NOP IN ($nopList) AND SPPT_TAHUN_PAJAK = '$tahun'";
            $q="UPDATE sw_pbb.cppmod_pbb_sppt_current c 
                INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=c.NOP 
                LEFT JOIN sw_pbb.cppmod_pbb_sppt_pengurangan_permanen p ON p.CPM_NOP=c.NOP AND p.CPM_TAHUN_AWAL<='$tahun' AND p.CPM_KTP=f.CPM_WP_NO_KTP 
                SET c.OP_TARIF = IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.1, IF(c.OP_NJOP>1000000000, 0.2, 0.15) ),
                    c.SPPT_PBB_PENGURANGAN = IF(p.CPM_PERSEN IS NULL, 0, (ROUND((c.OP_NJOP-(IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(c.OP_NJOP>1000000000, 0.002, 0.0015) ))) / 100 * p.CPM_PERSEN)),
                    c.SPPT_PBB_PERSEN_PENGURANGAN = IFNULL(p.CPM_PERSEN,0),
                    c.USER_PENETAPAN = '$uname',
                    c.OP_NJOPTKP= IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000),
                    c.OP_NJKP= (c.OP_NJOP-(IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000))),
                    c.SPPT_PBB_HARUS_DIBAYAR = GREATEST(
                                                    IF(p.CPM_PERSEN IS NULL, 
                                                        ROUND((c.OP_NJOP-(IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(c.OP_NJOP>1000000000, 0.002, 0.0015) ))),
                                                        (ROUND((c.OP_NJOP-(IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(c.OP_NJOP>1000000000, 0.002, 0.0015) ))) - (ROUND((c.OP_NJOP-(IF(c.OP_LUAS_BANGUNAN<='0', 0, 10000000))) * (IF(f.CPM_OT_JENIS='6' OR f.CPM_OT_JENIS='7', 0.001, IF(c.OP_NJOP>1000000000, 0.002, 0.0015) ))) / 100 * p.CPM_PERSEN))
                                                    ),
                                                    $batasminimum
                                                )
                WHERE NOP IN ($nopList) AND SPPT_TAHUN_PAJAK = '$tahun'";
            $dbSpec->sqlQueryRow($q, $res4);
        }
    }

    


    // | ini untuk Penetapan mundur 2023 ke kebawah
    // | ini untuk Penetapan mundur 2023 ke kebawah
    // | ini untuk Penetapan mundur 2023 ke kebawah
    // | ini untuk Penetapan mundur 2023 ke kebawah
    if($tipe==3 && $tahun<=2023){

        // Minimum SPPT PBB Terutang
        $batasminimum = 10000;

        /*
            | Tarif yang lama
            | 0,15 jika NJOP dari 0->1 Milyar
            | 0,2 jika NJOP diatas 1 Milyar (1.000.000.001)
        */
        $tarif1 = 0.15;
        $tarif1_= $tarif1/100;
        $tarif2 = 0.2;
        $tarif2_= $tarif2/100;

        $nops = explode(',',$nop);
        $temp = [];
        $res = [];
        foreach ($nops as $nop) $temp[] = "'".$nop."'";
        $nops = implode(',',$temp);
        $query="SELECT NOP
                FROM gw_pbb.pbb_sppt 
                WHERE 
                    NOP IN ($nops) AND 
                    SPPT_TAHUN_PAJAK = '$tahun'";

        $dbSpec->sqlQueryRow($query, $res);
        
        $n = count($res);
        if ($n > 0) {
            $nopList = implode(',', array_map(function($row) { return "'" . $row['NOP'] . "'"; }, $res));
        
            $updateQuery = "UPDATE gw_pbb.pbb_sppt 
                            SET OP_NJOPTKP = IF(OP_LUAS_BANGUNAN<='0', 0, 10000000),
                                OP_NJKP = (OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))),
                                SPPT_PBB_HARUS_DIBAYAR= CASE 
                                                            WHEN OP_NJOP>=1000000001 THEN GREATEST(ROUND((OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))) * $tarif2_), $batasminimum)
                                                            ELSE GREATEST(ROUND((OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))) * $tarif1_), $batasminimum)
                                                        END
                            WHERE NOP IN ($nopList) AND SPPT_TAHUN_PAJAK = '$tahun'";
            $dbSpec->sqlQuery($updateQuery);

            $tblcetak = 'sw_pbb.cppmod_pbb_sppt_cetak_' . $tahun;
            $q="UPDATE $tblcetak 
                SET OP_NJOPTKP = IF(OP_LUAS_BANGUNAN<='0', 0, 10000000),
                    OP_NJKP = (OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))),
                    SPPT_PBB_HARUS_DIBAYAR= CASE 
                                                WHEN OP_NJOP>=1000000001 THEN GREATEST(ROUND((OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))) * $tarif2_), $batasminimum)
                                                ELSE GREATEST(ROUND((OP_NJOP-(IF(OP_LUAS_BANGUNAN<='0', 0, 10000000))) * $tarif1_), $batasminimum)
                                            END,
                    OP_TARIF =  CASE 
                                    WHEN OP_NJOP>=1000000001 THEN $tarif2
                                    ELSE $tarif1
                                END
                                            
                WHERE NOP IN ($nopList) AND SPPT_TAHUN_PAJAK = '$tahun'";
            $dbSpec->sqlQueryRow($q, $res4);
        }
    }
}
