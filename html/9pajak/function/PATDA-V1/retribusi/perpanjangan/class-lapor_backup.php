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

		if ($rek == '4.1.01.09.01.001' || $rek == '4.1.01.09.01.002' || $rek == '4.1.01.09.01.003' || $rek == '4.1.01.09.05' || $rek == '4.1.01.09.10.001') { // Vidiotron/megatron, Reklame Billboard, Reklame Kendaraan berjalan, Reklame Peragaan dan Reklame Neonbox
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

			$profil = $Op->get_last_profil_perpanjangan($npwpd, $nop);

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
			atr.CPM_ATR_REKENING, atr.CPM_ATR_TYPE_MASA, atr.CPM_ATR_KAWASAN, atr.CPM_ATR_JALAN, atr.CPM_ATR_JALAN_TYPE ,atr.CPM_ATR_SUDUT_PANDANG, atr.CPM_ATR_PERHITUNGAN,
			atr.CPM_CEK_PIHAK_KETIGA, atr.CPM_NILAI_PIHAK_KETIGA,
			atr.CPM_ATR_NJOP, atr.CPM_ATR_NILAI_STRATEGIS,
			per.nmrek ,atr.CPM_ATR_TARIF, atr.CPM_ATR_JUMLAH_TAHUN,  atr.CPM_ATR_JUMLAH_BULAN,  atr.CPM_ATR_JUMLAH_MINGGU, atr.CPM_ATR_JUMLAH_HARI, per.type_masa, per.nmrek ,prf.CPM_NPWPD, prf.CPM_NOP, prf.CPM_NAMA_OP, prf.CPM_ALAMAT_OP,
			atr.CPM_ATR_SISI, atr.CPM_ATR_TINGGI, atr.CPM_ATR_HARGA_DASAR_UK, atr.CPM_ATR_HARGA_DASAR_TIN, atr.CPM_ATR_GEDUNG, atr.CPM_ATR_BANGUNAN, atr.CPM_ATR_ALKOHOL_ROKOK, atr.CPM_ATR_TOL, atr.CPM_ATR_JAM, prf.CPM_KECAMATAN_OP
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


	public function get_data_atr()
	{
		$Op = new ObjekPajak();
		$arr_rekening = $this->getRekening("4.1.01.09");
		$pajak_atr = array();
		$list_nop = array();

		// $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
		$query = "SELECT atr.CPM_ATR_ID, atr.CPM_ATR_ID_PROFIL,atr.CPM_ATR_JENIS, atr.CPM_ATR_JUDUL, atr.CPM_ATR_LOKASI, atr.CPM_ATR_PANJANG, atr.CPM_ATR_LEBAR,
		atr.CPM_ATR_MUKA, atr.CPM_ATR_JARI, atr.CPM_ATR_JUMLAH, atr.CPM_ATR_BATAS_AWAL, atr.CPM_ATR_BATAS_AKHIR, atr.CPM_ATR_BIAYA, atr.CPM_ATR_HARGA, atr.CPM_ATR_TOTAL,
		atr.CPM_ATR_REKENING, atr.CPM_ATR_TYPE_MASA, atr.CPM_ATR_KAWASAN, atr.CPM_ATR_JALAN, atr.CPM_ATR_JALAN_TYPE ,atr.CPM_ATR_SUDUT_PANDANG, atr.CPM_ATR_PERHITUNGAN,
		atr.CPM_CEK_PIHAK_KETIGA, atr.CPM_NILAI_PIHAK_KETIGA,
		atr.CPM_ATR_NJOP, atr.CPM_ATR_NILAI_STRATEGIS,
		per.nmrek ,atr.CPM_ATR_TARIF, atr.CPM_ATR_JUMLAH_TAHUN,  atr.CPM_ATR_JUMLAH_BULAN,  atr.CPM_ATR_JUMLAH_MINGGU, atr.CPM_ATR_JUMLAH_HARI, per.type_masa, per.nmrek ,prf.CPM_NPWPD, prf.CPM_NOP, prf.CPM_NAMA_OP, prf.CPM_ALAMAT_OP,
		atr.CPM_ATR_SISI, atr.CPM_ATR_TINGGI, atr.CPM_ATR_HARGA_DASAR_UK, atr.CPM_ATR_HARGA_DASAR_TIN, atr.CPM_ATR_GEDUNG, atr.CPM_ATR_BANGUNAN, atr.CPM_ATR_ALKOHOL_ROKOK, atr.CPM_ATR_TOL, atr.CPM_ATR_JAM, prf.CPM_KECAMATAN_OP
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
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
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
		$bangunan = $_POST['PAJAK_ATR']['CPM_ATR_BANGUNAN']['0'];
		// echo "<pre>";print_r($_POST);exit();

		if (count($params) == 0) {
			extract($_POST);
		} else {
			// print_r($params);exit;
			extract($params);
		}

		$biaya = $this->toNumber($biaya);
		// $harga_dasar_uk = $this->toNumber($harga_dasar_uk);
		// $harga_dasar_tin = $this->toNumber($harga_dasar_tin);
		$tarif_pajak = $tarif / 100;
		$harga_ketinggian = 0;
		$luas = round($panjang * $lebar, 2);
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

			// if ($gedung == 'DALAM' || $bangunan == 'BANGUNAN') $cmp_option = 2;

			switch ($bangunan) {
				case "BANGUNAN":
					$cmp_option = 2;
					break;
				case "TANAH":
					$cmp_option = 1;
					break;
				default:
					$cmp_option = 3;
			}

			// if ($bangunan == 'BANGUNAN') {
			// 	$cmp_option = 2;
			// } else {
			// 	$cmp_option = 1;
			// }
			$tinggii = str_replace(['<', '>'], '', $tinggi);

			// var_dump($bangunan);
			// die;
			// $sql = mysqli_query($this->Conn, "SELECT CPM_HARGA,CPM_SATUAN from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_OPTION='{$cmp_option}' AND CPM_REKENING='{$kdrek}' AND  CPM_TINGGI_UK = '{$tinggi}'");
			$sql = mysqli_query($this->Conn, "SELECT CPM_HARGA,CPM_SATUAN,CPM_OPTION from PATDA_REKLAME_TARIF_NJOPR WHERE CPM_OPTION='{$cmp_option}' AND CPM_REKENING='{$kdrek}' AND ('$tinggii' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)");

			// $sql = "SELECT CPM_HARGA, CPM_SATUAN FROM PATDA_REKLAME_TARIF_NJOPR WHERE CPM_OPTION='{$cmp_option}' AND CPM_REKENING='{$kdrek}' AND ('$tinggii' BETWEEN CPM_TINGGI_MIN AND CPM_TINGGI_MAX)";
			// var_dump($sql);
			// die;
			$data_njopr = mysqli_fetch_object($sql);
			$satuan = (isset($data_njopr->CPM_SATUAN) && !empty($data_njopr->CPM_SATUAN)) ? str_replace('2', '<sup>2</sup>', $data_njopr->CPM_SATUAN) : $satuan;
			$harga_dasar->ketinggian = $data_njopr->CPM_HARGA;
			$harga_dasar->option = $data_njopr->CPM_OPTION;
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
			$rumus = "NJOPR = (Ukuran Reklame x Harga Dasar Ukuran Reklame) + (Ketinggian Reklame x Harga Dasar Ketinggian Reklame) x Durasi<br>
					NSPR = (NFR + NFJ + NSP) x Harga Dasar NSPR <br>
					NSR = NJOPR + NSPR<br>
					Total Pajak = NSR x Tarif Pajak";

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
				NSR = NJOPR + (NSL x NJOPR)<br>
				Pajak Terpasangan = NSR x Tarif x Durasi x Lama Pemasangan (1 Tahun)<br>
				Total Pajak = Pajak Terpasangan x Luas x Durasi Op x Jumlah Unit";

				$total_nspr = ($NFR + $NFJ + $NSP) * $harga_dasar->nspr;

				$total_njopr = $harga_dasar->ketinggian;

				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);

				$hitung_nsr = "<b>NSR</b><br>
					=  NJOPR + (NSL x NJOPR)<br>
					= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
					= Rp. " . number_format($total_nsr, 2);
				$total_terpasang = $total_nsr * (25 / 100) * $durasi_hari;
				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Durasi x Lama Pemasangan (1 Tahun)<br>
					= " . number_format($total_nsr) . " x " . number_format(25) . "% x 1 x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total_terpasang);

				$total_pajak = $total_terpasang * $luas * (int)$jam * 60 * (int)$jumlah;
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
			} elseif ($kdrek == '4.1.01.09.01.002') { // neon
				// Formula Des 2022
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
					NSR = NJOPR + (NSL x NJOPR)<br>
					Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Tahun)<br>
					Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit";

				$harga_ketinggian = $total_njopr = $harga_dasar->ketinggian;
				$total_nsr = $total_njopr + (($tarif / 100) * $total_njopr);
				$subTotal_nsr = $total_nsr;
				// var_dump($bangunan);
				// die;
				$hitung_nsr = "<b>NSR</b><br>
				=  NJOPR + (NSL x NJOPR)<br>
				= " . number_format($total_njopr) . " + (" . number_format($tarif) . "% x " . number_format($total_njopr) . ")<br>
				= Rp. " . number_format($subTotal_nsr, 2, '.', ',');

				$total_terpasang = $subTotal_nsr * (25 / 100) * $durasi_hari;
				$subTotal_terpasang = $total_terpasang;

				if ($kdrek === '4.1.01.09.01.005') $total_terpasang = $total_nsr * (25 / 100) * 1; // reklame Kain

				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
				= NSR x Tarif x Lama Pemasangan (1 Tahun)<br>
				= " . number_format($subTotal_nsr, 2, '.', ',') . " x " . number_format(25) . "% x " . number_format($durasi_hari) . "<br>
				= Rp. " .  number_format($subTotal_terpasang);


				$total_pajak = $subTotal_terpasang * $luas * (int)$jumlah;
				$subTotal_pajak = round($total_pajak);

				$hitung_total = "<br><br><b>Total Pajak</b><br>
				= Pajak Terpasangan x Luas x Jumlah Unit<br>
				= " . number_format($subTotal_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
				= Rp. " . number_format($subTotal_pajak);

				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;

				// var_dump($subTotal_nsr);
				// die;
			} elseif ($kdrek == '4.1.01.09.01.001') { // billboard
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
				$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
					NSR = NJOPR + (NSL x NJOPR)<br>
					Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 Tahun)<br>
					Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit";

				if ($kdrek === '4.1.01.09.01.005' || $kdrek === '4.1.01.09.01.003') {  // reklame Kain atau banner
					$rumus = "NJOPR = Harga Dasar di Ketinggian<br>
						NSR = NJOPR + (NSL x NJOPR)<br>
						Pajak Terpasangan = NSR x Tarif Pajak x Lama Pemasangan (1 hari)<br>
						Total Pajak = Pajak Terpasangan x Luas x Jumlah Unit x Lama Pemasangan";
				}

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
				$subTotal_terpasang = $total_terpasang;


				if ($kdrek === '4.1.01.09.01.005' || $kdrek === '4.1.01.09.01.003') $total_terpasang = $total_nsr * (25 / 100) * 1; // reklame Kain / banner


				$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Lama Pemasangan (1 hari)<br>
					= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x " . number_format($durasi_hari) . "<br>
					= Rp. " .  number_format($total_terpasang, 2, '.', ',');

				if ($kdrek === '4.1.01.09.01.006') { // bando
					$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Lama Pemasangan (1 Tahun)<br>
					= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x " . number_format($durasi_hari) . "<br>
					= Rp. " .  number_format(floor($subTotal_terpasang));
				}

				if ($kdrek === '4.1.01.09.01.003') { // banner
					$hitung_pajak = "<br><b>Pajak Terpasangan</b><br>
					= NSR x Tarif x Lama Pemasangan (1 hari)<br>
					= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x " . 1 . "<br>
					= Rp. " .  number_format($total_terpasang, 2, '.', ',');
				}
				if ($kdrek === '4.1.01.09.01.005') { // reklame Kain
					$hitung_pajak = "<br><b>Pajak Reklame/M2/hari</b><br>
						= NSR x Tarif x Lama Pemasangan (1 hari)<br>
						= " . number_format($subTotal_nsr) . " x " . number_format(25) . "% x 1<br>
						= Rp. " . number_format($total_terpasang);
				}

				$total_pajak = $subTotal_terpasang * $luas * (int)$jumlah;
				$subTotal_pajak = round($total_pajak);

				if ($kdrek === '4.1.01.09.01.005') $subTotal_pajak = (int)$total_terpasang * $luas * (int)$jumlah * (int)$durasi_hari; // reklame Kain

				$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang, 2, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
					= Rp. " . number_format($subTotal_pajak);


				if ($kdrek === '4.1.01.09.01.006') { // bando
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
					= Rp. " . number_format(round($subTotal_pajak, -2));
				}


				if ($kdrek === '4.1.01.09.01.005' || $kdrek === '4.1.01.09.01.003') { // reklame Kain
					$hitung_total = "<br><br><b>Total Pajak</b><br>
								= Pajak Terpasangan x Luas x Jumlah Unit x Lama Pemasangan<br>
								= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
								= Rp. " . number_format($subTotal_pajak);
				}
				$hitung_total = $hitung_pajak . $hitung_total;
				$total = $total_pajak;
			}

			$pokok = $total;

			if ($gedung == 'DALAM') {
				$ttotal = $total;
				$total = $total * 0.35;
				// $total_alkohol_gedung = $total + 
				if ($alkohol_rokok) {
					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " x 50%<br>" : "";
					// $alkohol_total = $total * 0.5;
					$total = $total + ($total * 0.5);
				}



				if ($kdrek === '4.1.01.09.10') { // wait
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit x Dihitung 50% dari nilai perhitungan tarif pajak Billboard<br>
						= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . " x " . " 50% " . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek === '4.1.01.09.08') { // Slide/Film 
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Detik X Jumlah Unit x Lama Pemasangan<br>
						= " . (float)$total_terpasang . " x " . $luas . " x " . number_format((int)$jam * 60) . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.07') { // Suara 
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= NSR x Tarif x Durasi x Lama Pemasangan<br>
						= " . (float)$total_nsr . " x " . $tarif . "% x " . number_format((int)$jam) . " x " . number_format($durasi_hari) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.11') { // prgan tidak permanen
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit<br>
						= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.004') { // Vidiotron/megatron
					$hitung_total .= "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Durasi Op x Jumlah Unit<br>
						= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jam * 60) . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.05' || $kdrek == '4.1.01.09.06') { // Udara dan Apung
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Jumlah Unit x Lama Pemasangan<br>
						= " . number_format($total_terpasang) . " x " . number_format($jumlah) . " x " . (int)$durasi_hari . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.04') { // Reklame Berjalan
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit<br>
						= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.002') { // neon
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit<br>
						= " . number_format($subTotal_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.001') { // billboard
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit<br>
						= " . number_format($subTotal_terpasang, 2, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek === '4.1.01.09.01.006') { //bando
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit<br>
						= " . number_format($subTotal_terpasang, -2) . " x " . $luas . " x " . number_format($jumlah) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format(round($total, -2));
				} else if ($kdrek === '4.1.01.09.01.005' || $kdrek === '4.1.01.09.01.003') { //kain & banner
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= Pajak Terpasangan x Luas x Jumlah Unit x Lama Pemasangan<br>
						= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.07') {
					$hitung_total = "<br><br><b>Total Pajak</b><br>
						= NSR x Tarif x Durasi x Lama Pemasangan<br>
						= " . (float)$total_nsr . " x " . 25 . "% x " . number_format((int)$jam) . " x " . number_format($durasi_hari) . "<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				} else {
					$hitung_total = "<b>Total Pajak</b><br>
						= NSR x Tarif Pajak<br>
						= " . number_format($subTotal_terpasang) . " x " . number_format($tarif) . "%<br>
						= " . number_format($ttotal) . " x 35%<br>
						{$hitung_alkohol_rokok}
						= Rp. " . number_format($total);
				}
			} else {
				if ($alkohol_rokok) {
					$hitung_alkohol_rokok = $alkohol_rokok ? "= " . number_format($total) . " x 50%<br>" : "";
					$total = $total + ($pokok * 0.5);
				}

				if ($kdrek === '4.1.01.09.10') { // wait
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit x Dihitung 50% dari nilai perhitungan tarif pajak Billboard<br>
					= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . " x " . " 50% " . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek === '4.1.01.09.08') { // Slide/Film 
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Detik X Jumlah Unit x Lama Pemasangan<br>
					= " . (float)$total_terpasang . " x " . $luas . " x " . number_format((int)$jam * 60) . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.07') { // Suara 
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= NSR x Tarif x Durasi x Lama Pemasangan<br>
					= " . (float)$total_nsr . " x " . $tarif . "% x " . number_format((int)$jam) . " x " . number_format($durasi_hari) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.11') { // prgan tidak permanen
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.004') { // Vidiotron/megatron
					$hitung_total .= "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Durasi Op x Jumlah Unit<br>
					= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jam * 60) . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.05' || $kdrek == '4.1.01.09.06') { // Udara dan Apung
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Jumlah Unit x Lama Pemasangan<br>
					= " . number_format($total_terpasang) . " x " . number_format($jumlah) . " x " . (int)$durasi_hari . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.04') { // Reklame Berjalan
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.002') { // neon
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang) . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.01.001') { // billboard
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang, 2, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek === '4.1.01.09.01.006') { //bando
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang, -2) . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format(round($total, -2));
				} else if ($kdrek === '4.1.01.09.01.005' || $kdrek === '4.1.01.09.01.003') { //kain & banner
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit x Lama Pemasangan<br>
					= " . number_format($total_terpasang) . " x " . $luas . " x " . number_format($jumlah) . " x " . number_format($durasi_hari) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);
				} else if ($kdrek == '4.1.01.09.07') {
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= NSR x Tarif x Durasi x Lama Pemasangan<br>
					= " . (float)$total_nsr . " x " . 25 . "% x " . number_format((int)$jam) . " x " . number_format($durasi_hari) . "<br>
					= Rp. " . number_format($total);
				} else {
					$hitung_total = "<br><br><b>Total Pajak</b><br>
					= Pajak Terpasangan x Luas x Jumlah Unit<br>
					= " . number_format($subTotal_terpasang, 0, '.', ',') . " x " . $luas . " x " . number_format($jumlah) . "<br>
					{$hitung_alkohol_rokok}
					= Rp. " . number_format($total);

					// = NSR x Muka x Tarif Pajak<br>
					// = " . number_format($subTotal_terpasang) . " x " . number_format($sisi) . " x " . number_format($tarif) . "%<br>
					// {$hitung_alkohol_rokok}
					// = Rp. " . number_format($sisi * $total);
				}
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

		$response['harga'] = $subTotal_nsr;
		$response['total'] = $total;
		$response['html'] = $html;
		$response['tarif'] = $tarif;
		$response['harga_ketinggian'] = $total_njopr;
		$response['rumus_hitung'] = $rumus . $hitung;
		$response['lokasi_reklame'] = $locReklame;
		$response['harga_contoh'] = $subTotal_nsr;

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
                    CPM_MASA_PAJAK1, CPM_MASA_PAJAK2, CPM_SK_DISCOUNT, CPM_DISCOUNT, CPM_TYPE_PAJAK, CPM_DENDA_TERLAMBAT_LAP, CPM_PIUTANG)
                    VALUES ( '%s','%s','%s',%f,%f,%f,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%f,%d,%f,'%s')",
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
				$jln5 .= "<option value=''>Pilih Jalan</option>";
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
			<th colspan="2">Dimensi Reklame</th>
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
			<td align="left" valign="top">Ketinggian<b class="isi">*</b></td>
					<td align="center" valign="top">
					<select style="width:150px;height:30px;" class="form-control"  id="CPM_ATR_TINGGI-' . $no . '" onkeyup="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TINGGI][]">' . $opt_tinggi . '</select>

			</td>
		</tr>
		<tr>
        <td>Jenis Waktu Pemakaian</td>
        <td>
            <select class="form-control" style="height:30px;" id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">' . $opt_type_masa . ' hidden</select>

        </td> 
			<td class="BERADA-' . $no . '" align="left" valign="top">Berada di <b class="isi">*</b></td>
			<td class="BERADA-' . $no . '" align="left" valign="top" colspan="3">
				
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][' . ($no - 1) . ']" class="CPM_BANGUNAN-' . $no . '" value="TANAH" ' . ($atr['CPM_ATR_BANGUNAN'] == 'TANAH' ? ' checked' : '') . '  onclick="hitungDetail(' . $no . ')" /> DI ATAS TANAH</label> &nbsp;
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][' . ($no - 1) . ']" class="CPM_BANGUNAN-' . $no . '" value="BANGUNAN" ' . ($atr['CPM_ATR_BANGUNAN'] == 'BANGUNAN' ? ' checked' : '') . ' onclick="hitungDetail(' . $no . ')" />  DIATAS GEDUNG / BANGUNAN</label>
			
			</td>
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
		<td>Lokasi Jalan </td>
		<td>
			<select class="form-control CPM_ATR_JALAN_TYPE" style="height: 30px;" id="CPM_ATR_JALAN_TYPE-' . $no . '"
				name="PAJAK_ATR[CPM_ATR_JALAN_TYPE][]" onchange="hitungDetail(' . $no . ')">' . $jln2 . '</select>
		</td>
		<td colspan="2">
			Alkohol/Rokok <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" onclick="hitungDetail(' . $no . ')" /> Ya</label> &nbsp;
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" onclick="hitungDetail(' . $no . ')" /> Tidak</label>
		</td>
		<td colspan="2">
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][' . ($no - 1) . ']" class="CPM_GEDUNG-' . $no . '" value="LUAR" onclick="hitungDetail(' . $no . ')"  /> Luar Gedung</label> &nbsp;
			<label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][' . ($no - 1) . ']" class="CPM_GEDUNG-' . $no . '" value="DALAM" onclick="hitungDetail(' . $no . ')" /> Dalam Gedung</label>
		</td>

		</tr>
		
		
		<tr>
		<td>Lokasi Reklame</td>
			<td>
			<select class="form-control CPM_ATR_JALAN" style="height: 30px;" id="CPM_ATR_JALAN-' . $no . '"
				name="PAJAK_ATR[CPM_ATR_JALAN][]" onchange="hitungDetail(' . $no . ')">' . $jln5 . '</select>
		<tr>
		
		<tr>
			<td>Biaya Tarif Pajak</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_BIAYA][]" style="width: 260px;" placeholder="Biaya Tarif Pajak" type="text" class="number" id="CPM_ATR_BIAYA-' . $no . '" readonly />
			</td>
            <td align="left" colspan="4" rowspan="6" valign="top">
					<div id="area_perhitungan-' . $no . '"></div>	
				</td>
		</tr>

        <tr>
			<td>Harga Dasar Ketinggian</td>
			<td>
				<input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][]" style="width: 260px;" placeholder="Biaya Harga Dasar" type="text" class="number" id="CPM_ATR_HARGA_DASAR_TIN-' . $no . '" readonly value="' . $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_TIN'] . '" ' . ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) . ' ? "" : "readonly";
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
