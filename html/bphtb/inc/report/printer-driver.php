<?php

require_once("printer-command.php");
require_once("printer-type.php");

class PrinterDriver {
	var $ar;
	
	function PrinterDriver($file) {
		$reader = new PrinterCommand($file);
		$this->ar = $reader->getArrayCommand();
		//print_r($this->ar);
		// var_dump($this->ar);
	}
	
	private function applyParameter($s, $parameter) {
		if ($parameter != null) {
			return str_replace("#", $parameter, $s);
		}
		return $s;
	}
	
	public function command($s, $parameter = null) {
		// var_dump($this->ar);
	
		// get character
		$character = $this->ar[$s];
		// echo "s = $s<br />\n";
		// echo "character = $character<br />\n";
		
		// apply parameter
		if ($parameter != null) {
			$character = $this->applyParameter($character, $parameter);
		}
		
		// return
		return $character;
	}
}

?>
