<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);


$dataNotaris="";
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function getAuthor($uname) {
	global $DBLink,$appID;	
	$id= $appID;
	$qry = "select nm_lengkap from tbl_reg_user_notaris where userId = '".$uname."'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo mysqli_error($DBLink);
	}
	
	$num_rows = mysqli_num_rows($res);
	if ($num_rows==0) return $uname;
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['nm_lengkap'];
	}
}

function getConfigValue ($id,$key) {
	global $DBLink,$appID;	
	$id= $appID;
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}


function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getData($iddoc) {
	global $data,$DBLink,$dataNotaris;
	$query = sprintf("SELECT * , DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'",
					getConfigValue("1",'TENGGAT_WAKTU'),$iddoc);

	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		echo $query."<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res,"data"));	
	$dt = $dataNotaris->data[0];
	return $dt;
}

function getHTML ($iddoc) {
	global $uname,$NOP;
	$data = getData($iddoc);
	$jenishak= "<span class=\"document-x\">Jual Beli</span>";
	$npop = 0;
	$pwaris = "-";
	
	$a = strval($data->CPM_OP_LUAS_BANGUN)*strval($data->CPM_OP_NJOP_BANGUN)+strval($data->CPM_OP_LUAS_TANAH)*strval($data->CPM_OP_NJOP_TANAH);
	$b = strval($data->CPM_OP_HARGA);
	//$NPOPTKP =  getConfigValue("1",'NPOPTKP_STANDAR');
	$NPOPTKP =  $data->CPM_OP_NPOPTKP;
	$typeR = $data->CPM_OP_JENIS_HAK;
	$type = $data->CPM_PAYMENT_TIPE;
	$NOP =  $data->CPM_OP_NOMOR;
	$c1="";
	$c2="";
	$c3="";
	$c4="";
	
	if ($type=='1') $c1 = "X";
	if ($type=='2') $c2 = "X";
	if ($type=='3') $c3 = "X";
	if ($type=='4') $c4 = "X";
	
	/*if (($typeR==4) || ($typeR==6)){
		$NPOPTKP =  getConfigValue("1",'NPOPTKP_WARIS');
	}*/
	
	
	
	
	if ($b < $a) $npop = $a; else $npop = $b;
	
	$n = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);
	$a = $npop-strval($NPOPTKP) > 0 ? $npop-strval($NPOPTKP) : 0;
	$m = ($a)*0.05;
	$a = $a*0.05;
	
	if ($n != 0) $m = $m-$m*($n*0.01);
	$b = $npop - $NPOPTKP;
	if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_BPHTB_TU))) {
			$a = $data->CPM_OP_BPHTB_TU;
			$m = $a;
	}
	
	if ($data->CPM_OP_JENIS_HAK=='1')$jenishak= "<span class=\"document-x\">Jual beli</span>";
	if ($data->CPM_OP_JENIS_HAK=='2') $jenishak= "<span class=\"document-x\">Tukar Menukar</span>";
	if ($data->CPM_OP_JENIS_HAK=='3') $jenishak= "<span class=\"document-x\">Hibah</span>";
	if ($data->CPM_OP_JENIS_HAK=='4') $jenishak= "<span class=\"document-x\">Hibah Wasiat Sedarah Satu Derajat</span>";
	if ($data->CPM_OP_JENIS_HAK=='5') $jenishak= "<span class=\"document-x\">Hibah Wasiat Non Sedarah Satu Derajat</span>";
	if ($data->CPM_OP_JENIS_HAK=='6') $jenishak= "<span class=\"document-x\">Waris</span>";
	if ($data->CPM_OP_JENIS_HAK=='7') $jenishak= "<span class=\"document-x\">Hibah Wasiat Sedarah Satu Derajat</span>";
	if ($data->CPM_OP_JENIS_HAK=='8') $jenishak= "<span class=\"document-x\">Pemasukan dalam perseroan/badan hukum lainnya</span>";
	if ($data->CPM_OP_JENIS_HAK=='9') $jenishak= "<span class=\"document-x\">Pemisahan hak yang mengakibatkan peralihan</span>";
	if ($data->CPM_OP_JENIS_HAK=='10') $jenishak= "<span class=\"document-x\">Penunjukan pembel dalam lelang</span>";
	if ($data->CPM_OP_JENIS_HAK=='11') $jenishak= "<span class=\"document-x\">Pelaksanaan putusan hakim yang <br>mempunyai kekuatan hukum tetap</span>";
	if ($data->CPM_OP_JENIS_HAK=='12') $jenishak= "<span class=\"document-x\">Penggabungan usaha</span>";
	if ($data->CPM_OP_JENIS_HAK=='13') $jenishak= "<span class=\"document-x\">Pemekaran usaha</span>";
	if ($data->CPM_OP_JENIS_HAK=='14') $jenishak= "<span class=\"document-x\">Hadiah</span>";
	if ($data->CPM_OP_JENIS_HAK=='15') $jenishak= "<span class=\"document-x\">Jual beli khusus perolehan hak Rumah Sederhana dan
	Rumah Susun Sederhana melalui KPR bersubsidi</span>";
	if ($data->CPM_OP_JENIS_HAK=='16') $jenishak= "<span class=\"document-x\">Pemberian hak baru sebagai kelanjutan pelepasan hak</span>";
	if ($data->CPM_OP_JENIS_HAK=='17') $jenishak= "<span class=\"document-x\">Pemberian hak baru diluar pelepasan hak</span>";
	
	if (($data->CPM_OP_JENIS_HAK=='4') || ($data->CPM_OP_JENIS_HAK=='5')) {
	    $pwaris = number_format((($npop-strval($data->CPM_OP_NPOPTKP))*0.05)*0.5, 2, '.', ',');
	}
	$typepayment = "<span  class=\"document-x\">Penghitungan Wajib Pajak</span>";
	$fieldTambahan = "";
	if ($data->CPM_PAYMENT_TIPE==2) {
		if ($data->CPM_PAYMENT_TIPE_SURAT ==1 ) $typepayment = "<span class=\"document-x\">STPD BPHTB</span>";
		if ($data->CPM_PAYMENT_TIPE_SURAT ==2 ) $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar</span>";
		if ($data->CPM_PAYMENT_TIPE_SURAT ==3 ) $typrpayment = "<span class=\"document-x\">SKPD Kurang Bayar Tambahan</span>";
		$fieldTambahan = "<tr>
			   <td valign=\"top\" class=\"document-x\">Nomor : ".$data->CPM_PAYMENT_TIPE_SURAT_NOMOR."</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Tanggal : ".$data->CPM_PAYMENT_TIPE_SURAT_TANGGAL."</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Berdasakan peraturan KHD No : ".$data->CPM_PAYMENT_TIPE_KHD_NOMOR."</td>
			</tr>";
	}
	$infoReject = "";
	if ($data->CPM_TRAN_STATUS=='4') {
		$infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena :</strong>
						<br>".str_replace("\n","<br>",$data->CPM_TRAN_INFO)."</div>\n";
	}
	
	//$html = "<link rel=\"stylesheet\" href="../../../function/BPHTB/dispenda/func-display-dispenda.css\" type=\"text/css\">\n";
	$html = "<table width=\"900\" border=\"1\" cellpadding=\"2\">
  <tr>
    <td width=\"142\" rowspan=\"2\">&nbsp;</td>
    <td colspan=\"2\" align=\"center\" width=\"420\"><strong><font size=\"+2\"><br>SURAT SETORAN PAJAK DAERAH</font><font size=\"+2\"><br />
BEA PEROLEHAN HAK ATAS TANAH DAN BANGUNAN <br />
    </font></strong><strong><font size=\"+1\">(SSPD-BPHTB)</font></strong></td>
    <td width=\"142\" rowspan=\"2\">&nbsp;</td>
  </tr>
  <tr>
    <td colspan=\"2\" align=\"center\">BERFUNGSI SEBAGAI SURAT PEMBERITAHUAN OBJEK PAJAK<br />
    PAJAK BUMI DAN BANGUNAN (SPOP PBB)</td>
  </tr>
  <tr>
    <td colspan=\"4\">".getConfigValue("1",'NAMA_DINAS')."</td>
  </tr>
  <tr>
    <td colspan=\"4\"><table width=\"100%\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>A.</strong></font></td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"120\">Nama Wajib Pajak</td>
        <td width=\"18\">:</td>
        <td width=\"400\" colspan=\"11\" ><span class=\"document-x\">".$data->CPM_WP_NAMA."</span></td>
      </tr>
      <tr>
        <td align=\"right\">2.</td>
        <td>NPWP</td>
        <td>:</td>
        <td  colspan=\"11\"><span class=\"document-x\">".$data->CPM_WP_NPWP."</span></td>
      </tr>
      <tr>
        <td align=\"right\">3.</td>
        <td>Alamat Wajib Pajak</td>
        <td>:</td>
        <td colspan=\"11\"><span class=\"document-x\">".trim(strip_tags($data->CPM_WP_ALAMAT))."</span></td>
      </tr>
      <tr>
        <td align=\"right\">4.</td>
        <td>No. KTP</td>
        <td>:</td>
        <td  colspan=\"11\"><span class=\"document-x\">".$data->CPM_WP_NOKTP."</span></td>
      </tr>
      <tr>
        <td align=\"right\">5.</td>
        <td>Kelurahan/Desa</td>
        <td>:</td>
        <td width=\"120\"><span class=\"document-x\">".$data->CPM_WP_KELURAHAN."</span></td>
        <td width=\"8\">&nbsp;</td>
        <td align=\"right\" width=\"18\">6.</td>
        <td width=\"50\">RT/RW</td>
        <td width=\"18\">:</td>
        <td width=\"60\"><span class=\"document-x\">".$data->CPM_WP_RT."/".$data->CPM_WP_RW."</span></td>
        <td width=\"8\">&nbsp;</td>
        <td width=\"18\">7.</td>
        <td width=\"70\">Kecamatan</td>
        <td width=\"18\">:</td>
        <td width=\"150\"><span class=\"document-x\">".$data->CPM_WP_KECAMATAN."</span></td>
      </tr>
      <tr>
        <td align=\"right\">8.</td>
        <td>Kabupaten/Kota</td>
        <td>:</td>
        <td><span class=\"document-x\">".$data->CPM_WP_KABUPATEN."</span></td>
        <td>&nbsp;</td>
        <td align=\"right\">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align=\"right\">&nbsp;</td>
        <td>8.</td>
        <td>Kode Pos</td>
        <td>:</td>
        <td><span class=\"document-x\">".$data->CPM_WP_KABUPATEN."</span></td>
      </tr>
      
    </table></td>
  </tr>
  <tr>
    <td colspan=\"4\"><table width=\"600\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"4\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>B.</strong></font></td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"200\">Nomor Objek Pajak (NOP) PBB</td>
        <td width=\"18\">:</td>
        <td colspan=\"11\" ><span class=\"document-x\">".$data->CPM_OP_NOMOR."</span></td>
      </tr>
      <tr>
        <td align=\"right\">2.</td>
        <td>Letak tanah dan atau bangunan</td>
        <td>:</td>
        <td colspan=\"11\" ><span class=\"document-x\">".trim(strip_tags($data->CPM_OP_LETAK))."</span></td>
      </tr>
      <tr>
        <td align=\"right\">3.</td>
        <td>Kelurahan/Desa</td>
        <td>:</td>
        <td width=\"180\"><span class=\"document-x\">".$data->CPM_OP_KELURAHAN."</span></td>
        <td width=\"8\">&nbsp;</td>
        <td width=\"18\" align=\"right\">4.</td>
        <td width=\"100\">RT/RW</td>
        <td width=\"18\">:</td>
        <td width=\"100\" colspan=\"6\"><span class=\"document-x\">".$data->CPM_OP_RT."/".$data->CPM_OP_RW."</span></td>
      </tr>
      <tr>
        <td align=\"right\">5.</td>
        <td>Kecamatan</td>
        <td>:</td>
        <td><span class=\"document-x\">".$data->CPM_OP_KECAMATAN."</span></td>
        <td>&nbsp;</td>
        <td align=\"right\">6.</td>
        <td>Kabupaten/Kota</td>
        <td>:</td>
        <td colspan=\"6\" ><span class=\"document-x\">".$data->CPM_OP_KABUPATEN."</span></td>
      </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td colspan=\"14\"><table width=\"650\" border=\"1\" cellspacing=\"0\" cellpadding=\"1\">
          <tr>
            <td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></td>
          </tr>
          <tr>
            <td align=\"center\" valign=\"middle\" width=\"100\">Uraian</td>
            <td align=\"center\" valign=\"middle\" width=\"200\">Luas <br />
              (Diisi luas tanah dan atau bangunan yang haknya diperoleh)</td>
            <td align=\"center\" valign=\"middle\" width=\"200\"><font size=\"-1\">NJOP PBB /m² <br />
              (Diisi berdasakan SPPT PBB terakhir sebelum terjadinya peralihan hak) </font><span class=\"document-x\"></span></td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\" width=\"150\">Luas x NJOP PBB /m²</td>
          </tr>
          <tr>
            <td rowspan=\"2\" align=\"center\" valign=\"middle\">Tanah / Bumi</td>
            <td align=\"center\" valign=\"middle\">7. Luas Tanah (Bumi)</td>
            <td align=\"center\" valign=\"middle\">9. NJOP Tanah (Bumi) /m²</td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\">Angka (7x9)</td>
          </tr>
          <tr>
            <td align=\"center\" valign=\"middle\" class=\"document-x\">".number_format (intval($data->CPM_OP_LUAS_TANAH) ,0 ,',' , '.' )." m²</td>
            <td align=\"center\" valign=\"middle\" class=\"document-x\">Rp.".number_format (intval($data->CPM_OP_NJOP_TANAH) ,0 ,',' , '.' )."</td>
            <td align=\"center\" valign=\"middle\" width=\"25\">11.</td>
            <td align=\"right\" valign=\"middle\"  id=\"t1\" class=\"document-x\" width=\"125\">Rp.".number_format (intval($data->CPM_OP_NJOP_TANAH)*intval($data->CPM_OP_LUAS_TANAH) ,0 ,',' , '.' )."</td>
          </tr>
          <tr>
            <td rowspan=\"2\" align=\"center\" valign=\"middle\">Bangunan</td>
            <td align=\"center\" valign=\"middle\">8. Luas Bangunan</td>
            <td align=\"center\" valign=\"middle\">10. NJOP Bangunan / m²</td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\">Angka (8x10)</td>
          </tr>
          <tr>
            <td align=\"center\" valign=\"middle\" class=\"document-x\">".number_format (intval($data->CPM_OP_LUAS_BANGUN) ,0 ,',' , '.' )." m²</td>
            <td align=\"center\" valign=\"middle\" class=\"document-x\">Rp.".number_format (intval($data->CPM_OP_NJOP_BANGUN) ,0 ,',' , '.' )."</td>
            <td align=\"center\" valign=\"middle\">12.</td>
            <td align=\"right\" valign=\"middle\" id=\"t2\" class=\"document-x\">Rp.".number_format (intval($data->CPM_OP_NJOP_BANGUN)*intval($data->CPM_OP_LUAS_BANGUN),0 ,',' , '.' )."</td>
          </tr>
          <tr>
            <td colspan=\"3\" align=\"right\" valign=\"middle\">NJOP PBB </td>
            <td align=\"center\" valign=\"middle\">13.</td>
            <td align=\"right\" valign=\"middle\" id=\"t3\" class=\"document-x\">Rp.".number_format (intval($data->CPM_OP_NJOP_BANGUN)*intval($data->CPM_OP_LUAS_BANGUN)+intval($data->CPM_OP_NJOP_TANAH)*intval($data->CPM_OP_LUAS_TANAH),0 ,',' , '.' )."</td>
          </tr>
        </table></td>
        </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td width=\"25\" align=\"right\">14.</td>
        <td width=\"300\">Harga Transaksi / Nilai Pasar</td>
        <td>:</td>
        <td  colspan=\"11\" ><span class=\"document-x\">Rp.".number_format (intval($data->CPM_OP_HARGA) ,0 ,',' , '.' )."</span></td>
      </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td align=\"right\">15.</td>
        <td>Jenis perolehan hak atas tanah atau bangunan</td>
        <td>:</td>
        <td colspan=\"11\" >$jenishak</td>
      </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td align=\"right\">16.</td>
        <td>Nomor sertifikasi tanah</td>
        <td>:</td>
        <td colspan=\"11\">".$data->CPM_OP_NMR_SERTIFIKAT."</td>
      </tr>
     
    </table></td>
  </tr>
  <tr>
    <td colspan=\"4\"><table width=\"500\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>C.</strong></font></td>
        <td width=\"400\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
        <td width=\"18\">:</td>
        <td><span class=\"document-x\">Rp.".number_format(strval($data->CPM_SSB_AKUMULASI), 2, '.', ',')."</span></td>
        </tr>
      
    </table></td>
  </tr>
  <tr>
    <td colspan=\"4\"><table width=\"500\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>D.</strong></font></td>
        <td colspan=\"4\" align=\"left\"><strong>Perhitungan BPHTB</strong></td>
        </tr>
      <tr>
        <td width=\"400\" align=\"left\"><font size=\"-1\">Nilai Perolehan Objek Pajak (NPOP) memperhatikan nilai pada B.13 dan B.14</font></td>
        <td width=\"120\">&nbsp;</td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"120\" align=\"right\"><span class=\"document-x\">Rp.".number_format($npop, 2, '.', ',')."</span></td>
        </tr>
      <tr>
        <td align=\"left\">Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
        <td>&nbsp;</td>
        <td align=\"right\">2.</td>
        <td><span class=\"document-x\" align=\"right\">Rp.".number_format($NPOPTKP, 2, '.', ',')."</span></td>
        </tr>
      <tr>
        <td width=\"400\" align=\"left\">Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
        <td width=\"120\" ><font size=\"-2\">angka 1- angka 2</font></td>
        <td align=\"right\">3.</td>
        <td><span class=\"document-x\" align=\"right\">Rp.".number_format($a, 2, '.', ',')."</span></td>
        </tr>
      <tr>
        <td align=\"left\">Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
        <td><font size=\"-2\">5% angka 3</font></td>
        <td align=\"right\">4.</td>
        <td><span class=\"document-x\" align=\"right\">Rp.".number_format($a*0.05, 2, '.', ',')."</span></td>
        </tr>
      
    </table></td>
  </tr>
  <tr>
    <td colspan=\"4\"><table width=\"100%\" border=\"0\" cellpadding=\"1\">
  <tr>
    <td width=\"18\"><font size=\"+1\"><strong>E.</strong></font></td>
    <td colspan=\"5\"><strong>Jumlah Setoran Berdasarkan</strong> :</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td width=\"30\"><table width=\"100%\" border=\"1\" cellpadding=\"1\">
      <tr>
        <td align=\"center\">$c1</td>
      </tr>
    </table></td>
    <td width=\"18\">a.</td>
    <td colspan=\"3\">Perhitungan Wajib Pajak</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
      <tr>
        <td align=\"center\">$c2</td>
      </tr>
    </table></td>
    <td>b.</td>
    <td width=\"220\">STPD BPHTB/SKPDBKB/SKPDBKBT</td>
    <td width=\"150\">Nomor :".$data->CPM_PAYMENT_TIPE_SURAT_NOMOR."</td>
    <td width=\"200\">Tanggal : ".$data->CPM_PAYMENT_TIPE_SURAT_TANGGAL."</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
      <tr>
        <td align=\"center\">$c3</td>
      </tr>
    </table></td>
    <td>c.</td>
    <td>Pengurangan dihitung sendiri menjadi</td>
    <td>".$data->CPM_PAYMENT_TIPE_PENGURANGAN."%</td>
    <td>Bedasarkan peraturan KDH No  : ".$data->CPM_PAYMENT_TIPE_KHD_NOMOR."</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
      <tr>
        <td align=\"center\">$c4</td>
      </tr>
    </table></td>
    <td>d.</td>
    <td colspan=\"3\">".$data->CPM_PAYMENT_TIPE_OTHER."</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td colspan=\"5\"><table width=\"100%\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"31%\" align=\"center\">JUMLAH YANG DISETOR</td>
        <td width=\"2%\">&nbsp;</td>
        <td width=\"67%\">(Dengan huruf)</td>
      </tr>
      <tr>
        <td align=\"center\"> (Dengan angka)</td>
        <td>&nbsp;</td>
        <td rowspan=\"4\"><table width=\"100%\" border=\"1\" cellpadding=\"4\">
          <tr>
            <td><table width=\"100%\" border=\"0\" cellpadding=\"0\">
              
              <tr>
                <td><font size=\"-2\">".strtoupper(SayInIndonesian($m))." RUPIAH</font></td>
              </tr>
             
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align=\"center\"><table width=\"100%\" border=\"1\" cellpadding=\"1\">
          <tr>
            <td>Rp. ".number_format($m, 2, '.', ',')."</td>
          </tr>
        </table></td>
        <td>&nbsp;</td>
        </tr>
      <tr>
        <td align=\"center\">(Berdasarkan perhitungan D.4)</td>
        <td></td>
        </tr>
      
    </table></td>
  </tr>
</table></td>
  </tr>
  <tr>
    <td width=\"172\">
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\"><font size=\"-2\">WAJIB PAJAK/PENYETOR</font></td>
	        </tr>
			<tr>
		      <td align=\"center\"><font size=\"-2\"><br><br><br><br><br><br><br><br><br>_______________________</font></td>
	        </tr></table>
	</td>
	<td width=\"172\">
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\"><font size=\"-2\">Mengetahui <br>PPAT/NOTARIS</font></td>
	        </tr>
			
			<tr>
		      <td align=\"center\"><font size=\"-2\"><br><br><br><br><br><br><br><br>_______________________</font></td>
	        </tr></table>
	</td>
	<td width=\"176\">
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\"><font size=\"-2\">Diterima oleh <br>TEMPAT PEMBAYARAN BPHTB</font></td>
	        </tr>
			
			<tr>
		      <td align=\"center\"><font size=\"-2\"><br><br><br><br><br><br><br><br>_______________________</font></td>
	        </tr></table>
	</td>
	<td width=\"184\">
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\"><font size=\"-2\">Telah diverifikasi <br>DINAS PENDAPATAN PENGELOLAAN KEUANGAN & ASET DAERAH</font></td>
	        </tr>
			
			<tr><td align=\"center\"><font size=\"-2\"><br><br><br><br><br><br><br>_________________________</font></td></tr>
			<tr>
		      <td align=\"center\"></td>
	        </tr></table>
	</td>
  </tr> 
 
</table><br><table width=\"720\" border=\"0\" cellpadding=\"0\">
		    <tr>
		      <td width=\"130\"><font size=\"-2\">Tanggal kadaluarsa</font></td>
		      <td width=\"10\"><font size=\"-2\">:</font></td>
		      <td width=\"300\"><font size=\"-2\">".$data->EXPIRED."</font></td>
			  <td width=\"250\"></td>
	        </tr>
		    <tr>
		      <td><font size=\"-2\">Tempat pembayaran</font></td>
		      <td><font size=\"-2\">:</font></td>
		      <td><font size=\"-2\">BANK JABAR BANTEN</font></td>
			  <td align=\"right\"><font size=\"-3\"><i>No Reg ".$data->CPM_TRAN_SSB_ID."</i></font></td>
	        </tr>
	      </table>";
		  
	return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q); 
$q = $json->decode($q);

$v = count($q);

$NOP = "";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
$pdf->SetMargins(5, 14, 5);
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

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions=array('modify'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null);
$HTML = "";


for ($i=0;$i<$v;$i++) {
	$idssb = $q[$i]->id;
	$uname = "";//$q[0]->uname;
	$draf = $q[$i]->draf;
	$appID = base64_decode($q[$i]->axx);
	$fileLogo =  getConfigValue("1",'FILE_LOGO');
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	$pdf->Image($sRootPath.'view/Registrasi/configure/logo/'.$fileLogo, 5, 20, 40, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	if ($draf == 1) $pdf->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
	else if ($draf == 0) $pdf->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false);
}

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($NOP.'.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
?>
