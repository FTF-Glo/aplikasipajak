<?php

class PenetapanPajak extends Pajak
{

    public $_tambahan;

    function __construct()
    {
        parent::__construct();
        $this->CPM_VERSION = "1";
        $this->_tambahan = isset($_REQUEST['tambahan']) ? $_REQUEST['tambahan'] : "";

        $SKPDKB = isset($_POST['SKPDKB']) ? $_POST['SKPDKB'] : array();
        foreach ($SKPDKB as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }

        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_data($type)
    {

        $table = isset($_REQUEST['flg']) ? $this->arr_pajak_table[$type] : $this->arr_pajak_gw_table[$type];

        $tbl_pajak = "PATDA_{$table}_DOC";
        $tbl_profil = "PATDA_{$table}_PROFIL";

        $query = "SELECT '{$table}' as TYPE, pjk.*,prf.* FROM {$tbl_pajak} pjk INNER JOIN {$tbl_profil} prf ON
                    pjk.CPM_ID_PROFIL = prf.CPM_ID WHERE pjk.CPM_ID = '{$this->_id}'";
        $result = mysqli_query($this->Conn, $query);
        return (mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : $this->_id;
    }

    public function get_pajak($DATA = "", $_tambahan)
    {
        $arrPajak = array();

        if (is_array($DATA)) {
            #print_r($DATA);
            #inisialisasi data kosong
            $DATA['CPM_TOTAL_PAJAK'] = (isset($DATA['CPM_TOTAL_PAJAK']) && $DATA['CPM_TOTAL_PAJAK'] == "") ? 0 : $DATA['CPM_TOTAL_PAJAK'];
            $DATA['CPM_MASA_PAJAK'] = $DATA['TYPE'] == "REKLAME" ? (int) substr($DATA['CPM_MASA_PAJAK1'], 3, 2) : $DATA['CPM_MASA_PAJAK'];

            $arrPajak = array(
                "CPM_ID" => $DATA['CPM_ID'],
                "CPM_ID_PROFIL" => $DATA['CPM_ID_PROFIL'],
                "CPM_AUTHOR" => $DATA['CPM_AUTHOR'],
                "CPM_NO_SPTPD" => $DATA['CPM_NO'],
                "CPM_NO_SKPDKB" => "",
                "CPM_MASA_PAJAK" => $DATA['CPM_MASA_PAJAK'],
                "CPM_TAHUN_PAJAK" => $DATA['CPM_TAHUN_PAJAK'],
                "CPM_TGL_JATUH_TEMPO" => "",
                "CPM_NPWPD" => $DATA['CPM_NPWPD'],
                "CPM_NAMA_WP" => $DATA['CPM_NAMA_WP'],
                "CPM_ALAMAT_WP" => $DATA['CPM_ALAMAT_WP'],
                "CPM_NAMA_OP" => $DATA['CPM_NAMA_OP'],
                "CPM_ALAMAT_OP" => $DATA['CPM_ALAMAT_OP'],
                "CPM_TAHUN_PAJAK" => $DATA['CPM_TAHUN_PAJAK'],
                "CPM_JENIS_PAJAK" => $this->arr_pajak_gw_no[$this->_type],
                "CPM_PEMERIKSAAN_PAJAK" => "",
                "CPM_TOTAL_PAJAK" => $DATA['CPM_TOTAL_PAJAK'],
                "CPM_DENDA" => "",
                "CPM_BAYAR_TERUTANG" => "",
                "CPM_VERSION" => "1",
                "CPM_TERBILANG" => "",
                "CPM_KURANG_BAYAR" => "",
                "ACTION" => 0
            );

            #query untuk mengambil data pajak
            $CPM_ID = (isset($DATA['CPM_ID']) && $DATA['CPM_ID'] == "") ? $this->_id : $DATA['CPM_ID'];
        } else {
            $CPM_ID = $DATA;
        }

        $query = "SELECT * FROM PATDA_SKPDKB WHERE CPM_ID = '{$CPM_ID}' AND CPM_TAMBAHAN = '{$_tambahan}'";
        $result = mysqli_query($this->Conn, $query);
        #jika ada data 
        if (mysqli_num_rows($result) > 0 && $this->_i != 1) {
            $arrPajak = mysqli_fetch_assoc($result);
            $arrPajak['CPM_TERBILANG'] = $this->SayInIndonesian($arrPajak['CPM_KURANG_BAYAR']);
            $arrPajak['ACTION'] = 1;
        }
        return $arrPajak;
    }

    private function last_version()
    {
        $query = "SELECT * FROM PATDA_SKPDKB_TRANMAIN WHERE CPM_TRAN_SKPDKB_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        return $data['CPM_TRAN_SKPDKB_VERSION'];
    }

    public function new_version()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save()) {
            $query = "UPDATE PATDA_SKPDKB_TRANMAIN SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_SKPDKB_ID='{$id}'";
            $res = mysqli_query($this->Conn, $query);

            if ($res) {
                $_SESSION['_success'] = 'Data Pajak versi ' . $new_version . ' berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Data Pajak versi ' . $new_version . ' gagal disimpan';
            }
        }
    }

    private function validasi_save()
    {
        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        $query = "SELECT s.CPM_NO_SPTPD, s.CPM_TAMBAHAN, s.CPM_NO_SKPDKB, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG
                FROM PATDA_SKPDKB s INNER JOIN PATDA_SKPDKB_TRANMAIN tr ON s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID
                WHERE s.CPM_NO_SPTPD='{$this->CPM_NO_SPTPD}' AND CPM_TAMBAHAN = '{$this->CPM_TAMBAHAN}'
                ORDER BY tr.CPM_TRAN_STATUS DESC LIMIT 0,1";
        //        echo $this->CPM_NO_SPTPD;        
        //        echo $query;exit;

        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        if ($this->notif == true) {
            if ($this->CPM_NO_SPTPD == $data['CPM_NO_SPTPD'] && $this->CPM_TAMBAHAN == $data['CPM_TAMBAHAN']) {
                $this->Message->setMessage("Gagal disimpan, {$this->arr_kurangbayar[$this->CPM_TAMBAHAN]} untuk No. SPTPD {$data['CPM_NO_SPTPD']} sudah diinput sebelumnya!");
            }
        }

        $respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
        $respon['data'] = $data;

        return $respon;
    }

    public function save()
    {
        $validasi = $this->validasi_save();
        $res = false;
        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4' && $validasi['data']['CPM_TRAN_FLAG'] == '0')) {

            //echo '<pre>',print_r($_POST),'</pre>';exit;
            $this->CPM_PEMERIKSAAN_PAJAK = str_replace(",", "", $this->CPM_PEMERIKSAAN_PAJAK);
            $this->CPM_DENDA = (int) str_replace(",", "", $this->CPM_DENDA);
            $this->CPM_KURANG_BAYAR = str_replace(",", "", $this->CPM_KURANG_BAYAR);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TGL_INPUT = date("d-m-Y");
            $this->CPM_ID = c_uuid();

            #query untuk mengambil no urut pajak            
            $no = $this->get_config_value($this->_a, "PATDA_TAX{$this->_type}_SKPDKB_COUNTER");
            $this->CPM_NO_SKPDKB = str_pad($no, 9, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->_type] . "/SKPDKB/" . date("Y");

            $query = sprintf("INSERT INTO PATDA_SKPDKB 
                    (CPM_ID, CPM_TGL_INPUT, CPM_JENIS_PAJAK, CPM_NO_SPTPD, CPM_NO_SKPDKB,
                     CPM_NPWPD, CPM_NAMA_WP, CPM_ALAMAT_WP, CPM_AUTHOR,CPM_VERSION,
                     CPM_MASA_PAJAK, CPM_TAHUN_PAJAK, CPM_PEMERIKSAAN_PAJAK, CPM_TGL_JATUH_TEMPO,
                     CPM_DENDA, CPM_KURANG_BAYAR, CPM_TOTAL_PAJAK, CPM_TAMBAHAN,
                     CPM_NAMA_OP, CPM_ALAMAT_OP)
                     VALUES ('%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s')", $this->CPM_ID, $this->CPM_TGL_INPUT, $this->CPM_JENIS_PAJAK, $this->CPM_NO_SPTPD, $this->CPM_NO_SKPDKB, $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_AUTHOR, $this->CPM_VERSION, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_PEMERIKSAAN_PAJAK, $this->CPM_TGL_JATUH_TEMPO, $this->CPM_DENDA, $this->CPM_KURANG_BAYAR, $this->CPM_TOTAL_PAJAK, $this->CPM_TAMBAHAN, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP);
            //echo $query;exit;
            $res = mysqli_query($this->Conn, $query);
            $this->update_counter($this->_a, "PATDA_TAX{$this->_type}_SKPDKB_COUNTER");
            if ($res) {
                /* $arr_config = $this->get_config_value($this->_a);
                  $this->CPM_TOTAL_PAJAK = $this->CPM_KURANG_BAYAR;
                  $this->CPM_AUTHOR = $this->CPM_AUTHOR;
                  $this->CPM_NO = $this->CPM_NO_SPTPD;
                  $this->save_gateway($this->CPM_JENIS_PAJAK, $arr_config); */
                $param = array();
                $param['CPM_TRAN_SKPDKB_VERSION'] = "1";
                $param['CPM_TRAN_STATUS'] = "2";
                $param['CPM_TRAN_FLAG'] = "0";
                $param['CPM_TRAN_DATE'] = date("d-m-Y");
                $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
                $param['CPM_TRAN_OPR_DISPENDA'] = "";
                $param['CPM_TRAN_INFO'] = "";
                $this->save_tranmain($param);
                $res = $this->save_berkas_masuk($this->CPM_JENIS_PAJAK, "CPM_SKPDKB");

                if ($res) {
                    $_SESSION['_success'] = 'Data Pajak berhasil disimpan';
                } else {
                    $_SESSION['_error'] = 'Data Pajak gagal disimpan';
                }
            }
        }
        return $res;
    }

    public function verifikasi()
    {
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
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
          $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
          $this->save_tranmain($param); */
    }

    public function persetujuan()
    {
        $status = ($this->AUTHORITY == 1) ? 5 : 4;
        $param['CPM_TRAN_SKPDKB_VERSION'] = $this->CPM_VERSION;
        $param['CPM_TRAN_STATUS'] = $status;
        $param['CPM_TRAN_FLAG'] = "0";
        $param['CPM_TRAN_DATE'] = date("d-m-Y");
        $param['CPM_TRAN_OPR'] = "";
        $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
        $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;

        $res = $this->save_tranmain($param);
        if ($this->AUTHORITY == 1 && $res == true) {
            $arr_config = $this->get_config_value($this->_a);
            $this->update_jatuh_tempo($arr_config['TENGGAT_WAKTU']);
            $this->CPM_NO = $this->CPM_NO_SKPDKB;
            $this->IS_SKPDKB = 1;
            $res = $this->save_gateway($this->CPM_JENIS_PAJAK, $arr_config);

            if ($res) {
                $_SESSION['_success'] = 'Data Pajak berhasil disetujui';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal disetujui';
            }
        }
    }

    private function update_jatuh_tempo($dbLimit)
    {
        $query = "UPDATE PATDA_SKPDKB SET CPM_TGL_JATUH_TEMPO = DATE_ADD(CURDATE(), INTERVAL $dbLimit DAY)
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
    }

    private function save_tranmain($param)
    {
        #insert tranmain 
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_SKPDKB_ID = $this->CPM_ID;

        $query = "UPDATE PATDA_SKPDKB_TRANMAIN SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_SKPDKB_ID = '{$CPM_TRAN_SKPDKB_ID}'";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        $query = sprintf(
            "INSERT INTO PATDA_SKPDKB_TRANMAIN 
                    (CPM_TRAN_ID, CPM_TRAN_SKPDKB_ID, CPM_TRAN_SKPDKB_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE, 
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')",
            $CPM_TRAN_ID,
            $CPM_TRAN_SKPDKB_ID,
            $param['CPM_TRAN_SKPDKB_VERSION'],
            $param['CPM_TRAN_STATUS'],
            $param['CPM_TRAN_FLAG'],
            $param['CPM_TRAN_DATE'],
            $param['CPM_TRAN_OPR'],
            $param['CPM_TRAN_OPR_DISPENDA'],
            $param['CPM_TRAN_INFO']
        );
        return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    }

    public function update()
    {
        $this->CPM_PEMERIKSAAN_PAJAK = str_replace(",", "", $this->CPM_PEMERIKSAAN_PAJAK);
        $this->CPM_DENDA = str_replace(",", "", $this->CPM_DENDA);
        $this->CPM_KURANG_BAYAR = str_replace(",", "", $this->CPM_KURANG_BAYAR);
        $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);

        $query = sprintf(
            "UPDATE PATDA_SKPDKB SET                     
                     CPM_AUTHOR = '%s', 
                     CPM_PEMERIKSAAN_PAJAK = '%s', 
                     CPM_DENDA = '%s', 
                     CPM_KURANG_BAYAR = '%s', 
                     CPM_TOTAL_PAJAK = '%s',
                     CPM_TERBILANG = '%s',
                     CPM_TAMBAHAN = '%s'
                     WHERE
                     CPM_ID = '{$this->CPM_ID}'",
            $this->CPM_AUTHOR,
            $this->CPM_PEMERIKSAAN_PAJAK,
            $this->CPM_DENDA,
            $this->CPM_KURANG_BAYAR,
            $this->CPM_TOTAL_PAJAK,
            $this->CPM_TERBILANG,
            $this->CPM_TAMBAHAN
        );
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        if ($res) {
            $_SESSION['_success'] = 'Data Pajak berhasil diupdate';
        } else {
            $_SESSION['_error'] = 'Data Pajak gagal diupdate';
        }
    }

    public function delete()
    {
        $query = "DELETE FROM PATDA_SKPDKB WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
    }

    public function filtering($id)
    {
        if ($this->_i == 1) $this->arr_pajak = $this->arr_pajak_gw;
        $html = "<div class=\"filtering\">
                    <form>
                        <label>Jenis Pajak : </label>
                        <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" >
                        <option value=\"\">All</option>";
        foreach ($this->arr_pajak as $x => $y) {
            $html .= "<option value=\"{$x}\">{$y}</option>";
        }
        $html .= "</select>";
        $html .= ($this->_i == 1) ? "" : " <label style=\"margin-left: 10px\">No. SKPDKB :</label> <input type=\"text\" name=\"CPM_NO_SKPDKB-{$id}\" id=\"CPM_NO_SKPDKB-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" > ";
        $html .= " <label style=\"margin-left: 10px\">No. SPTPD :</label> <input type = \"text\" name=\"CPM_NO_SPTPD-{$id}\" id=\"CPM_NO_SPTPD-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\"  >  
                        <label style=\"margin-left: 10px\">NPWPD :</label> <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\"  >                        
                        <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important; display: inline-block; margin-left: 15px\"><i class=\"fa fa-search\"></i> Cari</button>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table()
    {

        $DIR = "PATDA-V1";
        $modul = "penetapan";
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
                            defaultSorting: " . ($this->_i == 1 ? "'payment_paid ASC'," : "'CPM_JENIS_PAJAK ASC',") . " 
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {";
        if ($this->_i == 1) {
            $html .= "  NO : {title: 'No',width: '3%'},
                                id_switching: {key: true,list: false},                                 
                                simpatda_type: {title: 'Jenis Pajak',width: '10%'},
                                sptpd: {title: 'No. SPTPD',width: '10%'},                                
                                simpatda_bulan_pajak: {title: 'Masa Pajak',width: '10%'},
                                simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '10%'},
                                npwpd: {title: 'NPWPD',width: '10%'},
                                payment_paid: {title:'Tanggal Bayar',width: '10%'},
                                simpatda_dibayar: {title: 'Total Pajak',width: '10%'}
                                }
                            });
                            $('#cari-{$this->_i}').click(function (e) {
                                e.preventDefault();
                                $('#laporanPajak-{$this->_i}').jtable('load', {
                                    CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                    CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
                                    CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val()                                    
                                });
                            });";
        } else {
            $html .= "  NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},                                 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_NO_SKPDKB: {title: 'No SKPDKB/T',width: '10%'},                                
                                CPM_NO_SPTPD: {title: 'No. SPTPD',width: '10%'},                                
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_KURANG_BAYAR: {title: 'Kurang Bayar',width: '10%'},
                                CPM_TAMBAHAN: {title: 'Jenis',width: '10%'},
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
                                    CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
                                    CPM_NO_SKPDKB : $('#CPM_NO_SKPDKB-{$this->_i}').val(),
                                    CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val()                                    
                                });
                            });";
        }
        $html .= "$('#cari-{$this->_i}').click();                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data()
    {
        try {
            if ($this->_i == 1) {
                $this->grid_data_pembayarn_pajak();
            } else {
                $this->grid_data_kurang_bayar();
            }
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    private function grid_data_pembayarn_pajak()
    {

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $dbLimit = $arr_config['TENGGAT_WAKTU'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName);

        $where = "payment_flag = '1'";

        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND npwpd like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND sptpd like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND simpatda_type like \"{$_REQUEST['CPM_JENIS_PAJAK']}%\" " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW WHERE {$where}";

        $result = mysqli_query($Conn_gw, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT * FROM SIMPATDA_GW a INNER JOIN SIMPATDA_TYPE b ON a.simpatda_type = b.id WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($Conn_gw, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&i={$this->_i}&f={$this->_f}&type={$row['simpatda_type']}&id={$row['id_switching']}&sptpd={$row['sptpd']}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['sptpd'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['sptpd']}</a>";
            $row['simpatda_type'] = $row['jenis'];
            $row['simpatda_dibayar'] = number_format($row['simpatda_dibayar'], 2);
            $row['simpatda_bulan_pajak'] = substr($row['periode'], 0, 6) . "-" . substr($row['periode'], 6, 6);
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

    private function grid_data_kurang_bayar()
    {

        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' ";
        if ($this->_s == 0) { #semua data
            $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
        } else {
            $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
        }
        $where .= ") ";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND s.CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NO_SKPDKB']) && $_REQUEST['CPM_NO_SKPDKB'] != "") ? " AND s.CPM_NO_SKPDKB like \"{$_REQUEST['CPM_NO_SKPDKB']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND s.CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_SKPDKB s INNER JOIN PATDA_SKPDKB_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID WHERE {$where}";

        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT * FROM PATDA_SKPDKB s INNER JOIN PATDA_SKPDKB_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $row['READ'] = 1;
            if ($this->_s != 0) { #untuk menandai dibaca atau belum
                $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;
            }

            $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&info={$row['CPM_TRAN_INFO']}&f={$this->_f}&flg={$row['CPM_TRAN_FLAG']}&type={$row['CPM_JENIS_PAJAK']}&id={$row['CPM_ID']}&tambahan={$row['CPM_TAMBAHAN']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NO_SKPDKB'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SKPDKB']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_KURANG_BAYAR'] = number_format($row['CPM_KURANG_BAYAR'], 2);
            $row['CPM_MASA_PAJAK'] = isset($this->arr_bulan[(int) $row['CPM_MASA_PAJAK']]) ? $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']] : $row['CPM_MASA_PAJAK'];
            $row['CPM_TAMBAHAN'] = $this->arr_kurangbayar[$row['CPM_TAMBAHAN']];
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

    public function print_sspd_old()
    {
        global $sRootPath;
        $jenis_pajak = $this->arr_pajak_table[$this->CPM_JENIS_PAJAK];
        $DATA = $this->get_pajak($this->CPM_ID, $this->CPM_TAMBAHAN);

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
            $PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);
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
        $DENDA = !empty($gw->patda_denda) ? $gw->patda_denda : 0;

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">   
                                        PEMERINTAH KOTA " . strtoupper($KOTA) . "<br />      
                                        DINAS PENDAPATAN DAERAH<br /><br />        
                                        <font class=\"normal\">{$JALAN}<br>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
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
                        <td>
                            <table width=\"100%\" border=\"0\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"230\">Nama Wajib Pajak</td>
                                    <td width=\"10\">:</td>
                                    <td width=\"463\">{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td>
                                    <td>:</td>
                                    <td>Pajak " . ucfirst($jenis_pajak) . "</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}  Kec. Ciparay Ds.Pakutandang, Andir RT 03/04 Sebelah Kolam pemancingan</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_NPWPD']}</td>
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
                                                <td align=\"left\">Pemeriksaan Pajak</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_PEMERIKSAAN_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td align=\"left\">Denda</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_DENDA'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td align=\"left\">Penyetoran</td>
                                                <td align=\"right\"><u>" . number_format($DATA['CPM_TOTAL_PAJAK'], 2) . "</u></td>
                                            </tr>
                                            <tr>
                                                <td>4.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td align=\"center\" colspan=\"2\"><i>JUMLAH</i></td>
                                                <td align=\"right\">Rp. " . number_format($DATA['CPM_KURANG_BAYAR'], 2) . "</td>
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
                                    <td width=\"280\" align=\"center\">
										<br/><br/>
										Penyetor<br/><br/>
										<br/>
										(" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>
                                    </td>
                                    <td width=\"150\" align=\"center\"></td>
                                    <td width=\"280\" align=\"center\">
										{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
										Bendahara Penerima<br/><br/>
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
                                    PT. Bank Rakyat Indonesia (Persero)<br/>
                                    Agar menerima penyetoran pada Rekening<br/>
                                    Bendahara Umum Daerah Kota {$KOTA}
                                    </td>
                                    <td width=\"355\" align=\"left\">PT. Bank Rakyat Indonesia (Persero)<br/>
                                    {$PROVINSI}<br/>
                                    Kode Rekening : <b>0243-01-001331-30-1</b>
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
        $pdf->SetTitle('9 PAJAK ONLINE');
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 8, 18, 15, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("sspd-{$jenis_pajak}.pdf", 'I');
    }

    public function print_sspd()
    {
        global $sRootPath;
        $jenis_pajak = $this->arr_pajak_table[$this->CPM_JENIS_PAJAK];
        $DATA = $this->get_pajak($this->CPM_ID, $this->CPM_TAMBAHAN);

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
            $PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);
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
        $DENDA = !empty($gw->patda_denda) ? $gw->patda_denda : 0;

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">   
                                        PEMERINTAH " . strtoupper($KOTA) . "<br />      
                                        BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH<br />
                                        PEMERINTAH KABUPATEN LAMPUNG SELATAN <br/><br/>        
                                        <font class=\"normal\">{$JALAN}<br>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
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
                        <td>
                            <table width=\"100%\" border=\"0\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"230\">Nama Wajib Pajak</td>
                                    <td width=\"10\">:</td>
                                    <td width=\"463\">{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td>
                                    <td>:</td>
                                    <td>Pajak " . ucfirst($jenis_pajak) . "</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_NPWPD']}</td>
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
                                                <td align=\"left\">Pemeriksaan Pajak</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_PEMERIKSAAN_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td align=\"left\">Denda</td>
                                                <td align=\"right\">" . number_format($DATA['CPM_DENDA'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td align=\"left\">Penyetoran</td>
                                                <td align=\"right\"><u>" . number_format($DATA['CPM_TOTAL_PAJAK'], 2) . "</u></td>
                                            </tr>
                                            <tr>
                                                <td>4.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td align=\"center\" colspan=\"2\"><i>JUMLAH</i></td>
                                                <td align=\"right\">Rp. " . number_format($DATA['CPM_KURANG_BAYAR'], 2) . "</td>
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
                                    <td width=\"280\" align=\"center\">
										<br/><br/>
										Penyetor<br/><br/>
										<br/>
										(" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>
                                    </td>
                                    <td width=\"150\" align=\"center\"></td>
                                    <td width=\"280\" align=\"center\">
										{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
										Bendahara Penerima<br/><br/>
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
                                    PT. Bank Rakyat Indonesia (Persero)<br/>
                                    Agar menerima penyetoran pada Rekening<br/>
                                    Bendahara Umum Daerah Kota {$KOTA}
                                    </td>
                                    <td width=\"355\" align=\"left\">PT. Bank Rakyat Indonesia (Persero)<br/>
                                    {$PROVINSI}<br/>
                                    Kode Rekening : <b>0243-01-001331-30-1</b>
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
        $pdf->SetTitle('9 PAJAK ONLINE');
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 7, 18, 15, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("sspd-{$jenis_pajak}.pdf", 'I');
    }

    public function print_skpdkb_old()
    {

        $SKPDKB = $this->get_pajak($this->CPM_ID, $this->CPM_TAMBAHAN);
        $SKPDKB_JENIS = $this->arr_kurangbayar[$this->CPM_TAMBAHAN];

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
                                <tr>
                                    <th valign=\"top\" width=\"350\" align=\"center\">            
                                        <b>PEMERINTAH KOTA " .  strtoupper($KOTA) . "<br />      
                                            DINAS PENDAPATAN DAERAH</b><br /><br />        
                                        {$JALAN}<br>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}
                                    </th>
                                    <th width=\"250\" align=\"center\">
                                        <b>SURAT KETETAPAN<br/>
                                            PAJAK DAERAH KURANG BAYAR " . ($this->CPM_TAMBAHAN == 1 ? "TAMBAHAN" : "") . "<br/>
                                            (SKPDKB)</b><br/><br/>";
        $html .= str_replace(";", "&nbsp;", str_pad("BULAN : {$this->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]}", 20, ";", STR_PAD_RIGHT)) . "<br/>";
        $html .= str_replace(";", "&nbsp;", str_pad("TAHUN : {$SKPDKB['CPM_TAHUN_PAJAK']}", 20, ";", STR_PAD_RIGHT));

        $html .= "</th>
                                    <th width=\"110\" align=\"center\">
                                        <b>No. {$SKPDKB_JENIS}</b><br/>{$this->CPM_NO_SKPDKB}
                                    </th>
                                </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td><table width=\"100%\" border=\"0\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>: {$SKPDKB['CPM_NPWPD']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Nama</td>
                                    <td>: {$SKPDKB['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>: {$SKPDKB['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Jatuh Tempo</td>
                                    <td>: {$SKPDKB['CPM_TGL_JATUH_TEMPO']}</td>
                                </tr>
                            </table><br/><br/>
                            <table width=\"100%\" align=\"center\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" class=\"child\">                            
                                <tr>
                                    <td width=\"30\">I.</td>
                                    <td align=\"left\" colspan=\"2\" width=\"680\">Berdasarkan Peraturan Perundang-Undangan yang berlaku, telah dilakukan pemeriksaan atau keterangan lain atas pelaksanaan kewajiban</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td width=\"200\" align=\"left\">Jenis Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$this->arr_pajak[$SKPDKB['CPM_JENIS_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\">Masa Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$this->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td width=\"30\">II.</td>
                                    <td width=\"680\" colspan=\"2\" align=\"left\">Hasil pemerikasaan atau keterangan lain tersebut di atas, penghitungan jumlah yang seharusnya dibayar adalah sebagai berikut :</td>
                                </tr>
                            </table><br/><br/>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"child\">                            
                                <tr>
                                    <td align=\"left\" colspan=\"2\"><table cellpadding=\"2\" width=\"850\" cellspacing=\"0\" border=\"1\">
                                            <tr>
                                                <th width=\"150\" align=\"center\">Pemeriksaan Pajak (Rp)</th>
                                                <th width=\"150\" align=\"center\">Sanksi Denda</th>
                                                <th width=\"130\" align=\"center\">Penyetoran (Rp)</th>
                                                <th width=\"130\" align=\"center\">Kekurangan Setor (Rp)</th>
                                            </tr>
                                            <tr>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_PEMERIKSAAN_PAJAK'], 2) . "</td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_DENDA'], 2) . "</td>
                                                <td align=\"right\">{$SKPDKB['CPM_TOTAL_PAJAK']}</td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_KURANG_BAYAR'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\" align=\"center\">
                                                    <b>JUMLAH PEMBAYARAN</b>
                                                </td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_KURANG_BAYAR'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"4\">
                                                    Dengan Huruf : {$SKPDKB['CPM_TERBILANG']}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"center\">
                            <table width=\"100%\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td>PERHATIAN : 
                                        <ol>
                                            <li>Surat Ketetapan Pajak Daerah, yang selanjutnya disingkat {$SKPDKB_JENIS}, adalah Surat Ketetapan Pajak Daerah Kurang Bayar " . ($this->CPM_TAMBAHAN == 1 ? "Tambahan" : "") . " yang menentukan besarnya jumlah pokok pajak, jumlah kredit Pajak, Kekurangan Pembayaran Pokok Pajak, besarnya sanksi administratif dan jumlah yang masih harus dibayar.</li>
                                            <li>Apabila {$SKPDKB_JENIS} ini tidak atau kurang setelah lewat waktu paling lama 30 hari sejak {$SKPDKB_JENIS} ini diterima dikenakan Sanksi Administrasi berupa bunga sebesar 2% per bulan dibayar.</li>
                                            <li>Harap penyetoran ke Kas Daerah melalui Bendahara Penerima Dinas Pendapatan Daerah Kota {$KOTA} atau {$BANK} {$KOTA} Dengan Nomor Rekening : <b>{$BANK_NOREK}</b>.</li>
                                        </ol>
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
                                        " . str_replace(";", "&nbsp;", str_pad("{$KOTA}, ", 50, ";", STR_PAD_RIGHT)) . "<br/><br/>
                                        Kepala Dinas Pendapatan Daerah<br/>Kota {$KOTA}<br/><br/>
                                        <br/>
                                        <u>{$KEPALA_DINAS}</u><br/>
                                        NIP. {$NIP}<br/>
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
                                        No. SKPD :                                     
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\" align=\"center\"><b>TANDA TERIMA</b></td>
                                </tr>
                                <tr>
                                    <td width=\"430\"><table border=\"0\" cellpadding=\"3\">
                                            <tr>
                                                <td width=\"100\">NPWPD</td>
                                                <td width=\"250\">: {$SKPDKB['CPM_NPWPD']}</td>
                                            </tr>
                                            <tr>
                                                <td>NAMA</td>
                                                <td>: {$SKPDKB['CPM_NAMA_WP']}</td>
                                            </tr>
                                            <tr>
                                                <td>ALAMAT</td>
                                                <td>: {$SKPDKB['CPM_ALAMAT_WP']}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width=\"280\" align=\"center\">
                                        " . str_replace(";", "&nbsp;", str_pad("{$KOTA}, ", 50, ";", STR_PAD_RIGHT)) . "<br/><br/>
                                        Yang menerima<br/><br/>
                                        <br/>
                                        (" . str_pad("", 30, "_", STR_PAD_RIGHT) . ")<br/>                                     
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>";
        ob_clean();
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
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 5, 10, 18, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("{$SKPDKB_JENIS}.pdf", 'I');
    }

    public function print_skpdkb()
    {

        $SKPDKB = $this->get_pajak($this->CPM_ID, $this->CPM_TAMBAHAN);
        $SKPDKB_JENIS = $this->arr_kurangbayar[$this->CPM_TAMBAHAN];
        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $BANK_NOREK = $config['BANK_NOREK'];
        $BANK = $config['BANK'];
        $KEPALA_DINAS = $config['KEPALA_DINAS_NAMA'];
        $NIP = $config['KEPALA_DINAS_NIP'];


        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"1000\" border=\"1\" cellpadding=\"12\">
                                <tr>
                                    <th valign=\"top\" width=\"390\" align=\"center\">            
                                        <b style=\"font-size:26px\"><br/>
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
                                        {$NAMA_PENGELOLA}<br/>
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
                                        <br/>
                                        {$JALAN}<br/>
                                        {$KOTA}
                                        </b>
                                    </th>
                                    <th width=\"200\" align=\"center\">
                                        <b>SURAT KETETAPAN<br/>
                                            PAJAK DAERAH KURANG BAYAR " . ($this->CPM_TAMBAHAN == 1 ? "TAMBAHAN" : "") . "<br/>
                                            (SKPDKB)</b><br/><br/>";
        $html .= str_replace(";", "&nbsp;", str_pad("MASA PAJAK : {$this->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]}", 20, ";", STR_PAD_RIGHT)) . "<br/>";
        $html .= str_replace(";", "&nbsp;", str_pad("TAHUN : {$SKPDKB['CPM_TAHUN_PAJAK']}", 20, ";", STR_PAD_RIGHT));

        $html .= "</th>
                                    <th width=\"120\" align=\"center\">
                                    <br/><br/>
                                        <b>No. {$SKPDKB_JENIS} :</b><br/>{$this->CPM_NO_SKPDKB}
                                    </th>
                                </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td><table width=\"100%\" border=\"0\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>: {$SKPDKB['CPM_NPWPD']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Nama</td>
                                    <td>: {$SKPDKB['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>: {$SKPDKB['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Jatuh Tempo</td>
                                    <td>: {$SKPDKB['CPM_TGL_JATUH_TEMPO']}</td>
                                </tr>
                            </table><br/><br/>
                            <table width=\"100%\" align=\"center\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" class=\"child\">                            
                                <tr>
                                    <td width=\"30\">I.</td>
                                    <td align=\"left\" colspan=\"2\" width=\"680\">Berdasarkan Peraturan Perundang-Undangan yang berlaku, telah dilakukan pemeriksaan atau keterangan lain atas pelaksanaan kewajiban</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td width=\"200\" align=\"left\">Jenis Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$this->arr_pajak[$SKPDKB['CPM_JENIS_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td width=\"30\"></td>
                                    <td align=\"left\">Masa Pajak</td>
                                    <td width=\"480\" align=\"left\">: {$this->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td width=\"30\">II.</td>
                                    <td width=\"680\" colspan=\"2\" align=\"left\">Hasil pemerikasaan atau keterangan lain tersebut di atas, penghitungan jumlah yang seharusnya dibayar adalah sebagai berikut :</td>
                                </tr>
                            </table><br/><br/>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"child\">                            
                                <tr>
                                    <td align=\"left\" colspan=\"2\"><table cellpadding=\"2\" width=\"850\" cellspacing=\"0\" border=\"1\">
                                            <tr>
                                                <th width=\"150\" align=\"center\">Pemeriksaan Pajak (Rp)</th>
                                                <th width=\"150\" align=\"center\">Sanksi Denda</th>
                                                <th width=\"130\" align=\"center\">Penyetoran (Rp)</th>
                                                <th width=\"130\" align=\"center\">Kekurangan Setor (Rp)</th>
                                            </tr>
                                            <tr>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_PEMERIKSAAN_PAJAK'], 2) . "</td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_DENDA'], 2) . "</td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_TOTAL_PAJAK'], 2) . "</td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_KURANG_BAYAR'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\" align=\"center\">
                                                    <b>JUMLAH PEMBAYARAN</b>
                                                </td>
                                                <td align=\"right\">" . number_format($SKPDKB['CPM_KURANG_BAYAR'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"4\">
                                                    Dengan Huruf : {$SKPDKB['CPM_TERBILANG']}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"center\">
                            <table width=\"100%\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td><b>PERHATIAN :</b> 
                                        <ol>
                                            <li>Harap Penyetoran dilakukan melalui Bank yang ditunjuk dengan menggunakan Surat Setoran Pajak Daerah (SSPD).</li>
                                            <li>Wajib Pajak dilarang melakukan pembayaran Pajak Terutang kepada petugas penagih yang tidak menunjukkan / memberikan Surat ketetapan Pajak Daerah kurang bayar (SKPDKB).</li>
                                            <li>Apabila SKPDKB ini tidak atau kurang dibayar setelah tanggal jatuh tempo dikenakan Sanksi Administrasi Bunga sebesar
											2% perbulan dan ditagih dengan menggunakan Surat Tagihan Pajak.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\" style=\"font-size:26px;\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
								<tr>
                                    <td width=\"400\"></td>
                                    <td align=\"center\">
                                        <b>KABUPATEN LAMPUNG SELATAN, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                                                    A.n Kepala Badan Pengelola Pajak dan Retribusi Daerah<br/> 
                                                    Kabupaten Lampung Selatan, <br/>
                                                    Kepala Bidang Pengembangan dan Penetapan<br/>
                                                    <br/><br/><br/><br/>
                                                    <u>AULIA RAKHMAN, S.Pi., M.M.</u><br/>
                                                    NIP. 19870403 201101 1 003</b>
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
                                        No. {$SKPDKB_JENIS} : {$this->CPM_NO_SKPDKB}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\" align=\"center\"><b>TANDA TERIMA</b></td>
                                </tr>
                                <tr>
                                    <td width=\"430\"><table border=\"0\" cellpadding=\"3\">
                                            <tr>
                                                <td width=\"100\">NPWPD</td>
                                                <td width=\"250\">: {$SKPDKB['CPM_NPWPD']}</td>
                                            </tr>
                                            <tr>
                                                <td>NAMA</td>
                                                <td>: {$SKPDKB['CPM_NAMA_WP']}</td>
                                            </tr>
                                            <tr>
                                                <td>ALAMAT</td>
                                                <td>: {$SKPDKB['CPM_ALAMAT_OP']}</td>
                                                
                                            </tr>
                                        </table>
                                    </td>
                                    <td width=\"280\" align=\"center\">
                                        " . str_replace(";", "&nbsp;", str_pad("{$KOTA},<br/>" . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/> 
                                        Yang menerima, ", 50, ";", STR_PAD_RIGHT)) . "<br/><br/>
                                        <br/><br/>
                                        <br/>
                                        {$SKPDKB['CPM_NAMA_WP']}
                                        " . str_pad("", 30, "_", STR_PAD_RIGHT) . "                    
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>";
        ob_clean();
        require_once("../../../inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('SKPDKB');
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
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 5, 10, 18, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output("{$SKPDKB_JENIS}.pdf", 'I');
    }

    public function read_dokumen()
    {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM PATDA_SKPDKB_TRANMAIN WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE PATDA_SKPDKB_TRANMAIN SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function read_dokumen_notif()
    {
        $arr_tab = explode(";", $_POST['tab']);

        $notif = array();
        $notif['proses'] = 0;
        $notif['ditolak'] = 0;
        $notif['disetujui'] = 0;
        $notif['tertunda'] = 0;

        $where = " (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND ";
        $query = "SELECT count(s.CPM_ID) as total FROM PATDA_SKPDKB s INNER JOIN PATDA_SKPDKB_TRANMAIN tr ON
                  s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID WHERE ";

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
