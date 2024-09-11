<?php

$sRoot = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'Monitoring', '', dirname(__FILE__))) . '/';
require_once($sRoot . "inc/payment/ctools.php");
require_once($sRoot . "inc/payment/comm-central.php");
require_once($sRoot . "inc/payment/json.php");

$tmp = array();
$json = new Services_JSON();


if ($_POST['download'] == '1') {
    if (isset($_POST['kc'])) {
        $kec = $_POST['kc'];
    }
    if (isset($_POST['namkec'])) {
        $nam1 = $_POST['namkec'];
    }

    if (isset($_POST['namkel'])) {
        $nam2 = $_POST['namkel'];
    }
    if ($kec != '0') {
        $_SESSION['lst'] = " a.KD_KECAMATAN=" . $kec;
    }
    if (isset($_POST['kl'])) {
        $kel = $_POST['kl'];
    }
    if ($kel != '0') {
        $_SESSION['lst'] .= " and a.KD_KELURAHAN=" . $kel;
    }
    $_SESSION['lst'] .=' and b.STATUS_PEMBAYARAN_SPPT=1';
    if (isset($_POST['tgl1']) && isset($_POST['tgl2'])) {
        $tgl1 = $_POST['tgl1'];
        $tgl2 = $_POST['tgl2'];
    }
    if (isset($_POST['tgl1']) && !isset($_POST['tgl2'])) {
        $tg1l = $_POST['tgl1'];
    }
    if ($tgl1 != '' && $tgl2 != '') {
        $_SESSION['lst'] .= " and a.TGL_PEMBAYARAN_SPPT between " . $_POST['tgl1'] . ' and ' . $_POST['tgl2'];
    }
    if ($tgl1 != '' && $tgl2 == '') {
        $_SESSION['lst'] .= " and a.TGL_PEMBAYARAN_SPPT =" . $_POST['tgl1'];
    }
    if (isset($_POST['awal'])) {
        $_SESSION['lst'] .= " and a.THN_PAJAK_SPPT =" . $_POST['awal'];
    }
    if (isset($_POST['src'])) {
        $cari = $_PORT['src'];
    }
    if ($cari != '') {
        $_SESSION['lst'] .= " and (b.NM_WP_SPPT LIKE '%" . $cari . "%' or b.KD_PROPINSI || b.KD_DATI2 || b.KD_KECAMATAN || b.KD_KELURAHAN || b.KD_BLOK || b.NO_URUT || b.KD_JNS_OP LIKE '%" . $cari . "%')";
    }
    if ($_GET["jtStartIndex"] == '0') {
        $index = 1;
    } else {
        $index = intval($_GET["jtStartIndex"]);
    }
    $size = intval($_GET["jtPageSize"]);
    $size = $index + $size;
    $kosong1['dimana'] = $_SESSION['lst'] .')'; 
        //where t between ' . $index . ' and ' . $size;
    $timeOut = '1000';
    $tmp1['f'] = 'pbbv21.list';

    $tmp1['i'] = $kosong1;
    $tmp1['PAN'] = '11000';
    $tmp1['IS_VALIDATE'] = '0';


    $host = $_POST['host'];
    $port = $_POST['port'];
    $timeOut = $_POST['time'];

    $sRequest = $json->encode($tmp1);
    $bOK = GetRemoteResponse($host, $port, '500', $sRequest, $sResp);
    $ts1 = $json->decode($sResp);
   // print_r($sResp);
    $hsl[] = $json->decode($ts1->o);
    $res = array();
    unset($temp);
    foreach ($hsl as $key => $value) {
        foreach ($value as $isi => $val) {
         
            $temp['Npwp'] = $val->NOP.'&nbsp;';
            $temp['Nama'] = $val->NM_WP_SPPT;
            $temp['Kec'] = $nam1; //$val->KD_KECAMATAN;
            $temp['Kel'] = $nam2; //$val->KD_KELURAHAN;
            $tgl = explode('-', $val->TGL_PEMBAYARAN_SPPT);
            $temp['Bayar'] = substr($tgl[2], 0, 2) . '-' . $tgl[1] . '-' . $tgl[0];
            $temp['Jumlah'] = number_format($val->JML_SPPT_YG_DIBAYAR, 2, ",", ".");
            $temp['Nip'] = $val->NIP_REKAM_BYR_SPPT;
            $tgl = explode('-', $val->TGL_REKAM_BYR_SPPT);
            $temp['Rekam'] = substr($tgl[2], 0, 2) . '-' . $tgl[1] . '-' . $tgl[0] . ' ' . substr($tgl[2], 2, 9);

            $res[] = $temp;
            
        }
    }

    echo '
                               <table id="SudahBayar">
                                <tr>
                                    <td>NOP</td>
                                    <td>Nama WP</td>
                                    <td>Kecamatan</td>
                                    <td>Kelurahan</td>
                                    <td>Tanggal Pembayaran</td>
                                    <td>Dibayar</td>
                                    <td>NIP Perekam</td>
                                    <td>Tanggal Rekam</td>
                                </tr>';
    
    foreach ($res as $key => $value) {
          
        echo'<tr>';
        echo '<td>' . $value['Npwp'];
        echo '<td>' . $value['Nama'];
        echo '<td>' . $value['Kec'];
        echo '<td>' . $value['Kel'];
        echo '<td>' . $value['Bayar'];
        echo '<td>' . $value['Jumlah'];
        echo '<td>' . $value['Nip'];
        echo '<td>' . $value['Rekam'];
        echo'</tr>';
    }
    echo'</table>';

//		        $file = explode('/',$addr->filename);
//			$hps =$addr->filename;
//			$alamat = '/'.$file[4].'/'.$file[5];
//			echo $alamat;
}
?>