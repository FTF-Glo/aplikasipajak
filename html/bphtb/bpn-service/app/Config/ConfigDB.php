<?php
namespace Config;

use \Core\Database;

class ConfigDB extends Database{

	private $hostGwBPHTB = '127.0.0.1';
	private $userGwBPHTB = 'root';
	private $passGwBPHTB = '@Lamsel2023';
	private $dbGwBPHTB 	 = 'GW_SSB';
	
	private $hostSwBPHTB = '127.0.0.1';
	private $userSwBPHTB = 'root';
	private $passSwBPHTB = '@Lamsel2023';
	private $dbSwBPHTB   = 'SW_SSB';

	private $hostGwPBB   = '127.0.0.1';
	private $userGwPBB   = 'root';
	private $passGwPBB   = '@Lamsel2023';
	private $dbGwPBB     = 'GW_PBB';
	
	private $hostSwPBB   = '127.0.0.1';
	private $userSwPBB   = 'root';
	private $passSwPBB   = '@Lamsel2023';
	private $dbSwPBB     = 'SW_PBB';
	
	protected $connGW;
	protected $connSW;
	protected $connGwPBB;
	protected $connSwPBB;

	protected function getConnGwBPHTB(){										  
		$this->connGW =  $this->connectDB($this->hostGwBPHTB, 
										  $this->dbGwBPHTB, 
										  $this->userGwBPHTB, 
										  $this->passGwBPHTB);
	}
	
	protected function getConnSwBPHTB(){
		$this->connSW =  $this->connectDB($this->hostSwBPHTB, 
										  $this->dbSwBPHTB, 
										  $this->userSwBPHTB, 
										  $this->passSwBPHTB);
	}	

	protected function getConnGwPBB(){										  
		$this->connGWPBB =  $this->connectDB($this->hostGwPBB, 
										  $this->dbGwPBB, 
										  $this->userGwPBB, 
										  $this->passGwPBB);
	}
	
	protected function getConnSwPBB(){
		$this->connSWPBB =  $this->connectDB($this->hostSwPBB, 
										  $this->dbSwPBB, 
										  $this->userSwPBB, 
										  $this->passSwPBB);
	}		
}
?>
