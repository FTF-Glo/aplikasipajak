<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller {
    function _remap($param) {
        $this->index($param);
    }
    
    public function index($code){
        $main = $this->m_db->get_where_col('page','code',$code);
        $data['main'] = $main[0];
        $this->load->view('theme/header');
		$this->load->view('page',$data);
		$this->load->view('theme/footer');
    }
    
}
