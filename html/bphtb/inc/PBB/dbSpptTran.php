<?php
class DbSpptTran {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id="", $filter="") {
		if (trim($id)!='') $filter['CPM_TRAN_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		
		$query = "SELECT * FROM cppmod_pbb_tranmain ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$last_key = end(array_keys($value));
						$query .= " ( ";						
						foreach ($value as $tkey => $val) {
							$query .= " $key = '".$val."' ";
							if ($tkey != $last_key) {
								$query .= " OR ";
							}
						}
						$query .= " ) ";
					} else {
						$query .= " $key = '$value' ";
					}
				} else {
					$query .= " $key LIKE '%$value%' ";
				}
				if ($key != $last_key) $query .= " AND ";
			}
			
		}
		
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function getDetail($id="", $filter="", $custom="") {
		if (trim($id)!='') $filter['CPM_TRAN_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		
		$query = "SELECT * FROM cppmod_pbb_tranmain as A, cppmod_pbb_sppt as B ";
		$query .="WHERE A.CPM_TRAN_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID AND A.CPM_SPPT_DOC_VERSION=B.CPM_SPPT_DOC_VERSION ";
		
		if (count($filter) > 0) {
			$query .="AND ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$tlast_key = end(array_keys($value));
						$query .= " ( ";						
						foreach ($value as $tkey => $val) {
							$query .= " $key = '".$val."' ";
							if ($tkey != $tlast_key) {
								$query .= " OR ";
							}
						}
						$query .= " ) ";						
					} else {
						$query .= " $key = '$value' ";
					}
				} else {
						$query .= " $key LIKE '%$value%' ";
				}
				if ($key != $last_key) $query .= " AND ";
			}			
		}
		
		if ($custom !="") {
			$query .= "AND ".$custom;
		}
		
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function add ($id, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($DBLink, $value);
		}
		
		$query = "INSERT INTO cppmod_pbb_tranmain (CPM_TRAN_ID, ";
		$tmpVals = "'$id', ";
		$last_key = end(array_keys($aValue));
		
		foreach ($aValue as $key => $value) {
			$query .= $key;
			$tmpVals .= "'".$value."'";
			
			if ($key != $last_key) {
				$query .= ", ";
				$tmpVals .= ", ";
			}
		}
		$query .= ") values (".$tmpVals.")";
		
		// echo $query;
		
		return $this->dbSpec->sqlQuery($query, $res);
		
	}
	
	public function edit($id, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_tranmain SET ";
		
		foreach ($aValue as $key => $value) {
			$query .= "$key='$value'";
			if ($key != $last_key) {
				$query .= ", ";
			}
		}
		
		$query .= "WHERE CPM_TRAN_ID='$id'";
		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function del($id="", $refnum="") {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		
		$query = "DELETE FROM cppmod_pbb_tranmain WHERE ";
		if ($id!="") $query .= "CPM_TRAN_ID='$id' ";
		if ($id!="" && $refnum!="") $query .= "AND ";
		if ($refnum!="") $query .= "CPM_TRAN_REFNUM='$refnum' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
}
?>