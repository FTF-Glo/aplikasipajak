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
class PaymentBillingPajak extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->db2 = $this->load->database('dbphtb', TRUE);

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; 
        $this->methods['users_post']['limit'] = 100; 
        $this->methods['users_delete']['limit'] = 50;
    }

    public function index_post()
    {
        $payment_code = $this->post("kdbill");
		$payment_ref_number = $this->post("payment_ref_number");

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
                //berhasil login
                if ($payment_code == "" || $payment_ref_number == "")
                {
                    $response['code']='01';
                    $response['description']='Kode billing atau Payment Ref Number belum di isi';
                }else{

                    $kdbelakang = substr($payment_code,-2);
                    $kddepan = substr($payment_code,0,-2);
                    $kdbillingfull = $kddepan .$kdbelakang;
                    
                    $ids = is_numeric($payment_code);
                    if($ids ==  1){
                        $panjangkode = '';
                        $panjangkode = strlen((string)$payment_code);

                        if($panjangkode <= 18){
                            //check panjang
                            $payment_code = $kdbillingfull;

                            if($kdbelakang == '02'){
                                $this->db2->where(array("payment_code"=>$payment_code));
                                $data = $this->db2->get('ssb')->result();
                        
                                if($data){
                                    $payment_flag = '1';
                                    $tomorrow  = date('Ymd', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
                                    $payment_settlement_date = $tomorrow;
                                    $payment_paid = date('Y-m-d H:i:s');
                                    $payment_offline_user_id = 'Sistem Bank Lampung';
                                    $payment_offline_paid = date('Y-m-d H:i:s');
                                    $payment_bank_code = "9996471";
                                    $bphtb_collectible = $data[0]->bphtb_dibayar;
                        
                                    if($data[0]->payment_flag == '1'){
                                        $response['code']='99';
                                        $response['description']= 'Kode billing ' . $payment_code .' Atas Nama ' . $data[0]->wp_nama . ' SUDAH LUNAS';
                                    }else{
                                        $where = array(
                                            "payment_code" =>$kdbillingfull
                                        );
                                
                                        $data = array(
                                            "payment_flag" =>$payment_flag,
                                            "payment_paid" =>$payment_paid,
                                            "payment_offline_user_id" =>$payment_offline_user_id,
                                            "payment_offline_paid" =>$payment_offline_paid,
                                            "payment_bank_code" =>$payment_bank_code,
                                            "bphtb_collectible" =>$bphtb_collectible,
                                            "payment_settlement_date" =>$payment_settlement_date,
                                            "PAYMENT_REF_NUMBER" =>$payment_ref_number,                              
                                        );
                                
                                        $update_bayar = $this->db2->update("ssb", $data, $where);
                                        
                                        if($update_bayar){
                                            $response['code']='06'; 
                                            $response['description']='Pembayaran berhasil';
                                        }else{
                                            $response['code']='88'; 
                                            $response['description']='Pembayaran Gagal';
                                        }

                                    }
                                }else{
                                        $response['code']='03';
                                        $response['description']='Data tidak ditemukan, Mohon cek kembali kode billing';
                                }
                
                            }else{
                                $this->db->where(array("payment_code"=>$payment_code));
                                $data = $this->db->get('simpatda_gw')->result();
                                
                                if($data){
                                    $payment_bank_code = '1234567';
                                    $operator = 'Sistem Bank Lampung';
                                    $patda_collectible = $data[0]->simpatda_dibayar;
                                    $patda_total_bayar = $data[0]->simpatda_dibayar + $data[0]->simpatda_denda;
                                    $patda_denda = $data[0]->simpatda_denda;
                                    $payment_paid = date('Y-m-d H:i:s');
                                    $tomorrow  = date('Ymd', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
                                    $payment_settelement_date = $tomorrow;
                                    $payment_flag = '1';
                        
                                    if($data[0]->payment_flag == '1'){
                                        $response['code']='99';
                                        $response['description']= 'Kode billing ' . $payment_code .' Atas Nama ' . $data[0]->wp_nama . ' SUDAH LUNAS';
                                    }else{
                                        $where = array(
                                            "payment_code" =>$kdbillingfull
                                        );
                                
                                        $data = array(
                                            "payment_flag" =>$payment_flag,
                                            "payment_bank_code" =>$payment_bank_code,
                                            "operator" =>$operator,
                                            "patda_collectible" =>$patda_collectible,
                                            "patda_total_bayar" =>$patda_total_bayar,
                                            "patda_denda" =>$patda_denda,
                                            "payment_paid" =>$payment_paid,
                                            "PAYMENT_SETTLEMENT_DATE" =>$payment_settelement_date,
											"PAYMENT_REF_NUMBER" =>$payment_ref_number
                                        );
                                
                                        $update_bayar = $this->db->update("simpatda_gw", $data, $where);
                            
                                        if($update_bayar){
                                            $response['code']='06'; 
                                            $response['description']='Pembayaran berhasil';
                                        }else{
                                            $response['code']='88'; 
                                            $response['description']='Pembayaran Gagal';
                                        }
                                    }
                                }else{
                                        $response['code']='03';
                                        $response['description']='Data tidak ditemukan, Mohon cek kembali kode billing';
                                }
                
                                
                            }
                            //end panjang
                        }else{
                            $response['code']='02';
                            $response['description']='Kode billing tidak sesuai format, hanya boleh angka';
                        }
                        
                    }else{
                        $response['code']='02';
                        $response['description']='Kode billing tidak sesuai format, hanya boleh angka';
                    }
                    
                    
                    
                                
                }
                $this->response($response, REST_Controller::HTTP_OK);
                //end login
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
