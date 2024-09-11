<?php
class DbWajibPajak {
	private $dbSpec = null;
	public $totalrows = 0;
        
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($filter,$perpage,$page) {
		$whereQuery = "";
		
		if (count($filter) > 0) {
			$whereQuery .="WHERE ";
			//$last_key = end(array_keys($filter));
			$last_key = array_keys($filter);
        	$last_key = end($last_key);

			foreach ($filter as $key => $value) {
			   if ($key == "CPM_WP_PEKERJAAN"){ 
					if($value=='2') $whereQuery .= " $key = 'Badan' ";
					else if($value=='1') $whereQuery .= " $key <> 'Badan' ";
			   } elseif($key == "CPM_WP_ID")
					$whereQuery .= " $key = '$value' ";
				else
					$whereQuery .= " $key LIKE '%$value%' ";
				
			   if ($key != $last_key) $whereQuery .= " AND ";
			}

		}
			
		$query = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_wajib_pajak $whereQuery ";
		$this->dbSpec->sqlQueryRow($query, $rowCount);
		$this->totalrows = $rowCount[0]['TOTAL'];
		
		$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		
		$query = "SELECT * FROM cppmod_pbb_wajib_pajak $whereQuery ";
		
		if ($perpage) {
			$query .= " ORDER BY CPM_WP_NAMA,CPM_WP_ID LIMIT $hal, $perpage ";
		}
		// echo $query;    
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function get_id($filter) {
		$whereQuery = "";
		
		if (count($filter) > 0) {
			$whereQuery .="WHERE ";
			//$last_key = end(array_keys($filter));
			$last_key = array_keys($filter);
        	$last_key = end($last_key);

			foreach ($filter as $key => $value) {
			   if ($key == "CPM_WP_PEKERJAAN"){ 
					if($value=='2') $whereQuery .= " $key = 'Badan' ";
					else if($value=='1') $whereQuery .= " $key <> 'Badan' ";
			   } elseif($key == "CPM_WP_ID")
					$whereQuery .= " $key = '$value' ";
				else
					$whereQuery .= " $key LIKE '%$value%' ";
				
			   if ($key != $last_key) $whereQuery .= " AND ";
			}

		}
			
		$query = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_wajib_pajak $whereQuery ";
		$this->dbSpec->sqlQueryRow($query, $rowCount);
		$this->totalrows = $rowCount[0]['TOTAL'];
		
		//$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		
		$query = "SELECT * FROM cppmod_pbb_wajib_pajak $whereQuery ";
		
		/*if ($perpage) {
			$query .= " ORDER BY CPM_WP_NAMA,CPM_WP_ID LIMIT $hal, $perpage ";
		}*/
		// echo $query;    
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function save($id, $aValue) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
		}
		
		$query = "INSERT INTO cppmod_pbb_wajib_pajak (CPM_WP_ID,CPM_WP_PEKERJAAN,CPM_WP_NAMA,
                    CPM_WP_ALAMAT,CPM_WP_KELURAHAN,CPM_WP_RT,CPM_WP_RW,CPM_WP_PROPINSI,CPM_WP_KOTAKAB
                    ,CPM_WP_KECAMATAN,CPM_WP_KODEPOS,CPM_WP_NO_HP)
                    VALUES ('".$id."', '".$aValue['CPM_WP_PEKERJAAN']."','".$aValue['CPM_WP_NAMA']."',
                    '".$aValue['CPM_WP_ALAMAT']."','".$aValue['CPM_WP_KELURAHAN']."','".$aValue['CPM_WP_RT']."','".$aValue['CPM_WP_RW']."','".$aValue['CPM_WP_PROPINSI']."','".$aValue['CPM_WP_KOTAKAB']."',
                    '".$aValue['CPM_WP_KECAMATAN']."','".$aValue['CPM_WP_KODEPOS']."','".$aValue['CPM_WP_NO_HP']."')
                    ON DUPLICATE KEY UPDATE CPM_WP_STATUS='".$aValue['CPM_WP_STATUS']."',CPM_WP_PEKERJAAN='".$aValue['CPM_WP_PEKERJAAN']."',CPM_WP_NAMA='".$aValue['CPM_WP_NAMA']."',
                    CPM_WP_ALAMAT='".$aValue['CPM_WP_ALAMAT']."',
                    CPM_WP_KELURAHAN='".$aValue['CPM_WP_KELURAHAN']."',CPM_WP_RT='".$aValue['CPM_WP_RT']."',CPM_WP_RW='".$aValue['CPM_WP_RW']."',CPM_WP_PROPINSI='".$aValue['CPM_WP_PROPINSI']."',CPM_WP_KOTAKAB='".$aValue['CPM_WP_KOTAKAB']."'
                    ,CPM_WP_KECAMATAN='".$aValue['CPM_WP_KECAMATAN']."',CPM_WP_KODEPOS='".$aValue['CPM_WP_KODEPOS']."',CPM_WP_NO_HP='".$aValue['CPM_WP_NO_HP']."' ";
		// echo $query; exit;
		return $this->dbSpec->sqlQuery($query, $res);
		
	}
        
    public function saveToSPPT ($id, $aValue) {
        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
        }
		$query = "UPDATE cppmod_pbb_sppt_final SET CPM_WP_PEKERJAAN='".$aValue['CPM_WP_PEKERJAAN']."',CPM_WP_NAMA='".$aValue['CPM_WP_NAMA']."',
			CPM_WP_ALAMAT='".$aValue['CPM_WP_ALAMAT']."',
			CPM_WP_KELURAHAN='".$aValue['CPM_WP_KELURAHAN']."',CPM_WP_RT='".$aValue['CPM_WP_RT']."',CPM_WP_RW='".$aValue['CPM_WP_RW']."',CPM_WP_PROPINSI='".$aValue['CPM_WP_PROPINSI']."',CPM_WP_KOTAKAB='".$aValue['CPM_WP_KOTAKAB']."'
			,CPM_WP_KECAMATAN='".$aValue['CPM_WP_KECAMATAN']."',CPM_WP_KODEPOS='".$aValue['CPM_WP_KODEPOS']."',CPM_WP_NO_HP='".$aValue['CPM_WP_NO_HP']."' WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'";

		$bOK = $this->dbSpec->sqlQuery($query, $res);
		if(!$bOK) return false;

		$query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_WP_PEKERJAAN='".$aValue['CPM_WP_PEKERJAAN']."',CPM_WP_NAMA='".$aValue['CPM_WP_NAMA']."',
			CPM_WP_ALAMAT='".$aValue['CPM_WP_ALAMAT']."',
			CPM_WP_KELURAHAN='".$aValue['CPM_WP_KELURAHAN']."',CPM_WP_RT='".$aValue['CPM_WP_RT']."',CPM_WP_RW='".$aValue['CPM_WP_RW']."',CPM_WP_PROPINSI='".$aValue['CPM_WP_PROPINSI']."',CPM_WP_KOTAKAB='".$aValue['CPM_WP_KOTAKAB']."'
			,CPM_WP_KECAMATAN='".$aValue['CPM_WP_KECAMATAN']."',CPM_WP_KODEPOS='".$aValue['CPM_WP_KODEPOS']."',CPM_WP_NO_HP='".$aValue['CPM_WP_NO_HP']."' WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'";

		$this->dbSpec->sqlQuery($query, $res);
		if(!$bOK) return false;

		$query = "UPDATE cppmod_pbb_sppt SET CPM_WP_PEKERJAAN='".$aValue['CPM_WP_PEKERJAAN']."',CPM_WP_NAMA='".$aValue['CPM_WP_NAMA']."',
			CPM_WP_ALAMAT='".$aValue['CPM_WP_ALAMAT']."',
			CPM_WP_KELURAHAN='".$aValue['CPM_WP_KELURAHAN']."',CPM_WP_RT='".$aValue['CPM_WP_RT']."',CPM_WP_RW='".$aValue['CPM_WP_RW']."',CPM_WP_PROPINSI='".$aValue['CPM_WP_PROPINSI']."',CPM_WP_KOTAKAB='".$aValue['CPM_WP_KOTAKAB']."'
			,CPM_WP_KECAMATAN='".$aValue['CPM_WP_KECAMATAN']."',CPM_WP_KODEPOS='".$aValue['CPM_WP_KODEPOS']."',CPM_WP_NO_HP='".$aValue['CPM_WP_NO_HP']."' WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'";

		$this->dbSpec->sqlQuery($query, $res);
		if(!$bOK) return false;
		
		//// Comment By ZNK 20171013
		//// Untuk sementara di nonaktifkan karena untuk update ke Current harus melalui penetapan
		// $query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_final WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'
					// UNION ALL SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'
					// UNION ALL SELECT CPM_NOP FROM cppmod_pbb_sppt WHERE TRIM(CPM_WP_NO_KTP) = '".$id."'";

		// if ($this->dbSpec->sqlQueryRow($query, $res2)){
			
			// $nop = "";
			// $i = 0;
			
			// foreach($res2 as $row){
				// if($i >0 ) $nop .= ",";
				// $nop .= "'".$row['CPM_NOP']."'";
				// $i++;
			// }
			
			// if($nop != ""){
				// $query = "UPDATE cppmod_pbb_sppt_current SET WP_NAMA='".$aValue['CPM_WP_NAMA']."', WP_ALAMAT='".$aValue['CPM_WP_ALAMAT']."',
					// WP_KELURAHAN='".$aValue['CPM_WP_KELURAHAN']."',WP_RT='".$aValue['CPM_WP_RT']."',WP_RW='".$aValue['CPM_WP_RW']."',WP_KOTAKAB='".$aValue['CPM_WP_KOTAKAB']."'
					// ,WP_KECAMATAN='".$aValue['CPM_WP_KECAMATAN']."' 
					// WHERE NOP IN ($nop)";
					
					// $bOK = $this->dbSpec->sqlQuery($query, $res);
			// }
		// }
		
		// if(!$bOK) return false;

		return true;                
	}
	
	public function del($id) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));		
		if (trim($vers)!='') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
		
		$query = "DELETE FROM cppmod_pbb_wajib_pajak WHERE CPM_WP_ID='$id' ";
		
		return $this->dbSpec->sqlQuery($query, $res);
	}
}
?>
