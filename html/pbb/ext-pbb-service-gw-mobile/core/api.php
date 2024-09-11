<?php
class api extends db
{
    public function __construct(){
        parent::__construct();
        $this->connect();
    }

    public function getTagihanSPPT(){
        $data = null;
        $data = PBB::getTagihanSPPT($this->conn);
        return $data;
    }

    public function getDaftarTagihanSPPT(){
        $data = null;
        $data = PBB::getDaftarTagihanSPPT($this->conn);
        return $data;
    }
    
    public function getRealisasiSPPT(){
        $data = null;
        $data = PBB::getRealisasiSPPT($this->conn);
        return $data;
    }
    
    public function getDataUser(){
        $data = null;
        $data = User::getDataUser($this->conn);
        return $data;
    }

    public function addToLog(){
        $data = null;
        $data = LogAcc::addToLog($this->conn);
        return $data;
    }

    public function getConfig(){
        $data = null;
        $data = ConfigVPOS::getConfig($this->conn);
        return $data;
    }
}
