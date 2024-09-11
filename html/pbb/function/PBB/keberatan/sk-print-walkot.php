<?php

error_reporting(E_ALL);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'keberatan', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/uuid.php");
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
require_once($sRootPath."function/PBB/pengurangan/pengurangan-lib.php");

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
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

function hitung($aValue) {
    
        global $DBLink, $minimum_njoptkp, $minimum_sppt_pbb_terhutang;

        $NJOPTKP = $minimum_njoptkp;
        $minPBBHarusBayar = $minimum_sppt_pbb_terhutang;


        $NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'];
        
        //Penentuan NJOPTKP Pidie
        //if($aValue['CPM_NJOP_BANGUNAN'] == 0)
        //    $NJOPTKP = 0;
        //Penentuan NJOPTKP Pidie Jaya & Bireuen
        //if($aValue['CPM_NJOP_BANGUNAN'] <= 10000000)
        //    $NJOPTKP = 0;
        //Penentuan NJOPTKP Kupang
        //if($NJOP < 250000000)
        //    $NJOPTKP = 0;
        
        if($NJOP > $NJOPTKP)
            $NJKP = $NJOP - $NJOPTKP;
        else $NJKP = 0;

        $aValue['OP_NJOP'] = $NJOP;
        $aValue['OP_NJKP'] = $NJKP;
        $aValue['OP_NJOPTKP'] = $NJOPTKP;

        $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                        CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND
                        CPM_TRF_NILAI_ATAS >= " . $NJKP; 
        $resTarif = mysqli_query($DBLink, $cari_tarif);
        if(!$resTarif){
                echo mysqli_error($DBLink);
                echo $cari_tarif;
        }
        
        $dataTarif = mysqli_fetch_array($resTarif);
        $op_tarif = $dataTarif['CPM_TRF_TARIF'];
        $aValue['OP_TARIF'] = $op_tarif;
        $PBB_HARUS_DIBAYAR = $NJKP * ($op_tarif / 100);

        if($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
            $PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
        $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $PBB_HARUS_DIBAYAR;
        
        return $aValue;
    }

function getConfigValue ($id,$key) {
	global $DBLink;	
	//$id= $appID;
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

function getData($idssb) {
	global $data,$DBLink,$dataNotaris;
	
	$query = "SELECT S.*, R.*, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN, DATE_FORMAT(R.CPM_OB_LHP_DATE,'%d-%m-%Y') AS LHP_DATE, DATE_FORMAT(S.CPM_DATE_RECEIVE,'%d-%m-%Y') AS TGL_MASUK 
			FROM cppmod_pbb_services S
			JOIN cppmod_pbb_service_objection R
			JOIN cppmod_tax_kecamatan TKEC
			JOIN cppmod_tax_kelurahan TKEL
			WHERE S.CPM_ID=R.CPM_OB_SID 
			AND TKEC.CPC_TKC_ID = S.CPM_OP_KECAMATAN
			AND TKEL.CPC_TKL_ID = S.CPM_OP_KELURAHAN
			AND CPM_ID='$idssb'";
	#echo $query;exit;
	$res = mysqli_query($DBLink, $query);
	$record = mysqli_num_rows($res);
	if($record<1){
		echo "Data tidak ada!";exit;	
	}
	if ( $res === false ){
		echo $query."<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res,"data"));	
	$dt = $dataNotaris->data[0];
	return $dt;
} 

function getHTML ($idssb, $initData, $fileLogo) {
	global $uname,$NOP,$appId;
	$data 				= getData($idssb);
	$kota 				= getConfigValue ($appId,'NAMA_KOTA');
	$kotaPengesahan		= ucfirst(strtolower(getConfigValue ($appId,'NAMA_KOTA_PENGESAHAN')));
	$pejabatSK 			= getConfigValue ($appId,'PEJABAT_SK');
	$namaPejabatSK		= getConfigValue ($appId,'NAMA_PEJABAT_SK');
	$header				= getConfigValue ($appId,'C_HEADER_SK');
	$judul				= getConfigValue ($appId,'C_JUDUL_SK');
	$kabkot				= getConfigValue ($appId,'C_KABKOT');
	$date				= tgl_indo(date("d-m-Y"));
	$rReduce 			= 0;
	$finalReduce 		= ($data->CPM_SPPT_DUE - $rReduce);
	$otomatis			= getConfigValue($appId,'NOMOR_SK_OTOMATIS');
	$SKFormat			= getConfigValue($appId,'NOMOR_SK_FORMAT');
	$SKNumber			= $data->CPM_OB_SK_NUMBER;
	
	$vObjection['CPM_NJOP_TANAH']		= $data->CPM_OB_NJOP_TANAH_APP;
    $vObjection['CPM_NJOP_BANGUNAN']   	= $data->CPM_OB_NJOP_BANGUNAN;
    $tagihan                            = hitung($vObjection);
//	print_r($tagihan); exit();
	if($data->CPM_OB_LUAS_TANAH==0){
		$njopTanahSemula = 0;
		$njopTanahAkhir = 0;
	} else {
		$njopTanahSemula = number_format(ceil(($data->CPM_OB_NJOP_TANAH/$data->CPM_OB_LUAS_TANAH)),0,',','.');
		$njopTanahAkhir = number_format(ceil(($data->CPM_OB_NJOP_TANAH_APP/$data->CPM_OB_LUAS_TANAH)),0,',','.');
	}
	if($data->CPM_OB_LUAS_BANGUNAN==0){
		$njopBangunanSemula = 0;
		$njopBangunanAkhir = 0;
	} else {
		$njopBangunanSemula = $njopBangunanAkhir = number_format(ceil(($data->CPM_OB_NJOP_BANGUNAN/$data->CPM_OB_LUAS_BANGUNAN)),0,',','.');
	}
	
	$ketetapanSemula	= number_format(($data->CPM_OB_NJOP_TANAH + $data->CPM_OB_NJOP_BANGUNAN),0,',','.');
	$ketetapanMenjadi	= number_format(($data->CPM_OB_NJOP_TANAH_APP + $data->CPM_OB_NJOP_BANGUNAN),0,',','.');
	
	$html = "
	<html>
<body>
	<table border=\"0\" width=\"650\" cellpadding=\"3\">
		<tr>
			<td align=\"center\" width=\"130\"></td>
			<td align=\"center\" colspan = \"2\" width=\"520\">".$header."
			</td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><hr style=\"height: 2px\"></td>
		</tr>
	</table>
	<table border=\"0\" width=\"650\" cellpadding=\"8\">
		<tr>
			<td align=\"center\" colspan=\"3\"><b>PEMERINTAH ".$kabkot." ".$kota."<br>NOMOR : ".(!empty($SKNumber) ? $SKNumber : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$SKFormat)." </b></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><b>TENTANG<br>PENGURANGAN PAJAK BUMI DAN BANGUNAN PERKOTAAN</b></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"3\"><b>".$pejabatSK.",</b></td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"130\" rowspan=\"2\">Menimbang :</td>
			<td valign=\"top\" width=\"40\">a.</td>
			<td align=\"justify\" width=\"480\">
			<!-- &nbsp;&nbsp;&nbsp;&nbsp; -->bahwa sehubungan dengan Surat Permohonan Keberatan Pajak Bumi dan Bangunan Perkotaan, atas nama Wajib Pajak <b>".$data->CPM_WP_NAME."</b><br>
			<!--&nbsp;&nbsp; -->Nomor : ".$data->CPM_ID." Tanggal <i>".tgl_indo($data->TGL_MASUK)."</i> atas SPPT PBB NOP <i>".$data->CPM_OP_NUMBER."</i> Tahun Pajak ".$data->CPM_SPPT_YEAR." yang diterima oleh petugas dan dengan mempertimbangkan hasil penelitian yang dituangkan dalam Laporan Hasil Penelitian Keberatan PBB<br>
			<!--&nbsp;&nbsp;&nbsp;&nbsp; -->Nomor : ".$data->CPM_OB_LHP_NUMBER." Tanggal <i>".tgl_indo($data->LHP_DATE)."</i> perlu diterbitkan keputusan atas permohonan keberatan PBB dimaksud;
			</td>
		</tr>
		<tr>
			<td valign=\"top\">b.</td>
			<td align=\"justify\">bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a, perlu menetapkan Keputusan Bupati Kab. Bangka Barat tentang Keberatan Pajak Bumi dan Bangunan Perkotaan;</td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"130\" rowspan=\"4\">Mengingat :</td>
			<td valign=\"top\" width=\"40\">1.</td>
			<td align=\"justify\" width=\"480\">Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah (Lembaran Negara Republik Indonesia Tahun 2009 Nomor 130, Tambahan Lembaran Negara Republik Indonesia Nomor 5049).</td>
		</tr>
		<tr>
			<td valign=\"top\">2.</td>
			<td align=\"justify\">Peraturan Menteri Keuangan Nomor 110 Tahun 2009 tentang Pemberian Pengurangan Pajak Bumi dan Bangunan.</td>
		</tr>
		<tr>
			<td valign=\"top\">3.</td>
			<td align=\"justify\">Peraturan Daerah Bangka Barat Nomor 3 Tahun 2011 tentang Pajak Bumi dan Bangunan Perkotaan (Lembaran Daerah Bangka Barat Tahun 2011 Nomor 3 Seri B).</td>
		</tr>
		<tr>
			<td valign=\"top\">4.</td>
			<td align=\"justify\">Peraturan Bupati Bangka Barat Nomor 12.a Tahun 2013 tentang Tata Cara Pemberian Pengurangan dan Penyelesaian Keberatan Pajak Bumi dan Bangunan Perkotaan.</td>
		</tr>
	</table>
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<table border=\"0\" width=\"650\" cellpadding=\"10\">
		<tr>
			<td align=\"center\" colspan=\"4\"><b>MEMUTUSKAN:</b><br>
			</td>
		</tr>
		<tr>
			<td align=\"left\" colspan=\"4\">Menetapkan:
			</td>
		</tr>
		<tr>
			<td valign=\"top\" width=\"100\">KESATU</td>
			<td valign=\"top\" width=\"40\">:</td>
			<td align=\"justify\" colspan=\"2\" width=\"510\">Mengabulkan seluruhnya / Mengabulkan sebagian / Menolak *) permohonan keberatan PBB terutang yang tercantum dalam SPPT PBB NOP <i>".$data->CPM_OP_NUMBER."</i> Tahun Pajak ".$data->CPM_SPPT_YEAR." sebagai berikut :<br>
				a. Wajib Pajak<br>
				&nbsp;&nbsp;&nbsp;&nbsp;<table border=\"0\">
					<tr>
						<td width=\"120\">Nama</td><td width=\"5\">:</td><td width=\"350\">".$data->CPM_REPRESENTATIVE."</td>
					</tr>
					<tr>
						<td>Alamat</td><td>:</td><td>".$data->CPM_WP_ADDRESS."</td>
					</tr>
				</table>
				<br><br>
				b. Obyek Pajak <br>
					&nbsp;&nbsp;&nbsp;&nbsp;<table border=\"0\">
					<tr>
						<td width=\"120\">Nama</td><td width=\"5\">:</td><td width=\"350\">".$data->CPM_WP_NAME."</td>
					</tr>
					<tr>
						<td width=\"120\">NOP</td><td width=\"5\">:</td><td width=\"350\">".$data->CPM_OP_NUMBER."</td>
					</tr>
					<tr>
						<td>Alamat</td><td>:</td><td>".$data->CPM_OP_ADDRESS."</td>
					</tr>
					<tr>
						<td>Kelurahan</td><td>:</td><td>".$data->CPC_TKL_KELURAHAN."</td>
					</tr>
					<tr>
						<td>Kecamatan</td><td>:</td><td>".$data->CPC_TKC_KECAMATAN."</td>
					</tr>
					<tr>
						<td>Kota</td><td>:</td><td>".$kota."</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign=\"top\">KEDUA</td>
			<td valign=\"top\">:</td>
			<td align=\"justify\" colspan=\"2\">Sesuai dengan diktum KESATU, besarnya Pajak Bumi dan Bangunan yang  terutang menjadi sebesar Rp ".number_format($tagihan['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')." (".SayInIndonesian($tagihan['SPPT_PBB_HARUS_DIBAYAR'])." rupiah)
			</td>
		</tr>
		<tr>
			<td valign=\"top\">KETIGA</td>
			<td valign=\"top\">:</td>
			<td align=\"justify\" colspan=\"2\">Penghitungan Besarnya Pajak Bumi dan Bangunan Perkotaan yang terutang sebagaimana dimaksud pada diktum KEDUA adalah sebagai berikut :<br><br>
				<font size=\"9\">
				<table border=\"1\" cellpadding=\"4\">
					<tr align=\"center\">
						<td width=\"58\" rowspan=\"2\" valign=\"middle\">URAIAN</td>
						<td width=\"122\" colspan=\"2\">LUAS<br>(M2)</td>
						<td width=\"150\" colspan=\"2\">NILAI JUAL OBJEK PAJAK<br>(M2)</td>
						<td width=\"85\" rowspan=\"2\" style=\"vertical-align:text-middle;\">TOTAL NJOP</td>
						<td width=\"90\" rowspan=\"2\" valign=\"middle\">KETETAPAN (PBB YANG HARUS DIBAYAR)</td>
					</tr>
					<tr align=\"center\">
						<td width=\"50\">BUMI</td>
						<td width=\"72\">BANGUNAN</td>
						<td width=\"70\">BUMI</td>
						<td width=\"80\">BANGUNAN</td>
					</tr>
					<tr align=\"center\">
						<td>SEMULA</td>
						<td align=\"right\">".($data->CPM_OB_LUAS_TANAH!='' ? number_format($data->CPM_OB_LUAS_TANAH,0,',','.') : 0)."</td>
						<td align=\"right\">".($data->CPM_OB_LUAS_BANGUNAN!='' ? number_format($data->CPM_OB_LUAS_BANGUNAN,0,',','.') : 0)."</td>
						<td align=\"right\">".$njopTanahSemula."</td>
						<td align=\"right\">".$njopBangunanSemula."</td>
						<td align=\"right\">".$ketetapanSemula."</td>
						<td align=\"right\">".($data->CPM_SPPT_DUE!='' ? number_format($data->CPM_SPPT_DUE,0,',','.') : 0)."</td>
					</tr>
					<tr align=\"center\">
						<td>MENJADI</td>
						<td align=\"right\">".($data->CPM_OB_LUAS_TANAH!='' ? number_format($data->CPM_OB_LUAS_TANAH,0,',','.') : 0)."</td>
						<td align=\"right\">".($data->CPM_OB_LUAS_BANGUNAN!='' ? number_format($data->CPM_OB_LUAS_BANGUNAN,0,',','.') : 0)."</td>
						<td align=\"right\">".$njopTanahAkhir."</td>
						<td align=\"right\">".$njopBangunanAkhir."</td>
						<td align=\"right\">".$ketetapanMenjadi."</td>
						<td align=\"right\">".($tagihan['SPPT_PBB_HARUS_DIBAYAR']!='' ? number_format($tagihan['SPPT_PBB_HARUS_DIBAYAR'],0,',','.') : 0)."</td>
					</tr>
				</table>
				</font>
			</td>
		</tr>
		<tr>
			<td valign=\"top\" rowspan=\"2\">KEEMPAT</td>
			<td valign=\"top\" rowspan=\"2\">:</td>
			<td valign=\"top\" width=\"35\">a.</td>
			<td align=\"justify\" valign=\"top\" width=\"475\">Keputusan ini mulai berlaku pada tanggal ditetapkan dan apabila ternyata terdapat kekeliruan akan diadakan perubahan dan perbaikan sebagaimana mestinya.
			</td>
		</tr>
		<tr>
			<td valign=\"top\">b.</td>
			<td valign=\"top\">Asli keputusan ini disampaikan kepada Wajib Pajak dan Salinan Keputusan ini disimpan sebagai arsip Badan Pengelolahan Pajak dan Retribusi Daerah Bangka Barat.</td>
		</tr>
		<tr>
			<td colspan=\"4\" align=\"left\">
				<table border=\"0\">
				<tr>
					<td width=\"200\"></td>
					<td width=\"200\"></td>
					<td width=\"250\">
						Ditetapkan di ".$kotaPengesahan."<br>
						pada tanggal ........... <br>
						<br>
						<b>".$pejabatSK."</b><br>
						<br>
						<br>
						<br>
						<br>
						<br>
						<b>".$namaPejabatSK."</b><br>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	";	  
	return $html;
}

function getInitData($id=""){    
    global $DBLink;	
    
	if($id == '') return getDataDefault();
	
    $qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
		return getDataDefault();
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'],8,2).'-'.substr($row['CPM_DATE_RECEIVE'],5,2).'-'.substr($row['CPM_DATE_RECEIVE'],0,4);
			return $row;
        }                
    }
}

function getDataDefault(){
	$default = array('CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '', 
	'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '', 
	'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '');
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q); 
$q = $json->decode($q);

$idssb = $q->svcId;
$appId = $q->appId;
$initData = getInitData($idssb);

$minimum_njoptkp =  getConfigValue($appId,'minimum_njoptkp');
$minimum_sppt_pbb_terhutang =  getConfigValue($appId,'minimum_sppt_pbb_terhutang');

$v = count($q);

$NOP = "";//$initData['CPM_OP_NUMBER'];
// create new PDF document
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
$pdf->SetMargins(12, 14, 12);
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
	
	
	//echo $appId;exit;
	$fileLogo =  getConfigValue($appId,'LOGO_CETAK_SK');
	//echo $fileLogo;exit;
	$pdf->AddPage('P', 'F4');
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
	$HTML = getHTML($idssb, $initData, $fileLogo);
	$pdf->Image($sRootPath.'image/'.$fileLogo, 30, 13, 28, '', '', '', '', false, 300, '', false);
	$pdf->writeHTML($HTML, true, false, false, false, '');
	//echo $sRootPath.'image/'.$fileLogo;
	
	$pdf->SetAlpha(0.3);

/*for ($i=0;$i<$v;$i++) {
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
*/

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('SK-Keberatan-'.$NOP.'.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
