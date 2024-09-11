<?php

class DbServices
{

    private $dbSpec = null;
    public $totalrows = 0;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }
    public function getDataChange($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $query = "SELECT * FROM cppmod_pbb_service_change WHERE CPM_NOP = '$id' ";
        // echo $query; exit;
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_assoc($res);
        // echo "<pre>";
        // print_r($row); exit;
        return $row;
    }
    public function getDataChangeBySID($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $query = "SELECT * FROM cppmod_pbb_service_change WHERE CPM_SID = '$id' ";
        // echo $query; exit;
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_assoc($res);
        // echo "<pre>";
        // print_r($row); exit;
        return $row;
    }
    public function get($filter = [], $srch = "", $jumhal = "", $perpage = "", $page = "", $additionalWhereQuery = "")
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $query = "SELECT a.*, b.CPC_TKC_KECAMATAN, c.CPC_TKL_KELURAHAN FROM cppmod_pbb_services a
				LEFT JOIN cppmod_tax_kecamatan b on b.CPC_TKC_ID = a.CPM_OP_KECAMATAN 
				LEFT JOIN cppmod_tax_kelurahan c on c.CPC_TKL_ID = a.CPM_OP_KELURAHAN 
				LEFT JOIN cppmod_pbb_service_new_op op ON op.CPM_NEW_SID = a.CPM_ID 
				";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_STATUS" || $key == "CPM_TYPE" || $key == "CPM_ID") {
                    if (is_array($value)) {
                        $tlast_key = array_keys($value);
                        $tlast_key = end($tlast_key);
                        $query .= " ( ";
                        foreach ($value as $tkey => $val) {
                            $query .= " $key = '" . $val . "' ";
                            if ($tkey != $tlast_key) {
                                $query .= " OR ";
                            }
                        }
                        $query .= " ) ";
                    } else {
                        $query .= " $key = '$value' ";
                    }
                } else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        if($srch){
            if(is_numeric($srch)){
                $query .= " AND (CPM_OP_NUMBER='$srch')";
            }else{
                $query .= " AND (CPM_ID LIKE '%$srch%' OR CPM_WP_NAME LIKE '%$srch%')";
            }
        }

        if ($additionalWhereQuery != "") {
            $query .= " " . $additionalWhereQuery . " ";
        }

        $this->dbSpec->sqlQueryRow($query, $total);
        if ($total != null && is_numeric($total)) {
            $this->totalrows = count($total);
        }

        if ($perpage) {
            $query .= " ORDER BY CPM_DATE_RECEIVE DESC LIMIT $hal, $perpage ";
        }
        // echo $query;exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {

            return $res;
        }
    }

    public function get_id($filter = "")
    {
        //$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $query = "SELECT a.*, b.CPC_TKC_KECAMATAN, c.CPC_TKL_KELURAHAN FROM cppmod_pbb_services a
				LEFT JOIN cppmod_tax_kecamatan b on b.CPC_TKC_ID = a.CPM_OP_KECAMATAN 
				LEFT JOIN cppmod_tax_kelurahan c on c.CPC_TKL_ID = a.CPM_OP_KELURAHAN 
				";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_STATUS" || $key == "CPM_TYPE" || $key == "CPM_ID") {
                    if (is_array($value)) {
                        $tlast_key = array_keys($value);
                        $tlast_key = end($tlast_key);
                        $query .= " ( ";
                        foreach ($value as $tkey => $val) {
                            $query .= " $key = '" . $val . "' ";
                            if ($tkey != $tlast_key) {
                                $query .= " OR ";
                            }
                        }
                        $query .= " ) ";
                    } else {
                        $query .= " $key = '$value' ";
                    }
                } else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        /*if ($srch) {
            $query .= " AND (CPM_ID LIKE '%$srch%' OR CPM_WP_NAME LIKE '%$srch%' OR CPM_OP_NUMBER LIKE '%$srch%')";
        }

        if ($additionalWhereQuery != "") {
            $query .= " " . $additionalWhereQuery . " ";
        }*/

        $this->dbSpec->sqlQueryRow($query, $total);
        $this->totalrows = count($total);

        /*if ($perpage) {
            $query .= " ORDER BY CPM_DATE_RECEIVE DESC LIMIT $hal, $perpage ";
        }*/
        // echo $query;exit;
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

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        #echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edit($id, $aVal)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $last_key = array_keys($aVal);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_services SET ";

        foreach ($aVal as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_ID='$id'";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function del($id, $vers = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '')
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '')
            $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

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
        $nop = mysqli_real_escape_string(trim($nop));

        $query = "SELECT a.CPM_SPPT_DOC_ID, a.CPM_SPPT_DOC_VERSION, 
		b.CPM_TRAN_ID, b.CPM_TRAN_REFNUM, b.CPM_TRAN_SPPT_DOC_ID, b.CPM_SPPT_DOC_VERSION, b.CPM_TRAN_STATUS, b.CPM_TRAN_FLAG 
		FROM cppmod_pbb_sppt a 
		INNER JOIN `cppmod_pbb_tranmain` b on a.CPM_SPPT_DOC_ID = b.CPM_TRAN_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION = b.CPM_SPPT_DOC_VERSION 
		AND b.CPM_TRAN_FLAG = '0'
		WHERE a.CPM_NOP = '" . $nop . "'";

        $res = mysqli_query($this->dbSpec->getDBLink(), $query);
        $nRes = mysqli_num_rows($res);
        return $nRes;
    }

    public function insertTransactionFromPendataan($idt, $idService, $newNOP, $uid = '')
    {

        $qrySelect = "SELECT CPM_TYPE FROM cppmod_pbb_services WHERE CPM_ID = '{$idService}' ";
        if ($this->dbSpec->sqlQueryRow($qrySelect, $res)) {
            if ($res[0]['CPM_TYPE'] == '1')
                $query = "INSERT INTO cppmod_pbb_service_new_op (CPM_NEW_ID, CPM_NEW_SID, CPM_NEW_NOP) VALUES ('{$idt}', '{$idService}', '{$newNOP}')";
            else
                $query = "INSERT INTO cppmod_pbb_service_split (CPM_SP_ID, CPM_SP_SID, CPM_SP_NOP) VALUES ('{$idt}', '{$idService}', '{$newNOP}')";

            $bOK = $this->dbSpec->sqlQuery($query, $res);
            if ($bOK) {
                $query = "UPDATE cppmod_pbb_services SET CPM_VALIDATOR = '{$uid}', CPM_DATE_VALIDATE='" . date("Y-m-d") . "' WHERE CPM_ID = '{$idService}' ";
                return $this->dbSpec->sqlQuery($query, $res);
            }
        }
    }

    public function insertTransactionSplit($idt, $idService, $newNOP, $uid = '', $CPM_SP_PENETAPAN_INDUK)
    {

        $qrySelect = "SELECT CPM_TYPE FROM cppmod_pbb_services WHERE CPM_ID = '{$idService}' ";
        if ($this->dbSpec->sqlQueryRow($qrySelect, $res)) {
            $query = "INSERT INTO cppmod_pbb_service_split (CPM_SP_ID, CPM_SP_SID, CPM_SP_NOP,CPM_SP_PENETAPAN_INDUK) VALUES ('{$idt}', '{$idService}', '{$newNOP}','{$CPM_SP_PENETAPAN_INDUK}')";

            $bOK = $this->dbSpec->sqlQuery($query, $res);
            if ($bOK) {
                $query = "UPDATE cppmod_pbb_services SET CPM_VALIDATOR = '{$uid}', CPM_DATE_VALIDATE='" . date("Y-m-d") . "' WHERE CPM_ID = '{$idService}' ";
                return $this->dbSpec->sqlQuery($query, $res);
            }
        }
    }




    public function updateTransactionFromPendataan($nop, $content)
    {
        $queryUpdate = array();
        foreach ($content as $key => $value) {
            $queryUpdate[] = " " . $key . " = '" . $value . "'";
        }
        $queryUpdate = 'SET ' . join($queryUpdate, ', ');

        $query = "UPDATE cppmod_pbb_services " . $queryUpdate . " 
                WHERE CPM_ID IN(
                    SELECT CPM_NEW_SID as SERVICE_ID 
                    FROM cppmod_pbb_service_new_op
                    WHERE CPM_NEW_NOP = '" . $nop . "'
                    UNION ALL 
                    SELECT CPM_SP_SID as SERVICE_ID 
                    FROM cppmod_pbb_service_split
                    WHERE CPM_SP_NOP = '" . $nop . "'
                ) AND CPM_STATUS != '4'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getMutasi($id = "", $filter = "", $custom = "", $perpage = "", $page = "")
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        if (trim($id) != '') {
            $filter['CPM_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        }

        $query = "SELECT * FROM cppmod_pbb_services ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == 'CPM_ID' || $key == 'CPM_STATUS') {
                    if (is_array($value)) {
                        $tlast_key = array_keys($value);
                        $tlast_key = end($tlast_key);
                        $query .= " ( ";
                        foreach ($value as $tkey => $val) {
                            $query .= " $key = '" . $val . "' ";
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
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        $query .= " AND CPM_TYPE IN (4, 5, 8, 3, 7)";

        if ($custom != "") {
            $query .= " AND " . $custom;
        }

        // echo $query;exit;

        $this->dbSpec->sqlQueryRow($query, $total);
        $this->totalrows = count($total);

        if ($perpage) {
            $query .= " LIMIT $hal, $perpage ";
        }

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }


    public function editServices($id, $aValue)
    {

        //        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_services SET ";
        //echo $query;exit;
        foreach ($aValue as $key => $value) {
            $query .= '' . $key . '="' . $value . '"';
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_ID = '$id'";
        // echo $query;
        // exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function editMutasi($id, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_service_mutations SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_MU_SID = '$id'";

        //echo $query; die();

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function addMutasi($id, $sid, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $sid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($sid));

        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
        }

        $query = "INSERT INTO cppmod_pbb_service_mutations (CPM_MU_ID, CPM_MU_SID, ";
        $tmpVals = "'$id', '$sid', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        #echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getWhereMutasi($filter)
    {
        $query = "SELECT * 
                FROM cppmod_pbb_service_mutations
                LEFT JOIN cppmod_pbb_services ON (CPM_MU_SID = CPM_ID) ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        //echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function editSalinan($id, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_service_copy SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_CP_SID = '$id'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function addSalinan($id, $sid, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $sid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($sid));

        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
        }

        $query = "INSERT INTO cppmod_pbb_service_copy (CPM_CP_ID, CPM_CP_SID, ";
        $tmpVals = "'$id', '$sid', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        #echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function editPerubahan($id, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_service_change SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_CH_SID = '$id'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function addPerubahan($id, $sid, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $sid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($sid));

        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
        }

        $query = "INSERT INTO cppmod_pbb_service_change (CPM_CH_ID, CPM_CH_SID, ";
        $tmpVals = "'$id', '$sid', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        #echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getWherePerubahan($filter)
    {
        $query = "SELECT * 
                  FROM cppmod_pbb_service_change ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        //echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function editPenggabungan($id, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_service_merge SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_MG_SID = '$id'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function addPenggabungan($id, $sid, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $sid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($sid));

        foreach ($aValue as $key => $value) {
            $aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink(), $value);
        }

        $query = "INSERT INTO cppmod_pbb_service_merge (CPM_MG_ID, CPM_MG_SID, ";
        $tmpVals = "'$id', '$sid', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        #echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function delPenggabungan($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "DELETE FROM cppmod_pbb_service_merge WHERE CPM_MG_SID='$id' ";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getWherePenggabungan($filter)
    {
        $query = "SELECT * 
                  FROM cppmod_pbb_service_merge ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        //echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function editSpptFinal($nop, $aValue)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $cari_final = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_NJOP_BANGUNAN from cppmod_pbb_sppt_final where CPM_NOP='$nop'";

        $this->dbSpec->sqlQuery($cari_final, $result);
        if ($final = mysqli_fetch_array($result)) {

            //$aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            //$aValue['CPM_NJOP_BANGUNAN'] = $aValue['CPM_OP_LUAS_BANGUNAN'] * ($final['CPM_NJOP_BANGUNAN'] / $final['CPM_OP_LUAS_BANGUNAN']);

            $updateFinal = "UPDATE cppmod_pbb_sppt_final SET ";
            foreach ($aValue as $key => $value) {
                $updateFinal .= "$key='$value',";
            }
            $updateFinal = substr($updateFinal, 0, strlen($updateFinal) - 1);
            $updateFinal .= " WHERE CPM_NOP='$nop'";

            $this->dbSpec->sqlQuery($updateFinal, $res);
        }

        $cari_susulan = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_NJOP_BANGUNAN from cppmod_pbb_sppt_susulan where CPM_NOP='$nop'";
        $this->dbSpec->sqlQuery($cari_susulan, $result);
        if ($susulan = mysqli_fetch_array($result)) {

            //$aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            //$aValue['CPM_NJOP_BANGUNAN'] = $aValue['CPM_OP_LUAS_BANGUNAN'] * ($final['CPM_NJOP_BANGUNAN'] / $final['CPM_OP_LUAS_BANGUNAN']);

            $updateSusulan = "UPDATE cppmod_pbb_sppt_susulan SET ";
            foreach ($aValue as $key => $value) {
                $updateSusulan .= "$key='$value',";
            }
            $updateSusulan = substr($updateSusulan, 0, strlen($updateSusulan) - 1);
            $updateSusulan .= " WHERE CPM_NOP='$nop'";

            $this->dbSpec->sqlQuery($updateSusulan, $res);
        }
        return true;
    }

    public function editSpptFinalPerubahanData($nop, $aValue)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $cari_final = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_NJOP_BANGUNAN from cppmod_pbb_sppt_final where CPM_NOP='$nop'";

        $this->dbSpec->sqlQuery($cari_final, $result);
        if ($final = mysqli_fetch_array($result)) {

            $aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($final['CPM_NJOP_TANAH'] / $final['CPM_OP_LUAS_TANAH']);
            $aValue['CPM_NJOP_BANGUNAN'] = $aValue['CPM_OP_LUAS_BANGUNAN'] * ($final['CPM_NJOP_BANGUNAN'] / $final['CPM_OP_LUAS_BANGUNAN']);

            $updateFinal = "UPDATE cppmod_pbb_sppt_final SET ";
            foreach ($aValue as $key => $value) {
                $updateFinal .= "$key='$value',";
            }
            $updateFinal = substr($updateFinal, 0, strlen($updateFinal) - 1);
            $updateFinal .= " WHERE CPM_NOP='$nop'";
            echo $updateFinal . "<br/>";
            //$this->dbSpec->sqlQuery($updateFinal, $res);
        }

        $cari_susulan = "select CPM_OP_LUAS_TANAH, CPM_NJOP_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_NJOP_BANGUNAN from cppmod_pbb_sppt_susulan where CPM_NOP='$nop'";
        $this->dbSpec->sqlQuery($cari_susulan, $result);
        if ($susulan = mysqli_fetch_array($result)) {

            $aValue['CPM_NJOP_TANAH'] = $aValue['CPM_OP_LUAS_TANAH'] * ($susulan['CPM_NJOP_TANAH'] / $susulan['CPM_OP_LUAS_TANAH']);
            $aValue['CPM_NJOP_BANGUNAN'] = $aValue['CPM_OP_LUAS_BANGUNAN'] * ($susulan['CPM_NJOP_BANGUNAN'] / $susulan['CPM_OP_LUAS_BANGUNAN']);

            $updateSusulan = "UPDATE cppmod_pbb_sppt_susulan SET ";
            foreach ($aValue as $key => $value) {
                $updateSusulan .= "$key='$value',";
            }
            $updateSusulan = substr($updateSusulan, 0, strlen($updateSusulan) - 1);
            $updateSusulan .= " WHERE CPM_NOP='$nop'";
            echo $updateSusulan . "<br/>";
            //$this->dbSpec->sqlQuery($updateSusulan, $res);
        }
        //exit();
        return true;
    }

    public function editSpptCurrent($nop, $aValue)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $cari = "select OP_LUAS_BUMI, OP_NJOP_BUMI,OP_LUAS_BANGUNAN, OP_NJOP_BANGUNAN, 
                 OP_NJOPTKP
                 from cppmod_pbb_sppt_current where NOP='$nop'";

        $this->dbSpec->sqlQuery($cari, $result);
        if ($final = mysqli_fetch_array($result)) {

            /*$aValue['OP_NJOP_BUMI'] = ($final['OP_NJOP_BUMI'] / $final['OP_LUAS_BUMI']) * $aValue['OP_LUAS_BUMI'];
            $aValue['OP_NJOP_BANGUNAN'] = ($final['OP_NJOP_BANGUNAN'] / $final['OP_LUAS_BANGUNAN']) * $aValue['OP_LUAS_BANGUNAN'];

            $aValue['OP_NJOP'] = $aValue['OP_NJOP_BUMI'] + $aValue['OP_NJOP_BANGUNAN'];
            $OP_NJKP = $aValue['OP_NJOP'] - $final['OP_NJOPTKP'];
            $aValue['OP_NJKP'] = ($OP_NJKP < 0) ? 0 : $OP_NJKP;

            $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                            CPM_TRF_NILAI_BAWAH <= " . $aValue['OP_NJKP'] . " AND
                            CPM_TRF_NILAI_ATAS >= " . $aValue['OP_NJKP'];
            $this->dbSpec->sqlQuery($cari_tarif, $resTarif);
            $dataTarif = mysqli_fetch_array($resTarif);
            $op_tarif = $dataTarif['CPM_TRF_TARIF'];
            $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $aValue['OP_NJKP'] * ($op_tarif / 100);*/

            $update = "UPDATE cppmod_pbb_sppt_current SET ";
            foreach ($aValue as $key => $value) {
                $update .= "$key='$value',";
            }
            $update = substr($update, 0, strlen($update) - 1);
            $update .= " WHERE NOP='$nop'";

            $this->dbSpec->sqlQuery($update, $res);
        }
        return true;
    }

    public function editSpptCurrentPerubahanData($nop, $aValue)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $cari = "select OP_LUAS_BUMI, OP_NJOP_BUMI,OP_LUAS_BANGUNAN, OP_NJOP_BANGUNAN, 
                 OP_NJOPTKP
                 from cppmod_pbb_sppt_current where NOP='$nop'";

        $this->dbSpec->sqlQuery($cari, $result);
        if ($final = mysqli_fetch_array($result)) {

            $aValue['OP_NJOP_BUMI'] = ($final['OP_NJOP_BUMI'] / $final['OP_LUAS_BUMI']) * $aValue['OP_LUAS_BUMI'];
            $aValue['OP_NJOP_BANGUNAN'] = ($final['OP_NJOP_BANGUNAN'] / $final['OP_LUAS_BANGUNAN']) * $aValue['OP_LUAS_BANGUNAN'];

            $aValue['OP_NJOP'] = $aValue['OP_NJOP_BUMI'] + $aValue['OP_NJOP_BANGUNAN'];
            $OP_NJKP = $aValue['OP_NJOP'] - $final['OP_NJOPTKP'];
            $aValue['OP_NJKP'] = ($OP_NJKP < 0) ? 0 : $OP_NJKP;

            $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                            CPM_TRF_NILAI_BAWAH <= " . $aValue['OP_NJKP'] . " AND
                            CPM_TRF_NILAI_ATAS >= " . $aValue['OP_NJKP'];
            $this->dbSpec->sqlQuery($cari_tarif, $resTarif);
            $dataTarif = mysqli_fetch_array($resTarif);
            $op_tarif = $dataTarif['CPM_TRF_TARIF'];
            $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $aValue['OP_NJKP'] * ($op_tarif / 100);

            $update = "UPDATE cppmod_pbb_sppt_current SET ";
            foreach ($aValue as $key => $value) {
                $update .= "$key='$value',";
            }
            $update = substr($update, 0, strlen($update) - 1);
            $update .= " WHERE NOP='$nop'";
            $this->dbSpec->sqlQuery($update, $res);
        }
        return true;
    }

    public function editSpptExt($docid, $aValue)
    {
        $docid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($docid));

        $set = "";
        foreach ($aValue as $key => $value) {
            $set .= "$key='$value',";
        }
        $set = substr($set, 0, strlen($set) - 1);
        $where = " WHERE CPM_SPPT_DOC_ID = '$docid'";

        $updateExtFinal = "UPDATE cppmod_pbb_sppt_ext_final SET " . $set . $where;
        $updateExtSusulan = "UPDATE cppmod_pbb_sppt_ext_susulan SET " . $set . $where;
        $this->dbSpec->sqlQuery($updateExtFinal, $res);
        $this->dbSpec->sqlQuery($updateExtSusulan, $res);
        return true;
    }

    public function getWhereSpptFinal($filter)
    {
        $query = "SELECT * 
                  FROM cppmod_pbb_sppt_final ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        $query .= "UNION ALL ";
        $query .= "SELECT * 
                  FROM cppmod_pbb_sppt_susulan ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        // echo $query;
        // die;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function editBgnSpptExt($docid, $no, $aValue)
    {
        $docid = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($docid));
        $no = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($no));
        $aValue = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($aValue));

        $updateExtFinal = "UPDATE cppmod_pbb_sppt_ext_final SET CPM_OP_LUAS_BANGUNAN = '$aValue' WHERE CPM_SPPT_DOC_ID='$docid' AND CPM_OP_NUM = '$no'";
        $updateExtSusulan = "UPDATE cppmod_pbb_sppt_ext_susulan SET CPM_OP_LUAS_BANGUNAN = '$aValue' WHERE CPM_SPPT_DOC_ID='$docid' AND CPM_OP_NUM = '$no'";
        $this->dbSpec->sqlQuery($updateExtFinal, $res);
        $this->dbSpec->sqlQuery($updateExtSusulan, $res);
        return true;
    }

    public function delSpptFinal($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $deleteFinal = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id'";
        $bOK = $this->dbSpec->sqlQuery($deleteFinal, $res);
        if (!$bOK) return false;

        $deleteSusulan = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$id'";
        return $this->dbSpec->sqlQuery($deleteSusulan, $res);
    }

    public function delSpptExt($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        /* Hapus data NOP */
        $deleteExtFinal = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id'";
        $bOK = $this->dbSpec->sqlQuery($deleteExtFinal, $res);
        if (!$bOK) return false;

        $deleteExtSusulan = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID='$id'";
        return $this->dbSpec->sqlQuery($deleteExtSusulan, $res);
    }

    public function delSpptCurrent($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP='$id' ";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function getWhereSpptExt($filter)
    {
        $query = "SELECT COUNT(*) AS OP_NUM 
                  FROM cppmod_pbb_sppt_ext_final ";

        if (count($filter) > 0) {
            $query .= " WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key='" . mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value)) . "' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        //        echo $query; die();

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getReduce($field, $id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $field = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($field));
        $query = "SELECT $field FROM cppmod_pbb_service_reduce WHERE CPM_RE_SID = '$id'";
        //echo $query;
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return $row[$field];
    }

    public function getReduces($id = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $query = "SELECT * FROM cppmod_pbb_service_reduce LEFT JOIN cppmod_pbb_service_lhp
				  WHERE CPM_RE_SID = '$id' 
				  AND CPM_RE_SID = CPM_LHP_SID";
        //echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getObjection($field, $id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $field = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($field));
        $query = "SELECT $field FROM cppmod_pbb_service_objection WHERE CPM_OB_SID = '$id'";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return $row == null ? null : $row[$field];
    }

    public function updateSvcByNop($nop)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "UPDATE cppmod_pbb_services SET CPM_STP = '1' WHERE CPM_OP_NUMBER = '$nop'";
        $res = $this->dbSpec->sqlQuery($query, $result);
        return $res;
    }

    public function getLHP($field, $id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $field = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($field));
        $query = "SELECT $field FROM cppmod_pbb_service_lhp WHERE CPM_LHP_SID = '$id'";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return $row == null ? null : $row[$field];
    }

    public function insertIntoHistory($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        /* Masukkan ke dalam tabel history */
        $qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID ='$id'";
        $bOK = $this->dbSpec->sqlQuery($qry, $res);
        if (!$bOK) return false;

        $qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID ='$id'";
        $bOK = $this->dbSpec->sqlQuery($qry, $res);
        if (!$bOK) return false;

        $qry = "INSERT INTO cppmod_pbb_sppt_history 
                SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID ='$id'";
        $bOK = $this->dbSpec->sqlQuery($qry, $res);
        if (!$bOK) return false;

        $qry = "INSERT INTO cppmod_pbb_sppt_history 
                SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID ='$id'";
        return $this->dbSpec->sqlQuery($qry, $res);
    }
}
