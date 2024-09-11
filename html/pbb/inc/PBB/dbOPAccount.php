<?php

class dbOPAccount {

    private $dbSpec = null;
    private $tableName = "cppmod_pbb_op_account";

    public $C_HOST_PORT;
    public $C_USER;
    public $C_PWD;
    public $C_DB;
                          


    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }
    
     public function updatePaymentCodeCurrentPembatalan($nop,$tahun,$payment_code) {
        $query = "UPDATE cppmod_pbb_sppt_current_pembatalan
        SET PAYMENT_CODE = '$payment_code' 
        WHERE 
        NOP = '$nop' 
        AND 
        SPPT_TAHUN_PAJAK = '$tahun'";
      
        
        return $this->dbSpec->sqlQuery($query);
     }

    public function updatePaymentCodeTagihan($nop,$tahun,$payment_code) {
        $LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
                
        $query = "UPDATE PBB_SPPT
        SET PAYMENT_CODE = '$payment_code' 
        WHERE 
        NOP = '$nop' 
        AND 
        SPPT_TAHUN_PAJAK = '$tahun'";
      
        return mysqli_query($LDBLink, $query);
        // return $this->dbSpec->sqlQuery($query);
     }
     public function getOPAccount($nop,$table) {
            $query = "SELECT CPM_OP_ACCOUNT
                FROM
                $table
                WHERE CPM_NOP = '$nop' ";
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }else{
            return false;
        }

     }

      public function getOPAccountSusulan($nop) {
            $query = "SELECT CPM_OP_ACCOUNT
                FROM
                cppmod_pbb_sppt_susulan A
                WHERE CPM_NOP = '$nop' ";
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }else{
            return false;
        }

     }
     public function generatePaymentCode($nop,$tahun) {
        $query = "SELECT
                CONCAT(
                '1',
                SUBSTR('$tahun', 3, 2),
                LPAD(B.CPM_OP_ACCOUNT, 7, '0')
                ) as PAYMENT_CODE
                FROM
                cppmod_pbb_sppt_final A
                LEFT JOIN 
                cppmod_pbb_op_account B
                ON A.CPM_NOP = B.CPM_NOP
                LEFT JOIN cppmod_pbb_sppt_susulan C
                ON C.CPM_NOP = B.CPM_NOP

                WHERE B.CPM_NOP = '$nop'

                    ";
                    // echo "$query";
                    // exit;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }else{
            return false;
        }

     }

     //  public function generatePaymentCodePembatalan($nop) {


     //    $query = "SELECT
     //            CONCAT(
     //            '1',
     //            SUBSTR(A.CPM_SPPT_THN_PENETAPAN, 3, 2),
     //            LPAD(B.CPM_OP_ACCOUNT, 7, '0')
     //            ) as PAYMENT_CODE
     //            FROM
     //            cppmod_pbb_sppt_final A
     //            LEFT JOIN 
     //            cppmod_pbb_op_account B
     //            ON A.CPM_NOP = B.CPM_NOP
     //            LEFT JOIN cppmod_pbb_sppt_susulan C
     //            ON C.CPM_NOP = B.CPM_NOP

     //            WHERE B.CPM_NOP = '360118100400300940'

     //                ";
     //                // echo "$sql";
     //    if ($this->dbSpec->sqlQueryRow($query, $res)) {
     //        return $res;
     //    }else{
     //        return false;
     //    }

     // }
     public function getMaxOPAccount() {
     $query = "SELECT IFNULL(MAX( CPM_OP_ACCOUNT),0)+1 AS MAX FROM $this->tableName  ";
        // echo $query;
        // exit;
       if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }else{
            echo "Gagal Ekesuksi getMaxOPAccount() ";
        }
     }
     public function save($param) {
        $query = "REPLACE INTO $this->tableName 
        (
            CPM_NOP,
            CPM_OP_ACCOUNT,
            CPM_LAST_INSERT,
            CPM_USER
        ) 
        VALUES 
        (
            '$param[CPM_NOP]',
            '$param[CPM_OP_ACCOUNT]',
            '$param[CPM_LAST_INSERT]',
            '$param[CPM_USER]'
        )
         ";
         // echo $query;exit;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return true;
        }else{
            return false;
        }
    }


    // public function getBangunan($table) {
    //     $query = "SELECT * FROM cppmod_pbb_" . strtoupper($table);

    //     if ($this->dbSpec->sqlQueryRow($query, $res)) {
    //         return $res;
    //     }
    // }

    // public function goHistoryBangunan($table, $yearBack, $yearNow = "") {
    //     $query = "CREATE TABLE IF NOT EXISTS cppmod_pbb_" . strtoupper($table) . "_" . $yearBack . " LIKE cppmod_pbb_" . strtoupper($table);
    //     $this->dbSpec->sqlQuery($query, $res);

    //     $data = $this->getBangunan($table);

    //     foreach ($data as $row) {
    //         $val = "'" . implode("','", $row) . "'";
    //         $key = implode(",", array_keys($row));

    //         $query = "INSERT INTO cppmod_pbb_" . strtoupper($table) . "_" . $yearBack . " ($key) VALUES ($val)";
    //         $result += $this->dbSpec->sqlQuery($query, $res);
    //     }

    //     if ($yearNow) {
    //         $query = "UPDATE cppmod_pbb_" . strtoupper($table) . " SET CPM_TAHUN = '$yearNow' WHERE CPM_TAHUN = '$yearBack'";
    //         $this->dbSpec->sqlQuery($query, $res);
    //     }

    //     return $result;
    // }

}

?>