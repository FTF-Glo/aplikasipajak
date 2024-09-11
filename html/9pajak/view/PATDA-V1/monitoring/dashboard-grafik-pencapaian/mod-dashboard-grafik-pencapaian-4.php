<?php
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;
global $DBLink;

$count_wp = mysqli_fetch_row(mysqli_query($DBLink, "SELECT COUNT(*)  FROM PATDA_WP"));
$count_op = 0;
$count_op_query = mysqli_query($DBLink, "
	SELECT COUNT(*) as JML_ROW FROM PATDA_AIRBAWAHTANAH_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_HIBURAN_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_HOTEL_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_JALAN_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_MINERAL_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_PARKIR_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_REKLAME_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_RESTORAN_PROFIL
	UNION
	SELECT COUNT(*) as JML_ROW FROM PATDA_WALET_PROFIL
	");
while ($count_op_res = mysqli_fetch_assoc($count_op_query)) {
	$count_op += ($count_op_res['JML_ROW']);
};
$tahunTargetPajak = mysqli_query($DBLink, "SELECT CPM_TAHUN FROM PATDA_TARGET_PAJAK GROUP BY CPM_TAHUN");
$arr_bulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");

// TABEL
$query_for_table = "
	SELECT
		SUM( A.patda_total_bayar ) AS TOTAL_PENDAPATAN,
		C.CPM_JUMLAH AS TARGET,
		B.id_sw AS ID_JENIS_PAJAK,
		B.jenis_sw AS JENIS_PAJAK,
		YEAR ( A.payment_paid ) AS TAHUN 
	FROM
		SIMPATDA_GW A
		INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
		INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
		AND C.CPM_TAHUN = YEAR ( CURRENT_DATE ) 
		AND C.CPM_AKTIF = '1' 
	WHERE
		YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
		AND A.payment_flag = '1' 
	GROUP BY
		A.simpatda_type;
";
$result_for_table = mysqli_query($DBLink, $query_for_table) or die(mysqli_error($DBLink));
function get_bulan_lalu_table()
{
	global $DBLink;
	$query = "
		SELECT
			SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
			C.CPM_JUMLAH AS TARGET,
			B.id_sw AS ID_JENIS_PAJAK,
			B.jenis_sw AS JENIS_PAJAK,
			YEAR(A.payment_paid) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN
		FROM
			SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
			INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
		WHERE
			YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE)
		GROUP BY A.simpatda_type, MONTH(A.payment_paid)
		UNION
		SELECT
			SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
			C.CPM_JUMLAH AS TARGET,
			B.id_sw AS ID_JENIS_PAJAK,
			B.jenis_sw AS JENIS_PAJAK,
			YEAR(A.payment_paid) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN
		FROM
			SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
			INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
		WHERE
			YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
		GROUP BY A.simpatda_type, MONTH(A.payment_paid);
	";

	$result = mysqli_query($DBLink, $query);

	$arr_res = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$arr_res[$row['BULAN']][$row['ID_JENIS_PAJAK']] = $row['TOTAL_PENDAPATAN'];
	}

	return $arr_res;
}

function get_2bulan_lalu_table()
{
	global $DBLink;
	$query = "
		SELECT
			SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
			C.CPM_JUMLAH AS TARGET,
			B.id_sw AS ID_JENIS_PAJAK,
			B.jenis_sw AS JENIS_PAJAK,
			YEAR(A.payment_paid) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN
		FROM
			SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
			INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
		WHERE
			YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE)
		GROUP BY A.simpatda_type, MONTH(A.payment_paid)
		UNION
		SELECT
			SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
			C.CPM_JUMLAH AS TARGET,
			B.id_sw AS ID_JENIS_PAJAK,
			B.jenis_sw AS JENIS_PAJAK,
			YEAR(A.payment_paid) AS TAHUN,
			MONTH(A.payment_paid) AS BULAN
		FROM
			SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
			INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
		WHERE
			YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
			AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE - INTERVAL 2 MONTH)
		GROUP BY A.simpatda_type, MONTH(A.payment_paid);
	";

	$result = mysqli_query($DBLink, $query);

	$arr_res = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$arr_res[$row['BULAN']][$row['ID_JENIS_PAJAK']] = $row['TOTAL_PENDAPATAN'];
	}

	return $arr_res;
}

$result_perbulan_for_table = get_bulan_lalu_table();
$result_per2bulan_for_table = get_2bulan_lalu_table();

?>
<input type="hidden" id="iLink" value="<?php echo "view/{$DIR}/monitoring/dashboard-grafik-pencapaian/svc-grafik.php" ?>">

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
							<strong class="pull-right">Wajib pajak terdaftar: <span><?php echo $count_wp[0] ?></span> wajib pajak/Objek Pajak Terdaftar: <span><?php echo $count_op ?></span> objek pajak</strong>
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
								echo "<td>{$row['JENIS_PAJAK']}</td>";
								echo "<td>" . number_format($row['TARGET'], 2) . "</td>";
								echo "<td>" . (isset($result_per2bulan_for_table[date('n', strtotime('-2 month'))][(int)$row['ID_JENIS_PAJAK']]) ? number_format($result_per2bulan_for_table[date('n', strtotime('-2 month'))][(int)$row['ID_JENIS_PAJAK']]) : '0') . "</td>";

								echo "<td>" . (isset($result_perbulan_for_table[date('n', strtotime('-1 month'))][(int)$row['ID_JENIS_PAJAK']]) ? number_format($result_perbulan_for_table[date('n', strtotime('-1 month'))][(int)$row['ID_JENIS_PAJAK']]) : '0') . "</td>";

								echo "<td>" . (isset($result_perbulan_for_table[date('n')][(int)$row['ID_JENIS_PAJAK']]) ? number_format($result_perbulan_for_table[date('n')][(int)$row['ID_JENIS_PAJAK']]) : '0') . "</td>";
								echo "<td>" . number_format($row['TOTAL_PENDAPATAN'], 2) . "</td>";
								echo "<td>" . round(((float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100), 2) . " %</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
					<form action="view/<?php echo $DIR ?>/monitoring/dashboard-grafik-pencapaian/svc-grafik.php" method="post">
						<input type="hidden" name="download-excel" value="1">
						<button type="submit" class="btn btn-primary pull-right">Download</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php


?>