<?php

function bytesToSize($bytes, $precision = 2) {
	$kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

function errorToMessage($code) {
	switch ($code) {
		case UPLOAD_ERR_INI_SIZE:
			$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
			break;
		case UPLOAD_ERR_PARTIAL:
			$message = "The uploaded file was only partially uploaded";
			break;
		case UPLOAD_ERR_NO_FILE:
			$message = "No file was uploaded";
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message = "Missing a temporary folder";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message = "Failed to write file to disk";
			break;
		case UPLOAD_ERR_EXTENSION:
			$message = "File upload stopped by extension";
			break;
		default:
			$message = "Unknown upload error";
			break;
	}
	return $message;
}

function compress($src, $quality = 75) {
	// rubah ukuran
	$imageProp = getimagesize($src);
	
	if($imageProp[0] > $imageProp[1]){
		$fct = $imageProp[0];
	}else{
		$fct = $imageProp[1];
	}
	$faktor = (1168 / $fct);
	
	$width = round($imageProp[0] * $faktor);
	$height = round($imageProp[1] * $faktor);
	
	$thumb = imagecreatetruecolor($width, $height);
	
	switch($imageProp['mime']) {
		case 'image/jpg':
		case 'image/jpeg':
			$img = imagecreatefromjpeg($src);
			break;
		case 'image/png':
			$img = imagecreatefrompng($src);
			break;
	}
	// imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, $imageProp[0], $imageProp[1]);
	imagecopyresized($thumb, $img, 0, 0, 0, 0, $width, $height, $imageProp[0], $imageProp[1]);
	imagejpeg($thumb, $src, $quality);
	// rubah dpi
	if(get_dpi($src) > 100){
		$bin = file_get_contents($src);
		$bin = substr_replace($bin, pack('cnn', 1, 100, 100), 13, 5);
		file_put_contents($src, $bin);
	}
	return $src;
}
function get_dpi($filename){
    $a = fopen($filename,'r');
    $string = fread($a,20);
    fclose($a);

    $data = bin2hex(substr($string,14,4));
    $x = substr($data,0,4);
    $y = substr($data,4,4);
    return hexdec($x);
}
