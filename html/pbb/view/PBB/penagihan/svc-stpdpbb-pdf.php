<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/PBB/dbUtils.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
if(!$q) die();

$q = base64_decode($q);
$q = json_decode($q);

$nop 		= (int)$q->nop;
$thnpajak	= (int)$q->thnpajak;
$appID   	= addslashes($q->appId);



class MYPDF extends TCPDF
{
	public function Header()
	{
		$headerData = $this->getHeaderData();
		$this->SetFont('bookmanoldstyle', '', 10);
		$this->writeHTML($headerData['string']);
		$image_file = K_PATH_IMAGES . 'Logo_doc2.jpg';
		$this->Image($image_file, 16, 8, 18, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setHeaderData($ln = '', $lw = 0, $ht = '', $hs = headerSK(), $tc = array(0, 0, 0), $lc = array(0, 0, 0));
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('STPD');
$pdf->SetSubject('Alfa System STPD');
$pdf->SetKeywords('Alfa System');
// $pdf->setPrintHeader(false);
// $pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(12, 14, 12, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('bookmanoldstyle', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('P', 'F4');

$ids 			= explode(",", $nop);
$idx 			= 0;
$strHTML 		= "";
$strHTMLSingle 	= "";
$sumbuYLogo		= 800;
foreach ($ids as $nop) {
	$strHTMLSingle 	= getHTML($nop,$thnpajak);
	if ($idx > 0) $strHTML .= '<br pagebreak="true"/>';
	$strHTML .= $strHTMLSingle;
	$idx++;
}
// $pdf->Image($sRootPath.'image/'.$fileLogo, 30, 15, 24, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($strHTML, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output('stpdpbb_'.$nop.'_'.substr(uniqid(),5,7).'.pdf', 'I');

function getConfigValue($appID, $key)
{
	global $DBLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $appID . "' and CTR_AC_KEY = '$key'";
	// echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function mysql2json($mysql_result, $name)
{
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
		$json .= "{\n";
		for ($y = 0; $y < count($field_names); $y++) {
			$json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
			if ($y == count($field_names) - 1) {
				$json .= "\n";
			} else {
				$json .= ",\n";
			}
		}
		if ($x == $rows - 1) {
			$json .= "\n}\n";
		} else {
			$json .= "\n},\n";
		}
	}
	$json .= "]\n}";
	return ($json);
}

function getKecamatanNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TKC_KECAMATAN'];
}

function getKelurahanNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TKL_KELURAHAN'];
}

function getKabkotaNama($kode)
{
	global $DBLink;
	$query 	= "SELECT * FROM `cppmod_tax_kabkota` WHERE CPC_TK_ID = '" . $kode . "';";
	$res 	= mysqli_query($DBLink, $query);
	$row	= mysqli_fetch_array($res);
	return $row['CPC_TK_KABKOTA'];
}

function getNoUrut()
{
    $fget = fopen("nourut.txt", "r");
    $tmp = stream_get_contents($fget);
    fclose($fget);
    return $tmp;
}


function getData($nop,$thn)
{
	global $DBLink, $dataNotaris;

	$thn = date('Y'); 

	$q="SELECT 
			g.ID_WP, 
			IFNULL(w.CPM_WP_NAMA,g.WP_NAMA) 	AS WP_NAMA, 
			IFNULL(w.CPM_WP_ALAMAT,g.WP_ALAMAT) AS WP_ALAMAT, 
			IFNULL(w.CPM_WP_RT,g.WP_RT) 		AS WP_RT, 
			IFNULL(w.CPM_WP_RW,g.WP_RW) 		AS WP_RW, 
			IFNULL(w.CPM_WP_KELURAHAN,g.WP_KELURAHAN) AS WP_KELURAHAN, 
			IFNULL(w.CPM_WP_KECAMATAN,g.WP_KECAMATAN) AS WP_KECAMATAN, 
			IFNULL(w.CPM_WP_KOTAKAB,g.WP_KOTAKAB) AS WP_KOTAKAB, 
			IFNULL(w.CPM_WP_PROPINSI,'') 		AS WP_PROPINSI 
		FROM gw_pbb.pbb_sppt g 
		LEFT JOIN sw_pbb.cppmod_pbb_wajib_pajak w ON w.CPM_WP_ID=g.ID_WP
		WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK<='$thn'
		ORDER BY SPPT_TAHUN_PAJAK DESC
		LIMIT 0,1";
	// print_r($query);exit;
	$res = mysqli_query($DBLink, $q);
	$record = mysqli_num_rows($res);
	if ($record < 1) {
		echo "Data tidak ada!";
		exit;
	}
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json 		= new Services_JSON();
	$dataNotaris=  $json->decode(mysql2json($res, "data"));
	$data 		= $dataNotaris->data[0];
	$noKTP 		= ($data->ID_WP!=='' && $data->ID_WP!=null) ? $data->ID_WP : false;
	$NAMA 		= addslashes(trim($data->WP_NAMA));
	$ALAMAT 	= addslashes(trim($data->WP_ALAMAT));
	$RT 		= ($data->WP_RT!='' && (int)$data->WP_RT!=0) ?  sprintf("%03d", (int)$data->WP_RT) : '';
	$RW 		= ($data->WP_RW!='' && (int)$data->WP_RW!=0) ?  sprintf("%02d", (int)$data->WP_RW) : '';
	$KELURAHAN 	= addslashes($data->WP_KELURAHAN);
	$KECAMATAN 	= addslashes($data->WP_KECAMATAN);
	$KOTAKAB 	= addslashes($data->WP_KOTAKAB);
	$PROPINSI 	= addslashes($data->WP_PROPINSI);

	$wp = (object)[];
	$wp->noKTP 	= $noKTP;
	$wp->nama 	= $NAMA;
	$wp->alamat = $ALAMAT;
	$wp->rt 	= $RT;
	$wp->rw 	= $RW;
	$wp->kel 	= $KELURAHAN;
	$wp->kec 	= $KECAMATAN;
	$wp->kota 	= $KOTAKAB;
	$wp->prov 	= $PROPINSI;

	$thn = date('Y');

	$addWherektp = ($noKTP) ? "AND ID_WP='$noKTP'" : '';

	$q="SELECT 
			NOP, 
			WP_NAMA AS NAMA,
			SPPT_TAHUN_PAJAK AS TAHUN, 
			SPPT_PBB_HARUS_DIBAYAR AS TAGIHAN, 
			SPPT_TANGGAL_JATUH_TEMPO AS TEMPO,
			OP_ALAMAT AS ALAMAT,
			OP_RT AS RT,
			OP_RW AS RW,
			OP_KELURAHAN_KODE AS KELURAHAN,
			OP_KECAMATAN_KODE AS KECAMATAN,
			OP_KOTAKAB_KODE AS KOTAKAB
		FROM gw_pbb.pbb_sppt
		WHERE NOP='$nop' 
		AND SPPT_TAHUN_PAJAK<='$thn' $addWherektp 
		AND (PAYMENT_FLAG='0' OR PAYMENT_FLAG IS NULL)
		ORDER BY SPPT_TAHUN_PAJAK DESC
		LIMIT 0,100";

	$res = mysqli_query($DBLink, $q);
	$dataNotaris =  $json->decode(mysql2json($res, "data"));
	$data = $dataNotaris->data;
	if(count($data)==0){
		echo "Data tidak ada!";
		exit;
	}

	$obj = (object)[];
	$obj->wp = $wp;
	$obj->tunggakan = $data;

	// echo '<pre>';
	// print_r($obj);exit;
	return $obj;
}

function headerSK()
{
	global $appID;
	return $strHeader = "
		<div align=\"center\"><br><br><table><tr><td width=\"8%\"></td><td width=\"92%\">" . getConfigValue($appID, 'C_HEADER_SK') . "</td></tr></table></div>
	";
}

function getHTML($nop,$thn)
{
	global $uname, $appID, $noSK, $DBLink;
	$dbUtils 			= new DbUtils($DBLink);
	$namaPejabatSK		= getConfigValue($appID, 'NAMA_PEJABAT_SK2');
	$jabatanPejabatSK	= getConfigValue($appID, 'PEJABAT_SK2');
	$NIPPejabatSK		= getConfigValue($appID, 'NAMA_PEJABAT_SK2_NIP');
	$SKNumber 			= sprintf("%04d", getNoUrut());

	$d	= getData($nop,$thn);

	$nama = strtoupper($d->wp->nama);
	$alamat = '';
	$alamat .= ($d->wp->alamat!='') ? strtoupper($d->wp->alamat) : '';
	$alamat .= ($d->wp->rt!='') ? ' RT: '. $d->wp->rt : '';
	$alamat .= ($d->wp->rw!='') ? ' RT: '.$d->wp->rw : '';
	$alamat .= ($d->wp->kel!='') ? ' '.strtoupper($d->wp->kel) : '';
	$alamat .= ($d->wp->kec!='') ? ', '.strtoupper($d->wp->kec) : '';
	$alamat .= ($d->wp->kota!='') ? ', '.strtoupper($d->wp->kota) : '';
	$alamat .= ($d->wp->prov!='') ? ', '.strtoupper($d->wp->prov) : '';

	$t = $d->tunggakan;
	$tempo = $t[0]->TEMPO ;

	$tbltunggakan = '';
	$n = 1;
	$sumpokok = 0;
	$sumdenda = 0;
	$sumtotal = 0;
	foreach ($t as $r) {
		$denda = $dbUtils->getDenda(date('Y-m-d', strtotime($r->TEMPO)), $r->TAGIHAN, 0, 24, 1);
		$tbltunggakan.='<tr>
							<td align="center">
								<table border="1"><tr><td>'.$n.'</td></tr></table>
							</td>
							<td align="center">
								<table border="1"><tr><td>'.$r->TAHUN.'</td></tr></table>
							</td>
							<td align="right">
								<table border="1"><tr><td>'.number_format($r->TAGIHAN, 2, ',','.').' &nbsp; </td></tr></table>
							</td>
							<td align="right">
							<table border="1"><tr><td>'.number_format($denda, 2, ',','.').' &nbsp; </td></tr></table>
							</td>
							<td align="right">
								<table border="1"><tr><td>'.number_format($r->TAGIHAN+$denda, 2, ',','.').' &nbsp; </td></tr></table>
							</td>
						</tr>';
		$n++;
		$sumpokok += $r->TAGIHAN;
		$sumdenda += $denda;
		$sumtotal += ($r->TAGIHAN+$denda);
	}

	$terbilang = strtoupper(SayInIndonesian($sumtotal));

	$tbltotal = '<tr>
					<td colspan="2" align="center">
						<table border="1" cellpadding="3"><tr><td>TOTAL</td></tr></table>
					</td>
					<td align="right">
						<table border="1" cellpadding="3"><tr><td>'.number_format($sumpokok, 2, ',','.').'</td></tr></table>
					</td>
					<td align="right">
						<table border="1" cellpadding="3"><tr><td>'.number_format($sumdenda, 2, ',','.').'</td></tr></table>
					</td>
					<td align="right">
						<table border="1" cellpadding="3"><tr><td>'.number_format($sumtotal, 2, ',','.').'</td></tr></table>
					</td>
				</tr>';

	$html = "
	<html>
	<body>
	<table border=\"0\" width=\"650\" cellpadding=\"4\" cellspacing=\"4\">
		<tr>
			<td colspan=\"3\"><br><br><br><br><hr style=\"height: 2px\"></td>
		</tr>
		<tr>
			<td colspan=\"3\" height=\"200\">
				<table border=\"0\" cellspacing=\"4\"> 
					<tr>
						<td align=\"center\">
							<font size=\"14\">S T P D</font><br>
							(Surat Tagihan Pajak Daerah)<br>
							<table border=\"0\">
								<tr>
									<td width=\"25%\"> </td>
									<td width=\"50%\"><table border=\"1\" cellspacing=\"4\" width=\"300\"><tr><td>NO SPTPD : 973/" . $SKNumber . "/V.04/STPD-PBB/".date('Y')."</td></tr></table></td>
									<td width=\"325%\"> </td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=\"center\"> </td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"1400\">
								<tr>
									<td width=\"140\"> Nama</td><td width=\"8\">:</td><td align=\"left\">" . $nama . "</td>
								</tr>
								<tr>
									<td> Alamat</td><td>:</td><td align=\"left\">" .$alamat. "</td>
								</tr>
								<tr>
									<td> Tanggal Jatuh Tempo</td><td>:</td><td align=\"left\">" .TanggalIndo($tempo). "</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr><td></td></tr>
					<tr>
						<td>
							<br>
							<table border=\"0\" width=\"1220\">
								<tr>
									<td width=\"15\">I.</td>
									<td align=\"justify\">Berdasarkan Ps. 14 Peraturan Daerah Kabupaten Lampung Selatan Nomor 3 Tahun 2011 telah dilakukan penelitian dan/atau pemeriksaan dan/atau keterangan lain atas pelaksanaan kewajiban Pajak Bumi dan Bangunan berikut :
										<br>
										<br>
										<table border=\"0\" width=\"500\">
											<tr>
												<td width=\"126\">Nama</td><td width=\"8\">:</td><td width=\"480\">" . $nama . "</td>
											</tr>
											<tr>
												<td>NOP</td><td>:</td><td width=\"480\">" . $t[0]->NOP . "</td>
											</tr>
											<tr>
												<td>Alamat Objek Pajak</td><td>:</td><td align=\"justify\">" . strtoupper($t[0]->ALAMAT) . " " . ($t[0]->RT != '' && (int)$t[0]->RT > 0 ? 'RT: ' . sprintf("%03d", (int)$t[0]->RT) : '') . " " . ($t[0]->RW != '' && (int)$t[0]->RW > 0 ? 'RW: ' . sprintf("%02d", (int)$t[0]->RW) : '') . "<br>" . ($t[0]->KELURAHAN != '' ? 'DESA ' . getKelurahanNama($t[0]->KELURAHAN) : '') . " " . ($t[0]->KECAMATAN != '' ? 'KEC. ' . getKecamatanNama($t[0]->KECAMATAN) : '') . ", " . ($t[0]->KOTAKAB != '' ? getKabkotaNama($t[0]->KOTAKAB) : '') . "</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>II.</td>
									<td align=\"justify\">Dari penelitian dan/atau pemeriksaan tersebut diatas, perhitungan jumlah PBB yang masih harus dibayar adalah:
										<br>
										<br>
										<table border=\"1\" width=\"678\">
											<tr>
												<th width=\"5%\" align=\"center\">
													<table border=\"1\" cellpadding=\"4\"><tr><td>NO</td></tr></table>
												</th>
												<th width=\"15%\" align=\"center\">
													<table border=\"1\" cellpadding=\"4\"><tr><td>TAHUN PAJAK</td></tr></table>
												</th>
												<th width=\"25%\" align=\"center\">
													<table border=\"1\" cellpadding=\"4\"><tr><td>POKOK PAJAK (Rp)</td></tr></table>
												</th>
												<th width=\"20%\" align=\"center\">
													<table border=\"1\" cellpadding=\"4\"><tr><td>DENDA (Rp)</td></tr></table>
												</th>
												<th width=\"25%\" align=\"center\">
													<table border=\"1\" cellpadding=\"4\"><tr><td>JUMLAH (Rp)</td></tr></table>
												</th>
											</tr>
											$tbltunggakan
											$tbltotal
										</table>
										<br>
										Terbilang: $terbilang RUPIAH
										<br>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							PERHATIAN :
							<br>
							<table border=\"0\" width=\"1220\" cellspacing=\"4\">
								<tr>
									<td width=\"15\">1.</td>
									<td align=\"justify\">Sanksi Administratif berupa Denda dihitung 2% per bulan sejak Tanggal Jatuh tempo.</td>
								</tr>
								<tr>
									<td>2.</td>
									<td align=\"justify\">Pembayaran Tagihan PBB dilakukan melalui Kas Daerah (Bank Lampung), Nomor Rekening : 383.00.09.000039 atas nama Rekening Kas Umum Daerah Kabupaten Lampung Selatan dengan memberikan Keterangan NOP PBB dan Tahun Pajak.</td>
								</tr>
								<tr>
									<td>3.</td>
									<td align=\"justify\">Silahkan abaikan Surat ini apabila Wajib Pajak telah membayar PBB Tahun Pajak tersebut diatas dan mohon agar Bukti Lunas pembayaran PBB disampaikan kepada Sub Bidang PBB-P2 Badan Pengelola Pajak dan Retribusi Daerah Kabupaten Lampung Selatan.</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<br><br>	
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"center\">Kalianda, " . TanggalIndo(date("Y-m-d")) . "</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" width=\"550\" cellspacing=\"0\">
								<tr>
									<td width=\"200\"></td>
									<td width=\"150\"></td>
									<td width=\"250\" align=\"center\">
										KEPALA BADAN PENGELOLA PAJAK
										<br>DAN RETRIBUSI DAERAH
										<br>KABUPATEN PESAWARAN
										<br>
										<br>
										<br>
										<br>
										<br>
										<br>
										<u>" . $namaPejabatSK. "</u>
										<br>NIP. ".$NIPPejabatSK."
									</td>
								</tr>
							</table>
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


function TanggalIndo($date)
{
	$BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

	$tahun = substr($date, 0, 4);
	$bulan = substr($date, 5, 2);
	$tgl   = substr($date, 8, 2);

	$result = $tgl . " " . $BulanIndo[(int)$bulan - 1] . " " . $tahun;
	return ($result);
}