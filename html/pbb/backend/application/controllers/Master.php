<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Master extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	//pajak info =====================START=============================
	public function info(){ 
		$this->u_access->module('master/info');
	    $data['main'] = $this->m_db->get('pajak_info');
	    $data['type'] = $this->m_db->get('pajak_type');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master/info', $data);
		$this->load->view('themes/main-bot');
	}
	public function info_act(){
	    $this->u_access->module('master/info');
	    $id = $this->input->post('txID');
	    $content = trim($this->input->post('txContent'));
	    $array = [
	                'content' => $content
                 ];
	    if($id > 0){
	        $this->m_db->_update('pajak_info',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } 
	    redirect('master/info');
	}
	
	public function info_del(){
        $this->u_access->module('master/info');
	    $id = $this->input->post('tbxID');
	    $this->m_db->_delete('pajak_info',$id);
	    $this->fun->alert('del',$name);
	    redirect('master/info');
	}
	//pajak info ======================END==================================
	//Badan Usaha =====================START=============================
	public function badan_usaha(){ 
		$this->u_access->module('master/badan_usaha');
	    $data['main'] = $this->m_db->get('badan_usaha');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master/badan_usaha', $data);
		$this->load->view('themes/main-bot');
	}
	public function badan_usaha_act(){
	    $this->u_access->module('master/badan_usaha');
	    $id = $this->input->post('txID');
	    $name = strip_tags($this->input->post('txName'));
	    $desc = strip_tags($this->input->post('txDesc'));
	    $array = [
	                'name' => $name,
	                'description' => $desc
                 ];
	    if($id > 0){
	        $this->m_db->_update('badan_usaha',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('badan_usaha',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master/badan_usaha');
	}
	
    public function badan_usaha_del(){
        $this->u_access->module('master/badan_usaha');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('badan_usaha',$id);
	    $this->fun->alert('del',$name);
	    redirect('master/badan_usaha');
	}
	
		//Badan Usaha ======================END==================================
	//Kelas Usaha =====================START=============================
	public function kelas_usaha(){ 
		$this->u_access->module('master/kelas_usaha');
	    $data['main'] = $this->m_db->get('usaha_class');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master/kelas_usaha', $data);
		$this->load->view('themes/main-bot');
	}
	public function kelas_usaha_act(){
	    $this->u_access->module('master/kelas_usaha');
	    $id = $this->input->post('txID');
	    $name = strip_tags($this->input->post('txName'));
	    $desc = strip_tags($this->input->post('txDesc'));
	    $array = [
	                'title' => $name,
	                'description' => $desc
                 ];
	    if($id > 0){
	        $this->m_db->_update('usaha_class',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('usaha_class',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master/kelas_usaha');
	}
	
    public function kelas_usaha_del(){
        $this->u_access->module('master/kelas_usaha');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('usaha_class',$id);
	    $this->fun->alert('del',$name);
	    redirect('master/kelas_usaha');
	}
	
	//Kelas Usaha =====================START=============================
	public function pajak_type(){ 
		$this->u_access->module('master/pajak_type');
		$data['main'] = $this->m_db->get('pajak_type');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master/pajak_type', $data);
		$this->load->view('themes/main-bot');
	}
	public function pajak_type_act(){
		$this->u_access->module('master/pajak_type');
		$id = $this->input->post('txID');
		$tax = $this->fun->getPrepNumberFloat($this->input->post('txTax'));
		$tax_fine = $this->fun->getPrepNumberFloat($this->input->post('txFine'));
		$array = [
					'tax' => $tax,
					'tax_fine' => $tax_fine
				 ];

		$this->m_db->_update('pajak_type',$id,$array);
		$this->fun->alert('edit',"ID: ".$id);

		redirect('master/pajak_type');
	}
	
	//Kelas Usaha =====================START=============================
	public function usaha(){ 
		$this->u_access->module('master/usaha');
		$data['main'] = $this->m_db->get('usaha');
		$data['class'] = $this->m_db->get('usaha_class');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master/usaha', $data);
		$this->load->view('themes/main-bot');
	}
	public function usaha_act(){
		$this->u_access->module('master/usaha');
		$id = $this->input->post('txID');
		$name = strip_tags($this->input->post('txName'));
		$class = $this->input->post('cbClass');
		$desc = strip_tags($this->input->post('txDesc'));
		$array = [
					'title' => $name,
					'id_class' => $class,
					'description' => $desc
				 ];
		if($id > 0){
			$this->m_db->_update('usaha',$id,$array);
			$this->fun->alert('edit',"ID: ".$id);
		} else{
			$this->m_db->_insert('usaha',$array);
			$this->fun->alert('add',$name);

		}

		redirect('master/usaha');
	}
	
	public function usaha_del(){
		$this->u_access->module('master/usaha');
		$id = $this->input->post('tbxID');
		$name = $this->input->post('tbxName');
		$this->m_db->_delete('usaha',$id);
		$this->fun->alert('del',$name);
		redirect('master/usaha');
	}
	
	

}
?>