<?php 
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);	
	define('PATH',__DIR__);
	
	require_once 'vendor/autoload.php';
	Core\Route::autoload();
?>


