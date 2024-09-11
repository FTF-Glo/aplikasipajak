<?php
require_once("../inc/PBB/dbUtils.php");

$dbUtils = new dbUtils(null);

// define("DBTYPE_MYSQL",1);
// define("DBTYPE_POSTGRESSQL",0);
// define("DBTYPE",DBTYPE_MYSQL);
// define("DBHOST","127.0.0.1");
// define("DBPORT","3306");
// define("DBNAME","GW_PBB");
// define("DBUSER","gw_user");
// define("DBPWD","gw_pwd");
// define("DBTABLE","PBB_SPPT");
// define("DBNAME_SW","SW_PBB");

// define("PBB_MAXPENALTY_MONTH",24);
// define("PBB_ONE_MONTH",30);
// define("PBB_PENALTY_PERCENT",2);
define("DBTYPE_MYSQL", 1);
define("DBTYPE_POSTGRESSQL", 0);
define("DBTYPE", DBTYPE_MYSQL);
define("DBHOST", "127.0.0.1");
define("DBPORT", "3306");
define("DBNAME", "gw_pbb");
define("DBUSER", "root");
define("DBPWD", "Lamsel2@21");
define("DBTABLE", "PBB_SPPT");
define("DBNAME_SW", "sw_pbb");

define("PBB_MAXPENALTY_MONTH", 24);
define("PBB_ONE_MONTH", 0);
define("PBB_PENALTY_PERCENT", 1);

// define("KABKOTA",'Base Area');
// define("NAMA_KASI",'ASEP DESI SUSANTO, S. Sos');
// define("NIP_KASI",'19761206 200801 1 006');

$ALLOWEDCLIENT = array();
$ALLOWEDCLIENT["3204"] = "n4f37e83a746531.97349520";

function AllowedClient($client, $area)
{
	global $ALLOWEDCLIENT;
	$bok = false;
	if (isset($ALLOWEDCLIENT[$area])) {
		return $ALLOWEDCLIENT[$area] == $client;
	}
	return $bok;
}

function DB_connect($dbhost, $dbport, $dbname, $dbuser, $dbpwd)
{
	$dbconn = false;
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		$dbconn = pg_connect("host=" . $dbhost . " port=" . $dbport . " dbname=" . $dbname . " user=" . $dbuser . " password=" . $dbpwd);
	} else {

		$dbconn = mysqli_connect($dbhost, $dbuser, $dbpwd, $dbname, $dbport);
		//mysql_select_db($dbname,$dbconn);
	}
	return $dbconn;
}

function DB_close($dbconn)
{
	$dbconn = false;
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		$dbconn = pg_close($dbconn);
	} else {
		$dbconn = mysqli_close($dbconn);
	}
	return $dbconn;
}

function DB_query($query, $dbconn)
{
	$result = false;
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		$result = pg_query($dbconn, $query);
	} else {
		$result = mysqli_query($dbconn, $query);
	}
	return $result;
}

function DB_fetch_array($result)
{
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		return pg_fetch_array($result);
	} else {
		return mysqli_fetch_array($result);
	}
}

function DB_fetch_assoc($result)
{
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		return pg_fetch_assoc($result);
	} else {
		return mysqli_fetch_assoc($result);
	}
}

function DB_escape($str, $dbconn)
{
	if (DBTYPE == DBTYPE_POSTGRESSQL) {
		return pg_escape_string($str);
	} else {
		// return mysql_real_escape_string($str, $dbconn);
		return mysqli_real_escape_string($dbconn, $str);
	}
}
function getStatusCollective($id)
{
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);
	$qry 	= "select * from " . DBNAME_SW . ".CPPMOD_COLLECTIVE_GROUP_STATUS WHERE ID =  '$id' ";
	$res = mysqli_query($dbconn, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($dbconn);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}
function getConfig($key)
{
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);
	$qry 	= "select * from " . DBNAME_SW . ".central_app_config where CTR_AC_AID = 'aPBB' and CTR_AC_KEY = '$key'";

	$res = mysqli_query($dbconn, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($dbconn);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}


function getBankCode($kode_va)
{
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);
	$query = " SELECT * FROM CDCCORE_BANK_VA WHERE CDC_VB_BANK_ID = '3601002' ";
	$result = DB_query($query, $dbconn);
	if (!$result) {
		die("Connection failed: " . mysqli_error($dbconn));
		exit;
	} else {
		$row = DB_fetch_assoc($result);
		return $row;
	}
}
function cekBankCode($kode_va)
{
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);
	$query = " SELECT * FROM CDCCORE_BANK_VA WHERE CDC_VB_CODE = '$kode_va' ";
	$result = DB_query($query, $dbconn);
	if (!$result) {
		die("Connection failed: " . mysqli_error($dbconn));
		exit;
	} else {
		return true;
	}
}
function cetakSTTSCollective($kode_va, $kode_bayar, $id_transaksi)
{
	// echo "masuk";
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);

	if (cekBankCode($kode_va)) {
		$query = "SELECT G.*,S.STATUS_NAME STATUS_NAME FROM CPPMOD_COLLECTIVE_GROUP AS G INNER JOIN CPPMOD_COLLECTIVE_GROUP_STATUS  S ON G.CPM_CG_STATUS = S.ID WHERE CPM_CG_PAYMENT_CODE = '$kode_bayar'  AND CPM_CG_STATUS='2'
				and CPM_CG_PAYMENT_CODE = '$id_transaksi'
			 ";
		//  echo $query;
		#AND CPM_CG_PAY_ID = '$id_transaksi'
		$result = DB_query($query, $dbconn);
		if (!$result) {
			die("Connection failed: " . mysqli_error($dbconn));
			exit;
		}
		// print_r($result);


	}
	// echo $query;
	// exit;
	$i = 0;
	while ($row = DB_fetch_assoc($result)) {
		$data[] = $row;
		$jatuhtempo	= $row["SPPT_TANGGAL_JATUH_TEMPO"];
		$dtjatuhtempo	= mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
		$dtnow		= time();
		$dayinterval	= ceil(($dtnow - $dtjatuhtempo) / (24 * 60 * 60));
		$monthinterval = ceil($dayinterval / PBB_ONE_MONTH);
		if ($monthinterval < 0) {
			$monthinterval = 0;
		} else {
			$monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
		}
		if ($row["PAYMENT_FLAG"] != 1) {
			$denda 			= floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $row["SPPT_PBB_HARUS_DIBAYAR"]);
			$denda_formated	= number_format(floor($denda), 0, ",", ".");
			$pbb_plus_denda = number_format(floor($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"])), 0, ",", ".");
			$pbb_plus_denda_xls	= floor($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"]));
			$status 		= $status_xls = "-";
			$sum_total		+= $row["SPPT_PBB_HARUS_DIBAYAR"];
			$sum_denda		+= $denda;
		} else {
			$denda 			= $row["PBB_DENDA"];
			$denda_formated	= number_format($row["PBB_DENDA"], 0, ",", ".");
			$pbb_plus_denda = $pbb_plus_denda_xls = 0;
			$status 		= "<b>LUNAS</b><i> " . $row["PAYMENT_PAID"] . "</i>";
			$status_xls		= "LUNAS " . $row["PAYMENT_PAID"];
		}
		// getStatusCollective
		// $data[$i]['CPM_CG_NOP_NUMBER'] 			= $row['CPM_CG_NOP_NUMBER'];
		// $data[$i]['DENDA'] 						= $denda_formated;
		// $data[$i]['DENDA_XLS'] 					= $denda;
		// $data[$i]['DENDA_PLUS_PBB'] 			= $pbb_plus_denda;
		// $data[$i]['DENDA_PLUS_PBB_XLS'] 		= $pbb_plus_denda_xls;
		// $data[$i]['STATUS'] 					= $status;
		// $data[$i]['STATUS_XLS'] 				= $status_xls;
		// $data[$i]['SPPT_PBB_HARUS_DIBAYAR_XLS'] = $data[$i]['SPPT_PBB_HARUS_DIBAYAR'];
		// $data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'],0,",",".");
		// $data[$i]['SUM_DENDA_XLS'] 				= ($sum_denda!=0 ? $sum_denda : 0);
		// $data[$i]['SUM_DENDA'] 					= number_format($sum_denda,0,",",".");
		// $data[$i]['SUM_TOTAL_XLS'] 				= ($sum_total!=0 ? $sum_total : 0);
		// $data[$i]['SUM_TOTAL'] 					= number_format($sum_total,0,",",".");
		// $data[$i]['SUM_TOTAL_DENDA_PDF'] 		= number_format($sum_total+$sum_denda,0,",",".");
		$i++;
	}
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	// exit;
	return $data;
}
function cetakSTTS($kode_va, $kode_bayar, $id_transaksi)
{
	// echo "masuk";
	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);

	if (cekBankCode($kode_va)) {
		// $query = "SELECT * FROM PBB_SPPT WHERE PAYMENT_CODE = '$kode_bayar' AND PAYMENT_REF_NUMBER='$id_transaksi' ";
		$query = "SELECT * FROM PBB_SPPT WHERE PAYMENT_CODE = '$kode_bayar' AND PAYMENT_CODE='$id_transaksi' ";
		// echo $query;exit;

		$result = DB_query($query, $dbconn);
		if (!$result) {
			die("Connection failed: " . mysqli_error($dbconn));
			exit;
		}
		// print_r($result);


	}
	//echo $query;
	$i = 0;
	while ($row = DB_fetch_assoc($result)) {
		$data[] = $row;
		$jatuhtempo	= $row["SPPT_TANGGAL_JATUH_TEMPO"];
		$dtjatuhtempo	= mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
		$dtnow		= time();
		$dayinterval	= ceil(($dtnow - $dtjatuhtempo) / (24 * 60 * 60));
		$monthinterval = ceil($dayinterval / PBB_ONE_MONTH);
		if ($monthinterval < 0) {
			$monthinterval = 0;
		} else {
			$monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
		}
		if ($row["PAYMENT_FLAG"] != 1) {
			$denda 			= floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $row["SPPT_PBB_HARUS_DIBAYAR"]);
			$denda_formated	= number_format(floor($denda), 0, ",", ".");
			$pbb_plus_denda = number_format(floor($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"])), 0, ",", ".");
			$pbb_plus_denda_xls	= floor($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"]));
			$status 		= $status_xls = "-";
			$sum_total		+= $row["SPPT_PBB_HARUS_DIBAYAR"];
			$sum_denda		+= $denda;
		} else {
			$denda 			= $row["PBB_DENDA"];
			$denda_formated	= number_format($row["PBB_DENDA"], 0, ",", ".");
			$pbb_plus_denda = $pbb_plus_denda_xls = 0;
			$status 		= "<b>LUNAS</b><i> " . $row["PAYMENT_PAID"] . "</i>";
			$status_xls		= "LUNAS " . $row["PAYMENT_PAID"];
		}
		$data[$i]['DENDA'] 						= $denda_formated;
		$data[$i]['DENDA_XLS'] 					= $denda;
		$data[$i]['DENDA_PLUS_PBB'] 			= $pbb_plus_denda;
		$data[$i]['DENDA_PLUS_PBB_XLS'] 		= $pbb_plus_denda_xls;
		$data[$i]['STATUS'] 					= $status;
		$data[$i]['STATUS_XLS'] 				= $status_xls;
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR_XLS'] = $data[$i]['SPPT_PBB_HARUS_DIBAYAR'];
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'], 0, ",", ".");
		$data[$i]['SUM_DENDA_XLS'] 				= ($sum_denda != 0 ? $sum_denda : 0);
		$data[$i]['SUM_DENDA'] 					= number_format($sum_denda, 0, ",", ".");
		$data[$i]['SUM_TOTAL_XLS'] 				= ($sum_total != 0 ? $sum_total : 0);
		$data[$i]['SUM_TOTAL'] 					= number_format($sum_total, 0, ",", ".");
		$data[$i]['SUM_TOTAL_DENDA_PDF'] 		= number_format($sum_total + $sum_denda, 0, ",", ".");
		$i++;
	}
	// print_r($data);
	return $data;
}
// function GetListByNOP($nop, $idwp, $thn1, $thn2){
// 	// echo "masuk";
//       $dbconn = DB_connect(DBHOST,DBPORT,DBNAME,DBUSER,DBPWD);
// 	$nop=DB_escape($nop,$dbconn);
// 	$where="";

//        if($nop!=""){
// 		if($thn1!=""){
// 			if(($thn1<=$thn2)||($thn2=="")){
// 			$where .=" AND SPPT_TAHUN_PAJAK >= ". $thn1 ."";
// 			}else{
// 			$where .=" AND SPPT_TAHUN_PAJAK <= ". $thn1 ."";	
// 			}
// 		}
// 		if($thn2!=""){
// 			if($thn1<=$thn2){
// 			$where .=" AND SPPT_TAHUN_PAJAK <= ". $thn2 ."";
// 			}else{
// 			$where .=" AND SPPT_TAHUN_PAJAK >= ". $thn2 ."";	
// 			}
// 		}	
//            // $query = "SELECT WP_NAMA, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO, PAYMENT_FLAG, PAYMENT_PAID, PBB_DENDA FROM ".DBTABLE." where NOP='$nop' ORDER BY NOP,SPPT_TAHUN_PAJAK";
//            $query = "SELECT * FROM ".DBTABLE." where NOP='$nop'  $where ORDER BY NOP,SPPT_TAHUN_PAJAK";
//        }elseif($idwp!=""){ 
// 		if($thn1!="")
// 			$where .=" AND x.SPPT_TAHUN_PAJAK >= ". $thn1 ."";
// 		if($thn2!="")
// 			$where .=" AND x.SPPT_TAHUN_PAJAK <= ". $thn2 ."";
// 		// $query = "SELECT x.NOP,WP_NAMA, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO, PAYMENT_FLAG, PAYMENT_PAID, PBB_DENDA FROM PBB_SPPT x, NOP_IDWP y where y.ID_WP='$idwp' AND x.NOP=y.NOP ORDER BY x.NOP,SPPT_TAHUN_PAJAK";
// 		$query = "SELECT x.*,y.* FROM PBB_SPPT x, NOP_IDWP y where y.ID_WP='$idwp' AND x.NOP=y.NOP  $where ORDER BY x.NOP,SPPT_TAHUN_PAJAK";
// 	}
// 	//echo $query;
// 	$result = DB_query( $query,$dbconn);
// 	if (!$result) {
// 		die("Connection failed: " . mysqli_error($dbconn)); exit;
// 	}
// 	$i=0;
// 	while($row = DB_fetch_assoc($result)){
// 		$data[] = $row;
// 		$jatuhtempo	= $row["SPPT_TANGGAL_JATUH_TEMPO"];
// 		$dtjatuhtempo	= mktime(23,59,59,substr($jatuhtempo,5,2),substr($jatuhtempo,8,2),substr($jatuhtempo,0,4));
// 		$dtnow		= time();
// 		$dayinterval	= ceil(($dtnow-$dtjatuhtempo)/(24*60*60));
// 		$monthinterval= ceil($dayinterval/PBB_ONE_MONTH);
// 		if($monthinterval<0) { 
// 			$monthinterval=0;
// 		} else {
// 			$monthinterval=$monthinterval>=PBB_MAXPENALTY_MONTH?PBB_MAXPENALTY_MONTH:$monthinterval;
// 		}
// 		if($row["PAYMENT_FLAG"]!=1){
// 			$denda 			= floor((PBB_PENALTY_PERCENT/100)*$monthinterval*$row["SPPT_PBB_HARUS_DIBAYAR"]);
// 			$denda_formated	= number_format(floor($denda),0,",",".");
// 			$pbb_plus_denda = number_format(floor($denda+floatval($row["SPPT_PBB_HARUS_DIBAYAR"])),0,",",".");
// 			$pbb_plus_denda_xls	= floor($denda+floatval($row["SPPT_PBB_HARUS_DIBAYAR"]));
// 			$status 		= $status_xls = "-";
// 			$sum_total		+= $row["SPPT_PBB_HARUS_DIBAYAR"];
// 			$sum_denda		+= $denda;
// 		}else{
// 			$denda 			= $row["PBB_DENDA"];
// 			$denda_formated	= number_format($row["PBB_DENDA"],0,",",".");
// 			$pbb_plus_denda = $pbb_plus_denda_xls = 0;
// 			$status 		= "<b>LUNAS</b><i> ".$row["PAYMENT_PAID"]."</i>";
// 			$status_xls		= "LUNAS ".$row["PAYMENT_PAID"];
// 		}
// 		$data[$i]['DENDA'] 						= $denda_formated;
// 		$data[$i]['DENDA_XLS'] 					= $denda;
// 		$data[$i]['DENDA_PLUS_PBB'] 			= $pbb_plus_denda;
// 		$data[$i]['DENDA_PLUS_PBB_XLS'] 		= $pbb_plus_denda_xls;
// 		$data[$i]['STATUS'] 					= $status;
// 		$data[$i]['STATUS_XLS'] 				= $status_xls;
// 		$data[$i]['SPPT_PBB_HARUS_DIBAYAR_XLS'] = $data[$i]['SPPT_PBB_HARUS_DIBAYAR'];
// 		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'],0,",",".");
// 		$data[$i]['SUM_DENDA_XLS'] 				= ($sum_denda!=0 ? $sum_denda : 0);
// 		$data[$i]['SUM_DENDA'] 					= number_format($sum_denda,0,",",".");
// 		$data[$i]['SUM_TOTAL_XLS'] 				= ($sum_total!=0 ? $sum_total : 0);
// 		$data[$i]['SUM_TOTAL'] 					= number_format($sum_total,0,",",".");
// 		$data[$i]['SUM_TOTAL_DENDA_PDF'] 		= number_format($sum_total+$sum_denda,0,",",".");
// 		$i++;
// 	}
// 	return $data;
// }
function GetListByNOP($nop, $idwp, $thn1, $thn2)
{
	////echo "GetListByNOP<br>";
	// if (SERVICETYPE != TYPE_QS) {
	if (true) {
		return GetListByNOPDB($nop, $idwp, $thn1, $thn2);
	} else {
		return GetListByNOPQS($nop, $idwp, $thn1, $thn2);
	}
}

function GetListByNOPDB($nop, $idwp, $thn1, $thn2)
{
	////echo "GetListByNOPDB IN<br>";
    global $dbUtils;

	$dbconn = DB_connect(DBHOST, DBPORT, DBNAME, DBUSER, DBPWD);
	$nop = DB_escape($nop, $dbconn);
	$where = "";
	if ($nop != "") {
		if ($thn1 != "")
			$where .= " AND SPPT_TAHUN_PAJAK >= " . $thn1 . "";
		if ($thn2 != "")
			$where .= " AND SPPT_TAHUN_PAJAK <= " . $thn2 . "";
		// $query = "SELECT WP_NAMA, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO, PAYMENT_FLAG, PAYMENT_PAID, PBB_DENDA FROM ".DBTABLE." where NOP='$nop' ORDER BY NOP,SPPT_TAHUN_PAJAK";
		$query = "SELECT * FROM " . DBTABLE . " where NOP='$nop'  $where AND SPPT_TAHUN_PAJAK > '2007' ORDER BY NOP,SPPT_TAHUN_PAJAK";
	} elseif ($idwp != "") {
		if ($thn1 != "")
			$where .= " AND x.SPPT_TAHUN_PAJAK >= " . $thn1 . "";
		if ($thn2 != "")
			$where .= " AND x.SPPT_TAHUN_PAJAK <= " . $thn2 . "";
		// $query = "SELECT x.NOP,WP_NAMA, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO, PAYMENT_FLAG, PAYMENT_PAID, PBB_DENDA FROM PBB_SPPT x, NOP_IDWP y where y.ID_WP='$idwp' AND x.NOP=y.NOP ORDER BY x.NOP,SPPT_TAHUN_PAJAK";
		$query = "SELECT x.*,y.* FROM PBB_SPPT x, NOP_IDWP y where y.ID_WP='$idwp' AND x.NOP=y.NOP  $where ORDER BY x.NOP,SPPT_TAHUN_PAJAK";
	}
	// print_r($query);
	// echo $query; exit;
	$result = DB_query($query, $dbconn);
	if (!$result) {
		die("Connection failede: " . mysqli_error($dbconn));
		exit;
	}
	$i = 0;
	$sum_total = 0; 
	$sum_denda = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		$data[$i] 		= $row;
		$jatuhtempo		= $row["SPPT_TANGGAL_JATUH_TEMPO"];
		$thnJatuhTempo 	= substr($jatuhtempo, 0, 4);
		$blnJatuhTempo 	= substr($jatuhtempo, 5, 2);
		$monthinterval = 0;
        
		if (PBB_ONE_MONTH == 0) {
			if ((date('Y') == $thnJatuhTempo) && (date('m') > $blnJatuhTempo)) {
				$monthinterval = date('m') - substr($jatuhtempo, 5, 2);
			} else if (date('Y') > $thnJatuhTempo) {
				$monthinterval = ((date("Y") - $thnJatuhTempo - 1) * 12) + (11 - $blnJatuhTempo) + date("m") + 1;
			}
		} else {
			$dtjatuhtempo	= mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
			$dtnow			= time();
			$dayinterval	= ceil(($dtnow-$dtjatuhtempo)/(24*60*60));
			$monthinterval	= ceil($dayinterval / PBB_ONE_MONTH);
		}

		// echo $monthinterval."<br>";

		if ($monthinterval <= 0) {
			$monthinterval = 0;
		} else {
			$monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
		}

        // aldes
        $newDenda = $dbUtils->getDenda($jatuhtempo, $row["SPPT_PBB_HARUS_DIBAYAR"], PBB_ONE_MONTH, PBB_MAXPENALTY_MONTH, PBB_PENALTY_PERCENT);

		if ($row["PAYMENT_FLAG"] != "1") {
			// $denda 			= floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $row["SPPT_PBB_HARUS_DIBAYAR"]);
			// $denda 			= round(calcPenalty($monthinterval,PBB_PENALTY_PERCENT,$row["SPPT_PBB_HARUS_DIBAYAR"]));
			// $denda 			= (calcPenalty($monthinterval,PBB_PENALTY_PERCENT,$row["SPPT_PBB_HARUS_DIBAYAR"]));
            $denda               = $newDenda;
            $denda_formated      = number_format(($denda), 0, ",", ".");
            $pbb_plus_denda      = number_format(($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"])), 0, ",", ".");
            $pbb_plus_denda_xls  = ($denda + floatval($row["SPPT_PBB_HARUS_DIBAYAR"]));
            $status              = $status_xls = "-";
            $sum_total          += $row["SPPT_PBB_HARUS_DIBAYAR"];
            $sum_denda          += $denda;

			// echo $denda.":".$denda_formated."<br>";
		} else {
			$denda 			= $row["PBB_DENDA"];
			$denda_formated	= number_format($row["PBB_DENDA"], 0, ",", ".");
			$pbb_plus_denda = 0;
            $pbb_plus_denda_xls = 0;
			$status 		= "<b>LUNAS</b><i> " . $row["PAYMENT_PAID"] . "</i>";
			$status_xls		= "LUNAS " . $row["PAYMENT_PAID"];
		}
		$data[$i]['DENDA'] 						= $denda_formated;
		$data[$i]['DENDA_XLS'] 					= $denda;
		$data[$i]['DENDA_PLUS_PBB'] 			= $pbb_plus_denda;
		$data[$i]['DENDA_PLUS_PBB_XLS'] 		= $pbb_plus_denda_xls;
		$data[$i]['STATUS'] 					= $status;
		$data[$i]['STATUS_XLS'] 				= $status_xls;
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR_XLS'] = $data[$i]['SPPT_PBB_HARUS_DIBAYAR'];
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'], 0, ",", ".");
		$data[$i]['SUM_DENDA_XLS'] 				= ($sum_denda != 0 ? number_format($sum_denda, 0, "", "") : 0);
		$data[$i]['SUM_DENDA'] 					= ($sum_denda != 0 ? number_format($sum_denda, 0, ",", ".") : 0);
		$data[$i]['SUM_TOTAL_XLS'] 				= ($sum_total != 0 ? $sum_total : 0);
		$data[$i]['SUM_TOTAL'] 					= number_format($sum_total, 0, ",", ".");
		$data[$i]['SUM_TOTAL_DENDA_PDF'] 		= number_format($sum_total + $sum_denda, 0, ",", ".");
		$i++;
	}
	////echo "GetListByNOPDB OUT<br>";

	return $data;
}

function GetListByNOPQS($nop, $idwp, $thn1, $thn2)
{
	////echo "GetListByNOPQS IN<br>";
	#INIT
	$sRequestStream = "";
	$sResp = "";

	if ($nop != "") {
		$kec = substr($nop, 0, 7);
		$kel = substr($nop, 0, 10);

		if ($thn1 != "" && $thn2 != "")
			$sRequestStream = "{\"f\":\"pbbv21.selectinquiryportlet_2nopthn\",\"PAN\":\"11000\",\"IS_VALIDATE\":0,\"UID\":\"000000000055\",\"i\":{\"NOP\":\"'$nop'\",\"KEC\":\"'$kec'\",\"KEL\":\"'$kel'\",\"THN1\":\"'$thn1'\",\"THN2\":\"'$thn2'\"}}";
		else
			$sRequestStream = "{\"f\":\"pbbv21.selectinquiryportlet_2nop\",\"PAN\":\"11000\",\"IS_VALIDATE\":0,\"UID\":\"000000000055\",\"i\":{\"NOP\":\"'$nop'\",\"KEC\":\"'$kec'\",\"KEL\":\"'$kel'\"}}";
	} elseif ($idwp != "") {
		if ($thn1 != "" && $thn2 != "")
			$sRequestStream = "{\"f\":\"pbbv21.selectinquiryportlet_2idthn\",\"PAN\":\"11000\",\"IS_VALIDATE\":0,\"UID\":\"000000000055\",\"i\":{\"WP_ID\":\"'$idwp'\",\"KEC\":\"''\",\"KEL\":\"''\",\"THN1\":\"'$thn1'\",\"THN2\":\"'$thn2'\"}}";
		else
			$sRequestStream = "{\"f\":\"pbbv21.selectinquiryportlet_2id\",\"PAN\":\"11000\",\"IS_VALIDATE\":0,\"UID\":\"000000000055\",\"i\":{\"WP_ID\":\"'$idwp'\",\"KEC\":\"''\",\"KEL\":\"''\"}}";
	}


	$bOK = GetRemoteResponse(QSHOST, QSPORT, QSTIMEOUT, $sRequestStream, $sResp);
	//////echo $bOK."<br>";
	//////echo $sResp."<br>";

	$json = new Services_JSON();
	$allData = $json->decode($sResp);
	//print_r($allData);
	if (isset($allData->o)) {
		$dt = $json->decode($allData->o);
	}
	//print_r($dt);
	$i = 0;
	$sum_total = 0;
	$sum_denda = 0;
	foreach ($dt as $row) {
		//$data[] = $row;
		$jatuhtempo	= $row->SPPT_TANGGAL_JATUH_TEMPO;
		$dtjatuhtempo	= mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
		$dtnow		= time();
		$dayinterval	= ceil(($dtnow - $dtjatuhtempo) / (24 * 60 * 60));
		$monthinterval = ceil($dayinterval / PBB_ONE_MONTH);
		if ($monthinterval < 0) {
			$monthinterval = 0;
		} else {
			$monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
		}
		if ($row->PAYMENT_FLAG != 1) {
			$denda 			= floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $row->SPPT_PBB_HARUS_DIBAYAR);
			$denda_formated	= number_format(floor($denda), 0, ",", ".");
			$pbb_plus_denda = number_format(floor($denda + floatval($row->SPPT_PBB_HARUS_DIBAYAR)), 0, ",", ".");
			$pbb_plus_denda_xls	= floor($denda + floatval($row->SPPT_PBB_HARUS_DIBAYAR));
			$status 		= $status_xls = "-";
			$sum_total		+= $row->SPPT_PBB_HARUS_DIBAYAR;
			$sum_denda		+= $denda;
		} else {
			$denda 			= $row->PBB_DENDA;
			$denda_formated	= number_format($row->PBB_DENDA, 0, ",", ".");
			$pbb_plus_denda = $pbb_plus_denda_xls = 0;
			$status 		= "<b>LUNAS</b><i> " . $row->PAYMENT_PAID . "</i>";
			$status_xls		= "LUNAS " . $row->PAYMENT_PAID;
		}

		$data[$i]['NOP'] 						= $row->NOP;
		$data[$i]['SPPT_TAHUN_PAJAK'] 			= $row->SPPT_TAHUN_PAJAK;
		$data[$i]['WP_NAMA'] 					= $row->WP_NAMA;
		$data[$i]['WP_ALAMAT'] 					= $row->WP_ALAMAT;
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= $row->SPPT_PBB_HARUS_DIBAYAR;
		$data[$i]['SPPT_TANGGAL_JATUH_TEMPO'] 	= $row->SPPT_TANGGAL_JATUH_TEMPO;
		$data[$i]['OP_ALAMAT'] 					= $row->OP_ALAMAT;
		$data[$i]['OP_KECAMATAN'] 				= $row->OP_KECAMATAN;
		$data[$i]['OP_KELURAHAN'] 				= $row->OP_KELURAHAN;
		$data[$i]['OP_LUAS_BUMI'] 				= $row->OP_LUAS_BUMI;
		$data[$i]['OP_NJOP_BUMI'] 				= $row->OP_NJOP_BUMI;
		$data[$i]['OP_LUAS_BANGUNAN'] 			= $row->OP_LUAS_BANGUNAN;
		$data[$i]['OP_NJOP_BANGUNAN'] 			= $row->OP_NJOP_BANGUNAN;
		$data[$i]['PAYMENT_FLAG'] 				= $row->PAYMENT_FLAG;
		$data[$i]['PAYMENT_PAID'] 				= $row->PAYMENT_PAID;
		$data[$i]['DENDA'] 						= $denda_formated;
		$data[$i]['DENDA_XLS'] 					= $denda;
		$data[$i]['DENDA_PLUS_PBB'] 			= $pbb_plus_denda;
		$data[$i]['DENDA_PLUS_PBB_XLS'] 		= $pbb_plus_denda_xls;
		$data[$i]['STATUS'] 					= $status;
		$data[$i]['STATUS_XLS'] 				= $status_xls;
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR_XLS'] = $data[$i]['SPPT_PBB_HARUS_DIBAYAR'];
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] 	= number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'], 0, ",", ".");
		$data[$i]['SUM_DENDA_XLS'] 				= ($sum_denda != 0 ? number_format($sum_denda, 0, "", "") : 0);
		$data[$i]['SUM_DENDA'] 					= ($sum_denda != 0 ? number_format($sum_denda, 0, ",", ".") : 0);
		$data[$i]['SUM_TOTAL_XLS'] 				= ($sum_total != 0 ? $sum_total : 0);
		$data[$i]['SUM_TOTAL'] 					= number_format($sum_total, 0, ",", ".");
		$data[$i]['SUM_TOTAL_DENDA_PDF'] 		= number_format($sum_total + $sum_denda, 0, ",", ".");
		$i++;
	};

	//////echo "GetListByNOPQS OUT<br>";

	return $data;
}
