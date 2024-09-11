<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class U_access{
	public function usersessions($page){
		if(!isset($_SESSION['user_id'])){
			redirect($page);
		} 
	}
	
	public function akun(){
		$result['usr'] = 'dshbpajak';
		$result['pwd'] = password_hash("dshbpajak", PASSWORD_DEFAULT);
		return $result;
	}
}