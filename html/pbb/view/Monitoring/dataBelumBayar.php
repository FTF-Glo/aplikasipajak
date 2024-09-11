<?php

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");


$json = new Services_JSON();
if ($_GET["action"] == "list") {
    try {
        if (isset($_SESSION['kec2']) != '0') {
            $_SESSION['lst'] = " a.KD_KECAMATAN=" . $_SESSION['kec2'];
            $buatCount = " a.KD_KECAMATAN=" . $_SESSION['kec2'];
        }
        if (isset($_SESSION['kel2']) != '0') {
            $_SESSION['lst'] .= " and a.KD_KELURAHAN=" . $_SESSION['kel2'];
            $buatCount .= " and a.KD_KELURAHAN=" . $_SESSION['kel2'];
        }

        if (isset($_SESSION['Nama_kec2'])) {
            $nam3 = $_SESSION['Nama_kec2'];
        }
        if (isset($_SESSION['Nam_kel2'])) {
            $nam4 = $_SESSION['Nam_kel2'];
        }
        $_SESSION['lst'] .=' and (b.STATUS_PEMBAYARAN_SPPT=0 or b.STATUS_PEMBAYARAN_SPPT=2 or b.STATUS_PEMBAYARAN_SPPT=3)';
        $buatCount .=' and (b.STATUS_PEMBAYARAN_SPPT=0 or b.STATUS_PEMBAYARAN_SPPT=2 or b.STATUS_PEMBAYARAN_SPPT=3)';
        if (isset($_SESSION['tanggal3']) && isset($_SESSION['tanggal4'])) {
            $tgl1 = $_SESSION['tanggal3'];
            $tgl2 = $_SESSION['tanggal4'];
        }
        if (isset($_SESSION['tanggal3']) && !isset($_SESSION['tanggal4'])) {
            $tg1l = $_SESSION['tanggal3'];
        }
        if (isset($_SESSION['thnawal2'])) {
            $_SESSION['lst'] .= " and a.THN_PAJAK_SPPT =" . $_SESSION['thnawal2'];
            $buatCount .= " and a.THN_PAJAK_SPPT =" . $_SESSION['thnawal2'];
        }

        if ($tgl1 != '' && $tgl2 != '') {
            $_SESSION['lst'] .= " and b.TGL_TERBIT_SPPT between TO_DATE('" . $tgl1 . "','yyyy/mm/dd') and TO_DATE('" . $tgl2 . "','yyyy/mm/dd ')";
        }
        if ($tgl1 != '' && $tgl2 == '') {
            $_SESSION['lst'] .= " and b.TGL_TERBIT_SPPT between TO_DATE('" . $tgl1 . "','yyyy/mm/dd') and TO_DATE('" . $tgl1 . "','yyyy/mm/dd ')";
        }
        if (isset($_SESSION['cari'])) {
            $cari = $_SESSION['cari'];
        }
        if ($cari != '') {
            $_SESSION['lst'] .= " and (b.NM_WP_SPPT LIKE '%" . strtoupper($cari) . "%' or b.KD_PROPINSI || b.KD_DATI2 || b.KD_KECAMATAN || b.KD_KELURAHAN || b.KD_BLOK || b.NO_URUT || b.KD_JNS_OP LIKE '%" . $cari . "%')";
        }
        $kosong1['dimana'] = $buatCount;
        $timeOut = '1000';
        $tmp1['f'] = 'pbbv21.countjumlah';

        $tmp1['i'] = $kosong1;
        $tmp1['PAN'] = '11000';
        $tmp1['IS_VALIDATE'] = '0';
        $sRequestStream1 = $json->encode($tmp1);
        $bOK = GetRemoteResponse($_SESSION['ip'], $_SESSION['port'], $timeOut, $sRequestStream1, $sResp);
        $ts1 = $json->decode($sResp);
        $hslJum[] = $json->decode($ts1->o);
        $bayar = $hslJum[0][0]->JUMLAH;
        unset($kosong1);
        unset($tmp1);
        
        $kosong1['dimana'] = $_SESSION['lst'];
        $timeOut = '1000';
        $tmp1['f'] = 'pbbv21.countList';

        $tmp1['i'] = $kosong1;
        $tmp1['PAN'] = '11000';
        $tmp1['IS_VALIDATE'] = '0';
        $sRequestStream1 = $json->encode($tmp1);
        $bOK = GetRemoteResponse($_SESSION['ip'], $_SESSION['port'], $timeOut, $sRequestStream1, $sResp);
        $ts1 = $json->decode($sResp);
        $hsl[] = $json->decode($ts1->o);

        $jumlah = $hsl[0][0]->JUMLAH;

        $tmp1['f'] = 'pbbv21.list';
        if ($_GET["jtStartIndex"] == '0') {
            $index = 1;
        } else {
            $index = intval($_GET["jtStartIndex"]);
        }
        $size = intval($_GET["jtPageSize"]);
        $size = $index + $size;
        $kosong1['dimana'] = $_SESSION['lst'] . ') where t between ' . $_GET["jtStartIndex"] . ' and ' . $size;
        $tmp1['i'] = $kosong1;
        unset($hsl);
        $sRequestStream1 = $json->encode($tmp1);
        $bOK = GetRemoteResponse($_SESSION['ip'], $_SESSION['port'], $timeOut, $sRequestStream1, $sResp);

        $ts1 = $json->decode($sResp);
        $hsl[] = $json->decode($ts1->o);
        //print_r($sResp);
        $jTableResult = array();
        $jTableResult['Result'] = 'OK';
        $res = array();
        unset($temp);
        foreach ($hsl as $key => $value) {
            foreach ($value as $isi => $val) {
                $temp['Npwp'] = $val->NOP;
                $temp['Nama'] = $val->NM_WP_SPPT;
                $temp['Kec'] = $nam3; //$val->KD_KECAMATAN;
                $temp['Kel'] = $nam4; //$val->KD_KELURAHAN;
                $tgl = explode('-', $val->TGL_TERBIT_SPPT);
                $temp['Terbit'] = substr($tgl[2], 0, 2) . '-' . $tgl[1] . '-' . $tgl[0];
                $tgl = explode('-', $val->TGL_JATUH_TEMPO_SPPT);
                $temp['Bayar'] = substr($tgl[2], 0, 2) . '-' . $tgl[1] . '-' . $tgl[0];
                $temp['Jumlah'] = $val->PBB_YG_HARUS_DIBAYAR_SPPT;
                //$sementara +=$val->JML_SPPT_YG_DIBAYAR;
                $res[] = $temp;
            }
        }

        $jTableResult['TotalRecordCount'] = $jumlah;
        $jTableResult['TotalSum2'] = number_format($bayar, 2, ",", ".");
        $jTableResult['Records'] = $res;
        print json_encode($jTableResult);
    } catch (Exception $ex) {
        //Return error message
        $jTableResult = array();
        $jTableResult['Result'] = "ERROR";
        $jTableResult['Message'] = $ex->getMessage();
        print json_encode($jTableResult);
    }
}
?>