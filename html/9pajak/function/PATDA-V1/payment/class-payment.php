<?php
class Payment extends Pajak{
    /*
     * 	RC 00 = SUKSES, PEMBAYARAN BERHASIL
		RC 14 = GAGAL, TAGIHAN TIDAK DITEMUKAN
		RC 88 = GAGAL, TAGIGAN SUDAH LUNAS
		RC 89 = GAGAL, TOTAL BAYAR TIDAK SESUAI DENGAN TAGIHAN
		RC 04 = GAGAL, KODE BANK TIDAK DITEMUKAN
     * */
    
    public function ConnToGw($app){
		$config = $this->get_config_value($app);
		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbUser = $config['PATDA_USERNAME'];
		$Conn = mysqli_connect($dbHost, $dbUser, $dbPwd); // edited by v -> migrating to mysqli
		mysqli_select_db($Conn, $dbName); // edited by v -> migrating to mysqli
		return $Conn;
	}
	
    public function dataForm(){
		global $a;
		$config = $this->get_config_value($a);
		$list_area_code = array($config['KODE_AREA'] => $config['PEMERINTAHAN_NAMA'].' - '.$config['KODE_AREA']);
		
		$data = array();
		$this->set_jenis_pajak();
		$data['list_simpatda_type'] = $this->arr_pajak_gw;
		$data['list_simpatda_bulan_pajak'] = $this->arr_bulan;
		$data['list_area_code'] = $list_area_code;
		
		$data['npwpd'] = '';#'P2010100766';
		$data['payment_code'] = '';#160900002928;
		$data['simpatda_tahun_pajak'] = '';#2016;
		$data['simpatda_bulan_pajak'] = '';#0;
		$data['simpatda_type'] = '';#30;
		
		return $data;
	}
	
	public function inquiry(){
		global $json;
		
		$data = (object) $_POST;
		$Conn = $this->ConnToGw($data->app);
		
		$query = sprintf("SELECT * FROM SIMPATDA_GW 
			WHERE payment_code = '%s' OR payment_code=REPLACE('%s','-','')",
			$data->payment_code, $data->payment_code
		);
		
		$res = mysqli_query($Conn,$query) or die(mysqli_error($Conn)); // edited by v -> migrating to mysqli
		$row = mysqli_num_rows($res); /* 20170216 RDN : cek row query agar object $data tidak hilang karena fetch_object return null*/ // edited by v -> migrating to mysqli
		if($row>0){
			if($data = mysqli_fetch_object($res)){ // edited by v -> migrating to mysqli
				$todays = date('Y-m-d');
				$expireds = $data->expired_date;
				if($data->payment_flag == 1){/*jika tagihan sudah dibayar*/
					$data->RC = '88';
					$data->MSG = 'Tagihan Sudah Diverifikasi.';
				}elseif($todays > $expireds){
					$data->RC = '88';
					$data->MSG = 'Kode Pembayaran Sudah Expired.';
				}
				else{/*jika belum dibayar*/
					$today = strtotime(date("Y-m-d"));
					$expired_date = strtotime($data->expired_date);
					
					$timeDiff = abs($expired_date - $today);
					$numberDays = $timeDiff/86400;  // 86400 seconds in one day
					
					// and you might want to convert to integer
					$numberDays = intval($numberDays);
					$banyak_bulan = floor($numberDays/30);
					
					// tambahan
					// die(var_dump($data));
					$data->patda_denda = $data->simpatda_denda;
					// $data->patda_denda = 0;
					// tidak dipakai karena denda hanya menghitung dari pelaporan saja, tidak ada denda telat bayar
					// seandai nya memang ada, harus dihitung dari total yang sudah ditambah denda lapor
					// if($today >= $expired_date){
					// 	//denda 2% perbulan (telat bayar/bayar diatas tanggal kadaluarsa)
					// 	$
					// jadi kalo ada tulisan denda di pembayaran, artinya denda telat lapor
					
					$data->npwpd = Pajak::formatNPWPD($data->npwpd);
					$data->patda_total_bayar = $data->simpatda_dibayar + $data->patda_denda;
					$data->RC = '00';
					$data->MSG = 'Inquiry Berhasil.';
					
					$data->html = '<table border="0" width="100%" align="center" class="child" style="padding:10px;background:#DDD">
						<tr>
							<td width="25%" style="background:#DDD">NPWPD</td>
							<td width="75%" style="background:#DDD"> : '.$data->npwpd.'</td>
						</tr>
						<tr>
							<td width="25%" style="background:#DDD">Nama WP</td>
							<td width="75%" style="background:#DDD"> : '.$data->wp_nama.'</td>
						</tr>
						<tr>
							<td width="25%" style="background:#DDD">No. Dok</td>
							<td width="75%" style="background:#DDD"> : '.$data->sptpd.'</td>
						</tr>
						<tr>
							<td width="25%" style="background:#DDD">Tahun Pajak</td>
							<td width="75%" style="background:#DDD"> : '.$data->simpatda_tahun_pajak.'</td>
						</tr>
						<tr>
							<td width="25%" style="background:#DDD">Masa Pajak</td>
							<td width="75%" style="background:#DDD"> : '.($data->masa_pajak_awal == '' || $data->masa_pajak_awal == '0000-00-00'? $data->simpatda_bulan_pajak : $data->masa_pajak_awal.' s.d '.$data->masa_pajak_akhir).'</td>
						</tr>
					</table>';
				}
			}
		}
		else {
			$data->RC = '04'; /*default tagihan tidak ditemukan */
			$data->MSG = 'Data Tagihan Tidak Ditemukan.';
		}
		
		return $json->encode($data);
	}
	
	public function bayar(){
		global $json;
		$data = (object) $_POST;
		
		$inquiryRes = $this->inquiry();
		$inq = $json->decode($inquiryRes);
		
		$data = (object) array_merge((array) $data, (array) $inq);
		
		$data->payment_paid = date('Y-m-d H:i:s');
		$data->PAYMENT_SETTLEMENT_DATE = date('Ymd');

		// tambahan karena tidak ada telat bayar.. setiap pelaporan pasti dibayar dihari yang sama
		$data->patda_denda = 0;
		
		if($data->RC == '00'){/*jika hasil inquiry berhasil*/
			
			$config = $this->get_config_value($data->app);
			$dbName = $config['PATDA_DBNAME'];
			$dbHost = $config['PATDA_HOSTPORT'];
			$dbPwd = $config['PATDA_PASSWORD'];
			$dbUser = $config['PATDA_USERNAME'];
			$settlmentDate = $config['PATDA_SETTLEMENT_DATE'];
			$Conn = mysqli_connect($dbHost, $dbUser, $dbPwd); // edited by v -> migrating to mysqli
			mysqli_select_db($Conn,$dbName); // edited by v -> migrating to mysqli
			
			$querySettlement = "SELECT ('{$data->payment_paid}' > '".date('Y-m-d')." {$settlmentDate}:00') AS LEBIH";
			$res = mysqli_query($Conn,$querySettlement) or die(mysqli_error($Conn)); // edited by v -> migrating to mysqli
			
			if($dataSettlement = mysqli_fetch_object($res)){ // edited by v -> migrating to mysqli
				/*jika lebih settlementdate*/
				if($dataSettlement->LEBIH == 1){
					$tomorrow  = date('Ymd', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
					$data->PAYMENT_SETTLEMENT_DATE = $tomorrow;
				}
			}
			
			$query = sprintf("
				UPDATE SIMPATDA_GW SET
					patda_collectible = '%s',
					patda_denda = '%s',
					patda_total_bayar = '%s',
					payment_flag = '%s',
					payment_paid = '%s',
					payment_bank_code = '%s',
					operator = '%s',
					PAYMENT_SETTLEMENT_DATE = '%s'
				WHERE id_switching = '%s'",
				
				$data->simpatda_dibayar,
				$data->patda_denda,
				$data->patda_total_bayar,
				1 /*flag*/,
				$data->payment_paid,
				'0000034'/*bankcode*/,
				$data->uid /*operator*/,
				$data->PAYMENT_SETTLEMENT_DATE,
				$inq->id_switching
			);
			
			if(mysqli_query($Conn, $query)){ // edited by v -> migrating to mysqli
				$data->RC = '00';
				$data->MSG = 'Verifikasi Berhasil.';
			}
		}
		return $json->encode($data);
	}

	public function printKwitansi(){
		global $DIR, $sRootPath, $json, $a;
		
		$data = (object) $_POST;
		$app = $data->app;
		$Conn = $this->ConnToGw($app);
		
		$inquiryRes = $this->inquiry();
		
		$data = $json->decode($inquiryRes);
		
		if($data->RC != '88'){
			exit("<h1 style='width:500px;height:200px;position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;'>Maaf, Tagihan Belum Dibayar.</h1>");
		}
		
		$jenis = strtolower($this->arr_pajak_gw_table[$data->simpatda_type]);
		require_once($sRootPath . "function/{$DIR}/{$jenis}/op/class-op.php");
		require_once($sRootPath . "function/{$DIR}/{$jenis}/lapor/class-lapor.php");
		
		//echo '<pre>',print_r($data,true),'</pre>';exit;
		
		//init
		// $_POST['PAJAK']['CPM_ID'] = $data->id_switching;
		$_POST['id_switching'] = $data->id_switching;
		$_REQUEST['a'] = $app;
		
		$lapor = new LaporPajak();
		// $lapor->print_sspd();
		$this->print_stts();
	}
	
	public function print_stts(){
		$data = (object) $_POST;
		$app = $data->app;
		$Conn = $this->ConnToGw($app);
		$list_bank = $this->get_bank_payment();
		
		
		$config = $this->get_config_value($app);		
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
		$NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KODE_AREA = $config['KODE_AREA'];
		
		$BANK = $config['BANK'];
		$BANK_ALAMAT = $config['BANK_ALAMAT'];
		$BANK_NOREK = $config['BANK_NOREK'];
		
		$BENDAHARA_NAMA = $config['BENDAHARA_NAMA'];
		$BENDAHARA_NIP  = $config['BENDAHARA_NIP'];
		
		$sql = "select * from SIMPATDA_GW where id_switching='{$data->id_switching}'";
		$qry = mysqli_query($Conn,$sql); // edited by v -> migrating to mysqli
		$payment = mysqli_fetch_object($qry); // edited by v -> migrating to mysqli
		$bank = (!empty($list_bank[$payment->payment_bank_code]->CDC_B_NAME)) ? $list_bank[$payment->payment_bank_code]->CDC_B_NAME : 'BENDAHARA PENERIMA BAPENDA';
		// print_r($payment); exit;
				  
		$html = "
		<table width=\"97%\" border=\"0\">
			<tr>
				<td align=\"center\" style=\"border-bottom: 1px solid #000;border-right: 1px solid #000;\" colspan=\"2\">
				{$JENIS_PEMERINTAHAN}<br>
				{$NAMA_PEMERINTAHAN}<br>
				<b>{$NAMA_PENGELOLA}</b><br>
				{$JALAN}<br>
				{$KOTA} - {$PROVINSI} {$KODE_POS}
				</td>
				<td align=\"center\" style=\"border-bottom: 1px solid #000;\" >
					<b>STTS</b><br>
					SURAT TANDA TERIMA SETORAN PAJAK DAERAH
				</td>
			</tr>
			<tr>
				<td colspan=\"3\">&nbsp;</td>
			</tr>
			<tr>
				<td width=\"45%\">NPWPD</td>
				<td width=\"10%\" align=\"center\">:</td>
				<td width=\"40%\">".Pajak::formatNPWPD($payment->npwpd)."</td>
			</tr>
			<tr>
				<td>NOP</td>
				<td align=\"center\">:</td>
				<td>{$payment->op_nomor}</td>
			</tr>
			<tr>
				<td>TAHUN PAJAK</td>
				<td align=\"center\">:</td>
				<td>{$payment->simpatda_tahun_pajak}</td>
			</tr>
			<tr>
				<td>NAMA WP</td>
				<td align=\"center\">:</td>
				<td>{$payment->wp_nama}</td>
			</tr>
			<tr>
				<td>TANGGAL JATUH TEMPO</td>
				<td align=\"center\">:</td>
				<td>".date("d/m/Y",strtotime($payment->expired_date))."</td>
			</tr>
			<tr>
				<td>TANGGAL VERIFIKASI</td>
				<td align=\"center\">:</td>
				<td>".date("d/m/Y",strtotime($payment->payment_paid))."</td>
			</tr>
			<tr>
				<td>POKOK SPTPD</td>
				<td align=\"center\">:</td>
				<td>
					<table border=\"0\" width=\"100%\">
						<tr>
							<td width=\"30%\">Rp.</td>
							<td width=\"70%\" align=\"right\">".number_format($payment->simpatda_dibayar,2,'.',',')."</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>DENDA</td>
				<td align=\"center\">:</td>
				<td>
					<table border=\"0\" width=\"100%\">
						<tr>
							<td width=\"30%\">Rp.</td>
							<td width=\"70%\" align=\"right\">".number_format(($payment->patda_denda+$payment->simpatda_denda),2,'.',',')."</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>TOTAL PEMBAYARAN</td>
				<td align=\"center\">:</td>
				<td>
					<table border=\"0\" width=\"100%\">
						<tr>
							<td width=\"30%\">Rp.</td>
							<td width=\"70%\" align=\"right\">".number_format($payment->patda_total_bayar,2,'.',',')."</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\" style=\"border-bottom: 1px solid #000;\" width=\"100%\"></td>
			</tr>			
			<tr>
				<td colspan=\"3\" width=\"100%\"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=\"center\">&nbsp;</td>
				<td align=\"center\">
				PETUGAS
				<br>
				<br>
				<br>				
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=\"center\">&nbsp;</td>
				<td align=\"center\" style=\"border-bottom: 1px solid #000;\">{$BENDAHARA_NAMA}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=\"center\">&nbsp;</td>
				<td align=\"center\">NIP : {$BENDAHARA_NIP}</td>
			</tr>
		</table>";
		
		ob_clean();
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpos');
        $pdf->SetTitle('Kwitansi Pembayaran Pajak Daerah');
        $pdf->SetSubject('kwitansi');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);        
		$pdf->AddPage('L', 'A5');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Output('Kwitansi Pembayaran Patda.pdf', 'I');
	}

	public function printKwitansi_(){
		global $json;
		$data = (object) $_POST;
		$Conn = $this->ConnToGw($data->app);
		
		$inquiryRes = $this->inquiry();
		$data = $json->decode($inquiryRes);
		
		if($data->RC != '88'){
			exit("<h1 style='width:500px;height:200px;position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;'>Maaf, Tagihan Belum Dibayar.</h1>");
		}
		$html = "<table width=\"400\" border=\"0\">
			<tr>
				<td align=\"right\">
					{$data->payment_code}<br/>
					{$data->simpatda_tahun_pajak}
				</td>
			</tr>
			<tr>
				<td>
					<table width=\"400\" border=\"0\" >
						<tr>
							<td></td>
							<td>".Pajak::formatNPWPD($data->npwpd)."</td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td>Pajak ".$this->arr_pajak_gw[$this->arr_pajak_gw_no[$data->simpatda_type]]."</td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td>{$data->wp_alamat}</td>
							<td></td>
						</tr>
					</table>
					<br/>
				</td>
			</tr>
			<tr>
				<td>
					<table width=\"300\" border=\"0\">
						<tr>
							<td width=\"30\">1.</td>
							<td width=\"270\">Biaya Tagihan</td>
							<td align=\"right\">".number_format($data->simpatda_dibayar,0)."</td>
						</tr>
						<tr>
							<td>2.</td>
							<td>Biaya Lain</td>
							<td align=\"right\">".number_format($data->patda_misc_fee,0)."</td>
						</tr>
						<tr>
							<td>3.</td>
							<td>Biaya Admin</td>
							<td align=\"right\">".number_format($data->patda_admin_gw,0)."</td>
						</tr>
						<tr>
							<td>4.</td>
							<td>Denda</td>
							<td align=\"right\">".number_format($data->patda_denda,0)."</td>
						</tr>
						<tr>
							<td>5.</td>
							<td>Total Tagihan</td>
							<td align=\"right\">".number_format($data->patda_total_bayar,0)."</td>
						</tr>
						<tr>
							<td>6.</td>
							<td>Pembayaran Pajak</td>
							<td>{$data->op_nama}</td>
						</tr>
					</table>
					<br/><br/>
				</td>
			</tr>
			<tr>
				<td>
					<table width=\"100%\">
						<tr><td align=\"left\">Rp. ".number_format($data->patda_total_bayar,0)."</td></tr>
						<tr><td>".ucwords($this->SayInIndonesian($data->patda_total_bayar))."</td></tr>
						<tr><td>".$data->payment_paid."</td></tr>
					</table>
				</td>
			</tr>
		</table>";
		
		ob_clean();
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpos');
        $pdf->SetTitle('Kwitansi Pembayaran Pajak Daerah');
        $pdf->SetSubject('kwitansi');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Output('Kwitansi Pembayaran Patda.pdf', 'I');

	}
	
	public function get_list_npwpd(){
		global $json;
		$data = (object) $_POST;
		
		$Conn = $this->ConnToGw($data->app);
		$NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['npwpd']);
		
		$query = sprintf("select npwpd, wp_nama FROM SIMPATDA_GW WHERE npwpd LIKE '%s' GROUP BY npwpd LIMIT 0,10", $NPWPD.'%');
		$res = mysqli_query($Conn, $query); // edited by v -> migrating to mysqli
		
		$list = array();
		while($row = mysqli_fetch_object($res)){ // edited by v -> migrating to mysqli
			$list['items'][] = array('id'=> $row->npwpd,'text'=>$row->wp_nama);
		}
		if(count($list) == 0){$list['items'][] = array('id'=>' ','text'=>'NPWPD tidak ditemukan');}
		echo $json->encode($list);
	}
	
	public function get_list_payment_code(){
		global $json;
		$data = (object) $_POST;
		
		$Conn = $this->ConnToGw($data->app);
		$NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['npwpd']);
		$PERIODE = $_REQUEST['payment_code'];
		$SIMPATDA_TYPE = $_REQUEST['simpatda_type'];
		
		$query = sprintf("select payment_code FROM SIMPATDA_GW WHERE payment_code like '%s' and npwpd = '%s' and simpatda_type = '%s' LIMIT 0,10", $PERIODE.'%',$NPWPD,$SIMPATDA_TYPE);
		$res = mysqli_query($Conn, $query); // edited by v -> migrating to mysqli
		
		$list = array();
		while($row = mysqli_fetch_object($res)){ // edited by v -> migrating to mysqli
			$list['items'][] = array('id'=> $row->payment_code);
		}
		if(count($list) == 0){$list['items'][] = array('id'=>' ','text'=>'Kode Bayar tidak ditemukan');}
		echo $json->encode($list);
	}
}

?>
