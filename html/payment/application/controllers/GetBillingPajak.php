<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'RestController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class GetBillingPajak extends RestController {

    function __construct()
    {
        parent::__construct();
        $this->logins();
        $this->spajak = $this->load->database('default', TRUE);
        $this->gw_ssb = $this->load->database('dbphtb', TRUE);
        $this->sw_ssb = $this->load->database('sw_ssb', TRUE);
    }

    public function index_get()
    {
        $id = $this->get('kdbill');
        $kdbelakang = substr($id,-2);
        $kddepan = substr($id,0,-2);
        $ids = is_numeric($id);
        $panjangkode = strlen((string)$id);

        if(empty($id)){
            return $this->simple('01');
        }

        if($ids != 1){
            return $this->simple('02');
        }

        if($panjangkode >= 18){
            return $this->simple('02');
        }
        
        if($kdbelakang == '02'){
            $inquiry = $this->inquiryPajakBPTHB($id);
        }else{
            $inquiry = $this->inquiryPajakSPajak($id);
        }

        if (!$inquiry) {
            return $this->simple('08');
        }

        if($inquiry['status_bayar'] == 'LUNAS'){
            return $this->simple('99', 'Kode billing ' . $id .' Atas Nama ' . $inquiry['nama_wajib_pajak'] . ' SUDAH LUNAS');
        }else{
            return $this->withDataND($inquiry, '00');
        }

    }

}

/** REFACTORED BY ALDES DAN AAN */