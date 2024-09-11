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
class LaporPajak extends Pajak
{
	#field
	#reklame

	public $id_pajak = 7;
	private $tax_periode = array("none", "harian", "mingguan", "bulanan", "tahunan");
	private $limitYear = 2;
	public $CPM_TYPE_PAJAK = 2;
	protected $CPM_DENDA_TERLAMBAT_LAP;

	public function __construct()
	{
		parent::__construct();
		$PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
		foreach ($PAJAK as $a => $b) {
			$this->$a = mysqli_escape_string($this->Conn, trim($b));
		}
		$this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
		if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
	}

	public function get_masa_pajak($masa = 0, $nilai = 0, $n = 0)
	{
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

	public function get_pajak($npwpd = '', $nop = '')
	{
		$Op = new ObjekPajak();
		$arr_rekening = $this->getRekening("4.1.01.09");
		$pajak_atr = array();
		$list_nop = array();

		$query = "SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
			FROM PATDA_REKLAME_DOC AS DOC WHERE DOC.CPM_ID = '{$this->_id}' LIMIT 0,1";

		$result = mysqli_query($this->Conn, $query);
		$pajak = $this->get_field_array($result);

		//if new entry
		if (empty($pajak['CPM_ID'])) {
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
				'CPM_ATR_PANJANG' => '',
				'CPM_ATR_LEBAR' => '',
				'CPM_ATR_MUKA' => '',
				'CPM_ATR_JARI' => '',
				'CPM_ATR_JUMLAH' => '',
				'CPM_ATR_BATAS_AWAL' => '',
				'CPM_ATR_BATAS_AKHIR' => '',
				'CPM_ATR_BIAYA' => '',
				'CPM_ATR_HARGA' => '',
				// 'CPM_ATR_HARGA_DASAR_UK' => '',
				// 'CPM_ATR_HARGA_DASAR_TIN' => '',
				'CPM_ATR_TOTAL' => '',
				'CPM_ATR_REKENING' => '',
				'CPM_ATR_TARIF' => '',
				'CPM_ATR_JUMLAH_TAHUN' => '',
				'CPM_ATR_JUMLAH_MINGGU' => '',
				'CPM_ATR_JUMLAH_BULAN' => '',
				'CPM_ATR_JUMLAH_HARI' => '',
				'CPM_ATR_TYPE_MASA' => '',
				'CPM_ATR_KAWASAN' => '',
				'CPM_ATR_JALAN' => '',
				'CPM_ATR_SUDUT_PANDANG' => '',
				'CPM_ATR_PERHITUNGAN' => '',
				'CPM_ATR_NJOP' => '',
				'CPM_ATR_NILAI_STRATEGIS' => '',
				'CPM_CEK_PIHAK_KETIGA' => '',
				'CPM_NILAI_PIHAK_KETIGA' => '',
				'type_masa' => '',
				'nmrek' => '',
				'CPM_ATR_JAM' => ''
			);
			$pajak_atr[] = $atr;
			// $pajak_atr[] = $atr;
			// $pajak_atr[] = $atr;

			$list_nop = $Op->get_list_nop($npwpd);
		} else { //if data available
			// $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
			$query = "SELECT atr.CPM_ATR_ID, atr.CPM_ATR_ID_PROFIL,atr.CPM_ATR_JENIS, atr.CPM_ATR_JUDUL, atr.CPM_ATR_LOKASI, atr.CPM_ATR_PANJANG, atr.CPM_ATR_LEBAR,
			atr.CPM_ATR_MUKA, atr.CPM_ATR_JARI, atr.CPM_ATR_JUMLAH, atr.CPM_ATR_BATAS_AWAL, atr.CPM_ATR_BATAS_AKHIR, atr.CPM_ATR_BIAYA, atr.CPM_ATR_HARGA, atr.CPM_ATR_TOTAL,
			atr.CPM_ATR_REKENING, atr.CPM_ATR_TYPE_MASA, atr.CPM_ATR_KAWASAN, atr.CPM_ATR_JALAN, atr.CPM_ATR_SUDUT_PANDANG, atr.CPM_ATR_PERHITUNGAN,
			atr.CPM_CEK_PIHAK_KETIGA, atr.CPM_NILAI_PIHAK_KETIGA,
			atr.CPM_ATR_NJOP, atr.CPM_ATR_NILAI_STRATEGIS,
			per.nmrek ,atr.CPM_ATR_TARIF, atr.CPM_ATR_JUMLAH_TAHUN,  atr.CPM_ATR_JUMLAH_BULAN,  atr.CPM_ATR_JUMLAH_MINGGU, atr.CPM_ATR_JUMLAH_HARI, per.type_masa, per.nmrek ,prf.CPM_NPWPD, prf.CPM_NOP, prf.CPM_NAMA_OP, prf.CPM_ALAMAT_OP,kel.CPM_KELURAHAN AS CPM_KELURAHAN_OP, kec.CPM_KECAMATAN AS CPM_KECAMATAN_OP,
			atr.CPM_ATR_SISI, atr.CPM_ATR_TINGGI, atr.CPM_ATR_HARGA_DASAR_UK, atr.CPM_ATR_HARGA_DASAR_TIN, atr.CPM_ATR_GEDUNG, atr.CPM_ATR_ALKOHOL_ROKOK, atr.CPM_ATR_TOL, atr.CPM_ATR_JAM
			FROM PATDA_REKLAME_DOC_ATR AS atr
			INNER JOIN PATDA_REKLAME_DOC AS doc ON doc.CPM_ID = atr.CPM_ATR_REKLAME_ID
			INNER JOIN PATDA_REKLAME_PROFIL AS prf ON prf.CPM_ID = atr.CPM_ATR_ID_PROFIL
			LEFT JOIN patda_mst_kecamatan AS kec ON kec.CPM_KEC_ID = prf.CPM_KECAMATAN_OP
			LEFT JOIN patda_mst_kelurahan AS kel ON kel.CPM_KEL_ID = prf.CPM_KELURAHAN_OP
			INNER JOIN {$this->PATDA_REK_PERMEN13} AS per ON per.kdrek = atr.CPM_ATR_REKENING
			WHERE atr.CPM_ATR_REKLAME_ID = '{$this->_id}'";
			$result = mysqli_query($this->Conn, $query);
			$x = 0;
			while ($data = mysqli_fetch_assoc($result)) {
				$pajak_atr[$x] = $data;
				$npwpd = $data['CPM_NPWPD'];
				$x++;
			}

			$profil = $Op->get_profil_bywp($npwpd);
		}

		$query = sprintf("SELECT * FROM PATDA_REKLAME_DOC_TRANMAIN WHERE CPM_TRAN_REKLAME_ID = '%s' AND CPM_TRAN_FLAG = '0'", $this->_id);
		$result = mysqli_query($this->Conn, $query);
		$tran_date = '';
		if ($d = mysqli_fetch_assoc($result)) {
			$tran_date = $d['CPM_TRAN_CLAIM_DATETIME'];
		}

		$pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
		$pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
		$pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak_reklame;
		$pajak['CPM_TRAN_CLAIM_DATETIME'] = $tran_date;

		$pajak = array_merge($pajak, $arr_rekening);


		//echo '<pre>',print_r($pajak,true),'</pre>';exit;
		return array(
			'pajak' => $pajak,
			'profil' => $profil,
			'pajak_atr' => $pajak_atr,
			'list_nop' => $list_nop
		);
	}


	public function get_previous_pajak($npwpd, $nop)
	{
		$Op = new ObjekPajak();
		$arr_rekening = $this->getRekening("4.1.01.09");
		$pajak_atr = array();
		$list_nop = array();

		$ms = $this->inisialisasi_masa_pajak();

		$pajak['CPM_ID'] = '';
		$pajak['CPM_NO'] = '';
		$pajak['CPM_ID_PROFIL'] = '';
		$pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
		$pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
		$pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
		$pajak['CPM_HARGA'] = 0;

		$pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
		$pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
		$pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak_reklame;

		$pajak = array_merge($pajak, $arr_rekening);

		//echo '<pre>',print_r($pajak,true),'</pre>';exit;
		return array(
			'pajak' => $pajak,
			'profil' => $profil,
			'pajak_atr' => $pajak_atr,
			'list_nop' => $list_nop
		);
	}

	private function get_tarif($id = "")
	{
		$data = array("CPM_ID" => "", "CPM_PERDA" => "", "CPM_TARIF_PAJAK" => "");

		$where = ($id != "") ? "CPM_ID='{$id}'" : "CPM_AKTIF = '1' AND CPM_JENIS_PAJAK='{$this->id_pajak}'";
		$query = "SELECT * FROM {$this->PATDA_TARIF} a WHERE {$where}";
		$res = mysqli_query($this->Conn, $query);
		if ($d = mysqli_fetch_assoc($res)) {
			$data['CPM_ID'] = $d['CPM_ID'];
			$data['CPM_TARIF_PAJAK'] = $d['CPM_TARIF_PAJAK'];
			$data['CPM_PERDA'] = $d['CPM_PERDA'];
		}
		return $data;
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

		$reks = $this->getRekening("4.1.01.09");
		$opt_rekening = '<option value="">All</option>' . json_encode($reks);
		foreach ($reks['ARR_REKENING'] as $k => $v) {
			$opt_rekening .= "<option value=\"{$k}\">$k - {$v['nmrek']}</option>";
		}

		$opt_pilih = "<option value=\"\">All</option>";
		foreach ($this->arr_jenis as $k => $v) {
			$opt_pilih .= "<option value=\"{$k}\">{$v}</option>";
		}

		$reks = $this->getRekening("4.1.01.09");
		$opt_jenis = ' <option value="">All</option>';
		foreach ($reks['ARR_REKENING'] as $k => $v) {
			$opt_jenis .= "<option value=\"{$k}\">$k - {$v['nmrek']}</option>";
		}
		$html = "<div class=\"filtering\">
                    <form>
						<table>
							<tr valign=\"bottom\">
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
								<button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\">x</button>";
		if ($this->_i == 4) {
			$html .= "<td>Total Pajak :<br><input type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\"> </td>";
		}
		$html .= "<td>Rekening :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
								</td>
								
							</tr>
						</table>
						<table>
                            <tr valign=\"bottom\">
							 	<td>Pilih Jenis :<br><select style=\"width:90px; height:30px;\" class=\"form-control\" name=\"CPM_PIUTANG-{$id}\" id=\"CPM_PIUTANG-{$id}\">{$opt_pilih}</select></td>
								 <td><br><b>Jenis Pajak	</b> : <select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_JENIS_PJK-{$id}\" id=\"CPM_JENIS_PJK-{$id}\">{$opt_jenis}</select></td>
								 <td>Filter V2 :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_FILTER_V2-{$id}\" style=\"max-width:200px\" id=\"CPM_FILTER_V2-{$id}\">
									 <option value=\"\">All</option>
									 <option value=\"4.1.01.09.01.01,4.1.01.09.01.04\">Pajak Reklame Papan / Billboard / Videotron / Megatron (Baru)</option>
									 <option value=\"4.1.01.09.01.001,4.1.01.09.01.004\">Pajak Reklame Papan / Billboard / Videotron / Megatron (Lama)</option>
								 </select></td>
                                <td><br><button type=\"submit\" style=\"width:50px; height:30px;\" class=\"btn btn-sm btn-secondary\" id=\"cari-{$id}\">Cari</button></td>
                                <td><br><button type=\"button\" style=\"width:90px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button></td>
                                <td><br><button type=\"button\" style=\"width:150px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-bentang-panjang.xls.php')\">Cetak Bentang Panjang</button> </td> 
								<td><br><button type=\"button\" style=\"width:110px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak-v2.xls.php')\">Export to xls V2</button></td>   
                            </tr>
                        </table>
					</form>
                </div> ";
		return $html;
	}

	public function grid_table()
	{
		$DIR = "PATDA-V1";
		$modul = "reklame";
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
                                CPM_TOTAL_OMZET: {title: 'Total Pajak',width: '10%'},
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
								CPM_JENIS_PJK : $('#CPM_JENIS_PJK-{$this->_i}').val(),
								CPM_FILTER_V2 : $('#CPM_FILTER_V2-{$this->_i}').val()
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
			// $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";

			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			// $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
				$where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
			}

			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
			$where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND atr.CPM_ATR_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";

			#count utk pagging
			$query = "SELECT COUNT(pj.CPM_ID) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj
                        INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
						INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pr.CPM_ID = atr.CPM_ATR_ID_PROFIL
                        INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                        WHERE {$where} GROUP BY pj.CPM_ID";
			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,pr.CPM_NOP,
                        pj.CPM_TOTAL_PAJAK, pj.CPM_TOTAL_OMZET, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_PIUTANG
                        FROM {$this->PATDA_REKLAME_DOC} pj
						INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
						INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pr.CPM_ID = atr.CPM_ATR_ID_PROFIL
                        INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                        WHERE {$where}
                        GROUP BY pj.CPM_ID ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($this->Conn, $query);

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
					$func = 'fPatdaLaporPiutang7';
				}

				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
				$url = "main.php?param=" . base64_encode($base64);

				$row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
				$row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
				$row['CPM_TOTAL_OMZET'] = number_format($row['CPM_TOTAL_OMZET'], 2);
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
		$modul = "reklame";
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
                                CPM_JENIS_PJK : $('#CPM_JENIS_PJK-{$this->_i}').val(),
                                CPM_PIUTANG : $('#CPM_PIUTANG-{$this->_i}').val(),
                                TOTAL_PAJAK : $('#TOTAL_PAJAK-{$this->_i}').val(),
								CPM_FILTER_V2 : $('#CPM_FILTER_V2-{$this->_i}').val()

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
		// var_dump($_REQUEST);
		// die;
		$selectedValues = explode(',', $_REQUEST['CPM_FILTER_V2']);
		$rekekningv2 = "'" . implode("','", $selectedValues) . "'";


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
			// $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";

			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			//$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = {$_REQUEST['TOTAL_PAJAK']} " : "";

			$where .= (isset($_REQUEST['CPM_FILTER_V2']) && $_REQUEST['CPM_FILTER_V2'] != "") ? " AND CPM_ATR_REKENING IN ( {$rekekningv2}) " : "";
			if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
				$where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") 
				OR TIMESTAMP >= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\", \" 00:00:00\")
    			AND TIMESTAMP <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\", \" 23:59:59\"))
				";
			}


			// $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
			// STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) " : "";


			$where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND atr.CPM_ATR_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
			$where .= (isset($_REQUEST['CPM_PIUTANG']) && $_REQUEST['CPM_PIUTANG'] != "") ? " AND CPM_PIUTANG='{$_REQUEST['CPM_PIUTANG']}' " : "";

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj
                            INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
							INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pr.CPM_ID = atr.CPM_ATR_ID_PROFIL
							INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                            WHERE {$where} GROUP BY pj.CPM_ID";

			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,pj.CPM_TYPE_PAJAK,
                           CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pj.CPM_TOTAL_OMZET, pr.CPM_NPWPD, pr.CPM_NAMA_WP,pr.CPM_NAMA_OP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_TGL_INPUT, tr.CPM_TRAN_DATE, pj.CPM_PIUTANG
                            FROM {$this->PATDA_REKLAME_DOC} pj
							INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
							INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pr.CPM_ID = atr.CPM_ATR_ID_PROFIL
							INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                            WHERE {$where}
                            GROUP BY pj.CPM_ID ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";

			// var_dump($query);
			// die;
			$result = mysqli_query($this->Conn, $query);

			$rows = array();
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));

				$row['READ'] = strpos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;


				$func = $this->_f;
				if ($row['CPM_PIUTANG'] == 1) {
					$func = 'fPatdaLaporPiutang7';
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
		$query = "SELECT * FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_REKLAME_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
		$res = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_assoc($res);

		return $data['CPM_TRAN_REKLAME_VERSION'];
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

			$res = mysqli_query($this->Conn, $sql);
			if (mysqli_num_rows($res))
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

		$res = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_assoc($res);

		if ($this->notif == true) {
			if (mysqli_num_rows($res)) {
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

		$respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
		$respon['result'] = ($input == 0) ? true : $respon['result'];
		$respon['data'] = $data;

		//echo '<pre>'.print_r($respon,true).'</pre>';exit;
		return $respon;
	}

	private function toNumber($str)
	{
		return preg_replace("/([^0-9\\.])/i", "", $str);
	}

	private function save_pajak($cpm_no = '')
	{	
		$validasi = $this->validasi_save();

		if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4')) {
			$this->Message->clearMessage();

			#update profil baru
			$query = "UPDATE {$this->PATDA_REKLAME_PROFIL} SET CPM_APPROVE ='1' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
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
			$PAJAK_ATR = $_POST['PAJAK_ATR'];


			$this->CPM_ID = c_uuid();
			$this->CPM_TGL_LAPOR = date("d-m-Y");
			$this->CPM_TOTAL_OMZET = $this->toNumber($this->CPM_TOTAL_OMZET);
			$this->CPM_TOTAL_PAJAK = $this->toNumber($this->CPM_TOTAL_PAJAK);
			$this->CPM_TARIF_PAJAK = $this->toNumber($this->CPM_TARIF_PAJAK);
			$this->CPM_NO_SSPD = ($this->CPM_NOP == '-') ? substr($this->CPM_NO, 0, 9) : substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
			$this->CPM_MASA_PAJAK1 = $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0];
			$this->CPM_MASA_PAJAK2 = $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0];
			
			$this->CPM_ID_PROFIL = $PAJAK_ATR['CPM_ATR_NOP'][0];

			$this->CPM_DENDA_TERLAMBAT_LAP = $this->toNumber($this->CPM_DENDA_TERLAMBAT_LAP);
			$this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

			$query = sprintf(
				"INSERT INTO {$this->PATDA_REKLAME_DOC}
                    (CPM_ID, CPM_ID_PROFIL, CPM_NO, CPM_MASA_PAJAK, CPM_TAHUN_PAJAK, CPM_TOTAL_OMZET, CPM_TOTAL_PAJAK, CPM_TARIF_PAJAK,CPM_TGL_JATUH_TEMPO,
                    CPM_KETERANGAN, CPM_VERSION, CPM_AUTHOR, CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD, CPM_JNS_MASA_PAJAK,
                    CPM_MASA_PAJAK1, CPM_MASA_PAJAK2, CPM_SK_DISCOUNT, CPM_DISCOUNT, CPM_TYPE_PAJAK, CPM_DENDA_TERLAMBAT_LAP, CPM_PIUTANG)
                    VALUES ( '%s','%s','%s',%f,%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%f,%d,%f,'%s')",
				$this->CPM_ID,
				$this->CPM_ID_PROFIL,
				$this->CPM_NO,
				$this->CPM_MASA_PAJAK,
				$this->CPM_TAHUN_PAJAK,
				$this->CPM_TOTAL_OMZET,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_TARIF_PAJAK,
				$this->CPM_TGL_JATUH_TEMPO,
				$this->CPM_KETERANGAN,
				$this->CPM_VERSION,
				$this->CPM_AUTHOR,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_NO_SSPD,
				$this->CPM_JNS_MASA_PAJAK,
				$PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0],
				$PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0],
				$this->CPM_SK_DISCOUNT,
				$this->CPM_DISCOUNT,
				$this->CPM_TYPE_PAJAK,
				$this->CPM_DENDA_TERLAMBAT_LAP,
				$this->CPM_PIUTANG
			);	 		
			//echo $this->CPM_NO;exit();
			$res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn) . ' ' . $query);
			if ($res) {

				$j = count($PAJAK_ATR['CPM_ATR_REKENING']);

				for ($x = 0; $x < $j; $x++) {
					$judul = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_JUDUL'][$x]);
					$kawasan = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_KAWASAN'][$x]);
					$nop = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_NOP'][$x]);
					$sudut_pandang = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_SUDUT_PANDANG'][$x]);
					$lebar = $this->toNumber($PAJAK_ATR['CPM_ATR_LEBAR'][$x]);
					$panjang = $this->toNumber($PAJAK_ATR['CPM_ATR_PANJANG'][$x]);
					$muka = $this->toNumber($PAJAK_ATR['CPM_ATR_MUKA'][$x]);
					$sisi = $this->toNumber($PAJAK_ATR['CPM_ATR_SISI'][$x]);
					$jari = ""; #$this->toNumber($PAJAK_ATR['CPM_ATR_JARI'][$x]);
					$total = $this->toNumber($PAJAK_ATR['CPM_ATR_TOTAL'][$x]);
					$biaya = $this->toNumber($PAJAK_ATR['CPM_ATR_BIAYA'][$x]);

					// $hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
					// $hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
					$norekening = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_REKENING'][$x]);
					$jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
					$tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_TARIF'][$x]);
					$jumlah_tahun = isset($PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN'][0] : 0;
					$jumlah_hari = isset($PAJAK_ATR['CPM_ATR_JUMLAH_HARI']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_HARI'][0] : 0;
					$jumlah_minggu = isset($PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU'][0] : 0;
					$jumlah_bulan = isset($PAJAK_ATR['CPM_ATR_JUMLAH_BULAN']) ? $PAJAK_ATR['CPM_ATR_JUMLAH_BULAN'][0] : 0;
					$jenis = "";
					$lokasi = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_LOKASI'][$x]);
					$batas_awal = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0]);
					$batas_akhir = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0]);
					$type_masa = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_TYPE_MASA'][$x]);
					$cek_pk = (!empty($PAJAK_ATR['CPM_CEK_PIHAK_KETIGA'][$x])) ? 'true' : 'false';
					$nilai_pk = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_NILAI_PIHAK_KETIGA'][$x]);


					//tambahan
					$hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
					$hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
					$tinggi = $this->toNumber($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
					$gedung = mysql_escape_string($PAJAK_ATR['CPM_ATR_GEDUNG'][$x]);
					$alkohol_rokok = (isset($PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x])) ? $PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x] : '0';
					$tol = (isset($PAJAK_ATR['CPM_ATR_TOL'][$x])) ? $PAJAK_ATR['CPM_ATR_TOL'][$x] : '0';
					$jalan = mysql_escape_string($PAJAK_ATR['CPM_ATR_JALAN'][$x]);
					$jam = (isset($PAJAK_ATR['CPM_ATR_JAM'][$x])) ? $PAJAK_ATR['CPM_ATR_JAM'][$x] : '0';

					//
					$res_hargadasar = $this->get_hargadasar(
						array(
							'panjang' => $panjang,
							'lebar' => $lebar,
							'muka' => $muka,
							'sisi' => $sisi,
							'durasi' => $this->CPM_MASA_PAJAK,
							'tarif' => $tarif,
							'jumlah' => $jumlah,
							'biaya' => $biaya,
							// 'harga_dasar_uk' => $hd_ukuran,
							// 'harga_dasar_tin' => $hd_ketinggian,
							'kdrek' => $norekening,
							'kawasan' => $kawasan,
							'sudut_pandang' => $sudut_pandang,
							'durasi_label' => $this->CPM_JNS_MASA_PAJAK,
							'x' => $cek_pk,
							'npk' => $nilai_pk,
							//tambahan
							'harga_dasar_uk' => $hd_ukuran,
							'harga_dasar_tin' => $hd_ketinggian,
							'tinggi' => $tinggi,
							'gedung' => $gedung,
							'alkohol_rokok' => $alkohol_rokok,
							'tol' => $tol,
							'jalan' => $jalan,
							'durasi_hari' => $jumlah_hari,
							'jam' => $jam,
						)
					);


					$nilai_strategis = $res_hargadasar['nilai_strategis'];
					$njop = $res_hargadasar['njop'];
					$perhitungan = $res_hargadasar['rumus_hitung'];
					$harga = $res_hargadasar['harga'];
					//
					// var_dump($res_hargadasar['rumus_hitung']);die;
					$query = sprintf(
						"INSERT INTO {$this->PATDA_REKLAME_DOC_ATR}
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA, CPM_ATR_HARGA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_PANJANG, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA,
                            CPM_ATR_NILAI_STRATEGIS, CPM_ATR_KAWASAN, CPM_ATR_JALAN, CPM_ATR_SUDUT_PANDANG, CPM_ATR_NJOP, CPM_ATR_PERHITUNGAN,CPM_CEK_PIHAK_KETIGA,CPM_NILAI_PIHAK_KETIGA,CPM_ATR_SISI, CPM_ATR_ID_PROFIL, CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, CPM_ATR_GEDUNG, CPM_ATR_ALKOHOL_ROKOK, CPM_ATR_TOL,
							CPM_ATR_JAM, CPM_RUMUS)
                            VALUES ('%s','%s',%f,%f,'%s',%f,%f,%f,%f,%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s')",
						$this->CPM_ID,
						$judul,
						$biaya,
						$harga,
						$lokasi,
						$lebar,
						$panjang,
						$jumlah,
						$jari,
						$muka,
						$tarif,
						$jumlah_tahun,
						$jumlah_hari,
						$jumlah_minggu,
						$jumlah_bulan,
						$batas_awal,
						$batas_akhir,
						$total,
						$norekening,
						$type_masa,
						$nilai_strategis,
						$kawasan,
						$jalan,
						$sudut_pandang,
						$njop,
						$perhitungan,
						$cek_pk,
						$nilai_pk,
						$sisi,
						$nop,
						$hd_ukuran,
						$hd_ketinggian,
						$tinggi,
						$gedung,
						$alkohol_rokok,
						$tol,
						$jam,
						$_POST['RMS']
					);
					mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn) . ' ' . $query);
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
		$CPM_TRAN_REKLAME_ID = $this->CPM_ID;

		$query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_REKLAME_ID = '{$CPM_TRAN_REKLAME_ID}'";
		$res = mysqli_query($this->Conn, $query);

		$query = sprintf(
			"INSERT INTO {$this->PATDA_REKLAME_DOC_TRANMAIN}
                    (CPM_TRAN_ID, CPM_TRAN_REKLAME_ID, CPM_TRAN_REKLAME_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE,
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s')",
			$CPM_TRAN_ID,
			$CPM_TRAN_REKLAME_ID,
			$param['CPM_TRAN_REKLAME_VERSION'],
			$param['CPM_TRAN_STATUS'],
			$param['CPM_TRAN_FLAG'],
			$param['CPM_TRAN_DATE'],
			$param['CPM_TRAN_OPR'],
			$param['CPM_TRAN_OPR_DISPENDA'],
			$param['CPM_TRAN_INFO']
		);
		#echo $query;exit();
		return mysqli_query($this->Conn, $query);
	}

	private function update_tgl_input()
	{
		$tgl_input = date("Y-m-d h:i:s");
		$query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

		return mysqli_query($this->Conn, $query);
	}

	private function update_tgl_lapor()
	{
		$tgl_input = date("d-m-Y");
		$query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_LAPOR = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

		return mysqli_query($this->Conn, $query);
	}

	private function update_tgl_lapor_ditolak($cpm_no, $tgl_lapor, $tgl_input)
	{
		$tgl_input = $tgl_input != '' ? $tgl_input : 'NULL';

		if ($tgl_input == 'NULL') {
			$query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}'
                  WHERE CPM_NO ='{$cpm_no}'";
		} else {
			$query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}', CPM_TGL_INPUT = '{$tgl_input}'
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
			$param['CPM_TRAN_REKLAME_VERSION'] = "1";
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
		if ($this->CPM_PIUTANG == 1) {
			if ($this->validasi_piutang() == false) {
				return false;
			}
		}	
			$this->CPM_VERSION = "1";
		if ($this->save_pajak($this->CPM_NO)) {
			$param['CPM_TRAN_REKLAME_VERSION'] = "1";
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

			$query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_REKLAME_ID='{$id}'";
			mysqli_query($this->Conn, $query);

			$param['CPM_TRAN_REKLAME_VERSION'] = $new_version;
			$param['CPM_TRAN_STATUS'] = "1";
			$param['CPM_TRAN_FLAG'] = "0";
			$param['CPM_TRAN_DATE'] = date("d-m-Y");
			$param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
			$param['CPM_TRAN_OPR_DISPENDA'] = "";
			$param['CPM_TRAN_READ'] = "";
			$this->save_tranmain($param);

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

			$query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_REKLAME_ID='{$id}'";
			mysqli_query($this->Conn, $query);

			$param['CPM_TRAN_REKLAME_VERSION'] = $new_version;
			$param['CPM_TRAN_STATUS'] = "2";
			$param['CPM_TRAN_FLAG'] = "0";
			$param['CPM_TRAN_DATE'] = date("d-m-Y");
			$param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
			$param['CPM_TRAN_OPR_DISPENDA'] = "";
			$param['CPM_TRAN_READ'] = "";
			$param['CPM_TRAN_INFO'] = "";
			$this->save_tranmain($param);

			// if ($this->update_tgl_lapor_ditolak($this->CPM_NO, $this->DITOLAK_TGL_LAPOR, $this->DITOLAK_TGL_INPUT)) {
			// 	//$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
			// } else {
			// 	$_SESSION['_error'] = 'Tgl input gagal disimpan';
			// }

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
			$param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
			$param['CPM_TRAN_STATUS'] = "2";
			$param['CPM_TRAN_FLAG'] = "0";
			$param['CPM_TRAN_DATE'] = date("d-m-Y");
			$param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
			$param['CPM_TRAN_OPR_DISPENDA'] = "";
			$param['CPM_TRAN_INFO'] = "";
			$param['CPM_TRAN_READ'] = "";
			$this->save_tranmain($param);
			// if ($this->update_tgl_lapor()) {
			// 	//$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
			// } else {
			// 	$_SESSION['_error'] = 'Tgl input gagal disimpan';
			// }

			$res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
			// echo"<pre>";
			// print_r($res);
			// exit;
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
			$this->Message->clearMessage();

			$this->CPM_TOTAL_OMZET = $this->toNumber($this->CPM_TOTAL_OMZET);
			$this->CPM_TOTAL_PAJAK = $this->toNumber($this->CPM_TOTAL_PAJAK);
			$this->CPM_TARIF_PAJAK = $this->toNumber($this->CPM_TARIF_PAJAK);
			$this->CPM_DENDA_TERLAMBAT_LAP = $this->toNumber($this->CPM_DENDA_TERLAMBAT_LAP);

			// $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
			$this->CPM_DPP = $this->toNumber($this->CPM_TOTAL_PAJAK);
			$this->CPM_BAYAR_TERUTANG = $this->toNumber($this->CPM_TOTAL_PAJAK);
			$this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

			$PAJAK_ATR = $_POST['PAJAK_ATR'];
			// var_dump($PAJAK_ATR);
			// die;
			$queryUp = sprintf(
				"UPDATE {$this->PATDA_REKLAME_DOC} SET
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
                    CPM_DISCOUNT = %f,
					CPM_PIUTANG = '%s'
                    WHERE
                    CPM_ID ='{$this->CPM_ID}'",
				$this->CPM_TOTAL_OMZET,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_TARIF_PAJAK,
				$this->CPM_DPP,
				$this->CPM_BAYAR_TERUTANG,
				$this->CPM_KETERANGAN,
				$this->CPM_MASA_PAJAK1,
				$this->CPM_MASA_PAJAK2,
				$this->CPM_TAHUN_PAJAK,
				$this->CPM_MASA_PAJAK,
				$this->CPM_PERPANJANG,
				$this->CPM_DENDA_TERLAMBAT_LAP,
				$this->CPM_NO_SSPD_SBLM,
				$PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0],
				$PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0],
				$this->CPM_SK_DISCOUNT,
				$this->CPM_DISCOUNT,
				$this->CPM_PIUTANG
			);

			$upd = mysqli_query($this->Conn, $queryUp);

			$ok = 0;
			$j = count($PAJAK_ATR['CPM_ATR_REKENING']);

			for ($x = 0; $x < $j; $x++) {
				$atr_id = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_ID'][$x]);
				$judul = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_JUDUL'][$x]);
				$kawasan = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_KAWASAN'][$x]);
				$jalan = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_JALAN'][$x]);
				$nop = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_NOP'][$x]);
				$sudut_pandang = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_SUDUT_PANDANG'][$x]);
				$lebar = $this->toNumber($PAJAK_ATR['CPM_ATR_LEBAR'][$x]);
				$panjang = $this->toNumber($PAJAK_ATR['CPM_ATR_PANJANG'][$x]);
				$muka = $this->toNumber($PAJAK_ATR['CPM_ATR_MUKA'][$x]);
				$sisi = $this->toNumber($PAJAK_ATR['CPM_ATR_SISI'][$x]);
				$jari = $this->toNumber($PAJAK_ATR['CPM_ATR_JARI'][$x]);
				$total = $this->toNumber($PAJAK_ATR['CPM_ATR_TOTAL'][$x]);
				$biaya = $this->toNumber($PAJAK_ATR['CPM_ATR_BIAYA'][$x]);
				// $hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
				// $hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
				$norekening = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_REKENING'][$x]);
				$type_masa = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_TYPE_MASA'][$x]);
				$jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
				$tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_BIAYA'][$x]);
				// $tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_TARIF'][$x]);
				$jumlah_tahun = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_TAHUN'][0]);
				$jumlah_hari = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_HARI'][0]);
				$jumlah_minggu = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_MINGGU'][0]);
				$jumlah_bulan = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH_BULAN'][0]);

				$jenis = "";
				$lokasi = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_LOKASI'][$x]);
				$batas_awal = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0]);
				$batas_akhir = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0]);
				$cek_pk = (!empty($PAJAK_ATR['CPM_CEK_PIHAK_KETIGA'][0])) ? 'true' : 'false';
				$nilai_pk = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_NILAI_PIHAK_KETIGA'][0]);

				//tambahan
				$hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][0]);
				$hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][0]);
				$tinggi = $this->toNumber($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
//				$gedung = mysql_escape_string($PAJAK_ATR['CPM_ATR_GEDUNG'][$x]);
//				$alkohol_rokok = (isset($PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x])) ? $PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x] : '0';
//				$tol = (isset($PAJAK_ATR['CPM_ATR_TOL'][$x])) ? $PAJAK_ATR['CPM_ATR_TOL'][$x] : '0';
				$jam = (isset($PAJAK_ATR['CPM_ATR_JAM'][$x])) ? $PAJAK_ATR['CPM_ATR_JAM'][$x] : '0';

				$res_hargadasar = $this->get_hargadasar(
					array(
						'panjang' => $panjang,
						'lebar' => $lebar,
						'muka' => $muka,
						'sisi' => $sisi,
						'durasi' => $this->CPM_MASA_PAJAK,
						'tarif' => $tarif,
						'jumlah' => $jumlah,
						'biaya' => $biaya,
						'kdrek' => $norekening,
						'kawasan' => $kawasan,
						'jalan' => $jalan,
						'sudut_pandang' => $sudut_pandang,
						'durasi_label' => $this->CPM_JNS_MASA_PAJAK,
						'x' => $cek_pk,
						'npk' => $nilai_pk,
						//tambahan
						'harga_dasar_uk' => $hd_ukuran,
						'harga_dasar_tin' => $hd_ketinggian,
						'tinggi' => $tinggi,
//						'gedung' => $gedung,
//						'alkohol_rokok' => $alkohol_rokok,
//						'tol' => $tol,
						'jalan' => $jalan,
						'durasi_hari' => $jumlah_hari,
						'jam' => $jam,
					)
				);

				$nilai_strategis = $res_hargadasar['nilai_strategis'];
				$njop = $res_hargadasar['njop'];
				$perhitungan = $res_hargadasar['rumus_hitung'];
				$harga = $res_hargadasar['harga'];
				if ($atr_id == '') {
					$query = sprintf(
						"INSERT INTO {$this->PATDA_REKLAME_DOC_ATR}
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA, CPM_ATR_HARGA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_PANJANG, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA,
                            CPM_ATR_NILAI_STRATEGIS, CPM_ATR_KAWASAN, CPM_ATR_JALAN, CPM_ATR_SUDUT_PANDANG, CPM_ATR_NJOP, CPM_ATR_PERHITUNGAN,CPM_CEK_PIHAK_KETIGA,CPM_NILAI_PIHAK_KETIGA,CPM_ATR_SISI, CPM_ATR_ID_PROFIL, CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, 
							CPM_ATR_JAM)
                            VALUES ('%s','%s',%f,%f,'%s',
                                    %f,%f,%f,%f,%f,
                                    %f,'%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
						$this->CPM_ID,
						$judul,
						$biaya,
						$harga,
						$lokasi,
						$lebar,
						$panjang,
						$jumlah,
						$jari,
						$muka,
						$tarif,
						$jumlah_tahun,
						$jumlah_hari,
						$jumlah_minggu,
						$jumlah_bulan,
						$batas_awal,
						$batas_akhir,
						$total,
						$norekening,
						$type_masa,
						$nilai_strategis,
						$kawasan,
						$jalan,
						$sudut_pandang,
						$njop,
						$perhitungan,
						$cek_pk,
						$nilai_pk,
						$sisi,
						$nop,
						$hd_ukuran,
						$hd_ketinggian,
						$tinggi,
						
						$jam
					);

					//CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, CPM_ATR_GEDUNG, CPM_ATR_ALKOHOL_ROKOK, CPM_ATR_TOL
				} else {
					$query = sprintf(
						"UPDATE {$this->PATDA_REKLAME_DOC_ATR} SET CPM_ATR_JUDUL='%s', CPM_ATR_BIAYA='%s', CPM_ATR_HARGA='%s',
						CPM_ATR_LEBAR='%s', CPM_ATR_PANJANG='%s', CPM_ATR_JUMLAH='%s',CPM_ATR_JARI='%s',
						CPM_ATR_MUKA='%s', CPM_ATR_TARIF='%s', CPM_ATR_JUMLAH_TAHUN='%s', CPM_ATR_JUMLAH_HARI='%s',
						CPM_ATR_JUMLAH_MINGGU='%s', CPM_ATR_JUMLAH_BULAN='%s', CPM_ATR_BATAS_AWAL='%s', CPM_ATR_BATAS_AKHIR='%s',
						CPM_ATR_TOTAL='%s', CPM_ATR_REKENING='%s', CPM_ATR_TYPE_MASA='%s', CPM_ATR_LOKASI ='%s',

						CPM_ATR_NILAI_STRATEGIS='%s', CPM_ATR_KAWASAN='%s', CPM_ATR_JALAN='%s', CPM_ATR_SUDUT_PANDANG='%s', CPM_ATR_NJOP='%s', CPM_ATR_PERHITUNGAN='%s',
						CPM_CEK_PIHAK_KETIGA='%s', CPM_NILAI_PIHAK_KETIGA='%s',
						CPM_ATR_SISI = '%s', CPM_ATR_HARGA_DASAR_UK = '%s', CPM_ATR_HARGA_DASAR_TIN = '%s', CPM_ATR_TINGGI = '%s', CPM_ATR_GEDUNG = '%s', CPM_ATR_ALKOHOL_ROKOK = '%s', CPM_ATR_TOL = '%s',
						CPM_ATR_JAM = '%s'

						WHERE CPM_ATR_ID='%s'
						",
						$judul,
						$biaya,
						$harga,
						$lebar,
						$panjang,
						$jumlah,
						$jari,
						$muka,
						$tarif,
						$jumlah_tahun,
						$jumlah_hari,
						$jumlah_minggu,
						$jumlah_bulan,
						$batas_awal,
						$batas_akhir,
						$total,
						$norekening,
						$type_masa,
						$lokasi,
						$nilai_strategis,
						$kawasan,
						$jalan,
						$sudut_pandang,
						$njop,
						$perhitungan,
						$cek_pk,
						$nilai_pk,
						$sisi,
						$hd_ukuran,
						$hd_ketinggian,
						$tinggi,
						$gedung,
						$alkohol_rokok,
						$tol,
						$jam,
						$atr_id
					);
				}

				// 	echo"<pre>";
				// print_r($query);
				// exit;

				if (mysqli_query($this->Conn, $query)) $ok++;
			}
			return ($upd || $ok > 0);
		}
		return false;
	}

	public function delete()
	{
		$query = "DELETE FROM {$this->PATDA_REKLAME_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
		$res = mysqli_query($this->Conn, $query);
		if ($res) {
			$query = "DELETE FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_REKLAME_ID ='{$this->CPM_ID}'";
			mysqli_query($this->Conn, $query);
		}
	}

	public function verifikasi()
	{
		if ($this->AUTHORITY == 1) {
			$query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
			// echo $query;exit;
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
          $param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
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
		$new_operator = $_SESSION['uname'];

		$status = ($this->AUTHORITY == 1) ? 5 : 4;
		$param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
		$param['CPM_TRAN_STATUS'] = $status;
		$param['CPM_TRAN_FLAG'] = "0";
		$param['CPM_TRAN_DATE'] = date("d-m-Y");
		$param['CPM_TRAN_OPR'] = "";
		$param['CPM_TRAN_OPR_DISPENDA'] = $new_operator;
		$param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
		$param['CPM_TRAN_READ'] = "";
		$res = $this->save_tranmain($param);
		if ($this->AUTHORITY == 1 && $res == true) {
			$arr_config = $this->get_config_value($this->_a);
			$res = $this->save_gateway($this->id_pajak, $arr_config);

			if ($res) {
				$this->update_jatuh_tempo($this->EXPIRED_DATE, $this->CPM_TGL_JATUH_TEMPO);
				$this->update_validasi($this->CPM_ID);
				$_SESSION['_success'] = 'Data Pajak berhasil disetujui';
			} else {
				$_SESSION['_error'] = 'Data Pajak gagal disetujui';
			}
		}
	}

	// private function update_jatuh_tempo($expired_date)
	// {
	// 	$query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
    //               WHERE CPM_ID ='{$this->CPM_ID}'";
	// 	return mysqli_query($this->Conn, $query);
	// }

	private function update_jatuh_tempo($expired_date, $tgl_jatuh_tempo = NULL)
    {
        
        if ($tgl_jatuh_tempo == NULL || $tgl_jatuh_tempo == '') {
           $expired_date = $expired_date;
        }else{
            $expired_date = "'" . $tgl_jatuh_tempo . "'";
        }
        $query = "UPDATE {$this->PATDA_REKLAME_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
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

	public function print_skpd_base()
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
		$BAG_VERIFIKASI_NAMA = $config['KEPALA_DINAS_NAMA'];
		$NIP = $config['KEPALA_DINAS_NIP'];
		$BANK = $config['BANK'];
		$BADAN = $config['NAMA_BADAN_PENGELOLA'];

		//echo '<pre>',print_r($DATA),'</pre>';exit;
		$html = "<table width=\"710\" border=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"220\"><p><strong>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
					" . strtoupper($NAMA_PENGELOLA) . "<br/>
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
                        Panjang : {$DATA['pajak_atr'][0]['CPM_ATR_PANJANG']} m,
                        Lebar : {$DATA['pajak_atr'][0]['CPM_ATR_LEBAR']} m,
                        Muka :  " . number_format($DATA['pajak_atr'][0]['CPM_ATR_MUKA'], 0) . ",<br/>
						Ukuran : " . ($DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] * $DATA['pajak_atr'][0]['CPM_ATR_LEBAR']) . " m<sup>2</sup>,
						Jumlah : " . number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'], 0) . ",
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
						<td width=\"96%\">Harapan penyetoran dilakukan pada " . ucwords(strtolower($BANK)) . " dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
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
					  <td width=\"289\" align=\"center\">{$KOTA}, " . $DATA['pajak']['CPM_TGL_LAPOR'] . "<br/>KEPALA BADAN PENGELOLAAN KEUANGAN DAERAH<br/>DAERAH " . strtoupper($KOTA) . "<br/></td>
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

	public function print_skpd($type = "")
	{
		global $sRootPath;
		$this->_id = $this->CPM_ID;
		$DATA = $this->get_pajak();
		$data = $DATA['pajak'];
		$profil = $DATA['profil'];
		$pajak_atr = $DATA['pajak_atr'];

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
		$NO_URUT = $DATA['CPM_NO']; //.'<br/>/REK/'.$d[1];
		// $NO_URUT = $d[0]; //.'<br/>/REK/'.$d[1];

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
		$query_atr = "SELECT CPM_NOP FROM PATDA_REKLAME_PROFIL WHERE CPM_NPWPD = '$get_npwpd'";
		$res = mysqli_query($this->Conn, $query_atr);
		$rows = mysqli_fetch_assoc($res);
		// var_dump($rows['CPM_NOP']);
		// die;
		//
		// $tes = $DATA['CPM_NO'];
		// $tes = $DATA['CPM_NO'];
		// var_dump($DATA['CPM_NO']);
		// die;

		$lala = $rows['CPM_ID'];
		$query_atrs = "SELECT COUNT(CPM_ATR_REKLAME_ID) as total_nop FROM PATDA_REKLAME_DOC_ATR WHERE CPM_ATR_REKLAME_ID = '$this->_id'";
		$ress = mysqli_query($this->Conn, $query_atrs);
		$rowss = mysqli_fetch_assoc($ress);

		$nop_nop = '';
		$total_total_nop = $rowss['total_nop'];
		if ($total_total_nop == 1) {
			$nop_nop = $rows['CPM_NOP'];
		} else {
			$nop_nop = $DATA['CPM_NO'];
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



		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"290\" valign=\"top\" align=\"center\" colspan=\"3\">
										<table border=\"0\" width=\"310\">
											<tr>
												<td width=\"70\"></td>
												<td width=\"220\">
													<b style=\"font-size:28px\"><br/>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "
													{$NAMA_PENGELOLA}<br/>
													
													<br/>
													{$JALAN}<br/>KABUPATEN " . strtoupper($NAMA_PEMERINTAHAN) . "
													<!--{$KOTA} - {$PROVINSI}-->
													</b>
												</td>
											</tr>
										</table>
										<br/>
                                    </td>
                                    <td width=\"300\" valign=\"top\" align=\"center\">
										<b style=\"font-size:35px\"> <br/>SKPD<br/>
										(SURAT KETETAPAN PAJAK DAERAH)<br/>
										</b>&nbsp;&nbsp;
										<br/>
										<tr>
										<td style=\"text-align:left\" width=\"70\"> Masa Pajak</td>
										<td width=\"330\"style=\"text-align:left\">: {$DATA['CPM_MASA_PAJAK1']} s/d {$DATA['CPM_MASA_PAJAK2']}</td>
										</tr>
										<tr>
										<td style=\"text-align:left\" width=\"70\"> Tahun</td>
										<td width=\"330\"style=\"text-align:left\">: {$DATA['CPM_TAHUN_PAJAK']}</td>
										</tr>
										</td>
                                    <td width=\"120\" valign=\"top\" align=\"center\">
										<br/>
										<b style=\"font-size:35px\"><br/>NO-SKPD :<br/></b>
										{$NO_URUT}<br/><br> 
										<b style=\"font-size:35px\">Kode Billing </b>
										{$gw->payment_code}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
						<td width=\"710\">
							<table width=\"710\" border=\"0\" cellpadding=\"2\">
							
							</table>
							<table width=\"710\" border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"400\">
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"150\">Nama Wajib Pajak</td>
												<td width=\"550\">: {$DATA['CPM_NAMA_WP']}</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Objek Pajak</td>
												<td width=\"550\">: {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}</td>
											</tr>
											<tr>
												<td>Alamat Objek Pajak</td>
												<td>: {$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']} &nbsp; {$DATA['pajak_atr']['0']['CPM_KELURAHAN_OP']} &nbsp; KEC. {$DATA['pajak_atr']['0']['CPM_KECAMATAN_OP']} {$DATA['CPM_KOTA_WP']}</td>
											</tr>
					
											<tr>
												<td>NPWPD</td>
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
                    
											<tr>
												<td>Tanggal Jatuh Tempo</td>
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
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
							<tr>
								<td colspan=\"2\">
									<table width=\"500\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\">
										<tr style=\"background-color:#CCC\">
											<td width=\"30\" align=\"center\"><b>No</b></td>
											<td width=\"170\" align=\"center\"><b>Kode Rekening</b></td>
											<td width=\"300\" align=\"center\"><b>Uraian Pajak Daerah</b></td>
											<td width=\"200\" align=\"center\"><b>Jumlah (Rp.)</b></td>
										</tr>
										<tr>
											<td align=\"center\">1.</td>
											<td align=\"center\">4.1.1.4.02</td>
											<td>Pajak Reklame</td>
											<td align=\"right\">" . number_format($atr['CPM_ATR_TOTAL'], 0) . "</td>
										</tr>";

		$html = '';
		$list_op = '';
		foreach ($pajak_atr as $no => $atr) {
			$no++;
			$html .= "<tr>
											<td align=\"center\"></td>
											<td align=\"center\">{$atr['CPM_ATR_REKENING']}</td>
											<td align=\"left\">
												[{$atr['CPM_NOP']}] Pembayaran Pajak Reklame Periode " . $this->formatDateForDokumen($atr['CPM_ATR_BATAS_AWAL']) . " s.d. " . $this->formatDateForDokumen($atr['CPM_ATR_BATAS_AKHIR']) . "<br>
												" . number_format($atr['CPM_ATR_PANJANG'], 2) . "M x {$atr['CPM_ATR_LEBAR']}M x " . intval($atr['CPM_ATR_TINGGI']) . " Tinggi x " . intval($atr['CPM_ATR_SISI']) . " Muka x " . intval($DATA['CPM_MASA_PAJAK']) . " {$DATA['CPM_JNS_MASA_PAJAK']} x <br>Rp. " . number_format($atr['CPM_ATR_TOTAL']) . " / {$DATA['CPM_JNS_MASA_PAJAK']} / M<sup>2</sup>
											</td>
											<td align=\"right\">" . number_format($atr['CPM_ATR_TOTAL']) . "</td>
										</tr>";
			$list_op .= $atr['CPM_NOP'] . ' | ' . $atr['CPM_NAMA_OP'] . ', ' . $atr['CPM_NAMA_OP'] . '<br>';
		}
		$html .= "<tr>
											<td align=\"left\" colspan=\"2\" rowspan=\"3\"></td>
											<td align=\"left\">
												Jumlah Ketetapan Pokok
											</td>
											<td align=\"right\">
												" . number_format($DATA['CPM_TOTAL_OMZET'], 0) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												Jumlah Denda
											</td>
											<td align=\"right\">
												" . number_format($DENDA, 2) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												<b>Jumlah Keseluruhan</b>
											</td>
											<td align=\"right\">
												<b>" . number_format(($TOTAL), 0) . "</b>
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
								<td><table width=\"100%\" border=\"0\" align=\"left\" style=\"font-size:26px;\">
										<tr>
											<td><b>PERHATIAN : </b></td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap Penyetoran dilakukan pada Bank/Bendahara Penerimaan.</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Apabila SKPD ini tidak atau kurang dibayar lewat waktu paling lama 30 hari setelah SKPD diterima atau (tanggal jatuh tempo), <br>
											&nbsp;&nbsp; &nbsp; &nbsp;
											dikenakan sanksi/denda administrasi sebesar 2% perbulan.</td>
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
										a.n. Kepala {$NAMA_PENGELOLA} <br/>
										Kabupaten " . ucwords(strtolower($NAMA_PEMERINTAHAN)) . ",<br/>
										Kepala Bidang PengPajak Daerah Lain<br>";
		if ($PEJABAT_MENGETAHUI['CPM_KEY'] ===  'KABAN_DIPENDA') {
			// $page1 .=  $PEJABAT_MENGETAHUI['CPM_JABATAN'] . '<br/>';
		}

		$page1 .= "<br/><br/><br/>

										<u>{$config['KABID_PENDATAAN_NAMA']}</u><br/>
										NIP. {$config['KABID_PENDATAAN_NIP']}
									</b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\" style=\"font-size:26px;\">
								<tr>
									<td align=\"center\">......................................potong di sini......................................<br><br></td>
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
												<td width=\"330\">: {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}</td>
												<!--<td width=\"270\" rowspan=\"6\" align=\"center\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>-->
                                                <td width=\"270\" rowspan=\"6\" align=\"center\">{$KOTA}, " . $tgl_pengesahans . "<br/>
												Yang Menerima,<br/><br/><br/><br/>

												<b><u>{$DATA['CPM_NAMA_WP']}</u></b>
												</td>
											</tr>
											<tr>
												<td>Alamat</td>
												<td>: {$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']} - {$DATA['pajak_atr']['0']['CPM_KELURAHAN_OP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; KEC. {$DATA['pajak_atr']['0']['CPM_KECAMATAN_OP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; KAB. PESAWARAN</td>
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
		// var_dump($DATA['pajak_atr']['0']['CPM_NAMA_OP']);die;
		// 				echo '<pre>';
		// print_r($DATA);
		// echo '</pre>';
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

		$pdf->Output('skpd-reklame.pdf', 'I');
	}

	public function print_skpd_20190225()
	{
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
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);
		$query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($d = mysqli_fetch_assoc($res)) {
			$DATA['CPM_TGL_JATUH_TEMPO'] = $d['expired_date'];
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
		$BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
		$NIP = $config['BAG_VERIFIKASI_NIP'];

		$DATA['pajak_atr'] = $DATA['pajak_atr'][0];
		unset($DATA['pajak_atr'][0]);

		$d = explode('/REK/', $DATA['CPM_NO']);
		$NO_URUT = $d[0] . '<br/>/REK/' . $d[1];

		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"310\" valign=\"top\" align=\"center\" colspan=\"3\">
										<table border=\"0\" width=\"310\">
											<tr>
												<td width=\"70\"></td>
												<td width=\"250\">
													<b style=\"font-size:26px\"><br/>
													PEMERINTAH " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
													{$NAMA_PENGELOLA}<br/>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
													<br/>
													{$JALAN}<br/>
													{$KOTA} - {$PROVINSI}
													</b>
												</td>
											</tr>
										</table>
										<br/>
                                    </td>
                                    <td width=\"300\" valign=\"top\" align=\"center\">
										<b style=\"font-size:35px\"><br/>
										SKPD<br/>
										SURAT KETETAPAN PAJAK DAERAH<br/>
										</b>&nbsp;&nbsp;
										<table border=\"0\" width=\"200\" cellpadding=\"0\">
											<tr>
												<td align=\"left\" width=\"80\">Masa Pajak</td>
												<td width=\"10\">:</td>
												<td width=\"195\"  align=\"left\" >" . number_format($DATA['CPM_MASA_PAJAK'], 0) . " {$DATA['CPM_JNS_MASA_PAJAK']}</td>
											</tr>
											<tr>
												<td align=\"left\" ></td>
												<td></td>
												<td align=\"left\" >{$DATA['pajak_atr']['CPM_ATR_BATAS_AWAL']} s.d {$DATA['pajak_atr']['CPM_ATR_BATAS_AKHIR']}</td>
											</tr>
											<tr>
												<td align=\"left\" >Tahun</td>
												<td>:</td>
												<td align=\"left\" >{$DATA['CPM_TAHUN_PAJAK']}</td>
											</tr>
										</table>
                                    </td>

                                    <td width=\"100\" valign=\"top\" align=\"center\">
										<br/><br/>
										<b>No. SKPD :</b> <br/>
										{$no_urut}<br/>
										
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
												<td width=\"550\">: {$DATA['CPM_NAMA_WP']}</td>
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
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
							<tr>
								<td colspan=\"2\">
									<table width=\"500\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\">
										<tr style=\"background-color:#CCC\">
											<td width=\"30\" align=\"center\">NO.</td>
											<td width=\"170\" align=\"center\">DASAR HUKUM</td>
											<td width=\"300\" align=\"center\">JENIS PAJAK DAERAH</td>
											<td width=\"200\" align=\"center\">JUMLAH<br/>Rp.</td>
										</tr>";


		$html = "<tr>
											<td align=\"center\" rowspan=\"5\">1.</td>
											<td align=\"left\" rowspan=\"5\">
												Peraturan Daerah " . ucwords(strtolower($JENIS_PEMERINTAHAN . " " . $NAMA_PEMERINTAHAN)) . " No. 08 Tahun 2012 Tentang Penyelenggaraan Reklame
											</td>
											<td align=\"left\">
												<b>Pajak Reklame {$DATA['CPM_TAHUN_PAJAK']}</b> atas pemasangan
												{$DATA['pajak_atr']['nmrek']} yang bertema <b>'{$DATA['pajak_atr']['CPM_ATR_JUDUL']}'</b><br/>
												dengan perincian sebagai berikut :<br/>
												1. Lokasi Pemasangan<br/>
												&nbsp;&nbsp;&nbsp;

												<table border=\"0\">
													<tr>
														<td>{$DATA['pajak_atr']['CPM_ATR_LOKASI']}</td>
													</tr>
												</table><br/><br/>

											</td>
											<td align=\"right\"></td>
										</tr>
										<tr>
											<td align=\"left\">
												Pokok Penetapan Pajak<br/>
												<table>
													<tr>
														<td>" . strip_tags($DATA['pajak_atr']['CPM_ATR_PERHITUNGAN'], '<br><br/>') . "</td>
													</tr>
												</table>
											</td>
											<td align=\"right\">
												" . number_format($DATA['CPM_TOTAL_OMZET'], 2) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												Jumlah Pokok penetapan
											</td>
											<td align=\"right\">
												<b>" . number_format($DATA['CPM_TOTAL_OMZET'], 2) . "</b>
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												SK Pengurangan {$DATA['CPM_SK_DISCOUNT']} (" . number_format($DATA['CPM_DISCOUNT'], 0) . "%)
											</td>
											<td align=\"right\">
												" . ($DATA['CPM_DISCOUNT'] < 1 ? 0 : "- " . number_format($DATA['CPM_TOTAL_OMZET'] * $DATA['CPM_DISCOUNT'] / 100, 2)) . "
											</td>
										</tr><tr>
											<td align=\"left\">
												<b>Jumlah yang harus dibayar</b>
											</td>
											<td align=\"right\">
												<b>" . number_format($DATA['CPM_TOTAL_PAJAK'], 2) . "</b>
											</td>
										</tr>
										<tr>
											<td align=\"left\" colspan=\"4\">
											Jumlah dengan huruf :<br/>
											<b><i>" . ucfirst($DATA['CPM_TERBILANG']) . "</i></b>
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
											<td>&nbsp;&nbsp;&nbsp;1. Harap Penyetoran dilakukan melalui Bank yang  ditunjuk<br/>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											dengan menggunakan Surat Setoran Pajak Daerah (SSPD)</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Apabila SKPD ini tidak atau kurang dibayar setelah tanggal jatuh tempo dikenakan Sanksi Administrasi Bunga<br/>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											sebesar 2% perbulan.</td>
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
										A.n Kepala Badan Pengelolaan Keuangan Daerah <br/>
										Kepala Bidang Pendataan " . ucwords(strtolower($JENIS_PEMERINTAHAN . " " . $NAMA_PEMERINTAHAN)) . ",<br/><br/>

										<u>{$BAG_VERIFIKASI_NAMA}</u><br/>
										NIP. {$NIP}
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
												<td colspan=\"3\"><table width=\"500\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
														<tr>
															<td width=\"150\">Tanggal Proses</td>
															<td>: {$DATA['CPM_TRAN_CLAIM_DATETIME']}</td>
														</tr>
													</table>
													<table width=\"500\" cellpadding=\"3\" border=\"0\" cellspacing=\"0\">
														<tr>
															<td width=\"480\"></td>
															<td width=\"70\">No. SKPD</td>
															<td width=\"145\">: {$DATA['CPM_NO']}</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td colspan=\"2\" width=\"700\" align=\"center\">TANDA TERIMA<br/></td>
											</tr>
											<tr>
												<td width=\"150\">Nama</td>
												<td width=\"260\" colspan=\"2\">: {$DATA['CPM_NAMA_WP']}</td>
											</tr>
											<tr>
												<td width=\"150\">Alamat</td>
												<td width=\"260\" colspan=\"2\">: {$DATA['CPM_ALAMAT_WP']}</td>
											</tr>
											<tr>
												<td width=\"150\">NPWPD</td>
												<td width=\"260\" colspan=\"2\">: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td width=\"410\" colspan=\"2\"></td>
												<td width=\"240\" align=\"center\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
												Yang Menerima,<br/></td>
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
		// echo $page1; exit;
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
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 9, 12, 17, '', '', '', '', false, 300, '', false);

		$pdf->Output('skpd-reklame.pdf', 'I');
	}

	public function print_skpd_basev2()
	{
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
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//select_db($dbName, $Conn_gw);
		$query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($d = mysqli_fetch_assoc($res)) {
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

		$DATA['pajak_atr'] = $DATA['pajak_atr'][0];
		unset($DATA['pajak_atr'][0]);

		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"710\" valign=\"top\" align=\"center\" colspan=\"3\">
										<b style=\"font-size:40px\">
										" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
										BADAN PENGELOLAAN KEUANGAN DAERAH<br/>
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
										{$rows['CPM_NOP']}<br/>
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
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
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
														<td width=\"90\" align=\"right\">{$DATA['pajak_atr']['CPM_ATR_PANJANG']} m</td>
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
												" . number_format($DATA['pajak_atr']['CPM_ATR_JUMLAH'], 0) . " buah
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
											" . ucfirst($DATA['CPM_TERBILANG']) . "
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
										a.n. KEPALA BADAN PENGELOLAAN KEUANGAN DAERAH<br/>
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
												<td width=\"260\" colspan=\"2\">: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
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

		$pdf->AddPage('P', 'F4');
		$pdf->writeHTML($page1, true, false, false, false, '');
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 27, 9, 12, '', '', '', '', false, 300, '', false);

		$pdf->Output('skpd-reklame.pdf', 'I');
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
		$BANK = $config['BANK'];

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
                            Yth. Kepala Badan Pengelolaan Keuangan Daerah<br/>
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
                                    <td>: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
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
									" . strtoupper($DATA['pajak_atr'][0]['CPM_ATR_JUDUL']) . "<br/>\n
								</td>
								<td>{$DATA['pajak_atr'][0]['CPM_ATR_LOKASI']}</td>
								<td>
									Panjang : {$DATA['pajak_atr'][0]['CPM_ATR_PANJANG']} M <br/>\n
									Lebar : {$DATA['pajak_atr'][0]['CPM_ATR_LEBAR']} M <br/>\n
									Muka :  " . number_format($DATA['pajak_atr'][0]['CPM_ATR_MUKA'], 0) . " <br/>\n
								</td>
								<td>" . number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'], 0) . "</td>
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
		$NIP = $config['BAG_VERIFIKASI_NIP'];

		
        $pemerintah = explode(' ', $JENIS_PEMERINTAHAN);
        $pemerintah_label = strtoupper($pemerintah[0]);
        $pemerintah_jenis = strtoupper($pemerintah[1]);

		$TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
		$tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);

		$config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
		$persen_terlambat_lap = $config_terlambat_lap->persen;
		$editable_terlambat_lap = $config_terlambat_lap->editable;

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
										<br/><br/><br/>
                                        <b>" . $pemerintah_label . "<br/>" . $pemerintah_jenis . ' ' . strtoupper($NAMA_PEMERINTAHAN) . "</b>
                                    </td>
                                    <td width=\"310\" valign=\"top\" align=\"center\">
										<b style=\"font-size:55px\">S P T P D</b><br/>
                                        (SURAT PEMBERITAHUAN PAJAK DAERAH)
                                        <b style=\"font-size:55px\">PAJAK REKLAME</b><br/>
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
												Kepala Badan Pendapatan Daerah<br/>
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
							<b>A. IDENTITAS WAJIB PAJAK</b>
						</td>
					</tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"7\">
							<tr>
								<td><table width=\"100%\" border=\"0\" align=\"left\">
										<tr>
											<td width=\"200\">A. NAMA</td>
											<td width=\"500\">: {$DATA['profil']['CPM_NAMA_WP']}</td>
										</tr>
										<tr>
											<td>B. ALAMAT</td>
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
										  </tr>";
		foreach ($DATA['pajak_atr'] as $no => $atr) {
			++$no;
			$page1 .= "<tr>
											<td align=\"right\">{$no}.</td>
											<td align=\"left\">
												{$atr['CPM_ATR_REKENING']}<br/>\n
												{$atr['nmrek']}<br/>\n
												" . strtoupper($atr['CPM_ATR_JUDUL']) . "\n
											</td>
											<td>{$atr['CPM_ATR_LOKASI']}</td>
											<td>
												P : {$atr['CPM_ATR_PANJANG']} m <br/>\n
												L : {$atr['CPM_ATR_LEBAR']} m <br/>\n
												Tinggi :  " . number_format($atr['CPM_ATR_TINGGI'], 0) . "\n
											</td>
											<td>" . number_format($atr['CPM_ATR_JUMLAH'], 0) . "</td>
											<td>
												{$atr['CPM_ATR_BATAS_AWAL']} <br/>s/d<br/> {$atr['CPM_ATR_BATAS_AKHIR']}
											</td>
										  </tr>";
		}
		$page1 .= "</table><br/>
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
												<td width=\"260\" colspan=\"2\">: {$tanggal_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Petugas</td>
												<td width=\"260\" colspan=\"2\">: {$petugas_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">NIP.</td>
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td colspan=\"3\"><br/><br/></td>
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
		$pdf->SetMargins(5, 5, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('P', 'F4');
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 27, 7, 14, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($page1, true, false, false, false, '');

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
        $result = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($result);
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
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui teller dan ATM Bank Nagari terdekat</td>
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
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 9, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-reklame.pdf', 'I');
    }
*/

	public function print_sspd()
	{
		global $sRootPath, $qrisLib;
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
		$PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);

		$BANK = $config['BANK'];
		$BANK_ALAMAT = $config['BANK_ALAMAT'];
		$BANK_NOREK = $config['BANK_NOREK'];

		$BENDAHARA_NAMA = $config['BENDAHARA_NAMA'];
		$BENDAHARA_NIP  = $config['BENDAHARA_NIP'];
		$query = "SELECT a.CPM_ATR_JUDUL,a.CPM_ATR_LOKASI FROM PATDA_REKLAME_DOC_ATR a INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
                  AND b.CPM_ID = '{$this->_id}'";
		$result = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_assoc($result);

		//get payment code
		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbTable = $config['PATDA_TABLE'];
		$dbUser = $config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);

		$gw = $this->get_gw_byid($Conn_gw, $this->CPM_ID);

		/// Add QRIS By d3Di ================================================

		$id_switching = $gw->id_switching;
		$datetimenow = date('Y-m-d H:i:s');
		$query4 = "SELECT qr FROM simpatda_qris WHERE id_switching='$id_switching' AND expired_date_time>='$datetimenow' ORDER BY id DESC LIMIT 0, 1";
		$r = mysqli_query($Conn_gw, $query4);
		$nx = mysqli_num_rows($r);

		$QRCodeSVG = false;
		if ($nx > 0 && $gw->payment_flag == 0) {
			$r = mysqli_fetch_array($r);
			$QRCodeSVG = $qrisLib->getBarcodeSVG($r['qr'], 'QRCODE', 3, 3);
			$icoQRIS = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="210mm" height="77.5mm" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 21000 7750" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs> <style type="text/css"> <![CDATA[ .fil0 {fill:black;fill-rule:nonzero} ]]> </style> </defs>
                            <g id="__x0023_Layer_x0020_1">
                                <metadata id="CorelCorpID_0Corel-Layer"/>
                                <path class="fil0" d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z"/>
                            </g>
                        </svg>';
		}
		//======================================================================


		$DATA['profil']['CPM_NAMA_OP'] = isset($gw->op_nama) ? $gw->op_nama : $DATA['profil']['CPM_NAMA_OP'];
		$DATA['profil']['CPM_ALAMAT_OP'] = isset($gw->op_alamat) ? $gw->op_alamat : $DATA['profil']['CPM_ALAMAT_OP'];

		$PAYMENT_CODE_BANK = $gw->periode;
		$PAYMENT_CODE = $gw->payment_code;
		$DENDA = !empty($gw->patda_denda) ? $gw->patda_denda : 0;

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
		$NPWPD = Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']);

		$TOTAL = $DATA['pajak']['CPM_TOTAL_PAJAK'];
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

		$html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr valign=\"top\">
                                    <th valign=\"top\" width=\"450\" align=\"center\">
										<table border=\"0\">
											<tr>
												<td width=\"80\">&nbsp;</td>
												<td width=\"350\">
													<strong>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
													" . strtoupper($NAMA_PENGELOLA) . "<br /><br />
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
                                         <br/><br/>
                                         <span style=\"margin:0px;!important;font-size:42px;font-weight:bold\">KODE BAYAR<br/>{$PAYMENT_CODE}</span>
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"450\"></td>
									<td width=\"310\"><table>
											<tr>
												<td width=\"80\"><strong>Nomor</strong></td>
												<td><strong>: {$DATA['pajak']['CPM_NO']}</strong></td>
											</tr>
											<tr>
												<td><strong>Tanggal</strong></td>
												<td><strong>: {$this->formatDateForDokumen($TGL_PENETAPAN)}</strong></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td><table width=\"1040\" border=\"0\" cellpadding=\"5\">
											<tr>
												<td width=\"180\">Nama</td>
												<td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_NAMA_WP']) . "</td>
											</tr>
											<tr style=\"vertical-align: top\">
												<td>Alamat</td>
												<td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_ALAMAT_WP']) . "</td>
											</tr>
											<tr>
												<td>Nama Usaha</td>
												<td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_NAMA_OP']) . "</td>
											</tr>
											<tr style=\"vertical-align: top\">
												<td>Alamat Usaha</td>
												<td colspan=\"3\">: " . strtoupper($DATA['profil']['CPM_ALAMAT_OP']) . "</td>
											</tr>
											<tr>
												<td>NPWPD</td>
												<td colspan=\"3\">: " . $NPWPD . "</td>
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
												<td width=\"200\">: <i>{$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL']} - {$DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR']}</i></td>
												<td width=\"60\"><i>Tahun</i></td>
												<td width=\"265\">: <i>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</i></td>
											</tr>					
											<tr>
												<td>Bank Penerima Setoran</td>
												<td>: " . strtoupper($BANK) . "</td>
												<td>No. Rek</td>
												<!-- <td>: {$BANK_NOREK}</td> -->
												<td>: 383.202.10.00006 </td>
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
                                                <th width=\"30\" align=\"center\">No.</th>
                                                <th width=\"130\" align=\"center\">Kode Rekening</th>
                                                <th width=\"400\" align=\"center\">Jenis Pajak</th>
                                                <th width=\"150\" align=\"center\">Nilai (Rp.)</th>
											</tr>";
		foreach ($DATA['pajak_atr'] as $no => $atr) {
			$no++;
			$html .= "<tr>
                                                <td align=\"center\">{$no}.</td>
                                                <td align=\"center\">
													{$atr['CPM_ATR_REKENING']}
                                                </td>
                                                <td>
													{$atr['nmrek']}<br/>\n
													Judul Reklame : {$atr['CPM_ATR_JUDUL']},<br/>\n
													Lokasi : {$atr['CPM_ATR_LOKASI']},<br/>\n
													Panjang : {$atr['CPM_ATR_PANJANG']} m,
													Lebar : {$atr['CPM_ATR_LEBAR']} m,
													Tinggi :  " . number_format($atr['CPM_ATR_TINGGI'], 0) . ",<br/>
													Jumlah : " . number_format($atr['CPM_ATR_JUMLAH'], 0) . ",
													Lama : {$DATA['pajak']['CPM_MASA_PAJAK']} {$DATA['pajak']['CPM_JNS_MASA_PAJAK']}
												</td>
                                                <td align=\"right\">
												" . number_format($atr['CPM_ATR_TOTAL'], 2) . "</td>
                                            </tr>";
		}
		$html .= $gw->payment_flag == 1 ?

			"<tr>
                                                <td></td>
                                                <td align=\"left\">
                                                </td>
                                                <td>
													Denda
                                                </td>
                                                <td align=\"right\">" . number_format($DENDA, 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td align=\"right\" colspan=\"3\">Jumlah</td>
                                                <td align=\"right\" colspan=\"1\">" . number_format($gw->patda_total_bayar, 2) . "</td>
                                            </tr>"
			:
			"
                                            <tr>
                                                <td></td>
                                                <td align=\"left\">
                                                </td>
                                                <td>
													Denda Pajak
                                                </td>
                                                <td align=\"right\">" . number_format($DENDA, 2) . "</td>
                                            </tr>

                                            <tr>
                                                <td align=\"right\" colspan=\"3\">Jumlah</td>
                                                <td align=\"right\" colspan=\"1\">" . number_format($TOTAL) . "</td>
                                            </tr>";


		$html .= " <tr>
                                                <td colspan=\"4\">
                                                    Terbilang : <i>" . ucwords($this->SayInIndonesian($TOTAL)) . " Rupiah</i>
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
                                    <td width=\"710\" align=\"center\">
									{$KOTA}, " . date("d") . " " . strtoupper($this->arr_bulan[(int) date("m")]) . " " . date("Y") . "<br/>
									Penyetor<br/><br/><br/><br/><br/><br/>
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
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui {$BANK} terdekat</td>
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
					<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>

                </table>";

		require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('9 PAJAK ONLINE');
		$pdf->SetSubject('spppd');
		$pdf->SetKeywords('9 PAJAK ONLINE');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 9, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);


		$pdf->AddPage('P', 'F4');
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 10, 12, 20, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($html, true, false, false, false, '');
		/// QRIS   ==============
		if ($QRCodeSVG && $NPWPD != '' && $NPWPD != null) {
			$pdf->ImageSVG('@' . $QRCodeSVG, $x = 172, $y = 112, $w = 32, $h = 32, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
			$pdf->ImageSVG('@' . $icoQRIS, $x = 183, $y = 105, $w = 10, $h = 10, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
		}
		// ======================
		$pdf->SetAlpha(0.3);

		$pdf->Output('sspd-reklame.pdf', 'I');
	}

	public function print_nota_hitung()
	{
		global $sRootPath;
		$this->_id = $this->CPM_ID;
		$DATA = $this->get_pajak();

		$pajak_atr = $DATA['pajak_atr'];
		$DATA = (object) array_merge($DATA['pajak'], $DATA['profil']); //, $DATA['pajak_atr'][0]);

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
		#if ($DATA->CPM_TIPE_PAJAK'] == 2) {
		$KODE_PAJAK = $this->non_reguler[$this->id_pajak];
		#}
		$KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
		$DATA->CPM_NO_SSPD = $DATA->CPM_NO;
		$PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);

		$rekening = $this->get_list_rekening($DATA->CPM_ATR_REKENING);
		$list_type_masa = $this->get_type_masa();

		$DATA->CPM_ATR_MUKA = (int) $DATA->CPM_ATR_MUKA;
		$DATA->CPM_ATR_JUMLAH = (int) $DATA->CPM_ATR_JUMLAH;
		$DATA->CPM_ATR_REKENING = substr($DATA->CPM_ATR_REKENING, 0, 10);

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

		$html = "<table width=\"1015\" class=\"main\" border=\"1\">
					<tr>
						<td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
								<tr>
									<th valign=\"top\" width=\"370\" align=\"center\">
										<table cellpadding=\"0\" border=\"0\"><tr><td width=\"100\"></td>
										<td width=\"250\"><b><font size=\"+1\">" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
										" . strtoupper($NAMA_PENGELOLA) . "</font></b><br /><br />
										<font class=\"normal\">{$JALAN}</font></td>
										</tr></table>
									</th>
									<th width=\"330\" align=\"center\">
										<br><b>NOTA PERHITUNGAN PAJAK<br/>
										Tahun : {$DATA->CPM_TAHUN_PAJAK}</b>
										<!--Batas Penyetoran terakhir tanggal: 13 Maret 2021-->
									</th>
									<th width=\"315\"><table>
										<tr><td>No. Kohir</td><td>: {$DATA->CPM_NO_SSPD}</td></tr>
										<tr><td>No. SPT yang dikirim</td><td>: ......................................</td></tr>
									</table></th>
								 </tr>
								<tr>
									<td><table cellpadding=\"1\" border=\"0\">
									<tr><td width=\"70\">Nama WP</td><td width=\"7\">:</td><td width=\"275\">{$DATA->CPM_NAMA_WP}</td></tr>
									<tr><td>Nama OP</td><td>:</td><td>{$DATA->CPM_NAMA_OP}</td></tr>
									</table></td>
									<td><table cellpadding=\"1\" border=\"0\"><tr>
										<td width=\"70\">Alamat</td><td width=\"7\">:</td><td width=\"250\">{$DATA->CPM_ALAMAT_WP} - {$DATA->CPM_KELURAHAN_WP}<br>KEC. {$DATA->CPM_KECAMATAN_WP}<br>KAB. {$DATA->CPM_KOTA_WP}</td>
									</tr></table></td><td><table cellpadding=\"1\" border=\"0\"><tr>
										<td width=\"80\">NPWPD</td><td width=\"180\">: " . Pajak::formatNPWPD($DATA->CPM_NPWPD) . "</td>
									</tr></table></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table width=\"1015\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:28px\">
								<tr>
									<td><table width=\"1015\" border=\"1\" cellpadding=\"3\">
											<tr>
												<th width=\"30\" rowspan=\"2\" align=\"center\"><br><br><b>NO.</b></th>
												<th width=\"150\" rowspan=\"2\" align=\"center\"><br><br><b>JENIS PAJAK</b></th>
												<th width=\"120\" rowspan=\"2\" align=\"center\"><br><br><b>AYAT</b></th>
												<th width=\"400\" colspan=\"2\" align=\"center\"><b>DASAR PENGENAAN</b></th>
												<th width=\"75\" rowspan=\"2\" align=\"center\"><br><br><b>TARIF</b></th>
												<th width=\"120\" rowspan=\"2\" align=\"center\"><b>KETETAPAN<br/>(Rp.)</b></th>
												<th width=\"120\" rowspan=\"2\" align=\"center\"><b>JUMLAH<br/>(Rp.)</b></th>
											</tr>
											<tr>
												<th width=\"200\" align=\"center\"><b>Uraian</b></th>
												<th width=\"200\" align=\"center\"><b>Banyak/Nilai</b></th>
											</tr>";
		// echo'<pre>';print_r($pajak_atr);exit;
		$row_atr = '';
		foreach ($pajak_atr as $no => $atr) {
			$row_atr .= "<tr>
												<td align=\"center\">" . ($no + 1) . ".</td>
												<td align=\"center\">{$atr['nmrek']}</td>
												<td align=\"center\">{$atr['CPM_ATR_REKENING']}</td>
												<td>[{$atr['CPM_NOP']}] {$atr['CPM_NAMA_OP']}</td>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
											</tr>
											<tr>
												<td></td>
												<td></td>
												<td></td>
												<td>Pembayaran Pajak Reklame Periode {$atr['CPM_ATR_BATAS_AWAL']} s.d. {$atr['CPM_ATR_BATAS_AKHIR']}</td>
												<td>{$atr['CPM_ATR_LEBAR']} M x {$atr['CPM_ATR_PANJANG']} M x {$atr['CPM_ATR_TINGGI']} Tinggi x {$atr['CPM_ATR_SISI']} Muka x {$DATA->CPM_MASA_PAJAK} {$DATA->CPM_JNS_MASA_PAJAK}</td>
												<td align=\"center\">" . number_format($atr['CPM_ATR_HARGA']) . " / {$DATA->CPM_JNS_MASA_PAJAK} / M<sup>2</sup></td>
												<td align=\"right\">" . number_format($atr['CPM_ATR_TOTAL']) . "</td>
												<td align=\"right\">" . number_format($atr['CPM_ATR_TOTAL']) . "</td>
											</tr>";
		}
		$html .= $row_atr;
		$html .= "<tr>
												<td colspan=\"6\" align=\"right\" style=\"border:none\"><b>DENDA</b></td>
												<td align=\"right\"><b>0</b></td>
												<td align=\"right\"><b>" . number_format($DATA->CPM_DENDA_TERLAMBAT_LAP) . "</b></td>
											</tr>
											<tr>
												<td colspan=\"6\" align=\"right\" style=\"border:none\"><b>JUMLAH</b></td>
												<td align=\"right\"><b>" . number_format($DATA->CPM_TOTAL_OMZET) . "</b></td>
												<td align=\"right\"><b>" . number_format($DATA->CPM_TOTAL_PAJAK) . "</b></td>
											</tr>
										</table>

										<table width=\"1015\" border=\"0\" cellpadding=\"3\">
											<tr>
												<td width=\"595\" align=\"right\" style=\"border:none\">Jumlah dengan huruf </td>
												<td width=\"420\">(" . ucwords($this->SayInIndonesian($DATA->CPM_TOTAL_PAJAK)) . " Rupiah)</td>
											</tr>
										</table>
										<br/>
										<table width=\"1015\" border=\"0\" cellpadding=\"3\">
											<tr>
												<td>
													<table width=\"299\" border=\"0\">
														<tr>
														  <td width=\"289\" align=\"center\">Mengetahui,
															<br/>A.n Kepala badan Pendapatan Daerah <br> Kabupaten Pesawaran<br> Kepala Bidang Pajak Daerah Lain</td>
														</tr>
														<tr>
														  <td><p>&nbsp;</p>
															<p>&nbsp;</p></td>
														</tr>
														<br/>
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
																Diperiksa oleh :<br/>Kasubid Perhitungan dan Penetapan<br><br><br>
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
														  <td>: {$DATA->CPM_TGL_LAPOR}</td>
														</tr>
														<tr>
														  <td>Oleh</td>
														  <td>: </td>
														</tr>
														<tr>
														  <td>Tanda Tangan</td>
														  <td>: </td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								
								<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
							</table>                            ";

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
		$pdf->SetMargins(5, 10, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('L', 'A4');
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 12, 17, 17, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($html, true, false, false, false, '');
		$pdf->SetAlpha(0.3);

		$pdf->Output('sspd-nota-hitung.pdf', 'I');
	}

	public function print_nota_hitung_()
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

		$PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);

		$query = "SELECT a.*,b.CPM_KETERANGAN FROM PATDA_REKLAME_DOC_ATR a INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
                  AND b.CPM_ID = '{$this->_id}'";
		$result = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_assoc($result);
		#print_r($DATA);exit;
		$html = "<table width=\"1015\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"300\" align=\"center\">
                                        <b>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        " . strtoupper($NAMA_PENGELOLA) . "<br /><br />
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
								<td>NPWPD : " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
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
													{$data['CPM_ATR_PANJANG']}m x
													{$data['CPM_ATR_LEBAR']}m x
													{$data['CPM_ATR_MUKA']}mk x {$DATA['pajak']['CPM_TARIF_PAJAK']}%<br/><br/>
													Masa : {$data['CPM_ATR_BATAS_AWAL']} s/d {$data['CPM_ATR_BATAS_AKHIR']}<br/><br/>
													{$data['CPM_KETERANGAN']}<br><br>
													Lokasi : {$data['CPM_ATR_LOKASI']}
												</td>
												<td>{$data['CPM_ATR_JUMLAH']} unit</td>
												<td align=\"right\"><!--" . number_format($data['CPM_ATR_BIAYA'], 2) . " x {$DATA['pajak']['CPM_TARIF_PAJAK']}-->{$data['CPM_ATR_PANJANG']}m x
													{$data['CPM_ATR_LEBAR']}m x
													{$data['CPM_ATR_MUKA']}mk</td>
												<td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
												<td align=\"right\">" . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
												<td align=\"right\"></td>
                                            </tr>
                                            <tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\">JUMLAH</td>
												<td colspan=\"3\" align=\"center\">Rp. " . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                        </table>

                                        <table width=\"1015\" border=\"0\" cellpadding=\"3\">
                                            <tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\"><font size=\"-2\">Jumlah dengan huruf </font></td>
												<td colspan=\"3\"><font size=\"-2\">(" . ucwords($this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah)</font></td>
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


	public function print_nota_bongkar()
	{
		global $sRootPath;
		$this->_id = $this->CPM_ID;
		$DATA = $this->get_pajak();

		$DATA = (object) array_merge($DATA['pajak'], $DATA['profil'], $DATA['pajak_atr'][0]);
		$kdrek = $DATA->CPM_ATR_REKENING;

		if ($kdrek == '4.1.1.4.01.1' || $kdrek == '4.1.1.4.01.2') {
			//Reklame Papan/BillBoard/Baliho/Neonbox
			//Reklame Videotron/Megatron
			$nilai_jaminan_bongkar = 15;
		} else {
			$nilai_jaminan_bongkar = 5;
		}

		$TOTAL_PAJAK = $DATA->CPM_TOTAL_PAJAK + (($nilai_jaminan_bongkar / 100) * $DATA->CPM_TOTAL_PAJAK);

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
		#if ($DATA->CPM_TIPE_PAJAK'] == 2) {
		$KODE_PAJAK = $this->non_reguler[$this->id_pajak];
		#}
		$KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);
		$DATA->CPM_NO_SSPD = $DATA->CPM_NO;
		$PERIODE = substr($this->CPM_NO, 14, 2) . "0" . substr($this->CPM_NO, 0, 9);

		$rekening = $this->get_list_rekening($DATA->CPM_ATR_REKENING);
		$list_type_masa = $this->get_type_masa();
		$kelas = array();

		$query = "SELECT a.*,b.CPM_KETERANGAN FROM PATDA_REKLAME_DOC_ATR a
					INNER JOIN PATDA_REKLAME_DOC b WHERE a.CPM_ATR_REKLAME_ID = b.CPM_ID
				  AND b.CPM_ID = '{$this->_id}'";
		$result = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_array($result);

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

		#print_r($DATA);exit;
		$html = "<table width=\"1015\" class=\"main\" border=\"1\">
					<tr>
						<td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
								<tr>
									<th valign=\"top\" width=\"300\" align=\"center\">
										<b>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
										" . strtoupper($NAMA_PENGELOLA) . "<br /><br />
										<font class=\"normal\">{$JALAN}<br/>
										{$KOTA} - {$PROVINSI} {$KODE_POS}</font></b>
									</th>
									<th width=\"415\" align=\"center\">
										<b>NOTA PERHITUNGAN PAJAK<br/>
										Tahun : {$DATA->CPM_TAHUN_PAJAK}</b>
									</th>
									<th width=\"300\" align=\"center\">
										<b>Nomor Nota Perhitungan :<br/>
										{$DATA->CPM_NO_SSPD}</b>
									</th>
								 </tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><table width=\"1015\" border=\"0\" cellpadding=\"5\">
							<tr>
								<td>Nama : {$DATA->CPM_NAMA_WP}</td>
								<td>Alamat : {$DATA->CPM_ALAMAT_WP}</td>
								<td>NPWPD : " . Pajak::formatNPWPD($DATA->CPM_NPWPD) . "</td>
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
											<tr style=\"font-size:30px\">
												<td>1.</td>
												<td>Pajak Reklame<br/>
												{$DATA->CPM_ATR_REKENING}<br/>\n
												{$DATA->nmrek}<br/>\n
												</td>
												<td>
													NOP : {$DATA->CPM_NOP}<br/>
													<u>{$DATA->CPM_ATR_JUDUL}</u><br/>
													({$DATA->CPM_NAMA_OP} - {$DATA->CPM_ALAMAT_OP})<br/><br/>

													Lebar : {$DATA->CPM_ATR_LEBAR} m <br/>
													Panjang : {$DATA->CPM_ATR_PANJANG} m<br/>
													Tinggi : {$DATA->CPM_ATR_TINGGI} m<br/>
													Masa : {$DATA->CPM_ATR_BATAS_AWAL} s/d {$DATA->CPM_ATR_BATAS_AKHIR}<br/>
													Lama : {$DATA->CPM_MASA_PAJAK} {$DATA->CPM_JNS_MASA_PAJAK}<br/>
													{$DATA->CPM_KETERANGAN}<br>
													Lokasi : {$DATA->CPM_ATR_LOKASI}
												</td>
												<td>{$DATA->CPM_ATR_JUMLAH} unit</td>
												<td align=\"right\">
													{$DATA->CPM_TARIF_PAJAK}%
												</td>
												<td align=\"right\">" . number_format($DATA->CPM_TOTAL_PAJAK, 2) . "</td>
												<td align=\"right\">" . number_format($DATA->CPM_DENDA_TERLAMBAT_LAP, 2) . "</td>
												<td align=\"right\"></td>
											</tr>
											<tr style=\"font-size:30px\">
												<td>2.</td>
												<td>Jaminan Bongkar</td>
												<td>{$nilai_jaminan_bongkar}% X Nilai Ketetapan
												</td>
												<td>{$DATA->CPM_ATR_JUMLAH} unit</td>
												<td align=\"right\">{$nilai_jaminan_bongkar}% X " . number_format($DATA->CPM_TOTAL_PAJAK, 2) . "</td>
												<td align=\"right\">" . number_format(($nilai_jaminan_bongkar / 100) * $DATA->CPM_TOTAL_PAJAK, 2) . "</td>
												<td align=\"right\">" . number_format($DATA->CPM_DENDA_TERLAMBAT_LAP, 2) . "</td>
												<td align=\"right\"></td>
											</tr>
											<tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\">JUMLAH</td>
												<td colspan=\"3\" align=\"center\">Rp. " . number_format($TOTAL_PAJAK, 2) . "</td>
											</tr>
										</table>

										<table width=\"1015\" border=\"0\" cellpadding=\"3\">
											<tr>
												<td colspan=\"5\" align=\"right\" style=\"border:none\"><font size=\"-2\">Jumlah dengan huruf </font></td>
												<td colspan=\"3\"><font size=\"-2\">(" . ucwords($this->SayInIndonesian($TOTAL_PAJAK)) . " Rupiah)</font></td>
											</tr>
										</table>
										<br/>
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
														  <td>: {$DATA->CPM_TGL_LAPOR}</td>
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
								
								<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>
							</table>                            ";

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
		$pdf->SetMargins(5, 10, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('L', 'A4');
		$pdf->writeHTML($html, true, false, false, false, '');
		$pdf->SetAlpha(0.3);

		$pdf->Output('sspd-nota-bongkar.pdf', 'I');
	}

	public function read_dokumen()
	{
		if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
			$idtran = $_REQUEST['idtran'];
			$select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_REKLAME_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
			$result = mysqli_query($this->Conn, $select);
			$data = mysqli_fetch_assoc($result);

			$read = $data['CPM_TRAN_READ'];
			$read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
			$query = "UPDATE {$this->PATDA_REKLAME_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
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
                    FROM {$this->PATDA_REKLAME_DOC} pj INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
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

	public function list_lokasi_reklame()
	{
		$id = $_POST['id'];
		$harga = $_POST['harga'];

		$query = "SELECT B.*,A.CPM_KETERANGAN FROM PATDA_REKLAME_TYPE_LOKASI A INNER JOIN PATDA_REKLAME_LOKASI B ON A.CPM_LOKASI_ID = B.CPM_LOKASI_ID
                    WHERE CPM_TYPE_ID='{$id}'";
		$result = mysqli_query($this->Conn, $query);
		$respon = array();
		$respon['option_lokasi'] = "";
		while ($data = mysqli_fetch_assoc($result)) {
			$respon['option_lokasi'] .= "<option value='{$data['CPM_LOKASI_ID']}' harga='{$data['CPM_LOKASI_HARGA']}'>{$data['CPM_LOKASI_NAMA']} - {$data['CPM_LOKASI_HARGA']}</option>";
			$respon['keterangan'] = $data['CPM_KETERANGAN'];
		}
		$respon['harga'] = $harga;

		echo $this->Json->encode($respon);
	}

	public function list_type_reklame()
	{
		$id = $_POST['id'];

		$query = "SELECT B.*,A.CPM_HARGA FROM PATDA_REKLAME_TYPE_LOKASI A LEFT JOIN PATDA_REKLAME_TYPE B ON A.CPM_TYPE_ID = B.CPM_TYPE_ID
                    WHERE CPM_LOKASI_ID='{$id}'";
		$result = mysqli_query($this->Conn, $query);
		$respon = array();
		$respon['option_type'] = "<option></option>";
		$null = 0;
		while ($data = mysqli_fetch_assoc($result)) {
			$null += (isset($data['CPM_TYPE_ID'])) ? 1 : 0;
			$respon['option_type'] .= (isset($data['CPM_TYPE_ID'])) ? "<option value='{$data['CPM_TYPE_ID']}' harga='{$data['CPM_HARGA']}'>{$data['CPM_TYPE_NAMA']} - {$data['CPM_HARGA']}</option>" : "";
			$respon['harga'] = $data['CPM_HARGA'];
		}
		$respon['null_type'] = $null;


		echo $this->Json->encode($respon);
	}

	public function get_lokasi_harga()
	{
		$id = $_POST['id'];
		$id_lokasi = $_POST['id_lokasi'];

		$query = "SELECT B.*,A.CPM_HARGA, A.TARIF_NORMAL, A.TARIF_KHUSUS FROM PATDA_REKLAME_TYPE_LOKASI A INNER JOIN PATDA_REKLAME_TYPE B ON A.CPM_TYPE_ID = B.CPM_TYPE_ID
                    WHERE A.CPM_TYPE_ID='{$id}' and CPM_LOKASI_ID='{$id_lokasi}'";
		$result = mysqli_query($this->Conn, $query);
		$data = mysqli_fetch_assoc($result);
		$respon = array();
		$respon['harga'] = $data['CPM_HARGA'];
		$respon['query'] = $query;
		$respon['option_tarif'] = "<option value='{$data['TARIF_NORMAL']}'>- Tarif Normal [{$data['TARIF_NORMAL']}]</option><option value='{$data['TARIF_KHUSUS']}'>- Tarif Khusus [{$data['TARIF_KHUSUS']}]</option>";

		echo $this->Json->encode($respon);
	}

	public function get_permen()
	{
		$sql = sprintf("SELECT * FROM {$this->PATDA_REK_PERMEN13} where nmheader3 = 'Reklame' ORDER BY nmrek ASC");
		$result = mysqli_query($this->Conn, $sql);
		$i = 0;
		$respon = array();
		$data = array();
		while ($data = mysqli_fetch_assoc($result)) {
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

		echo $this->Json->encode(array('items' => $respon));
	}

	public function get_npwpd($term)
	{
		$data['success'] = false;
		$sql = sprintf("SELECT CPM_ID,CPM_NPWPD,CPM_NAMA_WP,CPM_ALAMAT_WP,CPM_NAMA_OP,CPM_ALAMAT_OP,
		CPM_NOP FROM {$this->PATDA_REKLAME_PROFIL} where (CPM_NPWPD like '%%%s%%' OR CPM_NAMA_WP like '%%%s%%')", $term, $term);
		$result = mysqli_query($this->Conn, $sql);
		$i = 0;
		$respon = array();
		$data = array();
		//echo $sql;exit();
		while ($data = mysqli_fetch_assoc($result)) {
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

	public function get_no_sspd($nosspd)
	{
		$sql = "SELECT CPM_NO_SSPD FROM PATDA_REKLAME_DOC WHERE CPM_NO_SSPD='{}'";
		$res = mysqli_query($this->Conn, $sql);
		$ret = array();
		if (!mysqli_result($res))
			return $ret;
		$row = mysqli_fetch_assoc($res);
	}

	private function parseDate($date, $adding = "")
	{ //31/08/2016 to 2016-08-31
		$d = explode("/", $date);
		$date =  "{$d[2]}-{$d[1]}-{$d[0]}";

		if ($adding != "") {
			$date = date('Y-m-d', strtotime($date . $adding));
		}
		return $date;
	}

	public function hitung_masa($params = array(), $type = '')
	{

		if (isset($_POST['startdate']) && isset($_POST['enddate'])) {
			$startdate = $this->parseDate($_POST['startdate']);
			$enddate = $this->parseDate($_POST['enddate'], '+1 day');
		} elseif (isset($params['startdate']) && isset($params['enddate'])) {
			$startdate = $this->parseDate($params['startdate']);
			$enddate = $this->parseDate($params['enddate'], '+1 day');
		} else {
			return false;
		}

		$query = "SELECT
			DATEDIFF('{$enddate}','{$startdate}') as HARI,
			TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') +  DATEDIFF('{$enddate}', '{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') MONTH ) /
			DATEDIFF('{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') + 1 MONTH, '{$startdate}' + INTERVAL TIMESTAMPDIFF(MONTH, '{$startdate}', '{$enddate}') MONTH) as BULAN";

		$res = mysqli_query($this->Conn, $query);

		$response = array(
			'hari' => 0,
			'minggu' => 0,
			'bulan' => 0,
			'tahun' => 0,
			'triwulan' => 0,
			'semester' => 0,
			'durasi' => 0
		);

		if ($data = mysqli_fetch_assoc($res)) {
			$hari = $data['HARI'];
			$minggu = $data['HARI'] / 7;
			$bulan = $data['BULAN'];
			$tahun = $data['BULAN'] / 12;

			$hari = round($hari, 2);
			$minggu = round($minggu, 2);
			$bulan = round($bulan, 2);
			$tahun = round($tahun, 2);

			$triwulan = round($bulan / 3, 2);
			$semester = round($bulan / 6, 2);

			$response['hari'] = $hari;
			$response['minggu'] = $minggu;
			$response['bulan'] = $bulan;
			$response['tahun'] = $tahun;

			$response['triwulan'] = $triwulan;
			$response['semester'] = $semester;

			if ($type != '') {
				$arr = array(
					1 => 'tahun',
					2 => 'semester',
					3 => 'triwulan',
					4 => 'bulan',
					5 => 'minggu',
					6 => 'hari'
				);
				$response['durasi'] = $response[$arr[$type]];
			}
		}
		echo $this->Json->encode($response);
	}
	public function nsl($nslnya = NULL){
	$nsl['Jalan Utama/Jalan Nasional']  
					= ["tarif_pajak" => "Jalan Utama/Jalan Nasional", "nilai_poin" => 0.25];
	$nsl['Jalan Provinsi']  
					= ["tarif_pajak" => "Jalan Provinsi"			, "nilai_poin" => 0.20];
	$nsl['Jalan Kabupaten']  
					= ["tarif_pajak" => "Jalan Kabupaten"			, "nilai_poin" => 0.15];
	$nsl['Jalan lainnya/Jalan Desa']  
					= ["tarif_pajak" => "Jalan lainnya/Jalan Desa"	, "nilai_poin" => 0.10];
		switch (TRUE) {
			case !empty($nslnya) && array_key_exists($nslnya, $nsl):
				$tarif = $nsl[$nslnya];
			break;
			case !empty($nslnya) && !array_key_exists($nslnya, $nsl):
				$tarif = 'DATA TIDAK DITEMUKAN';
			break;
			default:
				$tarif = $nsl;
			break;
		}
		return $tarif;
	}
	public function jenis_reklamenya($jenisnya = NULL){
		$jenist 	= [
				"4.1.01.09.01.01" => [
					"jenis" 				 => "Papan Reklame/Baleho/Bando",
					"harga_dasar_ukuran" 	 => 250000,
					"harga_dasar_ketinggian" => 150000,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.02" => [
					"jenis" 				 => "Neon Box",
					"harga_dasar_ukuran" 	 => 300000,
					"harga_dasar_ketinggian" => 150000,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.03" => [
					"jenis" 				 => "PNT/Shopsign",
					"harga_dasar_ukuran" 	 => 250000,
					"harga_dasar_ketinggian" => 150000,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.04" => [
					"jenis" 				 => "Videotron",
					"harga_dasar_ukuran" 	 => 2200000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.05" => [
					"jenis" 				 => "Wallpainting",
					"harga_dasar_ukuran" 	 => 75000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.06" => [
					"jenis" 				 => "Spanduk Kain",
					"harga_dasar_ukuran" 	 => 30000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.07" => [
					"jenis" 				 => "Banner",
					"harga_dasar_ukuran" 	 => 40000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.08" => [
					"jenis" 				 => "Tenda",
					"harga_dasar_ukuran" 	 => 250000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.01.09" => [
					"jenis" 				 => "Stiker",
					"harga_dasar_ukuran" 	 => 500,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "lembar"
				],
				
				"4.1.01.09.02.01" => [
					"jenis" 				 => "Umbul-umbul",
					"harga_dasar_ukuran" 	 => 30000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.02.02" => [
					"jenis" 				 => "Selebaran",
					"harga_dasar_ukuran" 	 => 300,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "lembar"
				],
				"4.1.01.09.02.03" => [
					"jenis" 				 => "Reklame Berjalan/Kendaraan",
					"harga_dasar_ukuran" 	 => 300000,
					"harga_dasar_ketinggian" => "null",
					"satuan"				 => "m"
				],
				"4.1.01.09.02.04" => [
					"jenis" 				 => "Reklame Udara",
					"harga_dasar_ukuran" 	 => 450000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.02.05" => [
					"jenis" 				 => "Reklame Apung",
					"harga_dasar_ukuran" 	 => 300000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "m"
				],
				"4.1.01.09.02.06" => [
					"jenis" 				 => "Suara",
					"harga_dasar_ukuran" 	 => 150000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "hari"
				],
				"4.1.01.09.02.07" => [
					"jenis" 				 => "Film/Slide",
					"harga_dasar_ukuran" 	 => 300000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "roll"
				],
				"4.1.01.09.02.08" => [
					"jenis" 				 => "Peragaan",
					"harga_dasar_ukuran" 	 => 300000,
					"harga_dasar_ketinggian" => null,
					"satuan"				 => "hari/peragaan"
				]];
		$jenisrek = $jenist[$jenisnya];
		return $jenisrek;
	}
	public function hitung_njoprnya($ukuran_reklame 
							  =array('PANJANGRE' => 0,'LEBARRE'   => 0, 'UNIT' => 0),
							  $ketinggian_reklame = NULL, 
							  $jenis_reklame 	  = NULL){
		// Ambil nilai panjang, lebar, dan unit reklame dari array
		$panjang_reklame = $ukuran_reklame['PANJANGRE'];
		$lebar_reklame 	 = $ukuran_reklame['LEBARRE'];
		$unit_reklame 	 = $ukuran_reklame['UNIT'];
		$reklnya		 = $this->jenis_reklamenya($jenis_reklame);
		// Hitung NJOPR
		$harga_dasar_ukuran 	= $reklnya['harga_dasar_ukuran'];
		$satuannya 				= $reklnya['satuan'];
		$harga_dasar_ketinggian = $reklnya['harga_dasar_ketinggian'];
		
		switch(TRUE){
			case $harga_dasar_ketinggian == null && $satuannya == 'm' :
				 $NJOPR['total']	 = ($panjang_reklame 	* 
									   $lebar_reklame 		* 
									   $unit_reklame 		* 
									   $harga_dasar_ukuran);
				$NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Panjang x Lebar x Jumlah unit x Harga dasar Ukuran)<br> ";
				$NJOPR['html']		.= "= ($panjang_reklame x $lebar_reklame x $unit_reklame x ".number_format($harga_dasar_ukuran).")<br>";
				$NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
			case $satuannya == 'lembar'  && $harga_dasar_ketinggian == null  :
				 $NJOPR['total']	 = ($unit_reklame * $harga_dasar_ukuran);
				 $NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Jumlah unit x Harga dasar Ukuran)<br> ";
				 $NJOPR['html']		.= "= ( $unit_reklame x ".number_format($harga_dasar_ukuran).")<br>";
				 $NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
			case $satuannya == 'hari'    && $harga_dasar_ketinggian == null  :
				 $NJOPR['total']	 = ($unit_reklame * $harga_dasar_ukuran);
				 $NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Jumlah unit x Harga dasar Ukuran)<br> ";
				 $NJOPR['html']		.= "= ($unit_reklame x ".number_format($harga_dasar_ukuran).")<br>";
				 $NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
			case $satuannya == 'roll'    && $harga_dasar_ketinggian == null  :
				 $NJOPR['total']	 = ($unit_reklame * $harga_dasar_ukuran);
				 $NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Jumlah unit x Harga dasar Ukuran)<br> ";
				 $NJOPR['html']		.= "= ($unit_reklame x ".number_format($harga_dasar_ukuran).")<br>";
				 $NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
			case $satuannya == 'hari/peragaan'    && $harga_dasar_ketinggian == null  :
				 $NJOPR['total']	 = ($unit_reklame * $harga_dasar_ukuran);
				 $NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Jumlah unit x Harga dasar Ukuran)<br> ";
				 $NJOPR['html']		.= "= ($unit_reklame x ".number_format($harga_dasar_ukuran).")<br>";
				 $NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
			default:
				$NJOPR['total']		 = ($panjang_reklame 	* 
									   $lebar_reklame 		* 
									   $unit_reklame 		* 
									   $harga_dasar_ukuran)	+
									  ($ketinggian_reklame 	*  
									   $harga_dasar_ketinggian);
				$NJOPR['html']		 = "<strong>NJOPR</strong><br>
									    = (Panjang x Lebar x Jumlah unit x Harga dasar Ukuran) + (Tinggi x Harga dasar ketinggian)<br> ";
				$NJOPR['html']		.= "= ($panjang_reklame x $lebar_reklame x $unit_reklame x ".number_format($harga_dasar_ukuran).") + (".$ketinggian_reklame." x ".number_format($harga_dasar_ketinggian).")<br>";
				$NJOPR['html'] 		.= "= ".number_format($NJOPR['total']);
			break;
		}
		return $NJOPR;
	}
	public function get_hargadasar($params = array())
	{
/*======================= DEKLARASI POST =============================================*/
	$terima["kdrek"]		= isset($_POST["kdrek"]) 		? $_POST["kdrek"] 		:NULL;
	$terima["panjang"] 		= isset($_POST["panjang"])		? $_POST["panjang"] 	:NULL;
	$terima["lebar"] 		= isset($_POST["lebar"]) 		? $_POST["lebar"] 		:NULL;
	$terima["tinggi"] 		= isset($_POST["tinggi"])		? $_POST["tinggi"] 		:NULL;
	$terima["unit"]			= isset($_POST["sisi"])	 		? $_POST["sisi"] 		:NULL;
	$terima["durasi"]		= isset($_POST["durasi"])		? $_POST["durasi"] 		:NULL;
	$terima["durasi_label"]	= isset($_POST["durasi_label"])	? $_POST["durasi_label"]:NULL;
	$terima["durasi_hari"]	= isset($_POST["durasi_hari"])	? $_POST["durasi_hari"] :NULL;
	$terima["biaya"]		= isset($_POST["biaya"])		? $_POST["biaya"] 		:NULL;
	$terima["nsl"]			= isset($_POST["jalan"])		? $_POST["jalan"] 		:NULL;
	$terima["alkohol_rokok"]			= isset($_POST["alkohol_rokok"])		? $_POST["alkohol_rokok"] 		:NULL;
/*======================= DEKLARASI KOMPONEN ==========================================*/
	$jreklame 	 	= $this->jenis_reklamenya($terima["kdrek"]);
	$strNspr		= $this->nsl($terima["nsl"]);
		
	$jenisnya	 	= $jreklame['jenis'];
	$harga_dasar_uk = $jreklame['harga_dasar_ukuran'];
	$harga_dasar_ket= $jreklame['harga_darar_ketinggian'];
	$satuannya		= $jreklame['satuan'];
	
	// $tarifnspr		= $strNspr['nilai_poin'];
	$tarifnspr		= $strNspr['nilai_poin'];
	$tarif_			= 0.25;
	// var_dump($tarifnspr);die;
	$lokasi			= $strNspr['tarif_pajak'];
	// Contoh pemanggilan fungsi
	$ukuran_reklame 	= array('PANJANGRE' => $terima["panjang"], 
								'LEBARRE' 	=> $terima["lebar"], 
								'UNIT' 		=> $terima["unit"]);
	$luas_reklame		= ($terima["panjang"] * $terima["lebar"] * $terima["unit"]);
	$ketinggian_reklame = $terima["tinggi"];
	$jenis_reklame 		= $terima["kdrek"];
	$alkohol 		= $terima["alkohol_rokok"];
	if ($alkohol == 1) {
		$rokok = 25;
	}
	// var_dump($alkohol);die;
/*======================= PEMANGGILAN DAN IMPLEMENTASI RUMUS 1========================*/	
	$hitung_njoprnya 	= $this->hitung_njoprnya($ukuran_reklame, 
										   	  	 $terima["tinggi"], 
										   	  	 $jenis_reklame);
	$endNJPOR['total']	= $tarif_ * $hitung_njoprnya['total'];
	// harusnya ini njopr
	$endNJPOR['html']	= '
						= '.$tarif_ * 100 .'% x '. number_format($hitung_njoprnya['total']).'<br>
						= '.number_format($endNJPOR['total']);
	//$hitung_nsrnya	= $hitung_njoprnya 	+ $endNJPOR;
	$total_njop_x_nspr	= $endNJPOR['total'] * $tarifnspr;
	$total_nspr 				= $total_njop_x_nspr + $endNJPOR['total'];
	$total_hasil_persen 				= $total_nspr * $rokok / 100;
	$total 				= $total_nspr + $total_hasil_persen;


	if ($alkohol == 1) {
		$perhitungan_total = "<b>Total Pajak</b><br>
		= " . number_format($endNJPOR['total']) . " + " . number_format($total_njop_x_nspr) . "<br>
		= Rp. " . number_format($total_nspr) . " <br> 
		= " . number_format($total_nspr) . " + 25%<br>
		= " . number_format($total_hasil_persen) . "<br>
		= " . number_format($total_nspr) . " + " . number_format($total_hasil_persen) . " <br>
		= Rp. " . number_format($total) . " <br> <br> ";
	}else{
		$perhitungan_total = "<b>Total Pajak</b><br>
		= " . number_format($endNJPOR['total']) . " + " . number_format($total_njop_x_nspr) . " <br>
		= Rp. " . number_format($total) . " <br> <br> ";
	}
	// var_dump($total_hasil_persen);die;
	$hitung_total 		= "<b>Nilai strategis NSPR x NJOPR</b><br>
						= NSPR x NJOPR<br>
						= " . $tarifnspr * 100 . "% x " . number_format($endNJPOR['total']). "<br>
						= Rp. " . number_format($total_njop_x_nspr) . " <br> <br>";
						
	$hitung_total 		.= "$perhitungan_total ";
	
	$rumus  = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame)<br>
			Nilai Strategis NSPR = ".$tarifnspr * 100 ." x NJOPR <br>
			NSR = NSPR + NJOPR <br>
			Total Pajak 		 = Tarif Pajak x Nilai Strategis NSPR";
	$hitung = $hitung_njoprnya['html'] . '<br><br>' . $endNJPOR['html'] . '<br><br>'. $hitung_total ;
			$hitung = str_replace('<br><br><br><br>', '<br><br>', $hitung);

			$html  = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
			$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
			$html .= "<table width='100%'><tr><td>";
			$html .= 'Ukuran Reklame : ' .$luas_reklame. ' m<sup>2</sup> <br/>';
			$html .= 'Label tinggi' . ' : ' . number_format($terima["tinggi"]) . " " . str_replace('<sup>2</sup>', '', $satuannya) . "<br/>";
			$html .= 'Durasi 	  : ' . $terima["durasi"] . ' ' . $terima['durasi_label'] . " <br/>";
			$html .= 'Tarif pajak : ' . number_format($terima['biaya'], 0) . "% <br/>";
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$html .= "<table width='100%'>
					  <tr>
					  	<td style='background:#CCC;font-size:12px!important'>";
			$html .= $rumus;
			$html .= "	</td>
					  </tr></table>";
			$html .= "<table width='100%'><tr><td>";
			$html .= $hitung;
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$response['total'] 		  = $total;
			$response['tarif'] 		  = $terima['biaya'];
			$response['html']  		  = $html;
			$response['rumus_hitung'] = $rumus.$hitung;
		
		    if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
			
	}
	public function get_hargadasarK2($params = array())
	{
	
/*======================= DEKLARASI POST =============================================*/
	$terima["kdrek"]		= isset($_POST["kdrek"]) 		? $_POST["kdrek"] 		:NULL;
	$terima["panjang"] 		= isset($_POST["panjang"])		? $_POST["panjang"] 	:NULL;
	$terima["lebar"] 		= isset($_POST["lebar"]) 		? $_POST["lebar"] 		:NULL;
	$terima["tinggi"] 		= isset($_POST["tinggi"])		? $_POST["tinggi"] 		:NULL;
	$terima["unit"]			= isset($_POST["sisi"])	 		? $_POST["sisi"] 		:NULL;
	$terima["durasi"]		= isset($_POST["durasi"])		? $_POST["durasi"] 		:NULL;
	$terima["durasi_label"]	= isset($_POST["durasi_label"])	? $_POST["durasi_label"]:NULL;
	$terima["durasi_hari"]	= isset($_POST["durasi_hari"])	? $_POST["durasi_hari"] :NULL;
	$terima["biaya"]		= isset($_POST["biaya"])		? $_POST["biaya"] 		:NULL;
	$terima["nsl"]			= isset($_POST["jalan"])		? $_POST["jalan"] 		:NULL;
	$terima["alkohol_rokok"]= isset($_POST["alkohol_rokok"])? $_POST["alkohol_rokok"]:NULL;
/*======================= DEKLARASI KOMPONEN ==========================================*/

// var_dump($terima["kdrek"]);
	$jreklame 	 	= $this->jenis_reklamenya($terima["kdrek"]);
	$strNspr		= $this->nsl($terima["nsl"]);
		
	$jenisnya	 	= $jreklame['jenis'];
	$harga_dasar_uk = $jreklame['harga_dasar_ukuran'];
	$harga_dasar_ket= $jreklame['harga_darar_ketinggian'];
	$satuannya		= $jreklame['satuan'];
	
	$tarifnspr		= $strNspr['nilai_poin'];
	$lokasi			= $strNspr['tarif_pajak'];
	// Contoh pemanggilan fungsi
	$ukuran_reklame 	= array('PANJANGRE' => $terima["panjang"], 
								'LEBARRE' 	=> $terima["lebar"], 
								'UNIT' 		=> $terima["unit"]);
	$luas_reklame		= ($terima["panjang"] * $terima["lebar"] * $terima["unit"]);
	$ketinggian_reklame = $terima["tinggi"];
	$jenis_reklame 		= $terima["kdrek"];
	$alkohol 		= $terima["alkohol_rokok"];
	if ($alkohol == 1) {
		$rokok = 25;
	}
/*======================= PEMANGGILAN DAN IMPLEMENTASI RUMUS 2========================*/	
	$hitung_njoprnya 	= $this->hitung_njoprnya($ukuran_reklame, 
										   	  	 $terima["tinggi"], 
										   	  	 $jenis_reklame);
	$endNJPOR['total']	= $tarifnspr * $hitung_njoprnya['total'];
	// $total = $hitung_njoprnya['total'] + $endNJPOR['total'];


	$total_nspr 				= $hitung_njoprnya['total'] + $endNJPOR['total'];
	$total_hasil_persen 		= $total_nspr * $rokok / 100;
	$total 						= $total_nspr + $total_hasil_persen;


	if ($alkohol == 1) {
		$perhitungan_total = "<b>Total Pajak</b><br>
		= " . number_format($hitung_njoprnya['total']) . " + " . number_format($endNJPOR['total']) . "<br>
		= Rp. " . number_format($total_nspr) . " <br> 
		= " . number_format($total_nspr) . " + 25%<br>
		= " . number_format($total_hasil_persen) . "<br>
		= " . number_format($total_nspr) . " + " . number_format($total_hasil_persen) . " <br>
		= Rp. " . number_format($total) . " <br> <br> ";
	}else{
		$perhitungan_total = "<strong>Total Pajak </strong></br>
		= " . number_format($hitung_njoprnya['total']) . " + " . number_format($endNJPOR['total']) . "<br>
		= Rp. " . number_format($total) . " <br> <br> ";
	}


	$endNJPOR['html']	= '<strong>Nilai strategis NSPR x NJOPR </strong></br>
						= Nilai strategis NSPR x NJOPRRRR</br>
						= '.$tarifnspr * 100 .'% x '. number_format($hitung_njoprnya['total']).'<br>
						= '.number_format($endNJPOR['total']) . '<br><br>';

	$endNJPOR['html'] 	.= "$perhitungan_total ";
						
					
						// = '.number_format($hitung_njoprnya['total']).' + '.number_format($endNJPOR['total']).' <br>
						// = '.number_format($total).'';
	
	$rumus  = "
			NSR   = NSPR + NJOPR <br>
			NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame)<br>
			Total Pajak = Nilai strategis NSPR X NJOPR  ";
	$hitung = $hitung_njoprnya['html'] . '<br><br>' . $endNJPOR['html'] . '<br><br>';
			$hitung = str_replace('<br><br><br><br>', '<br><br>', $hitung);

			$html  = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
			$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
			$html .= "<table width='100%'><tr><td>";
//			$html .= 'Ukuran Reklame : ' .$luas_reklame. ' m<sup>2</sup> <br/>';
//			$html .= 'Label tinggi' . ' : ' . number_format($terima["tinggi"]) . " " . str_replace('<sup>2</sup>', '', $satuannya) . "<br/>";
			$html .= 'Durasi 	  : ' . $terima["durasi"] . ' ' . $terima['durasi_label'] . " <br/>";
			$html .= 'Tarif pajak : ' . number_format($terima['biaya'], 0) . "% <br/>";
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$html .= "<table width='100%'>
					  <tr>
					  	<td style='background:#CCC;font-size:12px!important'>";
			$html .= $rumus;
			$html .= "	</td>
					  </tr></table>";
			$html .= "<table width='100%'><tr><td>";
			$html .= $hitung;
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$response['total'] 		  = $total;
			$response['tarif'] 		  = $terima['biaya'];
			$response['html']  		  = $html;
			$response['rumus_hitung'] = $rumus.$hitung;
		
		    if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
			
	}
	public function get_hargadasar22($params = array())
	{

		if (count($params) == 0) {
			extract($_POST);
		} else {
			extract($params);
		}

		$biaya = $this->toNumber($biaya);
		$harga_dasar_uk = $this->toNumber($harga_dasar_uk);
		$harga_dasar_tin = $this->toNumber($harga_dasar_tin);
		$tarif_pajak = $tarif / 100;
		$luas = round($panjang * $lebar, 2);
		// $muka = $muka > 3 ? 4 : $muka;
		$alkohol_rokok = (isset($alkohol_rokok) && $alkohol_rokok == 1) ? true : false;
		$tol = (isset($tol) && $tol == 1) ? true : false;

		$response = array(
			'luas' => $luas,
			'njop' => 0,
			'nilai_strategis' => 0
		);
		
		if ($x == 'false') {
			$rumus = "";
			$jml_njopr = 0;
			$jml_nspr = 0;
			$hitung = "";
			$total = 0;
			$satuan = 'm';

			$param = array('NFR' => 0, 'NFJ' => 0, 'NSP' => 0);
			$harga_dasar = (object) array('nspr' => 0, 'ketinggian' => 0, 'ukuran' => 0);

			// data NJOPR (harga tinggi)
			$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA,CPM_SATUAN from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_REKENING='{$kdrek}' AND ('$tinggi' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)");
			$data_njopr = mysqli_fetch_object($sql);
			$satuan = (isset($data_njopr->CPM_SATUAN) && !empty($data_njopr->CPM_SATUAN)) ? str_replace('2', '<sup>2</sup>', $data_njopr->CPM_SATUAN) : $satuan;
			$harga_dasar->ketinggian = $data_njopr->CPM_HARGA;

			// data NJOPR (harga ukuran)
			$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA,CPM_SATUAN from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_REKENING='{$kdrek}' AND ('$luas' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)");
			$data_njopr = mysqli_fetch_object($sql);
			$harga_dasar->ukuran = $data_njopr->CPM_HARGA;
			//	var_dump($harga_ukuran);

			// data Harga Dasar
			$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA from PATDA_REKLAME_HARGADASAR WHERE CPM_REKENING='{$kdrek}' AND ('$luas' BETWEEN CPM_LUAS_MIN AND CPM_LUAS_MAX)");
			$hd = mysqli_fetch_object($sql);
			$harga_dasar->nspr = $hd->CPM_HARGA;

			// data milai NFR, NFJ, NSP
			//$sql = mysqli_query($this->Conn, "SELECT CPM_GRUP, CPM_NILAI FROM PATDA_REKLAME_PARAM_NILAI where CPM_PARAM='{$jalan}' OR CPM_PARAM='{$sudut_pandang}'");
			$sql = mysqli_query($this->Conn, "SELECT CPM_GRUP, CPM_NILAI FROM PATDA_REKLAME_PARAM_NILAI where CPM_NAMA='{$jalan}' OR CPM_NAMA='{$kawasan}' OR CPM_NAMA='{$sudut_pandang}'");
			while ($row = mysqli_fetch_assoc($sql)) {
				$param[$row['CPM_GRUP']] = $row['CPM_NILAI'];
			}
			// print_r($param);exit;
			extract($param);

			// var_dump("SELECT CPM_GRUP, CPM_NILAI FROM PATDA_REKLAME_PARAM_NILAI where CPM_NAMA='{$jalan}' OR CPM_NAMA='{$kawasan}' OR CPM_NAMA='{$sudut_pandang}'");
			// die;
			//$list_sudut_pandang = $this->get_sudut_pandang();

			$label_tinggi = 'Tinggi';
			$val_tinggi = $tinggi;

			// new formula April 2020
			$rumus = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
			NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
			NSR = NJOPR + NSPR<br>
			Total Pajak = NSR x Tarif Pajak";
			//var_dump($NFJ);
			$total_nspr = ($NFR + $NFJ + $NSP) * $harga_dasar->nspr;

			if ($kdrek == '4.1.01.09.01.004') { // Vidiotron/megatron 
				$harga_tinggi = 75000;
				$harga_ukuran = 175000;
				// peritungan asli
				// $total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $jam * $durasi_hari;
				// end peritungan asli
				$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $jam * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;

				// hitung_njop asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
				// 	= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
				// 	= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);

				// NJOPR Vidiotron/megatron 
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br> 
					= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
					= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
					= (" . number_format($NFR, 2) . " + " . number_format($NFJ) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + NSPR<br>
					= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
					= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.03') { // Selebaran: jumlah x harga 
				$harga_ukuran = 500;
				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_ukuran) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi_hari} Hari<br>
					= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// rumus asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				// end rumus asli
				$total_nspr = 0;
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$total_nsr = $total_njopr;
				$label_tinggi = 'Jumlah';
				$satuan = 'lembar';
				$val_tinggi = $sisi;
				$sisi = 1;
			} elseif ($kdrek == '4.1.01.09.02') { // Melekat/Stiker: jumlah x harga
				$harga_ukuran = 250;
				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_ukuran) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi_hari} Hari<br>
					= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// rumus asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				// end rumus asli
				$total_nspr = 0;
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$total_nsr = $total_njopr;
				$label_tinggi = 'Jumlah';
				$satuan = 'lembar';
				$val_tinggi = $sisi;
				$sisi = 1;
			} elseif ($kdrek == '4.1.01.09.07' || $kdrek == '4.1.01.09.07.1') { // Suara: jumlah x harga
				if ($kdrek == '4.1.01.09.07.1') {
					$harga_ukuran = 25000;
				} elseif ($kdrek == '4.1.01.09.07') {
					$harga_ukuran = 50000;
				}

				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$label_tinggi = 'Jumlah';
				$val_tinggi = $sisi;
			} elseif ($kdrek == '4.1.01.09.09' || $kdrek == '4.1.01.09.09.1') { // peragaan: jumlah x harga
				if ($kdrek == '4.1.01.09.09.1') {
					$harga_ukuran = 60000;
				} elseif ($kdrek == '4.1.01.09.09') {
					$harga_ukuran = 100000;
				}

				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$label_tinggi = 'Jumlah';
				$val_tinggi = $sisi;
			} elseif (
				$kdrek == '4.1.01.09.05' || // Udara: jumlah x harga
				$kdrek == '4.1.01.09.06'
			) { // Apung
				$rumus = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				$total_njopr = ($luas * $harga_dasar->ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.10') { // Wall Painting dan Sejenisnya
				// selain yg di atas formulanya sama: NJOPR + NSPR
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
					= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
					= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + NSPR<br>
					= " . number_format($total_njopr) . " + " . number_format($total_nspr) . " x 50%<br>
					= " . number_format($total_nsr) . " x 50%<br>
					= Rp. " . number_format(round($total_nsr / 2));
				$total_nsr = round($total_nsr / 2);
			} elseif ($kdrek == '4.1.01.09.01.005') { // Reklame kain
				// selain yg di atas formulanya sama: NJOPR + NSPR
				// $harga_tinggi = 75000;
				$harga_ukuran  = 5000;
				$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi;
				$total_nsr = ($total_njopr + $total_nspr);

				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
						= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
						= Rp. " . number_format($total_njopr);


				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.04') { // Reklame Kendaraan
				// selain yg di atas formulanya sama: NJOPR + NSPR
				// $harga_tinggi = 75000;
				$harga_ukuran  = 250000;
				$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi;
				$total_nsr = ($total_njopr + $total_nspr);

				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
						= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
						= Rp. " . number_format($total_njopr);


				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.01.001' || $kdrek == '4.1.01.09.01.002' || $kdrek == '4.1.01.09.01.006') { // Reklame biilboard,nenobox, banner, bando
				$harga_tinggi = 75000;
				$harga_ukuran = 175000;
				// selain yg di atas formulanya sama: NJOPR + NSPR
				// $total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				// $total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi; coding asli 
				$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi));
				$total_nsr = ($total_njopr + $total_nspr);
				// var_dump($total_njopr,$total_nspr);exit;
				// perhitungan njopr asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
				// 	= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
				// 	= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				// end perhitungan njopr asli
				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
						= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
						= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			}
			//baru 2022 
			elseif ($kdrek == '4.1.01.09.01.01' || $kdrek == '4.1.01.09.01.02' || $kdrek == '4.1.01.09.01.06' || $kdrek == '4.1.01.09.01.03') { // Reklame biilboard,nenobox, banner, bando
				// $harga_tinggi = 75000;
				// $harga_ukuran = 175000;
				// selain yg di atas formulanya sama: NJOPR + NSPR
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				// var_dump($total_njopr);
				// die;
				//$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi; //coding asli 
				// $total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi));
				$total_nsr = ($total_njopr + $total_nspr);

				// perhitungan njopr asli
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// end perhitungan njopr asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 		= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
				// 		= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
				// 		= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
				// 		= Rp. " . number_format($total_njopr);

				// var_dump($harga_dasar->nspr);die;
				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
				// } elseif ($kdrek == '4.1.01.09.01.03') { // Reklame biilboard,nenobox, banner, bando
				// $harga_tinggi = 75000;
				// $harga_ukuran = 175000;
				// selain yg di atas formulanya sama: NJOPR + NSPR
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				// var_dump($total_njopr);
				// die;
				//$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi; //coding asli 
				// $total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi));
				$total_nsr = ($total_njopr + $total_nspr);

				// perhitungan njopr asli
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// end perhitungan njopr asli
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 		= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
				// 		= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
				// 		= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
				// 		= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.01.04') { // Vidiotron/megatron 
				// $harga_tinggi = 75000;
				// $harga_ukuran = 175000;
				// peritungan asli
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $jam * $durasi_hari;
				// end peritungan asli
				//$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $jam * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;

				// hitung_njop asli
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);

				// NJOPR Vidiotron/megatron 
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br> 
				// 	= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
				// 	= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$jam} jam x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
					= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
					= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + NSPR<br>
					= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
					= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.01.05') { // Reklame kain
				// selain yg di atas formulanya sama: NJOPR + NSPR
				// $harga_tinggi = 75000;
				//$harga_ukuran  = 5000;
				//var_dump($harga_dasar->ketinggian);
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi;
				$total_nsr = ($total_njopr + $total_nspr);

				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x  {$durasi} {$durasi_label}<br>
						= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
						= Rp. " . number_format($total_njopr);


				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.02.01') { // Melekat/Stiker: jumlah x harga
				//	$harga_ukuran = 250;
				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				//			var_dump($harga_dasar->ukuran);
				$total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				//$total_njopr = ($sisi * $harga_ukuran) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				// rumus asli
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// end rumus asli
				$total_nspr = 0;
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$total_nsr = $total_njopr;
				$label_tinggi = 'Jumlah';
				$satuan = 'lembar';
				$val_tinggi = $sisi;
				$sisi = 1;
			} elseif ($kdrek == '4.1.01.09.03.01') { // Selebaran: jumlah x harga 
				//	$harga_ukuran = 500;
				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				$total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				//$total_njopr = ($sisi * $harga_ukuran) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= (" . number_format($sisi * $harga_ukuran) . ") x {$durasi_hari} Hari<br>
				// 	= Rp. " . number_format($total_njopr);
				// rumus asli
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				// end rumus asli
				$total_nspr = 0;
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$total_nsr = $total_njopr;
				$label_tinggi = 'Jumlah';
				$satuan = 'lembar';
				$val_tinggi = $sisi;
				$sisi = 1;
			} elseif ($kdrek == '4.1.01.09.04.01') { // Reklame Kendaraan
				// selain yg di atas formulanya sama: NJOPR + NSPR
				// $harga_tinggi = 75000;
				//$harga_ukuran  = 250000;
				//$total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi;
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				$total_nsr = ($total_njopr + $total_nspr);

				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x  {$durasi} {$durasi_label}<br>
						= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi} {$durasi_label}<br>
						= Rp. " . number_format($total_njopr);


				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			} elseif (
				$kdrek == '4.1.01.09.05.01' || // Udara: jumlah x harga
				$kdrek == '4.1.01.09.06.01'
			) { // Apung
				$rumus = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				$total_njopr = ($luas * $harga_dasar->ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_nsr);
			} elseif ($kdrek == '4.1.01.09.07.01') { // Suara: jumlah x harga
				if ($kdrek == '4.1.01.09.07.1') {
					$harga_ukuran = 25000;
				} elseif ($kdrek == '4.1.01.09.07.01') {
					$harga_ukuran = 50000;
				}

				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$label_tinggi = 'Jumlah';
				$val_tinggi = $sisi;
			} elseif ($kdrek == '4.1.01.09.09.01') { // peragaan: jumlah x harga
				if ($kdrek == '4.1.01.09.09.1') {
					$harga_ukuran = 60000;
				} elseif ($kdrek == '4.1.01.09.09.01') {
					$harga_ukuran = 100000;
				}

				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				// $total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi_hari;
				$total_njopr = ($sisi * $harga_dasar->ukuran) * $durasi;
				$total_nspr = 0;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
					= Rp. " . number_format($total_njopr);
				// $hitung_njopr = "<b>NJOPR</b><br>
				// 	= (Jumlah x Harga Dasar) x Durasi<br>
				// 	= ({$sisi} x " . number_format($harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= (" . number_format($sisi * $harga_dasar->ukuran) . ") x {$durasi} {$durasi_label}<br>
				// 	= Rp. " . number_format($total_njopr);
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$label_tinggi = 'Jumlah';
				$val_tinggi = $sisi;
			} elseif ($kdrek == '4.1.01.09.10.01') { // Wall Painting dan Sejenisnya
				// selain yg di atas formulanya sama: NJOPR + NSPR
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				$hitung_nspr = "<b>NSPR </b><br>
					= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
					= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
					= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + NSPR<br>
					= " . number_format($total_njopr) . " + " . number_format($total_nspr) . " x 50%<br>
					= " . number_format($total_nsr) . " x 50%<br>
					= Rp. " . number_format(round($total_nsr / 2));
				$total_nsr = round($total_nsr / 2);
			} else {

				// selain yg di atas formulanya sama: NJOPR + NSPR
				$total_njopr = (($luas * $harga_dasar->ukuran) + ($tinggi * $harga_dasar->ketinggian)) * $durasi_hari;
				// $total_njopr = (($luas * $harga_ukuran) + ($tinggi * $harga_tinggi)) * $durasi;
				$total_nsr = ($total_njopr + $total_nspr);

				// perhitungan njopr asli
				$hitung_njopr = "<b>NJOPR</b><br>
						= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
						= ({$panjang} x {$lebar} x " . number_format($harga_dasar->ukuran) . ") + ({$tinggi} x " . number_format($harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
						= (" . number_format($panjang * $lebar * $harga_dasar->ukuran) . ") + (" . number_format($tinggi * $harga_dasar->ketinggian) . ")  x {$durasi_hari} Hari<br>
						= Rp. " . number_format($total_njopr);
				// end perhitungan njopr asli

				// $hitung_njopr = "<b>NJOPR sdsds</b><br>
				// 	= (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
				// 	= ({$panjang} x {$lebar} x " . number_format($harga_ukuran) . ") + ({$tinggi} x " . number_format($harga_tinggi) . ")  x  {$durasi} {$durasi_label}<br>
				// 	= (" . number_format($panjang * $lebar * $harga_ukuran) . ") + (" . number_format($tinggi * $harga_tinggi) . ")  x {$durasi} {$durasi_label}<br>
				// 	= Rp. " . number_format($total_njopr);


				$hitung_nspr = "<b>NSPR </b><br>
						= (NFR + NFJ + NSP) x Harga Dasar NSR <br>
						= (" . number_format($NFR, 2) . " + " . number_format($NFJ, 2) . " + " . number_format($NSP, 2) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= (" . number_format($NFR + $NFJ + $NSP) . ") x Rp. " . number_format($harga_dasar->nspr) . " <br>
						= Rp. " . number_format($total_nspr);
				$hitung_nsr = "<b>NSR</b><br>
						=  NJOPR + NSPR<br>
						= " . number_format($total_njopr) . " + " . number_format($total_nspr) . "<br>
						= Rp. " . number_format($total_nsr);
			}
			//die(var_dump($NSP));
			$total = $total_nsr * $tarif_pajak;
			$pokok = $total;

			if ($gedung == 'DALAM') {
				$ttotal = $total;
				$total = $total * 0.35;
				if ($alkohol_rokok) {
					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " + 25%<br>" : "";
					$total = $total + ($pokok * 0.25);
				}
				if ($tol) {
					$hitung_tol = $tol ? "= " . number_format($total) . " + 10%<br>" : "";
					$total = $total + ($pokok * 0.1);
				}
				$hitung_total = "<b>Total Pajak</b><br>
					= NSR x Tarif Pajak<br>
					= " . number_format($total_nsr) . " x " . number_format($tarif) . "%<br>
					= " . number_format($ttotal) . " x 35%<br>
					{$hitung_alkohol_rokok}
					{$hitung_tol}
					= Rp. " . number_format($sisi * $total);
			} else {
				if ($alkohol_rokok) {
					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " + 25%<br>" : "";
					$total = $total + ($pokok * 0.25);
				}
				if ($tol) {
					$hitung_tol = $tol ? "= " . number_format($total) . " + 10%<br>" : "";
					$total = $total + ($pokok * 0.1);
				}
				$hitung_total = "<b>Total Pajak</b><br>
					= NSR x Muka x Tarif Pajak<br>
					= " . number_format($total_nsr) . " x " . number_format($sisi) . " x " . number_format($tarif) . "%<br>
					{$hitung_alkohol_rokok}
					{$hitung_tol}
					= Rp. " . number_format($sisi * $total);
			}


			$hitung = $hitung_njopr . '<br><br>' . $hitung_nspr . '<br><br>' . $hitung_nsr . '<br><br>' . $hitung_total;
			$hitung = str_replace('<br><br><br><br>', '<br><br>', $hitung);

			$html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
			$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
			$html .= "<table width='100%'><tr><td>";
			if ($luas > 0) $html .= 'Luas Reklame : ' . number_format($luas, 2) . " m<sup>2</sup> <br/>";
			if ($val_tinggi > 0) $html .= $label_tinggi . ' : ' . number_format($val_tinggi) . " " . str_replace('<sup>2</sup>', '', $satuan) . "<br/>";
			$html .= 'Durasi : ' . $durasi . ' ' . $durasi_label . " <br/>";
			$html .= 'Tarif pajak : ' . number_format($tarif, 0) . "% <br/>";
			// $html .= 'NJOP : ' . number_format($njop,0) . " m<sup>2</sup> <br/>";
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$html .= "<table width='100%'><tr><td style='background:#CCC;font-size:12px!important'>";
			$html .= $rumus;
			$html .= "</td></tr></table>";

			$html .= "<table width='100%'><tr><td>";
			$html .= $hitung;
			$html .= "</td></tr></table>";
			$html .= "</div>";
		} else {

			// echo $x."else";exit();
			#PERHITUNGAN DPP X TARIF PAJAK (PIHAK KETIGA)
			$total = (intval($npk) * (intval($biaya) / 100));
			$html = "<font color=\"blue\">Perhitungan : <font>";
			$html .= "<br><font color=\"blue\">Total Pajak : DPP x TARIF PAJAK<font>";
			$html .= "<br><font color=\"blue\">Total Pajak : (" . number_format(intval($npk)) . " X " . intval($biaya) / intval(100) . ")<font>";
			$html .= "<br><font color=\"blue\">Total Pajak : " . number_format($total) . "<font>";
			$rumus = $html;
		}


		$response['total'] = $sisi * $total;
		$response['html'] = $html;
		$response['rumus_hitung'] = $rumus . $hitung;
		if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
	}
	public function get_hargadasar_backup($params = array())
	{


		if (count($params) == 0) {
			extract($_POST);
		} else {
			extract($params);
		}

		$biaya = $this->toNumber($biaya);
		$tarif = $tarif / 100;
		$luas = $panjang * $lebar;
		$muka = $muka > 3 ? 4 : $muka;
		$response = array(
			'luas' => $luas,
			'njop' => 0,
			'nilai_strategis' => 0
		);

		$query = sprintf(
			"
			SELECT CPM_NJOP, 0 CPM_NILAI FROM PATDA_REKLAME_TARIF_NJOP
			WHERE CPM_LUAS_AWAL <= %s AND CPM_LUAS_AKHIR >= %s
			UNION
			SELECT 0 CPM_NJOP, CPM_NILAI FROM PATDA_REKLAME_NILAI_STRATEGIS
			WHERE CPM_KAWASAN = '%s' AND CPM_MUKA = '%s'",
			$luas,
			$luas,
			$kawasan,
			$muka
		);

		$res = mysqli_query($this->Conn, $query);
		while ($data = mysqli_fetch_assoc($res)) {
			if ($data['CPM_NJOP'] != 0) $response['njop'] = $data['CPM_NJOP'];
			if ($data['CPM_NILAI'] != 0) $response['nilai_strategis'] = $data['CPM_NILAI'];
		}

		extract($response);

		$rumus = "";
		$hitung = "";

		$total = 0;

		if ($kdrek == '4.1.1.4.01.1' || $kdrek == '4.1.1.4.01.2') {
			//Reklame Papan/BillBoard/Baliho/Neonbox
			//Reklame Videotron/Megatron
			$total = $luas * $nilai_strategis * $durasi * $tarif + $njop;
			$rumus = "(Luas x Nilai Strategis x Durasi x Tarif pajak) + NJOP<br/>";
			$hitung = "(" . number_format($luas, 0) . " x " . number_format($nilai_strategis, 0) . " x
			" . number_format($durasi, 2) . " x " . number_format($tarif, 2) . ") + " . number_format($njop, 0) . "";
			$hitung .= " = " . number_format($total, 2);
		} elseif ($kdrek == '4.1.1.4.02.1') {
			//Reklame kain /spanduk/umbul-umbul, tenda reklame, banner dan sejenisnya
			$total = $jumlah * $biaya * $durasi;
			$rumus = "(Jumlah x Tarif pajak x Durasi)<br/>";
			$hitung = "(" . number_format($jumlah, 0) . " x " . number_format($biaya, 0) . " x " . number_format($durasi, 2) . ")";
			$hitung .= " = " . number_format($total, 2);
		} elseif ($kdrek == '4.1.1.4.03.1' || $kdrek == '4.1.1.4.04.1') {
			//Reklame Melekat/Stiker
			//Reklame Selebaran/poster/leaflet
			$total = $biaya * $tarif;
			$rumus = "(Biaya penyelenggaraan x Tarif pajak)<br/>";
			$hitung = "(" . number_format($biaya, 0) . " x " . number_format($tarif, 2) . ")";
			$hitung .= " = " . number_format($total, 2);
		} elseif ($kdrek == '4.1.1.4.06.1' || $kdrek == '4.1.1.4.07.1') {
			//Reklame Udara
			//Reklame Apung
			$total = $biaya * $tarif * $durasi;
			$rumus = "(Biaya penyelenggaraan x Tarif pajak x Durasi)<br/>";
			$hitung = "(" . number_format($biaya, 0) . " x " . number_format($tarif, 2) . " x " . number_format($durasi, 2) . ")";
			$hitung .= " = " . number_format($total, 2);
		} elseif ($kdrek == '4.1.1.4.05.1') {
			//Reklame Berjalan termasuk pada Kendaraan
			$total = $biaya * $jumlah;
			$rumus = "(Tarif pajak x Jumlah)<br/>";
			$hitung = "(" . number_format($biaya, 0) . " x " . number_format($jumlah, 0) . ")";
			$hitung .= " = " . number_format($total, 2);
		} elseif ($kdrek == '4.1.1.4.08.1' || $kdrek == '4.1.1.4.09.1') {
			//Reklame Suara
			//Reklame Film/slide
			$total = $biaya;
			$rumus = "(Tarif pajak x Jumlah)<br/>";
			$hitung = "(" . number_format($biaya, 0) . " x " . number_format($jumlah, 0) . ")";
			$hitung .= " = " . number_format($total, 2);
		}

		$html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:550px!important;text-align:left;'>";
		$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
		$html .= "<table width='440'><tr><td>";
		$html .= 'Luas Reklame : ' . number_format($luas, 0) . " m<sup>2</sup> <br/>";
		$html .= 'NJOP : ' . number_format($njop, 0) . " m<sup>2</sup> <br/>";
		$html .= 'Lama : ' . $durasi . ' ' . $durasi_label . " <br/>";
		$html .= 'Nilai Strategis : ' . number_format($nilai_strategis, 0) . " <br/>";
		$html .= "</td></tr></table>";
		$html .= "</div>";
		$html .= "<table width='550'><tr><td style='background:#CCC;font-size:12px!important'>";
		$html .= $rumus;
		$html .= "</td></tr></table>";

		$html .= "<table width='550'><tr><td>";
		$html .= $hitung;
		$html .= "</td></tr></table>";
		$html .= "</div>";

		$response['total'] = $total;
		$response['html'] = $html;
		$response['rumus_hitung'] = $rumus . $hitung;
		if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
	}

	function list_tarif($kdrek = '', $lokasi = '', $type_masa = '')
	{
		$where = array();
		if ($kdrek != '') $where[] = "CPM_REKENING='$kdrek'";
		if ($lokasi != '') $where[] = "CPM_LOKASI='$lokasi'";
		if (!empty($where)) $where = 'WHERE ' . implode(' AND ', $where);
		else $where = '';
		$res = mysqli_query($this->Conn, "SELECT * from PATDA_REKLAME_TARIF $where");
		$output = array(
			'lokasi' => array(),
			'nspr' => array(),
			'njopr' => array(),
			'tarif' => array(),
		);
		while ($row = mysqli_fetch_assoc($res)) {
			$output['lokasi'][$row['CPM_LOKASI']] = $row['CPM_LOKASI'];
			$output['nspr'][$row['CPM_REKENING']][$row['CPM_LOKASI']] = array('bobot' => $row['CPM_NSPR_BOBOT'], 'scor' => $row['CPM_NSPR_SCOR'], 'titik' => $row['CPM_NSPR_TITIK']);
			$output['njopr'][$row['CPM_REKENING']][$row['CPM_LOKASI']] = array('biaya_pembuatan' => $row['CPM_NJOPR_BIAYA_PEMBUATAN'], 'biaya_pemeliharaan' => $row['CPM_NJOPR_BIAYA_PEMELIHARAAN']);
			$output['tarif'][$row['CPM_REKENING']][$row['CPM_LOKASI']][$row['CPM_TYPE_MASA']] = $row['CPM_TARIF'];
		}
		if ($lokasi != '') {
			$output['lokasi'] = isset($output['lokasi'][$lokasi]) ? $output['lokasi'][$lokasi] : $output['lokasi'];
		}

		if ($kdrek != '') {
			if ($lokasi != '') {
				$output['nspr'] = isset($output['nspr'][$kdrek][$lokasi]) ? $output['nspr'][$kdrek][$lokasi] : $output['nspr'];
				$output['njopr'] = isset($output['njopr'][$kdrek][$lokasi]) ? $output['njopr'][$kdrek][$lokasi] : $output['njopr'];
				$output['tarif'] = isset($output['tarif'][$kdrek][$lokasi]) ? $output['tarif'][$kdrek][$lokasi] : $output['tarif'];
			} else {
				$output['nspr'] = isset($output['nspr'][$kdrek]) ? $output['nspr'][$kdrek] : $output['nspr'];
				$output['njopr'] = isset($output['njopr'][$kdrek]) ? $output['njopr'][$kdrek] : $output['njopr'];
				$output['tarif'] = isset($output['tarif'][$kdrek]) ? $output['tarif'][$kdrek] : $output['tarif'];
			}
		}
		if ($type_masa != '') {
			$output['tarif'] = isset($output['tarif'][$type_masa]) ? $output['tarif'][$type_masa] : $output['tarif'];
		}
		return $output;
	}
	
	function hitung_denda()
	{
		$ms = explode('/', $_REQUEST['masa_pajak']);
		$masa = "$ms[2]-$ms[1]-$ms[0]";
		$persen_denda = $this->get_persen_denda($masa);
		$denda = 0;
		if (strtotime($masa) > strtotime(date('Y-m-d'))) {
			$denda = ($persen_denda / 100) * $_REQUEST['tagihan'];
		}
		echo json_encode(array('masa' => $masa, 'tagihan' => $_REQUEST['tagihan'], 'persen' => $persen_denda, 'denda' => $denda));
	}

	function addRow()
	{
		include __DIR__ . '/../op/class-op.php';
		$Op = new ObjekPajak();
		$no = $_REQUEST['no'] + 1;
		$idx = ($no * 10) + 4;
		$npwpd = str_replace('.', '', $_REQUEST['npwpd']);

		$list_nop = $Op->get_list_nop($npwpd);
		$list_rekening = $this->get_list_rekening();
		$list_type_masa = $this->get_type_masa();
		$list_sudut_pandang = $this->get_sudut_pandang();
		$list_tarif = $this->list_tarif();
		$list_kawasan = $list_tarif['lokasi'];
		$list_kawasan_1 = $this->get_kawasan();
		$list_jalan = $this->nsl();

		$opt_nop = '<option selected value="" disabled>Pilih NOP</option>';
		$opt_rekening = '<option selected value="" disabled>Pilih Rekening </option>';
		$opt_sudut_pandang = '';
		$opt_type_masa = '';
		$opt_kawasan = '';
		$opt_kawasan_1 = '';
		$opt_jalan = '';

		foreach ($list_nop as $list) {
			$alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
			$kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
			$opt_nop .= "<option value='{$list['CPM_ID']}'>{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
		}


		// var_dump($list['CPM_ID']);

		foreach ($list_rekening as $rek) {
			$opt_rekening .= "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
			data-tinggi='{$rek->tarif3}'>{$rek->kdrek} - {$rek->nmrek}</option>";
		}

		foreach ($list_sudut_pandang as $sp) {
			$opt_sudut_pandang .= "<option value='{$sp}'>$sp</option>";
		}

		foreach ($list_type_masa as $key => $val) {
			$sel = $key == $_REQUEST['type_masa'] ? ' selected' : '';
			$opt_type_masa .= "<option value='{$key}'{$sel}>$val</option>";
		}

		// foreach ($list_kawasan as $kws) {
		// 	$list_kawasan_1 .= "<option value='{$kws}'>$kws</option>";
		// }

		foreach ($list_kawasan_1 as $kwss) {
			$opt_kawasan_1 .= "<option value='{$kwss}'>$kwss</option>";
		}
		foreach ($list_jalan as $dalan => $jln) {
			// $opt_jalan .= "<option value=" . $jln['tarif_pajak'] . ">" . $jln['tarif_pajak'] . "</option>";
			$opt_jalan .= "<option value=\"" . htmlspecialchars($jln['tarif_pajak'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($jln['tarif_pajak'], ENT_QUOTES, 'UTF-8') . "</option>";
		}


		foreach ($this->arr_tipe_pajak_reklame as $x => $y) {
			$tipe_pajak .= "<option value='{$x}' selected>$y</option>";
			// $tipe_pajak .= "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
		}


		// var_dump($this->arr_tipe_pajak_reklame);

		echo '<table width="900" class="child" id="atr_rek-' . $no . '" border="0" style="margin-top:8px">
		<tr>
			<th colspan="2">Reklame</th>
			<th colspan="2">Dimensi Reklame</th>
			<th width="80">Jumlah (Qty)</th>
			<th width="111">Jangka Waktu</th>
		</tr>
		<tr>

			<td align="left" width="190" valign="top">Tipe Pajak <b class="isi">*</b></td> 
			<td align="left" width="240" valign="top">
				<select name="PAJAK_ATR[CPM_TIPE_PAJAK][' . ($no - 1) . ']" tabindex="' . ($idx) . '" id="CPM_NOP-' . $no . '" class="CPM_NOP" onchange="hitungDetail(' . $no . ')" style="max-width:260px">' . $tipe_pajak . '</select>
			</td>
			<td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
			<td width="130" align="center" valign="top"><label id="load-type-' . $no . '"></label>
				<input name="PAJAK_ATR[CPM_ATR_PANJANG][' . ($no - 1) . ']" type="text" class="number" tabindex="' . ($idx + 4) . '" id="CPM_ATR_PANJANG-' . $no . '" maxlength="6" style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="Panjang" onkeyup="hitungDetail(' . $no . ')" />
			</td>
			<td rowspan="3" align="center" valign="top">
				<input name="PAJAK_ATR[CPM_ATR_SISI][' . ($no - 1) . ']" type="text" class="number" tabindex="' . ($idx + 7) . '" id="CPM_ATR_SISI-' . $no . '" value="" maxlength="3" style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="Jumlah" onkeyup="hitungDetail(' . $no . ')" />
				<b class="isi">*</b>
			</td>
			<td rowspan="3" align="center" valign="top">
				<input name="PAJAK_ATR[CPM_MASA_PAJAK][' . ($no - 1) . ']" type="hidden" id="CPM_MASA_PAJAK-' . $no . '" value="' . $atr['CPM_MASA_PAJAK'] . '" />
				<span id="jangka-waktu-' . $no . '">' . $_REQUEST['waktu'] . '</span>
			</td>
		</tr>
		<tr>
			<td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
			<td align="left" width="240" valign="top">
				<select name="PAJAK_ATR[CPM_ATR_NOP][' . ($no - 1) . ']" tabindex="' . ($idx) . '" id="CPM_NOP-' . $no . '" class="CPM_NOP" onchange="hitungDetail(' . $no . ')" style="width:260px">' . $opt_nop . '</select>
			</td>
			<td align="left" valign="top">Lebar <b class="isi">*</b></td>
			<td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][' . ($no - 1) . ']" tabindex="' . ($idx + 5) . '" type="text" class="number" id="CPM_ATR_LEBAR-' . $no . '" maxlength="6" style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" /></td>

		</tr>
		<tr>

			<td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
			<td align="left" valign="top">
				<select class="form-control" tabindex="' . ($idx + 1) . '" name="PAJAK_ATR[CPM_ATR_REKENING][' . ($no - 1) . ']" onchange="rekDetail(' . $no . ')" id="CPM_ATR_REKENING-' . $no . '" style="width:260px">' . $opt_rekening . '</select>
			</td>

			
			<td align="left" valign="top">Tinggi <b class="isi">*</b></td>
			<td align="center" valign="top">
				<!--<select id="CPM_ATR_SUDUT_PANDANG-' . $no . '" onkeyup="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_SUDUT_PANDANG][]">' . $opt_sudut_pandang . '</select>-->
				<input name="PAJAK_ATR[CPM_ATR_TINGGI][' . ($no - 1) . ']" tabindex="' . ($idx + 6) . '" type="text" id="CPM_ATR_TINGGI-' . $no . '" class="number" maxlength="6" style="width:110px;height:30px;display:inline-block;font-size:small" value="" placeholder="Tinggi" />
			</td>
		</tr>
		<tr>
			<td align="left" valign="top">Nama rekening</td>
			<td align="left" valign="top"><span id="nama-rekening-' . $no . '" style="text-align:left;color:#1B1389;font-weight:bold"></span><br /><span id="warning-rekening"></span></td>

			
		</tr>
		<tr>
			<td>Jenis Waktu Pemakaian</td>
			<td>
				<select class="form-control" id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">' . $opt_type_masa . '</select>
			</td>
			<td colspan="2">
			<label>Alkohol/Rokok </label>
			<input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" onchange="hitungDetail(' . $no . ')" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" style="margin-left:20px"> Ya &nbsp;
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" onchange="hitungDetail(' . $no . ')" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" id="ForCheck2" checked /> Tidak</label>
		</td>
		
		</tr>

		<tr>	
			<td>Lokasi Reklame</td>
			<td>
			<select class="form-control" id="CPM_ATR_JALAN-' . $no . '" tabindex="' . ($idx + 3) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_JALAN][]">' . $opt_jalan . '</select>
			</td>
			
		<td colspan="2">
			<select class="form-control text-center" onchange="hitungDetail(' . $no . ')" name="RMS" id="RMS-' . $no . '">
				<option value="">-->PILIH RUMUS<--</option>
				<option value="RMS1">RUMUS 1</option>
				<option value="RMS2">RUMUS 2</option>
			</select>
		</td>
			
		</tr>
		
		<!--<tr>
			<td>Pembayaran Melalui Pihak Ketiga</td>
			<td>
				<input name="PAJAK_ATR[CPM_CEK_PIHAK_KETIGA][' . ($no - 1) . ']" style="width: 20px;" type="checkbox" id="CPM_CEK_PIHAK_KETIGA-' . $no . '" value="1" />
				<input name="PAJAK_ATR[CPM_NILAI_PIHAK_KETIGA][' . ($no - 1) . ']" style="width: 240px;" placeholder="Nilai Pihak Ketiga" type="text" id="CPM_NILAI_PIHAK_KETIGA-' . $no . '" readonly="readonly" />
			</td>
		</tr>-->
		<tr>
			<td>Biaya Tarif Pajak</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_BIAYA][' . ($no - 1) . ']" style="width: 260px;" placeholder="Biaya Tarif Pajak" type="text" class="number" id="CPM_ATR_BIAYA-' . $no . '" readonly />
			</td>
			<td align="left" colspan="4" rowspan="6" valign="top">
				<div id="area_perhitungan-' . $no . '"></div>
			</td>

		</tr>

		<tr>
			<td>Harga Dasar Ukuran</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_UK][' . ($no - 1) . ']" style="width: 260px;" placeholder="Harga Dasar Ukuran" type="text" class="number" id="CPM_ATR_HARGA_DASAR_UK-' . $no . '" readonly />
			</td>
		</tr>

		<tr>
			<td>Harga Dasar Ketinggian</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][' . ($no - 1) . ']" style="width: 260px;" placeholder="Harga Dasar Ketinggian" type="text" class="number" id="CPM_ATR_HARGA_DASAR_TIN-' . $no . '" readonly />
			</td>

		</tr>
		<tr>
			<td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
			<td align="left" valign="top"><div align="left">
					<textarea name="PAJAK_ATR[CPM_ATR_JUDUL][' . ($no - 1) . ']" id="CPM_ATR_JUDUL-' . $no . '" tabindex="' . ($idx + 8) . '" style="width: 260px;" placeholder="Judul Reklame"></textarea>
				</div></td>
		</tr>
		<tr>
			<td align="left" valign="top">Lokasi <b class="isi">*</b></td>
			<td align="left" valign="top"><div align="left">
					<textarea name="PAJAK_ATR[CPM_ATR_LOKASI][' . ($no - 1) . ']" id="CPM_ATR_LOKASI-' . $no . '" tabindex="' . ($idx + 9) . '" style="width: 260px;" placeholder="Lokasi"></textarea>
				</div></td>
		</tr>
		<tr>
			<td colspan="6" align="right" valign="top">
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][' . ($no - 1) . ']" id="CPM_ATR_ID-' . $no . '" value="" />
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][' . ($no - 1) . ']" id="CPM_ATR_TOTAL-' . $no . '" value="0" />
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][' . ($no - 1) . ']" id="CPM_ATR_TARIF-' . $no . '" value="0" />
				<button type="button" onclick="delRow(' . $no . ',\'xxxxxx\')">Hapus</button>
			</td>
		</tr>
	</table>';
	}

	function delRow()
	{
		$no = (int)$_REQUEST['no'];
		$idatr = (int)$_REQUEST['idatr'];

		$query = sprintf("DELETE FROM patda_reklame_doc_atr WHERE CPM_ATR_ID='%s'", $idatr);

		$resdel = mysqli_query($this->Conn, $query);
		$output = array('status' => 0, 'pesan' => 'Item gagal dihapus. Silahkan coba lagi!');
		if ($resdel) {
			$output = array('status' => 1, 'pesan' => 'Item berhasil dihapus');
		}
		echo json_encode($output);
	}
}
