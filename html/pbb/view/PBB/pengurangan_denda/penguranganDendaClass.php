<?php

class penguranganDenda
{
    protected $DBLink;
    protected $appConfig;

    protected $table = 'pengurangan_denda';
    protected $tableNOP = 'pbb_sppt';
    protected $debug = true;

    protected $perPage = 15;

    public function __construct($appConfig = array()) {
        $this->appConfig = $appConfig;
        $this->DBLink = $this->dbOpen();
    }

    public function set($var, $val)
    {
        $this->$var = $val;
        return $this;
    }

    public function get($var)
    {
        return $this->$var;
    }

    public function insert($data)
    {
        $sql = "INSERT INTO {$this->table} SET " . $this->dbSet($data);
        return $this->dbQuery($sql);
    }

    public function editServices($sid,$uname)
    {   $tgl = date('Y-m-d');
        $sql = "UPDATE sw_pbb.cppmod_pbb_services
                SET 
                    CPM_STATUS='4',
                    CPM_APPROVER='$uname',
                    CPM_DATE_APPROVER='$tgl'
                WHERE CPM_ID='$sid';";
        return $this->dbQuery($sql);
    }

    public function delete($id, $uname = null)
    {
        $find = $this->find($id);
        // $find = $this->find($id, true);
        
        if (empty($find)) {
            return false;
        }

        // if ($find[0]['DELETED_AT'] !== null) {
        //     $sql = "DELETE FROM {$this->table} WHERE ID = " . $this->dbEscape($id);
        //     return $this->dbQuery($sql);
        // }

        $set = $this->dbSet(array(
            'DELETED_AT' => date('Y-m-d H:i:s'),
            'DELETED_BY' => $uname
        ));

        $sql = "UPDATE {$this->table} SET {$set} WHERE ID = " . $this->dbEscape($id);

        return $this->dbQuery($sql);
    }

    public function find($id, $findDeleted = false)
    {
        $findDeletedQuery = !$findDeleted ? 'DELETED_AT IS NULL' : '1=1';

        $sql = "SELECT * FROM {$this->table} WHERE {$findDeletedQuery} AND ID = " . $this->dbEscape($id);
        $result = $this->dbQuery($sql);

        return $this->dbFetch($result);
    }

    public function findNOP($nop, $year = null)
    {
        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C ON C.NOP = A.NOP AND C.TAHUN = A.SPPT_TAHUN_PAJAK LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";
        
        $sql = "SELECT IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN, A.* FROM {$this->tableNOP} A {$joinPengurangan} WHERE A.NOP = '". $this->dbEscape($nop) ."'";
        if ($year !== null) $sql .= " AND A.SPPT_TAHUN_PAJAK = '". $this->dbEscape($year) ."'";
    
        $result = $this->dbQuery($sql);
        return $this->dbFetch($result);
    }

    // Udah gak dipake lagi karena pake Datatables
    public function history($page = 1, $whereClause = '')
    {
        $whereClause = empty($whereClause) ? '' : ' AND '. $whereClause;

        $sql = "SELECT * FROM {$this->table} a LEFT JOIN {$this->tableNOP} b ON a.NOP = b.NOP AND a.TAHUN = b.SPPT_TAHUN_PAJAK WHERE a.DELETED_AT IS NULL {$whereClause} ORDER BY a.CREATED_AT DESC";
        $totalRow = $this->dbGetNumRows($sql);

        $offset = $this->perPage * ($page - 1);
        $sql .= " LIMIT {$offset}, {$this->perPage}";
        
        $result = $this->dbQuery($sql);
        $rows = $this->dbFetch($result);

        return array(
            'totalRow' => $totalRow,
            'data'     => $rows,
            'paging'   => array(
                'prev' => $page > 1 ? $page - 1 : false,
                'next' => $totalRow > ($this->perPage * $page) ? $page + 1 : false
            )
        );

    }

    public function dbEscape($value)
    {
        return mysqli_real_escape_string($this->DBLink, $value);
    }

    protected function dbOpen()
    {
        $link = mysqli_connect(
            $this->appConfig['GW_DBHOST'],
            $this->appConfig['GW_DBUSER'],
            $this->appConfig['GW_DBPWD'],
            $this->appConfig['GW_DBNAME'],
            $this->appConfig['GW_DBPORT']
        );

        if ($this->debug && !$link) {
            die("Error connecting to {$this->appConfig['GW_DBNAME']}: " . mysqli_connect_error());
        }

        return $link;
    }

    protected function dbSet(array $data, $escape = true)
    {
        $set = array();
        foreach ($data as $column => $value) {
            if ($escape) {
                $column = "`{$column}`";
                $value  = "'". $this->dbEscape($value) ."'";
            }
            $set[] = "{$column} = {$value}";
        }

        return empty($set) ? false : implode(', ', $set);
    }

    protected function dbQuery($sql)
    {
        $result = mysqli_query($this->DBLink, $sql);
        
        if ($this->debug && !$result) {
            die("Error SQL '{$sql}': " . mysqli_error($this->DBLink));
        }

        return $result;
    }

    protected function dbFetchRow($result)
    {
        return mysqli_fetch_assoc($result);
    }

    protected function dbFetch($result)
    {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    protected function dbGetNumRows($sql)
    {
        $sql    = str_replace('*', 'COUNT(*) AS counts', $sql);
        $row    = $this->dbFetchRow($this->dbQuery($sql));
        return isset($row['counts']) ? $row['counts'] : 0;
    }

}
