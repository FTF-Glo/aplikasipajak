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
		$Conn = mysqli_connect($dbHost, $dbUser, $dbPwd);// edited by v -> migrating to mysqli
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
		$data['periode'] = '';#160900002928;
		$data['simpatda_tahun_pajak'] = '';#2016;
		$data['simpatda_bulan_pajak'] = '';#0;
		$data['simpatda_type'] = '';#30;
		
		return $data;
	}
	
	public function inquiry(){
		global $json;
		
		$data = (object) $_POST;
		$Conn = $this->ConnToGw($data->app);
		$tanggal_bayar = $data->tanggal;
		$query = sprintf("SELECT * FROM SIMPATDA_GW 
			WHERE payment_code = '%s' OR payment_code=REPLACE('%s','-','')",
			$data->payment_code, $data->payment_code
		);
		// var_dump($data);die;
		$res = mysqli_query($Conn, $query) or die(mysqli_error($Conn));
		$row = mysqli_num_rows($res); /* 20170216 RDN : cek row query agar object $data tidak hilang karena fetch_object return null*/ // edited by v -> migrating to mysqli

		if($row>0){
			if($data = mysqli_fetch_object($res)){				
				if($data->payment_flag == 1){/*jika tagihan sudah dibayar*/
					$data->RC = '88';
					$data->MSG = 'Tagihan Sudah Dibayar.';
				}else{/*jika belum dibayar*/

					$query = sprintf("SELECT * FROM SIMPATDA_TYPE 
						WHERE id = '%s'",
						$data->simpatda_type
					);
					
					$res = mysqli_query($Conn, $query) or die(mysqli_error($Conn)); // edited by v -> migrating to mysqli
					$jns_pjk = mysqli_fetch_object($res); // edited by v -> migrating to mysqli

					$arr_idpajak = array(1 => "AIRBAWAHTANAH", 2 => "HIBURAN", 3 => "HOTEL", 4 => "MINERAL", 5 => "PARKIR",6 => "JALAN", 7 => "REKLAME", 8 => "RESTORAN", 9 => "WALET");

					$postTanggal = isset($_REQUEST['payment_paid'])? $_REQUEST['payment_paid'] : $_REQUEST['tanggal'];

					$today1 = strtotime(date($postTanggal));
					$expired_date1 = strtotime($data->expired_date);
					
					$today = date('n',$today1);
					$expired_date = date('n',$expired_date1);
					// $timeDiff = abs($expired_date - $today);
					// echo "string";
					// echo $today;exit();
					// $numberDays = $timeDiff/86400;  // 86400 seconds in one day
					
					// and you might want to convert to integer
					// $numberDays = intval($numberDays);

					// $query = "SELECT * FROM HOTEL_ATR_JENIS_KAMAR order by Jenis";
					// $res = mysql_query($query, $this->Conn);

					// echo number_format($data->simpatda_dibayar,2);exit();

					$jns = $arr_idpajak[$jns_pjk->id_sw]; //REMOVING SW_PATDA as database NAME
					$query = sprintf("SELECT * FROM PATDA_".$jns."_DOC 
						WHERE CPM_NO = '%s'",
						$data->sspd
					);
						
					$res = mysqli_query($Conn, $query) or die(mysqli_error($Conn));
					$tgl = mysqli_fetch_object($res);
					$tgl_lapor = strtotime($tgl->CPM_TGL_LAPOR);
					$tanggal_lapor = date('n',$tgl_lapor);

					$banyak_bulan = ($today - $expired_date) - ($tanggal_lapor - $expired_date);
					// echo $banyak_bulan;exit();

					if($jns_pjk->id_sw == 1){
						if($tanggal_lapor == $today){
							$data->patda_denda = $data->simpatda_denda;
						}else{
							$data->patda_denda = $data->simpatda_denda;
							//ini perhitungan denda
							//$data->patda_denda = ($banyak_bulan * 0.02 * $data->simpatda_dibayar) + $data->simpatda_denda;
							// echo "string";exit();

						}
					}else{

						if($data->simpatda_rek == "4.1.01.12.01"){
							$banyak_bulan = $banyak_bulan-1;
						}

						if($today >= $expired_date){
							//denda 2% perbulan
							$data->patda_denda = $data->simpatda_denda;
							//ini perhitungan denda
							//$data->patda_denda = ($banyak_bulan * 0.02 * $data->simpatda_dibayar) + $data->simpatda_denda;
						}
					}

					/* hitung denda */
					$persen_denda = $this->get_persen_denda($data->expired_date,$tanggal_bayar);
					$DENDA = ($persen_denda / 100) * $data->simpatda_dibayar;
					$TOTAL = $data->simpatda_dibayar + $DENDA;
					/* end hitung denda */
				// var_dump($tanggal_bayar);die;
					
					//tambahan
					$data->patda_denda = $data->simpatda_denda + $DENDA;
					$data->npwpd = Pajak::formatNPWPD($data->npwpd);
					$data->patda_total_bayar = $data->simpatda_dibayar + $data->patda_denda;
					$data->RC = '00';
					$data->MSG = 'Inquiry Berhasil.';
				}
			}
		}
		else {
			$data->RC = '04'; /*default tagihan tidak ditemukan */
			$data->MSG = 'Data Tagihan Tidak Ditemukan.';
		}
		return $json->encode($data);
	}

	public function get_persen_denda($expired, $today = '')
    {
        if ($today == '') $today = date('Y-m-d');
        $date_of_month = date('Y-m-t', strtotime($expired));

        $bulan = 0;
        if (strtotime($today) > strtotime($expired)) {
            if ($expired != $date_of_month) {
                $date1 = new DateTime($expired);
                $date2 = $date1->diff(new DateTime($today));

                if ($date2->y > 0) {
                    $bulan += $date2->y * 12;
                }
                $bulan += $date2->m;
                $bulan += ($date2->d > 0) ? 1 : 0;
                $bulan = ($bulan == 0) ? 1 : $bulan;
            } else {
                $bulan = 0;
                $bulan = (date("Y", strtotime($today)) - date("Y", strtotime($expired))) * 12;
                $bulan += date("m", strtotime($today)) - date("m", strtotime($expired));
            }
        }

        $persen = ($bulan * 1);
        $persen = ($persen > 24) ? 24 : $persen;
        return $persen;
    }
	
	public function bayar(){
		global $json;
		$data = (object) $_POST;
		
		$inquiryRes = $this->inquiry();
		$inq = $json->decode($inquiryRes);
		$data = (object) array_merge((array) $inq, (array) $data);
		// var_dump($data);die;
		
		//gamabar upload
		$image = $data->berkas_backdate;
		$image = str_replace('data:image/jpeg;base64,', '', $image);

		$image = base64_decode($image, true);
		$nama = date('YmdHis');
		$filenam = 'paymentBackdate' . $nama . '.jpg';
		$file_path = '../../../upload/' . $filenam;

		file_put_contents($file_path, $image);
		//end gambar

		if($data->jml_kembali > 0){
			$data->jml_kembali = 0;
		}else{
			$data->jml_kembali = substr($data->jml_kembali,1);
		}
		//var_dump($data->jml_uang, $data->patda_total_bayar);die;
		
		
		$payPaid = $data->payment_paid;
		$data->payment_realdate = date('Y-m-d H:i:s');
		$data->payment_paid = ($payPaid == date('Y-m-d'))? date('Y-m-d H:i:s') : $payPaid.' 13:00:00';
		$data->PAYMENT_SETTLEMENT_DATE = str_replace('-','',$payPaid);
		//tambahan
		$data->payment_paid_kurangbayar = date('Y-m-d H:i:s');
		
		//echo '<pre>'.print_r($data,true).'</pre>';exit;
		if($data->RC == '00'){/*jika hasil inquiry berhasil*/
			
			$config = $this->get_config_value($data->app);
			$dbName = $config['PATDA_DBNAME'];
			$dbHost = $config['PATDA_HOSTPORT'];
			$dbPwd = $config['PATDA_PASSWORD'];
			$dbUser = $config['PATDA_USERNAME'];
			$settlmentDate = $config['PATDA_SETTLEMENT_DATE'];
			$Conn = mysqli_connect($dbHost, $dbUser, $dbPwd); 
			mysqli_select_db($Conn, $dbName);// edited by v -> migrating to mysqli
			
			$querySettlement = "SELECT ('{$data->payment_paid}' > '{$payPaid} {$settlmentDate}:00') AS LEBIH";
			$res = mysqli_query($Conn, $querySettlement) or die(mysqli_error($Conn));
			
			if($dataSettlement = mysqli_fetch_object($res)){
				/*jika lebih settlementdate*/
				if($dataSettlement->LEBIH == 1){
					$datePaid = explode('-',$payPaid);
					$tomorrow  = date('Ymd', mktime(0, 0, 0, $datePaid[1], $datePaid[2]+1, $datePaid[0]));
					$data->PAYMENT_SETTLEMENT_DATE = $tomorrow;
				}
			}
			
			//kondisi jenis pembayaran
			if($data->jenis_pembayaran == 0){
				$query = sprintf("
					UPDATE SIMPATDA_GW SET
						patda_collectible = '%s',
						patda_denda = '%s',
						patda_total_bayar = '%s',
						payment_flag = '%s',
						payment_paid = '%s',
						payment_bank_code = '%s',
						operator = '%s',
						PAYMENT_SETTLEMENT_DATE = '%s',
						payment_realdate = '%s'
					WHERE id_switching = '%s'",
					
					$data->simpatda_dibayar,
					$data->patda_denda,
					$data->patda_total_bayar,
					1 /*flag*/,
					$data->payment_paid,
					'1234567'/*bankcode*/,
					$data->uid /*operator*/,
					$data->PAYMENT_SETTLEMENT_DATE,
					$data->payment_realdate,
					$inq->id_switching
				);

				$query2 = sprintf("INSERT INTO patda_upload_file (CPM_NO_SPTPD,CPM_FILE_NAME,CPM_KODE_LAMPIRAN) 
				VALUES ('$data->sspd', '$filenam', '40') ");
			}else{
				//jika kurang bayar
				
					
				$jml_bayar = $data->jml_uang;
				
				if($jml_bayar >= $data->patda_denda){
					$jml_bayar = $jml_bayar - $data->patda_denda;
					$patda_denda = $data->patda_denda;
				}else{
					$data->patda_denda = $jml_bayar;
					$jml_bayar = 0;
				}


				if($jml_bayar >= $data->simpatda_dibayar){
					$jml_bayar = $jml_bayar - $data->simpatda_dibayar;
				}else{
					$data->simpatda_dibayar = $jml_bayar;
					$jml_bayar = 0;
				}
				
				//var_dump($data->patda_denda, $data->simpatda_dibayar, $data->jml_uang);die;
					
				$query = sprintf("
					UPDATE SIMPATDA_GW SET
						patda_collectible = '%s',
						patda_denda = '%s',
						patda_total_bayar = '%s',
						payment_flag = '%s',
						payment_paid = '%s',
						payment_bank_code = '%s',
						operator = '%s',
						PAYMENT_SETTLEMENT_DATE = '%s',
						payment_realdate = '%s',
						patda_kurangbayar = '%s'
					WHERE id_switching = '%s'",
					
					$data->simpatda_dibayar,
					$data->patda_denda,
					$data->jml_uang,
					1 /*flag*/,
					$data->payment_paid,
					'1234567'/*bankcode*/,
					$data->uid /*operator*/,
					$data->PAYMENT_SETTLEMENT_DATE,
					$data->payment_realdate,
					$data->jml_kembali,
					$inq->id_switching
				);
				$query2 = sprintf("INSERT INTO patda_upload_file (CPM_NO_SPTPD,CPM_FILE_NAME,CPM_KODE_LAMPIRAN) 
				VALUES ('$data->sspd', '$filenam', '40') ");
			}
			

			
			if(mysqli_query($Conn,$query) && mysqli_query($Conn, $query2)){
				$data->RC = '00';
				$data->MSG = 'Pembayaran Berhasil.';
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
		$qry = mysqli_query($Conn, $sql);
		$payment = mysqli_fetch_object($qry);
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
        $pdf->Output('Kwitansi Pembayaran Patda Backdate.pdf', 'I');
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
					{$data->periode}<br/>
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
		$res = mysqli_query($Conn, $query);
		
		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list['items'][] = array('id'=> $row->npwpd,'text'=>$row->wp_nama);
		}
		if(count($list) == 0){$list['items'][] = array('id'=>' ','text'=>'NPWPD tidak ditemukan');}
		echo $json->encode($list);
	}
	
	public function get_list_periode(){
		global $json;
		$data = (object) $_POST;
		
		$Conn = $this->ConnToGw($data->app);
		$NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['npwpd']);
		$PERIODE = $_REQUEST['periode'];
		$SIMPATDA_TYPE = $_REQUEST['simpatda_type'];
		
		$query = sprintf("select periode FROM SIMPATDA_GW WHERE periode like '%s' and npwpd = '%s' and simpatda_type = '%s' LIMIT 0,10", $PERIODE.'%',$NPWPD,$SIMPATDA_TYPE);
		$res = mysqli_query($Conn,$query);
		
		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list['items'][] = array('id'=> $row->periode);
		}
		if(count($list) == 0){$list['items'][] = array('id'=>' ','text'=>'Periode tidak ditemukan');}
		echo $json->encode($list);
	}
}

?>
