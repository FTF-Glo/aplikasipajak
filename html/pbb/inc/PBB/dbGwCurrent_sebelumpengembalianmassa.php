<?php
include_once("dbUtils.php");
class DbGwCurrent
{

    private $dbSpec = null;
    public $totalrows = 0;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }

    // public function get70($filter = array(), $srch, $jumhal, $perpage, $page, $tahun) {
    // global $appConfig;
    // $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
    // //echo $appConfig['tahun_tagihan'];
    // if($tahun==$appConfig['tahun_tagihan'])
    // {$table = 'cppmod_pbb_sppt_current';}
    // else{ 
    // $table = "cppmod_pbb_sppt_cetak_$tahun";
    // }
    // $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
    // $query = "SELECT * FROM $table ";

    // if (count($filter) > 0) {
    // $query .="WHERE ";
    // $queryCount .="WHERE ";
    // $last_key = end(array_keys($filter));

    // foreach ($filter as $key => $value) {
    // $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
    // if ($key == "NOP"){
    // $queryCount .= " $key = '$value' ";
    // $query .= " $key = '$value' ";
    // }else{
    // $queryCount .= " $key LIKE '%$value%' ";
    // $query .= " $key LIKE '%$value%' ";
    // }
    // if ($key != $last_key) {
    // $queryCount .= " AND ";
    // $query .= " AND ";
    // }

    // }
    // }

    // if ($srch) {
    // $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
    // $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
    // }

    // $this->dbSpec->sqlQueryRow($queryCount, $total);
    // $this->totalrows = $total[0]['TOTAL'];

    // $query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";

    // /* if (!$jumhal){
    // $query .= "LIMIT 10 ";
    // }
    // else if ($jumhal){
    // $query .= "LIMIT $jumhal ";
    // } */

    // //echo $query;
    // if ($this->dbSpec->sqlQueryRow($query, $res)) {
    // return $res;
    // }
    // }
    public function get70($filter = array(), $srch, $jumhal, $perpage, $page, $tahun, $appConfig)
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
        else $table = "cppmod_pbb_sppt_cetak_$tahun";

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "NOP") {
                    $nop         = explode(',', $filter['NOP']);
                    $last         = array_keys($nop);
                    $last = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if ($key != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $queryCount .= " NOP IN ($listNOP) ";
                    $query .= " NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "NOP") {
                        $queryCount .= " $key = '$value' ";
                        $query .= " $key = '$value' ";
                    } else {
                        $queryCount .= " $key LIKE '%$value%' ";
                        $query .= " $key LIKE '%$value%' ";
                    }
                    if ($key != $last_key) {
                        $queryCount .= " AND ";
                        $query .= " AND ";
                    }
                }
            }
        }

        if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
        }

        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        $query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function get70s($filter = array(), $srch, $additionalWhereQuery, $jumhal, $perpage, $page, $tahun, $appConfig)
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
        else $table = "cppmod_pbb_sppt_cetak_$tahun";

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "NOP") {
                    $nop         = explode(',', $filter['NOP']);
                    $last         = array_keys($nop);
                    $last = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if ($key != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $queryCount .= " NOP IN ($listNOP) ";
                    $query .= " NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "NOP") {
                        $queryCount .= " $key = '$value' ";
                        $query .= " $key = '$value' ";
                    } else {
                        $queryCount .= " $key LIKE '%$value%' ";
                        $query .= " $key LIKE '%$value%' ";
                    }
                    if ($key != $last_key) {
                        $queryCount .= " AND ";
                        $query .= " AND ";
                    }
                }
            }

            if ($srch) {
                $query .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            }
        } else {
            if ($srch) {
                $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "WHERE (" . $additionalWhereQuery . ")";
                    $queryCount .= "WHERE (" . $additionalWhereQuery . ")";
                }
            }
        }

        /*if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        } else {
            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        }*/
        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        $query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    public function changeStimulus($filter = array(), $tahun, $pengurangan, $appConfig)
    {
        //$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
        else $table = "cppmod_pbb_sppt_cetak_$tahun";

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);
            $listNOP = "";

            $key = 0;
            foreach ($filter as $value) {
                $val = trim($value);
                $listNOP .= "'$value'";
                if ($key != $last_key) {
                    $listNOP .= ",";
                }
                
                $key++;
            }

            $queryCount .= " NOP IN ($listNOP) ";
            $query .= " NOP IN ($listNOP) ";

            /*if ($srch) {
                $query .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            }*/
        } /*else {
            if ($srch) {
                $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "WHERE (" . $additionalWhereQuery . ")";
                    $queryCount .= "WHERE (" . $additionalWhereQuery . ")";
                }
            }
        }*/

        /*if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        } else {
            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        }*/
        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        //$query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";
        $query .= "ORDER BY FLAG ASC, NOP ASC";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            for($x=0;$x<count($res);$x++){
                $newNJKP = $res[$x]["OP_NJKP"] - (($pengurangan/100) * $res[$x]["OP_NJKP"]);
                $queryupdate = "UPDATE ".$table." set OP_NJKP = '".$newNJKP."', PENGURANGAN_STIMULUS = '".$pengurangan."' where NOP = '".$res[$x]["NOP"]."' and SPPT_TAHUN_PAJAK = '".$tahun."'";
                $this->dbSpec->sqlQueryRun($queryupdate);
            }
        }

        return true;
    }

    public function changeStimulusTemp($filter = array(), $tahun, $pengurangan, $appConfig)
    {
        //$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
        else $table = "cppmod_pbb_sppt_cetak_$tahun";

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM $table ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);
            $listNOP = "";

            $key = 0;
            foreach ($filter as $value) {
                $val = trim($value);
                $listNOP .= "'$value'";
                if ($key != $last_key) {
                    $listNOP .= ",";
                }
                
                $key++;
            }

            $queryCount .= " NOP IN ($listNOP) ";
            $query .= " NOP IN ($listNOP) ";

            /*if ($srch) {
                $query .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            }*/
        } /*else {
            if ($srch) {
                $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "WHERE (" . $additionalWhereQuery . ")";
                    $queryCount .= "WHERE (" . $additionalWhereQuery . ")";
                }
            }
        }*/

        /*if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        } else {
            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        }*/
        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        //$query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";
        $query .= "ORDER BY FLAG ASC, NOP ASC";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            for($x=0;$x<count($res);$x++){
                //$newNJKP = $res[$x]["OP_NJKP"] - (($pengurangan/100) * $res[$x]["OP_NJKP"]);
                $queryupdate = "UPDATE ".$table." set OP_NJKP_TEMP = '".$pengurangan."', PENGURANGAN_STIMULUS_TEMP = '".$pengurangan."' where NOP = '".$res[$x]["NOP"]."' and SPPT_TAHUN_PAJAK = '".$tahun."'";
                $this->dbSpec->sqlQueryRun($queryupdate);
            }
        }

        return true;
    }

    public function get70gs($filter = array(), $srch, $buku, $additionalWhereQuery, $jumhal, $perpage, $page, $tahun, $appConfig)
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']){
            $table = 'cppmod_pbb_sppt_current';
            $tablebefore = "cppmod_pbb_sppt_cetak_".($tahun-1);
        }
        else {
            $table = "cppmod_pbb_sppt_cetak_$tahun";
            $tablebefore = "cppmod_pbb_sppt_cetak_$tahun";
        }

        $querybefore = "SELECT * FROM $tablebefore ";
        $query = "SELECT * FROM $table ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $querybefore .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "NOP") {
                    $nop         = explode(',', $filter['NOP']);
                    $last         = array_keys($nop);
                    $last = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if ($key != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $querybefore .= " NOP IN ($listNOP) ";
                    $query .= " NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "NOP") {
                        $querybefore .= " $key = '$value' ";
                        $query .= " $key = '$value' ";
                    } else {
                        $querybefore .= " $key LIKE '%$value%' ";
                        $query .= " $key LIKE '%$value%' ";
                    }
                    if ($key != $last_key) {
                        $querybefore .= " AND ";
                        $query .= " AND ";
                    }
                }
            }

            if ($srch) {
                $query .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $querybefore .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $querybefore .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $querybefore .= "AND (" . $additionalWhereQuery . ")";
                }
            }
        } else {
            if ($srch) {
                $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
                $querybefore .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $querybefore .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "WHERE (" . $additionalWhereQuery . ")";
                    $querybefore .= "WHERE (" . $additionalWhereQuery . ")";
                }
            }
        }

        $query .= "ORDER BY FLAG ASC, NOP ASC";
        $querybefore .= "ORDER BY FLAG ASC, NOP ASC";

        //echo $query;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */
        
        $ress = null;
        
        $jumlahbuku1 = 0;
        $jumlahbuku2 = 0;
        $jumlahbuku3 = 0;
        $jumlahbuku4 = 0;
        $jumlahbuku5 = 0;
        
        $totalbuku1 = 0;
        $totalbuku2 = 0;
        $totalbuku3 = 0;
        $totalbuku4 = 0;
        $totalbuku5 = 0;

        $jumlahbukubefore1 = 0;
        $jumlahbukubefore2 = 0;
        $jumlahbukubefore3 = 0;
        $jumlahbukubefore4 = 0;
        $jumlahbukubefore5 = 0;
        
        $totalbukubefore1 = 0;
        $totalbukubefore2 = 0;
        $totalbukubefore3 = 0;
        $totalbukubefore4 = 0;
        $totalbukubefore5 = 0;

        $kenaikan1 = 0;
        $kenaikan2 = 0;
        $kenaikan3 = 0;
        $kenaikan4 = 0;
        $kenaikan5 = 0;

        $luasbumi1 = 0;
        $luasbumi2 = 0;
        $luasbumi3 = 0;
        $luasbumi4 = 0;
        $luasbumi5 = 0;
        
        $luasbangunan1 = 0;
        $luasbangunan2 = 0;
        $luasbangunan3 = 0;
        $luasbangunan4 = 0;
        $luasbangunan5 = 0;
          
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            //case 1: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000 "; break;
            //case 2: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 "; break;
            //case 3: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 "; break;
            //case 4: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 "; break;
            //case 5: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 "; break;

            if($res != null && count($res) > 0) {
                for($x=0;$x<count($res);$x++){
                    $pembanding = $res[$x]['SPPT_PBB_HARUS_DIBAYAR'];
                    if(isset($res[$x]['OP_NJKP_TEMP']) && $res[$x]['OP_NJKP_TEMP'] != null && $res[$x]['OP_NJKP_TEMP'] != 0) {
                        $pembanding = $res[$x]['OP_NJKP_TEMP'];
                    }

                    if($pembanding >= 0 && $pembanding <= 100000){
                        $jumlahbuku1++;
                        $totalbuku1 += $pembanding;
                        $luasbumi1 += $res[$x]['OP_LUAS_BUMI'];
                        $luasbangunan1 += $res[$x]['OP_LUAS_BANGUNAN'];
                    }
                    else if($pembanding >= 100001 && $pembanding <= 500000){
                        $jumlahbuku2++;
                        $totalbuku2 += $pembanding;
                        $luasbumi2 += $res[$x]['OP_LUAS_BUMI'];
                        $luasbangunan2 += $res[$x]['OP_LUAS_BANGUNAN'];
                    }
                    else if($pembanding >= 500001 && $pembanding <= 2000000){
                        $jumlahbuku3++;
                        $totalbuku3 += $pembanding;
                        $luasbumi3 += $res[$x]['OP_LUAS_BUMI'];
                        $luasbangunan3 += $res[$x]['OP_LUAS_BANGUNAN'];
                    }
                    else if($pembanding >= 2000001 && $pembanding <= 5000000){
                        $jumlahbuku4++;
                        $totalbuku4 += $pembanding;
                        $luasbumi4 += $res[$x]['OP_LUAS_BUMI'];
                        $luasbangunan4 += $res[$x]['OP_LUAS_BANGUNAN'];
                    }
                    else if($pembanding >= 5000001 && $pembanding <= 999999999999999){
                        $jumlahbuku5++;
                        $totalbuku5 += $pembanding;
                        $luasbumi5 += $res[$x]['OP_LUAS_BUMI'];
                        $luasbangunan5 += $res[$x]['OP_LUAS_BANGUNAN'];
                    }
                }
            }
        }

        if ($this->dbSpec->sqlQueryRow($querybefore, $resbefore)) {
            //case 1: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000 "; break;
            //case 2: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 "; break;
            //case 3: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 "; break;
            //case 4: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 "; break;
            //case 5: $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 "; break;

            if($resbefore != null && count($resbefore) > 0) {
                for($x=0;$x<count($resbefore);$x++){
                    $pembanding = $resbefore[$x]['SPPT_PBB_HARUS_DIBAYAR'];
                    if(isset($resbefore[$x]['OP_NJKP_TEMP']) && $resbefore[$x]['OP_NJKP_TEMP'] != null && $resbefore[$x]['OP_NJKP_TEMP'] != 0) {
                        $pembanding = $resbefore[$x]['OP_NJKP_TEMP'];
                    }

                    if($pembanding >= 0 && $pembanding <= 100000){
                        $jumlahbukubefore1++;
                        $totalbukubefore1 += $pembanding;
                    }
                    else if($pembanding >= 100001 && $pembanding <= 500000){
                        $jumlahbukubefore2++;
                        $totalbukubefore2 += $pembanding;
                    }
                    else if($pembanding >= 500001 && $pembanding <= 2000000){
                        $jumlahbukubefore3++;
                        $totalbukubefore3 += $pembanding;
                    }
                    else if($pembanding >= 2000001 && $pembanding <= 5000000){
                        $jumlahbukubefore4++;
                        $totalbukubefore4 += $pembanding;
                    }
                    else if($pembanding >= 5000001 && $pembanding <= 999999999999999){
                        $jumlahbukubefore5++;
                        $totalbukubefore5 += $pembanding;
                    }
                }
            }
        }

        $ress[0]["totalbuku"] = $totalbuku1;
        $ress[1]["totalbuku"] = $totalbuku2;
        $ress[2]["totalbuku"] = $totalbuku3;
        $ress[3]["totalbuku"] = $totalbuku4;
        $ress[4]["totalbuku"] = $totalbuku5;

        $ress[0]["jumlahbuku"] = $jumlahbuku1;
        $ress[1]["jumlahbuku"] = $jumlahbuku2;
        $ress[2]["jumlahbuku"] = $jumlahbuku3;
        $ress[3]["jumlahbuku"] = $jumlahbuku4;
        $ress[4]["jumlahbuku"] = $jumlahbuku5;

        $ress[0]["totalbukubefore"] = $totalbukubefore1;
        $ress[1]["totalbukubefore"] = $totalbukubefore2;
        $ress[2]["totalbukubefore"] = $totalbukubefore3;
        $ress[3]["totalbukubefore"] = $totalbukubefore4;
        $ress[4]["totalbukubefore"] = $totalbukubefore5;

        $ress[0]["jumlahbukubefore"] = $jumlahbukubefore1;
        $ress[1]["jumlahbukubefore"] = $jumlahbukubefore2;
        $ress[2]["jumlahbukubefore"] = $jumlahbukubefore3;
        $ress[3]["jumlahbukubefore"] = $jumlahbukubefore4;
        $ress[4]["jumlahbukubefore"] = $jumlahbukubefore5;

        $ress[0]["luasbumi"] = $luasbumi1;
        $ress[1]["luasbumi"] = $luasbumi2;
        $ress[2]["luasbumi"] = $luasbumi3;
        $ress[3]["luasbumi"] = $luasbumi4;
        $ress[4]["luasbumi"] = $luasbumi5;

        $ress[0]["luasbangungan"] = $luasbangunan1;
        $ress[1]["luasbangungan"] = $luasbangunan2;
        $ress[2]["luasbangungan"] = $luasbangunan3;
        $ress[3]["luasbangungan"] = $luasbangunan4;
        $ress[4]["luasbangungan"] = $luasbangunan5;

        $ress[0]["kenaikan"] = ($totalbukubefore1 > 0) ? ((($totalbuku1 - $totalbukubefore1)/$totalbukubefore1) * 100) : 0;
        $ress[1]["kenaikan"] = $totalbukubefore2 > 0 ? ((($totalbuku2 - $totalbukubefore2)/$totalbukubefore2) * 100) : 0;
        $ress[2]["kenaikan"] = $totalbukubefore3 > 0 ? ((($totalbuku3 - $totalbukubefore3)/$totalbukubefore3) * 100) : 0;
        $ress[3]["kenaikan"] = $totalbukubefore4 > 0 ? ((($totalbuku4 - $totalbukubefore4)/$totalbukubefore4) * 100) : 0;
        $ress[4]["kenaikan"] = $totalbukubefore5 > 0 ? ((($totalbuku5 - $totalbukubefore5)/$totalbukubefore5) * 100) : 0;

        return $ress;
    }

    public function get70ss($filter = array(), $srch, $additionalWhereQuery, $jumhal, $perpage, $page, $tahun, $appConfig)
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        //if ($tahun == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
        //else $table = "cppmod_pbb_sppt_cetak_$tahun";

        $GW_DBHOST = $appConfig['GW_DBHOST'];
        $GW_DBPORT = (isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306');
        $GW_DBNAME = $appConfig['GW_DBNAME'];
        $GW_DBUSER = $appConfig['GW_DBUSER'];
        $GW_DBPWD = $appConfig['GW_DBPWD'];

        $dbConn = mysqli_connect($GW_DBHOST, $GW_DBUSER, $GW_DBPWD, $GW_DBNAME, $GW_DBPORT);

        $queryCount = "SELECT COUNT(*) AS TOTAL from pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";
        $query = "SELECT A.* , IFNULL(A.sppt_pbb_harus_dibayar,0) AS 'SPPT_PBB_HARUS_DIBAYAR', IFNULL(B.pbb_denda,0) as 'PBB_DENDA' , IFNULL(A.sppt_pbb_harus_dibayar+B.pbb_denda,0) as 'PBB_TOTAL_BAYAR' from pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                if ($key == "NOP") {
                    $nop         = explode(',', $filter['NOP']);
                    $last         = array_keys($nop);
                    $last = end($last);
                    $listNOP     = "";
                    foreach ($nop as $key => $val) {
                        $val = trim($val);
                        $listNOP .= "'$val'";
                        if ($key != $last) {
                            $listNOP .= ",";
                        }
                    }
                    $queryCount .= " A.NOP IN ($listNOP) ";
                    $query .= " A.NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "NOP") {
                        $queryCount .= " $key = '$value' ";
                        $query .= " $key = '$value' ";
                    } else {
                        $queryCount .= " $key LIKE '%$value%' ";
                        $query .= " $key LIKE '%$value%' ";
                    }
                    if ($key != $last_key) {
                        $queryCount .= " AND ";
                        $query .= " AND ";
                    }
                }
            }

            if ($srch) {
                $query .= " AND (A.NOP LIKE '%$srch%' OR A.WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " AND (A.NOP LIKE '%$srch%' OR A.WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            }
        } else {
            if ($srch) {
                $query .= " WHERE (A.NOP LIKE '%$srch%' OR A.WP_NAMA LIKE '%$srch%') ";
                $queryCount .= " WHERE (A.NOP LIKE '%$srch%' OR A.WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "AND (" . $additionalWhereQuery . ")";
                    $queryCount .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $query .= "WHERE (" . $additionalWhereQuery . ")";
                    $queryCount .= "WHERE (" . $additionalWhereQuery . ")";
                }
            }
        }

        /*if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        } else {
            if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                $query .= "AND (" . $additionalWhereQuery . ")";
                $queryCount .= "AND (" . $additionalWhereQuery . ")";
            }
        }*/
        //$this->dbSpec->sqlQueryRow($queryCount, $total);
        //$this->totalrows = $total[0]['TOTAL'];

        $query .= "ORDER BY A.NOP ASC LIMIT $hal, $perpage ";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        //var_dump($query);
        $res = mysqli_query($dbConn, $query);

        $nRes = mysqli_num_rows($res);
        if ($nRes > 0) {
            $this->totalrows = $nRes;
            $ress = null;

            while ($row = mysqli_fetch_array($res)) {
                $ress[] = $row;
            }

            return $ress;
        } else {
            mysqli_close($dbConn);
            return 0;
        }

        //if ($this->dbSpec->sqlQueryRow($query, $res)) {
        //return $res;
        //}
    }

    public function get($filter = array(), $srch, $jumhal, $perpage, $page)
    {

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_sppt_current ";
        $query = "SELECT * FROM cppmod_pbb_sppt_current ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                if ($key == "NOP") {
                    $queryCount .= " $key = '$value' ";
                    $query .= " $key = '$value' ";
                } else {
                    $queryCount .= " $key LIKE '%$value%' ";
                    $query .= " $key LIKE '%$value%' ";
                }
                if ($key != $last_key) {
                    $queryCount .= " AND ";
                    $query .= " AND ";
                }
            }
        }

        if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
        }

        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        $query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */

        //echo $query;
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }




    public function del($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP='$nop' ";
        if ($tahun) {
            $query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
        }
        //echo $query."<br>";	exit;	
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function edit($nop, $aValue, $appConfig)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        if (isset($aValue['OP_LUAS_BUMI'])) {
            $cari = "select OP_LUAS_BUMI, OP_NJOP_BUMI,OP_LUAS_BANGUNAN, OP_NJOP_BANGUNAN, 
                 OP_NJOPTKP
                 from cppmod_pbb_sppt_current where NOP='$nop'";

            $this->dbSpec->sqlQuery($cari, $result);
            if ($final = mysqli_fetch_array($result)) {

                $aValue['OP_NJOP_BUMI'] = number_format(($final['OP_NJOP_BUMI'] / $final['OP_LUAS_BUMI']) * $aValue['OP_LUAS_BUMI'], 0, '', '');

                $dbUtils = new DbUtils($this->dbSpec);
                $bValue['CPM_NJOP_TANAH'] = $aValue['OP_NJOP_BUMI'];
                $bValue['CPM_NJOP_BANGUNAN'] = $final['CPM_NJOP_BANGUNAN'];
                $bValue['CPM_NJOP_BUMI_BERSAMA'] = $final['CPM_NJOP_BUMI_BEBAN'];
                $bValue['CPM_NJOP_BANGUNAN_BERSAMA'] = $final['CPM_NJOP_BNG_BEBAN'];
                $bValue = $dbUtils->hitungTagihan($aValue, $appConfig);

                $aValue['OP_NJOP'] = $bValue['OP_NJOP'];
                $aValue['OP_NJKP'] = $bValue['OP_NJKP'];
                $aValue['OP_NJOPTKP'] = $bValue['OP_NJOPTKP'];
                $aValue['OP_TARIF'] = $bValue['OP_TARIF'];
                $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $bValue['SPPT_PBB_HARUS_DIBAYAR'];
            }
        }

        //$last_key = end(array_keys($aValue));
        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE cppmod_pbb_sppt_current SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }

        $query .= " WHERE NOP='$nop'";

        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function isExist($nop, $thn)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $thn = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($thn));

        $query = "SELECT * FROM cppmod_pbb_sppt_current WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$thn'";

        //echo $query;		
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes > 0);
        }
    }

    public function getYearList($currentYear)
    {

        //        $query = "SHOW TABLES like 'cppmod_pbb_sppt_current%'";
        //        
        //        if ($this->dbSpec->sqlQueryRow($query, $res)) {
        //            //return $res;
        //            //var_dump($res);
        //            foreach ($res as $data) {
        //                //echo $data['NAMATABEL'];
        //                var_dump($data);
        //            }
        //        }
    }

    public function insertIntoCurrent($val, $appConfig)
    {

        $queryInsertCurrent = $this->queryInsertCurrent($val);
        return $this->dbSpec->sqlQuery($queryInsertCurrent, $res);
    }
    public function insertIntoTagihanSPPT($val, $appConfig, $GWDBLink)
    {
        $queryInsertTagihan = $this->queryInsertIntoTagihanSPPT($val);
        //echo $queryInsertTagihan;
        return  mysqli_query($GWDBLink, $queryInsertTagihan);
    }

    function queryInsertCurrent($val)
    {
        $queryInsertCurrent = "INSERT INTO cppmod_pbb_sppt_current ( "
            . "NOP,  "
            . "SPPT_TAHUN_PAJAK,  "
            . "SPPT_TANGGAL_TERBIT,  "
            . "SPPT_TANGGAL_CETAK,  "
            . "SPPT_TANGGAL_JATUH_TEMPO,  "
            . "SPPT_PBB_HARUS_DIBAYAR,  "
            . "WP_NAMA,  "
            . "WP_ALAMAT,  "
            . "WP_RT,  "
            . "WP_RW,  " //10
            . "WP_KELURAHAN,  "
            . "WP_KECAMATAN,  "
            . "WP_KOTAKAB,  "
            . "WP_KODEPOS, "
            . "WP_NO_HP,  "
            . "OP_LUAS_BUMI,  "
            . "OP_LUAS_BANGUNAN,  "
            . "OP_KELAS_BUMI,  "
            . "OP_KELAS_BANGUNAN,  "
            . "OP_NJOP_BUMI,  " //20
            . "OP_NJOP_BANGUNAN,  "
            . "OP_NJOP,  "
            . "OP_NJOPTKP,  "
            . "OP_NJKP,  "
            . "OP_ALAMAT,  "
            . "OP_RT,  "
            . "OP_RW,  "
            . "OP_KELURAHAN, "
            . "OP_KECAMATAN, "
            . "OP_KOTAKAB, " //30
            . "OP_KELURAHAN_KODE, "
            . "OP_KECAMATAN_KODE, "
            . "OP_KOTAKAB_KODE,  "
            . "OP_PROVINSI_KODE,  "
            . "SPPT_PBB_PENGURANGAN,  "
            . "SPPT_PBB_PERSEN_PENGURANGAN,  "
            . "OP_TARIF, "
            . "SPPT_DOC_ID ";

        /* Jika CPM_LUAS_BUMI_BEBAN != NULL, berarti NOP memiliki NOP bersama*/
        if ($res[0]["CPM_NOP_BERSAMA"] != null && trim($res[0]["CPM_NOP_BERSAMA"]) != '') {
            $queryInsertCurrent .= ", OP_LUAS_BUMI_BERSAMA, OP_LUAS_BANGUNAN_BERSAMA, OP_KELAS_BUMI_BERSAMA, OP_KELAS_BANGUNAN_BERSAMA, OP_NJOP_BUMI_BERSAMA, OP_NJOP_BANGUNAN_BERSAMA";
        }
        $queryInsertCurrent .= ") VALUES ("
            . "'" . $val["CPM_NOP"] . "',"
            . "'" . $val["SPPT_TAHUN_PAJAK"] . "',"
            . "'" . $val["SPPT_TANGGAL_TERBIT"] . "',"
            . "'" . $val["SPPT_TANGGAL_CETAK"] . "',"
            . "'" . $val["SPPT_TANGGAL_JATUH_TEMPO"] . "',"
            . "'" . $val["SPPT_PBB_HARUS_DIBAYAR"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_WP_NAMA"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_WP_ALAMAT"]) . "',"
            . "'" . $val["CPM_WP_RT"] . "',"
            . "'" . $val["CPM_WP_RW"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KELURAHAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KECAMATAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KOTA"]) . "',"
            . "'" . $val["CPM_WP_KODEPOS"] . "',"
            . "'" . $val["CPM_WP_NO_HP"] . "',"
            . "'" . $val["CPM_OP_LUAS_TANAH"] . "',"
            . "'" . $val["CPM_OP_LUAS_BANGUNAN"] . "',"
            . "'" . $val["CPM_OP_KELAS_TANAH"] . "',"
            . "'" . $val["CPM_OP_KELAS_BANGUNAN"] . "',"
            . "'" . $val["CPM_NJOP_TANAH"] . "',"
            . "'" . $val["CPM_NJOP_BANGUNAN"] . "',"
            . "'" . $val["OP_NJOP"] . "',"
            . "'" . $val["OP_NJOPTKP"] . "',"
            . "'" . $val["OP_NJKP"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_OP_ALAMAT"]) . "',"
            . "'" . $val["CPM_OP_RT"] . "',"
            . "'" . $val["CPM_OP_RW"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KELURAHAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KECAMATAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KOTA"]) . "',"
            . "'" . $val["CPM_OP_KELURAHAN"] . "',"
            . "'" . $val["CPM_OP_KECAMATAN"] . "',"
            . "'" . $val["CPM_OP_KOTAKAB"] . "',"
            . "'" . substr($val["CPM_OP_KOTAKAB"], 0, 2) . "',"
            . "'0',"
            . "'0',"
            . "'" . $val["OP_TARIF"] . "',"
            . "'" . $val["UUID"] . "'";
        if ($val["CPM_NOP_BERSAMA"] != null && $val["CPM_NOP_BERSAMA"] != '') {
            $queryInsertCurrent .= ",'" . $val["CPM_LUAS_BUMI_BEBAN"] . "',"
                . "'" . $val["CPM_LUAS_BNG_BEBAN"] . "',"
                . "'" . $val["CPM_KELAS_BUMI_BEBAN"] . "',"
                . "'" . $val["CPM_KELAS_BNG_BEBAN"] . "',"
                . "'" . $val["CPM_NJOP_BUMI_BEBAN"] . "',"
                . "'" . $val["CPM_NJOP_BNG_BEBAN"] . "',";
        }
        $queryInsertCurrent .= ")";
        return $queryInsertCurrent;
    }

    function queryInsertIntoTagihanSPPT($val)
    {
        $queryInsertTagihan = "INSERT INTO PBB_SPPT ( "
            . "NOP,  "
            . "SPPT_TAHUN_PAJAK,  "
            . "SPPT_TANGGAL_TERBIT,  "
            . "SPPT_TANGGAL_CETAK,  "
            . "SPPT_TANGGAL_JATUH_TEMPO,  "
            . "SPPT_PBB_HARUS_DIBAYAR,  "
            . "WP_NAMA,  "
            . "WP_ALAMAT,  "
            . "WP_RT,  "
            . "WP_RW,  " //10
            . "WP_KELURAHAN,  "
            . "WP_KECAMATAN,  "
            . "WP_KOTAKAB,  "
            . "WP_KODEPOS, "
            . "WP_NO_HP,  "
            . "OP_LUAS_BUMI,  "
            . "OP_LUAS_BANGUNAN,  "
            . "OP_KELAS_BUMI,  "
            . "OP_KELAS_BANGUNAN,  "
            . "OP_NJOP_BUMI,  " //20
            . "OP_NJOP_BANGUNAN,  "
            . "OP_NJOP,  "
            . "OP_NJOPTKP,  "
            . "OP_NJKP,  "
            . "OP_ALAMAT,  "
            . "OP_RT,  "
            . "OP_RW,  "
            . "OP_KELURAHAN, "
            . "OP_KECAMATAN, "
            . "OP_KOTAKAB, " //30
            . "OP_KELURAHAN_KODE, "
            . "OP_KECAMATAN_KODE, "
            . "OP_KOTAKAB_KODE,  "
            . "OP_PROVINSI_KODE "
            . " ) VALUES ("
            . "'" . $val["CPM_NOP"] . "',"
            . "'" . $val["SPPT_TAHUN_PAJAK"] . "',"
            . "'" . $val["SPPT_TANGGAL_TERBIT"] . "',"
            . "'" . $val["SPPT_TANGGAL_CETAK"] . "',"
            . "'" . $val["SPPT_TANGGAL_JATUH_TEMPO"] . "',"
            . "'" . $val["SPPT_PBB_HARUS_DIBAYAR"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_WP_NAMA"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_WP_ALAMAT"]) . "',"
            . "'" . $val["CPM_WP_RT"] . "',"
            . "'" . $val["CPM_WP_RW"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KELURAHAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KECAMATAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["WP_KOTA"]) . "',"
            . "'" . $val["CPM_WP_KODEPOS"] . "',"
            . "'" . $val["CPM_WP_NO_HP"] . "',"
            . "'" . $val["CPM_OP_LUAS_TANAH"] . "',"
            . "'" . $val["CPM_OP_LUAS_BANGUNAN"] . "',"
            . "'" . $val["CPM_OP_KELAS_TANAH"] . "',"
            . "'" . $val["CPM_OP_KELAS_BANGUNAN"] . "',"
            . "'" . $val["CPM_NJOP_TANAH"] . "',"
            . "'" . $val["CPM_NJOP_BANGUNAN"] . "',"
            . "'" . $val["OP_NJOP"] . "',"
            . "'" . $val["OP_NJOPTKP"] . "',"
            . "'" . $val["OP_NJKP"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["CPM_OP_ALAMAT"]) . "',"
            . "'" . $val["CPM_OP_RT"] . "',"
            . "'" . $val["CPM_OP_RW"] . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KELURAHAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KECAMATAN"]) . "',"
            . "'" . mysqli_real_escape_string($this->dbSpec->getDBLink(), $val["OP_KOTA"]) . "',"
            . "'" . $val["CPM_OP_KELURAHAN"] . "',"
            . "'" . $val["CPM_OP_KECAMATAN"] . "',"
            . "'" . $val["CPM_OP_KOTAKAB"] . "',"
            . "'" . substr($val["CPM_OP_KOTAKAB"], 0, 2) . "')";
        return $queryInsertTagihan;
    }


    // by 35uteh 4 april

    public function getDataTagihanSPPT($nop, $tahun, $GWDBLink)
    {
        $query = "SELECT * FROM pbb_sppt WHERE NOP = '{$nop}' AND SPPT_TAHUN_PAJAK = '{$tahun}'";

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

    public function getDataCurrent($nop)
    {
        $query = "SELECT * FROM cppmod_pbb_sppt_current WHERE NOP = '{$nop}' ";
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res[0];
        }
    }
    public function updateCurrentSPPT($nop, $tahun, $aValue, $appConfig)
    {
        $nop    = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun  = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE $appConfig[ADMIN_SW_DBNAME].cppmod_pbb_sppt_current SET ";

        foreach ($aValue as $key => $value) {
            $query .= "$key='$value'";
            if ($key != $last_key) {
                $query .= ", ";
            }
        }


        $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query, $res);
    }

    public function updateTagihanSPPT($nop, $tahun, $aValue, $GWDBLink)
    {
        $nop    = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $tahun  = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($tahun));

        $last_key = array_keys($aValue);
        $last_key = end($last_key);
        $query = "UPDATE PBB_SPPT SET ";

        foreach ($aValue as $key => $value) {
            // $query .= "$key='$value'";
            $query .= ' ' . $key . ' ="' . $value . '"';
            if ($key != $last_key) {
                $query .= ", ";
            }
        }
        //echo $query; exit;
        $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";
        return mysqli_query($GWDBLink, $query) or die(mysqli_error($GWDBLink));
    }
    public function getLastPenetapan($nop, $tahun, $GWDBLink)
    {
        $query = "SELECT MAX(PENETAPAN_KE) AS PENETAPAN_KE FROM pbb_sppt_penetapan_ulang WHERE NOP = '{$nop}' AND SPPT_TAHUN_PAJAK = '{$tahun}' ";

        $res = mysqli_query($GWDBLink, $query);
        if (!$res) {
            echo mysqli_error($GWDBLink);
            echo $query;
            return false;
            exit;
        }

        $row = mysqli_fetch_assoc($res);
        return $row['PENETAPAN_KE'];
    }


    // end by 35utech


}
