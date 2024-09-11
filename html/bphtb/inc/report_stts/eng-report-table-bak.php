<?php
require_once("eng-report.php");
Class ReportEngineTable extends ReportEngine{
	var $ReportTemplate;
	var $ReportHeaderValues;
	var $ReportBodyValues;
	var $ReportFooterValues;
	var $ReportRowPerPage;
	var $ReportRowCount;
	var $ReportPageCount;
	var $ReportRowInFirstPage;
	var $ReportObject;
	var $LastError;
	var $LastErrorMsg;
	var $currentDate;
	function ReportEngineTable($rt,$HValues,$BValues,$FValues)
    {
       if (!file_exists($rt)){
			$this->LastError=true;
			$this->LastErrorMsg="Template Not Found";
	   }
	   else{
			$this->ReportTemplate=$rt;
			$this->ReportObject=simplexml_load_file($this->ReportTemplate);
			$this->ReportEngine($rt);
			if($this->ReportObject===FALSE){
				$this->LastError=true;
				$this->LastErrorMsg="Error Parsing Template";
			}
			else{
				$this->ReportHeaderValues=$HValues;
				$this->ReportBodyValues=$BValues;
				$this->ReportFooterValues=$FValues;
				$this->ReportRowPerPage=intval($this->ReportObject["pagerow"]);
				$this->ReportRowInFirstPage=intval($this->ReportObject["firstpagerow"]);
				$this->ReportRowCount=count($this->ReportBodyValues);
				$this->LastError=false;
				if ($this->ReportRowCount <= $this->ReportRowInFirstPage) {
					$this->ReportPageCount = 1;
				} else {
					$this->ReportPageCount=ceil($this->ReportRowCount/$this->ReportRowPerPage);
				}
			}
	   }
    }

	function ApplyTemplateValue($txttemplate, $values) {
		$strxml=$txttemplate;
		foreach($values as $key => $value){
			$strxml = str_replace("#".$key."#", $value, $strxml);
		}
		return $strxml;
	}

	function ApplyTemplateValueHTML($txttemplate, $values) {
		$strxml=$txttemplate;
		foreach($values as $key => $value){
			$strxml = str_replace("#".$key."#", htmlspecialchars($value), $strxml);
		}
		return $strxml;
	}

	function procesLine2Printer($lines,$values){
		$strbuffer='';
		foreach ($lines as $line){
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

					$itemtext=$this->ApplyTemplateValue($itemtext,$values);

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
		return $strbuffer;
	}

	function Print2Printer($printerName,$startPage=1,$endPage=1){
		if($this->LastError){
			return FALSE;
		}
		else{
      $handle=printer_open($printerName);
			if($handle==FALSE){
				return FALSE;
			}
			else{
        printer_close($handle);
				$strbuffer="";
				$ctime=time();
				$this->currentDate=date('d/m/Y H:i:s',$ctime);
				$spage=$startPage;
			$epage=$endPage;
			if($startPage<1)
				$spage=1;
			elseif($startPage>$this->ReportPageCount)
				$spage=$this->ReportPageCount;

			if($endPage<1)
				$epage=1;
			elseif($endPage>$this->ReportPageCount)
				$epage=$this->ReportPageCount;
			for($page=$spage;$page<=$epage;$page++){
          $handle=printer_open($printerName);
					printer_set_option($handle, PRINTER_MODE, "RAW");
					$strbuffer=(chr(27)."@"); //reset
					$strbuffer.=(chr(27)."P"); //10cpi, init with (comm)
					$strbuffer.=(chr(27).chr(25).chr(1)); //select paper feeder
					$strbuffer.=(chr(27)."0"); //1/8 inch line space
					//if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
					//	$strbuffer.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 86 line
					//}
					$strbuffer.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
					$strbuffer.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line

					//proses header
					$this->ReportHeaderValues['PAGENO']=$page;
					$this->ReportHeaderValues['PAGECOUNT']=$this->ReportPageCount;
					$this->ReportHeaderValues['CURRENTDATE']=$this->currentDate;
					if($this->ReportObject->header){
						$strbuffer.=$this->procesLine2Printer($this->ReportObject->header->line,$this->ReportHeaderValues);
					}

					//proses body
					$startRec=(($page-1)*$this->ReportRowPerPage);
					$endRec=(($page)*$this->ReportRowPerPage)-1;
					if($page==$this->ReportPageCount){
						$endRec=$this->ReportRowCount-1;
					}

					for($i=$startRec;$i<=$endRec;$i++){
						$this->ReportBodyValues[$i]['RECNO']=$i+1;
						$strbuffer.=$this->procesLine2Printer($this->ReportObject->body->line,$this->ReportBodyValues[$i]);
					}


					//proses footer
					$this->ReportFooterValues['PAGENO']=$page;
					$this->ReportFooterValues['PAGECOUNT']=$this->ReportPageCount;
					$this->ReportFooterValues['CURRENTDATE']=$this->currentDate;
					if($this->ReportObject->footer){
						$strbuffer.=$this->procesLine2Printer($this->ReportObject->footer->line,$this->ReportFooterValues);
					}
          $strbuffer.=(chr(12).str_repeat(chr(13),1)); //FF
          $strbuffer.=(chr(27)."@"); //reset
          printer_write($handle,$strbuffer);
				  printer_close($handle);
				}

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

	function procesLineHTMLPreview($lines,$values){
		foreach ($lines as $line){
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

					$itemtext=$this->ApplyTemplateValueApplyTemplateValueHTML($itemtext,$values);

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
	}

	function PrintHTMLPreview($startPage=1,$endPage=1){
		if($this->LastError){
			return FALSE;
		}
		else{
			$ctime=time();
			$this->currentDate=date('d/m/Y H:i:s',$ctime);
			$spage=$startPage;
			$epage=$endPage;
			if($startPage<1)
				$spage=1;
			elseif($startPage>$this->ReportPageCount)
				$spage=$this->ReportPageCount;

			if($endPage<1)
				$epage=1;
			elseif($endPage>$this->ReportPageCount)
				$epage=$this->ReportPageCount;
			for($page=$spage;$page<=$epage;$page++){
				echo "<pre>";
				//proses header
				$this->ReportHeaderValues['PAGENO']=$page;
				$this->ReportHeaderValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportHeaderValues['CURRENTDATE']=$this->currentDate;
				if($this->ReportObject->header){
					$this->procesLineHTMLPreview($this->ReportObject->header->line,$this->ReportHeaderValues);
				}

				//proses body
				if ($page == 1) {
						$startRec = 0;
						$endRec = $startRec + ($this->ReportRowInFirstPage - 1); 
					} else {
						$startRec = $this->ReportRowInFirstPage + (($page-2)*$this->ReportRowPerPage);
						$endRec = $startRec + ($this->ReportRowPerPage - 1);
				}
				if($page==$this->ReportPageCount){
					$endRec=$this->ReportRowCount-1;
				}

				for($i=$startRec;$i<=$endRec;$i++){
					$this->ReportBodyValues[$i]['RECNO']=$i+1;
					$this->procesLineHTMLPreview($this->ReportObject->body->line,$this->ReportBodyValues[$i]);
				}


				//proses footer
				$this->ReportFooterValues['PAGENO']=$page;
				$this->ReportFooterValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportFooterValues['CURRENTDATE']=$this->currentDate;
				if($this->ReportObject->footer){
					$this->procesLineHTMLPreview($this->ReportObject->footer->line,$this->ReportFooterValues);
				}
				echo "</pre>";
			}
			return TRUE;
		}
	}


	function procesLineHTML($lines, $values,&$strHTML){
		$strHTML='';
		foreach ($lines as $line){
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

					$itemtext = $this->ApplyTemplateValueHTML($itemtext,$values);

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
	}

	function PrintHTML(&$strHTML, $startPage=1, $endPage=1){
		$strHTML='';
		if($this->LastError){
			return FALSE;
		}
		else{
			$ctime=time();
			$this->currentDate=date('d/m/Y H:i:s',$ctime);
			$spage=$startPage;
			$epage=$endPage;
			if($startPage<1)
				$spage=1;
			elseif($startPage>$this->ReportPageCount)
				$spage=$this->ReportPageCount;

			if($endPage<1)
				$epage=1;
			elseif($endPage>$this->ReportPageCount)
				$epage=$this->ReportPageCount;
			for($page=$spage;$page<=$epage;$page++){
				$strHTML.= "<pre>";
				//proses header
				/*
				$this->ReportHeaderValues['PAGENO']=$page;
				$this->ReportHeaderValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportHeaderValues['CURRENTDATE']=$this->currentDate;
				*/
				if($this->ReportObject->header){
					$this->procesLineHTML($this->ReportObject->header->line, $this->ReportHeaderValues, $html);
					$strHTML.=$html;
				}

				//proses body
				if ($page == 1) {
						$startRec = 0;
						$endRec = $startRec + ($this->ReportRowInFirstPage - 1); 
					} else {
						$startRec = $this->ReportRowInFirstPage + (($page-2)*$this->ReportRowPerPage);
						$endRec = $startRec + ($this->ReportRowPerPage - 1);
				}
				if($page==$this->ReportPageCount){
					$endRec=$this->ReportRowCount-1;
				}

				for($i=$startRec;$i<=$endRec;$i++){
					$this->ReportBodyValues[$i]['RECNO']=$i+1;
					$this->procesLineHTML($this->ReportObject->body->line,$this->ReportBodyValues[$i],$html);
					$strHTML.=$html;
				}


				//proses footer

				$this->ReportFooterValues['PAGENO']=$page;
				$this->ReportFooterValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportFooterValues['CURRENTDATE']=$this->currentDate;
				if($this->ReportObject->footer){
					$this->procesLineHTML($this->ReportObject->footer->line,$this->ReportFooterValues,$html);
					$strHTML.=$html;

				}
				$strHTML.= "</pre>";
			}
			return TRUE;
		}
	}

	function procesLine2File($lines,$values){
		$strbuffer='';
		foreach ($lines as $line){
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

					$itemtext=$this->ApplyTemplateValue($itemtext,$values);

					if(intval($item["center"])>0){
						$tlength=strlen($itemtext);
						if($tlength>=intval($item["center"])){
							$strtext=$itemtext;
						}else{
							$lpad=floor((intval($item["center"])-$tlength)/2)+$tlength;
							$rpad=ceil((intval($item["center"])-$tlength)/2)+$tlength;
							$strtext=str_pad(str_pad($itemtext,$lpad," ",STR_PAD_LEFT),$rpad," ",STR_PAD_RIGHT);
							
						}
					// }
					// else if(intval($item["repeat"])>1){
						// $itemtext=str_repeat($item["text"],intval($item["repeat"]));
					// }
					// else{
						// $itemtext=$item["text"];
					// }
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
					// echo "waduh: ".$strtext."<br />\n";
					$strbuffer.=($strtext);
					if(!$item["underline"]){
						$strbuffer.=(chr(27)."-"); //underline off
					}
				}
			}
			$strbuffer.=(chr(13).chr(10));
		}
		return $strbuffer;
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
				$ctime=time();
				$this->currentDate=date('d/m/Y H:i:s',$ctime);
				$strbuffer="";
				$spage=$startPage;
				$epage=$endPage;
				if($startPage<1)
					$spage=1;
				elseif($startPage>$this->ReportPageCount)
					$spage=$this->ReportPageCount;
				if($endPage<1)
					$epage=1;
				elseif($endPage>$this->ReportPageCount)
					$epage=$this->ReportPageCount;
				for($page=$spage;$page<=$epage;$page++){
					$strbuffer.=(chr(27)."@"); //reset
					$strbuffer.=(chr(27).chr(25).chr(1)); //select paper feeder
					$strbuffer.=(chr(27)."0"); //1/8 inch line space
					if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
						$strbuffer.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 22 line
					}
					$strbuffer.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
					$strbuffer.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line

					//proses header
					/*
					$this->ReportHeaderValues['PAGENO']=$page;
					$this->ReportHeaderValues['PAGECOUNT']=$this->ReportPageCount;
					$this->ReportHeaderValues['CURRENTDATE']=$this->currentDate;
					*/
					if($this->ReportObject->header){
						$strbuffer.=$this->procesLine2File($this->ReportObject->header->line,$this->ReportHeaderValues);
					}

					//proses body
					if ($page == 1) {
						$startRec = 0;
						$endRec = $startRec + ($this->ReportRowInFirstPage - 1); 
					} else {
						$startRec = $this->ReportRowInFirstPage + (($page-2)*$this->ReportRowPerPage);
						$endRec = $startRec + ($this->ReportRowPerPage - 1);
				 }
					if($page==$this->ReportPageCount){
						$endRec=$this->ReportRowCount-1;
					}

					for($i=$startRec;$i<=$endRec;$i++){
						$this->ReportBodyValues[$i]['RECNO']=$i+1;
						$strbuffer.=$this->procesLine2File($this->ReportObject->body->line,$this->ReportBodyValues[$i]);
                                                
					}


					//proses footer
					$this->ReportFooterValues['PAGENO']=$page;
					$this->ReportFooterValues['PAGECOUNT']=$this->ReportPageCount;
					$this->ReportFooterValues['CURRENTDATE']=$this->currentDate;
					if($this->ReportObject->footer){
						$strbuffer.=$this->procesLine2File($this->ReportObject->footer->line,$this->ReportFooterValues);
					}

					$strbuffer.=(chr(12).str_repeat(chr(13),1)); //FF
					$strbuffer.=(chr(27)."@"); //reset
				}
				fwrite($handle,$strbuffer);
				fclose($handle);
				
				return TRUE;
			}
		}
	}

	function Print2TXT(&$strTXT, $startPage=1,$endPage=1,$FFatEnd=1){
		$strTXT = "";
		if($this->LastError){
			return FALSE;
		}
		else{
			$ctime=time();
			$this->currentDate=date('d/m/Y H:i:s',$ctime);
			$spage=$startPage;
			$epage=$endPage;

			if($startPage<1)
				$spage=1;
			elseif($startPage>$this->ReportPageCount)
				$spage=$this->ReportPageCount;

			if($endPage<1)
				$epage=1;
			elseif($endPage>$this->ReportPageCount)
				$epage=$this->ReportPageCount;

			for($page=$spage;$page<=$epage;$page++){
				$strTXT.=(chr(27)."@"); //reset
				$strTXT.=(chr(27).chr(25).chr(1)); //select paper feeder
				$strTXT.=(chr(27)."0"); //1/8 inch line space
				if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
					$strTXT.=(chr(27)."C".chr(intval($this->ReportObject["length"]))); //page length 22 line
				}
				$strTXT.=(chr(27)."l".chr(intval($this->ReportObject["lmargin"]))); // left margin line
				$strTXT.=(chr(27)."Q".chr(intval($this->ReportObject["rmargin"]))); // left margin line

				//proses header
				/*
				$this->ReportHeaderValues['PAGENO']=$page;
				$this->ReportHeaderValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportHeaderValues['CURRENTDATE']=$this->currentDate;
				*/
				if($this->ReportObject->header){
					$strTXT.=$this->procesLine2File($this->ReportObject->header->line,$this->ReportHeaderValues);
				}

				//proses body
				if ($page == 1) {
					$startRec = 0;
					$endRec = $startRec + ($this->ReportRowInFirstPage - 1); 
				} else {
					$startRec = $this->ReportRowInFirstPage + (($page-2)*$this->ReportRowPerPage);
					$endRec = $startRec + ($this->ReportRowPerPage - 1);
				}
				if($page==$this->ReportPageCount){
					$endRec=$this->ReportRowCount-1;
				}

				for($i=$startRec;$i<=$endRec;$i++){
					$this->ReportBodyValues[$i]['RECNO']=$i+1;
					$strTXT.=$this->procesLine2File($this->ReportObject->body->line,$this->ReportBodyValues[$i]);
											
				}


				//proses footer
				$this->ReportFooterValues['PAGENO']=$page;
				$this->ReportFooterValues['PAGECOUNT']=$this->ReportPageCount;
				$this->ReportFooterValues['CURRENTDATE']=$this->currentDate;
				if($this->ReportObject->footer){
					$strTXT.=$this->procesLine2File($this->ReportObject->footer->line,$this->ReportFooterValues);
				}

				if($FFatEnd==1)$strTXT.=(chr(12).str_repeat(chr(13),1)); //FF
				$strTXT.=(chr(27)."@"); //reset
			}
			return TRUE;
		}
	}
	
	function Print2CUPS($pritnterqueue,$startPage=1,$endPage=1){
                if($this->LastError){
			return FALSE;
		}
		else{                        
			$tmpfname = tempnam("/tmp", "ONPAYS");
			@chmod($tmpfname,0777);
			if($this->Print2File($tmpfname,$startPage,$endPage)==TRUE){
                                $command="lpr -P ".$pritnterqueue." -l  ".$tmpfname;
                                @exec($command,$out);
                                @unlink($tmpfname);
				//echo " SUCC ".$tmpfname;
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
			return TRUE;
		}
	}
}
?>