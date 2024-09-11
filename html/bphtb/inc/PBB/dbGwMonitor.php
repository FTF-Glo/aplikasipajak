<?php

class DbGwMonitor {

    private $dbSpec = null;
    private $sTbl_name = "";
    private $aWhere = "";
    private $aLike = "";
    private $sOrder_by = "";
    private $aGroup_by = "";
    private $aQuerylog = array();
	private $conn = NULL;
	private $arrCon = array();
	private $dbtype = "";
	
    public function __construct($dbSpec, $kdKotaKab, $arrConn, $dbtype) {
        $this->dbSpec = $dbSpec;
        $this->like(array("nop" => mysqli_real_escape_string($DBLink, trim($kdKotaKab))));
        $this->sTbl_name = "PBB.PBB_SPPT";
		$this->arrCon = $arrConn;
		if ($dbtype=="postgres") $this->connectToPostgres();
		$this->dbtype = $dbtype;
    }
    
	public function connectToPostgres() {
		$host = $this->arrCon['HOST'];
		$port = $this->arrCon['PORT'];
		$dbname = $this->arrCon['DBNAME'];
		$user = $this->arrCon['USER'];
		$pass = $this->arrCon['PASS'];
		
		$this->conn = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}")
     		 or die ("Error Connecting : " . pg_last_error($conn)); 
	}
	
    public function where($where) {
        foreach ($where as $key => $value) {
            $this->aWhere[$key] = $value;
        }
    }

    public function like($like) {
        foreach ($like as $key => $value) {
            $this->aLike[$key] = $value;
        }
    }

    public function order_by($order_by, $opt = "") {
        $order_by = mysqli_real_escape_string($DBLink, trim($order_by));
        $opt = mysqli_real_escape_string($DBLink, trim($opt));

        $this->sOrder_by .= " ORDER BY " . $order_by . " " . $opt;
    }

    public function group_by($group_by) {
        if (is_array($group_by)) {
            foreach ($group_by as $value) {
                $this->aGroup_by[] = $value;
            }
        } else {
            $this->aGroup_by[] = $group_by;
        }
    }

    public function last_query() {
        return end(array_values($this->aQuerylog));
    }

    public function querylog() {
        return $this->aQuerylog;
    }

    public function get($field = array()) {
		$t = false;
        $field = (count($field) > 0) ? implode(", ", $field) : "*";
        $query = "SELECT $field FROM " . $this->sTbl_name;

        //WHERE and LIKE
        if (count($this->aWhere) > 0 || count($this->aLike) > 0) {
            $query .=" WHERE ";

            //WHERE
            if (count($this->aWhere) > 0) {
                $last_key = end(array_keys($this->aWhere));
                foreach ($this->aWhere as $key => $value) {
                    $query .= " $key = '" . mysqli_real_escape_string($DBLink, trim($value)) . "' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }

            }

            //LIKE
            if (count($this->aLike) > 0) {
                $last_key = end(array_keys($this->aLike));
				$q = explode("WHERE",$query);

				//if (strlen($q[1])!=1) $query .= "AND";
                foreach ($this->aLike as $key => $value) {
                    $query .= " $key LIKE '%" . mysqli_real_escape_string($DBLink, trim($value)) . "%' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }
            }
			$t=false;
        }

        //GROUP_BY
        if (count($this->aGroup_by) > 0) {
            $query .= " GROUP BY ";

            $last_key = end(array_keys($this->aGroup_by));
            foreach ($this->aGroup_by as $key => $val) {
                $query .= mysqli_real_escape_string($DBLink, trim($val));
                if ($key != $last_key)
                    $query .= ", ";
            }
        }

        //ORDER_BY
        if ($this->sOrder_by != "") {
            $query .= $this->sOrder_by;
        }

       //echo $query."<br>";
       // $this->aQuerylog[] = $query;
       // if ($this->dbSpec->sqlQueryRow($query, $res)) {
       //     return $res;
       // }
	   
	   if ($this->dbtype=="postgres") {
	   		$result = pg_query($this->conn, $query);
		 	if (!$result) {
			 die("Error in SQL query: " .$query." ".pg_last_error());
		 	} 
			$res = pg_fetch_all($result);
			pg_close($this->conn);	
		 	return $res ;   
			
	   } 
	   
	   if ($this->dbtype=="mysql") {
	   		$this->aQuerylog[] = $query;
		   if ($this->dbSpec->sqlQueryRow($query, $res)) {
		       return $res;
		   }  
	   }
    }

}

?>