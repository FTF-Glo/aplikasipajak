<?php
class m_menu extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}


	
	function get_menu(){
		$table = 'menu';
		$this->db->order_by('sort','asc');
		$query=$this->db->get($table);
		return $query->result_array();
	}	
	function get_menu_access($id,$module=""){
	    $this->db->from("admin_access");
        $this->db->join('menu','admin_access.id_menu = menu.id');
        $this->db->where("admin_access.id_admin", $id);
		if($module != ""){
            $this->db->where("menu.module", $module);
		}	
        $query =$this->db->get();
        return $query->result_array();
        
    }
    function get_category(){
		$table = 'menu_categories';
		$this->db->order_by('sort','asc');
		$query=$this->db->get($table);
		return $query->result_array();
	}	
	
	function get_menu_where($cat){
		$table = 'menu';
		$this->db->where('menu_categories_id', $cat);
		$this->db->order_by('sort','asc');
		$query=$this->db->get($table);
		return $query->result_array();
	}
	
	function get_where_col($col,$value){
		$table = $this->get_table();
		$this->db->where($col, $value);
		$query=$this->db->get($table);
		return $query->result_array();
	}
	
	function _insert($data){
		$table = $this->get_table();
		$this->db->insert($table, $data);
	}

	function _update($id, $data){
		$table = $this->get_table();
		$this->db->where('id', $id);
		$this->db->update($table, $data);
	}

	function _delete($id){
		$table = $this->get_table();
		$this->db->where('id', $id);
		$this->db->delete($table);
	}	
	

}
?>