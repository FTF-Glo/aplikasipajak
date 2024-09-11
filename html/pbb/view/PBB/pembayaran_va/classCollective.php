<?php
date_default_timezone_set('Asia/Jakarta');

class classCollective
{
    private $dbSpec = null;
    private $dbUtils;
    private $appConfig;

    public $C_HOST_PORT;
    public $C_USER;
    public $C_PWD;
    public $C_DB;
    public $userID;
    // aldes
    public $C_PORT;

    public function __construct($dbSpec, $dbUtils = null, $appConfig = null)
    {
        $this->dbSpec = $dbSpec;
        $this->dbUtils = $dbUtils; // aldes
        $this->appConfig = $appConfig; // aldes
    }       

    public function global_search()
    {
        $cbKecamatan = isset($_POST['cbKecamatan']) ? $_POST['cbKecamatan'] : "";
        $cbKelurahan = isset($_POST['cbKelurahan']) ? $_POST['cbKelurahan'] : "";
        $cbStatus = isset($_POST['cbStatus']) ? $_POST['cbStatus'] : "";
        $txSearch = isset($_POST['txSearch']) ? $_POST['txSearch'] : "";

        $where = "(1=1 ";
        
        $area = $cbKelurahan != "" ? $cbKelurahan : ($cbKecamatan != "" ? $cbKecamatan : "");
        if ($area != "") {
            $where .= "AND CPM_CG_AREA_CODE LIKE '{$area}%' ";
        }

        if ($cbStatus != "") {
            $where .= "AND CPM_CG_STATUS = '{$cbStatus}' ";
        }

        if ($txSearch != "") {
            $where .= "AND (
                CPM_CG_NAME LIKE '%{$txSearch}%'
                OR CPM_CG_COLLECTOR LIKE '%{$txSearch}%'
                OR CPM_CG_HP_COLLECTOR LIKE '%{$txSearch}%'
                OR CPM_CG_PAYMENT_CODE LIKE '%{$txSearch}%'
                OR CPM_CG_STATUS LIKE '%{$txSearch}%'
                OR CPM_CG_DESC LIKE '%{$txSearch}%'
                OR CPM_CG_EXPIRED_DATE LIKE '%{$txSearch}%'
                OR USERPBB.nm_lengkap LIKE '%{$txSearch}%'
                OR KC.CPC_TKC_KECAMATAN LIKE '%{$txSearch}%'
                OR K.CPC_TKL_KELURAHAN LIKE '%{$txSearch}%'
            )";
        }

        $where .= ") ";

        return $where;

    }

    public function getDetailGroup($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $joinDenda = "LEFT JOIN pbb_denda P ON A.CPM_CGM_NOP = P.NOP AND A.CPM_CGM_TAX_YEAR = P.SPPT_TAHUN_PAJAK";
        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C 
        ON C.NOP = P.NOP AND C.TAHUN = P.SPPT_TAHUN_PAJAK 
        LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";
        $joinMember = "LEFT JOIN (SELECT SUM(IFNULL(P.PBB_DENDA, 0) - IFNULL(B.NILAI, 0)) AS TOTAL_DENDA, A.CPM_CGM_ID FROM cppmod_cg_member A {$joinDenda} {$joinPengurangan} GROUP BY A.CPM_CGM_ID) CGMEMBER ON CGMEMBER.CPM_CGM_ID = G.CPM_CG_ID";
        
        $query = "SELECT 
            S.STATUS_NAME,
            G.*, 
            KL.CPC_TKL_KELURAHAN AS NAMA_KELURAHAN,
            KL.CPC_TKL_KCID AS KCID, 
            KC.CPC_TKC_KECAMATAN AS NAMA_KECAMATAN,
            CGMEMBER.TOTAL_DENDA AS TOTAL_DENDA
        FROM 
            cppmod_collective_group G
            LEFT JOIN cppmod_collective_group_status S on S.ID = G.CPM_CG_STATUS 
            LEFT JOIN  cppmod_tax_kelurahan AS KL ON KL.CPC_TKL_ID = G.CPM_CG_AREA_CODE
            LEFT JOIN  cppmod_tax_kecamatan AS KC ON KL.CPC_TKL_KCID = KC.CPC_TKC_ID
            {$joinMember}
		WHERE G.CPM_CG_ID = '$id'";
        // echo $query;
        // $queryCountMember = "SELECT COUNT(*) FROM cppmod_cg_temp_member WHERE CPM_CGTM_ID = ''"

        $array = array();
        
        $result = mysqli_query($LDBLink, $query);
        $no = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $array[$no]['ID']               = $id;
            $array[$no]['KCID']             = $row['KCID'];
            $array[$no]['NAMA_KELURAHAN']   = $row['NAMA_KELURAHAN'];
            $array[$no]['NAMA_KECAMATAN']   = $row['NAMA_KECAMATAN'];
            $array[$no]['CPM_CG_NAME']      = $row['CPM_CG_NAME'];
            $array[$no]['CPM_CG_DESC']      = $row['CPM_CG_DESC'];
            $array[$no]['CPM_CG_COLLECTOR'] = $row['CPM_CG_COLLECTOR'];
            $array[$no]['CPM_CG_HP_COLLECTOR'] = $row['CPM_CG_HP_COLLECTOR'];
            $array[$no]['CPM_CG_AREA_CODE'] = $row['CPM_CG_AREA_CODE'];
            $array[$no]['CPM_CG_NOP_NUMBER'] = $row['CPM_CG_NOP_NUMBER'];
            $array[$no]['CPM_CG_PAYMENT_CODE'] = $row['CPM_CG_PAYMENT_CODE'];
            $array[$no]['CPM_CG_ORIGINAL_AMOUNT'] = $row['CPM_CG_ORIGINAL_AMOUNT'];
            $array[$no]['CPM_CG_EXPIRED_DATE'] = $row['CPM_CG_EXPIRED_DATE'];
            $array[$no]['CPM_CG_STATUS']    = $row['CPM_CG_STATUS'];
            $array[$no]['STATUS_NAME']      = $row['STATUS_NAME'];
            $array[$no]['CPM_CG_PAY_DATE']  = $row['CPM_CG_PAY_DATE'];
            $array[$no]['TOTAL_DENDA']      = $row['TOTAL_DENDA'];
            $no++;
        }
        return $array;
    }

    public function getMemberTempByID($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        /* Useful $_POST Variables coming from the plugin */
        $draw                   = $_REQUEST["draw"]; //counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
        $orderByColumnIndex     = $_REQUEST['order'][0]['column']; // index of the sorting column (0 index based - i.e. 0 is the first record)

        if ($_REQUEST['columns'][$orderByColumnIndex]['data'] == "0") {
            $orderBy = "NAMA_GROUP";
        } else {
            $orderBy  = $_REQUEST['columns'][$orderByColumnIndex]['data']; //Get name of the sorting column from its index
            switch ($orderBy) {
                case '2':
                    $orderBy = "SPPT_TAHUN_PAJAK";
                    break;

                case '3':
                    $orderBy = "SPPT_TANGGAL_JATUH_TEMPO";
                    break;

                case '4':
                    $orderBy = "WP_NAMA";
                    break;

                case '5':
                    $orderBy = "OP_KECAMATAN";
                    break;

                case '6':
                    $orderBy = "OP_KELURAHAN";
                    break;

                case '7':
                    $orderBy = "SPPT_PBB_HARUS_DIBAYAR";
                    break;

                case '8':
                    $orderBy = "PBB_DENDA";
                    break;

                case '9':
                    $orderBy = "SPPT_PBB_HARUS_DIBAYAR + PBB_DENDA";
                    break;
                default:
                    $orderBy = "NOP";
                    break;
            }
        }
        $orderType  = $_REQUEST['order'][0]['dir']; // ASC or DESC

        if (isset($_REQUEST['start'])) {
            $start                  = $_REQUEST["start"]; //Paging first record indicator.
        } else {
            $start                  = 0;
        }

        if (isset($_REQUEST['length']) && $_REQUEST['length'] > 0) {
            $length                 = $_REQUEST['length']; //Number of records that the table can display in the current draw
            $limitQuery = "limit {$start}, {$length}";
        } else {
            $length = 0;
            $limitQuery = "";
        }
        /* END of POST variables */
        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C 
                                ON C.NOP = P.NOP AND C.TAHUN = P.SPPT_TAHUN_PAJAK 
                            LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";
        $selectPengurangan = "IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN";

        $query = "SELECT G.CPM_CG_NAME NAMA_GROUP, P.*, {$selectPengurangan} FROM cppmod_cg_temp_member T INNER JOIN pbb_sppt P ON T.CPM_CGTM_NOP = P.NOP INNER JOIN cppmod_collective_group G ON G.CPM_CG_ID = T.CPM_CGTM_ID {$joinPengurangan} WHERE T.CPM_CGTM_ID = '$id' and P.SPPT_TAHUN_PAJAK = T.CPM_CGTM_TAX_YEAR group by P.NOP, P.SPPT_TAHUN_PAJAK";
        $recordsTotal = count($this->getDataJson($query));
        if (!empty($_REQUEST['search']['value'])) {
            /* WHERE Clause for searching */
            for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
                if ($_REQUEST['columns'][$i]['searchable'] == "true") {

                    $column     =   $_REQUEST['columns'][$i]['name']; //we get the name of each column using its index from POST request
                    $where[]    =   "$column like '%" . $_REQUEST['search']['value'] . "%'";
                }
            }
            $where = "WHERE " . implode(" OR ", $where); // id like '%searchValue%' or name like '%searchValue%' ....
            /* End WHERE */
            $sql = "SELECT * FROM ($query) as d $where  {$limitQuery}  ";

            $recordsFiltered = count($this->getDataJson($sql)); //Count of search result
            $data = $this->getDataJson($sql);
        } else {
            $sql = "SELECT * FROM ($query) as d  ORDER BY $orderBy $orderType {$limitQuery} ";
            $data = $this->getDataJson($sql);
            $recordsFiltered = $recordsTotal;
        }

        $response = array(
            "draw"             => intval($draw),
            "recordsTotal"     => $recordsTotal,
            "recordsFiltered"  => $recordsFiltered,
            "data"             => $data
        );

        echo json_encode($response);


        exit;
        // echo "masuk";
        // $LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //               mysql_select_db($this->C_DB,$LDBLink);

        // $nop   = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));	

        // $query = "SELECT
        // 		G.CPM_CG_NAME NAMA_GROUP,
        // 		P.*
        // 		FROM
        // 		cppmod_cg_temp_member T 
        // 		INNER JOIN 
        // 		pbb_sppt P ON T.CPM_CGTM_NOP = P.NOP 
        // 		INNER JOIN
        // 		cppmod_collective_group G ON G.CPM_CG_ID = T.CPM_CGTM_ID
        // 		WHERE T.CPM_CGTM_ID = '$id' and P.SPPT_TAHUN_PAJAK = T.CPM_CGTM_TAX_YEAR
        // 		group by P.NOP, P.SPPT_TAHUN_PAJAK
        // 		limit 50
        // 		";

        // 		// echo $query;

        // $array = array();	
        // $result = mysqli_query($LDBLink, $query);
        // $no = 0;
        //      	while ($row = mysqli_fetch_assoc($result)){
        // 	$array[$no]['NAMA_GROUP']= $row['NAMA_GROUP'];
        // 	$array[$no]['NOP']= $row['NOP'];
        // 	$array[$no]['WP_NAMA']= $row['WP_NAMA'];
        // 	$array[$no]['SPPT_TAHUN_PAJAK']= $row['SPPT_TAHUN_PAJAK'];
        // 	$array[$no]['SPPT_TANGGAL_JATUH_TEMPO']= $row['SPPT_TANGGAL_JATUH_TEMPO'];
        // 	$array[$no]['OP_KECAMATAN']= $row['OP_KECAMATAN'];
        // 	$array[$no]['OP_KELURAHAN']= $row['OP_KELURAHAN'];
        // 	$array[$no]['SPPT_PBB_HARUS_DIBAYAR']= $row['SPPT_PBB_HARUS_DIBAYAR'];
        // 	$array[$no]['PBB_DENDA']= $this->getDenda($row['SPPT_TANGGAL_JATUH_TEMPO'],$row['SPPT_PBB_HARUS_DIBAYAR']);
        // 	$array[$no]['PBB_TOTAL_BAYAR']= 0;
        // 	$array[$no]['DENDA_PLUS_PBB']= $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['PBB_DENDA'];
        // 	$no++;
        // }
        // return $array;
    }

    function getDataJson($sql)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $res = mysqli_query($LDBLink, $sql);
        $stt = $_REQUEST['status'];
        // echo $sql;
        // exit;
        if ($res === false) {
            echo "false1 " . mysqli_error($LDBLink);
        } else {
            $json = array();
            $index = 0;
            while ($row = mysqli_fetch_assoc($res)) {
                // print_r($row);exit;
                // $row["nomor"]=$index+1;
                // $json[$index]= $row ;
                
                $denda = $this->getDenda($row['SPPT_TANGGAL_JATUH_TEMPO'], $row['SPPT_PBB_HARUS_DIBAYAR']);
                $pengurangan = isset($row['NILAI_PENGURANGAN']) ? $row['NILAI_PENGURANGAN'] : 0;
                $denda = $denda - $pengurangan;

                $status_bayar = $row['PAYMENT_FLAG'];
                $sb = " ";
                $isLunas = false;
                if ($status_bayar === NULL || $status_bayar == "0") {
                    $sb = "Belum Bayar";
                } else if ($status_bayar == "1") {
                    $sb = "Sudah Bayar ";
                    $denda = $row['PBB_TOTAL_BAYAR'] - $row['SPPT_PBB_HARUS_DIBAYAR'];
                    $isLunas = true;
                }

                $total = $denda + $row['SPPT_PBB_HARUS_DIBAYAR'];
                $tgl_tempo = $row['SPPT_TANGGAL_JATUH_TEMPO'];
                $row['SPPT_TANGGAL_JATUH_TEMPO'] = substr($tgl_tempo,8,2) . '-' . substr($tgl_tempo,5,2). '-' . substr($tgl_tempo,0,4);
                $tgl_bayar = $row['PAYMENT_PAID'];
                $row['PAYMENT_PAID'] = substr($tgl_bayar,8,2) . '-' . substr($tgl_bayar,5,2). '-' . substr($tgl_bayar,0,4). ' ' . substr($tgl_bayar,11);

                $array = array(
                    // "<a style='text-align:center' ><i data-nop='$row[NOP]' data-tahun='$row[SPPT_TAHUN_PAJAK]' class='btn-delete-temp fa-times fa '></i></a>",
                    ($stt==0) ? "<label for='td-ch' style='text-align:center;width:100%;height:100%' data-is-lunas='". ($isLunas ? 1 : 0) ."'>
                        <input class='nop-member btn-delete-temp' year='$row[SPPT_TAHUN_PAJAK]' value='$row[NOP]' type='checkbox'>
                    </label>" : "",
                    ($stt!=0) ? '<label data-is-lunas="'. ($isLunas ? 1 : 0) .'">'.$row['NOP']."</label>" : $row['NOP'],
                    $row['SPPT_TAHUN_PAJAK'],
                    $row['SPPT_TANGGAL_JATUH_TEMPO'],
                    $row['WP_NAMA'],
                    $row['OP_KECAMATAN'],
                    $row['OP_KELURAHAN'],
                    number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,',','.'),
                    number_format($denda,0,',','.'),
                    number_format($total,0,',','.')
                );

                if($stt==1 || $stt==2) array_push($array, $sb, $row['PAYMENT_PAID']);

                array_push($json, $array);

                $index++;
            }
            // echo "<pre>";
            // echo json_encode($json);
            // echo "</pre>";
            // exit;
            return $json;
        }
    }
    public function getMemberFailed($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $draw                   = $_REQUEST["draw"]; //counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
        $orderByColumnIndex     = $_REQUEST['order'][0]['column']; // index of the sorting column (0 index based - i.e. 0 is the first record)
        if ($_REQUEST['columns'][$orderByColumnIndex]['data'] == "0") {
            $orderBy = "NAMA_GROUP";
        } else {
            $orderBy                = $_REQUEST['columns'][$orderByColumnIndex]['data']; //Get name of the sorting column from its index
        }
        $orderType              = $_REQUEST['order'][0]['dir']; // ASC or DESC
        if (isset($_REQUEST['start'])) {
            $start                  = $_REQUEST["start"]; //Paging first record indicator.
        } else {
            $start                  = 0;
        }

        if (isset($_REQUEST['length']) && $_REQUEST['length'] > 0) {
            $length                 = $_REQUEST['length']; //Number of records that the table can display in the current draw
            $limitQuery = "limit {$start}, {$length}";
        } else {
            $length = 0;
            $limitQuery = "";
        }
        /* END of POST variables */
        $query = "
	    		
		    		SELECT
					G.CPM_CG_NAME NAMA_GROUP,
					P.*
					FROM
					cppmod_cg_temp_member_failed T 
					INNER JOIN 
					pbb_sppt P ON T.CPM_CGTM_NOP = P.NOP 
					INNER JOIN
					cppmod_collective_group G ON G.CPM_CG_ID = T.CPM_CGTM_ID
					WHERE T.CPM_CGTM_ID = '$id' and P.SPPT_TAHUN_PAJAK = T.CPM_CGTM_TAX_YEAR
					group by P.NOP, P.SPPT_TAHUN_PAJAK
				

				";
        $recordsTotal = count($this->getDataJson($query));
        // var_dump($recordsTotal);
        // exit;
        // var_dump($recordsTotal);
        if (!empty($_REQUEST['search']['value'])) {

            /* WHERE Clause for searching */

            // exit;.
            for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
                // echo  $_REQUEST['columns'][$i]['searchable'];
                // echo "<br>";
                if ($_REQUEST['columns'][$i]['searchable'] == "true") {

                    $column     =   $_REQUEST['columns'][$i]['name']; //we get the name of each column using its index from POST request
                    $where[]    =   "$column like '%" . $_REQUEST['search']['value'] . "%'";
                } else {
                    // echo "masuk";
                }
            }
            $where = "WHERE " . implode(" OR ", $where); // id like '%searchValue%' or name like '%searchValue%' ....
            /* End WHERE */

            // $sql = sprintf("SELECT * FROM %s %s %s", $query , $where, " AND CREATED_BY='".$uid."'");//Search query without limit clause (No pagination)
            $sql = "SELECT * FROM ($query) as d $where  {$limitQuery}  ";



            $recordsFiltered = count($this->getDataJson($sql)); //Count of search result
            $data = $this->getDataJson($sql);
        } else {
            $sql = "SELECT * FROM ($query) as d  ORDER BY $orderBy $orderType {$limitQuery} ";
            $data = $this->getDataJson($sql);
            $recordsFiltered = $recordsTotal;
            // var_dump($recordsFiltered);
        }


        $response = array(
            "draw"             => intval($draw),
            "recordsTotal"     => $recordsTotal,
            "recordsFiltered"  => $recordsFiltered,
            "data"             => $data
        );

        // echo "123";
        // exit;

        echo json_encode($response);
        exit;
    }
    public function getCountMemberKelByIDArray($kel, $tahun, $buku)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        $arrWhere = array();
        if ($buku != 0) {
            switch ($buku) {
                case 1:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
                    break;
                case 12:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
                    break;
                case 123:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 1234:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 12345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 2:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
                    break;
                case 23:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 234:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 2345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 3:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 34:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 4:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 45:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 5:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
            }
        }
        $where = implode(" AND ", $arrWhere);


        $query = "SELECT * FROM pbb_sppt WHERE OP_KELURAHAN_KODE = '$kel' and SPPT_TAHUN_PAJAK = '$tahun' and
			 $where

				";
        // echo $query;
        // exit;
        $my = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        return mysqli_num_rows($my);
    }
    public function getCountMemberTempByIDArray($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));

        $query = "SELECT
				P.*
				FROM
				(	
					SELECT M.CPM_CGTM_ID AS ID,M.CPM_CGTM_NOP NOP,M.CPM_CGTM_TAX_YEAR YEAR 
					FROM
					cppmod_cg_temp_member M
					INNER JOIN cppmod_collective_group G 
					ON G.CPM_CG_ID = M.CPM_CGTM_ID
				) AS T
				INNER JOIN pbb_sppt P ON T.NOP = P.NOP AND P.SPPT_TAHUN_PAJAK = T.YEAR 
				WHERE
				T.ID = '$id' 
				GROUP BY
				P.NOP,P.SPPT_TAHUN_PAJAK
				";
        $my = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        return mysqli_num_rows($my);
    }
    public function getCountMemberFailedByIDArray($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "SELECT * FROM cppmod_cg_temp_member_failed WHERE CPM_CGTM_ID = '$id'
				";

        $my = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        return mysqli_num_rows($my);
    }

    public function getMemberByIDArray($id, $returnQuery = false, $limit = 0, $offset = 0)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));

        $query = "SELECT CPM_CG_STATUS FROM cppmod_collective_group WHERE CPM_CG_ID = '$id'";

        $result = mysqli_query($LDBLink, $query);
        $row = mysqli_fetch_assoc($result);
        $cg_status = $row['CPM_CG_STATUS'];
        $add_cg_temp_member = ($cg_status=='0' || $cg_status=='1') ? "UNION ALL SELECT CPM_CGTM_ID AS ID,CPM_CGTM_NOP NOP,CPM_CGTM_TAX_YEAR YEAR FROM cppmod_cg_temp_member":"";
        // print_r($cg_status);exit;

        $limitQuery = '';
        if ($limit) {
            $limitQuery = "limit $offset, $limit";
        }

        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C 
                                ON C.NOP = P.NOP AND C.TAHUN = P.SPPT_TAHUN_PAJAK 
                            LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";
        $selectPengurangan = "IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN";

        $query = "SELECT
				P.*,
				CG.CPM_CG_NAME AS NAMA_GROUP,
                {$selectPengurangan}
				FROM
				(	
					SELECT CPM_CGM_ID AS ID,CPM_CGM_NOP NOP,CPM_CGM_TAX_YEAR YEAR FROM cppmod_cg_member 
					$add_cg_temp_member 
				) AS T
				LEFT JOIN pbb_sppt P ON T.NOP = P.NOP AND P.SPPT_TAHUN_PAJAK = T.YEAR
				LEFT JOIN cppmod_collective_group CG ON CG.CPM_CG_ID = T.ID
                {$joinPengurangan}
				WHERE
				T.ID = '$id' 
				GROUP BY
				P.NOP {$limitQuery}
				";

        $array = array();
        $result = mysqli_query($LDBLink, $query);
        if ($returnQuery) {
            return $result;
        }
        $no = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $getDenda = $this->getDenda($row['SPPT_TANGGAL_JATUH_TEMPO'], $row['SPPT_PBB_HARUS_DIBAYAR']);
            $pengurangan = isset($row['NILAI_PENGURANGAN']) ? $row['NILAI_PENGURANGAN'] : 0;
            $getDenda = $getDenda - $pengurangan;

            $array[$no]['NAMA_GROUP'] = $row['NAMA_GROUP'];
            $array[$no]['NOP'] = $row['NOP'];
            $array[$no]['WP_NAMA'] = $row['WP_NAMA'];
            $array[$no]['SPPT_TAHUN_PAJAK'] = $row['SPPT_TAHUN_PAJAK'];
            $array[$no]['SPPT_TANGGAL_JATUH_TEMPO'] = $row['SPPT_TANGGAL_JATUH_TEMPO'];
            $array[$no]['OP_KECAMATAN'] = $row['OP_KECAMATAN'];
            $array[$no]['OP_KELURAHAN'] = $row['OP_KELURAHAN'];
            $array[$no]['SPPT_PBB_HARUS_DIBAYAR'] = $row['SPPT_PBB_HARUS_DIBAYAR'];
            $array[$no]['PBB_DENDA'] = $getDenda;
            $array[$no]['PBB_TOTAL_BAYAR'] = 0;
            $array[$no]['PAYMENT_FLAG'] = $row['PAYMENT_FLAG'];
            $array[$no]['DENDA_PLUS_PBB'] = $row['SPPT_PBB_HARUS_DIBAYAR'] + $getDenda;
            $no++;
        }
        return $array;
    }


    public function getMemberByID($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        /* Useful $_POST Variables coming from the plugin */
        $draw                   = $_REQUEST["draw"]; //counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
        $orderByColumnIndex     = $_REQUEST['order'][0]['column']; // index of the sorting column (0 index based - i.e. 0 is the first record)
        if ($_REQUEST['columns'][$orderByColumnIndex]['data'] == "0") {
            $orderBy = "NAMA_GROUP";
        } else {
            $orderBy                = $_REQUEST['columns'][$orderByColumnIndex]['data']; //Get name of the sorting column from its index
        }
        $orderType              = $_REQUEST['order'][0]['dir']; // ASC or DESC
        if (isset($_REQUEST['start'])) {
            $start                  = $_REQUEST["start"]; //Paging first record indicator.
        } else {
            $start                  = 0;
        }

        if (isset($_REQUEST['length']) && $_REQUEST['length'] > 0) {
            $length                 = $_REQUEST['length']; //Number of records that the table can display in the current draw
            $limitQuery = "limit {$start}, {$length}";
        } else {
            $length = 0;
            $limitQuery = "";
        }
        /* END of POST variables */
        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C 
                                ON C.NOP = P.NOP AND C.TAHUN = P.SPPT_TAHUN_PAJAK 
                            LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";
        $selectPengurangan = "IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN";
        
        $query = "SELECT * 
                  FROM  (
		    		SELECT
                        G.CPM_CG_NAME NAMA_GROUP,
                        P.*,
                        {$selectPengurangan}
					FROM cppmod_cg_member T 
					INNER JOIN pbb_sppt P ON T.CPM_CGM_NOP = P.NOP 
					INNER JOIN cppmod_collective_group G ON G.CPM_CG_ID = T.CPM_CGM_ID
					{$joinPengurangan}
                    WHERE T.CPM_CGM_ID = '$id' and P.SPPT_TAHUN_PAJAK = T.CPM_CGM_TAX_YEAR
					group by P.NOP, P.SPPT_TAHUN_PAJAK
				) AS CAMPUR";
        $recordsTotal = count($this->getDataJson($query));
        // var_dump($recordsTotal);
        if (!empty($_REQUEST['search']['value'])) {

            /* WHERE Clause for searching */

            // exit;.
            for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
                // echo  $_REQUEST['columns'][$i]['searchable'];
                // echo "<br>";
                if ($_REQUEST['columns'][$i]['searchable'] == "true") {

                    $column     =   $_REQUEST['columns'][$i]['name']; //we get the name of each column using its index from POST request
                    $where[]    =   "$column like '%" . $_REQUEST['search']['value'] . "%'";
                } else {
                    // echo "masuk";
                }
            }
            $where = "WHERE " . implode(" OR ", $where); // id like '%searchValue%' or name like '%searchValue%' ....
            /* End WHERE */

            // $sql = sprintf("SELECT * FROM %s %s %s", $query , $where, " AND CREATED_BY='".$uid."'");//Search query without limit clause (No pagination)
            $sql = "SELECT * FROM ($query) as d $where  {$limitQuery}  ";


            // echo 1 ;exit;
            $recordsFiltered = count($this->getDataJson($sql)); //Count of search result
            $data = $this->getDataJson($sql);
        } else {
            // echo 2 ;exit;
            $sql = "SELECT * FROM ($query) as d  ORDER BY $orderBy $orderType {$limitQuery} ";
            $data = $this->getDataJson($sql);

            $recordsFiltered = $recordsTotal;
        }
        // echo $sql;

        // added by d3Di - set CPM_CG_STATUS Lunas jika PAYMENT_FLAG lunas semua
        // ======================================================= 
        $query = "SELECT
                    c.PAYMENT_FLAG AS FLAG, b.CPM_CG_STATUS AS CG_STATUS, b.CPM_CG_PAYMENT_CODE AS PAYMENT_CODE
                FROM `cppmod_cg_member` a
                INNER JOIN cppmod_collective_group b ON a.CPM_CGM_ID = b.CPM_CG_ID 
                INNER JOIN pbb_sppt c ON a.CPM_CGM_NOP = c.NOP AND a.CPM_CGM_TAX_YEAR = c.SPPT_TAHUN_PAJAK
                WHERE a.CPM_CGM_ID = '$id'";
        $cg_status = 0;
        $belum = 0;
        $rows = [];
        $result = mysqli_query($LDBLink, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
            $cg_status = $row['CG_STATUS'];
            if($row['FLAG']=='0' || $row['FLAG']=='' || $row['FLAG']==null) {
                $belum++;
            }
            if($row['PAYMENT_CODE']=='' || $row['PAYMENT_CODE']==null) $belum = 1000;
        }
        if($cg_status=='1' && $belum==0){
            $query = "UPDATE cppmod_collective_group SET CPM_CG_STATUS = 2 WHERE CPM_CG_ID = '$id'";
            mysqli_query($LDBLink, $query);
        }
        // =======================================================

        $response = array(
            "draw"              => intval($draw),
            "recordsTotal"      => $recordsTotal,
            "recordsFiltered"   => $recordsFiltered,
            "data"              => $data
            // "cg_status"       => $cg_status,
            // "belum"         => $belum,
            // "row"         => $rows
        );

        echo json_encode($response);


        exit;

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));



        // echo $query;

        $array = array();
        $result = mysqli_query($LDBLink, $query);
        $no = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $getDenda = $this->getDenda($row['SPPT_TANGGAL_JATUH_TEMPO'], $row['SPPT_PBB_HARUS_DIBAYAR']);

            $array[$no]['NAMA_GROUP'] = $row['NAMA_GROUP'];
            $array[$no]['NOP'] = $row['NOP'];
            $array[$no]['WP_NAMA'] = $row['WP_NAMA'];
            $array[$no]['SPPT_TAHUN_PAJAK'] = $row['SPPT_TAHUN_PAJAK'];
            $array[$no]['SPPT_TANGGAL_JATUH_TEMPO'] = $row['SPPT_TANGGAL_JATUH_TEMPO'];
            $array[$no]['OP_KECAMATAN'] = $row['OP_KECAMATAN'];
            $array[$no]['OP_KELURAHAN'] = $row['OP_KELURAHAN'];
            $array[$no]['SPPT_PBB_HARUS_DIBAYAR'] = number_format($row['SPPT_PBB_HARUS_DIBAYAR']);
            $array[$no]['PBB_DENDA'] = number_format($getDenda);
            $array[$no]['PBB_TOTAL_BAYAR'] = 0;
            $array[$no]['DENDA_PLUS_PBB'] = $row['SPPT_PBB_HARUS_DIBAYAR'] + $getDenda;
            $no++;
        }
        return $array;
    }
    // public function getMemberByID($id) {
    // 	$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
    //                mysql_select_db($this->C_DB,$LDBLink);

    // 	$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	

    // 	$query = "SELECT
    // 			P.*
    // 			FROM
    // 			cppmod_cg_member T
    // 			INNER JOIN pbb_sppt P ON T.CPM_CGM_NOP = P.NOP AND P.SPPT_TAHUN_PAJAK = T.CPM_CGM_TAX_YEAR 
    // 			WHERE
    // 			T.CPM_CGM_ID = '$id' 
    // 			GROUP BY
    // 			P.NOP,P.SPPT_TAHUN_PAJAK
    // 			";

    // 	$array = array();	
    // 	$result = mysqli_query($LDBLink, $query);
    // 	$no = 0;
    //       	while ($row = mysqli_fetch_assoc($result)){
    // 		$array[$no]['NAMA_GROUP']= $row['NAMA_GROUP'];
    // 		$array[$no]['NOP']= $row['NOP'];
    // 		$array[$no]['WP_NAMA']= $row['WP_NAMA'];
    // 		$array[$no]['SPPT_TAHUN_PAJAK']= $row['SPPT_TAHUN_PAJAK'];
    // 		$array[$no]['SPPT_TANGGAL_JATUH_TEMPO']= $row['SPPT_TANGGAL_JATUH_TEMPO'];
    // 		$array[$no]['OP_KECAMATAN']= $row['OP_KECAMATAN'];
    // 		$array[$no]['OP_KELURAHAN']= $row['OP_KELURAHAN'];
    // 		$array[$no]['SPPT_PBB_HARUS_DIBAYAR']= $row['SPPT_PBB_HARUS_DIBAYAR'];
    // 		$array[$no]['PBB_DENDA']= 0;
    // 		$array[$no]['PBB_TOTAL_BAYAR']= 0;
    // 		$array[$no]['DENDA_PLUS_PBB']= $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['PBB_DENDA'];
    // 		$no++;
    // 	}
    // 	return $array;
    // }
    public function getMaxOPAccountCollective()
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "SELECT
            IFNULL(
                    MAX(
                        substr(CPM_CG_PAYMENT_CODE, 5, 7)
                    ),
                    0
                ) + 1 AS MAX

            FROM
                cppmod_collective_group
            where 
            substr(CPM_CG_PAYMENT_CODE, 1, 1) = '2'  ";
        // echo $query;
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        if ($result) {
            $data = mysqli_fetch_array($result);
            return $data['MAX'];
        } else {
            return false;
        }
    }
    public function generatePaymentCodeCollective($tahun)
    {
        $kode_masal = 2;
        $tahun = substr($tahun, 2, 2);
        $payment_masal = 9;
        // exit;
        $max1 = $this->getMaxOPAccountCollective();
        // echo ";
        // var_dump($max1);
        // exit;
        $max1 = str_pad($max1, 6, "0", STR_PAD_LEFT);
        $kode = $kode_masal . $tahun . $payment_masal . $max1;
        return $kode;
    }
    public function saveMember($param)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));

        $query = "INSERT INTO cppmod_cg_temp_member
        (
            CPM_CGTM_ID,
            CPM_CGTM_NOP,
            CPM_CGTM_TAX_YEAR
        ) 
        VALUES 
        (
            '$param[CPM_CGTM_ID]',
            '$param[CPM_CGTM_NOP]',
            '$param[CPM_CGTM_TAX_YEAR]'
        )
         ";
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function isGroupExist($param)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        $query = "SELECT * FROM cppmod_collective_group WHERE CPM_CG_NAME = '" . $param . "' ";
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function saveGroup($param)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        // $now = date("DATE ");
        // $DAY = EXPIRED_DAY_GROUP
        // $payment_code = $this->generatePaymentCodeCollective(2018); 
        $now = date("Y-m-d H:i:s");
        $datetime = date("Y-m-d H:i:s");
        $datetime = strtotime($datetime);
        $expiredDate = strtotime("+7 day", $datetime);
        $expiredDate = date('Y-m-d H:i:s', $expiredDate);
        $q_uuid = "SELECT UPPER(UUID()) AS UUID";
        $uuid = mysqli_query($LDBLink, $q_uuid) or die(mysqli_error($LDBLink));
        $uuid = mysqli_fetch_array($uuid);
        // var_dump($uuid['UUID']);
        // exit;
        $uuid = $uuid['UUID'];
        $param2 = array();
        foreach ($param as $key => $value) {
            // $param2 = strtoupper($value);
            $param2[$key] = strtoupper($value);
        }

        $param = $param2;

        if ($this->isGroupExist($param['CPM_CG_NAME'])) {
            $array = array();
            $array['success'] = false;
            $array['message'] = "Group dengan nama $param[CPM_CG_NAME] telah ada sebelumnya, silahkan menggunakan nama lain ";
            echo json_encode($array);
            exit;
        }


        $query = "INSERT INTO cppmod_collective_group
        (
            CPM_CG_ID,
            CPM_CG_NAME,
            CPM_CG_DESC,
            CPM_CG_COLLECTOR,
            CPM_CG_HP_COLLECTOR,
            CPM_CG_AREA_CODE,
            CPM_CG_STATUS,
            CPM_CG_PAYMENT_CODE,
            CPM_CG_CREATED_DATE,
            CPM_CG_CREATED_USER
        ) 
        VALUES 
        (
            '$uuid',
            '$param[CPM_CG_NAME]',
            '$param[CPM_CG_DESC]',
            '$param[CPM_CG_COLLECTOR]',
            '$param[CPM_CG_HP_COLLECTOR]',
            '$param[CPM_CG_AREA_CODE]',
            '0',
            '$payment_code',
            '$now',
            '$param[userID]'
        )
         ";
        // echo $query;
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function setActiveGroup($id, $appConfig)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $now = date("Y-m-d H:i:s");
        $datetime = date("Y-m-d H:i:s");
        $datetime = strtotime($datetime);
        $dayAdd = $appConfig['EXPIRED_DAY_GROUP'];
        $expiredDate = strtotime("+$dayAdd day", $datetime);
        $expiredDate = date('Y-m-d H:i:s', $expiredDate);
        $q_uuid = "SELECT UPPER(UUID()) AS UUID";
        $uuid = mysqli_query($LDBLink, $q_uuid) or die(mysqli_error($LDBLink));
        $uuid = mysqli_fetch_array($uuid);
        // var_dump($uuid['UUID']);
        // exit;
        $uuid = $uuid['UUID'];
        // CPM_CG_EXPIRED_DATE =  '$expiredDate',

        $query = "UPDATE  cppmod_collective_group
        	SET 
            CPM_CG_STATUS =  '0',
            CPM_CG_EXPIRED_DATE =  ''
            WHERE 
            CPM_CG_ID = '$id' 

         ";
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));

        if ($result) {
            $pindah = $this->moveToMemberTempGroup($id);
            if ($pindah) {
                $hapus = $this->deleteMemberByGroupID($id);
                if ($hapus) {
                    return true;
                } else {
                    return false;
                }
                // return true;	
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function updateGroup($param)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        // $now = date("DATE ");
        // $payment_code = $this->generatePaymentCodeCollective(2018); 
        $now = date("Y-m-d H:i:s");
        $datetime = date("Y-m-d H:i:s");
        $datetime = strtotime($datetime);
        $expiredDate = strtotime("+7 day", $datetime);
        $expiredDate = date('Y-m-d H:i:s', $expiredDate);
        $q_uuid = "SELECT UPPER(UUID()) AS UUID";
        $uuid = mysqli_query($LDBLink, $q_uuid) or die(mysqli_error($LDBLink));
        $uuid = mysqli_fetch_array($uuid);
        // var_dump($uuid['UUID']);
        // exit;
        $uuid = $uuid['UUID'];

        $query = "UPDATE  cppmod_collective_group
        	SET 
            CPM_CG_NAME =  '" . strtoupper($param[CPM_CG_NAME]) . "',
            CPM_CG_DESC ='" . strtoupper($param[CPM_CG_DESC]) . "',
            CPM_CG_COLLECTOR = '" . strtoupper($param[CPM_CG_COLLECTOR]) . "',
            CPM_CG_HP_COLLECTOR =  '" . strtoupper($param[CPM_CG_HP_COLLECTOR]) . "',
            CPM_CG_AREA_CODE  = '" . strtoupper($param[CPM_CG_AREA_CODE]) . "',
            CPM_CG_MODIFIED_DATE  = '" . strtoupper($now) . "',
            CPM_CG_MODIFIED_USER  = '" . strtoupper($param[userID]) . "'
            WHERE 
            CPM_CG_ID = '" . strtoupper($param[CPM_CG_ID]) . "' 

         ";
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function getDataGroup($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        $query = "SELECT
		SUM(P.SPPT_PBB_HARUS_DIBAYAR) AS TOTAL_OM,
		COUNT(MTMP.CPM_CGTM_ID) AS TOTAL_M,
		G.*
		FROM
		cppmod_collective_group G
		LEFT JOIN cppmod_cg_temp_member MTMP ON G.CPM_CG_ID = MTMP.CPM_CGTM_ID
		LEFT JOIN cppmod_cg_member M ON M.CPM_CGM_ID = G.CPM_CG_ID
		LEFT JOIN pbb_sppt P ON P.NOP = MTMP.CPM_CGTM_NOP
		AND P.SPPT_TAHUN_PAJAK = MTMP.CPM_CGTM_TAX_YEAR
		WHERE G.CPM_CG_ID = '$id'
		GROUP BY
		G.CPM_CG_ID
		";

        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        if ($result) {
            $data = mysqli_fetch_array($result);
            return $data;
        } else {
            return false;
        }
    }
    public function moveToMemberGroup($id, $payment_code, $expiredDate)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $Baseselect  = " SELECT 
	    CPM_CGTM_ID,
		CPM_CGTM_NOP,
		CPM_CGTM_TAX_YEAR,
		SUM(SPPT_PBB_HARUS_DIBAYAR) TOTAL,
		0 AS DENDA,
		SUM(SPPT_PBB_HARUS_DIBAYAR) TOTAL_BAYAR";

        // $Baseselect2  = " SELECT  CPM_CGTM_NOP " ;

        $Basequery = "
		FROM 
		cppmod_cg_temp_member TMP 
		INNER JOIN pbb_sppt P 
		ON P.NOP = TMP.CPM_CGTM_NOP AND 
		P.SPPT_TAHUN_PAJAK = TMP.CPM_CGTM_TAX_YEAR
		WHERE CPM_CGTM_ID = '$id'
		GROUP BY  NOP,SPPT_TAHUN_PAJAK";

        $updateExpiredAndPaymentCode =  "UPDATE 
	    pbb_sppt A INNER JOIN cppmod_cg_temp_member B 
	    ON A.NOP = B.CPM_CGTM_NOP  and A.SPPT_TAHUN_PAJAK = B.CPM_CGTM_TAX_YEAR
	    INNER JOIN cppmod_collective_group C ON C.CPM_CG_ID = B.CPM_CGTM_ID
	    SET 
	    A.BOOKING_EXPIRED = '$expiredDate',
	    A.COLL_PAYMENT_CODE = '$payment_code'
	    WHERE C.CPM_CG_ID = '$id'
	     ";

        $result = mysqli_query($LDBLink, $updateExpiredAndPaymentCode) or die(json_encode(array("messsage" => mysqli_error($LDBLink))));
        if ($result) {
            $query = "
				INSERT INTO cppmod_cg_member 
				$Baseselect $Basequery
	         ";
            $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function moveToMemberTempGroup($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        $updateExpiredAndPaymentCode =  "UPDATE 
	    pbb_sppt A INNER JOIN cppmod_cg_member B 
	    ON A.NOP = B.CPM_CGM_NOP  and A.SPPT_TAHUN_PAJAK = B.CPM_CGM_TAX_YEAR
	    INNER JOIN cppmod_collective_group C ON C.CPM_CG_ID = B.CPM_CGM_ID
	    SET 
	    A.BOOKING_EXPIRED = NULL,
	    A.COLL_PAYMENT_CODE = NULL
	    WHERE C.CPM_CG_ID = '$id'
	     ";
        $result = mysqli_query($LDBLink, $updateExpiredAndPaymentCode) or die(mysqli_error($LDBLink));
        if ($result) {
            $query = "
				INSERT INTO cppmod_cg_temp_member
					SELECT 
						CPM_CGM_ID,
						CPM_CGM_NOP,
						CPM_CGM_TAX_YEAR
					FROM 
						cppmod_cg_member M 
					INNER JOIN pbb_sppt P 
					ON P.NOP = M.CPM_CGM_NOP AND 
					P.SPPT_TAHUN_PAJAK = M.CPM_CGM_TAX_YEAR
					WHERE CPM_CGM_ID = '$id'
					GROUP BY  P.NOP,P.SPPT_TAHUN_PAJAK
	         ";
            $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    // public function queryIfMemberExist($id) {
    // 	$sql = 
    // }
    public function moveToFailed($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $sql = "
	    	REPLACE INTO cppmod_cg_temp_member_failed 
				SELECT * FROM cppmod_cg_temp_member WHERE
				CPM_CGTM_ID = '$id'
				AND CPM_CGTM_NOP NOT IN (

				SELECT NOP FROM pbb_sppt WHERE
					(
					PAYMENT_FLAG IS NULL
					OR PAYMENT_FLAG != '1'
					)
					AND NOP = cppmod_cg_temp_member.CPM_CGTM_NOP AND SPPT_TAHUN_PAJAK = cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR
				)
				OR 
				CPM_CGTM_NOP IN (
					SELECT CPM_CGM_NOP FROM cppmod_cg_member MA WHERE 
					cppmod_cg_temp_member.CPM_CGTM_NOP =MA.CPM_CGM_NOP
					AND 
					cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR = MA.CPM_CGM_TAX_YEAR
				)
	    ";
        $q = mysqli_query($LDBLink, $sql) or die(mysqli_error($LDBLink));
        if ($q) {
            $sql2 = "
	    		DELETE FROM  cppmod_cg_temp_member WHERE
				CPM_CGTM_ID = '$id'
				AND CPM_CGTM_NOP NOT IN (

				SELECT NOP FROM pbb_sppt WHERE
				(
					PAYMENT_FLAG IS NULL
					OR PAYMENT_FLAG != '1'
					)
					AND NOP = cppmod_cg_temp_member.CPM_CGTM_NOP AND SPPT_TAHUN_PAJAK = cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR
				)

				OR 
				CPM_CGTM_NOP  IN (
					SELECT CPM_CGM_NOP FROM cppmod_cg_member MA WHERE 
					cppmod_cg_temp_member.CPM_CGTM_NOP =MA.CPM_CGM_NOP
					AND 
					cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR = MA.CPM_CGM_TAX_YEAR
				)
				";
            $q2 = mysqli_query($LDBLink, $sql2) or die(mysqli_error($LDBLink));
            return $q2;
        } else {
            return false;
        }
        // return $q;

    }
    public function finalGroup($id, $userID, $appConfig)
    {

        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $cekCount  = "SELECT * FROM cppmod_cg_temp_member A INNER JOIN cppmod_collective_group B ON A.CPM_CGTM_ID = B.CPM_CG_ID WHERE A.CPM_CGTM_ID = '$id' ";
        $queryCekCout = mysqli_query($LDBLink, $cekCount);
        $r = mysqli_num_rows($queryCekCout);
        if ($r <= 0) {
            echo json_encode(array("success" => false, "message" => " Group ini belum memiliki anggota "));
            exit;
        }




        $sql_cc = "SELECT CPM_CGTM_NOP FROM 
		 	cppmod_cg_temp_member
			INNER JOIN 
			cppmod_collective_group AS CC 
			ON CC.CPM_CG_ID = CPM_CGTM_ID
	  WHERE
			CPM_CGTM_ID = '$id'
			AND  
			(
					CPM_CGTM_NOP NOT IN 
					(
						SELECT
						NOP 
						FROM
						pbb_sppt 
						WHERE
						( PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' ) 
						AND NOP = cppmod_cg_temp_member.CPM_CGTM_NOP 
						AND SPPT_TAHUN_PAJAK = cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR 
						AND OP_KELURAHAN_KODE = CPM_CG_AREA_CODE 
					) 
			OR 

					CPM_CGTM_NOP  IN (
						SELECT CPM_CGM_NOP FROM cppmod_cg_member MA 
						WHERE 
						cppmod_cg_temp_member.CPM_CGTM_NOP = MA.CPM_CGM_NOP 
						AND 
						cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR = MA.CPM_CGM_TAX_YEAR 
						AND MA.CPM_CGM_ID = CPM_CG_ID 
					)
			)

		 ";
        // echo $sql_cc;
        // exit;
        $q = mysqli_query($LDBLink, $sql_cc);
        $r = mysqli_num_rows($q);
        if ($r > 0) { /// jika ada beberapa yang sudah bayar padahal masuk draft maka
            $this->moveToFailed($id);
        }

        $count_last =  $this->getCountMemberTempByIDArray($id);

        if ($count_last <= 0) {
            echo json_encode(array("success" => false, "message" => " Proses Final dibatalkan karena seluruh member telah membayar atau berada di Group yang telah difinalkan "));
            exit;
        }

        $now = date("Y-m-d H:i:s");
        $datetime = date("Y-m-d H:i:s");
        $datetime = strtotime($datetime);
        $dayAdd = $appConfig['EXPIRED_DAY_GROUP'];
        if ($dayAdd == "" || empty($dayAdd)) {
            $dayAdd = 0;
        }
        $expiredDate = strtotime("+$dayAdd day", $datetime);
        $expiredDate = date('Y-m-d H:i:s', $expiredDate);
        $q_uuid = "SELECT UPPER(UUID()) AS UUID";
        $uuid = mysqli_query($LDBLink, $q_uuid) or die(mysqli_error($LDBLink));
        $uuid = mysqli_fetch_array($uuid);

        // get total ammount
        $group_data = $this->getDataGroup($id);
        $TOTAL_OM = $group_data['TOTAL_OM'];
        $TOTAL_M = $group_data['TOTAL_M'];

        // get payment Code
        if ($group_data['CPM_CG_PAYMENT_CODE'] == "")
            $payment_code = $this->generatePaymentCodeCollective(date("Y"));
        else
            $payment_code = $group_data['CPM_CG_PAYMENT_CODE'];



        $uuid = $uuid['UUID'];

        $query = "UPDATE  cppmod_collective_group
        	SET 
            CPM_CG_MODIFIED_DATE  = '$now',
            CPM_CG_MODIFIED_USER  = '$userID',
            CPM_CG_ORIGINAL_AMOUNT  = '$TOTAL_OM',
            CPM_CG_PENALTY_FEE  = '$TOTAL_FE',
            CPM_CG_NOP_NUMBER  = '$TOTAL_M',
            CPM_CG_EXPIRED_DATE = '$expiredDate',
            CPM_CG_STATUS = '1',
            CPM_CG_PAYMENT_CODE = '$payment_code'
            WHERE 
            CPM_CG_ID = '$id' 
            

         ";
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(json_encode(array("messsage" => mysqli_error($LDBLink))));
        if ($result) {
            // return true;
            $move = $this->moveToMemberGroup($id, $payment_code, $expiredDate, $appConfig);
            if ($move) {
                $deleteTMP = $this->deleteMemberTEMPByGroupID($id);

                return $deleteTMP;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    // terjadi kesalahan function 

    // public function finalGroup($id,$userID,$appConfig) {

    // 	$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
    //     mysql_select_db($this->C_DB,$LDBLink);

    //     $cekCount  = "SELECT * FROM cppmod_cg_temp_member A INNER JOIN cppmod_collective_group B ON A.CPM_CGTM_ID = B.CPM_CG_ID WHERE A.CPM_CGTM_ID = '$id' ";
    //     $queryCekCout = mysqli_query($LDBLink, $cekCount);
    // 	$r = mysqli_num_rows($queryCekCout);
    // 	if ($r<=0){
    // 		echo json_encode(array("success"=>false,"message"=>" Group ini belum memiliki anggota ") );
    // 		exit;
    // 	}



    // 	$sql_cc ="SELECT CPM_CGTM_NOP FROM cppmod_cg_temp_member WHERE
    // 		CPM_CGTM_ID = '$id'
    // 		AND CPM_CGTM_NOP NOT IN (

    // 			SELECT NOP FROM pbb_sppt WHERE
    // 			(
    // 				PAYMENT_FLAG IS NULL
    // 				OR PAYMENT_FLAG != '1'

    // 			)
    // 			AND NOP = cppmod_cg_temp_member.CPM_CGTM_NOP AND SPPT_TAHUN_PAJAK = cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR
    // 		)
    // 		OR 

    // 		CPM_CGTM_NOP  IN (
    // 			SELECT CPM_CGM_NOP FROM cppmod_cg_member MA WHERE 
    // 			cppmod_cg_temp_member.CPM_CGTM_NOP =MA.CPM_CGM_NOP
    // 			AND 
    // 			cppmod_cg_temp_member.CPM_CGTM_TAX_YEAR = MA.CPM_CGM_TAX_YEAR
    // 		)

    // 	 ";
    // 	 // echo $sql_cc;
    // 	 // exit;
    //  	$q = mysqli_query($LDBLink, $sql_cc);
    // 	$r = mysqli_num_rows($q);
    // 	if ($r>0){ /// jika ada beberapa yang sudah bayar padahal masuk draft maka
    // 		$this->moveToFailed($id);
    // 	}

    // 	$count_last =  $this->getCountMemberTempByIDArray($id);

    // 	if ($count_last<=0){
    // 		echo json_encode(array("success"=>false,"message"=>" Proses Final dibatalkan karena seluruh member telah membayar atau berada di Group yang telah difinalkan ") );
    // 		exit;
    // 	}

    // 	$now = date("Y-m-d H:i:s");
    // 	$datetime = date("Y-m-d H:i:s");
    // 	$datetime = strtotime($datetime);
    // 	$dayAdd = $appConfig['EXPIRED_DAY_GROUP'];
    // 	if ($dayAdd=="" || empty($dayAdd)){
    // 		$dayAdd = 0;
    // 	}
    // 	$expiredDate = strtotime("+$dayAdd day", $datetime);
    // 	$expiredDate = date('Y-m-d H:i:s', $expiredDate);
    // 	$q_uuid = "SELECT UPPER(UUID()) AS UUID";
    // 	$uuid = mysqli_query($LDBLink, $q_uuid) or die(mysqli_error($LDBLink));
    // 	$uuid = mysqli_fetch_array($uuid);

    // 	// get total ammount
    //        $group_data = $this->getDataGroup($id);
    //        $TOTAL_OM = $group_data['TOTAL_OM'];
    //        $TOTAL_M = $group_data['TOTAL_M'];

    //        // get payment Code
    //        if ($group_data['CPM_CG_PAYMENT_CODE']=="")
    //         $payment_code = $this->generatePaymentCodeCollective(date("Y"));
    //     else
    //     	$payment_code = $group_data['CPM_CG_PAYMENT_CODE'];



    //         $uuid = $uuid['UUID'];

    // 	 $query = "UPDATE  cppmod_collective_group
    //        	SET 
    //            CPM_CG_MODIFIED_DATE  = '$now',
    //            CPM_CG_MODIFIED_USER  = '$userID',
    //            CPM_CG_ORIGINAL_AMOUNT  = '$TOTAL_OM',
    //            CPM_CG_PENALTY_FEE  = '$TOTAL_FE',
    //            CPM_CG_NOP_NUMBER  = '$TOTAL_M',
    //            CPM_CG_EXPIRED_DATE = '$expiredDate',
    //            CPM_CG_STATUS = '1',
    //            CPM_CG_PAYMENT_CODE = '$payment_code'
    //            WHERE 
    //            CPM_CG_ID = '$id' 


    //         ";
    // 	$array = array();
    // 	$result = mysqli_query($LDBLink, $query) or die(json_encode(array("messsage"=>mysqli_error($LDBLink))));
    // 	if ($result){	
    // 		// return true;
    // 		$move = $this->moveToMemberGroup($id,$payment_code,$expiredDate);
    // 		if ($move){
    // 			$deleteTMP = $this->deleteMemberTEMPByGroupID($id);

    // 			return $deleteTMP;

    // 		}else{
    // 			return false;		
    // 		}
    // 	}else{
    // 		return false;
    // 	}
    // }
    public function deleteAllMemberSelected($data)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        foreach ($data as $key => $value) {
            $query = "DELETE FROM 
			 cppmod_cg_temp_member 
			 WHERE 
			 CPM_CGTM_NOP = '$value[nop]'
			 and 
			 CPM_CGTM_TAX_YEAR = '$value[tahun]'
	         ";
            $result = mysqli_query($LDBLink, $query);
        }
        return $result;
    }
    public function deleteMemberTEMPByGroupID($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "DELETE FROM cppmod_cg_temp_member
            WHERE 
           CPM_CGTM_ID = '$id'
         ";
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteMemberByGroupID($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "DELETE FROM cppmod_cg_member
            WHERE 
           CPM_CGM_ID = '$id'
         ";
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteMemberTEMPFailedByGroupID($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "DELETE FROM cppmod_cg_temp_member_failed
            WHERE 
           CPM_CGTM_ID = '$id'
         ";
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteMemberTEMP($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "DELETE FROM cppmod_cg_temp_member
            WHERE 
            CPM_CGTM_NOP = '$nop' AND 
            CPM_CGTM_TAX_YEAR = '$tahun'
         ";
        // echo $query;
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteGroup($group_id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $query = "DELETE FROM cppmod_collective_group
            WHERE 
            CPM_CG_ID = '$group_id'
         ";
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            $queryDetail = "DELETE FROM cppmod_cg_temp_member
            WHERE 
            CPM_CGTM_ID = '$group_id'
         ";
            $result = mysqli_query($LDBLink, $queryDetail);
            if ($result) {
                $queryDetail = "DELETE FROM cppmod_cg_member
	            WHERE 
	            CPM_CGM_ID = '$group_id'
	        	 ";
                // return mysqli_query($LDBLink, $queryDetail);
                if (mysqli_query($LDBLink, $queryDetail)) {
                    $deleteFailed = $this->deleteMemberTEMPFailedByGroupID($group_id);
                    return $deleteFailed;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function isKelurahanMatch($nop, $tahun, $kode_kelurahan)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun   = mysqli_real_escape_string($LDBLink, trim($tahun));
        $query = "
		SELECT
			*
		FROM
			pbb_sppt P
		WHERE 
		P.SPPT_TAHUN_PAJAK = '$tahun'
		AND 
		P.NOP = '$nop'
		";
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        $data = mysqli_fetch_array($result);
        if ($data['OP_KELURAHAN_KODE'] == $kode_kelurahan) {
            return true;
        } else {
            return false;
        }

        // echo "string";
        // var_dump($data['OP_KELURAHAN_KODE']);
        // var_dump($kode_kelurahan);
        // exit;



    }
    public function isMemberTempExist($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun   = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "
		SELECT
			*
		FROM
			cppmod_cg_temp_member M
		INNER JOIN cppmod_collective_group G ON M.CPM_CGTM_ID = G.CPM_CG_ID
		WHERE
			CPM_CGTM_NOP = '$nop' and  CPM_CGTM_TAX_YEAR = '$tahun' 
		";
        // echo $query;
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        $cont = mysqli_num_rows($result);
        if ($cont > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }


    public function isMemberExist($nop, $tahun, $kode_kelurahan)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun   = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "
		SELECT
			*
		FROM
			cppmod_cg_temp_member M
		INNER JOIN cppmod_collective_group G ON M.CPM_CGTM_ID = G.CPM_CG_ID
		WHERE
			CPM_CGTM_NOP = '$nop' and  CPM_CGTM_TAX_YEAR = '$tahun' 
			AND CPM_CG_STATUS = '1'
		";
        // echo $query;
        $array = array();
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        $cont = mysqli_num_rows($result);
        if ($cont > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getDenda($jatuh_tempo, $pokok)
    {
        return $this->dbUtils->getDenda($jatuh_tempo, $pokok);

        define("PBB_MAXPENALTY_MONTH", 24);
        define("PBB_ONE_MONTH", 30);
        define("PBB_PENALTY_PERCENT", 1);
        $jatuhtempo    = $jatuh_tempo;
        $dtjatuhtempo    = mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
        $dtnow        = time();
        $dayinterval    = ceil(($dtnow - $dtjatuhtempo) / (24 * 60 * 60));
        $monthinterval = ceil($dayinterval / PBB_ONE_MONTH);
        if ($monthinterval < 0) {
            $monthinterval = 0;
        } else {
            $monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
        }
        $denda = floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $pokok);
        // return $denda;
        return '0';
    }
    public function isPaid($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));

        $query = "SELECT * FROM pbb_sppt where NOP = '$nop' and SPPT_TAHUN_PAJAK ='$tahun'";
        // echo $query;

        $array = array();
        $result = mysqli_query($LDBLink, $query);
        if ($result) {
            $data = mysqli_fetch_array($result);

            // aldes
            $data['PBB_DENDA'] = $this->getDenda($data['SPPT_TANGGAL_JATUH_TEMPO'], $data['SPPT_PBB_HARUS_DIBAYAR']);
            $data['PBB_TOTAL_BAYAR'] = $data['PBB_DENDA'] + $data['SPPT_PBB_HARUS_DIBAYAR'];
            return $data;
        } else {
            return false;
        }
    }



    public function copyToGroup($kelurahan_kode, $group_id, $tahun, $buku)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB, $this->C_PORT) or die("Error when connecting to {$this->C_HOST_PORT} . {$this->C_USER} . {$this->C_PWD} . {$this->C_DB} . {$this->C_PORT}");
        //mysql_select_db($this->C_DB,$LDBLink);
        $arrWhere = array();
        if ($buku != 0) {
            switch ($buku) {
                case 1:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
                    break;
                case 12:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
                    break;
                case 123:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 1234:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 12345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 2:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
                    break;
                case 23:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 234:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 2345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 3:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
                    break;
                case 34:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 345:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 4:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
                    break;
                case 45:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
                case 5:
                    array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
                    break;
            }
        }
        $where = implode(" AND ", $arrWhere);
        // print_r($arrWhere);
        // echo $where;
        // exit;

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "REPLACE INTO cppmod_cg_temp_member 
				  SELECT '$group_id', NOP, SPPT_TAHUN_PAJAK
				  FROM pbb_sppt 
				  WHERE 
				    (PAYMENT_FLAG IS NULL OR PAYMENT_FLAG!='1')

                    AND OP_KELURAHAN_KODE = '$kelurahan_kode'
                    AND SPPT_TAHUN_PAJAK = '$tahun'
                    AND $where
                    AND pbb_sppt.NOP NOT IN 
                    (
                        SELECT CPM_CGM_NOP
                        FROM cppmod_cg_member A
                        INNER JOIN  cppmod_collective_group B ON B.CPM_CG_ID = A.CPM_CGM_ID
                        WHERE
                            A.CPM_CGM_TAX_YEAR = '$tahun'
                            AND B.CPM_CG_AREA_CODE = '$kelurahan_kode'
                            AND (B.CPM_CG_STATUS = 2 OR B.CPM_CG_STATUS = 1) 
                    )";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query) or die("Error when executing: {$query}");
        return $result;
    }


    public function updateToExpiredByGroup($id)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        $query = "UPDATE cppmod_collective_group SET CPM_CG_STATUS = '99' WHERE  CPM_CG_ID ='$id' and CPM_CG_STATUS='1' ";
        $result = mysqli_query($LDBLink, $query) or die(mysqli_error($LDBLink));
        return $result;
    }
    public function getCollectiveGroup($userID, $isRm1 = false)
    {
        // $map_order_column = array(
        //     'JML_ANGGOTA' => 'COUNT(M.ID)',
        //     'NAMA_KECAMATAN' => 'KC.CPC_TKC_KECAMATAN',
        //     'NAMA_KELURAHAN' => 'K.CPC_TKL_KELURAHAN',
        //     'NAMA_USER' => 'USERPBB.nm_lengkap',
        // );
        
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) || die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);
        
        $draw                   = $_REQUEST["draw"]; //counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
        $orderByColumnIndex     = $_REQUEST['order'][0]['column']; // index of the sorting column (0 index based - i.e. 0 is the first record)
        if ($_REQUEST['columns'][$orderByColumnIndex]['data'] == "0") {
            $orderBy = "CPM_CG_ID";
        } else {
            $orderBy                = $_REQUEST['columns'][$orderByColumnIndex]['name']; //Get name of the sorting column from its index
        }

        // if (isset($map_order_column[$orderBy])) {
        //     $orderBy = $map_order_column[$orderBy];
        // }

        $orderType              = $_REQUEST['order'][0]['dir']; // ASC or DESC
        if (isset($_REQUEST['start'])) {
            $start                  = $_REQUEST["start"]; //Paging first record indicator.
        } else {
            $start                  = 0;
        }

        if (isset($_REQUEST['length']) && $_REQUEST['length'] > 0) {
            $length                 = $_REQUEST['length']; //Number of records that the table can display in the current draw
            $limitQuery = "limit {$start}, {$length}";
        } else {
            $length = 0;
            $limitQuery = "";
        }
        /* END of POST variables */

        $query = "SELECT
            K.CPC_TKL_ID AS KODE_KELURAHAN,
            KC.CPC_TKC_KECAMATAN AS NAMA_KECAMATAN,
            K.CPC_TKL_KELURAHAN AS NAMA_KELURAHAN,
            -- CONCAT('X1') AS JML_ANGGOTA,
            USERPBB.nm_lengkap AS NAMA_USER,
		    G.*
		FROM
		    gw_pbb.cppmod_collective_group G
		/* LEFT JOIN 
		(	
			SELECT CPM_CGTM_ID AS ID,CPM_CGTM_NOP,CPM_CGTM_TAX_YEAR FROM cppmod_cg_temp_member 
			UNION ALL 
			SELECT CPM_CGM_ID AS ID,CPM_CGM_NOP,CPM_CGM_TAX_YEAR FROM cppmod_cg_member 
		) AS M ON M.ID = G.CPM_CG_ID */
		LEFT JOIN cppmod_tax_kelurahan K ON K.CPC_TKL_ID = G.CPM_CG_AREA_CODE
		LEFT JOIN cppmod_tax_kecamatan KC ON K.CPC_TKL_KCID = KC.CPC_TKC_ID
        LEFT JOIN sw_pbb.tbl_reg_user_pbb USERPBB ON G.CPM_CG_CREATED_USER = USERPBB.ctr_u_id
        WHERE 1=1";

        $queryCopy = $query; // dibuat copy untuk dapetin recordsTotal tanpa filtering
        $query .= " AND " . $this->global_search();

        if (!$isRm1) {
            $query .= " AND G.CPM_CG_CREATED_USER = '$userID'";
            $queryCopy .= " AND G.CPM_CG_CREATED_USER = '$userID'";
        }

        $query .= " GROUP BY G.CPM_CG_ID";
        $queryCopy .= " GROUP BY G.CPM_CG_ID";//print_r($queryCopy);exit();
        $recordsTotal = count($this->getDataJson($queryCopy));

        if (!empty($_REQUEST['search']['value'])) {

            /* WHERE Clause for searching */

            for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
                // echo  $_REQUEST['columns'][$i]['searchable'];
                // echo "<br>";
                if ($_REQUEST['columns'][$i]['searchable'] == "true") {

                    $column     =   $_REQUEST['columns'][$i]['name']; //we get the name of each column using its index from POST request
                    $where[]    =   "$column like '%" . $_REQUEST['search']['value'] . "%'";
                } else {
                    // echo "masuk";
                }
            }
            $where = implode(" OR ", $where); // id like '%searchValue%' or name like '%searchValue%' ....
            /* End WHERE */

            // $sql = sprintf("SELECT * FROM %s %s %s", $query , $where, " AND CREATED_BY='".$uid."'");//Search query without limit clause (No pagination)
            $sql = "SELECT * FROM ($query) as d WHERE $where ORDER BY $orderBy $orderType ";
            // print_r('-');
            // print_r("{$sql} {$limitQuery}");exit;
            $data = $this->getCollectiveGroupJSON("{$sql} {$limitQuery} ");
            // $recordsFiltered = count($data); //Count of search result
        } else {
            $sql = "SELECT * FROM ($query) as d ORDER BY $orderBy $orderType ";
            // print_r('=');
            // print_r("{$sql} {$limitQuery}");exit;
            $data = $this->getCollectiveGroupJSON("{$sql} {$limitQuery} ");
            // $recordsFiltered = $recordsTotal;
        }
        
        $recordsFiltered = $this->getRecordsFiltered($sql);


        $response = array(
            "draw"             => intval($draw),
            "recordsTotal"     => $recordsTotal,
            "recordsFiltered"  => $recordsFiltered,
            "data"             => $data
        );

        // echo "123";
        // exit;

        echo json_encode($response);
        exit;
    }

    public function getRecordsFiltered($subquery)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));

        $haystack = $subquery;
        $needle = '*';
        $replace = 'COUNT(*) as total';

		// https://stackoverflow.com/a/1252710/9701449
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            $newstring = substr_replace($haystack, $replace, $pos, strlen($needle));
        }

        $query = $newstring;
        $result = mysqli_query($LDBLink, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }


    public function getCollectiveGroupJSON($query)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $array = array();
        $json = array();
        $result = mysqli_query($LDBLink, $query);
        $no = 0;

        $i = (isset($_POST['start'])) ? $_POST['start'] + 1 : 1;

        while ($row = mysqli_fetch_assoc($result)) {
            //       var aksi_reaktiv = " ";
            $aksi_hapus = " ";
            $aksi_pengantar = " ";
            $aksi_failed = " ";
            $aksi_edit = " ";
            $aksi_return = " ";
            // alert($row[is_expired]);
            if (($row['CPM_CG_STATUS'] == "1" || $row['CPM_CG_STATUS'] == "99") && (date('Y-m-d') >= $row['CPM_CG_EXPIRED_DATE'])) {
                $aksi_reaktiv =   " <a class='btn btn-sm btn-primary btn-reaktivasi' group-id='" . $row['CPM_CG_ID'] . "' title='Reaktivasi Group Final'><i class='fa btn-aksi fa-refresh'></i></a> &nbsp;";
            } else {
                $aksi_reaktiv = "";
            }

            if ($row['CPM_CG_STATUS'] == "1") {
                $aksi_return =   " <a class='btn btn-sm btn-warning btn-return' group-id='" . $row['CPM_CG_ID'] . "' title='Kembalikan ke Draft'><i class='fa btn-aksi fa-history'></i></a> &nbsp;";
            } else {
                $aksi_return = "";
            }


            if ($row['CPM_CG_STATUS'] == "1" || $row['CPM_CG_STATUS'] == "2") {
                $aksi_pengantar = "<a class='btn btn-sm btn-info btn-cetak-info-group' group-id='" . $row['CPM_CG_ID'] . "' title='Cetak Surat Pengantar'><i class='fa btn-aksi fa-book'></i></a> &nbsp";
            } else {
                $aksi_pengantar = " ";
            }

            if ($row['CPM_CG_STATUS'] == "1" || $row['CPM_CG_STATUS'] == "0") {
                $JML_FAILED = $this->getCountMemberFailedByIDArray($row['CPM_CG_ID']);
                if ($JML_FAILED > 0) {
                    $aksi_failed = " <a class='btn btn-sm btn-alt btn-view-failed' group-id='" . $row['CPM_CG_ID'] . "' style='color:red' title='Daftar NOP yang Gagal'><i class='fa btn-aksi fa-user-times'></i></a> &nbsp";
                }
            }

            if ($row['CPM_CG_STATUS'] == "0" || $row['CPM_CG_STATUS'] == "99") {
                $aksi_hapus =  "<a class='btn btn-sm btn-danger btn-delete-group' group_id='" . $row['CPM_CG_ID'] . "' title='Hapus Group'><i class='fa btn-aksi fa-times'></i></a> &nbsp;";

                $aksi_edit =  "<a class='btn btn-sm btn-primary btn-edit-group' group-id='" . $row['CPM_CG_ID'] . "' title='Ubah Data Group'><i class='fa fa-edit'></i></a> &nbsp;";
            }


            //  // if ($row[CPM_CG_STATUS]!="2" && $row[CPM_CG_STATUS]!="1"){ 
            //            // }




            $aksi = "<p>" . $aksi_return . " " . $aksi_edit .
                "<a class='btn btn-sm btn-success btn-add-nop' title='Kelola Member Group' group-id='" . $row['CPM_CG_ID'] . "'  status='" . $row['CPM_CG_STATUS'] . "' ><i class='fa btn-aksi fa-user'></i></a> &nbsp;" . $aksi_hapus . $aksi_pengantar . "</p>";


            // alert(v.CPM_CG_NAME);
            $tgl_bayar = " ";
            // alert($row[CPM_CG_PAY_DATE]);
            //  vType = typeof $row[CPM_CG_PAY_DATE];
            // alert(vType);
            if ($row['CPM_CG_PAY_DATE'] == "") {
                $tgl_bayar  = "  ";
            } else if ($row['CPM_CG_PAY_DATE'] === null) {
                $tgl_bayar  = " ";
            } else {

                $tgl_bayar  = " " . $row['CPM_CG_PAY_DATE'];
            }


            $status = "";

            if ($row['CPM_CG_STATUS'] == "1" && (date('Y-m-d') >= $row['CPM_CG_EXPIRED_DATE'])) {
                $update = $this->updateToExpiredByGroup($row['CPM_CG_ID']);
                // if ($update)
                $is_expired = true;
                // $array[$no]['is_expired']= true;
                // $array[$no]['is_expired']= TRUE;
                // else
                // 	$array[$no]['is_expired']= false;
            } else {
                $is_expired = false;
            }

            if ($is_expired) {
                $status = "<b style='color:red'>Expired</b>";
            } else {

                if ($row['CPM_CG_STATUS'] == "0") {
                    $status = "Draft";
                } else if ($row['CPM_CG_STATUS'] == "1") {
                    $status = "<b style='color:#ff6b00'>Final - Siap Dibayar</b>";
                } else if ($row['CPM_CG_STATUS'] == "2") {
                    $status = "<b style='color:green'>Sudah Di Bayar <i class='fa fa-check'></i> " . $tgl_bayar . "</b>";
                } else if ($row['CPM_CG_STATUS'] == "99") {
                    $status = "<b style='color:red'>Expired</b>";
                    // status = "Expired";
                }
            }

            $kode_bayar = "";
            if ($row['CPM_CG_PAYMENT_CODE'] == "") {
                $kode_bayar = "<b style='color:red'>Belum Tersedia</b>";
            } else {
                $kode_bayar = $row['CPM_CG_PAYMENT_CODE'];
            }


            if ($row['CPM_CG_EXPIRED_DATE'] == "" || $row['CPM_CG_STATUS'] == "2") {
                $date = "-";
            } else {
                $date = date("d M Y H:i", strtotime($row['CPM_CG_EXPIRED_DATE']));
            }

            $CREATED_DATE = $row['CPM_CG_CREATED_DATE'];
            $row['CPM_CG_CREATED_DATE'] = substr($CREATED_DATE,8,2) . '-' . substr($CREATED_DATE,5,2). '-' . substr($CREATED_DATE,0,4). ' ' . substr($CREATED_DATE,11);



            // added by d3Di - Ubah status Group Yg Nop anggota nya sudah bayar semua
            //===========================================================================
            $id_cg = $row['CPM_CG_ID'];
            if ($row['CPM_CG_STATUS'] == "1") {
                $qry = "SELECT  c.PAYMENT_FLAG AS FLAG
                        FROM `cppmod_cg_member` a
                        INNER JOIN pbb_sppt c ON a.CPM_CGM_NOP = c.NOP AND a.CPM_CGM_TAX_YEAR = c.SPPT_TAHUN_PAJAK
                        WHERE a.CPM_CGM_ID = '$id_cg'";
                $belum = 0;
                $ddd = mysqli_query($LDBLink, $qry);
                while ($r = mysqli_fetch_assoc($ddd)) {
                    $cg_status = $row['CG_STATUS'];
                    if($r['FLAG']=='0' || $r['FLAG']=='' || $r['FLAG']==null) {
                        $belum++;
                    }
                }

                if($belum==0){
                    $qry = "UPDATE cppmod_collective_group SET CPM_CG_STATUS = 2 WHERE CPM_CG_ID = '$id_cg'";
                    mysqli_query($LDBLink, $qry);
                }
            }
            //===========================================================================




            // added by d3Di - cari Jumlah Anggota
            //===========================================================================
            $idcg = $row['CPM_CG_ID'];
            $qryy = "SELECT COUNT(CPM_CGM_ID) AS JML FROM cppmod_cg_member WHERE CPM_CGM_ID = '$idcg'";
            if($row['CPM_CG_STATUS'] == "0" || $row['CPM_CG_STATUS'] == "1"){
                $qryy .= " UNION ALL SELECT COUNT(CPM_CGTM_ID) AS JML FROM cppmod_cg_temp_member WHERE CPM_CGTM_ID = '$idcg'";
            }
            $qry = "SELECT SUM(MMBR.JML) AS MEMBER FROM ($qryy) MMBR"; //print_r($qry);exit;
            $ddd = mysqli_query($LDBLink, $qry);
            $jml = mysqli_fetch_assoc($ddd);
            $jml = $jml['MEMBER'];
            //===========================================================================
            $json = array(
                $i,
                "$aksi",
                $row['CPM_CG_NAME'],
                $row['CPM_CG_COLLECTOR'],
                $row['CPM_CG_HP_COLLECTOR'],
                $jml . $aksi_failed,
                $kode_bayar,
                $status . "$aksi_reaktiv",
                $row['NAMA_KECAMATAN'],
                "<div class='nm-kel'>" . $row['NAMA_KELURAHAN'] . "</div><div class='kd-kel' style='display:none'>" . $row['KODE_KELURAHAN'] . "</div>",
                $row['CPM_CG_DESC'],
                $date,
                $row['NAMA_USER'],
                $row['CPM_CG_CREATED_DATE'],
                $xxxx
            );
            array_push($array, $json);
            $i++;



            // $array[$no]['CPM_CG_ID']= $row['CPM_CG_ID'];
            // $array[$no]['CPM_CG_NAME']= $row['CPM_CG_NAME'];
            // $array[$no]['CPM_CG_DESC']= $row['CPM_CG_DESC'];
            // $array[$no]['CPM_CG_COLLECTOR']= $row['CPM_CG_COLLECTOR'];
            // $array[$no]['CPM_CG_HP_COLLECTOR']= $row['CPM_CG_HP_COLLECTOR'];
            // $array[$no]['CPM_CG_AREA_CODE']= $row['CPM_CG_AREA_CODE'];
            // $array[$no]['CPM_CG_PAYMENT_CODE']= $row['CPM_CG_PAYMENT_CODE'];
            // $array[$no]['CPM_CG_STATUS']= $row['CPM_CG_STATUS'];
            // $array[$no]['JML_ANGGOTA']= $row['JML_ANGGOTA'];
            // $array[$no]['NAMA_KELURAHAN']= $row['NAMA_KELURAHAN'];
            // $array[$no]['CPM_CG_EXPIRED_DATE']= $row['CPM_CG_EXPIRED_DATE'];
            // $array[$no]['CPM_CG_PAY_DATE']= $row['CPM_CG_PAY_DATE'];
            // $array[$no]['JML_FAILED']= $this->getCountMemberFailedByIDArray($row['CPM_CG_ID']);

            // $no++;
        }
        return $array;
    }
    // public function getCollectiveGroup($userID) {
    // 	$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
    //                mysql_select_db($this->C_DB,$LDBLink);


    // 	$query = "SELECT
    // 	K.CPC_TKL_KELURAHAN NAMA_KELURAHAN,
    // 	COUNT(M.ID) AS JML_ANGGOTA,
    // 	#COUNT(F.CPM_CGTM_ID) AS JML_FAILED,

    // 	G.*
    // 	FROM
    // 	cppmod_collective_group G
    // 	LEFT JOIN 
    // 	(	
    // 		SELECT CPM_CGTM_ID AS ID,CPM_CGTM_NOP,CPM_CGTM_TAX_YEAR FROM cppmod_cg_temp_member 
    // 		UNION ALL 
    // 		SELECT CPM_CGM_ID AS ID,CPM_CGM_NOP,CPM_CGM_TAX_YEAR FROM cppmod_cg_member 
    // 	) AS M
    // 	ON M.ID = G.CPM_CG_ID
    // 	LEFT JOIN  cppmod_tax_kelurahan K ON K.CPC_TKL_ID = G.CPM_CG_AREA_CODE
    // 	#LEFT JOIN cppmod_cg_temp_member_failed AS F ON F.CPM_CGTM_ID = G.CPM_CG_ID


    // 	WHERE G.CPM_CG_CREATED_USER = '$userID'

    // 	GROUP BY
    // 	G.CPM_CG_ID";

    // 	// echo $query;
    // 	// exit;
    // 	$array = array();
    // 	$result = mysqli_query($LDBLink, $query);
    // 	$no = 0;
    //       	while ($row = mysqli_fetch_assoc($result)){
    // 		$array[$no]['CPM_CG_ID']= $row['CPM_CG_ID'];
    // 		$array[$no]['CPM_CG_NAME']= $row['CPM_CG_NAME'];
    // 		$array[$no]['CPM_CG_DESC']= $row['CPM_CG_DESC'];
    // 		$array[$no]['CPM_CG_COLLECTOR']= $row['CPM_CG_COLLECTOR'];
    // 		$array[$no]['CPM_CG_HP_COLLECTOR']= $row['CPM_CG_HP_COLLECTOR'];
    // 		$array[$no]['CPM_CG_AREA_CODE']= $row['CPM_CG_AREA_CODE'];
    // 		$array[$no]['CPM_CG_PAYMENT_CODE']= $row['CPM_CG_PAYMENT_CODE'];
    // 		$array[$no]['CPM_CG_STATUS']= $row['CPM_CG_STATUS'];
    // 		$array[$no]['JML_ANGGOTA']= $row['JML_ANGGOTA'];
    // 		$array[$no]['NAMA_KELURAHAN']= $row['NAMA_KELURAHAN'];
    // 		$array[$no]['CPM_CG_EXPIRED_DATE']= $row['CPM_CG_EXPIRED_DATE'];
    // 		$array[$no]['CPM_CG_PAY_DATE']= $row['CPM_CG_PAY_DATE'];
    // 		$array[$no]['JML_FAILED']= $this->getCountMemberFailedByIDArray($row['CPM_CG_ID']);
    // 		if ($row['CPM_CG_STATUS']=="99"  ) {
    // 			// $update = $this->updateToExpiredByGroup($row['CPM_CG_ID']);
    // 			// if ($update)
    // 				$array[$no]['is_expired']= true;
    // 			// else
    // 			// 	$array[$no]['is_expired']= false;
    // 		}else{
    // 			$array[$no]['is_expired']= false;
    // 		}

    // 		$no++;
    // 	}
    // 	return $array;

    //    }

    //==============ROLLBACK PENETAPAN
    public function copyToPembatalanPerKel($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "INSERT INTO pbb_sppt_PEMBATALAN 
				  SELECT * FROM pbb_sppt 
				  WHERE NOP LIKE '$nop%' 
				  AND SPPT_TAHUN_PAJAK = '$tahun' 
				  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            $queryReplace = "REPLACE INTO pbb_sppt_PEMBATALAN 
				  SELECT * FROM pbb_sppt 
				  WHERE NOP LIKE '$nop%' 
				  AND SPPT_TAHUN_PAJAK = '$tahun' 
				  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";

            $resultReplace = mysqli_query($LDBLink, $queryReplace);
            if (!$resultReplace) {
                return false;
            }
        }
        return true;
    }

    public function delGateWayPBBSPPTPerKel($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "DELETE FROM pbb_sppt WHERE NOP LIKE '$nop%' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";
        if ($tahun) {
            $query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
        }
        //print_r($query);		
        $result = mysqli_query($LDBLink, $query);
        $array = array();
        // while ($row = mysqli_fetch_assoc($result)){
        // 	$array= $row['PAYMENT_FLAG']
        // }
        // return $result;

        // if (!$result) {
        //     return false;
        // }
        // return true;
    }

    public function copySPPTCurrentToPembatalanPerKel($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "INSERT INTO cppmod_pbb_sppt_current_pembatalan 
				SELECT
					A.*
				FROM
					cppmod_pbb_sppt_current A
				LEFT JOIN " . $this->C_DB . ".pbb_sppt B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
				WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
				AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
        //echo $query; exit;
        return $this->dbSpec->sqlQuery($query);
    }

    public function replaceSPPTCurrentToPembatalanPerKel($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "REPLACE INTO cppmod_pbb_sppt_current_pembatalan 
				SELECT
					A.*
				FROM
					cppmod_pbb_sppt_current A
				LEFT JOIN " . $this->C_DB . ".pbb_sppt B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
				WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
				AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
        //echo $query; exit;
        return $this->dbSpec->sqlQuery($query);
    }

    public function deleteSPPTCurrentPerKel($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "DELETE A
					FROM
						cppmod_pbb_sppt_current A
					LEFT JOIN " . $this->C_DB . ".pbb_sppt B ON A.NOP = B.NOP
					AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK 
					WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
					AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query);
    }

    public function isCurrentExistPerKel($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT NOP FROM cppmod_pbb_sppt_current WHERE NOP LIKE '$nop%' AND SPPT_TAHUN_PAJAK = '$tahun'";

        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function updateTahunPenetapan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        // $query = "UPDATE cppmod_pbb_sppt_final SET CPM_SPPT_THN_PENETAPAN = '0' WHERE CPM_NOP LIKE '$nop%' ";
        $query = "UPDATE
					cppmod_pbb_sppt_final A
				JOIN " . $this->C_DB . ".pbb_sppt B ON A.CPM_NOP = B.NOP
				SET CPM_SPPT_THN_PENETAPAN = '0'
				WHERE
					B.SPPT_TAHUN_PAJAK = '$tahun'
				AND (
					B.PAYMENT_FLAG != '1'
					OR B.PAYMENT_FLAG IS NULL
				)
				AND CPM_NOP LIKE '$nop%' ";
        // echo $query; exit;
        $result = $this->dbSpec->sqlQuery($query);
        if (!$result) {
            // $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_SPPT_THN_PENETAPAN = '0' WHERE CPM_NOP LIKE '$nop%' ";	
            $query = "UPDATE
					cppmod_pbb_sppt_susulan A
				JOIN " . $this->C_DB . ".pbb_sppt B ON A.CPM_NOP = B.NOP
				SET CPM_SPPT_THN_PENETAPAN = '0'
				WHERE
					B.SPPT_TAHUN_PAJAK = '$tahun'
				AND (
					B.PAYMENT_FLAG != '1'
					OR B.PAYMENT_FLAG IS NULL
				)
				AND CPM_NOP LIKE '$nop%' ";
            $result = $this->dbSpec->sqlQuery($query);
        }
        return $result;
    }

    //===================================

    public function copyToPembatalan($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "REPLACE INTO pbb_sppt_PEMBATALAN SELECT * FROM pbb_sppt WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function addToLog($user, $nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $user   = mysqli_real_escape_string($LDBLink, trim($user));
        $nop       = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun     = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "INSERT INTO PBB_PEMBATALAN_SPPT_LOG VALUES (UUID(),'$user','$nop','$tahun',now()) ";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function delGateWayPBBSPPT($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "DELETE FROM pbb_sppt WHERE NOP='$nop' ";
        if ($tahun) {
            $query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
        }
        //echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function copySPPTCurrentToPembatalan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "REPLACE INTO cppmod_pbb_sppt_current_pembatalan SELECT * FROM cppmod_pbb_sppt_current WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query);
    }

    public function deleteSPPTCurrent($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";

        return $this->dbSpec->sqlQuery($query);
    }

    public function isCurrentExist($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT NOP FROM cppmod_pbb_sppt_current WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";

        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    #PENERBITAN ======================================================
    public function getGateWayPBBSPPTPembatalan($nop)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));

        $query = "SELECT
					NOP,
					WP_NAMA,
					WP_ALAMAT,
					OP_ALAMAT,
					SPPT_TAHUN_PAJAK,
					SPPT_TANGGAL_JATUH_TEMPO,
					SPPT_PBB_HARUS_DIBAYAR,
					PAYMENT_FLAG
				FROM
					pbb_sppt_PEMBATALAN WHERE NOP='$nop' ";
        // echo $query;
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        } else
            return $result;
    }

    public function copyToPBBSPPT($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "INSERT INTO pbb_sppt SELECT * FROM pbb_sppt_PEMBATALAN WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function addToLogPenerbitan($user, $nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $user   = mysqli_real_escape_string($LDBLink, trim($user));
        $nop       = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun     = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "INSERT INTO PBB_PENERBITAN_SPPT_LOG VALUES (UUID(),'$user','$nop','$tahun',now()) ";

        // echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function delGateWayPBBSPPTPembatalan($nop, $tahun)
    {
        $LDBLink = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

        $nop   = mysqli_real_escape_string($LDBLink, trim($nop));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));

        $query = "DELETE FROM pbb_sppt_PEMBATALAN WHERE NOP='$nop' ";
        if ($tahun) {
            $query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
        }
        //echo $query;exit;		
        $result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function copyPembatalanToSPPTCurrent($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "INSERT INTO cppmod_pbb_sppt_current SELECT * FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
        // echo $query; exit;
        return $this->dbSpec->sqlQuery($query);
    }

    public function deleteSPPTCurrentPembatalan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "DELETE FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";

        return $this->dbSpec->sqlQuery($query);
    }

    public function isCurrentPembatalanExist($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT NOP FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
        // echo $query;
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    #===============================================

    public function getDataFinal($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_fetch_assoc($res);
            return $nRes;
        }
    }

    public function isFinalExist($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isFinalExtExist($id)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $queryExt = "SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID = " . $id . " ";
        if ($this->dbSpec->sqlQuery($queryExt, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isSusulanExist($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isSusulanExtExist($nop)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $queryExt = "SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID = " . $id . " ";
        if ($this->dbSpec->sqlQuery($queryExt, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isPBBSPPTExist($nop)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "SELECT CPM_NOP FROM cppmod_pbb_sppt WHERE CPM_NOP = '$nop' ";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function isPBBSPPTExtExist($nop)
    {
        $id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));

        $queryExt = "SELECT * FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID = " . $id . " ";
        if ($this->dbSpec->sqlQuery($queryExt, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
    }

    public function updateJenisTanah($nop, $tahun, $table, $ot)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $table = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($table));

        $query = "UPDATE " . $table . " SET CPM_OT_JENIS = '" . $ot . "' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '" . $tahun . "'";
        // echo $query; 
        return $this->dbSpec->sqlQuery($query);
    }

    public function updateJenisTanahFinal($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "UPDATE cppmod_pbb_sppt_final SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query; 
        return $this->dbSpec->sqlQuery($query);
    }

    public function updateJenisTanahSusulan($nop, $tahun)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";

        return $this->dbSpec->sqlQuery($query);
    }

    public function updateJenisTanahPBBSPPT($nop)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

        $query = "UPDATE cppmod_pbb_sppt SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop'";

        return $this->dbSpec->sqlQuery($query);
    }




    // aldes, add multiple NOP to kolektif
    public function addMultipleNopCollective($request)
    {
        $GWLink = $this->makeGwConn();
        // $multipleNopEx = array_map(function($value) use ($GWLink) { return $this->mysqliEscape($GWLink, $value); }, explode(',', $multipleNop));
        $multipleNopEx = $this->mapAndFilterMultiNop($GWLink, explode(',', trim($request['data-nop'])));
        $multipleNop = implode(',', $multipleNopEx);

        $invalidNop = array();
        $validNop = array();
        $notfoundNop = $multipleNopEx;
        $insertIntoTempMember = array();

        $tahun = $this->mysqliEscape($GWLink, $request['data-tahun-pajak']);
        $kodeKelurahan = $this->mysqliEscape($GWLink, $request['data-kelurahan']);

        $leftJoin = "left join (select b.*, a.* from cppmod_cg_temp_member a inner join cppmod_collective_group b on a.CPM_CGTM_ID = b.CPM_CG_ID) b on b.CPM_CGTM_NOP = a.NOP and b.CPM_CGTM_TAX_YEAR = a.SPPT_TAHUN_PAJAK";
        $query = "select b.CPM_CGTM_ID, b.CPM_CG_NAME ,a.* from pbb_sppt a {$leftJoin} where a.SPPT_TAHUN_PAJAK = '{$tahun}' and a.NOP in ('" . implode("','", $multipleNopEx) . "')";

        $resultGetNOP = $this->mysqliQuery($GWLink, $query);
        while ($row = mysqli_fetch_assoc($resultGetNOP)) {
            if ($row['OP_KELURAHAN_KODE'] == $kodeKelurahan && ($row['PAYMENT_FLAG'] != 1 || $row['PAYMENT_FLAG'] == null) && $row['CPM_CGTM_ID'] == null) {
                $row['PBB_DENDA'] = $this->getDenda($row['SPPT_TANGGAL_JATUH_TEMPO'], $row['SPPT_PBB_HARUS_DIBAYAR']);
                $row['PBB_TOTAL_BAYAR'] = $row['PBB_DENDA'] + $row['SPPT_PBB_HARUS_DIBAYAR'];
                $validNop[] = $row;

                $insertIntoTempMember[] = array(
                    'CPM_CGTM_ID' => $request['data-group-id'],
                    'CPM_CGTM_NOP' => $row['NOP'],
                    'CPM_CGTM_TAX_YEAR' => $row['SPPT_TAHUN_PAJAK'],
                );
            } else {
                $invalidNop[] = array(
                    'NOP' => $row['NOP'],
                    'cause' => ($row['OP_KELURAHAN_KODE'] != $kodeKelurahan ? 'NOP tersebut tidak berada pada kelurahan yang dipilih'
                        : ($row['CPM_CGTM_ID'] != null ?
                            ($row['CPM_CGTM_ID'] == $request['data-group-id'] ? 'NOP sudah ada di grup ini' : 'NOP sudah ada di grup ' . $row['CPM_CG_NAME'])
                            : 'NOP tersebut telah membayar PBB Tahun ' . $tahun))
                );
            }

            if (false !== $key = array_search($row['NOP'], $notfoundNop)) {
                // jika $row['NOP'] ada di $notfoundNop, hapus NOP tersebut dari list $notfoundNop
                unset($notfoundNop[$key]);
            }
        }

        foreach ($notfoundNop as $nfnop) {
            $invalidNop[] = array(
                'NOP' => $nfnop,
                'cause' => 'NOP tidak ada pada data tagihan ' . $tahun
            );
        }

        $successStatus = false;
        $messageStatus = 'Oops.. Gagal saat proses insert member ke group. Harap hubungi administrator.';

        $sqlInsertIntoTempMember = $this->makeMultiInsertQuery('cppmod_cg_temp_member', array('CPM_CGTM_ID', 'CPM_CGTM_NOP', 'CPM_CGTM_TAX_YEAR'), $insertIntoTempMember);
        if ($this->mysqliQuery($GWLink, $sqlInsertIntoTempMember)) {
            // jika berhasil insert
            $successStatus = true;
            $messageStatus = 'NOP sebanyak ' . mysqli_affected_rows($GWLink) . ' dari total ' . count($multipleNopEx) . ' NOP berhasil ditambahkan.';
        }

        return array(
            'multiNop' => true,
            'success' => $successStatus,
            'message' => $messageStatus,
            'valid' => $validNop,
            'invalid' => $invalidNop
        );
    }

    protected function makeGwConn()
    {
        $link = mysqli_connect($this->C_HOST_PORT, $this->C_USER, $this->C_PWD, $this->C_DB, $this->C_PORT);
        if (mysqli_connect_errno()) {
            echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
            exit();
        }
        return $link;
    }

    protected function mysqliEscape($link, $string)
    {
        return mysqli_real_escape_string($link, trim($string));
    }

    protected function mysqliQuery($link, $query, $debug = false)
    {
        $result = mysqli_query($link, $query);
        if (!$result && $debug) {
            echo 'Error description: ' . mysqli_error($link);
            echo ' when executing: ' . $query;
            exit();
        }

        return $result;
    }

    protected function mysqliFecthAll($result)
    {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    protected function makeMultiInsertQuery($table, $fields, $data)
    {
        return "insert into {$table} (" . implode(',', $fields) . ") VALUES (" . implode('), (', array_map(function ($value) {
            return '"' . implode('", "', $value) . '"';
        }, $data)) . ")";
    }

    protected function mapAndFilterMultiNop($link, $multinop)
    {
        $newNop = array();
        foreach ($multinop as $nop) {
            $nop = trim($nop);
            if (strlen($nop)) {
                $newNop[] = $this->mysqliEscape($link, $nop);
            }
        }

        return $newNop;
    }

    public function checkGroupMemberPaymentStatus()
    {
        $link = $this->makeGwConn();

        // mengambil ID Collective Group yg statusnya final tetapi NOP nya sudah dibayarkan semua
        $query = "SELECT
            a.CPM_CGM_ID
        FROM `cppmod_cg_member` a
        INNER JOIN cppmod_collective_group b ON a.CPM_CGM_ID = b.CPM_CG_ID 
        INNER JOIN pbb_sppt c ON a.CPM_CGM_NOP = c.NOP AND a.CPM_CGM_TAX_YEAR = c.SPPT_TAHUN_PAJAK
        WHERE
            b.CPM_CG_STATUS = 1 AND
            c.PAYMENT_FLAG = 1
        GROUP BY
            a.CPM_CGM_ID";

        $result = $this->mysqliQuery($link, $query, true);
        $rows = $this->mysqliFecthAll($result);

        $ids = array();
        if (!empty($rows)) {
            $ids = array_map(function ($row) {
                return $row['CPM_CGM_ID'];
            }, $rows);
        }

        if (!empty($ids)) {
            $query = "UPDATE cppmod_collective_group SET CPM_CG_STATUS = 2 WHERE CPM_CG_ID IN ('" . implode("', '", $ids) . "')";
            $result = $this->mysqliQuery($link, $query, true);
        }

        return array(
            'status' => true,
            'updated' => count($ids)
        );
    }
}
