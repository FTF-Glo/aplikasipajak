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
class GetBillingPBB extends REST_Controller {

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

    public function index_get()
    {

        $nop = $this->get('nop');
        $tahun = $this->get('tahun');

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

                if($nop != ""){
                    if ($tahun == "")
                    {
                        //jika tahun tidak ada
                        $nops = is_numeric($nop);
                        if($nops == 1){
                            $pajangkode = '';
                            $panjangkode = strlen((string)$nop);

                            
                            if($panjangkode == 18){
                                $tgl = date("Y-m-d");
                                $response['code']='berhasil';
                                $sql = "SELECT NOP as nop, SPPT_TAHUN_PAJAK as tahun_pajak, WP_NAMA as nama_wajib_pajak, WP_ALAMAT as alamat_wajib_pajak, WP_RT as rt_wajib_pajak, WP_RW as rw_wajib_pajak, WP_KELURAHAN as kelurahan_wajib_pajak, WP_KECAMATAN as kecamatan_wajib_pajak, WP_KOTAKAB as kotakab_wajib_pajak, OP_ALAMAT as alamat_op, OP_RT as rt_op, OP_RW as rw_op, OP_KELURAHAN as kelurahan_op, OP_KECAMATAN as kecamatan_op, OP_KOTAKAB as kotakab_op, OP_LUAS_BUMI as luas_bumi_op, OP_LUAS_BANGUNAN as luas_bagunan_op,
                                SPPT_PBB_HARUS_DIBAYAR as nilai, @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,SPPT_TANGGAL_JATUH_TEMPO,'".$tgl."')/30) dendaBulan,
                                @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
                                @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
                                @denda := FLOOR((2/100)*@dendaBulan*SPPT_PBB_HARUS_DIBAYAR) as denda, (@denda+SPPT_PBB_HARUS_DIBAYAR) as total, IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar FROM pbb_sppt where NOP='$nop' AND payment_flag = 0";

                                // $nilai = $data[0]->SPPT_PBB_HARUS_DIBAYAR;
                                // $denda = $data[0]->SPPT_DENDA;
                                // $total = $nilai + $denda;

                                $data = $this->db3->query($sql)->result();
                                foreach ($data as $dat){
                                    $hasil[] = array(
                                        "nop" =>$dat->nop,
                                        "tahun_pajak" =>$dat->tahun_pajak,
                                        "nama_wp" =>$dat->nama_wajib_pajak,
                                        "alamat_wp" =>$dat->alamat_wajib_pajak,

                                        "rt_rw_wp" =>$dat->rt_wajib_pajak .'/'.$dat->rw_wajib_pajak,
                                        "kelurahan_wp" =>$dat->kelurahan_wajib_pajak,
                                        "kecamatan_wp" =>$dat->kecamatan_wajib_pajak,
                                        "kotakab_wp" =>$dat->kotakab_wajib_pajak,

                                        "alamat_op" =>$dat->alamat_op,
                                        "rt_rw_op" =>$dat->rt_op .'/'.$dat->rw_op,
                                        "kelurahan_op" =>$dat->kelurahan_op,
                                        "kecamatan_op" =>$dat->kecamatan_op,
                                        "kotakab_op" =>$dat->kotakab_op,

                                        "luas_bumi_bangunan_op" =>$dat->luas_bumi_op .'/'.$dat->luas_bagunan_op,

                                        "nilai" =>$dat->nilai,
                                        "denda" =>$dat->denda,
                                        "total" =>$dat->total,
                                        "status_bayar" =>$dat->status_bayar
                                    );
                                }


                                // var_dump($data);die;

                                //Cek apakah data ada atau gak
                                if ($data) {

                                    $response['code']='10';
                                    $bills = array("bills"=>$hasil);
                                    $response['data']=$bills;
    
                                } else {
                                    $response['code']='08';
                                    $response['description']='Data tidak ditemukan, Mohon cek kembali NOP atau Tahun';
                                }


                            }else{
                                $response['code']='07';
                                $response['description']='NOP dan Tahun tidak sesuai format, hanya boleh angka';
                            }


                        }else{
                            $response['code']='07';
                                $response['description']='NOP dan Tahun tidak sesuai format, hanya boleh angka';
                        }

                        

                    }else{

                        $nops = is_numeric($nop);
                        $tahuns = is_numeric($tahun);
                        if($nops == 1 && $tahuns == 1){
                            $pajangkode = '';
                            $panjangkode = strlen((string)$nop);
                            $panjangtahun = '';
                            $panjangtahun = strlen((string)$tahun);

                            
                            if($panjangkode == 18 && $panjangtahun == 4){
                                // $response['code']='berhasil';
                                // $sql = "SELECT NOP as nop, SPPT_TAHUN_PAJAK as tahun_pajak, WP_NAMA as nama_wajib_pajak, WP_ALAMAT as alamat_wajib_pajak, IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar FROM pbb_sppt where NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";
                                // $data = $this->db3->query($sql)->result();

                                // var_dump($data);die;



                                $tgl = date("Y-m-d");
                                $response['code']='berhasil';
                                $sql = "SELECT NOP as nop, SPPT_TAHUN_PAJAK as tahun_pajak, WP_NAMA as nama_wajib_pajak, WP_ALAMAT as alamat_wajib_pajak, WP_RT as rt_wajib_pajak, WP_RW as rw_wajib_pajak, WP_KELURAHAN as kelurahan_wajib_pajak, WP_KECAMATAN as kecamatan_wajib_pajak, WP_KOTAKAB as kotakab_wajib_pajak, OP_ALAMAT as alamat_op, OP_RT as rt_op, OP_RW as rw_op, OP_KELURAHAN as kelurahan_op, OP_KECAMATAN as kecamatan_op, OP_KOTAKAB as kotakab_op, OP_LUAS_BUMI as luas_bumi_op, OP_LUAS_BANGUNAN as luas_bagunan_op,
                                SPPT_PBB_HARUS_DIBAYAR as nilai, @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,SPPT_TANGGAL_JATUH_TEMPO,'".$tgl."')/30) dendaBulan,
                                @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
                                @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
                                @denda := FLOOR((2/100)*@dendaBulan*SPPT_PBB_HARUS_DIBAYAR) as denda, (@denda+SPPT_PBB_HARUS_DIBAYAR) as total, IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar FROM pbb_sppt where NOP='$nop'  AND SPPT_TAHUN_PAJAK='$tahun'";

                                // $nilai = $data[0]->SPPT_PBB_HARUS_DIBAYAR;
                                // $denda = $data[0]->SPPT_DENDA;
                                // $total = $nilai + $denda;

                                $data = $this->db3->query($sql)->result();
                                foreach ($data as $dat){
                                    $hasil[] = array(
                                        "nop" =>$dat->nop,
                                        "tahun_pajak" =>$dat->tahun_pajak,
                                        "nama_wp" =>$dat->nama_wajib_pajak,
                                        "alamat_wp" =>$dat->alamat_wajib_pajak,

                                        "rt_rw_wp" =>$dat->rt_wajib_pajak .'/'.$dat->rw_wajib_pajak,
                                        "kelurahan_wp" =>$dat->kelurahan_wajib_pajak,
                                        "kecamatan_wp" =>$dat->kecamatan_wajib_pajak,
                                        "kotakab_wp" =>$dat->kotakab_wajib_pajak,

                                        "alamat_op" =>$dat->alamat_op,
                                        "rt_rw_op" =>$dat->rt_op .'/'.$dat->rw_op,
                                        "kelurahan_op" =>$dat->kelurahan_op,
                                        "kecamatan_op" =>$dat->kecamatan_op,
                                        "kotakab_op" =>$dat->kotakab_op,

                                        "luas_bumi_bangunan_op" =>$dat->luas_bumi_op .'/'.$dat->luas_bagunan_op,

                                        "nilai" =>$dat->nilai,
                                        "denda" =>$dat->denda,
                                        "total" =>$dat->total,
                                        "status_bayar" =>$dat->status_bayar
                                    );
                                }

                                //Cek apakah data ada atau gak
                                if ($data) {
        
                                    if($data[0]->status_bayar == 'LUNAS'){
    
                                        $response['code']='99';
                                        $response['description']= 'NOP ' . $nop .' Atas Nama ' . $data[0]->nama_wajib_pajak . ' SUDAH LUNAS';
                                    }else{
                                        $response['code']='00';
                                        $bills = array("bills"=>$hasil);
										$response['data']=$bills;
                                    }
    
                                } else {
                                    $response['code']='08';
                                    $response['description']='Data tidak ditemukan, Mohon cek kembali NOP atau Tahun';
                                }


                            }else{
                                $response['code']='07';
                                $response['description']='NOP atau Tahun tidak sesuai format, hanya boleh angka';
                            }


                        }else{
                            $response['code']='07';
                                $response['description']='NOP atau Tahun tidak sesuai format, hanya boleh angka';
                        }

                    }

                }else{
                    $response['code']='09';
                    $response['description']='NOP masih kosong';
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
        
    }

}
