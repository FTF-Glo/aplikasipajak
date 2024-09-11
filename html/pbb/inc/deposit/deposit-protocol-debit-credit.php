<?php 
class DepositProtocolDebitCreditRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2200); //mti
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'p_code') $iKey = 3;
		elseif($sKey == 'rp') $iKey = 4;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'priv') $iKey = 48;
		elseif($sKey == 'priv2') $iKey = 60;
		elseif($sKey == 'priv3') $iKey = 61;
		elseif($sKey == 'priv4') $iKey = 62;
		elseif($sKey == 'priv5') $iKey = 63;
		
		return $iKey;
	}
	
	private function ConstructPrivateData($aPriv)
	{
		$sPriv = '';
		$sPriv .= str_pad($aPriv['amount'], 32, "0", STR_PAD_LEFT); //Amount debit/credit
		$sPriv .= str_pad($aPriv['sendermodname'], 10, " ", STR_PAD_RIGHT); //Sender module PAN module
		$sPriv .= str_pad($aPriv['swrefnum'], 32, " ", STR_PAD_RIGHT); //Reference number used in switcher
		
		return $sPriv;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		if($sKey == 'rp')
		{
			$value = '3600'.str_pad($value,12,"0",STR_PAD_LEFT);
		} 
		else if($sKey == 'pan')
		{
			$value = $value;
		} 
		else if($sKey == 'priv')
		{
			$value = $this->ConstructPrivateData($value);
		}
		$this->SetValueForDataElement($keyIdx,$value);
	}
}
class DepositProtocolDebitCreditResponse extends CISO8583Parser
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
			case 3: $sKey = 'p_code';
					break;
			case 4: $sKey = 'rp';
					break;
			case 12: $sKey = 'dt';
					break;
			case 39: $sKey = 'rc';
					break;
			case 48: $sKey = 'priv';
					break;
			case 60: $sKey = 'priv2';
					break;
			case 61: $sKey = 'priv3';
					break;
			case 62: $sKey = 'priv4';
					break;
			case 63: $sKey = 'priv5';
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
	private function SplitPrivateData($stream)
	{
		$splitter = new ElectricityProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockHeaderLengthArray(array(32, 10, 32));		
		$splitter->SetBlockHeaderAssocNameArray(array( 'amount', 'sendermodname', 'swrefnum'));
		$splitter->Extract();
		$this->privateDataHeader = $splitter->GetComponentHeaderArray();	
		
		foreach($this->privateDataHeader as $key => $value) $this->privateData[$key] = trim($value);		
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
			
			$this->SplitPrivateData($this->dataElement['priv']);
			$this->dataElement['priv'] = $this->privateData;
			$this->dataElement['rp'] = $this->GetTrxAmount($this->dataElement['rp']);
		}
	}
}
?>