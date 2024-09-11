<?php
require_once("tcpdf/tcpdf.php");
include_once("inc-config.php");

$nop 	= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$idwp 	= @isset($_REQUEST['idwp']) ? $_REQUEST['idwp'] : "";
$thn1 	= @isset($_REQUEST['thn1']) ? $_REQUEST['thn1'] : "";
$thn2 	= @isset($_REQUEST['thn2']) ? $_REQUEST['thn2'] : "";
$dt 	= GetListByNOP($nop,$idwp,$thn1,$thn2);
// echo "<pre>";
// print_r($dt); exit;
$c  = count($dt);
if($c>0){
	$sum_harus_dibayar 	= 0;
	$sum_denda			= 0;
	$isiTable = "";
	$i		  = 1;
	foreach($dt as $dt){
		if($nop==""){
			$dtNOP = substr($dt['NOP'],0,2).'.'.substr($dt['NOP'],2,2).'.'.substr($dt['NOP'],4,3).'.'.substr($dt['NOP'],7,3).'.'.substr($dt['NOP'],10,3).'-'.substr($dt['NOP'],13,4).'.'.substr($dt['NOP'],17,1);
		} else {
			$dtNOP = $dt['WP_NAMA'];
		}
		$isiTable .= "
			<tr>
				<td>".$dtNOP."</td>
				<td align=\"center\">".$dt['SPPT_TAHUN_PAJAK']."</td>
				<td align=\"right\">Rp ".$dt['SPPT_PBB_HARUS_DIBAYAR']."</td>
				<td align=\"right\">Rp ".$dt['DENDA']."</td>
				<td align=\"right\">".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],8,2)."/".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],5,2)."/".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],0,4)."</td>
				<td align=\"right\">Rp ".$dt['DENDA_PLUS_PBB']."</td>
				<td>".$dt['STATUS']."</td>
			</tr>";
		// $sum_harus_dibayar += ($dt['DENDA_PLUS_PBB']); 
		// $sum_denda 		   += ($dt['DENDA']); 
		$i++;
	}
	
	if($nop==""){
		$fdNOP = "NOP";
	} else {
		$fdNOP = "NAMA <br>WAJIB PAJAK";
	}
	
	$bulan = array(
		"01" => "Januari",
		"02" => "Februari",
		"03" => "Maret",
		"04" => "April",
		"05" => "Mei",
		"06" => "Juni",
		"07" => "Juli",
		"08" => "Agustus",
		"09" => "September",
		"10" => "Oktober",
		"11" => "November",
		"12" => "Desember"
	);
	
	$html = "
	<html>
		<table width=\"100%\" border=\"0\">
			<!--<tr>
				<td colspan=\"3\" align=\"center\">
					".getConfig('C_HEADER_DISPOSISI')."<br>
					".getConfig('C_ALAMAT_DISPOSISI')."<br>
					<hr>
				</td>
			</tr>-->

			<tr>
				<td colspan=\"3\" align=\"center\"><br><br><br><br><br><br><br><b>INFORMASI DATA PEMBAYARAN</b></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<table cellpadding=\"1\">
						<tr><td width=\"20%\">Nomor Objek Pajak</td><td width=\"3%\">:</td><td width=\"27%\">".substr($dt['NOP'],0,2).'.'.substr($dt['NOP'],2,2).'.'.substr($dt['NOP'],4,3).'.'.substr($dt['NOP'],7,3).'.'.substr($dt['NOP'],10,3).'-'.substr($dt['NOP'],13,4).'.'.substr($dt['NOP'],17,1)."</td><td width=\"20%\">Tahun Ketetapan</td><td width=\"3%\">:</td><td width=\"27%\">".$dt['SPPT_TAHUN_PAJAK']."</td></tr>
						<tr><td width=\"20%\">Luas Bumi</td><td width=\"3%\">:</td><td width=\"27%\">".$dt['OP_LUAS_BUMI']." m2</td><td width=\"20%\">NJOP Bumi</td><td width=\"3%\">:</td><td width=\"27%\">".number_format($dt['OP_NJOP_BUMI']/$dt['OP_LUAS_BUMI'], 2, ",", ".")."/m2</td></tr>
						<tr><td width=\"20%\">Luas Bangunan</td><td width=\"3%\">:</td><td width=\"27%\">".$dt['OP_LUAS_BANGUNAN']." m2</td><td width=\"20%\">NJOP Bangunan</td><td width=\"3%\">:</td><td width=\"27%\">".number_format($dt['OP_NJOP_BANGUNAN']/$dt['OP_LUAS_BANGUNAN'], 2, ",", ".")."/m2</td></tr>
						<tr><td width=\"20%\">Kecamatan Objek Pajak</td><td width=\"3%\">:</td><td width=\"27%\">".$dt['OP_KECAMATAN']."</td><td width=\"20%\">Kelurahan Objek Pajak</td><td width=\"3%\">:</td><td width=\"27%\">".$dt['OP_KELURAHAN']."</td></tr>
						<tr><td width=\"20%\">Alamat Objek Pajak</td><td width=\"3%\">:</td><td width=\"77%\">".$dt['OP_ALAMAT']."</td></tr>
						<tr><td width=\"20%\">Nama Wajib Pajak</td><td width=\"3%\">:</td><td width=\"77%\">".$dt['WP_NAMA']."</td></tr>
						<tr><td width=\"20%\">Alamat Wajib Pajak</td><td width=\"3%\">:</td><td width=\"77%\">".$dt['WP_ALAMAT']."</td></tr>
						<tr><td width=\"20%\">Tanggal <i>Printout</i></td><td width=\"3%\">:</td><td width=\"77%\">".date("d/m/Y")."</td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<table border=\"1\" width=\"100%\" cellpadding=\"1\">
					<tr>
						<td align=\"center\" width=\"19%\">".$fdNOP."</td>
						<td align=\"center\" width=\"7%\">TAHUN PAJAK</td>
						<td align=\"center\" width=\"14%\">PBB</td>
						<td align=\"center\" width=\"14%\">DENDA (*)</td>
						<td align=\"center\" width=\"10%\">JATUH<Br>TEMPO</td>
						<td align=\"center\" width=\"13%\">KURANG <br>BAYAR</td>
						<td align=\"center\" width=\"23%\">STATUS <br>BAYAR</td>
					</tr>
					".$isiTable."
					<!-- <tr>
						<td align=\"center\" colspan=\"5\">TOTAL</td>
						<td align=\"right\">Rp ".$sum_harus_dibayar."</td>
						<td align=\"center\"></td>
					</tr>-->
				</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<table border=\"0\" width=\"100%\" cellpadding=\"1\">
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">TOTAL PBB YANG BELUM DIBAYAR</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp ".$dt['SUM_TOTAL']."</td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">TOTAL DENDA (SESUAI TANGGAL <i>PRINTOUT</i>)</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp ".$dt['SUM_DENDA']."</td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">JUMLAH YANG HARUS DIBAYAR</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp ".$dt['SUM_TOTAL_DENDA_PDF']."</td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td colspan=\"3\">*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</td>
			</tr>
			
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td>Petugas : .......................................................</td>
				<td></td>
				<td align=\"center\">
					".getConfig('NAMA_KOTA_PENGESAHAN').", ".date('d')." ".$bulan[date('m')]." ".date('Y')."
				</td>
			</tr>
			<tr>
				<td colspan=\"3\">Keperluan : .......................................................</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align=\"center\">............................................................</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align=\"center\">............................................................</td>
			</tr>
		</table>
	</html>";
} else {
	$html = 'Data tidak tersedia';
}

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('pbb');
$pdf->SetSubject('pbb');
$pdf->SetKeywords('pbb');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 14, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('PL', 'A4');
$pdf->Image('logo.png', 95, 10, 20, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output('Data Tagihan PBB.pdf', 'I');

?>