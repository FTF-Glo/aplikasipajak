<?php

date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemekaran' . DIRECTORY_SEPARATOR . 'daftar', '', dirname(__FILE__))) . '/';
require_once($sRootPath . 'inc/payment/inc-payment-db-c.php');
require_once($sRootPath . 'inc/payment/db-payment.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$jenisPemekaran = [
	1 => 'Pindah kelurahan keseluruhan ke kecamatan',
	2 => 'Pindah blok keseluruhan ke kelurahan lain',
	3 => 'Gabung Beberapa Blok',
	4 => 'Pindah NOP Ke Blok Lain'
];

$statusPemerakan = [
	1  => 'Selesai',
	0  => 'Belum di proses',
	-1 => 'Gagal Ubah Data NOP PBB SPPT',
	-2 => 'Gagal Ubah Data NOP PBB SPPT FINAL',
	-3 => 'Gagal Ubah Data NOP PBB SPPT SUSULAN',
	-4 => 'Gagal Ubah Data Cetakan SPPT',
	-5 => 'Gagal Ubah Data Tagihan SPPT'
];

$filter = isset($_REQUEST['filter']) ? (int) $_REQUEST['filter'] : null;
$nop = isset($_REQUEST['nop']) ? mysqli_real_escape_string($DBLink, $_REQUEST['nop']) : null;

if ($filter === 1) {
	$where = ['1=1'];
	
	if ($nop) {
		$where[] = "(NOP_LAMA = '{$nop}' OR NOP_BARU = '{$nop}')";
	}

	$sql = "SELECT DISTINCT
			ID,
			JENIS,
			NOP_LAMA,
			NOP_BARU,
			TGL_UPDATE,
			`STATUS`
		FROM 
			cppmod_pbb_perubahan_nop
		WHERE 
			" . implode(' AND ', $where) . "
		ORDER BY ID ASC";

	$result = mysqli_query($DBLink, $sql);

	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$row['JENIS'] = isset($jenisPemekaran[(int) $row['JENIS']])
						? $jenisPemekaran[(int) $row['JENIS']]
						: $row['JENIS'];

		$row['STATUS'] = isset($statusPemerakan[(int) $row['STATUS']])
						? $statusPemerakan[(int) $row['STATUS']]
						: $row['STATUS'];

		$row['TGL_UPDATE'] = !is_null($row['TGL_UPDATE'])
							? utcToLocal($row['TGL_UPDATE'])
							: $row['TGL_UPDATE'];
		$rows[] = $row;
	}

	$data = [
		'status' => !empty($rows),
		'data' => $rows
	];
	
	header('Content-Type: application/json');
	echo json_encode($data);
	exit;	
}

// source: https://stackoverflow.com/questions/5806526/php-utc-to-local-time
function utcToLocal($utc)
{
	$utc_ts = strtotime($utc);  // UTC Unix timestamp.

	// Timezone offset in seconds. The offset for timezones west of UTC is always negative,
	// and for those east of UTC is always positive.
	$offset = date("Z");

	$local_ts = $utc_ts + $offset;  // Local Unix timestamp. Add because $offset is negative.

	return date("Y-m-d g:i A", $local_ts);  // Local time as yyyy-mm-dd h:m am/pm.
}
