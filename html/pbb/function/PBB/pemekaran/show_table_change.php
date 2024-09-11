<?php
//{"Result":"OK","Records":[{"StudentId":47,"ContinentalId":1,"CountryId":13,"CityId":46,"Name":"Agatha Wells","EmailAddress":"agatha.wells@jtable.org","Password":"123","Gender":"F","BirthDate":"\/Date(1024952400000)\/","About":"","Education":3,"IsActive":true,"RecordDate":"\/Date(1378242000000)\/"},{"StudentId":97,"ContinentalId":1,"CountryId":23,"CityId":65,"Name":"Albert Coolidge","EmailAddress":"albert.coolidge@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(-858308400000)\/","About":"","Education":2,"IsActive":false,"RecordDate":"\/Date(1400619600000)\/"},{"StudentId":98,"ContinentalId":5,"CountryId":4,"CityId":14,"Name":"Albert Einstein","EmailAddress":"albert.einstein@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(871506000000)\/","About":"","Education":1,"IsActive":false,"RecordDate":"\/Date(1364763600000)\/"},{"StudentId":79,"ContinentalId":2,"CountryId":6,"CityId":21,"Name":"Albert Leibniz","EmailAddress":"albert.leibniz@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(263682000000)\/","About":"","Education":1,"IsActive":true,"RecordDate":"\/Date(1394748000000)\/"},{"StudentId":2,"ContinentalId":2,"CountryId":2,"CityId":8,"Name":"Aldous Asimov","EmailAddress":"aldous.asimov@jtable.org","Password":"123","Gender":"F","BirthDate":"\/Date(-690343200000)\/","About":"","Education":2,"IsActive":true,"RecordDate":"\/Date(1406322000000)\/"},{"StudentId":109,"ContinentalId":1,"CountryId":10,"CityId":39,"Name":"Aldous Hegel","EmailAddress":"aldous.hegel@jtable.org","Password":"123","Gender":"F","BirthDate":"\/Date(-389934000000)\/","About":"","Education":3,"IsActive":false,"RecordDate":"\/Date(1400533200000)\/"},{"StudentId":17,"ContinentalId":1,"CountryId":18,"CityId":56,"Name":"Amin Baines","EmailAddress":"amin.baines@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(113173200000)\/","About":"","Education":1,"IsActive":true,"RecordDate":"\/Date(1376254800000)\/"},{"StudentId":4,"ContinentalId":1,"CountryId":10,"CityId":37,"Name":"Amin Gump","EmailAddress":"amin.gump@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(654901200000)\/","About":"","Education":1,"IsActive":true,"RecordDate":"\/Date(1374958800000)\/"},{"StudentId":80,"ContinentalId":2,"CountryId":3,"CityId":9,"Name":"Andrew Faulkner","EmailAddress":"andrew.faulkner@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(919980000000)\/","About":"","Education":1,"IsActive":true,"RecordDate":"\/Date(1360620000000)\/"},{"StudentId":83,"ContinentalId":1,"CountryId":10,"CityId":39,"Name":"Andrew Fowler","EmailAddress":"andrew.fowler@jtable.org","Password":"123","Gender":"M","BirthDate":"\/Date(860792400000)\/","About":"","Education":3,"IsActive":true,"RecordDate":"\/Date(1366664400000)\/"}],"TotalRecordCount":128}
$idexec = $_REQUEST['idexec'];
$start  = $_REQUEST['jtStartIndex'];
$limit  = $_REQUEST['jtPageSize'];

require_once('../../../inc/payment/inc-payment-db-c.php');
require_once('../../../inc/payment/db-payment.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$sql    = "SELECT COUNT(*) FROM cppmod_pbb_perubahan_nop A WHERE ID = $idexec ";
$result = mysqli_query($DBLink, $sql);
$row    = mysqli_fetch_array($result);
$TotalRecordCount = $row[0];
		 
$sql  = "SELECT CONCAT( SUBSTR(A.NOP_LAMA FROM 1  FOR 2), ' ', SUBSTR(A.NOP_LAMA FROM 3  FOR 2), ' ',
					    SUBSTR(A.NOP_LAMA FROM 5  FOR 3), ' ', SUBSTR(A.NOP_LAMA FROM 8  FOR 3), ' ',
					    SUBSTR(A.NOP_LAMA FROM 11 FOR 3), ' ', SUBSTR(A.NOP_LAMA FROM 14 FOR 4), '-',
					    SUBSTR(A.NOP_LAMA FROM 18 FOR 1), ' '  ) NOP_LAMA,
				CONCAT( SUBSTR(A.NOP_BARU FROM 1  FOR 2), ' ', SUBSTR(A.NOP_BARU FROM 3  FOR 2), ' ',
					    SUBSTR(A.NOP_BARU FROM 5  FOR 3), ' ', SUBSTR(A.NOP_BARU FROM 8  FOR 3), ' ',
					    SUBSTR(A.NOP_BARU FROM 11 FOR 3), ' ', SUBSTR(A.NOP_BARU FROM 14 FOR 4), '-',
					    SUBSTR(A.NOP_BARU FROM 18 FOR 1), ' ' ) NOP_BARU 
		 FROM cppmod_pbb_perubahan_nop A 
		 WHERE ID = '$idexec' LIMIT $start, $limit";
//echo $sql;exit();
// aldes, repair
$data = queryOpen($DBLink, $sql);

echo json_encode(
		array('Result' => 'OK', 
			  'Records' => $data, 
			  'TotalRecordCount' => $TotalRecordCount)
	 );
?>