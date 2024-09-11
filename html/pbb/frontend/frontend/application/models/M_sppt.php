<?php
class m_sppt extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->database();
	}

    function getByUser($id){
        $this->db->select("*,users_sppt.id as id_sppt, users_sppt.status as sppt_status, users_sppt.created_date as created_date, users_sppt.tax as tax_value, users_usaha.name as usaha_name,pajak_type.name as pajak_type_name, pajak_type.code as pajak_type_code");
        $this->db->from('users_sppt');
        $this->db->join('pajak_type','pajak_type.id = users_sppt.id_pajak_type');
        $this->db->join('users_usaha','users_usaha.id = users_sppt.id_usaha','left');
        $this->db->join('users_sppt_pbb','users_sppt_pbb.id = users_sppt.id_pbb','left');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    


}
	
	