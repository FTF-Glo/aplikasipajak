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
    
    public function calc_nilai($data, $use, $tax){
        $value = '0';
        foreach($data as $row){
            $range = explode(',',$row['ranges']);
            if($range[1] != ""){
                if($range[0] <= $use && $range[1] >= $use){
                    $value = $row['nilai'];
                }
            } else {
                if($range[0] <= $use){
                    $value = $row['nilai'];
                }
            }
            
        }
        $result = $value * $use * ($tax/100);
        return $result;
    }
    
    public function getMenuCat($acc = ""){
        $CI =& get_instance();
        $CI->load->model('m_menu');
		$menu_cat = $CI->m_menu->get_category();
		
		foreach($menu_cat as $row){
		    $menu = $CI->m_menu->get_menu_where($row['id']);
            $data_array[] = [
                'id' => $row['id'],
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
	
    function api($mod,$post){
        $ch = curl_init($this->getConfig('api_url').$mod);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response,true);
    }	
    
    function getArray($array){
        $key = ['keyx'=>$this->getConfig('api_key')];
        $result = array_merge($key,$array);
        return $result;
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
	
	public function alert($act,$val=""){
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
	        case 'cancel':
                return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> berhasil dibatalkan!</div>');
                break;
            case 'pay' :
                return $CI->session->set_flashdata('item','<div class="alert alert-success mb-2" role="alert">Data <strong>'.$val.'</strong> telah terbayar !</div>');
                break;
	        case 'error':
	            return $CI->session->set_flashdata('item','<div class="alert alert-warning mb-2" role="alert">Gagal !</div>');
                break;
            case 'success_val_loc':
                return $CI->session->set_flashdata('item','<div class="alert alert-warning mb-2" role="alert">Lokasi NOP:'.$val.' berhasil tersimpan !</div>');
            break;
            case 'error_val_loc':
                return $CI->session->set_flashdata('item','<div class="alert alert-warning mb-2" role="alert">Lokasi NOP:'.$val.' gagal tersimpan silahkan ulangi kembali atau hubungi otoritas terkait !</div>');
            break;
            case 'ready_loc':
                return $CI->session->set_flashdata('item','<div class="alert alert-warning mb-2" role="alert">Lokasi NOP:'.$val.' sudah terdaftar sebelumnya</div>');
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
    
    public function getPercent($value1, $value2){
        $result = (($value1/$value2) * 100);
        return ceil($result);
        
    }
    
    public function getRangeMonth($period,$total){
        for($i=0; $total > $i; $i++){
            $array[] = date("Y-m",strtotime($period.' -'.$i.' Month'));
        }
        return $array;
    }
    
    public function endDate($period,$start,$masa){
        $startDate = strtotime($start);
        switch($period){
            case 'tahun':
                $result = date('Y-m-d', strtotime("+".$masa." year", $startDate));
            break;
            case 'bulan':
                $result = date('Y-m-d', strtotime("+".$masa." month", $startDate));
            break;
            case 'minggu':
                $result = date('Y-m-d', strtotime("+".$masa." week", $startDate));
            break;
            case 'hari':
                $result = date('Y-m-d', strtotime("+".$masa." day", $startDate));
            break;
        }
        
        return $result;
    }
    
    public function checkExpired($expDate){
        $today = date('Y-m-d');
        $today = str_replace('-',"",$today);
        $expdate = str_replace('-',"",$expDate);
        if($expdate < $today){
            return true;
        }else{
            return false;
        }
    }
    
    public function flashdata(){
        $CI =& get_instance();
        $CI->load->library('session');
        if($CI->session->flashdata('item') != null){
            echo $CI->session->flashdata('item');
        }
    }
    
    public function getLocName($loc,$loc_id){
        $CI =& get_instance();
        $CI->load->model('m_db');
        $data = $CI->m_db->get_where($loc,$loc_id);
        return $data['name'];
        
    }

    public function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    public function select_opt($DtArray,$addname=null){
        $return = '';
        foreach ($DtArray as $key) {
            $arrypnjbr = [];
            foreach ($key as $name) {
                $arrypnjbr[] = $name;
            }
            // die(var_dump($arrypnjbr));
            if (is_null($addname)) {
                $return .= "<option value='$arrypnjbr[0]'>$arrypnjbr[1]</option>";
            }
            else{
                $return .= "<option value='$arrypnjbr[0]'>$arrypnjbr[1] $arrypnjbr[2]</option>";
            }
        }
        return $return;
    }
}