<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");

class DbSpptHistory
{

    private $dbSpec = null;
    private $dbSppt = null;
    private $dbSpptExt = null;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
        $this->dbSppt = new DbSppt($dbSpec);
        $this->dbSpptExt = new DbSpptExt($dbSpec);
    }
    // aldes
    // tambah parameter $tahunPenempatan
    public function goFinal($tranId, $tahunPenempatan = '0')
    {
        if (trim($tranId) != '') {
            $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tranId));
        } else {
            return false;
        }
        $bOK = false;
        $idDoc = "";
        $vers = "";

        //get the idDoc and version
        $query = "SELECT CPM_TRAN_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_tranmain WHERE CPM_TRAN_ID='$id'";
        // echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            $idDoc = $res[0]['CPM_TRAN_SPPT_DOC_ID'];
            $vers = $res[0]['CPM_SPPT_DOC_VERSION'];
        }

        $errCode = false;

        //get the value for SPPT
        $aSppt = $this->dbSppt->get($idDoc, $vers);


        $aSppt[0]['CPM_SPPT_THN_PENETAPAN'] = $tahunPenempatan;
        $columns = "";
        $vals = "";
        foreach ($aSppt[0] as $colname => $val) {
            if (!is_string($colname)) continue;
            $columns .= $colname . ",";
            $vals .= "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val) . "',";
        }
        $columns = substr($columns, 0, strlen($columns) - 1);
        $vals = substr($vals, 0, strlen($vals) - 1);
        $query = "INSERT INTO cppmod_pbb_sppt_final ($columns) values ($vals)";


        if ($this->dbSpec->sqlQuery($query, $res)) {
            $query = "INSERT INTO cppmod_pbb_sppt_ext_final SELECT * FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='$idDoc' AND CPM_SPPT_DOC_VERSION='$vers' ";
                
            if ($this->dbSpec->sqlQuery($query, $res)) {
                $bOK = true;
            } else {
                $errCode = true;
            }
        }
        // }

        if ($bOK) {
            //next we need to delete every trace from the original table
            $this->cleanFinal($id);
        } else {
            if ($errCode) {
                $query = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$idDoc' AND CPM_SPPT_DOC_VERSION='$vers' ";
                // echo $query;
                $this->dbSpec->sqlQuery($query, $res);
            }
        }
        return $bOK;
    }

    public function goSusulan($tranId)
    {
        if (trim($tranId) != '') {
            $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tranId));
        } else {
            return false;
        }
        $bOK = false;
        $idDoc = "";
        $vers = "";


        //get the idDoc and version
        $query = "SELECT CPM_TRAN_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_tranmain WHERE CPM_TRAN_ID='$id'";
        //echo $query;exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            $idDoc = $res[0]['CPM_TRAN_SPPT_DOC_ID'];
            $vers = $res[0]['CPM_SPPT_DOC_VERSION'];
        }

        $errCode = false;

        //get the value for SPPT
        $aSppt = $this->dbSppt->get($idDoc, $vers);
        $aSppt[0]['CPM_SPPT_THN_PENETAPAN'] = "0";
        $columns = "";
        $vals = "";
        foreach ($aSppt[0] as $colname => $val) {
            if (!is_string($colname)) continue;
            $columns .= $colname . ",";
            $vals .= "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val) . "',";
        }
        $columns = substr($columns, 0, strlen($columns) - 1);
        $vals = substr($vals, 0, strlen($vals) - 1);
        $query = "INSERT INTO cppmod_pbb_sppt_susulan ($columns) values ($vals)";
        //echo $query;exit();
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $query = "INSERT INTO cppmod_pbb_sppt_ext_susulan SELECT * FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='$idDoc' AND CPM_SPPT_DOC_VERSION='$vers' ";
            //echo $query;
            if ($this->dbSpec->sqlQuery($query, $res)) {
                $bOK = true;
            } else {
                $errCode = true;
            }
        }
        // }

        if ($bOK) {
            //next we need to delete every trace from the original table
            $this->cleanFinal($id);
        } else {
            if ($errCode) {
                $query = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_SPPT_DOC_ID='$idDoc' AND CPM_SPPT_DOC_VERSION='$vers' ";
                //echo $query;
                $this->dbSpec->sqlQuery($query, $res);
            }
        }
        return $bOK;
    }

    public function cleanFinal($tranID)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tranID));

        $idDoc = "";

        //get the idDoc and version
        $query = "SELECT CPM_TRAN_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_tranmain WHERE CPM_TRAN_ID='$id'";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            $idDoc = $res[0]['CPM_TRAN_SPPT_DOC_ID'];
        }

        //first get the refnum
        $query = "SELECT * FROM cppmod_pbb_tranmain WHERE CPM_TRAN_ID='$id'";
        $bOK = $this->dbSpec->sqlQueryRow($query, $res2);

        //then delete all matching refnum from tranmain
        $query = "DELETE FROM cppmod_pbb_tranmain WHERE CPM_TRAN_REFNUM='" . $res2[0]['CPM_TRAN_REFNUM'] . "'";
        $bOK = $this->dbSpec->sqlQuery($query, $res2);

        //then delete all matching docs from SPPT
        $query = "DELETE FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='$idDoc'";
        $bOK = $this->dbSpec->sqlQuery($query, $res2);

        //then delete all extension matching docs from SPPT_EXT
        $query = "DELETE FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='$idDoc'";
        $bOK = $this->dbSpec->sqlQuery($query, $res2);
    }

    public function goHistory($year, $aValue, $aExt = array())
    {

        //create new table of SPPT and SPPT EXT for history		
        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_sppt_$year LIKE cppmod_pbb_sppt";
        $this->dbSpec->sqlQuery($query, $res);

        $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_sppt_ext_$year LIKE cppmod_pbb_sppt_ext";
        $this->dbSpec->sqlQuery($query, $res);

        //inserting SPPT
        unset($aValue['CPM_SPPT_THN_PENETAPAN']);
        $vSppt = "'" . implode("','", $aValue) . "'";
        $hSppt = implode(",", array_keys($aValue));

        $query = "INSERT INTO cppmod_pbb_sppt_$year($hSppt) VALUES ($vSppt)";
        $this->dbSpec->sqlQuery($query, $res);

        //inserting extended SPPT
        if (count($aExt) > 0) {
            $hExt = "";
            $vExt = "";
            $hExt = implode(",", array_keys($aExt[0]));
            $query = "INSERT INTO cppmod_pbb_sppt_ext_$year($hExt) VALUES ";
            foreach ($aExt as $extVal) {
                $vExt = "'" . implode("','", $extVal) . "'";
                $query .= "(" . $vExt . "),";
            }
            $query = substr($query, 0, strlen($query) - 1);
            $this->dbSpec->sqlQuery($query, $res);
        }
    }

    private function getFinalYear()
    {
        $query = "SELECT DISTINCT YEAR(CPM_TRAN_DATE) as YEAR FROM cppmod_pbb_tranmain_final";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res[0]['YEAR'];
        }
    }
}
