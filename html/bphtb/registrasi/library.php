<?php
class kepala{
	public $judul;//default
	
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
	function tagImg($s,$b,$a){
		echo "<img src='".$s."' border='".$b."' alt='".$a."'></img>";
	}
	function tagInput($type,$name,$size,$id,$value,$onclick, $form){
		echo "<input type='".$type."' name='".$name."' size='".$size."' id='".$id."' value='".$value."' onClick='".$onclick."' class='".$form."'>";
	}
	function tagAhref($src,$onclick,$name){
		echo "<a href='".$src."' onClick='".$onclick."'>".$name."</a>";
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
