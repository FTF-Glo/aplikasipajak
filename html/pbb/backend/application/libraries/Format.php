<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Format {
        public function month_year($yearmonth){
			$month = substr($yearmonth, -2);
			$res_month = $this->monthArray($month);
			$res_year = substr($yearmonth, 0, 4);
			return $res_month.' '.$res_year;
        }
		public function month_year_simple($yearmonth){
			$month = substr($yearmonth, -2);
			$res_month = $this->monthArraySort($month);
			$res_year = substr($yearmonth, 0, 4);
			return $res_month." '".substr($res_year,-2);
        }
		public function fulldate($date){
			$day = substr($date,-2);
			$month = substr($date, 5,2);
			$res_month = $this->monthArray($month);
			$res_year = substr($date, 0, 4);
			return $day.' '.$res_month." ".$res_year;
        }
		
		public function date_long($date){
			$result = date('d F Y', strtotime($date));
			return $result;
		}
		
		public function date_monthyear_short($date){
			$result = date('m/y', strtotime($date));
			return $result;
		}
		public function date_my($date){
			$result = date('F Y', strtotime($date));
			return $result;
		}
		
		public function date_short($date){
			$result = date('d-m-Y', strtotime($date));
			return $result;
		}
		
		public function date_full($date){
			if($date == "0000-00-00 00:00:00" || $date == "0000-00-00"){
				$result = "-";	
			}else{
				$result = date('d-m-Y H:i', strtotime($date));
			}
			return $result;
		}
		
		function number($value, $decimal){
			$data = number_format($value,$decimal,',', '');
			return $data;
		}
		function currency($value){
			$data = number_format($value,0,',','.');
			return "Rp.&nbsp;". $data. ',-';
		}
		
		function currency_no($value){
			$data = number_format($value,0,',','.');
			return $data. ',-';
		}
		
		function no_curr($value){
			$data = number_format($value,0,',','.');
			return $data;
		}
		
		function monthArray($month){
			$month_array = array(
								 "01"=>"Januari",
								 "02"=>"Februari",
								 "03"=>"Maret",
								 "04"=>"April",
								 "05"=>"Mei",
								 "06"=>"Juni",
								 "07"=>"Juli",
								 "08"=>"Agustus",
								 "09"=>"September",
								 "10"=>"Oktober",
								 "11"=>"November",
								 "12"=>"Desember");
			return $month_array[$month];
		}
		function monthArraySort($month){
			$month_array = array(
								 "01"=>"Jan",
								 "02"=>"Feb",
								 "03"=>"Mar",
								 "04"=>"Apr",
								 "05"=>"Mei",
								 "06"=>"Jun",
								 "07"=>"Jul",
								 "08"=>"Ags",
								 "09"=>"Sep",
								 "10"=>"Okt",
								 "11"=>"Nov",
								 "12"=>"Des");
			return $month_array[$month];
		}
		
		function input_number($value){
			$data = preg_replace('~\D~', '', $value);
			return $data;
		}
		
		public function penyebut($nilai) {
			$nilai = abs($nilai);
			$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
			$temp = "";
			if ($nilai < 12) {
				$temp = " ". $huruf[$nilai];
			} else if ($nilai <20) {
				$temp = $this->penyebut($nilai - 10). " belas";
			} else if ($nilai < 100) {
				$temp = $this->penyebut($nilai/10)." puluh". $this->penyebut($nilai % 10);
			} else if ($nilai < 200) {
				$temp = " seratus" . $this->penyebut($nilai - 100);
			} else if ($nilai < 1000) {
				$temp = $this->penyebut($nilai/100) . " ratus" . $this->penyebut($nilai % 100);
			} else if ($nilai < 2000) {
				$temp = " seribu" . penyebut($nilai - 1000);
			} else if ($nilai < 1000000) {
				$temp = $this->penyebut($nilai/1000) . " ribu" . $this->penyebut($nilai % 1000);
			} else if ($nilai < 1000000000) {
				$temp = $this->penyebut($nilai/1000000) . " juta" . $this->penyebut($nilai % 1000000);
			} else if ($nilai < 1000000000000) {
				$temp = $this->penyebut($nilai/1000000000) . " milyar" . $this->penyebut(fmod($nilai,1000000000));
			} else if ($nilai < 1000000000000000) {
				$temp = $this->penyebut($nilai/1000000000000) . " trilyun" . $this->penyebut(fmod($nilai,1000000000000));
			}     
			return $temp;
		}
	 
		public function terbilang($nilai) {
			if($nilai<0) {
				$hasil = "minus ". trim($this->penyebut($nilai));
			} else {
				$hasil = trim($this->penyebut($nilai));
			}     		
			return $hasil;
		}
		
		public function address($a){
			
			$result = '<div>
							'.$a['address'].'<br />'.
							'RT:'.$a['rt'].'/RW:'.$a['rw'].
							'<br />Kel.'.$a['kel'].'- Kel.'.$a['kec'].'<br />'.
							$a['kab'].', '.$a['prov'].'
					   </div>';
			echo $result;
		}
		
}