<?php
date_default_timezone_set('Asia/Jakarta');

// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class Percobaan extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
	}
    public function index(){    	
	    $curl = curl_init();
	    // curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
	    $orderdate = explode('-', "2021-01-01".'-'.'-');
	    $year = $orderdate[0];
	    $month   = $orderdate[1];
	    $day  = $orderdate[2];
	  
	    curl_setopt_array($curl, array(
	      CURLOPT_URL => 'https://services.atrbpn.go.id/BpnApiService/api/BPHTB/getDataATRBPN',
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => '',
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 0,
	      CURLOPT_FOLLOWLOCATION => true,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => 'POST',
	      CURLOPT_POSTFIELDS => 'USERNAME=bapendakablampungtengah&PASSWORD=a&TANGGAL=' . $day . '%2F' . $month .'%2F' .$year,
	      CURLOPT_HTTPHEADER => array(
	        'Authorization: Basic OmFkbWlu',
	        'Content-Type: application/x-www-form-urlencoded'
	      ),
	      CURLOPT_CAINFO =>  "/var/www/html/9pajaklamteng/dashboard/application/libraries/cacert.pem",
	    ));
	    
	    $response = curl_exec($curl);
	    $error = curl_error($curl);
	    curl_close($curl);
	    $response;
	    
	    $data = json_decode($response, true);
	    var_dump($data);
    }
    public function nih(){
	    $curl = curl_init();
	    // curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
	    $orderdate = explode('-', date("Y-m-d").'-'.'-');
	    $year = $orderdate[0];
	    $month   = $orderdate[1];
	    $day  = $orderdate[2];
	  
	    curl_setopt_array($curl, array(
	      CURLOPT_URL => 'http://103.76.172.162:8090/dashboardapi/index.php/Rest_Api/getdata_9pajak',
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => '',
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 0,
	      CURLOPT_FOLLOWLOCATION => true,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => 'GET',
	      CURLOPT_CAINFO =>  "/var/www/html/9pajaklamteng/dashboard/application/libraries/cacert.pem",
	    ));
	    
	    $response = curl_exec($curl);
	    $error = curl_error($curl);
	    curl_close($curl);
	    $response;
	    
	    $data = json_decode($response, true);
	    var_dump($data);
    }
}