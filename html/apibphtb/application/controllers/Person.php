<?php

require APPPATH . 'libraries/REST_Controller.php';

class Person extends REST_Controller{

  // construct
  public function __construct(){
    parent::__construct();
    $this->load->model('PersonM');
  }

  // method index untuk menampilkan semua data person menggunakan method get
  public function index_get(){
    $response = $this->PersonM->all_person();
    $this->response($response);
  }

  
  // hapus data person menggunakan method delete
  /*public function index_get(){
    $response = $this->PersonM->select_person(
        $this->get('id')
      );
    $this->response($response);
  }*/


  // untuk menambah person menaggunakan method post
  public function add_post(){
    $response = $this->PersonM->add_person(
        $this->post('nama'),
        $this->post('alamat'),
        $this->post('telp')
      );
    $this->response($response);
  }

  // update data person menggunakan method put
  public function update_put(){
    $response = $this->PersonM->update_person(
        $this->put('id'),
        $this->put('nama'),
        $this->put('alamat'),
        $this->put('telp')
      );
    $this->response($response);
  }

  // hapus data person menggunakan method delete
  public function delete_delete(){
    $response = $this->PersonM->delete_person(
        $this->delete('id')
      );
    $this->response($response);
  }

}

?>
