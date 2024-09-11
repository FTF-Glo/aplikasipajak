<?php

class DbTanah {

    private $dbSpec = null;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }

    public function getZnt() {
        $query = "SELECT * FROM cppmod_pbb_znt";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function goHistoryZnt($year) {
        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_znt_$year LIKE cppmod_pbb_znt";
        $this->dbSpec->sqlQuery($query, $res);

        $dataZnt = $this->getZnt();
        foreach ($dataZnt as $rowZnt) {
            $valZnt = "'" . implode("','", $rowZnt) . "'";
            $keyZnt = implode(",", array_keys($rowZnt));

            $query = "INSERT INTO cppmod_pbb_znt_$year($keyZnt) VALUES ($valZnt)";
            $result += $this->dbSpec->sqlQuery($query, $res);
        }

        return $result;
    }

    public function getKelasBumi() {
        $query = "SELECT * FROM cppmod_pbb_kelas_bumi";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function goHistoryKelasBumi($year) {
        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_kelas_bumi_$year LIKE cppmod_pbb_kelas_bumi";
        $this->dbSpec->sqlQuery($query, $res);

        $dataBumi = $this->getKelasBumi();
        foreach ($dataBumi as $rowBumi) {
            $valBumi = "'" . implode("','", $rowBumi) . "'";
            $keyBumi = implode(",", array_keys($rowBumi));

            $query = "INSERT INTO cppmod_pbb_kelas_bumi_$year($keyBumi) VALUES ($valBumi)";
            $result += $this->dbSpec->sqlQuery($query, $res);
        }

        return $result;
    }

    public function getBlok() {
        $query = "SELECT * FROM cppmod_pbb_blok";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function goHistoryBlok($year) {
        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_blok_$year LIKE cppmod_pbb_blok";
        $this->dbSpec->sqlQuery($query, $res);

        $dataBlok = $this->getBlok();
        foreach ($dataBlok as $rowBlok) {
            $valBlok = "'" . implode("','", $rowBlok) . "'";
            $keyBlok = implode(",", array_keys($rowBlok));

            $query = "INSERT INTO cppmod_pbb_blok_$year($keyBlok) VALUES ($valBlok)";
            $result += $this->dbSpec->sqlQuery($query, $res);
        }

        return $result;
    }

}

?>