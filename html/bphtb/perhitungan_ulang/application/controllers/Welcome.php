<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function calculate_()
	{
		$this->dbgw = $this->load->database('dbgw',true);
		$getdata = $this->dbgw->where(['payment_flag'=>0])
								->where("bphtb_dibayar != 0")
								// ->where("wp_nama in ('tes malam 1','tes malam 2','tes malam 3')")
								->get('ssb')
								->result();
		$id_switching =[];
		foreach ($getdata as $key) {
			array_push($id_switching, $key->id_switching);
		}
		$data_doc = $this->db->where_in('CPM_SSB_ID',$id_switching)
				#->where('CPM_OP_JENIS_HAK = 1')
				->where('CPM_OP_JENIS_HAK = 7')
				->get('cppmod_ssb_doc')
				->result();
		$html ='<table border="2">';
		$html .='<thead>';
			$html.= "<tr>";
				$html.= "<td>ID SSB</td>";
				$html.= "<td>NAMA</td>";
				$html.= "<td>Jenis HAK</td>";
				$html.= "<td>LUAS TANAH</td>";
				$html.= "<td>NJOP TANAH</td>";
				$html.= "<td>LUAS BANGUNAN</td>";
				$html.= "<td>NJOP BANGUNAN</td>";
				$html.= "<td>HARGA TRANSAKSI</td>";
				$html.= "<td>NPOPTKP</td>";
				$html.= "<td>BPTHB BAYAR</td>";
				$html.= "<td>DENDA PERSEN</td>";
				$html.= "<td>JUMLAH BAYAR SEHARUSNYA</td>";

			$html.= "</tr>";
		$html .='</thead>';

		foreach ($data_doc as $key) {
			$NEW_BPHTB = 0;
			if ($key->CPM_OP_JENIS_HAK != 7 ) {
				$njop_luas = $key->CPM_OP_LUAS_TANAH * $key->CPM_OP_NJOP_TANAH;
				$njop_bangun =  $key->CPM_OP_LUAS_BANGUN * $key->CPM_OP_NJOP_BANGUN;
				$NJOPPBB = $njop_luas + $njop_bangun;
				$harga_transaksi = $key->CPM_OP_HARGA;

				$NPOP = $harga_transaksi;
				if ($harga_transaksi < $NJOPPBB) {
					$NPOP = $NJOPPBB;
				}

				$NPOPKP = $NPOP - $key->CPM_OP_NPOPTKP;
				$NEW_BPHTB = $NPOPKP * 0.05;
				if ($NEW_BPHTB < 0) {
					$NEW_BPHTB = 0;
				}
			}
			else{
				$NEW_BPHTB = $key->CPM_BPHTB_BAYAR;
			}
			// var_dump($this->dbgw->where('id_switching',$key->CPM_SSB_ID)->get('ssb')->result());
			// $this->dbgw->where('id_switching',$key->CPM_SSB_ID)->update('ssb',['bphtb_dibayar'=>$NEW_BPHTB]);
			// var_dump($this->db->where('CPM_SSB_ID',$key->CPM_SSB_ID)->get('cppmod_ssb_doc')->result());
			// $this->db->where('CPM_SSB_ID',$key->CPM_SSB_ID)->update('cppmod_ssb_doc',['CPM_BPHTB_BAYAR'=>$NEW_BPHTB]);
			if ($NEW_BPHTB != $key->CPM_BPHTB_BAYAR) {
				# code...
				$html.= "<tr>";
					$html.= "<td>{$key->CPM_SSB_ID}</td>";
					$html.= "<td>{$key->CPM_WP_NAMA}</td>";
					$html.= "<td>{$key->CPM_OP_JENIS_HAK}</td>";
					$html.= "<td>{$key->CPM_OP_LUAS_TANAH}</td>";
					$html.= "<td>{$key->CPM_OP_NJOP_TANAH}</td>";
					$html.= "<td>{$key->CPM_OP_LUAS_BANGUN}</td>";
					$html.= "<td>{$key->CPM_OP_NJOP_BANGUN}</td>";
					$html.= "<td>{$key->CPM_OP_HARGA}</td>";
					$html.= "<td>{$key->CPM_OP_NPOPTKP}</td>";
					$html.= "<td>{$key->CPM_BPHTB_BAYAR}</td>";
					$html.= "<td>{$key->CPM_PERSEN_DENDA}</td>";
					$html.= "<td>{$NEW_BPHTB}</td>";
				$html.= "</tr>";
			}
		}
		$html .='</table>';

		echo $html;
		echo count($data_doc);
	}
}
