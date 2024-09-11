 <?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'Token.php';

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';


// use namespace
use Restserver\Libraries\REST_Controller;

// use Restserver\Libraries\REST_Controller;

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;



class GetInquiryPajak extends Token {

    function __construct()
    {
        parent::__construct();
        // $this->logins();
        $this->spajak = $this->load->database('default', TRUE);
        $this->gw_ssb = $this->load->database('dbphtb', TRUE);
        $this->sw_ssb = $this->load->database('sw_ssb', TRUE);
    }

    public function getToken_post(){  
        $tax_type_belakang =  $this->post('tax_type',2);
        $tax_type =  substr($tax_type_belakang,2);             
        $exp = time() + 300;
        $token = array(
            "iss" => 'apprestservice',
            "aud" => 'pengguna',
            "iat" => time(),
            "nbf" => time() + 10,
            "exp" => $exp,
            "data" => array(
                "area_code" => $this->post('area_code'),
                "tax_type" => $tax_type,
                "payment_code" =>$this->post('billing_code')
            )
        );       
        
        $jwt = JWT::encode($token, $this->configToken());exit;
        // var_dump($jwt);exit;
        $output = [
                'status' => 200,
                'message' => 'Berhasil',
                "token" => $jwt,                
                "expireAt" => $token['exp']
            ];      
        $data = array('kode'=>'200', 'pesan'=>'token', 'data'=>array('token'=>$jwt, 'exp'=>$exp));
        $this->response($data, 200 );       
}




    public function index_post()
    {
        $area_code = $this->post('area_code');
        $tax_type_belakang =  $this->post('tax_type',2);
        $id = $this->post('billing_code');
        $tax_type =  substr($tax_type_belakang,2);
        $kdbelakang = substr($id,-2);
        $kddepan = substr($id,0,-2);
        $ids = is_numeric($id);
        $panjangkode = strlen((string)$id);
        // var_dump($area_code, $tax_type,$id);
        if(empty($tax_type && $area_code && $id)){
            return $this->simple('01');
        }

        if($ids != 1){
            return $this->simple('02');
        }

        if($panjangkode >= 18){
            return $this->simple('02');
        }
        
        // echo $this->authtoken();exit;
        // var_dump($this->authtoken());exit;
            if ($this->authtoken() == 'salah'){
                return $this->response(array('response_code'=>'05', 'message'=>'Token tidak sesuai atau sudah expired', 'data'=>['']), '401');
            }
        $inquiry = $this->inquirybniSPajak($area_code,$tax_type,$id);

        if (!$inquiry) {
            return $this->response(array('response_code'=>'13', 'message'=>'ID Tidak Terdaftar'), '401');
        }



        // if($inquiry['status_bayar'] == 'LUNAS'){
        //     return $this->simple('99', 'Kode billing ' . $id .' Atas Nama ' . $inquiry['name'] . ' SUDAH LUNAS');
        // }else{
            return $this->withDataND($inquiry, '00');
        // }

    }

} 

/** REFACTORED BY ALDES DAN AAN */


// defined('BASEPATH') or exit('No direct script access allowed');

// require_once 'RestController.php';

// // use namespace
// use Restserver\Libraries\REST_Controller;

// class GetInquiryPajak extends RestController {

//     function __construct()
//     {
//         parent::__construct();
//         // $this->logins();
//         $this->spajak = $this->load->database('default', TRUE);
//         $this->gw_ssb = $this->load->database('dbphtb', TRUE);
//         $this->sw_ssb = $this->load->database('sw_ssb', TRUE);
//     }

//     public function index_post()
//     {
//         $area_code = $this->post('area_code');
//         $tax_type_belakang =  $this->post('tax_type',2);
//         $id = $this->post('billing_code');
//         $tax_type =  substr($tax_type_belakang,2);
//         $kdbelakang = substr($id,-2);
//         $kddepan = substr($id,0,-2);
//         $ids = is_numeric($id);
//         $panjangkode = strlen((string)$id);
//         // var_dump($area_code, $tax_type,$id);
//         if(empty($tax_type && $area_code && $id)){
//             return $this->simple('01');
//         }

//         if($ids != 1){
//             return $this->simple('02');
//         }

//         if($panjangkode >= 18){
//             return $this->simple('02');
//         }
        
//         if($kdbelakang == '02'){
//             $inquiry = $this->inquiryPajakBPTHB($id);
//         }else{
//             $inquiry = $this->inquirybniSPajak($area_code,$tax_type,$id);
//             // Menyusun response untuk dikembalikan kepada pengguna

  
//         }

//         if (!$inquiry) {
//             return $this->simple('03');
//         }



//         // if($inquiry['status_bayar'] == 'LUNAS'){
//         //     return $this->simple('99', 'Kode billing ' . $id .' Atas Nama ' . $inquiry['name'] . ' SUDAH LUNAS');
//         // }else{
//             return $this->withDataND($inquiry, '00');
//         // }

//     }

// }

/** REFACTORED BY Ridwan */