<?php

/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : , print_sspd
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

	public function list_pemakaian($type = '')
	{
		$rek = (isset($_REQUEST['CPM_ATR_REKENING']) && $_REQUEST['CPM_ATR_REKENING'] != "") ?  $_REQUEST['CPM_ATR_REKENING'] : "";

		if ($rek == '4.1.01.09.01.001' || $rek == '4.1.01.09.01.001.1' || $rek == '4.1.01.09.01.002' || $rek == '4.1.01.09.01.002.1' || $rek == '4.1.01.09.01.003' || $rek == '4.1.01.09.05' || $rek == '4.1.01.09.10.001') { // Vidiotron/megatron, Reklame Billboard, Reklame Kendaraan berjalan, Reklame Peragaan dan Reklame Neonbox
			$list_pemakaian = array(1 => 'Tahun');
		} elseif ($rek == '4.1.01.09.02') { // Reklme Kain dan Sejenisnya
			$list_pemakaian = array(
				4 => 'Bulan',
				5 => 'Minggu',
				6 => 'Hari'
			);
		} elseif ($rek == '4.1.01.09.06' || $rek == '4.1.01.09.06') { // Udara dan Apung
			$list_pemakaian = array(4 => 'Bulan');
		} else {
			$list_pemakaian = array(
				1 => 'Tahun',
				4 => 'Bulan',
				5 => 'Minggu',
				6 => 'Hari'
			);
		}

		foreach ($list_pemakaian as $a => $id) {
			$list .= '<option value="' . $a . '">' . $id . '</option>';
		}

		echo $list;
	}

	public function get_pajak($npwpd = '', $nop = '')
	{
		// var_dump($npwpd);die;
		$Op = new ObjekPajak();
		$arr_rekening = $this->getRekening("4.1.01.09");
		$pajak_atr = array();
		$list_nop = array();

		$check = "SELECT COUNT(op_nomor) AS jml_tunggak FROM simpatda_gw WHERE op_nomor='$nop' AND payment_flag=0";

		$resultcheck = mysqli_query($this->Conn, $check);
		$check_tunggak = $this->get_field_array($resultcheck);
		$jml_tunggak = $check_tunggak['jml_tunggak'];

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
				'CPM_ATR_TINGGI' => '',
				'CPM_ATR_KAWASAN' => '',
				'CPM_ATR_JALAN' => '',
				'CPM_ATR_JALAN_TYPE' => '',
				'CPM_ATR_SUDUT_PANDANG' => '',
				'CPM_ATR_PERHITUNGAN' => '',
				'CPM_ATR_NJOP' => '',
				'CPM_ATR_NILAI_STRATEGIS' => '',
				'CPM_CEK_PIHAK_KETIGA' => '',
				'CPM_NILAI_PIHAK_KETIGA' => '',
				'CPM_ATR_GEDUNG' => '',
				'CPM_ATR_BANGUNAN' => '',
				'type_masa' => '',
				'nmrek' => '',
				'CPM_ATR_JAM' => '',
				'CPM_RUMUS' => ''
			);
			$pajak_atr[] = $atr;
			// $pajak_atr[] = $atr;
			// $pajak_atr[] = $atr;

			$list_nop = $Op->get_list_nop($npwpd);
		} else { //if data available
			// $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
			$query = "SELECT atr.CPM_ATR_ID, atr.CPM_ATR_ID_PROFIL,atr.CPM_ATR_JENIS, atr.CPM_ATR_JUDUL, atr.CPM_ATR_LOKASI, atr.CPM_ATR_PANJANG, atr.CPM_ATR_LEBAR,
			atr.CPM_ATR_MUKA, atr.CPM_ATR_JARI, atr.CPM_ATR_JUMLAH, atr.CPM_ATR_BATAS_AWAL, atr.CPM_ATR_BATAS_AKHIR, atr.CPM_ATR_BIAYA, atr.CPM_ATR_HARGA, atr.CPM_ATR_TOTAL,
			atr.CPM_ATR_REKENING, atr.CPM_ATR_TYPE_MASA, atr.CPM_ATR_KAWASAN, atr.CPM_ATR_JALAN, atr.CPM_ATR_JALAN_TYPE ,atr.CPM_ATR_SUDUT_PANDANG, atr.CPM_ATR_PERHITUNGAN,
			atr.CPM_CEK_PIHAK_KETIGA, atr.CPM_NILAI_PIHAK_KETIGA,
			atr.CPM_ATR_NJOP, atr.CPM_ATR_NILAI_STRATEGIS,
			per.nmrek ,atr.CPM_ATR_TARIF, atr.CPM_ATR_JUMLAH_TAHUN,  atr.CPM_ATR_JUMLAH_BULAN,  atr.CPM_ATR_JUMLAH_MINGGU, atr.CPM_ATR_JUMLAH_HARI, per.type_masa, per.nmrek ,prf.CPM_NPWPD, prf.CPM_NOP, prf.CPM_NAMA_OP, prf.CPM_ALAMAT_OP,
			atr.CPM_ATR_SISI, atr.CPM_ATR_TINGGI, atr.CPM_ATR_HARGA_DASAR_UK, atr.CPM_ATR_HARGA_DASAR_TIN, atr.CPM_ATR_GEDUNG, atr.CPM_ATR_BANGUNAN, atr.CPM_ATR_ALKOHOL_ROKOK, atr.CPM_ATR_TOL, atr.CPM_ATR_JAM, prf.CPM_KECAMATAN_OP,  atr.CPM_RUMUS
			FROM PATDA_REKLAME_DOC_ATR AS atr
			INNER JOIN PATDA_REKLAME_DOC AS doc ON doc.CPM_ID = atr.CPM_ATR_REKLAME_ID
			INNER JOIN PATDA_REKLAME_PROFIL AS prf ON prf.CPM_ID = atr.CPM_ATR_ID_PROFIL
			INNER JOIN {$this->PATDA_REK_PERMEN13} AS per ON per.kdrek = atr.CPM_ATR_REKENING
			WHERE atr.CPM_ATR_REKLAME_ID = '{$this->_id}'";
			$result = mysqli_query($this->Conn, $query);
			$x = 0;
			$pajak_atr = [];
			while ($data = mysqli_fetch_assoc($result)) {
				$pajak_atr[$x] = $data;
				$npwpd = $data['CPM_NPWPD'];
				$x++;
			}
			// echo '<pre>';
			// print_r($pajak_atr);exit;

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
		$pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;
		$pajak['CPM_TRAN_CLAIM_DATETIME'] = $tran_date;

		$pajak = array_merge($pajak, $arr_rekening);


		//echo '<pre>',print_r($pajak,true),'</pre>';exit;
		return array(
			'pajak' => $pajak,
			'profil' => $profil,
			'pajak_atr' => $pajak_atr,
			'jml_tunggak' => $jml_tunggak,
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
		$pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;

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
						</td>
                        <td bgcolor=\"#ffff00\">
                            <button type=\"submit\"  style=\"width:50px; height:30px;\" class=\"btn btn-sm btn-secondary\" id=\"cari-{$id}\">Cari</button>
                            <button type=\"button\"  style=\"width:90px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button>  
                        </td>
                    </tr></table></form>
                </div> ";
		return $html;
		// <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-bentang-panjang.xls.php')\">Cetak Bentang Panjang</button>            
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
								CPM_NAMA_WP: {title: 'Wajib Pajak',width: '10%'},
                                CPM_NAMA_OP: {title: 'Objek Pajak',width: '10%'},
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
								CPM_TOTAL_OMZET: {title: 'Pokok',width: '10%'},
                                CPM_TOTAL_OMZET: {title: 'Total Pajak',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '5%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '10%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
								
                                CPM_AUTHOR: {title: 'User Input',width: '10%'},
                                CPM_PERPANJANGAN: {title: 'Perpanjangan',width: '5%'}
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
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val()
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
					$where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,6) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
				} else {
					$where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
				}
			} elseif ($this->_mod == "ver2") { #verifikasi
				if ($this->_s == 0) { #semua data
					$where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5,6) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
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
			$where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NAMA_OP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";

			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
			$where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
			$where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
			$where .= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_KODE_REKENING']}' " : "";

			if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
				if ($_REQUEST['CPM_JENIS_PJK'] == 1)
					$where .= " AND pr.CPM_REKENING!='4.1.01.07.07'";
				elseif ($_REQUEST['CPM_JENIS_PJK'] == 2)
					$where .= " AND pr.CPM_REKENING='4.1.01.07.07'";
			}

			#count utk pagging
			$query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj
                            INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                            WHERE {$where}";
			$result = mysqli_query($this->Conn, $query);
			// var_dump($result);die;
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,pj.CPM_PERPANJANG,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP,  pr.CPM_NAMA_OP, pr.CPM_NOP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_PIUTANG
                            FROM {$this->PATDA_REKLAME_DOC} pj INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
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

				$func = $this->_f;
				if ($row['CPM_PIUTANG'] == 1) {
					$func = 'fPatdaLaporPiutang8';
				}

				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
				$url = "main.php?param=" . base64_encode($base64);


				if ($row['CPM_PERPANJANGAN'] == 1) {
					$row['CPM_PERPANJANGAN'] = 'Ya';
				} else {
					$row['CPM_PERPANJANGAN'] = 'Tidak';
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
                                CPM_AUTHOR: {title: 'User Input',width: '10%'},
                                CPM_PERPANJANGAN: {title: 'Perpanjangan',width: '5%'},
								" . ($this->_s == 5 ? "action: {title: '',width: '10%'}," : "") . "
								
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
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val()
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
			$where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
			// $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
			$where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NAMA_OP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";

			$where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
			//$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
			$where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
			$where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

			#count utk pagging
			$query = "SELECT COUNT(DISTINCT pj.CPM_ID) AS RecordCount FROM {$this->PATDA_REKLAME_DOC} pj
                            INNER JOIN {$this->PATDA_REKLAME_DOC_ATR} atr ON atr.CPM_ATR_REKLAME_ID = pj.CPM_ID
							INNER JOIN {$this->PATDA_REKLAME_PROFIL} pr ON pr.CPM_ID = atr.CPM_ATR_ID_PROFIL
							INNER JOIN {$this->PATDA_REKLAME_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_REKLAME_ID
                            WHERE {$where} ";

			$result = mysqli_query($this->Conn, $query);
			$row = mysqli_fetch_assoc($result);
			$recordCount = $row['RecordCount'];

			#query select list data
			$query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, pj.CPM_TAHUN_PAJAK,
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
							// var_dump($query);exit;
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
				// var_dump($func);
				// die;
				$base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
				$perpanjangan = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f=fPatdaPelayanan10&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
				$url = "main.php?param=" . base64_encode($base64);
				$urlperpanjangan = "main.php?param=" . base64_encode($perpanjangan);

				if ($row['CPM_TRAN_STATUS'] != '5') {
					$row['CPM_TRAN_DATE'] = '-';
				}

				if ($row['CPM_PERPANJANGAN'] == 1) {
					$row['CPM_PERPANJANGAN'] = 'Ya';
				} else {
					$row['CPM_PERPANJANGAN'] = 'Tidak';
				}

				$row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
				// $row['action'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">perpanjangan</a>";
				$row['action'] = "<a href=\"{$urlperpanjangan}\" title=\"Perpanjangan\"><img src=\"inc/PATDA-V1/jtable/themes/notes.png\" title=\"Perpanjangan\" /></a>";

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
		// exit('deries');
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

	function getIDBerkas($nosptpd)
	{
		// var_dump('adsas');die;
		global $DBLink;
		$patdaberkas = '';
		$qry = "select * from patda_berkas where CPM_NO_SPTPD = '$nosptpd'";
		$res = mysqli_query($DBLink, $qry);
		while ($row = mysqli_fetch_assoc($res)) {
			$patdaberkas = $row['CPM_ID'];
		}

		return $patdaberkas;
	}


	public function save_final()
	{

		if ($this->CPM_PIUTANG == 1) {
			if ($this->validasi_piutang() == false) {
				return false;
			}
		}
		// var_dump('sadasdas');
		// die;
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
			// exit();
			$berkass = $this->getIDBerkas($this->CPM_NO);
			$base644 = "a=aPatda&m=mPatdaPelayanan&f=fPatdaBerkas&id={$berkass}&sts=0&read=1";
			$urlberkas = "main.php?param=" . base64_encode($base644);
			if ($res) {
				$_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
				header("Location: ../../../../{$urlberkas}");
				exit();
			} else {
				$_SESSION['_error'] = 'Data Pajak gagal difinalkan';
			}
		}
	}

	public function save_final_perpanjangan()
	{
		// var_dump($this->CPM_PIUTANG);
		// die;
		// if ($this->CPM_PIUTANG == 1) {
		// 	if ($this->validasi_piutang() == false) {
		// 		return false;
		// 	}
		// }
		// var_dump('perpanjangan');
		// die;
		$this->CPM_VERSION = "1";
		if ($this->save_pajak_perpanjangan($this->CPM_NO)) {
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
			// exit();
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
			$param['CPM_TRAN_REKLAME_VERSION'] = $this->CPM_VERSION;
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
			$query = sprintf(
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
			//echo $query;exit();
			$upd = mysqli_query($this->Conn, $query);

			$ok = 0;
			$j = count($PAJAK_ATR['CPM_ATR_REKENING']);
			for ($x = 0; $x < $j; $x++) {
				$atr_id = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_ID'][$x]);
				$judul = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_JUDUL'][$x]);
				$kawasan = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_ATR_KAWASAN'][$x]);
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
				$tarif = $this->toNumber($PAJAK_ATR['CPM_ATR_TARIF'][$x]);
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
				$hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
				$hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
				$tinggi = mysql_escape_string($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
				$gedung = mysql_escape_string($PAJAK_ATR['CPM_ATR_GEDUNG'][$x]);
				$bangunan = mysql_escape_string($PAJAK_ATR['CPM_ATR_BANGUNAN'][$x]);
				$alkohol_rokok = (isset($PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x])) ? $PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x] : '0';
				$tol = (isset($PAJAK_ATR['CPM_ATR_TOL'][$x])) ? $PAJAK_ATR['CPM_ATR_TOL'][$x] : '0';
				$jam = (isset($PAJAK_ATR['CPM_ATR_JAM'][$x])) ? $PAJAK_ATR['CPM_ATR_JAM'][$x] : '0';
				$jam1 = (isset($PAJAK_ATR['CPM_ATR_JAM1'][$x])) ? $PAJAK_ATR['CPM_ATR_JAM1'][$x] : '0';


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
						'kdrek' => $norekening,
						'kawasan' => $kawasan,
						// 'jalan' => $jalan,
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


				if ($atr_id == '') {
					$query = sprintf(
						"INSERT INTO {$this->PATDA_REKLAME_DOC_ATR}
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA, CPM_ATR_HARGA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_PANJANG, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA,
                            CPM_ATR_NILAI_STRATEGIS, CPM_ATR_KAWASAN, CPM_ATR_JALAN, CPM_ATR_JALAN_TYPE, CPM_ATR_SUDUT_PANDANG, CPM_ATR_NJOP, CPM_ATR_PERHITUNGAN,CPM_CEK_PIHAK_KETIGA,CPM_NILAI_PIHAK_KETIGA,CPM_ATR_SISI, CPM_ATR_ID_PROFIL, CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, CPM_ATR_GEDUNG, CPM_ATR_BANGUNAN, CPM_ATR_ALKOHOL_ROKOK, CPM_ATR_TOL,
							CPM_ATR_JAM)
                            VALUES ('%s','%s',%f,%f,'%s',
                                    %f,%f,%f,%f,%f,
                                    %f,'%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s','%s','%s','%s','%s','%s','%s','%s')",
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
						$jalan_type,
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
						$bangunan,
						$alkohol_rokok,
						$tol,
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

						CPM_ATR_NILAI_STRATEGIS='%s', CPM_ATR_KAWASAN='%s', CPM_ATR_JALAN='%s', CPM_ATR_JALAN_TYPE='%s', CPM_ATR_SUDUT_PANDANG='%s', CPM_ATR_NJOP='%s', CPM_ATR_PERHITUNGAN='%s',
						CPM_CEK_PIHAK_KETIGA='%s', CPM_NILAI_PIHAK_KETIGA='%s',
						CPM_ATR_SISI = '%s', CPM_ATR_HARGA_DASAR_UK = '%s', CPM_ATR_HARGA_DASAR_TIN = '%s', CPM_ATR_TINGGI = '%s', CPM_ATR_GEDUNG = '%s', CPM_ATR_BANGUNAN = '%s', CPM_ATR_ALKOHOL_ROKOK = '%s', CPM_ATR_TOL = '%s',
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
						$jalan_type,
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
						$bangunan,
						$alkohol_rokok,
						$tol,
						$jam,
						$atr_id
					);
				}
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


	// public function verifikasi()
	// {
	// 	if ($this->AUTHORITY == 1) {
	// 		$query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
	// 		// echo $query;exit;
	// 		$res = mysqli_query($this->Conn, $query);
	// 		if (mysqli_num_rows($res) == 0) {
	// 			$msg = "Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!";
	// 			$this->Message->setMessage($msg);
	// 			$_SESSION['_error'] = $msg;
	// 			return false;
	// 		}
	// 	}
	// 	$this->verifikasi_2();
	// }

	public function verifikasi()
	{
		if ($this->AUTHORITY == 1) {
			$query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
			// echo $query;exit;
			$res = mysqli_query($this->Conn, $query);
			// if (mysqli_num_rows($res) == 0) {
			// 	$msg = "Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!";
			// 	$this->Message->setMessage($msg);
			// 	$_SESSION['_error'] = $msg;
			// 	return false;
			// }
		}
		$this->verifikasi_2();
	}

	public function persetujuan()
	{
		$new_operator = $_SESSION['uname'];
		// var_dump($this);
		// die;
		$status = ($this->AUTHORITY == 1) ? 6 : 4;
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
				$this->update_jatuh_tempo($this->EXPIRED_DATE);
				$_SESSION['_success'] = 'Data Pajak berhasil disetujui';
			} else {
				$_SESSION['_error'] = 'Data Pajak gagal disetujui';
			}
		}
	}

	public function verifikasi_2()
	{
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		$new_operator = $_SESSION['uname'];
		// var_dump($this);
		// die;
		// echo '<pre>';
		// print_r($this);
		// echo '</pre>';
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
				$this->update_jatuh_tempo($this->EXPIRED_DATE);
				$_SESSION['_success'] = 'Data Pajak berhasil disetujui';
			} else {
				$_SESSION['_error'] = 'Data Pajak gagal disetujui';
			}
		}
	}


	private function update_jatuh_tempo($expired_date)
	{
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

		$pdf->AddPage('P', 'F4');
		$pdf->writeHTML($html, true, false, false, false, '');
		// $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 18, 25, '', '', '', '', false, 300, '', false);
		$pdf->SetAlpha(0.3);

		$pdf->Output('skpd-reklame.pdf', 'I');
	}


	public function print_skpd($type = "")
	{

		global $sRootPath;
		// tambahan qr
		require_once($sRootPath . "qrcode.php");
		// var_dump($sRootPath);die;
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbTable = $arr_config['PATDA_TABLE'];
		$dbUser = $arr_config['PATDA_USERNAME'];
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		// tambahan qr
		// require_once($sRootPath . "qrcode.php");

		$this->_id = $this->CPM_ID;
		$DATA = $this->get_pajak();
		$data = $DATA['pajak'];
		$profil = $DATA['profil'];
		$pajak_atr = $DATA['pajak_atr'];
		// $tranmain = $this->get_Statustranmain();
		$upt_code = count($DATA['pajak_atr']) > 0 ? $DATA['pajak_atr'][0]['CPM_KECAMATAN_OP'] : false;
		$DATA = array_merge($data, $profil);
		$DATA['pajak_atr'] = $pajak_atr;

		// added by d312Is
		$q = sprintf("SELECT CPM_KECAMATAN FROM patda_mst_kecamatan WHERE CPM_KEC_ID ='$upt_code'");
		$result = mysqli_query($Conn_gw, $q);
		$CPM_KECAMATAN_OP = '-';
		if ($upt = mysqli_fetch_object($result)) {
			$CPM_KECAMATAN_OP = $upt->CPM_KECAMATAN;
		}

		$CPM_TRAN_REKLAME_ID = $DATA['CPM_ID'];

		$queryTran = sprintf("SELECT CPM_TRAN_DATE FROM patda_reklame_doc_tranmain WHERE CPM_TRAN_REKLAME_ID ='$CPM_TRAN_REKLAME_ID'");
		$result = mysqli_query($Conn_gw, $queryTran);
		if ($date_tran = mysqli_fetch_object($result)) {
			$CPM_TRAN_REKLAME_ID2 = $date_tran->CPM_TRAN_DATE;

			$tgl_obj = $CPM_TRAN_REKLAME_ID2;
			$k = explode('-', $tgl_obj);
			$tgl_obj = date_create($k[2] . '-' . $k[1] . '-' . $k[0]);
			$tgl_obj->modify('+1 month');
			$tgl_obj->modify('-1 day');
			$tgl_ke_depan = date_format($tgl_obj, "Y-m-d");
			// echo '<pre>';
			// print_r( $tgl_ke_depan );exit;
		}

		// $tgl_jatuh_tempo = $DATA['CPM_MASA_PAJAK1'];
		// $tgl_obj = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
		// $tgl_obj->modify('+1 month');
		// $tgl_obj->modify('-1 day');
		// $tgl_ke_depan = $tgl_obj->format('Y-m-d');

		// echo'<pre>';print_r($DATA);exit;
		$arr_rekening = $this->getRekening();

		//mysqli_select_db($dbName, $Conn_gw);
		$query = sprintf("select * from SIMPATDA_GW gw INNER JOIN PATDA_REKLAME_DOC_TRANMAIN tr ON tr.CPM_TRAN_REKLAME_ID=gw.id_switching WHERE tr.CPM_TRAN_STATUS = 5 AND gw.id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($gw = mysqli_fetch_object($res)) {
			$DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
			// $DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
		}

		// $con = $gw->payment_code;
		// echo'<pre>';print_r($con);exit;

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

		if ($DATA['A_QR'] == '') {
			$tgl_approve = '';
		} else {
			$pisahkan = explode("%0A", $DATA['A_QR']);
			$pisahkan_substring = substr($pisahkan[4], 5, 10);

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
				return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
			}

			$tgl_approve = tgl_indo_full($pisahkan_substring);
		}
		//var_dump($pisahkan_substring);die;
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
		$tanggal_sekarang = time();
		$batas_tanggal = strtotime('2023-07-12');
		if ($tanggal_sekarang > $batas_tanggal) {
			$KEPALA_NAMA = $config['KEPALA_DINAS_NAMA'];
			$KEPALA_NIP = $config['KEPALA_DINAS_NIP'];
		} else {
			$KEPALA_NAMA = "Drs. EKO DIAN SUSANTO, M.IP.";
			$KEPALA_NIP = "196709111993031009";
		}

		// $DATA['pajak_atr'] = $DATA['pajak_atr'][0];
		// unset($DATA['pajak_atr'][0]);

		$d = explode('/REK/', $DATA['CPM_NO']);
		$NO_URUT = $d[0]; //.'<br/>/REK/'.$d[1];

		$DENDA = 0;
		$TOTAL = $DATA['CPM_TOTAL_PAJAK'];

		// hitung denda
		// if(isset($gw) && !empty($gw)){
		// 	if($gw->payment_flag=='1'){
		// 		$TOTAL = $gw->patda_total_bayar;
		// 		$DENDA = $gw->patda_denda;
		// 	}elseif(strtotime(date('Y-m-d')) > strtotime($gw->expired_date)){
		// 		$persen_denda = $this->get_persen_denda($gw->expired_date);
		// 		$DENDA = ($persen_denda/100) * $TOTAL;
		// 		$TOTAL = $TOTAL + $DENDA;
		// 	}
		// }

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
		$masa_pajaks3 = $arr_tglx[0] - 1 . ' ' . $this->arr_bulan[$arr_tglx[1] + 1] . ' ' . $arr_tglx[2];

		$bulanxx = str_replace('/', '-', $DATA['CPM_MASA_PAJAK2']);
		$bulanxx = date('d/m/Y', strtotime($bulanxx));
		$arr_tglxx = explode('/', $bulanxx);
		$arr_tglxx = array_map(function ($v) {
			return (int) $v;
		}, $arr_tglxx);
		$masa_pajaks2 = $arr_tglxx[0] . ' ' . $this->arr_bulan[$arr_tglxx[1]] . ' ' . $arr_tglxx[2];
		// $masa_pajaks3 = $arr_tglxx[0] . ' ' . $this->arr_bulan[$arr_tglxx[1]+1] . ' ' . $arr_tglxx[2];
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

		// $tgl_jatuh_tempo = $DATA['CPM_MASA_PAJAK1'];
		// $tgl_obj = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
		// $tgl_obj->modify('+1 month');
		// $tgl_obj->modify('-1 day');
		// $tgl_ke_depan = $tgl_obj->format('Y-m-d');



		$total_omzet = $DATA['CPM_TOTAL_OMZET'];

		// $subtotal = $DATA['pajak_atr'][0]['CPM_ATR_HARGA'] * ($DATA['pajak_atr'][0]['CPM_ATR_TARIF'] / 100) * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
		$hargaDasar = $DATA['pajak_atr'][0]['CPM_ATR_HARGA'] * ($DATA['pajak_atr'][0]['CPM_ATR_TARIF'] / 100);

		// * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
		$subtotal2 =  $total_omzet - ($total_omzet * 0.5);

		$total_pajak2 = $subtotal2 - $subtotal;
		if ($DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
			$subtotal = $hargaDasar * $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] * $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
			$alkoholTotal = $subtotal;
			// var_dump($alkoholTotal);
			// die;
			$kenaikan = $DATA['CPM_TOTAL_OMZET'] - $alkoholTotal;
			$totalKetetapan = $DATA['CPM_TOTAL_OMZET'] - $alkoholTotal;
			$totalKeseluruhan =  $totalKetetapan + $DENDA + $alkoholTotal;
			// var_dump($totalKeseluruhan, $totalKetetapan, $alkoholTotal);
			// // var_dump();
			// die;
		} else {
			$totalKetetapan = $DATA['CPM_TOTAL_OMZET'];
			$totalKeseluruhan =  $DATA['CPM_TOTAL_OMZET'] + $DENDA + $alkoholTotal;
			$alkoholTotal =  $totalKeseluruhan;
		}

		// echo '<pre>';
		// var_dump($DATA);
		// exit;
		// 		ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		$total_pajak =  $DATA['CPM_TOTAL_OMZET'];

		// hitung_denda
		$tgl_jth_tempo = $DATA['CPM_MASA_PAJAK1'];
		$tgl_jatuh_tempo_timestamp = DateTime::createFromFormat('d/m/Y', $tgl_jth_tempo)->format('Y-m-d');
		$tgl_1_bulan_setelah = date('Y-m-d', strtotime($tgl_jatuh_tempo_timestamp . ' +30 days'));
		// var_dump($tgl_1_bulan_setelah);die;
		if ($DATA['CPM_PERPANJANGAN'] == 1) {
			$tgl_jatuh_tempo = $DATA['CPM_MASA_PAJAK1'];
			$datetime = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
			$tanggal_baru = $datetime->format('Y-m-d');
			// var_dump($tanggal_baru);die;
			$persen_denda = $this->get_persen_denda($tanggal_baru);
			// if(perse){

			// }
			$DENDA = ($persen_denda / 100) * $total_pajak;
			$TOTAL = $total_pajak + $DENDA;
			// var_dump($DENDA);
			// die;
		} else {
			if ($DATA['CPM_START_DENDA'] == 1) {

				if ($gw->payment_flag == 0 && ($DATA['CPM_MASA_PAJAK1'] != '' || $DATA['CPM_MASA_PAJAK1'] != NULL)) {
					// Denda otomatis
					$total_pajak = $DATA['CPM_TOTAL_OMZET'];
					$tgl_jatuh_tempo = $DATA['CPM_MASA_PAJAK1'];
					$tgl_jatuh_tempo_obj = date_create_from_format("d/m/Y", $tgl_jatuh_tempo);
					$tgl_sekarang = date("Y-m-d");
					// die;
					// Menghitung selisih bulan dan tahun antara tanggal sekarang dan tanggal jatuh tempo
					$selisih_bulan = 0;
					$selisih_tahun = 0;
					$tgl_sekarang_obj = date_create_from_format("Y-m-d", $tgl_sekarang);
					if ($tgl_jatuh_tempo_obj < $tgl_sekarang_obj) {
						$selisih = date_diff($tgl_jatuh_tempo_obj, $tgl_sekarang_obj);
						$selisih_bulan = $selisih->y * 12 + $selisih->m;
					}
					// var_dump($selisih_bulan);exit;
					// var_dump($selisih_bulan, $tgl_jatuh_tempo_obj , $tgl_sekarang);exit;
					// Menentukan jumlah denda per bulan dan jumlah maksimal denda
					$denda_per_bulan = 0.01; // 2% per bulan
					$denda_max = 0.24; // 24% maksimal denda

					// Menerapkan denda
					if ($selisih_bulan > 0) {
						$Denda = 0;
						$DENDA = $total_pajak * ($selisih_bulan * $denda_per_bulan);
						// var_dump($total_pajak,$selisih_bulan,$denda_per_bulan);exit;
						// Memastikan bahwa denda tidak melebihi jumlah maksimal yang ditetapkan
						if ($DENDA > ($total_pajak * $denda_max)) {
							$DENDA = $total_pajak * $denda_max;
						}

						$total_pajak += $DENDA;

						// Code Pembulatan
						if (($DENDA * 100) % 100 < 50) {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_DOWN);
						} else {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_UP);
						}


						// End code Pembulatan

						$tgl_kena_denda = $tgl_jatuh_tempo_obj->add(new DateInterval('P' . $selisih_bulan . 'M'))->format('d-m-Y');
						// var_dump($tgl_kena_denda);die;
					} else {
						$DENDA = 0;
					}
					$total = $DATA['pajak']['CPM_TOTAL_OMZET'] + $DENDA;
				} else {
					$DENDA = $gw->patda_denda;
					$total = $DATA['pajak']['CPM_TOTAL_OMZET'] + $DENDA;
				}
			} else {
				$DENDA = 0;
				$total = $DATA['pajak']['CPM_TOTAL_OMZET'];
			}
		}


		if ($gw->payment_flag == 1) {
			$DENDA = $gw->patda_denda;
		}

		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\">
                                <tr>
                                    <td width=\"350\" valign=\"top\" align=\"center\" colspan=\"3\">
										<table border=\"0\" width=\"310\">
											<tr>
												<td width=\"70\"></td>
												<td width=\"250\">
													<b style=\"font-size:32px\"><br/>
													" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br/>
													{$NAMA_PENGELOLA}<br/>
													
													{$JALAN}
													<!--{$KOTA} - {$PROVINSI}-->
													</b>
												</td>
											</tr>
										</table>
										<br/>
                                    </td>
                                    <td width=\"240\" valign=\"top\" align=\"center\">
									<b style=\"font-size:42px\"><br/>
									SKPD<br/>
										</b>
									
										<b style=\"font-size:37px\">
										(SURAT KETETAPAN PAJAK DAERAH)<br/>
										</b>
                                    </td>
                                    <td width=\"120\" valign=\"top\" align=\"center\">
										<br/><br/><br/>
										<b style=\"font-size:34px\">Kode Billing<br/><br/>
										{$gw->payment_code}</b><br/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
						<td width=\"710\">
							<table width=\"710\" border=\"0\" cellpadding=\"2\" style=\"font-size:28px;\">
							<tr>
								<td width=\"200\"></td>
								<td width=\"100\" >MASA PAJAK</td>
								<td width=\"300\" >: {$masa_pajaks1} S/d {$masa_pajaks2}</td>
							</tr>
							<tr>
								<td></td>
								<td>TAHUN</td>
								<td>: {$DATA['CPM_TAHUN_PAJAK']}</td>
							</tr>
							</table>
							<table width=\"710\" border=\"0\" cellpadding=\"5\" style=\"font-size:28px;\">
								<tr>
									<td width=\"400\">
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"150\">Nama Pemilik</td>
												<td width=\"550\">: {$DATA['CPM_NAMA_WP']}</td>
											</tr>
											<tr>
												<td>Alamat Wajib Pajak</td>
												<td>:&nbsp; {$DATA['CPM_ALAMAT_WP']}</td>
												<td></td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; {$DATA['CPM_KELURAHAN_WP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp; KEC. {$DATA['CPM_KECAMATAN_WP']}</td>
											</tr>
											<tr>
												<td></td>
												<td>&nbsp;&nbsp;{$DATA['CPM_KOTA_WP']}</td>
											</tr>
											<tr>
												<td>Alamat Objek Pajak</td>
												<td>: {$DATA['pajak_atr'][0]['CPM_ALAMAT_OP']}</td>
											</tr>
											<tr>
												<td>NPWPD</td>
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td>Nomor Pelaporan</td>
												<td>: {$DATA['CPM_NO']}</td>
											</tr>
										
										</table>
									</td>
									<td width=\"310\">
									</td>
								</tr>
							</table>

							<table width=\"710\" border=\"0\" cellpadding=\"5\" style=\"font-size:28px;\">
								<tr>
									<td width=\"400\">
										<table width=\"300\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td>Tanggal Jatuh Tempo</td>
												<td>:  " . tgl_indo($tgl_1_bulan_setelah) . "</td>
											</tr>
										</table>
									</td>
									<td width=\"400\">
										<table width=\"300\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td><b>UPT : {$CPM_KECAMATAN_OP}</b></td>
											</tr>
										</table>
									</td>
									
								</tr>
							</table>
						</td>
                    </tr>

                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\" style=\"font-size:34px;\">
							<tr>
								<td colspan=\"2\">
									<table width=\"500\" cellpadding=\"3\" border=\"1\" cellspacing=\"0\" style=\"font-size:28px;\">
										<tr style=\"background-color:#CCC\">
											<td width=\"30\" align=\"center\"><b>No</b></td>
											<td width=\"170\" align=\"center\"><b>Kode Rekening</b></td>
											<td width=\"420\" align=\"center\"><b>Uraian Pajak Daerah</b></td>
											<td width=\"80\" align=\"center\"><b>Jumlah (Rp.)</b></td>
										</tr>
										<tr>
											<td align=\"center\">1.</td>
											<td align=\"center\">4.1.1.0.4</td>
											<td>Pajak Reklame</td>
											<td align=\"right\"></td>
										</tr>";

		$html = '';

		$list_op = '';
		$total_keseluruhan_ketetapan = '';
		$total_keseluruhan_penambahan = '';
		$total_keseluruhan_jumlah = $DATA['CPM_TOTAL_PAJAK'];
		foreach ($pajak_atr as $no => $atr) {

			$biaya =  $atr['CPM_ATR_BIAYA'] / 100;
			$panajng_lebar =  $atr['CPM_ATR_LEBAR'] * $atr['CPM_ATR_PANJANG'];
			$jam =  $atr['CPM_ATR_JAM'] * 60;
			if ($atr['CPM_ATR_REKENING'] == "4.1.01.09.01.005") {
				$tot_terpasang = round($atr['CPM_ATR_HARGA'] * $biaya);
				$total_ketetapan = $tot_terpasang * $panajng_lebar * intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'];
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.01.004") {
				$tot_terpasang = $atr['CPM_ATR_HARGA'];
				$total_ketetapan = $tot_terpasang * $panajng_lebar *  intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $jam * $biaya;
				// $total_ketetapan = $tot_terpasang * $panajng_lebar * $jam  * $atr['CPM_ATR_JUMLAH'];
				// var_dump( $tot_terpasang);die;
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.08") {
				$tot_terpasang = $atr['CPM_ATR_HARGA'];
				$total_ketetapan = $tot_terpasang * $panajng_lebar *  intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $jam * $biaya;
				// var_dump( $jam);die;
				// var_dump(intval($total_ketetapan));die;
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.10") {
				$total_ketetapan_awal = $atr['CPM_ATR_HARGA'] * $panajng_lebar * intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $biaya;
				$total_ketetapan = $total_ketetapan_awal * 0.5;
			} else {
				$total_ketetapan = $atr['CPM_ATR_HARGA'] * $panajng_lebar * intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $biaya;
			}

			if ($atr['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
				$penambahan = round(($total_ketetapan * 50) / 100);
				$total_seluruh = $total_ketetapan + $penambahan;
				$total_keseluruhan_penambahan += $penambahan; // tambahkan ini
				// } elseif ($atr['CPM_ATR_GEDUNG'] == 'DALAM') {
				// 	$hasil_pengurangan = round(($total_ketetapan * 35) / 100);
				// 	$penambahan = $total_ketetapan - $hasil_pengurangan;
				// 	$total_keseluruhan_penambahan += $penambahan;
				// 	$total_seluruh = $total_ketetapan - $penambahan;
			} elseif ($atr['CPM_ATR_GEDUNG'] == 'LUAR') {
				$total_seluruh = $total_ketetapan;
			} else {
				$penambahan = 0;
				$total_seluruh = $total_ketetapan;
			}
			$total_keseluruhan_ketetapan += $total_ketetapan;
			// $total_keseluruhan_penambahan += $penambahan;
			// $total_keseluruhan_jumlah += $total_seluruh;
			// if ($DATA['CPM_NO'] == '900001783/REK/23') {
			// 	$DENDA = 8409600;
			// 	$total_pajak = 43449600;
			// }
			$total_huruf = round($total_keseluruhan_jumlah + $DENDA);
			// var_dump($tot_terpasang, $panajng_lebar,  intval($atr['CPM_ATR_JUMLAH_HARI']), $atr['CPM_ATR_JUMLAH'], $jam);
			// die;


			if ($gw->sspd == '900001888/REK/23') {
				$DENDA = $gw->simpatda_denda;
				$total_keseluruhan_jumlah = $gw->simpatda_dibayar;
				$total_huruf = round($total_keseluruhan_jumlah);
			} elseif ($gw->sspd == '900001889/REK/23') {
				$DENDA = $gw->simpatda_denda;
				$total_keseluruhan_jumlah = $gw->simpatda_dibayar;
				$total_huruf = round($total_keseluruhan_jumlah);
			}


			if ($gw->sspd == '900002343/REK/23') {
				$total_keseluruhan_jumlah = '3623400';
			}


			if ($gw->sspd == '900002359/REK/23' || $gw->sspd == "900004131/REK/24") {
				$total_ketetapan = $atr['CPM_ATR_TOTAL'];
				$total_huruf = $gw->simpatda_dibayar;
				$total_keseluruhan_ketetapan = $gw->simpatda_dibayar;
				$DENDA = 0;
			}

			$tgl = explode('-', $gw->CPM_TRAN_DATE);
			// $date = tgl_indo_full($tgl);
			// var_dump($tgl[0], date('d'));
			// die;

			$no++;
			$html .= "<tr>
											<td align=\"center\"></td>
											<td align=\"center\">{$atr['nmrek']}</td>
											<td align=\"left\">
												[{$atr['CPM_NOP']}] Pembayaran Pajak Reklame Periode " . $this->formatDateForDokumen($atr['CPM_ATR_BATAS_AWAL']) . " s.d. " . $this->formatDateForDokumen($atr['CPM_ATR_BATAS_AKHIR']) . "
												" . number_format($atr['CPM_ATR_PANJANG'], 2) . "M x {$atr['CPM_ATR_LEBAR']}M x  " . intval($atr['CPM_ATR_JUMLAH']) . " Buah x " . intval($DATA['CPM_MASA_PAJAK']) . " {$DATA['CPM_JNS_MASA_PAJAK']} x Rp. " . number_format($atr['CPM_ATR_HARGA'], 2) . " / {$DATA['CPM_JNS_MASA_PAJAK']} / M<sup>2</sup> x 25% " . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == 1 ? "+" . number_format($penambahan) : "") . " " . ($atr['CPM_ATR_GEDUNG'] == 'DALAM' ? "- (" . number_format($penambahan) . ")" : "") . " " . ($atr['CPM_ATR_REKENING'] == '4.1.01.09.10' ? "- 50%" : "") . "<br>
												Text Reklame : " . $atr['CPM_ATR_JUDUL'] . ", Alamat OP : " . $atr['CPM_ALAMAT_OP'] . "
											</td>
											<td align=\"right\">" . number_format($total_ketetapan) . "</td>

											</tr>";

			$list_op .= $atr['CPM_NOP'] . ' | ' . $atr['CPM_NAMA_OP'] . ', ' . $atr['CPM_ALAMAT_OP'] . '<br>';
		}
		$html .= "<tr>
											<td align=\"left\" colspan=\"2\" rowspan=\"4\"></td>
											<td align=\"left\">
												Jumlah Ketetapan Pokok
											</td>
											<td align=\"right\">
												" .  number_format($total_keseluruhan_ketetapan) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												Penambahan/Pengurangan
											</td>
											<td align=\"right\">
												" . number_format($total_keseluruhan_penambahan) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												Denda
											</td>
											<td align=\"right\">
												" . number_format($DENDA, 0) . "
											</td>
										</tr>
										<tr>
											<td align=\"left\">
												<b>Jumlah Keseluruhan</b>
											</td>
											<td align=\"right\">
												<b>" .  number_format($total_keseluruhan_jumlah + $DENDA) . "</b>
											</td>
										</tr>
										<tr>
											<td align=\"left\" colspan=\"4\">
											Dengan huruf :<br/>
											<b><i>" . ucwords($this->SayInIndonesian($total_huruf)) . " rupiah</i></b>
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
								<td><table width=\"100%\" border=\"0\" align=\"left\" style=\"font-size:28px;\">
										<tr>
											<td><b>PERHATIAN : </b></td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap Penyetoran dilakukan pada Bank/Bendahara Penerimaan.</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Apabila SKPD ini tidak atau kurang dibayar lewat waktu paling lama 30 hari setelah SKPD diterima atau (tanggal jatuh tempo).<br>
											&nbsp;&nbsp; &nbsp; &nbsp;
											dikenakan sanksi/denda administrasi sebesar 2 % per bulan</td>
										</tr>
									</table>
									</td>
								</tr>
							</table>
                        </td>
                    </tr>
					
					<tr>
						<td colspan=\"2\" align=\"center\" style=\"font-size:24px;\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
								<tr>
									<td width=\"355\">Di verifikasi pada tanggal : {$gw->CPM_TRAN_DATE}</td>
									<td align=\"center\"><b>
										Bandar Lampung, " . $tgl[0] . " {$this->arr_bulan[(int)$tgl[1]]} " . $tgl[2] . " {$tgl_approve} <br/>
										A.n Kepala Badan Pendapatan Daerah <br/> Kota Bandar Lampung <br/>
										Kepala Bidang Pendaftaran dan Penetapan <br/>		";


		// var_dump($gw->simpatda_dibayar);
		// die;

		$total = $gw->simpatda_dibayar + $DENDA;

		$strQR = "ANDRE SETIAWAN, S.IP., M.Si\n";
		$strQR .= "NIP. 19871223 201001 1 002\n";
		$strQR .= "NOP : {$DATA['CPM_NO']}\n";
		$strQR .= "TAGIHAN : " . number_format($total) . "\n";

		$imageGenerator = new QRCode($strQR);
		$imageQr = $imageGenerator->render_image();
		imagepng($imageQr, 'qrcode.png', 9);
		$page1 .= '<table><tr>';
		$page1 .= '<td align="right" width="62%"><img src="qrcode.png" style="width:90px;height:90px;display:block"></td>';
		$page1 .= '<td align="left" style="font-size:24px"><br><br><br><br>Dokumen ini sah dan<br>telah di tanda tangani</td></tr></table>';

		$page1 .= "<br>
										<u>ANDRE SETIAWAN, S.IP., M.Si</u><br/>
										NIP. 19871223 201001 1 002
									</b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<br><br>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\" style=\"font-size:25px;\">
								<tr>
									<td align=\"center\">......................................potong di sini......................................<br></td>
								</tr>
								<tr>
									<td>&nbsp;&nbsp;&nbsp;<table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" style=\"font-size:33px;\">
											<tr style=\"font-size:27px;\">
												<td width=\"430\" colspan=\"2\"><b><u>Tanda Terima</u></b></td>
												<td width=\"270\" align=\"center\">No. SKPD : {$DATA['CPM_NO']}</td>
											</tr>
											<tr>
												<td colspan=\"3\" align=\"center\"><br/></td>
											</tr>
											<tr style=\"font-size:28px;\">
												<td width=\"100\">Nama</td>
												<td width=\"330\">: {$DATA['CPM_NAMA_WP']}</td>
												<td width=\"270\" rowspan=\"6\" align=\"center\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
												Yang Menerima,<br/><br/><br/><br/>

												<b><u>{$DATA['CPM_NAMA_WP']}</u></b>
												</td>
											</tr>
											<tr style=\"font-size:27px;\">
												<td>Alamat</td>
												<td>: {$DATA['CPM_ALAMAT_WP']} &nbsp;&nbsp;- {$DATA['CPM_KELURAHAN_WP']}</td>
											</tr>
											<tr style=\"font-size:28px;\">
												<td></td>
												<td>&nbsp; KEC. {$DATA['CPM_KECAMATAN_WP']}</td>
											</tr>
											<tr style=\"font-size:28px;\">
												<td></td>
												<td>&nbsp; KOTA. {$DATA['CPM_KOTA_WP']}</td>
											</tr>
											<tr style=\"font-size:28px;\">
												<td>NPWPD</td>
												<td>: " . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
											</tr>
											<tr style=\"font-size:25px;\">
												<td colspan=\"2\">{$list_op}</td>
											</tr>
											<tr style=\"font-size:28px;\">
												<td>Keterangan</td>
												<td>: {$DATA['CPM_KETERANGAN']}</td>
											</tr>
										</table>
									</td>
									<td>

									</td>
								</tr>
							</table>
						</td>
					</tr>
					
					<span style=\"font-size:24px\"><i>BAPENDA BALAM {$tgl_cetak}</i></span>
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

		$pdf->Output('skpd-reklame.pdf', 'I');
	}

	public function print_sptpd()
	{
		global $sRootPath;
		$this->_id = $this->CPM_ID;
		// var_dump($gya);
		// die;
		$DATA = $this->get_pajak();
		$data = $DATA['pajak'];
		$profil = $DATA['profil'];
		$pajak_atr = $DATA['pajak_atr'];

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
		$persen_terlambat_lap = $config_terlambat_lap ? $config_terlambat_lap->persen : 0;
		$editable_terlambat_lap = $config_terlambat_lap ? $config_terlambat_lap->editable : 0;
		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbTable = $config['PATDA_TABLE'];
		$dbUser = $config['PATDA_USERNAME'];
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		$query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($gw = mysqli_fetch_object($res)) {
			$DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
			// $DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
		}

		$upt_code = count($DATA['pajak_atr']) > 0 ? $DATA['pajak_atr'][0]['CPM_KECAMATAN_OP'] : false;
		$q = sprintf("SELECT CPM_KECAMATAN FROM patda_mst_kecamatan WHERE CPM_KEC_ID ='$upt_code'");
		$result = mysqli_query($Conn_gw, $q);
		$CPM_KECAMATAN_OP = '-';
		if ($upt = mysqli_fetch_object($result)) {
			$CPM_KECAMATAN_OP = $upt->CPM_KECAMATAN;
		}



		// echo '<pre>';
		// print_r($DATA);


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

		// if ($gw->payment_flag == 0 && ($DATA['pajak']['CPM_MASA_PAJAK2'] != '' || $DATA['pajak']['CPM_MASA_PAJAK2'] != NULL)) {
		// 	// Denda otomatis
		// 	$total_pajak = $gw->simpatda_dibayar;
		// 	$total_pajak = str_replace(',', '', $total_pajak);
		// 	$tgl_jatuh_tempo = $DATA['pajak']['CPM_MASA_PAJAK2'];
		// 	$tgl_jatuh_tempo_obj = date_create_from_format("d/m/Y", $tgl_jatuh_tempo);
		// 	$tgl_sekarang = date("Y-m-d");
		// 	// var_dump($total_pajak);exit;
		// 	// var_dump($tgl_sekarang);exit;
		// 	// Menghitung selisih bulan dan tahun antara tanggal sekarang dan tanggal jatuh tempo
		// 	$selisih_bulan = 0;
		// 	$selisih_tahun = 0;
		// 	$tgl_sekarang_obj = date_create_from_format("Y-m-d", $tgl_sekarang);
		// 	if ($tgl_jatuh_tempo_obj < $tgl_sekarang_obj) {
		// 		$selisih = date_diff($tgl_jatuh_tempo_obj, $tgl_sekarang_obj);
		// 		$selisih_bulan = $selisih->y * 12 + $selisih->m;
		// 	}
		// 	// var_dump($tgl_jatuh_tempo_obj < $tgl_sekarang_obj);die;
		// 	// Menentukan jumlah denda per bulan dan jumlah maksimal denda
		// 	$denda_per_bulan = 0.02; // 2% per bulan
		// 	$denda_max = 0.48; // 24% maksimal denda

		// 	// Menerapkan denda
		// 	if ($selisih_bulan > 0) {
		// 		$Denda = 0;
		// 		$DENDA = $total_pajak * ($selisih_bulan * $denda_per_bulan);
		// 		// var_dump($total_pajak,$selisih_bulan,$denda_per_bulan);exit;
		// 		// Memastikan bahwa denda tidak melebihi jumlah maksimal yang ditetapkan
		// 		if ($DENDA > ($total_pajak * $denda_max)) {
		// 			$DENDA = $total_pajak * $denda_max;
		// 		}

		// 		$total_pajak += $DENDA;

		// 		// Code Pembulatan
		// 		if (($DENDA * 100) % 100 < 50) {
		// 			$DENDA = round($DENDA, 0, PHP_ROUND_HALF_DOWN);
		// 		} else {
		// 			$DENDA = round($DENDA, 0, PHP_ROUND_HALF_UP);
		// 		}
		// 		// End code Pembulatan

		// 		$tgl_kena_denda = $tgl_jatuh_tempo_obj->add(new DateInterval('P' . $selisih_bulan . 'M'))->format('d-m-Y');
		// 		// var_dump ($tgl_jatuh_tempo,$tgl_kena_denda);die;
		// 	} else {
		// 		$DENDA = 0;
		// 	}
		// 	$total = $this->CPM_TOTAL_OMZET + $DENDA;
		// } else {
		// 	$DENDA = $gw->patda_denda;
		// 	$total = $this->CPM_TOTAL_OMZET;
		// }

		// var_dump($DATA['pajak_atr']);die;
		$npwdR = "P" . substr($DATA['profil']['CPM_NPWPD'], 1, 1) . "." . substr($DATA['profil']['CPM_NPWPD'], 2, 4) . "." . substr($DATA['profil']['CPM_NPWPD'], 6, 3) . "." . substr($DATA['profil']['CPM_NPWPD'], 9, 3) . "-" . substr($DATA['profil']['CPM_NPWPD'], 12, 3) . "." . substr($DATA['profil']['CPM_NPWPD'], 15, 2) . ".00." . substr($DATA['profil']['CPM_NPWPD'], 17, 3);

		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
					<tr>
						<td colspan=\"2\"><table width=\"710\" border=\"1\" cellpadding=\"3\">
								<tr>
									<td width=\"460\" valign=\"top\" align=\"center\">                                   
										<b style=\"font-size:40px\">" . $pemerintah_label . " " . $pemerintah_jenis . ' ' . strtoupper($NAMA_PEMERINTAHAN) . "</b><br>
										<span style=\"font-size:28px\">{$NAMA_PENGELOLA} </span><br>
										<span style=\"font-size:27px\">{$JALAN} </span><br>
										<span style=\"font-size:30px\"> " . ucwords($NAMA_PEMERINTAHAN) . " </span>
										<br>
									</td>
									<td width=\"250\" valign=\"top\" align=\"center\">                                   
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
									<td width=\"400\" align=\"center\">  
										<table width=\"380\" border=\"0\" cellpadding=\"0\" align=\"center\">
										<td align=\"center\">
											<b style=\"font-size:60px\">SPTPD</b><br/>
											<span style=\"font-size:35px\">SURAT PEMBERITAHUAN TAGIHAN PAJAK DAERAH</span>
										</td>
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td align=\"center\"><br><br>
												<b style=\"font-size:50px\">PAJAK REKLAME</b>
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
								<td><table width=\"130%\" border=\"0\" align=\"left\">
										<tr>
											<td>PERHATIAN : </td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Setelah Formulir SPTD ini ditandatangani, wajib diserahkan <br>  &nbsp;&nbsp;&nbsp; kembali Badan Pendapatan Daerah <br> &nbsp;&nbsp;&nbsp;&nbsp; Kota Bandar Lampung</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Keterlambatan penyerahan dari tanggal tersebut diatas akan <br> &nbsp;&nbsp;&nbsp;&nbsp; dilakukan penetapan Secara Jabatan.</td>
										</tr>
									</table>
									</td>
									<td width=\"170\"><table width=\"410\" class=\"header\" border=\"0\">
										
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td>
												<span>Kepada Yth.</span> {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}<br/>
												{$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']}, Kec. {$CPM_KECAMATAN_OP}, kel. {$DATA['pajak_atr']['0']['CPM_KELURAHAN_OP']}<br/>
												NPWPD:  " . $npwdR . "
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
							<b>INFORMASI UMUM OBJEK PAJAK</b>
						</td>
					</tr>
					<tr style=\"font-size:32px\">
                        <td width=\"710\" colspan=\"2\" align=\"center\">
							<table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"0\" cellspacing=\"0\">
								<tr>
                                    <td width=\"30\"></td>
                                    <td align=\"right\" width=\"390\"></td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\"></td>
                                    <td align=\"left\" width=\"270\" colspan=\"3\">
										<table width=\"680\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
										  <tr>
											<td width=\"5%\" align=\"center\"><strong>NO</strong></td>
											<td width=\"30%\" align=\"center\"><strong>Jenis, Judul dan Lokasi Reklame</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Durasi</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Panjang</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Lebar</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Tinggi</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Jumlah</strong></td>
											<td width=\"12%\" align=\"center\"><strong>Tgl Mulai</strong></td>
											<td width=\"12%\" align=\"center\"><strong>Tgl Selesai</strong></td>
										  </tr>";
		//   echo '<pre>';
		//   print_r($DATA['pajak_atr']);exit;
		foreach ($DATA['pajak_atr'] as $no => $atr) {
			// var_dump($atr['CPM_ATR_TINGGI']);die;
			// if($no==0) continue;
			$atr['CPM_ATR_TINGGI'] = str_replace('<', '&lt;', $atr['CPM_ATR_TINGGI']);
			$atr['CPM_ATR_TINGGI'] = str_replace('>', '&gt;', $atr['CPM_ATR_TINGGI']);
			$no = ($no + 1);
			$page1 .= "<tr>
															<td align=\"right\">{$no}.</td>
															<td align=\"left\">
																{$atr['nmrek']}<br/>\n
																" . ($atr['CPM_NOP']) . '&nbsp;' . strtoupper($atr['CPM_ATR_JUDUL']) . "<br/>LOKASI : {$atr['CPM_ATR_LOKASI']} \n
															</td>
															<td>
															{$atr['CPM_ATR_JAM']} 
															</td>
															<td>
															{$atr['CPM_ATR_PANJANG']} 
															</td>
															<td>
															{$atr['CPM_ATR_LEBAR']} 
															</td>
															<td>
															{$atr['CPM_ATR_TINGGI']} 
															</td>
															<td>" . number_format($atr['CPM_ATR_JUMLAH'], 0) . "</td>
															<td>
															{$atr['CPM_ATR_BATAS_AWAL']}
															</td>
															<td>
															{$atr['CPM_ATR_BATAS_AKHIR']}
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
							<b>PERNYATAAN</b>
						</td>
					</tr>
					<tr style=\"font-size:30px\">
						<td width=\"880\" colspan=\"8\" align=\"center\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"14\">
								<tr>
									<td>
										<table width=\"100%\" border=\"0\" align=\"left\">
											<tr>
												<td>Dengan menyadari sepenuhnya akan segala akibat termasuk sanksi-sanksi sesuai dengan ketentuan perundang-undangan yang berlaku, saya atau yang saya beri kuasa menyatakan bahwa apa yang telah kami beritahukan tersebut diatas berserta lampiran-lampirannya adalah benar, lengkap dan jelas.</td>
											</tr>
										</table>
									</td>
									<td>
										<table width=\"200\" border=\"0\" align=\"left\">
											<tr>
												<td align=\"center\">{$KOTA}, {$tgl_pengesahans}<br><br><br><br>{$DATA['profil']['CPM_NAMA_WP']}</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=\"100%\" colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>DIISI OLEH PETUGAS PENDATA</b>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
							
									<td><table width=\"700\" cellpadding=\"0\" border-right=\"0\" cellspacing=\"0\"><br><br>
											<tr align=\"center\">
												<td width=\"300\">Tata Cara perhitungan dan penerapan : </td>
											</tr>
											<tr align=\"center\">
												<td width=\"300\"><i>Official Assesment(Dihitung dan ditetapkan oleh Pejabat BAPENDA)</i></td>
											</tr>
										</table>
									</td>
									<td><table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
											<tr>
												<td width=\"150\">Diterima Tanggal</td>
												<td width=\"260\" colspan=\"2\">: {$tanggal_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Petugas</td>
												<td width=\"260\" colspan=\"2\">:  {$petugas_verifikasi}</td>
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
												<td width=\"195\">:</td>
												<td width=\"345\" align=\"center\">
													<u>{$petugas_verifikasi}</u>
												</td>
											</tr>
										</table>
									</td>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
									<td><table width=\"700\" cellpadding=\"0\" border-right=\"0\" cellspacing=\"0\" ><br><br>
									<tr align=\"left\">
										&nbsp;&nbsp;&nbsp;&nbsp;<td width=\"80\">NPWPD</td>
										<td width=\"260\" colspan=\"2\">: {$npwdR}</td>
									</tr>
									<tr align=\"left\">
										<td width=\"80\">NAMA</td>
										<td width=\"260\" colspan=\"2\">:  {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}</td>
									</tr>
									<tr align=\"left\">
										<td width=\"80\">ALAMAT</td>
										<td width=\"260\" colspan=\"2\">:  {$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']}</td>
									</tr>
										</table>
									</td>
									<td>
									<table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">     
											<tr>
												<td width=\"150\">No. SPTPD</td>
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td width=\"100\">Bandar Lampung, </td>
												<td align=\"center\" width=\"200\" colspan=\"2\"><br><br><small>Yang Menerima<br><br><br>(Nama Jelas & Tanda Tangan)</small></td>
											</tr>
										</table>
									</td>
							</table>
						</td>
					</tr>
					
					<span style=\"font-size:24px\"><i>BAPENDA BALAM {$tgl_cetak}</i></span>

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
		//echo $page1;exit;
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 7, 9, 14, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($page1, true, false, false, false, '');

		$pdf->Output('sptpd-reklame.pdf', 'I');
	}

	public function print_sptpd_pelayanan($a = '')
	{
		// var_dump($a);
		// die;

		$getID = $this->getIdDoc($a, 'reklame');
		// var_dump($getID['0']);die;
		global $sRootPath;
		$this->_id = $getID['0']->CPM_ID;
		$DATA = $this->get_pajak($getID['0']->CPM_NPWPD, $getID['0']->CPM_NOP);
		$data = $DATA['pajak'];
		$profil = $DATA['profil'];
		$pajak_atr = $DATA['pajak_atr'];

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
		$persen_terlambat_lap = $config_terlambat_lap ? $config_terlambat_lap->persen : 0;
		$editable_terlambat_lap = $config_terlambat_lap ? $config_terlambat_lap->editable : 0;
		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbTable = $config['PATDA_TABLE'];
		$dbUser = $config['PATDA_USERNAME'];
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		$query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($gw = mysqli_fetch_object($res)) {
			$DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
			// $DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
		}

		$upt_code = count($DATA['pajak_atr']) > 0 ? $DATA['pajak_atr'][0]['CPM_KECAMATAN_OP'] : false;
		$q = sprintf("SELECT CPM_KECAMATAN FROM patda_mst_kecamatan WHERE CPM_KEC_ID ='$upt_code'");
		$result = mysqli_query($Conn_gw, $q);
		$CPM_KECAMATAN_OP = '-';
		if ($upt = mysqli_fetch_object($result)) {
			$CPM_KECAMATAN_OP = $upt->CPM_KECAMATAN;
		}



		// echo '<pre>';
		// print_r($DATA);


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

		// if ($gw->payment_flag == 0 && ($DATA['pajak']['CPM_MASA_PAJAK2'] != '' || $DATA['pajak']['CPM_MASA_PAJAK2'] != NULL)) {
		// 	// Denda otomatis
		// 	$total_pajak = $gw->simpatda_dibayar;
		// 	$total_pajak = str_replace(',', '', $total_pajak);
		// 	$tgl_jatuh_tempo = $DATA['pajak']['CPM_MASA_PAJAK2'];
		// 	$tgl_jatuh_tempo_obj = date_create_from_format("d/m/Y", $tgl_jatuh_tempo);
		// 	$tgl_sekarang = date("Y-m-d");
		// 	// var_dump($total_pajak);exit;
		// 	// var_dump($tgl_sekarang);exit;
		// 	// Menghitung selisih bulan dan tahun antara tanggal sekarang dan tanggal jatuh tempo
		// 	$selisih_bulan = 0;
		// 	$selisih_tahun = 0;
		// 	$tgl_sekarang_obj = date_create_from_format("Y-m-d", $tgl_sekarang);
		// 	if ($tgl_jatuh_tempo_obj < $tgl_sekarang_obj) {
		// 		$selisih = date_diff($tgl_jatuh_tempo_obj, $tgl_sekarang_obj);
		// 		$selisih_bulan = $selisih->y * 12 + $selisih->m;
		// 	}
		// 	// var_dump($tgl_jatuh_tempo_obj < $tgl_sekarang_obj);die;
		// 	// Menentukan jumlah denda per bulan dan jumlah maksimal denda
		// 	$denda_per_bulan = 0.02; // 2% per bulan
		// 	$denda_max = 0.48; // 24% maksimal denda

		// 	// Menerapkan denda
		// 	if ($selisih_bulan > 0) {
		// 		$Denda = 0;
		// 		$DENDA = $total_pajak * ($selisih_bulan * $denda_per_bulan);
		// 		// var_dump($total_pajak,$selisih_bulan,$denda_per_bulan);exit;
		// 		// Memastikan bahwa denda tidak melebihi jumlah maksimal yang ditetapkan
		// 		if ($DENDA > ($total_pajak * $denda_max)) {
		// 			$DENDA = $total_pajak * $denda_max;
		// 		}

		// 		$total_pajak += $DENDA;

		// 		// Code Pembulatan
		// 		if (($DENDA * 100) % 100 < 50) {
		// 			$DENDA = round($DENDA, 0, PHP_ROUND_HALF_DOWN);
		// 		} else {
		// 			$DENDA = round($DENDA, 0, PHP_ROUND_HALF_UP);
		// 		}
		// 		// End code Pembulatan

		// 		$tgl_kena_denda = $tgl_jatuh_tempo_obj->add(new DateInterval('P' . $selisih_bulan . 'M'))->format('d-m-Y');
		// 		// var_dump ($tgl_jatuh_tempo,$tgl_kena_denda);die;
		// 	} else {
		// 		$DENDA = 0;
		// 	}
		// 	$total = $this->CPM_TOTAL_OMZET + $DENDA;
		// } else {
		// 	$DENDA = $gw->patda_denda;
		// 	$total = $this->CPM_TOTAL_OMZET;
		// }

		// var_dump($DATA['pajak_atr']);die;
		$npwdR = "P" . substr($DATA['profil']['CPM_NPWPD'], 1, 1) . "." . substr($DATA['profil']['CPM_NPWPD'], 2, 4) . "." . substr($DATA['profil']['CPM_NPWPD'], 6, 3) . "." . substr($DATA['profil']['CPM_NPWPD'], 9, 3) . "-" . substr($DATA['profil']['CPM_NPWPD'], 12, 3) . "." . substr($DATA['profil']['CPM_NPWPD'], 15, 2) . ".00." . substr($DATA['profil']['CPM_NPWPD'], 17, 3);

		$page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
					<tr>
						<td colspan=\"2\"><table width=\"710\" border=\"1\" cellpadding=\"3\">
								<tr>
									<td width=\"460\" valign=\"top\" align=\"center\">                                   
										<b style=\"font-size:40px\">" . $pemerintah_label . " " . $pemerintah_jenis . ' ' . strtoupper($NAMA_PEMERINTAHAN) . "</b><br>
										<span style=\"font-size:28px\">{$NAMA_PENGELOLA} </span><br>
										<span style=\"font-size:27px\">{$JALAN} </span><br>
										<span style=\"font-size:30px\"> " . ucwords($NAMA_PEMERINTAHAN) . " </span>
										<br>
									</td>
									<td width=\"250\" valign=\"top\" align=\"center\">                                   
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
									<td width=\"400\" align=\"center\">  
										<table width=\"380\" border=\"0\" cellpadding=\"0\" align=\"center\">
										<td align=\"center\">
											<b style=\"font-size:60px\">SPTPD</b><br/>
											<span style=\"font-size:35px\">SURAT PEMBERITAHUAN TAGIHAN PAJAK DAERAH</span>
										</td>
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td align=\"center\"><br><br>
												<b style=\"font-size:50px\">PAJAK REKLAME</b>
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
								<td><table width=\"130%\" border=\"0\" align=\"left\">
										<tr>
											<td>PERHATIAN : </td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Setelah Formulir SPTD ini ditandatangani, wajib diserahkan <br>  &nbsp;&nbsp;&nbsp; kembali Badan Pendapatan Daerah <br> &nbsp;&nbsp;&nbsp;&nbsp; Kota Bandar Lampung</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Keterlambatan penyerahan dari tanggal tersebut diatas akan <br> &nbsp;&nbsp;&nbsp;&nbsp; dilakukan penetapan Secara Jabatan.</td>
										</tr>
									</table>
									</td>
									<td width=\"170\"><table width=\"410\" class=\"header\" border=\"0\">
										
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td>
												<span>Kepada Yth.</span> {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}<br/>
												{$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']}, Kec. {$CPM_KECAMATAN_OP}, kel. {$DATA['pajak_atr']['0']['CPM_KELURAHAN_OP']}<br/>
												NPWPD:  " . $npwdR . "
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
							<b>INFORMASI UMUM OBJEK PAJAK</b>
						</td>
					</tr>
					<tr style=\"font-size:32px\">
                        <td width=\"710\" colspan=\"2\" align=\"center\">
							<table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"0\" cellspacing=\"0\">
								<tr>
                                    <td width=\"30\"></td>
                                    <td align=\"right\" width=\"390\"></td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\"></td>
                                    <td align=\"left\" width=\"270\" colspan=\"3\">
										<table width=\"680\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
										  <tr>
											<td width=\"5%\" align=\"center\"><strong>NO</strong></td>
											<td width=\"30%\" align=\"center\"><strong>Jenis, Judul dan Lokasi Reklame</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Durasi</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Panjang</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Lebar</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Tinggi</strong></td>
											<td width=\"8%\" align=\"center\"><strong>Jumlah</strong></td>
											<td width=\"12%\" align=\"center\"><strong>Tgl Mulai</strong></td>
											<td width=\"12%\" align=\"center\"><strong>Tgl Selesai</strong></td>
										  </tr>";
		//   echo '<pre>';
		//   print_r($DATA['pajak_atr']);exit;
		foreach ($DATA['pajak_atr'] as $no => $atr) {
			// var_dump($atr['CPM_ATR_TINGGI']);die;
			// if($no==0) continue;
			$atr['CPM_ATR_TINGGI'] = str_replace('<', '&lt;', $atr['CPM_ATR_TINGGI']);
			$atr['CPM_ATR_TINGGI'] = str_replace('>', '&gt;', $atr['CPM_ATR_TINGGI']);
			$no = ($no + 1);
			$page1 .= "<tr>
															<td align=\"right\">{$no}.</td>
															<td align=\"left\">
																{$atr['nmrek']}<br/>\n
																" . ($atr['CPM_NOP']) . '&nbsp;' . strtoupper($atr['CPM_ATR_JUDUL']) . "<br/>LOKASI : {$atr['CPM_ATR_LOKASI']} \n
															</td>
															<td>
															{$atr['CPM_ATR_JAM']} 
															</td>
															<td>
															{$atr['CPM_ATR_PANJANG']} 
															</td>
															<td>
															{$atr['CPM_ATR_LEBAR']} 
															</td>
															<td>
															{$atr['CPM_ATR_TINGGI']} 
															</td>
															<td>" . number_format($atr['CPM_ATR_JUMLAH'], 0) . "</td>
															<td>
															{$atr['CPM_ATR_BATAS_AWAL']}
															</td>
															<td>
															{$atr['CPM_ATR_BATAS_AKHIR']}
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
							<b>PERNYATAAN</b>
						</td>
					</tr>
					<tr style=\"font-size:30px\">
						<td width=\"880\" colspan=\"8\" align=\"center\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"14\">
								<tr>
									<td>
										<table width=\"100%\" border=\"0\" align=\"left\">
											<tr>
												<td>Dengan menyadari sepenuhnya akan segala akibat termasuk sanksi-sanksi sesuai dengan ketentuan perundang-undangan yang berlaku, saya atau yang saya beri kuasa menyatakan bahwa apa yang telah kami beritahukan tersebut diatas berserta lampiran-lampirannya adalah benar, lengkap dan jelas.</td>
											</tr>
										</table>
									</td>
									<td>
										<table width=\"200\" border=\"0\" align=\"left\">
											<tr>
												<td align=\"center\">{$KOTA}, {$tgl_pengesahans}<br><br><br><br>{$DATA['profil']['CPM_NAMA_WP']}</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=\"100%\" colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>DIISI OLEH PETUGAS PENDATA</b>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
							
									<td><table width=\"700\" cellpadding=\"0\" border-right=\"0\" cellspacing=\"0\"><br><br>
											<tr align=\"center\">
												<td width=\"300\">Tata Cara perhitungan dan penerapan : </td>
											</tr>
											<tr align=\"center\">
												<td width=\"300\"><i>Official Assesment(Dihitung dan ditetapkan oleh Pejabat BAPENDA)</i></td>
											</tr>
										</table>
									</td>
									<td><table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
											<tr>
												<td width=\"150\">Diterima Tanggal</td>
												<td width=\"260\" colspan=\"2\">: {$tanggal_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Petugas</td>
												<td width=\"260\" colspan=\"2\">:  {$petugas_verifikasi}</td>
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
												<td width=\"195\">:</td>
												<td width=\"345\" align=\"center\">
													<u>{$petugas_verifikasi}</u>
												</td>
											</tr>
										</table>
									</td>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
									<td><table width=\"700\" cellpadding=\"0\" border-right=\"0\" cellspacing=\"0\" ><br><br>
									<tr align=\"left\">
										&nbsp;&nbsp;&nbsp;&nbsp;<td width=\"80\">NPWPD</td>
										<td width=\"260\" colspan=\"2\">: {$npwdR}</td>
									</tr>
									<tr align=\"left\">
										<td width=\"80\">NAMA</td>
										<td width=\"260\" colspan=\"2\">:  {$DATA['pajak_atr']['0']['CPM_NAMA_OP']}</td>
									</tr>
									<tr align=\"left\">
										<td width=\"80\">ALAMAT</td>
										<td width=\"260\" colspan=\"2\">:  {$DATA['pajak_atr']['0']['CPM_ALAMAT_OP']}</td>
									</tr>
										</table>
									</td>
									<td>
									<table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">     
											<tr>
												<td width=\"150\">No. SPTPD</td>
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td width=\"100\">Bandar Lampung, </td>
												<td align=\"center\" width=\"200\" colspan=\"2\"><br><br><small>Yang Menerima<br><br><br>(Nama Jelas & Tanda Tangan)</small></td>
											</tr>
										</table>
									</td>
							</table>
						</td>
					</tr>
					
					<span style=\"font-size:24px\"><i>BAPENDA BALAM {$tgl_cetak}</i></span>

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
		//echo $page1;exit;
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 7, 9, 14, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($page1, true, false, false, false, '');

		$pdf->Output('sptpd-reklame.pdf', 'I');
	}

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

		// Add QRIS By d3Di ================================================
		$id_switching = $gw->id_switching;
		$datetimenow = date('Y-m-d H:i:s');
		$query4 = "SELECT qr FROM simpatda_qris WHERE id_switching='$id_switching' AND expired_date_time>='$datetimenow' ORDER BY id DESC LIMIT 0, 1";
		$r = mysqli_query($Conn_gw, $query4);
		$nx = mysqli_num_rows($r);

		$QRCodeSVG = false;
		if ($nx > 0 && $gw->payment_flag == 0) {
			$r = mysqli_fetch_array($r);
			$r['qr'] = (strlen($r['qr']) >= 50) ? $r['qr'] : rand(1, 9);
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
		// var_dump( $QRCodeSVG);exit;

		$DATA['profil']['CPM_NAMA_OP'] = isset($gw->op_nama) ? $gw->op_nama : $DATA['profil']['CPM_NAMA_OP'];
		$DATA['profil']['CPM_ALAMAT_OP'] = isset($gw->op_alamat) ? $gw->op_alamat : $DATA['profil']['CPM_ALAMAT_OP'];

		$PAYMENT_CODE_BANK = $gw->periode;
		$PAYMENT_CODE = $gw->payment_code;
		// $DENDA = !empty($gw->patda_denda) ? $gw->patda_denda : 0;


		// Tanggal jatuh tempo
		$total_pajak = $DATA['pajak']['CPM_TOTAL_OMZET'];
		$tgl_jatuh_tempo = $DATA['pajak']['CPM_TGL_JATUH_TEMPO'];

		$tgl_jatuh_tempo_obj = date_create_from_format("d-m-Y", $tgl_jatuh_tempo);
		$tgl_jatuh_tempo = $tgl_jatuh_tempo_obj->format("Y-m-d");
		$tgl_sekarang = date("Y-m-d");

		// Menghitung selisih hari antara tanggal sekarang dan tanggal jatuh tempo
		$selisih_hari = strtotime($tgl_sekarang) - strtotime($tgl_jatuh_tempo);
		$selisih_hari = $selisih_hari / (60 * 60 * 24);

		// Menghitung jumlah bulan keterlambatan pembayaran pajak
		$jumlah_bulan = ceil($selisih_hari / 30);

		// var_dump($DATA['pajak']['CPM_TOTAL_OMZET']);die;
		$total_pajak =  $DATA['pajak']['CPM_TOTAL_OMZET'];

		// if ($DATA['pajak']['CPM_PERPANJANGAN'] == 1) {
		// 	$tgl_jatuh_tempo = $DATA['pajak']['CPM_MASA_PAJAK1'];
		// 	$datetime = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
		// 	// Mengubah format tanggal
		// 	$tanggal_baru = $datetime->format('Y-m-d');
		// 	$persen_denda = $this->get_persen_denda($tanggal_baru);
		// 	$DENDA = ($persen_denda / 100) * $total_pajak;
		// 	// Pembulatan nilai decimal
		// 	if ($DENDA - floor($DENDA) >= 0.5) {
		// 		$DENDA = ceil($DENDA);
		// 	} else {
		// 		$DENDA = floor($DENDA);
		// 	}
		// 	$TOTAL = $total_pajak + $DENDA;
		// } else {
		// 	$DENDA = 0;
		// 	$TOTAL = $total_pajak + $DENDA;
		// }


		if ($DATA['pajak']['CPM_PERPANJANGAN'] == 1) {
			$tgl_jatuh_tempo = $DATA['pajak']['CPM_MASA_PAJAK1'];
			$datetime = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
			$tanggal_baru = $datetime->format('Y-m-d');
			$persen_denda = $this->get_persen_denda($tanggal_baru);
			$DENDA = ($persen_denda / 100) * $total_pajak;
			$TOTAL = $total_pajak + $DENDA;
		} else {
			if ($DATA['pajak']['CPM_START_DENDA'] == 1) {
				if ($gw->payment_flag == 0 && ($DATA['pajak']['CPM_MASA_PAJAK1'] != '' || $DATA['pajak']['CPM_MASA_PAJAK1'] != NULL)) {
					// Denda otomatis
					$total_pajak = $DATA['pajak']['CPM_TOTAL_OMZET'];
					$tgl_jatuh_tempo = $DATA['pajak']['CPM_MASA_PAJAK1'];
					$tgl_jatuh_tempo_obj = date_create_from_format("d/m/Y", $tgl_jatuh_tempo);
					$tgl_sekarang = date("Y-m-d");
					// Menghitung selisih bulan dan tahun antara tanggal sekarang dan tanggal jatuh tempo
					$selisih_bulan = 0;
					$selisih_tahun = 0;
					$tgl_sekarang_obj = date_create_from_format("Y-m-d", $tgl_sekarang);
					if ($tgl_jatuh_tempo_obj < $tgl_sekarang_obj) {
						$selisih = date_diff($tgl_jatuh_tempo_obj, $tgl_sekarang_obj);
						$selisih_bulan = $selisih->y * 12 + $selisih->m;
					}
					// var_dump($selisih_bulan);exit;
					// var_dump($selisih_bulan, $tgl_jatuh_tempo_obj , $tgl_sekarang);exit;
					// Menentukan jumlah denda per bulan dan jumlah maksimal denda
					$denda_per_bulan = 0.01; // 2% per bulan
					$denda_max = 0.24; // 24% maksimal denda

					// Menerapkan denda
					if ($selisih_bulan > 0) {
						$Denda = 0;
						$DENDA = $total_pajak * ($selisih_bulan * $denda_per_bulan);
						// var_dump($total_pajak,$selisih_bulan,$denda_per_bulan);exit;
						// Memastikan bahwa denda tidak melebihi jumlah maksimal yang ditetapkan
						if ($DENDA > ($total_pajak * $denda_max)) {
							$DENDA = $total_pajak * $denda_max;
						}

						$total_pajak += $DENDA;

						// Code Pembulatan
						if (($DENDA * 100) % 100 < 50) {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_DOWN);
						} else {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_UP);
						}
						// End code Pembulatan

						$tgl_kena_denda = $tgl_jatuh_tempo_obj->add(new DateInterval('P' . $selisih_bulan . 'M'))->format('d-m-Y');
					} else {
						$DENDA = 0;
					}
					$TOTAL = $DATA['pajak']['CPM_TOTAL_OMZET'] + $DENDA;
				} else {
					$DENDA = $gw->patda_denda;
					$TOTAL = $DATA['pajak']['CPM_TOTAL_OMZET'] + $DENDA;
				}
			} else {
				$DENDA = 0;
				$TOTAL = $DATA['pajak']['CPM_TOTAL_OMZET'];
			}
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
                                         <span style=\"margin:0px;!important;font-size:42px;font-weight:bold\">KODE BAYAR <br/>
                                    {$PAYMENT_CODE}<br/></span><br/>
                                    
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
												<td colspan=\"3\">: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
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
											</tr>";
		foreach ($DATA['pajak_atr'] as $no => $atr) {
			$no++;
			$html .= "<tr>
                                                <td align=\"center\">{$no}.</td>
                                                <td align=\"left\">
													{$atr['CPM_ATR_REKENING']}
                                                </td>
                                                <td>
													{$atr['nmrek']}<br/>\n
													Judul Reklame : {$atr['CPM_ATR_JUDUL']},<br/>\n
													Lokasi : {$atr['CPM_ATR_LOKASI']},<br/>\n
													Panjang : {$atr['CPM_ATR_PANJANG']} m,
													Lebar : {$atr['CPM_ATR_LEBAR']} m,<br/>
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
                                                <td align=\"right\" colspan=\"1\">" . number_format($total_pajak, 2) . "</td>
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
									Penyetor<br/>
									{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
									<br/><br/><br/><br/><br/>
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
					<span style=\"font-size:24px\"><i>BAPENDA BANDAR LAMPUNG {$tgl_cetak}</i></span>

                </table>";

		require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('vpost');
		$pdf->SetTitle('9 PAJAK ONLINE');
		$pdf->SetSubject('spppd');
		$pdf->SetKeywords('9 PAJAK ONLINE');
		$pdf->setPrintHeader(false);
		font:
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 14, 5);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

		$pdf->AddPage('P', 'F4');

		// QRIS   ==============
		if ($QRCodeSVG) {
			$pdf->ImageSVG('@' . $QRCodeSVG, $x = 170, $y = 122, $w = 32, $h = 32, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
			$pdf->ImageSVG('@' . $icoQRIS, $x = 181, $y = 115, $w = 10, $h = 10, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
		}
		// ======================

		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 15, 17, 20, '', '', '', '', false, 300, '', false);
		$pdf->writeHTML($html, true, false, false, false, '');
		$pdf->SetAlpha(0.3);

		$pdf->Output('sspd-reklame.pdf', 'I');
	}


	public function print_nota_hitung()
	{
		global $sRootPath;
		$this->_id = $this->CPM_ID;
		$DATA = $this->get_pajak();

		$pajak_atr = $DATA['pajak_atr'];
		// echo '<pre>';
		// print_r($DATA);
		// exit;
		$DENDA = 0;
		$total_omzet = $DATA["pajak"]['CPM_TOTAL_OMZET'];

		$subtotal = $DATA['pajak_atr'][0]['CPM_ATR_HARGA'] * ($DATA['pajak_atr'][0]['CPM_ATR_TARIF'] / 100) * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
		$hargaDasar = $DATA['pajak_atr'][0]['CPM_ATR_HARGA'] * ($DATA['pajak_atr'][0]['CPM_ATR_TARIF'] / 100);

		// * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
		$subtotal2 =  $total_omzet - ($total_omzet * 0.5);

		$total_pajak2 = $subtotal2 - $subtotal;
		if ($DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
			$subtotal = $hargaDasar * $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] * $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] * $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
			// var_dump($subtotal);
			// die;
			$alkoholTotal = $subtotal;
			// $kenaikan = $alkoholTotal/2;
			// $kenaikan = $DATA['pajak']['CPM_TOTAL_OMZET'] - $alkoholTotal;
			// $kenaikan = $subtotal * 50 / 100;


			$totalKetetapan = $DATA['pajak']['CPM_TOTAL_OMZET'] - $alkoholTotal;

			$totalKeseluruhan =  $totalKetetapan + $DENDA + $alkoholTotal;
			// var_dump($subtotal);
			// die;
			// var_dump($totalKeseluruhan, $totalKetetapan, $alkoholTotal);
			// // var_dump();
			// die;
		} else {
			// $alkoholTotal = 0;
			$totalKetetapan = $DATA['pajak']['CPM_TOTAL_OMZET'];
			$totalKeseluruhan =  $DATA['pajak']['CPM_TOTAL_OMZET'] + $DENDA + $alkoholTotal;
		}
		// var_dump($subtotal);
		// die;

		$CPM_NAMA_OP = count($DATA['pajak_atr']) > 0 ? $DATA['pajak_atr'][0]['CPM_NAMA_OP'] : '-';
		$op_x = array('CPM_NAMA_OP' => $CPM_NAMA_OP);
		$DATA = (object) array_merge($DATA['pajak'], $DATA['profil'], $op_x); //, $DATA['pajak_atr'][0]);
		// echo'<pre>';print_r($DATA);exit;
		$config = $this->get_config_value($this->_a);
		$flag = '';
		$patda_denda = '';
		$dbName = $config['PATDA_DBNAME'];
		$dbHost = $config['PATDA_HOSTPORT'];
		$dbPwd = $config['PATDA_PASSWORD'];
		$dbTable = $config['PATDA_TABLE'];
		$dbUser = $config['PATDA_USERNAME'];
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		mysqli_select_db($dbName, $Conn_gw);
		$query = sprintf("select * from SIMPATDA_GW WHERE id_switching = '%s'", $this->CPM_ID);
		$res = mysqli_query($Conn_gw, $query);
		if ($gw = mysqli_fetch_assoc($res)) {
			// var_dump($gw['payment_flag']);exit;
			$flag = $gw['payment_flag'];
			$patda_denda = $gw['patda_denda'];
			// $DATA['CPM_TGL_JATUH_TEMPO'] = $gw->expired_date;
		}


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

		$KABID_PENETAPAN_JABATAN = $config['KABID_PENETAPAN_JABATAN'];
		$KABID_PENDATAAN_JABATAN = $config['KABID_PENDATAAN_JABATAN'];

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

		// $total_omzet = $DATA->CPM_TOTAL_OMZET;

		// $subtotal = $DATA->CPM_ATR_HARGA * ($DATA->CPM_ATR_TARIF / 100) * $DATA->CPM_ATR_JUMLAH_HARI;
		// $hargaDasar = $DATA->CPM_ATR_HARGA * ($DATA->CPM_ATR_TARIF / 100);

		// // * $DATA->CPM_ATR_JUMLAH_HARI;
		// $subtotal2 =  $total_omzet - ($total_omzet * 0.5);

		// $total_pajak2 = $subtotal2 - $subtotal;
		// if ($pajak_atr[0]['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
		// 	$subtotal = $hargaDasar * $DATA->CPM_ATR_PANJANG * $DATA->CPM_ATR_LEBAR * $DATA->CPM_ATR_JUMLAH * $DATA->CPM_ATR_JUMLAH_HARI;
		// 	var_dump($subtotal);
		// 	die;
		// 	$alkoholTotal = $subtotal;
		// 	$kenaikan = $DATA->CPM_TOTAL_OMZET - $alkoholTotal;


		// 	$totalKetetapan = $DATA->CPM_TOTAL_OMZET - $alkoholTotal;

		// 	$totalKeseluruhan =  $totalKetetapan + $DENDA + $alkoholTotal;
		// 	// var_dump($totalKeseluruhan, $totalKetetapan, $alkoholTotal);
		// 	// exit();
		// } else {
		// 	$alkoholTotal = 0;
		// 	$totalKetetapan = $DATA->CPM_TOTAL_OMZET;
		// 	$totalKeseluruhan =  $DATA->CPM_TOTAL_OMZET + $DENDA + $alkoholTotal;
		// }
		// var_dump($totalKetetapan);
		// 	exit();

		// var_dupm($pajak_atr);
		// die;
		// echo '<pre>';
		// print_r($DATA->CPM_MASA_PAJAK1);
		// echo '</pre>';
		$total_pajak =  $DATA->CPM_TOTAL_OMZET;
		// var_dump($DATA->CPM_MASA_PAJAK1);
		// die;
		// var_dump($DATA->CPM_START_DENDA);
		if ($DATA->CPM_PERPANJANGAN == 1) {
			$tgl_jatuh_tempo = $DATA->CPM_MASA_PAJAK1;
			$datetime = DateTime::createFromFormat('d/m/Y', $tgl_jatuh_tempo);
			$tanggal_baru = $datetime->format('Y-m-d');
			$persen_denda = $this->get_persen_denda($tanggal_baru);
			$DENDA = ($persen_denda / 100) * $total_pajak;
			$TOTAL = $total_pajak + $DENDA;
		} else {

			if ($DATA->CPM_START_DENDA == 1) {
				if ($gw->payment_flag == 0 && ($DATA->CPM_MASA_PAJAK1 != '' || $DATA->CPM_MASA_PAJAK1 != NULL)) {
					// Denda otomatis
					$total_pajak = $DATA->CPM_TOTAL_OMZET;
					$tgl_jatuh_tempo = $DATA->CPM_MASA_PAJAK1;
					$tgl_jatuh_tempo_obj = date_create_from_format("d/m/Y", $tgl_jatuh_tempo);
					$tgl_sekarang = date("Y-m-d");
					// var_dump($tgl_sekarang);exit;
					// Menghitung selisih bulan dan tahun antara tanggal sekarang dan tanggal jatuh tempo
					$selisih_bulan = 0;
					$selisih_tahun = 0;
					$tgl_sekarang_obj = date_create_from_format("Y-m-d", $tgl_sekarang);
					if ($tgl_jatuh_tempo_obj < $tgl_sekarang_obj) {
						$selisih = date_diff($tgl_jatuh_tempo_obj, $tgl_sekarang_obj);
						$selisih_bulan = $selisih->y * 12 + $selisih->m;
					}
					// var_dump($selisih_bulan, $tgl_jatuh_tempo_obj , $tgl_sekarang);exit;
					// Menentukan jumlah denda per bulan dan jumlah maksimal denda
					$denda_per_bulan = 0.01; // 2% per bulan
					$denda_max = 0.24; // 24% maksimal denda

					// Menerapkan denda
					if ($selisih_bulan > 0) {
						$Denda = 0;
						$DENDA = $total_pajak * ($selisih_bulan * $denda_per_bulan);
						// var_dump($total_pajak,$selisih_bulan,$denda_per_bulan);exit;
						// Memastikan bahwa denda tidak melebihi jumlah maksimal yang ditetapkan
						if ($DENDA > ($total_pajak * $denda_max)) {
							$DENDA = $total_pajak * $denda_max;
						}

						$total_pajak += $DENDA;

						// Code Pembulatan
						if (($DENDA * 100) % 100 < 50) {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_DOWN);
						} else {
							$DENDA = round($DENDA, 0, PHP_ROUND_HALF_UP);
						}
						// End code Pembulatan

						$tgl_kena_denda = $tgl_jatuh_tempo_obj->add(new DateInterval('P' . $selisih_bulan . 'M'))->format('d-m-Y');
					} else {
						$DENDA = 0;
					}
					$total = $DATA->CPM_TOTAL_OMZET + $DENDA;
				} else {
					$DENDA = $gw->patda_denda;
					$total = $DATA->CPM_TOTAL_OMZET;
				}
			} else {
				$DENDA = 0;
				$total = $DATA->CPM_TOTAL_OMZET;
			}
		}



		if ($flag == 1) {
			$DENDA = $gw['patda_denda'];
		}


		$html = "<table width=\"1015\" class=\"main\" border=\"1\">
					<tr>
						<td><table width=\"1015\" border=\"1\" cellpadding=\"10\">
								<tr>
									<th valign=\"top\" width=\"370\" align=\"center\">
										<table cellpadding=\"0\" border=\"0\"><tr><td width=\"100\"></td>
										<td width=\"250\"><b><font>" . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
										" . strtoupper($NAMA_PENGELOLA) . "</font></b><br /><br />
										<font class=\"normal\">{$JALAN}</font></td>
										</tr></table>
									</th>
									<th width=\"330\" align=\"center\">
										<br><b>NOTA PERHITUNGAN PAJAK<br/>
										Tahun : {$DATA->CPM_TAHUN_PAJAK} <br><br><br><br> Masa Pajak :  {$DATA->CPM_MASA_PAJAK1} - {$DATA->CPM_MASA_PAJAK2}</br>
										
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
										<td width=\"70\">Alamat</td><td width=\"7\">:</td><td width=\"250\">{$DATA->CPM_ALAMAT_WP} - {$DATA->CPM_KELURAHAN_WP}<br>KEC. {$DATA->CPM_KECAMATAN_WP}<br>KOTA. " . strtoupper($DATA->CPM_KOTA_WP) . "</td>
									</tr></table></td><td><table cellpadding=\"1\" border=\"0\"><tr>
										<td width=\"80\">NPWPD</td><td width=\"180\">: " . Pajak::formatNPWPD($DATA->CPM_NPWPD) . "</td>
									</tr></table></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table width=\"1015\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:34px\">
								<tr>
									<td><table width=\"1015\" border=\"1\" cellpadding=\"3\">
											<tr>
												<th width=\"30\" align=\"center\"><br><br><b>NO.</b></th>
												<th width=\"100\"  align=\"center\"><br><br><b>JENIS PAJAK</b></th>
												<th width=\"" . ($pajak_atr[0]['CPM_ATR_ALKOHOL_ROKOK'] == 1 ? "100" : "120") . "\"  align=\"center\"><br><br><b>AYAT</b></th>
												<th width=\"" . ($pajak_atr[0]['CPM_ATR_ALKOHOL_ROKOK'] == 1 ? "390" : "370") . "\"  align=\"center\"><b>URAIAN</b></th>	
												<th width=\"90\"  align=\"center\"><b>KETETAPAN<br/>(Rp.)</b></th>";
		// if ($pajak_atr[0]['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
		$html .= "<th width=\"110\"  align=\"center\"><b>PENAMBAHAN/<br/>PENGURANGAN</b></th>";
		$html .= "<th width=\"80\"  align=\"center\"><b>DENDA</b></th>";
		// }
		$html .= "
												<th width=\"115\"  align=\"center\"><b>JUMLAH<br/>(Rp.)</b></th>
											</tr>
											";
		// echo '<pre>';
		// print_r($pajak_atr);
		// exit;

		$row_atr = '';
		$total_keseluruhan_ketetapan = 0;
		$total_keseluruhan_penambahan = 0;
		$total_keseluruhan_jumlah = $DATA->CPM_TOTAL_PAJAK;
		// $total_keseluruhan_jumlah = 0;
		foreach ($pajak_atr as $no => $atr) {
			// print_r($atr['CPM_ATR_BIAYA']);
			// die;
			// if ($atr['CPM_ATR_HARGA'] == 'LUAR'){
			// 	$luar_gedung = '35%'
			// }
			$biaya =  $atr['CPM_ATR_BIAYA'] / 100;
			$panajng_lebar =  $atr['CPM_ATR_LEBAR'] * $atr['CPM_ATR_PANJANG'];
			$jam = $atr['CPM_ATR_JAM'] * 60;
			if ($atr['CPM_ATR_REKENING'] == "4.1.01.09.01.005") {
				$tot_terpasang = round($atr['CPM_ATR_HARGA'] * $biaya);
				$total_ketetapan = $tot_terpasang * $panajng_lebar * $atr['CPM_ATR_JUMLAH_HARI'] * $atr['CPM_ATR_JUMLAH'];
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.01.004") {
				$tot_terpasang = $atr['CPM_ATR_HARGA'];
				$total_ketetapan = $tot_terpasang * $panajng_lebar *  intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $jam * $biaya;
				// $total_ketetapan = $tot_terpasang * $panajng_lebar * $jam  * $atr['CPM_ATR_JUMLAH'];
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.08") {
				$tot_terpasang = $atr['CPM_ATR_HARGA'];
				$total_ketetapan = $tot_terpasang * $panajng_lebar *  intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $jam * $biaya;
			} elseif ($atr['CPM_ATR_REKENING'] == "4.1.01.09.10") {
				$total_ketetapan_awal = $atr['CPM_ATR_HARGA'] * $panajng_lebar * intval($atr['CPM_ATR_JUMLAH_HARI']) * $atr['CPM_ATR_JUMLAH'] * $biaya;
				$total_ketetapan = $total_ketetapan_awal * 0.5;
			} else {
				$total_ketetapan = $atr['CPM_ATR_HARGA'] * $panajng_lebar * $atr['CPM_ATR_JUMLAH_HARI'] * $atr['CPM_ATR_JUMLAH'] * $biaya;
				// var_dump($panajng_lebar);die;
			}
			if ($atr['CPM_ATR_ALKOHOL_ROKOK'] == 1) {
				$penambahan = round(($total_ketetapan * 50) / 100);
				$total_seluruh = $total_ketetapan + $penambahan;
				$total_keseluruhan_penambahan += $penambahan; // tambahkan ini
			} elseif ($atr['CPM_ATR_GEDUNG'] == 'DALAM') {
				$hasil_pengurangan = round(($total_ketetapan * 35) / 100);
				$penambahan = $total_ketetapan - $hasil_pengurangan;
				$total_keseluruhan_penambahan += $penambahan;
				$total_seluruh = $total_ketetapan - $penambahan;
			} elseif ($atr['CPM_ATR_GEDUNG'] == 'LUAR') {
				$total_seluruh = $total_ketetapan;
			} else {
				$penambahan = 0;
			}
			$total_keseluruhan_ketetapan += $total_ketetapan;
			// $total_keseluruhan_penambahan += $penambahan;
			// $total_keseluruhan_jumlah += $total_seluruh;
			if ($DATA->CPM_DISCOUNT > 0) {
				$diskon =  round(($total_keseluruhan_jumlah * $DATA->CPM_DISCOUNT) / 100);
				$total_keseluruhan_jumlah = $total_keseluruhan_jumlah - $diskon;
			}

			$total_huruf = round($total_keseluruhan_jumlah) + round($DENDA);

			// var_dump($atr['CPM_ATR_HARGA'], $biaya, $tot_terpasang, $panajng_lebar, $atr['CPM_ATR_JUMLAH_HARI'], $atr['CPM_ATR_JUMLAH'], $jam);
			// die;

			// var_dump($gw['sspd']);die;

			if ($gw['sspd'] == '900002359/REK/23') {
				$total_ketetapan = $atr['CPM_ATR_TOTAL'];
				$total_seluruh = $atr['CPM_ATR_TOTAL'];
				$total_huruf = $gw['simpatda_dibayar'];
				$total_keseluruhan_ketetapan = $gw['simpatda_dibayar'];
				$total_keseluruhan_jumlah = $gw['simpatda_dibayar'];
			}


			$row_atr .= "<tr>
												<td align=\"center\">" . ($no + 1) . ".</td>
												<td align=\"center\">Pajak Reklame</td>
												<td align=\"center\">{$atr['CPM_ATR_REKENING']}</td>
												<td>Rp." . number_format($atr['CPM_ATR_HARGA'], 2) . " x {$DATA->CPM_MASA_PAJAK} {$DATA->CPM_JNS_MASA_PAJAK} x {$panajng_lebar} M x {$atr['CPM_ATR_JUMLAH']} Buah x  " . number_format($atr['CPM_ATR_BIAYA']) . '% ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == 1 ? "+" . number_format($penambahan) : "") . " " . ($atr['CPM_ATR_GEDUNG'] == 'DALAM' ? "- (" . number_format($penambahan) . ")" : "") . " " . ($atr['CPM_ATR_REKENING'] == '4.1.01.09.10' ? "- 50%" : "") . "</td>
												<td  align=\"right\">" . number_format($total_ketetapan) . "</td>
											
												<td align=\"right\">" . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == 1 ? "" . number_format($penambahan) : "") . " " . ($atr['CPM_ATR_GEDUNG'] == 'DALAM' ? " (" . number_format($penambahan) . ")" : "") . "</td>
												<td align=\"right\"> 0 </td>
												<td align=\"right\">" . (number_format($total_seluruh))  . "</td>
										
				</tr>";
		}
		$html .= $row_atr;

		$html .= "<tr>
		<td colspan=\"4\" align=\"right\" align=\"right\"> <b>TOTAL</b></td>
		<td  align=\"right\">" . number_format($total_keseluruhan_ketetapan) . "</td>
		<td align=\"right\">" . number_format($total_keseluruhan_penambahan) . "</td>
		<td align=\"right\">" . (number_format($DENDA)) . "</td>
		<td align=\"right\"><b>" . number_format(round($total_keseluruhan_jumlah) + round($DENDA)) . "</b></td>
</tr>";
		$html .= "						
										</table>

										<table width=\"1015\" border=\"0\" cellpadding=\"3\" style=\"margin:100px\">
											<tr>
												<td width=\"350\" align=\"right\" style=\"border:none\">Jumlah dengan huruf </td>
												<td width=\"700\">(" . ucwords($this->SayInIndonesian($total_huruf)) . " Rupiah)</td>
											</tr>
										</table>
								<br>
								<br>
								
										<table width=\"1015\" border=\"0\" cellpadding=\"3\" style=\"padding-top:100px\" >
											<tr>
												<td>
													<table width=\"299\" border=\"0\">
														<tr>
														  <td width=\"289\" align=\"center\">Mengetahui,
															<br/>KEPALA BIDANG PENDAFTARAN DAN PENETAPAN</td>
														</tr>
														<tr>
														  <td><p>&nbsp;</p>
															<p>&nbsp;</p></td>
														</tr>
														<br/>		
														<tr>
														  <td align=\"center\">
															<strong><u>ANDRE SETIAWAN, S.IP., M.Si</u></strong><br/>
															NIP. 198712232010011002</td>
														</tr>
													</table>
												</td>
												<td>
													<table width=\"299\" border=\"0\">
														<tr>
															<td width=\"289\" align=\"center\">
																Diperiksa oleh :<br/>KASUBID PENDAFTARAN DAN PENETAPAN<br>
															</td>
														</tr>
														<tr>
															<td><p>&nbsp;</p>
																<p>&nbsp;</p>
															</td>
														</tr>
														
														<tr>
															<td align=\"center\">
															<strong><u>CICI SUHANI, S.S, MM</u></strong><br/>
															NIP. 19801229 201001 2 005</td>
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
								<span style=\"font-size:24px\"><i>BAPENDA BALAM {$tgl_cetak}</i></span>
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

		$pdf->AddPage('L', 'F4');
		$pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 12, 17, 17, '', '', '', '', false, 300, '', false);
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

		$KABID_PENETAPAN_JABATAN = $config['KABID_PENETAPAN_JABATAN'];
		$KABID_PENDATAAN_JABATAN = $config['KABID_PENDATAAN_JABATAN'];

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
		$html = "<table width=\"1115\" class=\"main\" border=\"1\">
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
										Tahun : {$DATA->CPM_TAHUN_PAJAK}</b> <br/>
									 
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
													Tinggi : {$DATA->CPM_ATR_PANJANG} m<br/>
													Muka : {$DATA->CPM_ATR_MUKA} m<br/>
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
															<br/>{$KABID_PENDATAAN_JABATAN}</td>
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
																Diperiksa oleh :<br/>{$KABID_PENETAPAN_JABATAN}<br>
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
								
								<span style=\"font-size:24px\"><i>BAPENDA BALAM {$tgl_cetak}</i></span>
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

		$pdf->AddPage('L', 'F4');
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
		$sql = sprintf("SELECT * FROM {$this->PATDA_REK_PERMEN13} where nmheader3 = 'Reklame'");
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




	public function get_hargadasar($params = array())
	{
		// $bangunan = $_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN']['0'];
		// if (!empty($_POST['bangunan'])) {
		// 	$bangunan = $_POST['bangunan'];
		// } else {
		// 	$bangunan = $_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN']['0'];
		// }
		$jumlahloop = count($_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN']);

		if (count($params) == 0) {
			extract($_POST);
		} else {
			// print_r($params);exit;
			extract($params);
		}
		// $jumlahloop = count($_POST['bangunan']);
		// echo '<pre>';
		// print_r($_POST);
		// echo '</pre>';
		// echo '<pre>';
		// print_r($params['html']);
		// echo '</pre>';

		// var_dump($_POST);
		// die;
		$biaya = $this->toNumber($biaya);
		// $harga_dasar_uk = $this->toNumber($harga_dasar_uk);
		// $harga_dasar_tin = $this->toNumber($harga_dasar_tin);
		$tarif_pajak = $tarif / 100;
		$harga_ketinggian = 0;
		$luas = (float)round($panjang * $lebar, 4);
		// $muka = $muka > 3 ? 4 : $muka;
		$alkohol_rokok = (isset($alkohol_rokok) && $alkohol_rokok == 1) ? true : false;
		$tol = (isset($tol) && $tol == 1) ? true : false;

		$response = array(
			'luas' => $luas,
			'njop' => 0,
			'harga' => 0,
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
			// if (!empty($_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN'])) {
			// 	$tinggii = str_replace(['<', '>'], '', $tinggi);

			// 	// Inisialisasi variabel untuk menyimpan hasil query
			// 	$results = [];

			// 	foreach ($_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN'] as $bangunan) {
			// 		switch ($bangunan) {
			// 			case "TANAH":
			// 				$cmp_option = 1;
			// 				break;
			// 			case "BANGUNAN":
			// 				$cmp_option = 2;
			// 				break;
			// 			default:
			// 				$cmp_option = 3;
			// 		}

			// 		$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA, CPM_SATUAN, CPM_OPTION from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_OPTION='{$cmp_option}' AND CPM_REKENING='{$kdrek}' AND ('$tinggii' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)");

			// 		$data_njopr = mysqli_fetch_object($sql);

			// 		if ($data_njopr) {
			// 			$results[] = $data_njopr;
			// 		}
			// 	}

			// 	// Gunakan hasil query yang disimpan dalam array $results
			// 	foreach ($results as $data_njopr) {
			// 		$satuan = (isset($data_njopr->CPM_SATUAN) && !empty($data_njopr->CPM_SATUAN)) ? str_replace('2', '<sup>2</sup>', $data_njopr->CPM_SATUAN) : $satuan;
			// 		$harga_dasar->ketinggian = $data_njopr->CPM_HARGA;
			// 		$harga_dasar->option = $data_njopr->CPM_OPTION;
			// 	}
			// } else {

			switch ($bangunan) {
				case "TANAH":
					$cmp_option = 1;
					break;
				case "BANGUNAN":
					$cmp_option = 2;
					break;
				default:
					$cmp_option = 3;
			}

			$tinggii = str_replace(['<', '>'], '', $tinggi);

			$sql = mysqli_query($this->Conn, "SELECT tarif2 from patda_rek_permen13 WHERE kdrek='{$kdrek}'");

			$data_njopr = mysqli_fetch_object($sql);
			$harga_dasar_ukuran = $data_njopr->tarif2;
			// var_dump($harga_dasar_ukuran);die;
			// }


			// var_dump($harga_dasar->ketinggian);
			// die;
			// data NJOPR (harga ukuran)
			$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA,CPM_SATUAN from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_REKENING='{$kdrek}' AND ('$luas' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)");
			$data_njopr = mysqli_fetch_object($sql);
			$harga_dasar->ukuran = $data_njopr->CPM_HARGA;

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
			extract($param);

			$NSL = [];
			// $sql = mysqli_query($this->Conn, "SELECT PN.CPM_PARAM, PN.CPM_TARIF_PERSENTAGE, RP.CPM_JALAN FROM PATDA_REKLAME_PARAM_NILAI PN JOIN patda_reklame_param_jalan RP ON PN.CPM_GRUP = RP.CPM_GRUP where RP.CPM_GRUP='NFJ' AND PN.CPM_GRUP = 'NFJ'");
			// while ($row = mysqli_fetch_assoc($sql)) {
			// 	$obj = (object)[];
			// 	$obj->lokasi = $row['CPM_PARAM'];
			// 	$obj->tarif = $row['CPM_TARIF_PERSENTAGE'];
			// 	array_push($NSL, $obj);
			// }

			// edit by derieseesss
			$sql = mysqli_query($this->Conn, "SELECT RP.CPM_JALAN, RP.NPM_PARAM, RP.NILAI, PN.CPM_PARAM
			FROM patda_reklame_param_jalan RP
			JOIN PATDA_REKLAME_PARAM_NILAI PN ON RP.NPM_PARAM = PN.CPM_PARAM
			WHERE RP.CPM_GRUP='NFJ' AND PN.CPM_GRUP = 'NFJ'");
			// var_dump($sql);die;
			while ($row = mysqli_fetch_assoc($sql)) {
				$obj = (object)[];
				$tarif = $row['NILAI'];
				$tarif = (float) str_replace('%', '', $tarif);

				$obj->jalan = $row['CPM_JALAN'];
				$obj->lokasi = $row['NPM_PARAM'];
				$obj->tarif = $tarif;
				array_push($NSL, $obj);
			}

			$label_tinggi = 'Tinggi';
			$val_tinggi = $tinggi;

			// Formula Des 2022
			// $rumus = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
			// 		NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
			// 		NSR = NJOPR + NSPR<br>
			// 		Total Pajak = NSR x Tarif Pajak";

			// $tarif = 25;
			// foreach ($NSL as $nnn) {
			// 	if ($nnn->lokasi === $jalan) {
			// 		$tarif = $nnn->tarif;
			// 		break;
			// 	}
			// }
			// add by Deries - Tuker value Jalan dan type Jalan

			if (count($params) > 0) {
				$temp_jalan = $jalan;
				$temp_typejalan = $jalan_type;
				$jalan = $temp_typejalan;
				$jalan_type = $temp_jalan;
			}

			foreach ($NSL as $nnn) {
				if ($jalan_type) {
					if ($nnn->lokasi === $jalan_type) {
						$tarif = $nnn->tarif;
						$locReklame = $nnn->lokasi;
						break;
					}
				} else {
					if ($nnn->jalan === $jalan) {
						$tarif = $nnn->tarif;
						$locReklame = $nnn->lokasi;
						break;
					}
				}
			}

			// print_r($NSL);exit;

			if ($kdrek == '4.1.01.09.01.004') { // Vidiotron/megatron 

				// Formula Des 2022
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
				NSR = NJOPR + (NSL x NJOPR sdsd)<br>
				Pajak Terpasangan = NSR x Tarif x Durasi x Lama Pemasangan (1 Tahun)<br>
				Total Pajak = Pajak Terpasangan x Luas x Durasi Op x Jumlah Unit";

				$total_nspr = ($NFR + $NFJ + $NSP) * $harga_dasar->nspr;

				$total_njopr = $harga_dasar->ketinggian;

				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$subTotal_nsr = $total_nsr;
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . number_format($total_nsr, 2);
				$total_terpasang = $total_nsr * (25 / 100) * $durasi_hari;
				$hitung_pajak = "<br><b>Pajak Terpasanganggg</b><br>
					= NSR x Tarif x Durasi x Lama Pemasangan (1 Tahun)<br>
					= " . number_format($total_nsr) . " x " . number_format(25) . "% x 1 x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total_terpasang, 2);

				$total_pajak = $total_terpasang * $luas * $jam * 60 * (int)$jumlah;

				// var_dump($jam);
				// die;
				// $hitung_total .= "<br><br><b>Total Pajak</b><br>
				// 	= Pajak Terpasangan x Luas x Durasi Op x Jumlah Unit<br>
				// 	= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jam * 60) . " x " . number_format($jumlah) . "<br>
				// 	= Rp. " . number_format($total_pajak);
				$total = $total_pajak;
			} elseif ($kdrek == '4.1.01.09.02' || $kdrek == '4.1.01.09.03') { // Melekat/Stiker // Selebaran
				$rumus = "NJOPR = (Jumlah x Harga Dasar) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";
				$total_njopr = ($jumlah * $harga_dasar->ukuran) * $durasi_hari;
				$total_nsr = $total_njopr + $total_nspr;
				$hitung_njopr = "<b>NJOPR</b><br>
					= (Jumlah x Harga Dasar) x Durasi<br>
					= ({$jumlah} x " . number_format($harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= (" . number_format($jumlah * $harga_dasar->ukuran) . ") x {$durasi_hari} Hari<br>
					= Rp. " . number_format($total_njopr);
				$total_nspr = 0;
				$hitung_nspr = '';
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR<br>
					= Rp. " . number_format($total_njopr);
				$total_nsr = $total_njopr;
				$label_tinggi = 'Jumlah';
				$satuan = 'lembar';
				$val_tinggi = $jumlah;
				$jumlah = 1;
			} elseif ($kdrek == '4.1.01.09.04') { // Reklame Berjalan
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
					NSR = NJOPR + (NSL x NJOPR)<br>
					Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Tahun)<br>
					Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit";
				$total_njopr = $harga_dasar->ketinggian;

				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . number_format($total_nsr, 2);

				$total_terpasang = (float)$total_nsr * (25 / 100) * $durasi_hari;

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Lama Pemasangan (1 Tahun)<br>
					= " . number_format($total_nsr) . " x " . number_format(25) . "% x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total_terpasang);

				$total_pajak = $total_terpasang * $luas * (int)$jumlah;

				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
					= Rp. " . number_format($total_pajak);

				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
				$subTotal_nsr = $total_nsr;
			} elseif ($kdrek == '4.1.01.09.05' || $kdrek == '4.1.01.09.06') { // Udara dan Apung
				$rumus = "NSR = NJOPR + (NSL x NJOPR)<br>
					Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Hari)<br>
					Total Pajak = Pajak Terpasangan x Jumlah Unit x Lama Pemasangan";

				$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;

				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . number_format($total_nsr, 2);

				$total_terpasang = (float)$total_nsr * (25 / 100) * 1;

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Lama Pemasangan (1 Hari)<br>
					= " . number_format($total_nsr) . " x " . number_format(25) . "% x 1<br>
					= Rp. " . number_format($total_terpasang);

				$total_pajak = number_format($total_terpasang, 0, ',', '') * (int)$jumlah * (int)$durasi_hari;

				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Jumlah Unit x Lama Pemasangan<br>
					= " . number_format($total_terpasang) . " x " . number_format($jumlah) . " x " . (int)$durasi_hari . "<br>
					= Rp. " . number_format($total_pajak);

				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
				// var_dump($total_terpasang);
			} elseif ($kdrek == '4.1.01.09.07') { // Suara 
				$rumus = "NJOPR = Harga Dasar Per Detik<br>
						NSR = NJOPR + (NSL x NJOPR)<br>
						Total Pajak = NSR x Tarif Pajak x Durasi (Detik) x Lama Pemasangan";
				$total_njopr = $harga_dasar->ketinggian;
				// $tarif = 25;  // ketentuan pasal 6 (ayat 6)
				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr, 2) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . number_format($total_nsr) . "/Detik/Hari";

				$total_pajak = $total_nsr * (25 / 100) * ((int)$jam) * $durasi_hari;
				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= NSR x Tarif x Durasi x Lama Pemasangan<br>
					= " . (float)$total_nsr . " x " . 25 . "% x " . number_format((int)$jam) . " x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total_pajak);
				//Rumus Yang di Pakai minta di ubah menjadi detik
				//$total_pajak = $total_nsr * ($tarif / 100) * ((int)$jam * 60) * $durasi_hari;
				// $hitung_total = "<br><br><b>Total Pajak</b><br>
				// = NSR x Tarif x Durasi x Lama Pemasangan<br>
				// = " . (float)$total_nsr . " x " . $tarif . "% x " . number_format((int)$jam * 60) . " x " . number_format($durasi_hari) . "<br>
				// = Rp. " . number_format($total_pajak);
				$total = $total_pajak;
			} elseif ($kdrek == '4.1.01.09.08') { // Slide/Film 
				$rumus = "NJOPR = Harga Dasar<br>
						NSR = NJOPR + (NSL x NJOPR)<br>
						Total Pajak = NSR x Tarif Pajak x Luas x Durasi x Jumlah Unit x Lama Pemasangan";
				$total_njopr = $harga_dasar->ketinggian;

				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$subTotal_nsr = $total_nsr;
				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr, 2) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . (float)$total_nsr . "/m2/Detik/Hari";

				$total_terpasang = (float)$total_nsr * (25 / 100);

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif<br>
					= " . (float)$total_nsr . " x " . number_format(25) . "%<br>
					= Rp. " . (float)$total_terpasang;

				$total_pajak = $total_terpasang * $luas * ((int)$jam * 60) * (int)$jumlah * $durasi_hari;

				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Detik X Jumlah Unit x Lama Pemasangan<br>
					= " . (float)$total_terpasang . " x " . $luas . " x " . number_format((int)$jam * 60) . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total_pajak);

				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
			} elseif ($kdrek == '4.1.01.09.10') { // Wall Painting dan Sejenisnya
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
					NSR = NJOPR + (NSL x NJOPR)<br>
					Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Tahun)<br>
					Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit";

				if ($kdrek === '4.1.01.09.01.006') {
					$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;
					$total_nsr = round($total_njopr + (($tarif / 100) * $total_njopr));
					$subTotal_nsr = $total_nsr;
				} else {
					$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;
					$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
					$subTotal_nsr = $total_nsr;
				}

				$hitung_nsr = "<b>NSR</b><br>
				=  NJOPR + (NSL x NJOPR)<br>
				= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
				= Rp. " . number_format($subTotal_nsr);

				$total_terpasang = $subTotal_nsr * (25 / 100) * $durasi_hari;
				$subTotal_terpasang = floor($total_terpasang);

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
				= NSR x Tarif x Lama Pemasangan (1 Tahun)<br>
				= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x " . number_format($durasi_hari) . "<br>
				= Rp. " .  number_format($subTotal_terpasang, 0, '.', ',');

				$total_pajak = $subTotal_terpasang * $luas * (50 / 100) *  (int)$jumlah;
				$subTotal_pajak = round($total_pajak);

				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit x Dihitung 50% dari nilai perhitungan tarif pajak Billboard<br>
					= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . " x " . " 50% " . "<br>
					= Rp. " . number_format($subTotal_pajak);

				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
				$total_nsr = round($total_nsr / 2);
	
			} elseif ($kdrek == '4.1.01.09.01.001' || $kdrek == '4.1.01.09.01.001.1') { // billboard
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
				NSR = NJOPR + (NSL x NJOPR)<br>
				Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Tahun)<br>
				Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit";

				if ($kdrek === '4.1.01.09.01.006') {
					$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;
					$total_nsr = round($total_njopr + (($tarif / 100) * $total_njopr));
					$subTotal_nsr = $total_nsr;
				} else {
					$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;
					$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
					$subTotal_nsr = $total_nsr;
				}
				// var_dump($harga_ketinggian);
				// die;
				$hitung_nsr = "<b>NSR</b><br>
				=  NJOPR + (NSL x NJOPR)<br>
				= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
				= Rp. " . number_format($subTotal_nsr);

				$total_terpasang = $subTotal_nsr * (25 / 100) * $durasi_hari;
				$subTotal_terpasang = $total_terpasang;

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
				= NSR x Tarif x Lama Pemasangan (1 hari)<br>
				= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x " . 1 . "<br>
				= Rp. " .  number_format($subTotal_terpasang, 2, '.', ',');


				// var_dump($subTotal_nsr);
				// die;
				$total_pajak = $subTotal_terpasang * $luas * (int)$jumlah;
				$subTotal_pajak = round($total_pajak);


				$hitung_total = "<br><br><b>Total Pajak</b><br>
				= Pajak Terpasangan x Luas x Jumlah Unit<br>
				= " . number_format($subTotal_terpasang, 2, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
				= Rp. " . number_format($subTotal_pajak);


				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
			} else {
				// var_dump($_POST);die;
				if ($_POST['rumus'] == 'RMS1') {
					$rumus  = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame)<br>
					Nilai Strategis NSPR = ". 0.25 * 100 ." x NJOPR <br>
					NSR = NSPR + NJOPR <br>
					Total Pajak 		 = Tarif Pajak x Nilai Strategis NSPR<br><br>";

					$subTotal_nsrr = $panjang * $lebar * $jumlah * $harga_dasar_ukuran;
					$subTotal_nsr = $subTotal_nsrr * (25 / 100);


					$hitung_nsr = "<b>NJOPR</b><br>
					= (Panjang x Lebar x Jumlah unit x Harga dasar Ukuran)<br>
					= " . number_format($panjang) . " x (" . number_format($lebar) . " x " . number_format($jumlah) . ") x  " . number_format($harga_dasar_ukuran) . "<br>
					= Rp. " . number_format($subTotal_nsrr) ." <br><br>

					= " . number_format(25) . "% x " . number_format($subTotal_nsrr) . "<br>
					= Rp. " . number_format($subTotal_nsr) ." ";

					$total_terpasang = $subTotal_nsr * ($tarif / 100);
					$subTotal_terpasang = $total_terpasang;

					$hitung_pajak = "<br><b>Nilai Strategis  NSPR x NJOPR</b><br>
						= NSPR x NJOPR <br>
						= " . number_format($tarif) . "% x " . number_format($subTotal_nsr) . "<br>
						= Rp. " .  number_format($total_terpasang, 2, '.', ',');

					$total_pajak_terpasang = $subTotal_nsr + $total_terpasang;
					$total_pajak = $total_pajak_terpasang;

					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= " . number_format($subTotal_nsr, 2, '.', ',') . " + " . number_format($total_terpasang) . "<br>
					= Rp. " . number_format($total_pajak_terpasang);


					$hitung_total = $hitung_pajak . $hitung_total;
					$total = $total_pajak;

				}elseif ($_POST['rumus'] == 'RMS2') {
					$rumus  = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame)<br>
					Nilai Strategis NSPR = ". 0.25 * 100 ." x NJOPR <br>
					NSR = NSPR + NJOPR <br>
					Total Pajak 		 = Nilai Strategis  NSPR x NJOPR";

					$subTotal_nsr = $panjang * $lebar * $jumlah * $harga_dasar_ukuran;
					// $subTotal_nsr = $subTotal_nsrr * (25 / 100);


					$hitung_nsr = "<b>NJOPR</b><br>
					= (Panjang x Lebar x Jumlah unit x Harga dasar Ukuran)<br>
					= " . number_format($panjang) . " x (" . number_format($lebar) . " x " . number_format($jumlah) . ") x  " . number_format($harga_dasar_ukuran) . "<br>
					= Rp. " . number_format($subTotal_nsr) ." <br>";

					$total_terpasang = $subTotal_nsr * ($tarif / 100);
					$subTotal_terpasang = $total_terpasang;

					$hitung_pajak = "<br><b>Nilai Strategis  NSPR x NJOPR</b><br>
						= " . number_format($tarif) . "% x " . number_format($subTotal_nsr) . "<br>
						= Rp. " .  number_format($total_terpasang, 2, '.', ',');

					$total_pajak_terpasang = $subTotal_nsr + $total_terpasang;
					$total_pajak = $total_pajak_terpasang;

					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= " . number_format($subTotal_nsr, 2, '.', ',') . " + " . number_format($total_terpasang) . "<br>
						= Rp. " . number_format($total_pajak_terpasang);


					$hitung_total = $hitung_pajak . $hitung_total;
					$total = $total_pajak;
				}
				
				
			}

			$pokok = $total;
			// var_dump($alkohol_rokok);die;
			if ($_POST['rumus'] == 'RMS1') {

				if ($alkohol_rokok) {
					$total_rokok = $total + ($pokok * 0.25);
					$hasil_persen_rokok = ($pokok * 0.25);

					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " + 25%<br> 
					= " . number_format($hasil_persen_rokok, 2, '.', ',') ."<br>
					= " . number_format($total, 2, '.', ',') . " + " . number_format($hasil_persen_rokok, 2, '.', ',') . "<br> " : " ";

					$total = $total + $hasil_persen_rokok;
				
				}
						
				$hitung_total = "<br><br><b>Total Pajak</b><br>
				= " . number_format($subTotal_nsr, 2, '.', ',') . " + " . number_format($total_terpasang) . "<br>
				{$hitung_alkohol_rokok}
				= Rp. " . number_format($total);

			} elseif($_POST['rumus'] == 'RMS2') {
				if ($alkohol_rokok) {
					$total_rokok = $total + ($pokok * 0.25);
					$hasil_persen_rokok = ($pokok * 0.25);

					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " + 25%<br> 
					= " . number_format($hasil_persen_rokok, 2, '.', ',') ."<br>
					= " . number_format($total, 2, '.', ',') . " + " . number_format($hasil_persen_rokok, 2, '.', ',') . "<br> " : " ";

					$total = $total + $hasil_persen_rokok;
				
				}
		
				$hitung_total = "<br><br><b>Total Pajak</b><br>
				= " . number_format($subTotal_nsr, 2, '.', ',') . " + " . number_format($total_terpasang) . "<br>
				{$hitung_alkohol_rokok}
				= Rp. " . number_format($total);
				
			}


			$hitung = $hitung_njopr . '<br>' . $hitung_nspr . '<br>' . $hitung_nsr . '<br>' . $hitung_pajak . '<br>' . $hitung_total;
			$hitung = str_replace('<br><br><br><br>', '<br><br>', $hitung);

			$html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
			$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
			$html .= "<table width='100%'><tr><td>";
			if ($luas > 0) $html .= 'Luas Reklame : ' . (float)number_format($luas, 2) . " m<sup>2</sup> <br/>";



			// if ($val_tinggi > 0) $html .= $label_tinggi . ' : ' . number_format($val_tinggi) . " " . str_replace('<sup>2</sup>', '', $satuan) . "<br/>";
			$html .= 'Durasi : ' . $durasi . ' ' . $durasi_label . " <br/>";
			if ($kdrek == '4.1.01.09.01.004' || $kdrek == '4.1.01.09.08') /*videoTron suara dan slide*/
				$rumus_menitOperasional = 60 * $jam;

			if ($kdrek == '4.1.01.09.07') {
				$html .= 'Waktu Operational : ' . $jam . ' Detik/' . $durasi_label . " <br/>";  /*videoTron suara dan slide*/
			} else if ($kdrek == '4.1.01.09.08') {
				$html .= 'Waktu Operational : ' . $rumus_menitOperasional . ' Detik/' . $durasi_label . " <br/>";
			} else {
				$html .= 'Waktu Operational : ' . $rumus_menitOperasional . ' Menit/Hari' . " <br/>";
			}
			// $html .= 'Tarif pajak : ' . number_format($tarif, 0) . "% <br/>";
			$html .= 'Tarif pajak : ' . number_format(25, 0) . "% <br/>";
			// $html .= 'NJOP : ' . number_format($njop,0) . " m<sup>2</sup> <br/>";
			$html .= "</td></tr></table>";
			$html .= "</div>";
			$html .= "<table width='100%'><tr><td style='background:#CDDCE6;font-size:12px!important'>";
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

		$response['njop'] = $subTotal_nsr;
		$response['total'] = $total;
		// $response['html'] = $html;
		$response['tarif'] = $tarif;
		$response['harga_ketinggian'] = $total_njopr;
		$response['rumus_hitung'] = $rumus . $hitung;
		// $response['rumus_hitung'] = $params['html'];
		$response['lokasi_reklame'] = $locReklame;
		$response['harga'] = $harga_dasar_ukuran;
		// var_dump($response['rumus_hitung']);die;
		// print_r($response['harga']);
		// die;
		if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
		// var_dump($response);
		// die;
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
				$this->CPM_NO = '';
				$this->CPM_NO = $this->get_config_value($this->_a, "KODE_SPTPD") . str_pad($no, 8, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->id_pajak] . "/" . date("y");
				$this->update_counter($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
			} else {
				$this->CPM_NO = $cpm_no;
			}

			#insert pajak baru
			$PAJAK_ATR = $_POST['PAJAK_ATR'];

			// echo '<pre>';
			// print_r($PAJAK_ATR);
			// echo '<br>';

			// exit();

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
                    (CPM_ID, CPM_ID_PROFIL, CPM_NO, CPM_MASA_PAJAK, CPM_TAHUN_PAJAK, CPM_TOTAL_OMZET, CPM_TOTAL_PAJAK, CPM_TARIF_PAJAK,
                    CPM_KETERANGAN, CPM_VERSION, CPM_AUTHOR, CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD, CPM_JNS_MASA_PAJAK,
                    CPM_MASA_PAJAK1, CPM_MASA_PAJAK2, CPM_SK_DISCOUNT, CPM_DISCOUNT, CPM_TYPE_PAJAK, CPM_DENDA_TERLAMBAT_LAP, CPM_PIUTANG, CPM_START_DENDA)
                    VALUES ( '%s','%s','%s',%f,%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%f,%d,%f,'%s','%s')",
				$this->CPM_ID,
				$this->CPM_ID_PROFIL,
				$this->CPM_NO,
				$this->CPM_MASA_PAJAK,
				$this->CPM_TAHUN_PAJAK,
				$this->CPM_TOTAL_OMZET,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_TARIF_PAJAK,
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
				$this->CPM_PIUTANG,
				1
			);



			// echo $this->CPM_TAHUN_PAJAK;exit();
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
					// $jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
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
					$no = $_REQUEST['no'] + 1;
					$nilai_pk2 = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_NILAI_PIHAK_KETIGA-' + $no][$x]);


					//tambahan
					$hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
					$hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
					$tinggi = mysql_escape_string($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
					$gedung = (isset($PAJAK_ATR['CPM_ATR_GEDUNG'][$x])) ? $PAJAK_ATR['CPM_ATR_GEDUNG'][$x] : 'LUAR';
					$bangunan = (isset($PAJAK_ATR['CPM_ATR_BANGUNAN'][$x])) ? $PAJAK_ATR['CPM_ATR_BANGUNAN'][$x] : 'TANAH';
					$alkohol_rokok = (isset($PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x])) ? $PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x] : '0';
					$tol = (isset($PAJAK_ATR['CPM_ATR_TOL'][$x])) ? $PAJAK_ATR['CPM_ATR_TOL'][$x] : '0';
					$jalan = mysql_escape_string($PAJAK_ATR['CPM_ATR_JALAN'][$x]);
					$jalan_type = mysql_escape_string($PAJAK_ATR['CPM_ATR_JALAN_TYPE'][$x]);
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
							'npk2' => $nilai_pk2,
							//tambahan
							'harga_dasar_uk' => $hd_ukuran,
							'harga_dasar_tin' => $hd_ketinggian,
							'tinggi' => $tinggi,
							'gedung' => $gedung,
							'alkohol_rokok' => $alkohol_rokok,
							'tol' => $tol,
							'jalan' => $jalan,
							'jalan_type' => $jalan_type,
							'durasi_hari' => $jumlah_hari,
							'jam' => $jam,
						)
					);
					// print_r($res_hargadasar);
					// die;
					$nilai_strategis = $res_hargadasar['nilai_strategis'];
					$njop = $res_hargadasar['njop'];
					$perhitungan = $res_hargadasar['rumus_hitung'];
					$harga = $res_hargadasar['harga'];
					var_dump($res_hargadasar);
					die;
					$tot = $bangunan;
					//
					// var_dump($res_hargadasar['rumus_hitung']);
					// die;
					$query = sprintf(
						"INSERT INTO {$this->PATDA_REKLAME_DOC_ATR} 
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA, CPM_ATR_HARGA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_PANJANG, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA,
                            CPM_ATR_NILAI_STRATEGIS, CPM_ATR_KAWASAN, CPM_ATR_JALAN, CPM_ATR_JALAN_TYPE, CPM_ATR_SUDUT_PANDANG, CPM_ATR_NJOP, CPM_ATR_PERHITUNGAN,CPM_CEK_PIHAK_KETIGA,CPM_NILAI_PIHAK_KETIGA,CPM_ATR_SISI, CPM_ATR_ID_PROFIL, CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, CPM_ATR_GEDUNG, CPM_ATR_BANGUNAN, CPM_ATR_ALKOHOL_ROKOK, CPM_ATR_TOL,
							CPM_ATR_JAM,CPM_RUMUS)
                            VALUES ('%s','%s',%f,%f,'%s',
                                    %f,%f,%f,%f,%f,
                                    %f,'%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s','%s','%s')",
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
						$jalan_type,
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
						$bangunan,
						$alkohol_rokok,
						$tol,
						$jam,
						$_POST['RMS']
					);

					// echo '<pre>';
					// print_r($query);
					// echo '<br>';

					// exit();

					mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn) . ' ' . $query);
				}
			}
			return $res;
		}
		return false;
	}

	private function save_pajak_perpanjangan($cpm_no = '')
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
				$this->CPM_NO = '';
				$this->CPM_NO = $this->get_config_value($this->_a, "KODE_SPTPD") . str_pad($no, 8, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->id_pajak] . "/" . date("y");
				$this->update_counter($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
			} else {
				$this->CPM_NO = $cpm_no;
			}

			#insert pajak baru
			$PAJAK_ATR = $_POST['PAJAK_ATR'];

			// echo '<pre>';
			// print_r($PAJAK_ATR);
			// echo '<br>';

			// var_dump($_POST['PAJAK']['CPM_TGL_JATUH_TEMPO']);
			// die;

			$this->CPM_ID = c_uuid();
			$this->CPM_TGL_LAPOR = date("d-m-Y");
			$this->CPM_TGL_JATUH_TEMPO = $_POST['PAJAK']['CPM_TGL_JATUH_TEMPO'];
			$this->CPM_TOTAL_OMZET = $this->toNumber($this->CPM_TOTAL_OMZET);
			$this->CPM_TOTAL_PAJAK = $this->toNumber($this->CPM_TOTAL_PAJAK);
			$this->CPM_TARIF_PAJAK = $this->toNumber($this->CPM_TARIF_PAJAK);
			$this->CPM_NO_SSPD = ($this->CPM_NOP == '-') ? substr($this->CPM_NO, 0, 9) : substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
			$this->CPM_MASA_PAJAK1 = $PAJAK_ATR['CPM_ATR_BATAS_AWAL'][0];
			$this->CPM_MASA_PAJAK2 = $PAJAK_ATR['CPM_ATR_BATAS_AKHIR'][0];
			$this->CPM_ID_PROFIL = $PAJAK_ATR['CPM_ATR_NOP'][0];

			// $this->CPM_TGL_JATUH_TEMPO = $PAJAK_ATR['CPM_ATR_NOP'][0];

			$this->CPM_DENDA_TERLAMBAT_LAP = $this->toNumber($this->CPM_DENDA_TERLAMBAT_LAP);
			$this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

			$query = sprintf(
				"INSERT INTO {$this->PATDA_REKLAME_DOC}
                    (CPM_ID, CPM_ID_PROFIL, CPM_NO, CPM_MASA_PAJAK,CPM_TGL_JATUH_TEMPO, CPM_TAHUN_PAJAK, CPM_TOTAL_OMZET, CPM_TOTAL_PAJAK, CPM_TARIF_PAJAK,
                    CPM_KETERANGAN, CPM_VERSION, CPM_AUTHOR, CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD, CPM_JNS_MASA_PAJAK,
                    CPM_MASA_PAJAK1, CPM_MASA_PAJAK2, CPM_SK_DISCOUNT, CPM_DISCOUNT, CPM_TYPE_PAJAK, CPM_DENDA_TERLAMBAT_LAP, CPM_PIUTANG, CPM_PERPANJANGAN)
                    VALUES ( '%s','%s','%s',%f,'%s',%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%f,%d,%f,'%s','%s')",
				$this->CPM_ID,
				$this->CPM_ID_PROFIL,
				$this->CPM_NO,
				$this->CPM_MASA_PAJAK,
				$this->CPM_TGL_JATUH_TEMPO,
				$this->CPM_TAHUN_PAJAK,
				$this->CPM_TOTAL_OMZET,
				$this->CPM_TOTAL_PAJAK,
				$this->CPM_TARIF_PAJAK,
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
				$this->CPM_PIUTANG,
				1
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
					// $jumlah = $this->toNumber($PAJAK_ATR['CPM_ATR_JUMLAH'][$x]);
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
					$no = $_REQUEST['no'] + 1;
					$nilai_pk2 = mysqli_escape_string($this->Conn, $PAJAK_ATR['CPM_NILAI_PIHAK_KETIGA-' + $no][$x]);


					//tambahan
					$hd_ukuran = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_UK'][$x]);
					$hd_ketinggian = $this->toNumber($PAJAK_ATR['CPM_ATR_HARGA_DASAR_TIN'][$x]);
					$tinggi = mysql_escape_string($PAJAK_ATR['CPM_ATR_TINGGI'][$x]);
					$gedung = (isset($PAJAK_ATR['CPM_ATR_GEDUNG'][$x])) ? $PAJAK_ATR['CPM_ATR_GEDUNG'][$x] : 'LUAR';
					$bangunan = (isset($PAJAK_ATR['CPM_ATR_BANGUNAN'][$x])) ? $PAJAK_ATR['CPM_ATR_BANGUNAN'][$x] : 'TANAH';
					$alkohol_rokok = (isset($PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x])) ? $PAJAK_ATR['CPM_ATR_ALKOHOL_ROKOK'][$x] : '0';
					$tol = (isset($PAJAK_ATR['CPM_ATR_TOL'][$x])) ? $PAJAK_ATR['CPM_ATR_TOL'][$x] : '0';
					$jalan = mysql_escape_string($PAJAK_ATR['CPM_ATR_JALAN'][$x]);
					$jalan_type = mysql_escape_string($PAJAK_ATR['CPM_ATR_JALAN_TYPE'][$x]);
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
							'npk2' => $nilai_pk2,
							//tambahan
							'harga_dasar_uk' => $hd_ukuran,
							'harga_dasar_tin' => $hd_ketinggian,
							'tinggi' => $tinggi,
							'gedung' => $gedung,
							'alkohol_rokok' => $alkohol_rokok,
							'tol' => $tol,
							'jalan' => $jalan,
							'jalan_type' => $jalan_type,
							'durasi_hari' => $jumlah_hari,
							'jam' => $jam,
						)
					);

					$nilai_strategis = $res_hargadasar['nilai_strategis'];
					$njop = $res_hargadasar['njop'];
					$perhitungan = $res_hargadasar['rumus_hitung'];
					$harga = $res_hargadasar['harga'];
					$tot = $bangunan;
					//
					// var_dump($harga);
					// die;
					$query = sprintf(
						"INSERT INTO {$this->PATDA_REKLAME_DOC_ATR} 
                            (CPM_ATR_REKLAME_ID,  CPM_ATR_JUDUL, CPM_ATR_BIAYA, CPM_ATR_HARGA,
                            CPM_ATR_LOKASI, CPM_ATR_LEBAR, CPM_ATR_PANJANG, CPM_ATR_JUMLAH,CPM_ATR_JARI,CPM_ATR_MUKA,
                            CPM_ATR_TARIF, CPM_ATR_JUMLAH_TAHUN, CPM_ATR_JUMLAH_HARI, CPM_ATR_JUMLAH_MINGGU, CPM_ATR_JUMLAH_BULAN,
                            CPM_ATR_BATAS_AWAL, CPM_ATR_BATAS_AKHIR, CPM_ATR_TOTAL, CPM_ATR_REKENING, CPM_ATR_TYPE_MASA,
                            CPM_ATR_NILAI_STRATEGIS, CPM_ATR_KAWASAN, CPM_ATR_JALAN, CPM_ATR_JALAN_TYPE, CPM_ATR_SUDUT_PANDANG, CPM_ATR_NJOP, CPM_ATR_PERHITUNGAN,CPM_CEK_PIHAK_KETIGA,CPM_NILAI_PIHAK_KETIGA,CPM_ATR_SISI, CPM_ATR_ID_PROFIL, CPM_ATR_HARGA_DASAR_UK, CPM_ATR_HARGA_DASAR_TIN, CPM_ATR_TINGGI, CPM_ATR_GEDUNG, CPM_ATR_BANGUNAN, CPM_ATR_ALKOHOL_ROKOK, CPM_ATR_TOL,
							CPM_ATR_JAM)
                            VALUES ('%s','%s',%f,%f,'%s',
                                    %f,%f,%f,%f,%f,
                                    %f,'%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s',
                                    '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s','%s')",
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
						$jalan_type,
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
						$bangunan,
						$alkohol_rokok,
						$tol,
						$jam
					);


					mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn) . ' ' . $query);
				}
			}
			return $res;
		}
		return false;
	}

	// public function get_hargadasar_backup($params = array())
	// {
	// 	if (count($params) == 0) {
	// 		extract($_POST);
	// 	} else {
	// 		extract($params);
	// 	}

	// 	$biaya = $this->toNumber($biaya);
	// 	$tarif = $tarif / 100;
	// 	$luas = $panjang * $lebar;
	// 	$muka = $muka > 3 ? 4 : $muka;
	// 	$response = array(
	// 		'luas' => $luas,
	// 		'njop' => 0,
	// 		'nilai_strategis' => 0
	// 	);

	// 	$query = sprintf(
	// 		"
	// 		SELECT CPM_NJOP, 0 CPM_NILAI FROM PATDA_REKLAME_TARIF_NJOP
	// 		WHERE CPM_LUAS_AWAL <= %s AND CPM_LUAS_AKHIR >= %s
	// 		UNION
	// 		SELECT 0 CPM_NJOP, CPM_NILAI FROM PATDA_REKLAME_NILAI_STRATEGIS
	// 		WHERE CPM_KAWASAN = '%s' AND CPM_MUKA = '%s'",
	// 		$luas,
	// 		$luas,
	// 		$kawasan,
	// 		$muka
	// 	);

	// 	$res = mysqli_query($this->Conn, $query);
	// 	while ($data = mysqli_fetch_assoc($res)) {
	// 		if ($data['CPM_NJOP'] != 0) $response['njop'] = $data['CPM_NJOP'];
	// 		if ($data['CPM_NILAI'] != 0) $response['nilai_strategis'] = $data['CPM_NILAI'];
	// 	}

	// 	extract($response);

	// 	$rumus = "";
	// 	$hitung = "";

	// 	$total = 0;

	// 	if ($kdrek == '4.1.1.4.01.1' || $kdrek == '4.1.1.4.01.2') {
	// 		//Reklame Papan/BillBoard/Baliho/Neonbox
	// 		//Reklame Videotron/Megatron
	// 		$total = $luas * $nilai_strategis * $durasi * $tarif + $njop;
	// 		$rumus = "(Luas x Nilai Strategis x Durasi x Tarif pajak) + NJOP<br/>";
	// 		$hitung = "(" . number_format($luas, 0) . " x " . number_format($nilai_strategis, 0) . " x
	// 		" . number_format($durasi, 2) . " x " . number_format($tarif, 2) . ") + " . number_format($njop, 0) . "";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	} elseif ($kdrek == '4.1.1.4.02.1') {
	// 		//Reklame kain /spanduk/umbul-umbul, tenda reklame, banner dan sejenisnya
	// 		$total = $sisi * $biaya * $durasi;
	// 		$rumus = "(Jumlah x Tarif pajak x Durasi)<br/>";
	// 		$hitung = "(" . number_format($sisi, 0) . " x " . number_format($biaya, 0) . " x " . number_format($durasi, 2) . ")";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	} elseif ($kdrek == '4.1.1.4.03.1' || $kdrek == '4.1.1.4.04.1') {
	// 		//Reklame Melekat/Stiker
	// 		//Reklame Selebaran/poster/leaflet
	// 		$total = $biaya * $tarif;
	// 		$rumus = "(Biaya penyelenggaraan x Tarif pajak)<br/>";
	// 		$hitung = "(" . number_format($biaya, 0) . " x " . number_format($tarif, 2) . ")";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	} elseif ($kdrek == '4.1.1.4.06.1' || $kdrek == '4.1.1.4.07.1') {
	// 		//Reklame Udara
	// 		//Reklame Apung
	// 		$total = $biaya * $tarif * $durasi;
	// 		$rumus = "(Biaya penyelenggaraan x Tarif pajak x Durasi)<br/>";
	// 		$hitung = "(" . number_format($biaya, 0) . " x " . number_format($tarif, 2) . " x " . number_format($durasi, 2) . ")";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	} elseif ($kdrek == '4.1.1.4.05.1') {
	// 		//Reklame Berjalan termasuk pada Kendaraan
	// 		$total = $biaya * $sisi;
	// 		$rumus = "(Tarif pajak x Jumlah)<br/>";
	// 		$hitung = "(" . number_format($biaya, 0) . " x " . number_format($sisi, 0) . ")";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	} elseif ($kdrek == '4.1.1.4.08.1' || $kdrek == '4.1.1.4.09.1') {
	// 		//Reklame Suara
	// 		//Reklame Film/slide
	// 		$total = $biaya;
	// 		$rumus = "(Tarif pajak x Jumlah)<br/>";
	// 		$hitung = "(" . number_format($biaya, 0) . " x " . number_format($sisi, 0) . ")";
	// 		$hitung .= " = " . number_format($total, 2);
	// 	}

	// 	$html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:550px!important;text-align:left;'>";
	// 	$html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
	// 	$html .= "<table width='440'><tr><td>";
	// 	$html .= 'Luas Reklame : ' . number_format($luas, 0) . " m<sup>2</sup> <br/>";
	// 	$html .= 'NJOP : ' . number_format($njop, 0) . " m<sup>2</sup> <br/>";
	// 	$html .= 'Lama : ' . $durasi . ' ' . $durasi_label . " <br/>";
	// 	$html .= 'Nilai Strategis : ' . number_format($nilai_strategis, 0) . " <br/>";
	// 	$html .= "</td></tr></table>";
	// 	$html .= "</div>";
	// 	$html .= "<table width='550'><tr><td style='background:#CDDCE6;font-size:12px!important'>";
	// 	$html .= $rumus;
	// 	$html .= "</td></tr></table>";

	// 	$html .= "<table width='550'><tr><td>";
	// 	$html .= $hitung;
	// 	$html .= "</td></tr></table>";
	// 	$html .= "</div>";

	// 	$response['total'] = $total;
	// 	$response['html'] = $html;
	// 	$response['rumus_hitung'] = $rumus . $hitung;
	// 	if (count($params) == 0) echo $this->Json->encode($response);
	// 	else return $response;
	// }

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
		$list_type_masa = $this->get_type_masa(); //$lapor->get_type_masa();
		$list_sudut_pandang = $this->get_sudut_pandang();
		$list_type_tinggi = $this->get_type_tinggi();
		$list_tarif = $this->list_tarif();
		$list_kawasan = $list_tarif['lokasi'];
		$list_jalan = $this->get_jalan();
		$list_jalan_type = $this->get_jalan_type();

		$opt_nop = '<option selected value="" disabled>Pilih NOP</option>';
		$opt_rekening = '<option selected value="" disabled>Pilih Rekening</option>';
		$opt_sudut_pandang = '';
		$opt_type_masa = '';
		$opt_kawasan = '';

		foreach ($list_nop as $list) {
			$alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
			$kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
			$opt_nop .= "<option value='{$list['CPM_ID']}'>{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
		}

		foreach ($list_rekening as $rek) {
			$selected = $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
			$disabled = (empty($DATA['pajak']['CPM_ID']) || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
			$opt_rekening .= "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
			data-tinggi='{$rek->tarif3}'{$selected}{$disabled}>{$rek->kdrek} - {$rek->nmrek}</option>";
		}

		foreach ($list_sudut_pandang as $sp) {
			$opt_sudut_pandang .= "<option value='{$sp}'>$sp</option>";
		}

		foreach ($list_type_masa as $key => $val) {
			$sel = $key == $_REQUEST['type_masa'] ? ' selected' : '';
			$opt_type_masa .= "<option value='{$key}'{$sel}>$val</option>";
		}

		foreach ($list_kawasan as $kws) {
			$opt_kawasan .= "<option value='{$kws}'>$kws</option>";
		}

		$type_tinggi = $DATA['pajak_atr'][0]['CPM_ATR_TINGGI'];
		if (in_array($lapor->_mod, array("pel", ""))) {
			if (!in_array($lapor->_i, array(1, 3, ""))) {
				$opt_tinggi .= "<option value='{$type_tinggi}' selected>$type_tinggi</option>";
			} else {
				foreach ($list_type_tinggi as $key => $val) {
					$opt_tinggi .= "<option value='{$key}' " . ($type_tinggi == $key ? 'selected' : '') . ">$val</option>";
				}
			}
		} else {
			$opt_tinggi .= "<option value='{$type_tinggi}' selected>{$list_type_tinggi[$type_tinggi]}</option>";
		}

		$jln = $DATA['pajak_atr'][0]['CPM_ATR_JALAN'];
		if (in_array($lapor->_mod, array("pel", ""))) {
			if (!in_array($lapor->_i, array(1, 3, ""))) {
				$opt_jalan .= "<option value='{$jln}' selected>{$jln}</option>";
			} else {
				foreach ($list_jalan as $kws) {

					$opt_jalan .= "<option value='{$kws}' " . ($jln == $kws ? 'selected' : '') . ">$kws</option>";
				}
			}
		} else {
			$opt_jalan .= "<option value='{$jln}' selected>{$jln}</option>";
		}

		if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {
			$wt .=  "";
		} else {
			$wt .= "readonly";
		}

		if ($DATA['pajak_atr'][0]['CPM_CEK_PIHAK_KETIGA'] == 'true') {
			$pKetiga .=  "checked='checked'";
		} else {
			$pKetiga .= "";
		}
		$qty3 = $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'];
		if (isset($qty3)) {
			$qty4 .= "$qty3";
		} else {
			$qty4 .= 1;
		}
		$wt2 .= $DATA['pajak_atr'][0]['CPM_ATR_JAM'];
		$qtyy = $DATA['pajak_atr'][0]['CPM_ATR_SISI'];

		$jln = $DATA['pajak_atr'][0]['CPM_ATR_JALAN_TYPE'];
		if (in_array($lapor->_mod, array("pel", ""))) {
			if (!in_array($lapor->_i, array(1, 3, ""))) {
				$jln2 .= "<option value='{$jln}' selected>{$jln}</option>";
			} else {
				$jln2 .= "<option value='' selected>Pilih Jalan</option>";
				foreach ($list_jalan_type as $kws) {
					$jln2 .= "<option data-jln='{$list_jalan_lok}' value='{$kws}' " . ($jln == $kws ? 'selected' : '') . ">$kws</option>";
				}
			}
		} else {
			$jln2 .= "<option value='{$jln}' selected>{$jln}</option>";
		}

		$jln_type = $DATA['pajak_atr'][0]['CPM_ATR_JALAN'];
		if (in_array($lapor->_mod, array("pel", ""))) {
			if (!in_array($lapor->_i, array(1, 3, ""))) {
				$jln5 .= "<option value='{$jln_type}' selected>{$jln_type}</option>";
			} else {
				// $jln5 .= "<option value=''>Pilih Jalan</option>";
				foreach ($list_jalan as $kws) {
					$jln5 .= "<option value='{$kws}' " . ($jln_type == $kws ? 'selected' : '') . ">$kws</option>";
				}
			}
		} else {
			$jln5 .= "<option value='{$jln_type}' selected>{$jln_type}</option>";
		}




		$checked = $DATA['pajak_atr'][0]['CPM_ATR_BANGUNAN'] == 'TANAH' ? ' checked' : '';



		echo '<table width="900" class="child" id="atr_rek-' . $no . '" border="0" style="margin-top:8px">
		<tr>
			<th colspan="2">Reklame</th>
			<th colspan="2">Dimensi Reklame 3</th>
			<th width="80">Jumlah (Qty)</th>
			<th width="111">Jangka Waktu</th>
		</tr>
		<tr>
			<td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
			<td align="left" width="240" valign="top">
				<select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="' . ($idx) . '" id="CPM_NOP-' . $no . '" class="CPM_NOP form-control" onchange="hitungDetail(' . $no . '); get_op_lainnya(' . $no . ')" data-no="' . $no . '" style="max-width:260px">' . $opt_nop . '</select>

			</td>
			<td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
			<td width="130" align="center" valign="top"><label id="load-type-' . $no . '"></label>
				<input name="PAJAK_ATR[CPM_ATR_PANJANG][]" type="text" class="number" tabindex="' . ($idx + 4) . '" id="CPM_ATR_PANJANG-' . $no . '" size="11" maxlength="11" placeholder="Panjang" onkeyup="hitungDetail(' . $no . ')" />
			</td>
			<td rowspan="3" align="center" valign="top">
				<input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="number" tabindex="' . ($idx + 7) . '" id="CPM_ATR_JUMLAH-' . $no . '" value="1" size="11" maxlength="11" placeholder="Jumlah" onkeyup="hitungDetail(' . $no . ')" />
				<b class="isi">*</b>
			</td>
			<td rowspan="3" align="center" valign="top">
				<span id="jangka-waktu-' . $no . '">' . $_REQUEST['waktu'] . '</span>
			</td>
		</tr>
		<tr>
			<td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
			<td align="left" valign="top">
				<select class="form-control" tabindex="' . ($idx + 1) . '" name="PAJAK_ATR[CPM_ATR_REKENING][]" onchange="rekDetail(' . $no . ')" id="CPM_ATR_REKENING-' . $no . '" style="width:260px">' . $opt_rekening . '</select>
			</td>
			<td align="left" valign="top">Lebar <b class="isi">*</b></td>
			<td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="' . ($idx + 5) . '" type="text" class="number" id="CPM_ATR_LEBAR-' . $no . '" size="11" maxlength="11" placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" /></td>

		</tr>
		<tr>
			<td align="left" valign="top">Nama rekening</td>
			<td align="left" valign="top">
                <span id="nama-rekening-' . $no . '" style="text-align:left;color:#1B1389;font-weight:bold"></span><br /><span id="warning-rekening"></span>
            </td>

			<td align="left" valign="top">Tinggi <b class="isi">*</b></td>
			<td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_TINGGI][]" tabindex="' . ($idx + 5) . '" type="text" class="number" id="CPM_ATR_TINGGI-' . $no . '" size="11" maxlength="11" placeholder="Tinggi" onkeyup="hitungDetail(' . $no . ')" /></td>

			<!-- <td align="left" valign="top">Tinggi<b class="isi">*</b></td>
					<td align="center" valign="top">
					<select style="width:150px;height:30px;" class="form-control"  id="CPM_ATR_TINGGI-' . $no . '" onkeyup="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TINGGI][]">' . $opt_tinggi . '</select>

			</td> -->
		</tr>
		<tr>
        <td>Jenis Waktu Pemakaian</td>
        <td>
            <select class="form-control" style="height:30px;" id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">' . $opt_type_masa . ' hidden</select>

		</tr>

        <tr>
			<td class="ID_JAM-' . $no . '"></td>
			<td class="ID_JAM-' . $no . '"></td>
			<td class="ID_JAM-' . $no . '" align="left" valign="top">Waktu Tayang <b class="isi">*</b></td>
			<td class="ID_JAM-' . $no . '" align="center" valign="top">
				<input class="form-control" style="width:150px;height:30px;display:inline-block;" name="PAJAK_ATR[CPM_ATR_JAM][]" tabindex="3" type="text" class="number" id="CPM_ATR_JAM-' . $no . '" size="11" minlength="" maxlength="11" onkeypress="" value="' . $wt2  . '"   placeholder="Menit/Hari" />
		</td>
		</tr>

		<tr>
	<!--	<td>Lokasi Jalan </td>
		<td>
			<select class="form-control CPM_ATR_JALAN_TYPE" style="height: 30px;" id="CPM_ATR_JALAN_TYPE-' . $no . '"
				name="PAJAK_ATR[CPM_ATR_JALAN_TYPE][]" onchange="hitungDetail(' . $no . ')">' . $jln2 . '</select>
		</td> -->
		
		

		</tr>
		
		
		<tr>
		<td>Lokasi Reklame</td>
			<td>
				<select class="form-control CPM_ATR_JALAN" style="height: 30px;" id="CPM_ATR_JALAN-' . $no . '"
					name="PAJAK_ATR[CPM_ATR_JALAN][]" onchange="hitungDetail(' . $no . ')">' . $jln5 . '</select>
			</td>
			<td colspan="2">
			Alkohol/Rokok <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" onclick="hitungDetail(' . $no . ')" /> Ya</label> &nbsp;
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" onclick="hitungDetail(' . $no . ')" /> Tidak</label>
		</td>
		<tr>
		
		<tr>
			<td>Biaya Tarif Pajak</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_BIAYA][]" style="width: 260px;" placeholder="Biaya Tarif Pajak" type="text" class="number" id="CPM_ATR_BIAYA-' . $no . '" readonly />
			</td>

			<td colspan="2">
			<select class="form-control text-center" onchange="hitungDetail(' . $no . ')" name="RMS" id="RMS-' . $no . '">
				<option value="">-->PILIH RUMUS<--</option>
				<option value="RMS1">RUMUS 1</option>
				<option value="RMS2">RUMUS 2</option>
			</select>
		</td>

  
		</tr>

        <tr>
			<td>Harga Dasar Ketinggian</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][]" style="width: 260px;" placeholder="Biaya Harga Dasar" type="text" class="number" id="CPM_ATR_HARGA_DASAR_TIN-' . $no . '" readonly value="' . $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_TIN'] . '" ' . ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) . ' ? "" : "readonly";
			</td>
			
			<td align="left" colspan="4" rowspan="6" valign="top">
				<div id="area_perhitungan-' . $no . '"></div>	
			</td>

		</tr>


		<tr>
			<td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
			<td align="left" valign="top"><div align="left">
					<textarea name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL-' . $no . '" tabindex="' . ($idx + 8) . '" onchange="hitungDetail(' . $no . ')" style="width: 260px;" placeholder="Judul Reklame"></textarea>
				</div></td>
		</tr>
		<tr>
			<td align="left" valign="top">Lokasi <b class="isi">*</b></td>
			<td align="left" valign="top"><div align="left">
					<textarea name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI-' . $no . '" tabindex="' . ($idx + 9) . '" style="width: 260px;" placeholder="Lokasi"></textarea>
				</div></td>
		</tr>
		<tr>
			<td colspan="6" align="right" valign="top">
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID-' . $no . '" value="" />
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL-' . $no . '" value="0" />
				<input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF-' . $no . '" value="0" />
				<button type="button" class="btn btn-sm btn-secondary my-1 mr-1" onclick="delRow(' . $no . ')" id="deleteRow">Hapus</button>
			</td>
		</tr>
		</table>';
	}

	function deleteRow()
	{
		$output = array('status' => 0, 'pesan' => 'Item gagal dihapus. Silahkan coba lagi!');
		$del = 1;
		if ($del) {
			$output = array('status' => 1, 'pesan' => 'Item berhasil dihapus');
		}
		echo json_encode($output);
	}

	public function get_dataop($params = array())
	{
		if (count($params) == 0) {
			extract($_POST);
		} else {
			extract($params);
		}
		$profile = $this->get_op_reklame($cpm_nop);
		$response['CPM_NAMA_OP'] = $profile['CPM_NAMA_OP'];
		$response['CPM_ALAMAT_OP'] = $profile['CPM_ALAMAT_OP'];
		if (count($params) == 0) echo $this->Json->encode($response);
		else return $response;
	}
}
