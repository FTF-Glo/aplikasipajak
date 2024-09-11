<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fun{
    public function calc_pajak_airtanah($data, $use, $tax){ //nilai pajak progresive berdasarkan pemakaian
        $value = '0';
        $take = '0';
        $value2 = '0';
        $status = true;
        foreach($data as $row){ 
            $range = explode(',',$row['ranges']);
            if($range[1] != ""){
                if($range[1] < $use){
                    $value += $row['nilai'] * $range[1];        
                    $take += $range[1];
                }else if($range[1] >= $use && $range[0] <= $use){ //berhenti saat sampai di range
                    $value += $row['nilai'] * ($use-$take); 
                    $status == false;
                }
            }else{
                if($range[0] <= $use && $status == true){ //setelah melewati range akhir / infinity
                    $value += $row['nilai'] * ($use-$take);
                }
               
            }
        }
        $result = $value * ($tax/100);
        return $result;
    }
    
    public function calc_pajak_airtanah_old($data, $use, $tax){ //nilai akhir langsung hitung berdasarkan range
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
    
    public function calc_pajak_minerba($data,$use,$tax){
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
    function alert_box($alert,$message){
        return '
                 <div class="alert alert-'.$alert.'">
                    '.$message.'
                  </div>
               ';
    }
    public function sendMail($fromMail,$fromMailName,$email,$subject,$message){
        $CI =& get_instance();
        $CI->load->library('email');
        $CI->email->from($fromMail, $fromMailName);
		$CI->email->to($email);
		$CI->email->subject($subject);
		$CI->email->message($message);
		$CI->email->set_mailtype("html");
		$CI->email->send();
    }
}