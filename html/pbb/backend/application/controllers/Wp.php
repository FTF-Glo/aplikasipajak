<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Wp extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	//pajak info =====================START=============================
	public function listing(){ 
		$this->u_access->module('wp/list');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('wp/list');
		$this->load->view('themes/main-bot');
	}
	
	public function usaha(){
        $this->u_access->module('wp/usaha');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('wp/usaha');
		$this->load->view('themes/main-bot');
	}
	
	public function validasi_usaha(){
        $this->u_access->module('wp/validasi_usaha');
        $this->load->model('m_usaha');
        $data['main'] = $this->m_usaha->getWhereActive();
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('wp/validasi_usaha',$data);
		$this->load->view('themes/main-bot');
	}
	
	public function validasi_usaha_detail($id){
        $this->u_access->module('wp/validasi_usaha');
        $this->load->model('m_usaha');
        $usaha = $this->m_usaha->getWhereID($id);
        $data['usaha'] = $usaha[0];
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('wp/validasi_usaha_detail',$data);
		$this->load->view('themes/main-bot');
	}
	
	public function validasi_usaha_accept(){
		$id = (int)$this->input->post('txID');
		$npwpd = strip_tags(trim($this->input->post('txNPWPD')));
		$array = [
					'status' => '1',
					'npwpd' => $npwpd
				 ];
		$this->m_db->_update('users_usaha',$id,$array);
		redirect('wp/validasi_usaha');
	}
	
	public function validasi_usaha_reject(){
		$id = (int)$this->input->post('txID');
		$array = [
					'status' => '2'
				 ];
		$this->m_db->_update('users_usaha',$id,$array);
		redirect('wp/validasi_usaha');
	}

}
?>