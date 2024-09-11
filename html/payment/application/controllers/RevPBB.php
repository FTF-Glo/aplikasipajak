<?php

defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'Token.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class RevPBB extends Token {

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        $this->pbb = $this->load->database('pbb', TRUE);
    }

    public function index_post()
    {

        $tax_year = $this->post('tax_year');
        $tax_type_belakang =  $this->post('tax_type',2);
        $id = $this->post('nop');
        $tax_type =  substr($tax_type_belakang,2);
        $kdbelakang = substr($id,-2);

        $refnum = $this->post("refnum");
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);

        $kdbelakang     = substr($id,-2);
        $kddepan        = substr($id,0,-2);
        $kdbillingfull  = $kddepan .$kdbelakang;
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        if(empty($tax_type || $area_code || $id || $refnum)){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        // var_dump($tax_year,$tax_type,$id,$refnum);exit;

        // if($ids != 1){
        //     return $this->simple('02');
        // }
        if ($tax_type <> 02) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        // if($panjangkode >= 18){
        //     return $this->simple('02');
        // }

        if ($this->authtoken() == 'benar'){
            if ($tax_type == 02) {
            $inquiry = $this->datareversalbniPBB($tax_year,$tax_type,$id,$refnum);
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.' refnum: '.$refnum.')');
            }else{
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
                return $this->response(array('response_code' => '13', 'message' => 'Tagihan Tidak Sesuai'), '401');
            }
        }else{
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.' refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
        }

        if($inquiry['status_bayar'] == 'BELUM LUNAS'){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.' refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Belum Lunas')).' ');
            return $this->response(array('response_code'=>'88', 'message'=>'Tagihan Belum Lunas'), '401');
        }

        if($inquiry['refnum'] <> $refnum){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code'=>'14', 'message'=>'ID Tidak Terdaftar'), '401');
        }
        $users = $this->getBank($this->username);
        $params = array(
            'payment_ref_number' => $refnum,
        );
        
        if ($this->authtoken() == 'benar'){
            $inquiry = $this->insertreversalPBB($inquiry, $params);
            $inquiry = $this->reversalPBB($inquiry, $params);
        }else{
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.' refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
        }

        if($inquiry == true){
            return $this->response(array('response_code'=>'00', 'message'=>'SUCCESS'), '00');
        }else{
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('14', 'ID Tidak Terdaftar');
        }

    }

}

/** REFACTORED BY Ridwan */