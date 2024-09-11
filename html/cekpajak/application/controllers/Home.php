<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function index(){
		$data['slider'] = $this->m_db->get('slider');
		$data['pajak'] = $this->m_db->get('pajak_type');
		$this->load->view('theme/header');
		$this->load->view('home',$data);
		$this->load->view('theme/footer');
	}
}
