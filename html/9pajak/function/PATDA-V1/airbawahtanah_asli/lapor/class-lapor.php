<?php

/**
 *Modified :
 *1. Penambahan konfigurasi nama badan pengelola :
 *    - modified by : RDN
 *    - date : 2016/01/03
 *    - function : print_sptpd, print_sspd
 */
class LaporPajak extends Pajak
{
    #field
    #airbawahtanah
    private $CPM_REKENING;
    private $CPM_HARGA;
    private $CPM_LOKASI_SUMBER_AIR;
    private $CPM_KUALITAS_AIR;
    private $CPM_TINGKAT_KERUSAKAN;
    public $id_pajak = 1;

    function __construct()
    {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();

        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }

        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_pajak($npwpd = '', $nop = '')
    {
        $Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.12");
        $list_nop = array();
        $pajak_atr = array();

        $query = "
            SELECT DOC.*, DOC.CPM_TGL_JATUH_TEMPO as CPM_TGL_JATUH_TEMPO
            FROM PATDA_AIRBAWAHTANAH_DOC AS DOC WHERE DOC.CPM_ID = '{$this->_id}' LIMIT 0,1";





        $result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);

        //if new entry
        if (empty($pajak['CPM_ID'])) {
            $ms = $this->inisialisasi_masa_pajak();

            $pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
            $pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
            $pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
            $pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
            $pajak['CPM_HARGA'] = 0;

            $profil = $Op->get_last_profil($npwpd, $nop);

            $atr = array(
                'CPM_ATR_BULAN' => $ms['masa_pajak'] + 0,
                'CPM_ATR_VOLUME' => '',
                'CPM_ATR_PERHITUNGAN' => '',
                'CPM_ATR_TOTAL' => '',
            );
            $pajak_atr[] = $atr;
            $pajak_atr[] = $atr;
            $pajak_atr[] = $atr;

            $tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
            $list_nop = $Op->get_list_nop($npwpd);
        } else { //if data available
            $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);

            $query_atr = "SELECT CPM_ATR_BULAN,CPM_ATR_VOLUME,CPM_ATR_PERHITUNGAN,CPM_ATR_TOTAL FROM PATDA_AIRBAWAHTANAH_DOC_ATR WHERE CPM_ATR_AIRBAWAHTANAH_ID='{$pajak['CPM_ID']}' ORDER BY CPM_ATR_BULAN ASC";

            // var_dump($query_atr);exit;
            $res = mysqli_query($this->Conn, $query_atr);
            while ($row = mysqli_fetch_assoc($res)) {
                $pajak_atr[] = $row;
            }
            /* if(count($pajak_atr<3)){
                for($i=0;$i<3;$i++){
                    if(!isset($pajak_atr[$i])) $pajak_atr[] = array(
                        'CPM_ATR_BULAN'=>'',
                        'CPM_ATR_VOLUME'=>'',
                        'CPM_ATR_PERHITUNGAN'=>'',
                        'CPM_ATR_TOTAL'=>'',
                    );
                }
            } */
            $tarif = $pajak['CPM_TARIF_PAJAK'];
        }

        $pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
        $pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;

        $query = "SELECT * FROM PATDA_AIRBAWAHTANAH_INDEX";
        $result = mysqli_query($this->Conn, $query);
        $index = array();
        while ($d = mysqli_fetch_assoc($result)) {
            $index[$d['CPM_FAKTOR']][$d['CPM_INDEX']]['URAIAN'] = "{$d['CPM_URAIAN']} ({$d['CPM_INDEX']})";
            $index[$d['CPM_FAKTOR']][$d['CPM_INDEX']]['INDEX'] = $d['CPM_INDEX'];
        }
        $pajak = array_merge($pajak, $arr_rekening);

        return array(
            'pajak' => $pajak,
            'pajak_atr' => $pajak_atr,
            'tarif' => $tarif,
            'profil' => $profil,
            'list_nop' => $list_nop,
            'index' => $index
        );
    }

    public function filtering($id)
    {
        $opt_tahun = '<option value="">All</option>';
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        }

        $opt_bulan = '<option value="">All</option>';
        foreach ($this->arr_bulan as $x => $y) {
            $opt_bulan .= "<option value='{$x}'>{$y}</option>";
        }

        $kec = $this->get_list_kecamatan();
        $opt_kecamatan = "<option value=\"\">All</option>";
        foreach ($kec as $k => $v) {
            $opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
        }
        $opt_triwulan = '<option value="">All</option>';
        foreach ($this->arr_triwulan as $x => $y) {
            $opt_triwulan .= "<option value='{$x}'>{$y}</option>";
        }

        $opt_pilih = "<option value=\"\">All</option>";
        foreach ($this->arr_jenis as $k => $v) {
            $opt_pilih .= "<option value=\"{$k}\">{$v}</option>";
        }

        $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td><input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                        NPWPD :<br><input style=\"width:100px; height:30px;\" class=\"form-control\" type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  </td>
                        <td>Nama/No Laporan  :<br><input style=\"width:130px; height:30px;\" class=\"form-control\" type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >  </td>
                        <td>Tahun Pajak :<br><select style=\"width:80px; height:30px;\" class=\"form-control\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select></td>
                        <td>Masa Pajak :<br><select style=\"width:80px; height:30px;\" class=\"form-control\" name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select></td>
                        <td>Kecamatan :<br><select style=\"width:80px; height:30px;\" class=\"form-control\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
                        <td>Kelurahan :<br><select style=\"width:80px; height:30px;\" class=\"form-control\" name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select></td>
                        <td>Tanggal Lapor :<br>
						<input style=\"width:100px;  height:30px;\" type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly size=\"10\" class=\"date\" >
						<button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\">x</button> s.d 
						<input style=\"width:100px; height:30px;\" type=\"text\"  name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" size=\"10\" class=\"date\" >
						<button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">x</button>
						</td>";
                        if ($this->_i == 4) { 
							$html .= "<td>Total Pajak :<br><input type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\"> </td>";
						}
                        $html .= " <td>Pilih Jenis :<br><select style=\"width:80px; height:30px;\" class=\"form-control\" name=\"CPM_PIUTANG-{$id}\" id=\"CPM_PIUTANG-{$id}\">{$opt_pilih}</select></td>
                       
                        <td bgcolor=\"#ffff00\">
                            <button type=\"submit\"  style=\"width:50px; height:30px;\" class=\"btn btn-sm btn-secondary\" id=\"cari-{$id}\">Cari</button>
                            <button type=\"button\"  style=\"width:90px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>
							<!--<button type=\"button\"  style=\"width:150px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-bentang-panjang.xls.php')\">Cetak Bentang Panjang</button>-->            
                            <button type=\"button\"  style=\"width:150px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/cetakpanjang/svc-download-bentang-panjang-atb.xls.php')\">Cetak Bentang Panjang</button>     
                            </td>
                    </tr></table></form>
                </div> ";
        return $html;
    }

    public function grid_table()
    {
        $DIR = "PATDA-V1";
        $modul = "airbawahtanah";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent!important}.filtering input,.filtering select{height:23px}</style>
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
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
								CPM_TRIWULAN : $('#CPM_TRIWULAN-{$this->_i}').val()
								
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        $('#CPM_KECAMATAN-{$this->_i}').change(function(){
                            if($(this).val()==''){
                                $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>');
                                return false;
                            }
                            $.ajax({
                                type: \"POST\",
                                url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
                                data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
                                cache:false,
                                beforeSend:function(){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>Loading...</option>');
                                },
                                success: function(html){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>'+html);
                                }
                            });
                        });
                    });
                </script>";
        echo $html;
        // var_dump($html);exit;
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

            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"{$_REQUEST['CPM_NAMA_WP']}%\") " : "";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            $where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";


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

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj
                        INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                        WHERE {$where}";

            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_PIUTANG
                        FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);
            // var_dump($query);
            // die;
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = 1;
                if ($this->_s != 0) { #untuk menandai dibaca atau belum
                    $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;
                }

                $func = $this->_f;
                if ($row['CPM_PIUTANG'] == 1) {
                    $func = 'fPatdaLaporPiutang1';
                }

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";

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

            mysqli_close($this->Conn);
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
        $modul = "airbawahtanah";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent!important}.filtering input,.filtering select{height:23px}</style>
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
                            defaultSorting: 'CPM_TGL_LAPOR DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/pelayanan/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},								
								CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%', type: 'datetime', displayFormat: 'dd-mm-yy HH:MM:SS'}, 
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'}, 
                                CPM_TRAN_DATE: {title: 'Tgl Verifikasi',width: '10%'},
                                CPM_NAMA_WP: {title: 'Wajib Pajak',width: '10%'},
                                CPM_NAMA_OP: {title: 'Objek Pajak',width: '10%'},
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
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
								CPM_TRIWULAN : $('#CPM_TRIWULAN-{$this->_i}').val(),
                                CPM_PIUTANG : $('#CPM_PIUTANG-{$this->_i}').val(),
                                TOTAL_PAJAK : $('#TOTAL_PAJAK-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        $('#CPM_KECAMATAN-{$this->_i}').change(function(){
                            if($(this).val()==''){
                                $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>');
                                return false;
                            }
                            $.ajax({
                                type: \"POST\",
                                url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
                                data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
                                cache:false,
                                beforeSend:function(){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>Loading...</option>');
                                },
                                success: function(html){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>'+html);
                                }
                            });
                        });
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
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\"" : "";
            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"{$_REQUEST['CPM_NAMA_WP']}%\") " : "";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            //$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            $where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";
            // echo $where."<br><br><br>";
            $where .= (isset($_REQUEST['CPM_PIUTANG']) && $_REQUEST['CPM_PIUTANG'] != "") ? " AND CPM_PIUTANG='{$_REQUEST['CPM_PIUTANG']}' " : "";
            // var_dump($_REQUEST);
            // die;
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


            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj
                            INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                            WHERE {$where}";
            // echo $query; exit;
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP,pr.CPM_NAMA_OP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_TGL_INPUT, tr.CPM_TRAN_DATE, pj.CPM_PIUTANG
                            FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                            WHERE {$where}
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);
            // var_dump($query);
            // die;
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

                $func = $this->_f;
                if ($row['CPM_PIUTANG'] == 1) {
                    $func = 'fPatdaLaporPiutang1';
                }

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);

                if ($row['CPM_TRAN_STATUS'] != '5') {
                    $row['CPM_TRAN_DATE'] = '-';
                }

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

            mysqli_close($this->Conn);
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
        $query = "SELECT * FROM {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} WHERE CPM_TRAN_AIRBAWAHTANAH_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        return $data['CPM_TRAN_AIRBAWAHTANAH_VERSION'];
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
                FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj
                INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                WHERE
                (
                pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND
                pr.CPM_NOP = '{$this->CPM_NOP}' AND
                pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND
                pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
                ORDER BY tr.CPM_TRAN_STATUS DESC, pj.CPM_VERSION DESC  LIMIT 0,1";
        #echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        if ($this->notif == true) {
            if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_TIPE_PAJAK == 1) {
                $this->Message->setMessage("Gagal disimpan, Pajak untuk tahun <b>{$this->CPM_TAHUN_PAJAK}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dilaporkan sebelumnya!");
            } elseif ($this->CPM_NO == $data['CPM_NO']) {
                $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
            }
        }

        $respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
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
            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_PROFIL} SET CPM_APPROVE ='1', CPM_KECAMATAN_WP = '{$this->CPM_KECAMATAN_WP}', CPM_KELURAHAN_WP = '{$this->CPM_KELURAHAN_WP}' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            mysqli_query($this->Conn, $query);

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
            // $this->CPM_VOLUME_AIR = str_replace(",", "", $this->CPM_VOLUME_AIR);
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);
            $this->CPM_HARGA = str_replace(",", "", $this->CPM_HARGA);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);

            #$this->CPM_NO_SSPD = substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
            $this->CPM_NO_SSPD = $this->CPM_NO;

            $this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

            $query = sprintf(
                "INSERT INTO {$this->PATDA_AIRBAWAHTANAH_DOC}
                    (CPM_ID,CPM_ID_PROFIL,CPM_NO,
                    CPM_MASA_PAJAK,CPM_TAHUN_PAJAK,
                    CPM_PERUNTUKAN,CPM_TYPE_MASA,
                    CPM_TOTAL_OMZET,CPM_TOTAL_PAJAK,CPM_TARIF_PAJAK,
                    CPM_KETERANGAN,CPM_TGL_JATUH_TEMPO,
                    CPM_VERSION,CPM_AUTHOR,CPM_ID_TARIF,
                    CPM_BAYAR_LAINNYA,CPM_DPP,CPM_BAYAR_TERUTANG,
                    CPM_NO_SSPD,CPM_HARGA,
                    CPM_MASA_PAJAK1,CPM_MASA_PAJAK2,CPM_TIPE_PAJAK,
                    CPM_DENDA_TERLAMBAT_LAP, CPM_PIUTANG)
                    VALUES ( '%s','%s','%s',
                             '%s','%s',
                             '%s','%s',
                             '%s','%s','%s',
                             '%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s',
                             '%s','%s','%s',
                             '%s', '%s')",
                $this->CPM_ID,
                $this->CPM_ID_PROFIL,
                $this->CPM_NO,
                $this->CPM_MASA_PAJAK,
                $this->CPM_TAHUN_PAJAK,
                $this->CPM_PERUNTUKAN,
                $this->CPM_TYPE_MASA,
                $this->CPM_TOTAL_OMZET,
                $this->CPM_TOTAL_PAJAK,
                $this->CPM_TARIF_PAJAK,
                $this->CPM_KETERANGAN,
                $this->CPM_TGL_JATUH_TEMPO,
                $this->CPM_VERSION,
                $this->CPM_AUTHOR,
                $this->CPM_ID_TARIF,
                doubleval($this->CPM_BAYAR_LAINNYA),
                $this->CPM_DPP,
                $this->CPM_BAYAR_TERUTANG,
                $this->CPM_NO_SSPD,
                doubleval($this->CPM_HARGA),
                $this->CPM_MASA_PAJAK1,
                $this->CPM_MASA_PAJAK2,
                $this->CPM_TIPE_PAJAK,
                $this->CPM_DENDA_TERLAMBAT_LAP,
                $this->CPM_PIUTANG
            );

            $save = mysqli_query($this->Conn, $query) or die(var_dump($this->CPM_BAYAR_LAINNYA));

            $ok = 0;
            if ($save && $this->CPM_PIUTANG == 0) {
                foreach ($_POST['PAJAK_ATR']['CPM_ATR_VOLUME'] as $x => $vol) {
                    $CPM_ATR_VOLUME = str_replace(',', '', $vol);
                    $CPM_ATR_BULAN = $_POST['PAJAK_ATR']['CPM_ATR_BULAN'][$x] + 0;
                    $CPM_ATR_PERHITUNGAN = $_POST['PAJAK_ATR']['CPM_ATR_PERHITUNGAN'][$x];
                    $CPM_ATR_TOTAL = $_POST['PAJAK_ATR']['CPM_ATR_TOTAL'][$x];
                    if ($CPM_ATR_BULAN != "" && $CPM_ATR_VOLUME != "" && $CPM_ATR_PERHITUNGAN != "" && $CPM_ATR_TOTAL != "") {
                        $query_atr = sprintf(
                            "INSERT INTO PATDA_AIRBAWAHTANAH_DOC_ATR
                                (CPM_ATR_AIRBAWAHTANAH_ID, CPM_ATR_BULAN,
                                CPM_ATR_VOLUME, CPM_ATR_PERHITUNGAN, CPM_ATR_TOTAL)
                                VALUES( '%s', '%s',
                                        '%s', '%s', '%s')",
                            $this->CPM_ID,
                            $CPM_ATR_BULAN,
                            $CPM_ATR_VOLUME,
                            $CPM_ATR_PERHITUNGAN,
                            $CPM_ATR_TOTAL
                        );
                        // echo $query_atr,"\n";
                        $save_atr = mysqli_query($this->Conn, $query_atr) or die(mysqli_error($this->Conn));
                        if ($save_atr) $ok++;
                    }
                }
            }

            if ($this->CPM_PIUTANG) {
                $ok = 1;
            }

            //mysqli_close($this->Conn);
            return $ok > 0;
        }
        return false;
    }

    private function save_tranmain($param)
    {
        #insert tranmain
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_AIRBAWAHTANAH_ID = $this->CPM_ID;

        $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_AIRBAWAHTANAH_ID = '{$CPM_TRAN_AIRBAWAHTANAH_ID}'";


        $res = mysqli_query($this->Conn, $query);



        //die(var_dump($param));

        $tranInfo = isset($param['CPM_TRAN_INFO']) ? $param['CPM_TRAN_INFO'] : '';

        $query = sprintf("INSERT INTO {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN}
                    (CPM_TRAN_ID, CPM_TRAN_AIRBAWAHTANAH_ID, CPM_TRAN_AIRBAWAHTANAH_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE,
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')", $CPM_TRAN_ID, $CPM_TRAN_AIRBAWAHTANAH_ID, $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'], $param['CPM_TRAN_STATUS'], $param['CPM_TRAN_FLAG'], $param['CPM_TRAN_DATE'], $param['CPM_TRAN_OPR'], $param['CPM_TRAN_OPR_DISPENDA'], $tranInfo);
        //echo $query;
        return mysqli_query($this->Conn, $query);
    }


    private function update_tgl_input()
    {
        $tgl_input = date("Y-m-d h:i:s");
        $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

        return mysqli_query($this->Conn, $query);
    }

    private function update_tgl_lapor()
    {
        $tgl_input = date("d-m-Y");
        $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_LAPOR = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

        return mysqli_query($this->Conn, $query);
    }

    private function update_tgl_lapor_ditolak($cpm_no, $tgl_lapor, $tgl_input)
    {
        $tgl_input = $tgl_input != '' ? $tgl_input : 'NULL';

        if ($tgl_input == 'NULL') {
            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}'
                  WHERE CPM_NO ='{$cpm_no}'";
        } else {
            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}', CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_NO ='{$cpm_no}'";
        }

        return mysqli_query($this->Conn, $query);
    }

    public function save()
    {
        if ($this->CPM_PIUTANG == 1) {
            if ($this->validasi_piutang() == false) {
                return false;
            }
        }

        $this->CPM_VERSION = "1";
        if ($this->save_pajak($this->CPM_NO)) {
            $param = array();
            $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";

            if ($this->update_tgl_input()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            if ($res = $this->save_tranmain($param)) {
                $_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal disimpan';
            }
        }
    }

    public function save_final()
    {
        // var_dump($this);die;
        if ($this->CPM_PIUTANG == 1) {
            if ($this->validasi_piutang() == false) {
                return false;
            }
        }

        $this->CPM_VERSION = "1";
        if ($this->save_pajak($this->CPM_NO)) {
            $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if ($res) {
                $_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal difinalkan';
            }
        }
    }

    public function new_version()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_AIRBAWAHTANAH_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";

            if ($this->update_tgl_lapor_ditolak($this->CPM_NO, $this->DITOLAK_TGL_LAPOR, $this->DITOLAK_TGL_INPUT)) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            if ($res = $this->save_tranmain($param)) {
                $_SESSION['_success'] = 'Data Pajak versi ' . $new_version . ' berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Data Pajak ' . $new_version . ' gagal disimpan';
            }
        }
    }

    public function new_version_final()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_AIRBAWAHTANAH_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor_ditolak($this->CPM_NO, $this->DITOLAK_TGL_LAPOR, $this->DITOLAK_TGL_INPUT)) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if ($res) {
                $_SESSION['_success'] = 'Data Pajak versi ' . $new_version . ' berhasil difinalkan';
            } else {
                $_SESSION['_error'] = 'Data Pajak ' . $new_version . ' gagal difinalkan';
            }
        }
    }

    public function update_final()
    {
        $this->CPM_VERSION = $this->last_version();
        if ($this->update()) {
            $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = $this->CPM_VERSION;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if ($res) {
                $_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal difinalkan';
            }
        }
    }

    public function update()
    {

        $validasi = $this->validasi_update();
        if ($validasi['result'] == true) {
            // $this->CPM_VOLUME_AIR = str_replace(",", "", $this->CPM_VOLUME_AIR);
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);
            $this->CPM_HARGA = str_replace(",", "", $this->CPM_HARGA);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);

            $this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

            $query = sprintf(
                "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET
                    CPM_PERUNTUKAN = '%s',
                    CPM_TYPE_MASA = '%s',
                    CPM_TOTAL_OMZET = '%s',
                    CPM_TOTAL_PAJAK = '%s',
                    CPM_TARIF_PAJAK = '%s',
                    CPM_BAYAR_LAINNYA = '%s',
                    CPM_DPP = '%s',
                    CPM_BAYAR_TERUTANG = '%s',
                    CPM_KETERANGAN = '%s',
                    CPM_HARGA = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_TIPE_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
					CPM_MASA_PAJAK = '%s',
                    CPM_DENDA_TERLAMBAT_LAP = '%s',
					CPM_PIUTANG = '%s'
                    WHERE
                    CPM_ID ='{$this->CPM_ID}'",
                $this->CPM_PERUNTUKAN,
                $this->CPM_TYPE_MASA,
                $this->CPM_TOTAL_OMZET,
                $this->CPM_TOTAL_PAJAK,
                $this->CPM_TARIF_PAJAK,
                $this->CPM_BAYAR_LAINNYA,
                $this->CPM_DPP,
                $this->CPM_BAYAR_TERUTANG,
                $this->CPM_KETERANGAN,
                $this->CPM_HARGA,
                $this->CPM_MASA_PAJAK1,
                $this->CPM_MASA_PAJAK2,
                $this->CPM_TIPE_PAJAK,
                $this->CPM_TAHUN_PAJAK,
                $this->CPM_MASA_PAJAK,
                $this->CPM_DENDA_TERLAMBAT_LAP,
                $this->CPM_PIUTANG
            );

            $save = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

            // delete ATR sebelum insert ulang
            mysqli_query($this->Conn, "DELETE FROM PATDA_AIRBAWAHTANAH_DOC_ATR WHERE CPM_ATR_AIRBAWAHTANAH_ID='{$this->CPM_ID}'");
            $ok = 0;
            foreach ($_POST['PAJAK_ATR']['CPM_ATR_VOLUME'] as $x => $vol) {
                $CPM_ATR_VOLUME = str_replace(',', '', $vol);
                $CPM_ATR_BULAN = $_POST['PAJAK_ATR']['CPM_ATR_BULAN'][$x] + 0;
                $CPM_ATR_PERHITUNGAN = $_POST['PAJAK_ATR']['CPM_ATR_PERHITUNGAN'][$x];
                $CPM_ATR_TOTAL = $_POST['PAJAK_ATR']['CPM_ATR_TOTAL'][$x];
                if ($CPM_ATR_BULAN != "" && $CPM_ATR_VOLUME != "" && $CPM_ATR_PERHITUNGAN != "" && $CPM_ATR_TOTAL != "") {
                    $query_atr = sprintf(
                        "INSERT INTO PATDA_AIRBAWAHTANAH_DOC_ATR
                            (CPM_ATR_AIRBAWAHTANAH_ID, CPM_ATR_BULAN,
                            CPM_ATR_VOLUME, CPM_ATR_PERHITUNGAN, CPM_ATR_TOTAL)
                            VALUES( '%s', '%s',
                                    '%s', '%s', '%s')",
                        $this->CPM_ID,
                        $CPM_ATR_BULAN,
                        $CPM_ATR_VOLUME,
                        $CPM_ATR_PERHITUNGAN,
                        $CPM_ATR_TOTAL
                    );
                    // echo $query_atr,"\n";
                    $save_atr = mysqli_query($this->Conn, $query_atr) or die(mysqli_error($this->Conn));
                    if ($save_atr) $ok++;
                }
            }
            return $save || $ok > 0;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->PATDA_AIRBAWAHTANAH_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
        if ($res) {
            $query = "DELETE FROM {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} WHERE CPM_TRAN_AIRBAWAHTANAH_ID ='{$this->CPM_ID}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function verifikasi()
    {	
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
            $res = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($res) == 0) {
                $msg = "Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
                return false;
            }
        }
		$this->persetujuan();
    }

    public function persetujuan()
    {	
        $new_operator = $_SESSION['uname'];

        $status = ($this->AUTHORITY == 1) ? 5 : 4;
        $param['CPM_TRAN_AIRBAWAHTANAH_VERSION'] = $this->CPM_VERSION;
        $param['CPM_TRAN_STATUS'] 				 = $status;
        $param['CPM_TRAN_FLAG'] 				 = "0";
        $param['CPM_TRAN_DATE'] 				 = date("d-m-Y");
        $param['CPM_TRAN_OPR'] 					 = "";
        $param['CPM_TRAN_OPR_DISPENDA'] 		 = $new_operator;
        $param['CPM_TRAN_INFO'] 				 = $this->CPM_TRAN_INFO;
        $param['CPM_TRAN_READ'] 				 = "";
		$expired 			 					 = explode('-',$_POST['PAJAK']['CPM_TGL_JATUH_TEMPO']);
		$tanggal = $expired[2];
		$tanggal .= '-'.$expired[1];
		$tanggal .= '-'.$expired[0];
        $res = $this->save_tranmain($param);
        if ($this->AUTHORITY == 1 && $res == true) {
            $arr_config = $this->get_config_value($this->_a);
            $res = $this->save_gateway($this->id_pajak, $arr_config);

            if ($res) {
                $this->update_jatuh_tempo($this->EXPIRED_DATE, $this->CPM_TGL_JATUH_TEMPO);
                $_SESSION['_success'] = 'Data Pajak berhasil disetujui';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal disetujui';
            }
        }
    }

    // private function update_jatuh_tempo($expired_date)
    // {
    //     $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_JATUH_TEMPO = '{$expired_date}'
    //               WHERE CPM_ID ='{$this->CPM_ID}'";
    //     return mysqli_query($this->Conn, $query);
    // }

    private function update_jatuh_tempo($expired_date, $tgl_jatuh_tempo = NULL)
    {
       
        
        if ($tgl_jatuh_tempo == NULL || $tgl_jatuh_tempo == '') {
            $expired_date = $expired_date;
         }else{
             $expired_date = "'" . $tgl_jatuh_tempo . "'";
         }
        $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
    }

    public function print_sptpd_base()
    {
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

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        //echo '<pre>'.print_r($DATA,true).'</pre>';exit;

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"700\" border=\"0\">
                                <tr>
                                    <th valign=\"top\" align=\"center\">
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        " . strtoupper($NAMA_PENGELOLA) . "<br /><br />
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
                                            PAJAK AIR BAWAH TANAH
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
                                    <td>&nbsp;&nbsp;&nbsp;Nama Air Bawah Tanah</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Air Bawah Tanah</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;NPWPD</td>
                                    <td>: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"left\"><strong>II. DIISI OLEH PENGUSAHA AIR BAWAH TANAH</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"1\" cellspacing=\"0\">
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;a. Golongan Air</td>
                                    <td align=\"left\" width=\"430\" colspan=\"2\"> {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;b. Harga per m<sup>3</sup></td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['harga'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;c. Volume Air yang diambil</td>
                                    <td align=\"right\" width=\"430\" colspan=\"2\"> " . number_format($DATA['pajak']['CPM_VOLUME_AIR'], 0) . " m<sup>3</sup></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;d. Pembayaran Pemakaian</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;e. Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;f. Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;g. Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;h. Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> 0</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;i. Sanksi Administrasi Telat Lapor ({$persen_terlambat_lap}%)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;j. Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;k. Data Pendukung</td>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 35, 15, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-airbawahtanah.pdf', 'I');
    }

    public function bulan($bln)
    {
        $bulan = $bln;
        switch ($bulan) {
            case 1:
                $bulan = "Januari";
                break;
            case 2:
                $bulan = "Februari";
                break;
            case 3:
                $bulan = "Maret";
                break;
            case 4:
                $bulan = "April";
                break;
            case 5:
                $bulan = "Mei";
                break;
            case 6:
                $bulan = "Juni";
                break;
            case 7:
                $bulan = "Juli";
                break;
            case 8:
                $bulan = "Agustus";
                break;
            case 9:
                $bulan = "September";
                break;
            case 10:
                $bulan = "Oktober";
                break;
            case 11:
                $bulan = "November";
                break;
            case 12:
                $bulan = "Desember";
                break;
        }
        return $bulan;
    }

    function tgl_indo_full($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Febuari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tahun
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tanggal

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }


    /*
    public function print_skpd($type="") {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak($this->CPM_ID);
        // $data = $DATA['pajak'];
        // $profil = $DATA['profil'];

        // $DATA = array_merge($data, $profil);
        $arr_rekening = $this->getRekening();

        // echo '<pre>',print_r($DATA),'</pre>';exit;

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];

        // $dbName = '9pajak_sw_patda';

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);

        // mysqli_select_db($Conn_gw, $dbName);

        $query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
        $res = mysqli_query($Conn_gw, $query);
        if($d = mysqli_fetch_assoc($res)){
            $DATA['CPM_TGL_JATUH_TEMPO'] = $d['expired_date'];
        }

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $ALAMAT_PROVINSI = $config['ALAMAT_PROVINSI'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $NIP = $config['BAG_VERIFIKASI_NIP'];
		
		$TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
		$tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);
		$PEJABAT = $this->get_pejabat();
        $PEJABAT_MENGETAHUI = $PEJABAT[$_POST['PAJAK']['CPM_PEJABAT_MENGETAHUI']];
		
        $pemerintah = explode(' ',$JENIS_PEMERINTAHAN);
        $pemerintah_label = strtoupper($pemerintah[0]);
        $pemerintah_jenis = strtoupper($pemerintah[1]);

        $KODE_REK = $DATA['profil']["CPM_REKENING"];

        // $NM_REK = $DATA['ARR_REKENING'][$KODE_REK]['nmrek'];
        $d = explode('/AIR/',$DATA['pajak']['CPM_NO']);
        $NO_URUT = $d[0].'<br/>/AIR/'.$d[1];

        $persen_sanksi = 0;
        if(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']+0)>0){
            //$persen_sanksi = round(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK']*100)/2,0)*2;
			            //tamabahan
            $tes3 =$DATA['pajak']['CPM_TOTAL_PAJAK']-$DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 =$DATA['pajak']['CPM_TOTAL_PAJAK']-$tes3;
            $persen_sanksi = round(($tes4/$tes3)*100);
        }

        $ms1 = (int)substr($DATA['pajak']['CPM_MASA_PAJAK1'], 3, 2);
        $ms2 = (int)substr($DATA['pajak']['CPM_MASA_PAJAK2'], 3, 2);
        $triwulan = $ms1+2==$ms2 ? 'TRIWULAN<br>' : '';
		
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

        $page1 = "<table width=\"710\" class=\"main\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"0\">
                            <tr>
                                    <td valign=\"top\" align=\"center\" colspan=\"3\">
                                        <table border=\"0\" width=\"310\">
                                            <tr>
                                                <td width=\"700\" align=\"center\">
                                                    <b><font size=\"13\">
                                                        ".strtoupper($JENIS_PEMERINTAHAN)." ".strtoupper($NAMA_PEMERINTAHAN)."
                                                    </font><br/>
                                                    <font size=\"15\">
                                                      ".strtoupper($NAMA_PENGELOLA)."<br/>
                                                    </font></b>
                                                    {$JALAN}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" align=\"center\">
                        <b>
                            SURAT KETETAPAN PAJAK DAERAH
                            <br/>
                            (SKPD)
                            <br/>
                        </b>
                            {$triwulan}
                            PAJAK AIR TANAH
                        </td>
                    </tr>
                    <tr>
                        <td><table cellpadding=\"3\">
                            <tr>
                                <td>Tahun Pajak : ".$DATA['pajak']['CPM_TAHUN_PAJAK']."</td>
                                <td align=\"right\">Bulan :
                        ";
                        $no=0;
                        $arr_bulan = $this->arr_bulan;
                        $blns = array_map(function($v) use ($arr_bulan){
                            return $arr_bulan[$v['CPM_ATR_BULAN']];

                        }, $DATA['pajak_atr']);

                        $months = array('Januari','Febuari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                        $begin = DateTime::createFromFormat('d/m/Y',$this->CPM_MASA_PAJAK1);
                        $end = DateTime::createFromFormat('d/m/Y',$this->CPM_MASA_PAJAK2);
                        $interval = DateInterval::createFromDateString('1 month');
                        $period = new DatePeriod($begin, $interval, $end);

                        $i=0;
                        foreach ($period as $key=>$dt) {
                            $i++;
                            $m = (int) $dt->format("m");
                            // $page1 .= implode(', ', $blns); // display bulan
                            if($i == 2 || $i == 4 || $i == 6){
                                $mon =  ", " . $months[$m-1] . ", ";
                            }else {
                                $mon = $months[$m-1];
                            }
                            $page1 .= $mon; // display bulan
                            // $page1 .= $months[$m-1] . ", "; // display bulan
                        }
                        // $page1 .= $months[$m-1];
                        $page1 .="
                                </td>
                            </tr>
                        </table>
                        </td>
                    </tr>
                    <tr>
                    <td width=\"710\"><table width=\"680\" border=\"0\" cellpadding=\"3\">
                    <tr>
                        <td width=\"20\">1.</td>
                        <td width=\"150\">NPWPD</td>
                        <td width=\"530\">: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Nama PKP</td>
                        <td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>Nama Objek Pajak</td>
                        <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                    </tr>
                    <tr>
                        <td>4.</td>
                        <td>Jenis Usaha</td>
                        <td>: {$DATA['pajak']['CPM_PERUNTUKAN']}</td>
                        </tr>
                    <tr>
                        <td>5.</td>
                        <td>Alamat</td>
                        <td>: ".$DATA['profil']['CPM_ALAMAT_OP']."</td>
                    </tr>
                </table>
                </td>
            </tr>

            <tr>
                <td width=\"710\"><table width=\"700\" border=\"0\" cellpadding=\"3\">
                        <tr>
                            <td width=\"20\">5.</td>
                            <td width=\"690\" colspan=\"2\">Perhitungan Pajak Air Tanah</td>
                        </tr>
            ";
            $pager = count($DATA['pajak_atr'])==3 ? ' (I + II + III)' : '';
            $arr=array('I','II','III');
            foreach ($DATA['pajak_atr'] as $no=>$atr) {
                $atr['CPM_ATR_PERHITUNGAN'] = str_replace('class=r', 'align="right"', $atr['CPM_ATR_PERHITUNGAN']);
                //tambahan
                // if($atr['CPM_ATR_PERHITUNGAN'] == true){
                if(true){
					$bulanss = isset($arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]:'';
                  //$bulanss =$arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
                  
                  if($arr[$no] === 'II'){
                      //$bulanss =$arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+1];
                      $bulanss = isset($arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+1]:'';
                  }
                  if($arr[$no] === 'III'){
                      //$bulanss = $arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+2];
                      $bulanss = isset($arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+2]:'';
                  }
                    $page1 .="<tr style=\"font-size:30px\">
                            <td width=\"20\"></td>
                            <td width=\"20\">".$arr[$no].". </td>
                            <td width=\"670\">Bulan ".$bulanss." Tahun {$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                        </tr>
                        <tr style=\"font-size:30px\">
                            <td colspan=\"2\" width=\"40\"></td>
                            <td width=\"670\">Volume Pemanfaatan Air Tanah = {$atr['CPM_ATR_VOLUME']} M<sup>3</sup></td>
                        </tr>
                        <tr style=\"font-size:30px\">
                            <td colspan=\"2\" width=\"40\"></td>
                            <td width=\"650\">{$atr['CPM_ATR_PERHITUNGAN']}</td>
                        </tr>
                        <tr style=\"font-size:30px\">
                            <td width=\"40\"></td>
                            <td width=\"470\">Jumlah Pokok Pajak</td>
                            <td width=\"60\" align=\"right\">= Rp.</td>
                            <td width=\"123\" align=\"right\" style=\"border-top:1px solid #000000\">".number_format($atr['CPM_ATR_TOTAL'],2)."</td>
                        </tr>";
                    if($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] > 0){
                        $page1.="
                            <tr style=\"font-size:30px\">
                                <td colspan=\"2\" width=\"40\"></td>
                                <td width=\"470\"><table><tr>
                                        <td width=\"200\">Jumlah Denda</td>
                                        <td width=\"100\">Rp. ".number_format($atr['CPM_ATR_TOTAL'],2)."</td>
                                        <td width=\"30\" align=\"center\">X</td>
                                        <td width=\"25\" align=\"center\">2%</td>
                                        <td width=\"30\" align=\"center\">X</td>
                                        <td width=\"80\" align=\"center\">".round($persen_sanksi/2)." Bulan</td>
                                        </tr></table>
                                </td>
                                <td width=\"60\" align=\"right\">= Rp.</td>
                                <td width=\"123\" align=\"right\" style=\"border-bottom:1px solid #000000\">".number_format($atr['CPM_ATR_TOTAL']*($persen_sanksi/100),2)."</td>
                                </tr>";
                    }else{
                        $page1.="
                            <tr>
                                <td colspan=\"2\" width=\"40\"></td>
                                <td width=\"470\"><table><tr>
                                        <td width=\"190\">Jumlah Denda</td>
                                        <td>Rp. 0</td>
                                        <td width=\"20\">X</td>
                                        <td align=\"center\">2%</td>
                                        <td width=\"20\">X</td>
                                        <td align=\"center\">".round($persen_sanksi/2)." Bulan</td>
                                    </tr></table>
                                </td>
                                <td width=\"60\" align=\"right\">= Rp.</td>
                                <td width=\"123\" align=\"right\"style=\"border-bottom:1px solid #000000\">0</td>
                            </tr>
                        ";
                }
				$jumlahkeseluruhan = '';
                $jumlahkeseluruhan = $atr['CPM_ATR_TOTAL'] + ($atr['CPM_ATR_TOTAL']*($persen_sanksi/100));
                $page1.="
                        <tr style=\"font-size:30px\">
                            <td colspan=\"2\" width=\"40\"></td>
                            <td width=\"470\"><b>Jumlah Keseluruhan</b></td>
                            <td width=\"60\" align=\"right\"><b>= Rp.</b></td>
                            <td width=\"123\" align=\"right\"><b>".number_format($jumlahkeseluruhan,2)."</b></td>
                            </tr>
                ";
                }
            }
            $page1 .="

                        <tr>
                            <td colspan=\"2\" width=\"20\"></td>
                            <td width=\"490\"><b>Jumlah Pokok Pajak yang harus di bayar{$pager}</b></td>
                            <td width=\"60\" align=\"right\" style=\"border-top:1px solid #000000\"><b>= Rp.</b></td>
                            <td width=\"123\" align=\"right\" style=\"border-top:1px solid #000000\"><b>". number_format($DATA['pajak']['CPM_TOTAL_PAJAK'],2) ."</b></td>
                        </tr>
                        <tr>
                            <td width=\"20\"></td>
                            <td width=\"690\" colspan=\"2\">Terbilang : ".ucwords($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK']))." Rupiah</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td><table cellpadding=\"3\">
                    <!--<tr>
                        <td width=\"20\">6.</td>
                        <td width=\"690\">PERNYATAAN<br>Dengan menyadari sepenuhnya akan segala akibatnya termasuk sanksi-sanksi sesuai ketentuan perundang-undangan yangberlaku, saya memberitahukan apa yangtekahs aya beritahukan di atas beserta lampiran-lampirannya adalah benar, lengkap, jelas dan bersyarat.</td>
                    </tr>-->
                    <tr>
                        <td width=\"400\"></td>
                        <td align=\"center\" width=\"300\"><br><br><br> {$KOTA}, {$tgl_pengesahans}
                        <br>KEPALA BADAN PENGELOLAAN PAJAK DAN RETRIBUSI DAERAH<br>
                        KABUPATEN LAMPUNG SELATAN<br><br><br><br><br><br>
                        <b><u> {$PEJABAT_MENGETAHUI['CPM_NAMA']}</b></u><br>
                        NIP. {$PEJABAT_MENGETAHUI['CPM_NIP']}

                        </td>
                    </tr>
                    </table>
                </td>
            </tr>
						<span style=\"font-size:24px\"><i>BPPRD LAMSEL {$tgl_cetak}</i></span>
        </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'F4');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 13, 7, 12, '', '', '', '', false, 300, '', false);
        $pdf->writeHTML($page1, true, false, false, false, '');

        $pdf->Output('skpd-airbawahtanah.pdf', 'I');
    }
	*/

    //new

    public function print_skpd($type = "")
    {
        ob_start();
        global $sRootPath;

        // tambahan qr
        require_once($sRootPath . "qrcode.php");

        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();
        $data = $DATA['pajak'];
        $profil = $DATA['profil'];
        $pajak_atr = $DATA['pajak_atr'];

        $get_npwpd = $profil['CPM_NPWPD'];
        $querys = "select * from patda_wp where CPM_NPWPD = '{$get_npwpd}'";
        $res = mysqli_query($this->Conn, $querys);
        while ($rows = mysqli_fetch_assoc($res)) {
            $kota_wp = $rows['CPM_KOTA_WP'];
            $kecamatan_wp = $rows['CPM_KECAMATAN_WP'];
            $kelurahan_wp = $rows['CPM_KELURAHAN_WP'];
            $alamat_wp = $rows['CPM_ALAMAT_WP'];
        }


        //var_dump($DATA['profil']['CPM_REKENING']);die;

        $DATA = array_merge($data, $profil);
        $DATA['pajak_atr'] = $pajak_atr;
        $arr_rekening = $this->getRekening();

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName, $Conn_gw);
        $query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
        $res = mysqli_query($Conn_gw, $query);
        if ($gw = mysqli_fetch_object($res)) {
			$DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
		}

        $check_approve = mysqli_num_rows($res);
        if ($check_approve == 0) {
            $DATA['CPM_TGL_JATUH_TEMPO'] = '';
            $DATA['A_QR'] = '';
            $DATA['A_STATUS'] = '';
        } else {
            if ($d = mysqli_fetch_assoc($res)) {
                $DATA['CPM_TGL_JATUH_TEMPO'] = $d['expired_date'];
                $DATA['A_QR'] = $d['approval_qr_text'];
                $DATA['A_STATUS'] = $d['approval_status'];
            }
        }
        //var_dump($DATA['CPM_TGL_JATUH_TEMPO'], $DATA['A_QR']);
        mysqli_close($Conn_gw);

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KEPALA_NAMA = $config['KEPALA_DINAS_NAMA'];
        $KEPALA_NIP = $config['KEPALA_DINAS_NIP'];


        $TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
        $tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);
        $PEJABAT = $this->get_pejabat();
        $PEJABAT_MENGETAHUI = $PEJABAT[$_POST['PAJAK']['CPM_PEJABAT_MENGETAHUI']];

        // $DATA['pajak_atr'] = $DATA['pajak_atr'][0];
        // unset($DATA['pajak_atr'][0]);

        $d = explode('/REK/', $DATA['CPM_NO']);
        $NO_URUT = $d[0]; //.'<br/>/REK/'.$d[1];

        $DENDA = 0;
        $TOTAL = $DATA['CPM_TOTAL_PAJAK'];

        // hitung denda
        if (isset($gw) && !empty($gw)) {
            if ($gw->payment_flag == '1') {
                $TOTAL = $gw->patda_total_bayar;
                $DENDA = $gw->patda_denda;
            } elseif (strtotime(date('Y-m-d')) > strtotime($gw->expired_date)) {
                $persen_denda = $this->get_persen_denda($gw->expired_date);
                $DENDA = ($persen_denda / 100) * $TOTAL;
                $TOTAL = $TOTAL + $DENDA;
                // var_dump($persen_denda);die;
            }
        }

        $bulan_awal = $this->arr_bulan[intval(substr($DATA['CPM_MASA_PAJAK1'], 3, 2))];
        $tahun_awal = substr($DATA['CPM_MASA_PAJAK1'], -4, 4);
        $bulan_akhir = $this->arr_bulan[intval(substr($DATA['CPM_MASA_PAJAK2'], 3, 2))];
        $tahun_akhir = substr($DATA['CPM_MASA_PAJAK2'], -4, 4);

        if ($tahun_awal == $tahun_akhir) {
            if ($bulan_awal == $bulan_akhir) {
                $masa_pajak = $bulan_awal . ' ' . $tahun_awal;
            } else {
                $masa_pajak = $bulan_awal . ' s/d. ' . $bulan_akhir . ' ' . $tahun_awal;
            }
        } else {
            $masa_pajak = $bulan_awal . ' ' . $tahun_awal . ' s/d. ' . $bulan_akhir . ' ' . $tahun_akhir;
        }

        $bulanss = str_replace('/', '-', $DATA['CPM_MASA_PAJAK2']);
        // $bulanss = date('d/m/Y', strtotime("+1 month",strtotime($bulanss)));
        $bulanss = date('t/m/Y', strtotime("+1 month", strtotime($bulanss)));
        $arr_tgl = explode('/', $bulanss); //preg_replace("/(\d+)\/(\d+)\/(\d+)/","$3-$2-$1", $DATA['pajak']['CPM_MASA_PAJAK2']);
        $arr_tgl = array_map(function ($v) {
            return (int) $v;
        }, $arr_tgl);
        $batas_setor = $arr_tgl[0] . ' ' . $this->arr_bulan[$arr_tgl[1]] . ' ' . $arr_tgl[2];

        $bulanx = str_replace('/', '-', $DATA['CPM_MASA_PAJAK1']);
        $bulanx = date('d/m/Y', strtotime($bulanx));
        $arr_tglx = explode('/', $bulanx);
        $arr_tglx = array_map(function ($v) {
            return (int) $v;
        }, $arr_tglx);
        $masa_pajaks1 = $arr_tglx[0] . ' ' . $this->arr_bulan[$arr_tglx[1]] . ' ' . $arr_tglx[2];

        $bulanxx = str_replace('/', '-', $DATA['CPM_MASA_PAJAK2']);
        $bulanxx = date('d/m/Y', strtotime($bulanxx));
        $arr_tglxx = explode('/', $bulanxx);
        $arr_tglxx = array_map(function ($v) {
            return (int) $v;
        }, $arr_tglxx);
        $masa_pajaks2 = $arr_tglxx[0] . ' ' . $this->arr_bulan[$arr_tglxx[1]] . ' ' . $arr_tglxx[2];
        //tamabahan
        //var_dump($masa_pajaks1, $masa_pajaks2);

        $get_npwpd = $DATA['CPM_NPWPD'];
        $query_atr = "SELECT CPM_NOP FROM PATDA_AIRBAWAHTANAH_PROFIL WHERE CPM_NPWPD = '$get_npwpd'";
        $res = mysqli_query($this->Conn, $query_atr);
        $rows = mysqli_fetch_assoc($res);

        $nop_nop = '';
        //$total_total_nop = $rowss['total_nop'];
        if ($rows['CPM_NOP'] == '') {
            $nop_nop = '';
        } else {
            $nop_nop = $rows['CPM_NOP'];
        }
        function tgl_indo($tglcetak)
        {
            $bulan = array(
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

            return $pecahkan[2] . '/' . $bulan[(int)$pecahkan[1]] . '/' . $pecahkan[0];
        }
        $tglcetak = date('Y-m-d');
        $tgl_cetak = tgl_indo($tglcetak);

        //var_dump($profil['CPM_REKENING']);
        //die;

        $page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"340\" valign=\"top\" align=\"center\" colspan=\"3\">
										<table border=\"0\" width=\"310\">
											<tr>
												<td width=\"70\"></td>
												<td width=\"250\">
													<b style=\"font-size:26px\"><br/>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
													{$NAMA_PENGELOLA}<br/>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
													<br/>
													{$JALAN}<br/>
													{$KOTA}
													</b>
												</td>
											</tr>
										</table>
										<br/>
                                    </td>
                                    <td width=\"260\" valign=\"top\" align=\"center\">
										<b style=\"font-size:35px\"><br/>
										SKPD<br/>
										SURAT KETETAPAN PAJAK DAERAH<br/>
										</b>&nbsp;&nbsp;
										<table border=\"0\" width=\"200\" cellpadding=\"0\">
											<tr>
												<td align=\"left\" width=\"80\"></td>
												<td width=\"10\"></td>
												<td width=\"195\"  align=\"left\" ></td>
											</tr>
											<tr>
												<td align=\"left\">Masa Pajak</td>
												<td>:</td>
												<td align=\"left\">{$DATA['CPM_MASA_PAJAK1']} s.d {$DATA['CPM_MASA_PAJAK2']}</td>
											</tr>
											<tr>
												<td align=\"left\" >Tahun</td>
												<td>:</td>
												<td align=\"left\" >{$DATA['CPM_TAHUN_PAJAK']}</td>
											</tr>
										</table>
                                    </td>
                                    
                                    <td width=\"110\" valign=\"top\" align=\"center\">                                   
										<br/><br/>
										<b>No. SKPD :</b><br/>
										{$NO_URUT}<br/><br/>
                                        <b style=\"font-size:35px\">Kode Billing</b> <br>
										<span style=\"font-size:32px\">{$gw->payment_code}<br/></span>
                                        
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>


					<tr style=\"font-size:32px\">
						<td width=\"710\">
							<table width=\"710\" border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"400\">
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"150\">NAMA</td>
												<td width=\"550\">: {$DATA['CPM_NAMA_OP']}</td>
											</tr>
											<tr>
												<td>ALAMAT</td>
												<td>: {$DATA['CPM_ALAMAT_WP']}</td>
											</tr>
											<tr>
												<td>KECAMATAN</td>
												<td>: {$DATA['CPM_KECAMATAN_WP']}</td>
											</tr>
											<tr>
												<td>KELURAHAN</td>
												<td>: {$DATA['CPM_KELURAHAN_WP']}</td>
											</tr>
											<tr>
												<td>N.P.W.P.D</td>
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td>TGL. JATUH TEMPO</td>
												<td>: {$DATA['CPM_TGL_JATUH_TEMPO']}</td>
											</tr>
										</table>
									</td>
									<td width=\"310\">
									</td>
								</tr>
							</table>
						</td>
                    </tr>

                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\" style=\"font-size:30px;\">
							<tr>
								<td colspan=\"2\">
									<table width=\"500\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\">
										<tr style=\"background-color:#CCC\">
											<td width=\"30\" align=\"center\"><b>No</b></td>
											<td width=\"170\" align=\"center\"><b>Kode Rekening</b></td>
											<td width=\"300\" align=\"center\"><b>Jenis Pajak Daerah</b></td>
											<td width=\"200\" align=\"center\"><b>Jumlah (Rp.)</b></td>
										</tr>
										<tr>
											<td align=\"center\">1.</td>
											<td align=\"center\">" . $profil['CPM_REKENING'] . ".001</td>
											<td>Pajak Air Tanah</td>
											<td align=\"right\">" . number_format($DATA['CPM_DPP'], 0) . "</td>
										</tr>";

        $html = '';
        $list_op = '';
        $html .= "<tr>
											<td align=\"left\" colspan=\"2\" rowspan=\"3\"></td>
											<td align=\"left\">
												Jumlah Ketetapan Pokok Pajak
											</td>
											<td align=\"right\">
												" . number_format($DATA['CPM_BAYAR_TERUTANG'], 0) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												Jumlah Denda
											</td>
											<td align=\"right\">
												" . number_format($DENDA, 0) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												<b>Jumlah yang harus dibayar</b>
											</td>
											<td align=\"right\">
												<b>" . number_format($TOTAL, 0) . "</b>
											</td>
										</tr>
										<tr>
											<td align=\"left\" colspan=\"4\">
											Dengan huruf :<br/>
											<b><i>" . ucfirst($DATA['CPM_TERBILANG']) . " rupiah</i></b>
											</td>

										</tr>";

        $page1 .= $html;
        $page1 .= "
									</table><br/>
								</td>
							</tr>
							</table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
							<tr>
								<td>
                                <table width=\"100%\" border=\"0\" align=\"left\" style=\"font-size:28px;\">
										<tr>
											<td><b>PERHATIAN : </b></td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap Penyetoran dilakukan melalui Bank yang ditunjuk dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Wajib Pajak dilarang melakukan pembayaran Pajak Terutang kepada petugas penagih yang tidak menunjukkan / memberikan<br>
											&nbsp;&nbsp; &nbsp; &nbsp;
											Surat Ketetapan Pajak Daerah (SKPD)</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;3. Apabila SKPD ini tidak atau kurang dibayar setelah tanggal jatuh tempo dikenakan Sanksi Administrasi Bunga sebesar <br>
											&nbsp;&nbsp; &nbsp; &nbsp;
											2% perbulan dan ditagih dengan menggunakan Surat Tagihan Pajak</td>
										</tr>
									</table>
									</td>
								</tr>
							</table>
                        </td>
                    </tr>
					<tr>
						<td colspan=\"2\" align=\"center\" style=\"font-size:26px;\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
								<tr>
									<td width=\"355\"></td>
									<td align=\"center\"><b>
										{$KOTA}, {$tgl_pengesahans}<br/>
										A.n Kepala Badan Pendapatan Daerah <br/>
										Kabupaten " . ucwords(strtolower($NAMA_PEMERINTAHAN)) . ",<br/>
										Kepala Bidang Pengembangan dan Penetapan <br/>
										";
        if ($PEJABAT_MENGETAHUI['CPM_KEY'] ===  'KABAN_DIPENDA') {
            $page1 .=  $PEJABAT_MENGETAHUI['CPM_JABATAN'] . '<br/>';
        }
        if ((int)$DATA['A_STATUS'] == 1) {
            $imageGenerator = new QRCode(urldecode($DATA['A_QR']), ['s' => 'qr']);
            $imageQr = $imageGenerator->render_image();
            imagepng($imageQr, 'qrcode.png', 9);
            $page1 .= '<table><tr>';
            $page1 .= '<td align="right" width="62%"><img src="qrcode.png" style="width:90px;height:90px;display:block"></td>';
            $page1 .= '<td align="left" style="font-size:24px"><br><br><br><br>Dokumen ini sah dan<br>telah di tanda tangani</td></tr></table>';
        } else {
            $page1 .= '<br><br><br><br><br>';
        }
        $page1 .= "<br>
										<u>{$PEJABAT_MENGETAHUI['CPM_NAMA']}</u><br/>
										NIP. {$PEJABAT_MENGETAHUI['CPM_NIP']}
									</b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\" style=\"font-size:26px;\">
								<tr>
									<td align=\"center\">......................................potong di sini......................................<br><br><br><br></td>
								</tr>
								<tr>
									<td>&nbsp;&nbsp;&nbsp;<table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" style=\"font-size:26px;\">
											<tr>
												<td width=\"430\" colspan=\"2\"><b style=\"font-size:28px\"><u>Tanda Terima</u></b></td>
												<td width=\"270\" align=\"center\">No. SKPD : {$DATA['CPM_NO']}</td>
											</tr>
											<tr>
												<td colspan=\"3\" align=\"center\"><br/></td>
											</tr>
											<tr>
												<td width=\"100\">Nama</td>
												<td width=\"330\">: {$DATA['CPM_NAMA_WP']}</td>
												<!-- <td width=\"270\" rowspan=\"6\" align=\"center\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/> -->
                                                <td width=\"270\" rowspan=\"6\" align=\"center\">{$KOTA}, " . $tgl_pengesahans . "<br/>
												Yang Menerima,<br/><br/><br/><br/><br/><br/><br/>

												<b><u>{$DATA['CPM_NAMA_WP']}</u></b>
												</td>
											</tr>
											<tr>
												<td>Alamat</td>
												<td>: {$DATA['CPM_ALAMAT_WP']} - {$DATA['CPM_KELURAHAN_WP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; KEC. {$DATA['CPM_KECAMATAN_WP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; KOTA/KAB. </td>
											</tr>
											<tr>
												<td>NPWPD</td>
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td colspan=\"2\">{$list_op}</td>
											</tr>
										</table>
									</td>
									<td>

									</td>
								</tr>
							</table>
						</td>
					</tr>
					
					<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
                </table>";
        // echo $page1; exit;
        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 8, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'F4');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 7, 12, 17, '', '', '', '', false, 300, '', false);
        $pdf->writeHTML($page1, true, false, false, false, '');
        ob_end_clean();
        $pdf->Output('skpd-reklame.pdf', 'I');
        ob_end_flush();
    }



    public function print_sptpd()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $data_input = $this->check_status(2, $this->CPM_ID, $this->id_pajak);
        $petugas_input = $data_input->operator_input;
        $role = $this->check_role($petugas_input);
        $data_verifikasi = $this->check_status(5, $this->CPM_ID, $this->id_pajak);
        $petugas_verifikasi = $role == 'rmPatdaWp' ? $data_verifikasi->operator_verifikasi : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $tanggal_verifikasi = $role == 'rmPatdaWp' ? $data_verifikasi->tanggal_verifikasi : '';

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
        $VERNIP = $config['BAG_VERIFIKASI_NIP'];
        $TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
        $tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);
        // echo'<pre>';print_r($config);exit;

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $pemerintah = explode(' ', $JENIS_PEMERINTAHAN);
        $pemerintah_label = strtoupper($pemerintah[0]);
        $pemerintah_jenis = strtoupper($pemerintah[1]);
        //tambahan
        $persen_sanksi = 0;
        if (($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] + 0) > 0) {
            // $persen_sanksi = ($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK'])*100;
            // $persen_sanksi = round($persen_sanksi/2)*2;
            // $persen_sanksi = round(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK']*100)/2,0)*2;
            //tamabahan
            $tes3 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $tes3;
            $persen_sanksi = round(($tes4 / $tes3) * 100);
        }
        //tambahan
        $volume_total = 0.0;
        for ($i = 0; $i < count($_POST['PAJAK_ATR']['CPM_ATR_VOLUME']); $i++) {
            $hasil = str_replace(",", ".", $_POST['PAJAK_ATR']['CPM_ATR_VOLUME'][$i]);
            $volume_total += $hasil;
        }
        $volume_total = $volume_total * 1000;

        function tgl_indo($tglcetak)
        {
            $bulan = array(
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

            return $pecahkan[2] . '/' . $bulan[(int)$pecahkan[1]] . '/' . $pecahkan[0];
        }
        $tglcetak = date('Y-m-d');
        $tgl_cetak = tgl_indo($tglcetak);

        $page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/>
										<br/><br/>
										<br/>
                                        <b>" . $pemerintah_label . "<br/>" . $pemerintah_jenis . ' ' . strtoupper($NAMA_PEMERINTAHAN) . "</b>
                                    </td>
                                    <td width=\"310\" valign=\"top\" align=\"center\">
										<b style=\"font-size:55px\">S P T P D</b><br/>
                                        (SURAT PEMBERITAHUAN PAJAK DAERAH)
                                        <b style=\"font-size:55px\">PAJAK AIR BAWAHTANAH</b><br/>
                                        <b>Tahun Pajak : {$DATA['pajak']['CPM_TAHUN_PAJAK']}</b>
                                    </td>
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/>
										Nomor SPTPD : <br/>
										{$DATA['pajak']['CPM_NO']}<br/><br/>
										Masa Pajak : <br/>
										{$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
						<td width=\"710\">
							<table width=\"710\" border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"400\">
										<br/><br/><br/>&nbsp;
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"100\">NPWPD</td>
												<td width=\"280\">: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td>No. Telp.</td>
												<td>: {$DATA['profil']['CPM_TELEPON_WP']}</td>
											</tr>
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td>
												Kepada Yth. <br/>
												Kepala Badan Pendapatan Daerah <br/>
												Kabupaten " . ucfirst(strtolower($NAMA_PEMERINTAHAN)) . "<br/>
												di <b style=\"font-size:40px\">{$KOTA}</b>
											</td>
										</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
                            <tr style=\"font-size:28px\">
                                <td><table width=\"100%\" border=\"0\" align=\"left\">
                                        <tr>
                                            <td>PERHATIAN : </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;&nbsp;&nbsp;1. Harap diisi dalam rangkap enam (6) ditulis dengan huruf <b>CETAK</b> </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;&nbsp;&nbsp;2. Beri nomor pada kotak yang tersedia untuk jawaban yang diberikan.</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;&nbsp;&nbsp;3. Formulir ini diterima oleh petugas setelah ditandatangani oleh Wajib Pajak atau Kuasanya.</td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
                            <b>A. IDENTITAS SUBJEK DAN OBJEK PAJAK</b>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"7\">
                            <tr>
                                <td><table width=\"100%\" border=\"0\" align=\"left\">
                                        <tr>
                                            <td width=\"200\">A. NAMA OBJEK PAJAK</td>
                                            <td width=\"500\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                        </tr>
                                        <tr>
                                            <td>B. ALAMAT OBJEK PAJAK</td>
                                            <td>: {$DATA['profil']['CPM_ALAMAT_OP']}<br/>
                                            &nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_NAMA_KECAMATAN_OP']}<br/>
                                            &nbsp;&nbsp;Kelurahan : {$DATA['profil']['CPM_NAMA_KELURAHAN_OP']}<br/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>C. NAMA WAJIB PAJAK</td>
                                            <td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                        </tr>
                                        <tr>
                                            <td>D. ALAMAT WAJIB PAJAK</td>
                                            <td>: {$DATA['profil']['CPM_ALAMAT_WP']}<br/>
                                            &nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_KECAMATAN_WP']}<br/>
                                            &nbsp;&nbsp;Kelurahan : {$DATA['profil']['CPM_KELURAHAN_WP']}<br/>
                                            </td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
                            <b>B. INFORMASI UMUM OBJEK PAJAK</b>
                        </td>
                    </tr>
                    <tr style=\"font-size:32px\">
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"0\" cellspacing=\"0\">
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;a.</td>
                                    <td align=\"left\" width=\"270\">Golongan Air</td>
                                    <td align=\"left\" width=\"420\" colspan=\"2\"> {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;b.</td>
                                    <td align=\"left\" width=\"270\">Harga per m<sup>3</sup></td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['harga'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;c.</td>
                                    <td align=\"left\" width=\"270\">Volume Air yang diambil</td>
                                    <td align=\"right\" width=\"180\" colspan=\"2\"> " .  number_format($volume_total, 2) . " m<sup>3</sup></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;d.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran Pemakaian</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;e.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;f.</td>
                                    <td align=\"left\" width=\"270\">Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;g.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;h.</td>
                                    <td align=\"left\" width=\"270\">Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> 0.00</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;i.</td>
                                    <td align=\"left\" width=\"270\">Sanksi Administrasi Telat Lapor ({$persen_terlambat_lap}%) x " . round($persen_sanksi / 2) . " Bulan</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;j.</td>
                                    <td align=\"left\" width=\"270\">Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;k.</td>
                                    <td align=\"left\" width=\"270\">Data Pendukung</td>
                                    <td align=\"left\" width=\"420\"> </td>
                                </tr>
                                <tr>
                                    <td align=\"right\" width=\"40\">a).</td>
                                    <td align=\"left\" width=\"250\">SPTPD</td>
                                    <td align=\"left\" width=\"420\">
                                        <table width=\"80\" border=\"0\">
                                            <tr>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"50\">1. Ada / </td>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"100\">2. Tidak Ada</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"right\" width=\"40\">b).</td>
                                    <td align=\"left\" width=\"250\">Rekapitulasi Pemanfaatan Air</td>
                                    <td align=\"left\" width=\"420\">
                                        <table width=\"80\" border=\"0\">
                                            <tr>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"50\">1. Ada / </td>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"100\">2. Tidak Ada</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"right\" width=\"40\">c).</td>
                                    <td align=\"left\" width=\"250\">Fotocopy SIPA, KTP, SIUP</td>
                                    <td align=\"left\" width=\"420\">
                                        <table width=\"80\" border=\"0\">
                                            <tr>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"50\">1. Ada / </td>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"100\">2. Tidak Ada</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"right\" width=\"40\">d).</td>
                                    <td align=\"left\" width=\"250\">Foto Water Meter</td>
                                    <td align=\"left\" width=\"420\">
                                        <table width=\"80\" border=\"0\">
                                            <tr>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"50\">1. Ada / </td>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"100\">2. Tidak Ada</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"right\" width=\"40\">e).</td>
                                    <td align=\"left\" width=\"250\">NPWP / NPWPD</td>
                                    <td align=\"left\" width=\"420\">
                                        <table width=\"80\" border=\"0\">
                                            <tr>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"50\">1. Ada / </td>
                                                <td width=\"30\">
                                                    <table cellpadding=\"0\" border=\"1\">
                                                        <tr style=\"background-color:#EBEBEB;\">
                                                            <td width=\"20\" align=\"center\">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width=\"100\">2. Tidak Ada</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"20\">&nbsp;&nbsp;l.</td>
                                    <td align=\"left\" width=\"270\">Keterangan</td>
                                    <td align=\"left\" width=\"420\">{$DATA['pajak']['CPM_KETERANGAN']}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
                            <b>C. PERNYATAAN</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\">
                            <table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5s\">
                                <tr>
                                    <td colspan=\"2\">Dengan menyadari sepenuhnya akan segala akibat termasuk sanksi-sanksi sesuai dengan ketentuan perundang-undangan yang berlaku, saya atau yang saya beri kuasa menyatakan bahwa apa yang telah kami beritahukan tersebut diatas berserta lampiran-lampirannya adalah benar, lengkap dan jelas.
                                    </td>
                                </tr>
                                <tr>
                                    <td width=\"355\"></td>
                                    <td align=\"center\">
                                        {$KOTA}, {$tgl_pengesahans}<br/>
                                        Wajib Pajak<br/><br/><br/><br><br>
                                        <u>{$DATA['profil']['CPM_NAMA_WP']}</u>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
                            <b>D. DIISI OLEH PETUGAS PENDATA</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
                                <tr>
                                    <td><table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
                                            <tr>
                                                <td width=\"150\">Diterima Tanggal</td>
                                                <td width=\"260\" colspan=\"2\">: {$tanggal_verifikasi}</td>
                                            </tr>
                                            <tr>
                                                <td width=\"150\">Nama Petugas</td>
                                                <td width=\"260\" colspan=\"2\">: {$petugas_verifikasi}</td>
                                            </tr>
                                            <tr>
                                                <td width=\"150\">NIP. </td>
                                                <td width=\"260\" colspan=\"2\">: </td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\"><br/></td>
                                            </tr>
                                            <tr>
												<td width=\"150\">Tanda Tangan</td>
												<td width=\"195\">:</td>
												<td width=\"345\" align=\"center\">
													<u>{$petugas_verifikasi}</u>
												</td>
											</tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
					
					<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>

                </table>";
        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 8, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'F4');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 29, 9, 9, '', '', '', '', false, 30, '', false);
        $pdf->writeHTML($page1, true, false, false, false, '');
        //echo "<pre>";print_r($pdf);exit;
        //echo $page1;
        ob_clean();
        $pdf->Output('sptpd-airbawahtanah.pdf', 'I');
    }
    //tamabahan
    /*public function print_sspd() {
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
    $KODE_AREA = $config['KODE_AREA'];
    $TGL_PENETAPAN = $this->getTanggalPenetapan($this->id_pajak, $this->CPM_ID);
    $bulan_pajak = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
    $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$bulan_pajak}";

    $KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
    if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
        $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
        #$PERIODE = "000" . substr($this->CPM_NO, 0, 9);
        $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);
    }
    $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);


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
            $months = [1,2,3,4,5,6,7,8,9,10,11,12];

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
                                <th width=\"130\" align=\"center\">
                                <h5>KOHIR</h5> <br/>
                                08.001030<br/>
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
                                <td width=\"230\">Kode Bayar (Bank)</td>
                                <td>: {$PERIODE}</td>
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
                            <td width=\"230\">Mata Anggaran</td>
                            <td rowspan=\"2\">Untuk Pembayaran Pajak Air Tanah</td>
                        </tr>
                        <tr>
                            <td width=\"230\">kosong</td>
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
            <td width=\"355\" align=\"center\">
            Mengetahui,<br>
            Bendahara Penerimaan/<br/>
            Bendara Penerimaan Pembantu <br/>
            <br/><br/>
            <h4 style=\"text-decoration:underline\">Patimah,S.SOS</h4><br/>
            NIP. 19630720 198703 2 002
            </td>
            <td width=\"355\" align=\"center\">
            Penyetor<br/>
            {$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
            <br/><br/><br/><br/><br/>
            (" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>
            </td>
            </tr>
        </table>
    </td>
</tr>

                Coret yang tidak perlu beri tanda X pada kolom yang berkenaan <br>
                Tembusan <br>
                1. Wajib Pajak <br>
                2. Arsip/Ekstra <br>
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
    $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 33, 27, 17, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);

    $pdf->Output('sspd-airbawahtanah.pdf', 'I');
}*/

    public function read_dokumen()
    {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];

            $select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);
            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysqli_query($this->Conn, $query);
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
                    FROM {$this->PATDA_AIRBAWAHTANAH_DOC} pj INNER JOIN {$this->PATDA_AIRBAWAHTANAH_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_AIRBAWAHTANAH_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_AIRBAWAHTANAH_ID
                    WHERE ";

        if (in_array("draf", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['draf'] = (int) $data['total'];
        }
        if (in_array("proses", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['proses'] = (int) $data['total'];
        }
        if (in_array("ditolak", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['ditolak'] = (int) $data['total'];
        }
        if (in_array("disetujui", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui'] = (int) $data['total'];
        }

        if (in_array("draf_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['draf_ply'] = (int) $data['total'];
        }
        if (in_array("proses_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['proses_ply'] = (int) $data['total'];
        }
        if (in_array("ditolak_ply", $arr_tab) || in_array("ditolak_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result)) {
                $notif['ditolak_ply'] = (int) $data['total'];
                $notif['ditolak_ver'] = (int) $data['total'];
            }
        }
        if (in_array("disetujui_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui_ply'] = (int) $data['total'];
        }

        if (in_array("tertunda", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['tertunda'] = $data['total'];
        }
        if (in_array("disetujui_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui_ver'] = (int) $data['total'];
        }
        echo $this->Json->encode($notif);
    }

    public function get_previous_pajak($npwpd, $nop)
    {
        $Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.12");
        $pajak_atr = array();
        $list_nop = array();

        $query = "
            SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
            FROM PATDA_AIRBAWAHTANAH_DOC AS DOC
            INNER JOIN PATDA_AIRBAWAHTANAH_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
            WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}' AND
            str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y') = (
                SELECT MAX(str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y'))
                FROM PATDA_AIRBAWAHTANAH_DOC AS DOC
                INNER JOIN PATDA_AIRBAWAHTANAH_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
                WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}'
            )";
        // echo $query;
        $result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);
		
        $ms = $this->inisialisasi_masa_pajak();

        if (empty($pajak['CPM_ID'])) {

            $pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
            $pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
            $pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
            $pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
            $pajak['CPM_HARGA'] = 0;

            $profil = $Op->get_last_profil($npwpd, $nop);

            $tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
            $list_nop = $Op->get_list_nop($npwpd);
        } else { //if data available
            $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
            $tarif = $pajak['CPM_TARIF_PAJAK'];
            $list_nop = $Op->get_list_nop($npwpd);
        }

        $pajak['CPM_ID'] = '';
        $pajak['CPM_NO'] = '';
        $pajak['CPM_ID_PROFIL'] = '';
        $pajak['CPM_HARGA'] = 0;

        $pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
        $pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;

        $query = "SELECT * FROM PATDA_AIRBAWAHTANAH_INDEX";
        $result = mysqli_query($this->Conn, $query);
        $index = array();
        while ($d = mysqli_fetch_assoc($result)) {
            $index[$d['CPM_FAKTOR']][$d['CPM_INDEX']]['URAIAN'] = "{$d['CPM_URAIAN']} ({$d['CPM_INDEX']})";
            $index[$d['CPM_FAKTOR']][$d['CPM_INDEX']]['INDEX'] = $d['CPM_INDEX'];
        }

        $pajak = array_merge($pajak, $arr_rekening);

        //echo '<pre>',print_r($pajak,true),'</pre>';exit;
        return array(
            'pajak' => $pajak,
            'tarif' => $tarif,
            'profil' => $profil,
            'list_nop' => $list_nop,
            'index' => $index
        );
    }

    function list_npa()
    {
        $query = "SELECT * from PATDA_AIRBAWAHTANAH_NPA WHERE CPM_AKTIF='1'  AND CPM_PERUNTUKAN NOT LIKE '%(Lama)'";
        $res = mysqli_query($this->Conn, $query);
        $output = array('combo' => array(), 'tarif' => array());
        while ($row = mysqli_fetch_assoc($res)) {
            $output['combo'][$row['CPM_PERUNTUKAN']] = $row['CPM_PERUNTUKAN'];
            $output['tarif'][$row['CPM_PERUNTUKAN']][$row['CPM_DEBIT_MIN'] . '-' . $row['CPM_DEBIT_MAX']] = $row['CPM_TARIF'];
        }
        //mysqli_close($this->Conn);
        return $output;
    }

    function list_npa2()
    {
        $query = "SELECT * from PATDA_AIRBAWAHTANAH_NPA WHERE CPM_AKTIF='1'  AND CPM_PERUNTUKAN  LIKE '%(Lama)'";
        $res = mysqli_query($this->Conn, $query);
        $output = array('combo' => array(), 'tarif' => array());
        while ($row = mysqli_fetch_assoc($res)) {
            $output['combo'][$row['CPM_PERUNTUKAN']] = $row['CPM_PERUNTUKAN'];
            $output['tarif'][$row['CPM_PERUNTUKAN']][$row['CPM_DEBIT_MIN'] . '-' . $row['CPM_DEBIT_MAX']] = $row['CPM_TARIF'];
        }
        //mysqli_close($this->Conn);
        return $output;
    }

    public function print_nota_hitung()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        // $DATA = (object) array_merge($DATA['pajak'], $DATA['profil'], $DATA['pajak_atr'][0]);


        // var_dump(mysqli_fetch_assoc($res)\);

        // echo "<pre>";
        // var_dump($DATA);exit();
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
        $KABID_NAMA = $config['KABID_PENDATAAN_NAMA'];
        $KABID_NIP = $config['KABID_PENDATAAN_NIP'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $VERNIP = $config['BAG_VERIFIKASI_NIP'];
        $KASIE_NAMA = $config['KASIE_PENETAPAN_NAMA'];
        $KASIE_NIP = $config['KASIE_PENETAPAN_NIP'];


        $TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
        $tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);
        $PEJABAT = $this->get_pejabat();
        $PEJABAT_MENGETAHUI = $PEJABAT[$_POST['PAJAK']['CPM_PEJABAT_MENGETAHUI']];

        #$KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        #if ($DATA->CPM_TIPE_PAJAK'] == 2) {
        $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
        #}
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
        $DATA['CPM_NO_SSPD'] = $DATA['pajak']['CPM_NO'];
        $PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);

        $rekening = $this->get_list_rekening($DATA['profil']['CPM_REKENING']);
        $list_type_masa = $this->get_type_masa();


        $query = "SELECT * from PATDA_AIRBAWAHTANAH_NPA WHERE CPM_AKTIF='1' AND CPM_PERUNTUKAN='{$DATA['pajak']['CPM_PERUNTUKAN']}'";
        // echo $query;
        $arr_tarif = array();
        $res = mysqli_query($this->Conn, $query);
        $tarif = array();
        while ($d = mysqli_fetch_array($res)) {
            array_push($tarif, $d['CPM_TARIF']);
        }

        $bulanss = str_replace('/', '-', $DATA['pajak']['CPM_MASA_PAJAK2']);
        // $bulanss = date('d/m/Y', strtotime("+1 month",strtotime($bulanss)));
        $bulanss = date('t/m/Y', strtotime("+1 day", strtotime($bulanss)));
        $arr_tgl = explode('/', $bulanss); //preg_replace("/(\d+)\/(\d+)\/(\d+)/","$3-$2-$1", $DATA['pajak']['CPM_MASA_PAJAK2']);
        $arr_tgl = array_map(function ($v) {
            return (int) $v;
        }, $arr_tgl);
        $batas_setor = $arr_tgl[0] . ' ' . $this->arr_bulan[$arr_tgl[1]] . ' ' . $arr_tgl[2];

        $persen_sanksi = 0;
        if (($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] + 0) > 0) {
            //$persen_sanksi = ($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK'])*100;
            //$persen_sanksi = round($persen_sanksi/2)*2;
            //tamabahan
            $tes3 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $tes3;
            $persen_sanksi = round(($tes4 / $tes3) * 100);
        }

        function tgl_indo($tglcetak)
        {
            $bulan = array(
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

            return $pecahkan[2] . '/' . $bulan[(int)$pecahkan[1]] . '/' . $pecahkan[0];
        }
        $tglcetak = date('Y-m-d');
        $tgl_cetak = tgl_indo($tglcetak);

        if ($DATA['pajak']['CPM_PERUNTUKAN'] == 'Industri Besar (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Industri Kecil (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Niaga Besar (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Niaga Kecil (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Perkebunan, Perikanan, Peternakan (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Usaha Lain Yang Bersifat Komersil/Industri Minuman (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Sosial (Lama)') {
            #print_r($DATA);exit;
            $html = "<table width=\"1015\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"350\" align=\"center\"><table>
                                    <tr>
                                        <td width=\"70\"></td>
                                        <td width=\"210\">
                                            <b>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                            " . strtoupper($NAMA_PENGELOLA) . "<br /><br />
                                            <font class=\"normal\">{$JALAN}<br/>
                                            </font></b>
                                        </td>
                                    </tr>
                                    </table>
                                    </th>
                                    <th width=\"365\">
                  										<table><tr><td width=\"365\" align=\"center\">
                  										<b>NOTA PERHITUNGAN PAJAK</b>
                  										</td></tr>

                                        </table>
                                    </th>
                                    <th width=\"300\">
                                        <table>
                                        <tr>
                                            <td width=\"150\">
                                                Nomor Nota Perhitungan
                                            </td>
                                            <td width=\"130\" align=\"right\">
                                                <b>{$DATA['pajak']['CPM_NO_SSPD']}</b>
                                            </td>
                                        </tr>

                                        </table>
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"5\">
                            <tr>
                                <td width=\"300\">
                                Nama WP : <b>{$DATA['profil']['CPM_NAMA_WP']}</b><br>
                                Nama OP : <b>{$DATA['profil']['CPM_NAMA_OP']}</b>
                                </td>
                                <td width=\"415\">Alamat : {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                <td width=\"300\">NPWPD : <b>" . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</b></td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table width=\"900\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                <td><table width=\"1000\" border=\"1\" cellpadding=\"2\">
                                    <tr style=\"font-size:30px;\">
                                        <th width=\"30\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>NO.</b></th>
                                        <th width=\"100\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>JENIS PAJAK</b></th>
                                        <th width=\"100\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>AYAT</b></th>
                                        <th width=\"200\" colspan=\"2\" align=\"center\"><b>DASAR PENGENAAN</b></th>
                                        <th width=\"210\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>TARIF</b></th>
                                        <th width=\"130\" rowspan=\"2\" align=\"center\"><b>KETETAPAN <br>(Rp.)</b></th>
                                        <th width=\"125\" rowspan=\"2\" align=\"center\"><b>DENDA/<br> BIAYA ADM. <br>(Rp.)</b></th>
                                        <th width=\"120\" rowspan=\"2\" align=\"center\"><b>JUMLAH <br>(Rp.) </b></th>
                                    </tr>
                                    <tr>
                                        <th width=\"90\" align=\"center\"><b>Uraian</b></th>
                                        <th width=\"110\" align=\"center\"><b>Banyaknya Nilai</b></th>
                                    </tr>";
            $i = 0;
            foreach ($DATA['pajak_atr'] as $no => $atr) {
                $no++;
                $i++;
                //$bulanss =$this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
                $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']] : '';

                if ($i == 2) {
                    //$bulanss =$this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+1];
                    $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 1] : '';
                }
                if ($i == 3) {
                    //$bulanss = $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+2];
                    $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 2] : '';
                }
                $html .= "<tr style=\"font-size:30px;\">
                                                        <td width=\"30\" align=\"center\">$no</td>
                                                        <td width=\"100\" align=\"center\">" . ($no == 1 ? 'PAJAK AIR TANAH<br><br>' : '') . "" . $bulanss . "</td>
                                                        <td width=\"100\" align=\"center\">" . ($no == 1 ? $DATA['profil']['CPM_REKENING'] : '') . "</td>
                                                        <td width=\"90\" align=\"right\">
                                                            0 - 100 M<sup>2</sup>&nbsp;<br>
                                                            101 - 1000 M<sup>2</sup>&nbsp;<br>
                                                            1001 - 2500 M<sup>2</sup>&nbsp;<br>
                                                            > 2500 M<sup>2</sup>&nbsp;<br>
                                                            
                                                        </td>
                                                        <td width=\"90\" align=\"right\">";

                // exit();
                $hitNilaiAwal = $atr['CPM_ATR_VOLUME'];
                // $sisa = $hitNilaiAwal - 100;
                $sisa = 0;

                if ($hitNilaiAwal > 100) {
                    $sisa = $hitNilaiAwal - 100;
                    $total1 = 100;
                    $html .= "100 M<sup>2</sup><br>";
                } else {

                    $html .= "{$hitNilaiAwal} M<sup>2</sup><br>";

                    $total1 = $hitNilaiAwal;
                    //$sisa = 100 - $hitNilaiAwal;
                    $sisa = 0;
                }
                // echo "$total1";exit();

                if ($sisa > 900) {
                    $sisa = $sisa - 900;
                    $total2 = 900;

                    $html .= "900 M<sup>2</sup><br>";
                } else {
                    if ($sisa != 0) {
                        $html .= "{$sisa} M<sup>2</sup><br>";
                    } else {
                        $html .= "0.00 M<sup>2</sup><br>";
                    }
                    $total2 = $sisa;
                    $sisa = $sisa - $sisa;
                }

                if ($sisa  > 1500) {
                    $sisa = $sisa - 1500;
                    $total3 = 1500;

                    $html .= "1500 M<sup>2</sup><br>";
                } else {
                    if ($sisa != 0) {
                        $html .= "{$sisa} M<sup>2</sup><br>";
                    } else {
                        $html .= "0.00 M<sup>2</sup><br>";
                    }
                    $total3 = $sisa;
                    $sisa = $sisa - $sisa;
                }


                $total4 = $sisa;

                $html .= number_format($sisa, 2) . " M<sup>2</sup>";




                $html .= "
                                                        </td>
                                                        <td width=\"20\" align=\"center\">
                                                        X<br>
                                                        X<br>
                                                        X<br>
                                                        X<br>
                                                        X
                                                        </td>
                                                        <td width=\"120\" align=\"center\">
                                                        <table>
                                                            <tr>
                                                                <td align=\"right\" width=\"110\">
                                                                Rp. " . number_format($tarif[0], 2) . "<br>
                                                                Rp. " . number_format($tarif[1], 2) . "<br>
                                                                Rp. " . number_format($tarif[2], 2) . "<br>
                                                                Rp. " . number_format($tarif[3], 2) . "<br>
                                                                
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        </td>
                                                        <td width=\"30\" align=\"center\">
                                                            X<br>
                                                            X<br>
                                                            X<br>
                                                            X
                                                        </td>

                                                        <td width=\"60\" align=\"center\">
                                                            20%<br>
                                                            20%<br>
                                                            20%<br>
                                                            20%
                                                        </td>
                                                ";


                $SUMTotal1 = $total1 * $tarif[0] * 0.2;
                $SUMTotal2 = $total2 * $tarif[1] * 0.2;
                $SUMTotal3 = $total3 * $tarif[2] * 0.2;
                $SUMTotal4 = $total4 * $tarif[3] * 0.2;


                $SANKSITotal1 = $SUMTotal1 * ($persen_sanksi / 100);
                $SANKSITotal2 = $SUMTotal2 * ($persen_sanksi / 100);
                $SANKSITotal3 = $SUMTotal3 * ($persen_sanksi / 100);
                $SANKSITotal4 = $SUMTotal4 * ($persen_sanksi / 100);


                $allTOTAL = $SUMTotal1 + $SUMTotal2 + $SUMTotal3 + $SUMTotal4;
                $sanksiTOTAL = $SANKSITotal1 + $SANKSITotal2 + $SANKSITotal3 + $SANKSITotal4;
                $html .= "
                                                        <td width=\"130\" align=\"right\">
                                                            " . number_format($SUMTotal1, 2) . " <br>
                                                            " . number_format($SUMTotal2, 2) . " <br>
                                                            " . number_format($SUMTotal3, 2) . " <br>
                                                            " . number_format($SUMTotal4, 2) . " <br>
                                                           
                                                        </td>
                                                        <td width=\"125\" align=\"right\"><br><br><br>" . number_format($sanksiTOTAL, 2) . "</td>
                                                        <td width=\"120\" align=\"right\">
                                                            " . number_format($SUMTotal1 + $SANKSITotal1, 2) . " <br>
                                                            " . number_format($SUMTotal2 + $SANKSITotal2, 2) . " <br>
                                                            " . number_format($SUMTotal3 + $SANKSITotal3, 2) . " <br>
                                                            " . number_format($SUMTotal4 + $SANKSITotal4, 2) . " <br>
                                                            
                                                        </td>
                                                    </tr>

                                            ";
            }

            $html .= "


                                        <tr>
                                            <td colspan=\"10\" align=\"right\"><b>JUMLAH</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td colspan=\"2\" align=\"right\"><b>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</b></td>
                                        </tr>
                                        <tr>
                                            <td colspan=\"12\">
                                                Jumlah dengan huruf:
                                                <i>" . ucfirst($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " rupiah</i>
                                            </td>
                                            </tr>
                                        </table>

                                        <br/><br/>


                                        <table width=\"1015\" border=\"0\" cellpadding=\"3\">
                                            <tr>
                                                <td>
                                                    <table width=\"299\" border=\"0\">
                                                        <tr>
                                                          <td width=\"289\" align=\"center\">
                                                          Kepala Bidang Daerah II<br>
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td><p>&nbsp;</p>
                                                            <p>&nbsp;</p></td>
                                                        </tr>
                                                        <tr>
                                                          <td align=\"center\">
                                                            <strong><u>{$KABID_NAMA}</u></strong><br/>
                                                            NIP.{$KABID_NIP}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table width=\"299\" border=\"0\">
                                                        <tr>
                                                            <td width=\"289\" align=\"center\">
                                                                <br/>Kasubid Penghitungan & Penetapan<br>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><p>&nbsp;</p>
                                                                <p>&nbsp;</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align=\"center\">
                                                            <strong><u>{$KASIE_NAMA}</u></strong><br/>
                                                            NIP.{$KASIE_NIP}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table width=\"470\" border=\"0\">
                                                        <tr>
                                                          <td width=\"100\">Dibuat Tanggal </td>
                                                          <td>: {$DATA['pajak']['CPM_TGL_LAPOR']}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
							<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
                            </table>                            ";
        } else {
            #print_r($DATA);exit;
            $html = "<table width=\"1015\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"350\" align=\"center\"><table>
                                    <tr>
                                        <td width=\"70\"></td>
                                        <td width=\"210\">
                                            <b>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                            " . strtoupper($NAMA_PENGELOLA) . "<br /><br />
                                            <font class=\"normal\">{$JALAN}<br/>
                                            </font></b>
                                        </td>
                                    </tr>
                                    </table>
                                    </th>
                                    <th width=\"365\">
                  										<table><tr><td width=\"365\" align=\"center\">
                  										<b>NOTA PERHITUNGAN PAJAK</b>
                  										</td></tr>

                                        </table>
                                    </th>
                                    <th width=\"300\">
                                        <table>
                                        <tr>
                                            <td width=\"150\">
                                                Nomor Nota Perhitungan
                                            </td>
                                            <td width=\"130\" align=\"right\">
                                                <b>{$DATA['pajak']['CPM_NO_SSPD']}</b>
                                            </td>
                                        </tr>

                                        </table>
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"5\">
                            <tr>
                                <td width=\"300\">
                                Nama WP : <b>{$DATA['profil']['CPM_NAMA_WP']}</b><br>
                                Nama OP : <b>{$DATA['profil']['CPM_NAMA_OP']}</b>
                                </td>
                                <td width=\"415\">Alamat : {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                <td width=\"300\">NPWPD : <b>" . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</b></td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table width=\"900\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                <td><table width=\"1000\" border=\"1\" cellpadding=\"2\">
                                    <tr style=\"font-size:30px;\">
                                        <th width=\"30\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>NO.</b></th>
                                        <th width=\"100\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>JENIS PAJAK</b></th>
                                        <th width=\"100\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>KODE REKENING</b></th>
                                        <th width=\"200\" colspan=\"2\" align=\"center\"><b>DASAR PENGENAAN</b></th>
                                        <th width=\"210\" rowspan=\"2\" align=\"center\"><div style=\"padding-top:10px\"></div><b>TARIF</b></th>
                                        <th width=\"130\" rowspan=\"2\" align=\"center\"><b>KETETAPAN <br>(Rp.)</b></th>
                                        <th width=\"125\" rowspan=\"2\" align=\"center\"><b>DENDA/<br> BIAYA ADM. <br>(Rp.)</b></th>
                                        <th width=\"120\" rowspan=\"2\" align=\"center\"><b>JUMLAH <br>(Rp.) </b></th>
                                    </tr>
                                    <tr>
                                        <th width=\"90\" align=\"center\"><b>Uraian</b></th>
                                        <th width=\"110\" align=\"center\"><b>Banyaknya Nilai</b></th>
                                    </tr>";
            $i = 0;
            foreach ($DATA['pajak_atr'] as $no => $atr) {
                $no++;
                $i++;
                //$bulanss =$this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
                $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']] : '';

                if ($i == 2) {
                    //$bulanss =$this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+1];
                    $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 1] : '';
                }
                if ($i == 3) {
                    //$bulanss = $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']+2];
                    $bulanss = isset($this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]) ? $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 2] : '';
                }
                $html .= "<tr style=\"font-size:30px;\">
                                                        <td width=\"30\" align=\"center\">$no</td>
                                                        <td width=\"100\" align=\"center\">" . ($no == 1 ? 'PAJAK AIR TANAH<br><br>' : '') . "" . $bulanss . "</td>
                                                        <td width=\"100\" align=\"center\">" . $DATA['profil']['CPM_REKENING'] . '.001' . "</td>
                                                        <td width=\"90\" align=\"right\">
                                                            0 - 100 M<sup>2</sup>&nbsp;<br>
                                                            101 - 500 M<sup>2</sup>&nbsp;<br>
                                                            501 - 1000 M<sup>2</sup>&nbsp;<br>
                                                            1001 - 2500 M<sup>2</sup>&nbsp;<br>
                                                            > 2500 M<sup>2</sup>&nbsp;
                                                        </td>
                                                        <td width=\"90\" align=\"right\">";

                // exit();
                $hitNilaiAwal = $atr['CPM_ATR_VOLUME'];
                // $sisa = $hitNilaiAwal - 100;
                $sisa = 0;

                if ($hitNilaiAwal > 100) {
                    $sisa = $hitNilaiAwal - 100;
                    $total1 = 100;
                    $html .= "100 M<sup>2</sup><br>";
                } else {

                    $html .= "{$hitNilaiAwal} M<sup>2</sup><br>";

                    $total1 = $hitNilaiAwal;
                    //$sisa = 100 - $hitNilaiAwal;
                    $sisa = 0;
                }
                // echo "$total1";exit();

                if ($sisa > 400) {
                    $sisa = $sisa - 400;
                    $total2 = 400;

                    $html .= "400 M<sup>2</sup><br>";
                } else {
                    if ($sisa != 0) {
                        $html .= "{$sisa} M<sup>2</sup><br>";
                    } else {
                        $html .= "0.00 M<sup>2</sup><br>";
                    }
                    $total2 = $sisa;
                    $sisa = $sisa - $sisa;
                }

                if ($sisa  > 500) {
                    $sisa = $sisa - 500;
                    $total3 = 500;

                    $html .= "500 M<sup>2</sup><br>";
                } else {
                    if ($sisa != 0) {
                        $html .= "{$sisa} M<sup>2</sup><br>";
                    } else {
                        $html .= "0.00 M<sup>2</sup><br>";
                    }
                    $total3 = $sisa;
                    $sisa = $sisa - $sisa;
                }

                if ($sisa > 1500) {
                    $sisa = $sisa - 1500;
                    $total4 = 1500;

                    $html .= "1500 M<sup>2</sup><br>";
                } else {
                    if ($sisa != 0) {
                        $html .= "{$sisa} M<sup>2</sup><br>";
                    } else {
                        $html .= "0.00 M<sup>2</sup><br>";
                    }
                    $total4 = $sisa;
                    $sisa = $sisa - $sisa;
                }
                $total5 = $sisa;

                $html .= number_format($sisa, 2) . " M<sup>2</sup>";




                $html .= "
                                                        </td>
                                                        <td width=\"20\" align=\"center\">
                                                        X<br>
                                                        X<br>
                                                        X<br>
                                                        X<br>
                                                        X
                                                        </td>
                                                        <td width=\"120\" align=\"center\">
                                                        <table>
                                                            <tr>
                                                                <td align=\"right\" width=\"110\">
                                                                Rp. " . number_format($tarif[0], 2) . "<br>
                                                                Rp. " . number_format($tarif[1], 2) . "<br>
                                                                Rp. " . number_format($tarif[2], 2) . "<br>
                                                                Rp. " . number_format($tarif[3], 2) . "<br>
                                                                Rp. " . number_format($tarif[4], 2) . "
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        </td>
                                                        <td width=\"30\" align=\"center\">
                                                            X<br>
                                                            X<br>
                                                            X<br>
                                                            X<br>
                                                            X
                                                        </td>

                                                        <td width=\"60\" align=\"center\">
                                                            20%<br>
                                                            20%<br>
                                                            20%<br>
                                                            20%<br>
                                                            20%
                                                        </td>
                                                ";


                $SUMTotal1 = $total1 * $tarif[0] * 0.2;
                $SUMTotal2 = $total2 * $tarif[1] * 0.2;
                $SUMTotal3 = $total3 * $tarif[2] * 0.2;
                $SUMTotal4 = $total4 * $tarif[3] * 0.2;
                $SUMTotal5 = $total5 * $tarif[4] * 0.2;

                $SANKSITotal1 = $SUMTotal1 * ($persen_sanksi / 100);
                $SANKSITotal2 = $SUMTotal2 * ($persen_sanksi / 100);
                $SANKSITotal3 = $SUMTotal3 * ($persen_sanksi / 100);
                $SANKSITotal4 = $SUMTotal4 * ($persen_sanksi / 100);
                $SANKSITotal5 = $SUMTotal5 * ($persen_sanksi / 100);

                $allTOTAL = $SUMTotal1 + $SUMTotal2 + $SUMTotal3 + $SUMTotal4 + $SUMTotal5;
                $sanksiTOTAL = $SANKSITotal1 + $SANKSITotal2 + $SANKSITotal3 + $SANKSITotal4 + $SANKSITotal5;
                $html .= "
                                                        <td width=\"130\" align=\"right\">
                                                            " . number_format($SUMTotal1, 2) . " <br>
                                                            " . number_format($SUMTotal2, 2) . " <br>
                                                            " . number_format($SUMTotal3, 2) . " <br>
                                                            " . number_format($SUMTotal4, 2) . " <br>
                                                            " . number_format($SUMTotal5, 2) . "
                                                        </td>
                                                        <td width=\"125\" align=\"right\"><br><br><br>" . number_format($sanksiTOTAL, 2) . "</td>
                                                        <td width=\"120\" align=\"right\">
                                                            " . number_format($SUMTotal1 + $SANKSITotal1, 2) . " <br>
                                                            " . number_format($SUMTotal2 + $SANKSITotal2, 2) . " <br>
                                                            " . number_format($SUMTotal3 + $SANKSITotal3, 2) . " <br>
                                                            " . number_format($SUMTotal4 + $SANKSITotal4, 2) . " <br>
                                                            " . number_format($SUMTotal5 + $SANKSITotal5, 2) . "
                                                        </td>
                                                    </tr>

                                            ";
            }

            $html .= "


                                        <tr>
                                            <td colspan=\"10\" align=\"right\"><b>JUMLAH</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td colspan=\"2\" align=\"right\"><b>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</b></td>
                                        </tr>
                                        <tr>
                                            <td colspan=\"12\">
                                                Jumlah dengan huruf:
                                                <i>" . ucfirst($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " rupiah</i>
                                            </td>
                                            </tr>
                                        </table>

                                        <br/><br/>


                                        <table width=\"1015\" border=\"0\" cellpadding=\"3\">
                                            <tr>
                                                <td>
                                                    <table width=\"320\" border=\"0\">
                                                        <tr>
                                                          <td width=\"320\" align=\"center\">
															A.n Kepala Badan Pendapatan Daerah <br/>
															Kabupaten " . ucwords(strtolower($NAMA_PEMERINTAHAN)) . ",<br/>
															Kepala Bidang Pengembangan dan Penetapan <br/>";
            if ($PEJABAT_MENGETAHUI['CPM_KEY'] ===  'KABAN_DIPENDA') {
                $html .=  $PEJABAT_MENGETAHUI['CPM_JABATAN'] . '<br/>';
            }

            $html .= "</td>
                                                        </tr>
                                                        <tr>
                                                          <td><p>&nbsp;</p>
                                                            <p>&nbsp;</p></td>
                                                        </tr>
                                                        <tr>
                                                          <td align=\"center\">
                                                            <strong><u>{$config['KABID_PENDATAAN_NAMA']}</u></strong><br/>
                                                            NIP.{$config['KABID_PENDATAAN_NIP']}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table width=\"470\" border=\"0\">
                                                        <tr>
                                                          <td width=\"100\">Dibuat Tanggal </td>
                                                          <td>: {$DATA['pajak']['CPM_TGL_LAPOR']}</td>
                                                        </tr>
                                                    </table>
                                                </td>

                                            </tr>
                                        </table>
                                    </td>
                                </tr>
							<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>

                            </table>                            ";
        }

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $resolution = array(230, 297);
        $pdf->AddPage('L', $resolution);
        //$pdf->AddPage('L', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 7, 12, 19, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-nota-hitung.pdf', 'I');
    }

    function hitung_npa()
    {
        $query = "SELECT * from PATDA_AIRBAWAHTANAH_NPA WHERE CPM_AKTIF='1' AND CPM_PERUNTUKAN='{$_REQUEST['CPM_PERUNTUKAN']}'";
        $arr_tarif = array();
        $res = mysqli_query($this->Conn, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $arr_tarif[$row['CPM_DEBIT_MIN'] . '-' . $row['CPM_DEBIT_MAX']] = $row['CPM_TARIF'] + 0;
        }

        $input = str_replace(',', '', $_REQUEST['CPM_VOLUME']);
        $total = 0;
        $prev = 0;
        $batas = 0;
        $grandtotal = 0.00;

        $table = '<table border="0" cellspacing="0">
        ';
        $no = 0;
        $alpa = 'a';
        foreach ($arr_tarif as $range => $tarif) {
            $nilai = explode('-', $range);
            $kateg = $nilai[1] > 0 ? number_format($nilai[0]) . ' - ' . number_format($nilai[1]) : '&gt;' . number_format($nilai[0]);
            $sisa = $input - $total;
            if ($sisa > 0 && $nilai[1] > 0) {
                // if($no == 0){
                //     $batas = 0;
                // }else{
                $batas = $nilai[1] - $prev;
                // }

                if ($sisa >= $batas) {
                    $total += $batas;
                    $val = $batas;
                    $sisa = $input - $total;
                } elseif ($sisa > 0 && $sisa < $batas) {
                    $total += $sisa;
                    $val = $sisa;
                    $sisa = $nilai[1] - $total;
                }
                $subtotal = $val * $tarif * (20 / 100);
                $grandtotal += $subtotal;
                $table .= " <tr>
                                <td>$alpa. {$kateg}</td>
                                <td class=r>{$val} M<sup>3</sup></td>
                                <td width=55> X Rp.</td>
                                <td class=r>" . number_format($tarif) . "</td>
                                <td width=10> X </td>
                                <td> 20% = </td>
                                <td class=r>" . number_format($subtotal, 2) . "</td>
                            </tr>";

                $prev = $nilai[1];
            } elseif ($sisa > 0 && $nilai[1] == 0) {
                $val = $sisa;
                $subtotal = $val * $tarif * (20 / 100);
                $grandtotal += $subtotal;
                $table .= " <tr>
                                <td>$alpa. {$kateg}</td>
                                <td class=r>{$val} M<sup>3</sup></td>
                                <td width=55> X Rp.</td>
                                <td class=r>" . number_format($tarif) . "</td>
                                <td width=10> X </td>
                                <td> 20% = </td>
                                <td class=r>" . number_format($subtotal, 2) . "</td>
                            </tr>";
            }
            $no++;
            $alpa++;
            // $grandtotal += $subtotal;
        }
        $table .= '</table>';
        echo json_encode(array('total' => $grandtotal, 'html' => ($grandtotal) ? $table : '0'));
    }

    function print_npa()
    {
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
        $NAMA = $config['KASIE_PENETAPAN_NAMA'];
        $NIP = $config['KASIE_PENETAPAN_NIP'];
        $KABID_NAMA = $config['KABID_PENDATAAN_NAMA'];
        $KABID_NIP = $config['KABID_PENDATAAN_NIP'];

        $TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
        $tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);
        $PEJABAT = $this->get_pejabat();
        $PEJABAT_MENGETAHUI = $PEJABAT[$_POST['PAJAK']['CPM_PEJABAT_MENGETAHUI']];
        $NAMA_PENGELOLAS = strtolower($NAMA_PENGELOLA);

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $DATA['pajak']['CPM_TARIF_PAJAK'] = $DATA['pajak']['CPM_TARIF_PAJAK'] > 0 ? $DATA['pajak']['CPM_TARIF_PAJAK'] : 20;

        $type_masa = $this->get_type_masa();
        $rmw = array(1 => 'I', 'II', 'III', 'IV');
        if ($DATA['pajak']['CPM_TYPE_MASA'] > 30) {
            $m = $DATA['pajak']['CPM_TYPE_MASA'] - 30;
            $masa = $type_masa[3] . ' ' . $rmw[$m];
        } else {
            $masa = $this->arr_bulan[(int)substr($DATA['pajak']['CPM_MASA_PAJAK1'], 3, 2)];
        }

        $persen_sanksi = 0;
        if (($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] + 0) > 0) {
            //$persen_sanksi = ($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK'])*100;
            //$persen_sanksi = round($persen_sanksi/2)*2;
            //$persen_sanksi = round(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK']*100)/2,0)*2;
            //tamabahan
            $tes3 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $tes3;
            $persen_sanksi = round(($tes4 / $tes3) * 100);
        }

        function tgl_indo($tglcetak)
        {
            $bulan = array(
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

            return $pecahkan[2] . '/' . $bulan[(int)$pecahkan[1]] . '/' . $pecahkan[0];
        }
        $tglcetak = date('Y-m-d');
        $tgl_cetak = tgl_indo($tglcetak);

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"0\" cellspacing=\"2\">
                    <tr>
                        <td colspan=\"2\"><table width=\"700\" border=\"0\">
                                <tr>
                                    <th valign=\"top\" align=\"center\">
                                        <font size=\"+1\"><b>NILAI PEROLEHAN AIR<br />
                                        PENGAMBILAN DAN PEMANFAATAN AIR TANAH</b></font>
                                    </th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\">
                            <table width=\"440\" class=\"header\" cellpadding=\"2\" border=\"0\">
                                <tr style=\"font-size:30px\">
                                    <td width=\"130\">Masa</td>
                                    <td width=\"310\" class=\"first\">: {$masa} {$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                                </tr>
                                <tr style=\"font-size:30px\">
                                    <td>Nomor</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_NO']}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\" style=\"font-size:32px\"><b>PENGUSAHA KENA PAJAK (PKP)</b><br>
                        <table width=\"710\" class=\"header\" cellpadding=\"2\" border=\"0\" align=\"left\">
                                <tr style=\"font-size:30px\">
                                    <td width=\"130\">NPWPD</td>
                                    <td width=\"550\" class=\"first\">: {$DATA['profil']['CPM_NPWPD']}</td>
                                </tr>
                                <tr style=\"font-size:30px\">
                                    <td>Nama Perusahaan</td>
                                    <td class=\"first\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr style=\"font-size:30px\">
                                    <td>Jenis Usaha</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_PERUNTUKAN']}</td>
                                </tr>
                                <tr style=\"font-size:30px\">
                                    <td>Alamat</td>
                                    <td class=\"first\">: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr bgcolor=\"#cccccc\">
                        <td width=\"710\" colspan=\"2\" align=\"center\" style=\"font-size:32px\"><strong>JUMLAH NILAI PEROLEHAN AIR</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table cellpadding=\"2\" border=\"0\">";
        $i = 0;
        foreach ($DATA['pajak_atr'] as $atr) {
            $i++;
            $bulanss = $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
            if ($i == 2) {
                $bulanss = $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 1];
            }
            if ($i == 3) {
                $bulanss = $this->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK'] + 2];
            }
            $table = str_replace("class=r", "align=\"right\"", $atr['CPM_ATR_PERHITUNGAN']);
            $table = str_replace('cellspacing="0"', 'cellpadding="3"', $table);
            $table = str_replace('<th>', '<th align="center"><b>', $table);
            $table = str_replace('<th colspan="3" align="right">', '<th colspan="3" align="right"><b>', $table);
            $table = str_replace('<th align="right">', '<th align="right"><b>', $table);
            $table = str_replace('<th>', '<th align="center"><b>', $table);
            $table = str_replace('</th>', '</b></th>', $table);
            $table = str_replace('Total', 'Total Perolehan Air', $table);
            $html .= "<tr>
                                    <td width=\"100\"><font size=\"6\">Bulan</font></td>
                                    <td width=\"10\"><font size=\"6\">:</font></td>
                                    <td width=\"585\"><font size=\"6\">" . $bulanss . " {$DATA['pajak']['CPM_TAHUN_PAJAK']}</font></td>
                                </tr>
                                <tr>
                                    <td><font size=\"6\">Volume Air</font></td>
                                    <td><font size=\"6\">:</font></td>
                                    <td><font size=\"6\">" . number_format($atr['CPM_ATR_VOLUME'], 2) . " m<sup>3</sup></font></td>
                                </tr>
                                <tr>
                                    <td><font size=\"6\"></font></td>
                                    <td><font size=\"6\"></font></td>
                                    <td><font size=\"6\">" . $table . "</font></td>
                                </tr>";
        }
        $html .= "</table></td>
                    </tr>
                    <tr bgcolor=\"#cccccc\">
                        <td width=\"500\" style=\"font-size:28px\"><b>Jumlah Nilai Perolehan Air Tanah</b></td>
                        <td width=\"200\" align=\"right\" style=\"font-size:30px\"><b>" . number_format($DATA['pajak']['CPM_TOTAL_OMZET']) . "</b></td>
                    </tr>
                    <tr bgcolor=\"#cccccc\">
                        <td width=\"500\" style=\"font-size:28px\"><b>Jumlah Denda ({$persen_terlambat_lap}%) x " . round($persen_sanksi / 2) . " Bulan</b></td>
                        <td width=\"200\" align=\"right\" style=\"font-size:30px\"><b>" . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']) . "</b></td>
                    </tr>
                    <tr bgcolor=\"#cccccc\">
                        <td style=\"font-size:30px\"><b>Jumlah Setoran Pajak &nbsp; &nbsp; &nbsp; ({$DATA['pajak']['CPM_TARIF_PAJAK']}%)</b></td>
                        <td align=\"right\" style=\"font-size:28px\"><b>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK']) . "</b></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" style=\"font-size:28px\"><b><i>Terbilang : " . ucwords($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah</i></b></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><br><br>
                        <font size=\"-1\">PESAWARAN, {$tgl_pengesahans}<br>
                       A.n. Kepala " . ucwords($NAMA_PENGELOLAS) . "<br>
                       Kabupaten " . ucwords(strtolower($NAMA_PEMERINTAHAN)) . " <br>
					   Kepala Bidang Pengembangan dan Penetapan <br>";
        if ($PEJABAT_MENGETAHUI['CPM_KEY'] ===  'KABAN_DIPENDA') {
            $html .=  $PEJABAT_MENGETAHUI['CPM_JABATAN'] . '<br/>';
        }

        $html .= "
                       <br><br><br>
                       <b><u>{$PEJABAT_MENGETAHUI['CPM_NAMA']}</u></b><br>
                       NIP. {$PEJABAT_MENGETAHUI['CPM_NIP']}</font>
                        </td>
                    </tr>
				<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
                </table>";


        // echo $html;exit;
        ob_clean();

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(6, 6, 6);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        // $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('npa-airbawahtanah.pdf', 'I');
    }
}
