<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Master_loc extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	//province =====================START=============================
	public function prov(){ 
		$this->u_access->module('master_loc/prov');
	    $data['main'] = $this->m_db->get('place_prov');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master_loc/prov', $data);
		$this->load->view('themes/main-bot');
	}
	public function prov_act(){
	    $this->u_access->module('master_loc/prov');
	    $id = $this->input->post('txID');
	    $name = strip_tags($this->input->post('txName'));
	    $code = strip_tags($this->input->post('txCode'));
	    $array = [
	                'name' => $name,
	                'code' => $code
                 ];
	    if($id > 0){
	        $this->m_db->_update('place_prov',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('place_prov',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master_loc/prov');
	}
	
    public function prov_del(){
        $this->u_access->module('master_loc/prov');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('place_prov',$id);
	    $this->fun->alert('del',$name);
	    redirect('master_loc/prov');
	}
	
    public function kab(){ 
		$this->u_access->module('master_loc/kab');
	    $data['main'] = $this->m_db->get('place_kab');
	    $data['prov'] = $this->m_db->get('place_prov');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master_loc/kab', $data);
		$this->load->view('themes/main-bot');
	}
	public function kab_act(){
	    $this->u_access->module('master_loc/kab');
	    $id = $this->input->post('txID');
	    $prov = $this->input->post('cbProv');
	    $name = strip_tags($this->input->post('txName'));
	    $array = [
	                'name' => $name,
	                'id_prov' => $prov
                 ];
	    if($id > 0){
	        $this->m_db->_update('place_kab',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('place_kab',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master_loc/kab');
	}
	
    public function kab_del(){
        $this->u_access->module('master_loc/kab');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('place_kab',$id);
	    $this->fun->alert('del',$name);
	    redirect('master_loc/kab');
	}
	
    public function kec(){ 
		$this->u_access->module('master_loc/kec');
	    $data['main'] = $this->m_db->get('place_kec');
        $data['prov'] = $this->m_db->get('place_prov');
	    $data['kab'] = $this->m_db->get('place_kab');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master_loc/kec', $data);
		$this->load->view('themes/main-bot');
	}
	public function kec_act(){
	    $this->u_access->module('master_loc/kec');
	    $id = $this->input->post('txID');
	    $prov = $this->input->post('cbProv');
	    $kab = $this->input->post('cbKab');
	    $name = strip_tags($this->input->post('txName'));
	    $array = [
	                'name' => $name,
	                'id_kab' => $kab
                 ];
	    if($id > 0){
	        $this->m_db->_update('place_kec',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('place_kec',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master_loc/kec');
	}
	
    public function kec_del(){
        $this->u_access->module('master_loc/kec');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('place_kec',$id);
	    $this->fun->alert('del',$name);
	    redirect('master_loc/kec');
	}
	
    public function kel(){ 
		$this->u_access->module('master_loc/kel');
	    $data['main'] = $this->m_db->get('place_kel');
        $data['prov'] = $this->m_db->get('place_prov');
	    $data['kab'] = $this->m_db->get('place_kab');
	    $data['kec'] = $this->m_db->get('place_kec');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('master_loc/kel', $data);
		$this->load->view('themes/main-bot');
	}
	public function kel_act(){
	    $this->u_access->module('master_loc/kel');
	    $id = $this->input->post('txID');
	    $prov = $this->input->post('cbProv');
	    $kab = $this->input->post('cbKab');
	    $kec = $this->input->post('cbKec');
	    $name = strip_tags($this->input->post('txName'));
	    $array = [
	                'name' => $name,
	                'id_kec' => $kec
                 ];
	    if($id > 0){
	        $this->m_db->_update('place_kel',$id,$array);
	        $this->fun->alert('edit',"ID: ".$id);
	    } else{
            $this->m_db->_insert('place_kel',$array);
	        $this->fun->alert('add',$name);

	    }

	    redirect('master_loc/kel');
	}
	
    public function kel_del(){
        $this->u_access->module('master_loc/kel');
	    $id = $this->input->post('tbxID');
	    $name = $this->input->post('tbxName');
	    $this->m_db->_delete('place_kel',$id);
	    $this->fun->alert('del',$name);
	    redirect('master_loc/kel');
	}

}
?>