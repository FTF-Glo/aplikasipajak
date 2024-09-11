<?php
class m_db extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	function getAll($table){
		$query=$this->db->get($table);
		return $query->result_array();
	}
	
	function get($table, $order_by="id"){
		$this->db->order_by($order_by,'asc');
		$query=$this->db->get($table);
		return $query->result_array();
	}	
	
	function get_where($table, $id){
		$this->db->where('id', $id);
		$query=$this->db->get($table);
		return $query->result_array();
	}
	
	function get_where_col($table,$col,$value,$order_by = "id"){
		$this->db->order_by($order_by, 'asc');
		$this->db->where($col, $value);
		$query=$this->db->get($table);
		return $query->result_array();
	}
	
	function get_where_col_2($table,$col_1,$value_1, $col_2, $value_2, $order_by='id'){
		$this->db->order_by($order_by, 'asc');
		$this->db->where($col_1, $value_1);
		$this->db->where($col_2, $value_2);
		$query = $this->db->get($table);
		return $query->result_array();
	}
	function get_where_col_like($table,$col_1,$value_1, $like_col, $like_val, $order_by){
		$this->db->order_by($order_by, 'asc');
		$this->db->where($col_1, $value_1);
		$this->db->like($like_col, $like_val);
		$this->db->count_all();
		$query = $this->db->get($table);
		return $query->result_array();
	}
	

	
	function get_like($table, $like_col, $like_val, $order_by){
		$this->db->order_by($order_by, 'asc');
		$this->db->like($like_col, $like_val);
		$query = $this->db->get($table);
		return $query->result_array();
	}
	
	function get_count($table){
		$query = $this->db->get($table);
		return $query->num_rows();
	}
	
	function get_count_where($table,$col,$value){
		$this->db->where($col,$value);
		$query = $this->db->get($table);
		return $query->num_rows();
	}
	
	function get_count_where_col2($table,$col,$value,$col1, $value1){
		$this->db->where($col,$value);
		$this->db->where($col1, $value1);
		$query = $this->db->get($table);
		return $query->num_rows();
	}
	
	function _insert($table, $data){
		$this->db->insert($table, $data);
	}

	function _update($table, $id, $data){
		$this->db->where('id', $id);
		$this->db->update($table, $data);
	}
	
	function _update_where($table, $col, $id, $data){
		$this->db->where($col, $id);
		$this->db->update($table, $data);
	}
	function _update_where_col2($table, $col, $val,$col2,$val2, $data){
		$this->db->where($col, $val);
		$this->db->where($col2, $val2);
		$this->db->update($table, $data);
	}


	function _delete($table, $id){
		$this->db->where('id', $id);
		$this->db->delete($table);
	}	
	function _delete_where($table,$col,$value){
		$this->db->where($col, $value);
		$this->db->delete($table);
	}
	

	
}
?>