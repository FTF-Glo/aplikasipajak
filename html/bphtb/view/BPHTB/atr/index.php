 <?php

if(!empty($_POST['tglbayar'])) {
  $tglbayar = $_POST['tglbayar'];
}else{
  $tglbayar = '';
}

$today = date('Y-m-d');

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
    CURLOPT_POSTFIELDS => 'USERNAME=bapendalampungselatan&PASSWORD=kablampungselatan-&TANGGAL=' . $day . '%2F' . $month .'%2F' .$year,
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


?>
<style>
.table th, .table td {
    padding: 0.5rem 1rem;
}
.card-header{
	text-align:center;
	font-size:22px;
}
</style>

<div class="card">
		<div class="card-header">
        <!-- <select class="form-control" style="text-align: center;" id="select2">
          <option value="<?= base64_encode('a=aBPHTB&m=mMonitoringPeralihanBPN') ?>">Monitoring Peralihan BPN</option>
          <option value="active" selected>Data ATR BPN</option>
        </select> -->
        Data ATR BPN
        </div>


  <div class="card-body card-dashboard" aria-expanded="true">
    <div class="card-block">
      
      <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="staticEmail" class="col-sm-2 col-form-label">Pilih Tanggal</label>
          <div class="col-sm-2">
            <input class="form-control" type="date" id="tglbayar" name="tglbayar" value='<?= $tglbayar == '' ? $today : $tglbayar; ?>'>
          </div>

      
      <input type="submit" class="btn btn-primary" name="caritgl" value="Cari">
      <input type="button" class="btn btn-primary" value="Cetak" onclick="open_win('<?=$tglbayar?>')">


      <br></br>
      <table class="table table-bordered table-striped table-responsive" id="table_id" class="display">
        <thead>
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
        </thead>
        <tbody>
  <?php 
          if(isset($_POST['caritgl'])){
            $tglbayar = $_POST['tglbayar'];


            $data = getApi($tglbayar);
            $data = $data['result'];
            foreach ($data as $key => $value) {
              echo '<tr>';
              echo '<td>' . $value['NOMOR_AKTA'] . '</td>';
              echo '<td>' . $value['TANGGAL_AKTA'] . '</td>';
              echo '<td>' . $value['NAMA_PPAT'] . '</td>';

              echo '<td>' . $value['NOP'] . '</td>';
              echo '<td>' . $value['NTPD'] . '</td>';
              echo '<td>' . $value['NOMOR_INDUK_BIDANG'] . '</td>';
              echo '<td>' . $value['KOORDINAT_X'] . '</td>';
              echo '<td>' . $value['KOORDINAT_Y'] . '</td>';
              echo '<td>' . $value['NIK'] . '</td>';
              echo '<td>' . $value['NPWP'] . '</td>';
              echo '<td>' . $value['NAMA_WP'] . '</td>';
              echo '<td>' . $value['KELURAHAN_OP'] . '</td>';
              echo '<td>' . $value['KECAMATAN_OP'] . '</td>';

              echo '<td>' . $value['KOTA_OP'] . '</td>';
              echo '<td>' . $value['LUASTANAH_OP'] . '</td>';
              echo '<td>' . $value['JENIS_HAK'] . '</td>';

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



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1-rc2/jquery.min.js" integrity="sha512-+ixTW85lGpwQjTESH/P7tmcyX7c8tzKWSeo6mX/XusuJf4yif5xKBKGTn1vbsGNxBSR0wT1o68Is76MskWu3Lw==" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css">
  
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.js"></script>
<script>
function open_win(tglbayar) {
    window.open('http://103.76.172.162:8070/view/BPHTB/atr/export_excel.php?tglbayar='+tglbayar);
}

$(document).ready( function () {

    $('#select2').change(function(){
      var optval = $(this).val()
      if (optval!=='active') {
        window.location = 'main.php?param='+optval;
      }
    })
    $('#table_id').DataTable();
} );

$('#table_id').dataTable( {
  "searching": false,
  "language": {
      "emptyTable": "Data tidak tersedia",
      "scrollX": true
    }
} );
</script>