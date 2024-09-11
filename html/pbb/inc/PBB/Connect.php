<?php
class Connect{
	private $host;
	private $user;
	private $pass;
	private $db;
	private $table;	
	private $conn;
	function __construct($host,$user,$pass,$db=""){
		$this->host = $host; $this->user = $user; $this->pass = $pass;
		$this->conn = mysqli_connect($host,$user,$pass,$db) or die("Koneksi Mysql Gagal");
	}
	function setDB($db){
		$this->db = $db;
		mysqli_select_db($this->conn, $db) or die("Koneksi Database Gagal");
	}
	function setTable($table){
		$this->table = $table;
	}
	function getConnect(){
		return $this->conn;
	}
	function getQuery($sql){
		$qu = mysqli_query($this->conn, $sql) or die(mysqli_error($DBLink)." => <b>".$sql."</b>");
		return $qu;
	}
	function view(){
		$sql = "select * from ".$this->table;
		$this->getQuery($sql);
	}
	function add($kolom){ // array('kolom'=>'value')
		$arMax = end($kolom);		
		$qkey = ""; $qval = "";	
		foreach($kolom as $key => $val){
			$qkey .= $key;
			if($arMax!=$val) $qkey .=",";					
			$qval .= "'".$val."'";
			if($arMax!=$val) $qval .=",";
				
		}
		$sql = "INSERT INTO ".$this->db.".".$this->table." (".$qkey.") VALUES (".$qval.");";
		$this->getQuery($sql);
	}	
	function edit($kolom,$where){
		$arMax = end($kolom);		
		$quSet = "";
		foreach($kolom as $key => $val){
			$quSet .= $key."= '".$val."'";
			if($arMax!=$val) $quSet .=",";
		}
		$sql = "UPDATE db_resep.".$this->table." SET ".$quSet." WHERE ".$this->table.".".$where." ;";
		$this->getQuery($sql);
	}
	function delete($kolom,$value){
		$sql = "DELETE FROM ".$this->table." WHERE ".$kolom." = '".$value."' ";
		$this->getQuery($sql);
	}
}
?>