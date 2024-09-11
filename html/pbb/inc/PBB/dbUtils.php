<?php
class DbUtils
{
    private $dbSpec = null;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }

    //35utech
    function getTarif($NJKP)
    {
        $cari_tarif = "SELECT CPM_TRF_TARIF FROM cppmod_pbb_tarif WHERE CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND CPM_TRF_NILAI_ATAS >= " . $NJKP;
 
        if (!$this->dbSpec->sqlQueryRow($cari_tarif, $resTarif)) {
            echo mysqli_error($this->dbSpec);
            echo $cari_tarif;
            return false;
        }
        $op_tarif = $resTarif[0]['CPM_TRF_TARIF'];

        return $op_tarif;
    }
    public function getUserDetailPbb($uid = "", $filter = array())
    {
        if (trim($uid) != '')
            $filter['ctr_u_id'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($uid));

        $query = "SELECT * FROM tbl_reg_user_pbb ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "ctr_u_id")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKabKota($id = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPC_TK_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "SELECT * FROM cppmod_tax_kabkota ";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TK_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TK_KABKOTA ASC";
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKecamatan($id = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPC_TKC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "SELECT * FROM cppmod_tax_kecamatan ";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKC_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";

                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TKC_KECAMATAN ASC";
        // echo $query."<br>";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKelurahan($id = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPC_TKL_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "SELECT * FROM cppmod_tax_kelurahan ";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKL_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }
        $query .= "ORDER BY CPC_TKL_KELURAHAN ASC";
        //echo $query."<br>";

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getKecamatanNama($kode)
    {
        $query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return (isset($row['CPC_TKC_KECAMATAN']) ? $row['CPC_TKC_KECAMATAN'] : '');
    }

    public function getKelurahanNama($kode)
    {
        $query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return (isset($row['CPC_TKL_KELURAHAN']) ? $row['CPC_TKL_KELURAHAN'] : '');
    }

    public function getKabkotaNama($kode)
    {
        $query = "SELECT * FROM `cppmod_tax_kabkota` WHERE CPC_TK_ID = '" . $kode . "';";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return $row['CPC_TK_KABKOTA'];
    }

    public function getProvNama($kode)
    {
        $query = "SELECT * FROM `cppmod_tax_propinsi` WHERE CPC_TP_ID = '" . $kode . "';";
        $this->dbSpec->sqlQuery($query, $res);
        $row = mysqli_fetch_array($res);
        return $row['CPC_TP_PROPINSI'];
    }

    public function getKelOnKota($id, $filter = array())
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "SELECT * from cppmod_tax_kelurahan WHERE CPC_TKL_KCID IN (
                        SELECT CPC_TKC_ID FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID=\"$id\"
                ) ";

        if ($filter != null && count($filter) > 0) {
            $query .= "AND ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TKL_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }

        $query .= "ORDER BY CPC_TKL_KELURAHAN ASC";
        #echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getProv($id = "", $filter = array())
    {
        if (trim($id) != '')
            $filter['CPC_TP_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $query = "SELECT * FROM cppmod_tax_propinsi ";
        if (count($filter) > 0) {
            $query .= "WHERE ";
            //$last_key = end(array_keys($filter));
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "CPC_TP_ID")
                    $query .= " $key = '$value' ";
                else
                    $query .= " $key LIKE '%$value%' ";
                if ($key != $last_key)
                    $query .= " AND ";
            }
        }
        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getConfigValue($key)
    {
        $qry = "select * from central_app_config where CTR_AC_AID = 'aPBB' and CTR_AC_KEY = '$key'";
        if (!$this->dbSpec->sqlQueryRow($qry, $res)) {
            echo mysqli_error($this->dbSpec);
            echo $qry;
            return false;
        }
        return $res[0]['CTR_AC_VALUE'];
        // global $DBLink, $appID;
        // $res = mysqli_query($this->dbSpec->getDBLink(), $qry);
        // if ($res === false) {
        // echo $qry . "<br>";
        // echo mysqli_error($this->dbSpec);
        // }
        // while ($row = mysqli_fetch_assoc($res)) {
        // return $row['CTR_AC_VALUE'];
        // }
        //mysqli_close($DBLink);
    }

    public function getZNT($id = "", $filter = array(), $tahun=false)
    {
        $tahun_tagihan = $this->getConfigValue('tahun_tagihan');
        $tahun = (!$tahun) ? $tahun_tagihan : $tahun;

        $query = "SELECT CPM_KODE_ZNT, IFNULL(CPM_NIR2,CPM_NIR) AS CPM_NIR FROM (
                        SELECT 
                            A.CPM_KODE_ZNT,
                            (A.CPM_NIR * 1000) as CPM_NIR, 
                            (B.CPM_NJOP_M2 * 1000) as CPM_NIR2 
                        FROM cppmod_pbb_znt A
                        -- LEFT JOIN cppmod_pbb_kelas_bumi B ON rpad(B.CPM_KELAS,3,' ')= A.CPM_KODE_ZNT 
                        LEFT JOIN cppmod_pbb_kelas_bumi B ON B.CPM_THN_AWAL>='2011' AND B.CPM_NILAI_BAWAH<=A.CPM_NIR AND B.CPM_NILAI_ATAS>=A.CPM_NIR 
                    WHERE A.CPM_TAHUN='$tahun'";


        if (count($filter) > 0) {
            $query .= " AND ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key = '$value' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }

        $query .= ") TBL ";
        //var_dump($query); exit();
        // echo $query; exit;  
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getZNT_with_kelas($filter = array())
    {
        $tahun_tagihan = $this->getConfigValue('tahun_tagihan');

        $query = "SELECT CPM_KODE_ZNT, IFNULL(CPM_NIR2,CPM_NIR) AS CPM_NIR, IFNULL(CPM_KELAS, 'XXX') AS CPM_KELAS FROM (
            SELECT 
                A.CPM_KODE_ZNT,
                (A.CPM_NIR * 1000) AS CPM_NIR, 
                (B.CPM_NJOP_M2 * 1000) AS CPM_NIR2, 
                B.CPM_KELAS
            FROM cppmod_pbb_znt A
            LEFT JOIN cppmod_pbb_kelas_bumi B ON B.CPM_KELAS=RIGHT(CONCAT('00',A.CPM_KODE_ZNT),3) AND B.CPM_THN_AWAL>='2011' AND B.CPM_THN_AKHIR<='9999'  
            WHERE A.CPM_TAHUN='$tahun_tagihan'";


        if (count($filter) > 0) {
            $query .= " AND ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $query .= " $key = '$value' ";
                if (count($filter) > 1 && $key != $last_key)
                    $query .= " AND ";
            }
        }

        $query .= ") TBL ORDER BY CPM_KODE_ZNT";
        //var_dump($query); exit();
        // echo $query; exit;  
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getPrinterName($userID, $m)
    {

        $query = "SELECT CPM_PRINTERNAME FROM cppmod_pbb_user_printer WHERE CPM_UID = '{$userID}' AND CPM_MODULE = '{$m}'";


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function getNoUrut($nop, $uname)
    {
        $enop = substr($nop, -1);
        $prenop = substr($nop, 0, 13);
        $sql = "select max(SUBSTRING(CPM_NOP,-5,4)) as CPM_NOP FROM cppmod_pbb_generate_nop where SUBSTRING(CPM_NOP,1,13)='$prenop'";
        $this->dbSpec->sqlQueryRow($sql, $res);

        $lastNoUrut = (int) $res[0]['CPM_NOP'];
        $nourut = str_pad($lastNoUrut + 1, 4, "0", STR_PAD_LEFT);
        $this->insertNOP($prenop . $nourut . $enop, $uname);
        //        if ($lastNoUrut >= 2000) {
        //            $nourut = str_pad($lastNoUrut + 1, 3, "0", STR_PAD_LEFT);
        //            $this->insertNOP($prenop.$nourut.$enop, $uname);
        //        } else {
        //            $nourut = "2" . str_pad(1, 3, "0", STR_PAD_LEFT);
        //            $this->insertNOP($prenop.$nourut.$enop, $uname);
        //        }
        return $prenop . $nourut . $enop;
    }

    public function checkNOP($nop, $uname)
    {
        $hasil = false;

        $sql = "select CPM_NOP FROM cppmod_pbb_generate_nop where CPM_NOP='$nop'";
        $this->dbSpec->sqlQuery($sql, $res);
        if (mysqli_num_rows($res) == 0) {
            $date = date("Y-m-d");
            $insert = "insert into cppmod_pbb_generate_nop values ('{$nop}','{$uname}','{$date}')";
            $this->dbSpec->sqlQuery($insert, $res);
            $hasil = true;
        }
        return $hasil;
    }

    public function insertNOP($nop, $uname)
    {
        $date = date("Y-m-d");
        $sql = "insert into cppmod_pbb_generate_nop values ('{$nop}','{$uname}','{$date}')";
        if ($this->dbSpec->sqlQueryRow($sql, $res)) {
            return $res;
        }
    }

    public function checkKalibrasi($kel, $thn)
    {
        $sql = "select count(*) as TOTAL FROM cppmod_pbb_kalibrasi where CPM_KELURAHAN='$kel' AND CPM_TAHUN_PAJAK='$thn' ";

        if ($this->dbSpec->sqlQueryRow($sql, $res)) {
            if ($res[0]['TOTAL'] == 0) return 0;
            else return 1;
        }
        return -1;
    }

    public function hitungTagihan($aValue, $appConfig)
    {
        // var_dump($aValue);die;
        /* tambahan ridwan */
        // $qryy = "SELECT OP_NJOPTKP
        // FROM gw_pbb.pbb_sppt a  
        // WHERE A.NOP = '{$res[0]['CPM_NOP']}' AND SPPT_TAHUN_PAJAK = {$res[0]['CPM_START_YEAR']} ";

        // if (!$this->dbSpec->sqlQueryRow($qryy, $ress)) {
        //     return false;
        // }
        /* end tambahan ridwan */



        // $NJOPTKP = ($appConfig['minimum_njoptkp']== null ) ? 10000000 :$appConfig['minimum_njoptkp'];
        // $minPBBHarusBayar = ($appConfig['minimum_sppt_pbb_terhutang']== null ) ? 25000 :$appConfig['minimum_sppt_pbb_terhutang'];
        if (isset($aValue['OP_NJOPTKP'])) {
            $NJOPTKP = ($aValue['OP_NJOPTKP'] != 0 || $aValue['OP_NJOPTKP'] != null || $aValue['OP_NJOPTKP'] != "" ? $aValue['OP_NJOPTKP'] : 0);
        } else {
            $NJOPTKP = 0;
        }

        // $NJOPTKP = ($appConfig['minimum_njoptkp']== null ) ? 10000000 :$appConfig['minimum_njoptkp'];
        $minPBBHarusBayar = ($appConfig['minimum_sppt_pbb_terhutang'] == null) ? 0 : $appConfig['minimum_sppt_pbb_terhutang'];

        $NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'] + $aValue['CPM_NJOP_BUMI_BERSAMA'] + $aValue['CPM_NJOP_BANGUNAN_BERSAMA'];

        $NJOPTKP = ($aValue['CPM_NJOP_BANGUNAN']>0 && $NJOP>10000000) ? 10000000 : 0;

        //Penentuan NJOPTKP Pidie
        //if($aValue['CPM_NJOP_BANGUNAN'] == 0)
        //    $NJOPTKP = 0;
        //Penentuan NJOPTKP Pidie Jaya & Bireuen
        //if($aValue['CPM_NJOP_BANGUNAN'] <= 10000000)
        //    $NJOPTKP = 0;
        //Penentuan NJOPTKP Kupang
        //if($NJOP < 250000000)
        //    $NJOPTKP = 0;

        if ($NJOP > $NJOPTKP)
            $NJKP = $NJOP - $NJOPTKP;
        else $NJKP = 0;

        // if ($NJKP >= 1000000000000 ) {
        //     $njkpnew =  $NJKP * 0.9;
        // }elseif ($NJKP >= 1000000000 ) {
        //     $njkpnew =  $NJKP * 0.7;
        // }elseif($NJKP < 1000000000) {
        //     $njkpnew =  $NJKP * 0.4;
        // }
       

        $aValue['OP_NJOP'] = $NJOP;
        $aValue['OP_NJKP'] = $NJKP;
        $aValue['OP_NJOPTKP'] = $NJOPTKP;

        $cari_tarif = "SELECT CPM_TRF_TARIF FROM cppmod_pbb_tarif WHERE
                        CPM_TRF_NILAI_BAWAH <= " . $NJOP . " AND
                        CPM_TRF_NILAI_ATAS >= " . $NJOP;
        if (!$this->dbSpec->sqlQueryRow($cari_tarif, $resTarif)) {
            echo mysqli_error($this->dbSpec);
            echo $cari_tarif;
            return false;
        }

        $op_tarif = $resTarif[0]['CPM_TRF_TARIF'];
        $aValue['OP_TARIF'] = $op_tarif;
        $PBB_HARUS_DIBAYAR = $njkpnew * ($op_tarif / 100);

        if ($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
            $PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
        $aValue['SPPT_PBB_HARUS_DIBAYAR'] = number_format($PBB_HARUS_DIBAYAR, 0, '', '');

        return $aValue;
    }

    function getTanggalJatuhTempo($date)
    {
        $qry = "SELECT CPM_TGL_JATUH_TEMPO from cppmod_pbb_tgl_jatuh_tempo WHERE CPM_TGL_PENETAPAN_AWAL <= '" . substr($date, 5, 5) . "' AND CPM_TGL_PENETAPAN_AKHIR >= '" . substr($date, 5, 5) . "'";

        if (!$this->dbSpec->sqlQueryRow($qry, $res)) {
            echo mysqli_error($this->dbSpec);
            echo $qry;
            return false;
        }
        return substr($date, 0, 5) . $res[0]['CPM_TGL_JATUH_TEMPO'];
    }

    function selectPenetapan($nop, $appConfig, $uuid)
    {
        $qry = $this->querySelectPenetapan($nop);

        if (!$this->dbSpec->sqlQueryRow($qry, $res)) {
            return false;
        }

        $aValue['CPM_NJOP_TANAH'] = $res[0]['CPM_NJOP_TANAH'];
        $aValue['CPM_NJOP_BANGUNAN'] = $res[0]['CPM_NJOP_BANGUNAN'];
        $aValue['CPM_NJOP_BUMI_BERSAMA'] = $res[0]['CPM_NJOP_BUMI_BEBAN'];
        $aValue['CPM_NJOP_BANGUNAN_BERSAMA'] = $res[0]['CPM_NJOP_BNG_BEBAN'];

        $aValue = $this->hitungTagihan($aValue, $appConfig);

        $res[0]['OP_NJOP'] = $aValue['OP_NJOP'];
        $res[0]['OP_NJKP'] = $aValue['OP_NJKP'];
        $res[0]['OP_NJOPTKP'] = $aValue['OP_NJOPTKP'];
        $res[0]['OP_TARIF'] = $aValue['OP_TARIF'];
        $res[0]['SPPT_PBB_HARUS_DIBAYAR'] = $aValue['SPPT_PBB_HARUS_DIBAYAR'];

        $date = date('Y-m-d');
        $dueDate = $this->getTanggalJatuhTempo($date);

        $res[0]['SPPT_TANGGAL_JATUH_TEMPO'] = $dueDate;
        $res[0]['SPPT_TANGGAL_TERBIT'] = $date;
        $res[0]['SPPT_TANGGAL_CETAK'] = $date;
        $res[0]['SPPT_TAHUN_PAJAK'] = $appConfig['tahun_tagihan'];
        $res[0]['UUID'] = $uuid;

        return $res[0];
    }

    function querySelectPenetapan($nop)
    {
        return "SELECT A.CPM_NOP, "
            . "A.CPM_NOP_BERSAMA, "
            . "A.CPM_WP_NAMA, "
            . "A.CPM_WP_ALAMAT, "
            . "A.CPM_WP_RT, "
            . "A.CPM_WP_RW, "
            . "A.CPM_WP_KODEPOS, "
            . "A.CPM_WP_NO_HP, "
            . "IFNULL(A.CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, "
            . "IFNULL(A.CPM_OP_LUAS_BANGUNAN,0) AS CPM_OP_LUAS_BANGUNAN,  "
            . "IFNULL(A.CPM_OP_KELAS_TANAH,'XXX') AS CPM_OP_KELAS_TANAH, "
            . "IFNULL(A.CPM_OP_KELAS_BANGUNAN,'XXX') AS CPM_OP_KELAS_BANGUNAN, "
            . "IFNULL(A.CPM_NJOP_TANAH,0) AS CPM_NJOP_TANAH, "
            . "IFNULL(A.CPM_NJOP_BANGUNAN,0) AS CPM_NJOP_BANGUNAN, "
            . "A.CPM_OP_ALAMAT, "
            . "A.CPM_OP_RT, "
            . "A.CPM_OP_RW, "
            . "A.CPM_OP_KELURAHAN, "
            . "A.CPM_OP_KECAMATAN, "
            . "A.CPM_OP_KOTAKAB, "
            . "B.CPC_TK_KABKOTA AS OP_KOTA, C.CPC_TKC_KECAMATAN AS OP_KECAMATAN, D.CPC_TKL_KELURAHAN AS OP_KELURAHAN, "
            . "A.CPM_WP_KOTAKAB AS WP_KOTA, A.CPM_WP_KECAMATAN AS WP_KECAMATAN, A.CPM_WP_KELURAHAN AS WP_KELURAHAN, "
            . "L.CPM_KELAS_BUMI_BEBAN, L.CPM_KELAS_BNG_BEBAN, L.CPM_LUAS_BUMI_BEBAN, L.CPM_LUAS_BNG_BEBAN, IFNULL(L.CPM_NJOP_BUMI_BEBAN,0) AS CPM_NJOP_BUMI_BEBAN, IFNULL(L.CPM_NJOP_BNG_BEBAN,0) AS CPM_NJOP_BNG_BEBAN, "
            . "A.CPM_OT_JENIS "
            . "FROM cppmod_pbb_sppt_final A LEFT JOIN "
            . "cppmod_tax_kabkota B ON A.CPM_OP_KOTAKAB = B.CPC_TK_ID LEFT JOIN "
            . "cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID LEFT JOIN "
            . "cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID LEFT JOIN "
            . "cppmod_pbb_sppt_anggota L ON A.CPM_NOP = L.CPM_NOP  "
            . "WHERE A.CPM_NOP = '" . $nop . "' "
            . "UNION ALL "
            . "SELECT A.CPM_NOP, "
            . "A.CPM_NOP_BERSAMA, "
            . "A.CPM_WP_NAMA, "
            . "A.CPM_WP_ALAMAT, "
            . "A.CPM_WP_RT, "
            . "A.CPM_WP_RW, "
            . "A.CPM_WP_KODEPOS, "
            . "A.CPM_WP_NO_HP, "
            . "IFNULL(A.CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, "
            . "IFNULL(A.CPM_OP_LUAS_BANGUNAN,0) AS CPM_OP_LUAS_BANGUNAN,  "
            . "IFNULL(A.CPM_OP_KELAS_TANAH,'XXX') AS CPM_OP_KELAS_TANAH, "
            . "IFNULL(A.CPM_OP_KELAS_BANGUNAN,'XXX') AS CPM_OP_KELAS_BANGUNAN, "
            . "IFNULL(A.CPM_NJOP_TANAH,0) AS CPM_NJOP_TANAH, "
            . "IFNULL(A.CPM_NJOP_BANGUNAN,0) AS CPM_NJOP_BANGUNAN, "
            . "A.CPM_OP_ALAMAT, "
            . "A.CPM_OP_RT, "
            . "A.CPM_OP_RW, "
            . "A.CPM_OP_KELURAHAN, "
            . "A.CPM_OP_KECAMATAN, "
            . "A.CPM_OP_KOTAKAB, "
            . "B.CPC_TK_KABKOTA AS OP_KOTA, C.CPC_TKC_KECAMATAN AS OP_KECAMATAN, D.CPC_TKL_KELURAHAN AS OP_KELURAHAN, "
            . "A.CPM_WP_KOTAKAB AS WP_KOTA, A.CPM_WP_KECAMATAN AS WP_KECAMATAN, A.CPM_WP_KELURAHAN AS WP_KELURAHAN, "
            . "L.CPM_KELAS_BUMI_BEBAN, L.CPM_KELAS_BNG_BEBAN, L.CPM_LUAS_BUMI_BEBAN, L.CPM_LUAS_BNG_BEBAN, IFNULL(L.CPM_NJOP_BUMI_BEBAN,0) AS CPM_NJOP_BUMI_BEBAN, IFNULL(L.CPM_NJOP_BNG_BEBAN,0) AS CPM_NJOP_BNG_BEBAN, "
            . "A.CPM_OT_JENIS "
            . "FROM cppmod_pbb_sppt_susulan A LEFT JOIN "
            . "cppmod_tax_kabkota B ON A.CPM_OP_KOTAKAB = B.CPC_TK_ID LEFT JOIN "
            . "cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID LEFT JOIN "
            . "cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID LEFT JOIN "
            . "cppmod_pbb_sppt_anggota L ON A.CPM_NOP = L.CPM_NOP  "
            . "WHERE A.CPM_NOP = '" . $nop . "' ";
    }


    public function getDenda($dueDate, $bill, $daysInMonth = 0, $maxPenaltyMonth = 24, $penaltyPercentagePerMonth = 1)
    {
        // hitungan Denda Terbaru 
        // Permintaan Pemda Kabupaten Pesawaran
        $penaltyPercentagePerMonth2 = 2;
        $penaltyPercentagePerMonth1 = 1;
        $datenow = date('Y-m-d');

        if(strtotime($dueDate) < strtotime($datenow)){

            if(strtotime($dueDate) > strtotime('2025-01-01')){
                // dapat 1 Persen
                $monthInterval = $this->getMonthsInterval_Pesawaran($dueDate . ' 23:59:59', date('Y-m-d H:i:s'));
                $monthInterval = ($monthInterval <= 0 ? 0 : $monthInterval);
                $monthInterval = ($monthInterval > 24 ? 24 : $monthInterval);
                $penaltyMonth  = ($monthInterval==0) ? 0 : ($penaltyPercentagePerMonth1 * $monthInterval) / 100;
                return floor($penaltyMonth * $bill);
                
            }else{
                // dapat 1 Persen dan 2 persen
                // step 1
                $monthInterval_1 = $this->getMonthsInterval_Pesawaran($dueDate . ' 23:59:59' , '2023-12-31 23:59:59');
                $monthInterval_1 = ($monthInterval_1 <= 0 ? 0 : $monthInterval_1);
                $monthInterval_1 = ($monthInterval_1 > 24 ? 24 : $monthInterval_1);
                $due24month      = ($monthInterval_1>=24) ? true : false;
                $penaltyMonth    = ($monthInterval_1==0) ? 0 : ($penaltyPercentagePerMonth2 * $monthInterval_1) / 100;
                $penaltyAmount1  = floor($penaltyMonth * $bill);
                // end step 1
        
                // step 2
                if($due24month){
                    $penaltyAmount2  = 0;
                }else{
                    $monthInterval_2 = $this->getMonthsInterval_Pesawaran('2024-01-01 00:00:00', date('Y-m-d H:i:s'));
                    $monthInterval_2 = ($monthInterval_2 <= 0 ? 0 : $monthInterval_2);
                    $monthInterval_2 = ($monthInterval_2 > 24 ? 24 : $monthInterval_2);
                    $penaltyMonth    = ($monthInterval_2==0) ? 0 : ($penaltyPercentagePerMonth1 * $monthInterval_2) / 100;
                    $penaltyAmount2  = floor($penaltyMonth * $bill);
                }
                // end step 2
        
                return ($penaltyAmount1 + $penaltyAmount2);
            }
        }
        return 0;
    }

    public function getMonthsInterval_Pesawaran($date1, $date2)
    {
        $monthsInYear = 12;
        
        $date1 = strtotime($date1);
        $date2 = strtotime($date2);
        $dueDateYear = date('Y', $date1);
        $nowYear = date('Y', $date2);
        $dueDateMonth = date('m', $date1);
        $nowMonth = date('m', $date2);
		$dueDateDay = date('d', $date1);
        $nowDay = date('d', $date2);
		
		$addHari = $nowDay > $dueDateDay ? 1 : 0;

        return ((($nowYear - $dueDateYear) * $monthsInYear) + ($nowMonth - $dueDateMonth)) + $addHari;
    }

    public function getMonthsInterval($dueDate)
    {
        $monthsInYear = 12;
        
        $dueDate = strtotime($dueDate);
        $dueDateYear = date('Y', $dueDate);
        $nowYear = date('Y', time());
        $dueDateMonth = date('m', $dueDate);
        $nowMonth = date('m', time());
		$dueDateDay = date('d', $dueDate);
        $nowDay = date('d', time());
		
		$addHari = $nowDay > $dueDateDay ? 1 : 0;

        return ((($nowYear - $dueDateYear) * $monthsInYear) + ($nowMonth - $dueDateMonth)) + $addHari;
    }

}
