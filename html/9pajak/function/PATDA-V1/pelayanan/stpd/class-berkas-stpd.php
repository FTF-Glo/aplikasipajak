<?php

class BerkasSTPD extends Pajak {

    function __construct() {
        parent::__construct();

        $BERKAS = isset($_POST['BERKAS']) ? $_POST['BERKAS'] : array();

        foreach ($BERKAS as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }

        $this->CPM_LAMPIRAN = isset($_POST['CPM_LAMPIRAN']) ? $_POST['CPM_LAMPIRAN'] : array();
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_berkas() {
        #inisialisasi data kosong
        $data = array("CPM_ID" => "", "CPM_TGL_INPUT" => date("d-m-Y"), "CPM_JENIS_PAJAK" => "",
            "CPM_NO_SPTPD" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
            "CPM_AUTHOR" => "", "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_LAMPIRAN" => "", "CPM_STATUS" => "",
        );

        #query untuk mengambil data berkas
        $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_ID = '{$this->_id}' AND CPM_STPD='1'";
        $result = mysqli_query($this->Conn, $query);
        #jika ada data 
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
        } else {
            $data['CPM_AUTHOR'] = $this->Data->uname;
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
            $result = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        } elseif ($this->CPM_JENIS_PAJAK == 5) {
            $query = "SELECT pr.* FROM PATDA_PARKIR_DOC pjk INNER JOIN PATDA_PARKIR_DOC_TRANMAIN tr ON
                    pjk.CPM_ID = tr.CPM_TRAN_PARKIR_ID INNER JOIN PATDA_PARKIR_PROFIL pr ON
                    pjk.CPM_ID_PROFIL = pr.CPM_ID
                    WHERE pjk.CPM_NO = '{$this->CPM_NO_SPTPD}' 
                    AND tr.CPM_TRAN_STATUS = '2' AND CPM_TRAN_FLAG ='0'";
            $result = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
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
            $result = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $data['result'] = 1;
            }
            echo $this->Json->encode($data);
        }
    }

    public function save() {
        $this->CPM_ID = c_uuid();
        $this->CPM_STATUS = 1; #count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
        $this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

        $query = "SELECT * FROM PATDA_BERKAS WHERE CPM_NO_SPTPD ='{$this->CPM_NO_SPTPD}' AND CPM_STPD='1'";
        $result = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($result) == 0) {
            $query = sprintf("INSERT INTO PATDA_BERKAS 
                    (CPM_ID,CPM_TGL_INPUT,CPM_JENIS_PAJAK,CPM_NO_SPTPD,CPM_NPWPD,
                    CPM_NAMA_WP,CPM_ALAMAT_WP,CPM_NAMA_OP,CPM_ALAMAT_OP,CPM_LAMPIRAN,
                    CPM_AUTHOR,CPM_STATUS, CPM_STPD)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','1')", $this->CPM_ID, $this->CPM_TGL_INPUT, $this->CPM_JENIS_PAJAK, $this->CPM_NO_SPTPD, $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_LAMPIRAN, $this->CPM_AUTHOR, $this->CPM_STATUS
            );
            if($res = mysqli_query($this->Conn, $query)){
				$_SESSION['_success'] = "Berkas berhasil disimpan";
			}else{
				$_SESSION['_error'] = "Berkas gagal disimpan";
			}
        } else {
			$msg = "Gagal disimpan, Berkas untuk No. SPTPD <b>{$this->CPM_NO_SPTPD}</b> sudah diinput sebelumnya!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
        }
    }

    public function update() {
        $this->CPM_STATUS = 1; # count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
        $this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

        $query = sprintf("UPDATE PATDA_BERKAS SET                    
                    CPM_LAMPIRAN = '%s',
                    CPM_AUTHOR = '%s',
                    CPM_STATUS = '%s'
                    WHERE CPM_ID = '{$this->CPM_ID}'", $this->CPM_LAMPIRAN, $this->CPM_AUTHOR, $this->CPM_STATUS
        );
        if($res = mysqli_query($this->Conn, $query)){
			$_SESSION['_success'] = "Berkas berhasil diupdate";
		}else{
			$_SESSION['_error'] = "Berkas gagal diupdate";
		}
    }

    public function filtering($id) {
        $opt_jenis_pajak = '<option value="">All</option>';
        foreach ($this->arr_pajak as $x => $y) {
            $opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
        }

        $opt_tahun = '<option value="">All</option>';
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        }

        $opt_bulan = '<option value="">All</option>';
        foreach ($this->arr_bulan as $x => $y) {
            $opt_bulan  .= "<option value=\"{$x}\">{$y}</option>";
        }


        $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td><input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" s=\"{$this->_s}\">
                        Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" class=\"form-control\" style=\"height: 32px; width: 96\">{$opt_jenis_pajak}</select></td>
                        <td>Tahun Pajak :<br><select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\" class=\"form-control\" style=\"height: 32px; width: 96\">{$opt_tahun}</select></td>
                        <td>Masa Pajak :<br><select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\"  class=\"form-control\" style=\"height: 32px; width: 96\">{$opt_bulan}</select> </td>
                        <td>No. STPD :<br><input type=\"text\" name=\"CPM_NO_STPD-{$id}\" id=\"CPM_NO_STPD-{$id}\" class=\"form-control\" style=\"height: 32px; width: 120px\" ></td>
                        <td>NPWPD :<br><input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" class=\"form-control\" style=\"height: 32px; width: 120px; display: inline-block;\" > </td>
                        <td>Tanggal Lapor :<br><input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" class=\"date form-control\" style=\"height: 32px; width: 96px; display: inline-block;\" readonly size=\"10\" ><button type=\"button\" value=\"x\" class=\"btn btn-danger\" style=\"padding: 4px 8px\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\">x</button> s.d <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" class=\"date form-control\" style=\"height: 32px; width: 96px; display: inline-block;\" size=\"10\" ><button type=\"button\" value=\"x\" class=\"btn btn-danger\" style=\"padding: 4px 8px\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">x</button></td>
                        <td>
                            <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important; margin-left: 10px\"><i class=\"fa fa-search\"></i> Cari</button>
                            <button type=\"button\" class=\"btn btn-success lm-btn\" style=\"font-size: 0.7rem !important;\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-berkas.xls.php','stpd')\"><i class=\"fa fa-download\"></i> Export to xls</button>
                        </td>    
                        </tr></table></form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "pelayanan/stpd";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent}.filtering input,.filtering select{height:23px}</style>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
						$('.date').datepicker({
							dateFormat: 'yy-mm-dd',
							changeYear: true,
							changeMonth: true
						});
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_TGL_INPUT DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},                                 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_NO_STPD: {title: 'No. STPD',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '5%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                " . ($this->_i == 3 ? "kode_verifikasi: {title: 'Kode Verifikasi',width: '10%'}," : "") . "
                                " . ($this->_i == 1 ? "" : "CPM_AUTHOR: {title: 'Petugas',width: '10%'},") . "
                                CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'}, 
                                " . ($this->_i == 3 ? "" : "CPM_STATUS: {title: 'Status',width: '10%'}") . "
                            },
                            recordsLoaded: function (event, data) {
                                for (var i in data.records) {                                    
                                    console.log(data.records[i])
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
                                CPM_NO_STPD : $('#CPM_NO_STPD-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),    
                                CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK: $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data() {
        try {
            if ($this->_i == 1) {
                $this->grid_data_berkas_masuk();
            } elseif ($this->_i == 2) {
                $this->grid_data_berkas_diterima();
            } elseif ($this->_i == 3) {
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

    private function grid_data_berkas_masuk() {
        $where = "CPM_STATUS='0' AND CPM_STPD='1' ";

		$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data
        $query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_STPD, CPM_VERSION, 
                    CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK, 
                    CPM_TAHUN_PAJAK, CPM_NPWPD, 
                    CPM_AUTHOR, DATE(CPM_TGL_INPUT) as CPM_TGL_INPUT, CPM_STATUS, CPM_TRAN_READ
                    FROM {$this->PATDA_BERKAS} WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

            $base64 = "a={$this->_a}&m={$this->_m}&f={$this->_f}&id={$row['CPM_ID']}&sts={$row['CPM_STATUS']}&read={$row['READ']}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NO_STPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_STPD']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_STATUS'] = ($row['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap";
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

    private function grid_data_berkas_diterima() {
        $where = "CPM_STATUS='1' AND CPM_STPD='1' ";

        $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data
        $query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_STPD, CPM_VERSION, 
                    CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK, 
                    CPM_TAHUN_PAJAK, CPM_NPWPD, 
                    CPM_AUTHOR, DATE(CPM_TGL_INPUT) as CPM_TGL_INPUT, CPM_STATUS, CPM_TRAN_READ
                    FROM {$this->PATDA_BERKAS} WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));

            $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

            $base64 = "a={$this->_a}&m={$this->_m}&f={$this->_f}&id={$row['CPM_ID']}&sts={$row['CPM_STATUS']}&read={$row['READ']}";
            $url = "main.php?param=" . base64_encode($base64);

            $row['CPM_NO_STPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_STPD']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_STATUS'] = ($row['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap";
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

    private function grid_data_pajak_disetujui() {
        $where = "tr.CPM_TRAN_STATUS='5'";

        $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
        $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
        $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                    STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_STPD} s INNER JOIN {$this->PATDA_STPD_TRANMAIN} tr ON
                  s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where}";

        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data        
        $query = "SELECT s.CPM_ID,s.CPM_ID_PROFIL, s.CPM_JENIS_PAJAK, s.CPM_NO_STPD, s.CPM_VERSION, s.CPM_MASA_PAJAK, s.CPM_TAHUN_PAJAK,
                    s.CPM_NPWPD, s.CPM_AUTHOR, STR_TO_DATE(s.CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_READ,
                    tr.CPM_TRAN_ID, tr.CPM_TRAN_STATUS
                    FROM {$this->PATDA_STPD} s INNER JOIN {$this->PATDA_STPD_TRANMAIN} tr ON
                    s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        $rows = array();
        $no_stpd = array();
        $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
        while ($row = mysqli_fetch_assoc($result)) {
            $row = array_merge($row, array("NO" => ++$no));
            $no_stpd[$row['CPM_NO_STPD']] = $row['CPM_NO_STPD'];

            $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

            $base64 = "a={$this->_a}&m={$this->_m}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}&i={$this->_i}&f={$this->_f}&type={$row['CPM_JENIS_PAJAK']}&id={$row['CPM_ID']}&idp={$row['CPM_ID_PROFIL']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
            $url = "main.php?param=" . base64_encode($base64);
            $row['CPM_NO_STPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_STPD']}</a>";
            $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
            $row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
            $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
            $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
            $rows[] = $row;
        }
        
        $config = $this->get_config_value($this->_a);

		$dbName = $config['PATDA_DBNAME'];
        $dbHost = $config['PATDA_HOSTPORT'];
        $dbPwd = $config['PATDA_PASSWORD'];
        $dbTable = $config['PATDA_TABLE'];
        $dbUser = $config['PATDA_USERNAME'];
        $day = $config['TENGGAT_WAKTU'];
        $area_code = $config['KODE_AREA'];
    
		// koneksi ke gw untuk dapatkan kode bayar (payment_code)
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);
		
		$res = mysqli_query($Conn_gw, "SELECT sptpd,payment_code from SIMPATDA_GW WHERE sptpd IN('".implode("','", $no_stpd)."')");
		while($gw=mysqli_fetch_assoc($res)){
			$no_stpd[$gw['sptpd']] = $gw['payment_code'];
		}

		// masukkan kode bayar by no sptpd ke data
		foreach($rows as $i=>$row){
			$rows[$i]['kode_verifikasi'] = $no_stpd[strip_tags($row['CPM_NO_STPD'])];
		}

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['q'] = $query;
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print $this->Json->encode($jTableResult);

        mysqli_close($this->Conn);
        mysqli_close($Conn_gw);
    }

    public function print_buktiterima() {
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_berkas();

        $petugas = $this->get_petugas_identity();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];

        $html = "<table border=\"1\" cellpadding=\"5\">
                    <tr>
                        <!--LOGO-->
                        <td align=\"center\" width=\"28%\">

                        </td>
                        <!--COP-->
                        <td align=\"center\" width=\"72%\" colspan=\"2\">
                            <br>
                            PEMERINTAH KOTA " . strtoupper($KOTA) . "<br/>BADAN PENGELOLAAN KEUANGAN DAERAH<br/><br/><br/>
                            <font class=\"normal\" size=\"-1\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
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
                                    <td width=\"180\">{$DATA['CPM_NO_STPD']}</td>
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
                                    <td>KOTA " . strtoupper($KOTA) . ", " . date("d-m-Y") . " 
                                        <br>
                                        <br>
                                        <br>
                                        <br>{$petugas['CPM_NAMA']}
                                        <hr style=\"width:120px\">NIP. {$petugas['CPM_NIP']} </td>
                                </tr>
                            </table>
                            </font>				
                        </td>
                    </tr>
                </table>";

        require_once("../../../../inc/payment/tcpdf/tcpdf.php");
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
        $resolution= array(105, 150);
        $pdf->AddPage('P', $resolution);
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 6, 7, 20, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('bukti_penerimaan.pdf', 'I');
    }

    public function print_disposisi() {
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_berkas();

        $radio_lampiran[1] = strpos($DATA['CPM_LAMPIRAN'], "1") === false ? "[_]" : "[x]";
        $radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "[_]" : "[x]";

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
		
		$VERIFIKASI_NIP = $config['BAG_VERIFIKASI_NIP'];
		$VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
		$VERIFIKASI_NAMA = str_pad($VERIFIKASI_NAMA, 40,".",STR_PAD_RIGHT);
		$VERIFIKASI_NAMA = str_replace(".","&nbsp;",$VERIFIKASI_NAMA);
        $html = "<table border=\"1\" cellpadding=\"10\">
                    <tr>
                        <td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
                        <td align=\"center\" width=\"60%\">
                            <!-- <font size=\"+4\"> --> PEMERINTAH KOTA " . strtoupper($KOTA) . "<br/>BADAN PENGELOLAAN KEUANGAN DAERAH<br />
                            <!-- </font> -->
                        </td>
                        <!--KOSONG-->
                        <td rowspan=\"2\" align=\"center\" width=\"20%\">
                        </td>
                    </tr>
                    <tr>
                        <td align=\"center\">
                            <font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"2\" cellspacing=\"7\">
                                <tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">DISPOSISI PAJAK " . strtoupper($this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]) . "<br /></font></td></tr>
                                <tr>
                                    <td>Nomor</td><td width=\"20\">:</td>
                                    <td>{$DATA['CPM_NO_STPD']}</td>
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
                                            <tr><td>{$radio_lampiran[1]} STPD</td></tr>                                            
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
                                                    <!--<table border=\"0\" cellspacing=\"3\" width=\"250\">
                                                    <tr><td align=\"center\">Petugas Verifikasi</td></tr>
                                                    <tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr> 
                                                    <tr><td><br><br><br><br></td></tr>
                                                    <tr><td align=\"left\">___________________________________<br />NIP : </td></tr>
                                                    </table>-->
                                            </td>
                                            <td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <!--<table border=\"0\" cellspacing=\"3\" width=\"250\">
                                                    <tr><td align=\"center\">Petugas Persetujuan</td></tr>
                                                    <tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
                                                    <tr><td><br><br><br><br></td></tr>
                                                    <tr><td>___________________________________<br />NIP : $VERIFIKASI_NIP</td></tr>
                                                    </table>-->
                                                    <table border=\"0\" cellspacing=\"3\" width=\"250\">
                                                    <tr><td align=\"center\">Petugas Verifikasi</td></tr>
                                                    <tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr> 
                                                    <tr><td><br><br><br><br></td></tr>
                                                    <tr><td align=\"left\"><u>{$VERIFIKASI_NAMA}</u><br />NIP : {$VERIFIKASI_NIP}</td></tr>
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

        require_once("../../../../inc/payment/tcpdf/tcpdf.php");
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
        $pdf->Image('../../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 13, 17, 24, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('disposisi.pdf', 'I');
    }

    public function read_dokumen() {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $select = "SELECT CPM_TRAN_READ FROM PATDA_BERKAS WHERE CPM_ID='{$this->_id}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE PATDA_BERKAS SET CPM_TRAN_READ = '{$read}' WHERE CPM_ID='{$this->_id}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function read_dokumen_notif() {
        $arr_tab = explode(";", $_POST['tab']);
        
        $notif = array();        
        $notif['masuk'] = 0;
        $notif['diterima'] = 0;
        $notif['disetujui'] = 0;

        if (in_array("masuk", $arr_tab)) {
            $q = "SELECT count(CPM_ID) as total FROM PATDA_BERKAS WHERE CPM_STATUS='0' AND CPM_STPD='1' AND 
                    (CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR CPM_TRAN_READ is null)";
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['masuk'] = (int) $data['total'];
        }        
        if (in_array("diterima", $arr_tab)) {
            $q = "SELECT count(CPM_ID) as total FROM PATDA_BERKAS WHERE CPM_STATUS='1' AND CPM_STPD='1' AND 
                    (CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR CPM_TRAN_READ is null)";
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['diterima'] = (int) $data['total'];
        }
        if (in_array("disetujui", $arr_tab)) {
            $where = "tr.CPM_TRAN_STATUS='5' AND (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null)";

            $query = "SELECT count(s.CPM_ID) as total FROM PATDA_STPD s INNER JOIN PATDA_STPD_TRANMAIN tr ON
                        s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where}";

            $result = mysqli_query($this->Conn, $query);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui'] = (int) $data['total'];
        }
        echo $this->Json->encode($notif);
    }

}

?>
