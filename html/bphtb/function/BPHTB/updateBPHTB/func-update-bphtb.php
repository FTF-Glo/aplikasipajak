<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'updateBPHTB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php"); 
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."function/BPHTB/updateBPHTB/func-form-update-bphtb.php");
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo $paytype;
$submit = @isset($_REQUEST['submit'])=='Submit' ? true : false;
if ($submit) {
	$statusNew = $_REQUEST['statusDokumen'];
	$statusCurrent = base64_decode($_REQUEST['statusDokumenCurrent']);
	$idDokumen = $_REQUEST['idssb'];
	$version = base64_decode($_REQUEST['ver-doc']);	
	
	
	$data = array();
	$data[0] = "-";
	$data[1] = "-";
    $cpm_ssb_author = @isset($_REQUEST['author'])? $_REQUEST['author']:"";
	$cpm_wp_nama = $data[2] = @isset($_REQUEST['name'])? $_REQUEST['name']:"Error: Nama Wajib Pajak tidak boleh dikosongkan!";
	$data[3] = @isset($_REQUEST['npwp'])? $_REQUEST['npwp']:"Error: NPWP tidak boleh dikosongkan!";
	$data[4] = @isset($_REQUEST['address'])? $_REQUEST['address']:"Error: Alamat tidak boleh dikosongkan!";
	$data[5] = "-";
	$data[6] = @isset($_REQUEST['kelurahan'])? $_REQUEST['kelurahan']:"Error: Kelurahan tidak boleh dikosongkan!"; 
	$data[7] = @isset($_REQUEST['rt'])? $_REQUEST['rt']:"Error: RT tidak boleh dikosongkan!";
	$data[8] = @isset($_REQUEST['rw'])? $_REQUEST['rw']:"Error: RW tidak boleh dikosongkan!";
	$data[9] = @isset($_REQUEST['kecamatan'])? $_REQUEST['kecamatan']:"Error: Kecamatan tidak boleh dikosongkan!";
	$data[10] = @isset($_REQUEST['kabupaten'])? $_REQUEST['kabupaten']:"Error: Kabupaten tidak boleh dikosongkan!";
	$data[11] = @isset($_REQUEST['zip-code'])? $_REQUEST['zip-code']:"Error: Kode POS tidak boleh dikosongkan!";
	$cpm_op_nomor = $data[12] = @isset($_REQUEST['name2'])? $_REQUEST['name2']:"Error: NOP PBB tidak boleh dikosongkan!";
	$data[13] = @isset($_REQUEST['address2'])? $_REQUEST['address2']:"Error: Alamat Objek Pajak tidak boleh dikosongkan!";
	$data[14] = "-";
	$data[15] = @isset($_REQUEST['kelurahan2'])? $_REQUEST['kelurahan2']:"Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
	$data[16] = @isset($_REQUEST['rt2'])? $_REQUEST['rt2']:"Error: RT Objek Pajak tidak boleh dikosongkan!";
	$data[17] = @isset($_REQUEST['rw2'])? $_REQUEST['rw2']:"Error: RW Objek Pajak tidak boleh dikosongkan!";
	$data[18] = @isset($_REQUEST['kecamatan2'])? $_REQUEST['kecamatan2']:"Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
	$data[19] = @isset($_REQUEST['kabupaten2'])? $_REQUEST['kabupaten2']:"Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
	$data[20] = @isset($_REQUEST['zip-code2'])? $_REQUEST['zip-code2']:"Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
	$data[21] = @isset($_REQUEST['right-year'])? $_REQUEST['right-year']:"Error: Tahun SPPT PBB tidak boleh dikosongkan!";
	$data[22] = @isset($_REQUEST['land-area'])? $_REQUEST['land-area']:"Error: Luas Tanah tidak boleh dikosongkan!";
	$data[23] = @isset($_REQUEST['land-njop'])? $_REQUEST['land-njop']:"Error: NJOP Tanah tidak boleh dikosongkan!";
	$data[24] = @isset($_REQUEST['building-area'])? $_REQUEST['building-area']:"Error: Luas Bangunan tidak boleh dikosongkan!";
	$data[25] = @isset($_REQUEST['building-njop'])? $_REQUEST['building-njop']:"Error: NJOP Bangunan tidak boleh dikosongkan!";
	$data[26] = @isset($_REQUEST['right-land-build'])? $_REQUEST['right-land-build']:"";
	$data[27] = @isset($_REQUEST['trans-value'])? $_REQUEST['trans-value']:"Error: Harga transasksi tidak boleh dikosongkan!";
	$data[28] = @isset($_REQUEST['certificate-number'])? $_REQUEST['certificate-number']:"Error: Nomor sertifikat tidak boleh dikosongkan!";
	$vNPOPTKP = @isset($_REQUEST['tNPOPTKP'])? $_REQUEST['tNPOPTKP']:"";
	$data[29] = @isset($_REQUEST['hd-npoptkp'])? ($vNPOPTKP ? $vNPOPTKP:$_REQUEST['hd-npoptkp']): "";
	$data[30] = @isset($_REQUEST['RadioGroup1'])? $_REQUEST['RadioGroup1']:"Error: Pilihan Jumlah Setoran tidak dipilih!";
	$data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose']:"Error: Pilihan jenis tidak dipilih!";
	$data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
	$data[33] = @isset($_REQUEST['jsb-choose-date'])? $_REQUEST['jsb-choose-date']:"Error: Tanggal surat tidak boleh dikosongkan!";
	$data[34] = "-";//$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
	$data[35] = @isset($_REQUEST['jsb-etc'])? $_REQUEST['jsb-etc']:"Error: Keterangan lain-lain tidak boleh dikosongkan!";
	$data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before']:"Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
	$data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] :"Error: No Aturan KHD tidak boleh di kosongkan!";
	$data[38] = @isset($_REQUEST['noktp'])? $_REQUEST['noktp']:"Error: Nomor KTP tidak boleh dikosongkan!";
	$data[39] = @isset($_REQUEST['jsb-choose-percent'])? $_REQUEST['jsb-choose-percent']:"0";
	$data[40] = @isset($_REQUEST['tBPHTBTU'])? $_REQUEST['tBPHTBTU']:"0";
	$data[41] = @isset($_REQUEST['nama-wp-lama'])? $_REQUEST['nama-wp-lama']:"Error: Nama WP lama tidak boleh di kosongkan!";
	$data[42] = @isset($_REQUEST['nama-wp-cert']) ? $_REQUEST['nama-wp-cert'] : "Error: Nama WP Sesuai Sertifikat tidak boleh di kosongkan!";
	
    $data[43] = @isset($_REQUEST['jsb-choose-fraction1']) ? $_REQUEST['jsb-choose-fraction1'] : "1";
    $data[44] = @isset($_REQUEST['jsb-choose-fraction2']) ? $_REQUEST['jsb-choose-fraction2'] : "1";
	$data[45] = @isset($_REQUEST['op-znt']) ? $_REQUEST['op-znt'] : "";
	$data[46] = @isset($_REQUEST['pengurangan-aphb']) ? $_REQUEST['pengurangan-aphb'] : "1";
	$koordinat = @isset($_REQUEST['koordinat']) ? $_REQUEST['koordinat'] : "0, 0";
	$denda = @isset($_REQUEST['denda-value'])? $_REQUEST['denda-value']:"0";
	$pdenda = @isset($_REQUEST['denda-percent'])? $_REQUEST['denda-percent']:"0";
	
	if (($data[29] == "0") || ($data[29] == 0)) {
				if (!getNOKTP($_REQUEST['noktp'])) {
					//print_r($_REQUEST['right-land-build']);
					if ($_REQUEST['right-land-build'] == 5) {
						$data[29] = getConfigValue('NPOPTKP_WARIS');
					} else if (($_REQUEST['right-land-build'] == 30) || ($_REQUEST['right-land-build'] == 31)|| ($_REQUEST['right-land-build'] == 32)|| ($_REQUEST['right-land-build'] == 33)) {
						$data[29] = 0;
					}else{
						$data[29] = getConfigValue('NPOPTKP_STANDAR');
					}
				}

			}
	
	$pAPHB="";
	if(($_REQUEST['right-land-build']==33) || ($_REQUEST['right-land-build']==7)){
		$pAPHB=$data[46];
	}else{
		$pAPHB="";
	}
	
	$typeSurat='';
	$typeSuratNomor='';
	$typeSuratTanggal='';
	$typePengurangan='';
	$typeLainnya='';
	$trdate=date("Y-m-d H:i:s"); 
	$opr=$_REQUEST['docauthor'];
	$nokhd="";
	$pengurangansplit=explode(".",$data[39]);
	$pengurangan=$pengurangansplit[1];
	$kdpengurangan=$pengurangansplit[0];
	if ($data[30] == 2) {
        $typeSurat = $data[31];
        $typeSuratNomor = $data[32];
        $typeSuratTanggal = $data[33];
    } else if ($data[30] == 3) {
        $typePengurangan = $data[34];
        $nokhd = $data[37];
    } else if ($data[30] == 4) {
        $typeLainnya = $data[35];
    }else if ($data[30] == 5) {
		$typePecahan  = $data[43]."/".$data[44];
    }
	$pengenaan=0;
		if (($_REQUEST['right-land-build'] == 5)||($_REQUEST['right-land-build'] == 4)||($_REQUEST['right-land-build'] == 31)) {
			$pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
		}
	$bphtb_sebelum=@isset($_REQUEST['tBPHTB_BAYAR']) ? $_REQUEST['tBPHTB_BAYAR'] : 0;
	$kurang_bayar = @isset($_REQUEST['bphtbtu']) ? $_REQUEST['bphtbtu'] : "0";
	if($data[30] == 2){
		$ccc = $kurang_bayar;
	}else{
		$ccc = getBPHTBPayment($data[24],$data[25],$data[22],$data[23],$data[27],$pengurangan,$data[26],$data[29], $pengenaan, $denda, $aphbt);
	}
	
	#proses ke table DOC
	$update = sprintf("UPDATE cppmod_ssb_doc
					SET CPM_KPP = '%s',
					 CPM_KPP_ID = '%s',
					 CPM_WP_NAMA = '%s',
					 CPM_WP_NPWP = '%s',
					 CPM_WP_ALAMAT = '%s',
					 CPM_WP_RT = '%s',
					 CPM_WP_RW = '%s',
					 CPM_WP_KELURAHAN = '%s',
					 CPM_WP_KECAMATAN = '%s',
					 CPM_WP_KABUPATEN = '%s',
					 CPM_WP_KODEPOS = '%s',
					 CPM_OP_NOMOR = '%s',
					 CPM_OP_LETAK = '%s',
					 CPM_OP_RT = '%s',
					 CPM_OP_RW = '%s',
					 CPM_OP_KELURAHAN = '%s',
					 CPM_OP_KECAMATAN = '%s',
					 CPM_OP_KABUPATEN = '%s',
					 CPM_OP_KODEPOS = '%s',
					 CPM_OP_THN_PEROLEH = '%s',
					 CPM_OP_LUAS_TANAH = '%s',
					 CPM_OP_LUAS_BANGUN = '%s',
					 CPM_OP_NJOP_TANAH = '%s',
					 CPM_OP_NJOP_BANGUN = '%s',
					 CPM_OP_JENIS_HAK = '%s',
					 CPM_OP_HARGA = '%s',
					 CPM_OP_NMR_SERTIFIKAT = '%s',
					 CPM_OP_NPOPTKP = '%s',
					 CPM_PAYMENT_TIPE = '%s',
					 CPM_PAYMENT_TIPE_SURAT = '%s',
					 CPM_PAYMENT_TIPE_SURAT_NOMOR = '%s',
					 CPM_PAYMENT_TIPE_SURAT_TANGGAL = '%s',
					 CPM_PAYMENT_TIPE_PENGURANGAN = '%s',
					 CPM_PAYMENT_TIPE_OTHER = '%s',
					 CPM_SSB_CREATED = '%s',
					 CPM_SSB_AUTHOR = '%s',
					 CPM_SSB_VERSION = '%s',
					 CPM_SSB_AKUMULASI = '%s',
					 CPM_PAYMENT_TIPE_KHD_NOMOR = '%s',
					 CPM_WP_NOKTP = '%s',
					 CPM_OP_BPHTB_TU = '%s',
					 CPM_WP_NAMA_LAMA = '%s',
					 CPM_WP_NAMA_CERT = '%s',
					 CPM_PAYMENT_TIPE_PECAHAN = '%s',
					 CPM_PAYMENT_TYPE_KODE_PENGURANGAN = '%s',
					 CPM_OP_ZNT = '%s',
					 CPM_PENGENAAN = '%s',
					 CPM_APHB = '%s',
					 CPM_KURANG_BAYAR_SEBELUM='%s',
					 CPM_KURANG_BAYAR='%s',
					 CPM_DENDA='%s',
					 CPM_PERSEN_DENDA='%s',
					 CPM_BPHTB_BAYAR='%s',
					 KOORDINAT='%s'
					 WHERE CPM_SSB_ID ='%s'",
					 '',
					 '',
					 mysqli_real_escape_string($DBLink, $data [ 2 ]),
					 mysqli_real_escape_string($DBLink, $data [ 3 ]),
					 mysqli_real_escape_string($DBLink, nl2br($data [ 4 ])),
					 mysqli_real_escape_string($DBLink, $data [ 7 ]),
					 mysqli_real_escape_string($DBLink, $data [ 8 ]),
					 mysqli_real_escape_string($DBLink, $data [ 6 ]),
					 mysqli_real_escape_string($DBLink, $data [ 9 ]),
					 mysqli_real_escape_string($DBLink, $data [ 10 ]),
					 mysqli_real_escape_string($DBLink, $data [ 11 ]),
					 mysqli_real_escape_string($DBLink, $data [ 12 ]),
					 mysqli_real_escape_string($DBLink, nl2br($data [ 13 ])),
					 mysqli_real_escape_string($DBLink, $data [ 16 ]),
					 mysqli_real_escape_string($DBLink, $data [ 17 ]),
					 mysqli_real_escape_string($DBLink, $data [ 15 ]),
					 mysqli_real_escape_string($DBLink, $data [ 18 ]),
					 mysqli_real_escape_string($DBLink, $data [ 19 ]),
					 mysqli_real_escape_string($DBLink, $data [ 20 ]),
					 mysqli_real_escape_string($DBLink, $data [ 21 ]),
					 mysqli_real_escape_string($DBLink, $data [ 22 ]),
					 mysqli_real_escape_string($DBLink, $data [ 24 ]),
					 mysqli_real_escape_string($DBLink, $data [ 23 ]),
					 mysqli_real_escape_string($DBLink, $data [ 25 ]),
					 mysqli_real_escape_string($DBLink, $data [ 26 ]),
					 mysqli_real_escape_string($DBLink, $data [ 27 ]),
					 mysqli_real_escape_string($DBLink, $data [ 28 ]),
					 mysqli_real_escape_string($DBLink, $data [ 29 ]),
					 mysqli_real_escape_string($DBLink, $data [ 30 ]),
					 mysqli_real_escape_string($DBLink, $typeSurat),
					 mysqli_real_escape_string($DBLink, $typeSuratNomor),
					 mysqli_real_escape_string($DBLink, $typeSuratTanggal),
					 mysqli_real_escape_string($DBLink, $pengurangan),
					 mysqli_real_escape_string($DBLink, $typeLainnya),
					 mysqli_real_escape_string($DBLink, $trdate),
					 mysqli_real_escape_string($DBLink, $opr),
					 mysqli_real_escape_string($DBLink, $version),
					 mysqli_real_escape_string($DBLink, $data [ 36 ]),
					 mysqli_real_escape_string($DBLink, $nokhd),
					 mysqli_real_escape_string($DBLink, $data [ 38 ]),
					 mysqli_real_escape_string($DBLink, $data [ 40 ]),
					 mysqli_real_escape_string($DBLink, $data [ 41 ]),
					 mysqli_real_escape_string($DBLink, $data [ 42 ]),
					 mysqli_real_escape_string($DBLink, $typePecahan),
					 mysqli_real_escape_string($DBLink, $kdpengurangan),
					 mysqli_real_escape_string($DBLink, $data [ 45 ]),
					 $pengenaan,
					 $pAPHB,
					 $bphtb_sebelum,
					 $kurang_bayar, 
					 $denda, 
					 $pdenda, 
					 $ccc, 
					 $koordinat, 
					 mysqli_real_escape_string($DBLink, $idDokumen));
	// echo $update;exit;

	mysqli_query($DBLink, $update);
	#proses ke table DOC LOG
	$update2 = sprintf("UPDATE cppmod_ssb_doc_log
					SET CPM_KPP = '%s',
					 CPM_KPP_ID = '%s',
					 CPM_WP_NAMA = '%s',
					 CPM_WP_NPWP = '%s',
					 CPM_WP_ALAMAT = '%s',
					 CPM_WP_RT = '%s',
					 CPM_WP_RW = '%s',
					 CPM_WP_KELURAHAN = '%s',
					 CPM_WP_KECAMATAN = '%s',
					 CPM_WP_KABUPATEN = '%s',
					 CPM_WP_KODEPOS = '%s',
					 CPM_OP_NOMOR = '%s',
					 CPM_OP_LETAK = '%s',
					 CPM_OP_RT = '%s',
					 CPM_OP_RW = '%s',
					 CPM_OP_KELURAHAN = '%s',
					 CPM_OP_KECAMATAN = '%s',
					 CPM_OP_KABUPATEN = '%s',
					 CPM_OP_KODEPOS = '%s',
					 CPM_OP_THN_PEROLEH = '%s',
					 CPM_OP_LUAS_TANAH = '%s',
					 CPM_OP_LUAS_BANGUN = '%s',
					 CPM_OP_NJOP_TANAH = '%s',
					 CPM_OP_NJOP_BANGUN = '%s',
					 CPM_OP_JENIS_HAK = '%s',
					 CPM_OP_HARGA = '%s',
					 CPM_OP_NMR_SERTIFIKAT = '%s',
					 CPM_OP_NPOPTKP = '%s',
					 CPM_PAYMENT_TIPE = '%s',
					 CPM_PAYMENT_TIPE_SURAT = '%s',
					 CPM_PAYMENT_TIPE_SURAT_NOMOR = '%s',
					 CPM_PAYMENT_TIPE_SURAT_TANGGAL = '%s',
					 CPM_PAYMENT_TIPE_PENGURANGAN = '%s',
					 CPM_PAYMENT_TIPE_OTHER = '%s',
					 CPM_SSB_CREATED = '%s',
					 CPM_SSB_AUTHOR = '%s',
					 CPM_SSB_VERSION = '%s',
					 CPM_SSB_AKUMULASI = '%s',
					 CPM_PAYMENT_TIPE_KHD_NOMOR = '%s',
					 CPM_WP_NOKTP = '%s',
					 CPM_OP_BPHTB_TU = '%s',
					 CPM_WP_NAMA_LAMA = '%s',
					 CPM_WP_NAMA_CERT = '%s',
					 CPM_PAYMENT_TIPE_PECAHAN = '%s',
					 CPM_PAYMENT_TYPE_KODE_PENGURANGAN = '%s',
					 CPM_OP_ZNT = '%s',
					 CPM_PENGENAAN = '%s',
					 CPM_APHB = '%s',
					 CPM_KURANG_BAYAR_SEBELUM='%s',
					 CPM_KURANG_BAYAR='%s',
					 CPM_DENDA='%s',
					 CPM_PERSEN_DENDA='%s',
					 CPM_BPHTB_BAYAR='%s'
					 WHERE CPM_SSB_ID ='%s'",
					 '',
					 '',
					 mysqli_real_escape_string($DBLink, $data [ 2 ]),
					 mysqli_real_escape_string($DBLink, $data [ 3 ]),
					 mysqli_real_escape_string($DBLink, nl2br($data [ 4 ])),
					 mysqli_real_escape_string($DBLink, $data [ 7 ]),
					 mysqli_real_escape_string($DBLink, $data [ 8 ]),
					 mysqli_real_escape_string($DBLink, $data [ 6 ]),
					 mysqli_real_escape_string($DBLink, $data [ 9 ]),
					 mysqli_real_escape_string($DBLink, $data [ 10 ]),
					 mysqli_real_escape_string($DBLink, $data [ 11 ]),
					 mysqli_real_escape_string($DBLink, $data [ 12 ]),
					 mysqli_real_escape_string($DBLink, nl2br($data [ 13 ])),
					 mysqli_real_escape_string($DBLink, $data [ 16 ]),
					 mysqli_real_escape_string($DBLink, $data [ 17 ]),
					 mysqli_real_escape_string($DBLink, $data [ 15 ]),
					 mysqli_real_escape_string($DBLink, $data [ 18 ]),
					 mysqli_real_escape_string($DBLink, $data [ 19 ]),
					 mysqli_real_escape_string($DBLink, $data [ 20 ]),
					 mysqli_real_escape_string($DBLink, $data [ 21 ]),
					 mysqli_real_escape_string($DBLink, $data [ 22 ]),
					 mysqli_real_escape_string($DBLink, $data [ 24 ]),
					 mysqli_real_escape_string($DBLink, $data [ 23 ]),
					 mysqli_real_escape_string($DBLink, $data [ 25 ]),
					 mysqli_real_escape_string($DBLink, $data [ 26 ]),
					 mysqli_real_escape_string($DBLink, $data [ 27 ]),
					 mysqli_real_escape_string($DBLink, $data [ 28 ]),
					 mysqli_real_escape_string($DBLink, $data [ 29 ]),
					 mysqli_real_escape_string($DBLink, $data [ 30 ]),
					 mysqli_real_escape_string($DBLink, $typeSurat),
					 mysqli_real_escape_string($DBLink, $typeSuratNomor),
					 mysqli_real_escape_string($DBLink, $typeSuratTanggal),
					 mysqli_real_escape_string($DBLink, $pengurangan),
					 mysqli_real_escape_string($DBLink, $typeLainnya),
					 mysqli_real_escape_string($DBLink, $trdate),
					 mysqli_real_escape_string($DBLink, $opr),
					 mysqli_real_escape_string($DBLink, $version),
					 mysqli_real_escape_string($DBLink, $data [ 36 ]),
					 mysqli_real_escape_string($DBLink, $nokhd),
					 mysqli_real_escape_string($DBLink, $data [ 38 ]),
					 mysqli_real_escape_string($DBLink, $data [ 40 ]),
					 mysqli_real_escape_string($DBLink, $data [ 41 ]),
					 mysqli_real_escape_string($DBLink, $data [ 42 ]),
					 mysqli_real_escape_string($DBLink, $typePecahan),
					 mysqli_real_escape_string($DBLink, $kdpengurangan),
					 mysqli_real_escape_string($DBLink, $data [ 45 ]),
					 $pengenaan,
					 $pAPHB,
					 $bphtb_sebelum,
					 $kurang_bayar, 
					 $denda, 
					 $pdenda, 
					 $ccc, 
					 mysqli_real_escape_string($DBLink,$idDokumen));
	mysqli_query($DBLink, $update2);
	
	#QUERY ke table doc berkas
	$update_to_berkas = sprintf("UPDATE cppmod_ssb_berkas SET CPM_BERKAS_KELURAHAN_OP='%s',
															CPM_BERKAS_KECAMATAN_OP ='%s',
															CPM_BERKAS_NAMA_WP='%s',
															CPM_BERKAS_NPWP='%s',
															CPM_BERKAS_HARGA_TRAN='%s',
															CPM_BERKAS_JNS_PEROLEHAN='%s'
															WHERE CPM_SSB_DOC_ID ='%s'",
					 										mysqli_real_escape_string($DBLink,$data[15]),
					 										mysqli_real_escape_string($DBLink,$data[18]),
					 										mysqli_real_escape_string($DBLink,$data[2]),
					 										mysqli_real_escape_string($DBLink,$data[3]),
					 										mysqli_real_escape_string($DBLink,$data[27]),
					 										mysqli_real_escape_string($DBLink,$data[26]),
					 										mysqli_real_escape_string($DBLink,$idDokumen));
	mysqli_query($DBLink, $update_to_berkas);

			
	$dbName = getConfigValue($a,'BPHTBDBNAME');
	$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
	$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
	$dbTable = getConfigValue($a,'BPHTBTABLE');
	$dbUser = getConfigValue($a,'BPHTBUSERNAME');
	$dbLimit = getConfigValue($a,'TENGGAT_WAKTU');
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	$update_to_ssb = sprintf("UPDATE ssb SET wp_nama='%s',
															wp_npwp='%s',
															wp_alamat ='%s',
															wp_rt ='%s',
															wp_rw ='%s',
															wp_kelurahan='%s',
															wp_kecamatan='%s',
															wp_kabupaten='%s',
															wp_kodepos='%s',
															op_nomor='%s',
															op_letak='%s',
															op_rt='%s',
															op_rw='%s',
															op_kelurahan='%s',
															op_kecamatan='%s',
															wp_noktp='%s',
															bphtb_dibayar='%s',
															op_luas_tanah='%s'

															WHERE id_switching ='%s'",
															mysqli_real_escape_string($DBLinkLookUp, $data [ 2 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 3 ]),
															mysqli_real_escape_string($DBLinkLookUp, nl2br($data [ 4 ])),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 7 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 8 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 6 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 9 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 10 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 11 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 12 ]),
															mysqli_real_escape_string($DBLinkLookUp, nl2br($data [ 13 ])),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 16 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 17 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 15 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data [ 18 ]),
															mysqli_real_escape_string($DBLinkLookUp, $data[38]),
					 										$ccc, 
															mysqli_real_escape_string($DBLinkLookUp, $data[22]),
					 										mysqli_real_escape_string($DBLinkLookUp,$idDokumen));

	mysqli_query($DBLinkLookUp, $update_to_ssb);
	
						
    $json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);


    $cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
    $cookiies=null;
    if (!empty($cData)) {
        $decData = base64_decode($cData);
        if ($decData) {
            $cookiies = $json->decode($decData);
        }
    }

	$insertssbhistorystatus="insert into ssb_history(id_ssb,id_switching,wp_nama,wp_alamat,wp_rt,wp_rw,wp_kelurahan,wp_kecamatan,wp_kabupaten,wp_kodepos,wp_noktp,wp_nohp,op_letak,op_rt,op_rw,op_kelurahan,op_kecamatan,op_kabupaten,bphtb_dibayar,op_nomor,payment_flag,isdraft,saved_date,expired_date,payment_paid,payment_ref_number,payment_bank_code,payment_sw_refnum,payment_gw_refnum,payment_sw_id,payment_merchant_code,bphtb_collectible,approved,payment_settlement_date,op_luas_tanah,op_luas_bangunan,bphtb_npop,bphtb_jenis_hak,bphtb_notaris,author,wp_npwp,payment_offline_user_id,payment_offline_paid,payment_code,approval_status,approval_msg,approval_qr_text,pelaporan_ke,user_perubahan) select id_ssb,id_switching,wp_nama,wp_alamat,wp_rt,wp_rw,wp_kelurahan,wp_kecamatan,wp_kabupaten,wp_kodepos,wp_noktp,wp_nohp,op_letak,op_rt,op_rw,op_kelurahan,op_kecamatan,op_kabupaten,bphtb_dibayar,op_nomor,payment_flag,isdraft,saved_date,expired_date,payment_paid,payment_ref_number,payment_bank_code,payment_sw_refnum,payment_gw_refnum,payment_sw_id,payment_merchant_code,bphtb_collectible,approved,payment_settlement_date,op_luas_tanah,op_luas_bangunan,bphtb_npop,bphtb_jenis_hak,bphtb_notaris,author,wp_npwp,payment_offline_user_id,payment_offline_paid,payment_code,approval_status,approval_msg,approval_qr_text,pelaporan_ke,'{$cookiies->uid}' from {$dbTable} where id_switching='".$idDokumen."'";
	mysqli_query($DBLinkLookUp, $insertssbhistorystatus);
	// var_dump($insertssbhistorystatus);
	// exit;
	if($statusNew!=$statusCurrent){
		
		$execute = true; #belum pembayaran
		#jika status sekarang final cek apakah di gateway sudah pembayaran
		if($statusCurrent==5){
			
			$dbName = getConfigValue($a,'BPHTBDBNAME');
			$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
			$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
			$dbTable = getConfigValue($a,'BPHTBTABLE');
			$dbUser = getConfigValue($a,'BPHTBUSERNAME');
			$dbLimit = getConfigValue($a,'TENGGAT_WAKTU');
			SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	
			$sql = "Select payment_flag from $dbTable where id_switching='".$idDokumen."'"; 
			$data = mysqli_query($DBLinkLookUp, $sql);	
			if($d = mysqli_fetch_array($data)){
				if($d['payment_flag']==1){
					$execute = false;						
				}else{
					$delete_ssb_update_dokumen = "delete from {$dbTable} where id_switching='".$idDokumen."'";
					mysqli_query($DBLinkLookUp, $delete_ssb_update_dokumen);
					$execute = true;
				}
			}		
		}
		
		#jika belum pembayaran
		if($execute==true){
			if ($statusNew != 99) {
				# code...
				#cek status yang baru apakah sudah ada di table
				$select = "select CPM_TRAN_ID 
							from cppmod_ssb_tranmain
							where 
							CPM_TRAN_FLAG='1' and
							CPM_TRAN_STATUS='".$statusNew."' and
							CPM_TRAN_SSB_ID='".$idDokumen."'";
				//echo $select;exit;
				$data = mysqli_query($DBLink, $select);
				
				#hapus posisi transaksi sekarang		
				$delete = "delete from cppmod_ssb_tranmain 
							where 
							CPM_TRAN_FLAG='0' and 
							CPM_TRAN_SSB_ID='".$idDokumen."'";
				mysqli_query($DBLink, $delete);
				
				#jika status yang baru sudah ada di table maka
				if(mysqli_num_rows($data)>0){
					$d = mysqli_fetch_array($data);				
					#update status yang baru menjadi aktif (flag=0)
					$update = "update cppmod_ssb_tranmain set CPM_TRAN_FLAG='0'
	                                            where 
	                                            CPM_TRAN_ID = '".$d['CPM_TRAN_ID']."'";
							
					mysqli_query($DBLink, $update);
					if(mysqli_num_rows($data)>0){
					
					if($statusNew<5){
						$dbName = getConfigValue($a,'BPHTBDBNAME');
						$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
						$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
						$dbTable = getConfigValue($a,'BPHTBTABLE');
						$dbUser = getConfigValue($a,'BPHTBUSERNAME');
						$dbLimit = getConfigValue($a,'TENGGAT_WAKTU');
						SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
				
						$sql = "Select * from $dbTable where id_switching='".$idDokumen."' and (payment_flag != 1 OR payment_flag IS NULL)"; 
						$data = mysqli_query($DBLinkLookUp, $sql);	
							$delete_ssbstatus = "delete from {$dbTable} where id_switching='".$idDokumen."'";
							mysqli_query($DBLinkLookUp, $delete_ssbstatus);
						}
						
					}

						
				}else{
					#status dokumen yang dipilih tidak ada di table
					$select_doc = "select CPM_SSB_AUTHOR 
							from cppmod_ssb_doc
							where 
							
							CPM_SSB_ID='".$idDokumen."'";
					$data_doc = mysqli_query($DBLink, $select_doc);
					$doc = mysqli_fetch_array($data_doc);
					$idtran = c_uuid();
					$refnum = c_uuid();
					
					$insert = sprintf("INSERT INTO cppmod_ssb_tranmain 
	                                                    (CPM_TRAN_ID,CPM_TRAN_REFNUM,
	                                                    CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,
	                                                    CPM_TRAN_STATUS,CPM_TRAN_FLAG,
	                                                    CPM_TRAN_DATE,CPM_TRAN_CLAIM,
	                                                    CPM_TRAN_OPR_NOTARIS,CPM_TRAN_OPR_DISPENDA_1,
	                                                    CPM_TRAN_OPR_DISPENDA_2,CPM_TRAN_INFO) 
	                                                    VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
	                                                    $idtran,$refnum,
	                                                    $idDokumen,$version,
	                                                    $statusNew,'0',
	                                                    mysqli_real_escape_string($DBLink, $trdate),'',
	                                                    mysqli_real_escape_string($DBLink, $doc['CPM_SSB_AUTHOR']),'',
	                                                    '','');	
					mysqli_query($DBLink, $insert);
				}
	            $arrLog = array(1=>10,2=>11,3=>12,4=>13,5=>14);
	            $action = $arrLog[$statusNew];
	            $log_input = "insert into cppmod_ssb_log(
	                        CPM_SSB_ID,
	                        CPM_SSB_LOG_ACTOR,
	                        CPM_SSB_LOG_ACTION,
	                        CPM_OP_NOMOR,
	                        CPM_WP_NAMA,
	                        CPM_SSB_AUTHOR) 
	                values ('".mysqli_real_escape_string($DBLink, $idDokumen)."',
	                        '".mysqli_real_escape_string($DBLink, $opr)."',                                   
	                        '".mysqli_real_escape_string($DBLink, $action)."',
	                        '".mysqli_real_escape_string($DBLink, $cpm_op_nomor)."',
	                        '".mysqli_real_escape_string($DBLink, $cpm_wp_nama)."',
	                        '".mysqli_real_escape_string($DBLink, $cpm_ssb_author)."')";                      
	            mysqli_query($DBLink, $log_input);
			}
			
			echo "Dokumen <b>Berhasil</b> diubah ...! ";
		}else{
			echo "<b>Maaf</b>! Dokumen tidak bisa direversal karena sudah pembayaran!";	
		}	
	}else{
		echo "Data <b>berhasil</b> diubah dan tidak ada perubahan terhadap status dokumen..";	
	}
	
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&n=4";
	$address = $_SERVER['HTTP_HOST']."payment/pc/svr/central/main.php?param=".base64_encode($params);
	echo "\n<script language=\"javascript\">\n";
	
	echo "	function delayer(){\n";
	echo "		window.location = \"./main.php?param=".base64_encode($params)."\"\n";
	echo "	}\n";
	echo "	Ext.onReady(function(){\n";
	echo "		setTimeout('delayer()', 3500);\n";
	echo "	});\n";
	echo "</script>\n";
}elseif($pros=='del'){
			$dbName = getConfigValue($a,'BPHTBDBNAME');
			$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
			$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
			$dbTable = getConfigValue($a,'BPHTBTABLE');
			$dbUser = getConfigValue($a,'BPHTBUSERNAME');
			$dbLimit = getConfigValue($a,'TENGGAT_WAKTU');
			SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
		$sql_ssb="select payment_flag from {$dbTable} where id_switching='".$idssb."'";
		$res = mysqli_query($DBLinkLookUp, $sql_ssb);
		$dt = mysqli_fetch_assoc($res);
		//echo $sql_ssb;exit;
		
		if($dt['payment_flag']!=1){
			$insertdochistory="insert into cppmod_ssb_doc_history select * from cppmod_ssb_doc where CPM_SSB_ID='".$idssb."'";
			$delete_doc = "delete from cppmod_ssb_doc where CPM_SSB_ID='".$idssb."'";
			$inserttranmainhistory="insert into cppmod_ssb_tranmain_history select * from cppmod_ssb_tranmain where CPM_TRAN_SSB_ID='".$idssb."'";
			$delete_tranmain = "delete from cppmod_ssb_tranmain where CPM_TRAN_SSB_ID='".$idssb."'";
			
			mysqli_query($DBLink, $insertdochistory);
			mysqli_query($DBLink, $delete_doc);
			mysqli_query($DBLink, $inserttranmainhistory);
			mysqli_query($DBLink, $delete_tranmain);
		
			
			$insertssbhistory="insert into ssb_history select * from {$dbTable} where id_switching='".$idssb."'";
			$delete_ssb = "delete from {$dbTable} where id_switching='".$idssb."'";
			//echo $delete_ssb;exit;
			mysqli_query($DBLinkLookUp, $insertssbhistory);
			mysqli_query($DBLinkLookUp, $delete_ssb);
			echo "Data <b>berhasil</b> dihapus!";
		}else{
			echo "<b>Maaf!</b> Dokumen yg Sudah dibayar tidak bisa dihapus!";
		}
		
		
		
	
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&n=4";
	$address = $_SERVER['HTTP_HOST']."payment/pc/svr/central/main.php?param=".base64_encode($params);
	echo "\n<script language=\"javascript\">\n";
	
	echo "	function delayer(){\n";
	echo "		window.location = \"./main.php?param=".base64_encode($params)."\"\n";
	echo "	}\n";
	echo "	Ext.onReady(function(){\n";
	echo "		setTimeout('delayer()', 3500);\n";
	echo "	});\n";
	echo "</script>\n";
}else{
	getSelectedData($idssb,$data);
	if($paytype!=2){
		$query = "SELECT * FROM cppmod_ssb_doc WHERE CPM_SSB_ID = '".$idssb."'";
		$datas = mysqli_query($DBLink, $query);
		$datas = mysqli_fetch_object($datas);
		echo formSSB ($datas, true);
	}else{
		echo formSSBKB ($data, true);
	}
	

}
function getConfigValues($key) {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}
function getNOKTP($noktp) {
    global $DBLink;

    $N1 = getConfigValues('NPOPTKP_STANDAR');
    $N2 = getConfigValues('NPOPTKP_WARIS');
    $day = getConfigValues("BATAS_HARI_NPOPTKP");
    $dbLimit = getConfigValues('TENGGAT_WAKTU');

    $dbName = getConfigValues('BPHTBDBNAME');
    $dbHost = getConfigValues('BPHTBHOSTPORT');
    $dbPwd = getConfigValues('BPHTBPASSWORD');
    $dbTable = getConfigValues('BPHTBTABLE');
    $dbUser = getConfigValues('BPHTBUSERNAME');
    // Connect to lookup database
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    //payment_flag, mysqli_real_escape_string($payment_flag),
	$tahun = date('Y');
    $qry = "select * 
	        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' and CPM_OP_THN_PEROLEH= '{$tahun}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
			AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 30 AND A.CPM_OP_JENIS_HAK <> 31 
			AND A.CPM_OP_JENIS_HAK <> 32 AND A.CPM_OP_JENIS_HAK <> 33";
//print_r($qry); 
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        return false;
    }

    if (mysqli_num_rows ($res)) {
		$num_rows = mysqli_num_rows($res);
		$query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
				FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
		//print_r($query2);
		$r = mysqli_query($DBLinkLookUp, $query2);
		if ( $r === false ){
			die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
		}
		if(mysqli_num_rows ($r)==0){
		
			$query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
			$r2 = mysqli_query($DBLinkLookUp, $query3);
			//print_r($query3);exit;
			if ( $r2 === false ){
				die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
			}
			if (mysqli_num_rows($r2)) {
				return true;
			}else{
				return false;
			}					
		}else{
			while($rowx = mysqli_fetch_assoc($r)){
				if ($rowx['EXPRIRE']) {
					return false;
				}else{
					return true;
				}
			
			}
		}
	}
	else return false;
}
?>