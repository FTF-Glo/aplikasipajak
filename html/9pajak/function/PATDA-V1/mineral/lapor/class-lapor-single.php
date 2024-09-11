<?php

class LaporPajak extends Pajak
{
    #field
    #mineral

    public $id_pajak = 4;

    function __construct()
    {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }
    }

    public function get_pajak()
    {
        #inisialisasi data kosong
        $ms = $this->inisialisasi_masa_pajak();
        $masa_pajak = $ms['masa_pajak'];
        $tahun_pajak = $ms['tahun_pajak'];
        $masa_pajak1 = $ms['masa_pajak1'];
        $masa_pajak2 = $ms['masa_pajak2'];

        $respon['pajak'] = array(
            "CPM_ID" => "", "CPM_ID_PROFIL" => "", "CPM_NO" => "", "CPM_MASA_PAJAK" => $masa_pajak, "CPM_MASA_PAJAK1" => $masa_pajak1, "CPM_MASA_PAJAK2" => $masa_pajak2,
            "CPM_TAHUN_PAJAK" => $tahun_pajak, "CPM_TOTAL_OMZET" => "", "CPM_TOTAL_PAJAK" => "", "CPM_BAYAR_LAINNYA" => "", "CPM_DPP" => "", "CPM_BAYAR_TERUTANG" => "",
            "CPM_TARIF_PAJAK" => "", "CPM_TGL_LAPOR" => "", "CPM_TGL_JATUH_TEMPO" => "",
            "CPM_TERBILANG" => "", "CPM_VERSION" => "", "CPM_AUTHOR" => "", "CPM_NPWPD" => "",
            "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "", "CPM_PERDA" => "", "CPM_KETERANGAN" => "", "CPM_TIPE_PAJAK" => ""
        );
        $respon['profil'] = array();
        $respon['tarif'] = array();

        $pajak_atr = array("CPM_ATR_NAMA" => "", "CPM_ATR_VOLUME" => 0, "CPM_ATR_HARGA" => 0);
        $respon['pajak_atr'][0] = $pajak_atr;

        #query untuk mengambil data pajak
        $query = "SELECT pjk.* FROM PATDA_MINERAL_DOC pjk INNER JOIN PATDA_MINERAL_PROFIL prf ON
                    pjk.CPM_ID_PROFIL = prf.CPM_ID WHERE pjk.CPM_ID = '{$this->_id}'";

        $result = mysql_query($query, $this->Conn);
        $arr_rekening = $this->getRekening("4.1.1.06");

        #jika ada data 
        if (mysql_num_rows($result) > 0) {
            $respon['pajak'] = mysql_fetch_assoc($result);
            $profil = new ProfilPajak();
            $respon['profil'] = $profil->getDataProfilById($respon['pajak']['CPM_ID_PROFIL']);
            $respon['tarif'] = $respon['pajak']['CPM_TARIF_PAJAK'];
            $respon['pajak']['CPM_TERBILANG'] = $this->SayInIndonesian($respon['pajak']['CPM_TOTAL_PAJAK']);
        } else {
            $profil = new ProfilPajak();
            $respon['profil'] = $profil->getDataProfilByUser($this->Data->uname);
            $respon['tarif'] = 0;
        }

        #query untuk mengambil data atribut pajak
        $query = "SELECT atr.*,rek.kdrek, rek.nmrek FROM PATDA_MINERAL_DOC pjk INNER JOIN PATDA_MINERAL_DOC_ATR atr ON pjk.CPM_ID = atr.CPM_ATR_MINERAL_ID 
                  INNER JOIN PATDA_REK_PERMEN13 rek ON atr.CPM_ATR_NAMA=rek.kdrek WHERE pjk.CPM_ID = '{$this->_id}' ORDER BY atr.CPM_ATR_ID ASC";

        $result = mysql_query($query, $this->Conn);
        #jika ada data 
        if (mysql_num_rows($result) > 0) {
            $x = 0;
            while ($data = mysql_fetch_assoc($result)) {
                $respon['pajak_atr'][$x] = $data;
                $x++;
            }
        }

        $respon['pajak'] = array_merge($respon['pajak'], $arr_rekening);
        $respon['pajak']['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $respon['pajak']['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;
        return $respon;
    }

    public function filtering($id)
    {
        $html = "<div class=\"filtering\">
                    <form> 
                        <input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  
                        NAMA  : <input type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >  
                        TAHUN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
        $html .= "<option value=''>All</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $html .= "<option value='{$th}'>{$th}</option>";
        }
        $html .= "</select> MASA PAJAK : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
        $html .= "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $html .= "<option value='{$x}'>{$y}</option>";
        }
        $html .= "</select>
                Tanggal Lapor : <input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" class=\"date\" > s.d
                          <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" class=\"date\" >
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                        <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>        
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table()
    {
        $DIR = "PATDA-V1";
        $modul = "mineral";
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
                            defaultSorting: 'CPM_TGL_LAPOR DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'}, 
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '5%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '10%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
                                CPM_AUTHOR: {title: 'User Input',width: '10%'}    
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
                                CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data()
    {
        try {
            $where = "(";
            $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_mod == "pel") { #pelaporan
                if ($this->_s == 0) { #semua data
                    $where = " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
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
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
            $where .= ") ";
            $where .= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD = '{$_SESSION['npwpd']}' " : "";
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND SUBSTRING(CPM_MASA_PAJAK1,3,2) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_MINERAL_DOC} pj 
                        INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID
                        WHERE {$where}";
            $result = mysql_query($query, $this->Conn);
            $row = mysql_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG, 
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                        FROM {$this->PATDA_MINERAL_DOC} pj INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID                            
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            // var_dump($query);
            // die;
            $result = mysql_query($query, $this->Conn);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysql_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = 1;
                if ($this->_s != 0) { #untuk menandai dibaca atau belum
                    $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;
                }

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
                $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
                $row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
                $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);

                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['q'] = $query;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function grid_table_pelayanan()
    {
        $DIR = "PATDA-V1";
        $modul = "mineral";
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
                            defaultSorting: 'CPM_TGL_LAPOR DESC',
                            selecting: true,                            
                            actions: {
                                listAction: 'view/{$DIR}/pelayanan/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'}, 
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '5%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '10%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
                                CPM_AUTHOR: {title: 'User Input',width: '10%'}    
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
                                CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data_pelayanan()
    {
        try {

            $where = "(";
            $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }

            $where .= ") ";
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_MINERAL_DOC} pj 
                            INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID
                            WHERE {$where}";
            $result = mysql_query($query, $this->Conn);
            $row = mysql_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG, 
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                            FROM {$this->PATDA_MINERAL_DOC} pj INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID                            
                            WHERE {$where}
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysql_query($query, $this->Conn);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysql_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
                $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
                $row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
                $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);

                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['q'] = $query;
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    private function last_version()
    {
        $query = "SELECT * FROM {$this->PATDA_MINERAL_DOC_TRANMAIN} WHERE CPM_TRAN_MINERAL_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($res);

        return $data['CPM_TRAN_MINERAL_VERSION'];
    }

    private function validasi_save()
    {
        return $this->validasi_pajak(1);
    }

    private function validasi_update()
    {
        return $this->validasi_pajak(0);
    }

    private function validasi_pajak($input = 1)
    {
        $where = ($input == 1) ? "OR pj.CPM_NO='{$this->CPM_NO}'" : "AND pj.CPM_NO!='{$this->CPM_NO}'";

        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        $query = "SELECT pj.CPM_NO, pj.CPM_TAHUN_PAJAK, pj.CPM_MASA_PAJAK, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG 
                FROM {$this->PATDA_MINERAL_DOC} pj 
                INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID
                INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                WHERE 
                (pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
                ORDER BY tr.CPM_TRAN_STATUS DESC, pj.CPM_VERSION DESC LIMIT 0,1";
        #echo $query;exit;
        $res = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($res);

        if ($this->notif == true) {
            if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_TIPE_PAJAK == 1) {
                $this->Message->setMessage("Gagal disimpan, Pajak untuk tahun <b>{$this->CPM_TAHUN_PAJAK}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dilaporkan sebelumnya!");
            } elseif ($this->CPM_NO == $data['CPM_NO']) {
                $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
            }
        }

        $respon['result'] = mysql_num_rows($res) > 0 ? false : true;
        $respon['result'] = ($this->CPM_TIPE_PAJAK == 2) ? true : $respon['result'];
        $respon['data'] = $data;

        return $respon;
    }

    private function save_pajak($cpm_no = '')
    {
        $validasi = $this->validasi_save();

        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4')) {
            $this->Message->clearMessage();

            #update profil baru
            $query = "UPDATE {$this->PATDA_MINERAL_PROFIL} SET CPM_APPROVE ='1' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            mysql_query($query, $this->Conn);

            if (empty($cpm_no)) {
                #query untuk mengambil no urut pajak            
                $no = $this->get_config_value($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
                $this->CPM_NO = $this->get_config_value($this->_a, "KODE_SPTPD") . str_pad($no, 8, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->id_pajak] . "/" . date("y");
                $this->update_counter($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
            } else {
                $this->CPM_NO = $cpm_no;
            }

            #insert pajak baru
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_LAPOR = date("d-m-Y");
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);

            #$this->CPM_NO_SSPD = substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
            $this->CPM_NO_SSPD = $this->CPM_NO;

            $query = sprintf(
                "INSERT INTO {$this->PATDA_MINERAL_DOC} 
                    (CPM_ID,CPM_ID_PROFIL,CPM_NO,
                    CPM_MASA_PAJAK,CPM_TAHUN_PAJAK,CPM_TIPE_PAJAK,CPM_TGL_JATUH_TEMPO,
                    CPM_TOTAL_OMZET,CPM_TOTAL_PAJAK,CPM_TARIF_PAJAK,
                    CPM_TGL_LAPOR,CPM_KETERANGAN,CPM_VERSION,
                    CPM_AUTHOR,CPM_ID_TARIF,CPM_BAYAR_LAINNYA,
                    CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD,
                    CPM_MASA_PAJAK1,CPM_MASA_PAJAK2)
                    VALUES ( '%s','%s','%s',
                             '%s','%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s')",
                $this->CPM_ID,
                $this->CPM_ID_PROFIL,
                $this->CPM_NO,
                $this->CPM_MASA_PAJAK,
                $this->CPM_TAHUN_PAJAK,
                $this->CPM_TIPE_PAJAK,
                $this->CPM_TGL_JATUH_TEMPO,
                $this->CPM_TOTAL_OMZET,
                $this->CPM_TOTAL_PAJAK,
                $this->CPM_TARIF_PAJAK,
                $this->CPM_TGL_LAPOR,
                $this->CPM_KETERANGAN,
                $this->CPM_VERSION,
                $this->CPM_AUTHOR,
                $this->CPM_ID_TARIF,
                $this->CPM_BAYAR_LAINNYA,
                $this->CPM_DPP,
                $this->CPM_BAYAR_TERUTANG,
                $this->CPM_NO_SSPD,
                $this->CPM_MASA_PAJAK1,
                $this->CPM_MASA_PAJAK2
            );
            #echo $query;exit;
            $res = mysql_query($query, $this->Conn);

            if ($res) {
                $PAJAK_ATR = $_POST['PAJAK_ATR'];
                $x = 0;
                foreach ($PAJAK_ATR['CPM_ATR_NAMA'] as $nama) {

                    $volume = str_replace(",", "", $PAJAK_ATR['CPM_ATR_VOLUME'][$x]);
                    $harga = str_replace(",", "", $PAJAK_ATR['CPM_ATR_HARGA'][$x]);

                    if ($nama != "") {
                        $query = sprintf(
                            "INSERT INTO PATDA_MINERAL_DOC_ATR 
                            (CPM_ATR_MINERAL_ID, CPM_ATR_NAMA,
                             CPM_ATR_VOLUME, CPM_ATR_HARGA)
                             VALUES ('%s','%s','%s','%s')",
                            $this->CPM_ID,
                            $nama,
                            $volume,
                            $harga
                        );
                        mysql_query($query, $this->Conn);
                    }
                    $x++;
                }
            }
            return $res;
        }
        return false;
    }

    private function save_tranmain($param)
    {
        #insert tranmain 
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_MINERAL_ID = $this->CPM_ID;

        $query = "UPDATE {$this->PATDA_MINERAL_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_MINERAL_ID = '{$CPM_TRAN_MINERAL_ID}'";
        $res = mysql_query($query, $this->Conn);

        $query = sprintf(
            "INSERT INTO {$this->PATDA_MINERAL_DOC_TRANMAIN} 
                    (CPM_TRAN_ID, CPM_TRAN_MINERAL_ID, CPM_TRAN_MINERAL_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE, 
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')",
            $CPM_TRAN_ID,
            $CPM_TRAN_MINERAL_ID,
            $param['CPM_TRAN_MINERAL_VERSION'],
            $param['CPM_TRAN_STATUS'],
            $param['CPM_TRAN_FLAG'],
            $param['CPM_TRAN_DATE'],
            $param['CPM_TRAN_OPR'],
            $param['CPM_TRAN_OPR_DISPENDA'],
            $param['CPM_TRAN_INFO']
        );
        return mysql_query($query, $this->Conn);
    }

    public function save()
    {
        $this->CPM_VERSION = "1";
        if ($this->save_pajak()) {
            $param = array();
            $param['CPM_TRAN_MINERAL_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
        }
    }

    public function save_final()
    {
        $this->CPM_VERSION = "1";
        if ($this->save_pajak()) {
            $param['CPM_TRAN_MINERAL_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
        }
    }

    public function new_version()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_MINERAL_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_MINERAL_ID='{$id}'";
            mysql_query($query, $this->Conn);

            $param['CPM_TRAN_MINERAL_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
        }
    }

    public function new_version_final()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_MINERAL_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_MINERAL_ID='{$id}'";
            mysql_query($query, $this->Conn);

            $param['CPM_TRAN_MINERAL_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
        }
    }

    public function update_final()
    {
        $this->CPM_VERSION = "1";
        if ($this->update()) {
            $param['CPM_TRAN_MINERAL_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
        }
    }

    public function update()
    {
        $validasi = $this->validasi_update();
        if ($validasi['result'] == true) {
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);

            $query = sprintf("UPDATE {$this->PATDA_MINERAL_DOC} SET
                    CPM_TOTAL_OMZET = '%s',
                    CPM_TOTAL_PAJAK = '%s',
                    CPM_TARIF_PAJAK = '%s',
                    CPM_BAYAR_LAINNYA = '%s',
                    CPM_DPP = '%s',
                    CPM_BAYAR_TERUTANG = '%s',                    
                    CPM_KETERANGAN = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_TIPE_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s'
                    WHERE 
                    CPM_ID ='{$this->CPM_ID}'", $this->CPM_TOTAL_OMZET, $this->CPM_TOTAL_PAJAK, $this->CPM_TARIF_PAJAK, $this->CPM_BAYAR_LAINNYA, $this->CPM_DPP, $this->CPM_BAYAR_TERUTANG, $this->CPM_KETERANGAN, $this->CPM_MASA_PAJAK1, $this->CPM_MASA_PAJAK2, $this->CPM_TIPE_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_MASA_PAJAK);
            $res =  mysql_query($query, $this->Conn);

            if ($res) {
                $PAJAK_ATR = $_POST['PAJAK_ATR'];
                $x = 0;
                foreach ($PAJAK_ATR['CPM_ATR_NAMA'] as $nama) {

                    $volume = str_replace(",", "", $PAJAK_ATR['CPM_ATR_VOLUME'][$x]);
                    $harga = str_replace(",", "", $PAJAK_ATR['CPM_ATR_HARGA'][$x]);

                    if ($nama != "") {
                        $query = sprintf("UPDATE {$this->PATDA_MINERAL_DOC_ATR} SET
                            CPM_ATR_NAMA = '%s',
                            CPM_ATR_VOLUME = '%s',
                            CPM_ATR_HARGA = '%s'
                            WHERE
                            CPM_ATR_MINERAL_ID = '{$this->CPM_ID}'", $nama, $volume, $harga);
                        mysql_query($query, $this->Conn);
                    }
                    $x++;
                }
            }
            return $res;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->PATDA_MINERAL_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysql_query($query, $this->Conn);
        if ($res) {
            $query = "DELETE FROM PATDA_MINERAL_DOC_TRANMAIN WHERE CPM_TRAN_MINERAL_ID ='{$this->CPM_ID}'";
            mysql_query($query, $this->Conn);
        }
    }

    public function verifikasi()
    {
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
            $res = mysql_query($query, $this->Conn);
            if (mysql_num_rows($res) == 0) {
                $this->Message->setMessage("Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!");
                return false;
            }
        }
        $this->persetujuan();

        #validasi hanya satu tahap yaitu verifikasi saja
        /* $status = ($this->AUTHORITY == 1) ? 3 : 4;
          $param['CPM_TRAN_MINERAL_VERSION'] = $this->CPM_VERSION;
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
        $param['CPM_TRAN_MINERAL_VERSION'] = $this->CPM_VERSION;
        $param['CPM_TRAN_STATUS'] = $status;
        $param['CPM_TRAN_FLAG'] = "0";
        $param['CPM_TRAN_DATE'] = date("d-m-Y");
        $param['CPM_TRAN_OPR'] = "";
        $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
        $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
        $param['CPM_TRAN_READ'] = "";
        $res = $this->save_tranmain($param);
        if ($this->AUTHORITY == 1 && $res == true) {
            $arr_config = $this->get_config_value($this->_a);
            $this->save_gateway($this->id_pajak, $arr_config);
            $this->update_jatuh_tempo($this->EXPIRED_DATE);
        }
    }

    private function update_jatuh_tempo($dbLimit)
    {
        $query = "UPDATE {$this->PATDA_MINERAL_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysql_query($query, $this->Conn);
    }

    public function print_sptpd()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $NIP = $config['BAG_VERIFIKASI_NIP'];

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"700\" border=\"0\">
                                <tr>
                                    <th valign=\"top\" align=\"center\">                                   
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        DINAS PENDAPATAN DAERAH<br /><br />        
                                        <font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"450\"><table width=\"440\" class=\"header\" border=\"0\">
                                <tr class=\"first\">
                                    <td width=\"440\" valign=\"top\" align=\"center\" colspan=\"2\">
                                        <b>
                                            SURAT PEMBERITAHUAN PAJAK DAERAH (SPTPD)<br />
                                            PAJAK MINERAL BUKAN LOGAM DAN BATUAN
                                        </b><br/>
                                    </td>
                                </tr>
                                <tr>                             
                                    <td width=\"130\">No. SPTPD</td>
                                    <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_NO']}</td>                  
                                </tr>
                                <tr>
                                    <td>Masa Pajak</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
                                </tr>
                                <tr>
                                    <td>Tahun Pajak</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                                </tr>
                            </table>
                        </td>
                        <td width=\"260\">Kepada : <br/>
                            Yth. Kepala Dinas Pendapatan Daerah<br/>
                            {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}<br/>
                            di - {$KOTA}
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                <tr>
                                    <td>Perhatian : </td>
                                </tr> 
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;1. Harap diisi dalam rangkap 3 (tiga) ditulis dengan huruf CETAK atau diketik. </td>
                                </tr> 
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;2. Beri nomor pada kotak yang tersedia untuk jawaban yang diberikan.</td>
                                </tr> 
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;3. Setelah diisi dan ditandatangani harap diserahkan kembali kepada Dinas Pendapatan Daerah.</td>
                                </tr> 
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kota {$KOTA} paling lambat tanggal 30 bulan berikutnya.</td>
                                </tr> 
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;4. Keterlambatan penyerahan SPTPD akan dikenakan sanksi sesuai ketentuan berlaku.</td>
                                </tr> 
                            </table>
                        </td>
                    </tr> 
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                <tr>
                                    <th align=\"left\" colspan=\"2\"><strong>I. IDENTITAS WAJIB PAJAK</strong></th>
                                </tr>
                                <tr>
                                    <td width=\"150\">&nbsp;&nbsp;&nbsp;Nama Wajib Pajak</td>
                                    <td width=\"550\">: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Nama Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;NPWPD</td>
                                    <td>: {$DATA['profil']['CPM_NPWPD']}</td>
                                </tr>
                            </table>            
                        </td>
                    </tr>  
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"left\"><strong>II. DIISI OLEH PENGUSAHA MINERAL BUKAN LOGAM DAN BATUAN</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\">
                        a. Data Objek Pajak<br/>
                        <table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"1\" cellspacing=\"0\">
                                <tr>
                                    <td width=\"30\">No.</td>
                                    <td width=\"350\">Kode Rekening - Golongan</td>
                                    <td width=\"130\">Volume /<br/> Tonase (m <sup>3</sup>)</td>
                                    <td width=\"150\">Harga pasar/<br/> nilai standar (m <sup>3</sup>)</td>
                                </tr>";
        $xx = 0;
        foreach ($DATA['pajak_atr'] as $pajak_atr) {
            $html .= "<tr>
                        <td width=\"30\">" . (++$xx) . ".</td>
                        <td width=\"350\" align=\"left\" > {$pajak_atr['kdrek']} - {$pajak_atr['nmrek']}</td>
                        <td align=\"right\" width=\"130\">" . number_format($pajak_atr['CPM_ATR_VOLUME'], 0) . "</td>
                        <td align=\"right\" width=\"150\">Rp. " . number_format($pajak_atr['CPM_ATR_HARGA'], 2) . "</td>
                    </tr>";
        }
        $html .= "</table><br/><br/><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"1\" cellspacing=\"0\">                                            
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;b. Pembayaran Objek Pajak</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;c. Pembayaran lain-lain</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;d. Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;e. Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;f. Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> 0</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;g. Sanksi Administrasi</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> 0</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;h. Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;i. Data Pendukung</td>
                                    <td align=\"left\" width=\"430\"> </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a). Surat Setoran Pajak Daerah (SSPD)</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b). Rekapitulasi Penjualan / Omzet</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c). Rekapitulasi Penggunaan Bill / Bonbill</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                            </table> 
                        </td>  
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">
                                <tr>
                                    <td align=\"left\" colspan=\"2\">&nbsp;&nbsp;&nbsp;Demikian formulir ini diisi dengan sebenar-benarnya, dan apabila ada ketidakbenaran dalam melakukan kewajiban pengisian SPTPD ini, saya bersedia diberikan sanksi sesuai Peraturan Daerah yang berlaku.                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"350\">&nbsp;&nbsp;&nbsp;Diterima oleh Petugas,</td>
                                    <td align=\"left\" width=\"350\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . " </td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;Tanggal : </td>
                                    <td align=\"left\">WP/Penanggung Pajak/Kuasa</td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;<u>{$BAG_VERIFIKASI_NAMA}</u></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;NIP. {$NIP}</td>
                                    <td align=\"left\">Nama jelas/Cap/Stempel</td>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-mineral.pdf', 'I');
    }

    public function print_sspd()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KODE_AREA = $config['KODE_AREA'];

        $bulan_pajak = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
        $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$bulan_pajak}";

        $KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
            $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
            #$PERIODE = "000" . substr($this->CPM_NO, 0, 9);
            $PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);
        }
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">   
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        DINAS PENDAPATAN DAERAH<br /><br />        
                                        <font class=\"normal\">{$JALAN}<br>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </th>
                                    <th width=\"260\" align=\"center\">
                                        SURAT SETORAN<br/>
                                        PAJAK DAERAH
                                        (SSPD)<br/><br/>
                                        Tahun : {$DATA['pajak']['CPM_TAHUN_PAJAK']}
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td><table width=\"960\" border=\"0\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"230\">Nama Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Nama Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td>
                                    <td>: Pajak Mineral Non Logam dan Batuan</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>: {$DATA['profil']['CPM_NPWPD']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Kode Area</td>
                                    <td>: {$KODE_AREA}</td>                                        
                                </tr>                                 
                                <tr>
                                    <td width=\"230\">Tipe Pajak</td>
                                    <td>: {$KODE_PAJAK}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Periode / Kode Bayar</td>
                                    <td>: {$PERIODE}</td>
                                </tr> 
                                <tr>
                                    <td width=\"230\">NO SSPD</td>
                                    <td>: {$DATA['pajak']['CPM_NO_SSPD']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Masa Pajak</td>
                                    <td>: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
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
                                                <td align=\"left\">Pembayaran pajak Objek Pajak {$DATA['profil']['CPM_NAMA_OP']}
                                                    <br/>Keterangan : {$DATA['pajak']['CPM_KETERANGAN']}</td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td align=\"left\">Biaya lain</td>
                                                <td align=\"right\">0</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td align=\"left\">Biaya admin</td>
                                                <td align=\"right\">0</td>
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
                                                <td align=\"right\">Rp. " . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\">
                                                    Dengan Huruf : {$this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])}
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
                                    <td width=\"355\">SSPD ini berlaku setelah dilampiri dengan bukti pembayaran yang sah dari Bank</td>
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui teller dan ATM Bank Sumselbabel terdekat</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
					<tr>
						<td>
							<font size='2' color=red>
							Jatuh tempo : {$DATA['pajak']['CPM_TGL_JATUH_TEMPO']}
							Denda 2% per bulan maksimal 24 bulan
							</font>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 6, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-mineral.pdf', 'I');
    }

    public function read_dokumen()
    {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_MINERAL_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysql_query($select, $this->Conn);
            $data = mysql_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE {$this->PATDA_MINERAL_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysql_query($query, $this->Conn);
        }
    }

    public function read_dokumen_notif()
    {
        $arr_tab = explode(";", $_POST['tab']);

        $notif = array();
        $notif['draf'] = 0;
        $notif['proses'] = 0;
        $notif['ditolak'] = 0;
        $notif['disetujui'] = 0;

        $notif['draf_ply'] = 0;
        $notif['proses_ply'] = 0;
        $notif['ditolak_ply'] = 0;
        $notif['disetujui_ply'] = 0;

        $notif['tertunda'] = 0;
        $notif['ditolak_ver'] = 0;
        $notif['disetujui_ver'] = 0;

        $where = " (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND ";
        $query = "SELECT count(pj.CPM_ID) as total
                    FROM {$this->PATDA_MINERAL_DOC} pj INNER JOIN {$this->PATDA_MINERAL_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_MINERAL_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_MINERAL_ID                            
                    WHERE ";

        if (in_array("draf", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['draf'] = (int) $data['total'];
        }
        if (in_array("proses", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['proses'] = (int) $data['total'];
        }
        if (in_array("ditolak", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['ditolak'] = (int) $data['total'];
        }
        if (in_array("disetujui", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['disetujui'] = (int) $data['total'];
        }

        if (in_array("draf_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['draf_ply'] = (int) $data['total'];
        }
        if (in_array("proses_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['proses_ply'] = (int) $data['total'];
        }
        if (in_array("ditolak_ply", $arr_tab) || in_array("ditolak_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result)) {
                $notif['ditolak_ply'] = (int) $data['total'];
                $notif['ditolak_ver'] = (int) $data['total'];
            }
        }
        if (in_array("disetujui_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['disetujui_ply'] = (int) $data['total'];
        }

        if (in_array("tertunda", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['tertunda'] = $data['total'];
        }
        if (in_array("disetujui_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysql_query($q, $this->Conn);
            if ($data = mysql_fetch_assoc($result))
                $notif['disetujui_ver'] = (int) $data['total'];
        }
        echo $this->Json->encode($notif);
    }

    public function get_harga()
    {
        $id = $_POST['id'];
        $tipe_pajak = $_POST['tipe_pajak'];

        $query = "SELECT tarif2 FROM PATDA_REK_PERMEN13 WHERE kdrek='{$id}'";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);
        $respon = array();
        $respon['harga'] = $data['tarif2'];
        echo $this->Json->encode($respon);
    }

    public function get_tarif_json()
    {
        $id = $_POST['id'];

        $tarif = $this->get_tarif($this->id_pajak);
        $respon['tarif'] = ($id == 1) ? $tarif['CPM_TARIF_PAJAK'] : 100;
        echo $this->Json->encode($respon);
    }
}
