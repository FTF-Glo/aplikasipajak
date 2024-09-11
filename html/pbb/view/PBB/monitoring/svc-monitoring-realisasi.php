<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/PBB/SimpleDB.php");


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

date_default_timezone_set("Asia/Jakarta");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}


$myDBLink = "";
function getRequest(){
     if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil nilai yang dikirim dari JavaScript
        $buku = $_POST["bk"];
        $tahun = $_POST["th"];
        $kecamatan = $_POST["kc"];
        $namakec = $_POST["n"];
        $eperiode = $_POST["eperiode"];
        $eperiode2 = $_POST["eperiode2"];
        $ketetapan = $_POST["ketetapan"];
        $displaypersen = $_POST["displaypersen"];
        $response = array(
            'buku'          => $buku,
            'tahun'         => $tahun,
            'kecamatan'     => $kecamatan,
            'namakec'       => $namakec,
            'eperiode'      => $eperiode,
            'eperiode2'     => $eperiode2,
            'ketetapan'     => $ketetapan,
            'displaypersen' => $displaypersen
        );
    } else {
        // Jika permintaan tidak melalui metode POST, kirim respon error
        http_response_code(405);
        $response = array("message" => "Metode permintaan tidak diizinkan");
    }
   return $response;
}

function headerMonitoringRealisasi($mod, $nama)
{
    global $appConfig, $eperiode, $eperiode2, $kecamatan, $arrBln, $ketetapan;
    $noKec = "";

    if($ketetapan==2){
        $ketetapan = ' SUSULAN';
    }elseif($ketetapan==1){
        $ketetapan = ' MASAL';
    }else{
        $ketetapan = '';
    }

    // echo "masuk";
    // exit;
    // $BULAN = isset($arrBln[$eperiode])? strtoupper($arrBln[$eperiode]) : '';

    //BY 35utech 17/04/2018
    $bulan_ini_idx = date("n", strtotime($eperiode2));
    if ($bulan_ini_idx == "1")
        $bulan_lalu_idx = 12;
    else
        $bulan_lalu_idx  = $bulan_ini_idx - 1;


    $bulan_ini = $arrBln[$bulan_ini_idx];
    $bulan_lalu = $arrBln[$bulan_lalu_idx];


    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }
    $model = ($mod == 0) ? strtoupper($appConfig['LABEL_KELURAHAN']) : strtoupper($appConfig['LABEL_KELURAHAN']);
    $AllCamat = ($mod == 0) ? "<th rowspan=2>KECAMATAN</th>" : "<th rowspan=2>NO</th>";
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }

    if ($kecamatan == "") {
        $noKec = '<th rowspan=2 width=30>No</th>';
    }
    $getRequest = getRequest();
    $html = '<table class="table table-bordered table-striped"><tr><th colspan=22 class=tleft><b>'.$dl.'<b></th></tr>
	  <tr>'.$noKec.'
		'.$AllCamat.'
		<th rowspan=2>'.$model.'</th>
		<th colspan=2>POKOK KETETAPAN<br>TAHUN '.$getRequest['tahun'].'</th>
		<th colspan=3>REALISASI POKOK<br>KETETAPAN TAHUN '.$getRequest['tahun'].'</th>
		<th colspan=3>SISA POKOK<br>KETETAPAN TAHUN '.$getRequest['tahun'].'</th>
	  </tr>
	  <tr>
		<th>SPPT</th>
		<th>JUMLAH<br>(Rp)</th>
		
        <th>SPPT</th>		
        <th>JUMLAH<br>(RP)</th>
        <th>%</th>
                
		<th>SPPT</th>		
        <th>JUMLAH<br>(RP)</th>
        <th>%</th>
      </tr>';
    return $html;
}

// koneksi postgres
function openMysql()
{
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname, $myDBLink);
    return $myDBLink;
}

function closeMysql($con)
{
    mysqli_close($con);
}

function showTableAllNew($mod = 0, $nama = "")
{
    global $appConfig, $kecamatan, $thn;

    $dtketetapan= getKetetapanAll();
    $realisasi  = getRealisasi();//print_r($realisasi);exit;

    $no   = 1;
    $c    = count($dtketetapan); 
    $html = '<div class="tbl-monitoring">';
    $html .= headerMonitoringRealisasi($mod, $nama);

    $subtotal = array('name' => 'JUMLAH', 'ketetapan_sppt' => 0, 'ketetapan_pbb' => 0, 'real_sppt' => 0, 'real_pbb' => 0, 'sisa_sppt' => 0, 'sisa_pbb' => 0);
    $total = array('name' => 'TOTAL','ketetapan_sppt' => 0, 'ketetapan_pbb' => 0, 'real_sppt' => 0, 'real_pbb' => 0, 'sisa_sppt' => 0, 'sisa_pbb' => 0);

    $idKec = '';

    for ($i = 0; $i < $c; $i++) {

        if($idKec!='' && $idKec != substr($dtketetapan[$i]['ID'], 0, 7)){
            $html .= '<tr class="" style="color:#fff; background: linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%) !important;">';
            $html .= ($nama=='') ? "<td colspan=3>".$subtotal['name']."</td>" : "<td colspan=2>".$subtotal['name']."</td>";
            $html .= "<td>".number_format($subtotal['ketetapan_sppt'],0,',','.')."</td>";
            $html .= "<td>".number_format($subtotal['ketetapan_pbb'],0,',','.')."</td>";
            $html .= "<td>".number_format($subtotal['real_sppt'],0,',','.')."</td>";
            $html .= "<td>".number_format($subtotal['real_pbb'],0,',','.')."</td>";
            $realpersen = ($subtotal['ketetapan_pbb'] != 0 && $subtotal['real_pbb'] != 0) ? ($subtotal['real_pbb'] / $subtotal['ketetapan_pbb'] * 100) : 0;
            $realpersen = str_replace('.',',',(float)number_format($realpersen,2,'.',','));
            $html .= "<td>".$realpersen."</td>";
            $html .= "<td>".number_format($subtotal['sisa_sppt'],0,',','.')."</td>";
            $html .= "<td>".number_format($subtotal['sisa_pbb'],0,',','.')."</td>";
            $sisapersen = ($subtotal['ketetapan_pbb'] != 0 && $subtotal['sisa_pbb'] != 0) ? ($subtotal['sisa_pbb'] / $subtotal['ketetapan_pbb'] * 100) : 0;
            $sisapersen = str_replace('.',',',(float)number_format($sisapersen,2,'.',','));
            $html .= "<td>".$sisapersen."</td>";
            $html .= '</tr>';
        }

        $html .= '<tr class="tright">';
        $jmlKetetapan = $dtketetapan[$i]['JML'];
        $pbbKetetapan = $dtketetapan[$i]['JUMLAH'];
        $realisasiJML = isset($realisasi[$dtketetapan[$i]['ID']]) ? $realisasi[$dtketetapan[$i]['ID']]['JML'] : 0;
        $realisasiPOKOK = isset($realisasi[$dtketetapan[$i]['ID']]) ? $realisasi[$dtketetapan[$i]['ID']]['POKOK'] : 0;
        $realisasiPBB = isset($realisasi[$dtketetapan[$i]['ID']]) ? $realisasi[$dtketetapan[$i]['ID']]['JUMLAH'] : 0;
        $realpersen = ($pbbKetetapan != 0 && $realisasiPBB != 0) ? ($realisasiPBB / $pbbKetetapan * 100) : 0;
        $realpersen = str_replace('.',',',(float)number_format($realpersen,2,'.',','));
        
        $sisaJML = $jmlKetetapan - $realisasiJML;
        $sisaPBB = $pbbKetetapan - $realisasiPOKOK;
        $sisapersen = ($pbbKetetapan != 0 && $sisaPBB != 0) ? ($sisaPBB / $pbbKetetapan * 100) : 0;
        $sisapersen = str_replace('.',',',(float)number_format($sisapersen,2,'.',','));



        if ($idKec != substr($dtketetapan[$i]['ID'], 0, 7)) {
            $idKec = substr($dtketetapan[$i]['ID'], 0, 7);
            $html .= "<td class=tright>" . $no . "</td>";
            if($nama=='') $html .= "<td class=tleft>" . $dtketetapan[$i]['KECAMATAN'] . "</td>";
            $no++;
            $subtotal = array('name' => 'JUMLAH', 'ketetapan_sppt' => 0, 'ketetapan_pbb' => 0, 'real_sppt' => 0, 'real_pbb' => 0, 'sisa_sppt' => 0, 'sisa_pbb' => 0);
        } else {
            $html .= "<td></td>";
            if($nama=='') $html .= "<td></td>";
        }
        $html .= "<td class=tleft>" . $dtketetapan[$i]['KELURAHAN'] . "</td>";
        $html .= "<td>" . number_format($jmlKetetapan,0,',','.') . "</td>";
        $html .= "<td>" . number_format($pbbKetetapan,0,',','.') . "</td>";
        $html .= "<td>" . number_format($realisasiJML,0,',','.') . "</td>";
        $html .= "<td>" . number_format($realisasiPBB,0,',','.') . "</td>";
        $html .= "<td>$realpersen</td>";
        $html .= "<td>" . number_format($sisaJML,0,',','.') . "</td>";
        $html .= "<td>" . number_format($sisaPBB,0,',','.') . "</td>";
        $html .= "<td>$sisapersen</td>";
        $html .= '</tr>';

        $subtotal['ketetapan_sppt'] = $subtotal['ketetapan_sppt'] + $jmlKetetapan;
        $subtotal['ketetapan_pbb']  = $subtotal['ketetapan_pbb'] + $pbbKetetapan;
        $subtotal['real_sppt']      = $subtotal['real_sppt'] + $realisasiJML;
        $subtotal['real_pbb']       = $subtotal['real_pbb'] + $realisasiPBB;
        $subtotal['sisa_sppt']      = $subtotal['sisa_sppt'] + $sisaJML;
        $subtotal['sisa_pbb']       = $subtotal['sisa_pbb'] + $sisaPBB;
        
        $total['ketetapan_sppt']    = $total['ketetapan_sppt'] + $jmlKetetapan;
        $total['ketetapan_pbb']     = $total['ketetapan_pbb'] + $pbbKetetapan;
        $total['real_sppt']         = $total['real_sppt'] + $realisasiJML;
        $total['real_pbb']          = $total['real_pbb'] + $realisasiPBB;
        $total['sisa_sppt']         = $total['sisa_sppt'] + $sisaJML;
        $total['sisa_pbb']          = $total['sisa_pbb'] + $sisaPBB;
    }

    $html .= '<tr class="" style="color:#fff;  background: linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%) !important;">';
    $html .= ($nama=='') ? "<td colspan=3>".$subtotal['name']."</td>" : "<td colspan=2>".$subtotal['name']."</td>";
    $html .= "<td>".number_format($subtotal['ketetapan_sppt'],0,',','.')."</td>";
    $html .= "<td>".number_format($subtotal['ketetapan_pbb'],0,',','.')."</td>";
    $html .= "<td>".number_format($subtotal['real_sppt'],0,',','.')."</td>";
    $html .= "<td>".number_format($subtotal['real_pbb'],0,',','.')."</td>";
    $realpersen = ($subtotal['ketetapan_pbb'] != 0 && $subtotal['real_pbb'] != 0) ? ($subtotal['real_pbb'] / $subtotal['ketetapan_pbb'] * 100) : 0;
    $realpersen = str_replace('.',',',(float)number_format($realpersen,2,'.',','));
    $html .= "<td>".$realpersen."</td>";
    $html .= "<td>".number_format($subtotal['sisa_sppt'],0,',','.')."</td>";
    $html .= "<td>".number_format($subtotal['sisa_pbb'],0,',','.')."</td>";
    $sisapersen = ($subtotal['ketetapan_pbb'] != 0 && $subtotal['sisa_pbb'] != 0) ? ($subtotal['sisa_pbb'] / $subtotal['ketetapan_pbb'] * 100) : 0;
    $sisapersen = str_replace('.',',',(float)number_format($sisapersen,2,'.',','));
    $html .= "<td>".$sisapersen."</td>";
    $html .= '</tr>';

    $html .= '<tr><td colspan=11></td></tr>';

    // total semua angka
    if ($kecamatan == "") {
        $html .= '<tr class="tright svrh1 tbold" style="color:#fff">';
        $html .= "<td colspan=3>".$total['name']."</td>";
        $html .= "<td>".number_format($total['ketetapan_sppt'],0,',','.')."</td>";
        $html .= "<td>".number_format($total['ketetapan_pbb'],0,',','.')."</td>";
        $html .= "<td>".number_format($total['real_sppt'],0,',','.')."</td>";
        $html .= "<td>".number_format($total['real_pbb'],0,',','.')."</td>";
        $realpersen = ($total['ketetapan_pbb'] != 0 && $total['real_pbb'] != 0) ? ($total['real_pbb'] / $total['ketetapan_pbb'] * 100) : 0;
        $realpersen = str_replace('.',',',(float)number_format($realpersen,2,'.',','));
        $html .= "<td>".$realpersen."</td>";
        $html .= "<td>".number_format($total['sisa_sppt'],0,',','.')."</td>";
        $html .= "<td>".number_format($total['sisa_pbb'],0,',','.')."</td>";
        $sisapersen = ($total['ketetapan_pbb'] != 0 && $total['sisa_pbb'] != 0) ? ($total['sisa_pbb'] / $total['ketetapan_pbb'] * 100) : 0;
        $sisapersen = str_replace('.',',',(float)number_format($sisapersen,2,'.',','));
        $html .= "<td>".$sisapersen."</td>";
        $html .= '</tr>';
    }

    return $html . "</table>";
}

function getData($where)
{
    global $myDBLink, $kd, $thn, $bulan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["POKOK"] = 0;
    $return["DENDA"] = 0;
    $return["TOTAL"] = 0;
    $whr = "";
    if ($where) {
        $whr = " where {$where}";
    }
    // $query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK, sum(PBB_DENDA) AS DENDA, "
    //         . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT {$whr}";
    $query = "SELECT count(wp_nama) AS WP, SUM(IF(PAYMENT_FLAG = '1', (PBB_TOTAL_BAYAR-PBB_DENDA), (SPPT_PBB_HARUS_DIBAYAR))) AS POKOK, SUM(IF(PAYMENT_FLAG = '1', PBB_SPPT.PBB_DENDA, IFNULL(PBB_DENDA.PBB_DENDA, 0))) AS DENDA, "
        . "SUM(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT LEFT JOIN PBB_DENDA ON PBB_DENDA.NOP = PBB_SPPT.NOP AND PBB_DENDA.SPPT_TAHUN_PAJAK = PBB_SPPT.SPPT_TAHUN_PAJAK {$whr}";
    //echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        //print_r($row);        
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
        $return["DENDA"] = ($row["DENDA"] != "") ? $row["DENDA"] : 0;
        $return["TOTAL"] = ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getKetetapanAll()
{
    global $DBLink, $appConfig, $thn, $qBuku, $speriode, $eperiode, $kecamatan, $ketetapan;
    $myDBLink = openMysql();
    $tahun_tagihan = $appConfig['tahun_tagihan'];

    $where = $wherekec ="";
    $ANDJOIN = '';

    if ($thn != "") {
        $where .= " AND g.SPPT_TAHUN_PAJAK='$thn' ";
        $ANDJOIN = " AND g.SPPT_TAHUN_PAJAK='$thn' ";
    }

    if ($kecamatan != "") {
        $where .= " AND KEL.CPC_TKL_KCID='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }

    if($ketetapan==2){
        
        if($tahun_tagihan==$thn){
            $joinAdd = "INNER JOIN sw_pbb.cppmod_pbb_sppt_susulan fi ON KEL.CPC_TKL_ID = LEFT(fi.CPM_NOP,10) AND KEL.CPC_TKL_KCID = LEFT(fi.CPM_NOP,7)";
            $onWhere = "g.NOP=fi.CPM_NOP";
        }else{
            $joinAdd = "";
            $onWhere = "LEFT(g.NOP,10)=KEL.CPC_TKL_ID";
        }

        $query = "SELECT 
                    ID, 
                    KELURAHAN, 
                    KECAMATAN, 
                    SUM(JML) AS JML, 
                    SUM(JUMLAH) AS JUMLAH
                FROM(	
                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 
                        0 JML, 
                        0 JUMLAH
                    FROM sw_pbb.cppmod_tax_kelurahan KEL
                    INNER JOIN sw_pbb.cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID 
                    $wherekec 
                    GROUP BY KEL.CPC_TKL_ID
    
                UNION ALL

                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
                        COUNT(g.NOP) AS JML,
                        SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
                    FROM sw_pbb.cppmod_tax_kelurahan KEL
                    INNER JOIN sw_pbb.cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                    $joinAdd 
                    INNER JOIN gw_pbb.pbb_sppt g ON $onWhere $ANDJOIN
                    WHERE 1=1 $where $qBuku
                    GROUP BY KEL.CPC_TKL_ID
                ) y
                GROUP BY ID
                ORDER BY KECAMATAN, ID";
                // echo $query.'<br/>';exit;
    }elseif($ketetapan==1){

        if($tahun_tagihan==$thn){
            $joinAdd = "INNER JOIN sw_pbb.cppmod_pbb_sppt_final fi ON KEL.CPC_TKL_ID = LEFT(fi.CPM_NOP,10) AND KEL.CPC_TKL_KCID = LEFT(fi.CPM_NOP,7)";
            $onWhere = "g.NOP=fi.CPM_NOP";
        }else{
            $joinAdd = "";
            $onWhere = "LEFT(g.NOP,10)=KEL.CPC_TKL_ID";
        }

        $query = "SELECT 
                    ID, 
                    KELURAHAN, 
                    KECAMATAN, 
                    SUM(JML) AS JML, 
                    SUM(JUMLAH) AS JUMLAH
                FROM (	
                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 
                        0 JML, 
                        0 JUMLAH
                    FROM sw_pbb.cppmod_tax_kelurahan KEL
                    INNER JOIN sw_pbb.cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID 
                    $wherekec 
                    GROUP BY KEL.CPC_TKL_ID
    
                UNION ALL

                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
                        COUNT(g.NOP) AS JML,
                        SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
                    FROM sw_pbb.cppmod_tax_kelurahan KEL
                    INNER JOIN sw_pbb.cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                    $joinAdd 
                    INNER JOIN gw_pbb.pbb_sppt g ON $onWhere $ANDJOIN
                    WHERE 1=1 $where $qBuku
                    GROUP BY KEL.CPC_TKL_ID
                ) y
                GROUP BY ID
                ORDER BY KECAMATAN, ID";
                // echo $query.'<br/>';exit;
                // if($_SERVER['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.0'){
                //     echo '<pre>';
                //     print_r($query);
                //     exit;
                // }
    }else{

        $where = $wherekec = "";

        if ($thn != "") $where .= " AND g.SPPT_TAHUN_PAJAK='$thn' ";
    
        if ($kecamatan != "") {
            $where .= " AND g.OP_KECAMATAN_KODE='$kecamatan' ";
            $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
        }

        $query = "SELECT 
                    ID, 
                    KELURAHAN, 
                    KECAMATAN, 
                    SUM(JML) AS JML, 
                    SUM(JUMLAH) AS JUMLAH
                FROM(	
                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 
                        0 JML, 
                        0 JUMLAH
                    FROM cppmod_tax_kelurahan KEL
                    JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID 
                    $wherekec 
                    GROUP BY KEL.CPC_TKL_ID
    
                UNION ALL

                    SELECT 
                        KEL.CPC_TKL_ID AS ID,
                        KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
                        KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 
                        COUNT(g.WP_NAMA) AS JML, 
                        SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
                    FROM cppmod_tax_kelurahan KEL
                    JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                    JOIN pbb_sppt g ON KEL.CPC_TKL_ID = g.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=g.OP_KECAMATAN_KODE 
                    WHERE 1=1 $where $qBuku 
                    GROUP BY KEL.CPC_TKL_ID
                    ORDER BY KEL.CPC_TKL_ID
                ) y
                GROUP BY ID
                ORDER BY KECAMATAN, ID";
                // echo $query.'<br/>';exit;
    }
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    $data     = array();
    $i        = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]         = $row["ID"];
        $data[$i]["KECAMATAN"]  = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]  = $row["KELURAHAN"];
        $data[$i]["JML"]        = $row["JML"];
        $data[$i]["JUMLAH"]     = $row["JUMLAH"];

        $i++;
    }
    // print_r($data);
    return $data;
}

function getRealisasi()
{
    global $DBLink, $appConfig, $thn, $qBuku, $eperiode, $eperiode2, $kecamatan, $ketetapan;
    $myDBLink = openMysql();

    $where="";
    $tahun_tagihan = $appConfig['tahun_tagihan'];

    if ($thn != "") {
        $where .= " AND g.SPPT_TAHUN_PAJAK='$thn' ";
    }

    if ($kecamatan != "") {
        $where .= " AND LEFT(g.NOP,7)='$kecamatan' ";
    }

    if($ketetapan==2){
        
        if($tahun_tagihan==$thn){
            $joinAdd = "INNER JOIN sw_pbb.cppmod_pbb_sppt_susulan fi ON g.NOP = fi.CPM_NOP";
            $selectJML = "fi.CPM_NOP";
        }else{
            $joinAdd = "";
            $selectJML = "g.NOP";
        }

        $query="SELECT
                LEFT(g.NOP,10) AS ID,
                COUNT($selectJML) AS JML,
                SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
                SUM(g.PBB_TOTAL_BAYAR) AS JUMLAH
            FROM gw_pbb.pbb_sppt g
            $joinAdd 
            WHERE 1=1 
                AND g.PAYMENT_FLAG='1'
                AND DATE(LEFT(g.PAYMENT_PAID,10))>='$eperiode'
                AND DATE(LEFT(g.PAYMENT_PAID,10))<='$eperiode2'
                $where $qBuku
            GROUP BY LEFT(g.NOP,10)";
                // echo $query.'<br/>';exit;
    }elseif($ketetapan==1){

        if($tahun_tagihan==$thn){
            $joinAdd = "INNER JOIN sw_pbb.cppmod_pbb_sppt_final fi ON g.NOP = fi.CPM_NOP";
            $selectJML = "fi.CPM_NOP";
        }else{
            $joinAdd = "";
            $selectJML = "g.NOP";
        }

        $query="SELECT
                LEFT(g.NOP,10) AS ID,
                COUNT($selectJML) AS JML,
                SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
                SUM(g.PBB_TOTAL_BAYAR) AS JUMLAH
            FROM gw_pbb.pbb_sppt g
            $joinAdd 
            WHERE 1=1 
                AND g.PAYMENT_FLAG='1'
                AND DATE(LEFT(g.PAYMENT_PAID,10))>='$eperiode'
                AND DATE(LEFT(g.PAYMENT_PAID,10))<='$eperiode2'
                $where $qBuku
            GROUP BY LEFT(g.NOP,10)";
            // echo $query.'<br/>';exit;
    }else{

        $where = "";

        if ($thn != "") $where .= " AND g.SPPT_TAHUN_PAJAK='$thn' ";
    
        if ($kecamatan != "") {
            $where .= " AND g.OP_KECAMATAN_KODE='$kecamatan' ";
        }

        $query="SELECT
                LEFT(g.NOP,10) AS ID,
                COUNT(g.WP_NAMA) AS JML,
                SUM(g.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
                SUM(g.PBB_TOTAL_BAYAR) AS JUMLAH
            FROM gw_pbb.pbb_sppt g
            WHERE 1=1 
                AND g.PAYMENT_FLAG='1'
                AND DATE(LEFT(g.PAYMENT_PAID,10))>='$eperiode'
                AND DATE(LEFT(g.PAYMENT_PAID,10))<='$eperiode2'
                $where $qBuku
            GROUP BY LEFT(g.NOP,10)";
        // echo $query.'<br/>';exit;
    }
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    $data     = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $id = $row["ID"];
        unset($row["ID"]);
        $data[$id] = $row;
    }
    // print_r($data);exit;
    return $data;
}

function showTableWithPercentage()
{
    global $kecamatan, $thn, $eperiode, $eperiode2, $qBuku;

    $core = (new SimpleDB())->dbOpen('gw');

    $where = array('1=1');
    if ($thn) {
        $where[] = "A.SPPT_TAHUN_PAJAK = '". $core->dbEscape($thn) ."'";
    }
    if ($kecamatan) {
        $where[] = "A.OP_KECAMATAN_KODE = '". $core->dbEscape($kecamatan) ."'";
    }
    if ($eperiode || $eperiode2) {
        $wherePeriode = array('A.PAYMENT_FLAG = 1');
        if ($eperiode) $wherePeriode[] = "DATE(A.PAYMENT_PAID) >= '". $core->dbEscape($eperiode) ."'";
        if ($eperiode2) $wherePeriode[] = "DATE(A.PAYMENT_PAID) <= '". $core->dbEscape($eperiode2) ."'";
        $where[] = sprintf("((A.PAYMENT_FLAG IS NULL OR A.PAYMENT_FLAG <> 1) OR (%s))", $core->flatten($wherePeriode, ' AND '));
    }
    if ($qBuku) {
        $where[] = '1=1 ' . $qBuku;
    }
    
    $select = array(
        'SUM(A.SPPT_PBB_HARUS_DIBAYAR)                      AS KETETAPAN',
        'SUM(IF(A.PAYMENT_FLAG = 1, A.PBB_TOTAL_BAYAR, 0))  AS REALISASI',
        'SUM(IFNULL(B.PBB_DENDA, 0))                        AS DENDA',
        'IFNULL(KEC.CPC_TKC_KECAMATAN, A.OP_KECAMATAN)      AS KECAMATAN'
    );
    $joinDenda = "LEFT JOIN pbb_denda B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK";
    $joinKecamatan = " LEFT JOIN cppmod_tax_kecamatan KEC ON A.OP_KECAMATAN_KODE = KEC.CPC_TKC_ID";

    $sql = sprintf("SELECT %s FROM pbb_sppt A %s WHERE %s GROUP BY A.OP_KECAMATAN_KODE",
                    $core->flatten($select, ', '),
                    $joinDenda . $joinKecamatan, 
                    $core->flatten($where, ' AND ')); //echo ''; print_r($sql); exit;

    $rows     = $core->dbQuery($sql)->fetchAll();
    $rowCount = count($rows);
    $total    = array('target' => 0, 'realisasi' => 0);

    $btnToPDF = '<button class="btn btn-warning btn-orange" onclick="pdfModelRealisasiPersen()" '. (!$rowCount ? 'disabled' : '') .'>Cetak PDF</button>';
    $btnToExcel = ' <button class="btn btn-primary btn-blue" onclick="excelModelRealisasi(1)" '. (!$rowCount ? 'disabled' : '') .'>Export XLS</button>';

    $html = '<div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-8">';
    $html .= $btnToPDF . $btnToExcel;
    $html .= '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Realisasi</th>
                        <th class="text-center">%%</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
                <tfoot>%s</tfoot>
            </table></div></div>';
    
    $tbody = '';
    $tfoot = '';
    foreach ($rows as $key => $row) {
        $target    = $row['KETETAPAN'] + $row['DENDA'];
        $realisasi = $row['REALISASI'];
        $persen    = $realisasi / $target * 100;

        $tbody .= "<tr>
                <td class='text-center'>". ($key + 1) ."</td>
                <td>". $row['KECAMATAN'] ."</td>
                <td class='text-right'>". number_format($target,0,',','.') ."</td>
                <td class='text-right'>". number_format($realisasi,0,',','.') ."</td>
                <td class='text-right'>". str_replace('.',',',(float)number_format($persen,2,'.',',')) ."</td>
            </tr>";
        
        $total['target'] += $target;
        $total['realisasi'] += $row['REALISASI'];

        if (($key + 1) == $rowCount) {
            $persen = $total['realisasi'] / $total['target'] * 100;
            $tfoot = "<tr>
                <th colspan='2'>Jumlah</th>
                <th class='text-right'>". number_format($total['target'],0,',','.') ."</th>
                <th class='text-right'>". number_format($total['realisasi'],0,',','.') ."</th>
                <th class='text-right'>". str_replace('.',',',(float)number_format($persen,2,'.',',')) ."</th>
            </tr>";
        }
    }

    return sprintf($html, $tbody, $tfoot);
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$arrBln = array(
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
);
//echo $s;

$User             = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig        = $User->GetAppConfig($a);
$kd               = $appConfig['KODE_KOTA'];
$kab              = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan        = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan        = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn              = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama             = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode         = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$eperiode2        = @isset($_REQUEST['eperiode2']) ? $_REQUEST['eperiode2'] : "";
$ketetapan        = @isset($_REQUEST['ketetapan']) ? $_REQUEST['ketetapan'] : "1";
$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$buku             = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";
$bulan_lalu_idx   = 0;

$tampilpersen     = isset($_REQUEST['displaypersen']) && $_REQUEST['displaypersen'] == "1" ? true : false;
// print_r($_REQUEST);exit;
// $arrWhere = array();
// if ($kecamatan !="") {
// array_push($arrWhere,"nop like '{$kecamatan}%'");
// }
// if ($thn!=""){
// array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");  
// array_push($arrWhere,"payment_paid like '{$thn}%'");  
// } 

$qBuku = "";
if ($buku != 0) {
    switch ($buku) {
        case 1:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
            break;
        case 12:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 123:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 1234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 12345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 2:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 23:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 2345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 3:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 34:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 4:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 45:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 5:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
    }
}
//echo $qBuku;
// $where = implode (" AND ",$arrWhere);

if ($tampilpersen) {
    die(showTableWithPercentage());
}

if ($kecamatan == "") {
    echo showTableAllNew();
} else {
    echo showTableAllNew(1, $nama);
}
