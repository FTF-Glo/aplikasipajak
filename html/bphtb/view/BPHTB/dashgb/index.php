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

$arr_bulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");


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
	while ($row = mysqli_fetch_assoc($result_for_target)) {
		$jumlah = $row['CTR_AC_VALUE'];
	}


	$query_for_target = "
	SELECT
		CTR_AC_VALUE
	FROM
		CENTRAL_APP_CONFIG
	WHERE
		CTR_AC_KEY = 'TAHUN_TARGET'
";
$result_for_target = mysqli_query($DBLink3, $query_for_target) or die(mysqli_error($DBLink3));
	while ($row = mysqli_fetch_assoc($result_for_target)) {
		$tahun = $row['CTR_AC_VALUE'];
	}

	// var_dump($tahun, $jumlah);

// TABEL
$query_for_table = "
	SELECT
		SUM( A.bphtb_collectible ) AS TOTAL_PENDAPATAN,
		YEAR ( A.payment_paid ) AS TAHUN
	FROM
		SSB A
	WHERE
		YEAR ( A.payment_paid ) = '$tahun' 
		AND A.payment_flag = '1' 
";
$result_for_table = mysqli_query($DBLink, $query_for_table) or die(mysqli_error($DBLink));

function get_bulan_lalu_table()
{
	global $DBLink;
	global $tahun;
	$query = "
		SELECT
			SUM( A.bphtb_collectible ) AS TOTAL_PENDAPATAN,
			YEAR ( A.payment_paid ) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN		
			FROM
			SSB A
		WHERE
			YEAR ( A.payment_paid ) = '$tahun'
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE)
		GROUP BY
			MONTH(A.payment_paid)
		UNION
		SELECT
			SUM( A.bphtb_collectible ) AS TOTAL_PENDAPATAN,
			YEAR ( A.payment_paid ) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN		
			FROM
			SSB A
		WHERE
			YEAR ( A.payment_paid ) = '$tahun'
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
		GROUP BY
			MONTH(A.payment_paid);
	";

	$result = mysqli_query($DBLink, $query);

	$arr_res = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$arr_res[$row['BULAN']] = $row['TOTAL_PENDAPATAN'];
	}

	return $arr_res;
}

function get_2bulan_lalu_table()
{
	global $DBLink;
	global $tahun;
	$query = "
		SELECT
			SUM( A.bphtb_collectible ) AS TOTAL_PENDAPATAN,
			YEAR ( A.payment_paid ) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN		
			FROM
			SSB A
		WHERE
			YEAR ( A.payment_paid ) = '$tahun' 
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE - INTERVAL 2 MONTH)
		GROUP BY
			MONTH(A.payment_paid);
		";

	$result = mysqli_query($DBLink, $query);

	$arr_res = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$arr_res[$row['BULAN']] = $row['TOTAL_PENDAPATAN'];
	}

	return $arr_res;
}

$result_perbulan_for_table = get_bulan_lalu_table();
$result_per2bulan_for_table = get_2bulan_lalu_table();

?>
<input type="hidden" id="iLink" value="<?php echo "/view/BPHTB/dashgb/json_grafik.php" ?>">

<style>
	#container #content {
		padding-left: 0 !important;
	}

	.customtable th {
		background-color: #e6EEEE;
	}

	.customtable,
	.customtable th,
	.customtable td {
		text-align: center;
	}
</style>
<script src="inc/js/jquery-1.9.1.min.js"></script>
<!-- <script src="https://code.highcharts.com/highcharts.js"></script> -->
<script src="inc/js/Highcharts/highcharts.js"></script>
<script src="inc/js/Highcharts/modules/exporting.js"></script>
<script>
	function visitorData(data) {
		$('#chart-container').highcharts({
			chart: {
				type: 'column'
			},
			title: {
				text: 'Grafik Objek Pajak'
			},
			xAxis: {
				categories: ['Objek Pajak']
			},
			yAxis: {
				title: {
					text: 'Persentase Pencapaian Pajak (%)'
				}
			},
			series: data,
		});
	}
	$(function() {
		$.ajax({
			url: $('#iLink').val(),
			type: 'GET',
			data: {
				get_target_pajak: 1
			},
			async: true,
			dataType: "json",
			success: function(data) {
				visitorData(data.grafik);
			}
		});
	});
</script>



<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default" style="margin-top: 3em">
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
							<h3>Statistik Pencapaian Target</h3>
						</div>
						<div class="col-sm-12">
							<!-- <strong class="pull-right">Wajib pajak terdaftar: <span>kosong</span> wajib pajak/Objek Pajak Terdaftar: <span>kosong</span> objek pajak</strong> -->
						</div>
						<div class="col-sm-12">
							<hr>
						</div>
						<div class="col-sm-12">
							<div id="chart-container"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<h3>Tabel Pencapaian Target</h3>
					<small><strong>Update per tanggal: <?php echo date('d') . " " . $arr_bulan[date('n')] . " " . date('Y') ?></strong></small>
					<table class="table table-bordered customtable">
						<thead>
							<tr>
								<th rowspan="2">Jenis Pajak</th>
								<th rowspan="2">Target (Rp)</th>
								<th colspan="4">Realisasi (Rp)</th>
								<th rowspan="2">Persentase</th>
							</tr>
							<tr>
								<th><?php echo $arr_bulan[date('n', strtotime('-2 month'))] ?> <?php echo date('Y') ?></th>
								<th><?php echo $arr_bulan[date('n', strtotime('-1 month'))] ?> <?php echo date('Y') ?></th>
								<th><?php echo $arr_bulan[date('n')] ?> <?php echo date('Y') ?></th>
								<th>Total Hingga Bulan Ini</th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($row = mysqli_fetch_assoc($result_for_table)) {
								// die(print_r($row));
								echo "<tr>";

								echo "<td>" . 'BPHTB' . "</td>";
								echo "<td>" . number_format($jumlah, 2) . "</td>";

								echo "<td>" . (isset($result_per2bulan_for_table[date('n', strtotime('-2 month'))]) ? number_format($result_per2bulan_for_table[date('n', strtotime('-2 month'))]) : '0') . "</td>";

								echo "<td>" . (isset($result_perbulan_for_table[date('n', strtotime('-1 month'))]) ? number_format($result_perbulan_for_table[date('n', strtotime('-1 month'))]) : '0') . "</td>";
								echo "<td>" . (isset($result_perbulan_for_table[date('n')]) ? number_format($result_perbulan_for_table[date('n')]) : '0') . "</td>";
								echo "<td>" . number_format($row['TOTAL_PENDAPATAN'], 2) . "</td>";
								echo "<td>" . round(((float) $row['TOTAL_PENDAPATAN'] / (float) $jumlah * 100), 2) . " %</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
					<form action="#" method="post">
						<input type="hidden" name="download-excel" value="1">
						<input type="button" class="btn btn-primary" value="Download" onclick="open_win()">
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
function open_win() {
    window.open('/view/BPHTB/dashgb/download_excel.php');
}
</script>
