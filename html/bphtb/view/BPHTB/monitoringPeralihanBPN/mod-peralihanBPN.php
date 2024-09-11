 <?php

session_start();

if(!empty($_POST['tglbayar'])) {
  $tglbayar = $_POST['tglbayar'];
}else{
  $tglbayar = '';
}
if (!empty($_POST['nop'])) {
  $nop = $_POST['nop'];
}
else{
  $nop='';
}
if (!empty($_POST['ktp'])) {
  $ktp = $_POST['ktp'];
}
else{
  $ktp='';
}

$today = date('Y-m-d');

$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
$par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID;
function getApi($tglbayar){
  global $data, $DBLink;
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
  // die(var_dump($data));
  return $data;
}


?>

<!-- select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        <select class="form-control" style="text-align: center;" id="select2">
          <option value="active">Monitoring Peralihan BPN</option>
          <!-- <option value="<?= base64_encode('a=aBPHTB&m=m141') ?>">Data ATR BPN</option> -->
        </select>
          
        </div>


  <div class="card-body card-dashboard" aria-expanded="true">
    <div class="card-block">
      
      <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group row">
          <!-- <div class="col-lg-12">
            <div class="form-group col-sm-2">
              <label for="staticEmail" class="col-form-label">Cari Berdasarkan</label>
            </div>
            <!- - <div class="form-group col-sm-2">
              <input class="form-control" type="text" id="nop" name="nop" value='<?= $nop; ?>' placeholder="NOP">
            </div>
            <div class="form-group col-sm-2">
              <input class="form-control" type="text" id="ktp" name="ktp" value='<?= $ktp; ?>' placeholder="NO KTP">
            </div> - ->
            <div class="form-group col-sm-2">
              <label class="col-form-label">Tanggal AKTA :</label>
            </div>
            <!- - <div class="form-group col-sm-2">
                <input class="form-control" type="date" id="tglbayar" name="tglbayar" value='<?= $tglbayar == '' ? $today : $tglbayar; ?>'>
            </div> - ->
            <div class="form-group col-sm-2">
                <input class="form-control" type="date" id="tglbayar" name="tglbayar" value='<?= $tglbayar == '' ? $today : $tglbayar; ?>'>
            </div>
            <div class="form-group col-sm-1">
                <input type="submit" class="btn btn-primary" name="caritgl" value="Cari">
                <!- - <input type="submit" class="btn btn-primary" name="caritgl" value="Cari"> - ->
            </div>
            <div class="form-group col-sm-1">
                <input type="button" class="btn btn-primary" value="Cetak" onclick="open_win('<?=$tglbayar?>')">
                <!- - <input type="submit" class="btn btn-primary" name="caritgl" value="Cari"> - ->
            </div>
          </div> -->
          <!-- <div class="col-sm-12"> -->
          <!-- </div> -->
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
                <th>NOP</th>
                <th>NIB</th>
                <th>NIK</th>
                <th>NPWP</th>
                <th>Nama WP</th>
                <th>Kelurahan OP</th>
                <th>Kecamatan OP</th>
                <th>Kota OP</th>
                <th>Luas Tanah</th>
                <!-- <th>No Sertifikat</th> -->
                <th>No Akta</th>
                <th>PPAT</th>
                <th>Tanggal Akta</th>
            </thead>
            <tbody>
      <?php 
              if(isset($_POST['caritgl'])){
                $tglbayar = $_POST['tglbayar'];


                $data = getApi($tglbayar);
                $data = $data['result'];
                foreach ($data as $key => $value) {
                  echo '<tr>';

                  echo '<td>' . $value['NOP'] . '</td>';
                  echo '<td>' . $value['NOMOR_INDUK_BIDANG'] . '</td>';
                  echo '<td>' . $value['NIK'] . '</td>';
                  echo '<td>' . $value['NPWP'] . '</td>';
                  echo '<td>' . $value['NAMA_WP'] . '</td>';
                  echo '<td>' . $value['KELURAHAN_OP'] . '</td>';
                  echo '<td>' . $value['KECAMATAN_OP'] . '</td>';
                  echo '<td>' . $value['KOTA_OP'] . '</td>';
                  echo '<td>' . $value['LUASTANAH_OP'] . '</td>';
                  echo '<td>' . $value['NOMOR_AKTA'] . '</td>';
                  echo '<td>' . $value['NAMA_PPAT'] . '</td>';
                  echo '<td>' . $value['TANGGAL_AKTA'] . '</td>';
                  
                  // echo '<td>' . $value['NTPD'] . '</td>';
                  // echo '<td>' . $value['KOORDINAT_X'] . '</td>';
                  // echo '<td>' . $value['KOORDINAT_Y'] . '</td>';
                  // echo '<td>' . $value['JENIS_HAK'] . '</td>';

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
    window.open('http://103.76.172.162:8070/view/BPHTB/monitoringPeralihanBPN/export_excel.php?tglbayar='+tglbayar);
}

$(document).ready( function () {
    // $('select2').select2({
    //     width : 'resolve',
    //     theme: "classic"
    // });
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