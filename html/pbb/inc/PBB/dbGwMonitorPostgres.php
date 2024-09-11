<?php
class DbGwMonitor {

    private $dbSpec = null;
    private $sTbl_name = "";
    private $aWhere = "";
    private $aLike = "";
    private $sOrder_by = "";
    private $aGroup_by = "";
    private $aQuerylog = array();
	private $attConn = array();
	private $connection = NULL;

    public function __construct($dbSpec, $kdKotaKab, $attConnection) {
        $this->dbSpec = $dbSpec;
        $this->where(array("OP_KOTAKAB_KODE" => mysql_real_escape_string(trim($kdKotaKab))));
        $this->sTbl_name = "PBB_SPPT";
		$this->attConn = $attConnection;
		$this->connectToPostgrees ();
    }
	
	public function connectToPostgrees () {
		
		$host = $this->attConn["HOST"];
		$dbname = $this->attConn["DBNAME"];
		$port = $this->attConn["PORT"];
		$user = $this->attConn["USER"];
		$pass = $this->attConn["PASS"];
		
		$dbconn = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}");
		
		if (!$dbconn) {
			 die("Error in connection: host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}" . pg_last_error());
		}  
		$this->connection = $dbconn;  
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
        $order_by = mysql_real_escape_string(trim($order_by));
        $opt = mysql_real_escape_string(trim($opt));

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
        $field = (count($field) > 0) ? implode(", ", $field) : "*";
        $query = "SELECT $field FROM " . $this->sTbl_name;

        //WHERE and LIKE
        if (count($this->aWhere) > 0 || count($this->aLike) > 0) {
            $query .=" WHERE ";

            //WHERE
            if (count($this->aWhere) > 0) {
                $last_key = end(array_keys($this->aWhere));
                foreach ($this->aWhere as $key => $value) {
                    $query .= " $key = '" . mysql_real_escape_string(trim($value)) . "' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }
            }

            //LIKE
            if (count($this->aLike) > 0) {
                $last_key = end(array_keys($this->aLike));
                foreach ($this->aLike as $key => $value) {
                    $query .= " $key LIKE '%" . mysql_real_escape_string(trim($value)) . "%' ";
                    if ($key != $last_key)
                        $query .= " AND ";
                }
            }
        }

        //GROUP_BY
        if (count($this->aGroup_by) > 0) {
            $query .= " GROUP BY ";

            $last_key = end(array_keys($this->aGroup_by));
            foreach ($this->aGroup_by as $key => $val) {
                $query .= mysql_real_escape_string(trim($val));
                if ($key != $last_key)
                    $query .= ", ";
            }
        }

        //ORDER_BY
        if ($this->sOrder_by != "") {
            $query .= $this->sOrder_by;
        }

        echo $query."<br>";
        $this->aQuerylog[] = $query;
        //if ($this->dbSpec->sqlQueryRow($query, $res)) {
         //   return $res;
        //}
		$result = pg_query($query) or die('Query failed: ' .
		pg_last_error());
		pg_free_result($result);// Closing connection
		pg_close($dbconn);

    }

}
?>
