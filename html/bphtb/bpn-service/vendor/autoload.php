<?php
	include 'lib/json.php';

	spl_autoload_register(function ($class) {		
		$vdir = PATH.'/vendor/';
		$cdir = PATH.'/app/';
				
		$vfile = $vdir . str_replace('\\', '/', $class) . '.php';
		$cfile = $cdir . str_replace('\\', '/', $class) . '.php';

		if (file_exists($vfile)){
			require $vfile;			
		} 
		elseif(file_exists($cfile)){
			require $cfile;
		} 
	});
?>
