<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	public function index(){
		$data['active_year'] = $this->getActiveYear();
		$data['main'] = $this->sum();
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('main', $data);
		$this->load->view('themes/main-bot');
	}
	
	public function year($year=""){
		$data['active_year'] = $this->getActiveYear();
		$data['main'] = $this->sum($year);
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('main', $data);
		$this->load->view('themes/main-bot');
	}
	
	public function getActiveYear(){
		$data = $this->m_db->get('users_sppt','created_date');
		if(count($data) != 0){
			$last = end($data);
			$start = $data[0];
			$end_year = substr($start['created_date'],0,4);
			$start_year = substr($last['created_date'],0,4);
			for($i=$start_year; $i<=$end_year; $i++){
				$year[] = $i;
			}
		}else{
			$year = "";
		}
	
		return $year;
	}
	
	public function sum($year=""){
		$this->load->model('m_sppt');
		$type = $this->m_db->get('pajak_type');
		if($year == ""){
			$year = date('Y');
		}
		$akumulasi_pendapatan = 0;
		$total_target = 0;
		foreach($type as $row){
			$data = $this->m_sppt->getSumTypeYear($row['id'],$year); 
			$target = $this->m_db->get_where_col_2('pajak_target','id_pajak_type',$row['id'],'year',$year);
			
			$total_pendapatan = $data->tax + $data->fine;
			if(count($target) != 0){
				$target_value = $target[0]['value'];
				if($target[0]['value'] == 0 || $total_pendapatan==0){
					$percent = 0;
				}else{
					$percent = $this->fun->getPercent($total_pendapatan, $target[0]['value']);
				}
				$total_target += $target[0]['value'];
			}else{
				$percent = 0;
				$total_target = 0;
				$target_value = 0;
			}
			
			$akumulasi_pendapatan += $total_pendapatan;
			$array['pajak'][] = [
						'jenis_pajak' => $row['name'],
						'total_pajak' => $data->tax,
						'total_denda' => $data->fine,
						'total_pendapatan'=> $total_pendapatan,
						'target' => $target_value,
						'pencapaian' => $percent
					   ]; 
					   
		}
		$percent_pencapaian_target = 0;
		if($total_target == 0 || $akumulasi_pendapatan == 0){
			$percent_pencapaian_target = 0;
		}else{
			$percent_pencapaian_target = $this->fun->getPercent($akumulasi_pendapatan, $total_target);
		}
		$array['total'][] = [
								'pendapatan' => $akumulasi_pendapatan,
								'target' => $total_target,
								'pencapaian' => $percent_pencapaian_target,
								'year' => $year
							];
		return $array;
	}
}
?>