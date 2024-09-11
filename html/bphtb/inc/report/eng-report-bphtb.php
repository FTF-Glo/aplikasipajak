<?php
/* 
 *  Print SSPD - BPHTB
 *  Author By ardi@vsi.co.id
 *  06-12-2016
 */

Class ReportEngineBPHTB extends ReportEngine{
	
	/*ARD+- : overide*/
	function Print2TXT(&$txt,$startPage=1,$endPage=1,$FFatEnd=1){
        if($this->LastError){
			return FALSE;
		}
		else{
			$strbuffer="";
			$strbuffer.=$this->PrinterDriver->command("init"); // reset
			$strbuffer.=$this->PrinterDriver->command("cpi10"); // 10cpi, init with (comm)
			$strbuffer.=$this->PrinterDriver->command("controlPaper"); // select paper feeder
			if($this->ReportObject["length"]>0 and $this->ReportObject["length"]<=127){
				$strbuffer.=($this->PrinterDriver->command("setPageLength", chr(intval($this->ReportObject["length"])))); //page length 22 line
			}
			$strbuffer.=($this->PrinterDriver->command("setLeftMargin", chr(intval($this->ReportObject["lmargin"])))); // left margin line
			$strbuffer.=($this->PrinterDriver->command("setRightMargin", chr(intval($this->ReportObject["rmargin"])))); // left margin line
			//echo $strbuffer;exit;
			$i=0;
			foreach ($this->ReportObject->line as $line){
				
				#ARD+- : menambah konfig line type / line spacing
				if(isset($line['ltype']) && $line['ltype'] == 'perhitungan'){
					$strbuffer.=($this->PrinterDriver->command("perhitungan"));
				}else if(isset($line['ltype']) && $line['ltype'] == 'smallspace'){
					$strbuffer.=($this->PrinterDriver->command("smallspace"));
				}else{ 
					//default
					$strbuffer.=$this->PrinterDriver->command("lineSpacing1/8"); // 1/8 inch line space
				}
				
				foreach ($line->item as $item){
					if($item["text"]!=""){
						if($this->ReportObject["draftmode"]=="true"){
							$strbuffer.=($this->PrinterDriver->command("draftOn"));
						}
						if((intval($item["font"])>=0 && intval($item["font"])<=11) || (intval($item["font"])>=30 && intval($item["font"])<=31)){
							$strbuffer.=($this->PrinterDriver->command("setTypeface", chr(intval($item["font"]))));
						}
						if($item["bold"]=="true"){
							$strbuffer.=($this->PrinterDriver->command("boldOn")); //bold
						}
						else{
							$strbuffer.=($this->PrinterDriver->command("boldOff")); //no bold
						}
						if($item["italic"]=="true"){
							$strbuffer.=($this->PrinterDriver->command("italicOn")); //italic
						}
						else{
							$strbuffer.=($this->PrinterDriver->command("italicOff")); //no italic
						}
						if($item["underline"]=="true"){
							$strbuffer.=($this->PrinterDriver->command("underlineOn")); //underline on
						}

						if(intval($item["size"])==10){
							$strbuffer.=($this->PrinterDriver->command("cpi10")); //10cpi
						}
						else if(intval($item["size"])==12){
							$strbuffer.=($this->PrinterDriver->command("cpi12")); //12cpi
						}
						else if(intval($item["size"])==20){
							$strbuffer.=($this->PrinterDriver->command("cpi15")); //20cpi
						}
						else{
							$strbuffer.=($this->PrinterDriver->command("cpi10")); //10cpi
						}
						
						#ARD+- : menambah konfig item / character spacing
						if(isset($item['space']) && $item['space'] == 'characterSpace'){
							$strbuffer.=$this->PrinterDriver->command("characterSpace");
						}else{ 
							//default
							$strbuffer.=$this->PrinterDriver->command("characterSpaceStop");
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
						/*
						else if(intval($item["repeat"])>1){
							$itemtext=str_repeat($item["text"],intval($item["repeat"]));
						}
						else{
							$itemtext=$item["text"];
						}*/
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
							$strbuffer.=($this->PrinterDriver->command("underlineOff")); //underline off
						}
					}
				}
				$strbuffer.=(chr(13).chr(10));
			}
			if($FFatEnd==1){
				$strbuffer .= ($this->PrinterDriver->command("formFeed") . str_repeat(chr(13),1)); //FF
			}
			$strbuffer .= ($this->PrinterDriver->command("init")); //reset
			$txt=$strbuffer;
			return TRUE;
		}
	}
}
?>
