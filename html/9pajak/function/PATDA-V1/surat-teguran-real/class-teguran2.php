<?php

class Teguran extends Pajak {

    private $tipe_teguran;

    function __construct() {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->CPM_NPWPD = isset($this->CPM_NPWPD)? preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD) : '';
    }

    public function get_pajak() {
        #inisialisasi data kosong
        $respon['pajak'] = array(
            "CPM_ID" => "",
            "CPM_NPWPD" => "",
            "CPM_NAMA_OP" => "",
            "CPM_ALAMAT_OP" => "",
            "CPM_KECAMATAN_OP" => "",
            "CPM_NAMA_WP" => "",
            "CPM_NO_SURAT" => "",
            "CPM_JENIS_PAJAK" => "",
            "CPM_MASA_PAJAK" => "",
            "CPM_TAHUN_PAJAK" => "",
            "CPM_NO_SKPD" => "",
            "CPM_TGL_SKPD" => "",
            "CPM_JATUH_TEMPO" => "",
            "CPM_JUMLAH_TUNGGAKAN" => "",
            "CPM_TERBILANG" => "",
            "CPM_TGL_INPUT" => "",
            "CPM_AUTHOR" => "",
            "CPM_SIFAT" => "",
            "CPM_LAMPIRAN" => ""
        );

        #query untuk mengambil data pajak
        $query = "SELECT * FROM PATDA_TEGURAN WHERE CPM_ID = '{$this->_id}'";

        $result = mysqli_query($this->Conn, $query);

        #jika ada data 
        if (mysqli_num_rows($result) > 0) {
            $respon['pajak'] = mysqli_fetch_assoc($result);
        }
        return $respon;
    }
	
	public function get_pajak_new() {
        #inisialisasi data kosong
        $respon['pajak'] = array(
            "CPM_ID" => "",
            "CPM_NPWPD" => "",
            "CPM_NAMA_OP" => "",
            "CPM_ALAMAT_OP" => "",
            "CPM_KECAMATAN_OP" => "",
            "CPM_NAMA_WP" => "",
            "CPM_NO_SURAT" => "",
            "CPM_JENIS_PAJAK" => "",
            "CPM_MASA_PAJAK" => "",
            "CPM_TAHUN_PAJAK" => "",
            "CPM_NO_SKPD" => "",
            "CPM_TGL_SKPD" => "",
            "CPM_JATUH_TEMPO" => "",
            "CPM_JUMLAH_TUNGGAKAN" => "",
            "CPM_TERBILANG" => "",
            "CPM_TGL_INPUT" => "",
            "CPM_AUTHOR" => "",
            "CPM_SIFAT" => "",
            "CPM_LAMPIRAN" => ""
        );
		
		$jns = $_REQUEST['jp'];
		
		if($jns == 1){
            $table = "PATDA_AIRBAWAHTANAH_DOC"; //airbawahtanah
        }else if($jns == 2){
            $table = "PATDA_HIBURAN_DOC"; // hiburan
        }else if($jns == 3){
            $table = "PATDA_HOTEL_DOC"; //hotel
        }else if($jns == 4){
            $table = "PATDA_MINERAL_DOC"; // mineral
        }else if($jns == 5){
            $table = "PATDA_PARKIR_DOC";; //parkir
        }else if($jns == 6){
            $table = "PATDA_JALAN_DOC"; //jalan
        }else if($jns == 7){
            $table = "PATDA_REKLAME_DOC"; //reklame
        }else if($jns == 8){
            $table = "PATDA_RESTORAN_DOC"; // restoran
        }else{
            $table = "PATDA_WALLET_DOC"; // wallet
        }

        
		#query untuk mengambil data pajak
        //$query = "SELECT * FROM PATDA_TEGURAN WHERE CPM_ID = '{$this->_id}'";
				$query = "SELECT a.CPM_ID, b.npwpd as CPM_NPWPD, b.op_nama as CPM_NAMA_OP, b.op_alamat as CPM_ALAMAT_OP, c.CPM_KECAMATAN as CPM_KECAMATAN_OP, b.wp_nama as CPM_NAMA_WP, d.CPM_NO as CPM_JENIS_PAJAK, a.CPM_MASA_PAJAK, a.CPM_TAHUN_PAJAK, b.sptpd as CPM_NO_SKPD, DATE_FORMAT(STR_TO_DATE(a.CPM_TGL_LAPOR,'%d-%m-%Y'), '%d/%m/%Y')  as CPM_TGL_SKPD, DATE_FORMAT(a.CPM_TGL_JATUH_TEMPO,'%d/%m/%Y') as CPM_JATUH_TEMPO,
a.CPM_BAYAR_TERUTANG as CPM_JUMLAH_TUNGGAKAN
		FROM {$table} a 
		INNER JOIN SIMPATDA_GW b ON a.CPM_ID = b.id_switching 
		INNER JOIN PATDA_MST_KECAMATAN c ON b.kecamatan_op = c.CPM_KEC_ID
		INNER JOIN PATDA_JENIS_PAJAK d ON b.simpatda_type = d.CPM_TIPE
		WHERE a.CPM_ID = '{$this->_id}'";
		//var_dump($query);die;
		
		
		$result = mysqli_query($this->Conn, $query);
		
        #jika ada data 
        if (mysqli_num_rows($result) > 0) {
            $respon['pajak'] = mysqli_fetch_assoc($result);
        }
        return $respon;
    }

    public function filtering($id) {
        $html = "<style>
                .filtering{font-weight:normal}
                .filtering table td{background:transparent}
                .filtering input,.filtering select,.filtering button{height:26px}
                </style>
                <div class=\"filtering\">
                <form><table>
                    <tr>
                        <td>
                            NPWPD :<br>
                            <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\">
                        </td>
                        <td>
                            No. Surat Teguran :<br>
                            <input type=\"text\" name=\"CPM_NO_SURAT-{$id}\" id=\"CPM_NO_SURAT-{$id}\">
                        </td>
                        <td>
                            Tahun :<br>
                            <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $html.= "<option value='{$th}'>{$th}</option>";
        }
        $html.= "</select>
                </td>
                <td>
                    Masa Pajak :<br>
                    <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $html.= "<option value='{$x}'>{$y}</option>";
        }
        $html.= "</select></td>
                <td>
                    Tanggal Input :<br>
                    <input type=\"tex\" name=\"CPM_TGL_INPUT1-{$id}\" id=\"CPM_TGL_INPUT1-{$id}\" class=\"datepicker\" size=\"10\"> s/d <input type=\"tex\" name=\"CPM_TGL_INPUT2-{$id}\" id=\"CPM_TGL_INPUT2-{$id}\" class=\"datepicker\" size=\"10\">
                </td>
                <td valign=\"bottom\">
                    <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    <button type=\"button\" onclick=\"download_excel('{$id}')\">Export Excel</button>
                </td>
                </tr>
                </table></form>
                </div> ";
        return $html;
    }

    public function grid_table_teguran1() {
        $this->tipe_teguran = 1;
        $this->grid_table();
    }

    public function grid_table_teguran2() {
        $this->tipe_teguran = 2;
        $this->grid_table();
    }

    public function grid_table_sptpd() {
        $this->tipe_teguran = "sptpd";
        $this->grid_table();
    }

    public function grid_table_jatuhtempo() {
        $this->tipe_teguran = "jatuhtempo";
        $this->grid_table();
    }
	
	public function grid_table_jatuh_tempo() {
        $this->tipe_teguran = "jatuh-tempo";
        $this->grid_table_new();
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "surat-teguran-real";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('.datepicker').datepicker({
							dateFormat: 'dd-mm-yy',
							changeMonth: true,
							showOn: \"button\",
							buttonImageOnly: false,
							buttonText: \"...\"});
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-{$this->tipe_teguran}.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_NO_SURAT: {title: 'No. Surat Teguran',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'}, 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_JUMLAH_TUNGGAKAN: {title: 'Tunggakan Pajak',width: '10%'},
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
                                CPM_NO_SURAT : $('#CPM_NO_SURAT-{$this->_i}').val(),    
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TGL_INPUT1 : $('#CPM_TGL_INPUT1-{$this->_i}').val(),
                                CPM_TGL_INPUT2 : $('#CPM_TGL_INPUT2-{$this->_i}').val(),
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });

                    function download_excel(){
                        var form = document.createElement('form');
                        form.setAttribute('method', 'post');
                        form.setAttribute('target', 'excel');
                        form.setAttribute('action', 'function/PATDA-V1/surat-teguran-real/svc-download.xls.php');

                       
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                </script>";
        echo $html;
    }
	
	
	public function grid_table_new() {
        $DIR = "PATDA-V1";
        $modul = "surat-teguran-real";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('.datepicker').datepicker({
							dateFormat: 'dd-mm-yy',
							changeMonth: true,
							showOn: \"button\",
							buttonImageOnly: false,
							buttonText: \"...\"});
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-{$this->tipe_teguran}.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_NO: {title: 'NPWPD',width: '10%'},
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%'}, 
                                CPM_TIPE_PAJAK: {title: 'Jenis Pajak',width: '10%'},
				CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_BAYAR_TERUTANG: {title: 'Tunggakan Pajak',width: '10%'},
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
                                CPM_NO : $('#CPM_NO-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });

                    function download_excel(){
                        var form = document.createElement('form');
                        form.setAttribute('method', 'post');
                        form.setAttribute('target', 'excel');
                        form.setAttribute('action', 'function/PATDA-V1/surat-teguran-real/svc-download.xls.php');

                       
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                </script>";
        echo $html;
    }

    public function grid_data_teguran1() {
        $this->tipe_teguran = 1;
        $this->grid_data();
    }

    public function grid_data_teguran2() {
        $this->tipe_teguran = 2;
        $this->grid_data();
    }

    public function grid_data_jatuhtempo() {
        $this->tipe_teguran = "jatuhtempo";
        $this->grid_data();
    }

    public function grid_data_sptpd() {
        $this->tipe_teguran = "sptpd";
        $this->grid_data();
    }
	
	public function grid_data_jatuh_tempo() {
        $this->tipe_teguran = "jatuh-tempo";
        $this->grid_data_new();
    }

    public function grid_data() {
        try {
            $where = "CPM_TIPE_TEGURAN='{$this->tipe_teguran}'";

            $_REQUEST['CPM_MASA_PAJAK'] = (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, 0, STR_PAD_LEFT) : "";

            $where.= (isset($_REQUEST['CPM_NO_SURAT']) && $_REQUEST['CPM_NO_SURAT'] != "") ? " AND CPM_NO_SURAT like \"{$_REQUEST['CPM_NO_SURAT']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";

            if ($_REQUEST['CPM_TGL_INPUT1'] != "" && $_REQUEST['CPM_TGL_INPUT2'] == "") {
				$where .= " AND CPM_TGL_INPUT = '{$_REQUEST['CPM_TGL_INPUT1']}'";
			} elseif ($_REQUEST['CPM_TGL_INPUT1'] == "" && $_REQUEST['CPM_TGL_INPUT2'] != "") {
				$where .=" AND CPM_TGL_INPUT = '{$_REQUEST['CPM_TGL_INPUT2']}'";
			} elseif ($_REQUEST['CPM_TGL_INPUT1'] != "" && $_REQUEST['CPM_TGL_INPUT2'] != "") {
				$where .=" AND (str_to_date(CPM_TGL_INPUT,'%d-%m-%Y') BETWEEN str_to_date('{$_REQUEST['CPM_TGL_INPUT1']}', '%d-%m-%Y') AND str_to_date('{$_REQUEST['CPM_TGL_INPUT2']}', '%d-%m-%Y'))";
			}

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_TEGURAN WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT *
                        FROM PATDA_TEGURAN t
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

                $row = array_merge($row, array("NO" => ++$no));

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&i={$this->_i}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_JENIS_PAJAK'] = $this->arr_pajak[(int) $row['CPM_JENIS_PAJAK']];
                $row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
                $row['CPM_NO_SURAT'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SURAT']}</a>";
                $row['CPM_NPWPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">".Pajak::formatNPWPD($row['CPM_NPWPD'])."</a>";

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
	
	
	public function grid_data_new() {
        try {
			$select = "(SELECT MAX(a.CPM_VERSION), a.CPM_ID, a.CPM_NO, STR_TO_DATE(a.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, a.CPM_BAYAR_TERUTANG, a.CPM_AUTHOR, a.CPM_MASA_PAJAK, a.CPM_TAHUN_PAJAK, c.id_sw as TIPE_PAJAK";
            $where = "TIME(a.CPM_TGL_JATUH_TEMPO) <= DATE_ADD(a.CPM_TGL_JATUH_TEMPO, INTERVAL 14 day) AND b.payment_flag = '0'";
			$join ="INNER JOIN simpatda_gw b ON a.CPM_NO = b.sptpd 
			INNER JOIN simpatda_type c ON b.simpatda_type = c.id";
			$order = "GROUP BY a.CPM_NO ORDER BY CPM_NO ASC)";
			
			
            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM 
			(SELECT a.CPM_NO  
			FROM patda_airbawahtanah_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_hiburan_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_hotel_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_mineral_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_parkir_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_jalan_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_reklame_doc a 
			{$join}
			WHERE {$where}
			UNION
			SELECT a.CPM_NO   
			FROM patda_restoran_doc a 
			{$join}
			WHERE {$where}			
			)
			AS tem";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];
			

            #query select list data
            $query = "{$select}  
			FROM patda_airbawahtanah_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_hiburan_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_hotel_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_mineral_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_parkir_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_jalan_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_reklame_doc a 
			{$join}
			WHERE {$where} {$order} UNION
			{$select}  
			FROM patda_restoran_doc a 
			{$join}
			WHERE {$where} {$order}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);
			//var_dump($query);die;
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

                $row = array_merge($row, array("NO" => ++$no));

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&i={$this->_i}&jp={$row['TIPE_PAJAK']}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_TIPE_PAJAK'] = $this->arr_pajak[(int) $row['TIPE_PAJAK']];
                $row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
                $row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">".Pajak::formatNPWPD($row['CPM_NO'])."</a>";

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

    public function save() {
        $this->tipe_teguran = isset($_REQUEST['tipe_teguran']) ? $_REQUEST['tipe_teguran'] : "";


        $validasi = $this->validasi_save();
        if ($validasi['result'] == true) {
            $this->Message->clearMessage();

			//no surat
			$tahun = date("Y");
			$length = 4;
			$queryS = "SELECT MAX(SUBSTRING(CPM_NO_SURAT,5, 4)) as nomor FROM patda_teguran_nomor_surat where SUBSTRING(CPM_NO_SURAT,15,4) = '{$tahun}'";
			$resultS = mysqli_query($this->Conn, $queryS);
			$checkS = mysqli_num_rows($resultS);
			if($checkS > 0){
				while($row = mysqli_fetch_assoc($resultS)){
					$nomor = $row['nomor']+1;
				}
			}else{
				$nomor = 1;
			}
			
			$nomor = str_pad($nomor, $length, '0', STR_PAD_LEFT);
			$nomor_surat = '800/'.$nomor.'/V.04/'.$tahun;
			
			$querySS = sprintf("INSERT INTO patda_teguran_nomor_surat
                    (
                    CPM_NO_SURAT,
                    CPM_NO_SKPD)
                    VALUES ( '%s','%s')", $nomor_surat, $this->CPM_NO_SKPD
            );
			mysqli_query($this->Conn, $querySS);
			//var_dump($nomor_surat);die;

            #insert pajak baru
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_INPUT = date("d-m-Y");
            $this->CPM_JUMLAH_TUNGGAKAN = str_replace(",", "", $this->CPM_JUMLAH_TUNGGAKAN);

            $query = sprintf("INSERT INTO PATDA_TEGURAN
                    (CPM_ID,
                    CPM_NPWPD,
                    CPM_NAMA_OP,
                    CPM_ALAMAT_OP,
		    CPM_KECAMATAN_OP,
                    CPM_NAMA_WP,
                    CPM_NO_SURAT,
                    CPM_JENIS_PAJAK,
                    CPM_MASA_PAJAK,
                    CPM_TAHUN_PAJAK,
                    CPM_NO_SKPD,
                    CPM_TGL_SKPD,
                    CPM_JATUH_TEMPO,
                    CPM_JUMLAH_TUNGGAKAN,
                    CPM_TERBILANG,
                    CPM_TGL_INPUT,
                    CPM_AUTHOR,
                    CPM_TIPE_TEGURAN,
                    CPM_SIFAT,
                    CPM_LAMPIRAN)
                    VALUES ( '%s','%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s','%s','%s')", $this->CPM_ID, $this->CPM_NPWPD, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KECAMATAN_OP, $this->CPM_NAMA_WP, $nomor_surat, $this->CPM_JENIS_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_NO_SKPD, $this->CPM_TGL_SKPD, $this->CPM_JATUH_TEMPO, $this->CPM_JUMLAH_TUNGGAKAN, $this->CPM_TERBILANG, $this->CPM_TGL_INPUT, $this->CPM_AUTHOR, $this->tipe_teguran, $this->CPM_SIFAT, $this->CPM_LAMPIRAN
            );
            return mysqli_query($this->Conn, $query);
        }
        return false;
    }

    private function validasi_save() {
        $query = "SELECT * FROM PATDA_TEGURAN WHERE CPM_NO_SURAT = '{$this->CPM_NO_SURAT}'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        $hasil = true;
        if (mysqli_num_rows($res) > 0) {
			$msg = "Gagal disimpan, Pajak dengan No. Teguran <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
            $hasil = false;
        }

        $respon['result'] = $hasil;
        $respon['data'] = $data;

        return $respon;
    }

    private function validasi_update() {
        $query = "SELECT * FROM PATDA_TEGURAN WHERE CPM_NO_SURAT = '{$this->CPM_NO_SURAT}'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        $hasil = true;
        if (mysqli_num_rows($res) == 0) {
			$msg = "Gagal disimpan, Pajak dengan No. Teguran <b>{$this->CPM_NO}</b> sudah tersedia!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
            $hasil = false;
        }

        $respon['result'] = $hasil;
        $respon['data'] = $data;

        return $respon;
    }

    public function update() {
        $this->tipe_teguran = isset($_REQUEST['tipe_teguran']) ? $_REQUEST['tipe_teguran'] : "";

        $validasi = $this->validasi_update();

        if ($validasi['result'] == true) {
            $this->Message->clearMessage();
            $this->CPM_JUMLAH_TUNGGAKAN = str_replace(",", "", $this->CPM_JUMLAH_TUNGGAKAN);

            $query = sprintf("UPDATE PATDA_TEGURAN set                
                    CPM_NPWPD = '%s',
                    CPM_NAMA_OP = '%s',
                    CPM_ALAMAT_OP = '%s',
                    CPM_KECAMATAN_OP = '%s',	
                    CPM_NAMA_WP = '%s',			
                    CPM_NO_SURAT = '%s',
                    CPM_JENIS_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_NO_SKPD = '%s',
                    CPM_TGL_SKPD = '%s',
                    CPM_JATUH_TEMPO = '%s',
                    CPM_JUMLAH_TUNGGAKAN = '%s',
                    CPM_TERBILANG = '%s',
                    CPM_TIPE_TEGURAN = '%s',
                    CPM_SIFAT = '%s',
                    CPM_LAMPIRAN = '%s'
                    WHERE CPM_ID = '%s'", $this->CPM_NPWPD, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KECAMATAN_OP, $this->CPM_NAMA_WP, $this->CPM_NO_SURAT, $this->CPM_JENIS_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_NO_SKPD, $this->CPM_TGL_SKPD, $this->CPM_JATUH_TEMPO, $this->CPM_JUMLAH_TUNGGAKAN, $this->CPM_TERBILANG, $this->tipe_teguran, $this->CPM_SIFAT, $this->CPM_LAMPIRAN, $this->CPM_ID
            );
            #echo $query;exit;
            return mysqli_query($this->Conn, $query);
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM PATDA_TEGURAN WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
    }

    public function print_teguran() {
        $this->tipe_teguran = isset($_REQUEST['tipe_teguran']) ? $_REQUEST['tipe_teguran'] : "";
        switch ($this->tipe_teguran) {
            case 1 : $this->print_teguran2("I");
                break;
            case 2 : $this->print_teguran2("II");
                break;
            case "sptpd" : $this->print_teguran_sptpd();
                break;
            case "jatuhtempo" : $this->print_teguran2("JATUH TEMPO");
                break;
        }
    }

    private function print_teguran2($tipe) {
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
        $MENDAGRI_TEGURAN = $config['MENDAGRI_TEGURAN'];

	
	if($this->tipe_teguran==1){
		$NAMA_TTD = $config['BAG_SURAT_TEGURAN_1_NAMA'];
		$NIP = $config['BAG_SURAT_TEGURAN_1_NIP'];
		$JABATAN = $config['BAG_SURAT_TEGURAN_1_JABATAN'];
	}else{
		$NAMA_TTD = $config['BAG_SURAT_TEGURAN_2_NAMA'];
		$NIP = $config['BAG_SURAT_TEGURAN_2_NIP'];
		$JABATAN = $config['BAG_SURAT_TEGURAN_2_JABATAN'];
        }

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" style=\"border:1px #000 solid\" cellspacing=\"0\">
                <tr>
                    <td><table width=\"700\" border=\"0\">
                            <tr>
                                <th valign=\"top\" align=\"center\">                                   
                                    ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />      
									DINAS PENDAPATAN DAERAH<br /><br />        
									<font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                </th>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td><table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"0\" cellspacing=\"0\">                        
                        <tr>
                            <td width=\"450\"><table width=\"440\" class=\"header\" border=\"0\">
                                    <tr>                             
                                        <td width=\"130\">NPWPD / NPWRD</td>
                                        <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_NPWPD']}</td>                  
                                    </tr>
                                </table>
                            </td>
                            <td width=\"260\">Kepada Yth <br/>
                                Sdr / Pemilik / Pimpinan<br/>
                                {$DATA['pajak']['CPM_NAMA_OP']} / {$DATA['pajak']['CPM_NAMA_WP']}<br/>
                                di {$KOTA}<br/>
                                Kec. {$DATA['pajak']['CPM_KECAMATAN_OP']}
                            </td>
                        </tr>
                        <tr class=\"first\">
                            <td valign=\"top\" align=\"center\" colspan=\"2\"><b>SURAT TEGURAN {$tipe}</b><br/>Nomor : {$DATA['pajak']['CPM_NO_SURAT']}</td>
                        </tr>                        
                        <tr>
                            <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                    <tr>
                                        <th align=\"left\" colspan=\"2\">Menurut Pembukuan kami, hingga saat ini saudara masih mempunyai tunggakan sebagai berikut :</th>
                                    </tr>
                                </table>            
                            </td>
                        </tr>
                        <tr>
                            <td width=\"710\" colspan=\"2\"><table width=\"700\" align=\"center\" cellpadding=\"2\" border=\"1\" cellspacing=\"0\">                                            
                                    <tr>
                                        <td width=\"40\"><b>No.</b></td>
                                        <td width=\"140\"><b>Jenis</b></td>
                                        <td width=\"120\"><b>Masa Pjk / Thn</b></td>
                                        <td width=\"140\"><b>No. SSPD / SKPD</b></td>
                                        <td width=\"120\"><b>Jatuh Tempo</b></td>
                                        <td width=\"140\"><b>Jumlah Tunggakan (Rp. )</b></td>
                                    </tr>
                                    <tr>
                                        <td>1.</td>
                                        <td align=\"left\">{$this->arr_pajak[(int) $DATA['pajak']['CPM_JENIS_PAJAK']]}</td>
                                        <td>" . $this->arr_bulan[(int) $DATA['pajak']['CPM_MASA_PAJAK']] . "-{$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                                        <td>{$DATA['pajak']['CPM_NO_SKPD']}</td>
                                        <td>{$DATA['pajak']['CPM_JATUH_TEMPO']}</td>
                                        <td align=\"right\">" . number_format($DATA['pajak']['CPM_JUMLAH_TUNGGAKAN'], 0) . "</td>
                                    </tr>
                                </table> 
                            </td>  
                        </tr>
                        <tr>
                            <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">
                                    <tr>
                                        <td align=\"left\" colspan=\"3\"></td>
                                    </tr>
                                    <tr>
                                        <td colspan=\"3\">&nbsp;</td>
                                    </tr>                                 
                                    <tr>
                                        <td align=\"center\" width=\"400\"><table border=\"1\"><tr><td>
                                            {$MENDAGRI_TEGURAN}
                                            </td></tr></table>
                                        </td>                                        
                                        <td align=\"left\" width=\"60\">&nbsp;</td>
                                        <td align=\"left\">{$PROVINSI},<br/>{$JABATAN}<br/>" . strtoupper($KOTA) . "</td>
                                    </tr>
                                    <tr>
                                        <td colspan=\"3\">&nbsp;</td>
                                    </tr>  
                                    <tr>
                                        <td align=\"left\"></td>
                                        <td align=\"left\"></td>
                                        <td align=\"left\"><u>{$NAMA_TTD}</u><br/>pembina Tk. 1<br/>NIP. {$NIP}</td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            <tr>
                                <td>Tembusan :<br/>
                                	1. Deputi Pencegahan Pemberantasa Korupsi (KPK) RI<br/>
                                    2. Badan Pemeriksa Keuangan (BPK) RI<br/>
                                    3. Plt. Bupati {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}<br/>
                                    4. Inspektur Kabupaten Lampung Selatan<br/>
                                    5. Arsip
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 10, 16, 15, '', '', '', '', false, 200, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-hotel.pdf', 'I');
    }

    private function print_teguran_sptpd() {
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

        $KEPALA_BIDANG_NAMA = $config['BAG_SURAT_TEGURAN_SPTPD_NAMA'];
        $NIP = $config['BAG_SURAT_TEGURAN_SPTPD_NIP'];
        $JABATAN = $config['BAG_SURAT_TEGURAN_SPTPD_JABATAN'];

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" style=\"border:1px #000 solid\" cellspacing=\"0\">
                <tr>
                    <td><table width=\"700\" border=\"0\">
                            <tr>
                                <th valign=\"top\" align=\"center\">                                   
                                    ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />      
									DINAS PENDAPATAN DAERAH<br /><br />        
									<font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                </th>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td><table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"0\" cellspacing=\"0\">                        
                        <tr>
                            <td width=\"450\"><table width=\"440\" class=\"header\" border=\"0\">
                                    <tr>                             
                                        <td width=\"130\"></td>
                                        <td width=\"310\" class=\"first\"></td>                  
                                    </tr>
                                    <tr>                             
                                        <td width=\"130\">Nomor</td>
                                        <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_NO_SURAT']}</td>                  
                                    </tr>
                                    <tr>                             
                                        <td width=\"130\">Sifat</td>
                                        <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_SIFAT']}</td>                  
                                    </tr>
                                    <tr>                             
                                        <td width=\"130\">Lampiran</td>
                                        <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_LAMPIRAN']}</td>                  
                                    </tr>
                                </table>
                            </td>
                            <td width=\"260\"><br/><br/>" . strtoupper($KOTA) . ", {$DATA['pajak']['CPM_TGL_INPUT']}<br/>
                                Kepada Yth <br/>                                
                                {$DATA['pajak']['CPM_NAMA_WP']}<br/>				
                                {$DATA['pajak']['CPM_ALAMAT_OP']}<br/>
                                di {$KOTA}<br/>
                            </td>
                        </tr>                        
                        <tr>
                            <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                    <tr>
                                        <th align=\"left\" colspan=\"2\">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Disampaikan dengan hormat, berdasarkan Peraturan No. 1 Tahun 2014 tentang Perubahan atas Peraturan Daerah No. 15 Tahun 2010 tentang Pajak Daerah, Pasal 68 bahwa :<br/>
                                            Ayat (1) <br/><b>Setiap Wajb Pajak yang membayar sendiri pajak yang terhutang wajib mengisi SPTPD</b><br/>
                                            Ayat (4) <br/><b>SPTPD sebagaimana dimaksud pada ayat (1) harus diisi dengan benar, jelas dan lengkap serta ditanda tangani oleh Wajib Pajak atau Kuasanya.</b><br/>
                                            Ayat (5) <br/><b>SPTPD sebagaimana dimaksud pada ayat (1) harus disampaikan kepada Dinas selambat lambat nya (15) hari setelah berakhirnya masa pajak</b><br/>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th align=\"left\" colspan=\"2\">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sehubungan dengan hal tersebut menurut catatan/data yang ada pada kami bahwa <b>sampai dengan saat ini Saudara belum menyampaikan omzet bulan ".$this->arr_bulan[(int) $DATA['pajak']['CPM_MASA_PAJAK']]." Tahun {$DATA['pajak']['CPM_TAHUN_PAJAK']} Untuk itu kami mengingatkan</b> dan minta agar Saudara segera menyampaikan SPTPD tersebut di atas sebagai  dasar penerbitan SKPD (Surat ketetapan Pajak Daerah)<br/>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th align=\"left\" colspan=\"2\">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Demikian disampaikan, atas perhatian dan kerja samanya diucapkan terima kasih.<br/><br/>
                                        </th>
                                    </tr>
                                </table>            
                            </td>
                        </tr>
                        <tr>
                            <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">                                                                   
                                    <tr>
                                        <td align=\"center\" width=\"400\">
                                        </td>                                        
                                        <td align=\"left\" width=\"60\">&nbsp;</td>
                                        <td align=\"left\">{$JABATAN}<br/><br/><br/></td>
                                    </tr>
                                    <tr>
                                        <td colspan=\"3\">&nbsp;</td>
                                    </tr>  
                                    <tr>
                                        <td align=\"left\"></td>
                                        <td align=\"left\"></td>
                                        <td align=\"left\"><u>{$KEPALA_BIDANG_NAMA}</u><br/>NIP. {$NIP}<br/></td>
                                    </tr>
                                </table>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 38, 16, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-hotel.pdf', 'I');
    }

    function download_excel() {
        echo 'tes';exit;
        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName);
        $devid = explode(";", $_REQUEST['alldevice']);
        $deviceId = "'" . implode("','", $devid) . "'";
        $where = "DeviceId in ({$deviceId}) ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
        $where.= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
        $where.= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between 
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and 
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

        $query = "select 
                        DeviceId, 
                        NotAdmitReason,
                        '{$_REQUEST['CPM_NPWPD']}' as CPM_NPWPD,
                        TransactionNumber,
                        TransactionDate,
                        TransactionAmount as total
                        from {$dbTable} 
                        WHERE {$where} ";


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
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'NPWPD')
                ->setCellValue('C1', 'Device Id')
                ->setCellValue('D1', 'Nomor Transaksi')
                ->setCellValue('E1', 'Tanggal Transaksi')
                ->setCellValue('F1', 'Total Pajak')
                ->setCellValue('G1', 'Alasan Tidak diakui');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['DeviceId'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['TransactionNumber']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['TransactionDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['total']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['NotAdmitReason']);
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Transaksi Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "G"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}
?>

