<?php
if (!isset($data)) {
	die("Forbidden direct access");
}

if (!$User) {
	die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
	die("Function access not permitted");
}

require_once("inc/PBB/dbFinalSppt.php");

$dbFinalSppt = new DbFinalSppt($dbSpec);


?>