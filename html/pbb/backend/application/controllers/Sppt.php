<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Sppt extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	function verification(){
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('sppt/verification');
		$this->load->view('themes/main-bot');
	}
	
	public function verification_detail($id){
		
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('sppt/verification_detail');
		$this->load->view('themes/main-bot');
	}
	
	function done(){
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('sppt/done');
		$this->load->view('themes/main-bot');
	}
	
		
	function pending(){
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('sppt/pending');
		$this->load->view('themes/main-bot');
	}
	function reject(){
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('sppt/reject');
		$this->load->view('themes/main-bot');
	}
	
	
	public function verification_act(){
		$id = $this->input->post('txID');
		$status = $this->input->post('txStatus');
		$array = ['status' => $status];
		$this->m_db->_update('users_sppt',$id,$array);
		redirect('sppt/verification');
	}

	
	public function getSPPTList($status = 0){
		$this->load->model('m_sppt');
		$list = $this->m_sppt->get_datatables($status);
		$data = array();
		$no = $_POST['start'];
		if($status == 0){
			foreach ($list as $row) {    
	            $no++;
	            $rows = array();
	            $rows[] = $row['id_sppt'];
	            $rows[] = $row['created_date'];
	            $rows[] = $row['masa_date'];
	            $rows[] = $row['usaha_name'];
	            $rows[] = $row['pajak_type'];
	            $rows[] = $this->format->currency($row['tax']+$row['fine']);
	            $data[] = $rows;
	        }
		}else{
			foreach ($list as $row) {    
	            $no++;
	            $rows = array();
	            $rows[] = $row['id_sppt'];
	            $rows[] = $row['created_date'];
	            $rows[] = $row['masa_date'];
	            $rows[] = $row['usaha_name'];
	            $rows[] = $row['pajak_type'];
	            $rows[] = $this->format->currency($row['tax']+$row['fine']);
	            $data[] = $rows;
	        }
		}
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->m_sppt->count_all($status),
            "recordsFiltered" => $this->m_sppt->count_filtered($status),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
	}
	
	public function getSPPTListVerification($status = 0){
		$this->load->model('m_sppt');
		$list = $this->m_sppt->get_datatables($status);
		$data = array();
		$no = $_POST['start'];
		if($status == 0){
			foreach ($list as $row) {    
	            $no++;
	            $total_pajak = $this->format->currency($row['tax']+$row['fine']);
	            $rows = array();
	            $rows[] = $row['id_sppt'];
	            $rows[] = $row['created_date'];
	            $rows[] = $row['masa_date'];
	            $rows[] = $row['usaha_name'];
	            $rows[] = $row['pajak_type'];
	            $rows[] = $total_pajak;
	            $rows[] = '
	                        <button class="btn btn-icon btn-status" data-status="1" data-id="'.$row['id_sppt'].'" data-usaha="'.$row['usaha_name'].'" data-type="'.$row['pajak_type'].'" data-date="'.$row['created_date'].'" data-total="'.$total_pajak.'" data-masa="'.$row['masa_date'].'"><i class="fa fa-check"></i></button>
	                        <button class="btn btn-icon btn-status" data-status="2" data-id="'.$row['id_sppt'].'" data-usaha="'.$row['usaha_name'].'" data-type="'.$row['pajak_type'].'" data-date="'.$row['created_date'].'" data-total="'.$total_pajak.'"  data-masa="'.$row['masa_date'].'"><i class="fa fa-times"></i></button>
	                      ';
	            $data[] = $rows;
	        }
		}
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->m_sppt->count_all($status),
            "recordsFiltered" => $this->m_sppt->count_filtered($status),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
	}
	public function getSPPTListReject($status = 0){
		$this->load->model('m_sppt');
		$list = $this->m_sppt->get_datatables($status);
		$data = array();
		$no = $_POST['start'];
		if($status == 0){
			foreach ($list as $row) {    
	            $no++;
	            $total_pajak = $this->format->currency($row['tax']+$row['fine']);
	            $rows = array();
	            $rows[] = $row['id_sppt'];
	            $rows[] = $row['created_date'];
	            $rows[] = $row['masa_date'];
	            $rows[] = $row['usaha_name'];
	            $rows[] = $row['pajak_type'];
	            $rows[] = $total_pajak;
	            $rows[] = '
	                        <button class="btn btn-icon btn-status" data-status="1" data-id="'.$row['id_sppt'].'" data-usaha="'.$row['usaha_name'].'" data-type="'.$row['pajak_type'].'" data-date="'.$row['created_date'].'" data-total="'.$total_pajak.'" data-masa="'.$row['masa_date'].'"><i class="fa fa-check"></i></button>
	                      ';
	            $data[] = $rows;
	        }
		}
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->m_sppt->count_all($status),
            "recordsFiltered" => $this->m_sppt->count_filtered($status),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
	}
    
}
?>