<?php

defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'Token.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class Pay9Pajak extends Token
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
        $payment_amount = $this->post('payment_amount');
        $payment_ref_number     = $this->post("payment_ref_number");
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);

        $new_paid               = $this->post('tanggal_bayar');
        $_new_payment_paid       = DateTime::createFromFormat('d-m-Y H:i:s', $new_paid);
        $new_payment_paid       = $_new_payment_paid !== false ? $_new_payment_paid->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $channel                = $this->post('channel');
        $new_channel            = isset($channel) ? $channel : "";

        $kdbelakang     = substr($id, -2);
        $kddepan        = substr($id, 0, -2);
        $kdbillingfull  = $kddepan . $kdbelakang;
        $ids            = is_numeric($id);
        $panjangkode    = strlen((string)$id);
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');

        // var_dump($tax_type,$kdbelakang);exit;
        if (empty($tax_type || $area_code || $id)) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        
        if ($_new_payment_paid == false && empty($new_paid) == false || $new_paid === '') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('17');
        }

        // if ($ids != 1) {
        //     return $this->simple('02');
        // }
        // if (strlen($tax_type) != 2) {
        //     return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        // }

        // if ($panjangkode >= 18) {
        //     return $this->simple('02');
        // }

        // var_dump($area_code );exit;

        if ($tax_type  <>  $kdbelakang) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        // if($kdbelakang == '02'){
        //     $inquiry = $this->inquiryPajakBPTHB($payment_code);
        // }else{

        if ($this->authtoken() == 'benar') {
            $inquiry = $this->paymentbniSPajak($area_code, $tax_type, $id, $payment_amount);
           
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired'));
        }
        // var_dump($this->authtoken() );exit;


        if (!$inquiry) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'Tagihan Tidak Sesuai'), '401');
        }

        if ($inquiry['status_bayar'] == 'LUNAS') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Sudah di Bayar')).' ');
            return $this->response(array('response_code' => '88', 'message' => 'Tagihan Sudah di Bayar'), '401');
        }

        $users = $this->getBank($this->username);
        $params = array(
            'payment_ref_number' => $payment_ref_number,
            'new_channel' => $new_channel,
            'new_payment_paid' => $new_payment_paid,
            // 'operator' => $users['operator'],
            'operator' => 'BNI',
            'payment_bank_code' => $users['payment_bank_code'],
            'payment_settlement_date' => date('Ymd', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")))
        );

        // $this->paymentbniSPajak($area_code, $tax_type, $id, $payment_amount);
        if ($this->authtoken() == 'benar') {
            $inquiry = $this->updatebniSPajak($inquiry, $params);
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$id.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }
        if ($inquiry == true) {
            return $this->response();
        } else {
            return $this->simple('88');
        }
    }
}

/** REFACTORED BY Ridwan */
