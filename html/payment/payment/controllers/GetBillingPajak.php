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
class GetBillingPajak extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->db2 = $this->load->database('dbphtb', TRUE);
        $this->db3 = $this->load->database('sw_ssb', TRUE);

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; 
        $this->methods['users_post']['limit'] = 100; 
        $this->methods['users_delete']['limit'] = 50; 
    }

    public function index_get()
    {

        $id = $this->get('kdbill');

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
                        // $kb = explode('-', $id.'-');
                        // $kddepan =  $kb[0];
                        // $kdbelakang   =  (int) $kb[1];
                        // $kdbillingfull = $kddepan.'-'.$kdbelakang;

                        $kdbelakang = substr($id,-2);
                        $kddepan = substr($id,0,-2);
                        $kdbillingfull = $kddepan .$kdbelakang;


                        if ($id == "")
                        {
                            //kode billing belum diisi
                            $response['code']='01';
                            $response['description']='Kode billing belum di isi';
                        }

                        else {

                            $ids = is_numeric($id);
                            if($ids == 1){
                                $pajangkode = '';
                                $panjangkode = strlen((string)$id);
                                
                                        if($panjangkode <= 18){
                                            //hasil cek kode
                                            if($kdbelakang == '02'){
                                            $sql = "SELECT
                                            id_switching,
                                            payment_code as kode_billing,
                                            op_nomor as npwpd,
                                            op_nomor as no_objek_pajak,
                                            wp_nama as nama_wajib_pajak,
                                            wp_alamat as alamat_wajib_pajak,
                                            wp_nama as nama_objek_pajak,
                                            op_letak As alamat_objek_pajak,
                                            bphtb_dibayar as jumlah_pajak_dibayar,
                                            saved_date as masa_pajak,
                                            IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar
                                            FROM ssb where payment_code='$kdbillingfull'";
                                            $data = $this->db2->query($sql)->result();
                                            
                                            if($data){
                                                $id_switching = $data[0]->id_switching;
                                                $query = "SELECT CPM_DENDA FROM cppmod_ssb_doc WHERE CPM_SSB_ID = '$id_switching' ";
                                                $data_bphtb = $this->db3->query($query)->result();

                                                //var_dump($data_bphtb);die;
                                                if($data_bphtb){
                                                    $denda_bphtb = $data_bphtb[0]->CPM_DENDA;
                                                }else{
                                                    $denda_bphtb = 0;
                                                }

                                                $nilai = $data[0]->jumlah_pajak_dibayar -  $denda_bphtb;
                                                $nilai = (string) $nilai;

                                                $date_awal= date_create($data[0]->masa_pajak);
                                                $date_awal= date_format($date_awal,"d-m-Y");
                                                
                                                foreach ($data as $dat){
                                                    $details[]= array(
                                                        "no_objek_pajak" =>$dat->no_objek_pajak,
                                                        "nama_objek_pajak" =>$dat->nama_objek_pajak,
                                                        "alamat_objek_pajak" =>$dat->alamat_objek_pajak,
                                                    );

                                                        $hasil= array(
                                                            "kode_billing" =>$dat->kode_billing,
                                                            "npwpd" =>$dat->npwpd,
                                                            "nama_wajib_pajak" =>$dat->nama_wajib_pajak,
                                                            "alamat_wajib_pajak" =>$dat->alamat_wajib_pajak,
                                                            "nilai" =>$nilai,
                                                            "denda" =>$denda_bphtb,
                                                            "total" =>$dat->jumlah_pajak_dibayar,                                                      
															"status_bayar" =>$dat->status_bayar,
                                                            "masa_pajak_1" =>$date_awal,
                                                            "masa_pajak_2" =>$date_awal,
                                                            "details_op" => $details
                                                        );
                                                }
                                            }else{
                                                $response['code']='03';
                                                $response['description']='Data tidak ditemukan, Mohon cek kembali kode billing';
                                            }
                                            

                                        }else{
                                            $sql = "SELECT
                                            payment_code as kode_billing,
                                            npwpd as npwpd,
                                            op_nomor as no_objek_pajak,
                                            wp_nama as nama_wajib_pajak,
                                            wp_alamat as alamat_wajib_pajak,
                                            op_nama as nama_objek_pajak,
                                            op_alamat As alamat_objek_pajak,
                                            simpatda_dibayar as nilai,
                                            simpatda_denda as denda,
                                            (simpatda_dibayar+simpatda_denda) as jumlah_pajak_dibayar,
                                            IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar,
                                            masa_pajak_awal as pajak_awal,
                                            masa_pajak_akhir as pajak_akhir,
                                            id_switching as id_switching
                                            FROM simpatda_gw where payment_code='$kdbillingfull'";
                                            $data = $this->db->query($sql)->result();
                                            
                                            if($data){
                                                $id_switching = $data[0]->id_switching;

                                                $date_awal= date_create($data[0]->pajak_awal);
                                                $date_awal= date_format($date_awal,"d-m-Y");

                                                $date_akhir= date_create($data[0]->pajak_akhir);
                                                $date_akhir= date_format($date_akhir,"d-m-Y");

                                                if($kdbelakang == '06'){
                                                    $query = "SELECT B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame FROM patda_reklame_doc_atr A INNER JOIN patda_reklame_profil B ON A.CPM_ATR_ID_PROFIL = B.CPM_ID WHERE A.CPM_ATR_REKLAME_ID = '$id_switching' ";
                                                    $data_opreklame = $this->db->query($query)->result();


                                                    foreach ($data as $dat){
                                                        foreach ($data_opreklame as $rek){
                                                            $reks[]= array(
                                                                "no_objek_pajak" =>$rek->nop,
                                                                "nama_objek_pajak" =>$rek->nama_op_reklame,
                                                                "alamat_objek_pajak" =>$rek->alamat_op_reklame,
                                                                "judul_reklame" =>$rek->judul,
                                                                "lokasi_reklame" =>$rek->lokasi,
                                                            );
                                                        }

                                                        $hasil= array(
                                                            "kode_billing" =>$dat->kode_billing,
                                                            "npwpd" =>$dat->npwpd,
                                                            "nama_wajib_pajak" =>$dat->nama_wajib_pajak,
                                                            "alamat_wajib_pajak" =>$dat->alamat_wajib_pajak,
                                                            "nilai" =>$dat->nilai,
                                                            "denda" =>$dat->denda,
                                                            "total" =>$dat->jumlah_pajak_dibayar,                                                        "status_bayar" =>$dat->status_bayar,
                                                            "masa_pajak_1" =>$date_awal,
                                                            "masa_pajak_2" =>$date_akhir,
                                                            "details_op" =>$reks,
                                                        );
                                                    }

                                                }else{
                                                    foreach ($data as $dat){
                                                        $details[]= array(
                                                            "no_objek_pajak" =>$dat->no_objek_pajak,
                                                            "nama_objek_pajak" =>$dat->nama_objek_pajak,
                                                            "alamat_objek_pajak" =>$dat->alamat_objek_pajak,
                                                        );

                                                        $hasil= array(
                                                            "kode_billing" =>$dat->kode_billing,
                                                            "npwpd" =>$dat->npwpd,
                                                            "nama_wajib_pajak" =>$dat->nama_wajib_pajak,
                                                            "alamat_wajib_pajak" =>$dat->alamat_wajib_pajak,
                                                            "nilai" =>$dat->nilai,
                                                            "denda" =>$dat->denda,
                                                            "total" =>$dat->jumlah_pajak_dibayar,
                                                            "status_bayar" =>$dat->status_bayar,
                                                            "masa_pajak_1" =>$date_awal,
                                                            "masa_pajak_2" =>$date_akhir,
                                                            "details_op" =>$details
                                                        );
                                                    }
                                                }


                                            }else{
                                                $response['code']='03';
                                                $response['description']='Data tidak ditemukan, Mohon cek kembali kode billing';
                                            }
                                            
                                        }
                                        
                                        //Cek apakah data ada atau gak
                                        if ($data) {
            
                                            if($data[0]->status_bayar == 'LUNAS'){
                                                $this->response([
                                                    'status' => FALSE,
                                                    'message' => 'Kode billing belum di isi'
                                                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
                                                $response['code']='99';
                                                $response['description']= 'Kode billing ' . $id .' Atas Nama ' . $data[0]->nama_wajib_pajak . ' SUDAH LUNAS';
                                            }else{
                                                $response['code']='00';
                                                $response['data']=$hasil;
                                            }
            
                                        } else {
                                            $response['code']='03';
                                            $response['description']='Data tidak ditemukan, Mohon cek kembali kode billing';
                                        }

                                    }else{
                                        $response['code']='02';
                                        $response['description']='Kode billing tidak sesuai format, hanya boleh angka';
                                    }

                                    //end cek kode
                                }else{
                                    $response['code']='02';
                                    $response['description']='Kode billing tidak sesuai format, hanya boleh angka';
                                }

                                

                            

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
