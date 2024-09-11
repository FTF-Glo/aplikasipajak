<?php
if (session_id() == '') {
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
// ini_set("error_log", "/tmp/patda-base-v2-error.log");

//DEFINE('BASE_URL', 'http://192.168.26.112/9pajak/kabkupang/');
$bURL = explode("?", isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
DEFINE('BASE_URL', $bURL[0]);

class Pajak
{

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
    protected $CPM_ATR_MASA_PAJAK1;
    protected $CPM_ATR_MASA_PAJAK2;

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
    protected $PATDA_JALAN_DOC_ATR;
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
    public $arr_triwulan = array(1 => "Triwulan I", 2 => "Triwulan II", 3 => "Triwulan III", 4 => "Triwulan IV");
    public $arr_kdpajak = array(1 => "AIR", 2 => "HIB", 3 => "HTL", 4 => "GAL", 5 => "PKR", 6 => "PPJ", 7 => "REK", 8 => "RES", 9 => "WLT");
    public $arr_pajak = array(
        1 => "Air Bawah Tanah", 2 => "Hiburan", 3 => "Hotel", 4 => "Mineral Non Logam dan Batuan", 5 => "Parkir",
        6 => "Penerangan Jalan", 7 => "Reklame", 8 => "Restoran", 9 => "Sarang Walet"
    );
    public $arr_idpajak = array(
        1 => "airbawahtanah", 2 => "hiburan", 3 => "hotel", 4 => "mineral", 5 => "parkir",
        6 => "jalan", 7 => "reklame", 8 => "restoran", 9 => "walet"
    );
    public $arr_bulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");
    public $arr_bulann = array('01' => "Januari", '02' => "Februari", '03' => "Maret", '04' => "April", '05' => "Mei", '06' => "Juni", '07' => "Juli", '08' => "Agustus", '09' => "September", '10' => "Oktober", '11' => "November", '12' => "Desember");
    public $arr_status = array(1 => "Draft", 2 => "Menunggu Verifikasi", 3 => "Menunggu Persetujuan", 4 => "Ditolak", 5 => "Disetujui", 6 => "Dibayar");
    public $arr_jenis = array(0 => "Pelaporan", 1 => "Piutang");
    public $arr_kurangbayar = array(0 => "SKPDKB", 1 => "SKPDKBT");
    public $arr_role = array(
        "rmPatdaAdmin" => "Admin",
        "rmPatdaPelayanan" => "Pelayanan",
        "rmPatdaVerifikasi" => "Verifikasi SubBid I",
        "rm2Verifikasi2" => "Verifikasi SubBid II",
        "rmPatdaPenetapan" => "Penetapan",
        "rmPatdaPenagihan" => "Penagihan",
        "rmPatdaMonitoring" => "Monitoring"
    );
    public $arr_tambahan = array(0 => "SKPDKB", 1 => "SKPDKBT");
    public $idpajak_sw_to_gw = array(
        1 => 11,
        2 => 6,
        3 => 4,
        4 => 9,
        5 => 10,
        6 => 8,
        7 => 7,
        8 => 5,
        9 => 12
    );
    public $idpajak_gw_to_sw = array(11 => 1, 6 => 2, 4 => 3, 9 => 4, 10 => 5, 8 => 6, 7 => 7, 5 => 8, 12 => 9);
    public $arr_tipe_pajak = array(1 => "Reguler", 2 => "Non Reguler");
    public $arr_tipe_pajak_reklame = array(2 => "Reklame Kain / Reklame Papan / Billboard / Videotron / Megatron");
    public $arr_tipe_pajak_hotel = array(1 => "Hotel", 2 => "Losmen");
    //public $arr_tipe_pajak_restoran = array(1 => "Restoran", 2 => "Jasa Boga / Katering", 3 => "Kafetaria", 4 => "Kantin", 5 => "Warung", 6 =>"Bar", 7 => "Rumah Makan");
    public $arr_tipe_pajak_restoran = array(1 => "Restoran", 2 => "Jasa Boga / Katering / Kafetaria / Kantin / Warung / Bar / Rumah Makan");
    public $non_reguler = array(1 => 31, 2 => 26, 3 => 24, 4 => 29, 5 => 30, 6 => 28, 7 => 27, 8 => 25, 9 => 32);

    // public $arr_tipe_pajak_res_d = array(1 => "Restoran", 2 => "Jasa Boga / Katering", 3 => "Kafetaria", 4 => "Kantin", 5 => "Warung", 6 => "Bar", 7 => "Rumah Makan");

    public $jenis_tipe_pajak_restoran = array(
        1 => 5,
        2 => 25,
        3 => 1,
        4 => 2,
        5 => 3,
        6 => 8,
        7 => 6,
    );
    protected $notif = true;
    public $arr_pajak_tapbox = array("HIBURAN" => "Hiburan", "HOTEL" => "Hotel", "PARKIR" => "Parkir", "RESTORAN" => "Restoran");

    function __construct()
    {
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
        $this->PATDA_JALAN_DOC_ATR = "PATDA_JALAN_DOC_ATR{$this->SUFIKS}";
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

        if ($this->Conn) $this->set_jenis_pajak();
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
    }

    protected function inisialisasi_masa_pajak()
    {
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
        $res['masa_pajak2'] = date("t", strtotime("{$tahun_pajak}-{$bln}")) . "/{$bln}/{$tahun_pajak}";

        return $res;
    }

    public function set_jenis_pajak()
    {
        $query = "SELECT * FROM PATDA_JENIS_PAJAK ORDER BY CPM_NO";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        while ($d = mysqli_fetch_assoc($res)) {
            if ($d['CPM_TIPE'] <= 12) {
                $this->arr_pajak[$d['CPM_NO']] = $d['CPM_JENIS'];
                $this->arr_pajak_table[$d['CPM_NO']] = $d['CPM_TABLE'];
            }

            $this->arr_pajak_gw[$d['CPM_TIPE']] = $d['CPM_JENIS'];
            $this->arr_pajak_gw_table[$d['CPM_TIPE']] = $d['CPM_TABLE'];
            $this->arr_pajak_gw_no[$d['CPM_TIPE']] = $d['CPM_NO'];
        }
    }

    function getIDBerkas($nosptpd)
	{
		// var_dump('adsas');die;
		global $DBLink;
		$patdaberkas = '';
		$qry = "select * from patda_berkas where CPM_NO_SPTPD = '$nosptpd'";
		$res = mysqli_query($DBLink, $qry);
		while ($row = mysqli_fetch_assoc($res)) {
			$patdaberkas = $row['CPM_ID'];
		}

		return $patdaberkas;
	}


    function getIDdoc($cpmno,$idpjk)
	{
        global $DBLink;
        $pjk = $this->arr_idpajak[$idpjk];
		$id_doc = '';
		$qry = "select * from patda_{$pjk}_doc where CPM_NO = '$cpmno'";
		// var_dump($qry);die;
		$res = mysqli_query($DBLink, $qry);
		while ($row = mysqli_fetch_assoc($res)) {
			$id_doc = $row['CPM_ID'];
		}

		return $id_doc;
	}

    function getIDTranmain($cpmid,$idpjk)
	{
        global $DBLink;
        $pjk = $this->arr_idpajak[$idpjk];
		$id_tranmain = '';
		$qry = "select * from patda_{$pjk}_doc_tranmain where CPM_TRAN_{$pjk}_ID = '$cpmid'";
        // var_dump($qry);die;
		// var_dump($qry);die;
		$res = mysqli_query($DBLink, $qry);
		while ($row = mysqli_fetch_assoc($res)) {
			$id_tranmain = $row['CPM_TRAN_ID'];
		}

		return $id_tranmain;
	}


    public function redirect($url = "")
    {
        $this->base_url = str_replace('main.php', '', $this->base_url);
        $this->base_url = str_replace('registrasi/registrasi.php', '', $this->base_url);
        $url = empty($url) ?
            $this->base_url . 'main.php?param=' . base64_encode("a={$this->_a}&m={$this->_m}") :
            $this->base_url . $url;

        header("location:{$url}");
    }

    private function get_norek_on_save_gateway()
    {
        $pajak = isset($_REQUEST['PAJAK']) ? $_REQUEST['PAJAK'] : null;
        $atr = isset($_REQUEST['PAJAK_ATR']) ? $_REQUEST['PAJAK_ATR'] : null;

        $rek = '';
        $rek = isset($pajak['CPM_REKENING']) ? $pajak['CPM_REKENING'] : $rek;

        if ($atr) {
            if (isset($atr['CPM_ATR_NAMA'])) {
                $_list_rek = array();
                foreach ($atr['CPM_ATR_NAMA'] as $val) {
                    $_list_rek[] = $val;
                }
                if (count($_list_rek) > 0) $rek = implode(';', $_list_rek);
            }

            $rek = isset($atr['CPM_ATR_REKENING']) ? $atr['CPM_ATR_REKENING'] : $rek;
        }
        $rek = isset($_REQUEST['TAGIHAN[CPM_AYAT_PAJAK]']) ? $_REQUEST['TAGIHAN[CPM_AYAT_PAJAK]'] : $rek;
        $rek = isset($_REQUEST['SKPDKB[CPM_JENIS_PAJAK]']) ? $_REQUEST['SKPDKB[CPM_JENIS_PAJAK]'] : $rek;

        return $rek;
    }

    protected function getNamaRekeningPermen()
    {
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

    public function get_pejabat($key = '')
    {
        $table = "PATDA_PEJABAT";
        $entity = array();

        try {
            //if(!$entity = cache_get($table)){

            $query = "SELECT * FROM PATDA_PEJABAT";
            $result = mysqli_query($this->Conn, $query);
            while ($d = mysqli_fetch_assoc($result)) {
                $entity[$d['CPM_KEY']] = $d;
            }
            //cache_set($table, $entity);
            //}

            $entity = (array) $entity;
        } catch (Exception $e) {
            save_log($e->getMessage());
        }

        return empty($key) ? $entity : $entity[$key];
    }


    public function get_pejabat_surat($key = '')
    {
        $entity = array();
        $data = array();
        try {
            $query = "SELECT CTR_AC_KEY, CTR_AC_VALUE FROM central_app_config WHERE CTR_AC_KEY IN ('KEPALA_DINAS_NAMA', 'KEPALA_DINAS_NIP','KEPALA_DINAS_GOLONGAN', 'SEKDA_NAMA', 'SEKDA_NIP', 'SEKDA_GOLONGAN', 'BUPATI_NAMA', 'BUPATI_NIP')";
            $result = mysqli_query($this->Conn, $query);
            while ($d = mysqli_fetch_assoc($result)) {
                $data[$d['CTR_AC_KEY']] = $d['CTR_AC_VALUE'];
            }

            $entity[$data['KEPALA_DINAS_NIP']] = array(
                'Nama' => $data['KEPALA_DINAS_NAMA'],
                'NIP' => $data['KEPALA_DINAS_NIP'],
                'Golongan' => $data['KEPALA_DINAS_GOLONGAN'],
                'Jabatan' => 'KEPALA_DINAS_NIP'
            );

            $entity[$data['SEKDA_NIP']] = array(
                'Nama' => $data['SEKDA_NAMA'],
                'NIP' => $data['SEKDA_NIP'],
                'Golongan' => $data['SEKDA_GOLONGAN'],
                'Jabatan' => 'SEKDA_NIP'
            );

            $entity[$data['BUPATI_NIP']] = array(
                'Nama' => $data['BUPATI_NAMA'],
                'NIP' => $data['BUPATI_NIP'],
                'Jabatan' => 'BUPATI_NIP'
            );
        } catch (Exception $e) {
            save_log($e->getMessage());
        }

        //return empty($key)? $entity : $entity[$key];
        return $entity;
    }


    public function get_config_terlambat_lap($jns)
    {
        $query = sprintf("SELECT CPM_PERSENTASE as persen, CPM_EDITABLE as editable FROM PATDA_DENDA_TERLAMBAT_LAPOR WHERE CPM_JENIS_PAJAK = '%s' AND CPM_TAHUN = '%s'", $jns, date('Y'));
        // var_dump($query);
        // die;
        $res = mysqli_query($this->Conn, $query);

        $data = (object) array('persen' => 0, 'editable' => 0);
        if ($data = mysqli_fetch_object($res)) {
            $data->editable = $data->editable;
            $data->persen = $data->persen;
        }/* else{
			$data = (object) array('persen'=>0,'editable'=>0);
		} */
        return $data;
    }

    public function get_gw_byid($conn, $id)
    {
        // var_dump($conn);exit;
        $query = sprintf("SELECT * FROM SIMPATDA_GW WHERE id_switching = '%s'", $id);
        $res = mysqli_query($conn, $query);
        $data = array();
        if ($data = mysqli_fetch_object($res)) {
        }
        // var_dump($res);exit;
        return $data;
    }

    function check_status($transtatus, $id, $jenis_pajak)
    {
        if ($jenis_pajak == 1) {
            $table_pajak = "PATDA_AIRBAWAHTANAH_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_AIRBAWAHTANAH_ID";
        } elseif ($jenis_pajak == 2) {
            $table_pajak = "PATDA_HIBURAN_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_HIBURAN_ID";
        } elseif ($jenis_pajak == 3) {
            $table_pajak = "PATDA_HOTEL_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_HOTEL_ID";
        } elseif ($jenis_pajak == 4) {
            $table_pajak = "PATDA_MINERAL_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_MINERAL_ID";
        } elseif ($jenis_pajak == 5) {
            $table_pajak = "PATDA_PARKIR_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_PARKIR_ID";
        } elseif ($jenis_pajak == 6) {
            $table_pajak = "PATDA_JALAN_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_JALAN_ID";
        } elseif ($jenis_pajak == 7) {
            $table_pajak = "PATDA_REKLAME_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_REKLAME_ID";
        } elseif ($jenis_pajak == 8) {
            $table_pajak = "PATDA_RESTORAN_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_RESTORAN_ID";
        } else {
            $table_pajak = "PATDA_WALET_DOC_TRANMAIN";
            $field_pajak = "CPM_TRAN_WALET_ID";
        }

        $query_check = "SELECT CPM_TRAN_OPR as operator_input,CPM_TRAN_OPR_DISPENDA as operator_verifikasi, CPM_TRAN_DATE as tanggal_verifikasi FROM {$table_pajak} WHERE {$field_pajak} = '{$id}' AND CPM_TRAN_STATUS = {$transtatus}";
        $res1 = mysqli_query($this->Conn, $query_check);
        //$check_status = mysqli_num_rows($res1);
        $data = array();
        $data = mysqli_fetch_object($res1);

        return $data;
    }

    function check_role($id)
    {

        $query_check = "select b.CTR_RM_ID from central_user a inner join central_user_to_app b ON a.CTR_U_ID = b.CTR_USER_ID where a.CTR_U_UID = '{$id}'";
        $res1 = mysqli_query($this->Conn, $query_check);
        //$check_status = mysqli_num_rows($res1);
        $data = array();
        $data = mysqli_fetch_object($res1);

        return $data->CTR_RM_ID;
    }

    public function get_payment_code($conn, $id = '', $config, $jns)
    {
        $payment_code = '';

        if ($jns == 1) {
            $jns = '10'; //airbawahtanah 10
        } else if ($jns == 2) {
            $jns = '05'; // hiburan 05
        } else if ($jns == 3) {
            $jns = '03'; //hotel 03
        } else if ($jns == 4) {
            $jns = '08'; // mineral 08
        } else if ($jns == 5) {
            $jns = '09'; //parkir 09
        } else if ($jns == 6) {
            $jns = '07'; //jalan 07
        } else if ($jns == 7) {
            $jns = '06'; //reklame 06
        } else if ($jns == 8) {
            $jns = '04'; // restoran 04
        } else {
            $jns = '11'; // wallet 11
        }

        if (!empty($id)) {
            $query = sprintf("SELECT payment_code FROM SIMPATDA_GW WHERE id_switching = '%s'", $id);
            $res = mysqli_query($conn, $query);
            if ($data = mysqli_fetch_assoc($res)) {
                $payment_code = $data['payment_code'];
            }
        } else {
            //kode daerah
            $sql = "SELECT * FROM CENTRAL_APP_CONFIG WHERE CTR_AC_KEY = 'KODE_AREA'";
            $res2 = mysqli_query($this->Conn, $sql);
            while ($row = mysqli_fetch_assoc($res2)) {
                $kode_daerah = $row['CTR_AC_VALUE'];
            }

            $year = date('y');
            //$garis = '-';
            $kode_prefix = '0';
            //$search_code = $garis.$jns;
            $search_code = $jns;
            // $length = isset($config['PATDA_PAYMENT_CODE_LENGTH'])? $config['PATDA_PAYMENT_CODE_LENGTH'] : 2;
            $length = 6;

            $today = date('Y-m-d');
            $pecahkan = explode('-', $today);
            $tah = substr($pecahkan[0], -2);;
            $bul = $pecahkan[1];
            $tahbul = $kode_daerah . $tah . $bul;

            $query = "SELECT MAX(SUBSTRING(payment_code,9, {$length})) nomor FROM simpatda_gw WHERE PAYMENT_CODE LIKE '{$tahbul}%________'";
            $res = mysqli_query($conn, $query);

            $nomor = 1;
            if ($data = mysqli_fetch_assoc($res)) {
                $nomor = $data['nomor'] + 1;
            }

            $payment_code = $tahbul . str_pad($nomor, $length, '0', STR_PAD_LEFT) . $jns;
        }

        //var_dump($payment_code, $nomor, $kode_daerah);die;
        return $payment_code;
    }

    private function get_op_reklame($id)
    {
        $res = mysqli_query($this->Conn, "SELECT * from PATDA_REKLAME_PROFIL WHERE CPM_ID='{$id}'");
        if ($data = mysqli_fetch_assoc($res)) {
            return $data;
        }
        return;
    }

    protected function update_validasi($cpm_id)
    {
        // var_dump($cpm_id);die;
        $query = "UPDATE simpatda_gw SET validasi_pelaporan = 1
                  WHERE id_switching ='{$cpm_id}'";
        return mysqli_query($this->Conn, $query);
    }

    protected function save_gateway($jns, $arr_config)
    {

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

        $payment_code = $this->get_payment_code($Conn_gw, '', $arr_config, $jns);

        // var_dump($this->CPM_TGL_JATUH_TEMPO);
        // die;
        // $tgl_jatuh_tempo = "'" . $this->CPM_TGL_JATUH_TEMPO . "'";

        $this->CPM_TOTAL_PAJAK = ceil(str_replace(",", "", $this->CPM_TOTAL_PAJAK));

        $dbLimit = "DATE_FORMAT(saved_date,'%Y-%m-15')";
        $bulan_pajak = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);

        // ??membuat validasi pajak jalan
        // ubah format tanggal
        // switch (TRUE) {
        //     case ($jns == 6):
        // $atr_masa_pajak1 = DateTime::createFromFormat('d/m/Y', $this->CPM_ATR_MASA_PAJAK1);
        // $ms1_ = $atr_masa_pajak1->format('Y-m-d');

        // $atr_masa_pajak2 = DateTime::createFromFormat('d/m/Y', $this->CPM_ATR_MASA_PAJAK2);
        // $ms2_ = $atr_masa_pajak2->format('Y-m-d');
        //         break;
        //     default:
        //         $this->CPM_MASA_PAJAK1;
        //         $this->CPM_MASA_PAJAK2;
        //         break;
        // };

        // end ubah format tanggal
        // var_dump($this->CPM_NO);die;
        if ($jns == 6) {
            $ms1 = $this->CPM_ATR_MASA_PAJAK1;
            $ms2 = $this->CPM_ATR_MASA_PAJAK2;
        } else {
            $ms1 = $this->CPM_MASA_PAJAK1;
            $ms2 = $this->CPM_MASA_PAJAK2;
        }

        $periode = "000000{$this->CPM_TAHUN_PAJAK}{$bulan_pajak}";

        if ($jns == 7) { #reklame semuanya non reguler
            $this->CPM_TIPE_PAJAK = 2;
        }
        if ($jns == 8) { #reklame semuanya non reguler
            // $this->CPM_TIPE_PAJAK = 2;
            $KODE_PAJAK = $this->jenis_tipe_pajak_restoran[$this->CPM_TIPE_PAJAK];
        }

        if ($this->CPM_TIPE_PAJAK == 2) {
            $dbLimit = "DATE_ADD(DATE(saved_date), INTERVAL 1 MONTH)";
            $bulan_pajak = "00";

            #$non_reguler = array(1 => "AIR", 2 => "HIB", 3 => "HTL", 4 => "GAL", 5 => "PKR", 6 => "LIS", 7 => "REK", 8 => "RES", 9 => "WLT");
            $KODE_PAJAK = $this->non_reguler[$jns];
            #$periode = substr($ms1, 8, 2) . "" . substr($ms1, 3, 2) . "" . substr($ms1, 0, 2) . "" . substr($ms2, 8, 2) . "" . substr($ms2, 3, 2) . "" . substr($ms2, 0, 2);
            $periode = substr($this->CPM_NO, -2) . "00" . substr($this->CPM_NO, 0, 8);
        }
      

        // jatuh tempo official: +1 bulan dari masa pajak akhir
        if ($jns == 7) {
            $dbLimit = "DATE_ADD(str_to_date('$ms2', '%d/%m/%Y'), INTERVAL +0 DAY)";
        }else{
            $dbLimit = "DATE_ADD(str_to_date('$ms2', '%d/%m/%Y'), INTERVAL 10 DAY)";
        }
        // var_dump($this->CPM_TGL_JATUH_TEMPO);die;

        if ( $this->CPM_TGL_JATUH_TEMPO == '' || $this->CPM_TGL_JATUH_TEMPO == NULL) { 
            $this->EXPIRED_DATE = $dbLimit;
        } else{
            $this->EXPIRED_DATE = "'" . $this->CPM_TGL_JATUH_TEMPO . "'";;
        }
        // $this->EXPIRED_DATE = date('Y-m-d', strtotime( date('Y-m-d H:i:s') . ' +1 day' ));

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
        if (!isset($this->IS_SKPDKB) && !isset($this->IS_STPD)) {
            $this->CPM_SANKSI = ceil(str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP));

            if ($this->CPM_SANKSI > 0) {
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
        if (isset($this->CPM_LUAR_DAERAH) && $this->CPM_LUAR_DAERAH == '0') {
            $CPM_KECAMATAN_WP = isset($this->CPM_KECAMATAN_WP) ? $this->CPM_KECAMATAN_WP : '';
            $CPM_KELURAHAN_WP = isset($this->CPM_KELURAHAN_WP) ? $this->CPM_KELURAHAN_WP : '';
        } else {
            $CPM_KECAMATAN_WP = isset($this->CPM_KECAMATAN_WP1) ? $this->CPM_KECAMATAN_WP1 : '';
            $CPM_KELURAHAN_WP = isset($this->CPM_KELURAHAN_WP1) ? $this->CPM_KELURAHAN_WP1 : '';
        }

        $CPM_KECAMATAN_OP = isset($this->CPM_KECAMATAN_OP) ? $this->CPM_KECAMATAN_OP : '';
        $CPM_KELURAHAN_OP = isset($this->CPM_KELURAHAN_OP) ? $this->CPM_KELURAHAN_OP : '';


        if ($jns == 7 || $jns == 27) {
            $op = (object) $this->get_op_reklame($_POST['PAJAK_ATR']['CPM_ATR_NOP'][0]);
            $this->CPM_NOP = $op->CPM_NOP;
            $this->CPM_NAMA_OP = $op->CPM_NAMA_OP;
            $this->CPM_ALAMAT_OP = $op->CPM_ALAMAT_OP;
            $CPM_KECAMATAN_OP = $op->CPM_KECAMATAN_OP;
            $CPM_KELURAHAN_OP = $op->CPM_KELURAHAN_OP;
            $simpatda_rek = $simpatda_rek[0];
        }

        // var_dump( $this->EXPIRED_DATE);die;
        $this->CPM_NO_SSPD = $this->CPM_NO;
        $PAYMENT_FLAG = ($this->CPM_TOTAL_PAJAK == 0) ? "1" : "0";
        $query = sprintf(
            "INSERT INTO {$dbTable}
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
                 %s,%s,'%s','%s','%s','%s','%s','%s','%s','%s')",
            $this->CPM_NPWPD,
            $this->CPM_NAMA_WP,
            $this->CPM_ALAMAT_WP,
            $this->CPM_NAMA_OP,
            $this->CPM_ALAMAT_OP,
            $this->CPM_TOTAL_PAJAK,
            $this->CPM_NO,
            date('Y-m-d H:i:s'),
            $this->CPM_ID,
            $this->EXPIRED_DATE,
            $PAYMENT_FLAG,
            $this->CPM_AUTHOR,
            $KODE_PAJAK,
            $this->CPM_TAHUN_PAJAK,
            $bulan_pajak,
            $periode,
            $this->CPM_SANKSI,
            $this->CPM_KETERANGAN,
            $this->CPM_NO_SSPD,
            "STR_TO_DATE('{$ms1}','%d/%m/%Y')",
            "STR_TO_DATE('{$ms2}','%d/%m/%Y')",
            $area_code,
            $CPM_KECAMATAN_OP,
            $CPM_KELURAHAN_OP,
            $payment_code,
            $simpatda_rek,
            $this->CPM_NOP,
            $CPM_KECAMATAN_WP,
            $CPM_KELURAHAN_WP
        );
        // var_dump($query);
        // die;
        $res = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
        mysqli_close($Conn_gw);

        return $res;
    }

    protected function save_berkas_masuk($jns_pajak, $jns_berkas)
    {
        $CPM_ID = c_uuid();
        $CPM_STATUS = 0;
        $CPM_LAMPIRAN = "";
        $CPM_TGL_INPUT = "NOW()";
        $this->CPM_NAMA_OP = $this->CPM_NAMA_OP;

        if (isset($this->CPM_MASA_PAJAK1)) {
            $ms1 = $this->CPM_MASA_PAJAK1;
            $ms2 = $this->CPM_MASA_PAJAK2;
            $MASA_PAJAK = substr($ms1, 8, 2) . "" . substr($ms1, 3, 2) . "" . substr($ms1, 0, 2) . "" . substr($ms2, 8, 2) . "" . substr($ms2, 3, 2) . "" . substr($ms2, 0, 2);
        } else {
            $masa_pajak = $this->CPM_MASA_PAJAK;
            $tahun_pajak = $this->CPM_TAHUN_PAJAK;
            $bln = str_pad($masa_pajak, 2, 0, STR_PAD_LEFT);

            $MASA_PAJAK = substr($tahun_pajak, -2) . '' . $bln . '01';
            $MASA_PAJAK .= substr($MASA_PAJAK, 0, 4) . date("t", strtotime("{$tahun_pajak}-{$bln}"));
        }

        $this->CPM_NO_SPTPD = isset($this->CPM_NO_SPTPD) ? $this->CPM_NO_SPTPD : $this->CPM_NO;
        $this->CPM_NO_SKPDKB = isset($this->CPM_NO_SKPDKB) ? $this->CPM_NO_SKPDKB : '';
        $this->CPM_NO_STPD = isset($this->CPM_NO_STPD) ? $this->CPM_NO_STPD : '';

        if ($jns_pajak == 7) {
            $query = "select * from PATDA_REKLAME_PROFIL where CPM_ID = '" . $_REQUEST['PAJAK_ATR']['CPM_ATR_NOP'][0] . "' ";
            // echo $query;
            $res = mysqli_query($this->Conn, $query);
            $data = mysqli_fetch_array($res);
            // var_dump($data);
            // exit();
            $this->CPM_NAMA_OP = $data['CPM_NAMA_OP'];
            $this->CPM_ALAMAT_OP = $data['CPM_ALAMAT_OP'];
        }

        $query = sprintf(
            "INSERT INTO PATDA_BERKAS
                    (CPM_ID,CPM_TGL_INPUT,CPM_JENIS_PAJAK,CPM_NO_SPTPD,CPM_NPWPD,
                    CPM_NAMA_WP,CPM_ALAMAT_WP, CPM_NAMA_OP,CPM_ALAMAT_OP,CPM_LAMPIRAN, CPM_AUTHOR,
                    CPM_STATUS,CPM_MASA_PAJAK,CPM_TAHUN_PAJAK,CPM_VERSION, CPM_NO_SKPDKB, CPM_NO_STPD, {$jns_berkas})
                    VALUES ( '%s',%s,'%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s', 1)",
            $CPM_ID,
            $CPM_TGL_INPUT,
            $jns_pajak,
            $this->CPM_NO_SPTPD,
            $this->CPM_NPWPD,
            $this->CPM_NAMA_WP,
            $this->CPM_ALAMAT_WP,
            $this->CPM_NAMA_OP,
            $this->CPM_ALAMAT_OP,
            $CPM_LAMPIRAN,
            $this->CPM_AUTHOR,
            $CPM_STATUS,
            $MASA_PAJAK,
            $this->CPM_TAHUN_PAJAK,
            $this->CPM_VERSION,
            $this->CPM_NO_SKPDKB,
            $this->CPM_NO_STPD
        );

        return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    }

    public function get_config_value($id, $key = "")
    {
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

    protected function update_counter($id, $key = "")
    {
        $query = "UPDATE CENTRAL_APP_CONFIG SET CTR_AC_VALUE=CTR_AC_VALUE+1 WHERE CTR_AC_AID = '{$id}' AND CTR_AC_KEY = '{$key}'";
        return mysqli_query($this->Conn, $query);
    }

    public function search_npwpd()
    {

        if ($this->CPM_JENIS_PAJAK == 1) {
            #inisialisasi data kosong
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_LOKASI_AIRBAWAHTANAH" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_GOL_HIBURAN_LAIN" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "result" => 0, "CPM_DEVICE_ID" => "", "CPM_NOP" => ""
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "",
                "CPM_RODA2_TARIF" => 0, "CPM_RODA4_TARIF" => 0, "CPM_NOP" => "", "result" => 0
            );

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

            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_ASAL_TENAGA" => "", "CPM_GOL_TARIF" => "", "CPM_VOLTASE" => "", "CPM_DAYA" => "", "CPM_TARIF_KWH" => "",
                "CPM_TGL_UPDATE" => "", "CPM_AKTIF" => "", "CPM_APPROVE" => "", "CPM_NOP" => "", "result" => 0
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_TGL_UPDATE" => "", "CPM_NOP" => "", "CPM_AKTIF" => "",
                "CPM_APPROVE" => "", "result" => 0
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_REKENING" => "", "CPM_NOP" => "", "result" => 0, "CPM_DEVICE_ID" => ""
            );

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
            $data = array(
                "CPM_ID" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
                "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_NOP" => "", "result" => 0
            );

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

    protected function get_petugas_identity()
    {
        $query = "SELECT * FROM PATDA_PETUGAS WHERE CPM_USER = '{$this->CPM_PETUGAS}'";
        $result = mysqli_query($this->Conn, $query);
        return mysqli_fetch_assoc($result);
    }

    public function __desctruct()
    {
        unset($this->Conn);
        unset($this->Data);
        unset($this->Message);
        unset($this->Json);
    }

    private function getError($msg)
    {
        $respon['amount'] = 0;
        $respon['link'] = '';
        $respon['formated_amount'] = $msg;
        $respon['error'] = 1;
        echo $this->Json->encode($respon);
    }

    public function get_val_tapbox()
    {
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
            $respon['amount'] = empty($data['TOTAL']) ? 0 : $data['TOTAL'];
            $respon['formated_amount'] = empty($data['TOTAL']) ? 0 : number_format($data['TOTAL'], 2);
        }
        mysqli_close($Conn_gw);
        echo $this->Json->encode($respon);
    }

    public function save_ket_tapbox()
    {
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

    private function Intg2Str($iNumber)
    {
        $sBuf = "";
        switch ($iNumber) {
            case 0:
                $sBuf = "nol";
                break;
            case 1:
                $sBuf = "satu";
                break;
            case 2:
                $sBuf = "dua";
                break;
            case 3:
                $sBuf = "tiga";
                break;
            case 4:
                $sBuf = "empat";
                break;
            case 5:
                $sBuf = "lima";
                break;
            case 6:
                $sBuf = "enam";
                break;
            case 7:
                $sBuf = "tujuh";
                break;
            case 8:
                $sBuf = "delapan";
                break;
            case 9:
                $sBuf = "sembilan";
                break;
            case 10:
                $sBuf = "sepuluh";
                break;
            case 11:
                $sBuf = "sebelas";
                break;
            case 12:
                $sBuf = "dua belas";
                break;
            case 13:
                $sBuf = "tiga belas";
                break;
            case 14:
                $sBuf = "empat belas";
                break;
            case 15:
                $sBuf = "lima belas";
                break;
            case 16:
                $sBuf = "enam belas";
                break;
            case 17:
                $sBuf = "tujuh belas";
                break;
            case 18:
                $sBuf = "delapan belas";
                break;
            case 19:
                $sBuf = "sembilan belas";
                break;
        }

        return $sBuf;
    }

    // end of Intg2Str

    private function SayTens($iNumber)
    {
        $sBuf = '';

        $iResult = intval($iNumber / 10);
        if ($iNumber >= 20) {
            $sBuf .= sprintf("%s puluh", $this->Intg2Str($iResult));
            $iNumber %= 10;

            if (($iNumber >= 1) && ($iNumber <= 9))
                $sBuf .= sprintf(" %s", $this->Intg2Str($iNumber));
        } else if (($iNumber >= 0) && ($iNumber <= 19))
            $sBuf .= $this->Intg2Str($iNumber);

        return trim($sBuf);
    }

    // end of SayTens

    private function SayHundreds($iNumber)
    {
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

    public function SayInIndonesian($number)
    {
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

    private function comma($number)
    {
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
            "sembilan"
        );

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

    protected function getRekening($kdrek = '')
    {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek like '{$kdrek}%'";
        $result = mysqli_query($this->Conn, $query);
        $data['ARR_REKENING'] = array();
        while ($d = mysqli_fetch_assoc($result)) {
            $data['ARR_REKENING'][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'tarif' => $d['tarif1'], 'harga' => $d['tarif2']);
        }
        return $data;
    }

    function download_tapbox_xls()
    {
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
        $where .= "AND NPWPD = '{$_REQUEST['CPM_NPWPD']}' ";
        $where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
        $where .= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
        $where .= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

        $where .= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";


        if (isset($_REQUEST['count'])) {
            $query = "select
			COUNT(*) AS RecordCount
					from {$dbTable}
					WHERE {$where}";
            #echo $query;exit;
            $result = mysqli_query($Conn_gw, $query);
            $data = mysqli_fetch_assoc($result);
            $arr['total_row'] = $data['RecordCount'];
            $arr['limit'] = $limit;
            echo $this->Json->encode($arr);
            exit;
        }

        $p = $_REQUEST['page'];
        $total = $limit;
        if ($p == 'all') {
            $offset = 0;
        } else {
            $offset = ($p - 1) * $total;
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
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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
        $no = $offset + 1;
        while ($rowData = mysqli_fetch_assoc($res)) {
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $rowData['total'] = (int) str_replace(",", "", $rowData['total']);
            $rowData['total_tax'] = (int) str_replace(",", "", $rowData['total_tax']);
            $rowData['TransactionNumber'] = preg_replace("/[^A-Za-z0-9]/", "", $rowData['TransactionNumber']);
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

    public function laporan_resume($p)
    {

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
        $where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$p->TAHUN_PAJAK}\" ";
        $where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$p->MASA_PAJAK}\" ";

        $query = "select count(TransactionNumber) as jumlah,
                        sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
                        from {$dbTable}
                        WHERE {$where}";

        $result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
        $data = mysqli_fetch_assoc($result);
        return array("total" => $data['total'], "jumlah" => $data['jumlah']);
    }

    function download_laporan_tran_tapbox_xls()
    {
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
        $where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";

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
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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
            ->setCellValue('A8', 'TOTAL TRX')->setCellValue('B8', ": " . number_format($resume['jumlah']))
            ->setCellValue('A9', 'TOTAL OMSET')->setCellValue('B9', ": Rp. " . number_format($resume['total']))
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

    public function humanTiming($time)
    {
        if ($time == '') return '--';
        $time = strtotime($time);
        $time = time() - $time; // to get the time since that moment
        $time = ($time < 1) ? 1 : $time;
        $tokens = array(
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
            return '<br/>(' . $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago)';
        }
    }

    public function print_skpd($type)
    {

        $arr_jenis = array(
            1 => "AIR BAWAH TANAH",
            2 => "HIBURAN",
            3 => "HOTEL",
            4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
            5 => "PARKIR",
            6 => "PENERANGAN JALAN",
            7 => "REKLAME",
            8 => "RESTORAN",
            9 => "SARANG WALET"
        );
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
        if ($type == 4) $KODE_REK = $DATA['pajak_atr']["CPM_ATR_NAMA"];
        elseif ($type == 7) $KODE_REK = $DATA['pajak_atr']["CPM_ATR_REKENING"];
        else $KODE_REK = $DATA['profil']["CPM_REKENING"];

        $NM_REK = $DATA['pajak']['ARR_REKENING'][$KODE_REK]['nmrek'];
        $html = "<table width=\"710\" border=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"220\"><p><strong>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
					" . strtoupper($NAMA_PENGELOLA) . "<br/>
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
						<td>: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
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
						<td width=\"96%\">Harapan penyetoran dilakukan pada Bendahara " . ucwords(strtolower($NAMA_PENGELOLA)) . " / Bank Sumsel Cab. " . ucwords(strtolower($NAMA_PEMERINTAHAN)) . " dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
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
					  <td width=\"289\" align=\"center\">Gedong Tataan, " . $DATA['pajak']['CPM_TGL_LAPOR'] . "<br/>a.n KEPALA " . strtoupper($NAMA_PENGELOLA) . "<br/>" . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
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
        $pdf->SetTitle('');
        $pdf->SetSubject('spppd');
        $pdf->SetKeywords('');
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

    function download_bentang_panjang()
    {
        // echo "asd";
        // exit;
        // yang di pakai pertama $this->download_pajak_xls_bentang_panjang();
        //$this->download_pajak_xls_bentang_panjang();
        $this->download_bentang_panjang_ter();
    }
    function download_bentang_panjang_res()
    {
        // echo "asd";
        // exit;
        // yang di pakai pertama $this->download_pajak_xls_bentang_panjang();
        $this->download_pajak_xls_bentang_panjang();
    }
    function download_bentang_panjang_pat()
    {
        // echo "asd";
        // exit;
        // yang di pakai pertama $this->download_pajak_xls_bentang_panjang();
        $this->download_pajak_xls_bentang_panjang_pat();
    }
    function download_bentang_panjangv2()
    {

        $this->download_pajak_xls_bentang_panjangV2();
    }
    function download_bentang_panjang_abt()
    {
        $this->download_pajak_xls_pat_tahunan();
    }
    function where3_cetak_bentang_abt()
    {

        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        // if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        // $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"".(date('Y')-1)."\" " ;
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"" . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) . "\" " : " AND CPM_TAHUN_PAJAK = \"" . (date('Y') - 1) . "\" ";
        // }

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
        }
        return $where;
    }

    //cetak untuk pat dan minerba
    private function download_pajak_xls_pat_tahunan()
    {

        // echo "string";exit();
        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
        }

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

        $query_wp = "select * from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
        // var_dump($query_wp);exit;



        #query select list data
        $query = "SELECT pj.CPM_ID, 
                    pj.CPM_NO, 
                    pj.CPM_TAHUN_PAJAK, 
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                    STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                    pj.CPM_AUTHOR, 
                    pj.CPM_VERSION,
                    pj.CPM_TOTAL_OMZET, 
                    pj.CPM_TARIF_PAJAK, 
                    pj.CPM_TOTAL_PAJAK, 
                    pr.CPM_NPWPD, 
                    pr.CPM_NAMA_WP,
                    pr.CPM_NAMA_OP,
                    pr.CPM_REKENING,
                    pr.CPM_KELURAHAN_OP,
                    pr.CPM_KELURAHAN_OP,
                    pr.CPM_KECAMATAN_WP, 
                    pr.CPM_ALAMAT_OP, 
                    tr.CPM_TRAN_STATUS, 
                    tr.CPM_TRAN_DATE, 
                    tr.CPM_TRAN_INFO, 
                    tr.CPM_TRAN_FLAG, 
                    tr.CPM_TRAN_READ, 
                    tr.CPM_TRAN_ID
                    FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                    WHERE {$where}
                    ORDER BY 1";

        $query2 = "SELECT
                    pj.CPM_TOTAL_PAJAK,
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_WP
                FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where} ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
        // echo "<pre>";var_dump($query2);exit;
        $data = array();
        $res = mysqli_query($this->Conn, $query2);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res)) {
            $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
            );
            // $jumlah_data++;
        }


        $where3 = $this->where3_cetak_bentang_abt();
        $query3 = "SELECT
                    pj.CPM_TOTAL_PAJAK,
                    pj.CPM_TIPE_PAJAK,
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_OP
                    pr.CPM_KECAMATAN_WP
                FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) IN (10,11,12) ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
        $data2 = array();
        $res2 = mysqli_query($this->Conn, $query3);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res2)) {
            $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
            );
        }

        $data_wp = array();
        $res_wp = mysqli_query($this->Conn, $query_wp);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res_wp)) {
            $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
        }

        // echo'<pre>';
        // print_r($data);
        // print_r($data['CPM_NPWPD']);
        // exit;


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("-")
            ->setDescription("bphtb")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        // $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? date('Y') : "Tahun Belum di Pilih" ;
        // $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? date('Y')-1 : "Tahun Belum di Pilih";
        $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
        $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "Triwulan IV " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "Triwulan IV " . (date('Y') - 1);
        // var_dump($tahun_pajak_label_sebelumnya);exit;
        $pajakk = '';
        $SPTPD = '';
        if ($JENIS_PAJAK === 'MINERAL') {
            $pajakk = 'MINERBA';
            $SPTPD = 'SPTPD';
        } elseif ($JENIS_PAJAK === 'AIRBAWAHTANAH') {
            $pajakk = 'PAJAK AIR TANAH';
            $SPTPD = 'SKPD';
        }


        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'REKAPITULASI ' . $SPTPD . '  ' . $pajakk)

            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', 'MASA PAJAK TRIWULAN I s/d TRIWULAN IV ' . $tahun_pajak_label . '')
            ->setCellValue('A6', 'BIDANG PENGEMBANGAN DAN PENETAPAN')

            ->setCellValue('A7', 'NO.')
            ->setCellValue('B7', 'NAMA WAJIB PAJAK.')
            ->setCellValue('C7', 'NAMA WAJIB PAJAK.')
            ->setCellValue('C7', 'PAJAK ' . $JENIS_PAJAK . ' TAHUN ' . $tahun_pajak_label . ' ')
            ->setCellValue('C8', 'TAPBOX.')
            ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
            // ->setCellValue('E8', $tahun_pajak_label_sebelumnya)
            ->setCellValue('E8', 'TRIWULAN I')
            ->setCellValue('F8', 'TRIWULAN II')
            ->setCellValue('G8', 'TRIWULAN III')
            ->setCellValue('H8', 'TRIWULAN IV')
            ->setCellValue('I7', 'JUMLAH.');


        // judul dok

        $objPHPExcel->getActiveSheet()->mergeCells("A1:J1");
        $objPHPExcel->getActiveSheet()->mergeCells("A2:J2");
        $objPHPExcel->getActiveSheet()->mergeCells("A3:J3");
        $objPHPExcel->getActiveSheet()->mergeCells("A4:J4");
        $objPHPExcel->getActiveSheet()->mergeCells("A6:J6");


        $objPHPExcel->getActiveSheet()->mergeCells("A7:A8");
        $objPHPExcel->getActiveSheet()->mergeCells("B7:B8");
        $objPHPExcel->getActiveSheet()->mergeCells("I7:I8");
        $objPHPExcel->getActiveSheet()->mergeCells("C7:H7");


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
        $tab = $jns[$this->_s];
        $jml = 0;

        $row = 9;
        $sumRows = mysqli_num_rows($res);
        $total_pajak = 0;

        foreach ($data_wp as $npwpd => $rowDataWP) {
            $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
            break;
        }
        $jumlah_data = count($data_wp);
        // echo $cek_kecamatan;exit();

        foreach ($data_wp as $npwpd => $rowDataWP) {
            $rowData = $data[$rowDataWP['CPM_NPWPD']];
            $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
            // var_dump($rowData['CPM_NPWPD']);die;
            //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
            //$nama_kecamatan = $cek_kecamatan;

            //var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;
            if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
                // var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;

                // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':B' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $desbelum);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $triwulan_satu);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $triwulan_dua);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $triwulan_tiga);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $triwulan_empat);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $total_pajak);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );

                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    // var_dump($row);die;
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':I' . $space);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                    $row++;
                }


                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                $row++;
            }







            if ($rowDataWP['CPM_KECAMATAN_WP']) {
                //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
                //$nama_kecamatan = $cek_kecamatan;
                //echo $nama_kecamatan;exit;
                if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':I' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    //$cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;


                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
                    //var_dump($row);die;
                    $no = 0;
                }
            }
            $query2 = "select CPM_ID, CPM_NPWPD,UPPER(CPM_NAMA_OP) as CPM_NAMA_OP from PATDA_RESTORAN_PROFIL where CPM_NPWPD='" . $rowData['CPM_NPWPD'] . "' order by CPM_TGL_UPDATE asc";



            $resR = mysqli_query($this->Conn, $query2);
            $row_cek = mysqli_fetch_array($resR);
            // echo "string";
            $history = strtoupper($row_cek['CPM_NAMA_OP']);
            //}

            $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
            // $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


            if ($nama_kecamatan != $nama_kecamatans) {
                $total_pajak = 0;
                $triwulan_satu = 0;
                $triwulan_dua = 0;
                $triwulan_tiga = 0;
                $triwulan_empat = 0;
                $desbelum = 0;
                $totaldesbelum = 0;
            }
            $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
            $triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $desbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

            //untuk total
            $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
            $total_triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $total_triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $total_triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $total_triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

            //var_dump($total_pajak);die;

            $jml++;
            $row++;
            $no++;
            // var_dump($jumlah_data, $row);die;
            if ($jumlah_data == $jml) {
                // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");


                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $desbelum);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $triwulan_satu);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $triwulan_dua);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $triwulan_tiga);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $triwulan_empat);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $total_pajak);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );


                if ($jumlah_data == $jml) {
                    // var_dump($row);die;
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $totaldesbelum);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_triwulan_satu);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_triwulan_dua);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_triwulan_tiga);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_triwulan_empat);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }


                if ($jumlah_data == $jml) {
                    //var_dump($row);die;
                    $space = $row + 3;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN");


                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->getStartColor()->setRGB('ffff00');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }

                // var_dump($space);die;
                $space = $space + 1;
                $no_keterangan = 0;
                $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%'  GROUP BY CPM_KECAMATAN_WP  ORDER BY CPM_KECAMATAN_WP ASC";

                $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                    $totalwp += $row_keterangan['TOTAL'] + 0;

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                    $space++;
                    $no_keterangan++;

                    // CODINGAN UNTUK MENAMPILKAN TOTAL WP DI CETAK BENTANG PANJANG

                    if ($no_keterangan == mysqli_num_rows($res_keterangan)) {
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($space), "TOTAL");
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($space), $totalwp);
                    }
                }
            }
        }




        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getActiveSheet()->getStyle('A1:I4')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5:I7')->getAlignment()->setWrapText(true);

        // border
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );


        // fill tabel header
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getActiveSheet()->getStyle('E8:I' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // // fill tabel footer
        // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);


        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        for ($x = "A"; $x <= "H"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }

    //cetak bentang fix bukan triwulan


    function where3_cetak_bentang()
    {
        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        // if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"" . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) . "\" " : " AND CPM_TAHUN_PAJAK <= \"" . (date('Y')) . "\" ";
        // }

        //$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
        }
        return $where;
    }


    private function download_pajak_xls_bentang_panjang()
    {

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = '';

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
        //}

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);


        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';



        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
        $jenisPajak = $this->arr_tipe_pajak;
        $z = 0;
        foreach ($jenisPajak as $jp => $jp_id) {
            if ($jenis_pajaks != $jp && $jenis_pajaks != '') {
                continue;
            }

            if ($jp == 2) {
                $no = 0;
            }

            if ($jp == 2) {
                $total_total_pajak = 0;
                $total_jan = 0;
                $total_feb = 0;
                $total_mar = 0;
                $total_apr = 0;
                $total_mei = 0;
                $total_jun = 0;
                $total_jul = 0;
                $total_agu = 0;
                $total_sep = 0;
                $total_okt = 0;
                $total_nov = 0;
                $total_des = 0;
            }

            //if(isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != ""){
            //$where .= " AND pj.CPM_TIPE_PAJAK={$_REQUEST['CPM_JENIS_PJK']}";    
            //if($_REQUEST['CPM_JENIS_PJK']==1)
            //	$where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            //elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //	$where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
            //}

            $where3 = $this->where3_cetak_bentang();

            // var_dump($where3);
            // die;
            if ($this->_idp == '7') {
                $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
            } else {
                $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
            }

            //$query_wp = "select * from patda_wp where  CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
            //if($this->_idp == '8'){
            $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
            //}

            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            #query select list data
            $query2 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
                        pr.CPM_KECAMATAN_WP,
						pr.CPM_KECAMATAN_OP
					FROM
						PATDA_{$JENIS_PAJAK}_DOC pj
						INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
						INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
						WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
						GROUP BY CPM_BULAN, pr.CPM_NPWPD
						ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
            // var_dump($query_wp);
            // exit();
            $data = array();
            $res = mysqli_query($this->Conn, $query2);
            while ($row = mysqli_fetch_assoc($res)) {

                $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
                $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
                // var_dump($query2);
                // break;
            }
            // echo $data[$row['CPM_NPWPD']]['CPM_NPWPD'];
            //exit();
            $query3 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
						pr.CPM_KECAMATAN_OP
                FROM
					PATDA_{$JENIS_PAJAK}_DOC pj
					INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) = 12 AND {$q_tipe_pajak} = '{$jp}'
                    GROUP BY CPM_BULAN, pr.CPM_NPWPD
					ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
            // var_dump($query3);
            // die;

            $data2 = array();
            $res2 = mysqli_query($this->Conn, $query3);
            // $jumlah_data;
            while ($row = mysqli_fetch_assoc($res2)) {
                $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
                $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                    'CPM_VOLUME' => $row['CPM_VOLUME'],
                    'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
                );
            }



            $data_wp = array();

            $res_wp = mysqli_query($this->Conn, $query_wp);
            // echo "<pre>";

            while ($row = mysqli_fetch_assoc($res_wp)) {
                $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
            }

            // Set properties
            $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("9 PAJAK ONLINE")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("9 PAJAK ONLINE");

            // Add some data
            $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
            $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);

            $objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
                ->setCellValue('A6', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
                ->setCellValue('A7', 'NO.')
                ->setCellValue('B7', 'NAMA WAJIB PAJAK.')
                ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . ' TAHUN ' . $tahun_pajak_label . ' ')
                ->setCellValue('Q8', 'JUMLAH.')
                ->setCellValue('C8', 'TAPBOX.')
                ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
                ->setCellValue('E8', 'JAN')
                ->setCellValue('F8', 'FEB')
                ->setCellValue('G8', 'MAR')
                ->setCellValue('H8', 'APRIL')
                ->setCellValue('I8', 'MEI')
                ->setCellValue('J8', 'JUNI')
                ->setCellValue('K8', 'JULI')
                ->setCellValue('L8', 'AGS')
                ->setCellValue('M8', 'SEPT')
                ->setCellValue('N8', 'OKT')
                ->setCellValue('O8', 'NOP')
                ->setCellValue('P8', 'DES');
            if ($JENIS_PAJAK == 'RESTORAN') {
                $objPHPExcel->setActiveSheetIndex($z)
                    ->setCellValue('B7', 'NAMA WAJIB OP.');
            }

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
            $objPHPExcel->getActiveSheet()->mergeCells("A6:R6");
            $objPHPExcel->getActiveSheet()->mergeCells("A7:A8");
            $objPHPExcel->getActiveSheet()->mergeCells("B7:B8");
            $objPHPExcel->getActiveSheet()->mergeCells("C7:Q7");


            // Miscellaneous glyphs, UTF-8
            $objPHPExcel->setActiveSheetIndex($z);

            $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
            $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
            $tab = $jns[$this->_s];
            $jml = 0;

            $row = 9;
            $sumRows = mysqli_num_rows($res);
            $total_pajak = 0;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                // var_dump($cek_kecamatan);
                // //break;
                // die;
            }

            $jumlah_data = count($data_wp);
            // print_r($data) . '<br><br>';
            // die;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                //print_r($data) . '<br>';
                // print_r($data[$rowDataWP['CPM_NPWPD']]);
                // die;
                $rowData = $data[$rowDataWP['CPM_NPWPD']];
                $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];

                // print_r($rowData['bulan'][10]['CPM_TOTAL_PAJAK']) . '<br>';
                // print_r($rowDataWP['CPM_KECAMATAN_WP']) . '<br>';
                // print_r($cek_kecamatan);
                // die;


                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
                    //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':Q' . $space);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                        $row++;
                    }

                    $no = 0;
                    $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;
                }


                if ($rowDataWP['CPM_KECAMATAN_WP']) {

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':Q' . $row);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );

                        $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                        $row++;

                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
                    }
                }
                // var_dump($rowData['bulan']);
                // die;

                $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                // echo $nama_kecamatan;
                // exit();
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
                if ($JENIS_PAJAK == 'RESTORAN') {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
                }
                // var_dump($JENIS_PAJAK == 'RESTORAN');
                // die;
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


                if ($nama_kecamatan != $nama_kecamatans) {
                    $total_pajak = 0;
                    $jan = 0;
                    $feb = 0;
                    $mar = 0;
                    $apr = 0;
                    $mei = 0;
                    $jun = 0;
                    $jul = 0;
                    $agu = 0;
                    $sep = 0;
                    $okt = 0;
                    $nov = 0;
                    $des = 0;
                }


                $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
                $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

                //untuk total
                $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

                //var_dump($total_pajak);die;

                $jml++;
                $row++;
                $no++;
                //var_dump($jumlah_data, $row);die;
                if ($jumlah_data == $jml) {
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

                        $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_jan);
                        $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_feb);
                        $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_mar);
                        $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_apr);
                        $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_mei);
                        $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_jun);
                        $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_jul);
                        $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_agu);
                        $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_sep);
                        $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_okt);
                        $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_nov);
                        $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_des);
                        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_total_pajak);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 3;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }

                    //var_dump($space);die;
                    $space = $space + 1;
                    $no_keterangan = 0;
                    $total_wp = 0;
                    //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
                    //if($this->_idp == '8'){
                    $query_keterangan = "SELECT
													wp.CPM_KECAMATAN_WP,
													count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
												FROM
													patda_wp wp
													INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
													AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
												WHERE
													wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
												GROUP BY
													CPM_KECAMATAN_WP 
												ORDER BY
													CPM_KECAMATAN_WP ASC";
                    //}
                    //var_dump($query_keterangan);die;

                    $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                    while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . $row_keterangan['CPM_KECAMATAN_WP']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $space++;
                        $no_keterangan++;
                        $total_wp += $row_keterangan['TOTAL'];
                    }
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }
            }




            /** style **/
            // judul dok + judul tabel
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q4')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A5:Q7')->getAlignment()->setWrapText(true);

            // border
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q' . $row)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );


            // fill tabel header
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->getStartColor()->setRGB('E4E4E4');

            // format angka col I & K
            $objPHPExcel->getActiveSheet()->getStyle('E8:Q' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // // fill tabel footer
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



            // Rename sheet
            //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
            for ($x = "A"; $x <= "H"; $x++) {
                if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
                else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
            }

            if ($_REQUEST['CPM_JENIS_PJK'] == 1) {
                $objPHPExcel->getActiveSheet()->setTitle("Reguler");
                $objPHPExcel->createSheet();
            } elseif ($_REQUEST['CPM_JENIS_PJK'] == 2) {
                $objPHPExcel->getActiveSheet()->setTitle("Non Reguler");
                $objPHPExcel->createSheet();
            } else {
                $objPHPExcel->getActiveSheet()->setTitle("$jp_id");
                $objPHPExcel->createSheet();
                $z++;
            }
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }


    private function download_pajak_xls_bentang_panjangV2()
    {

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
        }


        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }


        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);

        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

        $query_wp = "select * from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";

        #query select list data
        $query = "SELECT 
                    pj.CPM_ID, 
                    pj.CPM_NO, 
                    pj.CPM_TAHUN_PAJAK, 
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                    STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                    pj.CPM_AUTHOR, 
                    pj.CPM_VERSION,
                    pj.CPM_TOTAL_OMZET, 
                    pj.CPM_TARIF_PAJAK, 
                    pj.CPM_TOTAL_PAJAK, 
                    pr.CPM_NPWPD, 
                    pr.CPM_NAMA_WP,
                    pr.CPM_NAMA_OP,
                    pr.CPM_REKENING,
                    pr.CPM_KELURAHAN_OP,
                    pr.CPM_KECAMATAN_OP, 
                    pr.CPM_ALAMAT_OP, 
                    tr.CPM_TRAN_STATUS, 
                    tr.CPM_TRAN_DATE, 
                    tr.CPM_TRAN_INFO, 
                    tr.CPM_TRAN_FLAG, 
                    tr.CPM_TRAN_READ, 
                    pj.CPM_TIPE_PAJAK, 
                    tr.CPM_TRAN_ID
                    FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                    WHERE {$where}
                    ORDER BY 1";







        // $space = $space +1;
        // // $space = $space +1;
        // $no_keterangan = 0;
        // $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
        // $res_keterangan = mysqli_query($this->Conn, $query_keterangan);




        // while($row_keterangan = mysqli_fetch_array($res_keterangan)){

        // 		$objPHPExcel->getActiveSheet()->setCellValue('A'.$space, $no_keterangan+1);

        // 		$objPHPExcel->getActiveSheet()->setCellValue('B'.$space, 'JUMLAH WP KECAMATAN '.$row_keterangan['CPM_KECAMATAN_WP']);

        // 		$objPHPExcel->getActiveSheet()->setCellValue('C'.$space, $row_keterangan['TOTAL']);

        // 		$objPHPExcel->getActiveSheet()->getStyle('A'.$space.':R'.$space)->getFill()->getStartColor()->setRGB('ffff00');

        // 		$space++;
        // 		$no_keterangan++;
        // }



        $query2 = "SELECT
                    pj.CPM_TOTAL_PAJAK,
                    pj.CPM_TIPE_PAJAK,
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    pr.CPM_TIPE_PAJAK,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_OP
                    pr.CPM_KECAMATAN_WP
                FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where} ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

        // echo $query2,"\n\n";exit;
        // var_dump($query2);exit;

        // echo "<pre>" ;
        // print_r($_REQUEST);
        // echo $query2,"\n\n";

        // // $res = mysql_query($query2, $this->Conn);
        // // $history='KIKI KARAOKE';


        $data = array();
        $res = mysqli_query($this->Conn, $query2);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res)) {
            $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['CPM_TIPE_PAJAK'];
            $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
            );
            // $jumlah_data++;
        }

        // echo'<pre>';
        // print_r($data);
        // print_r($data['CPM_NPWPD']);
        // exit;
        // if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where3 = $this->where3_cetak_bentang();
        // }
        $query3 = "SELECT
                    pj.CPM_TOTAL_PAJAK,
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_OP
                FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) = 12 
                    ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

        // var_dump($query3);exit;

        $data2 = array();
        $res2 = mysqli_query($this->Conn, $query3);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res2)) {
            $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['CPM_TIPE_PAJAK'];
            $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
            );
        }
        $data_wp = array();
        $res_wp = mysqli_query($this->Conn, $query_wp);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res_wp)) {
            $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
            $data_wp[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['CPM_TIPE_PAJAK'];
            // $jumlah_data++;
        }

        // echo'<pre>';
        // print_r($data);
        // print_r($data['CPM_NPWPD']);
        // exit;

        // echo "<pre>" ;
        // print_r($_REQUEST);
        // echo $query2,"\n\n";
        // die;
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();


        $objPHPExcel->createSheet(1);
        $sheet2 = $objPHPExcel->getSheet(1)->setTitle('Non Reguler');
        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("-")
            ->setDescription("bphtb")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data

        $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
        $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);

        // $jenis_pajakk= '';
        // if ($JENIS_PAJAK == 'MINERAL'){
        //     $jenis_pajakk = 'MINERBA';
        // }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
            ->setCellValue('E6', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . '')
            ->setCellValue('A6', 'NO')
            ->setCellValue('B6', 'NAMA WP')
            ->setCellValue('C6', 'KECAMATAN')
            ->setCellValue('D6', 'TAPBOX.')
            ->setCellValue('E7', $tahun_pajak_label_sebelumnya)
            // ->setCellValue('C7', 'PAJAK '.$JENIS_PAJAK.' TAHUN '.$tahun_pajak_label.' ')
            ->setCellValue('F7', $tahun_pajak_label . ' ')
            ->setCellValue('F8', '')
            ->setCellValue('G8', 'JAN')
            ->setCellValue('H8', 'FEB')
            ->setCellValue('I8', 'MAR')
            ->setCellValue('J8', 'APRIL')
            ->setCellValue('K8', 'MEI')
            ->setCellValue('L8', 'JUNI')
            ->setCellValue('M8', 'JULI')
            ->setCellValue('N8', 'AGS')
            ->setCellValue('O8', 'SEPT')
            ->setCellValue('P8', 'OKT')
            ->setCellValue('Q8', 'NOP')
            ->setCellValue('R8', 'DES')
            ->setCellValue('S6', 'JUMLAH')
            ->setCellValue('T8', 'TIPE');


        $objPHPExcel->setActiveSheetIndex(1)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
            ->setCellValue('E6', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . '')
            ->setCellValue('A6', 'NO')
            ->setCellValue('B6', 'NAMA WP')
            ->setCellValue('C6', 'KECAMATAN')
            ->setCellValue('D6', 'TAPBOX.')
            ->setCellValue('E7', $tahun_pajak_label_sebelumnya)
            // ->setCellValue('C7', 'PAJAK '.$JENIS_PAJAK.' TAHUN '.$tahun_pajak_label.' ')
            ->setCellValue('F7', $tahun_pajak_label . ' ')
            ->setCellValue('F8', '')
            ->setCellValue('G8', 'JAN')
            ->setCellValue('H8', 'FEB')
            ->setCellValue('I8', 'MAR')
            ->setCellValue('J8', 'APRIL')
            ->setCellValue('K8', 'MEI')
            ->setCellValue('L8', 'JUNI')
            ->setCellValue('M8', 'JULI')
            ->setCellValue('N8', 'AGS')
            ->setCellValue('O8', 'SEPT')
            ->setCellValue('P8', 'OKT')
            ->setCellValue('Q8', 'NOP')
            ->setCellValue('R8', 'DES')
            ->setCellValue('S6', 'JUMLAH')
            ->setCellValue('T8', 'TIPE');

        // judul dok

        $objPHPExcel->getSheet(0)->mergeCells("A1:R1");
        $objPHPExcel->getSheet(0)->mergeCells("A2:R2");
        $objPHPExcel->getSheet(0)->mergeCells("A3:R3");
        $objPHPExcel->getSheet(0)->mergeCells("A4:R4");
        $objPHPExcel->getSheet(0)->mergeCells("E6:R6");

        $objPHPExcel->getSheet(0)->mergeCells("A6:A8");
        $objPHPExcel->getSheet(0)->mergeCells("B6:B8");
        $objPHPExcel->getSheet(0)->mergeCells("C6:C8");
        $objPHPExcel->getSheet(0)->mergeCells("D6:D8");
        $objPHPExcel->getSheet(0)->mergeCells("S6:S8");
        $objPHPExcel->getSheet(0)->mergeCells("F7:R7");

        $objPHPExcel->getSheet(1)->mergeCells("A1:R1");
        $objPHPExcel->getSheet(1)->mergeCells("A2:R2");
        $objPHPExcel->getSheet(1)->mergeCells("A3:R3");
        $objPHPExcel->getSheet(1)->mergeCells("A4:R4");
        $objPHPExcel->getSheet(1)->mergeCells("E6:R6");


        $objPHPExcel->getSheet(1)->mergeCells("A6:A8");
        $objPHPExcel->getSheet(1)->mergeCells("B6:B8");
        $objPHPExcel->getSheet(1)->mergeCells("C6:C8");
        $objPHPExcel->getSheet(1)->mergeCells("D6:D8");
        $objPHPExcel->getSheet(1)->mergeCells("S6:S8");
        $objPHPExcel->getSheet(1)->mergeCells("F7:R7");


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
        $tab = $jns[$this->_s];
        $jml = 0;

        $row = 9;
        $sumRows = mysqli_num_rows($res);
        $total_pajak = 0;

        // var_dump($data_wp);exit;
        foreach ($data_wp as $npwpd => $rowDataWP) {
            $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];

            break;
        }
        $jumlah_data = count($data_wp);
        // echo $cek_kecamatan;exit();
        foreach ($data_wp as $npwpd => $rowDataWP) {
            $rowData = $data[$rowDataWP['CPM_NPWPD']];
            $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
            // var_dump($data_wp);die;
            //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
            //$nama_kecamatan = $cek_kecamatan;

            //var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;
            if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
                //var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;

                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':E' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $desbelum);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $jan);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $feb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mar);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $apr);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mei);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $jun);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $jul);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $aug);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $sep);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $okt);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $nov);
                $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $des);
                $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $total_pajak);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':S' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':S' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':S' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );

                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    //var_dump($row);die;
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':S' . $space);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':S' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':S' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                    $row++;
                }


                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];

                $row++;
            }







            if ($rowDataWP['CPM_KECAMATAN_WP']) {
                //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
                //$nama_kecamatan = $cek_kecamatan;
                //echo $nama_kecamatan;exit;
                if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                    $objPHPExcel->getSheet(0)->mergeCells('A' . $row . ':S' . $row);
                    $objPHPExcel->getSheet(0)->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                    $objPHPExcel->getSheet(0)->getStyle('A' . $row . ':S' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getSheet(0)->getStyle('A' . $row . ':S' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getSheet(1)->mergeCells('A' . $row . ':S' . $row);
                    $objPHPExcel->getSheet(1)->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                    $objPHPExcel->getSheet(1)->getStyle('A' . $row . ':S' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getSheet(1)->getStyle('A' . $row . ':S' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getSheet(0)->getStyle('A' . $row . ':S' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                    $objPHPExcel->getSheet(1)->getStyle('A' . $row . ':S' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    //$cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;


                    $objPHPExcel->getSheet(0)->insertNewRowBefore($row + 2, 2);
                    $objPHPExcel->getSheet(1)->insertNewRowBefore($row + 2, 2);
                    //var_dump($row);die;
                    $no = 0;
                }
            }
            $query2 = "select CPM_ID, CPM_NPWPD,UPPER(CPM_NAMA_OP) as CPM_NAMA_OP from PATDA_HIBURAN_PROFIL where CPM_NPWPD='" . $rowData['CPM_NPWPD'] . "' order by CPM_TGL_UPDATE asc";

            $resR = mysqli_query($this->Conn, $query2);
            $row_cek = mysqli_fetch_array($resR);
            // echo "string";
            $history = strtoupper($row_cek['CPM_NAMA_OP']);
            // echo $history;
            // exit();

            //while($rowR = mysqli_fetch_array($resR)){
            //
            //    if($history != $row['CPM_NAMA_OP']){
            //        $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, ($jml+1));
            //        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$row,  $history, PHPExcel_Cell_DataType::TYPE_STRING);
            //        $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $nama_kecamatan);
            //        $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':R'.$row);
            //        $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, "Data sudah berubah dari ".$history." jadi ".$rowR['CPM_NAMA_OP']);
            //
            //        $objPHPExcel->getActiveSheet()->getStyle('F'.$row.':R'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            //        $objPHPExcel->getActiveSheet()->getStyle('F'.$row.':R'.$row)->getFill()->getStartColor()->setRGB('ffff00');
            //        $history = strtoupper($rowR['CPM_NAMA_OP']);
            //        $rowData['CPM_NAMA_OP'] = $history;
            //        $row++;
            //    }
            //}
            // var_dump($rowData['bulan']);exit;
            // var_dump($rowDataWP);exit;
            // $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];

            //  echo'<pre>';
            // print_r($data);
            // print_r($data['CPM_NPWPD']);
            // exit;

            $rowDataWP['CPM_NPWPD'] = Pajak::formatNPWPD($rowDataWP['CPM_NPWPD']);
            $objPHPExcel->getSheet(0)->setCellValue('A' . $row, ($no + 1));
            $objPHPExcel->getSheet(0)->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(0)->setCellValue('C' . $row,  $rowDataWP['CPM_KECAMATAN_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(0)->setCellValue('T' . $row,  $rowDataWP['CPM_TIPE_PAJAK'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(0)->setCellValue('D' . $row, '');
            $objPHPExcel->getSheet(0)->setCellValue('E' . $row, '');
            $objPHPExcel->getSheet(0)->setCellValue('F' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('G' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('H' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('I' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('J' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('K' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('L' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('M' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('N' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('O' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('P' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('Q' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('R' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(0)->setCellValue('S' . $row, "=SUM(D{$row}:P{$row})");
            // $objPHPExcel->getActiveSheet()->setCellValue('R'.$row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK']+$rowData['bulan'][2]['CPM_TOTAL_PAJAK']+$rowData['bulan'][3]['CPM_TOTAL_PAJAK']+$rowData['bulan'][4]['CPM_TOTAL_PAJAK']+$rowData['bulan'][5]['CPM_TOTAL_PAJAK']+$rowData['bulan'][6]['CPM_TOTAL_PAJAK']+$rowData['bulan'][7]['CPM_TOTAL_PAJAK']+$rowData['bulan'][8]['CPM_TOTAL_PAJAK']+$rowData['bulan'][9]['CPM_TOTAL_PAJAK']+$rowData['bulan'][10]['CPM_TOTAL_PAJAK']+$rowData['bulan'][11]['CPM_TOTAL_PAJAK']+$rowData['bulan'][12]['CPM_TOTAL_PAJAK']);

            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $objPHPExcel->getSheet(1)->setCellValue('A' . $row, ($no + 1));
            $objPHPExcel->getSheet(1)->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(1)->setCellValueExplicit('C' . $row,  $rowDataWP['CPM_KECAMATAN_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(0)->setCellValue('T' . $row,  $rowDataWP['CPM_TIPE_PAJAK'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getSheet(1)->setCellValue('D' . $row, '');
            $objPHPExcel->getSheet(1)->setCellValue('E' . $row, '');
            $objPHPExcel->getSheet(1)->setCellValue('F' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('G' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('H' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('I' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('J' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('K' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('L' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('M' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('N' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('O' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('P' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('Q' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('R' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getSheet(1)->setCellValue('S' . $row, "=SUM(D{$row}:P{$row})");
            // $objPHPExcel->getActiveSheet()->setCellValue('R'.$row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK']+$rowData['bulan'][2]['CPM_TOTAL_PAJAK']+$rowData['bulan'][3]['CPM_TOTAL_PAJAK']+$rowData['bulan'][4]['CPM_TOTAL_PAJAK']+$rowData['bulan'][5]['CPM_TOTAL_PAJAK']+$rowData['bulan'][6]['CPM_TOTAL_PAJAK']+$rowData['bulan'][7]['CPM_TOTAL_PAJAK']+$rowData['bulan'][8]['CPM_TOTAL_PAJAK']+$rowData['bulan'][9]['CPM_TOTAL_PAJAK']+$rowData['bulan'][10]['CPM_TOTAL_PAJAK']+$rowData['bulan'][11]['CPM_TOTAL_PAJAK']+$rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


            if ($nama_kecamatan != $nama_kecamatans) {
                $total_pajak = 0;
                $jan = 0;
                $feb = 0;
                $mar = 0;
                $apr = 0;
                $mei = 0;
                $jun = 0;
                $jul = 0;
                $aug = 0;
                $sep = 0;
                $nov = 0;
                $okt = 0;
                $des = 0;
                $desbelum = 0;
            }
            $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'];
            $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
            $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
            $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
            $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
            $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
            $aug += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
            $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $nov += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
            $okt += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
            $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $desbelum += $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

            //untuk total
            $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'];
            $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
            $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
            $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
            $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
            $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
            $total_aug += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
            $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $total_nov += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
            $total_okt += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
            $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $total_desbelum += $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;


            $jml++;
            $row++;
            $no++;
            if ($jumlah_data == $jml) {
                // var_dump($row);die;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':B' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $desbelum);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $aug);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );


                if ($jumlah_data == $jml) {
                    // var_dump($row);die;
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $total_desbelum);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_aug);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_des);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }


                if ($jumlah_data == $jml) {
                    //var_dump($row);die;
                    $space = $row + 3;
                    $objPHPExcel->getSheet(0)->insertNewRowBefore($space, 1);
                    $objPHPExcel->getSheet(0)->mergeCells('A' . $space . ':C' . $space);
                    $objPHPExcel->getSheet(0)->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN ");

                    $objPHPExcel->getSheet(0)->getStyle('A' . $space . ':S' . $space)->getFill()->getStartColor()->setRGB('ffff00');

                    $objPHPExcel->getSheet(1)->insertNewRowBefore($space, 1);
                    $objPHPExcel->getSheet(1)->mergeCells('A' . $space . ':C' . $space);
                    $objPHPExcel->getSheet(1)->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN ");

                    $objPHPExcel->getSheet(1)->getStyle('A' . $space . ':S' . $space)->getFill()->getStartColor()->setRGB('ffff00');

                    $objPHPExcel->getSheet(0)->getStyle('A' . $space . ':S' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                    $objPHPExcel->getSheet(1)->getStyle('A' . $space . ':S' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }
                //var_dump($space);die;
                $space = $space + 1;
                $no_keterangan = 0;
                $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
                $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                    $objPHPExcel->getSheet(0)->setCellValue('A' . $space, $no_keterangan + 1);
                    $objPHPExcel->getSheet(0)->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
                    $objPHPExcel->getSheet(0)->setCellValue('C' . $space, $row_keterangan['TOTAL']);

                    $objPHPExcel->getSheet(1)->setCellValue('A' . $space, $no_keterangan + 1);
                    $objPHPExcel->getSheet(1)->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
                    $objPHPExcel->getSheet(1)->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                    $totalwp += $row_keterangan['TOTAL'] + 0;
                    // var_dump(mysqli_num_rows($res_keterangan));exit;
                    $objPHPExcel->getSheet(0)->getStyle('A' . $space . ':S' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                    $objPHPExcel->getSheet(1)->getStyle('A' . $space . ':S' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                    $space++;
                    $no_keterangan++;
                    if ($no_keterangan == mysqli_num_rows($res_keterangan)) {
                        $objPHPExcel->getSheet(1)->setCellValue('B' . ($space), "TOTAL");
                        $objPHPExcel->getSheet(1)->setCellValue('C' . ($space), $totalwp);
                        $objPHPExcel->getSheet(0)->setCellValue('B' . ($space), "TOTAL");
                        $objPHPExcel->getSheet(0)->setCellValue('C' . ($space), $totalwp);
                    }
                }
            }
        }




        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getSheet(0)->getStyle('A1:S4')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getSheet(1)->getStyle('A1:S4')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getSheet(0)->getStyle('A6:S8')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getSheet(1)->getStyle('A6:S8')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getSheet(0)->getStyle('A5:S8')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:S8')->getAlignment()->setWrapText(true);

        // border
        $objPHPExcel->getSheet(0)->getStyle('A6:S' . ($row + 1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );
        $objPHPExcel->getSheet(1)->getStyle('A6:S' . ($row + 1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );


        // fill tabel header
        $objPHPExcel->getSheet(0)->getStyle('A7:S8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getSheet(0)->getStyle('A7:S8')->getFill()->getStartColor()->setRGB('E4E4E4');

        $objPHPExcel->getSheet(1)->getStyle('A7:S8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getSheet(1)->getStyle('A7:S8')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getSheet(0)->getStyle('F8:S' . $space)->getNumberFormat()->setFormatCode('#,##0');
        $objPHPExcel->getSheet(1)->getStyle('F8:S' . $space)->getNumberFormat()->setFormatCode('#,##0');

        // // fill tabel footer
        // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



        // Rename sheet 

        $objPHPExcel->getSheet(0)->setTitle('Reguler');


        $objPHPExcel->getSheet(0)->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getSheet(1)->getColumnDimension('C')->setWidth(10);
        for ($x = "A"; $x <= "H"; $x++) {
            if ($x == 'B') {
                $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(60);
            } elseif ($x == 'A') {
                $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(4);
            } elseif ($x == 'C') {
                $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(15);
            } elseif ($x == 'D') {
                $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(9);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
            }
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }
    function download_pajak_xls()
    {
        error_reporting(0);
        // exit($this->_idp);
        // var_dump($_REQUEST);
        // die;


        if ($this->_idp == 1) {
            //$this->download_pajak_xls_pat();
            $this->download_pajak_xls_non14();
        } elseif ($this->_idp == 4) {
            $this->download_pajak_xls_minerba();
        } else {
            $this->download_pajak_xls_non14();
        }
        exit;

        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";

        $where .= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";

        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
        //versi baru
        $where .= (isset($_REQUEST['CPM_FILTER_V2']) && $_REQUEST['CPM_FILTER_V2'] != "") ? " AND CPM_ATR_REKENING IN ( {$rekekningv2}) " : "";


        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);

        if ($this->_idp == 8 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            // $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        }
        //echo $_REQUEST['CPM_NPWPD'],$_REQUEST['CPM_NOP'],$_REQUEST['CPM_JENIS_PJK'];exit;
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

        // echo "<pre>" . print_r($_REQUEST, true);
        // "</pre>";
        // echo $query;
        // exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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

    function download_pajak_retribusi_xls()
    {

        if (empty($_REQUEST['BULAN']) || empty($_REQUEST['TAHUN'])) {
            echo "<script>alert('Silahkan pilih filter bulan dan tahun terlebih dahulu');</script>";
            echo "<script>window.close();</script>"; // langsung exit tab
            // Di sini Anda bisa tambahkan kode lain, atau langsung exit jika Anda ingin berhenti eksekusi
            exit;
        }

        $where = "(";
        $where .= "1=1 "; #jika status ditolak, maka flag tidak ditentukan
        $where .= ") ";
    

        $where .= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";

        
        $where .= (isset($_REQUEST['BULAN']) && $_REQUEST['BULAN'] != "") ? " AND BULAN = \"{$_REQUEST['BULAN']}\" " : "";
        $where .= (isset($_REQUEST['TAHUN']) && $_REQUEST['TAHUN'] != "") ? " AND TAHUN = \"{$_REQUEST['TAHUN']}\" " : "";
        $where .= (isset($_REQUEST['JENIS_PENERIMAAN']) && $_REQUEST['JENIS_PENERIMAAN'] != "") ? " AND JENIS_PENERIMAAN_ID like \"{$_REQUEST['JENIS_PENERIMAAN']}%\" " : "";
        $where .= (isset($_REQUEST['JENIS_RETRIBUSI']) && $_REQUEST['JENIS_RETRIBUSI'] != "") ? " AND JENIS_RETRIBUSI_ID like \"{$_REQUEST['JENIS_RETRIBUSI']}%\" " : "";

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);

        if ($this->_idp == 8 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
 
        }

  
        if ($_REQUEST['BULAN'] == '01') {
            $bulan_sebelumnya = '12';
            $tahun_sebelumnya =  $_REQUEST['TAHUN'] - 1;
        } else {
            $bulan_sebelumnya = sprintf("%02d", $_REQUEST['BULAN'] - 1);
            $tahun_sebelumnya = $_REQUEST['TAHUN'];
        }
        $query = "  SELECT
                    doc.CPM_ID,
                    doc.rekening,
                    doc.bulan,
                    doc.target,
                    doc.anggaran,
                    rek.nama_pendapatan,
                    jen.jenis_penerimaan,
                    doc.jumlah_realisasi,
                    (
                        SELECT SUM(p.jumlah_realisasi)
                        FROM patda_retribusi_doc AS p
                        WHERE p.bulan = {$bulan_sebelumnya} AND p.tahun = {$tahun_sebelumnya}
                            AND p.jenis_retribusi_id = doc.jenis_retribusi_id
                        LIMIT 1
                    ) AS realisasi_bulan_lalu
                FROM
                    patda_retribusi_doc doc
                INNER JOIN
                    rekening_retribusi rek ON doc.jenis_retribusi_id = rek.id
                INNER JOIN
                    jenis_penerimaan_retribusi jen ON doc.jenis_penerimaan_id = jen.id
                WHERE
                    {$where} ";
                  


        // echo "<pre>" . print_r($_REQUEST, true);
        // "</pre>";
        // echo $query;
        // exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("")
            ->setSubject("Retribusi")
            ->setDescription("Retribusi")
            ->setKeywords("");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'LAPORAN TARGET DAN REALISASI PENDAPATAN DAERAH KABUPATEN PESAWARAN T.A ' . $_REQUEST['TAHUN'])
            ->setCellValue('A2', ' YANG DIKELOLA BADAN/ DINAS / BAGIAN')
            ->setCellValue('A3', 'TAHUN ANGGARAN ' . $_REQUEST['TAHUN'])
            ->setCellValue('A4', 'PERIODE : ' .  $this->arr_bulann[$_REQUEST['BULAN']])

            ->setCellValue('A7', 'No.')

            ->setCellValue('B7', 'Jenis Penerimaan Pada')
            ->setCellValue('B8', 'Badan/Dinas/Bagian')

            ->setCellValue('C7', 'KODE')
            ->setCellValue('C8', 'REKENING')

            ->setCellValue('D7', 'ANGGARAN')
            ->setCellValue('D9', '(Rp)')

            ->setCellValue('E7', 'REALISASI')
            ->setCellValue('E8', 'S/D BULAN LALU')
            ->setCellValue('E9', '(Rp)')

            ->setCellValue('F8', 'BULAN INI')
            ->setCellValue('F9', '(Rp)')

            ->setCellValue('G8', 'JUMLAH')
            ->setCellValue('G9', '(Rp)')

            ->setCellValue('H7', 'PERSEN')
            ->setCellValue('H9', '(%)')


            ->setCellValue('F7', 'PERSEN');

            $objPHPExcel->getActiveSheet()->mergeCells("A1:H1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:H2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:H3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:H4");
            $objPHPExcel->getActiveSheet()->mergeCells("A7:A9");
    
            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A8:A9");
            $objPHPExcel->getActiveSheet()->mergeCells("B8:B9");
            $objPHPExcel->getActiveSheet()->mergeCells("C8:C9");
            $objPHPExcel->getActiveSheet()->mergeCells("D7:D8");
            
            $objPHPExcel->getActiveSheet()->mergeCells("E7:G7");
            
            $objPHPExcel->getActiveSheet()->mergeCells("H7:H8");

        $objPHPExcel->setActiveSheetIndex(0);

        $row = 10;
        $no = 1;
        $subItem = 'a';
        $currentJenisPenerimaan = "";

        $sumRows = mysqli_num_rows($res);
        $data = [];
        while ($rowData = mysqli_fetch_assoc($res)) {
            $data[] = $rowData;
        }
        
        // Group data by jenis_penerimaan
        $groupedData = [];
        foreach ($data as $rowData) {
            $groupedData[$rowData['jenis_penerimaan']][] = $rowData;
        }
        
        // Write data to the excel sheet
        foreach ($groupedData as $jenisPenerimaan => $pendapatans) {
            // Write jenis_penerimaan
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no . '.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $jenisPenerimaan);
              // Apply bold style to jenis_penerimaan

              $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true,
                    ),
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('B' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true,
                    ),
                )
            );
            $no++;
            $row++;
            $subItem = 'a';
        
            // Write nama_pendapatan and rekening for each jenis_penerimaan
            foreach ($pendapatans as $pendapatan) {
                $total_realisasi =  $pendapatan['jumlah_realisasi'] +  $pendapatan['realisasi_bulan_lalu'];
                // $persentase = ($total_realisasi) / $pendapatan['anggaran'] * 100;
                $persentase = round(($total_realisasi)/ $pendapatan['anggaran'] * 100, 2);

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, '');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $subItem . '. ' . $pendapatan['nama_pendapatan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $pendapatan['rekening']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $pendapatan['anggaran']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $pendapatan['realisasi_bulan_lalu']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $pendapatan['jumlah_realisasi']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $total_realisasi);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $persentase . "%");
                $row++;
                $subItem++;
            }
        }

        $lastRow = $row;
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Retribusi');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:H9')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . $lastRow)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFill()->getStartColor()->setRGB('E4E4E4');

        // $objPHPExcel->getActiveSheet()->getStyle('A8:G8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        // $objPHPExcel->getActiveSheet()->getStyle('A7:G7')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "H"; $x++) {
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

    function download_pajak_xls_v2()
    {
        error_reporting(0);
        $this->download_pajak_xls_rek_pajak();
    }


    private function download_pajak_xls_pat()
    {
        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                if (isset($_SESSION['npwpd']) && !empty($_SESSION['npwpd']))
                    $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
                else
                    $where = " ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";

        $where = (isset($_REQUEST['CPM_TRAN_DATE1']) && $_REQUEST['CPM_TRAN_DATE1'] != "") ? " AND STR_TO_DATE(CPM_TRAN_DATE,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TRAN_DATE1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TRAN_DATE,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TRAN_DATE2']}\",\" 23:59:59\")  " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }


        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

        #query select list data
        $query = "SELECT 
                    pj.CPM_ID, 
                    pj.CPM_NO, 
                    pj.CPM_TAHUN_PAJAK, 
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                    STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                    pj.CPM_AUTHOR, 
                    pj.CPM_VERSION,
                    pj.CPM_TOTAL_OMZET, 
                    pj.CPM_TARIF_PAJAK, 
                    pj.CPM_TOTAL_PAJAK,
                    DATE_FORMAT(pj.CPM_TGL_INPUT, '%d-%m-%Y %h:%i:%s') as CPM_TGL_INPUT,
                    pr.CPM_NPWPD, 
                    pr.CPM_NAMA_WP,
                    pr.CPM_NAMA_OP,
                    pr.CPM_REKENING,
                    pr.CPM_KELURAHAN_OP,
                    pr.CPM_KECAMATAN_OP, 
                    pr.CPM_ALAMAT_OP, 
                    tr.CPM_TRAN_STATUS, 
                    tr.CPM_TRAN_DATE, 
                    tr.CPM_TRAN_INFO, 
                    tr.CPM_TRAN_FLAG, 
                    tr.CPM_TRAN_READ, 
                    tr.CPM_TRAN_ID
                    FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                    WHERE {$where}
                    ORDER BY pj.CPM_NO ASC";

        // echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("-")
            ->setDescription("bphtb")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'DAFTAR SURAT KETETAPAN PAJAK AIR TANAH')
            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', ($periode != '' ? $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

            ->setCellValue('A6', 'NO.')
            ->setCellValue('B6', 'TGL INPUT')
            ->setCellValue('C6', 'TGL SKPD')
            ->setCellValue('D6', 'TGL VERIFIKASI')
            ->setCellValue('E6', 'NO SKPD')
            ->setCellValue('F6', 'NPWPD')
            ->setCellValue('G6', 'NAMA WAJIB PAJAK')
            ->setCellValue('H6', 'MASA PAJAK')
            ->setCellValue('I6', 'ALAMAT')
            ->setCellValue('J6', 'KETETAPAN');

        // judul dok
        $objPHPExcel->getActiveSheet()->mergeCells("A1:J1");
        $objPHPExcel->getActiveSheet()->mergeCells("A2:J2");
        $objPHPExcel->getActiveSheet()->mergeCells("A3:J3");
        $objPHPExcel->getActiveSheet()->mergeCells("A4:J4");

        // judul kolom
        $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
        $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
        $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
        $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
        $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
        $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
        $objPHPExcel->getActiveSheet()->mergeCells("G6:G7");
        $objPHPExcel->getActiveSheet()->mergeCells("H6:H7");
        $objPHPExcel->getActiveSheet()->mergeCells("I6:I7");
        $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");


        /* if ($this->_s == 0) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', 'Status'); #"CPM_TRAN_STATUS
        }

        if ($this->_s == 4) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1', 'Keterangan'); #CPM_TRAN_INFO            
        } */


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
        $tab = $jns[$this->_s];
        $jml = 0;

        $row = 8;
        $sumRows = mysqli_num_rows($res);
        $total_pajak = 0;

        // echo "<pre>";
        while ($rowData = mysql_fetch_assoc($res)) {
            $masa = isset($triwulan[$rowData['CPM_BULAN']]) ? strtoupper($triwulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_PAJAK'] : strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_PAJAK'];
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_TGL_INPUT']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, date('d/m/Y', strtotime($rowData['CPM_TRAN_DATE'])), PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $masa);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_ALAMAT_OP']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_TOTAL_PAJAK']);
            $total_pajak += $rowData['CPM_TOTAL_PAJAK'];
            $jml++;
            $row++;
        }

        // query total sampai bulan lalu
        $total_pajak_bulan_lalu = 0;

        if ($periode_bulan != '') {
            $bulan_lalu = date('Y-m', strtotime($periode_bulan . '-01 -1 month'));
            // $res_prev = mysql_query("SELECT SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT DISTINCT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
            //             FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            //             INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            //             INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            //             WHERE DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' group by pj.CPM_ID) a", $this->Conn);
            $query13 = "SELECT 
             
            sum(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK
          
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            WHERE{$where} AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu'
            ORDER BY pj.CPM_NO ASC";
            $res_prev = mysqli_query($this->Conn, $query13);
            // echo"SELECT 

            // sum(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK

            // FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            // INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            // INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            // WHERE{$where} AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu'
            // ORDER BY pj.CPM_NO ASC";exit;
            if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
            }
        }



        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "TOTAL");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=SUM(J6:J' . ($row - 1) . ')');
        $row++;

        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU ");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $total_pajak_bulan_lalu + 0);
        $row++;

        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=J' . ($row - 2) . '+J' . ($row - 1));

        $lastRow = $row;
        $rowFooter = array(5, 3);
        $row += 2;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'Jumlah SKPD diterbitkan = ' . $jml . ' SKPD, dengan nilai pajak sebesar Rp. ' . number_format($total_pajak));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':J' . $row);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        // $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->getFont()->setItalic(true);
        $lastRow = $row - 2;
        $rowFooter = array(7, 5);

        $row++;
        $row++;
        $row++;


        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getActiveSheet()->getStyle('A1:J7')->applyFromArray(
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
        // $objPHPExcel->getActiveSheet()->getStyle('A6:H7')->getAlignment()->setWrapText(true);
        // rata tengah data col A-D
        $objPHPExcel->getActiveSheet()->getStyle('A1:E' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );

        // bold footer
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - $rowFooter[0]) . ':J' . ($row - $rowFooter[1]))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - $rowFooter[0]) . ':J' . ($row - $rowFooter[1]))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // border
        $objPHPExcel->getActiveSheet()->getStyle('A6:J' . $lastRow)->applyFromArray(
            array(
                'borders' => array(
                    'allBorders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                )
            )
        );


        // fill tabel header
        $objPHPExcel->getActiveSheet()->getStyle('A6:J7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:J7')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getActiveSheet()->getStyle('H6:J' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 1), "Kepala Bidang");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), "Pengembangan dan Penetapan");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 6), "YUYUN MAYA SAPHIRA, SE");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 7), "NIP. 19780708 200312 2 008");
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':C' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':C' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 6) . ':C' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 7) . ':C' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6) . ':C' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, "Kalianda, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y'));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 1), "Staf Bidang Pengembangan");
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 2), "dan Penetapan");
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 6), "GUSTUS HARIYANTO, S.H");
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 7), "NIP. 19860828 201503 1 001");
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $row . ':G' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 1) . ':G' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 2) . ':G' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 6) . ':G' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 7) . ':G' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 6) . ':G' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $row += 8;
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, "Mengetahui");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 1), "Kepala Badan Pendapatan Daerah");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 2), "Kabupaten PESAWARAN");
        // $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 6), "Drs. BURHANUDDIN, MM");
        // $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 7), "NIP. 19630310 198411 1 002");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 6), "FERI BASTIAN, S.E., M.Ling.");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 7), "NIP. 19731104 199303 1 002");
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $row . ':E' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($row + 1) . ':E' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($row + 2) . ':E' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($row + 6) . ':E' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($row + 7) . ':E' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('D' . ($row + 6) . ':E' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('D' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 8) . ':I' . ($row + 7))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);


        for ($x = "A"; $x <= "H"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pajak-' . strtolower($JENIS_PAJAK) . '-' . date('Ymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }

    private function download_pajak_xls_non14()
    {
        // var_dump($_REQUEST);
        // die;
        $selectedValues = explode(',', $_REQUEST['CPM_FILTER_V2']);
        $rekekningv2 = "'" . implode("','", $selectedValues) . "'";

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = "";

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }

        $where .= ") ";
        $_REQUEST['CPM_NPWPD'] = str_replace('.', '', $_REQUEST['CPM_NPWPD']);
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
                $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") 
                    OR TIMESTAMP >= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\", \" 00:00:00\")
                     AND TIMESTAMP <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\", \" 23:59:59\"))";
                $periode = date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR1'])) . ' s/d ' . date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR2']));
                $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
            }
        } else {
            if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
                $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) 
                  ";
                $periode = date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR1'])) . ' s/d ' . date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR2']));
                $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
            }
        }
        if ($this->_idp == 8 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 3 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 7 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND atr.CPM_ATR_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        }
        $where .= (isset($_REQUEST['CPM_FILTER_V2']) && $_REQUEST['CPM_FILTER_V2'] != "") ? " AND CPM_ATR_REKENING IN ( {$rekekningv2}) " : "";

        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $cariBulan = $_REQUEST['CPM_MASA_PAJAK'];
        $cariTahun = $_REQUEST['CPM_TAHUN_PAJAK'];
        if (isset($_REQUEST['CPM_TRIWULAN']) && $_REQUEST['CPM_TRIWULAN'] != "") {
            if ($_REQUEST['CPM_TRIWULAN'] == 1) {
                $where .= " AND CPM_MASA_PAJAK IN(1,2,3)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 2) {
                $where .= " AND CPM_MASA_PAJAK IN(4,5,6)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 3) {
                $where .= " AND CPM_MASA_PAJAK IN(7,8,9)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 4) {
                $where .= " AND CPM_MASA_PAJAK IN(10,11,12)";
            }
        }
        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
        if ($this->_idp == 8) {
            $select_tambahan = "pj.PELAKSANA_KEGIATAN,pj.CPM_TIPE_PAJAK, ";
            if ($this->_i == 4) {
                # code...
                $select_tambahan = "pj.PELAKSANA_KEGIATAN,gw.payment_code,pj.CPM_TIPE_PAJAK,";
                $join_tambahan = "LEFT JOIN simpatda_gw gw ON gw.id_switching = pj.CPM_ID";
            }
        } elseif ($this->_idp == 7) {
            $select_tambahan = "pj.CPM_TYPE_PAJAK, ";
        } elseif ($this->_idp == 3) {
            $select_tambahan = "pj.CPM_TiPE_PAJAK, ";
        }


        #query select list data
        if ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $query = "SELECT 
                pj.CPM_ID, 
                pj.CPM_NO, 
                pj.CPM_TAHUN_PAJAK, 
                permen.nmrek,
                MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
                CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
             
                pj.CPM_AUTHOR, 
                pj.CPM_VERSION,
                pj.CPM_TOTAL_OMZET, 
                pj.CPM_TARIF_PAJAK, 
                pj.CPM_TOTAL_PAJAK,
                pr.CPM_NPWPD, 
                pr.CPM_NAMA_WP,
                pr.CPM_NAMA_OP,
                pr.CPM_REKENING,
                pr.CPM_KELURAHAN_OP,
                pr.CPM_KECAMATAN_OP, 
                tr.CPM_TRAN_STATUS, 
                tr.CPM_TRAN_DATE, 
                tr.CPM_TRAN_INFO, 
                tr.CPM_TRAN_FLAG,
                {$select_tambahan} 
                tr.CPM_TRAN_READ, 
                tr.CPM_TRAN_ID,
                TIMESTAMP
                FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
				INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
	
                INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
              
                INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
                {$join_tambahan}
                WHERE {$where} {$where2}
                GROUP BY CPM_ID
                ORDER BY pj.CPM_NO ASC";
            } else {
                $query = "SELECT 
                pj.CPM_ID, 
                pj.CPM_NO, 
                pj.CPM_TAHUN_PAJAK, 
                permen.nmrek,
                MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
                CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                pj.CPM_AUTHOR, 
                pj.CPM_VERSION,
                pj.CPM_TOTAL_OMZET, 
                pj.CPM_TARIF_PAJAK, 
                pj.CPM_TOTAL_PAJAK,
                pr.CPM_NPWPD, 
                pr.CPM_NAMA_WP,
                pr.CPM_NAMA_OP,
                pr.CPM_REKENING,
                pr.CPM_KELURAHAN_OP,
                pr.CPM_KECAMATAN_OP, 
                tr.CPM_TRAN_STATUS, 
                tr.CPM_TRAN_DATE, 
                tr.CPM_TRAN_INFO, 
                tr.CPM_TRAN_FLAG,
                {$select_tambahan} 
                tr.CPM_TRAN_READ, 
                tr.CPM_TRAN_ID,
                TIMESTAMP
                FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
				INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
                {$join_tambahan}
                WHERE {$where} {$where2}
                GROUP BY pj.CPM_ID  ORDER BY pj.CPM_NO ASC";
            }
        } else if ($this->_idp == 3) {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            permen.nmrek,
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            pj.CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            INNER JOIN PATDA_REK_PERMEN13 permen ON pr.CPM_REKENING = permen.kdrek
            {$join_tambahan}
            WHERE {$where} {$where2}
            ORDER BY pj.CPM_NO ASC";
        } else if ($this->_idp == 4) {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            SUM(pj.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY
             pj.CPM_NO
            ORDER BY
                pj.CPM_NO ASC";
            //  GROUP BY
            // pr.CPM_NPWPD,
            // CPM_TAHUN_MASA_PAJAK,
            // CPM_BULAN,
            // pj.CPM_NO
        } else if ($this->_idp == 8) {
            $query = "SELECT
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            SUM(pj.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            permen.nmrek,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            LEFT JOIN patda_rek_permen13 permen ON permen.kdrek = pr.CPM_REKENING
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY
             pj.CPM_NO
            ORDER BY
                pj.CPM_NO ASC";
            //  GROUP BY
            // pr.CPM_NPWPD,
            // CPM_TAHUN_MASA_PAJAK,
            // CPM_BULAN,
            // pj.CPM_NO
        } elseif ($this->_idp == 6) { // jalan
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            atr.CPM_ATR_TAHUN_PAJAK as CPM_TAHUN_PAJAK , 
            MONTH(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK2,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            atr.CPM_ATR_TOTAL_OMZET as CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN patda_jalan_doc_atr atr ON atr.CPM_ATR_JALAN_ID = pj.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY CPM_NO
            ORDER BY pj.CPM_NO ASC";
        } elseif ($this->_idp == 1) {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            pj.CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY CPM_NO
            ORDER BY pj.CPM_NO ASC";
        } else {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            pj.CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY CPM_NO
            ORDER BY pj.CPM_NO ASC";
        }

        // if($_SERVER['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'){

        // echo "<pre>";
        // print_r($query);
        // die;
        // }
        $res = mysqli_query($this->Conn, $query);
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("Alfa System")
            ->setDescription("Alfatax")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        if ($this->_idp == 8) { //restoran
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                ->setCellValue('A6', 'NO.')
                ->setCellValue('B6', 'TGL SPTPD')
                ->setCellValue('C6', 'NO SPTPD')
                ->setCellValue('D6', 'NPWPD')
                ->setCellValue('F6', 'REKENING')
                ->setCellValue('G6', 'NAMA WAJIB PAJAK')
                ->setCellValue('H6', 'NAMA OBJEK PAJAK')
                ->setCellValue('I6', 'ALAMAT USAHA WP')
                ->setCellValue('I7', 'DESA/KELURAHAN')
                ->setCellValue('J7', 'KECAMATAN')
                ->setCellValue('K6', 'MASA PAJAK')
                ->setCellValue('L6', 'OMSET')
                ->setCellValue('M6', 'TARIF')
                ->setCellValue('N6', 'NILAI PAJAK');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
            $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
            $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
            $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
            $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
            $objPHPExcel->getActiveSheet()->mergeCells("G6:G7");
            $objPHPExcel->getActiveSheet()->mergeCells("I6:J6");
            $objPHPExcel->getActiveSheet()->mergeCells("H6:H7");
            $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
            $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
            $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
            $objPHPExcel->getActiveSheet()->mergeCells("O6:O7");
        } elseif ($this->_idp == 3) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                ->setCellValue('A6', 'NO.')
                ->setCellValue('B6', 'TGL SPTPD')
                ->setCellValue('C6', 'NO SPTPD')
                ->setCellValue('D6', 'NPWPD')
                ->setCellValue('F6', 'NAMA WAJIB PAJAK')
                ->setCellValue('G6', 'NAMA OBJEK PAJAK')
                ->setCellValue('H6', 'ALAMAT USAHA WP')
                ->setCellValue('H7', 'DESA/KELURAHAN')
                ->setCellValue('I7', 'KECAMATAN')
                ->setCellValue('J6', 'MASA PAJAK')
                ->setCellValue('K6', 'OMSET')
                ->setCellValue('L6', 'TARIF')
                ->setCellValue('M6', 'NILAI PAJAK');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
            $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
            $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
            $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
            $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
            $objPHPExcel->getActiveSheet()->mergeCells("H6:I6");
            $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
            $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
            $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
            $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
        } elseif ($this->_idp == 7) {
            // var_dump($_REQUEST['CPM_JENIS_PJK']);
            // die;
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                    ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                    ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                    ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                    ->setCellValue('A6', 'NO.')
                    ->setCellValue('B6', 'TGL SPTPD')
                    ->setCellValue('C6', 'NO SPTPD')
                    ->setCellValue('D6', 'NPWPD')
                    ->setCellValue('E6', 'REKENING')
                    ->setCellValue('G6', 'NAMA WAJIB PAJAK')
                    ->setCellValue('H6', 'NAMA OBJEK PAJAK')
                    ->setCellValue('I6', 'ALAMAT USAHA WP')
                    ->setCellValue('I7', 'DESA/KELURAHAN')
                    ->setCellValue('J7', 'KECAMATAN')
                    ->setCellValue('K6', 'MASA PAJAK')
                    ->setCellValue('L6', 'OMSET')
                    ->setCellValue('M6', 'TARIF')
                    ->setCellValue('N6', 'NILAI PAJAK');

                // Kolom header dinamis

                $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
                $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
                $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
                $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
                $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

                // judul kolom
                $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
                $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
                $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
                $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
                $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
                $objPHPExcel->getActiveSheet()->mergeCells("H6:I6");
                $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
                $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
                $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
                $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                    ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                    ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                    ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                    ->setCellValue('A6', 'NO.')
                    ->setCellValue('B6', 'TGL SPTPD')
                    ->setCellValue('C6', 'NO SPTPD')
                    ->setCellValue('D6', 'NPWPD')
                    ->setCellValue('E6', 'REKENING')
                    ->setCellValue('F6', 'NAMA WAJIB PAJAK')
                    ->setCellValue('G6', 'NAMA OBJEK PAJAK')
                    ->setCellValue('H6', 'ALAMAT USAHA WP')
                    ->setCellValue('H7', 'DESA/KELURAHAN')
                    ->setCellValue('I7', 'KECAMATAN')
                    ->setCellValue('J6', 'MASA PAJAK')
                    ->setCellValue('K6', 'OMSET')
                    ->setCellValue('L6', 'TARIF')
                    ->setCellValue('M6', 'NILAI PAJAK');

                $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
                $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
                $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
                $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
                $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

                // judul kolom
                $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
                $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
                $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
                $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
                $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
                $objPHPExcel->getActiveSheet()->mergeCells("G6:G7");
                $objPHPExcel->getActiveSheet()->mergeCells("H6:I6");
                $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
                $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
                $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
                $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
            }
        } else {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))
                ->setCellValue('A6', 'NO.')
                ->setCellValue('B6', 'TGL SPTPD')
                ->setCellValue('C6', 'NO SPTPD')
                ->setCellValue('D6', 'NPWPD')
                ->setCellValue('E6', 'NAMA WAJIB PAJAK')
                ->setCellValue('F6', 'NAMA OBJEK PAJAK')
                ->setCellValue('G6', 'ALAMAT USAHA WP')
                ->setCellValue('G7', 'DESA/KELURAHAN')
                ->setCellValue('H7', 'KECAMATAN')
                ->setCellValue('I6', 'MASA PAJAK')
                ->setCellValue('J6', 'OMSET')
                ->setCellValue('K6', 'TARIF')
                ->setCellValue('L6', 'NILAI PAJAK');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
            $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
            $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
            $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
            $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
            $objPHPExcel->getActiveSheet()->mergeCells("G6:H6");
            $objPHPExcel->getActiveSheet()->mergeCells("I6:I7");
            $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
            $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
            $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
        }

        if ($this->_idp == 8) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O6', 'PELAKSANA KEGIATAN'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("N6:N7");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            if ($this->_i == 4) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('O6', 'KODE BAYAR');
                $objPHPExcel->getActiveSheet()->mergeCells("O6:O7");
            }
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            }
        } elseif ($this->_idp == 3) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
        }

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $tab = $jns[$this->_s];

        $row = 8;
        $sumRows = mysqli_num_rows($res);
        $total_omzet = 0;
        $total_pajak = 0;
        // echo "<pre>";


        while ($rowData = mysqli_fetch_assoc($res)) {
            // var_dump($this->_idp);
            // die;
            if ($this->_idp == 8) {
                $jenis_tipe_pajak = $this->arr_tipe_pajak_restoran[$rowData['CPM_TIPE_PAJAK']];
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jenis_tipe_pajak);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $rowData['nmrek']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['CPM_TOTAL_PAJAK']);
                if ($this->_idp == 8) {

                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['PELAKSANA_KEGIATAN']);
                    if ($this->_i == 4) {
                        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, "'" . $rowData['payment_code']);
                    }
                }

                $row++;
            } elseif ($this->_idp == 7) {

                $jenis_tipe_pajak = $this->arr_tipe_pajak_reklame[$rowData['CPM_TYPE_PAJAK']];
                $tgl = ($rowData['CPM_TGL_LAPOR'] == NULL) ?  date('d/m/Y', strtotime($rowData['TIMESTAMP'])) : date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR']));

                if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                    $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $tgl, PHPExcel_Cell_DataType::TYPE_STRING);
                    // var_dump($tgl);
                    // die;
                    // $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, (date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR']) != NULL ? date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])) : date('d/m/Y', strtotime($rowData['TIMESTAMP'])))), PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['nmrek']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_MASA_PAJAK']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                } else {
                    $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $tgl, PHPExcel_Cell_DataType::TYPE_STRING);
                    // var_dump("soni");
                    // die;
                    // $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' .  $row, $rowData['nmrek']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_MASA_PAJAK']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                }

                $row++;
            } elseif ($this->_idp == 3) {
                $jenis_tipe_pajak = $this->arr_tipe_pajak_hotel[$rowData['CPM_TiPE_PAJAK']];
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['nmrek']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                $row++;
            } else {

                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TOTAL_PAJAK']);
                if ($this->_idp == 8) {

                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['PELAKSANA_KEGIATAN']);
                    if ($this->_i == 4) {
                        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "'" . $rowData['payment_code']);
                    }
                }

                $row++;
            }
        }
        // var_dump("looossss");
        // die;
        // query total sampai bulan lalu
        $total_omzet_bulan_lalu = 0;
        $total_pajak_bulan_lalu = 0;
        if ($periode_bulan != '') {
            //tambahan
            $pecahkan = explode('-', $periode_bulan);
            $tah = $pecahkan[0];
            $bul = (int) $pecahkan[1];
            $tahtah = $tah . '-01';
            //end tambahan
            if ($this->_idp == 8) {
                $tambah = "AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}'";
            }
            if ($bul != 1) {
                $bulan_lalu = date('Y-m', strtotime($periode_bulan . '-01 -1 month'));
                $query11 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
							FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
							INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
							INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
							WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahtah' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$tambah}{$where2} group by pj.CPM_ID) a";
                $res_prev = mysqli_query($this->Conn, $query11);
                if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                    $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                    $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
                }
            }
        } elseif ($cariBulan != '' && $cariBulan != 1) {
            switch (TRUE) {
                case ($cariTahun != ''):
                    $tahun = $cariTahun;
                    break;
                default:
                    $tahun = date('Y');
                    break;
            }
            $bulan_lalu = date($tahun . '-m', strtotime($cariBulan . '-01 -1 month'));
            $query12 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahun' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$where2} group by pj.CPM_ID) a";
            $res_prev = mysqli_query($this->Conn, $query12);
            if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
            }
        } elseif ($cariBulan = 1) {
            switch (TRUE) {
                case ($cariTahun != ''):
                    $tahun = $cariTahun - 1;
                    break;
                default:
                    $tahun = date('Y');
                    break;
            };
            $bulan_lalu = date($cariTahun);
            $query13 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahun-12' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$where2} group by pj.CPM_ID) a";
            $res_prev = mysqli_query($this->Conn, $query13);
            if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
            }
        }
        if ($this->_idp == 8) {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=SUM(L6:L' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, '=SUM(N6:N' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $total_omzet_bulan_lalu + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $total_pajak_bulan_lalu + 0);
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=K' . ($row - 2) . '+L' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, '=M' . ($row - 2) . '+N' . ($row - 1));
        } elseif ($this->_idp == 3) {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu);
            $row++;
            // var_dump($row, '=K' . ($row - 2) . '+K' . ($row - 1));
            // die;
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+K' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+M' . ($row - 1));
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu + 0);
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+K' . ($row - 1));
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+M' . ($row - 1));
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu + 0);
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+K' . ($row - 1));
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+M' . ($row - 1));
            }
        } else {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=SUM(J6:J' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=SUM(L6:L' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $total_omzet_bulan_lalu + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $total_pajak_bulan_lalu + 0);
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=J' . ($row - 2) . '+J' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=L' . ($row - 2) . '+L' . ($row - 1));
        }
        $lastRow = $row;
        $row++;
        $row++;
        $row++;


        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getActiveSheet()->getStyle('A1:Z7')->applyFromArray(
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
        // rata tengah col A-D
        $objPHPExcel->getActiveSheet()->getStyle('A1:D' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('K1:K' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // bold footer
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 5) . ':M' . ($row - 3))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        // border
        $objPHPExcel->getActiveSheet()->getStyle('A6:M' . $lastRow)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        // fill tabel header
        $objPHPExcel->getActiveSheet()->getStyle('A6:L7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L7')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getActiveSheet()->getStyle('J6:J' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
        $objPHPExcel->getActiveSheet()->getStyle('L6:L' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');

        if ($this->_idp == 8) {
            // border
            $objPHPExcel->getActiveSheet()->getStyle('N6:O' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->getStartColor()->setRGB('E4E4E4');

            $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('N6:N' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
            $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->getStartColor()->setRGB('E4E4E4');

            if ($this->_i == 4) {
                $objPHPExcel->getActiveSheet()->getStyle('O6:O' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->getStartColor()->setRGB('E4E4E4');
            }
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                // border
                $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
            } else {
                $objPHPExcel->getActiveSheet()->getStyle('L6:L7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('L6:L7')->getFill()->getStartColor()->setRGB('E4E4E4');
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_NONE);
            }
            if ($this->_i == 4) {
                $objPHPExcel->getActiveSheet()->getStyle('N6:N' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->getStartColor()->setRGB('E4E4E4');
            }
        } elseif ($this->_idp == 3) {
            // border
            $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), "Kepala Bidang Pengembangan dan Penetapan,");
        $NAMAKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NAMA');
        $NIPKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NIP');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 6), $NAMAKABID);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 7), "NIP. " . $NIPKABID);

        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':D' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 6) . ':D' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 7) . ':D' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6) . ':D' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );
        // echo $this->arr_pajak[$this->_idp] ;exit();
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, "Kalianda, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y'));
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 1), "Staf Bidang Pengembangan");
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 2), "dan Penetapan");
        if ($this->arr_pajak[$this->_idp] == "Restoran") {
            if ($_REQUEST['CPM_JENIS_PJK'] == 2) {

                $nmstaffkatering = $this->get_config_value('aPatda', 'STAFF_KATERING_NAMA');
                $nipstaffkatering = $this->get_config_value('aPatda', 'STAFF_KATERING_NIP');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffkatering);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffkatering);
            } else {

                $nmstaffrstrn = $this->get_config_value('aPatda', 'STAFF_RSTRN_NAMA');
                $nipstaffkrstrn = $this->get_config_value('aPatda', 'STAFF_RSTRN_NIP');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffrstrn);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffkrstrn);
                // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+6), "Rita Fitri WSR, S.H");
                // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+7), "NIP. 19830627 200901 2 006");
            }
        } elseif ($this->arr_pajak[$this->_idp] == "Air Bawah Tanah") {
            $nmstaffatr = $this->get_config_value('aPatda', 'STAFF_ATR_NAMA');
            $nipstaffatr = $this->get_config_value('aPatda', 'STAFF_ATR_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffatr);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffatr);
            // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+6), "NOVILIA TRIANI");
            //          $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+7), "");
        } elseif ($this->arr_pajak[$this->_idp] == "Hiburan") {
            $nmstaffhbrn = $this->get_config_value('aPatda', 'STAFF_HBRN_NAMA');
            $nipstaffhbrn = $this->get_config_value('aPatda', 'STAFF_HBRN_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffhbrn);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffhbrn);
        } elseif ($this->arr_pajak[$this->_idp] == "Hotel") {

            $nmstaffhtl = $this->get_config_value('aPatda', 'STAFF_HTL_NAMA');
            $nipstaffhtl = $this->get_config_value('aPatda', 'STAFF_HTL_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffhtl);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffhtl);
        } elseif ($this->arr_pajak[$this->_idp] == "Mineral Non Logam dan Batuan") {
            $nmstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NAMA');
            $nipstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffmnrl);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffmnrl);
        } elseif ($this->arr_pajak[$this->_idp] == "Penerangan Jalan") {
            $nmstaffppj = $this->get_config_value('aPatda', 'STAFF_PPJ_NAMA');
            $nipstaffppj = $this->get_config_value('aPatda', 'STAFF_PPJ_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffppj);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffppj);
        } elseif ($this->arr_pajak[$this->_idp] == "Reklame") {
            $nmstaffrklm = $this->get_config_value('aPatda', 'STAFF_RKLM_NAMA');
            $nipstaffrklm = $this->get_config_value('aPatda', 'STAFF_RKLM_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffrklm);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffrklm);
        } elseif ($this->arr_pajak[$this->_idp] == "Parkir") {
            $nmstaffprkr = $this->get_config_value('aPatda', 'STAFF_PRKR_NAMA');
            $nipstaffprkr = $this->get_config_value('aPatda', 'STAFF_PRKR_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffprkr);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffprkr);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), "EKA DARMAYANTI");
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), "NIP. 19770323 201407 2 004");
        }

        $objPHPExcel->getActiveSheet()->mergeCells('I' . $row . ':L' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 1) . ':L' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 2) . ':L' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 6) . ':L' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 7) . ':L' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('I' . ($row + 6) . ':L' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('I' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $row += 8;

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Mengetahui");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 1), "Kepala Badan Pendapatan Daerah");
        //$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 2), "dan Retribusi Daerah");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 6), "EVANS SAGGITA R., S.E., M.M.");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 7), "NIP. 19731130 200804 1001");
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $row . ':H' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 1) . ':H' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 2) . ':H' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 6) . ':H' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 7) . ':H' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6) . ':H' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 8) . ':L' . ($row + 7))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );

        // Rename sheet
        // $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);


        for ($x = "A"; $x <= "O"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            elseif ($x == 'K') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(15);
            elseif ($x == 'P' && $this->_idp == 8) $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(30);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pajak-' . strtolower($JENIS_PAJAK) . '-' . date('Ymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        // echo $this->arr_pajak[$this->_idp];
        // exit();
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }

    private function download_pajak_xls_rek_pajak()
    {
        // exit('asdsa');
        $selectedValues = explode(',', $_REQUEST['CPM_FILTER_V2']);
        $rekekningv2 = "'" . implode("','", $selectedValues) . "'";
        // var_dump($_REQUEST['CPM_FILTER_V2']);
        // die;
        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = "";

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }

        $where .= ") ";
        $_REQUEST['CPM_NPWPD'] = str_replace('.', '', $_REQUEST['CPM_NPWPD']);
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) 
                    OR TIMESTAMP >= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\", \" 00:00:00\")
                AND TIMESTAMP <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\", \" 23:59:59\")
                    ";
            $periode = date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR1'])) . ' s/d ' . date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR2']));
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }
        if ($this->_idp == 8 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 3 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 7 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND atr.CPM_ATR_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        }
        $where .= (isset($_REQUEST['CPM_FILTER_V2']) && $_REQUEST['CPM_FILTER_V2'] != "") ? " AND CPM_ATR_REKENING IN ( {$rekekningv2}) " : "";

        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $cariBulan = $_REQUEST['CPM_MASA_PAJAK'];
        $cariTahun = $_REQUEST['CPM_TAHUN_PAJAK'];
        if (isset($_REQUEST['CPM_TRIWULAN']) && $_REQUEST['CPM_TRIWULAN'] != "") {
            if ($_REQUEST['CPM_TRIWULAN'] == 1) {
                $where .= " AND CPM_MASA_PAJAK IN(1,2,3)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 2) {
                $where .= " AND CPM_MASA_PAJAK IN(4,5,6)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 3) {
                $where .= " AND CPM_MASA_PAJAK IN(7,8,9)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 4) {
                $where .= " AND CPM_MASA_PAJAK IN(10,11,12)";
            }
        }
        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
        if ($this->_idp == 8) {
            $select_tambahan = "pj.PELAKSANA_KEGIATAN,pj.CPM_TIPE_PAJAK, ";
            if ($this->_i == 4) {
                # code...
                $select_tambahan = "pj.PELAKSANA_KEGIATAN,gw.payment_code,pj.CPM_TIPE_PAJAK,";
                $join_tambahan = "LEFT JOIN simpatda_gw gw ON gw.id_switching = pj.CPM_ID";
            }
        } elseif ($this->_idp == 7) {
            $select_tambahan = "pj.CPM_TYPE_PAJAK, ";
        } elseif ($this->_idp == 3) {
            $select_tambahan = "pj.CPM_TiPE_PAJAK, ";
        }

        #query select list data
        if ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $query = "SELECT 
                pj.CPM_ID, 
                pj.CPM_NO, 
                pj.CPM_TAHUN_PAJAK, 
                permen.nmrek,
                MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
                CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                pj.CPM_AUTHOR, 
                pj.CPM_VERSION,
                pj.CPM_TOTAL_OMZET, 
                pj.CPM_TARIF_PAJAK, 
                pj.CPM_TOTAL_PAJAK,
                pr.CPM_NPWPD, 
                pr.CPM_NAMA_WP,
                pr.CPM_NAMA_OP,
                pr.CPM_REKENING,
                pr.CPM_KELURAHAN_OP,
                pr.CPM_KECAMATAN_OP, 
                tr.CPM_TRAN_STATUS, 
                tr.CPM_TRAN_DATE, 
                tr.CPM_TRAN_INFO, 
                tr.CPM_TRAN_FLAG,
                {$select_tambahan} 
                tr.CPM_TRAN_READ, 
                tr.CPM_TRAN_ID
                FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                    
				INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                -- INNER JOIN PATDA_REKLAME_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_REKLAME_ID
                INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
                {$join_tambahan}
                WHERE {$where} {$where2}
                GROUP BY CPM_ID
                ORDER BY pj.CPM_NO ASC";
            } else {
                $query = "SELECT 
                pj.CPM_ID, 
                pj.CPM_NO, 
                pj.CPM_TAHUN_PAJAK, 
                MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
                CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
                pj.CPM_AUTHOR, 
                pj.CPM_VERSION,
                pj.CPM_TOTAL_OMZET, 
                pj.CPM_TARIF_PAJAK, 
                pj.CPM_TOTAL_PAJAK,
                pr.CPM_NPWPD, 
                pr.CPM_NAMA_WP,
                pr.CPM_NAMA_OP,
                pr.CPM_REKENING,
                pr.CPM_KELURAHAN_OP,
                pr.CPM_KECAMATAN_OP, 
                tr.CPM_TRAN_STATUS, 
                tr.CPM_TRAN_DATE, 
                tr.CPM_TRAN_INFO, 
                tr.CPM_TRAN_FLAG,
                {$select_tambahan} 
                tr.CPM_TRAN_READ, 
                tr.CPM_TRAN_ID
                FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                
				INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                {$join_tambahan}
                WHERE {$where} {$where2}
                GROUP BY CPM_ID

                ORDER BY pj.CPM_NO ASC";
            }
        } else if ($this->_idp == 3) {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            permen.nmrek,
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            pj.CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            INNER JOIN PATDA_REK_PERMEN13 permen ON pr.CPM_REKENING = permen.kdrek
            {$join_tambahan}
            WHERE {$where} {$where2}
            ORDER BY pj.CPM_NO ASC";
        } else if ($this->_idp == 8 || $this->_idp == 4) {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            SUM(pj.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            GROUP BY
                pr.CPM_NPWPD,
                CPM_TAHUN_MASA_PAJAK,
                CPM_BULAN,
                pj.CPM_NO
            ORDER BY
                pr.CPM_KECAMATAN_OP,
                pr.CPM_NAMA_OP";
        } else {
            $query = "SELECT 
            pj.CPM_ID, 
            pj.CPM_NO, 
            pj.CPM_TAHUN_PAJAK, 
            MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, 
            pj.CPM_AUTHOR, 
            pj.CPM_VERSION,
            pj.CPM_TOTAL_OMZET, 
            pj.CPM_TARIF_PAJAK, 
            pj.CPM_TOTAL_PAJAK,
            pr.CPM_NPWPD, 
            pr.CPM_NAMA_WP,
            pr.CPM_NAMA_OP,
            pr.CPM_REKENING,
            pr.CPM_KELURAHAN_OP,
            pr.CPM_KECAMATAN_OP, 
            tr.CPM_TRAN_STATUS, 
            tr.CPM_TRAN_DATE, 
            tr.CPM_TRAN_INFO, 
            tr.CPM_TRAN_FLAG,
            {$select_tambahan} 
            tr.CPM_TRAN_READ, 
            tr.CPM_TRAN_ID
            FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
            INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
            {$join_tambahan}
            WHERE {$where} {$where2}
            ORDER BY pj.CPM_NO ASC";
        }

        // echo "<pre>";
        // print_r($query);
        // die;
        $res = mysqli_query($this->Conn, $query);
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("Alfa System")
            ->setDescription("Alfatax")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        if ($this->_idp == 8 || $this->_idp == 3) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                ->setCellValue('A3', 'BADAN PENDAPATAN  DAERAH')
                ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                ->setCellValue('A6', 'NO.')
                ->setCellValue('B6', 'TGL SPTPD')
                ->setCellValue('C6', 'NO SPTPD')
                ->setCellValue('D6', 'NPWPD')
                ->setCellValue('F6', 'NAMA WAJIB PAJAK')
                ->setCellValue('G6', 'NAMA OBJEK PAJAK')
                ->setCellValue('H6', 'ALAMAT USAHA WP')
                ->setCellValue('H7', 'DESA/KELURAHAN')
                ->setCellValue('I7', 'KECAMATAN')
                ->setCellValue('J6', 'MASA PAJAK')
                ->setCellValue('K6', 'OMSET')
                ->setCellValue('L6', 'TARIF')
                ->setCellValue('M6', 'NILAI PAJAK');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
            $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
            $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
            $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
            $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
            $objPHPExcel->getActiveSheet()->mergeCells("H6:I6");
            $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
            $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
            $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
            $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                    ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                    ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                    ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                    ->setCellValue('A6', 'NO.')
                    ->setCellValue('B6', 'TGL SPTPD')
                    ->setCellValue('C6', 'NO SPTPD')
                    ->setCellValue('D6', 'NPWPD')
                    ->setCellValue('F6', 'NAMA WAJIB PAJAK')
                    ->setCellValue('G6', 'NAMA OBJEK PAJAK')
                    ->setCellValue('H6', 'ALAMAT USAHA WP')
                    ->setCellValue('H7', 'DESA/KELURAHAN')
                    ->setCellValue('I7', 'KECAMATAN')
                    ->setCellValue('J6', 'MASA PAJAK')
                    ->setCellValue('K6', 'DETAIL PAJAK')
                    ->setCellValue('L6', 'OMSET')
                    ->setCellValue('M6', 'TARIF')
                    ->setCellValue('N6', 'NILAI PAJAK');

                $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
                $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
                $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
                $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
                $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

                // judul kolom
                $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
                $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
                $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
                $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
                $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
                $objPHPExcel->getActiveSheet()->mergeCells("H6:I6");
                $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
                $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
                $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
                $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
                $objPHPExcel->getActiveSheet()->mergeCells("N6:N7");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                    ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                    ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                    ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                    ->setCellValue('A6', 'NO.')
                    ->setCellValue('B6', 'TGL SPTPD')
                    ->setCellValue('C6', 'NO SPTPD')
                    ->setCellValue('D6', 'NPWPD')
                    ->setCellValue('E6', 'NAMA WAJIB PAJAK')
                    ->setCellValue('F6', 'NAMA OBJEK PAJAK')
                    ->setCellValue('G6', 'ALAMAT USAHA WP')
                    ->setCellValue('G7', 'DESA/KELURAHAN')
                    ->setCellValue('H7', 'KECAMATAN')
                    ->setCellValue('I6', 'MASA PAJAK')
                    ->setCellValue('J6', 'DETAIL PAJAK')
                    ->setCellValue('K6', 'OMSET')
                    ->setCellValue('L6', 'TARIF')
                    ->setCellValue('M6', 'NILAI PAJAK');

                $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
                $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
                $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
                $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
                $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

                // judul kolom
                $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
                $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
                $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
                $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
                $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
                $objPHPExcel->getActiveSheet()->mergeCells("G6:H6");
                $objPHPExcel->getActiveSheet()->mergeCells("I6:I7");
                $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
                $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
                $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
                $objPHPExcel->getActiveSheet()->mergeCells("M6:M7");
            }
        } else {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

                ->setCellValue('A6', 'NO.')
                ->setCellValue('B6', 'TGL SPTPD')
                ->setCellValue('C6', 'NO SPTPD')
                ->setCellValue('D6', 'NPWPD')
                ->setCellValue('E6', 'NAMA WAJIB PAJAK')
                ->setCellValue('F6', 'NAMA OBJEK PAJAK')
                ->setCellValue('G6', 'ALAMAT USAHA WP')
                ->setCellValue('G7', 'DESA/KELURAHAN')
                ->setCellValue('H7', 'KECAMATAN')
                ->setCellValue('I6', 'MASA PAJAK')
                ->setCellValue('J6', 'OMSET')
                ->setCellValue('K6', 'TARIF')
                ->setCellValue('L6', 'NILAI PAJAK');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
            $objPHPExcel->getActiveSheet()->mergeCells("A5:L5");

            // judul kolom
            $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
            $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
            $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
            $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
            $objPHPExcel->getActiveSheet()->mergeCells("G6:H6");
            $objPHPExcel->getActiveSheet()->mergeCells("I6:I7");
            $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
            $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
            $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
        }


        // $objPHPExcel->getActiveSheet()->mergeCells("N6:N7");
        // $objPHPExcel->getActiveSheet()->mergeCells("O6:O7");
        // $objPHPExcel->getActiveSheet()->mergeCells("P6:P7");
        if ($this->_idp == 8) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N6', 'PELAKSANA KEGIATAN'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("N6:N7");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            if ($this->_i == 4) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('O6', 'KODE BAYAR');
                $objPHPExcel->getActiveSheet()->mergeCells("O6:O7");
            }
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
                $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
            }
        } elseif ($this->_idp == 3) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', 'TIPE PAJAK'); #"CPM_TRAN_STATUS
            $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
        }


        /* if ($this->_s == 0) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', 'Status'); #"CPM_TRAN_STATUS
        }

        if ($this->_s == 4) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1', 'Keterangan'); #CPM_TRAN_INFO            
        } */


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $tab = $jns[$this->_s];

        $row = 8;
        $sumRows = mysqli_num_rows($res);
        $total_omzet = 0;
        $total_pajak = 0;
        // echo "<pre>";

        while ($rowData = mysqli_fetch_assoc($res)) {

            if ($this->_idp == 8) {
                $jenis_tipe_pajak = $this->arr_tipe_pajak_restoran[$rowData['CPM_TIPE_PAJAK']];

                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jenis_tipe_pajak);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                if ($this->_idp == 8) {

                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['PELAKSANA_KEGIATAN']);
                    if ($this->_i == 4) {
                        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, "'" . $rowData['payment_code']);
                    }
                }

                $row++;
            } elseif ($this->_idp == 7) {
                $query_rek = "SELECT * FROM PATDA_REKLAME_DOC_ATR  WHERE CPM_ATR_REKLAME_ID='{$rowData['CPM_ID']}'";
                $res_rek = mysqli_query($this->Conn, $query_rek);

                $rek_data = array();

                while ($rek_row = mysqli_fetch_assoc($res_rek)) {
                    $rek_data[] = $rek_row;
                }

                // if (count($rek_data) > 1) {
                $total_values = array();
                foreach ($rek_data as $rek_row) {
                    $total_values[] = number_format($rek_row['CPM_ATR_TOTAL'], 2, ',', '.');
                    $total_string = implode(' - ', $total_values);
                    if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_string);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $total_string);
                    }
                }
                // }sss
                $jenis_tipe_pajak = $this->arr_tipe_pajak_reklame[$rowData['CPM_TYPE_PAJAK']];
                if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                    $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['nmrek']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TOTAL_OMZET']);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['CPM_TOTAL_PAJAK']);
                } else {
                    $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_OP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                }

                $row++;
            } elseif ($this->_idp == 3) {
                $jenis_tipe_pajak = $this->arr_tipe_pajak_hotel[$rowData['CPM_TiPE_PAJAK']];
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['nmrek']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_TOTAL_PAJAK']);
                $row++;
            } else {
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, date('d/m/Y', strtotime($rowData['CPM_TGL_LAPOR'])), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_OP']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_TOTAL_OMZET']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TOTAL_PAJAK']);
                if ($this->_idp == 8) {

                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['PELAKSANA_KEGIATAN']);
                    if ($this->_i == 4) {
                        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "'" . $rowData['payment_code']);
                    }
                }

                $row++;
            }
        }



        // query total sampai bulan lalu
        $total_omzet_bulan_lalu = 0;
        $total_pajak_bulan_lalu = 0;
        if ($periode_bulan != '') {
            //tambahan
            $pecahkan = explode('-', $periode_bulan);
            $tah = $pecahkan[0];
            $bul = (int) $pecahkan[1];
            $tahtah = $tah . '-01';
            //end tambahan
            if ($this->_idp == 8) {
                $tambah = "AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}'";
            }
            if ($bul != 1) {
                $bulan_lalu = date('Y-m', strtotime($periode_bulan . '-01 -1 month'));
                $query11 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
							FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
							INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
							INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
							WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahtah' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$tambah}{$where2} group by pj.CPM_ID) a";
                $res_prev = mysqli_query($this->Conn, $query11);
                if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                    $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                    $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
                }
            }
        } elseif ($cariBulan != '' && $cariBulan != 1) {
            switch (TRUE) {
                case ($cariTahun != ''):
                    $tahun = $cariTahun;
                    break;
                default:
                    $tahun = date('Y');
                    break;
            }
            $bulan_lalu = date($tahun . '-m', strtotime($cariBulan . '-01 -1 month'));
            $query12 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahun' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$where2} group by pj.CPM_ID) a";
            $res_prev = mysqli_query($this->Conn, $query12);
            if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
            }
        } elseif ($cariBulan = 1) {
            switch (TRUE) {
                case ($cariTahun != ''):
                    $tahun = $cariTahun - 1;
                    break;
                default:
                    $tahun = date('Y');
                    break;
            };
            $bulan_lalu = date($cariTahun);
            $query13 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahun-12' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' {$where2} group by pj.CPM_ID) a";
            $res_prev = mysqli_query($this->Conn, $query13);
            if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                $total_omzet_bulan_lalu = $prev_data['CPM_TOTAL_OMZET'];
                $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
            }
        }

        if ($this->_idp == 8) {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu + 0);
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+K' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+M' . ($row - 1));
        } elseif ($this->_idp == 3) {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu);
            $row++;
            // var_dump($row, '=K' . ($row - 2) . '+K' . ($row - 1));
            // die;
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+K' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+M' . ($row - 1));
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=SUM(L6:L' . ($row - 1) . ')');
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, '=SUM(N6:N' . ($row - 1) . ')');
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $total_omzet_bulan_lalu + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $total_pajak_bulan_lalu + 0);
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=L' . ($row - 2) . '+J' . ($row - 1));
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, '=N' . ($row - 2) . '+L' . ($row - 1));
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $total_omzet_bulan_lalu + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak_bulan_lalu + 0);
                $row++;

                $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=K' . ($row - 2) . '+I' . ($row - 1));
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=M' . ($row - 2) . '+K' . ($row - 1));
            }
        } else {
            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=SUM(J6:J' . ($row - 1) . ')');
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=SUM(L6:L' . ($row - 1) . ')');
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $total_omzet_bulan_lalu + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $total_pajak_bulan_lalu + 0);
            $row++;

            $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=J' . ($row - 2) . '+J' . ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=L' . ($row - 2) . '+L' . ($row - 1));
        }

        $lastRow = $row;
        $row++;
        $row++;
        $row++;


        /** style **/
        // judul dok + judul tabel

        // rata tengah col A-D
        $objPHPExcel->getActiveSheet()->getStyle('A1:D' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('K1:K' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // bold footer
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 5) . ':L' . ($row - 3))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        // border
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . $lastRow)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $objPHPExcel->getActiveSheet()->getStyle('A1:N7')->applyFromArray(
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

            $objPHPExcel->getActiveSheet()->getStyle('A6:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A6:N7')->getFill()->getStartColor()->setRGB('E4E4E4');
            $objPHPExcel->getActiveSheet()->getStyle('J6:J' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
            $objPHPExcel->getActiveSheet()->getStyle('L6:L' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
        } else {
            $objPHPExcel->getActiveSheet()->getStyle('A1:M7')->applyFromArray(
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
            $objPHPExcel->getActiveSheet()->getStyle('A6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
            $objPHPExcel->getActiveSheet()->getStyle('J6:J' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
            $objPHPExcel->getActiveSheet()->getStyle('M6:M' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
        }


        if ($this->_idp == 8) {
            // border
            $objPHPExcel->getActiveSheet()->getStyle('N6:N' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->getStartColor()->setRGB('E4E4E4');

            $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');

            if ($this->_i == 4) {
                $objPHPExcel->getActiveSheet()->getStyle('O6:O' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('O6:O7')->getFill()->getStartColor()->setRGB('E4E4E4');
            }
        } elseif ($this->_idp == 7) {
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                // border
                $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
            } else {
                $objPHPExcel->getActiveSheet()->getStyle('L6:L7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('L6:L7')->getFill()->getStartColor()->setRGB('E4E4E4');
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_NONE);
            }
            if ($this->_i == 4) {
                $objPHPExcel->getActiveSheet()->getStyle('N6:N' . $lastRow)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('N6:N7')->getFill()->getStartColor()->setRGB('E4E4E4');
            }
        } elseif ($this->_idp == 3) {
            // border
            $objPHPExcel->getActiveSheet()->getStyle('M6:M' . $lastRow)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('M6:M7')->getFill()->getStartColor()->setRGB('E4E4E4');
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), "Kepala Bidang Pengembangan dan Penetapan,");
        $NAMAKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NAMA');
        $NIPKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NIP');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 6), $NAMAKABID);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 7), "NIP. " . $NIPKABID);

        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':D' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 6) . ':D' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 7) . ':D' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6) . ':D' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, "Kalianda, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y'));
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 1), "Staf Bidang Pengembangan");
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 2), "dan Penetapan");
        if ($this->arr_pajak[$this->_idp] == "Restoran") {
            if ($_REQUEST['CPM_JENIS_PJK'] == 2) {

                $nmstaffkatering = $this->get_config_value('aPatda', 'STAFF_KATERING_NAMA');
                $nipstaffkatering = $this->get_config_value('aPatda', 'STAFF_KATERING_NIP');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffkatering);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffkatering);
            } else {

                $nmstaffrstrn = $this->get_config_value('aPatda', 'STAFF_RSTRN_NAMA');
                $nipstaffkrstrn = $this->get_config_value('aPatda', 'STAFF_RSTRN_NIP');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffrstrn);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffkrstrn);
            }
        } elseif ($this->arr_pajak[$this->_idp] == "Air Bawah Tanah") {
            $nmstaffatr = $this->get_config_value('aPatda', 'STAFF_ATR_NAMA');
            $nipstaffatr = $this->get_config_value('aPatda', 'STAFF_ATR_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffatr);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffatr);
            // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+6), "NOVILIA TRIANI");
            //          $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+7), "");
        } elseif ($this->arr_pajak[$this->_idp] == "Hiburan") {
            $nmstaffhbrn = $this->get_config_value('aPatda', 'STAFF_HBRN_NAMA');
            $nipstaffhbrn = $this->get_config_value('aPatda', 'STAFF_HBRN_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffhbrn);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffhbrn);
        } elseif ($this->arr_pajak[$this->_idp] == "Hotel") {

            $nmstaffhtl = $this->get_config_value('aPatda', 'STAFF_HTL_NAMA');
            $nipstaffhtl = $this->get_config_value('aPatda', 'STAFF_HTL_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffhtl);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffhtl);
        } elseif ($this->arr_pajak[$this->_idp] == "Mineral Non Logam dan Batuan") {
            $nmstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NAMA');
            $nipstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffmnrl);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffmnrl);
        } elseif ($this->arr_pajak[$this->_idp] == "Penerangan Jalan") {
            $nmstaffppj = $this->get_config_value('aPatda', 'STAFF_PPJ_NAMA');
            $nipstaffppj = $this->get_config_value('aPatda', 'STAFF_PPJ_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffppj);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffppj);
        } elseif ($this->arr_pajak[$this->_idp] == "Reklame") {
            $nmstaffrklm = $this->get_config_value('aPatda', 'STAFF_RKLM_NAMA');
            $nipstaffrklm = $this->get_config_value('aPatda', 'STAFF_RKLM_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffrklm);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffrklm);
        } elseif ($this->arr_pajak[$this->_idp] == "Parkir") {
            $nmstaffprkr = $this->get_config_value('aPatda', 'STAFF_PRKR_NAMA');
            $nipstaffprkr = $this->get_config_value('aPatda', 'STAFF_PRKR_NIP');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffprkr);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffprkr);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), "EKA DARMAYANTI");
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), "NIP. 19770323 201407 2 004");
        }

        $objPHPExcel->getActiveSheet()->mergeCells('I' . $row . ':L' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 1) . ':L' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 2) . ':L' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 6) . ':L' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 7) . ':L' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('I' . ($row + 6) . ':L' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('I' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $row += 8;

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Mengetahui");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 1), "Kepala Badan Pendapatan Daerah");
        //$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 2), "dan Retribusi Daerah");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 6), "EVANS SAGGITA R., S.E., M.M.");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 7), "NIP. 19731130 200804 1001");
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $row . ':H' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 1) . ':H' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 2) . ':H' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 6) . ':H' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 7) . ':H' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6) . ':H' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 8) . ':L' . ($row + 7))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );

        for ($x = "A"; $x <= "O"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            elseif ($x == 'K') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(15);
            elseif ($x == 'M' && $this->_idp == 8) $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(30);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pajak-' . strtolower($JENIS_PAJAK) . '-' . date('Ymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }

    private function download_pajak_xls_minerba()
    {
        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR1'])) . ' s/d ' . date('d/m/Y', strtotime($_REQUEST['CPM_TGL_LAPOR2']));
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }

        if (isset($_REQUEST['CPM_TRIWULAN']) && $_REQUEST['CPM_TRIWULAN'] != "") {
            if ($_REQUEST['CPM_TRIWULAN'] == 1) {
                $where .= " AND CPM_MASA_PAJAK IN(1,2,3)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 2) {
                $where .= " AND CPM_MASA_PAJAK IN(4,5,6)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 3) {
                $where .= " AND CPM_MASA_PAJAK IN(7,8,9)";
            } elseif ($_REQUEST['CPM_TRIWULAN'] == 4) {
                $where .= " AND CPM_MASA_PAJAK IN(10,11,12)";
            }
        }

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

        #query select list data
        $query = "SELECT 
                    pj.CPM_TGL_LAPOR AS TGL_LAPOR, 
                    pj.CPM_ID, 
                    pj.CPM_NO, 
                    pj.CPM_TAHUN_PAJAK, 
                    MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
					YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_TAHUN_MASA_PAJAK,
                    CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                    YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
                    YEAR(STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_TGL_LAPOR, 
                    pj.CPM_AUTHOR, 
                    pj.CPM_VERSION,
                    pj.CPM_TOTAL_OMZET ,
					pj.CPM_DPP,					
                    pj.CPM_TARIF_PAJAK, 
                    pj.CPM_TOTAL_PAJAK, 
                    pr.CPM_NPWPD, 
                    pr.CPM_NAMA_WP,
                    pr.CPM_NAMA_OP,
                    pr.CPM_REKENING,
                    pr.CPM_KELURAHAN_OP,
                    pr.CPM_KECAMATAN_OP, 
                    tr.CPM_TRAN_STATUS, 
                    tr.CPM_TRAN_DATE, 
                    tr.CPM_TRAN_INFO, 
                    tr.CPM_TRAN_FLAG, 
                    tr.CPM_TRAN_READ, 
                    tr.CPM_TRAN_ID
                    FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
                    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                    LEFT JOIN simpatda_gw gw ON gw.id_switching = pj.CPM_ID
                    WHERE {$where}
                    GROUP BY
                        CPM_BULAN,
                        CPM_NPWPD,
                        CPM_YEAR,
                        pj.CPM_NO
                    ORDER BY
                        pj.CPM_NO ASC";

        // echo"<pre>";
        // print_r($query);
        // die;

        // echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("bphtb")
            ->setLastModifiedBy("bphtb")
            ->setTitle("bphtb")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords(bphtb);

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'DAFTAR SPTPD PAJAK ' . strtoupper($this->arr_pajak[$this->_idp]) . ' ' . $JENIS_LAPOR)
            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', ($periode != '' ? 'PERIODE ' . $periode : 'PERIODE SAMPAI ' . date('d/m/Y')))

            ->setCellValue('A6', 'NO.')
            ->setCellValue('B6', 'TGL SPTPD')
            ->setCellValue('C6', 'NO SPTPD')
            ->setCellValue('D6', 'NPWPD')
            ->setCellValue('E6', 'WAJIB PAJAK')
            ->setCellValue('F6', 'OBJEK PAJAK')
            ->setCellValue('G6', 'DESA/KELURAHAN')
            ->setCellValue('H6', 'KECAMATAN')
            ->setCellValue('I6', 'MASA PAJAK')
            ->setCellValue('J6', 'OMSET')
            ->setCellValue('K6', 'TARIF')
            ->setCellValue('L6', 'PAJAK MINERBA');

        // judul dok
        $objPHPExcel->getActiveSheet()->mergeCells("A1:L1");
        $objPHPExcel->getActiveSheet()->mergeCells("A2:L2");
        $objPHPExcel->getActiveSheet()->mergeCells("A3:L3");
        $objPHPExcel->getActiveSheet()->mergeCells("A4:L4");
        // $objPHPExcel->getActiveSheet()->mergeCells("A5:K5");

        // judul kolom
        $objPHPExcel->getActiveSheet()->mergeCells("A6:A7");
        $objPHPExcel->getActiveSheet()->mergeCells("B6:B7");
        $objPHPExcel->getActiveSheet()->mergeCells("C6:C7");
        $objPHPExcel->getActiveSheet()->mergeCells("D6:D7");
        $objPHPExcel->getActiveSheet()->mergeCells("E6:E7");
        $objPHPExcel->getActiveSheet()->mergeCells("F6:F7");
        $objPHPExcel->getActiveSheet()->mergeCells("G6:G7");
        $objPHPExcel->getActiveSheet()->mergeCells("H6:H7");
        $objPHPExcel->getActiveSheet()->mergeCells("I6:I7");
        $objPHPExcel->getActiveSheet()->mergeCells("J6:J7");
        $objPHPExcel->getActiveSheet()->mergeCells("K6:K7");
        $objPHPExcel->getActiveSheet()->mergeCells("L6:L7");
        // $objPHPExcel->getActiveSheet()->mergeCells("N6:N7");
        // $objPHPExcel->getActiveSheet()->mergeCells("O6:O7");
        // $objPHPExcel->getActiveSheet()->mergeCells("P6:P7");

        /* if ($this->_s == 0) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', 'Status'); #"CPM_TRAN_STATUS
        }

        if ($this->_s == 4) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1', 'Keterangan'); #CPM_TRAN_INFO            
        } */


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
        $tab = $jns[$this->_s];
        $jml = 0;

        $row = 8;
        $sumRows = mysqli_num_rows($res);
        $total_omzet = 0;
        $total_pajak = 0;

        // echo "<pre>";
        while ($rowData = mysqli_fetch_assoc($res)) {
            //var_dump($rowData);die;
            $masa = isset($triwulan[$rowData['CPM_BULAN']]) ? strtoupper($triwulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK'] : strtoupper($this->arr_bulan[$rowData['CPM_BULAN']]) . ' ' . $rowData['CPM_TAHUN_MASA_PAJAK'];
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['TGL_LAPOR'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_NAMA_OP']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $this->get_nama_kelurahan($rowData['CPM_KELURAHAN_OP']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $this->get_nama_kecamatan($rowData['CPM_KECAMATAN_OP']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $masa);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_DPP'] == 0 ? 'NIHIL' : $rowData['CPM_DPP']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, number_format($rowData['CPM_TARIF_PAJAK'], 0) . '%');
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TOTAL_PAJAK'] == 0 ? 'NIHIL' : $rowData['CPM_TOTAL_PAJAK']);
            $jml++;
            $row++;
        }

        // query total sampai bulan lalu
        $total_omzet_bulan_lalu = 0;
        $total_pajak_bulan_lalu = 0;

        if ($periode_bulan != '') {
            //tambahan
            $pecahkan = explode('-', $periode_bulan);
            $tah = $pecahkan[0];
            $bul = (int) $pecahkan[1];
            $tahtah = $tah . '-01';
            //end tambahan
            if ($bul != 1) {
                $bulan_lalu = date('Y-m', strtotime($periode_bulan . '-01 -1 month'));
                $query12 = "SELECT SUM(a.CPM_TOTAL_OMZET) as CPM_TOTAL_OMZET, SUM(a.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK, SUM(a.CPM_DPP) as CPM_DPP from (SELECT pj.CPM_TOTAL_OMZET, pj.CPM_TOTAL_PAJAK, pj.CPM_DPP
							FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj
							INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
							INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
							WHERE tr.CPM_TRAN_STATUS = '5' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')>='$tahtah' AND DATE_FORMAT(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"), '%Y-%m')<='$bulan_lalu' group by pj.CPM_ID) a";
                $res_prev = mysqli_query($this->Conn, $query12);
                if ($res_prev && $prev_data = mysqli_fetch_assoc($res_prev)) {
                    $total_omzet_bulan_lalu = $prev_data['CPM_DPP']; //$prev_data['CPM_TOTAL_OMZET'];
                    $total_pajak_bulan_lalu = $prev_data['CPM_TOTAL_PAJAK'];
                }
            }
        }



        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH BULAN INI");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=SUM(J6:J' . ($row - 1) . ')');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=SUM(L6:L' . ($row - 1) . ')');
        $row++;

        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN LALU ");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $total_omzet_bulan_lalu);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $total_pajak_bulan_lalu + 0);
        $row++;

        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "JUMLAH S/D BULAN INI");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=J' . ($row - 2) . '+J' . ($row - 1));
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, '=L' . ($row - 2) . '+L' . ($row - 1));

        $lastRow = $row;
        $rowFooter = array(5, 3);
        if ($periode_bulan != '') {
            $row += 2;
            $bl = explode('-', $periode_bulan);
            $bulan_tahun = $this->arr_bulan[(int)$bl[1]] . ' ' . $bl[0];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'PADA BULAN ' . strtoupper($bulan_tahun) . ' SPTPD DITERBITKAN SEBANYAK = ' . $jml . ' SPTPD');
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':F' . $row);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':F' . $row)->getFont()->setItalic(true);
            $lastRow = $row - 2;
            $rowFooter = array(7, 5);
        }

        $row++;
        $row++;
        $row++;


        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getActiveSheet()->getStyle('A1:L7')->applyFromArray(
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
        // $objPHPExcel->getActiveSheet()->getStyle('A6:K7')->getAlignment()->setWrapText(true);
        // rata tengah data col A-D
        $objPHPExcel->getActiveSheet()->getStyle('A1:D' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('K1:K' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('J1:J' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('L1:L' . ($row - 6))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // bold footer
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - $rowFooter[0]) . ':L' . ($row - $rowFooter[1]))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - $rowFooter[0]) . ':I' . ($row - $rowFooter[1]))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // border
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . $lastRow)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );


        // fill tabel header
        $objPHPExcel->getActiveSheet()->getStyle('A6:L7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L7')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getActiveSheet()->getStyle('J6:J' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');
        $objPHPExcel->getActiveSheet()->getStyle('L6:L' . ($row - 3))->getNumberFormat()->setFormatCode('#,##0');


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), "Kepala Bidang Pengembangan dan Penetapan,");
        //$objPHPExcel->getActiveSheet()->setCellValue('A'.($row+2), "Pengembangan dan Penetapan");


        $NAMAKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NAMA');
        $NIPKABID = $this->get_config_value('aPatda', 'KABID_PNMBGN_PNTPN_NIP');
        // $objPHPExcel->getActiveSheet()->setCellValue('A'.($row+6), "HARISWANDA, SE,. MM");
        // $objPHPExcel->getActiveSheet()->setCellValue('A'.($row+7), "NIP. 19680606 198803 1 002");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 6), $NAMAKABID);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 7), "NIP. " . $NIPKABID);

        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':D' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 6) . ':D' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 7) . ':D' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6) . ':D' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, "Kalianda, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y'));
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 1), "Staf Bidang Pengembangan");
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 2), "dan Penetapan");


        $nmstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NAMA');
        $nipstaffmnrl = $this->get_config_value('aPatda', 'STAFF_MNRL_NIP');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 6), $nmstaffmnrl);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row + 7), $nipstaffmnrl);

        // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+6), "NOVILIA TRIANI");
        // $objPHPExcel->getActiveSheet()->setCellValue('H'.($row+7), "");
        $objPHPExcel->getActiveSheet()->mergeCells('I' . $row . ':L' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 1) . ':L' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 2) . ':L' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 6) . ':L' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($row + 7) . ':L' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('I' . ($row + 6) . ':L' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('K' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );

        $row += 8;

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Mengetahui");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 1), "Kepala Badan Pendapatan Daerah");
        //$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 2), "dan Retribusi Daerah");

        // $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 6), "Drs. BURHANUDDIN, MM");
        // $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 7), "NIP. 19630310 198411 1 002");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 6), "EVANS SAGGITA R., S.E., M.M.");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row + 7), "NIP. 19731130 200804 1001");
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $row . ':H' . $row);
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 1) . ':H' . ($row + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 2) . ':H' . ($row + 2));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 6) . ':H' . ($row + 6));
        $objPHPExcel->getActiveSheet()->mergeCells('E' . ($row + 7) . ':H' . ($row + 7));
        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6) . ':H' . ($row + 7))->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 6))->applyFromArray(
            array(
                'font' => array(
                    'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row - 8) . ':L' . ($row + 7))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);


        for ($x = "A"; $x <= "L"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            elseif ($x == 'G') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(18);
            elseif ($x == 'H') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(17);
            elseif ($x == 'J') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(15);
            elseif ($x == 'K') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(8);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pajak-' . strtolower($JENIS_PAJAK) . '-' . date('Ymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }


    function download_pajak_status_xls()
    {
        if ($this->_s == "skpdkb") {
            $this->download_pajak_status_skpdkb_xls();
        } else if ($this->_s == "stpd") {
            $this->download_pajak_status_stpd_xls();
        } else {
            $this->download_pajak_status_sptpd_xls();
        }
    }

    function download_pajak_status_sptpd_xls()
    {

        $PAJAK = strtoupper($this->arr_idpajak[$this->_i]);

        $where = "(";
        $where .= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR ";
        $where .= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')";
        $where .= ") ";

        $where .= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
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
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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

    function download_pajak_status_skpdkb_xls()
    {

        $where = "(";
        $where .= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR ";
        $where .= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        $where .= ") ";

        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND s.CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

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
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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

    function download_pajak_status_stpd_xls()
    {

        $where = "(";
        $where .= " (tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (2,3,4,5)) OR ";
        $where .= " (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        $where .= ") ";

        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND s.CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

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
            ->setTitle("")
            ->setSubject("bphtb")
            ->setDescription("bphtb")
            ->setKeywords("");

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

    protected function getTanggalPenetapan($id_pajak, $id)
    {
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



    public function formatDateForDokumen($dmY)
    {
        return intval(substr($dmY, 0, 2)) . " " . $this->arr_bulan[(int)substr($dmY, 3, 2)] . " " . substr($dmY, 6, 4);
    }

    /*
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
		
		function tgl_indo($tglcetak){
				$bulan = array (
					1 =>   'Jan',
					'Feb',
					'Mar',
					'Apr',
					'Mei',
					'Jun',
					'Jul',
					'Agu',
					'Sep',
					'Okt',
					'Nov',
					'Des'
				);
				$pecahkan = explode('-', $tglcetak);
				
				// variabel pecahkan 0 = tahun
				// variabel pecahkan 1 = bulan
				// variabel pecahkan 2 = tanggal
			 
				return $pecahkan[2] . '/' . $bulan[ (int)$pecahkan[1] ] . '/' . $pecahkan[0];
			}
			$tglcetak = date('Y-m-d');
			$tgl_cetak = tgl_indo($tglcetak);

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
                        <td width=\"400\">Terbilang :<br> <p align=\"center\">{$this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])} rupiah</p>
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

				<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
                </table>";

        ob_clean();

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('spppd');
        $pdf->SetKeywords('');
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
    }*/


    public function print_sspd()
    {
        // var_dump('asdas');die;
        ob_start();
        ob_end_clean();
        global $sRootPath, $qrisLib;
        require_once("{$sRootPath}inc/payment/sayit.php");

        // print_r($_POST['function']);exit;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();
        $arr_jenis = array(
            1 => "AIRBAWAHTANAH",
            2 => "HIBURAN",
            3 => "HOTEL",
            4 => "MINERAL",
            5 => "PARKIR",
            6 => "JALAN",
            7 => "REKLAME",
            8 => "RESTORAN",
            9 => "WALET"
        );
        $JENIS_PAJAK = $arr_jenis[$DATA['pajak']['CPM_JENIS_PAJAK']];
        if (empty($DATA['pajak']['CPM_ID'])) {
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


        $kd_reks = $DATA['profil']['CPM_REKENING'];
        $tarif = $DATA['pajak']['ARR_REKENING'][$kd_reks]['tarif'];
        $tgl_jth_tempo = $DATA['pajak']['CPM_TGL_JATUH_TEMPO'];
       

        if ($kd_reks == '4.1.01.12.01') {
            $tarif_pajak = '';
        } else {
            $tarif_pajak = 'X ' . $tarif . '%';
        }

        $TGL_PENETAPAN = $this->getTanggalPenetapan($this->id_pajak, $this->CPM_ID);

        // $query = "UPDATE PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN SET CPM_TRAN_PRINT = '1' WHERE CPM_TRAN_{$JENIS_PAJAK}_ID='{$this->_id}' AND CPM_TRAN_STATUS='5'";
        // mysql_query($query);

        $BULAN_PAJAK = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
        $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$BULAN_PAJAK}";
        $KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
            $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
            $PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);
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
        //mysql_select_db($dbName, $Conn_gw);;

        $gw = $this->get_gw_byid($Conn_gw, $this->CPM_ID);
        // var_dump($gw);exit;
        /// Add QRIS By d3Di ================================================
        $id_switching = $gw->id_switching;
        $datetimenow = date('Y-m-d H:i:s');
        $query4 = "SELECT qr FROM simpatda_qris WHERE id_switching='$id_switching' AND expired_date_time>='$datetimenow' ORDER BY id DESC LIMIT 0, 1";
        $r = mysqli_query($Conn_gw, $query4);
        $nx = mysqli_num_rows($r);

        $QRCodeSVG = false;
        if ($nx > 0 && $gw->payment_flag == 0) {
            $r = mysqli_fetch_array($r);
            $QRCodeSVG = $qrisLib->getBarcodeSVG($r['qr'], 'QRCODE', 3, 3);
            $icoQRIS = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="210mm" height="77.5mm" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 21000 7750" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs> <style type="text/css"> <![CDATA[ .fil0 {fill:black;fill-rule:nonzero} ]]> </style> </defs>
                            <g id="__x0023_Layer_x0020_1">
                                <metadata id="CorelCorpID_0Corel-Layer"/>
                                <path class="fil0" d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z"/>
                            </g>
                        </svg>';
        }
        //======================================================================
        // echo '<div style="width:20px;margin-left:20px">'.$img.'</div>';

        // print_r($gw);exit;
        

        $PAYMENT_CODE_BANK = $gw->periode;
        $PAYMENT_CODE = $gw->payment_code;
        $DENDA = !empty($gw->simpatda_denda) ? $gw->patda_denda : 0;

        $arr_norek = array(
            1 => $config['PATDA_NO_REK_AIRBAWAHTANAH'],
            2 => $config['PATDA_NO_REK_HIBURAN'],
            3 => $config['PATDA_NO_REK_HOTEL'],
            4 => $config['PATDA_NO_REK_MINERBA'],
            5 => $config['PATDA_NO_REK_PARKIR'],
            6 => $config['PATDA_NO_REK_JALAN'],
            7 => $config['PATDA_NO_REK_REKLAME'],
            8 => $config['PATDA_NO_REK_RESTORAN'],
            9 => $config['PATDA_NO_REK_WALET'],
        );
        $NO_REK_PAJAK = $arr_norek[$DATA['pajak']['CPM_JENIS_PAJAK']];

        // echo '<pre>';
        // print_r($DATA['pajak_atr']['0']);
        // echo '</pre>';die;

        // var_dump($DATA['pajak_atr']['0']['CPM_ATR_DPP']);
        //         die;
        if ($JENIS_PAJAK == "JALAN") {
            $TAHUN_PAJAK = $DATA['pajak_atr']['0']['CPM_ATR_TAHUN_PAJAK'];
            $MASA_PAJAK1 = $DATA['pajak_atr']['0']['CPM_ATR_MASA_PAJAK1'];
            $MASA_PAJAK2 = $DATA['pajak_atr']['0']['CPM_ATR_MASA_PAJAK2'];
            $CPM_DPP = $DATA['pajak_atr']['0']['CPM_ATR_DPP'];
        } else {
            $TAHUN_PAJAK = $DATA['pajak']['CPM_TAHUN_PAJAK'];
            $MASA_PAJAK1 = $DATA['pajak']['CPM_MASA_PAJAK1'];
            $MASA_PAJAK2 = $DATA['pajak']['CPM_MASA_PAJAK2'];
            $CPM_DPP = $DATA['pajak']['CPM_DPP'];
        }
// var_dump($DATA['pajak']['CPM_JENIS_PAJAK']);die;
        $TOTAL = $DATA['pajak']['CPM_TOTAL_PAJAK'];
        if ($DATA['pajak']['CPM_JENIS_PAJAK'] == 1) {
           
            $total_pajak = $DATA['pajak']['CPM_DPP']; 
        }else{
            $total_pajak = $DATA['pajak']['CPM_BAYAR_TERUTANG'];
        }



        


        if (isset($gw) && !empty($gw)) {
            if ($gw->payment_flag == '1') {
                $TOTAL = $gw->patda_total_bayar;
                $DENDA = $gw->patda_denda;
            } else{
                $persen_denda = $this->get_persen_denda($gw->expired_date);
                $DENDA = ($persen_denda / 100) * $total_pajak;
                $TOTAL = $total_pajak + $DENDA;
                // var_dump($persen_denda);die;
            }
        }

        if (isset($gw) && !empty($gw)) {
			if ($gw->payment_flag == '1') {
				$TOTAL = $gw->patda_total_bayar;
				$DENDA = $gw->patda_denda;
			} else{
				$persen_denda = $this->get_persen_denda($gw->expired_date);
				$DENDA = ($persen_denda / 100) * $total_pajak;
				$TOTAL = $total_pajak + $DENDA + $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
			}
		}
        // $TOTAL = $TOTAL;
        // var_dump($DENDA);die;
     
        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr valign=\"top\">
                                    <th valign=\"top\" width=\"450\" align=\"center\">
                                        <table border=\"0\">
                                            <tr>
                                                <td width=\"80\">&nbsp;</td>
                                                <td width=\"350\">
                                                    <strong>
                                                    " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                                    " . strtoupper($NAMA_PENGELOLA) . "<br /><br />        
                                                    </strong>
                                                    <font class=\"normal\">{$JALAN}<br/>
                                                    {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                                </td>
                                            </tr>
                                        </table>
                                    </th>
                                    <th width=\"260\">
										<div align=\"center\">
                                        <span style=\"margin:0px;!important;font-size:50px;font-weight:bold\">SSPD</span><br/>
                                        <strong>
                                        (SURAT SETORAN
                                        PAJAK DAERAH)
                                        </strong>
										</div>
										<br/>
										<b>Tahun &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;: {$TAHUN_PAJAK}<br/>
										Kode Bayar : {$PAYMENT_CODE}
										</b>
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td><table border=\"0\" cellpadding=\"5\">
                                
                                <tr>
                                    <td><table width=\"700\" border=\"0\" cellpadding=\"2\">
									        <tr>
                                                <td width=\"150\">Nomor</td>
                                                <td width=\"560\" colspan=\"3\">: {$DATA['pajak']['CPM_NO_SSPD']}</td>
                                            </tr>
											<tr>
                                                <td>Tanggal Jatuh Tempo</td>
                                                <td colspan=\"3\">: " . strtoupper(date('d-m-Y', strtotime($tgl_jth_tempo))) . "</td>
                                            </tr>
                                            <!--
                                            <tr style=\"vertical-align: top\">
                                                <td>Tanggal Penetapan</td>
                                                <td colspan=\"3\">: " . strtoupper($this->formatDateForDokumen($TGL_PENETAPAN)) . "</td>
                                            </tr> -->
											
                                            <tr>
                                                <td>Nama</td>
                                                <td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_NAMA_WP']) . "</td>
                                            </tr>
                                            <tr style=\"vertical-align: top\">
                                                <td>Alamat</td>
                                                <td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_ALAMAT_WP']) . "</td>
                                            </tr>
                                            <tr>
                                                <td>Nama Usaha</td>
                                                <td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_NAMA_OP']) . "</td>
                                            </tr>
                                            <tr style=\"vertical-align: top\">
                                                <td>Alamat Usaha</td>
                                                <td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_ALAMAT_OP']) . "</td>
                                            </tr>
                                            <tr>
                                                <td>NPWPD</td>
                                                <td colspan=\"3\">: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
                                            </tr>
                                            <tr>
                                                <td>Menyetor Berdasarkan</td>
                                                <td colspan=\"3\">: [_] SKPD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [_] STPD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [_] Lain-lain</td>
                                            </tr> 
											<tr>
                                                <td></td>
												<td colspan=\"3\">&nbsp; [_] SKPDT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [_] SPTPD </td>
                                            </tr> 
											<tr>
                                                <td></td>
                                                <td colspan=\"3\">&nbsp; [_] SKPDKB &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [_] SK Pembetulan </td>
                                            </tr> 
											<tr>
                                                <td></td>
                                                <td colspan=\"3\">&nbsp; [_] SKPDKBT &nbsp;&nbsp;&nbsp; [_] SK Keberatan </td>
                                            </tr> 
                                            <tr>
                                                <td>Masa Pajak</td>
                                                <td>: {$MASA_PAJAK1} - {$MASA_PAJAK2}</td>
                                            </tr>
											<tr>
                                                <td>Tahun</td>
                                                <td colspan=\"3\">: {$TAHUN_PAJAK}</td>
                                            </tr> 
                                            <tr>
                                                <td>Bank Penerima Setoran</td>
                                                <td>: " . strtoupper($BANK) . "</td>  
                                            </tr> 
											<!--<tr>
                                                <td>Nomor Rekening Pajak</td>
                                                <td colspan=\"3\">: {$NO_REK_PAJAK}</td>
                                            </tr> -->

                                            <tr>
                                                <td>Uraian Kegiatan</td>
                                                <td colspan=\"3\">: PAJAK " . strtoupper($this->arr_pajak[$this->id_pajak]) . "</td>
                                            </tr> ";
// var_dump($DATA['pajak_atr'] );die;


                                            if ($DATA['pajak']['CPM_JENIS_PAJAK'] == 4) {
                                                $nama_komoditas = [];
                                                foreach ($DATA['pajak_atr'] as $pajak_atr) {
                                                    $nama_komoditas[] =  $pajak_atr['nmrek'];
                                                }
                                                $komoditas_string = implode(", ", $nama_komoditas);
                                                $html .= "    <tr>
                                                <td>Uraian Komoditas</td>
                                                <td colspan=\"3\">: {$komoditas_string}</td>
                                            </tr>
                                            ";
                                            }
                                         
        if ($DATA['profil']['CPM_REKENING'] == "4.1.01.07.07") {
            $html .= "                      <tr>
                                                <td>Pelaksana Kegiatan</td>
                                                <td>: " . strtoupper($DATA['pajak']['PELAKSANA_KEGIATAN']) . "</td>
                                            </tr>";
        }
        $html .= "
                                            <tr>
                                                <td colspan=\"2\">Dengan rincian penerimaan sebagai berikut : </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"900\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">      
                                <tr>
                                    <td><table width=\"900\" border=\"1\" cellpadding=\"3\">
                                            <tr>
                                                <th width=\"30\" align=\"center\">No.</th>
                                                <th width=\"130\" align=\"center\">Kode Rekening</th>
                                                <th width=\"400\" align=\"center\">Jenis Pajak Daerah</th>
                                                <th width=\"150\" align=\"center\">Nilai (Rp.)</th>
                                            </tr>
                                            <tr>
                                                <td align=\"center\">1.</td>
                                                <td align=\"center\">
                                                    " . $DATA['profil']['CPM_REKENING'] . "
                                                </td>
                                                <td>
                                                    " . $DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek'] . "
                                                </td>
                                                <td align=\"right\">" . number_format($CPM_DPP, 2) . " {$tarif_pajak}</td>
                                            </tr>
                                            

                                            <tr>
                                                <td></td>
                                                <td align=\"left\">
                                                </td>
                                                <td>
                                                    Denda Terlambat Lapor
                                                </td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                                            </tr>

                                            <tr>
                                                <td></td>
                                                <td align=\"left\">
                                                </td>
                                                <td>
                                                    Denda Terlambat Bayar
                                                </td>
                                                <td align=\"right\">" . number_format($DENDA, 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td align=\"right\" colspan=\"3\">Jumlah</td>
                                                <td align=\"right\" colspan=\"1\">" . number_format($TOTAL, 2) . "</td>
                                            </tr>
                                            
                                            
                                            <tr>
                                                <td colspan=\"4\">
                                                    Terbilang : <i>" . ucwords($this->SayInIndonesian($TOTAL)) . " Rupiah</i>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>                            
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"236\" align=\"center\" >
                                   
                                    </td>
                                    <td width=\"236\" align=\"center\">
                                 
                                    </td>
                                    <td width=\"236\" align=\"center\" >
                                    Gedong Tataan, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                                    Penyetor<br/><br/>
                                    <br/><br/><br/><br/>
                                    ( " . str_pad("", 50, "..", STR_PAD_RIGHT) . " )<br/>                                     
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"1\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"355\">SSPD ini berlaku setelah dilampiri dengan bukti pembayaran yang sah dari Bank</td>
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui Teller dan ATM Bank {$BANK} terdekat</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle("9 PAJAK ONLINE");
        $pdf->SetSubject('spppd');
        $pdf->SetKeywords("9 PAJAK ONLINE");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 9, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);


        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        /// QRIS   ==============
        if ($QRCodeSVG) {
            $pdf->ImageSVG('@' . $QRCodeSVG, $x = 170, $y = 97, $w = 32, $h = 32, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
            $pdf->ImageSVG('@' . $icoQRIS, $x = 181, $y = 90, $w = 10, $h = 10, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
        }
        // ======================
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 10, 11, 18, '', '', '', '', false, 300, '', false);

        $pdf->SetAlpha(0.3);

        ob_end_clean();

        $pdf->Output('sspd.pdf', 'I');
    }

    public function get_type_masa()
    {
        return array(
            1 => 'Tahun',
            //	2=>'Semester',
            3 => 'Triwulan',
            4 => 'Bulan',
            5 => 'Minggu',
            6 => 'Hari',
        );
    }

    public function get_payment_flag($id_switching)
    {
        $query = "SELECT payment_flag,payment_code FROM simpatda_gw WHERE id_switching = '$id_switching'";
        $res = mysqli_query($this->Conn, $query);

        $payment_info = null;
        // Memastikan hasil query tidak kosong
        if ($row = $res->fetch_assoc()) {
            $payment_info = [
                'payment_flag' => $row['payment_flag'],
                'payment_code' => $row['payment_code']
            ];
        }

        // $row = mysqli_fetch_assoc($res);
        // $payment_flag = $row['payment_flag'];
        // $payment_flag = $row['payment_code'];

        return $payment_info;
    }

    public function get_list_rekening()
    {
        // $query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek LIKE '4.1.01.09%' AND nmrek LIKE '%Baru%' ORDER BY urut ASC";
        $query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek LIKE '4.1.01.09%' ORDER BY urut ASC";
        $res = mysqli_query($this->Conn, $query);
        $rek = array();
        while ($d = mysqli_fetch_object($res)) {
            $rek[] = $d;
        }
        return $rek;
    }

    public function get_kawasan()
    {
        $query = "SELECT CPM_NAMA FROM PATDA_REKLAME_PARAM_NILAI WHERE CPM_GRUP = 'NFR'";
        $res = mysqli_query($this->Conn, $query);
        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list[] = $row->CPM_NAMA;
        }
        return $list;
    }

    public function get_jalan_type()
    {
        $query = "SELECT * FROM PATDA_REKLAME_PARAM_JALAN WHERE CPM_GRUP = 'NFJ' ORDER BY NPM_PARAM DESC";
        $res = mysqli_query($this->Conn, $query);
        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list[] = $row->CPM_JALAN;
        }
        return $list;
    }

    public function get_jalan()
    {

        $query = "SELECT CPM_PARAM FROM PATDA_REKLAME_PARAM_NILAI WHERE CPM_GRUP = 'NFJ'";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list[] = $row->CPM_PARAM;
        }

        return $list;
    }

    public function get_type_tinggi()
    {
        return array(
            "<9,99m" => '<9,99m',
            "10m s/d 19,99m" => '10m s/d 19,99m',
            ">20m" => '>20m',
        );
    }

    public function get_sudut_pandang()
    {
        $query = "SELECT CPM_ID, CPM_NAMA_FUNGSI FROM PATDA_REKLAME_NSPR WHERE CPM_INDEX_NILAI = 'Sudut Pandang' ORDER BY CPM_ID ASC";
        $res = mysqli_query($this->Conn, $query);
        // die(var_dump($res));
        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list[] = $row->CPM_NAMA_FUNGSI;
        }
        // $list = array(
        //     '1 Arah',
        //     '2 Arah',
        //     '3 Arah',
        //     '4 Arah',
        // );

        return $list;
    }

    public static function formatNPWPD($str)
    {
        $npwpd = '';

        switch (strlen($str)) {
            case '15':
                $npwpd = substr($str, 0, 2) . '.';
                $npwpd .= substr($str, 2, 9) . '.';
                $npwpd .= substr($str, 11, 2) . '.';
                $npwpd .= substr($str, 13, 2);
                break;
            case '13':
                $npwpd = substr($str, 0, 2) . '.';
                $npwpd .= substr($str, 2, 7) . '.';
                $npwpd .= substr($str, 9, 2) . '.';
                $npwpd .= substr($str, 11, 2);
                break;
            default:
                $npwpd = $str;
                break;
        }

        return $npwpd;
    }

    public static function formatNOP($str)
    {
        if (strlen($str) == 11) {
            $nop = substr($str, 0, 2) . '.' . substr($str, 2, 9);
        } else {
            $nop = $str;
        }

        return $nop;
    }

    public function get_list_npwpd()
    {
        $NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
        $JNS_PAJAK = array_search(strtolower($_REQUEST['TBLJNSPJK']), $this->arr_idpajak);

        #TAMBAH INFORMASI OP PADA PENCARIAN
        // $query = sprintf("SELECT * FROM PATDA_WP WHERE
        // (CPM_NPWPD LIKE '%s' OR CPM_NAMA_WP like '%s') AND
        // CPM_JENIS_PAJAK LIKE '%s' LIMIT 0,10",
        // $NPWPD.'%',
        // $NPWPD.'%',
        // '%'.$JNS_PAJAK.'%');
        $query = sprintf(
            "
      			SELECT
      			WP.CPM_NPWPD,WP.CPM_NAMA_WP,group_concat(OP.CPM_NAMA_OP) CPM_NAMA_OP
      			FROM PATDA_WP WP
      			LEFT JOIN PATDA_{$_REQUEST['TBLJNSPJK']}_PROFIL OP on WP.CPM_NPWPD=OP.CPM_NPWPD
      			WHERE
                  (WP.CPM_NPWPD LIKE '%s' OR WP.CPM_NAMA_WP like '%s' OR OP.CPM_NAMA_OP like '%s' OR OP.CPM_NOP like '%s') AND
                  WP.CPM_JENIS_PAJAK LIKE '%s'
      			group by WP.CPM_NPWPD,WP.CPM_NAMA_WP
      			",
            '%' . $NPWPD . '%',
            '%' . $NPWPD . '%',
            '%' . $NPWPD . '%',
            '%' . $NPWPD . '%',
            '%' . $JNS_PAJAK . '%'
        );
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list['items'][] = array('id' => $this->formatNPWPD($row->CPM_NPWPD), 'text' => $row->CPM_NAMA_WP . '<br><b>' . $row->CPM_NAMA_OP . '</b>');
        }
        if (count($list) == 0) {
            $list['items'][] = array('id' => ' ', 'text' => 'NPWPD tidak ditemukan');
        }
        echo $this->Json->encode($list);
    }

    public function getWP()
    {
        $NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
        $TBL = $_REQUEST['TBLJNSPJK'];

        $TRUCK_ID = ($TBL == 'MINERAL') ? 'B.CPM_TRUCK_ID,' : '';
        $REKLAME = ($TBL == 'REKLAME') ? 'B.CPM_REKLAME' : "''";
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
		WHERE A.CPM_NPWPD = '%s'", $NPWPD);
        // echo $query;
        $res = mysqli_query($this->Conn, $query);

        $data = array();
        if ($row = mysqli_fetch_assoc($res)) {
            $data = $row;

            if (!empty($data['CPM_GOL'])) {
                $query = "SELECT tarif1 as tarif,tarif2 as harga FROM PATDA_REK_PERMEN13 WHERE kdrek = '{$data['CPM_GOL']}'";
                $res = mysqli_query($this->Conn, $query);
                if ($permen = mysqli_fetch_assoc($res)) {
                    $data['CPM_TARIF'] = $permen['tarif'];
                    $data['CPM_HARGA'] = $permen['harga'];
                }
            }

            if (isset($data['CPM_TRUCK_ID'])) {
                $data['CPM_TRUCK_ID'] = base64_encode($data['CPM_TRUCK_ID']);
            }
        }
        echo $this->Json->encode($data);
    }

    public function get_list_kecamatan()
    {
        $query = "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            // $list[] = $row;
            $list[$row->CPM_KEC_ID] = $row;
        }

        return $list;
    }

    public function get_list_jenis_penerimaan()
    {
        $query = "SELECT * FROM jenis_penerimaan_retribusi";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            // $list[] = $row;
            $list[$row->id] = $row;
        }
        return $list;
    }

    public function get_list_jenis_retribusi()
    {
        $query = "SELECT * FROM rekening_retribusi";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            // $list[] = $row;
            $list[$row->id] = $row;
        }
        return $list;
    }

    public function get_list_kelurahan($id = '', $param = '')
    {
        $KEC = !empty($id) ? $id : (isset($_REQUEST['CPM_KEC_ID']) ? $_REQUEST['CPM_KEC_ID'] : '');

        $query = "SELECT * FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID like '{$KEC}%'";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        if ($param == 'BERKAS') {
            if (empty($id)) {
                $list = '';
                while ($row = mysqli_fetch_assoc($res)) {
                    $list .= '<option value="' . $row['CPM_KEL_ID'] . '">' . $row['CPM_KELURAHAN'] . '</option>';
                }
                return $list;
            } else {
                $list = array();
                while ($row = mysqli_fetch_object($res)) {
                    $list[$row->CPM_KEL_ID] = $row;
                }
                return $list;
            }
        } elseif ($param == 'LIST') {
            $list = array();
            while ($row = mysqli_fetch_object($res)) {
                $list[$row->CPM_KEL_ID] = $row;
            }
            return $list;
        } else {
            if (empty($id)) {
                $list = '';
                while ($row = mysqli_fetch_assoc($res)) {
                    $list .= '<option value="' . $row['CPM_KEL_ID'] . '">' . $row['CPM_KELURAHAN'] . '</option>';
                }
                echo $list;
            } else {
                $list = array();
                while ($row = mysqli_fetch_object($res)) {
                    $list[$row->CPM_KEL_ID] = $row;
                }
                echo $list;
            }
        }
    }

    public function get_nama_kecamatan($id)
    {
        $query = "SELECT CPM_KECAMATAN FROM PATDA_MST_KECAMATAN WHERE CPM_KEC_ID = '{$id}' ";
        $res = mysqli_query($this->Conn, $query);

        $list = "";
        if ($row = mysqli_fetch_object($res)) {
            $list = $row->CPM_KECAMATAN;
        }

        return $list;
    }

    public function get_nama_kelurahan($id)
    {
        $query = "SELECT CPM_KELURAHAN FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID = '{$id}' ";
        $res = mysqli_query($this->Conn, $query);

        $list = "";
        if ($row = mysqli_fetch_object($res)) {
            $list = $row->CPM_KELURAHAN;
        }

        return $list;
    }

    //tambahan
    public function get_id_kecamatan_real($id)
    {
        $query = "SELECT CPM_KECAMATAN_REAL FROM PATDA_MST_KECAMATAN WHERE CPM_KEC_ID = '{$id}' ";
        $res = mysqli_query($this->Conn, $query);

        $list = "";
        if ($row = mysqli_fetch_object($res)) {
            $list = $row->CPM_KECAMATAN_REAL;
        }

        return $list;
    }

    public function get_id_kelurahan_real($id)
    {
        $query = "SELECT CPM_KELURAHAN_REAL FROM PATDA_MST_KELURAHAN WHERE CPM_KEL_ID = '{$id}' ";
        $res = mysqli_query($this->Conn, $query);

        $list = "";
        if ($row = mysqli_fetch_object($res)) {
            $list = $row->CPM_KELURAHAN_REAL;
        }

        return $list;
    }
    //end




    public function get_list_angkutan()
    {
        $query = "SELECT * FROM PATDA_MINERAL_AUDITTRAIL_ANGKUTAN order by CPM_TRUCK_ID";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $list[] = $row;
        }

        return $list;
    }

    public function get_list_jenis_kamar()
    {
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
        while ($row = mysqli_fetch_object($res)) {
            $list[$row->Kode] = $row;
        }

        return $list;
    }

    public function get_field_array($result)
    {
        $fields = array();

        if (!$fields = mysqli_fetch_assoc($result)) {
            while ($f = mysqli_fetch_field($result)) {
                $fields[$f->name] = '';
            }
        }

        return $fields;
    }

    public function get_bank_payment()
    {
        $data = array();
        $dbName = $this->get_config_value('aPatda', 'PATDA_DBNAME');
        $dbHost = $this->get_config_value('aPatda', 'PATDA_HOSTPORT');
        $dbPwd = $this->get_config_value('aPatda', 'PATDA_PASSWORD');
        $dbUser = $this->get_config_value('aPatda', 'PATDA_USERNAME');

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

        $sql = "select CDC_B_ID,CDC_B_NAME from CDCCORE_BANK";
        $qry = mysqli_query($Conn_gw, $sql);
        while ($bank = mysqli_fetch_object($qry)) {
            $data[$bank->CDC_B_ID] = $bank;
        }

        return $data;
    }


    public function jenis_rekening()
    {
        $query = "SELECT nmheader3, kdrek FROM PATDA_REK_PERMEN13 where nmheader3 != 'Reklame' GROUP BY nmheader3   ";
        $res = mysqli_query($this->Conn, $query);

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $nilai = explode(".", $row->kdrek, -1);
            $list[] = '<option value="' . implode(".", $nilai) . '">' . $row->nmheader3 . '</option>';
            // $list[] = $row->CPM_ID;
        }

        return $list;
    }

    public function get_no_rek($id = '', $param = '')
    {
        $KD = !empty($id) ? $id : (isset($_REQUEST['CPM_KD_ID']) ? $_REQUEST['CPM_KD_ID'] : '');

        $query = "SELECT kdrek,nmheader3, max(kdrek) as total FROM PATDA_REK_PERMEN13 WHERE kdrek like '{$KD}%' ORDER BY kdrek DESC limit 1";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        $list = array();
        while ($row = mysqli_fetch_object($res)) {
            $nilai = explode(".", $row->kdrek, -1);
            $nama = $row->nmheader3;
            $n = substr($row->total, -2) + 1;
            $list[] = implode(".", $nilai) . "." . sprintf("%02s", $n);
        }
        if ($KD == "") {
            $hsl = array("nilai" => " ", "nama" => " ");
        } else {
            $hsl = array("nilai" => $list['0'], "nama" => $nama);
        }
        echo json_encode($hsl);
    }

    public function get_persen_denda($expired, $today = '')
    {
        $year = date("Y", strtotime($expired));
        if ($year >= 2024) {
            $min_denda = 1; // 0.2% per bulan
            $max_denda = 24; // 24% maksimal denda
        } else {
            $min_denda = 2; // 0.2% per bulan
            $max_denda = 48; // 24% maksimal denda
        }
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

        $persen = ($bulan * $min_denda);
        $persen = ($persen > $max_denda) ? $max_denda : $persen;
        return $persen;
    }

    public function check_piutang($jns_pajak)
    {
        $table = '';
        $this->notif = true;

        if ($jns_pajak == 1) {
            $table = $this->PATDA_AIRBAWAHTANAH_DOC;
        }

        if ($jns_pajak == 2) {
            $table = $this->PATDA_HIBURAN_DOC;
        }

        if ($jns_pajak == 3) {
            $table = $this->PATDA_HOTEL_DOC;
        }

        if ($jns_pajak == 4) {
            $table = $this->PATDA_MINERAL_DOC;
        }

        if ($jns_pajak == 5) {
            $table = $this->PATDA_PARKIR_DOC;
        }

        if ($jns_pajak == 6) {
            $table = $this->PATDA_JALAN_DOC;
        }

        if ($jns_pajak == 7) {
            $table = $this->PATDA_REKLAME_DOC;
        }

        if ($jns_pajak == 8) {
            $table = $this->PATDA_RESTORAN_DOC;
        }

        $query = "SELECT * FROM {$table} WHERE CPM_NO = '{$this->CPM_NO}'";
        $res = mysqli_query($this->Conn, $query);
        $check = mysqli_num_rows($res);
        if ($check > 0) {
            $this->notif = false;
            $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Piutang <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
            return $this->notif;
        }

        return $this->notif;
    }

    public function validasi_piutang()
    {
        if (
            $this->check_piutang(1) == false || $this->check_piutang(2) == false || $this->check_piutang(3) == false || $this->check_piutang(4) == false ||
            $this->check_piutang(5) == false || $this->check_piutang(6) == false || $this->check_piutang(7) == false || $this->check_piutang(8) == false
        ) {

            return false;
        }

        return true;
    }

    protected function getDataRekening()
    {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 order by kdrek";
        $result = mysqli_query($this->Conn, $query);
        //$data['CPM_REKENING'] = array();
        $data = array();
        while ($d = mysqli_fetch_assoc($result)) {
            $data[substr($d['kdrek'], 0, 9)][$d['kdrek']] = array('kdrek' => $d['kdrek'], 'nmrek' => $d['nmrek'], 'nmheader3' => $d['nmheader3']);
            // $data[$d['kdrek']] = array('nmrek' => $d['nmrek'], 'nmheader3' => $d['nmheader3']);
        }
        return $data;
    }

    private function download_pajak_xls_bentang_panjang_pat()
    {

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = '';

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }

        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
        //}

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
        $jenisPajak = $this->arr_tipe_pajak;
        $z = 0;
        foreach ($jenisPajak as $jp => $jp_id) {
            if ($jenis_pajaks != $jp && $jenis_pajaks != '') {
                continue;
            }

            if ($jp == 2) {
                $no = 0;
            }

            if ($jp == 2) {
                $total_total_pajak = 0;
                $total_jan = 0;
                $total_feb = 0;
                $total_mar = 0;
                $total_apr = 0;
                $total_mei = 0;
                $total_jun = 0;
                $total_jul = 0;
                $total_agu = 0;
                $total_sep = 0;
                $total_okt = 0;
                $total_nov = 0;
                $total_des = 0;
            }

            //if(isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != ""){
            //$where .= " AND pj.CPM_TIPE_PAJAK={$_REQUEST['CPM_JENIS_PJK']}";    
            //if($_REQUEST['CPM_JENIS_PJK']==1)
            //	$where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            //elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //	$where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
            //}

            $where3 = $this->where3_cetak_bentang();


            if ($this->_idp == '7') {
                $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
            } else {
                $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
            }

            //$query_wp = "select * from patda_wp where  CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
            //if($this->_idp == '8'){
            $query_wp = "SELECT wp.* FROM patda_wp wp 
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
            //}
            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            #query select list data
            $query2 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
						pr.CPM_KECAMATAN_OP
					FROM
						PATDA_{$JENIS_PAJAK}_DOC pj
						INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
						INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
						WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
						GROUP BY CPM_BULAN, pr.CPM_NPWPD
						ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
            // var_dump($query2);
            // exit();
            $data = array();
            $res = mysqli_query($this->Conn, $query2);
            while ($row = mysqli_fetch_assoc($res)) {

                $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
                // var_dump($data);
                // break;
            }
            // echo $data[$row['CPM_NPWPD']]['CPM_NPWPD'];
            //exit();
            $query3 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
						pr.CPM_KECAMATAN_OP
                FROM
					PATDA_{$JENIS_PAJAK}_DOC pj
					INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) = 12 AND {$q_tipe_pajak} = '{$jp}'
                    GROUP BY CPM_BULAN, pr.CPM_NPWPD
					ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
            // var_dump($query3);
            // die;

            $data2 = array();
            $res2 = mysqli_query($this->Conn, $query3);
            // $jumlah_data;
            while ($row = mysqli_fetch_assoc($res2)) {
                $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
                $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                    'CPM_VOLUME' => $row['CPM_VOLUME'],
                    'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
                );
            }



            $data_wp = array();
            // var_dump($query_wp);
            // die;
            $res_wp = mysqli_query($this->Conn, $query_wp);
            // echo "<pre>";

            while ($row = mysqli_fetch_assoc($res_wp)) {
                $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
                // var_dump($data_wp);
                // break;
            }

            // Set properties
            $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("9 PAJAK ONLINE")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("9 PAJAK ONLINE");

            // Add some data
            $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
            $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);

            $objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
                ->setCellValue('A6', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
                ->setCellValue('A7', 'NO.')
                ->setCellValue('B7', 'NAMA WAJIB PAJAK.')
                ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . ' TAHUN ' . $tahun_pajak_label . ' ')
                ->setCellValue('H8', 'JUMLAH.')
                ->setCellValue('C8', 'TAPBOX.')
                // ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
                ->setCellValue('D8', 'TRIWULAN 1')
                ->setCellValue('E8', 'TRIWULAN 2')
                ->setCellValue('F8', 'TRIWULAN 3')
                ->setCellValue('G8', 'TRIWULAN 4');
            // ->setCellValue('I8', 'MEI')
            // ->setCellValue('J8', 'JUNI')
            // ->setCellValue('K8', 'JULI')
            // ->setCellValue('L8', 'AGS')
            // ->setCellValue('M8', 'SEPT')
            // ->setCellValue('N8', 'OKT')
            // ->setCellValue('O8', 'NOP')
            // ->setCellValue('P8', 'DES');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:G1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:G2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:G3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:G4");
            $objPHPExcel->getActiveSheet()->mergeCells("A6:G6");
            $objPHPExcel->getActiveSheet()->mergeCells("A7:A8");
            $objPHPExcel->getActiveSheet()->mergeCells("B7:B8");
            $objPHPExcel->getActiveSheet()->mergeCells("C7:G7");


            // Miscellaneous glyphs, UTF-8
            $objPHPExcel->setActiveSheetIndex($z);

            $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
            $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
            $tab = $jns[$this->_s];
            $jml = 0;

            $row = 9;
            $sumRows = mysqli_num_rows($res);
            $total_pajak = 0;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                // var_dump($cek_kecamatan);
                // //break;
                // die;
            }

            $jumlah_data = count($data_wp);
            // print_r($data_wp) . '<br><br>';
            // break;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                // print_r($data2) . '<br>';
                // print_r([$rowDataWP['CPM_NPWPD']]);
                // die;
                $rowData = $data[$rowDataWP['CPM_NPWPD']];
                $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];

                // print_r($rowData2);
                // die;


                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
                    //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    // $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    // $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    // $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    // $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    // $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    // $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    // $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    // $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':H' . $space);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                        $row++;
                    }

                    $no = 0;
                    $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;
                }

                // var_dump($data2);
                // var_dump($rowDataWP['CPM_NPWPD']);
                // die;
                if ($rowDataWP['CPM_KECAMATAN_WP']) {

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':H' . $row);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );

                        $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                        $row++;

                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
                    }
                }

                // var_dump($data);
                // var_dump($rowDataWP['CPM_NPWPD']);
                // die;
                $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                $thLalu = $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'];
                $triwulan01 = $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'];
                $triwulan02 = $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'];
                $triwulan03 = $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'];
                $triwulan04 = $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                // var_dump($rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK']);
                // var_dump($rowDataWP['CPM_NPWPD']);
                // exit();
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');

                // $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $thLalu + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $triwulan01 + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $triwulan02 + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $triwulan03 + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $triwulan04 + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0 + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
                // $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


                if ($nama_kecamatan != $nama_kecamatans) {
                    $total_pajak = 0;
                    $jan = 0;
                    $feb = 0;
                    $mar = 0;
                    $apr = 0;
                    $mei = 0;
                    $jun = 0;
                    $jul = 0;
                    $agu = 0;
                    $sep = 0;
                    $okt = 0;
                    $nov = 0;
                    $des = 0;
                }


                $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
                $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

                //untuk total
                $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

                //var_dump($total_pajak);die;

                $jml++;
                $row++;
                $no++;
                //var_dump($jumlah_data, $row);die;
                if ($jumlah_data == $jml) {
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    // $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    // $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    // $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    // $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    // $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    // $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    // $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    // $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':H' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

                        $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_jan);
                        $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_feb);
                        $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_mar);
                        $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_apr);
                        // $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_mei);
                        // $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_jun);
                        // $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_jul);
                        // $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_agu);
                        // $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_sep);
                        // $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_okt);
                        // $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_nov);
                        // $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_des);
                        $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_total_pajak);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 3;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':H' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }

                    //var_dump($space);die;
                    $space = $space + 1;
                    $no_keterangan = 0;
                    $total_wp = 0;
                    //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
                    //if($this->_idp == '8'){
                    $query_keterangan = "SELECT
													wp.CPM_KECAMATAN_WP,
													count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
												FROM
													patda_wp wp
													INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
													AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
												WHERE
													wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
												GROUP BY
													CPM_KECAMATAN_WP 
												ORDER BY
													CPM_KECAMATAN_WP ASC";
                    //}
                    //var_dump($query_keterangan);die;

                    $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                    while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . $row_keterangan['CPM_KECAMATAN_WP']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':I' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $space++;
                        $no_keterangan++;
                        $total_wp += $row_keterangan['TOTAL'];
                    }
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }
            }




            /** style **/
            // judul dok + judul tabel
            $objPHPExcel->getActiveSheet()->getStyle('A1:H4')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A7:H8')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A5:H7')->getAlignment()->setWrapText(true);

            // border
            $objPHPExcel->getActiveSheet()->getStyle('A7:H' . $row)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );


            // fill tabel header
            $objPHPExcel->getActiveSheet()->getStyle('A7:H8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A7:H8')->getFill()->getStartColor()->setRGB('E4E4E4');

            // format angka col I & K
            $objPHPExcel->getActiveSheet()->getStyle('E8:H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // // fill tabel footer
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



            // Rename sheet
            //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
            // $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
            for ($x = "A"; $x <= "G"; $x++) {
                if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
                else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
            }

            if ($_REQUEST['CPM_JENIS_PJK'] == 1) {
                $objPHPExcel->getActiveSheet()->setTitle("Reguler");
                $objPHPExcel->createSheet();
            } elseif ($_REQUEST['CPM_JENIS_PJK'] == 2) {
                $objPHPExcel->getActiveSheet()->setTitle("Non Reguler");
                $objPHPExcel->createSheet();
            } else {
                $objPHPExcel->getActiveSheet()->setTitle("$jp_id");
                $objPHPExcel->createSheet();
                $z++;
            }
        }
        // var_dump($where);
        // die;

        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }
    private function download_pajak_xls_bentang_panjang_S()
    {

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = '';

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
        //}

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';



        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
        $jenisPajak = $this->arr_tipe_pajak;
        $z = 0;
        foreach ($jenisPajak as $jp => $jp_id) {
            if ($jenis_pajaks != $jp && $jenis_pajaks != '') {
                continue;
            }

            if ($jp == 2) {
                $no = 0;
            }

            if ($jp == 2) {
                $total_total_pajak = 0;
                $total_jan = 0;
                $total_feb = 0;
                $total_mar = 0;
                $total_apr = 0;
                $total_mei = 0;
                $total_jun = 0;
                $total_jul = 0;
                $total_agu = 0;
                $total_sep = 0;
                $total_okt = 0;
                $total_nov = 0;
                $total_des = 0;
            }

            //if(isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != ""){
            //$where .= " AND pj.CPM_TIPE_PAJAK={$_REQUEST['CPM_JENIS_PJK']}";    
            //if($_REQUEST['CPM_JENIS_PJK']==1)
            //	$where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            //elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //	$where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
            //}

            $where3 = $this->where3_cetak_bentang();


            if ($this->_idp == '7') {
                $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
            } else {
                $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
            }

            //$query_wp = "select * from patda_wp where  CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
            //if($this->_idp == '8'){
            $query_wp = "SELECT wp.* FROM patda_wp wp 
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
            //}
            // var_dump($query_wp);
            // die;
            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            #query select list data
            $query2 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
                        pr.CPM_KECAMATAN_WP,
						pr.CPM_KECAMATAN_OP
					FROM
						PATDA_{$JENIS_PAJAK}_DOC pj
						INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
						INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
						WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
						GROUP BY CPM_BULAN, pr.CPM_NPWPD
						ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

            // var_dump($query2);
            // die;
            //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
            // var_dump($res);
            // exit();
            $data = array();
            $res = mysqli_query($this->Conn, $query2);
            while ($row = mysqli_fetch_assoc($res)) {

                $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
                $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
                // var_dump($data);
                // break;
            }
            // echo $data[$row['CPM_NPWPD']]['CPM_NPWPD'];
            //exit();
            $query3 = "SELECT
						SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
						MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
						pr.CPM_NPWPD,
						pr.CPM_NAMA_WP,
						UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
						pr.CPM_ALAMAT_WP,
						pr.CPM_ALAMAT_OP,
						pr.CPM_KECAMATAN_OP
                FROM
					PATDA_{$JENIS_PAJAK}_DOC pj
					INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) = 12 AND {$q_tipe_pajak} = '{$jp}'
                    GROUP BY CPM_BULAN, pr.CPM_NPWPD
					ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
            // var_dump($query3);
            // die;

            $data2 = array();
            $res2 = mysqli_query($this->Conn, $query3);
            // $jumlah_data;
            while ($row = mysqli_fetch_assoc($res2)) {
                $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
                $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
                //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
                $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                    'CPM_VOLUME' => $row['CPM_VOLUME'],
                    'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
                );
            }



            $data_wp = array();
            // var_dump($query_wp);
            // die;
            $res_wp = mysqli_query($this->Conn, $query_wp);
            // echo "<pre>";

            while ($row = mysqli_fetch_assoc($res_wp)) {
                $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
                $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
                // var_dump($data_wp);
                // break;
            }

            // Set properties
            $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("9 PAJAK ONLINE")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("9 PAJAK ONLINE");

            // Add some data
            $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
            $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);

            $objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
                ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
                ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
                ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
                ->setCellValue('A6', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
                ->setCellValue('A7', 'NO.')
                ->setCellValue('B7', 'NAMA WAJIB PAJAK.')
                ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . ' TAHUN ' . $tahun_pajak_label . ' ')
                ->setCellValue('Q8', 'JUMLAH.')
                ->setCellValue('C8', 'TAPBOX.')
                ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
                ->setCellValue('E8', 'JAN')
                ->setCellValue('F8', 'FEB')
                ->setCellValue('G8', 'MAR')
                ->setCellValue('H8', 'APRIL')
                ->setCellValue('I8', 'MEI')
                ->setCellValue('J8', 'JUNI')
                ->setCellValue('K8', 'JULI')
                ->setCellValue('L8', 'AGS')
                ->setCellValue('M8', 'SEPT')
                ->setCellValue('N8', 'OKT')
                ->setCellValue('O8', 'NOP')
                ->setCellValue('P8', 'DES');

            // judul dok
            $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
            $objPHPExcel->getActiveSheet()->mergeCells("A6:R6");
            $objPHPExcel->getActiveSheet()->mergeCells("A7:A8");
            $objPHPExcel->getActiveSheet()->mergeCells("B7:B8");
            $objPHPExcel->getActiveSheet()->mergeCells("C7:Q7");


            // Miscellaneous glyphs, UTF-8
            $objPHPExcel->setActiveSheetIndex($z);

            $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
            $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
            $tab = $jns[$this->_s];
            $jml = 0;

            $row = 9;
            $sumRows = mysqli_num_rows($res);
            $total_pajak = 0;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                // var_dump($cek_kecamatan);
                // //break;
                // die;
            }

            $jumlah_data = count($data_wp);
            // print_r($data) . '<br><br>';
            // die;


            foreach ($data_wp as $npwpd => $rowDataWP) {
                //print_r($data) . '<br>';
                // print_r($data[$rowDataWP['CPM_NPWPD']]);
                // die;
                $rowData = $data[$rowDataWP['CPM_NPWPD']];
                $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];

                // print_r($rowData['bulan'][10]['CPM_TOTAL_PAJAK']) . '<br>';
                // print_r($rowDataWP['CPM_KECAMATAN_WP']) . '<br>';
                // print_r($cek_kecamatan);
                // die;


                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
                    //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':Q' . $space);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                        $row++;
                    }

                    $no = 0;
                    $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;
                }


                if ($rowDataWP['CPM_KECAMATAN_WP']) {

                    if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':Q' . $row);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );

                        $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                        $row++;

                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
                    }
                }
                // var_dump($rowData['bulan']);
                // die;

                $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                // echo $nama_kecamatan;
                // exit();
                $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


                if ($nama_kecamatan != $nama_kecamatans) {
                    $total_pajak = 0;
                    $jan = 0;
                    $feb = 0;
                    $mar = 0;
                    $apr = 0;
                    $mei = 0;
                    $jun = 0;
                    $jul = 0;
                    $agu = 0;
                    $sep = 0;
                    $okt = 0;
                    $nov = 0;
                    $des = 0;
                }


                $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
                $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

                //untuk total
                $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
                $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
                $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
                $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
                $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
                $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
                $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
                $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
                $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
                $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
                $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
                $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
                $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

                //var_dump($total_pajak);die;

                $jml++;
                $row++;
                $no++;
                //var_dump($jumlah_data, $row);die;
                if ($jumlah_data == $jml) {
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 1;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

                        $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_jan);
                        $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_feb);
                        $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_mar);
                        $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_apr);
                        $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_mei);
                        $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_jun);
                        $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_jul);
                        $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_agu);
                        $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_sep);
                        $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_okt);
                        $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_nov);
                        $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_des);
                        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_total_pajak);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }


                    if ($jumlah_data == $jml) {
                        //var_dump($row);die;
                        $space = $row + 3;
                        $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
                            array(
                                'font' => array(
                                    'bold' => true
                                ),
                            )
                        );
                    }

                    //var_dump($space);die;
                    $space = $space + 1;
                    $no_keterangan = 0;
                    $total_wp = 0;
                    //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
                    //if($this->_idp == '8'){
                    $query_keterangan = "SELECT
													wp.CPM_KECAMATAN_WP,
													count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
												FROM
													patda_wp wp
													INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
													AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
												WHERE
													wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
												GROUP BY
													CPM_KECAMATAN_WP 
												ORDER BY
													CPM_KECAMATAN_WP ASC";
                    //}
                    //var_dump($query_keterangan);die;

                    $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                    while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . $row_keterangan['CPM_KECAMATAN_WP']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                        $space++;
                        $no_keterangan++;
                        $total_wp += $row_keterangan['TOTAL'];
                    }
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }
            }




            /** style **/
            // judul dok + judul tabel
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q4')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A5:Q7')->getAlignment()->setWrapText(true);

            // border
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q' . $row)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
            );


            // fill tabel header
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->getStartColor()->setRGB('E4E4E4');

            // format angka col I & K
            $objPHPExcel->getActiveSheet()->getStyle('E8:Q' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // // fill tabel footer
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



            // Rename sheet
            //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
            for ($x = "A"; $x <= "H"; $x++) {
                if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
                else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
            }

            if ($_REQUEST['CPM_JENIS_PJK'] == 1) {
                $objPHPExcel->getActiveSheet()->setTitle("Reguler");
                $objPHPExcel->createSheet();
            } elseif ($_REQUEST['CPM_JENIS_PJK'] == 2) {
                $objPHPExcel->getActiveSheet()->setTitle("Non Reguler");
                $objPHPExcel->createSheet();
            } else {
                $objPHPExcel->getActiveSheet()->setTitle("$jp_id");
                $objPHPExcel->createSheet();
                $z++;
            }
        }
        // die(var_dump($_REQUEST['CPM_JENIS_PJK']));
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }

    // FUNCTION BENTANG PANJANG REKLAME 
    private function download_bentang_panjang_ter()
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        // var_dump($_REQUEST);
        // die;
        $selectedValues = explode(',', $_REQUEST['CPM_FILTER_V2']);
        $rekekningv2 = "'" . implode("','", $selectedValues) . "'";

        $periode = '';
        $periode_bulan = '';
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
        $where2 = '';
        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

        //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " ";
        //}
        // $where .= (isset($_REQUEST['CPM_FILTER_V2']) && $_REQUEST['CPM_FILTER_V2'] != "") ? " AND CPM_ATR_REKENING IN ( {$rekekningv2}) " : "";

        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
            $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
            $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
            $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        }
        if ($this->_idp == 8 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 3 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pj.CPM_TIPE_PAJAK='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        } elseif ($this->_idp == 7 && isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
            $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND atr.CPM_ATR_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            // if($_REQUEST['CPM_JENIS_PJK']==1)
            //     $where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
            // elseif($_REQUEST['CPM_JENIS_PJK']==2)
            //     $where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
        }
        $thuuun = (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : date('Y');

        //die(var_dump($thuuun));

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
        //PENERANGAN JALAN

        $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

        //  die(var_dump($JENIS_PAJAK));
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $z = 0;

        $where3 = $this->where3_cetak_bentang();

        $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
            INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
            WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";

        #query select list data
        $query2 = "SELECT
        				SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                        YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
        				MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
        				pr.CPM_NPWPD,
        				pr.CPM_NAMA_WP,
        				UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
        				pr.CPM_ALAMAT_WP,
        				pr.CPM_ALAMAT_OP,
                        pr.CPM_KECAMATAN_WP,
        				pr.CPM_KECAMATAN_OP
        			FROM
        				PATDA_{$JENIS_PAJAK}_DOC pj
        				INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
        				INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                        INNER JOIN PATDA_REKLAME_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_REKLAME_ID
                    INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
        				WHERE {$where}
        				GROUP BY CPM_BULAN, pr.CPM_NPWPD,CPM_YEAR
        				ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


        //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
        $data = array();
        $res = mysqli_query($this->Conn, $query2);
        while ($row = mysqli_fetch_assoc($res)) {

            $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
            $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
        }
        $query3 = "SELECT
        				SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
        				MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
        				pr.CPM_NPWPD,
        				pr.CPM_NAMA_WP,
        				UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
        				pr.CPM_ALAMAT_WP,
        				pr.CPM_ALAMAT_OP,
        				pr.CPM_KECAMATAN_OP
                FROM
        			PATDA_{$JENIS_PAJAK}_DOC pj
        			INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                    INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    INNER JOIN PATDA_REKLAME_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_REKLAME_ID
                    INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
                    WHERE {$where3}
                    GROUP BY CPM_BULAN, pr.CPM_NPWPD
        			ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

        // echo "<pre>";
        // print_r($query3);
        // die;
        $data2 = array();
        $res2 = mysqli_query($this->Conn, $query3);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res2)) {
            $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
            $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
            );
        }

        $query4 = "SELECT
                    SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                    CPM_TAHUN_PAJAK,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_OP
            FROM
                PATDA_{$JENIS_PAJAK}_DOC pj
                INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                INNER JOIN PATDA_REKLAME_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_REKLAME_ID
                INNER JOIN PATDA_REK_PERMEN13 permen ON atr.CPM_ATR_REKENING = permen.kdrek
                WHERE {$where3}
                GROUP BY pr.CPM_NPWPD,CPM_TAHUN_PAJAK
                ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP,CPM_TAHUN_PAJAK";

        // die(var_dump($query4));
        $data3 = array();
        $res3 = mysqli_query($this->Conn, $query4);
        // $jumlah_data;
        while ($row = mysqli_fetch_assoc($res3)) {
            $data3[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data3[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data3[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data3[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
            $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
            $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
            $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
            //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
            $data3[$row['CPM_NPWPD']]['tahun'][$row['CPM_TAHUN_PAJAK']] = array(
                'CPM_VOLUME' => $row['CPM_VOLUME'],
                'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
            );
        }

        // var_dump($data2[$row['CPM_NPWPD']]['CPM_NPWPD']);
        // die(var_dump($data3[$row['CPM_NPWPD']]['CPM_NPWPD']));
        $data_wp = array();

        $res_wp = mysqli_query($this->Conn, $query_wp, MYSQLI_USE_RESULT);
        // echo "<pre>";
        // print_r($query_wp);die;
        //  $rows = [];
        while ($row = mysqli_fetch_assoc($res_wp)) {


            $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
            $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
            $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
            $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
        }
        // die(var_dump($data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP']));
        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("-")
            ->setDescription("bphtb")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        //die(var_dump($year));
        $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
        $ab = $_REQUEST['CPM_TAHUN_PAJAK'];

        $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);



        $objPHPExcel->setActiveSheetIndex($z)
            ->setCellValue('A1', 'PEMERINTAH KABUPATEN PESAWARAN')
            ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
            ->setCellValue('A3', 'BADAN PENDAPATAN DAERAH')
            ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
            ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
            ->setCellValue('A6', '')
            ->setCellValue('A7', 'NO.')
            ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
            ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK)
            ->setCellValue('C8',  'TAHUN ')
            //   ->setCellValue('Q7', 'JUMLAH.')
            ->setCellValue('C9', 'TAPBOX.')
            // ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
            // ->setCellValue('E8', 'JAN')
            // ->setCellValue('F8', 'FEB')
            // ->setCellValue('G8', 'MAR')
            // ->setCellValue('H8', 'APRIL')
            ->setCellValue('I10', 'JAN reklame')
            ->setCellValue('J10', 'FEB')
            ->setCellValue('K10', 'MAR')
            ->setCellValue('L10', 'APRIL')
            ->setCellValue('M10', 'MEI')
            ->setCellValue('N10', 'JUNI')
            ->setCellValue('O10', 'JULI')
            ->setCellValue('P10', 'AGS')
            ->setCellValue('Q10', 'SEPT')
            ->setCellValue('R10', 'OKT')
            ->setCellValue('S10', 'NOP')
            ->setCellValue('T10', 'DES')
            ->setCellValue('U7', 'JUMLAH');

        // 1 => "AIR BAWAH TANAH",
        // 2 => "HIBURAN",3 => "HOTEL",5 => "PARKIR", 6 => "PENERANGAN JALAN", 7 => "REKLAME", 8 => "RESTORAN",9 => "SARANG WALET"
        // 4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
        if ($JENIS_PAJAK == 'HIBURAN' || $JENIS_PAJAK == 'HOTEL' || $JENIS_PAJAK == 'PARKIR' || $JENIS_PAJAK == 'JALAN' || $JENIS_PAJAK == 'REKLAME') {
            for ($i = 0; $i < 6; $i++) {
                $bar = 9;
                $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
                $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
                $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
                //   echo $cell;

                $year = $tahun_pajak_label - $i;
                $objPHPExcel->setActiveSheetIndex($z)
                    ->setCellValue($cell, $year);
            }
        }
        // die;
        if ($JENIS_PAJAK == 'RESTORAN') {
            $objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('B7', 'NAMA WAJIB OP.');
        }

        // judul dok
        $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
        $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
        $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
        $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
        $objPHPExcel->getActiveSheet()->mergeCells("A5:R5");
        $objPHPExcel->getActiveSheet()->mergeCells("A7:A10");
        $objPHPExcel->getActiveSheet()->mergeCells("B7:B10");
        $objPHPExcel->getActiveSheet()->mergeCells("C9:C10");
        $objPHPExcel->getActiveSheet()->mergeCells("C7:T7");
        $objPHPExcel->getActiveSheet()->mergeCells("C8:T8");
        $objPHPExcel->getActiveSheet()->mergeCells("I9:T9");
        $objPHPExcel->getActiveSheet()->mergeCells("U7:U10");


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex($z);

        $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
        $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
        $tab = $jns[$this->_s];
        $jml = 0;

        $row = 11;
        $sumRows = mysqli_num_rows($res);
        $total_pajak = 0;


        foreach ($data_wp as $npwpd => $rowDataWP) {
            $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        }


        $jumlah_data = count($data_wp);
        foreach ($data_wp as $npwpd => $rowDataWP) {
            $rowData = $data[$rowDataWP['CPM_NPWPD']];
            $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
            $rowDAta3 = $data3[$rowDataWP['CPM_NPWPD']];

            if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
                // die(var_dump($tahun1));
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row,  $tahun1);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row,  $tahun2);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $tahun3);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row,  $tahun4);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row,  $tahun5);
                //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
                $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
                $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
                $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
                $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );

                if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':U' . $space);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffffff');
                    $row++;
                }

                $no = 0;
                $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                $row++;
            }

            if ($rowDataWP['CPM_KECAMATAN_WP']) {

                if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':U' . $row);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );

                    $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
                    $row++;

                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
                }
            }
            $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
            // echo $nama_kecamatan;
            // exit();
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);

            if ($JENIS_PAJAK == 'RESTORAN') {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
            }

            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0);
            // $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


            if ($nama_kecamatan != $nama_kecamatans) {
                //  $total_pajak = 0;
                $tahun1 = 0;
                $tahun2 = 0;
                $tahun3 = 0;
                $tahun4 = 0;
                $tahun5 = 0;
                $jan = 0;
                $feb = 0;
                $mar = 0;
                $apr = 0;
                $mei = 0;
                $jun = 0;
                $jul = 0;
                $agu = 0;
                $sep = 0;
                $okt = 0;
                $nov = 0;
                $des = 0;
            }


            $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
            $tahun1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
            $tahun2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
            $tahun3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
            $tahun4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
            $tahun5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
            $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
            $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
            $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
            $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
            $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
            $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
            $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
            $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
            $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
            $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

            //untuk total
            $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
            $tahunTotal1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
            $tahunTotal2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
            $tahunTotal3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
            $tahunTotal4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
            $tahunTotal5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
            $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
            $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
            $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
            $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
            $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
            $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
            $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
            $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
            $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
            $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
            $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
            $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;



            $jml++;
            $row++;
            $no++;

            if ($jumlah_data == $jml) {

                $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah");
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
                $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
                $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
                $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
                $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $total_pajak);


                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );


                // var_dump($row);
                // die;
                if ($jumlah_data == $jml) {
                    //var_dump($row);die;
                    $space = $row + 1;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $tahunTotal1);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $tahunTotal2);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $tahunTotal3);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $tahunTotal4);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $tahunTotal5);

                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_jan);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_feb);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_mar);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_apr);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_mei);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_jun);
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_jul);
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_agu);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_sep);
                    $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $total_okt);
                    $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $total_nov);
                    $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $total_des);
                    $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $total_total_pajak);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffc000');

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }


                if ($jumlah_data == $jml) {
                    //var_dump($row);die;
                    $space = $row + 3;
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
                        array(
                            'font' => array(
                                'bold' => true
                            ),
                        )
                    );
                }

                //var_dump($space);die;
                $space = $space + 1;
                $no_keterangan = 0;
                $total_wp = 0;
                //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
                //if($this->_idp == '8'){
                $query_keterangan = "SELECT
        											wp.CPM_KECAMATAN_WP,
        											count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
        										FROM
        											patda_wp wp
        											INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
        											AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
        										WHERE
        											wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
        										GROUP BY
        											CPM_KECAMATAN_WP 
        										ORDER BY
        											CPM_KECAMATAN_WP ASC";
                //}


                $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
                while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . $row_keterangan['CPM_KECAMATAN_WP']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
                    $space++;
                    $no_keterangan++;
                    $total_wp += $row_keterangan['TOTAL'];
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );
            }
            gc_collect_cycles();
        }


        // echo "COBA AH";
        // die;

        /** style **/
        // judul dok + judul tabel
        $objPHPExcel->getActiveSheet()->getStyle('A1:V5')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getAlignment()->setWrapText(true);

        // border
        $objPHPExcel->getActiveSheet()->getStyle('A7:U' . $row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    )
                )
            )
        );

        // fill tabel header
        $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->getStartColor()->setRGB('E4E4E4');

        // format angka col I & K
        $objPHPExcel->getActiveSheet()->getStyle('E11:U' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(15);
        for ($x = "A"; $x <= "H"; $x++) {
            if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
            else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }

        //  }
        // die(var_dump($_REQUEST['CPM_JENIS_PJK']));
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
        $objWriter->save('php://output');
        mysqli_close($this->Conn);
    }
}
