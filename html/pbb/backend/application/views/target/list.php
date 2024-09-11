<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <h3>Target Pajak</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <table id="myTable" class="table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Jenis Pajak</th>
                            <th>Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach($target as $row){
                                foreach($type as $ty){
                                    if($ty['id'] == $row['id_pajak_type']){
                                        $type_p = $ty['name'];
                                    }
                                }
                                echo '
                                        <tr>
                                            
                                            <td>'.$row['year'].'</td>
                                            <td>'.$type_p.'</td>
                                            <td>'.$row['value'].'</td>
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
<script>
$(document).ready(function() {
	$('#myTable').DataTable();	
});  
</script>
