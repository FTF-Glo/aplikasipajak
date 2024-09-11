<?php

defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'Token.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PayPBB extends Token
{

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        $this->pbb = $this->load->database('pbb', TRUE);
        // $this->penomoran = $this->load->database('penomoran', TRUE);
    }

    public function index_post()
    {
        $tax_year         = $this->post('tax_year');
        $tax_type_belakang =  $this->post('tax_type', 2);
        $tax_type =  substr($tax_type_belakang, 2);
        $nop              = $this->post('nop');
        $payment_amount   = $this->post('payment_amount');


        // $kdbelakang = substr($id,-2);
        $payment_amount = $this->post('payment_amount');
        $payment_ref_number = $this->post("payment_ref_number");
        // $ids            = is_numeric($id);
        // $panjangkode    = strlen((string)$id);

        $new_paid               = $this->post('tanggal_bayar');
        $_new_payment_paid       = DateTime::createFromFormat('d-m-Y H:i:s', $new_paid);
        $new_payment_paid       = $_new_payment_paid !== false ? $_new_payment_paid->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $channel                = $this->post('channel');
        $new_channel            = isset($channel) ? $channel : "";

        
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        // $kdbelakang     = substr($id,-2);
        // $kddepan        = substr($id,0,-2);
        // $kdbillingfull  = $kddepan .$kdbelakang;            
        // $ids            = is_numeric($id);
        // $panjangkode    = strlen((string)$id);

        if (empty($tax_year || $tax_type || $nop || $payment_amount)) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if ($_new_payment_paid == false && empty($new_paid) == false || $new_paid === '') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('17');
        }

        // if($nop != 1){
        //     return $this->simple('02');
        // }
        if ($tax_type_belakang != 02) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        $inquiryy = $this->paymentbniPBB_copy($tax_year, $tax_type, $nop, $payment_amount);


        if ($inquiryy['total'] <> $payment_amount) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Sesuai'), '401');
        } else {
            if ($this->authtoken() == 'benar') {
                if ($tax_type == 02) {
                $inquiry = $this->paymentbniPBB($tax_year, $tax_type, $nop, $payment_amount);
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.')');
                }else{
                    log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
                    return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Sesuai'), '401');
                }
            } else {
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
                return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired'), '401');
            }
        }
        // exit;
        if (!$inquiry) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if ($inquiry['status_bayar'] == 'LUNAS') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.' amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Sudah di Bayar')).' ');
            return $this->response(array('response_code' => '88', 'message' => 'Tagihan Sudah di Bayar'), '401');
        }

        $users = $this->getBank($this->username);
        $params = array(
            'payment_ref_number' => $payment_ref_number,
            'new_channel' => $new_channel,
            'new_payment_paid' => $new_payment_paid,
            'operator' => 'BNI',
            'payment_bank_code' => $users['payment_bank_code'],
            'payment_settlement_date' => date('Ymd', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")))

            // 'PAYMENT_FLAG'            => 1,
            // 'PAYMENT_PAID'            => $tanggalBayar,
            // 'PAYMENT_OFFLINE_PAID'    => $tanggalBayar,
            // 'PBB_DENDA'               => $inquiry['penalty'],
            // 'PBB_TOTAL_BAYAR'         => $jumlahBayar,
            // 'PAYMENT_REF_NUMBER'      => $paymentRefNum,
            // 'PAYMENT_OFFLINE_USER_ID' => $users['operator'],
            // 'PAYMENT_BANK_CODE'       => $users['payment_bank_code'],
            // 'COLL_PAYMENT_CODE'       => $collPaymentCode,
            // 'PAYMENT_MERCHANT_CODE'   => $channel,

        );

        $this->paymentbniPBB_copy($tax_year, $tax_type, $nop, $payment_amount);
        if ($this->authtoken() == 'benar') {
            $inquiry = $this->updatebniPBB($inquiry, $params);
           
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }
        // }
        if ($inquiry == true) {
            return $this->response();
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (year: '.$tax_year.' type: '.$tax_type.' nop: '.$nop.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('88');
        }
    }
}

/** REFACTORED BY Ridwan*/
