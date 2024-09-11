<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>Master Kelas Usaha</h3>
                </div>
            </div>
        
            <div class="card">
                <div class="card-body">
					<?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			        ?>
                    <table id="myTable" class="table table-bordered table-striped table-hover table-select">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Keterangan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                
                                foreach($main as $row){
                                    $ranges_text = "";
                                    foreach($use as $per){
                                        if($per['id_class'] == $row['id']){
                                             $range = explode(',',$per['ranges']);
								            if($range[1] == ""){$range[1] = " <";} else {$range[1] = ' - '.$range[1];}
								            $ranges_text .= '<strong>'.$per['nilai'].'</strong> = '.$range[0].$range[1].'<br />';
                                        }
                                    }
                                    $btn =  $this->html->href(base_url('parameter/airtanah_range/'.$row['id']),'<i class="fa fa-th-list"></i>','btn btn-icon mr-1','','data-toggle="tooltip" data-placement="top"
                                    title="Edit Parameter Minerba"');
                                    echo '
                                        <tr>
                                            <td>'.$row['id'].'</td>
                                            <td>'.$row['title'].'</td>
                                            <td>'.$ranges_text.'</td>
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
