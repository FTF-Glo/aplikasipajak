<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class U_access{
	public function usersessions($page){
		if(!isset($_SESSION['user_id'])){
			redirect($page);
		} 
	}
	
	public function module($menu_module){
		$CI =& get_instance();
		$CI->load->model('m_menu');
		if($_SESSION['user_priv'] != 1){
			$id = $_SESSION['user_id'];
			$menu = $CI->m_menu->get_menu_access($id,$menu_module);
			if(count($menu) == 0){
				redirect('');
			}
		}
	}
	
	function getMenu(){
		$CI =& get_instance();
		$CI->load->model('m_menu');
		$menu_cat = $CI->m_menu->get_category();
		$id = $_SESSION['user_id'];
		$priv = $_SESSION['user_priv'];
		//echo json_encode($menu_cat);
		$menu = $CI->m_menu->get_menu_access($id);
		foreach($menu_cat as $row){
			if($priv == '1'){
				$menu = $CI->m_menu->get_menu_where($row['id']);
				$data_array[] = [
									'id' => $row['id'],
 									'name' => $row['name'],
									'sub' => ((count($menu)==0)?'0':'1'),	
									'icon' => $row['icon'],
									'sub_menu' => $menu
								];
			} else {
				$no = 0;
				foreach($menu as $mn){
					if($mn['menu_categories_id'] == $row['id']){
						$menu_array[] = $mn;
						$no++;
					}
				}
				
				if($no > 0){
					$data_array[] = [
						'id' => $row['id'],
						'name' => $row['name'],
						'sub' => ((count($menu)==0)?'0':'1'),	
						'icon' => $row['icon'],
						'sub_menu' => $menu_array
					];
				}
				$menu_array = array();
			}
		}
		return $data_array;
	}
	
	function logout(){
		$this->session->unset_userdata('user_id');
		$this->session->unset_userdata('user_name');
		$this->session->unset_userdata('user_priv');
		$this->session->unset_userdata('user_fullname');
		redirect('login');
	}	
}