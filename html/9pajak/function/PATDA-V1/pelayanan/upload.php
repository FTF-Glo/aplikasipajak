<?php
$DIR = "PATDA-V1";
$modul = "pelayanan";
$submodul = "";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$host = ONPAYS_DBHOST;
$pass = ONPAYS_DBPWD;
$db = ONPAYS_DBNAME;
$user = ONPAYS_DBUSER;
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());

// var_dump(isset($_POST['upload']));die;

if (isset($_POST['upload'])){
   
   
    $idberkas = $_POST['sptpd'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name'];
    // var_dump($tipe);die;
    if(!in_array(($tipe),$exts)){
        echo"<script>alert('Format hanya boleh jpeg dan png !');history.go(-1);</script>";
    }else{
		if(($size !=0)&&($size>300000)){
            echo"<script>alert('Ukuran Gambar Terlalu besar !');history.go(-1);</script>";
		}else{
                $sameName = 0; // Menyimpan banyaknya file dengan nama yang sama dengan file yg diupload
                $handle = opendir($uploadDir);
                while(false !== ($file = readdir($handle))){ // Looping isi file pada directory tujuan
                    // Apabila ada file dengan awalan yg sama dengan nama file di uplaod
                    if(strpos($file,$extractFile['filename']) !== false)
                    $sameName++; // Tambah data file yang sama
                }
         
                $newName = empty($sameName) ? $uploadFile['name'] : $extractFile['filename'].'('.$sameName.').'.$extractFile['extension'];


                $qry="select * from patda_upload_file where CPM_NO_SPTPD = '$idberkas' AND CPM_KODE_LAMPIRAN= '$kodelampiran'";
                $result = mysqli_query($conn, $qry);
                $row = mysqli_num_rows($result);
                $rows = mysqli_fetch_row($result);

                if($row>=1){
                    $oldimg = $rows[2];
                    unlink($uploadDir . $oldimg);
                    $query = "UPDATE patda_upload_file SET CPM_FILE_NAME='$newName' where CPM_NO_SPTPD= '$idberkas' AND CPM_KODE_LAMPIRAN= '$kodelampiran'";
                    mysqli_query($conn, $query);

                    if(move_uploaded_file($uploadFile['tmp_name'],$uploadDir.$newName)){
                        echo"<script>alert('Gambar Berhasil diubah !');history.go(-1);</script>";
                        //header('Location: images.php');
                    }
                    else{
                        echo"<script>alert('File Gagal diupload !');history.go(-1);</script>";
                    }
                }else{
                    $sql = "INSERT INTO patda_upload_file (CPM_NO_SPTPD, CPM_FILE_NAME, CPM_KODE_LAMPIRAN) VALUES ('$idberkas', '$newName', '$kodelampiran')";
                    mysqli_query($conn, $sql);
                    
                    if(move_uploaded_file($uploadFile['tmp_name'],$uploadDir.$newName)){
                        echo"<script>alert('Gambar Berhasil diupload !');history.go(-1);</script>";
                        //header('Location: images.php');
                    }
                    else{
                        echo"<script>alert('File Gagal diupload !');history.go(-1);</script>";
                    }

                }


            } 
        }
}
// var_dump($_POST);die;
	//1
	if (isset($_POST['upload1'])){
    $idberkas = $_POST['sptpd1'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas1'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas1']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas1']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name1'];
    

    }
	
	//2
	if (isset($_POST['upload2'])){
    $idberkas = $_POST['sptpd2'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas2'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas2']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas2']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name2'];
    

    }
	
	
	//3
	if (isset($_POST['upload3'])){
    $idberkas = $_POST['sptpd3'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas3'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas3']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas3']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name3'];
    
    }
	
	//4
	if (isset($_POST['upload4'])){
    $idberkas = $_POST['sptpd4'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas4'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas4']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas4']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name4'];
    
    }
	
	
	//5
	if (isset($_POST['upload5'])){
    $idberkas = $_POST['sptpd5'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas5'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas5']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas5']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name5'];
    
    }
	
	
	//6
	if (isset($_POST['upload6'])){
    $idberkas = $_POST['sptpd6'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas6'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas6']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas6']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name6'];
    
    }
	
	
	//7
	if (isset($_POST['upload7'])){
    $idberkas = $_POST['sptpd7'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas7'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas7']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas7']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name7'];
    
    }
	
	
	//8
	if (isset($_POST['upload8'])){
    $idberkas = $_POST['sptpd8'];   
    $uploadDir = "upload/";
    $uploadFile = $_FILES['berkas8'];
    $extractFile = pathinfo($uploadFile['name']);
    $size = $_FILES['berkas8']['size']; //untuk mengetahui ukuran file
    $tipe = $_FILES['berkas8']['type'];// untuk mengetahui tipe file
    $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');

    $kodelampiran = $_POST['name8'];
    
    }

    //9 upload berkas transfer 
	if (isset($_POST['upload9'])){
        $idberkas = $_POST['sptpd9'];   
        $uploadDir = "upload/";
        $uploadFile = $_FILES['berkas9'];
        $extractFile = pathinfo($uploadFile['name']);
        $size = $_FILES['berkas9']['size']; //untuk mengetahui ukuran file
        $tipe = $_FILES['berkas9']['type'];// untuk mengetahui tipe file
        $exts =array('image/jpg','image/jpeg','image/pjpeg','image/png','image/x-png');
    
        $kodelampiran = $_POST['name9'];
        
    
        }
	
        // var_dump($uploadFile);die;
	    if(!in_array(($tipe),$exts)){
        echo"<script>alert('Format hanya boleh jpeg dan png !');history.go(-1);</script>";
    }else{
		if(($size !=0)&&($size>2000000)){
            echo"<script>alert('Ukuran Gambar Terlalu besar !');history.go(-1);</script>";
		}else{
                $sameName = 0; // Menyimpan banyaknya file dengan nama yang sama dengan file yg diupload
                $handle = opendir($uploadDir);
                while(false !== ($file = readdir($handle))){ // Looping isi file pada directory tujuan
                    // Apabila ada file dengan awalan yg sama dengan nama file di uplaod
                    if(strpos($file,$extractFile['filename']) !== false)
                    $sameName++; // Tambah data file yang sama
                }
         
                //$newName = empty($sameName) ? $uploadFile['name'] : $extractFile['filename'].'('.$sameName.').'.$extractFile['extension'];
				$newName = empty($sameName) ? $uploadFile['name'] : $extractFile['filename'].'_'.round(microtime(true)).'.'.$extractFile['extension'];

                $qry="select * from patda_upload_file where CPM_NO_SPTPD = '$idberkas' AND CPM_KODE_LAMPIRAN= '$kodelampiran'";
                $result = mysqli_query($conn, $qry);
                $row = mysqli_num_rows($result);
                $rows = mysqli_fetch_row($result);

                if($row>=1){
                    $oldimg = $rows[2];
                    unlink($uploadDir . $oldimg);
                    $query = "UPDATE patda_upload_file SET CPM_FILE_NAME='$newName' where CPM_NO_SPTPD= '$idberkas' AND CPM_KODE_LAMPIRAN= '$kodelampiran'";
                    mysqli_query($conn, $query);

                    if(move_uploaded_file($uploadFile['tmp_name'],$uploadDir.$newName)){
                        echo"<script>alert('Gambar Berhasil diubah !');history.go(-1);</script>";
                        //header('Location: images.php');
                    }
                    else{
                        echo"<script>alert('File Gagal diupload !');history.go(-1);</script>";
                    }
                }else{
                    $sql = "INSERT INTO patda_upload_file (CPM_NO_SPTPD, CPM_FILE_NAME, CPM_KODE_LAMPIRAN) VALUES ('$idberkas', '$newName', '$kodelampiran')";
                    mysqli_query($conn, $sql);
                    
                    if(move_uploaded_file($uploadFile['tmp_name'],$uploadDir.$newName)){
                        echo"<script>alert('Gambar Berhasil diupload !');history.go(-1);</script>";
                        //header('Location: images.php');
                    }
                    else{
                        echo"<script>alert('File Gagal diupload !');history.go(-1);</script>";
                    }

                }


            } 
        }




    

?>


