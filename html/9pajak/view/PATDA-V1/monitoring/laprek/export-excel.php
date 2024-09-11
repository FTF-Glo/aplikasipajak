<?php
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "/laprek";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul . $submodul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

if (isset($_GET['tglbayar']))
{
   $tglbayar=$_GET['tglbayar'];
   $tglbayar1=$_GET['tglbayar1'];
}else{
    $tglbayar=date('Y-m-d');
    $tglbayar1=date('Y-m-d');
}

if (isset($_GET['filter']))
{
   $filter=$_GET['filter'];
}else{
   $filter='';
}

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
	
	// variabel pecahkan 0 = tahun
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tanggal
 
	return $pecahkan[2] . '-' . $bulan[ (int)$pecahkan[1] ] . '-' . $pecahkan[0];
}

?>

<body>
	<style type="text/css">
	body{
		font-family: sans-serif;
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
	</style>

	<?php
	header("Content-type: application/vnd-ms-excel");
	header("Content-Disposition: attachment; filename=Laporan_rekapitulasi.xls");
	?>

	<table>
	<tr>
			<th colspan="10" id="judul">Laporan Rekapitulasi</th>  
	</tr>
	  <tr>
		<?php echo '<th colspan="10" id="judul"> Tanggal ' . tgl_indo($tglbayar) . ' s/d ' . tgl_indo($tglbayar1) .'</th>'; ?>  
	  </tr>
		<tr>
	
		<tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Air Tanah</th>
                  <th>Hiburan</th>
                  <th>Hotel</th>
                  <th>Minerba</th>
                  <th>Parkir</th>
                  <th>Penerangan Jalan</th>
                  <th>Reklame</th>
                  <th>Restoran</th>
		</tr>
		<?php 
		// koneksi database
		$servername = "localhost";
		$database = "9pajak_sw_patda";
		$username = "root";
		$password = "toor";
        $conn = mysqli_connect($servername, $username, $password, $database);

		// menampilkan 
		$no = 1;
                  
          $query = mysqli_query($conn, "SELECT SUM( A.patda_total_bayar ) AS TOTAL_PENDAPATAN, B.id_sw AS ID_JENIS_PAJAK, B.jenis_sw AS JENIS_PAJAK, CAST(A.payment_paid AS DATE) AS TGL_BAYAR FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id WHERE A.payment_paid BETWEEN '$tglbayar' AND '$tglbayar1' AND A.payment_flag = '1' GROUP BY TGL_BAYAR, B.jenis_sw ORDER BY `TGL_BAYAR` DESC");
          
          $query2 = mysqli_query($conn, "SELECT SUM( A.patda_total_bayar ) AS TOTAL_PENDAPATAN, B.id_sw AS ID_JENIS_PAJAK, B.jenis_sw AS JENIS_PAJAK, CAST(A.payment_paid AS DATE) AS TGL_BAYAR FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id WHERE A.payment_paid BETWEEN '$tglbayar' AND '$tglbayar1' AND A.payment_flag = '1' GROUP BY B.id_sw ORDER BY `TGL_BAYAR` DESC ");

            $campur = [];
            $campur2 = [];
            $campur3 = [];
            $tanggal = [];
            $jenispajak = [1,2,3,4,5,6,7,8];
            while ($row = mysqli_fetch_array($query)){
              $data2[] = $row;

              $campur[$row['ID_JENIS_PAJAK']][$row['TGL_BAYAR']] = $row['TOTAL_PENDAPATAN'];
              $campur2[$row['TGL_BAYAR']][$row['ID_JENIS_PAJAK']] = $row['TOTAL_PENDAPATAN'];
              $tanggal[$row['TGL_BAYAR']] = 1;

              if($row['ID_JENIS_PAJAK'] == '1'){
                $satu[] = array('tgl' => $row['TGL_BAYAR'],
                                'total' => $row['TOTAL_PENDAPATAN']
                          );
                  
                }
              }

              while ($rows = mysqli_fetch_array($query2)){
                $campur3[$rows['ID_JENIS_PAJAK']] = $rows['TOTAL_PENDAPATAN'];
              }

            foreach ($tanggal as $tgl => $value) {
              echo '<tr>';
              echo '<td>' . $no . '</td>';
              echo '<td>' . $tgl . '</td>';
              foreach ($jenispajak as $key => $id) {
                echo '<td>';
                echo (isset($campur2[$tgl][$id])) ? number_format($campur2[$tgl][$id], 0, ".", ".") : '0';
                echo '</td>';
              }
              echo '</tr>';
              $no++;
            }

            
            echo '<tr>';
            echo '<td colspan="2">' . 'TOTAL' .'</td>';
            foreach ($jenispajak as $key => $ids){
              echo '<td>';
              echo (isset($campur3[$ids])) ? number_format($campur3[$ids], 0, ".", ".") : '0';
              echo '</td>';                  
            }
            echo '</tr>';

         ?>
        </tbody>
      </table>
</body>
</html>