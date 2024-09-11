<?php

defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'Token.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PayBPHTB extends Token
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

        $area_code         = $this->post('area_code');
        $tax_type_belakang =  $this->post('tax_type', 2);
        $tax_type =  substr($tax_type_belakang, 2);
        $billing_code              = $this->post('billing_code');
        $payment_amount   = $this->post('payment_amount');


        // $kdbelakang = substr($billing_code,-2);
        $payment_ref_number = $this->post("payment_ref_number");
        // $billing_codes            = is_numeric($billing_code);
        // $panjangkode    = strlen((string)$billing_code);
        // var_dump($area_code, $tax_type, $billing_code, $payment_amount);
        // exit;
        $new_paid               = $this->post('tanggal_bayar');
        $_new_payment_paid       = DateTime::createFromFormat('d-m-Y H:i:s', $new_paid);
        $new_payment_paid       = $_new_payment_paid !== false ? $_new_payment_paid->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $channel                = $this->post('channel');
        $new_channel            = isset($channel) ? $channel : "";
        $ip_address = $this->input->ip_address();
        $bearer_token = $this->input->get_request_header('Authorization');
        if (strlen($tax_type) != 2) {
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }
        // echo 'sadas';
        // $kdbelakang     = substr($billing_code,-2);
        // $kddepan        = substr($billing_code,0,-2);
        // $kdbillingfull  = $kddepan .$kdbelakang;
        // $billing_codes            = is_numeric($billing_code);
        // $panjangkode    = strlen((string)$billing_code);

        if (empty($area_code || $tax_type || $billing_code || $payment_amount)) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            // return $this->simple('01', 'Kode billing atau tipe pajak atau jumlah bayar area code belum di isi');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if ($_new_payment_paid == false && empty($new_paid) == false || $new_paid === '') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->simple('17');
        }

        // if($nop != 1){
        //     return $this->simple('02');
        // }


        // if($nop >= 18){
        //     return $this->simple('02');
        // }
        // var_dump($this->authtoken());
        // exit;

        if ($this->authtoken() == 'benar') {
            if ($area_code == '1808' && $tax_type == 01) {
                $inquiry = $this->paymentbniBPHTB($area_code, $tax_type, $billing_code, $payment_amount);
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.' amount: '.$payment_amount.')');
                
            }else{
                log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'Tagihan Tidak Sesuai')).' ');
                return $this->response(array('response_code' => '13', 'message' => 'Tagihan Tidak Sesuai'), '401');
            }
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }


        if (!$inquiry) {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar')).' ');
            return $this->response(array('response_code' => '13', 'message' => 'ID Tidak Terdaftar'), '401');
        }

        if ($inquiry['status_bayar'] == 'LUNAS') {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'88', 'message'=>'Tagihan Sudah di Bayar')).' ');
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

        if ($this->authtoken() == 'benar') {
            $inquiry = $this->updatebniBPHTB($inquiry, $params);
        } else {
            log_message('info', 'API Call from IP: '.$ip_address.' with Bearer Token: '.$bearer_token.' Params (area: '.$area_code.' type: '.$tax_type.' code: '.$billing_code.'amount: '.$payment_amount.') API response:'.json_encode(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired')).' ');
            return $this->response(array('response_code' => '05', 'message' => 'Token tidak sesuai atau sudah expired', 'data' => ['']), '401');
        }


        if ($inquiry == true) {
            return $this->response();
        } else {
            return $this->simple('88');
        }
    }
}

/** REFACTORED BY Ridwan*/
