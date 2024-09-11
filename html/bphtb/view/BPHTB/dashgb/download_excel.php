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


$tglbayar = date('Y-m-d');

function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Jan',
		'Feb',
		'Mar',
		'Apr',
		'Mei',
		'Jun',
		'Jul',
		'Agu',
		'Sep',
		'Okt',
		'Nov',
		'Des'
	);
	$pecahkan = explode('-', $tanggal);
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

?>


<body>
	<style type="text/css">
	body{
		font-family: 'Times New Roman', serif;
	}
	table{
		margin: 20px auto;
		border-collapse: collapse;
	}
	table {
        border: 1px solid #dddddd;
		padding: 3px 8px;

	}
    td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
    }
	a{
		background: blue;
		color: #fff;
		padding: 8px 10px;
		text-decoration: none;
		border-radius: 2px;
	}

  #judul{
    text-align:center;
    font-size:20px;
  }
  .str{ 
      mso-number-format:\@; 
    } 

.center{
	text-align:center;
}
	</style>

	<?php
	header("Content-type: application/vnd-ms-excel");
	header("Content-Disposition: attachment; filename=pencapaian.xls");
	?>

    
	<table>
  <tr>
    <th colspan="9" id="judul">REKAPITULASI REALISASI DAN TARGET SEMUA OBJEK PAJAK <?= tgl_indo($tglbayar) ?></th>  
  </tr>
		<tr>
            <th class="center">NO</th>
            <th class="center">JENIS PAJAK</th>
            <th class="center">TARGET</th>
            <th class="center">BULAN LALU</th>
            <th class="center">BULAN INI</th>
            <th class="center">S/D BULAN INI</th>
            <th class="center">%</th>
            <th class="center">LEBIH/KURANG</th>
            <th class="center">KET</th>
		</tr>



		<?php 
		//jumlah dan tahun
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

		//total pendapatan
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


	//getbulan
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

			echo '<tr>';
		while ($row = mysqli_fetch_assoc($result_for_table)) {
			$lebihkurang = $jumlah - $row['TOTAL_PENDAPATAN'];
			if($lebihkurang > 0){
				$ket = 'kurang';
			}elseif($lebihkurang < 0){
				$lebihkurang *= -1;
				$ket = 'lebih';
			}else{
				$ket = 'pas';
			}

            echo '<td>' . '1' . '</td>';
            echo '<td>' . 'BPHTB' . '</td>';
            echo '<td>' . 'Rp. ' . number_format($jumlah, 2) . '</td>';
			echo "<td>" . 'Rp. ' . (isset($result_perbulan_for_table[date('n', strtotime('-1 month'))]) ? number_format($result_perbulan_for_table[date('n', strtotime('-1 month'))]) : '0') . "</td>";	
			echo "<td>" . 'Rp. ' . (isset($result_perbulan_for_table[date('n')]) ? number_format($result_perbulan_for_table[date('n')]) : '0') . "</td>";
			echo "<td>" . 'Rp. ' . number_format($row['TOTAL_PENDAPATAN'], 2) . "</td>";
			echo "<td>" . round(((float) $row['TOTAL_PENDAPATAN'] / (float) $jumlah * 100), 2) . " %</td>";
            echo '<td>' . 'Rp. ' . number_format($lebihkurang, 2) . '</td>';
            echo '<td>' . $ket . '</td>';

		}
			echo '</tr>';
         ?>

		
		<?php 
		$tes = array('','Dibuat Tanggal ' . tgl_indo($tglbayar), '', '', 'Mengetahui', 'Kepala BAPENDA Pringsewu', 'Kabupaten Pringsewu', '', '', '', 'MADANI, SE, MM', '19630813 199003 1 003');
		foreach($tes as $r) : ?>
		 <tr>
			 <td></td>
		 	 <td colspan="2" style="text-align:center"><b><?php echo $r ?></b></td>
			 <td></td>
			 <td></td>
			 <td></td>
			 <td></td>
			 <td></td>
			 <td></td>
		 </tr>
		<?php endforeach; ?>

        </tbody>
      </table>
</body>
</html>