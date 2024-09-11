<?php
$modul = "profile";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$host = ONPAYS_DBHOST;
$pass = ONPAYS_DBPWD;
$db = ONPAYS_DBNAME;
$user = ONPAYS_DBUSER;
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());

$ids = $_SESSION['uname'];
$role = $_SESSION['role'];
if($role == 'rmPatdaWp'){
	$tampil = mysqli_query($conn, "SELECT * FROM CENTRAL_USER WHERE CTR_U_ID = '$id'");
}else{
  $tampil = mysqli_query($conn, "SELECT * FROM CENTRAL_USER WHERE CTR_U_UID = '$ids'");
}
$row = mysqli_fetch_array($tampil);



if (isset($_POST['submit'])) {
    $usernamebaru     = $_POST['username'];
    $ids     = $_POST['ids'];
	
    if (!empty(trim($usernamebaru))) {
				
				$update_username = mysqli_query($conn, "UPDATE CENTRAL_USER SET CTR_U_UID = '$usernamebaru'  WHERE CTR_U_ID = '$ids'");
				
                if ($update_username) {
            		    session_start();
            		    $_SESSION['suc2'] = "Username berhasil di ubah Silakan Login Kembali";
                    // header('Location: http://103.76.172.162:8090/main.php');
            		    header('Location: http://36.92.151.83:7083/main.php');
            		    setcookie("centraldata",false);
                    $error = 2;
                } else {
                    $error = 1;
                    $errors = 'Ada Masalah Saat Ganti Username';
                }
            
	}
}
?>

<div class="container-fluid">
	<br>
    <b>User Profile</b>
    <form action="" method="post">
        <div class="form-group">
            <div class="col-md-4">
                
                <input type="hidden" class="form-control" name="ids" value="<?= $row['CTR_U_ID']; ?>" readonly> 
                
                <label for="message-text" class="col-form-label">Username Sekarang</label>
                <input type="text" class="form-control" name="usernamelama" value="<?= $row['CTR_U_UID']; ?>" readonly> 
                
                <label for="message-text" class="col-form-label">Username Baru</label>
                 <input type="text" class="form-control" name="username" id="username" class="form-control" value="" minlength="3" oninvalid="this.setCustomValidity('Username minimal 3 karakter')" required/>
                  <span id="availability"></span>
                  <br /><br />
                <button type="submit" name="submit" class="btn btn-info" id="register" disabled>Simpan</button>   
            </div>
        </div>
    </form>


</div>

<br>
    <!-- alert post -->
    <?php if ($error == 1) : ?>
        <div class="alert alert-danger" role="alert" id="sizealert">
            <?php echo $errors ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error == 2) : ?>
        <div class="alert alert-success" role="alert" id="sizealert">
            Update username berhasil, silakan login ulang
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

<script>  
 $(document).ready(function(){  
   $('#username').blur(function(){

     var username = $(this).val();

     $.ajax({
      url:'view/profile/check.php',
      method:"POST",
      data:{user_name:username},
      success:function(data)
      {
       if(data != '0')
       {
        $('#availability').html('<span class="text-danger">Username sudah digunakan</span>');
        $('#register').attr("disabled", true);
       }
       else
       {
        $('#availability').html('<span class="text-success">Username tersedia</span>');
        $('#register').attr("disabled", false);
       }
      }
     })

  });
 });  


document.getElementById("username").addEventListener("invalid", myFunction);
</script>