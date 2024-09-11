<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once 'Token.php';

// //Library JWT
// defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/JWT.php';
// require APPPATH . '/libraries/ExpiredException.php';
// require APPPATH . '/libraries/BeforeValidException.php';
// require APPPATH . '/libraries/SignatureInvalidException.php';


// use namespace
use Restserver\Libraries\REST_Controller;
// use \Firebase\JWT\JWT;

class InqPBB extends Token
{

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        // $this->db = $this->load->database(self::DB_PBB, TRUE);
        $this->pbb = $this->load->database('pbb', TRUE);
    }

    public function index_post()
    {

        $tax_year = $this->post('tax_year', true);
        $tax_type_belakang =  $this->post('tax_type', 2);
        $tax_type =  substr($tax_type_belakang, 2);
        $nop = $this->post('nop', true);
                $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        // var_dump($tax_year,$tax_type,$nop);
        if (empty($nop)) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        // if ($tax_type <> 01) {
        //     log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
        //     return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        // }

        if ($this->authtoken() == 'benar') {
            if ($tax_type == 02) {
                $inquiry = $this->inquirybniPBB($tax_year, $tax_type, $nop);
              
            }else{
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' code: '.$nop.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
                return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
            }
        }else{
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' code: '.$nop.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
        }
        // var_dump($inquiry);

        if (!$inquiry) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' code: '.$nop.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if($inquiry['status_bayar'] == 'LUNAS'){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' code: '.$nop.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Sudah di Bayar')).' ');
            return $this->response(array('response_code' => '88', 'message' => 'Tagihan Sudah di Bayar'), '401');
           }else{
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (tax year: '.$tax_year.' type: '.$tax_type.' code: '.$nop.') API response:'.json_encode(array('response_code'=>'00', 'message'=>'success')).' ');
            return $this->response(array('response_code'=>'00','data' =>$inquiry, 'message'=>'success'), '00');
           }

        // if ($inquiry[0]['status_bayar'] === self::PBB_LUNAS) {
        //     return $this->simple('99', "NOP {$inquiry[0]['nop']} Atas Nama {$inquiry[0]['nama_wp']} SUDAH LUNAS");
        // }

        // return $this->withDataND(array('bills' => $inquiry), empty($tahun) ? '10' : '00');
    }
}

/** REFACTORED BY Ridwan */


// API Call from IP: 192.168.88.1 with Bearer Token: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcHByZXN0c2VydmljZSIsImF1ZCI6InVzZXIiLCJpYXQiOjE2NzU2NTMwMzMsIm5iZiI6MTY3NTY1MzA0MywiZXhwIjoxNjc1NjUzMzMzLCJyZXNwb25zZV9jb2RlIjoiMDAiLCJyZXNwb25zZV9tZXNzYWdlIjoiTG9naW4gc3VjY2VzcyIsImRhdGEiOnsidXNlcm5hbWUiOiJCTkl0cmlhbCJ9fQ.7FZUANxRJu_ZsM0hV6K6FDE-wVY4rVNMQDGIIS7wtLw Params (tax year: 2021 type: 02 code: 180803000200001119) API response:{"response_code":"05","message":"Token tidak sesuai atau sudah expired"} 

// API Call from IP: 192.168.88.1 with Create Bearer Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcHByZXN0c2VydmljZSIsImF1ZCI6InVzZXIiLCJpYXQiOjE2NzU2NTMwMzMsIm5iZiI6MTY3NTY1MzA0MywiZXhwIjoxNjc1NjUzMzMzLCJyZXNwb25zZV9jb2RlIjoiMDAiLCJyZXNwb25zZV9tZXNzYWdlIjoiTG9naW4gc3VjY2VzcyIsImRhdGEiOnsidXNlcm5hbWUiOiJCTkl0cmlhbCJ9fQ.7FZUANxRJu_ZsM0hV6K6FDE-wVY4rVNMQDGIIS7wtLw