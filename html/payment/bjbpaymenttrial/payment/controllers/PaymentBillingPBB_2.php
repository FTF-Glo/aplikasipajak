<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class PaymentBillingPBB extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->db3 = $this->load->database('pbb', TRUE);

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; 
        $this->methods['users_post']['limit'] = 100; 
        $this->methods['users_delete']['limit'] = 50;
    }

    public function index_post()
    {
        $nop = $this->post("NOP");
        $total_jumlah_bayar = $this->post("total_jumlah_bayar");
		$payment_ref_number = $this->post("payment_ref_number");
        $detail =  $this->post("detail");

        // var_dump($nop, $total_jumlah_bayar, $detail);die;

        $username = $this->input->server('PHP_AUTH_USER');
        $password = $this->input->server('PHP_AUTH_PW');
        $valid_logins = $this->config->item('rest_valid_logins');
        
        if (empty($this->input->server('PHP_AUTH_USER')) || empty($this->input->server('PHP_AUTH_PW')))
        {
            header('WWW-Authenticate: Basic realm="My Realm"');
            //username dan password belum diisi
            $response['code']='05';
            $response['description']='Username atau Password Belum Diisi';
            $this->response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }else{
            $valid = isset($valid_logins[$username]) ? $valid_logins[$username] : '';
            if($password == $valid){
                if($nop != '' && $total_jumlah_bayar != '' && $payment_ref_number  != ''){
                    $nops = is_numeric($nop);
                    if($nops == 1){
                        $pajangkode = '';
                        $panjangkode = strlen((string)$nop);
                        
                        if($panjangkode == 18){
                            $tgl = date("Y-m-d");
                            $hasil = array();
                            foreach ($detail as $det => $d){
                                // $tahun = $d['tahun'];
                                // $jumlah_bayar = $d['jumlah_bayar'];
                                $tahun = isset($d['tahun']) ? $d['tahun'] : '';
                                $jumlah_bayar = isset($d['jumlah_bayar']) ? $d['jumlah_bayar'] : '';
                                // var_dump($tahun, $jumlah_bayar);
                                $panjangtahun = '';
                                $panjangtahun = strlen((string)$tahun);
                                $tahuns = is_numeric($tahun);

                                $panjangjumlah_bayar = '';
                                $panjangjumlah_bayar = strlen((string)$jumlah_bayar);
                                $jumlah_bayars = is_numeric($jumlah_bayar);
                                $total_semua = 0;


                                if($panjangtahun != 4 || $tahuns == 0 || $jumlah_bayars == 0 || $jumlah_bayar == ''){
                                    $description[] = array(
                                        "code" => '16',
                                        "description" => 'Tahun atau Jumlah bayar tidak sesuai format, hanya boleh angka',                                      
                                    );
                                    $response['data']= $description;
                                }else{
                                    $sql = "SELECT NOP,
                                    SPPT_TAHUN_PAJAK, 
                                    WP_NAMA, 
                                    OP_KECAMATAN,
                                    OP_KELURAHAN,
                                    SPPT_TANGGAL_JATUH_TEMPO,
                                    OP_LUAS_BUMI,
                                    OP_LUAS_BANGUNAN,
                                    SPPT_PBB_HARUS_DIBAYAR,
                                    PAYMENT_FLAG,
                                    @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,SPPT_TANGGAL_JATUH_TEMPO,'".$tgl."')/30) dendaBulan,
                                    @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
                                    @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
                                    FLOOR((2/100)*@dendaBulan*SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA,
                                    CONCAT('".$tgl."',DATE_FORMAT(NOW(),' %H:%i:%s')) AS PAYMENT_PAID
                                    FROM   PBB_SPPT
                                    WHERE  NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
                                    $data = $this->db3->query($sql)->result();

                                    if($data){
                                        //jika berhasil
                                    }else{
                                        $description[] = array(
                                            "code" => '13',
                                            "description" => 'Data tidak ditemukan, Mohon cek kembali Tahun dan Jumlah bayar',                                      
                                        );
                                        $response['data']= $description;
                                    }

                                    foreach ($data as $dat){
                                        $totals = $dat->SPPT_PBB_HARUS_DIBAYAR + $dat->SPPT_DENDA;
                                        $semua[] = $totals;
                                        $total_semua = array_sum($semua);
                                        if($d['jumlah_bayar'] == $totals){
                                            $hasil[] = array(
                                                "wp_nama" => $dat->WP_NAMA,
                                                "tahun_pajak" => $dat->SPPT_TAHUN_PAJAK,
                                                // "denda" => $dat->SPPT_DENDA,
                                                // "total" => $dat->SPPT_PBB_HARUS_DIBAYAR + $dat->SPPT_DENDA,
                                                "nilai" => $dat->SPPT_PBB_HARUS_DIBAYAR,
                                                "payment_flag" => $dat->PAYMENT_FLAG,
                                                "payment_paid" => $dat->PAYMENT_PAID,
                                                "pbb_denda" => $dat->SPPT_DENDA,
                                                "pbb_total_bayar" => $dat->SPPT_PBB_HARUS_DIBAYAR + $dat->SPPT_DENDA,
                                                "payment_offline_user_id" => 'Sistem Bank Lampung',
                                                "payment_offline_paid" => date('Y-m-d H:i:s')
                                            );
                                        }else{
                                            $description[] = array(
                                                "code" => '12',
                                                "description" => 'Jumlah bayar tidak sama',                                      
                                            );
                                            $response['data']= $description;
                                        }
                                    }
                                }
                                
                                // var_dump($hasil);
                            }                            

                            // if($total_semua == $total_jumlah_bayar){
                                if ($hasil) {
                                
                                    foreach ($hasil as $has => $h) {
                                        // var_dump($h['payment_flag']);
                                        
                                        if($h['payment_flag'] == '1'){
                                            // $response['code']='99';
                                            if($total_semua == $total_jumlah_bayar){
                                                $description[] = array(
                                                    "code" => '99',
                                                    "description" => 'NOP ' . $nop . ' Tahun ' . $h['tahun_pajak'] .' Atas Nama ' . $h['wp_nama'] . ' SUDAH LUNAS',                                      
                                                );
                                                $response['data']= $description;
                                            }else{
                                                $description[] = array(
                                                    "code" => '15',
                                                    "description" => 'Jumlah total bayar tidak sama',                                      
                                                );
                                                $response['data']= $description;
                                            }
                                            
                                        }else{
                                            $where = array(
                                                "NOP" =>$nop,
                                                "SPPT_TAHUN_PAJAK" =>$h['tahun_pajak']
                                            );
                                    
                                            $data = array(
                                                "PAYMENT_FLAG" => '1',
                                                "PAYMENT_PAID" =>$h['payment_paid'],
                                                "PBB_DENDA" =>$h['pbb_denda'],
                                                "PBB_TOTAL_BAYAR" =>$h['pbb_total_bayar'],
                                                "PAYMENT_OFFLINE_USER_ID" =>$h['payment_offline_user_id'],
                                                "PAYMENT_OFFLINE_PAID" =>$h['payment_offline_paid'],
                                                "PAYMENT_REF_NUMBER" =>$payment_ref_number,                              												
                                            );

                                            if($total_semua == $total_jumlah_bayar){
                                                $this->db3->update("PBB_SPPT", $data, $where);
                                    
                                                $description[] = array(
                                                    "code" => '06',
                                                    "description" => 'NOP ' . $nop . ' Tahun ' . $h['tahun_pajak'] . ' Pembayaran berhasil',                                      
                                                );
                                                $response['data']= $description;
                                            }else{
                                                $description[] = array(
                                                    "code" => '15',
                                                    "description" => 'Jumlah total bayar tidak sama',                                      
                                                );
                                                $response['data']= $description;
                                            }
                                    
                                        }
                                    }
    
                                    
                                } 
                            // }else{
                            //     $description[] = array(
                            //         "code" => '12',
                            //         "description" => 'Jumlah total bayar tidak sama',                                      
                            //     );
                            //     $response['data']= $description;
                            // }
                            //Cek apakah data ada atau gak

                            
                            // else {
                            //     $description[] = array(
                            //         "code" => '13',
                            //         "description" => 'Data tidak ditemukan, Mohon cek kembali Tahun dan Jumlah bayar
                            //         ',                                      
                            //     );
                            //     $response['data']= $description;
                            // }
                            //end check hasil


                        }else{
                            $response['code']='14';
                            $response['description']='NOP atau Total jumlah bayar tidak sesuai format, hanya boleh angka';
                        }


                    }else{
                        $response['code']='14';
                        $response['description']='NOP atau Total jumlah bayar tidak sesuai format, hanya boleh angka';
                    }

                }else{
                    $response['code']='11';
                    $response['description']='NOP ,Total jumlah bayar, atau PAYMENT Ref Number masih kosong';
                }
                        
                $this->response($response, REST_Controller::HTTP_OK);
                //end berhasil login
                        
            }else{
                //gagal login
                $response['code']='04';
                $response['description']='Username atau Password salah';
                $this->response($response, REST_Controller::HTTP_FORBIDDEN);
            }
        }

        // $this->response($response, 200);
    }

}
