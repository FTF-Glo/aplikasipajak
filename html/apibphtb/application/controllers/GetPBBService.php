<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
//require APPPATH . '/libraries/REST_Controller.php';
//use Restserver\Libraries\REST_Controller;


class GetPBBService extends REST_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        $this->load->database();
        $this->pbb = $this->load->database('pbb', TRUE);
    }

    //GET (TAMPILKAN DATA)
    function index_get() {
        $response['status']=502;
        $response['error']=true;
        $response['message']='METHOD GET BELUM TERSEDIA.!';

        /*$nop = $this->get('nop');
        $ntpd = $this->get('ntpd');

        if($nop == ""){
            $response['status']=502;
            $response['error']=true;
            $response['message']='NOP harus di isi';
        }elseif($ntpd == ""){
            $response['status']=502;
            $response['error']=true;
            $response['message']='NTPD harus di isi';
        }else{
            $sql = "SELECT
            ssb.op_nomor AS NOP,
            ssb.wp_noktp AS NIK,
            ssb.wp_nama AS NAMA,
            ssb.wp_alamat AS ALAMAT,
            ssb.op_kelurahan AS KELURAHAN_OP,
            ssb.op_kecamatan AS KECAMATAN_OP,
            ssb.op_kabupaten AS KOTA_OP,
            ssb.op_luas_tanah AS LUASTANAH,
            ssb.op_luas_bangunan AS LUASBANGUNAN,
            ssb.bphtb_dibayar AS PEMBAYARAN,
            IF(ssb.payment_flag=1, 'Y', 'T') AS `STATUS`,
            DATE_FORMAT(ssb.payment_paid, '%d-%m-%Y') AS TANGGAL_PEMBAYARAN,
            case when id_ssb < 10 then concat('0000000',id_ssb) else 
            case when id_ssb < 100 then concat('000000',id_ssb) else 
            case when id_ssb < 1000 then concat('00000',id_ssb) else
            case when id_ssb < 10000 then concat('0000',id_ssb) else
            case when id_ssb < 100000 then concat('000',id_ssb) else
            case when id_ssb < 1000000 then concat('00',id_ssb) else
            case when id_ssb < 10000000 then concat('0',id_ssb) else
            case when id_ssb < 100000000 then concat('',id_ssb) else 
            'x' end end end end end end end end as NTPD,
            IF(ssb.payment_flag=1, 'L', 'H') AS JENISBAYAR
            FROM ssb WHERE op_nomor='$nop' and id_ssb='$ntpd'";

            $response2 = $this->db->query($sql)->result();

            //Cek apakah data ada atau gak
            if ($response2) {
                $response['status']=200;
                $response['error']=false;
                $response['result']=$response2;
            } else {
                $response['status']=502;
                $response['error']=true;
                $response['message']='Data tidak ditemukan';
            }
        }*/
        $this->response($response, 200);
    }

    //POST (KIRIM ATAU TAMBAH DATA)
    function index_post() {

        $nop = $this->post('NOP');
        // $ntpd = $this->post('NTPD');

        $sql = "SELECT NOP FROM pbb_sppt WHERE NOP='$nop'";
        $sqlRun = $this->pbb->query($sql)->result();
            if (!$sqlRun) { //pake tanda seru ! artinya jika tidak ada atau tidak sama dengan
                $response['respon_code']='NOP tidak ditemukan';
                $this->response($response, 200);
            } 
    
        if($nop == ""){
            $response['respon_code']='NOP harus diisi';
        }else{
            $sql = "SELECT
             
            NOP,
            ID_WP AS NIK,
            wp_nama AS WP_NAMA,
            OP_ALAMAT AS ALAMAT_OP,
            OP_KECAMATAN AS KECAMATAN_OP,
            OP_KELURAHAN AS KELURAHAN_OP,
            OP_KOTAKAB AS KOTA_OP,
            OP_LUAS_BUMI AS LUASTANAH_OP,
            OP_LUAS_BANGUNAN AS LUASBANGUNAN_OP,
            OP_NJOP_BUMI AS NJOP_TANAH_OP,
            OP_NJOP_BANGUNAN AS NJOP_BANGUNAN_OP,         
            IF(payment_flag = 1, '100% LUNAS', 'BELUM LUNAS') AS STATUS_PEMBAYARAN
            FROM pbb_sppt 
           
            WHERE 
            NOP='$nop' AND 
            SPPT_TAHUN_PAJAK = ".date('Y')."
            AND payment_flag = '1'";

            $sqlRun = $this->pbb->query($sql)->result();
            // var_dump($sql);die;
            //Cek apakah data ada atau gak
            if ($sqlRun) {
                //$response['result']=$sqlRun; //ini bawa simbol [] pada result nya
                $response['result']=$sqlRun[0];
                $response['respon_code']='OK';
            } else {
                $response['respon_code']='Data tidak ditemukan';
            }
        }
        $this->response($response, 200);
    }
    

    //PUT (UBAH DATA)
    function index_put() {
        $id = $this->put('id');
        $data = array(
                    'id'       => $this->put('id'),
                    'nama'     => $this->put('nama'),
                    'alamat'   => $this->put('alamat'));
        $this->db->where('id', $id);
        $update = $this->db->update('tb_person', $data);
        if ($update) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    //DELETE (HAPUS DATA)
    function index_delete() {
        $id = $this->delete('id');
        $this->db->where('id', $id);
        $delete = $this->db->delete('tb_person');
        if ($delete) {
            $this->response(array('status' => 'success'), 201);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

}
?>
