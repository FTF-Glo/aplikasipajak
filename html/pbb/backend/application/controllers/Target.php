<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Target extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	public function listing(){
	    $data['target'] = $this->m_db->get('pajak_target');
	    $data['type'] = $this->m_db->get('pajak_type');
        $this->load->view('themes/main-top');
        $this->load->view('themes/main-menu');
        $this->load->view('target/list', $data);
        $this->load->view('themes/main-bot');
	}
	
	public function penetapan($year=""){
        $this->u_access->module('master/info');
        if($year == ""){
            $year = date('Y');
        }
        $target = $this->m_db->get_where_col('pajak_target','year',$year);
        $pajak_type = $this->m_db->get('pajak_type');
        $data['target'] = $target;
        $data['type'] = $pajak_type;
        $data['year'] = $year;
        $this->load->view('themes/main-top');
        $this->load->view('themes/main-menu');
        $this->load->view('target/penetapan', $data);
        $this->load->view('themes/main-bot');
	}
	
	public function save(){
	    $year = $this->input->post('txYear');
	    $check_data = $this->m_db->get_where_col('pajak_target','year',$year);
	    $type = $this->m_db->get('pajak_type');
        foreach($type as $row){
            $array = [
                        'id_pajak_type' => $row['id'],
                        'year' => $year,
                        'value' => (int)$this->input->post('txType'.$row['id']),
                     ];
            if(count($check_data) == 0){
                $this->m_db->_insert('pajak_target',$array);
            }else{
                $this->m_db->_update_where_col2('pajak_target','id_pajak_type',$row['id'],'year',$year,$array);
            }
        }
        $this->fun->alert('save','Target Pajak '.$year.' ');
        if($year == date('Y')){
            redirect('target/penetapan');
        }else{
            redirect('target/penetapan/'.$year);
        }
	}

}
?>