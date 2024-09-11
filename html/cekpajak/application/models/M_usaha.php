<?php
class m_usaha extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}

    
    function getByUser($id){
        $this->db->select("*,users_usaha.id as id_users_usaha, users_usaha.name as name,badan_usaha.name as badan_usaha_name");
        $this->db->from('users_usaha');
        $this->db->join('badan_usaha','badan_usaha.id=users_usaha.id_badan_usaha');
        $query = $this->db->get();
        return $query->result_array();
    }
    function getByUserActive($id){
        $this->db->select("*,users_usaha.id as id_users_usaha, users_usaha.name as name,badan_usaha.name as badan_usaha_name");
        $this->db->from('users_usaha');
        $this->db->join('badan_usaha','badan_usaha.id=users_usaha.id_badan_usaha');
        $this->db->where('users_usaha.status','1');
        $this->db->where('users_usaha.id_users',$id);
        $query = $this->db->get();
        return $query->result_array();
    }
    function getByUserPending($id){
        $this->db->select("*,users_usaha.id as id_users_usaha, users_usaha.name as name,badan_usaha.name as badan_usaha_name");
        $this->db->from('users_usaha');
        $this->db->join('badan_usaha','badan_usaha.id=users_usaha.id_badan_usaha');
        $this->db->where('users_usaha.status','0');
        $this->db->where('users_usaha.id_users',$id);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getUserObjekPajak($id_users_usaha){
        $this->db->select("*,users_objek_pajak.id as id_objek_pajak");
        $this->db->from('users_objek_pajak');
        $this->db->join('pajak_type','pajak_type.id = users_objek_pajak.id_pajak_type');
        $this->db->where('users_objek_pajak.id_users_usaha', $id_users_usaha);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getSpptList($id_user_usaha){
        $this->db->select("*,pajak_type.name as pajak_type_name, pajak_type.code as pajak_type_code, users_sppt.id as id_sppt, users_sppt.status as sppt_status, users_sppt.tax as tax_value");
        $this->db->from('users_sppt');
        $this->db->join('pajak_type','pajak_type.id = users_sppt.id_pajak_type');
        $this->db->where('users_sppt.id_usaha',$id_user_usaha);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getSpptDetail($id){
        $this->db->select("*, users_sppt.id as id_sppt, users_sppt.tax as tax_value, users_sppt.created_date as created_date, users_sppt.fine as fine_value, users_usaha.name as name_usaha, pajak_type.name as name_pajak_type");
        $this->db->from('users_sppt');
        $this->db->join('users_usaha','users_usaha.id = users_sppt.id_usaha','left');
        $this->db->join('pajak_type', 'pajak_type.id = users_sppt.id_pajak_type');
        $this->db->join('users', 'users.users_id = users_sppt.id_users');
        $this->db->where('users_sppt.id',$id);
        $query = $this->db->get();
        return $query->result_array();
    }
}
	
	