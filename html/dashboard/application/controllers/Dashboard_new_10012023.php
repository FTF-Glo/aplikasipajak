<?php
date_default_timezone_set('Asia/Jakarta');

// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class Dashboard_new extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		if(!isset($_SESSION['user_name'])){
			redirect("index.php/Auth");
		}
	}
    public function index(){
        $this->load->view('indexNew');
    }
    public function percobaan(){
    	$sembilan_pajak = $this->getdata_9pajak_get();
    	var_dump($sembilan_pajak);
    }
    public function getdataapi(){
		//$tahun = date("Y");
		$tahun = $this->input->post("tahun");
		
    	$sembilan_pajak = $this->getdata_9pajak_get($tahun);
    	$bphtb = $this->getdata_bphtb_get($tahun);
    	$pbb = $this->getdata_pbb_get($tahun);
    	$walet = $this->sarang_walet_get($tahun);
    	$html = '';
    	$no = 0;
    	$alltarget = 0;
    	$allbulanlalu = 0;
    	$allbulanini = 0;
    	$alldendabulanlalu = 0;
    	$alldendabulanini = 0;

    	$pbballdendabulanlalu = 0;
    	$pbballdendabulanini = 0;
		$monthnow = date('m');
		$jenis_sw = ['AIR BAWAH TANAH'=>1,
						'HIBURAN'=>2,
						'HOTEL'=>3,
						'MINERBA'=>4,
						'PARKIR'=>5,
						'PENERANGAN JALAN'=>6,
						'REKLAME'=>7,
						'RESTORAN'=>8,
						'Sarang Burung Walet'=>9];
		$allperbulan = [1=>0,
						2=>0,
						3=>0,
						4=>0,
						5=>0,
						6=>0,
						7=>0,
						8=>0,
						9=>0,
						10=>0,
						11=>0,
						12=>0,
					];
		$alldendaperbulan = [1=>0,
						2=>0,
						3=>0,
						4=>0,
						5=>0,
						6=>0,
						7=>0,
						8=>0,
						9=>0,
						10=>0,
						11=>0,
						12=>0,
					];
		$pbballdendaperbulan = [1=>0,
						2=>0,
						3=>0,
						4=>0,
						5=>0,
						6=>0,
						7=>0,
						8=>0,
						9=>0,
						10=>0,
						11=>0,
						12=>0,
					];
    	if (!empty($sembilan_pajak)) {
	    	foreach ($sembilan_pajak as $ket => $value) {
	    		$no++;

		    	$alltarget += intval($value->target);
		    	$allbulanlalu += intval($value->bulan_lalu);
		    	$allbulanini += intval($value->bulan_ini);
		    	$alldendabulanlalu += intval($value->denda_bulan_lalu);
		    	$alldendabulanini += intval($value->denda_bulan_ini);

	    		$html .= "<tr>";

	    		// foreach ($value as $key) {
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>".number_format(intval($value->target),0,',','.')."</td>";
				for ($i=1; $i <= $monthnow; $i++) { 
					$html .= "<td align='right'>".number_format(intval($this->permonth_pajak($jenis_sw[$ket],$i)),0,',','.')."</td>";
					$allperbulan[$i] += intval($this->permonth_pajak($jenis_sw[$ket],$i));
					$alldendaperbulan[$i] += intval($this->permonth_dendapajak($jenis_sw[$ket],$i));
				}
	    		$jumlah = $value->bulan_lalu + $value->bulan_ini;
				$html .= "<td align='right'>".number_format(intval($jumlah),0,',','.')."</td>";
				if ($value->target==0) {
					$total_persen=0;
				}
				else{
	    			$total_persen = intval($jumlah)/intval($value->target)  * 100;
				}
				$html .= "<td align='right'>".round($total_persen, 2)."%</td>";
	    		// }
	    		$html .= "</tr>";
	    	}
	    }
    	if (isset($bphtb)) {
	    	foreach ($bphtb as $ket => $value) {
	    		$no++;

		    	$alltarget += intval($value['target']);
		    	$allbulanlalu += intval($value['bulan_lalu']);
		    	$allbulanini += intval($value['bulan_ini']);
	    		$html .= "<tr>";
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>".number_format(intval($value['target']),0,',','.')."</td>";
				for ($i=1; $i <= $monthnow; $i++) { 
					$html .= "<td align='right'>".number_format(intval($this->permonth_bphtb($i)),0,',','.')."</td>";
					$allperbulan[$i] = $allperbulan[$i] + intval($this->permonth_bphtb($i));
				}
	    		$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right'>".number_format(intval($jumlah),0,',','.')."</td>";
				if ($value['target']==0) {
					$total_persen=0;
				}
				else{
	    			$total_persen = intval($jumlah) / intval($value['target']) * 100;
				}
				$html .= "<td align='right'>".number_format($total_persen, 2, '.', '')."%</td>";
	    		// }
	    		$html .= "</tr>";
	    	}
    	}
    	if (isset($pbb)) {
	    	foreach ($pbb as $ket => $value) {
	    		$no++;

		    	$alltarget += intval($value['target']);
		    	$allbulanlalu += intval($value['bulan_lalu']);
		    	$allbulanini += intval($value['bulan_ini']);
		    	$pbballdendabulanlalu += intval($value['denda_bulan_lalu']);
		    	$pbballdendabulanini += intval($value['denda_bulan_ini']);
	    		$html .= "<tr>";
				$html .= "<td align='center'>{$no}</td>";
				$html .= "<td>{$ket}</td>";
				$html .= "<td align='right'>".number_format(intval($value['target']),0,',','.')."</td>";
				for ($i=1; $i <= $monthnow; $i++) { 
					$html .= "<td align='right'>".number_format(intval($this->permonth_pbb($i)),0,',','.')."</td>";
					// $html .= "<td align='right'>".$i."</td>";
					$allperbulan[$i] = $allperbulan[$i] + intval($this->permonth_pbb($i));
					$pbballdendaperbulan[$i] += intval($this->permonth_dendapbb($i));
				}
	    		$jumlah = $value['bulan_lalu'] + $value['bulan_ini'];
				$html .= "<td align='right'>".number_format(intval($jumlah),0,',','.')."</td>";
				$target = $value['target'] != '' ? $value['target'] : 0;
				if ($target==0) {
					$total_persen=0;
				}
				else{
	    			$total_persen = (intval($jumlah) / intval($target)) * 100;
	    			// $total_persen = intval($value['target']) / intval($jumlah) * 100;
				}
				$html .= "<td align='right'>".number_format($total_persen, 2, '.', '')."%</td>";
	    		// }
	    		$html .= "</tr>";
	    	}
    	}
    	// die(var_dump($allperbulan));
    	$html .= "<tr style=\"background-color: #0C9463;\">";
		$html .= "<td></td>";
		$html .= "<td><b>TOTAL PENDAPATAN</b></td>";
		$html .= "<td align='right'><b>".number_format(intval($alltarget),0,',','.')."</b></td>";
		$jumlah = $allbulanlalu + $allbulanini;

		for ($i=1; $i <= $monthnow; $i++) { 
			$html .= "<td align='right'>".number_format(intval($allperbulan[$i]),0,',','.')."</td>";
		}
		$html .= "<td align='right'><b>".number_format(intval($jumlah),0,',','.')."</td>";
		if ($alltarget==0) {
			$total_persen=0;
		}
		else{
			// $total_persen = (intval($alltarget) / intval($jumlah)) * 100;
			$total_persen = intval($jumlah) /intval($alltarget) * 100;
		}
		$alldenda = $alldendabulanlalu+$alldendabulanini;
		$html .= "<td align='right'><b>".number_format($total_persen, 2, '.', '')."%</b></td>";
		// }
    	$html .= "<tr style=\"background-color: #FF8303;\">";
		$html .= "<td></td>";
		$html .= "<td colspan=\"2\"><b>TOTAL DENDA</b></td>";

		for ($i=1; $i <= $monthnow; $i++) { 
			$html .= "<td align='right'>".number_format(intval($alldendaperbulan[$i]),0,',','.')."</td>";
		}
		$html .= "<td align='right'><b>".number_format(intval($alldenda),0,',','.')."</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

		$pbballdenda = $pbballdendabulanlalu+$pbballdendabulanini;
    	$html .= "<tr style=\"background-color: #F88F01;\">";
		$html .= "<td></td>";
		$html .= "<td colspan=\"2\"><b>TOTAL DENDA PBB</b></td>";

		for ($i=1; $i <= $monthnow; $i++) { 
			$html .= "<td align='right'>".number_format(intval($pbballdendaperbulan[$i]),0,',','.')."</td>";
		}
		$html .= "<td align='right'><b>".number_format(intval($pbballdenda),0,',','.')."</b></td>";
		$html .= "<td></td>";
		$html .= "</tr>";

    	$data = [
    			'table'			=> $html,
    			'persentage'	=> round($total_persen,2)."%",
    			'bulan_lalu' 	=> 'Jan',
    			'bulan_sampai'	=> date('M',strtotime('-1 month')),
    			'bulan_ini'	=> date('M'),
    			];
    	die(json_encode($data));
    }

	public function getdata_9pajak_get($tahun = ''){
		// data 9 pajak
		$airbawahtanah = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 1
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
													(SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 1
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
													(SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 1
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
													(SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 1
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 1 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();
		$hiburan = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 2
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
						                          (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 2
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
						                          (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 2
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
						                          (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 2
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 2 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$hotel = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 3
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 3
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 3
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 3
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 3 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$minerba = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 4
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 4
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 4
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 4
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 4 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$parkir = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 5
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 5
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 5
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 5
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 5 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$jalan = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 6
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 6
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 6
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 6
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 6 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$reklame = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 7
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 7
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 7
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 7
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 7 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();

		$restoran = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 8
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
                                      (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 8
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)) AS bulan_ini,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 8
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_lalu,
                                      (SELECT SUM(c.patda_denda) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 8
															AND MONTH(c.payment_paid) = MONTH(CURRENT_DATE)
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)) AS denda_bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 8 AND CPM_TAHUN = '{$tahun}') AS target")
											->row();
		// 9 pajak end
		$result = [
					'HOTEL'			=>	$hotel,
					'RESTORAN'		=>	$restoran,
					'HIBURAN'		=>	$hiburan,
					'REKLAME'		=>	$reklame,
					'PENERANGAN JALAN'		=>	$jalan,
					'PARKIR'		=>	$parkir,
					'AIR BAWAH TANAH'	=>	$airbawahtanah,
					'MINERBA'		=>	$minerba,
					];
		// $result = $pajak;
		return $result;
	}
	public function sarang_walet_get(){


		$walet = $this->db->query("SELECT (SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 9
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_lalu,
										(SELECT SUM(c.patda_total_bayar) FROM simpatda_gw c
															INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
															WHERE PAYMENT_FLAG = 1 
															AND b.`id_sw` = 9
															AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
															AND MONTH(c.payment_paid) < MONTH(CURRENT_DATE)) AS bulan_ini,
													(SELECT cpm_jumlah FROM patda_target_pajak WHERE cpm_jenis_pajak = 9) AS target")
											->row();
					
		$result = ['SARANG WALET'=>$walet];
		return $result;
	}
	public function getdata_bphtb_get(){


		$this->db9gw_ssb = $this->load->database('gw_ssb', TRUE);
		$this->db9sw_ssb = $this->load->database('sw_ssb', TRUE);

		// bphtb
		$bphtb_total = $this->db9gw_ssb->query("SELECT (SELECT SUM( A.bphtb_collectible ) FROM SSB A
															WHERE YEAR ( A.payment_paid ) = YEAR(CURRENT_DATE)
															AND MONTH(A.payment_paid ) < MONTH(CURRENT_DATE)
															AND A.payment_flag = 1 ) AS bulan_lalu,
													(SELECT SUM( A.bphtb_collectible ) FROM SSB A
															WHERE YEAR ( A.payment_paid ) = YEAR(CURRENT_DATE)
															AND MONTH(A.payment_paid ) = MONTH(CURRENT_DATE)
															AND A.payment_flag = 1 ) AS bulan_ini")
											->row();
		$bphtb_target = $this->db9sw_ssb->query("SELECT CTR_AC_VALUE as target FROM central_app_config 
															WHERE CTR_AC_KEY =\"JUMLAH_TARGET\"")
											->row();
		$bphtb = [
					'bulan_lalu'=>$bphtb_total->bulan_lalu,
					'bulan_ini'=>$bphtb_total->bulan_ini,
					'target'=>isset($bphtb_target->target) != ''? $bphtb_target->target : 0,
					];


		$result = ['BPHTB'			=>	$bphtb];
		return $result;
	}
	public function getdata_pbb_get(){		
		$this->dbgw_pbb = $this->load->database('gw_pbb', TRUE);
		$this->dbsw_pbb = $this->load->database('sw_pbb', TRUE);

		// bphtb
		$pbb_total = $this->dbgw_pbb->query("SELECT (SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) FROM pbb_sppt 
															WHERE PAYMENT_FLAG = 1 AND 
															YEAR(payment_paid) = YEAR(NOW()) AND
															MONTH(payment_paid) < MONTH(NOW())) AS bulan_lalu,
													(SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) FROM pbb_sppt 
															WHERE PAYMENT_FLAG = 1 AND
															YEAR(payment_paid) = YEAR(NOW()) AND
															MONTH(payment_paid) = MONTH(NOW())) AS bulan_ini,
													(SELECT SUM(PBB_DENDA) FROM pbb_sppt 
															WHERE PAYMENT_FLAG = 1 AND
															YEAR(payment_paid) = YEAR(NOW()) AND
															MONTH(payment_paid) < MONTH(NOW())) AS denda_bulan_lalu,
													(SELECT SUM(PBB_DENDA) FROM pbb_sppt 
															WHERE PAYMENT_FLAG = 1 AND
															YEAR(payment_paid) = YEAR(NOW()) AND
															MONTH(payment_paid) = MONTH(NOW())) AS denda_bulan_ini")
											->row();
		$pbb_target = $this->dbsw_pbb->query("SELECT CTR_AC_VALUE as target FROM central_app_config 
															WHERE CTR_AC_KEY =\"JUMLAH_TARGET\"")
											->row();
		$pbb = [
					'bulan_lalu'=>$pbb_total->bulan_lalu,
					'bulan_ini'=>$pbb_total->bulan_ini,
					'denda_bulan_lalu'=>$pbb_total->denda_bulan_lalu,
					'denda_bulan_ini'=>$pbb_total->denda_bulan_ini,
					'target'=>27472200846,
					];


		$result = ['PBB'			=>	$pbb];
		return $result;
	}

	public function permonth_dendapajak($id,$month){
		$query = $this->db->query("SELECT SUM(c.patda_denda) as total_bayar FROM simpatda_gw c
										INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
										WHERE PAYMENT_FLAG = 1 
										AND b.`id_sw` = $id
										AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
										AND MONTH(c.payment_paid) =  $month")->row();
		return $query->total_bayar;
	}
	public function permonth_pajak($id,$month){

		$this->db9gw_ssb = $this->load->database('gw_ssb', TRUE);
		$this->db9sw_ssb = $this->load->database('sw_ssb', TRUE);
		$query = $this->db->query("SELECT SUM(c.patda_total_bayar) AS total_bayar FROM simpatda_gw c
  									INNER JOIN SIMPATDA_TYPE B ON c.simpatda_type = B.id
  									WHERE PAYMENT_FLAG = 1 AND b.`id_sw` = $id
									AND YEAR(c.payment_paid) = YEAR(CURRENT_DATE)
									AND MONTH(c.payment_paid) = $month")->row();
		return $query->total_bayar;
	}
	public function permonth_bphtb($month){
		$query = $this->db9gw_ssb->query("SELECT SUM( A.bphtb_collectible ) as total_bayar FROM SSB A
										WHERE YEAR ( A.payment_paid ) = YEAR(CURRENT_DATE)
										AND MONTH(A.payment_paid ) = $month
										AND A.payment_flag = 1")->row();
		return $query->total_bayar;
	}
	public function permonth_pbb($month){
		$this->dbgw_pbb = $this->load->database('gw_pbb', TRUE);
		$this->dbsw_pbb = $this->load->database('sw_pbb', TRUE);

		$query = $this->dbgw_pbb->query("SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) as total_bayar FROM pbb_sppt 
											WHERE PAYMENT_FLAG = 1 
											AND YEAR(payment_paid) = YEAR(NOW())
											AND MONTH(payment_paid) = $month")->row();
		return $query->total_bayar;
	}
	public function permonth_dendapbb($month){
		$this->dbgw_pbb = $this->load->database('gw_pbb', TRUE);
		$this->dbsw_pbb = $this->load->database('sw_pbb', TRUE);

		$query = $this->dbgw_pbb->query("SELECT SUM(PBB_DENDA) as total_bayar FROM pbb_sppt 
											WHERE PAYMENT_FLAG = 1 
											AND YEAR(payment_paid) = YEAR(NOW())
											AND MONTH(payment_paid) = $month")->row();
		return $query->total_bayar;
	}
}
