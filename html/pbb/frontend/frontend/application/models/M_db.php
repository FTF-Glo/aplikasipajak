<?php
class m_db extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	function get($table, $order_by="id"){
		$this->db->order_by($order_by,'asc');
		$query=$this->db->get($table);
		return $query->result_array();
	}	
    function get_where_id($table, $id, $order_by="id"){
		$this->db->where('id', $id);
		$query=$this->db->get($table);
		return $query->result_array();
	}
	function get_where_col($table,$col,$value,$order_by="id",$order="ASC"){
		$this->db->where($col,$value);
		$this->db->order_by($order_by,$order);
		$query=$this->db->get($table);
		return $query->result_array();
	}
	function get_where_col_2($table,$col1,$val1,$col2,$val2,$order_by="id",$order="ASC"){
		$this->db->where($col1,$val1);
		$this->db->where($col2,$val2);
		$this->db->order_by($order_by,$order);
		$query=$this->db->get($table);
		return $query->result_array();
	}

	function get_count_where($table,$col,$value){
		$this->db->where($col,$value);
		$query = $this->db->get($table);
		return $query->num_rows();
	}
	function _insert($table, $data){
		$this->db->insert($table, $data);
	}
	function _update_where($table, $col, $id, $data){
		$this->db->where($col, $id);
		$this->db->update($table, $data);
	}

}
	
	