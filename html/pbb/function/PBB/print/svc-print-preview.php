<?php
// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);
// echo "123";
// exit;
/*
Developer 	: Jajang Apriansyah 
Email 		: jajang@vsi.co.id
Tanggal  	: 25-11-2016

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
// QR Library Milon Added By d3Di
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/DNS2D.php");
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/QRcode.php");

use \Milon\Barcode\DNS2D;


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
    } else {
        $WP_ALAMAT = $row["WP_ALAMAT"];
        $WP_ALAMAT1 = " ";
    }

    if (strlen($row["OP_ALAMAT"]) > 30 && strlen($row["WP_ALAMAT"]) < 33) {
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
    } elseif (strlen($row["WP_ALAMAT"]) > 32 && strlen($row["OP_ALAMAT"]) < 31) {
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
    } elseif (strlen($row["WP_ALAMAT"]) < 33 && strlen($row["OP_ALAMAT"]) < 31) {
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

    $aTemplateValues["ALAMAT1A"] = ($aTemplateValues["ALAMAT1A"]) ? $aTemplateValues["ALAMAT1A"] : "";
    $aTemplateValues["ALAMAT1B"] = ($aTemplateValues["ALAMAT1B"]) ? $aTemplateValues["ALAMAT1B"] : "";
    $aTemplateValues["ALAMAT2A"] = ($aTemplateValues["ALAMAT2A"]) ? $aTemplateValues["ALAMAT2A"] : "";
    $aTemplateValues["ALAMAT2B"] = ($aTemplateValues["ALAMAT2B"]) ? $aTemplateValues["ALAMAT2B"] : "";
    $aTemplateValues["ALAMAT3A"] = ($aTemplateValues["ALAMAT3A"]) ? $aTemplateValues["ALAMAT3A"] : "";
    $aTemplateValues["ALAMAT3B"] = ($aTemplateValues["ALAMAT3B"]) ? $aTemplateValues["ALAMAT3B"] : "";
    $aTemplateValues["ALAMAT4A"] = ($aTemplateValues["ALAMAT4A"]) ? $aTemplateValues["ALAMAT4A"] : "";
    $aTemplateValues["ALAMAT4B"] = ($aTemplateValues["ALAMAT4B"]) ? $aTemplateValues["ALAMAT4B"] : "";
    $aTemplateValues["ALAMAT5A"] = ($aTemplateValues["ALAMAT5A"]) ? $aTemplateValues["ALAMAT5A"] : "";
    $aTemplateValues["ALAMAT5B"] = ($aTemplateValues["ALAMAT5B"]) ? $aTemplateValues["ALAMAT5B"] : "";
    $aTemplateValues["ALAMAT6A"] = ($aTemplateValues["ALAMAT6A"]) ? $aTemplateValues["ALAMAT6A"] : "";
    $aTemplateValues["ALAMAT6B"] = ($aTemplateValues["ALAMAT6B"]) ? $aTemplateValues["ALAMAT6B"] : "";
    $aTemplateValues["OP_RT"] = $row['OP_RT'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KELURAHAN"] = $row['OP_KELURAHAN'];
    $aTemplateValues["OP_KECAMATAN"] = $row['OP_KECAMATAN'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KOTAKAB"] = $row['OP_KOTAKAB'];

    $aTemplateValues["WP_NAMA"] = str_pad($row['WP_NAMA'], 2, " ", STR_PAD_RIGHT);
    $aTemplateValues["WP_ALAMAT"] = str_pad($row['WP_ALAMAT'], 2, " ", STR_PAD_RIGHT);
    $aTemplateValues["WP_RT"] = str_pad($row['WP_RT'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_RW"] = str_pad($row['WP_RW'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KELURAHAN"] = str_pad($row['WP_KELURAHAN'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KECAMATAN"] = str_pad($row['WP_KECAMATAN'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KOTAKAB"] = str_pad($row['WP_KOTAKAB'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KODEPOS"] = str_pad($row['WP_KODEPOS'], 2, " ", STR_PAD_RIGHT);;

    $OP_LUAS_TANAH_VIEW = '0';
    if (strrchr($row['OP_LUAS_BUMI'], '.') != '') {
        $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 2, ',', '.');
    } else $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 0, ',', '.');
    $aTemplateValues["OP_LUAS_BUMI"] = str_pad($OP_LUAS_TANAH_VIEW, 0, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_KELAS_BUMI"] = $row['OP_KELAS_BUMI'] != null && is_numeric($row['OP_KELAS_BUMI']) ? str_pad(number_format($row['OP_KELAS_BUMI'], 0, '', '.'), 5, " ", STR_PAD_LEFT) : $row['OP_KELAS_BUMI'];
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
    $aTemplateValues["OP_KELAS_BANGUNAN"] = $row['OP_KELAS_BANGUNAN'] != null && is_numeric($row['OP_KELAS_BANGUNAN']) ? str_pad(number_format($row['OP_KELAS_BANGUNAN'], 0, '', '.'), 8, " ", STR_PAD_LEFT) : $row['OP_KELAS_BANGUNAN'];
    $aTemplateValues["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 18, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 68, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJOP']-$row['OP_NJOPTKP'], 0, '', '.'), 68, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'], 0, '', '.');

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
        $aTemplateValues["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
        $aTemplateValues["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
        $aTemplateValues["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
    }

    if (isset($row['SELISIH']) && $row['SELISIH'] > 0) {
        if ($row['PAYMENT_TYPE'] == "1") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT KURANG BAYAR';
        } elseif ($row['PAYMENT_TYPE'] == "2") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT LEBIH BAYAR';
        }
    }

    //Jika Cetak SPPT Perhitungan Lama
    if ($tahunCetak < '2014') {
        $NJKP_OLD = $row['OP_NJOP'] - $row['OP_NJOPTKP'];
        $SPPT_PBB_HASIL_PERHITUNGAN_OLD = ($NJKP_OLD * ($row['OP_NJKP'] / 100)) * 0.005;
        $aTemplateValues["SPPT_PBB_HASIL_PERHITUNGAN_OLD"] = str_pad(number_format($SPPT_PBB_HASIL_PERHITUNGAN_OLD, 0, '', '.'), 36, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD"] = str_pad(number_format($NJKP_OLD, 0, '', '.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD_TANPA_PADDING"] = number_format($NJKP_OLD, 0, '', '.');
    }
    $aTemplateValues["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
    $aTemplateValues["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
    $aTemplateValues["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
    $aTemplateValues["SPPT_DOC_ID"] = generateNoReg($row); //$row['SPPT_DOC_ID'];
    $aTemplateValues["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
    $aTemplateValues["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
    $aTemplateValues["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
    $aTemplateValues["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
    $aTemplateValues["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
    $aTemplateValues["NAMA_PEJABAT_SK2_JABATAN"] = str_pad($appConfig['NAMA_PEJABAT_SK2_JABATAN'], 0, " ", STR_PAD_LEFT);;
    $aTemplateValues["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);
    $aTemplateValues["AKUN"] = $row['CPC_KD_AKUN'];
    $aTemplateValues["SEKTOR"] = $row['SEKTOR'];

    return true;
} // end of

function getValuesForPrints(&$aTemplateValues, $row, $tahunCetak, $rowfive, $numfive)
{
    global $appConfig;

    $row['NOP'] = substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);
    $namadepan = substr($row['WP_NAMA'], 0, 1);
    $namabelakang = substr($row['WP_NAMA'], -1);
    $nops = preg_replace('/[^A-Za-z0-9]/', '', str_replace(' ', '', $row['NOP']));
    $aTemplateValues['SPPT_DOC_ID'] = "#" . date('ymdHis') . '' . substr($nops, 0, 4) . '' . $namadepan . '' . $namabelakang . '' . substr($row['SPPT_PBB_HARUS_DIBAYAR'], 0, 1) . '' . strlen($row['SPPT_PBB_HARUS_DIBAYAR']) . '' . substr($nops, 13, 4) . "#";
    $aTemplateValues["SPPT_TAHUN_PAJAK"] = $row['SPPT_TAHUN_PAJAK'] . " " . $row['SEKTOR'];
    $aTemplateValues["NOP"] = $row['NOP'];
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

    if (strlen($row["WP_ALAMAT"]) > 33) {
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
    } else {
        $WP_ALAMAT = $row["WP_ALAMAT"];
        $WP_ALAMAT1 = " ";
    }

    if (strlen($row["OP_ALAMAT"]) > 30 && strlen($row["WP_ALAMAT"]) < 30) {
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
    } elseif (strlen($row["WP_ALAMAT"]) > 30 && strlen($row["OP_ALAMAT"]) < 30) {
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
    } elseif (strlen($row["WP_ALAMAT"]) < 30 && strlen($row["OP_ALAMAT"]) < 30) {
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
    } elseif (strlen($row["OP_ALAMAT"]) > 30 && strlen($row["WP_ALAMAT"]) > 30) {
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

    $aTemplateValues["ALAMAT1A"] = ($aTemplateValues["ALAMAT1A"]) ? $aTemplateValues["ALAMAT1A"] : "";
    $aTemplateValues["ALAMAT1B"] = ($aTemplateValues["ALAMAT1B"]) ? $aTemplateValues["ALAMAT1B"] : "";
    $aTemplateValues["ALAMAT2A"] = ($aTemplateValues["ALAMAT2A"]) ? $aTemplateValues["ALAMAT2A"] : "";
    $aTemplateValues["ALAMAT2B"] = ($aTemplateValues["ALAMAT2B"]) ? $aTemplateValues["ALAMAT2B"] : "";
    $aTemplateValues["ALAMAT3A"] = ($aTemplateValues["ALAMAT3A"]) ? $aTemplateValues["ALAMAT3A"] : "";
    $aTemplateValues["ALAMAT3B"] = ($aTemplateValues["ALAMAT3B"]) ? $aTemplateValues["ALAMAT3B"] : "";
    $aTemplateValues["ALAMAT4A"] = ($aTemplateValues["ALAMAT4A"]) ? $aTemplateValues["ALAMAT4A"] : "";
    $aTemplateValues["ALAMAT4B"] = ($aTemplateValues["ALAMAT4B"]) ? $aTemplateValues["ALAMAT4B"] : "";
    $aTemplateValues["ALAMAT5A"] = ($aTemplateValues["ALAMAT5A"]) ? $aTemplateValues["ALAMAT5A"] : "";
    $aTemplateValues["ALAMAT5B"] = ($aTemplateValues["ALAMAT5B"]) ? $aTemplateValues["ALAMAT5B"] : "";
    $aTemplateValues["ALAMAT6A"] = ($aTemplateValues["ALAMAT6A"]) ? $aTemplateValues["ALAMAT6A"] : "";
    $aTemplateValues["ALAMAT6B"] = ($aTemplateValues["ALAMAT6B"]) ? $aTemplateValues["ALAMAT6B"] : "";
    $aTemplateValues["OP_RT"] = $row['OP_RT'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KELURAHAN"] = $row['OP_KELURAHAN'];
    $aTemplateValues["OP_KECAMATAN"] = $row['OP_KECAMATAN'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_RW"] = $row['OP_RW'];
    $aTemplateValues["OP_KOTAKAB"] = $row['OP_KOTAKAB'];

    $aTemplateValues["WP_NAMA"] = str_pad($row['WP_NAMA'], 2, " ", STR_PAD_RIGHT);
    $aTemplateValues["WP_ALAMAT"] = str_pad($row['WP_ALAMAT'], 2, " ", STR_PAD_RIGHT);
    $aTemplateValues["WP_RT"] = str_pad($row['WP_RT'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_RW"] = str_pad($row['WP_RW'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KELURAHAN"] = str_pad($row['WP_KELURAHAN'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KECAMATAN"] = str_pad($row['WP_KECAMATAN'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KOTAKAB"] = str_pad($row['WP_KOTAKAB'], 2, " ", STR_PAD_RIGHT);;
    $aTemplateValues["WP_KODEPOS"] = str_pad($row['WP_KODEPOS'], 2, " ", STR_PAD_RIGHT);;

    $OP_LUAS_TANAH_VIEW = '0';
    if (strrchr($row['OP_LUAS_BUMI'], '.') != '') {
        $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 2, ',', '.');
    } else $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 0, ',', '.');
    $aTemplateValues["OP_LUAS_BUMI"] = str_pad($OP_LUAS_TANAH_VIEW, 7, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_KELAS_BUMI"] = $row['OP_KELAS_BUMI'] != null && is_numeric($row['OP_KELAS_BUMI']) ? str_pad(number_format($row['OP_KELAS_BUMI'], 0, '', '.'), 4, " ", STR_PAD_LEFT) : str_pad($row['OP_KELAS_BUMI'], 4, " ", STR_PAD_LEFT);
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
    $aTemplateValues["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN_VIEW, 7, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_KELAS_BANGUNAN"] = $row['OP_KELAS_BANGUNAN'] != null && is_numeric($row['OP_KELAS_BANGUNAN']) ? str_pad(number_format($row['OP_KELAS_BANGUNAN'], 0, '', '.'), 4, " ", STR_PAD_LEFT) : str_pad($row['OP_KELAS_BANGUNAN'], 4, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 18, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 68, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJOP']-$row['OP_NJOPTKP'], 0, '', '.'), 68, " ", STR_PAD_LEFT);
    $aTemplateValues["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'], 0, '', '.');

    $aTemplateValues["OP_TARIF"] = rtrim($row['OP_TARIF'], "0") . ' %   x  '. number_format($row['OP_NJKP'], 0, '', '.') . '         ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');

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
        $aTemplateValues["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
        $aTemplateValues["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
        $aTemplateValues["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
    }

    if (isset($row['SELISIH']) && $row['SELISIH'] > 0) {
        if ($row['PAYMENT_TYPE'] == "1") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT KURANG BAYAR';
        } elseif ($row['PAYMENT_TYPE'] == "2") {
            $aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_SELISIH"] = 'SPPT LEBIH BAYAR';
        }
    }

    //Jika Cetak SPPT Perhitungan Lama
    if ($tahunCetak < '2014') {
        $NJKP_OLD = $row['OP_NJOP'] - $row['OP_NJOPTKP'];
        $SPPT_PBB_HASIL_PERHITUNGAN_OLD = ($NJKP_OLD * ($row['OP_NJKP'] / 100)) * 0.005;
        $aTemplateValues["SPPT_PBB_HASIL_PERHITUNGAN_OLD"] = str_pad(number_format($SPPT_PBB_HASIL_PERHITUNGAN_OLD, 0, '', '.'), 36, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD"] = str_pad(number_format($NJKP_OLD, 0, '', '.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["NJKP_OLD_TANPA_PADDING"] = number_format($NJKP_OLD, 0, '', '.');
    }
    $aTemplateValues["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.'), 36, " ", STR_PAD_LEFT);
    $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
    $aTemplateValues["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
    // $aTemplateValues["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
    $aTemplateValues["SPPT_TANGGAL_TERBIT"] = date('d') . ' ' . strtoupper(GetIndonesianMonthShort((int)date('m') - 1)) . ' ' . date('Y');
    //$aTemplateValues["SPPT_DOC_ID"] = generateNoReg($row); //$row['SPPT_DOC_ID'];
    $aTemplateValues["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
    $aTemplateValues["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
    $aTemplateValues["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
    $aTemplateValues["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
    $aTemplateValues["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
    $aTemplateValues["NAMA_PEJABAT_SK2_JABATAN"] = str_pad($appConfig['NAMA_PEJABAT_SK2_JABATAN'], 0, " ", STR_PAD_LEFT);;
    $aTemplateValues["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);
    $aTemplateValues["AKUN"] = $row['CPC_KD_AKUN'];
    $aTemplateValues["SEKTOR"] = $row['SEKTOR'];

    $aTemplateValues["TAHUN_1"] = '';
    $aTemplateValues["POKOK_1"] = '';
    $aTemplateValues["DENDA_1"] = '';
    $aTemplateValues["JUMLAH_1"] = '';
    $aTemplateValues["KETERANGAN_1"] = '';

    $aTemplateValues["TAHUN_2"] = '';
    $aTemplateValues["POKOK_2"] = '';
    $aTemplateValues["DENDA_2"] = '';
    $aTemplateValues["JUMLAH_2"] = '';
    $aTemplateValues["KETERANGAN_2"] = '';

    $aTemplateValues["TAHUN_3"] = '';
    $aTemplateValues["POKOK_3"] = '';
    $aTemplateValues["DENDA_3"] = '';
    $aTemplateValues["JUMLAH_3"] = '';
    $aTemplateValues["KETERANGAN_3"] = '';

    $aTemplateValues["TAHUN_4"] = '';
    $aTemplateValues["POKOK_4"] = '';
    $aTemplateValues["DENDA_4"] = '';
    $aTemplateValues["JUMLAH_4"] = '';
    $aTemplateValues["KETERANGAN_4"] = '';

    $aTemplateValues["TAHUN_5"] = '';
    $aTemplateValues["POKOK_5"] = '';
    $aTemplateValues["DENDA_5"] = '';
    $aTemplateValues["JUMLAH_5"] = '';
    $aTemplateValues["KETERANGAN_5"] = '';

    if ($numfive > 0) {
        for ($x = 0; $x < $numfive; $x++) {
            $aTemplateValues["TAHUN_" . ($x + 1)] = $rowfive["TAHUN_" . ($x + 1)];
            $aTemplateValues["POKOK_" . ($x + 1)] = $rowfive["POKOK_" . ($x + 1)];
            $aTemplateValues["DENDA_" . ($x + 1)] = $rowfive["DENDA_" . ($x + 1)];
            $aTemplateValues["JUMLAH_" . ($x + 1)] = $rowfive["JUMLAH_" . ($x + 1)];
            $aTemplateValues["KETERANGAN_" . ($x + 1)] = $rowfive["KETERANGAN_" . ($x + 1)];
        }
    }

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

function printRequest($nop, &$no_ktp, $tahunCetak)
{
    global $DBLink, $tTime, $modConfig, $sRootPath, $sdata, $Setting, $prm, $appConfig;

    if ($tahunCetak >= '2014') $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-qrcode.xml");
    else $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print-old.xml");
    $driver = "epson";

    $re = new reportEngine($sTemplateFile, $driver);
    // echo $appConfig['tahun_tagihan'];
    // echo $tahunCetak;
    // echo "<br>";
    
    if ($tahunCetak == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
    else $table = "cppmod_pbb_sppt_cetak_$tahunCetak";

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
                    C.CPC_NM_SEKTOR, C.CPC_KD_AKUN, IF(B.CPC_TKL_KDSEKTOR='10','PEDESAAN','PERKOTAAN') AS SEKTOR
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
                    WHERE A.NOP='$nop'";
    // echo $query;exit;
    $result = mysqli_query($DBLink, $query);
    if ($row = mysqli_fetch_array($result)) {
        if (floatval($row['OP_LUAS_BUMI'])) {
            if ($row['OP_LUAS_BUMI'] == 0) {
                $row['OP_NJOP_BUMI_M2'] = 0;
            } else {
                $row['OP_NJOP_BUMI_M2'] = $row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'];
            }

            if ($row['OP_LUAS_BANGUNAN'] == 0) {
                $row['OP_NJOP_BANGUNAN_M2'] = 0;
            } else {
                $row['OP_NJOP_BANGUNAN_M2'] = $row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];
            }
        } else {
            $row['OP_NJOP_BUMI_M2'] = 0;
            $row['OP_NJOP_BANGUNAN_M2'] = 0;
        }

        if (floatval($row['OP_LUAS_BUMI_BERSAMA']) > 0) {

            $row['OP_NJOP_BUMI_M2_BERSAMA'] = $row['OP_NJOP_BUMI_BERSAMA'] / $row['OP_LUAS_BUMI_BERSAMA'];
            $row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = $row['OP_NJOP_BANGUNAN_BERSAMA'] / $row['OP_LUAS_BANGUNAN_BERSAMA'];
        } else {
            $row['OP_NJOP_BUMI_M2_BERSAMA'] = 0;
            $row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = 0;
        }
    }

    $no_ktp = $row['CPM_WP_NO_KTP'];

    $re = new reportEngine($sTemplateFile, $driver);

    $last5year = null;

    for ($x = $row['SPPT_TAHUN_PAJAK'] - 1; $x > $row['SPPT_TAHUN_PAJAK'] - 6; $x--) {
        $last5year .= "'" . $x . "'";
        if ($x != $row['SPPT_TAHUN_PAJAK'] - 5) {
            $last5year .= ', ';
        }
    }

    if ($last5year != null) {
        $queryfive = "SELECT 
                    A.SPPT_TAHUN_PAJAK, A.NOP,
                    A.OP_ALAMAT,A.OP_RT,A.OP_RW, A.OP_KELURAHAN, A.OP_KECAMATAN, A.OP_KOTAKAB,
                    IFNULL(E.CPM_WP_NAMA,A.WP_NAMA) AS WP_NAMA, IFNULL(E.CPM_WP_ALAMAT,A.WP_ALAMAT) AS WP_ALAMAT, IFNULL(E.CPM_WP_RT,A.WP_RT) AS WP_RT, IFNULL(E.CPM_WP_RW, A.WP_RW) AS WP_RW, IFNULL(E.CPM_WP_KELURAHAN, A.WP_KELURAHAN) AS WP_KELURAHAN, IFNULL(E.CPM_WP_KECAMATAN, A.WP_KECAMATAN) AS WP_KECAMATAN, IFNULL(E.CPM_WP_KOTAKAB, A.WP_KOTAKAB) AS WP_KOTAKAB, IFNULL(E.CPM_WP_KODEPOS, A.WP_KODEPOS) AS WP_KODEPOS,
                    A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
                    A.OP_NJOPTKP,A.OP_NJKP,
                    A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, 
                    A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID, A.OP_TARIF,
                    A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
                    A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
                    A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
                    A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA, CPM_WP_NO_KTP,
                    C.CPC_NM_SEKTOR, C.CPC_KD_AKUN, IF(B.CPC_TKL_KDSEKTOR='10','PEDESAAN','PERKOTAAN') AS SEKTOR
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
                    WHERE A.NOP='$nop' and A.SPPT_TAHUN_PAJAK in (" . $last5year . ")";
    }

    $resultfive = mysqli_query($DBLink, $queryfive);
    $numfive = mysqli_num_rows($resultfive);
    $rowfive = null;
    $i = 0;
    while ($rowfives = mysqli_fetch_array($resultfive)) {
        $rowfive["TAHUN_" . ($i + 1)] = $rowfives["SPPT_TAHUN_PAJAK"];
        $rowfive["POKOK_" . ($i + 1)] = $rowfives["SPPT_PBB_HARUS_DIBAYAR"];
        $rowfive["DENDA_" . ($i + 1)] = 0;
        $rowfive["JUMLAH_" . ($i + 1)] = 0;
        $rowfive["KETERANGAN_" . ($i + 1)] = '';
        $i++;
    }

    if (GetValuesForPrints($aTemplateValue, $row, $tahunCetak, $rowfive, $numfive)) {
        $re->ApplyTemplateValue($aTemplateValue);
        if ($driver == "other") {
            $re->Print2OnpaysTXT($printValue);
            $strTXT = $printValue;
        } else {
            $re->Print2TXT($printValue);
            $strTXT = base64_encode($printValue);
        }
        $re->PrintHTML($strHTMLSingle);
        // echo $strHTMLSingle; exit;
    }
    $strHTML .= $strHTMLSingle;

    return $strHTML;
}

function QRCodeSVG($nop, $thn)
{
    global $DBLink;

    $datetimenow = date('Y-m-d H:i:s');

    $qry = "SELECT q.qr, IFNULL(s.PAYMENT_FLAG,0) AS flag, IFNULL(s.SPPT_PBB_HARUS_DIBAYAR,0) AS tagihan 
            FROM gw_pbb.pbb_sppt_qris q 
            LEFT JOIN gw_pbb.pbb_sppt s ON s.NOP=q.tax_object AND s.SPPT_TAHUN_PAJAK=q.year 
            WHERE q.tax_object='$nop' AND q.year='$thn' AND q.expired_date_time>='$datetimenow'
            ORDER BY q.id DESC 
            LIMIT 0, 1";

    $r = mysqli_query($DBLink, $qry);
    $nx = mysqli_num_rows($r);
    $QRCodeSVG = false;
    if($nx>0){
        $r = mysqli_fetch_array($r);
        $d = new DNS2D();
        $d->setStorPath(__DIR__.'/cache/');
        $QRCodeSVG = ($r['flag']==0 && $r['tagihan']>0) ? $d->getBarcodeSVG($r['qr'], 'QRCODE', 3, 3) : false;
    }
    return $QRCodeSVG;
}

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
$Setting     = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

if ($params) {
    $strHTML = null;
    saveToPDF($strHTML, $lNOP, $tahunCetak);
}

function saveToPDF($strHTML = "", $listNOP, $tahunCetak)
{   
    $icoQRIS = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="210mm" height="77.5mm" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 21000 7750" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs> <style type="text/css"> <![CDATA[ .fil0 {fill:black;fill-rule:nonzero} ]]> </style> </defs>
                    <g id="__x0023_Layer_x0020_1">
                        <metadata id="CorelCorpID_0Corel-Layer"/>
                        <path class="fil0" d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z"/>
                    </g>
                </svg>';
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

    $x2    = 62;
    $y2    = 28;
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
    
    $pdf->SetMargins(2, 15, 0);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
    $pdf->setCellHeightRatio(0.84);
    $listNOP = explode(',', $listNOP);
    foreach ($listNOP as $nop) {
        $HTML = printRequest($nop, $no_ktp, $tahunCetak);
        $dataBarcode = $nop . "\\" . $tahunCetak;
        $resolution = array(170, 270);
        $pdf->AddPage('P', $resolution);
        $pdf->writeHTML($HTML, true, false, false, false, '');
        // $pdf->setJPEGQuality(100);
        // $pdf->SetAlpha(0.3);
        // $pdf->Image("esppt_Pesawaran_temp.jpeg", -20, 7, 193, 181, 'JPG', '', 'T', false, 100, '', false);

        $QRCodeSVG = QRCodeSVG($nop, $tahunCetak);
        if($QRCodeSVG){ //print_r($QRCodeSVG);exit;
            $pdf->ImageSVG('@'.$QRCodeSVG, $x=52.8, $y=120, $w=28, $h=28, $link='', $align='', $palign='', $border=0, $fitonpage=false);
            $pdf->ImageSVG('@'.$icoQRIS, $x=40.5, $y=140, $w=12, $h=12, $link='', $align='', $palign='', $border=0, $fitonpage=false);
        }
    }
    
    $pdf->Output($nop . '.pdf', 'I');
}
