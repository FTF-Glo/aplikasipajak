<?php
/* 
 *  Print SSPD - BPHTB
 *  Author By ardi@vsi.co.id
 *  06-12-2016
 */
 
function getAuthor($uname) {
    global $DBLink, $appID;
    $id = $appID;
    $qry = "select nm_lengkap from tbl_reg_user_notaris where userId = '" . $uname . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo mysqli_error($DBLink);
    }

    $num_rows = mysqli_num_rows($res);
    if ($num_rows == 0)
        return $uname;
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['nm_lengkap'];
    }
}

function getConfigValue($key) {
    global $DBLink, $appID;
    $id = $appID;
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function mysql2json($mysql_result, $name) {
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getData($iddoc) {
    global $data, $DBLink, $dataNotaris;
    $query = sprintf("SELECT * , DATE_FORMAT(A.CPM_SSB_CREATED, '%%d-%%m-%%Y') as COM_SSB_CREATED,
					DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'", getConfigValue('TENGGAT_WAKTU'), $iddoc);
	$res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $dataNotaris = $json->decode(mysql2json($res, "data"));
    $dt = $dataNotaris->data[0];
    
    return $dt;
}

function getNOKTP($noktp, $nop) {
    global $DBLink;
    $day = getConfigValue("BATAS_HARI_NPOPTKP");
    $qry = "select max(CPM_SSB_CREATED) as mx from cppmod_ssb_doc  where 
	CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
	and CPM_OP_NOMOR <> '{$nop}'";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        print_r($qry);
        return false;
    }

    if (mysqli_num_rows($res)) {
        $num_rows = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row["mx"]) {

                return true;
            }
        }
    }
    return false;
}

function getDocId($a,$nama){
     
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
	$dbLimit = getConfigValue('TENGGAT_WAKTU');
	// Connect to lookup database
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	$query2 = "select id_ssb from $dbTable where wp_nama='".$nama."'";
	$r = mysqli_query($DBLinkLookUp, $query2);
	if ($r === false) {
		echo "Error select1:" . $query2;
		die("Error");
	}else{
		$hasil = mysqli_fetch_array($r);
		$dok = str_pad($hasil['id_ssb'],8,'0',STR_PAD_LEFT);
	}
	
	return $dok;
}
?>
