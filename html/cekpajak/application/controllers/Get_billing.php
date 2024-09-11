<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Get_billing extends CI_Controller {
	public function getpajak(){
		PHPINFO();
		$server = '103.76.172.165';
		$username = 'iprotax';
		$password = 'iprotax';
		$database = 'IPROTAX';
		// Connect to MSSQL
		$link = mssql_connect($server, 'iprotax', 'iprotax');

		if (!$link) {
		    die('Something went wrong while connecting to MSSQL');
		}
		$this->load->library('curl');
		$result = $this->curl->simple_get('http://example.com/');
		var_dump($result);
	}
}