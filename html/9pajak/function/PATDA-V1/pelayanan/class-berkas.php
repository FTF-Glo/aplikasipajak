<?php

class BerkasPajak extends Pajak
{

	function __construct()
	{
		parent::__construct();

		$BERKAS = isset($_POST['BERKAS']) ? $_POST['BERKAS'] : array();
		foreach ($BERKAS as $a => $b) {
			$this->$a = mysqli_escape_string($this->Conn, trim($b));
		}

		$this->CPM_LAMPIRAN = isset($_POST['CPM_LAMPIRAN']) ? $_POST['CPM_LAMPIRAN'] : array();

		$this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
		if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
	}

	public function get_berkas()
	{
		# // inisialisasi data kosong
		$data = array(
			"CPM_ID" => "", "CPM_TGL_INPUT" => date("d-m-Y"), "CPM_JENIS_PAJAK" => "",
			"CPM_NO_SPTPD" => "", "CPM_NPWPD" => "", "CPM_NAMA_WP" => "", "CPM_ALAMAT_WP" => "",
			"CPM_AUTHOR" => "", "CPM_NAMA_OP" => "", "CPM_ALAMAT_OP" => "", "CPM_LAMPIRAN" => "", "CPM_STATUS" => "",
		);

		#query untuk mengambil data berkas
		$query = "SELECT * FROM PATDA_BERKAS WHERE CPM_ID = '{$this->_id}' AND CPM_SPTPD='1' ";
		// echo $query;exit();
		$result = mysqli_query($this->Conn, $query);
		#jika ada data
		if (mysqli_num_rows($result) > 0) {
			$data = mysqli_fetch_assoc($result);

			if ($data['CPM_JENIS_PAJAK'] == 7) {
				$list_nop = $this->get_list_nop($data['CPM_NO_SPTPD']);
				$arr_nop = array();
				foreach ($list_nop as $nop) {
					$arr_nop[] = $nop['CPM_NOP'] . ' - ' . $nop['CPM_NAMA_OP'];
				}
				$data['CPM_NAMA_OP'] = implode(", ", $arr_nop);
				$data['CPM_ALAMAT_OP'] = '-';
			}
		} else {
			$data['CPM_AUTHOR'] = $this->Data->uname;
		}

		return $data;
	}

	public function search_sptpd()
	{
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

	public function save()
	{
		$this->CPM_ID = c_uuid();
		$this->CPM_STATUS = 1; #count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
		$this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

		$query = "SELECT * FROM PATDA_BERKAS WHERE CPM_NO_SPTPD ='{$this->CPM_NO_SPTPD}' AND CPM_SPTPD='1' ";
		$result = mysqli_query($this->Conn, $query);
		if (mysqli_num_rows($result) == 0) {

			$query = sprintf(
				"INSERT INTO PATDA_BERKAS
					(CPM_ID,CPM_TGL_INPUT,CPM_JENIS_PAJAK,CPM_NO_SPTPD,CPM_NPWPD,
					CPM_NAMA_WP,CPM_ALAMAT_WP,CPM_NAMA_OP,CPM_ALAMAT_OP,CPM_LAMPIRAN,
					CPM_AUTHOR,CPM_STATUS)
					VALUES ( '%s','%s','%s','%s','%s',
							 '%s','%s','%s','%s','%s',
							 '%s','%s')",
				$this->CPM_ID,
				$this->CPM_TGL_INPUT,
				$this->CPM_JENIS_PAJAK,
				$this->CPM_NO_SPTPD,
				$this->CPM_NPWPD,
				$this->CPM_NAMA_WP,
				$this->CPM_ALAMAT_WP,
				$this->CPM_NAMA_OP,
				$this->CPM_ALAMAT_OP,
				$this->CPM_LAMPIRAN,
				$this->CPM_AUTHOR,
				$this->CPM_STATUS
			);
			$res = mysqli_query($this->Conn, $query);
			if ($res) {
				$_SESSION['_success'] = 'Berkas berhasil disimpan';
				$_SESSION['_tab_berkas'] = 1;
			} else {
				$_SESSION['_error'] = 'Berkas gagal disimpan';
			}
		} else {
			$msg = "Gagal disimpan, Berkas untuk No. SPTPD <b>{$this->CPM_NO_SPTPD}</b> sudah diinput sebelumnya!";
			$this->Message->setMessage($msg);
			$_SESSION['_error'] = $msg;
		}
	}

	public function update()
	{
		$this->CPM_STATUS = 1; # count($this->CPM_LAMPIRAN) == 3 ? 1 : 0;
		$this->CPM_LAMPIRAN = implode(";", $this->CPM_LAMPIRAN);

		$query = sprintf(
			"UPDATE PATDA_BERKAS SET
					CPM_LAMPIRAN = '%s',
					CPM_AUTHOR = '%s',
					CPM_STATUS = '%s'
					WHERE CPM_ID = '{$this->CPM_ID}'",
			$this->CPM_LAMPIRAN,
			$this->CPM_AUTHOR,
			$this->CPM_STATUS
		);
		$res = mysqli_query($this->Conn, $query);
		$urlpelaporan = '';
		$id_doc = $this->getIDdoc($this->CPM_NO_SPTPD,$this->CPM_JENIS_PAJAK);
		$id_tranmain = $this->getIDTranmain($id_doc,$this->CPM_JENIS_PAJAK);
		// $base644 = "a=aPatda&m=mPatdaPelayananPelapor2&mod=pel&f=fPatdaPelayanan2&id={$id_doc}&s=2&i=2&flg=0&info=&idtran={$id_tranmain}&read=0";
		$base644 ="a=aPatda&m=mPatdaPelayanan&f=fPatdaBerkasPajak{$this->CPM_JENIS_PAJAK}&id={$id_doc}&s=2&mod=ply&idtran={$id_tranmain}&read=0";
		$urlpelaporan = "main.php?param=" . base64_encode($base644);
		// var_dump($urlpelaporan);die;
	   if($res){
		   $_SESSION['_success'] = 'Berkas berhasil diupdate';
		//    $_SESSION['_tab_berkas'] = 1; 
		header("Location: ../../../{$urlpelaporan}");
		exit;
	   }else{
		   $_SESSION['_error'] = 'Berkas gagal diupdate';
	   }
   	}

	public function filtering($id)
	{
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

		$filter_kode_bayar = $id == 3 || $id == 6  ? "<td> Kode Bayar : <br><input type=\"text\" name=\"PAYMENT_CODE-{$id}\" id=\"PAYMENT_CODE-{$id}\" value=\"\" /> </td>" : '';
// var_dump($this->_i);die;

		// FILTER ASLI

		// $html = "<div class=\"filtering\">
		// 			<form><table><tr valign=\"bottom\">
		// 				<td><input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" s=\"{$this->_s}\">
		// 				Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >{$opt_jenis_pajak}</select></td>
		// 				<td>Tahun Pajak :<br><select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select></td>
		// 				<td>Masa Pajak :<br><select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\" >{$opt_bulan}</select> </td>
		// 				<td>No. SPTPD :<br><input type=\"text\" name=\"CPM_NO_SPTPD-{$id}\" id=\"CPM_NO_SPTPD-{$id}\" ></td>
		// 				{$filter_kode_bayar}";
		// 				if ($this->_i == 3 || $this->_i == 6) { 
		// 					$html .= "<td>Total Pajak :<br><input type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\"> </td>";
		// 				}
		// 				$html .= "<td>NPWPD :<br><input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\"> </td>

        //                 <td>Tanggal Lapor :<br><input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly size=\"10\" class=\"date\" >
		// 										<button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\">x</button> s.d 
		// 										<input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" size=\"10\" class=\"date\" >
		// 										<button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">x</button></td>
		// 				<td>
		// 					<button type=\"submit\" id=\"cari-{$id}\">Cari</button>
		// 					<button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-berkas.xls.php','sptpd')\">Export to xls</button>
		// 				</td>
		// 			</tr></table></form>
		// 		</div> ";

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
							<button class=\"btn btn-success button-rounded\" style=\"border-radius:8px\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$id}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$id}\">
								<i class=\"fa fa-filter\"></i> Filter Data
							</button>
						</div>
						<div class=\"col-12\"> 
							<div class=\"collapse\" id=\"collapsFilter-{$id}\">
								<div class=\"card card-body\">

									<div class=\"form-filtering\">
									
										<form>
											<table>
												<div class=\"row\">

												<div class=\" form-group col-md-3\"> 
														<label>No. SPTPD</label>
														<input  class=\"form-control\" type=\"text\" name=\"CPM_NO_SPTPD-{$id}\" id=\"CPM_NO_SPTPD-{$id}\" >
													</div>

													<div class=\" form-group col-md-3\"> 
														<label>NPWPD</label>
														<input class=\"form-control\"  type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\">
													</div>";
													if ($this->_i == 3 || $this->_i == 6) { 
														$html .= "<div class=\" form-group col-md-3\"> 
																		<label>Total Pajak</label>
																		<input class=\"form-control\" type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\">

																	</div>";
														
													}
											
													$html .= "<div class=\" form-group col-md-3\"> 
														<label>Jenis Pajak</label>
														<input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" s=\"{$this->_s}\">
														<select class=\"form-control\" name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\">{$opt_jenis_pajak}</select>
													</div>
													<div class=\" form-group col-md-3\"> 
														<label>Tahun</label>
														<select class=\"form-control\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select>
													</div>

													<div class=\" form-group col-md-3\"> 
														<label>Masa Pajak</label>
														<select class=\"form-control\" name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select>
													</div>

											

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

													<div class=\" form-group col-md-12\"> 
														<button type=\"submit\" class=\"btn btn-success\" id=\"cari-{$id}\"><i class=\"fa fa-search\"></i> Cari</button>  
														<button type=\"button\" class=\"btn btn-success\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-berkas.xls.php','sptpd')\"><i class=\"fa fa-download\"></i> Export to xls</button>
														
													</div>
												</div>
											</table>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>";

		return $html;
	}

	public function filtering_sptpd_tahunan($id)
	{
		$html = "<div class=\"filtering\">
					<form>

						NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >
						TAHUN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\"><option value=''>All</option>";
		for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
			$html .= "<option value='{$th}'>{$th}</option>";
		}
		$html .= "</select>
						<button type=\"submit\" id=\"cari-{$id}\"><i class=\"fa fa-search\"></i> Cari</button>
						<!-- <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-berkas.xls.php','sptpd')\"><i class=\"fa fa-download\"></i> Export to xls</button>  -->
					</form>
				</div> ";
		return $html;
	}

	public function grid_table()
	{
		$grid_nilai_pajak = $this->_i == 3 || $this->_i == 6 ? "CPM_NILAI_PAJAK : $('#CPM_NILAI_PAJAK-{$this->_i}').val()," : '';

		$DIR = "PATDA-V1";
		$modul = "pelayanan";
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
							defaultSorting: 'CPM_NO_SPTPD DESC',
							selecting: true,
							actions: {
								listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',
							},
							fields: {
								" . (($this->_i == 3) ? "QRIS : {title: '',width: '3%'}," : '') .
			"ROWNUM : {title: 'No',width: '3%'},
								CPM_ID: {key: true,list: false},
								CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								CPM_NO_SPTPD: {title: 'No. SPTPD',width: '10%'},
								CPM_VERSION: {title: 'Versi Dok',width: '7%'},
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_NAMA_WP: {title: 'Wajib Pajak',width: '10%'},
                                CPM_NAMA_OP: {title: 'Objek Pajak',width: '10%'},
                                CPM_AUTHOR: {title: 'User Input',width: '10%'},
                                " . ($this->_i == 3 || $this->_i == 6 ? "CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'}," : "") . "
                                " . ($this->_i == 3 || $this->_i == 5 || $this->_i == 6 ? "kode_verifikasi: {title: 'Kode Bayar',width: '10%'}," : "") . "
                                " . ($this->_i == 5 ? "expired_date: {title: 'Expired Date',width: '10%'}," : "") . "
								CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'},
								" . ($this->_i == 3 || $this->_i == 5 || $this->_i == 6 ? "" : "CPM_STATUS: {title: 'Status',width: '10%'}") . "
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
								CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
								PAYMENT_CODE : $('#PAYMENT_CODE-{$this->_i}').val(),
								CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
								CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
								CPM_TAHUN_PAJAK: $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                TOTAL_PAJAK : $('#TOTAL_PAJAK-{$this->_i}').val(),
								{$grid_nilai_pajak}
							});
						});
						$('#cari-{$this->_i}').click();
					});
				</script>";
		echo $html;
	}

	public function grid_table_sptpb_tahunan()
	{
		$DIR = "PATDA-V1";
		$modul = "pelayanan";
		$html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
				<script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				{$this->filtering_sptpd_tahunan($this->_i)}
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
								ROWNUM : {title: 'No',width: '3%'},
								CPM_ID: {key: true,list: false},
								CPM_NPWPD: {title: 'NPWPD',width: '10%'},
								CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								CPM_NO_SPTPD: {title: 'No. SPTPD',width: '10%'},
								CPM_VERSION: {title: 'Versi Dok',width: '7%'},
								CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
								" . ($this->_i == 1 ? "" : "CPM_AUTHOR: {title: 'Petugas',width: '10%'},") . "
								CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'},
								PRINT_SPTPD: {title: 'Action',width: '10%'}
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
								CPM_NO_SPTPD : $('#CPM_NO_SPTPD-{$this->_i}').val(),
								PAYMENT_CODE : $('#PAYMENT_CODE-{$this->_i}').val(),
								CPM_TAHUN_PAJAK: $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
								CPM_NPWPD: $('#CPM_NPWPD-{$this->_i}').val(),
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
			// print_r($this->_i );exit;
			if ($this->_i == 1) {
				$this->grid_data_berkas_masuk();
			} elseif ($this->_i == 2) {
				$this->grid_data_berkas_diterima();
			} elseif ($this->_i == 3) {
				$this->grid_data_pajak_disetujui();
			} elseif ($this->_i == 4) {
				$this->grid_data_sptpd_tahunan();
			} elseif ($this->_i == 5) {
				$this->grid_data_pajak_disetujui();
			} elseif ($this->_i == 6) {
				$this->grid_data_pajak_bayar();
			}
		} catch (Exception $ex) {
			#Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print $this->Json->encode($jTableResult);
		}
	}

	private function grid_data_berkas_masuk()
	{
		$wpid = $_SESSION['npwpd'];

		if ($_SESSION['role'] == 'rmPatdaWp') {
			$where = "CPM_STATUS='0' AND CPM_SPTPD='1' AND CPM_NPWPD ='$wpid'";
		} else {
			$where = "CPM_STATUS='0' AND CPM_SPTPD='1'";
		}

		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
		$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_BERKAS WHERE {$where}";
		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		$recordCount = $row['RecordCount'];

// var_dump($arrPajak);die;
		$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPTPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, date_FORMAT(CPM_TGL_INPUT, '%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ, CPM_NAMA_WP, CPM_NAMA_OP
					FROM PATDA_BERKAS WHERE {$where}
					ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		$result = mysqli_query($this->Conn, $query);

		$rows = array();
		$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
		while ($row = mysqli_fetch_assoc($result)) {
			$row = array_merge($row, array("ROWNUM" => ++$no));

			$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

			$base64 = "a={$this->_a}&m={$this->_m}&f={$this->_f}&id={$row['CPM_ID']}&sts={$row['CPM_STATUS']}&read={$row['READ']}";
			$url = "main.php?param=" . base64_encode($base64);

			$row['CPM_NO_SPTPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SPTPD']}</a>";
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

	private function grid_data_berkas_diterima()
	{
		$where = "CPM_STATUS='1' AND CPM_SPTPD='1'";

		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
		$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM PATDA_BERKAS WHERE {$where}";
		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		$recordCount = $row['RecordCount'];

		#query select list data
		$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPTPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, date_FORMAT(CPM_TGL_INPUT, '%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ, CPM_NAMA_WP, CPM_NAMA_OP
					FROM PATDA_BERKAS WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		$result = mysqli_query($this->Conn, $query);

		$rows = array();
		$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
		while ($row = mysqli_fetch_assoc($result)) {
			$row = array_merge($row, array("ROWNUM" => ++$no));

			$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

			$base64 = "a={$this->_a}&m={$this->_m}&f={$this->_f}&id={$row['CPM_ID']}&sts={$row['CPM_STATUS']}&read={$row['READ']}";
			$url = "main.php?param=" . base64_encode($base64);

			$row['CPM_NO_SPTPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SPTPD']}</a>";
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

	private function grid_data_pajak_disetujui()
	{
		$wpid = $_SESSION['npwpd'];

		if ($_SESSION['role'] == 'rmPatdaWp') {
			$where = "tr.CPM_TRAN_STATUS='2' && prf.CPM_NPWPD ='$wpid'";
		} else {
			$where = "tr.CPM_TRAN_STATUS='2'";
		}

		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		// tambahan
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ?
			" AND CPM_MASA_PAJAK = \"" . $_REQUEST['CPM_MASA_PAJAK'] . "\" " : "";
		// $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
		//$where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
		$where .= (isset($_REQUEST['PAYMENT_CODE']) && $_REQUEST['PAYMENT_CODE'] != "") ? " AND payment_code = \"{$_REQUEST['PAYMENT_CODE']}\" " : "";
		$where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

		$where .= (isset($_REQUEST['CPM_NILAI_PAJAK']) && $_REQUEST['CPM_NILAI_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = '" . str_replace(array(',', '.'), '', $_REQUEST['CPM_NILAI_PAJAK']) . "' " : "";


		/*
		  $arrPajak = array(3 => "HOTEL", 5 => "PARKIR", 8 => "RESTORAN");
		 */

		$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
		$res = mysqli_query($this->Conn, $sql);

		while ($row = mysqli_fetch_assoc($res)) {
			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			$arrFunction[$row["CPM_NO"]] = "fPatdaBerkasPajak" . $row["CPM_NO"];
		}

		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
		}

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			if ($idpjk == 7) {
				// data reklame multi op
				$query .= "(SELECT pjk.CPM_ID
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND (gw.payment_flag='0' OR gw.payment_flag is NULL) GROUP BY pjk.CPM_ID) UNION";
			} else {
				$query .= "(SELECT pjk.CPM_ID
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND (gw.payment_flag='0' OR gw.payment_flag is NULL) GROUP BY pjk.CPM_ID) UNION";
			}
		}

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak";

		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		$recordCount = $row['RecordCount'];

		#query select list data
		// var_dump($idpjk);die;
		$query = "SELECT pajak.* FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			if ($idpjk == 7) {
				// data reklame multi op
				$query .= "(SELECT pjk.CPM_ID, 7 as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR, prf.CPM_NAMA_OP as CPM_NAMA_OP, prf.CPM_NAMA_WP as CPM_NAMA_WP,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS, pjk.CPM_TOTAL_PAJAK, pjk.CPM_PIUTANG
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where}  AND (gw.payment_flag='0' OR gw.payment_flag is NULL) GROUP BY pjk.CPM_ID) UNION";
			}elseif ($idpjk == 6) {
				
				$query .= "(SELECT pjk.CPM_ID, 6 as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION, atr.CPM_ATR_TAHUN_PAJAK AS CPM_TAHUN_PAJAK,
						CONCAT(DATE_FORMAT(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(atr.CPM_ATR_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						 pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR, prf.CPM_NAMA_OP as CPM_NAMA_OP, prf.CPM_NAMA_WP as CPM_NAMA_WP,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS, pjk.CPM_TOTAL_PAJAK, pjk.CPM_PIUTANG
						FROM PATDA_JALAN_DOC pjk
						INNER JOIN PATDA_JALAN_DOC_ATR atr ON atr.CPM_ATR_JALAN_ID=pjk.CPM_ID
						INNER JOIN PATDA_JALAN_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_JALAN_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_JALAN_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where}  AND (gw.payment_flag='0' OR gw.payment_flag is NULL) GROUP BY pjk.CPM_ID) UNION";
			} else {
				$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR, prf.CPM_NAMA_OP as CPM_NAMA_OP, prf.CPM_NAMA_WP as CPM_NAMA_WP,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS, pjk.CPM_TOTAL_PAJAK, pjk.CPM_PIUTANG
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where}  AND (gw.payment_flag='0' OR gw.payment_flag is NULL) GROUP BY pjk.CPM_ID) UNION";
			}
		}
		

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		// var_dump($query);die;
		$result = mysqli_query($this->Conn, $query);

		$rows = array();
		$no_sptpd = array();
		$no_sptpds = array();
		$payment_flag = array();
		$id_switchings = array();
		$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
		while ($row = mysqli_fetch_assoc($result)) {
			$row = array_merge($row, array("ROWNUM" => ++$no));
			$no_sptpd[$row['CPM_NO_SPTPD']] = $row['CPM_NO_SPTPD'];

			///add by dedi  =========================================
			$id_switchings[] = "'" . $row['CPM_ID'] . "'";
			// =====================================================

			$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

			$func = $arrFunction[$row['CPM_JENIS_PAJAK']];
			if ($row['CPM_PIUTANG'] == 1) {
				$func = 'fPatdaLaporPiutang' . $row['CPM_JENIS_PAJAK'];
			}
			$base64 = "a={$this->_a}&m={$this->_m}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
			$url = "main.php?param=" . base64_encode($base64);

			$row['CPM_NO_SPTPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SPTPD']}</a>";
			$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
	
			$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
			$row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
			$row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
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

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd); // edited by v
		mysqli_select_db($Conn_gw, $dbName); // edited by v

		$res = mysqli_query($Conn_gw, "SELECT sptpd,payment_code,expired_date,payment_flag from SIMPATDA_GW WHERE sptpd IN('" . implode("','", $no_sptpd) . "')");
		while ($gw = mysqli_fetch_assoc($res)) {
			$no_sptpd[$gw['sptpd']] = $gw['payment_code'];
			$no_sptpds[$gw['sptpd']] = $gw['expired_date'];
			$payment_flag[$gw['sptpd']] = $gw['payment_flag'];
		}

		///add by dedi  =========================================
		$datetimenow = date('Y-m-d H:i:s');
		$id_switchings = implode(',', $id_switchings);
		$res = mysqli_query($Conn_gw, "SELECT * from simpatda_qris WHERE id_switching IN($id_switchings) AND expired_date_time>='$datetimenow' ORDER BY expired_date_time ASC");
		$id_switchings = [];
		while ($gw = mysqli_fetch_assoc($res)) $id_switchings[$gw['id_switching']] = true;
		// =====================================================

		// masukkan kode bayar by no sptpd ke data
		foreach ($rows as $i => $row) {
			$nop 	= strip_tags($row['CPM_NO_SPTPD']);
			$kode_bayar = $no_sptpd[$nop];
			$tipe = substr($kode_bayar,-2,2);
			$expired_date = $no_sptpds[$nop];
			$total_pajak = (float)str_replace(',', '', $row['CPM_TOTAL_PAJAK']);
			$status_bayar = $payment_flag[$nop];

			// if ($_SERVER['HTTP_USER_AGENT'] == 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0') {
			// 	print_r($_POST['CPM_NILAI_PAJAK']);exit;
			// }

			// if (isset($_POST['CPM_NILAI_PAJAK'])) {
				$id 	= $row['CPM_ID'];
				$adaQRIS = isset($id_switchings[$id]) ? true : false;
				if ($status_bayar >= 1) {
					$rows[$i]['QRIS'] = '<span data-status="' . $status_bayar . '"></span>';
				} elseif ($adaQRIS) {
					$rows[$i]['QRIS'] = '<img src="./image/icon/qr.png" width="20px" height="20px">';
				} else {
					$stop_date = "$expired_date 23:59:59";
					$stop_date = date('Y-m-d H:i:s', strtotime($stop_date . ' -24 hours'));
					$is_exp = strtotime("$expired_date 23:59:59") > strtotime($datetimenow) ? 1 : 0;

					$sha1 = sha1('#9PAJAK#PESIBAR#' . $id . '#' . date('Ymd') . '#');
					$param 	= "'$id','$sha1'";
					if (strtotime("$expired_date 23:59:59") > strtotime($datetimenow) && $total_pajak > 0 && $total_pajak <= 10000000) {
						$rows[$i]['QRIS'] = '<div id="divico' . $id . '">
												<a href="javascript:;" onclick="getQRCode(' . $param . ')" title="Generate QRIS">
													<img id="idico' . $id . '" src="./image/icon/qr_disable.png" width="20px" height="20px">
												</a>
											</div>';
					} else {
						$stop_date = "$expired_date 23:59:59";
	
						$stop_date = new DateTime($stop_date);
						$stop_date->modify('+28 day');            //   <---- Tambah 28 hari
						$stop_date = $stop_date->format('Y-m-d H:i:s');
	
						$stop_date = date('Y-m-d H:i:s', strtotime($stop_date . ' -24 hours'));
						$sha1 = sha1('#9PAJAK#PESIBAR#' . $id . '#' . date('Ymd') . '#');
						$param 	= "'$id','$sha1'";
	
						if ($total_pajak > 0 && $total_pajak <= 10000000) {
							$rows[$i]['QRIS'] = '<div id="divico' . $id . '">
													<a href="javascript:;" onclick="getQRCode(' . $param . ')" title="Generate QRIS">
														<img id="idico' . $id . '" src="./image/icon/qr_disable.png" width="20px" height="20px">
													</a>
												</div>';
						}
					}
				}
			// }
			$rows[$i]['kode_verifikasi'] = $kode_bayar;
			$rows[$i]['expired_date'] = $expired_date;
		}

		// echo '<pre>';
		// print_r($rows);exit;
		$jTableResult = array();
		// $jTableResult['code'] = "SELECT sptpd,payment_code from SIMPATDA_GW WHERE sptpd IN('".implode("','", array_keys($no_sptpd))."')";
		$jTableResult['Result'] = "OK";
		// $jTableResult['q'] = $query;
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print $this->Json->encode($jTableResult);

		mysqli_close($this->Conn);
		mysqli_close($Conn_gw);
	}


	private function grid_data_pajak_bayar()
	{
		$wpid = $_SESSION['npwpd'];

		if ($_SESSION['role'] == 'rmPatdaWp') {
			$where = "tr.CPM_TRAN_STATUS='5' && prf.CPM_NPWPD ='$wpid'";
		} else {
			$where = "tr.CPM_TRAN_STATUS='5'";
		}

		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		// tambahan
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ?
			" AND CPM_MASA_PAJAK = \"" . $_REQUEST['CPM_MASA_PAJAK'] . "\" " : "";
		// $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
		//$where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
		$where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NILAI_PAJAK']) && $_REQUEST['CPM_NILAI_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = '" . str_replace(array(',', '.'), '', $_REQUEST['CPM_NILAI_PAJAK']) . "' " : "";

		/*
		  $arrPajak = array(3 => "HOTEL", 5 => "PARKIR", 8 => "RESTORAN");
		 */

		$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
		$res = mysqli_query($this->Conn, $sql);

		while ($row = mysqli_fetch_assoc($res)) {
			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			$arrFunction[$row["CPM_NO"]] = "fPatdaBerkasPajak" . $row["CPM_NO"];
		}

		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
		}

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			if ($idpjk == 7) {
				// data reklame multi op
				$query .= "(SELECT pjk.CPM_ID
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND gw.payment_flag='1' GROUP BY pjk.CPM_ID) UNION";
			} else {
				$query .= "(SELECT pjk.CPM_ID
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND gw.payment_flag='1' GROUP BY pjk.CPM_ID) UNION";
			}
		}

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak";

		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		$recordCount = $row['RecordCount'];

		#query select list data
		$query = "SELECT pajak.* FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			if ($idpjk == 7) {
				// data reklame multi op
				$query .= "(SELECT pjk.CPM_ID, 7 as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR, prf.CPM_NAMA_OP as CPM_NAMA_OP, prf.CPM_NAMA_WP as CPM_NAMA_WP,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS, pjk.CPM_TOTAL_PAJAK
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND gw.payment_flag='1' GROUP BY pjk.CPM_ID) UNION";
			} else {
				$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR, prf.CPM_NAMA_OP as CPM_NAMA_OP, prf.CPM_NAMA_WP as CPM_NAMA_WP,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS, pjk.CPM_TOTAL_PAJAK
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} AND gw.payment_flag='1' GROUP BY pjk.CPM_ID) UNION";
			}
		}

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		$result = mysqli_query($this->Conn, $query);

		$rows = array();
		$no_sptpd = array();
		$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
		while ($row = mysqli_fetch_assoc($result)) {
			$row = array_merge($row, array("ROWNUM" => ++$no));
			$no_sptpd[$row['CPM_NO_SPTPD']] = $row['CPM_NO_SPTPD'];

			$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

			$base64 = "a={$this->_a}&m={$this->_m}&f={$arrFunction[$row['CPM_JENIS_PAJAK']]}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
			$url = "main.php?param=" . base64_encode($base64);

			$row['CPM_NO_SPTPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SPTPD']}</a>";
			$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
			$row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
			$row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
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

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd); // edited by v
		mysqli_select_db($Conn_gw, $dbName); // edited by v

		$res = mysqli_query($Conn_gw, "SELECT sptpd,payment_code,expired_date from SIMPATDA_GW WHERE sptpd IN('" . implode("','", $no_sptpd) . "')");
		while ($gw = mysqli_fetch_assoc($res)) {
			$no_sptpd[$gw['sptpd']] = $gw['payment_code'];
			$no_sptpds[$gw['sptpd']] = $gw['expired_date'];
		}

		// masukkan kode bayar by no sptpd ke data
		foreach ($rows as $i => $row) {
			$rows[$i]['kode_verifikasi'] = $no_sptpd[strip_tags($row['CPM_NO_SPTPD'])];
			$rows[$i]['expired_date'] = $no_sptpds[strip_tags($row['CPM_NO_SPTPD'])];
		}


		$jTableResult = array();
		// $jTableResult['code'] = "SELECT sptpd,payment_code from SIMPATDA_GW WHERE sptpd IN('".implode("','", array_keys($no_sptpd))."')";
		$jTableResult['Result'] = "OK";
		$jTableResult['q'] = $query;
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print $this->Json->encode($jTableResult);

		mysqli_close($this->Conn);
		mysqli_close($Conn_gw);
	}

	private function grid_data_sptpd_tahunan()
	{
		$where = "tr.CPM_TRAN_STATUS='5'";
		// var_dump($_REQUEST);exit();
		$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND pjk.CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
		//$where.= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
		$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
		$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

		/*
		  $arrPajak = array(3 => "HOTEL", 5 => "PARKIR", 8 => "RESTORAN");
		 */

		$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
		$res = mysqli_query($this->Conn, $sql);

		while ($row = mysqli_fetch_assoc($res)) {
			$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			$arrFunction[$row["CPM_NO"]] = "fPatdaBerkasPajak" . $row["CPM_NO"];
		}

		if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
		}

		#count utk pagging
		$query = "SELECT COUNT(*) AS RecordCount FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			$query .= "(SELECT pjk.CPM_ID
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						WHERE {$where} GROUP BY pjk.CPM_ID) UNION";
		}

		// data reklame multi op
		$query .= "(SELECT pjk.CPM_ID, 7 as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						WHERE {$where} GROUP BY prf.CPM_NPWPD,pjk.CPM_TAHUN_PAJAK) UNION";

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak";

		$result = mysqli_query($this->Conn, $query);
		$row = mysqli_fetch_assoc($result);
		$recordCount = $row['RecordCount'];

		#query select list data
		$query = "SELECT pajak.* FROM (";
		foreach ($arrPajak as $idpjk => $pjk) {
			$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						WHERE {$where} GROUP BY prf.CPM_NPWPD,pjk.CPM_TAHUN_PAJAK) UNION";
		}

		// data reklame multi op
		$query .= "(SELECT pjk.CPM_ID, 7 as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_REKLAME_DOC pjk
						INNER JOIN PATDA_REKLAME_DOC_ATR atr ON atr.CPM_ATR_REKLAME_ID=pjk.CPM_ID
						INNER JOIN PATDA_REKLAME_PROFIL prf ON atr.CPM_ATR_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_REKLAME_ID
						WHERE {$where} GROUP BY prf.CPM_NPWPD,pjk.CPM_TAHUN_PAJAK) UNION";

		$query = substr($query, 0, strlen($query) - 5);
		$query .= ") as pajak ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
		// echo $query;exit();
		$result = mysqli_query($this->Conn, $query);

		$rows = array();
		$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
		while ($row = mysqli_fetch_assoc($result)) {
			$row = array_merge($row, array("ROWNUM" => ++$no));

			$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

			$base64 = "a={$this->_a}&m={$this->_m}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&mod={$this->_mod}&idtran={$row['CPM_TRAN_ID']}&print={$row['READ']}&tipe={$row['CPM_JENIS_PAJAK']}&npwpd={$row['CPM_NPWPD']}&tahun_pajak={$row['CPM_TAHUN_PAJAK']}&total_pajak={$row['TOTAL_PAJAK']}";
			$url = $base64;

			$row['CPM_NO_SPTPD'] = "{$row['CPM_NO_SPTPD']}";
			$row['PRINT_SPTPD'] = "
						<button type=\"button\" onclick=\"javascript:download_pdf_tahunan('{$id}','function/PATDA-V1/svc-download-sptpd-tahunan.pdf.php?" . $url . "')\">Print SPTPD Tahunan</button> ";
			$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[$row['CPM_JENIS_PAJAK']];
			$row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
			$rows[] = $row;
		}

		$jTableResult = array();
		// $jTableResult['arrPajak'] = $arrPajak;
		$jTableResult['Result'] = "OK";
		$jTableResult['q'] = $query;
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print $this->Json->encode($jTableResult);

		mysqli_close($this->Conn);
	}

	public function print_buktiterima()
	{

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
						<td align=\"center\" width=\"20%\">

						</td>
						<!--COP-->
						<td align=\"center\" width=\"80%\" colspan=\"1\">
							<br>
							" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>BADAN PENGELOLAAN PAJAK DAN RETRIBUSI DAERAH<br/><br/>
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
									<td>" . strtoupper($KOTA) . ", " . date("d-m-Y") . "
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

		require_once("../../../inc/payment/tcpdf/tcpdf.php");
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(2, 4, 2);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('P', 'A6');
		$pdf->writeHTML($html, true, false, false, false, '');
		$pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 4, 7, 16, '', '', '', '', false, 300, '', false);
		$pdf->SetAlpha(0.3);

		$pdf->Output('bukti_penerimaan.pdf', 'I');
	}

	public function download_sptpd_tahunan()
	{

		$this->_id = $this->CPM_ID;

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

		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbTable = $config['PATDA_TABLE'];
		$dbUser = $config['PATDA_USERNAME'];
		$day = $config['TENGGAT_WAKTU'];
		$area_code = $config['KODE_AREA'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, true);

		mysqli_select_db($dbName, $Conn_gw);
		// echo $Conn_gw;

		if ($_REQUEST['tipe'] == 1) {
			$data = array(1 => "11", 2 => "31");
		} else if ($_REQUEST['tipe'] == 2) {
			$data = array(1 => "6", 2 => "26");
		} else if ($_REQUEST['tipe'] == 3) {
			$data = array(1 => "4", 2 => "24");
		} else if ($_REQUEST['tipe'] == 4) {
			$data = array(1 => "9", 2 => "29");
		} else if ($_REQUEST['tipe'] == 5) {
			$data = array(1 => "30", 2 => "10");
		} else if ($_REQUEST['tipe'] == 6) {
			$data = array(1 => "8", 2 => "28");
		} else if ($_REQUEST['tipe'] == 7) {
			$data = array(1 => "27", 2 => "7");
		} else if ($_REQUEST['tipe'] == 8) {
			$data = array(1 => "25", 2 => "5");
		} else if ($_REQUEST['tipe'] == 9) {
			$data = array(1 => "12", 2 => "32");
		}

		// echo $data[1]."-".$data[2];
		$query = sprintf(
			"select sum(simpatda_dibayar) as total, sum(patda_denda) as denda from SIMPATDA_GW WHERE npwpd = '%s' AND simpatda_tahun_pajak='%s' AND (simpatda_type = '%s' or simpatda_type = '%s' ) AND payment_flag=1",
			$_REQUEST['npwpd'],
			$_REQUEST['tahun_pajak'],
			$data[1],
			$data[2]
		);
		// echo $query;exit();
		$res = mysqli_query($Conn_gw, $query);
		$data_total = mysqli_fetch_array($res);

		$query2 = sprintf(
			"select sum(simpatda_dibayar) as total from SIMPATDA_GW WHERE npwpd = '%s' AND simpatda_tahun_pajak='%s' AND (simpatda_type = '%s' or simpatda_type = '%s' ) AND payment_flag=0",
			$_REQUEST['npwpd'],
			$_REQUEST['tahun_pajak'],
			$data[1],
			$data[2]
		);
		$res2 = mysqli_query($Conn_gw, $query2);
		$data_total_belum_bayar = mysqli_fetch_array($res2);




		$html = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td valign=\"top\" align=\"center\" colspan=\"3\">
                                        <table border=\"0\" width=\"310\">
                                            <tr>
                                                <td width=\"700\" align=\"center\">
                                                    <b><font size=\"13\">
                                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "
                                                    </font><br/>
                                                    <font size=\"15\">
                                                      " . strtoupper($NAMA_PENGELOLA) . "<br/>
                                                    </font></b>
                                                    Jl. Hl. Mochtar No. 1 Gunung Sugih Kabupaten Lampung Tengah<br>
													Telp. (0725) 639808 Fax. (0275) 529809
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
                        	<B>SURAT PEMBERITAHUAN PAJAK DAERAH<BR>(SPTPD)<BR> {JENIS PAJAK}</B><BR> SPTPD TAHUNAN
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" align=\"center\">
                        	<table width=\"200\">
	                        	<tr>
	                        		<td width=\"250\"></td>
	                        		<td align=\"left\" width=\"100\">Tahun Pajak</td>
	                        		<td align=\"left\">: ..............</td>
	                        	</tr>
	                        	<tr>
	                        		<td width=\"250\"></td>
	                        		<td align=\"left\" width=\"100\">NOMOR</td>
	                        		<td align=\"left\">: ..............</td>
	                        	</tr>
                        	</table>
                        </td>
                    </tr>

                    <tr>
                        <td width=\"710\" align=\"center\">
                        	PENGUSAHA KENA PAJAK
                        	<br>
                        	<table>
                        		<tr>
                        			<td width=\"15\" align=\"left\">1.</td>
                        			<td width=\"150\" align=\"left\">NPWPD</td>
                        			<td width=\"199\" align=\"left\">:</td>
                        		</tr>
                        		<tr>
                        			<td width=\"15\" align=\"left\">2.</td>
                        			<td width=\"150\" align=\"left\">Nama PKP</td>
                        			<td width=\"199\" align=\"left\">:</td>
                        		</tr>
                        		<tr>
                        			<td width=\"15\" align=\"left\">3.</td>
                        			<td width=\"150\" align=\"left\">Jenis Usaha</td>
                        			<td width=\"199\" align=\"left\">:</td>
                        		</tr>
                        		<tr>
                        			<td width=\"15\" align=\"left\">4.</td>
                        			<td width=\"150\" align=\"left\">Alamat</td>
                        			<td width=\"199\" align=\"left\">:</td>
                        		</tr>
                        		<tr>
                        			<td width=\"15\" align=\"left\">5.</td>
                        			<td width=\"150\" align=\"left\">No. Telp</td>
                        			<td width=\"199\" align=\"left\">:</td>
                        		</tr>
                        	</table>
                        </td>
                    </tr>

                    <tr>
                        <td width=\"710\" align=\"left\">
                        	Jumlah yang harus di bayar
                        	<br>
                        	<table>
                        		<tr>
                        			<td width=\"15\">a.</td>
                        			<td width=\"250\">Jumlah Pokok Pajak Triwulan I</td>
                        			<td width=\"280\">
                        			..........................................................................
                        			</td>
                        			<td>= Rp.........................</td>
                        		</tr>

                        		<tr>
                        			<td width=\"15\">b.</td>
                        			<td width=\"250\">Jumlah Pokok Pajak Triwulan II</td>
                        			<td width=\"280\">
                        			..........................................................................
                        			</td>
                        			<td>= Rp.........................</td>
                        		</tr>

                        		<tr>
                        			<td width=\"15\">c.</td>
                        			<td width=\"250\">Jumlah Pokok Pajak Triwulan III</td>
                        			<td width=\"280\">
                        			..........................................................................
                        			</td>
                        			<td>= Rp.........................</td>
                        		</tr>

                        		<tr>
                        			<td width=\"15\">d.</td>
                        			<td width=\"250\">Jumlah Pokok Pajak Triwulan IV</td>
                        			<td width=\"280\">
                        			..........................................................................
                        			</td>
                        			<td>= Rp.........................</td>
                        		</tr>

                        		<tr>
                        			<td width=\"15\"></td>
                        			<td width=\"250\">Jumlah Pokok Pajak Tahunan Final</td>
                        			<td width=\"280\">

                        			</td>
                        			<td>= Rp." . number_format($data_total_belum_bayar['total']) . "<br/><br/><br/></td>
                        		</tr>

                        		<tr>
                        			<td width=\"15\"></td>
                        			<td width=\"250\">Jumlah Pokok Pajak yang telah dibayar</td>
                        			<td width=\"280\">

                        			</td>
                        			<td>= Rp. " . number_format($data_total['total']) . "</td>
                        		</tr>


                        		<tr>
                        			<td width=\"15\"></td>
                        			<td width=\"250\">Jumlah pokok pajak yang belum dibayar</td>
                        			<td width=\"280\">

                        			</td>
                        			<td>= Rp. " . number_format($data_total_belum_bayar['total']) . " </td>
                        		</tr>
                        	</table>
                        </td>
                    </tr>

                    <tr>
                    	<td>
                    	Sanksi Administrasi<br>

                    	<table>
                        	<tr>
                        		<td width=\"15\">a.</td>
                        		<td width=\"250\">Jumlah Denda Keseluruhan</td>
                        		<td width=\"280\">

                        		</td>
                        		<td>= Rp." . number_format($data_total['denda']) . "</td>
                        	</tr>
                        	<tr>
                        		<td width=\"15\">b.</td>
                        		<td width=\"250\">Jumlah Pokok Pajak + Denda Keseluruhan</td>
                        		<td width=\"280\">

                        		</td>
                        		<td>= Rp." . number_format($data_total['total'] + $data_total['denda']) . "</td>
                        	</tr>
                    	</table>

                    	</td>
                    </tr>
                    <tr>
                    	<td>
	                    	<table>
		                    	<tr>
		                    		<td></td>
		                    		<td align=\"center\">
		                    		<br><br>
			                    		Gunung Sugih, .............................<br>
			                    		<b>Kepala Badan Pengelola Pajak
			                    		<br> dan Retribusi Daerah
			                    		<br> Kabupaten Lampung Tengah</b>

			                    		<br><br><br><br>
			                    		<u>..................................................</u><br>
			                    		<u>NIP..........................................</u>
			                    		<br>
		                    		</td>
		                    	</tr>
	                    	</table>
                    	</td>
                    </tr>
                </table>";

		require_once("../../inc/payment/tcpdf/tcpdf.php");
		// echo "string";exit();

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('-');
		$pdf->SetSubject('-');
		$pdf->SetKeywords('-');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 5, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('P', 'F4');
		$pdf->writeHTML($html, true, false, false, false, '');
		// $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 13, 6, 12, '', '', '', '', false, 300, '', false);
		$pdf->Image('../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 14, 7, 10, '', '', '', '', false, 300, '', false);
		$pdf->SetAlpha(0.3);

		$pdf->Output('bukti_penerimaan.pdf', 'I');
	}
	public function print_disposisi()
	{
		$this->_id = $this->CPM_ID;
		$DATA = $this->get_berkas();

		$radio_lampiran[1] = strpos($DATA['CPM_LAMPIRAN'], "1") === false ? "[_]" : "[x]";
		// $radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "[_]" : "[x]";
		// $radio_lampiran[3] = strpos($DATA['CPM_LAMPIRAN'], "3") === false ? "[_]" : "[x]";
		$radio_lampiran[8] = strpos($DATA['CPM_LAMPIRAN'], "8") === false ? "[_]" : "[x]";
		$lampiran_tambahan = '';

		//jika jenis pajak penerangan jalan maka ada tambahan lampiran
		if ($DATA['CPM_JENIS_PAJAK'] == 1) {
			$radio_lampiran[5] = strpos($DATA['CPM_LAMPIRAN'], "5") === false ? "[_]" : "[x]";
			$lampiran_tambahan1 = "<tr><td>{$radio_lampiran[5]} Rekapitulasi Pemanfaatan Air</td></tr>";

			$radio_lampiran[6] = strpos($DATA['CPM_LAMPIRAN'], "6") === false ? "[_]" : "[x]";
			$lampiran_tambahan2 = "<tr><td>{$radio_lampiran[6]} Fotocopy SIPA, KTP, SIUP</td></tr>";

			$radio_lampiran[7] = strpos($DATA['CPM_LAMPIRAN'], "7") === false ? "[_]" : "[x]";
			$lampiran_tambahan3 = "<tr><td>{$radio_lampiran[7]} Foto Water Meter</td></tr>";
		}

		if ($DATA['CPM_JENIS_PAJAK'] != 1 && $DATA['CPM_JENIS_PAJAK'] != 6) {
			$radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "[_]" : "[x]";
			$lampiran_tambahan4 = "<tr><td>{$radio_lampiran[2]} Laporan Omzet Harian</td></tr>";

			$radio_lampiran[3] = strpos($DATA['CPM_LAMPIRAN'], "3") === false ? "[_]" : "[x]";
			$lampiran_tambahan5 = "<tr><td>{$radio_lampiran[3]} Bon Bill</td></tr>";
		}

		//jika jenis pajak penerangan jalan maka ada tambahan lampiran
		if ($DATA['CPM_JENIS_PAJAK'] == 6) {
			$radio_lampiran[4] = strpos($DATA['CPM_LAMPIRAN'], "4") === false ? "[_]" : "[x]";
			$lampiran_tambahan = "<tr><td>{$radio_lampiran[4]} Rekapitulasi Kwh Penerangan Jalan</td></tr>";
		}

		$config = $this->get_config_value($this->_a);
		$LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
		$JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
		$NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
		$NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
		$JALAN = $config['ALAMAT_JALAN'];
		$KOTA = $config['ALAMAT_KOTA'];
		$PROVINSI = $config['ALAMAT_PROVINSI'];
		$KODE_POS = $config['ALAMAT_KODE_POS'];

		$VERIFIKASI_NIP = $config['BAG_VERIFIKASI_NIP'];
		$VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
		$VERIFIKASI_NAMA = str_pad($VERIFIKASI_NAMA, 40, ".", STR_PAD_RIGHT);
		$VERIFIKASI_NAMA = str_replace(".", "&nbsp;", $VERIFIKASI_NAMA);
		$html = "<table border=\"1\" cellpadding=\"3\">
					<tr>
						<td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
						<td align=\"center\" width=\"60%\">
							<!-- <font size=\"+4\"> --> " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>{$NAMA_PENGELOLA}<br />
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
							<table border=\"0\" cellpadding=\"2\" cellspacing=\"4\">
								<tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">DISPOSISI PAJAK " . strtoupper($this->arr_pajak[$DATA['CPM_JENIS_PAJAK']]) . "<br /></font></td></tr>
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
											<tr><td>{$radio_lampiran[8]} NPWP/NPWPD</td></tr>
											{$lampiran_tambahan1}
											{$lampiran_tambahan2}
											{$lampiran_tambahan3}
											{$lampiran_tambahan4}
											{$lampiran_tambahan5}
											{$lampiran_tambahan}
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
									<td><table border=\"0\" cellpadding=\"12\">
											<tr>
												<td width=\"351\" height=\"120\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											</td>
											<td width=\"351\" height=\"120\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<table border=\"0\" cellspacing=\"3\" width=\"250\">
													<tr><td align=\"center\">Petugas Verifikasi</td></tr>
													<tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr>
													<tr><td><br><br></td></tr>
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
		ob_clean();
		require_once("../../../inc/payment/tcpdf/tcpdf.php");
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 14, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('P', 'A4');
		$pdf->writeHTML($html, true, false, false, false, '');
		$pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 15, 17, 15, '', '', '', '', false, 300, '', false);
		$pdf->SetAlpha(0.3);

		$pdf->Output('disposisi.pdf', 'I');
	}

	public function read_dokumen()
	{
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

	public function read_dokumen_notif()
	{
		$wpid = $_SESSION['npwpd'];

		if ($_SESSION['role'] == 'rmPatdaWp') {
			$where = "AND CPM_NPWPD ='$wpid'";
			$where1 = "AND gw.npwpd ='$wpid'";
		} else {
			$where = "";
			$where1 = "";
		}


		$arr_tab = explode(";", $_POST['tab']);

		$notif = array();
		$notif['masuk'] = 0;
		$notif['diterima'] = 0;
		$notif['disetujui'] = 0;
		$notif['sbayar'] = 0;

		if (in_array("masuk", $arr_tab)) {
			$q = "SELECT count(CPM_ID) as total FROM PATDA_BERKAS WHERE CPM_STATUS='0' AND CPM_SPTPD='1' AND
					(CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR CPM_TRAN_READ is null) {$where}";
			$result = mysqli_query($this->Conn, $q);
			if ($data = mysqli_fetch_assoc($result))
				$notif['masuk'] = (int) $data['total'];
		}
		if (in_array("diterima", $arr_tab)) {
			$q = "SELECT count(CPM_ID) as total FROM PATDA_BERKAS WHERE CPM_STATUS='1' AND CPM_SPTPD='1' AND
					(CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR CPM_TRAN_READ is null) {$where}";
			$result = mysqli_query($this->Conn, $q);
			if ($data = mysqli_fetch_assoc($result))
				$notif['diterima'] = (int) $data['total'];
		}
		if (in_array("disetujui", $arr_tab)) {

			$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
			$res = mysqli_query($this->Conn, $sql);

			while ($row = mysqli_fetch_assoc($res)) {
				$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			}

			$where = "tr.CPM_TRAN_STATUS='5' AND (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND (gw.payment_flag='0' OR gw.payment_flag is NULL)  {$where1}";

			$query = "SELECT count(pajak.CPM_ID) as total FROM (";
			foreach ($arrPajak as $idpjk => $pjk) {
				$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION, pjk.CPM_MASA_PAJAK, pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						pjk.CPM_TGL_LAPOR as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where} ) UNION";
			}
			$query = substr($query, 0, strlen($query) - 5);
			$query .= ") as pajak";
			$result = mysqli_query($this->Conn, $query);
			if ($data = mysqli_fetch_assoc($result))
				$notif['disetujui'] = (int) $data['total'];
		}

		if (in_array("sbayar", $arr_tab)) {

			$sql = "SELECT * FROM PATDA_JENIS_PAJAK";
			$res = mysqli_query($this->Conn, $sql);

			while ($row = mysqli_fetch_assoc($res)) {
				$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			}

			$where = "tr.CPM_TRAN_STATUS='5' AND (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND gw.payment_flag='1'  {$where1}";

			$query = "SELECT count(pajak.CPM_ID) as total FROM (";
			foreach ($arrPajak as $idpjk => $pjk) {
				$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION, pjk.CPM_MASA_PAJAK, pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						pjk.CPM_TGL_LAPOR as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_{$pjk}_DOC pjk
						INNER JOIN PATDA_{$pjk}_PROFIL prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						INNER JOIN SIMPATDA_GW gw ON pjk.CPM_ID = gw.id_switching
						WHERE {$where}) UNION";
			}
			$query = substr($query, 0, strlen($query) - 5);
			$query .= ") as pajak";
			$result = mysqli_query($this->Conn, $query);
			if ($data = mysqli_fetch_assoc($result))
				$notif['sbayar'] = (int) $data['total'];
		}
		echo $this->Json->encode($notif);
	}


	function download_berkas_sptpd_xls()
	{

// 		ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// var_dump($this->_i);die;

		if ($this->_i == 1) {
			$where = "CPM_STATUS='0' AND CPM_SPTPD='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPTPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else if ($this->_i == 2) {
			$where = "CPM_STATUS='1' AND CPM_SPTPD='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND CPM_NO_SPTPD like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPTPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else {

			// // if ($this->_i == 3) {
			// // 	$where = "tr.CPM_TRAN_STATUS='5' AND gw.payment_flag = 0";
			// // }elseif ($this->_i == 6) {
			// // 	$where = "tr.CPM_TRAN_STATUS='5' AND gw.payment_flag = 1";
			// // }else{
			// $where = "tr.CPM_TRAN_STATUS='5'";
			// // }
			// $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			// $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND pjk.CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			// $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND prf.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			// $where .= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND pjk.CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
			// $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
			// 		STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			// $where .= (isset($_REQUEST['CPM_NILAI_PAJAK']) && $_REQUEST['CPM_NILAI_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = '" . str_replace(array(',', '.'), '', $_REQUEST['CPM_NILAI_PAJAK']) . "' " : "";
			// $where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND pjk.CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";
			// $sql = "SELECT * FROM {$this->PATDA_JENIS_PAJAK}";
			// $res = mysqli_query($this->Conn, $sql);

			// while ($row = mysqli_fetch_assoc($res)) {
			// 	$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
			// 	$arrFunction[$row["CPM_NO"]] = "fPatda{$this->MODULE_ID}Pelayanan" . $row["CPM_NO"];
			// }

			// if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
			// 	$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
			// }

			// #query select list data
			// $query = "SELECT pajak.* FROM (";
			// foreach ($arrPajak as $idpjk => $pjk) {
			// 	$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID,pjk.CPM_TOTAL_PAJAK, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
			// 			CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
			// 			pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
			// 			STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
			// 			FROM PATDA_{$pjk}_DOC{$this->SUFIKS} pjk
			// 			INNER JOIN PATDA_{$pjk}_PROFIL{$this->SUFIKS} prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
			// 			INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN{$this->SUFIKS} tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
			// 			WHERE {$where} GROUP BY pjk.CPM_ID) UNION";
			// }
			// $query = substr($query, 0, strlen($query) - 5);
			// $query .= ") as pajak ORDER BY 1";

			if ($this->_i == 3) {
				$where = "tr.CPM_TRAN_STATUS='5' AND gw.payment_flag = 0";
			}elseif ($this->_i == 6) {
				$where = "tr.CPM_TRAN_STATUS='5' AND gw.payment_flag = 1";
			}else{
			$where = "tr.CPM_TRAN_STATUS='5'";
			}

			// $where = "tr.CPM_TRAN_STATUS='5'";

			$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			$where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND pjk.CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND prf.CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where.= (isset($_REQUEST['CPM_NO_SPTPD']) && $_REQUEST['CPM_NO_SPTPD'] != "") ? " AND pjk.CPM_NO like \"{$_REQUEST['CPM_NO_SPTPD']}%\" " : "";
			$where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND pjk.CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";
			$where.= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			$sql = "SELECT * FROM {$this->PATDA_JENIS_PAJAK}";
			$res = mysqli_query($this->Conn, $sql);

			while ($row = mysqli_fetch_assoc($res)) {
				$arrPajak[$row["CPM_NO"]] = strtoupper($row["CPM_TABLE"]);
				$arrFunction[$row["CPM_NO"]] = "fPatda{$this->MODULE_ID}Pelayanan" . $row["CPM_NO"];
			}

			if ((isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "")) {
				$arrPajak = array($_REQUEST['CPM_JENIS_PAJAK'] => $arrPajak[$_REQUEST['CPM_JENIS_PAJAK']]);
			}

			#query select list data
			$query = "SELECT pajak.* FROM (";
			foreach ($arrPajak as $idpjk => $pjk) {
				$query .= "(SELECT pjk.CPM_ID, '{$idpjk}' as CPM_JENIS_PAJAK, tr.CPM_TRAN_ID, tr.CPM_TRAN_READ, pjk.CPM_VERSION,
						CONCAT(DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pjk.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
						pjk.CPM_TAHUN_PAJAK, pjk.CPM_NO as CPM_NO_SPTPD, prf.CPM_NPWPD, pjk.CPM_AUTHOR as CPM_AUTHOR,
						STR_TO_DATE(pjk.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_STATUS
						FROM PATDA_{$pjk}_DOC{$this->SUFIKS} pjk
						INNER JOIN PATDA_{$pjk}_PROFIL{$this->SUFIKS} prf ON pjk.CPM_ID_PROFIL=prf.CPM_ID
						INNER JOIN simpatda_gw gw ON pjk.CPM_ID= gw.id_switching
						INNER JOIN PATDA_{$pjk}_DOC_TRANMAIN{$this->SUFIKS} tr ON pjk.CPM_ID = tr.CPM_TRAN_{$pjk}_ID
						WHERE {$where} GROUP BY pjk.CPM_ID) UNION";
			}
			$query = substr($query, 0, strlen($query) - 5);
			$query.= ") as pajak ORDER BY 1";
		}

		// echo "<pre>" . print_r($_REQUEST, true) . "</pre>";die;
		// echo $query;
		// exit;
		$res = mysqli_query($this->Conn, $query);
		// Create new PHPExcel object

		$dataSize =  mysqli_num_rows($res); // calculate the size of your dataset;
// var_dump($dataSize);die;
		// Set a threshold for the dataset size
		$maxAllowedSize = 5000; // You can adjust this value

		if ($dataSize > $maxAllowedSize) {
			// Display an alert message
			echo "<script>alert('Data terlalu banyak, silahkan pilih flter yang lebih specifik.');</script>";
		} else {
			
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
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nomor SPTPD')
			->setCellValue('D1', 'Versi Dok')
			->setCellValue('E1', 'Masa Pajak')
			->setCellValue('F1', 'Tahun Pajak')
			->setCellValue('G1', 'NPWPD')
			->setCellValue('H1', 'Petugas')
			->setCellValue('I1', 'Tanggal Input');
		if ($this->_i != 3) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1', 'Status');
		}

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($res);

		while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_SPTPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_VERSION']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_MASA_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_AUTHOR']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_TGL_INPUT']);
			if ($this->_i == 1 || $this->_i == 2 || $this->_i == 5) {
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap");
			}
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('E4E4E4');

		for ($x = "A"; $x <= "L"; $x++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
		}
		ob_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	}

	function download_berkas_skpdkb_xls()
	{

		if ($this->_i == 1) {
			$where = "CPM_STATUS='0' AND CPM_SKPDKB='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SKPDKB']) && $_REQUEST['CPM_NO_SKPDKB'] != "") ? " AND CPM_NO_SKPDKB like \"{$_REQUEST['CPM_NO_SKPDKB']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SKPDKB, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else if ($this->_i == 2) {
			$where = "CPM_STATUS='1' AND CPM_SKPDKB='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SKPDKB']) && $_REQUEST['CPM_NO_SKPDKB'] != "") ? " AND CPM_NO_SKPDKB like \"{$_REQUEST['CPM_NO_SKPDKB']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SKPDKB, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else {
			$where = "tr.CPM_TRAN_STATUS='5'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SKPDKB']) && $_REQUEST['CPM_NO_SKPDKB'] != "") ? " AND CPM_NO_SKPDKB like \"{$_REQUEST['CPM_NO_SKPDKB']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
			#query select list data
			$query = "SELECT s.CPM_ID, s.CPM_JENIS_PAJAK, s.CPM_NO_SKPDKB, s.CPM_VERSION, s.CPM_MASA_PAJAK, s.CPM_TAHUN_PAJAK, s.CPM_TAMBAHAN,
					s.CPM_NPWPD, s.CPM_AUTHOR, STR_TO_DATE(s.CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_READ,
					tr.CPM_TRAN_ID, tr.CPM_TRAN_STATUS
					FROM {$this->PATDA_SKPDKB} s INNER JOIN {$this->PATDA_SKPDKB_TRANMAIN} tr ON
					s.CPM_ID = tr.CPM_TRAN_SKPDKB_ID WHERE {$where} ORDER BY 1";
		}

		//        echo "<pre>" . print_r($_REQUEST, true) . "</pre>";
		//        echo $query;
		//        exit;
		$res = mysqli_query($this->Conn, $query);
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
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nomor SKPDKB')
			->setCellValue('D1', 'Versi Dok')
			->setCellValue('E1', 'Masa Pajak')
			->setCellValue('F1', 'Tahun Pajak')
			->setCellValue('G1', 'NPWPD')
			->setCellValue('H1', 'Petugas')
			->setCellValue('I1', 'Tanggal Input');
		if ($this->_i != 3) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1', 'Status');
		}

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($res);

		while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_SKPDKB'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_VERSION']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_MASA_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_AUTHOR']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_TGL_INPUT']);
			if ($this->_i != 3) {
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap");
			}
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('E4E4E4');

		for ($x = "A"; $x <= "L"; $x++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
		}
		ob_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function download_berkas_stpd_xls()
	{

		if ($this->_i == 1) {
			$where = "CPM_STATUS='0' AND CPM_STPD='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_STPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else if ($this->_i == 2) {
			$where = "CPM_STATUS='1' AND CPM_STPD='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_STPD, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else {
			$where = "tr.CPM_TRAN_STATUS='5'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_STPD']) && $_REQUEST['CPM_NO_STPD'] != "") ? " AND CPM_NO_STPD like \"{$_REQUEST['CPM_NO_STPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#query select list data
			$query = "SELECT s.CPM_ID, s.CPM_JENIS_PAJAK, s.CPM_NO_STPD, s.CPM_VERSION, s.CPM_MASA_PAJAK, s.CPM_TAHUN_PAJAK,
					s.CPM_NPWPD, s.CPM_AUTHOR, STR_TO_DATE(s.CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_READ,
					tr.CPM_TRAN_ID, tr.CPM_TRAN_STATUS
					FROM {$this->PATDA_STPD} s INNER JOIN {$this->PATDA_STPD_TRANMAIN} tr ON
					s.CPM_ID = tr.CPM_TRAN_STPD_ID WHERE {$where} ORDER BY 1";
		}

		//        echo "<pre>" . print_r($_REQUEST, true) . "</pre>";
		//        echo $query;
		//        exit;
		$res = mysqli_query($this->Conn, $query);
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
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nomor STPD')
			->setCellValue('D1', 'Versi Dok')
			->setCellValue('E1', 'Masa Pajak')
			->setCellValue('F1', 'Tahun Pajak')
			->setCellValue('G1', 'NPWPD')
			->setCellValue('H1', 'Petugas')
			->setCellValue('I1', 'Tanggal Input');
		if ($this->_i != 3) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1', 'Status');
		}

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($res);

		while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_STPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_VERSION']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_MASA_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_AUTHOR']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_TGL_INPUT']);
			if ($this->_i != 3) {
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap");
			}
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('E4E4E4');

		for ($x = "A"; $x <= "L"; $x++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
		}
		ob_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function download_berkas_spa_xls()
	{

		if ($this->_i == 1) {
			$where = "CPM_STATUS='0' AND CPM_SPA='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPA']) && $_REQUEST['CPM_NO_SPA'] != "") ? " AND CPM_NO_SPA like \"{$_REQUEST['CPM_NO_SPA']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPA, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else if ($this->_i == 2) {
			$where = "CPM_STATUS='1' AND CPM_SPA='1'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPA']) && $_REQUEST['CPM_NO_SPA'] != "") ? " AND CPM_NO_SPA like \"{$_REQUEST['CPM_NO_SPA']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_BERKAS} WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT CPM_ID, CPM_JENIS_PAJAK, CPM_NO_SPA, CPM_VERSION,
					CONCAT(DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,1,6),'%y/%m/%d'),' - ', DATE_FORMAT(SUBSTR(CPM_MASA_PAJAK,7,6),'%y/%m/%d')) AS CPM_MASA_PAJAK,
					CPM_TAHUN_PAJAK, CPM_NPWPD, CPM_AUTHOR, CPM_STATUS, STR_TO_DATE(CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, CPM_TRAN_READ
					FROM {$this->PATDA_BERKAS} WHERE {$where}
					ORDER BY 1";
		} else {
			$where = "tr.CPM_TRAN_STATUS='5'";

			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(SUBSTRING(CPM_MASA_PAJAK,1,6),'%y%m%d')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK = \"{$_REQUEST['CPM_JENIS_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NO_SPA']) && $_REQUEST['CPM_NO_SPA'] != "") ? " AND CPM_NO_ANGSURAN like \"{$_REQUEST['CPM_NO_SPA']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
					STR_TO_DATE(CPM_TGL_INPUT,\"%Y-%m-%d\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#query select list data
			$query = "SELECT s.CPM_ID, s.CPM_JENIS_PAJAK, s.CPM_NO_ANGSURAN as CPM_NO_SPA, s.CPM_VERSION, concat(s.CPM_MASA_PAJAK1,' s.d ',CPM_MASA_PAJAK2) as CPM_MASA_PAJAK, s.CPM_TAHUN_PAJAK,
					s.CPM_NPWPD, s.CPM_AUTHOR, STR_TO_DATE(s.CPM_TGL_INPUT,'%d-%m-%Y') as CPM_TGL_INPUT, tr.CPM_TRAN_READ,
					tr.CPM_TRAN_ID, tr.CPM_TRAN_STATUS
					FROM PATDA_ANGSURAN_PLG s INNER JOIN PATDA_ANGSURAN_TRANMAIN_PLG tr ON
					s.CPM_ID = tr.CPM_TRAN_ANGSURAN_ID WHERE {$where} ORDER BY 1";
		}

		//        echo "<pre>" . print_r($_REQUEST, true) . "</pre>";
		//        echo $query;
		//        exit;
		$res = mysqli_query($this->Conn, $query);
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
			->setCellValue('B1', 'Jenis Pajak')
			->setCellValue('C1', 'Nomor SPA')
			->setCellValue('D1', 'Versi Dok')
			->setCellValue('E1', 'Masa Pajak')
			->setCellValue('F1', 'Tahun Pajak')
			->setCellValue('G1', 'NPWPD')
			->setCellValue('H1', 'Petugas')
			->setCellValue('I1', 'Tanggal Input');
		if ($this->_i != 3) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1', 'Status');
		}

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 2;
		$sumRows = mysqli_num_rows($res);

		while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $this->arr_pajak[$rowData['CPM_JENIS_PAJAK']]);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO_SPA'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_VERSION']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_MASA_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_AUTHOR']);
			$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_TGL_INPUT']);
			if ($this->_i != 3) {
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['CPM_STATUS'] == 1) ? "Lengkap" : "Belum Lengkap");
			}
			$row++;
		}


		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

		//----set style cell
		//style header
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
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

		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('E4E4E4');

		for ($x = "A"; $x <= "L"; $x++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
		}
		ob_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	private function get_list_nop($sptpd)
	{
		$res = mysqli_query($this->Conn, "SELECT prf.CPM_NPWPD, prf.CPM_NOP, prf.CPM_NAMA_OP
				FROM PATDA_REKLAME_DOC_ATR AS atr
				INNER JOIN PATDA_REKLAME_DOC AS doc ON doc.CPM_ID = atr.CPM_ATR_REKLAME_ID
				INNER JOIN PATDA_REKLAME_PROFIL AS prf ON prf.CPM_ID = atr.CPM_ATR_ID_PROFIL
				WHERE doc.CPM_NO='{$sptpd}'");
		$data = array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
}
