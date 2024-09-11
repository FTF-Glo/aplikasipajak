<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>List SPPT telah terbayar</h3>
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
                                <th>Waktu Bayar</th>
                                <th>Masa</th>
                                <th>Usaha</th>
                                <th>Jenis Pajak</th>
                                <th>Total Pajak</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var table;
    $(document).ready(function() {
        table = $('#myTable').DataTable({ 
            "processing": true, 
            "serverSide": true, 
            "order": [], 
            "ajax": {
                "url": "<?php echo site_url('sppt/getSPPTList/3')?>",
                "type": "POST"
            },
            //"columnDefs": [{ 
               // "targets": [1], 
               // "orderable": false, 
            //}],
        });
    });

</script>
