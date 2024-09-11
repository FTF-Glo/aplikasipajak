<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_khusus_2016', '', dirname(__FILE__))).'/';
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

$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$thnpajak = @isset($_REQUEST['thnpajak']) ? $_REQUEST['thnpajak'] : "";
$dbhost = @isset($_REQUEST['dbhost']) ? $_REQUEST['dbhost'] : "";
$dbuser = @isset($_REQUEST['dbuser']) ? $_REQUEST['dbuser'] : "";
$dbpwd = @isset($_REQUEST['dbpwd']) ? $_REQUEST['dbpwd'] : "";
$dbname = @isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : "";
$kepala = @isset($_REQUEST['kepala']) ? $_REQUEST['kepala'] : "";
$kota = @isset($_REQUEST['kota']) ? $_REQUEST['kota'] : "";
$nip = @isset($_REQUEST['nip']) ? $_REQUEST['nip'] : "";
$jabatan = @isset($_REQUEST['jabatan']) ? $_REQUEST['jabatan'] : "";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
$denda = @isset($_REQUEST['denda']) ? $_REQUEST['denda'] : "";
$totalBulanPajak = @isset($_REQUEST['totalBulanPajak']) ? $_REQUEST['totalBulanPajak'] : "";
$tipeKalkulasiPajak = @isset($_REQUEST['tipeKalkulasiPajak']) ? $_REQUEST['tipeKalkulasiPajak'] : "";
$kd_dispenda = @isset($_REQUEST['kd_dispenda']) ? $_REQUEST['kd_dispenda'] : "";
$kd_bidang = @isset($_REQUEST['kd_bidang']) ? $_REQUEST['kd_bidang'] : "";
$kd_seksi = @isset($_REQUEST['kd_seksi']) ? $_REQUEST['kd_seksi'] : "";
$appId = @isset($_REQUEST['appId']) ? $_REQUEST['appId'] : "";


SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost, $dbuser, $dbpwd, $dbname);


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

function getAuthor($uname) {
	global $DBLink,$appID;
	$id= $appID;
	$qry = "select nm_lengkap from TBL_REG_USER_NOTARIS where userId = '".$uname."'";
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
	global $DBLink;
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

function getHTML ($op) {
    global $tgl,$kepala,$nip,$jabatan, $kota,$bank,$kd_dispenda,$kd_bidang,$kd_seksi;
    $tahun = date("Y");
    $tgl = explode("-",$op['SPPT_TANGGAL_JATUH_TEMPO']);

    $html = "<table width=\"700\" border=\"1\" cellpadding=\"2\">
                <tr>
                    <td align=\"center\">
                        <br/><br/><br/><br/><br/><br/>
                        <font size=\"8\">Jl. Merdeka No. 21 Palembang<br/>
                        Telp. 0711-352282 - Fax 0711317393</font>
                    </td>
                    <td align=\"center\">
                        S T P D<br/>
                        (SURAT TAGIHAN PAJAK DAERAH)<br/><br/><br/>
                        Tahun Pajak : ".$op['SPPT_TAHUN_PAJAK']."
                    </td>
                    <td align=\"center\">
                        Nomor Urut<br/><br/><br/>
                        &nbsp;<table align=\"center\" border=\"1\" width=\"100%\">
                            <tr>
                                <td width=\"20%\">$kd_dispenda</td>
                                <td width=\"20%\">$kd_bidang</td>
                                <td width=\"20%\">".substr($op['NOP'],4,3)."</td>
                                <td width=\"20%\">".substr($op['NOP'],7,3)."</td>
                                <td width=\"20%\">".substr($op['NOP'],14,3)."</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">
                        &nbsp;<table width=\"100%\">
                            <tr>
                                <td width=\"35%\">Nama</td>
                                <td width=\"5%\">:</td>
                                <td width=\"60%\">".$op['WP_NAMA']."</td>
                            </tr>
                            <tr>
                                <td width=\"35%\">Alamat</td>
                                <td width=\"5%\">:</td>
                                <td width=\"60%\">".$op['WP_ALAMAT']."</td>
                            </tr>
                            <tr>
                                <td width=\"35%\">Tanggal Jatuh Tempo</td>
                                <td width=\"5%\">:</td>
                                <td width=\"60%\">".($tgl[2]."/".$tgl[1]."/".$tgl[0])."</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">
                        <table width=\"100%\">
                            <tr>
                                <td width=\"2%\">I.</td>
                                <td width=\"98%\">Berdasarkan Pasal 100 Undang-Undang Nomor 28 Tahun 2009 telah dilakukan penelitian dan / atau pemeriksaan dan / atau keterangan lain atas pelaksanaan kewajiban Pajak Bumi dan Bangunan berikut :</td>
                            </tr>
                            <tr>
                                <td width=\"2%\">&nbsp;</td>
                                <td width=\"98%\">
                                    <table width=\"100%\">
                                        <tr>
                                            <td width=\"35%\">Nama</td>
                                            <td width=\"5%\">:</td>
                                            <td width=\"60%\">".$op['WP_NAMA']."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"35%\">NOP</td>
                                            <td width=\"5%\">:</td>
                                            <td width=\"60%\">".$op['NOP']."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"35%\">Alamat</td>
                                            <td width=\"5%\">:</td>
                                            <td width=\"60%\">".$op['ALAMAT_OP']."</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td width=\"2%\">II.</td>
                                <td width=\"98%\">Dari penelitian dan / atau pemeriksaan tersebut diatas, penghitungan jumlah yang masih harus dibayar adalah :</td>
                            </tr>
                            <tr>
                                <td width=\"2%\">&nbsp;</td>
                                <td width=\"98%\">
                                    <table width=\"100%\">
                                        <tr>
                                            <td width=\"5%\">1.</td>
                                            <td width=\"60%\">Pajak yang kurang dibayar</td>
                                            <td width=\"35%\">Rp. ".number_format($op['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"5%\">2.</td>
                                            <td width=\"60%\">Sanksi Administrasi</td>
                                            <td width=\"35%\">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width=\"5%\">&nbsp;</td>
                                            <td width=\"60%\">a. Bunga (Pasal 100(2))</td>
                                            <td width=\"35%\">Rp. ".number_format($op['DENDA'],0,',','.')."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"5%\">3.</td>
                                            <td width=\"60%\">Jumlah yang harus dibayar (1+2.a)</td>
                                            <td width=\"35%\">Rp. ".number_format(($op['TOTAL_HARUS_DIBAYAR']),0,',','.')."</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">Dengan huruf :<br/>&nbsp;&nbsp;&nbsp;&nbsp;<i>".ucwords($op['TERBILANG'])." Rupiah</i></td>
                </tr>
                <tr>
                    <td colspan=\"3\"><u>PERHATIAN</u>
                        <ol>
                            <li>Harap penyetoran dilakukan melalui Kas Daerah (Bank Sumsel Babel)</li>
                            <li>Apabila SPPT ini tidak atau kurang dibayar setelah jatuh tempo maka akan dikenakan sanksi administrasi berupa bunga sebesar 2 % per bulan.</li>
                        </ol>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">
                        <table border=\"0\">
						<tr>
							<td>&nbsp;</td>
							<td align=\"left\">an. KEPALA DINAS PENDAPATAN DAERAH
							<br/>
							KOTA ".strtoupper($kota)."
							<br/>
							SEKERTARIS,
							<br/>
							<br/>
							<br/>
							<br/>
							<br/>
							<br/>
							".$kepala."
							<br/>
							".$jabatan."
							<br/>
							NIP ".$nip."
							</td>
						</tr>
					</table>
                    </td>
                </tr>
            </table>
            <table width=\"700\"><tr><td align=\"center\">-------------------------&nbsp; <i>gunting disini</i> &nbsp;-------------------------</td></tr></table>
            <table width=\"700\" border=\"1\" cellpadding=\"3\">
                <tr>
                    <td>
                        <table width=\"100%\" cellpadding=\"3\">
                            <tr>
                                <td width=\"32%\"></td>
                                <td width=\"32%\"></td>
                                <td width=\"32%\" align=\"center\">No. STPD : _____________________</td>
                            </tr>
                            <tr>
                                <td colspan=\"3\" align=\"center\">TANDA TERIMA</td>
                            </tr>
                            <tr>
                                <td colspan=\"3\">
                                    
                                    <table cellpadding=\"3\" width=\"100%\">
                                        <tr>
                                            <td width=\"50\"></td>
                                            <td width=\"130\">NOP</td>
                                            <td width=\"10\">:</td>
                                            <td width=\"470\">".$op['NOP']."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"50\"></td>
                                            <td >Nama Wajib Pajak</td>
                                            <td >:</td>
                                            <td >".$op['WP_NAMA']."</td>
                                        </tr>
                                        <tr>
                                            <td width=\"50\"></td>
                                            <td >Alamat Wajib Pajak</td>
                                            <td >:</td>
                                            <td >".$op['WP_ALAMAT_JALAN']."</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td width=\"32%\"></td>
                                <td width=\"32%\"></td>
                                <td width=\"32%\" align=\"center\">$kota,_______________$tahun <br/>
                                                                  Yang Menerima<br/><br/><br/>
                                                                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td><br/><br/><br/><strong>NOTE : </strong><i>Silahkan abaikan surat ini apabila wajib pajak telah membayar PBB tahun tersebut diatas dan mohon agar bukti
                    lunas pembayaran PBB dari bank atas tahun dimaksud untuk diserahkan ke seksi penagihan, keberatan
                    dan pengurangan bidang PBB dan BPHTB dinas pendapatan daerah kota $kota.</i>
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

function getNoUrut(){
    $fget = fopen("nourut.txt", "r");
    $tmp = stream_get_contents($fget);
    fclose($fget);
    return $tmp;
}

function getPbbspptById($nop,$thnpajak){
    global $DBLinkLookUp, $DBLink, $denda, $totalBulanPajak, $tipeKalkulasiPajak;
    $data = array();

    $sql = "SELECT * FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thnpajak'";
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
        $kab = mysqli_fetch_assoc($temp_wil);*/
        $data['WP_ALAMAT_JALAN'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'];
        $data['WP_ALAMAT'] = $buffer['WP_ALAMAT']." RT ".$buffer['WP_RT']."/RW ".$buffer['WP_RW'].
                             ", Kel. ".ucwords(strtolower($buffer['WP_KELURAHAN'])).", Kec. ".ucwords(strtolower($buffer['WP_KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($buffer['WP_KOTAKAB']));
        $data['SPPT_TAHUN_PAJAK'] = $buffer['SPPT_TAHUN_PAJAK'];
        $data['SPPT_TANGGAL_JATUH_TEMPO'] = $buffer['SPPT_TANGGAL_JATUH_TEMPO'];
        $data['SPPT_PBB_HARUS_DIBAYAR'] = $buffer['SPPT_PBB_HARUS_DIBAYAR'];
        $data['ALAMAT_OP'] = $buffer['OP_ALAMAT']." RT ".$buffer['OP_RT']."/RW ".$buffer['OP_RW'].
                             ", Kel. ".ucwords(strtolower($buffer['OP_KELURAHAN'])).", Kec. ".ucwords(strtolower($buffer['OP_KECAMATAN'])).", Kota/Kab. ".ucwords(strtolower($buffer['OP_KOTAKAB']));
        $data['KD_KECAMATAN'] = substr($buffer['OP_KECAMATAN_KODE'],4);
        $data['DENDA'] = getPenalty($data['SPPT_PBB_HARUS_DIBAYAR'], $data['SPPT_TANGGAL_JATUH_TEMPO']);
        $data['TOTAL_HARUS_DIBAYAR'] = $data['DENDA'] + $buffer['SPPT_PBB_HARUS_DIBAYAR'];
        $data['TERBILANG'] = SayInIndonesian(number_format($data['TOTAL_HARUS_DIBAYAR'],0,'',''));

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

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

//$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(5, 14, 10);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions=array('modify'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null);
$HTML = "";


$pdf->AddPage('P', 'F4');
$wp = getPbbspptById($nop,$thnpajak);
$HTML = getHTML($wp);

//$fileLogo =  getConfigValue($appId,'LOGO_CETAK_SK');
$fileLogo =  getConfigValue($appId,'LOGO_CETAK_PDF');

//$pdf->Image($sRootPath.'image/'.$fileLogo, 25, 13, 28, '', '', '', '', false, 200, '', false);
$pdf->Image($sRootPath.'image/'.$fileLogo, 20, 15, 35, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($HTML, true, false, false, false, '');

$pdf->Output('stpdpbb_'.$nop.'.pdf', 'I');
