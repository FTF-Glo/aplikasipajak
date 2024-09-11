<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

ini_set('max_execution_time', '-1');
set_time_limit(36000);


require_once 'inc/PBB/SimpleDB.php';
require_once 'inc/payment/constant.php';
require_once 'inc/payment/inc-payment-c.php';
require_once 'inc/payment/ctools.php';
require_once 'inc/payment/comm-central.php';
require_once 'inc/payment/inc-dms-c.php';

$cData = isset($_COOKIE['centraldata']) && $_COOKIE['centraldata'] ? $_COOKIE['centraldata'] : '';
$cData = !empty($cData) ? base64_decode($cData) : '';
$cData = !empty($cData) ? json_decode($cData, true) : '';

if (empty($cData) && !isset($_POST['proseslimit'])) {
    if (isset($_POST['proses'])) {
        header('Content-Type: application/json');
        echo json_encode(array('refresh' => 1));
        exit;
    }

    echo '<span>Anda belum login, silakan login lebih dahulu, lalu kembali lagi kesini.</span>';
    echo '<a href="/" target="_blank">klik disini untuk login</a><br>';
    echo '<span>Jika anda sudah login tetapi masih muncul pesan ini, silakan refresh halaman ini.</span>';
    echo '<a href="/penetapan-massal-auto.php">klik disini untuk refresh</a>';

    exit;
}

$core             = new SimpleDB();
$appConfig        = $core->get('appConfig');
$tahun            = $appConfig['tahun_tagihan'];
$terhutangMinimum = $appConfig['minimum_sppt_pbb_terhutang'];
$serverAddress    = $appConfig['TPB_ADDRESS'];
$serverPort       = $appConfig['TPB_PORT'];
$serverTimeout    = $appConfig['TPB_TIMEOUT'];

$userLogin = isset($_POST['userlogin']) ? $_POST['userlogin'] : (isset($cData['uname']) ? $cData['uname'] : '');
$tanggal = date('Y-m-d');

// $totalData=$core->dbGetNumRows('SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_THN_PENETAPAN <> "' . $tahun . '"');
$totalData=$core->dbGetNumRows("SELECT f.* 
                                FROM sw_pbb.cppmod_pbb_sppt_final f
                                INNER JOIN gw_pbb.pbb_sppt g ON g.NOP=f.CPM_NOP AND g.SPPT_TAHUN_PAJAK='2023' 
                                WHERE f.CPM_SPPT_THN_PENETAPAN<>'$tahun'");
$totalDataSudah = $core->dbGetNumRows('SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_THN_PENETAPAN = "' . $tahun . '"');
$perNop = 100;
$howMuch = ceil($totalData / $perNop);


/** functions */
function fnum($value)
{
    return number_format($value, 0, ',', '.');
}

/** handle penetapan */
if (isset($_POST['proses'])) {
    $totalBerhasil = 0;
	$logs = array();
    for ($i = 0; $i < $howMuch; $i++) {
        $listNOP = '';

		// NEW: Supaya ketika penetapan massal, di filter juga Pemeliharaan Data Piutang nya
		$subQueryDafnom = " (SELECT NOP, MAX(TAHUN_KEGIATAN) AS MAX_TAHUN_KEGIATAN FROM cppmod_dafnom_op WHERE TAHUN_KEGIATAN < '{$tahun}' GROUP BY NOP) bSubQuery ";
		$leftJoinDafnom = " LEFT JOIN {$subQueryDafnom} ON bSubQuery.NOP = cppmod_pbb_sppt_final.CPM_NOP 
                            LEFT JOIN cppmod_dafnom_op b ON b.NOP = bSubQuery.NOP AND b.TAHUN_KEGIATAN = bSubQuery.MAX_TAHUN_KEGIATAN";
						
		$queryFinal = "SELECT cppmod_pbb_sppt_final.* FROM cppmod_pbb_sppt_final {$leftJoinDafnom} WHERE IFNULL(b.KATEGORI, 4) IN (4, '') AND CPM_SPPT_THN_PENETAPAN <> '{$tahun}' LIMIT {$perNop}";
        
		$final = $core->dbQuery($queryFinal);
        while ($row = mysqli_fetch_assoc($core->get('result'))) {
            $listNOP .= (trim($row['CPM_NOP']) . ',');
        }

        $listNOP = rtrim($listNOP, ',');
        
        $sRequestStream = "{\"PAN\":\"TP\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"\",\"TIPE\":\"2\",\"NOP\":\"" . $listNOP . "\",\"SUSULAN\":\"0\",\"TANGGAL\":\"" . $tanggal . "\",\"USER\":\"" . $userLogin . "\"}";
        $bOK = GetRemoteResponse($serverAddress, $serverPort, $serverTimeout, $sRequestStream, $serverResponse);

        if ($bOK == 0) {
            $serverResponse = rtrim($serverResponse, END_OF_MSG); // trim trailing '@'
            $serverResponse = json_decode($serverResponse, true);
            $totalBerhasil = (isset($serverResponse['RC']) && $serverResponse['RC'] == "0000") ? $totalBerhasil + 1 : $totalBerhasil;
        }
		
		$logs[] = array(
			'request' => $sRequestStream,
			'response' => $serverResponse
		);
    }

    header('Content-Type: application/json');
    echo json_encode(array('total' => $howMuch, 'berhasil' => $totalBerhasil, 'logs' => $logs));
    exit;
}

if (isset($_POST['proseskelurahan'])) {
    $totalBerhasil = 0;
	$kelurahanfinal = $core->dbQuery('SELECT CPM_OP_KELURAHAN FROM cppmod_pbb_sppt_final GROUP BY CPM_OP_KELURAHAN');
	$countkelurahan = 0;
	$logs = array();
	while ($row = mysqli_fetch_assoc($core->get('result'))) {
		$countkelurahan++;
		$kelurahan = $row['CPM_OP_KELURAHAN'];
		$sRequestStream = "{\"PAN\":\"TP\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"" . $kelurahan . "\",\"TIPE\":\"1\",\"NOP\":\"\",\"SUSULAN\":\"0\",\"TANGGAL\":\"" . $tanggal . "\",\"USER\":\"" . $userLogin . "\"}";
        $bOK = GetRemoteResponse($serverAddress, $serverPort, $serverTimeout, $sRequestStream, $serverResponse);

        if ($bOK == 0) {
            $serverResponse = rtrim($serverResponse, END_OF_MSG); // trim trailing '@'
            $serverResponse = json_decode($serverResponse, true);
            $totalBerhasil = (isset($serverResponse['RC']) && $serverResponse['RC'] == "0000") ? $totalBerhasil + 1 : $totalBerhasil;
        }
		
		$logs[] = array(
			'request' => $sRequestStream,
			'response' => $serverResponse
		);
	}

    header('Content-Type: application/json');
    echo json_encode(array('total' => $countkelurahan, 'berhasil' => $totalBerhasil, 'logs' => $logs));
    exit;
}

// INI YANG DIPAKE UNTUK HANDLE PENETAPAN
if (isset($_POST['proseslimit'])) {
    
    // exit('exit;');
    
    $totalBerhasil = 0;
	$logs = array();
    
    $listNOP = '';

    if ($howMuch > 0) {
		
		// NEW: Supaya ketika penetapan massal, di filter juga Pemeliharaan Data Piutang nya
		$subQueryDafnom = " (SELECT NOP, MAX(TAHUN_KEGIATAN) AS MAX_TAHUN_KEGIATAN FROM cppmod_dafnom_op WHERE TAHUN_KEGIATAN < '{$tahun}' GROUP BY NOP) bSubQuery ";
		$leftJoinDafnom = " LEFT JOIN {$subQueryDafnom} ON bSubQuery.NOP = cppmod_pbb_sppt_final.CPM_NOP 
                            LEFT JOIN cppmod_dafnom_op b ON b.NOP = bSubQuery.NOP AND b.TAHUN_KEGIATAN = bSubQuery.MAX_TAHUN_KEGIATAN";
						
		$queryFinal = "SELECT cppmod_pbb_sppt_final.* 
                        FROM cppmod_pbb_sppt_final 
                        INNER JOIN gw_pbb.pbb_sppt g ON g.NOP=cppmod_pbb_sppt_final.CPM_NOP AND g.SPPT_TAHUN_PAJAK='2023' 
                        
                        WHERE 
                            
                            CPM_SPPT_THN_PENETAPAN <> '{$tahun}' 
                        LIMIT {$perNop}";
		// echo '<pre>';
        // print_r($queryFinal);
        // exit;

        $final = $core->dbQuery($queryFinal);
        while ($row = mysqli_fetch_assoc($core->get('result'))) {
            $listNOP .= (trim($row['CPM_NOP']) . ',');
        }

        $listNOP = rtrim($listNOP, ',');
        
        // $sRequestStream = "{\"PAN\":\"TP\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"\",\"TIPE\":\"2\",\"NOP\":\"" . $listNOP . "\",\"SUSULAN\":\"0\",\"TANGGAL\":\"" . $tanggal . "\",\"USER\":\"" . $userLogin . "\"}";
        $sRequestStream = array(
            'PAN'       => 'TP',
            'TAHUN'     => (string) $tahun,
            'KELURAHAN' => '',
            'TIPE'      => '2',
            'NOP'       => $listNOP,
            'SUSULAN'   => '0',
            'TANGGAL'   => $tanggal,
            'USER'      => $userLogin,
        );
        $sRequestStream = json_encode($sRequestStream);

        $bOK = GetRemoteResponse($serverAddress, $serverPort, $serverTimeout, $sRequestStream, $serverResponse);

        $nopValue = $listNOP;

        // Pisahkan nilai NOP menjadi array
        $arrayNOP = explode(',', $nopValue);

        // Tambahkan tanda kutip satu pada setiap nilai NOP
        $arrayNOPWithQuotes = array_map(function ($nop) {
            return "'" . trim($nop) . "'";
        }, $arrayNOP);

        // Gabungkan kembali array menjadi string dengan tanda kutip satu
        $nopWithQuotesString = implode(',', $arrayNOPWithQuotes);
        if ($bOK == 0) {
            $serverResponse = rtrim($serverResponse, END_OF_MSG); // trim trailing '@'
            $serverResponse = json_decode($serverResponse, true);
            $totalBerhasil = (isset($serverResponse['RC']) && $serverResponse['RC'] == "0000") ? $totalBerhasil + 1 : $totalBerhasil;

            $updateQuery = "UPDATE gw_pbb.pbb_sppt
                SET OP_NJKP = CASE 
                    WHEN OP_NJKP >= 1000000000000 THEN OP_NJKP * 0.9
                    WHEN OP_NJKP  >= 1000000000 THEN OP_NJKP * 0.7
                    WHEN OP_NJKP <= 1000000000 THEN OP_NJKP * 0.4
                    ELSE OP_NJKP
                    END, 
                    SPPT_PBB_HARUS_DIBAYAR = GREATEST(ROUND(OP_NJKP * 0.003), $terhutangMinimum),
                    SPPT_TANGGAL_JATUH_TEMPO = '2024-06-30',
                    SPPT_TANGGAL_TERBIT = '2024-01-01',
                    SPPT_TANGGAL_CETAK = '2024-01-02'
                WHERE NOP IN ($nopWithQuotesString) AND SPPT_TAHUN_PAJAK = '$tahun'";
               $core->dbQuery($updateQuery);
            // $dbSpec->sqlQuery($updateQuery);

            $q =   "UPDATE sw_pbb.cppmod_pbb_sppt_current  
                    SET OP_NJKP = CASE 
                        WHEN OP_NJKP >= 1000000000000 THEN OP_NJKP * 0.9
                        WHEN OP_NJKP  >= 1000000000 THEN OP_NJKP * 0.7
                        WHEN OP_NJKP <= 1000000000 THEN OP_NJKP * 0.4
                        ELSE OP_NJKP
                        END, 
                        SPPT_PBB_HARUS_DIBAYAR = GREATEST(ROUND(OP_NJKP * 0.003), $terhutangMinimum),
                        SPPT_TANGGAL_JATUH_TEMPO = '2024-06-30',
                        SPPT_TANGGAL_TERBIT = '2024-01-01',
                        SPPT_TANGGAL_CETAK = '2024-01-02'
                WHERE NOP IN ($nopWithQuotesString) AND SPPT_TAHUN_PAJAK = '$tahun'";

            $core->dbQuery($q);
        }
    }
    
    $logs[] = array(
        'request' => $sRequestStream,
        'response' => $serverResponse
    );

    header('Content-Type: application/json');
    echo json_encode(array('total' => 1, 'berhasil' => $totalBerhasil, 'logs' => $logs, 'exit' => $howMuch <= 1));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penetapan Massal Otomatis</title>
    <style>
        .berhasil {
            color: green
        }

        .gagal {
            color: red
        }

        .proses {
            color: orange
        }
    </style>
</head>

<body>
    <h1>Penetepan tahun <?= $tahun ?></h1>
    <div style="display: block;">
        <p>Informasi:</p>
        <ol>
            <li>Login sebagai: <?= $userLogin ?></li>
            <li>PBB Minimum: <?= fnum($terhutangMinimum) ?></li>
            <li>Total data: <?= fnum($totalData) ?> NOP</li>
            <li>Total yang sudah ditetapkan: <?= fnum($totalDataSudah) ?> NOP</li>
            <li>Total Penetapan: <?= fnum($howMuch) ?>x</li>
        </ol>
    </div>
    <div style="display: block;">
        <!-- <button type="button" id="proses">mulai penetapan</button>
		<button type="button" id="proseskelurahan">mulai penetapan perkelurahan</button> -->
		<button type="button" id="proseslimit">mulai penetapan per <?= $perNop ?></button>
        <p id="status" style="display: none">Status penetapan: <span>...</span></p>
    </div>
    <div style="display: block;">
        <p>Penetapan:</p>
        <ol id="resultpenetapanlimit">
            <li class="removethis">Belum ada yang diproses</li>
        </ol>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
		
		
        $(function() {
            let btn = $('#proses'),
				btnkelurahan = $('#proseskelurahan'),
				btnlimit = $('#proseslimit'),
                status = $('#status'),
                resultpenetapanlimit = $('#resultpenetapanlimit'),
                maxretry = 5;

            const USERLOGIN = '<?= $userLogin ?>';
				
			const ajaxcall = (data, btn) => {
				if (!confirm('Apakah anda yakin ?')) return;

				$.ajax({
					url: '/penetapan-massal-auto.php',
					type: 'POST',
					data: data,
					dataType: 'json',
					beforeSend: function() {
						status.show().find('span').removeClass('gagal berhasil').addClass('proses').html('proses...');
						btn.prop('disabled', true);
					},
					success: function(data) {
						console.log(data);

						if (typeof data.refresh !== 'undefined') {
							location.reload();
							return;
						}

						if (typeof data.total === 'undefined') {
							status.show().find('span').removeClass('berhasil proses').addClass('gagal').html('gagal');
							return;
						}

						if (data.total != data.berhasil) {
							status.show().find('span').removeClass('berhasil proses').addClass('gagal').html(`sebagian gagal [${data.berhasil}/${data.total} penetapan]`);
							return;
						}

						status.show().find('span').removeClass('gagal proses').addClass('berhasil').html('berhasil');
					},
					error: function(e, ee, eee) {
						status.show().find('span').removeClass('berhasil proses').addClass('gagal').html('gagal');
						console.error(e, ee, eee);
					}
				})
			}

            const ajaxcalllimit = (param, btn, count = 1, retry = 0) => {
                if (count == 1 && retry == 0 && !confirm('Apakah anda yakin ?')) return;

                const _li = (count = 1, status = 'proses') => `<li data-order="${count}">Penetapan ke-${count}: <span data-status class="proses">${status}</span></li>`;
                
				$.ajax({
					url: '/penetapan-massal-auto.php',
					type: 'POST',
					data: param,
					dataType: 'json',
					beforeSend: function() {
                        resultpenetapanlimit.find('.removethis').remove();

                        let completelistelement = _li(count, retry > 0 ? `proses ulang [${retry}/${maxretry}]` : 'proses');
                        if (retry > 0) {
                            resultpenetapanlimit.find(`li[data-order="${count}"]`).html(completelistelement)
                        } else {
                            resultpenetapanlimit.append(completelistelement);
                        }

						btn.prop('disabled', true);

					},
					success: function(data) {
						console.log(data);

                        let _statusSpan = resultpenetapanlimit.find(`li[data-order="${count}"] > [data-status]`);

						if (typeof data.refresh !== 'undefined') {
							location.reload();
							return;
						}

						if (typeof data.total === 'undefined') {
							_statusSpan.removeClass('berhasil proses').addClass('gagal').html('gagal');
							return;
						}

						if (data.total != data.berhasil) {
                            _statusSpan.removeClass('berhasil proses').addClass('gagal').html(`gagal, ` + (retry > 0 ? `percobaan ke [${retry}/${maxretry}]` : ``));
							if (++retry <= maxretry) ajaxcalllimit(param, btn, count, retry);
                            return;
						}

						_statusSpan.removeClass('gagal proses').addClass('berhasil').html('berhasil');
                        if (data.exit !== true) ajaxcalllimit(param, btn, ++count);
					},
					error: function(e, ee, eee) {
						_statusSpan.removeClass('berhasil proses').addClass('gagal').html('gagal');
						console.error(e, ee, eee);
					}
				})
            }
			
            btn.on('click', function() { ajaxcall({proses: 1}, btn) })
			btnkelurahan.on('click', function() { ajaxcall({proseskelurahan: 1}, btnkelurahan) })
			btnlimit.on('click', function() { ajaxcalllimit({proseslimit: 1, userlogin: USERLOGIN}, btnlimit) })
        })
    </script>
</body>

</html>