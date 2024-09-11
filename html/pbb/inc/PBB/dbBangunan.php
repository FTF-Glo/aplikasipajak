<?php

class DbBangunan {

    private $dbSpec = null;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }

    public function getBangunan($table) {
        $query = "SELECT * FROM cppmod_pbb_" . strtoupper($table);

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function goHistoryBangunan($table, $yearBack, $yearNow = "") {
        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_" . strtoupper($table) . "_" . $yearBack . " LIKE cppmod_pbb_" . strtoupper($table);
        $this->dbSpec->sqlQuery($query, $res);

        $data = $this->getBangunan($table);

        foreach ($data as $row) {
            $val = "'" . implode("','", $row) . "'";
            $key = implode(",", array_keys($row));

            $query = "INSERT INTO cppmod_pbb_" . strtoupper($table) . "_" . $yearBack . " ($key) VALUES ($val)";
            $result += $this->dbSpec->sqlQuery($query, $res);
        }

        if ($yearNow) {
            $query = "UPDATE cppmod_pbb_" . strtoupper($table) . " SET CPM_TAHUN = '$yearNow' WHERE CPM_TAHUN = '$yearBack'";
            $this->dbSpec->sqlQuery($query, $res);
        }

        return $result;
    }

}

?>