<?php

class DbUtils {

    private $dbSpec = null;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }

    public function getUserDetailPbb($uid = "", $filter = array()) {
        if (trim($uid) != '')
            $filter['ctr_u_id'] = mysqli_real_escape_string($DBLink, trim($uid));

        $query = "SELECT * FROM TBL_REG_USER_PBB ";

        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "ctr_u_id")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        //echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKabKota($id = "", $filter = array()) {
        if (trim($id) != '')
            $filter['CPC_TK_ID'] = mysqli_real_escape_string($DBLink, trim($id));

        $query = "SELECT * FROM cppmod_tax_kabkota ";
        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TK_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TK_KABKOTA ASC";
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKecamatan($id = "", $filter = array()) {
        if (trim($id) != '')
            $filter['CPC_TKC_ID'] = mysqli_real_escape_string($DBLink, trim($id));

        $query = "SELECT * FROM cppmod_tax_kecamatan ";
        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TKC_KECAMATAN ASC";
//        echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKelurahan($id = "", $filter = array()) {
        if (trim($id) != '')
            $filter['CPC_TKL_ID'] = mysqli_real_escape_string($DBLink, trim($id));

        $query = "SELECT * FROM cppmod_tax_kelurahan ";
        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKL_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TKL_KELURAHAN ASC";
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }
   
    public function getKecamatanNama($kode) {
		$query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '".$kode."';";
		$this->dbSpec->sqlQuery($query, $res);
		$row=mysqli_fetch_array($res);
		return $row['CPC_TKC_KECAMATAN'];
	}
	
    public function getKelurahanNama($kode) {
		$query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '".$kode."';";
		$this->dbSpec->sqlQuery($query, $res);
		$row=mysqli_fetch_array($res);
		return $row['CPC_TKL_KELURAHAN'];
	}

    public function getKelOnKota($id, $filter = array()) {
        $id = mysqli_real_escape_string($DBLink, trim($id));

        $query = "SELECT * from cppmod_tax_kelurahan WHERE CPC_TKL_KCID IN (
                        SELECT CPC_TKC_ID FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID=\"$id\"
                ) ";
        
        if (count($filter) > 0) {
            $query .="AND ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKL_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        
        $query .= "ORDER BY CPC_TKL_KELURAHAN ASC";
         // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getProv($id = "", $filter = array()) {
        if (trim($id) != '')
            $filter['CPC_TP_ID'] = mysqli_real_escape_string($DBLink, trim($id));

        $query = "SELECT * FROM cppmod_tax_propinsi ";
        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TP_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

}

?>