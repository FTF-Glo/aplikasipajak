<?php
class m_usaha extends CI_Model {
    var $table = 'users_usaha';
    var $column_order = array('users_usaha.id',
                            'users_usaha.name',
                            'users_usaha.npwpd',
                            'users_usaha.phone',
                            'users_usaha.prov',
                            'users_usaha.kab',
                            'users_usaha.kel',
                            'users_usaha.kec',
                            'users_usaha.last_payment',
                            'badan_usaha.name'); //field yang ada di table user
    var $column_search = array('users_usaha.id',
                               'users_usaha.name',
                               'users_usaha.npwpd',
                               'users_usaha.phone',
                               'users_usaha.prov',
                               'users_usaha.kab',
                               'users_usaha.kel',
                               'users_usaha.kec',
                               'users_usaha.last_payment',
                               'badan_usaha.name'); //field yang diizin untuk pencarian 
    var $order = array('users_usaha.id' => 'desc'); // default order 
 
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }
 
    private function _get_datatables_query(){
		$this->db->select('
							users_usaha.id as id,
							users_usaha.name as usaha_name,
							users_usaha.npwpd,
							users_usaha.phone,
							users_usaha.prov,
							users_usaha.kab,
							users_usaha.kel,
							users_usaha.kec,
							users_usaha.last_payment,
							badan_usaha.name as badan_name
						  ');
        $this->db->from('users_usaha');
        $this->db->join('badan_usaha','badan_usaha.id = users_usaha.id_badan_usaha');
        
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

    function get_datatables(){
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result_array();
    }
 
    function count_filtered(){
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
 
    public function count_all(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
    
    
    public function getWhereActive(){
        $this->db->select("*, users_usaha.id as id_usaha, badan_usaha.name as badan_name, users_usaha.name as usaha_name");
		$this->db->where('users_usaha.status', '0');
		$this->db->join('badan_usaha','badan_usaha.id = users_usaha.id_badan_usaha');
		$this->db->join('users','users.users_id = users_usaha.id_users');
		$query=$this->db->get('users_usaha');
		return $query->result_array();
    }
    
    public function getWhereID($id){
        $this->db->select("*, users_usaha.id as id_usaha, badan_usaha.name as badan_name, users_usaha.name as usaha_name");
		$this->db->where('users_usaha.id', $id);
		$this->db->join('badan_usaha','badan_usaha.id = users_usaha.id_badan_usaha');
		$this->db->join('users','users.users_id = users_usaha.id_users');
		$query=$this->db->get('users_usaha');
		return $query->result_array();
    }
    
    
   
}


?>