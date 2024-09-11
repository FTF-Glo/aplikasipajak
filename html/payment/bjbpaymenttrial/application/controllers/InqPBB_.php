<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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

class InqPBB extends Token {

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        // $this->db = $this->load->database(self::DB_PBB, TRUE);
        $this->pbb = $this->load->database('pbb', TRUE);
    }



    public function index_post()
    {
        
        var_dump($this->authtoken() );exit;
        $tax_year = $this->post('tax_year', true);
        $tax_type_belakang =  $this->post('tax_type',2);
        $tax_type =  substr($tax_type_belakang,2);
        $nop = $this->post('nop', true);
        // var_dump($tax_year,$tax_type,$nop);
        if (empty($nop)) {
            return $this->simple('09');
        }
        if ($this->authtoken() == 'benar'){
            if($tax_type == 01){
                $inquiry = $this->inquirybniPBB($tax_year,$tax_type,$nop);
            }
        }
        
        
        if (!$inquiry) {
            return $this->simple('08');
        }

        // if ($inquiry[0]['status_bayar'] === self::PBB_LUNAS) {
        //     return $this->simple('99', "NOP {$inquiry[0]['nop']} Atas Nama {$inquiry[0]['nama_wp']} SUDAH LUNAS");
        // }

        return $this->withDataND(array('bills' => $inquiry), empty($tahun) ? '10' : '00');
    }
}

/** REFACTORED BY Ridwan */