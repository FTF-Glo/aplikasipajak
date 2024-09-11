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
                if(is_numeric($srch)){
                    $query .= " AND (NOP='$srch') ";
                    $queryCount .= " AND (NOP='$srch') ";
                }else{
                    $query .= " AND (WP_NAMA LIKE '%$srch%') ";
                    $queryCount .= " AND (WP_NAMA LIKE '%$srch%') ";
                }

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
                if(is_numeric($srch)){
                    $query .= " WHERE (NOP = '$srch') ";
                    $queryCount .= " WHERE (NOP = '$srch') ";
                }else{
                    $query .= " WHERE (WP_NAMA LIKE '%$srch%') ";
                    $queryCount .= " WHERE (WP_NAMA LIKE '%$srch%') ";
                }

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

        $query .= "ORDER BY SPPT_TANGGAL_TERBIT DESC, NOP ASC LIMIT $hal, $perpage ";

        // echo $query; exit;

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */


        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            // sebagai Tanda Development
            // $dev = false;
            // $dedibrowser = explode(' ', $_SERVER['HTTP_USER_AGENT']);
            // if(in_array("Firefox/115.0", $dedibrowser)) $dev = true;

            // if($dev){
                // added d3Di - add QRIS icon
                $datenow = date('Y-m-d H:i:s');
                
                $nopnop = [];
                foreach ($res as $v) {
                    $bayar = $v['SPPT_PBB_HARUS_DIBAYAR'];
                    if($bayar>0 && $bayar<=10000000){
                        array_push($nopnop,"'".$v['NOP']."'");
                    }
                }

                
                $countNOP = count($nopnop);
                $nopnop = implode(',',$nopnop);
                
                $whereNOPin = ($countNOP>0) ? "AND p.tax_object IN ($nopnop)" : "AND p.tax_object='999-999'";

                $qry = "SELECT 
                            p.tax_object, 
                            IFNULL(g.PAYMENT_FLAG,0) AS flag
                        FROM gw_pbb.pbb_sppt_qris p 
                        INNER JOIN gw_pbb.pbb_sppt g ON p.tax_object=g.NOP AND p.`year`=g.SPPT_TAHUN_PAJAK
                        WHERE 
                            g.SPPT_PBB_HARUS_DIBAYAR>0 AND 
                            g.SPPT_PBB_HARUS_DIBAYAR<=10000000 AND 
                            p.expired_date_time>='$datenow' AND 
                            p.`year`='$tahun' $whereNOPin";
                $this->dbSpec->sqlQueryRow($qry, $qrExist);

                $nopnop = [];
                $nopflag = [];
                
                foreach ($qrExist as $v) {
                    array_push($nopnop,$v['tax_object']);
                    $nopflag[$v['tax_object']] = $v['flag'];
                }

                
                $bayar = 0;
                $datenow = new DateTime($datenow);

                foreach ($res as $k=>$row) {
                    for ($i=0; $i<=57; $i++) { 
                        if(isset($row[$i]) || $row[$i]==''){
                            unset($res[$k][$i]);
                        }
                    }
                    $nop    = $row['NOP'];
                    $dateexp= $row['SPPT_TANGGAL_JATUH_TEMPO'];
                    $bayar  = $row['SPPT_PBB_HARUS_DIBAYAR'];
                    $adaqris= in_array($nop,$nopnop);
                    $flagis = isset($nopflag[$nop]) ? $nopflag[$nop]:0;
                    
                    $exprdx  = (strlen($dateexp)>=11) ? $dateexp : $dateexp.' 23:59:59';
                    $dateexp = new DateTime($exprdx);
                    $sha1    = sha1('#PBB#LAMPUNG#SELATAN#'.$nop.'#'.date('Ymd').'#');
                    $parPOST = "'$nop','$sha1',$tahun,'$exprdx'";

                    if($adaqris && $flag==0){
                        $imgQRIS = '<img id="idico'.$nop.'" data-re="'.$parPOST.'" src="./image/icon/qr.png" width="20px" height="20px">';
                    }elseif($adaqris){
                        $imgQRIS = '<span data-flag="'.$flagis.'"></span>';
                    }else{
                        if(($bayar>0 && $bayar<=10000000 && $flagis==0 && $dateexp>=$datenow && $nop!='')){
                            $imgQRIS = '<div id="divico'.$nop.'"><a href="javascript:;" onclick="getQRCode('.$parPOST.')" title="Klik untuk generate QRIS"><img id="idico'.$nop.'" src="./image/icon/qr_disable.png" width="20px" height="20px"></a>';
                        }else{
                            $imgQRIS = '<span data-tagihan="'.$bayar.'" data-flag="'.$flagis.'" data-exp="'.$exprdx.'"></span>';
                        }
                    }
                    $res[$k]['QRIS'] = $imgQRIS;
                }

                // print_r($nopflag);exit;
                // print_r($res);exit;
                return $res;
            // }
            return $res;
        }
    }

    public function changeStimulus($filter = array(), $tahun, $pengurangan, $appConfig)
    {
        //$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
        $res = array();

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
        //$this->dbSpec->sqlQueryRow($queryCount, $total);
        //$this->totalrows = $total[0]['TOTAL'];

        //$query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";
        $query .= "ORDER BY FLAG ASC, NOP ASC";

        // echo $query; exit;

        /* if (!$jumhal){
        $query .= "LIMIT 10 ";
        }
        else if ($jumhal){
        $query .= "LIMIT $jumhal ";
        } */

        $GW_DBHOST = $appConfig['GW_DBHOST'];
        $GW_DBPORT = (isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306');
        $GW_DBNAME = $appConfig['GW_DBNAME'];
        $GW_DBUSER = $appConfig['GW_DBUSER'];
        $GW_DBPWD = $appConfig['GW_DBPWD'];
        $dbConn = mysqli_connect($GW_DBHOST, $GW_DBUSER, $GW_DBPWD, $GW_DBNAME, $GW_DBPORT);

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            if ($res != null && count($res) > 0) {
                for ($x = 0; $x < count($res); $x++) {
                    $newNJKP = $res[$x]["SPPT_PBB_HARUS_DIBAYAR"] - (($pengurangan / 100) * $res[$x]["SPPT_PBB_HARUS_DIBAYAR"]);
                    $queryupdate = "UPDATE " . $table . " set SPPT_PBB_HARUS_DIBAYAR = '" . $newNJKP . "', PENGURANGAN_STIMULUS = '" . $pengurangan . "' where NOP = '" . $res[$x]["NOP"] . "'";
                    $this->dbSpec->sqlQueryRun($queryupdate);

                    $queryupdates = "UPDATE pbb_sppt set SPPT_PBB_HARUS_DIBAYAR = '" . $newNJKP . "', PENGURANGAN_STIMULUS = '" . $pengurangan . "' where NOP = '" . $res[$x]["NOP"] . "'";
                    $ress = mysqli_query($dbConn, $queryupdates);
                }
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
            for ($x = 0; $x < count($res); $x++) {
                //$newNJKP = $res[$x]["OP_NJKP"] - (($pengurangan/100) * $res[$x]["OP_NJKP"]);
                $queryupdate = "UPDATE " . $table . " set OP_NJKP_TEMP = '" . $pengurangan . "', PENGURANGAN_STIMULUS_TEMP = '" . $pengurangan . "' where NOP = '" . $res[$x]["NOP"] . "' and SPPT_TAHUN_PAJAK = '" . $tahun . "'";
                $this->dbSpec->sqlQueryRun($queryupdate);
            }
        }

        return true;
    }

    public function changeStimulusReal($filter = array(), $tahun, $pengurangan, $appConfig)
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

        $GW_DBHOST = $appConfig['GW_DBHOST'];
        $GW_DBPORT = (isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306');
        $GW_DBNAME = $appConfig['GW_DBNAME'];
        $GW_DBUSER = $appConfig['GW_DBUSER'];
        $GW_DBPWD = $appConfig['GW_DBPWD'];

        $dbConn = mysqli_connect($GW_DBHOST, $GW_DBUSER, $GW_DBPWD, $GW_DBNAME, $GW_DBPORT);

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            for ($x = 0; $x < count($res); $x++) {
                //$newNJKP = $res[$x]["OP_NJKP"] - (($pengurangan/100) * $res[$x]["OP_NJKP"]);
                $queryupdate = "UPDATE " . $table . " set SPPT_PBB_HARUS_DIBAYAR = '" . $pengurangan . "', PENGURANGAN_STIMULUS = '" . $res[$x]["SPPT_PBB_HARUS_DIBAYAR"] . "' where NOP = '" . $res[$x]["NOP"] . "' and SPPT_TAHUN_PAJAK = '" . $tahun . "'";
                $this->dbSpec->sqlQueryRun($queryupdate);

                $queryupdateGW = "UPDATE pbb_sppt set SPPT_PBB_HARUS_DIBAYAR = '" . $pengurangan . "', PENGURANGAN_STIMULUS = '" . $res[$x]["SPPT_PBB_HARUS_DIBAYAR"] . "' where NOP = '" . $res[$x]["NOP"] . "' and SPPT_TAHUN_PAJAK = '" . $tahun . "'";
                $resGW = mysqli_query($dbConn, $queryupdateGW);
            }
        }

        return true;
    }

    public function get70gs($filter = array(), $srch, $buku, $additionalWhereQuery, $jumhal, $perpage, $page, $tahun, $appConfig, $simulasi = false)
    {
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        if ($tahun == $appConfig['tahun_tagihan']) {
            $table = 'cppmod_pbb_sppt_current' . ($simulasi ? '_simulasi' : ''); // aldes
            $tablebefore = "cppmod_pbb_sppt_cetak_" . ($tahun - 1);
        } else {
            $table = "cppmod_pbb_sppt_cetak_$tahun";
            $tablebefore = "cppmod_pbb_sppt_cetak_$tahun";
        }

        $querybefore = "SELECT count(SPPT_PBB_HARUS_DIBAYAR) as 'jumlah', SUM(SPPT_PBB_HARUS_DIBAYAR) as 'total_sppt', SUM(OP_LUAS_BUMI) as 'total_luas_bumi', SUM(OP_LUAS_BANGUNAN) as 'total_luas_bangunan' FROM $tablebefore ";

        $query = "SELECT count(SPPT_PBB_HARUS_DIBAYAR) as 'jumlah', SUM(CASE WHEN OP_NJKP_TEMP = 0 THEN SPPT_PBB_HARUS_DIBAYAR ELSE OP_NJKP_TEMP END) as 'total_sppt', SUM(OP_LUAS_BUMI) as 'total_luas_bumi', SUM(OP_LUAS_BANGUNAN) as 'total_luas_bangunan' FROM $table ";

        $querys = null;

        if (count($filter) > 0) {
            $querys .= " AND";
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
                    $querys .= " NOP IN ($listNOP) ";
                } else {
                    $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                    if ($key == "NOP") {
                        $querys .= " $key = '$value' ";
                    } else {
                        $querys .= " $key LIKE '%$value%' ";
                    }
                    if ($key != $last_key) {
                        $querys .= " AND ";
                    }
                }
            }

            if ($srch) {
                $querys .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $querys .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $querys .= "AND (" . $additionalWhereQuery . ")";
                }
            }
        } else {
            if ($srch) {
                $querys .= " AND (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";

                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $querys .= "AND (" . $additionalWhereQuery . ")";
                }
            } else {
                if ($additionalWhereQuery != null && !empty($additionalWhereQuery)) {
                    $querys .= " AND (" . $additionalWhereQuery . ")";
                }
            }
        }

        $querybefore1  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 0 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 100000 " . $querys;
        $querybefore2  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 100001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 500000 " . $querys;
        $querybefore3  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 500001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 2000000 " . $querys;
        $querybefore4  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 2000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 5000000 " . $querys;
        $querybefore5  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 5000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 999999999999999 " . $querys;

        $query1  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 0 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 100000 " . $querys;
        $query2  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 100001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 500000 " . $querys;
        $query3  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 500001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 2000000 " . $querys;
        $query4  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 2000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 5000000 " . $querys;
        $query5  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 5000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 999999999999999 " . $querys;

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

        $resdbefore = null;
        $resdbefore2 = null;
        $resdbefore3 = null;
        $resdbefore4 = null;
        $resdbefore5 = null;

        $resd = null;
        $resd2 = null;
        $resd3 = null;
        $resd4 = null;
        $resd5 = null;

        $resbefore = $this->dbSpec->sqlQueryRow($querybefore1, $resdbefore);

        if ($resdbefore != null && count($resdbefore) > 0) {
            $jumlahbukubefore1 = $resdbefore[0]["jumlah"];
            $luasbumi1 = $resdbefore[0]["total_luas_bumi"];
            $luasbangunan1 = $resdbefore[0]["total_luas_bangunan"];
            $totalbukubefore1 = $resdbefore[0]["total_sppt"] == null ? 0 : $resdbefore[0]["total_sppt"];
        }

        $resbefore2 = $this->dbSpec->sqlQueryRow($querybefore2, $resdbefore2);

        if ($resdbefore2 != null && count($resdbefore2) > 0) {
            $jumlahbukubefore2 = $resdbefore2[0]["jumlah"];
            $luasbumi2 = $resdbefore2[0]["total_luas_bumi"];
            $luasbangunan2 = $resdbefore2[0]["total_luas_bangunan"];
            $totalbukubefore2 = $resdbefore2[0]["total_sppt"] == null ? 0 : $resdbefore2[0]["total_sppt"];
        }

        $resbefore3 = $this->dbSpec->sqlQueryRow($querybefore3, $resdbefore3);

        if ($resdbefore3 != null && count($resdbefore3) > 0) {
            $jumlahbukubefore3 = $resdbefore3[0]["jumlah"];
            $luasbumi3 = $resdbefore3[0]["total_luas_bumi"];
            $luasbangunan3 = $resdbefore3[0]["total_luas_bangunan"];
            $totalbukubefore3 = $resdbefore3[0]["total_sppt"] == null ? 0 : $resdbefore3[0]["total_sppt"];
        }

        $resbefore4 = $this->dbSpec->sqlQueryRow($querybefore4, $resdbefore4);

        if ($resdbefore4 != null && count($resdbefore4) > 0) {
            $jumlahbukubefore4 = $resdbefore4[0]["jumlah"];
            $luasbumi4 = $resdbefore4[0]["total_luas_bumi"];
            $luasbangunan4 = $resdbefore4[0]["total_luas_bangunan"];
            $totalbukubefore4 = $resdbefore4[0]["total_sppt"] == null ? 0 : $resdbefore4[0]["total_sppt"];
        }

        $resbefore5 = $this->dbSpec->sqlQueryRow($querybefore5, $resdbefore5);

        if ($resdbefore5 != null && count($resdbefore5) > 0) {
            $jumlahbukubefore5 = $resdbefore5[0]["jumlah"];
            $luasbumi5 = $resdbefore5[0]["total_luas_bumi"];
            $luasbangunan5 = $resdbefore5[0]["total_luas_bangunan"];
            $totalbukubefore5 = $resdbefore5[0]["total_sppt"] == null ? 0 : $resdbefore5[0]["total_sppt"];
        }

        $res = $this->dbSpec->sqlQueryRow($query1, $resd);

        if ($resd != null && count($resd) > 0) {
            $jumlahbuku1 = $resd[0]["jumlah"];
            $luasbumi1 = $resd[0]["total_luas_bumi"];
            $luasbangunan1 = $resd[0]["total_luas_bangunan"];
            $totalbuku1 = $resd[0]["total_sppt"] == null ? 0 : $resd[0]["total_sppt"];
        }

        $res = $this->dbSpec->sqlQueryRow($query2, $resd2);

        if ($resd2 != null && count($resd2) > 0) {
            $jumlahbuku2 = $resd2[0]["jumlah"];
            $luasbumi2 = $resd2[0]["total_luas_bumi"];
            $luasbangunan2 = $resd2[0]["total_luas_bangunan"];
            $totalbuku2 = $resd2[0]["total_sppt"] == null ? 0 : $resd2[0]["total_sppt"];
        }

        $res = $this->dbSpec->sqlQueryRow($query3, $resd3);

        if ($resd3 != null && count($resd3) > 0) {
            $jumlahbuku3 = $resd3[0]["jumlah"];
            $luasbumi3 = $resd3[0]["total_luas_bumi"];
            $luasbangunan3 = $resd3[0]["total_luas_bangunan"];
            $totalbuku3 = $resd3[0]["total_sppt"] == null ? 0 : $resd3[0]["total_sppt"];
        }

        $res = $this->dbSpec->sqlQueryRow($query4, $resd4);

        if ($resd4 != null && count($resd4) > 0) {
            $jumlahbuku4 = $resd4[0]["jumlah"];
            $luasbumi4 = $resd4[0]["total_luas_bumi"];
            $luasbangunan4 = $resd4[0]["total_luas_bangunan"];
            $totalbuku4 = $resd4[0]["total_sppt"] == null ? 0 : $resd4[0]["total_sppt"];
        }

        $res = $this->dbSpec->sqlQueryRow($query5, $resd5);

        if ($resd5 != null && count($resd5) > 0) {
            $jumlahbuku5 = $resd5[0]["jumlah"];
            $luasbumi5 = $resd5[0]["total_luas_bumi"];
            $luasbangunan5 = $resd5[0]["total_luas_bangunan"];
            $totalbuku5 = $resd5[0]["total_sppt"] == null ? 0 : $resd5[0]["total_sppt"];
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

        $ress[0]["kenaikan"] = ($totalbukubefore1 > 0) ? ((($totalbuku1 - $totalbukubefore1) / $totalbukubefore1) * 100) : 0;
        $ress[1]["kenaikan"] = $totalbukubefore2 > 0 ? ((($totalbuku2 - $totalbukubefore2) / $totalbukubefore2) * 100) : 0;
        $ress[2]["kenaikan"] = $totalbukubefore3 > 0 ? ((($totalbuku3 - $totalbukubefore3) / $totalbukubefore3) * 100) : 0;
        $ress[3]["kenaikan"] = $totalbukubefore4 > 0 ? ((($totalbuku4 - $totalbukubefore4) / $totalbukubefore4) * 100) : 0;
        $ress[4]["kenaikan"] = $totalbukubefore5 > 0 ? ((($totalbuku5 - $totalbukubefore5) / $totalbukubefore5) * 100) : 0;

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

    public function get70ssb($filter = array(), $srch, $additionalWhereQuery, $jumhal, $perpage, $page, $tahun, $appConfig)
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

        $queryCount = "SELECT COUNT(*) AS TOTAL from pbb_sppt_temp A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";
        $query = "SELECT A.* , IFNULL(A.sppt_pbb_harus_dibayar,0) AS 'SPPT_PBB_HARUS_DIBAYAR', IFNULL(B.pbb_denda,0) as 'PBB_DENDA' , IFNULL(A.sppt_pbb_harus_dibayar+B.pbb_denda,0) as 'PBB_TOTAL_BAYAR' from pbb_sppt_temp A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";

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

    public function gets($filter = array(), $srch, $jumhal, $perpage, $page)
    {

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_sppt_current ";
        $queryCountUnion = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_sppt_current_simulasi ";
        $query = "SELECT * FROM cppmod_pbb_sppt_current ";
        $queryUnion = "SELECT * FROM cppmod_pbb_sppt_current_simulasi ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
            $queryCount .= "WHERE ";
            $queryUnion .= "WHERE ";
            $queryCountUnion .= "WHERE ";
            $last_key = array_keys($filter);
            $last_key = end($last_key);

            foreach ($filter as $key => $value) {
                $value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
                if ($key == "NOP") {
                    $queryCount .= " $key = '$value' ";
                    $query .= " $key = '$value' ";
                    $queryCountUnion .= " $key = '$value' ";
                    $queryUnion .= " $key = '$value' ";
                } else {
                    $queryCount .= " $key LIKE '%$value%' ";
                    $query .= " $key LIKE '%$value%' ";
                    $queryCountUnion .= " $key LIKE '%$value%' ";
                    $queryUnion .= " $key LIKE '%$value%' ";
                }
                if ($key != $last_key) {
                    $queryCount .= " AND ";
                    $query .= " AND ";
                    $queryCountUnion .= " AND ";
                    $queryUnion .= " AND ";
                }
            }
        }

        if ($srch) {
            $query .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCount .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryUnion .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
            $queryCountUnion .= " WHERE (NOP LIKE '%$srch%' OR WP_NAMA LIKE '%$srch%') ";
        }

        $this->dbSpec->sqlQueryRow($queryCount, $total);
        $this->totalrows = $total[0]['TOTAL'];

        $this->dbSpec->sqlQueryRow($queryCountUnion, $total2);
        $this->totalrows += $total2[0]['TOTAL'];

        $queryAll = $query . " UNION " . $queryUnion . " ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";

        /* if (!$jumhal){
          $query .= "LIMIT 10 ";
          }
          else if ($jumhal){
          $query .= "LIMIT $jumhal ";
          } */

        if ($this->dbSpec->sqlQueryRow($queryAll, $res)) {
            return $res;
        }
    }

    public function get($filter = array(), $srch, $jumhal, $perpage, $page)
    {

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $queryCount = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_sppt_current ";
        $query = "SELECT * FROM cppmod_pbb_sppt_current ";

        if (count($filter) > 0) {
            $query .= "WHERE ";
			$queryCount .= "WHERE ";
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

	    //$query .= "ORDER BY FLAG ASC, NOP ASC LIMIT $hal, $perpage ";
        $query .= "ORDER BY OP_NJKP DESC, NOP ASC LIMIT $hal, $perpage ";

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
