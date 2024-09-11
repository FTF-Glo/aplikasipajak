<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'Token.php';

// defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/JWT.php';
// require APPPATH . '/libraries/ExpiredException.php';
// require APPPATH . '/libraries/BeforeValidException.php';
// require APPPATH . '/libraries/SignatureInvalidException.php';

// // use namespace
use Restserver\Libraries\REST_Controller;

// // use Restserver\Libraries\REST_Controller;

// use chriskacerguis\RestServer\RestController;
// use \Firebase\JWT\JWT;
// use \Firebase\JWT\ExpiredException;



class Inq9Pajak extends Token {

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        $this->spajak = $this->load->database('default', TRUE);
        $this->gw_ssb = $this->load->database('dbphtb', TRUE);
        $this->sw_ssb = $this->load->database('sw_ssb', TRUE);
    }

    public function index_post()
    {
        $area_code = $this->post('area_code');
        $tax_type_belakang =  $this->post('tax_type',2);
        $id = $this->post('billing_code');
        $tax_type =  substr($tax_type_belakang,2);
        $kdbelakang = substr($id,-2);
        $kddepan = substr($id,0,-2);
        $ids = is_numeric($id);
        $panjangkode = strlen((string)$id);
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        // var_dump($area_code, $tax_type,$id);
        if(empty($tax_type && $area_code && $id)){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            // return $this->response(array('response_code'=>'01', 'message'=>'ID Tidak Terdaftar'), '01');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if ($ids != 1) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if($panjangkode >= 18){
            
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'02', 'message'=>'Hanya Boleh Diisi Angka')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        if($panjangkode <> 16){
            
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'02', 'message'=>'Hanya Boleh Diisi Angka')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }


        // var_dump($this->authtoken());exit;
        // if ($this->authtoken() == 'salah'|| $this->authtoken() == 'Authorization kosong'){
            //     return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
            // }
           
            if ($this->authtoken() == 'benar'){
                if ($area_code == '1808') {
                $inquiry = $this->inquirybniSPajak($area_code,$tax_type,$id);
                // log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode($inquiry).''); 
                }else{
                    log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
                    return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
                }
            }else{
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
                return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
            }   

            if (!$inquiry) {
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
                return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
            }
    


        if (!$inquiry) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar'), '13');
        }



        if($inquiry['status_bayar'] == 'LUNAS'){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Sudah di Bayar')).' ');
            return $this->response(array('response_code' => '88', 'message' => 'Tagihan Sudah di Bayar'), '401');
         }else{
           
            // return $this->withDataND($inquiry, '00');
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.') API response:'.json_encode(array('response_code'=>'00', 'message'=>'success')).' ');
            return $this->response(array('response_code'=>'00','data' =>$inquiry, 'message'=>'success'), '00');
         }
        
// log_message('info', 'API response: '.json_encode($inquiry));
    }

} 

/** REFACTORED BY RIDWAN */

