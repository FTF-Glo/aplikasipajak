<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once 'RestController.php';

class GetBillingPBB extends RestController {

    function __construct()
    {
        parent::__construct();
        $this->logins();
        // $this->db = $this->load->database(self::DB_PBB, TRUE);
        $this->pbb = $this->load->database('pbb', TRUE);
    }

    public function index_get()
    {
        
        $nop = $this->get('nop', true);
        $tahun = $this->get('tahun', true);

        if (empty($nop)) {
            return $this->simple('09');
        }
        
        if ($this->isInputValidPBB($nop, $tahun)) {
            return $this->simple('07');
        }
        
        $inquiry = $this->inquiryPBB($nop, $tahun);
        
        if (!$inquiry) {
            return $this->simple('08');
        }

        if ($inquiry[0]['status_bayar'] === self::PBB_LUNAS) {
            return $this->simple('99', "NOP {$inquiry[0]['nop']} Atas Nama {$inquiry[0]['nama_wp']} SUDAH LUNAS");
        }

        return $this->withDataND(array('bills' => $inquiry), empty($tahun) ? '10' : '00');
    }
}

/** REFACTORED BY ALDES DAN AAN */