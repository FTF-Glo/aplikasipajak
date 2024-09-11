<?php 
	$servername = "127.0.0.1";
	$username = "root";
	// $password = "";
	$password = "getpass";
	$dbname = "SW_SSB";
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}

	$usernameAccount = "bapendalampungselatan";
	$passwordAccount = "kablampungselatan-";
	$date = date("d/m/Y");
	// $date = "19/01/2021";

	$url = 'https://services.atrbpn.go.id/BPNApiService/Api/BPHTB/getDataATRBPN';
	$data = array('username' => $usernameAccount, 'password' => $passwordAccount, 'TANGGAL' => $date);

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { /* Handle error */ }

	$resultArray = json_decode($result, true);

	foreach ($resultArray['result'] as $key => $value) {
		$sql = "INSERT INTO TBL_ATRBPN (NOMOR_AKTA, 
		TANGGAL_AKTA,
		NAMA_PPAT,
		NOP,
		NTPD,
		NOMOR_INDUK_BIDANG,
		KOORDINAT_X,
		KOORDINAT_Y,
		NIK,
		NPWP,
		NAMA_WP,
		KELURAHAN_OP,
		KECAMATAN_OP,
		KOTA_OP,
		LUASTANAH_OP,
		JENIS_HAK)
		VALUES ('" .
		$value['NOMOR_AKTA'] . "','" . 
		$value['TANGGAL_AKTA'] . "','" . 
		$value['NAMA_PPAT'] . "','" . 
		$value['NOP'] . "','" . 
		$value['NTPD'] . "','" . 
		$value['NOMOR_INDUK_BIDANG'] . "','" . 
		$value['KOORDINAT_X'] . "','" . 
		$value['KOORDINAT_Y'] . "','" . 
		$value['NIK'] . "','" . 
		$value['NPWP'] . "','" . 
		$value['NAMA_WP'] . "','" . 
		$value['KELURAHAN_OP'] . "','" . 
		$value['KECAMATAN_OP'] . "','" . 
		$value['KOTA_OP'] . "','" . 
		$value['LUASTANAH_OP'] . "','" . 
		$value['JENIS_HAK'] . "')";

		if ($conn->query($sql) === TRUE) {
		// echo "New record created successfully";
		} else {
		// echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	header('Content-Type: application/json');
	
	$resultArray = json_decode(json_encode($result), true);
	echo $result;

	// var_dump($result);
?>


