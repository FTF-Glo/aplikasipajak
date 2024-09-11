<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	public function index($message="", $message_header=""){
		$data['module'] = 'index';
		$data['message'] = $message;
		$data['message_header'] = $message_header;
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('dashboard/main', $data);
		$this->load->view('themes/main-bot');
	}
	


}
?>