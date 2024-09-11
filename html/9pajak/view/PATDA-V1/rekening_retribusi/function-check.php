<?php
require_once "ClassPembatalan.php";

$function = $_POST['function'];

$spajak = new Pembatalan;

if($function == 'check_kodebayar'){
    $kodebayar = $_POST['kodebayar'];
    echo $spajak->check_kodebayar($kodebayar);
}

if($function == 'batalkan_pembayaran'){
    $kodebayar = $_POST['kodebayar'];
    echo $spajak->batalkan_pembayaran($kodebayar);
}


