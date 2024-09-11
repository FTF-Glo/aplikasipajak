<?php

class PrinterCommand {
	var $ar;
	
	function PrinterCommand($filename) {
		// read properties file
		$content = file_get_contents($filename);

		// split the properties
		$arTemp = explode("\n", $content);
		$size = count($arTemp);

		// process properties
		for ($i = 0; $i < $size; $i++) {
			// get string
			$a = trim($arTemp[$i]);
			
			// get command and character
			$indexEqual = strpos($a, "=");
			$command = substr($a, 0, $indexEqual);
			$character = substr($a, $indexEqual + 1);
			
			// insert to array
			$this->ar[$command] = $this->applyCharacter($character);
		}
	}
	
	private function applyCharacter($s) {
		// result
		$result = "";
		
		// split the character
		$arChar = explode(" ", $s);

		// process character
		
		foreach ($arChar as $a) {
			if (substr($a, 0, 1) == "#") {
				if (strlen($a) == 1) {
					// parameter #, just insert
					$result .= $a;
				} else {
					// convert all #xx to that character
					$stX = substr($a, 1);
					$x = ($stX + 0);
					$result .= chr($x);
				}
			} else {
				// other, langsung masuk
				$result .= $a;
			}
		}
		
		// return
		return $result;
	}
	
	public function getArrayCommand() {
		return $this->ar;
	}
}

?>
