<?php
class DbGwCurrent {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($filter=array()) {
		$query = "SELECT * FROM cppmod_pbb_sppt_current ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				$value = mysqli_real_escape_string($DBLink, trim($value));
				if ($key == "NOP") 
					$query .= " $key = '$value' ";
				else
					$query .= " $key LIKE '%$value%' ";
				if ($key != $last_key) $query .= " AND ";
			}		
		}
		$query .= "ORDER BY FLAG ASC, NOP ASC ";
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function del($nop) {
		$nop = mysqli_real_escape_string($DBLink, trim($nop));		
		
		$query = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP='$nop' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function isExist($nop, $thn) {
		$nop = mysqli_real_escape_string($DBLink, trim($nop));
		$thn = mysqli_real_escape_string($DBLink, trim($thn));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_current WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thn'";
		
		// echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
}
?>