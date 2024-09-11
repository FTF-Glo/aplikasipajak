<?php

if ($_GET['tglbayar'] != '')
{
   $tglbayar = $_GET['tglbayar'];
}else{
   $tglbayar = date('Y-m-d');
}

function getApi($tglbayar){

    $curl = curl_init();
    // curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
    $orderdate = explode('-', $tglbayar.'-'.'-');
    $year = $orderdate[0];
    $month   = $orderdate[1];
    $day  = $orderdate[2];
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://services.atrbpn.go.id/BpnApiService/api/BPHTB/getDataATRBPN',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'USERNAME=bapendapringsewu&PASSWORD=a&TANGGAL=' . $day . '%2F' . $month .'%2F' .$year,
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic OmFkbWlu',
        'Content-Type: application/x-www-form-urlencoded'
      ),
      CURLOPT_CAINFO =>  dirname(__FILE__)."/cacert.pem",
    ));
    
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    $response;
    
    $data = json_decode($response, true);
    return $data;
  }

$data = getApi($tglbayar);
$data = $data['result'];

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
  
  .str{ 
      mso-number-format:\@; 
  } 

	</style>

	<?php
	header("Content-type: application/vnd-ms-excel");
	header("Content-Disposition: attachment; filename=Laporan_atr.xls");
	?>

    
	<table>
  <tr>
    <th colspan="16" id="judul">Data ATRBPN</th>  
  </tr>
  <tr>
    <?php echo '<th colspan="16" id="judul"> Tanggal ' . tgl_indo($tglbayar) . '</th>'; ?>  
  </tr>
		<tr>
            <th>No Akta</th>
            <th>Tanggal Akta</th>
            <th>Nama PPAT</th>
            <th>NOP</th>
            <th>NTPD</th>
            <th>Nomor Induk Bidang</th>
            <th>Koordinat X</th>
            <th>Koordinat Y</th>
            <th>NIK</th>
            <th>NPWP</th>
            <th>Nama WP</th>
            <th>Kelurahan OP</th>
            <th>Kecamatan OP</th>
            <th>Kota OP</th>
            <th>Luas Tanah OP</th>
            <th>Jenis Hak</th>
		</tr>
		<?php 
	
            foreach ($data as $key => $value) {
            echo '<tr>';
            echo '<td>' . $value['NOMOR_AKTA'] . '</td>';
            echo '<td>' . $value['TANGGAL_AKTA'] . '</td>';
            echo '<td>' . $value['NAMA_PPAT'] . '</td>';
            echo '<td class="str">' . $value['NOP'] . '</td>';
            echo '<td>' . $value['NTPD'] . '</td>';
            echo '<td>' . $value['NOMOR_INDUK_BIDANG'] . '</td>';
            echo '<td>' . $value['KOORDINAT_X'] . '</td>';
            echo '<td>' . $value['KOORDINAT_Y'] . '</td>';
            echo '<td class="str">' . $value['NIK'] . '</td>';
            echo '<td>' . $value['NPWP'] . '</td>';
            echo '<td>' . $value['NAMA_WP'] . '</td>';
            echo '<td>' . $value['KELURAHAN_OP'] . '</td>';
            echo '<td>' . $value['KECAMATAN_OP'] . '</td>';
            echo '<td>' . $value['KOTA_OP'] . '</td>';
            echo '<td>' . $value['LUASTANAH_OP'] . '</td>';
            echo '<td>' . $value['JENIS_HAK'] . '</td>';

            }
            echo '</tr>';

         ?>
        </tbody>
      </table>
</body>
</html>