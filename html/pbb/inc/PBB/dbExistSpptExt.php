<?
class DbExistSpptExt {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id, $vers, $num="", $filter="") {
		$filter['CPM_SPPT_DOC_ID'] = mysql_real_escape_string(trim($id));
		$filter['CPM_SPPT_DOC_VERSION'] = mysql_real_escape_string(trim($vers));
		if (trim($num)!='') $filter['CPM_OP_NUM'] = mysql_real_escape_string(trim($num));

		
		$query = "SELECT * FROM cppmod_pbb_sppt_ext_existing ";
		
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
		
//		echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function add ($id, $vers, $num, $aValue) {
		$id = mysql_real_escape_string(trim($id));
		$vers = mysql_real_escape_string(trim($vers));
		$num = mysql_real_escape_string(trim($num));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysql_real_escape_string($value);
		}
		
		$query = "INSERT INTO cppmod_pbb_sppt_ext_existing (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, CPM_OP_NUM, ";
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
		$id = mysql_real_escape_string(trim($id));
		$vers = mysql_real_escape_string(trim($vers));
		$num = mysql_real_escape_string(trim($num));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_sppt_ext_existing SET ";
		
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
		$id = mysql_real_escape_string(trim($id));
		if (trim($vers)!='') $vers = mysql_real_escape_string(trim($vers));
		if (trim($num)!='') $vers = mysql_real_escape_string(trim($num));
		
		$query = "DELETE FROM cppmod_pbb_sppt_ext_existing WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
		if (trim($num)!='') $query .= "AND CPM_OP_NUM='$num' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function isExist($id, $vers, $num) {
		$id = mysql_real_escape_string(trim($id));
		$vers = mysql_real_escape_string(trim($vers));
		$num = mysql_real_escape_string(trim($num));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_ext_existing WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers' AND CPM_OP_NUM='$num'";
		
		// echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
	
	public function incVers($id, $vers, $oldvers) {
		$id = mysql_real_escape_string(trim($id));
		$vers = mysql_real_escape_string(trim($vers));
		$oldvers = mysql_real_escape_string(trim($oldvers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_ext_existing WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$oldvers'";
		
		$bOK = $this->dbSpec->sqlQueryRow($query, $res);
		
		$res[0]["CPM_SPPT_DOC_VERSION"] = $vers;
		
		$values = "'".implode("', '", $res[0])."'";
		$query = "INSERT INTO cppmod_pbb_sppt_ext_existing VALUES ($values)";
		
		return $this->dbSpec->sqlQuery($query, $res);
	}
}
?>