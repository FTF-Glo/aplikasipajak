<?php
class m_users extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_table() {
		$table = "admin";
		return $table;
	}
	
	function get($order_by = "id", $order = "asc"){
		$table = $this->get_table();
		$this->db->order_by($order_by,$order);
		$this->db->where('is_admin','0');
		$query=$this->db->get($table);
		
		return $query->result_array();
	}	
	
	function get_where($id){
		$table = $this->get_table();
		$this->db->where('id', $id);
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
	function _insert_access($data){
		$table = "admin_access";
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
	function _delete_access($id){
		$table = "admin_access";
		$this->db->where('id_admin', $id);
		$this->db->delete($table);
	}
	
	
	function is_user_available($username){  
		  $table = $this->get_table();
           $this->db->where('username', $username);  
           $query = $this->db->get($table);  
           if($query->num_rows() > 0)  {  
                return true;  
           } else  {  
                return false;  
           }  
    } 
	function get_query_customize($value){
		$table = $this->get_table();
		$this->db->where('username', $value);
		$query = $this->db->get($table);
		return $query->result_array();
	}	

}
?>