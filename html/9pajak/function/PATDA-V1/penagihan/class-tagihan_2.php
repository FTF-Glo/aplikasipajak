<?php

class TagihanPajak extends Pajak {

    protected $CPM_NO_STPD;
    protected $CPM_KURANG_BAYAR;
    protected $CPM_SANKSI;
    protected $CPM_TOTAL_SETOR;
    protected $CPM_BUNGA;
    protected $CPM_TAGIHAN;
    protected $CPM_MASA_STPD;
    protected $CPM_TAHUN_STPD;
    protected $CPM_TGL_INPUT_PAJAK;
    protected $CPM_TGL_SETOR;
    protected $CPM_NO_SKPDKB = "";
    protected $CPM_AYAT_PAJAK;
    protected $CPM_TGL_JATUH_TEMPO_PAJAK;

    public function __construct() {
        parent::__construct();
        $this->CPM_VERSION = "1";

        $STPD = isset($_POST['TAGIHAN']) ? $_POST['TAGIHAN'] : array();
        foreach ($STPD as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_data($type) {

        #$type = $this->idpajak_gw_to_sw[$type];
        $PAJAK = strtoupper($this->arr_pajak_table[$type]);
        $tbl_profil = "PATDA_{$PAJAK}_PROFIL";
        $gol_id = ((int) $type == 7 ? "''" : "CPM_REKENING");
        $id = $this->_idp;
        $query = "SELECT *, {$gol_id} as CPM_GOL_PAJAK FROM {$tbl_profil} WHERE CPM_ID = '{$id}'";
        $result = mysqli_query($this->Conn, $query);

        return mysqli_fetch_assoc($result);
    }

    private function get_last12Months() {
        $gol_id = strtoupper($this->arr_pajak_table[$this->_type]);
        $tbl_pajak = "PATDA_{$gol_id}_DOC";
        $tbl_pajak_tran = "PATDA_{$gol_id}_DOC_TRANMAIN";
        $tbl_profil = "PATDA_{$gol_id}_PROFIL";

        $query = "SELECT max(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK
                FROM {$tbl_pajak} pj INNER JOIN {$tbl_profil} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                INNER JOIN {$tbl_pajak_tran} tr ON pj.CPM_ID = tr.CPM_TRAN_{$gol_id}_ID                            
                WHERE tr.CPM_TRAN_STATUS = '5' and STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') > DATE_SUB(STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y'), INTERVAL 12 MONTH)
                GROUP BY pj.CPM_ID";
        $result = mysqli_query($this->Conn, $query);
        return mysqli_fetch_assoc($result);
    }

    public function get_tagihan($DATA = "") {
        $arrPajak = array();
		$arr_rek = array(
			1 => "4.1.1.8", 
			2 => "4.1.1.3", 
			3 => "4.1.1.1", 
			4 => "4.1.1.6", 
			5 => "4.1.1.7", 
			6 => "4.1.1.5", 
			7 => "4.1.1.4", 
			8 => "4.1.1.2", 
			9 => "4.1.1.9"
		);
		
		//echo $this->_type;exit;
		$arr_rekening = $this->getRekening($arr_rek[$this->_type]);
		
        if (is_array($DATA)) {
            $TOTAL = $this->get_last12Months();
            $TOTAL = $TOTAL['CPM_TOTAL_PAJAK'];

            $SANKSI = 2 / 100 * $TOTAL;
            $TOTAL_PAJAK = $SANKSI + $TOTAL;

            #inisialisasi data kosong
            $arrPajak = array("CPM_ID" => $this->_id, "CPM_ID_PROFIL" => $this->_idp, "CPM_TGL_INPUT" => "", "CPM_AYAT_PAJAK" => "", "CPM_JENIS_PAJAK" => $this->_type, "CPM_NO_STPD" => "",
                "CPM_NPWPD" => $DATA['CPM_NPWPD'], "CPM_NAMA_WP" => $DATA['CPM_NAMA_WP'], "CPM_ALAMAT_WP" => $DATA['CPM_ALAMAT_WP'], "CPM_NAMA_OP" => $DATA['CPM_NAMA_OP'],
                "CPM_ALAMAT_OP" => $DATA['CPM_ALAMAT_OP'], "CPM_AUTHOR" => "", "CPM_MASA_PAJAK" => "", "CPM_TAHUN_PAJAK" => "", "CPM_MASA_STPD" => date("m"),
                "CPM_TAHUN_STPD" => date("Y"), "CPM_KURANG_BAYAR" => $TOTAL, "CPM_SANKSI" => $SANKSI, "CPM_TOTAL_PAJAK" => $TOTAL_PAJAK, "CPM_TGL_INPUT_PAJAK" => "",
                "CPM_TGL_JATUH_TEMPO" => "", "CPM_TGL_JATUH_TEMPO_PAJAK" => "", "CPM_TGL_SETOR" => "", "CPM_TOTAL_SETOR" => 0, "CPM_BUNGA" => 0, "CPM_TAGIHAN" => 0,
                "CPM_TERBILANG" => "", "CPM_TIMESTAMP" => "", "CPM_VERSION" => 1, "ACTION" => 0, "CPM_REKENING" => $arr_rekening['ARR_REKENING'], "CPM_AYAT_PAJAK" => $DATA['CPM_GOL_PAJAK']);

            $CPM_ID = $this->_id;
        } else {
            $CPM_ID = $DATA;
        }

		//print_r($arrPajak);
        $query = "SELECT * FROM PATDA_STPD WHERE CPM_ID = '{$CPM_ID}'";
        $result = mysqli_query($this->Conn, $query);
        #jika ada data 
        if (mysqli_num_rows($result) > 0 && $this->_i != 1) {
            $arrPajak = mysqli_fetch_assoc($result);
            $arrPajak['ACTION'] = 1;
            $arrPajak = array_merge($arrPajak, $arr_rekening);
			if(empty($arrPajak['CPM_TOTAL_SETOR'])){						
				$arrPajak = array_merge($arrPajak, array( "CPM_TOTAL_SETOR" => 0, "CPM_BUNGA" => 0, "CPM_TAGIHAN" => 0, "CPM_TERBILANG" => ""));
			}
        }
		//print_r($arrPajak);
        return $arrPajak;
    }

    private function last_version() {
        $query = "SELECT * FROM PATDA_STPD_TRANMAIN WHERE CPM_TRAN_STPD_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        return $data['CPM_TRAN_STPD_VERSION'];
    }

    public function new_version() {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save()) {
            $query = "UPDATE PATDA_STPD_TRANMAIN SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_STPD_ID='{$id}'";
            mysqli_query($this->Conn, $query);
        }
    }

    private function validasi_save() {
        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        $query = "SELECT s.CPM_NO_STPD, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG, s.CPM_TAHUN_PAJAK, s.CPM_MASA_PAJAK, s.CPM_JENIS_PAJAK
                FROM PATDA_STPD s INNER JOIN PATDA_STPD_TRANMAIN tr ON s.CPM_ID = tr.CPM_TRAN_STPD_ID
                WHERE (s.CPM_NPWPD='{$this->CPM_NPWPD}' AND s.CPM_JENIS_PAJAK='{$this->CPM_JENIS_PAJAK}' AND s.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' 
                AND s.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}' AND s.CPM_VERSION='{$this->CPM_VERSION}') OR s.CPM_NO_STPD='{$this->CPM_NO_STPD}'
                ORDER BY tr.CPM_TRAN_STATUS DESC LIMIT 0,1";

        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        if ($this->notif == true) {
            if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_JENIS_PAJAK == $data['CPM_JENIS_PAJAK']) {
				$msg = "Gagal disimpan, STPD Pajak {$this->arr_pajak[$data['CPM_JENIS_PAJAK']]} untuk tahun <b>{$this->CPM_TAHUN_PAJAK}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dibuatkan sebelumnya!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
            }
        }

        $respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
        $respon['data'] = $data;

        return $respon;
    }

    public function save() {
        $validasi = $this->validasi_save();
        $res = false;
        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4' && $validasi['data']['CPM_TRAN_FLAG'] == '0')) {

            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_KURANG_BAYAR = str_replace(",", "", $this->CPM_KURANG_BAYAR);
            $this->CPM_SANKSI = str_replace(",", "", $this->CPM_SANKSI);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);

            $this->CPM_TGL_INPUT = date("d-m-Y");
            $this->CPM_ID = c_uuid();

            #query untuk mengambil no urut pajak
            $no = $this->get_config_value($this->_a, "PATDA_TAX{$this->_type}_STPD_COUNTER");
            $this->CPM_NO_STPD = str_pad($no, 9, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->_type] . "/STPD/" . date("Y");

            $query = sprintf("INSERT INTO PATDA_STPD 
                    (CPM_ID, CPM_ID_PROFIL, CPM_TGL_INPUT, CPM_JENIS_PAJAK, CPM_NO_STPD, CPM_NPWPD, CPM_AYAT_PAJAK,
                     CPM_NAMA_WP, CPM_ALAMAT_WP, CPM_NAMA_OP, CPM_ALAMAT_OP, CPM_AUTHOR, CPM_TGL_JATUH_TEMPO_PAJAK,
                     CPM_MASA_PAJAK, CPM_TAHUN_PAJAK, CPM_MASA_STPD, CPM_TAHUN_STPD, CPM_KURANG_BAYAR,
                     CPM_SANKSI, CPM_TOTAL_PAJAK, CPM_TGL_INPUT_PAJAK, CPM_TGL_JATUH_TEMPO, CPM_TGL_SETOR,
                     CPM_VERSION)
                     VALUES ('%s','%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s')", $this->CPM_ID, $this->CPM_ID_PROFIL, $this->CPM_TGL_INPUT, 
                             $this->CPM_JENIS_PAJAK, $this->CPM_NO_STPD, $this->CPM_NPWPD, $this->CPM_AYAT_PAJAK, $this->CPM_NAMA_WP, 
                             $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_AUTHOR, 
                             $this->CPM_TGL_JATUH_TEMPO_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_MASA_STPD, 
                             $this->CPM_TAHUN_STPD, $this->CPM_KURANG_BAYAR, $this->CPM_SANKSI, $this->CPM_TOTAL_PAJAK, 
                             $this->CPM_TGL_INPUT_PAJAK, $this->CPM_TGL_JATUH_TEMPO, $this->CPM_TGL_SETOR, $this->CPM_VERSION);

            $res = mysqli_query($this->Conn, $query);
            $this->update_counter($this->_a, "PATDA_TAX{$this->_type}_STPD_COUNTER");

            if ($res) {
                $param = array();
                $param['CPM_TRAN_STPD_VERSION'] = $this->CPM_VERSION;
                $param['CPM_TRAN_STATUS'] = "2";
                $param['CPM_TRAN_FLAG'] = "0";
                $param['CPM_TRAN_DATE'] = date("d-m-Y");
                $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
                $param['CPM_TRAN_OPR_DISPENDA'] = "";
                $param['CPM_TRAN_READ'] = "0";
                $param['CPM_TRAN_INFO'] = "";
                $this->save_tranmain($param);
                $res = $this->save_berkas_masuk($this->CPM_JENIS_PAJAK, "CPM_STPD");
                
                if($res){
					$_SESSION['_success'] = 'Berkas berhasil disimpan';
				}else{
					$_SESSION['_error'] = 'Data Pajak gagal disimpan';
				}
            }
        }
        return $res;
    }

    public function verifikasi() {
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_NO_STPD = '{$this->CPM_NO_STPD}' AND CPM_STATUS='1'";
            $res = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($res) == 0) {
                $this->Message->setMessage("Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!");
                return false;
            }
        }
        $this->persetujuan();

        #validasi hanya satu tahap yaitu verifikasi saja
        /* $status = ($this->AUTHORITY == 1) ? 3 : 4;
          $param['CPM_TRAN_HOTEL_VERSION'] = $this->CPM_VERSION;
          $param['CPM_TRAN_STATUS'] = $status;
          $param['CPM_TRAN_FLAG'] = "0";
          $param['CPM_TRAN_DATE'] = date("d-m-Y");
          $param['CPM_TRAN_OPR'] = "";
          $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
          $param['CPM_TRAN_READ'] = "0";
          $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
          $this->save_tranmain($param); */
    }

    public function persetujuan() {
        $status = ($this->AUTHORITY == 1) ? 5 : 4;
        $param['CPM_TRAN_STPD_VERSION'] = $this->CPM_VERSION;
        $param['CPM_TRAN_STATUS'] = $status;
        $param['CPM_TRAN_FLAG'] = "0";
        $param['CPM_TRAN_DATE'] = date("d-m-Y");
        $param['CPM_TRAN_OPR'] = "";
        $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
        $param['CPM_TRAN_READ'] = "0";
        $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
        
        //echo '<pre>',print_r($_POST),'</pre>'; echo $this->CPM_TOTAL_PAJAK;exit;
        
        $res = $this->save_tranmain($param);
        if ($this->AUTHORITY == 1 && $res == true) {
            $arr_config = $this->get_config_value($this->_a);
            $this->update_jatuh_tempo($arr_config['TENGGAT_WAKTU']);
            $this->CPM_NO = $this->CPM_NO_STPD;
            $this->IS_STPD = 1;
            $res = $this->save_gateway($this->CPM_JENIS_PAJAK, $arr_config);
            
            if($res){
				$_SESSION['_success'] = 'Data Pajak berhasil disetujui';
			}else{
				$_SESSION['_error'] = 'Data Pajak gagal disetujui';
			}
        }
    }

    private function update_jatuh_tempo($dbLimit) {
        $query = "UPDATE PATDA_STPD SET CPM_TGL_JATUH_TEMPO = DATE_ADD(CURDATE(), INTERVAL $dbLimit DAY)
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
    }

    private function save_tranmain($param) {
        #insert tranmain 
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_STPD_ID = $this->CPM_ID;

        $query = "UPDATE PATDA_STPD_TRANMAIN SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_STPD_ID = '{$CPM_TRAN_STPD_ID}'";
        $res = mysqli_query($this->Conn, $query);

        $query = sprintf("INSERT INTO PATDA_STPD_TRANMAIN 
                    (CPM_TRAN_ID, CPM_TRAN_STPD_ID, CPM_TRAN_STPD_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE, 
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_READ,CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s')", $CPM_TRAN_ID, $CPM_TRAN_STPD_ID, $param['CPM_TRAN_STPD_VERSION'], $param['CPM_TRAN_STATUS'], $param['CPM_TRAN_FLAG'], $param['CPM_TRAN_DATE'], $param['CPM_TRAN_OPR'], $param['CPM_TRAN_OPR_DISPENDA'], $param['CPM_TRAN_READ'], $param['CPM_TRAN_INFO']
        );
        return mysqli_query($this->Conn, $query);
    }

    public function update() {

        $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
        $this->CPM_KURANG_BAYAR = str_replace(",", "", $this->CPM_KURANG_BAYAR);
        $this->CPM_SANKSI = str_replace(",", "", $this->CPM_SANKSI);


        $query = sprintf("UPDATE PATDA_STPD SET                     
                     CPM_AUTHOR = '%s', 
                     CPM_TOTAL_PAJAK = '%s', 
                     CPM_KURANG_BAYAR = '%s', 
                     CPM_SANKSI = '%s'                     
                     WHERE
                     CPM_ID = '{$this->CPM_ID}'", $this->CPM_AUTHOR, $this->CPM_TOTAL_PAJAK, $this->CPM_KURANG_BAYAR, $this->CPM_SANKSI
        );

        $res = mysqli_query($this->Conn, $query);
        if($res){
			$_SESSION['_success'] = 'Berkas berhasil diupdate';
		}else{
			$_SESSION['_error'] = 'Data Pajak gagal diupdate';
		}
    }

    public function delete() {
        $query = "DELETE FROM PATDA_STPD WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
    }

    public function filtering($id) {
        if ($id == 6) {
            $html = "<div class=\"filtering\">
                    <form>
                        Jenis Pajak : 
                        <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >
                        <option value=\"\">All</option>";
            foreach ($this->arr_pajak as $x => $y) {
                $html .= "<option value=\"{$x}\">{$y}</option>";
            }
            $html.= "</select>
                        No. SPTPD : <input type=\"text\" name=\"CPM_NO_SPTPD-{$id}\" id=\"CPM_NO_SPTPD-{$id}\" >  
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >
                        Tahun Pajak : 
                        <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\" >
                        <option value=\"\">All</option>";
            for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                $html.= "<option value='{$th}'>{$th}</option>";
            }
            $html.= "</select>
                        Bulan Pajak : 
                        <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\" >
                        <option value=\"\">All</option>";
            foreach ($this->arr_bulan as $x => $y) {
                $html .= "<option value=\"" . str_pad($x, 2, 0, STR_PAD_LEFT) . "\">{$y}</option>";
            }
            $html.= "</select>
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        } else if ($id == 1) {
            $html = "<div class=\"filtering\">
                    <form>                        
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >                 
                        Nama WP : <input type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >    
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        } else {
            $html = "<div class=\"filtering\">
                    <form>
                        Jenis Pajak : 
                        <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >
                        <option value=\"\">All</option>";
            foreach ($this->arr_pajak as $x => $y) {
                $html .= "<option value=\"{$x}\">{$y}</option>";
            }
            $html.= "</select>
                        No. STPD : <input type=\"text\" name=\"CPM_NO_STPD-{$id}\" id=\"CPM_NO_STPD-{$id}\" >  
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >                        
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        }
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "penagihan";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting:" . ($this->_i == 6 ? "'saved_date ASC'," : "'CPM_NPWPD ASC'," ) . "
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },                            
                        fields: {";
        if ($this->_i == 6) {
            $html .= "              NO : {title: 'No',width: '3%'},
                                    id_switching: {key: true,list: false},                                 
                                    simpatda_type: {title: 'Jenis Pajak',width: '10%'},
                                    sptpd: {title: 'No. SPTPD',width: '10%'},                                
                                    simpatda_bulan_pajak: {title: 'Masa Pajak',width: '10%'},
                                    simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '10%'},
                                    npwpd: {title: 'NPWPD',width: '10%'},
                                    saved_date: {title:'Tanggal Masuk',width: '10%'},
                                    expired_date: {title:'Tanggal Jatuh Tempo',width: '10%'},
                                    simpatda_dibayar: {title: 'Total Pajak',width: '10%'}
                                }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val()
                            });
                        });";
        } else if ($this->_i == 1) {
            $html .= "          NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},                                
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_NAMA_WP: {title: 'Nama WP',width: '10%'},
                                CPM_NAMA_OP: {title: 'Nama OP',width: '10%'},
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val()
                            });
                        });";
        } else {
            $html .= "          NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},                                 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_NO_STPD: {title: 'No. STPD',width: '10%'},                                
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Tagihan',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '10%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '10%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
                                },
                                recordsLoaded: function (event, data) {
                                    for (var i in data.records) {
                                        if (data.records[i].READ == '0') {
                                            $('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#a0a0a0','border':'1px #CCC solid'});
                                        }
                                    }
                                }
                            });
                            $('#cari-{$this->_i}').click(function (e) {
                                e.preventDefault();
                                $('#laporanPajak-{$this->_i}').jtable('load', {
                                    CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                    CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                    CPM_NO_STPD : $('#CPM_NO_STPD-{$this->_i}').val(),
                                });
                            });";
        }
        $html .= "          $('#cari-{$this->_i}').click();                        
                    });
                </script>";

        echo $html;
    }

    public function grid_data() {
        try {
            if ($this->_i == 6) {
                $this->grid_data_expired();
            } else if ($this->_i == 1) {
                $this->grid_data_wp();
            } else {
                $this->grid_data_tagihan();
            }
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    private function grid_data_expired() {
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $dbLimit = $arr_config['TENGGAT_WAKTU'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName);

        $where = "payment_flag = '0' and date_format(date(expired_date),'%d-%m-%Y')<NOW()=1";

        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND npwpd like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND sptpd like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND simpatda_type like \"{$_REQUEST['CPM_JENIS_PAJAK']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND simpatda_tahun_pajak = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND simpatda_bulan_pajak = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW WHERE {$where}";

        $result = mysqli_query($Conn_gw, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT * FROM SIMPATDA_GW WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($Conn_gw, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&i={$this->_i}&f={$this->_f}&type={$row['simpatda_type']}&id={$row['id_switching']}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['sptpd'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['sptpd']}</a>";
            $row['simpatda_type'] = $this->arr_pajak[$row['simpatda_type']];
            $row['simpatda_dibayar'] = number_format($row['simpatda_dibayar'], 0);
            $row['simpatda_bulan_pajak'] = $this->arr_bulan[(int) $row['simpatda_bulan_pajak']];
            $row['npwpd'] = Pajak::formatNPWPD($row['npwpd']);
            $rows[] = $row;
        }

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysqli_close($this->Conn);
    }

    private function grid_data_wp() {
        $where = "CPM_AKTIF='1' AND CPM_APPROVE ='1'";

        $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";

        $arrPajak = array();
        foreach ($this->arr_pajak_table as $idpjk => $pjk) {
            $arrPajak[$idpjk] = "PATDA_" . strtoupper($pjk) . "_PROFIL";
        }

        if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
            $arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
        }

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT CPM_ID
                        FROM {$pjk}
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as profil";

        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT profil.* FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, CPM_NPWPD, CPM_NAMA_WP, CPM_ALAMAT_WP, CPM_NAMA_OP, CPM_ALAMAT_OP
                        FROM {$pjk}
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as profil ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $base64 = "a={$this->_a}&m={$this->_m}&type={$row['CPM_JENIS_PAJAK']}&f={$this->_f}&idp={$row['CPM_ID']}&mod={$this->_mod}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NPWPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">".Pajak::formatNPWPD($row['CPM_NPWPD'])."</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $rows[] = $row;
        }

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        #$jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysqli_close($this->Conn);
    }

    private function grid_data_tagihan() {

        $where = "(";
        $where.= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' ";
        if ($this->_s == 0) { #semua data
            $where.= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        } else {
            $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
        }
        $where.= ") ";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND s.CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND s.CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_STPD s INNER JOIN PATDA_STPD_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where}";

        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT * FROM PATDA_STPD s INNER JOIN PATDA_STPD_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $row['READ'] = 1;
            if ($this->_s != 0) { #untuk menandai dibaca atau belum
                $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;
            }

            $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&info={$row['CPM_TRAN_INFO']}&f={$this->_f}&flg={$row['CPM_TRAN_FLAG']}&type={$row['CPM_JENIS_PAJAK']}&id={$row['CPM_ID']}&idp={$row['CPM_ID_PROFIL']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
            $url = "main.php?param=" . base64_encode($base64);
            $row['CPM_NO_STPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_STPD']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
            $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
            $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
            $rows[] = $row;
        }

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysqli_close($this->Conn);
    }

    public function print_sspd() {
        global $sRootPath;
        $jenis_pajak = $this->arr_pajak_table[$this->CPM_JENIS_PAJAK];
        $DATA = $this->get_tagihan($this->CPM_ID);
		
        #print_r($DATA);exit;
		
        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        
        $BULAN_PAJAK = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
        $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$BULAN_PAJAK}";
		$KODE_PAJAK = $this->idpajak_sw_to_gw[$this->CPM_JENIS_PAJAK];
		if ($KODE_PAJAK == 7 || $KODE_PAJAK > 20) {
            $KODE_PAJAK = $this->non_reguler[$this->CPM_JENIS_PAJAK];
            $PERIODE = substr($this->CPM_NO_STPD, -2)."0" . substr($this->CPM_NO_STPD, 0, 9);
        }
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
        $KODE_AREA = $config['KODE_AREA'];
        
        //get payment code
		$dbName = $config['PATDA_DBNAME'];
        $dbHost = $config['PATDA_HOSTPORT'];
        $dbPwd = $config['PATDA_PASSWORD'];
        $dbTable = $config['PATDA_TABLE'];
        $dbUser = $config['PATDA_USERNAME'];
        
        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName, $Conn_gw);
        
        $gw = $this->get_gw_byid($Conn_gw, $this->CPM_ID);
        
		$PAYMENT_CODE = $gw->payment_code;
		$DENDA = !empty($gw->patda_denda)? $gw->patda_denda : 0;
		
        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">   
                                       

                                        <table border=\"0\">
											<tr>
												<td width=\"70\">&nbsp;</td>
												<td width=\"350\" >
                                                PEMERINTAH KOTA " . strtoupper($KOTA) . "<br />      
                                                BADAN PENGELOLAAN KEUANGAN DAERAH<br /><br />        
                                                <font class=\"normal\" style=\"font-size:35px\">{$JALAN}<br>
                                                {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
												</td>
											</tr>
                                        </table>
                                        <br>
                                    </th>
                                    <th width=\"260\" align=\"center\">
                                        SURAT SETORAN<br/>
                                        PAJAK DAERAH
                                        (SSPD)<br/><br/>
                                        Bulan : {$this->arr_bulan[$DATA['CPM_MASA_PAJAK']]}<br/>
                                        Tahun : {$DATA['CPM_TAHUN_PAJAK']}
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td>&nbsp;
							<table width=\"100%\" border=\"0\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"230\">Nama Wajib Pajak</td>
                                    <td width=\"10\">:</td>
                                    <td width=\"470\">{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td>
                                    <td>:</td>
                                    <td>Pajak " . ucfirst($jenis_pajak) . "</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>NPWPD</td>
                                    <td>:</td>
                                    <td>".Pajak::formatNPWPD($DATA['CPM_NPWPD'])."</td>
                                </tr>
                                <tr>
									<td>Kode Area</td>
									<td>:</td>
									<td>{$KODE_AREA}</td>                                        
								</tr>
								<tr>
									<td>Tipe Pajak</td>
									<td>:</td>
									<td>{$KODE_PAJAK}</td>
								</tr>
								<tr>
									<td>Kode Bayar</td>
									<td>:</td>
									<td>{$PAYMENT_CODE}</td>
								</tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"900\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">      
                                <tr>
                                    <td><table width=\"900\" border=\"1\" cellpadding=\"3\">
                                            <tr>
                                                <th width=\"50\" align=\"center\">No.</th>
                                                <th width=\"400\" align=\"center\">RINCIAN</th>
                                                <th width=\"260\" align=\"center\">JUMLAH</th>
                                            </tr>
                                            <tr>
                                                <td>1.</td>
                                                <td align=\"left\">Kurang Bayar</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_KURANG_BAYAR'], 0) . "</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td align=\"left\">Sanksi</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_SANKSI'], 0) . "</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td>4.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td>5.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td align=\"center\" colspan=\"2\"><i>JUMLAH</i></td>
                                                <td align=\"right\">Rp. " . number_format($DATA['CPM_TOTAL_PAJAK'], 0) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\">
                                                    Dengan Huruf : {$DATA['CPM_TERBILANG']}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>                            
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"430\"></td>
                                    <td width=\"280\" align=\"center\">
                                    {$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                                    Penyetor<br/><br/>
                                    <br/>
                                    (" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>                                     
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"1\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"355\">Kepada Yth.<br/>
                                    Direktur Utama<br/>
                                    PT. Bank Sumsel Babel <br/>
                                    Agar menerima penyetoran pada Rekening<br/>
                                    Bendahara Umum Daerah Kab. {$KOTA}
                                    </td>
                                    <td width=\"355\" align=\"left\">Bank Sumsel Babel<br/>
                                    {$PROVINSI}<br/>
                                    Kode Rekening : <b> </b>
                                    </td>
                                </tr>
                            </table>
                        </td>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 8, 18, 18, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("sspd-{$jenis_pajak}.pdf", 'I');
    }

    public function print_stpd() {

        $STPD = $this->get_tagihan($this->CPM_ID);

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $BANK_NOREK = $config['BANK_NOREK'];
        $BANK = $config['BANK'];
        $KEPALA_DINAS = $config['KEPALA_DINAS_NAMA'];
        $NIP = $config['KEPALA_DINAS_NIP'];
       
        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>";
                                
        $html .= "
        <th width=\"150\" align=\"center\" rowspan=\"2\">
        &nbsp;
        </th>
        <th width=\"250\" class=\"head\" align=\"center\" rowspan=\"2\" style=\"text-align: center;\">
        <div style=\"font-size:15pt\">&nbsp;</div>
        PEMERINTAH KABUPATEN <br>". strtoupper($KOTA)."
        </th>
                    <th width=\"310\" align=\"center\">
                        
                    <span style=\"font-size:20pt\"><b> STPD </b> </span>
                    <br><br>(SURAT TAGIHAN PAJAK DAERAH)
                        </th>
                            </tr>
                                <tr>
                                    <td>
                                    <div>Masa Pajak : {$this->arr_bulan[$STPD['CPM_MASA_PAJAK']]} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                                    Tahun : {$STPD['CPM_TAHUN_PAJAK']}</div>
                                    </td>                            
                                </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td>
                            <table width=\"100%\" border=\"0\" cellpadding=\"5\">
                            <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No. STPD : {$STPD['CPM_NO_STPD']}</td>
                            </tr>
                              
                                <tr>
                                    <td width=\"150\">Nama</td>
                                    <td>: {$STPD['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat</td>
                                    <td>: {$STPD['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                <td>N.P.W.P.D - OP</td>
                                <td>: ".Pajak::formatNPWPD($STPD['CPM_NPWPD'])."</td>
                                </tr>
                                <tr>
                                <td>Tanggal Jatuh Tempo</td>
                                <td>: {$STPD['CPM_TGL_JATUH_TEMPO_PAJAK']}</td>
                                </tr>
                            </table><br/><br/><table width=\"100%\" align=\"center\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" class=\"child\">                            
                                <tr>
                                    <td width=\"30\">I.</td>
                                    <td align=\"left\" colspan=\"2\" width=\"680\">Berdasarkan Pasal 100 Undang Undang Nomor 28 Tahun 2019 tentang Pajak Daerah dan Retribusi Daerah, <br>telah dilakukan penelitian dari / atau Pemeriksaan atau Keterangan lain atas pelaksanaan kewajiban</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td width=\"200\" align=\"left\">Ayat Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$STPD['CPM_AYAT_PAJAK']}</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\">Nama Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$STPD['CPM_NAMA_OP']}<div>&nbsp;</div></td>
                                </tr>
                           
                                <tr>
                                    <td width=\"30\">II.</td>
                                    <td width=\"680\" colspan=\"2\" align=\"left\">Dari Penelitian dan atau Pemeriksaan tersebut diatas, penghitungan jumlah yang masih harus dibayar adalah sebagai berikut :</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td width=\"280\" align=\"left\">1. Pajak yang kurang dibayar (Pokok Pajak)</td>
                                    <td align=\"right\">Rp. " . number_format($STPD['CPM_KURANG_BAYAR'], 0) . "</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\">2. Sanksi Administrasi :</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\"> &nbsp;&nbsp;&nbsp;a. Bunga (pasal 100 ayat (2)</td>
                                    <td align=\"right\">Rp. " . number_format($STPD['CPM_SANKSI'], 0) . "</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\">3. Jumlah yang masih harus dibayar (1+2)</td>
                                    <td align=\"right\">Rp. " . number_format($STPD['CPM_TOTAL_PAJAK'], 0) . "</td>
                                </tr>
                            </table><br/>
                        </td>
                    </tr>                    
                    <tr>
                        <td align=\"center\">
                            <table width=\"100%\" border=\"0\" align=\"left\" padding=\"1\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td>
                                    &nbsp;&nbsp;&nbsp;&nbsp;TERBILANG : <table border=\"1\"><tr><td>&nbsp;&nbsp;".strtoupper($this->SayInIndonesian($STPD['CPM_TOTAL_PAJAK']))." RUPIAH</td></tr></table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <tr>
                        <td align=\"center\">
                            <table width=\"100%\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td><u><b>PERHATIAN :</b></u> 
                                        <ol>
                                            <li>Harap penyetoran dilakukan melalui BSP atau Kas Daerah (PT. BANK LAMPUNG) dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</li>
                                            <li>Apabila STPD ini tidak atau kurang bayar setelah lewat waktu paling lama 30 (tiga puluh) hari sejak STPD ini diterima dikenakan sanksi administrasi berupa bunga sebesar 2% per bulan</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"390\"></td>
                                    <td width=\"280\" align=\"center\">
                                    {$KOTA}, "  . date("d") . " {$this->arr_bulan[(int) date("m")]} " . " Tahun ". date("Y") ."<br/><br/>
                                        Kepala Dinas Pendapatan Daerah<br/><br/><br/><br/>
                                        <br/>
                                        <u>{$KEPALA_DINAS}</u><br/>
                                        NIP. {$NIP}<br/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                </table>
                <table width=\"710\" class=\"main\" border=\"0\">
                <tr>
                <td align=\"center\"><br/><span style=\"font-size:15pt\">- - - - - - - - - - - - - - - - - - - -</span> Gunting Disini <span style=\"font-size:15pt\">- - - - - - - - - - - - - - - - - - - - </span></td>
                </tr>
                </table>
                <table width=\"710\" class=\"main\" border=\"1\">
                <tr>
                <td>
                <table width=\"300\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"400\"></td>
                                    <td width=\"280\" >
                                    No. STPD :   {$STPD['CPM_NO_STPD']}                                  
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\" align=\"center\"><b>TANDA TERIMA</b></td>
                                </tr>
                                <tr>
                                    <td width=\"430\"><table border=\"0\" cellpadding=\"3\">
                                            <tr>
                                                <td width=\"100\">NPWPD</td>
                                                <td width=\"250\">: {$STPD['CPM_NPWPD']}</td>
                                            </tr>
                                            <tr>
                                                <td>NAMA</td>
                                                <td>: {$STPD['CPM_NAMA_WP']}</td>
                                            </tr>
                                            <tr>
                                                <td>ALAMAT</td>
                                                <td>: {$STPD['CPM_ALAMAT_WP']}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width=\"240\" align=\"center\">
                                    <br/>
                                    {$KOTA}, "  . date("d") . " {$this->arr_bulan[(int) date("m")]} " . " Tahun ". date("Y") ."<br/><br/>
                                        Yang menerima<br/><br/>
                                        <br/>
                                 
                                        <br/>
                                        (" . str_pad("", 30, "_", STR_PAD_RIGHT) . ")<br/>                                     
                                    </td>
                                </tr>
                            </table>
                            </td>
                            </tr>
                            </table>
                           
                ";

        require_once("../../../inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(4, 4, 2);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 15, 11, 18, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("STPD {$this->arr_pajak[$STPD['CPM_JENIS_PAJAK']]}.pdf", 'I');
    }

    public function read_dokumen() {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM PATDA_STPD_TRANMAIN WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE PATDA_STPD_TRANMAIN SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function read_dokumen_notif() {
        $arr_tab = explode(";", $_POST['tab']);

        $notif = array();
        $notif['proses'] = 0;
        $notif['ditolak'] = 0;
        $notif['disetujui'] = 0;
        $notif['tertunda'] = 0;

        $where = " (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND ";
        $query = "SELECT count(s.CPM_ID) as total FROM PATDA_STPD s INNER JOIN PATDA_STPD_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE ";

        if (in_array("ditolak", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['ditolak'] = (int) $data['total'];
        }
        if (in_array("disetujui", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui'] = (int) $data['total'];
        }
        if (in_array("tertunda", $arr_tab) || in_array("proses", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result)) {
                $notif['tertunda'] = (int) $data['total'];
                $notif['proses'] = (int) $data['total'];
            }
        }
        echo $this->Json->encode($notif);
    }


}

?>
