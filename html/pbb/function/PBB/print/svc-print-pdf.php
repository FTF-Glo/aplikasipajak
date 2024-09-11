<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/*
 * Developer 	: Jajang Apriansyah 
 * Email 		: jajang@vsi.co.id
 * Tanggal  	: 25-11-2016
 * 
 */
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB/print', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/payment/cdatetime.php");
require_once($sRootPath . "inc/payment/error-messages.php");

require_once($sRootPath . "inc/report/eng-report.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/payment/nid.php");

require_once('printDoubleClass.php');
require_once('printSingleClass.php');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

function getValuesForPrint(&$aTemplateValues, $row, $tahunCetak)
{
    global $appConfig;

    $row['NOP'] = substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);
    $aTemplateValues["SPPT_TAHUN_PAJAK"] = $row['SPPT_TAHUN_PAJAK'];
    $aTemplateValues["NOP"] = $row['NOP'];
    
    $OP_ALAMAT = '';
    $OP_ALAMAT1 = '';
    $WP_ALAMAT = '';
    $WP_ALAMAT1 = '';

    if (strlen($row["OP_ALAMAT"]) > 30) {
        $split = explode(" ", $row["OP_ALAMAT"]);
        $l = count($split);
        $length = $l / 3;
        $a = ($l % 3 == 0) ? 1 : 0;
        for ($i = 0; $i < $length + $a; $i++) {
            $OP_ALAMAT .= $split[$i] . " ";
        }

        for ($i = $length + 1; $i < $l; $i++) {
            $OP_ALAMAT1 .= $split[$i] . " ";
        }

    } else {
        $OP_ALAMAT = $row["OP_ALAMAT"];
        $OP_ALAMAT1 = "";
    }

    $WPALAMATPOTONG = '';
    $WPALAMATPOTONG1 = '';

    if (strlen($row["WP_ALAMAT"]) > 32) {
        $split = explode(" ", $row["WP_ALAMAT"]);
        $l = count($split);
        $length = $l / 2;
        $a = ($l % 2 == 0) ? 1 : 0;
        for ($i = 0; $i < $length + $a; $i++) {
            $WP_ALAMAT .= $split[$i] . " ";
        }

        for ($i = $length + 1; $i < $l; $i++) {
            $WP_ALAMAT1 .= $split[$i] . " ";
        }

        // ALDES
        $MAX = 30;
        $WPALAMAT = $row["WP_ALAMAT"];
        $WPALAMATPOTONG = substr($WPALAMAT, 0, $MAX);
        if (substr($WPALAMATPOTONG, ($MAX - 1), 1) != ' ') {
            $lastSpacePos = strrpos($WPALAMATPOTONG, ' ');
            $WPALAMATPOTONG = substr($WPALAMATPOTONG, 0, $lastSpacePos);
        }
        $WPALAMATPOTONG1 = substr(trim(substr($WPALAMAT, strlen($WPALAMATPOTONG))), 0, $MAX);
    } else {
        $WP_ALAMAT = $row["WP_ALAMAT"];
        $WP_ALAMAT1 = " ";
    }

    if (strlen($row["OP_ALAMAT"]) > 30 && strlen($row["WP_ALAMAT"]) <= 32) {
        $aTemplateValues["ALAMAT1A"] = $OP_ALAMAT;
        $aTemplateValues["ALAMAT1B"] = $row["WP_NAMA"];

        $aTemplateValues["ALAMAT2A"] = $OP_ALAMAT1;
        $aTemplateValues["ALAMAT2B"] = $row["WP_ALAMAT"];

        $aTemplateValues["ALAMAT3A"] = "RT: " . $row['OP_RT'] . " RW: " . $row['OP_RW'];
        $aTemplateValues["ALAMAT3B"] = "RT: " . $row['WP_RT'] . " RW: " . $row['WP_RW'];

        $aTemplateValues["ALAMAT4A"] = $row["OP_KELURAHAN"];
        $aTemplateValues["ALAMAT4B"] = $row["WP_KELURAHAN"];

        $aTemplateValues["ALAMAT5A"] = $row["OP_KECAMATAN"];
        $aTemplateValues["ALAMAT5B"] = $row["WP_KOTAKAB"];

        $aTemplateValues["ALAMAT6A"] = $row["OP_KOTAKAB"];
        $aTemplateValues["ALAMAT6B"] = "";
    } elseif (strlen($row["WP_ALAMAT"]) > 32 && strlen($row["OP_ALAMAT"]) <= 30) {
        $aTemplateValues["ALAMAT1A"] = $row["OP_ALAMAT"];
        $aTemplateValues["ALAMAT1B"] = $row["WP_NAMA"];

        $aTemplateValues["ALAMAT2A"] = "RT: " . $row['OP_RT'] . " RW: " . $row['OP_RW'];
        $aTemplateValues["ALAMAT2B"] = $WP_ALAMAT;

        $aTemplateValues["ALAMAT3A"] = $row["OP_KELURAHAN"];
        $aTemplateValues["ALAMAT3B"] = $WP_ALAMAT1;

        $aTemplateValues["ALAMAT4A"] = $row["OP_KECAMATAN"];
        $aTemplateValues["ALAMAT4B"] = "RT: " . $row['WP_RT'] . " RW: " . $row['WP_RW'];

        $aTemplateValues["ALAMAT5A"] = $row["OP_KOTAKAB"];
        $aTemplateValues["ALAMAT5B"] = $row["WP_KELURAHAN"];

        $aTemplateValues["ALAMAT6A"] = "";
        $aTemplateValues["ALAMAT6B"] = $row["WP_KOTAKAB"];
    } elseif (strlen($row["WP_ALAMAT"]) <= 32 && strlen($row["OP_ALAMAT"]) <= 30) {
        $aTemplateValues["ALAMAT1A"] = $row["OP_ALAMAT"];
        $aTemplateValues["ALAMAT1B"] = $row["WP_NAMA"];

        $aTemplateValues["ALAMAT2A"] = "RT: " . $row['OP_RT'] . " RW: " . $row['OP_RW'];
        $aTemplateValues["ALAMAT2B"] = $row["WP_ALAMAT"];

        $aTemplateValues["ALAMAT3A"] = $row["OP_KELURAHAN"];
        $aTemplateValues["ALAMAT3B"] = "RT: " . $row['WP_RT'] . " RW: " . $row['WP_RW'];

        $aTemplateValues["ALAMAT4A"] = $row["OP_KECAMATAN"];
        $aTemplateValues["ALAMAT4B"] = $row["WP_KELURAHAN"];

        $aTemplateValues["ALAMAT5A"] = $row["OP_KOTAKAB"];
        $aTemplateValues["ALAMAT5B"] = $row["WP_KOTAKAB"];

        $aTemplateValues["ALAMAT6A"] = "";
        $aTemplateValues["ALAMAT6B"] = "";
    } elseif (strlen($row["OP_ALAMAT"]) > 30 && strlen($row["WP_ALAMAT"]) > 32) {
        $aTemplateValues["ALAMAT1A"] = $OP_ALAMAT;
        $aTemplateValues["ALAMAT1B"] = $row["WP_NAMA"];

        $aTemplateValues["ALAMAT2A"] = $OP_ALAMAT1;
        $aTemplateValues["ALAMAT2B"] = $WP_ALAMAT;

        $aTemplateValues["ALAMAT3A"] = "RT: " . $row['OP_RT'] . " RW: " . $row['OP_RW'];
        $aTemplateValues["ALAMAT3B"] = $WP_ALAMAT1;

        $aTemplateValues["ALAMAT4A"] = $row["OP_KELURAHAN"];
        $aTemplateValues["ALAMAT4B"] = "RT: " . $row['WP_RT'] . " RW: " . $row['WP_RW'];

        $aTemplateValues["ALAMAT5A"] = $row["OP_KECAMATAN"];
        $aTemplateValues["ALAMAT5B"] = $row["WP_KELURAHAN"];

        $aTemplateValues["ALAMAT6A"] = $row["OP_KOTAKAB"];
        $aTemplateValues["ALAMAT6B"] = $row["WP_KOTAKAB"];;
    } else {
        $aTemplateValues["ALAMAT1A"] = $row["OP_ALAMAT"];
        $aTemplateValues["ALAMAT1B"] = $row["WP_NAMA"];

        $aTemplateValues["ALAMAT2A"] = "RT: " . $row['OP_RT'] . " RW: " . $row['OP_RW'];
        $aTemplateValues["ALAMAT2B"] = $row["WP_ALAMAT"];

        $aTemplateValues["ALAMAT3A"] = $row["OP_KELURAHAN"];
        $aTemplateValues["ALAMAT3B"] = "RT: " . $row['WP_RT'] . " RW: " . $row['WP_RW'];

        $aTemplateValues["ALAMAT4A"] = $row["OP_KECAMATAN"];
        $aTemplateValues["ALAMAT4B"] = $row["WP_KELURAHAN"];

        $aTemplateValues["ALAMAT5A"] = $row["OP_KOTAKAB"];
        $aTemplateValues["ALAMAT5B"] = $row["WP_KOTAKAB"];

        $aTemplateValues["ALAMAT6A"] = "";
        $aTemplateValues["ALAMAT6B"] = "";
    }

    $aTemplateValues["ALAMAT1A"] = isset($aTemplateValues["ALAMAT1A"]) && $aTemplateValues["ALAMAT1A"] ? $aTemplateValues["ALAMAT1A"] : "";
    $aTemplateValues["ALAMAT1B"] = isset($aTemplateValues["ALAMAT1B"]) && $aTemplateValues["ALAMAT1B"] ? $aTemplateValues["ALAMAT1B"] : "";
    $aTemplateValues["ALAMAT2A"] = isset($aTemplateValues["ALAMAT2A"]) && $aTemplateValues["ALAMAT2A"] ? $aTemplateValues["ALAMAT2A"] : "";
    $aTemplateValues["ALAMAT2B"] = isset($aTemplateValues["ALAMAT2B"]) && $aTemplateValues["ALAMAT2B"] ? $aTemplateValues["ALAMAT2B"] : "";
    $aTemplateValues["ALAMAT3A"] = isset($aTemplateValues["ALAMAT3A"]) && $aTemplateValues["ALAMAT3A"] ? $aTemplateValues["ALAMAT3A"] : "";
    $aTemplateValues["ALAMAT3B"] = isset($aTemplateValues["ALAMAT3B"]) && $aTemplateValues["ALAMAT3B"] ? $aTemplateValues["ALAMAT3B"] : "";
    $aTemplateValues["ALAMAT4A"] = isset($aTemplateValues["ALAMAT4A"]) && $aTemplateValues["ALAMAT4A"] ? $aTemplateValues["ALAMAT4A"] : "";
    $aTemplateValues["ALAMAT4B"] = isset($aTemplateValues["ALAMAT4B"]) && $aTemplateValues["ALAMAT4B"] ? $aTemplateValues["ALAMAT4B"] : "";
    $aTemplateValues["ALAMAT5A"] = isset($aTemplateValues["ALAMAT5A"]) && $aTemplateValues["ALAMAT5A"] ? $aTemplateValues["ALAMAT5A"] : "";
    $aTemplateValues["ALAMAT5B"] = isset($aTemplateValues["ALAMAT5B"]) && $aTemplateValues["ALAMAT5B"] ? $aTemplateValues["ALAMAT5B"] : "";
    $aTemplateValues["ALAMAT6A"] = isset($aTemplateValues["ALAMAT6A"]) && $aTemplateValues["ALAMAT6A"] ? $aTemplateValues["ALAMAT6A"] : "";
    $aTemplateValues["ALAMAT6B"] = isset($aTemplateValues["ALAMAT6B"]) && $aTemplateValues["ALAMAT6B"] ? $aTemplateValues["ALAMAT6B"] : "";

    if (strlen(trim($aTemplateValues["ALAMAT1B"])) > 30) {
        // RT RW = 3B / 4B
        $WPNAMA = $aTemplateValues["ALAMAT1B"];
        $WPALAMAT = $aTemplateValues["ALAMAT1B"];
        $MAX = 30;
        
        $RTRWROW = substr($aTemplateValues["ALAMAT3B"], 0, 3) === "RT:" ? 3 : 4; // 4
        $RTRWIDX = 'ALAMAT' . $RTRWROW . 'B';
        
        $ALAMATROW = 2;
        $ALAMATROW1 = $RTRWROW - 1;
        $ALAMATIDX = 'ALAMAT' . $ALAMATROW . 'B';
        $ALAMATIDX1 = 'ALAMAT' . $ALAMATROW1 . 'B';
        
        $WPNAMAPOTONG = substr($WPNAMA, 0, $MAX);
        if (substr($WPNAMAPOTONG, ($MAX - 1), 1) != ' ') {
            $lastSpacePos = strrpos($WPNAMAPOTONG, ' ');
            $WPNAMAPOTONG = substr($WPNAMAPOTONG, 0, $lastSpacePos);
        }
        $WPNAMAPOTONG1 = substr(trim(substr($WPNAMA, strlen($WPNAMAPOTONG))), 0, $MAX);
        
        if (!empty($WPNAMAPOTONG1)) {
            $aTemplateValues[$RTRWIDX] = (trim(($WPALAMATPOTONG1 ? $WPALAMATPOTONG1 : $aTemplateValues[$ALAMATIDX1])) . " RT:{$row['WP_RT']}/{$row['WP_RW']}");
            if ($ALAMATROW != $ALAMATROW1) {
                $aTemplateValues[$ALAMATIDX1] = ($WPALAMATPOTONG ? $WPALAMATPOTONG :$aTemplateValues[$ALAMATIDX]);
            }
            $aTemplateValues[$ALAMATIDX] = $WPNAMAPOTONG1;
            $aTemplateValues['ALAMAT1B'] = $WPNAMAPOTONG;
        }
    }

    $aTemplateValues["OP_RT"] = $row['OP_RT'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KELURAHAN"] = $row['OP_KELURAHAN'];
    $aTemplateValues["OP_KECAMATAN"] = $row['OP_KECAMATAN'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KOTAKAB"] = $row['OP_KOTAKAB'];

    $aTemplateValues["WP_NAMA"] = $row['WP_NAMA'];
    $aTemplateValues["WP_ALAMAT"] = $row['WP_ALAMAT'];
    $aTemplateValues["WP_RT"] = $row['WP_RT'];
    $aTemplateValues["WP_RW"] = $row['WP_RW'];
    $aTemplateValues["WP_KELURAHAN"] = $row['WP_KELURAHAN'];
    $aTemplateValues["WP_KECAMATAN"] = $row['WP_KECAMATAN'];
    $aTemplateValues["WP_KOTAKAB"] = $row['WP_KOTAKAB'];
    $aTemplateValues["WP_KODEPOS"] = $row['WP_KODEPOS'];

    $OP_LUAS_TANAH_VIEW = '0';
    if (strrchr($row['OP_LUAS_BUMI'], '.') != '') {
        $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 2, ',', '.');
    } else $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 0, ',', '.');
    $aTemplateValues["OP_LUAS_BUMI"] = str_pad($OP_LUAS_TANAH_VIEW, 0, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_KELAS_BUMI"] = str_pad(number_format($row['OP_KELAS_BUMI'], 0, '', '.'), 5, " ", STR_PAD_LEFT);;
    $aTemplateValues["OP_NJOP_BUMI_M2"] = str_pad(number_format($row['OP_NJOP_BUMI_M2'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP_BUMI"] = str_pad(number_format($row['OP_NJOP_BUMI'], 0, '', '.'), 18, " ", STR_PAD_LEFT);

    if ($row['OP_LUAS_BUMI_BERSAMA'] != null && $row['OP_LUAS_BANGUNAN_BERSAMA'] != null) {
        $aTemplateValues["TITLE_BANGUNAN_BER"] = 'BANGUNAN BERSAMA';
        $aTemplateValues["OP_LUAS_BANGUNAN_BER"] = str_pad(number_format($row['OP_LUAS_BANGUNAN_BERSAMA'], 0, '', '.'), 6, " ", STR_PAD_LEFT);
        $aTemplateValues["OP_KELAS_BANGUNAN_BER"] = $row['OP_KELAS_BANGUNAN_BERSAMA'];
        $aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
        $aTemplateValues["OP_NJOP_BANGUNAN_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

        $aTemplateValues["TITLE_BUMI_BER"] = 'BUMI BERSAMA';
        $aTemplateValues["OP_LUAS_BUMI_BER"] = str_pad(number_format($row['OP_LUAS_BUMI_BERSAMA'], 0, '', '.'), 10, " ", STR_PAD_LEFT);
        $aTemplateValues["OP_KELAS_BUMI_BER"] = $row['OP_KELAS_BUMI_BERSAMA'];
        $aTemplateValues["OP_NJOP_BUMI_M2_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
        $aTemplateValues["OP_NJOP_BUMI_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    } else {
        $aTemplateValues["TITLE_BANGUNAN_BER"] = ' ';
        $aTemplateValues["OP_LUAS_BANGUNAN_BER"] = ' ';
        $aTemplateValues["OP_KELAS_BANGUNAN_BER"] = ' ';
        $aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = ' ';
        $aTemplateValues["OP_NJOP_BANGUNAN_BER"] = ' ';

        $aTemplateValues["TITLE_BUMI_BER"] = ' ';
        $aTemplateValues["OP_LUAS_BUMI_BER"] = ' ';
        $aTemplateValues["OP_KELAS_BUMI_BER"] = ' ';
        $aTemplateValues["OP_NJOP_BUMI_M2_BER"] = ' ';
        $aTemplateValues["OP_NJOP_BUMI_BER"] = ' ';
    }
    $aTemplateValues["TITLE_BANGUNAN"] = 'BANGUNAN';
    $OP_LUAS_BANGUNAN_VIEW = '0';
    if (strrchr($row['OP_LUAS_BANGUNAN'], '.') != '') {
        $OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'], 2, ',', '.');
    } else $OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'], 0, ',', '.');
    $aTemplateValues["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN_VIEW, 0, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_KELAS_BANGUNAN"] = str_pad(number_format($row['OP_KELAS_BANGUNAN'], 0, '', '.'), 8, " ", STR_PAD_LEFT);;
    $aTemplateValues["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 18, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 68, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    // $aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJKP'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'], 0, '', '.');

    if ($row['SPPT_TAHUN_PAJAK'] > 2023) {
        if ($row['OP_NJOP'] - $row['OP_NJOPTKP'] < 0) {
            $aTemplateValues["OP_TOTAL_NJOP"] = str_pad(number_format(0, 0, '', '.'), 0, " ", STR_PAD_LEFT);
        }else{
            $aTemplateValues["OP_TOTAL_NJOP"] = str_pad(number_format($row['OP_NJOP'] - $row['OP_NJOPTKP'], 0, '', '.'), 0, " ", STR_PAD_LEFT);
        }
        if ($row['OP_NJOP'] - $row['OP_NJOPTKP'] >= 1000000000000) {
            $aTemplateValues["TOTAL_NJKP"] = "90% X ";
        } elseif ($row['OP_NJOP'] - $row['OP_NJOPTKP'] >= 1000000000) {
            $aTemplateValues["TOTAL_NJKP"] = "70% X ";
        } else {
            $aTemplateValues["TOTAL_NJKP"] = "40% X ";
        }
        $aTemplateValues["OP_TOTAL_NJOP"] = ' ';
        $aTemplateValues["TOTAL_NJKP"] = ' ';

        $aTemplateValues["OP_NJKP"] = number_format($row['OP_NJOP']-$row['OP_NJOPTKP'] , 0, '', '.');
    }else{
        $aTemplateValues["TOTAL_NJKP"] = " ";
        $aTemplateValues["OP_TOTAL_NJOP"] = " ";
        
        $aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJOP']-$row['OP_NJOPTKP'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    }

    $aTemplateValues["OP_TARIF"] = rtrim($row['OP_TARIF'], "0");

    $aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = ' ';
    $aTemplateValues["SPPT_PBB_PENGURANGAN"] = ' ';
    $aTemplateValues["TITLE_PENGURANGAN1"] = ' ';
    $aTemplateValues["TITLE_PENGURANGAN2"] = ' ';

    $aTemplateValues["SELISIH"] = ' ';
    $aTemplateValues["TITLE_SELISIH"] = ' ';

    $SPPT_PBB_SEBELUM_PENGURANGAN = ($row['OP_TARIF'] / 100) * $row['OP_NJKP'];

    if ($row['SPPT_PBB_PENGURANGAN'] > 0) {
        $SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'] - $row['SPPT_PBB_PENGURANGAN'];
        $aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = $row['SPPT_PBB_PERSEN_PENGURANGAN'];
        $aTemplateValues["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
        $aTemplateValues["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
    }

    if ($row['SELISIH'] > 0) {
        if ($row['PAYMENT_TYPE'] == "1") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT KURANG BAYAR';
        } elseif ($row['PAYMENT_TYPE'] == "2") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT LEBIH BAYAR';
        }
    }

    //Jika Cetak SPPT Perhitungan Lama
    if ($tahunCetak < '2014') {
        $NJKP_OLD = $row['OP_NJOP'] - $row['OP_NJOPTKP'];
        $SPPT_PBB_HASIL_PERHITUNGAN_OLD = ($NJKP_OLD * ($row['OP_NJKP'] / 100)) * 0.005;
        $aTemplateValues["SPPT_PBB_HASIL_PERHITUNGAN_OLD"] = str_pad(number_format($SPPT_PBB_HASIL_PERHITUNGAN_OLD, 0, '', '.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD"] = str_pad(number_format($NJKP_OLD, 0, '', '.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD_TANPA_PADDING"] = number_format($NJKP_OLD, 0, '', '.');
    }
    $aTemplateValues["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
    $aTemplateValues["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
    $aTemplateValues["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
    $aTemplateValues["SPPT_DOC_ID"] = generateNoReg($row); //$row['SPPT_DOC_ID'];
    $aTemplateValues["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
    $aTemplateValues["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
    $aTemplateValues["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
    $aTemplateValues["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
    $aTemplateValues["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
    $aTemplateValues["NAMA_PEJABAT_SK2_JABATAN"] = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
    $aTemplateValues["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);
    $aTemplateValues["AKUN"] = $row['CPC_KD_AKUN'];
    $aTemplateValues["SEKTOR"] = $row['SEKTOR'];

    return true;
} // end of

function generateNoReg($row)
{
    $noReg = "#";
    $noReg .= date('dmyHis');
    $noReg .= substr($row['SPPT_PBB_HARUS_DIBAYAR'], 0, 1);
    $noReg .= substr($row['WP_NAMA'], 0, 1);
    $noReg .= "A"; //huruf awal znt
    $noReg .= substr($row['WP_NAMA'], -1);
    $noReg .= "Z"; //huruf akhir znt
    $noReg .= strlen($row['SPPT_PBB_HARUS_DIBAYAR']);
    $noReg .= "01"; //2 digit kode bangunan
    $noReg .= "AS"; //2 digit salinan atau bukan
    $noReg .= "01"; //2 digit penetapan ke berapa
    $noReg .= "#";
    return $noReg;
}

// aldes edit
function printRequest($nop, &$no_ktp, $tahunCetak, $returnTemplate = false, $newTemplate = false)
{
    global $DBLink, $tTime, $modConfig, $sRootPath, $sdata, $Setting, $prm, $appConfig;

    if ($tahunCetak >= 2024){
        $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-new.xml");
    }elseif($tahunCetak >= 2014){
        $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-qrcode.xml");
    }else{
        $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-old.xml");
    }
    $driver = "epson";

    if($newTemplate) {
        $sTemplateFile = $sRootPath . "function/PBB/print/svc-print-lamsel-single.xml";
    }

    $re = new reportEngine($sTemplateFile, $driver);
    // echo $appConfig['tahun_tagihan'].',';exit;
    // echo $tahunCetak;
    // echo "<br>";

    if ($tahunCetak == $appConfig['tahun_tagihan']) {
        $table = 'cppmod_pbb_sppt_current';
    } else {
        $table = "cppmod_pbb_sppt_cetak_$tahunCetak";
    }

    // if($tahunCetak == date(Y)) $table = 'cppmod_pbb_sppt_current';
    // else $table = "cppmod_pbb_sppt_cetak_$tahunCetak";


    $strHTML = '';
    $strHTMLSingle = '';
    $query = "SELECT 
                    A.SPPT_TAHUN_PAJAK, A.NOP,

                    TRIM(CONCAT(A.OP_ALAMAT,' ',IFNULL(D.CPM_OP_NOMOR,''))) AS OP_ALAMAT,

                    /* A.OP_ALAMAT, */
                    A.OP_RT,
                    A.OP_RW, 
                    A.OP_KELURAHAN, 
                    A.OP_KECAMATAN, 
                    A.OP_KOTAKAB,
                    
                    IF(E.CPM_WP_NAMA IS NOT NULL AND E.CPM_WP_NAMA!='', E.CPM_WP_NAMA, A.WP_NAMA ) AS WP_NAMA,
                    IF(E.CPM_WP_ALAMAT IS NOT NULL AND TRIM(E.CPM_WP_ALAMAT)!='', 
                        IF(LOCATE(TRIM(E.BLOK_KAV_NO_WP), E.CPM_WP_ALAMAT)>0, 
                            TRIM(E.CPM_WP_ALAMAT),
                            CONCAT(TRIM(E.CPM_WP_ALAMAT),' ',TRIM(IFNULL(E.BLOK_KAV_NO_WP,''))) 
                        ), 
                        A.WP_ALAMAT
                    ) AS WP_ALAMAT,
                    IF(E.CPM_WP_RT IS NOT NULL AND E.CPM_WP_RT!='', E.CPM_WP_RT, A.WP_RT ) AS WP_RT,
                    IF(E.CPM_WP_RW IS NOT NULL AND E.CPM_WP_RW!='', E.CPM_WP_RW, A.WP_RW ) AS WP_RW,
                    IF(E.CPM_WP_KELURAHAN IS NOT NULL AND E.CPM_WP_KELURAHAN!='', E.CPM_WP_KELURAHAN, A.WP_KELURAHAN ) AS WP_KELURAHAN,
                    IF(E.CPM_WP_KECAMATAN IS NOT NULL AND E.CPM_WP_KECAMATAN!='', E.CPM_WP_KECAMATAN, A.WP_KECAMATAN ) AS WP_KECAMATAN,
                    IF(E.CPM_WP_KOTAKAB IS NOT NULL AND E.CPM_WP_KOTAKAB!='', E.CPM_WP_KOTAKAB, A.WP_KOTAKAB ) AS WP_KOTAKAB,
                    IF(E.CPM_WP_KODEPOS IS NOT NULL AND E.CPM_WP_KODEPOS!='', E.CPM_WP_KODEPOS, A.WP_KODEPOS ) AS WP_KODEPOS,
                    /* 
                    IFNULL(E.CPM_WP_NAMA,A.WP_NAMA) AS WP_NAMA, IFNULL(E.CPM_WP_ALAMAT,A.WP_ALAMAT) AS WP_ALAMAT, IFNULL(E.CPM_WP_RT,A.WP_RT) AS WP_RT, IFNULL(E.CPM_WP_RW, A.WP_RW) AS WP_RW, IFNULL(E.CPM_WP_KELURAHAN, A.WP_KELURAHAN) AS WP_KELURAHAN, IFNULL(E.CPM_WP_KECAMATAN, A.WP_KECAMATAN) AS WP_KECAMATAN, IFNULL(E.CPM_WP_KOTAKAB, A.WP_KOTAKAB) AS WP_KOTAKAB, IFNULL(E.CPM_WP_KODEPOS, A.WP_KODEPOS) AS WP_KODEPOS,
                    */
                    A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
                    A.OP_NJOPTKP,A.OP_NJKP,
                    A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, 
                    A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID, A.OP_TARIF,
                    A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
                    A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
                    A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
                    A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA, CPM_WP_NO_KTP,
                    C.CPC_NM_SEKTOR, C.CPC_KD_AKUN, IF(B.CPC_TKL_KDSEKTOR='10','PEDESAAN','PERKOTAAN') AS SEKTOR,
                    IFNULL(F.PBB_DENDA,0) AS PBB_DENDA
                    FROM $table A 
                    LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
                    LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
                    LEFT JOIN (
                        SELECT CPM_NOP, CPM_WP_NO_KTP, CPM_OP_NOMOR FROM cppmod_pbb_sppt WHERE CPM_NOP='$nop'
                        UNION ALL 
                        SELECT CPM_NOP, CPM_WP_NO_KTP, CPM_OP_NOMOR FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'
                        UNION ALL 
                        SELECT CPM_NOP, CPM_WP_NO_KTP, CPM_OP_NOMOR FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='$nop'
                    ) D ON A.NOP=D.CPM_NOP
                    LEFT JOIN cppmod_pbb_wajib_pajak E ON E.CPM_WP_ID=D.CPM_WP_NO_KTP
                    LEFT JOIN gw_pbb.pbb_denda F ON F.NOP = A.NOP AND F.SPPT_TAHUN_PAJAK = A.SPPT_TAHUN_PAJAK
                    WHERE A.NOP='$nop' AND A.SPPT_TAHUN_PAJAK='$tahunCetak'";
    // echo $query;exit;
    $result = mysqli_query($DBLink, $query);
    if ($row = mysqli_fetch_array($result)) {
        if ($tahunCetak == $appConfig['tahun_tagihan']) {
            mysqli_query($DBLink, "UPDATE $table set TGL_CETAK = '" . date("Y-m-d") . "' where NOP = '" . $nop . "' and SPPT_TAHUN_PAJAK = '" . $row['SPPT_TAHUN_PAJAK'] . "'");
        }

        if ($row['OP_LUAS_BUMI'] > 0) {
            $row['OP_NJOP_BUMI_M2'] = $row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'];
        }

        if ($row['OP_LUAS_BANGUNAN'] > 0)
            $row['OP_NJOP_BANGUNAN_M2'] = $row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];
        if ($row['OP_LUAS_BUMI_BERSAMA'] > 0)
            $row['OP_NJOP_BUMI_M2_BERSAMA'] = $row['OP_NJOP_BUMI_BERSAMA'] / $row['OP_LUAS_BUMI_BERSAMA'];

        if ($row['OP_LUAS_BANGUNAN_BERSAMA'] > 0)
            $row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = $row['OP_NJOP_BANGUNAN_BERSAMA'] / $row['OP_LUAS_BANGUNAN_BERSAMA'];
    }

    $no_ktp = $row['CPM_WP_NO_KTP'];

    $re = new reportEngine($sTemplateFile, $driver);


    if (GetValuesForPrint($aTemplateValue, $row, $tahunCetak)) {
        $re->ApplyTemplateValue($aTemplateValue);
        if ($driver == "other") {
            $re->Print2OnpaysTXT($printValue);
            $strTXT = $printValue;
        } else {
            $re->Print2TXT($printValue);
            $strTXT = base64_encode($printValue);
        }
        $re->PrintHTML($strHTMLSingle);
        // echo $strHTMLSingle;
        // echo '<pre>';
        // print_r($aTemplateValue);
        // exit;
        if ($returnTemplate) {
            return $aTemplateValue;
        }
    }
    $strHTML .= $strHTMLSingle;

    return $strHTML;
}

/**
 * NEW ALDES
 */

function _singleSidePrintHandler(array $listNop, $tahunCetak, $return = 'html')
{
    global $sRootPath;
// echo "asdas";exit;
    if ($tahunCetak >= 2024) $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-lamsel-single-new.xml");
    elseif ($tahunCetak >= 2014) $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-lamsel-single.xml");
    else $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-old.xml");
    $driver = "epson";

    $re = new printSingle($sTemplateFile, $driver);

    switch ($return) {
        case 'html':
            $re->returnHtml();
            break;
        case 'base64':
            $re->returnBase64();
            break;
        default:
            $re->returnText();
            break;
    }

    if($return == 'html') {
        echo $re->getHtmlHead();
    }

    foreach ($listNop as $key => $nop) {

        // if ($key % 2 != 0) {
        //     continue;
        // }

        $aTemplateValue1 = printRequest($nop, $no_ktp1, $tahunCetak, true);
        $aTemplateValue2 = null;

        $singleSidedOutput = $re->newApplyTemplateValue($aTemplateValue1, $aTemplateValue2);
        echo $singleSidedOutput;
    }

    if($return == 'html') {
        echo $re->getHtmlFoot();
    }
}

function _doubleSidePrintHandler(array $listNop, $tahunCetak, $return = 'html')
{
    global $sRootPath;

    if ($tahunCetak >= '2014') $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-lamsel-single.xml");
    else $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-old.xml");
    $driver = "epson";
    $re = new printDouble($sTemplateFile, $driver);

    switch ($return) {
        case 'html':
            $re->returnHtml();
            break;
        case 'base64':
            $re->returnBase64();
            break;
        default:
            $re->returnText();
            break;
    }

    if($return == 'html') {
        echo $re->getHtmlHead();
    }

    foreach ($listNop as $key => $nop) {

        if ($key % 2 != 0) {
            continue;
        }

        $aTemplateValue1 = printRequest($nop, $no_ktp1, $tahunCetak, true);
        if (isset($listNop[($key + 1)])) {
            $aTemplateValue2 = printRequest($listNop[($key + 1)], $no_ktp2, $tahunCetak, true);
        } else {
            $aTemplateValue2 = null;
        }

        $doubleSidedOutput = $re->newApplyTemplateValue($aTemplateValue1, $aTemplateValue2);
        echo $doubleSidedOutput;
    }

    if($return == 'html') {
        echo $re->getHtmlFoot();
    }
}
// END ALDES

$params = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p         = base64_decode($params);
// echo $p;
$json     = new Services_JSON();
$prm     = $json->decode($p);
// echo $prm;
// exit;
$User     = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$appConfig     = $User->GetAppConfig($prm->appID);
$tahunCetak = $prm->tahun;
$lNOP        = $prm->NOP;

// aldes
$newCetak = $prm->newCetak;
$typePrint = isset($prm->typePrint) ? $prm->typePrint : '';
$printDouble = isset($prm->printDouble) ? $prm->printDouble : false;

$Setting     = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

// aldes
if ($params && $newCetak) {
    if(!$printDouble) {
        _singleSidePrintHandler(explode(',', $lNOP), $tahunCetak, $typePrint);
    }
    else {
        _doubleSidePrintHandler(explode(',', $lNOP), $tahunCetak, $typePrint);
    }
    exit;
}

// // aldes
// if ($params && $newCetak) {
//     $listNOP = explode(',', $lNOP);
//     // echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';
//     // echo '<style>@media print { .page-break {page-break-after: always;} } .selembar{ width: 70cm; } .satu-kertas { width: 35cm; height: 18.5cm; padding:0.4cm 0 0.4cm 0.5cm; position:relative } .satu-kertas div{font-size:12pt}</style>';
//     // echo '<html>';
//     // echo '<head>';
//     echo '<style>span{font-family:"Courier New"}</style>';
//     // echo '</head>';
//     // echo '<body>';
//     // echo '<table style="position:FIXED">';
//     // echo '<tr>';
//     //     // echo '<div class="row selembar">';
//     foreach ($listNOP as $key => $nop) {
//         //     // $data = printRequest($nop, $no_ktp, $tahunCetak, true);

//         //     if($key > 0 && $key % 2 == 0){
//         //         echo '</tr><tr>';
//         //     }
//         //     echo '<td style="width:40cm">'.($key + 1).' WAYAAAA</td>';
//         echo '<span> ';
//         for ($i = 1; $i <= 100; $i++) {
//             echo $i . '</span>';
//         }
//         echo '<br>';
//     }
//     // // echo '</div>';
//     // echo '</tr>';
//     // echo '</table>';
//     // echo '</body>';
//     // echo '</html>';



//     exit;
// }

if ($params) {
    saveToPDF($strHTML, $lNOP, $tahunCetak);
}

function saveToPDF($strHTML, $listNOP, $tahunCetak)
{
    // set style for barcode
    $style = array(
        'border' => 0,
        'vpadding' => '0',
        'hpadding' => '0',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false, //array(255,255,255),
        'module_width' => 1, // width of a single module in points
        'module_height' => 1 // height of a single module in points
    );

    $x2         = 62;
    $y2         = 28;
    // echo $idx; exit;
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('vpost');
    $pdf->SetTitle('Alfa System');
    $pdf->SetSubject('Alfa System spppd');
    $pdf->SetKeywords('Alfa System');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // $pdf->SetMargins(5, 10, 35);
    $pdf->SetMargins(5, 20, 0);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
    $pdf->setCellHeightRatio(0.84);
    $listNOP = explode(',', $listNOP);
    foreach ($listNOP as $nop) {
        $HTML = printRequest($nop, $no_ktp, $tahunCetak);
        $dataBarcode = $nop . "\\" . $tahunCetak;
        $resolution = array(170, 320);
        $pdf->AddPage('P', $resolution);
        $pdf->writeHTML($HTML, true, false, false, false, '');
        // $pdf->write2DBarcode($dataBarcode, 'QRCODE,H', 70, 38, 15, 15, $style, 'N');
        // $pdf->write2DBarcode($no_ktp, 'QRCODE,H', 148, 50, 13, 13, $style, 'N');
        // $pdf->write2DBarcode($dataBarcode, 'QRCODE,H', 92, 164, 15, 15, $style, 'N');
        // a  = aspect ratio (width/height);
        // e  = error correction level (0-8);

        // Macro Control Block options:

        // t  = total number of macro segments;
        // s  = macro segment index (0-99998);
        // f  = file ID;
        // o0 = File Name (text);
        // o1 = Segment Count (numeric);
        // o2 = Time Stamp (numeric);
        // o3 = Sender (text);
        // o4 = Addressee (text);
        // o5 = File Size (numeric);
        // o6 = Checksum (numeric).
        //$pdf->write2DBarcode($dataBarcode, 'PDF417,10:2,2,23,0', 5, 37, 0, 7, $style, 'N');
        //$pdf->write2DBarcode($dataBarcode, 'PDF417,10:2,23,0', 37, 171, 0, 7, $style, 'N');
        // $pdf->write2DBarcode($dataBarcode, 'PDF417,9:1,8', 37, 179, 0, 0, $style, 'N');
    }
    $pdf->SetAlpha(0.3);
    $pdf->Output($nop . '.pdf', 'I');
}
