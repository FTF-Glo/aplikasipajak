<?php
namespace Core;

use \DateTime;

class Helper{
	// echo result from service request
	public static function echoResponse($pResult){
		header('Content-type: application/json; charset=utf-8');
		echo $pResult; 		
	}
	
	// convert date string to date formated
	public static function convertDate($date){
		$date = DateTime::createFromFormat('dmY',$date);
		return $date->format('d/m/Y');
	}	
}
?>
