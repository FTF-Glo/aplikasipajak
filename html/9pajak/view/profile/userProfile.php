<?php
$DIR = "PATDA-V1";
$modul = "profile";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$host = ONPAYS_DBHOST;
$pass = ONPAYS_DBPWD;
$db = ONPAYS_DBNAME;
$user = ONPAYS_DBUSER;
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());

$id = $_SESSION['uname'];
$role = $_SESSION['role'];

if (isset($_POST['submit'])) {
    $passlama     = md5($_POST['passlama']);
    $passbaru       = md5($_POST['passbaru']);
    $passkonfirmasi   = md5($_POST['passkonfirmasi']);
	
	if($role == 'rmPatdaWp'){
		$tampil_pass = mysqli_query($conn, "SELECT * FROM CENTRAL_USER WHERE CTR_U_ID = '$id'");
	}else{
		$tampil_pass = mysqli_query($conn, "SELECT * FROM CENTRAL_USER WHERE CTR_U_UID = '$id'");
	}
	
	
    $row = mysqli_fetch_array($tampil_pass);
    if (!empty(trim($passlama)) && !empty(trim($passbaru)) && !empty(trim($passkonfirmasi))) {
        if ($row['CTR_U_PWD']  == $passlama) {
            if ($passbaru == $passkonfirmasi) {
				
				if($role == 'rmPatdaWp'){
				$update_pass = mysqli_query($conn, "UPDATE CENTRAL_USER SET CTR_U_PWD = '$passbaru'  WHERE CTR_U_ID = '$id'");
				}else{
				$update_pass = mysqli_query($conn, "UPDATE CENTRAL_USER SET CTR_U_PWD = '$passbaru'  WHERE CTR_U_UID = '$id'");
				}

                if ($update_pass) {
		    session_start();
		    $_SESSION['suc1'] = "Password berhasil di ubah Silakan Login Kembali";
		    header('Location: http://36.92.151.83:2000/main.php');
		    setcookie("centraldata",false);
                    $error = 2;
                } else {
                    $error = 1;
                    $errors = 'Ada Masalah Saat Ganti Password';
                }
            } else {
                $error = 1;
                $errors = 'Password Baru dan Konfirmasi Password Berbeda';
            }
        } else {
            $error = 1;
            $errors = 'Password Lama Salah';
        }
    } else {
        $error = 1;
        $errors = 'Password Lama, Password Baru, dan Konfirmasi Password Harus Diisi';
    }
}


?>

<div class="container-fluid">
	<br>
    <b>Ganti Password</b>
    <form action="" method="post">
        <div class="form-group">
            <div class="col-md-4">
                <label for="message-text" class="col-form-label">Password Lama</label>
                <input type="password" class="form-control" name="passlama" value="">
                <label for="message-text" class="col-form-label">Password Baru</label>
                <input type="password" class="form-control" name="passbaru" id="myInput" value="" minlength="3" oninvalid="this.setCustomValidity('Password minimal 3 karakter')" required>
                <label for="message-text" class="col-form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="passkonfirmasi" id="myInput" value="" minlength="3" oninvalid="this.setCustomValidity('Password minimal 3 karakter')" required>
                <br>
                <button type="submit" class="btn btn-primary" name="submit" value="submit">Ubah Password</button>
            </div>
        </div>
    </form>
    <br>


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
            Update Password Berhasil
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

<script>
document.getElementById("myInput").addEventListener("invalid", myFunction);
</script>