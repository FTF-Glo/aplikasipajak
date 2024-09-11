<?
class DbLog {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function checkLog($arrBefore, $arrAfter){
            $arrLog = array();
            $docId = $arrAfter['CPM_SPPT_DOC_ID'];
            foreach($arrAfter as $key => $value){
                if($key == 'CPM_OP_FOTO' || $key == 'CPM_OP_SKET'){
                    $value = trim($value);
                    $oldValue = isset($arrBefore[$key]) ? trim($arrBefore[$key]):'';
                } else {
                    $value = strtoupper(trim($value));
                    $oldValue = isset($arrBefore[$key]) ? strtoupper(trim($arrBefore[$key])):'';
                }
                
                if((!isset($arrBefore[$key]) && $value != '') || $value != $oldValue)
                   $arrLog[] = array('CPM_SPPT_DOC_ID' => $docId,
                       'CPM_FIELD' => $key,
                       'CPM_VALUE_BEFORE' => $oldValue,
                       'CPM_VALUE_AFTER' => $value); 
            }
            return $arrLog;
        }
	
	public function insertLog($arrLog){
            $query = "INSERT INTO cppmod_pbb_sppt_update_log (CPM_SPPT_DOC_ID, CPM_FIELD, CPM_VALUE_BEFORE, CPM_VALUE_AFTER) VALUES ";

            foreach ($arrLog as $key => $row) {
                    if($key > 0) $query .=',';
                    $query .= "('".$row['CPM_SPPT_DOC_ID']."','".$row['CPM_FIELD']."','".mysql_real_escape_string($row['CPM_VALUE_BEFORE'])."','".mysql_real_escape_string($row['CPM_VALUE_AFTER'])."')";
            }
			// echo $query; exit;
            return $this->dbSpec->sqlQuery($query, $res);
        }
	
	public function deleteLog($id){

            $query = "DELETE FROM cppmod_pbb_sppt_update_log WHERE CPM_SPPT_DOC_ID = '".$id."'";
//            echo $query;
            return $this->dbSpec->sqlQuery($query, $res);
        }
	
	public function processLog($arrBefore, $arrAfter, $spop=1){
            $bOk = true;
            
            if($spop){
                $arrLog = $this->checkLog($arrBefore, $arrAfter);

                if(count($arrLog) > 0){
                    $bOk = $this->deleteLog($arrAfter['CPM_SPPT_DOC_ID']);
                    if($bOk)
                        $bOk = $this->insertLog($arrLog);
                }
            }else{
                $arrLog = $this->checkLogExt($arrBefore, $arrAfter);
                if(count($arrLog) > 0){
                    $bOk = $this->deleteLogExt($arrAfter['CPM_SPPT_DOC_ID'],$arrAfter['CPM_OP_NUM']);
                    if($bOk)
                        $bOk = $this->insertLogExt($arrLog);
                }
                
            }
            return $bOk;
        }
        
        
	
	public function checkLogExt($arrBefore, $arrAfter){
            $arrLog = array();
            $docId = $arrAfter['CPM_SPPT_DOC_ID'];
            $opNum = $arrAfter['CPM_OP_NUM'];
            
            foreach($arrAfter as $key => $value){
                if(($key != 'CPM_SPPT_DOC_ID' && $key != 'CPM_OP_NUM' ) && (!isset($arrBefore[$key]) || $value != $arrBefore[$key]))
                   $arrLog[] = array('CPM_SPPT_DOC_ID' => $docId,
                       'CPM_OP_NUM' => $opNum,
                       'CPM_FIELD' => $key,
                       'CPM_VALUE_BEFORE' => isset($arrBefore[$key])? $arrBefore[$key]:'',
                       'CPM_VALUE_AFTER' => $value); 
            }
            
            return $arrLog;
        }
	
	public function insertLogExt($arrLog){
            $query = "INSERT INTO cppmod_pbb_sppt_update_ext_log (CPM_SPPT_DOC_ID, CPM_OP_NUM, CPM_FIELD, CPM_VALUE_BEFORE, CPM_VALUE_AFTER) VALUES ";

            foreach ($arrLog as $key => $row) {
                    if($key > 0) $query .=',';
                    $query .= "('".$row['CPM_SPPT_DOC_ID']."','".$row['CPM_OP_NUM']."','".$row['CPM_FIELD']."','".mysql_real_escape_string($row['CPM_VALUE_BEFORE'])."','".mysql_real_escape_string($row['CPM_VALUE_AFTER'])."')";
            }

            return $this->dbSpec->sqlQuery($query, $res);
        }
	
	public function deleteLogExt($id, $num=''){
            
            $query = "DELETE FROM cppmod_pbb_sppt_update_ext_log WHERE CPM_SPPT_DOC_ID = '".$id."' ";
            if (trim($num)!='')
                $query .= " AND CPM_OP_NUM = '".$num."'";
//             echo $query;       
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
        public function get($id="", $vers="", $filter="") {
		if (trim($id)!='')
                    {
                    $filter['CPM_SPPT_DOC_ID'] = mysql_real_escape_string(trim($id));
                    }
                    
                //there is no doc version field
                //if (trim($vers)!='') $filter['CPM_SPPT_DOC_VERSION'] = mysql_real_escape_string(trim($vers));
		$query = "SELECT * FROM cppmod_pbb_sppt_update_log ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));
                        
			foreach ($filter as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID" || $key == "CPM_FIELD") 
                                    {
                                    $query .= " $key = '$value' ";
                                    }
				else{
                                    $query .= " $key LIKE '%$value%' ";
                                    }
				if ($key != $last_key) $query .= " AND ";
			}
			
		}
		$query .= " ";
                //echo $query;
                //print_r($filter);
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
        
        public function getExt($id="", $num="", $filters="") {
		if (trim($id)!=''){
                    $filters['CPM_SPPT_DOC_ID'] = mysql_real_escape_string(trim($id));
                }
                if (trim($num)!=''){
                    $filters['CPM_OP_NUM'] = trim($num);
                }
		$query = "SELECT * FROM cppmod_pbb_sppt_update_ext_log ";
		//print_r($filters);
		if (count($filters) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filters));
                        
			foreach ($filters as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID" || $key == "CPM_OP_NUM" || $key == "CPM_FIELD") 
                                    {
                                    $query .= " $key = '$value' ";
                                    }
				else{
                                    $query .= " $key LIKE '%$value%' ";
                                    }
				if ($key != $last_key) $query .= " AND ";
			}
			
		}
		$query .= " ";
                //echo $query;
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
        
        public function getNumExt($id="") {
		$query = "SELECT CPM_OP_NUM FROM cppmod_pbb_sppt_update_ext_log WHERE CPM_SPPT_DOC_ID='".$id."' GROUP BY CPM_SPPT_DOC_ID,CPM_OP_NUM ";
                // echo $query;
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function delete($id){
            $bOk = $this->deleteLog($id);
            $bOk = $this->deleteLogExt($id);
            return $bOk;
        }
        
        public function getJmlBng($id="") {
		
                //there is no doc version field
                $query = "SELECT JML_BANGUNAN FROM (
                    SELECT IFNULL(CPM_VALUE_AFTER,0) AS JML_BANGUNAN, '1' AS URUTAN FROM cppmod_pbb_sppt_update_log WHERE CPM_SPPT_DOC_ID='".$id."' AND
                    CPM_FIELD='CPM_OP_JML_BANGUNAN'
                    UNION ALL
                    SELECT IFNULL(CPM_OP_JML_BANGUNAN,0) AS JML_BANGUNAN, '2' AS URUTAN FROM cppmod_pbb_sppt_existing WHERE CPM_SPPT_DOC_ID = '".$id."' 
                    ) TMP ORDER BY URUTAN ASC ";
		
                if ($this->dbSpec->sqlQueryRow($query, $res)) {
                        return $res[0]['JML_BANGUNAN'];
		}
	}
}
?>