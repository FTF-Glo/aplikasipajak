<?php

define('EPSON', "epson");
define('HP', "hp");
define('IBM', "ibm");
define('PATH', "driver/"); // ./inc/report/driver

class PrinterType {
	
	// var $EPSON = new PrinterType("epson");
	// var $HP = new PrinterType("hp");
	// var $IBM = new PrinterType("ibm");
	
	var $type;
	var $propertiesFile;
	
	function PrinterType($type, $propertiesFile = null) {
		$this->type = $type;
		$this->propertiesFile = $propertiesFile;
		if ($this->propertiesFile == null) {
			$this->propertiesFile = $type . ".txt";
		}
		
		// add path
		$this->propertiesFile = PATH . $this->propertiesFile;
	}
	
	public function getPropertiesFile() {
		return $this->propertiesFile; 
	}
	
	public function toString() {
		return "printer type[" . $this->type . "]";
	}
}

// $EPSON = new PrinterType("epson");
// $HP = new PrinterType("hp");
// $IBM = new PrinterType("ibm");

?>
