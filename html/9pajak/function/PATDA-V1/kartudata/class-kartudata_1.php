<?php

class KartuData extends Pajak {

    function __construct() {
        parent::__construct();
        $WP = isset($_POST['WP']) ? $_POST['WP'] : array();

        foreach ($WP as $a => $b) {
            $this->$a = is_array($b) ? $b : mysqli_escape_string($this->Conn, trim($b));
        }
    }

    public function filtering($id) {
		$opt_jenis_pajak = '<option value="">All</option>';
        foreach ($this->arr_pajak as $x => $y) {
            $opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
        }

        $reks = $this->getDataRekening();
        $opt_rekening = '<option value="">All</option>';
        foreach($reks as $header=>$rek){
            $aRek = array_values($rek);
            if(count($aRek)>1){
                $opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
                foreach($rek as $k=>$v){
                    $opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
                }
            }else{
                // $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
                $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
            }
        }
        
        $src_kec = $this->get_list_kecamatan();
		$opt_kecamatan = '<option value="">All</option>';
		foreach($src_kec as $k=>$v){
			$opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
		}
		
		$opt_tahun = "";
        for($x = date("Y")-5;$x<=date("Y");$x++){
			$opt_tahun.= "<option value='{$x}' ".(date("Y")==$x?"selected":"").">{$x}</option>";
        }
		
        $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td>Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >{$opt_jenis_pajak}</select></td>
                        <td>NPWPD :<br><input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" ></td>
                        <td>Tahun :<br><select name=\"CPM_TAHUN-{$id}\" id=\"CPM_TAHUN-{$id}\">{$opt_tahun}</select></td>
                        <td>Nama WP/OP :<br><input type=\"text\" name=\"CPM_NAMA-{$id}\" id=\"CPM_NAMA-{$id}\"></td>
						<td>Alamat :<br><input type=\"text\" name=\"CPM_ALAMAT-{$id}\" id=\"CPM_ALAMAT-{$id}\"></td>
                        <td>Tanggal :<br><input type=\"text\" name=\"date1-{$id}\" id=\"date1-{$id}\" class=\"datepicker\" size=\"10\" /> s/d <input type=\"text\" name=\"date2-{$id}\" id=\"date2-{$id}\" class=\"datepicker\" size=\"10\" /></td>
                    </tr></table>
                    <table><tr valign=\"bottom\">
                        <td>Kecamatan :<br><select name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
                        <td>Kelurahan :<br><select name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=''>All</option></select></td>
                        <td>Kode Rekening :<br><select name=\"CPM_KODEREKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODEREKENING-{$id}\">{$opt_rekening}</select></td>
                        <td><button type=\"submit\" id=\"cari-{$id}\">Cari</button></td>
                        <td colspan=\"5\">
                            <button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel_profilwp('{$id}','function/PATDA-V1/pelayanan/profilwp/svc-download.xls.php');\">Cetak Excel data WP</button>
                            <button type=\"button\" id=\"cetak-kartudata-{$id}\" onclick=\"javascript:download_kartudata_excel('{$id}');\">Cetak Excel Kartu Data</button>
                        </td>
                    </tr></table></form>
                    <style>#keterangan-dialog{
                    width: 100px;
					position: fixed;
					padding: 5px;
					border-top: 5px #7d91a7 solid;
					margin: 0 auto;
					right:20px;
					margin-top:-23px;
					border-radius: 2px;
					border: 1px #999 solid;
					box-shadow: 0px 1px 5px #FFF;
					background-color: #FFF;
					margin-bottom: 10px;
					z-index:9999!important;
					transition:.5s;
					font-weight:normal
                    }
                    #keterangan-dialog:hover{
                    opacity:0;
					}</style>
                    <div id='keterangan-dialog'>
						Keterangan : <br/><span style='background:#1B731E;border-radius:50px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Reguler 
						<br/><span style='background:#3F23DE;border-radius:50px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Non Reguler
                    </div>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "kartudata";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent}.filtering input,.filtering select{height:23px}</style>
                {$this->filtering($this->_i)}
                <div style=\"width:100%;overflow:scroll\">
					<div id=\"laporanPajak-{$this->_i}\" style=\"width:1800px;\"></div>
                </div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('.datepicker').datepicker({
							dateFormat: 'yy-mm-dd',
							changeYear: true,
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
                            defaultSorting: 'CPM_JENIS_PAJAK ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '5px'},
                                CPM_NPWPD: {title: 'NPWPD',key: true,width: '10px'},                                 
                                CPM_NAMA_OP: {title: 'Nama OP',width: '10px'},                                
								CPM_ALAMAT_OP: {title: 'Alamat OP',width: '10px'},
								CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10px'},
								BLN1: {title: 'Januari',width: '10px',sorting: false},
								BLN2: {title: 'Februari',width: '10px',sorting: false},
								BLN3: {title: 'Maret',width: '10px',sorting: false},
								BLN4: {title: 'April',width: '10px',sorting: false},
								BLN5: {title: 'Mei',width: '10px',sorting: false},
								BLN6: {title: 'Juni',width: '20px',sorting: false},
								BLN7: {title: 'Juli',width: '20px',sorting: false},
								BLN8: {title: 'Agustus',width: '10px',sorting: false},
								BLN9: {title: 'September',width: '10px',sorting: false},
								BLN10: {title: 'Oktober',width: '10px',sorting: false},
								BLN11: {title: 'November',width: '10px',sorting: false},
								BLN12: {title: 'Desember',width: '10px',sorting: false},
								BLN13: {title: 'Total',width: '10px',sorting: false}
								
                            },
                            recordsLoaded: function(event, data) {
								get_status_detail();
								get_total_detail();
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NAMA : $('#CPM_NAMA-{$this->_i}').val(),
								CPM_ALAMAT : $('#CPM_ALAMAT-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                				CPM_TAHUN : $('#CPM_TAHUN-{$this->_i}').val(),
								CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
								CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
								CPM_KODEREKENING : $('#CPM_KODEREKENING-{$this->_i}').val(),
								DATE1 : $('#date1-{$this->_i}').val(),
								DATE2 : $('#date2-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();

                        $('#CPM_TAHUN-{$this->_i}').change(function(){
							$('#cari-{$this->_i}').click();
                        });
                        
                        $('#CPM_KECAMATAN-{$this->_i}').change(function(){
							var KEC_ID = $(this).val();
							$.ajax({
								url:'function/{$DIR}/airbawahtanah/lapor/svc-lapor.php',   
								type:'post',
								data:{function:'get_list_kelurahan',CPM_KEC_ID:KEC_ID},
								cache:false,
								async:false,
								beforeSend: function() {
									$('#CPM_KELURAHAN-{$this->_i}').html('<option value=\"\">---Loading...--</option>');
								},
								success: function(html){
									$('#CPM_KELURAHAN-{$this->_i}').html('<option value=\"\">All</option>'+html);
								}
							});
						});
                        
                    });
                    function get_status_detail(){
						 var allid = '';
						 $('.detail-nop').val('loading...');
						 $('.detail-nop').each(function(){
							allid += $(this).attr('id')+'|';
						 })
						 $.ajax({
							 type:'post',
							 data:{allid:allid},
							 url : 'view/{$DIR}/{$modul}/svc-kartudata.php?function=get_status_detail&a={$this->_a}&m={$this->_m}',
							 dataType:'json',
							 success:function(data){
								console.log(data);
								$('.detail-nop').val('Detail');
								$('.detail-nop').hide();
								
								for(var x in data){
									if(data[x] > 0 && data[x] != ''){
										$('.'+x).show();
									}
									
								}
								
							 }
						 });
					}
					
					function get_total_detail(){
						 var allid = '';
						 $('.total_detail').val('loading...');
						 $('.total_detail').each(function(){
							allid += $(this).attr('id')+'|';
						 })
						 $.ajax({
							 type:'post',
							 data:{allid:allid,tahun:$('#CPM_TAHUN-{$this->_i}').val()},
							 url : 'view/{$DIR}/{$modul}/svc-kartudata.php?function=get_total_detail&a={$this->_a}&m={$this->_m}',
							 dataType:'json',
							 success:function(data){
								for(var x in data){
									$('.'+x).html(data[x]);
								}
							 }
						 });
					}
                </script>";
        echo $html;
    }

	private function getSpanTotal($id, $jenis, $bulan){
		$reg = $id.$this->idpajak_sw_to_gw[$jenis].$bulan;
		$nonreg = $id.$this->non_reguler[$jenis].$bulan;
		
		$creg = str_replace('.','',$reg);
		$cnonreg = str_replace('.','',$nonreg);
		
		return "<span class='total_detail {$creg}' style='color:#1B731E' id='{$reg}'>0</span> <br/>
		<span class='total_detail {$cnonreg}' id='{$nonreg}' style='color:#3F23DE'>0</span>";
	}
	
	private function getSpanTotalxls($id, $jenis, $bulan, $type){
		$reg = $id.$this->idpajak_sw_to_gw[$jenis].$bulan.$type;
		$nonreg = $id.$this->non_reguler[$jenis].$bulan.$type;
		return $type == 1 ? "{{$reg}}" : "{{$nonreg}}";
	}
	
    public function grid_data() {
        try {
            $where = "CPM_AKTIF = '1'";
  
			$tahun = (isset($_REQUEST['CPM_TAHUN']) && $_REQUEST['CPM_TAHUN'] != "") ? $_REQUEST['CPM_TAHUN'] : date("Y");
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
            $where.= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND CPM_ALAMAT_OP like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND CPM_KECAMATAN_OP =  '{$_REQUEST['CPM_KECAMATAN']}'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND CPM_KELURAHAN_OP =  '{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where.= (isset($_REQUEST['DATE1']) && $_REQUEST['DATE2'] != "") ? " AND DATE(b.CPM_TGL_UPDATE) BETWEEN '{$_REQUEST['DATE1']}' AND '{$_REQUEST['DATE2']}'" : "";
            if(isset($_REQUEST['CPM_KODEREKENING']) && $_REQUEST['CPM_KODEREKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODEREKENING'])==9)
                    $where.= " AND CPM_REKENING like '{$_REQUEST['CPM_KODEREKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODEREKENING'])>9)
                    $where.= " AND CPM_REKENING = '{$_REQUEST['CPM_KODEREKENING']}' ";
            }
            
            $sql = "SELECT DISTINCT CPM_NO, CPM_TABLE FROM PATDA_JENIS_PAJAK";
            $res = mysqli_query($this->Conn, $sql);

            while ($row = mysqli_fetch_assoc($res)) {
                $arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
            }

            if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
                $arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
            }

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM (";
            foreach ($arrPajak as $idpjk => $pjk) {
                $query .= "(SELECT CPM_ID, {$idpjk} AS CPM_JENIS_PAJAK
                        FROM PATDA_{$pjk}_PROFIL
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
                $query .= "(SELECT a.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, a.CPM_NPWPD, a.CPM_NAMA_WP, a.CPM_NAMA_OP, a.CPM_ALAMAT_OP
                        FROM PATDA_{$pjk}_PROFIL a INNER JOIN PATDA_WP b on b.CPM_NPWPD = a.CPM_NPWPD 
                        WHERE {$where} ) UNION";
            } 
            
            $query = substr($query, 0, strlen($query) - 5);
            $query.= ") as profil ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			// echo $query; exit;
            
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $jns_pajak = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
                $npwpd = $row['CPM_NPWPD'];
                $jenis = $row['CPM_JENIS_PAJAK'];
                
                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_NPWPD']}&s={$row['CPM_ID']}&i={$this->_i}";
                $url = "main.php?param=" . base64_encode($base64);
                $json = base64_encode($this->Json->encode(array("tahun"=>$tahun,"npwpd"=>$npwpd,"jenis"=>$jenis)));
                
                $row['CPM_NPWPD'] = "<a href=\"javascript:toExcel('{$row['CPM_NPWPD']}','{$row['CPM_JENIS_PAJAK']}','{$jns_pajak}')\">{$row['CPM_NPWPD']}</a><br/>".
                "<input type=\"button\" id=\"{$npwpd}{$tahun}\" class=\"btn btn-default detail-nop btn-xs\" onclick=\"javascript:getDetail('{$json}')\" value=\"Lihat Daftar Transaksi\">";
                
                $row['CPM_JENIS_PAJAK'] = $jns_pajak;
                		
				$row['BLN1'] = $this->getSpanTotal($npwpd.$tahun, $jenis, '01');
				$row['BLN2'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'02');
				$row['BLN3'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'03');
				$row['BLN4'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'04');
				$row['BLN5'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'05');
				$row['BLN6'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'06');
				$row['BLN7'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'07');
				$row['BLN8'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'08');
				$row['BLN9'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'09');
				$row['BLN10'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'10');
				$row['BLN11'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'11');
				$row['BLN12'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'12');
				$row['BLN13'] = $this->getSpanTotal($npwpd.$tahun, $jenis,'');

                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['Q'] = $query;
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
    
    
    public function grid_table_detail() {
        $DIR = "PATDA-V1";
        $modul = "kartudata";
        
        $html = "<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: false,
                            sorting: false,
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-detail-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                npwpd: {title: 'NPWPD',key: true},
                                bulan: {title: 'Bulan Pajak ({$_REQUEST['tahun']})',width: '15%'},
                                jenis: {title: 'Jenis Pajak',width: '15%'},
								reguler: {title: 'Reguler',width: '20%'},
								nonreguler: {title: 'Non Reguler',width: '20%'}
                            }
                        });
                        
						$('#laporanPajak-{$this->_i}').jtable('load', {
							npwpd : '{$_REQUEST['npwpd']}',
							tahun : '{$_REQUEST['tahun']}',
							jenis : '{$_REQUEST['jenis']}'
						});
                    });                    
                </script>";
        echo $html;
    }

    public function grid_data_detail() {
        try {
			
			$arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysql_select_db($dbName, $Conn_gw);
            
            $tahun = (isset($_REQUEST['tahun']) && $_REQUEST['tahun'] != "")? $_REQUEST['tahun'] : "";
            $jenis = (isset($_REQUEST['jenis']) && $_REQUEST['jenis'] != "")? $_REQUEST['jenis'] : "";
            $npwpd = (isset($_REQUEST['npwpd']) && $_REQUEST['npwpd'] != "")? $_REQUEST['npwpd'] : "";
            
            $where = " 1=1 "; 
			$where .= $tahun!="" ? " AND simpatda_tahun_pajak='{$tahun}'" : "";
			$where .= $npwpd!="" ? " AND npwpd='{$npwpd}'" : "";
			
			$where .= $jenis!="" ? "AND (
				simpatda_type='{$this->idpajak_sw_to_gw[$jenis]}' OR
				simpatda_type='{$this->non_reguler[$jenis]}')" : "";
			
			$query = "select count(*) as RecordCount
			FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
			WHERE {$where}";
			
			$result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

			$query = "select jenis, simpatda_type, op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak, date_format(masa_pajak_awal,'%m') as bulan_awal,patda_total_bayar,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, payment_paid 
					FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					WHERE {$where} ORDER BY simpatda_bulan_pajak ASC";
					
			#echo $query;exit;
            $result = mysqli_query($Conn_gw, $query);

            $rows = array();
            $no = 0;
            $totalReg = 0;
            $totalNonReg = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['bulan'] = isset($this->arr_bulan[(int) $row['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $row['simpatda_bulan_pajak']] : "";
                $row['bulan'] = ($row['bulan'] == "")? (isset($this->arr_bulan[(int) $row['bulan_awal']]) ? $this->arr_bulan[(int) $row['bulan_awal']] : "") : $row['bulan'];
                $row['reguler'] = '0';
                $row['nonreguler'] = '0';
                
                if(array_search($row['simpatda_type'], $this->non_reguler) !== false){
					$row['nonreguler'] = $row['patda_total_bayar'];
					$totalNonReg += $row['patda_total_bayar'];
				}else{
					$row['reguler'] = $row['patda_total_bayar'];
					$totalReg += $row['patda_total_bayar'];
				}
                $rows[] = $row;
            }
            
            $rows[] = array(
            'NO'=>'',
            'npwpd'=>'',
            'bulan'=>'',
            'jenis'=>"<b>Total</b> <input type=\"button\" class=\"btn btn-default btn-xs\" onclick=\"javascript:download_execute('{$npwpd}', '{$tahun}', '{$jenis}')\" value=\"Download Excel\">",
            'reguler'=> "<b>{$totalReg}</b>",
            'nonreguler'=>"<b>{$totalNonReg}</b>");
            

            $jTableResult = array();
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
	
	
	public function get_status_detail() {
        try {
			
			$arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysql_select_db($dbName, $Conn_gw);
            
            $allid = isset($_POST['allid'])? substr($_POST['allid'],0,strlen($_POST['allid'])-1) : ""; #substr menghilangkan tanda '|' di akhir 
			$arr_id = explode("|",$allid); #memecah semua device id menjadi array untuk keperluan query
			$where_id = "'".implode("','",$arr_id)."'"; #untuk dipakai di query DeviceId in ('123213','123213','13213')
            
            $field_id = "concat(npwpd,simpatda_tahun_pajak)";
            $where = " {$field_id} in ({$where_id}) ";
			
			$query = "select {$field_id} as id, count(*) as RecordCount
			FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
			WHERE {$where} GROUP BY {$field_id}";
			
			$result = mysqli_query($Conn_gw, $query);
			$res = array();
            while($row = mysqli_fetch_assoc($result)){
				$row['id'] = str_replace('.','',$row['id']);
				$res[$row['id']] = $row['RecordCount'];
			}
			
			print $this->Json->encode($res);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
    public function get_total_detail() {
        try {
			
			$arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysql_select_db($dbName, $Conn_gw);
            
            $allid = isset($_POST['allid'])? substr($_POST['allid'],0,strlen($_POST['allid'])-1) : ""; #substr menghilangkan tanda '|' di akhir 
			$arr_id = explode("|",$allid); #memecah semua device id menjadi array untuk keperluan query
			$where_id = "'".implode("','",$arr_id)."'"; #untuk dipakai di query DeviceId in ('123213','123213','13213')
            
            $field_id = "concat(npwpd,simpatda_tahun_pajak,simpatda_type,date_format(masa_pajak_awal,'%m'))";
            $where = " {$field_id} in ({$where_id}) ";
			
			$query = "SELECT 
					{$field_id} AS id, 
					simpatda_tahun_pajak as tahun,
					npwpd, 
					b.id_sw as jenis,
					date_format(masa_pajak_awal,'%m') as bulan,
					sum(patda_total_bayar) as total
					FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					WHERE {$where} and a.payment_flag='1'
					GROUP BY concat(npwpd, simpatda_tahun_pajak,simpatda_type,date_format(masa_pajak_awal,'%m'))";
			
			
			$result = mysqli_query($Conn_gw, $query);
			$res = array();
			$res_nominal = array();
            while($row = mysqli_fetch_assoc($result)){
				$npwpd = $row['npwpd'];
				$tahun = $row['tahun'];
				$jenis = $row['jenis'];
				$bulan = $row['bulan']; 
				
				$row['id'] = str_replace('.','',$row['id']);
				
				$res[$row['id']] = (int)$row['total']>0? "<a href='javascript:void(0)' style='color:inherit!important' onclick=\"javascript:download_execute('{$npwpd}', '{$tahun}', '{$jenis}', '{$bulan}')\">{$row['total']}</a>" : (int) $row['total'];
				$res_nominal[$row['id']] = $row['total'];
			}
			
			$tmp = $res_nominal;
			foreach($tmp as $a=>$b){
				$id = substr($a,0,strlen($a)-2);
				$id = str_replace('.','',$id);
				
				if(isset($res[$id])){
					$res[$id]+= $b;
				}else{
					$res[$id] = $b;
				}
			}
			
			print $this->Json->encode($res);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
    
    public function download_xls() {
        
        if(isset($_REQUEST['bulan']) && $_REQUEST['bulan']!=''){
            $this->download_xls_npwpd();
        }else{
            if($_REQUEST['jns_pajak']==8 || $_REQUEST['jnspajak']==8){
                $this->download_xls_restoran();
            }else{
                $this->download_xls_npwpd();
            }
        }
    }

    function download_xls_restoran(){
        $arr_config = $this->get_config_value($this->_a);

        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbUser = $arr_config['PATDA_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

        $where = " payment_flag=1 ";
        $where .= $_REQUEST['jnspajak'] == "" ? "" : " AND b.id_sw='{$_REQUEST['jnspajak']}'";
        $where .= $_REQUEST['tahun'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['tahun']}'";
        $where .= $_REQUEST['npwpd'] == "" ? "" : " AND npwpd='{$_REQUEST['npwpd']}'";
        // $where .= (!isset($_REQUEST['bulan']) || $_REQUEST['bulan'] == "") ? "" : " AND date_format(masa_pajak_awal,'%m') ='{$_REQUEST['bulan']}'";
        
        // data pembayaran
        $query = "select c.CPM_TGL_LAPOR as tanggal, SUM(c.CPM_TOTAL_OMZET) as total_bayar, SUM(simpatda_dibayar) as total_setor, 
                    MONTH(masa_pajak_awal) as bulan
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    INNER JOIN SW_PATDA.PATDA_RESTORAN_DOC c on c.CPM_ID=a.id_switching
                    WHERE {$where} ORDER BY simpatda_bulan_pajak ASC";
        //echo $query;
        //exit;
        
        $result = mysqli_query($Conn_gw, $query);
        // data meja
        $data = array();
        if($_REQUEST['tahun']==date('Y')){
            for($i=1;$i<=date('n');$i++) $data[$i] = array('tanggal'=>'', 'bulan'=>$i, 'total_bayar'=>0, 'total_setor'=>0);
        }else{
            for($i=1;$i<=12;$i++) $data[$i] = array('tanggal'=>'', 'bulan'=>$i, 'total_bayar'=>0, 'total_setor'=>0);
        }
        while ($rowData = mysqli_fetch_assoc($result)) {
            $data[$rowData['bulan']] = $rowData;
        }
        // data op
        $res_op = mysqli_query($this->Conn, "SELECT * from PATDA_RESTORAN_PROFIL WHERE CPM_NPWPD='{$_REQUEST['npwpd']}' AND CPM_AKTIF='1' order by CPM_TGL_UPDATE desc limit 1");
        $op = mysqli_fetch_assoc($res_op);
		
		// Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

		// Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("pbb")
                ->setKeywords("-");
        
                $objPHPExcel->getActiveSheet()
                ->mergeCells("A1:F1")
                ->mergeCells("A2:F2")
                ->mergeCells("A3:F3")
                ->mergeCells("A5:F5")
                ->mergeCells("A12:F12")
                ->mergeCells("A7:C7")->mergeCells("D7:F7")
                ->mergeCells("A8:C8")->mergeCells("D8:F8")
                ->mergeCells("A9:C9")->mergeCells("D9:F9")
                ->mergeCells("A10:C10")->mergeCells("D10:F10")
                ->mergeCells("C13:D13")
                ;

		// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'KARTU DATA')
                ->setCellValue('A2', 'PAJAK RESTORAN')
                ->setCellValue('A3', 'Tahun Pajak '.$_REQUEST['tahun'])
                ->setCellValue('A5', 'N.P.W.P.D : '.Pajak::formatNPWPD($_REQUEST['npwpd']))
                ->setCellValue('A7', '1. Nama Restoran')->setCellValue('D7', ': '.$op['CPM_NAMA_OP'])
                ->setCellValue('A8', '2. Alamat')->setCellValue('D8', ': '.$op['CPM_ALAMAT_OP'])
                ->setCellValue('A9', '3. Nama Pemilik')->setCellValue('D9', ': '.$op['CPM_NAMA_WP'])
                ->setCellValue('A10', '4. Alamat')->setCellValue('D10', ': '.$op['CPM_ALAMAT_WP'])
                ->setCellValue('A12', 'A. Objek Restoran')
                ->setCellValue('B13', 'No')
                ->setCellValue('C13', 'Jumlah Meja')
                ->setCellValue('E13', 'Jumlah Kursi')
                ->setCellValue('F13', 'Jumlah Tamu Per Hari');
        $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(18);


		// Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);
        $numRows = count($data);
        $total = array();
        
        $row = 14;

        /* A. Objek Restoran (data meja) */
        // data meja
        $rmeja = mysqli_query($this->Conn, "SELECT * from PATDA_RESTORAN_PROFIL_DETAIL WHERE CPM_NPWPD='{$_REQUEST['npwpd']}'");

        // style meja: border
        $objPHPExcel->getActiveSheet()->getStyle('A13:F'.($row-1))->applyFromArray(
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
        $total['meja'] = 0;
        $total['kursi'] = 0;
        $total['pengunjung'] = 0;
        $no = 1;
        while($roww = mysqli_fetch_assoc($rmeja)){
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.$row, $no)
                ->setCellValue('C'.$row, $roww['CPM_JUMLAH_MEJA'])
                ->setCellValue('E'.$row, $roww['CPM_JUMLAH_KURSI'])
                ->setCellValue('F'.$row, $roww['CPM_JUMLAH_PENGUNJUNG']);
                $total['meja'] += $roww['CPM_JUMLAH_MEJA'];
                $total['kursi'] += $roww['CPM_JUMLAH_KURSI'];
                $total['pengunjung'] += $roww['CPM_JUMLAH_PENGUNJUNG'];
            $objPHPExcel->getActiveSheet()->mergeCells("C{$row}:D{$row}");
            $no++;
            $row++;
        }
        // style border meja
        $objPHPExcel->getActiveSheet()->getStyle('B13:F'.($row-1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );
        // style meja align no
        $objPHPExcel->getActiveSheet()->getStyle('B14:B'.($row-1))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        // format angka
        $objPHPExcel->getActiveSheet()->getStyle('C14:F'.($row-1))->getNumberFormat()->setFormatCode('#,##0');

        echo json_encode($op);

        $opsi = array(1=>'1 - Ya', '2 - Tidak');
        $opsi_cashregister = $op['CPM_CASHREGISTER']!='' ? $opsi[$op['CPM_CASHREGISTER']] : '-';
        $opsi_pembukuan = $op['CPM_PEMBUKUAN']!='' ? $opsi[$op['CPM_PEMBUKUAN']] : '-';

        $row++;
        /* B. Objek Restoran */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$row, 'B. Diisi Untuk Objek Restoran')
                ->setCellValue('B'.($row+1), '1. Menggunakan Cash Register')->setCellValue('F'.($row+1), ': '.$opsi_cashregister)
                ->setCellValue('B'.($row+2), '2. Menggunakan Pembukuan / Pencatatan')->setCellValue('F'.($row+2), ': '.$opsi_pembukuan)
                ->setCellValue('B'.($row+3), '3. Jumlah Pembayaran dan Penyetoran yang dilakukan');
        $objPHPExcel->getActiveSheet()
                ->mergeCells("A{$row}:F{$row}")
                ->mergeCells("B".($row+1).":F".($row+1))
                ->mergeCells("B".($row+2).":F".($row+2))
                ->mergeCells("B".($row+3).":F".($row+3));
        $row += 4;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.$row, 'No')
                ->setCellValue('C'.$row, 'Tanggal')
                ->setCellValue('D'.$row, 'Masa')
                ->setCellValue('E'.$row, 'Jumlah Pembayaran (Rp)')
                ->setCellValue('F'.$row, 'Setoran (Rp)');
        // style header pembayaran
        $objPHPExcel->getActiveSheet()->getStyle('B'.($row).':F'.($row))->applyFromArray(
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
        $row++;
        // data pembayaran
		$no = $offset+1;
        $start = $row;
        $total['bayar'] = 0;
        $total['setor'] = 0;


        
        if($numRows>0){
            foreach ($data as $rowData) {
                $totalPayment += $rowData['simpatda_dibayar'];
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, ($no));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$row, $rowData['tanggal'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $this->arr_bulan[$rowData['bulan']]);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $rowData['total_bayar']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $rowData['total_setor']);
                $total['bayar'] += $rowData['total_bayar'];
                $total['setor'] += $rowData['total_setor'];
                $row++;
                $no++;
            }
        } 
        // row total
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, 'Total');
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $total['bayar']);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $total['setor']);
        $objPHPExcel->getActiveSheet()->mergeCells("B{$row}:D{$row}");
        // style border pembayaran
        $objPHPExcel->getActiveSheet()->getStyle('B'.($start-1).':F' . $row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );
        // style pembayaran align no
        $objPHPExcel->getActiveSheet()->getStyle('B'.$start.':C'.$row)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':F' . $row)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
        // format angka pembayaran
        $objPHPExcel->getActiveSheet()->getStyle('E'.$start.':F'.$row)->getNumberFormat()->setFormatCode('#,##0');

        // tanda tangan
        $objPHPExcel->getActiveSheet()
                    ->setCellValue('A'.($row+2), 'Kepala Sub Bidang')
                    ->setCellValue('A'.($row+3), 'Perhitungan dan Penetapan')
                    ->setCellValue('A'.($row+7), $arr_config['BAG_VERIFIKASI_NAMA'])
                    ->setCellValue('A'.($row+8), 'NIP. '.$arr_config['BAG_VERIFIKASI_NIP'])

                    ->setCellValue('E'.($row+2), 'Staff Bidang')
                    ->setCellValue('E'.($row+3), 'Pengembangan dan Penetapan')
                    ->setCellValue('E'.($row+7), $arr_config['KASIE_PENETAPAN_NAMA'])
                    ->setCellValue('E'.($row+8), 'NIP. '.$arr_config['KASIE_PENETAPAN_NIP'])

                    ->setCellValue('A'.($row+11), 'Kepala Bidang')
                    ->setCellValue('A'.($row+12), 'Pengembangan dan Penetapan')
                    ->setCellValue('A'.($row+16), $arr_config['KABID_PENDATAAN_NAMA'])
                    ->setCellValue('A'.($row+17), 'NIP. '.$arr_config['KABID_PENDATAAN_NIP']);
        $boldItalic = array(
            'font'=> array(
                'bold'=>true,
                'underline'=>PHPExcel_Style_Font::UNDERLINE_SINGLE,
            )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A'.($row+7))->applyFromArray($boldItalic);
        $objPHPExcel->getActiveSheet()->getStyle('E'.($row+7))->applyFromArray($boldItalic);
        $objPHPExcel->getActiveSheet()->getStyle('A'.($row+16))->applyFromArray($boldItalic);

        $objPHPExcel->getActiveSheet()
                    ->mergeCells('A'.($row+2).':D'.($row+2))
                    ->mergeCells('A'.($row+3).':D'.($row+3))
                    ->mergeCells('A'.($row+7).':D'.($row+7))
                    ->mergeCells('A'.($row+8).':D'.($row+8))
                    ->mergeCells('E'.($row+2).':F'.($row+2))
                    ->mergeCells('E'.($row+3).':F'.($row+3))
                    ->mergeCells('E'.($row+7).':F'.($row+7))
                    ->mergeCells('E'.($row+8).':F'.($row+8))
                    ->mergeCells('A'.($row+11).':F'.($row+11))
                    ->mergeCells('A'.($row+12).':F'.($row+12))
                    ->mergeCells('A'.($row+16).':F'.($row+16))
                    ->mergeCells('A'.($row+17).':F'.($row+17));
        $objPHPExcel->getActiveSheet()->getStyle('A'.($row+2).':F'.($row+17))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );

		// Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Kartu Data');

        // style judul
        $objPHPExcel->getActiveSheet()->getStyle('A1:F5')->applyFromArray(
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

		
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);

        ob_clean();

		$nmfile = "kartu-data-{$_REQUEST['npwpd']}-".date("dmYHis");
        header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');        
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
        $objWriter->save('php://output');
        mysqli_close($Conn_gw);
    }

    function download_xls_npwpd(){
        $arr_config = $this->get_config_value($this->_a);

        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbUser = $arr_config['PATDA_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName, $Conn_gw);

        $where = " payment_flag=1 ";
        $where .= $_REQUEST['jnspajak'] == "" ? "" : " AND b.id_sw='{$_REQUEST['jnspajak']}'";
        $where .= $_REQUEST['tahun'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['tahun']}'";
        $where .= $_REQUEST['npwpd'] == "" ? "" : " AND npwpd='{$_REQUEST['npwpd']}'";
        $where .= (!isset($_REQUEST['bulan']) || $_REQUEST['bulan'] == "") ? "" : " AND date_format(masa_pajak_awal,'%m') ='{$_REQUEST['bulan']}'";
        
        $query = "select jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
                    date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, 
                    date_format(saved_date,'%d-%m-%Y') as saved_date, payment_paid, simpatda_type,
                    date_format(masa_pajak_awal,'%m') as bulan_awal
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    WHERE {$where} ORDER BY simpatda_bulan_pajak ASC";
        $result = mysqli_query($Conn_gw, $query);
		
		// Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

		// Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("pbb")
                ->setKeywords("-");

		// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'NPWPD')
                ->setCellValue('C1', 'Jenis Pajak')
                ->setCellValue('D1', 'No. SPTPD')
                ->setCellValue('E1', 'Nama WP')
                ->setCellValue('F1', 'Alamat WP')
                ->setCellValue('G1', 'Tahun Pajak')
                ->setCellValue('H1', 'Bulan Pajak')
                ->setCellValue('I1', 'Tanggal Lapor')
                ->setCellValue('J1', 'Tgl Jatuh Tempo')
                ->setCellValue('K1', 'Tagihan (Rp)')
                ->setCellValue('L1', 'Status')
                ->setCellValue('M1', 'Tanggal Bayar');

		// Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

		$no = $offset+1;
        $row = 2;
        $sumRows = mysqli_num_rows($result);
        $totalPayment = 0;
        $numRows = mysqli_num_rows($result);
        while ($rowData = mysqli_fetch_assoc($result)) {
            $tgl_jth_tempo = explode('-', $rowData['expired_date']);
            if (count($tgl_jth_tempo) == 3)
                $tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];
                
            $bulan = isset($this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']] : "";
            $bulan = ($bulan == "")? (isset($this->arr_bulan[(int) $rowData['bulan_awal']]) ? $this->arr_bulan[(int) $rowData['bulan_awal']] : "") : $bulan;
            
            $totalPayment += $rowData['simpatda_dibayar'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['jenis']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['wp_nama']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_alamat']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['simpatda_tahun_pajak']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $bulan);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['saved_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $tgl_jth_tempo);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['simpatda_dibayar']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : 'Belum Lunas');
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['payment_paid']);
			$row++;
			$no++;
        }

        if($numRows>0){
			$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, "=SUM(K2:K" . ($row - 1) . ")");
			$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "");
			$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "");
        }
        $sumRows++;

		// Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Kartu Data');

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
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:M{$row}")->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:M' . ($sumRows + 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('I2:M' . ($sumRows + 1))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->getStartColor()->setRGB('E4E4E4');
        $objPHPExcel->getActiveSheet()->getStyle('A2:A' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B2:G' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('H2:J' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('K2:K' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('L2:L' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);

        ob_clean();

		$nmfile = "kartu-data-{$_REQUEST['npwpd']}-".date("dmYHis");
        header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');        
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    function download_kartudata_xls() {
    
		/*$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Name');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Email');
		
		$objPHPExcel->getActiveSheet()->setTitle('Emplyoee profile');

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Salary');
		$objPHPExcel->getActiveSheet()->setTitle('Emplyoee Salary');
		
		ob_clean();
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="kartudata-' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');*/

        $objPHPExcel = new PHPExcel();
   
		ini_set('memory_limit', '512M');
		ini_set("max_input_time", "180");
		ini_set("max_execution_time", "100000");
        
        $sql_kec = "SELECT * FROM PATDA_MST_KECAMATAN";
        $res_kec = mysqli_query($this->Conn, $sql_kec);
        
        $z = 0;
        // while($rows = mysql_fetch_assoc($res_kec, MYSQL_ASSOC)){

            
            //$objPHPExcel->setActiveSheetIndex($z);
            //$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
            //$objPHPExcel->getActiveSheet()->setCellValue('B1', 'KECAMATAN');

            //$objPHPExcel->getActiveSheet()->setCellValue("A1","IDNYA");
            //$objPHPExcel->getActiveSheet()->setCellValue("B2","KECAMATANNYA");
        
        $id_kecamatan = $rows["CPM_KEC_ID"];

        $where = "CPM_AKTIF = '1'";
		$tahun = (isset($_REQUEST['CPM_TAHUN']) && $_REQUEST['CPM_TAHUN'] != "") ? $_REQUEST['CPM_TAHUN'] : date("Y");
		$where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where.= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
		$where.= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND CPM_ALAMAT_OP like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";
		$where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND CPM_KECAMATAN_OP =  '{$_REQUEST['CPM_KECAMATAN']}'" : "";
        $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND CPM_KELURAHAN_OP =  '{$_REQUEST['CPM_KELURAHAN']}' " : "";
        $where.= (isset($_REQUEST['DATE1']) && $_REQUEST['DATE2'] != "") ? " AND DATE(b.CPM_TGL_UPDATE) BETWEEN '{$_REQUEST['DATE1']}' AND '{$_REQUEST['DATE2']}'" : "";
        if(isset($_REQUEST['CPM_KODEREKENING']) && $_REQUEST['CPM_KODEREKENING'] != ""){
            if(strlen($_REQUEST['CPM_KODEREKENING'])==9)
                $where.= " AND CPM_REKENING like '{$_REQUEST['CPM_KODEREKENING']}%' ";
            elseif(strlen($_REQUEST['CPM_KODEREKENING'])>9)
                $where.= " AND CPM_REKENING = '{$_REQUEST['CPM_KODEREKENING']}' ";
        }
		
		$sql = "SELECT DISTINCT CPM_NO, CPM_TABLE FROM PATDA_JENIS_PAJAK";
		$res = mysqli_query($this->Conn, $sql);

		while ($row = mysqli_fetch_assoc($res)) {
			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
		}


		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
		}

		#query select list data        
		$query = "SELECT profil.* FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
            $query .= "(SELECT a.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, a.CPM_NPWPD, a.CPM_NAMA_WP, a.CPM_NAMA_OP, a.CPM_ALAMAT_OP
                    FROM PATDA_{$pjk}_PROFIL a INNER JOIN PATDA_WP b on b.CPM_NPWPD = a.CPM_NPWPD 
                    WHERE {$where} ) UNION";
		}
		$query = substr($query, 0, strlen($query) - 5);
		$query.= ") as profil ORDER BY CPM_JENIS_PAJAK ASC";
		$result = mysqli_query($this->Conn, $query);
		
        // Create new PHPExcel object
        //$objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpos")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("patda")
                ->setKeywords("-");


		$objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('B1', 'KARTU DATA')
                ->setCellValue('B2', "TAHUN {$tahun}");
                
        // Add some data
        $objPHPExcel->setActiveSheetIndex($z)
                ->setCellValue('A5', 'No.')
                ->setCellValue('B5', 'NPWPD')
                ->setCellValue('C5', 'Nama OP')
                ->setCellValue('D5', 'Alamat OP')
                ->setCellValue('E5', 'Jenis Pajak')
                ->setCellValue('F5', 'Bulan')
                ->setCellValue('F6', 'Januari')
                ->setCellValue('H6', 'Februari')
                ->setCellValue('J6', 'Maret')
                ->setCellValue('L6', 'April')
                ->setCellValue('N6', 'Mei')
                ->setCellValue('P6', 'Juni')
                ->setCellValue('R6', 'Juli')
                ->setCellValue('T6', 'Agustus')
                ->setCellValue('P6', 'September')
                ->setCellValue('X6', 'Oktober')
                ->setCellValue('Z6', 'November')
                ->setCellValue('AB6', 'Desember')
                ->setCellValue('AD5', 'Jumlah');
		
		for($col = "A";$col<="E";$col++){
			$objPHPExcel->setActiveSheetIndex($z)->mergeCells("{$col}5:{$col}7");
		}
		
		$col = "F";
		for($x=1;$x<13;$x++){
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue("{$col}7", 'Reguler');
			$objPHPExcel->setActiveSheetIndex($z)->mergeCells("{$col}6:".(++$col)."6");
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue("{$col}7", 'Non Reguler');
			$col++;
		}
		
		$objPHPExcel->setActiveSheetIndex($z)->setCellValue("{$col}7", 'Reguler');
		$objPHPExcel->setActiveSheetIndex($z)->setCellValue((++$col)."7", 'Non Reguler');
		
		$objPHPExcel->setActiveSheetIndex($z)->mergeCells('H5:AB5');
		$objPHPExcel->setActiveSheetIndex($z)->mergeCells('AD5:AE6');
		
        // Miscellaneous glyphs, UTF-8	
        $objPHPExcel->setActiveSheetIndex($z);

        $row = 7;
        $row++;		
		$no = 1;
		while ($dataRow = mysqli_fetch_assoc($result)) {
			$jenis = $dataRow['CPM_JENIS_PAJAK'];
			$jns_pajak = $this->arr_pajak[$jenis];
			$npwpd = $dataRow['CPM_NPWPD'];
			
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue('A'.$row, $no++);
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue('B'.$row, $npwpd);
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue('C'.$row, $dataRow['CPM_NAMA_OP']);
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue('D'.$row, $dataRow['CPM_ALAMAT_OP']);
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue('E'.$row, $jns_pajak);
			$col="F";
			for($x=1;$x<=13;$x++){
				$bln = str_pad($x,2,'0',STR_PAD_LEFT);
				$objPHPExcel->setActiveSheetIndex($z)->setCellValue($col.$row, $this->getSpanTotalxls($npwpd.$tahun, $jenis, $bln, 1));
				$objPHPExcel->setActiveSheetIndex($z)->setCellValue((++$col).$row, $this->getSpanTotalxls($npwpd.$tahun, $jenis, $bln, 2));
				$col++;
			}
			$row++;
		}

		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysql_select_db($dbName, $Conn_gw);
		
		
		$where = " 1=1 ";
		$where.= (isset($_REQUEST['CPM_TAHUN']) && $_REQUEST['CPM_TAHUN'] != "") ? " AND simpatda_tahun_pajak = '{$_REQUEST['CPM_TAHUN']}'" : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND npwpd like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where.= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( op_nama like \"{$_REQUEST['CPM_NAMA']}%\" OR wp_nama like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
		$where.= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND op_alamat like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";

		$query = "SELECT 
				(case 
					when simpatda_type < 13 then concat(npwpd,simpatda_tahun_pajak,simpatda_type,date_format(masa_pajak_awal,'%m'),1)
					else concat(npwpd,simpatda_tahun_pajak,simpatda_type,date_format(masa_pajak_awal,'%m'),2)
				end) as id,
				simpatda_tahun_pajak as tahun,
				npwpd, 
				b.id_sw as jenis,
				date_format(masa_pajak_awal,'%m') as bulan,
				sum(patda_total_bayar) as total
				FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
				WHERE {$where}
				GROUP BY concat(npwpd, simpatda_tahun_pajak,simpatda_type,date_format(masa_pajak_awal,'%m'))";
		
		$result = mysqli_query($Conn_gw, $query);
		$res = array();
		while($r = mysqli_fetch_assoc($result)){
			$npwpd = $r['npwpd'];
			$tahun = $r['tahun'];
			$jenis = $r['jenis'];
			$bulan = $r['bulan']; 
			$res[$r['id']] = $r['total'];
		}
		
		$tmp = $res;
		foreach($tmp as $a=>$b){
			$id = substr($a,0,strlen($a)-2);
			if(isset($res[$id])){
				$res[$id]+= $b;
			}else{
				$res[$id] = $b;
			}
		}

		$sheet = $objPHPExcel->getSheet($z);
		foreach($sheet->getRowIterator() as $baris) {
			foreach ($baris->getCellIterator() as $cell) {
				$value = str_replace(array("{","}"),array("",""),$cell->getValue());
				if (isset($res[$value])) { $cell->setValue($res[$value]); }
			}
		}
		
		foreach($sheet->getRowIterator() as $baris) {
			foreach ($baris->getCellIterator() as $cell) {
				if (strpos($cell->getValue(), '{') !== false) {
					$cell->setValue(0);
				}

			}
		}
		
		$objPHPExcel->setActiveSheetIndex($z)->setCellValue("E".$row, "TOTAL");
		$col="F";
		for($x=1;$x<=26;$x++){
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue($col.$row, "=SUM({$col}8:{$col}" . ($row - 1) . ")");
			$col++;
		}
		
		for($x=8;$x<=($row-1);$x++){
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue("AD{$x}", "=F{$x}+H{$x}+J{$x}+L{$x}+N{$x}+P{$x}+R{$x}+T{$x}+V{$x}+X{$x}+Z{$x}+AB{$x}");
			$objPHPExcel->setActiveSheetIndex($z)->setCellValue("AE{$x}", "=G{$x}+I{$x}+K{$x}+M{$x}+O{$x}+Q{$x}+S{$x}+U{$x}+W{$x}+Y{$x}+AA{$x}+AC{$x}");
		}
	
                
		$objPHPExcel->getActiveSheet()->getStyle('H8:AE' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->setTitle("Kartu Data");
        $objPHPExcel->getActiveSheet()->getStyle('C1:C3')->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A5:AE7')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A5:AE7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A5:AE7')->getFill()->getStartColor()->setRGB('E4E4E4');
        
        $objPHPExcel->getActiveSheet()->getStyle("A".($row).":AE".($row)."")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle("A".($row).":AE".($row)."")->getFill()->getStartColor()->setRGB('E4E4E4');


		$objPHPExcel->getActiveSheet()->getStyle("A".($row).":AE".($row))->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    )
                )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A5:AE' . ($row))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );

        for ($x = "A"; $x <= "AD"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }


            $kecamatan = $rows['CPM_KECAMATAN'];
            $objPHPExcel->getActiveSheet()->setTitle("$kecamatan");
            $objPHPExcel->createSheet();
            
            //echo $kecamatan = $rows['CPM_KECAMATAN'];

            $z++;
            
        // }
        
        ob_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="kartudata-' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}

?> 
