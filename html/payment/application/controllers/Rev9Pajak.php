<?php

defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'Token.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class Rev9Pajak extends Token
{

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
        $tax_type_belakang =  $this->post('tax_type', 2);
        $id = $this->post('billing_code');
        $tax_type =  substr($tax_type_belakang, 2);
        $kdbelakang = substr($id, -2);

        $refnum = $this->post("refnum");
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);



        $kdbelakang     = substr($id, -2);
        $kddepan        = substr($id, 0, -2);
        $kdbillingfull  = $kddepan . $kdbelakang;
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);

        // var_dump($area_code,$tax_type,$id,$refnum,$kdbelakang);exit;
        if (empty($tax_type || $area_code || $id || $refnum)) {
            // return $this->simple('01', 'Kode billing atau type tax atau area code belum di isi');
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        // if($_new_payment_paid == false && empty($new_paid) == false || $new_paid === ''){
        //     return $this->simple('17');
        // }

        if ($ids != 1) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('02');
        }
        // if (strlen($tax_type) != 2) {
        //     return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        // }

        if ($panjangkode >= 18) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.') API response:'.json_encode(array('response_code'=>'02', 'message'=>'Hanya Boleh Diisi Angka')).' ');
            return $this->simple('02');
        }

        // if($tax_type  <>  $kdbelakang) {
        //     return $this->simple('03');
        // }
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        if ($this->authtoken() == 'benar') {
            $inquiry = $this->datareversalbniSPajak($area_code, $tax_type, $id, $refnum);
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.' refnum: '.$refnum.')');
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }

        // var_dump($inquiry['status_bayar'] == NULL || $inquiry['status_bayar'] == '');exit;
        // var_dump($inquiry['status_bayar']);exit;
        // if(!$inquiry) {
        //     return $this->simple('03');
        // }

        if ($inquiry['status_bayar'] == 'BELUM LUNAS') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Belum Lunas')).' ');
            return $this->response(array('response_code' => '88', 'message' => 'Tagihan Belum Lunas'), '401');
        }
        if($inquiry['refnum'] <> $refnum || $inquiry['billing_code'] <> $id){
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'Kode Bayar Tidak Terdaftar')).' ');
            return $this->response(array('response_code'=>'14', 'message'=>'Kode Bayar Tidak Terdaftar'), '401');
        }

        // if(isset($inquiry) && !empty($inquiry)){
        //     var_dump($inquiry);
        // }else{
        //     echo "Inquiry is not defined or empty";
        // }
        $users = $this->getBank($this->username);
        $params = array(
            'payment_ref_number' => $refnum,
        );

        if ($this->authtoken() == 'benar') {
            $inquiry = $this->insertreversalSpajak($inquiry, $params);
            $inquiry = $this->reversalSPajak($inquiry, $params);
        } else {
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }

        if ($inquiry == true) {
            return $this->response(array('response_code'=>'00', 'message'=>'SUCCESS'), '00');
            // return $this->simple('00');
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' response_code: '.$id.'refnum: '.$refnum.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'Kode Bayar Tidak Terdaftar')).' ');
            return $this->response(array('response_code'=>'14', 'message'=>'Kode Bayar Tidak Terdaftar'), '401');
        }
    }
}

/** REFACTORED BY Ridwan */
