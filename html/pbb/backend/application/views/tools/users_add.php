<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets-app/vendors/css/ui/jquery-ui.min.css">
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
					<h4>
					    <?php
					        if($id =="") {
					            echo "Tambah Pengguna";
					        } else {
					            echo "Edit Pengguna";
					        }
					    ?>
					</h4> 
				</div>
                <div class="card-body">
                <form action="<?php echo base_url();?>tools_users/save" method="post" id="myFormAdd" accept-charset="utf-8">
                    <input id="tbxID" name="tbxID" value="<?php echo $id;?>" hidden >
                    <div class="row">
                        <div class="col-md-6 pl-3 pr-3">
                            <h5 class="form-section mb-2"><i class="fa fa-user mr-1"></i> Login</h5>
                            <div class="form-group">
                                <label>Username</label>
                                <input name="tbxUsername" id="tbxUsername" value="<?php echo $this->fun->get_value($main,'username');?>" class="form-control"  required <?php echo ($id!="")?"readonly":""; ?>>
                                <span id="msgUsername" class="warn-msg danger"></span>
                            </div>
                            <div class="form-group">
                                <label for="tbxPassword1">Password</label>
                                <input type="password" name="tbxPassword1" id="tbxPassword1" class="form-control" >
                            </div>
                            <div class="form-group">
                                <label for="tbxPassword2">Ulangi Password</label>
                                <input type="password" name="tbxPassword2" id="tbxPassword2" class="form-control">
                            </div>
                            
                            <h5 class="form-section mb-2 mt-3"><i class="fa fa-info mr-1"></i> Detail</h5>
                            <div class="form-group">
                                <label for="tbxName">Nama Lengkap</label>
                                <input name="tbxName" id="tbxName" class="form-control" value="<?php echo $this->fun->get_value($main,'name');?>" required>
                            </div>
                            <div class="form-group">
                                <label for="tbxEmail">Email</label>
                                <input type="email" name="tbxEmail" id="tbxEmail" value="<?php echo $this->fun->get_value($main,'email');?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="tbxHP">HP</label>
                                <input name="tbxHP" id="tbxHP" value="<?php echo $this->fun->get_value($main,'hp');?>" class="form-control">
                            </div>    
                            <div class="form-group">
                                <label for="tbxJabatan">Jabatan</label>
                                <input name="tbxJabatan" id="tbxJabatan" value="<?php echo $this->fun->get_value($main,'jabatan');?>" class="form-control">
                            </div>     
                            
                        </div>
                        <div class="col-md-6 pl-3 pr-3" id="container-check">
                            <h5 class="form-section mb-2"><i class="fa fa-key mr-1"></i> Akses</h5>
                            <div class="text-right mb-1">
                              <input type="checkbox" onchange="checkAll(this)" name="chk[]" /> Pilih Semua
                            </div>
                            <table class="table">
                              <tr>
                                <td>Menu</td>
                                <td>Akses</td>
                              </tr>
                              <?php
                                foreach($menu as $row){
                                  echo '<tr>
                                          <td colspan="2" class="text-center bg-gray">'.$row['name'].'</td>
                                        </tr>
                                       ';
                                  foreach($row['sub_menu'] as $sm){
                                      $check = "";
                                      if($id != ""){
                                        foreach($access as $acc){
                                          if($sm['id'] == $acc['id_menu']){
                                            $check = "checked";
                                          }
                                        }
                                      }
                                      echo '<tr>
                                              <td>'.$sm['name'].'</td>
                                              <td><input type="checkbox" name="tbxMenuAccess[]" value="'.$sm['id'].'" '.$check.'></td>
                                            </tr>';
                                  }
                                }
                              ?>
                            </table>
                        
                        </div>
                        <div class="col-md-12 pl-3 pr-3">
                          <button type="button" class="btn btn-warning mr-1" onclick="window.history.back();">
                            <i class="ft-x"></i> Batal
                          </button>
                          <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Simpan
                          </button>
                        </div>
                    </div>
   
                </div>	
              </form>
			</div><!---card-content-->
		</div>
	  </div>
	</div>
</div>
<script src="<?php echo base_url();?>assets-app/vendors/js/forms/validation/jquery.validate.min.js" ></script>
<script type="text/javascript">


 $(function(){
	$("#myFormAdd").validate({
    rules: {
      tbxPassword1: {
        required: <?php echo ($id=="")?"true":"false";?>,
        minlength: 3
      },
	   tbxPassword2: {
		   equalTo: "#tbxPassword1"
	  },
      action: "required"
    },
    messages: {
	  tbxPassword1: {
        required: "Silahkan masukkan password",
        minlength: "Password harus terdiri minimal 3 karakter"
      },
	  tbxPassword2: {
		equalTo : "Password harus sama",
		required: "Silahkan masukkan ulangi password"
	  },
	  
      action: "Silahkan masukkan password",

    }
	});
});

<?php
if($id == ""){
?>
$("#tbxUsername").keyup(function(){
	var no = $("#tbxUsername").val();
	$.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>tools_users/check_exist_user/"+no,
			success: function(msg){
				var arr = JSON.parse(msg);
				if(arr.exist != 0){
					$("#msgUsername").html('Username sudah terdaftar !')
					$("#tbxUsername").parents().addClass('error');
					$("#tbxUsername").parents().removeClass('validate');
				}else {
					$("#msgUsername").html("");
					
				}					
			}
		});	
});
<?php } ?>
 function checkAll(ele) {
     var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     }
 }

</script>
