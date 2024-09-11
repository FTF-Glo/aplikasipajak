<?php
date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once $sRootPath . 'inc/payment/inc-payment-db-c.php';

class SimpleDB
{
    protected $link    = null;
    protected $result  = null;
    protected $debug   = true;
    protected $filters = array();
    protected $dbconfig = array();
    protected $query;
    protected $dbname;
    protected $dbhost;
    protected $dbuser;
    protected $dbpwd;
    protected $dbport;


    protected $appConfig;

    public $bulan = array(
        "01" => "Januari",
        "02" => "Februari",
        "03" => "Maret",
        "04" => "April",
        "05" => "Mei",
        "06" => "Juni",
        "07" => "Juli",
        "08" => "Agustus",
        "09" => "September",
        "10" => "Oktober",
        "11" => "November",
        "12" => "Desember"
    );
    public $buku = array(
        1     => array('min' => 0, 'max' => 100000),
        12    => array('min' => 0, 'max' => 500000),
        123   => array('min' => 0, 'max' => 2000000),
        1234  => array('min' => 0, 'max' => 5000000),
        12345 => array('min' => 0, 'max' => 999999999999999),
        2     => array('min' => 100001, 'max' => 500000),
        23    => array('min' => 100001, 'max' => 2000000),
        234   => array('min' => 100001, 'max' => 5000000),
        2345  => array('min' => 100001, 'max' => 999999999999999),
        3     => array('min' => 500001, 'max' => 2000000),
        34    => array('min' => 500001, 'max' => 5000000),
        345   => array('min' => 500001, 'max' => 999999999999999),
        4     => array('min' => 2000001, 'max' => 5000000),
        45    => array('min' => 2000001, 'max' => 999999999999999),
        5     => array('min' => 5000001, 'max' => 999999999999999),
    );

    const DEFAULT_MYSQL_PORT   = '3306';
    const APP_ID               = 'aPBB';

    public function __construct()
    {
        $this->appConfig = $this->getConfig();
    }

    public function set($varname, $value)
    {
        $this->$varname = $value;
        return $this;
    }

    public function get($varname)
    {
        return $this->$varname;
    }

    public function flatten($a, $join = ',')
    {
        if (!$a) {
            return '';
        } else if ($a && is_array($a)) {
            return implode($join, $a);
        }
        return $a;
    }

    public function dbSet($a, $escape = true, $link = null)
    {
        if (!$a) {
            return '';
        } else if ($a && is_array($a)) {
            $sets = array();
            foreach ($a as $column => $value) {
                $value = $escape ? $this->dbEscape($value, ($link !== null ? $link : $this->link)) : $value;
                $sets[] = "{$column} = '{$value}'";
            }
            return $this->flatten($sets);
        }
        return $a;
    }

    public function dbEscape($value, $link = null)
    {
        $link = $link !== null ? $link : $this->link;
        return $link ? mysqli_real_escape_string($link, $value) : $value;
    }

    public function dbOpen($type = 'sw', $local = false)
    {
        $DBHOST  = ONPAYS_DBHOST;
        $DBUSER  = ONPAYS_DBUSER;
        $DBPWD   = ONPAYS_DBPWD;
        $DBNAME  = ONPAYS_DBNAME;

        $dbhosts = explode(':', $DBHOST);
        $DBHOST  = $dbhosts[0];
        $DBPORT  = isset($dbhosts[1]) ? $dbhosts[1] : self::DEFAULT_MYSQL_PORT;

        if ($type == 'gw') {
            $DBHOST = $this->appConfig['GW_DBHOST'];
            $DBUSER = $this->appConfig['GW_DBUSER'];
            $DBPWD  = $this->appConfig['GW_DBPWD'];
            $DBNAME = $this->appConfig['GW_DBNAME'];
            $DBPORT = $this->appConfig['GW_DBPORT'];
        }

        $link = mysqli_connect($DBHOST, $DBUSER, $DBPWD, $DBNAME, $DBPORT);

        if (mysqli_connect_errno() !== 0) {
            if ($this->debug) {
                $this->showError('Error: ' . mysqli_connect_error());
            }
            return false;
        }

        $this->dbname = $DBNAME;
        $this->dbhost = $DBHOST;
        $this->dbuser = $DBUSER;
        $this->dbpwd  = $DBPWD;
        $this->dbport = $DBPORT;

        $this->dbconfig[$type] = array(
            'dbname' => $DBNAME,
            'dbhost' => $DBHOST,
            'dbuser' => $DBUSER,
            'dbpwd'  => $DBPWD,
            'dbport' => $DBPORT,
        );

        if (!$local) {
            $this->link = $link;
        }

        return $local ? $link : $this;
    }

    public function dbQuery($query, $link = null)
    {
        if (!$this->link && !$link) {
            $this->dbOpen();
        }

        $link = $link !== null ? $link : $this->link;

        $result = mysqli_query($link, $query);
        if ($result === false) {
            if ($this->debug) {
                $this->showError(array('Error: ' . mysqli_error($link), 'Query: ' . $query));
            }
            return false;
        }
        $this->query = $query;
        $this->result = $result;

        $statements = array('INSERT', 'UPDATE', 'DELETE', 'REPLACE', 'CREATE', 'DROP', 'ALTER');
        foreach ($statements as $statement) {
            if (substr($query, 0, strlen($statement)) == $statement) {
                return $this->result;
            }
        }

        return $this;
    }

    public function dbGetNumRows($sql, $link = null)
    {
        $sql    = "SELECT COUNT(*) AS counts FROM ( {$sql} ) sQuery";
        $row    = $this->dbQuery($sql, $link)->fetchRow();
        return isset($row['counts']) ? $row['counts'] : 0;
    }

    public function fetchAll()
    {
        // $results = array();
        // while ($row = mysqli_fetch_assoc($this->result)) {
        //     $results[] = $row;
        // }
        // mysqli_free_result($this->result);
        // return $results; // OLD SCHOOL

        if (! $this->result) {
            return null;
        }
        $rows = mysqli_fetch_all($this->result, MYSQLI_ASSOC); // MYSQLND
        mysqli_free_result($this->result);
        return $rows;
    }

    public function fetchRow()
    {
        if (!$this->result) {
            return null;
        }

        $row = mysqli_fetch_assoc($this->result);
        mysqli_free_result($this->result);

        return $row;
    }

    public function setFilter($condition, $value = null, $escape = true)
    {
        // $value = $value !== null ? $this->dbEscape($value) : '';
        // $value = $value && $escape ? "'{$value}'" : $value;
        $value = $value && $escape ? $this->dbEscape($value) : $value;
        $value = $value ? (is_string($value) ? "'{$value}'" : $value) : '';

        $this->filters[] = "{$condition} {$value}";
        return $this;
    }

    public function getFilter()
    {
        $filter = !empty($this->filters) ? $this->flatten($this->filters, ' AND ') : '1=1';
        $this->filters = array();
        return $filter;
    }

    protected function getConfig()
    {
        if ($this->appConfig) {
            return $this->appConfig;
        }

        $query = "SELECT * FROM central_app_config WHERE CTR_AC_AID = '" . self::APP_ID . "'";
        $rows = $this->dbQuery($query)->fetchAll();

        $config = array();
        foreach ($rows as $row) {
            $config[$row['CTR_AC_KEY']] = $row['CTR_AC_VALUE'];
        }

        return $config;
    }

    public function showError($msg)
    {
        $errors = '';
        if (is_array($msg)) {
            foreach ($msg as $m) {
                $errors .= print_r($m . PHP_EOL, true);
            }
        }
        http_response_code(500);
        echo '<pre>', (!empty($errors) ? $errors : $msg), '</pre>';
        exit;
    }
}

/** BY ALDES */
