<?php 
class DepositProtocolListAccountRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,"2600"); //mti
		$this->SetValueForDataElement(24,"305"); //f_code for inquiry list account
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'f_code') $iKey = 24;
		elseif($sKey == 'priv') $iKey = 48;
		
		return $iKey;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		$this->SetValueForDataElement($keyIdx,$value);
	}
}

class DepositProtocolListAccountResponse extends CISO8583Parser
{
	public $dataElement = array();

	function __construct($isoStream)
	{
		parent::__construct($isoStream);
	}
	
	private function GetMappingValue($idx)
	{
		switch($idx){
			case 0: $sKey = 'mti';
					break;
			case 1: $sKey = 'bitmap';
					break;
			case 12: $sKey = 'dt';
					break;
			case 24: $sKey = 'f_code';
					break;
			case 39: $sKey = 'rc';
					break;
			case 48: $sKey = 'priv';
					break;
			case 72: $sKey = 'data';
					break;
		}
		return $sKey;
	}
	
	public function ExtractDataElement()
	{
		if($this->Parse())
		{
			$rDataElmt = $this->GetParsedDataElement();
			foreach($rDataElmt as $iKey => $value)
			{
				$sKey = $this->GetMappingValue($iKey);
				$this->dataElement[$sKey] = $value;
			}
		}
	}
}
?>