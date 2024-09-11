<?php
/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : print_sptpd, print_sspd
*/
class LaporPajak extends Pajak {
    #field
    #jalan

    public $id_pajak = 6;

    public function __construct() {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function get_pajak($npwpd='', $nop='') {
		$Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.10");
        $list_nop = array();

		$query = "
			SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
			FROM PATDA_JALAN_DOC AS DOC WHERE DOC.CPM_ID = '{$this->_id}' LIMIT 0,1";

		$result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);

		//if new entry
        if(empty($pajak['CPM_ID'])){
			$ms = $this->inisialisasi_masa_pajak();

			$pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
			$pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
			$pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
			$pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
			$pajak['CPM_HARGA'] = 0;

			$profil = $Op->get_last_profil($npwpd, $nop);
			$tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
			$list_nop = $Op->get_list_nop($npwpd);

		}else{ //if data available
			$profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
			$tarif = $pajak['CPM_TARIF_PAJAK'];
		}

		$harga_dasar = $this->get_config_value($this->_a, "HARGA_DASAR_ENABLE_{$this->id_pajak}");
		$pajak['HARGA_DASAR_ENABLE'] = $harga_dasar;
		$pajak['CPM_HARGA_DASAR'] = (empty($pajak['CPM_HARGA_DASAR'])) ?
                (!empty($profil['CPM_REKENING'])) ? $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['harga'] : 0
                : $pajak['CPM_HARGA_DASAR'];
		$pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
		$pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;
        $pajak = array_merge($pajak, $arr_rekening);

        return array(
			'pajak'=>$pajak,
			'tarif'=>$tarif,
			'profil'=>$profil,
			'list_nop'=>$list_nop
		);
    }

    public function filtering($id) {
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
		foreach($kec as $k=>$v){
			$opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
        }

        $reks = $this->getRekening("4.1.01.10");
        $opt_rekening = '<option value="">All</option>'.json_encode($reks);
        foreach($reks['ARR_REKENING'] as $k=>$v){
            $opt_rekening .= "<option value=\"{$k}\">$k - {$v['nmrek']}</option>";
        }

       $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td><input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                        NPWPD :<br><input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  </td>
                        <td>Nama  :<br><input type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >  </td>
                        <td>Tahun Pajak :<br><select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select></td>
                        <td>Masa Pajak :<br><select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select></td>
                        <td>Tanggal Lapor :<br><input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly size=\"10\" class=\"date\" ><button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\">x</button> s.d <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" size=\"10\" class=\"date\" ><button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">x</button></td>
                        <td>Kecamatan :<br><select name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
                        <td>Kelurahan :<br><select name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select></td>
                        <td>Rekening :<br><select name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
                        <td bgcolor=\"#ffff00\">
                            <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                            <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>
							<button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-bentang-panjang.xls.php')\">Cetak Bentang Panjang</button>            
                        </td>
                    </tr></table></form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "jalan";
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
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()
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
            $where.= ($this->_mod == "pel")? " AND pr.CPM_NPWPD = '{$_SESSION['npwpd']}' " : "";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_KODE_REKENING']}' " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_JALAN_DOC} pj
                        INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
                        WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                        FROM {$this->PATDA_JALAN_DOC} pj INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

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

            mysqli_close($this->Conn);
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
        $modul = "jalan";
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
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()
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
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_KODE_REKENING']}' " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_JALAN_DOC} pj
                            INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
                            WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP,pr.CPM_NAMA_OP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_TGL_INPUT, tr.CPM_TRAN_DATE
                            FROM {$this->PATDA_JALAN_DOC} pj INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
                            WHERE {$where}
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);
				
				if ($row['CPM_TRAN_STATUS'] != '5'){
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

    private function last_version() {
        $query = "SELECT * FROM {$this->PATDA_JALAN_DOC_TRANMAIN} WHERE CPM_TRAN_JALAN_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        return $data['CPM_TRAN_JALAN_VERSION'];
    }

    private function validasi_save() {
        return $this->validasi_pajak(1);
    }

    private function validasi_update() {
        return $this->validasi_pajak(0);
    }

    private function validasi_pajak($input = 1) {
        $where = ($input == 1) ? "OR pj.CPM_NO='{$this->CPM_NO}'" : "AND pj.CPM_NO!='{$this->CPM_NO}'";

        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        $query = "SELECT pj.CPM_NO, pj.CPM_TAHUN_PAJAK, pj.CPM_MASA_PAJAK, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG
                FROM {$this->PATDA_JALAN_DOC} pj
                INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
                INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                WHERE
                (
                pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND
                pr.CPM_NOP = '{$this->CPM_NOP}' AND
                pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND
                pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
                ORDER BY tr.CPM_TRAN_STATUS DESC, pj.CPM_VERSION DESC  LIMIT 0,1";

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
        $respon['result'] = ($this->CPM_TIPE_PAJAK == 2)? true : $respon['result'];
        $respon['data'] = $data;

        return $respon;
    }

    private function save_pajak($cpm_no='') {
        $validasi = $this->validasi_save();

        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4')) {
            $this->Message->clearMessage();

            #update profil baru
            $query = "UPDATE {$this->PATDA_JALAN_PROFIL} SET CPM_APPROVE ='1' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            mysqli_query($this->Conn, $query);

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
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_LAPOR = date("d-m-Y");

            $this->CPM_TOTAL_KWH = str_replace(",", "", $this->CPM_TOTAL_KWH);
            $this->CPM_HARGA_DASAR = str_replace(",", "", $this->CPM_HARGA_DASAR);

            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);

            #$this->CPM_NO_SSPD = substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
            $this->CPM_NO_SSPD = $this->CPM_NO;

            $query = sprintf("INSERT INTO {$this->PATDA_JALAN_DOC}
                    (CPM_ID,CPM_ID_PROFIL,CPM_NO,
                    CPM_MASA_PAJAK,CPM_TAHUN_PAJAK,
                    CPM_TOTAL_KWH, CPM_HARGA_DASAR, CPM_TOTAL_OMZET,
                    CPM_TOTAL_PAJAK,CPM_TARIF_PAJAK,
                    CPM_KETERANGAN,CPM_VERSION,
                    CPM_AUTHOR,CPM_ID_TARIF,CPM_BAYAR_LAINNYA,
                    CPM_DPP,CPM_BAYAR_TERUTANG,CPM_NO_SSPD,
                    CPM_MASA_PAJAK1,CPM_MASA_PAJAK2,CPM_TIPE_PAJAK,CPM_DENDA_TERLAMBAT_LAP)
                    VALUES ( '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s','%s')", $this->CPM_ID, $this->CPM_ID_PROFIL, $this->CPM_NO, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK,
                             $this->CPM_TOTAL_KWH, $this->CPM_HARGA_DASAR, $this->CPM_TOTAL_OMZET, $this->CPM_TOTAL_PAJAK, $this->CPM_TARIF_PAJAK, $this->CPM_KETERANGAN, $this->CPM_VERSION, $this->CPM_AUTHOR, $this->CPM_ID_TARIF, $this->CPM_BAYAR_LAINNYA, $this->CPM_DPP, $this->CPM_BAYAR_TERUTANG, $this->CPM_NO_SSPD, $this->CPM_MASA_PAJAK1, $this->CPM_MASA_PAJAK2, $this->CPM_TIPE_PAJAK, $this->CPM_DENDA_TERLAMBAT_LAP
            );
            return mysqli_query($this->Conn, $query);
        }
        return false;
    }

    private function save_tranmain($param) {
        #insert tranmain
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_JALAN_ID = $this->CPM_ID;

        $query = "UPDATE {$this->PATDA_JALAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_JALAN_ID = '{$CPM_TRAN_JALAN_ID}'";
        $res = mysqli_query($this->Conn, $query);

        $query = sprintf("INSERT INTO {$this->PATDA_JALAN_DOC_TRANMAIN}
                    (CPM_TRAN_ID, CPM_TRAN_JALAN_ID, CPM_TRAN_JALAN_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE,
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')", $CPM_TRAN_ID, $CPM_TRAN_JALAN_ID, $param['CPM_TRAN_JALAN_VERSION'], $param['CPM_TRAN_STATUS'], $param['CPM_TRAN_FLAG'], $param['CPM_TRAN_DATE'], $param['CPM_TRAN_OPR'], $param['CPM_TRAN_OPR_DISPENDA'], $param['CPM_TRAN_INFO']
        );
        return mysqli_query($this->Conn, $query);
    }
	
	private function update_tgl_input() {
		$tgl_input = date("Y-m-d h:i:s");
        $query = "UPDATE {$this->PATDA_JALAN_DOC} SET CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";
				  
        return mysqli_query($this->Conn, $query);
    }
	
	private function update_tgl_lapor() {
		$tgl_input = date("d-m-Y");
        $query = "UPDATE {$this->PATDA_JALAN_DOC} SET CPM_TGL_LAPOR = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";
				  
        return mysqli_query($this->Conn, $query);
    }

    public function save() {
        $this->CPM_VERSION = "1";
        if ($this->save_pajak()) {
            $param = array();
            $param['CPM_TRAN_JALAN_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
			
			if($this->update_tgl_input()){
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            }else{
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

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
            $param['CPM_TRAN_JALAN_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);
			
			if($this->update_tgl_lapor()){
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            }else{
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

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

            $query = "UPDATE {$this->PATDA_JALAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_JALAN_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_JALAN_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";

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

            $query = "UPDATE {$this->PATDA_JALAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_JALAN_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_JALAN_VERSION'] = $new_version;
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
            $param['CPM_TRAN_JALAN_VERSION'] = $this->CPM_VERSION;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $this->save_tranmain($param);
			
			if($this->update_tgl_lapor()){
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            }else{
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

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

			$this->CPM_TOTAL_KWH = str_replace(",", "", $this->CPM_TOTAL_KWH);
            $this->CPM_HARGA_DASAR = str_replace(",", "", $this->CPM_HARGA_DASAR);
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);

            $query = sprintf("UPDATE {$this->PATDA_JALAN_DOC} SET
					CPM_TOTAL_KWH = '%s',
					CPM_HARGA_DASAR = '%s',
                    CPM_TOTAL_OMZET = '%s',
                    CPM_TOTAL_PAJAK = '%s',
                    CPM_TARIF_PAJAK = '%s',
                    CPM_BAYAR_LAINNYA = '%s',
                    CPM_DPP = '%s',
                    CPM_BAYAR_TERUTANG = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_TIPE_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s',
                    CPM_DENDA_TERLAMBAT_LAP = '%s'
                    WHERE
                    CPM_ID ='{$this->CPM_ID}'",
                    $this->CPM_TOTAL_KWH, $this->CPM_HARGA_DASAR,
                    $this->CPM_TOTAL_OMZET, $this->CPM_TOTAL_PAJAK, $this->CPM_TARIF_PAJAK, $this->CPM_BAYAR_LAINNYA, $this->CPM_DPP, $this->CPM_BAYAR_TERUTANG, $this->CPM_MASA_PAJAK1, $this->CPM_MASA_PAJAK2, $this->CPM_TIPE_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_MASA_PAJAK,$this->CPM_DENDA_TERLAMBAT_LAP);
            return mysqli_query($this->Conn, $query);
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM {$this->PATDA_JALAN_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
        if ($res) {
            $query = "DELETE FROM {$this->PATDA_JALAN_DOC_TRANMAIN} WHERE CPM_TRAN_JALAN_ID ='{$this->CPM_ID}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function verifikasi() {
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

        #validasi hanya satu tahap yaitu verifikasi saja
        /* $status = ($this->AUTHORITY == 1) ? 3 : 4;
          $param['CPM_TRAN_JALAN_VERSION'] = $this->CPM_VERSION;
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
        $param['CPM_TRAN_JALAN_VERSION'] = $this->CPM_VERSION;
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
        $query = "UPDATE {$this->PATDA_JALAN_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
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

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $html_harga_dasar = "";
        $harga_dasar = $config["HARGA_DASAR_ENABLE_{$this->id_pajak}"];
        $x = "c";
        if($harga_dasar == 1){
			$html_harga_dasar = "<tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;{$x}. Harga Dasar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_HARGA_DASAR'], 2) . "</td>
                                </tr>";
			$x++;
		}

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
                                            PAJAK PENERANGAN JALAN
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
                                    <td width=\"200\">&nbsp;&nbsp;&nbsp;Nama Wajib Pajak</td>
                                    <td width=\"500\">: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Nama Penerangan Jalan</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Penerangan Jalan</td>
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
                        <td width=\"710\" colspan=\"2\" align=\"left\"><strong>II. DIISI OLEH PENGUSAHA PENERANGAN JALAN</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"1\" cellspacing=\"0\">
                                <tr>
                                    <td align=\"left\" colspan=\"2\">&nbsp;&nbsp;&nbsp;a. Klasifikasi Pajak : {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;b. Pemakaian</td>
                                    <td width=\"30\">Kwh</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_TOTAL_KWH'], 2) . "</td>
                                </tr>
                                $html_harga_dasar
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Pembayaran Pemakaian Objek Pajak</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> 0</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Sanksi Administrasi Telat Lapor ({$persen_terlambat_lap}%) x ".round($persen_sanksi/2)." Bulan</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> ".number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'],2)."</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;".($x++).". Data Pendukung</td>
                                    <td align=\"left\" width=\"430\"> </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a). Surat Setoran Pajak Daerah (SSPD)</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b). NPWP / NPWPD</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c). Rekapitulasi Kwh Penerangan Jalan</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">
                                <tr>
                                    <td align=\"left\" colspan=\"2\">&nbsp;&nbsp;&nbsp;Dengan menyadari sepenuhnya akan segala akibatnya termasuk sanksi-sanksi sesuai ketentuan perundang-undangan yang berlaku, saya memberitahukan bahwa apa yang telah saya beritahukan diatas beserta lampiran-lampirannya adalah benar, lengkap, jelas dan bersyarat
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

        $pdf->Output('sptpd-jalan.pdf', 'I');
    }
	
	
	function tgl_indo_full($tanggal){
				$bulan = array (
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
			 
				return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
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
		
		$TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
		$tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $persen_sanksi = 0;
        if(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']+0)>0){
            //$persen_terlambat_lap = round($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK'], 1)*100;
            // $persen_sanksi = round(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK']*100)/2,0)*2;
            //tamabahan
            $tes3 =$DATA['pajak']['CPM_TOTAL_PAJAK']-$DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 =$DATA['pajak']['CPM_TOTAL_PAJAK']-$tes3;
            $persen_sanksi = round(($tes4/$tes3)*100);
        }

		$html_harga_dasar = "";
        $harga_dasar = $config["HARGA_DASAR_ENABLE_{$this->id_pajak}"];
        $x = "c";
        if($harga_dasar == 1){
			$html_harga_dasar = "<tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;{$x}.</td>
                                    <td align=\"left\" width=\"270\">Harga Dasar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_HARGA_DASAR'], 2) . "</td>
                                </tr>";
			$x++;
		}

		$pemerintah = explode(' ',$JENIS_PEMERINTAHAN);
		$pemerintah_label = strtoupper($pemerintah[0]);
		$pemerintah_jenis = strtoupper($pemerintah[1]);
		
		
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
                                        <b style=\"font-size:55px\">PAJAK PENERANGAN JALAN</b><br/>
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
												Kepala Badan Pengelolaan Keuangan Daerah<br/>
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
											&nbsp;&nbsp;Desa/Kelurahan : {$DATA['profil']['CPM_NAMA_KELURAHAN_OP']}<br/>
											&nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_NAMA_KECAMATAN_OP']}<br/>
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
                                    <td align=\"left\" width=\"270\">Golongan Penerangan Jalan</td>
                                    <td align=\"left\" width=\"420\" colspan=\"2\"> {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}</td>
                                </tr>

                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;b.</td>
                                    <td align=\"left\" width=\"270\">Pemakaian</td>
                                    <td width=\"30\" align=\"left\">Kwh</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_TOTAL_KWH'], 2) . "</td>
                                </tr>

                                {$html_harga_dasar}

                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Pembayaran Pemakaian Objek Pajak</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> 0.00</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Sanksi Administrasi Telat Lapor ({$persen_terlambat_lap}%) x ".round($persen_sanksi/2)." Bulan</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> ".number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'],2)."</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
                                    <td align=\"left\" width=\"270\">Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;".($x++).".</td>
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
									<td align=\"right\" width=\"40\">c).</td>
                                    <td align=\"left\" width=\"250\">Rekapitulasi Kwh Penerangan Jalan</td>
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
										Wajib Pajak<br/><br/><br/><br/><br/>
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
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td width=\"150\">NIP.</td>
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td colspan=\"3\"><br/></td>
											</tr>
											<tr>
												<td width=\"150\">Tanda Tangan</td>
												<td width=\"260\">:</td>
												<td width=\"240\">( .............................................................. )</td>
											</tr>
										</table>
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
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($page1, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 27, 8, 8, '', '', '', '', false, 300, '', false);


        $pdf->Output('sptpd-penerangan-jalan.pdf', 'I');
    }

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

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">
                                        ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        ".strtoupper($NAMA_PENGELOLA)."<br /><br />
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
                                    <td>: Pajak Penerangan Jalan</td>
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
                                    <td>: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
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
                                                <td align=\"left\">Pembayaran pajak Penerangan Jalan {$DATA['profil']['CPM_NAMA_OP']}
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

        $pdf->Output('sspd-jalan.pdf', 'I');
    }*/

    public function read_dokumen() {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_JALAN_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE {$this->PATDA_JALAN_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysqli_query($this->Conn, $query);
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
                    FROM {$this->PATDA_JALAN_DOC} pj INNER JOIN {$this->PATDA_JALAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_JALAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_JALAN_ID
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

	public function get_previous_pajak($npwpd, $nop) {
		$Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.10");
        $pajak_atr = array();
        $list_nop = array();

        $query = "
			SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
			FROM PATDA_JALAN_DOC AS DOC
			INNER JOIN PATDA_JALAN_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
			WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}' AND
			str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y') = (
				SELECT MAX(str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y'))
				FROM PATDA_JALAN_DOC AS DOC
				INNER JOIN PATDA_JALAN_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
				WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}'
			)";
		// echo $query;
		$result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);

		$ms = $this->inisialisasi_masa_pajak();

		if(empty($pajak['CPM_ID'])){

			$pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
			$pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
			$pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
			$pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
			$pajak['CPM_HARGA'] = 0;

			$profil = $Op->get_last_profil($npwpd, $nop);

			$tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
			$list_nop = $Op->get_list_nop($npwpd);

		}else{ //if data available
			$profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
			$tarif = $pajak['CPM_TARIF_PAJAK'];
			$list_nop = $Op->get_list_nop($npwpd);
		}

		$harga_dasar = $this->get_config_value($this->_a, "HARGA_DASAR_ENABLE_{$this->id_pajak}");
		$pajak['HARGA_DASAR_ENABLE'] = $harga_dasar;
		$pajak['CPM_HARGA_DASAR'] = (empty($pajak['CPM_HARGA_DASAR'])) ? $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['harga'] : $pajak['CPM_HARGA_DASAR'];

		$pajak['CPM_ID'] = '';
		$pajak['CPM_NO'] = '';
		$pajak['CPM_ID_PROFIL'] = '';
		$pajak['CPM_HARGA'] = 0;

		$pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
		$pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;

		$pajak = array_merge($pajak, $arr_rekening);

		//echo '<pre>',print_r($pajak,true),'</pre>';exit;
		return array(
			'pajak'=>$pajak,
			'tarif'=>$tarif,
			'profil'=>$profil,
			'list_nop'=>$list_nop
		);
    }

}

?>
