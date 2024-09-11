<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Auth extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('m_users');
		$this->load->library('form_validation');
		$this->load->helper('security');	
	}
	public function index($message="", $message_header=""){
		$data['module'] = 'index';
		$data['message'] = $message;
		$data['message_header'] = $message_header;
		$data['password'] = password_hash('admin', PASSWORD_DEFAULT);
		
		$this->load->view('themes/login-top');		
		$this->load->view('login', $data);
		$this->load->view('themes/login-bot');
	}
	
	function check(){
		$this->form_validation->set_rules('tbxUsername', 'Username', 'trim|required|xss_clean');
		$this->form_validation->set_rules('tbxPassword', 'Password', 'trim|required|xss_clean');
		if($this->form_validation->run() == true){
			$username = $this->input->post("tbxUsername");
			$password = $this->input->post("tbxPassword");
		
			$currUser = $this->m_users->get_query_customize($username);
			$num = count($currUser);
			if($num > 0){
				if(password_verify($password, $currUser[0]['password'])){
					$session_data = array('user_id' => $currUser[0]['id'],
										  'user_name' => $currUser[0]['username'],
										  'user_priv' => $currUser[0]['is_admin']);
					$this->session->set_userdata($session_data);
					$data_array = array('lastlogin' => date('Y-m-d H:i:s'));
					$this->m_users->_update($currUser[0]['id'], $data_array);
					redirect('main');
				} else {
					$new_record_array = array('status' => '0');
					$this->session->set_flashdata('message', "Wrong Username or Password");
					redirect('auth');
				}
			} else {
				$this->session->set_flashdata('message', "Wrong Username or Password");
				redirect('auth');	
			}
		}
	}
	
	function logout(){
		session_destroy();
		unset($_SESSION['user_id']);
		redirect('auth');
	}	
	
	

}
?>