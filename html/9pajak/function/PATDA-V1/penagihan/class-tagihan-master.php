<?php

class TagihanPajak {

    private $CPM_ID;
    private $CPM_TGL_INPUT;
    private $CPM_JENIS_PAJAK;
    private $CPM_NO_SPTPD;
    private $CPM_NPWPD;
    private $CPM_NAMA_WP;
    private $CPM_ALAMAT_WP;
    private $CPM_NAMA_OP;
    private $CPM_ALAMAT_OP;
    private $CPM_LAMPIRAN;
    private $CPM_PETUGAS;
    private $CPM_STATUS;

    #obj
    private $Conn;
    private $Json;
    private $Data;
    private $Message;

    #var 
    public $arr_pajak = array(1 => "Air Bawah Tanah", 2 => "Hiburan", 3 => "Hotel", 4 => "Mineral Non Logam dan Batuan", 5 => "Parkir",
        6 => "Penerangan Jalan", 7 => "Reklame", 8 => "Restoran", 9 => "Sarang Walet");
    public $arr_bulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");
    
    #request
    public $_a; #app
    public $_m; #modul_id    
    public $_mod; #mod
    public $_f; #function 
    public $_s; #tab 
    public $_id; #tab 
    public $_sts; #status berkas
    public $_i; #id tab

    function __construct() {
        global $DBLink, $json, $data;

        unset($this->arr_pajak[1]);
        unset($this->arr_pajak[2]);
        unset($this->arr_pajak[4]);
        unset($this->arr_pajak[6]);
        unset($this->arr_pajak[7]);
        unset($this->arr_pajak[9]);

        $this->Conn = $DBLink;
        $this->Json = $json;
        $this->Data = $data;
        $this->Message = class_exists("Message") ? new Message() : "";

        $BERKAS = isset($_POST['BERKAS']) ? $_POST['BERKAS'] : array();

        foreach ($BERKAS as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }

        $this->CPM_LAMPIRAN = isset($_POST['CPM_LAMPIRAN']) ? $_POST['CPM_LAMPIRAN'] : array();

        $this->_a = isset($_REQUEST['a']) ? $_REQUEST['a'] : "";
        $this->_m = isset($_REQUEST['m']) ? $_REQUEST['m'] : "";
        $this->_f = isset($_REQUEST['f']) ? $_REQUEST['f'] : "";
        $this->_s = isset($_REQUEST['s']) ? $_REQUEST['s'] : "";
        $this->_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
        $this->_sts = isset($_REQUEST['sts']) ? $_REQUEST['sts'] : "";
        $this->_mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : "";
        $this->_i = isset($_REQUEST['i']) ? $_REQUEST['i'] : "";
    }

    public function get_berkas() {
        #inisialisasi data kosong
        $data = array("CPM_ID" => "", "CPM_TGL_INPUT" => date("d-m-Y"), "CPM_JENIS_PAJAK" => "",
            "CPM_NO_SPTPD" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
            "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_LAMPIRAN" => "",
            "CPM_PETUGAS" => "", "CPM_STATUS" => "",
        );

        #query untuk mengambil data berkas
        $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_ID = '{$this->_id}'";
        $result = mysql_query($query, $this->Conn);
        #jika ada data 
        if (mysql_num_rows($result) > 0) {
            $data = mysql_fetch_assoc($result);
        }
        return $data;
    }

    public function search_sptpd() {
        $data = array("CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "", "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "result" => 0);
        if ($this->CPM_JENIS_PAJAK == 3) {
            $query = "SELECT pr.* FROM PATDA_HOTEL_DOC pjk INNER JOIN PATDA_HOTEL_DOC_TRANMAIN tr ON
                    pjk.CPM_ID = tr.CPM_TRAN_HOTEL_ID INNER JOIN PATDA_HOTEL_PROFIL pr ON
                    pjk.CPM_ID_PROFIL = pr.CPM_ID
                    WHERE pjk.CPM_NO = '{$this->CPM_NO_SPTPD}' 
                    AND tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG ='0'";
            $result = mysql_query($query, $this->Conn);
            if (mysql_num_rows($result) > 0) {
                $data = mysql_fetch_assoc($result);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 5) {
            $query = "SELECT pr.* FROM PATDA_PARKIR_DOC pjk INNER JOIN PATDA_PARKIR_DOC_TRANMAIN tr ON
                    pjk.CPM_ID = tr.CPM_TRAN_PARKIR_ID INNER JOIN PATDA_PARKIR_PROFIL pr ON
                    pjk.CPM_ID_PROFIL = pr.CPM_ID
                    WHERE pjk.CPM_NO = '{$this->CPM_NO_SPTPD}' 
                    AND tr.CPM_TRAN_STATUS = '2' AND CPM_TRAN_FLAG ='0'";
            $result = mysql_query($query, $this->Conn);
            if (mysql_num_rows($result) > 0) {
                $data = mysql_fetch_assoc($result);
                $data['CPM_NAMA_OP'] = $data['CPM_NOMOR_OP'];
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 8) {
            $query = "SELECT pr.* FROM PATDA_RESTORAN_DOC pjk INNER JOIN PATDA_RESTORAN_DOC_TRANMAIN tr ON
                    pjk.CPM_ID = tr.CPM_TRAN_RESTORAN_ID INNER JOIN PATDA_RESTORAN_PROFIL pr ON
                    pjk.CPM_ID_PROFIL = pr.CPM_ID
                    WHERE pjk.CPM_NO = '{$this->CPM_NO_SPTPD}' 
                    AND tr.CPM_TRAN_STATUS = '2' AND CPM_TRAN_FLAG ='0'";
            $result = mysql_query($query, $this->Conn);
            if (mysql_num_rows($result) > 0) {
                $data = mysql_fetch_assoc($result);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        }
    }

    public function save() {
        $this->CPM_ID = c_uuid();
        $this->CPM_STATUS = count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
        $this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

        $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_NO_SPTPD ='{$this->CPM_NO_SPTPD}'";
        $result = mysql_query($query, $this->Conn);
        if (mysql_num_rows($result) == 0) {
            $query = sprintf("INSERT INTO PATDA_BERKAS 
                    (CPM_ID,CPM_TGL_INPUT,CPM_JENIS_PAJAK,CPM_NO_SPTPD,CPM_NPWPD,
                    CPM_NAMA_WP,CPM_ALAMAT_WP,CPM_NAMA_OP,CPM_ALAMAT_OP,CPM_LAMPIRAN,
                    CPM_PETUGAS,CPM_STATUS)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s')", $this->CPM_ID, $this->CPM_TGL_INPUT, $this->CPM_JENIS_PAJAK, $this->CPM_NO_SPTPD, $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_LAMPIRAN, $this->CPM_PETUGAS, $this->CPM_STATUS
            );
            mysql_query($query, $this->Conn);
        } else {
            $this->Message->setMessage("Gagal disimpan, Berkas untuk No. SPTPD <b>{$this->CPM_NO_SPTPD}</b> sudah diinput sebelumnya!");
        }
    }

    public function update() {
        $this->CPM_STATUS = count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
        $this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

        $query = sprintf("UPDATE PATDA_BERKAS SET                    
                    CPM_LAMPIRAN = '%s',
                    CPM_PETUGAS = '%s',
                    CPM_STATUS = '%s'
                    WHERE CPM_ID = '{$this->CPM_ID}'", $this->CPM_LAMPIRAN, $this->CPM_PETUGAS, $this->CPM_STATUS
        );
        mysql_query($query, $this->Conn);
    }

    public function redirect() {
        $url = "../../../../main.php?param=" . base64_encode("a={$this->_a}&m={$this->_m}");
        header("location:{$url}");
    }

    public function filtering($id) {
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
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "penagihan";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable_basic.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: 'WP Yang Melapor',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: 30,
                            sorting: true,
                            defaultSorting: 'CPM_TGL_INPUT ASC',
                            //selecting: true,
                            //multiselect: true,
                            //selectingCheckboxes: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                CPM_ID: {key: true,list: false},                                 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_NO_SPTPD: {title: 'No. SPTPD',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'}, 
                                CPM_TGL_JATUH_TEMPO: {title: 'Jatuh Tempo',width: '10%'}
                            },
//                            recordsLoaded: function (event, data) {
//                                for ( var i in data.records) {                                  
//                                    if (data.records[i].CPM_TAHUN_PAJAK == '2014') {
//                                        $('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+index+')').css('background-color', '#FF0000');
//                                    }
//                                }
//                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val()                                    
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        
        echo $html;                
       
    }
    
    public function grid_table2(){
        $DIR = "PATDA-V1";
        $modul = "penagihan";
        $html = "<br/>
                 <div id=\"laporanPajak2-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('#laporanPajak2-{$this->_i}').jtable({
                            title: 'WP Yang Tidak Melapor',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: 30,
                            sorting: true,
                            defaultSorting: 'CPM_NPWPD ASC',
                            //selecting: true,
                            //multiselect: true,
                            //selectingCheckboxes: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data2.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                CPM_ID: {key: true,list: false},                                 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_NO_SPTPD: {title: 'No. SPTPD',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                               // CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'}, 
                               // CPM_TGL_JATUH_TEMPO: {title: 'Jatuh Tempo',width: '10%'}
                            },
//                            recordsLoaded: function (event, data) {
//                                for (var i in data.records) {
//                                    if (data.records[i].CPM_TAHUN_PAJAK == '2014') {
//                                        $('#laporanPajak2-{$this->_i}').find('.jtable tbody tr:eq(' + i + ')').css('cssText','background-color: #CCC!important;color:red');
//                                    }
//                                }
//                            }
                        });
                          $('#laporanPajak2-{$this->_i}').jtable('load'); 
                          $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak2-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val()                                    
                            });
                        });
                        
                    });
                </script>";
        echo $html;
    }
    public function grid_data() {
        try {
            if ($this->_i == 1) {                
                $this->grid_data_pajak_disetujui();
            }
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
    private function grid_data_pajak_disetujui() {
        $where = "tr.CPM_TRAN_STATUS='5'";

        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND prf.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND pjk.CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";

        $arrPajak = array(3 => "HOTEL", 5 => "PARKIR", 8 => "RESTORAN");
        if((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")){
            $arrPajak = array($_REQUEST['CPM_JENIS_PAJAK']=> $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
        }
        
        $arrFunction = array(3 => "fPatdaPelayanan3", 5 => "fPatdaPelayanan5", 8 => "fPatdaPelayanan8");

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT pjk.CPM_ID
                        FROM PATDA_{$pjk}_DOC pjk 
                        INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
                        INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as pajak";

        $result = mysql_query($query, $this->Conn);
        $row = mysql_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT pajak.* FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, pjk.CPM_MASA_PAJAK, pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, 
                        prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_PETUGAS, pjk.CPM_TGL_JATUH_TEMPO,
                        pjk.CPM_TGL_LAPOR as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
                        FROM PATDA_{$pjk}_DOC pjk 
                        INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
                        INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as pajak ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysql_query($query, $this->Conn);

        $rows = array();
        $no = 1;
        while ($row = mysql_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => $no));


            $base64 = "a={$this->_a}&m={$this->_m}&f={$arrFunction[$row['CPM_JENIS_PAJAK']]}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NO_SPTPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SPTPD']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_MASA_PAJAK'] = $this->arr_bulan[$row['CPM_MASA_PAJAK']];
            $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
            $rows[] = $row;
            $no++;
        }

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysql_close($this->Conn);
    }
    
    public function grid_data2() {
        try {
            if ($this->_i == 1) {                
                $this->grid_data_pajak_disetujui2();
            }
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
    private function grid_data_pajak_disetujui2() {
        $where = "prf.CPM_AKTIF=1 and pjk.CPM_MASA_PAJAK <> ".(int)date('m');

        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND prf.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND pjk.CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";

        $arrPajak = array(3 => "HOTEL", 5 => "PARKIR", 8 => "RESTORAN");
        if((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")){
            $arrPajak = array($_REQUEST['CPM_JENIS_PAJAK']=> $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
        }
        
        $arrFunction = array(3 => "fPatdaPelayanan3", 5 => "fPatdaPelayanan5", 8 => "fPatdaPelayanan8");

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT pjk.CPM_ID
                        FROM PATDA_{$pjk}_DOC pjk 
                        left JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
                        
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as pajak";
         
        $result = mysql_query($query, $this->Conn);
        $row = mysql_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT pajak.* FROM (";
        foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, pjk.CPM_MASA_PAJAK, pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, 
                        prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_PETUGAS 
                        FROM PATDA_{$pjk}_DOC pjk
                        left JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
                        
                        WHERE {$where} ) UNION";
        }
        $query = substr($query, 0, strlen($query) - 5);
        $query.= ") as pajak ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysql_query($query, $this->Conn);
        
        $rows = array();
        $no = 1;
        while ($row = mysql_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => $no));


            $base64 = "a={$this->_a}&m={$this->_m}&f={$arrFunction[$row['CPM_JENIS_PAJAK']]}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NO_SPTPD'] = "{$row['CPM_NO_SPTPD']}";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_MASA_PAJAK'] = $this->arr_bulan[$row['CPM_MASA_PAJAK']];
            $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
            $rows[] = $row;
            $no++;
        }

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysql_close($this->Conn);
    }
    private function get_config_value($id, $key) {
        $query = "SELECT * FROM CENTRAL_APP_CONFIG WHERE CTR_AC_AID = '{$id}' AND CTR_AC_KEY = '{$key}'";
        $res = mysql_query($query, $this->Conn);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysql_error();
        }
        while ($row = mysql_fetch_assoc($res)) {
            return $row['CTR_AC_VALUE'];
        }
    }

    public function print_buktiterima() {
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_berkas();

        $html = "<table border=\"1\" cellpadding=\"5\">
                    <tr>
                        <!--LOGO-->
                        <td align=\"center\" width=\"28%\">

                        </td>
                        <!--COP-->
                        <td align=\"center\" width=\"72%\" colspan=\"2\">
                            <br>
                            PEMERINTAH KOTA PALANGKA RAYA<br/>DINAS PENGELOLAAN KEUANGAN<br/>DAN KEKAYAAN DAERAH<br/><br/>
                            <font class=\"normal\" size=\"-1\">Jl. Tjilik Riwut No. 98 Km. 55<br/>Palangka raya - Kalimantan Tengah 73112</font>
                        </td>
                    </tr>
                    <tr>
                        <!--ISI-->
                        <td colspan=\"3\">
                            <font size=\"-1\">
                            <table border=\"0\" cellpadding=\"1\" cellspacing=\"5\">
                                <tr>
                                    <td colspan=\"3\" align=\"center\">BUKTI PENERIMAAN PAJAK " . strtoupper($this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]) . "<br></td>
                                </tr>
                                <tr>
                                    <td width=\"125\">Nomor</td><td width=\"10\">:</td>
                                    <td width=\"180\">{$DATA['CPM_NO_SPTPD']}</td>
                                </tr>
                                <tr>
                                    <td>Nama Wajib Pajak</td><td>:</td>
                                    <td>{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Surat Masuk</td><td>:</td>
                                    <td>{$DATA['CPM_TGL_INPUT']}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td><td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>                    
                                <tr>
                                    <td>Jenis Pajak</td><td>:</td>
                                    <td>{$this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td></td><td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td><td></td>
                                    <td>KOTA PALANGKA RAYA, " . date("d-m-Y") . " 
                                        <br>
                                        <br>
                                        <br>
                                        <br>                                        
                                        <hr style=\"width:120px\">
                                        NIP. </td>
                                </tr>
                            </table>
                            </font>				
                        </td>
                    </tr>
                </table>";

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
        $pdf->SetMargins(2, 4, 2);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $fileLogo = $this->get_config_value($this->_a, 'LOGO_CETAK_PDF');
        $pdf->AddPage('P', 'A6');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $fileLogo, 6, 11, 20, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('bukti_penerimaan.pdf', 'I');
    }

    public function print_disposisi() {
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_berkas();

        $radio_lampiran[1] = strpos($DATA['CPM_LAMPIRAN'], "1") === false ? "[_]" : "[x]";
        $radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "[_]" : "[x]";
        $radio_lampiran[3] = strpos($DATA['CPM_LAMPIRAN'], "3") === false ? "[_]" : "[x]";

        $html = "<table border=\"1\" cellpadding=\"10\">
                    <tr>
                        <td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
                        <td align=\"center\" width=\"60%\">
                            <!-- <font size=\"+4\"> --> PEMERINTAH KOTA PALANGKA RAYA<br/>DINAS PENDAPATAN DAERAH<br />
                            <!-- </font> -->
                        </td>
                        <!--KOSONG-->
                        <td rowspan=\"2\" align=\"center\" width=\"20%\">
                        </td>
                    </tr>
                    <tr>
                        <td align=\"center\">
                            <font class=\"normal\">Jl. Tjilik Riwut No. 98 Km. 55<br/>Palangka raya - Kalimantan Tengah 73112</font>
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"2\" cellspacing=\"7\">
                                <tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">BUKTI PENERIMAAN PAJAK " . strtoupper($this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]) . "<br /></font></td></tr>
                                <tr>
                                    <td>Nomor</td><td width=\"20\">:</td>
                                    <td>{$DATA['CPM_NO_SPTPD']}</td>
                                </tr>
                                <tr>
                                    <td>Nama Wajib Pajak</td><td>:</td>
                                    <td>{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Surat Masuk</td><td>:</td>
                                    <td>{$DATA['CPM_TGL_INPUT']}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td><td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td><td>:</td>
                                    <td>{$this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]}</td>
                                </tr>
                                <tr>
                                    <td>Lampiran</td><td>:</td> 
                                    <td width=\"auto\" cellspacing=\"5\"><table>
                                            <tr><td>{$radio_lampiran[1]} SPTPD</td></tr>
                                            <tr><td>{$radio_lampiran[2]} Kwitansi</td></tr>
                                            <tr><td>{$radio_lampiran[3]} Lain-lain</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>					
                        </td>
                    </tr>
                    <!--SALINAN DISPOSISI-->
                    <tr>
                        <td colspan=\"3\"><table border=\"0\">
                                <tr>
                                    <td><table border=\"1\" cellpadding=\"12\">
                                            <tr>
                                                <td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <table border=\"0\" cellspacing=\"3\" width=\"250\">
                                                    <tr><td align=\"center\">Petugas Verifikasi</td></tr>
                                                    <tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr> 
                                                    <tr><td><br><br><br><br></td></tr>
                                                    <tr><td align=\"left\">___________________________________<br />NIP : </td></tr>
                                                    </table>
                                            </td>
                                            <td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <table border=\"0\" cellspacing=\"3\" width=\"250\">
                                                    <tr><td align=\"center\">Petugas Persetujuan</td></tr>
                                                    <tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
                                                    <tr><td><br><br><br><br></td></tr>
                                                    <tr><td>___________________________________<br />NIP : </td></tr>
                                                    </table>
                                            </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>";

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
        $pdf->SetMargins(5, 14, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $fileLogo = $this->get_config_value($this->_a, 'LOGO_CETAK_PDF');
        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $fileLogo, 10, 21, 30, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('disposisi.pdf', 'I');
    }

}

?>
