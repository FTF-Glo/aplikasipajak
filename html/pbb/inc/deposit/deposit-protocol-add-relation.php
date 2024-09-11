<?php 
class DepositProtocolAddRelationRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2600); //mti
		$this->SetValueForDataElement(24,"441"); //p_code
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'rpup') $iKey = 4;
		elseif($sKey == 'rpdown') $iKey = 5;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'f_code') $iKey = 24;
		elseif($sKey == 'ran') $iKey = 42;
		elseif($sKey == 'priv') $iKey = 48;
		
		return $iKey;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		if($sKey == 'rpup')
		{
			$value = '3600'.str_pad($value,12,"0",STR_PAD_LEFT);
		} else if($sKey == 'rpdown')
		{
			$value = '3600'.str_pad($value,12,"0",STR_PAD_LEFT);
		} 
		$this->SetValueForDataElement($keyIdx,$value);
	}
}
class DepositProtocolAddRelationResponse extends CISO8583Parser
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
			case 4: $sKey = 'rpup';
					break;
			case 5: $sKey = 'rpdown';
					break;
			case 12: $sKey = 'dt';
					break;
			case 24: $sKey = 'f_code';
					break;
			case 39: $sKey = 'rc';
					break;
			case 48: $sKey = 'priv';
					break;
		}
		return $sKey;
	}
	private function GetTrxAmount($stream)
	{
		$splitter = new ElectricityProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockHeaderLengthArray(array(3, 1, 12));
		$splitter->SetBlockHeaderAssocNameArray(array('icc','cmu','val'));
		$splitter->Extract();
		$arr = $splitter->GetComponentHeaderArray();
		$dec = pow(10,$arr['cmu']);
		return $arr['val']/$dec;
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
			
			$this->dataElement['rpup'] = $this->GetTrxAmount($this->dataElement['rpup']);
			$this->dataElement['rpdown'] = $this->GetTrxAmount($this->dataElement['rpdown']);
		}
	}
}
?>