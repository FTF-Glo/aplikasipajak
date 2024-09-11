<?php

defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'RestController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PaymentBillingPajak extends RestController {

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

        $payment_code           = $this->post("kdbill");
		$payment_ref_number     = $this->post("payment_ref_number");
		$new_paid               = $this->post('tanggal_bayar');
		$_new_payment_paid       = DateTime::createFromFormat('d-m-Y H:i:s', $new_paid);
		$new_payment_paid       = $_new_payment_paid !== false ? $_new_payment_paid->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $channel                = $this->post('channel');
        $new_channel            = isset($channel) ? $channel : "";

        $kdbelakang     = substr($payment_code,-2);
        $kddepan        = substr($payment_code,0,-2);
        $kdbillingfull  = $kddepan .$kdbelakang;            
        $ids            = is_numeric($payment_code);
        $panjangkode    = strlen((string)$payment_code);
        
        if(empty($payment_code) || empty($payment_ref_number)){
            return $this->simple('01', 'Kode billing atau Payment Ref Number belum di isi');
        }

        if($_new_payment_paid == false && empty($new_paid) == false || $new_paid === ''){
            return $this->simple('17');
        }

        if($ids != 1){
            return $this->simple('02');
        }


        if($panjangkode >= 18){
            return $this->simple('02');
        }

        if($kdbelakang == '02'){
            $inquiry = $this->inquiryPajakBPTHB($payment_code);
        }else{
            $inquiry = $this->inquiryPajakSPajak($payment_code);
        }

        if(!$inquiry) {
            return $this->simple('03');
        }
        
        if($inquiry['status_bayar'] == 'LUNAS'){
            return $this->simple('99', 'Kode billing ' . $payment_code .' Atas Nama ' . $inquiry['nama_wajib_pajak'] . ' SUDAH LUNAS');
        }

        $users = $this->getBank($this->username);
        $params = array(
            'payment_ref_number' => $payment_ref_number,
            'new_channel' => $new_channel,
            'new_payment_paid' => $new_payment_paid,
            'operator' => $users['operator'],
            'payment_bank_code' => $users['payment_bank_code'],
            'payment_settlement_date' => date('Ymd', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")))
        );
        
        if($kdbelakang == '02'){
            $inquiry = $this->updatePajakBPHTB($inquiry, $params);
        }else{
            $inquiry = $this->updatePajakSPajak($inquiry, $params);
        }

        if($inquiry == true){
            return $this->simple('06');
        }else{
            return $this->simple('88');
        }

    }

}

/** REFACTORED BY ALDES DAN AAN */