<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Page extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	public function listing(){
        $this->u_access->module('page/listing');
	    $data['main'] = $this->m_db->get('page');
        $this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('page', $data);
		$this->load->view('themes/main-bot');
	}
	
    public function act(){
	    $this->u_access->module('page/listing');
	    $id = $this->input->post('txID');
	    $title = strip_tags($this->input->post('txTitle'));
	    $code = strip_tags($this->input->post('txCode'));
	    $content = strip_tags($this->input->post('txContent'));
	    $array = [
	                'title' => $title,
	                'code' => $code,
	                'content' => $content
                 ];
	    if($id > 0){
	        $this->m_db->_update('page',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('page',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('page/listing');
	}
	
    public function del(){
        $this->u_access->module('page/listing');
	    $id = $this->input->post('tbxID');
	    $title = $this->input->post('tbxTitle');
	    $this->m_db->_delete('page',$id);
	    $this->fun->alert('del',$title);
	    redirect('page/listing');
	}
	


}
?>