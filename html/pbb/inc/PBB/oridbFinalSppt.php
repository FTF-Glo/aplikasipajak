<?php

class DbFinalSppt
{

    private $dbSpec = null;
    public $totalrows = 0;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }

    public function get($id = "", $vers = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key='$value' ";
                else
                    $query .= " $key='$value' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";

        //echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function gets($id = "", $vers = "")
    {
        if (trim($id) != '')
            $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            //$last_key = array_keys($filter);
            //$last_key = end($last_key);

            $x = 0;
            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key='$value' ";
                else
                    $query .= " $key='$value' ";
                if (count($filter) > 1 && (count($filter) - 1) != $x)
                    $query .= " AND ";

                $x++;
            }
        }
        $query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";

        // echo $query; exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getSusulan($id = "", $vers = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";

        // echo $query;exit;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getExt($id, $vers, $num = "", $filter = [])
    {
        $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $filter['CPM_OP_NUM'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));


        $query = "SELECT * FROM cppmod_pbb_sppt_ext_final ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }

        //echo $query; exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getExts($id, $vers, $num = "")
    {
        $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $filter['CPM_OP_NUM'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));


        $query = "SELECT * FROM cppmod_pbb_sppt_ext_final ";

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
                if (count($filter) > 1 && (count($filter) - 1) != $x)
                    $query .= " AND ";

                $x++;
            }
        }

        //echo $query; exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getExtSusulan($id, $vers, $num = "", $filter = [])
    {
        $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $filter['CPM_OP_NUM'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));


        $query = "SELECT * FROM cppmod_pbb_sppt_ext_susulan ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }

        //		echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getExtSusulans($id, $vers, $num = "")
    {
        $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $filter['CPM_OP_NUM'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));


        $query = "SELECT * FROM cppmod_pbb_sppt_ext_susulan ";

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
                if (count($filter) > 1 && (count($filter) - 1) != $x)
                    $query .= " AND ";

                $x++;
            }
        }

        //		echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_by_nop($id = "")
    {
        $res = null;

        $query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '" . $id . "'";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_where($filter = [], $srch = null, $jumlah = null, $perpage = 1, $page = 1)
    {
        $res = null;
        $queryCount = "SELECT count(*) as total FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $queryCount .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if (count($filter) > 1 && $key != $last_key)
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
        $query = "SELECT * FROM cppmod_pbb_sppt_final ";
        // echo $nop2;exit();
        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {

                if ($key == "CPM_NOP" and strlen($value) > 18) {
                    $query .= " $key IN(" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . ") ";
                } else {
                    $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                }
                if (count($filter) > 1 && $key != $last_key)
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

        //var_dump($query);exit();

        /* if (!$jumlah){
          $query .= "LIMIT 10 ";
          }
          else if ($jumlah){
          $query .= "LIMIT $jumlah ";
          } */
        //echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_susulans($filter = [], $srch, $jumlah, $perpage, $page)
    {
        $queryCount = "SELECT count(*) as total FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $queryCount .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if (count($filter) > 1 && $key != $last_key)
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

        $query = "SELECT * FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if (count($filter) > 1 && $key != $last_key)
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
            $query .= " LIMIT $hal, $perpage ";
        }

        // echo $query."<br>";
        //echo $queryCount;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_susulan($filter = [], $srch = null, $jumlah = 0, $perpage = 1, $page = 1)
    {
        $table = "cppmod_pbb_sppt_susulan";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_NOP") {
                    $nop         = explode(',', $filter['CPM_NOP']);
                    $last         = array_keys($nop);
                    $last       = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if (count($nop) > 1 && $key != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $queryCount .= " CPM_NOP IN ($listNOP) ";
                    $query .= " CPM_NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "CPM_NOP") {
                        $queryCount .= " $key = '$value' ";
                        $query .= " $key = '$value' ";
                    } else if ($key == "CPM_SPPT_THN_PENETAPAN !") {
                        $queryCount .= " $key= '$value' ";
                        $query .= " $key= '$value' ";
                    } else {
                        $queryCount .= " $key LIKE '%$value%' ";
                        $query .= " $key LIKE '%$value%' ";
                    }
                    if (count($filter) > 1 && $key != $last_key) {
                        $queryCount .= " AND ";
                        $query .= " AND ";
                    }
                }
            }
        }

        if ($srch) {
            if (count($filter) > 0)
                $query .= " AND ";
            else
                $query .= " WHERE ";
            $query .= " (CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
        }


        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        if ($perpage) {
            $query .= "LIMIT $hal, $perpage ";
        }

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get_where_finalsusulan($filter, $srch, $jumlah, $perpage, $page)
    {

        $queryCount = "SELECT SUM(TOTAL) as total FROM ( ";
        $queryCount .= "SELECT count(*) as total FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $queryCount .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
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

        $queryCount .= "UNION ALL SELECT count(*) as total FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $queryCount .= "WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $queryCount .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if (count($filter) > 1 && $key != $last_key)
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
        $queryCount .= ") TBL ";
        //echo $queryCount;
        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['total'];

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $query = "SELECT * FROM (
                SELECT * FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_NOP" && strlen($value) >= 19) {
                    // count($value);exit;
                    $nop         = explode(',', $value);
                    $last         = array_keys($nop);
                    $last       = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key1 => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if (count($nop) > 1 && $key1 != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $query .= " $key IN(" . $listNOP . ") ";
                } else {
                    $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                }
                if (count($filter) > 1 && $key != $last_key)
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

        $query .= " UNION ALL SELECT * FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_NOP" && strlen($value) >= 19) {
                    // count($value);exit;
                    $nop         = explode(',', $value);
                    $last         = array_keys($nop);
                    $last       = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key1 => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if (count($nop) > 1 && $key1 != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $query .= " $key IN(" . $listNOP . ") ";
                } else {
                    $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                }
                // echo $key." = ".$last_key;
                if (count($filter) > 1 && $key != $last_key)
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
        $query .= ") TBL ";
        if ($perpage) {
            $query .= "LIMIT $hal, $perpage ";
        }

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

        $query = "INSERT INTO cppmod_pbb_sppt_final (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
        $tmpVals = "'$id', '$vers', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if (count($filter) > 1 && $key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        // echo $query;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edit($id, $vers, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        #edit by ardi : untuk mengupdate NJOP TANAH
        if (isset($aValue['CPM_OP_LUAS_TANAH'])) {
            $cari_final = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH from cppmod_pbb_sppt_final
                           WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
            $this->dbSpec->sqlQuery($cari_final, $result);
            if ($final = mysqli_fetch_array($result)) {
                $aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            }
        } #end        

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_sppt_final SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if (count($filter) > 1 && $key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        //echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edits($id, $nop, $aValue)
    {
        $res = null;
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_sppt_final SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $value) . "'";
            if (count($aValue) > 1 && $key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_SPPT_DOC_ID='" . $id . "' and CPM_NOP = '" . $nop . "'";

        //var_dump($query);exit();

        //echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function editFromPersetujuan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));
        $query = "UPDATE cppmod_pbb_sppt_final SET CPM_SPPT_THN_PENETAPAN='0' WHERE CPM_NOP='$nop'";
        //echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function editSusulan($id, $vers, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        #edit by ardi : untuk mengupdate NJOP TANAH
        if (isset($aValue['CPM_OP_LUAS_TANAH'])) {
            $cari_final = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH from cppmod_pbb_sppt_susulan
                           WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
            $this->dbSpec->sqlQuery($cari_final, $result);
            if ($final = mysqli_fetch_array($result)) {
                $aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            }
        } #end  


        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_sppt_susulan SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if (count($filter) > 1 && $key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= "WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        // echo $query;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function del($id, $vers = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delExt($id, $vers = "", $num = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $num = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
        if (trim($num) != '')
            $query .= "AND CPM_OP_NUM='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delSusulan($id, $vers = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delSusulanExt($id, $vers = "", $num = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $num = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));

        $query = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
        if (trim($num) != '')
            $query .= "AND CPM_OP_NUM='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function move($id, $vers = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "INSERT INTO cppmod_pbb_sppt_final SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function moveExt($id, $vers = "", $num = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        if (trim($num) != '')
            $num = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($num));

        $query = "INSERT INTO cppmod_pbb_sppt_ext_final SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
        if (trim($num) != '')
            $query .= "AND CPM_OP_NUM='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function moveToSusulan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $query = "INSERT INTO cppmod_pbb_sppt_susulan SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";
        if ($tahun) {
            $query .= " AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        }
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function moveToSusulanExt($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));
        $query = "INSERT INTO cppmod_pbb_sppt_ext_susulan SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID=(SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun')";
        //echo $query; exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function editThnSusulan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_SPPT_THN_PENETAPAN = '0' WHERE CPM_NOP='$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delFinalExt($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID=(SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun')";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delFinal($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";
        if ($tahun) {
            $query .= " AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        }
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function isExist($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        // echo $query;		
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes > 0);
        }
    }

    public function isNopExist($nop)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";

        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isNopExistInSusulan($nop)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='$nop'";

        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function doResurect($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        //get the SPPT content. Long way to get because field count is not the same
        $aSppt = $this->get($id, $vers, '');
        unset($aSppt[0]['CPM_SPPT_THN_PENETAPAN']);
        $headers = "";
        $vals = "";
        // echo $query;exit();

        foreach ($aSppt[0] as $header => $val) {
            $headers .= $header . ",";
            $vals .= "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val) . "',";
        }
        $headers = substr($headers, 0, strlen($headers) - 1);
        $vals = substr($vals, 0, strlen($vals) - 1);

        $query = "INSERT INTO cppmod_pbb_sppt ($headers) VALUES ($vals)";

        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "INSERT INTO cppmod_pbb_sppt_ext SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function doResurectSusulan($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        //get the SPPT content. Long way to get because field count is not the same
        $aSppt = $this->getSusulan($id, $vers);
        unset($aSppt[0]['CPM_SPPT_THN_PENETAPAN']);
        $headers = "";
        $vals = "";
        foreach ($aSppt[0] as $header => $val) {
            $headers .= $header . ",";
            $vals .= "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val) . "',";
        }
        $headers = substr($headers, 0, strlen($headers) - 1);
        $vals = substr($vals, 0, strlen($vals) - 1);

        $query = "INSERT INTO cppmod_pbb_sppt ($headers) VALUES ($vals)";
        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "INSERT INTO cppmod_pbb_sppt_ext SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function doPurge($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id'";
        // echo $query;exit();
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function doPurgeSusulan($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        $bOK = $this->dbSpec->sqlQuery($query, $res);

        $query = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id'";
        if ($bOK)
            return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getFinal($perpage, $page, $qSearch, $qSearchAlmt)
    {
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
		" . $queryWhere . " 
		UNION ALL 
		SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_KECAMATAN, CPM_OP_KELURAHAN FROM cppmod_pbb_sppt_susulan
		" . $queryWhere . " 
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

    public function moveFinalToHistory($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        $bOK = $this->dbSpec->sqlQuery($qry, $res);
        if (!$bOK) return false;

        $qry = "INSERT INTO cppmod_pbb_sppt_history 
                SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        return $this->dbSpec->sqlQuery($qry, $res);
    }

    public function moveSusulanToHistory($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        $bOK = $this->dbSpec->sqlQuery($qry, $res);
        if (!$bOK) return false;

        $qry = "INSERT INTO cppmod_pbb_sppt_history 
                SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
        return $this->dbSpec->sqlQuery($qry, $res);
    }

    public function get_fasilitas_umum($filter, $srch, $jumlah, $perpage, $page)
    {

        $queryCount = "SELECT count(*) as total FROM cppmod_pbb_sppt_final ";
        $where = " ";
        if (count($filter) > 0) {
            $where .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $where .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if (count($filter) > 1 && $key != $last_key)
                    $where .= " AND ";
            }
        }
        if ($srch) {
            if (count($filter) > 0)
                $where .= " AND ";
            else
                $where .= " WHERE ";
            $where .= " (CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
        }

        $queryCount = "SELECT COUNT(*) as total FROM (
        SELECT CPM_NOP FROM cppmod_pbb_sppt_final " . $where . "  
        UNION ALL
        SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan " . $where . "  
        ) TBL1";
        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['total'];

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $query = "SELECT * FROM (
        SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_ALAMAT, CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_sppt_final " . $where . "  
        UNION ALL
        SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_ALAMAT, CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_sppt_susulan " . $where . "  
        ) TBL1 ORDER BY CPM_NOP ";
        if ($perpage) {
            $query .= "LIMIT $hal, $perpage ";
        }

        //echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }
}
