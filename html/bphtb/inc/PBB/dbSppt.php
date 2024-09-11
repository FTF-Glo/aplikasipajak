<?php
class DbSppt {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id="", $vers="", $filter="") {
		if (trim($id)!='') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		if (trim($vers)!='') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID") 
					$query .= " $key = '$value' ";
				else
					$query .= " $key LIKE '%$value%' ";
				if ($key != $last_key) $query .= " AND ";
			}
			
		}
		$query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";
		
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function add ($id, $vers, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($DBLink, $value);
		}
		
		$query = "INSERT INTO cppmod_pbb_sppt (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
		$tmpVals = "'$id', '$vers', ";
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
		
		echo $query;
		
		return $this->dbSpec->sqlQuery($query, $res);
		
	}
	
	public function edit($id, $vers, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_sppt SET ";
		
		foreach ($aValue as $key => $value) {
			$query .= "$key='$value'";
			if ($key != $last_key) {
				$query .= ", ";
			}
		}
		
		$query .= "WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		
		// echo $query;
		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function del($id, $vers="") {
		$id = mysqli_real_escape_string($DBLink, trim($id));		
		if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "DELETE FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function isExist($id, $vers) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		
		// echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
}
?>