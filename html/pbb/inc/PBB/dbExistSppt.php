<?

class DbExistSppt {

    private $dbSpec = null;
    public $totalrows = 0;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }

    public function get($id = "", $vers = "", $filter = array()) {
        if (trim($id) != '')
            $filter['CPM_SPPT_DOC_ID'] = mysql_real_escape_string(trim($id));
        if (trim($vers) != '')
            $filter['CPM_SPPT_DOC_VERSION'] = mysql_real_escape_string(trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_existing ";

        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key='$value' ";
                else
                    $query .= " $key='$value' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= " ORDER BY CPM_NOP DESC";

         // echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getExt($id, $vers, $num = "", $filter = "") {
        $filter['CPM_SPPT_DOC_ID'] = mysql_real_escape_string(trim($id));
        $filter['CPM_SPPT_DOC_VERSION'] = mysql_real_escape_string(trim($vers));
        if (trim($num) != '')
            $filter['CPM_OP_NUM'] = mysql_real_escape_string(trim($num));


        $query = "SELECT * FROM cppmod_pbb_sppt_ext_final ";

        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        //echo $query; exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_where($filter, $srch, $jumlah, $perpage, $page) {

        $queryCount = "SELECT count(*) as total FROM cppmod_pbb_sppt_existing ";

        if (count($filter) > 0) {
            $queryCount .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == 'CPM_OP_ALAMAT') $queryCount .= " $key like '%" . mysql_real_escape_string(trim($value)) . "%' ";
                else $queryCount .= " $key='" . mysql_real_escape_string(trim($value)) . "' ";
                
                if ($key != $last_key)
                    $queryCount .= " AND ";
            }
        }
        if ($srch) {
            if (count($filter) > 0)
                $queryCount .= " AND ";
            else
                $queryCount .= " WHERE ";
            $queryCount .= " (CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
        }

        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['total'];

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $query = "SELECT * FROM cppmod_pbb_sppt_existing ";

        if (count($filter) > 0) {
            $query .="WHERE ";
            $last_key = end(array_keys($filter));

            foreach ($filter as $key => $value) {
                if ($key == 'CPM_OP_ALAMAT') $query .= " $key like '%" . mysql_real_escape_string(trim($value)) . "%' ";
                else $query .= " $key='" . mysql_real_escape_string(trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        if ($srch) {
            if (count($filter) > 0)
                $query .= " AND ";
            else
                $query .= " WHERE ";
            $query .= " (CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
        }

        if ($perpage) {
            $query .= "LIMIT $hal, $perpage ";
        }

         //echo $query.'<br>';
	 //echo $queryCount;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function add($id, $vers, $aValue) {
        $id = mysql_real_escape_string(trim($id));
        $vers = mysql_real_escape_string(trim($vers));

        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysql_real_escape_string($value);
        }

        $query = "INSERT INTO cppmod_pbb_sppt_final (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
        $tmpVals = "'$id', '$vers', ";
        $last_key = end(array_keys($aValue));

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        // echo $query;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edit($id, $vers, $aValue) {
        $id = mysql_real_escape_string(trim($id));
        $vers = mysql_real_escape_string(trim($vers));

        #edit by ardi : untuk mengupdate NJOP TANAH
        if (isset($aValue['CPM_OP_LUAS_TANAH'])) {
            $cari_final = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH from cppmod_pbb_sppt_final
                           WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
            $this->dbSpec->sqlQuery($cari_final, $result);
            if ($final = mysqli_fetch_array($result)) {
                $aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            }
        }#end        
        
        $last_key = end(array_keys($aValue));
        $query = "UPDATE cppmod_pbb_sppt_final SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        //echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function del($id, $vers = "") {
        $id = mysql_real_escape_string(trim($id));
        if (trim($vers) != '')
            $vers = mysql_real_escape_string(trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delExt($id, $vers = "", $num = "") {
        $id = mysql_real_escape_string(trim($id));
        if (trim($vers) != '')
            $vers = mysql_real_escape_string(trim($vers));
        if (trim($num) != '')
            $num = mysql_real_escape_string(trim($num));

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
        if (trim($num) != '')
            $query .= "AND CPM_OP_NUM='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function move($id, $vers = "") {
        $id = mysql_real_escape_string(trim($id));
        if (trim($vers) != '')
            $vers = mysql_real_escape_string(trim($vers));

        $query = "INSERT INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function moveExt($id, $vers = "", $num = "") {
        $id = mysql_real_escape_string(trim($id));
        if (trim($vers) != '')
            $vers = mysql_real_escape_string(trim($vers));
        if (trim($num) != '')
            $num = mysql_real_escape_string(trim($num));

        $query = "INSERT INTO cppmod_pbb_sppt_ext_final SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
        if (trim($num) != '')
            $query .= "AND CPM_OP_NUM='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delExistExt($nop, $tahun) {
        $nop = mysql_real_escape_string(trim($nop));
        $tahun = mysql_real_escape_string(trim($tahun));

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID=(SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun')";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delExist($nop, $tahun) {
        $nop = mysql_real_escape_string(trim($nop));
        $tahun = mysql_real_escape_string(trim($tahun));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";
        if ($tahun) {
            $query .= " AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        }
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function isExist($id, $vers) {
        $id = mysql_real_escape_string(trim($id));
        $vers = mysql_real_escape_string(trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        // echo $query;		
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes > 0);
        }
    }

    public function isNopExist($nop) {
        $id = mysql_real_escape_string(trim($nop));

        $query = "SELECT * FROM cppmod_pbb_sppt_existing WHERE CPM_NOP='$nop'";
        
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function doResurect($id, $vers) {
        $id = mysql_real_escape_string(trim($id));
        $vers = mysql_real_escape_string(trim($vers));

        //get the SPPT content. Long way to get because field count is not the same
        $aSppt = $this->get($id, $vers);
        unset($aSppt[0]['CPM_SPPT_THN_PENETAPAN']);
        $headers = "";
        $vals = "";
        foreach ($aSppt[0] as $header => $val) {
            $headers .= $header . ",";
            $vals .= "'" . $val . "',";
        }
        $headers = substr($headers, 0, strlen($headers) - 1);
        $vals = substr($vals, 0, strlen($vals) - 1);

        $query = "INSERT INTO cppmod_pbb_sppt ($headers) VALUES ($vals)";
        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "INSERT INTO cppmod_pbb_sppt_ext SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function doPurge($id, $vers) {
        $id = mysql_real_escape_string(trim($id));
        $vers = mysql_real_escape_string(trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id'";
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getFinal($perpage, $page, $qSearch, $qSearchAlmt) {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $queryWhere = "";

        if (($qSearch) && ($qSearchAlmt)) {
            $queryWhere .= " WHERE CPM_WP_NAMA LIKE '%$qSearch%' AND CPM_OP_ALAMAT LIKE '%$qSearchAlmt%'";
        } else if (($qSearch) || ($qSearchAlmt)) {
            $queryWhere .= " WHERE";
            if ($qSearch) {
                $queryWhere .= " CPM_WP_NAMA LIKE '%$qSearch%'";
            } else if ($qSearchAlmt) {
                $queryWhere .= " CPM_OP_ALAMAT LIKE '%$qSearchAlmt%'";
            }
        }
		
		$query = "SELECT A.*, B.CPC_TKC_KECAMATAN, C.CPC_TKL_KELURAHAN  FROM (
		SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_KECAMATAN, CPM_OP_KELURAHAN FROM cppmod_pbb_sppt_final
		".$queryWhere." 
		UNION ALL 
		SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_KECAMATAN, CPM_OP_KELURAHAN FROM cppmod_pbb_sppt_susulan
		".$queryWhere." 
		) A LEFT JOIN
		cppmod_tax_kecamatan B ON B.CPC_TKC_ID=A.CPM_OP_KECAMATAN LEFT JOIN
		cppmod_tax_kelurahan C ON C.CPC_TKL_ID=A.CPM_OP_KELURAHAN ";

        if ($perpage) {
            $query .= " LIMIT $hal, $perpage ";
        }
        
        $this->dbSpec->sqlQueryRow($query, $total);
        $this->totalrows = count($total);

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            if ($res) {
                return $res;
            } else {
                echo "Data tidak ditemukan!";
            }
        }
    }

}

?>