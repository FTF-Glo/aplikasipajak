<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-right col-md-6 col-12">
          <div class="media width-250 float-right">
            <media-left class="media-middle">
              <div id="sp-bar-total-sales"></div>
            </media-left>
            <div class="media-body media-right text-right">
            </div>
          </div>
        </div>
      </div>
	  <div class="content-body">
		<div class="card">	
			<div class="card-content">
				<div class="card-header">
					<h4>List Pengguna</h4> 
					<div class="heading-elements">
                        <a href="<?php echo base_url();?>tools_users/add" class="btn btn-primary"><i class="fa fa-plus mr-1"></i>Tambah Pengguna</a>
					</div>
				</div>
				<div class="card-body">
					<table class="table responsive table-striped table-bordered zero-configuration table-sm" id="myTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>Username</th>
								<th>Fullname</th>
								<th>Email</th>
								<th>HP</th>
								<th>Jabatan</th>
								<th>Last Login</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
				
								foreach($main as $key => $row){
									$no = $key+1;
									echo '
											<tr>
												<td>'.$no.'</td>
											    <td>'.$row['username'].'</td>
											    <td>'.$row['name'].'</td>
											    <td>'.$row['email'].'</td>
												<td>'.$row['hp'].'</td>
												<td>'.$row['jabatan'].'</td>
												<td>'.$row['lastlogin'].'</td>
								                <td>
                                                    <a href="'.base_url().'tools_users/add/'.$row['id'].'" class="btn btn-icon btn-a" data-toggle="tooltip" data-placement="top" title="Ubah Data">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button data-toggle="tooltip" data-placement="top" title="Hapus Data" class="btn btn-icon btn-del"><i class="fa fa-trash"></i>
                                                        <span class="oID" hidden>'.$row['id'].'</span>
                                                        <span class="oName" hidden>'.$row['username'].'</span>
                                                    </button>
								                </td>
											</tr>
										 ';
								}
							?>
						</tbody>
					</table>
				</div>
			</div><!---card-content-->
		</div>
	  </div>
	</div>
</div>
<script>
	$(document).ready(function() {
		var table = $('#myTable').DataTable({
			"autoWidth":false,
			"order": [[ 0, "asc" ]]
			   
		});	
	});
</script>