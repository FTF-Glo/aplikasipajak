<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fun {
    public function get_value($data,$name){
        if(isset($data)){
            if(is_array($data) > 0){
                return $data[0][$name];
            }else {
                return '';
            }
        }
    }
    
    public function get_selected($val1, $val2){
        $result = "";
        if($val1 == $val2){
            $result = 'selected';
        }
        return $result;
    }
    
    public function alert_check_meteran($start, $end){
        $result = "";
        if($start > $end){
            $result .= ' <div class="alert alert-danger  mb-2" role="alert">
                            <span class="fa fa-danger"></span>
                            Meteran awal lebih besar dibanding kan meteran akhir !
                        </div>';
        }
        return $result;
    }
    
    public function check_date($start,$end){
        $start = substr($start,0,7);
        $end = substr($end,0,7);
       
        if($start != "0000-00"){
            if(strtotime($start) >= strtotime($end)){
                return false;
            
            }else {
                return true;
            }
        }else {
            return true;
        }
       
    }
    
    public function getStartEnd($period1, $period2){ //2019-04
        if($period1 == "" || $period2 == ""){
            $start = $period1.$period2;
            $end = $start;
        
        } else {
            if(strtotime($period1) < strtotime($period2)){
                $start = $period1;
                $end = $period2;
            } else {
                $start = $period2;
                $end = $period1;
            }
        }
        return [$start,$end];
    }
    
    public function alert_check_date($start,$end){
        $result = "";
        $start = substr($start,0,7);
        $end = substr($end,0,7);
        $start_year = substr($start,0,4);
        
        if($start_year == "0000"){
            $result .= ' <div class="alert alert-dark  mb-2" role="alert">
                            Data meteran awal belum tersedia!
                        </div>';
        }else {
            $diff = abs(strtotime($end) - strtotime($start));
            $years = floor($diff / (365*60*60*24)); 
            $month = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));  
            if($month != '1'){
                    $result .= ' <div class="alert alert-warning mb-2" role="alert">
                                 Terdapat selisih bulan berjalan !
                         </div>';
            }
        }
        return $result;
    }
    
    public function add_months($months, DateTime $dateObject) {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');
        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }

    public function endCycle($d1, $months){
        $date = new DateTime($d1);
        $newDate = $date->add($this->add_months($months, $date));
        $newDate->sub(new DateInterval('P1D')); 
        $dateReturned = $newDate->format('Y-m-d'); 
        return $dateReturned;
    }  
    
    public function set_last_date($start_date,$end_date){
        $year_start = substr($start_date,0,4);
        if($year_start != "0000"){ 
            $yearmonth = substr($start_date,0,7);
            $set_date = $this->endCycle($yearmonth,2);
        }else {
            $set_date = $end_date;
        }
        return $set_date;
    }
    
 
    public function getMenuCat($acc = ""){
        $CI =& get_instance();
        $CI->load->model('m_menu');
		$menu_cat = $CI->m_menu->get_category();
		
		foreach($menu_cat as $row){
		    $menu = $CI->m_menu->get_menu_where($row['id']);
            $data_array[] = [
                'name' => $row['name'],
                'sub' => ((count($menu)==0)?'0':'1'),	
                'icon' => $row['icon'],
                'sub_menu' => $menu
            ];
		}
		
		return $data_array;
    }
    
    public function getArrayMonth(){
	    $month = date('m');
	    for($i=(int)$month; $i > 0; $i--){
	        $result[] = sprintf("%02d",$i);
	    }
	    return $result;
	}
	
	public function getCurrentMonth(){
        $CI =& get_instance();
        $CI->load->library('format');
	    $period = date('Y-m');
	    return $CI->format->month_year($period);
	}
	
	public function getConfig($code){
        $CI =& get_instance();
        $CI->load->model('m_db');
		$menu_cat = $CI->m_db->get_where_col('config','code',$code);
		return $menu_cat[0]['value'];
	}
	
	public function getStatusValidasi($status){
	    switch ($status){
	        case 0:
	            return '<span class="badge badge-secondary">Belum Divalidasi</span>';
	            break;
	        case 1:
	            return '<span class="badge badge-success">Disetujui</span>';
	            break;
	        case 2: 
	            return '<span class="badge badge-warning">Revisi</span>';
	            break;
	        case 3:
	            return '<span class="badge badge-danger">Ditolak</span>';
	            break;
	    }
	}
	
	public function alert($act,$val){
        $CI =& get_instance();
        $CI->load->library('session');
        switch ($act){
	        case 'add':
	            return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> berhasil ditambahkan !</div>');
	            break;
	        case 'edit':
	            return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> berhasil diperbaharui !</div>');
	            break;
	        case 'del': 
	            return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> berhasil dihapus!</div>');
	            break;
            case 'save':
                return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> berhasil disimpan !</div>');
                break;
	    }
	    
	}
	
	public function valid_monthyear($date){
	    $date = strip_tags($date);
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/",$date)) {
            return true;
        } else {
            return false;
        }
	}
    
    public function getDistanceBetween($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km') { 
    	$theta = $longitude1 - $longitude2; 
    	$distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
    	$distance = acos($distance); 
    	$distance = rad2deg($distance); 
    	$distance = $distance * 60 * 1.1515; 
    	switch($unit) 
    	{ 
    		case 'Mi': break; 
    		case 'Km' : $distance = $distance * 1.609344; 
    	} 
    	return (round($distance,2)); 
    }
    
    public function getArrayMonthYear($total){ //get countdown month year $total month ago		
        for( $i = $total-1; $i > 0; $i--){
			$array[] = date('Y-m', strtotime('-'.$i.' months'));
		}
		$array[] = date('Y-m');
		return $array;
    }
    
    public function getDotExist($data){
        $result = "";
        foreach($data as $row){
            if($row == '0'){
                $result .= '<i class="fa fa-circle danger"></i> ';
            }else {
                $result .= '<i class="fa fa-circle success"></i> ';
            }
        }
        return $result;
    }
    
    public function getPercent($value1,$value2){
        $result = (($value1/$value2) * 100);
        return ceil($result);
        
    }
    
    public function getRangeMonth($period,$total){
        for($i=0; $total > $i; $i++){
            $array[] = date("Y-m",strtotime($period.' -'.$i.' Month'));
        }
        return $array;
    }
    
    public function getPrepNumberFloat($value){
        return preg_replace("/[^0-9.]/", "", $value);
    }
    public function getPrepCommaToDot($value){
        return str_replace(',','.',$value);
    }
    
    public function getCountValidasiUsaha(){
        $CI =& get_instance();
        $CI->load->model('m_db');
		$menu_cat = $CI->m_db->get_where_col('users_usaha','status','0');
		return count($menu_cat);
    }
    
    public function getCountVerificationSppt(){
        $CI =& get_instance();
        $CI->load->model('m_db');
		$menu_cat = $CI->m_db->get_where_col('users_sppt','status','0');
		return count($menu_cat);
    }
    

    
}