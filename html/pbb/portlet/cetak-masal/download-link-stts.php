<?php
if(isset($_GET['file'])){
	$file = base64_decode($_GET['file']);
	$arrName = explode('/',$file);
	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="'.end($arrName).'"');
	readfile($file);
}
?>
