<?php
class DbSpptExtPenggabungan {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id, $vers, $num="", $filter="") {
		
        $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		$filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		if (trim($num)!='') $filter['CPM_OP_NUM'] = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($num));

		
		$query = "SELECT * FROM cppmod_pbb_service_merge_sppt_ext ";
		
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
		//echo $query;
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function add ($id, $vers, $num, $aValue) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		$num = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($num));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(),$value);
		}
		
		$query = "INSERT INTO cppmod_pbb_service_merge_sppt_ext (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, CPM_OP_NUM, ";
		$tmpVals = "'$id', '$vers', '$num', ";
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
	
	public function edit($id, $vers, $num, $aValue) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		$num = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($num));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_service_merge_sppt_ext SET ";
		
		foreach ($aValue as $key => $value) {
			$query .= " $key='$value' ";
			if ($key != $last_key) {
				$query .= ", ";
			}
		}
		
		$query .= " WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers' AND CPM_OP_NUM='$num'";
		
		// echo $query;
		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function del($id, $vers="", $num="") {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		if (trim($vers)!='') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		if (trim($num)!='') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($num));
		
		$query = "DELETE FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
		if (trim($num)!='') $query .= "AND CPM_OP_NUM='$num' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function isExist($id, $vers, $num) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		$num = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($num));
		
		$query = "SELECT * FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers' AND CPM_OP_NUM='$num'";
		
		// echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
}
?>