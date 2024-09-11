<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

class kepala{
	public $judul="Pendaftaran Notaris";//default
	
	function ganti($baru){
		$this->judul=$baru;
	}
	function tampilJudul(){
		echo $this->judul;
	}
}

class body{
	public $warna="#FFFFFF";//default

	function ubah($warnaBaru){
		$this->warna=$warnaBaru;
	}
	function tampilWarna(){
		echo $this->warna;
	}
}

class konek{
	public $h;
	public $u;
	public $p;
	public $d;

	function koneksiHost($host,$user,$pwd){
		$this->h=$host;
		$this->u=$user;
		$this->p=$pwd;
	}

	function konekDb($db){
		$this->d=$db;
	}
	
	function konekH(){
		echo $this->h;
	}

	function konekU(){
		echo $this->u;
	}

	function konekP(){
		echo $this->p;
	}

	function konekD(){
		echo $this->d;
	}
}
?>
