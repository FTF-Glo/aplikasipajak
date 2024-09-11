<?php
require_once('../../../inc/payment/inc-payment-db-c.php');
require_once('../../../inc/payment/db-payment.php');
require_once("../../../inc/payment/json.php");
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$json = new Services_JSON();
if ($id = $_REQUEST['idexec']) {
	$host = $_REQUEST['host'];
	$port = $_REQUEST['port'];
	$user = $_REQUEST['user'];
	$pass = $_REQUEST['pass'];
	$dbGWname = $_REQUEST['dbgw'];
	$dbSWname = $_REQUEST['dbsw'];
	$uname = $_REQUEST['uname']; // aldes


	/** @var mysqli $adminDBLink */

	$adminDBLink = mysqli_connect($host, $user, $pass, $dbSWname, $port);
	if (!$adminDBLink) {
		returnFailure(mysqli_error($adminDBLink));
		//mysqli_close($adminDBLink);
		exit();
	}

	//$database = mysql_select_db($dbSWname,$adminDBLink);
	// aldes, added author
	$uname = !empty($uname) ? $uname : 'admin-pbb-perubahan';

	$sql = "INSERT INTO cppmod_pbb_generate_nop SELECT A.NOP_BARU, IFNULL(A.AUTHOR, '{$uname}'), A.TGL_UPDATE FROM cppmod_pbb_perubahan_nop A WHERE A.ID = '$id'";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		returnFailure('Gagal Buat NOP');
		//mysqli_close($adminDBLink);
		exit();
	}

	$sql = "UPDATE cppmod_pbb_perubahan_nop A INNER JOIN cppmod_pbb_sppt B
				ON A.NOP_LAMA = B.CPM_NOP AND A.ID = '$id'
				SET B.CPM_NOP = A.NOP_BARU, B.CPM_OP_KECAMATAN = LEFT(A.NOP_BARU,7), B.CPM_OP_KELURAHAN = LEFT(A.NOP_BARU,10)";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='-1' where ID='$id'";
		mysqli_query($adminDBLink, $qry);
		returnFailure('Gagal Ubah Data NOP PBB SPPT');
		exit();
	}

	$sql = "UPDATE cppmod_pbb_perubahan_nop A INNER JOIN cppmod_pbb_sppt_final B
				ON A.NOP_LAMA = B.CPM_NOP AND A.ID = '$id'
				SET B.CPM_NOP = A.NOP_BARU, B.CPM_OP_KECAMATAN = LEFT(A.NOP_BARU,7), B.CPM_OP_KELURAHAN = LEFT(A.NOP_BARU,10)";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='-2' where ID='$id'";
		mysqli_query($adminDBLink, $qry);
		returnFailure('Gagal Ubah Data NOP PBB SPPT FINAL');
		//mysqli_close($adminDBLink);
		exit();
	}

	$sql = "UPDATE cppmod_pbb_perubahan_nop A INNER JOIN cppmod_pbb_sppt_susulan B
				ON A.NOP_LAMA = B.CPM_NOP AND A.ID = '$id'
				SET B.CPM_NOP = A.NOP_BARU, B.CPM_OP_KECAMATAN = LEFT(A.NOP_BARU,7), B.CPM_OP_KELURAHAN = LEFT(A.NOP_BARU,10)";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='-3' where ID='$id'";
		mysqli_query($adminDBLink, $qry);
		returnFailure('Gagal Ubah Data NOP PBB SPPT SUSULAN');
		//mysqli_close($adminDBLink);
		exit();
	}

	$sql = "UPDATE cppmod_pbb_perubahan_nop A INNER JOIN cppmod_pbb_sppt_current B
                        ON A.NOP_LAMA = B.NOP AND A.ID = '$id'
                        LEFT JOIN cppmod_tax_kecamatan C ON C.CPC_TKC_ID=LEFT(A.NOP_BARU,7)
                        LEFT JOIN cppmod_tax_kelurahan D ON D.CPC_TKL_ID=LEFT(A.NOP_BARU,10)
                        SET B.NOP = A.NOP_BARU, B.OP_KECAMATAN_KODE = LEFT(A.NOP_BARU,7), B.OP_KELURAHAN_KODE = LEFT(A.NOP_BARU,10)
                        ,B.OP_KECAMATAN=C.CPC_TKC_KECAMATAN, B.OP_KELURAHAN=D.CPC_TKL_KELURAHAN";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='-4' where ID='$id'";
		mysqli_query($adminDBLink, $qry);
		returnFailure('Gagal Ubah Data Cetakan SPPT');
		//mysqli_close($adminDBLink);
		exit();
	}

	$sql = "UPDATE cppmod_pbb_perubahan_nop A INNER JOIN " . $dbGWname . ".PBB_SPPT B
                        ON A.NOP_LAMA = B.NOP AND A.ID = '$id'
                        LEFT JOIN cppmod_tax_kecamatan C ON C.CPC_TKC_ID=LEFT(A.NOP_BARU,7)
                        LEFT JOIN cppmod_tax_kelurahan D ON D.CPC_TKL_ID=LEFT(A.NOP_BARU,10)
                        SET B.NOP = A.NOP_BARU, B.OP_KECAMATAN_KODE = LEFT(A.NOP_BARU,7), B.OP_KELURAHAN_KODE = LEFT(A.NOP_BARU,10)
                        ,B.OP_KECAMATAN=C.CPC_TKC_KECAMATAN, B.OP_KELURAHAN=D.CPC_TKL_KELURAHAN";
	$bOk = mysqli_query($adminDBLink, $sql);
	if (!$bOk) {
		$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='-5' where ID='$id'";
		mysqli_query($adminDBLink, $qry);
		returnFailure('Gagal Ubah Data Tagihan SPPT');
		//mysqli_close($adminDBLink);
		exit();
	}
	$qry = "UPDATE cppmod_pbb_perubahan_nop SET STATUS='1' where ID='$id'";
	mysqli_query($adminDBLink, $qry);


	//mysqli_close($adminDBLink);
	returnSuccess();
} else {
	$q                      = $_REQUEST['q'];
	$jenis                  = $_REQUEST['jenis'];
	$blok_akhir_lama   	= $_REQUEST['blok_akhir_lama'] * 1;
	if ($blok_akhir_lama < 10) $blok_akhir_lama = "00$blok_akhir_lama";
	else if ($blok_akhir_lama < 100) $blok_akhir_lama = "0$blok_akhir_lama";
	$blok_awal_lama   	= $_REQUEST['blok_awal_lama'] * 1;
	if ($blok_awal_lama < 10) $blok_awal_lama = "00$blok_awal_lama";
	else if ($blok_awal_lama < 100) $blok_awal_lama = "0$blok_awal_lama";
	$blok_baru   		= $_REQUEST['blok_baru'] * 1;
	if ($blok_baru < 10) $blok_baru = "00$blok_baru";
	else if ($blok_baru < 100) $blok_baru = "0$blok_baru";
	$kec_baru   		= $_REQUEST['kec_baru'];
	$kec_lama   		= $_REQUEST['kec_lama'];
	$kel_baru         	= $_REQUEST['kel_baru'];
	$kel_lama               = $_REQUEST['kel_lama'];
	$urut_akhir_lama  	= $_REQUEST['urut_akhir_lama'] * 1;
	$urut_awal_lama   	= $_REQUEST['urut_awal_lama'] * 1;

	if ($urut_akhir_lama < 10)       $urut_akhir_lama = "000$urut_akhir_lama";
	else if ($urut_akhir_lama < 100) $urut_akhir_lama = "00$urut_akhir_lama";
	else if ($urut_akhir_lama < 1000) $urut_akhir_lama = "0$urut_akhir_lama";

	if ($urut_awal_lama < 10)       $urut_awal_lama = "000$urut_awal_lama";
	else if ($urut_awal_lama < 100) $urut_awal_lama = "00$urut_awal_lama";
	else if ($urut_awal_lama < 1000) $urut_awal_lama = "0$urut_awal_lama";

	if (!$blok_akhir_lama) $blok_akhir_lama = $blok_awal_lama;

	$author          = $_REQUEST['author']; // aldes

	$sql = "SELECT MAX(B.ID) FROM cppmod_pbb_perubahan_nop B";
	$q   = mysqli_query($DBLink, $sql);
	$r = mysqli_fetch_array($q);
	$id = $r[0] + 1;

	$sqlTable = "((SELECT CPM_NOP FROM cppmod_pbb_sppt) UNION
					  (SELECT CPM_NOP FROM cppmod_pbb_sppt_final) UNION
					  (SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan)) ";

	if ($jenis == 1) {
		// aldes, added author
		$sql = "INSERT INTO cppmod_pbb_perubahan_nop 
					(SELECT DISTINCT '$id', '$jenis', A.CPM_NOP NOP_LAMA, CONCAT('$kel_baru',RIGHT(A.CPM_NOP,8)), NOW(),'0', '{$author}' FROM $sqlTable A WHERE A.CPM_NOP LIKE '$kel_lama%')";
	} else if ($jenis == 2) {
		// aldes, added author
		mysqli_query($DBLink, "SET @cnt := (SELECT MAX(SUBSTR(A.CPM_NOP FROM 11 FOR 3)) FROM cppmod_pbb_generate_nop A WHERE A.CPM_NOP LIKE '$kel_baru%')");
		mysqli_query($DBLink, "SET @cnt := IF(@cnt IS NULL,0,@cnt)");

		$sql = "INSERT INTO cppmod_pbb_perubahan_nop (
						SELECT DISTINCT '$id', '$jenis', Y.CPM_NOP, CONCAT('$kel_baru',X.BLOK_BARU,RIGHT(Y.CPM_NOP,5)), NOW(),'0', '{$author}' 
						FROM (
							SELECT A.KELURAHAN, A.BLOK_LAMA,  
								   IF(A.BLOK_BARU < 10,CONCAT('00',A.BLOK_BARU),IF(A.BLOK_BARU < 100,CONCAT('0',A.BLOK_BARU),BLOK_BARU)) BLOK_BARU
							FROM(
								SELECT AA.BLOK_LAMA, (@cnt := @cnt +1) BLOK_BARU, AA.KELURAHAN
								FROM (SELECT SUBSTR(AAA.CPM_NOP FROM 11 FOR 3) BLOK_LAMA, LEFT(AAA.CPM_NOP,10)KELURAHAN  FROM $sqlTable AAA WHERE AAA.CPM_NOP LIKE '$kel_lama%' GROUP BY BLOK_LAMA) AA
								WHERE AA.BLOK_LAMA >= '$blok_awal_lama' AND AA.BLOK_LAMA <= '$blok_akhir_lama'							
							) A
						) X INNER JOIN $sqlTable Y ON LEFT(Y.CPM_NOP,10) = X.KELURAHAN  AND SUBSTR(Y.CPM_NOP FROM 11 FOR 3) = X.BLOK_LAMA
					)";
	} else if ($jenis == 3) {
		// aldes, added author
		mysqli_query($DBLink, " SET @cnt := (SELECT MAX(SUBSTR(A.CPM_NOP FROM 14 FOR 4)) FROM cppmod_pbb_generate_nop A WHERE A.CPM_NOP LIKE '$kel_baru$blok_baru%'); ");
		mysqli_query($DBLink, " SET @cnt := IF(@cnt IS NULL, 0, @cnt) ");
		$sql = "INSERT INTO cppmod_pbb_perubahan_nop (
						SELECT DISTINCT '$id','3',X.CPM_NOP, CONCAT('$kel_baru$blok_baru',X.NOP_BARU,RIGHT(X.CPM_NOP,1)), NOW(),'0', '{$author}' 
						FROM (
							SELECT A.CPM_NOP, IF(A.NOP_BARU < 10, CONCAT('000',A.NOP_BARU), IF(A.NOP_BARU <100,CONCAT('00',A.NOP_BARU), IF(A.NOP_BARU<1000, CONCAT('0',A.NOP_BARU), A.NOP_BARU))) NOP_BARU
							FROM (
								SELECT A.CPM_NOP, (@cnt:=@cnt+1) NOP_BARU
								FROM   $sqlTable A
								WHERE  LEFT(A.CPM_NOP,13) >= '$kel_lama$blok_awal_lama' AND LEFT(A.CPM_NOP,13) <= '$kel_lama$blok_akhir_lama'
							) A
						) X
					);";
	} else if ($jenis == 4) {
		// aldes, added author
		mysqli_query($DBLink, "SET @cnt := (SELECT MAX(SUBSTR(A.CPM_NOP FROM 14 FOR 4)) FROM cppmod_pbb_generate_nop A WHERE A.CPM_NOP LIKE '$kel_baru$blok_baru%')") or die(mysqli_error($DBLink));
		mysqli_query($DBLink, "SET @cnt := IF(@cnt IS NULL, 0, @cnt)") or die(mysqli_error($DBLink));
		$sql = "INSERT INTO cppmod_pbb_perubahan_nop (
						SELECT DISTINCT '$id','4',X.CPM_NOP, CONCAT('$kel_baru$blok_baru',X.NOP_BARU,RIGHT(X.CPM_NOP,1)), NOW(),'0', '{$author}' 
						FROM (
							SELECT A.CPM_NOP, IF(A.NOP_BARU < 10, CONCAT('000',A.NOP_BARU), IF(A.NOP_BARU <100,CONCAT('00',A.NOP_BARU), IF(A.NOP_BARU<1000, CONCAT('0',A.NOP_BARU), A.NOP_BARU))) NOP_BARU
							FROM (
								SELECT A.CPM_NOP, (@cnt:=@cnt+1) NOP_BARU
								FROM   $sqlTable A
								WHERE  LEFT(A.CPM_NOP,17) >= '$kel_lama$blok_awal_lama$urut_awal_lama' AND LEFT(A.CPM_NOP,17) <= '$kel_lama$blok_awal_lama$urut_akhir_lama'
							) A
						) X
					);";
					
		
	}
	mysqli_query($DBLink, $sql) or die(mysqli_error($DBLink));
	echo $id;
}


function returnSuccess()
{
	global $json;

	$response = array();
	$response['r'] = true;

	$val = $json->encode($response);
	echo $val;
}

function returnFailure($message)
{
	global $json;

	$response = array();
	$response['r'] = false;
	$response['m'] = $message;

	$val = $json->encode($response);
	echo $val;
}
