<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dashgb', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

$servername = GW_DBHOST;
$database = GW_DBNAME;
$username = GW_DBUSER;
$password = GW_DBPWD;
$DBLink = mysqli_connect($servername, $username, $password, $database);
global $DBLink;

$servername3 = ONPAYS_DBHOST;
$database3 = ONPAYS_DBNAME;
$username3 = ONPAYS_DBUSER;
$password3 = ONPAYS_DBPWD;
$DBLink3 = mysqli_connect($servername3, $username3, $password3, $database3);
global $DBLink3;

function get_target_pajak($returnQuery = false){
	global $DBLink;
	global $DBLink3;

		//TARGET
		$query_for_target = "
		SELECT
			CTR_AC_VALUE
		FROM
			CENTRAL_APP_CONFIG
		WHERE
			CTR_AC_KEY = 'JUMLAH_TARGET'
		";
		$result_for_target = mysqli_query($DBLink3, $query_for_target) or die(mysqli_error($DBLink3));
		while ($row1 = mysqli_fetch_assoc($result_for_target)) {
			$jumlah = $row1['CTR_AC_VALUE'];
		}


		$query_for_target2 = "
		SELECT
			CTR_AC_VALUE
		FROM
			CENTRAL_APP_CONFIG
		WHERE
			CTR_AC_KEY = 'TAHUN_TARGET'
		";
		$result_for_target2 = mysqli_query($DBLink3, $query_for_target2) or die(mysqli_error($DBLink3));
		while ($row2 = mysqli_fetch_assoc($result_for_target2)) {
			$tahun = $row2['CTR_AC_VALUE'];
		}

		// var_dump($tahun, $jumlah);


		$query = "
		SELECT
			SUM( A.bphtb_collectible ) AS TOTAL_PENDAPATAN,
			YEAR ( A.payment_paid ) AS TAHUN
		FROM
			SSB A
		WHERE
			YEAR ( A.payment_paid ) = '$tahun' 
			AND A.payment_flag = '1' 
		";

		$result = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));

		if ($returnQuery) {
			return $result;
		}

		$arr_res = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$arr_res['pendapatan'][] = $row['TOTAL_PENDAPATAN'];
			$arr_res['target'][] = $jumlah;
			$arr_res['persentase'][] = (float) $row['TOTAL_PENDAPATAN'] / $jumlah * 100;

			$arr_res['grafik'][] = array(
				'name' => 'BPHTB',
				'data' => array((float) $row['TOTAL_PENDAPATAN'] / $jumlah * 100),
				'tooltip' => array(
					'valueSuffix' => ' % [Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2) . ']'
				)
			);
		}
		return json_encode($arr_res);
	}


	echo get_target_pajak();

?>