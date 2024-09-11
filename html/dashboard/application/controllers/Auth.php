<?php
date_default_timezone_set('Asia/Jakarta');

// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class Auth extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('security');	
	}
	public function index(){
		$this->load->view('login');
	}

	function check(){
		$this->form_validation->set_rules('Username', 'Username', 'required');
		$this->form_validation->set_rules('Password', 'Password', 'required');
		if($this->form_validation->run() == true){
			$username = $this->input->post("Username");
			$password = $this->input->post("Password");
		
			$currUser = $this->u_access->akun();
			$num = count($currUser);
			if($currUser['usr']==$username){
				if(password_verify($password, $currUser['pwd'])){
					$session_data = array('user_name' => $username);
					$this->session->set_userdata($session_data);
					redirect('index.php/Dashboard');
				} else {
					$new_record_array = array('status' => '0');
					$this->session->set_flashdata('message', "Username/Password Salah!!!");
					redirect('index.php/auth');
				}
			} else {
				$this->session->set_flashdata('message', "Username Tidak Ditemukan.");
				redirect('index.php/auth');
			}
		}
	}
	
	function logout(){
		session_destroy();
		redirect('index.php/auth');
	}	
}