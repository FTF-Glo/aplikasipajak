<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Parameter extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->u_access->usersessions('auth');
	}
	
    public function hiburan(){ 
		$this->u_access->module('parameter/hiburan');
	    $data['main'] = $this->m_db->get('pajak_hiburan_type');
	    $data['omzet'] = $this->m_db->get('pajak_hiburan_omzet');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/hiburan_type', $data);
		$this->load->view('themes/main-bot');
	}
	
    public function hiburan_act(){
	    $this->u_access->module('parameter/hiburan');
	    $id = $this->input->post('txID');
	    $title = $this->input->post('txTitle');
	    $tax = $this->fun->getPrepNumberFloat($this->input->post('txTax'));
	    $array = [
	                'title' => $title,
	                'tax' => $tax
                 ];
        if($id > 0){
            $this->m_db->_update('pajak_hiburan_type',$id,$array);
            $this->fun->alert('edit',"ID: ".$id);
        } else{
            $this->m_db->_insert('pajak_hiburan_type',$array);
            $this->fun->alert('add',$title);

        }
	    redirect('parameter/hiburan');
	}
    public function hiburan_del(){
        $this->u_access->module('parameter/hiburan');
	    $id = $this->input->post('tbxID');
	    $this->m_db->_delete('pajak_hiburan_type',$id);
	    $this->fun->alert('del',$id);
	    redirect('parameter/hiburan');
	}
	public function hiburan_range($id){
        $this->u_access->module('parameter/hiburan');
        $data['parent'] = 'parameter/hiburan';
        $data['type'] = $this->m_db->get_where_col('pajak_hiburan_type','id',$id);
        $data['range'] = $this->m_db->get_where_col('pajak_hiburan_omzet','id_hiburan',$id);
        $this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/hiburan_range', $data);
		$this->load->view('themes/main-bot');
	}
	
	public function hiburan_range_act(){
        $this->u_access->module('parameter/hiburan');
        $id = (int)$this->input->post('txID');
        $type = (int)$this->input->post('txTypeID');
        $range1 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange1'));
	    $range2 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange2'));
	    $nilai = $this->fun->getPrepNumberFloat($this->input->post('tbxNilai'));
        $data_array = [
            'id_hiburan' => $type,
            'range_min' => $range1,
            'range_max' => $range2,
            'nilai' => $nilai     
          ];
        if($id > 0) {
			$this->m_db->_update('pajak_hiburan_omzet', $id, $data_array);
			$this->fun->alert('edit',$range1.'-'.$range2);
		}else {
			$this->m_db->_insert('pajak_hiburan_omzet', $data_array);
			$this->fun->alert('add',$range1.'-'.$range2);
		}
		redirect('parameter/hiburan_range/'.$type);
	}
	
	public function hiburan_range_del(){
        $this->u_access->module('parameter/hiburan');
	    $id = $this->input->post('tbxID');
	    $type = (int)$this->input->post('txTypeID');
	    $this->m_db->_delete('pajak_hiburan_omzet',$id);
	    $this->fun->alert('del',$id);
	    redirect('parameter/hiburan_range/'.$type);
	}
	
    public function minerba(){ 
		$this->u_access->module('parameter/minerba');
	    $data['main'] = $this->m_db->get('pajak_minerba_type');
	    $data['use'] = $this->m_db->get('pajak_minerba');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/minerba_type', $data);
		$this->load->view('themes/main-bot');
	}
	
    public function minerba_act(){
	    $this->u_access->module('parameter/minerba');
	    $id = $this->input->post('txID');
	    $title = $this->input->post('txTitle');
	    $array = [
	                'title' => $title
                 ];
        if($id > 0){
            $this->m_db->_update('pajak_minerba_type',$id,$array);
            $this->fun->alert('edit',"ID: ".$id);
        } else{
            $this->m_db->_insert('pajak_minerba_type',$array);
            $this->fun->alert('add',$title);

        }
	    redirect('parameter/minerba');
	}
    public function minerba_del(){
        $this->u_access->module('parameter/minerba');
	    $id = $this->input->post('tbxID');
	    $this->m_db->_delete('pajak_minerba_type',$id);
	    $this->fun->alert('del',$id);
	    redirect('parameter/minerba');
	}
	
    public function minerba_range($id){
        $this->u_access->module('parameter/minerba');
        $data['parent'] = 'parameter/minerba';
        $data['type'] = $this->m_db->get_where_col('pajak_minerba_type','id',$id);
        $data['range'] = $this->m_db->get_where_col('pajak_minerba','id_type',$id);
        $this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/minerba_range', $data);
		$this->load->view('themes/main-bot');
	}
	
    public function minerba_range_act(){
        $this->u_access->module('parameter/minerba');
        $id = (int)$this->input->post('txID');
        $type = (int)$this->input->post('txTypeID');
        $range1 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange1'));
	    $range2 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange2'));
	    $nilai = $this->fun->getPrepNumberFloat($this->input->post('tbxNilai'));
        $data_array = [
            'id_type' => $type,
            'ranges' => $range1.','.$range2,
            'nilai' => $nilai     
          ];
        if($id > 0) {
			$this->m_db->_update('pajak_minerba', $id, $data_array);
			$this->fun->alert('edit',$range1.'-'.$range2);
		}else {
			$this->m_db->_insert('pajak_minerba', $data_array);
			$this->fun->alert('add',$range1.'-'.$range2);
		}
		redirect('parameter/minerba_range/'.$type);
	}
	
	public function minerba_range_del(){
        $this->u_access->module('parameter/hiburan');
	    $id = $this->input->post('tbxID');
	    $type = (int)$this->input->post('txTypeID');
	    $this->m_db->_delete('pajak_minerba',$id);
	    $this->fun->alert('del',$id);
	    redirect('parameter/minerba_range/'.$type);
	}
	
	public function airtanah(){ 
		$this->u_access->module('master/airtanah');
		$data['main'] = $this->m_db->get('usaha_class');
		$data['use'] = $this->m_db->get('pajak_airtanah');
		$this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/airtanah_type', $data);
		$this->load->view('themes/main-bot');
	}
	public function airtanah_range($id){
        $this->u_access->module('parameter/airtanah');
        $data['parent'] = 'parameter/airtanah';
        $data['type'] = $this->m_db->get_where_col('usaha_class','id',$id);
        $data['range'] = $this->m_db->get_where_col('pajak_airtanah','id_class',$id);
        $this->load->view('themes/main-top');
		$this->load->view('themes/main-menu');
		$this->load->view('parameter/airtanah_range', $data);
		$this->load->view('themes/main-bot');
	}
	
	public function airtanah_range_act(){
        $this->u_access->module('parameter/airtanah');
        $id = (int)$this->input->post('txID');
        $type = (int)$this->input->post('txTypeID');
        $range1 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange1'));
	    $range2 = $this->fun->getPrepNumberFloat($this->input->post('tbxRange2'));
	    $nilai = $this->fun->getPrepNumberFloat($this->input->post('tbxNilai'));
        $data_array = [
            'id_class' => $type,
            'ranges' => $range1.','.$range2,
            'nilai' => $nilai     
          ];
        if($id > 0) {
			$this->m_db->_update('pajak_airtanah', $id, $data_array);
			$this->fun->alert('edit',$range1.'-'.$range2);
		}else {
			$this->m_db->_insert('pajak_airtanah', $data_array);
			$this->fun->alert('add',$range1.'-'.$range2);
		}
		redirect('parameter/airtanah_range/'.$type);
	}
	
	public function airtanah_range_del(){
        $this->u_access->module('parameter/airtanah');
	    $id = $this->input->post('tbxID');
	    $type = (int)$this->input->post('txTypeID');
	    $this->m_db->_delete('pajak_airtanah',$id);
	    $this->fun->alert('del',$id);
	    redirect('parameter/airtanah_range/'.$type);
	}
	
}
?>