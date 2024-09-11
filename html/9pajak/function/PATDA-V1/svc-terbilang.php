<?php
if (isset($_POST['num'])) {
    require_once("class-pajak.php");
    $pajak = new Pajak();
    $num = str_replace(",", "", $_POST['num']);
    if (is_numeric($num))
        echo ucfirst($pajak->SayInIndonesian($num));
}
?>