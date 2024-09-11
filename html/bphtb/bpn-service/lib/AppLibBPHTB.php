<?php
include 'json.php';

class AppLibBPHTB{
	private $API_USER_BPN = 'bapendapekanbaru';
	private $API_PASS_BPN = 'a';
	private $API_URL_ADD_BPHTB = 'http://103.49.37.84:8080/BPNApiService/Api/BPHTB/AddBPHTB';
	private $API_URL_GET_PPAT = 'http://103.49.37.84:8080/BPNApiService/Api/BPHTB/GetPPAT';
	
	public function __construct(){}
	
	public function postDataBPHTB($transNo, $tanggal, $nop, $wpNama, $bayar, $luas){
		$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
		
		$params = array(
					'username'	=> $this->API_USER_BPN,
					'password'	=> $this->API_PASS_BPN,
					'transno'	=> $transNo,
					'tanggal'	=> $tanggal,
					'nop'		=> $nop,
					'wp_nama'	=> $wpNama,
					'bayar'		=> $bayar,
					'luas'		=> $luas
					);
		
		$options = array('http' => array(
			'method'  => 'POST',
			'content' => $json->encode($params),
			'header'=>  "Content-Type: application/json\r\n" .
						"Accept: application/json\r\n"
			)
		);
			
		$context  = stream_context_create( $options );					
		$response =  file_get_contents( $this->API_URL_ADD_BPHTB, false, $context );	
		
		header('Content-type: application/json; charset=utf-8');
		echo $response; 				
	}
	
		
    public function getPPAT($nama, $email){			
		$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
		
		$params = array(
					'username'	=> $this->API_USER_BPN,
					'password'	=> $this->API_PASS_BPN,
					'nama'		=> $nama.'%',
					'email'		=> $email
					);


		$options = array('http' => array(
			'method'  => 'POST',
			'content' => $json->encode($params),
			'header'=>  "Content-Type: application/json\r\n" .
						"Accept: application/json\r\n"
			)
		);
			
		$context  = stream_context_create( $options );					
		$response =  file_get_contents( $this->API_URL_GET_PPAT, false, $context );	
		
		header('Content-type: application/json; charset=utf-8');
		echo $response; 						
	}	


}
?>
