<?php

class m_sppt extends CI_Model {
    var $table = 'users_sppt';
    var $column_order = array('users_sppt.id',
                            'users_sppt.created_date',
                            'users_sppt.payment_date',
                            'users_sppt.token',
                            'pajak_type.name',
                            'users_usaha.name',
                            'users_usaha.id_badan_usaha'
                            ); //field yang ada di table user
    var $column_search = array('users_sppt.id',
                                'users_sppt.created_date',
                                'users_sppt.payment_date',
                                'users_sppt.token',
                                'pajak_type.name',
                                'users_usaha.name',
                                'users_usaha.id_badan_usaha'); //field yang diizin untuk pencarian 
    var $order = array('users_sppt.id' => 'desc'); // default order 
 
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }
 
    private function _get_datatables_query($status){
		$this->db->select('users_sppt.id as id_sppt,
                            users_sppt.created_date as created_date,
                            users_sppt.payment_date as payment_date,
                            users_sppt.token as token,
                            users_sppt.masa_date as masa_date,
                            pajak_type.name as pajak_type,
                            users_usaha.name as usaha_name,
                            users_usaha.id_badan_usaha as id_badan_usaha,
                            users_sppt.tax as tax,
                            users_sppt.fine as fine');
        $this->db->from('users_sppt');
        $this->db->join('users_usaha','users_usaha.id = users_sppt.id_usaha','left');
        $this->db->join('pajak_type','pajak_type.id = users_sppt.id_objek_pajak');
        $this->db->where('users_sppt.status',$status);
        
        $i = 0;
        foreach ($this->column_search as $item) {
            if($_POST['search']['value']) {
                if($i===0) {
                    $this->db->group_start(); 
                    $this->db->like($item, $_POST['search']['value']);
                }else{
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search) - 1 == $i) 
                    $this->db->group_end(); 
            }
            $i++;
        }
        
        if(isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if(isset($this->order)){
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($status){
        $this->_get_datatables_query($status);
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result_array();
    }
 
    function count_filtered($status){
        $this->_get_datatables_query($status);
        $query = $this->db->get();
        return $query->num_rows();
    }
 
    public function count_all(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
	
    function getSumTypeYear($type,$year){
        $table = 'users_sppt';
        $this->db->select_sum('tax');
        $this->db->select_sum('fine');
        $this->db->from($table);
        $this->db->like('payment_date',$year);
        $this->db->where('id_pajak_type',$type);
        $query = $this->db->get();
        return $query->row();
    }
	
	
	
	
}
?>