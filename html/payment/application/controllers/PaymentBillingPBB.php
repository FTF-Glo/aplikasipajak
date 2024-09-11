<?php

defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once 'RestController.php';

class PaymentBillingPBB extends RestController
{
    const FORMAT_TANGGAL_BAYAR = 'd-m-Y H:i:s';

    function __construct()
    {
        parent::__construct();
        $this->logins();

        $this->pbb = $this->load->database('pbb', TRUE);
    }

    public function index_post()
    {

        $nop              = $this->post('NOP');
        $totalBayar       = $this->post('total_jumlah_bayar');
        $paymentRefNum    = $this->post('payment_ref_number');
        $tipeBayar        = $this->post('tipe_pembayaran');
        $channel          = $this->post('channel');
        $details          = $this->post('detail');
        $_tanggalBayar   = $this->post('tanggal_bayar');
        $__tanggalBayar  = DateTime::createFromFormat(self::FORMAT_TANGGAL_BAYAR, $_tanggalBayar);
        $tanggalBayar    = $__tanggalBayar !== false ? $__tanggalBayar->format('Y-m-d H:i:s') : $this->time;  // sementara karena tidak ada response code jika salah format

        $collPaymentCode  = $tipeBayar !== null ? $tipeBayar : '';
        $channel          = $channel !== null ? $channel : '';

        if (
            empty($nop) ||
            empty($totalBayar) ||
            empty($paymentRefNum) 
        ) {
            return $this->simple('11','NOP ,Total jumlah bayar, atau PAYMENT Ref Number masih kosong');
        }
        
        if($__tanggalBayar == false && empty($_tanggalBayar) == false || $_tanggalBayar === ''){
            return $this->simple('17');
        }

        if ($this->isInputValidPBB($nop)) {
            return $this->simple('14');
        }

        $details = $this->getDetails($details);
        if($details == false){
            return $this->simple('16');
        }

        $inquiry = $this->inquiryPBB($nop, array_keys($details), $tanggalBayar);

        /** JIKA HASIL INQUIRY KOSONG ATAU ADA TAHUN PAJAK YANG TIDAK DITEMUKAN */
        if (!$inquiry || count($details) !== count($inquiry)) {
            return $this->simple('13');
        }

        $totalBayarDetail = 0;
        $updatePBB        = array();
        $returnDetails    = array();

        foreach ($inquiry as $row) {
            $users = $this->getBank($this->username);

            $jumlahBayar = $details[$row['tahun_pajak']];

            if (
                empty($jumlahBayar) ||
                !is_numeric($jumlahBayar)
            ) {
                return $this->simple('16');
            }

            if ($jumlahBayar != $row['total']) {
                return $this->simple('12');
            }

            if ($row['status_bayar'] === self::PBB_BELUM_LUNAS) {
                $updatePBB[] = array(
                    'SPPT_TAHUN_PAJAK'        => $row['tahun_pajak'],
                    'PAYMENT_FLAG'            => 1,
                    'PAYMENT_PAID'            => $tanggalBayar,
                    'PAYMENT_OFFLINE_PAID'    => $tanggalBayar,
                    'PBB_DENDA'               => $row['denda'],
                    'PBB_TOTAL_BAYAR'         => $jumlahBayar,
                    'PAYMENT_REF_NUMBER'      => $paymentRefNum,
                    'PAYMENT_OFFLINE_USER_ID' => $users['operator'],
                    'PAYMENT_BANK_CODE'       => $users['payment_bank_code'],
                    'COLL_PAYMENT_CODE'       => $collPaymentCode,
                    'PAYMENT_MERCHANT_CODE'   => $channel,
                );
            }

            $returnDetails[] = array(
                'NOP'    => $row['nop'],
                'tahun'  => $row['tahun_pajak'],
                'status' => self::PBB_LUNAS
            );

            $totalBayarDetail += $jumlahBayar;
        }

        if ($totalBayar != $totalBayarDetail) {
            return $this->simple('15');
        }

        /** JIKA SEMUA TAHUN PAJAK SUDAH LUNAS */
        if (empty($updatePBB)) {
            return $this->withDataRaw(array('details' => $returnDetails), '99', 'SUDAH LUNAS');
        }

        /** UPDATE PBB_SPPT */
        if ($this->updateBatchPBB($updatePBB, $nop) === false) {
            return $this->simple('88');
        }

        return $this->withDataRaw(array('details' => $returnDetails), '06', 'Pembayaran PBB berhasil');
    }

    private function getDetails($details)
    {
        $results = array();

        foreach ($details as $detail) {
            if(strlen($detail['tahun']) == 4 && is_numeric($detail['tahun'])){
                $results[$detail['tahun']] = $detail['jumlah_bayar'];
            }else{
                return false;
            }
        }

        return $results;
    }
}

/** REFACTORED BY ALDES DAN AAN */