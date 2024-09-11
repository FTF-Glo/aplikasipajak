<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	function getTrayek($id){
        $data = $this->m_db->get_where('trayek',$id);
		echo json_encode($data);
	}
	

}
?>