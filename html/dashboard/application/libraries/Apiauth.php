<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Apiauth
{
    function __construct(){
        $this->apilamteng = 'http://103.76.172.162:8090/dashboardapi/index.php/Rest_Api/';
    }
    public function pajakalldata($funct){
        $CI =& get_instance();
        $CI->load->library('curl');
        $getdata = json_decode($CI->curl->simple_get($this->apilamteng.'/'.$funct));

        return $getdata;
    }
}
