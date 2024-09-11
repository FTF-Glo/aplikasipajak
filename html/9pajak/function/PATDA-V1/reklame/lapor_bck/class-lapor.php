<?php
/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : print_skpd, print_sspd
2. Perubahan wording Nama Hiburan menjadi Nama Reklame
	- modified by : RDN
	- date : 2017/02/16
	- function : print_sptpd
*/
class LaporPajak extends Pajak {
    #field
    #reklame

    public $id_pajak = 7;
    private $tax_periode = array("none", "harian", "mingguan", "bulanan", "tahunan");
    private $limitYear = 2;
    public $CPM_TYPE_PAJAK = 2;
    protected $CPM_DENDA_TERLAMBAT_LAP;

    public function __construct() {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_masa_pajak($masa = 0, $nilai = 0, $n = 0) {
        if (($masa & 16) && ($n == 1))
            return $nilai;
        if (($masa & 32) && ($n == 2))
            return $nilai;
        if (($masa & 64) && ($n == 3))
            return $nilai;
        if (($masa & 128) && ($n == 4))
            return $nilai;
        return 0;
    }

    public function get_pajak($npwpd='', $nop='') {
		$Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.1.2");
        $pajak_atr = array();
        $list_nop = array();
        
        $query = "
			SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO 
			FROM PATDA_REKLAME_DOC AS DOC WHERE DOC.CPM_ID = '{$this->_id}' LIMIT 0,1";
			
		$result = mysql_query($query, $this->Conn);
        $pajak = $this->get_field_array($result);
       
		//if new entry
        if(empty($pajak['CPM_ID'])){
			$ms = $this->inisialisasi_masa_pajak();
			
			$pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
			$pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
			$pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
			$pajak['CPM_HARGA'] = 0;
			
			$profil = $Op->get_last_profil($npwpd, $nop);
			
			$atr = array(
				'CPM_ATR_JENIS' => '', 
				'CPM_ATR_JUDUL' => '', 
				'CPM_KETERANGAN' => '', 
				'CPM_ATR_LOKASI' => '', 
				'CPM_ATR_TINGGI' => '', 
				'CPM_ATR_LEBAR' => '', 
				'CPM_ATR_MUKA' => '', 
				'CPM_ATR_JARI' => '', 
				'CPM_ATR_JUMLAH' => '', 
				'CPM_ATR_BATAS_AWAL' => '', 
				'CPM_ATR_BATAS_AKHIR' => '', 
				'CPM_ATR_BIAYA' => '', 
				'CPM_ATR_TOTAL' => '', 
				'CPM_ATR_REKENING' => '', 
				'CPM_ATR_TARIF' => '', 
				'CPM_ATR_JUMLAH_TAHUN' => '', 
				'CPM_ATR_JUMLAH_MINGGU' => '',
				'CPM_ATR_JUMLAH_BULAN' => '', 
				'CPM_ATR_JUMLAH_HARI' => '',
				'CPM_ATR_TYPE_MASA' => '', 
				'type_masa' => '', 
				'nmrek' => ''
			);
			$pajak_atr[] = $atr;
			$pajak_atr[] = $atr;
			$pajak_atr[] = $atr;
			
			$list_nop = $Op->get_list_nop($npwpd);
		
		}else{ //if data available
			$profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
			
			$query = "SELECT atr.CPM_ATR_JENIS, atr.CPM_ATR_JUDUL, atr.CPM_ATR_LOKASI, atr.CPM_ATR_TINGGI, atr.CPM_ATR_LEBAR,
			atr.CPM_ATR_MUKA, atr.CPM_ATR_JARI, atr.CPM_ATR_JUMLAH, atr.CPM_ATR_BATAS_AWAL, atr.CPM_ATR_BATAS_AKHIR, atr.CPM_ATR_BIAYA, atr.CPM_ATR_TOTAL, 
			atr.CPM_ATR_REKENING, atr.CPM_ATR_TYPE_MASA, per.nmrek ,atr.CPM_ATR_TARIF, atr.CPM_ATR_JUMLAH_TAHUN,  atr.CPM_ATR_JUMLAH_BULAN,  atr.CPM_ATR_JUMLAH_MINGGU, atr.CPM_ATR_JUMLAH_HARI, per.type_masa, per.nmrek FROM PATDA_REKLAME_DOC AS doc INNER JOIN PATDA_REKLAME_DOC_ATR AS atr ON doc.CPM_ID = atr.CPM_ATR_REKLAME_ID INNER JOIN {$this->PATDA_REK_PERMEN13} AS per ON per.kdrek = atr.CPM_ATR_REKENING WHERE atr.CPM_ATR_REKLAME_ID = '{$this->_id}'";

			$result = mysql_query($query, $this->Conn);
			$x = 0;
			while ($data = mysql_fetch_assoc($result, MYSQL_ASSOC)) {
				$pajak_atr[$x] = $data;
				$x++;
			}
		}
		
		$query = sprintf("SELECT * FROM PATDA_REKLAME_DOC_TRANMAIN WHERE CPM_TRAN_REKLAME_ID = '%s' AND CPM_TRAN_FLAG = '0'", $this->_id);
		$result = mysql_query($query, $this->Conn);
		$tran_date = '';
		if($d = mysql_fetch_assoc($result)){
			$tran_date = $d['CPM_TRAN_CLAIM_DATETIME'];
		}
		
		$pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
		$pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;
        $pajak['CPM_TRAN_CLAIM_DATETIME'] = $tran_date;
        
		$pajak = array_merge($pajak, $arr_rekening);
		
		
		//echo '<pre>',print_r($pajak,true),'</pre>';exit;
		return array(
			'pajak'=>$pajak,
			'profil'=>$profil,
			'pajak_atr'=>$pajak_atr,
			'list_nop'=>$list_nop
		);
    }

    private function get_tarif($id = "") {
        $data = array("CPM_ID" => "", "CPM_PERDA" => "", "CPM_TARIF_PAJAK" => "");

        $where = ($id != "") ? "CPM_ID='{$id}'" : "CPM_AKTIF = '1' AND CPM_JENIS_PAJAK='{$this->id_pajak}'";
        $query = "SELECT * FROM {$this->PATDA_TARIF} a WHERE {$where}";
        $res = mysql_query($query, $this->Conn);
        if ($d = mysql_fetch_assoc($res)) {
            $data['CPM_ID'] = $d['CPM_ID'];
            $data['CPM_TARIF_PAJAK'] = $d['CPM_TARIF_PAJAK'];
            $data['CPM_PERDA'] = $d['CPM_PERDA'];
        }

        return $data;
    }

    public function filtering($id) {
		$html = "<div class=\"filtering\">
                    <form> 
                        <input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  
                        NAMA  : <input type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >  
                        TAHUN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $html.= "<option value='{$th}'>{$th}</option>";
        }
        $html.= "</select> MASA PAJAK : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $html.= "<option value='{$x}'>{$y}</option>";
        }
        $html.= "</select>
                Tanggal Lapor : <input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly class=\"date\" ><input type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\"> s.d
                          <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" readonly class=\"date\" ><input type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                        <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>        
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "reklame";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
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
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data() {
        try {
            $where = "(";
            $where.= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_mod == "pel") { #pelaporan
                if ($this->_s == 0) { #semua data
                    $where = " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
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
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
            $where.= ") ";
            $where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD = '{$_SESSION['npwpd']}' " : "";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj 
                        INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                        WHERE {$where}";
            $result = mysql_query($query, $this->Conn);
            $row = mysql_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pj.CPM_TOTAL_OMZET, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG, 
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                        FROM {$this->PATDA_REKLAME_DOC} pj INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID                            
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
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

    public function grid_table_pelayanan() {
        $DIR = "PATDA-V1";
        $modul = "reklame";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
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

    public function grid_data_pelayanan() {
        try {

            $where = "(";
            $where.= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_s == 0) { #semua data
                $where.= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where.= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where.= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }

            $where.= ") ";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";  

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj 
                            INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                            WHERE {$where}";
            $result = mysql_query($query, $this->Conn);
            $row = mysql_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pj.CPM_TOTAL_OMZET, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG, 
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                            FROM {$this->PATDA_REKLAME_DOC} pj INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID                            
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

    private function last_version() {
        $query = "SELECT * FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_REKLAME_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($res);

        return $data['CPM_TRAN_REKLAME_VERSION'];
    }

    private function validasi_save() {
        return $this->validasi_pajak(1);
    }

    private function validasi_update() {
        return $this->validasi_pajak(0);
    }

    private function validasi_pajak($input = 1) {
        $PAJAK_ATR = $_POST['PAJAK_ATR'];
        $where = ($input == 1) ? "AND pjk.CPM_NO='{$this->CPM_NO}'" : "AND pjk.CPM_NO!='{$this->CPM_NO}'";

        if ($input != 1) {
            $sql = "SELECT STR_TO_DATE(atr.CPM_ATR_BATAS_AWAL,'%d/%m/%Y') AS AWAL,STR_TO_DATE(atr.CPM_ATR_BATAS_AKHIR,'%d/%m/%Y') AS AKHIR, 
					pjk.CPM_ID,pjk.CPM_ID_PROFIL,pjk.CPM_NO,pjk.CPM_NO_SSPD,pro.CPM_NPWPD FROM PATDA_REKLAME_DOC AS pjk 
					INNER JOIN PATDA_REKLAME_DOC_ATR AS atr ON pjk.CPM_ID = atr.CPM_ATR_REKLAME_ID INNER JOIN 
					PATDA_REKLAME_PROFIL AS pro ON pjk.CPM_ID_PROFIL = pro.CPM_ID WHERE 
					atr.CPM_ATR_BATAS_AWAL='{$PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0]}' AND 
					atr.CPM_ATR_BATAS_AKHIR='{$PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0]}' AND 
					CPM_NPWPD = '{$this->CPM_NPWPD}' AND 
					pr.CPM_NOP = '{$this->CPM_NOP}' AND 
					pjk.CPM_NO !='{$this->CPM_NO}'";
            //echo $sql;exit;

            $res = mysql_query($sql, $this->Conn);
            if (mysql_num_rows($res))
                $this->notif = false;
            else
                $this->notif = true;
        }
        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        /* $query = "SELECT pj.CPM_NO, pj.CPM_TAHUN_PAJAK, pj.CPM_MASA_PAJAK, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG 
          FROM PATDA_REKLAME_DOC pj INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
          INNER JOIN PATDA_REKLAME_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
          WHERE (pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND
          pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
          ORDER BY tr.CPM_TRAN_STATUS DESC LIMIT 0,1"; */

        $query = "SELECT STR_TO_DATE(atr.CPM_ATR_BATAS_AWAL,'%d/%m/%Y') AS AWAL,STR_TO_DATE(atr.CPM_ATR_BATAS_AKHIR,'%d/%m/%Y') AS AKHIR,
                pjk.CPM_ID,pjk.CPM_ID_PROFIL,pjk.CPM_NO,pjk.CPM_NO_SSPD,pro.CPM_NPWPD,
                tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG
                
                FROM {$this->PATDA_REKLAME_DOC} AS pjk
                INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} AS atr ON pjk.CPM_ID = atr.CPM_ATR_REKLAME_ID
                INNER JOIN {$this->PATDA_REKLAME_PROFIL} AS pro ON pjk.CPM_ID_PROFIL = pro.CPM_ID 
                INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                
                WHERE STR_TO_DATE('{$PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0]}','%d/%m/%Y') BETWEEN 
                (STR_TO_DATE(atr.CPM_ATR_BATAS_AWAL,'%d/%m/%Y')) AND 
                STR_TO_DATE(atr.CPM_ATR_BATAS_AKHIR,'%d/%m/%Y') AND 
                pro.CPM_NPWPD = '{$this->CPM_NPWPD}' AND 
                pro.CPM_NOP = '{$this->CPM_NOP}' AND 
                atr.CPM_ATR_REKENING='{$PAJAK_ATR['CPM_ATR_REKENING'][0]}' {$where}
                ORDER BY tr.CPM_TRAN_STATUS DESC, pjk.CPM_VERSION DESC LIMIT 0,1";

        //echo $query;exit;

        $res = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($res);

        if ($this->notif == true) {
            if (mysql_num_rows($res)) {
                $this->Message->setMessage("Gagal disimpan, Data termasuk dalam masa pajak <b>{$data['AWAL']} s/d {$data['AKHIR']}</b> yang telah dilaporkan sebelumnya!");
            } elseif ($this->CPM_NO == $data['CPM_NO']) {
                $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
            }
        }

        /* if ($this->notif == true) {
          if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK']) {
          $this->Message->setMessage("Gagal disimpan, Masa pajak pajak <b>{$data['AWAL']}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dilaporkan sebelumnya!");
          } elseif ($this->CPM_NO == $data['CPM_NO']) {
          $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
          }
          } */

        $respon['result'] = mysql_num_rows($res) > 0 ? false : true;
        $respon['result'] = ($input == 0) ? true : $respon['result'];
        $respon['data'] = $data;
		
		//echo '<pre>'.print_r($respon,true).'</pre>';exit;
        return $respon;
    }

    private function toNumber($str) {
        return preg_replace("/([^0-9\\.])/i", "", $str);
    }

    private function save_pajak($cpm_no='') {
        $validasi = $this->validasi_save();

        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4')) {
            $this->Message->clearMessage();

            #update profil baru
            $query = "UPDATE {$this->PATDA_REKLAME_PROFIL} SET CPM_APPROVE ='1' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            mysql_query($query, $this->Conn);
			
			if(empty($cpm_no)){
				#query untuk mengambil no urut pajak            
				$no = $this->get_config_value($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
				$this->CPM_NO = $this->get_config_value($this->_a, "KODE_SPTPD") . str_pad($no, 8, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->id_pajak] . "/" . date("y");
				$this->update_counter($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
			}
			else {
				$this->CPM_NO = $cpm_no;
			}
			
            #insert pajak baru
            $PAJAK_ATR = $_POST['PAJAK_ATR'];
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_LAPOR = date("d-m-Y");
            $this->CPM_TOTAL_OMZET = $this->toNumber($this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = $this->toNumber($this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = $this->toNumber($this->CPM_TARIF_PAJAK);
            $this->CPM_NO_SSPD = ($this->CPM_NOP == '-')? substr($this->CPM_NO, 0, 9) : substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
            $this->CPM_MASA_PAJAK1 = $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0];
            $this->CPM_MASA_PAJAK2 = $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0];

            $this->CPM_DENDA_TERLAMBAT_LAP = $this->toNumber($this->CPM_DENDA_TERLAMBAT_LAP);

            $query = sprintf("INSERT INTO {$this->PATDA_REKLAME_DOC} 
                    (CPM_ID, CPM_ID_PROFIL, CPM_NO, CPM_MASA_PAJAK, CPM_TAHUN_PAJAK, CPM_TOTAL_OMZET, CPM_TOTAL_PAJAK, CPM_TARIF_PAJAK,
                    CPM_TGL_LAPOR,CPM_KETERANGAN, CPM_VERSION, CPM_AUTHOR, CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD, CPM_JNS_MASA_PAJAK, 
                    CPM_MASA_PAJAK1, CPM_MASA_PAJAK2, CPM_SK_DISCOUNT, CPM_DISCOUNT, CPM_TYPE_PAJAK, CPM_DENDA_TERLAMBAT_LAP)
                    VALUES ( '%s','%s','%s',%f,%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%f,%d,%f)", 
                    $this->CPM_ID, $this->CPM_ID_PROFIL, $this->CPM_NO, $this->CPM_MASA_PAJAK, 
                    $this->CPM_TAHUN_PAJAK, $this->CPM_TOTAL_OMZET, $this->CPM_TOTAL_PAJAK, $this->CPM_TARIF_PAJAK, $this->CPM_TGL_LAPOR, 
                    $this->CPM_KETERANGAN, $this->CPM_VERSION, $this->CPM_AUTHOR, $this->CPM_TOTAL_PAJAK, $this->CPM_TOTAL_PAJAK, $this->CPM_NO_SSPD, 
                    $this->CPM_JNS_MASA_PAJAK, $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0], $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0], $this->CPM_SK_DISCOUNT, 
                    $this->CPM_DISCOUNT, $this->CPM_TYPE_PAJAK, $this->CPM_DENDA_TERLAMBAT_LAP);

            //echo $query;print_r($this);exit;
            $res = mysql_query($query, $this->Conn);
            if ($res) {
                $j = count($PAJAK_ATR['CPM_ATR_JUDUL']);
                for ($x = 0; $x < $j; $x++) {
                    $judul = mysql_escape_string($PAJAK_ATR['CPM_ATR_JUDUL'][$x]);
                    $lebar = $this->toNumber($PAJAK_ATR['CPM_ATR_LEBAR'][$x]);
                    $tinggi = $this->toNumber($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
                    $muka = $this->toNumber($PAJAK_ATR['CPM_ATR_MUKA'][$x]);
                    $jari = "";#$this->toNumber($PAJAK_ATR['CPM_ATR_JARI'][$x]);
                    $total = $this->toNumber($PAJAK_ATR['CPM_ATR_TOTAL'][$x]);
                    $biaya = $this->toNumber($PAJAK_ATR['CPM_ATR_BIAYA'][$x]);
                    $norekening = mysql_escape_string($PAJAK_ATR['CPM_ATR_REKENING'][$x]);
                    $jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
                    $tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_TARIF'][$x]);
                    $jumlah_tahun = isset($PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN'][$x] : 0;
                    $jumlah_hari = isset($PAJAK_ATR['CPM_ATR_JUMLAH_HARI']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_HARI'][$x] : 0;
                    $jumlah_minggu = isset($PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU'][$x] : 0;
                    $jumlah_bulan = isset($PAJAK_ATR['CPM_ATR_JUMLAH_BULAN']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_BULAN'][$x] : 0;
                    $jenis = "";
                    $lokasi = mysql_escape_string($PAJAK_ATR['CPM_ATR_LOKASI'][$x]);
                    $batas_awal = mysql_escape_string($PAJAK_ATR['CPM_ATR_BATAS_AWAL'][$x]);
                    $batas_akhir = mysql_escape_string($PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][$x]);
                    $type_masa = mysql_escape_string($PAJAK_ATR['CPM_ATR_TYPE_MASA'][$x]); 

                    $query = sprintf("INSERT INTO {$this->PATDA_REKLAME_DOC_ATR} 
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_TINGGI, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA)
                            VALUES ('%s','%s',%f,'%s',
                                    %f,%f,%f,%f,%f,
                                    %f,'%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s')", $this->CPM_ID, $judul, $biaya, $lokasi, $lebar, $tinggi, $jumlah, $jari, $muka, $tarif, $jumlah_tahun, $jumlah_hari, $jumlah_minggu, $jumlah_bulan, $batas_awal, $batas_akhir, $total, $norekening, $type_masa
                    );
                    mysql_query($query, $this->Conn);
                }
            }
            return $res;
        }
        return false;
    }

    private function save_tranmain($param) {
        #insert tranmain 
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_REKLAME_ID = $this->CPM_ID;

        $query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_REKLAME_ID = '{$CPM_TRAN_REKLAME_ID}'";
        $res = mysql_query($query, $this->Conn);

        $query = sprintf("INSERT INTO {$this->PATDA_REKLAME_DOC_TRANMAIN} 
                    (CPM_TRAN_ID, CPM_TRAN_REKLAME_ID, CPM_TRAN_REKLAME_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE, 
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')", $CPM_TRAN_ID, $CPM_TRAN_REKLAME_ID, $param['CPM_TRAN_REKLAME_VERSION'], $param['CPM_TRAN_STATUS'], $param['CPM_TRAN_FLAG'], $param['CPM_TRAN_DATE'], $param['CPM_TRAN_OPR'], $param['CPM_TRAN_OPR_DISPENDA'], $param['CPM_TRAN_INFO']
        );
        #echo $query;exit();
        return mysql_query($query, $this->Conn);
    }

    public function save() {
        $this->CPM_VERSION = "1";
        if ($this->save_pajak()) {
            $param = array();
            $param['CPM_TRAN_REKLAME_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            
            if($res = $this->save_tranmain($param)){
				$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
			}else{
				$_SESSION['_error'] = 'Data Pajak gagal disimpan';
			}
        }
    }

    public function save_final() {
        $this->CPM_VERSION = "1";
        if ($this->save_pajak()) {
            $param['CPM_TRAN_REKLAME_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            
            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if($res){
				$_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
			}else{
				$_SESSION['_error'] = 'Data Pajak gagal difinalkan';
			}
        }
    }

    public function new_version() {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_REKLAME_ID='{$id}'";
            mysql_query($query, $this->Conn);

            $param['CPM_TRAN_REKLAME_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            
            if($res = $this->save_tranmain($param)){
				$_SESSION['_success'] = 'Data Pajak versi '.$new_version.' berhasil disimpan';
			}else{
				$_SESSION['_error'] = 'Data Pajak '.$new_version.' gagal disimpan';
			}
        }
    }

    public function new_version_final() {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_REKLAME_ID='{$id}'";
            mysql_query($query, $this->Conn);

            $param['CPM_TRAN_REKLAME_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $this->save_tranmain($param);
            
            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if($res){
				$_SESSION['_success'] = 'Data Pajak versi '.$new_version.' berhasil difinalkan';
			}else{
				$_SESSION['_error'] = 'Data Pajak '.$new_version.' gagal difinalkan';
			}
        }
    }

    public function update_final() {
        $this->CPM_VERSION = $this->last_version();
        if ($this->update()) {
            $param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
            
            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if($res){
				$_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
			}else{
				$_SESSION['_error'] = 'Data Pajak gagal difinalkan';
			}
        }
    }

    public function update() {
        $validasi = $this->validasi_update();

        if ($validasi['result'] == true) {
			$this->Message->clearMessage();
			
			$this->CPM_TOTAL_OMZET = $this->toNumber($this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = $this->toNumber($this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = $this->toNumber($this->CPM_TARIF_PAJAK);
            $this->CPM_DENDA_TERLAMBAT_LAP = $this->toNumber($this->CPM_DENDA_TERLAMBAT_LAP);

            // $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = $this->toNumber($this->CPM_TOTAL_PAJAK);
            $this->CPM_BAYAR_TERUTANG = $this->toNumber($this->CPM_TOTAL_PAJAK);
            $PAJAK_ATR = $_POST['PAJAK_ATR'];
            $query = sprintf("UPDATE {$this->PATDA_REKLAME_DOC} SET
                    CPM_TOTAL_OMZET = %f,
                    CPM_TOTAL_PAJAK = %f,
                    CPM_TARIF_PAJAK = %f,
                    CPM_DPP = %f,
                    CPM_BAYAR_TERUTANG = %f,
                    CPM_KETERANGAN = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s',
                    CPM_PERPANJANG = '%s',
                    CPM_DENDA_TERLAMBAT_LAP = %f,
                    CPM_NO_SSPD_SBLM = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_SK_DISCOUNT = '%s',
                    CPM_DISCOUNT = %f
                    WHERE 
                    CPM_ID ='{$this->CPM_ID}'", $this->CPM_TOTAL_OMZET, $this->CPM_TOTAL_PAJAK, $this->CPM_TARIF_PAJAK, $this->CPM_DPP, $this->CPM_BAYAR_TERUTANG, $this->CPM_KETERANGAN, $this->CPM_MASA_PAJAK1, $this->CPM_MASA_PAJAK2, $this->CPM_TAHUN_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_PERPANJANG, $this->CPM_DENDA_TERLAMBAT_LAP, $this->CPM_NO_SSPD_SBLM, $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0], $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0], $this->CPM_SK_DISCOUNT, $this->CPM_DISCOUNT,$this->CPM_DENDA_TERLAMBAT_LAP);
            //echo $query;exit();
            mysql_query($query, $this->Conn);


            $j = count($PAJAK_ATR['CPM_ATR_JUDUL']);
            for ($x = 0; $x < $j; $x++) {
                $judul = mysql_escape_string($PAJAK_ATR['CPM_ATR_JUDUL'][$x]);
                $lebar = $this->toNumber($PAJAK_ATR['CPM_ATR_LEBAR'][$x]);
                $tinggi = $this->toNumber($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
                $muka = $this->toNumber($PAJAK_ATR['CPM_ATR_MUKA'][$x]);
                $jari = $this->toNumber($PAJAK_ATR['CPM_ATR_JARI'][$x]);
                $total = $this->toNumber($PAJAK_ATR['CPM_ATR_TOTAL'][$x]);
                $biaya = $this->toNumber($PAJAK_ATR['CPM_ATR_BIAYA'][$x]);
                $norekening = mysql_escape_string($PAJAK_ATR['CPM_ATR_REKENING'][$x]);
                $type_masa = mysql_escape_string($PAJAK_ATR['CPM_ATR_TYPE_MASA'][$x]);
                $jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
                $tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_TARIF'][$x]);
                $jumlah_tahun = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN'][$x]);
                $jumlah_hari = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_HARI'][$x]);
                $jumlah_minggu = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU'][$x]);
                $jumlah_bulan = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_BULAN'][$x]);

                $batas_awal = mysql_escape_string($PAJAK_ATR['CPM_ATR_BATAS_AWAL'][$x]);
                $batas_akhir = mysql_escape_string($PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][$x]);

                $query = sprintf("UPDATE {$this->PATDA_REKLAME_DOC_ATR} SET CPM_ATR_JUDUL='%s', CPM_ATR_BIAYA='%s',
					CPM_ATR_LEBAR='%s', CPM_ATR_TINGGI='%s', CPM_ATR_JUMLAH='%s',CPM_ATR_JARI='%s',
					CPM_ATR_MUKA='%s', CPM_ATR_TARIF='%s', CPM_ATR_JUMLAH_TAHUN='%s', CPM_ATR_JUMLAH_HARI='%s', 
					CPM_ATR_JUMLAH_MINGGU='%s', CPM_ATR_JUMLAH_BULAN='%s', CPM_ATR_BATAS_AWAL='%s', CPM_ATR_BATAS_AKHIR='%s', 
					CPM_ATR_TOTAL='%s', CPM_ATR_REKENING='%s', CPM_ATR_TYPE_MASA='%s'  WHERE CPM_ATR_REKLAME_ID='%s'
					", $judul, $biaya, $lebar, $tinggi, $jumlah, $jari, $muka, $tarif, $jumlah_tahun, $jumlah_hari, $jumlah_minggu, $jumlah_bulan, $batas_awal, $batas_akhir, $total, $norekening, $type_masa, $this->CPM_ID
                );
                //echo $query;exit();
                return mysql_query($query, $this->Conn);
            }
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM {$this->PATDA_REKLAME_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysql_query($query, $this->Conn);
        if ($res) {
            $query = "DELETE FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_REKLAME_ID ='{$this->CPM_ID}'";
            mysql_query($query, $this->Conn);
        }
    }

    public function verifikasi() {
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
            //echo $query;exit;
            $res = mysql_query($query, $this->Conn);
            if (mysql_num_rows($res) == 0) {
				$msg = "Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
                return false;
            }
        }
        $this->persetujuan();

        #validasi hanya satu tahap yaitu verifikasi saja
        /* $status = ($this->AUTHORITY == 1) ? 3 : 4;
          $param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
          $param['CPM_TRAN_STATUS'] = $status;
          $param['CPM_TRAN_FLAG'] = "0";
          $param['CPM_TRAN_DATE'] = date("d-m-Y");
          $param['CPM_TRAN_OPR'] = "";
          $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
          $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
          $this->save_tranmain($param); */
    }

    public function persetujuan() {
        $status = ($this->AUTHORITY == 1) ? 5 : 4;
        $param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
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
            $res = $this->save_gateway($this->id_pajak, $arr_config);
            
            if($res){
				$this->update_jatuh_tempo($this->EXPIRED_DATE);
				$_SESSION['_success'] = 'Data Pajak berhasil disetujui';
			}else{
				$_SESSION['_error'] = 'Data Pajak gagal disetujui';
			}
        }
    }

    private function update_jatuh_tempo($expired_date) {
        $query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysql_query($query, $this->Conn);
    }

    public function print_skpd_base() {
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
        $BAG_VERIFIKASI_NAMA = $config['KEPALA_DINAS_NAMA'];
        $NIP = $config['KEPALA_DINAS_NIP'];
        $BANK = $config['BANK'];
        $BADAN = $config['NAMA_BADAN_PENGELOLA'];
        
        //echo '<pre>',print_r($DATA),'</pre>';exit;
        $html = "<table width=\"710\" border=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"220\"><p><strong>".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
					".strtoupper($NAMA_PENGELOLA)."<br/>
					{$JALAN}<br/>
					{$KOTA} - {$PROVINSI} {$KODE_POS}</strong></p></td>
					<td width=\"330\" align=\"center\"><p><strong>SURAT KETETAPAN PAJAK DAERAH<br/>PAJAK REKLAME</strong></p>
					  <table width=\"310\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" align=\"left\">
						<tr>
						  <td width=\"100\">MASA PAJAK</td>
						  <td width=\"190\">: {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} - {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}</td>
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
					<td colspan=\"4\"><table border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
					  <tr>
						<td width=\"248\">NAMA</td>
						<td width=\"430\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
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
						<td>: {$DATA['profil']['CPM_NPWPD']}</td>
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
						<td align=\"left\">
							{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}
							{$DATA['pajak_atr'][0]['nmrek']}
						</td>
						<td>Judul Reklame : {$DATA['pajak_atr'][0]['CPM_ATR_JUDUL']},<br/>\n
                        Lokasi : {$DATA['pajak_atr'][0]['CPM_ATR_LOKASI']},<br/>\n
                        Panjang : {$DATA['pajak_atr'][0]['CPM_ATR_TINGGI']} m, 
                        Lebar : {$DATA['pajak_atr'][0]['CPM_ATR_LEBAR']} m, 
                        Muka :  ".number_format($DATA['pajak_atr'][0]['CPM_ATR_MUKA'],0).",<br/>
						Ukuran : " . ($DATA['pajak_atr'][0]['CPM_ATR_TINGGI'] * $DATA['pajak_atr'][0]['CPM_ATR_LEBAR']) . " m<sup>2</sup>, 
						Jumlah : ".number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'],0).",
						Lama : {$DATA['pajak']['CPM_MASA_PAJAK']} {$DATA['pajak']['CPM_JNS_MASA_PAJAK']},<br/>
						Periode : {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} s/d {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}</td>
						<td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
					  </tr>
					  <tr>
						<td align=\"right\">2.</td>
						<td align=\"center\"></td>
						<td>Denda Keterlambatan Pelaporan</td>
						<td align=\"right\">" . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                      </tr>
					  <tr>
						<td colspan=\"3\" align=\"center\">Jumlah Ketetapan Pokok Pajak</td>
						<td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
						</tr>
					</table></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><i>" . ucwords($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah</i></td>
				  </tr>
				  <tr>
					<td colspan=\"4\"><table width=\"100%\" border=\"0\">
					  <tr>
						<td colspan=\"2\"><strong><u>P E R H A T I A N</u></strong></td>
					  </tr>
					  <tr>
						<td width=\"4%\" align=\"right\">1.</td>
						<td width=\"96%\">Harapan penyetoran dilakukan pada Bendahara Dinas Pendapatan Daerah / ".ucwords(strtolower($BANK))." dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
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
					  <td width=\"289\" align=\"center\">{$KOTA}, " . $DATA['pajak']['CPM_TGL_LAPOR'] . "<br/>KEPALA BADAN PENDAPATAN<br/>DAERAH ".strtoupper($KOTA)."<br/></td>
					</tr>
					<tr>
					  <td><p>&nbsp;</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p></td>
					</tr>
					<tr>
					  <td align=\"center\"><strong><u>{$BAG_VERIFIKASI_NAMA}</u></strong><br/>
						NIP.{$NIP}</td>
					</tr>
				  </table></td></tr></table></td>
				  </tr>
				</table>
				";

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

        $pdf->Output('skpd-reklame.pdf', 'I');
    }

	public function print_skpd() {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak($this->CPM_ID);
        $data = $DATA['pajak'];
        $profil = $DATA['profil'];
        $pajak_atr = $DATA['pajak_atr'];
        
        $DATA = array_merge($data, $profil);
        $DATA['pajak_atr'] = $pajak_atr;
        $arr_rekening = $this->getRekening();
        
        //echo '<pre>',print_r($arr_rekening,true),'</pre>';exit;
        
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbTable = $arr_config['PATDA_TABLE'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $Conn_gw = mysql_connect($dbHost, $dbUser, $dbPwd, true);
        mysql_select_db($dbName, $Conn_gw);
        $query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
        $res = mysql_query($query, $Conn_gw);
        if($d = mysql_fetch_assoc($res)){
			$DATA['CPM_TGL_JATUH_TEMPO'] = $d['expired_date'];
		}
        
        //$rek = $this->getRekening($DATA['CPM_GOL_'.$DATA['TYPE']]);
        //$DATA = array_merge($DATA, $rek);
        //echo "<pre>".print_r($_REQUEST, true)."</pre>";
        //echo "<pre>".print_r($DATA, true)."</pre>";exit;

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
        
		$pemerintah = explode(' ',$JENIS_PEMERINTAHAN);
		$pemerintah_label = strtoupper($pemerintah[0]);
		$pemerintah_jenis = strtoupper($pemerintah[1]); 
		
		$DATA['pajak_atr'] = $DATA['pajak_atr'][0];
		unset($DATA['pajak_atr'][0]);
		
		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"710\" valign=\"top\" align=\"center\" colspan=\"3\">
										<b style=\"font-size:40px\">
										".strtoupper($JENIS_PEMERINTAHAN)." ".strtoupper($NAMA_PEMERINTAHAN)."<br/>
										BADAN PENGELOLAAN PENDAPATAN DAERAH<br/>
										{$JALAN}<br/>
										{$KOTA} - {$PROVINSI}
										</b>
                                    </td>
                                </tr>
                                <tr>
                                    
                                    <td width=\"510\" valign=\"top\" align=\"center\">
										<b style=\"font-size:35px\">
										SURAT KETETAPAN PAJAK DAERAH<br/>
										TAHUN PAJAK : {$DATA['CPM_TAHUN_PAJAK']}<br/>
										MASA : {$DATA['CPM_MASA_PAJAK1']} - {$DATA['CPM_MASA_PAJAK2']}
										</b>
                                    </td>
                                    
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/><br/>
										Nomor Kohir : <br/>
										{$DATA['CPM_NO']}<br/>
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
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"150\">NAMA</td>
												<td width=\"230\">: {$DATA['CPM_NAMA_OP']}</td>
											</tr>
											<tr>
												<td>ALAMAT</td>
												<td>: {$DATA['CPM_ALAMAT_OP']}</td>
											</tr>
											<tr>
												<td>N.P.W.P.D</td>
												<td>: ".Pajak::formatNPWPD($DATA['CPM_NPWPD'])."</td>
											</tr>
											<tr>
												<td></td>
												<td></td>
											</tr>
										</table>
									</td>
									<td width=\"310\"><table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"160\"></td>
												<td width=\"145\"></td>
											</tr>
											<tr>
												<td></td>
												<td></td>
											</tr>
											<tr>
												<td></td>
												<td></td>
											</tr>
											<tr>
												<td>TANGGAL JATUH TEMPO</td>
												<td>: {$DATA['CPM_TGL_JATUH_TEMPO']}</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
                    </tr>
                    
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
							<tr>
								<td colspan=\"2\">
									&nbsp;&nbsp;&nbsp;&nbsp;<table width=\"500\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\">                                            
										<tr style=\"background-color:#CCC\">
											<td width=\"30\" align=\"center\">NO.</td>
											<td width=\"150\" align=\"center\">KODE REKENING</td>
											<td width=\"300\" align=\"center\">JENIS PAJAK DAERAH</td>
											<td width=\"200\" align=\"center\">JUMLAH</td>
										</tr>";
									
									$total_npa = 0;
									$total_volume = 0;
									
									//echo '<pre>',print_r($DATA,true),'</pre>';exit;
									
									$html = "<tr>
											<td align=\"center\">1.</td>
											<td align=\"left\" >
												{$DATA['pajak_atr']['CPM_ATR_REKENING']}<br/><br/>
												<table border=\"0\" width=\"600\">
													<tr>
														<td width=\"40\">P</td>
														<td width=\"5\">:</td>
														<td width=\"90\" align=\"right\">{$DATA['pajak_atr']['CPM_ATR_TINGGI']} m</td>
													</tr>
													<tr>
														<td>L</td>
														<td>:</td>
														<td align=\"right\">{$DATA['pajak_atr']['CPM_ATR_LEBAR']} m</td>
													</tr>
													<tr>
														<td>MK</td>
														<td>:</td>
														<td align=\"right\">{$DATA['pajak_atr']['CPM_ATR_MUKA']} Muka</td>
													</tr>
												</table>
											</td>
											<td align=\"left\">
												<table border=\"0\" width=\"600\">
													<tr>
														<td width=\"60\">Jenis</td>
														<td width=\"235\">: {$arr_rekening['ARR_REKENING'][$DATA['pajak_atr']['CPM_ATR_REKENING']]}</td>
													</tr>
													<tr>
														<td>Lokasi</td>
														<td>: {$DATA['pajak_atr']['CPM_ATR_LOKASI']}</td>
													</tr>
													<tr>
														<td>Judul</td>
														<td>: {$DATA['pajak_atr']['CPM_ATR_JUDUL']}</td>
													</tr>
													<tr>
														<td>Periode</td>
														<td>: {$DATA['pajak_atr']['CPM_ATR_BATAS_AWAL']} s/d {$DATA['pajak_atr']['CPM_ATR_BATAS_AKHIR']}</td>
													</tr>
													<tr>
														<td colspan=\"0\">Jumlah NJOP + Nilai Strategis</td>
													</tr>
												</table>
											</td>
											<td align=\"right\"><br/><br/><br/><br/><br/>
												".number_format($DATA['pajak_atr']['CPM_ATR_JUMLAH'],0)." buah
											</td>
										</tr>
										<tr>
											<td align=\"center\" rowspan=\"2\" colspan=\"2\"></td>
											<td align=\"left\">
												<table border=\"0\" width=\"600\">
													<tr>
														<td width=\"290\" colspan=\"2\">
														Jumlah Ketetapan<br/>
														Discount / Kenaikan
														</td>
													</tr>
												</table>
											</td>
											<td align=\"right\">
												" . number_format($DATA['CPM_TOTAL_PAJAK'], 2) . "<br/>
												" . number_format($DATA['CPM_DISCOUNT'], 2) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												J U M L A H &nbsp;&nbsp;K E S E L U R U H A N
											</td>
											<td align=\"right\">
												" . number_format($DATA['CPM_TOTAL_PAJAK'], 2) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\" colspan=\"4\">
											Jumlah dengan huruf :<br/>
											".ucfirst($DATA['CPM_TERBILANG'])."
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
								<td><table width=\"100%\" border=\"0\" align=\"left\">
										<tr>
											<td><b>PERHATIAN : </b></td>
										</tr> 
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap Penyetoran dilakukan pada Kas Daerah atau tempat lain yang ditunjuk (Bendahara Penerimaan) dengan<br/>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
										</tr> 
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Apabila SKPDN ini tidak atau Kurang dibayar setelah lewat waktu paling lama 30 hari semenjak SKPDN ini diterima atau<br/>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											tanggal jatuh tempo dikenakan sanksi anministrasi berupa bunga 2% per bulan.</td>
										</tr> 
									</table>
									</td>
								</tr>
							</table>
                        </td>
                    </tr> 
					<tr>
						<td colspan=\"2\" align=\"center\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
								<tr>
									<td width=\"355\"></td>
									<td align=\"center\">
										{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
										a.n. KEPALA BADAN PENGELOLAAN PENDAPATAN<br/>
										KABID BINA POTENSI PAJAK & RETRIBUSI<br/><br/><br/>
										<u>{$DATA['CPM_NAMA_WP']}</u>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
								<tr>
									<td><table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">                                            
											<tr>
												<td colspan=\"3\">
													<table width=\"500\" cellpadding=\"3\" border=\"0\" cellspacing=\"0\">                                            
														<tr>
															<td width=\"150\">Tanggal Proses</td>
															<td>: {$DATA['CPM_TRAN_CLAIM_DATETIME']}</td>
														</tr>
													</table>
													<table width=\"500\" cellpadding=\"3\" border=\"0\" cellspacing=\"0\">                                            
														<tr>
															<td width=\"480\"></td>
															<td width=\"90\">No. KOHIR</td>
															<td width=\"125\">: {$DATA['CPM_NO']}</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td colspan=\"2\" width=\"700\" align=\"center\">TANDA TERIMA</td>
											</tr>
											<tr>
												<td width=\"150\">Nama</td>
												<td width=\"260\" colspan=\"2\">: {$DATA['CPM_NAMA_OP']}</td>
											</tr>
											<tr>
												<td width=\"150\">Alamat</td>
												<td width=\"260\" colspan=\"2\">: {$DATA['CPM_ALAMAT_OP']}</td>
											</tr>
											<tr>
												<td width=\"150\">NPWPD</td>
												<td width=\"260\" colspan=\"2\">: ".Pajak::formatNPWPD($DATA['CPM_NPWPD'])."</td>
											</tr>
											<tr>
												<td width=\"410\" colspan=\"2\"></td>
												<td width=\"240\" align=\"center\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
												Yang Menerima,<br/><br/></td>
											</tr>
											<tr>
												<td width=\"150\"></td>
												<td width=\"260\"></td>
												<td width=\"240\">( .............................................................. )</td>
											</tr>
										</table>
									</td>
									<td>
										
									</td>
								</tr>
							</table>
						</td>
					</tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 8, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($page1, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 21, 9, 25, '', '', '', '', false, 300, '', false);
        
		$pdf->Output('skpd-reklame.pdf', 'I');
    }
    
    public function print_sptpd_base() {
        
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
        $BANK = $config['BANK'];

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"700\" border=\"0\">
                                <tr>
                                    <th valign=\"top\" align=\"center\">                                   
                                        ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        ".strtoupper($NAMA_PENGELOLA)."<br /><br />        
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
                                            PAJAK REKLAME
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
                            Yth. Kepala Badan Pendapatan Daerah<br/>
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
                                    <td>&nbsp;&nbsp;&nbsp;Nama Reklame</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;NPWPD</td>
                                    <td>: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
                                </tr>
                            </table>            
                        </td>
                    </tr>  
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"left\"><strong>II. DIISI OLEH PENGUSAHA REKLAME</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\">
							<table width=\"100%\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
							  <tr>
								<td width=\"5%\" align=\"center\"><strong>NO</strong></td>
								<td width=\"30%\" align=\"center\"><strong>Jenis Reklame dan Judul</strong></td>
								<td width=\"20%\" align=\"center\"><strong>Lokasi</strong></td>
								<td width=\"15%\" align=\"center\"><strong>Ukuran</strong></td>
								<td width=\"15%\" align=\"center\"><strong>Jumlah</strong></td>
								<td width=\"15%\" align=\"center\"><strong>Jangka Waktu</strong></td>
							  </tr>
							  <tr>
								<td align=\"right\">1.</td>
								<td align=\"left\">
									{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}<br/>\n
									{$DATA['pajak_atr'][0]['nmrek']}<br/>\n
									".strtoupper($DATA['pajak_atr'][0]['CPM_ATR_JUDUL'])."<br/>\n
								</td>
								<td>{$DATA['pajak_atr'][0]['CPM_ATR_LOKASI']}</td>
								<td>
									Panjang : {$DATA['pajak_atr'][0]['CPM_ATR_TINGGI']} M <br/>\n
									Lebar : {$DATA['pajak_atr'][0]['CPM_ATR_LEBAR']} M <br/>\n
									Muka :  ".number_format($DATA['pajak_atr'][0]['CPM_ATR_MUKA'],0)." <br/>\n
								</td>
								<td>".number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'],0)."</td>
								<td>
									{$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} <br/>s/d<br/> {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}
								</td>
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

        $pdf->Output('sptpd-hiburan.pdf', 'I');
    }


    public function print_sptpd() {
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
		
		$pemerintah = explode(' ',$JENIS_PEMERINTAHAN);
		$pemerintah_label = strtoupper($pemerintah[0]);
		$pemerintah_jenis = strtoupper($pemerintah[1]); 
		
		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/>
										<br/><br/>
										<br/>
                                        <b>".$pemerintah_label."<br/>" . $pemerintah_jenis.' '.strtoupper($NAMA_PEMERINTAHAN)."</b>
                                    </td>
                                    <td width=\"310\" valign=\"top\" align=\"center\">
										<b style=\"font-size:55px\">S P T P D</b><br/>
                                        (SURAT PEMBERITAHUAN PAJAK DAERAH)
                                        <b style=\"font-size:50px\">PAJAK REKLAME</b><br/>
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
												<td width=\"100\">N.P.W.P.D</td>
												<td width=\"280\">: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
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
												Kepala Badan Pengeloaan Pendapatan Daerah<br/>
												".ucfirst(strtolower($JENIS_PEMERINTAHAN))." ".ucfirst(strtolower($NAMA_PEMERINTAHAN))."<br/>
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
							<tr>
								<td><table width=\"100%\" border=\"0\" align=\"left\">
										<tr>
											<td>PERHATIAN : </td>
										</tr> 
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap diisi dalam rangkap dua (2) ditulis dengan huruf <b>CETAK</b> </td>
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
											&nbsp;&nbsp;Desa/Kelurahan : {$DATA['profil']['CPM_NAMA_KELURAHAN_OP']}<br/>
											&nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_NAMA_KECAMATAN_OP']}<br/>
											&nbsp;&nbsp;Telepon : {$DATA['profil']['CPM_TELEPON_OP']}<br/>
											</td>
										</tr>
										<tr>
											<td>C. NAMA WAJIB PAJAK</td>
											<td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
										</tr>
										<tr>
											<td>D. ALAMAT WAJIB PAJAK</td>
											<td>: {$DATA['profil']['CPM_ALAMAT_WP']}<br/>
											&nbsp;&nbsp;Desa/Kelurahan : {$DATA['profil']['CPM_KELURAHAN_WP']}<br/>
											&nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_KECAMATAN_WP']}<br/>
											&nbsp;&nbsp;Telepon : {$DATA['profil']['CPM_TELEPON_WP']}
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
					<tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\">
							<table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"0\" cellspacing=\"0\">
								<tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;a.</td>
                                    <td align=\"left\" width=\"270\">Data Objek Pajak</td>
                                    <td width=\"30\"></td>
                                    <td align=\"right\" width=\"390\"></td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\"></td>
                                    <td align=\"left\" width=\"270\" colspan=\"3\">
										<table width=\"680\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
										  <tr>
											<td width=\"5%\" align=\"center\"><strong>NO</strong></td>
											<td width=\"30%\" align=\"center\"><strong>Jenis Reklame dan Judul</strong></td>
											<td width=\"20%\" align=\"center\"><strong>Lokasi</strong></td>
											<td width=\"15%\" align=\"center\"><strong>Ukuran</strong></td>
											<td width=\"15%\" align=\"center\"><strong>Jumlah</strong></td>
											<td width=\"15%\" align=\"center\"><strong>Jangka Waktu</strong></td>
										  </tr>
										  <tr>
											<td align=\"right\">1.</td>
											<td align=\"left\">
												{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}<br/>\n
												{$DATA['pajak_atr'][0]['nmrek']}<br/>\n
												".strtoupper($DATA['pajak_atr'][0]['CPM_ATR_JUDUL'])."<br/>\n
											</td>
											<td>{$DATA['pajak_atr'][0]['CPM_ATR_LOKASI']}</td>
											<td>
												Panjang : {$DATA['pajak_atr'][0]['CPM_ATR_TINGGI']} M <br/>\n
												Lebar : {$DATA['pajak_atr'][0]['CPM_ATR_LEBAR']} M <br/>\n
												Muka :  ".number_format($DATA['pajak_atr'][0]['CPM_ATR_MUKA'],0)." <br/>\n
											</td>
											<td>".number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'],0)."</td>
											<td>
												{$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} <br/>s/d<br/> {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}
											</td>
										  </tr>
										</table><br/>
                                    </td>
                                </tr>
                            </table><br/>
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
									<td colspan=\"2\">
										Demikian formulir ini diisi dengan sebenar-benarnya, dan apabila ada ketidakbenaran dalam melakukan kewajiban
										pengisian SPTPD ini, saya bersedia diberikan sanksi sesuai Peraturan Daerah yang berlaku.
									</td>
								</tr>
								<tr>
									<td width=\"355\"></td>
									<td align=\"center\">
										{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
										Wajib Pajak<br/><br/><br/><br/>
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
												<td width=\"260\" colspan=\"2\">:</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Petugas</td>
												<td width=\"260\" colspan=\"2\">: {$BAG_VERIFIKASI_NAMA}</td>
											</tr>
											<tr>
												<td width=\"150\">NIP.</td>
												<td width=\"260\" colspan=\"2\">: {$NIP}</td>
											</tr>
											<tr>
												<td colspan=\"3\"><br/><br/><br/></td>
											</tr>
											<tr>
												<td width=\"150\">Tandatangan</td>
												<td width=\"260\">:</td>
												<td width=\"240\">( .............................................................. )</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 8, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($page1, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 21, 9, 25, '', '', '', '', false, 300, '', false);
        
        $pdf->Output('sptpd-reklame.pdf', 'I');
    }	
        
/*    public function print_sspd() {
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
        $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
        $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);

        $query = "SELECT a.CPM_ATR_JUDUL,a.CPM_ATR_LOKASI FROM PATDA_REKLAME_DOC_ATR a INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
                  AND b.CPM_ID = '{$this->_id}'";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);
		$html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">   
                                        ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        ".strtoupper($NAMA_PENGELOLA)."<br /><br />        
                                        <font class=\"normal\">{$JALAN}<br/>
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
                                    <td>: Pajak Reklame</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
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
                                    <td>: {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} - {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}</td>
                                </tr> 
                                <tr>
                                    <td width=\"230\">Tanggal Lapor</td>
                                    <td>: {$DATA['pajak']['CPM_TGL_LAPOR']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tanggal Penetapan</td>
                                    <td>: {$TGL_PENETAPAN}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tanggal Jatuh Tempo</td>
                                    <td>: {$DATA['pajak']['CPM_TGL_JATUH_TEMPO']}</td>
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
                                                <br/>Judul : {$data['CPM_ATR_JUDUL']}
                                                <br/>Lokasi : {$data['CPM_ATR_LOKASI']}
                                                <br/>Keterangan : {$DATA['pajak']['CPM_KETERANGAN']}
                                                </td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
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
                                                <td align=\"left\">Denda Keterlambatan Pelaporan</td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
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
                                                    Dengan Huruf : " . ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah
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

        $pdf->Output('sspd-reklame.pdf', 'I');
    }
*/

    public function print_sspd() {
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
        $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
        $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);
		
		$BANK = $config['BANK'];
		$BANK_ALAMAT = $config['BANK_ALAMAT'];
		$BANK_NOREK = $config['BANK_NOREK'];
		
		$BENDAHARA_NAMA = $config['BENDAHARA_NAMA'];
		$BENDAHARA_NIP  = $config['BENDAHARA_NIP'];
        $query = "SELECT a.CPM_ATR_JUDUL,a.CPM_ATR_LOKASI FROM PATDA_REKLAME_DOC_ATR a INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
                  AND b.CPM_ID = '{$this->_id}'";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);
		$html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr valign=\"top\">
                                    <th valign=\"top\" width=\"450\" align=\"center\">
										<table border=\"0\">
											<tr>
												<td width=\"100\">&nbsp;</td>
												<td width=\"330\">
												<strong>
												".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
												".strtoupper($NAMA_PENGELOLA)."<br /><br />        
												</strong>
												<font class=\"normal\">{$JALAN}<br/>
												{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
												</td>
											</tr>
										</table>
                                    </th>
                                    <th width=\"260\" align=\"center\">
                                        <span style=\"margin:0px;!important;font-size:50px;font-weight:bold\">SSPD</span><br/>
                                        <strong>
                                        (SURAT SETORAN
                                        PAJAK DAERAH)
                                        </strong><br/>
                                        Tahun : {$DATA['pajak']['CPM_TAHUN_PAJAK']}
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>                   
                    <tr>
                        <td><table border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"400\"></td>
									<td width=\"310\"><table>
											<tr>
												<td width=\"80\"><strong>Nomor</strong></td>
												<td><strong>: {$DATA['pajak']['CPM_NO_SSPD']}</strong></td>
											</tr>
											<tr>
												<td><strong>Tanggal</strong></td>
												<td><strong>: {$this->formatDateForDokumen($TGL_PENETAPAN)}</strong></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td><table width=\"700\" border=\"0\" cellpadding=\"5\">
											<tr>
												<td width=\"180\">Nama</td>
												<td colspan=\"3\">: {$DATA['profil']['CPM_NAMA_WP']}</td>
											</tr>
											<tr style=\"vertical-align: top\">
												<td>Alamat</td>
												<td colspan=\"3\">: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
											</tr>
											<tr>
												<td>Nama Usaha</td>
												<td colspan=\"3\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
											</tr>
											<tr style=\"vertical-align: top\">
												<td>Alamat Usaha</td>
												<td colspan=\"3\">: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
											</tr>
											<tr>
												<td>NPWPD</td>
												<td colspan=\"3\">: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
											</tr>
											<tr>
												<td>Menyetor Berdasarkan</td>
												<td colspan=\"3\">: SKPD</td>
											</tr> 
											<tr>
												<td>Dokumen Penetapan</td>
												<td colspan=\"3\">: {$DATA['pajak']['CPM_NO']}</td>
											</tr>
											<tr>
												<td><i>Masa Pajak</i></td>
												<td width=\"230\">: <i>{$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} - {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}</i></td>
												<td width=\"70\"><i>Tahun</i></td>
												<td width=\"225\">: <i>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</i></td>
											</tr> 
											<tr>
												<td>Cara Pembayaran</td>
												<td colspan=\"3\">: [_] Tunai &nbsp;&nbsp;&nbsp; [_] Bank</td>                                        
											</tr> 
											<tr>
												<td>Bank Penerima Setoran</td>
												<td width=\"230\">: {$BANK}</td>
												<td width=\"70\">No. Rek</td>
												<td width=\"225\">: {$BANK_NOREK}</td>
											</tr> 
											<tr>
												<td>Kode Area</td>
												<td colspan=\"3\">: {$KODE_AREA}</td>                                        
											</tr>                                 
											<tr>
												<td>Tipe Pajak</td>
												<td colspan=\"3\">: {$KODE_PAJAK}</td>
											</tr>
											<tr>
												<td>Periode / Kode Bayar</td>
												<td colspan=\"3\">: {$PERIODE}</td>
											</tr>
											<tr>
												<td>Uraian</td>
												<td colspan=\"3\">: Pajak Reklame</td>
											</tr>
											<tr>
												<td colspan=\"4\">Dengan rincian penerimaan sebagai berikut : </td>
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
                                                <th width=\"50\" align=\"center\">No.</th>
                                                <th width=\"130\" align=\"center\">Kode Rekening</th>
                                                <th width=\"330\" align=\"center\">Jenis Pajak</th>
                                                <th width=\"200\" align=\"center\">Nilai (Rp.)</th>
                                            </tr>
                                            <tr>
                                                <td>1.</td>
                                                <td align=\"left\">
													{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}
                                                </td>
                                                <td>
													{$DATA['pajak_atr'][0]['nmrek']}
                                                </td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td align=\"right\" colspan=\"3\">Jumlah</td>
                                                <td align=\"right\" colspan=\"1\">" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"4\">
                                                    Terbilang : <i>" . ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah</i>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>                            
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"1\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"236\" align=\"center\">
                                    Ruang untuk Teraan<br/>
                                    Kas Register/Tanda Tangan<br/>
                                    Petugas Penerimaan<br/><br/>
                                    <br/><br/><br/>
                                    " . str_pad("", 50, "..", STR_PAD_RIGHT) . "<br/>                                     
                                    </td>
                                    <td width=\"236\" align=\"center\">
                                    Diterima Oleh :<br/>
                                    Bendahara Penerimaan<br/><br/>
                                    <br/><br/><br/><br/>
                                    ".(empty($BENDAHARA_NAMA)? str_pad("", 50, "..", STR_PAD_RIGHT) : "<u>{$BENDAHARA_NAMA}</u>")."<br/>
                                    NIP. {$BENDAHARA_NIP}                                     
                                    </td>
                                    <td width=\"236\" align=\"center\">
                                    {$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                                    Penyetor<br/><br/>
                                    <br/><br/><br/><br/>
                                    " . str_pad("", 50, "..", STR_PAD_RIGHT) . "<br/>                                     
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"1\" align=\"left\" class=\"header\" cellpadding=\"5\">                                
                                <tr>
                                    <td width=\"355\">SSPD ini berlaku setelah dilampiri dengan bukti pembayaran yang sah dari Bank</td>
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui teller dan ATM Bank {$BANK} terdekat</td>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 9, 20, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-reklame.pdf', 'I');
    }
    
        
    public function print_nota_hitung() {
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
		$KABID_NAMA = $config['KABID_PENDATAAN_NAMA'];
        $KABID_NIP = $config['KABID_PENDATAAN_NIP'];
        
        $KASIE_NAMA = $config['KASIE_PENETAPAN_NAMA'];
        $KASIE_NIP = $config['KASIE_PENETAPAN_NIP'];
        
        #$KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        #if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
        $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
        #}        
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
        $DATA['pajak']['CPM_NO_SSPD'] = $DATA['pajak']['CPM_NO'];
        
        $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);

        $query = "SELECT a.*,b.CPM_KETERANGAN FROM PATDA_REKLAME_DOC_ATR a INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
                  AND b.CPM_ID = '{$this->_id}'";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);
		#print_r($DATA);exit;
        $html = "<table width=\"1015\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"300\" align=\"center\">   
                                        <b>".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        ".strtoupper($NAMA_PENGELOLA)."<br /><br />        
                                        <font class=\"normal\">{$JALAN}<br/>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}</font></b>
                                    </th>
                                    <th width=\"415\" align=\"center\">
                                        <b>NOTA PERHITUNGAN PAJAK<br/>
                                        Tahun : {$DATA['pajak']['CPM_TAHUN_PAJAK']}</b>
                                    </th>
                                    <th width=\"300\" align=\"center\">
                                        <b>Nomor Nota Perhitungan :<br/>
                                        {$DATA['pajak']['CPM_NO_SSPD']}</b>
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"1015\" border=\"0\" cellpadding=\"5\">
							<tr>
								<td>Nama : {$DATA['profil']['CPM_NAMA_WP']}</td>
								<td>Alamat : {$DATA['profil']['CPM_ALAMAT_WP']}</td>
								<td>NPWPD : ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
							</tr>
							</table>
						</td>
                    </tr>
                </table>
                <table width=\"1015\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">      
                                <tr>
                                    <td><table width=\"1015\" border=\"1\" cellpadding=\"3\">
                                            <tr>
                                                <th width=\"50\" rowspan=\"2\" align=\"center\">NO.</th>
                                                <th width=\"180\" rowspan=\"2\" align=\"center\">JENIS PAJAK</th>
                                                <th width=\"300\" colspan=\"2\" align=\"center\">DASAR PENGENAAN</th>
                                                <th width=\"110\" rowspan=\"2\" align=\"center\">TARIF</th>
                                                <th width=\"130\" rowspan=\"2\" align=\"center\">KETETAPAN<br/>(Rp.)</th>
                                                <th width=\"125\" rowspan=\"2\" align=\"center\">DENDA BIAYA ADM.<br/>(Rp.)</th>
                                                <th width=\"120\" rowspan=\"2\" align=\"center\">JUMLAH<br/>(Rp.)</th>
                                            </tr>
                                            <tr>
                                                <th width=\"220\" align=\"center\">URAIAN</th>
                                                <th width=\"80\" align=\"center\">Banyak/Nilai</th>
                                            </tr>
                                            <tr>
												<td>1.</td>
												<td>Pajak Reklame</td>
												<td><u>{$data['CPM_ATR_JUDUL']}</u><br/>
													({$DATA['profil']['CPM_ALAMAT_OP']})<br/><br/>
													{$data['CPM_ATR_TINGGI']}m x
													{$data['CPM_ATR_LEBAR']}m x
													{$data['CPM_ATR_MUKA']}mk x {$DATA['pajak']['CPM_TARIF_PAJAK']}%<br/><br/>
													Masa : {$data['CPM_ATR_BATAS_AWAL']} s/d {$data['CPM_ATR_BATAS_AKHIR']}<br/><br/>
													{$data['CPM_KETERANGAN']}<br><br>
													Lokasi : {$data['CPM_ATR_LOKASI']}
												</td>
												<td>{$data['CPM_ATR_JUMLAH']} unit</td>
												<td align=\"right\"><!--".number_format($data['CPM_ATR_BIAYA'],2)." x {$DATA['pajak']['CPM_TARIF_PAJAK']}-->{$data['CPM_ATR_TINGGI']}m x
													{$data['CPM_ATR_LEBAR']}m x
													{$data['CPM_ATR_MUKA']}mk</td>
												<td align=\"right\">".number_format($DATA['pajak']['CPM_TOTAL_OMZET'],2)."</td>
												<td align=\"right\">".number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'],2)."</td>
												<td align=\"right\"></td>
                                            </tr>
                                            <tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\">JUMLAH</td>
												<td colspan=\"3\" align=\"center\">Rp. ".number_format($DATA['pajak']['CPM_TOTAL_PAJAK'],2)."</td>
                                            </tr>
                                        </table>
                                        
                                        <table width=\"1015\" border=\"0\" cellpadding=\"3\">
                                            <tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\"><font size=\"-2\">Jumlah dengan huruf </font></td>
												<td colspan=\"3\"><font size=\"-2\">(" . ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah)</font></td>
                                            </tr>
                                        </table>
                                        <br/><br/>
                                        <table width=\"1015\" border=\"0\" cellpadding=\"3\">
											<tr>
												<td>
													<table width=\"299\" border=\"0\">
														<tr>
														  <td width=\"289\" align=\"center\">Mengetahui,
															<br/>KEPALA BIDANG PENDAFTARAN, PENILAIAN DAN PENETAPAN</td>
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
																Diperiksa oleh :<br/>Kasubid Pendaftaran, Pendataan dan Penetapan<br>
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
														<tr>
														  <td>Oleh</td>
														  <td>: </td>
														</tr>
														<tr>
														  <td>Tanda tangan</td>
														  <td>: </td>
														</tr>
													</table>
												</td>
											</tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>                            ";

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

        $pdf->AddPage('L', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-nota-hitung.pdf', 'I');
    }

    public function read_dokumen() {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysql_query($select, $this->Conn);
            $data = mysql_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysql_query($query, $this->Conn);
        }
    }

    public function read_dokumen_notif() {
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
                    FROM {$this->PATDA_REKLAME_DOC} pj INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID                            
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

    public function list_lokasi_reklame() {
        $id = $_POST['id'];
        $harga = $_POST['harga'];

        $query = "SELECT B.*,A.CPM_KETERANGAN FROM PATDA_REKLAME_TYPE_LOKASI A INNER JOIN PATDA_REKLAME_LOKASI B ON A.CPM_LOKASI_ID = B.CPM_LOKASI_ID 
                    WHERE CPM_TYPE_ID='{$id}'";
        $result = mysql_query($query, $this->Conn);
        $respon = array();
        $respon['option_lokasi'] = "";
        while ($data = mysql_fetch_assoc($result)) {
            $respon['option_lokasi'].= "<option value='{$data['CPM_LOKASI_ID']}' harga='{$data['CPM_LOKASI_HARGA']}'>{$data['CPM_LOKASI_NAMA']} - {$data['CPM_LOKASI_HARGA']}</option>";
            $respon['keterangan'] = $data['CPM_KETERANGAN'];
        }
        $respon['harga'] = $harga;

        echo $this->Json->encode($respon);
    }

    public function list_type_reklame() {
        $id = $_POST['id'];

        $query = "SELECT B.*,A.CPM_HARGA FROM PATDA_REKLAME_TYPE_LOKASI A LEFT JOIN PATDA_REKLAME_TYPE B ON A.CPM_TYPE_ID = B.CPM_TYPE_ID 
                    WHERE CPM_LOKASI_ID='{$id}'";
        $result = mysql_query($query, $this->Conn);
        $respon = array();
        $respon['option_type'] = "<option></option>";
        $null = 0;
        while ($data = mysql_fetch_assoc($result)) {
            $null += (isset($data['CPM_TYPE_ID'])) ? 1 : 0;
            $respon['option_type'].= (isset($data['CPM_TYPE_ID'])) ? "<option value='{$data['CPM_TYPE_ID']}' harga='{$data['CPM_HARGA']}'>{$data['CPM_TYPE_NAMA']} - {$data['CPM_HARGA']}</option>" : "";
            $respon['harga'] = $data['CPM_HARGA'];
        }
        $respon['null_type'] = $null;


        echo $this->Json->encode($respon);
    }

    public function get_lokasi_harga() {
        $id = $_POST['id'];
        $id_lokasi = $_POST['id_lokasi'];

        $query = "SELECT B.*,A.CPM_HARGA, A.TARIF_NORMAL, A.TARIF_KHUSUS FROM PATDA_REKLAME_TYPE_LOKASI A INNER JOIN PATDA_REKLAME_TYPE B ON A.CPM_TYPE_ID = B.CPM_TYPE_ID 
                    WHERE A.CPM_TYPE_ID='{$id}' and CPM_LOKASI_ID='{$id_lokasi}'";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);
        $respon = array();
        $respon['harga'] = $data['CPM_HARGA'];
        $respon['query'] = $query;
        $respon['option_tarif'] = "<option value='{$data['TARIF_NORMAL']}'>- Tarif Normal [{$data['TARIF_NORMAL']}]</option><option value='{$data['TARIF_KHUSUS']}'>- Tarif Khusus [{$data['TARIF_KHUSUS']}]</option>";

        echo $this->Json->encode($respon);
    }

    public function get_permen() {
		$sql = sprintf("SELECT * FROM {$this->PATDA_REK_PERMEN13} where nmheader3 = 'Reklame'");
        $result = mysql_query($sql, $this->Conn);
        $i = 0;
        $respon = array();
        $data = array();
        while ($data = mysql_fetch_assoc($result)) {
            $respon[$i]['id'] = $data['kdrek'];
            $respon[$i]['text'] = $data['nmrek'];
            $respon[$i]['kode_rekening'] = $data['kdrek'];
            $respon[$i]['nama_rekening'] = $data['nmrek'];
            $respon[$i]['tarif1'] = $data['tarif1'];
            $respon[$i]['tarif2'] = $data['tarif2'];
            $respon[$i]['tarif3'] = $data['tarif3'];
            $respon[$i]['type_masa'] = $data['type_masa'];
            $respon[$i]['label'] = $data['nmrek'];
            $i++;
        }
		
        echo $this->Json->encode(array('items'=>$respon));
    }

    public function get_npwpd($term) {
        $data['success'] = false;
        $sql = sprintf("SELECT CPM_ID,CPM_NPWPD,CPM_NAMA_WP,CPM_ALAMAT_WP,CPM_NAMA_OP,CPM_ALAMAT_OP,
		CPM_NOP FROM {$this->PATDA_REKLAME_PROFIL} where (CPM_NPWPD like '%%%s%%' OR CPM_NAMA_WP like '%%%s%%')", $term, $term);
        $result = mysql_query($sql, $this->Conn);
        $i = 0;
        $respon = array();
        $data = array();
        //echo $sql;exit();
        while ($data = mysql_fetch_assoc($result)) {
            $respon[$i]['CPM_NPWPD'] = $data['CPM_NPWPD'];
            $respon[$i]['CPM_NAMA_WP'] = $data['CPM_NAMA_WP'];
            $respon[$i]['CPM_ALAMAT_WP'] = $data['CPM_ALAMAT_WP'];
            $respon[$i]['CPM_ALAMAT_OP'] = $data['CPM_ALAMAT_OP'];
            $respon[$i]['CPM_NAMA_OP'] = $data['CPM_NAMA_OP'];
            $respon[$i]['CPM_NOP'] = $data['CPM_NOP'];
            $respon[$i]['CPM_ID'] = $data['CPM_ID'];
            $i++;
        }

        if ($i != 0) {
            $data['data'] = $respon;
            $data['success'] = true;
        }

        //echo $this->Json->encode($data);
        return $data;
    }

    public function get_no_sspd($nosspd) {
        $sql = "SELECT CPM_NO_SSPD FROM PATDA_REKLAME_DOC WHERE CPM_NO_SSPD='{}'";
        $res = mysql_query($sql, $this->Conn);
        $ret = array();
        if (!mysql_result($res))
            return $ret;
        $row = mysql_fetch_assoc($res);
    }
    
    private function parseDate($date, $adding = ""){ //31/08/2016 to 2016-08-31
		$d = explode("/",$date);
		$date =  "{$d[2]}-{$d[1]}-{$d[0]}";
		
		if($adding!=""){
			$date = date('Y-m-d', strtotime($date . $adding));
		}
		return $date;
	}
	
    public function hitung_masa(){
		if(isset($_POST['startdate']) && isset($_POST['enddate'])){
			$startdate = $this->parseDate($_POST['startdate']);
			$enddate = $this->parseDate($_POST['enddate'],'+1 day');
			
			$query = "SELECT 
				DATEDIFF('{$enddate}','{$startdate}') as HARI,
				TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') +  DATEDIFF('{$enddate}', '{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') MONTH ) /
				DATEDIFF('{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') + 1 MONTH, '{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') MONTH) as BULAN";
				
			$res = mysql_query($query, $this->Conn);
			
			$response = array(
				'hari'=>0,
				'minggu'=>0,
				'bulan'=>0,
				'tahun'=>0
			);
			
			if($data = mysql_fetch_assoc($res)){
				$hari = $data['HARI'];
				$minggu = $data['HARI']/7;
				$bulan = $data['BULAN'];
				$tahun = $data['BULAN']/12;
				
				$hari = round($hari,2);
				$minggu = round($minggu,2);
				$bulan = round($bulan,2);
				$tahun = round($tahun,2);
				
				$triwulan = round($bulan/3,2);
				$semester = round($bulan/6,2);
				
				$response['hari'] = $hari;
				$response['minggu'] = $minggu;
				$response['bulan'] = $bulan;
				$response['tahun'] = $tahun;
				
				$response['triwulan'] = $triwulan;
				$response['semester'] = $semester;
			}
			echo $this->Json->encode($response);
		}
	}
	
	public function get_hargadasar(){
		$kdrek = $_POST['REKENING'];
		$type_masa = $_POST['TYPE_MASA'];
		
		$query = sprintf("SELECT harga_dasar FROM PATDA_REK_PERMEN_REKLAME WHERE kdrek = '%s' and type_masa='%s'", $kdrek, $type_masa);
		$res = mysql_query($query, $this->Conn);
		
		$response = array(
			'harga_dasar'=>0
		);
		
		if($data = mysql_fetch_assoc($res)){
			$response['harga_dasar'] = $data['harga_dasar'];
		}
		echo $this->Json->encode($response);
	}
}

?>
