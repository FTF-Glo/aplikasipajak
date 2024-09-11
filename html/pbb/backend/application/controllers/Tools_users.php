<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Tools_users extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
		$this->load->model('m_users');
		$this->load->model('m_menu');
		$this->load->model('m_db');
	}
	
	//VIEW PAGES =====================START=============================
	public function view(){ 
        $this->u_access->module('tools_users/view');
		$data['main'] = $this->m_users->get();
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('tools/users', $data);
		$this->load->view('themes/main-bot');
	}
	
    public function add($id=""){
        $this->u_access->module('tools_users/view');
        if($id==""){
           $data['main'] = "";
        } else {
            $data['main'] = $this->m_users->get_where($id);   
            $data['access'] = $this->m_menu->get_menu_access($id);
        }
        $data['id'] = $id;
        $data['menu'] = $this->fun->getMenuCat();
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('tools/users_add', $data);
		$this->load->view('themes/main-bot');
    }
    
    public function save(){
        $this->u_access->module('tools_users/view');
        $date_now = date("Y-m-d H:i:s");
        $id_users = $this->input->post('tbxID');
        $password1 = $this->input->post('tbxPassword1');
        $password = password_hash($password1, PASSWORD_DEFAULT);
        $data_array = [
                        'username' => $this->input->post('tbxUsername'),
                        'name' => $this->input->post('tbxName'),
                        'email' => $this->input->post('tbxEmail'),
                        'hp' => $this->input->post('tbxHP'),
                        'jabatan' => $this->input->post('tbxJabatan'),
                        'created_at' => $date_now
                      ];
        $menu_access = $this->input->post("tbxMenuAccess");
        if($id_users != ""){
            $this->m_users->_delete_access($id_users);
            $id = $id_users;
            if($password1 != ""){
                $array_password = ['password' => $password];
                $data_array = array_merge($data_array,$array_password);
            } 
            $this->m_users->_update($id, $data_array);
        }else {
            $array_password = ['password' => $password];
            $data_array = array_merge($data_array,$array_password);
            $this->m_users->_insert($data_array);
            $id = $this->db->insert_id();
        }
        
        foreach($menu_access as $row){
            $this->m_users->_insert_access(['id_admin'=>$id, 'id_menu'=>$row]);
        }
        
       redirect('tools_users/view');
    }
	
	public function check_exist_user($value=0){
        $this->u_access->module('tools_users/view');
        $data = $this->m_db->get_count_where('users','username',$value);
	    if($data == 0){
	        $array = ['exist' => '0'];
	    } else {
	        $array = ['exist' => '1'];
	    }
	    echo json_encode($array);	    
	}
	function selfEdit(){
        $this->u_access->module('tools_users/view');
		$page = $this->input->post('tbxPage');
		$id = $this->input->post('tbxIDSelf');
		$password = $this->input->post('tbxPasswordSelf');
		$hash_password = password_hash($password, PASSWORD_DEFAULT);
		if($password != ''){
			$data_array = array('password' => $hash_password);
			$this->m_users->_update($id, $data_array);
		}
		redirect($page);
	}
}
?>