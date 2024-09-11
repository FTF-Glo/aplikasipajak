<?php
date_default_timezone_set('Asia/Jakarta');

// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class Dashboard_new extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION['user_name'])) {
			redirect("index.php/Auth");
		}
	}
	public function index()
	{
		$this->load->view('indexNew');
	}
	public function percobaan()
	{
		$sembilan_pajak = $this->getdata_9pajak_get('2023','getJson');
		var_dump($sembilan_pajak);
	}

	public function headtbl()
	{
		$html = '';
		$tahun = $this->input->post("tahun");
		$html .= "<tr style=\"text-align: center;background-color: #4682B4\">";
		$html .= "<th style=\"width: 5%;\">No.</th><th style=\"width: 35%;\">Uraian</th><th>Target</th>";
		//$monthnow = date('m');
		$monthnow = ($tahun != date('Y')) ? 12 : (int)date('m');
		$month = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agust', 'Sept', 'Okt', 'Nov', 'Des'];
		for ($i = 0; $i < $monthnow; $i++) {
			$html .= "<th>" . $month[$i] . "</th>";
		}
		$no = 1;
		$html .= "<th>Jumlah</th><th>Persen</th></tr><tr style=\"text-align: center;background-color: lightblue\">";
		$html .= "<td>(" . $no++ . ")</td><td>(" . $no++ . ")</td><td>(" . $no++ . ")</td>";

		for ($i = 0; $i < $monthnow; $i++) {
			$html .= "<td>(" . $no++ . ")</td>";
		}

		$html .= "<td>(" . $no++ . ")</td><td>(" . $no++ . ")</td></tr>";


		$data = [
			'headtbl' => $html,
		];
		die(json_encode($data));
	}

	public function getdataapi()
	{
		//$tahun = date("Y");
		$tahun = $this->input->post("tahun");
		$rangking2 = $this->input->post("rangking2");
		$mode = $this->input->post("mode");

		$sembilan_pajak = $this->getdata_9pajak_get($tahun, $mode);
		$bphtb = $this->getdata_bphtb_get($tahun, $mode);
		$pbb = $this->getdata_pbb_get($tahun, $mode);
		// var_dump($pbb);
		// die;
		// $walet = $this->sarang_walet_get($tahun);
		$html = '';
		$no = 0;
		$alltarget = 0;
		$allbulanlalu = 0;
		$allbulanini = 0;
		$alldendabulanlalu = 0;
		$alldendabulanini = 0;

		$pbballdendabulanlalu = 0;
		$pbballdendabulanini = 0;

		$dataFilter2 = [];
		//$monthnow = date('m');
		$monthnow = ($tahun != date("Y")) ? 12 : (int)date('m');
		$allperbulan = [
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0,
			10 => 0,
			11 => 0,
			12 => 0,
		];
		$dendamonth9pajak = [
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0,
			10 => 0,
			11 => 0,
			12 => 0,
		];
		if (!empty($sembilan_pajak)) {
			foreach ($sembilan_pajak as $ket => $value) {
				$no++;

				$alltarget += (float)$value->target;
				$allbulanlalu += (float)$value->bulan_lalu;
				$allbulanini += (float)$value->bulan_ini;
				$alldendabulanlalu += (float)$value->denda_bulan_lalu;
				$alldendabulanini += (float)$value->denda_bulan_ini;

				$html .= "<tr>";

				// foreach ($value as $key) {
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>" . number_format((float)$value->target, 0, ',', '.') . "</td>";
				$detailmonth9pajak = $value->detailmonth;
				$detaildendamonth9pajak = $value->detaildendamonth;
				for ($i = 1; $i <= $monthnow; $i++) {
					$pajakVal = isset($detailmonth9pajak->$i) ? (float)$detailmonth9pajak->$i : 0;
					$html .= "<td align='right'>" . number_format($pajakVal, 0, ',', '.') . "</td>";
					$allperbulan[$i] += $pajakVal;
					$dendamonth9pajak[$i] += $dendamonth9pajak[$i] + (isset($detaildendamonth9pajak->$i) ? (float)$detaildendamonth9pajak->$i : 0);
				}
				$jumlah = $value->bulan_lalu + $value->bulan_ini;
				$html .= "<td align='right'>" . number_format($jumlah, 0, ',', '.') . "</td>";
				if ($value->target == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = $jumlah / (float)$value->target  * 100;
				}
				$html .= "<td align='right'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				// }
				$html .= "</tr>";

				// $x = sprintf("%06d", str_replace('.','',number_format($value->target, 2, '.', '')));

				// $dataFilter2[$x] = $html;
			}
		}
		if (isset($bphtb)) {
			foreach ($bphtb as $ket => $value) {
				$no++;

				$alltarget += (float)$value['target'];
				$allbulanlalu += (float)$value['bulan_lalu'];
				$allbulanini += (float)$value['bulan_ini'];
				$html .= "<tr>";
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>" . number_format((float)$value['target'], 0, ',', '.') . "</td>";
				$detailmonthBPHTB = $value['detailmonth'];
				for($i = 1; $i <= $monthnow; $i++) {
					$bphtbVal = isset($detailmonthBPHTB[$i]) ? (float)$detailmonthBPHTB[$i] : 0;
					$html .= "<td align='right'>" . number_format($bphtbVal, 0, ',', '.') . "</td>";
					$allperbulan[$i] = $allperbulan[$i] + $bphtbVal;
				}
				$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right'>" . number_format($jumlah, 0, ',', '.') . "</td>";
				if ($value['target'] == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = ($jumlah / (float)$value['target']) * 100;
				}
				$html .= "<td align='right'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				// }
				$html .= "</tr>";

				// $x = sprintf("%06d", str_replace('.','',number_format($value['target'], 2, '.', '')));

				// $dataFilter2[$x] = $html;
			}
		}
		if (isset($pbb)) {
			foreach ($pbb as $ket => $value) {
				$no++;

				$alltarget += (float)$value['target'];
				$allbulanlalu += (float)$value['bulan_lalu'];
				$allbulanini += (float)$value['bulan_ini'];
				$pbballdendabulanlalu += (float)$value['denda_bulan_lalu'];
				$pbballdendabulanini += (float)$value['denda_bulan_ini'];
				$html .= "<tr>";
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>" . number_format((float)$value['target'], 0, ',', '.') . "</td>";
				$detailmonthPBB = $value['detailmonth'];
				$detaildendamonth = $value['detaildendamonth'];
				for ($i = 1; $i <= $monthnow; $i++) {
					$pbbVal = isset($detailmonthPBB[$i]) ? (float)$detailmonthPBB[$i] : 0;
					$html .= "<td align='right'>" . number_format($pbbVal, 0, ',', '.') . "</td>";
					$allperbulan[$i] = $allperbulan[$i] + $pbbVal;
				}
				$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right'>" . number_format($jumlah, 0, ',', '.') . "</td>";
				$target = $value['target'] != '' ? (float)$value['target'] : 0;
				if ($target == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = ($jumlah / (float)$target) * 100;
					// $total_persen = (float)$value['target'] / (float)$jumlah * 100;
				}
				$html .= "<td align='right'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				// }
				$html .= "</tr>";

				// $x = sprintf("%06d", str_replace('.','',number_format($value['target'], 2, '.', '')));

				// $dataFilter2[$x] = $html;
			}
		}

		if($rangking2=='asc'){
			ksort($dataFilter2);
		}else{
			krsort($dataFilter2);
		}
		
		foreach ($dataFilter2 as $row) {
			$html .= $row;
		}
		// var_dump($allbulanini, $allbulanlalu);
		// die;
		$html .= "<tr style=\"background-color:#4682B4\">";
		$html .= "<td></td>";
		$html .= "<td><b>TOTAL PENDAPATAN</b></td>";
		$html .= "<td align='right'><b>" . number_format($alltarget, 0, ',', '.') . "</b></td>";
		$jumlah = $allbulanlalu + $allbulanini;

		for ($i = 1; $i <= $monthnow; $i++) {
			$html .= "<td align='right'>" . number_format($allperbulan[$i], 0, ',', '.') . "</td>";
		}
		$html .= "<td align='right'><b>" . number_format($jumlah, 0, ',', '.') . "</td>";
		if ($alltarget == 0 || $jumlah == 0) {
			$total_persen = 0;
		} else {
			// $total_persen = ($alltarget / $jumlah) * 100;
			$total_persen = $jumlah / $alltarget * 100;
			$total_persen = str_replace('.',',',(float)round($total_persen, 2));
		}
		$alldenda = $alldendabulanlalu + $alldendabulanini;
		$html .= "<td align='right'><b>" . $total_persen . "%</b></td>";
		// }
		$html .= "<tr style=\"background-color: orange\">";
		$html .= "<td></td>";
		$html .= "<td colspan=2><b>DENDA 9PAJAK</b></td>";

		for ($i = 1; $i <= $monthnow; $i++) {
			$denda9pajakVal = isset($dendamonth9pajak[$i]) ? (float)$dendamonth9pajak[$i] : 0;
			$html .= "<td align='right'>" . number_format($denda9pajakVal, 0, ',', '.') . "</td>";
		}
		$html .= "<td align='right'><b>" . number_format($alldenda, 0, ',', '.') . "</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

		$pbballdenda = $pbballdendabulanlalu + $pbballdendabulanini;
		$html .= "<tr style=\"background-color: orange\">";
		$html .= "<td></td>";
		$html .= "<td colspan=2><b>DENDA PAJAK PBB</b></td>";

		for ($i = 1; $i <= $monthnow; $i++) {
			$dendapbbVal = isset($detaildendamonth[$i]) ? (float)$detaildendamonth[$i] : 0;
			$html .= "<td align='right'>" . number_format($dendapbbVal, 0, ',', '.') . "</td>";
		}
		$html .= "<td align='right'><b>" . number_format($pbballdenda, 0, ',', '.') . "</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

		$data = [
			'table'			=> $html,
			'persentage'	=> $total_persen,
			'bulan_lalu' 	=> 'Jan',
			'bulan_sampai'	=> date('M', strtotime('-1 month')),
			'bulan_ini'		=> date('M'),
		];
		// var_dump($jumlah);
		// die;
		if($mode=='getJson') die(json_encode($data));
		die('OK');
	}

	public function getdata_9pajak_get($tahun = null, $mode='getJson')
	{
		if($mode=='getJson'){
			$isFile = file_exists("9pajak_$tahun.json");
			if($isFile){
				$getJson = file_get_contents("9pajak_$tahun.json");
				return json_decode($getJson);
			}
		}

		$month = ($tahun != date("Y")) ? "'12'" : "MONTH(CURRENT_DATE)";
		$bln = ($tahun!=date("Y")) ? 12 : (int)date("m");

		$airbawahtanah = $this->db->query(" SELECT 
												MONTH(c.payment_paid) AS BLN,
												SUM(c.patda_total_bayar) AS NILAI,
												SUM(c.patda_denda) AS DENDA
											FROM simpatda_gw c
											INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
											WHERE 
												PAYMENT_FLAG = 1 
												AND b.`id_sw` = 1
												AND YEAR(c.payment_paid) = '{$tahun}'
												AND MONTH(c.payment_paid) <= {$month}
											GROUP BY MONTH(c.payment_paid)
											
											UNION ALL 

											SELECT 
												'target' AS BLN, 
												cpm_jumlah AS NILAI, 
												'' AS DENDA 
											FROM patda_target_pajak 
											WHERE 
												cpm_jenis_pajak = 1 
												AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];
		
		foreach ($airbawahtanah as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$airbawahtanah = (object)[];
		$airbawahtanah->target = (float)$target;
		$airbawahtanah->bulan_lalu = $bulan_lalu;
		$airbawahtanah->denda_bulan_lalu = $denda_bulan_lalu ;
		$airbawahtanah->bulan_ini = $bulan_ini;
		$airbawahtanah->denda_bulan_ini = $denda_bulan_ini;
		$airbawahtanah->detailmonth = $detailmonth;
		$airbawahtanah->detaildendamonth = $detaildendamonth;

		$hiburan =$this->db->query("SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 2
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 2 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($hiburan as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$hiburan = (object)[];
		$hiburan->target = (float)$target;
		$hiburan->bulan_lalu = $bulan_lalu;
		$hiburan->denda_bulan_lalu = $denda_bulan_lalu ;
		$hiburan->bulan_ini = $bulan_ini;
		$hiburan->denda_bulan_ini = $denda_bulan_ini;
		$hiburan->detailmonth = $detailmonth;
		$hiburan->detaildendamonth = $detaildendamonth;

		$hotel = $this->db->query(" SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 3
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 3 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($hotel as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$hotel = (object)[];
		$hotel->target = (float)$target;
		$hotel->bulan_lalu = $bulan_lalu;
		$hotel->denda_bulan_lalu = $denda_bulan_lalu ;
		$hotel->bulan_ini = $bulan_ini;
		$hotel->denda_bulan_ini = $denda_bulan_ini;
		$hotel->detailmonth = $detailmonth;
		$hotel->detaildendamonth = $detaildendamonth;

		$minerba = $this->db->query(" SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 4
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 4 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($minerba as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$minerba = (object)[];
		$minerba->target = (float)$target;
		$minerba->bulan_lalu = $bulan_lalu;
		$minerba->denda_bulan_lalu = $denda_bulan_lalu ;
		$minerba->bulan_ini = $bulan_ini;
		$minerba->denda_bulan_ini = $denda_bulan_ini;
		$minerba->detailmonth = $detailmonth;
		$minerba->detaildendamonth = $detaildendamonth;

		$parkir = $this->db->query("SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 5
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 5 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($parkir as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$parkir = (object)[];
		$parkir->target = (float)$target;
		$parkir->bulan_lalu = $bulan_lalu;
		$parkir->denda_bulan_lalu = $denda_bulan_lalu ;
		$parkir->bulan_ini = $bulan_ini;
		$parkir->denda_bulan_ini = $denda_bulan_ini;
		$parkir->detailmonth = $detailmonth;
		$parkir->detaildendamonth = $detaildendamonth;
		
		$jalan = $this->db->query(" SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 6
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 6 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($jalan as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$jalan = (object)[];
		$jalan->target = (float)$target;
		$jalan->bulan_lalu = $bulan_lalu;
		$jalan->denda_bulan_lalu = $denda_bulan_lalu ;
		$jalan->bulan_ini = $bulan_ini;
		$jalan->denda_bulan_ini = $denda_bulan_ini;
		$jalan->detailmonth = $detailmonth;
		$jalan->detaildendamonth = $detaildendamonth;

		$reklame = $this->db->query(" SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 7
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 7 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($reklame as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$reklame = (object)[];
		$reklame->target = (float)$target;
		$reklame->bulan_lalu = $bulan_lalu;
		$reklame->denda_bulan_lalu = $denda_bulan_lalu ;
		$reklame->bulan_ini = $bulan_ini;
		$reklame->denda_bulan_ini = $denda_bulan_ini;
		$reklame->detailmonth = $detailmonth;
		$reklame->detaildendamonth = $detaildendamonth;

		$restoran = $this->db->query(" SELECT 
										MONTH(c.payment_paid) AS BLN,
										SUM(c.patda_total_bayar) AS NILAI,
										SUM(c.patda_denda) AS DENDA
									FROM simpatda_gw c
									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
									WHERE 
										PAYMENT_FLAG = 1 
										AND b.`id_sw` = 8
										AND YEAR(c.payment_paid) = '{$tahun}'
										AND MONTH(c.payment_paid) <= {$month}
									GROUP BY MONTH(c.payment_paid)
									
									UNION ALL 

									SELECT 
										'target' AS BLN, 
										cpm_jumlah AS NILAI, 
										'' AS DENDA 
									FROM patda_target_pajak 
									WHERE 
										cpm_jenis_pajak = 8 
										AND CPM_TAHUN = '{$tahun}'")->result();
		$target = 0;
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];

		foreach ($restoran as $row) {
			if(is_numeric($row->BLN)){
				if($row->BLN==$bln){
					$bulan_ini = $bulan_ini + $row->NILAI;
					$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
				}else{
					$bulan_lalu = $bulan_lalu + $row->NILAI;
					$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
				}
				$detailmonth[$row->BLN] = (float)$row->NILAI;
				$detaildendamonth[$row->BLN] = (float)$row->DENDA;
			}else{
				$target = $row->NILAI;
			}
		}

		$restoran = (object)[];
		$restoran->target = (float)$target;
		$restoran->bulan_lalu = $bulan_lalu;
		$restoran->denda_bulan_lalu = $denda_bulan_lalu ;
		$restoran->bulan_ini = $bulan_ini;
		$restoran->denda_bulan_ini = $denda_bulan_ini;
		$restoran->detailmonth = $detailmonth;
		$restoran->detaildendamonth = $detaildendamonth;

		// 9 pajak end
		$result = [
			'HOTEL'			=>	$hotel,
			'RESTORAN'		=>	$restoran,
			'HIBURAN'		=>	$hiburan,
			'REKLAME'		=>	$reklame,
			'PENERANGAN JALAN'=> $jalan,
			'PARKIR'		=>	$parkir,
			'AIR BAWAH TANAH'=>	$airbawahtanah,
			'MINERBA'		=>	$minerba,
		];

		$myfile = fopen("9pajak_$tahun.json", "w") or die("Unable to open file ringkas_9pajak_$tahun.json !");
		$json = json_encode($result);
		fwrite($myfile, $json);
		fclose($myfile);

		// $result = $pajak;
		return $result;
	}

	public function sarang_walet_get($tahun = null)
	{


		$month = ($tahun != date("Y")) ? 12 : "MONTH(CURRENT_DATE)";
		$walet = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 9
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < {$month}) AS bulan_lalu,
										(SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 9
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < {$month}) AS bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 9 AND CPM_TAHUN = '{$tahun}') AS target")
			->row();

		$result = ['SARANG WALET' => $walet];
		return $result;
	}

	public function getdata_bphtb_get($tahun = null, $mode='getJson')
	{
		if($mode=='getJson'){
			$isFile = file_exists("bphtb_$tahun.json");
			if($isFile){
				$getJson = file_get_contents("bphtb_$tahun.json");
				return json_decode($getJson,true);
			}
		}

		$this->db9gw_ssb = $this->load->database('gw_ssb', TRUE);
		$this->db9sw_ssb = $this->load->database('sw_ssb', TRUE);

		// bphtb
		$month = ($tahun != date("Y")) ? "'12'" : "MONTH(CURRENT_DATE)";
		$filterTarget = ($tahun != date("Y")) ? "JUMLAH_TARGET_" . $tahun : "JUMLAH_TARGET";
		
		$bphtb_total = $this->db9gw_ssb->query("SELECT 
													MONTH(payment_paid) AS BLN, 
													SUM(bphtb_collectible) AS DIBAYAR
												FROM ssb
												WHERE YEAR (payment_paid) = '{$tahun}'
													AND MONTH(payment_paid) <= {$month}
													AND payment_flag = 1
												GROUP BY MONTH(payment_paid);")->result();
		$bphtb_target=$this->db9sw_ssb->query(" SELECT CTR_AC_VALUE AS target 
												FROM central_app_config 
												WHERE CTR_AC_KEY ='{$filterTarget}';")->row();
		
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$detailmonth = [];
		$month = ($tahun!=date("Y")) ? 12 : (int)date("m");
		
		foreach ($bphtb_total as $row) {
			if($row->BLN==$month){
				$bulan_ini = $bulan_ini + $row->DIBAYAR;
			}else{
				$bulan_lalu = $bulan_lalu + $row->DIBAYAR;
			}
			$detailmonth[$row->BLN] = (float)$row->DIBAYAR;
		}

		$bphtb = [
			'target' => isset($bphtb_target->target) != '' ? (float)$bphtb_target->target : 0,
			'bulan_lalu' => $bulan_lalu,
			'bulan_ini' => $bulan_ini,
			'detailmonth' => $detailmonth
		];


		$result = ['BPHTB' => $bphtb];

		$myfile = fopen("bphtb_$tahun.json", "w") or die("Unable to open file detail_bphtb_$tahun.json !");
		$json = json_encode($result);
		fwrite($myfile, $json);
		fclose($myfile);

		return $result;
	}

	public function getdata_pbb_get($tahun = null, $mode='getJson')
	{
		if($mode=='getJson'){
			$isFile = file_exists("pbb_$tahun.json");
			if($isFile){
				$getJson = file_get_contents("pbb_$tahun.json");
				return json_decode($getJson,true);
			}
		}

		$this->dbgw_pbb = $this->load->database('gw_pbb', TRUE);
		$this->dbsw_pbb = $this->load->database('sw_pbb', TRUE);

		$month = ($tahun != date("Y")) ? "'12'" : "MONTH(CURRENT_DATE)";
		$filterTarget = ($tahun != date("Y")) ? "JUMLAH_TARGET_" . $tahun : "JUMLAH_TARGET";
		
		$pbb_total=$this->dbgw_pbb->query(" SELECT 
												MONTH(payment_paid) AS BLN,
												SUM(SPPT_PBB_HARUS_DIBAYAR) AS BAYAR, 
												SUM(PBB_DENDA) AS DENDA
											FROM pbb_sppt 
											WHERE 
												PAYMENT_FLAG = 1 AND
												YEAR(payment_paid) = '{$tahun}' AND
												MONTH(payment_paid) <= {$month}
											GROUP BY MONTH(payment_paid);")->result();
		$pbb_target=$this->dbsw_pbb->query("SELECT CTR_AC_VALUE AS target 
											FROM central_app_config 
											WHERE CTR_AC_KEY ='{$filterTarget}'")->row();
		$bulan_lalu = 0;
		$bulan_ini = 0;
		$denda_bulan_lalu = 0;
		$denda_bulan_ini = 0;
		$detailmonth = [];
		$detaildendamonth = [];
		$month = ($tahun!=date("Y")) ? 12 : (int)date("m");
		
		foreach ($pbb_total as $row) {
			if($row->BLN==$month){
				$bulan_ini = $bulan_ini + $row->BAYAR;
				$denda_bulan_ini = $denda_bulan_ini + $row->DENDA;
			}else{
				$bulan_lalu = $bulan_lalu + $row->BAYAR;
				$denda_bulan_lalu = $denda_bulan_lalu + $row->DENDA;
			}
			$detailmonth[$row->BLN] = (float)$row->BAYAR;
			$detaildendamonth[$row->BLN] = (float)$row->DENDA;
		}

		$pbb = [
			'target' => isset($pbb_target->target) != '' ? (float)$pbb_target->target : 0,
			'bulan_lalu' => $bulan_lalu,
			'bulan_ini' => $bulan_ini,
			'denda_bulan_lalu' => $denda_bulan_lalu,
			'denda_bulan_ini' => $denda_bulan_ini,
			'detailmonth' => $detailmonth,
			'detaildendamonth' => $detaildendamonth
		];

		$result = ['PBB' =>	$pbb];
		
		$myfile = fopen("pbb_$tahun.json", "w") or die("Unable to open file detail_pbb_$tahun.json !");
		$json = json_encode($result);
		fwrite($myfile, $json);
		fclose($myfile);

		return $result;
	}
}
