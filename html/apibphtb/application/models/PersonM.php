<?php

// extends class Model
class PersonM extends CI_Model{

  // response jika field ada yang kosong
  public function empty_response(){
    $response['status']=502;
    $response['error']=true;
    $response['message']='Field tidak boleh kosong';
    return $response;
  }


 // mengambil semua data person
  public function all_person(){

    $all = $this->db->get("tb_person")->result();
    $response['status']=200;
    $response['error']=false;
    $response['message']=$all;
    return $response;

  }

  // mengambil semua data person
  public function all_person2($id){
        $id = $this->get('id');
        if ($id == '') {
            $all = $this->db->get('tb_person')->result();
            $response['status']=200;
            $response['error']=false;
            $response['message']=$all;
            return $response;
        } else {
            $this->db->where('id', $id);
            $all = $this->db->get('tb_person')->result();
            $response['status']=502;
           $response['error']=true;
           $response['message']=$all;
           return $response;
        }
        //$this->response($response, 200);
  }

    /*public function select_person($id){

    if($id == ''){
      return $this->empty_response();
    }else{
      $where = array(
        "id"=>$id
      );

      $this->db->where($where);
      $select = $this->db->select("tb_person");
      if($select){
        $response['status']=200;
        $response['error']=false;
        $response['message']='Data ditemukan.';
        return $response;
      }else{
        $response['status']=502;
        $response['error']=true;
        $response['message']='Data tidak ditemukan.';
        return $response;
      }
    }

  }*/

  // ambil data by id
  /*public function select_person($id){

    if($id == '' || empty($name) || empty($address) || empty($phone)){
      return $this->empty_response();
    }else{
      $where = array(
        "id"=>$id
      );

      $set = array(
        "name"=>$name,
        "address"=>$address,
        "phone"=>$phone
      );

      $this->db->where($where);
      $select = $this->db->select("tb_person",$set);
      if($select){
        $response['status']=200;
        $response['error']=false;
        $response['message']='Data person ok.';
        return $response;
      }else{
        $response['status']=502;
        $response['error']=true;
        $response['message']='Data person ga ok.';
        return $response;
      }
    }

  }*/


  
  // Insert data ke tabel tb_person
  public function add_person($name,$address,$phone){

    if(empty($name) || empty($address) || empty($phone)){
      return $this->empty_response();
    }else{
      $data = array(
        "name"=>$name,
        "address"=>$address,
        "phone"=>$phone
      );

      $insert = $this->db->insert("tb_person", $data);

      if($insert){
        $response['status']=200;
        $response['error']=false;
        $response['message']='Data person ditambahkan.';
        return $response;
      }else{
        $response['status']=502;
        $response['error']=true;
        $response['message']='Data person gagal ditambahkan.';
        return $response;
      }
    }

  }


  // hapus data person
  public function delete_person($id){

    if($id == ''){
      return $this->empty_response();
    }else{
      $where = array(
        "id"=>$id
      );

      $this->db->where($where);
      $delete = $this->db->delete("tb_person");
      if($delete){
        $response['status']=200;
        $response['error']=false;
        $response['message']='Data person dihapus.';
        return $response;
      }else{
        $response['status']=502;
        $response['error']=true;
        $response['message']='Data person gagal dihapus.';
        return $response;
      }
    }

  }

  // update data
  public function update_person($id,$name,$address,$phone){

    if($id == '' || empty($name) || empty($address) || empty($phone)){
      return $this->empty_response();
    }else{
      $where = array(
        "id"=>$id
      );

      $set = array(
        "name"=>$name,
        "address"=>$address,
        "phone"=>$phone
      );

      $this->db->where($where);
      $update = $this->db->update("tb_person",$set);
      if($update){
        $response['status']=200;
        $response['error']=false;
        $response['message']='Data person diubah.';
        return $response;
      }else{
        $response['status']=502;
        $response['error']=true;
        $response['message']='Data person gagal diubah.';
        return $response;
      }
    }

  }

}

?>
