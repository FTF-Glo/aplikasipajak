<?php
set_time_limit(0);
ini_set('memory_limit', '1G');
class MonitoringPajak extends Pajak
{

	public function __construct()
	{
		parent::__construct();
		if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
	}

	public function filtering_penre($id)
	{

		$src_kec = $this->get_list_kecamatan();
		$list_kecamatan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}
		$opt_resto = '<option value="">All</option><option value="1">Restoran</option><option value="2">Katering</option>';
		$html = "<div class=\"filtering\">
					<form>
						Jenis Pajak : <select name=\"JNS_PAJAK-{$id}\" id=\"JNS_PAJAK-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($this->arr_pajak_table as $a => $b) {
			$html .= "<option value='{$a}'>{$b}</option>";
		}
		$html .= "</select>
						<select name=\"CPM_JENIS_RESTORAN\" id=\"CPM_JENIS_RESTORAN-{$id}\">{$opt_resto}</select>
						Tahun : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
		for ($th = date("Y"); $th >= date("Y") - 5; $th--) {
			$html .= "<option value='{$th}'>{$th}</option>";
		}
		$html .= "</select> Sampai Bulan : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($this->arr_bulan as $x => $y) {
			$html .= "<option value='{$x}'>{$y}</option>";
		}

		// $html.= "</select> Kecamatan : <input type=\"text\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">";

		$html .= "</select> Kecamatan : <select name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($list_kecamatan as $x => $y) {
			$html .= "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select> Kelurahan : <select name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\">";
		$html .= "<option value=''>All</option></select>";



		// $html.= "User Name : <input type=\"text\" name=\"CPM_USER-{$id}\" id=\"CPM_USER-{$id}\" >  
		// Nama : <input type=\"text\" name=\"CPM_NAMA-{$id}\" id=\"CPM_NAMA-{$id}\" >  
		// NIP : <input type=\"text\" name=\"CPM_NIP-{$id}\" id=\"CPM_NIP-{$id}\" >  
		// Role Petugas : <select name=\"CPM_ROLE-{$id}\" id=\"CPM_ROLE-{$id}\">";
		//$html.= "<option value=''>All</option>";
		//foreach ($this->arr_role as $a => $b) {
		//    $html.= "<option value='{$a}'>{$b}</option>";
		//}
		//$html.= "</select>    
		$html .= " 
						NPWPD : <input type=\"text\" name=\"CPM_NPWPD\" id=\"CPM_NPWPD-{$id}\" />
						Nama WP/OP : <input type=\"text\" name=\"CPM_NAMA\" id=\"CPM_NAMA-{$id}\" />
						Tgl Lapor :
						<input type=\"date\" name=\"CPM_TGL_LAPOR1\" id=\"CPM_TGL_LAPOR1-{$id}\" class=\"datepicker\" size=\"10\" />
						<input type=\"date\" name=\"CPM_TGL_LAPOR2\" id=\"CPM_TGL_LAPOR2-{$id}\" class=\"datepicker\" size=\"10\" />
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
						<input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
						<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span></td>
					</form>
				</div> ";
		return $html;
	}


	public function grid_table_penre()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_penre($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
				<style>.number{text-align:right}</style>
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$('#laporanPajak-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: true,
							pageSize: {$this->pageSize},
							sorting: true,
							defaultSorting: 'CPM_TGL_LAPOR ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/pen-re/penre-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
							},
							fields: {
								NO : {title: 'No',width: '5%'},
								npwpd: {title: 'NPWPD',width: '10%',key: true}, 
								wp_nama: {title: 'Nama WP',width: '10%',key: true}, 
								op_nama: {title: 'Nama OP',width: '10%'}, 
								kecamatan_op: {title: 'Kecamatan',width: '10%'}, 
								kelurahan_op: {title: 'Kelurahan',width: '10%'}, 
								op_alamat: {title: 'Alamat',width: '35%'}, 
								sspd: {title: 'SSPD',width: '7%'}, 
								sptpd: {title: 'SKPD',width: '7%'}, 
								simpatda_dibayar: {title: 'Penetapan',width: '7%',listClass:'number'}, 
								patda_total_bayar: {title: 'Realisasi',width: '7%',listClass:'number'}, 
								sisa: {title: 'Sisa',width: '7%',listClass:'number'}, 
								payment_paid: {title: 'Tgl Bayar',width: '15%'}, 
								CPM_TGL_LAPOR: {title: 'Tgl Lapor',width: '15%'},
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
								CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
								JNS_PAJAK : $('#JNS_PAJAK-{$this->_i}').val(),
								CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
								CPM_JENIS_RESTORAN : $('#CPM_JENIS_RESTORAN-{$this->_i}').val(),
								CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
								CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_NAMA : $('#CPM_NAMA-{$this->_i}').val(),
								// CPM_NIP : $('#CPM_NIP-{$this->_i}').val(),
								// CPM_USER : $('#CPM_USER-{$this->_i}').val(),
								// CPM_ROLE : $('#CPM_ROLE-{$this->_i}').val(),
								// CPM_NAMA : $('#CPM_NAMA-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click();

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
				</script>";
		echo $html;
	}

	public function grid_data_penre()
	{
		$this->grid_data_penre_new();
		exit;

		$src_kec = $this->get_list_kecamatan();
		$src_kel = $this->get_list_kelurahan('', 'LIST');
		$list_kecamatan = array();
		$list_kelurahan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}
		foreach ($src_kel as $kec) {
			$list_kelurahan[$kec->CPM_KEL_ID] = $kec->CPM_KELURAHAN;
		}

		try {
			// $where = "CPM_STATUS = '{$this->_s}' ";
			$where = " ";
			$where .= (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != "") ? " WHERE B.id_sw like \"{$_REQUEST['JNS_PAJAK']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND YEAR(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) <= \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			// $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = \"{$_REQUEST['CPM_KECAMATAN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND A.kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";


			//Koneksi GW
			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];
			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where}"; //WHERE {$where}
			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			// $query = "SELECT * FROM PATDA_PETUGAS WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$query = "SELECT * FROM SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type 
			{$where} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";

			$result = mysqli_query($Conn_gw, $query);
			// if ($result == true) {
			//     print_r(mysqli_fetch_assoc($result));
			// }else{
			//     echo mysqli_error();
			// }
			// echo $query;
			// print_r($this->get_config_value($this->_a));

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));

				$row['kecamatan_op'] = isset($list_kecamatan[$row['kecamatan_op']]) ? $list_kecamatan[$row['kecamatan_op']] : $row['kecamatan_op'];
				$row['kelurahan_op'] = isset($list_kelurahan[$row['kelurahan_op']]) ? $list_kelurahan[$row['kelurahan_op']] : $row['kelurahan_op'];
				// mysqli_close($this->Conn);
				// $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_USER']}&s={$row['CPM_STATUS']}&i={$this->_i}";
				// $url = "main.php?param=" . base64_encode($base64);

				// $row['CPM_USER'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_USER']}</a>";
				// $row['CPM_ROLE'] = $this->arr_role[$row['CPM_ROLE']];
				$sisa = $row['simpatda_dibayar'] - $row['patda_total_bayar'];
				$row['sisa'] = $sisa <= 0 ? 0 : $row['simpatda_dibayar'];
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar'], 0);
				$row['patda_total_bayar'] = number_format($row['patda_total_bayar'], 0);
				$row['sisa'] = number_format($row['sisa']);
				$row['sspd'] = $row['sptpd'];
				// $row['sisa'] = $row['simpatda_dibayar'] - $row['patda_total_bayar'];
				$rows[] = $row;
			}
			// echo "<pre>";
			// print_r($rows);
			// echo "</pre>";
			// echo "string";
			// exit();

			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			$jTableResult['q'] = $query;
			$jTableResult['TotalRecordCount'] = $recordCount;
			$jTableResult['Records'] = $rows;
			print $this->Json->encode($jTableResult);

			mysqli_close($Conn_gw);
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}


	private function grid_data_penre_new()
	{
		// var_dump($_REQUEST);exit();
		$src_kec = $this->get_list_kecamatan();
		$src_kel = $this->get_list_kelurahan('', 'LIST');
		$list_kecamatan = array();
		$list_kelurahan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}
		foreach ($src_kel as $kel) {
			$list_kelurahan[$kel->CPM_KEL_ID] = $kel->CPM_KELURAHAN;
		}

		try {
			// $where = "CPM_STATUS = '{$this->_s}' ";
			// $where = " ";
			$where = "WHERE YEAR(A.saved_date) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" ";
			$where .= (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != "") ? " AND B.id_sw = \"{$_REQUEST['JNS_PAJAK']}\" " : "";

			$thn_val = $_REQUEST['CPM_TAHUN_PAJAK'] != '' ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
			$bln_val = $_REQUEST['CPM_MASA_PAJAK'] != '' ? substr('0' . $_REQUEST['CPM_MASA_PAJAK'], -2, 2) : date('m');
			if ($_REQUEST['CPM_MASA_PAJAK'] != '') {
				$where .= " AND DATE_FORMAT(A.saved_date,'%Y-%m') = '{$thn_val}-{$bln_val}' ";
			} else {
				$bln_val = (int) $bln_val;
				$where .= $thn_val == date('Y') ? " AND MONTH(A.saved_date) <= '{$bln_val}' " : 'AND MONTH(A.saved_date) <=12';
			}
			// $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND YEAR(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			// $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) <= \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

			// $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = \"{$_REQUEST['CPM_KECAMATAN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND A.kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";
			if (isset($_REQUEST['CPM_JENIS_RESTORAN']) && $_REQUEST['CPM_JENIS_RESTORAN'] != "") {
				if ($_REQUEST['CPM_JENIS_RESTORAN'] == 1) {
					$where .= " AND B.id_sw=8 AND A.simpatda_rek!='4.1.01.07.07'";
				} elseif ($_REQUEST['CPM_JENIS_RESTORAN'] == 2) {
					$where .= " AND B.id_sw=8 AND A.simpatda_rek='4.1.01.07.07'";
				}
			}

			if ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] == "") {
				$where .= " AND str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR1']}'";
			} elseif ($_REQUEST['CPM_TGL_LAPOR1'] == "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
				$where .= " AND str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') <= '{$_REQUEST['CPM_TGL_LAPOR2']}'";
			} elseif ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
				$where .= " AND (str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') BETWEEN '{$_REQUEST['CPM_TGL_LAPOR1']}' AND '{$_REQUEST['CPM_TGL_LAPOR2']}')";
			}
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND A.npwpd like '%{$_REQUEST['CPM_NPWPD']}%'" : "";
			$where .= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND (A.op_nama like '%{$_REQUEST['CPM_NAMA']}%' OR A.wp_nama like '%{$_REQUEST['CPM_NAMA']}%')" : "";


			//Koneksi GW
			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];
			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where}"; //WHERE {$where}
			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			$tbl_pajak = $this->arr_idpajak;
			if (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != '') {
				$tbl_pajak = array($_REQUEST['JNS_PAJAK'] => $this->arr_idpajak[$_REQUEST['JNS_PAJAK']]);
			}

			foreach ($tbl_pajak as $k => $v) {
				$tbl_pajak[$k] = 'PATDA_' . strtoupper($v) . '_DOC';
			}

			#query select list data
			/* // $query = "SELECT * FROM PATDA_PETUGAS WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$query = "SELECT * FROM SIMPATDA_GW A 
			INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type 
			INNER JOIN SW_PATDA_LAMPUNGSELATAN.
			{$where} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			// echo $query;exit();
			$result = mysql_query($query, $Conn_gw);
			// if ($result == true) {
			//     print_r(mysql_fetch_assoc($result));
			// }else{
			//     echo mysql_error();
			// }
			// echo $query;
			// print_r($this->get_config_value($this->_a)); */

			$query = "select *,DATE_FORMAT(payment_paid,'%d-%m-%Y') as payment_paid FROM (";
			$arr_query = array();
			foreach ($tbl_pajak as $id => $tbl) {
				$arr_query[] = "(SELECT A.*,C.CPM_TGL_LAPOR from SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type INNER JOIN {$tbl} C on C.CPM_ID=A.id_switching $where AND B.id_sw='$id')";
			}
			$query .= implode(' UNION ', $arr_query);
			$query .= ") A order by {$_GET['jtSorting']} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";


			$result = mysqli_query($Conn_gw, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));

				$row['kecamatan_op'] = isset($list_kecamatan[$row['kecamatan_op']]) ? $list_kecamatan[$row['kecamatan_op']] : $row['kecamatan_op'];
				$row['kelurahan_op'] = isset($list_kelurahan[$row['kelurahan_op']]) ? $list_kelurahan[$row['kelurahan_op']] : $row['kelurahan_op'];
				// mysql_close($this->Conn);
				// $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_USER']}&s={$row['CPM_STATUS']}&i={$this->_i}";
				// $url = "main.php?param=" . base64_encode($base64);

				// $row['CPM_USER'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_USER']}</a>";
				// $row['CPM_ROLE'] = $this->arr_role[$row['CPM_ROLE']];
				$row['sisa'] = $row['simpatda_dibayar'] - $row['patda_total_bayar'];
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar'], 0);
				$row['patda_total_bayar'] = number_format($row['patda_total_bayar'], 0);
				$row['sisa'] = number_format($row['sisa']);
				$row['sspd'] = $row['sptpd'];
				// $row['sisa'] = $row['simpatda_dibayar'] - $row['patda_total_bayar'];
				$rows[] = $row;
			}
			// echo "<pre>";
			// print_r($rows);
			// echo "</pre>";
			// echo "string";
			// exit();

			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			$jTableResult['q'] = $query;
			$jTableResult['TotalRecordCount'] = $recordCount;
			$jTableResult['Records'] = $rows;
			print $this->Json->encode($jTableResult);

			mysqli_close($Conn_gw);
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function filtering_sptpd($id)
	{
		$src_kec = $this->get_list_kecamatan();
		$list_kecamatan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}

		$html = "<div class=\"filtering\">
					<form>
						NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" style=\"width:90px\" >
						NO. STPPD : <input type=\"text\" name=\"CPM_NO-{$id}\" id=\"CPM_NO-{$id}\" style=\"width:120px\" >
						THN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
		$html .= "<option value=''>All</option>";
		for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
			$html .= "<option value='{$th}'>{$th}</option>";
		}


		$html .= "</select> Kecamatan : <select name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($list_kecamatan as $x => $y) {
			$html .= "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select> Kelurahan : <select name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\">";
		$html .= "<option value=''>All</option></select>";


		$html .= "</select> MS PAJAK : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($this->arr_bulan as $x => $y) {
			$html .= "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select>
					TGL LAPOR :
					<input type=\"text\" name=\"CPM_TGL_LAPOR1\" id=\"CPM_TGL_LAPOR1-{$id}\" class=\"datepicker\" size=\"10\" />
					<input type=\"text\" name=\"CPM_TGL_LAPOR2\" id=\"CPM_TGL_LAPOR2-{$id}\" class=\"datepicker\" size=\"10\" />
					STATUS : <select name=\"CPM_TRAN_STATUS-{$id}\" id=\"CPM_TRAN_STATUS-{$id}\">";
		$html .= "<option value=''>All</option>";
		foreach ($this->arr_status as $x => $y) {
			$html .= "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select>
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
						<input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
			<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_sptpd()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-dok/sptpd";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_sptpd($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
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
							defaultSorting: 'CPM_TGL_LAPOR ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_ID: {key: true,list: false}, 
								CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%'}, 
								CPM_NO: {title: 'Nomor Laporan',width: '10%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
								CPM_VERSION: {title: 'Versi Dok',width: '5%'},
								CPM_AUTHOR: {title: 'User Input',width: '10%'},
								CPM_TRAN_STATUS: {title: 'Status',width: '10%'}                                    
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_NO : $('#CPM_NO-{$this->_i}').val(),
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
								CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
								CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
								CPM_TRAN_STATUS : $('#CPM_TRAN_STATUS-{$this->_i}').val(),
								CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
								CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click();                        
						
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
				</script>";

		echo $html;
	}

	public function grid_data_sptpd()
	{
		try {
			$PAJAK = strtoupper($this->arr_pajak_table[$this->_i]);

			$where = "(";
			$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
			$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] != "") ?
				" AND tr.CPM_TRAN_STATUS  = '{$_REQUEST['CPM_TRAN_STATUS']}') " :
				" AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')) ";

			$where .= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

			if ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] == "") {
				$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR1']}'";
			} elseif ($_REQUEST['CPM_TGL_LAPOR1'] == "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
				$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR2']}'";
			} elseif ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
				$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') BETWEEN '{$_REQUEST['CPM_TGL_LAPOR1']}' AND '{$_REQUEST['CPM_TGL_LAPOR2']}'";
			}

			$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
			$res = mysqli_query($this->Conn, $sql);

			while ($row = mysqli_fetch_assoc($res)) {
				$arrFunction[$row["CPM_NO"]] = "fPatdaPelayanan" . $row["CPM_NO"];
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_{$PAJAK}_DOC pj 
							INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
							INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID
							WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
							CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
							pj.CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
							pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG
							FROM PATDA_{$PAJAK}_DOC pj INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
							INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID                            
							WHERE {$where}
							ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($this->Conn, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$arrFunction[$this->_i]}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}";
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

			mysqli_close($this->Conn);
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function filtering_skpdkb($id)
	{
		$html = "<div class=\"filtering\">
					<form>
						Jenis Pajak : 
						<select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >
						<option value=\"\">All</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$html .= "<option value=\"{$x}\">{$y}</option>";
		}
		$html .= "</select>
						No. Pelaporan : <input type=\"text\" name=\"CPM_NO_SPTPD-{$id}\" id=\"CPM_NO_SPTPD-{$id}\" >  
						NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >                        
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_skpdkb()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-dok/skpdkb";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_skpdkb($this->_i)}
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
							defaultSorting: 'CPM_NPWPD ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_ID: {key: true,list: false},                                 
								CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								CPM_NO_SKPDKB: {title: 'No SKPDKB/T',width: '10%'},                                
								CPM_NO_SPTPD: {title: 'No. Pelaporan',width: '10%'},                                
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_KURANG_BAYAR: {title: 'Kurang Bayar',width: '10%'},
								CPM_TAMBAHAN: {title: 'Jenis',width: '10%'},
								CPM_VERSION: {title: 'Versi Dok',width: '10%'},
								CPM_TRAN_STATUS: {title: 'Status',width: '10%'}
							}
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

	public function grid_data_skpdkb()
	{
		try {
			$where = "(";
			$where .= " tr.CPM_TRAN_FLAG = '0' ";
			$where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";

			$where .= ") ";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND s.CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

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

				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&f={$this->_f}&flg={$row['CPM_TRAN_FLAG']}&type={$row['CPM_JENIS_PAJAK']}&id={$row['CPM_ID']}&tambahan={$row['CPM_TAMBAHAN']}";
				$url = "main.php?param=" . base64_encode($base64);
				$row['CPM_NO_SKPDKB'] = "<a href='{$url}' title='Klik untuk detail'>{$row['CPM_NO_SKPDKB']}</a>";
				$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
				$row['CPM_KURANG_BAYAR'] = number_format($row['CPM_KURANG_BAYAR'], 2);
				$row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
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
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function filtering_stpd($id)
	{
		$html = "<div class=\"filtering\">
					<form>
						Jenis Pajak : 
						<select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >
						<option value=\"\">All</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$html .= "<option value=\"{$x}\">{$y}</option>";
		}
		$html .= "</select>
						No. STPD : <input type=\"text\" name=\"CPM_NO_STPD-{$id}\" id=\"CPM_NO_STPD-{$id}\" >  
						NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >                        
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_stpd()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-dok/stpd";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_stpd($this->_i)}
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
							defaultSorting: 'CPM_NPWPD ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_ID: {key: true,list: false},                                
								CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								CPM_NO_STPD: {title: 'No. STPD',width: '10%'},                                
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_TOTAL_PAJAK: {title: 'Total Tagihan',width: '10%'},
								CPM_VERSION: {title: 'Versi Dok',width: '10%'},
								CPM_TRAN_STATUS: {title: 'Status',width: '10%'},                                
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val()                                    
							});
						});
						$('#cari-{$this->_i}').click();                        
					});
				</script>";

		echo $html;
	}

	public function grid_data_stpd()
	{
		try {
			$where = "(";
			$where .= " tr.CPM_TRAN_FLAG = '0' ";
			$where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
			$where .= ") ";

			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND s.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND s.CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";

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

				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&f={$this->_f}&flg={$row['CPM_TRAN_FLAG']}&type={$row['CPM_JENIS_PAJAK']}&id={$row['CPM_ID']}&idp={$row['CPM_ID_PROFIL']}";
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
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function grid_data_log_tapbox()
	{
		try {
			if ((isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) == "") || trim($_REQUEST['CPM_JENIS_PAJAK']) == "") {
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['TotalRecordCount'] = 0;
				$jTableResult['Records'] = array();
				print $this->Json->encode($jTableResult);
				exit;
			}

			$tbl_profil = "PATDA_" . strtoupper($this->arr_pajak_table[$_REQUEST['CPM_JENIS_PAJAK']]) . "_PROFIL";
			$query = "SELECT CPM_DEVICE_ID FROM {$tbl_profil} WHERE CPM_NPWPD = '{$_REQUEST['CPM_NPWPD']}' AND CPM_AKTIF='1'";
			$result = mysqli_query($this->Conn, $query);
			$data = mysqli_fetch_assoc($result);
			$devid = explode(";", $data['CPM_DEVICE_ID']);
			$deviceId = "'" . implode("','", $devid) . "'";

			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_TB_DBNAME'];
			$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
			$dbTable = $arr_config['PATDA_TB_TABLE'];
			$dbUser = $arr_config['PATDA_TB_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName,$Conn_gw);

			$where = (!empty($_POST["CPM_NPWPD"])) ? "deviceId in ({$deviceId}) " : "1=1 ";
			$where .= (isset($_REQUEST['CPM_TAHUN']) && $_REQUEST['CPM_TAHUN'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%Y') = \"{$_REQUEST['CPM_TAHUN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA']) && $_REQUEST['CPM_MASA'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%m') = \"{$_REQUEST['CPM_MASA']}\" " : "";
			$where .= (isset($_REQUEST['CPM_TANGGAL']) && $_REQUEST['CPM_TANGGAL'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%d') = \"{$_REQUEST['CPM_TANGGAL']}\" " : "";

			$limit = (!empty($_POST["CPM_NPWPD"])) ? "" : " limit 0,100";
			$q = $query = "SELECT DeviceId, count(deviceId) as totalRows from ALARM a INNER JOIN ALARM_CODE b ON a.JenisAlarm = b.code 
					  WHERE {$where} GROUP BY deviceId {$limit}";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));

			$rows = array();
			$no = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("req" => base64_encode($this->Json->encode($_REQUEST))));
				$json = base64_encode($this->Json->encode($row));
				$row = array_merge($row, array("NO" => ++$no));
				$row['DeviceId'] = "<a href='javascript:void()' onclick=\"javascript:getDetTranTapbox('{$json}')\">" . $row['DeviceId'] . "</a>";
				$rows[] = $row;
			}

			$jTableResult = array();
			$jTableResult['q'] = $q;
			$jTableResult['Result'] = "OK";
			$jTableResult['TotalRecordCount'] = 0;
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

	public function grid_data_log_tapbox_detail()
	{
		try {
			$p = $this->Json->decode(base64_decode($_REQUEST['p']));
			$req = $this->Json->decode(base64_decode($p->req));

			$_REQUEST = array(
				"CPM_JENIS_PAJAK" => $req->CPM_JENIS_PAJAK,
				"CPM_NPWPD" => $req->CPM_NPWPD,
				"CPM_TAHUN" => $req->CPM_TAHUN,
				"CPM_MASA" => $req->CPM_MASA,
				"CPM_TANGGAL" => $req->CPM_TANGGAL,
				"ALARM_CODE" => $_REQUEST['ALARM_CODE']
			);

			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_TB_DBNAME'];
			$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
			$dbTable = $arr_config['PATDA_TB_TABLE'];
			$dbUser = $arr_config['PATDA_TB_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			mysqli_select_db($dbName);

			$where = "deviceId = '{$p->DeviceId}' ";
			$where .= (isset($_REQUEST['CPM_TAHUN']) && $_REQUEST['CPM_TAHUN'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%Y') = \"{$_REQUEST['CPM_TAHUN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA']) && $_REQUEST['CPM_MASA'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%m') = \"{$_REQUEST['CPM_MASA']}\" " : "";
			$where .= (isset($_REQUEST['CPM_TANGGAL']) && $_REQUEST['CPM_TANGGAL'] != "") ? " AND DATE_FORMAT(a.ServerTimeStamp,'%d') = \"{$_REQUEST['CPM_TANGGAL']}\" " : "";
			$where .= (isset($_REQUEST['ALARM_CODE']) && $_REQUEST['ALARM_CODE'] != "") ? " AND b.code = \"{$_REQUEST['ALARM_CODE']}\" " : "";

			$query = "SELECT COUNT(*) AS RecordCount from ALARM a INNER JOIN ALARM_CODE b ON a.JenisAlarm = b.code 
					  WHERE {$where}";
			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			$q = $query = "SELECT a.*,description from ALARM a INNER JOIN ALARM_CODE b ON a.JenisAlarm = b.code 
					  WHERE {$where} ORDER by ServerTimeStamp DESC
					  LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				$rows[] = $row;
			}

			$jTableResult = array();
			$jTableResult['q'] = $q;
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

	public function filtering($id)
	{
		$html = "<div class=\"filtering\">
					<form>
						NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >";
		$html .= " JENIS PAJAK : <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >";
		$jns_pajak = array();
		$jns_pajak[3] = $this->arr_pajak[3];
		$jns_pajak[5] = $this->arr_pajak[5];
		$jns_pajak[8] = $this->arr_pajak[8];
		$jns_pajak[2] = $this->arr_pajak[2];

		$html .= "<option value='' selected>All</option>";
		foreach ($jns_pajak as $x => $y) {
			$html .= "<option value=\"{$x}\">{$y}</option>";
		}
		$html .= "</select> | SERVER TIME STAMP (TAHUN : <select name=\"CPM_TAHUN-{$id}\" id=\"CPM_TAHUN-{$id}\">";
		for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
			$html .= ($th == date("Y")) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
		}
		$html .= "</select> BULAN : <select name=\"CPM_MASA-{$id}\" id=\"CPM_MASA-{$id}\">";
		foreach ($this->arr_bulan as $x => $y) {
			$html .= ($x == date("m")) ? "<option value='{$x}'selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select> TGL : <select name=\"CPM_TANGGAL-{$id}\" id=\"CPM_TANGGAL-{$id}\">";
		$html .= "<option value='' selected>All</option>";
		for ($x = 1; $x <= 31; $x++) {
			$html .= "<option value='{$x}'>{$x}</option>";
		}
		$html .= "</select> )
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_log_tapbox()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "log-tapbox";
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
							paging: false,
							pageSize: {$this->pageSize},
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-log-tapbox.php?action=list&a={$this->_a}&m={$this->_m}',                            
							},
							fields: {
								NO : {title: 'No',width: '5%'},
								DeviceId: {title: 'Device ID',width: '30%'},
								totalRows: {title: 'Total Logs',width: '85%'}
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_TAHUN : $('#CPM_TAHUN-{$this->_i}').val(),
								CPM_MASA : $('#CPM_MASA-{$this->_i}').val(),
								CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
								CPM_TANGGAL : $('#CPM_TANGGAL-{$this->_i}').val()    
							});
						});
						$('#cari-{$this->_i}').click();
						$('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');
					});
				</script>";
		echo $html;
	}

	public function filtering_log_tapbox_detail($id)
	{
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbNam);
		//mysqli_select_db($dbName);

		$query = "SELECT code, description from ALARM_CODE";
		$result = mysqli_query($Conn_gw, $query);

		$desc = "";
		while ($row = mysqli_fetch_assoc($result)) {
			$desc .= "<option value='{$row['code']}'>{$row['description']}</option>";
		}
		mysqli_close($Conn_gw);
		$html = "<div class=\"filtering\">
					<form>
						Deskripsi : <select name=\"ALARM_CODE-{$id}\" id=\"ALARM_CODE-{$id}\" >{$desc}</select>
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_log_tapbox_detail()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "log-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_log_tapbox_detail($this->_i)}    
				<div id=\"tapboxPajak-{$this->_i}\" style=\"width:100%;\"></div>
				<script type=\"text/javascript\">
					$(document).ready(function() {                        
						$('#tapboxPajak-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: true,
							pageSize: {$this->pageSize},
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-log-tapbox-detail.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',                            
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								DeviceId: {title: 'Device ID',width: '5%'},
								ServerTimeStamp: {title: 'Server Time Stamp',width: '8%'},
								TimeStamp: {title: 'Time Stamp',width: '8%'},
								JenisAlarm: {title: 'Jenis Alarm',width: '5%'},
								description: {title: 'Deskripsi',width: '10%'}
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#tapboxPajak-{$this->_i}').jtable('load', {
								ALARM_CODE : $('#ALARM_CODE-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click();
					});
				</script>";
		echo $html;
	}

	public function get_total_pajak()
	{
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$allid = isset($_POST['allid']) ? substr($_POST['allid'], 0, strlen($_POST['allid']) - 1) : ""; #substr menghilangkan tanda '|' di akhir 
		$arr_group_id = explode("|", $allid); #grouping menjadi array device id pernpwpd

		$arr_id = explode(";", str_replace("|", ";", $allid)); #memecah semua device id menjadi array untuk keperluan query
		$where_id = "'" . implode("','", $arr_id) . "'"; #untuk dipakai di query DeviceId in ('123213','123213','13213')

		$data['id'] = $allid;
		$where = "DeviceId in ({$where_id})";
		$where .= isset($_POST['bln']) ? " AND DATE_FORMAT(TransactionDate,'%m') = '" . str_pad($_POST['bln'], 2, 0, STR_PAD_LEFT) . "'" : "";
		$where .= isset($_POST['thn']) ? " AND DATE_FORMAT(TransactionDate,'%Y') = '{$_POST['thn']}'" : "";

		$query = "select 
					DeviceId,
					sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
					from {$dbTable} 
					WHERE {$where}
					group by DeviceId, DATE_FORMAT(TransactionDate,'%m'), DATE_FORMAT(TransactionDate,'%Y')";
		#echo $query;exit;
		$result = mysqli_query($Conn_gw, $query);
		$data = array();
		while ($row = mysqli_fetch_assoc($result)) {
			foreach ($arr_group_id as $group) {
				if (strpos($group, $row['DeviceId']) !== false) {
					$data[$group][] = $row['total'];
				}
			}
		}
		$group_data = array();
		foreach ($data as $key => $val) {
			$total = 0;
			foreach ($val as $subtotal) {
				$total += $subtotal;
			}
			$group_data["#" . str_replace(";", "_", $key)] = number_format($total, 0);
		}
		#$group_data['q'] = $query;
		print $this->Json->encode($group_data);
	}

	public function grid_data_pembanding()
	{
		try {
			$JENIS_PAJAK = (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? $_REQUEST['CPM_JENIS_PAJAK'] : "PARKIR";
			$TAHUN_PAJAK = (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date("Y");
			$MASA_PAJAK = (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? $_REQUEST['CPM_MASA_PAJAK'] : date("m");
			$MASA_PAJAK = str_pad($MASA_PAJAK, 2, 0, STR_PAD_LEFT);

			$where = "WHERE CPM_AKTIF='1' AND (CPM_DEVICE_ID IS NOT NULL AND CPM_DEVICE_ID !='') ";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? "AND CPM_NPWPD = '{$_REQUEST['CPM_NPWPD']}'" : "";
			$query = "SELECT CPM_NPWPD, CPM_DEVICE_ID from PATDA_{$JENIS_PAJAK}_PROFIL {$where}
					LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			$result = mysqli_query($this->Conn, $query);

			$result_count = mysqli_query($this->Conn, "SELECT COUNT(CPM_NPWPD) AS RecordCount from PATDA_{$JENIS_PAJAK}_PROFIL {$where} ");
			$row = mysqli_fetch_assoc($result_count);
			$recordCount = $row['RecordCount'];

			$img_loading = "<img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'>";

			while ($row = mysqli_fetch_assoc($result)) {
				$row['TAHUN_PAJAK'] = $TAHUN_PAJAK;
				$row['MASA_PAJAK'] = $MASA_PAJAK;

				$json = base64_encode($this->Json->encode($row));

				$row = array_merge($row, array("NO" => ++$no));
				$row['CPM_NPWPD'] = "<a href='javascript:void(0)' onclick=\"javascript:getDetTranTapbox('{$json}')\">" . Pajak::formatNPWPD($row['CPM_NPWPD']) . "</a>";
				$row['TAHUN_PAJAK'] = $TAHUN_PAJAK;
				$row['MASA_PAJAK'] = isset($this->arr_bulan[(int) $MASA_PAJAK]) ? $this->arr_bulan[(int) $MASA_PAJAK] : $MASA_PAJAK;
				$row['AMOUNT'] = "<span class='deviceidstr' deviceid='{$row['CPM_DEVICE_ID']}' id='" . str_replace(";", "_", $row['CPM_DEVICE_ID']) . "'>{$img_loading}</span>";
				$row['LAPORAN'] = "<input type='button' class='btn btn-default btn-xs' onclick=\"javascript:getLaporanTranTapbox('{$json}')\" value='Cetak Laporan'>";
				$rows[] = $row;
			}

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

	public function filtering_pembanding($id)
	{
		$DIR = "PATDA-V1";
		$arr_pajak = $this->arr_pajak_tapbox;

		$html = "<script>
				jQuery(document).ready(function(){                      
					$('#CPM_NPWPD-{$id}').autocomplete({                    
						source : function(request, response, url) {
									$.ajax({
										url: 'view/{$DIR}/monitoring/trans-tapbox/svc-list-npwpd.php',
										dataType: \"json\",
										data : {jns :$('#CPM_JENIS_PAJAK-{$id}').val(), term:request.term },
										type: \"POST\",
										success: function (data) {
											response($.map(data, function(item) {
												return { label: item.label, value: item.value};
											}));
										}
									});
								},
						autoFocus: true,
				minLength: 0
				}).data( \"ui-autocomplete\" )._renderItem = function( ul, item ) {
					return $( \"<li></li>\" )
				.data( \"item.autocomplete\", item )
				.append( $(\"<a></a>\").html(item.label))
				.appendTo( ul );
			};
			});
			</script>
			<div class=\"filtering\">
					<form>
						Jenis Pajak : <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\">";
		foreach ($arr_pajak as $key => $val) {
			$html .= "<option value='{$key}'>{$val}</option>";
		}
		$html .= "</select> NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  
						TAHUN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
		#$html.= "<option value=''>All</option>";
		for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
			$html .= ($th == date("Y")) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
		}
		$html .= "</select> MASA PAJAK : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
		#$html.= "<option value=''>All</option>";
		foreach ($this->arr_bulan as $x => $y) {
			$html .= ($x == date("m")) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select>    
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_pembanding()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring/trans-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_pembanding($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$('#laporanPajak-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: true,
							pageSize: 10, /*{$this->pageSize},*/
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding.php?action=list&a={$this->_a}&m={$this->_m}',                            
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								AMOUNT: {title: 'Total Pajak',width: '10%'},
								LAPORAN: {title: 'Laporan',width: '10%'},
							},recordsLoaded: function(event, data) {
								get_total_pajak_from_tapbox();
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
								CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val()
							});
						});                        
						$('#cari-{$this->_i}').click();
						$('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');
						
						function get_total_pajak_from_tapbox(){
							var allid = '';
							var thn = $('#CPM_TAHUN_PAJAK-{$this->_i}').val();
								   var bln = $('#CPM_MASA_PAJAK-{$this->_i}').val();
							$('.deviceidstr').each(function(){
								allid += $(this).attr('deviceid')+'|';
							})
							$.ajax({
								type:'post',
								data:{allid:allid,bln:bln,thn:thn},
								url : 'view/{$DIR}/{$modul}/svc-list-data-pembanding.php?action=total&a={$this->_a}&m={$this->_m}',
								dataType:'json',
								success:function(res){
									 $('.deviceidstr').each(function(){
										   $(this).html('0');
									   })
									for(var x in res){
										$(x).html(res[x]);
									}									
								}
							})
						}
					});
				</script>";
		echo $html;
	}

	public function grid_data_pembanding_detail()
	{
		try {
			$p = $this->Json->decode(base64_decode($_REQUEST['p']));

			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_TB_DBNAME'];
			$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
			$dbTable = $arr_config['PATDA_TB_TABLE'];
			$dbUser = $arr_config['PATDA_TB_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);
			$devid = explode(";", $p->CPM_DEVICE_ID);
			$deviceId = "'" . implode("','", $devid) . "'";
			$where = "DeviceId in ({$deviceId}) ";
			$where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$p->TAHUN_PAJAK}\" ";
			$where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$p->MASA_PAJAK}\" ";
			$where .= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

			$where .= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between 
					CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and 
					CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

			$query = "select 
						DeviceId, 
						NotAdmitReason,
						'{$p->CPM_NPWPD}' as CPM_NPWPD,
						TransactionNumber,
						TransactionDate,
						TransactionAmount as total
						from {$dbTable} 
						WHERE {$where} ";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
			$recordCount = mysqli_num_rows($result);

			$q = $query .= "LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
			$rows = array();

			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($data = mysqli_fetch_assoc($result)) {
				$dataTapbox = array();
				$dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));
				$dataTapbox['CPM_DEVICE_ID'] = $data['DeviceId'];
				$dataTapbox['CPM_NPWPD'] = $data['CPM_NPWPD'];
				$dataTapbox['TRAN_NUMBER'] = $data['TransactionNumber'];
				$dataTapbox['TRAN_DATE'] = $data['TransactionDate'];
				$dataTapbox['AMOUNT'] = $data['total'];
				$NotAdmitReason = $data['NotAdmitReason'] == "" ? "..." : $data['NotAdmitReason'];
				$dataTapbox['ADMIT'] = "<a href='javascript:void(0)' onclick='javascript:admit(\"{$dataTapbox['TRAN_NUMBER']}\",\"{$this->_a}\")' id='link-{$dataTapbox['TRAN_NUMBER']}'>{$NotAdmitReason}</a><div class='admit' ket='{$NotAdmitReason}' id='admit-{$dataTapbox['TRAN_NUMBER']}'></div>";

				$rows[] = $dataTapbox;
			}
			#query select list data

			$jTableResult = array();
			$jTableResult['q'] = $q;
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

	public function grid_data_pembanding_detail_harian()
	{
		try {
			$p = $this->Json->decode(base64_decode($_REQUEST['p']));

			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_TB_DBNAME'];
			$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
			$dbTable = $arr_config['PATDA_TB_TABLE'];
			$dbUser = $arr_config['PATDA_TB_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);
			$devid = explode(";", $p->CPM_DEVICE_ID);
			$deviceId = "'" . implode("','", $devid) . "'";
			$where = "DeviceId in ({$deviceId}) ";

			$where .= "AND DATE_FORMAT(TransactionDate,'%d-%m-%Y') = \"" . date("d-m-Y") . "\" ";
			$where .= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
			$where .= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

			$query = "select 
						DeviceId, 
						NotAdmitReason,
						'{$p->CPM_NPWPD}' as CPM_NPWPD,
						TransactionNumber,
						TransactionDate,
						TransactionAmount as total
						from {$dbTable} 
						WHERE {$where} ";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
			$recordCount = mysqli_num_rows($result);

			$q = $query .= "LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
			$rows = array();

			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($data = mysqli_fetch_assoc($result)) {
				$dataTapbox = array();
				$dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));
				$dataTapbox['CPM_DEVICE_ID'] = $data['DeviceId'];
				$dataTapbox['CPM_NPWPD'] = $data['CPM_NPWPD'];
				$dataTapbox['TRAN_NUMBER'] = $data['TransactionNumber'];
				$dataTapbox['TRAN_DATE'] = $data['TransactionDate'];
				$dataTapbox['AMOUNT'] = $data['total'];
				$NotAdmitReason = $data['NotAdmitReason'] == "" ? "..." : $data['NotAdmitReason'];
				$dataTapbox['ADMIT'] = "<a href='javascript:void(0)' onclick='javascript:admit(\"{$dataTapbox['TRAN_NUMBER']}\",\"{$this->_a}\")' id='link-{$dataTapbox['TRAN_NUMBER']}'>{$NotAdmitReason}</a><div class='admit' ket='{$NotAdmitReason}' id='admit-{$dataTapbox['TRAN_NUMBER']}'></div>";

				$rows[] = $dataTapbox;
			}
			#query select list data

			$jTableResult = array();
			$jTableResult['q'] = $q;
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

	public function filtering_pembanding_detail($id)
	{
		$p = $this->Json->decode(base64_decode($_REQUEST['p']));
		$q = $this->Json->decode(base64_decode($_REQUEST['q']));
		$arr = explode(";", $p->CPM_DEVICE_ID);
		$deviceId = "<option value='' selected>All</option>";
		foreach ($arr as $val) {
			$deviceId .= "<option value='{$val}'>{$val}</option>";
		}

		$html = "<div class=\"filtering\">
					<form>
						<input type='hidden' id=\"HIDDEN-{$id}\" tahun=\"{$p->TAHUN_PAJAK}\" a=\"{$q->a}\" npwpd=\"{$p->CPM_NPWPD}\" bulan=\"{$p->MASA_PAJAK}\" deviceid=\"{$p->CPM_DEVICE_ID}\">
						<table>
							<tr>
								<td style='background:transparent;padding:2px'>No. Transaksi</td>
								<td style='background:transparent;padding:2px'>: <input type=\"text\" name=\"NO_TRAN-{$id}\" id=\"NO_TRAN-{$id}\" >
									Device Id : <select name=\"CPM_DEVICE_ID-{$id}\" id=\"CPM_DEVICE_ID-{$id}\" all=\"{$p->CPM_DEVICE_ID}\" >{$deviceId}</select>
								</td>
								<td style='background:transparent;padding:2px'></td>
							</tr>
							<tr>
								<td style='background:transparent;padding:2px'>Tanggal Transaksi</td>
								<td style='background:transparent;padding:2px'>: 
								<input type=\"text\" name=\"TRAN_DATE1-{$id}\" id=\"TRAN_DATE1-{$id}\" readonly onclick=\"javascript:openDate(this);\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE1-{$id}').val('');\"> 
									s.d
								<input type=\"text\" style='width:143px' name=\"TRAN_DATE2-{$id}\" readonly id=\"TRAN_DATE2-{$id}\" onclick=\"javascript:openDate(this);\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE2-{$id}').val('');\">
								</td>
								<td style='background:transparent;padding:2px'>
									<button type=\"submit\" id=\"cariDetail-{$id}\">Cari</button>
									<button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel('{$id}','{$q->url}');\">Cetak Excel</button>
					<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
								</td>    
							</tr>
						</table>
					</form>
				</div> ";
		return $html;
	}

	public function filtering_pembanding_detail_harian($id)
	{
		$p = $this->Json->decode(base64_decode($_REQUEST['p']));
		$q = $this->Json->decode(base64_decode($_REQUEST['q']));
		$arr = explode(";", $p->CPM_DEVICE_ID);
		$deviceId = "<option value='' selected>All</option>";
		foreach ($arr as $val) {
			$deviceId .= "<option value='{$val}'>{$val}</option>";
		}

		$html = "<div class=\"filtering\">
					<form>
						<input type='hidden' id=\"HIDDEN-{$id}\" tahun=\"{$p->TAHUN_PAJAK}\" a=\"{$q->a}\" npwpd=\"{$p->CPM_NPWPD}\" bulan=\"{$p->MASA_PAJAK}\" deviceid=\"{$p->CPM_DEVICE_ID}\">
						<table>
							<tr>
								<td style='background:transparent;padding:2px'>Tanggal</td>
								<td style='background:transparent;padding:2px'>: " . date("d-m-Y") . "</td>
								<td style='background:transparent;padding:2px'></td>    
							</tr>
							<tr>
								<td style='background:transparent;padding:2px'>No. Transaksi</td>
								<td style='background:transparent;padding:2px'>: <input type=\"text\" name=\"NO_TRAN-{$id}\" id=\"NO_TRAN-{$id}\" >
									Device Id : <select name=\"CPM_DEVICE_ID-{$id}\" id=\"CPM_DEVICE_ID-{$id}\" all=\"{$p->CPM_DEVICE_ID}\" >{$deviceId}</select>
								</td>
								<td style='background:transparent;padding:2px'>
									<button type=\"submit\" id=\"cariDetail-{$id}\">Cari</button>
									<button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel('{$id}','{$q->url}');\">Cetak Excel</button>
								<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
								</td>    
							</tr>
						</table>
					</form>
				</div> ";
		return $html;
	}


	public function grid_table_pembanding_detail()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring/trans-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_pembanding_detail($this->_i)}
				<div id=\"tapboxPajakDetail-{$this->_i}\" style=\"width:100%;\"></div>                
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$(\".date\").datepicker({
							showOn: \"button\",
							buttonImage: \"images/calendar.gif\",
							buttonImageOnly: true,
							buttonText: \"Select date\"
						});
						$('#tapboxPajakDetail-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: true,
							pageSize: 15,
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',                            
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_NPWPD: {title: 'NPWPD',width: '5%'},
								CPM_DEVICE_ID: {title: 'Device Id',width: '5%'},
								TRAN_NUMBER: {title: 'Nomor Transaksi',width: '7%'},
								TRAN_DATE: {title: 'Tanggal Transaksi',width: '10%'},
								AMOUNT: {title: 'Total Pajak',width: '6%'},
								ADMIT: {title: 'Alasan tidak diakui',width: '10%'}
							}
						});
						$('#cariDetail-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#tapboxPajakDetail-{$this->_i}').jtable('load', {
								NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
								CPM_DEVICE_ID : $('#CPM_DEVICE_ID-{$this->_i}').val(),
								TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
								TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val()    
							});
							$('#cetak-{$this->_i}').removeAttr('disabled');
						});
						$('#cariDetail-{$this->_i}').click();
					});
					function openDate(obj) {
						$(obj).datepicker({dateFormat: 'dd-mm-yy'});
						$(obj).datepicker('show');
					}

				</script>";
		echo $html;
	}

	public function grid_table_pembanding_detail_harian()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring/trans-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_pembanding_detail_harian($this->_i)}
				<div id=\"tapboxPajakDetail-{$this->_i}\" style=\"width:100%;\"></div>                
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$(\".date\").datepicker({
							showOn: \"button\",
							buttonImage: \"images/calendar.gif\",
							buttonImageOnly: true,
							buttonText: \"Select date\"
						});
						$('#tapboxPajakDetail-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: true,
							pageSize: 15,
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail-harian.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',                            
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_NPWPD: {title: 'NPWPD',width: '5%'},
								CPM_DEVICE_ID: {title: 'Device Id',width: '5%'},
								TRAN_NUMBER: {title: 'Nomor Transaksi',width: '7%'},
								TRAN_DATE: {title: 'Tanggal Transaksi',width: '10%'},
								AMOUNT: {title: 'Total Pajak',width: '6%'},
								ADMIT: {title: 'Alasan tidak diakui',width: '10%'}
							}
						});
						$('#cariDetail-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#tapboxPajakDetail-{$this->_i}').jtable('load', {
								NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
								CPM_DEVICE_ID : $('#CPM_DEVICE_ID-{$this->_i}').val(),
								TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
								TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val()    
							});
							$('#cetak-{$this->_i}').removeAttr('disabled');
						});
						$('#cariDetail-{$this->_i}').click();
					});
					function openDate(obj) {
						$(obj).datepicker({dateFormat: 'dd-mm-yy'});
						$(obj).datepicker('show');
					}

				</script>";
		echo $html;
	}



	public function grid_table_laporan_tran_tapbox()
	{
		$p = $this->Json->decode(base64_decode($_REQUEST['p']));
		$q = $this->Json->decode(base64_decode($_REQUEST['q']));
		$resume = $this->laporan_resume($p);
		$DIR = "PATDA-V1";
		$modul = "monitoring/trans-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				<input type='hidden' id=\"HIDDEN-{$this->_i}\" tahun=\"{$p->TAHUN_PAJAK}\" npwpd=\"{$p->CPM_NPWPD}\" a=\"{$this->_a}\" bulan=\"{$p->MASA_PAJAK}\" deviceid=\"{$p->CPM_DEVICE_ID}\">    
				<div class='row'>
					<div class='col-xs-2'></div>
					<div class='col-xs-8 text-center'><strong>SISTEM INFORMASI<br/>PAJAK ONLINE DAERAH PALEMBANG</strong></div>
				</div>                
				<div class='row'>
					<div class='col-xs-12 text-center'><strong>LAPORAN TRANSAKSI KENA PAJAK BULANAN</strong></div>
				</div>                    
				<div class='row filtering'>
					<div class='row'>
						<div class='col-xs-4'><strong>WAJIB PAJAK</strong></div>
						<div class='col-xs-6'>: {$p->CPM_NPWPD}</div>
					</div>
					<div class='row'>
						<div class='col-xs-4'><strong>PERIODE</strong></div>
						<div class='col-xs-6'>: " . (isset($this->arr_bulan[(int) $p->MASA_PAJAK]) ? $this->arr_bulan[(int) $p->MASA_PAJAK] : "") . " {$p->TAHUN_PAJAK}</div>
					</div>
					<div class='row'>
						<div class='col-xs-4'><strong>TOTAL TRX</strong></div>
						<div class='col-xs-6'>: " . number_format($resume['jumlah']) . "</div>
					</div>
					<div class='row'>
						<div class='col-xs-4'><strong>TOTAL OMSET</strong></div>
						<div class='col-xs-6'>: Rp. " . number_format($resume['total']) . "</div>
					</div>
					<div class='row'>
						<div class='col-xs-4'><strong>TANGGAL CETAK</strong></div>
						<div class='col-xs-6'>: " . date("d-m-Y") . "</div>
						<div class='col-xs-2'>
							<input type='button' class='btn btn-success btn-xs' onclick=\"javascript:download_excel('{$this->_i}','function/PATDA-V1/svc-download-laporan-tran-tapbox.xls.php');\" value='Cetak ke xls'>
							<span id=\"loadlink-{$this->_i}\" style=\"font-size: 10px; display: none;\">Loading...</span>
						</div>
					</div>
				</div>
				<div id=\"tapboxPajakDetail-{$this->_i}\" style=\"width:100%;\"></div>                
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$('#tapboxPajakDetail-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: false,
							pageSize: 15,
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data-laporan-tran-tapbox.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',                            
							},
							fields: {
								TANGGAL: {title: 'Tanggal',width: '5%'},
								JUMLAH_TRANSAKSI: {title: 'Jumlah Transaksi',width: '5%'},
								TOTAL_OMSET: {title: 'Total Omset',width: '7%'}
							}
						});
						$('#tapboxPajakDetail-{$this->_i}').jtable('load', {});                            
						
					});
				</script>";
		echo $html;
	}

	public function grid_data_laporan_tran_tapbox()
	{
		try {
			$p = $this->Json->decode(base64_decode($_REQUEST['p']));

			$arr_config = $this->get_config_value($this->_a);
			$dbName = $arr_config['PATDA_TB_DBNAME'];
			$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
			$dbTable = $arr_config['PATDA_TB_TABLE'];
			$dbUser = $arr_config['PATDA_TB_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);
			$devid = explode(";", $p->CPM_DEVICE_ID);
			$deviceId = "'" . implode("','", $devid) . "'";
			$where = "DeviceId in ({$deviceId}) ";
			$where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$p->TAHUN_PAJAK}\" ";
			$where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$p->MASA_PAJAK}\" ";

			$query = "select                         
						DATE_FORMAT(TransactionDate,'%d-%m-%Y') as TransactionDate,
						count(TransactionNumber) as jumlah,
						sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
						from {$dbTable} 
						WHERE {$where} GROUP BY DATE_FORMAT(TransactionDate,'%d-%m-%Y')";

			$result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
			$rows = array();

			while ($data = mysqli_fetch_assoc($result)) {
				$dataTapbox = array();
				$dataTapbox['TANGGAL'] = $data['TransactionDate'];
				$dataTapbox['JUMLAH_TRANSAKSI'] = number_format($data['jumlah']);
				$dataTapbox['TOTAL_OMSET'] = number_format($data['total']);

				$rows[] = $dataTapbox;
			}
			#query select list data

			$jTableResult = array();
			$jTableResult['Result'] = "OK";
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

	function download_excel_status_dok()
	{

		$PAJAK = strtoupper($this->arr_pajak_table[$this->_i]);

		$where = "(";
		$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
		$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] != "") ?
			" AND tr.CPM_TRAN_STATUS  = '{$_REQUEST['CPM_TRAN_STATUS']}') " :
			" AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')) ";

		$where .= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";


		if ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] == "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR1']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] == "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR2']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') BETWEEN '{$_REQUEST['CPM_TGL_LAPOR1']}' AND '{$_REQUEST['CPM_TGL_LAPOR2']}'";
		}

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		#query select list data
		$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
						CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
						pj.CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
						pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG
						FROM PATDA_{$PAJAK}_DOC pj INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
						INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID                            
						WHERE {$where}
						ORDER BY CPM_TGL_LAPOR ASC LIMIT {$offset}, {$total}";
		$res = mysqli_query($this->Conn, $query);

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
			->setCellValue('B1', 'Tanggal Lapor')
			->setCellValue('C1', 'Nomor Laporan')
			->setCellValue('D1', 'NPWPD')
			->setCellValue('E1', 'Nama')
			->setCellValue('F1', 'Tahun Pajak')
			->setCellValue('G1', 'Masa Pajak')
			->setCellValue('H1', 'Total Pajak')
			->setCellValue('I1', 'Versi Dokumen')
			->setCellValue('J1', 'User Input')
			->setCellValue('K1', 'Status');

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($res);

		while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_TGL_LAPOR']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_MASA_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_TOTAL_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_VERSION']);
			$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_AUTHOR']);
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('SPTPD Pajak');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('E4E4E4');

		for ($x = "A"; $x <= "K"; $x++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
		}

		ob_clean();
		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function download_excel()
	{

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
		$where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
		$where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
		$where .= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
		$where .= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

		$where .= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between 
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

	function npwpd()
	{
		$arr_npwpd = array();
		$data = array();
		$table = "PATDA_{$_REQUEST['jns']}_PROFIL";
		$npwpd = mysqli_escape_string($this->Conn, $_REQUEST['term']);
		$query = "SELECT CPM_NPWPD as npwpd, CPM_NAMA_OP as namaop FROM {$table} WHERE (CPM_NAMA_OP LIKE '%{$npwpd}%' or CPM_NPWPD LIKE '{$npwpd}%') 
					AND CPM_AKTIF='1' AND (CPM_DEVICE_ID IS NOT NULL AND CPM_DEVICE_ID !='') LIMIT 0,5";
		$res = mysqli_query($this->Conn, $query);
		while ($data = mysqli_fetch_assoc($res)) {
			$arr_npwpd[] = array("label" => "NPWPD : {$data['npwpd']}<br/>NAMA OP : {$data['namaop']}", "value" => $data['npwpd']);
		}
		echo json_encode($arr_npwpd);
	}

	public function filtering_dashboard($id)
	{
		$DIR = "PATDA-V1";
		$html = "<div class=\"filtering\">
					<form>Bulan : <select name=\"CPM_BULAN_PAJAK-{$id}\" id=\"CPM_BULAN_PAJAK-{$id}\">";
		foreach ($this->arr_bulan as $x => $y) {
			$html .= ($x == date("m")) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
		}
		$html .= "</select>    
						<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
					</form>
				</div>";
		return $html;
	}

	public function grid_table_dashboard()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring/trans-tapbox";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_dashboard($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
				<script type=\"text/javascript\">
					$(document).ready(function() {
						$('#laporanPajak-{$this->_i}').jtable({
							title: '',
							columnResizable : false,
							columnSelectable : false,
							paging: false,
							pageSize: {$this->pageSize},
							sorting: false,
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data-dashboard.php?action=list&a={$this->_a}&m={$this->_m}',                            
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_JENIS_PAJAK : {title: 'Jenis Pajak',width: '10%'},";
		for ($x = 1; $x <= 31; $x++) {
			$html .= "TGL_{$x}: {title: '{$x}',width: '10%'},";
		}
		$html .= "}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {
								CPM_BULAN_PAJAK : $('#CPM_BULAN_PAJAK-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click();
						$('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');
					});
				</script>";
		echo $html;
	}

	private function generate_device_value($tahun, $bulan, $hari)
	{
		$arr_pajak = $this->arr_pajak_tapbox;
		$arr_deviceid = array();
		foreach ($arr_pajak as $key => $val) {
			$query = "SELECT CPM_DEVICE_ID, CPM_NPWPD from PATDA_{$key}_PROFIL WHERE CPM_AKTIF='1' AND (CPM_DEVICE_ID is not null AND CPM_DEVICE_ID != '') ";
			$result = mysqli_query($this->Conn, $query);
			while ($data = mysqli_fetch_assoc($result)) {
				$arr_deviceid[$key][] = $data['CPM_DEVICE_ID'];
			}
		}

		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$device = array();
		foreach ($arr_deviceid as $pajak => $arrayid) {
			$d = "";
			foreach ($arrayid as $deviceid) {
				$d .= "'" . str_replace(";", "','", $deviceid) . "',";
			}
			$d = substr($d, 0, strlen($d) - 1);

			$query = "select sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total from {$dbTable} WHERE DeviceId in ({$d}) ";
			$query .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$tahun}\" ";
			$query .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$bulan}\" ";
			$query .= "AND DATE_FORMAT(TransactionDate,'%d') = \"{$hari}\" ";

			$result = mysqli_query($Conn_gw, $query);
			$data = mysqli_fetch_assoc($result);

			$device[$pajak] = $data['total'];
		}
		return $device;
	}

	public function generate_summary()
	{
		$tahun = date("Y");
		$bulan = date("m");
		$hari = date("d");

		$query = "SELECT * FROM PATDA_SUMMARY_TAPBOX WHERE CPM_BULAN_TRANSAKSI ='{$bulan}' AND CPM_TAHUN_TRANSAKSI = '{$tahun}'";

		$res = mysqli_query($this->Conn, $query);
		if (mysqli_num_rows($res)) {

			$device = $this->generate_device_value($tahun, $bulan, $hari);
			foreach ($device as $key => $val) {
				$query = "UPDATE PATDA_SUMMARY_TAPBOX SET CPM_TGL_{$hari}='{$val}'
					WHERE CPM_BULAN_TRANSAKSI ='{$bulan}' AND CPM_TAHUN_TRANSAKSI = '{$tahun}' AND CPM_TIPE_PAJAK = '{$key}'";
				mysqli_query($this->Conn, $query);
			}
		} else {
			$device = $this->generate_device_value($tahun, $bulan, $hari);
			foreach ($device as $key => $val) {
				$CPM_ID = c_uuid();
				$query = "INSERT INTO PATDA_SUMMARY_TAPBOX (
						CPM_ID,
						CPM_BULAN_TRANSAKSI,
						CPM_TAHUN_TRANSAKSI,
						CPM_TGL_{$hari},
						CPM_TIPE_PAJAK
						) VALUES ('{$CPM_ID}','{$bulan}','{$tahun}','{$val}','{$key}')";
				mysqli_query($this->Conn, $query);
			}
		}
	}

	public function grid_data_dashboard()
	{
		try {
			$tahun = date("Y");
			$bulan = str_pad($_REQUEST['CPM_BULAN_PAJAK'], 2, '0', STR_PAD_LEFT);

			$arr_pajak = $this->arr_pajak_tapbox;
			$query = "SELECT * FROM PATDA_SUMMARY_TAPBOX WHERE CPM_BULAN_TRANSAKSI ='{$bulan}' AND CPM_TAHUN_TRANSAKSI = '{$tahun}'";

			$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

			$rows = array();
			$no = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				$rows[] = $row;
			}

			$jTableResult = array();
			#$jTableResult['q'] = $query;
			$jTableResult['Result'] = "OK";
			$jTableResult['TotalRecordCount'] = 3;
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

	public function filtering_status_bayar($id)
	{
		$bank = "";
		$operator = "";
		$kurang_bayar = "";
		$lbl_date1 = "Tgl Jatuh Tempo awal";
		$lbl_date2 = "Tgl Jatuh Tempo akhir";

		if ($id == "1") {
			$opt_bank = "<option value=\"\">Semua</option>";
			$bank = $this->get_bank_payment();
			if (!empty($bank)) {
				foreach ($bank as $k => $v) {
					$opt_bank .= "<option value=\"{$v->CDC_B_ID}\">{$v->CDC_B_NAME}</option>";
				}
			}
			$bank = "Bank : <br/><select class=\"form-control\" style=\"width:100px;height:30px;\" id=\"bank-{$id}\" name=\"bank-{$id}\">{$opt_bank}</select>";
			$lbl_date1 = "Tgl Bayar awal";
			$lbl_date2 = "Tgl Bayar akhir";
		}

		if ($id == "1") {
			$operator .= "Operator : <br/><input class=\"form-control\" style=\"width:110px;height:30px;\" type=\"text\" name=\"operator-{$id}\" id=\"operator-{$id}\" />";
		}

		if ($id == "1") {
			$kurang_bayar .= "Kurang Bayar : <input type=\"checkbox\" name=\"kurang_bayar-{$id}\" id=\"kurang_bayar-{$id}\"/ onClick=\"checkK({$id})\"/ value=\"0\">";
		}

		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$thn = date("Y");
		$opt_tahun = "<option value=\"\">Semua</option>";
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$bln = date("m");
		$opt_bulan = "<option value=\"\">Semua</option>";
		for ($b = 1; $b <= 12; $b++) {
			$opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
		}

		// foreach ($this->arr_tipe_pajak as $key => $val) {
		// 	$opt_jenis_lapor .= "<option value=\"{$key}\">{$val}</option>";
		// }
		$query = "SELECT * FROM jenis_tipe_pajak";
		// var_dump($query);exit;

		$opt_jenis_lapor = "<option value=\"\" id=\"semua_pajak\">Semua</option>";
		$result = mysqli_query($this->Conn, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$opt_jenis_lapor .= "<option value=\"{$row['simpatda_rek']}\" data-id_pajak=\"{$row['id_pajak']}\">{$row['nama_tipe_pajak']}</option>";
		}

		$src_kec = $this->get_list_kecamatan();
		$list_kecamatan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}

		$html = "<div class=\"filtering\">
					<style> .monitoring td{background:transparent}</style>
					<form>
						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
						
								<tr>
									<td colspan=\"4\">
										<br>
										<br>

									</td>
								</tr>
							</table>

							<div  class=\"p-2\">
								<div class=\"row\"> 
									<div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
										<button class=\"btn btn-success\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$id}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$id}\">
											<i class=\"fa fa-filter\"></i> Filter Data
										</button>
									</div>
									<div class=\"col-12\"> 
										<div class=\"collapse\" id=\"collapsFilter-{$id}\">
											<div class=\"form-filtering\">
												<form>
														<div class=\"row\">
														
															<div class=\"form-group col-md-3\" >
																<input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
																<label>NPWPD</label>
																<input class=\"form-control\" type=\"text\" name=\"sptpd-{$id}\" id=\"sptpd-{$id}\" >
															</div>

															<div class=\"form-group col-md-3\" >
																
																<label>Nama WP / Tempat Usaha</label>
																<input class=\"form-control\" type=\"text\" name=\"wp_nama-{$id}\" id=\"wp_nama-{$id}\" >
															</div>
															
															<div class=\"form-group col-md-3\" >
																<label>Alamat</label>
																<input class=\"form-control\" type=\"text\" name=\"wp_alamat\" id=\"wp_alamat-{$id}\"/>
															</div>

															<div class=\"form-group col-md-3\">
																<label>Kode Bayar</label>
																<input class=\"form-control\" type=\"text\" name=\"payment_code-{$id}\" id=\"payment_code-{$id}\" />
															</div>

															<div class=\"form-group col-md-3\">
																<label>Operator</label>
																<input class=\"form-control\" type=\"text\" name=\"operator-{$id}\" id=\"operator-{$id}\" />
															</div>
																
															<div class=\" form-group col-md-3\"> 
																<label>Nama/No Laporan</label>
																<input class=\"form-control\" type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >
															</div>
										
															<div class=\" form-group col-md-3\"> 
																<label>Tahun Pajak</label>
																<select class=\"form-control\" name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\">{$opt_tahun}</select>
															</div>
										
															<div class=\" form-group col-md-3\"> 
																<label>Bulan</label>
																<select class=\"form-control\" name=\"simpatda_bulan_pajak-{$id}\" id=\"simpatda_bulan_pajak-{$id}\">{$opt_bulan}</select>
															</div>
															<div class=\" form-group col-md-3\"> 
																<label>Kecamatan</label>
																<select class=\"form-control\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\"><option value=''>All</option>";
																foreach ($list_kecamatan as $x => $y) {
																	$html .= "<option value='{$x}'>{$y}</option>";
																}
																$html .= "</select>
															</div>
															
															<div class=\" form-group col-md-3\"> 
																<label>kelurahan</label>
															

																<select class=\"form-control\" name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select>
															</div>
															
															<div class=\" form-group col-md-3\"> 
																<label>Jenis Pajak</label>
																<select class=\"form-control\" name=\"jenis-{$id}\" id=\"jenis-{$id}\">{$opt_jenis_pajak}</select>
															</div>

															<div class=\" form-group col-md-3\"> 
																<label>Jenis Pelaporan</label>
																<select class=\"form-control\" id=\"jenis-{$id}\" name=\"jenis-{$id}\">{$opt_jenis_lapor}</select>	
															</div>
					

															<div class=\" form-group col-md-3\"> 
																<label>Jenis Pajak</label>
																<select class=\"form-control\" id=\"simpatda_dibayar-{$id}\" name=\"simpatda_dibayar-{$id}\">
																	<option value=\"0\">--semua--</option>
																	<option value=\"1\">0 s/d <5jt</option>
																	<option value=\"2\">5jt s/d <10jt</option>
																	<option value=\"3\">10jt s/d <20jt</option>
																	<option value=\"4\">20jt s/d <30jt</option>
																	<option value=\"5\">30jt s/d <40jt</option>
																	<option value=\"6\">40jt s/d <50jt</option>
																	<option value=\"7\">50jt s/d <100jt</option>
																	<option value=\"8\">>=100jt</option>
																	<option value=\"9\">>100jt</option>
																</select>
															</div>
					
															<div class=\"form-group col-md-3\">
																<label>Tgl Bayar awal </label>
																<div style=\"display: flex; align-items: center;\">
																
																	<input type=\"text\" name=\"expired_date1\" id=\"expired_date1-{$id}\" class=\"datepicker\"/>
																</div>
															</div>

															<div class=\"form-group col-md-3\">
																<label>Tgl Bayar akhir</label>
																<div style=\"display: flex; align-items: center;\">
																	<input type=\"text\" name=\"expired_date2\" id=\"expired_date2-{$id}\" class=\"datepicker\"/>	
																</div>
															</div>

															<div class=\"form-group col-md-3\">
																
																<div style=\"display: flex; align-items: center;\">
																	<input type=\"checkbox\" name=\"kurang_bayar-{$id}\" id=\"kurang_bayar-{$id}\"/ onClick=\"checkK({$id})\"/ value=\"0\">
																
																</div>
															</div>
														
														</div>
					
					
														<div class=\"row\">";
											
													
														
														$html .=  "
															<div class=\" form-group col-md-12\">    
																<input  type=\"submit\" class=\"btn btn-success\" name=\"button2\" id=\"cari-{$id}\" value=\"Tampilkan\"/>

																<input class=\"btn btn-success\" type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
																<input class=\"btn btn-success\" type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor V2\" onClick=\"toExcelV2({$id})\"/>
																<input class=\"btn btn-success\" type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke pdf\" onClick=\"toPdf({$id})\"/>    
																
																<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
															</div>
														</div>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						
					</form>
				</div> ";
		return $html;
	}

	public function filtering_status_bayar_($id)
    {
		$bank = "";
		$operator = "";
		$kurang_bayar = "";
		$lbl_date1 = "Tgl Jatuh Tempo awal";
		$lbl_date2 = "Tgl Jatuh Tempo akhir";

		if ($id == "1") {
			$opt_bank = "<option value=\"\">Semua</option>";
			$bank = $this->get_bank_payment();
			if (!empty($bank)) {
				foreach ($bank as $k => $v) {
					$opt_bank .= "<option value=\"{$v->CDC_B_ID}\">{$v->CDC_B_NAME}</option>";
				}
			}
			$bank = "Bank : <br/><select class=\"form-control\" style=\"width:100px;height:30px;\" id=\"bank-{$id}\" name=\"bank-{$id}\">{$opt_bank}</select>";
			$lbl_date1 = "Tgl Bayar awal";
			$lbl_date2 = "Tgl Bayar akhir";
		}

		if ($id == "1") {
			$operator .= "Operator : <br/><input class=\"form-control\" style=\"width:110px;height:30px;\" type=\"text\" name=\"operator-{$id}\" id=\"operator-{$id}\" />";
		}

		if ($id == "1") {
			$kurang_bayar .= "Kurang Bayar : <input type=\"checkbox\" name=\"kurang_bayar-{$id}\" id=\"kurang_bayar-{$id}\"/ onClick=\"checkK({$id})\"/ value=\"0\">";
		}

		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$thn = date("Y");
		$opt_tahun = "<option value=\"\">Semua</option>";
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$bln = date("m");
		$opt_bulan = "<option value=\"\">Semua</option>";
		for ($b = 1; $b <= 12; $b++) {
			$opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
		}

		// foreach ($this->arr_tipe_pajak as $key => $val) {
		// 	$opt_jenis_lapor .= "<option value=\"{$key}\">{$val}</option>";
		// }
		$query = "SELECT * FROM jenis_tipe_pajak";
		// var_dump($query);exit;

		$opt_jenis_lapor = "<option value=\"\" id=\"semua_pajak\">Semua</option>";
		$result = mysqli_query($this->Conn, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$opt_jenis_lapor .= "<option value=\"{$row['simpatda_rek']}\" data-id_pajak=\"{$row['id_pajak']}\">{$row['nama_tipe_pajak']}</option>";
		}

		$src_kec = $this->get_list_kecamatan();
		$list_kecamatan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}




        // $opt_tahun = '<option value="">All</option>';
        // for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
        //     $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        // }

        // $opt_bulan = '<option value="">All</option>';
        // foreach ($this->arr_bulan as $x => $y) {
        //     $opt_bulan .= "<option value='{$x}'>{$y}</option>";
        // }

        // $kec = $this->get_list_kecamatan();
        // $opt_kecamatan = "<option value=\"\">All</option>";
        // foreach ($kec as $k => $v) {
        //     $opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
        // }
        // $opt_triwulan = '<option value="">All</option>';
        // foreach ($this->arr_triwulan as $x => $y) {
        //     $opt_triwulan .= "<option value='{$x}'>{$y}</option>";
        // }

        // $opt_pilih = "<option value=\"\">All</option>";
        // foreach ($this->arr_jenis as $k => $v) {
        //     $opt_pilih .= "<option value=\"{$k}\">{$v}</option>";
        // }

        $html .= "
        <style>
        .form-filtering {
            background-color: #fff;
            padding: 20px 40px;
            
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    
        }
        </style>
        <div  class=\"p-2\">
            <div class=\"row\"> 
                <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
                    <button class=\"btn btn-success\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$id}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$id}\">
                        <i class=\"fa fa-filter\"></i> Filter Data
                    </button>
                </div>
                <div class=\"col-12\"> 
                    <div class=\"collapse\" id=\"collapsFilter-{$id}\">
                        <div class=\"form-filtering\">
                            <form>
                                    <div class=\"row\">

                                        <div class=\"form-group col-md-3\" >
                                            <input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                                            <label>NPWPD</label>
                                            <input class=\"form-control\" type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >
                                        </div>
                                            
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Nama/No Laporan</label>
                                            <input class=\"form-control\" type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >
                                        </div>
                    
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Tahun Pajak</label>
                                            <select class=\"form-control\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select>
                                        </div>
                    
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Masa Pajak</label>
                                            <select class=\"form-control\" name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select>
                                        </div>
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Kecamatan</label>
                                            <select class=\"form-control\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select>
                                        </div>
                                        
                                        <div class=\" form-group col-md-3\"> 
                                            <label>kelurahan</label>
                                            <select class=\"form-control\" name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select>
                                        </div>";



                                        $html .= " 
                    
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Lapor </label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly class=\"form-control date\" style=\"flex-grow: 1; margin-right: 10px;\">
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Lapor</label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" readonly class=\"form-control date\" style=\"flex-grow: 1; margin-right: 10px;\">
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                    
                                    </div>


                                    <div class=\"row\">";
                        
                                    if ($this->_i == 4) { 
                                        $html .= "<div class=\" form-group col-md-3\"> 
                                            <label>Total Pajak</label>
                                            <input class=\"form-control\" type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\">
                                        </div>";
                                    }
                                    
                                    $html .=  "<div class=\" form-group col-md-3\"> 
                                            <label>Pilih Jenis</label>
                                            <select class=\"form-control\" name=\"CPM_PIUTANG-{$id}\" id=\"CPM_PIUTANG-{$id}\">{$opt_pilih}</select>
                                        </div>
                                        <div class=\" form-group col-md-12\">    
                                            <button type=\"submit\" class=\"btn btn-success\" id=\"cari-{$id}\">Cari</button>                       
                                            <button type=\"button\" class=\"btn btn-success\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>
                                            <button type=\"button\" class=\"btn btn-success\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/cetakpanjang/svc-download-bentang-panjang-atb.xls.php')\">Cetak Bentang Panjang</button>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
        return $html;
    }



	public function update()
	{
		try {
			foreach ($_REQUEST as $a => $b) {
				$$a = mysqli_escape_string($this->Conn, $b);
			}
			$ymd = date_create_from_format('d-m-Y H:i:s', $payment_paid);
			$dates1 = date_format($ymd, 'Y-m-d H:i:s');
			$query = sprintf("UPDATE SIMPATDA_GW SET 
					payment_paid='%s' WHERE sptpd = '%s' AND payment_code = '%s'", $dates1, $sptpd, $payment_code);
			//var_dump($payment_raid, $sptpd);
			//var_dump($payment_paids);

			$result = mysqli_query($this->Conn, $query);

			$jTableResult = array();
			$jTableResult['Q'] = $query;
			$jTableResult['Result'] = "OK";
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




	public function grid_table_status_bayar()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-bayar";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_status_bayar($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
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
							defaultSorting: 'saved_date ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-status-bayar.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',     
								
                
							},
							fields: {
								NO : {title: 'No',width: '3%',edit :false},
								jenis_rekening: {title: 'Jenis Pajak',width: '10%',edit :false},
								sptpd: {title: 'No. Pelaporan',width: '7%'},
								npwpd: {title: 'NPWPD',width: '5%',edit :false},
								op_nama: {title: 'Nama Pajak',width: '10%',edit :false},
								wp_nama: {title: 'Nama WP',width: '10%',edit :false},
								wp_alamat: {title: 'Alamat WP',width: '10%'},
								simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '5%',edit :false},
								simpatda_bulan_pajak: {title: 'Masa Pajak',width: '8%',edit :false},
								saved_date: {title: 'Tgl Disetujui',width: '7%',edit :false},
								expired_date: {title: 'Jatuh Tempo',width: '7%',edit :false},
								simpatda_dibayar: {title: 'Tagihan',width: '7%',edit :false},
								simpatda_denda: {title: 'Denda Lapor',width: '7%',edit :false},
								payment_code: {title: 'Kode Verifikasi',width: '10%'},
								validasi: {title: 'validasi',width: '10%'},
								operator: {title: 'Operator',width: '10%'}, 
								pelaksana_kegiatan: {title: 'Pelaksana',width: '10%'}, 
								payment_paid: {title: 'Tanggal Bayar',width: '10%'},
								patda_kurangbayar: {title: 'Kurang Bayar',width: '10%'},
								payment_paid_kurangbayar: {title: 'Tanggal Kurang Bayar',width: '10%'},
								
								
							}
						});
						$('#jenis_lapor-{$this->_i} option').hide();
						$('#jenis-{$this->_i}').change(function () {
							id_pajak = $(this).val();
							if(!$('#jenis_lapor-{$this->_i} option[data-id_pajak=" . "' + id_pajak + '" . "]:visible').length) {
								$('#jenis_lapor-{$this->_i}').val('');
							}
							$('#jenis_lapor-{$this->_i} option').hide();
    						$('#jenis_lapor-{$this->_i} option[data-id_pajak=" . "' + id_pajak + '" . "]').show();
						});

			

						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {                                
								sptpd : $('#sptpd-{$this->_i}').val(),
								wp_alamat : $('#wp_alamat-{$this->_i}').val(),                                
								wp_nama : $('#wp_nama-{$this->_i}').val(),
								simpatda_dibayar : $('#simpatda_dibayar-{$this->_i}').val(),
								jenis : $('#jenis-{$this->_i}').val(),
								expired_date1 : $('#expired_date1-{$this->_i}').val(),
								expired_date2 : $('#expired_date2-{$this->_i}').val(),    
								simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),
								simpatda_bulan_pajak : $('#simpatda_bulan_pajak-{$this->_i}').val(),
								jenis_lapor : $('#jenis_lapor-{$this->_i}').val(),
								bank : $('#bank-{$this->_i}').val(),
								CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
								CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
								operator : $('#operator-{$this->_i}').val(),
								payment_code : $('#payment_code-{$this->_i}').val(),
								kurang_bayar : $('#kurang_bayar-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click();    


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
				</script>";

		echo $html;
	}

	public function grid_data_status_bayar()
	{
		try {
			$arr_config = $this->get_config_value($this->_a);

			$dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);


			$where = " 1=1 ";


			$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
			$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
			$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
			$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
			$where .= (!isset($_REQUEST['bank']) || $_REQUEST['bank'] == "" || $_REQUEST['bank'] == 'undefined') ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";

			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND A.kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";
			$where .= $_REQUEST['operator'] == "" ? "" : " AND (operator like '%{$_REQUEST['operator']}%' or operator like '%{$_REQUEST['operator']}%')";
			$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

			if ($this->_s == 1) {
				$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
			}

			if (!empty($_REQUEST['jenis'])) {
				$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
				if ($_REQUEST['jenis'] == 8) {

					$tipe_pajak_restoran = $this->jenis_tipe_pajak_restoran[$_REQUEST['jenis_lapor']];
					// var_dump($_REQUEST['jenis_lapor'], $tipe_pajak_restoran);die;
				} else {

					$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
				}

				if (empty($_REQUEST['jenis_lapor'])) {
					$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( a.simpatda_type='{$jenis_reg}' OR a.simpatda_type='{$jenis_nonreg}' )";
				} else {
					if ($_REQUEST['jenis'] == 8) {
						$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $tipe_pajak_restoran;
					} else {
						$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $jenis_nonreg;
					}
					$where .= " AND permen.kdrek='{$_REQUEST['jenis_lapor']}'";
					// $where .= " AND a.simpatda_type='{$jenis}'";
				}
			} elseif (!empty($_REQUEST['jenis_lapor'])) {
				$where .= ($_REQUEST['jenis_lapor'] == 1) ? " AND a.simpatda_type<=12 " : "AND a.simpatda_type>12 ";
			}
			// var_dump($where);die;

			// $where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
			$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND YEAR(saved_date)='{$_REQUEST['simpatda_tahun_pajak']}'";
			// $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";
			$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND MONTH(saved_date)='{$_REQUEST['simpatda_bulan_pajak']}'";

			if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
				$where .= ($this->_s == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
			} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
				$where .= ($this->_s == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
			} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
				$where .= ($this->_s == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
			}

			$where .= ($this->_s == 1) ? " AND payment_flag = 1" : " AND (payment_flag != 1 OR payment_flag IS NULL)";

			if ($_REQUEST['simpatda_dibayar'] != 0) {
				$arr_dibayar = array(
					1 => " (simpatda_dibayar < 5000000) ",
					2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
					3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
					4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
					5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
					6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
					7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
					8 => "(simpatda_dibayar >= 100000000)",
					9 => "(simpatda_dibayar > 100000000)"
				);
				$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
			left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek WHERE {$where}";
			// var_dump($query);die;
			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data        
			// if($jenis_reg == 6 && $jenis_nonreg == 26){
			// 	$query = "select  jtp.nama_tipe_pajak,jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
			// 	date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, simpatda_denda, IFNULL(payment_flag,0) as payment_flag, 
			// 	date_format(saved_date,'%d-%m-%Y') as saved_date, 
			// 	date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, IF(payment_flag=1, operator, '') as operator, patda_kurangbayar, payment_paid_kurangbayar,
			// 	a.masa_pajak_awal, a.masa_pajak_akhir
			// 	FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
			// 	left join jenis_tipe_pajak jtp on jtp.simpatda_type = a.simpatda_type 
			// 	WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			// }else{
			$query = "select doc.pelaksana_kegiatan,permen.nmrek,jenis,op_nama,b.jenis_sw, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, simpatda_denda, IFNULL(payment_flag,0) as payment_flag, 
					date_format(saved_date,'%d-%m-%Y') as saved_date, a.validasi_pelaporan,
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, IF(payment_flag=1, operator, '') as operator, patda_kurangbayar, payment_paid_kurangbayar,
					a.masa_pajak_awal, a.masa_pajak_akhir
					FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					left join patda_restoran_doc doc on a.id_switching = doc.CPM_ID
					left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek
					WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			// }	
// var_dump($query);die; 	
			$result = mysqli_query($Conn_gw, $query);


			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				//if($row['simpatda_bulan_pajak'] == "00"){
				//	$row['simpatda_bulan_pajak'] = $row['masa_pajak_awal']." s.d ".$row['masa_pajak_akhir'];
				//}else{
				//	$row['simpatda_bulan_pajak'] = isset($this->arr_bulan[(int) $row['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $row['simpatda_bulan_pajak']] : $row['simpatda_bulan_pajak'];
				//}
				if ($row['nama_tipe_pajak'] == "" || $row['nama_tipe_pajak'] == null) {

					$row['jenis'] = $row['jenis'];
				} else {
					$row['jenis'] = $row['nama_tipe_pajak'];
				}
				$row['simpatda_bulan_pajak'] = $row['masa_pajak_awal'] . " s.d " . $row['masa_pajak_akhir'];
				if ($row['jenis'] <> $row['nmrek']) {
					$row['jenis_rekening'] = $row['jenis_sw'] . "<br>(" . $row['nmrek'] . ")";
				} else {
					$row['jenis_rekening'] = $row['jenis'];
				}

				if ($row['validasi_pelaporan'] == 1) {
					$row['validasi'] = '<div style="display: flex; align-items: center; gap: 10px;">
											<div style="width:10px; height:10px; border-radius:50%; background-color:green;"></div>
											<span>Sudah Validasi</span>
										</div>';
				}else{
					$row['validasi'] = '<div style="display: flex; align-items: center; gap: 10px;">
											<div style="width:10px; height:10px; border-radius:50%; background-color:red;"></div>
												<span>belum Validasi</span>
											</div>';
				}

				$row['npwpd'] = Pajak::formatNPWPD($row['npwpd']);
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar'], 2);
				$row['simpatda_denda'] = number_format($row['simpatda_denda'], 2);
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

	public function filtering_belum_lapor($id)
	{
		$opt_jenis_pajak = "";
		foreach ($this->arr_pajak as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$opt_tahun = "";
		$thn = date("Y");
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$opt_bulan = "";
		$bln = date("m");
		for ($b = 1; $b <= 12; $b++) {
			$opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
		}

		$kec = $this->get_list_kecamatan();
		$opt_kecamatan = "<option value=\"\">Semua</option>";
		foreach ($kec as $k => $v) {
			$opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
		}
		$opt_kelurahan = "<option value=\"\">Semua</option>";

		$html = "<div class=\"filtering\">
					<style> .monitoring td{background:transparent}</style>
						<form>
							<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
								<tr>
									<td>Tahun Pajak </td>
									<td> :
										<select class=\"form-control\" style=\"width:100px;height:30px;display:inline-block;\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select>   
										<select class=\"form-control\" style=\"width:100px;height:30px;display:inline-block;\" name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select>
									</td>
									<td></td>
									<td>NPWPD</td>
									<td>: <input class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;\" type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\"></td>
									<td></td>
									<td>Kecamatan</td>
									<td>: <select class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;\" name=\"kecamatan-{$id}\" id=\"kecamatan-{$id}\">{$opt_kecamatan}</select></td>
									<td></td>
								</tr>
								<tr>
									<td>Jenis Pajak</td>
									<td>: <select class=\"form-control\" style=\"width:205px;height:30px;display:inline-block;\" id=\"CPM_JENIS_PAJAK-{$id}\" name=\"CPM_JENIS_PAJAK-{$id}\">{$opt_jenis_pajak}</select>                                        
									</td>   
									<td></td>
									<td>Nama WP / Tempat Usaha</td>
									<td>: <input class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;\" type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\"></td>
									<td></td>
									<td>Kelurahan</td>
									<td>: <select class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;\" name=\"kelurahan-{$id}\" id=\"kelurahan-{$id}\" style=\"width:92%\">{$opt_kelurahan}</select></td>
									<td></td>
									<td colspan=\"2\">
										<input class=\"btn btn-sm btn-secondary\" style=\"height:30px;\" type=\"button\" name=\"button2\" id=\"cari-{$id}\" value=\"Tampilkan\"/>
										<input class=\"btn btn-sm btn-secondary\" style=\"height:30px;\" type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"javascript:toExcelBelumLapor({$id})\"/>
										<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
									</td>
								</tr>
							</table>
						</form>
				</div> ";
		return $html;
	}

	public function grid_table_belum_lapor()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-bayar";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_belum_lapor($this->_i)}
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
							defaultSorting: 'CPM_NPWPD ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-belum-lapor.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_NAMA_WP: {title: 'Nama Wajib Pajak',width: '10%'},
								CPM_NAMA_OP: {title: 'Nama Objek Pajak',width: '10%'}
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {                                
								CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),                                
								CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
								CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val(),
								CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
								kecamatan : $('#kecamatan-{$this->_i}').val(),
								kelurahan : $('#kelurahan-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click(); 
						$('#kecamatan-{$this->_i}').change(function(){
							$.ajax({
								type: \"POST\",
								url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
								data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
								async:false,
								success: function(html){
									$('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>'+html);
								}
							});
						});
					});
				</script>";

		echo $html;
	}

	public function grid_data_belum_lapor()
	{
		try {

			$PAJAK = strtoupper($this->arr_pajak_table[$_REQUEST['CPM_JENIS_PAJAK']]);
			$wilayah = $_REQUEST['kecamatan'] == "" ? "" : " AND (CPM_KECAMATAN_OP like '%" . $_REQUEST['kecamatan'] . "%' or CPM_KECAMATAN_WP like '%" . $_REQUEST['kecamatan'] . "%')";
			$wilayah .= $_REQUEST['kelurahan'] == "" ? "" : " AND (CPM_KELURAHAN_OP like '%" . $_REQUEST['kelurahan'] . "%' or CPM_KELURAHAN_WP like '%" . $_REQUEST['kelurahan'] . "%')";

			$where = "CPM_ID NOT IN(
					SELECT prf.CPM_ID
					FROM  PATDA_{$PAJAK}_DOC pjk
					INNER JOIN PATDA_{$PAJAK}_PROFIL prf ON prf.CPM_ID = pjk.CPM_ID_PROFIL  
					WHERE  CPM_TAHUN_PAJAK='{$_REQUEST['CPM_TAHUN_PAJAK']}' AND CPM_MASA_PAJAK='{$_REQUEST['CPM_MASA_PAJAK']}' AND prf.CPM_APPROVE ='1' AND prf.CPM_AKTIF ='1' {$wilayah}
				) AND CPM_APPROVE ='1' AND CPM_AKTIF ='1' AND CPM_NPWPD like '%{$_REQUEST['CPM_NPWPD']}%'
				  AND (CPM_NAMA_WP like '%{$_REQUEST['CPM_NAMA_WP']}%' OR CPM_NAMA_OP like '%{$_REQUEST['CPM_NAMA_WP']}%') {$wilayah}";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_{$PAJAK}_PROFIL WHERE {$where}";

			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data        
			$query = "SELECT '{$_REQUEST['CPM_TAHUN_PAJAK']}' as CPM_TAHUN_PAJAK,'{$_REQUEST['CPM_MASA_PAJAK']}' as CPM_MASA_PAJAK, CPM_NPWPD, CPM_NAMA_WP, CPM_NAMA_OP FROM PATDA_{$PAJAK}_PROFIL
						WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($this->Conn, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				$row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']] . " " . $row['CPM_TAHUN_PAJAK'];
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

	public function grafik_filter()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "status-bayar";
		$uid = "xxx";

		$id = $this->_i;

		$opt_jenis_pajak = "<option value=\"All\">Semua</option>";
		foreach ($this->arr_pajak_gw as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$opt_tahun = "<option value=\"All\">Semua</option>";
		$thn = date("Y");
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$html = "<div class=\"filtering\">
					<style> .monitoring td{background:transparent}</style>
					<script type=\"text/javascript\">                        
						function getLaporan(sts) {
							var tahun = $(\"#tahun-pajak-\" + sts).val();
							var jns = $(\"#jenispajak-\" + sts).val();
							$(\"#monitoring-content-\" + sts).html(\"loading ...\");

							var svc = \"\";
							$(\"#monitoring-content-\" + sts).load(\"view/{$DIR}/{$modul}/{$submodul}/svc-grafik.php?q=" . base64_encode("{'a':'{$this->_a}', 'm':'{$this->_m}', 's':'23','uid':'{$uid}'}") . "\",
									{th: tahun, jns: jns}, function (response, status, xhr) {
								if (status == \"error\") {
									var msg = \"Sorry but there was an error: \";
									$(\"#monitoring-content-\" + sts).html(msg + xhr.status + \" \" + xhr.statusText);
								}
							});
						}
					</script>
						<form>
							<table width=\"1200\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
								<tr>
									<td width=\"115\">Tahun Pajak </td>
									<td width=\"3\">:</td>
									<td width=\"80\"> 
										<select name=\"tahun-pajak-{$id}\" id=\"tahun-pajak-{$id}\">{$opt_tahun}</select>
									</td>                                    
									<td width=\"100\"> Jenis Pajak </td>
									<td width=\"280\">: <select id=\"jenispajak-{$id}\" name=\"jenispajak-{$id}\">{$opt_jenis_pajak}</select></td>    
									<td><input type=\"button\" name=\"cari-{$id}\" id=\"cari-{$id}\" value=\"Tampilkan\" onClick=\"javascript:getLaporan({$id})\"/></td>
								</tr>
							</table>
						</form>                        
				</div> <div id=\"monitoring-content-{$id}\" class=\"monitoring-content\">";
		echo $html;
	}

	public function countforlink_penre()
	{
		echo $this->count_penre($this->_i);
	}

	private function count_penre($id)
	{
		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);


		$where = " ";
		$where .= $_REQUEST['JNS_PAJAK'] == "" ? "" : " WHERE B.id_sw like \"{$_REQUEST['JNS_PAJAK']}%\" ";
		/* $where .= $_REQUEST['CPM_TAHUN_PAJAK'] == "" ? "" : " AND A.simpatda_tahun_pajak like \"{$_REQUEST['CPM_TAHUN_PAJAK']}%\" ";
		$where .= $_REQUEST['CPM_MASA_PAJAK'] == "" ? "" : " AND A.simpatda_bulan_pajak like \"{$_REQUEST['CPM_MASA_PAJAK']}%\" "; */
		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND YEAR(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) <= \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where}";

		$result = mysqli_query($Conn_gw, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['RecordCount'];
	}

	public function countforlink()
	{

		//1:sudah bayar, 2:belum bayar, 3:belum lapor, 4:tagihan, 5:tunggakan
		if ($this->_i == 1 || $this->_i == 2) echo $this->count_status_bayar($this->_i);
		elseif ($this->_i == 3) echo $this->count_belum_lapor($this->_i);
		elseif ($this->_i == 4 || $this->_i == 5) echo $this->count_tagihan_tunggakan($this->_i);
	}

	public function countforlinkdok()
	{
		$PAJAK = strtoupper($this->arr_pajak_table[$this->_i]);

		$where = "(";
		$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
		$where .= (isset($_REQUEST['CPM_TRAN_STATUS']) && $_REQUEST['CPM_TRAN_STATUS'] != "") ?
			" AND tr.CPM_TRAN_STATUS  = '{$_REQUEST['CPM_TRAN_STATUS']}') " :
			" AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')) ";

		$where .= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		if ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] == "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR1']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] == "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR2']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND str_to_date(substr(CPM_TGL_LAPOR,1,10),'%d-%m-%Y') BETWEEN '{$_REQUEST['CPM_TGL_LAPOR1']}' AND '{$_REQUEST['CPM_TGL_LAPOR2']}'";
		}

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_{$PAJAK}_DOC pj 
						INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
						INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID
						WHERE {$where}";
		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		ob_clean();
		echo $recordCount = $row['RecordCount'];
	}



	private function count_tagihan_tunggakan($id)
	{

		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		// id 4 = tagihan, 5 = tunggakan
		$where = ($this->_i == 4) ? " expired_date >= NOW() " : " expired_date < NOW() ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND op_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND op_nama like '%{$_REQUEST['wp_nama']}%'";

		if (empty($_REQUEST['jenis'])) {

			if (isset($_REQUEST['simpatda_jenis'])) {
				if ($_REQUEST['simpatda_jenis'] == 1) {
					$where .= $_REQUEST['simpatda_jenis'] == "" ? "" : " AND b.id_sw in (1,7)";
				} else if ($_REQUEST['simpatda_jenis'] == 2) {
					$where .= $_REQUEST['simpatda_jenis'] == "" ? "" : " AND b.id_sw in (2,3,4,5,6,8,9)";
				}
			}
		} else {
			$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
			$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( simpatda_type='{$jenis_reg}' OR simpatda_type='{$jenis_nonreg}' )";
		}

		$where .= $_REQUEST['kecamatan'] == "" ? "" : " AND (kecamatan_op like '%{$_REQUEST['kecamatan']}%' or kecamatan_wp like '%{$_REQUEST['kecamatan']}%')";
		$where .= $_REQUEST['kelurahan'] == "" ? "" : " AND (kelurahan_op like '%{$_REQUEST['kelurahan']}%' or kelurahan_wp like '%{$_REQUEST['kelurahan']}%')";

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";

		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= " AND (payment_flag != 1 OR payment_flag IS NULL)";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

		$result = mysqli_query($Conn_gw, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['RecordCount'];
	}

	private function count_status_bayar($id)
	{

		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = " 1=1 ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
		// $where .= $_REQUEST['simpatda_dibayar'] == "" ? "" : " AND simpatda_dibayar='{$_REQUEST['simpatda_dibayar']}'";
		$where .= $_REQUEST['jenis'] == "" ? "" : " AND b.id_sw='{$_REQUEST['jenis']}'";

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		if ($id == 1) {
			$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
		}

		if ($id == 1) {
			$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
		}

		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";

		$where .= ($_REQUEST['bank'] == "" || $_REQUEST['bank'] == "undefined") ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";

		//if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
		//	$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		//} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		//} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		//}

		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= ($id == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($id == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($id == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($id == 1) ? " AND payment_flag = 1" : " AND (payment_flag != 1 OR payment_flag IS NULL)";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}
		// echo $where; exit;
		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

		// echo $query; exit;
		$result = mysqli_query($Conn_gw, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['RecordCount'];
	}

	private function count_belum_lapor($id)
	{
		$PAJAK = strtoupper($this->arr_pajak_table[$_REQUEST['CPM_JENIS_PAJAK']]);

		$where = "CPM_ID NOT IN(
					SELECT prf.CPM_ID
					FROM  PATDA_{$PAJAK}_DOC pjk
					INNER JOIN PATDA_{$PAJAK}_PROFIL prf ON prf.CPM_ID = pjk.CPM_ID_PROFIL  
					WHERE  CPM_TAHUN_PAJAK='{$_REQUEST['CPM_TAHUN_PAJAK']}' AND CPM_MASA_PAJAK='{$_REQUEST['CPM_MASA_PAJAK']}' AND prf.CPM_APPROVE ='1' AND prf.CPM_AKTIF ='1'
				) AND CPM_APPROVE ='1' AND CPM_AKTIF ='1' AND CPM_NPWPD like '%{$_REQUEST['CPM_NPWPD']}%'
				  AND (CPM_NAMA_WP like '%{$_REQUEST['CPM_NAMA_WP']}%' OR CPM_NAMA_OP like '%{$_REQUEST['CPM_NAMA_WP']}%')";

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_{$PAJAK}_PROFIL WHERE {$where}";

		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['RecordCount'];
	}

	public function download_excel_status_bayar_penre()
	{
		$this->download_excel_penre_new();
		exit;

		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = " ";
		$where .= (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != "") ? " WHERE B.id_sw like \"{$_REQUEST['JNS_PAJAK']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND YEAR(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) <= \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
		// $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = \"{$_REQUEST['CPM_KECAMATAN']}\" " : "";
		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		$query = "SELECT * FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where} LIMIT {$offset}, {$total}";
		$result = mysqli_query($Conn_gw, $query);

		$query1 = "SELECT jenis_sw FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where} LIMIT 1";
		$result1 = mysqli_query($Conn_gw, $query1);

		//Get Params
		$MONTH_NOW_A = date("F");
		$YEAR_NOW_A = date("Y");
		$JNSJNS = mysqli_fetch_assoc($result1);
		$JNS_PAJAK_A = $_REQUEST['JNS_PAJAK'] == "" ? "SEMUA" : $JNSJNS['jenis_sw'];
		$TAHUN_PAJAK_A = $_REQUEST['CPM_TAHUN_PAJAK'] == "" ? $YEAR_NOW_A : $_REQUEST['CPM_TAHUN_PAJAK'];
		$MASA_PAJAK_A = $_REQUEST['CPM_MASA_PAJAK'] == "" ? "DESEMBER" : $_REQUEST['CPM_MASA_PAJAK'];

		//Get Nama Kecamatan
		$rowws = array();
		while ($rowData = mysqli_fetch_assoc($result)) {
			$sql = "SELECT CPM_KECAMATAN FROM PATDA_MST_KECAMATAN WHERE CPM_KEC_ID = \"{$rowData['kecamatan_op']}\"";
			$res = mysqli_query($this->Conn, $sql);

			$rowData['kecamatan_op'] = "";
			while ($roww = mysqli_fetch_assoc($res)) {
				$rowData['kecamatan_op'] = $roww['CPM_KECAMATAN'];
			}
			$rowws[] = $rowData;
		}

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");
		// mergeCells
		$objPHPExcel->getActiveSheet()
			->mergeCells('A1:I1')
			->mergeCells('A2:I2')
			->mergeCells('A3:I3')
			->mergeCells('A4:A5')
			->mergeCells('B4:B5')
			->mergeCells('C4:C5')
			->mergeCells('D4:E4')
			->mergeCells('F4:H4')
			->mergeCells('I4:I5');
		// Add some data
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'PENETAPAN DAN REALISASI PAJAK ' . strtoupper($JNS_PAJAK_A))
			->setCellValue('A2', 'KABUPATEN LAMPUNG SELATAN TAHUN ' . $TAHUN_PAJAK_A)
			->setCellValue('A3', 'KEADAAN SAMPAI DENGAN BULAN ' . strtoupper($this->arr_bulan[$MASA_PAJAK_A]))
			->setCellValue('A4', 'NO')
			->setCellValue('B4', 'KECAMATAN')
			->setCellValue('C4', 'ALAMAT')
			->setCellValue('D4', 'NO')
			->setCellValue('F4', 'TAHUN - ' . $TAHUN_PAJAK_A)
			->setCellValue('I4', 'TGL BAYAR')
			->setCellValue('D5', ($_REQUEST['JNS_PAJAK'] == 7 ? 'SKPD' : 'SPTPD'))
			->setCellValue('E5', 'SSPD')
			->setCellValue('F5', 'PENETAPAN')
			->setCellValue('G5', 'REALISASI')
			->setCellValue('H5', 'SISA');

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 6;
		$sumRows = mysqli_num_rows($result);
		// $totalPayment = 0;
		foreach ($rowws as $rowData) {
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 5));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['kecamatan_op']));
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['op_alamat']));
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($rowData['sptpd']));
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, ($rowData['sptpd']));
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, ($rowData['simpatda_dibayar']));
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($rowData['simpatda_dibayar'] - $rowData['patda_total_bayar']));
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ($rowData['patda_total_bayar']));
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($rowData['payment_paid']));
			$row++;
		}

		// while ($rowData = $rowws) {
		// }

		// Rename sheet
		$sheetName = "";
		if ($tagihan == true) {
			$sheetName = "Tagihan";
		} else {
			$sheetName = $this->_i == 1 ? 'Sudah Bayar' : 'Belum bayar';
		}
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:I5')->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:I{$row}")->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle('A1:I' . ($row - 1))->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('I2:I' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFill()->getStartColor()->setRGB('E4E4E4');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('4');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('15');
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);

		ob_clean();

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}


	private function download_excel_penre_new()
	{
		// print_r($_REQUEST);exit;
		$arr_config = $this->get_config_value($this->_a);

		$src_kec = $this->get_list_kecamatan();
		$src_kel = $this->get_list_kelurahan('', 'LIST');
		$list_kecamatan = array();
		$list_kelurahan = array();
		foreach ($src_kec as $kec) {
			$list_kecamatan[$kec->CPM_KEC_ID] = $kec->CPM_KECAMATAN;
		}
		foreach ($src_kel as $kel) {
			$list_kelurahan[$kel->CPM_KEL_ID] = $kel->CPM_KELURAHAN;
		}

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);

		$where = " WHERE 1=1 ";
		$where .= (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != "") ? " AND B.id_sw = \"{$_REQUEST['JNS_PAJAK']}\" " : "";

		$thn_val = $_REQUEST['CPM_TAHUN_PAJAK'] != '' ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
		$bln_val = $_REQUEST['CPM_MASA_PAJAK'] != '' ? substr('0' . $_REQUEST['CPM_MASA_PAJAK'], -2, 2) : date('m');
		if ($_REQUEST['CPM_MASA_PAJAK'] != '') {
			$where .= " AND DATE_FORMAT(A.saved_date,'%Y-%m') = '{$thn_val}-{$bln_val}' ";
		} else {
			$bln_val = (int) $bln_val;
			$where .= " AND YEAR(A.saved_date)='{$thn_val}' AND MONTH(A.saved_date) <= '{$bln_val}' ";
		}
		// $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND YEAR(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		// $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(A.saved_date,'%Y-%m-%d')) <= \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

		// $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = \"{$_REQUEST['CPM_KECAMATAN']}\" " : "";
		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND A.kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND A.kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		if ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] == "") {
			$where .= " AND str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') = '{$_REQUEST['CPM_TGL_LAPOR1']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] == "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') <= '{$_REQUEST['CPM_TGL_LAPOR2']}'";
		} elseif ($_REQUEST['CPM_TGL_LAPOR1'] != "" && $_REQUEST['CPM_TGL_LAPOR2'] != "") {
			$where .= " AND (str_to_date(C.CPM_TGL_LAPOR,'%d-%m-%Y') BETWEEN '{$_REQUEST['CPM_TGL_LAPOR1']}' AND '{$_REQUEST['CPM_TGL_LAPOR2']}')";
		}


		if (isset($_REQUEST['CPM_JENIS_RESTORAN']) && $_REQUEST['CPM_JENIS_RESTORAN'] != "") {
			if ($_REQUEST['CPM_JENIS_RESTORAN'] == 1) {
				$where .= " AND B.id_sw=8 AND A.simpatda_rek!='4.1.01.07.07'";
			} elseif ($_REQUEST['CPM_JENIS_RESTORAN'] == 2) {
				$where .= " AND B.id_sw=8 AND A.simpatda_rek='4.1.01.07.07'";
			}
		}


		$tbl_pajak = $this->arr_idpajak;
		if (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != '') {
			$tbl_pajak = array($_REQUEST['JNS_PAJAK'] => $this->arr_idpajak[$_REQUEST['JNS_PAJAK']]);
		}

		foreach ($tbl_pajak as $k => $v) {
			$tbl_pajak[$k] = 'PATDA_' . strtoupper($v) . '_DOC';
		}

		#query select list data
		/* // $query = "SELECT * FROM PATDA_PETUGAS WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		$query = "SELECT * FROM SIMPATDA_GW A 
		INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type 
		{$where} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		// echo $query;exit();
		$result = mysql_query($query, $Conn_gw);
		// if ($result == true) {
		//     print_r(mysql_fetch_assoc($result));
		// }else{
		//     echo mysql_error();
		// }
		// echo $query;
		// print_r($this->get_config_value($this->_a)); */
		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		// $query = "select *,DATE_FORMAT(payment_paid,'%d-%m-%Y') as payment_paid FROM (";
		$query = "SELECT *,DATE_FORMAT(payment_paid,'%d-%m-%Y') as payment_paid,MONTH(masa_pajak_awal) as masa_pajak,B.id_sw as jenis_pajak,DATE_ADD(masa_pajak_akhir, INTERVAL -2 MONTH) as cek_triwulan FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where} LIMIT {$offset}, {$total}";

		$tbl_pajak = $this->arr_idpajak;
		if (isset($_REQUEST['JNS_PAJAK']) && $_REQUEST['JNS_PAJAK'] != '') {
			$tbl_pajak = array($_REQUEST['JNS_PAJAK'] => $this->arr_idpajak[$_REQUEST['JNS_PAJAK']]);
		}

		foreach ($tbl_pajak as $k => $v) {
			$tbl_pajak[$k] = 'PATDA_' . strtoupper($v) . '_DOC';
		}


		$query = "select *,DATE_FORMAT(payment_paid,'%d-%m-%Y') as payment_paid,MONTH(masa_pajak_awal) as masa_pajak FROM (";
		$arr_query = array();
		foreach ($tbl_pajak as $id => $tbl) {
			$arr_query[] = "(SELECT A.*,C.CPM_TGL_LAPOR,B.id_sw jenis_pajak,DATE_ADD(masa_pajak_akhir, INTERVAL -2 MONTH) as cek_triwulan from SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type INNER JOIN {$tbl} C on C.CPM_ID=A.id_switching $where AND B.id_sw='$id')";
		}
		$query .= implode(' UNION ', $arr_query);
		$query .= ") A";
		$result = mysqli_query($Conn_gw, $query) or die(mysql_error());

		/* $arr_query = array();
		foreach($tbl_pajak as $id=>$tbl){
			$arr_query[] = "(SELECT A.*,MONTH(A.masa_pajak_awal) as bulan_pajak,C.CPM_TGL_LAPOR,B.id_sw from SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type INNER JOIN SW_PATDA.{$tbl} C on C.CPM_ID=A.id_switching $where AND B.id_sw='$id')";
		}
		$query .= implode(' UNION ', $arr_query);
		$query .= ") A LIMIT {$offset}, {$total}"; */


		// echo '<pre>',$query,"\n\n";exit;

		/* $query1 = "SELECT jenis_sw FROM SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type {$where} LIMIT 1";
		$result1 = mysql_query($query1, $Conn_gw); */

		//Get Params
		$MONTH_NOW_A = date("F");
		$YEAR_NOW_A = date("Y");
		// $JNSJNS = mysql_fetch_assoc($result1);
		$JNS_PAJAK_A = $_REQUEST['JNS_PAJAK'] == "" ? "SEMUA" : $this->arr_pajak[$_REQUEST['JNS_PAJAK']];
		$TAHUN_PAJAK_A = $_REQUEST['CPM_TAHUN_PAJAK'] == "" ? $YEAR_NOW_A : $_REQUEST['CPM_TAHUN_PAJAK'];
		$MASA_PAJAK_A = $_REQUEST['CPM_MASA_PAJAK'] == "" ? "DESEMBER" : $_REQUEST['CPM_MASA_PAJAK'];

		if ($_REQUEST['CPM_MASA_PAJAK'] != '') {
			$periode = 'BULAN ' . strtoupper($this->arr_bulan[$_REQUEST['CPM_MASA_PAJAK']]) . ' ' . $TAHUN_PAJAK_A;
		} else {
			if ($_REQUEST['CPM_TAHUN_PAJAK'] != '')
				$periode = 'PERIODE SAMPAI ' . strtoupper($this->arr_bulan[date('n')]) . ' ' . $_REQUEST['CPM_TAHUN_PAJAK'];
			else
				$periode = 'PERIODE SAMPAI ' . strtoupper($this->arr_bulan[date('n')]) . ' ' . $TAHUN_PAJAK_A;
		}
		$triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');

		//Get Nama Kecamatan
		/* $rowws = Array();
		while ($rowData = mysql_fetch_assoc($result)) {
			$rowws[]= $rowData;
		} */

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");
		// mergeCells
		$objPHPExcel->getActiveSheet()
			->mergeCells('A1:K1')
			->mergeCells('A2:K2')
			->mergeCells('A3:K3')

			->mergeCells('A4:A5')
			->mergeCells('B4:B5')
			->mergeCells('C4:C5')
			->mergeCells('D4:D5')
			->mergeCells('E4:E5')
			->mergeCells('F4:F5')
			->mergeCells('G4:G5')
			->mergeCells('H4:H5')
			->mergeCells('I4:I5')
			->mergeCells('J4:J5')
			->mergeCells('K4:L4')
			->mergeCells('M4:M5');
		// Add some data
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'DAFTAR KETETAPAN PAJAK ' . strtoupper($JNS_PAJAK_A))
			->setCellValue('A2', $arr_config['NAMA_BADAN_PENGELOLA'] . ' KABUPATEN LAMPUNG SELATAN')
			->setCellValue('A3', $periode)

			->setCellValue('A4', 'NO')
			->setCellValue('B4', 'TGL SPTPD')
			->setCellValue('C4', 'NO SPTPD')
			->setCellValue('D4', 'NPWPD')
			->setCellValue('E4', 'NAMA WAJIB PAJAK')
			->setCellValue('F4', 'MASA PAJAK')
			->setCellValue('G4', 'ALAMAT')
			->setCellValue('H4', 'KECAMATAN')
			->setCellValue('I4', 'KELURAHAN')
			->setCellValue('J4', 'KETETAPAN')
			->setCellValue('K4', 'SSPD/STS')
			->setCellValue('K5', 'TERBAYAR')
			->setCellValue('L5', 'TANGGAL')
			->setCellValue('M4', 'PIUTANG');;

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 6;
		$sumRows = mysqli_num_rows($result);
		if ($sumRows == 0) {
			echo "<script>alert('Tidak ada data');history.back();</script>";
			exit;
		}
		while ($rowData = mysqli_fetch_assoc($result)) {
			$npwpd = Pajak::formatNPWPD($rowData['npwpd']);
			$bl1 = date('Y-n', strtotime($rowData['masa_pajak_awal']));
			$b1 = explode('-', $bl1);
			$b1 = $b1[1];
			$tw = date('Y-n', strtotime($rowData['cek_triwulan']));
			// echo $rowData['sptpd'],' = ',$bl1,' - ',$tw,"\n";
			if ($rowData['jenis_pajak'] == 1) {
				$masa_pajak = isset($triwulan[$rowData['masa_pajak']]) ? $triwulan[$rowData['masa_pajak']] . ' ' . $rowData['simpatda_tahun_pajak'] : $this->arr_bulan[$rowData['masa_pajak']] . ' ' . $rowData['simpatda_tahun_pajak'];
			} elseif ($rowData['jenis_pajak'] == 4) {
				/* if($rowData['simpatda_type']>12){
					$masa_pajak = $this->arr_bulan[$rowData['masa_pajak']].' '.$rowData['simpatda_tahun_pajak'];
				}else{ */
				if ($bl1 == $tw && isset($triwulan[$rowData['masa_pajak']]))
					$masa_pajak = $triwulan[$rowData['masa_pajak']] . ' ' . $rowData['simpatda_tahun_pajak'];
				else
					$masa_pajak = $this->arr_bulan[$rowData['masa_pajak']] . ' ' . $rowData['simpatda_tahun_pajak'];
				// $masa_pajak = isset($triwulan[$rowData['masa_pajak']]) ? $triwulan[$rowData['masa_pajak']].' '.$rowData['simpatda_tahun_pajak'] : $this->arr_bulan[$rowData['masa_pajak']].' '.$rowData['simpatda_tahun_pajak'];
				/* } */
			} else {
				$masa_pajak = $this->arr_bulan[$rowData['masa_pajak']] . ' ' . $rowData['simpatda_tahun_pajak'];
			}

			$rowData['kecamatan_op'] = isset($list_kecamatan[$rowData['kecamatan_op']]) ? $list_kecamatan[$rowData['kecamatan_op']] : $rowData['kecamatan_op'];
			$rowData['kelurahan_op'] = isset($list_kelurahan[$rowData['kelurahan_op']]) ? $list_kelurahan[$rowData['kelurahan_op']] : $rowData['kelurahan_op'];

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 5));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, date('d-m-Y', strtotime(substr($rowData['saved_date'], 0, 10))));
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['sptpd']));
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($npwpd));
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, ($rowData['wp_nama']));
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, ($masa_pajak));
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($rowData['wp_alamat']));
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ($rowData['kecamatan_op']));
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($rowData['kelurahan_op']));
			$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['simpatda_dibayar']));
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, ($rowData['patda_total_bayar']));
			$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($rowData['payment_paid'] != '' ? date('d-m-Y H:i', strtotime($rowData['payment_paid'])) : ''));
			$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, ($rowData['simpatda_dibayar'] - $rowData['patda_total_bayar'] > 0 ? $rowData['simpatda_dibayar'] : 0));
			$row++;
		}

		// get data sampai bulan sebelumnya
		$data_prev = (object) array('ketetapan' => 0, 'pembayaran' => 0, 'piutang' => 0);
		if ($_REQUEST['CPM_MASA_PAJAK'] != '') {
			$where_jenis = $_REQUEST['JNS_PAJAK'] != '' ? "B.id_sw='{$_REQUEST['JNS_PAJAK']}' AND " : '';
			$prev_bln = $TAHUN_PAJAK_A . '-' . substr('0' . $_REQUEST['CPM_MASA_PAJAK'], -2, 2) . '-01';
			$query_prev = "SELECT SUM(A.simpatda_dibayar) as ketetapan, SUM(A.patda_total_bayar) as pembayaran, SUM(IF(A.payment_flag=0,A.simpatda_dibayar,0)) as piutang 
			from SIMPATDA_GW A INNER JOIN SIMPATDA_TYPE B ON B.id = A.simpatda_type WHERE {$where_jenis} YEAR(A.saved_date)='{$TAHUN_PAJAK_A}' AND DATE(A.saved_date)<'{$prev_bln}'";
			// echo $query_prev,"\n\n";
			$sql_prev = mysqli_query($Conn_gw, $query_prev) or die(mysql_error());
			$prev = mysqli_fetch_object($sql_prev);
			if (!empty($prev->ketetapan)) {
				$data_prev->ketetapan = $prev->ketetapan;
				$data_prev->pembayaran = $prev->pembayaran + 0;
				$data_prev->piutang = $prev->piutang + 0;
			}
		}

		// footer
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL ');
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, '=SUM(J6:J' . ($row - 1) . ')');
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, '=SUM(K6:K' . ($row - 1) . ')');
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, '=SUM(M6:M' . ($row - 1) . ')');
		$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':G' . $row);
		$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 1), 'JUMLAH S/D BULAN LALU ');
		$objPHPExcel->getActiveSheet()->setCellValue('J' . ($row + 1), $data_prev->ketetapan);
		$objPHPExcel->getActiveSheet()->setCellValue('K' . ($row + 1), $data_prev->pembayaran);
		$objPHPExcel->getActiveSheet()->setCellValue('M' . ($row + 1), $data_prev->piutang);
		$objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':G' . ($row + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), 'JUMLAH S/D BULAN INI ');
		$objPHPExcel->getActiveSheet()->setCellValue('J' . ($row + 2), '=J' . ($row) . '+J' . ($row + 1) . ')');
		$objPHPExcel->getActiveSheet()->setCellValue('K' . ($row + 2), '=K' . ($row) . '+K' . ($row + 1) . ')');
		$objPHPExcel->getActiveSheet()->setCellValue('M' . ($row + 2), '=M' . ($row) . '+M' . ($row + 1) . ')');
		$objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':G' . ($row + 2));

		// Rename sheet
		$sheetName = "Penetapan dan Realisasi";
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:M5')->applyFromArray(
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
		// col A-D: center
		$objPHPExcel->getActiveSheet()->getStyle('A6:D' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				)
			)
		);
		// col F: center
		$objPHPExcel->getActiveSheet()->getStyle('F6:F' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				)
			)
		);
		// footer
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:M" . ($row + 2))->applyFromArray(
			array(
				'font' => array(
					'bold' => true
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);
		// border
		$objPHPExcel->getActiveSheet()->getStyle('A4:M' . ($row + 2))->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		// number format
		$objPHPExcel->getActiveSheet()->getStyle('J6:K' . ($row + 2))->getNumberFormat()->setFormatCode('#,##0');
		$objPHPExcel->getActiveSheet()->getStyle('M6:M' . ($row + 2))->getNumberFormat()->setFormatCode('#,##0');

		// bg header
		$objPHPExcel->getActiveSheet()->getStyle('A4:M5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A4:M5')->getFill()->getStartColor()->setRGB('E4E4E4');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('4');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('15');
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

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
		$objWriter->save('php://output');
		mysql_close($Conn_gw);
	}


	public function download_excel_status_bayar()
	{
		// var_dump($_REQUEST);
		// die;

		$tagihan = (isset($_REQUEST['m']) && $_REQUEST['m'] == 'mPatdaMonDaftarTagihan') ? true : false;
		echo '<pre>', print_r($_REQUEST, true), '</pre>';
		if ($_REQUEST['jenis'] == 29) {
			$this->download_excel_status_bayar_mineral();
			exit;
		}
		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = ($tagihan == true) ? "expired_date >= NOW()" : " 1=1 ";

		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
		$where .= ($_REQUEST['bank'] == "" || $_REQUEST['bank'] == 'undefined') ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";


		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";


		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
		}

		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
		}

		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		if (!empty($_REQUEST['jenis'])) {
			$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			if ($_REQUEST['jenis'] == 8) {

				$tipe_pajak_restoran = $this->jenis_tipe_pajak_restoran[$_REQUEST['jenis_lapor']];
				// var_dump($_REQUEST['jenis_lapor'], $tipe_pajak_restoran);die;
			} else {

				$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
			}
			if (empty($_REQUEST['jenis_lapor'])) {
				$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( a.simpatda_type='{$jenis_reg}' OR a.simpatda_type='{$jenis_nonreg}' )";
			} else {
				if ($_REQUEST['jenis'] == 8) {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $tipe_pajak_restoran;
				} else {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $jenis_nonreg;
				}
				// $where .= " AND a.simpatda_type='{$jenis}'";
				$where .= " AND permen.kdrek='{$_REQUEST['jenis_lapor']}'";
			}
		} elseif (!empty($_REQUEST['jenis_lapor'])) {
			$where .= ($_REQUEST['jenis_lapor'] == 1) ? " AND a.simpatda_type<=12 " : "AND a.simpatda_type>12 ";
		}

		// $where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		// $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";
		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND YEAR(saved_date)='{$_REQUEST['simpatda_tahun_pajak']}'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND MONTH(saved_date)='{$_REQUEST['simpatda_bulan_pajak']}'";

		//if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
		//	$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		//} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		//} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		//}

		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($this->_i == 1) ? " AND payment_flag = 1" : " AND (payment_flag != 1 OR payment_flag IS NULL)";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}


		if ($_REQUEST['jenis_lapor'] == '4.1.01.07.07') {

			$join = "join patda_restoran_doc doc on a.id_switching = doc.CPM_ID";
		}

		// var_dump($_REQUEST['jenis_lapor']);die;
		if ($_REQUEST['jenis_lapor'] == '4.1.01.07.07') {
			$query = "select permen.nmrek,jenis,op_nama,doc.pelaksana_kegiatan,b.jenis_sw , permen.nmrek as nmrek,sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, patda_kurangbayar, payment_paid_kurangbayar, a.masa_pajak_awal, a.masa_pajak_akhir
					FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					join patda_restoran_doc doc on a.id_switching = doc.CPM_ID
					left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek
					WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC ";
			//LIMIT {$offset}, {$total}
		} else {
			$query = "select permen.nmrek,jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat,b.jenis_sw , permen.nmrek, simpatda_tahun_pajak,simpatda_bulan_pajak,
			date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
			date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, patda_kurangbayar, payment_paid_kurangbayar, a.masa_pajak_awal, a.masa_pajak_akhir
			FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
			
			left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek
			WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC ";
		}
		// $query = "select jtp.nama_tipe_pajak,jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
		// 			date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
		// 			date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, patda_kurangbayar, payment_paid_kurangbayar, a.masa_pajak_awal, a.masa_pajak_akhir
		// 			FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
		// 			left join jenis_tipe_pajak jtp on jtp.simpatda_type = a.simpatda_type 
		// 			WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC LIMIT {$offset}, {$total}";
		// echo $query;
		// exit;
		$result = mysqli_query($Conn_gw, $query);
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");

		// Add some data
		// var_dump($_REQUEST['jenis_lapor']);die;

		if ($_REQUEST['jenis_lapor'] == '4.1.01.07.07') {
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'No.')
				->setCellValue('B1', 'Jenis Pajak')
				->setCellValue('C1', 'Nama Pajak')
				->setCellValue('D1', 'No. Pelaporan')
				->setCellValue('E1', 'NPWPD')
				->setCellValue('F1', 'Nama WP')
				->setCellValue('G1', 'Alamat WP')
				->setCellValue('H1', 'Tahun Pajak')
				->setCellValue('I1', 'Bulan Pajak')
				->setCellValue('J1', 'Tgl Jatuh Tempo')
				->setCellValue('K1', 'Tagihan (Rp)')
				->setCellValue('L1', 'Status')
				->setCellValue('M1', 'Tanggal Lapor')
				->setCellValue('N1', 'Tanggal Bayar')
				->setCellValue('O1', 'Kode Verifikasi')
				->setCellValue('P1', 'Kurang Bayar')
				->setCellValue('Q1', 'Tanggal Kurang Bayar')
				->setCellValue('R1', 'Pelaksana Kegiatan');
		} else {
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'No.')
				->setCellValue('B1', 'Jenis Pajak')
				->setCellValue('C1', 'Nama Pajak')
				->setCellValue('D1', 'No. Pelaporan')
				->setCellValue('E1', 'NPWPD')
				->setCellValue('F1', 'Nama WP')
				->setCellValue('G1', 'Alamat WP')
				->setCellValue('H1', 'Tahun Pajak')
				->setCellValue('I1', 'Bulan Pajak')
				->setCellValue('J1', 'Tgl Jatuh Tempo')
				->setCellValue('K1', 'Tagihan (Rp)')
				->setCellValue('L1', 'Status')
				->setCellValue('M1', 'Tanggal Lapor')
				->setCellValue('N1', 'Tanggal Bayar')
				->setCellValue('O1', 'Kode Verifikasi')
				->setCellValue('P1', 'Kurang Bayar')
				->setCellValue('Q1', 'Tanggal Kurang Bayar');
		}

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($result);
		$totalPayment = 0;
		while ($rowData = mysqli_fetch_assoc($result)) {
			if ($rowData['nama_tipe_pajak'] == "" || $rowData['nama_tipe_pajak'] == null) {

				$rowData['jenis'] = $rowData['jenis'];
			} else {
				$rowData['jenis'] = $rowData['nama_tipe_pajak'];
			}
			$tgl_jth_tempo = explode('-', $rowData['expired_date']);
			if (count($tgl_jth_tempo) == 3)
				$tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

			$totalPayment += $rowData['simpatda_dibayar'];
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['jenis'] . "(" . $rowData['nmrek'] . ")", PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['op_nama']));
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_nama']);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['wp_alamat']);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['simpatda_tahun_pajak']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['masa_pajak_awal'] . " s.d " . $rowData['masa_pajak_akhir']);
			$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $tgl_jth_tempo);
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['simpatda_dibayar']);
			$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : '');
			$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['saved_date']);
			$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['payment_paid']);
			$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['payment_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['patda_kurangbayar']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['payment_paid_kurangbayar']);
			// if($_REQUEST['jenis_lapor'] == '4.1.01.07.07'){
			$objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['pelaksana_kegiatan']);
			// }
			$row++;
		}

		$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $totalPayment);
		$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "");
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "");
		$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "");

		$objPHPExcel->getActiveSheet()->mergeCells("B" . ($row + 3) . ":D" . ($row + 3) . "");
		$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 3), "YANG MELAPORKAN");
		$objPHPExcel->getActiveSheet()->mergeCells("F" . ($row + 3) . ":H" . ($row + 3) . "");
		$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 3), "BENDAHARA PENERIMA");
		$objPHPExcel->getActiveSheet()->mergeCells("K" . ($row + 3) . ":M" . ($row + 3) . "");
		$objPHPExcel->getActiveSheet()->setCellValue('K' . ($row + 3), "KASI PAJAK DAERAH");

		$sumRows++;

		// Rename sheet
		$sheetName = "";
		if ($tagihan == true) {
			$sheetName = "Tagihan";
		} else {
			$sheetName = $this->_i == 1 ? 'Sudah Bayar' : 'Belum bayar';
		}
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:R{$row}")->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle('A1:R' . $row)->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('I2:N' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 3) . ':R' . ($row + 3) . '')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->getStartColor()->setRGB('E4E4E4');
		$objPHPExcel->getActiveSheet()->getStyle('A2:A' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('B2:G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('H2:J' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('K2:K' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getStyle('L2:L' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('O2:Q' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


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
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);

		ob_clean();

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function download_excel_status_bayarV2()
	{
		// var_dump($_REQUEST['jenis_lapor']);die;

		$tagihan = (isset($_REQUEST['m']) && $_REQUEST['m'] == 'mPatdaMonDaftarTagihan') ? true : false;
		echo '<pre>', print_r($_REQUEST, true), '</pre>';
		if ($_REQUEST['jenis'] == 29) {
			$this->download_excel_status_bayar_mineral();
			exit;
		}
		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = ($tagihan == true) ? "expired_date >= NOW()" : " 1=1 ";

		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
		$where .= ($_REQUEST['bank'] == "" || $_REQUEST['bank'] == 'undefined') ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";


		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		$MONTH_NOW_A = date("F");
		$YEAR_NOW_A = date("Y");
		$JNSJNS = mysqli_fetch_assoc($result1);
		$JNS_PAJAK_A = $_REQUEST['JNS_PAJAK'] == "" ? "SEMUA" : $JNSJNS['jenis_sw'];
		$TAHUN_PAJAK_A = $_REQUEST['CPM_TAHUN_PAJAK'] == "" ? $YEAR_NOW_A : $_REQUEST['CPM_TAHUN_PAJAK'];
		$MASA_PAJAK_A = $_REQUEST['CPM_MASA_PAJAK'] == "" ? "DESEMBER" : $_REQUEST['CPM_MASA_PAJAK'];


		// $queryTunggakan = "SELECT *
		//                     FROM simpatda_gw
		//                     WHERE simpatda_type AND kecamatan_wp ORDER BY kecamatan_wp ASC";

		// echo "<pre>";
		// print_r($queryTunggakan);exit;


		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
		}

		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
		}

		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		if (!empty($_REQUEST['jenis'])) {
			$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			if ($_REQUEST['jenis'] == 8) {

				$tipe_pajak_restoran = $this->jenis_tipe_pajak_restoran[$_REQUEST['jenis_lapor']];
				// var_dump($_REQUEST['jenis_lapor'], $tipe_pajak_restoran);die;
			} else {

				$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
			}
			if (empty($_REQUEST['jenis_lapor'])) {
				$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( a.simpatda_type='{$jenis_reg}' OR a.simpatda_type='{$jenis_nonreg}' )";
			} else {
				if ($_REQUEST['jenis'] == 8) {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $tipe_pajak_restoran;
				} else {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $jenis_nonreg;
				}
				// $where .= " AND a.simpatda_type='{$jenis}'";
				$where .= " AND permen.kdrek='{$_REQUEST['jenis_lapor']}'";
			}
		} elseif (!empty($_REQUEST['jenis_lapor'])) {
			$where .= ($_REQUEST['jenis_lapor'] == 1) ? " AND a.simpatda_type<=12 " : "AND a.simpatda_type>12 ";
		}

		// $where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		// $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";
		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND MONTH(saved_date)='{$_REQUEST['simpatda_bulan_pajak']}'";

		//if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
		//	$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		//} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		//} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		//}

		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($this->_i == 1) ? " AND (payment_flag = 1 OR payment_flag = 0)" : " AND (payment_flag != 1 OR payment_flag IS NULL)";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		if ($_REQUEST['jenis_lapor'] == '4.1.01.07.07') {

			$join = "join patda_restoran_doc doc on a.id_switching = doc.CPM_ID";
		}



		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");

		// mergeCells
		$objPHPExcel->getActiveSheet()
			->mergeCells('A1:V1')
			->mergeCells('A2:V2')
			->mergeCells('A3:V3')
			->mergeCells('A4:A5')
			->mergeCells('B4:B5')
			->mergeCells('C4:C5')
			->mergeCells('D4:D5')
			->mergeCells('E4:F4')
			->mergeCells('G4:J4')
			->mergeCells('K4:K5')
			->mergeCells('L4:O4')
			->mergeCells('P4:S4')
			->mergeCells('T4:T5')
			->mergeCells('U4:V4')
			->mergeCells('W4:W5');

		// if($_REQUEST['jenis_lapor'] == '4.1.01.07.07'){
		// $objPHPExcel->setActiveSheetIndex(0)
		// 	->setCellValue('A1', 'No.')
		// 	->setCellValue('B1', 'Jenis Pajak')
		// 	->setCellValue('C1', 'Nama Pajak')
		// 	->setCellValue('D1', 'No. Pelaporan')
		// 	->setCellValue('E1', 'NPWPD')
		// 	->setCellValue('F1', 'Nama WP')
		// 	->setCellValue('G1', 'Alamat WP')
		// 	->setCellValue('H1', 'Tahun Pajak')
		// 	->setCellValue('I1', 'Bulan Pajak')
		// 	->setCellValue('J1', 'Tgl Jatuh Tempo')
		// 	->setCellValue('K1', 'Tagihan (Rp)')
		// 	->setCellValue('L1', 'Status')
		// 	->setCellValue('M1', 'Tanggal Lapor')
		// 	->setCellValue('N1', 'Tanggal Bayar')
		// 	->setCellValue('O1', 'Kode Verifikasi')
		// 	->setCellValue('P1', 'Kurang Bayar')
		// 	->setCellValue('Q1', 'Tanggal Kurang Bayar')
		// 	->setCellValue('R1', 'Pelaksana Kegiatan');
		// }else{
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'PENETAPAN DAN REALISASI PAJAK ' . strtoupper($JNS_PAJAK_A))
			->setCellValue('A2', 'KABUPATEN LAMPUNG SELATAN TAHUN ' . $TAHUN_PAJAK_A)
			->setCellValue('A3', 'PERIODE ' . strtoupper($this->arr_bulan[$MASA_PAJAK_A]))
			->setCellValue('A4', 'NO')
			->setCellValue('B4', 'KECAMATAN')
			->setCellValue('C4', 'KELURAHAN')
			->setCellValue('D4', 'JENIS PAJAK')
			->setCellValue('E4', 'KETETAPAN')
			->setCellValue('E5', 'WP')
			->setCellValue('F5', 'RP')
			->setCellValue('G4', 'REALISASI BULAN LALU (RP)')
			->setCellValue('G5', 'WP')
			->setCellValue('H5', 'POKOK')
			->setCellValue('I5', 'DENDA')
			->setCellValue('J5', 'TOTAL')
			->setCellValue('K4', '%')
			->setCellValue('L4', 'REALISASI BULAN INI (RP)')
			->setCellValue('L5', 'WP')
			->setCellValue('M5', 'POKOK')
			->setCellValue('N5', 'DENDA')
			->setCellValue('O5', 'TOTAL')
			->setCellValue('P4', 'REALISASI s/d BULAN INI (RP)')
			->setCellValue('P5', 'WP')
			->setCellValue('Q5', 'POKOK')
			->setCellValue('R5', 'DENDA')
			->setCellValue('S5', 'TOTAL')
			->setCellValue('T4', '%')
			->setCellValue('U4', 'SISA KETETAPAN')
			->setCellValue('U5', 'WP')
			->setCellValue('V5', 'RP')
			->setCellValue('W4', '%');
		// }

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);
		$sumRows = mysqli_num_rows($result);

		$query = "SELECT 
			COUNT(NPWPD) AS ketetapanWP,
			SUM(simpatda_dibayar) AS ketetapanRP, 
			SUM(patda_denda) AS denda, 
			kec.CPM_KECAMATAN, 
			kel.CPM_KELURAHAN,
			permen.nmrek,
			jenis_sw,	
			COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_BULAN_LALU,

			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_BULAN_LALU,

			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_BULAN_LALU,

			COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_BULAN_INI,
			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_BULAN_INI,
			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_BULAN_INI,

			COUNT(IF(payment_flag='0', NPWPD, NULL)) AS SISA_WP,
			SUM(IF(payment_flag='0', simpatda_dibayar, NULL)) AS SISA_RP, 
			payment_flag, 
			DATE_FORMAT(payment_paid,'%d-%m-%Y') AS payment_paid,

			COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_SAMPAI_BULAN_INI,
			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_SAMPAI_BULAN_INI,
			SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_SAMPAI_BULAN_INI

        FROM SIMPATDA_GW a
        LEFT JOIN SIMPATDA_TYPE b ON a.simpatda_type = b.id
        LEFT JOIN patda_mst_kecamatan kec ON a.kecamatan_op = kec.CPM_KEC_ID
		LEFT JOIN patda_mst_kelurahan kel ON a.kelurahan_op = kel.CPM_KEL_ID
		LEFT JOIN patda_rek_permen13 permen on a.simpatda_rek=permen.kdrek
        WHERE {$where} 
		-- AND jenis_sw IS NOT NULL AND CPM_KECAMATAN IS NOT NULL AND CPM_KELURAHAN IS NOT NULL
        GROUP BY kec.CPM_KECAMATAN, kel.CPM_KELURAHAN,jenis_sw
        ORDER BY kec.CPM_KECAMATAN, kel.CPM_KELURAHAN ASC";

		// echo"<pre>";
		// print_r($query);die;
		$result = mysqli_query($Conn_gw, $query);

		$dataQuery = [];
		$jenisSwUnique = []; // Array untuk menyimpan jenis_sw unik
		$jenisSwUnique2 = []; // Array untuk menyimpan jenis_sw unik

		while ($rowData = mysqli_fetch_assoc($result)) {
			$CPMKecamatan = $rowData['CPM_KECAMATAN'];
			$CPM_KELURAHAN = $rowData['CPM_KELURAHAN'];
			$jenisSw = $rowData['jenis_sw'];

			// Jika jenis_sw belum ada dalam array jenisSwUnique untuk CPM_KECAMATAN ini
			if (!array_key_exists($CPMKecamatan, $CPM_KELURAHAN, $jenisSwUnique, $jenisSwUnique2)) {
				$jenisSwUnique[$CPMKecamatan] = [];
				$jenisSwUnique2[$CPM_KELURAHAN] = []; // Inisialisasi array jenis_sw untuk CPM_KECAMATAN ini
			}

			// Jika jenis_sw belum ada dalam array jenisSwUnique untuk CPM_KECAMATAN ini
			if (!in_array($jenisSw, $jenisSwUnique[$CPMKecamatan], $jenisSwUnique2[$CPM_KELURAHAN])) {
				$jenisSwUnique[$CPMKecamatan][] = $jenisSw; // Tambahkan jenis_sw ke array jenisSwUnique untuk CPM_KECAMATAN ini
				$jenisSwUnique2[$CPM_KELURAHAN][] = $jenisSw;
				$rowData['CPM_KECAMATAN'] = $CPMKecamatan; // Set CPM_KECAMATAN hanya sekali untuk jenis_sw ini
				$rowData['CPM_KELURAHAN'] = $CPM_KELURAHAN;
			} else {
				$rowData['CPM_KECAMATAN'] = ''; // Kosongkan CPM_KECAMATAN untuk jenis_sw yang sama
				$rowData['CPM_KELURAHAN'] = '';
			}

			$dataQuery[] = $rowData; // Tambahkan data ke dataQuery
		}

		$row = 6;
		$currentCPMKecamatan = null;
		$currentCPMKelurahan = null;

		$totalKetetapan = 0;
		$totalRealisasiBulanLalu = 0;
		$totalRealisasiBulanIni = 0;
		$totalRealisasisampaiBulanIni = 0;
		$totalSisa = 0;
		foreach ($dataQuery as $rowData) {
			$CPMKecamatan = $rowData['CPM_KECAMATAN'];
			$jenisSw = $rowData['jenis_sw'];
			$CPM_KELURAHAN = $rowData['CPM_KELURAHAN'];
			$ketetapanWP = $rowData['ketetapanWP'];
			$no = $row - 5;
			$savedDate = $rowData['payment_paid'];
			$payment_flag = $rowData['payment_flag'];
			$expired_date = $rowData['expired_date'];
			$realisasiKom =  $rowData['REALISASI_RP_BULAN_LALU'] + $rowData['REALISASI_RP_BULAN_INI'];

			$totalKetetapan += $rowData['ketetapanRP'];
			$totalRealisasiBulanLalu += $rowData['REALISASI_RP_BULAN_LALU'];
			$totalRealisasiBulanIni += $rowData['REALISASI_RP_BULAN_INI'];
			$totalRealisasisampaiBulanIni += $rowData['REALISASI_RP_SAMPAI_BULAN_INI'];
			$totalSisa += $rowData['SISA_RP'];

			// Jika CPM_KECAMATAN berubah, tampilkan CPM_KECAMATAN
			if ($CPMKecamatan !== $currentCPMKecamatan) {
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $CPMKecamatan);
				$currentCPMKecamatan = $CPMKecamatan;
			}

			if ($CPM_KELURAHAN !== $currentCPMKelurahan) {
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $CPM_KELURAHAN);
				$currentCPMKelurahan = $CPM_KELURAHAN;
			}

			if ($rowData['ketetapanRP'] == 0 || $rowData['REALISASI_RP_BULAN_LALU'] == 0 || $rowData['REALISASI_RP_SAMPAI_BULAN_INI'] == 0) {
				$persentaseLalu = 0; // Jika salah satu atau keduanya adalah 0, maka persentase adalah 0%.
				$persentaseIni = 0;
				$persentaseSisa = 0;
			} elseif ($rowData['ketetapanRP'] == $rowData['REALISASI_RP_BULAN_LALU'] || $rowData['ketetapanRP'] == $rowData['REALISASI_RP_SAMPAI_BULAN_INI']) {
				$persentaseLalu = 100; // Jika keduanya sama, maka persentase adalah 100%.
				$persentaseIni = 100;
				$persentaseSisa = 100;
			} else {
				$persentaseLalu = (($rowData['ketetapanRP'] - $rowData['REALISASI_RP_BULAN_LALU']) / $rowData['ketetapanRP']) * 100;
				$persentaseIni = (($rowData['ketetapanRP'] - $rowData['REALISASI_RP_SAMPAI_BULAN_INI']) / $rowData['ketetapanRP']) * 100;
				$persentaseSisa = 100 - $persentaseIni;
			}


			// Tampilkan jenis_sw dan data lainnya
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no));
			// $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $CPM_KELURAHAN);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $jenisSw . "(" . $rowData['nmrek'] . ")", PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $ketetapanWP);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['ketetapanRP']);
			// Bulan lalu
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['REALISASI_WP_BULAN_LALU']);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['REALISASI_RP_BULAN_LALU'] - $rowData['REALISASI_DENDA_BULAN_LALU']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['REALISASI_DENDA_BULAN_LALU']);
			$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['REALISASI_RP_BULAN_LALU']);
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, number_format($persentaseLalu, 2) . '%');

			// Bulan Ini
			$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['REALISASI_WP_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['REALISASI_RP_BULAN_INI'] - $rowData['REALISASI_DENDA_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['REALISASI_DENDA_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['REALISASI_RP_BULAN_INI']);
			// S/d Bulan saat ini 
			$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['REALISASI_WP_SAMPAI_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['REALISASI_RP_SAMPAI_BULAN_INI'] - $rowData['REALISASI_DENDA_SAMPAI_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['REALISASI_DENDA_SAMPAI_BULAN_INI']);
			$objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['REALISASI_RP_SAMPAI_BULAN_INI']);

			$objPHPExcel->getActiveSheet()->setCellValue('T' . $row, number_format($persentaseIni, 2) . '%');


			$objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowData['SISA_WP']);
			$objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $rowData['SISA_RP']);

			$objPHPExcel->getActiveSheet()->setCellValue('W' . $row, number_format($persentaseSisa, 2) . '%');
			$row++;
		}

		// echo"<pre>";
		// print_r($query);exit;


		$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:D{$row}");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $totalKetetapan);
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $totalRealisasiBulanLalu);
		$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $totalRealisasiBulanIni);
		$objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $totalRealisasisampaiBulanIni);
		$objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $totalSisa);

		// $objPHPExcel->getActiveSheet()->mergeCells("B" . ($row + 3) . ":D" . ($row + 3) . "");
		// $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 3), "YANG MELAPORKAN");
		// $objPHPExcel->getActiveSheet()->mergeCells("F" . ($row + 3) . ":H" . ($row + 3) . "");
		// $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 3), "BENDAHARA PENERIMA");
		// $objPHPExcel->getActiveSheet()->mergeCells("K" . ($row + 3) . ":M" . ($row + 3) . "");
		// $objPHPExcel->ge tActiveSheet()->setCellValue('K' . ($row + 3), "KASI PAJAK DAERAH");
		$sumRows++;

		// Rename sheet
		$sheetName = "";
		if ($tagihan == true) {
			$sheetName = "Tagihan";
		} else {
			$sheetName = $this->_i == 1 ? 'Sudah Bayar' : 'Belum bayar';
		}
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:W5')->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:W{$row}")->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle('A4:W' . $row)->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('W2:W' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 3) . ':W' . ($row + 3) . '')->applyFromArray(
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


		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:w{$row}");
		$objPHPExcel->getActiveSheet()->getStyle('A4:W5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A4:W5')->getFill()->getStartColor()->setRGB('E4E4E4');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('4');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('25');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('25');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('25');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('10');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('10');

		ob_clean();

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	// public function download_excel_status_bayarV2()
	// {
	// 	// var_dump($_REQUEST['jenis_lapor']);die;

	// 	$tagihan = (isset($_REQUEST['m']) && $_REQUEST['m'] == 'mPatdaMonDaftarTagihan') ? true : false;
	// 	echo '<pre>', print_r($_REQUEST, true), '</pre>';
	// 	if ($_REQUEST['jenis'] == 29) {
	// 		$this->download_excel_status_bayar_mineral();
	// 		exit;
	// 	}
	// 	$arr_config = $this->get_config_value($this->_a);

	// 	$dbName = $arr_config['PATDA_DBNAME'];
	// 	$dbHost = $arr_config['PATDA_HOSTPORT'];
	// 	$dbPwd = $arr_config['PATDA_PASSWORD'];
	// 	$dbUser = $arr_config['PATDA_USERNAME'];

	// 	$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
	// 	//mysqli_select_db($dbName);

	// 	$where = ($tagihan == true) ? "expired_date >= NOW()" : " 1=1 ";

	// 	$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
	// 	$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
	// 	$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
	// 	$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
	// 	$where .= ($_REQUEST['bank'] == "" || $_REQUEST['bank'] == 'undefined') ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";


	// 	$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
	// 	$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

	// 	$MONTH_NOW_A = date("F");
	// 	$YEAR_NOW_A = date("Y");
	// 	$JNSJNS = mysqli_fetch_assoc($result1);
	// 	$JNS_PAJAK_A = $_REQUEST['JNS_PAJAK'] == "" ? "SEMUA" : $JNSJNS['jenis_sw'];
	// 	$TAHUN_PAJAK_A = $_REQUEST['CPM_TAHUN_PAJAK'] == "" ? $YEAR_NOW_A : $_REQUEST['CPM_TAHUN_PAJAK'];
	// 	$MASA_PAJAK_A = $_REQUEST['CPM_MASA_PAJAK'] == "" ? "DESEMBER" : $_REQUEST['CPM_MASA_PAJAK'];

	// 	// $queryTunggakan = "SELECT *
	//     //                     FROM simpatda_gw
	//     //                     WHERE simpatda_type AND kecamatan_wp ORDER BY kecamatan_wp ASC";

	// 	// echo "<pre>";
	//     // print_r($queryTunggakan);exit;


	// 	if ($this->_i == 1) {
	// 		$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
	// 	}

	// 	if ($this->_i == 1) {
	// 		$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
	// 	}

	// 	$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

	// 	if (!empty($_REQUEST['jenis'])) {
	// 		$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
	// 		if ($_REQUEST['jenis'] == 8) {

	// 			$tipe_pajak_restoran = $this->jenis_tipe_pajak_restoran[$_REQUEST['jenis_lapor']];
	// 			// var_dump($_REQUEST['jenis_lapor'], $tipe_pajak_restoran);die;
	// 		} else {

	// 			$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
	// 		}
	// 		if (empty($_REQUEST['jenis_lapor'])) {
	// 			$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( a.simpatda_type='{$jenis_reg}' OR a.simpatda_type='{$jenis_nonreg}' )";
	// 		} else {
	// 			if ($_REQUEST['jenis'] == 8) {
	// 				$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $tipe_pajak_restoran;
	// 			} else {
	// 				$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $jenis_nonreg;
	// 			}
	// 			// $where .= " AND a.simpatda_type='{$jenis}'";
	// 			$where .= " AND permen.kdrek='{$_REQUEST['jenis_lapor']}'";
	// 		}
	// 	} elseif (!empty($_REQUEST['jenis_lapor'])) {
	// 		$where .= ($_REQUEST['jenis_lapor'] == 1) ? " AND a.simpatda_type<=12 " : "AND a.simpatda_type>12 ";
	// 	}

	// 	// $where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
	// 	//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
	// 	// $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";
	// 	$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND YEAR(saved_date)='{$_REQUEST['simpatda_tahun_pajak']}'";
	// 	$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND MONTH(saved_date)='{$_REQUEST['simpatda_bulan_pajak']}'";

	// 	//if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
	// 		//	$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
	// 		//} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
	// 			//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
	// 			//} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
	// 	//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
	// 	//}

	// 	if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
	// 		$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
	// 	} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
	// 		$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
	// 	} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
	// 		$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
	// 	}

	// 	$where .= ($this->_i == 1) ? " AND (payment_flag = 1 OR payment_flag = 0)" : " AND (payment_flag != 1 OR payment_flag IS NULL)";

	// 	if ($_REQUEST['simpatda_dibayar'] != 0) {
	// 		$arr_dibayar = array(
	// 			1 => " (simpatda_dibayar < 5000000) ",
	// 			2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
	// 			3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
	// 			4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
	// 			5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
	// 			6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
	// 			7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
	// 			8 => "(simpatda_dibayar >= 100000000)",
	// 			9 => "(simpatda_dibayar > 100000000)"
	// 		);
	// 		$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
	// 	}

	// 	$p = $_REQUEST['p'];
	// 	if ($p == 'all') {
	// 		$total = $_REQUEST['total'];
	// 		$offset = 0;
	// 	} else {
	// 		$total = 20000;
	// 		$offset = ($p - 1) * $total;
	// 	}

	// 	if($_REQUEST['jenis_lapor'] == '4.1.01.07.07'){

	// 		$join = "join patda_restoran_doc doc on a.id_switching = doc.CPM_ID";
	// 	}


	// 	// Create new PHPExcel object
	// 	$objPHPExcel = new PHPExcel();

	// 	// Set properties
	// 	$objPHPExcel->getProperties()->setCreator("vpost")
	// 		->setLastModifiedBy("vpost")
	// 		->setTitle("-")
	// 		->setSubject("-
	// 		->setDescription("pbb")
	// 		->setKeywords("-");

	// 		// mergeCells
	// 	$objPHPExcel->getActiveSheet()
	// 		->mergeCells('A1:V1')
	// 		->mergeCells('A2:V2')
	// 		->mergeCells('A3:V3')
	// 		->mergeCells('A4:A5')
	// 		->mergeCells('B4:B5')
	// 		->mergeCells('C4:C5')
	// 		->mergeCells('D4:E4')
	// 		->mergeCells('F4:I4')
	// 		->mergeCells('J4:J5')
	// 		->mergeCells('K4:N4')
	// 		->mergeCells('O4:R4')
	// 		->mergeCells('S4:S5')
	// 		->mergeCells('T4:U4')
	// 		->mergeCells('V4:V5');

	// 	// Add some data
	// 	// var_dump($_REQUEST['jenis_lapor']);die;

	// 	if($_REQUEST['jenis_lapor'] == '4.1.01.07.07'){
	// 	$objPHPExcel->setActiveSheetIndex(0)
	// 		->setCellValue('A1', 'No.')
	// 		->setCellValue('B1', 'Jenis Pajak')
	// 		->setCellValue('C1', 'Nama Pajak')
	// 		->setCellValue('D1', 'No. Pelaporan')
	// 		->setCellValue('E1', 'NPWPD')
	// 		->setCellValue('F1', 'Nama WP')
	// 		->setCellValue('G1', 'Alamat WP')
	// 		->setCellValue('H1', 'Tahun Pajak')
	// 		->setCellValue('I1', 'Bulan Pajak')
	// 		->setCellValue('J1', 'Tgl Jatuh Tempo')
	// 		->setCellValue('K1', 'Tagihan (Rp)')
	// 		->setCellValue('L1', 'Status')
	// 		->setCellValue('M1', 'Tanggal Lapor')
	// 		->setCellValue('N1', 'Tanggal Bayar')
	// 		->setCellValue('O1', 'Kode Verifikasi')
	// 		->setCellValue('P1', 'Kurang Bayar')
	// 		->setCellValue('Q1', 'Tanggal Kurang Bayar')
	// 		->setCellValue('R1', 'Pelaksana Kegiatan');
	// 	}else{
	// 		$objPHPExcel->setActiveSheetIndex(0)
	// 		->setCellValue('A1', 'PENETAPAN DAN REALISASI PAJAK ' . strtoupper($JNS_PAJAK_A))
	// 		->setCellValue('A2', 'KABUPATEN LAMPUNG SELATAN TAHUN ' . $TAHUN_PAJAK_A)
	// 		->setCellValue('A3', 'PERIODE ' . strtoupper($this->arr_bulan[$MASA_PAJAK_A]))
	// 		->setCellValue('A4', 'NO')
	// 		->setCellValue('B4', 'KECAMATAN')
	// 		->setCellValue('C4', 'JENIS PAJAK')
	// 		->setCellValue('D4', 'KETETAPAN')
	// 		->setCellValue('D5', 'WP')
	// 		->setCellValue('E5', 'RP')
	// 		->setCellValue('F4', 'REALISASI BULAN LALU (RP)')
	// 		->setCellValue('F5', 'WP')
	// 		->setCellValue('G5', 'POKOK')
	// 		->setCellValue('H5', 'DENDA')
	// 		->setCellValue('I5', 'TOTAL')
	// 		->setCellValue('J4', '%')
	// 		->setCellValue('K4', 'REALISASI BULAN INI (RP)')
	// 		->setCellValue('K5', 'WP')
	// 		->setCellValue('L5', 'POKOK')
	// 		->setCellValue('M5', 'DENDA')
	// 		->setCellValue('N5', 'TOTAL')
	// 		->setCellValue('O4', 'REALISASI s/d BULAN INI (RP)')
	// 		->setCellValue('O5', 'WP')
	// 		->setCellValue('P5', 'POKOK')
	// 		->setCellValue('Q5', 'DENDA')
	// 		->setCellValue('R5', 'TOTAL')
	// 		->setCellValue('S4', '%')
	// 		->setCellValue('T4', 'SISA KETETAPAN')
	// 		->setCellValue('T5', 'WP')
	// 		->setCellValue('U5', 'RP')
	// 		->setCellValue('V4', '%');
	// 	}

	// 	// Miscellaneous glyphs, UTF-8
	// 	$objPHPExcel->setActiveSheetIndex(0);

	// 	$sumRows = mysqli_num_rows($result);

	// 	$query = "SELECT 
	// 		COUNT(NPWPD) AS ketetapanWP, 
	// 		SUM(simpatda_dibayar) AS ketetapanRP, 
	// 		SUM(patda_denda) AS denda, 
	// 		kec.CPM_KECAMATAN, 
	// 		jenis_sw, 
	// 		COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_BULAN_LALU,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_BULAN_LALU,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_BULAN_LALU,

	// 		COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_BULAN_INI,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_BULAN_INI,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_BULAN_INI,

	// 		COUNT(IF(payment_flag='0', NPWPD, NULL)) AS SISA_WP,
	// 		SUM(IF(payment_flag='0', simpatda_dibayar, NULL)) AS SISA_RP, 
	// 		payment_flag, 
	// 		DATE_FORMAT(payment_paid,'%d-%m-%Y') AS payment_paid,

	// 		COUNT(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), NPWPD, NULL)) AS REALISASI_WP_SAMPAI_BULAN_INI,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), simpatda_dibayar, NULL)) AS REALISASI_RP_SAMPAI_BULAN_INI,
	// 		SUM(IF(payment_flag='1' AND DATE_FORMAT(payment_paid, '%Y-%m') <= DATE_FORMAT(CURRENT_DATE, '%Y-%m'), patda_denda, NULL)) AS REALISASI_DENDA_SAMPAI_BULAN_INI


	//     FROM SIMPATDA_GW a
	//     LEFT JOIN SIMPATDA_TYPE b ON a.simpatda_type = b.id
	//     LEFT JOIN patda_mst_kecamatan kec ON a.kecamatan_op = kec.CPM_KEC_ID
	//     WHERE {$where} AND jenis_sw IS NOT NULL AND CPM_KECAMATAN IS NOT NULL
	//     GROUP BY kec.CPM_KECAMATAN, jenis_sw
	//     ORDER BY kec.CPM_KECAMATAN ASC";

	// 	$result = mysqli_query($Conn_gw, $query);

	// 	$dataQuery = [];
	// 	$jenisSwUnique = []; // Array untuk menyimpan jenis_sw unik

	// 	while ($rowData = mysqli_fetch_assoc($result)) {
	// 		$CPMKecamatan = $rowData['CPM_KECAMATAN'];
	// 		$jenisSw = $rowData['jenis_sw'];

	// 		// Jika jenis_sw belum ada dalam array jenisSwUnique untuk CPM_KECAMATAN ini
	// 		if (!array_key_exists($CPMKecamatan, $jenisSwUnique)) {
	// 			$jenisSwUnique[$CPMKecamatan] = []; // Inisialisasi array jenis_sw untuk CPM_KECAMATAN ini
	// 		}

	// 		// Jika jenis_sw belum ada dalam array jenisSwUnique untuk CPM_KECAMATAN ini
	// 		if (!in_array($jenisSw, $jenisSwUnique[$CPMKecamatan])) {
	// 			$jenisSwUnique[$CPMKecamatan][] = $jenisSw; // Tambahkan jenis_sw ke array jenisSwUnique untuk CPM_KECAMATAN ini
	// 			$rowData['CPM_KECAMATAN'] = $CPMKecamatan; // Set CPM_KECAMATAN hanya sekali untuk jenis_sw ini
	// 		} else {
	// 			$rowData['CPM_KECAMATAN'] = ''; // Kosongkan CPM_KECAMATAN untuk jenis_sw yang sama
	// 		}

	// 		$dataQuery[] = $rowData; // Tambahkan data ke dataQuery
	// 	}

	// 	$row = 6;
	// 	$currentCPMKecamatan = null;
	// 	foreach ($dataQuery as $rowData) {
	// 		$CPMKecamatan = $rowData['CPM_KECAMATAN'];
	// 		$jenisSw = $rowData['jenis_sw'];
	// 		$ketetapanWP = $rowData['ketetapanWP'];
	// 		$no = $row - 5;
	// 		$savedDate = $rowData['payment_paid'];
	// 		$payment_flag = $rowData['payment_flag'];
	// 		$expired_date = $rowData['expired_date'];
	// 		$realisasiKom =  $rowData['REALISASI_RP_BULAN_LALU'] + $rowData['REALISASI_RP_BULAN_INI'];


	// 		// Jika CPM_KECAMATAN berubah, tampilkan CPM_KECAMATAN
	// 		if ($CPMKecamatan !== $currentCPMKecamatan) {
	// 			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $CPMKecamatan);
	// 			$currentCPMKecamatan = $CPMKecamatan;
	// 		}

	// 		if ($rowData['ketetapanRP'] == 0 || $rowData['REALISASI_RP_BULAN_LALU'] == 0 || $rowData['REALISASI_RP_SAMPAI_BULAN_INI'] == 0) {
	// 			$persentaseLalu = 0; // Jika salah satu atau keduanya adalah 0, maka persentase adalah 0%.
	// 			$persentaseIni = 0; 
	// 			$persentaseSisa = 0;
	// 		} elseif ($rowData['ketetapanRP'] == $rowData['REALISASI_RP_BULAN_LALU'] || $rowData['ketetapanRP'] == $rowData['REALISASI_RP_SAMPAI_BULAN_INI']) {
	// 			$persentaseLalu = 100; // Jika keduanya sama, maka persentase adalah 100%.
	// 			$persentaseIni = 100;
	// 			$persentaseSisa = 100;
	// 		} else {
	// 			$persentaseLalu = (($rowData['ketetapanRP'] - $rowData['REALISASI_RP_BULAN_LALU']) / $rowData['ketetapanRP']) * 100;
	// 			$persentaseIni = (($rowData['ketetapanRP'] - $rowData['REALISASI_RP_SAMPAI_BULAN_INI']) / $rowData['ketetapanRP']) * 100;
	// 			$persentaseSisa = 100 - $persentaseIni;
	// 		}


	// 		// Tampilkan jenis_sw dan data lainnya
	// 		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no));
	// 		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $jenisSw);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $ketetapanWP);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['ketetapanRP']);
	// 		// Bulan lalu
	// 		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['REALISASI_WP_BULAN_LALU']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['REALISASI_RP_BULAN_LALU'] - $rowData['REALISASI_DENDA_BULAN_LALU']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['REALISASI_DENDA_BULAN_LALU']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['REALISASI_RP_BULAN_LALU']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, number_format($persentaseLalu, 2) . '%');

	// 		// Bulan Ini
	// 		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['REALISASI_WP_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['REALISASI_RP_BULAN_INI'] - $rowData['REALISASI_DENDA_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['REALISASI_DENDA_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['REALISASI_RP_BULAN_INI']);
	// 		// S/d Bulan saat ini 
	// 		$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['REALISASI_WP_SAMPAI_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['REALISASI_RP_SAMPAI_BULAN_INI'] - $rowData['REALISASI_DENDA_SAMPAI_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['REALISASI_DENDA_SAMPAI_BULAN_INI']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['REALISASI_RP_SAMPAI_BULAN_INI']);

	// 		$objPHPExcel->getActiveSheet()->setCellValue('S' . $row, number_format($persentaseIni, 2) . '%');


	// 		$objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['SISA_WP']);
	// 		$objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowData['SISA_RP']);

	// 		$objPHPExcel->getActiveSheet()->setCellValue('V' . $row, number_format($persentaseSisa, 2) . '%');
	// 		$row++;
	// 	}

	// 	// echo"<pre>";
	// 	// print_r($query);exit;




	// 	// $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $totalPayment);
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "");

	// 	// $objPHPExcel->getActiveSheet()->mergeCells("B" . ($row + 3) . ":D" . ($row + 3) . "");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 3), "YANG MELAPORKAN");
	// 	// $objPHPExcel->getActiveSheet()->mergeCells("F" . ($row + 3) . ":H" . ($row + 3) . "");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 3), "BENDAHARA PENERIMA");
	// 	// $objPHPExcel->getActiveSheet()->mergeCells("K" . ($row + 3) . ":M" . ($row + 3) . "");
	// 	// $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row + 3), "KASI PAJAK DAERAH");

	// 	$sumRows++;

	// 	// Rename sheet
	// 	$sheetName = "";
	// 	if ($tagihan == true) {
	// 		$sheetName = "Tagihan";
	// 	} else {
	// 		$sheetName = $this->_i == 1 ? 'Sudah Bayar' : 'Belum bayar';
	// 	}
	// 	$objPHPExcel->getActiveSheet()->setTitle($sheetName);

	// 	//----set style cell
	// 	//style header
	// 	$objPHPExcel->getActiveSheet()->getStyle('A1:V5')->applyFromArray(
	// 		array(
	// 			'font' => array(
	// 				'bold' => true
	// 			),
	// 			'alignment' => array(
	// 				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	// 				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	// 			)
	// 		)
	// 	);
	// 	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:V{$row}")->applyFromArray(
	// 		array(
	// 			'font' => array(
	// 				'bold' => true
	// 			),
	// 			'alignment' => array(
	// 				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	// 				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	// 			)
	// 		)
	// 	);
	// 	$objPHPExcel->getActiveSheet()->getStyle('A4:V' . ($row - 1))->applyFromArray(
	// 		array(
	// 			'borders' => array(
	// 				'allborders' => array(
	// 					'style' => PHPExcel_Style_Border::BORDER_THIN
	// 				)
	// 			)
	// 		)
	// 	);
	// 	$objPHPExcel->getActiveSheet()->getStyle('V2:V' . $row)->applyFromArray(
	// 		array(
	// 			'alignment' => array(
	// 				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	// 				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	// 			)
	// 		)
	// 	);



	// 	$objPHPExcel->getActiveSheet()->getStyle('A4:V5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	// 	$objPHPExcel->getActiveSheet()->getStyle('A4:V5')->getFill()->getStartColor()->setRGB('E4E4E4');

	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('4');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('25');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('25');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('10');
	// 	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('10');

	// 	ob_clean();

	// 	$nmfile = $_REQUEST['nmfile'];
	// 	header('Content-Type: application/vnd.ms-excel');
	// 	header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
	// 	header('Cache-Control: max-age=0');

	// 	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	// 	$objWriter->save('php://output');
	// }


	public function download_excel_tunggakan_tagihan()
	{
		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);
		//=======

		$list_kecamatan = $this->get_list_kecamatan();
		$list_kelurahan = $this->get_list_kelurahan('', 'LIST');

		$where = ($this->_i == 4) ? " expired_date >= NOW() " : " expired_date < NOW() ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND op_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND op_nama like '%{$_REQUEST['wp_nama']}%'";
		$where .= $_REQUEST['kecamatan'] == "" ? "" : " AND (kecamatan_op like '%{$_REQUEST['kecamatan']}%' or kecamatan_wp like '%{$_REQUEST['kecamatan']}%')";
		$where .= $_REQUEST['kelurahan'] == "" ? "" : " AND (kelurahan_op like '%{$_REQUEST['kelurahan']}%' or kelurahan_wp like '%{$_REQUEST['kelurahan']}%')";

		if (empty($_REQUEST['jenis'])) {

			if (isset($_REQUEST['simpatda_jenis'])) {
				if ($_REQUEST['simpatda_jenis'] == 1) {
					$where .= $_REQUEST['simpatda_jenis'] == "" ? "" : " AND b.id_sw in (1,7)";
				} else if ($_REQUEST['simpatda_jenis'] == 2) {
					$where .= $_REQUEST['simpatda_jenis'] == "" ? "" : " AND b.id_sw in (2,3,4,5,6,8,9)";
				}
			}
		} else {
			$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
			$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( simpatda_type='{$jenis_reg}' OR simpatda_type='{$jenis_nonreg}' )";
		}

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";


		if ($this->_i == 4) {
			if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
			} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
			} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
			}
		} else {
			if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
			} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
			} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
			}
		}


		$where .= " AND (payment_flag != 1 OR payment_flag IS NULL)";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		//=======

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = 20000;
			$offset = $p * $total;
		} else {
			$total = $_REQUEST['total'];
			$offset = 1;
		}

		$query = "select jenis,op_nama, sptpd, npwpd, op_nama, op_alamat, kecamatan_op, kelurahan_op, wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, patda_total_bayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %H:%i:%s') as payment_paid 
					FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC, payment_paid ASC LIMIT {$offset}, {$total}";


		$result = mysqli_query($Conn_gw, $query);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");

		// Add some data
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'No.')
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nama Pajak')
			->setCellValue('D1', 'No. Pelaporan')
			->setCellValue('E1', 'NPWPD')
			->setCellValue('F1', 'Nama Usaha')
			->setCellValue('G1', 'Alamat OP')
			->setCellValue('H1', 'Kecamatan OP')
			->setCellValue('I1', 'Kelurahan OP')
			->setCellValue('J1', 'Tahun Pajak')
			->setCellValue('K1', 'Bulan Pajak')
			->setCellValue('L1', 'Tgl Jatuh Tempo')
			->setCellValue('M1', 'Ketetapan (Rp)')
			->setCellValue('N1', 'Pembayaran (Rp)')
			->setCellValue('O1', 'Status')
			->setCellValue('P1', 'Tanggal Lapor')
			->setCellValue('Q1', 'Tanggal Kurang Bayar');

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($result);
		$totalPayment = 0;
		while ($rowData = mysqli_fetch_assoc($result)) {
			$tgl_jth_tempo = explode('-', $rowData['expired_date']);
			if (count($tgl_jth_tempo) == 3)
				$tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

			$rowData['kecamatan_op'] = isset($list_kecamatan[$rowData['kecamatan_op']]) ? $list_kecamatan[$rowData['kecamatan_op']]->CPM_KECAMATAN : $rowData['kecamatan_op'];
			$rowData['kelurahan_op'] = isset($list_kelurahan[$rowData['kelurahan_op']]) ? $list_kelurahan[$rowData['kelurahan_op']]->CPM_KELURAHAN : $rowData['kelurahan_op'];

			$totalPayment += $rowData['simpatda_dibayar'];
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['jenis']));
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['op_nama']));
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['op_nama']);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['op_alamat']);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['kecamatan_op']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['kelurahan_op']);
			$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['simpatda_tahun_pajak']);
			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, isset($this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']] : $rowData['simpatda_bulan_pajak']);
			$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $tgl_jth_tempo);
			$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['simpatda_dibayar']);
			$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['patda_total_bayar']);
			$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : 'Belum Lunas');
			$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['saved_date']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, '');
			$row++;
		}

		$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:L{$row}");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "=SUM(M2:M" . ($row - 1) . ")");
		$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "=SUM(N2:N" . ($row - 1) . ")");
		$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, "");
		$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, "");
		$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "");
		$sumRows++;

		// Rename sheet
		$sheetName = "";
		$sheetName = $this->_i == 4 ? 'Tagihan' : 'Tunggakan';
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:Q{$row}")->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q' . $row)->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('I2:Q' . $row)->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->getStartColor()->setRGB('E4E4E4');
		$objPHPExcel->getActiveSheet()->getStyle('A2:A' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('B2:I' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('J2:L' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('M2:N' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getStyle('O2:O' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('P2:P' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('Q2:Q' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);

		ob_clean();

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function download_excel_belum_lapor()
	{

		$PAJAK = strtoupper($this->arr_pajak_table[$_REQUEST['CPM_JENIS_PAJAK']]);

		$wilayah = $_REQUEST['kecamatan'] == "" ? "" : " AND (CPM_KECAMATAN_OP like '%" . $_REQUEST['kecamatan'] . "%' or CPM_KECAMATAN_WP like '%" . $_REQUEST['kecamatan'] . "%')";
		$wilayah .= $_REQUEST['kelurahan'] == "" ? "" : " AND (CPM_KELURAHAN_OP like '%" . $_REQUEST['kelurahan'] . "%' or CPM_KELURAHAN_WP like '%" . $_REQUEST['kelurahan'] . "%')";

		$p = $_REQUEST['p'];

		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		$where = "CPM_ID NOT IN(
					SELECT prf.CPM_ID
					FROM  PATDA_{$PAJAK}_DOC pjk
					INNER JOIN PATDA_{$PAJAK}_PROFIL prf ON prf.CPM_ID = pjk.CPM_ID_PROFIL  
					WHERE  CPM_TAHUN_PAJAK='{$_REQUEST['CPM_TAHUN_PAJAK']}' AND CPM_MASA_PAJAK='{$_REQUEST['CPM_MASA_PAJAK']}' AND prf.CPM_APPROVE ='1' AND prf.CPM_AKTIF ='1' {$wilayah}
				) AND CPM_APPROVE ='1' AND CPM_AKTIF ='1' AND CPM_NPWPD like '%{$_REQUEST['CPM_NPWPD']}%'
				  AND (CPM_NAMA_WP like '%{$_REQUEST['CPM_NAMA_WP']}%' OR CPM_NAMA_OP like '%{$_REQUEST['CPM_NAMA_WP']}%' {$wilayah})";

		#query select list data        
		$query = "SELECT '{$_REQUEST['CPM_TAHUN_PAJAK']}' as CPM_TAHUN_PAJAK,'{$_REQUEST['CPM_MASA_PAJAK']}' as CPM_MASA_PAJAK, CPM_NPWPD, CPM_NAMA_WP, CPM_NAMA_OP FROM PATDA_{$PAJAK}_PROFIL
						WHERE {$where} ORDER BY 1 LIMIT {$offset}, {$total}";
		$result = mysqli_query($this->Conn, $query);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");


		// Add some data
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'No.')
			->setCellValue('B1', 'NPWPD')
			->setCellValue('C1', 'Nama Wajib Pajak')
			->setCellValue('D1', 'Nama Objek Pajak');

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($result);
		$totalPayment = 0;
		while ($rowData = mysqli_fetch_assoc($result)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['CPM_NAMA_WP']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_NAMA_OP']);
			$row++;
		}
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Belum Lapor');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:D{$row}")->applyFromArray(
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

		ob_clean();
		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function filtering_tagihan($id)
	{
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		foreach ($this->arr_pajak_gw as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$thn = date("Y");
		$opt_tahun = "<option value=\"\">Semua</option>";
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$bln = date("m");
		$opt_bulan = "<option value=\"\">Semua</option>";
		for ($b = 1; $b <= 12; $b++) {
			$opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
		}

		$kec = $this->get_list_kecamatan();
		$opt_kecamatan = "<option value=\"\">Semua</option>";
		foreach ($kec as $k => $v) {
			$opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
		}
		$opt_kelurahan = "<option value=\"\">Semua</option>";

		$reks = $this->getDataRekening();
		$opt_rekening = '<option value="">Semua</option>';
		foreach ($reks as $header => $rek) {
			$aRek = array_values($rek);
			if (count($aRek) > 1) {
				$opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
				foreach ($rek as $k => $v) {
					$opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
				}
			} else {
				// $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
				$opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
			}
		}

		$html = "<div class=\"filtering\">
		<style> .monitoring td{background:transparent}.monitoring input[type=\"text\"],.monitoring select{height:23px}</style>
					<form>
						<table width=\"1300\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
								<tr>
									<td width=\"105\">Tahun Pajak </td>
									<td width=\"3\">:</td>
									<td width=\"115\" colspan=\"3\"><select name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\">{$opt_tahun}</select> &nbsp; &nbsp; &nbsp;
									Bulan Pajak : <select name=\"simpatda_bulan_pajak-{$id}\" id=\"simpatda_bulan_pajak-{$id}\">{$opt_bulan}</select></td>
									<td width=\"150\">No. Pelaporan </td>
									<td width=\"3\">:</td>
									<td width=\"110\"><input type=\"text\" name=\"sptpd-{$id}\" id=\"sptpd-{$id}\" /></td>                                    
									<td width=\"90\"> Jenis Pajak </td>
									<td width=\"210\">: <select id=\"jenis-{$id}\" name=\"jenis-{$id}\" style=\"width:90%;\">{$opt_jenis_pajak}</select></td>    
									<td>Alamat : <input type=\"text\" name=\"wp_alamat\" id=\"wp_alamat-{$id}\"/></td>
								</tr>                                
								<tr>
									<td>Tgl&nbsp;Jatuh Tempo</td>
									<td>:</td>
									<td><input type=\"text\" name=\"expired_date1\" id=\"expired_date1-{$id}\" class=\"datepicker\" size=\"10\" /></td>
									<td width=\"22\">s/d </td>
									<td><input type=\"text\" name=\"expired_date2\" id=\"expired_date2-{$id}\" class=\"datepicker\" size=\"10\" /></td>
									<td width=\"150\">Nama WP / Tempat Usaha</td>
									<td>:</td>
									<td width=\"110\"><input type=\"text\" name=\"wp_nama\" id=\"wp_nama-{$id}\" /></td>                                    
									<td>Nilai Tagihan </td>
									<td> : <select id=\"simpatda_dibayar-{$id}\" name=\"simpatda_dibayar-{$id}\">
											<option value=\"0\" >--semua--</option>
											<option value=\"1\" >0 s/d <5jt</option>
											<option value=\"2\" >5jt s/d <10jt</option>
											<option value=\"3\" >10jt s/d <20jt</option>
											<option value=\"4\" >20jt s/d <30jt</option>
											<option value=\"5\" >30jt s/d <40jt</option>
											<option value=\"6\" >40jt s/d <50jt</option>
											<option value=\"7\" >50jt s/d <100jt</option>
											<option value=\"8\" >>=100jt</option>
											<option value=\"9\" >>100jt</option>
										</select></td>
									<td><input type=\"submit\" name=\"button2\" id=\"cari-{$id}\" value=\"Tampilkan\"/>
										<input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
										<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span></td>
								</tr>
								<tr>
                                    <td>Rekening</td>
                                    <td>:</td>
                                    <td colspan=\"3\"><select name=\"rekening-{$id}\" style=\"max-width:200px\" id=\"rekening-{$id}\">{$opt_rekening}</select></td>
                                    <td>Kecamatan</td>
                                    <td>:</td>
                                    <td><select name=\"kecamatan-{$id}\" id=\"kecamatan-{$id}\" >{$opt_kecamatan}</select></td>									
                                    <td>Kelurahan</td>
                                    <td>: <select name=\"kelurahan-{$id}\" id=\"kelurahan-{$id}\" style=\"max-width:200px;\">{$opt_kelurahan}</select></td>
                                    <td></td>
									<td></td>
								</tr>								
							</table>
					</form>
				</div> ";
		return $html;
	}

	public function filtering_tunggakan($id)
	{
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		foreach ($this->arr_pajak_gw as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
		}

		$thn = date("Y");
		$opt_tahun = "<option value=\"\">Semua</option>";
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

		$bln = date("m");
		$opt_bulan = "<option value=\"\">Semua</option>";
		for ($b = 1; $b <= 12; $b++) {
			$opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
		}

		$kec = $this->get_list_kecamatan();
		$opt_kecamatan = "<option value=\"\">Semua</option>";
		foreach ($kec as $k => $v) {
			$opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
		}
		$opt_kelurahan = "<option value=\"\">Semua</option>";

		$reks = $this->getDataRekening();
		$opt_rekening = '<option value="">Semua</option>';
		foreach ($reks as $header => $rek) {
			$aRek = array_values($rek);
			if (count($aRek) > 1) {
				$opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
				foreach ($rek as $k => $v) {
					$opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
				}
			} else {
				// $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
				$opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
			}
		}

		$html = "<div class=\"filtering\">
        <style> .monitoring td{background:transparent}.monitoring input[type=\"text\"],.monitoring select{height:23px}</style>
        <form>
						<table width=\"1300\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
								<tr>
									<td width=\"105\">Tahun Pajak </td>
									<td width=\"3\">:</td>
									<td width=\"115\"><select name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\">{$opt_tahun}</select></td>
									<td width=\"100\" colspan=\"2\"><select name=\"simpatda_bulan_pajak-{$id}\" id=\"simpatda_bulan_pajak-{$id}\">{$opt_bulan}</select></td>
									<td width=\"150\">No. Pelaporan </td>
									<td width=\"3\">:</td>
									<td width=\"110\"><input type=\"text\" name=\"sptpd-{$id}\" id=\"sptpd-{$id}\" /></td>                                    
									<td width=\"90\"> Jenis Pajak </td>
									<td width=\"210\">: <select id=\"jenis-{$id}\" name=\"jenis-{$id}\" style=\"width:90%;\">{$opt_jenis_pajak}</select></td>    
									<td>Alamat : <input type=\"text\" name=\"wp_alamat\" id=\"wp_alamat-{$id}\"/></td>
								</tr>                                
								<tr>
									<td>Tgl&nbsp;Lapor</td>
									<td>:</td>
									<td><input type=\"text\" name=\"expired_date1\" id=\"expired_date1-{$id}\" class=\"datepicker\" size=\"10\" /></td>
									<td width=\"22\">s/d </td>
									<td><input type=\"text\" name=\"expired_date2\" id=\"expired_date2-{$id}\" class=\"datepicker\" size=\"10\" /></td>
									<td width=\"150\">Nama WP / Tempat Usaha</td>
									<td>:</td>
									<td width=\"110\"><input type=\"text\" name=\"wp_nama\" id=\"wp_nama-{$id}\" /></td>                                    
									<td>Nilai Tagihan </td>
									<td> : <select id=\"simpatda_dibayar-{$id}\" name=\"simpatda_dibayar-{$id}\">
											<option value=\"0\" >--semua--</option>
											<option value=\"1\" >0 s/d <5jt</option>
											<option value=\"2\" >5jt s/d <10jt</option>
											<option value=\"3\" >10jt s/d <20jt</option>
											<option value=\"4\" >20jt s/d <30jt</option>
											<option value=\"5\" >30jt s/d <40jt</option>
											<option value=\"6\" >40jt s/d <50jt</option>
											<option value=\"7\" >50jt s/d <100jt</option>
											<option value=\"8\" >>=100jt</option>
											<option value=\"9\" >>100jt</option>
										</select></td>
									<td><input type=\"submit\" name=\"button2\" id=\"cari-{$id}\" value=\"Tampilkan\"/>
										<input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
										<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span></td>
								</tr>
								<tr>
                                    <td>Rekening</td>
                                    <td>:</td>
                                    <td colspan=\"3\"><select name=\"rekening-{$id}\" style=\"max-width:200px\" id=\"rekening-{$id}\">{$opt_rekening}</select></td>
                                    <td>Kecamatan</td>
                                    <td>:</td>
                                    <td><select name=\"kecamatan-{$id}\" id=\"kecamatan-{$id}\" >{$opt_kecamatan}</select></td>									
                                    <td>Kelurahan</td>
                                    <td>: <select name=\"kelurahan-{$id}\" id=\"kelurahan-{$id}\" style=\"max-width:200px;\">{$opt_kelurahan}</select></td>
                                    <td></td>
									<td></td>
								</tr>
							</table>
					</form>
				</div> ";
		return $html;
	}

	public function grid_table_tagihan()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "tagihan";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				<style>.number{text-align:right}</style>
                {$this->filtering_tagihan($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
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
							defaultSorting: 'saved_date ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-tagihan.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								jenis: {title: 'Jenis Pajak',width: '10%'},
								sptpd: {title: 'No. Pelaporan',width: '10%'},                                
								npwpd: {title: 'NPWPD',width: '10%'},
								op_nama: {title: 'Nama Pajak / Alamat',width: '10%'},
								simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '10%'},
								simpatda_bulan_pajak: {title: 'Masa Pajak',width: '10%'},
								saved_date: {title: 'Tanggal Ketetapan',width: '10%'},
								expired_date: {title: 'Jatuh Tempo',width: '10%'},
								simpatda_dibayar: {title: 'Tagihan',width: '10%', listClass:'number'},
								simpatda_denda: {title: 'Denda',width: '10%', listClass:'number'},
								
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {                                
								sptpd : $('#sptpd-{$this->_i}').val(),
								wp_alamat : $('#wp_alamat-{$this->_i}').val(),                                
								wp_nama : $('#wp_nama-{$this->_i}').val(),
								simpatda_dibayar : $('#simpatda_dibayar-{$this->_i}').val(),
								jenis : $('#jenis-{$this->_i}').val(),
								expired_date1 : $('#expired_date1-{$this->_i}').val(),
								expired_date2 : $('#expired_date2-{$this->_i}').val(),    
								simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),
								simpatda_bulan_pajak : $('#simpatda_bulan_pajak-{$this->_i}').val(),
								kecamatan : $('#kecamatan-{$this->_i}').val(),
								kelurahan : $('#kelurahan-{$this->_i}').val(),
								rekening : $('#rekening-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click(); 
						$('#kecamatan-{$this->_i}').change(function(){
							if($(this).val()==''){
								$('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>');
								return false;
							}
							$('#kelurahan-{$this->_i}').html('<option value=\'\'>Loading...</option>');
							$.ajax({
								type: \"POST\",
								url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
								data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
								async:false,
								success: function(html){
									$('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>'+html);
								}
							});
						});
					});
				</script>";

		echo $html;
	}

	public function grid_data_tagihan()
	{
		try {
			$arr_config = $this->get_config_value($this->_a);

			$dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);

			$where = " expired_date >= NOW() ";
			$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
			$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
			$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
			$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
			$where .= $_REQUEST['jenis'] == "" ? "" : " AND simpatda_type='{$_REQUEST['jenis']}'";

			$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
			//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND (simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "' OR MONTH(masa_pajak_awal)='{$_REQUEST['simpatda_bulan_pajak']}')";
			$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";

			$where .= $_REQUEST['kecamatan'] == "" ? "" : " AND kecamatan_op = '" . $_REQUEST['kecamatan'] . "'";
			$where .= $_REQUEST['kelurahan'] == "" ? "" : " AND kelurahan_op = '" . $_REQUEST['kelurahan'] . "'";

			if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
			} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
			} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(expired_date,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
			}

			$where .= " AND (payment_flag != 1 OR payment_flag IS NULL)";

			if ($_REQUEST['simpatda_dibayar'] != 0) {
				$arr_dibayar = array(
					1 => " (simpatda_dibayar < 5000000) ",
					2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
					3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
					4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
					5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
					6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
					7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
					8 => "(simpatda_dibayar >= 100000000)",
					9 => "(simpatda_dibayar > 100000000)"
				);
				$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
			}

			if (isset($_REQUEST['rekening']) && $_REQUEST['rekening'] != "") {
				if (strlen($_REQUEST['rekening']) == 9)
					$where .= " AND simpatda_rek like '{$_REQUEST['rekening']}%' ";
				elseif (strlen($_REQUEST['rekening']) > 9)
					$where .= " AND simpatda_rek = '{$_REQUEST['rekening']}' ";
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data        
			$query = "select jenis,sptpd, npwpd, concat(op_nama,' / ',op_alamat) as op_nama, simpatda_tahun_pajak,month(masa_pajak_awal) as bulan,simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
					FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($Conn_gw, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				//die(print($query));
				$row = array_merge($row, array("NO" => ++$no));
				if ($row['simpatda_bulan_pajak'] > 0)
					$row['simpatda_bulan_pajak'] = $this->arr_bulan[(int) $row['simpatda_bulan_pajak']];
				else
					$row['simpatda_bulan_pajak'] = $this->arr_bulan[(int) $row['bulan']];
				$row['npwpd'] = Pajak::formatNPWPD($row['npwpd']);
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar']);
				//$row['simpatda_denda'] = number_format($row['simpatda_denda']);
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

	public function grid_table_tunggakan()
	{
		$DIR = "PATDA-V1";
		$modul = "monitoring";
		$submodul = "tagihan";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.number{text-align:right}</style>
                {$this->filtering_tunggakan($this->_i)}
				<div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
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
							defaultSorting: 'saved_date ASC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-tunggakan.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
							},
							fields: {
								NO : {title: 'No',width: '3%'},
								jenis: {title: 'Jenis Pajak',width: '10%'},
								sptpd: {title: 'No. Pelaporan',width: '10%'},                                
								npwpd: {title: 'NPWPD',width: '10%'},
								op_nama: {title: 'Nama Pajak / Alamat',width: '10%'},
								simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '7%'},
								simpatda_bulan_pajak: {title: 'Masa Pajak',width: '7%'},
								saved_date: {title: 'Tanggal Ketetapan',width: '10%'},
								expired_date: {title: 'Jatuh Tempo',width: '7%'},
								simpatda_dibayar: {title: 'Tagihan',width: '7%', listClass:'number'},
								simpatda_denda: {title: 'Denda',width: '5%', listClass:'number'},
								
							}
						});
						$('#cari-{$this->_i}').click(function (e) {
							e.preventDefault();
							$('#laporanPajak-{$this->_i}').jtable('load', {                                
								sptpd : $('#sptpd-{$this->_i}').val(),
								wp_alamat : $('#wp_alamat-{$this->_i}').val(),                                
								wp_nama : $('#wp_nama-{$this->_i}').val(),
								simpatda_dibayar : $('#simpatda_dibayar-{$this->_i}').val(),
								jenis : $('#jenis-{$this->_i}').val(),
								expired_date1 : $('#expired_date1-{$this->_i}').val(),
								expired_date2 : $('#expired_date2-{$this->_i}').val(),    
								simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),
								simpatda_bulan_pajak : $('#simpatda_bulan_pajak-{$this->_i}').val(),
								kecamatan : $('#kecamatan-{$this->_i}').val(),
								kelurahan : $('#kelurahan-{$this->_i}').val(),
								rekening : $('#rekening-{$this->_i}').val()
							});
						});
						$('#cari-{$this->_i}').click(); 
						$('#kecamatan-{$this->_i}').change(function(){
							$.ajax({
								type: \"POST\",
								url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
								data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
								async:false,
								success: function(html){
									$('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>'+html);
								}
							});
						});
					});
				</script>";

		echo $html;
	}

	public function grid_data_tunggakan()
	{
		try {
			$arr_config = $this->get_config_value($this->_a);

			$dbName = $arr_config['PATDA_DBNAME'];
			$dbHost = $arr_config['PATDA_HOSTPORT'];
			$dbPwd = $arr_config['PATDA_PASSWORD'];
			$dbUser = $arr_config['PATDA_USERNAME'];

			$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
			//mysqli_select_db($dbName);

			$where = " expired_date < NOW() ";
			$where .= $_REQUEST['sptpd'] == "" ? "" : " AND sptpd like '%{$_REQUEST['sptpd']}%'";
			$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
			$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%'";
			$where .= $_REQUEST['jenis'] == "" ? "" : " AND simpatda_type='{$_REQUEST['jenis']}'";

			$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
			//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
			$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";


			if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
			} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
			} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
				$where .= " AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
			}

			$where .= " AND (payment_flag != 1 OR payment_flag IS NULL)";

			$where .= $_REQUEST['kecamatan'] == "" ? "" : " AND kecamatan_op = '" . $_REQUEST['kecamatan'] . "'";
			$where .= $_REQUEST['kelurahan'] == "" ? "" : " AND kelurahan_op = '" . $_REQUEST['kelurahan'] . "'";

			if ($_REQUEST['simpatda_dibayar'] != 0) {
				$arr_dibayar = array(
					1 => " (simpatda_dibayar < 5000000) ",
					2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
					3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
					4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
					5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
					6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
					7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
					8 => "(simpatda_dibayar >= 100000000)",
					9 => "(simpatda_dibayar > 100000000)"
				);
				$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
			}

			if (isset($_REQUEST['rekening']) && $_REQUEST['rekening'] != "") {
				if (strlen($_REQUEST['rekening']) == 9)
					$where .= " AND simpatda_rek like '{$_REQUEST['rekening']}%' ";
				elseif (strlen($_REQUEST['rekening']) > 9)
					$where .= " AND simpatda_rek = '{$_REQUEST['rekening']}' ";
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

			$result = mysqli_query($Conn_gw, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data        
			$query = "select jenis,sptpd, npwpd, concat(op_nama,' / ',op_alamat) as op_nama, simpatda_tahun_pajak,DATE_FORMAT(saved_date, '%d-%m-%Y') as saved_date, simpatda_bulan_pajak,MONTH(masa_pajak_awal) as bulan,
            date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, payment_paid 
            FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";

			$result = mysqli_query($Conn_gw, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				if ($row['simpatda_bulan_pajak'] > 0)
					$row['simpatda_bulan_pajak'] = $this->arr_bulan[(int) $row['simpatda_bulan_pajak']];
				else
					$row['simpatda_bulan_pajak'] = $this->arr_bulan[(int) $row['bulan']];
				$row['npwpd'] = Pajak::formatNPWPD($row['npwpd']);
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar']);
				$row['simpatda_denda'] = isset($row['simpatda_denda']) ? number_format($row['simpatda_denda']) : '';
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

	public function countforsummary()
	{

		$arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$thn = date('Y');
		$where1 = "simpatda_tahun_pajak ='$thn' AND payment_flag = 1";
		$where2 = "simpatda_tahun_pajak < '$thn' AND payment_flag = 1  and PAYMENT_PAID like '$thn%'";
		$query = "SELECT COALESCE(SUM(simpatda_dibayar),0) as total FROM SIMPATDA_GW WHERE ";

		$resThnBerjalan = mysqli_query($Conn_gw, $query . $where1);
		$resTunggakan = mysqli_query($Conn_gw, $query . $where2);

		$rowThnBerjalan = mysqli_fetch_assoc($resThnBerjalan);
		$rowTunggakan = mysqli_fetch_assoc($resTunggakan);

		$data = array("thnberjalan" => number_format($rowThnBerjalan['total']), "tunggakan" => number_format($rowTunggakan['total']), "total" => number_format(($rowThnBerjalan['total'] + $rowTunggakan['total'])));
		echo $this->Json->encode($data);
	}

	public function download_pdf_status_bayar()
	{
		if ($_REQUEST['jenis'] == 29) {
			$this->download_pdf_status_bayar_mineral();
			exit;
		}
		global $sRootPath;
		$arr_config = $this->get_config_value($this->_a);

		$LOGO_CETAK_PDF = $arr_config['LOGO_CETAK_PDF'];
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
		$NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
		$JALAN = $arr_config['ALAMAT_JALAN'];
		$KOTA = $arr_config['ALAMAT_KOTA'];
		$PROVINSI = $arr_config['ALAMAT_PROVINSI'];
		$KODE_POS = $arr_config['ALAMAT_KODE_POS'];
		$PEMERIKSA_NAMA = $arr_config['KASIE_PENGOLAHAN_DATA_NAMA'];
		$PEMERIKSA_NIP = $arr_config['KASIE_PENGOLAHAN_DATA_NIP'];
		$PENDATA_NAMA = $arr_config['KABID_PENDATAAN_NAMA'];
		$PENDATA_NIP = $arr_config['KABID_PENDATAAN_NIP'];

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = " 1=1 ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
		$where .= ($_REQUEST['bank'] == "" || $_REQUEST['bank'] == 'undefined') ? "" : " AND payment_bank_code='{$_REQUEST['bank']}'";

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
		}

		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['kurang_bayar']) && $_REQUEST['kurang_bayar'] == "0") ? "" : " AND patda_kurangbayar > 0";
		}


		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		if (!empty($_REQUEST['jenis'])) {
			$jenis_reg = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			if ($_REQUEST['jenis'] == 8) {

				$tipe_pajak_restoran = $this->jenis_tipe_pajak_restoran[$_REQUEST['jenis_lapor']];
				// var_dump($_REQUEST['jenis_lapor'], $tipe_pajak_restoran);die;
			} else {

				$jenis_nonreg = $this->non_reguler[$_REQUEST['jenis']];
			}

			if (empty($_REQUEST['jenis_lapor'])) {
				$where .= $_REQUEST['jenis'] == "" ? "" : " AND ( a.simpatda_type='{$jenis_reg}' OR a.simpatda_type='{$jenis_nonreg}' )";
			} else {
				if ($_REQUEST['jenis'] == 8) {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $tipe_pajak_restoran;
				} else {
					$jenis = ($_REQUEST['jenis_lapor'] == 1) ? $jenis_reg : $jenis_nonreg;
				}
				// $where .= " AND a.simpatda_type='{$jenis}'";
				$where .= " AND permen.kdrek='{$_REQUEST['jenis_lapor']}'";
			}
		} elseif (!empty($_REQUEST['jenis_lapor'])) {
			$where .= ($_REQUEST['jenis_lapor'] == 1) ? " AND a.simpatda_type<=12 " : "AND a.simpatda_type>12 ";
		}

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";
		// var_dump($_REQUEST);exit;

		//if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
		//	$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		//} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		//} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
		//	$where .=" AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		//}

		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'" : " AND expired_date = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'" : " AND expired_date = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= ($this->_i == 1) ? " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'" : " AND expired_date BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($this->_i == 1) ? " AND payment_flag = 1" : " AND payment_flag = 0";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		$p = $_REQUEST['p'];
		$total = 500;
		if ($p == 'all') {
			$offset = 0;
		} else {
			$offset = ($p - 1) * $total;
		}

		if ($_REQUEST['jenis_lapor'] = '4.1.01.07.07') {

			$join = "join patda_restoran_doc doc on a.id_switching = doc.CPM_ID";
		}

		$query = "select  permen.nmrek,jenis,op_nama,op_alamat,doc.pelaksana_kegiatan, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,
					substr(masa_pajak_awal,6,2) as masa_pajak_awal, simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, 
					date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, simpatda_denda
					FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					{$join}
					left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek
					WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC, payment_paid ASC LIMIT {$offset}, {$total}";


		$query = "select permen.nmrek,jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid, payment_code, patda_kurangbayar, payment_paid_kurangbayar, a.masa_pajak_awal, a.masa_pajak_akhir
					FROM SIMPATDA_GW a left join SIMPATDA_TYPE b on a.simpatda_type = b.id 
					
					left join patda_rek_permen13 permen on  a.simpatda_rek=permen.kdrek
					WHERE {$where} ORDER BY jenis, simpatda_tahun_pajak DESC,simpatda_bulan_pajak DESC LIMIT {$offset}, {$total}";


		// echo $query;
		// exit;
		$result = mysqli_query($Conn_gw, $query);

		$startListData = "<table border=\"1\" width=\"690\" cellpadding=\"2\" style=\"font-size:32px\">
						<tr>
							<th align=\"center\" width=\"20\"><b>No.</b></th>
							<th align=\"center\" width=\"80\"><b>Jenis Pajak</b></th>
							<th align=\"center\" width=\"80\"><b>No. Pelaporan</b></th>
							<th align=\"center\" width=\"60\"><b>NPWPD</b></th>
							<th align=\"center\" width=\"80\"><b>Nama Pajak</b></th>
							<th align=\"center\" width=\"80\"><b>Nama WP</b></th>
							<th align=\"center\" width=\"80\"><b>Alamat WP</b></th>
							<th align=\"center\" width=\"30\"><b>Petugas</b></th>
							<th align=\"center\" width=\"40\"><b>Tahun Pajak</b></th>
							<th align=\"center\" width=\"58\"><b>Masa Pajak</b></th>
							<th align=\"center\" width=\"68\"><b>Tanggal Disetujui</b></th>
							<th align=\"center\" width=\"70\"><b>Jatuh Tempo</b></th>
							<th align=\"center\" width=\"70\"><b>Tagihan</b></th>
							<th align=\"center\" width=\"70\"><b>Denda Lapor</b></th>
							<th align=\"center\" width=\"70\"><b>Kode Verifikasi</b></th>		
							<th align=\"center\" width=\"70\"><b>Tanggal Bayar</b></th>
						</tr>";
		$listData = "";
		$i = $offset + 1;
		while ($rowData = mysqli_fetch_assoc($result)) {
			if ($rowData['nama_tipe_pajak'] == "" || $rowData['nama_tipe_pajak'] == null) {

				$rowData['jenis'] = $rowData['jenis'];
			} else {
				$rowData['jenis'] = $rowData['nama_tipe_pajak'];
			}
			$bulan = isset($this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']] : $this->arr_bulan[(int) $rowData['masa_pajak_awal']];
			$listData .= "<tr>
							<td align=\"right\">{$i}</td>
							<td align=\"left\">{$rowData['jenis']}</td>
							<td align=\"left\">{$rowData['sptpd']}</td>
							<td align=\"left\">{$rowData['npwpd']}</td>
							<td align=\"left\">{$rowData['op_nama']}</td>
							<td align=\"left\">{$rowData['wp_nama']}</td>
							<td align=\"left\">{$rowData['wp_alamat']}</td>
							<td align=\"left\">{$rowData['pelaksana_kegiatan']}</td>
							<td align=\"center\">{$rowData['simpatda_tahun_pajak']}</td>
							<td align=\"center\">{$bulan}</td>
							<td align=\"left\">{$rowData['saved_date']}</td>
							<td align=\"left\">{$rowData['expired_date']}</td>
							<td align=\"right\">" . number_format($rowData['simpatda_dibayar']) . "</td>
							<td align=\"right\">" . number_format($rowData['simpatda_denda']) . "</td>
							<td align=\"left\">{$rowData['payment_code']}</td>
							<td align=\"left\">{$rowData['payment_paid']}</td>    
						</tr>";
			$i++;
		}
		$endListData = "</table>";

		$htmlData = $startListData . $listData . $endListData;
		$html = "<html>
				<table border=\"0\" cellpadding=\"2\" width=\"1015\">
					<tr>
						<td><table width=\"1010\" border=\"0\">
								<tr>
									<th valign=\"top\" align=\"center\">                                   
										" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
										BADAN PENGELOLAAN PAJAK DAN RETRIBUSI DAERAH<br /><br />        
										<font class=\"normal\">{$JALAN} - {$KODE_POS}<br/>{$PROVINSI}</font>
									</th>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><hr style=\"height: 2px;\"/></td>
					</tr>
					<tr>
						<td align=\"center\"><b style=\"text-decoration: underline\">DATA PAJAK " . ($this->_i == 1 ? "SUDAH BAYAR" : "BELUM BAYAR") . "</b></td>
					</tr>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td>{$htmlData}</td>
					</tr>
					<tr>
						<td></td>
					</tr>                    
				</table>
			</html>";

		$htmlPengesahan = "<table border=\"0\" cellpadding=\"2\" width=\"1015\"><tr>
						<td>
							<table>
								<tr>
									<td align=\"center\">
										Mengetahui,<br>
										KABID PERENCANAAN DAN<br/>PENGENDALIAN OPERASIONAL
										<br>
										<br>
										<br>
										<br>
										<br>
										{$PENDATA_NAMA}<br>
										PENATA TK. I<br>
										NIP. {$PENDATA_NIP}
									</td>
									<td align=\"center\">
										Diperiksa oleh,<br>
										KASIE PENGOLAHAN DATA DAN<br/>INFORMASI
										<br>
										<br>
										<br>
										<br>
										<br>
										{$PEMERIKSA_NAMA}<br>
										PENATA<br>
										NIP. {$PEMERIKSA_NIP}
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>";

		ob_clean();
		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);

		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setLanguageArray(array("w_page" => "halaman"));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


		$pdf->SetAuthor('Alfa System');
		$pdf->SetTitle('Alfatax');
		$pdf->SetSubject('Alfatax spppd');
		$pdf->SetKeywords('Alfatax');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 14, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->startPageGroup();
		$pdf->AddPage('L', 'A4');
		// $pdf->Image($sRootPath . "view/Registrasi/configure/logo/" . $LOGO_CETAK_PDF, 35, 15, 30, '', '', '', '', false, 300, '', false);
		$pdf->Image($sRootPath . "view/Registrasi/configure/logo/" . $LOGO_CETAK_PDF, 20, 3, 25, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($html, true, false, false, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->writeHTML($htmlPengesahan, true, false, false, false, '');
		$nmfile = ($this->_i == 1) ? "sudah bayar" : "belum bayar";
		$pdf->Output("{$nmfile}.pdf", 'I');
	}


	public function download_pdf_status_bayar_mineral()
	{
		global $sRootPath;
		$arr_config = $this->get_config_value($this->_a);

		$LOGO_CETAK_PDF = $arr_config['LOGO_CETAK_PDF'];
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
		$NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
		$JALAN = $arr_config['ALAMAT_JALAN'];
		$KOTA = $arr_config['ALAMAT_KOTA'];
		$PROVINSI = $arr_config['ALAMAT_PROVINSI'];
		$KODE_POS = $arr_config['ALAMAT_KODE_POS'];
		$PEMERIKSA_NAMA = $arr_config['KASIE_PENGOLAHAN_DATA_NAMA'];
		$PEMERIKSA_NIP = $arr_config['KASIE_PENGOLAHAN_DATA_NIP'];
		$PENDATA_NAMA = $arr_config['KABID_PENDATAAN_NAMA'];
		$PENDATA_NIP = $arr_config['KABID_PENDATAAN_NIP'];

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = " simpatda_type='29' ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";
		$where .= $_REQUEST['operator'] == "" ? "" : " AND (operator like '%{$_REQUEST['operator']}%' or operator like '%{$_REQUEST['operator']}%')";


		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";


		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($this->_s == 1) ? " AND payment_flag = 1" : " AND payment_flag = 0";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		$p = $_REQUEST['p'];
		$total = 100;
		if ($p == 'all') {
			$offset = 0;
		} else {
			$offset = ($p - 1) * $total;
		}

		$query = "select d.nmrek, jenis,op_nama,op_alamat, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,
					substr(masa_pajak_awal,6,2) as masa_pajak_awal, simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, 
					date_format(saved_date,'%d-%m-%Y') as saved_date, 
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
					FROM GW_PATDA_BASE.SIMPATDA_GW a 
					inner join GW_PATDA_BASE.SIMPATDA_TYPE b on a.simpatda_type = b.id 
					inner join SW_PATDA_BASE.PATDA_MINERAL_DOC_ATR c on a.id_switching = c.CPM_ATR_MINERAL_ID
					inner join SW_PATDA_BASE.PATDA_REK_PERMEN13 d on c.CPM_ATR_NAMA= d.kdrek
					WHERE {$where} ORDER BY simpatda_tahun_pajak DESC, masa_pajak_awal DESC, payment_paid ASC  LIMIT {$offset}, {$total}";
		#echo $query;exit;
		$result = $res = mysqli_query($Conn_gw, $query);
		$startListData = "<table border=\"1\" width=\"690\" cellpadding=\"2\" style=\"font-size:32px\">
						<tr>
							<th align=\"center\" width=\"30\"><b>No.</b></th>
							<th align=\"center\" width=\"100\"><b>Jenis Pajak</b></th>
							<th align=\"center\" width=\"65\"><b>Golongan</b></th>
							<th align=\"center\" width=\"65\"><b>No. Pelaporan</b></th>
							<th align=\"center\" width=\"80\"><b>NPWPD</b></th>
							<th align=\"center\" width=\"100\"><b>Nama Pajak</b></th>
							<th align=\"center\" width=\"100\"><b>Nama WP</b></th>
							<th align=\"center\" width=\"100\"><b>Alamat WP</b></th>
							<th align=\"center\" width=\"50\"><b>Tahun Pajak</b></th>
							<th align=\"center\" width=\"58\"><b>Masa Pajak</b></th>
							<th align=\"center\" width=\"67\"><b>Tanggal Lapor</b></th>
							<th align=\"center\" width=\"67\"><b>Jatuh Tempo</b></th>
							<th align=\"center\" width=\"70\"><b>Tagihan</b></th>
							<th align=\"center\" width=\"70\"><b>Tanggal Bayar</b></th>
						</tr>";

		$rowGroup = array();
		while ($rowData = mysqli_fetch_assoc($res)) {
			$tgl_byr = substr($rowData['payment_paid'], 0, 10);
			$rowGroup["{$rowData['npwpd']}{$rowData['simpatda_tahun_pajak']}{$rowData['masa_pajak_awal']}{$tgl_byr}"][] = $rowData;
		}

		$listData = "";
		$i = $offset + 1;
		foreach ($rowGroup as $rows) {
			$total = 0;
			foreach ($rows as $rowData) {
				$bulan = $this->arr_bulan[(int) $rowData['masa_pajak_awal']];
				$listData .= "<tr>
						<td align=\"right\">{$i}</td>
						<td align=\"left\">{$rowData['jenis']}</td>
						<td align=\"left\">{$rowData['nmrek']}</td>
						<td align=\"left\">{$rowData['sptpd']}</td>
						<td align=\"left\">{$rowData['npwpd']}</td>
						<td align=\"left\">{$rowData['op_nama']}</td>
						<td align=\"left\">{$rowData['wp_nama']}</td>
						<td align=\"left\">{$rowData['wp_alamat']}</td>
						<td align=\"center\">{$rowData['simpatda_tahun_pajak']}</td>
						<td align=\"center\">{$bulan}</td>
						<td align=\"left\">{$rowData['saved_date']}</td>
						<td align=\"left\">{$rowData['expired_date']}</td>
						<td align=\"right\">" . number_format($rowData['simpatda_dibayar']) . "</td>
						<td align=\"left\">{$rowData['payment_paid']}</td>    
					</tr>";
				$total += $rowData['simpatda_dibayar'];
				$i++;
			}
			$listData .= "<tr>
						<td align=\"right\" colspan=\"12\"><b>TOTAL</b></td>
						<td align=\"right\"><b>" . number_format($total) . "</b></td>
						<td align=\"right\"></td>
					</tr>";
		}
		$endListData = "</table>";

		$htmlData = $startListData . $listData . $endListData;
		$html = "<html>
				<table border=\"0\" cellpadding=\"2\" width=\"1015\">
					<tr>
						<td><table width=\"1010\" border=\"0\">
								<tr>
									<th valign=\"top\" align=\"center\">                                   
										PEMERINTAH KABUPATEN " . strtoupper($KOTA) . "<br />      
										BADAN PENGELOLAAN PAJAK DAN RETRIBUSI DAERAH<br /><br />        
										<font class=\"normal\">{$JALAN} - {$KODE_POS}<br/>{$PROVINSI}</font>
									</th>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><hr style=\"height: 2px;\"/></td>
					</tr>
					<tr>
						<td align=\"center\"><b style=\"text-decoration: underline\">DATA PAJAK " . ($this->_s == 1 ? "SUDAH BAYAR" : "BELUM BAYAR") . "</b></td>
					</tr>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td>{$htmlData}</td>
					</tr>
					<tr>
						<td></td>
					</tr>                    
				</table>
			</html>";
		$htmlPengesahan = "<table border=\"0\" cellpadding=\"2\" width=\"1015\"><tr>
						<td>
							<table>
								<tr>
									<td align=\"center\">
										Mengetahui,<br>
										KABID PERENCANAAN DAN<br/>PENGENDALIAN OPERASIONAL
										<br>
										<br>
										<br>
										<br>
										<br>
										{$PENDATA_NAMA}<br>
										PENATA TK. I<br>
										NIP. {$PENDATA_NIP}
									</td>
									<td align=\"center\">
										Diperiksa oleh,<br>
										KASIE PENGOLAHAN DATA DAN<br/>INFORMASI
										<br>
										<br>
										<br>
										<br>
										<br>
										{$PEMERIKSA_NAMA}<br>
										PENATA<br>
										NIP. {$PEMERIKSA_NIP}
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>";

		ob_clean();
		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);

		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setLanguageArray(array("w_page" => "halaman"));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('-');
		$pdf->SetSubject('-');
		$pdf->SetKeywords('-');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 14, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->startPageGroup();
		$pdf->AddPage('L', 'A4');
		$pdf->Image($sRootPath . "view/Registrasi/configure/logo/" . $LOGO_CETAK_PDF, 35, 15, 30, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($html, true, false, false, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->writeHTML($htmlPengesahan, true, false, false, false, '');

		$nmfile = ($this->_s == 1) ? "sudah bayar" : "belum bayar";
		$pdf->Output("{$nmfile}.pdf", 'I');
	}

	public function download_excel_status_bayar_mineral()
	{
		global $sRootPath;
		$arr_config = $this->get_config_value($this->_a);

		$LOGO_CETAK_PDF = $arr_config['LOGO_CETAK_PDF'];
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
		$NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
		$JALAN = $arr_config['ALAMAT_JALAN'];
		$KOTA = $arr_config['ALAMAT_KOTA'];
		$PROVINSI = $arr_config['ALAMAT_PROVINSI'];
		$KODE_POS = $arr_config['ALAMAT_KODE_POS'];
		$PEMERIKSA_NAMA = $arr_config['KASIE_PENGOLAHAN_DATA_NAMA'];
		$PEMERIKSA_NIP = $arr_config['KASIE_PENGOLAHAN_DATA_NIP'];
		$PENDATA_NAMA = $arr_config['KABID_PENDATAAN_NAMA'];
		$PENDATA_NIP = $arr_config['KABID_PENDATAAN_NIP'];

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);

		$where = " simpatda_type='29' ";
		$npwpd = $_REQUEST['sptpd'] == "" ? "" : preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['sptpd']);
		$where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$npwpd}%')";
		$where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
		$where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";

		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}'" : "";

		if ($this->_i == 1) {
			$where .= (isset($_REQUEST['operator']) && $_REQUEST['operator'] != "") ? " AND (operator like '%{$_REQUEST['operator']}%')" : "";
		}

		$where .= $_REQUEST['payment_code'] == "" ? "" : " AND (payment_code like '%{$_REQUEST['payment_code']}%' or payment_code like '%{$_REQUEST['payment_code']}%')";

		$where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
		//$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
		$where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND  MONTH(STR_TO_DATE(masa_pajak_awal,'%Y-%m-%d')) = '{$_REQUEST['simpatda_bulan_pajak']}'";


		if ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] == "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date1']}'";
		} elseif ($_REQUEST['expired_date1'] == "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') = '{$_REQUEST['expired_date2']}'";
		} elseif ($_REQUEST['expired_date1'] != "" && $_REQUEST['expired_date2'] != "") {
			$where .= " AND str_to_date(substr(payment_paid,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['expired_date1']}' AND '{$_REQUEST['expired_date2']}'";
		}

		$where .= ($this->_i == 1) ? " AND payment_flag = 1" : " AND payment_flag = 0";

		if ($_REQUEST['simpatda_dibayar'] != 0) {
			$arr_dibayar = array(
				1 => " (simpatda_dibayar < 5000000) ",
				2 => "(simpatda_dibayar >= 5000000 AND simpatda_dibayar < 10000000)",
				3 => "(simpatda_dibayar >= 10000000 AND simpatda_dibayar < 20000000)",
				4 => "(simpatda_dibayar >= 20000000 AND simpatda_dibayar < 30000000)",
				5 => "(simpatda_dibayar >= 30000000 AND simpatda_dibayar < 40000000)",
				6 => "(simpatda_dibayar >= 40000000 AND simpatda_dibayar < 50000000)",
				7 => "(simpatda_dibayar >= 50000000 AND simpatda_dibayar < 100000000)",
				8 => "(simpatda_dibayar >= 100000000)",
				9 => "(simpatda_dibayar > 100000000)"
			);
			$where .= " AND {$arr_dibayar[$_REQUEST['simpatda_dibayar']]}";
		}

		$p = $_REQUEST['p'];
		if ($p == 'all') {
			$total = $_REQUEST['total'];
			$offset = 0;
		} else {
			$total = 20000;
			$offset = ($p - 1) * $total;
		}

		$query = "select d.nmrek, jenis,op_nama,op_alamat, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,
					substr(masa_pajak_awal,6,2) as masa_pajak_awal, simpatda_bulan_pajak,
					date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, 
					date_format(saved_date,'%d-%m-%Y') as saved_date, patda_kurangbayar, payment_paid_kurangbayar,
					date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
					FROM GW_PATDA_BASE.SIMPATDA_GW a 
					inner join GW_PATDA_BASE.SIMPATDA_TYPE b on a.simpatda_type = b.id 
					inner join SW_PATDA_BASE.PATDA_MINERAL_DOC_ATR c on a.id_switching = c.CPM_ATR_MINERAL_ID
					inner join SW_PATDA_BASE.PATDA_REK_PERMEN13 d on c.CPM_ATR_NAMA= d.kdrek
					WHERE {$where} ORDER BY simpatda_tahun_pajak DESC, masa_pajak_awal DESC, payment_paid ASC  LIMIT {$offset}, {$total}";
		#echo $query;exit;
		$res = mysqli_query($Conn_gw, $query);
		$sumRows = mysqli_num_rows($res);
		$rowGroup = array();
		while ($rowData = mysqli_fetch_assoc($res)) {
			$tgl_byr = substr($rowData['payment_paid'], 0, 10);
			$rowGroup["{$rowData['npwpd']}{$rowData['simpatda_tahun_pajak']}{$rowData['masa_pajak_awal']}{$tgl_byr}"][] = $rowData;
		}

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpost")
			->setTitle("-")
			->setSubject("-
			->setDescription("pbb")
			->setKeywords("-");

		// Add some data
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'No.')
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nama Pajak')
			->setCellValue('D1', 'No. Pelaporan')
			->setCellValue('E1', 'NPWPD')
			->setCellValue('F1', 'Nama WP')
			->setCellValue('G1', 'Alamat WP')
			->setCellValue('H1', 'Tahun Pajak')
			->setCellValue('I1', 'Bulan Pajak')
			->setCellValue('J1', 'Tgl Jatuh Tempo')
			->setCellValue('K1', 'Tagihan (Rp)')
			->setCellValue('L1', 'Status')
			->setCellValue('M1', 'Tanggal Lapor')
			->setCellValue('N1', 'Tanggal Bayar')
			->setCellValue('O1', 'Kode Verifikasi')
			->setCellValue('P1', 'Kurang Bayar')
			->setCellValue('Q1', 'Tanggal Bayar');

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$i = $offset + 1;
		foreach ($rowGroup as $rows) {
			$total = 0;
			$awalCount = $row;
			foreach ($rows as $rowData) {
				$tgl_jth_tempo = explode('-', $rowData['expired_date']);
				if (count($tgl_jth_tempo) == 3)
					$tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];
				$bulan = $this->arr_bulan[(int) $rowData['masa_pajak_awal']];

				$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $i);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['jenis']));
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['op_nama']));
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_nama']);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['wp_alamat']);
				$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['simpatda_tahun_pajak']);
				$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $bulan);
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $tgl_jth_tempo);
				$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['simpatda_dibayar']);
				$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : '');
				$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['saved_date']);
				$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['payment_paid']);
				$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['payment_code']);
				$objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['patda_kurangbayar']);
				$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['payment_paid_kurangbayar']);
				$row++;
				$i++;
			}

			$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, "=SUM(K{$awalCount}:K" . ($row - 1) . ")");

			$objPHPExcel->getActiveSheet()->getStyle("A{$row}:Q{$row}")->applyFromArray(
				array(
					'font' => array(
						'bold' => true
					)
				)
			);
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle($this->_i == 1 ? 'Sudah Bayar' : 'Belum bayar');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:Q' . ($row - 1))->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('I2:Q' . ($row - 1))->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->getStartColor()->setRGB('E4E4E4');
		$objPHPExcel->getActiveSheet()->getStyle('A2:A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('B2:G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('H2:J' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('K2:K' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getStyle('L2:L' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('M2:M' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('N2:N' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);

		ob_clean();

		$nmfile = $_REQUEST['nmfile'];
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}
