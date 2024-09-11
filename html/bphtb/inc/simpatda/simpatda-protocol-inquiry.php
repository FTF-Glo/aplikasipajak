<?php
$sRootPath = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'simpatda', '', dirname(__FILE__)).'/');
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/protocol-generic.php");

class simpatdaProtocolInquiryRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2100); //mti for inquiry
		$this->SetValueForDataElement(26,"6014"); //Merchant code for Internet
		$this->SetValueForDataElement(32,"0000000"); //Bank code
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'stan') $iKey = 11;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'merchant') $iKey = 26;
		elseif($sKey == 'bank_code') $iKey = 32;
		elseif($sKey == 'central_id') $iKey = 33;
		elseif($sKey == 'ppid') $iKey = 41;
		elseif($sKey == 'priv') $iKey = 48;
		
		return $iKey;
	}
	
	private function ConstructPrivateData($aPriv)
	{
		$sPriv = '';
		$sPriv .= str_pad($aPriv['sid'], 7, "0", STR_PAD_LEFT); //switchid
		$sPriv .= str_pad($aPriv['area_code'],6,"0", STR_PAD_LEFT); //
		$sPriv .= str_pad($aPriv['tax_type'],4,"0", STR_PAD_LEFT); //
		$sPriv .= $aPriv['flag']; //
		$sPriv .= str_pad($aPriv['nop_npwp'],32," ",STR_PAD_RIGHT); //
		
		return $sPriv;
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		if($sKey == 'priv')
		{
			$value = $this->ConstructPrivateData($value);
		}
		elseif($sKey == 'stan')
		{
			$value = str_pad($value,12,"0",STR_PAD_LEFT);
		}
		elseif($sKey == 'central_id')
		{
			$value = str_pad($value,7,"0",STR_PAD_LEFT);
		}
		
		
		$this->SetValueForDataElement($keyIdx,$value);
	}
}

class simpatdaProtocolInquiryResponse extends CISO8583Parser
{
	public $dataElement = array();
	public $privateData = array();
	public $privateDataHeader = array();
	public $secondPrivateData = array();
	public $secondPrivateDataHeader = array();
	public $secondPrivateDataSingle = array();
	public $secondPrivateDataRepeat = array();
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
			case 4: $sKey = 'rp';
					break;
			case 11: $sKey = 'stan';
					break;
			case 12: $sKey = 'dt';
					break;
			case 26: $sKey = 'merchant';
					break;
			case 32: $sKey = 'bank_code';
					break;
			case 33: $sKey = 'central_id';
					break;
			case 39: $sKey = 'rc';
					break;
			case 41: $sKey = 'ppid';
					break;
			case 48: $sKey = 'priv';
					break;
			case 62: $sKey = 'priv2';
		}
		return $sKey;
	}
	
	private function GetTrxAmount($stream)
	{
		$splitter = new ProtocolGeneric();
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
		$splitter = new ProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockHeaderLengthArray(array(7, 6, 4, 1, 32, 50, 50, 32, 32, 32, 32, 8, 1, 17, 17, 17, 17, 17, 9, 25, 25, 9, 25, 25, 25, 5, 25, 25, 9, 25, 25, 25));		
		$splitter->SetBlockHeaderAssocNameArray(array('sid', 'area_code', 'tax_type', 'flag', 'nop_npwp', 'area_name','tax_name','tax_refnum','gw_refnum','sw_refnum',
		'pay_refnum','due_date','minor','total_amount','ori_bill','collectible_bil','misc_bill','penalty_fee','admin_fee','subject_name','subject_address',
		'subject_rt_rw','subject_kelurahan','subject_kecamatan','subject_kabupaten','subject_zip_post','object_name','object_address','object_rt_rw','object_kelurahan',
		'object_kecamatan','object_kabupaten'));
		$splitter->Extract();
		$this->privateDataHeader = $splitter->GetComponentHeaderArray();	
		
		foreach($this->privateDataHeader as $key => $value) $this->privateData[$key] = trim($value);		
	}
	
	private function SplitSecondPrivateData($stream){
		$splitter = new ProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockSingleLengthArray(array(1, 2));
		$splitter->SetBlockSingleAssocNameArray(array( 'minor', 'total'));
		
		$splitter->SetBlockRepeatLengthArray(array(2,17));
		$splitter->SetBlockRepeatAssocNameArray(array('type', 'amount'));
		
		$splitter->SetRepeatKey('total');
		
		$splitter->Extract();
	/*	$this->secondPrivateDataHeader = $splitter->GetComponentHeaderArray();
		
		foreach($this->secondPrivateDataHeader as $key => $value) $this->secondPrivateData[$key] = trim($value);*/
		
		
		
		$this->secondPrivateDataSingle = $splitter->GetComponentSingleArray();
		$this->secondPrivateDataRepeat = $splitter->GetComponentRepeatArray();
		
		foreach($this->secondPrivateDataSingle as $key => $value) $this->secondPrivateData[$key] = trim($value);
		if ($this->secondPrivateDataRepeat) {
			foreach($this->secondPrivateDataRepeat as $key => $value) 
			{
				for($i = 0 ; $i < $this->secondPrivateDataSingle[$splitter->GetRepeatKey()]; $i++)
				{
					$idx = $i+1;
					$this->secondPrivateData[$key][$idx] = trim($value[$i]);
				}
			}
		}
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
			if ($this->dataElement['rc'] == "0000") {
				$this->SplitPrivateData($this->dataElement['priv']);
				$this->SplitSecondPrivateData($this->dataElement['priv2']);
				$this->dataElement['priv'] = $this->privateData;
				$this->dataElement['priv2'] = $this->secondPrivateData;
				$this->dataElement['rp'] = $this->GetTrxAmount($this->dataElement['rp']);
			}
		}
	}
}
?>
