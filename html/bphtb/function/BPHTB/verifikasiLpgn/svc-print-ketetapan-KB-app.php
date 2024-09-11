<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/payment/cdatetime.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);


$dataNotaris = "";
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

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

function getConfigValue($id, $key) {
    global $DBLink, $appID;
    $id = $appID;
    //$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
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
					DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%Y-%%m-%%d') as EXPIRED
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'", getConfigValue("1", 'TENGGAT_WAKTU_KB'), $iddoc);
	//echo $query;exit;
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
    $day = getConfigValue("1", "BATAS_HARI_NPOPTKP");
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$idssb = $q->id;
$uname = $q->uname;
$draf = $q->draf;
$setuju = isset($q->setuju) ? $q->setuju : 0;

function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP) {
       
        $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
        $b = strval($h);
        $npop = 0;
        if ($b <= $a)
            $npop = $a;
        else
            $npop = $b;

        $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
        $tp = strval($p);
        if ($tp != 0)
            $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

        if ($jmlByr < 0)
            $jmlByr = 0;

        return $jmlByr;
    }
function getsudahdibayar($idssb) {
    global $DBLink;
    $qry = "select * from cppmod_ssb_doc where CPM_SSB_ID ='". $idssb ."'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$before="";
    while ($rw = mysqli_fetch_assoc($res)) {
		$NPOPTKP = strval($rw['CPM_OP_NPOPTKP']);
        $a = strval($rw['CPM_OP_LUAS_BANGUN']) * strval($rw['CPM_OP_NJOP_BANGUN']) + strval($rw['CPM_OP_LUAS_TANAH']) * strval($rw['CPM_OP_NJOP_TANAH']);
        $b = strval($rw['CPM_OP_HARGA']);
        $npop = 0;
			if ($b <= $a)
                $npop = $a;
            else
                $npop = $b;

        $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
        $tp = strval($rw['CPM_PAYMENT_TIPE_PENGURANGAN']);
            if ($tp != 0)
                $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

            $ccc =getBPHTBPayment($rw['CPM_OP_LUAS_BANGUN'], $rw['CPM_OP_NJOP_BANGUN'], $rw['CPM_OP_LUAS_TANAH'], $rw['CPM_OP_NJOP_TANAH'], $rw['CPM_OP_HARGA'], $rw['CPM_PAYMENT_TIPE_PENGURANGAN'], $rw['CPM_OP_JENIS_HAK'], $rw['CPM_OP_NPOPTKP']);
            if (($rw['CPM_PAYMENT_TIPE'] == '2') && (!is_null($rw['CPM_OP_BPHTB_TU']))) {
                $ccc = $rw['CPM_OP_BPHTB_TU'];
            }
	
	}
	$before=$ccc;
	return $before;
}
function bulanromawi($bulan)
{
$bulan_angka=array('01','02','03','04','05','06','07','08','09','10','11','12');
$bulankite=array('I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
$konversi=str_ireplace($bulan_angka,$bulankite,$bulan);
return $konversi;
}	
function getHTML($iddoc) {
    global $uname, $NOP, $setuju, $draf;

    $data = getData($iddoc);
    $jenishak = "<span class=\"document-x\">Jual Beli</span>";
    $npop = 0;
    $pwaris = "-";
    //print_r($data);
    $a = strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH);
    $b = strval($data->CPM_OP_HARGA);
    //$NPOPTKP =  getConfigValue("1",'NPOPTKP_STANDAR');
    $NPOPTKP = $data->CPM_OP_NPOPTKP;
    $typeR = $data->CPM_OP_JENIS_HAK;
    $type = $data->CPM_PAYMENT_TIPE;
    $NOP = $data->CPM_OP_NOMOR;
	$headerKB=strtoupper(getConfigValue("1", 'C_HEADER_KETETAPAN_KB'));
	$alamatKB=strtoupper(getConfigValue("1", 'C_ALAMAT_KETETAPAN_KB'));
    $c1 = "";
    $c2 = "";
    $c3 = "";
    $c4 = "";
	$nmpjbsah="";
	$nippjbsah="";
	if($data->CPM_PAYMENT_TIPE==2){
		$nmpjbsah=strtoupper(getConfigValue("1", 'NAMA_PJB_PENGESAH'));
		$nippjbsah="NIP: ". getConfigValue("1", 'NIP_PJB_PENGESAH');
		$nmpjbsubsah=strtoupper(getConfigValue("1", 'NAMA_PJB_SUB_BPHTB'));
		$nippjbsubsah="NIP: ". getConfigValue("1", 'NIP_SUB_BPHTB');
	}
	else{
		$nmpjbsah="";
		$nippjbsah="";
	}
    if ($type == '1')
        $c1 = "X";
    if ($type == '2')
        $c2 = "X";
    if ($type == '3')
        $c3 = "X";
    if ($type == '4')
        $c4 = "X";

    /* if (($typeR==4) || ($typeR==6)){
      $NPOPTKP =  getConfigValue("1",'NPOPTKP_WARIS');
      }

      if(getNOKTP($data->CPM_WP_NOKTP,$data->CPM_OP_NOMOR)) {
      $NPOPTKP = 0;
      } */


    // if ($b <= $a)
        // $npop = $a;
    // else
        // $npop = $b;
	$npop=$data->CPM_OP_NPOP;
    $n = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);
    $a = $npop - strval($NPOPTKP) > 0 ? $npop - strval($NPOPTKP) : 0;
    $m = ($a) * 0.05;
    $a = $a * 0.05;

    if ($n != 0)
        $m = $m - $m * ($n * 0.01);
    $npopkp = $npop - $NPOPTKP;
	if($npopkp<=0){
		$npopkp=0;
	}
    if ($b < 0)
        $b = 0;
    if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_BPHTB_TU))) {
        $a = $data->CPM_OP_BPHTB_TU;
        $m = $a;
    }

   if ($data->CPM_OP_JENIS_HAK == '1')
        $jenishak = "<span class=\"document-x\">01. Jual beli</span>";
    if ($data->CPM_OP_JENIS_HAK == '2')
        $jenishak = "<span class=\"document-x\">02. Tukar Menukar</span>";
    if ($data->CPM_OP_JENIS_HAK == '3')
        $jenishak = "<span class=\"document-x\">03. Hibah</span>";
    if ($data->CPM_OP_JENIS_HAK == '4')
        $jenishak = "<span class=\"document-x\">04. Hibah Wasiat</span>";
    if ($data->CPM_OP_JENIS_HAK == '5')
        $jenishak = "<span class=\"document-x\">05. Waris</span>";
    if ($data->CPM_OP_JENIS_HAK == '6')
        $jenishak = "<span class=\"document-x\">06. Pemasukan dalam perseroan</span>";
    if ($data->CPM_OP_JENIS_HAK == '7')
        $jenishak = "<span class=\"document-x\">07. APHB</span>";
    if ($data->CPM_OP_JENIS_HAK == '8')
        $jenishak = "<span class=\"document-x\">08. Lelang</span>";
    if ($data->CPM_OP_JENIS_HAK == '9')
        $jenishak = "<span class=\"document-x\">09. Putusan hakim</span>";
    if ($data->CPM_OP_JENIS_HAK == '10')
        $jenishak = "<span class=\"document-x\">10. Penggabungan usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '11')
        $jenishak = "<span class=\"document-x\">11. Peleburan usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '12')
        $jenishak = "<span class=\"document-x\">12. Pemekaran usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '13')
        $jenishak = "<span class=\"document-x\">13. Hadiah</span>";
    if ($data->CPM_OP_JENIS_HAK == '14')
        $jenishak = "<span class=\"document-x\">14. Jual Beli Khusus perolehan hak RSS dan KPR Bersubsidi</span>";
    if ($data->CPM_OP_JENIS_HAK == '21')
        $jenishak = "<span class=\"document-x\">21. Pemberian hak baru sebagai kelanjutan pelepasan hak</span>";
    if ($data->CPM_OP_JENIS_HAK == '22')
        $jenishak = "<span class=\"document-x\">22. Pemberian hak baru diluar pelepasan hak</span>";

    if (($data->CPM_OP_JENIS_HAK == '4') || ($data->CPM_OP_JENIS_HAK == '5')) {
        $pwaris = number_format((($npop - strval($data->CPM_OP_NPOPTKP)) * 0.05) * 0.5, 2, '.', ',');
    }
    $typepayment = "<span  class=\"document-x\">Penghitungan Wajib Pajak</span>";
    $fieldTambahan = "";
    if ($data->CPM_PAYMENT_TIPE == 2) {
        if ($data->CPM_PAYMENT_TIPE_SURAT == 1)
            $typepayment = "<span class=\"document-x\">STPD BPHTB</span>";
        if ($data->CPM_PAYMENT_TIPE_SURAT == 2)
            $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar</span>";
        if ($data->CPM_PAYMENT_TIPE_SURAT == 3)
            $typrpayment = "<span class=\"document-x\">SKPD Kurang Bayar Tambahan</span>";
        $fieldTambahan = "<tr>
			   <td valign=\"top\" class=\"document-x\">Nomor : " . $data->CPM_PAYMENT_TIPE_SURAT_NOMOR . "</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Tanggal : " . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Berdasakan peraturan KHD No : " . $data->CPM_PAYMENT_TIPE_KHD_NOMOR . "</td>
			</tr>";
    }
    $infoReject = "";
    if ($data->CPM_TRAN_STATUS == '4') {
        $infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena :</strong>
						<br>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . "</div>\n";
    }
    $data->CPM_OP_LUAS_TANAH = number_format($data->CPM_OP_LUAS_TANAH, 0, '', '');
    $data->CPM_OP_NJOP_TANAH = number_format($data->CPM_OP_NJOP_TANAH, 0, '', '');
    $data->CPM_OP_LUAS_BANGUN = number_format($data->CPM_OP_LUAS_BANGUN, 0, '', '');
    $data->CPM_OP_NJOP_BANGUN = number_format($data->CPM_OP_NJOP_BANGUN, 0, '', '');
	$bphtb_before=getsudahdibayar($data->CPM_IDSSB_KURANG_BAYAR);
	$bunga=0;

    $html = "&nbsp;&nbsp;<table width=\"900\" border=\"0\" cellpadding=\"2\">
  <tr>
    <td width=\"142\" >&nbsp;</td>
    <td colspan=\"2\" align=\"center\" width=\"425\" height=\"65\" style=\"vertical-align:middle\"><strong>".$headerKB."<br />
$alamatKB <br /></strong></td>
    <td width=\"142\" >&nbsp;</td>
  </tr>
 
  <tr>
  <td colspan=\"4\" align=\"center\" style=\"solid black;border-top: 1px solid black;\"><font size=\"-1\"><strong><br>SURAT KETETAPAN PAJAK DAERAH KURANG BAYAR</strong></font></td>
  </tr>
  <tr>
    <td colspan=\"4\">
	<br><br>
	<table width=\"100%\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"7\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong></strong></font></td>
        <td width=\"18\" align=\"right\"></td>
        <td width=\"150\">Nomor</td>
        <td width=\"8\">:</td>
        <td width=\"400\" colspan=\"11\" ><span class=\"document-x\">" . substr(getConfigValue("1", 'NO_KETETAPAN_KB'),0,4). "" . $data->CPM_NO_KURANG_BAYAR . "" . substr(getConfigValue("1", 'NO_KETETAPAN_KB'),4,9) . "" . bulanromawi(substr($data->CPM_SSB_CREATED,5,2)) . "" . substr(getConfigValue("1", 'NO_KETETAPAN_KB'),13,16) . "</span></td>
      </tr>	  
      <tr>
        <td align=\"right\"></td>
        <td>Tanggal Penerbitan</td>
        <td>:</td>
        <td  colspan=\"11\"><span class=\"document-x\">".substr($data->CPM_SSB_CREATED, 8, 2)." " . GetIndonesianMonthLong((substr($data->CPM_SSB_CREATED, 5, 2) - 1)) . " ". substr($data->CPM_SSB_CREATED, 0, 4) ."</span></td>
      </tr>
      <tr>
        <td align=\"right\"></td>
        <td>Tanggal Jatuh Tempo</td>
        <td>:</td>
        <td colspan=\"11\"><span class=\"document-x\">" . substr($data->EXPIRED, 8, 2)." " . GetIndonesianMonthLong((substr($data->EXPIRED, 5, 2) - 1)) . " ". substr($data->EXPIRED, 0, 4) . "</span></td>
      </tr>
          
    </table>	
	</td>
  </tr>
  <tr>
    <td colspan=\"4\">
	<br><br>
	<table width=\"780\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong></strong></font></td>
        <td width=\"18\" align=\"right\"></td>
        <td width=\"70\">Kepada Yth</td>
        <td width=\"8\">:</td>
        <td  colspan=\"13\"><span class=\"document-x\">" . $data->CPM_WP_NAMA . "</span></td>	  	
      </tr>
      <tr>
        <td align=\"right\"></td>
        <td colspan=\"15\">(Nama dan Alamat Wajib Pajak)</td> 
      </tr>
      <tr>
        <td align=\"right\"></td>
        <td colspan=\"15\">di ".$data->CPM_WP_ALAMAT."</td>
      </tr>
      
	  <tr>
		<td align=\"right\"></td>
	  	<td colspan=\"15\"></td>
	  </tr>
	  <tr>
		<td align=\"right\"></td>
		<td align=\"right\" width=\"40\">I. </td>
		<td align=\"right\"></td>
        <td colspan=\"13\" width=\"627\">Berdasarkan Peraturan Daerah Kab. Cianjur Nomor ". getConfigValue("1", 'PERDA_NO_1') ." Tahun ". getConfigValue("1", 'PERDA_THN_1') ." 
										 tentang Bea Perolehan Hak atas Tanah dan Bangunan dan Peraturan Bupati Cianjur Nomor ". getConfigValue("1", 'PERDA_NO_2') ." Tahun ". getConfigValue("1", 'PERDA_THN_2') ." 
										 tentang Petunjuk Teknis Pelaksanaan Pemungutan Bea Perolehan Hak atas Tanah dan Bangunan , maka  telah dilakukan pemeriksaan atau berdasarkan keterangan lain mengenai pelaksanaan kewajiban Bea Perolehan Hak atas Tanah dan Bangunan terhadap :<br></td>        
      </tr>
	  <tr>
		<td align=\"right\"></td>
		<td align=\"right\" width=\"40\"></td>
		<td align=\"right\"></td>
		<td align=\"right\" width=\"8\"></td>
        <td colspan=\"12\" width=\"619\">
			<table border=\"0\">
				<tr>
				<td width=\"100\">NOP</td><td width=\"8\">:</td><td align=\"left\" width=\"500\">". $data->CPM_OP_NOMOR ."</td>
				</tr>	
				<tr>
				<td>Letak NOP</td><td>:</td><td>". $data->CPM_OP_LETAK ."</td>
				</tr>	
					
				<tr>
				<td>Nama WP</td><td>:</td><td>". $data->CPM_WP_NAMA ."</td>
				</tr>	
				<tr>
				<td>Alamat WP</td><td>:</td><td>". $data->CPM_WP_ALAMAT ."</td>
				</tr>	
				<tr>
				<td>Nama WP Lama</td><td>:</td><td>". $data->CPM_WP_NAMA_LAMA ."</td>
				</tr>	
				<tr>
				<td>Alamat WP Lama</td><td>:</td><td></td>	
				</tr>
				
				<tr>
				<td>Jenis Perolehan</td><td>:</td><td> $jenishak </td>	
				</tr>
				
			</table>
		</td>        
      </tr>
       <tr>
		<td align=\"right\"></td>
		<td align=\"right\"></td>
		<td align=\"right\">II. </td>
		<td align=\"right\" width=\"8\"></td>
        <td colspan=\"12\" width=\"619\">
			Dari Pemeriksaan tersebut di atas, jumlah yang masih harus dibayar adalah sebagai berikut: <br>
		</td>        
      </tr>
	  <tr>
		<td align=\"right\"></td>
		<td align=\"right\"></td>
		<td align=\"right\"></td>
		<td align=\"right\" width=\"8\"></td>
        <td colspan=\"12\" width=\"619\">
			<table border=\"1\">
				<tr>
					<td width=\"400\"> &nbsp; 1. Nilai Perolehan Objek Pajak (NPOP)</td><td width=\"109\" align=\"right\">Rp. ".number_format($npop,0,'','.')." &nbsp;</td><td width=\"110\"></td>
				</tr>
				<tr>
					<td> &nbsp; 2. Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td><td align=\"right\">Rp. ".number_format($NPOPTKP,0,'','.')." &nbsp;</td><td></td>
				</tr>
				<tr>
					<td> &nbsp; 3. Nilai Perolehan Objek Pajak Kena Pajak (1-2)</td><td align=\"right\">Rp. ".number_format($npopkp,0,'','.')." &nbsp;</td><td></td>
				</tr>
				<tr>
					<td> &nbsp; 4. Pajak yang seharusnya terutang : 5% X  Rp.........(3)</td><td></td><td align=\"right\">Rp. ".number_format($npopkp*0.05,0,'','.')." &nbsp;</td>
				</tr>
				<tr>
					<td> &nbsp; 5. Pajak yang seharusnya dibayar (5)</td><td></td><td align=\"right\">Rp. ".number_format($npopkp*0.05,0,'','.')." &nbsp;</td>
				</tr>
				<tr>
					<td> &nbsp; 6. Pajak yang telah dibayar</td><td align=\"right\">Rp. ".number_format($bphtb_before,0,'','.')." &nbsp;</td><td></td>
				</tr>
				<tr>
					<td> &nbsp; 7. Pajak yang kurang dibayar ( 5 - 6 )</td><td></td><td align=\"right\">Rp. ".number_format((($npopkp*0.05)-$bphtb_before),0,'','.')." &nbsp;</td>
				</tr>
				<tr>
					<td> &nbsp; 8. Sanksi administrasi berupa bunga (Pasal ….Perda BPHTB ):</td><td rowspan=\"2\"></td><td rowspan=\"2\"  align=\"right\" style=\"vertical-align:'middle';\">Rp. ".number_format($bunga,0,'','.')." &nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bunga  = ………bulan X 2% X Rp…………(7)</td>
				</tr>
				<tr>
					<td> &nbsp; 9. Jumlah yang masih harus dibayar (7 + 8)</td><td></td><td  align=\"right\">Rp. ".number_format((($npopkp*0.05)-$bphtb_before+$bunga),0,'','.')." &nbsp;</td>
				</tr>
			</table>
		</td>        
      </tr>
    </table>	
	</td>
  </tr>
 
  
  <tr>
    <td colspan=\"4\">
	
<br>
</td>
  </tr>
  <tr>
    <td width=\"300\" height=\"90\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;				
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  <font size=\"-2\"></font><br><font size=\"-1\"></font>			  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"50\">&nbsp;</td>
	        </tr>	
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\"  width=\"250\">
			  	<font size=\"-2\" ><b></b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-2\" ><b></b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
		
	</td>
	<td width=\"100\" height=\"90\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;				
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  <font size=\"-2\"></font>			  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"50\">&nbsp;</td>
	        </tr>	
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" >
			  	<font size=\"-2\" ><b></b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-2\" ><b></b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
		
	</td>
	<td width=\"300\" height=\"90\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;				
		<table width=\"100%\" border=\"\">
		    <tr>
		      <td align=\"center\" height=\"\" colspan=\"3\">
			  <font size=\"-2\">Cianjur, ".date("d")." ". GetIndonesianMonthLong(date("m")-1). " ".date("Y")."</font>			  
			  </td>
	        </tr>
			 <tr>
		      <td align=\"center\" colspan=\"3\">
			  <font size=\"-2\">Mengetahui, </font>			  
			  </td>
	        </tr>
			<tr>
		      <td align=\"center\" colspan=\"3\">
			  <font size=\"-1\">a.n KEPALA BADAN PENGELOLAAN PAJAK DAERAH<br>KAB CIANJUR<br>KEPALA BIDANG PBB DAN BPHTB</font>		  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"50\">&nbsp;</td>
	        </tr>	
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"250\">
			  	<font size=\"-2\" ><b>" . $nmpjbsah . "</b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-2\" ><b>" . $nippjbsah . "</b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
		
	</td>
	
	
  </tr> 
</table><br>
<table width=\"720\" border=\"0\" cellpadding=\"0\">
			<tr>
			  <td colspan=\"2\"><font size=\"-2\">Tembusan</font></td>
			  
			</tr>
			<tr>
			  <td width=\"20\"><font size=\"-2\"></font></td>
			  <td><font size=\"-2\">1. Bupati Kab. CIanjur</font></td>
			</tr>
			<tr>
			  <td width=\"20\"></td>
			  <td><font size=\"-2\">2.	Wajib Pajak</font></td>
			</tr>
			<tr>
			  <td width=\"20\"></td>
			  <td><font size=\"-2\">3.	Arsip</font></td>
			</tr>
		</table>
		<table border=\"0\">
		<tr>
			<td style=\"border-bottom:1px #000 dashed;\" width=\"725\" align=\"center\"><font size=\"-2\">Gunting disini</font></td>
		</tr>
		</table><br><br>
		<table border=\"1\">
		<tr>
			<td height=\"150\">
			&nbsp;<br>&nbsp;
			<table border=\"0\">
				<tr>
					<td width=\"110\">NOP</td>
					<td width=\"8\">:</td>
					<td>". $data->CPM_OP_NOMOR ."</td>
				</tr>
				<tr>
					<td>Nama</td>
					<td>:</td>
					<td>". $data->CPM_WP_NAMA ."</td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td>:</td>
					<td>". $data->CPM_WP_ALAMAT ."</td>
				</tr>
			</table>
			
			<table align=\"right\" border=\"0\"  width=\"700\">
				<tr><td width=\"500\"></td>
					<td align=\"center\" width=\"200\">Cianjur, ".date("d")." ". GetIndonesianMonthLong(date("m")-1). " ".date("Y")."</td>
				</tr>
				<tr><td></td>
					<td align=\"center\">Yang Menerima</td>
				</tr>
				<tr><td></td>
					<td align=\"right\" style=\"border-bottom:1px #000 dashed;\"><br><br></td>
				</tr>
				<tr><td></td>
					<td></td>
				</tr>
			</table>
			</td>
		</tr>
		</table>";

    return $html;
}

$appID = base64_decode($q->axx);
$v = count($idssb);
$NOP = "";
// create new PDF document
$pagelayout = array(210, 500);
$pdf = new TCPDF("P", PDF_UNIT, $pagelayout, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('-');
$pdf->SetSubject('-');
$pdf->SetKeywords('-');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 5, 5);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(5);
//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);
// ---------------------------------------------------------
// set font
//$pdf->SetFont('helvetica', 'B', 20);
// add a page
//$pdf->AddPage();
//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('segoeui', '', 10);
#$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";

for ($i = 0; $i < $v; $i++) {
    $fileLogo = getConfigValue("1", 'FILE_LOGO_KETETAPAN');
    $resolution = array(215, 350);
    $pdf->AddPage('P', $resolution);

    #$pdf->AddPage('P', 'FOLIO');
    //$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
    $HTML = getHTML($idssb);
    //echo $HTML;

    $style = array('position' => 'fixed',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false, //array(255,255,255),
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    $pdf->writeHTML($HTML, true, false, false, false, '');
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 10, 2, 25, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);
    if ($draf == 1) {
        $bottomMargin = 0;
        for ($x = 1; $x <= 9; $x++) {
            $rightMargin = 0;
            for ($y = 1; $y <= 7; $y++) {
                $pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 15, 35, '', '', '', true, false, 300, '', false);
                $rightMargin +=35;
            }
            $bottomMargin +=35;
        }
        $rightMargin = 0;
        for ($y = 1; $y <= 7; $y++) {
            $pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 17, 35, '', '', '', true, false, 300, '', false);
            $rightMargin +=35;
        }
    } else if ($draf == 0)
        $pdf->Image($sRootPath . 'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
    $pdf->ln(1);
    $pdf->SetAlpha(1);
    //$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
}

// -----------------------------------------------------------------------------
//Close and output PDF document
$pdf->Output($NOP . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
?>
