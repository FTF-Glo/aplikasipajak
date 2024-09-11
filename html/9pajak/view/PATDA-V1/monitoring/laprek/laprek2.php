<?php
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;
global $DBLink;


$servername = "localhost";
$database = "9pajaktrial";
$username = "root";
$password = "toor";
$conn = mysqli_connect($servername, $username, $password, $database);



if(!empty($_POST['tglbayar'])) {
    $tglbayar = $_POST['tglbayar'];
    $tglbayar1 = $_POST['tglbayar1'];
    $filter = $_POST['filter'];
  }else{
    $tglbayar = '';
    $tglbayar1 = '';
    $filter = '';
  }

$today = date('Y-m-d');

?>

<style>
.table th, .table td {
    padding: 0.5rem 1rem;
}
</style>



      <div class="card">
        <div class="card-body collapse in" aria-expanded="true">
          <div class="card-block">
            
            <form action="" method="post" enctype="multipart/form-data">
              <div class="form-group row">
              <label for="staticEmail" class="col-sm-2 col-form-label">Pilih Tanggal</label>
                <div class="col-sm-2">
                  <input class="form-control" type="date" id="tglbayar" name="tglbayar" value='<?= $tglbayar == '' ? $today : $tglbayar; ?>'>
                </div>
              <label for="staticEmail" class="col-sm-2 col-form-label">Sampai Tanggal</label>
                <div class="col-sm-2">
                  <input class="form-control" type="date" id="tglbayar1" name="tglbayar1" value='<?= $tglbayar1 == '' ? $today : $tglbayar1; ?>'>
                </div>



            
            
            <input type="submit" class="btn btn-primary" name="caritgl" value="Cari">
                        <input type="button" class="btn btn-primary" value="Export Excel" onclick="open_win('<?=$tglbayar?>','<?=$tglbayar1?>','<?=$filter?>')">

            <br></br>
            <table class="table table-bordered table-striped">
              <thead>
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
              </thead>
              <tbody>
				<?php 
                                $no = 1;
                if(isset($_POST['caritgl'])){
                  $tglbayar = $_POST['tglbayar'];
                  $tglbayar1 = $_POST['tglbayar1'];
                  
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
                      // var_dump($value['TGL_BAYAR']);
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
                      echo (isset($campur2[$tgl][$id])) ? $campur2[$tgl][$id] : '0';
                      // echo $campur2[$tgl][$id] ?? '0';
                      echo '</td>';

                    }
                    echo '</tr>';
                    $no++;
                  }

                  echo '<tr>';
                  echo '<td colspan="2">' . 'TOTAL' .'</td>';
                  foreach ($jenispajak as $key => $ids){
                    echo '<td>';
                    echo (isset($campur3[$ids])) ? $campur3[$ids] : '0';
                    echo '</td>';                  
                  }
                  echo '</tr>';

              }
               ?>
              </tbody>
            </table>
            </form>
          </div>
        </div>
      </div>


<script>
function open_win(tglbayar, tglbayar1, filter) {
    window.open('http://103.76.172.162:8090/view/PATDA-V1/monitoring/laprek/export-excel.php?tglbayar='+tglbayar+'&'+'tglbayar1='+tglbayar1+'&'+'filter='+filter);
}
</script>