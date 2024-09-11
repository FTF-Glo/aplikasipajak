
<?php

class ProfilWajibPajak extends Pajak
{

	function __construct()
	{
		parent::__construct();
		$WP = isset($_POST['WP']) ? $_POST['WP'] : array();

		foreach ($WP as $a => $b) {
			$this->$a = is_array($b) ? $b : mysqli_escape_string($this->Conn, trim($b));
		}
	}

	public function filtering($id)
	{
		$opt_jenis_pajak = '<option value="">Semua Jenis Pajak</option>';
		foreach ($this->arr_pajak as $x => $y) {
			$opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
		}

		$opt_kecamatan = '<option value="">Semua</option>';
		$res = mysqli_query($this->Conn, "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN");
		while ($list = mysqli_fetch_object($res)) {
			$opt_kecamatan .= "<option value=\"" . $list->CPM_KEC_ID . "\">" . $list->CPM_KECAMATAN . "</option>";
		}

		$reks = $this->getDataRekening();
		unset($reks['4.1.01.09']); // reklame
		unset($reks['4.1.01.14']); // mineral
		$opt_rekening = '<option value="">Semua</option>';
		foreach ($reks as $header => $rek) {
			$aRek = array_values($rek);
			if (count($aRek) > 1) {
				$opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
				foreach ($rek as $k => $v) {
					$opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
				}
			} else {
				$opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
			}
		}


		// $html = "<div class=\"filtering\">
        //             <form><table><tr valign=\"bottom\">
        //                 <td>Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >{$opt_jenis_pajak}</select></td>
        //                 <td>NPWPD :<br><input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" ></td>
        //                 <td>Nama WP/OP :<br><input type=\"text\" name=\"CPM_NAMA-{$id}\" id=\"CPM_NAMA-{$id}\" ></td>
		// 				<td>Kecamatan :<br><select id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
		// 				<td>Kelurahan :<br><select id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">Semua</option></select></td>
		// 				<td>Rekening :<br><select style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
		// 				<td>
		// 					<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
		// 					<button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel_profilwp('{$id}','function/PATDA-V1/pelayanan/profilwp/svc-download.xls.php');\">Eksport ke Excel</button>
		// 					<button type=\"button\" id=\"cetakv2-{$id}\" onclick=\"javascript:download_excel_profilwp('{$id}','function/PATDA-V1/pelayanan/profilwp/svc-download.xls-V2.php');\">Eksport ke Excel V2</button>
		// 				</td>
		// 				</tr></table></form>
        //         </div> ";

		$html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td><label>Jenis Pajak :</label><select class=\"form-control\" style=\"height: 32px; width: 144px\" name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >{$opt_jenis_pajak}</select></td>
                        <td><label>NPWPD :</label><input type=\"text\" style=\"height: 32px; width: 128\" class=\"form-control\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" ></td>
                        <td><label>Nama WP/OP :</label><input type=\"text\" style=\"height: 32px; width: 128\" class=\"form-control\" name=\"CPM_NAMA-{$id}\" id=\"CPM_NAMA-{$id}\" ></td>
						<td><label>Kecamatan :</label><select class=\"form-control\" style=\"height: 32px; width: 128\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
						<td><label>Kelurahan :</label><select class=\"form-control\" style=\"height: 32px; width: 128\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">Semua</option></select></td>
						<td><label>Rekening :</label><select class=\"form-control\" style=\"height: 32px; width: 128; max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
						<td style=\"padding-left: 10px\">
							<button class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important;\" type=\"submit\" id=\"cari-{$id}\"><i class='fa fa-search'></i> Cari</button>
							<button class=\"btn btn-success lm-btn\" style=\"font-size: 0.7rem !important;\" type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel_profilwp('{$id}','function/PATDA-V1/pelayanan/profilwp/svc-download.xls.php');\"><i class='fa fa-download'></i> Eksport ke Excel</button>
							<button class=\"btn btn-success lm-btn\" style=\"font-size: 0.7rem !important;\" type=\"button\" id=\"cetakv2-{$id}\" onclick=\"javascript:download_excel_profilwp('{$id}','function/PATDA-V1/pelayanan/profilwp/svc-download.xls-V2.php');\"><i class='fa fa-download'></i> Eksport ke Excel V2</button>
						</td>
						</tr></table></form>
                </div> ";
		return $html;
	}

	public function grid_table()
	{
		$DIR = "PATDA-V1";
		$modul = "pelayanan/profilwp";

		$html = "
				<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />			
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				<style>.filtering td{background:transparent}.filtering input,.filtering select{height:23px}</style>
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
                            defaultSorting: 'CPM_JENIS_PAJAK ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_NPWPD: {title: 'NPWPD',key: true,width: '15%'},
                                CPM_NAMA_WP: {title: 'Nama WP',width: '15%'},
                                CPM_NOP: {title: 'NOP',key: true,width: '10%'},
                                CPM_NAMA_OP: {title: 'Nama Usaha',width: '15%'},
                                CPM_ALAMAT_OP: {title: 'Alamat Usaha',width: '20%'},
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
                                CPM_ACTION: {title: 'Action',width: '10%'}
                            },
                            recordsLoaded: function(event, data) {
								get_allow_deactivated();
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NAMA : $('#CPM_NAMA-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()
                                    
                            });
                        });
						$('#cari-{$this->_i}').click();	
						$(\"select#CPM_KECAMATAN-{$this->_i}\").change(function () {
							if($(this).val()==''){
								$(\"select#CPM_KELURAHAN-{$this->_i}\").html(\"<option value=''>Semua</option>\");
								return false;
							}
							showKelurahan({$this->_i});
						})				
					});
					
					function showKelurahan(sts) {
						var id = $('select#CPM_KECAMATAN-{$this->_i}').val()
			
						var request = $.ajax({
						url: \"function/PATDA-V1/pelayanan/svc-kecamatan.php\",
							type: \"POST\",
							data: {id: id, kel: 1},
							dataType: \"json\",
							beforeSend : function(d){
								// alert(d);
								$(\"select#CPM_KELURAHAN-{$this->_i}\").html(\"<option value=''>Loading...</option>\");
							},
							success: function (data) {
								var c = data.msg.length;
								var options = \"\";
			
								if (parseInt(c)>=0){
									options += \"<option value=''>Semua</option>\";
									for (var i = 0; i < c; i++) {
										options += \"<option value='\" + data.msg[i].id + \"'>\" + data.msg[i].name + \"</option>\";
									}
									$(\"select#CPM_KELURAHAN-{$this->_i}\").html(options);
								}else{
									options += \"<option value=''>Tidak ada kelurahan</option>\";
								}
							},
							error : function(msg){
								$(\"select#CPM_KELURAHAN-{$this->_i}\").html(\"<option value=''>Semua</option>\");
							}  
						})      
					}

                    function get_allow_delete(){
						 var allid = '';
						 $('.NPWPD_JENIS').val('loading...');
						 $('.NPWPD_JENIS').each(function(){
							allid += $(this).attr('id')+'|';
						 })
						 $.ajax({
							 type:'post',
							 data:{allid:allid,'function':'get_allow_delete',a:'{$this->_a}',m:'{$this->_m}'},
							 url : 'view/{$DIR}/{$modul}/svc-profilwp.php',
							 dataType:'json',
							 success:function(data){
								for(var x in data){
									$('#'+x).html(data[x]);
								}
								
							 }
						 });
					}
                    function get_allow_deactivated(){
						 var allid = '';
						 $('.NPWPD_JENIS').val('loading...');
						 $('.NPWPD_JENIS').each(function(){
							allid += $(this).attr('id')+'|';
						 })
						 $.ajax({
							 type:'post',
							 data:{allid:allid,'function':'get_allow_deactivated',a:'{$this->_a}',m:'{$this->_m}'},
							 url : 'view/{$DIR}/{$modul}/svc-profilwp.php',
							 dataType:'json',
							 success:function(data){
								for(var x in data){
									$('#'+x).html(data[x]);
								}
								
							 }
						 });
					}
					function deleteNPWPD(id){
						if(confirm('Apakah anda yakin untuk menghapus NPWPD ini?') == false) return false;
						var alasan = prompt('Masukan alasan penghapusan :');
						if(alasan!=''){
							$.ajax({
								 type:'post',
								 data:{id:id,'function':'delete_npwpd',alasan:alasan,a:'{$this->_a}',m:'{$this->_m}',u:'{$_REQUEST['u']}'},
								 url : 'view/{$DIR}/{$modul}/svc-profilwp.php',
								 dataType:'json',
								 success:function(data){
									alert(data.msg);
									if(data.res == 1) $('#cari-{$this->_i}').click();
								 }
							 });
						}
						else {
							alert('Alasan harus diisi!');
							return false;
						}
					}	
					
					function deactivatedNPWPD(id){
						
						console.log(id);
						
						if(confirm('Apakah anda yakin untuk menonaktifkan NPWPD ini?') == false) return false;
						var alasan = prompt('Masukan alasan penonaktifan :');
						if(alasan!=''){
							$.ajax({
								 type:'post',
								 data:{id:id,'function':'deactivated_npwpd',alasan:alasan,a:'{$this->_a}',m:'{$this->_m}',u:'{$_REQUEST['u']}'},
								 url : 'view/{$DIR}/{$modul}/svc-profilwp.php',
								 dataType:'json',
								 success:function(data){
									alert(data.msg);
									if(data.res == 1) $('#cari-{$this->_i}').click();
								 }
							});
						}
						else {
							alert('Alasan harus diisi!');
							return false;
						}
					}
					
					function activatedNPWPD(id){
						
						console.log(id);
						
						//if(confirm('Apakah anda yakin untuk mengaktifkan kembali NPWPD ini?') == false) return false;
						var conf = confirm('Apakah anda yakin untuk mengaktifkan kembali NPWPD ini?');
						//var alasan = prompt('Masukan alasan penonaktifan :');
						if(conf == true){
							$.ajax({
								 type:'post',
								 data:{id:id,'function':'activated_npwpd',a:'{$this->_a}',m:'{$this->_m}',u:'{$_REQUEST['u']}'},
								 url : 'view/{$DIR}/{$modul}/svc-profilwp.php',
								 dataType:'json',
								 success:function(data){
									alert(data.msg);
									if(data.res == 1) $('#cari-{$this->_i}').click();
								 }
							});
						}
						else {
							//alert('Alasan harus diisi!');
							//return false;
						}
					}					
                </script>";
		echo $html;
	}

	public function grid_data()
	{
		if (isset($_REQUEST['CPM_NPWPD']))
			$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
		// var_dump($_REQUEST);exit();
		try {
			switch ($this->_i) {
				case '1':
					$where = "CPM_AKTIF = '1'";
					break;
				case '2':
					$where = "CPM_AKTIF = '0'";
					break;
				case '3':
					$where = "CPM_AKTIF = '2'";
					break;
			}

			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";


			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND  CPM_KECAMATAN_OP like \"{$_REQUEST['CPM_KECAMATAN']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND  CPM_KELURAHAN_OP like \"{$_REQUEST['CPM_KELURAHAN']}%\" " : "";

			// $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";

			if (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") {
				if (strlen($_REQUEST['CPM_KODE_REKENING']) == 9)
					$where .= " AND CPM_REKENING like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
				elseif (strlen($_REQUEST['CPM_KODE_REKENING']) > 9)
					$where .= " AND CPM_REKENING = '{$_REQUEST['CPM_KODE_REKENING']}' ";
			}

			if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
				$this->arr_pajak_table = array($_REQUEST['CPM_JENIS_PAJAK'] => $this->arr_pajak_table[$_REQUEST['CPM_JENIS_PAJAK']]);
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM (";
			foreach ($this->arr_pajak_table as $idpjk => $pjk) {
				$query .= "(SELECT CPM_ID, {$idpjk} AS CPM_JENIS_PAJAK
                        FROM PATDA_{$pjk}_PROFIL
                        WHERE {$where} ) UNION";
			}
			$query = substr($query, 0, strlen($query) - 5);
			$query .= ") as profil";
			// echo $query;

			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result) or die(mysqli_error($this->Conn));
			$recordCount = $row['RecordCount'];

			#query select list data      ini   
			$query = "SELECT profil.* FROM (";
			foreach ($this->arr_pajak_table as $idpjk => $pjk) {
				$query .= "(SELECT CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, CPM_NOP, CPM_NPWPD, CPM_NAMA_WP, CPM_NAMA_OP, CPM_ALAMAT_OP
                        FROM PATDA_{$pjk}_PROFIL
                        WHERE {$where} ) UNION";
			}
			$query = substr($query, 0, strlen($query) - 5);
			$query .= ") as profil ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			// echo $query;
			$result = mysqli_query($this->Conn, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {

				$row = array_merge($row, array("NO" => ++$no));
				$base64 = "a={$this->_a}&m={$this->_m}&f=fPatdaProfilOP{$row['CPM_JENIS_PAJAK']}&npwpd={$row['CPM_NPWPD']}&nop={$row['CPM_NOP']}";
				$url = "main.php?param=" . base64_encode($base64);

				$id_action = $row['CPM_NPWPD'] . '_' . $row['CPM_NOP'] . '_' . $row['CPM_JENIS_PAJAK'];

				switch ($this->_i) {
					case '1':
						$row['CPM_NPWPD'] = "<a href='" . $url . "'>" . Pajak::formatNPWPD($row['CPM_NPWPD']) . "</a>";
						$row['CPM_ACTION'] = "<button class=\"NPWPD_JENIS btn btn-danger\" id=\"{$id_action}\" onclick=\"deactivatedNPWPD('$id_action')\"><i class=\"fa fa-times\"></i> Nonaktif</button>"; //kahfi
						break;
					case '2':
						$row['CPM_NPWPD'] = "<a href='#' class='btn-action' data-id='{$row['CPM_NPWPD']}_{$row['CPM_NOP']}_{$row['CPM_JENIS_PAJAK']}' data-idx='{$this->_i}'>" . Pajak::formatNPWPD($row['CPM_NPWPD']) . "</a>";
						$row['CPM_ACTION'] = "<button class=\"NPWPD_JENIS btn btn-primary\" id=\"{$id_action}\" onclick=\"activatedNPWPD('$id_action')\"><i class=\"fa fa-check\"></i> Aktif</button>"; //kahfi
						break;
					case '3':
						$row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
						break;
				}

				$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
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

	function download_excel()
	{
		// echo '<pre>';
		// print_r($_REQUEST);
		// die();
		$objPHPExcel = new PHPExcel();

		$sql_kec = "SELECT * FROM PATDA_MST_KECAMATAN";
		$res_kec = mysqli_query($this->Conn, $sql_kec);

		$z = 0;
		while ($rows = mysqli_fetch_assoc($res_kec)) {


			//$objPHPExcel->setActiveSheetIndex($z);
			//$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
			//$objPHPExcel->getActiveSheet()->setCellValue('B1', 'KECAMATAN');

			//$objPHPExcel->getActiveSheet()->setCellValue("A1","IDNYA");
			//$objPHPExcel->getActiveSheet()->setCellValue("B2","KECAMATANNYA");

			$id_kecamatan = $rows["CPM_KEC_ID"];



			switch ($_REQUEST['s']) {
				case '1':
					$where = "CPM_AKTIF = '1' AND CPM_KECAMATAN_OP = '$id_kecamatan'";
					break;
				case '2':
					$where = "CPM_AKTIF = '0' AND CPM_KECAMATAN_OP = '$id_kecamatan'";
					break;
				case '3':
					$where = "CPM_AKTIF = '2' AND CPM_KECAMATAN_OP = '$id_kecamatan'";
					break;
			}

			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
			$where .= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND CPM_ALAMAT_OP like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";

			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND CPM_KECAMATAN_OP like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND CPM_KELURAHAN_OP like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";

			$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
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
				$query .= "(SELECT CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, CPM_NPWPD, CPM_NOP, CPM_NAMA_WP, CPM_NAMA_OP, CPM_ALAMAT_OP
					FROM PATDA_{$pjk}_PROFIL
					WHERE {$where} ) UNION";
			}

			$query = substr($query, 0, strlen($query) - 5);
			$query .= ") as profil ORDER BY profil.CPM_JENIS_PAJAK ASC";

			// echo $query;exit;
			$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
			// Create new PHPExcel object
			//$objPHPExcel = new PHPExcel();

			// Set properties
			$objPHPExcel->getProperties()->setCreator("vpost")
				->setLastModifiedBy("vpos")
				->setTitle("-")
				->setSubject("-")
				->setDescription("patda")
				->setKeywords("-");

			// Add some data
			$objPHPExcel->setActiveSheetIndex($z)
				->setCellValue('A1', 'No.')
				->setCellValue('B1', 'NPWPD')
				->setCellValue('C1', 'NOP')
				->setCellValue('D1', 'Nama WP')
				->setCellValue('E1', 'Nama OP')
				->setCellValue('F1', 'Jenis Pajak')
				->setCellValue('G1', 'Alamat');

			// Miscellaneous glyphs, UTF-8
			$objPHPExcel->setActiveSheetIndex($z);

			$row = 2;
			$sumRows = mysqli_num_rows($res);

			while ($rowData = mysqli_fetch_assoc($result)) {
				$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

				$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NOP'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_NAMA_WP']);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_OP']);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_ALAMAT_OP']);
				$row++;
			}


			// Rename sheet
			//$objPHPExcel->getActiveSheet()->setTitle('Daftar Wajib Pajak');

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

			$objPHPExcel->getActiveSheet()->getStyle('A1:G' . ($row - 1))->applyFromArray(
				array(
					'borders' => array(
						'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
						)
					)
				)
			);

			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('E4E4E4');

			for ($x = "A"; $x <= "G"; $x++) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
			}

			$kecamatan = $rows['CPM_KECAMATAN'];
			$objPHPExcel->getActiveSheet()->setTitle("$kecamatan");
			$objPHPExcel->createSheet();

			$z++;
		}

		ob_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="Data-Objek-Pajak-' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	// function download_excel_v2() {	
	// 	// echo '<pre>';
	// 	// print_r($_REQUEST);
	// 	// die();
	// 	$objPHPExcel = new PHPExcel();

	// 	$sql_kec = "SELECT * FROM PATDA_MST_KECAMATAN";
	//     $res_kec = mysqli_query($this->Conn, $sql_kec);

	//     $z = 0;
	//     while($rows = mysqli_fetch_assoc($res_kec)){


	//         //$objPHPExcel->setActiveSheetIndex($z);
	//         //$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
	//         //$objPHPExcel->getActiveSheet()->setCellValue('B1', 'KECAMATAN');

	//         //$objPHPExcel->getActiveSheet()->setCellValue("A1","IDNYA");
	//         //$objPHPExcel->getActiveSheet()->setCellValue("B2","KECAMATANNYA");

	// 		$id_kecamatan = $rows["CPM_KEC_ID"];



	// 		switch($_REQUEST['s']){
	// 			case '1' : $where = "CPM_AKTIF = '1' AND CPM_KECAMATAN_OP = '$id_kecamatan'"; break;
	// 			case '2' : $where = "CPM_AKTIF = '0' AND CPM_KECAMATAN_OP = '$id_kecamatan'"; break;
	// 			case '3' : $where = "CPM_AKTIF = '2' AND CPM_KECAMATAN_OP = '$id_kecamatan'"; break;
	// 		} 

	// 		$where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
	// 		$where.= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
	// 		$where.= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND CPM_ALAMAT_OP like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";

	// 		$where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND CPM_KECAMATAN_OP like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
	// 		$where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND CPM_KELURAHAN_OP like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";

	// 		$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
	// 		$res = mysqli_query($this->Conn, $sql);

	// 		while ($row = mysqli_fetch_assoc($res)) {
	// 			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
	// 		}

	// 		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
	// 			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
	// 		}

	// 		#query select list data        
	// 		$query = "SELECT profil.* FROM (";
	// 		foreach ($arrPajak as $idpjk => $pjk) {
	// 			$query .= "(SELECT CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, CPM_NPWPD, CPM_NOP, CPM_NAMA_WP, CPM_NAMA_OP, CPM_ALAMAT_OP
	// 				FROM PATDA_{$pjk}_PROFIL
	// 				WHERE {$where} ) UNION";
	// 		}

	// 		$query = substr($query, 0, strlen($query) - 5);
	// 		$query.= ") as profil ORDER BY profil.CPM_JENIS_PAJAK ASC";

	// 		// echo $query;exit;
	// 		$result = mysqli_query($this->Conn, $query) or die (mysqli_error($this->Conn));
	// 		// Create new PHPExcel object
	// 		//$objPHPExcel = new PHPExcel();

	// 		// Set properties
	// 		$objPHPExcel->getProperties()->setCreator("vpost")
	// 				->setLastModifiedBy("vpos")
	// 				->setTitle("-")
	// 				->setSubject("-")
	// 				->setDescription("patda")
	// 				->setKeywords("-");

	// 		// Add some data
	// 		$objPHPExcel->setActiveSheetIndex($z)
	// 				->setCellValue('A1', 'No.')
	// 				->setCellValue('B1', 'NPWPD')
	// 				->setCellValue('C1', 'NOP')
	// 				->setCellValue('D1', 'Nama WP')
	// 				->setCellValue('E1', 'Nama OP')
	// 				->setCellValue('F1', 'Jenis Pajak')
	// 				->setCellValue('G1', 'Alamat');

	// 		// Miscellaneous glyphs, UTF-8
	// 		$objPHPExcel->setActiveSheetIndex($z);

	// 		$row = 2;
	// 		$sumRows = mysqli_num_rows($res);

	// 		while ($rowData = mysqli_fetch_assoc($result)) {
	// 			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

	// 			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
	// 			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
	// 			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NOP'], PHPExcel_Cell_DataType::TYPE_STRING);
	// 			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_NAMA_WP']);
	// 			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_OP']);
	// 			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
	// 			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_ALAMAT_OP']);
	// 			$row++;
	// 		}


	// 		// Rename sheet
	// 		//$objPHPExcel->getActiveSheet()->setTitle('Daftar Wajib Pajak');

	// 		//----set style cell
	// 		//style header
	// 		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray(
	// 				array(
	// 					'font' => array(
	// 						'bold' => true
	// 					),
	// 					'alignment' => array(
	// 						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	// 						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	// 					)
	// 				)
	// 		);

	// 		$objPHPExcel->getActiveSheet()->getStyle('A1:G' . ($row - 1))->applyFromArray(
	// 				array(
	// 					'borders' => array(
	// 						'allborders' => array(
	// 							'style' => PHPExcel_Style_Border::BORDER_THIN
	// 						)
	// 					)
	// 				)
	// 		);

	// 		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	// 		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('E4E4E4');

	// 		for ($x = "A"; $x <= "G"; $x++) {
	// 			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
	// 		}

	// 		$kecamatan = $rows['CPM_KECAMATAN'];
	//         $objPHPExcel->getActiveSheet()->setTitle("$kecamatan");
	//         $objPHPExcel->createSheet();

	// 		$z++;
	// 	}

	// 	ob_clean();
	// 	// Redirect output to a client’s web browser (Excel5)
	// 	header('Content-Type: application/vnd.ms-excel');

	// 	header('Content-Disposition: attachment;filename="Data-Objek-Pajak-' . date('yymdhmi') . '.xls"');
	// 	header('Cache-Control: max-age=0');

	// 	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	// 	$objWriter->save('php://output');

	// }

	function download_excel_v2()
	{
		$objPHPExcel = new PHPExcel();

		switch ($_REQUEST['s']) {
			case '1':
				$where = "CPM_AKTIF = '1'";
				break;
			case '2':
				$where = "CPM_AKTIF = '0'";
				break;
			case '3':
				$where = "CPM_AKTIF = '2'";
				break;
			default:
				$where = "CPM_AKTIF = '1'";
				break;
		}

		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND ( CPM_NAMA_WP like \"{$_REQUEST['CPM_NAMA']}%\" OR CPM_NAMA_OP like  \"{$_REQUEST['CPM_NAMA']}%\") " : "";
		$where .= (isset($_REQUEST['CPM_ALAMAT']) && $_REQUEST['CPM_ALAMAT'] != "") ? " AND CPM_ALAMAT_OP like  \"%{$_REQUEST['CPM_ALAMAT']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND CPM_KECAMATAN_OP like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
		$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND CPM_KELURAHAN_OP like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";

		$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
		$res = mysqli_query($this->Conn, $sql);

		$arrPajak = array();
		while ($row = mysqli_fetch_assoc($res)) {
			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
		}

		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
		}

		// Gabungkan query untuk semua jenis pajak
		$query = "SELECT profil.* FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			$query .= "(SELECT CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, CPM_NPWPD, CPM_NOP, CPM_NAMA_WP, CPM_NAMA_OP, CPM_ALAMAT_OP, CPM_KECAMATAN_OP
				FROM PATDA_{$pjk}_PROFIL
				WHERE {$where} ) UNION ";
		}
		$query = substr($query, 0, strlen($query) - 7);
		$query .= ") as profil ORDER BY profil.CPM_KECAMATAN_OP, profil.CPM_JENIS_PAJAK ASC";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		// Set properties
		$objPHPExcel->getProperties()->setCreator("vpost")
			->setLastModifiedBy("vpos")
			->setTitle("-")
			->setSubject("-")
			->setDescription("patda")
			->setKeywords("-");

		// Isi semua data ke dalam satu sheet
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("Data Objek Pajak");

		// Add header data
		$objPHPExcel->getActiveSheet()
			->setCellValue('A1', 'No.')
			->setCellValue('B1', 'NPWPD')
			->setCellValue('C1', 'NOP')
			->setCellValue('D1', 'Nama WP')
			->setCellValue('E1', 'Nama OP')
			->setCellValue('F1', 'Jenis Pajak')
			->setCellValue('G1', 'Alamat')
			->setCellValue('H1', 'Kecamatan');

		$row = 2;
		while ($rowData = mysqli_fetch_assoc($result)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NOP'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_NAMA_WP']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_OP']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_ALAMAT_OP']);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_KECAMATAN_OP']);
			$row++;
		}

		// Set style untuk header
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
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

		// Set border untuk seluruh data
		$objPHPExcel->getActiveSheet()->getStyle('A1:H' . ($row - 1))->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);

		// Set warna background untuk header
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('E4E4E4');

		// Set lebar kolom otomatis
		foreach (range('A', 'H') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}

		ob_clean();
		// Redirect output ke browser
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="Data-Objek-Pajak-' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function get_allow_delete()
	{
		try {

			$allid = isset($_POST['allid']) ? substr($_POST['allid'], 0, strlen($_POST['allid']) - 1) : ""; #substr menghilangkan tanda '|' di akhir 
			$arr_id = explode("|", $allid); #memecah semua device id menjadi array untuk keperluan query

			$result = array();

			foreach ($arr_id as $id) {
				$data = explode("_", $id);
				$npwpd = $data[0];
				$nop = $data[1];
				$jns = $data[2];
				$pjk = $this->arr_pajak_table[$jns];

				$query = "select count(*) as total FROM PATDA_{$pjk}_PROFIL A INNER JOIN PATDA_{$pjk}_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
				WHERE A.CPM_NPWPD = '{$npwpd}' AND A.CPM_NOP = '{$nop}'";
				$res = mysqli_query($this->Conn, $query);
				$res_data = mysqli_fetch_assoc($res);
				$btn_hapus = ($res_data['total'] == 0) ? "<input type='button' value='hapus' onclick=\"javascript:deleteNPWPD('{$id}')\">" : "-";
				$result[$id] = $btn_nonaktif;
			}

			print $this->Json->encode($result);

			mysqli_close($this->Conn);
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function get_allow_deactivated()
	{
		try {

			$allid = isset($_POST['allid']) ? substr($_POST['allid'], 0, strlen($_POST['allid']) - 1) : ""; #substr menghilangkan tanda '|' di akhir 
			$arr_id = explode("|", $allid); #memecah semua device id menjadi array untuk keperluan query

			$result = array();

			foreach ($arr_id as $id) {
				$data = explode("_", $id);
				$npwpd = $data[0];
				$nop = preg_match('/^[\w]+$/', $data[1]);
				$jns = $data[2];
				$pjk = $this->arr_pajak_table[$jns];
				$new_id = "{$npwpd}_{$nop}_{$jns}";
				$query = "select count(*) as total FROM PATDA_{$pjk}_PROFIL A INNER JOIN PATDA_{$pjk}_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
				WHERE A.CPM_NPWPD = '{$npwpd}' AND A.CPM_NOP = '{$nop}'";
				$res = mysqli_query($this->Conn, $query);
				$res_data = mysqli_fetch_assoc($res);
				$btn_nonaktif = ($res_data['total'] == 0) ? "<input type='button' value='Nonaktif' onclick=\"javascript:deactivatedNPWPD('{$new_id}')\">" : "-";
				$result[$new_id] = $btn_nonaktif;
			}

			print $this->Json->encode($result);

			mysqli_close($this->Conn);
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	public function delete_npwpd()
	{
		$result = array('res' => 0, 'msg' => 'Kesalahan server, NPWPD gagal di hapus!');

		if (isset($_POST['id'])) {
			$id = $_POST['id'];
			$user = $_POST['u'];
			$alasan = mysqli_real_escape_string($this->Conn, $_POST['alasan']);
			$data = explode("_", $id);
			$npwpd = $data[0];
			$nop = $data[1];
			$jns = $data[2];
			$pjk = $this->arr_pajak_table[$jns];

			$query = "select count(*) as total FROM PATDA_{$pjk}_PROFIL A INNER JOIN PATDA_{$pjk}_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
			WHERE A.CPM_NPWPD = '{$npwpd}' AND A.CPM_NOP = '{$nop}'";
			$res = mysqli_query($this->Conn, $query);
			$res_data = mysqli_fetch_assoc($res);
			if ($res_data['total'] == 0) {
				// $query = "DELETE FROM PATDA_{$pjk}_PROFIL WHERE CPM_NPWPD = '{$npwpd}' AND CPM_NOP = '{$nop}'";
				$query = "UPDATE PATDA_{$pjk}_PROFIL SET CPM_AKTIF='2',CPM_ALASAN='{$alasan}',CPM_LAST_USER='{$user}',CPM_LAST_DATETIME=now() WHERE CPM_NPWPD = '{$npwpd}' AND CPM_NOP = '{$nop}'";
				$res = mysqli_query($this->Conn, $query);
				if ($res) {
					$result['res'] = 1;
					$result['msg'] = "NPWPD berhasil dihapus!";
				}
			} else {
				$result['msg'] = "NPWPD tidak dapat dihapus karena telah melakukan pelaporan!";
			}
		}
		print $this->Json->encode($result);
	}

	public function deactivated_npwpd()
	{
		//$result = array('res'=>0, 'msg'=>'Kesalahan server, NPWPD gagal di nonaktifkan!');

		if (isset($_POST['id'])) {
			$id = $_POST['id'];
			$user = $_POST['u'];
			$alasan = mysqli_real_escape_string($this->Conn, $_POST['alasan']);
			$data = explode("_", $id);
			$npwpd = $data[0];
			$nop = $data[1];
			$jns = $data[2];
			$pjk = $this->arr_pajak_table[$jns];

			$query = "select count(*) as total FROM PATDA_{$pjk}_PROFIL A INNER JOIN PATDA_{$pjk}_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
			WHERE A.CPM_NPWPD = '{$npwpd}' AND A.CPM_NOP = '{$nop}'";
			$res = mysqli_query($this->Conn, $query);
			$res_data = mysqli_fetch_assoc($res);
			if ($res_data['total'] == 0) {
				$query = "UPDATE PATDA_{$pjk}_PROFIL SET CPM_AKTIF='0',CPM_ALASAN='{$alasan}',CPM_LAST_USER='{$user}',CPM_LAST_DATETIME=now() WHERE CPM_NPWPD = '{$npwpd}' AND CPM_NOP = '{$nop}'";
				$res = mysqli_query($this->Conn, $query);
				if ($res) {
					$result['res'] = 1;
					$result['msg'] = "NPWPD berhasil dinonaktifkan!";
				}
			} else {
				$result['msg'] = "NPWPD tidak dapat dinonaktifkan karena telah melakukan pelaporan!";
			}
		}
		print $this->Json->encode($result);
	}

	public function activated_npwpd()
	{
		//$result = array('res'=>0, 'msg'=>'Kesalahan server, NPWPD gagal di diaktifkan!');

		if (isset($_POST['id'])) {
			$id = $_POST['id'];
			$user = $_POST['u'];
			$data = explode("_", $id);
			$npwpd = $data[0];
			$nop = $data[1];
			$jns = $data[2];
			$pjk = $this->arr_pajak_table[$jns];

			$query = "select count(*) as total FROM PATDA_{$pjk}_PROFIL A INNER JOIN PATDA_{$pjk}_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
			WHERE A.CPM_NPWPD = '{$npwpd}' AND A.CPM_NOP = '{$nop}'";
			$res = mysqli_query($this->Conn, $query);
			$res_data = mysqli_fetch_assoc($res);
			if ($res_data['total'] == 0) {
				$query = "UPDATE PATDA_{$pjk}_PROFIL SET CPM_AKTIF='1',CPM_ALASAN='',CPM_LAST_USER='{$user}',CPM_LAST_DATETIME=now() WHERE CPM_NPWPD = '{$npwpd}' AND CPM_NOP = '{$nop}'";
				$res = mysqli_query($this->Conn, $query);
				if ($res) {
					$result['res'] = 1;
					$result['msg'] = "NPWPD berhasil diaktifkan!";
				}
			} else {
				$result['msg'] = "NPWPD tidak dapat diaktifkan karena telah melakukan pelaporan!";
			}
		}
		print $this->Json->encode($result);
	}
}

?>
