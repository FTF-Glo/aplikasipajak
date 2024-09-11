<?php
date_default_timezone_set('Asia/Jakarta');

// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class Dashboard extends CI_Controller
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
		$this->load->view('index');
	}

	public function percobaan()
	{
		$sembilan_pajak = $this->getdata_9pajak_get('2023','getJson');
		var_dump($sembilan_pajak);
	}

	public function getdataapi()
	{
		$tahun = $this->input->post("tahun");
		$rangking = $this->input->post("rangking");
		$mode = $this->input->post("mode");

		$sembilan_pajak = $this->getdata_9pajak_get($tahun,$mode);

		$bphtb = $this->getdata_bphtb_get($tahun,$mode);

		$pbb = $this->getdata_pbb_get($tahun,$mode);

		// var_dump($pbb);
		// die;

		// $walet = $this->sarang_walet_get($tahun);
		$no = 0;
		$alltarget = 0;
		$allbulanlalu = 0;
		$allbulanini = 0;
		$alldendabulanlalu = 0;
		$alldendabulanini = 0;

		$pbballdendabulanlalu = 0;
		$pbballdendabulanini = 0;

		$dataFilter = [];

		if (!empty($sembilan_pajak)) {
			$z = 0;
			foreach ($sembilan_pajak as $ket => $value) {
				$z++;
				$alltarget += intval($value->target);
				$allbulanlalu += intval($value->bulan_lalu);
				$allbulanini += intval($value->bulan_ini);
				$alldendabulanlalu += intval($value->denda_bulan_lalu);
				$alldendabulanini += intval($value->denda_bulan_ini);

				$html = "<tr>";

				// foreach ($value as $key) {
				$html .= "<td align='center'>{nomorurut}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>" . number_format(intval($value->target), 0, ',', '.') . "</td>";
				$html .= "<td align='right'>" . number_format(intval($value->bulan_lalu), 0, ',', '.') . "</td>";
				$html .= "<td align='right'>" . number_format(intval($value->bulan_ini), 0, ',', '.') . "</td>";
				$jumlah = $value->bulan_lalu + $value->bulan_ini;
				$html .= "<td align='right'>" . number_format(intval($jumlah), 0, ',', '.') . "</td>";
				if ($value->target == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = (float)$jumlah / (float)$value->target  * 100;
				}
				$html .= "<td align='right'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				// }
				$html .= "</tr>";

				$x = sprintf("%06d", str_replace('.', '', number_format(($value->target + $z), 2, '.', '')));

				$dataFilter[$x] = $html;
			}
		}
		if (isset($bphtb)) {
			foreach ($bphtb as $ket => $value) {

				$alltarget += intval($value['target']);
				$allbulanlalu += intval($value['bulan_lalu']);
				$allbulanini += intval($value['bulan_ini']);
				$html = "<tr>";
				$html .= "<td align='center'>{nomorurut}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>" . number_format(intval($value['target']), 0, ',', '.') . "</td>";
				$html .= "<td align='right'>" . number_format(intval($value['bulan_lalu']), 0, ',', '.') . "</td>";
				$html .= "<td align='right'>" . number_format(intval($value['bulan_ini']), 0, ',', '.') . "</td>";
				$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right'>" . number_format(intval($jumlah), 0, ',', '.') . "</td>";
				if ($value['target'] == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = intval($jumlah) / intval($value['target']) * 100;
				}
				$html .= "<td align='right'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				
				$html .= "</tr>";

				$x = sprintf("%06d", str_replace('.', '', number_format($value['target'], 2, '.', '')));

				$dataFilter[$x] = $html;
			}
		}
		if (isset($pbb)) {
			foreach ($pbb as $ket => $value) {

				$alltarget += intval($value['target']);
				$allbulanlalu += intval($value['bulan_lalu']);
				$allbulanini += intval($value['bulan_ini']);
				$pbballdendabulanlalu += intval($value['denda_bulan_lalu']);
				$pbballdendabulanini += intval($value['denda_bulan_ini']);
				$html = "<tr>";
				$html .= "<td align='center'>{nomorurut}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right' data-label='target'>" . number_format(intval($value['target']), 0, ',', '.') . "</td>";
				$html .= "<td align='right' data-label='bulan_lalu'>" . number_format(intval($value['bulan_lalu']), 0, ',', '.') . "</td>";
				$html .= "<td align='right' data-label='bulan_ini'>" . number_format(intval($value['bulan_ini']), 0, ',', '.') . "</td>";
				$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right' data-label='jumlah'>" . number_format(intval($jumlah), 0, ',', '.') . "</td>";
				$target = $value['target'] != '' ? (float)$value['target'] : 0;
				if ($target == 0 || $jumlah == 0) {
					$total_persen = 0;
				} else {
					$total_persen = (intval($jumlah) / intval($target)) * 100;
					// $total_persen = intval($value['target']) / intval($jumlah) * 100;
				}
				$html .= "<td align='right' data-label='total_persen'>" . str_replace('.',',',(float)round($total_persen, 2)) . "%</td>";
				// }
				$html .= "</tr>";

				$x = sprintf("%06d", str_replace('.', '', number_format($value['target'], 2, '.', '')));

				$dataFilter[$x] = $html;
			}
		}

		if ($rangking == 'asc') {
			ksort($dataFilter);
		} else {
			krsort($dataFilter);
		}

		foreach ($dataFilter as $key=>$row) {
			$no++;
			$dataFilter[$key] = str_replace("{nomorurut}",$no,$row);
		}

		$html = '';

		foreach ($dataFilter as $row) {
			$html .= $row;
		}

		$html .= "<tr style=\"background-color: #0C9463;\">";
		$html .= "<td></td>";
		$html .= "<td><b>TOTAL PENDAPATAN</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($alltarget), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($allbulanlalu), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($allbulanini), 0, ',', '.') . "</b></td>";

		$jumlah = $allbulanlalu + $allbulanini;
		$html .= "<td align='right'><b>" . number_format(intval($jumlah), 0, ',', '.') . "</td>";
		if ($alltarget == 0) {
			$total_persen = 0;
		} else {
			// $total_persen = (intval($alltarget) / intval($jumlah)) * 100;
			$total_persen = intval($jumlah) / intval($alltarget) * 100;
			$total_persen = str_replace('.',',',(float)round($total_persen, 2));
		}
		$html .= "<td align='right'><b>" . $total_persen . "%</b></td>";

		$alldenda = $alldendabulanlalu + $alldendabulanini;
		// }
		$html .= "<tr style=\"background-color: #FF8303;\">";
		$html .= "<td></td>";
		$html .= "<td colspan=\"2\"><b>DENDA 9PAJAK</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($alldendabulanlalu), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($alldendabulanini), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($alldenda), 0, ',', '.') . "</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

		$pbballdenda = $pbballdendabulanlalu + $pbballdendabulanini;
		// }
		$html .= "<tr style=\"background-color: #F88F01;\">";
		$html .= "<td></td>";
		$html .= "<td colspan=\"2\"><b>PAJAK PBB</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($pbballdendabulanlalu), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($pbballdendabulanini), 0, ',', '.') . "</b></td>";
		$html .= "<td align='right'><b>" . number_format(intval($pbballdenda), 0, ',', '.') . "</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

		$month = date('M', strtotime('-1 month'));

		$data = [
			'table'			=> $html,
			'persentage'	=> $total_persen . "%",
			'bulan_lalu' 	=> $month == 'Dec' ? ($tahun != date("Y") ? "Jan" : "-") : "Jan",
			'bulan_sampai'	=> $month == 'Dec' ? ($tahun != date("Y") ? "Nov" : "-") : $month,
			'bulan_ini'		=> ($tahun != date("Y") ? "Dec" : date('M')),
		];

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

		$myfile = fopen("bphtb_$tahun.json", "w") or die("Unable to open file ringkas_bphtb_$tahun.json !");
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

		// pbb
		$month = ($tahun != date("Y")) ? "'12'" : "MONTH(CURRENT_DATE)";
		$filterTarget = ($tahun != date("Y")) ? "JUMLAH_TARGET_" . $tahun : "JUMLAH_TARGET";
	
		$pbb_total = $this->dbgw_pbb->query("SELECT 
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

		// echo '<pre>';
		// print_r("<br>bulan_lalu ".$bulan_lalu);;
		// print_r("<br>bulan_ini ".$bulan_ini);
		// print_r("<br>denda_bulan_lalu ".$denda_bulan_lalu);;
		// print_r("<br>denda_bulan_ini ".$denda_bulan_ini);
		// exit;

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
		
		$myfile = fopen("pbb_$tahun.json", "w") or die("Unable to open file ringkas_pbb_$tahun.json !");
		$json = json_encode($result);
		fwrite($myfile, $json);
		fclose($myfile);

		return $result;
	}

	public function ipprotax_get()
	{
		$server = 'IPROTAX';
		$username = 'iprotax';
		$password = 'iprotax';
		$database = 'IPROTAX';
		$konek = mssqli_connect($server, $username, $password);
		if ($konek) {
			echo "Koneksi sukses ke server SQL Server<br />";
		} else {
			die("Koneksi Gagal");
		}
		// phpinfo();
		$this->ipprotax = $this->load->database('ipro', TRUE);
		$result = $this->ipprotax->query("SELECT (SELECT SUM(PBB_YG_HRS_DIBAYAR_SPPT) FROM IPROTAXPBB.SPPT 
															WHERE STATUS_PEMBAYARAN_SPPT=1
															AND THN_PAJAK_SPPT = YEAR(getdate())
															AND MONTH(TGL_TERBIT_SPPT) < MONTH(getdate())) as bulan_lalu,
												(SELECT SUM(PBB_YG_HRS_DIBAYAR_SPPT) FROM IPROTAXPBB.SPPT 
															WHERE STATUS_PEMBAYARAN_SPPT=1
															AND THN_PAJAK_SPPT = YEAR(getdate())
															AND MONTH(TGL_TERBIT_SPPT) = MONTH(getdate())) as bulan_ini
															")->result();
	}
}
