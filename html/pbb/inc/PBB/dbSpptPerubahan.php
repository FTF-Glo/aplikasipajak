<?php
include_once("dbUtils.php");

class DbSpptPerubahan
{
    private $dbSpec = null;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }

    public function get($id = "", $vers = "", $filter = "")
    {
        if (trim($id) != '') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_service_change ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPM_SPPT_DOC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key) $query .= " AND ";
            }
        }
        $query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";

        // echo $query;

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

        $query = "INSERT INTO cppmod_pbb_service_change (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
        $tmpVals = "'$id', '$vers', ";
        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        //$last_key = end(array_keys($aValue));

        foreach ($aValue as $key => $value) {
            $query .= $key;
            $tmpVals .= "'" . $value . "'";

            if ($key != $last_key) {
                $query .= ", ";
                $tmpVals .= ", ";
            }
        }
        $query .= ") values (" . $tmpVals . ")";

        // echo $query;exit;

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edit($id, $aValue)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (isset($vers)) {
            $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
        } else {
            $vers = null;
        }

        //$last_key = end(array_keys($aValue));
        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_service_change SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE CPM_SID='$id'";
        $this->dbSpec->sqlQuery($query, $res)       ;
        // var_dump($query, $res);exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function del($id, $vers = "")
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        if (trim($vers) != '') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "DELETE FROM cppmod_pbb_service_change WHERE CPM_SPPT_DOC_ID='$id' ";
        if (trim($vers) != '') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";

        // echo $query;		
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function isExist($id, $vers)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
        $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

        $query = "SELECT * FROM cppmod_pbb_service_change WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";

        #echo $query;		
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes > 0);
        }
    }

    public function isExist_NOP($nop)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT a.CPM_SPPT_DOC_ID, a.CPM_SPPT_DOC_VERSION, 
		b.CPM_TRAN_ID, b.CPM_TRAN_REFNUM, b.CPM_TRAN_SPPT_DOC_ID, b.CPM_SPPT_DOC_VERSION, b.CPM_TRAN_STATUS, b.CPM_TRAN_FLAG 
		FROM cppmod_pbb_service_change a 
		INNER JOIN `cppmod_pbb_tranmain` b on a.CPM_SPPT_DOC_ID = b.CPM_TRAN_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION = b.CPM_SPPT_DOC_VERSION 
		AND b.CPM_TRAN_FLAG = '0'
		WHERE a.CPM_NOP = '" . $nop . "';";
        // echo $query;		
        $res = mysqli_query($this->dbSpec->getDBLink(), $query);
        $nRes = mysqli_num_rows($res);
        return $nRes;
    }

    public function getInitData($id)
    {

        $qry = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_service_change where CPM_SID = '{$id}'";
        // echo $qry;

        if ($this->dbSpec->sqlQueryRow($qry, $row)) {
            // var_dump($row[0]['TOTAL']);
            // echo "masuk";
            // exit;
            if ($row[0]['TOTAL'] == 0) {
                // echo "masuk sini";
                // exit;
                return $this->getDataDefault($id);
            } else {
                $qry = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN, A.CPM_SPPT_YEAR_BERLAKU FROM cppmod_pbb_services A, cppmod_pbb_service_change B, 
                            cppmod_tax_kelurahan C, cppmod_tax_kecamatan D
                            WHERE A.CPM_ID = B.CPM_SID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,10) = C.CPC_TKL_ID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,7) = D.CPC_TKC_ID AND
                            A.CPM_ID = '{$id}' ";
                // echo $qry;
                // die;
                if ($this->dbSpec->sqlQueryRow($qry, $res)) {
                    return $res[0];
                }
            }
        }
    }

    public function getDataDefault($id)
    {
        $qryTotal = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_service_change
                        WHERE CPM_SID = '{$id}'";

        if ($this->dbSpec->sqlQueryRow($qryTotal, $row)) {
            if ($row[0]['TOTAL'] == 0) {
                $qry = "SELECT CPM_OP_NUMBER, CPM_SPPT_YEAR FROM cppmod_pbb_services WHERE CPM_ID = '{$id}'";

                $bOK = $this->dbSpec->sqlQueryRow($qry, $rowDetail);

                $qryInsert = "INSERT INTO cppmod_pbb_service_change 
                    SELECT 
                    '" . $id . "', '', '" . $rowDetail[0]['CPM_SPPT_YEAR'] . "', TBL.* 
                    FROM 
                    (SELECT * FROM cppmod_pbb_sppt_final
                    WHERE CPM_NOP = '" . $rowDetail[0]['CPM_OP_NUMBER'] . "' 
                    UNION ALL
                    SELECT * FROM cppmod_pbb_sppt_susulan 
                    WHERE CPM_NOP = '" . $rowDetail[0]['CPM_OP_NUMBER'] . "') TBL ";

                // die($qryInsert);
                $bOK = $this->dbSpec->sqlQuery($qryInsert, $res);

                $qry = "INSERT INTO cppmod_pbb_service_change_ext
                            SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID = 
                            (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP='" . $rowDetail[0]['CPM_OP_NUMBER'] . "')
                            UNION ALL
                            SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID = 
                            (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='" . $rowDetail[0]['CPM_OP_NUMBER'] . "')";

                $bOK = $this->dbSpec->sqlQuery($qry, $res);

                $qrySelect = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN, A.CPM_SPPT_YEAR_BERLAKU FROM cppmod_pbb_services A, cppmod_pbb_service_change B, 
                            cppmod_tax_kelurahan C, cppmod_tax_kecamatan D
                            WHERE A.CPM_ID = B.CPM_SID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,10) = C.CPC_TKL_ID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,7) = D.CPC_TKC_ID AND
                            A.CPM_ID = '{$id}' ";

                if ($this->dbSpec->sqlQueryRow($qrySelect, $resSelect)) {
                    return (isset($resSelect[0]) ? $resSelect[0] : '');
                }
            } else {
                $qrySelect = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN, A.CPM_SPPT_YEAR_BERLAKU FROM cppmod_pbb_services A, cppmod_pbb_service_change B, 
                            cppmod_tax_kelurahan C, cppmod_tax_kecamatan D
                            WHERE A.CPM_ID = B.CPM_SID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,10) = C.CPC_TKL_ID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,7) = D.CPC_TKC_ID AND
                            A.CPM_ID = '{$id}' ";

                if ($this->dbSpec->sqlQueryRow($qrySelect, $resSelect)) {
                    return $resSelect[0];
                }
            }
        } else {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
    }

    public function updateToFinal($id, $cpm_id)
    {

        $cari_final = "SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id'";
        $cari_final_ext = "SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID='$id'";

        $bOK = $this->dbSpec->sqlQuery($cari_final, $result);
        $bOKS = $this->dbSpec->sqlQuery($cari_final_ext, $result2);

        // var_dump($bOK, $bOKS, mysqli_fetch_array($result));
        // die;

        if (!$bOK) return $bOK;

        if (!$bOKS) return $bOKS;

        $tableName = 'cppmod_pbb_sppt_final';
        $tableNameExt = 'cppmod_pbb_sppt_ext_final';
        if ($final = mysqli_fetch_array($result)) {
            $tableName = 'cppmod_pbb_sppt_final';
            $tableNameExt = 'cppmod_pbb_sppt_ext_final';
        } else {
            $tableName = 'cppmod_pbb_sppt_susulan';
            $tableNameExt = 'cppmod_pbb_sppt_ext_susulan';
        }

        // var_dump($id, $cpm_id, $tableName, $tableNameExt);
        // exit;

        //Masukan data lama ke Change History by ZNK
        $bOK = $this->moveToChangeHistory($id, $cpm_id, $tableName, $tableNameExt);
        // $
        // var_dump($bOK);
        // exit;
        if (!$bOK) return $bOK;
        // end ZNK

        $query = "DELETE FROM " . $tableNameExt . " WHERE CPM_SPPT_DOC_ID='$id'";

        $bOK = $this->dbSpec->sqlQuery($query, $res);

        if (!$bOK) return $bOK;

        $query = "INSERT INTO " . $tableNameExt . " SELECT * FROM cppmod_pbb_service_change_ext WHERE CPM_SPPT_DOC_ID='$id'";

        $bOK = $this->dbSpec->sqlQuery($query, $res);
        
        if (!$bOK) return $bOK;

        $updateFinal = "UPDATE " . $tableName . " A, cppmod_pbb_service_change B
            SET A.CPM_SPPT_DOC_ID = B.CPM_SPPT_DOC_ID, A.CPM_SPPT_DOC_VERSION = B.CPM_SPPT_DOC_VERSION, 
            A.CPM_SPPT_DOC_AUTHOR = B.CPM_SPPT_DOC_AUTHOR, A.CPM_SPPT_DOC_CREATED = B.CPM_SPPT_DOC_CREATED, A.CPM_NOP = B.CPM_NOP, A.CPM_NOP_BERSAMA = B.CPM_NOP_BERSAMA, 
            A.CPM_OP_ALAMAT = B.CPM_OP_ALAMAT, A.CPM_OP_NOMOR = B.CPM_OP_NOMOR, A.CPM_OP_KELURAHAN = B.CPM_OP_KELURAHAN, A.CPM_OP_RT = B.CPM_OP_RT, 
            A.CPM_OP_RW = B.CPM_OP_RW, A.CPM_OP_KECAMATAN = B.CPM_OP_KECAMATAN, A.CPM_OP_KOTAKAB = B.CPM_OP_KOTAKAB, A.CPM_WP_STATUS = B.CPM_WP_STATUS, 
            A.CPM_WP_PEKERJAAN = B.CPM_WP_PEKERJAAN, A.CPM_WP_NAMA = B.CPM_WP_NAMA, A.CPM_WP_ID = B.CPM_WP_ID, A.CPM_WP_ALAMAT = B.CPM_WP_ALAMAT, 
            A.CPM_WP_KELURAHAN = B.CPM_WP_KELURAHAN, A.CPM_WP_RT = B.CPM_WP_RT, A.CPM_WP_RW = B.CPM_WP_RW, A.CPM_WP_KOTAKAB = B.CPM_WP_KOTAKAB, A.CPM_WP_PROPINSI = B.CPM_WP_PROPINSI,
            A.CPM_WP_KECAMATAN = B.CPM_WP_KECAMATAN, A.CPM_WP_KODEPOS = B.CPM_WP_KODEPOS, A.CPM_WP_NO_KTP = B.CPM_WP_NO_KTP, A.CPM_WP_NO_HP = B.CPM_WP_NO_HP, 
            A.CPM_OT_LATITUDE = B.CPM_OT_LATITUDE, A.CPM_OT_LONGITUDE = B.CPM_OT_LONGITUDE, A.CPM_OT_ZONA_NILAI = B.CPM_OT_ZONA_NILAI, A.CPM_OT_JENIS = B.CPM_OT_JENIS, 
            A.CPM_OT_PENILAIAN_TANAH = B.CPM_OT_PENILAIAN_TANAH, A.CPM_OT_PAYMENT_SISTEM = B.CPM_OT_PAYMENT_SISTEM, A.CPM_OT_PAYMENT_INDIVIDU = B.CPM_OT_PAYMENT_INDIVIDU, 
            A.CPM_OP_JML_BANGUNAN = B.CPM_OP_JML_BANGUNAN, A.CPM_PP_TIPE = B.CPM_PP_TIPE, A.CPM_PP_NAMA = B.CPM_PP_NAMA, A.CPM_PP_DATE = B.CPM_PP_DATE, 
            A.CPM_OPR_TGL_PENDATAAN = B.CPM_OPR_TGL_PENDATAAN, A.CPM_OPR_NAMA = B.CPM_OPR_NAMA, A.CPM_OPR_NIP = B.CPM_OPR_NIP, 
            A.CPM_PJB_TGL_PENELITIAN = B.CPM_PJB_TGL_PENELITIAN, A.CPM_PJB_NAMA = B.CPM_PJB_NAMA, A.CPM_PJB_NIP = B.CPM_PJB_NIP, 
            A.CPM_OP_SKET = B.CPM_OP_SKET, A.CPM_OP_FOTO = B.CPM_OP_FOTO, A.CPM_OP_LUAS_TANAH = B.CPM_OP_LUAS_TANAH, A.CPM_OP_KELAS_TANAH = B.CPM_OP_KELAS_TANAH, 
            A.CPM_NJOP_TANAH = B.CPM_NJOP_TANAH, A.CPM_OP_LUAS_BANGUNAN = B.CPM_OP_LUAS_BANGUNAN, A.CPM_OP_KELAS_BANGUNAN = B.CPM_OP_KELAS_BANGUNAN, 
            A.CPM_NJOP_BANGUNAN = B.CPM_NJOP_BANGUNAN, A.CPM_SPPT_THN_PENETAPAN = B.CPM_SPPT_THN_PENETAPAN
            WHERE A.CPM_SPPT_DOC_ID = B.CPM_SPPT_DOC_ID AND B.CPM_SPPT_DOC_ID='$id'";

        return $this->dbSpec->sqlQuery($updateFinal, $res);
    }

    public function updateToCurrent($id, $appConfig)
    {

        $qry = "SELECT A.CPM_NOP, A.CPM_WP_NAMA, A.CPM_WP_ALAMAT, A.CPM_WP_RT, A.CPM_WP_RW, A.CPM_WP_KODEPOS, A.CPM_WP_NO_HP, 
                IFNULL(A.CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, IFNULL(A.CPM_OP_LUAS_BANGUNAN,0) AS CPM_OP_LUAS_BANGUNAN, 
                IFNULL(A.CPM_OP_KELAS_TANAH,'XXX') AS CPM_OP_KELAS_TANAH, IFNULL(A.CPM_OP_KELAS_BANGUNAN,'XXX') AS CPM_OP_KELAS_BANGUNAN, 
                IFNULL(A.CPM_NJOP_TANAH,0) AS CPM_NJOP_TANAH, IFNULL(A.CPM_NJOP_BANGUNAN,0) AS CPM_NJOP_BANGUNAN, A.CPM_OP_ALAMAT, A.CPM_OP_RT,
                A.CPM_OP_RW, A.CPM_START_YEAR, A.CPM_WP_KOTAKAB, A.CPM_WP_KECAMATAN, A.CPM_WP_KELURAHAN,
                L.CPM_KELAS_BUMI_BEBAN, L.CPM_KELAS_BNG_BEBAN, L.CPM_LUAS_BUMI_BEBAN, L.CPM_LUAS_BNG_BEBAN, L.CPM_NJOP_BUMI_BEBAN, L.CPM_NJOP_BNG_BEBAN 
                FROM cppmod_pbb_service_change A LEFT JOIN  
                cppmod_pbb_sppt_anggota L ON A.CPM_NOP = L.CPM_NOP WHERE A.CPM_SID = '{$id}' ";
        // echo $qry; exit;

        if (!$this->dbSpec->sqlQueryRow($qry, $res)) {
            return false;
        }

        $dbUtils = new DbUtils($this->dbSpec);
        $aValue['CPM_NJOP_TANAH'] = $res[0]['CPM_NJOP_TANAH'];
        $aValue['CPM_NJOP_BANGUNAN'] = $res[0]['CPM_NJOP_BANGUNAN'];
        $aValue['CPM_NJOP_BUMI_BERSAMA'] = $res[0]['CPM_NJOP_BUMI_BEBAN'];
        $aValue['CPM_NJOP_BANGUNAN_BERSAMA'] = $res[0]['CPM_NJOP_BNG_BEBAN'];
        $aValue = $dbUtils->hitungTagihan($aValue, $appConfig);

        $nilaiPengurangan = $persenPengurangan = 0;
        // print_r($res);
        // exit;
        // var_dump($aValue);die;
        $queryUpdateCurrent = "UPDATE cppmod_pbb_sppt_current SET 
                SPPT_PBB_HARUS_DIBAYAR =  " . $aValue['SPPT_PBB_HARUS_DIBAYAR'] . ",
                WP_NAMA =  '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_NAMA']) . "' ,
                WP_ALAMAT = '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_ALAMAT']) . "' , 
                WP_RT =  '" . $res[0]['CPM_WP_RT'] . "' ,
                WP_RW = '" . $res[0]['CPM_WP_RW'] . "' ,
                WP_KELURAHAN = '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_KELURAHAN']) . "',  
                WP_KECAMATAN = '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_KECAMATAN']) . "',  
                WP_KOTAKAB = '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_KOTAKAB']) . "',  
                WP_KODEPOS = '" . $res[0]['CPM_WP_KODEPOS'] . "', 
                WP_NO_HP = '" . $res[0]['CPM_WP_NO_HP'] . "',  
                OP_LUAS_BUMI = '" . $res[0]['CPM_OP_LUAS_TANAH'] . "',  
                OP_LUAS_BANGUNAN = '" . $res[0]['CPM_OP_LUAS_BANGUNAN'] . "',  
                OP_KELAS_BUMI = '" . $res[0]['CPM_OP_KELAS_TANAH'] . "',  
                OP_KELAS_BANGUNAN = '" . $res[0]['CPM_OP_KELAS_BANGUNAN'] . "',  
                OP_NJOP_BUMI = '" . $res[0]['CPM_NJOP_TANAH'] . "',   
                OP_NJOP_BANGUNAN = '" . $res[0]['CPM_NJOP_BANGUNAN'] . "',  
                OP_NJOP = '" . $aValue['OP_NJOP'] . "',  
                  
                OP_NJKP = '" . $aValue['OP_NJKP'] . "' ,    
                OP_ALAMAT = '" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_OP_ALAMAT']) . "',  
                OP_RT = '" . $res[0]['CPM_OP_RT'] . "',  
                OP_RW = '" . $res[0]['CPM_OP_RW'] . "', 
                OP_TARIF = '" . $aValue['OP_TARIF'] . "',
                SPPT_PBB_PENGURANGAN = '" . $nilaiPengurangan . "',
                SPPT_PBB_PERSEN_PENGURANGAN = '" . $persenPengurangan . "'
                WHERE NOP = '" . $res[0]['CPM_NOP'] . "'  
                ";
               
        // print_r($queryUpdateCurrent); exit;
        return $this->dbSpec->sqlQuery($queryUpdateCurrent, $res);
    }

    public function moveToChangeHistory($doc_id, $cpm_id, $table, $table_ext)
    {
        $query = "INSERT INTO cppmod_pbb_service_change_ext_history SELECT '$cpm_id',A.* FROM $table_ext A WHERE CPM_SPPT_DOC_ID='$doc_id' ";
        $bOK = $this->dbSpec->sqlQuery($query, $res);
        // var_dump($bOK);
        // echo $query; exit;
        if (!$bOK) return false;

        // $query = "INSERT INTO cppmod_pbb_service_change_history SELECT '$cpm_id',*,CPM_WP_NAMA,CPM_WP_ALAMAT,CPM_OP_ALAMAT,CPM_OP_LUAS_TANAH,CPM_OP_LUAS_BANGUNAN FROM $table WHERE CPM_SPPT_DOC_ID = '$doc_id' ";
        $query = "
				INSERT INTO cppmod_pbb_service_change_history
				SELECT
					A.*, 
					B.CPM_WP_NAMA,
					B.CPM_WP_ALAMAT,
					B.CPM_OP_ALAMAT,
					B.CPM_OP_LUAS_TANAH,
					B.CPM_OP_LUAS_BANGUNAN
				FROM
					cppmod_pbb_service_change A 
				JOIN $table B ON A.CPM_NOP=B.CPM_NOP
				WHERE
					A.CPM_SPPT_DOC_ID = '$doc_id' ";

        // var_dump($bOK);
        // echo $query; exit;
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function deleteDataPerubahan($id)
    {
        $query = "DELETE FROM cppmod_pbb_service_change_ext WHERE CPM_SPPT_DOC_ID='$id'";
        // echo $query; exit;
        $bOK = $this->dbSpec->sqlQuery($query, $res);
        if (!$bOK) return false;

        $query = "DELETE FROM cppmod_pbb_service_change WHERE CPM_SPPT_DOC_ID='$id'";
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }


    public function getDataTagihanSPPT($nop, $tahun, $GWDBLink)
    {
        $query = "SELECT * FROM PBB_SPPT WHERE NOP = '{$nop}' AND SPPT_TAHUN_PAJAK = '{$tahun}'";

        $res = mysqli_query($GWDBLink, $query);
        if (!$res) {
            echo mysqli_error($GWDBLink);
            echo $query;
            return false;
            exit;
        }

        $row = mysqli_fetch_assoc($res);

        return $row;
    }

    public function get_sertifikat($nop)
	{
		$nop = (int)$nop;
		$query = "SELECT * FROM cppmod_pbb_sppt_sertifikat WHERE CPM_NOP='$nop' ORDER BY CPM_DATE_UPDATE DESC, CPM_DATE_CREATED DESC";
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
		return array( array() );
	}

	public function update_sertifikat($nop, $se)
	{
		$nop 	= (int)$nop;
		$res 	= [];
		$now 	= date('Y-m-d H:i:s');
		$nomor 	= $se['CPM_NOMOR_SERTIFIKAT'];
		$tgl 	= $se['CPM_TANGGAL'];
		$nama 	= $se['CPM_NAMA_SERTIFIKAT'];
		$js 	= $se['CPM_JENIS_HAK'];
		$nama2 	= $se['CPM_NAMA_PEMEGANG'];
		$query 	= "SELECT * FROM cppmod_pbb_sppt_sertifikat WHERE CPM_NOP='$nop'";
		$this->dbSpec->sqlQueryRow($query, $res);
		if(count($res)>0){
			$query =   "UPDATE cppmod_pbb_sppt_sertifikat 
						SET 
							CPM_NOMOR_SERTIFIKAT='$nomor',
							CPM_TANGGAL='$tgl',
							CPM_NAMA_SERTIFIKAT='$nama',
							CPM_JENIS_HAK='$js',
							CPM_NAMA_PEMEGANG='$nama2',
							CPM_DATE_UPDATE='$now'
						WHERE CPM_NOP='$nop'";
			$this->dbSpec->sqlQuery($query, $res);
		}else{
			$query="INSERT INTO cppmod_pbb_sppt_sertifikat 
					(CPM_NOP,CPM_NOMOR_SERTIFIKAT,CPM_TANGGAL,CPM_NAMA_SERTIFIKAT,CPM_JENIS_HAK,CPM_NAMA_PEMEGANG,CPM_DATE_CREATED) 
					VALUES ('$nop', '$nomor', '$tgl', '$nama', '$js', '$nama2', '$now')";
			$this->dbSpec->sqlQuery($query, $res);
		}
	}
}
