<?php
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
// ------------
// MAIN PROGRAM
// ------------

if (!$User) {
	die();
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

// bypass cek function granted
// $bOK = true;

if ($bOK) {
echo"Fungsi 0";	
}
?>
	