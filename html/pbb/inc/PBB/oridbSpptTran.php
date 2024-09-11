<?php
class DbSpptTran
{
	private $dbSpec = null;
	public $totalrows = 0;

	public function __construct($dbSpec)
	{
		$this->dbSpec = $dbSpec;
	}

	public function get($id = "", $filter = [])
	{
		$res = null;
		// echo "<pre>";
		// die(var_dump($this->dbSpec->getDBLink()));
		if (trim($id) != '') {
			$filter['CPM_TRAN_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		}
		$query = "SELECT * FROM cppmod_pbb_tranmain ";
		// var_dump( mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id)));
		if (count($filter) > 0) {
			$query .= "WHERE ";
			$last_key = array_keys($filter);
			$last_key = end($last_key);

			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$last_key = array_keys($value);
						$last_key = end($last_key);
						$query .= " ( ";
						foreach ($value as $tkey => $val) {
							$query .= " $key = '" . $val . "' ";
							if (count($value) > 1 && $tkey != $last_key) {
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
				if (count($value) > 1 && $key != $last_key) $query .= " AND ";
			}
		}
		// echo $query; exit;

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function gets($id = "")
	{
		$res = null;
		$filter = [];
		if (trim($id) != '') {
			$filter['CPM_TRAN_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		}

		$query = "SELECT * FROM cppmod_pbb_tranmain ";

		if (count($filter) > 0) {
			$query .= "WHERE ";
			$x = 0;
			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$last_key = array_keys($value);
						$last_key = end($last_key);
						$query .= " ( ";
						foreach ($value as $tkey => $val) {
							$query .= " $key = '" . $val . "' ";
							if (count($value) > 1 && $tkey != $last_key) {
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
				if (($value || (is_array($value) && count($value) > 1)) && (count($filter) - 1) != $x) $query .= " AND ";

				$x++;
			}
		}

		// echo $query; exit;

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function get_by_nop($id = "")
	{
		$res = null;
		/*$filter = [];
		if (trim($id) != '') {
			$filter['CPM_NOP'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		}*/

		$query = "SELECT * FROM cppmod_pbb_tranmain as A, cppmod_pbb_sppt as B WHERE A.CPM_TRAN_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID AND A.CPM_SPPT_DOC_VERSION=B.CPM_SPPT_DOC_VERSION AND CPM_TRAN_FLAG LIKE '%0%' AND B.CPM_NOP = '" . $id . "'";

		/*if (count($filter) > 0) {
			$query .= "WHERE ";
			$x = 0;
			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$last_key = array_keys($value);
						$last_key = end($last_key);
						$query .= " ( ";
						foreach ($value as $tkey => $val) {
							$query .= " $key = '" . $val . "' ";
							if (count($value) > 1 && $tkey != $last_key) {
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
				if (($value || (is_array($value) && count($value) > 1)) && (count($filter) - 1) != $x) $query .= " AND ";

				$x++;
			}
		}*/

		// echo $query; exit;

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function getDetail($id = "", $filter = [], $custom = "", $jumhal, $perpage, $page)
	{
		$res = null;
		$total = 0;

		if (trim($id) != '') $filter['CPM_TRAN_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

		$queryCount = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_tranmain as A, cppmod_pbb_sppt as B ";

		$query = "SELECT * FROM cppmod_pbb_tranmain as A, cppmod_pbb_sppt as B ";

		$whereClause = "WHERE A.CPM_TRAN_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID AND A.CPM_SPPT_DOC_VERSION=B.CPM_SPPT_DOC_VERSION ";

		if (count($filter) > 0) {
			$whereClause .= "AND ";
			$last_key = array_keys($filter);
			$last_key = end($last_key);

			foreach ($filter as $key => $value) {
				if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
					if (is_array($value)) {
						$tlast_key = array_keys($value);
						$tlast_key = end($tlast_key);
						$whereClause .= " ( ";
						foreach ($value as $tkey => $val) {
							$whereClause .= " $key = '" . $val . "' ";
							if (count($value) > 1 && $tkey != $tlast_key) {
								$whereClause .= " OR ";
							}
						}
						$whereClause .= " ) ";
					} else {
						$whereClause .= " $key = '$value' ";
					}
				} else {
					$whereClause .= " $key LIKE '%$value%' ";
				}
				if ($key != $last_key) $whereClause .= " AND ";
			}
		}

		if ($custom != "") {
			$whereClause .= "AND " . $custom;
		}
		$queryCount .= $whereClause;
		$this->dbSpec->sqlQueryRow($queryCount, $total);
		$this->totalrows = $total[0]['TOTAL'];

		$query .= $whereClause;
		if ($perpage) {
			$query .= " LIMIT $hal, $perpage ";
		}

		/* if (!$jumhal){
			$query .= "LIMIT 10 ";
		} 
		else if ($jumhal){
			$query .= "LIMIT $jumhal ";
		} */

		//echo $query; exit();

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function move($id, $vers = "")
	{
		$res = null;

		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		if (trim($vers) != '')
			$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		//$querycheck = "SELECT FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID = '" . $id . "'";
		//if (count($querycheck) == 0) {
		$query = "INSERT INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='" . $id . "'";
		//} else {
		//$query = "REPLACE INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='" . $id . "'";
		//}

		//$query = "INSERT INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers) != '')
			$query .= " AND CPM_SPPT_DOC_VERSION='" . $vers . "' ";

		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function add($id, $aValue)
	{
		$res = null;
		// $aValue = [];
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
		}

		$query = "REPLACE INTO cppmod_pbb_tranmain (CPM_TRAN_ID, ";
		$tmpVals = "'$id', ";
		$last_key = array_keys($aValue);
		$last_key = end($last_key);

		foreach ($aValue as $key => $value) {
			if (!is_string($key)) continue;
			$query .= $key;
			$tmpVals .= "'" . $value . "'";

			if (count($aValue) > 1 && $key != $last_key) {
				$query .= ", ";
				$tmpVals .= ", ";
			}
		}
		$query .= ") values (" . $tmpVals . ")";
		// echo $query; exit;
		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function edit($id, $aValue)
	{
		$res = null;
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

		$last_key = array_keys($aValue);
		$last_key = end($last_key);
		$query = "UPDATE cppmod_pbb_tranmain SET ";

		foreach ($aValue as $key => $value) {
			if (!is_string($key)) continue;
			$query .= "$key='$value'";
			if (count($aValue) > 1 && $key != $last_key) {
				$query .= ", ";
			}
		}

		$query .= " WHERE CPM_TRAN_ID='$id'";

		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function del($id = "", $refnum = "")
	{
		$res = null;
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

		$query = "DELETE FROM cppmod_pbb_tranmain WHERE ";
		if ($id != "") $query .= "CPM_TRAN_ID='$id' ";
		if ($id != "" && $refnum != "") $query .= "AND ";
		if ($refnum != "") $query .= "CPM_TRAN_REFNUM='$refnum' ";

		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
}
