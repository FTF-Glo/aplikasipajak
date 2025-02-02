<?php
class DbFinalSppt {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id="", $vers="", $filter=array()) {
		if (trim($id)!='') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		if (trim($vers)!='') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_final ";
		
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
		
//		 echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
        public function getSusulan($id="", $vers="", $filter=array()) {
		if (trim($id)!='') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		if (trim($vers)!='') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_susulan ";
		
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
		
//		 echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
        
	public function getExt($id, $vers, $num="", $filter="") {
		$filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		$filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($DBLink, trim($vers));
		if (trim($num)!='') $filter['CPM_OP_NUM'] = mysqli_real_escape_string($DBLink, trim($num));

		
		$query = "SELECT * FROM cppmod_pbb_sppt_ext_final ";
		
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
		
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
        public function getExtSusulan($id, $vers, $num="", $filter="") {
		$filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($DBLink, trim($id));
		$filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($DBLink, trim($vers));
		if (trim($num)!='') $filter['CPM_OP_NUM'] = mysqli_real_escape_string($DBLink, trim($num));

		
		$query = "SELECT * FROM cppmod_pbb_sppt_ext_susulan ";
		
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
        
	public function get_where($filter) {
		$query = "SELECT * FROM cppmod_pbb_sppt_final ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				$query .= " $key='".mysqli_real_escape_string($DBLink, trim($value))."' ";
				if ($key != $last_key) $query .= " AND ";
			}
			
		}		
		// echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
        
	public function get_susulan($filter) {
		$query = "SELECT * FROM cppmod_pbb_sppt_susulan ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				$query .= " $key='".mysqli_real_escape_string($DBLink, trim($value))."' ";
				if ($key != $last_key) $query .= " AND ";
			}
			
		}		
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
		
		$query = "INSERT INTO cppmod_pbb_sppt_final (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
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
		
		// echo $query;
		
		return $this->dbSpec->sqlQuery($query, $res);
		
	}
	
	public function edit($id, $vers, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_sppt_final SET ";
		
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
	
        public function editSusulan($id, $vers, $aValue) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_sppt_susulan SET ";
		
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
		
		$query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
        public function delExt($id, $vers="", $num="") {
            $id = mysqli_real_escape_string($DBLink, trim($id));		
            if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));
            if (trim($num)!='') $num = mysqli_real_escape_string($DBLink, trim($num));
            
            $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' ";
            if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
            if (trim($num)!='') $query .= "AND CPM_OP_NUM='$vers' ";
            
            // echo $query;		
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
        public function delSusulan($id, $vers="") {
            $id = mysqli_real_escape_string($DBLink, trim($id));		
            if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));

            $query = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
            if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

            // echo $query;		
            return $this->dbSpec->sqlQuery($query, $res);
	}
        
        public function delSusulanExt($id, $vers="", $num="") {
            $id = mysqli_real_escape_string($DBLink, trim($id));		
            if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));
            if (trim($num)!='') $num = mysqli_real_escape_string($DBLink, trim($num));
            
            $query = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
            if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
            if (trim($num)!='') $query .= "AND CPM_OP_NUM='$vers' ";
            
            // echo $query;		
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
        public function move($id, $vers="") {
            $id = mysqli_real_escape_string($DBLink, trim($id));		
            if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));
            
            $query = "INSERT INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
            if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
            
             // echo $query;		
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
        public function moveExt($id, $vers="", $num="") {
            $id = mysqli_real_escape_string($DBLink, trim($id));		
            if (trim($vers)!='') $vers = mysqli_real_escape_string($DBLink, trim($vers));
            if (trim($num)!='') $num = mysqli_real_escape_string($DBLink, trim($num));
            
            $query = "INSERT INTO cppmod_pbb_sppt_ext_final SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
            if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
            if (trim($num)!='') $query .= "AND CPM_OP_NUM='$vers' ";
            
            // echo $query;		
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
	public function isExist($id, $vers) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		
		// echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
	
	public function isNopExist($nop) {
		$id = mysqli_real_escape_string($DBLink, trim($nop));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";
			
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes == 1);
		}
	}
	
	public function doResurect($id, $vers) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		//get the SPPT content. Long way to get because field count is not the same
		$aSppt = $this->get($id, $vers);
		unset($aSppt[0]['CPM_SPPT_THN_PENETAPAN']);
		$headers = ""; $vals="";
		foreach ($aSppt[0] as $header => $val) {
			$headers .= $header.",";
			$vals .= "'".$val."',";
		}
		$headers = substr($headers,0,strlen($headers)-1);
		$vals = substr($vals,0,strlen($vals)-1);
		
		$query = "INSERT INTO cppmod_pbb_sppt ($headers) VALUES ($vals)";
		$bOK = $this->dbSpec->sqlQuery($query, $res);
		
		$query = "INSERT INTO cppmod_pbb_sppt_ext SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";	
		if ($bOK) 
			return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function doPurge($id, $vers) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		$bOK = $this->dbSpec->sqlQuery($query, $res);
		
		$query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id'";
		if ($bOK) 
			return $this->dbSpec->sqlQuery($query, $res);
	}
        
        public function doPurgeSusulan($id, $vers) {
		$id = mysqli_real_escape_string($DBLink, trim($id));
		$vers = mysqli_real_escape_string($DBLink, trim($vers));
		
		$query = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		$bOK = $this->dbSpec->sqlQuery($query, $res);
		
		$query = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id'";
		if ($bOK) 
			return $this->dbSpec->sqlQuery($query, $res);
	}
}
?>