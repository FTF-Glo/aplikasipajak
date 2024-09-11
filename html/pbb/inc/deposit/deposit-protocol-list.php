<?php 
class DepositProtocolListRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2200); //mti
		$this->SetValueForDataElement(3,340000); //processing code for list transaction history
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'priv') $iKey = 48;
		elseif($sKey == 'priv2') $iKey = 61;
		elseif($sKey == 'priv3') $iKey = 62;
		return $iKey;
	}
	
	private function ConstructPrivateData($aPriv)
	{
		$sPriv = '';
		$sPriv .= $aPriv['dt_from']; //Date Start List Transaction
		$sPriv .= $aPriv['dt_until']; //Date End List Transaction
		$sPriv .= str_pad((isset($aPriv['start_idx']) ? $aPriv['start_idx'] : "1") , 4, "0", STR_PAD_LEFT); //Indeks Start List Transaction.
		$sPriv .= str_pad((isset($aPriv['total']) ? $aPriv['total'] : "0"), 4, "0", STR_PAD_LEFT); //Max total transaction in list, 0 means all / undefined
		$sPriv .= (isset($aPriv['aggregat']) ? $aPriv['aggregat'] : "0") ; //0 = false; 1 = true
		
		return $sPriv;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		if($sKey == 'priv')
		{
			$value = $this->ConstructPrivateData($value);
		}
		$this->SetValueForDataElement($keyIdx,$value);
	}
}
class DepositProtocolListResponse extends CISO8583Parser
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
			case 12: $sKey = 'dt';
					break;
			case 39: $sKey = 'rc';
					break;
			case 48: $sKey = 'priv';
					break;
			case 61: $sKey = 'priv2';
					break;
			case 62: $sKey = 'priv3';
					break;
			case 72: $sKey = 'data';
					break;
		}
		return $sKey;
	}

	private function SplitPrivateData($stream)
	{
		$splitter = new ElectricityProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockHeaderLengthArray(array(8, 8, 4, 4, 1, 4, 4));		
		$splitter->SetBlockHeaderAssocNameArray(array( 'dt_from', 'dt_until', 'start_idx', 'total', 'aggregat', 'total_in_range', 'total_retrieved'));
		$splitter->Extract();
		$this->privateDataHeader = $splitter->GetComponentHeaderArray();	
		
		foreach($this->privateDataHeader as $key => $value) $this->privateData[$key] = trim($value);		
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
			if ($this->dataElement['rc'] == "0000") {
				$this->SplitPrivateData($this->dataElement['priv']);
				$this->dataElement['priv'] = $this->privateData;
			}
		}
	}
}
?>