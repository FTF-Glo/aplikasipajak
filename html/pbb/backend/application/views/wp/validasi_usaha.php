<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Validasi Pendaftaran Usaha</h3>
                </div>

            </div>
        
            <div class="card">
                <div class="card-body">
					<?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			        ?>
                    <table id="myTable" class="table table-bordered table-striped table-sm table-hover table-select">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Badan</th>
                                <th>Phone</th>
                                <th>Alamat</th>
                                <th>Lokasi</th>
                                <th></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach($main as $row){
                                    $address = $row['address'].'<br />RT:'.$row['rt'].'/RW:'.$row['rw'];
                                    $loc1 = 'Kel.'.$row['kel'].'- Kec.'.$row['kec'];
                                    $loc2 = $row['kab'].', '.$row['prov'];
                                    $btn = '<a href="'.base_url('wp/validasi_usaha_detail/'.$row['id_usaha']).'" class="btn btn-primary btn-small">Validasi</a>';
                                    echo '
                                            <tr>
                                                <td>'.$row['id_usaha'].'</td>
                                                <td>'.$row['usaha_name'].'</td>
                                                <td>'.$row['badan_name'].'</td>
                                                <td>'.$row['phone'].'</td>
                                                <td>'.$address.'</td>
                                                <td>'.$loc1.'<br />'.$loc2.'</td>
                                                <td>'.$btn.'</td>
                                            </tr>
                                         ';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
	$('#myTable').DataTable();	
});  
</script>
