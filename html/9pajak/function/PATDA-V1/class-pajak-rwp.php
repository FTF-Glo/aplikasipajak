<?php
if(session_id() == '') {
    session_start();
}

/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : print_skpd
*/
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
//ini_set("error_log", "/tmp/patda-base-v2-error.log");

//DEFINE('BASE_URL', 'http://192.168.26.112/9pajak/kabkupang/');
$bURL = explode("?", isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
DEFINE('BASE_URL',$bURL[0]);
class Pajak {

    public $base_url = BASE_URL;

    #grid
    protected $pageSize = 30;
    #request
    public $_id; #id data
    public $_idp; #id profil
    public $_s; #status
    public $_mod; #modul
    public $_flg; #flag
    public $_info; #info
    public $_a; #app
    public $_m; #modul_id
    public $_f; #function
    public $_i; #id tab
    public $_type; #tipe pajak
    public $_sts; #status berkas

    #field
    protected $CPM_ID;
    protected $CPM_ID_PROFIL;
    protected $CPM_NO;
    protected $CPM_NPWPD;
    protected $CPM_NAMA_WP;
    protected $CPM_ALAMAT_WP;
    protected $CPM_KELURAHAN_WP;
    protected $CPM_KECAMATAN_WP;
	protected $CPM_KECAMATAN_WP1; //new by v
	protected $CPM_KELURAHAN_WP1; //new by v
    protected $CPM_TRUCK_ID; //new by v - minerba tracking
    protected $CPM_KAPASITAS_ANGKUT; //new by v - minerba tracking
    protected $CPM_NOPOL; //new by v - minerba tracking
	protected $CPM_LUAR_DAERAH;
    protected $CPM_NOP;
    protected $CPM_NAMA_OP;
    protected $CPM_ALAMAT_OP;
    protected $CPM_JENIS_PAJAK;
    protected $CPM_MASA_PAJAK;
    protected $CPM_MASA_PAJAK1;
    protected $CPM_MASA_PAJAK2;
    protected $CPM_TAHUN_PAJAK;
    protected $CPM_TOTAL_KWH;
    protected $CPM_HARGA_DASAR;
    protected $CPM_TOTAL_OMZET;
    protected $CPM_TIPE_MASA;
    protected $CPM_TOTAL_PAJAK;
    protected $CPM_TARIF_PAJAK;
    protected $CPM_TGL_LAPOR;
    protected $CPM_TGL_JATUH_TEMPO;
    protected $CPM_TERBILANG;
    protected $CPM_TRAN_INFO;
    protected $CPM_VERSION;
    protected $CPM_AUTHOR;
    protected $CPM_KETERANGAN;
    protected $CPM_TIPE_PAJAK;
    protected $CPM_NO_SSPD;
    protected $CPM_PERPANJANG;
    protected $AUTHORITY;
    protected $CPM_SANKSI = 0;
    protected $CPM_TRAN_READ = "";
    protected $CPM_DISCOUNT;
    protected $EXPIRED_DATE;

    #table
    protected $SUFIKS = "";
    protected $MODULE_ID = "";
    protected $PATDA_AIRBAWAHTANAH_DOC;
    protected $PATDA_AIRBAWAHTANAH_DOC_TRANMAIN;
    protected $PATDA_AIRBAWAHTANAH_PROFIL;
    protected $PATDA_HIBURAN_DOC;
    protected $PATDA_HIBURAN_DOC_TRANMAIN;
    protected $PATDA_HIBURAN_PROFIL;
    protected $PATDA_HOTEL_DOC;
    protected $PATDA_HOTEL_DOC_TRANMAIN;
    protected $PATDA_HOTEL_PROFIL;
    protected $PATDA_MINERAL_DOC;
    protected $PATDA_MINERAL_DOC_ATR;
    protected $PATDA_MINERAL_DOC_TRANMAIN;
    protected $PATDA_MINERAL_PROFIL;
    protected $PATDA_PARKIR_DOC;
    protected $PATDA_PARKIR_DOC_TRANMAIN;
    protected $PATDA_PARKIR_PROFIL;
    protected $PATDA_JALAN_DOC;
    protected $PATDA_JALAN_DOC_TRANMAIN;
    protected $PATDA_JALAN_PROFIL;
    protected $PATDA_REKLAME_DOC;
    protected $PATDA_REKLAME_DOC_ATR;
    protected $PATDA_REKLAME_DOC_TRANMAIN;
    protected $PATDA_REKLAME_PROFIL;
    protected $PATDA_RESTORAN_DOC;
    protected $PATDA_RESTORAN_DOC_TRANMAIN;
    protected $PATDA_RESTORAN_PROFIL;
    protected $PATDA_WALET_DOC;
    protected $PATDA_WALET_DOC_ATR;
    protected $PATDA_WALET_DOC_TRANMAIN;
    protected $PATDA_WALET_PROFIL;
    protected $PATDA_SKPDKB;
    protected $PATDA_SKPDKB_TRANMAIN;
    protected $PATDA_STPD;
    protected $PATDA_STPD_TRANMAIN;
    protected $PATDA_BERKAS;
    protected $PATDA_PETUGAS;
    protected $PATDA_TARIF;
    protected $PATDA_WP;
    protected $PATDA_REK_PERMEN13;
    protected $PATDA_JENIS_PAJAK;

    #object
    protected $Conn;
    protected $Data;
    protected $Message;
    protected $Json;

    #var
    public $arr_kdpajak = array(1 => "AIR", 2 => "HIB", 3 => "HTL", 4 => "GAL", 5 => "PKR", 6 => "LIS", 7 => "REK", 8 => "RES", 9 => "WLT");
    public $arr_pajak = array(1 => "Air Bawah Tanah", 2 => "Hiburan", 3 => "Hotel", 4 => "Mineral Non Logam dan Batuan", 5 => "Parkir",
        6 => "Penerangan Jalan", 7 => "Reklame", 8 => "Restoran", 9 => "Sarang Walet");
    public $arr_idpajak = array(1 => "airbawahtanah", 2 => "hiburan", 3 => "hotel", 4 => "mineral", 5 => "parkir",
        6 => "jalan", 7 => "reklame", 8 => "restoran", 9 => "walet");
    public $arr_bulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");
    public $arr_status = array(1 => "Draft", 2 => "Menunggu Verifikasi", 3 => "Menunggu Persetujuan", 4 => "Ditolak", 5 => "Disetujui", 6 => "Dibayar");
    public $arr_kurangbayar = array(0 => "SKPDKB", 1 => "SKPDKBT");
    public $arr_role = array("rmPatdaAdmin" => "Admin",
        "rmPatdaPelayanan" => "Pelayanan",
        "rmPatdaVerifikasi" => "Verifikasi SubBid I",
        "rm2Verifikasi2" => "Verifikasi SubBid II",
	"rmPatdaPenetapan" => "Penetapan",
        "rmPatdaPenagihan" => "Penagihan",
        "rmPatdaMonitoring" => "Monitoring");
    public $arr_tambahan = array(0 => "SKPDKB", 1 => "SKPDKBT");
    public $idpajak_sw_to_gw = array(1 => 11, 2 => 6, 3 => 4, 4 => 9, 5 => 10, 6 => 8, 7 => 7, 8 => 5, 9 => 12);
    public $idpajak_gw_to_sw = array(11 => 1, 6 => 2, 4 => 3, 9 => 4, 10 => 5, 8 => 6, 7 => 7, 5 => 8, 12 => 9);
    public $arr_tipe_pajak = array(1 => "Reguler", 2 => "Non Reguler");
    public $non_reguler = array(1 => 31, 2 => 26, 3 => 24, 4 => 29, 5 => 30, 6 => 28, 7 => 27, 8 => 25, 9 => 32);
    protected $notif = true;
    public $arr_pajak_tapbox = array("HIBURAN" => "Hiburan", "HOTEL" => "Hotel", "PARKIR" => "Parkir", "RESTORAN" => "Restoran");

    function __construct() {
        global $DBLink, $data, $id, $json;

        $this->base_url = BASE_URL;
        $this->Conn = $DBLink;
        $this->Data = $data;
        $this->Json = $json;
        $this->Message = class_exists("Message") ? new Message() : "";

        $this->_a = isset($_REQUEST['a']) ? $_REQUEST['a'] : "";
        $this->_m = isset($_REQUEST['m']) ? $_REQUEST['m'] : "";
        $this->_f = isset($_REQUEST['f']) ? $_REQUEST['f'] : "";

        $this->_id = isset($id) ? $id : "";
        $this->_idp = isset($_REQUEST['idp']) ? $_REQUEST['idp'] : "";
        $this->_s = isset($_REQUEST['s']) ? $_REQUEST['s'] : "";
        $this->_mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : "";
        $this->_flg = isset($_REQUEST['flg']) ? $_REQUEST['flg'] : "";
        $this->_info = isset($_REQUEST['info']) ? $_REQUEST['info'] : "";
        $this->_i = isset($_REQUEST['i']) ? $_REQUEST['i'] : "";
        $this->_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
        $this->_sts = isset($_REQUEST['sts']) ? $_REQUEST['sts'] : "";

       #table
        $this->PATDA_AIRBAWAHTANAH_DOC = "PATDA_AIRBAWAHTANAH_DOC{$this->SUFIKS}";
        $this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN = "PATDA_AIRBAWAHTANAH_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_AIRBAWAHTANAH_PROFIL = "PATDA_AIRBAWAHTANAH_PROFIL{$this->SUFIKS}";
        $this->PATDA_HIBURAN_DOC = "PATDA_HIBURAN_DOC{$this->SUFIKS}";
        $this->PATDA_HIBURAN_DOC_TRANMAIN = "PATDA_HIBURAN_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_HIBURAN_PROFIL = "PATDA_HIBURAN_PROFIL{$this->SUFIKS}";
        $this->PATDA_HOTEL_DOC = "PATDA_HOTEL_DOC{$this->SUFIKS}";
        $this->PATDA_HOTEL_DOC_TRANMAIN = "PATDA_HOTEL_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_HOTEL_PROFIL = "PATDA_HOTEL_PROFIL{$this->SUFIKS}";
        $this->PATDA_MINERAL_DOC = "PATDA_MINERAL_DOC{$this->SUFIKS}";
        $this->PATDA_MINERAL_DOC_ATR = "PATDA_MINERAL_DOC_ATR{$this->SUFIKS}";
        $this->PATDA_MINERAL_DOC_TRANMAIN = "PATDA_MINERAL_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_MINERAL_PROFIL = "PATDA_MINERAL_PROFIL{$this->SUFIKS}";
        $this->PATDA_PARKIR_DOC = "PATDA_PARKIR_DOC{$this->SUFIKS}";
        $this->PATDA_PARKIR_DOC_TRANMAIN = "PATDA_PARKIR_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_PARKIR_PROFIL = "PATDA_PARKIR_PROFIL{$this->SUFIKS}";
        $this->PATDA_JALAN_DOC = "PATDA_JALAN_DOC{$this->SUFIKS}";
        $this->PATDA_JALAN_DOC_TRANMAIN = "PATDA_JALAN_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_JALAN_PROFIL = "PATDA_JALAN_PROFIL{$this->SUFIKS}";
        $this->PATDA_REKLAME_DOC = "PATDA_REKLAME_DOC{$this->SUFIKS}";
        $this->PATDA_REKLAME_DOC_ATR = "PATDA_REKLAME_DOC_ATR{$this->SUFIKS}";
        $this->PATDA_REKLAME_DOC_TRANMAIN = "PATDA_REKLAME_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_REKLAME_PROFIL = "PATDA_REKLAME_PROFIL{$this->SUFIKS}";
        $this->PATDA_RESTORAN_DOC = "PATDA_RESTORAN_DOC{$this->SUFIKS}";
        $this->PATDA_RESTORAN_DOC_TRANMAIN = "PATDA_RESTORAN_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_RESTORAN_PROFIL = "PATDA_RESTORAN_PROFIL{$this->SUFIKS}";
        $this->PATDA_WALET_DOC = "PATDA_WALET_DOC{$this->SUFIKS}";
        $this->PATDA_WALET_DOC_ATR = "PATDA_WALET_DOC_ATR{$this->SUFIKS}";
        $this->PATDA_WALET_DOC_TRANMAIN = "PATDA_WALET_DOC_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_WALET_PROFIL = "PATDA_WALET_PROFIL{$this->SUFIKS}";
        $this->PATDA_SKPDKB = "PATDA_SKPDKB{$this->SUFIKS}";
        $this->PATDA_SKPDKB_TRANMAIN = "PATDA_SKPDKB_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_STPD = "PATDA_STPD{$this->SUFIKS}";
        $this->PATDA_STPD_TRANMAIN = "PATDA_STPD_TRANMAIN{$this->SUFIKS}";
        $this->PATDA_BERKAS = "PATDA_BERKAS{$this->SUFIKS}";
        $this->PATDA_PETUGAS = "PATDA_PETUGAS{$this->SUFIKS}";
        $this->PATDA_TARIF = "PATDA_TARIF{$this->SUFIKS}";
        $this->PATDA_WP = "PATDA_WP{$this->SUFIKS}";
        $this->PATDA_REK_PERMEN13 = "PATDA_REK_PERMEN13{$this->SUFIKS}";
        $this->PATDA_JENIS_PAJAK = "PATDA_JENIS_PAJAK{$this->SUFIKS}";

        if($this->Conn) $this->set_jenis_pajak();
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
    }

	protected function inisialisasi_masa_pajak(){
		/*PALEMBANG
		 * menampilkan bulan kemarin,
		 * misal sekarang adalah februari 2016, maka yang tampil adalah januari 2016
		 * misal sekarang adalah januari 2016, maka yang tampil adalah desember 2015

		$masa_pajak = date("m")==1? 12 : date("m")-1;
        $tahun_pajak = date("m")==1? date("Y")-1 : date("Y");
        $bln = str_pad($masa_pajak, 2, 0, STR_PAD_LEFT);
        */

        $masa_pajak = date("m");
        $tahun_pajak = date("Y");
        $bln = str_pad($masa_pajak, 2, 0, STR_PAD_LEFT);

        $res['masa_pajak'] = $masa_pajak;
        $res['tahun_pajak'] = $tahun_pajak;
        $res['masa_pajak1'] = "01/{$bln}/{$tahun_pajak}";
        $res['masa_pajak2'] = date("t",strtotime("{$tahun_pajak}-{$bln}"))."/{$bln}/{$tahun_pajak}";

        return $res;
	}

	public function set_jenis_pajak() {
        $query = "SELECT * FROM PATDA_JENIS_PAJAK ORDER BY CPM_NO";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        while($d = mysqli_fetch_assoc($res)){
            if($d['CPM_TIPE']<=12){
				$this->arr_pajak[$d['CPM_NO']] = $d['CPM_JENIS'];
				$this->arr_pajak_table[$d['CPM_NO']] = $d['CPM_TABLE'];
			}

            $this->arr_pajak_gw[$d['CPM_TIPE']] = $d['CPM_JENIS'];
			$this->arr_pajak_gw_table[$d['CPM_TIPE']] = $d['CPM_TABLE'];
			$this->arr_pajak_gw_no[$d['CPM_TIPE']] = $d['CPM_NO'];

        }
    }

    public function redirect($url = "") {
        $this->base_url = str_replace('main.php','',$this->base_url);
        $url = empty($url) ?
        $this->base_url. 'main.php?param=' . base64_encode("a={$this->_a}&m={$this->_m}") :
        $this->base_url. $url;

        header("location:{$url}");
    }

   	private function get_norek_on_save_gateway(){
		$pajak = isset($_REQUEST['PAJAK'])? $_REQUEST['PAJAK'] : null;
		$atr = isset($_REQUEST['PAJAK_ATR'])? $_REQUEST['PAJAK_ATR'] : null;

		$rek = '';
		$rek = isset($pajak['CPM_REKENING'])? $pajak['CPM_REKENING'] : $rek;

		if($atr){
			if(isset($atr['CPM_ATR_NAMA'])){
				$_list_rek = array();
				foreach($atr['CPM_ATR_NAMA'] as $val){
					$_list_rek[] = $val;
				}
				if(count($_list_rek)> 0) $rek = implode(';',$_list_rek);
			}

			$rek = isset($atr['CPM_ATR_REKENING'])? $atr['CPM_ATR_REKENING'] : $rek;
		}
		$rek = isset($_REQUEST['TAGIHAN[CPM_AYAT_PAJAK]'])? $_REQUEST['TAGIHAN[CPM_AYAT_PAJAK]'] : $rek;
		$rek = isset($_REQUEST['SKPDKB[CPM_JENIS_PAJAK]'])? $_REQUEST['SKPDKB[CPM_JENIS_PAJAK]'] : $rek;

		return $rek;
    }

    protected function getNamaRekeningPermen() {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 ORDER BY nmrek DESC";
        $result = mysqli_query($this->Conn, $query);
        //$data['CPM_REKENING'] = array();
        $data['ARR_REKENING'] = array();
        while ($d = mysqli_fetch_assoc($result)) {
            //$data['CPM_REKENING'][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'tarif' => $d['tarif1'], 'harga' => $d['tarif2']);
            $data['ARR_REKENING'][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'tarif' => $d['tarif1'], 'harga' => $d['tarif2']);
        }
        return $data;
    }


	public function get_config_terlambat_lap($jns){
		$query = sprintf("SELECT CPM_PERSENTASE as persen, CPM_EDITABLE as editable FROM PATDA_DENDA_TERLAMBAT_LAPOR WHERE CPM_JENIS_PAJAK = '%s' AND CPM_TAHUN = '%s'", $jns, date('Y'));
		$res = mysqli_query($this->Conn, $query);

        $data = (object) array('persen'=>0,'editable'=>0);
		if($data = mysqli_fetch_object($res)){
			$data->editable = $data->editable;
			$data->persen = $data->persen;
		}/* else{
			$data = (object) array('persen'=>0,'editable'=>0);
		} */
		return $data;
	}

	public function get_gw_byid($conn, $id){
		$query = sprintf("SELECT * FROM SIMPATDA_GW WHERE id_switching = '%s'", $id);
		$res = mysqli_query($conn, $query);
		$data = array();
		if($data = mysqli_fetch_object($res)){

		}

		return $data;
	}

	public function get_payment_code($conn, $id = '', $config, $jns){
		$payment_code = '';
		
        if($jns == 1){
            $jns = '02';
        }else if($jns == 2){
            $jns = '06';
        }else if($jns == 3){
            $jns = '04';
        }else if($jns == 4){
            $jns = '08';
        }else if($jns == 5){
            $jns = '09';
        }else if($jns == 6){
            $jns = '07';
        }else if($jns == 7){
            $jns = '01';
        }else if($jns == 8){
            $jns = '05';
        }else{
            $jns = '10';
        }

		if(!empty($id)){
			$query = sprintf("SELECT payment_code FROM SIMPATDA_GW WHERE id_switching = '%s'", $id);
			$res = mysqli_query($conn, $query);
			if($data = mysqli_fetch_assoc($res)){
				$payment_code = $data['payment_code'];
			}
		}else{
            $year = date('y');
            $garis = '-';
            $kode_prefix = '0';
            $search_code = $garis.$jns;
			// $length = isset($config['PATDA_PAYMENT_CODE_LENGTH'])? $config['PATDA_PAYMENT_CODE_LENGTH'] : 2;
			$length = 6;
			$query = "SELECT MAX(SUBSTRING(payment_code,1, {$length} )) nomor FROM SIMPATDA_GW WHERE DATE_FORMAT(saved_date, '%y') = '{$year}' AND PAYMENT_CODE LIKE '%{$search_code}%'";
			$res = mysqli_query($conn, $query);

			$nomor = 1;
			if($data = mysqli_fetch_assoc($res)){
				$nomor = $data['nomor'] + 1;
            }
            
            

			$payment_code = str_pad($nomor, $length, '0', STR_PAD_LEFT).$garis.$jns;
        }
        

		return $payment_code;
    }

    private function get_op_reklame($id){
        $res = mysqli_query($this->Conn, "SELECT * from PATDA_REKLAME_PROFIL WHERE CPM_ID='{$id}'");
        if($data = mysqli_fetch_assoc($res)){
            return $data;
        }
        return;
    }

    protected function save_gateway($jns, $arr_config) {

		$simpatda_rek = $this->get_norek_on_save_gateway();
		$dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $day = $arr_config['TENGGAT_WAKTU'];
        $area_code = $arr_config['KODE_AREA'];
        // echo $dbHost;
        // echo $dbPwd;
        // exit();

        $KODE_PAJAK = $this->idpajak_sw_to_gw[$jns];
        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

        $payment_code = $this->get_payment_code($Conn_gw,'',$arr_config, $jns);
        $this->CPM_TOTAL_PAJAK = ceil(str_replace(",", "", $this->CPM_TOTAL_PAJAK));

        $dbLimit = "DATE_FORMAT(saved_date,'%Y-%m-15')";
        $bulan_pajak = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);

        $ms1 = $this->CPM_MASA_PAJAK1;
        $ms2 = $this->CPM_MASA_PAJAK2;
        $periode = "000000{$this->CPM_TAHUN_PAJAK}{$bulan_pajak}";

        if ($jns == 7) { #reklame semuanya non reguler
            $this->CPM_TIPE_PAJAK = 2;
        }

        if ($this->CPM_TIPE_PAJAK == 2) {
            $dbLimit = "DATE_ADD(DATE(saved_date), INTERVAL 1 MONTH)";
            $bulan_pajak = "00";

            #$non_reguler = array(1 => "AIR", 2 => "HIB", 3 => "HTL", 4 => "GAL", 5 => "PKR", 6 => "LIS", 7 => "REK", 8 => "RES", 9 => "WLT");
            $KODE_PAJAK = $this->non_reguler[$jns];
            #$periode = substr($ms1, 8, 2) . "" . substr($ms1, 3, 2) . "" . substr($ms1, 0, 2) . "" . substr($ms2, 8, 2) . "" . substr($ms2, 3, 2) . "" . substr($ms2, 0, 2);
            $periode = substr($this->CPM_NO, -2) . "00" . substr($this->CPM_NO, 0, 8);
        }

        // jatuh tempo self: +20 hari dari masa pajak akhir
        $dbLimit = "DATE_ADD(str_to_date('$ms2', '%d/%m/%Y'), INTERVAL +20 DAY)";

        // jatuh tempo official: +1 bulan dari masa pajak akhir
        if($jns==7 || $jns==1){
            $dbLimit = "DATE_ADD(str_to_date('$ms2', '%d/%m/%Y'), INTERVAL +1 MONTH)";
        }
        $this->EXPIRED_DATE = $dbLimit;

		/**
		 * ARD+-
		 * memperbaiki save tagihan untuk SKPDKB
		 *
		 */
		$this->CPM_SANKSI = 0;

        if (isset($this->IS_SKPDKB)) {
            $this->CPM_TOTAL_PAJAK = ceil(str_replace(",", "", $this->CPM_KURANG_BAYAR));
            $this->CPM_SANKSI = ceil(str_replace(",", "", $this->CPM_DENDA));
        }

		//jika bukan stpd dan skpdkb maka ...
		if(!isset($this->IS_SKPDKB) && !isset($this->IS_STPD)){
			$this->CPM_SANKSI = ceil(str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP));

			if($this->CPM_SANKSI > 0){
				$this->CPM_TOTAL_PAJAK = $this->CPM_TOTAL_PAJAK - $this->CPM_SANKSI;
			}

			if ($jns == 7 || $jns == 27) { #untuk reklame

				if ($this->CPM_DISCOUNT > 0) { #jika ada pengurangan
					$this->CPM_SANKSI = $this->CPM_SANKSI - ($this->CPM_SANKSI * $this->CPM_DISCOUNT / 100);
					$this->CPM_TOTAL_PAJAK = $this->CPM_TOTAL_OMZET - ($this->CPM_TOTAL_OMZET * $this->CPM_DISCOUNT / 100);
				}

			}
        }
		// edited by v
		if(isset($this->CPM_LUAR_DAERAH) && $this->CPM_LUAR_DAERAH == '0'){
			$CPM_KECAMATAN_WP = isset($this->CPM_KECAMATAN_WP)? $this->CPM_KECAMATAN_WP : '';
			$CPM_KELURAHAN_WP = isset($this->CPM_KELURAHAN_WP)? $this->CPM_KELURAHAN_WP : '';
		}else {
			$CPM_KECAMATAN_WP = isset($this->CPM_KECAMATAN_WP1)? $this->CPM_KECAMATAN_WP1 : '';
			$CPM_KELURAHAN_WP = isset($this->CPM_KELURAHAN_WP1)? $this->CPM_KELURAHAN_WP1 : '';
		}

		$CPM_KECAMATAN_OP = isset($this->CPM_KECAMATAN_OP)? $this->CPM_KECAMATAN_OP : '';
        $CPM_KELURAHAN_OP = isset($this->CPM_KELURAHAN_OP)? $this->CPM_KELURAHAN_OP : '';


        if($jns==7 || $jns==27){
            $op = (object) $this->get_op_reklame($_POST['PAJAK_ATR']['CPM_ATR_NOP'][0]);
            $this->CPM_NOP = $op->CPM_NOP;
            $this->CPM_NAMA_OP = $op->CPM_NAMA_OP;
            $this->CPM_ALAMAT_OP = $op->CPM_ALAMAT_OP;
            $CPM_KECAMATAN_OP = $op->CPM_KECAMATAN_OP;
            $CPM_KELURAHAN_OP = $op->CPM_KELURAHAN_OP;
            $simpatda_rek = $simpatda_rek[0];
        }

        $this->CPM_NO_SSPD = $this->CPM_NO;
		$PAYMENT_FLAG = ($this->CPM_TOTAL_PAJAK ==0)? "1" : "0";
        $query = sprintf("INSERT INTO {$dbTable}
                (npwpd,wp_nama,wp_alamat,
                op_nama,op_alamat,simpatda_dibayar,
                sptpd,saved_date,id_switching,
                expired_date,payment_flag,operator,
                simpatda_type,simpatda_tahun_pajak,simpatda_bulan_pajak,
                periode,simpatda_denda,simpatda_keterangan, sspd,
                masa_pajak_awal, masa_pajak_akhir, area_code,
                kecamatan_op, kelurahan_op, payment_code, simpatda_rek, op_nomor,
                kecamatan_wp, kelurahan_wp)
                VALUES
                ('%s','%s','%s',
                 '%s','%s','%s',
                 '%s','%s','%s',
                  %s,'%s','%s',
                 '%s','%s','%s',
                 '%s','%s','%s','%s',
                 %s,%s,'%s','%s','%s','%s','%s','%s','%s','%s')", $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP,
                 $this->CPM_ALAMAT_OP, $this->CPM_TOTAL_PAJAK, $this->CPM_NO, date('Y-m-d H:i:s'), $this->CPM_ID,
                 $this->EXPIRED_DATE, $PAYMENT_FLAG, $this->CPM_AUTHOR, $KODE_PAJAK, $this->CPM_TAHUN_PAJAK,
                 $bulan_pajak, $periode, $this->CPM_SANKSI, $this->CPM_KETERANGAN, $this->CPM_NO_SSPD,
                 "STR_TO_DATE('{$ms1}','%d/%m/%Y')", "STR_TO_DATE('{$ms2}','%d/%m/%Y')", $area_code,
                 $CPM_KECAMATAN_OP, $CPM_KELURAHAN_OP, $payment_code, $simpatda_rek, $this->CPM_NOP,
                 $CPM_KECAMATAN_WP, $CPM_KELURAHAN_WP);

        $res = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
        mysqli_close($Conn_gw);

        return $res;
    }

    protected function save_berkas_masuk($jns_pajak, $jns_berkas) {
        $CPM_ID = c_uuid();
        $CPM_STATUS = 0;
        $CPM_LAMPIRAN = "";
        $CPM_TGL_INPUT = "NOW()";
        $this->CPM_NAMA_OP = $this->CPM_NAMA_OP;

		if(isset($this->CPM_MASA_PAJAK1)){
			$ms1 = $this->CPM_MASA_PAJAK1;
			$ms2 = $this->CPM_MASA_PAJAK2;
			$MASA_PAJAK = substr($ms1, 8, 2) . "" . substr($ms1, 3, 2) . "" . substr($ms1, 0, 2) . "" . substr($ms2, 8, 2) . "" . substr($ms2, 3, 2) . "" . substr($ms2, 0, 2);
		}else{
			$masa_pajak = $this->CPM_MASA_PAJAK;
			$tahun_pajak = $this->CPM_TAHUN_PAJAK;
			$bln = str_pad($masa_pajak, 2, 0, STR_PAD_LEFT);

			$MASA_PAJAK = substr($tahun_pajak,-2).''.$bln.'01';
			$MASA_PAJAK.= substr($MASA_PAJAK,0,4). date("t",strtotime("{$tahun_pajak}-{$bln}"));

		}

        $this->CPM_NO_SPTPD = isset($this->CPM_NO_SPTPD)? $this->CPM_NO_SPTPD : $this->CPM_NO;
        $this->CPM_NO_SKPDKB = isset($this->CPM_NO_SKPDKB)? $this->CPM_NO_SKPDKB : '';
        $this->CPM_NO_STPD = isset($this->CPM_NO_STPD)? $this->CPM_NO_STPD : '';

        if($jns_pajak == 7){
            $query = "select * from PATDA_REKLAME_PROFIL where CPM_ID = '".$_REQUEST['PAJAK_ATR']['CPM_ATR_NOP'][0]."' ";
            // echo $query;
            $res = mysqli_query($this->Conn, $query);
            $data = mysqli_fetch_array($res);
            // var_dump($data);
            // exit();
            $this->CPM_NAMA_OP = $data['CPM_NAMA_OP'];
            $this->CPM_ALAMAT_OP = $data['CPM_ALAMAT_OP'];
        }

        $query = sprintf("INSERT INTO PATDA_BERKAS
                    (CPM_ID,CPM_TGL_INPUT,CPM_JENIS_PAJAK,CPM_NO_SPTPD,CPM_NPWPD,
                    CPM_NAMA_WP,CPM_ALAMAT_WP, CPM_NAMA_OP,CPM_ALAMAT_OP,CPM_LAMPIRAN, CPM_AUTHOR,
                    CPM_STATUS,CPM_MASA_PAJAK,CPM_TAHUN_PAJAK,CPM_VERSION, CPM_NO_SKPDKB, CPM_NO_STPD, {$jns_berkas})
                    VALUES ( '%s',%s,'%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s', 1)", $CPM_ID, $CPM_TGL_INPUT, $jns_pajak, $this->CPM_NO_SPTPD,
                             $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP,
                             $this->CPM_ALAMAT_OP, $CPM_LAMPIRAN, $this->CPM_AUTHOR, $CPM_STATUS, $MASA_PAJAK, $this->CPM_TAHUN_PAJAK,
                             $this->CPM_VERSION, $this->CPM_NO_SKPDKB, $this->CPM_NO_STPD);
        // echo "<pre>";
        // var_dump($_REQUEST);
        // echo $query;
        // echo $jns_pajak;
        // echo $_REQUEST['PAJAK_ATR']['CPM_ATR_NOP'][0];
        // exit();
        return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    }

    public function get_config_value($id, $key = "") {
        $where = ($key == "") ? "" : "AND CTR_AC_KEY = '{$key}'";
        $query = "SELECT * FROM CENTRAL_APP_CONFIG WHERE CTR_AC_AID = '{$id}' {$where}";
        $res = mysqli_query($this->Conn, $query);

        $VALUE = "";
        if ($key == "") {
            $VALUE = array();
            while ($data = mysqli_fetch_assoc($res)) {
                $VALUE[$data['CTR_AC_KEY']] = $data['CTR_AC_VALUE'];
            }
        } else {
            $data = mysqli_fetch_assoc($res);
            $VALUE = $data['CTR_AC_VALUE'];
        }
        return $VALUE;
    }

    protected function update_counter($id, $key = "") {
        $query = "UPDATE CENTRAL_APP_CONFIG SET CTR_AC_VALUE=CTR_AC_VALUE+1 WHERE CTR_AC_AID = '{$id}' AND CTR_AC_KEY = '{$key}'";
        return mysqli_query($this->Conn, $query);
    }

    public function search_npwpd() {

        if ($this->CPM_JENIS_PAJAK == 1) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_LOKASI_AIRBAWAHTANAH" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_AIRBAWAHTANAH_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 2) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_GOL_HIBURAN_LAIN" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_HIBURAN_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['CPM_DEVICE_ID_ORI'] = $data['CPM_DEVICE_ID'];
                $data['CPM_DEVICE_ID'] = base64_encode($data['CPM_DEVICE_ID']);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 3) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "result" => 0, "CPM_DEVICE_ID" => "", "CPM_NOP" => "");

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_HOTEL_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['CPM_DEVICE_ID_ORI'] = $data['CPM_DEVICE_ID'];
                $data['CPM_DEVICE_ID'] = base64_encode($data['CPM_DEVICE_ID']);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 4) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_MINERAL_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 5) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "",
                "CPM_RODA2_TARIF" => 0, "CPM_RODA4_TARIF" => 0, "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_PARKIR_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['CPM_DEVICE_ID_ORI'] = $data['CPM_DEVICE_ID'];
                $data['CPM_DEVICE_ID'] = base64_encode($data['CPM_DEVICE_ID']);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 6) {
            #inisialisasi data kosong

            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_ASAL_TENAGA" => "", "CPM_GOL_TARIF" => "", "CPM_VOLTASE" => "", "CPM_DAYA" => "", "CPM_TARIF_KWH" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_JALAN_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 7) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_TGL_UPDATE" => "", "CPM_NOP" => "", "CPM_AKTIF" => "",
                "CPM_APPROVE" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_REKLAME_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 8) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_NOP" => "", "result" => 0, "CPM_DEVICE_ID" => "");

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_RESTORAN_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['CPM_DEVICE_ID_ORI'] = $data['CPM_DEVICE_ID'];
                $data['CPM_DEVICE_ID'] = base64_encode($data['CPM_DEVICE_ID']);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 9) {
            #inisialisasi data kosong
            $data = array("CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_NOP" => "", "result" => 0);

            #query untuk mengambil data profil
            $query = "SELECT * FROM PATDA_WALET_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            $result = mysqli_query($this->Conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $dataProfil = mysqli_fetch_assoc($result);
                $data = array_merge($data, $dataProfil);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        }
    }

    protected function get_petugas_identity() {
        $query = "SELECT * FROM PATDA_PETUGAS WHERE CPM_USER = '{$this->CPM_PETUGAS}'";
        $result = mysqli_query($this->Conn, $query);
        return mysqli_fetch_assoc($result);
    }

    public function __desctruct() {
        unset($this->Conn);
        unset($this->Data);
        unset($this->Message);
        unset($this->Json);
    }

    private function getError($msg){
        $respon['amount'] = 0;
        $respon['link'] = '';
        $respon['formated_amount'] = $msg;
        $respon['error'] = 1;
        echo $this->Json->encode($respon);
    }

    public function get_val_tapbox() {
        global $DIR;
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        #print_r($arr_config);
        $id = base64_decode($_POST['id']);
        $arr_id = explode(";", $id);
        $id = implode("','", $arr_id);

        $Conn_gw = @mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName) or die($this->getError("Error Koneksi Data Pembanding"));
        //mysql_select_db($dbName, $Conn_gw);

        $this->CPM_MASA_PAJAK = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);

        $query = "select sum(REPLACE(TransactionAmount, ',', '')) as TOTAL
                 from TRANSACTION WHERE
                 NPWPD = '{$this->CPM_NPWPD}' and
                 NOP = '{$this->CPM_NOP}' and
                 DATE_FORMAT(TransactionDate,'%Y')='{$this->CPM_TAHUN_PAJAK}' and
                 DATE_FORMAT(TransactionDate,'%m')='{$this->CPM_MASA_PAJAK}'";
        $result = mysqli_query($Conn_gw, $query) or die($this->getError(mysqli_error($Conn_gw)));

        $respon = array();
        $respon['amount'] = 0;
        $respon['q'] = $query;
        if ($data = mysqli_fetch_assoc($result)) {

            $row['CPM_DEVICE_ID'] = str_replace("','", ";", $id);
            $row['CPM_NPWPD'] = $this->CPM_NPWPD;
            $row['CPM_NOP'] = $this->CPM_NOP;
            $row['MASA_PAJAK'] = $this->CPM_MASA_PAJAK;
            $row['TAHUN_PAJAK'] = $this->CPM_TAHUN_PAJAK;
            $row['AMOUNT'] = $data['TOTAL'];

            $json = base64_encode($this->Json->encode($row));
            $q = base64_encode("{'a':'{$this->_a}', 'm':'{$this->_m}','u':'{$_SESSION['uname']}','i':'6','url':'function/{$DIR}/svc-download-tapbox.xls.php'}");
            $link = "<a href='javascript:void(0)' onclick=\"javascript:getDetTranTapbox('{$q}','{$json}')\">Lihat detail</a>";

            $respon['link'] = ($data['TOTAL'] == 0) ? "data pembanding tidak tersedia." : $link;
            $respon['amount'] = empty($data['TOTAL'])? 0 : $data['TOTAL'];
            $respon['formated_amount'] = empty($data['TOTAL'])? 0 : number_format($data['TOTAL'],2);
        }
        mysqli_close($Conn_gw);
        echo $this->Json->encode($respon);
    }

    public function save_ket_tapbox(){
		global $DIR;
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = @mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName) or die($this->getError("Error Koneksi Data Pembanding"));
        //mysql_select_db($dbName, $Conn_gw);

        $ket = preg_replace("/[^A-Za-z0-9 ]/", '', $_POST['ket']);
        $query = sprintf("UPDATE TRANSACTION SET NotAdmitReason = '%s' WHERE TransactionNumber = '%s'", $ket, $_POST['id']);
        return mysqli_query($Conn_gw, $query);

	}

    private function Intg2Str($iNumber) {
        $sBuf = "";
        switch ($iNumber) {
            case 0 : $sBuf = "nol";
                break;
            case 1 : $sBuf = "satu";
                break;
            case 2 : $sBuf = "dua";
                break;
            case 3 : $sBuf = "tiga";
                break;
            case 4 : $sBuf = "empat";
                break;
            case 5 : $sBuf = "lima";
                break;
            case 6 : $sBuf = "enam";
                break;
            case 7 : $sBuf = "tujuh";
                break;
            case 8 : $sBuf = "delapan";
                break;
            case 9 : $sBuf = "sembilan";
                break;
            case 10 : $sBuf = "sepuluh";
                break;
            case 11 : $sBuf = "sebelas";
                break;
            case 12 : $sBuf = "dua belas";
                break;
            case 13 : $sBuf = "tiga belas";
                break;
            case 14 : $sBuf = "empat belas";
                break;
            case 15 : $sBuf = "lima belas";
                break;
            case 16 : $sBuf = "enam belas";
                break;
            case 17 : $sBuf = "tujuh belas";
                break;
            case 18 : $sBuf = "delapan belas";
                break;
            case 19 : $sBuf = "sembilan belas";
                break;
        }

        return $sBuf;
    }

// end of Intg2Str

    private function SayTens($iNumber) {
        $sBuf = '';

        $iResult = intval($iNumber / 10);
        if ($iNumber >= 20) {
            $sBuf .= sprintf("%s puluh", $this->Intg2Str($iResult));
            $iNumber %= 10;

            if (($iNumber >= 1) && ($iNumber <= 9))
                $sBuf .= sprintf(" %s", $this->Intg2Str($iNumber));
        }
        else if (($iNumber >= 0) && ($iNumber <= 19))
            $sBuf .= $this->Intg2Str($iNumber);

        return trim($sBuf);
    }

// end of SayTens

    private function SayHundreds($iNumber) {
        $sBuf = '';
        $iResult = 0;

        $iResult = intval($iNumber / 100);
        if (($iResult > 0) && ($iResult != 1))
            $sBuf .= sprintf("%s ratus ", $this->Intg2Str($iResult));
        else if ($iResult == 1)
            $sBuf = "seratus ";
        $iNumber %= 100;

        if ($iNumber > 0)
            $sBuf .= $this->SayTens($iNumber);

        return trim($sBuf);
    }

// end of SayHundreds

    public function SayInIndonesian($number) {
        $arrNumber = explode(".", $number);
        $iNumber = $arrNumber[0];
        $iResult = 0;
        $sBuf = '';

        if ($iNumber == 0)
            $sBuf = 'nol';
        else {
            // handling large number > 2 milyar
            $sBufL = '';
            $sNumber = strval($iNumber);
            $nNumberLen = strlen($sNumber);
            if ($nNumberLen > 9) { // large number
                $sNewNumber = substr($sNumber, $nNumberLen - 9, 9);
                //echo "sNewNumber [$sNewNumber]\n";
                $iNumber = intval($sNewNumber);
                //echo "iNumber [$iNumber]\n";
                // trilyun
                $iLargeNumber = intval(substr($sNumber, 0, $nNumberLen - 9));
                //echo "iLargeNumber [$iLargeNumber]\n";
                $iResult = intval($iLargeNumber / 1000);
                //echo "iResult [$iResult]\n";
                if ($iResult > 0)
                    $sBufL = sprintf("%s trilyun ", $this->SayHundreds($iResult));

                // milyar
                $iLargeNumber %= 1000;
                $iResult = $iLargeNumber;
                if ($iResult > 0)
                    $sBufL .= sprintf("%s milyar ", $this->SayHundreds($iResult));
            }
            //echo "[$sBufL]\n";
            // miliar
            $iResult = intval($iNumber / 1000000000);
            if ($iResult > 0)
                $sBuf .= sprintf("%s miliar ", $this->SayHundreds($iResult));
            $iNumber %= 1000000000;
            // juta
            $iResult = intval($iNumber / 1000000);
            if ($iResult > 0)
                $sBuf .= sprintf("%s juta ", $this->SayHundreds($iResult));
            $iNumber %= 1000000;
            // ribu
            $iResult = intval($iNumber / 1000);
            if (($iResult > 0) && ($iResult != 1))
                $sBuf .= sprintf("%s ribu ", $this->SayHundreds($iResult));
            else if ($iResult >= 1)
                $sBuf .= "seribu ";
            $iNumber %= 1000;
            // ratus
            if ($iNumber > 0)
                $sBuf .= $this->SayHundreds($iNumber);

            // final
            //echo "[$sBufL] [$sBuf]\n";
            $sBuf = $sBufL . $sBuf;
        }

        $angka = trim($sBuf);
        $koma = trim($this->comma($number));
        return "{$angka} {$koma}";
    }

// end of SayInIndonesian

    private function comma($number) {
        $after_comma = stristr($number, '.');
        $arr_number = array(
            "nol",
            "satu",
            "dua",
            "tiga",
            "empat",
            "lima",
            "enam",
            "tujuh",
            "delapan",
            "sembilan");

        $results = "";
        $length = strlen($after_comma);
        $i = 1;
        while ($i < $length) {
            $get = substr($after_comma, $i, 1);
            $results .= " " . $arr_number[$get];
            $i++;
        }
        $results = trim($results);
        return ($i != 1 && $after_comma != "00") ? $results = " koma {$results}" : "";
    }

    protected function getRekening($kdrek='') {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek like '{$kdrek}%'";
        $result = mysqli_query($this->Conn, $query);
        //$data['CPM_REKENING'] = array();
        $data['ARR_REKENING'] = array();
        while ($d = mysqli_fetch_assoc($result)) {
            //$data['CPM_REKENING'][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'tarif' => $d['tarif1'], 'harga' => $d['tarif2']);
            $data['ARR_REKENING'][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'tarif' => $d['tarif1'], 'harga' => $d['tarif2']);
        }
        return $data;
    }

    function download_tapbox_xls() {
		$limit = 2000;
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName,$Conn_gw);

        $where = "NOP = '{$_REQUEST['CPM_NOP']}' ";
        $where.= "AND NPWPD = '{$_REQUEST['CPM_NPWPD']}' ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
        $where.= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
        $where.= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";


		if(isset($_REQUEST['count'])){
			$query = "select
			COUNT(*) AS RecordCount
					from {$dbTable}
					WHERE {$where}";
			#echo $query;exit;
				$result = mysqli_query($Conn_gw, $query);
					$data = mysqli_fetch_assoc($result);
			$arr['total_row'] = $data['RecordCount'];
			$arr['limit'] = $limit;
			echo $this->Json->encode($arr);exit;
		}

		$p = $_REQUEST['page'];
		$total = $limit;
        if ($p == 'all') {
            $offset = 0;
        } else {
            $offset = ($p-1) * $total;
        }

		$query = "select
					DeviceId,
					NotAdmitReason,
					NPWPD as CPM_NPWPD,
					NOP as CPM_NOP,
					TransactionNumber,
					TransactionDate,
					REPLACE(REPLACE(TransactionAmount,'.',''),',','') as total,
					REPLACE(REPLACE(TaxAmount,'.',''),',','') as total_tax
					from {$dbTable}
					WHERE {$where} ORDER BY TransactionDate ASC LIMIT {$offset}, {$total}";
		$res = mysqli_query($Conn_gw, $query);
		#echo mysql_num_rows($res);exit;

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'NPWPD')
                ->setCellValue('C1', 'NOP')
                ->setCellValue('D1', 'Nomor Transaksi')
                ->setCellValue('E1', 'Tanggal Transaksi')
                ->setCellValue('F1', 'Total Transaksi')
                ->setCellValue('G1', 'Total Pajak')
                ->setCellValue('H1', 'Alasan Tidak diakui');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);
		$no = $offset+1;
        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
			$rowData['total'] = (int) str_replace(",","",$rowData['total']);
			$rowData['total_tax'] = (int) str_replace(",","",$rowData['total_tax']);
			$rowData['TransactionNumber'] = preg_replace("/[^A-Za-z0-9]/","",$rowData['TransactionNumber']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NOP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['TransactionNumber'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['TransactionDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['total']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['total_tax']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['NotAdmitReason']);
            $row++;
			$no++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Transaksi Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "H"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }

		header('Content-Type: application/vnd.ms-excel');
        if ($p != 'all')
            header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
        else
            header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');

        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    public function laporan_resume($p) {

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);
        $devid = explode(";", $p->CPM_DEVICE_ID);
        $deviceId = "'" . implode("','", $devid) . "'";
        $where = "DeviceId in ({$deviceId}) ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$p->TAHUN_PAJAK}\" ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$p->MASA_PAJAK}\" ";

        $query = "select count(TransactionNumber) as jumlah,
                        sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
                        from {$dbTable}
                        WHERE {$where}";

        $result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
        $data = mysqli_fetch_assoc($result);
        return array("total" => $data['total'], "jumlah" => $data['jumlah']);
    }

    function download_laporan_tran_tapbox_xls() {
        $limit = 2000;

        if (isset($_REQUEST['count'])) {
            $arr['total_row'] = 31;
            $arr['limit'] = $limit;
            echo $this->Json->encode($arr);
            exit;
        }

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);
        $devid = explode(";", $_REQUEST['alldevice']);
        $deviceId = "'" . implode("','", $devid) . "'";
        $where = "DeviceId in ({$deviceId}) ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";

        $query = "select
                        DATE_FORMAT(TransactionDate,'%d-%m-%Y') as TransactionDate,
                        count(TransactionNumber) as jumlah,
                        sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
                        from {$dbTable}
                        WHERE {$where} GROUP BY DATE_FORMAT(TransactionDate,'%d-%m-%Y')";
        $res = mysqli_query($Conn_gw, $query);


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $_REQUEST['CPM_DEVICE_ID'] = $_REQUEST['alldevice'];
        $p = $this->Json->encode($_REQUEST);
        $p = $this->Json->decode($p);


        $resume = $this->laporan_resume($p);
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B1', 'SISTEM INFORMASI')
                ->setCellValue('B2', 'PAJAK ONLINE DAERAH PALEMBANG')
                ->setCellValue('B4', 'LAPORAN TRASAKSI KENA PAJAK')
                ->setCellValue('A6', 'WAJIB PAJAK')->setCellValue('B6', ": {$_REQUEST['CPM_NPWPD']}")
                ->setCellValue('A7', 'PERIODE')->setCellValue('B7', ": " . (isset($this->arr_bulan[(int) $_REQUEST['MASA_PAJAK']]) ? $this->arr_bulan[(int) $_REQUEST['MASA_PAJAK']] : "") . " {$_REQUEST['TAHUN_PAJAK']}")
                ->setCellValue('A8', 'TOTAL TRX')->setCellValue('B8', ": ".number_format($resume['jumlah']))
                ->setCellValue('A9', 'TOTAL OMSET')->setCellValue('B9', ": Rp. ".number_format($resume['total']))
                ->setCellValue('A10', 'TANGGAL CETAK')->setCellValue('B10', ": " . date("d-m-Y"));


        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A12', 'Tanggal')
                ->setCellValue('B12', 'Jumlah Transaksi')
                ->setCellValue('C12', 'Total Omset');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 13;
        $sumRows = mysqli_num_rows($res);
        while ($rowData = mysqli_fetch_assoc($res)) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $rowData['TransactionDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, number_format($rowData['jumlah']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, number_format($rowData['total']));
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Laporan Transaksi Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:C12')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A6:C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A6:C10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:C10')->getFill()->getStartColor()->setRGB('E4E4E4');

        $objPHPExcel->getActiveSheet()->getStyle('A12:C12')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A12:C12')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "C"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

	public function humanTiming ($time){
		if($time=='') return '--';
		$time = strtotime($time);
		$time = time() - $time; // to get the time since that moment
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);

		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return '<br/>('.$numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').' ago)';
		}
	}

	public function print_skpd($type) {

		$arr_jenis = array(
			1 => "AIR BAWAH TANAH",
			2 => "HIBURAN",
			3 => "HOTEL",
			4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
			5 => "PARKIR",
			6 => "PENERANGAN JALAN",
			7 => "REKLAME",
			8 => "RESTORAN",
			9 => "SARANG WALET");
		$JENIS_PAJAK = $arr_jenis[$type];
		$NO_REKENING = "";
		$NAMA_REKENING = "";

		global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();
        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
		$JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
		$NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
		$NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
		$JALAN = $config['ALAMAT_JALAN'];
		$KOTA = $config['ALAMAT_KOTA'];
		$PROVINSI = $config['ALAMAT_PROVINSI'];
		$KODE_POS = $config['ALAMAT_KODE_POS'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $NIP = $config['BAG_VERIFIKASI_NIP'];

        $KODE_REK = "";
		if($type == 4) $KODE_REK = $DATA['pajak_atr']["CPM_ATR_NAMA"];
		elseif($type == 7) $KODE_REK = $DATA['pajak_atr']["CPM_ATR_REKENING"];
		else $KODE_REK = $DATA['profil']["CPM_REKENING"];

		$NM_REK = $DATA['pajak']['ARR_REKENING'][$KODE_REK]['nmrek'];
        $html = "<table width=\"710\" border=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"220\"><p><strong>".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN). "<br/>
					".strtoupper($NAMA_PENGELOLA)."<br/>
					{$JALAN}<br/>
					{$KOTA} - {$PROVINSI} {$KODE_POS}</strong></p></td>
					<td width=\"330\" align=\"center\"><p><strong>SURAT KETETAPAN PAJAK DAERAH<br/>PAJAK {$JENIS_PAJAK}</strong></p>
					  <table width=\"310\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" align=\"left\">
						<tr>
						  <td width=\"100\">MASA PAJAK</td>
						  <td width=\"190\">: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
						</tr>
						<tr>
						  <td>TAHUN</td>
						  <td>: {$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
						</tr>
					</table></td>
					<td width=\"140\" colspan=\"2\" align=\"center\"><strong>NOMOR SKPD<br/>{$DATA['pajak']['CPM_NO']}
					</strong></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><table width=\"90%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
					  <tr>
						<td width=\"248\">NAMA</td>
						<td width=\"990\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
						</tr>
					  <tr>
						<td>NAMA PEMILIK</td>
						<td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
						</tr>
					  <tr>
						<td>ALAMAT</td>
						<td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
						</tr>
					  <tr>
						<td>NPWPD</td>
						<td>: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
						</tr>
					  <tr>
						<td>TANGGAL JATUH TEMPO</td>
						<td>: {$DATA['pajak']['CPM_TGL_JATUH_TEMPO']}</td>
						</tr>
					</table></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><table width=\"100%\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
					  <tr>
						<td width=\"5%\" align=\"center\"><strong>NO</strong></td>
						<td width=\"20%\" align=\"center\"><strong>REKENING</strong></td>
						<td width=\"60%\" align=\"center\"><strong>URAIAN</strong></td>
						<td width=\"15%\" align=\"center\"><strong>JUMLAH</strong></td>
					  </tr>
					  <tr>
						<td align=\"right\">1.</td>
						<td align=\"center\">{$KODE_REK}</td>
						<td>{$NM_REK}<br/>\n
						Periode : {$DATA['pajak']['CPM_MASA_PAJAK1']} s/d {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
						<td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
					  </tr>
					  <tr>
						<td align=\"right\">2.</td>
						<td align=\"center\"></td>
						<td>Denda Keterlambatan Pelaporan</td>
						<td align=\"right\">" . number_format(0, 2) . "</td>
                      </tr>
					  <tr>
						<td colspan=\"3\" align=\"center\">Jumlah Ketetapan Pokok Pajak</td>
						<td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
						</tr>
					</table></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><i>" . ucwords($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_OMZET'])) . " Rupiah</i></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><table width=\"100%\" border=\"0\">
					  <tr>
						<td colspan=\"2\"><strong><u>P E R H A T I A N</u></strong></td>
					  </tr>
					  <tr>
						<td width=\"4%\" align=\"right\">1.</td>
						<td width=\"96%\">Harapan penyetoran dilakukan pada Bendahara ".ucwords(strtolower($NAMA_PENGELOLA))." / Bank Sumsel Cab. ".ucwords(strtolower($NAMA_PEMERINTAHAN))." dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
					  </tr>
					  <tr>
						<td align=\"right\">2.</td>
						<td>Apabila SKPD tidak atau kurang dibayar setelah tengat waktu paling lama 30 hari setelah SKPD ini diterima akan dikenakan sanksi administrasi berupa bunga sebesar 2 %</td>
					  </tr>
					</table></td>
				  </tr>
				  <tr>
				  <td  colspan=\"4\" align=\"right\"><table border=\"0\" width=\"100%\"><tr><td width=\"50%\"></td><td><table width=\"299\" border=\"0\">
					<tr>
					  <td width=\"289\" align=\"center\">Palembang, " . $DATA['pajak']['CPM_TGL_LAPOR'] . "<br/>a.n KEPALA ".strtoupper($NAMA_PENGELOLA)."<br/>".strtoupper($NAMA_PEMERINTAHAN)."<br/>
						KABID PENDATAAN DAN PENETAPAN</td>
					</tr>
					<tr>
					  <td><p>&nbsp;</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p></td>
					</tr>
					<tr>
					  <td align=\"center\"><strong><u>{$BAG_VERIFIKASI_NAMA}</u></strong><br/>
						PENATA TK.I<br/>NIP.{$NIP}</td>
					</tr>
				  </table></td></tr></table></td>
				  </tr>
				</table>";

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 14, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        // $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-reklame.pdf', 'I');
    }

    function download_pajak_xls() {
		error_reporting(0);
        $where = "(";
        $where.= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where.= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where.= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where.= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where.= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where.= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where.= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";

        $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";

        $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);

        #query select list data
        $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_OP, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE {$where}
                        ORDER BY 1";
                        // echo $query;exit();

        // echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'Tanggal Lapor')
                ->setCellValue('C1', 'Nomor Laporan')
                ->setCellValue('D1', 'NPWPD')
                ->setCellValue('E1', 'Nama')
                ->setCellValue('F1', 'Tahun Pajak')
                ->setCellValue('G1', 'Masa Pajak')
                ->setCellValue('H1', 'Total Pajak')
                ->setCellValue('J1', 'Versi Dokumen')
                ->setCellValue('K1', 'User Input')
                ->setCellValue('I1', 'Objek Pajak');

        if ($this->_s == 0) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', 'Status'); #"CPM_TRAN_STATUS
        }

        if ($this->_s == 4) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1', 'Keterangan'); #CPM_TRAN_INFO
        }


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_TGL_LAPOR']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_MASA_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_VERSION']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_AUTHOR']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_NAMA_OP']);

            if ($this->_s == 0) {
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
            }

            if ($this->_s == 4) {
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TRAN_INFO']);
            }
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "M"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        // header output to CSV
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->save('php://output');
    }

    function download_pajak_status_xls() {
        if ($this->_s == "skpdkb") {
            $this->download_pajak_status_skpdkb_xls();
        } else if ($this->_s == "stpd") {
            $this->download_pajak_status_stpd_xls();
        } else {
            $this->download_pajak_status_sptpd_xls();
        }
    }

    function download_pajak_status_sptpd_xls() {

        $PAJAK = strtoupper($this->arr_idpajak[$this->_i]);

        $where = "(";
        $where.= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR ";
        $where.= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')";
        $where.= ") ";

        $where.= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        $sql = "SELECT * FROM CSCMOD_TAX_MOD_TYPE";
        $res = mysqli_query($this->Conn, $sql);

        while ($row = mysqli_fetch_assoc($res)) {
            $arrFunction[$row["CSM_TAX_MOD_ID"]] = "fPatda{$this->SUFIKS}Pelayanan" . $row["CSM_TAX_MOD_ID"];
        }

        #query select list data
        $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            pj.CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG
                            FROM PATDA_{$PAJAK}_DOC{$this->SUFIKS} pj INNER JOIN PATDA_{$PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID
                            WHERE {$where}
                            ORDER BY pj.CPM_TGL_LAPOR DESC";


        #echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'Tanggal Lapor')
                ->setCellValue('C1', 'Nomor Laporan')
                ->setCellValue('D1', 'NPWPD')
                ->setCellValue('E1', 'Tahun Pajak')
                ->setCellValue('F1', 'Masa Pajak')
                ->setCellValue('G1', 'Total Pajak')
                ->setCellValue('H1', 'Versi Dokumen')
                ->setCellValue('I1', 'User Input')
                ->setCellValue('J1', 'Status Dokumen');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_TGL_LAPOR']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_TAHUN_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_MASA_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_VERSION']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_AUTHOR']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "L"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    function download_pajak_status_skpdkb_xls() {

        $where = "(";
        $where.= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR ";
        $where.= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        $where.= ") ";

        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND s.CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

        #query select list data
        $query = "SELECT * FROM {$this->PATDA_SKPDKB} s INNER JOIN {$this->PATDA_SKPDKB_TRANMAIN} tr ON
                  s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID WHERE {$where} ORDER BY 1";


        #echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'Jenis Pajak')
                ->setCellValue('C1', 'No SKPDKB/T')
                ->setCellValue('D1', 'Nomor SPTPD')
                ->setCellValue('E1', 'Masa Pajak')
                ->setCellValue('F1', 'Tahun Pajak')
                ->setCellValue('G1', 'NPWPD')
                ->setCellValue('H1', 'Kurang Bayar')
                ->setCellValue('I1', 'Jenis')
                ->setCellValue('J1', 'Versi Dokumen')
                ->setCellValue('K1', 'Status Dokumen');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_SKPDKB'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NO_SPTPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $this->arr_bulan[(int) $rowData['CPM_MASA_PAJAK']]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_KURANG_BAYAR']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->arr_tambahan[$rowData['CPM_TAMBAHAN']]);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_VERSION']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "L"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    function download_pajak_status_stpd_xls() {

        $where = "(";
        $where.= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (2,3,4,5)) OR ";
        $where.= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        $where.= ") ";

        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND s.CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

        #query select list data
        $query = "SELECT * FROM {$this->PATDA_STPD} s INNER JOIN {$this->PATDA_STPD_TRANMAIN} tr ON
                  s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where} ORDER BY 1";


        #echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'Jenis Pajak')
                ->setCellValue('C1', 'Nomor SPTPD')
                ->setCellValue('D1', 'Masa Pajak')
                ->setCellValue('E1', 'Tahun Pajak')
                ->setCellValue('F1', 'NPWPD')
                ->setCellValue('G1', 'Total Tagihan')
                ->setCellValue('H1', 'Versi Dokumen')
                ->setCellValue('I1', 'Status Dokumen');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_STPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $this->arr_bulan[(int) $rowData['CPM_MASA_PAJAK']]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_TAHUN_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_VERSION']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "L"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    protected function getTanggalPenetapan($id_pajak, $id){
		$pjk = strtoupper($this->arr_idpajak[$id_pajak]);
		$query = "SELECT CPM_TRAN_DATE FROM PATDA_{$pjk}_DOC_TRANMAIN WHERE CPM_TRAN_{$pjk}_ID = '{$id}' AND CPM_TRAN_STATUS='5'";
		$result = mysqli_query($this->Conn, $query);
		$tgl = "";
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$tgl = $row['CPM_TRAN_DATE'];
		}
		return $tgl;
	}

	public function formatDateForDokumen($dmY){
		return intval(substr($dmY,0,2))." ".$this->arr_bulan[(int)substr($dmY,3,2)]." ".substr($dmY,6,4);
	}


	public function print_sspd() {
        global $sRootPath;
        require_once("{$sRootPath}inc/payment/sayit.php");

        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();
		
		if(empty($DATA['pajak']['CPM_ID'])){
			die('error : id tidak tersedia di sw..');
		}
        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
		$NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KODE_AREA = $config['KODE_AREA'];

		$TGL_PENETAPAN = $this->getTanggalPenetapan($this->id_pajak, $this->CPM_ID);

        $BULAN_PAJAK = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
        $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$BULAN_PAJAK}";
		$KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
            $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
            $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);
        }
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);

		$BANK = $config['BANK'];
		$BANK_ALAMAT = $config['BANK_ALAMAT'];
		$BANK_NOREK = $config['BANK_NOREK'];

		$BENDAHARA_NAMA = $config['BENDAHARA_NAMA'];
		$BENDAHARA_NIP  = $config['BENDAHARA_NIP'];


		//get payment code
		$dbName = $config['PATDA_DBNAME'];
        $dbHost = $config['PATDA_HOSTPORT'];
        $dbPwd = $config['PATDA_PASSWORD'];
        $dbTable = $config['PATDA_TABLE'];
        $dbUser = $config['PATDA_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

		$gw = $this->get_gw_byid($Conn_gw, $this->CPM_ID);

        $PAYMENT_CODE_BANK = $gw->periode;
		$PAYMENT_CODE = $gw->payment_code;
        $DENDA = !empty($gw->patda_denda)? $gw->patda_denda : 0;

        $row_keterangan = '';
        if($this->id_pajak==8){
            $row_keterangan = "<tr>
                                    <td>Keterangan</td>
                                    <td colspan=\"3\">: {$DATA['pajak']['CPM_KETERANGAN']}</td>
                                </tr>";
        }

        $bulan1 = '-';
        $bulan2 = '-';
        $bulan3 = '-';
        $bulan4 = '-';
        $bulan5 = '-';
        $bulan6 = '-';
        $bulan7 = '-';
        $bulan8 = '-';
        $bulan9 = '-';
        $bulan10 = '-';
        $bulan11 = '-';
        $bulan12 = '-';
        $months = array(1,2,3,4,5,6,7,8,9,10,11,12);

        $begin = DateTime::createFromFormat('d/m/Y',$this->CPM_MASA_PAJAK1);
        $end = DateTime::createFromFormat('d/m/Y',$this->CPM_MASA_PAJAK2);
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $m = (int) $dt->format("m");
                    $months[$m-1];
                    if($months[$m-1] == 1){
                        $bulan1 = 'Jan';
                    }
                    if($months[$m-1] == 2){
                        $bulan2 = 'Feb';
                    }
                    if($months[$m-1] == 3){
                        $bulan3 = 'Mar';
                    }
                    if($months[$m-1] == 4){
                        $bulan4 = 'Apr';
                    }
                    if($months[$m-1] == 5){
                        $bulan5 = 'Mei';
                    }
                    if($months[$m-1] == 6){
                        $bulan6 = 'Jun';
                    }
                    if($months[$m-1] == 7){
                        $bulan7 = 'Jul';
                    }
                    if($months[$m-1] == 8){
                        $bulan8 = 'Agu';
                    }
                    if($months[$m-1] == 9){
                        $bulan9 = 'Sep';
                    }
                    if($months[$m-1] == 10){
                        $bulan10 = 'Okt';
                    }
                    if($months[$m-1] == 11){
                        $bulan11 = 'Nov';
                    }
                    if($months[$m-1] == 12){
                        $bulan12 = 'Des';
                    }
        }

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"270\" align=\"center\">
                                        ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        <br /><br />
                                        <br /><br />
                                        <br /><br />
                                        ".strtoupper($NAMA_PENGELOLA)."
                                    </th>
                                    <th width=\"310\" align=\"center\" style=\"font-size:45px\" \>
                                        SURAT SETORAN PAJAK DAERAH<br/>
                                        <h1>(SSPD)</h1><br/>
                                    </th>
                                    <th width=\"130\" align=\"center\" style=\"font-size:42px\"><br /><br />
                                        <br /><br />
                                    <b>KODE BAYAR <br/>
                                    {$PAYMENT_CODE}<br/></b>
                                </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"960\" border=\"0\" cellpadding=\"5\">
                        <tr>
                            <td width=\"230\">NPWPD</td>
                            <td>: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
                        </tr>
                                <tr>
                                    <td width=\"230\">Nama PKP</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Nama OP</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Jenis Usaha</td>
                                    <td>: ".$this->arr_pajak[$this->id_pajak]." </td>
                                </tr>
                                <tr>
                                    <td width=\"230\">KOHIR</td>
                                    <td>: {$this->CPM_NOP} </td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Alamat</td>
                                    <td>: Kel. ".$this->CPM_KELURAHAN_WP." <br>  Kec. ".$this->CPM_KECAMATAN_WP."</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                <tr>
                    <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                            <tr>
                                <td width=\"230\" align=\"center\">Mata Anggaran</td>
                                <td rowspan=\"2\" align=\"center\">
                                <br>Untuk Pembayaran Pajak ".$this->arr_pajak[$this->id_pajak]." <br> ".$this->CPM_KETERANGAN."</td>
                            </tr>
                            <tr>
                                <td width=\"230\" align=\"center\">kosong</td>
                            </tr>
                        </table>
                    </td>
                </tr>


            <tr>
                <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                        <tr>
                            <td width=\"480\">
                            <table>
                                <tr>
                                <td>
                                Setoran [__] &nbsp; Massa [__] &nbsp; Tahunan/Final [__]  &nbsp; STPD [__] &nbsp; SKPD [__]
                                    </td>
                                </tr>
                            </table>
                            </td>
                            <td width=\"230\" align=\"center\">Tahun</td>
                        </tr>
                        <tr align=\"center\">
                            <td width=\"40\">{$bulan1}</td>
                            <td width=\"40\">{$bulan2}</td>
                            <td width=\"40\">{$bulan3}</td>
                            <td width=\"40\">{$bulan4}</td>
                            <td width=\"40\">{$bulan5}</td>
                            <td width=\"40\">{$bulan6}</td>
                            <td width=\"40\">{$bulan7}</td>
                            <td width=\"40\">{$bulan8}</td>
                            <td width=\"40\">{$bulan9}</td>
                            <td width=\"40\">{$bulan10}</td>
                            <td width=\"40\">{$bulan11}</td>
                            <td width=\"40\">{$bulan12}</td>
                            <td width=\"230\">{$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                        </tr>
                    </table>
                </td>
            </tr>


            <tr>
            <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                    <tr>
                        <td width=\"710\">Nomor Ketetapan : {$DATA['pajak']['CPM_NO']}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                    <tr>
                        <td width=\"710\">Diisi Sesuai Nomor Ketetapan : STPD/SKPD</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                    <tr>
                        <td width=\"310\">Rp. " . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                        <td width=\"400\">Terbilang :<br> <p align=\"center\">{$this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
        <td><table width=\"960\" border=\"1\" cellpadding=\"5\">
                <tr>

                <td width=\"710\" align=\"center\">
                Penyetor<br/>
                {$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                <br/><br/><br/><br/><br/>
                (" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>
                </td>
                </tr>
            </table>
        </td>
        </tr>


                </table>";

        ob_clean();

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 14, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 33, 27, 17, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd.pdf', 'I');
    }

	public function get_type_masa(){
		return array(
			1=>'Tahun',
		//	2=>'Semester',
			3=>'Triwulan',
			4=>'Bulan',
			5=>'Minggu',
			6=>'Hari',
		);
	}

	public function get_list_rekening(){
		$query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek LIKE '4.1.01.09%'";
		$res = mysqli_query($this->Conn, $query);
		$rek = array();
		while($d = mysqli_fetch_object($res)){
			$rek[] = $d;
		}
		return $rek;
	}

    public function get_kawasan(){
        $query = "SELECT CPM_ID, CPM_NAMA_FUNGSI FROM PATDA_REKLAME_NSPR WHERE CPM_INDEX_NILAI = 'Fungsi Ruang' ORDER BY CPM_ID ";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while($row = mysqli_fetch_object($res)){
            $list[] = $row->CPM_NAMA_FUNGSI;
        }

        return $list;
    }

    public function get_jalan(){
        $query = "SELECT CPM_ID, CPM_NAMA_FUNGSI FROM PATDA_REKLAME_NSPR WHERE CPM_INDEX_NILAI = 'Fungsi Jalan' ORDER BY CPM_ID ";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while($row = mysqli_fetch_object($res)){
            $list[] = $row->CPM_NAMA_FUNGSI;
        }

        return $list;
    }


    public function get_sudut_pandang(){
        $query = "SELECT CPM_ID, CPM_NAMA_FUNGSI FROM PATDA_REKLAME_NSPR WHERE CPM_INDEX_NILAI = 'Sudut Pandang' ORDER BY CPM_ID DESC";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while($row = mysqli_fetch_object($res)){
            $list[] = $row->CPM_NAMA_FUNGSI;
        }

        return $list;
    }

	public static function formatNPWPD($str){
		$npwpd = '';

		switch(strlen($str)){
			case '15' :
				$npwpd = substr($str,0,2).'.';
				$npwpd.= substr($str,2,9).'.';
				$npwpd.= substr($str,11,2).'.';
				$npwpd.= substr($str,13,2);
			break;
			case '13' :
				$npwpd = substr($str,0,2).'.';
				$npwpd.= substr($str,2,7).'.';
				$npwpd.= substr($str,9,2).'.';
				$npwpd.= substr($str,11,2);
			break;
			default:
				$npwpd = $str;
			break;

		}

		return $npwpd;
    }

    public static function formatNOP($str){
		if(strlen($str)==11){
            $nop = substr($str, 0,2).'.'.substr($str, 2,9);
        }else{
            $nop = $str;
        }

		return $nop;
	}

	public function get_list_npwpd(){
		$NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
		$JNS_PAJAK = array_search(strtolower($_REQUEST['TBLJNSPJK']),$this->arr_idpajak);

		#TAMBAH INFORMASI OP PADA PENCARIAN
		// $query = sprintf("SELECT * FROM PATDA_WP WHERE
            // (CPM_NPWPD LIKE '%s' OR CPM_NAMA_WP like '%s') AND
            // CPM_JENIS_PAJAK LIKE '%s' LIMIT 0,10",
            // $NPWPD.'%',
            // $NPWPD.'%',
            // '%'.$JNS_PAJAK.'%');
            $query = sprintf("
      			SELECT
      			WP.CPM_NPWPD,WP.CPM_NAMA_WP,group_concat(OP.CPM_NAMA_OP) CPM_NAMA_OP
      			FROM PATDA_WP WP
      			LEFT JOIN PATDA_{$_REQUEST['TBLJNSPJK']}_PROFIL OP on WP.CPM_NPWPD=OP.CPM_NPWPD
      			WHERE
                  (WP.CPM_NPWPD LIKE '%s' OR WP.CPM_NAMA_WP like '%s' OR OP.CPM_NAMA_OP like '%s' OR OP.CPM_NOP like '%s') AND
                  WP.CPM_JENIS_PAJAK LIKE '%s'
      			group by WP.CPM_NPWPD,WP.CPM_NAMA_WP
      			",
                  '%'. $NPWPD.'%',
                  '%'. $NPWPD.'%',
                  '%'. $NPWPD.'%',
                  '%'.$NPWPD.'%',
                  '%'.$JNS_PAJAK.'%');
		$res = mysqli_query($this->Conn, $query);

		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list['items'][] = array('id'=> $this->formatNPWPD($row->CPM_NPWPD),'text'=>$row->CPM_NAMA_WP.'<br><b>'.$row->CPM_NAMA_OP.'</b>');
		}
		if(count($list) == 0){$list['items'][] = array('id'=>' ','text'=>'NPWPD tidak ditemukan');}
		echo $this->Json->encode($list);
	}

	public function getWP(){
		$NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
		$TBL = $_REQUEST['TBLJNSPJK'];

		$TRUCK_ID = ($TBL == 'MINERAL')? 'B.CPM_TRUCK_ID,' : '';
		$REKLAME = ($TBL == 'REKLAME')? 'B.CPM_REKLAME' : "''";
		$query = sprintf("SELECT
		A.CPM_NPWPD,
		A.CPM_NAMA_WP,
		A.CPM_ALAMAT_WP,
        A.CPM_KELURAHAN_WP,
        A.CPM_KECAMATAN_WP,
        {$TRUCK_ID}
		B.CPM_ID,
		B.CPM_NAMA_OP,
		B.CPM_ALAMAT_OP,
		B.CPM_KELURAHAN_OP,
		B.CPM_KECAMATAN_OP,
		B.CPM_NOP,
		{$REKLAME} as CPM_GOL,
		0 AS CPM_TARIF,
		0 AS CPM_HARGA,
		-- KEC.CPM_KECAMATAN CPM_KEC_NAMA_WP,KEL.CPM_KELURAHAN CPM_KEL_NAMA_WP,
		KEC_OP.CPM_KECAMATAN as CPM_KEC_NAMA_OP,
        KEL_OP.CPM_KELURAHAN as CPM_KEL_NAMA_OP
		FROM PATDA_WP A
		LEFT JOIN PATDA_{$TBL}_PROFIL B ON A.CPM_NPWPD = B.CPM_NPWPD AND B.CPM_AKTIF='1'
		-- LEFT JOIN PATDA_MST_KECAMATAN KEC ON B.CPM_KECAMATAN_WP=KEC.CPM_KEC_ID
		-- LEFT JOIN PATDA_MST_KELURAHAN KEL on B.CPM_KELURAHAN_WP=KEL.CPM_KEL_ID
		LEFT JOIN PATDA_MST_KECAMATAN KEC_OP ON B.CPM_KECAMATAN_OP=KEC_OP.CPM_KEC_ID
		LEFT JOIN PATDA_MST_KELURAHAN KEL_OP on B.CPM_KELURAHAN_OP=KEL_OP.CPM_KEL_ID
		WHERE A.CPM_NPWPD = '%s'",$NPWPD);
		// echo $query;
		$res = mysqli_query($this->Conn, $query);

		$data = array();
		if($row = mysqli_fetch_assoc($res)){
			$data = $row;

			if(!empty($data['CPM_GOL'])){
				$query = "SELECT tarif1 as tarif,tarif2 as harga FROM PATDA_REK_PERMEN13 WHERE kdrek = '{$data['CPM_GOL']}'";
				$res = mysqli_query($this->Conn, $query);
				if($permen = mysqli_fetch_assoc($res)){
					$data['CPM_TARIF'] = $permen['tarif'];
					$data['CPM_HARGA'] = $permen['harga'];
				}
			}

			if(isset($data['CPM_TRUCK_ID'])){
				$data['CPM_TRUCK_ID'] = base64_encode($data['CPM_TRUCK_ID']);
			}
		}
		echo $this->Json->encode($data);
	}

	public function get_list_kecamatan(){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "pesawaran2@24";
		$conn = mysqli_connect($servername, $username, $password, $database);
		$query = "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN";
		$res = mysqli_query($conn, $query);

		$list = array();
		while($row = mysqli_fetch_object($res)){
			// $list[] = $row;
			$list[$row->CPM_KEC_ID] = $row;
		}

		return $list;
	}

	public function get_list_kelurahan($id = '', $param = ''){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "Lamsel2@21";
		$conn = mysqli_connect($servername, $username, $password, $database);
		
		$KEC = !empty($id)? $id : (isset($_REQUEST['CPM_KEC_ID'])? $_REQUEST['CPM_KEC_ID'] : '');

		$query = "SELECT * FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID like '{$KEC}%'";
		$res = mysqli_query($conn, $query) or die (mysqli_error($this->Conn));

		if ($param == 'BERKAS') {
			if(empty($id)){
				$list = '';
				while($row = mysqli_fetch_assoc($res)){
					$list .= '<option value="'.$row['CPM_KEL_ID'].'">'.$row['CPM_KELURAHAN'].'</option>';
				}
				return $list;
			}else{
				$list = array();
				while($row = mysqli_fetch_object($res)){
					$list [$row->CPM_KEL_ID]= $row;
				}
				return $list;
			}
		}elseif ($param == 'LIST') {
			$list = array();
			while($row = mysqli_fetch_object($res)){
				$list [$row->CPM_KEL_ID]= $row;
			}
			return $list;
		}else{
			if(empty($id)){
				$list = '';
				while($row = mysqli_fetch_assoc($res)){
					$list .= '<option value="'.$row['CPM_KEL_ID'].'">'.$row['CPM_KELURAHAN'].'</option>';
				}
				echo $list;
			}else{
				$list = array();
				while($row = mysqli_fetch_object($res)){
					$list [$row->CPM_KEL_ID]= $row;
				}
				echo $list;
			}
		}

    }

    public function get_nama_kecamatan($id){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "Lamsel2@21";
		$conn = mysqli_connect($servername, $username, $password, $database);
    
        $query = "SELECT CPM_KECAMATAN FROM PATDA_MST_KECAMATAN WHERE CPM_KEC_ID = '{$id}' ";
        $res = mysqli_query($conn, $query);

        $list = "";
        if($row = mysqli_fetch_object($res)){
            $list = $row->CPM_KECAMATAN;
        }

        return $list;
    }

    public function get_nama_kelurahan($id){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "Lamsel2@21";
		$conn = mysqli_connect($servername, $username, $password, $database);
    
        $query = "SELECT CPM_KELURAHAN FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID = '{$id}' ";
        $res = mysqli_query($conn, $query);

        $list = "";
        if($row = mysqli_fetch_object($res)){
            $list = $row->CPM_KELURAHAN;
        }

        return $list;
    }
	
	
	//tambahan
	public function get_id_kecamatan_real($id){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "Lamsel2@21";
		$conn = mysqli_connect($servername, $username, $password, $database);
		
        $query = "SELECT CPM_KECAMATAN_REAL FROM PATDA_MST_KECAMATAN WHERE CPM_KEC_ID = '{$id}' ";
        $res = mysqli_query($conn, $query);

        $list = "";
        if($row = mysqli_fetch_object($res)){
            $list = $row->CPM_KECAMATAN_REAL;
        }

        return $list;
    }

    public function get_id_kelurahan_real($id){
		$servername = "localhost";
		$database = "9pajak";
		$username = "root";
		$password = "Lamsel2@21";
		$conn = mysqli_connect($servername, $username, $password, $database);
		
        $query = "SELECT CPM_KELURAHAN_REAL FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID = '{$id}' ";
        $res = mysqli_query($conn, $query);

        $list = "";
        if($row = mysqli_fetch_object($res)){
            $list = $row->CPM_KELURAHAN_REAL;
        }

        return $list;
    }
	//end
	
	

	public function get_list_angkutan(){
		$query = "SELECT * FROM PATDA_MINERAL_AUDITTRAIL_ANGKUTAN order by CPM_TRUCK_ID";
		$res = mysqli_query($this->Conn, $query);

		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list[] = $row;
		}

		return $list;
	}

	public function get_list_jenis_kamar(){
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];

		$conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysql_select_db($dbName, $conn);

		$query = "SELECT * FROM HOTEL_ATR_JENIS_KAMAR order by Jenis";
		$res = mysqli_query($this->Conn, $query, $conn);

		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list[$row->Kode] = $row;
		}

		return $list;
	}

	public function get_field_array($result){
		$fields = array();

		if(!$fields = mysqli_fetch_assoc($result)){
			while ($f=mysqli_fetch_field($result)){
				$fields[$f->name] = '';
			}
		}

		return $fields;
	}

	public function get_bank_payment(){
		$data = array();
		$dbName = $this->get_config_value('aPatda','PATDA_DBNAME');
        $dbHost = $this->get_config_value('aPatda','PATDA_HOSTPORT');
        $dbPwd = $this->get_config_value('aPatda','PATDA_PASSWORD');
        $dbUser = $this->get_config_value('aPatda','PATDA_USERNAME');

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

		$sql = "select CDC_B_ID,CDC_B_NAME from CDCCORE_BANK";
		$qry = mysqli_query($Conn_gw, $sql);
		while($bank = mysqli_fetch_object($qry)){
			$data[$bank->CDC_B_ID] = $bank;
		}

		return $data;
    }


    public function jenis_rekening(){
        $query = "SELECT nmheader3, kdrek FROM PATDA_REK_PERMEN13 where nmheader3 != 'Reklame' GROUP BY nmheader3   ";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while($row = mysqli_fetch_object($res)){
            $nilai = explode (".",$row->kdrek, -1);
            $list[] ='<option value="'.implode (".",$nilai).'">'.$row->nmheader3.'</option>';
            // $list[] = $row->CPM_ID;
        }

        return $list;
    }

    public function get_no_rek($id = '', $param = ''){
		$KD = !empty($id)? $id : (isset($_REQUEST['CPM_KD_ID'])? $_REQUEST['CPM_KD_ID'] : '');

		$query = "SELECT kdrek,nmheader3, max(kdrek) as total FROM PATDA_REK_PERMEN13 WHERE kdrek like '{$KD}%' ORDER BY kdrek DESC limit 1";
		$res = mysqli_query($this->Conn, $query) or die (mysqli_error($this->Conn));

        $list = array();
        while($row = mysqli_fetch_object($res)){
            $nilai = explode (".",$row->kdrek, -1);
            $nama = $row->nmheader3;
            $n = substr($row->total,-2)+1;
            $list[] = implode (".",$nilai).".".sprintf("%02s", $n);

        }
        if($KD==""){
            $hsl = array("nilai"=>" ", "nama"=>" ");
        }else{
            $hsl = array("nilai"=>$list['0'], "nama"=>$nama);
        }
        echo json_encode($hsl);
	}

    public function get_persen_denda($expired, $today=''){
		if($today=='') $today = date('Y-m-d');
		$date_of_month = date('Y-m-t', strtotime($expired));

		$bulan = 0;
		if(strtotime($today) > strtotime($expired)){
			if($expired != $date_of_month){
				$date1 = new DateTime($expired);
				$date2 = $date1->diff(new DateTime($today));

				if($date2->y > 0){
					$bulan += $date2->y * 12;
				}
				$bulan += $date2->m;
				$bulan += ($date2->d>0)? 1 : 0;
				$bulan = ($bulan == 0)? 1 : $bulan;
			} else {
				$bulan = 0;
				$bulan = (date("Y",strtotime($today)) - date("Y",strtotime($expired))) * 12;
				$bulan += date("m",strtotime($today)) - date("m",strtotime($expired));
			}
		}

		$persen = ($bulan * 2);
		$persen = ($persen > 48)? 48 : $persen;
		return $persen;

    }

    protected function getDataRekening() {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 order by kdrek";
        $result = mysqli_query($this->Conn, $query);
        //$data['CPM_REKENING'] = array();
        $data = array();
        while ($d = mysqli_fetch_assoc($result)) {
            $data[substr($d['kdrek'],0,9)][$d['kdrek']] = array('kdrek'=>$d['kdrek'], 'nmrek' => $d['nmrek'], 'nmheader3' => $d['nmheader3']);
            // $data[$d['kdrek']] = array('nmrek' => $d['nmrek'], 'nmheader3' => $d['nmheader3']);
        }
        return $data;
    }
}

?>
