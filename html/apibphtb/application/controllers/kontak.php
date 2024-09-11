<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
//require APPPATH . '/libraries/REST_Controller.php';
//use Restserver\Libraries\REST_Controller;


class Kontak extends REST_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        $this->load->database();
    }

    //Menampilkan data kontak
    function index_get() {
        $id = $this->get('id');
        if ($id == '') {
            //$kontak = $this->db->get('tb_person')->result();
            $response['status']=502;
            $response['error']=true;
            $response['message']='NOP tidak boleh kosong,silakan cek kembali';
            //return $response;
        } else {
            //$this->db->where('id', $id);
            //$response = $this->db->get('tb_person')->result();

            $sql = "SELECT id,nama,alamat,telp From tb_person WHERE id='$id'";
            $response2 = $this->db->query($sql)->result();

            $response['status']=200;
            $response['error']=false;
            $response['message']=$response2;
        }
        $this->response($response, 200);
    }


        
        



    //Mengirim atau menambah data kontak baru
    function index_post() {
        $data = array(
                    'id'           => $this->post('id'),
                    'nama'          => $this->post('nama'),
                    'alamat'    => $this->post('alamat'));
        $insert = $this->db->insert('tb_person', $data);
        if ($insert) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    //Memperbarui data kontak yang telah ada
    function index_put() {
        $id = $this->put('id');
        $data = array(
                    'id'       => $this->put('id'),
                    'nama'          => $this->put('nama'),
                    'alamat'    => $this->put('alamat'));
        $this->db->where('id', $id);
        $update = $this->db->update('tb_person', $data);
        if ($update) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    //Menghapus salah satu data kontak
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


    //Masukan function selanjutnya disini
    //Masukan function selanjutnya disini
    //Masukan function selanjutnya disini

}
?>