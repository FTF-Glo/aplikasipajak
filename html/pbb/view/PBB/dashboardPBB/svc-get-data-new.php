<?php
ini_set('memory_limit', '2G');

require_once 'Dashboard.php';

$dashboard = new Dashboard();
$data = array();

if (!empty($dashboard->request('getChart'))) {
    $tanggalBayarStart = $dashboard->request('tanggalBayarStart');
    $tanggalBayarEnd   = $dashboard->request('tanggalBayarEnd');
    $kecamatan         = $dashboard->request('kecamatan');
    $kelurahan         = $dashboard->request('kelurahan');
    $tahun             = $dashboard->request('tahun');
    $buku              = $dashboard->request('buku');
    $mode              = $dashboard->request('mode', 'getJson');
    $mode              = ($mode=='') ? 'getJson' : $mode;

    $data = false;
    
    $sh1 = sha1('Chart'. $tanggalBayarStart . $tanggalBayarEnd . $kecamatan . $kelurahan . $tahun . $buku);

    if($mode=='getJson'){
        $isFile = file_exists("chart_$sh1.json");
        if($isFile){
            $getJson = file_get_contents("chart_$sh1.json");
            $data = json_decode($getJson);
            $data[0]->FROMJSON = true;
        }
    }

    if(!$data){
        if ($tanggalBayarStart) {
            $dashboard->setFilter("DATE(A.PAYMENT_PAID) >=", $tanggalBayarStart);
        }
    
        if ($tanggalBayarEnd) {
            $dashboard->setFilter("DATE(A.PAYMENT_PAID) <=", $tanggalBayarEnd);
        }
    
        if ($kecamatan) {
            $dashboard->setFilter("A.OP_KECAMATAN_KODE =", $kecamatan);
        }
    
        if ($kelurahan) {
            $dashboard->setFilter("A.OP_KELURAHAN_KODE =", $kelurahan);
        }
    
        if ($tahun) {
            $dashboard->setFilter("A.SPPT_TAHUN_PAJAK =", $tahun);
        }
    
        if ($buku) {
            $getBuku = isset($dashboard->buku[$buku]) ? $dashboard->buku[$buku] : null;
    
            if ($getBuku !== null) $dashboard->setFilter("A.SPPT_PBB_HARUS_DIBAYAR BETWEEN {$getBuku['min']} AND {$getBuku['max']}");
        }
    
        $data = $dashboard->getData();
        if (!$data) {
            $data = array(array(
                'SUM_KETETAPAN'   => 0,
                'SUM_REALISASI'   => 0,
                'TAHUN_PAJAK'     => 0,
                'SUM_DENDA'       => 0
            ));
        }
    
        $myfile = fopen("chart_$sh1.json", "w") or die("Unable to open file ringkas_bphtb_$tahun.json !");
        $json = json_encode($data);
        fwrite($myfile, $json);
        fclose($myfile);
    }
}

if (!empty($dashboard->request('getKecamatan'))) {
    $data = $dashboard->getKecamatan();
}

if (!empty($dashboard->request('getKelurahan'))) {
    $kcid = $dashboard->request('kcid');
    $data = $dashboard->getKelurahan($kcid);
}

if(!empty($dashboard->request('hitungDendaMassal'))) {
    $data = array(
        'lastDenda' => (new HitungDendaMassal())->execute()
    );
}

if (!empty($dashboard->request('getTappingRealisasi'))) {
    $thn = $dashboard->request('tahun');
    $data = $dashboard->getTappingRealisasi($thn);
}

/** counter */
if(!empty($dashboard->request('counter'))) {
    $counterName= $dashboard->request('counter');
    $period     = $dashboard->request('period', 'today');
    $status     = $dashboard->request('status', 'sudah bayar');

    $counter = 0;
    if ($counterName == 'getCounterTunggakan') {
        $data = $dashboard->getCounterTunggakan();
        $counter = $data ? ($data['SUM_KETETAPAN'] + $data['SUM_DENDA']) : 0;
    }
    if ($counterName == 'getCounterRealisasi') {
        $data = $dashboard->getCounterRealisasi($period);
        $counter = $data ? $data['SUM_REALISASI'] : 0;
    }
    if ($counterName == 'getCounterNOP') {
        $data = $dashboard->getCounterNOP($period, $status);
        $counter = $data ? $data['COUNT_NOP'] : 0;
    }

    $data = array(
        'original'  => $counter,
        'formatted' => number_format($counter),
        'display'   => $dashboard->number_format_short($counter),
    );
    
    
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit;

/** ALDES */