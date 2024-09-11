<?php 
date_default_timezone_set("Asia/Jakarta");
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$DIR = "PATDA-V1";
$modul = "rekening_retribusi";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
// var_dump($sRootPath);die;
$conn = mysqli_connect(ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);

// Handle Delete Request
// var_dump($_GET['delete_id']);die;
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM rekening_retribusi WHERE id = '$delete_id'";
    // var_dump($sql_delete);
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>
        alert('Berhasil di hapus');
        window.history.back();
      </script>";
    } else {
        echo "Error: " . $sql_delete . "<br>" . mysqli_error($conn);
    }
}

// Handle Insert Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_penerimaan = $_POST['jenis_penerimaan'];
    $nama_pendapatan = $_POST['nama_pendapatan'];
    $rekening = $_POST['rekening'];
    $anggaran = $_POST['anggaran'];
    $target = $_POST['target'];

    $sql = "INSERT INTO rekening_retribusi (nama_pendapatan,rekening,jenis_penerimaan, anggaran, target)
            VALUES ('$nama_pendapatan', '$rekening', '$jenis_penerimaan', '$anggaran', '$target')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('New record created successfully');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<style>
		.form-group{
			font-size:1rem;
		}
        .table_data{
            background: rgb(111, 0, 0);
            background: linear-gradient(175deg, rgba(111, 0, 0, 1) 28%, rgba(0, 0, 0, 1) 89%);
            color: white;
        }
	</style>
	
  </head>
  
  <body>
<p style="font-size : 20px; text-align:center">Rekening Retribusi</p><br>
<button type="button" class="btn btn-primary lm-btn mb-1 tab" data-toggle="modal" data-target="#exampleModal">
<i class="fa fa-add"></i> Tambah Rekening
</button>
  <table class="table">
  <thead>
    <tr>
      <th style="" class="table_data">No</th>
      <th class="table_data" style="width:150px">Rekening</th>
      <th class="table_data">Jenis Penerimaan</th>
      <th class="table_data">Nama Pendapatan</th>
      <th class="table_data">Target</th>
      <th class="table_data">Anggaran</th>
      <th class="table_data">action</th>
    </tr>
  </thead>
  <tbody>
    <?php 
        $sql = "SELECT rek.id as id_rekekning , rek.*, pen.* FROM rekening_retribusi rek inner join jenis_penerimaan_retribusi pen ON rek.jenis_penerimaan = pen.id";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<th style='background-color:white !important'>{$no}</th>";
                echo "<td>{$row['rekening']}</td>";
                echo "<td>{$row['jenis_penerimaan']}</td>";
                echo "<td>{$row['nama_pendapatan']}</td>";
                echo "<td>{$row['target']}</td>";
                echo "<td>".number_format($row['anggaran'],2)."</td>";
                // echo "<td><a href=view/{$DIR}/{$modul}?delete_id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin Ingin Menghapus Data in ?\")'>Delete</a></td>";
                echo "<td><a href='view/{$DIR}/{$modul}?delete_id={$row['id_rekekning']}' class='btn btn-danger lm-btn' onclick='return confirm(\"Yakin Ingin Menghapus Data ini?\")'><i class=\"fa fa-trash\"></i> Delete</a></td>";
                echo "</tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center'>Data Tidak Ada</td></tr>";
        }
    ?>
  </tbody>  
</table>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Insert Rekening Baru</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form method="POST" action="">
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Jenis Penerimaan</label>
                <div class="col-sm-8">
                    <select class="form-control" name="jenis_penerimaan" required>
                        <option selected disabled>--Pilih--</option>
                        <?php
                       
                        $sql = "SELECT id, jenis_penerimaan FROM jenis_penerimaan_retribusi";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['id']}'>{$row['jenis_penerimaan']}</option>";
                            }
                        } else {
                            echo "<option disabled>No data found</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Nama Pendapatan</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="nama_pendapatan" placeholder="Nama Pendapatan" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Rekening</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="rekening" placeholder="Rekening" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Anggaran</label>
                <div class="col-sm-8">
                    <input type="number" class="form-control" name="anggaran" placeholder="Anggaran">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Target</label>
                <div class="col-sm-8">
                    <input type="number" class="form-control" name="target" placeholder="Target" >
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
     
    </div>
  </div>
</div>

  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </body>
</html>