<?php
class DbSppt
{
	private $dbSpec = null;

	public function __construct($dbSpec)
	{
		$this->dbSpec = $dbSpec;
	}

	public function get($id = "", $vers = '', $filter = [])
	{
		if (trim($id) != '') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		if (trim($vers) != '') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$query = "SELECT * FROM cppmod_pbb_sppt ";

		if (count($filter) > 0) {
			$query .= "WHERE ";
			$last_key = array_keys($filter);
			$last_key = end($last_key);

			foreach ($filter as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID")
					$query .= " $key = '$value' ";
				else
					$query .= " $key LIKE '%$value%' ";
				if (count($filter) > 1 && $key != $last_key) $query .= " AND ";
			}
		}
		$query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";
		// echo $query;exit;

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function gets($id = "", $vers = "")
	{
		if (trim($id) != '') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		if (trim($vers) != '') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$query = "SELECT * FROM cppmod_pbb_sppt ";

		if (count($filter) > 0) {
			$query .= "WHERE ";
			//$last_key = array_keys($filter);
			//$last_key = end($last_key);

			$x = 0;
			foreach ($filter as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID")
					$query .= " $key = '$value' ";
				else
					$query .= " $key LIKE '%$value%' ";
				if (count($filter) > 1 && (count($filter) - 1) != $x) $query .= " AND ";

				$x++;
			}
		}

		$query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";

		//var_dump($query);exit();
		// echo $query;

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function add($id, $vers, $aValue)
	{
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
		}

		$query = "INSERT INTO cppmod_pbb_sppt (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
		$tmpVals = "'$id', '$vers', ";
		$last_key = array_keys($aValue);
		$last_key = end($last_key);

		foreach ($aValue as $key => $value) {
			$query .= $key;
			$tmpVals .= "'" . $value . "'";

			if (count($aValue) > 1 && $key != $last_key) {
				$query .= ", ";
				$tmpVals .= ", ";
			}
		}
		$query .= ") values (" . $tmpVals . ")";

		//var_dump($query);exit();

		#echo $query;exit;

		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function edit($id, $vers, $aValue)
	{
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
		
		$queryFormat = "UPDATE cppmod_pbb_sppt SET %s WHERE CPM_SPPT_DOC_ID='{$id}' AND CPM_SPPT_DOC_VERSION='{$vers}'";
		
		$sets = [];
		
		foreach ($aValue as $key => $value) {
			$value = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
			$sets[] = "{$key} = '{$value}'";
		}
		
		return $this->dbSpec->sqlQuery(sprintf($queryFormat, implode(', ', $sets)), $res);
		
		
		// OLD
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$last_key = array_keys($aValue);
		$last_key = end($last_key);
		$query = "UPDATE cppmod_pbb_sppt SET ";

		foreach ($aValue as $key => $value) {
			$query .= "$key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $value) . "'";
			if (count($aValue) > 1 && $key != $last_key) {
				$query .= ", ";
			}
		}

		$query .= " WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

		//var_dump($query);exit();

		//echo $query;exit;

		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function edits($id, $aValue)
	{
		$res = null;
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

		$last_key = array_keys($aValue);
		$last_key = end($last_key);
		$query = "UPDATE cppmod_pbb_sppt SET ";

		foreach ($aValue as $key => $value) {
			$query .= "$key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $value) . "'";
			if (count($aValue) > 1 && $key != $last_key) {
				$query .= ", ";
			}
		}

		$query .= " WHERE CPM_SPPT_DOC_ID='$id'";

		//var_dump($query);exit();

		//echo $query;exit;

		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function del($id, $vers = "")
	{
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		if (trim($vers) != '') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$query = "DELETE FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers) != '') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function isExist($id, $vers)
	{
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$query = "SELECT * FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

		#echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}

	public function isExist_NOP($nop)
	{
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

		$query = "SELECT a.CPM_SPPT_DOC_ID, a.CPM_SPPT_DOC_VERSION, 
		b.CPM_TRAN_ID, b.CPM_TRAN_REFNUM, b.CPM_TRAN_SPPT_DOC_ID, b.CPM_SPPT_DOC_VERSION, b.CPM_TRAN_STATUS, b.CPM_TRAN_FLAG 
		FROM cppmod_pbb_sppt a 
		INNER JOIN `cppmod_pbb_tranmain` b on a.CPM_SPPT_DOC_ID = b.CPM_TRAN_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION = b.CPM_SPPT_DOC_VERSION 
		AND b.CPM_TRAN_FLAG = '0'
		WHERE a.CPM_NOP = '" . $nop . "';";
		// echo $query;		
		$res = mysqli_query($this->dbSpec->getDBLink(), $query);
		$nRes = mysqli_num_rows($res);
		return $nRes;
	}

	public function getAnggota($nopInduk, $nopAnggota)
	{

		$query = "SELECT CPM_LUAS_BUMI_BEBAN, CPM_LUAS_BNG_BEBAN, CPM_NILAI_SISTEM_BUMI_BEBAN, CPM_NILAI_SISTEM_BNG_BEBAN, CPM_NJOP_BUMI_BEBAN, CPM_NJOP_BNG_BEBAN, CPM_KELAS_BUMI_BEBAN, CPM_KELAS_BNG_BEBAN FROM cppmod_pbb_sppt_anggota WHERE CPM_NOP_INDUK = '{$nopInduk}' AND CPM_NOP = '{$nopAnggota}' ";

		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}

	public function addAnggota($nopInduk, $nopAnggota)
	{
		$query = "INSERT INTO cppmod_pbb_sppt_anggota (CPM_NOP_INDUK, CPM_NOP) VALUES ('{$nopInduk}', '{$nopAnggota}') ";
		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function delAnggota($nopInduk, $nopAnggota)
	{

		$query = "DELETE FROM cppmod_pbb_sppt_anggota WHERE CPM_NOP_INDUK = '{$nopInduk}' AND CPM_NOP = '{$nopAnggota}' ";

		return $this->dbSpec->sqlQuery($query, $res);
	}

	public function getNoUrut($nop, $uname)
	{
		//            $sql = "select max(SUBSTRING(CPM_NOP,-5,4)) as CPM_NOP FROM cppmod_pbb_generate_nop where SUBSTRING(CPM_NOP,1,13)=SUBSTRING('$nop',1,13)";
		//            $this->dbSpec->sqlQueryRow($sql, $res);
		////            return $res[0]['CPM_NOP'];
		//            
		//            $enop = substr($nop, 17,1);
		//            $lastNoUrut = (int) $res[0]['CPM_NOP'];
		//            if ($lastNoUrut >= 3000) {
		//                $nourut = str_pad($lastNoUrut + 1, 3, "0", STR_PAD_LEFT);
		//            } else {
		//                $nourut = "3" . str_pad($lastNoUrut + 1, 3, "0", STR_PAD_LEFT);
		//            }
		//            
		//            $date = date("Y-m-d");
		//            $nopComp = substr($nop, 1,14) . $nourut . substr($nop, 17,1);
		//            $sql = "insert into cppmod_pbb_generate_nop values ('{$nopComp}','{$uname}','{$date}')";
		//            if ($this->dbSpec->sqlQuery($sql, $res)) {
		//                return $res;
		//            }
		//            
		//            return 
	}

	public function checkNOP($nop, $uname)
	{
		$hasil = false;

		$sql = "select CPM_NOP FROM cppmod_pbb_generate_nop where CPM_NOP='$nop'";
		$this->dbSpec->sqlQuery($sql, $res);
		if (mysqli_num_rows($res) == 0) {
			$date = date("Y-m-d");
			$insert = "insert into cppmod_pbb_generate_nop values ('{$nop}','{$uname}','{$date}')";
			$this->dbSpec->sqlQuery($insert, $res);
			$hasil = true;
		}
		return $hasil;
	}

	public function movePBBSPPTToHistory($id, $vers)
	{
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
					SELECT * FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

		$bOK = $this->dbSpec->sqlQuery($qry, $res);
		if (!$bOK) return false;

		$qry = "INSERT INTO cppmod_pbb_sppt_history 
					SELECT *,'' FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		// echo $qry; exit;
		return $this->dbSpec->sqlQuery($qry, $res);
	}

	public function get_sertifikat($nop)
	{
		$nop = (int)$nop;
		$query = "SELECT * FROM cppmod_pbb_sppt_sertifikat WHERE CPM_NOP='$nop' ORDER BY CPM_DATE_UPDATE DESC, CPM_DATE_CREATED DESC";
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
		return array( array() );
	}

	public function update_sertifikat($nop, $se)
	{
		$nop 	= (int)$nop;
		$res 	= [];
		$now 	= date('Y-m-d H:i:s');
		$nomor 	= $se['CPM_NOMOR_SERTIFIKAT'];
		$tgl 	= $se['CPM_TANGGAL'];
		$nama 	= $se['CPM_NAMA_SERTIFIKAT'];
		$js 	= $se['CPM_JENIS_HAK'];
		$nama2 	= $se['CPM_NAMA_PEMEGANG'];
		$query 	= "SELECT * FROM cppmod_pbb_sppt_sertifikat WHERE CPM_NOP='$nop'";
		$this->dbSpec->sqlQueryRow($query, $res);
		if(count($res)>0){
			$query =   "UPDATE cppmod_pbb_sppt_sertifikat 
						SET 
							CPM_NOMOR_SERTIFIKAT='$nomor',
							CPM_TANGGAL='$tgl',
							CPM_NAMA_SERTIFIKAT='$nama',
							CPM_JENIS_HAK='$js',
							CPM_NAMA_PEMEGANG='$nama2',
							CPM_DATE_UPDATE='$now'
						WHERE CPM_NOP='$nop'";
			$this->dbSpec->sqlQuery($query, $res);
		}else{
			$query="INSERT INTO cppmod_pbb_sppt_sertifikat 
					(CPM_NOP,CPM_NOMOR_SERTIFIKAT,CPM_TANGGAL,CPM_NAMA_SERTIFIKAT,CPM_JENIS_HAK,CPM_NAMA_PEMEGANG,CPM_DATE_CREATED) 
					VALUES ('$nop', '$nomor', '$tgl', '$nama', '$js', '$nama2', '$now')";
			$this->dbSpec->sqlQuery($query, $res);
		}
	}
}
