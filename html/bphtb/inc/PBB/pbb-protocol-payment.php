<?php 
$sRootPath = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'pbb', '', dirname(__FILE__)).'/');
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/protocol-generic.php");

class pbbProtocolPaymentRequest extends CISO8583Message
{
	function __construct()
	{
		parent::__construct();
		$this->SetVersion("2003");
		//set defaults
		$this->SetValueForDataElement(0,2200); //mti
		$this->SetValueForDataElement(26,"6014"); //merchant
	}
	private function GetMappingKeyIdx($sKey)
	{
		if($sKey == 'mti') $iKey = 0;
		elseif($sKey == 'pan') $iKey = 2;
		elseif($sKey == 'tran_amount') $iKey = 4;
		elseif($sKey == 'stan') $iKey = 11;
		elseif($sKey == 'dt') $iKey = 12;
		elseif($sKey == 'bank_code') $iKey = 32;
		elseif($sKey == 'central_id') $iKey = 33;
		elseif($sKey == 'ppid') $iKey = 41;
		elseif($sKey == 'priv') $iKey = 48;
		elseif($sKey == 'priv2') $iKey = 61;
		elseif($sKey == 'priv3') $iKey = 62;
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
		$sPriv .= str_pad($aPriv['area_name'],50," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['tax_name'],50," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['tax_refnum'],32," ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['gw_refnum'],32," ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['sw_refnum'],32," ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['pay_refnum'],32," ", STR_PAD_RIGHT);
		$sPriv .= $aPriv['due_date'];
		$sPriv .= str_pad($aPriv['minor'],1,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['total_amount'],17,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['ori_bill'],17,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['collectible_bil'],17,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['misc_bill'],17,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['penalty_fee'],17,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['admin_fee'],9,"0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv['subject_name'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_address'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_rt_rw'],9," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_kelurahan'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_kecamatan'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_kabupaten'],5," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['subject_zip_post'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['object_address'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['object_rt_rw'],9," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['object_kelurahan'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['object_kecamatan'],25," ",STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv['object_kabupaten'],25," ",STR_PAD_RIGHT);
		return $sPriv;
	}
	
	private function ConstructSecondPrivateData($aPriv2)
	{
		$sPriv = '';
		$sPriv .= str_pad($aPriv2['repeat'], 2, "0", STR_PAD_LEFT);
		$sPriv .= str_pad($aPriv2['sppt_tgl_terbit'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['sppt_tgl_cetak'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_luas_bumi'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_luas_bangunan'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_kelas_bumi'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_kelas_bangunan'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_njop_bumi'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_njop_bangunan'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_njop'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_njop_tkp'], 32, " ", STR_PAD_RIGHT);
		$sPriv .= str_pad($aPriv2['op_njkp'], 32, " ", STR_PAD_RIGHT);
		
		return $sPriv;	
	}
	
	private function ConstructThirdPrivateData($aPriv2)
	{
		/*$sPriv2 = '';
		$sPriv2 .= $aPriv2['minor'];
		$sPriv2 .= $aPriv2['total'];
		for($i = 0; $i < trim($aPriv2['total']); $i++){
			$sPriv2 .= str_pad($aPriv2['type'][$i], 2, "0", STR_PAD_LEFT);
			$sPriv2 .= str_pad($aPriv2['amount'][$i], 17, "0", STR_PAD_LEFT);  
		}*/
		
		$sPriv = '';
		$sPriv .= $aPriv2['minor'];
		$sPriv .= str_pad($aPriv2['total'], 2, "0", STR_PAD_LEFT);
		//echo "total ->".$aPriv2['total']*1;
		for($i = 0; $i <$aPriv2['total']*1; $i++){
			$sPriv .= str_pad($aPriv2['type'][$i+1], 2, "0", STR_PAD_LEFT);
			$sPriv .= str_pad($aPriv2['amount'][$i+1], 17, "0", STR_PAD_LEFT);  
		}
		//print_r($aPriv2['amount']);
		return $sPriv;	
	}
	
	public function SetComponentTmp($sKey,$value)
	{
		$keyIdx = $this->GetMappingKeyIdx($sKey);
		if($sKey == 'rp')
		{
			$value = '3600'.str_pad($value,12,"0",STR_PAD_LEFT);
		}
		elseif($sKey == 'priv')
		{
			$value = $this->ConstructPrivateData($value);
		}		
		elseif($sKey == 'priv2')
		{
			$value = $this->ConstructSecondPrivateData($value);
		}
		elseif($sKey == 'priv3')
		{
			$value = $this->ConstructThirdPrivateData($value);
		}
		elseif($sKey == 'ppid')
		{
			$value = str_pad($value,16,"0",STR_PAD_LEFT);
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
class pbbProtocolPaymentResponse extends CISO8583Parser
{
	public $dataElement = array();
	public $privateData = array();
	public $secondPrivateData = array();
	public $privateDataHeader = array();
	public $privateDataSingle = array();
	public $privateDataTracking = array();
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
			case 15: $sKey = 'dt_set';
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
			case 61: $sKey = 'priv2';
					break;
		    case 62: $sKey = 'priv3';
					break;
			case 63: $sKey = 'infotext';
					break;
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
	
	private function SplitPrivateData($stream){
		$splitter = new ProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockHeaderLengthArray(array(7, 6, 4, 1, 32, 50, 50, 32, 32, 32, 32, 8, 1, 17, 17, 17, 17, 17, 9, 25, 25, 9, 25, 25, 25, 5, 25, 9, 25, 25, 25));		
		$splitter->SetBlockHeaderAssocNameArray(array('sid', 'area_code', 'tax_type', 'flag', 'nop_npwp', 'area_name','tax_name','tax_refnum','gw_refnum','sw_refnum',
		'pay_refnum','due_date','minor','total_amount','ori_bill','collectible_bil','misc_bill','penalty_fee','admin_fee','subject_name','subject_address',
		'subject_rt_rw','subject_kelurahan','subject_kecamatan','subject_kabupaten','subject_zip_post','object_address','object_rt_rw','object_kelurahan',
		'object_kecamatan','object_kabupaten'));
		$splitter->Extract();
		$this->privateDataHeader = $splitter->GetComponentHeaderArray();	
		foreach($this->privateDataHeader as $key => $value) $this->privateData[$key] = trim($value);
	}

	private function SplitSecondPrivateData($stream){ 
		$splitter = new ProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockSingleLengthArray(array(2, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32));
		$splitter->SetBlockSingleAssocNameArray(array( 'repeat', 'sppt_tgl_terbit','sppt_tgl_cetak','op_luas_bumi','op_luas_bangunan','op_kelas_bumi','op_kelas_bangunan',
		'op_njop_bumi','op_njop_bangunan','op_njop','op_njop_tkp','op_njkp'));
		$splitter->Extract();
		$this->secondPrivateDataSingle = $splitter->GetComponentSingleArray();
		foreach($this->secondPrivateDataSingle as $key => $value) $this->secondPrivateData[$key] = trim($value);
	}
	
	private function SplitThirdPrivateData($stream){
		$splitter = new ProtocolGeneric();
		$splitter->SetStream($stream);
		$splitter->SetBlockSingleLengthArray(array(1, 2));
		$splitter->SetBlockSingleAssocNameArray(array( 'minor', 'total'));
		
		$splitter->SetBlockRepeatLengthArray(array(2,17));
		$splitter->SetBlockRepeatAssocNameArray(array('type', 'amount'));
		
		$splitter->SetRepeatKey('total');
		
		$splitter->Extract();
		
		$this->thirdPrivateDataSingle = $splitter->GetComponentSingleArray();
		$this->thirdPrivateDataRepeat = $splitter->GetComponentRepeatArray();
		
		foreach($this->thirdPrivateDataSingle as $key => $value) $this->thirdPrivateData[$key] = trim($value);
		if ($this->thirdPrivateDataRepeat) {
			foreach($this->thirdPrivateDataRepeat as $key => $value) 
			{
				for($i = 0 ; $i < $this->thirdPrivateDataSingle[$splitter->GetRepeatKey()]; $i++)
				{
					$idx = $i+1;
					$this->thirdPrivateData[$key][$idx] = trim($value[$i]);
				}
			}
		}
	}
	
	function ExtractDataElement()
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
		$this->SplitSecondPrivateData($this->dataElement['priv2']);
		$this->SplitThirdPrivateData($this->dataElement['priv2']);
		$this->dataElement['priv'] = $this->privateData;
		$this->dataElement['priv2'] = $this->secondPrivateData;
		$this->dataElement['priv3'] = $this->thirdPrivateData;
		$this->dataElement['rp'] = $this->GetTrxAmount($this->dataElement['rp']);
		}
		
	}
}
?>
