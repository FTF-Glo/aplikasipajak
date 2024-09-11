<?php
class api extends db
{
	protected $tReq;
	
    public function __construct($pReq){		
        parent::__construct();
        $this->connectSW();
        $this->connectGW();
        $this->connectPG();
		$this->tReq = $pReq;
    }
		
	public function getDataWPPBB(&$message){
		$status = 0;
		$data = PBB::getDataWPPBB($this->connSw,$this->tReq->params->wpid,$status);			
		if (!empty($data)){
			$message["rc"] = "1";
			$message['status'] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['status'] = $status;
			$message['result'] = null;
		}	
		return $data;
	}
	public function getDataOPPBB(&$message){
		$status = 0;
		$data = PBB::getDataOPPBB($this->connSw,$this->tReq->params->nop,$status);			
		if (!empty($data)){
			$message["rc"] = "1";
			$message['status'] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['status'] = $status;
			$message['result'] = null;
		}	
		return $data;
	}
	public function getDataOPChangePBB(&$message){
		$status = 0;
		$data = PBB::getDataOPChangePBB($this->connSw,$this->connGw,$this->tReq->params->nop,$status);			
		if (!empty($data)){
			$message["rc"] = "1";
			$message['status'] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['status'] = $status;
			$message['result'] = null;
		}	
		return $data;
	}
	public function checkTaxPBB(&$message){
		$status = 0;
		$data = PBB::checkTaxPBB($this->connSw,$this->tReq->params->nop,$status);			
		if (!empty($data)){
			$message["rc"] = "1";
			$message['status'] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['status'] = $status;
			$message['result'] = null;
		}	
		return $data;
	}
	public function checkDebtPBB(&$message){
		$status = 0;
		$data = PBB::checkDebtPBB($this->connGw,$this->tReq->params->nop,$this->tReq->params->tahun,$status);			
		if (!empty($data)){
			$message["rc"] = "1";
			$message['status'] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['status'] = $status;
			$message['result'] = null;
		}	
		return $data;
	}
	public function dataChangePBB(&$message){
		$status = 0;
		$data = PBB::dataChangePBB($this->connSw,$this->connGw,$this->tReq,$status);
		if (!empty($data)){
			$message["rc"] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['result'] = null;
		}		
		return $data;
	}
	public function updateOPPBBTematik_PG(&$message){
		$status = 0;
		$data = PBB::updateOPPBBTematik_PG($this->connSw,$this->connPg,$this->tReq->params->nop,$status);
		if (!empty($data)){
			$message["rc"] = $status;
			$message['result'] = $data;
		} else {
			$message["rc"] 	= "5";
			$message['result'] = null;
		}		
		return $data;
	}
    public function addToLog(){
        $data = null;
        $data = LogAcc::addToLog($this->connSw);
        return $data;
    }	
}
