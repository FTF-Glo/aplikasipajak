<?php
include_once("dbUtils.php");
class DbSpptPenggabungan {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function get($id="", $vers="", $filter="") {
		if (trim($id)!='') $filter['CPM_SPPT_DOC_ID'] = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		if (trim($vers)!='') $filter['CPM_SPPT_DOC_VERSION'] = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_service_merge_sppt ";
		
		if (count($filter) > 0) {
			$query .="WHERE ";
			$last_key = end(array_keys($filter));

			foreach ($filter as $key => $value) {
				if ($key == "CPM_SPPT_DOC_ID") 
					$query .= " $key = '$value' ";
				else
					$query .= " $key LIKE '%$value%' ";
				if ($key != $last_key) $query .= " AND ";
			}
			
		}
		$query .= " ORDER BY CPM_SPPT_DOC_CREATED DESC";
		
		//echo $query;
	
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
        
	public function add ($id, $vers, $aValue) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(),trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
		
		foreach ($aValue as $key => $value) {
			$aValue[$key] = mysqli_real_escape_string($this->dbSpec->getDBLink() , $value);
		}
		
		$query = "INSERT INTO cppmod_pbb_service_merge_sppt (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, ";
		$tmpVals = "'$id', '$vers', ";
		$last_key = end(array_keys($aValue));
		
		foreach ($aValue as $key => $value) {
			$query .= $key;
			$tmpVals .= "'".$value."'";
			
			if ($key != $last_key) {
				$query .= ", ";
				$tmpVals .= ", ";
			}
		}
		$query .= ") values (".$tmpVals.")";
		
		#echo $query;exit;
		
		return $this->dbSpec->sqlQuery($query, $res);
		
	}
    
    // aldes
	public function edit($id, $aValue) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));

		$last_key = end(array_keys($aValue));
		$query = "UPDATE cppmod_pbb_service_merge_sppt SET ";
		
		foreach ($aValue as $key => $value) {
			$query .= "$key='$value'";
			if ($key != $last_key) {
				$query .= ", ";
			}
		}
		
		$query .= " WHERE CPM_SID='$id'";
//                echo $query;exit();
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function del($id, $vers="") {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));		
		if (trim($vers)!='') $vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
		
		$query = "DELETE FROM cppmod_pbb_service_merge_sppt WHERE CPM_SPPT_DOC_ID='$id' ";
		if (trim($vers)!='') $query .= "AND CPM_SPPT_DOC_VERSION='$vers' ";
		
		// echo $query;		
		return $this->dbSpec->sqlQuery($query, $res);
	}
	
	public function isExist($id, $vers) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		$vers = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($vers));
		
		$query = "SELECT * FROM cppmod_pbb_service_merge_sppt WHERE CPM_SPPT_DOC_ID='$id' AND CPM_SPPT_DOC_VERSION='$vers'";
		
		#echo $query;		
		if ($this->dbSpec->sqlQuery($query, $res)) {
			$nRes = mysqli_num_rows($res);
			return ($nRes > 0);
		}
	}
	
	public function isExist_NOP($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT a.CPM_SPPT_DOC_ID, a.CPM_SPPT_DOC_VERSION, 
		b.CPM_TRAN_ID, b.CPM_TRAN_REFNUM, b.CPM_TRAN_SPPT_DOC_ID, b.CPM_SPPT_DOC_VERSION, b.CPM_TRAN_STATUS, b.CPM_TRAN_FLAG 
		FROM cppmod_pbb_service_merge_sppt a 
		INNER JOIN `cppmod_pbb_tranmain` b on a.CPM_SPPT_DOC_ID = b.CPM_TRAN_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION = b.CPM_SPPT_DOC_VERSION 
		AND b.CPM_TRAN_FLAG = '0'
		WHERE a.CPM_NOP = '".$nop."';";
		// echo $query;		
		$res = mysqli_query($this->dbSpec->getDBLink(), $query);
		$nRes = mysqli_num_rows($res);
		return $nRes;
		
	}
        
        public function getInitDataNOP($id) {
            global $DBLink;
            
            $qry = "SELECT CPM_ID, CPM_OP_NUMBER FROM cppmod_pbb_services  WHERE CPM_ID = '{$id}' ";
            
            if ($this->dbSpec->sqlQueryRow($qry, $row)) {
                $qry = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_service_merge WHERE CPM_MG_SID = '{$id}'";
                
                if ($this->dbSpec->sqlQueryRow($qry, $row2)) {
                    $row[0]['TOTAL'] = $row2[0]['TOTAL'];
                    
                    $qry = "SELECT CPM_WP_NAMA, IFNULL(CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, IFNULL(CPM_OP_LUAS_BANGUNAN, 0) AS CPM_OP_LUAS_BANGUNAN, CPM_OP_ALAMAT FROM cppmod_pbb_sppt_final
                            WHERE CPM_NOP = '".$row[0]['CPM_OP_NUMBER']."'
                            UNION ALL 
                            SELECT CPM_WP_NAMA, IFNULL(CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, IFNULL(CPM_OP_LUAS_BANGUNAN, 0) AS CPM_OP_LUAS_BANGUNAN, CPM_OP_ALAMAT FROM cppmod_pbb_sppt_susulan
                            WHERE CPM_NOP = '".$row[0]['CPM_OP_NUMBER']."'";
                    
                    if ($this->dbSpec->sqlQueryRow($qry, $row3)) {
                        $row[0]['CPM_WP_NAMA'] = $row3[0]['CPM_WP_NAMA'];
                        $row[0]['CPM_OP_LUAS_TANAH'] = $row3[0]['CPM_OP_LUAS_TANAH'];
                        $row[0]['CPM_OP_LUAS_BANGUNAN'] = $row3[0]['CPM_OP_LUAS_BANGUNAN'];
						$row[0]['CPM_OP_ALAMAT'] = $row3[0]['CPM_OP_ALAMAT'];
                        return $row[0];
                    } 
                    return $row[0];
                } 
            }else exit();
            
        }

        public function getInitData($id) {
            
            $qry = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_service_merge_sppt where CPM_SID = '{$id}'";
            
            if ($this->dbSpec->sqlQueryRow($qry, $row)) {
                if($row[0]['TOTAL'] == 0){
                    return $this->getDataDefault($id);
                } else {
                    $qry = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN FROM cppmod_pbb_services A, cppmod_pbb_service_merge_sppt B, 
                            cppmod_tax_kelurahan C, cppmod_tax_kecamatan D
                            WHERE A.CPM_ID = B.CPM_SID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,10) = C.CPC_TKL_ID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,7) = D.CPC_TKC_ID AND
                            A.CPM_ID = '{$id}' ";
                            
                    if ($this->dbSpec->sqlQueryRow($qry, $res)) {
                        return $res[0];
                    }
                }
            
            }
        }

        public function getDataDefault($id) {
            
            $qryTotal = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_service_merge_sppt
                        WHERE CPM_SID = '{$id}'";

            // die(var_dump($this->dbSpec->sqlQueryRow($qryTotal, $row)));

            if ($this->dbSpec->sqlQueryRow($qryTotal, $row)) {


                if($row[0]['TOTAL'] == 0){
                    
                    $qry = "SELECT CPM_OP_NUMBER, CPM_SPPT_YEAR FROM cppmod_pbb_services WHERE CPM_ID = '{$id}'";
                    
                    $bOK = $this->dbSpec->sqlQueryRow($qry, $rowDetail);
                    
                    $qryInsert = "INSERT INTO cppmod_pbb_service_merge_sppt 
                    SELECT 
                    '".$id."', TBL.* ,0
                    FROM 
                    (SELECT * FROM cppmod_pbb_sppt_final
                    WHERE CPM_NOP = '".$rowDetail[0]['CPM_OP_NUMBER']."' 
                    UNION ALL
                    SELECT * FROM cppmod_pbb_sppt_susulan 
                    WHERE CPM_NOP = '".$rowDetail[0]['CPM_OP_NUMBER']."') TBL ";
                    
                    $bOK = $this->dbSpec->sqlQuery($qryInsert, $res);
                    
                    $qrySppt = "SELECT CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_service_merge_sppt
                        WHERE CPM_SID = '{$id}'";
            
                    if ($this->dbSpec->sqlQueryRow($qrySppt, $rowSppt)) {
                        $spptdocid= $rowSppt[0]['CPM_SPPT_DOC_ID'];
                        $spptdocversion = $rowSppt[0]['CPM_SPPT_DOC_VERSION'];
                    }
                    
                    $qry = "SELECT CPM_MG_NOP_ANAK FROM cppmod_pbb_service_merge WHERE CPM_MG_SID = '{$id}'";
                    
                    $bOK = $this->dbSpec->sqlQuery($qry, $res);
                    $nops = "'".$rowDetail[0]['CPM_OP_NUMBER']."'";
                    while($ext = mysqli_fetch_array( $res )){
                        $nops .= ", '".$ext['CPM_MG_NOP_ANAK']."'";
                    } 
                    
                    $qry = "SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID IN 
                            (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN (".$nops."))
                            UNION ALL
                            SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID IN 
                            (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN (".$nops."))";
                    
                    $bOK = $this->dbSpec->sqlQuery($qry, $res);
                    
                    $num = 1;
                    
                    while($ext = mysqli_fetch_array( $res )){
                        $queryInsertExt = "INSERT INTO cppmod_pbb_service_merge_sppt_ext (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, CPM_OP_NUM, cpm_op_penggunaan, CPM_OP_LUAS_BANGUNAN, CPM_OP_JML_LANTAI, CPM_OP_THN_DIBANGUN, CPM_OP_THN_RENOVASI, CPM_OP_DAYA, 
                        CPM_OP_KONDISI, CPM_OP_KONSTRUKSI, CPM_OP_ATAP, CPM_OP_DINDING, CPM_OP_LANTAI, CPM_OP_LANGIT, CPM_FOP_AC_SPLIT, CPM_FOP_AC_WINDOW, CPM_FOP_AC_CENTRAL, 
                        CPM_FOP_KOLAM_LUAS, CPM_FOP_KOLAM_LAPISAN, CPM_FOP_PERKERASAN_RINGAN, CPM_FOP_PERKERASAN_SEDANG, CPM_FOP_PERKERASAN_BERAT, CPM_FOP_PERKERASAN_PENUTUP, 
                        CPM_FOP_TENIS_LAMPU_BETON, CPM_FOP_TENIS_LAMPU_ASPAL, CPM_FOP_TENIS_LAMPU_TANAH, CPM_FOP_TENIS_TANPA_LAMPU_BETON, CPM_FOP_TENIS_TANPA_LAMPU_ASPAL, 
                        CPM_FOP_TENIS_TANPA_LAMPU_TANAH, CPM_FOP_LIFT_PENUMPANG, CPM_FOP_LIFT_KAPSUL, CPM_FOP_LIFT_BARANG, CPM_FOP_ESKALATOR_SEMPIT, CPM_FOP_ESKALATOR_LEBAR, 
                        CPM_FOP_SALURAN, CPM_FOP_SUMUR, CPM_PAYMENT_PENILAIAN_BGN, CPM_PAYMENT_SISTEM, CPM_PAYMENT_INDIVIDU, CPM_NJOP_BANGUNAN, CPM_PAGAR_BESI_PANJANG, CPM_PAGAR_BATA_PANJANG, 
                        CPM_PEMADAM_HYDRANT, CPM_PEMADAM_SPRINKLER, CPM_PEMADAM_FIRE_ALARM, CPM_JPB2_KELAS_BANGUNAN, CPM_JPB3_TINGGI_KOLOM, CPM_JPB3_DAYA_DUKUNG_LANTAI, CPM_JPB3_LEBAR_BENTANG, 
                        CPM_JPB3_KELILING_DINDING, CPM_JPB3_LUAS_MEZZANINE, CPM_JPB4_KELAS_BANGUNAN, CPM_JPB5_KELAS_BANGUNAN, CPM_JPB5_LUAS_KMR_AC_CENTRAL, CPM_JPB5_LUAS_RUANG_AC_CENTRAL, 
                        CPM_JPB6_KELAS_BANGUNAN, CPM_JPB7_JENIS_HOTEL, CPM_JPB7_JUMLAH_BINTANG, CPM_JPB7_JUMLAH_KAMAR, CPM_JPB7_LUAS_KMR_AC_CENTRAL, CPM_JPB7_LUAS_RUANG_AC_CENTRAL, 
                        CPM_JPB8_TINGGI_KOLOM, CPM_JPB8_DAYA_DUKUNG_LANTAI, CPM_JPB8_LEBAR_BENTANG, CPM_JPB8_KELILING_DINDING, CPM_JPB8_LUAS_MEZZANINE, CPM_JPB9_KELAS_BANGUNAN, 
                        CPM_JPB12_TIPE_BANGUNAN, CPM_JPB13_JUMLAH_APARTEMEN, CPM_JPB13_KELAS_BANGUNAN, CPM_JPB13_LUAS_APARTEMEN_AC_CENTRAL, CPM_JPB13_LUAS_RUANG_AC_CENTRAL, 
                        CPM_JPB15_TANGKI_MINYAK_KAPASITAS, CPM_JPB15_TANGKI_MINYAK_LETAK, CPM_JPB16_KELAS_BANGUNAN)
                        VALUES ('".$spptdocid."', '".$spptdocversion."', '".$num."', '".$ext['cpm_op_penggunaan']."', '".$ext['CPM_OP_LUAS_BANGUNAN']."', 
                        '".$ext['CPM_OP_JML_LANTAI']."', '".$ext['CPM_OP_THN_DIBANGUN']."', '".$ext['CPM_OP_THN_RENOVASI']."', '".$ext['CPM_OP_DAYA']."', '".$ext['CPM_OP_KONDISI']."', 
                        '".$ext['CPM_OP_KONSTRUKSI']."', '".$ext['CPM_OP_ATAP']."', '".$ext['CPM_OP_DINDING']."', '".$ext['CPM_OP_LANTAI']."', '".$ext['CPM_OP_LANGIT']."', '".$ext['CPM_FOP_AC_SPLIT']."', 
                        '".$ext['CPM_FOP_AC_WINDOW']."', '".$ext['CPM_FOP_AC_CENTRAL']."', '".$ext['CPM_FOP_KOLAM_LUAS']."', '".$ext['CPM_FOP_KOLAM_LAPISAN']."', '".$ext['CPM_FOP_PERKERASAN_RINGAN']."', 
                        '".$ext['CPM_FOP_PERKERASAN_SEDANG']."', '".$ext['CPM_FOP_PERKERASAN_BERAT']."', '".$ext['CPM_FOP_PERKERASAN_PENUTUP']."', '".$ext['CPM_FOP_TENIS_LAMPU_BETON']."', 
                        '".$ext['CPM_FOP_TENIS_LAMPU_ASPAL']."', '".$ext['CPM_FOP_TENIS_LAMPU_TANAH']."', '".$ext['CPM_FOP_TENIS_TANPA_LAMPU_BETON']."', '".$ext['CPM_FOP_TENIS_TANPA_LAMPU_ASPAL']."', 
                        '".$ext['CPM_FOP_TENIS_TANPA_LAMPU_TANAH']."', '".$ext['CPM_FOP_LIFT_PENUMPANG']."', '".$ext['CPM_FOP_LIFT_KAPSUL']."', '".$ext['CPM_FOP_LIFT_BARANG']."', 
                        '".$ext['CPM_FOP_ESKALATOR_SEMPIT']."', '".$ext['CPM_FOP_ESKALATOR_LEBAR']."', '".$ext['CPM_FOP_SALURAN']."', '".$ext['CPM_FOP_SUMUR']."', '".$ext['CPM_PAYMENT_PENILAIAN_BGN']."', 
                        '".$ext['CPM_PAYMENT_SISTEM']."', '".$ext['CPM_PAYMENT_INDIVIDU']."', '".$ext['CPM_NJOP_BANGUNAN']."', '".$ext['CPM_PAGAR_BESI_PANJANG']."', '".$ext['CPM_PAGAR_BATA_PANJANG']."', 
                        '".$ext['CPM_PEMADAM_HYDRANT']."', '".$ext['CPM_PEMADAM_SPRINKLER']."', '".$ext['CPM_PEMADAM_FIRE_ALARM']."', '".$ext['CPM_JPB2_KELAS_BANGUNAN']."', 
                        '".$ext['CPM_JPB3_TINGGI_KOLOM']."', '".$ext['CPM_JPB3_DAYA_DUKUNG_LANTAI']."', '".$ext['CPM_JPB3_LEBAR_BENTANG']."', '".$ext['CPM_JPB3_KELILING_DINDING']."', 
                        '".$ext['CPM_JPB3_LUAS_MEZZANINE']."', '".$ext['CPM_JPB4_KELAS_BANGUNAN']."', '".$ext['CPM_JPB5_KELAS_BANGUNAN']."', '".$ext['CPM_JPB5_LUAS_KMR_AC_CENTRAL']."', 
                        '".$ext['CPM_JPB5_LUAS_RUANG_AC_CENTRAL']."', '".$ext['CPM_JPB6_KELAS_BANGUNAN']."', '".$ext['CPM_JPB7_JENIS_HOTEL']."', '".$ext['CPM_JPB7_JUMLAH_BINTANG']."', 
                        '".$ext['CPM_JPB7_JUMLAH_KAMAR']."', '".$ext['CPM_JPB7_LUAS_KMR_AC_CENTRAL']."', '".$ext['CPM_JPB7_LUAS_RUANG_AC_CENTRAL']."', '".$ext['CPM_JPB8_TINGGI_KOLOM']."', 
                        '".$ext['CPM_JPB8_DAYA_DUKUNG_LANTAI']."', '".$ext['CPM_JPB8_LEBAR_BENTANG']."', '".$ext['CPM_JPB8_KELILING_DINDING']."', '".$ext['CPM_JPB8_LUAS_MEZZANINE']."', 
                        '".$ext['CPM_JPB9_KELAS_BANGUNAN']."', '".$ext['CPM_JPB12_TIPE_BANGUNAN']."', '".$ext['CPM_JPB13_JUMLAH_APARTEMEN']."', '".$ext['CPM_JPB13_KELAS_BANGUNAN']."', 
                        '".$ext['CPM_JPB13_LUAS_APARTEMEN_AC_CENTRAL']."', '".$ext['CPM_JPB13_LUAS_RUANG_AC_CENTRAL']."', '".$ext['CPM_JPB15_TANGKI_MINYAK_KAPASITAS']."', 
                        '".$ext['CPM_JPB15_TANGKI_MINYAK_LETAK']."', '".$ext['CPM_JPB16_KELAS_BANGUNAN']."')
                        ";
                        $num++;
                        
                        $bOK = $this->dbSpec->sqlQuery($queryInsertExt, $resInsertExt);
                    }
                    
                    $qrySelect = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN FROM cppmod_pbb_services A, cppmod_pbb_service_merge_sppt B, 
                            cppmod_tax_kelurahan C, cppmod_tax_kecamatan D
                            WHERE A.CPM_ID = B.CPM_SID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,10) = C.CPC_TKL_ID AND
                            SUBSTR(A.CPM_OP_NUMBER,1,7) = D.CPC_TKC_ID AND
                            A.CPM_ID = '{$id}' ";
                    
                    if ($this->dbSpec->sqlQueryRow($qrySelect, $resSelect)) {
                        return $resSelect[0];
                    }
                    
                } else {
                    $qrySelect = "SELECT A.CPM_ID, A.CPM_REPRESENTATIVE, A.CPM_ATTACHMENT, A.CPM_DATE_RECEIVE, A.CPM_SPPT_DUE, A.CPM_SPPT_YEAR, A.CPM_SPPT_PAYMENT_DATE, B.*, C.CPC_TKL_KELURAHAN, D.CPC_TKC_KECAMATAN FROM cppmod_pbb_services A, cppmod_pbb_service_merge_sppt B, 
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
        
        public function updateToFinal($id) {
            
            $cari_final = "SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_SPPT_DOC_ID='$id'";
            
            $bOK = $this->dbSpec->sqlQuery($cari_final, $result);
            
            if(!$bOK) return $bOK;
            
            $tableName = 'cppmod_pbb_sppt_final';
            $tableNameExt = 'cppmod_pbb_sppt_ext_final';
            if ($final = mysqli_fetch_array($result)) {
                $tableName = 'cppmod_pbb_sppt_final';
                $tableNameExt = 'cppmod_pbb_sppt_ext_final';
            }else{
                $tableName = 'cppmod_pbb_sppt_susulan';
                $tableNameExt = 'cppmod_pbb_sppt_ext_susulan';
            }
            
            $query = "DELETE FROM ".$tableNameExt." WHERE CPM_SPPT_DOC_ID='$id'";
            
            $bOK = $this->dbSpec->sqlQuery($query, $res);
            
            if(!$bOK) return $bOK;
            
            $query = "INSERT INTO ".$tableNameExt." SELECT * FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID='$id'";
            
            $bOK = $this->dbSpec->sqlQuery($query, $res);
            
            if(!$bOK) return $bOK;
            
            $updateFinal = "UPDATE ".$tableName." A, cppmod_pbb_service_merge_sppt B
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
                
        public function updateToCurrent($id, $appConfig) {
            
            $qry = "SELECT A.CPM_NOP, A.CPM_WP_NAMA, A.CPM_WP_ALAMAT, A.CPM_WP_RT, A.CPM_WP_RW, A.CPM_WP_KODEPOS, A.CPM_WP_NO_HP, 
                IFNULL(A.CPM_OP_LUAS_TANAH,0) AS CPM_OP_LUAS_TANAH, IFNULL(A.CPM_OP_LUAS_BANGUNAN,0) AS CPM_OP_LUAS_BANGUNAN, 
                IFNULL(A.CPM_OP_KELAS_TANAH,'XXX') AS CPM_OP_KELAS_TANAH, IFNULL(A.CPM_OP_KELAS_BANGUNAN,'XXX') AS CPM_OP_KELAS_BANGUNAN, 
                IFNULL(A.CPM_NJOP_TANAH,0) AS CPM_NJOP_TANAH, IFNULL(A.CPM_NJOP_BANGUNAN,0) AS CPM_NJOP_BANGUNAN, A.CPM_OP_ALAMAT, A.CPM_OP_RT,
                A.CPM_OP_RW, A.CPM_WP_KOTAKAB, A.CPM_WP_KECAMATAN, A.CPM_WP_KELURAHAN,
                L.CPM_KELAS_BUMI_BEBAN, L.CPM_KELAS_BNG_BEBAN, L.CPM_LUAS_BUMI_BEBAN, L.CPM_LUAS_BNG_BEBAN, L.CPM_NJOP_BUMI_BEBAN, L.CPM_NJOP_BNG_BEBAN 
                FROM cppmod_pbb_service_merge_sppt A LEFT JOIN cppmod_tax_kabkota E ON A.CPM_WP_KOTAKAB = E.CPC_TK_ID LEFT JOIN 
                cppmod_pbb_sppt_anggota L ON A.CPM_NOP = L.CPM_NOP WHERE A.CPM_SID = '{$id}' ";
            
            if (!$this->dbSpec->sqlQueryRow($qry, $res)) {
                return false;
            }
            $dbUtils = new DbUtils($this->dbSpec);
            $aValue['CPM_NJOP_TANAH']=$res[0]['CPM_NJOP_TANAH'];
            $aValue['CPM_NJOP_BANGUNAN']=$res[0]['CPM_NJOP_BANGUNAN'];
            $aValue['CPM_NJOP_BUMI_BERSAMA']=$res[0]['CPM_NJOP_BUMI_BEBAN'];
            $aValue['CPM_NJOP_BANGUNAN_BERSAMA']=$res[0]['CPM_NJOP_BNG_BEBAN'];
            $aValue = $dbUtils->hitungTagihan($aValue, $appConfig);
            
            $nilaiPengurangan = $persenPengurangan = 0;
            $queryUpdateCurrent = "UPDATE cppmod_pbb_sppt_current SET 
                SPPT_PBB_HARUS_DIBAYAR =  ".$aValue['SPPT_PBB_HARUS_DIBAYAR'].",
                WP_NAMA =  '".mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_NAMA'])."' ,
                WP_ALAMAT = '".mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_WP_ALAMAT'])."' , 
                WP_RT =  '".$res[0]['CPM_WP_RT']."' ,
                WP_RW = '".$res[0]['CPM_WP_RW']."' ,
                WP_KELURAHAN = '".$res[0]['CPM_WP_KELURAHAN']."',  
                WP_KECAMATAN = '".$res[0]['CPM_WP_KECAMATAN']."',  
                WP_KOTAKAB = '".$res[0]['CPM_WP_KOTAKAB']."',  
                WP_KODEPOS = '".$res[0]['CPM_WP_KODEPOS']."', 
                WP_NO_HP = '".$res[0]['CPM_WP_NO_HP']."',  
                OP_LUAS_BUMI = '".$res[0]['CPM_OP_LUAS_TANAH']."',  
                OP_LUAS_BANGUNAN = '".$res[0]['CPM_OP_LUAS_BANGUNAN']."',  
                OP_KELAS_BUMI = '".$res[0]['CPM_OP_KELAS_TANAH']."',  
                OP_KELAS_BANGUNAN = '".$res[0]['CPM_OP_KELAS_BANGUNAN']."',  
                OP_NJOP_BUMI = '".$res[0]['CPM_NJOP_TANAH']."',   
                OP_NJOP_BANGUNAN = '".$res[0]['CPM_NJOP_BANGUNAN']."',  
                OP_NJOP = '".$aValue['OP_NJOP']."',  
                OP_NJOPTKP = '".$aValue['OP_NJOPTKP']."',  
                OP_NJKP = '".$aValue['OP_NJKP']."',  
                OP_ALAMAT = '".mysqli_real_escape_string($this->dbSpec->getDBLink(), $res[0]['CPM_OP_ALAMAT'])."',  
                OP_RT = '".$res[0]['CPM_OP_RT']."',  
                OP_RW = '".$res[0]['CPM_OP_RW']."', 
                OP_TARIF = '".$aValue['OP_TARIF']."',
                SPPT_PBB_PENGURANGAN = '".$nilaiPengurangan."',
                SPPT_PBB_PERSEN_PENGURANGAN = '".$persenPengurangan."'
                WHERE NOP = '".$res[0]['CPM_NOP']."'
            ";
            
            return $this->dbSpec->sqlQuery($queryUpdateCurrent, $res);
                
        }
        
        public function deleteDataPenggabungan($id) {
            $query = "DELETE FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID='$id'";
            $bOK = $this->dbSpec->sqlQuery($query, $res);
            if(!$bOK) return false; 
            
            $query = "DELETE FROM cppmod_pbb_service_merge_sppt WHERE CPM_SPPT_DOC_ID='$id'";
            return $this->dbSpec->sqlQuery($query, $res);
        }
        
        public function deleteDataAnak($id) {
            $qry = "SELECT CPM_MG_NOP_ANAK FROM cppmod_pbb_service_merge WHERE CPM_MG_SID = '{$id}'";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            if(!$bOK) return false;
            $nops = "";
            $i = 0;
            while($ext = mysqli_fetch_array( $res )){
                if ($i > 0) $nops .= ", ";
                $nops .= "'".$ext['CPM_MG_NOP_ANAK']."'";
                $i++;
            } 
			
            /* Masukkan ke dalam tabel history */
			$qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                    SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID IN 
                    (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN (".$nops."))";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            // if(!$bOK) return array('res'=>false,'msg'=>'Gagal insert history PBB SPPT EXT FINAL');
            if(!$bOK) return false;
            
            $qry = "INSERT INTO cppmod_pbb_sppt_ext_history 
                    SELECT * FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID IN 
                    (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN (".$nops."))";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            // if(!$bOK) return array('res'=>false,'msg'=>'Gagal insert history PBB SPPT EXT SUSULAN');
            if(!$bOK) return false;
            
            echo $qry = "INSERT INTO cppmod_pbb_sppt_history 
                    SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN (".$nops.")";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            // if(!$bOK) return array('res'=>false,'msg'=>'Gagal insert history PBB SPPT FINAL');
            if(!$bOK) return false;
            
            $qry = "INSERT INTO cppmod_pbb_sppt_history 
                    SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN (".$nops.")";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            // if(!$bOK) return array('res'=>false,'msg'=>'Gagal insert history PBB SPPT SUSULAN');
            if(!$bOK) return false;
            
            /* Hapus data NOP anak */
            $qry = "DELETE FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID IN 
                    (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN (".$nops."))";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            if(!$bOK) return false;
            
            $qry = "DELETE FROM cppmod_pbb_sppt_ext_susulan WHERE CPM_SPPT_DOC_ID IN 
                    (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN (".$nops."))";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            if(!$bOK) return false;
            
            $qry = "DELETE FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN (".$nops.")";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            if(!$bOK) return false;
            
            $qry = "DELETE FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN (".$nops.")";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            if(!$bOK) return false;
            
            $qry = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP IN (".$nops.")";
            $bOK = $this->dbSpec->sqlQuery($qry, $res);
            return $bOK;
        }
}
?>
