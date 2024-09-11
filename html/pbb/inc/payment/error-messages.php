<?php
class ErrorMessageMapper {
	private $iDebug = 0;
	private $sLogFilename = "";
	private $DBLink = NULL;
	private $sThisFile;
	private $iErrCode = 0;
	private $sErrMsg = '';

	public function __construct($iDebug = 0, $sLogFilename, $DBLink) {
		$this->iDebug = $iDebug;
		$this->sLogFilename = $sLogFilename;
		$this->DBLink = $DBLink;
		$this->sThisFile = basename(__FILE__);
		$this->initTable();
		$this->initValue();
	}

	private function SetError($iErrCode=0, $sErrMsg='') {
		$this->iErrCode = $iErrCode;
		$this->sErrMsg = $sErrMsg;
	}

	public function GetLastError(&$iErrCode, &$sErrMsg) {
		$iErrCode = $this->iErrCode;
		$sErrMsg = $this->sErrMsg;
	}
	
	private function initTable() {
			try {
				$sQ = "CREATE TABLE IF NOT EXISTS cppmod_rc_general (".
						"CPM_RC_MTI INTEGER,".
						"CPM_RC_RC INTEGER,".
						"CPM_RC_MESSAGE TEXT,".
						"PRIMARY KEY (CPM_RC_MTI, CPM_RC_RC)".
						")";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "CREATE TABLE IF NOT EXISTS cppmod_rc_module (".
						"CPM_RC_MODULE varchar(255),".
						"CPM_RC_MTI INTEGER,".
						"CPM_RC_RC INTEGER,".
						"CPM_RC_MESSAGE TEXT,".
						"PRIMARY KEY (CPM_RC_MODULE, CPM_RC_MTI, CPM_RC_RC)".
						")";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "CREATE TABLE IF NOT EXISTS cppmod_rc_custom (".
						"CPM_RC_CUSTOM varchar(255),".
						"CPM_RC_MODULE varchar(255),".
						"CPM_RC_MTI INTEGER,".
						"CPM_RC_RC INTEGER,".
						"CPM_RC_MESSAGE TEXT,".
						"PRIMARY KEY (CPM_RC_CUSTOM, CPM_RC_MODULE, CPM_RC_MTI, CPM_RC_RC)".
						")";
				@mysqli_query($this->DBLink, $sQ);
			} catch (Exception $err) {
				
			}
		}
		
		private function initValue(){
			try {
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','0','Cek Tagihan Sukses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2110','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);

				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','0','Pembayaran Sukses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2210','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);

				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','0','Cek Tagihan Sukses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2230','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);

				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','0','Cek Tagihan Sukses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2231','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);

				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','0','Transaksi dibatalkan silahkan ulangi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2410','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);
				
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','0','Transaksi dibatalkan silahkan ulangi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','4','[4] ERROR Biller Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','5','[5] ERROR Lainnya')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','6','[6] ERROR Sentral diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','7','[7] ERROR PPID diblok')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','8','[8] ERROR Waktu akses tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','9','[9] ERROR Akun tidak aktif')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','11','[11] ERROR NEED TO SIGN ON')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','12','[12] ERROR Tidak bisa dibatalkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','13','[13] ERROR Nilai Transaksi tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','14','[14] ERROR ID Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','15','[15] ERROR No Meter Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','16','[16] ERROR PRR SUBSCRIBER')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','17','[17] ERROR ID Punya Tunggakan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','18','[18] ERROR Permintaan Sedang diproses')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','29','[29] ERROR Kode Hash tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','30','[30] ERROR Pesan tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','31','[31] ERROR Kode Bank tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','32','[32] ERROR Sentral tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','33','[33] ERROR Produk tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','34','[34] ERROR PPID Tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','35','[35] ERROR Akun Tidak Terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','41','[41] ERROR Nilai Transaksi dibawah Nilai Minimum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','42','[42] ERROR Nilai Transaksi diatas Nilai Maximum')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','43','[43] ERROR Daya Baru Lebih Kecil dari Daya Sekarang')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','44','[44] ERROR Nilai Daya Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','45','[45] ERROR Nilai Biaya Administrasi Tidak Valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','46','[46] ERROR Deposit Tidak Mencukupi')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','47','[47] ERROR Diluar Batas KWH')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','48','[48] ERROR Permintaan sudah kadaluarsa')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','51','[51] ERROR Transaksi Gagal dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','52','[52] ERROR Transaksi dipending dari Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','53','[53] ERROR Produk tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','54','[54] ERROR Jawaban dari Biller Tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','55','[55] ERROR Lainnya Mesin Vending')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','56','[56] ERROR Nomor Telpon Tidak diketahui')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','63','[63] ERROR Tidak ada Pembayaran')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','66','[60] ERROR Akun sudah didaftarkan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','67','[67] ERROR CANNOT CONNECT')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','68','[68] ERROR Timeout')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','69','[69] ERROR Sertifikat tidak dikenal')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','70','[70] ERROR Timeout tidak refund')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','72','[72] ERROR Permintaan tidak mungkin dilayani')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','73','[73] ERROR Request dipending di Biller')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','77','[77] ERROR Id di suspend')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','88','[88] ERROR Tagihan sudah dibayar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','89','[89] ERROR Tagihan tidak tersedia')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','90','[90] ERROR sedang proses CUT OFF')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','91','[91] ERROR Database')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','92','[92] ERROR Nomor Referensi Switching tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','93','[93] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','94','[94] ERROR Pembatalan sudah dilakukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','95','[95] ERROR Kode Merchant tidak terdaftar')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','96','[96] ERROR Transaksi tidak ditemukan')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','97','[97] ERROR SW BANK Tidak identik')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','98','[98] ERROR Nomor Referensi Switching tidak valid')";
				@mysqli_query($this->DBLink, $sQ);
				$sQ= "INSERT INTO cppmod_rc_general VALUES ('2411','146','[146] ERROR di servis deposit')";
				@mysqli_query($this->DBLink, $sQ);

			} catch (Exception $err) {
				
			}
		}
		
		public function getMessage($MTI, $RC, $module = "", $custom = "") {
			$retval = "ERROR Tidak Terdefinisi";
		
			try {
					$sQ = "SELECT * FROM cppmod_rc_general WHERE CPM_RC_MTI = '".$MTI.
							"' AND CPM_RC_RC = '".$RC.
							"'";
					if ($result = mysqli_query($this->DBLink, $sQ)) {
						if($row = mysqli_fetch_array($result)){
							$retval=$row[2];
						}
					}
			} catch (Exception $err) {
			}
			
			if ($module != "") {
				try {
					$sQ = "SELECT * FROM cppmod_rc_module WHERE CPM_RC_MODULE = '".$module.
							"' AND CPM_RC_MTI = '".$MTI.
							"' AND CPM_RC_RC = '".$RC.
							"'";
					if ($result = mysqli_query($this->DBLink, $sQ)) {
						if($row = mysqli_fetch_array($result)){
							$retval=$row[3];
						}
					}
				} catch (Exception $err) {
						
				}
			}
				
			if ($module != "" && $custom != "") {
				try {
					$sQ = "SELECT * FROM cppmod_rc_custom WHERE CPM_RC_CUSTOM = '".$custom.
							"' AND CPM_RC_MODULE = '".$module.
							"' AND CPM_RC_MTI = '".$MTI.
							"' AND CPM_RC_RC = '".$RC.
							"'";
					if ($result = mysqli_query($this->DBLink, $sQ)) {
						if($row = mysqli_fetch_array($result)){
							$retval=$row[4];
						}
						}
				} catch (Exception $err) {
						
				}
			}
			
			return $retval;
		}
	
}

?>
