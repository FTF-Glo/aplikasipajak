<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'RestController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ReversalPajak extends RestController {

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
        $tax_year = $this->post('tax_year');
        $tax_type_belakang =  $this->post('tax_type',2);
        $nop = $this->post('nop');
        $refnum = $this->post('refnum');
        $tax_type =  substr($tax_type_belakang,2);
        $kdbelakang = substr($id,-2);
        $ids = is_numeric($id);
        $panjangkode = strlen((string)$id);
        var_dump($area_code, $tax_type,$id);

        if(empty($tax_type && $area_code && $nop && $refnum)){
            return $this->simple('01');
        }

        if($ids != 1){
            return $this->simple('02');
        }
        
        if($kdbelakang == '02'){
            $inquiry = $this->inquiryPajakBPTHB($id);
        }else{
            $inquiry = $this->reversalSPajak($tax_year,$tax_type,$nop,$refnum);
        }

        if (!$inquiry) {
            return $this->response(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar'), '401');
        }



        if($inquiry['status_bayar'] == 'LUNAS'){
            return $this->response(array('response_code'=>'88', 'message'=>'Tagihan Belum Lunas'), '401');
        }else{
            return $this->withDataND($inquiry, '00');
        }

    }

}

/** REFACTORED BY RIDWAN */