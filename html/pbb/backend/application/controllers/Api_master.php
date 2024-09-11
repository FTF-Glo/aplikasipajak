<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api_master extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
	
	
	function getProvID($id){
        $data = $this->m_db->get_where('place_prov',$id);
		echo json_encode($data);
	}
	function getKabID($id){
        $data = $this->m_db->get_where('place_kab',$id);
		echo json_encode($data);
	}
	function getKecID($id){
        $data = $this->m_db->get_where('place_kec',$id);
		echo json_encode($data);
	}
	function getKelID($id){
        $data = $this->m_db->get_where('place_kel',$id);
		echo json_encode($data);
	}
    function getInfoID($id){
        $data = $this->m_db->get_where('pajak_info',$id);
		echo json_encode($data);
    }
    function getBadanUsahaID($id){
        $data = $this->m_db->get_where('badan_usaha',$id);
		echo json_encode($data);
    }
    function getKelasUsahaID($id){
        $data = $this->m_db->get_where('usaha_class',$id);
		echo json_encode($data);
    }
    function getUsahaID($id){
        $data = $this->m_db->get_where('usaha',$id);
		echo json_encode($data);
    }
    function getPajakTypeID($id){
        $data = $this->m_db->get_where('pajak_type',$id);
		echo json_encode($data);
    }
    
	
	function getPajakHiburanTypeID($id){
        $data = $this->m_db->get_where('pajak_hiburan_type',$id);
		echo json_encode($data);
	}
    function getPajakHiburanOmzetID($id){
        $data = $this->m_db->get_where('pajak_hiburan_omzet',$id);
		echo json_encode($data);
	}
    function getPajakMinerbaID($id){
        $data = $this->m_db->get_where('pajak_minerba',$id);
		echo json_encode($data);
	}
    function getPajakAirtanahID($id){
        $data = $this->m_db->get_where('pajak_airtanah',$id);
		echo json_encode($data);
	}
	
	
	public function getKab(){
        $idProv = $this->input->post('idProv');
        $data = $this->m_db->get_where_col('place_kab','id_prov',$idProv);
        echo json_encode($data);
    }
    public function getKec(){
        $idKab = $this->input->post('idKab');
        $data = $this->m_db->get_where_col('place_kec','id_kab',$idKab);
        echo json_encode($data);
    }
    public function getKel(){
        $idKec = $this->input->post('idKec');
        $data = $this->m_db->get_where_col('place_kel','id_kec',$idKec);
        echo json_encode($data);
    }
    public function getSppt($id){
        $data = $this->m_db->get_where_col('users_sppt','id',$id);
        echo json_encode($data);
    }
    
    
    function getPageID($id){
        $data = $this->m_db->get_where('page',$id);
		echo json_encode($data);
	}
    public function getUsersListActive(){
		$this->load->model('m_wp');
		$list = $this->m_wp->get_datatables();
		$data = array();
		$no = $_POST['start'];
		
		foreach ($list as $row) {    
            $no++;
            $rows = array();
            $rows[] = $row['id'];
            $rows[] = $row['ktp'];
            $rows[] = $row['fullname'];
            $rows[] = $row['email'];
            $rows[] = $row['created'];
            $rows[] = $row['last_login'];
            $data[] = $rows;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->m_wp->count_all(),
            "recordsFiltered" => $this->m_wp->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
	}
	
	public function getUsahaListActive(){
		$this->load->model('m_usaha');
		$list = $this->m_usaha->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $row) {    
            $no++;
            $rows = array();
            $rows[] = $row['id'];
            $rows[] = $row['usaha_name'];
            $rows[] = $row['badan_name'];
            $rows[] = $row['npwpd'];
            $rows[] = $row['phone'];
            $rows[] = $row['prov'];
            $rows[] = $row['kab'];
            $rows[] = $row['kec'];
            $rows[] = $row['kel'];
            $rows[] = $row['last_payment'];
        
            $data[] = $rows;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->m_usaha->count_all(),
            "recordsFiltered" => $this->m_usaha->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
	}
 
	
}
?>