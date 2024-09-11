<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Tools_config extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
		$this->load->model('m_db');
	}
	
	//VIEW PAGES =====================START=============================
	public function view(){ 
		$this->u_access->module('tools_config/view');
		$data['main'] = $this->m_db->get('config');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('tools/config', $data);
		$this->load->view('themes/main-bot');
	}
	
	public function save(){
		$this->u_access->module('tools_config/view');
	    $data = $this->m_db->get('config');
	    foreach($data as $row){
	        $data_array = ['value' => $this->input->post($row['code'])];
	        $this->m_db->_update('config',$row['id'],$data_array);
	    }
	    $this->session->set_flashdata('item',$this->html->alert_success_edit('konfigurasi'));
	    redirect('tools_config/view');
	}
	
}
?>