<?php
class DbDashboard {

    private $GW_dbSpec = NULL;
    private $SW_dbSpec = NULL;
    private $sTbl_name = "";
    private $aWhere = "";
    private $aLike = "";
    private $sOrder_by = "";
    private $aGroup_by = "";
    private $aQuerylog = array();
    private $id_kota = "";
    private $nm_kota = "";

    public function __construct($Gw_dbSpec, $SW_dbSpec, $kdKotaKab, $nmKotaKab) {
        $this->GW_dbSpec = $Gw_dbSpec;
        $this->SW_dbSpec = $SW_dbSpec;
        $this->where(array("OP_KOTAKAB_KODE" => mysql_real_escape_string(trim($kdKotaKab))));
        $this->sTbl_name = "PBB_SPPT";
		$this->id_kota = mysql_real_escape_string(trim($kdKotaKab));
		$this->nm_kota = mysql_real_escape_string(trim($nmKotaKab));
    }

    public function where($where) {
        foreach ($where as $key => $value) {
            $this->aWhere[$key] = $value;
        }
    }

    public function like($like) {
        foreach ($like as $key => $value) {
            $this->aLike[$key] = $value;
        }
    }

    public function order_by($order_by, $opt = "") {
        $order_by = mysql_real_escape_string(trim($order_by));
        $opt = mysql_real_escape_string(trim($opt));

        $this->sOrder_by .= " ORDER BY " . $order_by . " " . $opt;
    }

    public function group_by($group_by) {
        if (is_array($group_by)) {
            foreach ($group_by as $value) {
                $this->aGroup_by[] = $value;
            }
        } else {
            $this->aGroup_by[] = $group_by;
        }
    }

    public function last_query() {
        return end(array_values($this->aQuerylog));
    }

    public function querylog() {
        return $this->aQuerylog;
    }

    public function get($field = array()) {
        $field = (count($field) > 0) ? implode(", ", $field) : "*";
        $query = "SELECT $field FROM " . $this->sTbl_name;

        //WHERE and LIKE
        if (count($this->aWhere) > 0 || count($this->aLike) > 0) {
            $query .=" WHERE ";

            //WHERE
            if (count($this->aWhere) > 0) {
                $last_key = end(array_keys($this->aWhere));
                foreach ($this->aWhere as $key => $value) {
                    $query .= " $key = '" . mysql_real_escape_string(trim($value)) . "' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }
            }

            //LIKE
            if (count($this->aLike) > 0) {
                $last_key = end(array_keys($this->aLike));
                foreach ($this->aLike as $key => $value) {
                    $query .= " $key LIKE '%" . mysql_real_escape_string(trim($value)) . "%' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }
            }
        }

        //GROUP_BY
        if (count($this->aGroup_by) > 0) {
            $query .= " GROUP BY ";

            $last_key = end(array_keys($this->aGroup_by));
            foreach ($this->aGroup_by as $key => $val) {
                $query .= mysql_real_escape_string(trim($val));
                if ($key != $last_key)
                    $query .= ", ";
            }
        }

        //ORDER_BY
        if ($this->sOrder_by != "") {
            $query .= $this->sOrder_by;
        }

//        echo $query."<br>";
        $this->aQuerylog[] = $query;
        if ($this->GW_dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }
	
	public function getKecamatanList() {
		$query = " SELECT * FROM cppmod_tax_kecamatan where CPC_TKC_KKID = '".$this->id_kota."' order by CPC_TKC_KECAMATAN ASC ";
		$this->SW_dbSpec->sqlQueryRow($query, $res);
		return $res;
	}
	
	public function getKelurahanList() {
		$query = " SELECT * FROM cppmod_tax_kelurahan where CPC_TKL_ID like '".$this->id_kota."%' ORDER BY CPC_TKL_KELURAHAN ASC ";
		$this->SW_dbSpec->sqlQueryRow($query, $res);
		return $res;
	}
	
	public function isi_DB() {
		$query = " SELECT * FROM PBB_SPPT ORDER BY NOP ";
		$this->GW_dbSpec->sqlQueryRow($query, $res);
		$n=0;
		foreach($res as $value){
		$qu = " SELECT kecamatan.CPC_TKC_ID as id_kecamatan, kelurahan.CPC_TKL_ID as id_kelurahan, kecamatan.CPC_TKC_KECAMATAN kec, kelurahan.CPC_TKL_KELURAHAN kel 
		FROM cppmod_tax_kecamatan as kecamatan 
		INNER JOIN cppmod_tax_kelurahan as kelurahan on kecamatan.CPC_TKC_ID=kelurahan.CPC_TKL_KCID WHERE kecamatan.CPC_TKC_KKID='3273' LIMIT $n,1; ";
		$this->SW_dbSpec->sqlQuery($qu, $ress);
		$ress = mysqli_fetch_assoc($ress);
			echo"<pre>"; 
			print_r($ress); 
			$sql = "UPDATE PBB_SPPT SET OP_KECAMATAN_KODE='".$ress['id_kecamatan']."', OP_KELURAHAN_KODE='".$ress['id_kelurahan']."', 
			OP_KECAMATAN='".$ress['kec']."', OP_KELURAHAN='".$ress['kel']."', OP_KOTAKAB='".$this->nm_kota."' WHERE NOP='$value[NOP]' ";
			$this->GW_dbSpec->sqlQuery($sql, $res);
			echo"</pre>";
			$n++;
			if($n==30)$n=0;
		}
	}
	
	public function pArray($q) {
		echo "<pre>"; print_r($q); echo "</pre>";
	}

	public function getName($q,$ket) {
		if($ket=='kec'){
			$query = " SELECT CPC_TKC_ID, CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan where CPC_TKC_ID = '".$q."'";
			$this->SW_dbSpec->sqlQueryRow($query, $res);
			return $res[0]['CPC_TKC_KECAMATAN'];
		}else if($ket=='kel'){
			$query = " SELECT CPC_TKL_ID, CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan where CPC_TKL_ID = '".$q."'";
			$this->SW_dbSpec->sqlQueryRow($query, $res);
			return $res[0]['CPC_TKL_KELURAHAN'];
		}else{
			return "Semua Daerah";
		}
	}

	public function getDataRow() {
		$quData="SELECT COUNT(*) d, OP_KECAMATAN, OP_KECAMATAN_KODE, SPPT_TAHUN_PAJAK FROM `PBB_SPPT` GROUP BY SPPT_TAHUN_PAJAK,OP_KECAMATAN_KODE ORDER BY OP_KECAMATAN,SPPT_TAHUN_PAJAK ASC;";
		$this->GW_dbSpec->sqlQueryRow($quData, $rData); 
		$data=array();
		foreach($rData as $key => $value){
			$data['d']=$value['d'];
			$data['OP_KECAMATAN']=$value['OP_KECAMATAN'];
			$data['OP_KECAMATAN_KODE']=$value['OP_KECAMATAN_KODE'];
			$data['SPPT_TAHUN_PAJAK']=$value['SPPT_TAHUN_PAJAK'];
		}
		$this->pArray($data);
	}
	
	public function getData($tahun,$kode,$flag,$ket,$objek,&$data,$n) {
		global $kodeAr,$i;
		if($ket=='kec'){
			$var1 = " and OP_KECAMATAN_KODE='".$kode."' ";
		}else if($ket=='kel'){
			$var1 = "  and OP_KELURAHAN_KODE='".$kode."'  ";
		}else if($ket=='semua'){
			$var1 = "  GROUP BY SPPT_TAHUN_PAJAK ASC ";
		}
		
		if($flag==0){ // mendefinisikan sql flag belum di bayar
			$var2 = " and PAYMENT_FLAG='0' ";
		}else if($flag==1){  // mendefinisikan sql flag sudah di bayar
			$var2 = "  and PAYMENT_FLAG='1' ";
		}else if($flag==100){ // flag 100/semua hasilnya sama dengan flag 101/tidak di tentukan, hanya saja flag 100 harus di buat variabel $var2 agar query tidak error karena mengambil nama variabel yang tidak definisikan
			$var2 = "";
		}
		
		if($objek==100){
			$col = " COUNT(*) ";
		}else if($objek==1){
			$col = " SUM(SPPT_PBB_HARUS_DIBAYAR) ";
		}
		
		if ($flag!=10){ //jika flag tidak 10 variabel $data di keluarkan
		  $quNilai = "SELECT ".$col." as d FROM PBB_SPPT where SPPT_TAHUN_PAJAK='".$tahun."' ".$var2.$var1;
		  //echo $quNilai;
		  $this->GW_dbSpec->sqlQuery($quNilai, $rN);
		  $reN = mysqli_fetch_assoc($rN);
		  //echo $reN['d']."<br>";
		  $data[$n][] = ($reN['d']==NULL)?0:$reN['d'];
		}else{ //akan mengambil flag yang tidak di tentukan dan mendefinisikan flag yang sudah bayar dan belum bayar sebagai sql terpisah 
		  $quNilai = "SELECT ".$col." as d FROM PBB_SPPT where SPPT_TAHUN_PAJAK='".$tahun."' and PAYMENT_FLAG='0' ".$var1;
		  $this->GW_dbSpec->sqlQuery($quNilai, $rN);
		  $reN = mysqli_fetch_assoc($rN);
		  $label_1 = "belum bayar: ";
		  $value_1 = ($reN['d']==NULL)?0:$reN['d'];

		  $quNilai = "SELECT ".$col." as d FROM PBB_SPPT where SPPT_TAHUN_PAJAK='".$tahun."' and PAYMENT_FLAG='1' ".$var1;
		  $this->GW_dbSpec->sqlQuery($quNilai, $rN);
		  $reN = mysqli_fetch_assoc($rN);
		  $label_2 = " sudah bayar: ";
		  $value_2 = ($reN['d']==NULL)?0:$reN['d'];
		  $title  = $this->getName($kode,$ket)."<br>".$tahun;
		  
		  $kodeAr[$i] = $kode;

		  echo ($i>0 and $kodeAr[$i] != $kodeAr[$i-1])?"<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><hr/>":"";
		  
		  echo getContainetChart("cChart_".$i,200,200);
		  echo getChartPie("cChart_".$i, $title,"", "[ ['$label_1',   $value_1], ['$label_2', $value_2] ]");
		  $i++;
		}
	}

	public function getTahun($tahun1,$tahun2,&$thn) {
		if(isset($tahun1) and $tahun2==NULL){
				$thn[0]['tahun']=$tahun1;
		}else if(isset($tahun1) and $tahun2!=NULL){
			$a=0;
			for($i=$tahun1; $i<=$tahun2; $i++){
				$thn[$a]['tahun']=$i;
				$a++;
			}
		}
	}

	public function getTahunDefault(&$rThn) {
		  $quThn="SELECT SPPT_TAHUN_PAJAK as tahun FROM PBB_SPPT GROUP BY SPPT_TAHUN_PAJAK ASC;";
		  $this->GW_dbSpec->sqlQueryRow($quThn, $rThn); 
	}
	
	public function getTitle($objek,$flag,&$satuan){
		if($objek==100){
			$satuan = "Buah";
			$title = "Jumlah Banyaknya SPPT";
		}else{
			$satuan = "Rupiah";
			$title = "Jumlah Nilai Perolehan SPPT";
		}
		
		if($flag==0){
			$title .=" (Belum di bayar)";
		}else if($flag==1){
			$title .=" (Sudah di bayar)";
		}
				
		return $title;
	}
	public function barDaerah($rq,$ket){
		foreach ($rq as $value){
			  $daerah[] = $this->getName($value,$ket);
		}
		$barDaerah = "['";
		$last_key = end(array_keys($daerah));
		foreach ($daerah as $key => $value){
			  $barDaerah .= $value;
			  if($key != $last_key)
			  $barDaerah .= "', '";
		}
		$barDaerah .= "']";
		return $barDaerah;
	}
	public function barSeriesData($data){
		$series = "[";
		$last_key = end(array_keys($data));
		foreach ($data as $key => $value){
			  $series .= "{";
			  
			  $data2 = $value;
			  $last_key2 = end(array_keys($data2));
			  foreach($data2 as $key2 => $value2){
				if($key2 == 0){
			  		$series .= "name: '".$value2."', data: [";
				}else if($key2 == $last_key2){
					$series .= $value2."]";
				}else{
					$series .= $value2.",";
				}
			  }

			  $series .= "}";
			  if($key != $last_key)
			  $series .= ", ";
		}
		$series .= "]";
		return $series;
	}
	public function getSPPT($_REQ) {
		$n=0; 
		if(!isset($_REQ['flag'])) $_REQ['flag']=101;	// mendefinisikan flag
		if(!empty($_REQ['tgl_0']) or isset($_REQ['tgl_1'])){	// mendefinisikan tahun
				if(!isset($_REQ['tgl_1'])) $_REQ['tgl_1']=NULL;
			$this->getTahun($_REQ['tgl_0'],$_REQ['tgl_1'],$rThn); 
		}else{
			$this->getTahunDefault($rThn); 
		}
		
		if(isset($_REQ['Ckec'])){ // mendefinisikan kecamatan
		  $rq=$_REQ['Ckec'];
		  $ket="kec";
		}else if(isset($_REQ['Ckel'])){ // mendefinisikan kelurahan
		  $rq=$_REQ['Ckel'];
		  $ket="kel";
		}
		
		if(isset($rq) and $_REQ['flag']!=10){ // jika kecamatan atau kelurahan didefinisikan
				foreach ($rThn as $key_thn => $v_thn){
					$data[$n][] = $v_thn['tahun'];
					  foreach ($rq as $value){
						  $this->getData($v_thn['tahun'],$value,$_REQ['flag'],$ket,$_REQ['objek'],$data,$n);
					  }
					$n++;
				}
		}else if(isset($rq) and $_REQ['flag']==10){ // jika kecamatan atau kelurahan didefinisikan
				  foreach ($rq as $value){
					  $data[$n][] = $this->getName($value,$ket);
					  foreach ($rThn as $key_thn => $v_thn){
							$this->getData($v_thn['tahun'],$value,$_REQ['flag'],$ket,$_REQ['objek'],$data,$n);
					  }
					  $n++;
				  }
		}else{	
			// menumjukan bahwa yang akan di pilih adalah total semua daerah (kecamatan dan kelurahan)
				foreach ($rThn as $key_thn => $v_thn){
					$data[$n][] = $v_thn['tahun'];
					$this->getData($v_thn['tahun'],0,$_REQ['flag'],'semua',$_REQ['objek'],$data,$n);
					$n++;
				}
				$rq[]=0;
		}
	foreach ($rThn as $key_thn => $v_thn){ 
		$thn_label[] = $v_thn['tahun']; 
	}

	if($_REQ['flag']!=10){
		if(!isset($rq)) $rq[0] = 0;
		if(!isset($ket)) $ket = "0";
		$height = (count($rq)>2)?count($rq)*150:300;

		$title = $this->getTitle($_REQ['objek'],$_REQ['flag'],$satuan);
		$subTitle = $this->nm_kota; //Nama kota request dari configuration
		$series = $this->barSeriesData($data);
		$xAxisData = $this->barDaerah($rq,$ket);
		$yAxisTitle = "Nilai Perolehan"; //Permanen
		echo getContainetChart('containerChartBar',1200,$height);
		echo getChartBar('containerChartBar', $title ,$subTitle, $series, $xAxisData, $yAxisTitle,$satuan);
		//echo"<img src='view/PBB/dashboard/chartImageBar.php?data=".$data."&thn_label=".$thn_label."&title=".$title."&height=".$height."' alt='Chart' />";
	}

	}
}

?>