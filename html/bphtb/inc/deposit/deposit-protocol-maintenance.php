<?php 
class DepositProtocolMaintenanceRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2600); //mti
		$this->SetValueForDataElement(24,113); //f_code
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'f_code') $iKey = 24;
		
		return $iKey;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		$this->SetValueForDataElement($keyIdx,$value);
	}
}
class DepositProtocolMaintenanceResponse extends CISO8583Parser
{
	public $dataElement = array();
	public $privateData = array();
	public $secondPrivateData = array();
	public $privateDataHeader = array();

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
			case 2: $sKey = 'pan';
					break;
			case 12: $sKey = 'dt';
					break;
			case 24: $sKey = 'f_code';
					break;
			case 39: $sKey = 'rc';
					break;
		}
		return $sKey;
	}

	public function getDataElement()
	{
		return $this->dataElement['rp'];
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