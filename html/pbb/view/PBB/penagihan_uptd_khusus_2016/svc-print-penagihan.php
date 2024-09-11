<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_uptd_khusus_2016', '', dirname(__FILE__))).'/';
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
// print_r($_REQUEST);exit;
$appID = @isset($_REQUEST['appId']) ? $_REQUEST['appId'] : "";
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$thnpajak = @isset($_REQUEST['thnpajak']) ? $_REQUEST['thnpajak'] : "";
$kepala 	= @isset($_REQUEST['kepala']) ? $_REQUEST['kepala'] : "";
$kota 		= @isset($_REQUEST['kota']) ? $_REQUEST['kota'] : "";
$nip 		= @isset($_REQUEST['nip']) ? $_REQUEST['nip'] : "";
$jabatan 	= @isset($_REQUEST['jabatan']) ? $_REQUEST['jabatan'] : "";
$bank 		= @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
$denda 		= @isset($_REQUEST['denda']) ? $_REQUEST['denda'] : "";
$totalBulanPajak 	= @isset($_REQUEST['totalBulanPajak']) ? $_REQUEST['totalBulanPajak'] : "";
$tipeKalkulasiPajak = @isset($_REQUEST['tipeKalkulasiPajak']) ? $_REQUEST['tipeKalkulasiPajak'] : "";
$SP 	= @isset($_REQUEST['SP']) ? "SP".$_REQUEST['SP'] : "";
$SP 	= str_replace("SP","",$SP);
$uname 	= @isset($_REQUEST['uname']) ? $_REQUEST['uname'] : "";

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($appID);
$dbhost 	= $appConfig['GW_DBHOST'];
$dbuser 	= $appConfig['GW_DBUSER'];
$dbpwd 		= $appConfig['GW_DBPWD'];
$dbname 	= $appConfig['GW_DBNAME'];

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost, $dbuser, $dbpwd, $dbname);
//echo $dbhost." | ".$dbuser." | ".$dbpwd." | ".$dbname;

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
error_reporting(E_ALL);
ini_set('display_errors', 1);

$month = array("01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei",
               "06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");
$tgl = date("d")."/".$month[date("m")]."/".date("Y");

function getDatKUPTD($uname) {
	global $DBLink;
	$qry = "SELECT
				B.*,C.CPC_TKC_KECAMATAN AS NAMA_KEC
			FROM
				TBL_REG_USER_PBB A
			LEFT JOIN cppmod_pbb_kuptd B ON A.kecamatan = B.KUPTD_KD_KEC
			LEFT JOIN cppmod_tax_kecamatan C ON B.KUPTD_KD_KEC = C.CPC_TKC_ID 
			WHERE
				ctr_u_id = '".$uname."'";
	// echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo mysqli_error($DBLink);
	} else {
		$row = mysqli_fetch_assoc($res);
		return $row;
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
					FROM CPPMOD_SSB_DOC A,CPPMOD_SSB_TRANMAIN B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
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

function getTunggakan($nop) {
	global $DBLinkLookUp,$thnpajak;	

        $qry = "SELECT SPPT_PBB_HARUS_DIBAYAR,SPPT_TAHUN_PAJAK,SPPT_TANGGAL_JATUH_TEMPO FROM PBB_SPPT WHERE NOP = '".$nop."' AND NOW() > SPPT_TANGGAL_JATUH_TEMPO AND SPPT_TAHUN_PAJAK = '{$thnpajak}' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ORDER BY SPPT_TAHUN_PAJAK ASC";
        
	$res = mysqli_query($DBLinkLookUp, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
        
        return $res;
}

function getHTML ($op) {
    global $uname,$lastTagihan,$kepala,$nip, $jabatan,$kota,$bank, $SP,$thnArr,$appID, $month,$listTagihan,$listDenda,$tagihanPlusDenda,$listTahun;
	$angkaRomawi = array('01'=>'I','02'=>'II','03'=>'III','04'=>'IV','05'=>'V','06'=>'VI','07'=>'VII','08'=>'VIII','09'=>'IX','10'=>'X','11'=>'XI','12'=>'XII');
    $tahun = date("Y");
    $tgl = explode("-",$op['SPPT_TANGGAL_JATUH_TEMPO']);
    $header_berkas	= getConfigValue ($appID, 'C_HEADER_SK');
    $alamat_berkas	= getConfigValue ($appID, 'C_ALAMAT_DISPOSISI');
	
    $datKUPTD		= getDatKUPTD($uname);
	$KUPTDKdKec		= $datKUPTD['KUPTD_KD_KEC'];
	$KUPTDNamaKec	= $datKUPTD['NAMA_KEC'];
	$KUPTDNama		= $datKUPTD['KUPTD_NAMA'];
	$KUPTDNIP		= $datKUPTD['KUPTD_NIP'];
	$KUPTDJabatan	= $datKUPTD['KUPTD_JABATAN'];
    $sumTagihan	     = array_sum($listTagihan);
    $sumDenda 		 = array_sum($listDenda);
    $sumTagihanDenda = array_sum($tagihanPlusDenda);
    $listTahun = join(',',$listTahun);
	
	
	$ttd = "";
	//Jika tagihan diatas 5 juta
	if($lastTagihan>5000000){
	// if($sumTagihanDenda>5000000){
		$ttd .= "
			<table border=\"0\">
                <tr>
					<td width=\"420\"></td>
					<td align=\"left\"><b>an. KEPALA DINAS PENDAPATAN DAERAH</b>
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>DAERAH KOTA ".strtoupper($kota)."</b>
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>SEKERTARIS</b>,
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".$kepala."
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".$jabatan."
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NIP. ".$nip."
					</td>
				</tr>
			</table>
		";
	} 
        else {
		if($KUPTDKdKec=='1671060'){
			$ttd .= "
			<table border=\"0\">
                <tr>
					<td width=\"440\"></td>
					<td align=\"left\"><b>Plt. KEPALA UPTD ".strtoupper($KUPTDNamaKec)."</b>
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KASUBBAG TU,
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".$KUPTDNama."
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".$KUPTDJabatan."
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NIP. ".$KUPTDNIP."
					</td>
				</tr>
			</table>
		";
		} 
                else {
			$ttd .= "
			<table border=\"0\">
                <tr>
					<td width=\"420\"></td>
					<td align=\"left\"><b>KEPALA UPTD ".strtoupper($KUPTDNamaKec)."</b>
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					<br/>
					&nbsp;&nbsp;&nbsp; ".$KUPTDNama."
					<br/>
					&nbsp;&nbsp;&nbsp; ".$KUPTDJabatan."
					<br/>
					&nbsp;&nbsp;&nbsp; NIP. ".$KUPTDNIP."
					</td>
				</tr>
			</table>
		";
		}
		
	}
	
    $html = "
			<html>
			<table border=\"0\" cellpadding=\"10\" width=\"100%\">
				<tr>
					<!--LOGO-->
					<td align=\"center\" width=\"20%\">
						
					</td>
					<!--COP-->
					<td align=\"center\" width=\"79%\">
						".$header_berkas."
					</td>
					<!--KOSONG-->
					<td align=\"center\" width=\"1%\">
					</td>
				</tr>
				<tr>
					<td colspan=\"3\"><hr></td>
				</tr>
                <tr>
                    <td colspan=\"3\">
					<br/>
					<table width=\"100%\" border=\"0\">
                        <tr>
                            <td width=\"90\">Nomor</td>
							<td width=\"10\">:</td>
							<td width=\"350\">973/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/Dispenda-II/".$angkaRomawi[date("m")]."/".$tahun."</td><td width=\"23\"></td>
							<td width=\"250\">".ucfirst(strtolower($kota)).", &nbsp;&nbsp;&nbsp;&nbsp;".$month[date("m")]." ".$tahun."</td>
						</tr>
						<tr>
                            <td>Sifat</td>
							<td>:</td>
							<td>Biasa</td><td></td>
							<td>Kepada</td>
						</tr>
						<tr>
                            <td>Lampiran</td>
							<td>:</td>
							<td>1 (satu) berkas</td><td>Yth. </td>
							<td>Sdr. ".$op['WP_NAMA']."</td>
						</tr>
						<tr>
                            <td>Perihal</td>
							<td>:</td>
							<td>Pemberitahuan ".$SP."</td><td></td>
							<td>di-</td>
						</tr>
						<tr>
                            <td></td><td></td><td></td><td></td><td>".ucfirst(strtolower($kota))."</td>
						</tr>
					</table>		
					<br/>
					<br/>
                    <table width=\"700\" border=\"0\">
                        <tr>
                           <td width=\"100\"></td>
                           <td colspan=\"5\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Berdasarkan data Dispenda Kota Palembang, ternyata saudara masih menunggak Pajak Bumi dan Bangunan, PBB yang menjadi kewajiban saudara adalah sebagai berikut :</td>
                        </tr>
                        <tr>
                           <td></td>
                           <td colspan=\"5\"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td width=\"200\">Nomor SPPT</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\" width=\"375\">".$op['NOP']."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Nama Wajib Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">".$op['WP_NAMA']."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Alamat Wajib Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">".$op['WP_ALAMAT']."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Alamat Objek Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">".$op['ALAMAT_OP']."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Tahun Pajak</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">".$listTahun."</td>
                        </tr> 
						<!--
                        <tr>
                            <td></td>
                            <td>PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td>Rp. ".number_format($op['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Denda</td>
                            <td width=\"10\">:</td>
                            <td>Rp. ".number_format($op['DENDA'],0,',','.')."</td>
                        </tr> 
						-->
                        <tr>
                            <td></td>
                            <td>PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td width=\"30\" >Rp </td>
							<td width=\"90\"align=\"right\">".number_format($sumTagihan,0,',','.')."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Denda</td>
                            <td width=\"10\">:</td>
                            <td width=\"30\">Rp </td>
							<td width=\"90\" align=\"right\">".number_format($sumDenda,0,',','.')."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Jumlah PBB Terhutang</td>
                            <td width=\"10\">:</td>
                            <td width=\"30\">Rp </td>
							<td width=\"90\" align=\"right\">".number_format($sumTagihan+$sumDenda,0,',','.')."</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Jatuh Tempo</td>
                            <td width=\"10\">:</td>
                            <td width=\"auto\" colspan=\"2\"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Bank Tempat Pembayaran</td>
                            <td width=\"10\">:</td>
                            <td colspan=\"2\">$bank</td>
                        </tr>
                        <tr>
                           <td></td>
                           <td colspan=\"5\"></td>
                        </tr>
                        <tr>
                           <td></td>
                           <td colspan=\"5\" width=\"575\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Diminta untuk segera melunasi kewajiban membayar PBB diatas guna menghindari penerapan sanksi lebih lanjut sesuai dengan peraturan perundang-undangan yang berlaku, Jatuh tempo pada tanggal 30 September 2015 karenanya akan dikenakan denda sebesar 2 % perbulan sejak tanggal Jatuh tempo.</td>
                        </tr>
                        <tr>
                           <td></td>
                           <td colspan=\"5\"></td>
                        </tr>
                        <tr>
                           <td></td>
                           <td colspan=\"5\" width=\"575\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Dalam hal saudara telah membayar dan melunasi PBB tersebut diatas, kami harapkan saudara dapat menyampaikan salinan bukti lunas PBB kepada kami.</td>
                        </tr>
                    </table>
                    <br/><br/>
                    
                    <br/>
                        ".$ttd."
						<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                &nbsp;&nbsp;&nbsp;
						<table border=\"1\" width=\"310\" cellpadding=\"5\">
							<tr><td>
						
						<table border=\"0\" width=\"300\">
                            <tr>
                                <td colspan=\"2\" align=\"center\"><b>UNTUK DIMAKLUMI</b></td>
                            </tr>
							<tr>
                                <td colspan=\"2\" align=\"justify\">Berdasarkan Perda Nomor 3 Tahun 2011 Tentang Pajak Bumi dan Bangunan  Perkotaan :</td>
                            </tr>
							<tr>
                                <td align=\"left\" width=\"20\">a</td>
								<td align=\"justify\" width=\"280\">Pasal 14 ayat 1 bahwa Pajak yang terutang berdasarkan Surat Tagihan Pajak Daerah (STPD), Surat Keputusan Pembetulan, Surat Keputusan Keberatan, dan Putusan Banding yang tidak atau kurang dibayar oleh wajib pajak pada waktunya dapat ditagih dengan surat paksa.</td>
                            </tr>
							<tr>
                                <td align=\"left\" width=\"20\">b</td>
								<td align=\"justify\">Pasal 14 ayat 2 bahwa Penagihan Pajak dengan Surat Paksa dilaksanakan berdasarkan peraturan perundang-undangan.</td>
                            </tr>
                        </table>
						</td></tr>
						</table>
                    </td>
                </tr>
            </table>
            ";

	return $html;
}

function countDay($s_day, $e_day){
    $startTimeStamp = strtotime($s_day);
    $endTimeStamp = strtotime($e_day);
    
    if($startTimeStamp > $endTimeStamp)
        return 0;
    
    $timeDiff = abs($endTimeStamp - $startTimeStamp);

    $numberDays = $timeDiff/86400;  // 86400 seconds in one day

    //convert to integer
    $numberDays = intval($numberDays);

    return $numberDays;
}

function getPenalty($pbbHarusDibayar, $jatuhTempo){
    global $totalBulanPajak, $tipeKalkulasiPajak, $denda;
    
    $penalty = 0;
    
    switch($tipeKalkulasiPajak){
        case 0 : $day = countDay($jatuhTempo,date('Y-m-d'));
                 $penalty = $denda * $day * $pbbHarusDibayar /100;
                 break;
        case 1 : $month = ceil(countDay($jatuhTempo,date('Y-m-d'))/30);
                 if($month > $totalBulanPajak){
                     $month = $totalBulanPajak;
                 }
                 $penalty = $denda * $month * $pbbHarusDibayar / 100;
                 break;
    }
    
    return $penalty;
}

function getPbbspptById($nop,$thnpajak){
    global $DBLinkLookUp, $DBLink, $denda, $totalBulanPajak, $tipeKalkulasiPajak;
    $data = array();

    $sql = "SELECT * FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thnpajak'";
	// echo $sql; exit;
    $result = mysqli_query($DBLinkLookUp, $sql);

    if($result){
        $buffer = mysqli_fetch_assoc($result);
        $data['NOP'] = $buffer['NOP'];
        $data['WP_NAMA'] = $buffer['WP_NAMA'];
        /*$temp_wil = mysql_query("SELECT CPC_TKL_KELURAHAN AS KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID='".$buffer['WP_KELURAHAN']."'",$DBLink);
        $kel = mysqli_fetch_assoc($temp_wil);

        $temp_wil = mysql_query("SELECT CPC_TKC_KECAMATAN AS KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID='".$buffer['WP_KECAMATAN']."'",$DBLink);
        $kec = mysqli_fetch_assoc($temp_wil);

        $temp_wil = mysql_query("SELECT CPC_TK_KABKOTA AS KABKOTA FROM cppmod_tax_kabkota WHERE CPC_TK_ID='".$buffer['WP_KOTAKAB']."'",$DBLink);
        $kab = mysqli_fetch_assoc($temp_wil);

        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'].
                             ", Kel. ".ucwords(strtolower($kel['KELURAHAN'])).", Kec. ".ucwords(strtolower($kec['KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($kab['KABKOTA']));*/
        $data['WP_ALAMAT_JALAN'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'];
        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'].
                        ", Kel. ".ucwords(strtolower($buffer['WP_KELURAHAN'])).", Kec. ".ucwords(strtolower($buffer['WP_KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($buffer['WP_KOTAKAB']));
        $data['SPPT_TAHUN_PAJAK'] = $buffer['SPPT_TAHUN_PAJAK'];
        $data['SPPT_TANGGAL_JATUH_TEMPO'] = $buffer['SPPT_TANGGAL_JATUH_TEMPO'];
        $data['SPPT_PBB_HARUS_DIBAYAR'] = $buffer['SPPT_PBB_HARUS_DIBAYAR'];
        $data['ALAMAT_OP'] = $buffer['OP_ALAMAT']." RT ".$buffer['OP_RT']."/RW ".$buffer['OP_RW'].
                             ", Kel. ".ucwords(strtolower($buffer['OP_KELURAHAN'])).", Kec. ".ucwords(strtolower($buffer['OP_KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($buffer['OP_KOTAKAB']));

        $data['DENDA'] = getPenalty($data['SPPT_PBB_HARUS_DIBAYAR'], $data['SPPT_TANGGAL_JATUH_TEMPO']);
    }else{
        echo mysqli_error($DBLink);
    }
    return $data;
}

class MYPDF extends TCPDF {
    public function Header() {
        global $sRootPath,$draf;
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        $this->SetAlpha(0.3);
//	if ($draf == 1) $this->Image($sRootPath.'image/DRAF.png', 50, 70, 100, '', '', '', '', false, 300, '', false, false, 0);
//	else if ($draf == 0) $this->Image($sRootPath.'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
    }
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 7, 4);
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

$fileLogo =  getConfigValue($appID,'LOGO_CETAK_PDF');

$pdf->AddPage('P', 'F4');
//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
$resTunggakan = getTunggakan($nop);
$i = 0;
$listTagihan = $listDenda = $tagihanPlusDenda = $listTahun = array();
while ($row = mysqli_fetch_assoc($resTunggakan)) {
    $listTagihan[]      = $row['SPPT_PBB_HARUS_DIBAYAR'];//
    $listDenda[]        = getPenalty($listTagihan[$i],$row['SPPT_TANGGAL_JATUH_TEMPO']);
    $tagihanPlusDenda[] = $listTagihan[$i]+$listDenda[$i];
    $listTahun[] = $row['SPPT_TAHUN_PAJAK'];
    $i++;
}
$lastTagihan = end($listTagihan);
// $thnpajak = $listTahun[count($listTahun)-1];
$wp = getPbbspptById($nop,$thnpajak);
// echo "<pre>";
// print_r($wp); exit;
$HTML = getHTML($wp);
$pdf->writeHTML($HTML, true, false, false, false, '');
//echo $sRootPath.'image/'.$fileLogo;
$pdf->Image($sRootPath.'image/'.$fileLogo, 20, 12, 35, '', '', '', '', false, 300, '', false);
$pdf->SetAlpha(0.3);

//Close and output PDF document
$pdf->Output($nop.'.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE
//============================================================+
