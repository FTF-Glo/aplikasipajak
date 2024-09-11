<?php
//SINGLE LINE
define("VERTICAL_LINE",chr(179));
define("HORIZONTAL_LINE",chr(196));
define("LEFT_TOP_LINE",chr(218));
define("RIGHT_TOP_LINE",chr(191));
define("LEFT_BOTTOM_LINE",chr(192));
define("RIGHT_BOTTOM_LINE",chr(217));
define("LEFT_SIDE_LINE",chr(195));
define("RIGHT_SIDE_LINE",chr(180));
define("TOP_SIDE_LINE",chr(194));
define("BOTTOM_SIDE_LINE",chr(193));
define("CENTER_LINE",chr(197));

//DOUBLE LINE
define("VERTICAL_DLINE",chr(186));
define("HORIZONTAL_DLINE",chr(205));
define("LEFT_TOP_DLINE",chr(201));
define("RIGHT_TOP_DLINE",chr(187));
define("LEFT_BOTTOM_DLINE",chr(200));
define("RIGHT_BOTTOM_DLINE",chr(188));
define("LEFT_SIDE_DLINE",chr(204));
define("RIGHT_SIDE_DLINE",chr(185));
define("TOP_SIDE_DLINE",chr(203));
define("BOTTOM_SIDE_DLINE",chr(202));
define("CENTER_DLINE",chr(206));

//HORIZONTAL DOUBLE LINE, VERTICAL SINGLE LINE
define("LEFT_LINE_TOP_DLINE",chr(213));
define("RIGHT_LINE_TOP_DLINE",chr(184));
define("LEFT_LINE_BOTTOM_DLINE",chr(212));
define("RIGHT_LINE_BOTTOM_DLINE",chr(190));

define("LEFT_LINE_HORIZONTAL_DLINE",chr(198));
define("RIGHT_LINE_HORIZONTAL_DLINE",chr(181));
define("TOP_DLINE_VERTICAL_LINE",chr(209));
define("BOTTOM_DLINE_VERTICAL_LINE",chr(207));
define("CENTER_VERTICAL_LINE_HORIZONTAL_DLINE",chr(216));

//HORIZONTAL SINGLE LINE, VERTICAL DOUBLE LINE
define("LEFT_DLINE_TOP_LINE",chr(214));
define("RIGHT_DLINE_TOP_LINE",chr(183));
define("LEFT_DLINE_BOTTOM_LINE",chr(211));
define("RIGHT_DLINE_BOTTOM_LINE",chr(189));

define("LEFT_DLINE_HORIZONTAL_LINE",chr(199));
define("RIGHT_DLINE_HORIZONTAL_LINE",chr(182));
define("TOP_LINE_VERTICAL_DLINE",chr(210));
define("BOTTOM_LINE_VERTICAL_DLINE",chr(208));
define("CENTER_VERTICAL_DLINE_HORIZONTAL_LINE",chr(215));

Class ReportEngine{
	var $ReportTemplate;
	var $ReportObject;
	var $LastError;
	var $LastErrorMsg;
	function ReportEngine($rt)
    {
		if (!file_exists($rt)){
			$this->LastError=true;
			$this->LastErrorMsg="Template Not Found";
		}
		else{
			$this->ReportTemplate=$rt;
			$this->ReportObject=simplexml_load_file($this->ReportTemplate);
			if($this->ReportObject===FALSE){
				$this->LastError=true;
				$this->LastErrorMsg="Error Parsing Template";
			}
		}
    }

	function ApplyTemplateValue($values){
		$strxml=file_get_contents($this->ReportTemplate);
		//echo "isi strxml: ";
		//var_dump($strxml);
		//echo "<br /><br />";
		foreach($values as $key => $value){

			$strxml=str_replace("#".$key."#",htmlspecialchars($value),$strxml);
		}
		$this->ReportObject=simplexml_load_string($strxml);
		// return $strxml;
			// // foreach($values as $key => $value) {
				// // $tes = str_replace("#".$key."#", htmlspecialchars($value), $itemtext[0]);
				// // echo $tes;
				// // return $tes;
			// // }
		// } else {
			// // echo "<br />";
			// foreach($values as $key => $value) {
				// $tes = str_replace("#".$key."#", htmlspecialchars($value), $itemtext);
				// // echo "nih: ".$tes.":".$itemtext."<br />";
				// return $tes;
			// }
			// // return $itemtext;
		// }
	}

	function ApplyTemplateValueCoba($values){
		$strxml=file_get_contents($this->ReportTemplate);
		// var_dump($itemtext);
		// echo "<br />";
		// var_dump($strxml);
		// echo "<br />";
		// var_dump($values);
		// echo "<br /><br />";
		if (is_string($itemtext)) {
			return $itemtext;
			echo "wkwkwk masuk jg<br /><br />";
			foreach($itemtext as $key => $value){

				$strxml=str_replace("#".$key."#",htmlspecialchars($value),$strxml);
			}
			$this->ReportObject=simplexml_load_string($strxml);
			return $strxml;
			// foreach($values as $key => $value) {
				// $tes = str_replace("#".$key."#", htmlspecialchars($value), $itemtext[0]);
				// echo $tes;
				// return $tes;
			// }
		} else {
			// echo "<br />";
			foreach($values as $key => $value) {
				$tes = str_replace("#".$key."#", htmlspecialchars($value), $itemtext);
				// echo "nih: ".$tes.":".$itemtext."<br />";
				return $tes;
			}
			// return $itemtext;
		}
	}
	
	function Print2Printer($printerName){
		if($this->LastError){
			return FALSE;
		}
		else{
			$handle=printer_open($printerName);
			if($handle==FALSE){
				return FALSE;
			}
			else{
				$strbuffer="";
				printer_set_option($handle, PRINTER_MODE, "RAW");
				$strbuffer.=(chr(27)."@"); //reset
				$strbuffer.=(chr(27)."P"); //10cpi, init with (comm)
				$strbuffer.=(chr(27).chr(25).chr(1)); //select paper feeder
				$strbuffer.=(chr(27)."0"); //1/8 inch line space
				if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
					$strbuffer.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 22 line
				}
				$strbuffer.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
				$strbuffer.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line
				foreach ($this->ReportObject->line as $line){
					foreach ($line->item as $item){
						if($item["text"]!=""){
							if($this->ReportObject["draftmode"]=="true"){
								$strbuffer.=(chr(27)."x".chr(0));
							}
							if((intval($item["font"])>=0 && intval($item["font"])<=11) || (intval($item["font"])>=30 && intval($item["font"])<=31)){
								$strbuffer.=(chr(27)."k".chr(intval($item["font"])));
							}
							if($item["bold"]=="true"){
								$strbuffer.=(chr(27)."E"); //bold
							}
							else{
								$strbuffer.=(chr(27)."F"); //no bold
							}
							if($item["italic"]=="true"){
								$strbuffer.=(chr(27)."4"); //italic
							}
							else{
								$strbuffer.=(chr(27)."5"); //no italic
							}
							if($item["underline"]=="true"){
								$strbuffer.=(chr(27)."-"); //underline on
							}

							if(intval($item["size"])==10){
								$strbuffer.=(chr(27)."P"); //10cpi
							}
							else if(intval($item["size"])==12){
								$strbuffer.=(chr(27)."M"); //12cpi
							}
							else if(intval($item["size"])==20){
								$strbuffer.=(chr(27)."g"); //20cpi
							}
							else{
								$strbuffer.=(chr(27)."P"); //10cpi
							}
							$itemtext="";
							if(intval($item["repeat"])>1){
								$itemtext=str_repeat($item["text"],intval($item["repeat"]));
							}
							else{
								$itemtext=$item["text"];
							}

							if(intval($item["center"])>0){
								$tlength=strlen($itemtext);
								if($tlength>=intval($item["center"])){
									$strtext=$itemtext;
								}else{
									$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
									$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
									$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
								}
							}
							else if(intval($item["lpad"])<=0 && intval($item["rpad"])<=0){
								$strtext=$itemtext;
							}
							else if(intval($item["lpad"])>0 && intval($item["rpad"])<=0){
								$strtext=str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT);
							}
							else if(intval($item["lpad"])<=0 && intval($item["rpad"])>0){
								$strtext=str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT);
							}
							else if(intval($item["lpad"])<intval($item["rpad"])){
								$strtext=str_pad(str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT),intval($item["rpad"])," ",STR_PAD_RIGHT);
							}
							else{
								$strtext=str_pad(str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT),intval($item["lpad"])," ",STR_PAD_LEFT);
							}
							$strbuffer.=($strtext);
							if(!$item["underline"]){
								$strbuffer.=(chr(27)."-"); //underline off
							}
						}
					}
					$strbuffer.=(chr(13).chr(10));

				}
				$strbuffer.=(chr(12).str_repeat(chr(13),1)); //FF
				$strbuffer.=(chr(27)."@"); //reset
				printer_write($handle,$strbuffer);
				printer_close($handle);
				return TRUE;
			}
		}
	}

	function getFontSizeInPoint($i){
		$fontsize="";
		switch($i){
			case 10:
				$fontsize="12pt";
				break;
			case 12:
				$fontsize="10pt";
				break;
			case 20:
				$fontsize="8pt";
				break;
			default:
				$fontsize="12pt";
		}
		return $fontsize;
	}

	function getFontFace($i){
		$fonface="";
		switch($i){
			case 0:
				$fonface="Roman";
				break;
			case 1:
				$fonface="Sans serif";
				break;
			case 2:
				$fonface="Courier";
				break;
			case 3:
				$fonface="Prestige";
				break;
			case 4:
				$fonface="Script";
				break;
			case 5:
				$fonface="OCR-B";
				break;
			case 6:
				$fonface="OCR-A";
				break;
			case 7:
				$fonface="Orator";
				break;
			case 8:
				$fonface="Orator-S";
				break;
			case 9:
				$fonface="Script-C";
				break;
			case 10:
				$fonface="Roman-T";
				break;
			case 11:
				$fonface="Sans serif-H";
				break;
			case 30:
				$fonface="SV Busaba";
				break;
			case 31:
				$fonface="SV Jittra";
				break;
			default:
				$fonface="Roman";
		}
		return $fontface;
	}

	function PrintHTML(&$strHTML,$startPage=1,$endPage=1){
		$strHTML="";
		if($this->LastError){
			return FALSE;
		}
		else{

			$strHTML.= "<pre>";
			foreach ($this->ReportObject->line as $line){
				foreach ($line->item as $item){
					if($item["text"]!=""){
						$strHTML.= "<span style=\"";
						if($this->ReportObject["draftmode"]=="true"){
							$strHTML.= "font-face:Arial;";
						}
						else{
							$strHTML.= "font-face:".$this->getFontFace(intval($item["font"])).";";
						}
						$strHTML.= $item->attributes["bold"];
						if($item["bold"]=="true"){
							$strHTML.= "font-weight:bold;";
						}
						else{
							$strHTML.= "font-weight:normal;";
						}
						if($item["italic"]=="true"){
							$strHTML.= "font-style:italic;";
						}
						else{
							$strHTML.= "font-style:normal;";
						}
						if($item["underline"]=="true"){
							$strHTML.= "text-decoration:underline;";
						}
						else{
							$strHTML.= "text-decoration:none;";
						}

						$strHTML.= "font-size:".$this->getFontSizeInPoint(intval($item["size"])).";\">";
						$itemtext="";
						if(intval($item["repeat"])>1){
							$itemtext=str_repeat($item["text"],intval($item["repeat"]));
						}
						else{
							$itemtext=$item["text"];
						}

						if(intval($item["center"])>0){
							$tlength=strlen($itemtext);
							if($tlength>=intval($item["center"])){
								$strtext=$itemtext;
							}else{
								$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
								$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
								$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
							}
						}
						else if(intval($item["lpad"])<=0 && intval($item["rpad"])<=0){
							$strtext=$itemtext;
						}
						else if(intval($item["lpad"])>0 && intval($item["rpad"])<=0){
							$strtext=str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						else if(intval($item["lpad"])<=0 && intval($item["rpad"])>0){
							$strtext=str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else if(intval($item["lpad"])<intval($item["rpad"])){
							$strtext=str_pad(str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT),intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else{
							$strtext=str_pad(str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT),intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						$strHTML.= $strtext;
						$strHTML.= "</span>";
					}
				}
				$strHTML.= chr(13).chr(10);
			}
			$strHTML.= "</pre>";
			return TRUE;
		}
	}

	function PrintHTMLPreview($startPage=1,$endPage=1){
		if($this->LastError){
			return FALSE;
		}
		else{

			echo "<pre>";
			foreach ($this->ReportObject->line as $line){
				foreach ($line->item as $item){
					if($item["text"]!=""){
						echo "<span style=\"";
						if($this->ReportObject["draftmode"]=="true"){
							echo "font-face:Arial;";
						}
						else{
							echo "font-face:".$this->getFontFace(intval($item["font"])).";";
						}
						echo $item->attributes["bold"];
						if($item["bold"]=="true"){
							echo "font-weight:bold;";
						}
						else{
							echo "font-weight:normal;";
						}
						if($item["italic"]=="true"){
							echo "font-style:italic;";
						}
						else{
							echo "font-style:normal;";
						}
						if($item["underline"]=="true"){
							echo "text-decoration:underline;";
						}
						else{
							echo "text-decoration:none;";
						}

						echo "font-size:".$this->getFontSizeInPoint(intval($item["size"])).";\">";
						$itemtext="";
						if(intval($item["repeat"])>1){
							$itemtext=str_repeat($item["text"],intval($item["repeat"]));
						}
						else{
							$itemtext=$item["text"];
						}

						if(intval($item["center"])>0){
							$tlength=strlen($itemtext);
							if($tlength>=intval($item["center"])){
								$strtext=$itemtext;
							}else{
								$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
								$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
								$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
							}
						}
						else if(intval($item["lpad"])<=0 && intval($item["rpad"])<=0){
							$strtext=$itemtext;
						}
						else if(intval($item["lpad"])>0 && intval($item["rpad"])<=0){
							$strtext=str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						else if(intval($item["lpad"])<=0 && intval($item["rpad"])>0){
							$strtext=str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else if(intval($item["lpad"])<intval($item["rpad"])){
							$strtext=str_pad(str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT),intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else{
							$strtext=str_pad(str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT),intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						echo $strtext;
						echo "</span>";
					}
				}
				echo chr(13).chr(10);
			}
			echo "</pre>";
			return TRUE;
		}
	}

	function Print2File($PathFile,$startPage=1,$endPage=1,$FFatEnd=1){
		if($this->LastError){
			return FALSE;
		}
		else{
			$handle=fopen($PathFile,"w");
			if($handle==FALSE){
				return FALSE;
			}
			else{
				$strbuffer="";
				$strbuffer.=(chr(27)."@"); //reset
				$strbuffer.=(chr(27)."P"); //10cpi, init with (comm)
				$strbuffer.=(chr(27).chr(25).chr(1)); //select paper feeder
				$strbuffer.=(chr(27)."0"); //1/8 inch line space
				if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
					$strbuffer.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 22 line
				}
				$strbuffer.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
				$strbuffer.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line
				foreach ($this->ReportObject->line as $line){
					foreach ($line->item as $item){
						if($item["text"]!=""){
							if($this->ReportObject["draftmode"]=="true"){
								$strbuffer.=(chr(27)."x".chr(0));
							}
							if((intval($item["font"])>=0 && intval($item["font"])<=11) || (intval($item["font"])>=30 && intval($item["font"])<=31)){
								$strbuffer.=(chr(27)."k".chr(intval($item["font"])));
							}
							if($item["bold"]=="true"){
								$strbuffer.=(chr(27)."E"); //bold
							}
							else{
								$strbuffer.=(chr(27)."F"); //no bold
							}
							if($item["italic"]=="true"){
								$strbuffer.=(chr(27)."4"); //italic
							}
							else{
								$strbuffer.=(chr(27)."5"); //no italic
							}
							if($item["underline"]=="true"){
								$strbuffer.=(chr(27)."-"); //underline on
							}

							if(intval($item["size"])==10){
								$strbuffer.=(chr(27)."P"); //10cpi
							}
							else if(intval($item["size"])==12){
								$strbuffer.=(chr(27)."M"); //12cpi
							}
							else if(intval($item["size"])==20){
								$strbuffer.=(chr(27)."g"); //20cpi
							}
							else{
								$strbuffer.=(chr(27)."P"); //10cpi
							}
							$itemtext="";
							if(intval($item["repeat"])>1){
								$itemtext=str_repeat($item["text"],intval($item["repeat"]));
							}
							else{
								$itemtext=$item["text"];
							}
							if(intval($item["center"])>0){
								$tlength=strlen($itemtext);
								if($tlength>=intval($item["center"])){
									$strtext=$itemtext;
								}else{
									$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
									$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
									$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
								}
							}
							else if(intval($item["repeat"])>1){
								$itemtext=str_repeat($item["text"],intval($item["repeat"]));
							}
							else{
								$itemtext=$item["text"];
							}
							 if(intval($item["lpad"])<=0 && intval($item["rpad"])<=0){
								$strtext=$itemtext;
							}
							else if(intval($item["lpad"])>0 && intval($item["rpad"])<=0){
								$strtext=str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT);
							}
							else if(intval($item["lpad"])<=0 && intval($item["rpad"])>0){
								$strtext=str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT);
							}
							else if(intval($item["lpad"])<intval($item["rpad"])){
								$strtext=str_pad(str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT),intval($item["rpad"])," ",STR_PAD_RIGHT);
							}
							else{
								$strtext=str_pad(str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT),intval($item["lpad"])," ",STR_PAD_LEFT);
							}
							$strbuffer.=($strtext);
							if(!$item["underline"]){
								$strbuffer.=(chr(27)."-"); //underline off
							}
						}
					}
					$strbuffer.=(chr(13).chr(10));

				}
				if($FFatEnd==1)$strbuffer.=(chr(12).str_repeat(chr(13),1)); //FF
				$strbuffer.=(chr(27)."@"); //reset
				fwrite($handle,$strbuffer);
				fclose($handle);
				return TRUE;
			}
		}
	}

	function Print2TXT(&$txt,$startPage=1,$endPage=1,$FFatEnd=1){
        if($this->LastError){
			return FALSE;
		}
		else{
			$strbuffer="";
			$strbuffer.=(chr(27)."@"); //reset
			$strbuffer.=(chr(27)."P"); //10cpi, init with (comm)
			$strbuffer.=(chr(27).chr(25).chr(1)); //select paper feeder
			$strbuffer.=(chr(27)."0"); //1/8 inch line space
			if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
				$strbuffer.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 22 line
			}
			//echo "jumlah line :".$this->ReportObject["length"]."<br /><br />\n";
			//echo "jumlah line :".count($this->ReportObject->line)."<br /><br />\n";
			$strbuffer.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
			$strbuffer.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line
			$i=0;
			foreach ($this->ReportObject->line as $line){
				//echo "no: ".($i++);
				//var_dump($line);
				//echo "<br /><br />\n";
				foreach ($line->item as $item){
					if($item["text"]!=""){
						if($this->ReportObject["draftmode"]=="true"){
							$strbuffer.=(chr(27)."x".chr(0));
						}
						if((intval($item["font"])>=0 && intval($item["font"])<=11) || (intval($item["font"])>=30 && intval($item["font"])<=31)){
							$strbuffer.=(chr(27)."k".chr(intval($item["font"])));
						}
						if($item["bold"]=="true"){
							$strbuffer.=(chr(27)."E"); //bold
						}
						else{
							$strbuffer.=(chr(27)."F"); //no bold
						}
						if($item["italic"]=="true"){
							$strbuffer.=(chr(27)."4"); //italic
						}
						else{
							$strbuffer.=(chr(27)."5"); //no italic
						}
						if($item["underline"]=="true"){
							$strbuffer.=(chr(27)."-"); //underline on
						}

						if(intval($item["size"])==10){
							$strbuffer.=(chr(27)."P"); //10cpi
						}
						else if(intval($item["size"])==12){
							$strbuffer.=(chr(27)."M"); //12cpi
						}
						else if(intval($item["size"])==20){
							$strbuffer.=(chr(27)."g"); //20cpi
						}
						else{
							$strbuffer.=(chr(27)."P"); //10cpi
						}
						$itemtext="";
						if(intval($item["repeat"])>1){
							$itemtext=str_repeat($item["text"],intval($item["repeat"]));
						}
						else{
							$itemtext=$item["text"];
						}
						if(intval($item["center"])>0){
							$tlength=strlen($itemtext);
							if($tlength>=intval($item["center"])){
								$strtext=$itemtext;
							}else{
								$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
								$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
								$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
							}
						}
						else if(intval($item["repeat"])>1){
							$itemtext=str_repeat($item["text"],intval($item["repeat"]));
						}
						else{
							$itemtext=$item["text"];
						}
						 if(intval($item["lpad"])<=0 && intval($item["rpad"])<=0){
							$strtext=$itemtext;
						}
						else if(intval($item["lpad"])>0 && intval($item["rpad"])<=0){
							$strtext=str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						else if(intval($item["lpad"])<=0 && intval($item["rpad"])>0){
							$strtext=str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else if(intval($item["lpad"])<intval($item["rpad"])){
							$strtext=str_pad(str_pad($itemtext,intval($item["lpad"])," ",STR_PAD_LEFT),intval($item["rpad"])," ",STR_PAD_RIGHT);
						}
						else{
							$strtext=str_pad(str_pad($itemtext,intval($item["rpad"])," ",STR_PAD_RIGHT),intval($item["lpad"])," ",STR_PAD_LEFT);
						}
						$strbuffer.=($strtext);
						if(!$item["underline"]){
							$strbuffer.=(chr(27)."-"); //underline off
						}
					}
				}
				$strbuffer.=(chr(13).chr(10));
			}
			if($FFatEnd==1) $strbuffer.=(chr(12).str_repeat(chr(13),1)); //FF
			$strbuffer.=(chr(27)."@"); //reset
			$txt=$strbuffer;
			return TRUE;
		}
	}
	
	function Print2CUPS($pritnterqueue){
		if($this->LastError){
			return FALSE;
		}
		else{                        
			$tmpfname = tempnam("/tmp", "ONPAYS");
			@chmod($tmpfname,0777);
			if($this->Print2File($tmpfname)==TRUE){
				$command="lpr -P ".$pritnterqueue." -l  ".$tmpfname;
				@exec($command,$out);
				@unlink($tmpfname);	
				return TRUE;
			}else{
				@unlink($tmpfname);
				return FALSE;
			}
		}
	}

	function PrintObject(){
		if($this->LastError){
			return FALSE;
		}
		else{
			var_dump($this->ReportObject);
			return TRUE;
		}
	}
}
?>